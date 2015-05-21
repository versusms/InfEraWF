<?php
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.HtmlForm.php
 * Type:     block
 * Name:     HtmlForm
 * Purpose:  DisplayFormContainer
 * -------------------------------------------------------------
 */
function smarty_block_HtmlForm($params, $content, $smarty, $repeat)
{    
    $result = "";
    if ($repeat)
    {
        $id  = (isset($params['id'])) ? 'id="' . $params['id'] . '"' : '';
        $method = (isset($params['method'])) ? 'method="' . $params['method'] . '"' : 'method="POST"';
        $action = (isset($params['action'])) ? 'action="' . $params['action'] . '"' : 'action=""';
        $onsubmit = (isset($params['onsubmit'])) ? 'onsubmit="' . $params['onsubmit'] . '"' : '';
        $multipart = (isset($params['multipart'])) ? 'enctype="multipart/form-data"' : '';
        $result  = "<form $id $method $action $onsubmit $multipart>";
        $result .= '<input type="hidden" name="iefwfs" value="1" />';
    }
    else
    {
        $result = "$content</form>";
    }
    return $result;
}