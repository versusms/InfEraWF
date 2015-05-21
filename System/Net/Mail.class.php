<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra[System]
 * @subpackage Net
 */
namespace InfEra\System\Net
{  
    use \InfEra\Application as Application;
    require_once(Application::$Configuration->FrameworkPath . 'Libs/PHPMailer/class.phpmailer.php');
    
    /**
     * Sending emails
     * 
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Net
     */
    class Mail extends \PHPMailer
    {     
        public function __construct()
        {
            // @TODO Encodings
            //$this->Encoding = "UTF-8";
            //$this->CharSet = "UTF-8";
            
            parent::__construct();
        }
    }
}