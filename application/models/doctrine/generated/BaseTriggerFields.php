<?php

	abstract class BaseTriggerFields extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('trigger_fields');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('fieldname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_label', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isform', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{

			$this->hasOne('TriggerForms', array(
				'local' => 'formid',
				'foreign' => 'id'
			));
		}

	}

?>