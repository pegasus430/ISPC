<?php

	abstract class BaseHeInvoiceItemsPeriod extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('he_invoice_items_period');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('item', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('paid', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('from_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('till_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>