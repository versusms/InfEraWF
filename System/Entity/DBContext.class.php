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
use InfEra\WAFP\System\Entity\Connectors\IConnector;
use InfEra\WAFP\System\Entity\DbSet;
use InfEra\WAFP\System\Entity\DbAssociation;

/**
 * Database context class.
 * Allowed to create different connections.
 * Connection will be created at the first call of <b>Get()</b> or <b>GetDbSet()</b>methods.
 *
 * @author     Alexander A. Popov <alejandro.popov@outlook.com>
 * @version    1.0
 * @package    InfEra[System]
 * @subpackage Entity
 */
class DBContext
{
    /**
     * Array of created connections
     * @var array
     */
    private $Connections = array();

    /**
     * Create connection to database
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $ConnectionAlias Name of connection
     * Needs to be declared in config file.
     * If empty - will create a <b>default</b> connection.
     */
    private function CreateConnection(string $ConnectionAlias = "")
    {

        if ($connectionParams = Application::$Configuration->DBContext[$ConnectionAlias]) {
            if ($connectionParams['type'] != '') {
                $fullConnectorName = '\InfEra\WAFP\System\Entity\Connectors\\' . $connectionParams['type'];
                $this->Connections[$ConnectionAlias] = new $fullConnectorName($connectionParams['params']);
                if (!($this->Connections[$ConnectionAlias] instanceof Connectors\IConnector)) {
                    trigger_error
                    (
                        "[DBContext] Connector \"$ConnectionAlias\" is not an instance of \"InfEra\WAFP\System\Entity\Connectors\IConnector\"",
                        E_USER_ERROR
                    );
                }
            } else {
                trigger_error
                (
                    "[DBContext] DataBase type is not defined",
                    E_USER_ERROR
                );
            }
        }
    }

    /**
     * Getting connection resource by alias
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $ConnectionAlias Connection alias.
     * If empty - returns the <b>default</b> connection
     *
     * @return IConnector
     */
    public function Get(string $ConnectionAlias = "") : IConnector
    {
        $result = NULL;

        if ($ConnectionAlias == "") {
            $ConnectionAlias = "default";
        }

        if (isset(Application::$Configuration->DBContext[$ConnectionAlias])) {
            if (!isset($this->Connections[$ConnectionAlias])) {
                $this->CreateConnection($ConnectionAlias);
            }

            $result = $this->Connections[$ConnectionAlias];
        } else {
            trigger_error
            (
                sprintf("[DBContext] DataBase connector with alias '%s' not found", $ConnectionAlias),
                E_USER_ERROR
            );
        }

        return $result;
    }

    /**
     * Getting DbSet by name and connection alias
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $ModelName Name of class with model
     * @param string $ConnectionAlias Connection alias.
     * If empty - uses the <b>default</b> connection
     *
     * @return DbSet
     */
    public function GetDbSet(string $ModelName, string $ConnectionAlias = "") : DbSet
    {
        $result = NULL;

        if ($ConnectionAlias == "") {
            $ConnectionAlias = "default";
        }
        if (isset(Application::$Configuration->DBContext[$ConnectionAlias])) {
            if (!isset($this->Connections[$ConnectionAlias])) {
                $this->CreateConnection($ConnectionAlias);
            }

            $result = new DbSet($ConnectionAlias, $ModelName);
            $result->Reset();
        } else {
            trigger_error
            (
                sprintf("[DBContext] DataBase connector with alias '%s' not found", $ConnectionAlias),
                E_USER_ERROR
            );
        }

        return $result;
    }

    /**
     * Getting DbAssociation by name and connection alias
     *
     * @author     Alexander A. Popov <alejandro.popov@outlook.com>
     * @version    1.0
     *
     * @param string $AssociationName Name of table with association
     * @param string $ConnectionAlias Connection alias.
     * If empty - uses the <b>default</b> connection
     *
     * @return DbAssociation
     */
    public function GetDbAssociation(string $AssociationName, string $ConnectionAlias = "") : DbAssociation
    {
        $result = NULL;

        if ($ConnectionAlias == "") {
            $ConnectionAlias = "default";
        }
        if (isset(Application::$Configuration->DBContext[$ConnectionAlias])) {
            if (!isset($this->Connections[$ConnectionAlias])) {
                $this->CreateConnection($ConnectionAlias);
            }

            $result = new DbAssociation($ConnectionAlias, $AssociationName);
            $result->Reset();
        } else {
            trigger_error
            (
                sprintf("[DBContext] DataBase connector with alias '%s' not found", $ConnectionAlias),
                E_USER_ERROR
            );
        }

        return $result;
    }
}