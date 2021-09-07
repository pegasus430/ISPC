<?php
abstract class BasePatientXbdtActions extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('patient_xbdt_actions');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('userid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('team', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('course_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('action', 'integer', 20, array('type' => 'integer','length' => 11));
		$this->hasColumn('action_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
		$this->hasColumn('file_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('edited_from', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('list', 'contactform', 'bdt','heinvoice','internalinvoice')));//TODO-3012 Ancuta 20-23.03.2020 - added  new type:  internalinvoice
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
		$this->hasOne('XbdtActions', array(
				'local' => 'action',
				'foreign' => 'id'
		));
		
	 	$this->actAs(new Timestamp());
	}
	

}

?>