<?php

	abstract class BaseVwEmailsLog extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_emails_log');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('sender', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('recipient', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('content', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('recipients', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('attachment_id', 'integer', 11, array(
					'type' => 'integer',
					'length' => 11,
					'fixed' => false,
					'unsigned' => true,
					'primary' => false,
					'notnull' => false,
					'comment' => "id from member_files"
			));
				
			$this->hasColumn('batch_id', 'integer', 11, array(
					'type' => 'integer',
					'length' => 11,
					'fixed' => false,
					'unsigned' => true,
					'primary' => false,
					'notnull' => false,
					'comment' => "first id if multiple emails are sents, used to groupBy"
			));
		}

		
		function setUp()
		{
			$this->hasOne('Voluntaryworkers', array(
				'local' => 'id',
				'foreign' => 'sender'
			));

			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));
			$this->actAs(new Timestamp());
		}

	}

?>