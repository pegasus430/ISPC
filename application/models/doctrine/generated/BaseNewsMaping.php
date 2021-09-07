<?php

	abstract class BaseNewsMaping extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('news_mapping');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('newsid', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'clientid',
				'foreign' => 'id'
			));

			$this->hasOne('User', array(
				'local' => 'userid',
				'foreign' => 'id'
			));

			$this->hasOne('News', array(
				'local' => 'newsid',
				'foreign' => 'id'
			));
		}

	}

?>