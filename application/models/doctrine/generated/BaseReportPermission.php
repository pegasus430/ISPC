<?php


abstract class BaseReportPermission extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('report_permissions');
		$this->hasColumn('id', 'integer', 8, array('type' => 'integer','length' => 8, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer','length' => 8));
		$this->hasColumn('report_id', 'varchar', 255, array('type' => 'varchar','length' => 255));
		
		
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());	
	
	}
	
}

?>