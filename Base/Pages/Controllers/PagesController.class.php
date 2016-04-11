<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra\WAFP[Base]
 * @subpackage Pages[Controllers]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\Base\Pages\Controllers;

use InfEra\WAFP\System\Localization as L;
use InfEra\WAFP\System\Mvc\Controllers\Controller;
use InfEra\WAFP\System\Mvc\Results\ActionResult;

/**
 * Textpages controller
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP\[Base]
 * @subpackage Pages[Controllers]
 */
class PagesController extends Controller
{
    /**
     *
     * @return ActionResult
     */
    public function Index() : ActionResult
    {
        $this->ViewBag['Title'] = L::__('Home Page');
        return $this->View();
    }
}