<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Mvc
 */
namespace InfEra\System\Mvc
{           
    use \InfEra\Application as Application;
    require_once(Application::$Configuration->FrameworkPath . 'Libs/Smarty/Smarty.class.php');
    /**
     * View object.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @package    InfEra[System]
     * @subpackage Mvc
     */
    class View
    {
        /**
         * Templater object
         * @var object
         */
        private $Templater = null;
        
        /**
         * Constructor 
         */
        public function __construct()
        {
            $this->Templater = new \Smarty();
            $this->Templater->debugging = false;
            $this->Templater->caching = false;
            $this->Templater->cache_lifetime = 120;                        
            
            $this->Templater->setTemplateDir(Application::$Configuration->ProjectPath . 'View/');
            $this->Templater->setCompileDir(Application::$Configuration->CashePath);            
            $this->Templater->setCacheDir(Application::$Configuration->CashePath);
            $this->Templater->addPluginsDir(Application::$Configuration->FrameworkPath . 'Libs/Smarty/mvcplugins/');
        }
        
        public function Display($controller, $action, $viewbag, $actionresult)
        {
            $Document = array
            (
                'language' => \InfEra\System\Localization::$CurrentLocale
            );
            $this->Templater->assign('View', $Document);
            $this->Templater->assign('ViewBag', $viewbag);
            $this->Templater->assign('Model', $actionresult);
            $this->Templater->assign('User', array
                (
                    'IsLogged' => Application::$Security->IsUserLogged,
                    'UserData' => Application::$Security->CurrentUser,
                ));
            
            $tDirs = $this->Templater->getTemplateDir();  
            $templatePath = (is_file($tDirs[0] . $controller . '/' . $action . '.xtpl')) ? $controller . '/' . $action . '.xtpl' : '_Shared/' . $action . '.xtpl';           
            
            $tTemplate = $this->Templater->fetch($templatePath);            
            $_Layout = (!array_key_exists('VIEWLAYOUT', $GLOBALS)) ? "_Layout.xtpl" : $GLOBALS['VIEWLAYOUT'];
                
            if (!is_null($_Layout))
            {
                $this->Templater->assign('RenderBody', $tTemplate);
                $this->Templater->display($_Layout);                
            }
            else
            {
                $this->Templater->display($templatePath);
            }                        
        }
    }    
    
    $ViewBag = array();
}