<?php

	abstract class BaseExtraForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('extra_forms');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('inadmission', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('indetails', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('instammdatenerweitert', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>