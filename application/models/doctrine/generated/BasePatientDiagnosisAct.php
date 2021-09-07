<?php

	abstract class BasePatientDiagnosisAct extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_diagnosis_act');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('act', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>