<?php
/*
 * ISPC-2672 Lore 23.10.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientSchool', 'IDAT');


abstract class BasePatientSchool extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_school');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('contactperson', 'string', 255, array('type' => 'string', 'length' => 255));
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
			$this->hasColumn('last_visit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_visit_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_visit_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('picked_up', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('picked_up_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('accom_required', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('accom_required_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('accom_pg_required', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('accom_pg_required_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('aids_available', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('aids_available_text', 'string', 255, array('type' => 'string', 'length' => 255));			
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>