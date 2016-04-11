<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc[Controllers]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc\Controllers;

use InfEra\WAFP\System\Mvc\Results;

/**
 * Base controller class.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Mvc[Controllers]
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
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function __construct()
    {
    }

    /**
     * Get result for View.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param object $Object Object for View.
     *
     * @return \InfEra\Sysmem\Mvc\Results\ActionResult Result object.
     */
    protected function View(/*object*/
        $Object = null)
    {
        return new \InfEra\WAFP\System\Mvc\Results\ActionResult($Object);
    }

    /**
     * Set redirect to url.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $UrlToRedirect Url to redirect
     *
     * @return \InfEra\Sysmem\Mvc\Results\RedirectResult Redirect object
     */
    protected function Redirect(string $UrlToRedirect)
    {
        $redirect = new \InfEra\System\Mvc\Results\RedirectResult();
        $redirect->SetUrl($UrlToRedirect);
        return $redirect;
    }

    /**
     * Set redirect to action.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param array $Params Parameters for link
     * @param string $RouteName The name of Route. On empty - uses current route.
     *
     * @return \InfEra\Sysmem\Mvc\Results\RedirectResult Redirect object
     */
    protected function RedirectTo(array $Params = NULL, $RouteName = "")
    {
        return new \InfEra\System\Mvc\Results\RedirectResult($Params, $RouteName);
    }
}