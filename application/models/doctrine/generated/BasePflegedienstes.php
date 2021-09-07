<?php

	abstract class BasePflegedienstes extends Pms_Doctrine_Record {

		public $ipid = null;//ISPC-2045
		
		function setTableDefinition()
		{
			$this->setTableName('pflegedienste');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
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
			$this->hasColumn('nursing', 'string', 255, array('type' => 'string', 'length' => 255));
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
			$this->hasColumn('indrop', 'integer', 1, array('type' => 'indrop', 'length' => 1));
			$this->hasColumn('medical_speciality', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_till', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('palliativpflegedienst', 'integer', 1, array('type' => 'indrop', 'length' => 1));
			$this->hasColumn('logo', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ppd', 'integer', 1, array('type' => 'indrop', 'length' => 1));
			$this->hasColumn('ik_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('is_contact', 'integer', 1, array(
					'type' => 'integer',
					'length' => 1,
					'default' => 0,
					'comments' => 'ist die Kontakt-Telefonnummer',
			));
			
			//ispc-2291
			$this->hasColumn('nursing_career', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Name versorgende Pflegefachkraft',
			));
			$this->hasColumn('qualification', 'enum', 23, array(
			    'type' => 'enum',
			    'length' => 23,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'Health and Healthcare',
			        1 => 'Health and child nurses',
			        2 => 'Nurse',
			        3 => 'Caregiver',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Qualifikation',
			));
			$this->hasColumn('qualification_extra', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Zusatzqualifikation',
			));
			$this->hasColumn('substitution_nurse', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Name versorgende Pflegefachkraft im Vertretungsfall',
			));
			$this->hasColumn('substitution_qualification', 'enum', 23, array(
			    'type' => 'enum',
			    'length' => 23,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'Health and Healthcare',
			        1 => 'Health and child nurses',
			        2 => 'Nurse',
			        3 => 'Caregiver',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Qualifikation',
			));
			$this->hasColumn('substitution_qualification_extra', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Zusatzqualifikation',
			));
				
		}

		function setUp()
		{
		    parent::setUp();
		    
		    /*
		     * @cla on 22.06.2018
		     * who the f is this ???
		     */
		    /*
			$this->hasOne('PatientPflegedienstes', array(
				'local' => 'id',
				'foreign' => 'pflid'
			));
			*/
		    
		    /*
		     * if you request is from patient/versorger, prevent deleting the ones with indrop=0
		     */
		    $this->addListener(new PreventIndrodDelete(array("indrop" => "indrop")));
		    
		    $this->actAs(new Softdelete());
		    
			$this->actAs(new Timestamp());
			
			
			$this->addListener(new PatientContactPhoneListener(array(
					"is_contact"	=> "is_contact",
					"phone"			=> "phone_practice",
					"mobile"		=> null,
					"first_name"	=> "first_name",
					"last_name" 	=> "last_name",
					"other_name"	=> "nursing"
			)));
		
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
			
		}

	}

?>