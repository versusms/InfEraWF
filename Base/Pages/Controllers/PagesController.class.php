<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra[Base]
 * @subpackage Pages[Controllers]
 */
namespace InfEra\Base\Pages\Controllers
{  
    use InfEra\System\Localization as L;
    /**
     * Textpages controller
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[Base]
     * @subpackage Pages[Controllers]
     */
    class PagesController extends \InfEra\System\Mvc\Controller
    {    
       /**
        * 
        * @return Mvc\Controller
        */
        public function Index()
        {
            $this->ViewBag['Title'] = L::__('Home Page');
            return $this->View();            
        }
    }

}