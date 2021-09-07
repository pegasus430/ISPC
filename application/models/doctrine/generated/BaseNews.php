<?php

	abstract class BaseNews extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('news');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('news_title', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('news_content', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('news_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('assign_user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('issystem', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('viewcount', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('acknowledge', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'clientid',
				'foreign' => 'id'
			));

			$this->hasOne('Client', array(
				'local' => 'userid',
				'foreign' => 'id'
			));

			$this->actAs(new Timestamp());
		}

	}

?>