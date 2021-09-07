<?php

	abstract class BaseMessageFolder extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('message_folder');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('folder_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('parentid', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isedit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('total_messages', 'integer', 11, array('type' => 'integer', 'length' => 11));
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

			$this->actAs(new Timestamp());
		}

	}

?>