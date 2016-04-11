<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 09.11.2015 16:48
 * @package InfEra\System\Entity
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Mvc;

use InfEra\WAFP\Application;
use InfEra\WAFP\System\Collections\Dictionary;
use InfEra\WAFP\System\Reflection\DocComments;
use InfEra\WAFP\System\Entity\Exceptions\ModelException;

/**
 * Class Model
 * Base Model class
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Entity
 */
// @TODO Custom PK
// @TODO Different Connections
// @TODO Constructor with fields
class Model
{
    /**
     * [DESCRIPTION]
     * @var \InfEra\System\Entity\ModelSpecification
     */
    private $Specification = NULL;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->__SpecifyModel();
        $this->__Init();
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @throws ModelException
     */
    private function __SpecifyModel()
    {
        if (is_null($this->Specification = Application::$Cache->Get('Model_' . get_class($this))))
        {
            $this->Specification = new ModelSpecification;
            $this->Specification->ModelName = get_class($this);
            $ModelNameArray = explode('\\', $this->Specification->ModelName);
            $this->Specification->EntityName = end($ModelNameArray);

            $ModelDescription = DocComments::Parse(
                (new \ReflectionClass($this->Specification->ModelName))->getDocComment()
            );

            if (array_key_exists('TableName', $ModelDescription) && trim($ModelDescription['TableName']) != '') {
                $this->Specification->TableName = strtoupper(trim($ModelDescription['TableName']));
            } else {
                throw new ModelException(
                    "[Model] Table not defined in annotation for model " . $this->Specification->ModelName,
                    ModelException::NO_TABLENAME_IN_MODEL_SPECIFICATION
                );
            }

            $this->Specification->Fields = array();
            $ModelDescription = new \ReflectionClass($this->Specification->ModelName);
            $ModelFields = $ModelDescription->getProperties();

            foreach ($ModelFields as $field) {
                $FieldDescription = DocComments::Parse($field->getDocComment());
                if (array_key_exists('var', $FieldDescription)) {
                    if (array_key_exists('virtual', $FieldDescription)) {
                        // virtual field
                        if ($field->isProtected())
                        {
                            if (array_key_exists('entitylink', $FieldDescription)) {
                                if ($FieldDescription['var'] == '\InfEra\WAFP\System\Collections\Dictionary' && !array_key_exists('entity', $FieldDescription)) {
                                    throw new ModelException(
                                        "[Model] No entity defined for virtual field \"$field->name\" for model " . $this->Specification->ModelName,
                                        ModelException::NO_ENTITY_FOR_VIRTUAL_FIELD
                                    );
                                }
                                $this->Specification->Fields[$field->name] = new FieldSpecification();
                                $this->Specification->Fields[$field->name]->Virtual = true;
                                $this->Specification->Fields[$field->name]->Type = $FieldDescription['var'];
                                $this->Specification->Fields[$field->name]->Nullable = (array_key_exists('nullable', $FieldDescription));
                                $this->Specification->Fields[$field->name]->DefaultValue =
                                    (array_key_exists('default', $FieldDescription))
                                        ? (strtolower(trim($FieldDescription['default'])) == 'null'
                                        ? NULL : 'object')
                                        : (($this->Specification->Fields[$field->name]->Nullable)
                                        ? NULL : 'object');
                                $this->Specification->Fields[$field->name]->Key = (array_key_exists('key', $FieldDescription))
                                    ? $FieldDescription['key']
                                    : NULL;
                                $this->Specification->Fields[$field->name]->Entity = (array_key_exists('entity', $FieldDescription))
                                    ? $FieldDescription['entity']
                                    : NULL;
                                if (array_key_exists('entity', $FieldDescription))
                                {
                                    $FieldArray = explode('\\',$FieldDescription['entity']);
                                    $this->Specification->Fields[$field->name]->EntityName = end($FieldArray);
                                }
                                else
                                {
                                    $this->Specification->Fields[$field->name]->EntityName = NULL;
                                }
                                if ($FieldDescription['var'] == '\InfEra\WAFP\System\Collections\Dictionary')
                                {
                                    if (array_key_exists('relation', $FieldDescription))
                                    {
                                        switch ($FieldDescription['relation'])
                                        {
                                            case 'single' : {
                                                $this->Specification->Fields[$field->name]->Relation = FieldSpecification::RL_SINGLE;
                                                break;
                                            }
                                            case 'multiple' : {
                                                $this->Specification->Fields[$field->name]->Relation = FieldSpecification::RL_MULTIPLE;
                                                break;
                                            }
                                            case 'collection' :
                                            default : {
                                                $this->Specification->Fields[$field->name]->Relation = FieldSpecification::RL_COLLECTION;
                                                break;
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if ($FieldDescription['var'] == '\InfEra\System\Collections\Dictionary')
                                        {
                                            $this->Specification->Fields[$field->name]->Relation = FieldSpecification::RL_COLLECTION;
                                        }
                                        else
                                        {
                                            $this->Specification->Fields[$field->name]->Relation = FieldSpecification::RL_SINGLE;
                                        }
                                    }
                                }
                                $this->Specification->Fields[$field->name]->EntityLink =  $FieldDescription['entitylink'];
                            } else {
                                throw new ModelException(
                                    "[Model] No entity link defined for virtual field \"$field->name\" for model " . $this->Specification->ModelName,
                                    ModelException::NO_ENTITY_LINK_FOR_VIRTUAL_FIELD
                                );
                            }
                        } else {
                            throw new ModelException(
                                "[Model] Virtual field \"$field->name\" had to be protected for model " . $this->Specification->ModelName,
                                ModelException::VIRTUAL_IS_NOT_PROTECTED
                            );
                        }
                    }
                    else
                    {
                        // regular field
                        $this->Specification->Fields[$field->name] = new FieldSpecification();
                        $this->Specification->Fields[$field->name]->Virtual = false;
                        $this->Specification->Fields[$field->name]->Type = $FieldDescription['var'];
                        $this->Specification->Fields[$field->name]->Nullable = (array_key_exists('nullable', $FieldDescription));
                        $this->Specification->Fields[$field->name]->Key = (array_key_exists('key', $FieldDescription))
                            ? $FieldDescription['key']
                            : NULL;

                        switch ($FieldDescription['var']) {
                            case 'BOOL' : {
                                $this->Specification->Fields[$field->name]->DefaultValue =
                                    (array_key_exists('default', $FieldDescription))
                                        ? (trim($FieldDescription['default']) == 'true' ? true : false)
                                        : (($this->Specification->Fields[$field->name]->Nullable)
                                        ? NULL : false);
                                break;
                            }
                            case 'INT' : {
                                $this->Specification->Fields[$field->name]->DefaultValue =
                                    (array_key_exists('default', $FieldDescription))
                                        ? (int)trim($FieldDescription['default'])
                                        : (($this->Specification->Fields[$field->name]->Nullable)
                                        ? NULL : 0);
                                break;
                            }
                            case 'ENUM' :
                            case 'STRING' : {
                                $this->Specification->Fields[$field->name]->DefaultValue =
                                    (array_key_exists('default', $FieldDescription))
                                        ? (string)trim($FieldDescription['default'])
                                        : (($this->Specification->Fields[$field->name]->Nullable)
                                        ? NULL : '');
                                break;
                            }
                            case 'FLOAT' : {
                                $this->Specification->Fields[$field->name]->DefaultValue =
                                    (array_key_exists('default', $FieldDescription))
                                        ? (float)trim($FieldDescription['default'])
                                        : (($this->Specification->Fields[$field->name]->Nullable)
                                        ? NULL : 0.0);
                                break;
                            }
                            case 'DATETIME' : {
                                $this->Specification->Fields[$field->name]->DefaultValue =
                                    (array_key_exists('default', $FieldDescription))
                                        ? new \DateTime(trim($FieldDescription['default']))
                                        : (($this->Specification->Fields[$field->name]->Nullable)
                                        ? NULL : new \DateTime());
                                break;
                            }
                            default : {
                                throw new ModelException(
                                    "[Model] Unknown type \"" . $FieldDescription['var'] . "\" defined for field \"$field->name\" for model " . $this->Specification->ModelName,
                                    ModelException::UNKNOWN_TYPE_FOR_REGULAR_FIELD
                                );
                            }
                        }
                    }
                } else {
                    throw new ModelException(
                        "[Model] No type defined for field \"$field->name\" for model " . $this->Specification->ModelName,
                        ModelException::NO_TYPE_FOR_FIELD
                    );
                }
            }
            Application::$Cache->Add('Model_' . $this->Specification->ModelName, $this->Specification);
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     */
    private function __Init()
    {
        foreach ($this->Specification->Fields as $fieldName => $fieldSpecification)
        {
            $this->$fieldName = (!$fieldSpecification->Virtual)
                ? $fieldSpecification->DefaultValue
                : (is_null($fieldSpecification->DefaultValue)
                    ? NULL
                    : new $fieldSpecification->Type());
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $Data
     * @throws ModelException
     */
    public function FillWithData($Data)
    {
        foreach ($this->Specification->Fields as $key => $field)
        {
            if (array_key_exists($key, $Data)
                && !$this->Specification->Fields[$key]->Virtual)
            {
                switch ($this->Specification->Fields[$key]->Type) {
                    case 'BOOL' : {
                        $this->$key = ($this->Specification->Fields[$key]->Nullable && is_null($Data[$key]))
                            ? NULL
                            :(bool)(int)$Data[$key];
                        break;
                    }
                    case 'INT' : {
                        $this->$key = ($this->Specification->Fields[$key]->Nullable && is_null($Data[$key]))
                            ? NULL
                            :(int)$Data[$key];
                        break;
                    }
                    case 'ENUM' :
                    case 'STRING' : {
                        $this->$key = ($this->Specification->Fields[$key]->Nullable && is_null($Data[$key]))
                            ? NULL
                            :(string)$Data[$key];;
                        break;
                    }
                    case 'FLOAT' : {
                        $this->$key = ($this->Specification->Fields[$key]->Nullable && is_null($Data[$key]))
                            ? NULL
                            :(float)$Data[$key];;
                        break;
                    }
                    case 'DATETIME' : {
                        $this->$key = ($this->Specification->Fields[$key]->Nullable && is_null($Data[$key]))
                            ? NULL
                            :new \DateTime($Data[$key]);
                        break;
                    }
                    default : {
                        throw new ModelException(
                            "[Model] Unknown type \"" . $this->Specification->Fields[$field->name]->Type . "\" defined for field \"$field->name\" for model " . $this->Specification->ModelName,
                            ModelException::UNKNOWN_TYPE_FOR_REGULAR_FIELD
                        );
                    }
                }
            }
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $FieldName
     */
    private function __LoadVirtualField($FieldName)
    {
        if ($this->Specification->Fields[$FieldName]->Type == '\InfEra\WAFP\System\Collections\Dictionary')
        {
            $EntityLink = $this->Specification->Fields[$FieldName]->EntityLink;

            switch ($this->Specification->Fields[$FieldName]->Relation)
            {
                case FieldSpecification::RL_COLLECTION : {
                    $this->$FieldName = Application::$DBContext->GetDbSet($this->Specification->Fields[$FieldName]->Entity)
                        ->Where("$EntityLink = $this->ID")
                        ->Select("*");

                    break;
                }
                case FieldSpecification::RL_MULTIPLE : {

                    $Association = Application::$DBContext->GetDbAssociation($EntityLink);
                    $LeftKey = $this->Specification->EntityName .
                        $this->Specification->GetPrimaryKeyFieldName();

                    $RightEntity = new $this->Specification->Fields[$FieldName]->Entity();
                    $RightKey = $this->Specification->Fields[$FieldName]->EntityName .
                        $RightEntity->Specification->GetPrimaryKeyFieldName();

                    $EntitiesLinks = $Association
                        ->Where("$LeftKey = $this->ID")
                        ->Select($RightKey);
                    $AssKeys = array();
                    foreach ($EntitiesLinks as $association)
                    {
                        $AssKeys[] = $association[$RightKey];
                    }

                    if (count($AssKeys) > 0)
                    {
                        $AssKeys = implode(', ', $AssKeys);

                        $this->$FieldName = Application::$DBContext->GetDbSet($this->Specification->Fields[$FieldName]->Entity)
                            ->Where($RightEntity->Specification->GetPrimaryKeyFieldName() . ' IN (' . $AssKeys . ')')
                            ->Select();
                    }
                    else
                    {
                        $this->$FieldName = new Dictionary();
                    }

                    break;
                }
            }
        }
        else
        {
            $EntityLink = $this->Specification->Fields[$FieldName]->EntityLink;

            if (!is_null($this->$EntityLink))
            {
                $EntityValue = Application::$DBContext->GetDbSet($this->Specification->Fields[$FieldName]->Type)
                    ->Where('ID = ' . $this->$EntityLink)
                    ->Select("*", false);
                if ($EntityValue->Count() > 0)
                {
                    $this->$FieldName = $EntityValue[0];
                }
            }
        }
        //@TODO Cache data
        //$this->Specification->Fields[$FieldName]->Loaded = true;
    }

    /**
     * Getter for model specification and virtual fields
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $name string
     * @return mixed
     */
    public function __get(string $name)
    {
        if (($name !== 'Specification'
            && array_key_exists($name, $this->Specification->Fields)
            && !$this->Specification->Fields[$name]->Virtual)
        || ($name !== 'Specification'
                && !array_key_exists($name, $this->Specification->Fields)))
        {
            var_dump($name, $this->Specification);
            throw new ModelException(
                "[Model] Access to unknown field \"$name\" for model " . $this->Specification->ModelName,
                ModelException::ACCESS_TO_UNKNOWN_FIELD
            );
        }
        elseif ($name !== 'Specification'
            && $this->Specification->Fields[$name]->Virtual
            && !$this->Specification->Fields[$name]->Loaded)
        {
            $this->__LoadVirtualField($name);
        }

        return $this->$name;
    }

    /**
     * Setter for virtual fields only
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $name string
     * @param $value mixed
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->Specification->Fields)
            && $this->Specification->Fields[$name]->Virtual)
        {
            $this->$name = $value;
        }
        else
        {
            throw new ModelException(
                "[Model] Access to unknown field \"$name\" for model " . $this->Specification->ModelName,
                ModelException::ACCESS_TO_UNKNOWN_FIELD
            );
        }
    }

    public function __debugInfo()
    {
        $fields = array();

        foreach ($this->Specification->Fields as $key => $value)
        {
            $fields[$key] = $this->$key;
        }

        return $fields;
    }
}


//TODO ReadOnly Properties
/**
 * Class ModelSpecification
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Entity
 */
class ModelSpecification
{
    public $ModelName = '';
    public $EntityName = '';
    public $TableName = '';
    public $Fields = array();

    public function GetPrimaryKeyFieldName()
    {
        $result = '';

        foreach ($this->Fields as $fieldsName => $field)
        {
            if ($field->Key == FieldSpecification::FK_PRIMARY)
            {
                $result = $fieldsName;
                break;
            }
        }

        return $result;
    }
}

//TODO ReadOnly Properties
class FieldSpecification
{
    const FK_NONE = '';
    const FK_PRIMARY = 'PRIMARY';
    const FK_INDEX = 'INDEX';

    const RL_SINGLE = 'SINGLE';
    const RL_COLLECTION = 'COLLECTION';
    const RL_MULTIPLE = 'MULTIPLE';

    public $Virtual = false;
    public $Type = '';
    public $Nullable = true;
    public $DefaultValue = NULL;
    public $Key = FieldSpecification::FK_NONE;
    public $Entity = NULL;
    public $EntityName = NULL;
    public $EntityLink = NULL;
    public $Relation = NULL;
    public $Loaded = false;
}