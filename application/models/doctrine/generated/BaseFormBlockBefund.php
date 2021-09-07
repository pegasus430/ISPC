<?php

abstract class BaseFormBlockBefund extends Doctrine_Record 
{

    /**
     * default when inserting into patient_course
     */
    const PATIENT_COURSE_TYPE       = 'B';
    
	function setTableDefinition()
	{
		$this->setTableName('form_block_befund');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));

		$this->hasColumn('kopf', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('kopf_text', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('thorax', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('thorax_text', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('abdomen', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('abdomen_text', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('extremitaten', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('extremitaten_text', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('haut_wunden', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('haut_wunden_text', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('neurologisch_psychiatrisch', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('neurologisch_psychiatrisch_text', 'string', 255, array('type' => 'string', 'length' => 255));

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