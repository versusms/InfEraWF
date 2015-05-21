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
     * Represents XML-response.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>     
     * @package    InfEra[System]
     * @subpackage Mvc
     * @version    1.0
     */
    class XMLResult extends ActionResult
    {                  
        /**
         * Get XML-string of data.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return string XML-string of data.
         */
        public function GetDataForView()
        {
            // @TODO Create real XML-string
            return serialize($this->Data);
        }               
    }        
}