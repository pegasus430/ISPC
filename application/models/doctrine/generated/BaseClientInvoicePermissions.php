<?php

	abstract class BaseClientInvoicePermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_invoice_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('canadd', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canedit', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canview', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('candelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			
		}

	}

?>