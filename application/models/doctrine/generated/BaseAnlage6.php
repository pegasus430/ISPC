<?php

	abstract class BaseAnlage6 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage6');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('shortcut', 'varchar', 1, array('type' => 'varchar', 'length' => 1));
			$this->hasColumn('date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>