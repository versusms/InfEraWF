<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 10.04.2016 22:04
 * @package InfEra\WAFP\System\Mvc
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc;

use InfEra\WAFP\System\Web\Request;
/**
 * Route object
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Mvc
 */
class Route
{
    public $Name = "";
    private $Segments = array();
    private $SegmentsMinCount = 0;
    private $Defaults = array();
    private $Pattern = "";

    public function __construct($RouteName, $RoutePattern, $RouteDefaults)
    {
        //@TODO Optional parameter is not allowed in the middle
        //@TODO Wrong route without controller or action
        $this->Name = $RouteName;
        $this->Defaults = $RouteDefaults;
        $this->Pattern = $RoutePattern;

        $droute = trim($RoutePattern, '/');
        $droute = rtrim($droute, '/');
        $droute = explode('/', $droute);
        foreach ($droute as $segment) {
            $sstatic = !(strpos($segment, '{') === 0 && strpos($segment, '}') === strlen($segment) - 1);
            $segment = str_replace(array('{', '}'), '', $segment);
            $sdefault = $sstatic ? $segment : (array_key_exists($segment, $RouteDefaults) && $RouteDefaults[$segment] !== Request::URL_PARAMETER_OPTIONAL ? $RouteDefaults[$segment] : Request::URL_PARAMETER_OPTIONAL);
            $soptional = $sstatic ? false : (array_key_exists($segment, $RouteDefaults) && $RouteDefaults[$segment] === Request::URL_PARAMETER_OPTIONAL);
            $this->Segments[] = new RouteSegment($segment, $sstatic, $soptional);
            if (!$soptional && !array_key_exists($segment, $RouteDefaults)) {
                $this->SegmentsMinCount++;
            }
        }
    }

    /**
     * Matching route with request
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Request
     * @return bool
     */
    public function Match($Request)
    {
        $result = true;

        if ($Request != "") {
            $Request = explode('/', $Request);
        } else {
            $Request = array();
        }

        if (count($Request) >= $this->SegmentsMinCount && count($Request) <= count($this->Segments)) {
            foreach ($this->Segments as $key => $segment) {
                if ((!$segment->Optional && !array_key_exists($key, $Request) && array_key_exists($key, $this->Defaults)) ||
                    ($segment->Static && $Request[$key] != $segment->Value)
                ) {
                    $result = false;
                    break;
                }
            }
        } else {
            $result = false;
        }

        return $result;
    }

    public function GetInvokeInfo($Request)
    {
        $vars = $this->GetVariables($Request);

        return array(
            'package' => array_key_exists('package', $vars) ? $vars['package'] : '',
            'controller' => array_key_exists('controller', $vars) ? $vars['controller'] : '',
            'action' => array_key_exists('action', $vars) ? $vars['action'] : ''
        );
    }

    public function GetParameters($Request)
    {
        $vars = $this->GetVariables($Request);
        foreach ($vars as $key => $value) {
            if ($key == 'controller' || $key == 'action') {
                unset($vars[$key]);
            }
        }
        return $vars;
    }

    public function GetControllerByDefault()
    {
        return (isset($this->Defaults['controller']) ? $this->Defaults['controller'] : null);
    }

    public function GetActionByDefault()
    {
        return (isset($this->Defaults['action']) ? $this->Defaults['action'] : null);
    }

    public function CreateUrl($Params)
    {
        $UrlParams = $Params;
        $result = '/';

        $segmentsValues = array();
        $rsegments = array_reverse($this->Segments);

        $stopSkip = false;

        foreach ($rsegments as $segment) {
            if (isset($UrlParams[$segment->Value]) && $UrlParams[$segment->Value] != ''
                && (!isset($this->Defaults[$segment->Value]) ||
                    $this->Defaults[$segment->Value] != $UrlParams[$segment->Value]
                )
            ) {
                $segmentsValues[] = $UrlParams[$segment->Value];
                $stopSkip = true;
            } elseif (!$segment->Optional && isset($this->Defaults[$segment->Value])) {
                if ($stopSkip) {
                    $segmentsValues[] = $this->Defaults[$segment->Value];
                }
            } elseif (!$segment->Optional) {
                trigger_error
                (
                    "[Router] Not all parameters in route \"$this->Name\" has values. Result \"$result\" might be incorrect!",
                    E_USER_WARNING
                );
            }

            if (isset($UrlParams[$segment->Value])) {
                unset($UrlParams[$segment->Value]);
            }
        }

        $segmentsValues = array_reverse($segmentsValues);
        $result .= count($segmentsValues) > 0 ? strtolower(implode('/', $segmentsValues)) . '/' : '';

        if (count($UrlParams) > 0) {
            $first = true;
            foreach ($UrlParams as $getParameterName => $getParameterValue) {
                $result .= ($first ? '?' : '&') . strtolower($getParameterName) . "=$getParameterValue";
                if ($first) {
                    $first = false;
                }
            }
        }

        return $result;
    }

    /**
     * Get mapped variables from request
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Request
     * @return array
     */
    private function GetVariables($Request)
    {
        $result = array();

        if ($Request != "") {
            $Request = explode('/', $Request);
        } else {
            $Request = array();
        }

        $osgments = array();

        foreach ($this->Segments as $key => $segment) {
            if (!$segment->Static && array_key_exists($key, $Request)) {
                if ($segment->Value == 'controller') {
                    $exploded = explode('.', $Request[$key]);
                    foreach ($exploded as &$value) {
                        $value = ucfirst($value);
                    }
                    $result['package'] = $exploded[0];
                    $Request[$key] = count($exploded) > 1 ? $exploded[1] : $Request[$key];
                }
                $result[$segment->Value] = ($segment->Value == 'controller' || $segment->Value == 'action') ? ucfirst(strtolower($Request[$key])) : $Request[$key];
            }
            if ($segment->Optional) {
                $osgments[] = $segment->Value;
            }
        }
        foreach ($this->Defaults as $key => $value) {
            if (!array_key_exists($key, $result) && !in_array($key, $osgments)) {
                $result[$key] = ($key == 'controller' || $key == 'action') ? ucfirst(strtolower($value)) : $value;
            }
        }

        if (!array_key_exists('package', $result)) {
            $result['package'] = $result['controller'];
        }

        return $result;
    }
}