<?php

abstract class BaseStammblatt5 extends Doctrine_Record {
	
	function setTableDefinition()
	{
		$this->setTableName('stammblatt5');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('familienstand', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('wohnsituation', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('allergien', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('zuzahlung', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('pflegestufe', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('patientenverfugung', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('vorsorgevollmacht', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('bevollmachtigter', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('bevollmachtigter_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('betreuung', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('betreuer', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('betreuer_handy', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('betreuer_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('betreuer_fax', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('erstkontakt_am', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('erstkontakt_durch', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('ambulant', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('stationar', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('religion', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('ecog', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('genogramm', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('main_diagnosis', 'text', NULL, array('type' => 'text', 'length' => NULL));
		//ISPC-1790
		$this->hasColumn('cntpers_1_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('cntpers_1_handy', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('cntpers_2_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('cntpers_2_handy', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('hausarzt_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('hausarzt_fax', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pflegedienst_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pflegedienst_fax', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('apotheke_tel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('apotheke_fax', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pattel', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pathandy', 'string', 255, array('type' => 'string', 'length' => 255));
		
		//ISPC-2590 Andrei 25.05.2020
		$this->hasColumn('cntpers_1_legalguardian', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('cntpers_1_hatversorgungsvollmacht', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('cntpers_2_legalguardian', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('cntpers_2_hatversorgungsvollmacht', 'integer', 1, array('type' => 'integer', 'length' => 1));
		
		
		
		
		
	}
	
	function setUp()
	{
		$this->actAs(new Timestamp());
	}
	

}
?>