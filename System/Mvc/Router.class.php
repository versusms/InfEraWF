<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Mvc
 */
namespace InfEra\System\Mvc
{  
    use \InfEra\Application as Application;
    /**
    * Router object
    *
    * @author     Alexander A. Popov <versusms@gmail.com>
    * @version    1.0
    * @package    InfEra[System]
    * @subpackage Mvc
    */
    class Router
    {
        /**
         * Current route object.
         * @var Route
         */
        private $CurrentRoute = null;
                
        /**
         * List of registereg routes.
         * @var array
         */
        private $Routes = array();       
        
        /**
         * Mapping new route for application.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $RouteName Name of new Route.
         * @param string $RouteDescription Description string.
         * @param string $RouteDefaults [optional] Description of default settings for route.
         */                
        public function MapRoute($RouteName, $RouteDescription, $RouteDefaults = array())
        {
            if (!isset($this->Routes[$RouteName]))
            {
                $this->Routes[$RouteName] = new Route($RouteName, $RouteDescription, $RouteDefaults);
            }
            else
            {
                trigger_error
                (
                    "[Router] Route \"$RouteName\" already registered",
                    E_USER_WARNING
                );
            }
        }
        
        /**
         * Parsing request string and getting info about current controller and action.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return array Controller/Action/Params info. If Route not found - NULL.
         */
        public function GetRoutePath()
        {
            // @TODO Work with diffenet routes
            $result = null;            
            $request = trim(Application::$Request['url'], "/");
            $request = rtrim($request);
            if (count($this->Routes) > 0)
            {
                if ($request != "")
                {
                    $request = explode('/', $request);                                
                }             
                else
                {
                    $request = array();
                }
                
                $this->CurrentRoute = $this->Routes['Default'];
                
                $droute = trim($this->Routes['Default']->Description, '/');                
                $droute = rtrim($droute, '/');                
                $droute = explode('/', $droute);                
                
                if (count($request) <= count($droute))
                {
                    foreach ($droute as $index => $segment)
                    {
                        $segment = str_replace(array('{','}'), '', $segment);

                        if (isset($request[$index]))
                        {
                            $result[$segment] = $request[$index];
                        }
                        else
                        {
                            if (isset($this->CurrentRoute->Defaults[$segment]))
                            {
                                $result[$segment] = $this->CurrentRoute->Defaults[$segment];
                            }
                            else
                            {
                                trigger_error
                                (
                                    "[Router] Invalid Route Description",
                                    E_USER_WARNING
                                );
                                break;
                            }
                        }
                    }
                    $this->CurrentRoute->Params = $result;
                }
                else
                {
                    trigger_error("[Router] No routes found for current request", E_USER_ERROR);
                }
            }
            else
            {
                trigger_error("[Router] No routes registered yet", E_USER_ERROR);
            }            
            
            return $result;
        }
        
        /**
         * Getting full controller's class name.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param array $RequestInfo
         * 
         * @return string Class of controller
         */
        public function GetFullControllerName($RequestInfo)
        {
            $result = "";
            
            if (isset($RequestInfo['controller']) && isset($RequestInfo['action']))
            {
                $pathInProject = Application::$Configuration->ProjectPath . 'Code/' . Application::$Configuration->Namespace . '/' . $RequestInfo['controller'] . '/Controllers/' . $RequestInfo['controller'] . 'Controller.class.php';
                $pathInFramework = Application::$Configuration->FrameworkPath . 'Base/' . $RequestInfo['controller'] . '/Controllers/' . $RequestInfo['controller'] . 'Controller.class.php';                
                if (is_file($pathInProject))
                {
                    $result = Application::$Configuration->Namespace . '\\' . $RequestInfo['controller'] . '\Controllers\\' . $RequestInfo['controller'] . 'Controller';
                }
                elseif (is_file($pathInFramework))
                {
                    $result = '\InfEra\Base\\' . $RequestInfo['controller'] . '\Controllers\\' . $RequestInfo['controller'] . 'Controller';
                }
                else
                {
                    trigger_error("[Router] Invalid request description: controller or action not found", E_USER_ERROR);
                }                
            }
            else
            {
                trigger_error("[Router] Invalid request description: unknown controller or action", E_USER_ERROR);
            }                        
            
            return $result;
        }
        
        /**
         * Get base url of application.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return string 
         */
        public function GetBaseUrl()
        {            
            // @TODO Server Object
            $protocol = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) ? "https" : "http") . '://';
            $host = $_SERVER['HTTP_HOST'];            

            // use port if non default
            $port =
                isset($parts['port']) &&
                (($protocol === 'http://' && $parts['port'] !== 80) ||
                ($protocol === 'https://' && $parts['port'] !== 443))
                ? ':' . $parts['port'] : '';

            // rebuild
            return $protocol . $host . $port;            
        }               
        
        /**
         * Create Url
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param array  $Params Parameters for link         
         * @param string $RouteName The name of Route. On empty - uses current route.
         * 
         * @return string Url-string
         */
        public function CreateUrl($Params = NULL, $RouteName = "")
        {            
            if ($RouteName == "")
            {
                $RouteName = $this->CurrentRoute->Name;
            }
            if (!isset($this->Routes[$RouteName]))
            {
                trigger_error
                (
                    "[Router] Router with name \"$RouteName\" was not found. Uses current Route.",
                    E_USER_WARNING
                );
                $RouteName = $this->CurrentRoute->Name;
            }
            
            $Route = $this->Routes[$RouteName];
            $RouteString = $Route->Description;
            // Apply link params
            if (is_array($Params))
            {
                foreach ($Params as $ParamName => $ParamValue)
                {
                    $RouteString = str_replace('{' . $ParamName . '}', $ParamValue, $RouteString);
                }
            }
            // Apply route defaults
            if (is_array($Route->Defaults) && count($Route->Defaults) > 0)
            {
                foreach ($Route->Defaults as $ParamName => $ParamValue)
                {
                    $RouteString = str_replace('{' . $ParamName . '}', $ParamValue, $RouteString);
                }
            }
            // Check if another parameters is not filled
            if (strpos($RouteString, '{') !== false || strpos($RouteString, '}') !== false)
            {
                trigger_error
                (
                    "[Router] Not all parameters in route \"$RouteName\" has values. Result \"$RouteString\" might be incorrect!",
                    E_USER_WARNING
                );
            }
            
            return $this->GetBaseUrl() . $RouteString;
        }
    }
    
    /**
    * Route object
    *
    * @author     Alexander A. Popov <versusms@gmail.com>
    * @version    1.0
    * @package    InfEra[System]
    * @subpackage Mvc
    */
    class Route
    {
        public $Name = "";
        public $Description = "";
        public $Defaults = array();
        public $Params = array();
        
        public function __construct($RouteName, $RouteDescription, $RouteDefaults)
        {
            $this->Name = $RouteName;
            $this->Description = $RouteDescription;
            $this->Defaults = $RouteDefaults;
        }
    }
}