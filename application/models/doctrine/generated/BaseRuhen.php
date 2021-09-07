<?php

	abstract class BaseRuhen extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('ruhen');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('begindate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('enddate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('einweisung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativzentrum', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('r_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('r_address', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>