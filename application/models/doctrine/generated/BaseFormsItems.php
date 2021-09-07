<?php

	abstract class BaseFormsItems extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('forms_items');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('form', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('item', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('v', 't', 'h'), 'default' => 'h'));
		}

	}

?>