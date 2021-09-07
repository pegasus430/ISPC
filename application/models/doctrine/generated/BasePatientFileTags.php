<?php

	abstract class BasePatientFileTags extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_file_tags');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('restricted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tag', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp()); // added on 21.03.2018
		}

	}

?>