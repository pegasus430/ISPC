<?php

	abstract class BaseFormBlocksSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_blocks_settings');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//ISPC-2612 Ancuta 30.06.2020
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
			$this->hasColumn('block', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('option_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('option_category', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('available', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('form_item_class', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_item_id', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('coordinator_notification', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('valid_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('valid_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
			
			$this->hasOne('FormBlocksSettingsCategories', array(
					'local' => 'option_category',
					'foreign' => 'id'
			));
			
			
			//ISPC-2612 Ancuta 30.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>