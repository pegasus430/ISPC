<?php

	abstract class BasePriceHessen extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_hessen');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('list_type', 'string', 20, array('type' => 'string', 'length' => 20));
			$this->hasColumn('shortcut', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
		}

	}

?>