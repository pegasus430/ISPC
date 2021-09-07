<?php

	abstract class BaseEmailLog extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('email_log');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('sender', 'string', 255, array('type' => 'bigint', 'length' => 255));
			$this->hasColumn('receiver', 'string', 255, array('type' => 'bigint', 'length' => 255));
			$this->hasColumn('subject', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ipid', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('error', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>