<?php

	abstract class BasePatientHospizCourse extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_hospiz_course');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('user_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('course_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('course_short', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('course_long', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('wrongcomment', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>