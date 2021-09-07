<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoiceSettings', 'SYSDAT');

	abstract class BaseInternalInvoiceSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('internal_invoice_settings');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice_prefix', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_start', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice_pay_days', 'int', 11, array('type' => 'integer', 'length' => 11));
		}

	}

?>