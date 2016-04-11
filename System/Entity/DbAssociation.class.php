<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 13.03.2016 14:53
 * @package InfEra\System\Entity
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Entity;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Entity\Exceptions\DbSetException;
use InfEra\WAFP\System\Mvc\Model;

/**
 * Class DbAssociation
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Entity
 */
class DbAssociation extends DbSet
{
    /**
     * DbAssociation constructor.
     * @param string $ConnectionAlias
     * @param string $AccociationTable
     */
    public function __construct(string $ConnectionAlias, string $AccociationTable)
    {
        $this->ConnectionAlias = $ConnectionAlias;
        $this->TableName = strtoupper($AccociationTable);

        ######### CHECKING ##########
        $tables = Application::$DBContext->Get($this->ConnectionAlias)->GetTablesList();

        if (in_array($this->TableName, $tables))
        {
            $this->TableDescription = Application::$DBContext->Get($this->ConnectionAlias)->GetTableDescription($this->TableName);
        }
        else
        {
            throw new DbSetException(
                "[DbSet] Table \"$this->TableName\" not found for association \"$AccociationTable\"",
                DbSetException::TABLE_NOT_FOUND
            );
        }
    }

    /**
     * Select records from database
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $Fields Fields to select
     * @param bool $IndexesByKeys
     *
     * @return array Array of associated keys
     */
    public function Select(string $Fields = "*", bool $IndexesByKeys = true)
    {
        $Result = array();

        $this->Fields = $Fields;

        $query = "SELECT
                        $this->Fields
                      FROM
                        $this->TableName
                      $this->Joins " .
            (($this->Condition != '') ? "WHERE
                        $this->Condition" : "") . "
                      $this->Order
                      $this->Limit";

        $resource = Application::$DBContext->Get($this->ConnectionAlias)->ExecQuery($query);
        $Result = Application::$DBContext->Get($this->ConnectionAlias)->FetchAll($resource);

        return $Result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param Model $Object
     * @return int
     */
    public function Add(Model &$Object) : int
    {
        throw new DbSetException(
            "[DbSet] Method \"Add\" not allowed for association. Use \"AddAssoc\" instead",
            DbSetException::METHOD_NOT_ALLOWED
        );
    }

    /**
     * Add record to DbAssociation and
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param array $KeysValues Keys values
     */
    public function AddAssoc(array $KeysValues) : bool
    {
        $result = false;

        Application::$DBContext->Get($this->ConnectionAlias)->Insert($this->TableName, $KeysValues);

        $result = true;

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param Model $Object
     * @return int
     */
    public function Store(Model $Object) : int
    {
        throw new DbSetException(
            "[DbSet] Method \"Store\" not allowed for association. Use \"StoreAssoc\" instead",
            DbSetException::METHOD_NOT_ALLOWED
        );
    }

    /**
     * Save object's changes to database
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param array $KeysValues
     * @param array $Condition
     * @return int
     */
    public function StoreAssoc(array $KeysValues, array $Condition) : int
    {
        $result = 0;

        $result = Application::$DBContext->Get($this->ConnectionAlias)->Update($this->TableName, $KeysValues, $Condition);

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param Model $Object
     * @throws DbSetException
     */
    public function Delete(Model $Object)
    {
        throw new DbSetException(
            "[DbSet] Method \"Delete\" not allowed for association. Use \"DeleteAssoc\" instead",
            DbSetException::METHOD_NOT_ALLOWED
        );
    }

    /**
     * Delete object from database
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param array $Condition
     */
    public function DeleteAssoc(array $Condition = array())
    {
        Application::$DBContext->Get($this->ConnectionAlias)->Delete($this->TableName, $Condition);
    }
}