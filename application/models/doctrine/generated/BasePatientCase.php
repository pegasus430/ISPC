<?php

	abstract class BasePatientCase extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_case');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('epid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('admission_date', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ref_doc', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('refdoc_details', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('discharge_date', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('discharge_time', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dis_methodid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('dis_locationid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new PatientUpdate());
		}

	}

?>