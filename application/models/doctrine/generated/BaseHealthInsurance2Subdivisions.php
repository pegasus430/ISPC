<?php

	abstract class BaseHealthInsurance2Subdivisions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('health_insurance2subdivision');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('company_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('subdiv_id', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('insurance_provider', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('name2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('contact_person', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('city', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('phone', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('phone2', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('post_office_box ', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('post_office_box_location ', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('zip_mailbox', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('fax', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('email', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('kvnumber', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('iknumber', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('ikbilling', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('debtor_number', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('comments', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('valid_from', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('valid_till', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('patientonly', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('onlyclients', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('HealthInsurance', array(
				'local' => 'company_id',
				'foreign' => 'id'
			));

			$this->hasOne('HealthInsuranceSubdivisions', array(
				'local' => 'subdiv_id',
				'foreign' => 'id'
			));

			$this->actAs(new Timestamp());
		}

	}

?>