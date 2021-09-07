<?php

	abstract class BaseSocialCodePriceList extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_price_list');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price_sheet', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('private', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>