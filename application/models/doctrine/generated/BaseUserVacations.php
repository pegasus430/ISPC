<?php

	abstract class BaseUserVacations extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_vacations');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}


		function setUp()
		{
			$this->hasOne('User', array(
					'local' => 'userid',
					'foreign' => 'id'
			));
		}

	}

?>