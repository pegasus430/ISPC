<?php

	abstract class BaseDiagnosisMeta extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('diagnosis_meta');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('meta_title', 'text', NULL, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>