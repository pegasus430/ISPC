<?php

	abstract class BaseEmergencyPlanSapv24Medication extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('emergency_plan_sapv24_medication');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('planid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('med_type', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('medication', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dosage', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('indication', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('indication_color', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('nursing_measures', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));
			$this->hasColumn('iscomplete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasOne('EmergencyPlanSapv24', array(
					'local' => 'planid',
					'foreign' => 'id'
			));
		}	

	}

?>