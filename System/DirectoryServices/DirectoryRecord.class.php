<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 10.03.2016 23:55
 * @package InfEra\WAFP\System\DirectoryServices
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\DirectoryServices;

use InfEra\WAFP\System\Collections\Dictionary;


/**
 * Class DirectoryRecord
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\WAFP\System\DirectoryServices
 */
class DirectoryRecord
{
    /**
     * [DESCRIPTION]
     * @var string
     */
    public $GUID = '';

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $DN = '';

    /**
     * [DESCRIPTION]
     * @var InfEra\WAFP\System\Collections\Dictionary
     */
    public $Attributes = null;

    /**
     * DirectoryRecord constructor.
     */
    public function __construct()
    {
        $this->Attributes = new Dictionary();
    }
}