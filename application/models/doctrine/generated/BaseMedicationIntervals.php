<?php
abstract class BaseMedicationIntervals extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('medication_intervals');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('time_interval', 'time', NULL, array('type' => 'time','length' => NULL));
		$this->hasColumn('medication_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('all','actual','isbedarfs','isivmed','isnutrition','isschmerzpumpe','treatment_care','iscrisis','isintubated')));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
	 	$this->actAs(new Timestamp());
	}
	

}

?>