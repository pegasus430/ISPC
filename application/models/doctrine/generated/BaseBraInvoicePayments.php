<?php

	abstract class BaseBraInvoicePayments extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bra_invoice_payments');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('amount', 'decimal', 11, array('scale' => 2));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('paid_date', 'datetime', 255, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>