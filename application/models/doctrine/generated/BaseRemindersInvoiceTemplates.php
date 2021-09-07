<?php

	abstract class BaseRemindersInvoiceTemplates extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('reminders_invoice_templates');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('template_type', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('first_warning', 'second_warning'), 'default' => 'default_warning'));
			$this->hasColumn('file_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('file_path', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>