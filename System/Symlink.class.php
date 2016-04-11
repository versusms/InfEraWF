<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 04.04.2016 18:08
 * @package InfEra\WAFP\System
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System;

use InfEra\WAFP\Application;

/**
 * Class Symlink
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\WAFP\System
 */
class Symlink
{
    /**
     * Link to symlink
     * @var string
     */
    public $Value = null;

    /**
     * Controller for symlink
     * @var string
     */
    private $Controller;

    /**
     * Action for symlink
     * @var string
     */
    private $Action;

    /**
     * Symlink constructor.
     * @param string $Reference
     */
    public function __construct(string $Reference)
    {
        // @TODO Check Reference
        $Reference = explode('.', $Reference);
        $this->Controller = Application::$Router->GetFullControllerName(array(
            'controller' => $Reference[0]
        ));
        $this->Action = $Reference[1];
    }

    /**
     * Execute symlink
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     */
    public function Execute()
    {
        //@TODO Check access
        //@TODO Check availability
        if (method_exists($this->Controller, $this->Action)) {
            $Reflection = new \ReflectionMethod($this->Controller, $this->Action);
            if ($Reflection->isPublic()) {
                $this->Value = call_user_func(array($this->Controller, $this->Action));
            }
        }
    }
}