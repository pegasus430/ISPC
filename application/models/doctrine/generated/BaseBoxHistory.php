<?php

	abstract class BaseBoxHistory extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('box_history');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('formid', 'varchar', 10, array('type' => 'varchar', 'length' => 10));
			$this->hasColumn('fieldname', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('fieldvalue', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new PatientInsert());
		}

	}

?>