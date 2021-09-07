<?php

abstract class BaseFormBlockTodos extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('form_block_todos');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));

		$this->hasColumn('todo_id', 'integer', 11, array('type' => 'integer','length' => 11));
		
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
	}

	function setUp()
	{
		
		$this->hasOne('ToDos', array(
				'local' => 'todo_id',
				'foreign' => 'id'
		));
		
		$this->actAs(new TimeStamp());
	}

}

?>
