<?php

abstract class BaseLmuPatientSpecialAttributes extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('lmu_patient_special_attributes');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('phase', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('karnofsky', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('bewusstsein', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('orient_ort', 'integer', 1, array('type' => 'integer','length' => 1));	
		$this->hasColumn('orient_person', 'integer', 1, array('type' => 'integer','length' => 1));	
		$this->hasColumn('orient_situation', 'integer', 1, array('type' => 'integer','length' => 1));	
		$this->hasColumn('orient_zeit', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('keineorient', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('case_number', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('oberarzt', 'integer', 11, array('type' => 'integer','length' => 1));
        $this->hasColumn('assistenzarzt', 'integer', 11, array('type' => 'integer','length' => 1));

		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));

        $this->hasColumn('infection', 'string', NULL, array('type' => 'string','length' => NULL));
        $this->hasColumn('priority', 'integer', 11, array('type' => 'integer','length' => 11));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
	}

}

?>
