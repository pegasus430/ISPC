<?php
Doctrine_Manager::getInstance()->bindComponent('GroupIconsDefaultPermissions', 'SYSDAT');

class GroupIconsDefaultPermissions extends BaseGroupIconsDefaultPermissions
{

    public function getGroupIconsClientAll($group_id, $clientid)
    {
        $q = Doctrine_Query::create()->select('*')
            ->from('GroupIconsDefaultPermissions')
            ->where('master_group_id="' . $group_id . '"')
            ->andWhere('clientid="' . $clientid . '"');
        $permisions = $q->fetchArray();
        
        if ($permisions) {
            foreach ($permisions as $k_perms => $v_perms) {
                $permissions[$v_perms['icon_type']][$v_perms['icon']]['canedit'] = $v_perms['canedit'];
                $permissions[$v_perms['icon_type']][$v_perms['icon']]['canadd'] = $v_perms['canadd'];
                $permissions[$v_perms['icon_type']][$v_perms['icon']]['canview'] = $v_perms['canview'];
                $permissions[$v_perms['icon_type']][$v_perms['icon']]['candelete'] = $v_perms['candelete'];
                ksort($permissions[$v_perms['icon_type']]);
            }
        }
        
        return $permissions;
    }

    /**
     * ISPC-2302 pct.3 @Lore 25.10.2019
     * @author Lore
     * @param unknown $clientid
     * @return unknown
     */
    public function getAllGroupIconsClientAll($clientid)
    {
        $q = Doctrine_Query::create()->select('*')
        ->from('GroupIconsDefaultPermissions')
        ->where('clientid = ?', $clientid);
        
        $permisions = $q->fetchArray();
        
        if ($permisions) {
            foreach ($permisions as $k_perms => $v_perms) {
                $permissions[$v_perms['master_group_id']][$v_perms['icon_type']][$v_perms['icon']]['canedit'] = $v_perms['canedit'];
                $permissions[$v_perms['master_group_id']][$v_perms['icon_type']][$v_perms['icon']]['canadd'] = $v_perms['canadd'];
                $permissions[$v_perms['master_group_id']][$v_perms['icon_type']][$v_perms['icon']]['canview'] = $v_perms['canview'];
                $permissions[$v_perms['master_group_id']][$v_perms['icon_type']][$v_perms['icon']]['candelete'] = $v_perms['candelete'];
                ksort($permissions[$v_perms['icon_type']]);
            }
        }
        
        return $permissions;
    }
    
    public function getGroupAllowedIcons($group_id, $clientid)
    {   
        $q = Doctrine_Query::create()->select('*')
            ->from('GroupIconsDefaultPermissions')
            ->where('master_group_id="' . $group_id . '"')
            ->andWhere('clientid="' . $clientid . '"');
        $permisions = $q->fetchArray();
        
        if ($permisions) {
            foreach ($permisions as $p_k => $p_v) {
                $permissions[$p_v['icon_type']][] = $p_v['icon'];
                ksort($permissions);
            }
            
            if (count($permissions['system']) == '0') {
                $permissions['system'][] = '999999999';
            }
            
            if (count($permissions['custom']) == '0') {
                $permissions['custom'][] = '999999999';
            }
        } else {
            $permissions = array(
                'system' => array(),
                'custom' => array()
            );
        }
        
        return $permissions;
    }

    public function check_empty_table()
    {
        $q = Doctrine_Query::create()->select('*')->from('GroupIconsDefaultPermissions');
        $permisions = $q->fetchArray();
        
        if ($permisions) {
            // return false if not empty
            return false;
        } else {
            return true;
        }
    }

    /**
     * it's just a re-write of self::getGroupAllowedIcons, same result
     * returns just the id's, grouped by icon_type(system or custom)
     * @author claudiu 01.02.2018
     * @param number $group_id            
     * @param number $clientid            
     * @return void|multitype:multitype:
     */
    public static function getGroupAllowedIconsV2($group_id = 0, $clientid = 0)
    {
        if (empty($group_id) || empty($clientid)) {
            return;
        }
        
        $result = array(
            'system' => array(),
            'custom' => array()
        );
        
        $permisions = Doctrine_Query::create()->select('id, icon_type, icon')
            ->from('GroupIconsDefaultPermissions')
            ->where('master_group_id= ?', $group_id)
            ->andWhere('clientid= ?', $clientid)
            ->fetchArray();
        
        if ($permisions) {
            foreach ($permisions as $row) {
                $result[$row['icon_type']][] = $row['icon'];
            }
            
            ksort($result);
        }
        return $result;
    }

  	/**
     * returns an array with "Gruppenechte Icon" for the loghedin user
     * based on the group he belongs
	 * optional get for a specific $userid
     * 
  	 * @author claudiu 01.02.2018
     * @return bool|array
     */
    public function getDetailedInfo($userid = null)
    {
        if (is_null($userid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $userid = $logininfo->userid;
        }
        
        $ClientidAndGroupid = User::get_ClientidAndGroupid($userid);
        
        $groupid = $ClientidAndGroupid['groupid'];
        $clientid = $ClientidAndGroupid['clientid'];
        
        if (! $clientid || ! $groupid) {
            return;
        }
        
        $master_group = Usergroup::getMasterGroup($groupid);
        $allowed_icons = self::getGroupAllowedIconsV2($master_group, $clientid);
        
        $system_icons_list = array();
        $client_icons_list = array();
        
        if (! empty($allowed_icons['system'])) {
            $sys_icons = new IconsMaster();
            $system_icons_list = $sys_icons->get_system_icons($clientid, $allowed_icons['system'], false, false);
        }
        
        if (! empty($allowed_icons['custom'])) {
            // get custom icons
            $icons = new IconsClient();
            $client_icons_list = $icons->get_client_icons($clientid, $allowed_icons['custom']);
        }
        
        $group_allowed_icons = array(
            'system' => $system_icons_list,
            'custom' => $client_icons_list
        );
        
        return $group_allowed_icons;
    }
}

?>