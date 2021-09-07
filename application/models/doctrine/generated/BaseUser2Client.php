<?php

	abstract class BaseUser2Client extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user2client');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
		}

		function setUp()
		{
			$this->hasOne('User', array(
				'local' => 'user',
				'foreign' => 'id'
			));
			
			
			$this->actAs(new Timestamp());
		}

	}

?>