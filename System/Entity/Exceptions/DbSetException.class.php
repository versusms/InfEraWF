<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 02.06.2015 13:48
 * @package InfEra\System\Entity\Exceptions
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Entity\Exceptions;
/**
 * Class DbSetException
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Entity\Exceptions
 */
//@TODO To ErrorException
class DbSetException extends \Exception
{
    /**
     * Error codes - 12XX
     */
    const TABLE_NOT_FOUND = 1201;
    const INVALID_OBJECT_TYPE = 1202;
    const NO_FIELD_IN_DB = 1203;
    const NOT_EQUAL_TYPES_FOR_FIELD = 1207;
    const NOT_EQUAL_FIELDS_COUNT = 1208;
    const METHOD_NOT_ALLOWED = 1209;
}