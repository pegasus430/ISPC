<?php
abstract class BaseWlAnlage7 extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('wl_anlage7');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('client_fax', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('death_date', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('has_hospital_treatment', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('', 'yes', 'no')));
		$this->hasColumn('dead_in_hospital', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('nursing', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1', '2', '3')));
		$this->hasColumn('service_phone', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('service_phone_amount', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('service_visit', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('service_visit_amount', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('rated', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1', '2', '3', '4', '5')));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
		
		$this->hasOne('WlAnlage7HospitalStays', array(
				'local' => 'id',
				'foreign' => 'anlage7_form_id'
		));
		
	}
}
?>