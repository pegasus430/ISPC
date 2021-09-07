<?php

	abstract class BasePatientHealthInsurance extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_health_insurance');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cardentry_date', 'datetime');
			$this->hasColumn('company_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('companyid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('kvk_no', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('institutskennzeichen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('insurance_no', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vk_no', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('insurance_status', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rezeptgebuhrenbefreiung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('exemption_till_date', 'date'); // ISPC - 2079
			$this->hasColumn('privatepatient', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('direct_billing', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bg_patient', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('private_valid_contribution', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('private_contribution',  'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status_added', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_insurance_provider', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_middle_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_contactperson', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date_of_birth', 'date');
			$this->hasColumn('ins_country', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ins_zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_phone2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_phonefax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_post_office_box', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_post_office_box_location', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_zip_mailbox', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_debtor_number', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('card_valid_till', 'date');
			$this->hasColumn('checksum', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('help1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('help2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('help3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('help4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cost', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			//ISPC-2666 Lore 16.09.2020
			$this->hasColumn('ins_over_both_p', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_over_mother', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_over_father', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('self_insured', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_over_others', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_over_others_text', 'string', 255, array('type' => 'string', 'length' => 255));
			//.
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
			$this->actAs(new PatientUpdate());

			
			$this->hasOne('PatientMaster', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			$this->hasOne('HealthInsurance', array(
			    'local' => 'companyid',
			    'foreign' => 'id'
			)); //this is a different connection
			
			
			//ISPC-2614 Ancuta 19.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			    
			)), "IntenseConnectionListener");
			//
		}

	}

?>