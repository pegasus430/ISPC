<?php

	abstract class BaseInvoicePayments extends Doctrine_Record	 {
		function setTableDefinition() {
			$this->setTableName('invoice_payments');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint','length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoiceId', 'int', 1, array('type' => 'integer','length' => 1));
			$this->hasColumn('amount', 'decimal', 11, array('scale' => 2));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string','length' => 255));
			$this->hasColumn('paidDate', 'date', 255, array('type' => 'date','length' => NULL));
			$this->hasColumn('isDelete', 'int', 1, array('type' => 'integer','length' => 1));
			$this->hasColumn('create_date', 'date', NULL, array('type' => 'date','length' => NULL));
		}

		function setUp() {
			$this->actAs(new Timestamp());
		}
	}
?>