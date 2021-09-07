<?php

abstract class BaseBayEmergencyPlan extends Doctrine_Record {
	
	function setTableDefinition() 
	{
		$this->setTableName('bay_emergency_plan');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		
		// block notfallplan
		$this->hasColumn('notfallplan', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pat_address', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pat_phone', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('hausarzt', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('client_phone', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('client_cellphone', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('notarzt', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('mobil', 'string', 255, array('type' => 'string', 'length' => 255));
		
		//block Notfallmedikation
		$this->hasColumn('akuteblutungen_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('akuteblutungen_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('akuteblutungen_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('akuteblutungen_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('atemnot_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('atemnot_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('atemnot_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('atemnot_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('rasselatmung_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('rasselatmung_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('rasselatmung_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('rasselatmung_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('unruhe_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('unruhe_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('unruhe_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('unruhe_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('darmverschluss_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('darmverschluss_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('darmverschluss_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('darmverschluss_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('schmerzen_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('schmerzen_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('schmerzen_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('schmerzen_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('ubelkeit_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('ubelkeit_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('ubelkeit_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('ubelkeit_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('delir_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('delir_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('delir_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('delir_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('krampfanfall_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('krampfanfall_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('krampfanfall_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('krampfanfall_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('fieber_vompat', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('fieber_vomart', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('fieber_dosierung', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('fieber_24std', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('create_user', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('change_user', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());
	}
}