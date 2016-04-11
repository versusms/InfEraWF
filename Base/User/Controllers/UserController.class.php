<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra\WAFP[Base]
 * @subpackage User[Controllers]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\Base\User\Controllers;

use InfEra\WAFP\System\Localization as L;
use InfEra\WAFP\Application;
use InfEra\WAFP\System\Mvc\ActionResult;
use InfEra\WAFP\System\Mvc\Controllers\Controller;
use InfEra\WAFP\System\Mvc\Results\JSONResult;
use InfEra\WAFP\System\Security\Security;
use InfEra\WAFP\System\Net\Mail;
use InfEra\WAFP\Base\User\Models;

/**
 * User's controller
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP[Base]
 * @subpackage User[Controllers]
 */
class UserController extends Controller
{
    /**
     * Profile controller
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpGet
     *
     * @return ActionResult Profile data
     */
    public function Index() : ActionResult
    {
        $this->ViewBag['Title'] = L::__('Profile');
        if (Application::$Security->IsUserLogged) {
            return $this->View(Application::$Security->CurrentUser);
        } else {
            return $this->RedirectTo(array
            (
                'controller' => 'User',
                'action' => 'Login',
            ));
        }
    }

    /**
     * Save profile
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpPost
     */
    public function Index_HttpPost()
    {
        $FormData = Application::$Request;

        $User = Application::$Security->CurrentUser;
        unset($User->Password);
        if ($User->Provider == 'Base') {
            $User->FirstName = $FormData['FirstName'];
            $User->LastName = $FormData['LastName'];
            $User->Birthday = new \DateTime($FormData['Birthday']);
            $User->Location = $FormData['Location'];
            $User->Gender = $FormData['Gender'];
            $User->Timezone = (int)$FormData['Timezone'];

            if (isset($FormData['Avatar']) && $FormData['Avatar']['size'] > 0) {
                if ($User->Avatar != "" && strpos($User->Avatar, 'http') === false) {
                    unlink(Application::$Configuration->ProjectPath . $User->Avatar);
                }
                // @TODO Uploader Class
                // @TODO Thumbnails
                $NewFileExt = ".file";
                switch ($FormData['Avatar']['type']) {
                    case 'image/jpeg' : {
                        $NewFileExt = ".jpg";
                    }
                }

                if (move_uploaded_file(
                    $FormData['Avatar']['tmp_name'],
                    Application::$Configuration->ProjectPath . 'Content/Images/Avatars/' . md5($User->Login) . $NewFileExt)) {
                    $User->Avatar = '/Content/Images/Avatars/' . md5($User->Login) . $NewFileExt;
                }
            }
        }

        $User->Locale = $FormData['Locale'];
        Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\User')->Store($User);

        return $this->RedirectTo(array
        (
            'controller' => 'User',
            'action' => 'Index',
        ));
    }

    /**
     * Registration form
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpGet
     *
     * @return ActionResult
     */
    public function Register() : ActionResult
    {
        $this->ViewBag['Title'] = L::__('Registration');
        $NewUserData = new Models\User();
        return $this->View($NewUserData);
    }

