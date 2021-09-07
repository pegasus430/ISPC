<?php

	abstract class BaseUserMessageClient extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_message_client');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

	}

?>