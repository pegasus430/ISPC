<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDischargePlanning', 'MDAT');

	abstract class BasePatientDischargePlanning extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_discharge_planning');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('start_time', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('end_time', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('plan_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('expected_discharge_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('driving_time', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('driving_distance', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('location', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('location_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('location_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('user_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('user_phone', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('interview_with_patient', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('interview_with_nurse', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('interview_with_doctor', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('interview_with_contact', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('interview_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('interview_contact', 'text', NULL, array('type' => 'text', 'length' => NULL));
			
			$this->hasColumn('provide_care', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('provide_care_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('existing_care_service', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('existing_care_service_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('new_care_service', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('new_care_service_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('care_application_set', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('care_application_provided', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('care_application_upgrade', 'integer', 1, array('type' => 'integer', 'length' => 1));

//	 		$this->hasColumn('existing_aid', 'text', NULL, array('type' => 'text','length' => NULL));
//	 		$this->hasColumn('aid_available', 'integer', 1, array('type' => 'integer','length' => 1));
//	 		$this->hasColumn('aid_formulated', 'integer', 1, array('type' => 'integer','length' => 1));
//	 		$this->hasColumn('other_aids', 'text', NULL, array('type' => 'text','length' => NULL));
//	 		$this->hasColumn('ordered_from_company', 'string', 255, array('type' => 'string','length' => 255));

			$this->hasColumn('another_service', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('another_service_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('inofficial_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('official_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>