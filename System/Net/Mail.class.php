<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Net
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Net;

use InfEra\WAFP\Application;
require_once(Application::$Configuration->FrameworkPath . 'Libs/PHPMailer/class.phpmailer.php');

/**
 * Sending emails
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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