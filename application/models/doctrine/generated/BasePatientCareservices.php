<?php

	abstract class BasePatientCareservices extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_care_services');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('item', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shift', 'enum', NULL, array('type' => 'enum', 'notnull' => false, 'values' => array('morning', 'noon', 'night')));
			$this->hasColumn('full', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('full_amount', 'integer', 12, array('type' => 'integer', 'length' => 12));
			$this->hasColumn('partial', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('partial_amount', 'integer', 12, array('type' => 'integer', 'length' => 12));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		

		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>