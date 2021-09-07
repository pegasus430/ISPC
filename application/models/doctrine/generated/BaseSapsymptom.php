<?php

	abstract class BaseSapsymptom extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_symptom');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('sapvalues', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('gesamt_zeit_in_minuten', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('gesamt_fahrstrecke_in_km', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('davon_fahrtzeit', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('visit_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('visit_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('', 'bayern', 'contactform')));
			
			$this->hasColumn('patient_course_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>