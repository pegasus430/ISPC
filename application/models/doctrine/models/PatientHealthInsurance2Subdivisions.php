<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientHealthInsurance2Subdivisions', 'IDAT');

	class PatientHealthInsurance2Subdivisions extends BasePatientHealthInsurance2Subdivisions {

	    
	    protected $_encypted_columns = array(
	        'ins2s_name',
	        'ins2s_insurance_provider',
	        'ins2s_contact_person',
	        'ins2s_street1',
	        'ins2s_street2',
	        'ins2s_zip',
	        'ins2s_city',
	        'ins2s_phone',
	        'ins2s_phone2',
	        'ins2s_post_office_box',
	        'ins2s_post_office_box_location',
	        'ins2s_zip_mailbox',
	        'ins2s_email',
	        'comments',
	        'ins2s_fax',
	        'ins2s_iknumber',
	        'ins2s_ikbilling',
	        'ins2s_debtor_number',
	        'ins2s_kvnumber',
	    );
	    
		public function get_hi_subdivisions($ipid, $hi_company_id)
		{
			$hi2s = Doctrine_Query::create()
				->select("*,AES_DECRYPT(ins2s_name,'" . Zend_Registry::get('salt') . "') as name,
					AES_DECRYPT(ins2s_insurance_provider,'" . Zend_Registry::get('salt') . "') as insurance_provider,
					AES_DECRYPT(ins2s_contact_person,'" . Zend_Registry::get('salt') . "') as contact_person,
					AES_DECRYPT(ins2s_street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(ins2s_street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(ins2s_zip,'" . Zend_Registry::get('salt') . "') as zip,
					AES_DECRYPT(ins2s_city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(ins2s_phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(ins2s_phone2,'" . Zend_Registry::get('salt') . "') as phone2,
					AES_DECRYPT(ins2s_post_office_box,'" . Zend_Registry::get('salt') . "') as post_office_box,
					AES_DECRYPT(ins2s_post_office_box_location,'" . Zend_Registry::get('salt') . "') as post_office_box_location,
					AES_DECRYPT(ins2s_zip_mailbox,'" . Zend_Registry::get('salt') . "') as zip_mailbox,
					AES_DECRYPT(ins2s_email,'" . Zend_Registry::get('salt') . "') as email,
					AES_DECRYPT(comments,'" . Zend_Registry::get('salt') . "') as comments,
					AES_DECRYPT(ins2s_fax,'" . Zend_Registry::get('salt') . "') as fax,
					AES_DECRYPT(ins2s_iknumber,'" . Zend_Registry::get('salt') . "') as iknumber,
					AES_DECRYPT(ins2s_ikbilling,'" . Zend_Registry::get('salt') . "') as ikbilling,
					AES_DECRYPT(ins2s_debtor_number,'" . Zend_Registry::get('salt') . "') as debtor_number,
					AES_DECRYPT(ins2s_kvnumber,'" . Zend_Registry::get('salt') . "') as kvnumber ")
				->from("PatientHealthInsurance2Subdivisions")
				->where("company_id = " . $hi_company_id . " ")
				->andWhere('ipid LIKE "' . $ipid . '"');
			$hi2s_arr = $hi2s->fetchArray();

			if($hi2s_arr)
			{
				foreach($hi2s_arr as $skey => $subdiv_details)
				{
					$subdivizion_details[$subdiv_details['subdiv_id']] = $subdiv_details;
				}

				return $subdivizion_details;
			}
		}

		public static function get_hi_subdivisions_multiple($hi_company_id , $ipids = array())
		{
			if(is_array($hi_company_id))
			{
				$hi_company_ids = $hi_company_id;
			}
			else
			{
				$hi_company_ids = array($hi_company_id);
			}

			$hi2s = Doctrine_Query::create()
				->select("*,AES_DECRYPT(ins2s_name,'" . Zend_Registry::get('salt') . "') as name,
					AES_DECRYPT(ins2s_insurance_provider,'" . Zend_Registry::get('salt') . "') as insurance_provider,
					AES_DECRYPT(ins2s_contact_person,'" . Zend_Registry::get('salt') . "') as contact_person,
					AES_DECRYPT(ins2s_street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(ins2s_street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(ins2s_zip,'" . Zend_Registry::get('salt') . "') as zip,
					AES_DECRYPT(ins2s_city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(ins2s_phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(ins2s_phone2,'" . Zend_Registry::get('salt') . "') as phone2,
					AES_DECRYPT(ins2s_post_office_box,'" . Zend_Registry::get('salt') . "') as post_office_box,
					AES_DECRYPT(ins2s_post_office_box_location,'" . Zend_Registry::get('salt') . "') as post_office_box_location,
					AES_DECRYPT(ins2s_zip_mailbox,'" . Zend_Registry::get('salt') . "') as zip_mailbox,
					AES_DECRYPT(ins2s_email,'" . Zend_Registry::get('salt') . "') as email,
					AES_DECRYPT(comments,'" . Zend_Registry::get('salt') . "') as comments,
					AES_DECRYPT(ins2s_fax,'" . Zend_Registry::get('salt') . "') as fax,
					AES_DECRYPT(ins2s_iknumber,'" . Zend_Registry::get('salt') . "') as iknumber,
					AES_DECRYPT(ins2s_ikbilling,'" . Zend_Registry::get('salt') . "') as ikbilling,
					AES_DECRYPT(ins2s_debtor_number,'" . Zend_Registry::get('salt') . "') as debtor_number,
					AES_DECRYPT(ins2s_kvnumber,'" . Zend_Registry::get('salt') . "') as kvnumber ")
				->from("PatientHealthInsurance2Subdivisions");
			$hi2s->andWhereIn('company_id', $hi_company_ids);
			
			
			if (is_array($ipids) && !empty($ipids)){
				
				$hi2s->andWhereIn('ipid', $ipids);
			}
			
			$hi2s_arr = $hi2s->fetchArray();

			$subdivizion_details = array();
			if($hi2s_arr)
			{
				foreach($hi2s_arr as $skey => $subdiv_details)
				{
					$subdivizion_details[$subdiv_details['ipid']][$subdiv_details['subdiv_id']] = $subdiv_details;
				}

				return $subdivizion_details;
			}
		}

		public function health_insurance_subdivision($ipid, $hi_company_id, $subdivision_alias)
		{
			if($subdivision_alias == "sgbv")
			{
				$subdivision_sql = " AND subdiv_id = 1 ";
			}
			elseif($subdivision_alias == "sgbxi")
			{
				$subdivision_sql = " AND subdiv_id = 2 ";
			}
			elseif($subdivision_alias == "sapv")
			{
				$subdivision_sql = " AND subdiv_id = 3 ";
			}
			elseif($subdivision_alias == "hilfsmittel")
			{
				$subdivision_sql = " AND subdiv_id = 4 ";
			}
			else
			{
				$subdivision_sql = "";
			}

			$hi2s = Doctrine_Query::create()
				->select("*,AES_DECRYPT(ins2s_name,'" . Zend_Registry::get('salt') . "') as name,
					AES_DECRYPT(ins2s_insurance_provider,'" . Zend_Registry::get('salt') . "') as insurance_provider,
					AES_DECRYPT(ins2s_contact_person,'" . Zend_Registry::get('salt') . "') as contact_person,
					AES_DECRYPT(ins2s_street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(ins2s_street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(ins2s_zip,'" . Zend_Registry::get('salt') . "') as zip,
					AES_DECRYPT(ins2s_city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(ins2s_phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(ins2s_phone2,'" . Zend_Registry::get('salt') . "') as phone2,
					AES_DECRYPT(ins2s_post_office_box,'" . Zend_Registry::get('salt') . "') as post_office_box,
					AES_DECRYPT(ins2s_post_office_box_location,'" . Zend_Registry::get('salt') . "') as post_office_box_location,
					AES_DECRYPT(ins2s_zip_mailbox,'" . Zend_Registry::get('salt') . "') as zip_mailbox,
					AES_DECRYPT(ins2s_email,'" . Zend_Registry::get('salt') . "') as email,
					AES_DECRYPT(comments,'" . Zend_Registry::get('salt') . "') as comments,
					AES_DECRYPT(ins2s_fax,'" . Zend_Registry::get('salt') . "') as fax,
					AES_DECRYPT(ins2s_debtor_number,'" . Zend_Registry::get('salt') . "') as debtor_number,
					AES_DECRYPT(ins2s_iknumber,'" . Zend_Registry::get('salt') . "') as iknumber,
					AES_DECRYPT(ins2s_ikbilling,'" . Zend_Registry::get('salt') . "') as ikbilling,
					AES_DECRYPT(ins2s_kvnumber,'" . Zend_Registry::get('salt') . "') as kvnumber ")
				->from("PatientHealthInsurance2Subdivisions")
				->where("company_id = " . $hi_company_id . "   " . $subdivision_sql . "   ")
				->andWhere('ipid LIKE "' . $ipid . '"');
			$hi2s_arr = $hi2s->fetchArray();

			if($hi2s_arr)
			{
				$subdivizion_details = $hi2s_arr[0];

				return $subdivizion_details;
			}
		}

	}

?>