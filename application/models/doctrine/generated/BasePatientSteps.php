<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientSteps', 'MDAT');

	abstract class BasePatientSteps extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_steps');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('step', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('green', 'blue', 'red', 'gray')));
			$this->hasColumn('recordid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('step_identification', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>