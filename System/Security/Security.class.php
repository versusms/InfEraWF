<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 */
namespace InfEra\System\Security
{               
    use \InfEra\Application as Application;
    /**
     * Base security provider.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>     
     * @package    InfEra[System]
     * @subpackage Security
     * @version    1.0
     */
    class Security
    {
        /**
         * User state - User logged in 
         */
        const USER_LOGGEDIN = 1;
        
        /**
         * User state - User not found in database
         */
        const USER_NOTFOUND = 2;
        
        /**
         * User state - User's does not match password in database
         */
        const USER_INVALIDPASSWORD = 3;
        
        /**
         * User state - User's account disabled
         */
        const USER_DISABLED = 4;
        
        ################################################
        ################################################
        ################################################

        /**
         * User logged flag.
         * @var boolean 
         */
        public $IsUserLogged = false;
        
        /**
         * Current user object.
         * @var \InfEra\Base\User\Models\User 
         */
        public $CurrentUser = NULL;
        
        /**
         * Current Provider.
         * @var InfEra\System\Security\Authorization\IAuthorization
         */
        private $CurrentProvider = null;
        
        /**
         * Providers List.
         * @var array
         */
        private $Providers = array();   
        
        /**
         * HashKey for encryption.
         * @var string 
         */
        private $HashKey = 'dAx"g%RGOXwXeq5n:gAKF:73s{ug~kAkrVxt)%%S3Wavc?(N;1';
        
        /**
         * Detection and Initialization of Security Providers.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function __construct()
        {
            if ($HashKey = Application::$Configuration->Get('HashKey'))
            {
                $this->HashKey = $HashKey;
            }
            
            $providers = Application::$Configuration->Get('AuthorizationProviders');
            
            if (!is_array($providers) || count($providers) == 0)
            {
                $providers = array('Base');
            }            
            if (!is_null($providerName = Application::$Session['iefwsp']))
            {
                if (in_array($providerName, $providers))
                {
                    $providerName = 'InfEra\System\Security\Authorization\\' . $providerName;
                    $this->CurrentProvider = new $providerName();
                }
            }            
            
            foreach ($providers as $provider)
            {
                $providerName = 'InfEra\System\Security\Authorization\\' . $provider;                
                $this->Providers[$provider] = new $providerName();
                if ($this->Providers[$provider]->Detect() &&
                        (is_null($this->CurrentProvider) ||
                        ($this->CurrentProvider instanceof \InfEra\System\Security\Authorization\Base))
                    )
                {
                    $this->CurrentProvider = $this->Providers[$provider];
                    Application::$Session['iefwsp'] = $provider;
                }
            }
            
            if (!($this->CurrentProvider instanceof \InfEra\System\Security\Authorization\IAuthorization))
            {                
                trigger_error('Security provider is not detected', E_USER_ERROR);
            }
        }
        
        /**
         * 
         */
        public function Init()
        {            
            if (!($this->CurrentProvider instanceof \InfEra\System\Security\Authorization\IAuthorization))
            {                
                trigger_error('Security provider is not detected', E_USER_ERROR);
            }
            else
            {                
                $this->CurrentProvider->Init();                
            }
        }
        
        /**
         * 
         */
        public function UserRegister(\InfEra\Base\User\Models\User $UserData)
        {
            // @TODO Check if exists
            $UserData->Password = $this->Hash($UserData->Password);                       
            Application::$DBContext->GetDbSet("\InfEra\Base\User\Models\User")->Add($UserData);
        }
        
