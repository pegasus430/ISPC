<?php
abstract class BaseUser2admission extends Doctrine_Record{
	function setTableDefinition(){
		$this->setTableName('user2admission');
		$this->hasColumn('id', 'int', 11, array('type' => 'int','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('admission_type', 'enum', null, array( 'type' => 'enum', 'notnull' => false, 'values' => array('a','r')));
		$this->hasColumn('admission_status', 'enum', null, array( 'type' => 'enum', 'notnull' => false, 'values' => array('n','h')));
		$this->hasColumn('user_id', 'int', 11, array('type' => 'int','length' => 11));
		$this->hasColumn('user_type', 'enum', null, array( 'type' => 'enum', 'notnull' => false, 'values' => array('nurse','doc')));
	}

	function setUp(){
		$this->actAs(new Timestamp());
	}
}
?>