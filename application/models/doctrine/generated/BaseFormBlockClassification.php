<?php

abstract class BaseFormBlockClassification extends Doctrine_Record 
{

    /**
     * default when inserting into patient_course
     */
    const PATIENT_COURSE_TYPE       = 'K';
    
	function setTableDefinition()
	{
		$this->setTableName('form_block_classification');

		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('beratung', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('koordination', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('intern', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('psyhosocial', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('grief_endcall', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
		
		/*
		 * disabled by default, because it was created JUST for inserts from the Kontaktformular
		 */
		$this->addListener(new PostInsertWriteToPatientCourseListener(array(
		    "disabled"    => true,
		    "course_type" => self::PATIENT_COURSE_TYPE,
		)),
		    'PostInsertWriteToPatientCourse'
		);
	}

}

?>