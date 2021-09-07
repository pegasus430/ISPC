<?php
/**
 * BasePatientCourse
 *
 *
 * @package    ISPC
 */
abstract class BasePatientCourse extends Doctrine_Record 
{

	function setTableDefinition()
	{
		$this->setTableName('patient_course');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('user_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('course_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('course_type', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('course_title', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('recordid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('recorddata', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('wrong', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('isstandby', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('wrongcomment', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('ishidden', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('isserialized', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('source_ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('done_date', 'timestamp', 255, array('type' => 'timestamp', 'length' => 255));
		$this->hasColumn('done_name', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('done_id', 'integer', 11, array('type' => 'inteeger', 'length' => 11));
		//$this->hasColumn('alien', 'integer', 1, array('type' => 'inteeger','length' => 1)); //used ONYL Nico sync, do NOT UPLOAD
		
		
	}

	function setUp()
	{
	    
	    /*
	     * ISPC-2071
	     * if a cf's-form-block is edited, the old values from this block is removed, and you display only the latest value
	     * p.s. tha entry is not wrong, it was just updated, so you still have the old one as is_removed
	     */
	    $this->hasOne('PatientCourseExtra', array(
	        'local' => 'id',
	        'foreign' => 'patient_course_id'
	    ));
	    
	    /* 
	     * ISPC-2071
	     * leave this listener first
	     */
		$this->addListener(new ContactForms2PatientCourseListener(array(
// 		    "disabled" => false
		)), 'ContactForms2PatientCourseListener');
		
		$this->actAs(new Timestamp());
		$this->actAs(new Trigger());
		$this->actAs(new PatientInsert());
		
		
	}

}

?>