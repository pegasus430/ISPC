<?php
abstract class BaseMedipumpsInvoiceItems extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('medipumps_invoice_items');
		$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
		$this->hasColumn('shortcut', 'string', 1, array('type' => 'string', 'length' => 1));
		$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('qty', 'int', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('price', 'int', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('priceset', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('f', 's')));
		$this->hasColumn('custom', 'int', 1, array('type' => 'integer', 'length' => 1));     //ISPC-2747 Lore 25.11.2020
		$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));     //ISPC-2747 Lore 25.11.2020
	}

}
?>