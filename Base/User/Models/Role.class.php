<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 11.03.2016 9:43
 * @package \InfEra\WAFP\Base\User\Models
 */
declare(strict_types = 1);

namespace InfEra\WAFP\Base\User\Models;

use InfEra\WAFP\System\Mvc\Model;
use InfEra\WAFP\System\Collections\Dictionary;

/**
 * Class Role
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package \InfEra\WAFP\Base\User\Models
 *
 * @TableName Roles
 */
class Role extends Model
{
    /**
     * [DESCRIPTION]
     * @var int
     * @key PRIMARY
     */
    public $ID;

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $Name = '';

    /**
     * [DESCRIPTION]
     * @var bool
     * @default false
     */
    public $IsSystem = false;

    /**
     * [DESCRIPTION]
     * @virtual
     * @relation multiple
     * @var \InfEra\WAFP\System\Collections\Dictionary
     * @entity \InfEra\WAFP\Base\User\Models\AccessRule
     * @entitylink Roles_AccessRules
     */
    protected $AccessRules;

    /**
     * [DESCRIPTION]
     * @virtual
     * @relation multiple
     * @var \InfEra\WAFP\System\Collections\Dictionary
     * @entity \InfEra\WAFP\Base\User\Models\User
     * @entitylink Roles_Users
     */
    protected $Users;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        // @TODO Default Controller/Action for Role
        parent::__construct();
        $this->AccessRules = new Dictionary();
        $this->Users = new Dictionary();
    }
}