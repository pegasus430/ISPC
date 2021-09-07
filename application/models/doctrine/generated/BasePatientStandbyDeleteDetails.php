<?php

	abstract class BasePatientStandbyDeleteDetails extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_standbydelete_details');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('date_type', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2614 Ancuta 16.07.2020
			$this->addListener(new IntenseConnectionAdmissionsListener(array(
			)), "IntenseConnectionAdmissionsListener");
			//
// 			//ISPC-2614 Ancuta 16.07.2020
// 			$this->addListener(new IntenseConnectionListener(array(
			    
// 			)), "IntenseConnectionListener");
// 			//
		}

	}

?>