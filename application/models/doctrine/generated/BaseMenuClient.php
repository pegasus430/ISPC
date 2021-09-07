<?php

	abstract class BaseMenuClient extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('menu_client');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('menu_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>