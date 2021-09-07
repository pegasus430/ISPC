<?php

	abstract class BasePatientDrugPlanCocktails extends Doctrine_Record {

		function setTableDefinition()
		{//Changes for ISPC-1848 F
			$this->setTableName('patient_drugplan_cocktails');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bolus', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('max_bolus', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('flussrate', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('flussrate_type', 'string', 255, array('type' => 'string', 'length' => 255));       //ISPC-2684 Lore 02.10.2020
			$this->hasColumn('sperrzeit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('carrier_solution', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('pumpe_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('', 'pump', 'pca')));
			$this->hasColumn('pumpe_medication_type', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('source_cocktailid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('source_ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasOne('PatientDrugPlan', array(
				'local' => 'id',
				'foreign' => 'cocktailid'
			));
		}

	}

?>