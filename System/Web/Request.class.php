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
     * Request object.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Web
     */
    class Request extends \InfEra\System\Patterns\ISingleton
                    implements \Iterator, \ArrayAccess
    {
        /**
         * GET request
         */
        const TYPE_GET = "GET";
        
        /**
         * POST request
         */
        const TYPE_POST = "POST";
        
        /**
         * POST request with uploaded files
         */
        const TYPE_POSTFILES = "POSTFILES";        
        
        /**       
         * All data from request.
         * @var array
         */
        private $Data = array();
        
        /**       
         * Request type.
         * @var string
         */
        public $Type;
        
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
         */
        public function __construct()
        {
            $this->Data = array_merge($_GET, $_POST, $_FILES);  
            // @TODO Server Object
            if ($_SERVER['REQUEST_METHOD'] == Request::TYPE_GET)
            {
                $this->Type = Request::TYPE_GET;                
            }
            else
            {
                if (count($_FILES) > 0)
                {
                    $this->Type = Request::TYPE_POSTFILES;
                }
                else
                {
                    $this->Type = Request::TYPE_POST;
                }
            }
        }                
        
        /**
         * Getting GET/POST/FILES data.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @static
         * 
         * @param string $paramName Name of parameter.
         * @return mixed Value of parameter (null - on not found).
         */
        public function Get($paramName)
        {                        
            return $this->offsetGet($paramName);
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
                $result = isset($this->Data[$offset]);
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
                    $result = $this->Data[$offset];
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
         * <b>[ArrayAccess]</b> Offset to set.<br/>
         * <b>NOT ALLOWED FOR REQUEST OBJECT.</b>
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset The offset to assign the value to.
         * @param mixed $value The value to set.
         */
        public function offsetSet($offset, $value)
        {                 
            trigger_error
            (
                "[Request] Attempt to set data for Request Object",
                E_USER_NOTICE
            );            
        }
        
        /**
         * <b>[ArrayAccess]</b> Offset to unset.<br/>
         * <b>NOT ALLOWED FOR REQUEST OBJECT.</b>
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset The offset to unset.    
         */
        public function offsetUnset($offset)
        {            
            trigger_error
            (
                "[Request] Attempt to unset data for Request Object",
                E_USER_NOTICE
            ); 
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
            reset($this->Data);
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
            $result = each($this->Data);
            if (is_array($result))
            {
                $this->Current = $result;
            }
            return (is_array($result));
        }
    }
}