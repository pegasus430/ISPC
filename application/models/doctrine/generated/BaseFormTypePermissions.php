<?php
abstract class BaseFormTypePermissions extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('form_type_permissions');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('type', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}

}
?>