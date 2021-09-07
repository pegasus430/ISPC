<?php

	abstract class BasePseudoUsers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('lv_pseudo_users');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('ishidden', 'integer', 1, array('type' => 'string', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>