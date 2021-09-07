<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientGroupPermissions', 'IDAT');

	class PatientGroupPermissions extends BasePatientGroupPermissions {

		public function getPatientGroupPermisionsAll($groupid, $ipid, $full = true)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientGroupPermissions')
				->where('groupid="' . $groupid . '" and ipid = "' . $ipid . '" and (canview = "1" OR canedit = "1")');
			$permisions = $q->fetchArray();

			if($permisions)
			{
				foreach($permisions as $p_k => $p_v)
				{
					if($full === true)
					{
						$permissions[$p_v['pat_nav_id']] = $p_v;
					}
					else
					{
						$permissions[$p_v['pat_nav_id']] = $p_v['pat_nav_id'];
					}
					ksort($permissions);
				}
				return $permissions;
			}
		}

		public function getPatientGroupMiscPermisionsAll($groupid, $ipid, $full = true)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientGroupPermissions')
				->where('groupid="' . $groupid . '" and ipid = "' . $ipid . '" and (canview = "1" OR canedit = "1") and misc_id>0');
			$permisions = $q->fetchArray();

			if($permisions)
			{
				foreach($permisions as $p_k => $p_v)
				{
					if($full === true)
					{
						$permissions[$p_v['misc_id']] = $p_v;
					}
					else
					{
						$permissions[$p_v['misc_id']] = $p_v['misc_id'];
					}
					ksort($permissions);
				}
				return $permissions;
			}
		}

	}

?>