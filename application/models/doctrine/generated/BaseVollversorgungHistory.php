<?php

abstract class BaseVollversorgungHistory extends Doctrine_Record{
    function setTableDefinition(){
	$this->setTableName('vollversorgung_history');
	$this->hasColumn('id', 'int', 11, array('type' => 'int','length' => 11, 'primary' => true, 'autoincrement' => true));
	$this->hasColumn('user_id', 'int', 11, array('type' => 'int','length' => 11));
	$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
	$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
	$this->hasColumn('date_type', 'int', 11, array('type' => 'int','length' => 11));
	$this->hasColumn('isdelete', 'int', 1, array('type' => 'int','length' => 1));

    }

    function setUp(){
	$this->actAs(new Timestamp());
    }
}
?>