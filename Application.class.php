<?php
/**
 * Web Application Framework for PHP
 * Created by InfEra Solutions.
 * @date 04.02.2015 18:08
 * @version 1.5
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra\WAFP
 */
declare(strict_types = 1);

namespace InfEra\WAFP;

use InfEra\WAFP\System\Collections\Dictionary;
use InfEra\WAFP\System\Mvc\Controllers\{ApiController, Controller};
use InfEra\WAFP\System\Localization;
use InfEra\WAFP\System\Mvc;
use InfEra\WAFP\System\Reflection\DocComments;
use InfEra\WAFP\System\Symlinks;

/**
 * Main application class.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP
 */
class Application
{
    /**
     * Global Session object
     * @var \InfEra\WAFP\System\Web\Session
     */
    public static $Session;

    /**
     * Global Request object
     * @var \InfEra\WAFP\System\Web\Request
     */
    public static $Request;

    /**
     * Global Response object
     * @var \InfEra\WAFP\System\Web\Response
     */
    public static $Response;

    /**
     * Global Security object
     * @var \InfEra\WAFP\System\Security\Security
     */
    public static $Security;

    /**
     * Global Security object
     * @var \InfEra\WAFP\System\Entity\DBContext
     */
    public static $DBContext;

    /**
     * Global Security object
     * @var \InfEra\WAFP\System\Configuration
     */
    public static $Configuration;

    /**
     * Router object.
     * @var \InfEra\WAFP\System\Mvc\Router
     */
    public static $Router;

    /**
     * Application settings object.
     * @var \InfEra\WAFP\System\Settings
     */
    public static $Settings;

    /**
     * Cache in memory object.
     * @var \InfEra\WAFP\System\Collections\Dictionary
     */
    // @TODO InMemory Cache
    // @TODO MEMCACHE
    // @TODO File Cache
    public static $Cache;

    /**
     * Collection of Symlinks for Application
     * @var \InfEra\WAFP\System\Symlinks
     */
    public static $Symlinks;

    /**
     * View object.
     * @var \InfEra\WAFP\System\Mvc\View
     */
    public $View;

