<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 02.06.2015 13:35
 * @package InfEra\WAFP\System\Configuration
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Settings;

use InfEra\WAFP\System\Mvc\Model;

/**
 * Class Setting
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Configuration
 *
 * @TableName Settings
 */
class Setting extends Model
{
    /**
     * [DESCRIPTION]
     * @var string
     */
    public $Key = '';

    /**
     * [DESCRIPTION]
     * @var string
     * @nullable
     */
    public $Value = '';
}