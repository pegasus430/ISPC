<?php

	abstract class BaseUserOffline extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_offline');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('username', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('password', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->index('username', array('fields' => array('username'), 'type' => 'unique'));
		}

	}

?>