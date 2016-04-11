<?php
/**
 * Created by InfEra Solutions.
 * @author  Alexander A. Popov <alejandro.popov@outlook.com>
 * @package InfEra\WAFP\System
 */
namespace InfEra\WAFP\System;

use InfEra\WAFP\Application;

/**
 * Localization class.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP\System
 */
class Localization
{
    /**
     * Current locale.
     * @var string
     */
    public static $CurrentLocale = 'en';

    /**
     * Current locale strings.
     * @var string
     */
    private static $CurrentLocaleStrings = array();

    /**
     * Initialization of localization mechnism
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     */
    public static function init()
    {
        if ($DefaultLocale = Application::$Configuration->Get('DefaultLocale')) {
            Localization::$CurrentLocale = $DefaultLocale;
        }
        if ($locale = Application::$Request['lng']) {
            Localization::$CurrentLocale = $locale;
            Application::$Session['locale'] = $locale;
        } elseif ($locale = Application::$Session['locale']) {
            Localization::$CurrentLocale = $locale;
        }
        // Loading locale strings
        $localeDir = Application::$Configuration->ProjectPath . 'Localization/' . Localization::$CurrentLocale;
        if (is_dir($localeDir)) {
            $localeDirHndl = opendir($localeDir);
            while ($localeFile = readdir($localeDirHndl)) {
                if (!in_array($localeFile, array('.', '..'))) {
                    require_once($localeDir . '/' . $localeFile);
                }
            }
        }
    }

    /**
     * Add locale strings.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param array $localeStrings Associative array of locale strings.
     */
    public static function Add(array $localeStrings)
    {
        Localization::$CurrentLocaleStrings = array_merge(Localization::$CurrentLocaleStrings, $localeStrings);
    }

    /**
     * Get localized string.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $string Name of parameter.
     *
     * @return string Localized string
     */
    public static function __($string) : string
    {
        return (isset(Localization::$CurrentLocaleStrings[$string])) ? Localization::$CurrentLocaleStrings[$string] : $string;
    }
}