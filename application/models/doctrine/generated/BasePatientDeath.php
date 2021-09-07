<?php

abstract class BasePatientDeath extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('patient_death');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint','length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('death_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('death_comment', 'text', NULL, array('type' => 'text','length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());
//		$this->actAs(new Trigger());
//		$this->actAs(new PatientUpdate());
	}

	
}

?>