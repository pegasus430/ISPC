<?php

	Doctrine_Manager::getInstance()->bindComponent('BriefTemplates', 'MDAT');

	class BriefTemplates extends BaseBriefTemplates {

		public function get_template($clientid, $template = false, $limit = false)
		{
			if($template)
			{
				$res = Doctrine_Query::create()
					->select('*')
					->from('BriefTemplates')
					->where('clientid = "' . $clientid . '"')
					->andWhere('id = "' . $template . '"')
					->andWhere('isdeleted = "0"');
				if($limit)
				{
					$res->limit($limit);
				}

				$res_arr = $res->fetchArray();

				if($res_arr)
				{
					return $res_arr;
				}
				else
				{
					return false;
				}
			}
		}

		public function get_patient_forms($clientid, $ipid, $search_string = false)
		{
//			done so farkvno_nurse_form, contact_form

			$excluded_form_ids = PatientCourse::get_deleted_visits($ipid);
			$patient_visits = array();
//1. Contact Forms
			$cf = new ContactForms();
			$search_fields = false;
			$fields = array();
			if($search_string)
			{
				$fields['date'] = $search_string;
				$search_fields['contact_form'] = array();
				$search_fields['contact_form'] = $fields;
			}

			$cforms = $cf->get_patient_contactforms($ipid, $excluded_form_ids['contact_form'], $search_fields['contact_form']);
			if(!empty($cforms))
			{
				$patient_visits = array_merge($patient_visits, $cforms);
			}



//2. Kvno Nurse
			$knurse = new KvnoNurse();
			$search_fields = false;
			$fields = array();
			if($search_string)
			{
				$fields['vizit_date'] = $search_string;
				$search_fields['kvno_nurse_form'] = array();
				$search_fields['kvno_nurse_form'] = $fields;
			}

			$kvno_nurse_form = $knurse->get_patient_nurse_visits($ipid, $excluded_form_ids['kvno_nurse_form'], $search_fields['kvno_nurse_form']);
			if(!empty($kvno_nurse_form))
			{
				$patient_visits = array_merge($patient_visits, $kvno_nurse_form);
			}


//3. Kvno Doctor
			$kdoctor = new KvnoDoctor();
			$search_fields = false;
			$fields = array();
			if($search_string)
			{
				$fields['vizit_date'] = $search_string;
				$search_fields['kvno_doctor_form'] = array();
				$search_fields['kvno_doctor_form'] = $fields;
			}

			$kvno_doctor_form = $kdoctor->get_patient_doctor_visits($ipid, $excluded_form_ids['kvno_doctor_form'], $search_fields['kvno_doctor_form']);
			if(!empty($kvno_doctor_form))
			{
				$patient_visits = array_merge($patient_visits, $kvno_doctor_form);
			}



//4. Bayern Doctor Visit
			$search_fields = false;
			$fields = array();
			if($search_string)
			{
				$fields['visit_date'] = $search_string;
				$search_fields['bayern_doctorvisit'] = array();
				$search_fields['bayern_doctorvisit'] = $fields;
			}

			$bayern_d = new BayernDoctorVisit();
			$bayern_doctor_visits = $bayern_d->get_patient_bayern_visits($ipid, $excluded_form_ids['bayern_doctorvisit'], $search_fields['bayern_doctorvisit']);

			if(!empty($bayern_doctor_visits))
			{
				$patient_visits = array_merge($patient_visits, $bayern_doctor_visits);
			}

//5. Visit Koordination
			$search_fields = false;
			$fields = array();
			if($search_string)
			{
				$fields['visit_date'] = $search_string;
				$search_fields['kvno_koord_visits'] = array();
				$search_fields['kvno_koord_visits'] = $fields;
			}

			$kvno_k = new VisitKoordination();
			$kvno_koord_visits = $kvno_k->get_patient_koordination_visits($ipid, $excluded_form_ids['visit_koordination_form'], $search_fields['kvno_koord_visits']);


			if(!empty($kvno_koord_visits))
			{
				$patient_visits = array_merge($patient_visits, $kvno_koord_visits);
			}

			return $patient_visits;
		}

		public function get_visit($visit_credentials = false)
		{
			if($visit_credentials)
			{
				switch(trim(rtrim($visit_credentials['type'])))
				{
					case "cf":
						$cf = new ContactForms();
						$form_data = $cf->get_patient_contact_form($visit_credentials['id'], $visit_credentials);
						break;

					case "knur":
						$k_nurse = new KvnoNurse();
						$form_data = $k_nurse->getNurseVisits($visit_credentials['id'], $visit_credentials);
						break;

					case "kdoc":
						$k_doc = new KvnoDoctor();
						$form_data = $k_doc->getDoctorVisits($visit_credentials['id'], $visit_credentials);
						break;

					case "bayern_doctorvisit":
						$k_doc = new BayernDoctorVisit();
						$form_data = $k_doc->get_doctor_visits_brief($visit_credentials['id'], $visit_credentials);
						break;

					case "vkf":
						$k_koord = new VisitKoordination();
						$form_data = $k_koord->get_koordination_visits($visit_credentials['id']);
						break;
				}

				if($form_data)
				{
					return $form_data;
				}
				else
				{
					return false;
				}
			}
		}

		//Docs template system functions
		public function get_patient_details($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$Tr = new Zend_View_Helper_Translate();
			$hidemagic = Zend_Registry::get('hidemagic');

			$sql = "*, e.epid as epid, id,AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(p.title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(p.salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			$sql .= ",AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') as street1";
			$sql .= ",AES_DECRYPT(p.street2,'" . Zend_Registry::get('salt') . "') as street2";
			$sql .= ",AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') as zip";
			$sql .= ",AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') as city";
			$sql .= ",AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') as phone";
			$sql .= ",AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') as mobile";
			$sql .= ",AES_DECRYPT(p.birth_name,'" . Zend_Registry::get('salt') . "') as birth_name";
			$sql .= ",AES_DECRYPT(p.birth_city,'" . Zend_Registry::get('salt') . "') as birth_city";
			$sql .= ",AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as sex";
			$sql .= ",AES_DECRYPT(p.email,'" . Zend_Registry::get('salt') . "') as email";           //ISPC-1236 Lore 13.05.2020
			
			$isadmin = 0;
			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*,e.epid as epid, id,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.birth_city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as birth_city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.birth_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as birth_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.email,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as email, ";       //ISPC-1236 Lore 13.05.2020
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster as p')
				->where('p.isdelete = 0')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid)
				->andWhere('p.ipid LIKE "' . $ipid . '"');
			$patient_res = $patient->fetchArray();

			if($patient_res)
			{
				foreach($patient_res as $k_res => $v_res)
				{
					$v_res_final['patienten_id'] = strtoupper($v_res['epid']);
					$v_res_final['aufnahmedatum'] = date('d.m.Y', strtotime($v_res['admission_date']));
					$v_res_final['nachname_patient'] = (strlen($v_res['last_name']) > '0' ? html_entity_decode($v_res['last_name'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['vorname_patient'] = (strlen($v_res['first_name']) > '0' ? html_entity_decode($v_res['first_name'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['geb_datum_patient'] = date('d.m.Y', strtotime($v_res['birthd']));
					$v_res_final['straße_patient'] = (strlen($v_res['street1']) > '0' ? html_entity_decode($v_res['street1'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['plz_patient'] = (strlen($v_res['zip']) > '0' ? html_entity_decode($v_res['zip'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['ort_patient'] = (strlen($v_res['city']) > '0' ? html_entity_decode($v_res['city'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['telefon_patient'] = (strlen($v_res['phone']) > '0' ? html_entity_decode($v_res['phone'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['mobiltelefon_patient'] = (strlen($v_res['mobile']) > '0' ? html_entity_decode($v_res['mobile'], ENT_QUOTES, 'utf-8') : '');

					//ISPC-1236 Lore 05.02.2020
					$v_res_final['geburtsort'] = (strlen($v_res['birth_city']) > '0' ? html_entity_decode($v_res['birth_city'], ENT_QUOTES, 'utf-8') : '');
					$v_res_final['geburtsnamen'] = (strlen($v_res['birth_name']) > '0' ? html_entity_decode($v_res['birth_name'], ENT_QUOTES, 'utf-8') : '');
					//.
					
					$v_res_final['patient_email'] = (strlen($v_res['email']) > '0' ? html_entity_decode($v_res['email'], ENT_QUOTES, 'utf-8') : '');       //ISPC-1236 Lore 13.05.2020
					
					
					if($v_res['sex'] == '1') //male
					{
						$v_res_final['patient_by_sex'] = $Tr->translate('patient_male');
						$v_res_final['patients_by_sex'] = $Tr->translate('patients_male');

						$v_res_final['herrfrau'] = $Tr->translate('herr_frau_male');
						$v_res_final['ihresihrer'] = $Tr->translate('ihres_ihrer_male');
						$v_res_final['ihrihre'] = $Tr->translate('ihr_ihre_male');

						$v_res_final['HerrFrau'] = $Tr->translate('herr_frau_male');
						$v_res_final['IhresIhrer'] = $Tr->translate('ihres_ihrer_male');
						$v_res_final['IhrIhre'] = $Tr->translate('ihr_ihre_male');

						$v_res_final['genannte'] = $Tr->translate('called_male');
						$v_res_final['desPatienten'] = $Tr->translate('thepatient_male');
						$v_res_final['despatienten'] = $Tr->translate('thepatient_male');
						$v_res_final['seineIhre'] = $Tr->translate('his_their_male');
						$v_res_final['seineihre'] = $Tr->translate('his_their_male');
						$v_res_final['unsere'] = $Tr->translate('our_male');
						$v_res_final['gemeinsame'] = $Tr->translate('common_male');
						$v_res_final['versicherte'] = $Tr->translate('insured_male');
					}
					else if($v_res['sex'] == '2') //female
					{
						$v_res_final['patient_by_sex'] = $Tr->translate('patient_female');
						$v_res_final['patients_by_sex'] = $Tr->translate('patients_female');

						$v_res_final['herrfrau'] = $Tr->translate('herr_frau_female');
						$v_res_final['ihresihrer'] = $Tr->translate('ihres_ihrer_female');
						$v_res_final['ihrihre'] = $Tr->translate('ihr_ihre_female');

						$v_res_final['HerrFrau'] = $Tr->translate('herr_frau_female');
						$v_res_final['IhresIhrer'] = $Tr->translate('ihres_ihrer_female');
						$v_res_final['IhrIhre'] = $Tr->translate('ihr_ihre_female');

						$v_res_final['genannte'] = $Tr->translate('called_female');
						$v_res_final['desPatienten'] = $Tr->translate('thepatient_female');
						$v_res_final['despatienten'] = $Tr->translate('thepatient_female');
						$v_res_final['seineIhre'] = $Tr->translate('his_their_female');
						$v_res_final['seineihre'] = $Tr->translate('his_their_female');
						$v_res_final['unsere'] = $Tr->translate('our_female');
						$v_res_final['gemeinsame'] = $Tr->translate('common_female');
						$v_res_final['versicherte'] = $Tr->translate('insured_female');
					}
					else //allow empty value so we can remove the token if patient has no sex value selected
					{
						$v_res_final['patient_by_sex'] = '';
						$v_res_final['patients_by_sex'] = '';

						$v_res_final['herrfrau'] = '';
						$v_res_final['ihresihrer'] = '';
						$v_res_final['ihrihre'] = '';

						$v_res_final['HerrFrau'] = '';
						$v_res_final['IhresIhrer'] = '';
						$v_res_final['IhrIhre'] = '';

						$v_res_final['genannte'] = '';
						$v_res_final['desPatienten'] = '';
						$v_res_final['despatienten'] = '';
						$v_res_final['seineIhre'] = '';
						$v_res_final['seineihre'] = '';
						$v_res_final['unsere'] = '';
						$v_res_final['gemeinsame'] = '';
						$v_res_final['versicherte'] = '';
					}

					$patient_details = $v_res_final;
				}
				
				return $patient_details;
			}
		}

		public function get_patient_familydoctor($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$patient = Doctrine_Query::create()
				->select('p.id, p.familydoc_id, p.isdelete')
				->from('PatientMaster as p')
				->where('p.isdelete = 0')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid)
				->andWhere('p.ipid LIKE "' . $ipid . '"');
			$patient_res = $patient->fetchArray();

			if($patient_res && $patient_res[0]['familydoc_id'] > '0')
			{
				$patient_fam_doc = FamilyDoctor::getFamilyDoc($patient_res[0]['familydoc_id']);

				if($patient_fam_doc)
				{
					foreach($patient_fam_doc as $k_fam_doc => $v_fam_doc)
					{
						$fam_doc_details['vorname_hausarzt'] = (strlen($v_fam_doc['first_name']) > '0' ? html_entity_decode($v_fam_doc['first_name'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['nachname_hausarzt'] = (strlen($v_fam_doc['last_name']) > '0' ? html_entity_decode($v_fam_doc['last_name'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['anrede_hausarzt'] = (strlen($v_fam_doc['salutation']) > '0' ? html_entity_decode($v_fam_doc['salutation'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['titel_hausarzt'] = (strlen($v_fam_doc['title']) > '0' ? html_entity_decode($v_fam_doc['title'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['straße_hausarzt'] = (strlen($v_fam_doc['street1']) > '0' ? html_entity_decode($v_fam_doc['street1'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['plz_hausarzt'] = (strlen($v_fam_doc['zip']) > '0' ? html_entity_decode($v_fam_doc['zip'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['ort_hausarzt'] = (strlen($v_fam_doc['city']) > '0' ? html_entity_decode($v_fam_doc['city'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['fax_nr_hausarzt'] = (strlen($v_fam_doc['fax']) > '0' ? html_entity_decode($v_fam_doc['fax'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['telefon_hausarzt'] = (strlen($v_fam_doc['phone_practice']) > '0' ? html_entity_decode($v_fam_doc['phone_practice'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['privtelefon_hausarzt'] = (strlen($v_fam_doc['phone_private']) > '0' ? html_entity_decode($v_fam_doc['phone_private'], ENT_QUOTES, 'utf-8') : '');
						$fam_doc_details['mobiltelefon_hausarzt'] = (strlen($v_fam_doc['phone_cell']) > '0' ? html_entity_decode($v_fam_doc['phone_cell'], ENT_QUOTES, 'utf-8') : '');
					}
				}
			}
			else
			{
				$fam_doc_details['vorname_hausarzt'] = '';
				$fam_doc_details['nachname_hausarzt'] = '';
				$fam_doc_details['anrede_hausarzt'] = '';
				$fam_doc_details['titel_hausarzt'] = '';
				$fam_doc_details['straße_hausarzt'] = '';
				$fam_doc_details['plz_hausarzt'] = '';
				$fam_doc_details['ort_hausarzt'] = '';
				$fam_doc_details['fax_nr_hausarzt'] = '';
				$fam_doc_details['telefon_hausarzt'] = '';
				$fam_doc_details['privtelefon_hausarzt'] = '';
				$fam_doc_details['mobiltelefon_hausarzt'] = '';
			}

			return $fam_doc_details;
		}

		public function get_patient_sapv_details($ipid)
		{
			$patient_sapv = SapvVerordnung::get_patient_last_sapv($ipid);
			$actual_sapv = SapvVerordnung::get_today_active_sapvs(array($ipid));
			
			if($patient_sapv)
			{
				foreach($patient_sapv as $k_sapv => $v_sapv)
				{
					$patient_sapv_details['verordnung_anfang'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
					$patient_sapv_details['verordnung_ende'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
				}
			}
			else
			{
				$patient_sapv_details['verordnung_anfang'] = '-';
				$patient_sapv_details['verordnung_ende'] = '-';
			}

			if($actual_sapv)
			{
				foreach($actual_sapv as $k_a_sapv => $v_a_sapv)
				{
					$patient_sapv_details['aktuelle_verordnung_ende'] = date('d.m.Y', strtotime($v_a_sapv['verordnungbis']));
				}
			}
			else
			{
				$patient_sapv_details['aktuelle_verordnung_ende'] = "-";
			}


			return $patient_sapv_details;
		}

		public function get_patient_hi_details($ipid)
		{
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);

			if($healthinsu_array)
			{
				$patient_hi_res['krankenkasse'] = (strlen($healthinsu_array[0]['company_name']) > '0' ? html_entity_decode($healthinsu_array[0]['company_name'], ENT_QUOTES, 'utf-8') : '');
				$patient_hi_res['straße_krankenkasse'] = (strlen($healthinsu_array[0]['ins_street']) > '0' ? html_entity_decode($healthinsu_array[0]['ins_street'], ENT_QUOTES, 'utf-8') : '');
				$patient_hi_res['plz_krankenkasse'] = (strlen($healthinsu_array[0]['ins_zip']) > '0' ? html_entity_decode($healthinsu_array[0]['ins_zip'], ENT_QUOTES, 'utf-8') : '');
				$patient_hi_res['ort_krankenkasse'] = (strlen($healthinsu_array[0]['ins_city']) > '0' ? html_entity_decode($healthinsu_array[0]['ins_city'], ENT_QUOTES, 'utf-8') : '');
				$patient_hi_res['versicherungsnummer_patient'] = (strlen($healthinsu_array[0]['insurance_no']) > '0' ? html_entity_decode($healthinsu_array[0]['insurance_no'], ENT_QUOTES, 'utf-8') : '');
				$patient_hi_res['fax_nr_krankenkasse'] = (strlen($healthinsu_array[0]['ins_phonefax']) > '0' ? html_entity_decode($healthinsu_array[0]['ins_phonefax'], ENT_QUOTES, 'utf-8') : '');

				//ISPC-1236 Lore 05.02.2020
				$patient_hi_res['kassennummer'] = (strlen($healthinsu_array[0]['kvk_no']) > '0' ? html_entity_decode($healthinsu_array[0]['kvk_no'], ENT_QUOTES, 'utf-8') : '');
				//.
				
				//ISPC-1236 Lore 18.05.2020
				$patient_hi_res['institutskennzeichen'] = (strlen($healthinsu_array[0]['institutskennzeichen']) > '0' ? html_entity_decode($healthinsu_array[0]['institutskennzeichen'], ENT_QUOTES, 'utf-8') : '');
				//.
				
				if(!empty($healthinsu_array[0]['companyid']) && $healthinsu_array[0]['companyid'] != 0)
				{
					$helathins = Doctrine::getTable('HealthInsurance')->find($healthinsu_array[0]['companyid']);
					$healtharray = $helathins->toArray();

					if(strlen($patient_hi_res['straße_krankenkasse']) == 0)
					{
						$patient_hi_res['straße_krankenkasse'] = (strlen($healtharray['street1']) > '0' ? html_entity_decode($healtharray['street1'], ENT_QUOTES, 'utf-8') : '');
					}

					if(strlen($patient_hi_res['plz_krankenkasse']) == 0)
					{
						$patient_hi_res['plz_krankenkasse'] = (strlen($healtharray['zip']) > '0' ? html_entity_decode($healtharray['zip'], ENT_QUOTES, 'utf-8') : '');
					}

					if(strlen($patient_hi_res['ort_krankenkasse']) == 0)
					{
						$patient_hi_res['ort_krankenkasse'] = (strlen($healtharray['city']) > '0' ? html_entity_decode($healtharray['city'], ENT_QUOTES, 'utf-8') : '');
					}

					if(strlen($patient_hi_res['fax_nr_krankenkasse']) == '0')
					{
						$patient_hi_res['fax_nr_krankenkasse'] = (strlen($healtharray['phonefax']) > '0' ? html_entity_decode($healtharray['phonefax'], ENT_QUOTES, 'utf-8') : '');
					}
				}
			}
			else
			{
				$patient_hi_res['krankenkasse'] = '';
				$patient_hi_res['straße_krankenkasse'] = '';
				$patient_hi_res['plz_krankenkasse'] = '';
				$patient_hi_res['ort_krankenkasse'] = '';
				$patient_hi_res['versicherungsnummer_patient'] = '';
				$patient_hi_res['fax_nr_krankenkasse'] = '';
				$patient_hi_res['kassennummer'] = '';           //ISPC-1236 Lore 05.02.2020
				$patient_hi_res['institutskennzeichen'] = '';   //ISPC-1236 Lore 18.05.2020
			}

			return $patient_hi_res;
		}

		public function get_client_details($clientid)
		{
			$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax,
					AES_DECRYPT(institutskennzeichen,'" . Zend_Registry::get('salt') . "') as institutskennzeichen,
					AES_DECRYPT(betriebsstattennummer,'" . Zend_Registry::get('salt') . "') as betriebsstattennummer,
					AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment,
					AES_DECRYPT(dgp_user,'" . Zend_Registry::get('salt') . "') as dgp_user,
					AES_DECRYPT(lbg_sapv_provider,'" . Zend_Registry::get('salt') . "') as lbg_sapv_provider,
					AES_DECRYPT(lbg_street,'" . Zend_Registry::get('salt') . "') as lbg_street,
					AES_DECRYPT(lbg_postcode,'" . Zend_Registry::get('salt') . "') as lbg_postcode,
					AES_DECRYPT(lbg_city,'" . Zend_Registry::get('salt') . "') as lbg_city,
					AES_DECRYPT(lbg_institutskennzeichen,'" . Zend_Registry::get('salt') . "') as lbg_institutskennzeichen
				")
				->from('Client')
				->where('id = ' . $clientid);
			$client_res = $client->fetchArray();

			if($client_res)
			{
				foreach($client_res as $k_cli => $v_cli)
				{

					//$client_res_details['name_mandant'] = (strlen($v_cli['client_name']) > '0' ? html_entity_decode($v_cli['client_name'], ENT_QUOTES, 'utf-8') : '');
					$client_res_details['name_mandant'] = (strlen($v_cli['team_name']) > '0' ? html_entity_decode($v_cli['team_name'], ENT_QUOTES, 'utf-8') : '');
					$client_res_details['telefonnummer_mandant'] = (strlen($v_cli['phone']) > '0' ? html_entity_decode($v_cli['phone'], ENT_QUOTES, 'utf-8') : '');
					$client_res_details['straße_mandant'] = (strlen($v_cli['street1']) > '0' ? html_entity_decode($v_cli['street1'], ENT_QUOTES, 'utf-8') : '');
					$client_res_details['plz_mandant'] = (strlen($v_cli['postcode']) > '0' ? html_entity_decode($v_cli['postcode'], ENT_QUOTES, 'utf-8') : '');
					$client_res_details['ort_mandant'] = (strlen($v_cli['city']) > '0' ? html_entity_decode($v_cli['city'], ENT_QUOTES, 'utf-8') : '');
				}

				return $client_res_details;
			}
		}

		public function get_patient_diagnosis($client = false, $ipid = false)
		{
			if($client && $ipid)
			{
				$diagnosis_data = PatientDiagnosis::get_multiple_patients_diagnosis($ipid);
				$dg = new DiagnosisType();
				$dtsarr = $dg->get_client_diagnosistypes($client);

				if(!empty($diagnosis_data))
				{
					foreach($diagnosis_data as $k_diag => $v_diag)
					{
						if(strlen(trim(rtrim($v_diag['icdnumber']))) > '0' || strlen(trim(rtrim($v_diag['diagnosis']))) > '0')
						{
							$pat_diag['all'][] = $v_diag['icdnumber'] . ' ' . trim(rtrim($v_diag['diagnosis']));
							$pat_diag_rest[$v_diag['diagnosis_type_id']][] = $v_diag['icdnumber'] . ' ' . trim(rtrim($v_diag['diagnosis']));
						}
					}

					//all
					$patient_diagnosis['diagnose'] = html_entity_decode(implode(', ', $pat_diag['all']), ENT_QUOTES, 'utf-8');

					//TODO-3625 Ancuta 20.11.2020
					$patient_diagnosis['hauptdiagnose'] ="";
					$patient_diagnosis['nebendiagnose'] ="";
					//--
					
					if(!empty($dtsarr))
					{
						//each type with its name
						foreach($pat_diag_rest as $k_diag_rest => $v_diag_rest)
						{
							if(!empty($v_diag_rest) && strlen($dtsarr[$k_diag_rest]['description']) > '0')
							{
								$patient_diagnosis[strtolower($dtsarr[$k_diag_rest]['description'])] = html_entity_decode(implode(', ', $v_diag_rest), ENT_QUOTES, 'utf-8');
							}
							//in case the diagnosis type has no description use the abreviation and create a token
							elseif(!empty($v_diag_rest) && strlen($dtsarr[$k_diag_rest]['abbrevation']) > '0')
							{
								$patient_diagnosis[strtolower($dtsarr[$k_diag_rest]['abbrevation'])] = html_entity_decode(implode(', ', $v_diag_rest), ENT_QUOTES, 'utf-8');
							}

							//TODO-3625 Ancuta 20.11.2020
							if(!empty($v_diag_rest) && strlen($dtsarr[$k_diag_rest]['abbrevation']) > '0')
							{
							    // If this was not previously filled, fill NOW 
							    if($dtsarr[$k_diag_rest]['abbrevation'] == 'HD' && empty($patient_diagnosis['hauptdiagnose'])){
								    $patient_diagnosis['hauptdiagnose'] = html_entity_decode(implode(', ', $v_diag_rest), ENT_QUOTES, 'utf-8');
								}
								if($dtsarr[$k_diag_rest]['abbrevation'] == 'ND' && empty($patient_diagnosis['nebendiagnose'])){
								    $patient_diagnosis['nebendiagnose'] = html_entity_decode(implode(', ', $v_diag_rest), ENT_QUOTES, 'utf-8');
								}
							}
							//--
						}

						foreach($dtsarr as $k_dtsarr => $v_dtsarr)
						{
							if(!array_key_exists(strtolower($v_dtsarr['description']), $patient_diagnosis) && strlen($v_dtsarr['description']) > '0')
							{
								$patient_diagnosis[strtolower($v_dtsarr['description'])] = '';
							}
							elseif(!array_key_exists(strtolower($v_dtsarr['abbrevation']), $patient_diagnosis) && strlen($v_dtsarr['abbrevation']) > '0')
							{
								$patient_diagnosis[strtolower($v_dtsarr['abbrevation'])] = '';
							}
						}
					}
				}
				else
				{
					$patient_diagnosis['diagnose'] = '';
					if(!empty($dtsarr))
					{
						foreach($dtsarr as $k_diagtype => $v_diagtype)
						{
							if(strlen($v_diagtype['description']) > '0')
							{
								$patient_diagnosis[strtolower($v_diagtype['description'])] = '';
							}
							elseif(strlen($v_diagtype['abbrevation']) > '0')
							{
								$patient_diagnosis[strtolower($v_diagtype['abbrevation'])] = '';
							}
						}
					}
				}
			}

			return $patient_diagnosis;
		}
		
		
		public function get_patient_diagnosis_table($client = false, $ipid = false)
		{
			if($client && $ipid)
			{
			    $cell_style = 'style="border: 1px solid; padding: 0; margin-top: 5px; margin-bottom: 5px;margin-left: 2px;margin-right: 2px;"';
			    
				$diagnosis_data = PatientDiagnosis::get_multiple_patients_diagnosis($ipid);
				$dg = new DiagnosisType();
				$dtsarr = $dg->get_client_diagnosistypes($client);
				
				if(!empty($diagnosis_data))
				{
					foreach($diagnosis_data as $k_diag => $v_diag)
					{
					    if($dtsarr[$v_diag['diagnosis_type_id']]['abbrevation'] == "HD"){
					        $d_token = "Hauptdiagnose_Tabelle";
					    }
					    elseif($dtsarr[$v_diag['diagnosis_type_id']]['abbrevation'] == "ND"){
					        $d_token = "Nebendiagnose_Tabelle";
					    }
					    
						if(strlen(trim(rtrim($v_diag['icdnumber']))) > '0' || strlen(trim(rtrim($v_diag['diagnosis']))) > '0')
						{
						    $diagnosis_details[$dtsarr[$v_diag['diagnosis_type_id']]['abbrevation']][$k_diag]['icdnumber'] = $v_diag['icdnumber']; 
						    $diagnosis_details[$dtsarr[$v_diag['diagnosis_type_id']]['abbrevation']][$k_diag]['description'] = $v_diag['diagnosis'];
						    
						    $all_diagnosis_details[$k_diag]['icdnumber'] = $v_diag['icdnumber']; 
						    $all_diagnosis_details[$k_diag]['description'] = $v_diag['diagnosis'];
						    
						        $diagno_html[$d_token] .= '<tr>
									<td colspan="3" ' . $cell_style . '>' .  $diagnosis_details[$dtsarr[$v_diag['diagnosis_type_id']]['abbrevation']][$k_diag]['icdnumber']   . '</td>
									<td colspan="27" ' . $cell_style . '>' . $diagnosis_details[$dtsarr[$v_diag['diagnosis_type_id']]['abbrevation']][$k_diag]['description'] . '</td>
								</tr>';
						        
						        $diagno_html['Diagnose_Tabelle'] .= '<tr>
									<td colspan="3" ' . $cell_style . '>' . $v_diag['icdnumber']   . '</td>
									<td colspan="27" ' . $cell_style . '>' . $v_diag['diagnosis'] . '</td>
								</tr>';
						}
					}
					
					$patient_diagnosis["Diagnose_Tabelle"] = $all_diagnosis_details;
					$patient_diagnosis["Hauptdiagnose_Tabelle"] = $diagnosis_details['HD'];
					$patient_diagnosis["Nebendiagnose_Tabelle"] = $diagnosis_details['ND'];
				}
			}
			
			foreach($patient_diagnosis as $d_token=>$diagno_details){
			    if(!empty($patient_diagnosis[$d_token]))
			    {
		             $score_html_final[$d_token] = '
					  <table style="font-family: Arial; width: 100%; font-size: 8pt; border-collapse:collapse; table-layout:fixed;" cellpadding="0" cellspacing="0">
					  <thead>';
		    				    $score_html_final[$d_token] .= '</tr>';
		    				    $score_html_final[$d_token] .='<tr>
								<th colspan="3" ' . $cell_style . '>ICD</th>
								<th colspan="27" ' . $cell_style . '>Beschreibung</th>
			 				</tr>
							</thead>
							<tbody>
							' . $diagno_html[$d_token]  . '
							</tbody>
						</table>';
			         $pat_diagno_html[$d_token] = $score_html_final[$d_token];
			    } else {
			        $pat_diagno_html[$d_token] = "";
			    }
			}

			return $pat_diagno_html;
		}

		public function get_patient_symptomatology($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$clientsymsets = SymptomatologyPermissions::getClientSymptomatology($clientid);
			$clientsymsets = array_values($clientsymsets);

			$setid = $clientsymsets[0]['setid'];
			$patient_sym_arr = Symptomatology::getPatientSymptpomatologyBySet($ipid, $setid);

			$set_details = SymptomatologyValues::getSymptpomatologyValues($setid, true, false);


			foreach($patient_sym_arr as $k_sym => $v_sym)
			{
				if(strlen(trim(rtrim($v_sym['input_value']))))
				{
					$symptomatology_data[] = $set_details[$v_sym['symptomid']]['sym_description'] . ': ' . $v_sym['input_value'];
				}
			}

			if($symptomatology_data)
			{
				$final_sym_data['symptomatik'] = implode('; ', $symptomatology_data);
			}
			else
			{
				$final_sym_data['symptomatik'] = '';
			}

			if($_REQUEST['dbgxv'])
			{
				print_r($set_details);
				print_r($final_sym_data);
				exit;
			}

			return $final_sym_data;
		}

		public function get_user_details($uid)
		{
			$user_details_res = User::getUsersDetails($uid);

			if($user_details_res)
			{
				foreach($user_details_res as $k_user => $v_user)
				{
					$user_details['user_vorname'] = $v_user['first_name'];
					$user_details['user_nachname'] = $v_user['last_name'];
				}
			}
			else
			{
				$user_details['user_vorname'] = '';
				$user_details['user_nachname'] = '';
			}

			return $user_details;
		}

		public function get_patient_medications($ipid)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $modules = new Modules();
		     
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
		    {
		        $acknowledge_func = '1';
		         
		        // Get declined data
		        $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid);
		        if(empty($declined)){
		            $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		         
		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid);
		        foreach($non_approved['change'] as $drugplan_id =>$not_approved){
		            $not_approved_ids[] = $not_approved['drugplan_id'];
		             
		            if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                $newly_not_approved[] = $not_approved['drugplan_id'];;
		            }
		             
		        }
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		         
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }		    
		    
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere("isdelete = '0'");
            if($acknowledge_func == "1")//Medication acknowledge
			{
			    $drugs->andWhereNotIn('id',$declined); // remove declined
			    $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
			}
			$drugs->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();

			foreach($drugsarray as $key => $drugp)
			{
				$master_meds[] = $drugp['medication_master_id'];
			}

			if(empty($master_meds))
			{
				$master_meds['999999999'] = 'XXXXX';
			}

			$medic = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->whereIn("id", $master_meds);
			$master_medication = $medic->fetchArray();

			foreach($master_medication as $k_medi => $v_medi)
			{
				$medications[$v_medi['id']] = $v_medi['name'];
			}

			foreach($drugsarray as $key => $drugp)
			{
				$medication = '';
				$medication = trim(rtrim($medications[$drugp['medication_master_id']]));
				if(strlen(trim(rtrim($drugp['dosage']))) > '0')
				{
					$medication .= ' | ' . trim(rtrim($drugp['dosage']));
				}

				$patient_medication[] = $medication;
				
 
				if($drugp['isbedarfs'] == "0" && $drugp['isivmed'] == "0" && $drugp['isschmerzpumpe'] == "0" && $drugp['treatment_care'] == "0" && $drugp['isnutrition'] == "0"){

				    $medication_actual = '';
				    $medication_actual = trim(rtrim($medications[$drugp['medication_master_id']]));
				    if(strlen(trim(rtrim($drugp['dosage']))) > '0')
				    {
				        $medication_actual .= ', ' . trim(rtrim($drugp['dosage']));
				    }
				    if(strlen(trim(rtrim($drugp['comments']))) > '0')
				    {
				        $medication_actual .= ', ' . trim(rtrim($drugp['comments']));
				    }
				    
				    $patient_medication_special['M'][] = $medication_actual;
				}
				
				
				if($drugp['isbedarfs'] == "1"){

				    $medication_bedarf = '';
				    $medication_bedarf = trim(rtrim($medications[$drugp['medication_master_id']]));
				    if(strlen(trim(rtrim($drugp['dosage']))) > '0')
				    {
				        $medication_bedarf .= ', ' . trim(rtrim($drugp['dosage']));
				    }
				    if(strlen(trim(rtrim($drugp['comments']))) > '0')
				    {
				        $medication_bedarf .= ', ' . trim(rtrim($drugp['comments']));
				    }
				    
				    $patient_medication_special['N'][] = $medication_bedarf;
				}
				//ispc 1823 ??? 
				
				if($drugp['iscrisis'] == "1"){
				
					$medication_crisis = '';
					$medication_crisis = trim(rtrim($medications[$drugp['medication_master_id']]));
					if(strlen(trim(rtrim($drugp['dosage']))) > '0')
					{
						$medication_crisis .= ', ' . trim(rtrim($drugp['dosage']));
					}
					if(strlen(trim(rtrim($drugp['comments']))) > '0')
					{
						$medication_crisis .= ', ' . trim(rtrim($drugp['comments']));
					}
				
					$patient_medication_special['KM'][] = $medication_crisis;
				}
				
				
				
				
			}

			if(count($patient_medication) > '0')
			{
				$pat_medications['aktuelle_medikation'] = html_entity_decode(implode('; ', $patient_medication), ENT_QUOTES, 'utf-8');
			}
			else
			{
				$pat_medications['aktuelle_medikation'] = '';
			}
			
			
			if(count($patient_medication_special['M']) > '0')
			{

    				$pat_medications['allgemeine_Medikation'] = html_entity_decode(implode("\n", $patient_medication_special['M']), ENT_QUOTES, 'utf-8');
			}
			else
			{
				$pat_medications['allgemeine_Medikation'] = '';
			}
			
			
			if(count($patient_medication_special['N']) > '0')
			{

    				$pat_medications['Bedarfs_Medikation'] = html_entity_decode(implode("\n", $patient_medication_special['N']), ENT_QUOTES, 'utf-8');
			}
			else
			{
				$pat_medications['Bedarfs_Medikation'] = '';
			}
			
			return $pat_medications;
		}

		public function get_patient_medications_table($ipid)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $modules = new Modules();
		    	
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
		    {
		        $acknowledge_func = '1';
		        	
		        // Get declined data
		        $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid);
		        if(empty($declined)){
		            $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		        	
		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid);
		        foreach($non_approved['change'] as $drugplan_id =>$not_approved){
		            $not_approved_ids[] = $not_approved['drugplan_id'];
		             
		            if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                $newly_not_approved[] = $not_approved['drugplan_id'];;
		            }
		             
		        }
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		         
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }
		    
		    //ISPC-1236 Lore 13.04.2021
		    // get patient extra details
		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$logininfo->clientid);
		    
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere("isdelete = '0'");
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    $drugs->andWhereNotIn('id',$declined); // remove declined
				    $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
				}
			$drugs->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();

			foreach($drugsarray as $key => $drugp)
			{
				$master_meds[] = $drugp['medication_master_id'];
			}

			if(empty($master_meds))
			{
				$master_meds['999999999'] = 'XXXXX';
			}

			$medic = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->whereIn("id", $master_meds);
			$master_medication = $medic->fetchArray();

			foreach($master_medication as $k_medi => $v_medi)
			{
				$medications[$v_medi['id']] = $v_medi['name'];
			}

			// get user details
			$user_m = new User();
			$all_users_array = $user_m->getUserByClientid($logininfo->clientid);
			$user_array[] = "";
			foreach($all_users_array as $user)
			{
			    $user_array[$user['id']] = trim($user['last_name']) . " " . trim($user['first_name']);
			}

			// get cocktail details
			$c_ids[] = '9999999999';
			foreach($drugsarray as $key => $drugp)
			{
			    if($drugp['cocktailid'] > 0)
			    {
			        $c_ids[] = $drugp['cocktailid'];
			    }
			}
			$c_ids = array_values(array_unique($c_ids));
			
			$cocktails = new PatientDrugPlanCocktails();
			$cocktails_details = $cocktails->getDrugCocktails($c_ids);
			
			foreach($drugsarray as $key => $drugp)
			{
			    if($drugp['isbedarfs'] == "1" && $drugp['iscrisis'] == "0" && $drugp['isivmed'] == "0" && $drugp['isschmerzpumpe'] == "0" && $drugp['treatment_care'] == "0" && $drugp['isnutrition'] == "0"){
                    $type = "bedarf";
			    }
			    elseif($drugp['isbedarfs'] == "0" && $drugp['iscrisis'] == "1" && $drugp['isivmed'] == "0" && $drugp['isschmerzpumpe'] == "0" && $drugp['treatment_care'] == "0" && $drugp['isnutrition'] == "0"){
			    	$type = "crisis";	 
			    }
			    elseif($drugp['isbedarfs'] == "0" && $drugp['iscrisis'] == "0" && $drugp['isivmed'] == "1" && $drugp['isschmerzpumpe'] == "0" && $drugp['treatment_care'] == "0" && $drugp['isnutrition'] == "0"){
                    $type = "iv";
			        
			    }
			    elseif($drugp['isbedarfs'] == "0" && $drugp['iscrisis'] == "0" && $drugp['isivmed'] == "0" && $drugp['isschmerzpumpe'] == "1" && $drugp['treatment_care'] == "0" && $drugp['isnutrition'] == "0"){
                    $type = "pumpe";
			    }
			    elseif($drugp['isbedarfs'] == "0" && $drugp['iscrisis'] == "0" && $drugp['isivmed'] == "0" && $drugp['isschmerzpumpe'] == "0" && $drugp['treatment_care'] == "1" && $drugp['isnutrition'] == "0"){
                    $type = "bp";
			    }
			    elseif($drugp['isbedarfs'] == "0" && $drugp['iscrisis'] == "0" && $drugp['isivmed'] == "0" && $drugp['isschmerzpumpe'] == "0" && $drugp['treatment_care'] == "0" && $drugp['isnutrition'] == "1"){
                    $type = "Ernährung";
			    } else{
			        $type = "medikation";
			    }
			    
			    if($type=="pumpe"){
			        
    			    if($drugp['medication_change'] != "0000-00-00 00:00:00")
    			    {
    			        $medication_details[$type][$drugp['cocktailid']][$key]['date'] = date('d.m.Y', strtotime($drugp['medication_change']));
    			    }
    			    else if($drugp['medication_change'] == "0000-00-00 00:00:00" && $drugp['change_date'] != "0000-00-00 00:00:00")
    			    {
    			        $medication_details[$type][$drugp['cocktailid']][$key]['date'] = date('d.m.Y', strtotime($drugp['change_date']));
    			    }
    			    else
    			    {
    			        $medication_details[$type][$drugp['cocktailid']][$key]['date'] = date('d.m.Y', strtotime($drugp['create_date']));
    			    }
    			    
    			    $medication_details[$type][$drugp['cocktailid']][$key]['medication'] = trim(rtrim($medications[$drugp['medication_master_id']]));
    			    $medication_details[$type][$drugp['cocktailid']][$key]['dosage'] = trim(rtrim($drugp['dosage']));
                    
    			    //ISPC-1236 Lore 13.04.2021
    			    $dosage_value = "";
    			    $dosage_value = str_replace(",",".",$drugp['dosage']);
    			    $medication_details[$type][$drugp['cocktailid']][$key]['dosage_24h'] = $dosage_value * 24 ;
    			    $medication_details[$type][$drugp['cocktailid']][$key]['unit'] = $medication_extra[$drugp['id']]['unit'];
    			    $modules = new Modules();
    			    if($modules->checkModulePrivileges("240", $logininfo->clientid)){
    			        $medication_details[$type][$drugp['cocktailid']][$key]['unit'] = "ml";
    			        $medication_details[$type][$drugp['cocktailid']][$key]['unit_module'] = $medication_extra[$drugp['id']]['unit'];
    			        $medication_details[$type][$drugp['cocktailid']][$key]['unit_dosage']     =  $medication_extra[$drugp['id']]['unit_dosage'] ;
    			        $medication_details[$type][$drugp['cocktailid']][$key]['unit_dosage_24h'] =  $medication_extra[$drugp['id']]['unit_dosage_24h'];
    			    }
    			    //.
    			    
    			    $medication_details[$type][$drugp['cocktailid']][$key]['comments'] = trim(rtrim($drugp['comments']));
    			    $medication_details[$type][$drugp['cocktailid']][$key]['verordnetvon'] = $user_array[$drugp['verordnetvon']];
    			    
    			    $medication_details[$type][$drugp['cocktailid']]['cocktail_details'] = $cocktails_details[$drugp['cocktailid']];
			    
			    }
			    else
			    {
    			    if($drugp['medication_change'] != "0000-00-00 00:00:00")
    			    {
    			        $medication_details[$type][$key]['date'] = date('d.m.Y', strtotime($drugp['medication_change']));
    			    }
    			    else if($drugp['medication_change'] == "0000-00-00 00:00:00" && $drugp['change_date'] != "0000-00-00 00:00:00")
    			    {
    			        $medication_details[$type][$key]['date'] = date('d.m.Y', strtotime($drugp['change_date']));
    			    }
    			    else
    			    {
    			        $medication_details[$type][$key]['date'] = date('d.m.Y', strtotime($drugp['create_date']));
    			    }
    			    
    			    $medication_details[$type][$key]['medication'] = trim(rtrim($medications[$drugp['medication_master_id']]));
    			    $medication_details[$type][$key]['dosage'] = trim(rtrim($drugp['dosage']));
    			    $medication_details[$type][$key]['comments'] = trim(rtrim($drugp['comments']));
    			    $medication_details[$type][$key]['verordnetvon'] = $user_array[$drugp['verordnetvon']];
			    }
			}
// 			print_r($medication_details); exit;
			$types_array = array("medikation","bedarf","iv","pumpe","bp","Ernährung");

			$cell_style = 'style="border: 1px solid; padding: 0; margin-top: 5px; margin-bottom: 5px;margin-left: 2px;margin-right: 2px;"';
			
			foreach($types_array as $m_type){
    			if(count($medication_details[$m_type]) > '0')
    			{
    			    if($m_type != "pumpe")
    			    {
                        foreach($medication_details[$m_type] as $k=>$med){
            				$med_html[$m_type] .= '<tr>
									<td colspan="3" ' . $cell_style . '>' . $med ['date']   . '</td>
									<td colspan="12" ' . $cell_style . '>' . $med ['medication'] . '</td>
									<td colspan="5" ' . $cell_style . '>' . $med ['dosage'] . '</td>
									<td colspan="4" ' . $cell_style . '>' . $med ['comments'] . '</td>
									<td colspan="6" ' . $cell_style . '>' . $med ['verordnetvon'] . '</td>
								</tr>';
                        }
    			    }
    			    else
    			    {
    			        
    			        foreach($medication_details[$m_type] as $pumpe_id => $pumpe_med)
    			        {
        			        foreach($pumpe_med as $k=>$med)
        			        {
        			            //ISPC-1236 Lore 13.04.2021      --- don't know why ...if $k == '0' does not enter in the loop with $k! = "cocktail_details"
        			            if($k == '0'){
        			                $med_html[$m_type][$pumpe_id] .= '<tr>
        									<td colspan="3" ' . $cell_style . '>' . $med ['date']   . '</td>
        									<td colspan="12" ' . $cell_style . '>' . $med ['medication'] . '</td>
        									<td colspan="5" ' . $cell_style . '>' . $med ['dosage'] . $med ['unit']     .'<br/>'. $med ['unit_dosage'] . $med ['unit_module'] . '</td>
        									<td colspan="4" ' . $cell_style . '>' . $med ['dosage_24h'] . $med ['unit'] .'<br/>'. $med ['unit_dosage_24h'] . $med ['unit_module'] . '</td>
        									<td colspan="6" ' . $cell_style . '>' . $med ['verordnetvon'] . '</td>
        								</tr>';
        			                
        			            }
        			            
                                if($k != "cocktail_details"){

/*                     				$med_html[$m_type][$pumpe_id] .= '<tr>
        									<td colspan="3" ' . $cell_style . '>' . $med ['date']   . '</td>
        									<td colspan="12" ' . $cell_style . '>' . $med ['medication'] . '</td>
        									<td colspan="9" ' . $cell_style . '>' . $med ['dosage'] . '</td>
        									<td colspan="6" ' . $cell_style . '>' . $med ['verordnetvon'] . '</td>
        								</tr>'; */
                                    
                    				//ISPC-1236 Lore 13.04.2021
                    				$med_html[$m_type][$pumpe_id] .= '<tr>
        									<td colspan="3" ' . $cell_style . '>' . $med ['date']   . '</td>
        									<td colspan="12" ' . $cell_style . '>' . $med ['medication'] . '</td>
        									<td colspan="5" ' . $cell_style . '>' . $med ['dosage'] . $med ['unit']     .'<br/>'. $med ['unit_dosage'] . $med ['unit_module'] . '</td>
        									<td colspan="4" ' . $cell_style . '>' . $med ['dosage_24h'] . $med ['unit'] .'<br/>'. $med ['unit_dosage_24h'] . $med ['unit_module'] . '</td>
        									<td colspan="6" ' . $cell_style . '>' . $med ['verordnetvon'] . '</td>
        								</tr>';
                                }
                            }
                            
            				$med_html[$m_type][$pumpe_id] .= '<tr>
									<td colspan="30" ' . $cell_style . '>Kommentar: ' . $pumpe_med['cocktail_details'] ['description']   . '</td>
								</tr>';
            				
            				$med_html[$m_type][$pumpe_id] .= '<tr>
									<td colspan="30" ' . $cell_style . '>Applikationsweg: ' . $pumpe_med['cocktail_details'] ['pumpe_medication_type']   . '</td>
								</tr>';
            				
            				$med_html[$m_type][$pumpe_id] .= '<tr>
									<td colspan="30" ' . $cell_style . '>Flussrate: ' . $pumpe_med['cocktail_details'] ['flussrate']   . '</td>
								</tr>';
            				
            				$med_html[$m_type][$pumpe_id] .= '<tr>
									<td colspan="30" ' . $cell_style . '>Trägerlösung: ' . $pumpe_med['cocktail_details'] ['carrier_solution']   . '</td>
								</tr>';
            				
            				$med_html[$m_type][$pumpe_id] .= '<tr>
									<td colspan="30" ' . $cell_style . '>Bolus: ' . $pumpe_med['cocktail_details'] ['bolus']   . '</td>
								</tr>';
            				
            				
            				$med_html[$m_type][$pumpe_id] .= '<tr>
									<td colspan="30" ' . $cell_style . '>Sperrzeit: ' . $pumpe_med['cocktail_details'] ['sperrzeit']   . '</td>
								</tr>';
                        }
    			    }
    			}
    			else
    			{
    				$pat_medications[$m_type.'_als_Tabelle'] = "";
    			}
			}
		
			foreach($types_array as $m_type){
			    if(count($medication_details[$m_type]) > '0')
			    {
			        if($m_type != "pumpe"){
    				    $score_html_final[$m_type] = '
						  <table style="font-family: Arial; width: 100%; font-size: 8pt; border-collapse:collapse; table-layout:fixed;" cellpadding="0" cellspacing="0">
						  <thead>';
                        $score_html_final[$m_type] .= '</tr>';
                        $score_html_final[$m_type] .='<tr>
									<th colspan="3" ' . $cell_style . '>Datum</th>
									<th colspan="12" ' . $cell_style . '>Medikation</th>
									<th colspan="5" ' . $cell_style . '>Dosierung</th>
									<th colspan="4" ' . $cell_style . '>Kommentar</th>
									<th colspan="6" ' . $cell_style . '>Verordnet von</th>
				 				</tr>
								</thead>
								<tbody>
								' . $med_html[$m_type]  . '
								</tbody>
							</table>';
    			         $pat_medications[$m_type.'_als_Tabelle'] = $score_html_final[$m_type];
    			         
			        } else {
			            foreach($medication_details[$m_type] as $pump_id => $pump_details ){
        				    $score_html_final[$m_type][$pump_id] = '
    						  <table style="font-family: Arial; width: 100%; min-width:100%; font-size: 8pt; border-collapse:collapse; table-layout:fixed;" cellpadding="0" cellspacing="0">
    						  <thead>';
                            $score_html_final[$m_type][$pump_id] .= '</tr>';
                            $score_html_final[$m_type][$pump_id] .='<tr>
    									<th colspan="3" ' . $cell_style . '>Datum</th>
    									<th colspan="12" ' . $cell_style . '>Medikation</th>
    									<th colspan="5" ' . $cell_style . '>Dosierung/h</th>
    									<th colspan="4" ' . $cell_style . '>Dosierung/24h</th>
    									<th colspan="6" ' . $cell_style . '>Verordnet von</th>
    				 				</tr>
    								</thead>
    								<tbody>
    								' . $med_html[$m_type][$pump_id]  . '
    								</tbody>
    							</table>';
        			     $pat_medications[$m_type.'_als_Tabelle'] .= $score_html_final[$m_type][$pump_id];
			                
			            }
			        }
			    }else{
			        $pat_medications[$m_type.'_als_Tabelle'] = "";
			    }
			    
			    
			}
			
			return $pat_medications;
		}

		public function get_patient_discharge($clientid, $ipid)
		{
		    if( empty($clientid) || empty($ipid )){
		        return ;
		    }
		    
			$todarray = Doctrine_Query::create()
				->select("*")
				->from('DischargeMethod')
				->where("isdelete = 0 ")
				->andwhere("clientid= ?", $clientid)
			    ->fetchArray();

			$discharge_method = array();
			$death_methods = array('tod','tod','tod','verstorben','verstorben','verstorben');
			
			foreach($todarray as $todmethod)
			{
			    if(in_array(strtolower($todmethod['abbr']),$death_methods)){
    				$tod_ids[] = $todmethod['id'];
			    }
			    $discharge_method[$todmethod['id']] = $todmethod; 
			    
			}
			
			if( ! empty($tod_ids)){
    			$dischargedArr = Doctrine_Query::create()
    				->select("*")
    				->from("PatientDischarge")
    				->where("ipid= ?",$ipid)
    				->andWhereIn("discharge_method", $tod_ids)
    				->andWhere('isdelete = "0"')
    				->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
			}
			if($dischargedArr)
			{
				$patient_discharge_vars['entlassungsdatum'] = date('d.m.Y', strtotime($dischargedArr['discharge_date']));
				$patient_discharge_vars['todeszeitpunkt'] = date('d.m.Y', strtotime($dischargedArr['discharge_date']));     //ISPC-1236 Lore 05.02.2020
			}
			else
			{
				$patient_discharge_vars['entlassungsdatum'] = '';
				$patient_discharge_vars['todeszeitpunkt'] = ''; //ISPC-1236 Lore 05.02.2020
			}

			
			$discharged_arr = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->where("ipid = ?", $ipid)
				->andWhere('isdelete = "0"')
				->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

			if($discharged_arr)
			{
				$patient_discharge_vars['datum_behandlungsende'] = date('d.m.Y', strtotime($discharged_arr['discharge_date']));
				$patient_discharge_vars['entlassungsgrund'] = $discharge_method[$discharged_arr['discharge_method']]['description']; // @Ancuta 24.07.2018
			}
			else
			{
				$patient_discharge_vars['datum_behandlungsende'] = '';
				$patient_discharge_vars['entlassungsgrund'] = '';
			}

			if($patient_discharge_vars)
			{
				return $patient_discharge_vars;
			}
		}

		public function get_patient_contact_person($ipid)
		{

		}

		public function get_valid_last_location($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete="0"')
				->andWhere('discharge_location = "0"')
				->andWhere("valid_till='0000-00-00 00:00:00' OR DATE(valid_till) <= DATE(NOW())")
				->orderBy('valid_from,id ASC');
			$patlocs = $patloc->fetchArray();

			$last_location['last_location'] = '';
			if($patlocs)
			{ //there are no active today location
				foreach($patlocs as $kpatloc => $vpatloc)
				{
					$pat_loc_ids[] = $vpatloc['location_id'];
					$pat_ipid2patlocid[$vpatloc['ipid']][] = $vpatloc['location_id'];
					$last_location_id = $vpatloc['id'];
				}
				$pat_loc_ids = array_values(array_unique($pat_loc_ids));

				if ($pat_loc_ids){
				    
				    $drop = Doctrine_Query::create()
				    ->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
				    ->from('Locations')
				    ->where('client_id = "' . $clientid . '"')
				    ->andWhereIn('id', $pat_loc_ids)
				    ->andWhere('isdelete = 0');
				    $droparray = $drop->fetchArray();
				   
				    foreach($droparray as $k_drop => $v_drop)
				    {
				        $master_loc_res[$v_drop['id']] = $v_drop;
				    }
				
				    foreach($patlocs as $k_pat_loc => $v_pat_loc)
				    {
				        if($v_pat_loc['id'] == $last_location_id)
				        {
				            $last_location['last_location'] = $master_loc_res[$v_pat_loc['location_id']]['location'];
				        }
				    }
				}

				if(strlen($last_location) > '0')
				{
					return $last_location;
				}
				else
				{
					return $last_location;
				}
			}
			else
			{
				return $last_location;
			}
		}
//ISPC-1236
		public function get_valid_last_location_adress($ipid)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $last_location_adress['last_location_adress'] = ' ';
		    
		    $patloc = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientLocation')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('isdelete="0"')
		    ->andWhere('discharge_location = "0"')
		    ->andWhere("valid_till='0000-00-00 00:00:00' OR DATE(valid_till) <= DATE(NOW())")
		    ->orderBy('valid_from,id ASC');
		    $patlocs = $patloc->fetchArray();
		    
		    if($patlocs){
		        $last_location = end($patlocs);
		    }
		    
		    $lc = new Locations();
		    $locationsdata = $lc->getLocations($logininfo->clientid, 0);
		    $locType6 = $lc->checkLocationsClientByType($logininfo->clientid, 6);
		    
		    foreach($locationsdata as $locationdata)
		    {
		        $locdata[$locationdata['id']] = $locationdata;
		    }
		    
		   /*  if($locType6)
		    {
		        $pc = new ContactPersonMaster();
		        $pcps = $pc->getPatientContact($ipid, false);
		        
		        $locdata[$last_location['location_id']]['location'] = 'bei Kontaktperson (' . $pcps[substr($last_location['location_id'], -1)-1]['cnt_last_name'] . ($pcps[substr($last_location['location_id'], -1)-1]['cnt_first_name'] != '' ? ', ' . $pcps[substr($last_location['location_id'], -1)-1]['cnt_first_name']: '' ) . ')';
		        $last_location_adress['last_location_adress'] = $locdata[$last_location['location_id']]['location'].'\n'.$pcps[substr($last_location['location_id'], -1)-1]['cnt_street1'].'\n '.$pcps[substr($last_location['location_id'], -1)-1]['cnt_zip'].', '.$pcps[substr($last_location['location_id'], -1)-1]['cnt_city'] ;        
		    }  */
		    
		    if($patlocs)
		    { //there are no active today location
		        foreach($patlocs as $kpatloc => $vpatloc)
		        {
		            $pat_loc_ids[] = $vpatloc['location_id'];
		            $pat_ipid2patlocid[$vpatloc['ipid']][] = $vpatloc['location_id'];
		            $last_location_id = $vpatloc['id'];
		        }
		        $pat_loc_ids = array_values(array_unique($pat_loc_ids));

		        if ($pat_loc_ids){
		            		       
		            $drop = Doctrine_Query::create()
		            ->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
		            ->from('Locations')
		            ->where('client_id = "' . $clientid . '"')
		            ->andWhereIn('id', $pat_loc_ids)
		            ->andWhere('isdelete = 0');
		            $droparray = $drop->fetchArray();
		            
		            foreach($droparray as $k_drop => $v_drop)
		            {
		                $master_loc_res[$v_drop['id']] = $v_drop;
		     
		            }
		            if($v_drop) {
		                
		                if ($v_drop['location_type'] === '5'){
		                    
		                    $sql = "*, e.epid as epid, id,AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
		                    $sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
		                    $sql .= ",AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') as street1";
		                    $sql .= ",AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') as zip";
		                    $sql .= ",AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') as city";
		                    
		                    $isadmin = 0;
		                    // if super admin check if patient is visible or not
		                    if($logininfo->usertype == 'SA')
		                    {
		                        $sql = "*,e.epid as epid, id,";
		                        $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
		                        $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
		                        $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
		                        $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
		                        $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
		                    }
		                    //adress from patient
		                    $patient = Doctrine_Query::create()
		                    ->select($sql)
		                    ->from('PatientMaster as p')
		                    ->where('p.isdelete = 0')
		                    ->leftJoin("p.EpidIpidMapping e")
		                    ->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid)
		                    ->andWhere('p.ipid LIKE "' . $ipid . '"');
		                    $patient_res = $patient->fetchArray();
		                    
		                    if($patient_res) {
		                        foreach($patient_res as $k_res => $v_res)
		                        {
		                            $v_res_final['straße_patient'] = (strlen($v_res['street1']) > '0' ? html_entity_decode($v_res['street1'], ENT_QUOTES, 'utf-8') : '');
		                            $v_res_final['plz_patient'] = (strlen($v_res['zip']) > '0' ? html_entity_decode($v_res['zip'], ENT_QUOTES, 'utf-8') : '');
		                            $v_res_final['ort_patient'] = (strlen($v_res['city']) > '0' ? html_entity_decode($v_res['city'], ENT_QUOTES, 'utf-8') : '');
		                            
		                            $last_location_adress['last_location_adress'] = $v_drop['location']. '\n ' .$v_res_final['straße_patient'].'\n '.$v_res_final['plz_patient'].', '.$v_res_final['ort_patient'] ;
		                        }
		                    }
		                }else {
		                    // adresa de aici...
		                    foreach($patlocs as $k_pat_loc => $v_pat_loc)
		                    {
		                        if($v_pat_loc['id'] == $last_location_id)
		                        {
		                            $last_location_adress['last_location_adress'] = $master_loc_res[$v_pat_loc['location_id']]['location']. '\n ' .$master_loc_res[$v_pat_loc['location_id']]['street'].'\n '.$master_loc_res[$v_pat_loc['location_id']]['zip'].', '.$master_loc_res[$v_pat_loc['location_id']]['city'] ;
		                        }
		                    }
		                } 
		            }

		        } 
		    }
		    return $last_location_adress;	    
		}
		
		public function get_contact_persons($ipid)
		{
			$contact_pers = new ContactPersonMaster();
			$contact_persons = $contact_pers->getPatientContact($ipid, true);
			
			// get client family degree
			$FamilyDegree_m = new FamilyDegree();
			$fam_deg = $FamilyDegree_m->getFamilyDegrees(1,$ipid);
			
			//cnt_familydegree_id
			$incr = '1';
			
			if($contact_persons)
			{
			    $contact_person_data = array();
			    $contact_person_data['name_vorsorgebevollmaechtigt'] = '';   //ISPC-1236 Lore 13.05.2020
			    
				foreach($contact_persons as $k_cp => $v_cp)
				{
					$contact_person_data['contact_vorname_#' . $incr] = (strlen($v_cp['cnt_first_name']) > '0' ? html_entity_decode($v_cp['cnt_first_name'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_nachname_#' . $incr] = (strlen($v_cp['cnt_last_name']) > '0' ? html_entity_decode($v_cp['cnt_last_name'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_straße_#' . $incr] = (strlen($v_cp['cnt_street1']) > '0' ? html_entity_decode($v_cp['cnt_street1'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_plz_#' . $incr] = (strlen($v_cp['cnt_zip']) > '0' ? html_entity_decode($v_cp['cnt_zip'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_ort_#' . $incr] = (strlen($v_cp['cnt_city']) > '0' ? html_entity_decode($v_cp['cnt_city'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_telefon_#' . $incr] = (strlen($v_cp['cnt_phone']) > '0' ? html_entity_decode($v_cp['cnt_phone'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_mobiltelefon_#' . $incr] = (strlen($v_cp['cnt_mobile']) > '0' ? html_entity_decode($v_cp['cnt_mobile'], ENT_QUOTES, 'utf-8') : '');
					$contact_person_data['contact_status_#' . $incr] = (strlen($v_cp['cnt_familydegree_id']) > '0' ? html_entity_decode($fam_deg[$v_cp['cnt_familydegree_id']], ENT_QUOTES, 'utf-8') : '');
					//Lore 28.11.2019
					if(!empty($v_cp['cnt_familydegree_id'])){
					    $contact_person_data['contact_beziehung#'.$incr] = $fam_deg[$v_cp['cnt_familydegree_id']];
					} else {
					    $contact_person_data['contact_beziehung#'.$incr] ='';
					}
					
					//ISPC-1236 Lore 13.05.2020
					if($v_cp['cnt_hatversorgungsvollmacht'] == 1){
					    $contact_person_data['name_vorsorgebevollmaechtigt'] = (strlen($v_cp['cnt_first_name']) > '0' ? html_entity_decode($v_cp['cnt_first_name'], ENT_QUOTES, 'utf-8') : '').' '.(strlen($v_cp['cnt_last_name']) > '0' ? html_entity_decode($v_cp['cnt_last_name'], ENT_QUOTES, 'utf-8') : '');
					}
					
					
					$incr++;
				}

				if(count($contact_persons) < '3')
				{
					for($incr = (count($contact_persons) + 1); $incr < '3'; $incr++)
					{
						$contact_person_data['contact_vorname_#' . $incr] = '';
						$contact_person_data['contact_nachname_#' . $incr] = '';
						$contact_person_data['contact_straße_#' . $incr] = '';
						$contact_person_data['contact_plz_#' . $incr] = '';
						$contact_person_data['contact_ort_#' . $incr] = '';
						$contact_person_data['contact_telefon_#' . $incr] = '';
						$contact_person_data['contact_mobiltelefon_#' . $incr] = '';
						$contact_person_data['contact_status_#' . $incr] = '';
						
						$contact_person_data['contact_beziehung#' . $incr] = ''; //ISPC-1236 Lore 28.11.2019
						
					}
				}
			}
			else
			{
				for($incr = 1; $incr < '3'; $incr++)
				{
					$contact_person_data['contact_vorname_#'.$incr] = '';
					$contact_person_data['contact_nachname_#'.$incr] = '';
					$contact_person_data['contact_straße_#'. $incr] = '';
					$contact_person_data['contact_plz_#'. $incr] = '';
					$contact_person_data['contact_ort_#'.$incr] = '';
					$contact_person_data['contact_telefon_#'. $incr] = '';
					$contact_person_data['contact_mobiltelefon_#'. $incr] = '';
					$contact_person_data['contact_status_#'. $incr] = '';
					
					$contact_person_data['contact_beziehung#'. $incr] = '';		//ISPC-1236 Lore 28.11.2019
					
				}
				
				$contact_person_data['name_vorsorgebevollmaechtigt'] = '';   //ISPC-1236 Lore 13.05.2020
				
			}
			
			return $contact_person_data;
		}

		public function get_patient_nursing($ipid)
		{
			$pfleges = PatientPflegedienste::get_multiple_patient_pflegedienste($ipid);
			$pfleges['results'][$ipid] = array_values($pfleges['results'][$ipid]);

			if(!empty($pfleges['results'][$ipid]))
			{
				$pflege_data_arr['pflegedienst'] = $pfleges['results'][$ipid][0]['nursing'];
				$pflege_data_arr['pflegedienst_vorname'] = $pfleges['results'][$ipid][0]['first_name'];
				$pflege_data_arr['pflegedienst_nachname'] = $pfleges['results'][$ipid][0]['last_name'];
				$pflege_data_arr['pflegedienst_straße'] = $pfleges['results'][$ipid][0]['street1'];
				$pflege_data_arr['pflegedienst_plz'] = $pfleges['results'][$ipid][0]['zip'];
				$pflege_data_arr['pflegedienst_ort'] = $pfleges['results'][$ipid][0]['city'];
				$pflege_data_arr['pflegedienst_anrede'] = $pfleges['results'][$ipid][0]['salutation'];
				$pflege_data_arr['pflegedienst_telefonnummer'] = $pfleges['results'][$ipid][0]['phone_practice'];
				
				$pflege_data_arr['behandelnder_Pflegedienst'] = $pfleges['results'][$ipid][0]['nursing'];
				
				//ispc 1236 24.11.2016
// 				if (!empty(trim($pfleges['results'][$ipid][0]['fax']))){
				if (!empty($pfleges['results'][$ipid][0]['fax']) ){
					$pflege_data_arr['behandelnder_Pflegedienst_fax'] = trim($pfleges['results'][$ipid][0]['fax']); 
				} else{
					$pflege_data_arr['behandelnder_Pflegedienst_fax'] = ""; 
				}
				
				
				// ISPC-1236  24.07.2018 Ancuta
				if (!empty($pfleges['results'][$ipid][0]['phone_emergency']) ){
					$pflege_data_arr['Notrufnummer_Pflegedienst'] = trim($pfleges['results'][$ipid][0]['phone_emergency']); 
				} else{
					$pflege_data_arr['Notrufnummer_Pflegedienst'] = ""; 
				}
				
			}
			else
			{
				$pflege_data_arr['pflegedienst'] = '';
				$pflege_data_arr['pflegedienst_vorname'] = '';
				$pflege_data_arr['pflegedienst_nachname'] = '';
				$pflege_data_arr['pflegedienst_straße'] = '';
				$pflege_data_arr['pflegedienst_plz'] = '';
				$pflege_data_arr['pflegedienst_ort'] = '';
				$pflege_data_arr['pflegedienst_anrede'] = '';
				$pflege_data_arr['pflegedienst_telefonnummer'] = '';
				
				$pflege_data_arr['behandelnder_Pflegedienst'] = '';
				$pflege_data_arr['behandelnder_Pflegedienst_fax'] = '';
				$pflege_data_arr['Notrufnummer_Pflegedienst'] = ''; //24.07.2018 
			}
			//Multiple
//			if(!empty($pfleges))
//			{
//				$incr = '1';
//				foreach($pfleges['results'][$ipid] as $k_res => $v_res)
//				{
//					$pflege_data_arr['pflegedienst_vorname_#' . $incr] = $v_res['first_name'];
//					$pflege_data_arr['pflegedienst_nachname_#' . $incr] = $v_res['last_name'];
//					$pflege_data_arr['pflegedienst_straße_#' . $incr] = $v_res['street1'];
//					$pflege_data_arr['pflegedienst_plz_#' . $incr] = $v_res['zip'];
//					$pflege_data_arr['pflegedienst_ort_#' . $incr] = $v_res['city'];
//					$incr++;
//				}
//
//				if(count($pfleges['results'][$ipid]) < '3')
//				{
//					$i = '1';
//					for($i = (count($pfleges['results'][$ipid]) + 1); $i <= '3'; $i++)
//					{
//						$pflege_data_arr['pflegedienst_vorname_#' . $i] = '';
//						$pflege_data_arr['pflegedienst_nachname_#' . $i] = '';
//						$pflege_data_arr['pflegedienst_straße_#' . $i] = '';
//						$pflege_data_arr['pflegedienst_plz_#' . $i] = '';
//						$pflege_data_arr['pflegedienst_ort_#' . $i] = '';
//						$i++;
//					}
//				}
//			}
//			else
//			{
//				for($i = 1; $i <= '3'; $i++)
//				{
//					$pflege_data_arr['pflegedienst_vorname_#' . $i] = '';
//					$pflege_data_arr['pflegedienst_nachname_#' . $i] = '';
//					$pflege_data_arr['pflegedienst_straße_#' . $i] = '';
//					$pflege_data_arr['pflegedienst_plz_#' . $i] = '';
//					$pflege_data_arr['pflegedienst_ort_#' . $i] = '';
//				}
//			}

			return $pflege_data_arr;
		}

		public function get_patient_nursing_multiple($ipid)
		{
		    $pfleges = PatientPflegedienste::get_multiple_patient_pflegedienste($ipid);
		    $pfleges['results'][$ipid] = array_values($pfleges['results'][$ipid]);
		    $incr = '1';
		    
		    if(!empty($pfleges['results'][$ipid]))
		    {
		        
		        foreach($pfleges['results'][$ipid] as $k_pf => $v_pf) 
		        {
		            $pflege_data_arr['pflegedienst_#' . $incr ]                = (strlen($v_pf['nursing']) > '0' ? html_entity_decode($v_pf['nursing'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_vorname_#' . $incr ]        = (strlen($v_pf['first_name']) > '0' ? html_entity_decode($v_pf['first_name'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_nachname_#' . $incr ]       = (strlen($v_pf['last_name']) > '0' ? html_entity_decode($v_pf['last_name'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_straße_#' . $incr ]         = (strlen($v_pf['street1']) > '0' ? html_entity_decode($v_pf['street1'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_plz_#' . $incr ]            = (strlen($v_pf['zip']) > '0' ? html_entity_decode($v_pf['zip'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_ort_#' . $incr]             = (strlen($v_pf['city']) > '0' ? html_entity_decode($v_pf['city'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_anrede_#' . $incr]          = (strlen($v_pf['salutation']) > '0' ? html_entity_decode($v_pf['salutation'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['pflegedienst_telefonnummer_#' . $incr]   = (strlen($v_pf['phone_practice']) > '0' ? html_entity_decode($v_pf['phone_practice'], ENT_QUOTES, 'utf-8') : '');
		            
		            $pflege_data_arr['behandelnder_Pflegedienst_#' . $incr ]    = (strlen($v_pf['nursing']) > '0' ? html_entity_decode($v_pf['nursing'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['behandelnder_Pflegedienst_fax_#' . $incr] = (strlen($v_pf['fax']) > '0' ? html_entity_decode($v_pf['fax'], ENT_QUOTES, 'utf-8') : '');
		            $pflege_data_arr['Notrufnummer_Pflegedienst_#' . $incr]     = (strlen($v_pf['phone_emergency']) > '0' ? html_entity_decode($v_pf['phone_emergency'], ENT_QUOTES, 'utf-8') : '');

		            $incr++;
		        }
		        if(count($pfleges['results'][$ipid]) < '4')
		        {
		            for($incr = (count($pfleges['results'][$ipid]) + 1); $incr < '4'; $incr++)
		            {
		                $pflege_data_arr['pflegedienst_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_vorname_#' . $incr] = '';
		                $pflege_data_arr['pflegedienst_nachname_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_straße_#' . $incr] = '';
		                $pflege_data_arr['pflegedienst_plz_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_ort_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_anrede_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_telefonnummer_#' . $incr ]= '';
		                $pflege_data_arr['behandelnder_Pflegedienst_#' . $incr ]= '';
		                $pflege_data_arr['behandelnder_Pflegedienst_fax_#' . $incr ]= '';
		                $pflege_data_arr['Notrufnummer_Pflegedienst_#' . $incr ]= ''; 
		            }
		        }
		        
		     }
		        else
		        {
		            for($incr = 1; $incr < '4'; $incr++)
		            {
		                $pflege_data_arr['pflegedienst_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_vorname_#' . $incr] = '';
		                $pflege_data_arr['pflegedienst_nachname_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_straße_#' . $incr] = '';
		                $pflege_data_arr['pflegedienst_plz_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_ort_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_anrede_#' . $incr ]= '';
		                $pflege_data_arr['pflegedienst_telefonnummer_#' . $incr ]= '';
		                $pflege_data_arr['behandelnder_Pflegedienst_#' . $incr ]= '';
		                $pflege_data_arr['behandelnder_Pflegedienst_fax_#' . $incr ]= '';
		                $pflege_data_arr['Notrufnummer_Pflegedienst_#' . $incr ]= ''; //24.07.2018
		            }
		        }

              return $pflege_data_arr;
        }
		                    
		public function get_patient_specialists($ipid)
		{
			$patient_specialists = PatientSpecialists::get_patient_specialists($ipid, true);
			$patient_specialists = array_values($patient_specialists);

			if($patient_specialists)
			{
				//token with umlaut
				$specialist_data_arr['fachärzt_vorname'] = $patient_specialists[0]['master']['first_name'];
				$specialist_data_arr['fachärzt_nachname'] = $patient_specialists[0]['master']['last_name'];
				$specialist_data_arr['fachärzt_straße'] = $patient_specialists[0]['master']['street1'];
				$specialist_data_arr['fachärzt_plz'] = $patient_specialists[0]['master']['zip'];
				$specialist_data_arr['fachärzt_ort'] = $patient_specialists[0]['master']['city'];
				$specialist_data_arr['fachärzt_anrede'] = $patient_specialists[0]['master']['salutation'];
				$specialist_data_arr['fachärzt_titel'] = $patient_specialists[0]['master']['title'];
				$specialist_data_arr['fachärzt_fax'] = $patient_specialists[0]['master']['fax'];

				//token without umlaut
				$specialist_data_arr['facharzt_vorname'] = $patient_specialists[0]['master']['first_name'];
				$specialist_data_arr['facharzt_nachname'] = $patient_specialists[0]['master']['last_name'];
				$specialist_data_arr['facharzt_straße'] = $patient_specialists[0]['master']['street1'];
				$specialist_data_arr['facharzt_plz'] = $patient_specialists[0]['master']['zip'];
				$specialist_data_arr['facharzt_ort'] = $patient_specialists[0]['master']['city'];
				$specialist_data_arr['facharzt_anrede'] = $patient_specialists[0]['master']['salutation'];
				$specialist_data_arr['facharzt_titel'] = $patient_specialists[0]['master']['title'];
				$specialist_data_arr['facharzt_fax'] = $patient_specialists[0]['master']['fax'];
			}
			else
			{
				//token with umlaut
				$specialist_data_arr['fachärzt_vorname'] = '';
				$specialist_data_arr['fachärzt_nachname'] = '';
				$specialist_data_arr['fachärzt_straße'] = '';
				$specialist_data_arr['fachärzt_plz'] = '';
				$specialist_data_arr['fachärzt_ort'] = '';
				$specialist_data_arr['fachärzt_anrede'] = '';
				$specialist_data_arr['fachärzt_titel'] = '';
				$specialist_data_arr['fachärzt_fax'] = '';

				//token without umlaut
				$specialist_data_arr['facharzt_vorname'] = '';
				$specialist_data_arr['facharzt_nachname'] = '';
				$specialist_data_arr['facharzt_straße'] = '';
				$specialist_data_arr['facharzt_plz'] = '';
				$specialist_data_arr['facharzt_ort'] = '';
				$specialist_data_arr['facharzt_anrede'] = '';
				$specialist_data_arr['facharzt_titel'] = '';
				$specialist_data_arr['facharzt_fax'] = '';
			}
			//Multiple
//			if($patient_specialists)
//			{
//				$incr = 1;
//				foreach($patient_specialists as $k_spec => $v_spec)
//				{
//					//token with umlaut
//					$specialist_data_arr['fachärzt_vorname_#' . $incr] = $v_spec['master']['first_name'];
//					$specialist_data_arr['fachärzt_nachname_#' . $incr] = $v_spec['master']['last_name'];
//					$specialist_data_arr['fachärzt_straße_#' . $incr] = $v_spec['master']['street1'];
//					$specialist_data_arr['fachärzt_plz_#' . $incr] = $v_spec['master']['zip'];
//					$specialist_data_arr['fachärzt_ort_#' . $incr] = $v_spec['master']['city'];
//
//					//token without umlaut
//					$specialist_data_arr['facharzt_vorname_#' . $incr] = $v_spec['master']['first_name'];
//					$specialist_data_arr['facharzt_nachname_#' . $incr] = $v_spec['master']['last_name'];
//					$specialist_data_arr['facharzt_straße_#' . $incr] = $v_spec['master']['street1'];
//					$specialist_data_arr['facharzt_plz_#' . $incr] = $v_spec['master']['zip'];
//					$specialist_data_arr['facharzt_ort_#' . $incr] = $v_spec['master']['city'];
//					$incr++;
//				}
//
//				if(count($patient_specialists) < 3)
//				{
//					$i = '1';
//					for($i = (count($patient_specialists) + 1); $i <= '3'; $i++)
//					{
//						//token with umlaut
//						$specialist_data_arr['fachärzt_vorname_#' . $i] = '';
//						$specialist_data_arr['fachärzt_nachname_#' . $i] = '';
//						$specialist_data_arr['fachärzt_straße_#' . $i] = '';
//						$specialist_data_arr['fachärzt_plz_#' . $i] = '';
//						$specialist_data_arr['fachärzt_ort_#' . $i] = '';
//
//						//token without umlaut
//						$specialist_data_arr['facharzt_vorname_#' . $i] = '';
//						$specialist_data_arr['facharzt_nachname_#' . $i] = '';
//						$specialist_data_arr['facharzt_straße_#' . $i] = '';
//						$specialist_data_arr['facharzt_plz_#' . $i] = '';
//						$specialist_data_arr['facharzt_ort_#' . $i] = '';
//					}
//				}
//			}
//			else
//			{
//				$i = '1';
//				for($i = (count($patient_specialists) + 1); $i <= '3'; $i++)
//				{
//					//token with umlaut
//					$specialist_data_arr['fachärzt_vorname_#' . $i] = '';
//					$specialist_data_arr['fachärzt_nachname_#' . $i] = '';
//					$specialist_data_arr['fachärzt_straße_#' . $i] = '';
//					$specialist_data_arr['fachärzt_plz_#' . $i] = '';
//					$specialist_data_arr['fachärzt_ort_#' . $i] = '';
//
//					//token without umlaut
//					$specialist_data_arr['facharzt_vorname_#' . $i] = '';
//					$specialist_data_arr['facharzt_nachname_#' . $i] = '';
//					$specialist_data_arr['facharzt_straße_#' . $i] = '';
//					$specialist_data_arr['facharzt_plz_#' . $i] = '';
//					$specialist_data_arr['facharzt_ort_#' . $i] = '';
//				}
//			}

			return $specialist_data_arr;
		}

		public function get_patient_pharmacy($ipid)
		{
		    $patient_pharmacy = PatientPharmacy::getPatientPharmacy($ipid);
		    $patient_pharmacy = array_values($patient_pharmacy);
		    
		    if($patient_pharmacy)
		    {
		        $patient_pharmacy_arr['apotheke_name'] = $patient_pharmacy[0]['apotheke'];
		        $patient_pharmacy_arr['apotheke_vorname'] = $patient_pharmacy[0]['Pharmacy']['first_name'];
		        $patient_pharmacy_arr['apotheke_nachname'] = $patient_pharmacy[0]['Pharmacy']['last_name'];
		        $patient_pharmacy_arr['apotheke_anrede'] = $patient_pharmacy[0]['salutation'];
		        $patient_pharmacy_arr['apotheke_straße'] = $patient_pharmacy[0]['street1'];
		        $patient_pharmacy_arr['apotheke_plz'] = $patient_pharmacy[0]['zip'];
		        $patient_pharmacy_arr['apotheke_ort'] = $patient_pharmacy[0]['city'];
		        $patient_pharmacy_arr['apotheke_telefon'] = $patient_pharmacy[0]['phone'];
		        $patient_pharmacy_arr['apotheke_fax'] = $patient_pharmacy[0]['fax'];
		        $patient_pharmacy_arr['apotheke_email'] = $patient_pharmacy[0]['Pharmacy']['email'];
		    }
		    else
		    {
		        $patient_pharmacy_arr['apotheke_name'] = '';
		        $patient_pharmacy_arr['apotheke_vorname'] = '';
		        $patient_pharmacy_arr['apotheke_nachname'] = '';
		        $patient_pharmacy_arr['apotheke_anrede'] = '';
		        $patient_pharmacy_arr['apotheke_straße'] = '';
		        $patient_pharmacy_arr['apotheke_plz'] = '';
		        $patient_pharmacy_arr['apotheke_ort'] = '';
		        $patient_pharmacy_arr['apotheke_telefon'] = '';
		        $patient_pharmacy_arr['apotheke_fax'] = '';
		        $patient_pharmacy_arr['apotheke_email'] = '';
		    }
		    return $patient_pharmacy_arr;
		    
		}
		//ISPC-tokens_24-07-2018
		public function get_patient_pharmacy_multiple($ipid)
		{
			$patient_pharmacy = PatientPharmacy::getPatientPharmacy($ipid);
			$patient_pharmacy = array_values($patient_pharmacy);

			$incr = '1';
			
			if($patient_pharmacy)
			{
			    foreach($patient_pharmacy as $k_ph => $v_ph) 
			    {
			        $patient_pharmacy_arr['apotheke_name_#' .$incr]      = (strlen($v_ph['apotheke']) > '0' ? html_entity_decode($v_ph['apotheke'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_vorname_#' . $incr]  = (strlen($v_ph['Pharmacy']['first_name']) > '0' ? html_entity_decode($v_ph['Pharmacy']['first_name'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_nachname_#' . $incr] = (strlen($v_ph['Pharmacy']['last_name']) > '0' ? html_entity_decode($v_ph['Pharmacy']['last_name'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_anrede_#' . $incr]   = (strlen($v_ph['salutation']) > '0' ? html_entity_decode($v_ph['salutation'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_straße_#' . $incr]   = (strlen($v_ph['street1']) > '0' ? html_entity_decode($v_ph['street1'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_plz_#' . $incr]      = (strlen($v_ph['zip']) > '0' ? html_entity_decode($v_ph['zip'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_ort_#' . $incr]      = (strlen($v_ph['city']) > '0' ? html_entity_decode($v_ph['city'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_telefon_#' . $incr]  = (strlen($v_ph['phone']) > '0' ? html_entity_decode($v_ph['phone'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_fax_#' . $incr]      = (strlen($v_ph['fax']) > '0' ? html_entity_decode($v_ph['fax'], ENT_QUOTES, 'utf-8') : '');
			        $patient_pharmacy_arr['apotheke_email_#' . $incr]    = (strlen($v_ph['Pharmacy']['email']) > '0' ? html_entity_decode($v_ph['Pharmacy']['email'], ENT_QUOTES, 'utf-8') : '');
			        
			        $incr++;
			    }
			    
			    if(count($patient_pharmacy) < '4')
			    {
			        for($incr = (count($patient_pharmacy) + 1); $incr < '4'; $incr++)
			        {
			            $patient_pharmacy_arr['apotheke_name_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_vorname_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_nachname_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_anrede_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_straße_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_plz_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_ort_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_telefon_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_fax_#' . $incr] = '';
			            $patient_pharmacy_arr['apotheke_email_#' . $incr] = '';       
			        }
			    }   
			}
			else
			{
			    for($incr = 1; $incr < '4'; $incr++)
			    {
    			    $patient_pharmacy_arr['apotheke_name_#' . $incr] = '';
    	   		    $patient_pharmacy_arr['apotheke_vorname_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_nachname_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_anrede_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_straße_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_plz_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_ort_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_telefon_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_fax_#' . $incr] = '';
    			    $patient_pharmacy_arr['apotheke_email_#' . $incr] = '';
			    }
			}
			
			return $patient_pharmacy_arr;
		}

		public function get_patient_voluntaryworkers($ipid)
		{
			$patient_voluntaryworkers = PatientVoluntaryworkers::get_patient_voluntaryworkers($ipid, true);
			$patient_voluntaryworkers = array_values($patient_voluntaryworkers);
			
			$hospv = new PatientHospizvizits();
			$hospizvizits = $hospv->getPatienthospizvizits($ipid);
			
			foreach($hospizvizits as $k=>$vvisits){
			    if($vvisits['type'] == "n"){
    			    $assigned_vw_visits[$vvisits['vw_id']] += 1;
			    } else{
    			    $assigned_vw_visits[$vvisits['vw_id']] += $vvisits['amount'];
			    }
			}
			
			if($patient_voluntaryworkers)
			{
				$voluntaryworkers_arr['ehrenamtliche_vorname'] = $patient_voluntaryworkers[0]['master']['first_name'];
				$voluntaryworkers_arr['ehrenamtliche_nachname'] = $patient_voluntaryworkers[0]['master']['last_name'];
				$voluntaryworkers_arr['ehrenamtliche_straße'] = $patient_voluntaryworkers[0]['master']['street'];
				$voluntaryworkers_arr['ehrenamtliche_plz'] = $patient_voluntaryworkers[0]['master']['zip'];
				$voluntaryworkers_arr['ehrenamtliche_ort'] = $patient_voluntaryworkers[0]['master']['city'];
				$voluntaryworkers_arr['ehrenamtliche_anrede'] = $patient_voluntaryworkers[0]['master']['salutation'];
				$voluntaryworkers_arr['ehrenamtliche_telefon'] = $patient_voluntaryworkers[0]['master']['phone'];
				$voluntaryworkers_arr['ehrenamtliche_email'] = $patient_voluntaryworkers[0]['master']['email'];
				
				
				$voluntaryworkers_arr['zugeordneter_Ehrenamtlicher'] = $patient_voluntaryworkers[0]['master']['first_name'].' '.$patient_voluntaryworkers[0]['master']['last_name'];

                if($patient_voluntaryworkers[0]['start_date'] != "0000-00-00 00:00:00"){
    				$voluntaryworkers_arr['Versorgungsstart_Ehrenamtlicher'] = date("d.m.Y",strtotime($patient_voluntaryworkers[0]['start_date']));
                }else{
    				$voluntaryworkers_arr['Versorgungsstart_Ehrenamtlicher'] = "";
                }
                
                if($patient_voluntaryworkers[0]['end_date'] != "0000-00-00 00:00:00"){
    				$voluntaryworkers_arr['versorgungsende_Ehrenamtlicher'] = date("d.m.Y",strtotime($patient_voluntaryworkers[0]['end_date']));
                }else{
    				$voluntaryworkers_arr['versorgungsende_Ehrenamtlicher'] = "";
                }
                
                if($assigned_vw_visits[$patient_voluntaryworkers[0]['master']['parent_id']] ){
    				$voluntaryworkers_arr['AnzahlBesuche_voluntaryworker'] = $assigned_vw_visits[$patient_voluntaryworkers[0]['master']['parent_id']];
                } else {
    				$voluntaryworkers_arr['AnzahlBesuche_voluntaryworker'] = "";
                }
			}
			else
			{
				$voluntaryworkers_arr['ehrenamtliche_vorname'] = '';
				$voluntaryworkers_arr['ehrenamtliche_nachname'] = '';
				$voluntaryworkers_arr['ehrenamtliche_straße'] = '';
				$voluntaryworkers_arr['ehrenamtliche_plz'] = '';
				$voluntaryworkers_arr['ehrenamtliche_ort'] = '';
				$voluntaryworkers_arr['ehrenamtliche_anrede'] = '';
				$voluntaryworkers_arr['ehrenamtliche_telefon'] = '';
				$voluntaryworkers_arr['ehrenamtliche_email'] = '';
				
				$voluntaryworkers_arr['zugeordneter_Ehrenamtlicher'] = '';
				$voluntaryworkers_arr['Versorgungsstart_Ehrenamtlicher'] = '';
				$voluntaryworkers_arr['versorgungsende_Ehrenamtlicher'] = '';
				$voluntaryworkers_arr['AnzahlBesuche_voluntaryworker'] = '';
			}
			
			return $voluntaryworkers_arr;
		}

		/**
		 * ISPC-1236 Lore
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_familienstand($ipid)
		{
		    $familienstand = Stammdatenerweitert::getFamilienstandfun();
		    $patient_familienstand = Stammdatenerweitert::getStammdatenerweitert($ipid, true);
		    $patient_familienstand_ids = array_map(function($plan) {
		        return $plan['familienstand'];
		      }, $patient_familienstand);
		    		    
		    if($patient_familienstand_ids)
		    {   
		        $familienstand_arr['familienstand'] = $familienstand[$patient_familienstand_ids[0]];
		    }
		    else
		    { 	        
		        $familienstand_arr['familienstand'] = '';
		    }

		    return $familienstand_arr;
		}
		
		/**
		 * ISPC-1236 Lore
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_pflegegrad($ipid)
		{
		    $patient_pflegegrad = PatientMaintainanceStage::getpatientMaintainanceStage($ipid, true);
		    $patient_pflegegrad = array_values($patient_pflegegrad);	    
		    
		    if($patient_pflegegrad)
		    {
		        $pflegegrad_arr['pflegegrad'] = ' ';
		        $pflegegrad_arr['PG_Erst_hoeher'] = ' '; //Lore 28.11.2019
		        
		        foreach($patient_pflegegrad as $k_ps => $v_ps)
		        {
		            //$pflegegrad_arr['pflegegrad'] .= ' PG'.$v_ps['stage'].' '.date("d.m.Y",strtotime($v_ps['fromdate'])) ;
		            $pflegegrad_arr['pflegegrad'] .=  $v_ps['fromdate'] !='0000-00-00' ? ' PG'.$v_ps['stage'].' '.date("d.m.Y",strtotime($v_ps['fromdate'])) : ' PG'.$v_ps['stage'] ;     //Lore 21.08.2020
		            
		            if($v_ps['erstantrag'] != 0 ){
		                $pflegegrad_arr['pflegegrad'] .= $v_ps['e_fromdate'] != '0000-00-00'  ? ', Erstantrag '.date("d.m.Y",strtotime($v_ps['e_fromdate'])) : ', Erstantrag ' ;          //Lore 21.08.2020
		                $pflegegrad_arr['PG_Erst_hoeher'] = 'Erstantrag';
		                
		            }
		            if($v_ps['horherstufung'] != 0 ){
		                $pflegegrad_arr['pflegegrad'] .= $v_ps['h_fromdate'] != '0000-00-00' ? ', Höherstufung beantragt '.date("d.m.Y",strtotime($v_ps['h_fromdate'])) : ', Höherstufung beantragt ' ;       //Lore 21.08.2020
		                $pflegegrad_arr['PG_Erst_hoeher'] = 'Höherstufung beantragt';		//ISPC-1236 Lore 28.11.2019
		            } 
		            $pflegegrad_arr['pflegegrad'] .= '; ';
		        }		        
		    }
		    else
		    {
		        $pflegegrad_arr['pflegegrad'] = '';
		        $pflegegrad_arr['PG_Erst_hoeher'] = '';
		        
		    }
		    return $pflegegrad_arr;
		
		}
	
		/**
		 * ISPC-1236 Lore 28.11.2019
		 * living_will from ACP
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_patientenvollmach($ipid)
		{
		    $acp = new PatientAcp();
		    $acp_data = $acp->getByIpid(array($ipid));
		    $current_acp_data = $acp_data[$ipid];
		    
		    if ( ! empty($current_acp_data)) {
		        foreach ($current_acp_data as $k => $block) {
		            switch ($block['division_tab']) {
		                
		                case "living_will" :
        		            if($block['active'] == "yes")  {
        		                $patientenvollmach_arr['Patientenvollmacht'] = 'Ist vorhanden, '; 
        		                
        		                if (!empty($block['comments'])){
        		                    $patientenvollmach_arr['Patientenvollmacht'] .= 'wo hinterlegt '.$block['comments'];
        		                }
        		                if (!empty($block['files'])){
        		                    $patientenvollmach_arr['Patientenvollmacht'] .= ', ausgestellt am '.date("d.m.Y",strtotime($block['files'][0]['file_date'])); 
        		                }
        		            } 
        		            else 
        		            {
        		                if($block['active'] == "no")  {
        		                   $patientenvollmach_arr['Patientenvollmacht'] = 'Ist nicht vorhanden ';
        		                  }
        		                //ISPC-2671 Lore 07.09.2020
        		                elseif($block['active'] == "no_wanted")  {
        		                      $patientenvollmach_arr['Patientenvollmacht'] = 'Ist nicht gewollt ';
        		                  }
        		                 //. 
        		                else
        		                  {
        		                   $patientenvollmach_arr['Patientenvollmacht'] = 'nicht bekannt';
        		                  }
        		            }
        		        break;
		            }
		        }
		    }
		    else 
		    {
		        $patientenvollmach_arr['Patientenvollmacht'] = ' ';
		    }
		    return $patientenvollmach_arr;  
		}
		

		/**
		 * ISPC-1236 Lore 28.11.2019
		 * healthcare_proxy from ACP
		 * Vorsogevollmacht show all information from the box “Advance Care Planning - Vorsorgevollmacht” in patients details. 
		 * It should show the status which is selected, the contact person which is set in this box and the date which is set in ausgestellt am
		 * $bevollmaechtigter$ - Firstname Surname of the contactperson which is shown in Advance care planing box "Vorsorgevollmacht"
		 * $bevollmaechtigter_tel$ - phone number of that person		 
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_vorsogevollmacht($ipid)   
		{
		    $acp = new PatientAcp();
		    $acp_data = $acp->getByIpid(array($ipid));
		    $current_acp_data = $acp_data[$ipid];
		    $cnt_id = 0;
		   // dd($current_acp_data);
		    if ( ! empty($current_acp_data)) {
		        foreach ($current_acp_data as $k => $block) {
		            
		            if ($block['division_tab'] == "healthcare_proxy" ) {
		                
    		            if ($block['active'] == "yes") {

    		                $cnt_id = $block['contactperson_master_id'];
    		                
    		                $vorsogevollmacht_arr['Vorsogevollmacht'] = ' Ist vorhanden, ';
    		                
    		                if (!empty($block['comments'])){
    		                    $vorsogevollmacht_arr['Vorsogevollmacht'] .= 'wo hinterlegt '.$block['comments'];
    		                }
    		                if (!empty($block['files'])){
    		                    $vorsogevollmacht_arr['Vorsogevollmacht'] .= ', ausgestellt am '.date("d.m.Y",strtotime($block['files'][0]['file_date']));
    		                }
    		                
    		            }
    		            elseif($block['active'] == "no")  {
    		                $vorsogevollmacht_arr['Vorsogevollmacht'] = 'Ist nicht vorhanden ';
    		            }
    		            //ISPC-2671 Lore 07.09.2020
    		            elseif($block['active'] == "no_wanted")  {
    		                $vorsogevollmacht_arr['Vorsogevollmacht'] = 'Ist nicht gewollt ';
    		            }
    		            //. 
    		            else
    		            {
    		                $vorsogevollmacht_arr['Vorsogevollmacht'] = 'nicht bekannt';
    		            }
		            }
		        }
		    }    

		   
		    $contact = array();
		    if (! empty($cnt_id)) {
		        $pcs = ContactPersonMaster::getPatientContactById($cnt_id, false);
		        if(!empty($pcs)){
		            $contact = $pcs[0];
		        }
		    }
		    
		    if (! empty($contact)) {
                $vorsogevollmacht_arr['Vorsogevollmacht'] .=', '.$contact['cnt_last_name'].', '.$contact['cnt_first_name']; 	
                $vorsogevollmacht_arr['bevollmaechtigter'] = $contact['cnt_first_name'].', '.$contact['cnt_last_name']; 
                $vorsogevollmacht_arr['bevollmaechtigter_tel'] = $contact['cnt_phone']; 
                
		    } else {
		        $vorsogevollmacht_arr['Vorsogevollmacht'] .= '';
		        $vorsogevollmacht_arr['bevollmaechtigter'] = '';				
		        $vorsogevollmacht_arr['bevollmaechtigter_tel'] = ''; 
		    }
		    
		    return $vorsogevollmacht_arr; 
		    
		}
		
		/**
		 * ISPC-1236 Lore 28.11.2019
		 * care_orders from ACP
		 * $Betreuer$ - Firstname Surname of the contactperson which is shown in Advance care planing box "Betreuungsverfügung"
		 * $Betreuer_Tel$ - phone number of that person
		 * $Betreuer_Mobil$ - mobile number of that person
		 * @param unknown $ipid
		 * @return string|NULL
		 */
		public function get_patient_gesetzlicher_betreuer($ipid)
		{
		    $acp = new PatientAcp();
		    $acp_data = $acp->getByIpid(array(
		        $ipid
		    ));
		    $current_acp_data = $acp_data[$ipid];
		    
		    
		    $cnt_id = 0;
		    if (! empty($current_acp_data)) {
		        foreach ($current_acp_data as $k => $block) {
		            if ($block['division_tab'] == "care_orders" && $block['active'] == "yes") {
		                $cnt_id = $block['contactperson_master_id'];
		            }
		        }
		    }
		    $contact = array();
		    if (! empty($cnt_id)) {
		        $pcs = ContactPersonMaster::getPatientContactById($cnt_id, false);
		        if(!empty($pcs)){
		            $contact = $pcs[0];
		        }
		    }
		    
		    if (! empty($contact)) {
		        $patient_gesetzlicher_arr['gesetzlicher_Betreuer'] = $contact['cnt_last_name'] . ', ' . $contact['cnt_first_name'];
		        $patient_gesetzlicher_arr['Betreuer'] = $contact['cnt_first_name'] . ', ' . $contact['cnt_last_name']; 
		        $patient_gesetzlicher_arr['Betreuer_Tel'] = $contact['cnt_phone'];
		        $patient_gesetzlicher_arr['Betreuer_Mobil'] = $contact['cnt_mobile'];
		    } else {
		        $patient_gesetzlicher_arr['gesetzlicher_Betreuer'] = '';
		        $patient_gesetzlicher_arr['Betreuer'] = ''; 
		        $patient_gesetzlicher_arr['Betreuer_Tel'] = ''; 
		        $patient_gesetzlicher_arr['Betreuer_Mobil'] = ''; 
		    }
		    
		    
		    return $patient_gesetzlicher_arr;
		}
		
		/**
		 * ISPC-1236 Lore
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_memo($ipid)
		{
		    $patient_memo = PatientMemo::getpatientMemo($ipid, true);  
		    $memo_pat = trim(strip_tags($patient_memo[0]['memo']));
		    
		    if (strlen($memo_pat) == '0')
		    {
		        $memo_arr['Memo'] = '';
		        $memo_arr['memo'] = '';
    		}
        	else
    		{
    		    $memo_arr['Memo'] = strip_tags($patient_memo[0]['memo']);
    		    $memo_arr['memo'] = strip_tags($patient_memo[0]['memo']); // ISPC-1236 Lore 28.11.2019
		    }
	
		    return $memo_arr;
		}

		/**
		 * ISPC-1236 Lore 28.11.2019
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_admission_details($ipid)
		{
		    $pat_adm_det = Doctrine_Query::create()
/* 		    ->select('*')
		    ->from('Formone')
		    ->where('ipid = ?', $ipid); */
		    ->select('*')             
		    ->from('PatientReadmission')                  //TODO-3825 Lore 08.02.2021
		    ->where('ipid = ?', $ipid)
		    ->orderBy("date DESC")
		    ->limit(1);
		    $pat_adm_det_arr= $pat_adm_det->fetchArray();
		    // Erstkontakt durch
		    $first_contact = array(
		        "1"=>"Patient/Angehörige",
		        "2"=>"Beratungsdienst",
		        "3"=>"Hausarzt",
		        "4"=>"Stationäres Hospiz",
		        "5"=>"Krankenhaus",
		        "6"=>"Palliativstation",
		        "7"=>"Facharzt",
		        "8"=>"ambulanter Hospizdienst",
		        "9"=>"Ambulante Pflege",
		        "10"=>"Stationäre Pflege",
		        "11"=>"Sonstige"
		    );
		    $adm_det_arr = array();
		    if ($pat_adm_det_arr){
		        //$adm_det_arr['erstkontakt'] = $pat_adm_det_arr[0]['erstkontakt'];
		        $adm_det_arr['erstkontakt'] = $first_contact[$pat_adm_det_arr[0]['first_contact']];           //TODO-3825 Lore 08.02.2021
		    }
		    else
		    {
		        $adm_det_arr['erstkontakt'] = ''; 
		    }
		    
		    return $adm_det_arr;
		}
		
		public function get_patient_supplies($ipid)
		{
			$patient_supplies = PatientSupplies::get_patient_supplies($ipid, true);
			$patient_supplies = array_values($patient_supplies);

			
			if($patient_supplies)
			{
				//token with umlaut
				$supplies_arr['sanitätshäuser_name'] = $patient_supplies[0]['master']['supplier'];
				$supplies_arr['sanitätshäuser_vorname'] = $patient_supplies[0]['master']['first_name'];
				$supplies_arr['sanitätshäuser_nachname'] = $patient_supplies[0]['master']['last_name'];
				$supplies_arr['sanitätshäuser_straße'] = $patient_supplies[0]['master']['street1'];
				$supplies_arr['sanitätshäuser_plz'] = $patient_supplies[0]['master']['zip'];
				$supplies_arr['sanitätshäuser_ort'] = $patient_supplies[0]['master']['city'];
				$supplies_arr['sanitätshäuser_anrede'] = $patient_supplies[0]['master']['salutation'];
				$supplies_arr['sanitätshäuser_telefon'] = $patient_supplies[0]['master']['phone'];
				$supplies_arr['sanitätshäuser_fax'] = $patient_supplies[0]['master']['fax'];
				$supplies_arr['sanitätshäuser_email'] = $patient_supplies[0]['master']['email'];

				//token without umlaut
				$supplies_arr['sanitatshauser_name'] = $patient_supplies[0]['master']['supplier'];
				$supplies_arr['sanitatshauser_vorname'] = $patient_supplies[0]['master']['first_name'];
				$supplies_arr['sanitatshauser_nachname'] = $patient_supplies[0]['master']['last_name'];
				$supplies_arr['sanitatshauser_straße'] = $patient_supplies[0]['master']['street1'];
				$supplies_arr['sanitatshauser_plz'] = $patient_supplies[0]['master']['zip'];
				$supplies_arr['sanitatshauser_ort'] = $patient_supplies[0]['master']['city'];
				$supplies_arr['sanitatshauser_anrede'] = $patient_supplies[0]['master']['salutation'];
				$supplies_arr['sanitatshauser_telefon'] = $patient_supplies[0]['master']['phone'];
				$supplies_arr['sanitatshauser_fax'] = $patient_supplies[0]['master']['fax'];
				$supplies_arr['sanitatshauser_email'] = $patient_supplies[0]['master']['email'];
			}
			else
			{
				//token with umlaut
				$supplies_arr['sanitätshäuser_name'] = '';
				$supplies_arr['sanitätshäuser_vorname'] = '';
				$supplies_arr['sanitätshäuser_nachname'] = '';
				$supplies_arr['sanitätshäuser_straße'] = '';
				$supplies_arr['sanitätshäuser_plz'] = '';
				$supplies_arr['sanitätshäuser_ort'] = '';
				$supplies_arr['sanitätshäuser_anrede'] = '';
				$supplies_arr['sanitätshäuser_telefon'] = '';
				$supplies_arr['sanitätshäuser_fax'] = '';
				$supplies_arr['sanitätshäuser_email'] = '';

				//token without umlaut
				$supplies_arr['sanitatshauser_name'] = '';
				$supplies_arr['sanitatshauser_vorname'] = '';
				$supplies_arr['sanitatshauser_nachname'] = '';
				$supplies_arr['sanitatshauser_straße'] = '';
				$supplies_arr['sanitatshauser_plz'] = '';
				$supplies_arr['sanitatshauser_ort'] = '';
				$supplies_arr['sanitatshauser_anrede'] = '';
				$supplies_arr['sanitatshauser_telefon'] = '';
				$supplies_arr['sanitatshauser_fax'] = '';
				$supplies_arr['sanitatshauser_email'] = '';
			}

			return $supplies_arr;
		}

		public function get_patient_supplies_multiple($ipid)
		{
		    $patient_supplies = PatientSupplies::get_patient_supplies($ipid, true);
		    $patient_supplies = array_values($patient_supplies);
		    $incr = '1';
		    
		    if($patient_supplies)
		    {
		        foreach($patient_supplies as $k_ps => $v_ps)
		        {

		            $supplies_arr['sanitätshäuser_name_#' . $incr]     = (strlen($v_ps['master']['supplier']) > '0' ? html_entity_decode($v_ps['master']['supplier'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_vorname_#' . $incr]  = (strlen($v_ps['master']['first_name']) > '0' ? html_entity_decode($v_ps['master']['first_name'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_nachname_#' . $incr] = (strlen($v_ps['master']['last_name']) > '0' ? html_entity_decode($v_ps['master']['last_name'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_straße_#' . $incr]   = (strlen($v_ps['master']['street1']) > '0' ? html_entity_decode($v_ps['master']['street1'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_plz_#' . $incr]      = (strlen($v_ps['master']['zip']) > '0' ? html_entity_decode($v_ps['master']['zip'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_ort_#' . $incr]      = (strlen($v_ps['master']['city']) > '0' ? html_entity_decode($v_ps['master']['city'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_anrede_#' . $incr]   = (strlen($v_ps['master']['salutation']) > '0' ? html_entity_decode($v_ps['master']['salutation'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_telefon_#' . $incr]  = (strlen($v_ps['master']['phone']) > '0' ? html_entity_decode($v_ps['master']['phone'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_fax_#' . $incr]      = (strlen($v_ps['master']['fax']) > '0' ? html_entity_decode($v_ps['master']['fax'], ENT_QUOTES, 'utf-8') : '');
		            $supplies_arr['sanitätshäuser_email_#' . $incr]    = (strlen($v_ps['master']['email']) > '0' ? html_entity_decode($v_ps['master']['email'], ENT_QUOTES, 'utf-8') : '');
		            
		            $incr++;
		        }
		        if(count($patient_supplies) < '4')
		        {
		            for($incr = (count($patient_supplies) + 1); $incr < '4'; $incr++)
		            {
		                $supplies_arr['sanitätshäuser_name_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_vorname_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_nachname_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_straße_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_plz_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_ort_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_anrede_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_telefon_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_fax_#' . $incr] = '';
		                $supplies_arr['sanitätshäuser_email_#' . $incr] = ''; 
		            }
		        }
		    }
		    else
		    {
		        for($incr = 1; $incr < '4'; $incr++)
		        {
		            $supplies_arr['sanitätshäuser_name_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_vorname_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_nachname_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_straße_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_plz_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_ort_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_anrede_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_telefon_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_fax_#' . $incr] = '';
		            $supplies_arr['sanitätshäuser_email_#' . $incr] = ''; 
		        }
		    }
		    return $supplies_arr;
		}
		
		public function get_patient_suppliers($ipid)
		{
			$patient_suppliers = PatientSuppliers::get_patient_suppliers($ipid, true);
			$patient_suppliers = array_values($patient_suppliers);
			
			$incr = '1';
			
			
			if($patient_suppliers)
			{
			    foreach($patient_suppliers as $k_ps => $v_ps)
			    {
/*     				$suppliers_arr['versorger_name_#' . $incr] = $patient_suppliers[$incr]['master']['supplier'];
    				$suppliers_arr['versorger_typ_#' . $incr] = $patient_suppliers[$incr]['master']['type'];
    				$suppliers_arr['versorger_vorname_#' . $incr] = $patient_suppliers[$incr]['master']['first_name'];
    				$suppliers_arr['versorger_nachname_#' . $incr] = $patient_suppliers[$incr]['master']['last_name'];
    				$suppliers_arr['versorger_straße_#' . $incr] = $patient_suppliers[$incr]['master']['street1'];
    				$suppliers_arr['versorger_plz_#' . $incr] = $patient_suppliers[$incr]['master']['zip'];
    				$suppliers_arr['versorger_ort_#' . $incr] = $patient_suppliers[$incr]['master']['city'];
    				$suppliers_arr['versorger_anrede_#' . $incr] = $patient_suppliers[$incr]['master']['salutation'];
    				$suppliers_arr['versorger_telefon_#' . $incr] = $patient_suppliers[$incr]['master']['phone'];
    				$suppliers_arr['versorger_fax_#' . $incr] = $patient_suppliers[$incr]['master']['fax'];
    				$suppliers_arr['versorger_email_#' . $incr] = $patient_suppliers[$incr]['master']['email']; */
			        
			        $suppliers_arr['versorger_name'] = $patient_suppliers[0]['master']['supplier'];
			        $suppliers_arr['versorger_typ'] = $patient_suppliers[0]['master']['type'];
			        $suppliers_arr['versorger_vorname'] = $patient_suppliers[0]['master']['first_name'];
			        $suppliers_arr['versorger_nachname'] = $patient_suppliers[0]['master']['last_name'];
			        $suppliers_arr['versorger_straße'] = $patient_suppliers[0]['master']['street1'];
			        $suppliers_arr['versorger_plz'] = $patient_suppliers[0]['master']['zip'];
			        $suppliers_arr['versorger_ort'] = $patient_suppliers[0]['master']['city'];
			        $suppliers_arr['versorger_anrede'] = $patient_suppliers[0]['master']['salutation'];
			        $suppliers_arr['versorger_telefon'] = $patient_suppliers[0]['master']['phone'];
			        $suppliers_arr['versorger_fax'] = $patient_suppliers[0]['master']['fax'];
			        $suppliers_arr['versorger_email'] = $patient_suppliers[0]['master']['email'];
    				
    				$incr++;
                }
			}
			else
			{
				$suppliers_arr['versorger_name'] = '';
				$suppliers_arr['versorger_typ'] = '';
				$suppliers_arr['versorger_vorname'] = '';
				$suppliers_arr['versorger_nachname'] = '';
				$suppliers_arr['versorger_straße'] = '';
				$suppliers_arr['versorger_plz'] = '';
				$suppliers_arr['versorger_ort'] = '';
				$suppliers_arr['versorger_anrede'] = '';
				$suppliers_arr['versorger_telefon'] = '';
				$suppliers_arr['versorger_fax'] = '';
				$suppliers_arr['versorger_email'] = '';
			}

			return $suppliers_arr;
		}

		/**
		 * TODO-2650  Lore 14.11.2019
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_suppliers_multiple($ipid)
		{
		    $patient_suppliers = PatientSuppliers::get_patient_suppliers($ipid, true);
		    $patient_suppliers = array_values($patient_suppliers);

		    $incr = '1';
		    
		    if($patient_suppliers)
		    {
		        foreach($patient_suppliers as $k_ps => $v_ps)
		        {
		            $suppliers_arr['versorger_name_#' . $incr] = $patient_suppliers[$incr]['master']['supplier'];
		            $suppliers_arr['versorger_typ_#' . $incr] = $patient_suppliers[$incr]['master']['type'];
		            $suppliers_arr['versorger_vorname_#' . $incr] = $patient_suppliers[$incr]['master']['first_name'];
		            $suppliers_arr['versorger_nachname_#' . $incr] = $patient_suppliers[$incr]['master']['last_name'];
		            $suppliers_arr['versorger_straße_#' . $incr] = $patient_suppliers[$incr]['master']['street1'];
		            $suppliers_arr['versorger_plz_#' . $incr] = $patient_suppliers[$incr]['master']['zip'];
		            $suppliers_arr['versorger_ort_#' . $incr] = $patient_suppliers[$incr]['master']['city'];
		            $suppliers_arr['versorger_anrede_#' . $incr] = $patient_suppliers[$incr]['master']['salutation'];
		            $suppliers_arr['versorger_telefon_#' . $incr] = $patient_suppliers[$incr]['master']['phone'];
		            $suppliers_arr['versorger_fax_#' . $incr] = $patient_suppliers[$incr]['master']['fax'];
		            $suppliers_arr['versorger_email_#' . $incr] = $patient_suppliers[$incr]['master']['email'];
		            
		            $incr++;
		        }
		        
		        if(count($patient_suppliers) < '4')
		        {
		            for($incr = (count($patient_suppliers) + 1); $incr < '4'; $incr++)
		            {
		                $suppliers_arr['versorger_name_#' . $incr] = '';
		                $suppliers_arr['versorger_typ_#' . $incr] = '';
		                $suppliers_arr['versorger_vorname_#' . $incr] = '';
		                $suppliers_arr['versorger_nachname_#' . $incr] = '';
		                $suppliers_arr['versorger_straße_#' . $incr] = '';
		                $suppliers_arr['versorger_plz_#' . $incr] = '';
		                $suppliers_arr['versorger_ort_#' . $incr] = '';
		                $suppliers_arr['versorger_anrede_#' . $incr] = '';
		                $suppliers_arr['versorger_telefon_#' . $incr] = '';
		                $suppliers_arr['versorger_fax_#' . $incr] = '';
		                $suppliers_arr['versorger_email_#' . $incr] = '';
		            }
		        }  
		        
		        
		        
		        
		    }
		    else
		    {
		        for($incr = 1; $incr < '4'; $incr++){
		            $suppliers_arr['versorger_name_#' . $incr] = '';
		            $suppliers_arr['versorger_typ_#' . $incr] = '';
		            $suppliers_arr['versorger_vorname_#' . $incr] = '';
		            $suppliers_arr['versorger_nachname_#' . $incr] = '';
		            $suppliers_arr['versorger_straße_#' . $incr] = '';
		            $suppliers_arr['versorger_plz_#' . $incr] = '';
		            $suppliers_arr['versorger_ort_#' . $incr] = '';
		            $suppliers_arr['versorger_anrede_#' . $incr] = '';
		            $suppliers_arr['versorger_telefon_#' . $incr] = '';
		            $suppliers_arr['versorger_fax_#' . $incr] = '';
		            $suppliers_arr['versorger_email_#' . $incr] = '';
		        }
		    }
		    
		    return $suppliers_arr;
		}
		
		public function get_patient_physiotherapists($ipid)
		{
			$patient_physiotherapists = PatientPhysiotherapist::get_patient_physiotherapists($ipid, true);
			$patient_physiotherapists = array_values($patient_physiotherapists);

			if($patient_physiotherapists)
			{
				$physiotherapists_arr['physiotherapeuten_name'] = $patient_physiotherapists[0]['master']['physiotherapist'];
				$physiotherapists_arr['physiotherapeuten_vorname'] = $patient_physiotherapists[0]['master']['first_name'];
				$physiotherapists_arr['physiotherapeuten_nachname'] = $patient_physiotherapists[0]['master']['last_name'];
				$physiotherapists_arr['physiotherapeuten_straße'] = $patient_physiotherapists[0]['master']['street1'];
				$physiotherapists_arr['physiotherapeuten_plz'] = $patient_physiotherapists[0]['master']['zip'];
				$physiotherapists_arr['physiotherapeuten_ort'] = $patient_physiotherapists[0]['master']['city'];
				$physiotherapists_arr['physiotherapeuten_anrede'] = $patient_physiotherapists[0]['master']['salutation'];
				$physiotherapists_arr['physiotherapeuten_telefon'] = $patient_physiotherapists[0]['master']['phone_practice'];
				$physiotherapists_arr['physiotherapeuten_fax'] = $patient_physiotherapists[0]['master']['fax'];
				$physiotherapists_arr['physiotherapeuten_email'] = $patient_physiotherapists[0]['master']['email'];
			}
			else
			{
				$physiotherapists_arr['physiotherapeuten_name'] = '';
				$physiotherapists_arr['physiotherapeuten_vorname'] = '';
				$physiotherapists_arr['physiotherapeuten_nachname'] = '';
				$physiotherapists_arr['physiotherapeuten_straße'] = '';
				$physiotherapists_arr['physiotherapeuten_plz'] = '';
				$physiotherapists_arr['physiotherapeuten_ort'] = '';
				$physiotherapists_arr['physiotherapeuten_anrede'] = '';
				$physiotherapists_arr['physiotherapeuten_telefon'] = '';
				$physiotherapists_arr['physiotherapeuten_fax'] = '';
				$physiotherapists_arr['physiotherapeuten_email'] = '';
			}

			return $physiotherapists_arr;
		}

		
		public function get_patient_homecares($ipid)
		{
		    $patient_homecares = PatientHomecare::get_patient_homecares($ipid, true);
		    $patient_homecares = array_values($patient_homecares);
		    
		    if($patient_homecares)
		    {
		        $homecares_arr['homecare_name'] = $patient_homecares[0]['master']['homecare'];
		        $homecares_arr['homecare_vorname'] = $patient_homecares[0]['master']['first_name'];
		        $homecares_arr['homecare_nachname'] = $patient_homecares[0]['master']['last_name'];
		        $homecares_arr['homecare_straße'] = $patient_homecares[0]['master']['street1'];
		        $homecares_arr['homecare_plz'] = $patient_homecares[0]['master']['zip'];
		        $homecares_arr['homecare_ort'] = $patient_homecares[0]['master']['city'];
		        $homecares_arr['homecare_anrede'] = $patient_homecares[0]['master']['salutation'];
		        $homecares_arr['homecare_telefon'] = $patient_homecares[0]['master']['phone_practice'];
		        $homecares_arr['homecare_fax'] = $patient_homecares[0]['master']['fax'];
		        $homecares_arr['homecare_email'] = $patient_homecares[0]['master']['email'];
		    }
		    else
		    {
		        $homecares_arr['homecare_name'] = '';
		        $homecares_arr['homecare_vorname'] = '';
		        $homecares_arr['homecare_nachname'] = '';
		        $homecares_arr['homecare_straße'] = '';
		        $homecares_arr['homecare_plz'] = '';
		        $homecares_arr['homecare_ort'] = '';
		        $homecares_arr['homecare_anrede'] = '';
		        $homecares_arr['homecare_telefon'] = '';
		        $homecares_arr['homecare_fax'] = '';
		        $homecares_arr['homecare_email'] = '';
		    }
		    
		    return $homecares_arr;
		}
		//ISPC-tokens_24-07-2018
		public function get_patient_homecares_multiple($ipid)
		{
			$patient_homecares = PatientHomecare::get_patient_homecares($ipid, true);
			$patient_homecares = array_values($patient_homecares);

			$incr = '1';
			
			if($patient_homecares)
			{
			    foreach($patient_homecares as $k_hc => $v_hc)
			    {
			        $homecares_arr['homecare_name_#' . $incr]    =(strlen($v_hc['master']['homecare'])       > '0' ? html_entity_decode($v_hc['master']['homecare'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_vorname_#' . $incr] =(strlen($v_hc['master']['first_name'])     > '0' ? html_entity_decode($v_hc['master']['first_name'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_nachname_#' . $incr]=(strlen($v_hc['master']['last_name'])      > '0' ? html_entity_decode($v_hc['master']['last_name'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_straße_#' . $incr]  =(strlen($v_hc['master']['street1'])        > '0' ? html_entity_decode($v_hc['master']['street1'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_plz_#' . $incr]     =(strlen($v_hc['master']['zip'])            > '0' ? html_entity_decode($v_hc['master']['zip'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_ort_#' . $incr]     =(strlen($v_hc['master']['city'])           > '0' ? html_entity_decode($v_hc['master']['city'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_anrede_#' . $incr]  =(strlen($v_hc['master']['salutation'])     > '0' ? html_entity_decode($v_hc['master']['salutation'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_telefon_#' . $incr] =(strlen($v_hc['master']['phone_practice']) > '0' ? html_entity_decode($v_hc['master']['phone_practice'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_fax_#' . $incr]     =(strlen($v_hc['master']['fax'])            > '0' ? html_entity_decode($v_hc['master']['fax'], ENT_QUOTES, 'utf-8') : '');
			        $homecares_arr['homecare_email_#' . $incr]   =(strlen($v_hc['master']['email'])          > '0' ? html_entity_decode($v_hc['master']['email'], ENT_QUOTES, 'utf-8') : '');
			    
			        $incr++;    
			    } 
			    if(count($patient_homecares) < '4')
			    {
			        for($incr = (count($patient_homecares) + 1); $incr < '4'; $incr++)
			        {
			            $homecares_arr['homecare_name_#' . $incr]    ='';
			            $homecares_arr['homecare_vorname_#' . $incr] ='';
			            $homecares_arr['homecare_nachname_#' . $incr]='';
			            $homecares_arr['homecare_straße_#' . $incr]  ='';
			            $homecares_arr['homecare_plz_#' . $incr]     ='';
			            $homecares_arr['homecare_ort_#' . $incr]     ='';
			            $homecares_arr['homecare_anrede_#' . $incr]  ='';
			            $homecares_arr['homecare_telefon_#' . $incr] ='';
			            $homecares_arr['homecare_fax_#' . $incr]     ='';
			            $homecares_arr['homecare_email_#' . $incr]   ='';
			        }
			    }
			}
			else
			{
			    for($incr = 1; $incr < '4'; $incr++)
			    {
			        $homecares_arr['homecare_name_#' . $incr]    ='';
			        $homecares_arr['homecare_vorname_#' . $incr] ='';
			        $homecares_arr['homecare_nachname_#' . $incr]='';
			        $homecares_arr['homecare_straße_#' . $incr]  ='';
			        $homecares_arr['homecare_plz_#' . $incr]     ='';
			        $homecares_arr['homecare_ort_#' . $incr]     ='';
			        $homecares_arr['homecare_anrede_#' . $incr]  ='';
			        $homecares_arr['homecare_telefon_#' . $incr] ='';
			        $homecares_arr['homecare_fax_#' . $incr]     ='';
			        $homecares_arr['homecare_email_#' . $incr]   ='';
			    }   
			}
			return $homecares_arr;
		}

		public function get_patient_hospice_assoc($ipid)
		{
			$patient_hosp_assoc = PatientHospiceassociation::get_patient_hospiceassociations($ipid, true);
			$patient_hosp_assoc = array_values($patient_hosp_assoc);

			if($patient_hosp_assoc)
			{
				$hosp_assoc_arr['hospizdienst_name'] = $patient_hosp_assoc[0]['master']['hospice_association'];
				$hosp_assoc_arr['hospizdienst_vorname'] = $patient_hosp_assoc[0]['master']['first_name'];
				$hosp_assoc_arr['hospizdienst_nachname'] = $patient_hosp_assoc[0]['master']['last_name'];
				$hosp_assoc_arr['hospizdienst_straße'] = $patient_hosp_assoc[0]['master']['street1'];
				$hosp_assoc_arr['hospizdienst_plz'] = $patient_hosp_assoc[0]['master']['zip'];
				$hosp_assoc_arr['hospizdienst_ort'] = $patient_hosp_assoc[0]['master']['city'];
				$hosp_assoc_arr['hospizdienst_anrede'] = $patient_hosp_assoc[0]['master']['salutation'];
				$hosp_assoc_arr['hospizdienst_telefon'] = $patient_hosp_assoc[0]['master']['phone_practice'];
				$hosp_assoc_arr['hospizdienst_fax'] = $patient_hosp_assoc[0]['master']['fax'];
				$hosp_assoc_arr['hospizdienst_email'] = $patient_hosp_assoc[0]['master']['email'];
			}
			else
			{
				$hosp_assoc_arr['hospizdienst_name'] = '';
				$hosp_assoc_arr['hospizdienst_vorname'] = '';
				$hosp_assoc_arr['hospizdienst_nachname'] = '';
				$hosp_assoc_arr['hospizdienst_straße'] = '';
				$hosp_assoc_arr['hospizdienst_plz'] = '';
				$hosp_assoc_arr['hospizdienst_ort'] = '';
				$hosp_assoc_arr['hospizdienst_anrede'] = '';
				$hosp_assoc_arr['hospizdienst_telefon'] = '';
				$hosp_assoc_arr['hospizdienst_fax'] = '';
				$hosp_assoc_arr['hospizdienst_email'] = '';
			}

			return $hosp_assoc_arr;
		}

		public function get_patient_course($ipid)
		{
		    // ISPC-1741
		    //* Verlauf Anamnese
		    //* Verlauf Befund
		    //* Verlauf Therapie // JUST this 3 were required
 

            $full_short_array = array(
    		    "A"=>"verlauf_anamnese",//ISPC-1741
    		    "B"=>"verlauf_befund",//ISPC-1741
    		    "C"=>"verlauf_cave",
    		    "D"=>"verlauf_diagnosen",
    		    "G"=>"verlauf_gespräch",
    		    "H"=>"verlauf_hauptdiagnose",
    		    "I"=>"verlauf_iv_medikation",
    		    "K"=>"verlauf_kommentar",
    		    "L"=>"verlauf_leistung",
    		    "M"=>"verlauf_medikation",
    		    "N"=>"verlauf_bedarfsmedikamente",
    		    "Q"=>"verlauf_apotheke",
    		    "T"=>"verlauf_therapie",//ISPC-1741
    		    "V"=>"verlauf_koordination",
    		    "XT"=>"verlauf_telefon"	    
            );
            
            
/*
            A :    $anamnese$//ISPC-1741
            B :    $befund$//ISPC-1741
            C :    $cave$
            D :    $diagnosen$
            //E :    $leistungserfassung$ //  removed because generates  errors
            G :    $gespräch$
            H :    $hauptdiagnose$
            I :    $iv_medikation$
            K :    $kommentar$
            L :    $leistung$
            M :    $medikation$
            N :    $bedarfsmedikamente$
            Q :    $apotheke$
            T :    $therapie$//ISPC-1741
            V :    $koordination$
            XT :    $telefon */
            $short_array = array_keys($full_short_array);
            $patient_course = PatientCourse::get_multi_pat_shortcuts_course(array($ipid), $short_array,false,false,false );
			$patient_course = array_values($patient_course);

			if($patient_course)
			{
    			foreach($patient_course as $k=>$cd)    
    			{
    			    foreach($full_short_array as $sh=>$sh_data){
    			        if($cd['course_type'] == $sh && strlen($cd['course_title']) > 0 ){
    			            $course_array[$sh_data] .= $cd['course_title'].", ";
    			        }
    			    }
    			}
    			
    			foreach($full_short_array as $sh=>$sh_data){
    			    if(strlen($course_array[$sh_data]) > 0 ){
    			        $course_arr[$sh_data] = substr($course_array[$sh_data],0,-2);
    			    }else {
    			        $course_arr[$sh_data] = "";
    			    }
    			}
			}
			else
			{
				foreach($full_short_array as $sh=>$sh_data){
   			        $course_arr[$sh_data] = "";
    			}
			}

			return $course_arr;
		}
		
		
		
		public function get_patient_assigned_users($ipid)
		{			
		    $logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		    
		    $epid = Pms_CommonData::getEpid($ipid);
		        
		    $usergroup = new Usergroup();
		    $MasterGroups = array("4","5");
		    $usersgroups = $usergroup->getUserGroups($MasterGroups);

		    if(count($usersgroups) > 0)
		    {
		        foreach($usersgroups as $group)
		        {
		            if($group['groupmaster'] == 4)
		            {
		                $groups_doctor_array[] = $group['id'];
		            }
		            elseif($group['groupmaster'] == 5)
		            {
		                $groups_nurse_array[] = $group['id'];
		            }
		            else
		            {
		                $groupsarray[] = $group['id'];
		            }
		        }
		    }
		    
		    $usrs = new User();
		    $users_docs_array = $usrs->getuserbyGroupId($groups_doctor_array, $clientid, true);
		    
		    //$Tr = new Zend_View_Helper_Translate();
		    foreach($users_docs_array as $user)
		    {
		        $docs_ids[] = $user['id'];
		        $docs[$user['id']] = $user['user_title'] . " " . $user['first_name'] . " " .$user['last_name'] ;
		        
		        $docs_faxes[$user['id']] = $user['user_title'] . " " . $user['first_name'] . " " .$user['last_name'] ;
		        //ispc 1236
		        if(!empty($user['fax'])){
		        	$docs_faxes[$user['id']] .= " (". trim($user['fax']) .")";
		        }
		    }
		    
		    $usrs = new User();
		    $users_docs_array = $usrs->getuserbyGroupId($groups_doctor_array, $clientid, true);
		    
	    
		    $docs_address = array(); 
		    $docs_phones = array(); 
		    //$Tr = new Zend_View_Helper_Translate();
		    foreach($users_docs_array as $user)
		    {
		        $docs_ids[] = $user['id'];
		        $docs[$user['id']] = $user['user_title'] . " " . $user['first_name'] . " " .$user['last_name'] ;
		        
		        $docs_faxes[$user['id']] = $user['user_title'] . " " . $user['first_name'] . " " .$user['last_name'] ;
		        //ispc 1236
		        if(!empty($user['fax'])){
		        	$docs_faxes[$user['id']] .= " (". trim($user['fax']) .")";
		        }
		        
		        
		        
		        // ISPC-2136 - address 24.07.2018 Ancuta 
		        $docs_address[$user['id']] = "";
		        if(!empty($user['user_title'])){
		            $docs_address[$user['id']] .= $user['user_title']." ";
		        }
		        
		        $docs_address[$user['id']] .= $user['first_name'] . " " .$user['last_name'] ;
		        
		        if(!empty($user['street1'])){
		        	$docs_address[$user['id']] .= " \n\r". trim($user['street1']) ."";
		        }
		        if(!empty($user['zip'])){
		        	$docs_address[$user['id']] .= "\n\r". trim($user['zip']) ."";
		        }
		        
		        if(!empty($user['city'])){
		            if(empty($user['zip'])){
    		        	$docs_address[$user['id']] .= "\n\r ";
		            }
		        	$docs_address[$user['id']] .= " ". trim($user['city']) ."";
		        }
		        
		        
		        //TODO-1767
		        if(!empty($user['phone'])){
		            $docs_phones[$user['id']] = trim($user['phone']);//  TODO-1767 new token ISPC, Added on 22.08.2018 
		        }
		        
		    }
		    
		    // ISPC-1236 - 19.10.2017
		    $users_nurse_array = $usrs->getuserbyGroupId($groups_nurse_array, $clientid, true);
		    
		    foreach($users_nurse_array as $user_n_data)
		    {
		    	$nurse_ids[] = $user_n_data['id'];
		    	$nurse[$user_n_data['id']] = $user_n_data['user_title'] . " " . $user_n_data['first_name'] . " " .$user_n_data['last_name'] ;
		        
		    	$nurse_faxes[$user_n_data['id']] = $user_n_data['user_title'] . " " . $user_n_data['first_name'] . " " .$user_n_data['last_name'] ;
		        //ispc 1236
		    	if(!empty($user_n_data['fax'])){
		        	$nurse_faxes[$user['id']] .= " (". trim($user_n_data['fax']) .")";
		        }
		    }
		    
		    
		    
		    $assigned_users = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientQpaMapping')
		    ->where('clientid =' . $logininfo->clientid)
		    ->andWhere('epid = "' . $epid . '"');
		    $assigned_users_res = $assigned_users->fetchArray();
		    
		    
		    $a_users[] = '99999999999';
		    foreach($assigned_users_res as $k_au => $v_au)
		    {
		        $a_users[] = $v_au['userid'];
		    }
 
		    
		    
		    foreach($assigned_users_res as $k_au => $v_au)
		    {
		        if(in_array($v_au['userid'],$docs_ids)) {
    		        $patient_assigned_doctors[] =  $docs[$v_au['userid']];
    		        $patient_assigned_doctors_fax[] =  $docs_faxes[$v_au['userid']];
    		        $patient_assigned_doctors_addresses[] =  $docs_address[$v_au['userid']];
    		        $patient_assigned_doctors_phones[] =  $docs_phones[$v_au['userid']];
		        } 
		        elseif(in_array($v_au['userid'],$nurse_ids)) 
		        {
		        	$patient_assigned_nurses[] =  $nurse[$v_au['userid']];
		        	$patient_assigned_nurses_fax[] =  $nurse_faxes[$v_au['userid']];
		        	
		        }
		    }
		    
		    if(!empty($patient_assigned_doctors))
		    {
	           $assigned_data["behandelnder_Arzt"] = implode(", ",$patient_assigned_doctors);
	           $assigned_data["Adresse_behandelnder_Arzt"] = implode(",\n\r",$patient_assigned_doctors_addresses);
	           
	           $assigned_data["behandelnder_Arzt_fon"] = implode(", ",$patient_assigned_doctors_phones); //  TODO-1767 new token ISPC, Added on 22.08.2018 
		    } 
		    else
		    {
	           $assigned_data["behandelnder_Arzt"] = "";
	           $assigned_data["Adresse_behandelnder_Arzt"] = "";
	           
	           $assigned_data["behandelnder_Arzt_fon"] = "";//  TODO-1767 new token ISPC, Added on 22.08.2018 
		    }
		    
		    // ISPC-1236 - 19.10.2017
		    if(!empty($patient_assigned_nurses))
		    {
		    	$assigned_data["behandelnde_Pflegekraft"] = implode(", ",$patient_assigned_nurses);
		    } 
		    else
		    {
	           $assigned_data["behandelnde_Pflegekraft"] = "";
		    }
		    
		    
		    if(!empty($patient_assigned_doctors_fax))
		    {
	           $assigned_data["behandelnder_Arzt_mit_fax"] = implode(", ",$patient_assigned_doctors_fax);
		    } 
		    else
		    {
	           $assigned_data["behandelnder_Arzt_mit_fax"] = "";
		    }
		    
		    
		    return $assigned_data;
 
		}

		public function get_Anlage4_next_date($ipids){
		    
		    if( ! is_array(($ipids))){
		        $ipids = array($ipids);
		    }
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;

		    $wlprevileges = new Modules();
		    $wl = $wlprevileges->checkModulePrivileges("51", $clientid);
		    
		   
		    
		    if(!$wl)
		    {	
		       $result_data['WL_Anlage4_8Wochen'] = "";
 		       return  $result_data;
		    }	    
 
		    
		    //get patients with activ hospiz location // ISPC-2062
		    $fdoc = Doctrine_Query::create()
		    ->select("id")
		    ->from('Locations')
		    ->where("location_type=2")
		    ->andWhere('isdelete=0')
		    ->andWhere("client_id=?", $logininfo->clientid)
		    ->orderBy('location ASC');
		    $lochospizarr = $fdoc->fetchArray();
		    	
		    foreach($lochospizarr as $k_hospiz => $v_hospiz)
		    {
		        $locid_hospiz[] = $v_hospiz['id'];
		    }
		    	
		    //get patient with location active Hospiz
		    if(!empty($locid_hospiz)){
		        $patlocs = Doctrine_Query::create()
		        ->select('location_id,ipid')
		        ->from('PatientLocation')
		        ->where('isdelete="0"')
		        ->andWhereIn('location_id', $locid_hospiz)
		        ->andWhere("valid_till='0000-00-00 00:00:00'")
		        ->orderBy('id DESC');
		        $patloc_hospizarr = $patlocs->fetchArray();
		        	
		        foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
		        {
		            $ipids_hospiz[] = $v_pathospiz['ipid'];
		        }
		    }
		    
		    
		    $q = "";
		    $q = Doctrine_Query::create();
		    $q->select("*")
		    ->from('EpidIpidMapping')
		    ->where("clientid= ?", $clientid)
		    ->andWhereIn("ipid", $ipids);
		    $ipidarray = $q->fetchArray();
		    
		    foreach($ipidarray as $key => $val)
		    {
		        $ipidarrays[] = $val['ipid'];
		    }
		   
		    if( empty( $ipidarrays)){
		       $result_data['WL_Anlage4_8Wochen'] = "";
 		       return  $result_data;
		    }
		    
		    //exclude the private patients
		    $health = Doctrine_Query::create();
		    $health->select("*")
		    ->from('PatientHealthInsurance')
		    ->whereIn('ipid', $ipidarrays)
		    ->andWhere('privatepatient="1"');
		    $health_arr = $health->fetchArray();
		    
		    
		    foreach($health_arr as $k_health => $v_health)
		    {
		        $privat_patient[] = $v_health['ipid'];
		    }
		    
		    foreach($ipidarrays as $k_ipid => $v_ipid)
		    {
		        if(!in_array($v_ipid, $privat_patient) && !in_array($v_ipid, $ipids_hospiz))
		        {
		            $ipidarr[] = $v_ipid;
		        }
		    }
		    
		    if(empty($ipidarr)){
               $result_data['WL_Anlage4_8Wochen'] = "";
 		       return  $result_data;
		    }
		  
		    //get 6weeks patients recheck
		    $start_date = date('Y-m-d', strtotime('-1 day', time()));
		    $end_date = date('Y-m-d', strtotime('+100 days', time()));
		    
		    
		    $sql = '';
		    $sql .= "*,";
		    $sql .= "e.epid,";
		    $sql .= "DATEDIFF('" . $start_date . "', p.admission_date) as month_start_days,";
		    $sql .= "DATEDIFF('" . $end_date . "', p.admission_date) as month_end_days,";
		    $sql .= "DATEDIFF('" . $start_date . "', p.admission_date)/56 as month_start,";
		    $sql .= "DATEDIFF('" . $end_date . "', p.admission_date)/56 as month_end,";
		    $sql .= "ADDDATE( p.admission_date, (ceil( DATEDIFF( '" . $start_date . "', p.admission_date ) /56 ) *56 )) AS event_day,";
		    $sql .= "(floor(DATEDIFF('" . $end_date . "', p.admission_date)/56) - floor(DATEDIFF('" . $start_date . "', p.admission_date)/56)) as have_value";
		    
		    $patientq = Doctrine_Query::create();
		    $patientq->select($sql)
		    ->from('PatientMaster p')
		    ->where('p.isdelete = 0')
		    ->andWhere('p.isarchived = 0')
		    ->andWhere('p.isstandby = 0')
		    ->andWhere('p.isstandbydelete = 0')
		    ->andWhereIn('ipid', $ipidarr);
		    $patientq->leftJoin("p.EpidIpidMapping e");
		    $patientq->andWhere('e.clientid = ' . $clientid);
		    $patientq->having('have_value > 0');
		    $patientsArray = $patientq->fetchArray();

		    foreach($patientsArray as $patientSel)
		    {
		        $ipids_final[] = $patientSel['ipid'];
		        $patients_final[$patientSel['ipid']] = $patientSel;
		    }
		    
		    $pm = new PatientMaster();
		    $patientInfo = $pm->getTreatedDaysRealMultiple($ipids_final, false);
		    foreach($patients_final as $k_ipid => $v_patient)
		    {
		        if($v_patient['isdischarged'] == '1')
		        {
		            //get last discharge date
		            $dis_arr = end($patientInfo[$k_ipid]['dischargeDates']);
		            $v_patient['last_allowed_date'] = date('Y-m-d', strtotime($dis_arr['date']));
		        }
		        else
		        {
		            $v_patient['last_allowed_date'] = $end_date;
		        }
		    
		        if(
		            strtotime($v_patient['event_day']) <= strtotime($v_patient['last_allowed_date']) && strtotime($v_patient['admission_date']) < strtotime($v_patient['event_day']))
		        {
		            $result_data['WL_Anlage4_8Wochen'] = date("d.m.Y", strtotime($v_patient['event_day']));
		        }
		    }
		    
		    if(empty($result_data)){
		        $result_data['WL_Anlage4_8Wochen'] = "";
		    }
		    
		    return $result_data;
		}
		
		/**
		 * ISPC-1236 Lore 19.12.2019
		 * @param unknown $ipid
		 * @return string
		 */
		public function get_patient_religions($ipid)
		{
		    $patient_religions = PatientReligions::getReligionsData($ipid);
		    
		    if (!empty($patient_religions[0]['religion'])){
		        
		        $religions = PatientReligions::getReligionsNames();
		        
		        $patient_religions_arr['Religionszugehörigkeit'] = $religions[$patient_religions[0]['religion']];
 
		    }
		    else {
		        $patient_religions_arr['Religionszugehörigkeit'] = '';
		    }

		    return $patient_religions_arr;
		}
		
		/**
		 * ISPC-1236 Lore 13.05.2020
		 * @param  $ipid
		 * @return string
		 */
		public function get_patient_lives($ipid)
		{
		    $pat_lives = PatientLives::getpatientLivesData($ipid);
		    
		    $patient_lives_arr = array();
		    $patient_lives_arr['patient_wohnsituation'] = '';
		    
		    if (!empty($pat_lives)){

		        if($pat_lives[0]['alone'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', alleine' : 'alleine'); 
		        }
		        
		        if($pat_lives[0]['house_of_relatives'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', im Haus der Angehörigen' : 'im Haus der Angehörigen'); 
		        }
		        
		        if($pat_lives[0]['apartment'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', Wohnung' : 'Wohnung'); 
		        }
		        if($pat_lives[0]['home'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', Heim' : 'Heim'); 
		        }
		        if($pat_lives[0]['hospiz'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', Hospiz' : 'Hospiz'); 
		        }
		        
		        if($pat_lives[0]['sonstiges'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', Sonstige' : 'Sonstige'); 
		        }
		        if($pat_lives[0]['with_partner'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', mit Partner' : 'mit Partner'); 
		        }
		        if($pat_lives[0]['with_child'] == 1)
		        {
		            $patient_lives_arr['patient_wohnsituation'] = (!empty($patient_lives_arr['patient_wohnsituation']) ?  $patient_lives_arr['patient_wohnsituation'].', mit Kindern' : 'mit Kindern'); 
		        }
		    }
		    
		    return $patient_lives_arr;
		}
		
	}

?>