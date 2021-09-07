<?php
abstract class BaseXbdtActions extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('xbdt_actions');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('action_id', 'string',255, array('type' => 'string','length' => 255));
		$this->hasColumn('name', 'string',NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('groupname', 'string',255, array('type' => 'string','length' => 255));
		$this->hasColumn('block_option_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('available', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
		$this->hasColumn('contact_form_block', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('goaii', 'ebmii')));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
	 	$this->actAs(new Timestamp());
	}
	

}

?>