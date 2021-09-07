<?php

	abstract class BasePatientVisitsSettings extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_visits_settings');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('visits_per_day', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_duration', 'integer', 10, array('type' => 'integer', 'length' => 10));
			//ispc-1533
			$this->hasColumn('visit_day', 'tinyint', 1, array('type' => 'tinyint', 'length' => 1));
			$this->hasColumn('visit_hour', 'tinyint', 4, array('type' => 'tinyint', 'length' => 4));
			$this->hasColumn('visitor_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('grups', 'user', 'pseudogrups')));
			$this->hasColumn('visitor_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdeleted', 'tinyint', 1, array('type' => 'tinyint', 'length' => 1));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasOne('PatientMaster', array(
					'local' => 'ipid',
					'foreign' => 'ipid'
			));
			
			
			$this->actAs(new Softdelete(['name' => 'isdeleted']));
				
		}

	}

?>