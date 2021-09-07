<?php

	Doctrine_Manager::getInstance()->bindComponent('ComplaintUsers', 'SYSDAT');

	abstract class BaseComplaintUsers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('complaint_users');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('open_case', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('close_case', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>