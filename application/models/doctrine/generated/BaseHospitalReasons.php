<?php

abstract class BaseHospitalReasons extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('locations_hospital_reasons');
		$this->hasColumn('id', 'int', 11, array('type' => 'int', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
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
		$this->hasColumn('reason', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('reason_old_id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
		$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
		
		
		//ISPC-2612 Ancuta 29.06.2020
		// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
		$this->addListener(new ListConnectionListner(array(
		    
		)), "ListConnectionListner");
		//
	}

}

?>
