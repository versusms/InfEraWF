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
     * Session object.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Web
     */
    class Session extends \InfEra\System\Patterns\ISingleton
                    implements \Iterator, \ArrayAccess
    {
        /**
         * Is session startes.
         * @var bool Is session started
         */
        public $IsStarted = false;
        
        /**
         * Current item for iterator.
         * @var array
         */        
        private $Current = array();
        
        /**
         * Constructor.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @static
         */
        public function __construct()
        {        
            $this->IsStarted = session_start();
        }
        
        /**
         * Get session data.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $paramName Name of parameter.
         * @return mixed Value of parameter or NULL if not exists.
         */
        public function Get($paramName)
        {
            return $this->offsetGet($paramName);
        }
        
        /**
         * Set session data.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $paramName Name of parameter.       
         * @param mixed $paramValue Value of parameter.         
         */
        public function Set($paramName, $paramValue)
        {            
            $this->offsetSet($paramName, $paramValue);            
        }
        
        /**
         * Remove session data.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $paramName Name of parameter.        
         */
        public function Remove($paramName)
        {            
            $this->offsetUnset($paramName);
        }
        
        ###################################################
        #              ArrayAccess Methods                #
        ###################################################
        /**
         * <b>[ArrayAccess]</b> Whether a offset exists.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset An offset to check for.
         * 
         * @return bool Returns TRUE on success or FALSE on failure.
         */
        public function offsetExists($offset)
        {            
            $result = false;
            if (is_string($offset))
            {                
                $result = isset($_SESSION[$offset]);
            }
            else
            {
                trigger_error
                (
                    "[Session] Key must be a string",
                    E_USER_WARNING
                );
            }
            return $result;
        }
        
        /**
         * <b>[ArrayAccess]</b> Offset to retrieve.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset The offset to retrieve.
         * 
         * @return mixed Offset to retrieve or NULL if not exists.
         */
        public function offsetGet($offset)
        {            
            $result = NULL;
            if (is_string($offset))
            {
                if ($this->offsetExists($offset))
                {
                    $result = $_SESSION[$offset];
                }                
            }
            else
            {
                trigger_error
                (
                    "[Session] Key must be a string",
                    E_USER_WARNING
                );
            }
            
            return $result;
        }
        
        /**
         * <b>[ArrayAccess]</b> Offset to set.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset The offset to assign the value to.
         * @param mixed $value The value to set.
         */
        public function offsetSet($offset, $value)
        {            
            if (is_string($offset))
            {                
                $_SESSION[$offset] = $value;             
            }
            else
            {
                trigger_error
                (
                    "[Session] Key must be a string",
                    E_USER_WARNING
                );
            }
        }
        
        /**
         * <b>[ArrayAccess]</b> Offset to unset.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset The offset to unset.    
         */
        public function offsetUnset($offset)
        {            
            if (is_string($offset))
            {                
                unset($_SESSION[$offset]);
            }
            else
            {
                trigger_error
                (
                    "[Session] Key must be a string",
                    E_USER_WARNING
                );
            }
        }
        
        ###################################################
        #                Iterator Methods                 #
        ###################################################
        /**
         * <b>[Iterator]</b> Return the current element.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return mixed Current element.
         */
        public function current()
        {                  
            return $this->Current['value'];
        }
        
        /**
         * <b>[Iterator]</b> Return the key of the current element.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return scalar Returns scalar on success, or NULL on failure.
         */
        public function key()
        {
            return (string)$this->Current['key'];
        }
        
        /**
         * <b>[Iterator]</b> Move forward to next element.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function next() {}
        
        /**
         * <b>[Iterator]</b> Rewind the Iterator to the first element.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function rewind()
        {           
            reset($_SESSION);
        }
        
        /**
         * <b>[Iterator]</b> Checks if current position is valid.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return bool Returns TRUE on success or FALSE on failure.
         */
        public function valid()
        {       
            $result = each($_SESSION);
            if (is_array($result))
            {
                $this->Current = $result;
            }
            return (is_array($result));
        }
    }
}