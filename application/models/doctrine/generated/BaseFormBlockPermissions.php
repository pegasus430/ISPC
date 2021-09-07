<?php

	abstract class BaseFormBlockPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('block', 'string', 25, array('type' => 'integer', 'length' => 25));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>