<?php

	abstract class BasePatientDrugPlanExtraHistory extends Doctrine_Record {
	    
	    function setTableDefinition()
	    {
	        $this->setTableName('patient_drugplan_extra_history');
	        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
	        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
	        $this->hasColumn('history_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pde_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pde_drugplan_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pde_drug', 'string', 255, array('type' => 'string', 'length' => 255));
	        $this->hasColumn('pde_unit', 'integer', 11, array('type' => 'integer', 'length' => 11));
	        $this->hasColumn('pde_type', 'integer', 11, array('type' => 'integer', 'length' => 11));
	        $this->hasColumn('pde_indication', 'integer', 11, array('type' => 'integer', 'length' => 11));
	        $this->hasColumn('pde_importance', 'integer', 11, array('type' => 'integer', 'length' => 11));
	        $this->hasColumn('pde_dosage_form', 'integer', 11, array('type' => 'integer', 'length' => 11));
	        $this->hasColumn('pde_concentration', 'string', 255, array('type' => 'string', 'length' => 255));
	        $this->hasColumn('pde_isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	        // ISPC-2176
	        $this->hasColumn('pde_packaging', 'integer', 3, array('type' => 'integer', 'length' => 3));
	        $this->hasColumn('pde_kcal', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_volume', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        //-- 
	        
	        //ISPC-2684 Lore 05.10.2020
	        $this->hasColumn('pde_dosage_24h_manual', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
	        $this->hasColumn('pde_unit_dosage', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
	        $this->hasColumn('pde_unit_dosage_24h', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
	        //.
	        
	        // ISPC-2247 
	        $this->hasColumn('pde_escalation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('1', '2', '3')));
	        //--
	        
	        //ISPC-2833 Ancuta 26.02.2021
	        $this->hasColumn('pde_overall_dosage_h', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_overall_dosage_24h', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_overall_dosage_pump', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_drug_volume', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_unit2ml', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_concentration_per_drug', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        $this->hasColumn('pde_bolus_per_med', 'string', NULL, array('type' => 'string', 'length' => NULL));
	        //--
	        
	        $this->hasColumn('pde_create_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pde_create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
	        $this->hasColumn('pde_change_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
	        $this->hasColumn('pde_change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
	        
	        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	    }
 
		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>