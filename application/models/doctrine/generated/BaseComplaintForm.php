<?php
//TODO-2888 Carmen 12.02.2020 add complaint_number, preparation_already_applied, preparation_already_discontinued
	abstract class BaseComplaintForm extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_complaint_form');
			
			//formular_id
			$this->hasColumn('id', 'integer', NULL, 
					array('type' => 'integer', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			
			$this->hasColumn('ipid', 'string', 255, 
					array('type' => 'string', 'length' => 255));

			$this->hasColumn('status', 'enum', null, array(
					'type' => 'enum', 
					'notnull' => false, 
					'values' => array(
							'opened', 
							'closed')
			));
			
			
			$this->hasColumn('cp_name', 'string', 255, array(
					'type' => 'string',
					'length' => 255,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			
			$this->hasColumn('cp_phone', 'string', 255, array(
					'type' => 'string',
					'length' => 255,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('cp_fax', 'string', 255, array(
					'type' => 'string',
					'length' => 255,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			
			$this->hasColumn('zubmixid', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('complaint_number', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('drug', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('product', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('infusion_drugs', 'object', null, array(
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
			$this->hasColumn('infusion_other', 'object', null, array(
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
			
			$this->hasColumn('complaint_products', 'object', null, array(
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
			
			$this->hasColumn('reason', 'object', null, array(
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
			$this->hasColumn('chamber', 'object', null, array(
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
			
			$this->hasColumn('preparation_already_applied', 'object', null, array(
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
			
			$this->hasColumn('preparation_already_discontinued', 'object', null, array(
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

			//ISPC-2806 Dragos 28.01.2021
			$this->hasColumn('complaint_email_to', 'string', 4, array(
				'type' => 'string',
				'length' => 4,
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false,
			));
			// -- //
			
			$this->hasColumn('other_reason_text', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			
			
			$this->hasColumn('comment', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			
			

			
			$this->hasColumn('user_name', 'text', NULL, array(
					'type' => 'string',
					'length' => NULL,
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			
			
			$this->hasColumn('form_date', 'timestamp', null, array(
		             'type' => 'timestamp',
		             'fixed' => false,
		             'unsigned' => false,
		             'primary' => false,
		             'notnull' => false,
		             'autoincrement' => false,
             ));
			
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
