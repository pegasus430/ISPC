<?php

	abstract class BaseNutritionFormularList extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('nutrition_form_list');
			
			//formular_id
			$this->hasColumn('id', 'integer', NULL, 
					array('type' => 'integer', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			
			$this->hasColumn('clientid', 'integer', null, 
					array('type' => 'integer', 'length' => null));

			//ISPC-2612 Ancuta 25.06.2020-27.06.2020
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
			
			$this->hasColumn('isdelete', 'integer', 1,
					array('type' => 'integer', 'length' => 1, "default" => 0));
				
			$this->hasColumn('field_name', 'enum', null, 
					array('type' => 'enum', 'values' => array('application') , 'default' => 'application'));
			
			$this->hasColumn('field_value', 'string', null, 
					array('type' => 'string', 'length' => 255));
						
			
			$this->index('id', array(
            	'fields' => array('id'),
            	'primary' => true
       		));
			
			
			$this->index('clientid+isdelete', array(
					'fields' => array(
							'clientid',
							'isdelete'
					)
			));
			
			$this->index('field_name', array(
					'fields' => array('field_name')
			));
			
			
		}
		
		

		function setUp()
		{
// 			$this->hasOne('Client', array(
// 				'local' => 'clientid',
// 				'foreign' => 'id'
// 			));
			
			$this->actAs(new Timestamp());
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>