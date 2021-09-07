<?php

	abstract class BaseGroupIconsDefaultPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('group_icons_default_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('master_group_id', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('icon', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('icon_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('canadd', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canedit', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canview', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('candelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}
	}

?>