<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
namespace InfEra\System\Security\Authorization
{            
    use InfEra\Application as Application;    
    /**
     * FaceBook Authorization Provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Security\Authorization
     */
    class Base extends IAuthorization
    {
        public function __construct(array $Params)
        {

        }

        /**
         * Detection of Accessibility of Current Provider
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return boolean Is provider accessible 
         */
        public function Detect()
        {                                    
            return true;
        }
        
        /**
         * Initialization of Current Provider 
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         */
        public function Init()
        {                        
            if (isset(Application::$Session['iefwcu']))
            {                
                $cusd = unserialize(Application::$Security->DecryptString(Application::$Session['iefwcu']));
                if ($cusd['r'] == 'Base')
                {                 
                    Application::$Security->UserLogin($cusd['l'], $cusd['p']);
                }
            }
        }
        
        /**
         * Getting login url
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return string Login url 
         */
        public function GetLoginUrl()
        {
            return "";//$this->FaceBook->getLoginUrl();
        }
        
        /**
         * Getting logout url
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return string Logout url 
         */
        public function GetLogoutUrl()
        {
            return "";//$this->FaceBook->getLoginUrl();
        }
        
        /**
         * Logout user with provider         
         */
        public function LogoutUser()
        {            
        }
        
        /**
         * Getting User object
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return InfEra\Base\User\Models\BaseUser User object 
         */
        public function GetUser($Login, $Password)
        {
            return null;
        }
    }
}