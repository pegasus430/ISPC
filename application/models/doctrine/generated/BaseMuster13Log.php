<?php

	abstract class BaseMuster13Log extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('muster13_log');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('user', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('muster13id', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('operation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('created','edited','deleted','duplicated','printed')));
			$this->hasColumn('source', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>