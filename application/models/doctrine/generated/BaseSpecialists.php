<?php

	abstract class BaseSpecialists extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('specialists');
			$this->hasColumn('id', 'integer', 20, array('type' => 'integer', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//ISPC-2612 Ancuta 25.06.2020-28.06.2020
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
			$this->hasColumn('practice', 'string', 255, array('type' => 'string', 'length' => 255));
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
			$this->hasColumn('phone_private', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_cell', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kv_no', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('medical_speciality', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_till', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('indrop', 'integer', 1, 
			    array(
			        'type' => 'integer', 
			        'length' => 1,
			        'default' => 1, //not in the livesearch dropdown
			    ));
			
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
		    parent::setUp();
		    
		    /*
		     * if you request is from patient/versorger, prevent deleting the ones with indrop=0
		     */
		    $this->addListener(new PreventIndrodDelete(array("indrop" => "indrop")));
		    
		    $this->actAs(new Softdelete());
		    
			$this->actAs(new Timestamp());

			$this->hasOne('SpecialistsTypes', array(
				'local' => 'medical_speciality',
				'foreign' => 'id'
			));
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>