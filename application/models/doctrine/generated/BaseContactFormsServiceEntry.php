<?php
abstract class BaseContactFormsServiceEntry extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('contact_forms_service_entry');
		$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('service_entry_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('last_value', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('curent_value', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('entry_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}
}