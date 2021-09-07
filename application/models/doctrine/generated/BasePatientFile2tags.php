<?php

	abstract class BasePatientFile2tags extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_file2tags');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('file', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('tag', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->hasOne('PatientFileUpload', array(
		        'local' => 'file',
		        'foreign' => 'id'
		    ));
		    
		}

	}

?>