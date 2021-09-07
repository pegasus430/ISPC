<?php

	abstract class BaseSymptomatologyZapvDetails extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('symptomatology_zapv_details');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('select_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('item_name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{

			$this->hasOne('SymptomatologyValues', array(
				'local' => 'select_id',
				'foreign' => 'details_select'
			));
		}

	}

?>