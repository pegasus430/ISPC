<?php

	abstract class BaseRosterClientUsersRows extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('roster_client_users_rows');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('rows_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>