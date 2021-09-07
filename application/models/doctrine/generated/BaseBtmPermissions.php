<?php

	abstract class BaseBtmPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('btm_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canadd', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('candelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			
		}

	}

?>