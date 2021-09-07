<?php
Doctrine_Manager::getInstance()->bindComponent('GroupDefaultPermissions', 'SYSDAT');

class GroupDefaultPermissions extends BaseGroupDefaultPermissions
{

    public function getPermissionsByGroup($group_id = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = 0')
            ->andWhere('pat_nav_id > 0')
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['pat_nav_id']]['canedit'] = $v_perms['canedit'];
                $permissions[$v_perms['pat_nav_id']]['canview'] = $v_perms['canview'];
            }
        }
        
        return $permissions;
    }

    
    public function getPermissionsByGroupAndClient($group_id = 0, $clientid = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = ?', $clientid)
            ->andWhere('pat_nav_id > 0')
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['pat_nav_id']] = $v_perms;
            }
        }
        
        return $permissions;
    }

    public function getMiscPermissionsByGroupAndClient($group_id = 0, $clientid = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = ?', $clientid)
            ->andWhere('misc_id > 0')
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['misc_id']] = $v_perms;
            }
        }
        
        return $permissions;
    }

    public function getPermissionsByGroupAndClientAll($group_id = 0, $clientid = 0)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = ?', $clientid)
            ->andWhere('pat_nav_id != 0')
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['pat_nav_id']] = $v_perms['pat_nav_id'];
            }
        }
        
        return $permissions;
    }

    public function getPermissionsByGroupAndClientSystem($group_id = 0, $clientid = 0)
    {
        $gp = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = ?', $clientid)
            ->andWhere('menu_id != 0');
        $gparray = $gp->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $k_perms => $v_perms) {
                $permissions[$v_perms['menu_id']] = $v_perms['menu_id'];
            }
            
        } else {
            
            $sql_log = $gp->getSqlQuery();
            PatientPermissions::LogRightsError(2, $sql_log);
        }
        
        return $permissions;
    }

    
    public function verifyPermissionByGroupAndClient($group_id, $clientid, $navid, $permission = 'canview', $perm = 'pat_nav_id')
    {
        $modelColumns = Doctrine_Core::getTable('GroupDefaultPermissions')->getColumns();
        
        $permission = trim($permission);
        $perm = trim($perm);
        
        if ( ! isset($modelColumns[$permission])) {
            $permission = 'canview';
        }

        if ( ! isset($modelColumns[$perm])) {
            $perm = 'pat_nav_id';
        }
        
        
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = ?', $clientid)
            ->andWhere("{$perm} = ?", $navid)
            ->andWhere("{$permission} =  1")
            ->fetchArray();
        
        return $gparray;
    }

    public function getGroupPermisionsAll($group_id)
    {
        $gparray = Doctrine_Query::create()
            ->select('*')
            ->from('GroupDefaultPermissions')
            ->where('master_group_id = ?', $group_id)
            ->andWhere('clientid = 0')
            ->andWhere('pat_nav_id != 0')
            ->fetchArray();
        
        if ($gparray) {
            
            $permissions = array();
            
            foreach ($gparray as $p_k => $p_v) {
                $permissions[$p_v['pat_nav_id']] = $p_v['pat_nav_id'];
                ksort($permissions);
            }
            
            return $permissions;
        }
    }
}

?>