<?php

	abstract class BaseSymptomatologyValues extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('symptomatology_values');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('set', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('custom', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('value', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('alias', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('other_alias', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('details_select', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->hasOne('SymptomatologySets', array(
				'local' => 'set',
				'foreign' => 'id'
			));
			$this->hasOne('SymptomatologyZapvDetails', array(
				'local' => 'details_select',
				'foreign' => 'select_id'
			));
		}

	}

?>