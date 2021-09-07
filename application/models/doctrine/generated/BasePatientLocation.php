<?php

	abstract class BasePatientLocation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_location');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('discharge_comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('admission_comments', 'string', 255, array('type' => 'string', 'length' => 255));
			
// 			$this->hasColumn('valid_from', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_from', 'datetime', null, array('type' => 'datetime', ));
			
			$this->hasColumn('reason', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('reason_old', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('reason_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('station', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('hospdoc', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('transport', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
// 			$this->hasColumn('valid_till', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_till', 'datetime', null, array('type' => 'datetime',));
			
			$this->hasColumn('location_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('resident_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('institute_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('discharge_location', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('is_contact', 'integer', 1, array(
					'type' => 'integer',
					'length' => 1,
					'default' => 0,
					'comments' => 'ist die Kontakt-Telefonnummer',
			));
		}

		function setUp()
		{
			
			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
			
			// ISPC-2024 - removed locations as contact number
			$this->addListener(new PatientContactPhoneListener(array(
					"is_contact"	=> "is_contact",
					"phone"			=> "phone",
					"mobile"		=> "mobile",
					"first_name"	=> "first_name",
					"last_name" 	=> "last_name",
					"other_name"	=> "other_name",
			)));
			
			$this->actAs(new PatientUpdate());
			
			//ISPC-2614 Ancuta 16-17.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			    
			)), "IntenseConnectionListener");
			//
		}

	}

?>