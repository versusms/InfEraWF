<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Web
 */
namespace InfEra\System\Web
{   
    /**
     * Base Response class
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @package    InfEra[System]
     * @subpackage Web
     */
    class Response extends \InfEra\System\Patterns\ISingleton
    {
        /**
         * Headers for response
         * @var array
         */
        private $Headers = array();
        
        /**
         * Constructor
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function __construct()
        {        
        }
        
        /**
         * Write string to output
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $String String to write         
         */
        public function Write($String)
        {
            echo (string)$String;
        }
        
        /**
         * Set headers for response.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Name Name of header.
         * @param string $Value Value of header.
         */
        public function SetHeader($Name, $Value)
        {
            $this->Headers[$Name] = $Value;
        }
        
        /**
         * Send headers to output.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function SendHeaders()
        {
            foreach ($this->Headers as $Name => $Value)
            {
                header("$Name: $Value");
            }
        }      
    }
}