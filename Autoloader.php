<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra
 */
namespace InfEra
{ 
    /**
     * Autoloader for namespaces.
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra
     */
    class Autoloader
    {      
        /**
         * Load file with class.
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * @param string $ClassName Class name
         */
        public static function Load($ClassName)
        {            
            if(strpos($ClassName, 'Smarty') === false)
            {
                //var_dump($ClassName);
                $tNSPath = str_replace('\\', '/', $ClassName);
                if (strpos($tNSPath, "InfEra/") !== false)
                {
                    $tNSPath = str_replace("InfEra/", $GLOBALS['APP_SETTINGS']['FrameworkPath'], $tNSPath);
                }
                else
                {
                    $tNSPath = $GLOBALS['APP_SETTINGS']['ProjectPath'] . "Code/" . $tNSPath;
                }            

                $filepath = $tNSPath . '.class.php';            

                if (file_exists($filepath))
                {                
                    require_once($filepath);
                }
                else
                { 
                    trigger_error
                    (
                        "[AutoLoader] Can't find file " . $filepath . " for class " . $ClassName,
                        E_USER_ERROR
                    );
                }
            }
        }        
    }
    
    /**
     * Registering autoloader     
     */
    \spl_autoload_register('InfEra\Autoloader::Load');
}