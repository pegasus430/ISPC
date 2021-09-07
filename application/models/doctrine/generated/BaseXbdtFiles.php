<?php
abstract class BaseXbdtFiles extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('xbdt_files');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('file_content', 'text',NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('file_actions', 'text',NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('export_request', 'text',NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
	 	$this->actAs(new Timestamp());
	}
	

}

?>