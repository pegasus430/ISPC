<?php

	abstract class BaseUserSessions extends Pms_DoctrineRecord {

		function setTableDefinition()
		{
			$this->setTableName('user_sessions');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('session', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lastaction', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->index('session', array('fields' => array('sessions'), 'type' => 'unique'));
		}

	}

?>