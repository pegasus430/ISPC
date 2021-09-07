<?php

	abstract class BaseSocialCodePriceListGrouped extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_price_list_grouped');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('price_list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>