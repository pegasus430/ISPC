<?php

	abstract class BaseAnlage5Pretreatment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage5_pretreatment');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('formid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('symptom', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>