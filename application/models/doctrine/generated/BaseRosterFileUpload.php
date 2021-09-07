<?php

	abstract class BaseRosterFileUpload extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('dienstplan_files');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>