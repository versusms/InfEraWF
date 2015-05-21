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
     * Base redirect object.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>     
     * @package    InfEra[System]
     * @subpackage Mvc
     * @version    1.0
     */
    class RedirectResult extends ActionResult
    {      
        /**
         * Object for View.
         * @var string 
         */
        private $Url;
                
        /**
         * Constructor.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param array  $Params Parameters for link
         * @param string $RouteName The name of Route. On empty - uses current route.
         */
        public function __construct($Params = NULL, $RouteName = "")
        {                               
            if (!is_null($Params))
            {
                $this->Url = Application::$Router->CreateUrl($Params, $RouteName);
            }
        }       
        
        /**
         * Get redirect url.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return string Url to redirect
         */
        public function GetUrl()
        {
            return $this->Url;
        }
        
        /**
         * Set redirect url.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Url
         */
        public function SetUrl($Url)
        {
            $this->Url = $Url;
        }
    }        
}