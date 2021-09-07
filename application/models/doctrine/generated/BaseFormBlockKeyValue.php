<?php

abstract class BaseFormBlockKeyValue extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('form_block_key_value');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('block', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('k', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('v', 'string', NULL, array('type' => 'string','length' => NULL));

		
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
	}

	function setUp()
	{
		$this->hasOne('ContactForms as ContactForm', array(
            'local' => 'contact_form_id',
            'foreign' => 'id'
        ));
		/**
		 * Maria:: Migration CISPC to ISPC 22.07.2020
		 */ 
		$this->actAs(new Softdelete());
		$this->actAs(new TimeStamp());
		
		//TODO-3219 Carmen 18.06.2020
		/*
		 * disabled by default, because it was created JUST for inserts from the Kontaktformular
		 */
		$this->addListener(new PostInsertWriteToPatientCourseListener(array(
				"disabled"      => true,
				"course_type"   => static::PATIENT_COURSE_TYPE,
				//"done_name"     => static::PATIENT_COURSE_DONE_NAME,
		)), 'PostInsertWriteToPatientCourse');
		//--
	}

}

?>
