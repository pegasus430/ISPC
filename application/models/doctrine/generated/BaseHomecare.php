<?php

	abstract class BaseHomecare extends Pms_Doctrine_Record {

		public $ipid = null;//ISPC-2045
		
		function setTableDefinition()
		{
			$this->setTableName('homecare');
			$this->hasColumn('id', 'integer', 20, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 20, array('type' => 'integer', 'length' => 11));
			//ISPC-2612 Ancuta 25.06.2020
			$this->hasColumn('connection_id', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'id from connections_master',
			));
			$this->hasColumn('master_id', 'integer', 11, array(
			    'type' => 'integer',
			    'length' => 11,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'id from of master entry from parent client',
			));
			//--
			
			$this->hasColumn('homecare', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('salutation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('title_letter', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('salutation_letter', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('doctornumber', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_practice', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_emergency', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_private', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kv_no', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('medical_speciality', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_till', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('logo', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('indrop', 'integer', 1, array('type' => 'indrop', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('ik_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('is_contact', 'integer', 1, array(
					'type' => 'integer',
					'length' => 1,
					'default' => 0,
					'comments' => 'ist die Kontakt-Telefonnummer',
			));
		}

		function setUp()
		{
		    parent::setUp();
		    
			$this->hasOne('PatientHomecare', array(
				'local' => 'id',
				'foreign' => 'homeid'
			));
			$this->actAs(new Timestamp());
			
			/*
			 * if you request is from patient/versorger, prevent deleting the ones with indrop=0
			 */
	       $this->addListener(new PreventIndrodDelete(array("indrop" => "indrop")));
						
			$this->actAs(new Softdelete());
			
			$this->addListener(new PatientContactPhoneListener(array(
					"is_contact"	=> "is_contact",
					"phone"			=> "phone_practice",
					"mobile"		=> null,
					"first_name"	=> "first_name",
					"last_name" 	=> "last_name",
					"other_name"	=> "homecare",
			)));
				
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>