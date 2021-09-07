<?php

	Doctrine_Manager::getInstance()->bindComponent('VwAssociatedGroups', 'SYSDAT');

	class VwAssociatedGroups extends BaseVwAssociatedGroups {
 
		function associated_groups_get()
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('VwAssociatedGroups')
				->where("status = '0'")
				->orderby('id ASC');

			$groups = $q->fetchArray();

			if($groups)
			{
				return $groups;
			}
		}
	}

?>