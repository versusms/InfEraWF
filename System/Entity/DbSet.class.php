<?php
/**
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Entity
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Entity;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Collections\Dictionary;
use InfEra\WAFP\System\Entity\Exceptions\DbSetException;
use InfEra\WAFP\System\Mvc\Model;
use InfEra\WAFP\System\Reflection\DocComments;

/**
 * Class for Database Set
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
    const JOIN_LEFT = "LEFT";

    /**
     * RIGHT JOIN
     */
    const JOIN_RIGH = "RIGHT";

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
    protected $ModelName = NULL;

    /**
     * Connection alias
     * @var string
     */
    protected $ConnectionAlias = "";

    /**
     * WHERE-condition
     * @var string
     */
    protected $Condition = "";

    /**
     * Table name
     * @var string
     */
    protected $TableName = "";

    /**
     * Table description
     * @var array
     */
    protected $TableDescription = array();

    /**
     * Model Specification
     * @var array
     */
    protected $ModelSpecification = array();

    /**
     * Fields to select
     * @var string
     */
    protected $Fields = "*";

    /**
     * Tables for join
     * @var string
     */
    protected $Joins = "";

    /**
     * Order of resultset
     * @var string
     */
    protected $Order = "";

    /**
     * Offset and number of records to be selected
     * @var string
     */
    protected $Limit = "";

    /**
     * Constructor
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $ConnectionAlias Connection alias
     * @param string $Model Name of class with model for resultset
     */
    public function __construct(string $ConnectionAlias, string $Model)
    {
        $TM = new $Model();
        $this->ConnectionAlias = $ConnectionAlias;
        $this->ModelSpecification = $TM->Specification;
        $this->ModelName = $this->ModelSpecification->ModelName;
        $this->TableName = $this->ModelSpecification->TableName;

        ######### CHECKING ##########
        $tables = Application::$DBContext->Get($this->ConnectionAlias)->GetTablesList();

        if (in_array($this->TableName, $tables)) {
            $this->TableDescription = Application::$DBContext->Get($this->ConnectionAlias)->GetTableDescription($this->TableName);
        } else {
            throw new DbSetException(
                "[DbSet] Table \"$this->TableName\" not found for model \"$this->ModelName\"",
                DbSetException::TABLE_NOT_FOUND
            );
        }
    }

    /**
     * Reset DbSet to default (empty) state
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @return Dictionary Dictionary of Model's objects
     */
    public function Refresh() : Dictionary
    {
        return $this->Select($this->Fields);
    }

    /**
     * Set WHERE-condition
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $Condition Where-condition
     *
     * @return DbSet Current DbSet object
     */
    public function Where(string $Condition) : DbSet
    {
        $this->Condition = $Condition;
        return $this;
    }

    /**
     * Set parameters of offset and number of records to be selected
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param int $StartFrom Start position
     * @param int $Length Number of records to select
     *
     * @return DbSet Current DbSet object
     */
    public function Limit(int $StartFrom = 0, int $Length = 10) : DbSet
    {
        $this->Limit = "LIMIT $StartFrom, $Length";
        return $this;
    }

    /**
     * Add ordering condition
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $FieldName Field name
     * @param string $Direction Ordering direction
     * Values:
     * - DbSet::ORDER_ASC - ORDER BY <field> ASC (default)
     * - DbSet::ORDER_DESC - ORDER BY <field> DESC
     *
     * @return DbSet Current DbSet object
     */
    public function OrderBy(string $FieldName, string $Direction = DbSet::ORDER_ASC) : DbSet
    {
        $this->Order .= (($this->Order == "") ? "ORDER BY " : ", ") . $FieldName . " $Direction";
        return $this;
    }

    /**
     * Add JOIN-condition
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
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
     * @return DbSet Current DbSet object
     */
    public function Join(string $TableName, string $On, string $Type = DbSet::JOIN_INNER) : Dbset
    {
        $this->Joins .= " $Type JOIN $TableName ON $On";
        return $this;
    }

    /**
     * Select records from database
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $Fields Fields to select
     * @param bool $LazyLoad Load all linked entities only when call it
     *
     * @return Dictionary Dictionary of Model's objects
     */
    public function Select(string $Fields = "*", bool $IndexesByKeys = true)
    {
        $ResultSet = array();
        $Result = new \InfEra\WAFP\System\Collections\Dictionary();
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
        $ResultSet = Application::$DBContext->Get($this->ConnectionAlias)->FetchAll($resource);

        $zeroIndex = 0;
        foreach ($ResultSet as $Key => $Record) {
            $newRecord = new $this->ModelName();
            $newRecord->FillWithData($Record);
            if ($IndexesByKeys) {
                $Result[$Key] = $newRecord;
            } else {
                $Result[$zeroIndex++] = $newRecord;
            }
        }
        return $Result;
    }

    /**
     * Get records count by query
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return int
     */
    public function Count() : int
    {
        $query = "SELECT
                        COUNT(1) as RECORDSCOUNT
                      FROM
                        $this->TableName
                      $this->Joins " .
            (($this->Condition != '') ? "WHERE
                        $this->Condition" : "");

        $resource = Application::$DBContext->Get($this->ConnectionAlias)->ExecQuery($query);
        $ResultSet = Application::$DBContext->Get($this->ConnectionAlias)->FetchAll($resource);

        return (int)$ResultSet[0]['RECORDSCOUNT'];
    }

    /**
     * Add object to DbSet and
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param Model $Object Model's object
     */
    public function Add(Model &$Object) : int
    {
        $result = 0;
        if (get_class($Object) == $this->ModelName) {
            $set = array();
            // @TODO Check fields with Model
            foreach ($Object as $PropertyName => $PropertyValue) {
                if (is_object($PropertyValue)) {
                    switch (get_class($PropertyValue)) {
                        case 'DateTime' : {
                            $PropertyValue = $PropertyValue->format('Y-m-d H:i:s');
                            break;
                        }
                    }
                }
                if ($PropertyValue === NULL) {
                    $PropertyValue = 'NULL';
                }
                $set[$PropertyName] = $PropertyValue;
            }
            if (isset($set['ID']) && $set['ID'] === 0) {
                unset($set['ID']);
            }

            $result = Application::$DBContext->Get($this->ConnectionAlias)->Insert($this->TableName, $set);
            if (property_exists($Object, 'ID')) {
                $Object->ID = $result;
            }
        } else {
            throw new DbSetException(
                "[DbSet] Invalid object type \"" . get_class($Object) . "\". Necessary to use $this->ModelName",
                DbSetException::INVALID_OBJECT_TYPE
            );
        }

        return $result;
    }

    /**
     * Save object's changes to database
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param Model $Object Model's object
     */
    public function Store(Model $Object) : int
    {
        $result = 0;

        if (get_class($Object) == $this->ModelName) {
            $set = array();
            // @TODO Check fields with Model
            foreach ($Object as $PropertyName => $PropertyValue) {
                if (is_object($PropertyValue)) {
                    switch (get_class($PropertyValue)) {
                        case 'DateTime' : {
                            $PropertyValue = $PropertyValue->format('Y-m-d H:i:s');
                            break;
                        }
                    }
                }
                if ($PropertyValue === NULL) {
                    $PropertyValue = 'NULL';
                }
                $set[$PropertyName] = $PropertyValue;
            }
            if (isset($set['ID'])) {
                unset($set['ID']);
            }
            $result = Application::$DBContext->Get($this->ConnectionAlias)->Update($this->TableName, $set, 'ID = ' . $Object->ID);
        } else {
            throw new DbSetException(
                "[DbSet] Invalid object type \"" . get_class($Object) . "\". Necessary to use $this->ModelName",
                DbSetException::INVALID_OBJECT_TYPE
            );
        }

        return $result;
    }

    /**
     * Delete object from database
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param Model $Object Model's object
     */
    public function Delete(Model $Object)
    {
        if ('\\' . get_class($Object) == $this->ModelName) {
            Application::$DBContext->Get($this->ConnectionAlias)->Delete($this->TableName, 'ID = ' . $Object->ID);
        } else {
            throw new DbSetException(
                "[DbSet] Invalid object type \"\\" . get_class($Object) . "\". Necessary to use $this->ModelName",
                DbSetException::INVALID_OBJECT_TYPE
            );
        }
    }
}