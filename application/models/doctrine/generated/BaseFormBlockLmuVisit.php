<?php

abstract class BaseFormBlockLmuVisit extends Doctrine_Record
{
    
    /**
     * default when inserting into patient_course
     */
    const PATIENT_COURSE_TYPE       = 'K';
    
	function setTableDefinition()
	{
		$this->setTableName('form_block_lmu_visit');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		//ISPC-2683 Carmen 15.10.2020
		$this->hasColumn('source', 'enum', 3, array(
				'type' => 'enum',
				'length' => 3,
				'fixed' => false,
				'unsigned' => false,
				'values' =>
				array(
						0 => 'cf',
						1 => 'charts',
				),
				'primary' => false,
				'default' => null,
				'notnull' => true,
				'autoincrement' => false,
		));
		$this->hasColumn('vigilance_awareness_date', 'timestamp', null, array(
				'type' => 'timestamp',
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false,
		));
		//--
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));

		$this->hasColumn('phase', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('karnofsky', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('bewusstsein', 'string', 255, array('type' => 'string','length' => 255));
			
		$this->hasColumn('ort', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('person', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('situation', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('zeit', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('keineorient', 'integer', 1, array('type' => 'integer','length' => 1));

		$this->hasColumn('klau_diag', 'string', null, array('type' => 'string','length' => null));
		$this->hasColumn('klau_frage', 'string', null, array('type' => 'string','length' => null));
        $this->hasColumn('linkedklau', 'integer', 11, array('type' => 'integer','length' => 11));

		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
	}

	function setUp()
	{
		$this->actAs(new TimeStamp());
		
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
