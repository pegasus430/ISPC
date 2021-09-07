<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDischargePlanningAid', 'MDAT');

	abstract class BasePatientDischargePlanningAid extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_discharge_planning_aid');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('plan_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('aid_item', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('aid_item_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('aid_type', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('aid_company', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>