<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra[System]
 * @subpackage Mvc
 */
namespace InfEra\System\Mvc
{               
    /**
     * Base controller class.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>     
     * @package    InfEra[System]
     * @subpackage Mvc
     * @version    1.0
     */
    class Controller
    {   
        /**
         * Custom View container.
         * @var mixed
         */
        public $ViewBag = array();
        
        /**
         * Constructor.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function __construct()
        {                               
        }        
        
        /**
         * Get result for View.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param object $Object Object for View.
         * 
         * @return \InfEra\Sysmem\Mvc\ActionResult Result object.
         */
        public function View($Object = null)
        {            
            return new ActionResult($Object);
        }  
        
        /**
         * Set redirect to url.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param object $UrlToRedirect Url to redirect
         * 
         * @return \InfEra\Sysmem\Mvc\RedirectResult Redirect object
         */
        public function Redirect($UrlToRedirect)
        {
            $redirect = new RedirectResult();
            $redirect->SetUrl($UrlToRedirect);
            return $redirect;
        } 
        
        /**
         * Set redirect to action.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param array  $Params Parameters for link
         * @param string $RouteName The name of Route. On empty - uses current route.
         * 
         * @return \InfEra\Sysmem\Mvc\RedirectResult Redirect object
         */
        public function RedirectTo($Params = NULL, $RouteName = "")
        {
            return new RedirectResult($Params, $RouteName);
        }                 
    }
}