<?php

	abstract class BaseInvoicePaymentsImportStatus extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('invoice_payments_import_status');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('invoice_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('import_date', 'datetime', 255, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('filename', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('filecontent', 'blob', null, array(
					'type' => 'blob',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('filedelimiter', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'text', NULL, array('type' => 'text', 'length' => NULL));
			
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