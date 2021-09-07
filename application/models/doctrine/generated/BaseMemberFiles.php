<?php

	abstract class BaseMemberFiles extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('member_files');
			
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('parent_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('revision', 'integer', 11, array('type' => 'integer', 'length' => 11));
			/**
			 * revision  -  a version number of a file is incremented by a mysql trigger query
			 */
			
			
			$this->hasColumn('clientid', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			
			$this->hasColumn('member_id', 'integer', 11, array('type' => 'integer', 'length' => 11));		
			$this->hasColumn('template_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_showname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_realname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ftp_path', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('template_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
							
			
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