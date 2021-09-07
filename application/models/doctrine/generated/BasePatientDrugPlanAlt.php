<?php

	abstract class BasePatientDrugPlanAlt extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_drugplan_alt');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			
			//ISPC-1483  Medical acknowledge function
			$this->hasColumn('drugplan_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			//---
			$this->hasColumn('medication_master_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('medication', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dosage', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			
			//ISPC-2110
			$this->hasColumn('dosage_interval', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			//ispc-2291
			$this->hasColumn('dosage_product', 'enum', 3, array(
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
			
			$this->hasColumn('pattern1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pattern2', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pattern3', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('verordnetvon', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('dosage_unit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dosage_method', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('active_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('inactive_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('comments', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('isbedarfs', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-1823
			$this->hasColumn('iscrisis', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));
			$this->hasColumn('isivmed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isschmerzpumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cocktailid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('treatment_care', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isnutrition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-2176
			$this->hasColumn('isintubated', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' =>0));
			//--
			$this->hasColumn('edit_type', 'varchar', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('medication_change', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			// ISPC-2162
			$this->hasColumn('delete_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			//ISPC-1483  Medical acknowledge function
			$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('new', 'edit', 'delete', 'renew')));
			
			//     ISPC-1712 New medi page changes
			$this->hasColumn('scheduled', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('has_interval', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('days_interval', 'integer', 3, array('type' => 'integer', 'length' => 3));
			
			//ispc-2291
			$this->hasColumn('days_interval_technical', 'enum', 3, array(
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
			
			$this->hasColumn('administration_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
				
			
			
			$this->hasColumn('inactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('approved', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('approval_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('approval_user', 'bigint');
			
			$this->hasColumn('declined', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('decline_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('decline_user', 'bigint');
			
			
			$this->hasColumn('change_source', 'enum', 7, array(
			    'type' => 'enum',
			    'length' => 7,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'online',
			        1 => 'offline',
			    ),
			    'primary' => false,
			    'default' => null,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comments' => 'insert was made from offline app, or other ',
			));
			//---
			
			
			//ISPC-2833 Ancuta 26.02.2021
			$this->hasColumn('ispumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pumpe_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//--
			
			//ISPC-2829 Ancuta 07.04.2021
			$this->hasColumn('related_drugplan_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('related_alt_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//--
 
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
// 			$this->actAs(new Trigger());
			
			$this->hasOne('PatientDrugPlanAltCocktails', array(
				'local' => 'cocktailid',
				'foreign' => 'id'
			));
			
			
			//ISPC-2833 Ancuta 26.02.2021
			$this->hasOne('PatientDrugPlanPumpe', array(
			    'local' => 'pumpe_id',
			    'foreign' => 'id'
			));
			//--
		}

		
		
		/**
		 * ALLWAYS set `change_source` = 'online', so we know this drug was added from www.ispc-login.de
		 * 
		 * (non-PHPdoc)
		 * @see Doctrine_Record::preInsert()
		 */
		public function preInsert(Doctrine_Event $event)
		{
		    parent::preInsert($event);
		    
		    $this->change_source = 'online';		    
		}
		
		
		/**
		 * IF empty then `change_source` = 'online', so we know this drug was edited from www.ispc-login.de
		 *
		 * (non-PHPdoc)
		 * @see Doctrine_Record::preInsert()
		 */
		public function preUpdate(Doctrine_Event $event)
		{
		    parent::preUpdate($event);
		    
		    if (empty($this->change_source)) {
		        
		        $this->change_source = 'online';
		        
		    }
		}
	}

?>