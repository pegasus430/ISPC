<?php
/*
 * ISPC-2672 Carmen 21.10.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientKindergarten', 'IDAT');


	abstract class BasePatientKindergarten extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_kindergarten');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('name_of_kindergarten', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('type_of_kindergarten', 'enum', 3, array(
					'type' => 'enum',
					'length' => 3,
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => 'kindergarten_regular',
							1 => 'kindergarten_integrative',
							2 => 'kindergarten_special_educational',
					),
					'primary' => false,
					'default' => null,
					'notnull' => true,
					'autoincrement' => false,
			));
			$this->hasColumn('contactperson', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('function', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phonefax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip_mailbox', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_visit', 'object', null, array(
        		'type' => 'object',
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        	));
			$this->hasColumn('picked_up_brought_home', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('accompaniment_required', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('accompaniment_required_in_kindergarten', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('aids_available_in_kindergarten', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>