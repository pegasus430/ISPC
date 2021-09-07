<?php

	abstract class BasePatientDrugPlanDosageAlt extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_drugplan_dosage_alt');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('drugplan_id_alt', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('drugplan_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('dosage', 'string', 255, array('type' => 'string', 'length' => 255));
			//TODO-3624 Ancuta 23.11.2020
			$this->hasColumn('dosage_full', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dosage_concentration', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dosage_concentration_full', 'string', 255, array('type' => 'string', 'length' => 255));
			//--
			$this->hasColumn('dosage_time_interval', 'time', NULL, array('type' => 'time', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
 
			$this->hasOne('PatientDrugPlanAlt', array(
				'local' => 'drugplan_id_alt',
				'foreign' => 'id'
			));
		}

	}

?>