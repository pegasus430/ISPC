<?php

	abstract class BaseAnlage5Part2 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage5part2');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('diagnosis', 'integer', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('medication', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>