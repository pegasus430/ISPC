<?php

	abstract class BaseSgbvInvoiceItems extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sgbv_invoice_items');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('qty', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('custom', 'int', 1, array('type' => 'integer', 'length' => 1));     //ISPC-2747 Lore 25.11.2020
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));     //ISPC-2747 Lore 25.11.2020
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));    //ISPC-2747 Lore 03.12.2020
			
		}

	}

?>