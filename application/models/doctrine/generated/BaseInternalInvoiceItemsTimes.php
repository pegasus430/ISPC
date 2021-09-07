<?php

	abstract class BaseInternalInvoiceItemsTimes extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('internal_invoice_items_times');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('item', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('start_hours', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_hours', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>