<?php
abstract class BaseVitabookPatient extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('vitabook_patient');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
		
		$this->hasColumn('vitabook_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		$this->hasColumn('clientid', 'int', 11, array('type' => 'int', 'length' => 11));
		$this->hasColumn('userid', 'int', 11, array('type' => 'int', 'length' => 11));
		$this->hasColumn('isdelete', 'int', 1, array('type' => 'int', 'length' => 1));
	
	
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
	}

}

?>