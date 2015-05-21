<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Entity
 */
namespace InfEra\System\Entity
{   
    use InfEra\Application as Application;
    /**
     * Class for Database Set     
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[System]
     * @subpackage Entity
     */    
    class DbSet
    {
        /**
         * INNER JOIN
         */
        const JOIN_INNER = "INNER";
        
        /**
         * LEFT JOIN
         */
        const JOIN_LEFT  = "LEFT";
        
        /**
         * RIGHT JOIN
         */
        const JOIN_RIGH  = "RIGHT";
        
        /**
         * ORDER BY <field> ASC
         */
        const ORDER_ASC = "ASC";
        
        /**
         * ORDER BY <field> DESC
         */
        const ORDER_DESC = "DESC";
        
        #################################################################
        #################################################################
        #################################################################
        
        /**
         * Name of class with model
         * @var string
         */
        private $ModelName = NULL;
        
        /**
         * Connection alias
         * @var string
         */
        private $ConnectionAlias = "";
                
        /**
         * WHERE-condition
         * @var string
         */
        private $Condition = "";
        
        /**
         * Table name
         * @var string
         */
        private $TableName = "";
        
        /**
         * Table description
         * @var array
         */
        private $TableDescription = array();
        
        /**
         * Fields to select
         * @var string 
         */
        private $Fields = "*";
        
        /**
         * Tables for join
         * @var string
         */
        private $Joins = "";
        
        /**
         * Order of resultset
         * @var string
         */
        private $Order = "";
        
        /**
         * Offset and number of records to be selected
         * @var string
         */
        private $Limit = "";
        
        /**
         * Constructor
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $ConnectionAlias Connection alias
         * @param string $Model Name of class with model for resultset
         */        
        public function __construct($ConnectionAlias, $Model)
        {            
            $this->ConnectionAlias = $ConnectionAlias;
            $this->ModelName = $Model;
                        
            $ShortModelName = explode("\\", $Model);
            if (is_array($ShortModelName))
            {
                $ShortModelName = array_reverse($ShortModelName);
                $ShortModelName = $ShortModelName[0];
            }
            else
            {
                $ShortModelName = $Model;
            }
            
            $this->TableName = $ShortModelName;
            if (substr($ShortModelName, strlen($ShortModelName)-1) == "s")
            {
                $this->TableName .= "es";
            }
            elseif (substr($ShortModelName, strlen($ShortModelName)-1) == "y")
            {
                $this->TableName = substr($this->TableName, 0, strlen($ShortModelName)-1) . "ies";
            }
            else
            {
                $this->TableName .= "s";
            }
            
            ######### CHECKING ##########            
            $tables = Application::$DBContext->Get($this->ConnectionAlias)->GetTablesList();                                                    
            
            if (in_array($this->TableName, $tables))
            {                
                $this->TableDescription = Application::$DBContext->Get($this->ConnectionAlias)->GetTableDescription($this->TableName);
                // @TODO Check Model fields and fields in table
            }
            else
            {
                trigger_error
                (
                    "[DbSet] Table \"$this->TableName\" not found for model \"$this->ModelName\"",
                    E_USER_ERROR
                );
            }                        
        }
        
        /**
         * Reset DbSet to default (empty) state
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         */
        public function Reset()
        {
            $this->Fields = "*";
            $this->Condition = "";            
            $this->Joins = "";
            $this->Order = "";
            $this->Limit = "";
        }
        
        /**
         * Refresh current dataset
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @return \InfEra\System\Collections\Dictionary Dictionary of Model's objects
         */
        public function Refresh()
        {
            return $this->Select($this->Fields);
        }
        
        /**
         * Set WHERE-condition
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Condition Where-condition
         * 
         * @return \InfEra\System\Entity\DbSet Current DbSet object
         */
        public function Where($Condition)
        {
            $this->Condition = $Condition;
            return $this;
        }
        
        /**
         * Set parameters of offset and number of records to be selected
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param type $StartFrom Start position 
         * @param type $Length Number of records to select
         * 
         * @return \InfEra\System\Entity\DbSet Current DbSet object
         */
        public function Limit($StartFrom = 0, $Length = 10)
        {
            $this->Limit = "LIMIT $StartFrom, $Length";
            return $this;
        }
        
        /**
         * Add ordering condition
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $FieldName Field name
         * @param string $Direction Ordering direction
         * Values:
         * - DbSet::ORDER_ASC - ORDER BY <field> ASC (default)
         * - DbSet::ORDER_DESC - ORDER BY <field> DESC
         * 
         * @return \InfEra\System\Entity\DbSet Current DbSet object
         */
        public function OrderBy($FieldName, $Direction = DbSet::ORDER_ASC)
        {
            $this->Order .= (($this->Order == "") ? "ORDER BY " : ", ") . $FieldName . " $Direction";
            return $this;
        }
        
        /**
         * Add JOIN-condition
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $TableName Table name
         * @param string $On JOIN-condition
         * @param string $Type Type of JOIN<br/>
         * Values:
         * - DbSet::JOIN_INNER - INNER JOIN (default)
         * - DbSet::JOIN_LEFT - INNER LEFT
         * - DbSet::JOIN_RIGHT - INNER RIGHT
         * 
         * @return \InfEra\System\Entity\DbSet Current DbSet object
         */
        public function Join($TableName, $On, $Type = DbSet::JOIN_INNER)
        {
            $this->Joins .= " $Type JOIN $TableName ON $On";
            return $this;
        }
        
