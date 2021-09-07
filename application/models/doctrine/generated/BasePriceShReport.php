<?php

	abstract class BasePriceShReport extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_sh_report');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 3));
			$this->hasColumn('doctor', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('nurse', 'decimal', 10, array('scale' => 2));
		}

		function setUp()
		{
			
		}

	}

?>