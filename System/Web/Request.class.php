<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Web
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Web;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Collections\Dictionary;

/**
 * Request object.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Web
 */
class Request extends \InfEra\WAFP\System\Patterns\ISingleton
    implements \Iterator, \ArrayAccess
{
    /**
     * @link https://ru.wikipedia.org/wiki/HTTP#.D0.9C.D0.B5.D1.82.D0.BE.D0.B4.D1.8B
     */

    /**
     * OPTIONS request
     */
    const TYPE_OPTIONS = "OPTIONS";

    /**
     * GET request
     */
    const TYPE_GET = "GET";

    /**
     * HEAD request
     */
    const TYPE_HEAD = "HEAD";

    /**
     * POST request
     */
    const TYPE_POST = "POST";

    /**
     * POST request with uploaded files
     */
    const TYPE_POSTFILES = "POSTFILES";

    /**
     * PUT request
     */
    const TYPE_PUT = "PUT";

    /**
     * PATCH request
     */
    const TYPE_PATCH = "PATCH";

    /**
     * DELETE request
     */
    const TYPE_DELETE = "DELETE";

    /**
     * TRACE request
     */
    const TYPE_TRACE = "TRACE";

    /**
     * CONNECT request
     */
    const TYPE_CONNECT = "CONNECT";

    /**
     * Value for optional paramener
     */
    const URL_PARAMETER_OPTIONAL = NULL;

    /**
     * All data from request.
     * @var array
     */
    private $Data = array();

    /**
     * Request type.
     * @var string
     */
    public $Type;

    /**
     * [DESCRIPTION]
     * @var \InfEra\System\Collections\Dictionary
     */
    public $Cookie;

    /**
     * Current item for iterator.
     * @var array
     */
    private $Current = array();

    /**
     * [DESCRIPTION]
     * @var string
     */
    private $HttpXRequestedWith = '';


    /**
     * Constructor.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function __construct()
    {
        if (!array_key_exists('url', $_GET))
        {
            $_GET['url'] = '/';
        }
        $this->Data = array_merge($_GET, $_POST, $_FILES);
        // @TODO Server Object
        if ($_SERVER['REQUEST_METHOD'] == Request::TYPE_POST) {
            if (count($_FILES) > 0) {
                $this->Type = Request::TYPE_POSTFILES;
            } else {
                $this->Type = Request::TYPE_POST;
            }
        } else {
            $this->Type = $_SERVER['REQUEST_METHOD'];
        }

        if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
            $this->HttpXRequestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
        $this->Cookie = new Dictionary();
        foreach ($_COOKIE as $Name => $Value) {
            $this->Cookie->Add($Name, $Value);
        }
    }

    public function UpdateDataWithRoute()
    {
        if (!is_null(Application::$Router->CurrentRoute)) {
            $vars = Application::$Router->CurrentRoute->GetParameters($this['url']);
            foreach ($vars as $key => $value) {
                if (!array_key_exists($key, $this->Data)) {
                    $_GET[$key] = $value;
                    $this->Data[$key] = $value;
                }
            }
        }
    }

    /**
     * Getting GET/POST/FILES data.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @static
     *
     * @param string $paramName Name of parameter.
     * @return mixed Value of parameter (null - on not found).
     */
    public function Get($paramName)
    {
        return $this->offsetGet($paramName);
    }

    public function IsAsync()
    {
        return $this->HttpXRequestedWith != '';
    }

    ###################################################
    #              ArrayAccess Methods                #
    ###################################################
    /**
     * <b>[ArrayAccess]</b> Whether a offset exists.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $offset An offset to check for.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        $result = false;
        if (is_string($offset)) {
            $result = isset($this->Data[$offset]);
        } else {
            trigger_error
            (
                "[Session] Key must be a string",
                E_USER_WARNING
            );
        }
        return $result;
    }

    /**
     * <b>[ArrayAccess]</b> Offset to retrieve.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Offset to retrieve or NULL if not exists.
     */
    public function offsetGet($offset)
    {
        $result = NULL;
        if (is_string($offset)) {
            if ($this->offsetExists($offset)) {
                $result = $this->Data[$offset];
            }
        } else {
            trigger_error
            (
                "[Session] Key must be a string",
                E_USER_WARNING
            );
        }

        return $result;
    }

    /**
     * <b>[ArrayAccess]</b> Offset to set.<br/>
     * <b>NOT ALLOWED FOR REQUEST OBJECT.</b>
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        trigger_error
        (
            "[Request] Attempt to set data for Request Object",
            E_USER_NOTICE
        );
    }

    /**
     * <b>[ArrayAccess]</b> Offset to unset.<br/>
     * <b>NOT ALLOWED FOR REQUEST OBJECT.</b>
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        trigger_error
        (
            "[Request] Attempt to unset data for Request Object",
            E_USER_NOTICE
        );
    }

    ###################################################
    #                Iterator Methods                 #
    ###################################################
    /**
     * <b>[Iterator]</b> Return the current element.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return mixed Current element.
     */
    public function current()
    {
        return $this->Current['value'];
    }

    /**
     * <b>[Iterator]</b> Return the key of the current element.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return scalar Returns scalar on success, or NULL on failure.
     */
    public function key()
    {
        return (string)$this->Current['key'];
    }

    /**
     * <b>[Iterator]</b> Move forward to next element.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function next()
    {
    }

    /**
     * <b>[Iterator]</b> Rewind the Iterator to the first element.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function rewind()
    {
        reset($this->Data);
    }

    /**
     * <b>[Iterator]</b> Checks if current position is valid.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function valid()
    {
        $result = each($this->Data);
        if (is_array($result)) {
            $this->Current = $result;
        }
        return (is_array($result));
    }
}