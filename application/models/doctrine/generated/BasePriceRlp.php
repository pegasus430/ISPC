<?php

	abstract class BasePriceRlp extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_rlp');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 50, array('type' => 'string', 'length' => 20));
			$this->hasColumn('location_type', 'string', 50, array('type' => 'string', 'length' => 20));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('dta_digits_3_4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dta_digits_7_10', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dta_price', 'decimal', 10, array('scale' => 2));
		}

	}

?>