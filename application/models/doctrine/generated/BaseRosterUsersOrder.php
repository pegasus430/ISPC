<?php

	abstract class BaseRosterUsersOrder extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('roster_users_order');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('user_sort', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('group_sort', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sort_order', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>