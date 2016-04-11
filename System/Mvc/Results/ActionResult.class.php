<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc[Results]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc\Results;

/**
 * Base result object.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 * @version    1.0
 */
class ActionResult
{
    /**
     * Object for View.
     * @var mixed
     */
    protected $Data;

    /**
     * Constructor.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed Object for view
     */
    public function __construct($ObjectForView = NULL)
    {
        $this->Data = $ObjectForView;
    }

    /**
     * Get data for View.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return mixed Object for view
     */
    public function GetDataForResponse()
    {
        return $this->Data;
    }
}