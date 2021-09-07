<?php


abstract class BaseFormBlockServiceEntry extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('service_entry');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('item_name', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}

	

}

?>