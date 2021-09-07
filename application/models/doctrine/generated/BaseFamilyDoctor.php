<?php

	abstract class BaseFamilyDoctor extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('family_doctor');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			
			$this->hasColumn('self_id', 'integer', 11, array(
			    'type' => 'integer',
			    'length' => 11,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'default' => NULL,
			    'comment' => 'if this has indrop=1, is a clone, then self_id is the original one'
			));
			
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
			$this->hasColumn('doctor_bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_practice', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_private', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone_cell', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kv_no', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('indrop', 'integer', 1, array('type' => 'indrop', 'length' => 1));
			$this->hasColumn('medical_speciality', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_till', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			
			// ISPC-2257
			$this->hasColumn('shift_billing', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default'=> 0 ));
			//ISPC-2272
			$this->hasColumn('debitor_number', 'string', 255, array('type' => 'string', 'length' => 255));

			
			$this->hasColumn('infusion_protocol', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'no',
			        1 => 'yes',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Arzt w�nscht Infusionsprotokoll',
			));
			$this->hasColumn('infusion_protocol_freetext', 'string', 1024, array(
			    'type' => 'string',
			    'length' => 1024,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => '',
			));
			$this->hasColumn('emergency_call_number', 'string', 32, array(
			    'type' => 'string',
			    'length' => 32,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Notfallrufnummer',
			));
			$this->hasColumn('emergency_preparedness_1', 'string', null, array(
			    'type' => 'string',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Bereitschaft im Notfall',
			));
			$this->hasColumn('emergency_preparedness_2', 'string', null, array(
			    'type' => 'string',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			
			$this->index('isdelete', array(
			    'fields' =>
			    array(
			        0 => 'isdelete',
			    ),
			));
			$this->index('first_name', array(
			    'fields' =>
			    array(
			        0 => 'first_name',
			    ),
			));
			$this->index('last_name', array(
			    'fields' =>
			    array(
			        0 => 'last_name',
			    ),
			));
			$this->index('clientid', array(
			    'fields' =>
			    array(
			        0 => 'clientid',
			    ),
			));
			$this->index('indrop', array(
			    'fields' =>
			    array(
			        0 => 'indrop',
			    ),
			));
			$this->index('zip', array(
			    'fields' =>
			    array(
			        0 => 'zip',
			    ),
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		/*
    		$this->hasOne('PatientMaster', array(
    		    'local' => 'id',
    		    'foreign' => 'familydoc_id',
    		));
			
*/         
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
			
			
			
			//ISPC-2614 Ancuta 19.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
		}
	}

?>