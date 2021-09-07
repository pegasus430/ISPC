<?php

	abstract class BaseRemindersInvoice extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('reminders_invoice');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoiceid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('reminder_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('reminder_doc_type', 'enum', 3, array(
					'type' => 'enum',
					'length' => 3,
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => 'pdf',
							1 => 'docx',
					),
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
					'default' => 'pdf'
			));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>