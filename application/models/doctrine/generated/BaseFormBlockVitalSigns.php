<?php
abstract class BaseFormBlockVitalSigns extends Pms_Doctrine_Record 
{

    /**
     * default when inserting into patient_course
     * if client has module=117 then the course_type is saved with B
     */
    const PATIENT_COURSE_TYPE       = 'K';
    
	function setTableDefinition()
	{
		$this->setTableName('form_block_vital_signs');
		
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));

		$this->hasColumn('source', 'enum', 16, array(
		    'type' => 'enum',
		    'length' => 16,
		    'fixed' => false,
		    'unsigned' => false,
		    'values' =>
		    array(
		        0 => 'cf',
		        1 => 'icon',
		        2 => 'mambo_assessment', //ISPC-2292 @cla from mambo assessment
		    	3 => 'charts', //ISPC-2515 Carmen 16.04.2020 #ISPC-2512PatientCharts
		    	4 => 'medication' //ISPC-2664 Carmen 28.09.2020
		    ),
		    'primary' => false,
		    'default' => 'cf',
		    'notnull' => true,
		    'autoincrement' => false,
		));
		
		$this->hasColumn('signs_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('blood_pressure_a', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('blood_pressure_b', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('puls', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('respiratory_frequency', 'decimal', 10, array('scale' => 2));	
		$this->hasColumn('temperature', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('temperature_dd', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('oxygen_saturation', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('blood_sugar', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('weight', 'decimal', 10, array('scale' => 3)); //ISPC-2664 Carmen 28.09.2020
		// ISPC-1920 Carmen 2017.03.16
		$this->hasColumn('height', 'decimal', 10, array('scale' => 2));
		
		$this->hasColumn('head_circumference', 'decimal', 10, array('scale' => 2));
		// ISPC-2068 Carmen 2017.10.27
		$this->hasColumn('waist_circumference', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		
		
		//from mambo assessment
		$this->index('ipid', array(
		    'fields' =>
		    array(
		        0 => 'ipid',
		    ),
		));
		$this->index('signs_date', array(
		    'fields' =>
		    array(
		        0 => 'signs_date',
		    ),
		));
		$this->index('isdelete', array(
		    'fields' =>
		    array(
		        0 => 'isdelete',
		    ),
		));
		$this->index('contact_form_id', array(
		    'fields' =>
		    array(
		        0 => 'contact_form_id',
		    ),
		));
		$this->index('source', array(
		    'fields' =>
		    array(
		        0 => 'source',
		    ),
		));
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());
		
		$this->actAs(new Softdelete());
		
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