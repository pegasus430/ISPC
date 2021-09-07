<?php

	abstract class BasePatientDrugPlanDosageIntervals extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_drugplan_dosage_intervals');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medication_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('all','actual','isbedarfs','isivmed','isnutrition','isschmerzpumpe','treatment_care','iscrisis','isintubated')));
			$this->hasColumn('time_interval', 'time', NULL, array('type' => 'time', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>