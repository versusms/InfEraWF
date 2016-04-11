<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Entity[Connectors]
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Entity\Connectors;
/**
 * Interface for connectors to Database.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Entity[Connectors]
 */
interface IConnector
{
    /**
     * Check connection
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return   bool
     */
    public function IsConnectionEstablished() : bool;

    /**
     * Fetch result from query resource
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    mixed $Resource Reference to query result resource
     * @param    bool $ReturnAssoc Return as associative array
     *
     * @return   array
     */
    public function Fetch($Resource, bool $ReturnAssoc = true) : array;

    /**
     * Fetch all result rows from query resource
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    mixed $Resource Reference to query result resource
     * @param    bool $ReturnAssoc Return as associative array
     *
     * @return   array
     */
    public function FetchAll($Resource, bool $ReturnAssoc = true) : array;

    /**
     * Execute SQL-query
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    string $Sql SQL-query
     *
     * @return   resource
     */
    public function ExecQuery(string $Sql);

    /**
     * Get number of affected rows
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return   int
     */
    public function GetAffectedRows() : int;

    /**
     * Get number of rows in result set
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    mixed $Resource Reference to query result resource
     *
     * @return   int
     */
    public function GetNumRows($Resource) : int;

    /**
     * Get value of last increased autoincrement field
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return   int
     */
    public function GetLastInsertedId() : int;

    /**
     * Get tables list
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return   array
     */
    public function GetTablesList() : array;

    /**
     * Get table description
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $TableName The name of table to descript
     *
     * @return   array
     */
    public function GetTableDescription(string $TableName) : array;

    /**
     * Escape string for SQL-query
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param   string $Value String of SQL-запроса
     * @param   bool $Like Will be used in LIKE-query
     *
     * @return   string
     */
    public function Escape(string $Value, bool $Like = false) : string;

    /**
     * Free resource of result resource
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    mixed $Resource to query result resource
     */
    public function FreeResult($Resource);

    /**
     * Prepare dataset for using in SQL-query
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    array $Dataset Dataset
     *
     * @return   array
     */
    public function PrepareSet(array $Dataset) : array;

    /**
     * Prepare field for using in SQL-query
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    mixed $FieldValue Field value
     *
     * @return   string
     */
    public function PrepareField($FieldValue) : string;

    /**
     * Insert data to table
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    string $TableName Table name
     * @param    array $Dataset Dataset
     *
     * @return   int Last inserted ID
     */
    public function Insert(string $TableName, array $Dataset) : int;

    /**
     * Update data in table
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    string $TableName Table name
     * @param    array $Dataset Dataset
     * @param    string $Condition WHERE-Condition
     *
     * @return   int Number of affected rows
     */
    public function Update(string $TableName, array $Dataset, string $Condition = '') : int;

    /**
     * Deleting data from table
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param    string $TableName Table name
     * @param    string $Condition WHERE-Condition
     * @param    bool $Force Force delete all records if condition is empty [default = false]
     *
     * @return   int Number of affected rows
     */
    public function Delete(string $TableName, string $Condition = '', bool $Force = false) : int;

    /**
     * Close establishing connection
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     */
    public function Close();
}