<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Web
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Web;
/**
 * Base Response class
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[System]
 * @subpackage Web
 */
class Response extends \InfEra\WAFP\System\Patterns\ISingleton
{
    /**
     * Headers for response
     * @var array
     */
    private $Headers = array();

    /**
     * Cookie
     * @var array
     */
    private $Cookie = array();

    /**
     * Constructor
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function __construct()
    {
    }

    /**
     * Write string to output
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $String String to write
     */
    public function Write(string $String)
    {
        echo $String;
    }

    /**
     * Set headers for response.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $Name Name of header.
     * @param string $Value Value of header.
     */
    public function SetHeader(string $Name, string $Value)
    {
        $this->Headers[$Name] = $Value;
    }

    /**
     * Send headers to output.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function SendHeaders()
    {
        foreach ($this->Headers as $Name => $Value) {
            header("$Name: $Value");
        }
        foreach ($this->Cookie as $Name => $Value) {
            setcookie(
                $Name,
                $Value['value'],
                $Value['expire'],
                $Value['path'],
                $Value['domain'],
                $Value['secure'],
                $Value['httponly']
            );
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Name
     * @param string $Value
     * @param int $Expire
     * @param string|NULL $Path
     * @param string|NULL $Domain
     * @param bool $Secure
     * @param bool $HttpOnly
     */
    public function SetCookie(string $Name, string $Value = '', int $Expire = 0, string $Path = NULL, string $Domain = NULL, bool $Secure = false, bool $HttpOnly = false)
    {
        $this->Cookie[$Name] = array(
            'value' => $Value,
            'expire' => $Expire,
            'path' => $Path,
            'domain' => $Domain,
            'secure' => $Secure,
            'httponly' => $HttpOnly,
        );
    }
}