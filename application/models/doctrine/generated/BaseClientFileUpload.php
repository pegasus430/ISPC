<?php

	abstract class BaseClientFileUpload extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_files');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('folder', 'integer', 20, array('type' => 'integer', 'length' => 20));
			$this->hasColumn('recordid', 'integer', 20, array('type' => 'integer', 'length' => 20));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('parent_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('revision', 'integer', 6, array('type' => 'integer', 'length' => 1));
			/**
			 * revision  -  a version number of a file is incremented by a mysql trigger query
			 */
					
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			/**
			 * link a FtpPutQueue record -> to this model's primaryKey
			 * @see FtpPutQueue2RecordListener
			 */
			$this->addListener(new FtpPutQueue2RecordListener(), 'FtpPutQueue2RecordListener');
		}

	}

?>