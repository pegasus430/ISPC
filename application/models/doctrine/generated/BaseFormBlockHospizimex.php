<?php

abstract class BaseFormBlockHospizimex extends Doctrine_Record 
{

	    /**
	     * default when inserting into patient_course
	     */
	    const PATIENT_COURSE_TYPE       = 'EA';
	    
	    public $course_title = '';
	    
	    
		function setTableDefinition()
		{
			$this->setTableName('form_block_hospiz_imex');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('import', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('export', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
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