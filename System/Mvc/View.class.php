<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Mvc
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Mvc\View\Controls\Menu;
use InfEra\WAFP\System\Mvc\View\Controls\MenuItem;
use InfEra\WAFP\System\Collections\Dictionary;

require_once(Application::$Configuration->FrameworkPath . 'Libs/Smarty/Smarty.class.php');

/**
 * View object.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
     * [DESCRIPTION]
     * @var null
     */
    private $UMenus = null;

    //@TODO Register Controller/Action Menus

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Templater = new \Smarty();
        $this->Templater->debugging = false;
        $this->Templater->caching = false;
        $this->Templater->cache_lifetime = 120;

        $this->Templater->setTemplateDir(Application::$Configuration->ProjectPath . 'Views/');
        $this->Templater->setCompileDir(Application::$Configuration->CashePath);
        $this->Templater->setCacheDir(Application::$Configuration->CashePath);
        $this->Templater->addPluginsDir(Application::$Configuration->FrameworkPath . 'Libs/Smarty/mvcplugins/');

        $this->UMenus = new Dictionary();
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $package
     * @param string $controller
     * @param string $action
     * @param array $viewbag
     * @param $actionresult
     */
    public function Display(string $package, string $controller, string $action, array $viewbag, $actionresult)
    {
        $Document = array
        (
            'language' => \InfEra\WAFP\System\Localization::$CurrentLocale
        );

        $this->Templater->assign('View', $Document);
        $this->Templater->assign('ViewBag', $viewbag);
        $this->Templater->assign('Model', $actionresult);
        $this->Templater->assign('Menus', $this->CreateMenus());
        $this->Templater->assign('Symlinks', Application::$Symlinks);
        $this->Templater->assign('User', array
        (
            'IsLogged' => Application::$Security->IsUserLogged,
            'UserData' => Application::$Security->CurrentUser,
            'LoginUrl' => Application::$Security->GetLoginUrl(),
            'LogoutUrl' => Application::$Security->GetLogoutUrl(),
            'ProfileUrl' => Application::$Security->GetProfileUrl(),
            'AuthMode' => Application::$Security->GetAuthrityMode(),
        ));

        $tDirs = $this->Templater->getTemplateDir();
        $pathByController = ($package == $controller) ? $package . '/' . $action . '.phtml' : $package . '/' . $controller . '.' . $action . '.phtml';
        $pathByShared = '_Shared/' . $action . '.phtml';

        $templatePath = (is_file($tDirs[0] . $pathByController)) ? $pathByController : $pathByShared;

        $tTemplate = $this->Templater->fetch($templatePath);
        $_Layout = (!array_key_exists('VIEWLAYOUT', $GLOBALS)) ? "_Layout.phtml" : $GLOBALS['VIEWLAYOUT'];

        if (!is_null($_Layout)) {
            $this->Templater->assign('RenderBody', $tTemplate);
            $this->Templater->display($_Layout);
        } else {
            $this->Templater->display($templatePath);
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param Menu $Menu
     */
    public function AddMenu(string $Index, Menu $Menu)
    {
        $Menu->ValidateMenuAccess();
        $this->UMenus->Add($Index, $Menu);
    }

    private function CreateMenus()
    {
        $result = $this->UMenus;

        if (count(Application::$Configuration['Menus'])) {
            foreach (Application::$Configuration['Menus'] as $menuIndex => $menuItems) {
                $NewMenu = new Menu();

                foreach ($menuItems as $menuItemIndex => $menuItemDescription) {
                    $NewMenu->Add($menuItemIndex, new MenuItem($menuItemIndex, $menuItemDescription));
                }

                $NewMenu->ValidateMenuAccess();

                $result->Add($menuIndex, $NewMenu);
            }
        }
        return $result;
    }
}

$ViewBag = array();