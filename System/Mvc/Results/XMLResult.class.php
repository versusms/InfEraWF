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
 * Represents XML-response.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 * @version    1.0
 */
class XMLResult extends ActionResult
{
    /**
     * Get XML-string of data.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return string XML-string of data.
     */
    public function GetDataForResponse()
    {
        // @TODO Create real XML-string
        return serialize($this->Data);
    }
}