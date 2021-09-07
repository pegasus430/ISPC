<?php

	Doctrine_Manager::getInstance()->bindComponent('GroupSecrecyVisibility', 'SYSDAT');

	class GroupSecrecyVisibility extends BaseGroupSecrecyVisibility {

		public function getDefaultVisibilityByGroup($group_id)
		{
			$gp = Doctrine_Query::create()
				->select('*')
				->from('GroupSecrecyVisibility')
				->where('master_group_id="' . $group_id . '"')
				->andWhere('clientid = 0');
			$gparray = $gp->fetchArray();
			if($gparray)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function getDefaultVisibilityAll()
		{
			$gp = Doctrine_Query::create()
				->select('*')
				->from('GroupSecrecyVisibility')
				->andWhere('clientid = 0');
			$gparray = $gp->fetchArray();
			if($gparray)
			{
				foreach($gparray as $k_perms => $v_perms)
				{
					$visibility[$v_perms['master_group_id']] = $v_perms['master_group_id'];
				}
			}

			return $visibility;
		}

		/**
		 *  Loredana backup fn getClientVisibilityByGroup
		 *  ISPC-2482 Lore 22.11.2019
		 * @param unknown $group_id
		 * @param unknown $clientid
		 * @return boolean
		 * @deprecated
		 */
		public static function getClientVisibilityByGroupold($group_id, $clientid)
		{
			$gp = Doctrine_Query::create()
// 				->select('*')
				->select('id')
				->from('GroupSecrecyVisibility')
				->where('master_group_id= ?' , $group_id)
				->andWhere('clientid = ?' , $clientid);
			$gparray = $gp->fetchArray();
			if($gparray)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * ISPC-2482 Lore 22.11.2019
		 * copy of fn getClientVisibilityByGroupold
		 * search also by group id, not ONLY by mastergroup_id
		 * @param unknown $mastergroup
		 * @param unknown $clientid
		 * @param boolean $group_id
		 * @return boolean
		 */
		public static function getClientVisibilityByGroup($mastergroup, $clientid, $group_id = false)
		{
		    $gp = Doctrine_Query::create()
		    ->select('id')
		    ->from('GroupSecrecyVisibility')
		    ->where('clientid = ?' , $clientid)
		    ->andWhere('master_group_id= ?' , $mastergroup);
		    if($group_id){
		        $gp->andWhere('groupid= ?' , $group_id);
		    }
		    $gparray = $gp->fetchArray();

		    if($gparray)
		    {
		        return true;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		/**
		 * Loredana backup of fn getClientVisibilityAll
		 * ISPC-2482 Lore 22.11.2019
		 * @param unknown $clientid
		 * @return unknown
		 * @deprecated
		 */
		public function getClientVisibilityAllold($clientid)
		{
			$gp = Doctrine_Query::create()
				->select('*')
				->from('GroupSecrecyVisibility')
				->andWhere('clientid = ' . $clientid);
			$gparray = $gp->fetchArray();
			if($gparray)
			{
				foreach($gparray as $k_perms => $v_perms)
				{
					$visibility[$v_perms['master_group_id']] = $v_perms['master_group_id'];
				}
			}

			return $visibility;
		}

		/**
		 * ISPC-2482 Lore 22.11.2019
		 * copy of fn getClientVisibilityAllold
		 * @param unknown $clientid
		 * @return unknown
		 */
		public function getClientVisibilityAll($clientid)
		{
		    
		    $gp = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupSecrecyVisibility')
		    ->andWhere('clientid = ?', $clientid);
		    $gparray = $gp->fetchArray();

		    if($gparray)
		    {
		        foreach($gparray as $k_perms => $v_perms)
		        {
		            $visibility[$v_perms['master_group_id']][]  = $v_perms['groupid'];
		        }
		    }
		    
		    return $visibility;
		}
	}

?>