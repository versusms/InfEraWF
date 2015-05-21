<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
namespace InfEra\System\Security\Authorization
{        
    /**
     * Abstract Authorization Provider
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Security\Authorization
     */
    abstract class IAuthorization
    {   
        /**
         * Detection of Accessibility of Current Provider
         * 
         * @return boolean Is provider accessible 
         */
        public abstract function Detect();        
        
        /**
         * Initialization of Current Provider 
         */
        public abstract function Init();
        
        /**
         * Getting login url
         * 
         * @return string Login url 
         */
        public abstract function GetLoginUrl();
        
        /**
         * Getting logout url
         * 
         * @return string Login url 
         */
        public abstract function GetLogoutUrl();
        
        /**
         * Logout user with provider         
         */
        public abstract function LogoutUser();
        
        /**
         * Getting User object
         * 
         * @return InfEra\Base\User\Models\BaseUser User object 
         */
        public abstract function GetUser();
    }
}