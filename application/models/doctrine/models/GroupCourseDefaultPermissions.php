<?php

	Doctrine_Manager::getInstance()->bindComponent('GroupCourseDefaultPermissions', 'SYSDAT');

	class GroupCourseDefaultPermissions extends BaseGroupCourseDefaultPermissions {

		public function getPermissionsByGroup($group_id)
		{
			$gp = Doctrine_Query::create()
				->select('*')
				->from('GroupCourseDefaultPermissions')
				->where('master_group_id="' . $group_id . '"');
			$gparray = $gp->fetchArray();
			if($gparray)
			{
				foreach($gparray as $k_perms => $v_perms)
				{
					$permissions[$v_perms['shortcutid']]['canedit'] = $v_perms['canedit'];
					$permissions[$v_perms['shortcutid']]['canadd'] = $v_perms['canadd'];
					$permissions[$v_perms['shortcutid']]['canview'] = $v_perms['canview'];
					$permissions[$v_perms['shortcutid']]['candelete'] = $v_perms['candelete'];
				}
			}

			return $permissions;
		}

		public function getGroupPermisionsAll($group_id)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('GroupCourseDefaultPermissions')
				->where('master_group_id="' . $group_id . '"');
			$permisions = $q->fetchArray();

			if($permisions)
			{
				foreach($permisions as $p_k => $p_v)
				{
					$permissions[$p_v['shortcutid']] = $p_v['shortcutid'];
					ksort($permissions);
				}
				return $permissions;
			}
		}

		public function getShortcutsByGroupAndClient($group_id, $clientid, $permission = 'canview')
		{
			$gp = Doctrine_Query::create()
				->select('*')
				->from('GroupCourseDefaultPermissions')
				->where('master_group_id="' . $group_id . '"')
				->andWhere('clientid="' . $clientid . '"')
				->andwhere('' . $permission . ' = 1');
			$gparray = $gp->fetchArray();
			if($gparray)
			{
				return $gparray;
			}
		}

		public function getGroupShortcutsClientAll($group_id, $clientid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('GroupCourseDefaultPermissions')
				->where('master_group_id="' . $group_id . '"')
				->andWhere('clientid="' . $clientid . '"');
			$permisions = $q->fetchArray();

			if($permisions)
			{
				foreach($permisions as $k_perms => $v_perms)
				{
					$permissions[$v_perms['shortcutid']]['canedit'] = $v_perms['canedit'];
					$permissions[$v_perms['shortcutid']]['canadd'] = $v_perms['canadd'];
					$permissions[$v_perms['shortcutid']]['canview'] = $v_perms['canview'];
					$permissions[$v_perms['shortcutid']]['candelete'] = $v_perms['candelete'];
				}
			}

			return $permissions;
		}


		/**
		 * ISPC-2302 pct.3 @Lore 24.10.2019
		 * @author Lore
		 * @param unknown $clientid
		 * @return unknown
		 */
		public function getGCShortcutsClientAll( $clientid)
		{
		    $q = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupCourseDefaultPermissions')
		    ->where('clientid= ?' , $clientid );
		    $permisions = $q->fetchArray();
		   
		    if($permisions)
		    {
		        foreach($permisions as $k_perms => $v_perms)
		        {
		           $permissions[$v_perms['master_group_id']][$v_perms['shortcutid']]['canedit'] = $v_perms['canedit'];
		           $permissions[$v_perms['master_group_id']][$v_perms['shortcutid']]['canadd'] = $v_perms['canadd'];
		           $permissions[$v_perms['master_group_id']][$v_perms['shortcutid']]['canview'] = $v_perms['canview'];
		           $permissions[$v_perms['master_group_id']][$v_perms['shortcutid']]['candelete'] = $v_perms['candelete'];
		        }
		    }
		    
		    return $permissions;
		}
		
		public function getGroupPermisionsClientAll($group_id, $clientid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('GroupCourseDefaultPermissions')
				->where('master_group_id="' . $group_id . '"')
				->andWhere('clientid="' . $clientid . '"');
			$permisions = $q->fetchArray();

			if($permisions)
			{
				foreach($permisions as $p_k => $p_v)
				{
					$permissions[$p_v['shortcutid']] = $p_v['shortcutid'];
					ksort($permissions);
				}
				return $permissions;
			}
		}

	}

?>