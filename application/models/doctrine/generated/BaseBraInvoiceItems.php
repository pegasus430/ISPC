<?php

	abstract class BaseBraInvoiceItems extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bra_invoice_items');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qty', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('total', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('custom', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));
			
		}

	}

?>