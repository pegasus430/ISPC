<?php

	abstract class BaseLocations extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('locations_master');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('location', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
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
			$this->hasColumn('location_type', 'int', 20, array('type' => 'int', 'length' => 11));
			$this->hasColumn('location_sub_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
		}

		function setUp()
		{
		    $this->hasMany('LocationsStations', array(
		        'local' => 'id',
		        'foreign' => 'location_id'
		    ));
		    
			$this->actAs(new Timestamp());
			
			// ISPC-2024 - removed locations as contact number
			//TODO: listener needs to be adapted to work on Location update
// 			$this->addListener(new PatientContactPhoneListener(array(
// 					"is_contact"	=> "is_contact", 
// 					"phone"			=> "phone1", 
// 					"mobile"		=> "phone2", 
// 					"first_name"	=> null,
// 					"last_name" 	=> null,
// 					"other_name"	=> "location",
// 			)));

			
			
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>
