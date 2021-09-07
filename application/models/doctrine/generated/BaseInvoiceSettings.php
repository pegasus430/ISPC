<?php

	Doctrine_Manager::getInstance()->bindComponent('InvoiceSettings', 'SYSDAT');

	abstract class BaseinvoiceSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('invoice_settings');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_prefix', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_start', 'int', 11, array('type' => 'integer', 'length' => 11));
		}

	}

?>