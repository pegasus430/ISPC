<?php

	abstract class BaseUserPageResults extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_page_results');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('page', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tab', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('results', 'string', 255, array('type' => 'string', 'length' => 255));
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