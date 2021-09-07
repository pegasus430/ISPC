<?php

	abstract class BaseMessagesDeleted extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('messages_deleted');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('messages_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('sender', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('recipient', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('folder_id', 'int', 11, array('type' => 'int', 'length' => 11));

		}

		function setUp()
		{
			
			$this->hasOne('Messages', array(
				'local' => 'messages_id',
				'foreign' => 'id'
			));

			$this->actAs(new Timestamp());
			
		}

	}

?>