<?php

	abstract class BaseInvoicePaymentsImport extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('invoice_payments_import');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice_id', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('import_file_id', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('payment_id', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('payment_table', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('healthinsurance', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('amount', 'decimal', 11, array(
					'type' => 'decimal',
					'length' => 11,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
					'scale' => '2',
			));
			$this->hasColumn('paid_date', 'datetime', 255, array('type' => 'datetime', 'length' => NULL));
			
			$this->index('clientid+isdelete', array(
					'fields' =>
					array(
							0 => 'clientid',
							1 => 'isdelete',
					),
			));
		}
		
		public function setUp()
		{
			parent::setUp();
		
			$this->actAs(new Timestamp());
			$this->actAs(new Softdelete());
		}

	}

?>