<?php

	abstract class BasePatientMaintainanceStage extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_maintainance_stage');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fromdate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('tilldate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('stage', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('erstantrag', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('horherstufung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			// ISPC-2078
			$this->hasColumn('e_fromdate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('h_fromdate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			//ISPC-2668 Lore 11.09.2020
			$this->hasColumn('rejected_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('opposition_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			
			//@cla on 04.12.2018
			$this->hasColumn('status', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('date_of_decision', 'date', null, array(
			    'type' => 'date',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('nursing_care_visit_necessary', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('implemented_by', 'string', null, array(
			    'type' => 'string',
			    'length' => null,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('required_on', 'string', null, array(
			    'type' => 'string',
			    'length' => null,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new Softdelete());
			
			//ISPC-2614 Ancuta 16-17.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
		}

	}

?>