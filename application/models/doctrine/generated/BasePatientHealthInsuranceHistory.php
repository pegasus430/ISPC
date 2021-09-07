<?php

	abstract class BasePatientHealthInsuranceHistory extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_health_insurance_history');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('exemption_till_date', 'date');
			$this->hasColumn('patient_hi_data', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			$this->hasOne('PatientMaster', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
		}

	}

?>