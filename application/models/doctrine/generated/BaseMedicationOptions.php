<?php
abstract class BaseMedicationOptions extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('medication_options');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('medication_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('actual','isbedarfs','isivmed','isnutrition','isschmerzpumpe','treatment_care','iscrisis','isintubated')));
		$this->hasColumn('time_schedule', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
	 	$this->actAs(new Timestamp());
	}
	

}

?>