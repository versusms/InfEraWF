<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 10.04.2016 22:03
 * @package InfEra\WAFP\System\Mvc
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc;


/**
 * Route object
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Mvc
 */
class RouteSegment
{
    /**
     * Segment value
     * @var string
     */
    public $Value;

    /**
     * Is static
     * @var bool
     */
    public $Static;

    /**
     * Is optional
     * @var bool
     */
    public $Optional;

    public function __construct($Value, $Static = true, $Optional = true)
    {
        $this->Value = $Value;
        $this->Static = $Static;
        $this->Optional = $Optional;
    }
}