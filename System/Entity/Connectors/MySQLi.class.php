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
     * Database connector to MySQLi. 
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Entity[Connectors]
     */
    class MySQLi implements IConnector
    {
        /**
         * Connections retry count
         * @var    int
         */
        private $TryConnectionCount = 5;

        /**
         * Timeout to next retry
         * @var    int
         */
        private $Sleep = 100000;

        /**
         * Connection ID
         * @var    resource
         */	
        private $ConnectionId = null;

        ######################################################
        ######################################################
        ######################################################

        /**
         * Constructor
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    array $Params Connection parameters
         */
        public function __construct($Params)
        {
            if (function_exists("mysqli_connect"))
            {                        
                $count = $this->TryConnectionCount;

                while($count-- > 0)
                {                
                    $this->ConnectionId = mysqli_connect($Params['server'], $Params['user'], $Params['password'], $Params['dbname']);

                    if (!$this->isConnectionEstablished())
                    {
                        trigger_error
                        (
                            sprintf('[MySQLi Connector] Attempting #%03d to connect with DataBase fault', $this->TryConnectionCount - $count),
                            E_USER_WARNING
                        );

                        // try one more time
                        usleep($this->Sleep);
                    }
                    else
                    {
                        $sql = "SET NAMES 'utf8',
                                    collation_connection='utf8_general_ci',
                                    collation_server='utf8_general_ci',
                                    collation_server='utf8_general_ci',
                                    character_set_client='utf8',
                                    character_set_connection='utf8',
                                    character_set_results='utf8',
                                    character_set_server='utf8'";

                        $this->execQuery($sql);
                        break;
                    }                
                }

                if (-1 == $count)
                {                
                    trigger_error
                    (
                        sprintf('[MySQLi Connector] Unable to connect to database server "%s".', $Params['server']),
                        E_USER_ERROR
                    );
                }
            }
            else
            {            
                trigger_error
                (
                    sprintf('[MySQLi Connector] Cannot find MySQLi extension to connect to database server "%s".', $Params['server']),
                    E_USER_ERROR
                );
            }
        }

        /**
         * Check connection
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   boolean
         */
        public function IsConnectionEstablished()
        {
            return is_object($this->ConnectionId);
        }

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
        public function Fetch($Resource, $ReturnAssoc = true)
        {                    
            $result = array();

            if (!is_null($Resource))
            {
                if ($ReturnAssoc)
                {
                    if (!($result = mysqli_fetch_assoc($Resource)))
                    {
                        $result = array();
                    }
                }
                else
                {
                    if (!($result = mysqli_fetch_row($Resource)))
                    {
                        $result = array();
                    }
                }
            }

            return $result;
        }
        
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
        public function FetchAll($Resource, $ReturnAssoc = true)
        {                    
            $result = array();

            while ($row = $this->Fetch($Resource, $ReturnAssoc))
            {
                if ($ReturnAssoc && isset($row['ID']))
                {
                    $result[(int)$row['ID']] = $row;
                }
                else
                {
                    $result[] = $row;
                }
            }

            return $result;
        }

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
        public function ExecQuery($Sql)
        {        
            $result = mysqli_query($this->ConnectionId, $Sql);        

            if (false === $result)
            {
                trigger_error
                (
                    sprintf('SQL error[%s]: %s. SQL Query: %s',  mysqli_errno($this->ConnectionId), mysqli_error($this->ConnectionId), str_replace(array('__BR__', ''), array("\n", "\r"), $Sql)),
                    E_USER_ERROR
                );
                $result = null;
            }        

            return $result;
        }

        /**
         * Get number of affected rows
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   int         
         */
        public function GetAffectedRows()
        {
            return (int)mysqli_affected_rows($this->ConnectionId);
        }

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
        public function GetNumRows($Resource)
        {        
            return (int)mysqli_num_rows($Resource);
        }

        /**
         * Get value of last increased autoincrement field
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   int         
         */
        public function GetLastInsertedId()
        {        		
            return (int)mysqli_insert_id($this->ConnectionId);
        }
        
        /**
         * Get tables list
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return   array         
         */
        public function GetTablesList()
        {
            $result = array();
            
            $query = "SHOW TABLES";
            $resource = $this->ExecQuery($query);            
            $result = $this->FetchAll($resource);
                                        
            foreach ($result as $key => &$table)
            {                
                $tName = "";
                foreach ($table as $tableName)
                {
                    $tName = $tableName;
                }
                $table = $tName;
            }
            
            return $result;
        }
        
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
        public function GetTableDescription($TableName)
        {
            $result = array();
            
            $query = "DESCRIBE $TableName";
            $resource = $this->ExecQuery($query);            
            $fields = $this->FetchAll($resource);                                
            
            foreach ($fields as $field)
            {                
                unset($field['Extra']);
                
                /* INTEGER */
                if (strpos($field['Type'], 'int') !== false)
                {
                    if (strpos($field['Type'], 'tinyint(1)') !== false)
                    {
                        $field['Type'] = 'BOOL';
                    }
                    else
                    {
                        if (strpos($field['Type'], 'tinyint') !== false)
                        {
                            $field['MINVAL'] = (strpos($field['Type'], 'unsigned') === false) ? -128 : 0;
                            $field['MAXVAL'] = (strpos($field['Type'], 'unsigned') === false) ? 127 : 255;
                        }
                        elseif (strpos($field['Type'], 'smallint') !== false)
                        {
                            $field['MINVAL'] = (strpos($field['Type'], 'unsigned') === false) ? -32768 : 0;
                            $field['MAXVAL'] = (strpos($field['Type'], 'unsigned') === false) ? 32767 : 65535;
                        }
                        elseif (strpos($field['Type'], 'mediumint') !== false)
                        {
                            $field['MINVAL'] = (strpos($field['Type'], 'unsigned') === false) ? -8388608 : 0;
                            $field['MAXVAL'] = (strpos($field['Type'], 'unsigned') === false) ? 8388607 : 16777215;
                        }
                        elseif (strpos($field['Type'], 'bigint') !== false)
                        {
                            $field['MINVAL'] = (strpos($field['Type'], 'unsigned') === false) ? -9223372036854775808 : 0;
                            $field['MAXVAL'] = (strpos($field['Type'], 'unsigned') === false) ? 9223372036854775807 : 18446744073709551615;
                        }
                        else                            
                        {
                            $field['MINVAL'] = (strpos($field['Type'], 'unsigned') === false) ? -2147483648 : 0;
                            $field['MAXVAL'] = (strpos($field['Type'], 'unsigned') === false) ? 2147483647 : 4294967295;
                        }
                        $field['Type'] = 'INT';                        
                    }
                }
                /* STRING */
                if (strpos($field['Type'], 'char') !== false)
                {
                    $field['MAXLEN'] = (int)str_replace(array('varchar', 'varchar', '(', ')'), '', $field['Type']);
                    $field['Type'] = 'STRING';
                }
                /* ENUM */
                if (strpos($field['Type'], 'enum') !== false)
                {                    
                    $field['VALUES'] = explode(',', str_replace(array('enum', '(', ')', '\''), '', $field['Type']));
                    $field['Type'] = 'ENUM';
                }
                /* DOUBLE */
                if (strpos($field['Type'], 'float') !== false || strpos($field['Type'], 'double') !== false)
                {
                    $field['Type'] = 'FLOAT';
                }        
                /* DATETIME */
                if (strpos($field['Type'], 'timestamp') !== false ||
                        strpos($field['Type'], 'time') !== false ||
                        strpos($field['Type'], 'datetime') !== false ||
                        strpos($field['Type'], 'date') !== false)
                {
                    $field['Type'] = 'DATETIME';
                }        
                $result[$field['Field']] = $field;
            }            
            return $result;
        }

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
        public function Escape($Value, $Like = false)
        {                
            $Value = mysqli_real_escape_string($this->ConnectionId, $Value);
            $Value = htmlspecialchars($Value, ENT_QUOTES);

            if ($Like)
            {            
                $Value = str_replace(array('%', '_'), array('\\%', '\\_'), $Value);
            }

            return (string)$Value;		
        }

        /**
         * Free resource of result resource
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param    mysqli_result  $Resource to query result resource
         */
        public function FreeResult($Resource)
        {        
            if (!is_null($Resource))
            {
                mysqli_free_result($Resource);
            }
        }

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
        public function PrepareSet($Dataset)
        {
            $result = $Dataset;

            if (count($result))
            {
                foreach ($result as $fieldName => $fieldValue)
                {
                    $result[$fieldName] = $this->PrepareField($fieldValue);                    
                }
            }

            return $result;
        }

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
        public function PrepareField($FieldValue)
        {
            $result = '';

            if (!is_numeric($FieldValue) && $FieldValue!= 'NULL' )
            {                    
                $result = (string)("'" . $this->Escape($FieldValue) . "'");
            }
            else
            {
                $result = (string)$FieldValue;
            }

            return $result;
        }

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
        public function Insert($TableName, $Dataset)
        {
            if (is_object($this->ConnectionId) && count($Dataset)>0)
            {
                $Dataset = $this->prepareSet($Dataset);                        

                $sql = "INSERT INTO " . $TableName . "
                        (" . implode(', ', array_keys($Dataset)) . ")
                            VALUES
                        (" . implode(', ', array_values($Dataset)) . ")";

                $this->execQuery($sql);            
            }

            return $this->GetLastInsertedId();
        }

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
        public function Update($TableName, $Dataset, $Condition = '')
        {
            if (is_object($this->ConnectionId) && count($Dataset)>0)
            {
                $Dataset = $this->PrepareSet($Dataset);

                $setValues = array();

                foreach ($Dataset as $fieldName => $fieldValue)
                {
                    $setValues[] = $fieldName . '=' . $fieldValue;
                }                        

                $sql = "UPDATE " . $TableName .
                        " SET " . (implode(", ", $setValues)) . 
                        (($Condition != '') ? " WHERE " . $Condition : "");

                $this->execQuery($sql);            
            }

            return $this->GetAffectedRows();
        }

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
        public function Delete($TableName, $Condition = '', $Force = false)
        {
            if (is_object($this->ConnectionId))
            {        
                $sql = "DELETE FROM " . $TableName . (($Condition != '') ? " WHERE " . $Condition : "");

                if (($Condition != '') || ($Condition == '' && $Force))
                {
                    $this->execQuery($sql);
                }
                else
                {
                    trigger_error
                    (
                        sprintf('Attempting to delete ALL rows from table "%s" was declined.', $TableName),
                        E_USER_WARNING
                    );
                }
            }

            return $this->GetAffectedRows();
        }

        /**
         * Close establishing connection 
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function Close()
        {
            if (is_object($this->ConnectionId))
            {
                mysqli_close($this->ConnectionId);
                $this->ConnectionId = null;
            }
        }          
    }
}