    /**
     * Registration form
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpPost
     *
     * @param Models\User $NewUserData New user data
     *
     * @return ActionResult
     */
    public function Register_HttpPost(Models\User $NewUserData) : ActionResult
    {
        if ($NewUserData->Login == "" ||
            $NewUserData->Password == "" ||
            $NewUserData->Email == "" ||
            $NewUserData->Birthday == ""
        ) {
            $this->ViewBag['Errors'] = L::__('All fields are required');
        } else {
            if ($NewUserData->Password != Application::$Request['PasswordConfirm']) {
                $this->ViewBag['Errors'] = L::__('Passwords does not match');
            } else {
                $dbs = Application::$DBContext->GetDbSet('\InfEra\Base\User\Models\User')
                    ->Where("Login = '$NewUserData->Login' OR
                                 Email = '$NewUserData->Email'");
                $users = $dbs->Select();
                if (count($users)) {
                    $this->ViewBag['Errors'] = L::__('Selected Login or Email already in use.');
                } else {
                    Application::$Security->UserRegister($NewUserData);

                    $mail = new Mail();
                    $mail->From = "robot@sonet.ru";
                    $mail->AddAddress($NewUserData->Email);
                    $mail->Subject = L::__('Registration');
                    $mail->Body = "Hellow, " . $NewUserData->Login . PHP_EOL .
                        "Thanks for registration on " . Application::$Router->GetBaseUrl();
                    $mail->Send();

                    Application::$Security->UserLogin($NewUserData->Login, $NewUserData->Password);
                    return $this->RedirectTo(array('controller' => 'User'));
                }
            }
        }

        $this->ViewBag['Title'] = L::__('Registration');
        return $this->View($NewUserData);
    }

    /**
     * Login form
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpGet
     *
     * @return ActionResult
     */
    public function Login() : ActionResult
    {
        $this->ViewBag['Title'] = L::__('Autorization');
        $this->ViewBag['LoginLinks'] = Application::$Security->GetLoginUrls();
        $this->ViewBag['RestorePasswordLink'] = Application::$Router->CreateUrl(array(
            'controller' => 'User',
            'action' => 'RestorePassword',
        ));
        return $this->View();
    }

    /**
     * Registration form
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpPost
     *
     * @param string $UserLogin User's login
     * @param string $UserPassword User's password
     * @param Boolean $Remember Remember login
     *
     * @return ActionResult
     */
    public function Login_HttpPost(string $UserLogin, string $UserPassword, string $Remember = '0') : ActionResult
    {
        //var_dump($UserLogin, $UserPassword, $Remember);
        $errors = '';

        if ($UserLogin == "" ||
            $UserPassword == ""
        ) {
            $errors = L::__('All fields are required');
        } else {
            $State = Application::$Security->UserLogin($UserLogin, $UserPassword, $Remember);
            switch ($State) {
                case Security::USER_NOTFOUND : {
                    $errors = L::__('User not found');
                    break;
                }
                case Security::USER_DISABLED : {
                    $errors = L::__('Your account is disabled');
                    break;
                }
                case Security::USER_INVALIDCREDENTIALS : {
                    $errors = L::__(Application::$Security->LastUserState);
                    break;
                }
            }
        }

        if (Application::$Request->IsAsync()) {
            $JSONResult = new JSONResult(array(
                'successful' => $errors == '',
                'message' => $errors
            ));
            return $JSONResult;
        } else {
            $this->ViewBag['Title'] = L::__('Autorization');
            $this->ViewBag['LoginLinks'] = Application::$Security->GetLoginUrls();

            if ($errors != '') {
                $this->ViewBag['Errors'] = $errors;
                return $this->View(array('UserLogin' => $UserLogin));
            }
        }
    }

    /**
     * Login with non-base provider
     *
     * @param string $p Provider name
     *
     * @return ActionResult
     */
    public function LoginVia(string $p) : ActionResult
    {
        $link = "";
        if ($p != "") {
            $link = Application::$Security->GetLoginUrlViaProvider($p);
        }
        return $this->Redirect(($link != "") ? $link : "/");
    }

    /**
     * Restore password form
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpGet
     *
     * @return ActionResult
     */
    public function RestorePassword() : ActionResult
    {
        $this->ViewBag['Title'] = L::__('Restore password');
        return $this->View();
    }

    /**
     * Restore password form
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @method HttpPost
     *
     * @return ActionResult
     */
    public function RestorePassword_HttpPost(string $Email = "") : ActionResult
    {
        if ($Email == "") {
            $this->ViewBag['Errors'] = L::__('Email field is required');
        } else {
            if ($user = Application::$Security->GetUserByEmail($Email)) {
                $mail = new Mail();
                $mail->From = "robot@wafp.ru";
                $mail->AddAddress($user->Email);
                $mail->Subject = L::__('Restore password');
                $key = serialize(array
                (
                    'u' => $user->Login,
                    'm' => $user->Email
                ));
                $key = base64_encode($key);
                $mail->Body = "Follow this link " . PHP_EOL .
                    Application::$Router->CreateUrl(array(
                        'controller' => 'User',
                        'action' => 'ResetPassword'
                    )) .
                    "?p=" . $key . PHP_EOL .
                    "to restore password";
                $mail->Send();
                $this->ViewBag['Messages'] = L::__('Check your email');
            } else {
                $this->ViewBag['Errors'] = L::__('Email was not found');
            }
        }
        $this->ViewBag['Title'] = L::__('Restore password');
        return $this->View();
    }

    /**
     * @method HttpGet
     */
    public function ResetPassword(string $p = "") : ActionResult
    {
        $result = array
        (
            'Email' => '',
            'Token' => '',
        );
        if ($p != "") {
            try {
                $pp = base64_decode($p);
                $pp = unserialize($pp);
                if (isset($pp['u']) && isset($pp['m'])) {
                    $result = array
                    (
                        'Email' => $pp['m'],
                        'Token' => $p,
                    );
                }
            } catch (Exception $e) {
                $this->ViewBag['Errors'] = L::__('Invalid access token. Please try again!');
                $this->ViewBag['DisplayForm'] = false;
            }
        } else {
            $this->ViewBag['Errors'] = L::__('Invalid access token. Please try again!');
            $this->ViewBag['DisplayForm'] = false;
        }
        $this->ViewBag['Title'] = L::__('Restore password');
        return $this->View($result);
    }

    /**
     * @method HttpPost
     */
    public function ResetPassword_HttpPost(string $Email = "", string $Token = "", string $Password = "", string $PasswordConfirm = "") : ActionResult
    {
        $result = array
        (
            'Email' => $Email,
            'Token' => $Token,
        );
        if ($Email == '' || $Token == '') {
            $this->ViewBag['Errors'] = L::__('Params error. Please try again!');
            $this->ViewBag['DisplayForm'] = false;
        } elseif ($Password == '' || $PasswordConfirm == '') {
            $this->ViewBag['Errors'] = L::__('All fields are required.');
        } elseif ($Password != $PasswordConfirm) {
            $this->ViewBag['Errors'] = L::__('Passwords does not match.');
        } else {
            $user = Application::$Security->GetUserByEmail($Email);
            if ($user) {
                $user->Password = Application::$Security->Hash($Password);
                Application::$DBContext->GetDbSet('\InfEra\Base\User\Models\User')->Store($user);
                $this->ViewBag['Messages'] = L::__('New passwords is set.');
                $this->ViewBag['DisplayForm'] = false;
            } else {
                $this->ViewBag['Errors'] = L::__('Params error. Please try again!');
                $this->ViewBag['DisplayForm'] = false;
            }
        }

        $this->ViewBag['Title'] = L::__('Restore password');
        return $this->View($result);
    }

    /**
     * Logout user
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return ActionResult
     */
    public function Logout() : ActionResult
    {
        if ($url = Application::$Security->UserLogout()) {
            return $this->Redirect($url);
        } else {
            return $this->RedirectTo(array
            (
                'controller' => 'Pages',
                'action' => 'Index'
            ));
        }
    }
}