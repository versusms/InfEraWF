<?php
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.Layout.php
 * Type:     function
 * Name:     Layout
 * Purpose:  Associate Layout template
 * -------------------------------------------------------------
 */
function smarty_function_Layout($params, $smarty)
{    
    if (array_key_exists('tpl', $params))
    {
        $GLOBALS['VIEWLAYOUT'] = $params['tpl'];
    }
    else
    {
        $GLOBALS['VIEWLAYOUT'] = 'default';
    }
    return '';
}