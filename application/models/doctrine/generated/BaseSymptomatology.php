<?php

	abstract class BaseSymptomatology extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('symptomatology');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('setid', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
			$this->hasColumn('symptomid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('kvnoid', 'string', 12, array('type' => 'string', 'length' => 12));
			$this->hasColumn('custom_description', 'text', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('entry_date', 'datetime');
			$this->hasColumn('input_value', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => null));
			$this->hasColumn('critical_value', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => null));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
		}

	}

?>