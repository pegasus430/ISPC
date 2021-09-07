<?php

	abstract class BasePatientMemo extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_memo');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('memo', 'string', NULL, array('type' => 'string', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>