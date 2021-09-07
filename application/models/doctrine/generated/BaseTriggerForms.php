<?php

	abstract class BaseTriggerForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('trigger_forms');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formname', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->hasMany('TriggerFields', array(
				'local' => 'id',
				'foreign' => 'formid'
			));
		}

	}

?>