<?php
abstract class BaseWlAnlage7HospitalStays extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('wl_anlage7_hospital_stays');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('anlage7_form_id', 'integer',11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('period', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('reason', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
		
		
		$this->hasOne('WlAnlage7', array(
				'local' => 'anlage7_form_id',
				'foreign' => 'id'
		));
		
	}
}
?>