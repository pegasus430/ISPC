<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
abstract class BaseFormBlockTimedocumentationClinicUser extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('form_block_timedocumentation_clinic_user');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		$this->hasColumn('username', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('groupid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		$this->hasColumn('groupname', 'string', 255, array('type' => 'string', 'length' => 255));

		$this->hasColumn('minutes', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_patient', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_family', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_systemic', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('mins_profi', 'integer', 11, array('type' => 'integer','length' => 11));
		//ISPC-2899,Elena,23.04.2021
		$this->hasColumn('call_on_duty', 'integer', 1, array('type' => 'integer','length' => 1, 'default' =>0));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	function setUp()
	{
		$this->hasOne('FormBlockTimedocumentationClinic as TimedocumentationClinic', array(
            'local' => 'form_id',
            'foreign' => 'id'
        ));
		$this->actAs(new Softdelete());
		$this->actAs(new TimeStamp());
	}

}

?>
