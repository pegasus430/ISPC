<?php

	Doctrine_Manager::getInstance()->bindComponent('AssociatedGroups', 'SYSDAT');

	class AssociatedGroups extends BaseAssociatedGroups {

		function associated_groups_create($name = '', $status = '0')
		{
			$group = new AssociatedGroups();
			$group->name = $name;
			$group->status = $status;
			$group->save();

			return $group->id;
		}

		function associated_groups_get()
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('AssociatedGroups')
				->where("status = '0'")
				->orderby('id ASC');

			$groups = $q->fetchArray();

			if($groups)
			{
				return $groups;
			}
		}

		function associated_groups_mark_deleted($group)
		{
			$cl_group = Doctrine_Query::create()
				->update("AssociatedGroups")
				->set('status', "1")
				->where("id='" . $group . "'");
			$cl_group->execute();

			return $cl_group->id;
		}

	}

?>