        /**
         * 
         * @return int Login status
         */
        public function UserLogin($Login, $Password = "")
        {
            $result = Security::USER_LOGGEDIN;                        
            
            $dbs = Application::$DBContext->GetDbSet("\InfEra\Base\User\Models\User")
                        ->Where("Login = '$Login'")
                        ->Limit(0, 1);
            
            $users = $dbs->Select();            
            
            if (count($users) > 0)
            {
                $cUser = array_shift($users);
                if ($cUser->IsEnabled)
                {
                    if ($cUser->Provider == 'Base')
                    {
                        if ($cUser->Password == $this->Hash($Password))
                        {                                                        
                            $this->CurrentUser = $cUser;
                            $this->CurrentUser->Status = 'Online';
                            $this->CurrentUser->LastActivity = $this->CurrentUser->LastActivity->setTimestamp(time());
                            $dbs->Store($this->CurrentUser);                            
                            
                            $cusd = array('l' => $Login, 'p' => $Password, 'r' => 'Base');
                            Application::$Session["iefwcu"] = $this->EncryptString(serialize($cusd));
                            $this->CurrentUser->Password = "";
                            $this->IsUserLogged = true;
                            if (!isset(Application::$Session['locale']))
                            {
                                Application::$Session['locale'] = $this->CurrentUser->Locale;
                            }
                            $result = Security::USER_LOGGEDIN;
                        }
                        else
                        {
                            $result = Security::USER_INVALIDPASSWORD;
                        }                        
                    }
                    else
                    {
                        if ($pu = $this->CurrentProvider->GetUser())
                        {
                            $this->CurrentUser = $cUser;
                            $this->CurrentUser->Status = 'Online';
                            $this->CurrentUser->LastActivity = $this->CurrentUser->LastActivity->setTimestamp(time());
                            $dbs->Store($this->CurrentUser);                            
                            
                            $cusd = array('l' => $Login, 'r' => $pu->Provider);
                            Application::$Session["iefwcu"] = $this->EncryptString(serialize($cusd));
                            $this->CurrentUser->Password = "";
                            $this->IsUserLogged = true;
                            if (!isset(Application::$Session['locale']))
                            {
                                Application::$Session['locale'] = $this->CurrentUser->Locale;
                            }
                            $result = Security::USER_LOGGEDIN;
                        }
                    }                    
                }
                else
                {
                    $result = Security::USER_DISABLED;
                }                
            }
            else
            {
                $result = Security::USER_NOTFOUND;
            }
            
            return $result;
        }
        
        /**
         * 
         * @return string Logout url for provider
         */
        public function UserLogout()
        {
            $result = "";
            if ($this->IsUserLogged && $this->CurrentUser)
            {
                $this->CurrentUser->Status = 'Offline';
                $this->CurrentUser->LastActivity = $this->CurrentUser->LastActivity->setTimestamp(time());
                unset($this->CurrentUser->Password);                
                Application::$DBContext->GetDbSet("\InfEra\Base\User\Models\User")->Store($this->CurrentUser);                
                unset(Application::$Session['iefwcu']);
                unset(Application::$Session['iefwsp']);
                $result = $this->CurrentProvider->GetLogoutUrl();
                $this->CurrentProvider->LogoutUser();
            }
            return $result;
        }
        
        /**
         * @return \InfEra\Base\User\Models\User User with login
         */
        public function GetUserByLogin($Login)
        {
            $result = NULL;
            
            $dbs = Application::$DBContext->GetDbSet("\InfEra\Base\User\Models\User")
                        ->Where("Login = '$Login'")
                        ->Limit(0, 1);
            
            $users = $dbs->Select();
            
            if (count($users))
            {
                $result = array_shift($users);
                $result->Password = "";
            }
            
            return $result;
        }
        
        /**
         * @return \InfEra\Base\User\Models\User  User with email
         */
        public function GetUserByEmail($Email)
        {
            $result = NULL;
            
            $dbs = Application::$DBContext->GetDbSet("\InfEra\Base\User\Models\User")
                        ->Where("Email = '$Email'")
                        ->Limit(0, 1);
            
            $users = $dbs->Select();
            
            if (count($users))
            {
                $result = array_shift($users);
                $result->Password = "";
            }
            
            return $result;
        }
        
        /**
         * Getting list og login urls 
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return array List of login urls
         */
        public function GetLoginUrls()
        {
            $result = array();            
            
            foreach ($this->Providers as $providerName => $provider)
            {
                $result[$providerName] = "/User/LoginVia/?p=" . $providerName;
            }
            
            return $result;
        }
        
        /**
         * Getting list og login urls 
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return array List of login urls
         */
        public function GetLoginUrlViaProvider($ProviderName)
        {
            $result = "";
            
            if (isset($this->Providers[$ProviderName]))
            {
                $result = $this->Providers[$ProviderName]->GetLoginUrl();
            }
            
            return $result;
        }
        
        /**
         * Формирует хеш из строки
         * 
         * @param   string $string - исходная строка
         * 
         * @return   string
         */
        public function Hash($string)
        {
            $string = md5($string);
            return md5(substr($string, 32 - 19, 19) . substr($string, 0, 32 - 19));
        }
        
        /**
         * Шифрует строку с обратным шифрованием
         * 
         * @param   string  $string - исходная строка
         * 
         * @return  string
         */
        public function EncryptString($string)
        {
            $result = "";
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $result = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->Hash($this->HashKey), $string, MCRYPT_MODE_ECB, $iv);

            return $result;
        }

        /**
         * Расшифровывает строку
         * 
         * @param   string  $string - шифрованная строка
         * 
         * @return  string
         */
        public function DecryptString($string)
        {
            $result = "";
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->Hash($this->HashKey), $string, MCRYPT_MODE_ECB, $iv);        
            $result = rtrim($result, "\0\4");

            return $result;
        }
    }
}