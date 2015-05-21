<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 */
namespace InfEra\System\Mvc
{               
    use \InfEra\Application as Application;
    /**
     * Represents JSON-response.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>     
     * @package    InfEra[System]
     * @subpackage Mvc
     * @version    1.0
     */
    class JSONResult extends ActionResult
    {                         
        /**
         * Get JSON-string of data.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return string JSON-string of data.
         */
        public function GetDataForView()
        {
            return json_encode($this->Data);
        }               
    }        
}