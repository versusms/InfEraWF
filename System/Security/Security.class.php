<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Security;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Security\AccessControl\AccessController;
use InfEra\WAFP\System\Security\Authorization\IAuthorization;
use InfEra\WAFP\Base\User\Models\User;

/**
 * Base security provider.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
     * User state - Invalid credentials by provider
     */
    const USER_INVALIDCREDENTIALS = 3;

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
     * Last User state string
     * @var string
     */
    public $LastUserState = '';

    /**
     * Current Provider Name.
     * @var string
     */
    private $CurrentProviderName = '';

    /**
     * Current Provider.
     * @var \InfEra\System\Security\Authorization\IAuthorization
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
     * [DESCRIPTION]
     * @var \InfEra\System\Security\AccessControl\AccessController
     */
    private $AccessController;

    /**
     * Detection and Initialization of Security Providers.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function __construct()
    {
        if ($HashKey = Application::$Configuration->Get('HashKey')) {
            $this->HashKey = $HashKey;
        }

        $providers = Application::$Configuration->Get('AuthorizationProviders');

        if (!is_array($providers) || count($providers) == 0) {
            $providers = array('Base' => array());
        }
        if (!is_null($providerName = Application::$Session['iefwsp'])) {
            if (in_array($providerName, $providers)) {
                $providerName = 'InfEra\WAFP\System\Security\Authorization\\' . $providerName;
                $this->CurrentProvider = new $providerName();
            }
        }

        // @TODO Few providers
        foreach ($providers as $provider => $providerParams) {
            $providerName = 'InfEra\WAFP\System\Security\Authorization\\' . $provider;
            $this->Providers[$provider] = new $providerName($providerParams);
            if ($this->Providers[$provider]->Detect() &&
                (is_null($this->CurrentProvider) ||
                    ($this->CurrentProvider instanceof \InfEra\WAFP\System\Security\Authorization\Base))
            ) {
                $this->CurrentProviderName = $provider;
                $this->CurrentProvider = $this->Providers[$provider];
                Application::$Session['iefwsp'] = $provider;
            }
        }

        if (!($this->CurrentProvider instanceof IAuthorization)) {
            trigger_error('Security provider is not detected', E_USER_ERROR);
        }

        $this->AccessController = new AccessController();
//            $this->AccessController->CollectAccessRules();
//            $this->AccessController->UpdateGuestRules();
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     */
    public function Init()
    {
        if (!($this->CurrentProvider instanceof IAuthorization)) {
            trigger_error('Security provider is not detected', E_USER_ERROR);
        } else {
            $this->CurrentProvider->Init();
            $cusd = array();
            if (isset(Application::$Session['iefwcu'])) {
                $cusd = unserialize(Application::$Security->DecryptString(Application::$Session['iefwcu']));
            } elseif (isset(Application::$Request->Cookie['iefwcu'])) {
                $cusd = unserialize(Application::$Security->DecryptString(Application::$Request->Cookie['iefwcu']));
            }
            if (count($cusd) > 0) {
                if ($this->UserReLogin($cusd['l'], $cusd['r'])) {
                    // Add User Role
                    $this->CurrentUser->Roles->Add(AccessController::ROLE_USER, $this->AccessController->GetUserRole());
                } else {
                    // Add Guest Role
                    // $this->CurrentUser->Roles->Add(AccessController::ROLE_GUEST, $this->AccessController->GetGuestRole());
                }
            }
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param User $UserData
     * @throws \InfEra\WAFP\System\Entity\Exceptions\DbSetException
     */
    public function UserRegister(User $UserData)
    {
        // @TODO Check if exists
        $UserData->Password = $this->Hash($UserData->Password);
        Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User')->Add($UserData);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Login
     * @param string $Password
     * @param bool $Remember
     * @return int Login status
     */
    public function UserLogin(string $Login, string $Password = "", bool $Remember = false) : int
    {
        $result = Security::USER_LOGGEDIN;

        $dbs = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User');
        $users = $dbs->Where("Login = '$Login'")
            ->Limit(0, 1)
            ->Select();

        if ($this->CurrentProvider->CheckAuthoritty($Login, $Password)) {
            if ($users->Count() === 0
                && isset(Application::$Configuration['AuthorizationCreateFromProvider'])
                && Application::$Configuration['AuthorizationCreateFromProvider']
            ) {
                $userFromProvider = $this->CurrentProvider->GetUser($Login, $Password);
                // @TODO Aprove from admin by settings
                $uid = $dbs->Add($userFromProvider);
                $users = $dbs->Where("Login = '$Login'")
                    ->Limit(0, 1)
                    ->Select();
            }

            if ($users->Count() > 0) {
                $cUser = $users->First();
                if ($cUser->IsEnabled) {
                    $this->CurrentUser = $cUser;
                    // @TODO ServiceJob User to offline
                    $this->CurrentUser->Status = 'Online';
                    $this->CurrentUser->LastActivity = $this->CurrentUser->LastActivity->setTimestamp(time());
                    $dbs->Store($this->CurrentUser);

                    $cusd = array('l' => $Login, 'r' => $this->CurrentUser->Provider);
                    Application::$Session["iefwcu"] = $this->EncryptString(serialize($cusd));
                    if ($Remember) {
                        Application::$Response->SetCookie(
                            "iefwcu",
                            Application::$Session["iefwcu"],
                            time() + 60 * 60 * 24 * 30,
                            '/',
                            null,
                            null,
                            true
                        );
                    }
                    $this->CurrentUser->Password = "";
                    $this->IsUserLogged = true;
                    if (!isset(Application::$Session['locale'])) {
                        Application::$Session['locale'] = $this->CurrentUser->Locale;
                    }
                    $result = Security::USER_LOGGEDIN;
                } else {
                    $result = Security::USER_DISABLED;
                }
            } // Not found
            else {
                $result = Security::USER_NOTFOUND;
            }
        } else {
            $result = Security::USER_INVALIDCREDENTIALS;
            $this->LastUserState = '[' . $this->CurrentProviderName . '] ' . $this->CurrentProvider->GetLastOperationState();
        }

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Login
     * @param string $Provider
     * @return bool
     * @throws \InfEra\WAFP\System\Entity\Exceptions\DbSetException
     */
    private function UserReLogin(string $Login, string $Provider) : bool
    {
        $result = false;

        $dbs = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User');
        $users = $dbs->Where("Login = '$Login' AND Provider = '$Provider'")
            ->Limit(0, 1)
            ->Select();

        if ($users->Count() > 0) {
            $Flag = ($this->CurrentProvider->GetAuthorityMode() == IAuthorization::AUTHORITY_EXTERNAL) ? $this->CurrentProvider->CheckAuthoritty($Login, "") : true;
            //var_dump($Flag);die();
            if ($Flag) {
                $cUser = $users->First();
                if ($cUser->IsEnabled) {
                    $this->CurrentUser = $cUser;
                    // @TODO ServiceJob User to offline
                    $this->CurrentUser->Status = 'Online';
                    $this->CurrentUser->LastActivity = $this->CurrentUser->LastActivity->setTimestamp(time());
                    $dbs->Store($this->CurrentUser);

//                    if (Application::$Request->Cookie->HasKey("iefwcu"))
//                    {
//                        Application::$Response->SetCookie(
//                            //@TODO to settings
//                            "iefwcu",
//                            Application::$Request->Cookie["iefwcu"],
//                            time() + 60 * 60 * 24 * 30,
//                            '/',
//                            null,
//                            null,
//                            true
//                        );
//                    }
                    $this->CurrentUser->Password = "";
                    $this->IsUserLogged = true;
                    if (!isset(Application::$Session['locale'])) {
                        Application::$Session['locale'] = $this->CurrentUser->Locale;
                    }
                    $result = true;
                } else {
                    //@TODO Notification
                }
            }
        }

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return string
     * @throws \InfEra\WAFP\System\Entity\Exceptions\DbSetException
     */
    public function UserLogout() : string
    {
        $result = "";
        if ($this->IsUserLogged && $this->CurrentUser) {
            $this->CurrentUser->Status = 'Offline';
            $this->CurrentUser->LastActivity = $this->CurrentUser->LastActivity->setTimestamp(time());
            unset($this->CurrentUser->Password);
            Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User')->Store($this->CurrentUser);
            unset(Application::$Session['iefwcu']);
            unset(Application::$Session['iefwsp']);
            $result = $this->CurrentProvider->GetLogoutUrl();
            $this->CurrentProvider->LogoutUser();
            if (Application::$Request->Cookie->HasKey("iefwcu")) {
                Application::$Response->SetCookie("iefwcu", "", time(0), '/');
            }
        }
        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Login
     * @return User
     */
    public function GetUserByLogin(string $Login) : User
    {
        $result = NULL;

        $dbs = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User')
            ->Where("Login = '$Login'")
            ->Limit(0, 1);

        $users = $dbs->Select();

        if (count($users)) {
            $result = array_shift($users);
            $result->Password = "";
        }

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Email
     * @return User
     */
    public function GetUserByEmail(string $Email) : User
    {
        $result = NULL;

        $dbs = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User')
            ->Where("Email = '$Email'")
            ->Limit(0, 1);

        $users = $dbs->Select();

        if (count($users)) {
            $result = array_shift($users);
            $result->Password = "";
        }

        return $result;
    }

    /**
     * Getting login url via current provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string Login url via current provider
     */
    public function GetLoginUrl() : string
    {
        return $this->CurrentProvider->GetLoginUrl();
    }

    /**
     * Getting logout url via current provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string Logout url via current provider
     */
    public function GetLogoutUrl() : string
    {
        return $this->CurrentProvider->GetLogoutUrl();
    }

    /**
     * Getting user profile url via current provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string User profile url via current provider
     */
    public function GetProfileUrl() : string
    {
        return $this->CurrentProvider->GetProfileUrl();
    }

    /**
     * Getting authority mode via current provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return int Authority mode via current provider
     */
    public function GetAuthrityMode() : int
    {
        return $this->CurrentProvider->GetAuthorityMode();
    }

    /**
     * Getting list og login urls
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return array List of login urls
     */
    public function GetLoginUrls() : array
    {
        $result = array();

        foreach ($this->Providers as $providerName => $provider) {
            $result[$providerName] = "/User/LoginVia/?p=" . $providerName;
        }

        return $result;
    }

    /**
     * Getting list og login urls
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $ProviderName
     * @return array List of login urls
     */
    public function GetLoginUrlViaProvider(string $ProviderName) : array
    {
        $result = "";

        if (isset($this->Providers[$ProviderName])) {
            $result = $this->Providers[$ProviderName]->GetLoginUrl();
        }

        return $result;
    }

    /**
     * Get hash from string
     *
     * @param   string $string - исходная строка
     * @return   string
     */
    public function Hash(string $string) : string
    {
        $string = md5($string);
        return md5(substr($string, 32 - 19, 19) . substr($string, 0, 32 - 19));
    }

    /**
     * Encrypt string
     *
     * @param   string $String Source string
     * @return  string
     */
    public function EncryptString(string $String) : string
    {
        $result = "";
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $result = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->Hash($this->HashKey), $String, MCRYPT_MODE_ECB, $iv);

        return $result;
    }

    /**
     * Decode string
     *
     * @param   string $String Encrypted string
     * @return  string
     */
    public function DecryptString(string $String) : string
    {
        $result = "";
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->Hash($this->HashKey), $String, MCRYPT_MODE_ECB, $iv);
        $result = rtrim($result, "\0\4");

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Object
     * @param \InfEra\Base\User\Models\User $User
     *
     * @return bool
     */
    public function ValidateUserAccess(string $Object, User $User = NULL) : bool
    {
        if (is_null($User)) {
            $User = $this->CurrentUser;
        }

        return $this->AccessController->ValidateAccess($Object, $User);
    }

    /**
     * Generate UUID v4 Identifier
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $data
     * @return string
     */
    public function GUIDv4($data) : string
    {
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}