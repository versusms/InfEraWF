<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 04.02.2016 23:11
 * @package InfEra\System\Mvc\View\Controls
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc\View\Controls;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Collections\Dictionary;

/**
 * Class Menu
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Mvc\View\Controls
 */
class Menu extends Dictionary
{
    public function ValidateMenuAccess()
    {
        foreach($this as $MenuItemIndex => $MenuItem)
        {
            $exploded = explode('.', $MenuItem->Controller);
            $params = array(
                'package' => $exploded[0],
                'controller' => count($exploded) > 1 ? $exploded[1] : $exploded[0],
                'action' => $MenuItem->Action
            );
            
            $FullContollerName = Application::$Router->GetFullControllerName($params);

            if (!Application::$Security->ValidateUserAccess($FullContollerName . "." . $params['action']))
            {
                unset($this[$MenuItemIndex]);
            }
        }
    }
}