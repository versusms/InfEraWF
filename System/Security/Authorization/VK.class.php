<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
namespace InfEra\System\Security\Authorization
{    
    //require_once(FRAMEWORK_PATH . 'Libs/FaceBook/FaceBook.class.php');
    use InfEra\System\Localization as L;
    use InfEra\Application as Application;
    /**
     * FaceBook Authorization Provider
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Security\Authorization
     */
    class VK extends IAuthorization
    {
        /**
         * VK AppId
         * @var string
         */
        private $AppId = '428088973922904';
        
        /**
         * VK AppSecret
         * @var string
         */
        private $AppSecrect = 'e3e913d601a5bd37d1b5367b4bbdc879';                        
        
        /**
         * VK Query Object
         * @var object
         */
        private $VK = null;        
        
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
            return (!is_null(Application::$Request->Get("api_url")));
        }
        
        /**
         * Initialization of Current Provider 
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         */
        public function Init()
        {            
            /*$this->FaceBook = new \Facebook
            (
                array
                (
                    'appId' => $this->AppId,
                    'secret' => $this->AppSecrect,
                )
            );*/
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
         * Getting User object
         * 
         * @author     Alexander A. Popov <alejandro.popov@outlook.com>
         * @version    1.0
         * 
         * @return InfEra\Base\User\Models\BaseUser User object 
         */
        public function GetUser()
        {
            return null;
        }
    }
}