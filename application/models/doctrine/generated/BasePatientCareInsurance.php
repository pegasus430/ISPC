<?php
/*
 * ISPC-2667 Lore 21.09.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientCareInsurance', 'IDAT');


	abstract class BasePatientCareInsurance extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_care_insurance');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kind_ins_legally', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('kind_ins_private', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('kind_ins_no', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('kind_ins_others', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('kind_ins_others_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('power_ins_daily_care', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('power_ins_nursing_pension', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('power_ins_nursing_care', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('power_ins_others', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('power_ins_others_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('name_of_insurer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('contactperson', 'string', 255, array('type' => 'string', 'length' => 255));
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