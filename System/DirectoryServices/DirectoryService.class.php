<?php
/**
 * Created by InfEra Solutions.
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @date 25.02.2016 17:19
 */
declare(strict_types = 1);

namespace InfEra\WAFP\System\DirectoryServices;

use InfEra\WAFP\System\Collections\Dictionary;

/**
 * Class DirectoryService
 * [DESCRIPTION]
 *
 * @author Alexander A. Popov <alejandro.popov@outlook.com>
 * @version 1.0
 */
class DirectoryService
{
    /**
     * [DESCRIPTION]
     * @var int
     */
    public $LastErrorNo = 0;

    /**
     * [DESCRIPTION]
     * @var string
     */
    public $LastErrorMessage = '';

    /**
     * [DESCRIPTION]
     * @var string
     */
    private $Host = '';

    /**
     * [DESCRIPTION]
     * @var int
     */
    private $Port = 389;

    /**
     * [DESCRIPTION]
     * @var string
     */
    private $DomainPrefix = '';

    /**
     * [DESCRIPTION]
     * @var string
     */
    private $DomainSuffix = '';

    /**
     * [DESCRIPTION]
     * @var resource
     */
    private $LinkIdentifier = null;

    /**
     * DirectoryService constructor
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $Host
     * @param string $DomainPrefix
     * @param string $DomainSuffix
     */
    public function __construct(string $Host, string $DomainPrefix = '', string $DomainSuffix = '', string $Port = 389)
    {
        $this->Host = $Host;
        $this->DomainPrefix = $DomainPrefix;
        $this->DomainSuffix = $DomainSuffix;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @return bool
     */
    public function Connect() : bool
    {
        $result = false;

        $this->SetECatcher();

        try {
            $this->LinkIdentifier = ldap_connect($this->Host, $this->Port);
            ldap_set_option($this->LinkIdentifier, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->LinkIdentifier, LDAP_OPT_REFERRALS,0);
            if (ldap_bind($this->LinkIdentifier))
            {
                $result = true;
            }
            else
            {
                $this->Close();
            }
        }
        catch (DirectoryServiceException $e) {
            $this->LastErrorNo = $e->GetLastCode($this->LinkIdentifier);
            $this->LastErrorMessage = $e->GetLastMessage($this->LinkIdentifier);
        }

        $this->ReleaseECatcher();

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $LogonName
     * @param string $Password
     * @return bool
     */
    public function CheckAuthority(string $LogonName, string $Password) : bool
    {
        $result = false;

        $this->SetECatcher();

        try {
            $LogonName = $this->DomainPrefix != '' ? $this->DomainPrefix . '\\' . $LogonName :
                ($this->DomainSuffix != '' ? $LogonName . '@' . $this->DomainSuffix : $LogonName);
            if (ldap_bind($this->LinkIdentifier, $LogonName, $Password))
            {
                $result = true;
            }
        }
        catch (DirectoryServiceException $e) {
            $this->LastErrorNo = $e->GetLastCode($this->LinkIdentifier);
            $this->LastErrorMessage = $e->GetLastMessage($this->LinkIdentifier);
        }

        $this->ReleaseECatcher();

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $BaseDN
     * @param string $Filter
     * @param array $Attributes
     * @return Dictionary
     */
    public function Search(string $BaseDN, string $Filter = '', array $Attributes = array()) : Dictionary
    {
        $result = new Dictionary();

        $this->SetECatcher();

        if (!array_key_exists('objectGUID', $Attributes))
        {
            $Attributes[] = 'objectGUID';
        }

        try {
            $request = ldap_search($this->LinkIdentifier, $BaseDN, $Filter, $Attributes);
            if (ldap_count_entries($this->LinkIdentifier, $request) > 0)
            {
                $DirectoryEntry = ldap_first_entry($this->LinkIdentifier, $request);
                do
                {
                    $DirectoryRecord = new DirectoryRecord();
                    $DirectoryRecord->DN = ldap_get_dn($this->LinkIdentifier, $DirectoryEntry);
                    $DirectoryAttribute = ldap_first_attribute($this->LinkIdentifier, $DirectoryEntry);
                    do
                    {
                        $gavfunc = ($DirectoryAttribute == 'objectGUID') ? 'ldap_get_values_len' : 'ldap_get_values';
                        $AttributeValues = $gavfunc($this->LinkIdentifier, $DirectoryEntry, $DirectoryAttribute);
                        if (array_key_exists('count', $AttributeValues))
                        {
                            unset($AttributeValues['count']);
                        }
                        if ($DirectoryAttribute == 'objectGUID')
                        {
                            foreach($AttributeValues as $index => $value)
                            {
                                $AttributeValues[$index] = $this->GUIDtoStr($value);
                            }
                            $DirectoryRecord->GUID = $AttributeValues[0];
                        }
                        $DirectoryRecord->Attributes->Add($DirectoryAttribute, $AttributeValues);
                    }
                    while ($DirectoryAttribute = ldap_next_attribute($this->LinkIdentifier, $DirectoryEntry));
                    $result->Add($DirectoryRecord->GUID, $DirectoryRecord);
                }
                while ($DirectoryEntry = ldap_next_entry($this->LinkIdentifier, $DirectoryEntry));
            }
        }
        catch (DirectoryServiceException $e) {
            $this->LastErrorNo = $e->GetLastCode($this->LinkIdentifier);
            $this->LastErrorMessage = $e->GetLastMessage($this->LinkIdentifier);
        }

        $this->ReleaseECatcher();

        return $result;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param string $LogonName
     * @return string
     */
    public function GetFullLogonName(string $LogonName) : string
    {
        return $this->DomainPrefix != '' ? $this->DomainPrefix . '\\' . $LogonName :
            ($this->DomainSuffix != '' ? $LogonName . '@' . $this->DomainSuffix : $LogonName);
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     */
    public function Close()
    {
        if (is_resource($this->LinkIdentifier)) {
            ldap_close($this->LinkIdentifier);
        }
        $this->LinkIdentifier = null;
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     */
    private function SetECatcher()
    {
        set_error_handler(array('InfEra\System\DirectoryServices\DirectoryServiceException', 'ErrorHandler'));
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     */
    private function ReleaseECatcher()
    {
        restore_error_handler();
    }

    /**
     * [DESCRIPTION]
     * @author Alexander A. Popov <alejandro.popov@outlook.com>
     * @version 1.0
     *
     * @param $BinaryGUID
     * @return string
     */
    //@TODO $BinaryGUID type
    private function GUIDtoStr($BinaryGUID) : string
    {
        $HexGUID = unpack("H*hex", $BinaryGUID);
        $Hex = $HexGUID["hex"];

        $Hex1 = substr($Hex, -26, 2) . substr($Hex, -28, 2) . substr($Hex, -30, 2) . substr($Hex, -32, 2);
        $Hex2 = substr($Hex, -22, 2) . substr($Hex, -24, 2);
        $Hex3 = substr($Hex, -18, 2) . substr($Hex, -20, 2);
        $Hex4 = substr($Hex, -16, 4);
        $Hex5 = substr($Hex, -12, 12);

        $GUIDStr = $Hex1 . "-" . $Hex2 . "-" . $Hex3 . "-" . $Hex4 . "-" . $Hex5;

        return $GUIDStr;
    }
}