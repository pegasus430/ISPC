<?php


abstract class UserGroups extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('usergroups');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint','length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('groupname', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('groupmaster', 'string', 128, array('type' => 'string','length' => 128));
		$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint','length' => NULL));		
		$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer','length' => 11));
						
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());	
		
		$this->hasOne('Client', array(
	            'local' => 'id',
	            'foreign' => 'clientid'
	        ));
		
	}	
}

?>