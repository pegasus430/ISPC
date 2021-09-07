<?php

	abstract class BaseNutritionFormular extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_nutrition');
			
			//formular_id
			$this->hasColumn('id', 'integer', NULL, 
					array('type' => 'integer', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			
			$this->hasColumn('ipid', 'string', 255, 
					array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('formular_values', 'text', null, 
					array('type' => 'text', 'length' => null));
			
			$this->hasColumn('isdelete', 'integer', 1, 
					array('type' => 'integer', 'length' => 1));

			
			
			$this->index('id', array(
            	'fields' => array('id'),
            	'primary' => true
       		));
			
			
			$this->index('ipid+isdelete', array(
					'fields' => array(
							'ipid',
							'isdelete'
					)
			));
			
		}
		
		

		function setUp()
		{
// 			$this->hasOne('Client', array(
// 				'local' => 'clientid',
// 				'foreign' => 'id'
// 			));
			
			$this->actAs(new Timestamp());
		}

	}

?>