<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 10.02.2016 22:54
 * @package InfEra\WAFP\System
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Symlink;

/**
 * Class SymlinksCollection
 * Collection of symlinks
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\WAFP\System
 */
class SymlinksCollection extends Collections\Dictionary
{
    /**
     * SymlinksCollection constructor.
     */
    public function __construct() {
        if (array_key_exists('Symlinks', Application::$Configuration)
            && count(Application::$Configuration['Symlinks']) > 0)
        {
            foreach (Application::$Configuration['Symlinks'] as $key => $action)
            {
                $params = explode('.', $action);
                if (count($params) == 2)
                {
                    $params = array(
                        'package' => $params[0],
                        'controller' => $params[0],
                        'action' => $params[1]
                    );
                }
                else
                {
                    $params = array(
                        'package' => $params[0],
                        'controller' => $params[1],
                        'action' => $params[2]
                    );
                }

                $FullContollerName = Application::$Router->GetFullControllerName($params);

                if (Application::$Security->ValidateUserAccess($FullContollerName . "." . $params['action']))
                {
                    $this->Add($key, new Symlink($action));
                }
            }
        }
    }

    /**
     * Collect data from symlinks
     * 
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     */
    public function CollectData()
    {
        foreach ($this as $symlink)
        {
            $symlink->Execute();
        }
    }
}