<?php

	abstract class BaseTherapyplanItems extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('therapyplan_items');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('therapyplan_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('user_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('full_name', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('comment', 'string', null, array('type' => 'integer', 'length' => null));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>