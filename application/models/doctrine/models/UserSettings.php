<?php
Doctrine_Manager::getInstance()->bindComponent('UserSettings', 'SYSDAT');

class UserSettings extends BaseUserSettings
{
    
    public static function defaults_patient_contactphone() 
    {
        return [
            'comment' => self::translate('[display comment]'), 
            'address' => self::translate('[display address]'),
        ];
            
    }

    public static function getUserSettings($userid = 0)
    {
        if (empty($userid)) {
            return;
        }
        
        $drop = Doctrine_Query::create()->select('*')
            ->from('UserSettings')
            ->where('userid = :user_id')
//             ->andWhere('isdelete = "0"') //softDelete
            ->fetchOne(array("user_id"=> $userid), Doctrine_Core::HYDRATE_ARRAY);
        return $drop;
    }

    /**
     * be aware this fn gets the full table
    */
    public static function getallUsersSettings()
    {
        $drop = Doctrine_Query::create()->select('*')
            ->from('UserSettings')
            ->where('isdelete = "0"')
            ->fetchArray();
        
        return $drop;
    }
}


?>