<?php

	abstract class BaseMessages extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('messages');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('sender', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('recipient', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('folder_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('msg_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('content', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('read_msg', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('delete_msg', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('replied_msg', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('source', 'string', 24, array('type' => 'string', 'length' => 24));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('recipients', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('priority', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none','low','middle','high'),'default'=>'none'));
		}

		function setUp()
		{
			$this->hasOne('User', array(
				'local' => 'id',
				'foreign' => 'sender'
			));

			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));

			
			$this->hasMany('MessagesDeleted', array(
					'local' => 'id',
					'foreign' => 'messages_id'
			));
			
			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
		}

	}

?>