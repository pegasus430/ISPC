<?php

	abstract class BaseRpControl extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('rp_control');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('qty_home', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('qty_nurse', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('qty_hospiz', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}
	}

?>