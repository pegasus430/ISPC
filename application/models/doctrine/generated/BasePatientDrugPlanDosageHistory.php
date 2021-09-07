<?php

	abstract class BasePatientDrugPlanDosageHistory extends Doctrine_Record {

	    function setTableDefinition()
	    {
	        $this->setTableName('patient_drugplan_dosage_history');
	        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
	        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
	        $this->hasColumn('history_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pdd_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pdd_drugplan_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pdd_dosage', 'string', 255, array('type' => 'string', 'length' => 255));
	        //TODO-3624 Ancuta 23.11.2020
	        $this->hasColumn('pdd_dosage_full', 'string', 255, array('type' => 'string', 'length' => 255));
	        $this->hasColumn('pdd_dosage_concentration', 'string', 255, array('type' => 'string', 'length' => 255));
	        $this->hasColumn('pdd_dosage_concentration_full', 'string', 255, array('type' => 'string', 'length' => 255));
	        //--
	        $this->hasColumn('pdd_dosage_time_interval', 'integer',11, array('type' => 'integer', 'length' => 1));
	        $this->hasColumn('pdd_isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	        $this->hasColumn('pdd_create_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('pdd_create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
	        $this->hasColumn('pdd_change_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('pdd_change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
	        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	    }
	    
		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>