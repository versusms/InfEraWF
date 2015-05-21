<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Entity[Connectors]
 */
namespace InfEra\System\Entity\Connectors
{
    /**
     * Interface for connectors to Database. 
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Entity[Connectors]
     */
    interface IConnector
    {
        /**
         * Check connection
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   boolean
         */
        public function IsConnectionEstablished();

        /**
         * Fetch result from query resource
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    mysqli_result  $Resource Reference to query result resource
         * @param    boolean  $ReturnAssoc Return as associative array
         * 
         * @return   array   
         */
        public function Fetch($Resource, $ReturnAssoc = true);
        
        /**
         * Fetch all result rows from query resource
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    mysqli_result  $Resource Reference to query result resource
         * @param    boolean  $ReturnAssoc Return as associative array
         * 
         * @return   array   
         */
        public function FetchAll($Resource, $ReturnAssoc = true);

        /**
         * Execute SQL-query
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    string  $Sql SQL-query
         * 
         * @return   resource
         */
        public function ExecQuery($Sql);

        /**
         * Get number of affected rows
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   int         
         */
        public function GetAffectedRows();

        /**
         * Get number of rows in result set
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    mysqli_result  $Resource Reference to query result resource
         * 
         * @return   int         
         */
        public function GetNumRows($Resource);

        /**
         * Get value of last increased autoincrement field
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   int         
         */
        public function GetLastInsertedId();
        
        /**
         * Get tables list
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   array         
         */
        public function GetTablesList();
        
        /**
         * Get table description
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $TableName The name of table to descript
         * 
         * @return   array         
         */
        public function GetTableDescription($TableName);

        /**
         * Escape string for SQL-query
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param   string  $Value String of SQL-запроса
         * @param   boolean $Like Will be used in LIKE-query
         * 
         * @return   string         
         */
        public function Escape($Value, $Like = false);

        /**
         * Free resource of result resource
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    mysqli_result  $Resource to query result resource
         */
        public function FreeResult($Resource);

        /**
         * Prepare dataset for using in SQL-query
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    array  $Dataset Dataset
         * 
         * @return   array
         */
        public function PrepareSet($Dataset);

        /**
         * Prepare field for using in SQL-query
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    mixed  $FieldValue Field value
         * 
         * @return   string
         */
        public function PrepareField($FieldValue);

        /**
         * Insert data to table
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0  
         * 
         * @param    string $TableName Table name
         * @param    array  $Dataset Dataset
         * 
         * @return   int Last inserted ID
         */
        public function Insert($TableName, $Dataset);

        /**
         * Update data in table
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0     
         * 
         * @param    string  $TableName Table name
         * @param    array   $Dataset Dataset
         * @param    string  $Condition WHERE-Condition
         * 
         * @return   int Number of affected rows
         */
        public function Update($TableName, $Dataset, $Condition = '');

        /**
         * Deleting data from table
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    string   $TableName Table name
         * @param    string   $Condition WHERE-Condition
         * @param    boolean  $Force Force delete all records if condition is empty [default = false]
         * 
         * @return   int Number of affected rows
         */
        public function Delete($TableName, $Condition = '', $Force = false);

        /**
         * Close establishing connection 
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function Close();
    }
}