<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 02.06.2015 17:09
 * @package InfEra\System\Reflection
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Reflection;

/**
 * Class DocCommentsParse
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Reflection
 */
class DocComments
{
    /**
     * [DESCRIPTION]
     *
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Comments
     */
    public static function Parse(string $Comments)
    {
        $result = array();
        if (preg_match_all('/@([^\s]+)\s+([a-zA-Z0-9<>._@\\\ ]+)/', $Comments, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result[$match[1]] = $match[1] == 'var' && in_array($match[2], array('int', 'bool', 'string', 'float', 'DateTime')) ? strtoupper($match[2]) : $match[2];
            }
        }
        return $result;
    }
}