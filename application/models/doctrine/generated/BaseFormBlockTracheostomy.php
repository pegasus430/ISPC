<?php

abstract class BaseFormBlockTracheostomy extends Doctrine_Record 
{
    
    /**
     * default when inserting into patient_course
     */
    const PATIENT_COURSE_TYPE       = 'K';
    
	function setTableDefinition()
	{
		$this->setTableName('form_block_tracheostomy');

		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
				
		$this->hasColumn('size', 'string', 255);		
		$this->hasColumn('designation', 'string', 255);
		$this->hasColumn('company', 'string', 255);
		$this->hasColumn('speaking_cannula', 'int', 1);
		$this->hasColumn('cuff_pressure', 'integer', 11);
		$this->hasColumn('change_interval', 'integer', 11);	
		$this->hasColumn('last_change', 'date');	
		$this->hasColumn('change_by', 'string', 255);	
		
		$this->hasColumn('isdelete', 'integer', 1);
		
		//create_date create_user change_date change_user
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