        /**
         * Select records from database
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param string $Fields Fields to select
         * 
         * @return \InfEra\System\Collections\Dictionary Dictionary of Model's objects
         */
        public function Select($Fields = "*")
        {
            $ResultSet = array();
            $Result = array();
            $this->Fields = $Fields;
            
            $query = "SELECT
                        $this->Fields
                      FROM
                        $this->TableName
                      $this->Joins
                      WHERE
                        $this->Condition
                      $this->Order
                      $this->Limit";
            
            $resource = Application::$DBContext->Get($this->ConnectionAlias)->ExecQuery($query);            
            $ResultSet = Application::$DBContext->Get($this->ConnectionAlias)->FetchAll($resource);
            
            foreach ($ResultSet as $Key => $Record)
            {
                $Result[$Key] = $this->FillModel($Record);
            }                        
            return $Result;
        }
        
        /**
         * Add object to DbSet and 
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param object $Object Model's object
         */
        public function Add($Object)
        {       
            $result = 0;
            if ('\\' . get_class($Object) == $this->ModelName)
            {
                $set = array();
                // @TODO Check fields with Model
                foreach ($Object as $PropertyName => $PropertyValue)
                {
                    if (is_object($PropertyValue))
                    {
                        switch (get_class($PropertyValue))
                        {
                            case 'DateTime' :
                            {
                                $PropertyValue = $PropertyValue->format('Y-m-d H:i:s');
                                break;
                            }
                        }
                    }
                    if ($PropertyValue === NULL)
                    {
                        $PropertyValue = 'NULL';
                    }
                    $set[$PropertyName] = $PropertyValue;                    
                }
                if (isset($set['ID']))
                {
                    unset($set['ID']);
                }
                $result = Application::$DBContext->Get($this->ConnectionAlias)->Insert($this->TableName, $set);
            }  
            else
            {
                trigger_error
                (
                    "[DbSet] Invalid object type \"\\" . get_class($Object) . "\". Necessary to use $this->ModelName",
                    E_USER_ERROR
                );
            }
            
            return $result;
        }
        
        /**
         * Save object's changes to database
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param object $Object Model's object
         */
        public function Store($Object)
        {            
            $result = 0;
            if ('\\' . get_class($Object) == $this->ModelName)
            {
                $set = array();
                // @TODO Check fields with Model
                foreach ($Object as $PropertyName => $PropertyValue)
                {
                    if (is_object($PropertyValue))
                    {
                        switch (get_class($PropertyValue))
                        {
                            case 'DateTime' :
                            {
                                $PropertyValue = $PropertyValue->format('Y-m-d H:i:s');
                                break;
                            }
                        }
                    }
                    if ($PropertyValue === NULL)
                    {
                        $PropertyValue = 'NULL';
                    }
                    $set[$PropertyName] = $PropertyValue;                    
                }
                if (isset($set['ID']))
                {
                    unset($set['ID']);
                }
                $result = Application::$DBContext->Get($this->ConnectionAlias)->Update($this->TableName, $set, 'ID = ' . $Object->ID);
            }  
            else
            {
                trigger_error
                (
                    "[DbSet] Invalid object type \"\\" . get_class($Object) . "\". Necessary to use $this->ModelName",
                    E_USER_ERROR
                );
            }
            
            return $result;
        }
        
        /**
         * Delete object from database
         * 
         * @author     Alexander A. Popov <versusms@gmail.com>
         * @version    1.0
         * 
         * @param object $Object Model's object
         */
        public function Delete($Object)
        {            
            if ('\\' . get_class($Object) == $this->ModelName)
            {
                Application::$DBContext->Get($this->ConnectionAlias)->Delete($this->TableName, 'ID = ' . $Object->ID);
            }  
            else
            {
                trigger_error
                (
                    "[DbSet] Invalid object type \"\\" . get_class($Object) . "\". Necessary to use $this->ModelName",
                    E_USER_ERROR
                );
            }
        }
        
        /**
         * Fill model with data
         */
        private function FillModel($Data)
        {
            $Result = new $this->ModelName();
            
            foreach ($Result as $PropertyName => &$PropertyValue)
            {
                if (isset($this->TableDescription[$PropertyName]) && isset($Data[$PropertyName]))
                {
                    switch($this->TableDescription[$PropertyName]['Type'])
                    {
                        case 'BOOL' : 
                        {
                            $PropertyValue = (bool)(int)$Data[$PropertyName];
                            break;
                        }
                        case 'INT' : 
                        {
                            $PropertyValue = (int)$Data[$PropertyName];
                            break;
                        }
                        case 'ENUM' :
                        case 'STRING' : 
                        {
                            $PropertyValue = (string)$Data[$PropertyName];
                            break;
                        }
                        case 'FLOAT' : 
                        {
                            $PropertyValue = (float)$Data[$PropertyName];
                            break;
                        }
                        case 'DATETIME' : 
                        {
                            $PropertyValue = new \Datetime($Data[$PropertyName]);
                            break;
                        }
                    }                    
                }                
            }
            
            return $Result;
        }
    }
}