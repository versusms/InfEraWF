<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra
 */
namespace InfEra
{   
    use InfEra\System\Mvc as Mvc;
    use InfEra\System\Localization as Localization;
    
    /**
     * Main application class.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra
     */
    class Application
    {   
        /**
         * Global Session object
         * @var \InfEra\System\Web\Session 
         */
        public static $Session;
        
        /**
         * Global Request object
         * @var \InfEra\System\Web\Request
         */
        public static $Request;
        
        /**
         * Global Response object
         * @var \InfEra\System\Web\Response
         */
        public static $Response;
        
        /**
         * Global Security object
         * @var \InfEra\System\Security\Security
         */
        public static $Security;
        
        /**
         * Global Security object
         * @var \InfEra\System\Entity\DBContext
         */
        public static $DBContext;
        
        /**
         * Global Security object
         * @var \InfEra\System\Configuration
         */
        public static $Configuration;
        
        /**
         * Router object.
         * @var InfEra\System\Mvc\Router
         */        
        public static $Router;
        
        /**
         * View object.
         * @var InfEra\System\Mvc\View
         */        
        public $View;
        
        /**
         * Constructor.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function __construct()
        {   
            Application::$Configuration = new System\Configuration();
            Application::$DBContext = new System\Entity\DBContext();
            Application::$Session = new System\Web\Session();
            Application::$Request = new System\Web\Request();                        
            Application::$Response = new System\Web\Response();
            Application::$Security = new System\Security\Security();
            Application::$Security->Init();
            Application::$Router = new Mvc\Router();
            $this->View = new Mvc\View();
            Localization::init();                                                                        
        }

        /**
         * Register routes for application.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
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
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public final function Start()
        {            
            $this->RegisterRoutes();
            $CurrentRoute = Application::$Router->GetRoutePath();                        
            if (($CurrentControllerClass = Application::$Router->GetFullControllerName($CurrentRoute)) != '')
            {
                $CurrentController = new $CurrentControllerClass();
                if (!($CurrentController instanceof \InfEra\System\Mvc\Controller))
                {
                    trigger_error
                    (
                        "[Application] Current controller class \"$CurrentControllerClass\" is not an instance of \"InfEra\System\Mvc\Controller\"",
                        E_USER_ERROR
                    );
                }
                
                $CurrentActionName = $CurrentRoute['action'];
                
                if (!is_null(Application::$Request["iefwfs"]))
                {
                    if (method_exists($CurrentController, $CurrentActionName . "_HttpPost"))
                    {
                        $CurrentActionName .= "_HttpPost";
                    }
                }
                
                if (method_exists($CurrentController, $CurrentActionName))
                {
                    $Reflection = new \ReflectionMethod($CurrentControllerClass, $CurrentActionName);
                    $ActionParams = $Reflection->getParameters();
                    
                    $ParamsToCall = array();
                    
                    foreach ($ActionParams as $param)
                    {
                        // if object
                        if ($paramClass = $param->getClass())
                        {                            
                            $tParam = new $paramClass->name;
                            $tProperties = $paramClass->getProperties();
                            foreach ($tProperties as $property)
                            {                                
                                switch (gettype($property->getValue($tParam)))
                                {
                                    case 'boolean' : 
                                    {
                                        if ($vir = Application::$Request->Get($property->name))
                                        {
                                            $property->setValue($tParam, (bool)$vir);
                                        }
                                        break;
                                    }
                                    case 'integer' : 
                                    {
                                        if ($vir = Application::$Request->Get($property->name))
                                        {
                                            $property->setValue($tParam, (int)$vir);
                                        }
                                        break;
                                    }
                                    case 'double' : 
                                    {
                                        if ($vir = Application::$Request->Get($property->name))
                                        {
                                            $property->setValue($tParam, (double)$vir);
                                        }
                                        break;
                                    }
                                    case 'string' : 
                                    {
                                        if ($vir = Application::$Request->Get($property->name))
                                        {
                                            $property->setValue($tParam, trim($vir));
                                        }
                                        break;
                                    }
                                    case 'array' : 
                                    {
                                        if ($vir = Application::$Request->Get($property->name))
                                        {
                                            $property->setValue($tParam, $vir);
                                        }
                                        break;
                                    }
                                    case 'object' :                                    
                                    {
                                        $RClass = new \ReflectionClass($property->getValue($tParam));
                                        switch ($RClass->name)
                                        {
                                            case 'DateTime' :
                                            {
                                                if ($vir = Application::$Request->Get($property->name))
                                                {
                                                    $property->setValue($tParam, new \DateTime($vir));
                                                }
                                                break;
                                            }
                                            default : {var_dump($RClass);}
                                        }                                        
                                    }
                                }                         
                            }
                            $ParamsToCall[$param->name] = $tParam;
                        }
                        // simple param
                        else
                        {
                            $ParamsToCall[$param->name] = ($pdv = Application::$Request->Get($param->name)) ? $pdv : $param->getDefaultValue();
                        }                        
                    }                    

                    $ActionResult = call_user_func_array
                    (
                        array($CurrentController, $CurrentActionName),
                        $ParamsToCall
                    );
                    if (!is_null($ActionResult))
                    {
                        
                        switch(get_class($ActionResult))
                        {
                            case 'InfEra\System\Mvc\ActionResult' :
                            {
                                $this->View->Display
                                (
                                    $CurrentRoute['controller'],
                                    $CurrentRoute['action'],
                                    $CurrentController->ViewBag,
                                    $ActionResult->GetDataForView()
                                );
                                break;
                            }
                            case 'InfEra\System\Mvc\RedirectResult' :
                            {
                                Application::$Response->SetHeader('Location', $ActionResult->GetUrl());
                                Application::$Response->SendHeaders();
                                die();
                                break;
                            }
                            case 'InfEra\System\Mvc\JSONResult' :
                            {
                                Application::$Response->SetHeader('Content-Type', 'application/json; charset=utf-8');
                                Application::$Response->SendHeaders();
                                Application::$Response->Write($ActionResult->GetDataForView());
                                die();
                                break;
                            }
                            case 'InfEra\System\Mvc\XMLResult' :
                            {
                                Application::$Response->SetHeader('Content-Type', 'text/xml; charset=utf-8');
                                Application::$Response->SendHeaders();
                                Application::$Response->Write($ActionResult->GetDataForView());
                                die();
                                break;
                            }
                            default : 
                            {                                    
                                trigger_error
                                (
                                    "[Application] Unknown result type \"" . get_class($ActionResult) . "\" for method \"$CurrentActionName\"",
                                    E_USER_ERROR
                                );
                                break;
                            }
                        }                        
                    }   
                    else
                    {
                        trigger_error
                        (
                            "[Application] Method \"$CurrentActionName\" did not return any result.",
                            E_USER_ERROR
                        );
                    }
                }
                else
                {
                    trigger_error
                    (
                        "[Application] Method \"" . $CurrentRoute['action'] . "\" was not found for current controller class \"$CurrentControllerClass\"",
                        E_USER_ERROR
                    );
                }
            }
                        
            /*var_dump("COOKIE", $_COOKIE);
            var_dump("ENV", $_ENV);
            var_dump("REQUEST", $_REQUEST);
            var_dump("SERVER", $_SERVER);
            */
        }
    }
}