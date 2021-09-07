<?php

	abstract class BaseClientSapvPrices extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_sapv_prices');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('sapv_type', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('price', 'int', 11, array('type' => 'string', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>