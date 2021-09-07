<?php

abstract class BaseMemberships extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('memberships');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('membership', 'string', 255, array('type' => 'string','length' => NULL));
		$this->hasColumn('shortcut', 'string', 10, array('type' => 'string','length' => 255));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		
	}
	
	function setUp()
	{
	 	$this->actAs(new Timestamp());
	}
	

}

?>