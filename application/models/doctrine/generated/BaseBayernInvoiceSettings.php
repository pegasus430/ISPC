<?php

	abstract class BaseBayernInvoiceSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bayern_invoice_settings');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('listid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('option_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('value', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>