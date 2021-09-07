<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientHealthInsurance', 'IDAT');

	class PatientHealthInsurance extends BasePatientHealthInsurance {

		public $triggerformid = 8;
		public $triggerformname = "frmpatientHealthIns";

		protected $_encypted_columns = array(
		    'insurance_status',
		    'status_added',
		    'company_name',
		    'ins_insurance_provider',
		    'ins_first_name',
		    'ins_middle_name',
		    'ins_last_name',
		    'ins_contactperson',
		    'ins_zip',
		    'ins_city',
		    'ins_phone',
		    'ins_phone2',
		    'ins_phonefax',
		    'ins_post_office_box',
		    'ins_post_office_box_location',
		    'ins_email',
		    'ins_debtor_number',
		    'ins_zip_mailbox',
		    'ins_street',
		    'help1',
		    'help2',
		    'help3',
		    'help4',
		    'comment',
		    //ISPC-2666 Lore 16.09.2020
		    'ins_over_both_p',
		    'ins_over_mother',
		    'ins_over_father',
		    'self_insured',
		    'ins_over_others',
		    'ins_over_others_text',
		    //.
		);
		
		public function getPatientHealthInsurance($ipid)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$sql = "ipid, AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
			$sql.=",insurance_no as insurance_no";
			$sql.=",institutskennzeichen as institutskennzeichen";
			$sql.=",kvk_no as kvk_no, companyid";
			$sql.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
			$sql.=",exemption_till_date as exemption_till_date"; // ISPC - 2079
			$sql.=",privatepatient as privatepatient";
			$sql.=",direct_billing as direct_billing";
			$sql.=",bg_patient as bg_patient";
			$sql.=",private_valid_contribution as private_valid_contribution";
			$sql.=",private_contribution as private_contribution";
			$sql.=",AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') as status_added";
			$sql.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";
			$sql.=",AES_DECRYPT(ins_insurance_provider,'" . Zend_Registry::get('salt') . "') as ins_insurance_provider";
			$sql.=",AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') as ins_first_name";
			$sql.=",AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') as ins_middle_name";
			$sql.=",AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') as ins_last_name";
			$sql.=",AES_DECRYPT(ins_contactperson,'" . Zend_Registry::get('salt') . "') as ins_contactperson";
			$sql.=",AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip";
			$sql.=",AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') as ins_city";
			$sql.=",AES_DECRYPT(ins_phone,'" . Zend_Registry::get('salt') . "') as ins_phone";
			$sql.=",AES_DECRYPT(ins_phone2,'" . Zend_Registry::get('salt') . "') as ins_phone2";
			$sql.=",AES_DECRYPT(ins_phonefax,'" . Zend_Registry::get('salt') . "') as ins_phonefax";
			$sql.=",AES_DECRYPT(ins_post_office_box,'" . Zend_Registry::get('salt') . "') as ins_post_office_box";
			$sql.=",AES_DECRYPT(ins_post_office_box_location,'" . Zend_Registry::get('salt') . "') as ins_post_office_box_location";
			$sql.=",AES_DECRYPT(ins_email,'" . Zend_Registry::get('salt') . "') as ins_email";
			$sql.=",AES_DECRYPT(ins_debtor_number,'" . Zend_Registry::get('salt') . "') as ins_debtor_number";
			$sql.=",AES_DECRYPT(ins_zip_mailbox,'" . Zend_Registry::get('salt') . "') as ins_zip_mailbox";
			$sql.=",AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') as ins_street";
			$sql.=",AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') as help1";
			$sql.=",AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') as help2";
			$sql.=",AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') as help3";
			$sql.=",AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') as help4";
			$sql.=",AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment";
			$sql.=",AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') as ins_street";
			$sql.=",AES_DECRYPT(ins_over_both_p,'" . Zend_Registry::get('salt') . "') as ins_over_both_p";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_mother,'" . Zend_Registry::get('salt') . "') as ins_over_mother";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_father,'" . Zend_Registry::get('salt') . "') as ins_over_father";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(self_insured,'" . Zend_Registry::get('salt') . "') as self_insured";        //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_others,'" . Zend_Registry::get('salt') . "') as ins_over_others";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_others_text,'" . Zend_Registry::get('salt') . "') as ins_over_others_text";//ISPC-2666 Lore 16.09.2020
			
			$adminvisible = PatientMaster::getAdminVisibility($ipid);

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('PatientHealthInsurance')
				->where("ipid=?",  $ipid);
			$droparray = $drop->fetchArray();
			if($droparray)
			{
				if(count($droparray))
				{
					if($droparray[0]['cardentry_date'] != '0000-00-00 00:00:00')
					{
						$droparray[0]['cardentry_date'] = date('d.m.Y', strtotime($droparray[0]['cardentry_date']));
					}
					else
					{
						$droparray[0]['cardentry_date'] = "-";
					}
					
					if($droparray[0]['exemption_till_date'] != '0000-00-00')
					{
						$droparray[0]['exemption_till_date'] = date('d.m.Y', strtotime($droparray[0]['exemption_till_date']));
					}
					else
					{
						$droparray[0]['exemption_till_date'] = '';
					}

					return $droparray;
				}
			}
		}

		public function get_patients_healthinsurance($ipids)
		{
			if(empty($ipids) || !is_array($ipids)) {
				return false;
			}
			
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$hidemagic = Zend_Registry::get('hidemagic');

				$sql = "ipid, AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
				$sql.=",insurance_no as insurance_no";
				$sql.=",institutskennzeichen as institutskennzeichen";
				$sql.=",kvk_no as kvk_no, companyid";
				$sql.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
				$sql.=",exemption_till_date as exemption_till_date"; // ISPC - 2079
				$sql.=",privatepatient as privatepatient";
				$sql.=",direct_billing as direct_billing";
				$sql.=",bg_patient as bg_patient";
				$sql.=",AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') as status_added";
				$sql.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";
				$sql.=",AES_DECRYPT(ins_insurance_provider,'" . Zend_Registry::get('salt') . "') as ins_insurance_provider";
				$sql.=",AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') as ins_first_name";
				$sql.=",AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') as ins_middle_name";
				$sql.=",AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') as ins_last_name";
				$sql.=",AES_DECRYPT(ins_contactperson,'" . Zend_Registry::get('salt') . "') as ins_contactperson";
				$sql.=",AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip";
				$sql.=",AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') as ins_city";
				$sql.=",AES_DECRYPT(ins_phone,'" . Zend_Registry::get('salt') . "') as ins_phone";
				$sql.=",AES_DECRYPT(ins_phone2,'" . Zend_Registry::get('salt') . "') as ins_phone2";
				$sql.=",AES_DECRYPT(ins_phonefax,'" . Zend_Registry::get('salt') . "') as ins_phonefax";
				$sql.=",AES_DECRYPT(ins_post_office_box,'" . Zend_Registry::get('salt') . "') as ins_post_office_box";
				$sql.=",AES_DECRYPT(ins_post_office_box_location,'" . Zend_Registry::get('salt') . "') as ins_post_office_box_location";
				$sql.=",AES_DECRYPT(ins_email,'" . Zend_Registry::get('salt') . "') as ins_email";
				$sql.=",AES_DECRYPT(ins_debtor_number,'" . Zend_Registry::get('salt') . "') as ins_debtor_number";
				$sql.=",AES_DECRYPT(ins_zip_mailbox,'" . Zend_Registry::get('salt') . "') as ins_zip_mailbox";
				$sql.=",AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') as ins_street";
				$sql.=",AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') as help1";
				$sql.=",AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') as help2";
				$sql.=",AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') as help3";
				$sql.=",AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') as help4";
				$sql.=",AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment";
				$sql.=",AES_DECRYPT(ins_over_both_p,'" . Zend_Registry::get('salt') . "') as ins_over_both_p";  //ISPC-2666 Lore 16.09.2020
				$sql.=",AES_DECRYPT(ins_over_mother,'" . Zend_Registry::get('salt') . "') as ins_over_mother";  //ISPC-2666 Lore 16.09.2020
				$sql.=",AES_DECRYPT(ins_over_father,'" . Zend_Registry::get('salt') . "') as ins_over_father";  //ISPC-2666 Lore 16.09.2020
				$sql.=",AES_DECRYPT(self_insured,'" . Zend_Registry::get('salt') . "') as self_insured";        //ISPC-2666 Lore 16.09.2020
				$sql.=",AES_DECRYPT(ins_over_others,'" . Zend_Registry::get('salt') . "') as ins_over_others";  //ISPC-2666 Lore 16.09.2020
				$sql.=",AES_DECRYPT(ins_over_others_text,'" . Zend_Registry::get('salt') . "') as ins_over_others_text";//ISPC-2666 Lore 16.09.2020
				
			/*if(count($ipids) == 0)
			{
				$ipids[] = '9999999999';
			}*/
				$drop = Doctrine_Query::create()
					->select($sql)
					->from('PatientHealthInsurance')
					->whereIn('ipid', $ipids);
				
				$droparray = $drop->fetchArray();
				
				if(count($droparray))
				{
					return $droparray;
				}
				else
				{
					return false;
				}
			
		}

		public function getPatientHISdata($his)
		{
			$hidemagic = Zend_Registry::get('hidemagic');

			$sql = "ipid, AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
			$sql.=",insurance_no as insurance_no";
			$sql.=",institutskennzeichen as institutskennzeichen";
			$sql.=",kvk_no as kvk_no, companyid";
			$sql.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
			$sql.=",exemption_till_date as exemption_till_date"; // ISPC - 2079
			$sql.=",privatepatient as privatepatient";
			$sql.=",direct_billing as direct_billing";
			$sql.=",bg_patient as bg_patient";
			$sql.=",AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') as status_added";
			$sql.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";
			$sql.=",AES_DECRYPT(ins_insurance_provider,'" . Zend_Registry::get('salt') . "') as ins_insurance_provider";
			$sql.=",AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') as ins_first_name";
			$sql.=",AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') as ins_middle_name";
			$sql.=",AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') as ins_last_name";
			$sql.=",AES_DECRYPT(ins_contactperson,'" . Zend_Registry::get('salt') . "') as ins_contactperson";
			$sql.=",AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip";
			$sql.=",AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') as ins_city";
			$sql.=",AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') as ins_street";
			$sql.=",AES_DECRYPT(ins_debtor_number,'" . Zend_Registry::get('salt') . "') as ins_debtor_number";
			$sql.=",AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') as help1";
			$sql.=",AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') as help2";
			$sql.=",AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') as help3";
			$sql.=",AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') as help4";
			$sql.=",AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment";


			$drop = Doctrine_Query::create()
				->select($sql)
				->from('PatientHealthInsurance')
				->where("id=?",  $his);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_record($ipid, $target_ipid, $target_client)
		{
			//get patient health insurance
			$sql = "*, ipid, AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
			$sql.=",insurance_no as insurance_no";
			$sql.=",institutskennzeichen as institutskennzeichen";
			$sql.=",kvk_no as kvk_no, companyid as companyid";
			$sql.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
			$sql.=",exemption_till_date as exemption_till_date"; // ISPC - 2079
			$sql.=",privatepatient as privatepatient";
			$sql.=",direct_billing as direct_billing";
			$sql.=",bg_patient as bg_patient";
			$sql.=",AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') as status_added";
			$sql.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";
			$sql.=",AES_DECRYPT(ins_insurance_provider,'" . Zend_Registry::get('salt') . "') as ins_insurance_provider";
			$sql.=",AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') as ins_first_name";
			$sql.=",AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') as ins_middle_name";
			$sql.=",AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') as ins_last_name";
			$sql.=",AES_DECRYPT(ins_contactperson,'" . Zend_Registry::get('salt') . "') as ins_contactperson";
			$sql.=",AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip";
			$sql.=",AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') as ins_city";
			$sql.=",AES_DECRYPT(ins_phone,'" . Zend_Registry::get('salt') . "') as ins_phone";
			$sql.=",AES_DECRYPT(ins_phone2,'" . Zend_Registry::get('salt') . "') as ins_phone2";
			$sql.=",AES_DECRYPT(ins_phonefax,'" . Zend_Registry::get('salt') . "') as ins_phonefax";
			$sql.=",AES_DECRYPT(ins_post_office_box,'" . Zend_Registry::get('salt') . "') as ins_post_office_box";
			$sql.=",AES_DECRYPT(ins_post_office_box_location,'" . Zend_Registry::get('salt') . "') as ins_post_office_box_location";
			$sql.=",AES_DECRYPT(ins_email,'" . Zend_Registry::get('salt') . "') as ins_email";
			$sql.=",AES_DECRYPT(ins_zip_mailbox,'" . Zend_Registry::get('salt') . "') as ins_zip_mailbox";
			$sql.=",AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') as ins_street";
			$sql.=",AES_DECRYPT(ins_debtor_number,'" . Zend_Registry::get('salt') . "') as ins_debtor_number";
			$sql.=",AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') as help1";
			$sql.=",AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') as help2";
			$sql.=",AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') as help3";
			$sql.=",AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') as help4";
			$sql.=",AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment";
			$sql.=",AES_DECRYPT(ins_over_both_p,'" . Zend_Registry::get('salt') . "') as ins_over_both_p";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_mother,'" . Zend_Registry::get('salt') . "') as ins_over_mother";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_father,'" . Zend_Registry::get('salt') . "') as ins_over_father";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(self_insured,'" . Zend_Registry::get('salt') . "') as self_insured";        //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_others,'" . Zend_Registry::get('salt') . "') as ins_over_others";  //ISPC-2666 Lore 16.09.2020
			$sql.=",AES_DECRYPT(ins_over_others_text,'" . Zend_Registry::get('salt') . "') as ins_over_others_text";//ISPC-2666 Lore 16.09.2020
			

			$hi = Doctrine_Query::create()
				->select($sql)
				->from('PatientHealthInsurance')
				->where("ipid=?",  $ipid);
				

			$phi = $hi->fetchArray();

			if($phi)
			{
				if($phi[0]['companyid'] > 0)
				{
					//clone in master!
					$healthins = new HealthInsurance();
					$company_id = $healthins->clone_record($phi[0]['companyid'], $target_client);
				}
				else
				{
					$company_id = $phi[0]['companyid'];
				}

				$cust = new PatientHealthInsurance();
				//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
				$pc_listener = $cust->getListener()->get('IntenseConnectionListener');
				$pc_listener->setOption('disabled', true);
				//--
				$cust->ipid = $target_ipid;
				$cust->cardentry_date = $phi[0]['cardentry_date'];
				$cust->kvk_no = $phi[0]['kvk_no'];
				$cust->institutskennzeichen = $phi[0]['institutskennzeichen'];
				$cust->insurance_no = $phi[0]['insurance_no'];
				$cust->vk_no = $phi[0]['vk_no'];
				$cust->insurance_status = Pms_CommonData::aesEncrypt($phi[0]['insurance_status']);
				$cust->company_name = Pms_CommonData::aesEncrypt($phi[0]['company_name']);
				$cust->ins_insurance_provider = Pms_CommonData::aesEncrypt($phi[0]['ins_insurance_provider']);
				$cust->companyid = $company_id;
				$cust->status_added = Pms_CommonData::aesEncrypt($phi[0]['status_added']);
				$cust->ins_first_name = Pms_CommonData::aesEncrypt($phi[0]['ins_first_name']);
				$cust->ins_middle_name = Pms_CommonData::aesEncrypt($phi[0]['ins_middle_name']);
				$cust->ins_last_name = Pms_CommonData::aesEncrypt($phi[0]['ins_last_name']);
				$cust->ins_contactperson = Pms_CommonData::aesEncrypt($phi[0]['ins_contactperson']);
				$cust->comment = $phi[0]['comment'];
				$cust->date_of_birth = $phi[0]['date_of_birth'];
				$cust->rezeptgebuhrenbefreiung = $phi[0]['rezeptgebuhrenbefreiung'];
				$cust->exemption_till_date = $phi[0]['exemption_till_date'];
				$cust->privatepatient = $phi[0]['privatepatient'];
				$cust->direct_billing = $phi[0]['direct_billing'];
				$cust->bg_patient = $phi[0]['bg_patient'];
				$cust->ins_country = $phi[0]['ins_country'];
				$cust->ins_zip = Pms_CommonData::aesEncrypt($phi[0]['ins_zip']);
				$cust->ins_city = Pms_CommonData::aesEncrypt($phi[0]['ins_city']);

				$cust->ins_phone = Pms_CommonData::aesEncrypt($phi[0]['ins_phone']);
				$cust->ins_phone2 = Pms_CommonData::aesEncrypt($phi[0]['ins_phone2']);
				$cust->ins_phonefax = Pms_CommonData::aesEncrypt($phi[0]['ins_phonefax']);
				$cust->ins_post_office_box = Pms_CommonData::aesEncrypt($phi[0]['ins_post_office_box']);
				$cust->ins_post_office_box_location = Pms_CommonData::aesEncrypt($phi[0]['ins_post_office_box_location']);
				$cust->ins_email = Pms_CommonData::aesEncrypt($phi[0]['ins_email']);
				$cust->ins_zip_mailbox = Pms_CommonData::aesEncrypt($phi[0]['ins_zip_mailbox']);
				$cust->ins_street = Pms_CommonData::aesEncrypt($phi[0]['ins_street']);
				$cust->ins_debtor_number= Pms_CommonData::aesEncrypt($phi[0]['ins_debtor_number']);

				$cust->card_valid_till = $phi[0]['card_valid_till'];
				$cust->checksum = $phi[0]['checksum'];
				$cust->help1 = Pms_CommonData::aesEncrypt($phi[0]['help1']);
				$cust->help2 = Pms_CommonData::aesEncrypt($phi[0]['help2']);
				$cust->help3 = Pms_CommonData::aesEncrypt($phi[0]['help3']);
				$cust->help4 = Pms_CommonData::aesEncrypt($phi[0]['help4']);
				$cust->cost = $phi[0]['cost'];
				
				//ISPC-2666 Lore 16.09.2020
				$cust->ins_over_both_p = $phi[0]['ins_over_both_p'];
				$cust->ins_over_mother = $phi[0]['ins_over_mother'];
				$cust->ins_over_father = $phi[0]['ins_over_father'];
				$cust->self_insured = $phi[0]['self_insured'];
				$cust->ins_over_others = $phi[0]['ins_over_others'];
				$cust->ins_over_others_text = $phi[0]['ins_over_others_text'];
				//.
				
				$cust->save();
				//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
				$pc_listener->setOption('disabled', false);
				//--

				if($cust)
				{
					return $cust->id;
				}
			}
		}

		public function get_multiple_patient_healthinsurance($ipids, $master_data = false)
		{
			if(empty($ipids))
			{
				return false;
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');			
			
				$sql = "*,";
				$sql .= "CONVERT(AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') using latin1) as insurance_status,";
				$sql .= "CONVERT(AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') using latin1)  as status_added,";
				$sql .= "CONVERT(AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') using latin1)  as company_name,";
				$sql .= "CONVERT(AES_DECRYPT(ins_insurance_provider,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_insurance_provider,";
				$sql .= "CONVERT(AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_first_name,";
				$sql .= "CONVERT(AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_middle_name,";
				$sql .= "CONVERT(AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_last_name,";
				$sql .= "CONVERT(AES_DECRYPT(ins_contactperson,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_contactperson,";
				$sql .= "CONVERT(AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_zip,";
				$sql .= "CONVERT(AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') using latin1)  as ins_city,";
				$sql .= "CONVERT(AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') using latin1) as ins_street,";
				$sql .= "CONVERT(AES_DECRYPT(ins_phone,'" . Zend_Registry::get('salt') . "') using latin1) as ins_phone,";
				$sql .= "CONVERT(AES_DECRYPT(ins_phone2,'" . Zend_Registry::get('salt') . "') using latin1) as ins_phone2,";
				$sql .= "CONVERT(AES_DECRYPT(ins_phonefax,'" . Zend_Registry::get('salt') . "') using latin1) as ins_phonefax,";
				$sql .= "CONVERT(AES_DECRYPT(ins_post_office_box,'" . Zend_Registry::get('salt') . "') using latin1) as ins_post_office_box,";
				$sql .= "CONVERT(AES_DECRYPT(ins_post_office_box_location,'" . Zend_Registry::get('salt') . "') using latin1) as ins_post_office_box_location,";
				$sql .= "CONVERT(AES_DECRYPT(ins_email,'" . Zend_Registry::get('salt') . "') using latin1) as ins_email,";
				$sql .= "CONVERT(AES_DECRYPT(ins_debtor_number,'" . Zend_Registry::get('salt') . "') using latin1) as ins_debtor_number,";
				$sql .= "CONVERT(AES_DECRYPT(ins_zip_mailbox,'" . Zend_Registry::get('salt') . "') using latin1) as ins_zip_mailbox,";
				$sql .= "CONVERT(AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') using latin1)  as help1,";
				$sql .= "CONVERT(AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') using latin1)  as help2,";
				$sql .= "CONVERT(AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') using latin1)  as help3,";
				$sql .= "CONVERT(AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') using latin1)  as help4,";
				$sql .= "CONVERT(AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') using latin1)  as comment,";
				$sql .= "CONVERT(AES_DECRYPT(ins_over_both_p,'" . Zend_Registry::get('salt') . "') using latin1) as ins_over_both_p,";  //ISPC-2666 Lore 16.09.2020
				$sql .= "CONVERT(AES_DECRYPT(ins_over_mother,'" . Zend_Registry::get('salt') . "') using latin1) as ins_over_mother,";  //ISPC-2666 Lore 16.09.2020
				$sql .= "CONVERT(AES_DECRYPT(ins_over_father,'" . Zend_Registry::get('salt') . "') using latin1) as ins_over_father,";  //ISPC-2666 Lore 16.09.2020
				$sql .= "CONVERT(AES_DECRYPT(self_insured,'" . Zend_Registry::get('salt') . "') using latin1) as self_insured,";        //ISPC-2666 Lore 16.09.2020
				$sql .= "CONVERT(AES_DECRYPT(ins_over_others,'" . Zend_Registry::get('salt') . "') using latin1) as ins_over_others,";  //ISPC-2666 Lore 16.09.2020
				$sql .= "CONVERT(AES_DECRYPT(ins_over_others_text,'" . Zend_Registry::get('salt') . "') using latin1) as ins_over_others_text";//ISPC-2666 Lore 16.09.2020
				

				$drop_hi = Doctrine_Query::create()
					->select($sql)
					->from('PatientHealthInsurance')
					->whereIn("ipid", $ipids);
				$hi_array = $drop_hi->fetchArray();
			
				if($hi_array)
				{
					if($master_data)
					{
						foreach($hi_array as $khi => $v_hi_arr)
						{
							if(!empty($v_hi_arr['companyid']) && $v_hi_arr['companyid'] != 0)
							{
								$company_ids[] = $v_hi_arr['companyid'];
							}
						}

						if($company_ids)
						{
							$hi_q = Doctrine_Query::create()
								->select('*')
								->from('HealthInsurance')
								->whereIn('id', $company_ids);
							$hi_q_res = $hi_q->fetchArray();

							if($hi_q_res)
							{
								foreach($hi_q_res as $k_hiq => $v_hiq)
								{
									$companys[$v_hiq['id']] = $v_hiq;
								}
							}
						}
					}

					foreach($hi_array as $k_hi => $v_hi)
					{
						if($master_data)
						{
							$v_hi['company'] = $companys[$v_hi['companyid']];
						}

						if($v_hi['cardentry_date'] != '0000-00-00 00:00:00')
						{
							$v_hi['cardentry_date'] = date('d.m.Y', strtotime($v_hi['cardentry_date']));
						}
						else
						{
							$v_hi['cardentry_date'] = "-";
						}
						/* ISPC -2079 */
						if($v_hi['exemption_till_date'] != '0000-00-00')
						{
							$v_hi['exemption_till_date'] = date('d.m.Y', strtotime($v_hi['exemption_till_date']));
						}
						else
						{
							$v_hi['exemption_till_date'] = "";
						}
						/* ISPC -2079 */
						$health_insurances[$v_hi['ipid']] = $v_hi;
					}

					return $health_insurances;
				}
				else
				{
					return false;
				}
		}

		// $hi_data wether to return health insurance data or not
		public function check_priv_patient($ipid = false, $health_ins_data = false)
		{
			if($ipid)
			{
				$hi_data = self::get_multiple_patient_healthinsurance($ipid, false);

				if($hi_data[$ipid] && $hi_data[$ipid]['privatepatient'] == '1')
				{
					if($health_ins_data)
					{
						return $hi_data[$ipid];
					}
					else
					{
						return true;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_patients_healthinsurance_number($ipids)
		{
			if(empty($ipids))
			{
				return false;
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
		
			$sql = "id, ipid";
			//$sql .= ", AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
			$sql.=", insurance_no as insurance_no";
			/*if(count($ipids) == 0)
			{
				$ipids[] = '9999999999';
			}*/
			
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('PatientHealthInsurance')
				->whereIn('ipid', $ipids);
		
			$droparray = $drop->fetchArray();
			
			if(count($droparray))
			{
				foreach ($droparray as $k => $v)
				{
					$droparray[$v['ipid']] = $v;	
				}
				return $droparray;
			}
			else
			{
				return false;
			}
		}
		
		
		public function get_kvk_no($ipids = array()) {
		    
		    $result =  array();
		    
		    if (empty($ipids)) {
		        return $result;
		    }
		    
		    $ipids = is_array($ipids) ? $ipids : array($ipids);
		    
		    $q = $this->getTable()->createQuery()
		    ->select('id, ipid, kvk_no')
		    ->whereIn('ipid' , $ipids)
		    ->fetchArray();
		    
		    foreach ($q as $row) {
		        $result [$row['ipid']] = $row['kvk_no'];
		    }
		    
		    return $result;
		}
		
		
		/**
		 * @claudiu
		 * @param string $fieldName
		 * @param unknown $value
		 * @param array $data
		 * @param unknown $hydrationMode
		 * @return Doctrine_Record
		 * 
		 * @removed by @cla
		 */
		/*
		public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
		{
		    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
		
		        if ($fieldName != $this->getTable()->getIdentifier()) {
		            $entity = $this->getTable()->create(array( $fieldName => $value));
		        } else {
		            $entity = $this->getTable()->create();
		        }
		    }
		
    	    $this->_encryptData($data);
		
		    $entity->fromArray($data); //update
		
		    $entity->save(); //at least one field must be dirty in order to persist
		
		    return $entity;
		}
		*/
		
		/*
		private function _encryptData(&$data)
		{
		    if (empty($data) || ! is_array($data)) {
		        return;
		    }
		    $data_encrypted = Pms_CommonData::aesEncryptMultiple($data);
		    foreach($data_encrypted as $column=>$val) {
		        if (in_array($column, $this->_encypted_columns)) {
		            $data[$column] = $val;
		        }
		    }
		}
		*/
		public function reset_exemption_till_date()
		{
	
			$current_date = date('Y-m-d');
			$drop_hi = Doctrine_Query::create()
			->select('*')
			->from('PatientHealthInsurance')
			->where("rezeptgebuhrenbefreiung = '1'")
			->andWhere("exemption_till_date != ? and exemption_till_date < ?", array('0000-00-00', $current_date));

			$hi_array = $drop_hi->fetchArray();
			
			
			$hi_current_free_data = array();
			foreach($hi_array as $khi=>$vhi)
			{
			 	$hi_current_free_data[] = array(
			 		'ipid'=>$vhi['ipid'],
			 		'exemption_till_date'=>	$vhi['exemption_till_date'],
			 		'patient_hi_data'=>	serialize($vhi),
			 		'create_user'=>	"-1",
			 	);
			}
			
			if(!empty($hi_current_free_data)){
				$collection = new Doctrine_Collection('PatientHealthInsuranceHistory');
				$collection->fromArray($hi_current_free_data);
				$collection->save();
			}
			
			foreach($hi_array as $khi=>$vhi)
			{
				$phireset = Doctrine::getTable('PatientHealthInsurance')->findOneById($vhi['id']);
				$phireset->exemption_till_date = '0000-00-00';
				$phireset->rezeptgebuhrenbefreiung = '0';
				$phireset->save();
			}
		}

		/**
		 * @author Ancuta 
		 * ISPC-2452
		 * @param unknown $client
		 * @param unknown $company_id
		 * @return void|Ambigous <multitype:, Doctrine_Collection>|boolean
		 */
		
		public function get_client_hicompany_patients($client,$company_id){
		    
		    if(empty($company_id)){
		        return;
		    }

		    if(empty($client)){
                $logininfo = new Zend_Session_Namespace('Login_Info');
                $client = $logininfo->clientid;  
		    }
		     
		    //get all ipids of curent client 
		    $sql = "p.id,p.ipid,e.epid,e.clientid,h.id,h.ipid,h.companyid,h.change_date,h.change_user";
		    $sql.=",h.institutskennzeichen as institutskennzeichen";
		    $sql.=",AES_DECRYPT(h.company_name,'" . Zend_Registry::get('salt') . "') as company_name";
		    $sql.=",AES_DECRYPT(h.ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip";
		    $sql.=",AES_DECRYPT(h.ins_city,'" . Zend_Registry::get('salt') . "') as ins_city";
		    $sql.=",AES_DECRYPT(h.ins_debtor_number,'" . Zend_Registry::get('salt') . "') as ins_debtor_number";
		    
		    $patient = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientMaster p')
		    ->where("p.isdelete = 0");
		    $patient->leftJoin("p.EpidIpidMapping e");
		    $patient->leftJoin("p.PatientHealthInsurance h");
		    $patient->andWhere("e.clientid = ? ", $client);
		    $patient->andWhere("h.ipid IS NOT NULL");
		    $patient->andWhere("h.companyid= ? ", $company_id);
		    $droparray = $patient->fetchArray();
		    $patient2company = array();
		    
		    if(!empty($droparray)){
		        foreach($droparray as $k=>$pdata){
    		        $patient2company[$pdata['ipid']] = $pdata['PatientHealthInsurance'];  
    		        $patient2company[$pdata['ipid']]['epid'] = $pdata['EpidIpidMapping']['epid'];  
    		        $patient2company[$pdata['ipid']]['clientid'] = $pdata['EpidIpidMapping']['clientid'];  
		        }
		    }
		    
		    
// 		    $drop = Doctrine_Query::create()
// 		    ->select($sql)
// 		    ->from('PatientHealthInsurance')
// 		    ->where("companyid=?",  $company_id);
// 		    $droparray = $drop->fetchArray();
		    
		    
		    if(!empty($patient2company)){
		        return $patient2company;
		    } else{
		        return false;
		    }
		    
		} 
		
	}

?>
