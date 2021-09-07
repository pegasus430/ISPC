<?php
abstract class BasePatientRemedies extends Pms_Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('patient_remedies');
		$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('remedies', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('supplier', 'string', 50, array('type' => 'string', 'length' => 50));
		$this->hasColumn('suppstatus', 'string', 255, array('type' => 'string', 'length' => 255)); //ISPC-2381 Carmen 26.01.2021
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());
		
		$this->actAs(new Softdelete());
		
		$this->hasOne('Supplies', array(
		    'local' => 'supplier',
		    'foreign' => 'id'
		));
	}
	

}
?>