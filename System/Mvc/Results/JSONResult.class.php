<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc[Results]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc\Results;

use InfEra\WAFP\Application;

/**
 * Represents JSON-response.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 * @version    1.0
 */
class JSONResult extends ActionResult
{
    /**
     * Get JSON-string of data.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string JSON-string of data.
     */
    public function GetDataForResponse()
    {
        return json_encode($this->Data);
    }
}