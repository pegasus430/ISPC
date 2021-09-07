<?php

	abstract class BaseBedarfsmedication extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bedarfsmedication');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('bid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			//ISPC-2612 Ancuta 25.06.2020-28.06.2020
			$this->hasColumn('connection_id', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'id from connections_master',
			));
			$this->hasColumn('master_id', 'integer', 11, array(
			    'type' => 'integer',
			    'length' => 11,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'id from of master entry from parent client',
			));
			//--
			$this->hasColumn('medication_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('dosage', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('comments', 'text', NULL, array('type' => 'string', 'length' => 255));
			//ISPC - 2124
			$this->hasColumn('drug', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('unit', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('indication', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('dosage_form', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('concentration', 'string', 255, array('type' => 'string', 'length' => 255));
			//ISPC-2554 pct.3 Carmen 07.04.2020	
			$this->hasColumn('atc_code', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('atc_description', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('atc_groupe_code', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('atc_groupe_description', 'string', 255, array('type' => 'string', 'length' => 255));
			//--
			$this->hasColumn('verordnetvon', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('importance', 'string', 255, array('type' => 'string', 'length' => 255));
			//ISPC - 2124
			
			// ISPC-2612 Ancuta
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			// ISPC-2612 Ancuta
			$this->actAs(new Softdelete());
			
			
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
			
			
		}

	}

?>