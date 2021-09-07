<?php
abstract class BaseVacationsReplacements extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('vacations_replacements');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('vacation', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('replacement', 'integer', 11, array('type' => 'integer', 'length' => 11));
	}
	
	
	
	
	function setUp()
	{
		$this->hasOne('User', array(
				'local' => 'userid',
				'foreign' => 'id'
		));
	}
	

}
?>