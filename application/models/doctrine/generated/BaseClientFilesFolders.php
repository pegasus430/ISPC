<?php

	abstract class BaseClientFilesFolders extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_files_folders');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('folder_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('ClientFileUpload', array(
				'local' => 'clientid',
				'foreign' => 'clientid'
			));
			
			$this->actAs(new Timestamp());
		}

	}

?>