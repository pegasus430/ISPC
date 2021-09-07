<?php

	abstract class BaseUsersa2Client extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('usersa2client');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

		function setUp()
		{
			$this->hasOne('User', array(
				'local' => 'user',
				'foreign' => 'id'
			));
		}

	}

?>