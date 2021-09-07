<?php

abstract class BasePatientSync extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('patient_sync');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('client', 'bigint', 11, array('type' => 'int', 'length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
	}
	
	
	public function setUp()
	{
	    parent::setUp();
	    
	    $this->actAs(new Timestamp());
	}

}

?>