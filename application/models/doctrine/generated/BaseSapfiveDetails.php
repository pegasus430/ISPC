<?php


abstract class BaseSapfiveDetails extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('sapfive_details');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint','length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('wunden', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('grad', 'string', 255, array('type' => 'integer','length' => 255));
		$this->hasColumn('probleme', 'string', 255, array('type' => 'string','length' => 255));
								
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());	
	}	
}

?>