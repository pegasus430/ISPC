<?php

	abstract class BasePatientDischarge extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_discharge');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('discharge_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('discharge_method', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('discharge_location', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('discharge_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('death_wish', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('manually_deleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
			$this->actAs(new PatientUpdate());
			
// 			//ISPC-2614 Ancuta 16.07.2020
// 			$this->addListener(new IntenseConnectionListener(array(
			    
// 			)), "IntenseConnectionListener");
// 			//
			
			//ISPC-2614 Ancuta 16.07.2020
			$this->addListener(new IntenseConnectionAdmissionsListener(array(
			)), "IntenseConnectionAdmissionsListener");
			//
		}

	}

?>