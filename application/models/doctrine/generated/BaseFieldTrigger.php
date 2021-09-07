<?php

	abstract class BaseFieldTrigger extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('field_triggers');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('fieldid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('triggerid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('event', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('operator', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('operand', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('inputs', 'text', NULL, array('type' => 'text'));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			$this->hasOne('TriggerTriggers', array(
				'local' => 'triggerid',
				'foreign' => 'id'
			));

			$this->hasOne('TriggerForms', array(
				'local' => 'formid',
				'foreign' => 'id'
			));

			$this->hasOne('TriggerFields', array(
				'local' => 'fieldid',
				'foreign' => 'id'
			));
		}

	}

?>