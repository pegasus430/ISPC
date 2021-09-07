<?php
/*
 * ISPC-2672 Lore 26.10.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientFamilySupportService', 'IDAT');


abstract class BasePatientFamilySupportService extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_family_support_service');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('amount_time', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('func_cnt_person', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phonefax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));		
			$this->hasColumn('zip_mailbox', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>