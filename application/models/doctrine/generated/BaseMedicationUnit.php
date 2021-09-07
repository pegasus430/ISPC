<?php
abstract class BaseMedicationUnit extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('medication_unit');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
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
		$this->hasColumn('extra', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('unit', 'string',255, array('type' => 'string','length' => 255));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
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