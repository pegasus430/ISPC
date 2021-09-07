<?php

	abstract class BasePatientUsers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_users');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('allowforall', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('iskeyuser', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}
		

	}

?>