<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP[System]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Entity\Exceptions\DbSetException;
use InfEra\WAFP\System\Collections\Dictionary;

/**
 * Configuration class.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 */
class Settings extends Dictionary
{
    /**
     * Settings constructor.
     */
    public function __construct()
    {
        $this->Reload();
    }

    /**
     * Load application settings from database
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     */
    public function Reload()
    {
        try {
            $this->Clear();
            $AppSettings = Application::$DBContext->GetDbSet('\InfEra\WAFP\System\Settings\Setting')->Select();
            foreach ($AppSettings as $setting) {
                $this->Add($setting->Key, $setting->Value);
            }
        } catch (DbSetException $e) {
        }
    }

    /**
     * Store application settings to database
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     */
    public function Store()
    {
        //TODO STORE APP SETTINGS
        var_dump($this);
    }
}