<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 25.02.2016 18:21
 * @package InfEra\WAFP\System\DirectoryServices
 */

namespace InfEra\WAFP\System\DirectoryServices;


/**
 * Class DirecotyServiceException
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\WAFP\System\DirectoryServices
 */
class DirectoryServiceException extends \Exception
{
    // TODO Check types
    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $LinkIdentifier
     * @return int
     */
    public function GetLastCode($LinkIdentifier)
    {
        return ldap_errno($LinkIdentifier);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $LinkIdentifier
     * @return string
     */
    public function GetLastMessage($LinkIdentifier)
    {
        return ldap_error($LinkIdentifier);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param array $errcontext
     * @return bool
     * @throws DirectoryServiceException
     */
    public function ErrorHandler($errno, $errstr, $errfile, $errline, array $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new DirectoryServiceException($errstr, $errno);
    }
}