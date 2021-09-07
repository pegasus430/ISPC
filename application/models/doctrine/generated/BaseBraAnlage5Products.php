<?php

	abstract class BaseBraAnlage5Products extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bra_anlage5_products');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anlage5_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('start_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('end_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qty', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('total', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>