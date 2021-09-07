<?php

	abstract class BaseFormTypes extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_types');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('action', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('calendar_color', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('calendar_text_color', 'string', 255, array('type' => 'string', 'length' => 255));
		}
		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>