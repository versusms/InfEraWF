<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra\WAFP
 */
declare(strict_types = 1);

namespace InfEra\WAFP;
/**
 * Autoloader for namespaces.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP
 */
class Autoloader
{
    /**
     * Load file with class.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     * @param string $ClassName Class name
     */
    public static function Load(string $ClassName)
    {
        if (strpos($ClassName, 'Smarty') === false) {

            $tNSPath = str_replace('\\', '/', $ClassName);
            if (strpos($tNSPath, "InfEra/WAFP/") !== false) {
                $tNSPath = str_replace("InfEra/WAFP/", $GLOBALS['APP_SETTINGS']['FrameworkPath'], $tNSPath);
            } else {
                $tNSPath = $GLOBALS['APP_SETTINGS']['ProjectPath'] . "Src/" . $tNSPath;
            }

            $filepath = $tNSPath . '.class.php';

            if (file_exists($filepath)) {
                require_once($filepath);
            }
        }
    }
}

/**
 * Registering autoloader
 */
\spl_autoload_register('InfEra\WAFP\Autoloader::Load');