<?php

	abstract class BaseSgbvFormsHistory extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sgbv_forms_history');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			// the sgbv from where  all this antities are contained
			$this->hasColumn('parent', 'integer', 11, array('type' => 'integer', 'length' => 1));
			// here we should specify the type in order to group them better
			$this->hasColumn('entity_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('sgbv', 'action')));
			// here should be the form id or the action id
			$this->hasColumn('entity_id', 'integer', 11, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('new_status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('old_status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>