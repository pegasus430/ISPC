<?php

	abstract class BaseMembersSepaXml extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('members_sepa_xml');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('memberid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('invoiceid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('invoiceid_extra', 'text', null, array('type' => 'text'));
			
			$this->hasColumn('paymentid', 'int', 11, array(
					'type' => 'int',
					'length' => 11,
					'notnull' => true,
					'default' => 0,
			));
			
			$this->hasColumn('filename_nice', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('ftp_file', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('file_type', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			
			
			$this->hasColumn('status', 'int', 1, array('type' => 'integer', 'length' => 1));
			
			
			$this->hasColumn('batchid', 'integer', 11, array(
					'type' => 'integer',
					'length' => 11,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
					'default' => 0,
			));
			
			$this->hasColumn('comment', 'text', null, array(
					'type' => 'text',
					'length' => null,
					'fixed' => false,
			));
			
			
			
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'string', 'length' => 1));

			$this->index('id', array(
					'fields' => array('id'),
					'primary' => true
			));
				
			$this->index('clientid', array(
					'fields' => array('clientid')
			));
			$this->index('invoiceid', array(
					'fields' => array('invoiceid')
			));
			
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