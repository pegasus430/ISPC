<?php

	abstract class BasePatientDrugPlanAltCocktails extends Doctrine_Record {

		function setTableDefinition()
		{//Changes for ISPC-1848 F
			$this->setTableName('patient_drugplan_alt_cocktails');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('drugplan_cocktailid', 'bigint', 20, array('type' => 'bigint', 'length' => 2));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 1, array('type' => 'integer', 'length' => 1));
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
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('new', 'edit', 'delete', 'renew')));

			
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
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasOne('PatientDrugPlanAlt', array(
				'local' => 'id',
				'foreign' => 'cocktailid'
			));
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