<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodeGroups', 'SYSDAT');

	class SocialCodeGroups extends BaseSocialCodeGroups {

		public function getCientSocialCodeGroups($clientid)
		{
			$nogroup[0]['id'] = '0';
			$nogroup[0]['groupname'] = 'keine Gruppe';
			$nogroup[0]['groupshortcut'] = 'KG';
			$nogroup[0]['group_order'] = '0';

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeGroups')
				->where("clientid=" . $clientid . "")
				->andWhere('isdelete = 0')
				->orderBy('group_order ASC');
			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				$groupsarray[] = $nogroup[0];
				return $groupsarray;
			}
		}

		public function getSocialCodeGroup($groupid)
		{
			$group_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeGroups')
				->where("id = '" . $groupid . "'")
				->andWhere('isdelete = 0');
			$group_details = $group_sql->fetchArray();

			if($group_details)
			{
				return $group_details;
			}
		}

		public function getSocialCodeGroupName($clientid)
		{
			$group_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeGroups')
				->where("clientid = '" . $clientid . "'")
				->andWhere('isdelete = 0');
			$group_details = $group_sql->fetchArray();

			foreach($group_details as $key => $group)
			{
				$groupname[$group['id']] = $group['groupname'];
			}

			if($group_details)
			{
				return $groupname;
			}
		}

	}

?>