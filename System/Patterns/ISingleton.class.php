<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Patterns
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Patterns;
/**
 * Interface for Singleton classes.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Patterns
 */
class ISingleton
{
    /**
     * Instanse of class.
     * @var string
     */
    private static $Instance = NULL;

    /**
     * Close access to <b>__construct()</b> method.
     * No allowed for Singleton to call it from the outside.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    private function __construct()
    {
    }

    /**
     * Close access to <b>__clone()</b> method.
     * No allowed for Singleton to call it from the outside.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    private function __clone()
    {
    }

    /**
     * Close access to <b>__wakeup()</b> method.
     * No allowed for Singleton to call it from the outside.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    private function __wakeup()
    {
    }

    /**
     * Get single instanse of class.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return self Instanse of class.
     */
    public static function getInstance() : self
    {
        if (null === self::$Instance) {
            self::$Instance = new self();
        }
        return self::$Instance;
    }
}