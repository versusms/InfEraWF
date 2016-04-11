<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @package    InfEra[Base]
 * @subpackage User[Models]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\Base\User\Models;

use InfEra\WAFP\System\Mvc\Model;

/**
 * Base user model
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[Base]
 * @subpackage User[Models]
 *
 * @TableName Users
 */
class User extends Model
{
    /**
     * [DESCRIPTION]
     * @var int
     * @key PRIMARY
     */
    public $ID = 0;

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $GUID = '';

    /**
     * [DESCRIPTION]
     * @var string
     * @default Base
     */
    public $Provider = "Base";

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $Login = "";

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $Password = "";

    /**
     * [DESCRIPTION]
     * @var string
     * @nullable
     */
    public $FirstName = NULL;
    /**
     * [DESCRIPTION]
     * @var string
     * @nullable
     */
    public $LastName = NULL;

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $Email = "";

    /**
     * [DESCRIPTION]
     * @var string
     * @nullable
     */
    public $Avatar = NULL;
    /**
     * Birthday
     * @var DateTime
     * @nullable
     */
    public $Birthday;

    /**
     * [DESCRIPTION]
     * @var string
     * @nullable
     */
    public $Location = NULL;

    /**
     * [DESCRIPTION]
     * @var string
     * @nullable
     */
    public $Gender = NULL;

    /**
     * [DESCRIPTION]
     * @var int
     * @default 3
     */
    public $Timezone = 3;

    /**
     * [DESCRIPTION]
     * @var string
     * @default en
     */
    public $Locale = "en";

    /**
     * [DESCRIPTION]
     * @var DateTime
     */
    public $RegistredAt;

    /**
     * [DESCRIPTION]
     * @var bool
     */
    public $IsSystem = false;

    /**
     * [DESCRIPTION]
     * @var bool
     * @default true
     */
    public $IsEnabled = true;

    /**
     * [DESCRIPTION]
     * @var string
     * @default Offline
     */
    public $Status = "Offline";

    /**
     * Last activity in system
     * @var DateTime
     */
    public $LastActivity;

    /**
     * [DESCRIPTION]
     * @var bool
     */
    public $IsSU = false;

    /**
     * [DESCRIPTION]
     * @virtual
     * @relation multiple
     * @var \InfEra\System\Collections\Dictionary
     * @entity \InfEra\Base\User\Models\Role
     * @entitylink Roles_Users
     */
    protected $Roles;


    public function __construct()
    {
        parent::__construct();
    }
}