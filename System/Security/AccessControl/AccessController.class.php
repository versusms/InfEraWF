<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 11.03.2016 10:27
 * @package InfEra\System\Security\AccessControl
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\Security\AccessControl;

use InfEra\WAFP\Application;
use InfEra\WAFP\Base\User\Models\AccessRule;
use InfEra\WAFP\Base\User\Models\Role;
use InfEra\WAFP\Base\User\Models\User;
use InfEra\WAFP\System\Collections\Dictionary;
use InfEra\WAFP\System\Reflection\DocComments;

/**
 * Class AccessController
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 * @package InfEra\System\Security\AccessControl
 */
class AccessController
{
    /**
     * System role - Guest
     */
    const ROLE_GUEST = 1;

    /**
     * System role - User
     */
    const ROLE_USER = 2;

    /**
     * System role - System
     */
    const ROLE_SYSTEM = 3;

    /**
     * [DESCRIPTION]
     * @var \InfEra\System\Collections\Dictionary
     */
    private $RolesCache = null;

    /**
     * [DESCRIPTION]
     * @var \InfEra\System\Collections\Dictionary
     */
    private $RightsCache = null;

    public function __construct()
    {
        $this->RightsCache = new Dictionary();
        $this->RolesCache = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\Role')
            ->Select();

        foreach ($this->RolesCache as $Role)
        {
            $this->RightsCache->Add($Role->ID, $Role->AccessRules);
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return Role
     */
    public function GetGuestRole() : Role
    {
        return $this->RolesCache->Get(AccessController::ROLE_GUEST);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return Role
     */
    public function GetUserRole() : Role
    {
        return $this->RolesCache->Get(AccessController::ROLE_USER);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return Role
     */
    public function GetSystemRole() : Role
    {
        return $this->RolesCache->Get(AccessController::ROLE_SYSTEM);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     */
    public function UpdateGuestRules()
    {
        $DBC_AR = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\AccessRule');
        $DBC_RAR = Application::$DBContext->GetDbAssociation('Roles_AccessRules');

        $DBC_RAR->Delete("RoleID = " . AccessController::ROLE_GUEST);
        $GuestRules = $DBC_AR
            ->Where('Access = ' . AccessRule::ACCESS_PUBLIC)
            ->Select();
        foreach ($GuestRules as $Rule)
        {
            $DBC_RAR->Add(array(
                'RoleID' => AccessController::ROLE_GUEST,
                'AccessRuleID' => $Rule->ID
            ));
        }
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @throws \InfEra\WAFP\System\Entity\Exceptions\DbSetException
     */
    public function CollectAccessRules()
    {
        //@TODO Objects by package!!!

        $DBC = Application::$DBContext->GetDbSet('\InfEra\WAFP\Base\User\Models\AccessRule');

        $SearchPaths = array(
            Application::$Configuration['FrameworkPath'] . 'Base',
            Application::$Configuration['ProjectPath'] . "src/". str_replace('\\', '/', Application::$Configuration['Namespace']),
        );
        $ControllersClasses = array();
        foreach ($SearchPaths as $Folder)
        {
            $ControllersClasses = array_merge($ControllersClasses, $this->GetControllersFiles($Folder));
        }

        foreach ($ControllersClasses as $Class)
        {
            $ControllerDescription = new \ReflectionClass($Class);
            $ControllerDocDescription = DocComments::Parse($ControllerDescription->getDocComment());
            $ControllerAccess = (
                array_key_exists('access', $ControllerDocDescription) &&
                $ControllerDocDescription['access'] == 'ACCESS_AUTH'
            ) ? AccessRule::ACCESS_AUTH : AccessRule::ACCESS_PUBLIC;

            $ControllersActions = $ControllerDescription->getMethods(\ReflectionMethod::IS_PUBLIC);

            $Objects = array();
            foreach ($ControllersActions as $Method)
            {
                if (!$Method->isConstructor())
                {
                    $ObjectName = str_replace('\\', '.', $Method->class) . '.' .
                        str_replace(array('_HttpGet', '_HttpPost', '_HttpPut', '_HttpDelete'), '', $Method->name);
                    if (!in_array($ObjectName, $Objects))
                    {
                        $Objects[] = $ObjectName;
                        $AccessRule = new AccessRule();
                        $AccessRule->Object = strtolower($ObjectName);

                        $ActionDocDescription = DocComments::Parse($Method->getDocComment());
                        $AccessRule->Access = (
                            array_key_exists('access', $ActionDocDescription) &&
                            $ActionDocDescription['access'] == 'ACCESS_AUTH'
                        ) ? AccessRule::ACCESS_AUTH : $ControllerAccess;

                        $StoredRule = $DBC->Where("Object = '$AccessRule->Object'")
                            ->Select()
                            ->First();

                        if (!is_null($StoredRule))
                        {
                            $StoredRule->Access = $AccessRule->Access;
                            $DBC->Store($StoredRule);
                        }
                        else
                        {
                            $DBC->Add($AccessRule);
                        }
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
     * @param string $Path
     * @return array
     */
    private function GetControllersFiles(string $Path = ".") : array
    {
        $files = array();
        if ($handle = opendir($Path)) {
            while (false !== ($item = readdir($handle))) {
                if (is_file("$Path/$item")) {
                    if (end(explode('/', $Path)) == 'Controllers' && strpos("$Path/$item", 'Controller.class') !== false)
                    {
                        $files[] = str_replace(
                            array(
                                Application::$Configuration['FrameworkPath'],
                                Application::$Configuration['ProjectPath'] . 'src',
                                '.class.php',
                                '/'
                            ),
                            array(
                                '\InfEra\\',
                                '',
                                '',
                                '\\'
                            ),
                            "$Path/$item"
                        );
                    }
                }
                elseif (is_dir("$Path/$item") && ($item != ".") && ($item != "..")){
                    $files = array_merge($files, $this->GetControllersFiles("$Path/$item"));
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $Object
     * @param User|NULL $User
     * @return bool
     */
    public function ValidateAccess($Object, User $User = NULL) : bool
    {
        if (!is_null($User) && $User->IsSU)
        {
            return true;
        }

        $Object = strtolower(str_replace('\\', '.', $Object));
        $UserAccesRules = array();

        foreach($this->GetGuestRole()->AccessRules as $AccessRule)
        {
            $UserAccesRules[] = $AccessRule->Object;
        }

        // Not Guest
        if (!is_null($User))
        {
            foreach ($User->Roles as $UserRole)
            {
                foreach($UserRole->AccessRules as $AccessRule)
                {
                    $UserAccesRules[] = $AccessRule->Object;
                }
            }
        }

        return in_array($Object, $UserAccesRules);
    }
}