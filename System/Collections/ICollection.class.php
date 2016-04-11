<?php
/**
 * Created by InfEra Solutions.
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra\WAFP\System
 * @subpackage Collections
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Collections;
/**
 * Interface for collections.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Collections
 */
interface ICollection extends \Iterator, \ArrayAccess
{
    /**
     * Get number of items.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return int Number of items in collection.
     */
    public function Count() : int;

    /**
     * Get first item in collection.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return mixed First item in collection or NULL if collection is empty.
     */
    public function First();

    /**
     * Get last item in collection.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return mixed Last item in collection or NULL if collection is empty.
     */
    public function Last();

    /**
     * Get item by key in collection.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $Key Key of item in collection.
     *
     * @return mixed Returns item by key in collection or NULL on failure.
     */
    public function Get($Key);

    /**
     * Add item to collection.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $Key The key to assign the value to.
     * @param mixed $Value The value to set.
     */
    public function Add($Key, $Value);

    /**
     * Remove item from collection.
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param mixed $Key The key to delete value.
     */
    public function Remove($Key);
}