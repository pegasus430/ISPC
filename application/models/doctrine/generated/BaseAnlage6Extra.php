<?php

	abstract class BaseAnlage6Extra extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage6_extra');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('period', 'varchar', 8, array('type' => 'varchar', 'length' => 8));
			$this->hasColumn('related_users', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>