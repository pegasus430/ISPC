<?php

	abstract class BasePatientDrugPlanExtra extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_drugplan_extra');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('drugplan_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('drug', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('unit', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('indication', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('dosage_form', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('concentration', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('importance', 'string', 255, array('type' => 'string', 'length' => 255));
			// ISPC-2176
			$this->hasColumn('packaging', 'integer', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('kcal', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('volume', 'string', NULL, array('type' => 'string', 'length' => NULL));
			//--
			
			//ISPC-2684 Lore 05.10.2020
			$this->hasColumn('dosage_24h_manual', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('unit_dosage', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('unit_dosage_24h', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			//.
			
			// ISPC-2247 
			$this->hasColumn('escalation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('1', '2', '3')));
			//--
			
			//ISPC-2833 Ancuta 26.02.2021
			$this->hasColumn('overall_dosage_h', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('overall_dosage_24h', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('overall_dosage_pump', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('drug_volume', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('unit2ml', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('concentration_per_drug', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('bolus_per_med', 'string', NULL, array('type' => 'string', 'length' => NULL));
			//--
			
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasOne('PatientDrugPlan', array(
				'local' => 'drugplan_id',
				'foreign' => 'id'
			));
		}

	}

?>