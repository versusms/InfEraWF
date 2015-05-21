<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Collections
 */
namespace InfEra\System\Collections
{

    /**
     * Dictionary collection.
     * 
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Collections
     */
    class Dictionary implements ICollection
    {    
        /**
         * Container for items.
         * @var array 
         */
        private $Items = array();
        
        /**
         * Current item for iterator.
         * @var array
         */        
        private $Current = array();
        
        
        ###################################################
        #              ICollection Methods                #
        ###################################################
        /**
         * Get number of items.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return int Number of items in collection.
         */
        public function Count()
        {             
            return count($this->Items);
        }
        
        /**
         * Get first item in collection.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return mixed First item in collection or NULL if collection is empty.
         */
        public function First()
        {          
            $result = NULL;
            
            if ($this->Count() > 0)
            {
                $result = reset($this->Items);
            }
            
            return $result;
        }
        
        /**
         * Get last item in collection.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return mixed Last item in collection or NULL if collection is empty.
         */
        public function Last()
        {           
            $result = NULL;
            
            if ($this->Count() > 0)
            {
                $result = end($this->Items);
            }
            
            return $result;
        }
        
        /**
         * Get item by key in collection.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Key Key of item in collection.
         * 
         * @return mixed Returns item by key in collection or NULL on failure.
         */
        public function Get($Key)
        {       
            return $this->offsetGet($Key);
        }
        
        /**
         * Add item to collection.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Key The key to assign the value to.
         * @param mixed $Value The value to set.
         */
        public function Add($Key, $Value)
        {
            $this->offsetSet($Key, $Value);
        }
        
        /**
         * Remove item from collection.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Key The key to delete value.
         */
        public function Remove($Key)
        {
            $this->offsetUnset($Key);
        }
        
        ###################################################
        #                Extended Methods                 #
        ###################################################
        
        public function AddAfter()
        {           
        }
        
        public function AddBefore()
        {           
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
                $result = isset($this->Items[$offset]);
            }
            else
            {
                trigger_error
                (
                    "[Collections|Dictionary] Key must be a string",
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
                    $result = $this->Items[$offset];
                }                
            }
            else
            {
                trigger_error
                (
                    "[Collections|Dictionary] Key must be a string",
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
                $this->Items[$offset] = $value;             
            }
            else
            {
                trigger_error
                (
                    "[Collections|Dictionary] Key must be a string",
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
                unset($this->Items[$offset]);
            }
            else
            {
                trigger_error
                (
                    "[Collections|Dictionary] Key must be a string",
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
            reset($this->Items);
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
            $result = each($this->Items);
            if (is_array($result))
            {
                $this->Current = $result;
            }
            return (is_array($result));
        }
    }
}