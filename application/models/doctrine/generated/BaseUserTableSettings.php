<?php

	Doctrine_Manager::getInstance()->bindComponent('UserTableSettings', 'SYSDAT');

	abstract class BaseUserTableSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_table_settings');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('client', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('page', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tab', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('column_id', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('column_order', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('visible', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('yes', 'no')));
		}
	}

?>