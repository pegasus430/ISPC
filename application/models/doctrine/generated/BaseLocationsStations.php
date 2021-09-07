<?php

abstract class BaseLocationsStations extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('locations_stations');
		$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('station', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('client_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		$this->hasColumn('location_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		$this->hasColumn('phone1', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('phone2', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
		
		/*
		 * TODO: ISPC-2121 on station update, update all the records like location does now
		 * this will take 2 days
		 * wacky ideea, trigger PatientLocation update by modifying the change_date
		 */
		/*
		$this->addListener(new PatientContactPhoneListener(array(
		    "is_contact"	=> "is_contact",
		    "phone"			=> "phone1",
		    "mobile"		=> "phone2",
		    "first_name"	=> null,
		    "last_name" 	=> null,
		    "other_name"	=> "station",
		)));
		*/
	}

}

?>
