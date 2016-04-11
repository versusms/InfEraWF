<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 13.03.2016 0:18
 * @package InfEra\WAFP\Base\Models
 */
declare(strict_types = 1);

namespace InfEra\WAFP\Base\User\Models;

use InfEra\WAFP\System\Mvc\Model;

/**
 * Class AccessRule
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\WAFP\Base\User\Models
 *
 * @TableName AccessRules
 */
class AccessRule extends Model
{
    const ACCESS_PUBLIC = 0;
    const ACCESS_AUTH = 1;

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
    public $Object = '';

    /**
     * [DESCRIPTION]
     * @var int
     */
    public $Access = 0;
}