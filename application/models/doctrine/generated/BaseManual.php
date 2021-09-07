<?php

	abstract class BaseManual extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('manual');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('filename', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>