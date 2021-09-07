<?php

	abstract class BasePriceDaily extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_daily');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 3, array('type' => 'string', 'length' => 3));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('client', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('doctor', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('duty_nurse', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('duty_doctor', 'decimal', 10, array('scale' => 2));
			//ISPC 1512
			$this->hasColumn('dta_id', 'string', 255, array('type' => 'sting', 'length' => 255));
			$this->hasColumn('dta_price', 'decimal', 10, array('scale' => 2));
		}

		function setUp()
		{
			
		}

	}

?>