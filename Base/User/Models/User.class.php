<?php
/**
 * @author     Alexander A. Popov <versusms@gmail.com>
 * @package    InfEra[Base]
 * @subpackage User[Models]
 */
namespace InfEra\Base\User\Models
{          
    /**
     * Base user model
     *
     * @author     Alexander A. Popov <versusms@gmail.com>
     * @version    1.0
     * @package    InfEra[Base]
     * @subpackage User[Models]
     */
    class User
    { 
        public $ID = 0;
        public $Provider = "Base";
        public $Login = "";
        public $Password = "";
        public $FirstName = NULL;
        public $LastName = NULL;
        public $Email = "";
        public $Avatar = NULL;
        /**
         * Birthday
         * @var DateTime
         */
        public $Birthday;
        public $Location = NULL;
        public $Gender = NULL;
        public $Timezone = 4;
        public $Locale = "ru";
        public $RegistredAt;
        public $IsSystem = false;
        public $IsEnabled = true;
        public $Status = "Offline";
        /**
         * Last activity in system
         * @var DateTime
         */
        public $LastActivity;
        public $IsSU = false;
        
        
        public function __construct()
        {
            $this->Birthday = new \DateTime();
            $this->Birthday->modify("-20 year");
            $this->RegistredAt = new \DateTime();
            $this->LastActivity = new \DateTime();
        }
    }    
}