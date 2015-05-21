<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 */
namespace InfEra\System
{       
    /**
     * Configuration class.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]     
     */
    class Configuration extends Collections\Dictionary
    {
        /**
         * Is allowed to change configuration parameters.
         * Use to disable changes after initing.
         * @var bool 
         */
        private $AllowedToChange = true;
        
        public function __construct()
        {
            if (isset($GLOBALS['APP_SETTINGS']))
            {
                foreach ($GLOBALS['APP_SETTINGS'] as $ParameterName => $ParameterValue)
                {
                    $this->Add($ParameterName, $ParameterValue);
                    $this->$ParameterName = $ParameterValue;
                }
            }
            else
            {
                trigger_error
                (
                    "[Configuration] Configuration not found",
                    E_USER_ERROR
                );
            }
            $this->AllowedToChange = false;
        }
        
        ###################################################
        #              ArrayAccess Methods                #
        ###################################################        
        /**
         * <b>[ArrayAccess]</b> Offset to set.<br/>
         * <b>NOT ALLOWED FOR CONFIGURATION OBJECT.</b>
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param mixed $offset The offset to assign the value to.
         * @param mixed $value The value to set.
         */
        public function offsetSet($offset, $value)
        {                 
            if ($this->AllowedToChange)
            {
                parent::offsetSet($offset, $value);                
            }
            else
            {
                trigger_error
                (
                    "[Configuration] Attempt to set data for Configuration Object",
                    E_USER_NOTICE
                );            
            }
        }
        
        /**
         * <b>[ArrayAccess]</b> Offset to unset.<br/>
         * <b>NOT ALLOWED FOR CONFIGURATION OBJECT.</b>
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
                "[Configuration] Attempt to unset data for Configuration Object",
                E_USER_NOTICE
            ); 
        }
    }
}
?>
