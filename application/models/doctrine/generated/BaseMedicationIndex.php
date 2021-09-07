<?php

	abstract class BaseMedicationIndex extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('medication_index');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pzn', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('package_size', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('amount_unit', 'float', NULL, array('type' => 'string', 'float' => NULL));
			$this->hasColumn('price', 'float', NULL, array('type' => 'string', 'float' => NULL));
			$this->hasColumn('extra', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('manufacturer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('package_amount', 'float', NULL, array('type' => 'string', 'float' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>