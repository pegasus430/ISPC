<?php

	abstract class BasePrintUsersAssigned extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('print_users_assigned');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('receipt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>