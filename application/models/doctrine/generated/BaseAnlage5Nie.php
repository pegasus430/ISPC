<?php


abstract class BaseAnlage5Nie extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('anlage5nie');
		$this->hasColumn('id', 'bigint', 11, array('type' => 'bigint', 'length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('sapv_period', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('sapv_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('sapv_to', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('anlage5_checkbox', 'string', 50, array('type' => 'string', 'length' => 50));
		$this->hasColumn('erst_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('erst_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('erst_time', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('beratung_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('beratung_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('beratung_time', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse1_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse1_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse1_time', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse2_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse2_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse2_time', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse3_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse3_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('nurse3_time', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('doctor1_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('doctor1_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('doctor1_time', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('doctor2_daily', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('doctor2_wtl', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('doctor2_time', 'string', 255, array('type' => 'string', 'length' => 255));

	}

	function setUp()
	{
		$this->actAs(new Timestamp());
	}

}

?>