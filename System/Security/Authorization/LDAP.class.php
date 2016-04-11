<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
namespace InfEra\System\Security\Authorization;
use InfEra\Application as Application;
use InfEra\Base\User\Models\User as User;
use InfEra\System\DirectoryServices\DirectoryService as DirectoryService;

/**
 * LDAP Authorization Provider
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Security\Authorization
 */
class LDAP extends IAuthorization
{
    private $DS_HOST = '';
    private $DS_PORT = 389;
    private $DS_PREFIX = '';
    private $DS_SUFFIX = '';
    private $DS_BASEDN = '';

    private $DirectoryService = null;

    private $LastState = '';

    /**
     * LDAP constructor.
     * @param array $Params
     */
    public function __construct(array $Params)
    {
        $this->AuthorityMode = IAuthorization::AUTHORITY_INTERNAL;

        if (array_key_exists('DS_HOST', $Params))
        {
            $this->DS_HOST = $Params['DS_HOST'];
        }
        else
        {
            throw new \Exception("LDAP Authority: No DS-host in params");
        }

        if (array_key_exists('DS_PORT', $Params))
        {
            $this->DS_PORT = $Params['DS_PORT'];
        }

        if (array_key_exists('DS_PREFIX', $Params))
        {
            $this->DS_PREFIX = $Params['DS_PREFIX'];
        }

        if (array_key_exists('DS_SUFFIX', $Params))
        {
            $this->DS_SUFFIX = $Params['DS_SUFFIX'];
        }

        if (array_key_exists('DS_BASEDN', $Params))
        {
            $this->DS_BASEDN = $Params['DS_BASEDN'];
        }
        else
        {
            throw new \Exception("LDAP Authority: No DS Base DN in params");
        }

        $this->DirectoryService = new DirectoryService($this->DS_HOST, $this->DS_PREFIX, $this->DS_SUFFIX);
    }

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
        return function_exists('ldap_connect');
    }

    /**
     * Initialization of Current Provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function Init()
    {
//        if (isset(Application::$Session['iefwcu']))
//        {
//            var_dump(Application::$Session['iefwcu']);
//            $cusd = unserialize(Application::$Security->DecryptString(Application::$Session['iefwcu']));
//            var_dump($cusd);
//            if ($cusd['r'] == 'Base')
//            {
//                Application::$Security->UserLogin($cusd['l'], $cusd['p']);
//            }
//        }
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
        return Application::$Router->CreateUrl(array_merge(
            array(
                'controller' => 'User',
                'action' => 'Login'
            ), array()));
    }

    /**
     * Getting logout url
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string Logout url
     */
    public function GetLogoutUrl()
    {
        return Application::$Router->CreateUrl(array_merge(
            array(
                'controller' => 'User',
                'action' => 'Logout'
            ), array()));
    }

    /**
     * Getting logout url
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string Logout url
     */
    public function GetProfileUrl()
    {
        return Application::$Router->CreateUrl(array_merge(
            array(
                'controller' => 'User',
                'action' => 'Profile'
            ), array()));
    }

    /**
     * Logout user with provider
     */
    public function LogoutUser()
    {
    }

    /**
     * Check user's authority with provider
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Login
     * @param string $Password
     * @return boolean
     */
    public function CheckAuthoritty($Login, $Password)
    {
        $result = false;

        if ($this->DirectoryService->Connect())
        {
            if ($this->DirectoryService->CheckAuthority($Login, $Password))
            {
                $result = true;
                $this->LastState = '';
                $this->DirectoryService->Close();
            }
            else
            {
                $this->LastState = '[' .
                    $this->DirectoryService->LastErrorNo . '] ' .
                    $this->DirectoryService->LastErrorMessage;
            }

        }
        else
        {
            $this->LastState = '[' .
                $this->DirectoryService->LastErrorNo . '] ' .
                $this->DirectoryService->LastErrorMessage;
        }

        return $result;
    }

    /**
     * Getting User object
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $Login
     * @param string $Password
     * @param bool $Remember
     *
     * @return InfEra\Base\User\Models\BaseUser User object
     */
    public function GetUser($Login, $Password)
    {
        $result = null;

        if ($this->DirectoryService->Connect())
        {
            if ($this->DirectoryService->CheckAuthority($Login, $Password))
            {
                $LDAPUserInfo = $this->DirectoryService->Search(
                    $this->DS_BASEDN,
                    "(sAMAccountName=$Login*)",
                    array(
                        "objectGUID",
                        "sAMAccountName",
                        "givenName",
                        "initials",
                        "sn",
                        "displayNamePrintable",
                        "mailNickName",
                        "mail",
                        "wWWHomePage",
                        "memberOf",
                        "userWorkstations",
                        "company",
                        "department"
                    )
                );
                $this->DirectoryService->Close();
                if ($LDAPUserInfo->Count() > 0)
                {
                    $LDAPUserInfo = $LDAPUserInfo->First();
                    $User = new User();
                    $User->Provider = 'LDAP';
                    $User->Login = $LDAPUserInfo->Attributes['sAMAccountName'][0];
                    if (isset($LDAPUserInfo->Attributes['givenName']))
                    {
                        $User->FirstName = $LDAPUserInfo->Attributes['givenName'][0];
                    }
                    if (isset($LDAPUserInfo->Attributes['sn']))
                    {
                        $User->LastName = $LDAPUserInfo->Attributes['sn'][0];
                    }
                    if (isset($LDAPUserInfo->Attributes['mail']))
                    {
                        $User->Email = $LDAPUserInfo->Attributes['mail'][0];
                    }
                    $result = $User;
                }
            }
            else
            {
                $this->LastState = '[' .
                    $this->DirectoryService->LastErrorNo . '] ' .
                    $this->DirectoryService->LastErrorMessage;
            }

        }
        else
        {
            $this->LastState = '[' .
                $this->DirectoryService->LastErrorNo . '] ' .
                $this->DirectoryService->LastErrorMessage;
        }

        return $result;
    }

    /**
     * Get Last Operation State
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return string
     */
    public function GetLastOperationState()
    {
        return $this->LastState;
    }
}