<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 04.02.2016 23:13
 * @package InfEra\System\Mvc\View\Controls
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc\View\Controls;

use InfEra\WAFP\Application;

/**
 * Class MenuItem
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Mvc\View\Controls
 */
class MenuItem
{
    public $Url = "";
    public $Title = "";
    public $Icon = "";
    public $Badge = "";
    public $isActive = false;

    public $Controller = "";
    public $Action = "";

    public function __construct($Key, $Params = array())
    {
        if (isset($Params['title']))
        {
            $this->Title = $Params['title'];
            unset($Params['title']);
        }
        else
        {
            $this->Title = $Key;
        }

        if (isset($Params['icon'])) {
            $this->Icon = $Params['icon'];
            unset($Params['icon']);
        }

        if (isset($Params['badge'])) {
            $this->Badge = $Params['badge'];
            unset($Params['badge']);
        }

        if (isset($Params['controller'])) {
            $this->Controller = $Params['controller'];
            unset($Params['controller']);
        }
        else {
            $this->Controller = Application::$Router->CurrentRoute->GetControllerByDefault();
        }

        if (isset($Params['action'])) {
            $this->Action = $Params['action'];
            unset($Params['action']);
        }
        else {
            $this->Action = Application::$Router->CurrentRoute->GetActionByDefault();
        }

        $this->Url = Application::$Router->CreateUrl(array_merge(
            array(
                'controller' => $this->Controller,
                'action' => $this->Action
            ),
            $Params
        ));

        $current = Application::$Router->GetRouteInfo();
        $this->isActive = ($this->Controller == $current['controller'] && $this->Action == $current['action']);

        // @TODO Security Check Access
    }

    public function isActive()
    {
        return $this->isActive;
    }
}