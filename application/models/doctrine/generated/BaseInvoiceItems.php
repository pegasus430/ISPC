<?php

	abstract class BaseInvoiceItems extends Doctrine_Record	 {
		function setTableDefinition() {
			$this->setTableName('invoice_items');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint','length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'int', 11, array('type' => 'bigint','length' => 11));
			$this->hasColumn('invoiceId', 'int', 1, array('type' => 'string','length' => 1));
			$this->hasColumn('itemLabel', 'string', 255, array('type' => 'string','length' => 255));
			$this->hasColumn('itemValue', 'int', 11, array('type' => 'string','length' => 11));
			$this->hasColumn('itemString', 'string', 255, array('type' => 'string','length' => 255));
			$this->hasColumn('itemVat', 'int', 11, array('type' => 'string','length' => 11));
			$this->hasColumn('itemVatValue', 'int', 11, array('type' => 'string','length' => 11));
			$this->hasColumn('sortOrder', 'int', 1, array('type' => 'string','length' => 1));
			$this->hasColumn('isDelete', 'int', 1, array('type' => 'string','length' => 1));
			$this->hasColumn('create_date', 'date', NULL, array('type' => 'date','length' => NULL));
		}

		function setUp() {
			$this->actAs(new Timestamp());
		}
	}
?>