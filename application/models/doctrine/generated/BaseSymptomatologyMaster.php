<?php

	abstract class BaseSymptomatologyMaster extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('symptomatology_master');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('sym_description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('min_alert', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('max_alert', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('alert_color', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('entry_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('input_value', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('critical_value', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>