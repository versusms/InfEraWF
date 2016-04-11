<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 09.11.2015 17:58
 * @package InfEra\System\Entity\Exceptions
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Entity\Exceptions;

/**
 * Class ModelException
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Entity\Exceptions
 */
//@TODO To ErrorException
class ModelException extends \Exception
{
    /**
     * Error codes - 13XX
     */
    const NO_TYPE_FOR_FIELD = 1301;
    const NO_ENTITY_FOR_VIRTUAL_FIELD = 1302;
    const NO_ENTITY_LINK_FOR_VIRTUAL_FIELD = 1303;
    const NO_TABLENAME_IN_MODEL_SPECIFICATION = 1304;
    const VIRTUAL_IS_NOT_PROTECTED = 1305;
    const ACCESS_TO_UNKNOWN_FIELD = 1306;
    const UNKNOWN_TYPE_FOR_REGULAR_FIELD = 1307;
}