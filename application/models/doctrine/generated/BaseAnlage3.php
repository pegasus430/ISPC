<?php

abstract class BaseAnlage3 extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('anlage3');
		$this->hasColumn('id', 'bigint', 11, array('type' => 'bigint', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('discharge_date', 'date', 255, array('type' => 'date', 'length' => 255));
		$this->hasColumn('discharge_reason', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('death_date', 'date', 255, array('type' => 'date', 'length' => 255));
		$this->hasColumn('checkbox_death', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('discharge_location', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('members_included', 'integer', 1, array('type' => 'integer', 'length' => 1));
		
	}

	function setUp()
	{
		$this->actAs(new Createtimestamp());
	}

}

?>