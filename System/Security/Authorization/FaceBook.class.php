<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
namespace InfEra\System\Security\Authorization
{        
    use InfEra\WAFP\Application;
    require_once(Application::$Configuration->FrameworkPath . 'Libs/FaceBook/FaceBook.class.php');
    /**
     * FaceBook Authorization Provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Security\Authorization
     */
    class FaceBook extends IAuthorization
    {
        /**
         * FaceBook AppId
         * @var string
         */
        private $AppId = '428088973922904';
        
        /**
         * FaceBook AppSecret
         * @var string
         */
        private $AppSecrect = 'e3e913d601a5bd37d1b5367b4bbdc879';                        
        
        /**
         * FaceBook Query Object
         * @var object
         */
        private $FaceBook = null;   
        
        /**
         * Base user object
         * @var InfEra\Base\User\Models\BaseUser
         */
        private $User = null;
        
        /**
         * Detection of Accessibility of Current Provider
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return boolean Is provider accessible 
         */
        public function Detect()
        {                            
            $user = $this->FaceBook->getUser();             
            return (!is_null(Application::$Request["signed_request"]) || $user);
        }
        
        /**
         * Constructor 
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         */
        public function __construct()
        {
            $this->FaceBook = new \Facebook
            (
                array
                (
                    'appId' => $this->AppId,
                    'secret' => $this->AppSecrect,
                )
            );
        }
        
        /**
         * Initialization of Current Provider 
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         */
        public function Init()
        {                 
            if (isset(Application::$Session['iefwcu']))
            {                
                $cusd = unserialize(Application::$Security->DecryptString(Application::$Session['iefwcu']));
                if ($cusd['r'] == 'FaceBook')
                {                 
                    Application::$Security->UserLogin($cusd['l']);
                }
            }
            else
            {
                $user = $this->FaceBook->getUser();
                if($user)
                {                    
                    $this->GetUser();                
                    if (Application::$Security->GetUserByLogin($this->User->Login) === NULL)                
                    {                        
                        // @TODO Check if exists
                        Application::$Security->UserRegister($this->User);                     
                    }
                    Application::$Security->UserLogin($this->User->Login);
                }   
            }
        }
        
        /**
         * Getting login url
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return string Login url 
         */
        public function GetLoginUrl()
        {
            return $this->FaceBook->getLoginUrl(array
            (
                'redirect_uri' => Application::$Router->CreateUrl(array
                (
                    'controller' => 'User',                    
                ))
            ));
        }
        
        /**
         * Getting logout url
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return string Logot url 
         */
        public function GetLogoutUrl()
        {            
            return $this->FaceBook->getLogoutUrl(array
            (
                'next' => Application::$Router->CreateUrl(array
                (
                    'controller' => 'Pages',   
                    'action' => 'Index',
                )),
            ));
        }
        
        /**
         * Logout user with provider         
         */
        public function LogoutUser()
        {
            $this->FaceBook->destroySession();
        }
        
        /**
         * Getting User object
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return InfEra\Base\User\Models\BaseUser User object 
         */
        public function GetUser()
        {
            $Result = NULL;
            if ($this->User)
            {
                $Result = $this->User;
            }
            else
            {
                $user = $this->FaceBook->getUser();
                if($user)
                {
                    $Profile = $this->FaceBook->api('/me');                
                    $Result = new \InfEra\Base\User\Models\User();
                    $Result->Provider = "FaceBook";
                    $Result->Login = $Profile['username'];
                    $Result->Password = NULL;
                    $Result->FirstName = $Profile['first_name'];
                    $Result->LastName = $Profile['last_name'];
                    $Result->Email = $Profile['email'];
                    $Result->Avatar = "https://graph.facebook.com/$user/picture";
                    $Result->Birthday = new \DateTime($Profile['birthday']);
                    $Result->Location = $Profile['location']['name'];
                    $Result->Gender = $Profile['gender'];
                    $Result->Timezone = (int)$Profile['timezone'];
                    $Result->Locale = substr($Profile['locale'], 0, 2);                
                    
                    $this->User = $Result;
                } 
            }            
            
            return $Result;
        }
    }
}