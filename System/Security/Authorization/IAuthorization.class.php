<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Authorization
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Security\Authorization;

/**
 * Abstract Authorization Provider
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Security\Authorization
 */
abstract class IAuthorization
{
    /**
     * Internal authority
     */
    const AUTHORITY_INTERNAL = 0;

    /**
     * External authority
     */
    const AUTHORITY_EXTERNAL = 1;

    /**
     * Authority Mode
     * @var int
     */
    protected $AuthorityMode;

    /**
     * Returns authority mode
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return int
     */
    public function GetAuthorityMode() : int
    {
        return $this->AuthorityMode;
    }

    /**
     * IAuthorization constructor.
     * @param array $Params
     */
    public abstract function __construct(array $Params);

    /**
     * Detection of Accessibility of Current Provider
     *
     * @return boolean Is provider accessible
     */
    public abstract function Detect();

    /**
     * Initialization of Current Provider
     */
    public abstract function Init();

    /**
     * Getting login url
     *
     * @return string Login url
     */
    public abstract function GetLoginUrl();

    /**
     * Getting logout url
     *
     * @return string Login url
     */
    public abstract function GetLogoutUrl();

    /**
     * Getting logout url
     *
     * @return string Login url
     */
    public abstract function GetProfileUrl();

    /**
     * Logout user with provider
     */
    public abstract function LogoutUser();

    /**
     * Get Last Operation State
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return string
     */
    public abstract function GetLastOperationState();

    /**
     * Check user's authority with provider
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Login
     * @param string $Password
     * @return boolean
     */
    public abstract function CheckAuthoritty($Login, $Password);

    /**
     * Getting User object
     *
     * @param string $Login
     * @param string $Password
     * @param bool $Remember
     *
     * @return InfEra\Base\User\Models\BaseUser User object
     */
    public abstract function GetUser($Login, $Password);
}