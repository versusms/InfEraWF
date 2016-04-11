<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Mvc
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Web\Request;

/**
 * Router object
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
    public $CurrentRoute = null;

    /**
     * List of registereg routes.
     * @var array
     */
    private $Routes = array();

    /**
     * Mapping new route for application.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $RouteName Name of new Route.
     * @param string $RouteDescription Description string.
     * @param string $RouteDefaults [optional] Description of default settings for route.
     */
    public function MapRoute($RouteName, $RouteDescription, $RouteDefaults = array())
    {
        if (!isset($this->Routes[$RouteName])) {
            $this->Routes[$RouteName] = new Route($RouteName, $RouteDescription, $RouteDefaults);
        } else {
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
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return array Controller/Action/Params info. If Route not found - NULL.
     */
    public function GetRouteInfo()
    {
        $result = null;
        $request = rtrim(trim(Application::$Request['url'], "/"));

        if (count($this->Routes) > 0) {
            foreach ($this->Routes as $route) {
                if ($route->Match($request)) {
                    $this->CurrentRoute = $route;
                    break;
                }
            }

            if (!is_null($this->CurrentRoute)) {
                $result = $this->CurrentRoute->GetInvokeInfo($request);
            } else {
                // @TODO To Application, redirect to 404
                trigger_error("[Router] No routes found for current request", E_USER_ERROR);
            }
        } else {
            trigger_error("[Router] No routes registered yet", E_USER_ERROR);
        }

        return $result;
    }

    /**
     * Getting full controller's class name.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param array $RequestInfo
     *
     * @return string Class of controller
     */
    public function GetFullControllerName($RequestInfo)
    {
        $result = "";

        if (isset($RequestInfo['controller'])) {
            $Package = (array_key_exists('package', $RequestInfo)) ? $RequestInfo['package'] : $RequestInfo['controller'];

            $pathInProject = str_replace("\\", "/", Application::$Configuration->ProjectPath . 'Src/' . Application::$Configuration->Namespace . '/' . $Package . '/Controllers/' . $RequestInfo['controller'] . 'Controller.class.php');
            $pathInFramework = str_replace("\\", "/", Application::$Configuration->FrameworkPath . 'Base/' . $Package . '/Controllers/' . $RequestInfo['controller'] . 'Controller.class.php');

            if (is_file($pathInProject)) {
                $result = Application::$Configuration->Namespace . '\\' . $Package . '\Controllers\\' . $RequestInfo['controller'] . 'Controller';
            } elseif (is_file($pathInFramework)) {
                $result = '\InfEra\Base\\' . $Package . '\Controllers\\' . $RequestInfo['controller'] . 'Controller';
            } else {
                trigger_error("[Router] Invalid request description: controller not found", E_USER_ERROR);
            }
        } else {
            trigger_error("[Router] Invalid request description: unknown controller", E_USER_ERROR);
        }

        return $result;
    }

    /**
     * Get base url of application.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param array $Params Parameters for link
     * @param string $RouteName The name of Route. On empty - uses current route.
     *
     * @return string Url-string
     */
    public function CreateUrl($Params = NULL, $RouteName = "")
    {
        if ($RouteName == "") {
            $RouteName = $this->CurrentRoute->Name;
        }
        if (!isset($this->Routes[$RouteName])) {
            trigger_error
            (
                "[Router] Router with name \"$RouteName\" was not found. Uses current Route.",
                E_USER_WARNING
            );
            $RouteName = $this->CurrentRoute->Name;
        }

        $Route = $this->Routes[$RouteName];

        //return $this->GetBaseUrl() . $Route->CreateUrl($Params);;
        return $Route->CreateUrl($Params);;
    }
}