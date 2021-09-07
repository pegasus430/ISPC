<?php

	abstract class BaseRpInvoiceItems extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('rp_invoice_items');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('qty_home', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('qty_nurse', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('qty_hospiz', 'int', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('price_home', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price_nurse', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price_hospiz', 'int', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('total_home', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('total_nurse', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('total_hospiz', 'decimal', 10, array('scale' => 2));
			
			$this->hasColumn('custom', 'int', 1, array('type' => 'integer', 'length' => 1));     //ISPC-2747 Lore 25.11.2020
			//ISPC-2747 Lore 07.12.2020
			$this->hasColumn('qty', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('total_custom', 'decimal', 10, array('scale' => 2));
			//.
			
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>