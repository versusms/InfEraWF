<?php
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.Locale.php
 * Type:     function
 * Name:     Locale
 * Purpose:  Return localized string
 * -------------------------------------------------------------
 */
use InfEra\System\Localization as L;

function smarty_function_Locale($params, $smarty)
{    
    return (array_key_exists('str', $params)) ? L::__($params['str']) : "";    
}