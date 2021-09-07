<?php
/*
 * @cla on 05.07.2018 
 * changed to extend Pms_Doctrine_Record
 */
	abstract class BasePatientMaster extends Pms_Doctrine_Record {

		//ISPC-2045 used this 3 vars to know if you ckecked as 'ist die Kontakt-Telefonnummer'
// 		public $is_contact = null; 
		public $is_contact_Location = null; 
		public $other_name = null;
		
		function setTableDefinition()
		{
			$this->setTableName('patient_master');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('referred_by', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('recording_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('middle_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('salutation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mobile', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kontactnumber', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kontactnumbertype', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('birthd', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('birth_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('birth_city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sex', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('denomination_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('familydoc_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('familydoc_id_qpa', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pflegedienste', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pflege_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('admission_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('fdoc_caresalone', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdischarged', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isstandby', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ishospiz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ishospizverein', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isarchived', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isstandbydelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('last_update', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('last_update_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('living_will', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('living_will_from', 'datetime', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('living_will_deposited', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('vollversorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vollversorgung_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('traffic_status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isadminvisible', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wlanlage7completed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('orderadmission', 'integer', 11, array('type' => 'integer', 'length' => 11));
			// Ancuta 01.02.2016 (!!!! TODO-84  FOR IMPORT ONLY) 
			$this->hasColumn('import_pat', 'string', 255, array('type' => 'string', 'length' => 255));
// 			$this->hasColumn('import_fd', 'string', 255, array('type' => 'string', 'length' => 255));
// 			$this->hasColumn('import_hi', 'string', 255, array('type' => 'string', 'length' => 255));
			// Claudiu 2016.08.23
			$this->hasColumn('height', 'string', 255, array('type' => 'string', 'length' => 255));
			
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
			
			//@Alex ISPC-2429 - table log
			$this->actAs(new TableLogTemplate());

			$this->addListener(new PatientContactPhoneListener(array(
					"is_contact"	=> "is_contact",
					"phone"			=> "phone",
					"mobile"		=> "mobile",
					"first_name"	=> "first_name",
					"last_name" 	=> "last_name",
					"other_name"	=> "other_name",
			)));
			$this->actAs(new Trigger());


			$this->hasOne('EpidIpidMapping', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));

			$this->hasOne('PatientHealthInsurance', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			$this->hasMany('PatientDischarge', array(
					'local' => 'ipid',
					'foreign' => 'ipid'
			));
			
			$this->hasMany('PatientContactphone', array(
					'local' => 'ipid',
					'foreign' => 'ipid'
			));
			
			$this->hasMany('PatientReadmission', array(
			    'local' => 'ipid',
			    'foreign' => 'ipid'
			));

			$this->hasMany('PatientActive', array(
			    'local' => 'ipid',
			    'foreign' => 'ipid'
			));

			// Maria:: Migration CISPC to ISPC 22.07.2020
            $this->hasMany('PatientCaseStatus', array(
                'local' => 'ipid',
                'foreign' => 'ipid'
            ));
			//-- 


			$this->hasOne('PatientSurveySettings', array(
					'local' => 'ipid',
					'foreign' => 'ipid'
			));
			//ISPC-2432 Ancuta 03.02.2020
			$this->hasOne('MePatientDevicesNotifications', array(
					'local' => 'ipid',
					'foreign' => 'ipid'
			));
			
			
			//ISPC-2474 Ancuta 23.10.2020
			$this->hasOne('Patient4Deletion', array(
			    'local' => 'ipid',
			    'foreign' => 'ipid'
			));
			
			$this->actAs(new PatientInsert());
			$this->actAs(new PatientUpdate());
			
			
			

			/*
			$this->addListener(new HidemagicListener(array(
					"first_name",
					"last_name",
					"mobile",
			)));
			*/
			
			
			
			
		}

	}

?>