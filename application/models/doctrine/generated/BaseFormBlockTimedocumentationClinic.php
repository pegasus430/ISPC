<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */

abstract class BaseFormBlockTimedocumentationClinic extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('form_block_timedocumentation_clinic');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('contact_form_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('patient_case_status_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('patient_case_type', 'string', 16, array('type' => 'string','length' => 16));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));

		//TODO-4163
		//ISPC-2815
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));

		$this->hasColumn('minutes', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_patient', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_family', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_systemisch', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_profi', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('timelog', 'string', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}

	function setUp()
	{
		$this->hasOne('ContactForms as ContactForm', array(
            'local' => 'contact_form_id',
            'foreign' => 'id'
        ));

		$this->hasMany('FormBlockTimedocumentationClinicUser', array(
			'local' => 'contact_form_id',
			'foreign' => 'form_id',
			'cascade' => array('delete'),
		));

		$this->actAs(new Softdelete());
		$this->actAs(new TimeStamp());
	}

}

?>
