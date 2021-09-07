<?php

	abstract class BaseSocialCodePriceListGroups extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_price_list_groups');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('private', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>