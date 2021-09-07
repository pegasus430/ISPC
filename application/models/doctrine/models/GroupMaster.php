<?php

	Doctrine_Manager::getInstance()->bindComponent('GroupMaster', 'SYSDAT');

	class GroupMaster extends BaseGroupMaster {

		public function getGroupMaster()
		{

			$master = Doctrine_Query::create()
				->select('id,groupname')
				->from('GroupMaster')
				->where('id not in ("1","2")');

			$masters = $master->fetchArray();
			if($masters)
			{
				foreach($masters as $master_item)
				{
					$newmasterarr[$master_item['id']] = $master_item['groupname'];
				}
				return $newmasterarr;
			}
		}

	}

?>