<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Security\Authorization;

use InfEra\WAFP\Application;
use InfEra\WAFP\Base\User\Models\User;

/**
 * FaceBook Authorization Provider
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Security\Authorization
 */
class OAuth2 extends IAuthorization
{
    /**
     * OAuth Service URL
     * @var string
     */
    private $ServiceURL = '';

    /**
     * OAuth Service ID
     * @var string
     */
    private $ServiceId = '';

    /**
     * FaceBook AppSecret
     * @var string
     */
    private $ServiceSecrect = '';

    /**
     * [DESCRIPTION]
     * @var string
     */
    private $LastState = '';

    /**
     * [DESCRIPTION]
     * @var object
     */
    private $CurrentUserInfo = null;

    private $CurrentToken = null;

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
        //$user = $this->FaceBook->getUser();
        return true; //(!is_null(Application::$Request["signed_request"]) || $user);
    }

    /**
     * Constructor
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function __construct(array $Params)
    {
        $this->AuthorityMode = IAuthorization::AUTHORITY_EXTERNAL;
        
        if (array_key_exists('OA_URL', $Params)) {
            $this->ServiceURL = $Params['OA_URL'];
        } else {
            throw new \Exception("OAuth2 Authority: No Service URL in params");
        }

        if (array_key_exists('OA_ID', $Params)) {
            $this->ServiceId = $Params['OA_ID'];
        } else {
            throw new \Exception("OAuth2 Authority: No Service ID in params");
        }

        if (array_key_exists('OA_SECRET', $Params)) {
            $this->ServiceSecrect = $Params['OA_SECRET'];
        } else {
            throw new \Exception("OAuth2 Authority: No Service Secret in params");
        }
    }

    /**
     * Initialization of Current Provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function Init()
    {
        if ((isset(Application::$Request['code']) && !isset(Application::$Session['oauthv2c']))
            ||
            (isset(Application::$Request['code']) && isset(Application::$Session['oauthv2c']) && Application::$Session['oauthv2c'] != Application::$Request['code'])
        )
        {
            if ($token = $this->GetTokenByCode(Application::$Request['code']))
            {
                if ($userInfo = $this->GetMe($token))
                {
                    $this->StartSession(Application::$Request['code'], $token, $userInfo);
                    Application::$Security->UserLogin($this->CurrentUserInfo->login);
                }
                else
                {
                    $this->CloseSession();
                }
            }
            else
            {
                $this->CloseSession();
            }
        }
        elseif (isset(Application::$Session['oauthv2t']))
        {
            $this->RestartSession();
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
        return $this->ServiceURL . "/oauth2/auth" .
            "?response_type=code" .
            "&state=0.0.0.0" .
            "&redirect_uri=" . urlencode(Application::$Router->GetBaseUrl() . '/') .
            "&request_credentials=default" .
            "&client_id=" . $this->ServiceId .
            "&client_id=0-0-0-0-0" .
            "&scope=0-0-0-0-0%20" . $this->ServiceId;
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
        return $this->ServiceURL . "/oauth2/auth" .
            "?response_type=code" .
            "&state=0.0.0.0" .
            "&redirect_uri=" . urlencode(Application::$Router->GetBaseUrl() . '/') .
            "&request_credentials=required" .
            "&client_id=0-0-0-0-0" .
            "&scope=0-0-0-0-0%20" . $this->ServiceId;
    }

    /**
     * Getting logout url
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string Logot url
     */
    public function GetProfileUrl()
    {
        $result = '';
        if (!is_null($this->CurrentUserInfo))
        {
            $result = str_replace('api/rest/', '', $this->CurrentUserInfo->url);
        }
        return $result;
    }

    /**
     * Logout user with provider
     */
    public function LogoutUser()
    {
        $this->CloseSession();
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
        return !is_null($this->CurrentUserInfo);
    }

    /**
     * Getting User object
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return InfEra\Base\User\Models\BaseUser User object
     */
    public function GetUser($Login, $Password)
    {
            $User = NULL;
            if (!is_null($this->CurrentUserInfo))
            {
                $User = new User();
                $User->Provider = 'OAuth2';
                $User->GUID = $this->CurrentUserInfo->id;
                $User->Login = $this->CurrentUserInfo->login;
                $uname = explode(' ', $this->CurrentUserInfo->name);
                $User->FirstName = (isset($uname[0]) ? $uname[0] : '');
                $User->LastName = (isset($uname[1]) ? $uname[1] : '');
                // @TODO EMAIL SYNC NEW DATA FROM USER
                $User->Email = (isset($this->CurrentUserInfo->profile->email->email) ? $this->CurrentUserInfo->profile->email->email : "");
                $User->Avatar = $this->CurrentUserInfo->avatar->url;
            }

            return $User;
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

    private function GetTokenByCode($Code)
    {
        $result = NULL;
        if ($Code != "")
        {
            $options = array(
                'http' => array(
                    'method' => "POST",
                    'header' => "Authorization: Basic " . base64_encode($this->ServiceId . ":" . $this->ServiceSecrect) . "\r\n" .
                        "Accept: application/json\r\n" .
                        "Content-Type: application/x-www-form-urlencoded\r\n\r\n",
                    'content' => 'grant_type=authorization_code' .
                        '&code=' . $Code  .
                        '&redirect_uri=' . urlencode(Application::$Router->GetBaseUrl() . '/')
                )
            );

            $context = stream_context_create($options);

            $erl = error_reporting();
            //error_reporting(E_ERROR);
            if ($response = file_get_contents($this->ServiceURL . '/oauth2/token', false, $context))
            {
                $response = json_decode($response);
                $result = $response->access_token;
            }
            error_reporting($erl);
        }

        return $result;
    }

    private function GetMe($Token = "")
    {
        $result = NULL;

        if ($Token != "" || ($Token == "" && !is_null($this->CurrentToken)))
        {
            if ($Token == '')
            {
                $Token = $this->CurrentToken;
            }

            $options = array(
                'http' => array(
                    'method' => "GET",
                    'header' => "Authorization: Bearer " . $Token . "\r\n" .
                        "Accept: application/json\r\n"
                )
            );

            $context = stream_context_create($options);

            $erl = error_reporting();
            //error_reporting(E_ERROR);
            if ($response = file_get_contents($this->ServiceURL . '/users/me', false, $context)) {
                $result = json_decode($response);
            }
            error_reporting($erl);
        }

        return $result;
    }

    private function RestartSession()
    {
        if (isset(Application::$Session['oauthv2t']))
        {
            $this->CurrentToken = Application::$Session['oauthv2t'];
        }

        $this->CurrentUserInfo = $this->GetMe();
        if (is_null($this->CurrentUserInfo))
        {
            $this->CloseSession();
        }
    }

    private function StartSession($Code, $Token, $User)
    {
        $this->CurrentUserInfo = $User;

        Application::$Session->Set('oauthv2c', $Code);

        $this->CurrentToken = $Token;
        Application::$Session->Set('oauthv2t', $Token);
    }

    private function CloseSession()
    {
        Application::$Session->Remove('oauthv2c');
        Application::$Session->Remove('oauthv2t');
    }
}