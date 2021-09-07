<?php

	abstract class BaseDiagnosis extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('diagnosis');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
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
			$this->hasColumn('catalogue', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('main_group', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('detail_code', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icd_year', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('valid_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('icd_primary', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icd_star', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icd_cross', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rating', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('terminal', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gender', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lowest_age', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('highest_age', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('version', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('error1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('error2', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>