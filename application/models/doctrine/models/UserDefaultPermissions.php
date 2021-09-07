<?php

Doctrine_Manager::getInstance()->bindComponent('UserDefaultPermissions', 'SYSDAT');

class UserDefaultPermissions extends BaseUserDefaultPermissions
{

    /**
     * @see getPermissionsByGroupAndClientAll, they are the same ?
     *
     * @param number $user_id
     * @param number $clientid
     * @return multitype:Ambigous <>
     */
    public function getPermissionsByUserAndClientSystem($user_id = 0, $clientid = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('UserDefaultPermissions')
            ->where('user_id = ? ', $user_id)
            ->andWhere('clientid = ?', $clientid)
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['menu_id']] = $v_perms['menu_id'];
            }
        }
        
        return $permissions;
    }

    /**
     * @see getPermissionsByUserAndClientSystem, they are the same ?
     * 
     * @param number $user_id
     * @param number $clientid
     * @return multitype:Ambigous <>
     */
    public function getPermissionsByGroupAndClientAll($user_id = 0, $clientid = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('UserDefaultPermissions')
            ->where('user_id= ?', $user_id)
            ->andWhere('clientid = ?', $clientid)
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['menu_id']] = $v_perms['menu_id'];
            }
        }
        
        return $permissions;
    }

    /**
     *
     * @since 13.06.2018 static
     *       
     * @param int $user_id            
     * @param int $clientid            
     * @param int $menuid            
     * @param string $permission            
     * @return Ambigous <multitype:, Doctrine_Collection>
     */
    public static function verifyPermissionByUserAndClient($user_id = 0, $clientid = 0, $menuid = 0, $permission = 'canview')
    {
        $modelColumns = Doctrine_Core::getTable('UserDefaultPermissions')->getColumns();
        
        $permission = trim($permission);
        
        if ( ! isset($modelColumns[$permission])) {
            $permission = 'canview';
        }
        
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('UserDefaultPermissions')
            ->where('user_id = ?', $user_id)
            ->andWhere('clientid = ?', $clientid)
            ->andWhere('menu_id = ?', $menuid)
            ->andWhere("{$permission} = 1")
            ->fetchArray();
        
        return $gparray;
    }

    
    public function getGroupPermisionsAll($user_id = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('UserDefaultPermissions')
            ->where('user_id = ?', $user_id)
            ->andWhere('clientid = 0')
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $p_k => $p_v) {
                $permissions[$p_v['menu_id']] = $p_v['menu_id'];
                ksort($permissions);
            }
            return $permissions;
        }
    }
}

?>