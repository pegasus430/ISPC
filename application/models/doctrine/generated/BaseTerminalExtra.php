<?php

	abstract class BaseTerminalExtra extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('terminal_extra_data');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('wop', 'integer', 11, array('type' => 'varchar', 'length' => 11));
			$this->hasColumn('rsa', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('legal_family', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('country', 'varchar', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('card_expiration_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('card_read_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('approve_number', 'varchar', 255, array('type' => 'integer', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>