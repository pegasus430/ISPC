<?php
//TODO-2888 Carmen 12.02.2020 add formular_complaint_number, formular_preparation_already_applied, formular_preparation_already_discontinued
	abstract class BaseComplaintFormHistory extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_complaint_form_history');
			
			$this->hasColumn('id', 'integer', NULL, 
					array('type' => 'integer', 
							'length' => NULL, 
							'primary' => true, 
							'autoincrement' => true));
			
			
			
			//formular_id
			$this->hasColumn('formular_id', 'integer', NULL,array(
					'type' => 'integer', 
					'length' => NULL, 
					'primary' => false, 
					'autoincrement' => false
			));
			
			$this->hasColumn('formular_ipid', 'string', 255,
					array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('formular_status', 'enum', null, array(
					'type' => 'enum',
					'notnull' => false,
					'values' => array(
							'opened',
					'closed')
			));
				
				
			$this->hasColumn('formular_cp_name', 'string', 255, array(
					'type' => 'string',
					'length' => 255,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_cp_phone', 'string', 255, array(
					'type' => 'string',
					'length' => 255,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_cp_fax', 'string', 255, array(
					'type' => 'string',
					'length' => 255,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_zubmixid', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_complaint_number', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_drug', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_product', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_infusion_drugs', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_infusion_other', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
							3 => '3',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_complaint_products', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
							2 => '3',
							3 => '4',
							4 => '5',
							5 => '6',
							6 => '7',
							7 => '8',
							8 => '9',
							9 => '10',
							10 => '11',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_reason', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
							2 => '3',
							3 => '4',
							4 => '5',
							5 => '6',
							6 => '7',
							7 => '8',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_chamber', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
							2 => '3',
							3 => '4',
							4 => '5',
							5 => '6',
							6 => '7',
							7 => '8',
							8 => '9',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
			$this->hasColumn('formular_preparation_already_applied', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_preparation_already_discontinued', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => '1',
							1 => '2',
					),
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_other_reason_text', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
				
				
			
				
			$this->hasColumn('formular_user_name', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
				
				
			$this->hasColumn('formular_form_date', 'timestamp', null, array(
					'type' => 'timestamp',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
				
			$this->hasColumn('formular_isdelete', 'integer', 1,
					array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn ( 'formular_create_user', 'integer', 11, array (
					'type' => 'integer',
					'length' => 11 
			) );
			
			$this->hasColumn ( 'formular_create_date', 'datetime', NULL, array (
					'type' => 'datetime',
					'length' => NULL 
			) );
			
			$this->hasColumn ( 'formular_change_user', 'integer', 11, array (
					'type' => 'integer',
					'length' => 11 
			) );
			
			$this->hasColumn ( 'formular_change_date', 'datetime', NULL, array (
					'type' => 'datetime',
					'length' => NULL 
			) );
			 
			
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
			
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());
		}

	}

?>