    /**
     * Constructor.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function __construct()
    {
        date_default_timezone_set('Etc/GMT');
        Application::$Cache = new Dictionary();
        Application::$Configuration = new System\Configuration();
        Application::$DBContext = new System\Entity\DBContext();
        Application::$Session = new System\Web\Session();
        Application::$Request = new System\Web\Request();
        Application::$Response = new System\Web\Response();
        Application::$Security = new System\Security\Security();
        Application::$Router = new Mvc\Router();
        Application::$Settings = new System\Settings();
        Application::$Symlinks = new System\SymlinksCollection();
        $this->View = new Mvc\View();
        Application::$Security->Init();
        Localization::init();
    }

    /**
     * Register routes for application.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    protected function RegisterRoutes()
    {
        Application::$Router->MapRoute
        (
            'Default',
            '/{controller}/{action}/',
            array('controller' => 'Pages', 'action' => 'Index')
        );
    }

    /**
     * Start application.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public final function Start()
    {
        $this->RegisterRoutes();
        $RouteInfo = Application::$Router->GetRouteInfo();
        Application::$Request->UpdateDataWithRoute();

        if (($CurrentControllerClass = Application::$Router->GetFullControllerName($RouteInfo)) != '') {
            $CurrentController = new $CurrentControllerClass();
            if (!($CurrentController instanceof Controller)) {
                trigger_error
                (
                    "[Application] Current controller class \"$CurrentControllerClass\" is not an instance of \"InfEra\WAFP\System\Mvc\Controllers\Controller\"",
                    E_USER_ERROR
                );
            }

            $CurrentActionName = $RouteInfo['action'];

            if ($CurrentController instanceof ApiController) {
                switch (Application::$Request->Type) {
                    case System\Web\Request::TYPE_GET : {
                        if ($CurrentActionName == '') {
                            $CurrentActionName = "Get";
                        }
                        break;
                    }
                    case System\Web\Request::TYPE_PUT : {
                        if ($CurrentActionName == '') {
                            $CurrentActionName = "Put";
                        }
                        break;
                    }
                    // except file uploading
                    case System\Web\Request::TYPE_POST : {
                        if ($CurrentActionName == '') {
                            $CurrentActionName = "Post";
                        }
                        break;
                    }
                    case System\Web\Request::TYPE_POSTFILES : {
                        // try to call original method
                        break;
                    }
                    case System\Web\Request::TYPE_DELETE : {
                        if ($CurrentActionName == '') {
                            $CurrentActionName = "Delete";
                        }
                        break;
                    }
                    default : {
                        throw new \InfEra\WAFP\System\Mvc\Controllers\ApiControllerException("Uknown request type \"" . Application::$Request->Type . "\"for API-controller $CurrentControllerClass");
                        break;
                    }
                }
            } else {
                if (Application::$Request->Type == System\Web\Request::TYPE_GET && method_exists($CurrentController, $CurrentActionName . "_HttpGet")) {
                    $CurrentActionName .= "_HttpGet";
                }
                if (Application::$Request->Type == System\Web\Request::TYPE_PUT && method_exists($CurrentController, $CurrentActionName . "_HttpPut")) {
                    $CurrentActionName .= "_HttpPut";
                } elseif ((Application::$Request->Type == System\Web\Request::TYPE_POST || Application::$Request->Type == System\Web\Request::TYPE_POSTFILES) && method_exists($CurrentController, $CurrentActionName . "_HttpPost")) {
                    $CurrentActionName .= "_HttpPost";
                }
                if (Application::$Request->Type == System\Web\Request::TYPE_DELETE && method_exists($CurrentController, $CurrentActionName . "_HttpDelete")) {
                    $CurrentActionName .= "_HttpDelete";
                }
            }

            if ($CurrentActionName != '') {
                if (method_exists($CurrentController, $CurrentActionName)) {
                    $ControllerDescription = new \ReflectionClass($CurrentControllerClass);
                    $ActionDescription = new \ReflectionMethod($CurrentControllerClass, $CurrentActionName);
                    $ActionParams = $ActionDescription->getParameters();

                    if (Application::$Security->ValidateUserAccess($CurrentControllerClass . '.' . $CurrentActionName)) {

                        $ParamsToCall = array();

                        foreach ($ActionParams as $param) {
                            // if object
                            if ($paramClass = $param->getClass()) {
                                $tParam = new $paramClass->name;
                                $tProperties = $paramClass->getProperties();
                                foreach ($tProperties as $property) {
                                    switch (gettype($property->getValue($tParam))) {
                                        case 'boolean' : {
                                            if ($vir = Application::$Request->Get($property->name)) {
                                                $property->setValue($tParam, (bool)$vir);
                                            }
                                            break;
                                        }
                                        case 'integer' : {
                                            if ($vir = Application::$Request->Get($property->name)) {
                                                $property->setValue($tParam, (int)$vir);
                                            }
                                            break;
                                        }
                                        case 'double' : {
                                            if ($vir = Application::$Request->Get($property->name)) {
                                                $property->setValue($tParam, (double)$vir);
                                            }
                                            break;
                                        }
                                        case 'string' : {
                                            if ($vir = Application::$Request->Get($property->name)) {
                                                $property->setValue($tParam, trim($vir));
                                            }
                                            break;
                                        }
                                        case 'array' : {
                                            if ($vir = Application::$Request->Get($property->name)) {
                                                $property->setValue($tParam, $vir);
                                            }
                                            break;
                                        }
                                        case 'object' : {
                                            $RClass = new \ReflectionClass($property->getValue($tParam));
                                            switch ($RClass->name) {
                                                case 'DateTime' : {
                                                    if ($vir = Application::$Request->Get($property->name)) {
                                                        $property->setValue($tParam, new \DateTime($vir));
                                                    }
                                                    break;
                                                }
                                                default : {
                                                    var_dump($RClass);
                                                }
                                            }
                                        }
                                    }
                                }
                                $ParamsToCall[$param->name] = $tParam;
                            } // simple param
                            else {
                                // @TODO Case unsensitive
                                $ParamsToCall[$param->name] = ($pdv = Application::$Request->Get($param->name)) ? $pdv : $param->getDefaultValue();
                            }
                        }
                        $ActionResult = call_user_func_array
                        (
                            array($CurrentController, $CurrentActionName),
                            $ParamsToCall
                        );
                        $this::$Symlinks->CollectData();
                        if (!is_null($ActionResult)) {

                            switch (get_class($ActionResult)) {
                                case 'InfEra\WAFP\System\Mvc\Results\ActionResult' : {
                                    if (method_exists($CurrentController, 'GetControllerMenu')) {
                                        $this->View->AddMenu('controllermenu', call_user_func
                                        (
                                            array($CurrentController, 'GetControllerMenu')
                                        ));
                                    }
                                    $this->View->Display
                                    (
                                        $RouteInfo['package'],
                                        $RouteInfo['controller'],
                                        $RouteInfo['action'],
                                        $CurrentController->ViewBag,
                                        $ActionResult->GetDataForResponse()
                                    );
                                    break;
                                }
                                case 'InfEra\WAFP\System\Mvc\Results\RedirectResult' : {
                                    Application::$Response->SetHeader('Location', $ActionResult->GetUrl());
                                    Application::$Response->SendHeaders();
                                    die();
                                    break;
                                }
                                case 'InfEra\WAFP\System\Mvc\Results\JSONResult' : {
                                    Application::$Response->SetHeader('Content-Type', 'application/json; charset=utf-8');
                                    Application::$Response->SendHeaders();
                                    Application::$Response->Write($ActionResult->GetDataForResponse());
                                    die();
                                    break;
                                }
                                case 'InfEra\WAFP\System\Mvc\Results\XMLResult' : {
                                    Application::$Response->SetHeader('Content-Type', 'text/xml; charset=utf-8');
                                    Application::$Response->SendHeaders();
                                    Application::$Response->Write($ActionResult->GetDataForResponse());
                                    die();
                                    break;
                                }
                                default : {
                                    trigger_error
                                    (
                                        "[Application] Unknown result type \"" . get_class($ActionResult) . "\" for method \"$CurrentActionName\"",
                                        E_USER_ERROR
                                    );
                                    break;
                                }
                            }
                        } else {
                            trigger_error
                            (
                                "[Application] Method \"$CurrentActionName\" did not return any result.",
                                E_USER_ERROR
                            );
                        }
                    } else {
                        echo "403";
                        die();
                    }
                } else {
                    trigger_error
                    (
                        "[Application] Method \"" . $CurrentActionName . "\" was not found for current controller class \"$CurrentControllerClass\"",
                        E_USER_ERROR
                    );
                }
            } else {
                trigger_error
                (
                    "[Application] No method to call for controller \"$CurrentControllerClass\".",
                    E_USER_ERROR
                );
            }
        }
    }
}