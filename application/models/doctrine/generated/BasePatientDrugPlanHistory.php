<?php

	abstract class BasePatientDrugPlanHistory extends Doctrine_Record {

		function setTableDefinition()
		{//Changes for ISPC-1848 F
			$this->setTableName('patient_drugplan_history');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));


			$this->hasColumn('pd_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('pd_medication_master_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('pd_medication_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pd_medication', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pd_dosage', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			
			//ISPC-2110
			$this->hasColumn('pd_dosage_interval', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			//ispc-2291
			$this->hasColumn('pd_dosage_product', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'no',
			        1 => 'yes',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Dosage according to the product information; ispc-2291',
			));
				
			
			$this->hasColumn('pd_pattern1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_pattern2', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pd_pattern3', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pd_amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pd_verordnetvon', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('pd_dosage_unit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pd_dosage_method', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pd_active_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('pd_inactive_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('pd_comments', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('pd_isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			// ISPC-2162
			$this->hasColumn('pd_delete_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			
			$this->hasColumn('pd_isbedarfs', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-1823
			$this->hasColumn('pd_iscrisis', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));
			$this->hasColumn('pd_isivmed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_isschmerzpumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_cocktailid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pd_cocktail_comment', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('pd_cocktail_bolus', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('pd_cocktail_max_bolus', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('pd_cocktail_flussrate', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('pd_cocktail_sperrzeit', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('pd_treatment_care', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_isnutrition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-2176
			$this->hasColumn('pd_isintubated', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' =>0));
			//--

            //     ISPC-1712 New medi page changes 
			$this->hasColumn('pd_scheduled', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_has_interval', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_days_interval', 'integer', 3, array('type' => 'integer', 'length' => 3));
			
			//ispc-2291
			$this->hasColumn('pd_days_interval_technical', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'no',
			        1 => 'yes',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Interval according to technical information; ispc-2291',
			));
			
			$this->hasColumn('pd_administration_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('pd_edit_type', 'varchar', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('pd_medication_change', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			//ISPC-2524 pct.2)  Lore 15.01.2020
			$this->hasColumn('istransition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('pd_create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('pd_create_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('pd_change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('pd_change_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));

			//ISPC-2833 Ancuta 26.02.2021
			$this->hasColumn('pd_ispumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pd_pumpe_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//--
			

			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>