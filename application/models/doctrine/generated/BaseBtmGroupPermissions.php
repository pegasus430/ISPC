<?php

	abstract class BaseBtmGroupPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('btm_group_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>