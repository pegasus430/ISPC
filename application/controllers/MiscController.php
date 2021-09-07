<?php

	class MiscController extends Pms_Controller_Action {

		public function init()
		{
		    array_push($this->actions_with_js_file, "uploadfiles");
			
		}

		public function symptomexportAction()
		{
			//get client & user data
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($_POST)
			{
				//1. Get all client patients
				$client_patients = $this->getAllClientPatients($clientid, $whereepid);

				$symperm = new SymptomatologyPermissions();
				$clientsymsets = $symperm->getClientSymptomatology($clientid);

				//2. Prepare "where in" array single level
				$client_ipids[] = '99999999';
				foreach($client_patients as $k_pat => $v_pat)
				{
					$patient_details[$v_pat['ipid']] = $v_pat;
					$client_ipids[] = $v_pat['ipid'];
				}

				$set_ids[] = '99999999999';
				foreach($clientsymsets as $k_set => $v_set)
				{
					$set_ids[] = $v_set['setid'];
				}

				//symptoms names
				$sv = new SymptomatologyValues();
				$sets_symptom_details = $sv->getSymptpomatologyValues($set_ids);

				//3. Get all symptomatology for all client users
				$symptomatology = Doctrine_Query::create()
					->select('*')
					->from('Symptomatology')
					->whereIn('ipid', $client_ipids)
					->andWhereIn('setid', $set_ids)
					->orderBy('symptomid,entry_date,id');

				$symptomatologys = $symptomatology->fetchArray();

				//4. Prepare symptoms export array
				foreach($symptomatologys as $k_sym => $v_sym)
				{
					$symptoms[$sets_symptom_details[$v_sym['symptomid']]['value']][$patient_details[$v_sym['ipid']]['EpidIpidMapping']['epid_num']][0] = strtoupper($patient_details[$v_sym['ipid']]['EpidIpidMapping']['epid']);
					$symptoms[$sets_symptom_details[$v_sym['symptomid']]['value']][$patient_details[$v_sym['ipid']]['EpidIpidMapping']['epid_num']][] = date('m/d/Y', strtotime($v_sym['entry_date']));
					$symptoms[$sets_symptom_details[$v_sym['symptomid']]['value']][$patient_details[$v_sym['ipid']]['EpidIpidMapping']['epid_num']][] = $v_sym['input_value'];
					$symptoms_data[$v_sym['symptomid']] = $sets_symptom_details[$v_sym['symptomid']]['value'];
					ksort($symptoms[$sets_symptom_details[$v_sym['symptomid']]['value']]);
				}

				if(!empty($_REQUEST['sid']))
				{
					$export_symptom_id = $_REQUEST['sid'];
				}
				else
				{
					$export_symptom_id = 'all';
				}
				$this->generateCSV($symptoms, 'export.xls', $symptoms_data, $export_symptom_id);
				exit;
			}
		}

		public function generateCSV($data, $filename = 'export.xls', $symptoms_data = false, $symptomid = false)
		{
			$file = fopen('php://output', 'w');

			foreach($data as $symptom => $patient_data)
			{
				//uncomment when exporting one symptom at a time
				if($symptomid && $symptoms_data && $symptomid != 'all' && $symptom == $symptoms_data[$symptomid])
				{
					fputcsv($file, array(''));
					fputcsv($file, array(strtoupper(utf8_encode($symptom))));

					foreach($patient_data as $epid => $values)
					{
						fputcsv($file, $values);
					}
				}
				else if($symptomid == 'all') //print all symptoms JUST FOR TESTING
				{
					fputcsv($file, array(''));
					fputcsv($file, array(strtoupper(utf8_encode($symptom))));

					foreach($patient_data as $epid => $values)
					{
						fputcsv($file, $values);
					}
				}
			}

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-dType: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $filename);
			exit;
		}

		function getAllClientPatients($clientid, $whereepid, $extra_details = false)
		{
			$actpatient = Doctrine_Query::create();
			if($extra_details)
			{
				$actpatient->select("p.ipid,e.epid,e.epid_num,AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
					AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name,");
			}
			else
			{
				$actpatient->select("p.ipid, e.epid, e.epid_num");
			}
			$actpatient->from('PatientMaster p');
			$actpatient->leftJoin("p.EpidIpidMapping e");
			$actpatient->where($whereepid . 'e.clientid = ' . $clientid);


			$actipidarray = $actpatient->fetchArray();

			return $actipidarray;
		}

		function sixweekslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			//		Load required models
			$modules = new Modules();
			$user = new User();

			$wl_client = $modules->checkModulePrivileges('51', $clientid);

			if(!$wl_client)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$assigned = false;
			$involved = false;

			if($_REQUEST['mode'] == 'assigned')
			{
				$assigned = true;
			}
			else if($_REQUEST['mode'] == 'involved')
			{
				$involved = true;
			}

			if($assigned)
			{
				//1.1 get user asigned patient
				$fdoc = Doctrine_Query::create()
					->select("*, e.epid, q.userid")
					->from('PatientQpaMapping q')
					->where("q.userid  = '" . $userid . "'")
					->andWhere("q.clientid  = '" . $clientid . "'")
					->andWhere('q.epid != ""');
				$fdoc->leftJoin('q.EpidIpidMapping e');
				$fdoc->andWhere('q.epid = e.epid');

				$doc_assigned_patients = $fdoc->fetchArray();

				$assigned_patients[] = '999999999';
				foreach($doc_assigned_patients as $k_asigned_pat => $v_asigned_pat)
				{
					$assigned_patients[] = $v_asigned_pat['ipid'];
				}

				$assigned_patients = array_values(array_unique($assigned_patients));
			}
			else
			{
				$assigned_patients[] = '99999999';
			}

			if($involved)
			{
				//1.2 get user involved patients (did verlauf on them)
				$inv_doc = Doctrine_Query::create()
					->select("*")
					->from('PatientCourse')
					->where('create_user = "' . $userid . '"')
					->orWhere('change_user = "' . $userid . '"')
					->orWhere('user_id = "' . $userid . '"')
					->andWhere('ipid != ""')
					->andWhere('ipid IS NOT NULL')
					->andWhere('source_ipid = ""');
				$doc_involved_patients = $inv_doc->fetchArray();

				$involved_patients[] = '999999999';
				foreach($doc_involved_patients as $k_inv_pat => $v_inv_pat)
				{
					if(!empty($v_inv_pat['ipid'])) //some verlauf entries have no ipid...!?
					{
						$involved_patients[] = $v_inv_pat['ipid'];
					}
				}

				$involved_patients = array_values(array_unique($involved_patients));
			}
			else
			{
				$involved_patients[] = '9999999999999';
			}

			//1.3 get client patients
			$client_patients = Doctrine_Query::create()
				->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
					AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
					ipid, admission_date, e.epid, e.clientid")
				->from('PatientMaster as p')
				->where('isdelete = 0')
				->andWhere('isdischarged = 0')
				->andWhere('isstandby = 0')
				->andWhere('isarchived = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere('admission_date < DATE(NOW())');
			$client_patients->leftJoin("p.EpidIpidMapping e");
			$client_patients->andWhere('e.ipid = p.ipid');

			if($assigned || $involved) // provide ipid set if is required to filter
			{
				$patients_ipids = array_merge($assigned_patients, $involved_patients);
				$client_patients->andWhereIn('ipid', $patients_ipids);
			}
			$client_patients->andWhere('e.clientid = "' . $clientid . '"');

			$all_client_patients = $client_patients->fetchArray();
		}

		public function patientimportAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$userid = $logininfo->userid;
			$uploads_dir = './uploads';
			$pm = new PatientMaster();

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				if(empty($_POST['f_name']))
				{
					//first step is loading the file and put it in upload directory for later use
					$filename = $uploads_dir . "/" . $_FILES['file']['name'];
					$this->view->file_name = $_FILES['file']['name'];

					move_uploaded_file($_FILES['file']['tmp_name'], $filename);
				}
				else
				{
					//second step filename is read from disk from upload directory
					$filename = $uploads_dir . '/' . $_POST['f_name'];
				}
 
				if(!$_POST['patient_index']) //first_step import file and parse + check if patients exists
				{

					$xml = simplexml_load_file($filename);

					if(empty($xml->patient))
					{
						$xml = array();
						$xml['patient'] = simplexml_load_file($filename);
					}

					$i = '0';
					foreach($xml as $k_pat => $v_patient)
					{
						if(!empty($v_patient->PatName->Vorname) && !empty($v_patient->PatName->Nachname) && !empty($v_patient->PatDetail->GebDat))
						{
							foreach($v_patient as $k_field => $v_field)
							{

								if($k_field != 'comment')
								{
									$xml_patients_details[$i][$k_field] = $v_field;
								}
							}
							$patients_verification[$i]['first_name'] = (string) trim($v_patient->PatName->Vorname);
							$patients_verification[$i]['last_name'] = (string) trim($v_patient->PatName->Nachname);
							$patients_verification[$i]['dob'] = date('Y-m-d', strtotime((string) $v_patient->PatDetail->GebDat));
						}
						else
						{
							$xml_incomplete_patients_details[$i] = $v_patient;
						}
						$i++;
					}

					$patients_found = $pm->check_patients_exists($patients_verification);
					if($patients_found)
					{
						$this->view->duplicates = count($patients_found);

						foreach($xml_patients_details as $k_patient => $v_patient)
						{
							if(array_key_exists($k_patient, $patients_found))
							{
								$xml_patients_details[$k_patient]['details_full'] = $patients_found[$k_patient];
							}
						}
					}

					$this->view->xml_patients = count($xml_patients_details);
					$this->view->xml_patients_data = $xml_patients_details;
					$this->view->xml_incomplete_patients_data = $xml_incomplete_patients_details;
				}
				else //second step
				{
					$xml_second = simplexml_load_file($filename);
					if(empty($xml_second->patient))
					{
						$xml_second_array = array();
						$xml_second_array['patient'][] = simplexml_load_file($filename);

						$patients_array = json_decode(json_encode($xml_second_array), true);
					}
					else
					{
						$patients_array = json_decode(json_encode($xml_second), true);
					}

					foreach($patients_array['patient'] as $k_pat_import => $v_pat_import)
					{
						if(in_array($k_pat_import, $_POST['patient_index']))
						{
							$import_array[$k_pat_import] = $v_pat_import;
						}
					}

					$import = $pm->import_patient($import_array);

					$this->view->imported_patients = $import;

					if($import)
					{
						$this->save_history($userid, $clientid, $xml_second->asXML(), $import);
					}
				}
			}
		}

		public function pricelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_addmission = new PriceAdmission();
			$p_daily = new PriceDaily();
			$p_visits = new PriceVisits();
			$p_sh_report = new PriceShReport();

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "misc/pricelist");
				}
			}
			else
			{
				$list_details = $p_lists->get_last_list($clientid);
				$list = $list_details[0]['id'];
			}

			$this->view->listid = $list;
			$this->view->shortcuts_admission = $shortcuts['admission'];
			$this->view->shortcuts_daily = $shortcuts['daily'];
			$this->view->shortcuts_visits = $shortcuts['visits'];

			if($_REQUEST['op'] == 'del' && $list)
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$form_list = new Application_Form_ClientPriceList();
				$delete_list = $form_list->delete_price_list($list);

				// ISPC-2312
				if($_REQUEST['redirect2new']==1){
    				$this->_redirect(APP_BASE . "misc/clientpricelist");
       				exit;
				}
				$this->_redirect(APP_BASE . "misc/pricelist");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$form = new Application_Form_ClientPriceList();
				if($form->validate_period($_POST))
				{
					if($_REQUEST['op'] == 'edit' && $_POST['edit_period'] == '1')
					{
						$returned_list_id = $form->edit_list($_POST, $list);
					}
					else
					{
						$returned_list_id = $form->save_price_list($_POST);

						if($returned_list_id)
						{
							$default_price_list = Pms_CommonData::get_default_price_shortcuts();

							foreach($default_price_list['performance'] as $k_price => $v_price)
							{
								$default_prices[$k_price]['price'] = $v_price;
							}

// 							$save_default_performance = $form->save_prices_performance($default_prices, $returned_list_id);
						}
					}

					$this->_redirect(APP_BASE . "misc/pricelist");
					exit;
				}
				else
				{
					$form->assignErrorMessages();
				}
			}

			$price_admissions = $p_addmission->get_prices($list, $clientid, $this->view->shortcuts_admission);
			$price_daily = $p_daily->get_prices($list, $clientid, $this->view->shortcuts_daily);
			$price_visits = $p_visits->get_prices($list, $clientid, $this->view->shortcuts_visits);
			$price_lists = $p_lists->get_lists($clientid);
			$price_sh_report = $p_sh_report->get_prices($list, $clientid, $this->view->shortcuts_sh_report);

			$this->view->price_list = $price_lists;
			$this->view->price_admissions = $price_admissions;
			$this->view->price_daily = $price_daily;
			$this->view->price_visits = $price_visits;
			$this->view->price_sh_report = $price_shreport;
		}

		public function pricelistdetailsAction()
		{ 
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_addmission = new PriceAdmission();
			$p_daily = new PriceDaily();
			$p_visits = new PriceVisits();
			$p_performance = new PricePerformance();
			$p_bra_sapv = new PriceBraSapv();
			$p_bra_sapv_weg = new PriceBraSapvWeg();
			$p_bre_sapv = new PriceBreSapv();
			$p_bre_dta = new PriceBreDta();
			$p_bre_hospiz = new PriceBreHospiz();
			$p_bayern_sapv = new PriceBayernSapv();
			$p_medipumps = new PriceMedipumps();
			$p_sgbxi = new PriceSgbxi();
			$p_bayern = new PriceBayern();
			$p_rp = new PriceRpInvoice();
			$p_sh = new PriceShInvoice();
			$p_sh_report = new PriceShReport();
			$p_sh_internal = new PriceShInternal();
			$p_sh_internal_user_shifts = new PriceShInternalUserShifts();
			$p_hospiz = new PriceHospiz();
			$p_care_level = new PriceCareLevel();

			$internal_price_user_groups = $p_sh_internal_user_shifts->internal_price_user_groups();
			$this->view->internal_price_user_groups = $internal_price_user_groups; 
			
			$medipumps = new Medipumps();
			$medipumpslist = $medipumps->getMedipumps($clientid);

		    $bw_location_types =  Pms_CommonData::get_default_bw_price_location_types();
			$this->view->bw_location_types = $bw_location_types;
			 
			$dta_locations = DtaLocations::get_client_dta_locations($clientid);
			$this->view->dta_locations = $dta_locations;

			foreach($medipumpslist as $key => $med)
			{
				$medipump_details[$med['id']]['id'] = $med['id'];
				$medipump_details[$med['id']]['medipump'] = $med['medipump'];
				$medipump_details[$med['id']]['shortcut'] = $med['shortcut'];
			}

			$this->view->medipumps_details = $medipump_details;

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current client;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}

			$this->view->listid = $list;
			$this->view->shortcuts_admission = $shortcuts['admission'];
			$this->view->shortcuts_daily = $shortcuts['daily'];
			$this->view->shortcuts_visits = $shortcuts['visits'];
			$this->view->shortcuts_performance = $shortcuts['performance'];
			$this->view->shortcuts_brasapv = $shortcuts['bra_sapv'];
			$this->view->shortcuts_bresapv = $shortcuts['bre_sapv'];
			$this->view->shortcuts_brehospiz = $shortcuts['bre_hospiz'];
			$this->view->shortcuts_bredta = $shortcuts['bre_dta'];
			$this->view->shortcuts_bayern_sapv = $shortcuts['bayern_sapv'];
			$this->view->shortcuts_sgbxi = $shortcuts['sgbxi'];
			$this->view->shortcuts_bayern = $shortcuts['bayern'];
			$this->view->shortcuts_rp = $shortcuts['rp'];
			$this->view->shortcuts_sh = $shortcuts['shanlage14'];
			$this->view->shortcuts_sh_report = $shortcuts['shanlage14_report'];
			$this->view->shortcuts_sh_internal = $shortcuts['sh_internal'];
			$this->view->shortcuts_sh_shifts_internal = $shortcuts['sh_shifts_internal'];
			$this->view->shortcuts_bra_sapv_weg = $shortcuts['bra_sapv_weg'];
			
			$this->view->shortcuts_hospiz = $shortcuts['hospiz'];
			$this->view->shortcuts_care_level = $shortcuts['care_level'];
			// Maria:: Migration ISPC to CISPC 08.08.2020
			$this->view->shortcuts_nranlage10 = $shortcuts['nr_anlage10'];

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				if($_POST['bayern_options'])
				{
					$bayern_invoice_settings = new Application_Form_BayernInvoiceSettings();
					$insert = $bayern_invoice_settings->insert_data($_POST['bayern_options'], $list, $clientid);
					unset($_POST['bayern_options']);
				}

				$form = new Application_Form_ClientPriceList();

				if($_POST['save_prices'])
				{
 
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelistdetails?list=" . $list);
					exit;
				}
			}

			$price_admissions = $p_addmission->get_prices($list, $clientid, $this->view->shortcuts_admission);
			$price_daily = $p_daily->get_prices($list, $clientid, $this->view->shortcuts_daily);
			$price_visits = $p_visits->get_prices($list, $clientid, $this->view->shortcuts_visits);
			$price_performance = $p_performance->get_prices($list, $clientid, $this->view->shortcuts_performance, $default_price_list['performance']);
			$price_performancebylocation = $p_performance->get_pricesbylocation_type($list, $clientid, $this->view->shortcuts_performance, $default_price_list['performancebylocation'],$bw_location_types);

			$price_bra_sapv = $p_bra_sapv->get_prices($list, $clientid, $this->view->shortcuts_brasapv, $default_price_list['bra_sapv']);
			$price_bra_sapv_weg = $p_bra_sapv_weg->get_prices($list, $clientid, $this->view->shortcuts_bra_sapv_weg, $default_price_list['bra_sapv_weg']);

			$price_bre_sapv = $p_bre_sapv->get_prices($list, $clientid, $this->view->shortcuts_bresapv);
			$price_bre_hospiz = $p_bre_hospiz->get_prices($list, $clientid, $this->view->shortcuts_brehospiz, $default_price_list['bre_hospiz']);
			$price_bre_dta = $p_bre_dta->get_prices($list, $clientid, $this->view->shortcuts_bredta);
			$price_bayern_sapv = $p_bayern_sapv->get_prices($list, $clientid, $this->view->shortcuts_bayern_sapv, $default_price_list['bayern_sapv']);
			$price_lists = $p_lists->get_lists($clientid);
			$price_medipumps = $p_medipumps->get_prices($list, $clientid, $medipump_details);
			$price_sgbxi = $p_sgbxi->get_prices($list, $clientid, $this->view->shortcuts_sgbxi, $default_price_list['sgbxi']);
			$price_bayern = $p_bayern->get_prices($list, $clientid, $this->view->shortcuts_bayern, $default_price_list['bayern']);
			$price_rp = $p_rp->get_prices($list, $clientid, $this->view->shortcuts_rp, $default_price_list['rp']);
			$price_sh = $p_sh->get_prices($list, $clientid, $this->view->shortcuts_sh, $default_price_list['sh']);
			$price_sh_report = $p_sh_report->get_prices($list, $clientid, $this->view->shortcuts_sh_report, $default_price_list['sh_report']);
			$price_sh_internal = $p_sh_internal->get_prices($list, $clientid, $this->view->shortcuts_sh_internal, $default_price_list['sh_internal']);
			$price_sh_internal_user_shifts = $p_sh_internal_user_shifts ->get_prices($list, $clientid, $this->view->shortcuts_sh_shifts_internal, $default_price_list['sh_shifts_internal']);
			
			$price_hospiz = $p_hospiz->get_prices($list, $clientid, $this->view->shortcuts_hospiz, $default_price_list['hospiz']);
			$price_care_level = $p_care_level->get_prices($list, $clientid, $this->view->shortcuts_care_level, $default_price_list['care_level']);
			$price_nr_anlage10 = PriceNordrheinAnlage10Table::findListsPrices($list, $clientid, $default_price_list['nr_anlage10'])[$list];
			
			$bayern_invoice = new BayernInvoiceSettings();
			$bayern_invoice_settings_arr = $bayern_invoice->get_list_invoice_settings($list, $clientid);

			$this->view->price_list = $price_lists;
			$this->view->price_admissions = $price_admissions;
			$this->view->price_daily = $price_daily;
			$this->view->price_visits = $price_visits;
			$this->view->price_performance = $price_performance;
			$this->view->price_performancebylocation = $price_performancebylocation;
			$this->view->price_medipumps = $price_medipumps;
			$this->view->price_bra_sapv = $price_bra_sapv;
			$this->view->price_bra_sapv_weg= $price_bra_sapv_weg;
			$this->view->price_bre_sapv = $price_bre_sapv;
			$this->view->price_bre_dta = $price_bre_dta;
			$this->view->price_bre_hospiz = $price_bre_hospiz;
			$this->view->price_bayern_sapv = $price_bayern_sapv;
			$this->view->price_sgbxi = $price_sgbxi;
			$this->view->price_bayern = $price_bayern;
			$this->view->bayern_invoice_settings = $bayern_invoice_settings_arr;
			$this->view->price_rp = $price_rp;
			$this->view->price_sh = $price_sh;
			$this->view->price_sh_report = $price_sh_report;
			$this->view->price_sh_internal = $price_sh_internal;
			$this->view->price_sh_internal_user_shifts = $price_sh_internal_user_shifts;
			
			$this->view->price_hospiz = $price_hospiz;
			$this->view->price_care_level = $price_care_level;
			$this->view->price_nr_anlage10 = $price_nr_anlage10;
		}

		public function pricelistrpdtaAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_rp = new PriceRpInvoice();
			$p_rp_dta = new PriceRpDta();


			$dta_locations = DtaLocations::get_client_dta_locations($clientid);
			$this->view->dta_locations = $dta_locations;

			
			
			$rp_location_types = array("5"=>"home","3"=> "pflege", "2"=> "hospiz");
			$rp_sapv_types = array("be","beko","tv","vv");

			$this->view->rp_location_types = $rp_location_types;
			$this->view->rp_sapv_types = $rp_sapv_types;
			
			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			
			
			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current client;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}

			$this->view->listid = $list;
 
			$this->view->shortcuts_rp = $shortcuts['rp'];
		 
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
 
				$form = new Application_Form_ClientPriceList();

				if($_POST['save_prices'])
				{

					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelistrpdta?list=" . $list);
					exit;
				}
			}

			$price_rp = $p_rp_dta->get_prices($list, $clientid, $this->view->shortcuts_rp, $rp_location_types, $rp_sapv_types);

			$this->view->price_rp = $price_rp;
		}
		
		public function pricelistrlpAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
	
			
			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
					
				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current client;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			
			
				$form = new Application_Form_ClientPriceList();
			
				if($_POST['save_prices'])
				{
			
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;
			
							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelistrlp?list=" . $list);
					exit;
				}
			}
			

	
			$p_rp = new RlpInvoices();
			$p_rp_dta = new PriceRlp();
			
			$data['products'] = $p_rp->rlp_products();
			$data['default_price_list'] = $p_rp->rlp_products_default_prices();
			$data['locations']= $p_rp->rlp_locations();
			
			// TODO-2058 p2 Ancuta 31.01.2019
			$data['client_products'] = RlpProductsTable::find_client_products($clientid);
			// --


			$data['price_list'] = $p_rp_dta->get_prices($list, $clientid);
// 			dd($data['price_list']);
			if( empty($data['price_list'])){
				$data['price_list'] = $data['default_price_list'];
			}
			
// 			dd($data);
			$this->view->data = $data;
			$this->view->listid = $list;
		}
		
		
		public function pricelistbrekinderAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
	
			
			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
					
				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current client;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			
			
				$form = new Application_Form_ClientPriceList();
			
				if($_POST['save_prices'])
				{
			
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;
			
							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelistbrekinder?list=" . $list);
					exit;
				}
			}
			

	        $invoice_type = "bre_kinder_invoice";   
			$invoice_settings = new InvoiceSystem();
			$invoice_settings_dta = new PriceBreKinder();
			
			$data['products'] = $invoice_settings->invoice_products($invoice_type);
			$data['default_price_list'] = $invoice_settings->invoice_products_default_prices($invoice_type);
			$data['locations']= $invoice_settings->invoice_locations_mapping($invoice_type);
			
		 


			$data['price_list'] = $invoice_settings_dta->get_prices($list, $clientid);
// 			dd($data['price_list']);
			if( empty($data['price_list'])){
				$data['price_list'] = $data['default_price_list'];
			}
			
// 			dd($data);
			$this->view->data = $data;
			$this->view->listid = $list;
		}
		
		
		/**
		 * Ancuta 06.12.2018
		 * ISPC-2286
		 */
		
		public function pricelistnordrheinAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
	
			
			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
					
				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current client;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			
			
				$form = new Application_Form_ClientPriceList();
 
				if($_POST['save_prices'])
				{
			
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;
			
							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelistnordrhein?list=" . $list);
					exit;
				}
			}
			

	        $invoice_type = "nr_invoice";   
			$invoice_settings = new InvoiceSystem();
			$invoice_settings_dta = new PriceNordrhein();
			
			$data['products'] = $invoice_settings->invoice_products($invoice_type);
			$data['default_price_list'] = $invoice_settings->invoice_products_default_prices($invoice_type);
			$data['locations']= $invoice_settings->invoice_locations_mapping($invoice_type);
			


			$data['price_list'] = $invoice_settings_dta->get_prices($list, $clientid);

			if( empty($data['price_list'])){
				$data['price_list'] = $data['default_price_list'];
			}
			
			$this->view->data = $data;
			$this->view->listid = $list;
		}

		public function pricelistmembershipsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_memberships = new PriceMemberships();


			// get memberships
			$client_memberships = Memberships::get_memberships($clientid);
			$this->view->memberships = $client_memberships;
			
				
			
			
			foreach($client_memberships as $key => $med)
			{
				$membership_details[$med['id']]['id'] = $med['id'];
				$membership_details[$med['id']]['membership'] = $med['membership'];
				$membership_details[$med['id']]['shortcut'] = $med['shortcut'];
			}

			$this->view->membership_details = $membership_details;

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current client;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}

			$this->view->listid = $list;

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
 
				$form = new Application_Form_ClientPriceList();
				if($_POST['save_prices'])
				{
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelist");
					exit;
				}
			}

			$price_lists = $p_lists->get_lists($clientid);
			$this->view->price_list = $price_lists;

			$price_memberships = $p_memberships->get_prices($list, $clientid, $membership_details);
			$this->view->price_memberships = $price_memberships;
		}

		public function priceformblocksAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_formblocks = new PriceFormBlocks();

			
			$blocks_settings = new FormBlocksSettings();
			$blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);
			
			foreach($blocks_settings_array as $key => $value)
			{
			    $settings_array[$value['block']][] = $value;
			}
			$blocks_master = array('ebm', 'ebmii', 'goa', 'goaii');
			
			foreach($blocks_master as $block)
			{
			    $form_blocks_details[$block] = $settings_array[$block];
			}
			
			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
				}
			}

			$this->view->listid = $list;
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$form = new Application_Form_ClientPriceList();

				if($_POST['save_prices'])
				{
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/priceformblocks?list=" . $list);
					exit;
				}
			}

			$price_form_blocks = $p_formblocks->get_prices($list, $clientid, $form_blocks_details, true);

			$price_lists = $p_lists->get_lists($clientid);
			$this->view->price_list = $price_lists;

			$this->view->price_form_blocks = $price_form_blocks;
			
            if($price_lists[$list]['start'] != "0000-00-00 00:00:00") {
                $start_date_price_list  = date("Y-m-d",strtotime($price_lists[$list]['start']));
                $period['start'] = date("Y-m-d",strtotime($price_lists[$list]['start']));
            }
            
            if($price_lists[$list]['end'] != "0000-00-00 00:00:00") {
                $end_date_price_list  = date("Y-m-d",strtotime($price_lists[$list]['end']));
                $period['end'] = date("Y-m-d",strtotime($price_lists[$list]['end']));
            }
            
			$blocks_settings = new FormBlocksSettings();
// 			$blocks_settings_array = $blocks_settings->get_blocks_settings($clientid,$start_date_price_list);
			$blocks_settings_array_pr = $blocks_settings->get_blocks_settings_pr($clientid,$period);
				
			foreach($blocks_settings_array_pr as $key => $value)
			{
			    $settings_array_pr[$value['block']][] = $value;
			}
			$blocks_master = array('ebm', 'ebmii', 'goa', 'goaii');
				
			foreach($blocks_master as $block)
			{
			    $form_blocks_details_pr[$block] = $settings_array_pr[$block];
			}
				
			$this->view->blocks_settings = $form_blocks_details_pr;
				
				
			
		}

		
		
		public function pricexbdtactionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();

			$p_xa = new PriceXbdtActions();

			$xbdtactions_m = new XbdtActions();
// 			$blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);

			$xbdtactions_array= $xbdtactions_m->client_xbdt_actions($clientid);
			
            foreach($xbdtactions_array as $kl=>$kaction){
                $action_list[$kaction['id']] = $kaction;
            }


			$this->view->actions_list = $action_list;

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
				}
			}

			$this->view->listid = $list;

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$form = new Application_Form_ClientPriceList();

				if($_POST['save_prices'])
				{
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricexbdtactions?list=" . $list);
					exit;
				}
			}

			$price_xa = $p_xa->get_prices($list, $clientid, $action_list, true);

			$price_lists = $p_lists->get_lists($clientid);
			$this->view->price_list = $price_lists;

			$this->view->price_xa = $price_xa;
		}

		public function nationalholidaysAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$regions = Pms_CommonData::getRegions();
			$this->view->regions = $regions;

			$nationalhd2state = Doctrine_Query::create()
				->select("s.*,n.*")
				->from('NationalHolidays2State s')
				->where("s.isdelete  = 0 ");
			$nationalhd2state->leftJoin('s.NationalHolidays n');
			$nationalhd2state->andWhere('s.holiday_id = n.id');
			$nationalhd2state->andWhere('n.isdelete = 0');
			$nationalhd2state_array = $nationalhd2state->fetchArray();

			foreach($nationalhd2state_array as $kh => $vnh)
			{
				$snational_holiday[$vnh['holiday_id']]['holiday_name'] = $vnh['NationalHolidays']['holiday'];
				$snational_holiday[$vnh['holiday_id']]['holiday_date'] = date('d.m.Y', strtotime($vnh['NationalHolidays']['date']));
				$snational_holiday[$vnh['holiday_id']]['holiday_states'][] = $vnh['state'];
			}

			$this->view->snational_holiday = $snational_holiday;

			if($this->getRequest()->isPost())
			{
				if(!empty($_POST['nholiday_del']))
				{
					// 				marck as deleted the holydays with Region
					$sph = Doctrine_Query::create()
						->update('NationalHolidays2State')
						->set('isdelete', '1')
						->where("holiday_id= ?", $_POST['nholiday_del']);
					$sph->execute();

					// 				marck as deleted the national holyday
					$ph = Doctrine_Query::create()
						->update('NationalHolidays')
						->set('isdelete', '1')
						->where("id= ?", $_POST['nholiday_del']);
					$ph->execute();
				}

				$this->_redirect(APP_BASE . "misc/nationalholidays");
			}
		}

		public function addnationalholidayAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$this->view->regions = Pms_CommonData::getRegions();

			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_NationalHoliday();

				if($form->validate($_POST))
				{
					$nh = new NationalHolidays();
					$nh->holiday = $_POST['holiday'];
					$nh->date = date('Y-m-d H:i:s', strtotime($_POST['date']));
					$nh->create_user = $userid;
					$nh->create_date = date('Y-m-d H:i:s');
					$nh->save();
					$holiday_id = $nh->id;

					foreach($_POST['region'] as $key => $region_id)
					{
						$nh2s = new NationalHolidays2State();
						$nh2s->holiday_id = $holiday_id;
						$nh2s->state = $region_id;
						$nh2s->create_user = $userid;
						$nh2s->create_date = date('Y-m-d H:i:s');
						$nh2s->save();
					}

					$this->_redirect(APP_BASE . "misc/nationalholidays");
				}
				else
				{
					$form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function editnationalholidayAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer('addnationalholiday');
			$this->view->regions = Pms_CommonData::getRegions();

			$holiday = $_REQUEST['holiday_id'];

			$nationalhd2state = Doctrine_Query::create()
				->select("s.*,n.*")
				->from('NationalHolidays2State s')
				->where("s.holiday_id = " . $holiday)
				->andWhere("s.isdelete  = 0 ");
			$nationalhd2state->leftJoin('s.NationalHolidays n');
			$nationalhd2state->andWhere('s.holiday_id = n.id');
			$nationalhd2state->andWhere('n.isdelete = 0');
			$nationalhd2state_array = $nationalhd2state->fetchArray();

			foreach($nationalhd2state_array as $kh => $vnh)
			{
				$snational_holiday['holiday'] = $vnh['NationalHolidays']['holiday'];
				$snational_holiday['date'] = date('d.m.Y', strtotime($vnh['NationalHolidays']['date']));
				$snational_holiday['holiday_regions'][] = $vnh['state'];
			}

			$this->view->holiday = $snational_holiday['holiday'];
			$this->view->date = $snational_holiday['date'];
			$holiday_regions = $snational_holiday['holiday_regions'];
			$this->view->region = $snational_holiday['holiday_regions'];

			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_NationalHoliday();

				if($form->validate($_POST))
				{
					$nh = Doctrine::getTable('NationalHolidays')->find($holiday);
					$nh->holiday = $_POST['holiday'];
					$nh->date = date('Y-m-d H:i:s', strtotime($_POST['date']));
					$nh->change_user = $userid;
					$nh->change_date = date('Y-m-d H:i:s');
					$nh->save();


					if(!empty($_POST['region']))
					{

						$ph = Doctrine_Query::create()
							->delete('NationalHolidays2State')
							->where("holiday_id='" . $holiday . "'");
						$ph->execute();

						foreach($_POST['region'] as $key => $region_id)
						{
							$nh2s = new NationalHolidays2State();
							$nh2s->holiday_id = $holiday;
							$nh2s->state = $region_id;
							$nh2s->create_user = $userid;
							$nh2s->create_date = date('Y-m-d H:i:s');
							$nh2s->save();
						}
					}

					$this->_redirect(APP_BASE . "misc/nationalholidays");
				}
				else
				{
					$form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function managedashboardlabelsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$dashboard_form = new Application_Form_Dashboard();
			$dashboard_labels = new DashboardLabels();

			$label_actions = Pms_CommonData::get_dashboard_actions();
			foreach($label_actions as $k_label => $v_label)
			{
				$labels_actions_array[$v_label] = $this->view->translate('name_' . $v_label);
			}
			$this->view->label_actions = $labels_actions_array;


			if($this->getRequest()->isPost())
			{
				if(strlen($_REQUEST['lid']) > 0)
				{
					//edit
					$del_label = $dashboard_form->delete_label($_REQUEST['lid']);
					$insert_label = $dashboard_form->add_label($_POST);
				}
				else
				{
					//add
					$insert_label = $dashboard_form->add_label($_POST);
				}
			}

			if(strlen($_REQUEST['lid']) > 0)
			{
				if($_REQUEST['op'] == 'del')
				{
					$del_label = $dashboard_form->delete_label($_REQUEST['lid']);

					$this->_redirect(APP_BASE . 'misc/managedashboardlabels');
					exit;
				}
				else if($_REQUEST['op'] == 'edit')
				{
					$label_details = $dashboard_labels->getLabel($_REQUEST['lid']);
					$this->view->label_name = $label_details[0]['name'];
					$this->view->color = $label_details[0]['color'];
					$this->view->font_color = $label_details[0]['font_color'];
					$this->view->action = $label_details[0]['action'];
				}
			}

			$all_labels_details = $dashboard_labels->getClientLabels();
			$this->view->labels = $all_labels_details;
		}

		public function sapvactivitygridAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$pm = new PatientMaster();
			

			//Check patient permissions on controller and action
			$patient_privileges = PatientPermissions::checkPermissionOnRun();
			if(!$patient_privileges)
			{
				$this->_redirect(APP_BASE . 'error/previlege');
			}
			
			/*			 * ******************************************* */
			$this->view->patientinfo = $pm->getMasterData($decid, 1);
			$patient_details = $pm->getMasterData($decid, 0);
			$this->view->patientdetails = $patient_details;
			$pdf_data['patientdetails'] = $patient_details;

			$ph = new PatientHealthInsurance();
			$phi = $ph->getPatientHealthInsurance($ipid);

			$this->view->company_name = $phi[0]['company_name'];
			$pdf_data['company_name'] = $phi[0]['company_name'];

			$this->view->insurance_no = $phi[0]['insurance_no'];
			$pdf_data['insurance_no'] = $phi[0]['insurance_no'];

			$sql = "*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,";
			$sql .= "AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .= "AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .= "AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,";
			$sql .= "AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .= "AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,";
			$sql .= "AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,";
			$sql .= "AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,";
			$sql .= "AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .= "AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax,";
			$sql .= "AES_DECRYPT(institutskennzeichen,'" . Zend_Registry::get('salt') . "') as institutskennzeichen,";
			$sql .= "AES_DECRYPT(betriebsstattennummer,'" . Zend_Registry::get('salt') . "') as betriebsstattennummer,";
			$sql .= "AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment";

			$cust = Doctrine_Query::create()
				->select($sql)
				->from('Client')
				->where('id = ' . $clientid);
			$clientarray = $cust->fetchArray();

			if($clientarray)
			{
				$this->view->iknumber = $clientarray[0]['institutskennzeichen'];
				$this->view->teamname = $clientarray[0]['team_name'];
				$this->view->address = $clientarray[0]['street1'] . ", " . $clientarray[0]['street2'] . "," . $clientarray[0]['city'] . "," . $clientarray[0]['postcode'];
				$this->view->phone = $clientarray[0]['phone'];

				$pdf_data['iknumber'] = $clientarray[0]['institutskennzeichen'];
				$pdf_data['teamname'] = $clientarray[0]['team_name'];
				$pdf_data['address'] = $clientarray[0]['street1'] . ", " . $clientarray[0]['street2'] . "," . $clientarray[0]['city'] . "," . $clientarray[0]['postcode'];
				$pdf_data['phone'] = $clientarray[0]['phone'];
			}

			//get pflegestufe
			$pms = new PatientMaintainanceStage();
			$pat_pms = $pms->getLastpatientMaintainanceStage($ipid);

			if($pat_pms[0]['stage'] > 0 && !empty($pat_pms[0]['stage']) && $pat_pms[0]['stage'] > 0)
			{
				$this->view->pfl_stgage = $pat_pms[0]['stage'];
				$pdf_data['pfl_stgage'] = $pat_pms[0]['stage'];
			}
			else
			{
				$pdf_data['pfl_stgage'] = "--";
			}

			$tm = new TabMenus();
			$this->view->tabmenus = $tm->getMenuTabs();
			/* ######################################################### */
			//construct months array
			$start_period = '2010-01-01';
			$end_period = date('Y-m-d', time());
			$period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');

			foreach($period_months_array as $k_month => $v_month)
			{
				$month_select_array[$v_month] = $v_month;
			}

			if(count($month_select_array) == 0)
			{
				$month_select_array['99999999'] = '';
			}

			//see how many days in selected month
			if(empty($_REQUEST['list']) && strlen($list) == 0)
			{
				$selected_month = end($month_select_array);
			}
			else
			{
				if(strlen($list) == 0)
				{
					$list = $_REQUEST['list'];
				}
				$selected_month = $month_select_array[$list];
			}

			$this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
			$pdf_data['month_selected'] = date('m.Y', strtotime($selected_month . '-01'));

			if(!function_exists('cal_days_in_month'))
			{
				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
			}
			else
			{
				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
			}

			//construct selected month array (start, days, end, month, year)
			$months_details[$selected_month]['start'] = $selected_month . "-01";
			$months_details[$selected_month]['days_in_month'] = $month_days;
			$months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
			$months_details[$selected_month]['year'] = date('Y', strtotime($selected_month . "-01"));
			$months_details[$selected_month]['month'] = date('m', strtotime($selected_month . "-01"));

			$attrs['onChange'] = 'changeMonth(this.value);';
			$attrs['class'] = 'select_month_sapvgrid';

			krsort($month_select_array);
			$this->view->months_selector = $this->view->formSelect("select_month", $selected_month, $attrs, $month_select_array);

			//set current period to work with
			$current_period = $months_details[$selected_month];

			//construct master days array
			$month_days = $pm->getDaysInBetween($current_period['start'], $current_period['end']);

			foreach($month_days as $k_mdays => $v_mdays)
			{
				$master_month_days[$v_mdays] = array();
			}

			//1. Doctor Visits + Verlauf exclusion
			$exclude_doc_visits = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid ="' . $ipid . '"')
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form'")
				->andWhere('source_ipid = ""')
				->orderBy('course_date ASC');
			$doc_visits_exclusion = $exclude_doc_visits->fetchArray();

			$exclusion_doc_ids[] = '99999999999';
			foreach($doc_visits_exclusion as $k_doc_ex => $v_doc_ex)
			{
				$exclusion_doc_ids[] = $v_doc_ex['recordid'];
			}

			$doc_visits = Doctrine_Query::create()
				->select("*")
				->from("KvnoDoctor")
				->where('ipid LIKE "' . $ipid . '" ')
				->andWhere('YEAR(`vizit_date`) = "' . $current_period['year'] . '"')
				->andWhere('MONTH(`vizit_date`) = "' . $current_period['month'] . '"')
				->andWhereNotIn('id', $exclusion_doc_ids);

			$doctorvisit = $doc_visits->fetchArray();
			foreach($doctorvisit as $k_doc_v => $v_doc_v)
			{
				$visit_date = date('Y-m-d', strtotime($v_doc_v['vizit_date']));

				$master_month_days[$visit_date]['doctor_visit'][] = '1';
			}

			//		2. Nurse Visits + Verlauf exclusion
			$exclude_nurse_visits = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid ="' . $ipid . '"')
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form'")
				->andWhere('source_ipid = ""')
				->orderBy('course_date ASC');
			$nurse_visits_exclusion = $exclude_nurse_visits->fetchArray();

			$exclusion_nurse_ids[] = '99999999999';
			foreach($nurse_visits_exclusion as $k_nurse_ex => $v_nurse_ex)
			{
				$exclusion_nurse_ids[] = $v_nurse_ex['recordid'];
			}

			$nurse_visits = Doctrine_Query::create()
				->select("*")
				->from("KvnoNurse")
				->where('ipid LIKE "' . $ipid . '" ')
				->andWhere('YEAR(`vizit_date`) = "' . $current_period['year'] . '"')
				->andWhere('MONTH(`vizit_date`) = "' . $current_period['month'] . '"')
				->andWhereNotIn('id', $exclusion_nurse_ids);

			$nursevisit = $nurse_visits->fetchArray();

			foreach($nursevisit as $k_nurse_v => $v_nurse_v)
			{
				$n_visit_date = date('Y-m-d', strtotime($v_nurse_v['vizit_date']));
				$master_month_days[$n_visit_date]['nurse_visit'][] = '1';
			}

			//3. Koordination Visits + Verlauf Exclusion
			$exclude_koord_visits = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid ="' . $ipid . '"')
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'visit_koordination_form'")
				->andWhere('source_ipid = ""')
				->orderBy('course_date ASC');
			$koord_visits_exclusion = $exclude_koord_visits->fetchArray();

			$exclusion_koord_ids[] = '99999999999';
			foreach($koord_visits_exclusion as $k_koord_ex => $v_koord_ex)
			{
				$exclusion_koord_ids[] = $v_koord_ex['recordid'];
			}

			$koord_visits = Doctrine_Query::create()
				->select("*")
				->from("VisitKoordination")
				->where('ipid LIKE "' . $ipid . '" ')
				->andWhere('YEAR(`visit_date`) = "' . $current_period['year'] . '"')
				->andWhere('MONTH(`visit_date`) = "' . $current_period['month'] . '"')
				->andWhereNotIn('id', $exclusion_koord_ids);

			$koordvisit = $koord_visits->fetchArray();

			foreach($koordvisit as $k_koord_v => $v_koord_v)
			{
				$k_visit_date = date('Y-m-d', strtotime($v_koord_v['visit_date']));
				$master_month_days[$k_visit_date]['koord_visit'][] = '1';
			}
			//4. Verlauf shortcuts U(Beratung) + V(Koordination)
			$courses = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
				->from('PatientCourse')
				->where('ipid ="' . $ipid . '"')
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'U' OR AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'V'")
				->andWhere("wrong = 0")
				->andWhere('YEAR(`done_date`) = "' . $current_period['year'] . '"')
				->andWhere('MONTH(`done_date`) = "' . $current_period['month'] . '"')
				->andWhere('source_ipid = ""')
				->orderBy('course_date ASC');
			$courses_res = $courses->fetchArray();

			foreach($courses_res as $k_course => $v_course)
			{
				$course_date = date('Y-m-d', strtotime($v_course['done_date']));
				$master_month_days[$course_date]['verlauf_' . $v_course['course_type']][] = '1';
			}

			$this->view->master_month_days = $master_month_days;
			$pdf_data['master_month_days'] = $master_month_days;

			if($this->getRequest()->isPost())
			{
				$this->generateformPdf('3', $pdf_data, 'sapvactivitygrid', 'sapvactivitygrid_pdf.html');
			}
		}

		public function sapvbulkexportAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$sapv_reeval = new SapvReevaluation();
			$pm = new PatientMaster();


			if($this->getRequest()->isPost())
			{
				if($_POST['f'] == '1')
				{
					if($_REQUEST['start_date'])
					{
						$period['start_date'] = date('Y-m-d', strtotime($_REQUEST['start_date']));
					}

					if($_REQUEST['end_date'])
					{
						$period['end_date'] = date('Y-m-d', strtotime($_REQUEST['end_date']));
					}
					else
					{
						$period['end_date'] = date('Y-m-d', time()); //now
					}

					$period_days = $pm->getDaysInBetween($period['start_date'], $period['end_date']);
					$period['days_period'] = $period_days;

					//get client patients
					$client_pats = Doctrine_Query::create()
						->select("p.ipid, e.epid")
						->from('PatientMaster p')
						->Where('isdelete = 0')
						->andWhere('isstandbydelete = 0');
					$client_pats->leftJoin("p.EpidIpidMapping e");
					$client_pats->andWhere('e.clientid = ' . $clientid);
					$client_patients = $client_pats->fetchArray();

					$client_ipids[] = '9999999999999';
					foreach($client_patients as $k_client_pat => $v_client_pat)
					{
						$client_ipids[] = $v_client_pat['ipid'];
					}

					$sapv_reeval_export = $sapv_reeval->get_export_ready_sapv($clientid, $client_ipids, $period);
				}
				else if($_POST['f'] == '0')
				{
					ob_flush();
					ob_clean();
					$sapv_xml_data = $this->export_multiple_sapv_xml($_POST['userid']);

					//send xml to be downloaded
					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-type: text/xml; charset=utf-8");
					header("Content-Disposition: attachment; filename=sapv_bulk_export-" . date('d-m-Y_H-i-s') . ".xml");
					echo $sapv_xml_data;
					exit;
				}
			}

			//		DONE 02.07.2013
			$export_history = new SapvExportHistory();
			$history_data = $export_history->get_sapv_export_history($clientid);

			$history_ipids[] = '99999999999';
			foreach($history_data as $k_history => $v_history)
			{
				$history_ipids[] = $v_history['ipid'];
			}

			if(!$sapv_reeval_export && $_POST['f'] != '1')
			{
				$sapv_reeval_export = $sapv_reeval->get_export_ready_sapv($clientid, false, false);
			}

			$export_pat_ipids[] = '99999999999';
			$allready_exported_ipids[] = '99999999999';
			$all_sapv_reeval_ipids[] = '99999999999';

			foreach($sapv_reeval_export as $k_sapv_reeval => $v_sapv_reeval)
			{
				if(!in_array($v_sapv_reeval['ipid'], $history_ipids))
				{
					$export_pat_ipids[] = $v_sapv_reeval['ipid'];
				}
				else
				{
					$allready_exported_ipids[] = $v_sapv_reeval['ipid'];
				}
				$all_sapv_reeval_ipids[] = $v_sapv_reeval['ipid'];

				if(date('Y-m-d', strtotime($sapv_reeval_export[$k_sapv_reeval]['beginSapvFall'])) == '1970-01-01')
				{
					$sapv_reeval_export[$k_sapv_reeval]['beginSapvFall'] = ' - ';
				}

				if(date('Y-m-d', strtotime($sapv_reeval_export[$k_sapv_reeval]['endSapvFall'])) == '1970-01-01')
				{
					$sapv_reeval_export[$k_sapv_reeval]['endSapvFall'] = ' - ';
				}
			}

			$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.traffic_status,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, p.birthd,p.admission_date,p.change_date,p.isadminvisible,p.traffic_status,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$pmaster = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where('p.isdelete = "0"')
				->andWhereIn('p.ipid', $all_sapv_reeval_ipids);
			$pmaster_res = $pmaster->fetchArray();

			$pmaster_allowed_ipids[] = '99999999999';
			foreach($pmaster_res as $k_pmaster => $v_pmaster)
			{
				$pmaster_resources[$v_pmaster['ipid']] = $v_pmaster;
				$pmaster_allowed_ipids[] = $v_pmaster['ipid'];
			}

			$this->view->patients_details = $pmaster_resources;

			//get discharge dates of alowed patients START
			//TODO here check what is wrong with patient discharge
			$dis_allowed_patients = Doctrine_Query::create()
				->select('*')
				->from('PatientDischarge')
				->where('isdelete = "0"')
				->andWhereIn('ipid', $pmaster_allowed_ipids);
			$dis_allowed_patients_res = $dis_allowed_patients->fetchArray();

			foreach($dis_allowed_patients_res as $k_dis_allow_patient => $v_dis_allow_patient)
			{
				$discharged_patients_details[$v_dis_allow_patient['ipid']]['discharge_date'][] = date('Y-m-d', strtotime($v_dis_allow_patient['discharge_date']));
			}

			$this->view->discharge_details = $discharged_patients_details;
			//get discharge dates of alowed patients END

			foreach($sapv_reeval_export as $k_sre => $v_sre)
			{
				if(in_array($v_sre['ipid'], $pmaster_allowed_ipids) && !in_array($v_sre['ipid'], $allready_exported_ipids))
				{
					$allowed_sapv_reeval_export[] = $v_sre;
				}
				elseif(in_array($v_sre['ipid'], $pmaster_allowed_ipids) && in_array($v_sre['ipid'], $allready_exported_ipids))
				{
					$history_allowed_sapv_reeval_export[] = $v_sre;
				}
			}

			if($_REQUEST['dbg'])
			{
				print_r("allowed_sapv_reeval_export\n");
				print_r($allowed_sapv_reeval_export);

				print_r("history_allowed_sapv_reeval_export\n");
				print_r($history_allowed_sapv_reeval_export);
				exit;
			}

			$this->view->sapv_reeval_export = $allowed_sapv_reeval_export;
			$this->view->sapv_reeval_exported = $history_allowed_sapv_reeval_export;
		}

		public function export_multiple_sapv_xml($patient_ids)
		{
			//get patient master data
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			//get patient decid to pid
			$decid_arr[] = '9999999999999';
			foreach($patient_ids as $k_pid => $v_pid)
			{
				$dec_id = Pms_Uuid::decrypt($v_pid);

				$patients_data[$v_pid]['decid'] = $dec_id;
				$decid_arr[] = $dec_id;
			}

			//get patients ipids based on pids
			$pat_details = Doctrine_Query::create()
				->select('id, ipid, birthd')
				->from('PatientMaster')
				->whereIn('id', $decid_arr);
			$pat_details_res = $pat_details->fetchArray();

			$selected_patients_ipids[] = '9999999999';
			foreach($pat_details_res as $k_det_res => $v_det_res)
			{
				$patientinfo[$v_det_res['ipid']] = $v_det_res;
				$selected_patients_ipids[] = $v_det_res['ipid'];
			}

			//get patients sapvreeval data based on ipids
			$sapv_reeval = new SapvReevaluation();
			$forms_data = $sapv_reeval->get_multiple_sapv_reeval($selected_patients_ipids);

			//mapped arrays
			$gender_arr = array('1' => 'männlich', '2' => 'weiblich');
			$verordnet_map = array('1' => 'Beratung', '2' => 'Koordination', '3' => 'Teilversorgung', '4' => 'Vollversorgung');
			$wohn_map = array('1' => 'zu hause, allein', '2' => 'zu Hause mit Angehörigen', '3' => 'im stat.Hospiz', '4' => 'in stat.Pflegeeinrichtung');
			$deathwish_map = array('1' => 'ja', '2' => 'nein', '3' => 'unbekannt/unbestimmt');
			$sapv_expect_map = array('1' => 'ja', '2' => 'nein', '3' => 'teilweise', '4' => 'unbekannt');
			$sapv_dead_map = array('1' => 'im häuslichen Umfeld verstorben', '2' => 'auf Palliativstation verstorben', '3' => 'im Krankenhaus verstorben', '4' => 'im Heim verstorben', '5' => 'im Hospiz verstorben');
			$wunsch_patient = array('ja', 'nein', 'unbekannt');

			//get HD client diagnosis type id
			$dg = new DiagnosisType();
			$patdia = new PatientDiagnosis();

			$hd_ids = $dg->getDiagnosisTypes($clientid, "'HD'");

			$typeid[] = '99999999999';
			foreach($hd_ids as $key => $valdia)
			{
				$typeid[] = $valdia['id'];
			}
			$dianoarray = $patdia->get_multiple_finaldata($selected_patients_ipids, $typeid);

			if(count($dianoarray) > 0)
			{
				foreach($dianoarray as $key => $valdia)
				{
					if(strlen($valdia['diagnosis']) > 0 && in_array($valdia['icdnumber'], explode(',', $forms_data[$valdia['ipid']]['icddiagnosis'])))
					{
						$diagnosis[$valdia['ipid']][] = $valdia['icdnumber'];
						$diagnosis_label[$valdia['ipid']][] = $valdia['diagnosis'];
					}
				}
			}

			//get ND client diagnosis type id
			$nd_ids = $dg->getDiagnosisTypes($clientid, "'ND'");

			$typeid_nd[] = '99999999999';
			foreach($nd_ids as $key => $valdia)
			{
				$typeid_nd[] = $valdia['id'];
			}

			$dianoarray_nd = $patdia->get_multiple_finaldata($selected_patients_ipids, $typeid_nd);

			if(count($dianoarray_nd) > 0)
			{
				foreach($dianoarray_nd as $key => $valdia)
				{
					if(strlen($valdia['diagnosis']) > 0 && in_array($valdia['icdnumber'], explode(',', $forms_data[$valdia['ipid']]['icdNDdiagnosis'])))
					{
						$diagnosis_nd[$valdia['ipid']][$valdia['hidd_diagnosis']] = $valdia['icdnumber'];
						$diagnosis_nd_label[$valdia['ipid']][$valdia['hidd_diagnosis']] = $valdia['diagnosis'];
					}
				}
			}

			//first and last name of user who is exporting
			$user = new User();
			$user_details = $user->getUserDetails($logininfo->userid);

			foreach($forms_data as $k_form_ipid => $v_form_data)
			{
				$xml_export_data[$k_form_ipid]['e_pk'] = ''; //blank
				$xml_export_data[$k_form_ipid]['e_status'] = ''; //blank
				$xml_export_data[$k_form_ipid]['e_patienten_id'] = strtoupper($v_form_data['epid']); //blank
				$xml_export_data[$k_form_ipid]['e_ersteller_titel'] = ''; //blank

				$xml_export_data[$k_form_ipid]['e_ersteller_vorname'] = $user_details[0]['last_name'];
				$xml_export_data[$k_form_ipid]['e_ersteller_nachname'] = $user_details[0]['first_name'];
				$xml_export_data[$k_form_ipid]['e_erstellt_am'] = date('Y-m-d');

				$xml_export_data[$k_form_ipid]['e_bearbeiter_titel'] = ''; //blank
				$xml_export_data[$k_form_ipid]['e_bearbeiter_vorname'] = ''; //blank
				$xml_export_data[$k_form_ipid]['e_bearbeiter_nachname'] = ''; //blank
				$xml_export_data[$k_form_ipid]['e_bearbeitet_am'] = date('Y-m-d'); //blank
				//version
				$xml_export_data[$k_form_ipid]['e_formular'] = 'Vers 2012';
				$xml_export_data[$k_form_ipid]['e_geburtsdatum'] = '';

				//health insurance matching
				$patient_health_insu = trim(mb_strtolower($v_form_data['hi_company_name'], 'UTF-8'));


				$handler = fopen(APPLICATION_PATH . "/../public/sapv2012/s_kostentraeger.csv", "r");

				if($handler) //avoid huge loading if file not exists
				{
					while(($data = fgetcsv($handler, '500', ';', '"')) !== FALSE)
					{
						$csv_data_arr[$data[0]] = trim(mb_strtolower($data[1], 'UTF-8'));
					}
					fclose($handler);
				}


				$health_insu_match = array_search($patient_health_insu, $csv_data_arr);

				if($health_insu_match)
				{
					//				http://smart-q.taskshere.com/project/718/ new: https://smartq.atlassian.net/browse/ISPC-40
					$xml_export_data[$k_form_ipid]['e_kostentraeger_fk'] = $health_insu_match;
				}
				else
				{
					$xml_export_data[$k_form_ipid]['e_kostentraeger_fk'] = '--';
				}
				//health insurance matching end

				$xml_export_data[$k_form_ipid]['ev_pk'] = '';
				$xml_export_data[$k_form_ipid]['eb_geburtsdatum'] = $patientinfo['birthd'];
				$xml_export_data[$k_form_ipid]['eb_alter'] = $v_form_data['age'];
				$xml_export_data[$k_form_ipid]['eb_geschlecht'] = $gender_arr[$v_form_data['gender']];
				$xml_export_data[$k_form_ipid]['eb_beginn'] = date('d.m.Y', strtotime($v_form_data['beginSapvFall']));
				$xml_export_data[$k_form_ipid]['eb_kostentraeger'] = $v_form_data['hi_company_name'];
				$xml_export_data[$k_form_ipid]['eb_grundkrankheit'] = implode(', ', $diagnosis[$k_form_ipid]);
				$xml_export_data[$k_form_ipid]['eb_grundkrankheit_name'] = implode(', ', $diagnosis_label[$k_form_ipid]);

				if(!empty($v_form_data['firstSapvMaxbe']))
				{
					$verordnet[$k_form_ipid][] = $verordnet_map[$v_form_data['firstSapvMaxbe']];
				}

				if(!empty($v_form_data['firstSapvMaxko']))
				{
					$verordnet[$k_form_ipid][] = $verordnet_map[$v_form_data['firstSapvMaxko']];
				}

				if(!empty($v_form_data['firstSapvMaxtv']))
				{
					$verordnet[$k_form_ipid][] = $verordnet_map[$v_form_data['firstSapvMaxtv']];
				}

				if(!empty($v_form_data['firstSapvMaxvv']))
				{
					$verordnet[$k_form_ipid][] = $verordnet_map[$v_form_data['firstSapvMaxvv']];
				}

				$xml_export_data[$k_form_ipid]['eb_verordnet'] = implode(', ', $verordnet[$k_form_ipid]);



				if(!empty($v_form_data['alone']))
				{
					$wohn[$k_form_ipid][] = $wohn_map[1];
				}

				if(!empty($v_form_data['house_of_relatives']))
				{
					$wohn[$k_form_ipid][] = $wohn_map[2];
				}

				if(!empty($v_form_data['hospiz']))
				{
					$wohn[$k_form_ipid][] = $wohn_map[3];
				}

				if(!empty($v_form_data['nursingfacility']))
				{
					$wohn[$k_form_ipid][] = $wohn_map[4];
				}

				$xml_export_data[$k_form_ipid]['eb_wohnsituation'] = implode(', ', $wohn[$k_form_ipid]);


				if(!empty($v_form_data['curentlivingmore']))
				{
					$xml_export_data[$k_form_ipid]['eb_wohnsituation_text'] = $v_form_data['curentlivingmore'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['eb_wohnsituation_text'] = '';
				}

				if(!empty($v_form_data['stagekeine']))
				{
					$pflegestufe[$k_form_ipid][] = 'keine';
				}

				if(!empty($v_form_data['stageone']))
				{
					$pflegestufe[$k_form_ipid][] = $v_form_data['stageone'];
				}

				if(!empty($v_form_data['stagetwo']))
				{
					$pflegestufe[$k_form_ipid][] = $v_form_data['stagetwo'];
				}

				if(!empty($v_form_data['stagethree']))
				{
					$pflegestufe[$k_form_ipid][] = $v_form_data['stagethree'];
				}

				$xml_export_data[$k_form_ipid]['eb_pflegestufe'] = implode(', ', $pflegestufe[$k_form_ipid]);

				if(!empty($v_form_data['beantragt']))
				{
					$pflegestufe_antrag[$k_form_ipid][] = 'beantragt';
				}

				if(!empty($v_form_data['nbeantragt']))
				{
					$pflegestufe_antrag[$k_form_ipid][] = 'nicht beantragt';
				}

				$xml_export_data[$k_form_ipid]['eb_pflegestufe_antrag'] = implode(', ', $pflegestufe_antrag[$k_form_ipid]);


				$xml_export_data[$k_form_ipid]['eb_wunsch_patienten_sapv'] = $wunsch_patient[$v_form_data['deathwishja']]; // "ja" / "nein" / "unbekannt" taken from "Sterbeort n. Wunsch:"

				if(count($diagnosis_nd[$k_form_ipid]) > 0)
				{
					$incr = '1';
					foreach($diagnosis_nd[$k_form_ipid] as $key_arr => $ndiag)
					{
						if($incr < '5')
						{
							$keys_s = 'ed_nebendiagnose_' . $incr;
							$xml_export_data[$k_form_ipid][$keys_s] = $ndiag . ', ' . $diagnosis_nd_label[$k_form_ipid][$key_arr];
							$incr++;
						}
					}
				}

				if(!empty($v_form_data['stabilization']))
				{
					$xml_export_data[$k_form_ipid]['ee_ende_stabilisierung'] = 'Stabilisierung';
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_ende_stabilisierung'] = '';
				}

				if(!empty($v_form_data['causaltherapy']))
				{
					$xml_export_data[$k_form_ipid]['ee_ende_therapieansatz'] = 'Kausaler Therapieansatz';
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_ende_therapieansatz'] = '';
				}

				if(!empty($v_form_data['regulationexpiration']))
				{
					$xml_export_data[$k_form_ipid]['ee_ende_ablauf_verordnung'] = 'Ablauf der Verordnung';
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_ende_ablauf_verordnung'] = '';
				}


				if(!empty($v_form_data['laying']))
				{
					$ende[$k_form_ipid][] = $ende_map[4];
				}

				if(!empty($v_form_data['deceased']))
				{
					$xml_export_data[$k_form_ipid]['ee_ende_verstorben'] = 'verstorben';
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_ende_verstorben'] = '';
				}

				if(!empty($v_form_data['noneedsapv']))
				{
					$ende[$k_form_ipid][] = $ende_map[6];
				}

				if(!empty($v_form_data['sapvterminationother']))
				{
					$xml_export_data[$k_form_ipid]['ee_ende_sonstiges'] = 'Sonstiges';
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_ende_sonstiges'] = '';
				}

				$xml_export_data[$k_form_ipid]['ee_ende_sonstiges_text'] = ''; //blank


				if(!empty($v_form_data['stagelastkeine']))
				{
					$pflegestufe_last[$k_form_ipid][] = 'keine';
				}

				if(!empty($v_form_data['stagelastone']))
				{
					$pflegestufe_last[$k_form_ipid][] = $v_form_data['stagelastone'];
				}

				if(!empty($v_form_data['stagelasttwo']))
				{
					$pflegestufe_last[$k_form_ipid][] = $v_form_data['stagelasttwo'];
				}

				if(!empty($v_form_data['stagelastthree']))
				{
					$pflegestufe_last[$k_form_ipid][] = $v_form_data['stagelastthree'];
				}

				$xml_export_data[$k_form_ipid]['ee_pflegestufe_abschluss'] = implode(', ', $pflegestufe_last[$k_form_ipid]);


				if(!empty($v_form_data['lastbeantragt']))
				{
					$pflegestufe_last_antrag[$k_form_ipid][] = 'beantragt';
				}

				if(!empty($v_form_data['nlastbeantragt']))
				{
					$pflegestufe_last_antrag[$k_form_ipid][] = 'nicht beantragt';
				}

				$xml_export_data[$k_form_ipid]['ee_pflegestufe_antrag'] = implode(', ', $pflegestufe_last_antrag[$k_form_ipid]);

				//sapv death start
				if(!empty($v_form_data['homedead']))
				{
					$sapv_dead[$k_form_ipid][] = $sapv_dead_map[$v_form_data['homedead']];
				}

				if(!empty($v_form_data['heimdead']))
				{
					$sapv_dead[$k_form_ipid][] = $sapv_dead_map[$v_form_data['heimdead']];
				}

				if(!empty($v_form_data['hospizdead']))
				{
					$sapv_dead[$k_form_ipid][] = $sapv_dead_map[$v_form_data['hospizdead']];
				}

				if(!empty($v_form_data['palliativdead']))
				{
					$sapv_dead[$k_form_ipid][] = $sapv_dead_map[$v_form_data['palliativdead']];
				}

				if(!empty($v_form_data['krankendead']))
				{
					$sapv_dead[$k_form_ipid][] = $sapv_dead_map[$v_form_data['krankendead']];
				}

				$xml_export_data[$k_form_ipid]['ee_zusatz_verstorben_wo'] = implode(', ', $sapv_dead[$k_form_ipid]);
				//sapv death end

				if(!empty($v_form_data['deathwishja']))
				{
					$xml_export_data[$k_form_ipid]['ee_zusatz_sterbeort_wunsch'] = $deathwish_map[$v_form_data['deathwishja']];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_zusatz_sterbeort_wunsch'] = '';
				}

				$xml_export_data[$k_form_ipid]['ee_besuche'] = $v_form_data['besuche'];

				if($v_form_data['hospitalwithNotarz'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ee_notarzteinsaetze'] = $v_form_data['hospitalwithNotarz'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_notarzteinsaetze'] = '';
				}

				if($v_form_data['hospitalwithoutNotarz'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ee_kh_einweisungen'] = $v_form_data['hospitalwithoutNotarz'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ee_kh_einweisungen'] = ' ';
				}

				if($v_form_data['stathospiz'] == '1')
				{
					$durch[$k_form_ipid][] = 'Stationäres Hospiz';
				}

				if($v_form_data['kranken'] == '1')
				{
					$durch[$k_form_ipid][] = 'Krankenhaus';
				}
				if($v_form_data['palliativ'] == '1')
				{
					$durch[$k_form_ipid][] = 'Palliativstation';
				}
				if($v_form_data['statpflege'] == '1')
				{
					$durch[$k_form_ipid][] = 'Stationäre Pflege';
				}
				if($v_form_data['ambhospizdienst'] == '1')
				{
					$durch[$k_form_ipid][] = 'ambulanter Hospizdienst';
				}
				if($v_form_data['ambpflege'] == '1')
				{
					$durch[$k_form_ipid][] = 'Amb. Pflege';
				}

				if($v_form_data['harzt'] == '1')
				{
					$durch[$k_form_ipid][] = 'Hausarzt';
				}

				if($v_form_data['farzt'] == '1')
				{
					$durch[$k_form_ipid][] = 'Facharzt';
				}

				if($v_form_data['patange'] == '1')
				{
					$durch[$k_form_ipid][] = 'Patient/Angehörige';
				}

				if($v_form_data['beratung'] == '1')
				{
					$durch[$k_form_ipid][] = 'Beratungsdienst';
				}



				if($v_form_data['erstsapv'] == '1')
				{
					$durch_sapv[$k_form_ipid][] = 'Erst-SAPV';
				}

				if($v_form_data['weideraufnahme'] == '1')
				{
					$durch_sapv[$k_form_ipid][] = 'Wiederaufnahme SAPV';
				}

				if(count($durch_sapv[$k_form_ipid]) > 0)
				{
					$xml_export_data[$k_form_ipid]['ev_erstkontakt_durch_sapv'] = implode(', ', $durch_sapv[$k_form_ipid]); //"Erst-SAPV" oder "Wiederaufnahme-SAPV" take from form
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erstkontakt_durch_sapv'] = '';
				}

				if(count($durch[$k_form_ipid]) > 0)
				{
					$xml_export_data[$k_form_ipid]['ev_erstkontakt_durch'] = implode(', ', $durch[$k_form_ipid]);
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erstkontakt_durch'] = '';
				}


				if(!empty($v_form_data['expectationkeine']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_keine_angabe'] = '1';
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_keine_angabe'] = '0';
				}

				if(!empty($v_form_data['expectationsonstiges']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_sonstiges_text'] = $v_form_data['expectationsonstiges'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_sonstiges_text'] = '';
				}

				if(!empty($v_form_data['expectation']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_sonstiges'] = $v_form_data['expectation'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_sonstiges'] = '';
				}

				if(!empty($v_form_data['preabilitation']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_pall_reha'] = $v_form_data['preabilitation'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_pall_reha'] = '';
				}

				if(!empty($v_form_data['symptomrelief']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_symptomlind'] = $v_form_data['symptomrelief'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_symptomlind'] = '';
				}

				if(!empty($v_form_data['nohospital']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_kein_krankenhaus'] = $v_form_data['nohospital'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_kein_krankenhaus'] = '';
				}

				if(!empty($v_form_data['nolifeenxendingmeasures']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_kein_lebensverl'] = $v_form_data['nolifeenxendingmeasures'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_kein_lebensverl'] = '';
				}

				if(!empty($v_form_data['leftalone']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_in_ruhe_lassen'] = $v_form_data['leftalone'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_in_ruhe_lassen'] = '';
				}

				if(!empty($v_form_data['activeparticipation']))
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_selbstbestimmung'] = $v_form_data['activeparticipation'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_erwart_beginn_selbstbestimmung'] = '';
				}

				$patient_erwartung = array('ja', 'nein', 'teilweise', 'unbekannt');

				if(!empty($formdataarray['patientexpectationsapv']))
				{
					$xml_export_data[$k_form_ipid]['ev_pat_erwart_sapv_real'] = $patient_erwartung[$formdataarray['patientexpectationsapv']];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_pat_erwart_sapv_real'] = '';
				}

				if($v_form_data['painsymptoms'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_schmerz'] = $v_form_data['painsymptoms'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_schmerz'] = '';
				}

				if($v_form_data['gastrointestinalsymptoms'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_gastro'] = $v_form_data['gastrointestinalsymptoms'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_gastro'] = '';
				}

				if($v_form_data['psychsymptoms'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_neuro'] = $v_form_data['psychsymptoms'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_neuro'] = '';
				}

				if($v_form_data['urogenitalsymptoms'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_uro'] = $v_form_data['urogenitalsymptoms'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_uro'] = '';
				}

				if($v_form_data['ulztumor'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_ulz'] = $v_form_data['ulztumor'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_ulz'] = '';
				}

				if($v_form_data['cardiacsymptoms'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_respir'] = $v_form_data['cardiacsymptoms'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_kompl_sympt_respir'] = '';
				}



				if($v_form_data['ethicalconflicts'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_ethisch'] = $v_form_data['ethicalconflicts'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_ethisch'] = '';
				}

				if($v_form_data['acutecrisispat'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_akut'] = $v_form_data['acutecrisispat'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_akut'] = '';
				}

				if($v_form_data['paliatifpflege'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_palliativ'] = $v_form_data['paliatifpflege'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_palliativ'] = '';
				}

				if($v_form_data['privatereferencesupport'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_unterst'] = $v_form_data['privatereferencesupport'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_unterst'] = '';
				}

				if($v_form_data['sociolegalproblems'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_betreuung'] = $v_form_data['sociolegalproblems'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_betreuung'] = '';
				}

				if($v_form_data['securelivingenvironment'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_sicherung'] = $v_form_data['securelivingenvironment'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_sicherung'] = '';
				}

				if($v_form_data['coordinationcare'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_koordination'] = $v_form_data['coordinationcare'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_koordination'] = '';
				}

				if($v_form_data['otherrequirements'] >= '0')
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_sonstiges'] = $v_form_data['otherrequirements'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_sonstiges'] = '';
				}

				if(!empty($v_form_data['complexeventsmore']))
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_sonstiges_text'] = $v_form_data['complexeventsmore'];
				}
				else
				{
					$xml_export_data[$k_form_ipid]['ev_weit_gesch_sonstiges_text'] = '';
				}

				if($v_form_data['actualconductedko'])
				{
					$sapvlast[$k_form_ipid][] = $verordnet_map[1];
				}

				if($v_form_data['actualconductedbe'])
				{
					$sapvlast[$k_form_ipid][] = $verordnet_map[2];
				}

				if($v_form_data['actualconductedtv'])
				{
					$sapvlast[$k_form_ipid][] = $verordnet_map[3];
				}

				if($v_form_data['actualconductedvv'])
				{
					$sapvlast[$k_form_ipid][] = $verordnet_map[4];
				}

				$xml_export_data[$k_form_ipid]['ev_tats_durchgef_sapv'] = implode(', ', $sapvlast[$k_form_ipid]);
				$xml_export_data[$k_form_ipid]['ev_ende_sapv_am'] = date('d.m.Y', strtotime($v_form_data['endSapvFall']));
				$xml_export_data[$k_form_ipid]['ev_ende_sapv_kein_bedarf'] = $v_form_data['noneedsapv'];
				$xml_export_data[$k_form_ipid]['ev_sapv_real_erfolgreich'] = $sapv_expect_map[$v_form_data['sapvstatusja']];
				$xml_export_data[$k_form_ipid]['ev_tage_24h_bereitschaft'] = $v_form_data['bereitschft'];
				$xml_export_data[$k_form_ipid]['ev_tage_intermittierung'] = $v_form_data['allhospitaldays'];

				$history_xml[$k_form_ipid]['evaluation'] = $xml_export_data[$k_form_ipid];
				//construct collection items
				$records[] = array(
					"client" => $clientid,
					"parent" => '',
					"ipid" => $k_form_ipid,
					"xml" => $this->toXml($history_xml[$k_form_ipid])
				);
			}

			$xml_data = $this->toXml($xml_export_data);

			//insert master xml in history
			$sapv_export_history = new Application_Form_SapvExportHistory();
			$master_id = $sapv_export_history->insert_sapv_export_master($xml_data);

			foreach($records as $k_data_rec => $v_data_rec)
			{
				$records[$k_data_rec]['parent'] = $master_id;
			}

			//insert individual xml data at once in history
			$collection = new Doctrine_Collection('SapvExportHistory');
			$collection->fromArray($records);
			$collection->save();

			return $this->xmlpp($xml_data);
		}

		public function sapvbulkexporthistoryAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$export_history = new SapvExportHistory();
			$hidemagic = Zend_Registry::get('hidemagic');

			$history_data = $export_history->get_sapv_export_history($clientid);

			$master_history_ipids[] = '99999999999';
			foreach($history_data as $k_history => $v_history)
			{
				if($v_history['parent'] == '0')
				{
					$master_history_data[$v_history['id']] = $v_history;
				}
				else
				{
					$master_history_data[$v_history['parent']]['slave'][] = $v_history;
					$master_history_ipids[] = $v_history['ipid'];
				}
			}

			$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.traffic_status,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, p.birthd,p.admission_date,p.change_date,p.isadminvisible,p.traffic_status,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$client_pats = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->Where('isdelete = 0')
				->andWhere('isstandbydelete = 0');
			$client_pats->leftJoin("p.EpidIpidMapping e");
			$client_pats->andWhere('e.clientid = ' . $clientid);
			$client_pats->andWhereIn('e.ipid', $master_history_ipids);
			$client_patients_details_res = $client_pats->fetchArray();

			$client_patients_details[] = '9999999999999';
			foreach($client_patients_details_res as $k_patients_det => $v_patients_det)
			{
				$client_patients_details[$v_patients_det['ipid']] = $v_patients_det;
			}
			;
			foreach($history_data as $k_hist_data => $v_hist_data)
			{
				if($v_hist_data['parent'] > '0')
				{
					$master_patients_ipids[$v_hist_data['parent']][] = $client_patients_details[$v_hist_data['ipid']]['EpidIpidMapping']['epid'];
				}
			}

			$this->view->patient_details = $client_patients_details;
			$this->view->master_history_data = $master_history_data;
			$this->view->master_patients_ipids = $master_patients_ipids;
		}

		function xmlpp($xml, $html_output = false)
		{
			$xml_obj = new SimpleXMLElement($xml);

			$level = 4;
			$indent = 0; // current indentation level
			$pretty = array();

			// get an array containing each XML element
			$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

			// shift off opening XML tag if present
			if(count($xml) && preg_match('/^<\?\s*xml/', $xml[0]))
			{
				$pretty[] = array_shift($xml);
			}

			foreach($xml as $el)
			{
				if(preg_match('/^<([\w])+[^>\/]*>$/U', $el))
				{
					// opening tag, increase indent
					$pretty[] = str_repeat(' ', $indent) . $el;
					$indent += $level;
				}
				else
				{
					if(preg_match('/^<\/.+>$/', $el))
					{
						$indent -= $level;  // closing tag, decrease indent
					}
					if($indent < 0)
					{
						$indent += $level;
					}
					$pretty[] = str_repeat(' ', $indent) . $el;
				}
			}
			$xml = implode("\n", $pretty);
			$xml = html_entity_decode($xml, ENT_QUOTES, "utf-8");

			return ($html_output) ? $xml : $xml;
		}

		public function verlaufentriesexportAction()
		{
			//get client & user data
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->view->clientarray = Pms_CommonData::getAllClientsDD();

			if($_POST)
			{
				if($_POST['dates'])
				{
					$dates_array = explode(",", $_POST['dates']);
				}
				$days_str = '"0000",';

				foreach($dates_array as $k => $day)
				{
					$day = (string) trim($day);
					if(strlen($day) == 10)
					{
						$clean_day_array[] = date('d.m.Y', strtotime($day));
						$days_str .='"' . date('Y-m-d', strtotime($day)) . '",';
					}
				}

				if($days_str)
				{
					$days_str = substr($days_str, 0, -1);
				}

				/* ----------------- Get all client patients ---------------------------- */
				$client_patients = $this->getAllClientPatients($_POST['client'], $whereepid, true);

				/* ----------------- Prepare "where in" array single level ---------------------------- */
				$client_ipids[] = '99999999';
				foreach($client_patients as $k_pat => $v_pat)
				{
					$patient_details[$v_pat['ipid']]['patient_name'] = $v_pat['last_name'] . ' ' . $v_pat['first_name'];
					$patient_details[$v_pat['ipid']]['epid'] = $v_pat['EpidIpidMapping']['epid'];
					$client_ipids[] = $v_pat['ipid'];
				}

				/* -----------------Gel all verlauf entries in the "posted" data---------------------------- */
				$verlauf_entries_q = Doctrine_Query::create()
					->select("ipid,course_date")
					->from('PatientCourse')
					->where(' date(course_date)  in (' . $days_str . ') ')
					->andWhereIn('ipid', $client_ipids)
					->andWhere('source_ipid = ""');
				$verlauf_entries_array = $verlauf_entries_q->fetchArray();

				$i = 1;
				foreach($verlauf_entries_array as $key => $values)
				{
					$date_entry = date('d.m.Y', strtotime($values['course_date']));
					$verlauf[$date_entry] [$patient_details[$values['ipid']]['epid']][0] = $patient_details[$values['ipid']]['epid'];
					$verlauf[$date_entry] [$patient_details[$values['ipid']]['epid']][1] = $patient_details[$values['ipid']]['patient_name'];
					$i++;
				}

				/* ------------------------------Prepare export array ------------------------------ */
				foreach($clean_day_array as $key => $day_value)
				{
					if(!empty($verlauf[$day_value]))
					{
						$export_data[$day_value] = $verlauf[$day_value];
					}
					else
					{
						$export_data[$day_value] = "--";
					}
				}

				$this->generate_verlauf_CSV($export_data, 'export.xls');
				exit;
			}
		}

		public function generate_verlauf_CSV($export_data, $filename = 'export.xls')
		{
			$file = fopen('php://output', 'w');

			foreach($export_data as $date => $patient_data)
			{
				fputcsv($file, array(''));
				fputcsv($file, array(strtoupper(utf8_encode($date))));


				foreach($patient_data as $epid => $values)
				{
					fputcsv($file, $values);
				}
			}

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-dType: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $filename);
			exit;
		}

		public function pricelisthessenAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_hessen = new PriceHessen();

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
				}
			}

			$this->view->listid = $list;
			$this->view->shortcuts_hessen = $shortcuts['hessen'];

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$form = new Application_Form_ClientPriceList();

				if($_POST['save_prices'])
				{
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelisthessen?list=" . $list);
					exit;
				}
			}

			$price_hessen = $p_hessen->get_prices($list, $clientid, $this->view->shortcuts_hessen, $default_price_list['hessen']);

			$this->view->price_hessen = $price_hessen;
		}

		/*
		 * now it is possible to perform upload/delete between any associated clients files
		 * TODO : on assert_associated ->setIsmaster()
		 * only Ismaster should be allowed to deleted files from other clients
		 * only Ismaster should be allowed to upload to other clients
		 * 
		 * @cla 16.05.2018
		 * ISPC-2139 + added redirect to projects/overview ... if you delete a file via that controller
		 */
		public function uploadfilesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$file_password = $logininfo->filepass;

			//ini_set("upload_max_filesize", "10M"); // this does nothing... this is PHP_INI_PERDIR

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			
			//verify if requested client is associated with the loghed-in
			$cid = $this->getRequest()->isPost() ? $this->getRequest()->getPost('cid') : $this->getRequest()->getParam('cid');
			if ($cid) {
			    $post_clientid = Pms_Uuid::decrypt($cid);
			
			    $acf_obj = new AssociatedClientFiles();
			
			    if($acf_obj->assert_associated($post_clientid)) {
			
			        //all ok, change the $clientid for witch we save the file
			        $clientid = $post_clientid;
			
			        $post_clientid_data = Client::getClientDataByid($clientid);
			        $file_password = $post_clientid_data[0]['fileupoadpass'];
			
			    } else {
			        
			        if ($this->getRequest()->isXmlHttpRequest()) {
			            throw new Zend_Exception('ClientFiles not associated ! admin must verify this error.', 1);
			        } else {
			            $this->redirect( "overview/overview", array(
			                "exit" => true,
			                "prependBase" => true,
			            ));
			        }
			         
			        exit; //for readability
			        /*
			         * you can reach this if delete file then change client( get params remain here)
			         * or the posted cid is not associated ... then you have a xhr and user dosen't see the redirect
			         */			
			    }
			}
			
			
			/*			 * ********Deletefile*********** */
			if($this->getRequest()->isGet() && $_GET['delid'] > 0) {
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				 
				$upload_form = new Application_Form_ClientFileUpload();
				$upload_form->deleteFile($_GET['delid'], false, $clientid);
				
				//if added for ISPC-2139
				if (strpos($this->getRequest()->getHeader('referer'), 'projects/overview')) {
				    $this->redirect(
				        APP_BASE .  'projects/overview'
				        . "?step=view_project"
				        . "&project_ID=" . $this->getRequest()->getParam('project_ID', '')
				        . "&selected_tab=" . $this->getRequest()->getParam('selected_tab', ''),
				        array("exit" => true));
				    exit; //for readabilitys
				}
				
				
			}

			if($this->getRequest()->isGet() && $_GET['doc_id'] > 0) {
				$patient = Doctrine_Query::create()
					->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('CLientFileUpload')
					->where('id= ?', $_GET['doc_id']);
				$fl = $patient->execute();

				if($fl)
				{
					$flarr = $fl->toArray();

					$explo = explode("/", $flarr[0]['file_name']);

					$fdname = $explo[0];
					$flname = utf8_decode($explo[1]);
				}
				
				//TODO change this direct ftp download into Pms_CommonData::ftp_download
				$con_id = Pms_FtpFileupload::ftpconnect();
				if($con_id)
				{
					$upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip');
					Pms_FtpFileupload::ftpconclose($con_id);
				}

				$cmd = "unzip -P " . $logininfo->filepass . " clientuploads/" . $fdname . ".zip;";
				exec($cmd);

				$file = file_get_contents("clientuploads/" . $fdname . "/" . $flname);
				ob_end_clean();
				ob_start();
				$expl = explode(".", $flname);


				if($expl[count($expl) - 1] == 'doc' || $expl[count($expl) - 1] == 'docx' || $expl[count($expl) - 1] == 'xls' || $expl[count($expl) - 1] == 'xlsx')
				{
					header("location: " . APP_BASE . "clientuploads/" . $fdname . "/" . $flname);
				}
				else
				{
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . $flname . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize("clientuploads/" . $fdname . "/" . $flname));
					ob_clean();
					flush();

					echo readfile("clientuploads/" . $fdname . "/" . $flname);
				}
				exit;
			}

			/*			 * ************************************ */
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				
				/*
				$ftype = $_SESSION['filetype'];
				if($ftype)
				{
					$filetypearr = explode("/", $ftype);
					if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
					{
						$filetype = "XLSX";
					}
					elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
					{
						$filetype = "docx";
					}
					elseif($filetypearr[1] == "X-OCTET-STREAM")
					{
						$filetype = "PDF";
					}
					else
					{
						$filetype = $filetypearr[1];
					}
				}
				*/

				$af_cfu = new Application_Form_ClientFileUpload();
				
				$action = 'upload_client_files';
				
				$qquid_s = $this->getRequest()->getPost('qquuid');
				
				$qquuid_title_s = $this->getRequest()->getPost('qquuid_title');

				$folder_id = $this->getRequest()->getPost('folder_id', 0);
				
				//dd($qquid_s, $qquuid_title_s, $folder_id,$clientid, $this->get_last_uploaded_file($action, null, $clientid));
				
				//$this->_log_info("this files need to be saved:" . print_r($qquid_s, true));
				
				$all_uploaded_files_of_client = $this->get_last_uploaded_file($action, null, $clientid);
				
				//$this->_log_info("session : " . print_r($all_uploaded_files_of_client, true));
				
				foreach ( $qquid_s as $k => $qquid) {
				    
				    $uploadedFile = $all_uploaded_files_of_client[$qquid];
// 				    $uploadedFile = $this->get_last_uploaded_file($action, $qquid, $clientid);
// 				    $uploadedFile = $uploadedFile[$qquid];
				    
				    //$this->_log_info("fileX: " . print_r($uploadedFile, true));
				    
				    if ( ! $uploadedFile || ! $uploadedFile['isZipped']) {
				        
				        //$this->_log_info("file is not zipped {$qquid}");
				        continue;
				    }
				    
				    
				    
				    $file_name = pathinfo($uploadedFile['filepath'], PATHINFO_FILENAME) . "/" . $uploadedFile['fileInfo']['name'];

				    $data2save = array(
				        'clientid' => $uploadedFile['clientid'],
				        
				        'title' => ! empty($qquuid_title_s[$k]) ? $qquuid_title_s[$k] : pathinfo($uploadedFile['filename'], PATHINFO_FILENAME),
				        
				        'file_type' => strtoupper(pathinfo($uploadedFile['filename'], PATHINFO_EXTENSION)),

				        'file_name' => $file_name,
				        
				        'folder' => $folder_id,
				        
				        'tabname' => null,
				        'recordid' => null,
				        'parent_id' => null,
				    );

				    //$this->_log_info("data2save : ". print_r($data2save, true));
				    
				    $record = $af_cfu->InsertNewRecord($data2save);
 				    
				    if ($record->id) {
				        
				        //$this->_log_info("InsertNewRecord result: ". $record->id);
				        
				        $ftp_put_queue_result = Pms_CommonData::ftp_put_queue(
				            $uploadedFile['filepath'], 
				            $uploadedFile['legacy_path'], 
				            array(  
    				            "is_zipped" => true,
    				            "file_name" => $file_name,
    				            "insert_id" => $record->id,
    				            "db_table" => "ClientFileUpload"
    				        ), 
				            $foster_file = false, 
				            $uploadedFile['clientid'],
				            $uploadedFile['filepass']
			            );
				        
				        if ($ftp_put_queue_result) {
				            //delete the file from uploaded location... it is now in the ft_que_path
				            $this->delete_last_uploaded_file($action, $qquid, $clientid);
				        }
				        
				    }
				    
				}
				/*
				
				$upload_form = new Application_Form_ClientFileUpload();

				$a_post = $_POST;
				$a_post['clientid'] = $clientid;
				$a_post['filepass'] = $file_password;
				$a_post['filetype'] = $_SESSION['filetype'];

				if($upload_form->validate($a_post))
				{
					$upload_form->insertData($a_post);
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retainValues($_POST);
				}

				//remove session stuff
				$_SESSION['filename'] = '';
				$_SESSION['filetype'] = '';
				$_SESSION['filetitle'] = '';
				unset($_SESSION['filename']);
				unset($_SESSION['filetype']);
				unset($_SESSION['filetitle']);
				
				*/
			}

			
			
			//list files for all associated client from /client/associateclientfiles
			//ISPC-2176
			$acf_obj = new AssociatedClientFiles();
			$associate_clients = $acf_obj->fetchAssociatedClients($logininfo->clientid);
			
			//set this variable here as true... and you will allways have the files tabulated/client
			$logendin_client_ismaster = false;
			
			$associated_client_IDs = array();
			if ( ! empty($associate_clients)) {
			    foreach ($associate_clients as $groupid=>$clients) {
			        
			        $logendin_client_ismaster = $clients[$logininfo->clientid] ['ismaster'] == 'yes' ? true : $logendin_client_ismaster;
			        
			        $associated_client_IDs = array_merge($associated_client_IDs, array_column($clients, 'clientid'));
			    }
    			$associated_client_IDs = array_unique($associated_client_IDs);

    			$c_obj = new Client();
    			$associated_clients = $c_obj->fetchById($associated_client_IDs);

    			//make this client first in the tabs
    			$this_client = $associated_clients[$logininfo->clientid];
    			unset($associated_clients[$logininfo->clientid]);
    			array_unshift($associated_clients, $this_client);
    			
			} else {
			    //single client, default style
			    $c_obj = new Client();
			    $associated_clients = $c_obj->fetchById($logininfo->clientid);
			}
			
			$this->view->associated_clients = $associated_clients;

			//set this variable here as true... and you will allways have the files tabulated/client
			$this->view->logendin_client_ismaster = $logendin_client_ismaster;
			
			$this->view->logendin_clientid = $logininfo->clientid;
			
            $this->view->showInfo = $logininfo->showinfo;
            
            
            $files_data = array();

            foreach ($associated_clients as $client) {
                
                $clientid = $client['id'];
                                
                //count the files/folder
                $files = new ClientFileUpload();
                $files_data[$clientid]['filesData'] = $files->getClientFiles($clientid);
                	
                $files_data[$clientid]['files2folders'] = $files->getClientFiles2folders($clientid);
                                
                $users = User::get_AllByClientid($clientid);
                $files_data[$clientid]['allusers'] = array_column($users, 'nice_name', 'id');
                
                //ISPC-2434 Lore 20.08.2019  
                $client_folders = Doctrine_Query::create()
                ->select("*, AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name, LOWER( CONVERT( AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') USING 'utf8' )) as foldername")
                ->from('ClientFilesFolders indexBy id')
                ->where('clientid = ?', $clientid)
                ->andWhere('isdelete = 0')
                ->orderby('foldername ASC')
                ->fetchArray()
                ;
                //dd($client_folders);
                $files_data[$clientid]['client_folders'] = $client_folders;
                
            }
            $this->view->files_data = $files_data;

            
            $selected_tab = 0; 
            $cid = $this->getRequest()->isPost() ? $this->getRequest()->getPost('cid') : $this->getRequest()->getParam('cid');
            if ($cid) {
                $selected_tab = Pms_Uuid::decrypt($cid);
            } elseif($tabs_data_cid = $this->getRequest()->getParam('tabs_data_cid')) {
                $selected_tab =  Pms_Uuid::decrypt($tabs_data_cid);
            }
            $this->view->selected_tab =  $selected_tab;
            
		}
		
		
		//this should allways be a POST request
		public function clientuploadifyAction()
		{

		    $response = array(); // return as json
		    
// 		    if ( ! $this->getRequest()->isPost()) {
// 		        $this->_helper->json->sendJson(array('success' => false,  'message' => 'wrong method'));
// 		        exit; //for readbility
// 		    }
		    
		    $this->_helper->layout->setLayout('layout');
		    $this->_helper->viewRenderer->setNoRender();
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$file_password = $logininfo->filepass;
			
			
			if ($this->getRequest()->isPost()) {
			    
			    if ( ! ($action = $this->getRequest()->getPost('action'))) {			        
			        $action = 'upload_client_files'; //default if empty
			    }
			    
			    $_method = $this->getRequest()->getPost('_method');
			    
			    $cid = $this->getRequest()->getPost('cid');
			    
			} else {
			    
			    if ( ! ($action = $this->getRequest()->getQuery('action'))) {
			        $action = 'upload_client_files'; //default if empty
			    }
			    
			    $_method = $this->getRequest()->getQuery('_method');
			    
			    $cid = $this->getRequest()->getQuery('cid');
			}
			
			//verify if requested client is associated with the loghed-in
			if ($cid) {
			    $post_clientid = Pms_Uuid::decrypt($cid);
			
			    $acf_obj = new AssociatedClientFiles();
			
			    if($acf_obj->assert_associated($post_clientid)) {
			        //all ok, change the $clientid for witch we request the files
			        $clientid = $post_clientid;
			         
			        $post_clientid_data = Client::getClientDataByid($clientid);
			        $file_password = $post_clientid_data[0]['fileupoadpass'];
			
			    } else {
			        
			        if ($this->getRequest()->isXmlHttpRequest()) {
			            throw new Zend_Exception('ClientFiles not associated ! admin must verify this error.', 1);
			        } else {
			            $this->redirect( "overview/overview", array(
			                "exit" => true,
			                "prependBase" => true,
			            ));
			        }
			        
			        exit; //for readability
			        /*
			         * you can reach this if delete file then change client( get params remain here)
			         * or the posted cid is not associated ... then you have a xhr and user dosen't see the redirect
			         */
			    }
			}
			
			if ($_method == 'SESSION') {
			    
			    $all_uploaded_files_of_client = $this->get_last_uploaded_file($action, null, $clientid);
			    
			    foreach ($all_uploaded_files_of_client as $qquid => $file) {
			        
			        if (empty($file) 
			            || empty($file['filename']) 
			            || empty($file['fileInfo']['size'])
			            || ! is_file($file['filepath'])) 
			        {
			            continue; // this because fineUploader will stall if any of this is wrong
			        }
			        
			        $file = array(			            
			            'name' => $file['filename'],
			            'uuid' => $qquid,
			            'size' => $file['fileInfo']['size'],
			             
			            //https://docs.fineuploader.com/features/session.html
			            /*
    			        'thumbnailUrl' => '',
    			         
    			        'deleteFileEndpoint' => '',
    			        'deleteFileParams' => '',
    			         
    			        's3Key' => '',
    			        's3Bucket' => '',
    			        'blobName' => '',
    			        */
			        );
			        
			        array_push($response, $file);
			    }
			    			    
			} elseif ($_method == 'DELETE') {
			    
			    $this->delete_last_uploaded_file($action, $this->getRequest()->getPost('qquuid', 0) , $clientid);
			    
			    $response = array('success' => true);
			    
			} else {
			    
			    $response = $this->upload_qq_file( array(
			        "allowed_file_extensions" => null, // this means any extension is allowed
			        "max-filesize" => null,//this means ini_get
			        "action" => $action,
			        "public_file_path" => CLIENTUPLOADS_PATH,
			        "clientid" => $clientid,
			        "filepass" => $file_password,
			        "zip_file" => true,
			    ));
			    
			}
			
			//ob_end_clean();
			//ob_start();
			
			$this->_helper->json->sendJson($response);
			
			exit; // for readbility
			
			
		}

		/*
		 * @cla on 20.04.2018 modified the fn
		 * @cla on 23.04.2018 changed download header filename=\"{$nice_filename}
		 */
		public function clientfileAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$file_password = $logininfo->filepass;

			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();

			$doc_id = intval($this->getRequest()->getParam('doc_id'));
			
			
			if ($doc_id > 0) {
			    
			    //verify if requested client is associated with the loghed-in
			    $cid = $this->getRequest()->isPost() ? $this->getRequest()->getPost('cid') : $this->getRequest()->getParam('cid');
			    if ($cid) {
			        $post_clientid = Pms_Uuid::decrypt($cid);
			    
			        $acf_obj = new AssociatedClientFiles();
			    
			        if($acf_obj->assert_associated($post_clientid)) {
			    
			            //all ok, change the $clientid for witch we save the file
			            $clientid = $post_clientid;
			    
			            $post_clientid_data = Client::getClientDataByid($clientid);
			            $file_password = $post_clientid_data[0]['fileupoadpass'];
			    
			        } else {
			            
			            if ($this->getRequest()->isXmlHttpRequest()) {
			                throw new Zend_Exception('ClientFiles not associated ! admin must verify this error.', 1);
			            } else {
			                $this->redirect( "overview/overview", array(
			                    "exit" => true,
			                    "prependBase" => true,
			                ));
			            }
			             
			            exit; //for readability
			            /*
			             * you can reach this if delete file then change client( get params remain here)
			             * or the posted cid is not associated ... then you have a xhr and user dosen't see the redirect
			             */
			        }
			    }
			    
			    
				$flarr = Doctrine_Query::create()
					->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('ClientFileUpload')
					->where('id = ?', $doc_id )
					->andWhere('clientid = ? ', $clientid )
					->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY)
					;
				


				if ( ! empty($flarr)) { 
				    
				    $explo = explode("/", $flarr['file_name']);
				    
				    $fdname = $explo[0];
				    $flname = utf8_decode($explo[1]);
				    
					//$file_password = $logininfo->filepass;
					
					$old = $_REQUEST['old'] ? true : false;
					if (($path = Pms_CommonData::ftp_download('clientuploads/' . $fdname . '.zip' , $file_password , $old , $flarr['clientid'] , $flarr['file_name'], "ClientFileUpload", $flarr['id'])) === false){
						//failed to download file
						throw new Zend_Exception('Failed to download Client File, please inform admin !' , 4);//warning
					}
					$fullPath = $path . "/". $flname;
					
					//$path = $_SERVER['DOCUMENT_ROOT'] . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
// 					$path = PUBLIC_PATH. "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
// 					$fullPath = $path . $flname;

					if($fd = fopen($fullPath, "r"))
					{
						$fsize = filesize($fullPath);
						$path_parts = pathinfo($fullPath);
						
						$nice_filename = $flarr['title'] . '.' . strtolower($flarr['file_type']);
						$nice_filename = Pms_CommonData::filter_filename($nice_filename, true);

						$ext = strtolower($path_parts["extension"]);
						switch($ext)
						{
							case "pdf":
								header('Content-Description: File Transfer');
								header("Content-type: application/pdf"); // add here more headers for diff. extensions
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Pragma: public');
								if($_COOKIE['mobile_ver'] != 'yes')
								{ //if on mobile version don't send content-disposition to play nice with iPad
									//header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
									header("Content-Disposition: attachment; filename=\"{$nice_filename}\"");
								}
								break;
							default;
								header('Content-Description: File Transfer');
								header("Content-type: application/octet-stream");
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Pragma: public');
								if($_COOKIE['mobile_ver'] != 'yes')
								{ //if on mobile version don't send content-disposition to play nice with iPad
									//header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
									header("Content-Disposition: attachment; filename=\"{$nice_filename}\"");
								}
						}
						header("Content-length: $fsize");
						header("Cache-control: private"); //use this to open files directly
						readfile($fullPath);
					
						fclose($fd);
    					@unlink($fullPath);
					} else {
					    //why can't we open?... or where is the file? or is the password wrong?
					    throw new Zend_Exception('Failed to open Client File, please inform admin !' , 4);//warning
					    					
					}
					
					
					exit;
				}
			}
		}

		public function dataexportAction()
		{
			//get client & user data
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($this->getRequest()->isPost())
			{
				if(strlen($_POST['export_locations']) > 0 && !empty($_POST['export_locations']))
				{
					$fdoc = Doctrine_Query::create()
						->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
						->from('Locations')
						->where("client_id='" . $clientid . "'")
						->andWhere('isdelete=0')
						->orderBy('location ASC');
					$export_data = $fdoc->fetchArray();
					$filename = "locations.csv";
				}
				else if(strlen($_POST['export_family_doctors']) > 0 && !empty($_POST['export_family_doctors']))
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('FamilyDoctor')
						->where("isdelete = 0 and valid_till='0000-00-00' and (first_name!='' or last_name!='')")
						->andwhere("clientid='" . $clientid . "'")
						->andWhere('indrop=0');
					$export_data = $drop->fetchArray();
					$filename = "family_doctors.csv";
				}
				else if(strlen($_POST['export_pflegedienst']) > 0 && !empty($_POST['export_pflegedienst']))
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('Pflegedienstes')
						->where("isdelete = 0 and valid_till='0000-00-00'")
						->andWhere('indrop=0')
						->andWhere("clientid='" . $clientid . "'");
					$export_data = $drop->fetchArray();
					$filename = "nursing_services.csv";
				}
				else if(strlen($_POST['export_health_insurance']) > 0 && !empty($_POST['export_health_insurance']))
				{
				    $drop = Doctrine_Query::create()
				    ->select('*')
				    ->from('HealthInsurance')
				    ->where("isdelete = 0")
				    ->andWhere('extra = 0')
				    ->andWhere("clientid = '" . $clientid . "'")
				    ->andWhere("onlyclients = '1'");
				    $export_data = $drop->fetchArray();
				    $filename = "health_insurance.csv";
				}
				else if(strlen($_POST['export_clients']) > 0 && !empty($_POST['export_clients']))
				{
				    $drop = Doctrine_Query::create()
				    ->select("team_name,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname")
				    ->from('Client')
				    ->where("isdelete = 0")
				    ->orderBy("convert(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
				    $export_data = $drop->fetchArray();
				    $filename = "clients.csv";
				}
				else if(strlen($_POST['all_shortcuts']) > 0 && !empty($_POST['all_shortcuts']))
				{
					$client_obj = new Client();
					$client_array = $client_obj->get_all_clients();
				    
				    $cs_q = Doctrine_Query::create()
				    ->select('*')
				    ->from('Courseshortcuts')
				    ->where('isdelete=0')
				    ->orderBy('shortcut ASC');
				    $cs = $cs_q->fetchArray();
				    
				    
				    foreach($cs as $k=>$cs_data){
				    	if(array_key_exists($cs_data['clientid'], $client_array)){
				    		
				    	$export_data[$cs_data['shortcut_id']]['shortcut'] = $cs_data['shortcut'];
				    	$export_data[$cs_data['shortcut_id']]['course_fullname'] = $cs_data['course_fullname'];
				    	$export_data[$cs_data['shortcut_id']]['clieant_name'] = $client_array[$cs_data['clientid']]['client_name'];
				    	}
				    }
				    $export_data = array_values($export_data);
				    
				    $filename = "all_shortcuts.csv";
				}
				else if(strlen($_POST['form_blocks']) > 0 && !empty($_POST['form_blocks']))
				{
				 
					
					$client_obj = new Client();
					$client_array = $client_obj->get_all_clients();
					
					
					$ftype = Doctrine_Query::create()
					->select('*')
					->from('FormTypes');
					$ftype_res = $ftype->fetchArray();

					foreach($ftype_res as $k=>$ft){
						$ft_details[$ft['id']] = $ft;
					}
					
					$select = Doctrine_Query::create()
					->select('*')
					->from('FormBlocks2Type')
					->where('form_block LIKE "%ebm%" OR form_block LIKE "%goa%" OR form_block LIKE "%sgbv%"  ')
					->andWhereNotIN('clientid',array("1","32"));
					$select_res = $select->fetchArray();
					
					
					foreach($select_res as $k=>$data){
						
						if($client_array[$data['clientid']]['isdelete'] != "1" && $ft_details[$data['form_type']]['isdelete'] != "1"){
							
							$fb2t[$data['id']]['form_block'] = $this->view->translate('block_'.$data['form_block']); 
							$fb2t[$data['id']]['form_type'] = $ft_details[$data['form_type']]['name'];
							$fb2t[$data['id']]['client'] = $client_array[$data['clientid']]['client_name'];
						} 
					}
					$export_data = array_values($fb2t);
					$filename = "form_blocks.csv";
				}
				foreach($export_data as $key => $data_array)
				{
					foreach($data_array as $column => $value)
					{
						$headers[$key][] = $column;
						$export_data_utf[$key][$column] = utf8_encode($value);
					}
				}

				$export_headers = $headers[0];

				if(!empty($export_data) && !empty($export_headers))
				{
					$this->export_data2CSV($export_data, $filename, $export_headers);
					exit;
				}
			}
		}

		public function export_data2CSV($data, $filename = 'export.xls', $headers = false)
		{
			$file = fopen('php://output', 'w');

			if($headers)
			{
				fputcsv($file, $headers);
			}

			foreach($data as $key => $items_array)
			{
				fputcsv($file, $items_array);
			}

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-dType: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $filename);
			exit;
		}

		public function mmireceiptblocksAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($_REQUEST['flg'])
			{
				if($_REQUEST['flg'] == 'err')
				{
					$this->view->error_mesage = $this->view->translate('error');
				}
				else if($_REQUEST['flg'] == 'inv')
				{
					$this->view->error_mesage = $this->view->translate('invalid_template');
				}
				else if($_REQUEST['flg'] == 'suc')
				{
					$this->view->success_message = $this->view->translate('success');
				}
				else if($_REQUEST['flg'] == 'del_suc')
				{
					$this->view->delete_message = $this->view->translate('deletedsuccessfully');
				}
			}
			
			if($this->getRequest()->isPost() && $_POST['submit'])
			{
				$post = $_POST;
				$form = new Application_Form_MmiTextBlocks();
				$mmi_text_remove = $form->remove_multiple_data($post, $clientid);
				
				$this->redirect(APP_BASE.'misc/mmireceiptblocks?flg=suc');
			}
		}

		public function fetchmmireceiptblocksAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;


			$columnarray = array(
				"id" => "id",
				"date" => "create_date"
			);

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];


			if($clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$fdoc = Doctrine_Query::create()
				->select('count(*)')
				->from('MmiReceiptTxtBlocks')
				->where("isdeleted = 0 " . $where)
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

			//used in pagination of search results
			if(!empty($_REQUEST['val']))
			{
				$fdoc->andWhere("text != ''");
				$fdoc->andWhere("text like '%" . trim($_REQUEST['val']) . "%'");
			}
			$fdocarray = $fdoc->fetchArray();

			$limit = 50;
			$fdoc->select('*');
			$fdoc->where("isdeleted = 0 " . $where . "");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("text != ''");
				$fdoc->andWhere("text like '%" . trim($_REQUEST['val']) . "%'");
			}
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());


			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "textslist.html");
				$this->view->texts_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("textsnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->texts_grid = '<tr><td colspan="5" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['textslist'] = $this->view->render('misc/fetchmmireceiptblocks.html');

			echo json_encode($response);
			exit;
		}

		public function addmmitextAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_MmiTextBlocks();
				$post = $_POST;

				if($_POST['save'])
				{
					if($form->validate($post))
					{
						$form->insert_data($post);
						$this->redirect(APP_BASE . 'misc/mmireceiptblocks');
						exit;
					}
					else
					{
						$form->assignErrorMessages();
						$this->retain_values($_POST);
					}
				}
			}
		}
		
		public function editmmitextAction()
		{
			$this->_helper->viewRenderer('addmmitext');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_MmiTextBlocks();
				$post = $_POST;

				if($_POST['save'])
				{
					if($form->validate($post) && strlen($_REQUEST['mtid'])>0)
					{
						$form->update_data($_REQUEST['mtid'], $post);
						$this->redirect(APP_BASE . 'misc/mmireceiptblocks');
						exit;
					}
					else
					{
						$form->assignErrorMessages();
						$this->retain_values($_POST);
					}
				}
			}
			
			if(strlen($_REQUEST['mtid'])>0)
			{
				$mmi_text = new MmiReceiptTxtBlocks();
				$mmi_text_res = $mmi_text->get_receipt_txt($_REQUEST['mtid'], $clientid);

				if($mmi_text_res)
				{
					$this->retainValues($mmi_text_res[0]);
				}
			}
		}

		public function deletemmireceiptblockAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();
			
			if(is_numeric($_REQUEST['mtid']) && strlen($_REQUEST['mtid']) > '0')
			{
				$form = new Application_Form_MmiTextBlocks();
				$mmi_text_remove = $form->remove_data($_REQUEST['mtid']);
				
				$this->redirect(APP_BASE.'misc/mmireceiptblocks?flg=suc');
			}
		}
		
		private function generateformPdf($chk, $post, $pdfname, $filename)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$clientinfo = Pms_CommonData::getClientData($clientid);
			$post = Pms_CommonData::clear_pdf_data($post);
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$post['ipid'] = Pms_CommonData::getIpid($decid);
			$userid = $logininfo->userid;
			$patientmaster = new PatientMaster();
			$parr = $patientmaster->getMasterData($decid, 0);

			$epid = Pms_CommonData::getEpidFromId($decid);
			$this->view->epid = $epid;

			$post['patientname'] = $parr['last_name'] . ", " . $parr['first_name'] . "<br>" . $parr['street1'] . "<br>" . $parr['zip'] . "<br>" . $parr['city'];
			$post['patientaddress'] = $parr['street1'] . "<br>" . $parr['zip'] . " " . $parr['city'];
			$post['pataddress'] = $parr['street1'] . ", " . $parr['zip'] . " " . $parr['city'];
			$post['patname'] = $parr['last_name'] . ", " . $parr['first_name'];
			$post['patbirth'] = $parr['birthd'];
			$post['epid'] = $epid;

			if($parr['sex'] == 1)
			{
				$this->view->male = "checked='checked'";
			}
			if($parr['sex'] == 2)
			{
				$this->view->female = "checked='checked'";
			}

			if($parr['sex'] == 1)
			{
				$this->view->gender = "männlich";
			}
			elseif($parr['sex'] == 2)
			{
				$this->view->gender = "weiblich";
			}
			elseif($parr['sex'] == 0)
			{
			    $this->view->gender = "divers";  //ISPC-2442 @Lore   30.09.2019
			}
			elseif(empty($parr['sex']))
			{
				$this->view->gender = "keine Angabe";
			}

			$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
			$this->view->refarray = $ref['referred_name'];

			$loguser = Doctrine::getTable('User')->find($logininfo->userid);

			if($loguser)
			{
				$loguserarray = $loguser->toArray();
				$this->view->lastname = $loguserarray['last_name'];
				$this->view->firstname = $loguserarray['first_name'];
			}

			// sapv questionnaire
			$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);

			if($chk == 1)
			{
				$tmpstmp = time();
// 				mkdir("uploads/" . $tmpstmp);
				mkdir(PDF_PATH. "/" . $tmpstmp);
// 				$pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
				$pdf->Output(PDF_PATH. '/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
				$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 				$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
// 				exec($cmd);
				$zipname = $tmpstmp . ".zip";
				$filename = "uploads/" . $tmpstmp . ".zip";
				/*
				$con_id = Pms_FtpFileupload::ftpconnect();
				if($con_id)
				{
					$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
					Pms_FtpFileupload::ftpconclose($con_id);
				}
				*/
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH. '/' . $tmpstmp . '/' . $pdfname . '.pdf', "uploads" );
				
			}
			if($chk == 2)
			{
				ob_end_clean();
				ob_start();
				$pdf->Output($pdfname . '.pdf', 'D');
				exit;
			}

			if($chk == 3)
			{
				$navnames = array(
					"sapvactivitygrid" => "Sapv Activity Grid",
				);

				$pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
				$pdf->setDefaults(true); //defaults with header
				$pdf->setImageScale(1.6);
				$pdf->SetMargins(10, 5, 10); //reset margins
// 				$pdf->SetFont('arial', '', 11);
				$pdf->SetFont('dejavusans', '', 11);

				switch($pdfname)
				{

					case 'sapvactivitygrid':
						$background_type = '23';
						break;
					default:
						$background_type = false;
						break;
				}

				$pdf->HeaderText = false;
				if($background_type != false)
				{
					$bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
					if($bg_image !== false)
					{
						$bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
						if(is_file($bg_image_path))
						{
							$pdf->setBackgroundImage($bg_image_path);
						}
					}
				}

				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);

				$pdf->setHTML($html);

				$tmpstmp = $pdf->uniqfolder(PDF_PATH);


				$file_name_real = basename($tmpstmp);

				$pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');

				$_SESSION ['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 				$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;

// 				exec($cmd);
				$zipname = $file_name_real . ".zip";
				$filename = "uploads/" . $file_name_real . ".zip";

				/*
				$con_id = Pms_FtpFileupload::ftpconnect();

				if($con_id)
				{
					$upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
					Pms_FtpFileupload::ftpconclose($con_id);
				}
				*/
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf', "uploads" );
				
				
				if($pdfname == 'ContactForm' || $pdfname == 'ContactFormSave')
				{
					$record_id = $post['contact_form_id'];
					$form_tabname = 'contact_form';
				}
				else
				{
					$record_id = '';
					$form_tabname = '';
				}

				$cust = new PatientFileUpload ();
				$cust->title = Pms_CommonData::aesEncrypt(addslashes($navnames [$pdfname]));
				$cust->ipid = $ipid;
				$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']); //$post['fileinfo']['filename']['name'];
				$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				$cust->recordid = $record_id;
				$cust->system_generated = "1";
				$cust->tabname = $form_tabname;
				$cust->save();
				$recordid = $cust->id;

				if($pdfname == "sapvactivitygrid")
				{
					$cust = new PatientCourse ();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Formular ' . $navnames [$pdfname] . ' wurde erstellt'));
					$cust->user_id = $logininfo->userid;
					$cust->save();
				}

				ob_end_clean();
				ob_start();
				$pdf->toBrowser($pdfname . '.pdf', "d");

				exit;
			}
		}

		private function save_history($user, $client, $xml, $imported_data = false)
		{
			if($imported_data)
			{
				$imported_data = serialize($imported_data);
			}
			else
			{
				$imported_data = NULL;
			}

			$history = new PatientImportHistory();
			$history->user = $user;
			$history->client = $client;
			$history->date = date('Y-m-d H:i:s');
			$history->xml_string = $xml;
			$history->imported_patients = $imported_data;
			$history->save();
		}

		private function toXml($data, $rootNodeName = '<evaluations></evaluations>', $xml = null, $elem_root = 'evaluation', $xsd_file = false)
		{
			// turn off compatibility mode as simple xml throws a wobbly if you don't.
			if(ini_get('zend.ze1_compatibility_mode') == 1)
			{
				ini_set('zend.ze1_compatibility_mode', 0);
			}

			if($xml == null)
			{
				$xml = simplexml_load_string("<?xml version='1.0' ?>$rootNodeName");
			}

			// loop through the data passed in.
			foreach($data as $key => $value)
			{
				// no numeric keys in our xml please!
				if(is_numeric($key))
				{
					// make string key...
					$key = "unknownNode" . (string) $key;
				}

				// if there is another array found recrusively call this function
				if(is_array($value))
				{
					$key = $elem_root; //transform ipid key into $elem_root
					$node = $xml->addChild($key);
					// recrusive call.
					$this->toXml($value, $rootNodeName, $node);
				}
				else
				{
					// add single node.
					$value = html_entity_decode($value, ENT_QUOTES, "utf-8");
					$xml->addChild($key, $value);
				}
			}
			// pass back as string. or simple xml object if you want!

			return $xml->asXML();
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}
		
		private function retain_values($values, $prefix = '')
		{
			foreach($values as $key => $val)
			{
				if(!is_array($val))
				{
					$this->view->$key = $val;
				}
				else
				{
					//retain 1 level array used in multiple hospizvbulk form
					foreach($val as $k_val => $v_val)
					{
						if(!is_array($v_val))
						{
							$this->view->{$prefix . $key . $k_val} = $v_val;
						}
					}
				}
			}
		}
		
		public function pricelisthessendtaAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$p_lists = new PriceList();
			$p_hessen = new PriceHessenDta();

			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();
			$default_dta_ids = Pms_CommonData::get_he_dta_default_ids();

			if($_REQUEST['list'])
			{
				//check if the list belongs to this client
				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "error/previlege");
				}
			}

			$this->view->listid = $list;
			$this->view->shortcuts_hessen = $shortcuts['hessen_dta'];

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$form = new Application_Form_ClientPriceList();

				if($_POST['save_prices'])
				{
					foreach($_POST as $key_table => $post_data)
					{
						if($key_table != 'save_prices')
						{
							$save_function = 'save_prices_' . $key_table;

							$insert = $form->$save_function($post_data, $list);
						}
					}
					//avoid resubmit post
					$this->_redirect(APP_BASE . "misc/pricelisthessendta?list=" . $list);
					exit;
				}
			}

			$price_hessen = $p_hessen->get_prices($list, $clientid, $this->view->shortcuts_hessen, $default_price_list['hessen_dta'], $default_dta_ids['hessen_dta']);

			$this->view->price_hessen = $price_hessen;
		}

		
		public function populategrundAction(){
		    
   		    $this->_helper->layout->setLayout('layout');
   		    $this->_helper->viewRenderer->setNoRender();

		    if( $_REQUEST['execute'] == "all" || $_REQUEST['execute'] == "new" ){

		        
    		    // get all clients
    		    $pt = Doctrine_Query::create()
    		    ->select("id,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
    		    			->from('Client')
    		    			->where('isdelete=0')
    		    			->orderBy("id ASC");
    		    $ptarray = $pt->fetchArray();
    		    
    		    
    		    if($ptarray)
    		    {
    		        foreach($ptarray as $key => $val)
    		        {
    		            $clients[$val['id']] = $val['client_name'];
    		        }
    		    }
    		    
    		    $titlearray = array(
    		        "1" => "Allgemein",
    		        "2" => "Hausbesuch in Privatwohnung",
    		        "3" => "Besuch im Krankenhaus /Palliativstation",
    		        "4" => "Besuch in stationärer Pflegeeinrichtung",
    		        "5" => "Besuch in stat. Pflegeeinrichtung / Hospiz",
    		        "6" => "Besuch in Arztpraxis",
    		        "7" => "Beratung im Büro",
    		        "8" => "Sitzwache",
    		        "9" => "Telefonate/ E-Mails/Briefe",
    		        "10" => "Beratung im Klinikum",
    		        "11" => "Trauerbegleitung",
    		        "12" => "Palliativklinik-Präsenz"
    			
    		    );
    		    
    		    foreach($clients as  $cl_id => $cl_name){
    		        if( $cl_id != "0" ){
        		        foreach($titlearray as $type_id =>$type_name){
        		            $new_array[] = array(
        		                "clientid"=>$cl_id,
        		                "grund"=>$type_name,
        		                "old_id"=>$type_id
        		            );
        		        }
    		        }
    		    }
    		    $collection = new Doctrine_Collection('HospizVisitsTypes');
    		    $collection->fromArray($new_array);
    		    $collection->save();

    		    $this->_redirect(APP_BASE . "misc/populategrund");
    		    exit;
    		    
    		} else {
    		    echo  "please check request";
    		    exit;
    		}
	    }
	    

		public function updategrundAction(){
		    
   		    $this->_helper->layout->setLayout('layout');
   		    $this->_helper->viewRenderer->setNoRender();

       		if( $_REQUEST['update'] == "all"){
       		        
       		    // get all patient visits
       		    $drop = Doctrine_Query::create()
       		    ->select('ipid')
       		    ->from('PatientHospizvizits')
       		    ->where('isdelete="0" ');
       		    $droparray = $drop->fetchArray();
    
       		    
       		    foreach($droparray as $k=>$ipids){
       		        if($ipids['ipid']){
           		        $patient_ipids[] =$ipids['ipid'];
       		        }
       		    }
    
       		    // get ipids per clients 
       		    $actpatient = Doctrine_Query::create()
      		    ->select("ipid,clientid")
       		    ->from('EpidIpidMapping')
       		    ->whereIn('ipid',$patient_ipids);
       		    $actipidarray = $actpatient->fetchArray();
       		    
       		    foreach($actipidarray as $ek=>$epat){
       		        $patient_client[$epat['ipid']] = $epat['clientid'];
       		        $client_ipids[$epat['clientid']][] = $epat['ipid']; 
       		    }
       		    
       		    
       		    // existing grund
       		    $titlearray = array(
       		        "1" => "Allgemein",
       		        "2" => "Hausbesuch in Privatwohnung",
       		        "3" => "Besuch im Krankenhaus /Palliativstation",
       		        "4" => "Besuch in stationärer Pflegeeinrichtung",
       		        "5" => "Besuch in stat. Pflegeeinrichtung / Hospiz",
       		        "6" => "Besuch in Arztpraxis",
       		        "7" => "Beratung im Büro",
       		        "8" => "Sitzwache",
       		        "9" => "Telefonate/ E-Mails/Briefe",
       		        "10" => "Beratung im Klinikum",
       		        "11" => "Trauerbegleitung",
       		        "12" => "Palliativklinik-Präsenz"
       		    );
       		    
       		    // get new grund ids
       		    $type_q = Doctrine_Query::create()
       		    ->select("*")
       		    ->from('HospizVisitsTypes')
       		    ->where('isdelete=0');
       		    $type_array = $type_q->fetchArray();
       		    
       		    foreach($type_array as $k=>$tar){
       		        $grund_type[$tar['clientid']][$tar['old_id']] = $tar['id']; 
       		    }
       		    //UPDATE 
       		    foreach( $client_ipids as $client_id => $ipids){
       		        foreach($titlearray as $old_id =>$name){
       		            if(!empty($ipids)){
                   		    $sph = Doctrine_Query::create()
                   		    ->update('PatientHospizvizits')
                   		    ->set('grund', $grund_type[$client_id][$old_id])
                   		    ->whereIn("ipid", $ipids)
                   		    ->andWhere("grund = ". $old_id);
                  		    $sph->execute();
//                    		    echo $sph->getSqlQuery();
//                    		    echo "\n"; 
       		            }
       		        }
       		    }
//        		    exit;
       		    $this->_redirect(APP_BASE . "misc/updategrund?SUCCESS=1");    		    
        	} else {
    		    echo  "please check request";
    		    exit;
    		    
    		}
	    }

	    
		
		public function populatefamdegreeAction(){
		    
   		    $this->_helper->layout->setLayout('layout');
   		    $this->_helper->viewRenderer->setNoRender();

		    if( $_REQUEST['execute'] == "all" || $_REQUEST['execute'] == "new" ){

		        
    		    // get all clients
    		    $pt = Doctrine_Query::create()
    		    ->select("id,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
    		    			->from('Client')
    		    			->where('isdelete=0')
    		    			->orderBy("id ASC");
    		    $ptarray = $pt->fetchArray();
    		    
    		    
    		    if($ptarray)
    		    {
    		        foreach($ptarray as $key => $val)
    		        {
    		            $clients[$val['id']] = $val['client_name'];
    		        }
    		    }
    		    
    		    $fdoc = Doctrine_Query::create()
    		    ->select($sql)
    		    ->from('FamilyDegree')
    		    ->where('isdelete=0')
    		    ->orderBy('id ASC');
    		    $loc = $fdoc->execute();
   		        $fmdarray = $loc->toArray();
    		    
   		        foreach($fmdarray as $k=>$fm_data){
   		            $titlearray[$fm_data['id']] = $fm_data['family_degree'];
   		        }
    		    
    		    foreach($clients as  $cl_id => $cl_name){
    		        if( $cl_id != "0" ){
        		        foreach($titlearray as $type_id =>$type_name){
        		            $new_array[] = array(
        		                "clientid"=>$cl_id,
        		                "family_degree"=>$type_name,
        		                "old_id"=>$type_id
        		            );
        		        }
    		        }
    		    }
    		    $collection = new Doctrine_Collection('FamilyDegree');
    		    $collection->fromArray($new_array);
    		    $collection->save();

    		    $this->_redirect(APP_BASE . "misc/populatefamdegree");
    		    exit;
    		    
    		} else {
    		    echo  "please check request";
    		    exit;
    		}
	    }
	    

		public function updatefamdegreeAction(){
   		   	set_time_limit(0);
   		   	
   		    $this->_helper->layout->setLayout('layout');
   		    $this->_helper->viewRenderer->setNoRender();

   		    
       		if( $_REQUEST['update'] == "all"){
       		        
       		    // get ipids per clients 
       		    $actpatient = Doctrine_Query::create()
      		    ->select("ipid,clientid")
       		    ->from('EpidIpidMapping');
       		    $actipidarray = $actpatient->fetchArray();
       		    
       		    foreach($actipidarray as $ek=>$epat){
       		        $patient_client[$epat['ipid']] = $epat['clientid'];
       		        $client_ipids[$epat['clientid']][] = $epat['ipid']; 
       		    }
       		    
       		    // existing family degree
       		   $fdoc = Doctrine_Query::create()
    		    ->select($sql)
    		    ->from('FamilyDegree')
    		    ->where('isdelete=0')
    		    ->andWhere('clientid = 0')
    		    ->orderBy('id ASC');
    		    $loc = $fdoc->execute();
   		        $fmdarray = $loc->toArray();
    		    
   		        foreach($fmdarray as $k=>$fm_data){
   		            $titlearray[$fm_data['id']] = $fm_data['family_degree'];
   		        }
       		    
       		    // get new family degree ids
       		    $type_q = Doctrine_Query::create()
       		    ->select("*")
       		    ->from('FamilyDegree')
       		    ->where('isdelete=0');
       		    $type_array = $type_q->fetchArray();
       		    
       		    foreach($type_array as $k=>$tar){
       		        $fd_type[$tar['clientid']][$tar['old_id']] = $tar['id']; 
       		    }
       		    //UPDATE 
       		    foreach( $client_ipids as $client_id => $ipids){
       		        foreach($titlearray as $old_id =>$name){
       		            if(!empty($ipids)){
                   		    $sph = Doctrine_Query::create()
                   		    ->update('ContactPersonMaster')
                   		    ->set('cnt_familydegree_id', '"'.$fd_type[$client_id][$old_id].'"')
                   		    ->set('old_familydegree_id', '"'.$old_id.'"')
                   		    ->whereIn("ipid", $ipids)
                   		    ->andWhere("cnt_familydegree_id != 0 ")
                   		    ->andWhere('cnt_familydegree_id = "'.$old_id.'"' );
                  		    $sph->execute();
//                    		    echo $sph->getSqlQuery();
//                    		    echo "\n"; 
       		            }
       		        }
       		    }
       		    $this->_redirect(APP_BASE . "misc/updatefamdegree?SUCCESS=1");    		    
        	} else {
    		    echo  "please check request";
    		    exit;
    		    
    		}
	    }

	    
	    
	    public function populatevwcolorstatusesAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	        
	        // get all color statuses existing
	        $ext_statuses_q = Doctrine_Query::create()
        	        ->select('*')
        	        ->from('VwColorStatuses'); 
	        $ext_statuses_array = $ext_statuses_q->fetchArray();
	        
	        $have_statuses = array();
	        foreach($ext_statuses_array as $ks =>$vw_st){
	            if(!in_array($vw_st['vw_id'],$have_statuses)){
    	            $have_statuses[] = $vw_st['vw_id'];
	            }
	        }
	        
	        $fdoc1 = Doctrine_Query::create()
        	        ->select('*')
        	        ->from('Voluntaryworkers')
        	        ->where("isdelete = 0  ")
        	        ->andWhere("indrop = 0  ")
        	        ->andWhere("status_color in('y','r','b')  ")
        	        ->andWhereNotIn("id",$have_statuses);
	        $fdocarray = $fdoc1->fetchArray();
	         
	        foreach($fdocarray as $kv =>$vw_data){
	            
	            if($vw_data['create_date'] != "0000-00-00 00:00:00"){
	                $arr_status_color[$vw_data['id']]['start_date'] = date("Y-m-d 00:00:00",strtotime($vw_data['create_date']));
	            } else  if($vw_data['change_date'] != "0000-00-00 00:00:00"){
	                $arr_status_color[$vw_data['id']]['start_date'] = date("Y-m-d 00:00:00",strtotime($vw_data['change_date']));
	            } else{
	                $arr_status_color[$vw_data['id']]['start_date'] = "";
	            }
	            
	            $statuses_array[] = array(
	                'vw_id' => $vw_data['id'],
	                'clientid' => $vw_data['clientid'],
	                'status' => $vw_data['status_color'],
	                'start_date' => $arr_status_color[$vw_data['id']]['start_date'],
	                'create_date' => date('Y-m-d H:i:s'),
	                'create_user' => "338"
	            );
	            
	        }

	        if($_REQUEST['dbg'] == "1"){
	            print_r("\n all vws that already have statuses \n ");
	            print_r($have_statuses);
    	        
	            print_r("\n new data to be entered \n ");
	            print_r($statuses_array);

	            print_r("\n ");
	            print_r("\n ");
	            print_r("\n ");
	            print_r("\n ");
	            exit;
	        }	        
	        
	        
	        if($_REQUEST['populate'] == "1" && !empty($statuses_array)){
	            $collection = new Doctrine_Collection('VwColorStatuses');
	            $collection->fromArray($statuses_array);
	            $collection->save();
	        }
	        
	    }

	    



        //    ISPC-1625  Datei folders
        /**
         * update 17.04.2018 ISPC-2176
         * list files for all associated client from /client/associateclientfiles
         */
	    public function clientfileslistAction()
	    {
	        $this->_helper->layout->setLayout('layout_ajax');
	        
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        
	        $folder = $this->getRequest()->getParam('folder');
	        $folder_id = ( ! empty($folder) && $folder != "all") ? $folder : false;
	        
	        $associated_tab = $this->getRequest()->getParam('associated_tab', false);
	        
	        //verify if requested client is associated with the loghed-in
	        $cid = $this->getRequest()->isPost() ? $this->getRequest()->getPost('cid') : $this->getRequest()->getParam('cid');
	        
	        if ($folder == "all" && $associated_tab){
	            //get the filed from associated cleints of this $loghedin
	        }
	        elseif ($cid) {
	            $post_clientid = Pms_Uuid::decrypt($cid);
	        
	            $acf_obj = new AssociatedClientFiles();
	        
	            if($acf_obj->assert_associated($post_clientid)) {
	        
	                //all ok, change the $clientid for witch we save the file
	                $clientid = $post_clientid;
	        
	                //$post_clientid_data = Client::getClientDataByid($clientid);
	                //$file_password = $post_clientid_data[0]['fileupoadpass'];
	        
	            } else {
	                
	                if ($this->getRequest()->isXmlHttpRequest()) {
	                    throw new Zend_Exception('ClientFiles not associated ! admin must verify this error.', 1);
	                } else {
	                    $this->redirect( "overview/overview", array(
	                        "exit" => true,
	                        "prependBase" => true,
	                    ));
	                }
	                 
	                exit; //for readability
	                /*
	                 * you can reach this if delete file then change client( get params remain here)
	                 * or the posted cid is not associated ... then you have a xhr and user dosen't see the redirect
	                 */
	            }
	        }
	        $this->view->cid = $clientid;
	        
	        $files = new ClientFileUpload();
	       
	        //get the filed from associated cleints of this $loghedin
	        if ($folder == "all" && $associated_tab){
	            //get the filed from associated cleints of this $loghedin
    	        $acf_obj = new AssociatedClientFiles();
    	        $associate_clients = $acf_obj->fetchAssociatedClients($this->logininfo->clientid);
    	        
    	        
    	        
    	        $associated_client_IDs = array();
    	        if ( ! empty($associate_clients)) {
    	            foreach ($associate_clients as $groupid=>$clients) {    	                 
    	                $associated_client_IDs = array_merge($associated_client_IDs, array_column($clients, 'clientid'));
    	            }
    	            $associated_client_IDs = array_flip(array_unique($associated_client_IDs));    	            
    	            unset($associated_client_IDs[$this->logininfo->clientid]);
    	            $associated_client_IDs = array_keys($associated_client_IDs);    
    	        }
    	        
    	        $all_filesData = array();
    	        $allUsersArray = array();
    	        foreach ($associated_client_IDs as $clientid) {
    	            
    	            $filesData = $files->getClientFiles($clientid, false, 0);
    	            $filesData = array_filter($filesData, function($file) {
    	                return  ! $file['isdeleted'];
    	            });
	                $all_filesData = array_merge($all_filesData, $filesData);
	                
	                
	                $users = User::get_AllByClientid($clientid);
	                $allUsersArray += array_column($users, 'nice_name', 'id');
	                
    	        } 
    	        $filesData = $all_filesData;
    	        
    	        $client_folders = '0';
    	        
	        } else {
	            
	            $filesData = $files->getClientFiles($clientid, false, $folder_id);
	            
	            $users = User::get_AllByClientid($clientid);
	            $allUsersArray = array_column($users, 'nice_name', 'id');
	            
	            //ISPC-2434 Lore 20.08.2019
	            $client_folders = Doctrine_Query::create()
	            ->select("*, AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name, LOWER( CONVERT( AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') USING 'utf8' )) as foldername")
	            ->from('ClientFilesFolders indexBy id')
	            ->where('clientid = ? ' , $clientid)
	            ->andWhere('isdelete = 0')
	            ->orderby('foldername ASC')
	            ->fetchArray()
	            ;
	        }
	        
	        
	       
	        $this->view->filesData = $filesData;
	        
	        
	        $this->view->showInfo = $logininfo->showinfo;
	    
	        
	        $this->view->allusers = $allUsersArray;
	        
	        $this->view->client_folders = $client_folders;
	        
	        $this->view->associated_tab = $associated_tab;
	        
	        if ($this->getRequest()->isPost())
	        {
	        	
	        	$has_edit_permissions = Links::checkLinkActionsPermission();
	        	
	        	if(!$has_edit_permissions) // if canedit = 0 - don't allow any changes
	        	{
	        		$this->_redirect(APP_BASE . "error/previlege");
	        		exit;
	        	}
	        	
	            if ( isset($_POST['files']) && is_array($_POST['files']) && $_POST['folder_association'] != "-1") {
           
                    $update = Doctrine_Query::create()
                    ->update('ClientFileUpload')
                    ->set('folder', '?', $_POST['folder_association'])
                    ->whereIn('id', array_values($_POST['files']))
                    ->andWhere('clientid = ?', $clientid)
                    ->execute();

                    /*
	                foreach($_POST['files'] as $key => $val)
                    {
                        $update = Doctrine_Query::create()
                        ->update('ClientFileUpload')
                        ->set('folder', '?', $_POST['folder_association'])
                        ->where('id = ?', $val)
                        ->andWhere('clientid = ?', $clientid)
                        ->execute();
                        
                    }
                    */
                    
                    $this->view->error_message = $this->view->translate('clientfilemovetofolder');
	            }
	            
   	            //$this->_redirect(APP_BASE . "misc/uploadfiles?tabs_data_cid=" . Pms_Uuid::encrypt($clientid));
   	            
   	            $this->redirect( "misc/uploadfiles?tabs_data_cid=" . Pms_Uuid::encrypt($clientid), array(
   	                "exit" => true,
   	                "prependBase" => true,
   	            ));
   	            
   	            exit; //for readability
	            
	        }
	    }
	    
	    
	    public function clientfilesfolderAction()
	    {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	         
	        if($this->getRequest()->isPost())
	        {
	            $previleges = new Pms_Acl_Assertion();
	            $return = $previleges->checkPrevilege('createfolder', $logininfo->userid, 'canadd');
	            if(!$return)
	            {
	                $this->_redirect(APP_BASE . "error/previlege");
	            }
	    
	            $folder_form = new Application_Form_ClientFilesFolders();
	    
	            if($folder_form->validatefolder($_POST))
	            {
	                $folder_form->InsertFolderData($_POST);
	                $this->view->error_message = $this->view->translate('foldercreated');
	            }
	            else
	            {
	                $folder_form->assignErrorMessages();
	            }
	        }
	    
	    
	        if($_GET['flg'] == 'suc')
	        {
	            $error_message = $this->view->translate('folderdelete');
	        }
	        else if($_GET['flg'] == 'err')
	        {
	            $error_message = $this->view->translate('cannotdeletefolder');
	        }
	    
	        //ISPC-2434 Lore 20.08.2019
	        $folder_q = Doctrine_Query::create()
	        ->select("*, AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name, LOWER( CONVERT( AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') USING 'utf8' )) as foldername")
	        ->from('ClientFilesFolders')
	        ->where('clientid = ' . $logininfo->clientid)
	        ->andWhere('isdelete = 0')
	        ->orderby('foldername ASC');
	        $folderarray = $folder_q->fetchArray();
	    
	        $this->view->folderarray = $folderarray ;
	    }
	    
	    public function editfolderAction()
	    {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	    
	        if(strlen($_GET['id']) > 0)
	        {
	            $folder_id_q = Doctrine_Query::create()
	            ->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
	            ->from('ClientFilesFolders')
	            ->where('clientid = ' . $logininfo->clientid)
	            ->andWhere('id= ?', $_GET['id']);
	            $folder_array = $folder_id_q->fetchArray();
	            
	            $this->retainValues($folder_array[0]);
	        }
	        
	        $this->_helper->viewRenderer('clientfilesfolder');
	    
	        if($this->getRequest()->isPost())
	        {
	    
	            $folder_form = new Application_Form_ClientFilesFolders();
	    
	            if($folder_form->validatefolder($_POST))
	            {
	                $folder_form->EditFolderData($_POST);
	                $this->view->error_message = $this->view->translate('folderupdated');
	                $this->retainValues($_POST);
	            }
	            else
	            {
	                $folder_form->assignErrorMessages();
	            }
	        }
	       
	        //ISPC-2434 Lore 20.08.2019
	        $folder_q = Doctrine_Query::create()
	        ->select("*, AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name, LOWER( CONVERT( AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') USING 'utf8' )) as foldername")
	        ->from('ClientFilesFolders')
	        ->where('clientid = ' . $logininfo->clientid)
	        ->andWhere('isdelete = 0')
	        ->orderby('foldername ASC');
	        $folderarray = $folder_q->fetchArray();
	        
	        $this->view->folderarray = $folderarray;
	    }
	    
	    
	    
	    public function deletefolderAction()
	    {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
 
	        $folder_form = new Application_Form_ClientFilesFolders();
	    
	        $this->_helper->viewRenderer('clientfilesfolder');
	        if($_GET['fid'] > 0)
	        {
	            
	            /// DELETE FILES 
	            if($_GET['m'] == 'all')
	            {
	                $files_update= Doctrine_Query::create()
	                ->update('ClientFileUpload')
	                ->set("isdeleted",'1')
	                ->where('folder = ?', $_GET['fid']);
	                $files_update_arr = $files_update->execute();
	                
	                
	                
	                $folder_update_q= Doctrine_Query::create()
	                ->update('ClientFilesFolders')
	                ->set("isdelete",'1')
	                ->where('id= ?', $_GET['fid']);
	                $folder_update_arr = $folder_update_q->execute();
	    
	                if($folder_update_arr)
	                {
	                    $this->_redirect(APP_BASE . 'misc/clientfilesfolder?flg=suc');
	                }
	            }
	            else
	            {
	                $message = Doctrine_Query::create()
	                ->select('*')
	                ->from('ClientFileUpload')
	                ->where('folderid = ?'. $_GET['fid']);
	                $mess = $message->execute();
	                $messagearay = $mess->toArray();
	    
	                if(count($messagearay) > 1)
	                {
	                    $this->_redirect(APP_BASE . 'misc/clientfilesfolder?flg=err');
	                }
	                else
	                {
	                    $delete = Doctrine_Query::create()
	                    ->update('ClientFilesFolders')
	                    ->set("isdelete",'1')
	                    ->where('id= ?', $_GET['fid']);
	                    $delexec = $delete->execute();
	                    if($delexec)
	                    {
	                        $this->_redirect(APP_BASE . 'misc/clientfilesfolder?flg=suc');
	                    }
	                }
	            }
	        }
	    }

	    public function addnewlocationsAction(){
	    
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        if( $_REQUEST['execute'] == "all" || $_REQUEST['execute'] == "new" ){
	    
	    
	            // get all clients
	            $pt = Doctrine_Query::create()
	            ->select("id,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
	            ->from('Client')
	            ->where('isdelete=0')
	            ->orderBy("id ASC");
	            $ptarray = $pt->fetchArray();
	            if($ptarray)
	            {
	                foreach($ptarray as $key => $val)
	                {
	                    $clients[$val['id']] = $val['client_name'];
	                }
	            }
	    
	            $locations_array = array(
	                array("location" => Pms_CommonData::aesEncrypt('zu Hause, alleine'),"sub_type"=>"alone"),
	                array("location" => Pms_CommonData::aesEncrypt('zu Hause, mit Angehörigen'),"sub_type"=>"with_relatives"),
	                
	            );
	            
	    
	            foreach($clients as  $cl_id => $cl_name){
	                if( $cl_id != "0" ){
	                    foreach($locations_array as $loc_key =>$loc_data){
	                        $new_array[] = array(
	                            "client_id"=>$cl_id,
	                            "location"=>$loc_data['location'],
	                            "location_type"=>"5",
	                            "location_sub_type"=>$loc_data['sub_type']
	                        );
	                    }
	                }
	            }
	            
	            $collection = new Doctrine_Collection('Locations');
	            $collection->fromArray($new_array);
	            $collection->save();
	    
	            $this->_redirect(APP_BASE . "misc/addnewlocations");
	            exit;
	    
	        } else {
	            echo  "please check request";
	            exit;
	        }
	    }
	     
	    
	    
	    public function updatekarnofskyAction()
	    {
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        set_time_limit(0);
	        if( $_REQUEST['execute'] == "all"){
	    
        	    //get all contact forms
        	     
	            $pt = Doctrine_Query::create()
	            ->select("*")
	            ->from('ContactForms')
	            ->andWhere('ecog > 0 ')
	            ->orderBy("id ASC");
	            $ptarray = $pt->fetchArray();
	            
	            if($ptarray)
	            {
	                foreach($ptarray as $key => $val)
	                {
	                    $ref = Doctrine::getTable('ContactForms')->find($val['id']);
	                    
	                    if($ref['ecog'] == "1"){
	                        $ref->karnofsky = "80";
	                    }
	                    elseif($ref['ecog'] == "2"){
	                        $ref->karnofsky = "60";
	                        
	                    }
	                    elseif($ref['ecog'] == "3"){
	                        $ref->karnofsky = "40";
	                        
	                    }
	                    elseif($ref['ecog'] == "4"){
	                        $ref->karnofsky = "20";
	                    }
	                    elseif($ref['ecog'] == "5"){
	                        $ref->karnofsky = "0";
	                    }
	                     
	                    $ref->save();
	                }
	            }
 
	            $this->_redirect(APP_BASE . "misc/updatekarnofsky");
	            exit;
	    
	        } else {
	            echo  "please check request";
	            exit;
	        }
	    }
	    
	    public function updatekarnofskysapvAction()
	    {
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        set_time_limit(0);
	        if( $_REQUEST['execute'] == "all"){
	    
        	    //get all contact forms
        	     
	            $pt = Doctrine_Query::create()
	            ->select("*")
	            ->from('SapvEvaluationMsp1');
	            $ptarray = $pt->fetchArray();
	            
	            if($ptarray)
	            {
	                foreach($ptarray as $key => $val)
	                {
	                    $ref = Doctrine::getTable('SapvEvaluationMsp1')->find($val['id']);
                        $ref->akps_old = $ref['akps'];
                        
                        
	                    if($ref['akps'] == "1"){
	                        $ref->akps = "100";
	                    }
	                    elseif($ref['akps'] == "2"){
	                        $ref->akps = "90";
	                    }
	                    elseif($ref['akps'] == "3"){
	                        $ref->akps = "80";
	                    }
	                    elseif($ref['akps'] == "4"){
	                        $ref->akps = "70";
	                    }
	                    elseif($ref['akps'] == "5"){
	                        $ref->akps = "60";
	                    }
	                    elseif($ref['akps'] == "6"){
	                        $ref->akps = "50";
	                    }
	                    elseif($ref['akps'] == "7"){
	                        $ref->akps = "40";
	                    }
	                    elseif($ref['akps'] == "8"){
	                        $ref->akps = "30";
	                    }
	                    elseif($ref['akps'] == "9"){
	                        $ref->akps = "20";
	                    }
	                    elseif($ref['akps'] == "10"){
	                        $ref->akps = "10";
	                    } 
	                    elseif($ref['akps'] == "0"){
	                        $ref->akps = NULL;
	                    }
	                    $ref->save();
	                }
	            }
	            $this->_redirect(APP_BASE . "misc/updatekarnofskysapv");
	            exit;
	    
	        } else {
	            echo  "please check request";
	            exit;
	        }
	    }
	    
	    
	    public function medicationdosageAction(){
	        
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	         
	        set_time_limit(0);
	        
	        /* $qpa1 = Doctrine_Query::create()
	        ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
	        ->from('PatientCourse')
	        ->where('wrongcomment!=1  and 	course_type="' . addslashes(Pms_CommonData::aesEncrypt("M")) . '" and 	AES_DECRYPT(course_title,"' . Zend_Registry::get('salt') . '")   LIKE "%Änderung:%" ')
	        ->andWhere('DATE(create_date) > "2016-02-01"')
	        ->orderBy("convert(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
	        echo $qpa1->getSqlQuery(); exit;
	        $qparray = $qpa1->fetchArray(); */
	    }
	    
	    
	    public function  createnewcontactformAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	        
	        set_time_limit(0);
	         
	        $form_blocks_order_form = new Application_Form_FormBlocksOrder();
	        $form_types_blocks_form = new Application_Form_FormBlocks2Type();
	        $form_blocks_options_form = new Application_Form_FormBlocksOptions();
	        
	        
	        $fdoc1 = Doctrine_Query::create()
	        ->select('*')
	        ->from('FormTypes');
	        $fdocarray = $fdoc1->fetchArray();
	        
	        
	        $clients_cfs = array();
	        foreach( $fdocarray as $k=>$cfs){
	            if(!in_array($cfs['clientid'],$clients_cfs)){
    	            $clients_cfs[] = $cfs['clientid'];
	            }
	        }

	        
	        
	        $post = array(
	            'order' => array(
	                
	                "0" => "time",
	                "1" => "drivetime",
	                "2" => "symp",
	                "3" => "med",
	                "4" => "com",
	                "5" => "com_ph",
	                "6" => "anam",
	                "7" => "ebm",
	                "8" => "goa",
	                "9" => "sgbv",
	                "10" => "ecog",
	                "11" => "befund",
	                "12" => "careinstructions",
	                "13" => "visitplan",
	                "14" => "internalcomment",
	                "15" => "classification",
	                "16" => "ebmii",
	                "17" => "goaii",
	                "18" => "bra_sapv",
	                "19" => "additional_users",
	                "20" => "sgbxi",
	                "21" => "measures",
	                "22" => "befund_txt",
	                "23" => "free_visit",
	                "24" => "symp_zapv",
	                "25" => "symp_zapv_complex",
	                "26" => "therapy",
	                "27" => "sgbxi_actions",
	                "28" => "ebm_ber",
	                "29" => "service_entry",
	                "30" => "vital_signs",
	                "31" => "bowel_movement",
	                "32" => "hospiz_imex",
	                "33" => "hospiz_medi",
	                "34" => "ipos",
	                "35" => "drivetime_doc",
	                "36" => "lmu_visit",
	                "37" => "lmu_pmba_body",
	                "38" => "lmu_pmba_pain",
	                "39" => "med_time_dosage",
	                "40" => "lmu_pmba_wishes",
	                "41" => "lmu_pmba_aufklaerung",
	                "42" => "todos"
	            ),
	            
	            "open" => Array
	            (
	                "time" => "1",
	            ),
	            
	            "assign" => Array
	            (
	                "ipos" => "1",
	                "drivetime_doc" => "1",
	                "lmu_visit" => "1",
	                "lmu_pmba_body" => "1",
	                "lmu_pmba_pain" => "1",
	                "lmu_pmba_wishes" => "1",
	                "lmu_pmba_aufklaerung" => "1",
	                "todos" => "1"
	            )
            
	        );
	        

	        if($_REQUEST['execute'] == "yes"){
	            
    	        foreach($clients_cfs as $clientid){
        	        $insert = new FormTypes();
        	        $insert->clientid = $clientid;
        	        $insert->name = "Basisassessment";
        	        $insert->action = "0";
        	        $insert->save();
        	        
        	        $form_type_id = $insert->id;
        	        
        	        if($form_type_id){
            	        $save_assignation = $form_types_blocks_form->assign_form_blocks($clientid, $form_type_id, $post);
            	        $save_open_blocks = $form_blocks_options_form->open_form_blocks($clientid, $form_type_id, $post);
            	        $save_blocks_order = $form_blocks_order_form->save_form_blocks($clientid, $form_type_id, $post['order']);
        	        } 
    	        }
    	        $this->_redirect(APP_BASE . "misc/createnewcontactform");
    	        exit;
	        } 
	        else
	        {
	            echo "check request";
    	        exit;
	        }
	    }
	    
	    
	    public function medcourseupdateAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	         
	        set_time_limit(0);
	        
	        
	        $fdoc1 = Doctrine_Query::create()
	        ->select("id,ipid, recordid,create_date,
	            AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
	            AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, 
	            substring_index(substring_index(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'), 'Änderung:', -1),'->', 1)  as old_entry, 
	            replace(substring_index(substring_index(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'), 'Änderung:', -1),'->', 1) ,' ','') as old, 
                substring_index(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'), '-> ', -1) as new_entry,
                replace(substring_index(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'), '-> ', -1),' ','') as new
	            ")
	        ->from('PatientCourse')
	        ->where(" AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "')  LIKE 'Änderung:%->%' ")
	        ->andWhere(" AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') in ('M','N','I') ")
	        
// 	        ->andWhere("ipid ='cf904627ecce7e2d4bf8d189cd87f398640174f4'")
	        
	        ->andWhere("replace(substring_index(substring_index(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'), 'Änderung:', -1),'->', 1) ,' ','') = replace(substring_index(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'), '-> ', -1),' ','') ")
	        ->andWhere("DATE(create_date) > '2016-02-01' ")
	        ->andWhere("create_user != '338' ")
			->andWhere('source_ipid = ""')
	        ->orderBy('create_date ASC');
// 	        echo $fdoc1->getSqlQuery(); exit;
	        $fdocarray = $fdoc1->fetchArray();
	        
	        $records_array[] = "9999999999";
	        foreach($fdocarray as $k=>$pc_data){
	            $resulted[$pc_data['id']] =$pc_data;
	            $records_array[] = $pc_data['recordid'];
	            $changed_entry[$pc_data['recordid']][date("Y-m-d H:i",strtotime($pc_data['create_date']))][] = $pc_data['new_entry']; 
	        }
	        
            
            // go through patient drugplan dosage
            
	        $pdd_q = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosageHistory')
	        ->whereIn("pdd_drugplan_id",$records_array);
	        $pdd_array= $pdd_q->fetchArray();
	        
	        foreach($pdd_array as $kpdd=>$pdd){
                $dosage_history[$pdd['history_id']][$pdd['pdd_drugplan_id']][]  = $pdd['pdd_dosage']; 
	        }
 
	        
	        $pdh_q = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanHistory')
	        ->whereIn("pd_id",$records_array);
	        $pdh_array= $pdh_q->fetchArray();

	        foreach($pdh_array as $k=>$pdh_data){
	            $all_history_entry[$pdh_data['pd_id']][] = $pdh_data;
	        }

	        
	        
	        
	        foreach($all_history_entry as $drug_id=>$hy_data){
               foreach($hy_data as $k=>$data){
                   if( strlen($data['pd_comments']) > 0 ){
                       $data_comment = " | ".$data['pd_comments'];
                   } else{
                       $data_comment  = "";
                   }
                   
                   if($data['pd_medication_change'] != "0000-00-00 00:00:00"){
                       $pdata_medication_change = date("d.m.Y",strtotime($data['pd_medication_change']));
                   } elseif($data['pd_change_date'] != "0000-00-00 00:00:00"){
                       $pdata_medication_change = date("d.m.Y",strtotime($data['pd_change_date']));
                   } else{
                       $pdata_medication_change = date("d.m.Y",strtotime($data['pd_create_date']));
                   }

                   if(!empty($dosage_history[$data['id']][$data['pd_id']])){
                       $dosage = implode("-",$dosage_history[$data['id']][$data['pd_id']]);
                   } else{
                       $dosage = $data['pd_dosage'];
                   }
                   
	               $hy_entry[$drug_id][date("Y-m-d H:i",strtotime($data['create_date']))] = $data['pd_medication_name']." | ".$dosage .$data_comment ." | ".$pdata_medication_change;
                }
	        }
	         
	        
	        foreach($resulted as $dr_id=>$cours_entry_data){
	            if($hy_entry[$cours_entry_data['recordid']][date("Y-m-d H:i",strtotime($cours_entry_data['create_date']))]){
	                $resulted_old[$dr_id]['real_old_entry'] = $hy_entry[$cours_entry_data['recordid']][date("Y-m-d H:i",strtotime($cours_entry_data['create_date']))];
	                if(str_replace(' ','',$resulted_old[$dr_id]['real_old_entry']) != str_replace(' ','',$cours_entry_data['old_entry']) ){
    	                $resulted[$dr_id]['real_course_title'] = 'Änderung: '.$resulted_old[$dr_id]['real_old_entry'].' -> '.$cours_entry_data['new_entry'];  
	                }
	            } 
	        }
	        
	        foreach($resulted as $course_id => $course_values){
	            
	            if(strlen($course_values['real_course_title']) > 0 ){
	                $dbg_resulted[] = $resulted[$course_id];
	                $correct_course = 'AES_ENCRYPT("'.addslashes($course_values["real_course_title"]).'","' . Zend_Registry::get('salt') . '")';
	                
	                echo 'UPDATE patient_course SET course_title = '.$correct_course.' WHERE (id = "'.$course_id.'");';
	                print_r("\n");
	            }
	        }
	        print_r($dbg_resulted); exit;
	        
	        $correct_course="";
	        /* foreach($resulted as $course_id =>$course_values){
	            
	            if(strlen($course_values['real_course_title']) > 0 ){
    	                
    	            $correct_course = 'AES_ENCRYPT("'.addslashes($course_values["real_course_title"]).'","' . Zend_Registry::get('salt') . '")';
    
    	            $sph = Doctrine_Query::create()
    	            ->update('PatientCourse')
    	            ->set('course_title', $correct_course)
    	            ->where("id='" . $course_id . "'");
//     	            $sph->execute();
                   echo $sph->getSqlQuery().";"; 
                   print_r("\n"); 
	            }
	        } */
	        
	        echo "EXECUTED";
	        var_dump($resulted); 
            exit;
 
	        
	    }
	    
	    public function  updatesapvevaluationAction(){

	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	        
	        set_time_limit(0);
	         
	        
	        // get all saved sapp_evaluation
	        
	        //Saved data
	        $m_sapv_evaluation = new  SapvEvaluation();
	        
	        $sp = Doctrine_Query::create()
	        ->select('*')
	        ->from('SapvEvaluation')
	        ->where('isdelete = 0')
	        ->andWhere('admissionid = 0');
	        $sparr = $sp->fetchArray();
	        
	        foreach($sparr as $k=>$sdata){
	            $ipids[] = $sdata['ipid'];
	            $sapv_evaluation[$sdata['ipid']] = $sdata;
	        }
	        
	        
	        if(!empty($ipids)){
	            $sql = 'e.epid, p.ipid, e.ipid,';
	            $conditions['periods'][0]['start'] = '2009-01-01';
	            $conditions['periods'][0]['end'] = date('Y-m-d');
	            $conditions['include_standby'] = true;
	            $conditions['ipids'] = $ipids;
	            
	            $patient_days = Pms_CommonData::patients_days($conditions,$sql);

	            foreach($patient_days as $pat_ipid => $data)
	            {
	                $adm_no = 1;
	                foreach($data['active_periods'] as $period_identification => $period_details)
	                {
	                    $admission_periods[$pat_ipid][$adm_no]['start'] = $period_details['start'];
	                    $admission_periods[$pat_ipid][$adm_no]['end'] = $period_details['end'];
	                    $adm_no++;
	                }
 
	            }
	            
	        }
	        
	        
	        if($_REQUEST['dbg'] == "1"){
	            print_R("sapv_evaluation");
	            print_R($sapv_evaluation);
	            print_R("\n admission_periods \n ");
	            print_R($admission_periods);
	            exit;
	        }
// 	            print_R($admission_periods);
// 	            exit;

	        foreach($sapv_evaluation as $sipid => $sdata)
	        {
    	        foreach($admission_periods[$sipid] as $admissionid => $periods)
    	        {
	                $ind = new SapvEvaluation();
	                $ind->ipid = $sipid;
	                $ind->status = $sdata['status'];
	                $ind->admissionid = $admissionid;
	                $ind->create_user = $sdata['create_user'];
	                $ind->create_date = $sdata['create_date'];
	                $ind->change_user = $sdata['change_user'];
	                $ind->change_date = $sdata['change_date'];
	                $ind->save();
	                
	                $new_sapv_id = $ind->id;
	                
	                $new_lines['sapv_evaluation'][] = $new_sapv_id ; 
	                
	                if($new_sapv_id){
	                    
	                    /*-----------------*/
	                    // get data for MSP1 
	                    /*-----------------*/
	                    $msp1_q = Doctrine_Query::create()
	                    ->select('*')
	                    ->from('SapvEvaluationMsp1')
	                    ->where('isdelete = 0')
	                    ->andWhere('form_id = "'.$sdata['id'].'"');
	                    $msp1_array = $msp1_q->fetchArray();
	                    
                        if(!empty($msp1_array)){
                            $msp1_data = $msp1_array[0];

                            // insert data for MSP1
                            $msp1_insert = new SapvEvaluationMsp1();
                            $msp1_insert->ipid = $sipid;
                            $msp1_insert->form_id = $new_sapv_id;
                            
                            foreach($msp1_data as $field=>$value) {
                                if($field != "id" && $field != "form_id"){
                                    $msp1_insert->$field = $value;
                                }
                                
                            }
                            $msp1_insert->save();
                            
                            $new_lines['sapv_evaluation_msp1'][] = $msp1_insert->id ;
                        }
                        
                        
	                    /*-----------------*/
	                    // get data for MSP2 
	                    /*-----------------*/
	                    $msp2_q = Doctrine_Query::create()
	                    ->select('*')
	                    ->from('SapvEvaluationMsp2')
	                    ->where('isdelete = 0')
	                    ->andWhere('form_id = "'.$sdata['id'].'"');
	                    $msp2_array = $msp2_q->fetchArray();
	                    
                        if(!empty($msp2_array)){
                            $msp2_data = $msp2_array[0];

                            // insert data for MSP2
                            $msp2_insert = new SapvEvaluationMsp2();
                            $msp2_insert->ipid = $sipid;
                            $msp2_insert->form_id = $new_sapv_id;
                            
                            foreach($msp2_data as $field_msp2=>$value_msp2) {
                                if($field_msp2 != "id"  && $field_msp2 != "form_id") {
                                    $msp2_insert->$field_msp2 = $value_msp2;
                                }
                            }
                            $msp2_insert->save();
                            $new_lines['sapv_evaluation_msp2'][] = $msp2_insert->id ;
                        }

                        
	                    /*-----------------*/
	                    // get data for IPOS1 
	                    /*-----------------*/
	                    $ipos1_q = Doctrine_Query::create()
	                    ->select('*')
	                    ->from('SapvEvaluationIpos1')
	                    ->where('isdelete = 0')
	                    ->andWhere('form_id = "'.$sdata['id'].'"');
	                    $ipos1_array = $ipos1_q->fetchArray();
	                    
                        if(!empty($ipos1_array)){
                            $ipos1_data = $ipos1_array[0];

                            // insert data for IPOS1
                            $ipos1_insert = new SapvEvaluationIpos1();
                            $ipos1_insert->ipid = $sipid;
                            $ipos1_insert->form_id = $new_sapv_id;
                            
                            foreach($ipos1_data as $field_ipos1=>$value_ipos1) {
                                if($field_ipos1 != "id"  && $field_ipos1 != "form_id") {
                                    $ipos1_insert->$field_ipos1 = $value_ipos1;
                                }
                            }
                            $ipos1_insert->save();
                            
                            $new_lines['sapv_evaluation_ipos1'][] = $ipos1_insert->id ;
                        }
                        
	                    /*-----------------*/
	                    // get data for IPOS2 
	                    /*-----------------*/
	                    $ipos2_q = Doctrine_Query::create()
	                    ->select('*')
	                    ->from('SapvEvaluationIpos2')
	                    ->where('isdelete = 0')
	                    ->andWhere('form_id = "'.$sdata['id'].'"');
	                    $ipos2_array = $ipos2_q->fetchArray();
	                    
                        if(!empty($ipos2_array)){
                            $ipos2_data = $ipos2_array[0];

                            // insert data for IPOS2
                            $ipos2_insert = new SapvEvaluationIpos2();
                            $ipos2_insert->ipid = $sipid;
                            $ipos2_insert->form_id = $new_sapv_id;
                            
                            foreach($ipos2_data as $field_ipos2=>$value_ipos2) {
                                if($field_ipos2 != "id"  && $field_ipos2 != "form_id") {
                                    $ipos2_insert->$field_ipos2 = $value_ipos2;
                                }
                            }
                            $ipos2_insert->save();
                            $new_lines['sapv_evaluation_ipos2'][] = $ipos2_insert->id ;
                        }
                        
                        
                        // marck as deleted te old one
                        $sph = Doctrine_Query::create()
                        ->update('SapvEvaluation')
                        ->set('isdelete', '1')
                        ->where("id='" . $sdata['id'] . "'");
                        $sph->execute();
	                }
    	        }
	        }

	        print_r($new_lines);
	        echo "GATA"; exit;
	    }
	    

	    public function bayernlAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	         
	        set_time_limit(0);
	        
	        $sp = Doctrine_Query::create()
	        ->select('*')
	        ->from('Sapsymptom')
	        ->where('isdelete = 0')
	        ->andWhere('visit_type = "contactform"')
	        ->andWhere('davon_fahrtzeit != "0"');
	        $sparr = $sp->fetchArray();
	        
	        foreach($sparr as $k=>$cd){
	            $cf_ids[] =  $cd['visit_id'];
	            $bayern2cf[$cd['id']] =  $cd['visit_id'];
	            $cf2bayern[$cd['visit_id']] =  $cd['id'];
	            $cf_deais[$cd['visit_id']]['bayern_total'] = $cd['gesamt_zeit_in_minuten'];
	            $cf_deais[$cd['visit_id']]['bayern_id'] = $cd['id'];
	            $cf_deais[$cd['visit_id']]['cf_id'] = $cd['visit_id'];
	        }
	        
	        if(!empty($cf_ids)){
	            
	            $cfq = Doctrine_Query::create()
	            ->select('*')
	            ->from('ContactForms')
	            ->where('isdelete = 0')
	            ->andWhere('fahrtzeit != 0')
	            ->andWhereIn('id',$cf_ids);
	            $cf_arr = $cfq->fetchArray();
	            
                

	            $groups_sql = Doctrine_Query::create()
	            ->select('*')
	            ->from('FormBlockDrivetimedoc')
	            ->whereIn('contact_form_id ',$cf_ids)
	            ->andWhere('fahrtzeit1 != 0 OR fahrt_doc1 != 0');
                $groups_sql->andWhere('isdelete = 0');
	            $groupsarray = $groups_sql->fetchArray();
	            
	            
                if(!empty($groupsarray)){
                    foreach($groupsarray as $fdtk=>$fdt_data){
                        $dt[$fdt_data['contact_form_id']] = $fdt_data;
                    }    
                }

                $driving_time = 0;
                $documentation_time = 0;
                $cf_duration = 0;
                foreach($cf_arr as $ck =>$cf_data)
                {
    	            
    	            $start_ts = strtotime($cf_data['start_date']);
    			    $end_ts = strtotime($cf_data['end_date']);
    			 
    			    $cf_duration = round(($end_ts - $start_ts) / 60);
    			    $cf_deais[$cf_data['id']]['duration'] = $cf_duration;
    
    	            if($cf_data['fahrtzeit'] != '0')
    	            {
    	                $driving_time = $cf_data['fahrtzeit'] * 2;
    	                $cf_deais[$cf_data['id']]['fahrtzeit'] =$cf_data['fahrtzeit'];
    	            }
    	            elseif($dt[$cf_data['id']]['fahrtzeit1'] != '0')
    	            {
    	                $driving_time = $dt[$cf_data['id']]['fahrtzeit1'] * 2;
    	                $cf_deais[$cf_data['id']]['fahrtzeit1'] =$dt[$cf_data['id']]['fahrtzeit1'];
    	            }
    	            else
    	            {
    	                $driving_time = '0';
    	                $cf_deais[$cf_data['id']]['fahrtzeit1'] = 0;
    	            }
    	            
    	            if(!empty($dt[$cf_data['id']]['fahrt_doc1']))
    	            {
    	                $documentation_time  = $dt[$cf_data['id']]['fahrt_doc1'];
    	                $cf_deais[$cf_data['id']]['fahrt_doc1'] = $dt[$cf_data['id']]['fahrt_doc1'];
    	            }
    	            else
    	            {
    	                $documentation_time = '0';
    	                $cf_deais[$cf_data['id']]['fahrt_doc1'] = "0";
    	            }
    	            
	               $cf_deais[$cf_data['id']]['total'] =  $cf_duration + $driving_time + $documentation_time;
                }

                $i=0;
                foreach($cf_deais as $contact_form_id => $ct_data){
                    
//                     if($contact_form_id  == $ct_data['cf_id'] && $ct_data['bayern_total'] != $ct_data['total'] ){

                    if($_REQUEST['dbg'] =='1' ){
                        print_r('contact form id'.$contact_form_id);
                        print_r("\n");
                        print_r('contact form id'.$ct_data['cf_id']);
                        print_r("\n");
                        print_r('gesamt_zeit_in_minuten '.$ct_data['bayern_total']);
                        print_r("\n");
                        print_r('cf duration '.$ct_data['duration']);
                        print_r("\n");
                        print_r('cf fahrtzeit '.$ct_data['fahrtzeit']);
                        print_r("\n");
                        print_r('cf fahrtzeit1 '.$ct_data['fahrtzeit1']);
                        print_r("\n");
                        print_r('cf fahrt_doc1 '.$ct_data['fahrt_doc1']);
                        print_r("\n");
                        print_r('NEW cf duration '.$ct_data['total']);
                        print_r("\n");
                    }
                    
                    
                    if($contact_form_id  == $ct_data['cf_id'] && $ct_data['bayern_total'] == $ct_data['duration'] && $ct_data['duration'] != $ct_data['total'] ){
                        echo '-- '.$i;
                        print_r("\n");    
                        $sql_upd = 'UPDATE sapv_symptom SET gesamt_zeit_in_minuten = "'.$ct_data['total'].'" WHERE (id = "'.$ct_data['bayern_id'].'" AND visit_id = "'.$ct_data['cf_id'].'" AND visit_type = "contactform" );';
                        echo $sql_upd;
                        print_r("\n");    
                        $i++;	            
                    }
                }
	        }
            exit;	        
	    }
	    


	    public function copyinternalactionlistAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();

	        $list="17";
	        $client="101";
	        $new_list="29";
	        
	        
	        $sp = Doctrine_Query::create()
	        ->select("*, IF(range_type = 'km', km_range_start, range_start) as range_start, IF(range_type = 'km', km_range_end, range_end) as range_end")
	        ->from('InternalInvoicesActionProducts')
	        ->andWhere("list = '" . $list . "'")
	        ->andWhere("client = '" . $client . "'")
	        ->andWhere('isdelete=0')
	        ->orderBy('id DESC');
	        $specific_products = $sp->fetchArray();

	        foreach( $specific_products as $k=>$l){
	            $products[] = $l['id'];
	            $products_details[$l['id']] = $l;
	        }
	        
	        $spa = Doctrine_Query::create()
	        ->select("*")
	        ->from('InternalInvoicesAction2Products')
	        ->whereIn('product_id', $products)
	        ->andWhere("list = '" . $list . "'")
	        ->andWhere("client = '" . $client . "'")
	        ->andWhere('isdelete = 0');
	        $specific_productsa = $spa->fetchArray();
	        
	        foreach($specific_productsa as $sk=>$av){
// 	            $products_details[$av['product_id']]['actions'] = $av;
	            $actions[$av['product_id']][] = $av;
	        }
	        
	        foreach($specific_products as  $pr_id => $pr_data){

	            // insert  
	            $prodinsert = new InternalInvoicesActionProducts();
	            $prodinsert->client = $client;
	            $prodinsert->list = $new_list;
	            $prodinsert->usergroup = $pr_data['usergroup'];
	            $prodinsert->contactform_type = $pr_data['contactform_type'];
	            $prodinsert->range_start = $pr_data['range_start'];
	            $prodinsert->range_end = $pr_data['range_end'];
	            $prodinsert->km_range_start = $pr_data['km_range_start'];
	            $prodinsert->km_range_end = $pr_data['km_range_end'];
	            $prodinsert->range_type = $pr_data['range_type'];
	            $prodinsert->time_start = $pr_data['time_start'];
	            $prodinsert->time_end = $pr_data['time_end'];
	            $prodinsert->calculation_trigger = $pr_data['calculation_trigger'];
	            $prodinsert->holiday = $pr_data['holiday'];
	            $prodinsert->save();
	            
	            $prod_id = $prodinsert->id;

	            if($prod_id){
	             foreach($actions[$pr_data['id']] as $k=>$ad){
	                 
	                 // insert
	                 $prodinserta = new InternalInvoicesAction2Products();
	                 $prodinserta->client = $client;
	                 $prodinserta->list = $new_list;
	                 $prodinserta->product_id = $prod_id;
	                 $prodinserta->action_id = $ad['action_id'];
	                 $prodinserta->save();
	               }   
	            }
	        }
	        
	        echo "sssssssss";
	    }
	    
	    
	    public function createdosageintervalsAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	        
	        // get all saved dosage intervals - assignd them to "actual" and copy them to isivmed
// 	        exit;
	        
	        
// 	        PatientDrugPlanDosageIntervals

	        $sp = Doctrine_Query::create()
	        ->select("*")
	        ->from('PatientDrugPlanDosageIntervals')
	        ->orderBy('id ASC');
	        $specific_products = $sp->fetchArray();
	         
	        
	        foreach($specific_products as $k=>$data){
	            
	            $prodinserta = new PatientDrugPlanDosageIntervals();
	            $prodinserta->ipid = $data['ipid'];
	            $prodinserta->medication_type = "isivmed";
	            $prodinserta->time_interval = $data['time_interval'];
	            $prodinserta->isdelete = $data['isdelete'];
	            
	            $prodinserta->create_date = $data['create_date'];
	            $prodinserta->change_date = $data['change_date'];
	            $prodinserta->create_user = $data['create_user'];
	            $prodinserta->change_user = $data['change_user'];
	            $prodinserta->save();
	             
	        }
	        
	        
	    }
	    
	    
	    public function cleansharcourseAction(){
	        set_time_limit(0);
	        
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	        
	        $ipid = "880da22266f1e6b4886f6b4273569e65deaff671";
	        
	        $patient_slq = Doctrine_Query::create()
	        ->select("*")
	        ->from('PatientsShareLog')
	        ->where(' ipid = "'.$ipid.'" ')
	        ->andwhere('source_ipid = "" ');
	        $sh_log_patients = $patient_slq->fetchArray();
	        
	        $empty_log_id = array(); 
	        foreach($sh_log_patients as $k=>$log){
	            $empty_log_id[] = $log['course_id'];
	        }

// 	        print_R($empty_log_id); exit;
	        
	        
	        
	        
	        $courser = Doctrine_Query::create()
	        ->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
	        					->from('PatientCourse')
	        					->where('ipid ="' . $ipid . '"')
	        					->andWhere('source_ipid =""')
	                             ->andWhereIn('id',$empty_log_id);
	        $courser_arr = $courser->fetchArray();
	         
	        
	        
	        print_R(count($courser_arr));
	        
	        
	        if(!empty($empty_log_id))
	        {
    	        $q = Doctrine_Query::create()
                    ->delete('PatientCourse')
                    ->where('ipid ="' . $ipid . '"')
	        		->andWhere('source_ipid =""')
                    ->andWhereIn('id',$empty_log_id);
    	        $q->execute();
    	        
    	        echo "<br/>";
    	        echo count($empty_log_id);
    	        echo "<br/>";
    	        echo "done";
    	        exit;
	        }
	        
	        
	        
	        
	    }
	    

	    public function  updatedgpkernAction(){
	         
	        exit;
	        set_time_limit(0);
	    
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	        
	        $kern = Doctrine_Query::create()
	        ->select("count(*) as nr_per_ipid,ipid,create_date,change_date")
			->from('DgpKern')
			->groupBy('ipid');
	        $kern_arr = $kern->fetchArray();
	        
	        foreach($kern_arr as $k=>$ko){
	            if($ko['nr_per_ipid'] == "1"){
	               $adm_kerns_ipids[] = $ko['ipid'];    
	            } 
	            else
	            {
	               $kern_all[]  = $ko;
	            }
	        }
	        
	        
	        if(!empty($adm_kerns_ipids)){
	            
	            $adm_all_q = Doctrine_Query::create()
	            ->update('DgpKern')
	            ->set('form_type', '"adm"')
	            ->whereIn('ipid',$adm_kerns_ipids);
	            $adm_all_q->execute();
	        }
	        
	        
	        foreach($kern_all as $key=>$kp){

	            $kern_p= Doctrine_Query::create()
	            ->select("*")
	            ->from('DgpKern')
	            ->where('ipid = "'.$kp['ipid'].'" ')
	            ->orderBy('id,create_date asc');
	            $kern_p_arr = $kern_p->fetchArray();
	            
	            $kern_ids[$kp['ipid']]['first']  = $kern_p_arr[0]['id'];
	            $last_ipid_kenr = array();
	            $last_ipid_kenr = end($kern_p_arr);
	            $kern_ids[$kp['ipid']]['last']  = $last_ipid_kenr['id']; 
	            
	            if(!empty($kern_ids[$kp['ipid']]['first']))
	            {
	                $adm_q = Doctrine_Query::create()
	                ->update('DgpKern')
	                ->set('form_type', '"adm"')
	                ->where("id='" . $kern_ids[$kp['ipid']]['first'] . "'");
	                $adm_q->execute();
	            }
	            
	            if(!empty($kern_ids[$kp['ipid']]['last']) && $kern_ids[$kp['ipid']]['first'] != $kern_ids[$kp['ipid']]['last'])
	            {
	                $dis_q = Doctrine_Query::create()
	                ->update('DgpKern')
	                ->set('form_type', '"dis"')
	                ->where("id='" . $kern_ids[$kp['ipid']]['last'] . "'");
	                $dis_q->execute();
	                
	            }
	        }
	        
	        echo "success"; exit;
	    }

	    
	    
	    public function createdosageintervalsallAction(){
	        $this->_helper->layout->setLayout('layout');
	        $this->_helper->viewRenderer->setNoRender();
	         
	        // get all saved dosage intervals - assignd them to "actual" and copy them to isivmed
	        // 	        exit;
	         
	         
	        // 	        PatientDrugPlanDosageIntervals
	    
	        $sp = Doctrine_Query::create()
	        ->select("*")
	        ->from('PatientDrugPlanDosageIntervals')
	        ->where('medication_type = "actual" ')
	        ->orderBy('id ASC');
	        $specific_products = $sp->fetchArray();
	    
	         
	        // update all 

	        $sph = Doctrine_Query::create()
	        ->update('PatientDrugPlanDosageIntervals')
	        ->set('isdelete', '1')
	        ->where("medication_type='all' ");
	        $sph->execute();
	         
	        
	        foreach($specific_products as $k=>$data){
	             
	            $prodinserta = new PatientDrugPlanDosageIntervals();
	            $prodinserta->ipid = $data['ipid'];
	            $prodinserta->medication_type = "all";
	            $prodinserta->time_interval = $data['time_interval'];
	            $prodinserta->isdelete = $data['isdelete'];
	             
	            $prodinserta->create_date = $data['create_date'];
	            $prodinserta->change_date = $data['change_date'];
	            $prodinserta->create_user = $data['create_user'];
	            $prodinserta->change_user = $data['change_user'];
	            $prodinserta->save();
	    
	        }
	         
	         
	    }
	    
	    
	public function paidinvoicesAction(){
		
		 
		$invoice_table = "RpInvoices";
		$period['start'] = "2010-01-01";
		$period['end'] = "2016-12-31 23:59:59";
		$clientid ="115";
		$status = "unpaid";
		
		
		$storno_inv = Doctrine_Query::create()
		->select('record_id')
		->from($invoice_table)
		->where('client =?',$client)
		->andWhere('isdelete =?',0)
		->andWhere('storno =?',1);
		$storno_inv_array = $storno_inv->fetchArray();

		$storno_ids_str = '"XXXXXX",';
		if(!empty($storno_inv_array)){
			foreach($storno_inv_array as $ksi=>$si){
				$stornoed_invoices[] = $si['record_id'];
				$storno_ids_str .= '"' . $si['record_id'] . '",';
			}
		}
		
		
		$storno_ids_str = substr($storno_ids_str, 0, -1);
		
// 		print_r($storno_ids_str); exit;
// 		print_r($status); exit;

		
		switch($status)
		{
		
			case 'draft':
				$filters = ' AND status ="1" AND isdelete=0';
				break;
		
			case 'unpaid':
				$filters = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN (' . $storno_ids_str . ') AND isdelete = 0 ';
		
				break;
		
			case 'paid':
				$filters = ' AND status="3"  AND storno = 0 AND id NOT IN (' . $storno_ids_str . ')  AND isdelete=0';
				break;
		
			case 'deleted':
				$filters = ' AND (status="4" OR isdelete="1") ';
				break;
		
			case 'overdue':
				$filters = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN (' . $storno_ids_str . ') AND DATE(NOW()) > DATE(DATE_ADD(`completed_date`,INTERVAL 2 WEEK)) AND isdelete=0';
				break;
		
			case 'all':
				$filters = ' ';
				break;
		
			default: // unpaid- open
				$filters = ' AND (status = "2" OR status = "5")   AND storno = 0 AND id NOT IN (' . $storno_ids_str . ') AND isdelete = 0 ';
				break;
		}
		
		
		if(!empty($period['end'] ) && $period['end']  != '99999999')
		{
			$filters .= ' AND DATE(invoice_end) <= "2016-12-31" ';
		}
 
		
		
		$inv = Doctrine_Query::create()
		->select('*')
		->from($invoice_table)
		->where('isdelete =?',0)
		->andWhere("client='" . $clientid . "'" . $filters);
		
		$inv_array = $inv->fetchArray();
		
		foreach($inv_array as $row=>$invoice_data){
			
			// update invoice set as paid and paid date
			$inv_update = Doctrine::getTable($invoice_table)->find($invoice_data['id']);
			$inv_update->paid_date = date('Y-m-d H:i:s');
			$inv_update->status = 3;
			$inv_update->change_user = "634"; // Verena
			$inv_update->change_date = date('Y-m-d H:i:s');
			$inv_update->save();
			
			// insert in payment
// 			RpInvoicePayments
// 			$payment = Doctrine::getTable($invoice_table)->find($invoice_data['id']);
			$payment =  new RpInvoicePayments();
			$payment->invoice = $invoice_data['id'];
			$payment->amount = $invoice_data['invoice_total'];
			$payment->comment= "requested by Smart-q";
			$payment->paid_date= date('Y-m-d H:i:s');
			$payment->create_user = "634"; // Verena
			$payment->create_date= date('Y-m-d H:i:s');
			$payment->save();
			$payms[] = $payment->id;
			
		}
		
// 		print_R($inv_array); exit;
// 		print_R(count($inv_array)); exit;
		print_R(count($payms)); 
		
		echo "success";
		exit;
		
	}
	

		public function standbypatientsAction(){
			
			
			if(isset($_REQUEST['test']) && $_REQUEST['test'] == 1)
			{
				error_reporting(E_ALL);
			}
			else
			{
				error_reporting(0);
			}
			
			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();
			
			$actpatient = Doctrine_Query::create();
			$actpatient->select("p.ipid,p.isstandby,p.isstandbydelete,p.admission_date,create_date,create_user");
			
			$actpatient->from('PatientMaster p');
			$actpatient->Where('p.isdelete =?',0);
			$actpatient->andWhere('p.isarchived =?',0);
			$actpatient->andWhere('p.isdischarged =?',0);
			$actpatient->andWhere('p.isstandby =?',1);
			$actpatient->OrWhere('p.isstandbydelete =?',1);
			$actipidarray = $actpatient->fetchArray();
			
			$standby = array();
			$stand_by_actpatientq = Doctrine_Query::create();
			$stand_by_actpatientq->select("*");
			$stand_by_actpatientq->from('PatientStandby');
			$stand_by_actpatient = $stand_by_actpatientq->fetchArray();
			
			foreach($stand_by_actpatient as $k=>$pat){
				$standby[] = $pat['ipid'];
			}
 
			$standby_det = array();
			$stand_by_det_actpatientq = Doctrine_Query::create();
			$stand_by_det_actpatientq->select("*");
			$stand_by_det_actpatientq->from('PatientStandbyDetails');
			$stand_by_det_actpatient = $stand_by_det_actpatientq->fetchArray();
			
			foreach($stand_by_det_actpatient as $k=>$pat){
				$standby_det[] = $pat['ipid'];
			}
			
			
			foreach($actipidarray as $k=>$sp){
				
// 				if($sp['isstandby'] == "1")
// 				{
					if(!in_array($sp['ipid'],$standby) && !in_array($sp['ipid'],$standby_det) ){
						
						$standby =  new PatientStandby();
						$standby->ipid = $sp['ipid'];
						$standby->start = date("Y-m-d",strtotime($sp['admission_date']));
						$standby->save();
						
						$standby_details =  new PatientStandbyDetails();
						$standby_details->ipid = $sp['ipid'];
						$standby_details->date = $sp['admission_date'];
						$standby_details->date_type = "1";
						$standby_details->comment = "db update";
						$standby_details->create_date= $sp['create_date'];
						$standby_details->create_user= $sp['create_user'];
						$standby_details->save();
					}
					
				/* } else {
					
					if(!in_array($sp['ipid'],$standbydelete) && !in_array($sp['ipid'],$standbydelete_det) ){
						
						$standby =  new PatientStandbyDelete();
						$standby->ipid = $sp['ipid'];
						$standby->start = date("Y-m-d",strtotime($sp['admission_date']));
						$standby->save();
						
						
						$standby_details =  new PatientStandbyDeleteDetails();
						$standby_details->ipid = $sp['ipid'];
						$standby_details->date = $sp['admission_date'];
						$standby_details->date_type = "1";
						$standby_details->comment = "db ubdate";
						$standby_details->create_date= $sp['create_date'];
						$standby_details->create_user= $sp['create_user'];
						$standby_details->save();
					}
				} */
			}
		}
	
	
	
	private function fixbtmconnectionspatient2patient()
	{
		echo "<hr> START fixbtmconnectionspatient2patient <hr>";
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($logininfo->usertype != 'SA')
			{
				die(" normal Ben ? ");
			}
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
		
			
echo "<pre>";

// 			 $ipids = array_column($r_connected, 'ipid');

			$r_ipids = Doctrine_Query::create()
			->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
			->from("MedicationPatientHistory INDEXBY id")
			// 		->WhereIn('ipid', $ipids )
// 			->andWhere('isdelete = 0')
			->andWhere('source = "u"')
			->andWhere('self_id = 0')
			->andWhere('methodid IN (8) ')
// 			->limit(200)
			->fetchArray();
			
			
			
			foreach( $r_ipids  as $row )
			{
				// 				$r_new_connected['clientid']['methodid']['medicationid']['amount']['date'] = $row ;
				$r_new_connected[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [] = $row ;
			}
			
			
			
			$r_ipids_v2 = Doctrine_Query::create()
			->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
			->from("MedicationPatientHistory INDEXBY id")
			// 		->WhereIn('ipid', $ipids )
// 			->andWhere('isdelete = 0')
			->andWhere('source = ""')
			->andWhere('userid = 0')
			->andWhere('to_userid = 0')
			->andWhere('self_id = 0')
			->andWhere('methodid =0 ')
			->andWhere('done_date = \'0000-00-00 00:00:00\' ')
			// 			->limit(200)
			->fetchArray();
			
			$r_new_connected_v2 = array();
			foreach( $r_ipids_v2  as $row )
			{
				// 				$r_new_connected['clientid']['methodid']['medicationid']['amount']['date'] = $row ;
				$r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [] = $row ;
			}
			
			
			
// 			print_r($r_ipids);
// 			print_r($r_new_connected_v2);
// 			die();
			$update_rows = array();
			foreach ($r_new_connected as $clientid) {
				foreach ($clientid as $ipid) {
					foreach ($ipid as $medicationid) {
						foreach ($medicationid as $amount) {
							foreach ($amount as $done_create_date) {
								if (count($done_create_date) == 2) {
									
// 									die("ssssss");
									
									$ipid = $done_create_date[0]['ipid'];
									$id = $done_create_date[0]['amount'] > $done_create_date[1]['amount'] ? $done_create_date[0]['id'] : $done_create_date[1]['id'];
									//validate if we have the 
									
									$r2 = Doctrine_Query::create()
									->select('*')
									->from("MedicationClientHistory")
									->Where('ipid = ?', $ipid)
									->andWhere('patient_stock_id = ? ' , $id)
// 									->andWhere('isdelete = 0')
									->limit(1)
									->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
									
// 									print_r($r2);
									if (!empty($r2)) {
										
										
										$done_create_date[0]['self_id'] = $done_create_date[1]['id'];
										$done_create_date[1]['self_id'] = $done_create_date[0]['id'];
										
										$update_rows[] = $done_create_date[0];
										$update_rows[] = $done_create_date[1];
										
									} else {
										
										$unlinkable[] = $done_create_date;
// 										die (print_r($done_create_date));
									}
// 									print_r($update_rows) ;
// 									die("dddddd");
									
// 									die (print_r($done_create_date));
									
								} elseif (count($done_create_date) == 1) {
										
									$row = $done_create_date[0];
									
// 									print_r($row);
									
// 									print_r($r_new_connected_v2[ $row['clientid'] ][  $row['ipid'] ] [ $row['medicationid'] ][ abs($row['amount']) ] ); 
// 									print_r($r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] );
// 									print_r($r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] );
									
// 									die("ssssssss");
									
									if (isset($r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [0] ) 
											&& $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [0]['amount'] == (-1)*$row['amount']
											&& $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [0]['create_user'] == $row['create_user']

									) {
										
										
										$row['self_id'] = $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [0] ['id'];
										$r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [0] ['self_id'] = $row['id'];
										
										$update_rows[] = $row;
										$update_rows[] = $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [0];
										
										//print_R($update_rows);
										//die("b");
									} 
									elseif (isset($r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] [0] ) 
											&& $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] [0]['amount'] == (-1)*$row['amount']
											&& $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] [0]['create_user'] == $row['create_user']
												
											) 
									{
										
										
										$row['self_id'] = $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] [0] ['id'];
										$r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] [0] ['self_id'] = $row['id'];
										
										$update_rows[] = $row;
										$update_rows[] = $r_new_connected_v2[ $row['clientid'] ] [  $row['ipid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['create_date'] ] [0];
											
										//print_R($update_rows);	
										//die("b");
										
									}
									
										
									
								}
							}
						}
					}
				}
			}
			
			
// 			print_r($update_rows);
// 			die();
			
			
			foreach ($update_rows as $row) {
				$q = Doctrine_Query::create()
				->update("MedicationPatientHistory")
				->set('self_id', '?', $row['self_id'] )
				->Where('id = ?', $row['id'])
				->execute();
			}
			
			echo (count($update_rows) . " : linked together from : ". count($r_ipids) ." <hr>");
			
			
			

				
			
			
// 			print_r($update_rows);
			
			echo( "<hr>" . count($r_ipids) . " ");
			echo( "<hr>UNLINKABLE <hr> ");
			
		
			die (print_r($unlinkable));
			
			
			
			
			
	}	
	
	public function fixbtmconnectionsAction()
	{
		set_time_limit(0);
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		//link user to user
		echo "<pre>";
		$r_connected = Doctrine_Query::create()
		->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
		->from("MedicationClientHistory")
		->WhereIn('methodid', array(1,4) )
		->andWhere('stid = 0')
		->andWhere('self_id = 0')
		->andWhere('patient_stock_id = 0')
		->andWhere('ipid = "0"')
// 		->andWhere('isdelete = 0')
		->fetchArray();
			
			
		$r_new_connected = array();
		$update_rows = array();
			
		foreach( $r_connected  as $row )
		{
			// 				$r_new_connected['clientid']['methodid']['medicationid']['amount']['date'] = $row ;
			$r_new_connected[ $row['clientid'] ] [  $row['methodid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [$row['id']] = $row ;
		}
		
		foreach( $r_new_connected  as $clientid ){
			foreach( $clientid  as $methodid ){
				foreach( $methodid  as $medicationid ){
					foreach( $medicationid  as $amount ){
						foreach( $amount  as $done_create_date ){
								
							$this_two_rows = array_values($done_create_date);
		
							if (sizeof($done_create_date) <> 2  || $this_two_rows[0]['amount'] != (-1)*$this_two_rows[1]['amount'] ){
									
								echo  PHP_EOL ."HOW do we link this?  manualy change the done_create_date ?". PHP_EOL;
								//print_r($clientid);
								print_r($done_create_date);
									
								//die("WHY?");
							} else {
									
								$this_two_rows = array_values($done_create_date);
								$this_two_rows[0]['self_id'] = $this_two_rows[1]['id'];
								$this_two_rows[1]['self_id'] = $this_two_rows[0]['id'];
									
								//print_r ($this_two_rows);
									
								$update_rows[] = $this_two_rows[0];
								$update_rows[] = $this_two_rows[1];
									
							}
								
						}
					}
				}
			}
		}
		
		
		// 			print_r($update_rows);
		/* foreach ($update_rows as $row) {
			$q = Doctrine_Query::create()
			->update("MedicationClientHistory")
			->set('self_id', '?', $row['self_id'] )
			->Where('id = ?', $row['id'])
			->execute();
		} */
		echo "<hr> Connecting user-2-user total :" . (sizeof($update_rows)) . "<hr>".PHP_EOL;
			
// 		die();
		
		
		
		
		
		
		
		
		
		
		//link user to patient
		echo "<pre>";
		$r_connected = Doctrine_Query::create()
		->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
		->from("MedicationClientHistory INDEXBY id")
		->Where('ipid != "0"')
		->andWhere('stid = 0')
		->andWhere('self_id = 0')
		->andWhere('patient_stock_id = 0')
		->limit(500)
// 		->andWhere('isdelete = 0')
		->fetchArray();
		
// 		die(print_r($r_connected) . "<hr>");
// 		die(sizeof($r_connected) . "<hr>");
		
		$ipids = array_column($r_connected, 'ipid');

		$r_ipids = Doctrine_Query::create()
		->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
		->from("MedicationPatientHistory INDEXBY id")
		->WhereIn('ipid', $ipids )
// 		->andWhere('isdelete = 0')
		->fetchArray();
		
		
		$ipids2 = array_column($r_ipids, 'ipid');
		
		$ipids = array_unique( array_merge($ipids, $ipids2)) ;
		
		$r_new_connected = array();
		$update_rows = array();
			
		
		
		$arr_btm_ct = array( Pms_CommonData::aesEncrypt("btm_master") , Pms_CommonData::aesEncrypt("btm_patient_icon"));
		
		$r_ipids_course = Doctrine_Query::create()
		->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
// 		->select('count(*)')
		->from("PatientCourse")
		->WhereIn('ipid', $ipids )
// 		->andWhere('isdelete = 0')
		->andWhere('isserialized = 1')
		->andWhereIn('tabname' , $arr_btm_ct )
// 		->limit(10)
		->fetchArray();
		
//recorddata
// 		print_r($r_ipids_course); exit;
		

		$course_array = array();
		foreach($r_ipids_course as $row_course) {
			
			$recorddata = unserialize($row_course['recorddata']);
			
			$recorddata['patient_stock_id'];
			$recorddata['client_history_id'];
			
			if (	! isset($recorddata['patient_stock_id']) 
					|| ! isset($recorddata['client_history_id'])
					|| ! isset( $r_connected [$recorddata['client_history_id']])
					//|| ! isset( $r_ipids [$recorddata['patient_stock_id']])
					
			) {
// 				echo "<hr>is this deleted? or why?";
// 				(print_r($recorddata));
// 				echo PHP_EOL;
			}
			else {
				$course_array[$recorddata['client_history_id']] = $recorddata;
				$course_array_2[$recorddata['client_history_id']] = $row_course;
			}
		}
		
// 		print_R($r_connected);
		
		foreach( $r_connected  as $row_user )
		{
			
// 			if ($row_user['ipid'] != '10c2cb5f3cbfcacdf97dc22ea097addf91abeca1' ) {
// 				continue;
// 			}
			
			
			
			
			$this_two_rows = array();
			foreach($r_ipids as $row_ipid) {

// 				if ($row_ipid['ipid'] != '10c2cb5f3cbfcacdf97dc22ea097addf91abeca1' ) {
// 					continue;
// 				}
				
// 				print_R($row_user);
// 				print_R($row_ipid);
// 				die("ssssssssss");
				
// 				die(print_r($row_user));
				
				
				if ( $row_user['ipid'] == $row_ipid['ipid']
						&& $row_user['clientid'] == $row_ipid['clientid']
						
						&& ($row_user['userid'] == $row_ipid['userid']
									||
								
								($row_user['methodid'] == 12 && $row_user['userid'] == $row_ipid['to_userid'])
						)
						
						
						&& $row_user['medicationid'] == $row_ipid['medicationid']
						&& $row_user['methodid'] == $row_ipid['methodid']
						&& $row_user['amount'] == (-1)*($row_ipid['amount'])
// 						&& $row_user['done_create_date'] == $row_ipid['done_create_date']
						&& $row_user['create_user'] == $row_ipid['create_user']
 						&& $course_array[ $row_user['id'] ]['patient_stock_id'] == $row_ipid['id']
				){
					//this 2 are connected?		
					$this_two_rows['user'] = $row_user;
					$this_two_rows[] = $row_ipid;
					
				} else if(
						$row_user['ipid'] == $row_ipid['ipid']
						&& $row_user['clientid'] == $row_ipid['clientid']
						&& ( $row_user['userid'] == $row_ipid['userid']
							 || ($row_user['methodid'] == 12 && $row_user['userid'] == $row_ipid['to_userid'])
						)
						
						&& $row_user['medicationid'] == $row_ipid['medicationid']
						&& $row_user['methodid'] == $row_ipid['methodid']
						&& ($row_user['amount']) == (-1)*($row_ipid['amount'])
						&& ( $row_user['done_create_date'] == $row_ipid['done_create_date']
								|| (abs( strtotime($row_user['done_create_date']) - strtotime($row_ipid['done_create_date']) ) < 2)
						)
						
						&& $row_user['create_user'] == $row_ipid['create_user']
//  						&& $course_array[ $row_user['id'] ]['patient_stock_id'] == $row_ipid['id'])
				)
				{

					//this 2 are connected?
					$this_two_rows['user'] = $row_user;
					$this_two_rows[] = $row_ipid;
				}
				else if(
						$row_user['ipid'] == $row_ipid['ipid']
						&& $row_user['clientid'] == $row_ipid['clientid']
// 						&& $row_user['userid'] == $row_ipid['userid']
						&& $row_ipid['userid'] == 0
						&& $row_ipid['to_userid'] == 0
						&& $row_ipid['methodid'] == 0
						&& $row_ipid['amount'] > 0
						&& $row_ipid['source'] == ""
						
						&& $row_user['medicationid'] == $row_ipid['medicationid']
// 						&& $row_user['methodid'] == $row_ipid['methodid']
						&& ($row_user['amount']) == (-1)*($row_ipid['amount'])
						
						
						
						&&  (	abs( strtotime($row_user['create_date']) - strtotime($row_ipid['create_date']) ) < 5
								||
								abs( strtotime($row_user['done_create_date']) - strtotime($row_ipid['done_create_date'])) < 5
								)
						
						&& $row_user['create_user'] == $row_ipid['create_user']
//  						&& $course_array[ $row_user['id'] ]['patient_stock_id'] == $row_ipid['id'])
				)
				{

					//die(strtotime($row_user['done_create_date']) ." ");
					//this 2 are connected?
					$this_two_rows['user'] = $row_user;
					$this_two_rows[] = $row_ipid;
// 					print_r($this_two_rows);
					
				}
				
				else if ( $row_user['ipid'] == $row_ipid['ipid'] ){
					//die("sssssssssss");
					
				}
				
				
				
			}
			
			if (  !empty($this_two_rows)  && sizeof($this_two_rows) != 2) {
//  				print_r($this_two_rows); die("???");
			} else if(empty($this_two_rows)) {


				echo "<hr>user NOT RELATE TO ANYTHING?";
				
				echo $row_user['id'] ." " .$row_user['done_create_date'] ."<br>";
				
 				print_r($row_user);
 				print_r($course_array[ $row_user['id'] ]);
//  				print_r($course_array_2[ $row_user['id'] ]);
 				
 				
 				
 				$cnt++;
 				echo "<hr>";
			} elseif( sizeof($this_two_rows) == 2){
// 				print_r($this_two_rows);
// 				$row_user ['patient_stock_id'] = $course_array[ $row_user['id'] ] ['patient_stock_id'];
				$row_user ['patient_stock_id'] = $this_two_rows[ 0 ] ['id'];
				$update_rows[] = $row_user;
				
			}
			
			// 				$r_new_connected['clientid']['methodid']['medicationid']['amount']['date'] = $row ;
// 			$r_new_connected[ $row['clientid'] ] [  $row['methodid'] ] [ $row['medicationid'] ] [ abs($row['amount']) ] [ $row['done_create_date'] ] [$row['id']] = $row ;
		}
		echo "<hr> user NOT RELATE TO ANYTHING total: ".$cnt;
		
		
		echo "<hr> Connecting user-2-patient total :" . (sizeof($update_rows)) ." from" .(sizeof($r_connected)) . "<hr>".PHP_EOL;
		
// print_r($update_rows);
		foreach ($update_rows as $row) {
			
// 			(print_r($row));
			$q = Doctrine_Query::create()
			->update("MedicationClientHistory")
			->set('patient_stock_id', '?', $row['patient_stock_id'] )
			->Where('id = ?', $row['id'])
			->execute();
		}
// 		die();
// print_r($update_rows);
		
		
		$not_interconnected_methods = array(2, 3, 6, 10, 11, 13);
		
		//link other
		echo "<pre>";
		$r_connected = Doctrine_Query::create()
		->select('* , IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as done_create_date')
		->from("MedicationClientHistory INDEXBY id")
		->Where('stid = 0')
		->andWhere('self_id = 0')
		->andWhere('patient_stock_id = 0')
		->andWhereIn('methodid', $not_interconnected_methods,  true);
// 		->andWhere('isdelete = 0');
		
// 					Pms_DoctrineUtil::get_raw_sql($r_connected);

		
		$r_connected = $r_connected->fetchArray();
		echo "<hr> NOT CONNECTED total :" .(sizeof($r_connected)) . "<hr>".PHP_EOL;
		
		
// 		print_r($r_connected);
		
		$ipids = array_column($r_connected, 'ipid');
		

		$r_new_connected = array();
		$update_rows = array();
		
		
		self::fixbtmconnectionspatient2patient();
		
		die("FINISH");
	}
	    
	//should be run only once
	public function btmgrouppermisionsupdateAction()
	{
		exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		echo "<pre>";
		$r_connected = Doctrine_Query::create()
		->select('*')
		->from("BtmGroupPermissions INDEXBY id")
		->fetchArray();
		
		foreach( $r_connected  as $row )
		{
			if ( $row['value'] == 1) {
				$r_new_connected[ $row['clientid'] ] [  $row['groupid'] ] [] =  $row['name']  ;
			}
		}
		$update = array();
// 		print_r($r_new_connected);
		foreach( $r_new_connected  as $k_clientid => $clientid ){
			foreach( $clientid  as $k_groupid => $groupid ){
				if ( in_array("use", $groupid)) {
					if ( ! in_array("method_sonstiges", $groupid)) {
						$update[] = array(
								"clientid" => $k_clientid,
								"groupid" => $k_groupid,
								"name" => "method_sonstiges",	
								"value" => "1"	
						);
					}
					if ( ! in_array("method_ubergabe_abgabe", $groupid)) {
						$update[] = array(
								"clientid" => $k_clientid,
								"groupid" => $k_groupid,
								"name" => "method_ubergabe_abgabe",	
								"value" => "1"		
						);
					}
					if ( ! in_array("method_verbrauch", $groupid)) {
						$update[] = array(
								"clientid" => $k_clientid,
								"groupid" => $k_groupid,
								"name" => "method_verbrauch",	
								"value" => "1"		
						);
					}
					if ( ! in_array("method_rucknahme_ruckgabe", $groupid)) {
						$update[] = array(
								"clientid" => $k_clientid,
								"groupid" => $k_groupid,
								"name" => "method_rucknahme_ruckgabe",	
								"value" => "1"		
						);
					}
				}
			}
		}
		
		
		if( !empty ($update) )
		{
			
			$collection = new Doctrine_Collection('BtmGroupPermissions');
			$collection->fromArray($update);
			$collection->save();
		}
		
		echo "<hr>inserted rows: " .sizeof($update) ."<hr>";
		
		
	}


	public function updatemembersreferaltabAction ()
	{
		exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		echo "<pre>";
		
		$membership_query_r = Doctrine_Query::create()
		->select('*')
		->from('Member2Memberships')
// 		->limit("1000")
		->fetchArray();
		
// 		print_r($medics);
		$memberships = array();
		
		foreach($membership_query_r as $row) {
			
// 			$memberships[ $cleintid ]['member']['first_membership '] = isdelete
			
// 			if (! isdelete) {
// 				$memberships[ $cleintid ]['member']['last_membership '] = date
// 			}
			
			if ( ! isset($memberships[ $row['clientid'] ] [ $row['member'] ] ['first'])){
				$memberships[ $row['clientid'] ] [ $row['member'] ] ['first'] = $row['start_date'];
				
			} elseif( strtotime($row['start_date']) < strtotime($memberships[ $row['clientid'] ] [ $row['member'] ] ['first'] )) {
				$memberships[ $row['clientid'] ] [ $row['member'] ] ['first'] = $row['start_date'];
			}
			
			if ( ! isset($memberships[ $row['clientid'] ] [ $row['member'] ] ['last'])) {
				
				$memberships[ $row['clientid'] ] [ $row['member'] ] ['last'] = $row['start_date'];
				
			} elseif (strtotime($row['start_date']) > strtotime($memberships[ $row['clientid'] ] [ $row['member'] ] ['last'] )) {
				
				$memberships[ $row['clientid'] ] [ $row['member'] ] ['last'] = $row['start_date'];
				
			}
			

		}
		
		foreach($memberships as $clientid) {
			foreach($clientid as $member) {
				if ($member['first'] != $member['last']){
// 					print_R($member); 
				}
			
			
			}
		}
// 		print_r($memberships);
		
		
		$donation_query_r = Doctrine_Query::create()
		->select("*")
		->from('MemberDonations')
// 		->limit("1000")
		->fetchArray();
// 						print_r($donation_query_r);
		
		$donations = array();
		foreach($donation_query_r as $row) {

			
			if ( ! isset($donations[ $row['clientid'] ] [ $row['member'] ] ['first'])){
				$donations[ $row['clientid'] ] [ $row['member'] ] ['first'] = $row['donation_date'];
		
			} elseif( strtotime($row['donation_date']) < strtotime($donations[ $row['clientid'] ] [ $row['member'] ] ['first'] )) {
				$donations[ $row['clientid'] ] [ $row['member'] ] ['first'] = $row['donation_date'];
			}
				
			if ( ! isset($donations[ $row['clientid'] ] [ $row['member'] ] ['last'])) {
		
				$donations[ $row['clientid'] ] [ $row['member'] ] ['last'] = $row['donation_date'];
		
			} elseif (strtotime($row['donation_date']) > strtotime($donations[ $row['clientid'] ] [ $row['member'] ] ['last'] )) {
		
				$donations[ $row['clientid'] ] [ $row['member'] ] ['last'] = $row['donation_date'];
		
			}
				
		
		}

// 		print_r($donations);
		
		foreach($donations as $clientid) {
			foreach($clientid as $member) {
				if ($member['first'] != $member['last']){
// 										print_R($member);
				}
					
					
			}
		}
		
		
		$final_array = array();
		
		$processed_ids = array();
		foreach($memberships as $c_key => $clientid) {
			foreach($clientid as $m_key => $member) {
				
				if (isset($donations[$c_key][$m_key]['first']) 
						&& strtotime($donations[$c_key][$m_key]['first']) < strtotime($member['first'])
				) {

					$final_array[] = array(
							'clientid' => $c_key,
							'memberid' => $m_key,
							'referal_tab' => 'donors',
					);
				
					
				} else {
					$final_array[] = array(
							'clientid' => $c_key,
							'memberid' => $m_key,
							'referal_tab' => 'members',
					);
				}
				
				
				
				$processed_ids[] = $m_key;
					
			}
		}
		
		
		foreach($donations as $c_key => $clientid) {
			foreach($clientid as $m_key => $member) {
				
				if ( ! in_array($m_key, $processed_ids)) {	
// 					print_R($member);echo (" not membership<hr>");
					$only_donations[] = $m_key;
					
					
					$final_array[] = array(
							'clientid' => $c_key,
							'memberid' => $m_key,
							'referal_tab' => 'donors',
					);
					
					$processed_ids[] = $m_key;
				}
			}		
		}
		
// 		print_R($only_donations);
		
		
		$member_query_r = Doctrine_Query::create()
		->select("*")
		->from('Member')
// 				->limit("10")
		->fetchArray();
		
// 		print_r($member_query_r);
		
		foreach($member_query_r as $row) {
			if ( ! in_array($row['id'], $processed_ids)) {
				$without_donation_or_membership[] = $row;
				
				$final_array[] = array(
						'clientid' => $row['clientid'],
						'memberid' => $row['id'],
						'referal_tab' => 'members',
						'undecided' => 'undecided',
				);
			}
		}
		
		
		echo "<hr> total only_donations: ";
		print_r(count($only_donations));
		
		
		echo "<hr> total without_donation_or_membership: ";
		print_r(count($without_donation_or_membership));
		
		echo "<hr> total MemberReferalTab: ";
		print_r(count($final_array));
		
		if( !empty ($final_array) )
		{
				
			$collection = new Doctrine_Collection('MemberReferalTab');
			$collection->fromArray($final_array);
			$collection->save();
		}
		
		
	}
	
	
	public function encryptmembersAction ()
	{
		
	}
	
	public function updatecoursedateAction ()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$inv_doc = Doctrine_Query::create()
		->select("*")
		->from('PatientCourse')
		->where('create_user = 3031 ')
		->andWhere('date(create_date) = "2017-05-12" ')
		->andWhere('course_date = "0000-00-00 00:00:00" ');
		$doc_involved_patients = $inv_doc->fetchArray();
		
		print_r($doc_involved_patients); exit;
		
	}
	
	
	
	public function patientsfixAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		
		$sql_client = 'e.clientid = "' . $clientid . '"';
		$q = Doctrine_Query::create()
		->select("e.ipid,p.admission_date,p.ipid,e.epid,e.epid_num,AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
			AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name, a.*,r.*")
		->from('EpidIpidMapping e')
		->leftJoin('e.PatientMaster p')
		->leftJoin('e.PatientActive a ')
		->where($sql_client)
		->andWhere('p.isdelete = 0')
		->andWhere('p.isstandbydelete = 0');
		$actipidarray = $q->fetchArray();
		
		$wrong_falls_ipids[0] = "wrong patients";
		$wrong_falls_ipid2epid[0] = "wrong epid patients";
		foreach($actipidarray as  $k=>$pdata){
			foreach($pdata['PatientActive'] as $pak=>$pav){
				if($pav['start'] == "1970-01-01"){
					$wrong_falls_patients[] = $pdata;
					$wrong_falls_ipids[] = $pav['ipid'];
					$wrong_falls_ipid2epid[$pdata['ipid']] = $pdata['epid'];
					
					$fall_dates[$pdata['ipid']]['admission'] = date('Y-m-d',strtotime($pdata['PatientMaster']['admission_date']));
					$admission_dates[$pdata['ipid']]= $pdata['PatientMaster']['admission_date'];
				}
			}			
		}
		
		$readm_q = Doctrine_Query::create()
		->select('*')
		->from('PatientReadmission')
		->whereIn('ipid', $wrong_falls_ipids);
		$readm_arr = $readm_q->fetchArray();
		
		$dis_q = Doctrine_Query::create()
		->select('*')
		->from('PatientDischarge')
		->whereIn('ipid', $wrong_falls_ipids)
		->andWhere('isdelete = 0 ');
		$disch_arr = $dis_q->fetchArray();
		
		foreach($disch_arr as $pdk=>$pdv){
			$discharge_dates[$pdv['ipid']] = $pdv['discharge_date'];
			$fall_dates[$pdv['ipid']]['discharge'] =  date('Y-m-d',strtotime($pdv['discharge_date']));
		}
 
		foreach($readm_arr as $prk=>$prv){
			$readm_vals[$prv['ipid']][] = $prv; 
		}
		
		$multiple_admission[0] = 'multiple admissions';
		$discharge_changes[0] = 'discharge changes';
		$admission_changes[0] = 'admission changes';
		
		print_r($wrong_falls_ipids); 
		print_r($wrong_falls_ipid2epid); 
		
		foreach($wrong_falls_ipids as $ipid )
		{
			if(count($readm_vals[$ipid]) == "2")
			{
				if($fall_dates[$ipid]['admission'] > $fall_dates[$ipid]['discharge'])
				{
					$new_discharge_date[$ipid] = date('Y-m-d H:i:s',strtotime("+1 minutes",strtotime( $admission_dates[$ipid])));
					
					// update patient discharge with admission date + 1minute
					$qd = Doctrine_Query::create()
					->update('PatientDischarge')
					->set('discharge_date','?', $new_discharge_date[$ipid] )
					->where("ipid = ?", $ipid)
					->andWhere("discharge_date = ?", $discharge_dates[$ipid]);
					$qd->execute();
					
 				   // update readmision = date type = 2 with admission date + 1 minute
					$q = Doctrine_Query::create()
					->update('PatientReadmission')
					->set('date','?', $new_discharge_date[$ipid])
					->where("ipid = ?", $ipid)
					->andWhere("date_type = ?", 2)
					->andWhere("date = ?", $discharge_dates[$ipid]);
					$q->execute();
					
					// REFRESH PATEINT ACTIVE
					//added patient admission/readmission new procedure
					PatientMaster::get_patient_admissions($ipid);
					$discharge_changes[] = $ipid;
					
				} 
				elseif($fall_dates[$ipid]['admission'] < $fall_dates[$ipid]['discharge'] && $fall_dates[$ipid]['admission'] =="1970-01-01") 
				{
					$new_admission_date[$ipid] = date('Y-m-d H:i:s',strtotime("-1 minutes",strtotime( $discharge_dates[$ipid])));
					
					$details_p = Doctrine_Query::create()
					->select('*')
					->from('PatientMaster')
					->where("ipid = ?", $ipid)
					->andWhere("admission_date = ?", $admission_dates[$ipid] );
					$details_pq = $details_p->fetchArray();
						
					if(!empty($details_pq))
					{
						// update patientMaster with discharge date  - 1minute
						$qa = Doctrine_Query::create()
						->update('PatientMaster')
						->set('admission_date','?',$new_admission_date[$ipid])
						->where("ipid = ?", $ipid)
						->andWhere("admission_date = ?", $admission_dates[$ipid]);
						$qa->execute();

						// update readmision = date type = 1 with discharge date  - 1 minute 
						$details_pr = Doctrine_Query::create()
						->select('*')
						->from('PatientReadmission')
						->where("ipid = ?", $ipid)
						->andWhere("date = ?", $admission_dates[$ipid])
						->andWhere("date_type = ?",  1);
						$details_pqr = $details_pr->fetchArray();
					
						if(!empty($details_pqr))
						{
							$q = Doctrine_Query::create()
							->update('PatientReadmission')
							->set('date','?', $new_admission_date[$ipid])
							->where("ipid = ?", $ipid)
							->andWhere("date_type = ?", 1)
							->andWhere("date = ?", $admission_dates[$ipid]);
							$q->execute();
						}
							
						// REFRESH PATEINT ACTIVE
						//added patient admission/readmission new procedure
						PatientMaster::get_patient_admissions($ipid);
						$admission_changes[] = $ipid; 
					}
				}
			} 
			else
			{
				$multiple_admission[] =$ipid; 
			}
		}
		
		print_r($wrong_falls_ipids);
		
		print_r($multiple_admission); 
		print_r($admission_changes); 
		print_r($discharge_changes); 
		print_r("succes "); 
		print_r("\n "); 
		print_r("client: ". $clientid); 
		exit;
		
	}
	
	
	public function testconnectionAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
	
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	
		echo PHP_EOL . "SYSDAT" . PHP_EOL;
		$conn = Doctrine_Manager::getInstance()->getConnection('SYSDAT');
		$r = $conn->execute('SHOW VARIABLES;')->fetchAll(Doctrine_Core::FETCH_ASSOC);
		echo print_r($r) . PHP_EOL;
		$r = $conn->execute('SHOW TRIGGERS;')->fetchAll(Doctrine_Core::FETCH_ASSOC);
		echo print_r($r) . PHP_EOL;
	
	
		echo PHP_EOL . "MDAT" . PHP_EOL;
		$conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
		$r = $conn->execute('SHOW VARIABLES;')->fetchAll(Doctrine_Core::FETCH_ASSOC);
		echo print_r($r) . PHP_EOL;
		$r = $conn->execute('SHOW TRIGGERS;')->fetchAll(Doctrine_Core::FETCH_ASSOC);
		echo print_r($r) . PHP_EOL;
	
		echo PHP_EOL . "IDAT" . PHP_EOL;
		$conn = Doctrine_Manager::getInstance()->getConnection('IDAT');
		$r = $conn->execute('SHOW VARIABLES;')->fetchAll(Doctrine_Core::FETCH_ASSOC);
		echo print_r($r) . PHP_EOL;
		$r = $conn->execute('SHOW TRIGGERS;')->fetchAll(Doctrine_Core::FETCH_ASSOC);
		echo print_r($r) . PHP_EOL;
	
		echo  phpinfo();
	}
	
	public function triggerdoctorreciperequestAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}	
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		//run once
		$lock_file = sys_get_temp_dir() . "/" . "lock2_" . __CLASS__."_".__FUNCTION__;
		$now_time = time();
		if (file_exists($lock_file) ) {
			die("file_exists({$lock_file}) so we DO NOT RUN AGAIN !");
		}
		elseif ($filehandle = fopen($lock_file, "w+")) {
			//start new ftp upload
			fwrite($filehandle, $now_time ."\n". date("Y-m-d H:i:s"));
			fclose($filehandle);
		}
		
		$sendMail = array(
				"subject" => "Eine Rezept-Anforderung liegt vor.",
				"fromaddress" => "info@smart-q.de",
				"message" => "#patientlastname, #patientfirstname\n\nBitte loggen Sie sich unter https://www.ispc-login.de ein.",
		);
		$imput_sendMail = serialize($sendMail);
		
		$addInternalMessage = array(
				"title" => "Rezept-Anforderung",
				"content" => "",
				
		);
		$imput_addInternalMessage = serialize($addInternalMessage);

		$sendMail_id = Doctrine_Core::getTable('TriggerTriggers')->findOneByTriggername("sendMail");
		$sendMail_id = $sendMail_id->id;
		$addInternalMessage_id = Doctrine_Core::getTable('TriggerTriggers')->findOneByTriggername("addInternalMessage");
		$addInternalMessage_id = $addInternalMessage_id->id;
		$addValuetoToDos_id = Doctrine_Core::getTable('TriggerTriggers')->findOneByTriggername("addValuetoToDos");
		$addValuetoToDos_id = $addValuetoToDos_id->id;
		
		$TriggerForms_id = Doctrine_Core::getTable('TriggerForms')->findOneByFormname("RezeptAnforderung");
		$TriggerForms_id = $TriggerForms_id->id;
		
		$TriggerFields_id = Doctrine_Core::getTable('TriggerFields')->findOneByFieldname("frmDoctorRecipeRequest");
		$TriggerFields_id = $TriggerFields_id->id;
		
		
		$cleintids = Client::get_all_clients_ids();
		
		$create_date =  date("Y-m-d H:00:00");
// 		die_claudiu($cleintids);
		$records_array =  array();
		foreach($cleintids as $id){
			
			
			//sendMail
			$records_array[] =  array(
					"clientid" => $id,
					"fieldid" => $TriggerFields_id,
					"formid" => $TriggerForms_id,
					"triggerid" => $sendMail_id,
					"event" => 2,
					"operator" => 0,
					"operand" => "",
					"inputs" => $imput_sendMail,
					"isdelete" => 0,
					"create_date" => $create_date,
					"create_user" => $logininfo->userid,
					
			);
			//addInternalMessage
			$records_array[] =  array(
					"clientid" => $id,
					"fieldid" => $TriggerFields_id,
					"formid" => $TriggerForms_id,
					"triggerid" => $addInternalMessage_id,
					"event" => 2,
					"operator" => 0,
					"operand" => "",
					"inputs" => $imput_addInternalMessage,
					"isdelete" => 0,
					"create_date" => $create_date,
					"create_user" => $logininfo->userid,
						
			);
			
			//addValuetoToDos
			$records_array[] =  array(
					"clientid" => $id,
					"fieldid" => $TriggerFields_id,
					"formid" => $TriggerForms_id,
					"triggerid" => $addValuetoToDos_id,
					"event" => 2,
					"operator" => 0,
					"operand" => "",
					"inputs" => '',
					"isdelete" => 0,
					"create_date" => $create_date,
					"create_user" => $logininfo->userid,
			
			);
			

			
		}

		
		$collection = new Doctrine_Collection('FieldTrigger');
		$collection->fromArray($records_array);
		$collection->save();
		
		die("FINISH ... DO NOT RUN AGAIN !");
		
	}
	
	
	
	public function sapvorderAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		print_r("\n");
		print_r("------------------- START -------------------------");
		print_r("\n");
		echo date("d.m.Y H:i:s");
		print_r("\n");
				
				
		$dropSapv_all = Doctrine_Query::create()
		->select('count(*),ipid,sum(sapv_order) as order_sum')
		->from('SapvVerordnung')
		->where('verordnungbis !="000-00-00 00:00:00" ')
		->andWhere('verordnungam !="000-00-00 00:00:00" ')
		->andWhere('isdelete=0')
		->orderBy('verordnungam ASC')
		->groupBy('ipid')
		->having('order_sum = 0');
		$sapv_array_all = $dropSapv_all->fetchArray();
		
		if($sapv_array_all){
				
			foreach($sapv_array_all as $k=>$data){
				if($data['count'] == "1"){
					$ipids_first[] = $data['ipid'];
				} else {
					$ipids[] = $data['ipid'];
				}
			}
			
			if(!empty($ipids_first)){
				$ipids_first  =  array_unique($ipids_first);
				
				echo "The number of patients that have only one entry  that should be updated:  ";
				var_dump(count($ipids_first)); 
				print_r("\n");
	
				echo date("d.m.Y H:i:s");
				$sph = Doctrine_Query::create()
				->update('SapvVerordnung')
				->set('sapv_order', '1')
				->whereIn("ipid",$ipids_first);
				$sph->execute();
				
				print_r("\n after first_update: ");
				echo date("d.m.Y H:i:s");
				print_r("\n");
							
			} else {
				
				print_r("\n");
				echo "No data with one entry to update";
				print_r("\n");
			}	
			
			if(!empty($ipids)){
				
				$ipids  =  array_unique($ipids);
				$ipids_str = '"0", ';
				foreach($ipids as $ipid )
				{
					$ipids_str .= '"'.$ipid.'", ';
				}			
				
				if($ipids_str )
				{
					$ipids_str  = substr($ipids_str , 0, -2);
				}
				print_r("\n"); 
				print_r("Number of patients with other data: "); 
				var_dump(count($ipids)); 
				print_r("\n"); 
				
				// update all  with "2"
				print_r("-----------------------------------------------------------");
				print_r("\n");
				echo date("d.m.Y H:i:s");
				print_r("\n");
				$update_2 = Doctrine_Query::create()
				->update('SapvVerordnung')
				->set('sapv_order', '2')
				->whereIn("ipid",$ipids);
				$update_2->execute();
				
	
				print_r("\n after SECOND_update: ");
				echo date("d.m.Y H:i:s");
				print_r("\n");
				
				// get the the entries that need to be marked with 1 
				$conn = Doctrine_Manager::getInstance()->getCurrentConnection('IDAT');
				$q = 'SELECT id FROM `patient_sapvverordnung` pv
						WHERE pv.id = (
						SELECT pv2.id
						FROM `patient_sapvverordnung` pv2
						WHERE pv.ipid = pv2.ipid
						AND pv2.isdelete = 0
						ORDER BY pv2.`verordnungam` ASC
						LIMIT 1 )
						AND pv.ipid IN (' . $ipids_str  . ')
						AND pv.verordnungam != "1970-01-01 00:00:00"
						AND pv.verordnungbis != "1970-01-01 00:00:00"
						AND pv.verordnungbis != "000-00-00 00:00:00"
						AND pv.verordnungam <= pv.verordnungbis
						AND pv.verordnet != ""
						AND pv.isdelete = 0
						ORDER BY pv.verordnungbis ASC';
				$r = $conn->execute($q)->fetchAll();
				
				if($r){
					
					foreach($r as $k=>$sdata){
						$sapv_order1_ids[] =$sdata['id'];
					}
					
					
					if(!empty($sapv_order1_ids))
					{
						
						print_r("-----------------------------------------------------------");
						print_r("\n");
						echo date("d.m.Y H:i:s");
						print_r("\n");
						$update_1 = Doctrine_Query::create()
						->update('SapvVerordnung')
						->set('sapv_order', '1')
						->whereIn("id",$sapv_order1_ids);
						$update_1->execute();
						
						print_r("\n after THIRD_update: ");
						echo date("d.m.Y H:i:s");
						print_r("\n");
					}
				}
				
			}else {
				
				print_r("\n");
				echo "No data with OTHER entry to update";
				print_r("\n");
			}	
		}
		
		
		
		
		
		
		print_r("\n");
		print_r(" ------------------------------------------------------ ");
		print_r("\n");
		echo "ENTRIES THAT HAVE ZERO ";
		print_r("\n");
		print_r(" ------------------------------------------------------ ");
		print_r("\n");
		
		$dropSapv_zero_all = Doctrine_Query::create()
		->select('ipid')
		->from('SapvVerordnung')
		->where('verordnungbis !="000-00-00 00:00:00" ')
		->andWhere('verordnungam !="000-00-00 00:00:00" ')
		->andWhere('isdelete=0')
		->andWhere('sapv_order = 0')
		->orderBy('verordnungam ASC')
		->groupBy('ipid');
		$sapv_zero_all = $dropSapv_zero_all->fetchArray();
		
		foreach($sapv_zero_all as $lp=>$ss){
			$zero_ipids[] = $ss['ipid'];
			$zero_ipids_str .= '"'.$ss['ipid'].'", ';
		}
		
		print_r("\n");
		echo "0 order: ";
		var_dump(count($zero_ipids));
		print_r("\n");
		
		
		if($zero_ipids_str )
		{
			$zero_ipids_str  = substr($zero_ipids_str , 0, -2);
		}

		// FROM ZERO- select the ones they already have 1 
		$dropSapv_one_all = Doctrine_Query::create()
		->select('ipid')
		->from('SapvVerordnung')
		->where('verordnungbis != "000-00-00 00:00:00" ')
		->andWhere('verordnungam != "000-00-00 00:00:00" ')
		->andWhere('isdelete=0')
		->andWhere('sapv_order = 1')
		->andWhereIn('ipid',$zero_ipids)
		->orderBy('verordnungam ASC')
		->groupBy('ipid');
		$sapv_one_all = $dropSapv_one_all->fetchArray();
		
		foreach($sapv_one_all as $lp=>$all){
			$ones_ipids[] = $all['ipid'];
			$ones_ipids_str .= '"'.$all['ipid'].'", ';
		}

		print_r("\n");
		echo "1 order: ";
		var_dump(count($ones_ipids));
		print_r("\n");
		
		
		// FROM ZERO- select the ones they already have 1
		$dropSapv_2_all = Doctrine_Query::create()
		->select('ipid')
		->from('SapvVerordnung')
		->where('verordnungbis != "000-00-00 00:00:00" ')
		->andWhere('verordnungam != "000-00-00 00:00:00" ')
		->andWhere('isdelete=0')
		->andWhere('sapv_order = 2')
		->andWhereIn('ipid',$zero_ipids)
		->andWhereNotIn('ipid',$ones_ipids)
		->orderBy('verordnungam ASC');
		$sapv_2_all = $dropSapv_2_all->fetchArray();
		
		foreach($sapv_2_all as $lp=>$s2all){
			$two_ipids[] = $s2all['ipid'];
			$two_ipids_str .= '"'.$s2all['ipid'].'", ';
		}
		
		if($two_ipids_str )
		{
			$two_ipids_str  = substr($two_ipids_str , 0, -2);
		}
		
		print_r("\n");
		echo "2 order: ";
		var_dump(count($two_ipids));
		print_r("\n");
		
		if(!empty($two_ipids)){
			$dropSapv_22_all = Doctrine_Query::create()
			->select('ipid,sapv_order,verordnungam')
			->from('SapvVerordnung')
			->where('verordnungbis != "000-00-00 00:00:00" ')
			->andWhere('verordnungam != "000-00-00 00:00:00" ')
			->andWhere('isdelete=0')
			->andWhereIn('ipid',$two_ipids)
			->orderBy('verordnungam ASC');
			$sapv_22_all = $dropSapv_22_all->fetchArray();
			
			foreach($sapv_22_all as $k=>$sdada){
				$wt[$sdada['ipid']][] = $sdada;
				if($sdada['sapv_order'] == "2"){
					$nochange[] = $sdada['id']; 				
					$nochange_str .= '"'.$sdada['id'].'", ';
				}
			}
			
			if($nochange_str )
			{
				$nochange_str  = substr($nochange_str , 0, -2);
			}
			// update all with 2 
			
			$update_2 = Doctrine_Query::create()
			->update('SapvVerordnung')
			->set('sapv_order', '2')
			->whereIn("ipid",$two_ipids);
			$update_2->execute();
			
			
			// update all wit one except the ones from  nochange 
			
			// get the the entries that need to be marked with 1
			$conn = Doctrine_Manager::getInstance()->getCurrentConnection('IDAT');
			$q2 = 'SELECT id FROM `patient_sapvverordnung` pv
							WHERE pv.id = (
							SELECT pv2.id
							FROM `patient_sapvverordnung` pv2
							WHERE pv.ipid = pv2.ipid
							AND pv2.isdelete = 0
							ORDER BY pv2.`verordnungam` ASC
							LIMIT 1 )
							AND pv.ipid IN (' . $two_ipids_str  . ')
							AND pv.id NOT IN (' . $nochange_str  . ')
							AND pv.verordnungam != "1970-01-01 00:00:00"
							AND pv.verordnungbis != "1970-01-01 00:00:00"
							AND pv.verordnungbis != "000-00-00 00:00:00"
							AND pv.verordnungam <= pv.verordnungbis
							AND pv.verordnet != ""
							AND pv.isdelete = 0
							ORDER BY pv.verordnungbis ASC';
			$r2 = $conn->execute($q2)->fetchAll();
			
			if($r2){
					
				foreach($r2 as $k=>$sdata){
					$sapv_order01_ids[] =$sdata['id'];
				}
					
					
				if(!empty($sapv_order01_ids))
				{
			
					print_r("-----------------------------------------------------------");
					print_r("\n");
					echo date("d.m.Y H:i:s");
					print_r("\n");
					$update_1 = Doctrine_Query::create()
					->update('SapvVerordnung')
					->set('sapv_order', '1')
					->whereIn("id",$sapv_order01_ids);
					$update_1->execute();
			
					print_r("\n after THIRD_update: ");
					echo date("d.m.Y H:i:s");
					print_r("\n");
				}
			}
		} else if(empty($two_ipids) && !empty($ones_ipids)){
		        
			print_r("-----------------------------------------------------------");
			print_r("\n");
			echo date("d.m.Y H:i:s");
			print_r("\n");
			
			$update_2os = Doctrine_Query::create()
			->update('SapvVerordnung')
			->set('sapv_order', '2')
			->whereIn("ipid",$ones_ipids)
			->andWhere("sapv_order =?","0");
			$update_2os->execute();
	
			print_r("\n after forth_update: ");
			echo date("d.m.Y H:i:s");
			print_r("\n");
		    
		}

		
		
		print_r("\n");
		print_r("-------------------END -------------------------");
		print_r("\n");
		echo date("d.m.Y H:i:s");
		print_r("\n");
		
		
		
		
		
	}
	
	public function populateregistertextsAction(){
		
// 		exit;
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		print_r("\n");
		print_r("------------------- START -------------------------");
		print_r("\n");
		echo date("d.m.Y H:i:s");
		print_r("\n");
		

		
		
		$values_134 = array(
			"Diagnostik",
			"Edukation Patient und/ oder Beteiligte zum Umgang mit Schmerzereignissen inkl. Anleitung",
			"Beratung und Unterstützung bei Therapieentscheidungen",
			"Assessment von Ressourcen",
			"Edukation Patient und/ oder Beteiligte zur Entwicklung und Gewinnung von Ressourcen",
			"Beratung und Unterstützung zur Ressourcengewinnung",
			"Ernährungsberatung",
			"Beratung und Unterstützung zur Therapieentscheidung für oder gegen parenterale/ enterale Ernährung Beratung und Unterstützung bei psychosozialen Entscheidungen",
			"Anpassung der medikamentösen Schmerztherapie",
			"nichtmedikamentöse Maßnahmen zur Schmerzreduktion und zum Umgang mit Schmerzereignissen Optimierung der Therapie",
			"Infektionsprophylaxe",
			"Notfallplanung, Notfallverfügung",
			"Anpassung lokaler Maßnahme",
			"Geruchsmanagement",
			"angepasste Hautpflege",
			"Milieugestaltung",
			"Behandlungskoordination",
			"Hilfekoordination",
			"Unterstützung im Umgang mit Sterben und Tod",
			"psycho-soziale Entlastungsgespräche"
		);
		
		$values_135 = array(
			"Vorhandene bzw. verfügbare ambulante Versorgungsformen reichen nicht aus",
			"Mittel der Regelversorgung reichen nicht aus I komplexes Symptomgeschehen",
			"viele beteiligte Bezugspersonen und/oder Dienstleister",
			"Angehörige befinden sich nicht in unmittelbarer Nähe",
			"Gefühl der sozialen Isolation",
			"hohe psychosoziale Belastungssituation",
			"finanzielle Notsituation",
			"kognitive Einschränkung des Patienten oder Zugehörigen"
		);
		$field_name_arr_134 = array("bedarf","massnahmen");
		$field_name_arr_135 = array("aufwand_mit");
		
		$cust = Doctrine_Query::create()
		->select('id')
		->from('Client')
		->where('isdelete = 0');
		$clientarray = $cust->fetchArray();
 
		
		foreach($clientarray as $k=>$cl_id)
		{
			foreach($field_name_arr_134 as $field_name){
				foreach($values_134 as $k=>$field_value){
					$record_arr[] = array(
						'clientid'=>$cl_id['id'],
						'field_name'=>$field_name,
						'field_value'=>$field_value
					);
				}
			}

			foreach($field_name_arr_135 as $field_name135){
				foreach($values_135 as $k=>$field_value135){
					$record_arr[] = array(
						'clientid'=>$cl_id['id'],
						'field_name'=>$field_name135,
						'field_value'=>$field_value135
					);
				}
			}
			
		}
// 		print_r($record_arr); exit;

		if(!empty($record_arr)){
			$collection = new Doctrine_Collection('RegisterTextsList');
			$collection->fromArray($record_arr);
			$collection->save();
		}
		
		print_r("\n");
		print_r("-------------------END -------------------------");
		print_r("\n");
		echo date("d.m.Y H:i:s");
		print_r("\n");
		
		
		exit;
	}
	/*
	public function testqqAction(){

		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$qq = new Zend_Session_Namespace('qqFileUpload');
		echo "<pre>";
		print_r($qq->getIterator());
		exit;
	}
	*/
	
	/*
	public function testdoctrineAction(){
	
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	
	    $file = dirname(__FILE__) . "/../Bootstrap.php";
	    echo nl2br(htmlentities(file_get_contents($file)));
	    
	       $manager = Doctrine_Manager::getInstance();
	      var_dump( $manager->getAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS));
	      exit;	

	      
	     
	}
	*/
	
	public function updatepnbAction(){
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		exit;
		$epids = array(
				"PNB1231",
				"PNB2228",
				"PNB1342",
				"PNB1352",
				"PNB1215",
				"PNB1498",
				"PNB1236",
				"PNB2060",
				"PNB1295",
				"PNB1188",
				"PNB1186",
				"PNB1255",
				"PNB1220",
				"PNB1885",
				"PNB1497",
				"PNB1723",
				"PNB1281",
				"PNB1155",
				"PNB4533",
				"PNB1237");
		
		// get data in patient 
		$actpatient= Doctrine_Query::create();
		$actpatient->select("e.epid,e.ipid, pd.*,p.ipid, p.admission_date, p.isdischarged, p.isstandby,p.last_update,p.last_update_user");
		$actpatient->from('EpidIpidMapping e');
		$actpatient->leftJoin("e.PatientMaster p");
		$actpatient->leftJoin("e.PatientDischarge pd");
		$actpatient->whereIn('e.epid',$epids);
		$actipidarray = $actpatient->fetchArray();
		
		foreach($actipidarray as $k=>$pat){
			$ipids[] = $pat['ipid']; 
			$all_data[$pat['ipid']] = $pat;
		}
		 
		// get data in patient readmission
		$readm_q = Doctrine_Query::create()
		->select('*')
		->from('PatientReadmission')
		->whereIn('ipid', $ipids);
		$readm_arr = $readm_q->fetchArray();
		
		foreach($readm_arr as $red=>$red_val){
			$all_data[$red_val['ipid']]['PatientReadmission'][] = $red_val;
		}
		
		print_r($all_data);
		$pream_data = "";
		foreach($all_data as $ipid=>$det){

			if(empty($det['PatientReadmission'])){
				//insert admission and discharge
				$status['no_readmission_info'][] =$ipid;
			} elseif(count($det['PatientReadmission']) == 1 ){
				if($det['PatientReadmission'][0]['date_type'] == "1" && count($det['PatientDischarge']) == "1" && $det['PatientMaster']['isdischarged'] == "1" ){
					$pream_data = $det['PatientDischarge'][0];
					
					if(strtotime($pream_data['discharge_date']) >= strtotime($det['PatientMaster']['admission_date']) && strtotime($det['PatientMaster']['admission_date']) == strtotime($det['PatientReadmission'][0]['date']) ){
						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = $pream_data['create_user'];
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $pream_data['discharge_date'];
						$patientreadmission->date_type = 2; //1 =admission-readmission 2- discharge
						$patientreadmission->save();
	 
						
						$update_active = PatientMaster::get_patient_admissions($ipid);
					
						$status['DISCHARGE_INFO_INSERTED'][] =$ipid;
					} else{
						$status['invalid periods'][] =$ipid;
					}
				}
			}else{
				$status['no_actions'][] =$ipid; 
			}
			
		}
		print_r($status); exit;
		exit;
		
	}
	
	
	
	
	
	
	

	public function clientexportAction()
	{
		
		
		$clist = Doctrine_Query::create()
		->select("
					(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) as client_name,
				
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					team_name")
							->from('Client')
							->where('isdelete=0')
							->orderBy("(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) ASC");
		$clientlist = $clist->fetchArray();		
		

		$i = 1;
		foreach($clientlist  as $k=>$cdata){
			$export_array[$i]['Mandant'] = trim($cdata['client_name']);
			$export_array[$i]['Straße'] = $cdata['street1'];
			$export_array[$i]['Straße 2'] = $cdata['street2'];
			$export_array[$i]['PLZ'] = $cdata['postcode'];
			$export_array[$i]['Stadt'] = $cdata['city'];
			$export_array[$i]['Vorname'] = $cdata['firstname'];
			$export_array[$i]['Nachname'] = $cdata['lastname'];
			$export_array[$i]['Email Adresse'] = $cdata['emailid'];
			$export_array[$i]['Team Name'] = $cdata['team_name'];
			
			$i++;
		}
		
// 		print_r($export_array ); exit;
		$this->generateCSVclient($export_array, 'export.xls', false, 'all');
		exit;
	}

	/**
	 * Ancuta 10.12.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
	 *   export of all mmi clients in ispc
	 */
	
	public function mmiclientexportAction()
	{
	    $modules_array = array('87');
	    
	    $mmi_clients = Modules::clients2modules($modules_array);
	    
	    $clist = Doctrine_Query::create()
	    ->select("
		(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) as client_name,

		AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
		AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
		AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
		AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
		AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
		AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
		AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
		team_name")
		->from('Client')
		->where('isdelete=0');
		if(!empty($mmi_clients)){
		    $clist->andWhereIn('id',$mmi_clients);
		    
		}
		$clist->orderBy("(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) ASC");
		$clientlist = $clist->fetchArray();
					
		$i = 0;
		$export_array = array();
		$export_array[$i]['Mandant'] = 'Mandant';
		$export_array[$i]['Straße'] = 'Straße';
		$export_array[$i]['PLZ'] = 'PLZ';
		$export_array[$i]['Stadt'] = 'Stadt';
		$export_array[$i]['Vorname'] = 'Vorname';
		$export_array[$i]['Nachname'] = 'Nachname';
		$export_array[$i]['Email Adresse'] ='Email Adresse';
		$export_array[$i]['Team Name'] = 'Team Name';
		$i = 1;
		foreach($clientlist  as $k=>$cdata){
		    $export_array[$i]['Mandant'] = trim($cdata['client_name']);
		    $export_array[$i]['Straße'] = $cdata['street1'];
		    $export_array[$i]['PLZ'] = $cdata['postcode'];
		    $export_array[$i]['Stadt'] = $cdata['city'];
		    $export_array[$i]['Vorname'] = $cdata['firstname'];
		    $export_array[$i]['Nachname'] = $cdata['lastname'];
		    $export_array[$i]['Email Adresse'] = $cdata['emailid'];
		    $export_array[$i]['Team Name'] = $cdata['team_name'];
		    
		    $i++;
		}
		
		// 		print_r($export_array ); exit;
		$this->generateCSVclient($export_array, 'export.xls', false, 'all');
		exit;
	}
	
	public function clientexportfullAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout->setLayout('layout');
	    $this->_helper->viewRenderer->setNoRender();
		$clist = Doctrine_Query::create()
		->select("
					(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) as client_name,
				
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax,
					AES_DECRYPT(emergencynr_a,'" . Zend_Registry::get('salt') . "') as emergencynr_a,
					AES_DECRYPT(emergencynr_b,'" . Zend_Registry::get('salt') . "') as emergencynr_b,
					team_name")
							->from('Client')
							->where('isdelete=0')
// 							->andWhere('isactive=0')
							->orderBy("(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) ASC");
		$clientlist = $clist->fetchArray();		

		$i = 0;
			$export_array[$i]['Mandant'] = "Mandant";
			$export_array[$i]['Mandant 2'] = "Mandant 2";
			$export_array[$i]['Straße'] = "Straße";
			$export_array[$i]['Straße 2'] = "Straße 2";
			$export_array[$i]['PLZ'] ="PLZ";
			$export_array[$i]['Stadt'] = "Stadt";
			$export_array[$i]['Vorname'] = "Vorname";
			$export_array[$i]['Nachname'] = "Nachname";
			$export_array[$i]['Email Adresse'] = "Email Adresse";
			$export_array[$i]['Telefon'] = "Telefon";
			$export_array[$i]['Fax'] = "Fax";
			$export_array[$i]['Emergency 1'] = "Emergency 1";
			$export_array[$i]['Emergency 2'] = "Emergency 2";
		$i++;	
		foreach($clientlist  as $k=>$cdata){
			$export_array[$i]['Mandant'] = trim($cdata['client_name']);
			$export_array[$i]['Mandant 2'] = $cdata['team_name'];
			$export_array[$i]['Straße'] = $cdata['street1'];
			$export_array[$i]['Straße 2'] = $cdata['street2'];
			$export_array[$i]['PLZ'] = $cdata['postcode'];
			$export_array[$i]['Stadt'] = $cdata['city'];
			$export_array[$i]['Vorname'] = $cdata['firstname'];
			$export_array[$i]['Nachname'] = $cdata['lastname'];
			$export_array[$i]['Email Adresse'] = $cdata['emailid'];
			$export_array[$i]['Telefon'] = $cdata['phone'];
			$export_array[$i]['Fax'] = $cdata['fax'];
			$export_array[$i]['Emergency 1'] = $cdata['emergencynr_a'];
			$export_array[$i]['Emergency 2'] = $cdata['emergencynr_b'];
			
			$i++;
		}
		$this->generateCSVclient($export_array, 'export.xls', false, 'all');
		exit;
	}
	
	
	
	public function generateCSVclient($data, $filename = 'export.xls')
	{
		$file = fopen('php://output', 'w');
		
		
		fputcsv($file, array(''));
		
		foreach($data as $k => $data)
		{
			fputcsv($file, $data);
		}
	
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-dType: application/octet-stream");
		header("Content-type: application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment; filename=" . $filename);
		exit;
	}
	
	
	public function addvountaryworkersstatusesAction(){
			
		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();
			
		// get all clients
		$clt = Doctrine_Query::create()
		->select("id")
		->from('Client');
		//->whereNotIn('id', array(1, 102, 72, 91));
		//->orderBy("id ASC");
		$cltarray = $clt->fetchArray();
	
		//get all old statuses
		$st = Doctrine_Query::create()
		->select("*")
		->from('VoluntaryworkersStatusesDetails');
		$starray = $st->fetchArray();
			
		foreach($cltarray as  $cl_id)
		{
			foreach($starray as $st_data)
			{
				//new voluntaryworkers statuses table
				$stvw = new VoluntaryWorkersSecondaryStatuses();
	
				$stvw->clientid = $cl_id['id'];
				$stvw->description = $st_data['description'];
				if($st_data['status_id'] == 'n')
				{
					$stvw->isdelete = '1';
				}
				$stvw->save();
	
				if($stvw)
				{
					$inserted_id = $stvw->id;
				}
				//update vw attached statuses with the id from the new table
				$qvwst = Doctrine_Query::create()
				->update('VoluntaryworkersStatuses')
				->set('status', '"' . $inserted_id . '"')
				->where('clientid = ? and status_old = ?', array($cl_id['id'], $st_data['status_id']));
				$qvwst->execute();
			}
		}
		exit;
	}
	
	
	public function updatemoreinfodkAction ()
	{
	    exit;
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $dks = array();
	    $flower = new PatientMoreInfo();
	    $flower = $flower->getTable()->createQuery()
	    ->select('id, ipid, dk')
	    ->where("dk = 1")
	    ->fetchArray();
	    $dks = array_column($flower, 'ipid');
	    
	    
// 	    dd(count($flower));
	    
	    
	    $fix_flower = new Stammdatenerweitert();
	    $collection = $fix_flower->getTable()->createQuery()
	    ->select('id, ipid, ausscheidung')
	    ->whereIn("ipid", $dks)
// 	    ->limit(10)
	    ->execute(null, Doctrine_Core::HYDRATE_RECORD);
	    ;
	    
	    $ipids_nodks = array();
	    $iterableResult = $collection->getIterator();
	    
	    foreach ($iterableResult as $entity) {
	        if (in_array($entity['ipid'], $dks) && strpos($entity->ausscheidung, '4') === false) {  //redundant if
	            $entity->ausscheidung =  trim($entity->ausscheidung == '') ? '4' : $entity->ausscheidung . ',4';
// 	            $entity->save();
	            $x = $entity;
	            $x->save();
	            $x->free();
	        }

	        array_push($ipids_nodks , $entity->ipid);
	        $entity->free();
	    }
	    
	    $new_ipids = array_diff($dks, $ipids_nodks);
	    
	    $fix_flower = new Stammdatenerweitert();
	    foreach ($new_ipids as $ipid) {
	        $fix_flower->getTable()->create(array('ipid' => $ipid, 'ausscheidung' => '4'))->save();
	    }
	     
	    
	    die(__METHOD__ . __LINE__ . '<br/> : with MoreInfo '.count($flower) . '<br/> please RUN ONLY ONCE if no error' . '<br/>updated:'. count($ipids_nodks). '<br/> new ipid: '. count($new_ipids));
	    
	    
		exit;
	    
	}
	
	
	public function updatecoursetodosAction(){
 		exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		// select all todos triggered by 
		
		$todo = Doctrine_Query::create()
		->select("course_id ")
		->from('ToDos')
		->where('iscompleted = 0')
		->andWhere('isdelete = 0')
// 		->andWhere('ipid = "cf14294f08efbfacbf8f87e7bcf9da7e59939a18" ')
		->andWhere('triggered_by = "frmFormBlockTodos" ');
		$todoarray = $todo->fetchArray();
			
	
		
		$todos_cids = array();
		foreach($todoarray as  $k=>$td){
			$todos_cids[] = $td['course_id'];
			$todos_cids_str .= $td['course_id'].", ";
		}
		print_r($todos_cids_str );
		print_r($todos_cids);
						
		
		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name")
								->from('PatientCourse')
								->whereIn('id',$todos_cids)
// 								->andWhere('ipid = "cf14294f08efbfacbf8f87e7bcf9da7e59939a18" ')
		;
		$patientarray = $patient->fetchArray();
		
		print_r("initial");
		print_r($patientarray);
		
		$replaced = array();
		foreach($patientarray as $k=>$pcdata){
			
				$iinital_data[$pcdata['id']] = $pcdata['course_title']; 
				$course_title_rep = "";
				if(!strpos($pcdata['course_title'], "|---------|")){
					$course_title_rep  = str_replace("|", '|---------|', $pcdata['course_title']);
					$replaced[] = $pcdata['id'];
				}
							
				$course_title =  explode( "|---------|",$course_title_rep);
				 if(count($course_title) == "3"){
				 	$course_title_rep .=" |---------| 0";
				 }
				
				$patientarray[$k]['course_title_rep']= $course_title_rep;

				if(in_array($pcdata['id'],$replaced)){
					$update_pc = Doctrine::getTable('PatientCourse')->find($pcdata['id']);
					if($update_pc ){
// 						$update_pc->course_title =  Pms_CommonData::aesEncrypt(addslashes($course_title_rep));
// 						$update_pc->save();
					}
				}
				
		}
		
		print_r("  \n data to be replaced \n"); 
		print_r($iinital_data);
		print_r("  \n  replaced \n"); 
		print_r($replaced);

		
		
		$patient_new = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name")
								->from('PatientCourse')
								->whereIn('id',$todos_cids);
		$patientarray_new = $patient_new->fetchArray();
		

		print_r("second");
		print_r($patientarray_new);
		exit;
		
	}
	
	public function updatecoursetodoscompletedAction(){
		exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		$ipid =  "cf14294f08efbfacbf8f87e7bcf9da7e59939a18";		
		
		
		
		// select all todos triggered by 
 
		$todo = Doctrine_Query::create()
		->select("course_id ,complete_user,complete_date")
		->from('ToDos')
		->where('iscompleted = 1')
		->andWhere('isdelete = 0')
		->andWhere('ipid = ?',$ipid)
		->andWhere('triggered_by = "frmFormBlockTodos" ');
		$todoarray = $todo->fetchArray();
// 		print_r($todoarray );
			
		foreach($todoarray as  $k=>$td){
			$users_ids[] =  $td['complete_user'];
		}
		$user = new User();
		$user_details = $user->getMultipleUserDetails($users_ids);
	
// 		print_r($user_details);
		
		
		$username = $userdata[0]['first_name'].' '.$userdata[0]['last_name'];
		
		
		
		$todos_cids = array();
		foreach($todoarray as  $k=>$td){
			$todos_cids[] = $td['course_id'];
			$todos_cids_str .= $td['course_id'].", ";
			
			$complete_texts[$td['course_id']] = $td['complete_user'].", Erledigt von ".$user_details[$td['complete_user']]['first_name']." ".$user_details[$td['complete_user']]['last_name']." am ".date('d.m.Y',strtotime($td['complete_date']));
		}
		print_r($todos_cids_str );
		print_r($complete_texts );
		print_r($todos_cids);
						
		
		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name")
								->from('PatientCourse')
								->whereIn('id',$todos_cids)
								->andWhere('ipid = ?', $ipid)
		;
		$patientarray = $patient->fetchArray();
		
		print_r("initial");
		print_r($patientarray);

		$replaced = array();
		foreach($patientarray as $k=>$pcdata){
			
				$iinital_data[$pcdata['id']] = $pcdata['course_title']; 
				$course_title_rep = "";
				if(!strpos($pcdata['course_title'], "|---------|")){
					$course_title_rep  = str_replace("|", '|---------|', $pcdata['course_title']);
					$replaced[] = $pcdata['id'];
				}
							
				$course_title =  explode( "|---------|",$course_title_rep);
				
// 				$course_title_string = 
				 if(count($course_title) == "3"){
				 	$course_title_rep .=" |---------| 0";
				 }
				
				 $course_title_rep .=" |---------|".$complete_texts[$pcdata['id']] ;
				$patientarray[$k]['course_title_rep']= $course_title_rep;

				if(in_array($pcdata['id'],$replaced)){
					$update_pc = Doctrine::getTable('PatientCourse')->find($pcdata['id']);
					if($update_pc ){
						$update_pc->course_title =  Pms_CommonData::aesEncrypt(addslashes($course_title_rep));
						$update_pc->save();
					}
				}
				
		}
		
		print_r("  \n data to be replaced \n"); 
		print_r($iinital_data);
		print_r("  \n  replaced \n"); 
		print_r($replaced);
		print_r("  \n  replaced data \n"); 
		print_r($patientarray);

		
		
		$patient_new = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name")
								->from('PatientCourse')
								->whereIn('id',$todos_cids);
		$patientarray_new = $patient_new->fetchArray();
		

		print_r("second");
		print_r($patientarray_new);
		exit;
		
	}
	
	public function cleandgppatienthistroyAction(){
		exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		
		$phist = Doctrine_Query::create()
		->select("*")
		->from('DgpPatientsHistory')
		->where('user != 0');
		$phistarray = $phist->fetchArray();
		
		$ipids = array();
		foreach($phistarray as $k=>$ph){
			$ipids[] =  $ph['ipid'];
		}
		$ipids = array_unique($ipids);
		$ipids = array_values($ipids);
		

		// get ipids per clients
		$actpatient = Doctrine_Query::create()
		->select("ipid,clientid")
		->from('EpidIpidMapping')
		->whereIn('ipid',$ipids);
		$actipidarray = $actpatient->fetchArray();
		 
		foreach($actipidarray as $ek=>$epat){
			$patient_client[$epat['ipid']] = $epat['clientid'];
		}
		
		//407615da5010b41979e55f1774da21149db6dd82
		$remove_array = array();
		foreach($phistarray as $k=>$ph_data){

			if($ph_data['client'] != $patient_client[$ph_data['ipid']]){
				$remove_array_data[] = $ph_data['id'].' '.$ph_data['ipid'].' Wrong client:'.$ph_data['client'].' Corect client: '.$patient_client[$ph_data['ipid']];
				$remove_array[] = $ph_data['id'];
			}
		}
		print_r($phistarray);
		print_r("\n");
		print_r(count($remove_array_data)); 
		print_r("\n");
		exit;
// 		if(!empty($remove_array)){
// 			$ph_delete = Doctrine_Query::create()
// 			->delete('DgpPatientsHistory')
// 			->whereIn('id',$remove_array);
// 			$ph_delete->execute();
// 		}
		
		
		$phist_2 = Doctrine_Query::create()
		->select("id")
		->from('DgpPatientsHistory')
		->where('user != 0');
		$phistarray_2 = $phist_2->fetchArray();
		print_r("\n");
		var_dump(count($phistarray_2));
		exit;
		
		
		//0355474ebb811cbc071bf246f4b1a795c6c76b10
// 		print_r($patient_client); exit;
		
	}
	
	
	public function memberinvoicesfixAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		// membership details
		$qm1 = Doctrine_Query::create()
		->select('*')
		->from('Memberships')
		->andWhere("isdelete='0'");
		$medicss = $qm1->fetchArray();
		
		$membership_data = array();
		foreach($medicss as $k=>$md){
			$membership_data[$md['id']]['membership'] = $md['membership'];
			$membership_data[$md['id']]['shortcut'] = $md['shortcut'];
		}
		
		// member to membership
		$medic = Doctrine_Query::create()
		->select('*')
		->from('Member2Memberships');
		$medics = $medic->fetchArray();
		
		foreach($medics  as $k=>$ms){
			$member2membership[$ms['id']] = $membership_data[ $ms['membership']]; 
		}
		
		
		// member invoices
		$q1 = Doctrine_Query::create()
		->select("*")
		->from('MembersInvoices');
		$minvoices = $q1->fetchArray();
		
		foreach($minvoices as $k=>$miv){
			$invoices_ids[] = $miv['id'];
			$invoice2membershipdata[$miv['id']] = $member2membership[$miv['membership_data']];
		}

		// member invoice items
		$q2 = Doctrine_Query::create()
		->select("*")
		->from('MembersInvoiceItems');
		$minvoices_items = $q2->fetchArray();
		
		foreach($minvoices_items as $k=>$invitems){
			if( empty($invitems['shortcut']) &&  empty($invitems['description']) && $invitems['custom'] == 0){
				$wrongitems[$invitems['invoice']][] = $invitems;
			}
		}

		// UPDATE invoice items
		foreach($wrongitems as $inv=>$items){
			if(count(items) == 1)
			{
				foreach($items as $ki=>$it)
				{
					$ref = Doctrine::getTable('MembersInvoiceItems')->find($it['id']);
					if($ref && !empty($invoice2membershipdata[$it['invoice']])){
						$ref->description = $invoice2membershipdata[$it['invoice']]['membership'];
						$ref->shortcut = $invoice2membershipdata[$it['invoice']]['shortcut'];
						$ref->save();
						$updated_invoice[] = $it['invoice'];
					}
				}
			}
		}
		
		print_R($updated_invoice); exit;
		
	}
	 
	
	private function  _ob_flush()
	{
	    ob_end_flush();
	    ob_flush();
	    flush();
	    ob_start();
	}
	
	
	/**
	 * ISPC-2071
	 */
	public function updatepatientcourse2contactformAction()
	{
	    set_time_limit(10 * 60);
	    exit;
	    /**
	     * TODO : LOCK/UNLOCK TABLE PC ?
	     */
	    /**
	     * TODO: optional..but RECOMMENDED INDEX:
	     * 
	     * ALTER TABLE `patient_course` ADD INDEX ( `done_id` ) ; #=> 200 Mb new index space
	     * 
	     * ... table has now 5.08 Gb and the index 1.86Gb 
	     */
	    
	    
	    
	    $start = microtime(true);
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    
// 	    Doctrine_Core::getTable('PatientCourse')->hasOne('PatientFileUpload', array(
// 	        'local' => 'recordid',
// 	        'foreign' => 'id'
// 	    ));
	    
// 	    // file details
// 	    $qm1 = Doctrine_Query::create()
// 	    ->select('pc.*, pf.*') 
// 	    ->from('PatientCourse pc')
// 	    ->leftJoin('pc.PatientFileUpload pf ON (pc.ipid = pf.ipid AND pf.id = pc.recordid) ')
// // 	    ->where('pc.ipid = \'8ecef26eac5103c6f10e773241a04c5169b2e11b\'')
// 	    ->where("AES_DECRYPT(pc.tabname, 'encrypt') IN ('contact_form_save')")
// 	    ->andWhere("pc.done_name = ''")
// 	    ->andWhere('pc.done_id = 0')
// 	    ->limit(100)
// // 	    ->fetchArray();
// 	    ;
// 	    dd($qm1);
// 	    Doctrine_Table::
	    
	    /*
	     * update patient_course rows about pdf genereated by a contact_form
	     * set_time_limit(xxxxxx) because we have to update row by row ~800k
	     *  Showing rows 0 - 29 (689813 total, Query took 0.6384 sec)
	     */
	    
	    
	    
	    
	    $tabnames = array(
	        //this are all in contactform
	        'contact_form',
	        
	        'fahrtzeit_block',
	        'fahrtstreke_km_block',
	        'comment_block',
	        'quality_block',
	        'internal_comment_block',
	        'comment_apotheke_block',
	        'case_history_block',
	        'care_instructions_block',
	        'befund_txt_block',
	        'therapy_txt_block',
	        'karnofsky_block',
	        'ecog_block_old',//removed from php
	        'contact_form_measures', //this is 2 different ones?
	        'comments_l_block', // this inside a foreach without delete
	        
	        
	        'cf_client_symptoms', //this is Symptome II
	        'PatientSymptomatology',//this is Symptome, Symptome ZAPV I, Symptome ZAPV II
	        
	        
	        'contact_form_change_date',
	        'contact_form_change_begin',
	        'contact_form_change_end',
	        'contact_form_first_date',  // TODO : change or remove thin on live
	    
	        'ContactFormServiceEntry',
	        'FormBlockAdditionalUsers',
	        'FormBlockBefund',
	        'FormBlockBowelMovement',
	        'FormBlockBraSapv',
	        'FormBlockClassification',
	        'FormBlockEbmBer',
	        'FormBlockEbmi',
	        'FormBlockEbmii',
	        'FormBlockGoai',
	        'FormBlockGoaii',
	        'FormBlockHospizmedi',
	        'FormBlockSgbv',
	        'FormBlockVentilation',
	        'FormBlockVitalSigns',
	        'FormBlockTracheostomy',
	        'FormBlockSgbxiActions',
	        'FormBlockHospizimex',
	        'FormBlockDrivetimedoc',
	    
	    );
	    $tabnames = array_combine($tabnames, $tabnames);
	    $tabnames = Pms_CommonData::aesEncryptMultiple($tabnames);
	    foreach ($tabnames as $tab=>$tab_encrypted) {
	        $$tab = $tab_encrypted;
	    }
	    
	    $contact_form_encrypted = $contact_form;
	    
// 	    $contact_form_encrypted = Pms_CommonData::aesEncrypt("contact_form");
	    
	    
	    $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
	    
	    
	    $step = $this->getRequest()->getQuery('step', 1);
	    
	    
	    if ($step == '1' || $step == 'NinjaTurtle') {
    	    /**
    	     * step1 pdf files
    	     */
    	    $sql_txt = "
    	    	    SELECT 
    	        pc.id as pc_id, 
    	        pf.id as pf_id, 
    	        pc.recordid as pc_recordid,
    	        pf.recordid as pf_recordid, 
    	        pf.tabname as pf_tabname, 
    	        pc.done_id as pc_done_id ,  
    	        aes_decrypt(pc.tabname, 'encrypt'), 
    	        aes_decrypt(pc.course_title, 'encrypt'), 
    	        aes_decrypt(pc.done_name, 'encrypt') , 
    	        aes_decrypt(pf.file_name, 'encrypt')
    	    	    FROM `patient_course` pc
    	    	    LEFT JOIN patient_file pf ON (pc.ipid = pf.ipid AND pf.id = pc.recordid)
        	    WHERE
    	    	   /*  AES_DECRYPT(pc.tabname, 'encrypt') IN ('contact_form_save') */
    	           pc.tabname =  AES_ENCRYPT('contact_form_save', 'encrypt') 
    	    	   AND pc.done_name = ''
    	        LIMIT 300000
    	    	    
    	    ";//AND pc.done_id = 0
    	   
    	    $collection = $conn->execute($sql_txt);
    	    
    	    $cnt = 0;
    	    while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
    	        
    	        if ($row['pc_done_id'] > 0) {
    	             
    	            $sql_update = "UPDATE `patient_course` SET `done_name` = :done_name WHERE `id`= :pc_id";
    	            
    	            $params = array(
    	                'done_name'    => $contact_form_encrypted,
    	                'pc_id'        => $row['pc_id']
    	            );
    	        } else {
    
    	            $sql_update = "UPDATE `patient_course` SET `done_name` = :done_name, `done_id` = :done_id WHERE `id`= :pc_id";
    	            $params = array(
    	                'done_name'    => $contact_form_encrypted,
    	                'done_id'      => $row['pf_recordid'],
    	                'pc_id'        => $row['pc_id']
    	            );
    	        }
    	        $stmt = $conn->execute($sql_update, $params);	  
    	        $stmt->closeCursor();
    	        $cnt++;
    	        
    	        $row = null;
    	        unset($row);
    	    }
    	    
    	    $collection = null;
    	    unset($collection);
    	    
            echo ('pdfs : '. $cnt . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
            $start = microtime(true);
            $this->_ob_flush();
    
            if ($cnt != 0 && $step != 'NinjaTurtle') {
                die("Step 1 Semi-Finisded we must repeat, <a href='?step=1' target='_self' >click here to continue [STEP 1]</a>");
                
            }
            elseif ($cnt == 0 && $step != 'NinjaTurtle') {        
                die("Step 1 Finisded, <a href='?step=2.1' target='_self' >click here for [STEP 2.1]</a>");
            }
            
            $cnt = 0;
	    }
        
        
        /**
         * step 2
         */
	    if ($step == '2.1' || $step == 'NinjaTurtle') {
            /**
             * step 2.1
             */
            //contact_form_measures
            $sql_txt = "
    	    	SELECT
        	        id,
        	        aes_decrypt(course_type, 'encrypt') as course_type,
        	        aes_decrypt(course_title, 'encrypt') as course_title
        	    FROM `patient_course`
                WHERE
                    done_id > 0
        	        AND tabname = ''
    	    	    /* AND AES_DECRYPT(done_name, 'encrypt') = 'contact_form_measures' */
    	    	    AND done_name = AES_ENCRYPT('contact_form_measures', 'encrypt')
            
    	    ";
            $collection = $conn->execute($sql_txt);
            $cnt = 0;
            while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
                
                if (strpos($row['course_title'] , 'Maßnahmen : ') === 0 ) {                
                    $tabname = $contact_form_measures;
                    $done_name = $contact_form;
                }
                
                elseif (strpos($row['course_title'] , 'SGB XI Leistungen :') === 0 ) {
                    
                    $tabname = $FormBlockSgbxiActions;
                    $done_name = $contact_form;
                    
                } else {
    
                    die('ERROR line:' .__LINE__ . print_r($row, true));
                }
                
                $sql_update = "UPDATE `patient_course` SET `tabname`= :tabname, `done_name` = :done_name WHERE `id`= :pc_id";
                $params=  array(
                    'pc_id' => $row['id'],
                    'tabname' => $tabname,
                    'done_name' => $done_name,
                );
                
                $stmt = $conn->execute($sql_update, $params);
                $stmt->closeCursor();
                $cnt++;
                 
                $row = null;
                unset($row);
            }
             
            $collection = null;
            unset($collection);
            
            echo ('split FormBlockSgbxiActions from FormBlockMeasures same done_name=contact_form_measures: '. $cnt . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
            $cnt = 0;
            $start = microtime(true);
            $this->_ob_flush();
            
            
            if ($step != 'NinjaTurtle') {
                die("Step 2.1 Finisded, <a href='?step=2.2' target='_self' >click here for [STEP 2.2]</a>");
            }
	    }
	    
	    if ($step == '2.2' || $step == 'NinjaTurtle') {
            /**
             * step 2.2
             */
            //contact_form_drivetimedoc
            $sql_txt = "
    	    	SELECT
        	        id,
        	        aes_decrypt(course_type, 'encrypt') as course_type,
        	        aes_decrypt(course_title, 'encrypt') as course_title
        	    FROM `patient_course`
                WHERE
                    done_id > 0
        	        AND tabname = ''
    	    	    /* AND AES_DECRYPT(done_name, 'encrypt') = 'contact_form_drivetimedoc' */
    	    	    AND done_name = AES_ENCRYPT('contact_form_drivetimedoc', 'encrypt')
            
    	    ";
            $collection = $conn->execute($sql_txt);
            $cnt = 0;
            while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
                
                $tabname = $FormBlockDrivetimedoc;
                $done_name = $contact_form;
            
              
                $sql_update = "UPDATE `patient_course` SET `tabname`= :tabname, `done_name` = :done_name WHERE `id`= :pc_id";
                $params =  array(
                    'pc_id' => $row['id'],
                    'tabname' => $tabname,
                    'done_name' => $done_name,
                );
            
                $stmt = $conn->execute($sql_update, $params);
                $stmt->closeCursor();
                $cnt++;
                 
                $row = null;
                unset($row);
            }
             
            $collection = null;
            unset($collection);
            
            echo ('rename done_name=contact_form_drivetimedoc to done_name=contact_form and tabname=FormBlockDrivetimedoc: '. $cnt . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
            $cnt = 0;
            $start = microtime(true);
            $this->_ob_flush();
            
            
            if ($step != 'NinjaTurtle') {
                die("Step 2.2 Finisded, <a href='?step=3' target='_self' >click here for [STEP 3]</a>");
            }
            
            
	    }
        
        
	    if ($step == '3' || $step == 'NinjaTurtle') {
            /**
             * step 3
             */
            //contact_form_hospiz_imex
            
            $sql_txt = "
    	    	SELECT
        	        id,
        	        aes_decrypt(course_type, 'encrypt') as course_type,
        	        aes_decrypt(course_title, 'encrypt') as course_title
        	    FROM `patient_course`
                WHERE
                    done_id > 0
        	        AND tabname = ''
    	    	    /* AND AES_DECRYPT(done_name, 'encrypt') = 'contact_form_hospiz_imex' */
    	    	    AND done_name = AES_ENCRYPT('contact_form_hospiz_imex', 'encrypt')
            
    	    ";
            $collection = $conn->execute($sql_txt);
            $cnt = 0;
            while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
            
                $tabname = $FormBlockHospizimex;
                $done_name = $contact_form;
                
                $sql_update = "UPDATE `patient_course` SET `tabname`= :tabname, `done_name` = :done_name WHERE `id`= :pc_id";
                $params=  array(
                    'pc_id' => $row['id'],
                    'tabname' => $tabname,
                    'done_name' => $done_name,
                );
            
    //             dd('ERROR',$row, $sql_update, $params);
                
                $stmt = $conn->execute($sql_update, $params);
                $stmt->closeCursor();
                $cnt++;
                 
                $row = null;
                unset($row);
            }
             
            $collection = null;
            unset($collection);
            
            echo ('update FormBlockHospizimex done_name=contact_form_hospiz_imex: '. $cnt . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
            $cnt = 0;
            $start = microtime(true);
            $this->_ob_flush();
            
            if ($step != 'NinjaTurtle') {
                die("Step 3 Finisded, <a href='?step=4' target='_self' >click here for [STEP 4]</a>");
            }
	    }
        
	    
	    if ($step == '4' || $step == 'NinjaTurtle') {
            /**
             * step 4
             */
            
            
            //edit by text in course_title and letter
            $sql_txt = "
    	    	SELECT count(id) as cnt
        	    FROM `patient_course`
                WHERE
                    done_id > 0
        	        AND tabname = ''
    	    	    /* AND AES_DECRYPT(done_name, 'encrypt') = 'contact_form' */
    	    	    AND done_name = AES_ENCRYPT('contact_form', 'encrypt')
                
                    /* AND AES_DECRYPT(course_type, 'encrypt') != 'S' #debugmode */
                
    	    ";
            $countstm = $conn->execute($sql_txt);
            $count = $countstm->fetch(PDO::FETCH_ASSOC);
            
            //TODO: remember to put this back 
    //         $count =  array('cnt' => 2548354);// this is for debug only!  2 548 354
            
            echo ('letter+text total we have to process cnt : '. $count['cnt'] . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
            $cnt = 0;
            $start = microtime(true);
            $this->_ob_flush();
            
            /* LIMIT %u OFFSET %u */ /* why did i used offset? */
            $query = "
    	    	SELECT
        	        id,
        	        aes_decrypt(course_type, 'encrypt') as course_type,
        	        aes_decrypt(course_title, 'encrypt') as course_title
        	    FROM `patient_course` 
                WHERE
                    done_id > 0
        	        AND tabname = ''
    	    	    /*AND AES_DECRYPT(done_name, 'encrypt') = 'contact_form'*/
    	    	    AND done_name = AES_ENCRYPT('contact_form', 'encrypt')
                
                    /*AND AES_DECRYPT(course_type, 'encrypt') != 'S'#debugmode*/
                
                ORDER BY id ASC
                
                LIMIT %u
                ;
            
    	    ";  
                  
            $offset = $rows_counter = 0;
            $cnt_collections = 0;
            
            $limit = 50000;
            
            echo ('todo collections ~ : '. ceil($count['cnt'] / $limit) . "<hr>" . PHP_EOL);
            
            while ($rows_counter < $count['cnt'] )
    	    {
    	        set_time_limit(3 * 60);
    	        
    	        $rows_counter += $limit;
    	        
    	        $start_coll = microtime(true);
    	        
    	        $offset++;
    	        
    // 	        if ($offset < 44) continue;
    	        
    	        
    	        //$collection = $conn->execute(sprintf($query, $limit,  ($offset-1) * $limit));/* why did i used offset? */
    	        $collection = $conn->execute(sprintf($query, $limit));
    	        $_row_counter = 0 ;
    	        $_rows_updated = 0;
    	        
    	        while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {

    	            $_row_counter++;
    	            
    	            $tabname = '';
    	            
    	            switch ($row['course_type']) {
    	        
    	                case 'PD' :
    	                    if ( ! empty($row['course_title']) && ($data = @unserialize($row['course_title'])) !== false && isset($data[0]['input_value'])) {
    	                        //update ContactFormServiceEntry
    	                        $tabname = $ContactFormServiceEntry;
    	                        
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                        
    	                    }
    	                    else {
    	                        
    	                        dd("ERROR", $row, $tabname, $cnt);
    	                    }
    	        
    	                    break;
    	        
    	                case 'K' :
    	                    if (strpos($row['course_title'] , 'Beteiligte Mitarbeiter: ') === 0 ) {
    	                        //update FormBlockAdditionalUsers
    	                        $tabname = $FormBlockAdditionalUsers;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    elseif (strpos($row['course_title'] , 'Datum: ') === 0 ) {
    	                        //update form move TODO
    	                        $tabname = $contact_form_change_date;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    elseif (strpos($row['course_title'] , 'Beginn: ') === 0 ) {
    	                        //update  form move TODO
    // 	                        $tabname = $contact_form_change_begin;
    	                        $tabname = $contact_form_change_date;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    elseif (strpos($row['course_title'] , 'Ende: ') === 0 ) {
    	                        //update  form move TODO
    // 	                        $tabname = $contact_form_change_end;
    	                        $tabname = $contact_form_change_date;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Fahrtzeit: ") === 0 ) {
    	                        //update fahrtzeit_block
    	                        $tabname = $fahrtzeit_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Fahrtstrecke: ") === 0 ) {
    	                        //update fahrtstreke_km_block
    	                        $tabname = $fahrtstreke_km_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Sonstiges / Kommentar:" ) === 0 ) {
    	                        //update comment_block
    	                        $tabname = $comment_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Besuch war: ") === 0 ) {
    	                        //update quality_block ... they are all with XB in the PC
    	                        $tabname = $quality_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Pflege-Anweisung: ") === 0 ) {
    	                        //update care_instructions_block
    	                        $tabname = $care_instructions_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Karnofsky: " ) === 0 ) {
    	                        //update karnofsky_block
    	                        $tabname = $karnofsky_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , " letzter Stuhlgang: ") === 0 ) {
    	                        //update FormBlockBowelMovement
    	                        $tabname = $FormBlockBowelMovement;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'BRA - SAPV Team - Hausarzt Einsatz:') === 0 ) {
    	                        //update FormBlockBraSapv
    	                        $tabname = $FormBlockBraSapv;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Klassifizierung: ") === 0 ) {
    	                        //update FormBlockClassification
    	                        $tabname = $FormBlockClassification;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "EBM: ") === 0 ) {
    	                        //update FormBlockEbmBer
    	                        $tabname = $FormBlockEbmBer;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'Arzt EBM: ') === 0 ) {
    	                        //update FormBlockEbmi
    	                        $tabname = $FormBlockEbmi;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'EBM Hausbesuch: ') === 0 ) {
    	                        //update FormBlockEbmii
    	                        $tabname = $FormBlockEbmii;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'Arzt GOÄ: ') === 0 ) {
    	                        //update FormBlockGoai
    	                        $tabname = $FormBlockGoai;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'GOÄ Hausbesuch: ') === 0 ) {
    	                        //update FormBlockGoaii
    	                        $tabname = $FormBlockGoaii;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'Leistungseingabe: ') === 0 ) {
    	                        //update FormBlockSgbv
    	                        $tabname = $FormBlockSgbv;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , 'Trachealkan&uuml;le: ') === 0 ) {
    	                        //update FormBlockTracheostomy
    	                        $tabname = $FormBlockTracheostomy;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , " Beatmung: ") === 0 ) {
    	                        //update FormBlockVentilation
    	                        $tabname = $FormBlockVentilation;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , " Vitalwerte: Datum: ") === 0 ) {
    	                        //update FormBlockVitalSigns
    	                        $tabname = $FormBlockVitalSigns;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "ECOG: ") === 0 ) {
    	                        //update ecog_block_old
    	                        $tabname = $ecog_block_old;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Maßnahmen: ") === 0 ) {
    	                        //update Maßnahmen 2014 = 2017 ??
    	                        $tabname = $contact_form_measures;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    
    	                    else {
    	                        //this if's I pressume are from the fomular moved
    	                        
    	                        if (preg_match('/^[0-9]{2}:[0-9]{2} - [0-9]{2}:[0-9]{2}  [0-9]{2}.[0-9]{2}.[0-9]{4}$/', $row['course_title'] , $matches)//"08:29 - 08:44  15.03.2013"
    	                            || preg_match('/^[0-9]{2}:[0-9]{2} - [0-9]{2}:[0-9]{2}   [0-9]{2}.[0-9]{2}.[0-9]{4}$/', $row['course_title'] , $matches)//"07:25 - 08:00   26.07.2014"
    	                            || preg_match('/^[0-9]{2}:[0-9]{2} - [0-9]{2}:[0-9]{2}   [0-9]{2}.(0)[0-9]{2}.[0-9]{4}$/', $row['course_title'] , $matches)//"07:25 - 08:00   26.070.2014"
    	                            || preg_match('/^: - :  $/', $row['course_title'] , $matches)//": - :  "
    	                            || preg_match('/^[0-9]{2}:[0-9]{2} - [0-9]{2}:[0-9]{2}  $/', $row['course_title'] , $matches) //"15:35 - 16:15  "
    	                            || preg_match('/^[0-9]{2}:[0-9]{2} - [0-9]{2}:[0-9]{2} [0-9]{2}.[0-9]{2}.[0-9]{4}$/', $row['course_title'] , $matches)//"13:00 - 13:39 30.06.2016"
                                ) {
    	                            
    	                            $tabname = $contact_form_first_date;
    	                        } 
    	                        
    	                        else {
    	                            
    	                           dd("ERROR?", $row, $matches, $cnt, "\"". $row['course_title'] . "\"");
    	                        }
    	                    }
    	        
    	                    break;
    	        
    	                case 'A' :
    	                    
    	                    if (strpos($row['course_title'] , "Anamnese: ") === 0 ) {
    	                        //update case_history_block
    	                        $tabname = $case_history_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    
    	                    else {
    	                        dd("ERROR?", $row, $matches, $cnt);
    	                    }
    	                    
    	                    
    	                    break;
    	        
    	        
    	                case 'B' :
    	                    
    	                    if (strpos($row['course_title'] , " Vitalwerte: Datum: ") === 0 ) {
    	                        //update FormBlockVitalSigns
    	                        $tabname = $FormBlockVitalSigns;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    elseif (strpos($row['course_title'] , "Kopf: ") === 0 
    	                        || strpos($row['course_title'] , "Thorax: ") === 0
    	                        || strpos($row['course_title'] , "Abdomen: ") === 0
    	                        || strpos($row['course_title'] , "Extremitaten: ") === 0
    	                        || strpos($row['course_title'] , "Haut/Wunden: ") === 0
    	                        || strpos($row['course_title'] , "neurologisch / psychiatrisch: ") === 0
    	                        ) {
    	                        //update FormBlockBefund
    	                        $tabname = $FormBlockBefund;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    
    	                    else {
    	                        //update befund_txt_block
    	                        $tabname = $befund_txt_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    break;
    	        
    	        
    	                case 'E' :
    	                   
    	                        //update  	Absprache mit/ Beratung von FachkollegInnen : behandelnde Ärzte
    	                        //comments_l is inside a foreach... and there is also a "Ein bestehender LE Eintrag vom " without any done_name or tabname or done_id
    	                        //     	                    ClientFb3categories::defaultClientFb3categories();
    	                        $tabname = $comments_l_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
                                /*
                                 * this can be split into 2 options :
                                 * Ein bestehender LE Eintrag vom =>  upcomments
                                 * 
                                 */
    	                         
    	                        
    	                   
    	                    break;
    	        
    	        
    	                case 'MG' :
    
    	                    if (strpos($row['course_title'] , "Medikamente gestellt : ") === 0 ) {
    	                        //update FormBlockHospizmedi
    	                        $tabname = $FormBlockHospizmedi;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    elseif (strpos($row['course_title'] , "Medikamente  verabreicht : ") === 0 ) {
    	                        //update FormBlockHospizmedi
    	                        //this is a bug from 2015... MV was not added ant the entry saved with MG.. should we cor
    	                        $tabname = $FormBlockHospizmedi;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    else {
    	                        dd("ERROR?", $row, $cnt);
    	                    }
    	                    break;
    	        
    	        
    	                case 'MV' :
    	                    if (strpos($row['course_title'] , "Medikamente  verabreicht : ") === 0 ) {
    	                        //update FormBlockHospizmedi
    	                        $tabname = $FormBlockHospizmedi;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    else {
    	                        dd("ERROR?", $row, $cnt);
    	                    }
    	                    break;
    	        
    	        
    	                case 'Q' :
    	                    if (strpos($row['course_title'] , "Kommentar Medikation / Pumpe / Apotheke:") === 0 ) {
    	                        //update comment_apotheke
    	                        $tabname = $comment_apotheke_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    else {
    	                        dd("ERROR?", $row, $cnt);
    	                    }
    	                    break;
    	        
    	        
    	                case 'S' :
    
    	                    if ( ! empty($row['course_title']) 
    	                        && ($data = @unserialize($row['course_title'])) !== false 
    	                        && isset($data[0]['input_value'])
    	                        && isset($data[0]['second_value'])
    	                        && isset($data[0]['symptid'])
    	                        && isset($data[0]['setid']) ) 
    	                    {
    
    	                        
//     	                        PatientSymptomatology
    	                        $tabname = $PatientSymptomatology;
//     	                        dd($row['course_type'], $row, $tabname, $cnt, $tabnames);
    	                    
    	                    }
    	                    else {
    	                        
    	                        //cf_client_symptoms
    	                        if ( ! empty($row['course_title']) 
    	                            && ($data = @unserialize($row['course_title'])) !== false 
    	                            && isset($data[0]['description']) 
    	                            && isset($data[0]['severity']) 
    	                            && isset($data[0]['sorrowfully']) 
    	                            && isset($data[0]['care_specifications']) ) 
    	                        {
    	                            //cf_client_symptoms allreay have tabname
    	                            $tabname = $cf_client_symptoms;
    	                            dd($row['course_type'], $row, $tabname, $cnt, $data);
    	                        	          
    	                        } 
    	                        elseif (preg_match('/^(.*){2,}\s|\s(.*){2,}$/', $row['course_title'] , $matches)) {
    	                            //clientsymptoms were inserter like this: Schme | kein | ette | tetertre
    	                            //rev.666 changed to serialize
    	                            $tabname = $PatientSymptomatology;
//     	                            dd($row['course_type'], $row, $tabname, $cnt, $data);
    	                        }
    	                        else {
            	                    dd("ERROR", $row);
    	                            
    	                        }
    	                        
    	                        
    	                            
    	                    }
    	                    //dd("it was S");
    	                    break;
    	        
    	        
    	                case 'T' :
    	                    
    	                    //update therapy_txt
    	                    $tabname = $therapy_txt_block;
    // 	                    dd($row['course_type'], $row, $tabname, $cnt);
    	                    
    	                    break;
    	        
    	        
    	                case 'XB' :
    	                    
    	                    if (strpos($row['course_title'] , "Sonstiges / Kommentar: " ) === 0 ) {
    	                        //update comment_block
    	                        $tabname = $comment_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    elseif (strpos($row['course_title'] , "Besuch war: ") === 0 ) {
    	                        //update quality_block
    	                        $tabname = $quality_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    }
    	                    else {
    	                        dd("ERROR?", $row, $cnt);
    	                    }
    	                    
    	                    break;
    	        
    	        
    	                case 'XI' :
                        case 'XK' :
                            
    	                    //update internal_comment
    	                    $tabname = $internal_comment_block;
    // 	                    dd($row['course_type'], $row, $tabname, $cnt);
    	                    
    	                    break;
    	        
    	        
    	                case 'XP' :
    	                    if (strpos($row['course_title'] , "Pflege-Anweisung: ") === 0 ) {
    	                        //update care_instructions_block
    	                        $tabname = $care_instructions_block;
    // 	                        dd($row['course_type'], $row, $tabname, $cnt);
    	                    } else {
    	                        dd("ERROR", $cnt);
    	                    }
    	                    
    	                    break;
    	        
    	                    
    	                default:
    	                    dd('DEFAULT ERROR:', $row, $tabname, $cnt);
    	                    break;
    	        
    	            }
    	            
    	            if ( ! empty($tabname)) {
    	                
    
    	                $sql_update = "UPDATE `patient_course` SET `tabname` = :tabname WHERE `id`= :pc_id";
    	                $params = array(
    	                    'tabname'      => $tabname,
    	                    'pc_id'        => $row['id']
    	                );
    	                
    	                
    	                $manual_verify = array(
    	                    'fahrtzeit_block',
    	                    'comment_block',
    	                    'comment_apotheke_block',
    	                    'case_history_block',
    	                    'internal_comment_block',
    	                    'care_instructions_block',
    	                    'FormBlockAdditionalUsers',
    	                    'befund_txt_block',
    	                    'therapy_txt_block',
    	                    'FormBlockBefund',
    	                    'FormBlockEbmi',
    	                    'FormBlockEbmii',
    	                    'FormBlockEbmBer',
    	                    'FormBlockClassification',
    	                    'fahrtstreke_km_block',
    	                    'ecog_block_old',
    	                    'FormBlockSgbv',
    	                    'contact_form_measures',
    	                    'ContactFormServiceEntry',
    	                    'FormBlockVitalSigns',
    	                    'FormBlockGoaii',
    	                    'FormBlockHospizmedi',
    	                    'FormBlockGoai',
    	                    'quality_block',
    	                    'contact_form_change_begin',
    	                    'contact_form_change_end',
    	                    'contact_form_change_date',
    	                    'FormBlockBraSapv',
    	                    'karnofsky_block',
    	                    'cf_client_symptoms',
    	                    'comments_l_block',
    	                    'contact_form_first_date',
    	                    'FormBlockTracheostomy',
    	                    'FormBlockBowelMovement',
    	                    'FormBlockVentilation',
    	                    'PatientSymptomatology',
    	                    
    	                    
    	                );
    	                
    	                if (in_array(array_search($tabname, $tabnames), $manual_verify)) {
    	                    //dd($sql_update, $params, $tabname, $tabnames);
        	                $stmt = $conn->execute($sql_update, $params);
        	                $stmt->closeCursor();
        	                
        	                $_rows_updated++;
    	                    
    	                } else {
    	                    dd(array_search($tabname, $tabnames),  $tabname, $row, $sql_update, $params );
    	                     
    	                    die( "line:" . __LINE__);
    	                }
    // 	                dd(array_search($tabname, $tabnames), $tabname, $row , $sql_update, $params );
    	                
    	                 
    	                
    	            } else {
    	                dd("this are the formular moved to date.... ??", $row); // this are the formular moved to date....
    	            }
    	            
    	            $cnt++;
    	            $row = null;
    	            unset($row);
    	            
    	        }
    	        
    	        $collection->closeCursor();
    	        $collection = null;
    	        unset($collection);
    	        
    	        
    	        $cnt_collections++;
    	        
    	        echo ('letter+text collection : '. $cnt_collections . " rows_fetched:{$_row_counter}"  . " rows_updated:{$_rows_updated}" . ' spent time:' . (microtime(true) - $start_coll) . "<hr>" . PHP_EOL);
    	        $this->_ob_flush();
    	    }
    	    
    	    echo ('letter+text fetch all : '. $count['cnt'] . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
    	    $cnt = 0;
    	    $start = microtime(true);
    	    $this->_ob_flush();
    	    
    	    
    	    $rows_counter += 10000;
    	     
    	    
            
            $cnt = 0;
           
            { 
                
    //             if ($row['pc_done_id'] > 0) {
            
    //                 $sql_update = "UPDATE `patient_course` SET `done_name` = :done_name WHERE `id`= :pc_id";
                     
    //                 $params = array(
    //                     'done_name'    => $contact_form_encrypted,
    //                     'pc_id'        => $row['pc_id']
    //                 );
    //             } else {
            
    //                 $sql_update = "UPDATE `patient_course` SET `done_name` = :done_name, `done_id` = :done_id WHERE `id`= :pc_id";
            
    //                 $params = array(
    //                     'done_name'    => $contact_form_encrypted,
    //                     'done_id'      => $row['pf_recordid'],
    //                     'pc_id'        => $row['pc_id']
    //                 );
    //             }
                 
    //             $conn->execute($sql_update, $params);
                $cnt++;
                unset($row);
                $row = null;
            }
             
            
    
            echo ('letter+text : '. $cnt . ' spent time:' . (microtime(true) - $start). "<hr>" . PHP_EOL);
            $cnt = 0;
            $start = microtime(true);
            
            if ($step != 'NinjaTurtle') {
                die("Step 4 Finisded, <a href='?step=NinjaTurtle' target='_self' >click here ONCE to rerun all steps, to be sure</a>");
            }
	    }
        
        die("The NinjaTurtle has crossed the Finish line");
        
	    $params = null;
	    // Returns instance of Doctrine_Collection_OnDemand
	    $collection = $qm1->execute($params, Doctrine_Core::HYDRATE_ON_DEMAND);
	    foreach ($collection as $obj)
	    {
	        dd($obj->toArray()); // do something with your object
	    }
	    

	}
	
	
	public function ispc2138Action()
	{
	    exit;
        /**
         * this fn is to be run ONLY once on ISPC-2138
         * it will assign module 160 to all cleints with name like ('SH_%')
         */
	    $start = microtime(true);
	     
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $cleints_arr = Doctrine_Query::create()
	    ->select('id, AES_DECRYPT(client_name , \''.Zend_Registry::get('salt').'\')')
	    ->from('Client')
	    ->where('AES_DECRYPT(client_name , \''.Zend_Registry::get('salt').'\') LIKE (\'SH_%\')')
	    ->andWhere("isdelete='0'")
	    ->fetchArray();
	    
	    $moduleid = 160;
	    $canaccess = 1;
	    
	    $records = array();
	    
	    foreach ($cleints_arr as $cl) {
            $records[] = array(
                "clientid"=>$cl['id'],
                "moduleid"=>$moduleid,
                "canaccess"=> $canaccess
            );
	    }
	    
	    $collection = array();
	    
	    if ( ! empty($records)) {
	        $collection = new Doctrine_Collection('ClientModules');
	        $collection->fromArray($records);
	        $collection->save();
	    }
	    
	    echo 'this fn is to be run ONLY once<br>it will assign module 160 to all cleints with name like (\'SH_%\')<hr>';
	    echo 'saved this: <hr><pre>';
	    print_r($collection->toArray());
	    exit;
// 	    dd('this fn is to be run ONLY once<br>it will assign module 160 to all cleints with name like (\'SH_%\')', 'saved this:', $collection->toArray());
	    
	}
	
	
	
	
	/**
	 * this fn todo1409Action is to be run ONLY once for TODO-1409
	 * p.s. you can run multiple times, not a problem
	 *
	 the new contact phone number patch works great. all numbers are shown nicely.
	
	 But all patients which had the contact phone number set for LOCATION where the checkbox was CHECKED before the patch, the contact phone number is not shown.
	 this means all users need to open the patieent records, and remove the checkbox and recheck the checkbox.
	
	 Can you do by script?
	 */
	/**
	 * TODO : next time use the listeners available :(
	 * 
	 * TODO-1409 morphed into ISPC-2166
	 */
	public function todo1409Action()
	{
		exit;
	    $start = microtime(true);
	    set_time_limit(360);
	    
	    
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	     
	    //rc.1 ... fetch all the records in one single pass

	    /* 
	    $alldready_done = Doctrine_Query::create()
	    ->select('pc.ipid')
	    ->from('PatientContactphone pc')
	    ->where("(pc.isdelete = 0 OR pc.isdelete = 1)")
	    ->andWhere("pc.parent_table = 'PatientMaster'")
	    ->andWhere("pc.from_locations = 'no'")
// 	    ->fetchArray()
	    ; */
	    
// 	    $alldready_done_ipids = array_unique(array_column($alldready_done, 'ipid')); // this will be ignored	  
// 	    unset($alldready_done);
	    
	    $q = Doctrine_Query::create()
// 	    $q = Doctrine_Core::getTable('PatientMaster')->createQuery('p')
	    ->select("p.id, p.ipid,
	        aes_decrypt(p.kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,
	        aes_decrypt(p.phone, '" . Zend_Registry::get('salt') . "') as phone,
	        aes_decrypt(p.mobile, '" . Zend_Registry::get('salt') . "') as mobile,
	        aes_decrypt(p.first_name, '" . Zend_Registry::get('salt') . "') as first_name,
	        aes_decrypt(p.last_name, '" . Zend_Registry::get('salt') . "') as last_name
	        
	        "
	        )
	        /*
	         * 
	        (p.kontactnumber) as enc_kontactnumber,
	        (p.phone) as enc_phone,
	        (p.mobile) as enc_mobile,
	        (p.first_name) as enc_first_name,
	        (p.last_name) as enc_last_name
	         */
	    ->from('PatientMaster p')
	    ->leftJoin('p.PatientContactphone pc ON (p.ipid = pc.ipid AND pc.parent_table = \'PatientMaster\' AND (pc.isdelete = 0 OR pc.isdelete = 1))')
	    
	    ->where('pc.ipid IS NULL')
	    
	    ->andWhere('p.is_contact = 0')
	    ->andWhere("p.isdelete = 0")
	    ->andWhere('p.kontactnumbertype = 0')
	    ->andWhere('p.kontactnumber != \'\'')
	    
	    ->limit("10000")
// 	    ->execute(null, Doctrine_Core::HYDRATE_ON_DEMAND)
	    
	    ->fetchArray()
	    ;
	   
// 	    //why you no cum instance of Doctrine_Collection_OnDemand??
//      //why we have to do pdo?
// 	    $result = $q->execute(array(), Doctrine_Core::HYDRATE_ON_DEMAND);
// 	    foreach ($result as $obj) {
// 	    }

// 	    $iConn = Doctrine_Manager::getInstance()->getConnection('IDAT');
// 	    $sql_txt = $q->getSqlQuery();
// 	    $collection = $iConn->execute($sql_txt);
	    
	    $patient_master_update_id = array();
	    $patient_contactphone_update_arr = array();
	    
       $cnt = 0;
       foreach ($q as $patient) {
      /*  while ($patient = $collection->fetch(PDO::FETCH_ASSOC)) {
           
           $patient['ipid']         = $patient['p__ipid'];
           $patient['id']           = $patient['p__id'];
           $patient['phone']        = $patient['p__1'];
           $patient['mobile']       = $patient['p__2'];
           $patient['first_name']   = $patient['p__3'];
           $patient['last_name']    = $patient['p__4'];
       */     
           
           $ipid = $patient['ipid'];
           
           $contact_from_locations =  'no';
           $ComponentName		= 'PatientMaster';
           $Table_id			= $patient['id'];
           
           $contact_phone		= $patient['phone'];
           $contact_mobile		= $patient['mobile'];
           $contact_last_name	= $patient['last_name'];
           $contact_first_name	= $patient['first_name'];
           $contact_other_name	= null;
            
            
           array_push($patient_master_update_id, (int)$patient['id']);
           $pcp_obj = array(
               "ipid"			=> $ipid,
               "parent_table"	=> $ComponentName,
               "table_id"		=> $Table_id,
               "from_locations"=> $contact_from_locations,
               "phone"			=> $contact_phone,
               "mobile"		=> $contact_mobile,
               "first_name"	=> $contact_first_name,
               "last_name"		=> $contact_last_name,
               "other_name"	=> $contact_other_name,
               "isdelete"		=> 0
           );
           
           array_push($patient_contactphone_update_arr, $pcp_obj);
           
           $row = null;
           unset($row);
       }
	    
/*        $collection->closeCursor();
 */       
       $collection = null;
       unset($collection);
	   unset($q);

	   if ( ! empty($patient_contactphone_update_arr)) {
    	    $collection = new Doctrine_Collection('PatientContactphone');
    	    $collection->fromArray($patient_contactphone_update_arr);
    	    $collection->save();
    	    $inserted_keys = $collection->getPrimaryKeys();
	   }
	    
	   if ( ! empty($patient_master_update_id)) {
	    //mark location as contactphone
	    $patient_master_update = Doctrine_Query::create()
	    ->update('PatientMaster')
	    ->set('is_contact', 1)
	    ->whereIn('id' , $patient_master_update_id)
	    ;
	    
	    $patient_master_update->getConnection()->getManager()->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, false );
	    
	    $patient_master_update
	    ->execute(null, Doctrine_Core::HYDRATE_ON_DEMAND)
	    ;
	   }
	    
	  
	     
	    // 	    $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
	    // 	    $sql_txt = "INSERT INTO xxx VALUES $inserted_keys";
	    // 	    $conn->execute($sql_txt);
	     
	    echo ('PatientMaster: '. count($patient_master_update_id) . "<hr>". "PatientContactphone: ". count($inserted_keys) . "<hr>" .  "Total microtime: " . (microtime(true) - $start) . "<hr><br>");
	     
	    unset($patient_master_update);
	    unset($collection);
	    unset($inserted_keys);
// 	    dd(count($patients), count($patient_contactphone_update_arr), count($patient_master_update_id));
	    
// 	    dd("ddddddddd", count($patient_master_update_id));
	     
	    if ( ! empty($patient_master_update_id)) {
	        die('<hr> F5 ... there are more... PatientMaster comes in batches of 10000');
	    }
	    
	    
	    
	    
	    
	    /* 
	    //get all patients that have allready CHECKED the location... this will NOT be modified
	    $alldready_done = Doctrine_Query::create()
	    ->select('id, ipid')
	    ->from('PatientContactphone')
	    ->where('from_locations = \'yes\'')
	    ->andWhere("(isdelete = 0 OR isdelete = 1)")
	    ->fetchArray();
	    
	    $alldready_done_ipids = array_unique(array_column($alldready_done, 'ipid')); // this will be ignored
	    unset($alldready_done);
	    
	    $patients = Doctrine_Query::create()
	    ->select('id, ipid ')
        ->from('PatientMaster')
        ->whereNotIn('ipid', $alldready_done_ipids)
        ->andWhere('kontactnumbertype = 1')
        ->andWhere("isdelete = 0")
        

//         ->andWhere('ipid = ?' ,'1f9b78da68c2d4ee927e37a8fb9613d63664406b')
        
//         ->limit(10)
//         ->orderBy('id DESC')
        ->fetchArray()
	    ;

	    
	     */
	    
	    $start = microtime(true);
	    echo "<br><hr>START from_locations contact number<br>";
	    
	    $patients = Doctrine_Query::create()
	    ->select("p.id, p.ipid,
	        aes_decrypt(p.kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,
	        aes_decrypt(p.phone, '" . Zend_Registry::get('salt') . "') as phone,
	        aes_decrypt(p.mobile, '" . Zend_Registry::get('salt') . "') as mobile,
	        aes_decrypt(p.first_name, '" . Zend_Registry::get('salt') . "') as first_name,
	        aes_decrypt(p.last_name, '" . Zend_Registry::get('salt') . "') as last_name")
	    ->from('PatientMaster p')
	    ->leftJoin('p.PatientContactphone pc ON (p.ipid = pc.ipid AND pc.from_locations = \'yes\' AND (pc.isdelete = 0 OR pc.isdelete = 1))')
	     
	    ->where('pc.ipid IS NULL')
	     
	    ->andWhere("p.isdelete = 0")
	    ->andWhere('p.kontactnumbertype = 1')
	    ->andWhere('p.kontactnumber != \'\'')
	     
	    ->limit("5000")
	    // 	    ->execute(null, Doctrine_Core::HYDRATE_ON_DEMAND)
	     
	    ->fetchArray()
	    ;
	    
	    
	    $patient_location_update_id = array();
	    $patient_contactphone_update_arr = array();
	    
	    
	    foreach($patients as $patient) {
	        
    	    $last_location = PatientLocation::getIpidLastLocationDetails($patient['ipid']);
    	    
    	    if ( ! empty($last_location)) {
        	    $ipid = $patient['ipid']; 
        	    
        	    $contact_from_locations =  'yes';
    	    	$ComponentName		= $last_location['ComponentName'];
    			$Table_id			= $last_location['id'];
    					
    			$contact_phone		= $last_location['phone'];
    			$contact_mobile		= $last_location['mobile'];
    			$contact_last_name	= $last_location['last_name'];
    			$contact_first_name	= $last_location['first_name'];
    			$contact_other_name	= $last_location['other_name'];
    	    
    			//mark location as contactphone
//     			$patient_location_update = Doctrine_Query::create()
//     			->update('PatientLocation')
//     			->set('is_contact', 1)
//     			->where('id = ?' , (int)$last_location['PatientLocation_id'])
//     			->andwhere('ipid = ?' , $ipid)
//     			->execute()
//     			;
    			
    			array_push($patient_location_update_id, (int)$last_location['PatientLocation_id']);
    			
    			
    			
    			//add to contact phone table
    			if ($last_location['valid_till'] == '0000-00-00 00:00:00'
    			        || Pms_CommonData::isToday($last_location['valid_till'])
    			        || Pms_CommonData::isFuture($last_location['valid_till']))
    			{
    			    //current_location is valid
    			    $isdelete = 0;
    			} else {
    			    $isdelete = 1; // add also invalid so we can clear the older ones
    			}
    			
    			
    			$pcp_obj = array(
        			"ipid"			=> $ipid,
        			"parent_table"	=> $ComponentName,
        			"table_id"		=> $Table_id,
        			"from_locations"=> $contact_from_locations,
        			"phone"			=> $contact_phone,
        			"mobile"		=> $contact_mobile,
        			"first_name"	=> $contact_first_name,
        			"last_name"		=> $contact_last_name,
        			"other_name"	=> $contact_other_name,
        			"isdelete"		=> $isdelete
    			);
    			
    			array_push($patient_contactphone_update_arr, $pcp_obj);
    			
//     	    dd($patients, $last_location, $patient_location_update_id, $patient_contactphone_update_arr);
    			
// 			    $pcp_obj = new PatientContactphone();
//     	            $pcp_obj->ipid			= $ipid;
//     	            $pcp_obj->parent_table	= $ComponentName;
//     	            $pcp_obj->table_id		= $Table_id;
//     	            $pcp_obj->from_locations= $contact_from_locations;
//     	            $pcp_obj->phone			= $contact_phone;
//     	            $pcp_obj->mobile		= $contact_mobile;
//     	            $pcp_obj->first_name	= $contact_first_name;
//     	            $pcp_obj->last_name		= $contact_last_name;
//     	            $pcp_obj->other_name	= $contact_other_name;
//     	            $pcp_obj->isdelete		= $isdelete;
//     	            $pcp_obj->save();
//     	        $inserted_id = $pcp_obj->id;
    	        
//     	        dd($last_location, $patient_contactphone_update_arr, $patient_location_update_id);
   			
    	        
    	    }
	        
    	       
	    }
	    
	    
	    if ( ! empty($patient_location_update_id)) {
    	    //mark location as contactphone
    	    $patient_location_update = Doctrine_Query::create()
    	    ->update('PatientLocation')
    	    ->set('is_contact', 1)
    	    ->whereIn('id' , $patient_location_update_id)
    	    ->execute()
    	    ;
	    }
	    
	    if ( ! empty($patient_contactphone_update_arr)) {
    	    $collection = new Doctrine_Collection('PatientContactphone');
    	    $collection->fromArray($patient_contactphone_update_arr);
    	    $collection->save();				
    	    $inserted_keys = $collection->getPrimaryKeys();
	    }
	    
	    
	    echo ('PATIENTs: '. count($patients) . '<hr> PatientLocation: ' . count($patient_location_update_id). "<hr>". "PatientContactphone: ". count($inserted_keys) . "<hr>" .  "Total microtime: " . (microtime(true) - $start) . "<hr>");
	    unset($patients);
	    
	    if ( ! empty($patient_contactphone_update_arr)) {
	        die('<hr> F5 ... there are more... PatientLocation comes in batches of 5000');
	    }
	    
	    
	    
	    
	    
	    
	    
	    
	    //contact person
	    $start = microtime(true);
	    echo "<br><hr>START contact person contact number<br>";
	    
	    
	    $patients = Doctrine_Query::create()
	    ->select("p.id, p.ipid,
	        aes_decrypt(p.kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,
	        aes_decrypt(p.phone, '" . Zend_Registry::get('salt') . "') as phone,
	        aes_decrypt(p.mobile, '" . Zend_Registry::get('salt') . "') as mobile,
	        aes_decrypt(p.first_name, '" . Zend_Registry::get('salt') . "') as first_name,
	        aes_decrypt(p.last_name, '" . Zend_Registry::get('salt') . "') as last_name")
	    	        ->from('PatientMaster p')
	    	        ->leftJoin('p.PatientContactphone pc ON (p.ipid = pc.ipid AND parent_table=\'ContactPersonMaster\' AND pc.from_locations = \'no\' AND (pc.isdelete = 0 OR pc.isdelete = 1))')
	    
	    	        ->where('pc.ipid IS NULL')
	    
	    	        ->andWhere("p.isdelete = 0")
	    	        ->andWhere('p.kontactnumbertype = 2')
	    	        ->andWhere('p.kontactnumber != \'\'')
	    
	    	        ->limit("5000")
	    	        // 	    ->execute(null, Doctrine_Core::HYDRATE_ON_DEMAND)
	    
	    ->fetchArray()
	    ;
	     
	     
	    $cpr = new ContactPersonMaster();
	    $cprarr = $cpr->getAllPatientContact( array_column($patients, 'ipid') );
	    
	    $contact_person_update_id = array();
	    $patient_contactphone_update_arr = array();
	    $relatives = array();
	    
	    foreach($patients as $patient) {
	        
	        $ipid = $patient['ipid'];
	        
	        if ( ! empty($cprarr[$ipid])) {
	         
    	        $contact_from_locations =  'no';
    	        $ComponentName		= 'ContactPersonMaster';
    	        
        	    //wacky identify the cp... 
        	    foreach ($cprarr[$ipid] as $contact_person) {

        	        if ($patient['kontactnumber'] == $contact_person['cnt_phone'] || $patient['kontactnumber'] == $contact_person['cnt_mobile']) {
        	            
        	            
        	            $Table_id			= $contact_person['id'];
        	            
        	            $contact_phone		= $contact_person['cnt_phone'];
        	            $contact_mobile		= $contact_person['cnt_mobile'];
        	            $contact_last_name	= $contact_person['cnt_last_name'];
        	            $contact_first_name	= $contact_person['cnt_first_name'];
        	            $contact_other_name	= $contact_person['cnt_familydegree_id'];
        	            
        	            if ( (int)$contact_other_name > 0 && ! isset($relatives[$contact_other_name])) {
        	                $familydegree = new FamilyDegree();
        	                $degreearray = $familydegree->get_relation($contact_other_name);
        	                $relatives[$contact_other_name] = $degreearray[0]['family_degree'];
        	            }
        	            
        	            if ( (int)$contact_other_name > 0) {
        	                $contact_other_name = $relatives[$contact_other_name];
        	            } else {
        	                $contact_other_name = null;
        	            }
        	            
        	            
        	            $pcp_obj = array(
        	                "ipid"			=> $ipid,
        	                "parent_table"	=> $ComponentName,
        	                "table_id"		=> $Table_id,
        	                "from_locations"=> $contact_from_locations,
        	                "phone"			=> $contact_phone,
        	                "mobile"		=> $contact_mobile,
        	                "first_name"	=> $contact_first_name,
        	                "last_name"		=> $contact_last_name,
        	                "other_name"	=> $contact_other_name,
        	                "isdelete"		=> $isdelete
        	            );
        	             
        	            array_push($patient_contactphone_update_arr, $pcp_obj);
        	            array_push($contact_person_update_id, $Table_id);
        	            
        	            
//         	            dd('FOUND!', $contact_person_update_id, $patient_contactphone_update_arr);
        	            
        	            
        	            break 1;
        	        }
        	    }

//         	    dd($patient, $cprarr);
        	     
	        }
	    }
	     
	    if ( ! empty($contact_person_update_id)) {
	        //mark location as contactphone
	        $patient_location_update = Doctrine_Query::create()
	        ->update('ContactPersonMaster')
	        ->set('is_contact', 1)
	        ->whereIn('id' , $contact_person_update_id)
	        ->execute()
	        ;
	    }
	    
	    if ( ! empty($patient_contactphone_update_arr)) {
	        $collection = new Doctrine_Collection('PatientContactphone');
	        $collection->fromArray($patient_contactphone_update_arr);
	        $collection->save();
	        $inserted_keys = $collection->getPrimaryKeys();
	    }
	     
	    echo ('PATIENTs: '. count($patients) . '<hr> ContactPersonMaster: ' . count($contact_person_update_id). "<hr>". "PatientContactphone: ". count($inserted_keys) . "<hr>" .  "Total microtime: " . (microtime(true) - $start) . "<hr>");
	    unset($patients);
	     
	    if ( ! empty($contact_person_update_id)) {
	        die('<hr> F5 ... there are more... ContactPersonMaster comes in batches of 5000');
	    }
	     
	    
	     
	    
	    
	    
	    
	    
	    
	    
	    //pflegedienst contact number
	    $start = microtime(true);
	    echo "<br><hr>START pflegedienst contact number<br>";

	    $pflegedienst_update_id = array();
	    $patient_contactphone_update_arr = array();
	    $relatives = array();
	    
	    $patients = Doctrine_Query::create()
	    ->select("p.id, p.ipid,
	        aes_decrypt(p.kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,
	        aes_decrypt(p.phone, '" . Zend_Registry::get('salt') . "') as phone,
	        aes_decrypt(p.mobile, '" . Zend_Registry::get('salt') . "') as mobile,
	        aes_decrypt(p.first_name, '" . Zend_Registry::get('salt') . "') as first_name,
	        aes_decrypt(p.last_name, '" . Zend_Registry::get('salt') . "') as last_name")
	    	        ->from('PatientMaster p')
	    	        ->leftJoin('p.PatientContactphone pc ON (p.ipid = pc.ipid AND parent_table=\'Pflegedienstes\' AND pc.from_locations = \'no\' AND (pc.isdelete = 0 OR pc.isdelete = 1))')
	    	         
	    	        ->where('pc.ipid IS NULL')
	    	         
	    	        ->andWhere("p.isdelete = 0")
	    	        ->andWhere('p.kontactnumbertype = 3')
	    	        ->andWhere('p.kontactnumber != \'\'')
	    	         
	    	        ->limit("5000")
	    	        // 	    ->execute(null, Doctrine_Core::HYDRATE_ON_DEMAND)
	     
	    ->fetchArray()
	    ;
	    
	    

	    $cnt_pflege = 0;
	    foreach ($patients as $patient) {
	        
	        $ipid = $patient['ipid'];
	        
	        $pfle = new PatientPflegedienste();
	        $pflegedienst = $pfle->getTable()->createQuery('pp')
	        
	        ->select('pp.*, p.*')
	        ->leftJoin('pp.Pflegedienstes p ON (pp.pflid = p.id  AND phone_practice = ?)', $patient['kontactnumber'])
	        ->where('ipid = ?', $ipid)
	        ->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
	        
	        if ($pflegedienst && isset($pflegedienst->Pflegedienstes) && $pflegedienst->Pflegedienstes->is_contact == 0) { //this is using the trigger
	        
    	        $cnt_pflege++;
    	       
    	        $pflegedienst->Pflegedienstes->ipid = $ipid;
    	        $pflegedienst->Pflegedienstes->is_contact = 1;
    	        $pflegedienst->save();
	        }
	        
	    }
	    

	    echo ('PATIENTs: '. count($patients) . '<hr> Pflegedienstes: ' . $cnt_pflege. "<hr>".  "Total microtime: " . (microtime(true) - $start) . "<hr>");
	    unset($patients);
	    
	    if ( ! empty($cnt_pflege)) {
	        die('<hr> F5 ... there are more... Pflegedienstes comes in batches of 5000');
	    }
	    
	    
	    die("The NinjaTurtle has crossed the Finish line");
	}
	
	
	
	






	public function completeinvoicesAction(){
	
		exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->usertype != 'SA')
		{
			die(" normal Ben ? ");
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	
	
		$invoices_array = Doctrine_Query::create()
		->select("*")
		->from('BwInvoicesNew')
		->andWhere("isdelete = 0")
		->andWhere('status = 5')
		->andWhere('id = 6699')
		->fetchArray()
		;
	
		$invs_for_update = array();
		$invs_str="";
		foreach($invoices_array as $k=>$inv_data){
	
			$invoices = Doctrine_Query::create()
			->select("invoice, SUM(amount) as paid_amount")
			->from('ShInvoicePayments')
			->Where("invoice='" . $inv_data['id'] . "'")
			->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();
	
			if(!empty($itemInvArray) && $itemInvArray['0']['paid_amount'] == $inv_data['invoice_total'] ){
				$invs_for_update[] = $inv_data;
				$invs_str .= $inv_data['client']." ---- ".$inv_data['prefix']." ".$inv_data['invoice_number']." =====>". $inv_data['id'].', change_date: '.$inv_data['change_date'].' <br/>';
			}
	
		}
		print_r('UPDATE ShInvoices');
		print_r("\n <br/>");
		print_r($invs_str);
		print_r("\n <br/>");
		print_r($invs_for_update);
	
		if(!empty($invs_for_update)){
	
			foreach($invs_for_update as $ki=>$invoice_data){
	
				$upd_inv = Doctrine::getTable('ShInvoices')->find($invoice_data['id']);
				if($upd_inv){
					echo "vasile";
// 					$sph = Doctrine_Query::create()
// 					->update('ShInvoices')
// 					->set('status', '3')
// 					->set('change_user', "?", $invoice_data['change_user'])
// 					->set('change_date',"?", $invoice_data['change_date'])
// 					->where("id= ?", $invoice_data['id']);
// 					$sph->execute();
				}
			}
		}
		exit;
	}
	
	
	/**
	 * for each client, login with one CA user, get the last ipid and visit all a href from /patientcourse/patientcourse?id=
	 */
	public function visitallmenuAction()
	{
	    if($this->logininfo->usertype != 'SA') {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $step = $this->getRequest()->getQuery('step', 'random_1');
	    
	    if ($step == 'random_1') {
	        echo "<b>pick only 1 random client and visit all a href from /patientcourse/patientcourse?id=</b><br/>";
	    } else {
	        echo "for each client, login with one CA user, get the last ipid and visit all a href from /patientcourse/patientcourse?id=<br>";
	    }
	    
	    $website_url = APP_BASE; //'http://10.0.0.36/ispc2017_08/public/';
	    
	    $all_jsons = [];
	    
	    $users = Doctrine_Query::create()
	    ->select('*')
	    ->from('User')
	    ->where("usertype = ?", 'CA')
	    ->andWhere('isdelete = 0')
	    ->andWhere('isactive = 0')
	    ->groupBy('clientid')
	    ->fetchArray();
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    
	    $cntTUsers = count($users);
	    $cntUsers = 0;
	    
	    $random_1 = rand(1, $cntTUsers);
	    
	    foreach ($users as $user) 
	    {
	        
	        $cntUsers++;
	        
	        if ($step == 'random_1' && $random_1 != $cntUsers) {
	            continue;
	        }
	        
	        echo "<b>set_time_limit 600</b><br/>" . PHP_EOL;
	         
	        set_time_limit(10 * 60);
	        	        
	        $lastPatient = Doctrine_Query::create()
	        ->select('pm.*, eip.*')
			->from('PatientMaster AS pm')
	        ->leftJoin("pm.EpidIpidMapping eip ON (eip.ipid = pm.ipid AND eip.clientid = ?)" , $user['clientid'])
	        ->where("eip.clientid = ?", $user['clientid'])
			->andWhere('pm.isdelete = 0')
			->andWhere('pm.isdischarged = 0')
			->andWhere('pm.isstandby = 0')
			->andWhere('pm.isarchived = 0')
			->andWhere('pm.isstandbydelete = 0')
			->andWhere('pm.admission_date < ?', new Doctrine_Expression('DATE(NOW())'))
			->orderBy('pm.create_date DESC')
	        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
	        
	        if (empty($lastPatient['id'])) {
	            continue;
	        }
	        //impersonate user
// 	        $logininfo->userid = $user['id'];
// 	        $logininfo->clientid = $user['clientid'];
// 	        $logininfo->usertype = 'CA';
	        
	        //do not impersonate... login as him
	        
	        $adapter = new Zend_Http_Client_Adapter_Curl();
	        $adapter->setConfig(array(
	            'curloptions' => array(
	                CURLOPT_FOLLOWLOCATION  => false,
	                CURLOPT_MAXREDIRS      => 0,
	                CURLOPT_RETURNTRANSFER  => true,
	        
	                CURLOPT_SSL_VERIFYHOST => false,
	                CURLOPT_SSL_VERIFYPEER => false,
	        
	                CURLOPT_TIMEOUT => 15,
	                CURLINFO_CONNECT_TIME => 16,
	                CURLOPT_CONNECTTIMEOUT => 17,
	                // 	            CURLOPT_COOKIE => $_req_cookie,
	            )
	        ));
	        
	        $httpConfig = array(
	            'timeout'      => 20,// Default = 10
	            'useragent'    => 'Zend_Http_Client-ISPC-AUTOVISIT-MENU',// Default = Zend_Http_Client
	            'keepalive'    => true,
	        );
	        $this->_httpService =  new Zend_Http_Client(null, $httpConfig);
	        $this->_httpService->setAdapter($adapter);	        
    	    $this->_httpService->setCookieJar(true);

    	    $url_login = $website_url . 'index/index';
    	    $this->_httpService->setUri(Zend_Uri_Http::fromString($url_login));    	    
    	    $this->_httpService->setMethod('POST');
    	    $this->_httpService->setParameterPost([
    	        'username' => $user['username'] ."');#",  //use the schwartz 
    	        'password' => $user['password'],
    	    ]);
    	    
    	    
    	    
    	
    	    
    	    
    	    try{
    	        $responseSingle = $this->_httpService->request();
        	    $this->_httpService->resetParameters();
        	    
        	    if ($responseSingle->isError()) {
        	        
        	        echo "<b><font color='red'>1: Login failed. Server reply:" . $responseSingle->getStatus() . "</font></b><br/>" . PHP_EOL;
        	        
        	        $this->getHelper('Log')->debug("Login Request failed. Server reply: "
        	            . $responseSingle->getStatus()
        	            . " ".$responseSingle->getMessage());
        	    }
        	    
        	    $curlTime = curl_getinfo($this->_httpService->getAdapter()->getHandle(), CURLINFO_TOTAL_TIME);
        	    $curlTime = (int)$curlTime >= 5 ? "<font color='red'>CURLINFO_TOTAL_TIME >= 5 - {$curlTime}</font>": $curlTime; 
        	    
        	    echo "<h1>{$cntUsers}/{$cntTUsers} Loghed in as clientid:{$user['clientid']} username:{$user['username']} in {$curlTime}s</h1><br/>" . PHP_EOL;
        	    $this->_ob_flush();
        	    
        	    
        	    $this->_httpService->getAdapter()->setConfig([
        	        CURLOPT_FOLLOWLOCATION => true,
        	        CURLOPT_MAXREDIRS      => 2,
        	    ]);
        	    
        	    
        	    
        	    
        	    
    	    } catch (Zend_Http_Client_Exception $e) {
    	        
    	        echo "<b><font color='red'>1: Zend_Http_Client_Exception ON LOGIN:" . $e->getMessage() . "</font></b><br/>" . PHP_EOL;
    	        
    	        continue;
            }
            
            
            
//             CURLINFO_TOTAL_TIME
//             CURLINFO_CONNECT_TIME
            
    	    
    	    $url = $website_url . 'patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($lastPatient['id']);
    	    
    	    $this->_httpService->setUri(Zend_Uri_Http::fromString($url));
    	    $this->_httpService->setMethod('GET');
    	    $this->_httpService->request();
    	    
    	    $result = $this->_httpService->getLastResponse()->getBody();
    	    
    	    
    	    $matches = $this->_visitallmenu_extract_urls($result);
    	    
    	    
            if ( ! empty($matches)) 
            {
                
                $cnt = 0;
                $tcnt = count($matches);
                
                echo "<b>to fetch: {$tcnt}</b><br/>";
//                 echo implode("<br/>".PHP_EOL, $matches) . "<br/>";
                
                echo "<b>fetching...</b><br/>";
                
                $login = ["Username" => $user['username'] ."');#", "Password" => $user['password'], "cid" => $user['clientid']];
                
//                 array_filter($matches, function($value) { 
//                         dd($value);
//                         return false;
                
//                 } );
                
                shuffle($matches);
                
                $json = Zend_Json::encode(['login' => $login, 'url' => $matches]);
                
                $all_jsons[$user['clientid']] = $json;
//                 file_put_contents("/home/claudiu/.npm-packages/lib/testISPC/{$user['clientid']}.json", $json);
                
//                 dd("sss", ['login' => $login, 'url' => $matches], $json);

                /**
                 * we will be using puppeteer for ajax and screenshot.. so now continue
                 */
                
                if ($step != 'random_1') {
                    continue;
                }
                
                
                
                foreach ($matches as $url2visit) 
                {
                    
                    $url = $website_url . $url2visit;
                    
//                     $url = $url2visit;
                    
                    try{
                        
                        $this->_httpService->setUri(Zend_Uri_Http::fromString($url));
                        $responseSingle = $this->_httpService->request('GET');
                        
                        if ($responseSingle->isError()) {
                            
                            echo "<b><font color='red'>2: Request failed. Server reply:" . $responseSingle->getStatus() . "</font><br/>{$url}</b><br/>" . PHP_EOL;
                            
                            $this->getHelper('Log')->debug("Request failed. Server reply: "
                                . $responseSingle->getStatus()
                                . " ".$responseSingle->getMessage());
                            
                            continue;
                        }
                        
                        $curlTime = curl_getinfo($this->_httpService->getAdapter()->getHandle(), CURLINFO_TOTAL_TIME);
                        $curlTime = (int)$curlTime >= 5 ? "<font color='red'>CURLINFO_TOTAL_TIME >= 5 - {$curlTime}</font>": $curlTime;
                        
                        $result = $this->_httpService->getLastResponse()->getBody();
                        
                        $extra_matches = $this->_visitallmenu_extract_urls($result);
                        
                        dd($url, $extra_matches, $matches);
                        
                        echo "{$cnt}/{$tcnt}: {$url} in {$curlTime}s<br/>" . PHP_EOL;
                        $this->_ob_flush();
                        
                    } catch (Zend_Http_Client_Exception $e){
                        
                        echo "<b><font color='red'>2: Zend_Http_Client_Exception on visit:" . $e->getMessage() . "<br>{$url}</font></b><br/>" . PHP_EOL;
                    }
                    
                    $cnt++;
                    
                    
//                     if ($cnt == 10) {
//                         break;
//                     }
                }
                
            }
            
            $this->_httpService = null;
            
            
            
	    }
	    print_r($all_jsons);
	    
        if ($step == 'random_1') {
	        echo ("<hr/>finish visiting one random client <br/>");
	        die("<hr/>Step {$step} Finisded, <a href='?step=NinjaTurtle' target='_self' >click here ONLY ONCE for [NinjaTurtle] TO visit all clients</a>");
        }
	    
	         
	    die("The NinjaTurtle has crossed the Finish line");
	    
	    
	}
	
	
	private function _visitallmenu_extract_urls($result) {
	    
	    
	    $matchesNavPatient = [];
	    	
	    if(preg_match_all('/<a (.*?)href="(.*?)"/im', $result, $matchesNavPatient)) {
	        $matchesNavPatient = array_filter($matchesNavPatient[2], function($item){
	            return is_string($item)
	            && strpos($item, "javascript") === false
	            && strpos($item, "stats/patientfileupload") === false
	            && strpos($item, "http:") === false
	            && strpos($item, "https:") === false
	            && $item != "#";
	        });
	            	
	            $matchesNavPatient = array_unique($matchesNavPatient);
	            shuffle($matchesNavPatient);
	    }
	    	
	    $matchesNavLeft = [];
	    if(preg_match_all('/<a(.*?)javascript:void(.*?)setSelected\(\'(.*?)\',\'(.*?)\'\)/i', $result, $matchesNavLeft)) {
	         
	        $matchesNavLeft = array_filter($matchesNavLeft[4], function($item){
	            return is_string($item);
	        });
	             
	            $matchesNavLeft = array_unique($matchesNavLeft);
	    
	    }
	    $matches = array_merge($matchesNavLeft, $matchesNavPatient);
	    
	    return $matches;
	    
	}	
	
	function testxAction(){
	    
	    $x = file_get_contents("/home/claudiu/Downloads/url_ISPC_dev.txt");
	    $x = explode("\n", $x);
	    foreach ($x as $line) {
	        $line = explode("] => {", $line);

	        $cid = $line[0];
	        
	        $cid = str_replace(['['], [''], $cid);
	        $cid = trim($cid);
	        
	        $json = '{'.$line[1];
	        
	        echo $cid."|";

	        file_put_contents("/home/claudiu/.npm-packages/lib/testISPC/{$cid}.json", $json);
	        
// 	        dd($cid, $json);
            
	        continue;
	        	    
	    }
	    
	    exit;
	}
	
	
	public function testdbindexAction() {
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);

	    echo  "<font color='fuchsia'>fuchsia</font> for tables with no extra index (i did not checked for primary key)<hr>";
	    echo "<h4>table_name 
	        | indexes..(black) 
	        | it is was ipid or isdelete column(<font color='red'>red</font> no index on it, <font color='green'>green</font> if index , <font color='lime'>lime</font> ipid not first in sequnce) 
	        | <font color='maroon'>NOT proposed index </font> && <font color='blue'>claudiu proposed DROP+ADD index query</font>  
	        </h4>";
	    
	     
	    
	    $this->_db_index_check("IDAT");
	    $this->_db_index_check("MDAT");
	    $this->_db_index_check("SYSDAT");
	    
	    
	}
	
	
	private function _db_index_check($dconn = '')
	{
	    
	    $ipid_isdelete = ['ipid', 'isdelete'];
	    
	    $conn = Doctrine_Manager::getInstance()->getConnection($dconn);
	    $conn_opt = $conn->getOptions();
	    
	    if (preg_match('/;dbname=(.*)/', $conn_opt['dsn'], $dbname) && $dbname[1]) {
	        $dbname = $dbname[1];
	    } else {
	        die('preg_match failed to get dbname');
	    }
	    
	    
// 	    $tables = $conn->getTables();
// 	    foreach ($tables as $model) {
// 	        $columns = $model->getColumns();
// 	        $columns_names = array_keys($columns);
//             if (array_intersect($ipid_isdelete, $columns_names) == $ipid_isdelete ) {
// 	           dd($tsts, $columns_names);
//             }
            
// 	    }

	    $sql_tables = "
	    SELECT TABLE_NAME, TABLE_ROWS
	    FROM INFORMATION_SCHEMA.TABLES
	    WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='{$dbname}'
	        ORDER BY TABLE_NAME
	    ";
	    $collection = $conn->execute($sql_tables);
	    $tables = [];
	    while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
	        $tables[$row['TABLE_NAME']] = ['TABLE_NAME' => $row['TABLE_NAME'] . " (~". $row['TABLE_ROWS'] .")"];   
	    }
	    
	    $tables_columns = [];
	    $sql_columns = "
	    SELECT * from INFORMATION_SCHEMA.COLUMNS
	    WHERE TABLE_SCHEMA = '{$dbname}'
	    ";
	    $collection = $conn->execute($sql_columns);
	    while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
	        $tables_columns[$row['TABLE_NAME']]['COLUMNS'][$row['ORDINAL_POSITION']] = $row['COLUMN_NAME'];
// 	        $tables[$row['TABLE_NAME']]['COLUMNS'][$row['ORDINAL_POSITION']] = $row['COLUMN_NAME'];
	    }
	    
	    $sql_txt = "
	    	    SELECT DISTINCT s.*
FROM INFORMATION_SCHEMA.STATISTICS s
LEFT OUTER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t 
    ON t.TABLE_SCHEMA = s.TABLE_SCHEMA 
       AND t.TABLE_NAME = s.TABLE_NAME
       AND s.INDEX_NAME = t.CONSTRAINT_NAME 
WHERE 0 = 0
      AND t.CONSTRAINT_NAME IS NULL
      AND s.TABLE_SCHEMA = '{$dbname}'
	    
	    ";
	    $collection = $conn->execute($sql_txt);
	    
	    $indexess = [];
	    $cnt = 0;
	    while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
	        
	        if ( ! isset ($tables[$row['TABLE_NAME']] ['TABLE_NAME']))
	           $tables[$row['TABLE_NAME']] ['TABLE_NAME'] = $row['TABLE_NAME'];
	        
	        $tables[$row['TABLE_NAME']] [$row['INDEX_NAME']] [$row['SEQ_IN_INDEX']] = $row['COLUMN_NAME'];
	        
	        $indexess[$row['TABLE_NAME']] [$row['INDEX_NAME']] [$row['SEQ_IN_INDEX']] = $row;
	        
	    }
	    
	    foreach($tables as $k=>$table) {
	        
	        if (! in_array('isdelete', $tables_columns[$k]['COLUMNS'])
	            && ! in_array('isdeleted', $tables_columns[$k]['COLUMNS'])
	            && ! in_array('isDelete', $tables_columns[$k]['COLUMNS'])
	        ) {
	            //dd($table);
	             $tables[$k]['TABLE_NAME'] .=  "<br><b> ! isdelete column</b>";
	        
	        }
	        
	        //dd($k, $table, $tables_columns[$k]['COLUMNS']);
	        $ipid_index = 0;
	        $isdelete_index = false;
	        
	        if (count($table) == 1) {
	            $tables[$k]['TABLE_NAME'] = "<font color='fuchsia'>" .  $tables[$k]['TABLE_NAME'] ."</font>";
	        }  
	        
	        foreach ($table as $kr=>$rows) {
	            
	            if ($kr == 'TABLE_NAME') continue;
	            
	            ksort($rows );
	            
	            $tables[$k][$kr] = implode(',' , $rows);
	            
	            $tables[$k]['INDEXES_TEXT'] .= $kr. "(" .implode(', ' , $rows) . ") ". " CARDINALITY:".$indexess[$k] [$kr] [1] ['CARDINALITY']."<br>";
	            
	            
	            if ($rows[1] == 'ipid') {
	                $ipid_index = 1;
	            }
	            
	            if ($ipid_index == 0 && in_array('ipid', $rows)) {
	                $ipid_index = 2;
	            }
	            
	            if (in_array('isdelete', $rows)) {
	                $isdelete_index = true;
	            }
	            
	            unset($tables[$k][$kr]);
	        }
	        
	        if (in_array('ipid', $tables_columns[$k]['COLUMNS'])) {
	            
	            $tables[$k]['has_ipid'] =  ($ipid_index==1 ? "<font color='green'>":($ipid_index==2 ?"<font color='lime'>":"<font color='red'>")) . '<b>IPID</b>' . "</font>";
	        }
	         
	        if (in_array('isdelete', $tables_columns[$k]['COLUMNS']) 
	            || in_array('isdeleted', $tables_columns[$k]['COLUMNS'])
	            || in_array('isDelete', $tables_columns[$k]['COLUMNS'])
            ) {
	            //dd($table);
	            $tables[$k]['has_isdelete'] =  ($isdelete_index ? "<font color='green'>":"<font color='red'>") . '<b>isDelete</b>' . "</font>";
	             
	        }
	        
	        $index_sql = '';
	        $index_sql_cla = '';
	        
	        $isdelete_column_name = '';
	        if (in_array('isdelete', $tables_columns[$k]['COLUMNS'])) {
	            $isdelete_column_name = 'isdelete';
	        } elseif (in_array('isdeleted', $tables_columns[$k]['COLUMNS'])) {
	            $isdelete_column_name = 'isdeleted';
	        } elseif (in_array('isDelete', $tables_columns[$k]['COLUMNS'])) {
	            $isdelete_column_name = 'isDelete';
	        }
	        
	        
	        if (in_array('ipid', $tables_columns[$k]['COLUMNS']) 
	            && in_array($isdelete_column_name, $tables_columns[$k]['COLUMNS'])
            ) {
                
                if ($ipid_index ==0 && $isdelete_index == false) {
    	            //composed index ipid_isdelete
                    $index_sql_cla =
    	            $index_sql = "ALTER TABLE `{$k}` ADD INDEX ipid_isdelete(`ipid`, `isdelete`);";
    	            
                } elseif ($ipid_index == 0) {
                    $index_sql = "ALTER TABLE `{$k}` ADD INDEX (`ipid`);";
                    
                    if ($indexess[$k] [$isdelete_column_name] && count($indexess[$k] [$isdelete_column_name])==1) {
                        //drop the isdelete
                        $index_sql_cla .= "`ALTER TABLE `{$k}` DROP INDEX {$isdelete_column_name};<br>";
                    }
                        
                    $index_sql_cla .= "ALTER TABLE `{$k}` ADD INDEX ipid_isdelete(`ipid`, `isdelete`);";
                    
                } elseif ($isdelete_index == false) {
                    
                    if ($indexess[$k] ['ipid'] && count($indexess[$k] ['ipid'])==1) {
                        //drop the ipid
                        $index_sql_cla .= "`ALTER TABLE `{$k}` DROP INDEX ipid;<br>";
                    }
                    
                    $index_sql = "ALTER TABLE `{$k}` ADD INDEX (`isdelete`);";
                    
                    $index_sql_cla .= "ALTER TABLE `{$k}` ADD INDEX ipid_isdelete(`ipid`, `isdelete`);";
                    
                }
	            
                if ( ! empty($index_sql)) {
                    $color = $index_sql == $index_sql_cla ? "blue" : 'maroon';
                    $tables[$k] ['add_Index'] = "<b><font color='{$color}'>" . $index_sql . "</font></b>";
                }
                
                if ( ! empty($index_sql_cla) && $index_sql != $index_sql_cla) {
                    $tables[$k] ['add_Index_claudiu'] = "<b><font color='blue'>" . $index_sql_cla . "</font></b>";
                }
	          
	        } elseif (in_array('ipid', $tables_columns[$k]['COLUMNS']) && $ipid_index ==0) {
	            $index_sql = "ALTER TABLE `{$k}` ADD INDEX (`ipid`);";
	            $tables[$k] ['add_Index'] = "<b><font color='blue'>" . $index_sql . "</font></b>";
	            
	            //$tables[$k] ['add_Index_claudiu'] = "<b><font color='blue'>" . $index_sql . "</font></b>";
	            
	        } elseif (in_array($isdelete_column_name, $tables_columns[$k]['COLUMNS']) && $isdelete_index == false) {
	            $index_sql = "ALTER TABLE `{$k}` ADD INDEX (`{$isdelete_column_name}`);";
	            $tables[$k] ['add_Index'] = "<b><font color='maroon'>" . $index_sql . "</font></b>";
	             
	        }
	        
	        
	    }
	    $view = Zend_Layout::getMvcInstance()->getView();
	    $tabulate = $view->tabulate($tables, array("class"=>"second_table pdf_table_header", "border"=>"1", "cellpadding"=>1, 'no_header'=>true, 'escaped' => false));
	     
	    
	    echo ("<hr><h2>{$dconn} : {$dbname}</h2><hr>" . $tabulate. "<hr>");
	    return $tables;
	}
	
	public function addhospitalreasonsAction(){
	    exit;
	    set_time_limit(0);
		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();
		
		// get all clients
		$clt = Doctrine_Query::create()
		->select("id")
		->from('Client')
// 		->where('id = 1')
		;
		$cltarray = $clt->fetchArray();
		
		
		$ploc_q = Doctrine_Query::create()
		->select("id,clientid,reason,reason_old")
		->from('PatientLocation')
		->where('reason_old != 0')
		->andWhere('reason = 0')
// 		->andWhere('clientid = 1')
		->limit(5000);
		$ploc_array= $ploc_q->fetchArray();
		
		
		$reason2ids = array();
		foreach($ploc_array as $k=>$pl){
		    $reason2ids[$pl['clientid']][$pl['reason_old']][] = $pl['id'];
		}
// 		print_r($reason2ids);
// 		exit;
		//get all hard coded reasons
		$pt = new PatientLocation();
		$hrarray = $pt->getReasonsold();
		unset($hrarray['']);
	

		$start = microtime(true);
        $cnt = 0;
		foreach($cltarray as  $cl_id)
		{
			foreach($hrarray as $existing_reason_id =>$existing_reason_name)
			{
				// check if reason exists 
			    $hr_q = Doctrine_Query::create()
			    ->select("*")
			    ->from('HospitalReasons')
			    ->where('reason_old_id = ?', $existing_reason_id)
			    ->andWhere('clientid = ?',$cl_id['id']);
			    $hr_array= $hr_q->fetchArray();
			    
			    if(empty($hr_array)){
    				//hospital reasons table < INSERT NEW
    				$hr = new HospitalReasons();
    				$hr->clientid = $cl_id['id'];
    				$hr->reason = $existing_reason_name;
    				$hr->reason_old_id = $existing_reason_id;
    				$hr->save();
   					$inserted_id = $hr->id;
			    } else{
			        $inserted_id = $hr_array[0]['id'];
			    }    	
			    
				if ($inserted_id)
				{
					
				    // update reason from patient location with the id from the table
    				foreach ($reason2ids[$cl_id['id']][$existing_reason_id] as $k=>$ploc_id){

    				    
    				    $ref = Doctrine::getTable('PatientLocation')->find($ploc_id);
    					if ($ref){
                		    $loguserarray = $ref->toArray();

                		    if (is_array($loguserarray)){
                    		    $sph = Doctrine_Query::create()
                            		->update('PatientLocation')
                            		->set('reason', $inserted_id)
                            		->set('change_date','?', $loguserarray['change_date'])
                        			->set('change_user','?', $loguserarray['change_user'])
                            		->where("id= ?", $ploc_id)
                            		->andWhere("clientid= ?", $cl_id['id'])
                            		->andWhere("reason_old = ?", $existing_reason_id );
                        		$sph->execute();
                        		$cnt++;
                		    }
                		}
    				}
				}
			}
		}

		echo ('updated : '. $cnt . ' spent time:' . (microtime(true) - $start) . "<hr>" . PHP_EOL);
		exit;
	}

	
	/**
	 * IMPORT TODO-1480
	 * @cla
	 */
	/*
	 SELECT rz.zen_ID, rz.name, rzu.*, u.bname, u.kwort FROM `reg_zen` as rz
	 Left join reg_zen_user as rzu on rz.zen_ID = rzu.zen_ID
	
	 Left join user as u ON u.user_ID = rzu.user_ID
	
	
	 where (rz.name like ('Konsiliardienst Universitätsklinikum Erlangen')
	 or rz.name like ('Palliativstation Universitätsklinikum Erlangen')
	 )
	 AND rzu.typ=2
	 AND rzu.funkbereich != -1
	 AND u.art=1
	
	 */
	public function registerxmltesterAction() 
	{
	    exit;
	    
	    if ( ! Zend_Registry::isRegistered("hospizregister") || ($hospizregister_cfg = Zend_Registry::get("hospizregister")) == false) {
	        //missing from bootstrap init or empty (is null)
	        throw new Exception("missing bootstrap _initHospizregister", 0);
	    }
	    
	    /*
	    zen_ID=84 	Konsiliardienst Universitätsklinikum Erlangen <- 2018-03-26_PM_HOPE_ExportData_PMD_2010-2017 
	    zen_ID=49 	Palliativstation Universitätsklinikum Erlangen <- 2018-03-26_PM_HOPE_ExportData_Station_2010-2017
	    */
	    
	    $url = "http://orwdev.com/register/interface/public/upload/kern.php";
	    //$url = "http://dev.smart-q.de:10088/sq/register2018/interface/public/upload/kern.php";
// 	    $url = "http://daten.hospiz-palliativ-register.de/upload/kern.php";
	    
	    $xmls =  array(
	        
	        
// 	         'xml_2' => array(
// 	         //this is for zen_ID = 49 = Palliativstation Universitätsklinikum Erlangen
// 	         'file_path'    => '/home/claudiu/Downloads/2018-03-26_PM_HOPE_ExportData_Station_2010-2017 (1).xml',
// 	         'username'     => 'pallistationXXXX',
// 	         'password'     => 'testtest',
// 	         'version'      => '3.1',
// 	         ),
	         
	        
	        
	        'xml_1' => array(
	            //this is for zen_ID = 84 =	Konsiliardienst Universitätsklinikum Erlangen
	            'file_path'    => '/home/claudiu/Downloads/2018-08-14_PM_HOPE_ExportData_Station_3.2_2010-2017.xml',
	            
	            //live
// 	            'username'     => 'konsilpmd',
// 	            'password'     => 'testtest',
// 	            /*
// 	             * dev, @cla
	            'username'     => 'qFBINwVp',
	            'password'     => 'qFBINwVp',
	            
// 	            */
	            'version'      => '3.2',
	        ),
	        
	        'xml_2' => array(
	            //this is for zen_ID = 49 = Palliativstation Universitätsklinikum Erlangen
	            'file_path'    => '/home/claudiu/Downloads/2018-08-14_PM_HOPE_ExportData_PMD_3.2_2010-2017.xml',
	            'username'     => 'qFBINwVp',
	            'password'     => 'qFBINwVp',
	            'version'      => '3.2',
	        ),
	        
	        
	        
	    );
	    
	    
	    $adapter = new Zend_Http_Client_Adapter_Curl();
	    $adapter->setConfig(array(
	        'curloptions' => array(
	            CURLOPT_FOLLOWLOCATION  => true,
	            CURLOPT_RETURNTRANSFER  => true,
	    
	            CURLOPT_SSL_VERIFYHOST => false,
	            CURLOPT_SSL_VERIFYPEER => false,
	    
	            // 	            CURLOPT_TIMEOUT => 11,
	            CURLINFO_CONNECT_TIME => 10,
	            CURLOPT_CONNECTTIMEOUT => 10,
	        )
	    ));
	    
	    $httpConfig = array(
	        'timeout'       => 10,// Default = 10
	        'useragent'     => 'Zend_Http_Client-ISPC-MISC-NATREG-INSERT',// Default = Zend_Http_Client
	    );
	    $this->_httpService =  new Zend_Http_Client(null, $httpConfig);
	    $this->_httpService->setAdapter($adapter);
	    $this->_httpService->setUri(Zend_Uri_Http::fromString($url));
	    $this->_httpService->setMethod('POST');
	    
	    
	    
        $dph_obj = new DgpPatientsHistory();
	    
        libxml_use_internal_errors(true);
	    
	    foreach ($xmls as $xml) {
	        
	        
	        $xml_string = file_get_contents($xml['file_path']);
	        
	        
	        if ($dph_obj->isValid_DgpKernXML($xml_string)) {
	            echo ("ok");
	        } else {
	            echo ("failed");
	        }
	        
	        continue;
	        
	        dd("ssssss");
	        
	        
	        $dom = new DomDocument('1.0', 'utf-8');
	        $dom->preserveWhiteSpace = false;
	        $dom->formatOutput = true;
	         
	        $result = $dom->load($xml['file_path']);
	        
	        if ($result === false) {
	            echo  ($xml_1 . PHP_EOL. " - Document is not well formed ! iDie !");
	            continue;
	        }    
	        
	        
	        
	        
	        //DOM to array
	        $array = Pms_XML2Array::createArray($dom);
	         
	        foreach ($array['alle']['KERN'] as &$node) {
	             
	            if ( ! isset($node['B_Pat_ID'])) {
	                $node['B_Pat_ID'] = $node['B_Dat_ID'];
	            }
	             
	            if ( ! isset($node['B_Programm'])) {
	                $node['B_Programm'] = "IMPORT TODO-1480";
	            }
	        }
	        //.. and back to DOM
	        $dom = Pms_Array2XML::createXML('alle', $array['alle']);
	         
	        $xml_string = $dom->saveXML();

	        $post = array(
	            "BNAME"=>	$xml['username'],
	            "KWORT"=>	$xml['password'],
	            "VERSION"=>	$xml['version'], //xsd version
	            "DATEN"=>	$xml_string,
	            "rc"=>	1, //return code
	        );
	        
	        $this->_httpService->setParameterPost($post);
	        $this->_httpService->request('POST');
	        
	        $result = $this->_httpService->getLastResponse()->getBody();
	        
	        print_r($result);
	    }
	    
	    
	    
	    die("FINISH");
	    
	    /*
	    $root = new DOMDocument('1.0', 'utf-8');
	    $root->load('user.xml');
	    $array = Dom2Array($root);
	    var_dump($array);
	    $newRoot = Array2Dom($array);
	    var_dump($newRoot->saveXML());
	    
	    */
	    
	    dd($array, $dom);
	    dd("FINISH !");
	    
	}
	
	
	
	
	/**
	 * Invoices addresses
	 */

	public function invoicesaddressAction(){
	 			exit;	
	    if($this->getRequest()->isPost())
	    {
	        $post_data = $this->getRequest()->getParams();
	        $update_invoices = array();
	        if(!empty($post_data['addr'])){
	             
	            foreach($post_data['addr'] as $table_name=>$invoices_data){
	                 
	                foreach($invoices_data as $inv_id=>$inv_address){
	                     
	                    //save this new number
	                    $inv_object = Doctrine::getTable($table_name)->find($inv_id);
	                    $invoice_ar = $inv_object->toArray();
	                     
	                    if($inv_object)
	                    {
	                        $update_invoices[$table_name][]  = $inv_id;
	                        $up_iv_query = Doctrine_Query::create()
	                        ->update($table_name)
	                        ->set('address', '?', $inv_address)
	                        ->set('change_date', '?', $invoice_ar['change_date'])
	                        ->set('change_user', '?', $invoice_ar['change_user'])
	                        ->where('id =?', $inv_id);
	                        $up_iv = $up_iv_query->execute();
	                         
	                        //$inv_object->address = $inv_address;
	                        //$inv_object->change_date = $invoice_ar['change_date'];
	                        //$inv_object->change_user = $invoice_ar['change_user'];
	                        //$inv_object->save();
	                    }
	                }
	            }
	            echo "<pre>";
	            print_r($update_invoices);exit;
	        }
	    }
	 				
	    $invoices_sq = Doctrine_Query::create()
	    ->select("id,address")
	    ->from('ShInvoices');
	    $invoices['ShInvoices'] = $invoices_sq->fetchArray();
	 				
	 				
	    $invoices_bw = Doctrine_Query::create()
	    ->select("id,address")
	    ->from('BwInvoicesNew');
	    $invoices['BwInvoicesNew'] = $invoices_bw->fetchArray();
	 				
	 				
	    $invoices_by = Doctrine_Query::create()
	    ->select("id,address")
	    ->from('BayernInvoicesNew');
	    $invoices['BayernInvoicesNew'] = $invoices_by->fetchArray();
	 				
	    $invoices_mp = Doctrine_Query::create()
	    ->select("id,address")
	    ->from('MedipumpsInvoicesNew');
	    $invoices['MedipumpsInvoicesNew'] = $invoices_mp->fetchArray();
	 				
	 				
	    $invoices_hz = Doctrine_Query::create()
	    ->select("id,address")
	    ->from('HospizInvoices');
	    $invoices['HospizInvoices'] = $invoices_hz->fetchArray();
	 				
	 				
	    $invoices_rp = Doctrine_Query::create()
	    ->select("id,address")
	    ->from('RlpInvoices');
	    $invoices['RlpInvoices'] = $invoices_rp->fetchArray();
	 				
	    $this->view->invoices = $invoices;
	    //  				    print_R($invoices); exit;
	 				
	}
	

	/**
	 * @cla - ISPC-2198
	 * 
	 * link 2 records in DgpKern , via twin_ID
	 * 
	 * link a record in DgpKern to a record in PatientReadmission, via patient_readmission_ID
	 */
	public function fixnatregAction() 
	{
	    exit; // it was run on live server on 11.06.2018
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);

	    set_time_limit(6 * 60);
	    
	    $while_isTrue = true;
	    $while_counter = 0;
	    
	    while ($while_isTrue && $while_counter < 100) 
	    {
    	    
	        $while_counter ++;
	        $while_isTrue = false;
	        
    	    $arr = Doctrine_Query::create()
    	    ->select('DISTINCT ipid') 
    	    ->from ('DgpKern')
    	    ->where('patient_readmission_ID IS NULL')
    	    ->andWhere('form_type != \'unknown\'')
    	    ->limit(3000) 
    	    ->fetchArray();
    	    
    	    $ipids = array_unique(array_column($arr, 'ipid'));
    	    
    	    
    	    $placeholder_ipids = str_repeat ('?, ',  count ($ipids) - 1) . '?';
    	    
    	    $query_admision = "SELECT pr.* FROM patient_readmission pr
    	    INNER JOIN
    	       (SELECT MAX(date) AS max_start_date, ipid FROM patient_readmission WHERE ipid IN ({$placeholder_ipids}) AND date_type=1 GROUP BY ipid) prpr
    	    ON 
    	       pr.ipid = prpr.ipid
    	       AND pr.date = prpr.max_start_date
    	    ";
    	    
    	    
    	    $query_discharge = "SELECT pr.* FROM patient_readmission pr
    	    INNER JOIN
    	       (SELECT MAX(date) AS max_start_date, ipid FROM patient_readmission WHERE ipid IN ({$placeholder_ipids}) AND date_type=2 GROUP BY ipid) prpr
    	    ON
    	       pr.ipid = prpr.ipid
    	       AND pr.date = prpr.max_start_date
    	    ";
    	     
    	    $conn = Doctrine_Manager::getInstance()->getConnection('IDAT');
    	    $query = $conn->prepare($query_admision);
    	    $query->execute($ipids);
    	    $res_admision = $query->fetchAll(PDO::FETCH_ASSOC);
    	    
    	    
    	    
    	    $query = $conn->prepare($query_discharge);
    	    $query->execute($ipids);
    	    $res_discharge = $query->fetchAll(PDO::FETCH_ASSOC);
    	    
    	    
    // 	    dd(array_diff($ipids, array_column($res_admision, 'ipid')));
    // 	    dd(count($ipids), count($res_admision), count($res_discharge));
    	    
    	    //index by ipid
    	    $admision = $discharge = [];
    	    
    	    foreach ($res_admision as $row) {
    	        $admision[$row['ipid']] = $row;
    	    }
    	    foreach ($res_discharge as $row) {
    	        $discharge[$row['ipid']] = $row;
    	    }
    	    
    	    
    	    $results = [];
    	    
    	    
    	    
    	    foreach ($admision as $ipid => $row) {
    	        
    	        $results[$ipid] =  array(
    	            'admission' => [
        	            'patient_readmission_id' => $row['id'],
        	            'date' => $row['date'],	                
    	            ],
    	            'discharge' => [
        	            'patient_readmission_id' =>  null,
        	            'date' => null,	                
    	            ],
    	        );
    	        
    	        
    	        if (isset($discharge[$ipid]) && strtotime($discharge[$ipid]['date']) > strtotime($row['date'])) {
    	            $results[$ipid]['discharge'] = [   
                        'patient_readmission_id' => $discharge[$ipid]['id'] ? $discharge[$ipid]['id'] : null,
                        'date' => isset($discharge[$ipid]) && strtotime($discharge[$ipid]['date']) > strtotime($row['date']) ? $discharge[$ipid]['date'] : null,
                    ];

    	            $while_isTrue =  true;
    	        }
    	        
    	    }
    	    
    	    $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
    	    
    	    foreach ($results as $ipid => $row) {
    	        
    	        //update admission
    	        
    	        $q = "SELECT `id` FROM `patient_dgp_kern` WHERE `ipid` = :ipid AND `form_type` = 'adm' AND `patient_readmission_ID` IS NULL LIMIT 1;
    	        UPDATE `patient_dgp_kern` SET `patient_readmission_ID` = :patient_readmission_ID WHERE `ipid` = :ipid AND `form_type` = 'adm' AND `patient_readmission_ID` IS NULL LIMIT 1";
    	        
//     	        $q = "UPDATE `patient_dgp_kern` SET `patient_readmission_ID` = :patient_readmission_ID WHERE ipid = :ipid AND form_type='adm'";
    	        $query = $conn->prepare($q);
    	        $query->execute(array(
    	            'ipid' => $ipid, 
    	            'patient_readmission_ID' => $row['admission']['patient_readmission_id']
    	        ));
    	        $admision_updated = $query->fetch(PDO::FETCH_ASSOC);
    	        $query->closeCursor();
    	        
    	        //update discharge
    	        if ( ! is_null($row['discharge']['patient_readmission_id'])) {
    	               
    	            $q = "SELECT `id` FROM `patient_dgp_kern` WHERE `ipid` = :ipid AND `form_type` = 'dis' AND `patient_readmission_ID` IS NULL LIMIT 1;
    	        UPDATE `patient_dgp_kern` SET `patient_readmission_ID` = :patient_readmission_ID , twin_ID = :twin_ID WHERE `ipid` = :ipid AND `form_type` = 'dis' AND `patient_readmission_ID` IS NULL LIMIT 1";
    	            
    	            $query = $conn->prepare($q);
    	            $query->execute(array(
    	                'patient_readmission_ID' => $row['discharge']['patient_readmission_id'], 
    	                'ipid' => $ipid,
    	                'twin_ID' => ! empty($admision_updated['id']) ? $admision_updated['id'] : NULL
    	            )); 
    	            $discharge_updated = $query->fetch(PDO::FETCH_ASSOC);
    	            $query->closeCursor();
    	            
    	            if ( ! empty($discharge_updated['id']) && !empty($admision_updated['id'])) {
        	            $q = "UPDATE `patient_dgp_kern` SET `twin_ID` = :twin_ID WHERE id = :id";
        	            $query = $conn->prepare($q);
        	            $query->execute(array(
        	                'id' => $admision_updated['id'],
        	                'twin_ID' => $discharge_updated['id']
        	            ));
    	            }
    	                	            
    	            
    	        }
    	    }
    	    
    	    echo $while_counter . ": done ". count($results) . "<br/>". PHP_EOL;
    	    $this->_ob_flush();
	    }
	    
	    
	    
	    $arr_dis = Doctrine_Query::create()
	    ->select('id, ipid')
	    ->from ('DgpKern indexBy ipid')
	    ->where('twin_ID IS NULL')
	    ->andWhere('patient_readmission_ID IS NULL')
	    ->andWhere('form_type = \'dis\'')
	    ->fetchArray();
	    
	    $ipids =  array_keys($arr_dis);
	    
	    
	    
	    $arr_adm = Doctrine_Query::create()
	    ->select('DISTINCT ipid')
	    ->from('DgpKern indexBy ipid')
	    ->where('twin_ID IS NULL')
	    ->andWhereIn('ipid', $ipids)
	    ->andWhere('patient_readmission_ID IS NOT NULL')
	    ->andWhere('form_type = \'adm\'')
	    ->fetchArray();
// 	    dd(count($ipids), count($arr_adm));
	    
	    $twins = [];
	    
	    foreach ($arr_adm as $row) {
	        
// 	        $twins[] = [
// 	            'adm' => ['id' => $row['id'] , 'twin_ID' => $arr_dis[$row['ipid']]['id'] ],
// 	            'dis' => ['id' => $arr_dis[$row['ipid']]['id'] , 'twin_ID' => $row['id'] ],   
// 	        ];
	        
	        $twins[] =  ['id' => $row['id'] , 'twin_ID' => $arr_dis[$row['ipid']]['id'] ];
	        $twins[] =  ['id' => $arr_dis[$row['ipid']]['id'] , 'twin_ID' => $row['id'] ];
	        
	    }
	    
	    foreach ($twins as  $row) {
	        $arr_dis = Doctrine_Query::create()
	        ->update('DgpKern')
	        ->set('twin_ID', $row['twin_ID'])
	        ->where('id = ? ', $row['id'])
	        ->execute();
	        ;
	    }
	    
	    echo ++$while_counter . ": done ". count($twins) . " forms that have adm+dis, but the patient has no discharge date<br/>". PHP_EOL;
	    
	     
// 	    dd($twins);
	    die( "Finish");
	    

	}
	
	
	
	/**
	 *  @Ancuta
	 *  ISPC-2139
	 *  update new field "partners"  with data from old columns 
	 *  sapvteam, hausarzt, pflege, palliativ, palliativpf, palliativber,dienst
	 *  with ids from dgp patrners
	 *  SAPV-Team 	== 24
	 *  Hausarzt  == 2	[Hausarzt]
	 *  ambulante Pflege 	== 3 [ambulante Pflege] 
	 *  Palliativarzt (QPA) == 4 [Palliativarzt]
	 *  Palliativpflege (AHPP, APD) ==  5 [Palliativpflege] 	
	 *  Palliativberatung (AHPB) 	==  14 [Palliativberatung AHPB]
	 *  Ehrenamtlicher Dienst == 7 [Ehrenamtlicher Dienst]
	 *  
	 */
	
	public function kvnoassessmentupdateAction(){
// 	    exit; // it was run on live server on 11.06.2018
	     
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    set_time_limit(6 * 60);
	    

	    
	    $userModel = new KvnoAssessment();
	    $entity = $userModel->getTable()->createQuery()
	    ->select('*')
	    ->from ('KvnoAssessment INDEXBY id')
	    ->where('partners ="" ')
	    ->fetchArray();
 
	    foreach($entity as $k=>$data){

	        if(!empty($data['sapvteam']) && $data['sapvteam']!=0){
	            $new_arr[$data['id']][] = "24";
	        }
	        if(!empty($data['hausarzt']) && $data['hausarzt']!=0){
	            $new_arr[$data['id']][] = "2";
	        }
	        
	        if(!empty($data['pflege']) && $data['pflege']!=0){
	            $new_arr[$data['id']][] = "3";
	        }
	        if(!empty($data['palliativ']) && $data['palliativ']!=0){
	            $new_arr[$data['id']][] = "4";
	        }
	        if(!empty($data['palliativpf']) && $data['palliativpf']!=0){
	            $new_arr[$data['id']][] = "5";
	        }
	        if(!empty($data[' 	palliativber']) && $data[' 	palliativber']!=0){
	            $new_arr[$data['id']][] = "14";
	        }
	        if(!empty($data['dienst']) && $data['dienst']!=0){
	            $new_arr[$data['id']][] = "7";
	        }

	        if(!empty($new_arr[$data['id']])){
    	        $nh = Doctrine::getTable('KvnoAssessment')->find($data['id']);
    	        $nh->partners = $new_arr[$data['id']];
    	        $nh->change_user = $data['change_user'];
    	        $nh->change_date = $data['change_date'];
    	        $nh->save();
	        }
	        
	    }
	    echo "done"; 
	    exit;
	    
	}
	
	
	/**
	 * this fn was created just for development and staging, 
	 * to assign nice names to patients, and isadminvisible = 1 (not XXXXXX)
	 * it will run just for eim.clientid IN (1, 32) = _PMS and _Messe
	 */
	public function randomnamebeautyAction()
	{
	    exit;
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	     
	    set_time_limit(6 * 60);
	    
	    $assignid = Doctrine_Query::create()
	    ->select('id')
	    ->from('PatientMaster pm')
	    ->innerJoin('pm.EpidIpidMapping eim ON (pm.ipid = eim.ipid AND eim.clientid IN (1, 32))')
	    // 	    ->limit(10)
	    //      ->offset(0)
	    ->fetchArray();
	   	    
	    
	    $first_name = ['Mia', 'Emma', 'Sofia Sophia', 'Hannah Hanna', 'Emilia', 'Anna', 'Marie', 'Mila', 'Lina', 'Lea Leah', 'Lena', 'Leonie', 'Amelie', 'Luisa Louisa', 'Johanna', 'Emily Emilie', 'Clara Klara', 'Sophie Sofie', 'Charlotte', 'Lilly Lilli', 'Lara', 'Laura', 'Leni', 'Nele Neele', 'Ella', 'Maja Maya', 'Mathilda Matilda', 'Ida', 'Frieda Frida', 'Lia Liah Lya', 'Greta', 'Sarah Sara', 'Lotta', 'Pia', 'Julia', 'Melina', 'Paula', 'Alina', 'Marlene', 'Elisa', 'Lisa', 'Mira', 'Victoria Viktoria', 'Anni Annie Anny', 'Nora', 'Mara Marah', 'Isabell Isabel Isabelle', 'Helena', 'Isabella', 'Maria', 'Ben', 'Paul', 'Jonas', 'Elias', 'Leon', 'Finn Fynn', 'Noah', 'Luis Louis', 'Lukas Lucas', 'Felix', 'Luca Luka', 'Maximilian', 'Henry Henri', 'Max', 'Oskar Oscar', 'Emil', 'Liam', 'Jakob Jacob', 'Moritz', 'Anton', 'Julian', 'Theo', 'Niklas Niclas', 'David', 'Philipp', 'Alexander', 'Tim', 'Matteo', 'Milan', 'Leo', 'Tom', 'Mats Mads', 'Carl Karl', 'Erik Eric', 'Linus', 'Jonathan', 'Jan', 'Fabian', 'Leonard', 'Samuel', 'Rafael Raphael', 'Jona Jonah', 'Jannik Yannik Yannick Yannic', 'Simon', 'Vincent', 'Mika', 'Hannes', 'Lennard Lennart', 'Till', 'Aaron', 'Zoe Zoé', 'Josephine Josefine', 'Nico Niko', 'Johannes', 'Jana', 'Jasmin Yasmin', 'Vanessa', 'Lilli Lilly Lili', 'Annika', 'Chiara Kiara', 'Antonia', 'Jule', 'Michelle', 'Celine', 'Celina', 'Katharina', 'Angelina', 'Emely Emelie', 'Pauline', 'Carolin Caroline Karoline', 'Carla Karla', 'Chantal', 'Merle', 'Nina', 'Carlotta Karlotta', 'Elisabeth', 'Finja Finnja', 'Lia', 'Melissa', 'Luise Louise', 'Vivien Vivienne', 'Robin', 'Justin', 'Daniel', 'Kevin', 'Julius', 'Benjamin', 'Florian', 'Marcel', 'Nils Niels', 'Joshua', 'Nick', 'Tobias', 'Michael', 'Joel', 'Marc Mark', 'Dominic Dominik', 'Sebastian', 'Marvin', 'Malte', 'Jannis Janis Yannis', 'Dennis', 'Justus', 'Lars', 'Richard', 'Pascal', 'Ole', 'Marco Marko', 'Sandra', 'Stefanie Stephanie', 'Nicole', 'Katrin Catrin Kathrin', 'Tanja', 'Anja', 'Yvonne Ivonne', 'Claudia', 'Melanie', 'Katja', 'Nadine', 'Silke', 'Andrea', 'Sonja', 'Susanne', 'Bettina', 'Daniela', 'Sabine', 'Alexandra', 'Kerstin', 'Christina', 'Maike Meike', 'Bianca Bianka', 'Silvia Sylvia', 'Anke', 'Christiane', 'Maren', 'Michaela', 'Christine', 'Diana', 'Martina', 'Simone', 'Christian', 'Markus Marcus', 'Stefan Stephan', 'Andreas', 'Thomas', 'Sven', 'Torsten', 'Matthias', 'Frank', 'Martin', 'Jens', 'Oliver', 'Andre André', 'Jörg', 'Maik Meik Mike', 'Sascha', 'Robert', 'Karsten Carsten', 'Björn', 'Holger', 'René', 'Christoph', 'Dirk', 'Timo', 'Martha Marta', 'Erna', 'Gertrud', 'Margarethe Margarete', 'Elsa', 'Berta Bertha', 'Hedwig', 'Minna', 'Helene', 'Wilhelmine', 'Dora', 'Auguste', 'Alma', 'Meta Metha', 'Wilhelm', 'Hans', 'Friedrich', 'Hermann', 'Otto', 'Heinrich', 'Ernst', 'Walter Walther', 'Adolf', 'Willi Willy', 'Franz', 'Fritz', 'Johann', 'Rudolf Rudolph', 'Alfred', 'August', 'Georg', 'Gustav', 'Artur Arthur', 'Josef Joseph', 'Helga', 'Karin', 'Ingrid', 'Renate', 'Ursula', 'Christa', 'Gisela', 'Elke', 'Inge', 'Erika', 'Christel', 'Waltraud', 'Gerda', 'Hannelore', 'Ilse', 'Marianne', 'Hildegard', 'Irmgard', 'Ingeborg', 'Rita', 'Edith', 'Rosemarie', 'Brigitte', 'Margrit', 'Marion', 'Lieselotte', 'Margot', 'Jutta', 'Anneliese', 'Sigrid', 'Elfriede', 'Bärbel', 'Peter', 'Klaus Claus', 'Günter Günther', 'Jürgen', 'Horst', 'Dieter', 'Uwe', 'Manfred', 'Werner', 'Helmut Helmuth', 'Heinz', 'Gerhard', 'Wolfgang', 'Rolf', 'Herbert', 'Gerd Gert', 'Harald', 'Joachim', 'Bernd', 'Curt Kurt', 'Siegfried', 'Rainer Reiner', 'Ulrich', 'Lothar', 'Monika', 'Heike', 'Barbara', 'Heidi', 'Heidemarie', 'Ute', 'Marlies', 'Antje', 'Gudrun', 'Birgit', 'Volker', 'Norbert', 'Hartmut', 'Wilfried', 'Reinhardt', 'Dorothea', 'Hertha Herta', 'Elise', 'Else', 'Käthe', 'Olga', 'Albert', 'Erich', 'Ludwig', 'Anne', 'Jessika Jessica', 'Miriam', 'Tina', 'Annett Anett', 'Patrick', 'Kai Kay', ' Abbas', 'Abbe', 'Abegglen', 'Abel', 'Abeln', 'Abend', 'Abendroth', 'Aber', 'Abitz', 'Abke', 'Abt', 'Abts', 'Ach', 'Achatz', 'Achen', 'Achenbach', 'Achorn', 'Achter', 'Achterhof', 'Achziger', 'Ackermann', 'Ackert', 'Ackmann', 'Acord', 'Adami', 'Adamy', 'Addleman', 'Adel', 'Adelberg', 'Adelmann', 'Adelsberger', 'Adelsperger', 'Adelstein', 'Ader', 'Aderman', 'Aders', 'Adler', 'Afflerbach', 'Affolter', 'Agler', 'Agricola', 'Ahl', 'Ahlbrecht', 'Ahles', 'Ahlf', 'Ahlgrim', 'Ahmann', 'Ahn', 'Ahr', 'Airey', 'Albach', 'Alberding', 'Alberg', 'Albitz', 'Albracht', 'Albrecht', 'Albus', 'Aldag', 'Alder', 'Aldinger', 'Alexy', 'Alger', 'Alig', 'Alleman', 'Allenbach', 'Allendorf', 'Aller', 'Allers', 'Allert', 'Allgaier', 'Allgeier', 'Allgeyer', 'Alling', 'Allinger', 'Allman', 'Alman', 'Almendinger', 'Almer', 'Alpert', 'Alpha', 'Alsdorf', 'Alsman', 'Alspach', 'Alt', 'Altemose', 'Altenbach', 'Altenburg', 'Altenburger', 'Altendorf', 'Altenhofen', 'Altepeter', 'Alter', 'Altergott', 'Baab', 'Baack', 'Baade', 'Baal', 'Baalman', 'Baar', 'Baars', 'Baas', 'Baasch', 'Baase', 'Baatz', 'Baba', 'Babayan', 'Babb', 'Babbit', 'Babbitt', 'Babbs', 'Babcock', 'Babe', 'Babel', 'Baber', 'Babers', 'Babey', 'Babiak', 'Babiarz', 'Babic', 'Babich', 'Babicz', 'Babik', 'Babin', 'Babine', 'Babineau', 'Babineaux', 'Babinec', 'Babington', 'Babino', 'Babinski', 'Babish', 'Babka', 'Bable', 'Babler', 'Babson', 'Babst', 'Babu', 'Babula', 'Babyak', 'Baca', 'Bacak', 'Bacallao', 'Bacani', 'Bacca', 'Baccam', 'Baccari', 'Baccaro', 'Bacchi', 'Bacchus', 'Bacci', 'Bacco', 'Baccus', 'Bach', 'Bacha', 'Bachand', 'Bachar', 'Bacharach', 'Bache', 'Bachelder', 'Bacheller', 'Bachelor', 'Bacher', 'Bachert', 'Bachhuber', 'Bachicha', 'Bachler', 'Bachman', 'Bachmann', 'Bachmeier', 'Bachner', 'Bacho', 'Bachrach', 'Bachtel', 'Bachtell', 'Bachus', 'Bacigalupi', 'Bacigalupo', 'Bacik', 'Bacino', 'Back', 'Backe', 'Backen', 'Backer', 'Backes', 'Backhaus', 'Backhus', 'Backlund', 'Backman', 'Backs', 'Backstrom', 'Backus', 'Bacon', ];
	    $last_name = ['Haöns', 'Aachen', 'Albrechtö', 'Altdörfer', 'Gertrüd', 'Arndt', 'Ernstö', 'Barlach', 'Günther', 'Behnisch', 'Peter', 'Behrens', 'Sibylle', 'Bergemann', 'Jöseph', 'Beüys', 'Hermann', 'Biöw', 'Elisabeth', 'Böhm', 'Gottfried', 'Arnoö', 'Breker', 'Lövis', 'Corinth', 'Lucas', 'Cranach', 'Yitzhak', 'Danziger', 'Ottö', 'Dix', 'Dürer', 'Egön', 'Eiermann', 'Max', 'Carl', 'Eytel', 'Caspar', 'David', 'Friedrich', 'Dörte', 'Gatermann', 'Willi', 'Glasauer', 'Walter', 'Gropius', 'George', 'Grosz', 'Johann', 'Gottlieb', 'Hantzsch', 'Hannah', 'Höch', 'Holbein', 'Jörg', 'Immendorff', 'Helmut', 'Jahn', 'Horst', 'Janssen', 'Ulli', 'Kampelmann', 'Anselm', 'Kiefer', 'Martin', 'Kippenberger', 'Ludwig', 'Kirchner', 'Leo', 'Klenze', 'Kollhoff', 'Käthe', 'Kollwitz', 'Christian', 'Lemmerz', 'Liebermann', 'Markus', 'Lüpertz', 'August', 'Macke', 'Harro', 'Magnussen', 'Franz', 'Marc', 'Mies', 'van', 'der', 'Rohe', 'Paulä', 'Modersohn-Becker', 'Georg', 'Muche', 'Newton', 'Frei', 'Pechstein', 'Sigmar', 'Polke', 'Gerhard', 'Richter', 'Julius', 'Runge', 'Karl', 'Schinkel', 'Oskar', 'Schlemmer', 'Eberhard', 'Schlotter', 'Schmidt-Rottluff', 'Kurt', 'Schwitters', 'Fritz', 'Schumacher', 'Slevogt', 'Spitzweg', 'Birgit', 'Stauch', 'Stoltenberg', 'Stück', 'Yigäl', 'Tumarkin', 'Wolf', 'Vostell', 'Berthä', 'Wehnert-Beckmann', 'Emilie', 'Winkelmänn', 'Theo', 'Bamberger', 'John', 'Jacob', 'Bausch', 'Bayer', 'Paul', 'Beiersdorf', 'Melitta', 'Bentz', 'Benz', 'Maximilian', 'Delphinius', 'Berlitz', 'Bertelsmann', 'Adam', 'Birkenstock', 'Borsig', 'Robert', 'Bosch', 'Hugo', 'Boss', 'Braun', 'Adolphus', 'Busch', 'Adolph', 'Coors', 'Daimler', 'Adolf', 'Dassler', 'Rudolf', 'Adelbert', 'Delbrück', 'Guido', 'Henckel', 'Donnersmarck', 'Engelhorn', 'Kaspar', 'Faber', 'Fielmann', 'Eduard', 'Fresenius', 'Jakob', 'Fugger', 'Marcus', 'Goldman', 'Grundig', 'Richard', 'Hellmann', 'Henkel', 'Henckels', 'Horch', 'Rudolph', 'Karstadt', 'Keil', 'Kellner', 'Krupp', 'Henry', 'Lehman', 'Linde', 'Lomb', 'Oscar', 'Ferdinand', 'Mayer', 'Mendelssohn', 'Merck', 'Georg(e)', 'Heinrich', 'Meyerfreund', 'Miele', 'Frederick', 'Miller', 'Josef', 'Neckermann', 'Oetker', 'Opel', 'Salomon', 'Oppenheim', 'Ernest', 'Oppenheimer', 'Werner', 'Porsche', 'Quandt', 'Rapp', 'Emil', 'Rathenau', 'Reuter', 'Riegel', 'Nathan', 'Rothschild', 'Schering', 'Anton', 'Schlecker', 'Schmidt', 'Wilhelm', 'Schmidt-Ruthenbeck', 'Sennheiser', 'Siemens', 'Staedtler', 'Steinway', 'Stinnes', 'Storck-Oberwelland', 'Ströher', 'Thieme', 'Thyssen', 'Tietz', 'Leopöld', 'Ullstein', 'Moses', 'Warburg', 'Gersön', 'Siegmund', 'Bartholömeus', 'Welser', 'Wertheim', 'Zeiss', 'Zeppelin', ];
	     
	    $first_name =  Pms_CommonData::aesEncryptMultiple($first_name);
	    $last_name =  Pms_CommonData::aesEncryptMultiple($last_name);
	    foreach ($assignid as $row) {
	        $q = Doctrine_Query::create()
	        ->update('PatientMaster')
	        //     ->set('first_name', "AES_ENCRYPT(?,?)" , [random_name::first(), 'encrypt'])
	        ->set('first_name', "?" , $first_name[rand(0, count($first_name)-1)])
	        //     ->set('last_name', "AES_ENCRYPT(?,?)" , [random_name::last(), 'encrypt'])
	        ->set('last_name', "?" , $last_name[rand(0, count($last_name)-1)])
	        ->set('isadminvisible', "?" , 1)
	        ->where("id = ?", $row['id'])
	        ->execute()
	        ;
	    
	    }
	     
	    die("renamed + isadminvisible :".count($assignid));
	}
	
	public function modulestoclientsettingsAction(){
			exit;
		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();
	
		// get all clients
		$clt = Doctrine_Query::create()
		->select("*")
		->from('Client')
// 		->whereIn('id', array(1, 32))
		;
		//->whereNotIn('id', array(1, 121));
		//->orderBy("id ASC");
		$cltarray = $clt->fetchArray();
		//var_dump($cltarray);exit;
		foreach($cltarray as  $cl_data)
		{
			$modules =  new Modules();
			$clientModulesset = $modules->get_client_modules($cl_data['id']);
			
			$clientModules = array();
			foreach($clientModulesset as $km=>$vm)
			{
				if(!is_array($vm))
				{
					$clientModules[] = $km;
				}
			}
			//var_dump($clientModules);exit;
			$client_teammeeting_settings = $cl_data['teammeeting_settings'];
		//var_dump($cl_data);exit;	
			$array_modules2clientsettings = array('92', '93', '110', '160', '161', '171', '172');
	
			foreach($array_modules2clientsettings as $kcl=>$vcl)
			{
				if(in_array($vcl, $clientModules))
				{
					switch ($vcl)
					{
						case '92':
							$client_teammeeting_settings['addextrapat'] = 'yes';
							break;
						case '93':
							$client_teammeeting_settings['onlyactivepat'] = 'yes';
							break;
						case '110':
							$client_teammeeting_settings['statusdrop'] = 'yes';
							break;
						case '160':
							$client_teammeeting_settings['xt'] = 'yes';
							break;
						case '161':
							$client_teammeeting_settings['showtodos'] = 'yes';
							break;
						case '171':
							$client_teammeeting_settings['tbyusers'] = 'yes';
							break;
						case '172':
							$client_teammeeting_settings['lastfcomment'] = 'yes';
							break;
						default:
							break;
					}
				}
			}
			//var_dump($client_teammeeting_settings); exit;
			$cust = Doctrine::getTable('Client')->find($cl_data['id']);
			$cust->teammeeting_settings = $client_teammeeting_settings;
			$cust->save();
		}
		exit;
	}
	
	
	public function ispc2221Action()
	{
	    exit;
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    set_time_limit(6 * 60);
	    
	    Doctrine_Core::getTable('PatientContactphone')->hasOne('ContactPersonMaster', array(
	        'local' => 'table_id',
	        'foreign' => 'id'
	    ));
	    
	    $q = Doctrine_Query::create()
	    // 	    $q = Doctrine_Core::getTable('PatientMaster')->createQuery('p')
	    ->select("pc.*, pc.id, pc.ipid,
	        
	        aes_decrypt(cpm.cnt_street1, '" . Zend_Registry::get('salt') . "') as cnt_street1,
	        aes_decrypt(cpm.cnt_zip, '" . Zend_Registry::get('salt') . "') as cnt_zip,
	        aes_decrypt(cpm.cnt_city, '" . Zend_Registry::get('salt') . "') as cnt_city,
	        aes_decrypt(cpm.cnt_comment, '" . Zend_Registry::get('salt') . "') as cnt_comment
	        "
	    )
	    ->from('PatientContactphone pc')
	    ->leftJoin('pc.ContactPersonMaster cpm ON (pc.ipid = cpm.ipid AND pc.table_id = cpm.id)')
	    ->where("pc.parent_table = 'ContactPersonMaster'")
	    ->andWhere("pc.extra IS NULL")
	    ->limit("1000")
	    // 	    ->execute(null, Doctrine_Core::HYDRATE_ON_DEMAND)
	    ->execute()
	    ;
	    
	    foreach ($q->getIterator() as $row) {
	        
	        $contact_extra = serialize([
	            'comment' =>  ! empty($row->cnt_comment) ? $row->cnt_comment : null,
	            'street' =>  ! empty($row->cnt_street1) ? $row->cnt_street1 : null,
	            'city' =>  ! empty($row->cnt_city) ? $row->cnt_city : null,
	            'zip' =>  ! empty($row->cnt_zip) ? $row->cnt_zip : null,
	        ]);
	        
	        $row['extra'] =  $contact_extra;
	        
// 	        dd($row->toArray());
	        
	    }
	    
	    $q->save();
	    
	    die("modified: " . $q->count());
	    
	}
	
	
	/**
	 * /misc/bugfix_ftp_queue
	 * 
	 * link by primaryKey a a record in ftp_queue to a record in a file table 
	 * 
	 * @return boolean
	 */
	public function bugfixftpqueueAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    set_time_limit(10 * 60);

	    $step = $this->getRequest()->getQuery('step', 1);
	    
	    Doctrine_Core::getTable('FtpPutQueue')->getRecordListener()->get('FtpPutQueue2RecordListener')->setOption('disabled', true);
	    Doctrine_Core::getTable('FtpPutQueue')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
	    
	    
	    echo ("IF YOU GET <b>Fatal error:</b> Allowed memory size ... it means you the script exausted it,,, so please F5<hr><br/>");
	    
	    

	    if ($step == '1' || $step == 'NinjaTurtle') 
	    {
    	        
    	    /**
    	     * fix FtpPutQueue <-> PatientFileUpload 
    	     */
	        echo ("<h1>Step {$step} => fix FtpPutQueue <-> PatientFileUpload</h1><br/>");
	        
    	    $fpq_count = Doctrine_Query::create()
    	    ->select('count(*) as cnt')
    	    ->from('FtpPutQueue')
    	    ->where("parent_table IS NULL")
    	    ->andWhere('bugfix IS NULL')
    	    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    	    $total_ftp =  $fpq_count['cnt'];
    	    
    	    $rows_per_step = 3000;
    	    
    	    $total_step = ceil($total_ftp / $rows_per_step);
    	   
    	    echo ("Step {$step} will have {$total_step} microsteps * {$rows_per_step} rows <br/>" . PHP_EOL);
    	    
    	    
    	    for ($i=0;  $i <= $total_step; $i++) {
    	        
    	        echo ("{$i} of {$total_step}, ");
    	        
    	        $this->_ob_flush();
    	        
    	        set_time_limit(2 * 60);
    	        
    	        $fpq_files = Doctrine_Query::create()
    	        ->select('id, file_name')
    	        ->from('FtpPutQueue')
    	        ->where("parent_table IS NULL")
    	        ->andWhere('parent_table_id IS NULL')
    	        ->andWhere('bugfix IS NULL')
    	        ->limit($rows_per_step)
//     	        ->offset($i * $rows_per_step)
    	        ->execute();
    	        
    	        foreach ($fpq_files->getIterator() as $row) {
    	            $row->bugfix = 1;
    	        }
    	            
    	        $file_name_arr = array_column($fpq_files->toArray(), 'file_name');
    	        
    	        if ( ! empty($file_name_arr)) {
    
    	            $q_pfu = Doctrine_Query::create()
        	        ->select('id, file_name')
        	        ->from('PatientFileUpload')
        	        ->whereIn('file_name' , array_values($file_name_arr))
        	        ;
    	            $pfu = $q_pfu->fetchArray();
    	            $q_pfu->free();
    	            
        	        
    	            if ( ! empty($pfu)) {
        	            foreach ($fpq_files->getIterator() as $row) {
        	                $found = array_filter($pfu, function($item) use ($row) {
        	                    return $item['file_name'] == $row->file_name;
        	                }); 
    
        	                if ( ! empty($found)) {
        	                    $found = reset($found);
        	                    $row->parent_table = "PatientFileUpload";
        	                    $row->parent_table_id = $found['id'];
        	                }
        	            }
    	            }
    	        }
                $fpq_files->save();    	        
    	        $fpq_files->free(true);
    	        unset($fpq_files);
    	    }
    	    
    	    //reset the temprorary field `bugfix` to null
    	    Doctrine_Query::create()
    	    ->update('FtpPutQueue')
    	    ->set('bugfix', 'NULL')
    	    ->execute();
    	    echo ("bugfix field has been reset to NULL <br/>");
    	    
    	    
    	    if ($step != 'NinjaTurtle') {
    	        die("<hr/>Step {$step} Finisded, <a href='?step=". ($step+1) . "' target='_self' >click here for [STEP ". ($step+1) . "]</a>");
    	    }
	    }
	    
	
	    
	    
	    if ($step == '2' || $step == 'NinjaTurtle') 
	    {
    	    /**
    	     * fix FtpPutQueue <-> ClientFileUpload
    	     */
	        echo ("<h1>Step {$step} => fix FtpPutQueue <-> ClientFileUpload</h1><br/>");
	       
	        
    	    $fpq_count = Doctrine_Query::create()
    	    ->select('count(*) as cnt')
    	    ->from('FtpPutQueue')
    	    ->where("parent_table IS NULL")
    	    ->andWhere('bugfix IS NULL')
    	    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    	    $total_ftp =  $fpq_count['cnt'];
    	     
    	    $rows_per_step = 2000;
    	    $total_step = ceil($total_ftp / $rows_per_step);
    	     
    	    echo ("Step {$step} will have {$total_step} microsteps * {$rows_per_step} rows <br/>" . PHP_EOL);
    	    	
    	     
    	    for ($i=0;  $i <= $total_step; $i++) {
    	         
    	        echo ("{$i} of {$total_step}, ");
    	         
    	        $this->_ob_flush();
    	         
    	        set_time_limit(2 * 60);
    	        
    	        $fpq_files = Doctrine_Query::create()
    	        ->select('id, file_name')
    	        ->from('FtpPutQueue')
    	        ->where("parent_table IS NULL")
    	        ->andWhere('bugfix IS NULL')
    	        ->limit($rows_per_step)
    	        ->execute();
    	        
    	        foreach ($fpq_files->getIterator() as $row) {
    	            $row->bugfix = 1;
    	        }
    	        
    	        $file_name_arr = array_column($fpq_files->toArray(), 'file_name');
    	        
    	        if ( ! empty($file_name_arr)) {
    	    
    	            $cfu = Doctrine_Query::create()
    	            ->select('id, file_name')
    	            ->from('ClientFileUpload')
    	            ->whereIn('file_name' , array_values($file_name_arr))
    	            ->fetchArray();
    	            
    	            if ( ! empty($cfu)) {
    	                foreach ($fpq_files->getIterator() as $row) {
    	                    $found = array_filter($cfu, function($item) use ($row) {
    	                        return $item['file_name'] == $row->file_name;
    	                    });
    	    
                            if ( ! empty($found)) {
                                $found = reset($found);
                                $row->parent_table = "ClientFileUpload";
                                $row->parent_table_id = $found['id'];
                            }
    	                }
    	            }
    	             
    	        }
                $fpq_files->save();
    	        $fpq_files->free(true);
    	        unset($fpq_files);
    	    }
    	    
    	    
    	    //reset the temprorary field `bugfix` to null
    	    Doctrine_Query::create()
    	    ->update('FtpPutQueue')
    	    ->set('bugfix', 'NULL')
    	    ->execute();
    	    echo ("bugfix field has been reset to NULL <br/>");
    	    
    	    if ($step != 'NinjaTurtle') {
    	        die("<hr/>Step {$step} Finisded, <a href='?step=". ($step+1) . "' target='_self' >click here for [STEP ". ($step+1) . "]</a>");
    	    }
	    }
	    
	    
	    
	    if ($step == '3' || $step == 'NinjaTurtle') 
	    {       
    	    /**
    	     * fix FtpPutQueue <-> MemberFiles
    	     */
	        echo ("<h1>Step {$step} => fix FtpPutQueue <-> MemberFiles</h1><br/>");
	        
    	    $fpq_count = Doctrine_Query::create()
    	    ->select('count(*) as cnt')
    	    ->from('FtpPutQueue')
    	    ->where("parent_table IS NULL")
    	    ->andWhere('bugfix IS NULL')
    	    ->andWhere("controllername = 'member'")
    	    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    	    $total_ftp =  $fpq_count['cnt'];
    	    
    	    
    	    $rows_per_step = 100;
    	    $total_step = ceil($total_ftp / $rows_per_step);
    	    
    	    echo ("Step {$step} will have {$total_step} microsteps * {$rows_per_step} rows <br/>" . PHP_EOL);
    	    	
    	    for ($i=0;  $i <= $total_step; $i++) {

    	        echo ("{$i} of {$total_step}, ");
    	        
    	        $this->_ob_flush();
    	        
    	        set_time_limit(2 * 60);
    	        
    	        $fpq_files = Doctrine_Query::create()
    	        ->select('id, ftp_path')
    	        ->from('FtpPutQueue')
    	        ->where("parent_table IS NULL")
    	        ->andWhere('bugfix IS NULL')
    	        ->andWhere("controllername = 'member'")
    	        ->limit($rows_per_step)
    	        ->execute();
    	         
    	        foreach ($fpq_files->getIterator() as $row) {
    	            $row->bugfix = 1;
    	        }
    	        
    	        $file_name_arr = array_column($fpq_files->toArray(), 'ftp_path');
    	        array_walk($file_name_arr, function(&$item) {
    	            $item = end(explode("/",$item)) . "$";
    	        });
    	    
    	        if ( ! empty($file_name_arr)) {
    	            
    	            $cfu = Doctrine_Query::create()
    	            ->select('*')
    	            ->from('MemberFiles')
    	            ->where('ftp_path REGEXP ?' , implode('|', $file_name_arr))
    	            ->fetchArray()
    	            ;
    	            
    	            if ( ! empty($cfu)) {
    	                
    	                foreach ($fpq_files->getIterator() as $row) {
    	                    
    	                    $found = array_filter($cfu, function($item) use ($row) {
    	                        
//     	                        $match_file = end(explode("/", $row->ftp_path)) . "$";    
//     	                        return (preg_match('/'.$match_file.'/', $item['ftp_path'], $matched) == 1);
    	                        
    	                        $match_file = end(explode("/", $row->ftp_path));
    	                        return strpos($item['ftp_path'], $match_file);
    	                    });
    	                    
	                        if ( ! empty($found)) {
	                            //dd($found, $row->toArray());
	                            $found = reset($found);
	                            $row->parent_table = "MemberFiles";
	                            $row->parent_table_id = $found['id'];
	                        }
    	                }
    	            }    	    
    	        }
    	        
    	        $fpq_files->save();
    	        $fpq_files->free(true);
    	        unset($fpq_files);
    	    }
    	    
    	    
    	    //reset the temprorary field `bugfix` to null
    	    Doctrine_Query::create()
    	    ->update('FtpPutQueue')
    	    ->set('bugfix', 'NULL')
    	    ->execute();
    	    echo ("bugfix field has been reset to NULL <br/>");
    	    
    	    if ($step != 'NinjaTurtle') {
    	        die("<hr/>Step {$step} Finisded, <a href='?step=". ($step+1) . "' target='_self' >click here for [STEP ". ($step+1) . "]</a>");
    	    }
	    }
	    
	    
	    
	    /**
	     * MembersSepaXml cannot be linked this way ... 
	     * the id of ftp_queue must be saved in MembersSepaXml table ...
	     * cause this is backwads.. FtpQueue->hasMany(MembersSepaXml)
	     */
	    
	    /**
	     * fix FtpPutQueue <-> MembersSepaXml
	     */
	    /*
	    if ($step == '4' || $step == 'NinjaTurtle') 
	    {
    	    
	        echo ("<h1>Step {$step} => fix FtpPutQueue <-> MembersSepaXml</h1><br/>");
	        
	        
    	    $fpq_count = Doctrine_Query::create()
    	    ->select('count(*) as cnt')
    	    ->from('FtpPutQueue')
    	    ->where("parent_table IS NULL")
    	    ->andWhere('bugfix IS NULL')
    	    ->andWhere("actionname = 'generate_sepa_xml_batch'")
    	    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    	    $total_ftp =  $fpq_count['cnt'];
    	     
    	    $rows_per_step = 10;
    	    $total_step = ceil($total_ftp / $rows_per_step);
    	    
    	    echo ("Step {$step} will have {$total_step} microsteps * {$rows_per_step} rows <br/>" . PHP_EOL);
    	    	
    	     
    	    for ($i=0;  $i <= $total_step; $i++) {

    	        echo ("{$i} of {$total_step}, ");
    	        
    	        $this->_ob_flush();
    	        
    	        set_time_limit(2 * 60);
    	         
    	        $fpq_files = Doctrine_Query::create()
    	        ->select('id, ftp_path')
    	        ->from('FtpPutQueue')
    	        ->where("parent_table IS NULL")
    	        ->andWhere('bugfix IS NULL')
    	        ->andWhere("actionname = 'generate_sepa_xml_batch'")
    	        ->limit($rows_per_step)
    	        ->execute();
    	         
    	        foreach ($fpq_files->getIterator() as $row) {
    	            $row->bugfix = 1;
    	        }
    	        
    	        $file_name_arr = array_column($fpq_files->toArray(), 'ftp_path');
    	        array_walk($file_name_arr, function(&$item) {
    	            $item = end(explode("/",$item)) . "$";
    	        });

    	        
    	        if ( ! empty($file_name_arr)) {

    	            $cfu = Doctrine_Query::create()
    	            ->select('*')
    	            ->from('MembersSepaXml')
    	            ->where('ftp_file REGEXP ?' , implode('|', $file_name_arr))    	             
    	            ->fetchArray();
    	            
    	            
    	            dd($cfu);
    	             
    	            if ( ! empty($cfu)) {
    	                foreach ($fpq_files->getIterator() as $row) {
    	                    $found = array_filter($cfu, function($item) use ($row) {
    	                        return $item['file_name'] == $row->file_name;
    	                    });
    	    
    	                        if ( ! empty($found)) {
    	                            $found = reset($found);
    	                            $row->parent_table = "MembersSepaXml";
    	                            $row->parent_table_id = $found['id'];
    	                        }
    	                }
    	            }
    	             
    	            $fpq_files->save();
    	        }
    	    }
    	    
    	    
    	    //reset the temprorary field `bugfix` to null
    	    Doctrine_Query::create()
    	    ->update('FtpPutQueue')
    	    ->set('bugfix', 'NULL')
    	    ->execute();
    	    echo ("bugfix field has been reset to NULL <br/>");
    	     
    	    
    	    if ($step != 'NinjaTurtle') {
    	        die("<hr/>Step {$step} Finisded, <a href='?step=NinjaTurtle' target='_self' >click here ONLY ONCE for [NinjaTurtle] TO RE-RUN all as a fail-safe</a>");
    	    }
	    }
	    */
	    
	    
	    $fpq_count = Doctrine_Query::create()
	    ->select('count(*) as cnt')
	    ->from('FtpPutQueue')
	    ->where("parent_table IS NULL")
	    ->andWhere("foster_file = 'NO'")
	    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
	    $total_ftp =  $fpq_count['cnt'];
	    	
	    echo ("<hr/>ftp files that have NOT been linked via uniqueKey :  $total_ftp <br/>");
	    if ($step != 'NinjaTurtle') {
	        die("<hr/>Step {$step} Finisded, <a href='?step=NinjaTurtle' target='_self' >click here ONLY ONCE for [NinjaTurtle] TO RE-RUN all as a fail-safe</a>");
	    }
	    
	    die("The NinjaTurtle has crossed the Finish line");	    
	}
	
	
	
	
	public function checkadmAction(){
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	
	    set_time_limit(6 * 60);
	   
	    
	    $readm_q = Doctrine_Query::create()
	    ->select('ipid')
	    ->from('PatientReadmission')
//	    ->where('date(create_date) > "2018-08-01" ');
 	    ->where('date(create_date) > "2017-12-14" ');
	    $readm_arr = $readm_q->fetchArray();
	     
	    $ipids = array();
	    foreach($readm_arr as $k=>$pa){
	        $ipids[] = $pa['ipid'];
	    }
	    $ipids = array_values(array_unique($ipids));
	     
// 	    echo "<pre>";
// 	    print_r($ipids);
// 	    exit; 
	     
	    $readm_qall = Doctrine_Query::create()
	    ->select('id,date,date_type,ipid, create_date')
	    ->from('PatientReadmission')
	    ->whereIn('ipid', $ipids)
	    ->orderBy('date ASC');
	    $readm_all_arr = $readm_qall->fetchArray();
	     
	    
	    $pat_details = Doctrine_Query::create()
	    ->select('ipid, admission_date')
	    ->from('PatientMaster')
	    ->whereIn('ipid', $ipids);
	    $pat_details_res = $pat_details->fetchArray();
	    
	     
	    foreach($pat_details_res as $k=>$pdata){
	        $admission_pm[$pdata['ipid']] = $pdata['admission_date'];
	    }
	    
// 	    echo "<pre>";
// 	    print_r($admission_pm);
// 	    exit; 
	     
 
	     
	    foreach($readm_all_arr as $rk=>$rd){
	           $all_readm[$rd['ipid']][] = $rd;
	         
	        $per_ipid[$rd['ipid']]['dates'][] = $rd['date'].' - '.$rd['date_type'].' -------------- '.$rd['create_date'].'______________________'.$admission_pm[$rd['ipid']];
	        if($rd['date'] == $admission_pm[$rd['ipid']] &&   date("H:i:s",strtotime($admission_pm[$rd['ipid']])) == "00:00:00"){
	            $per_ipid[$rd['ipid']]['dates_wrong'][] = $rd['date'];
	        }
	        
	        if(
	            $rd['date_type'] =="1" 
	            && date("Y-m-d",strtotime($rd['date'])) == date('Y-m-d',strtotime($admission_pm[$rd['ipid']])) &&  $rd['date'] != $admission_pm[$rd['ipid']] &&   date("H:i:s",strtotime($admission_pm[$rd['ipid']])) == "00:00:00"){
	            $per_ipid[$rd['ipid']]['dates_wrong_XXXX'][] = $rd['date'];
	            
	            $per_ipid[$rd['ipid']]['qruery'][] = ' UPDATE patient_master SET admission_date = "'. $rd['date'].'"  WHERE ipid ="'.$rd['ipid'].'"  AND admission_date = "'.$admission_pm[$rd['ipid']].'"; ';
	            
	        }
	        
	        
	        if($rd['date_type'] =="1"){
	            $per_ipid[$rd['ipid']]['adm'] = $per_ipid[$rd['ipid']]['adm']+1;
	            $per_ipid[$rd['ipid']]['adm_real'] = $per_ipid[$rd['ipid']]['adm_real']+1;
	            if(in_array(date('Y-m-d',strtotime($rd['date'])),$adm_dates[$rd['ipid']] ) ){
    	            $per_ipid[$rd['ipid']]['adm'] = $per_ipid[$rd['ipid']]['adm']+1;
	            }
	            $adm_dates[$rd['ipid']][] = date('Y-m-d',strtotime($rd['date']));
	        }
	        if($rd['date_type'] =="2"){
	            $per_ipid[$rd['ipid']]['dis'] = $per_ipid[$rd['ipid']]['dis']+1;
	        }
	    }
	    
	    $update_single="";
	    $update_single_ipids = "";
	    foreach($ipids as $ipid){
	        if(count($all_readm[$ipid]) == 1){
	            if($all_readm[$ipid][0]['date_type'] == "1" && $all_readm[$ipid][0]['date'] != $admission_pm[$ipid] ){
	                $per_ipid[$ipid]['STANDBY - SINGLE ISSUE'] = "1";
	                $update_single .= '<br/> UPDATE patient_master SET admission_date = "'.$all_readm[$ipid][0]['date'].'"  WHERE ipid ="'.$ipid.'"  AND admission_date = "'.$admission_pm[$ipid].'"; ';
	                $update_single_ipids  .= '"'.$ipid.'", ';
	            }
	        }
	        
	        if(count($all_readm[$ipid]) == 3 && date("H:i:s",strtotime($admission_pm[$ipid])) == "00:00:00"
				&& $per_ipid[$ipid]['adm_real']  == 2 
	            && !empty($per_ipid[$ipid]['dates_wrong'])
	            ){
	            $adm2dis1[$ipid] = $per_ipid[$ipid];
	        }
	    }   
//	    echo "<pre>";
//	    print_R($adm2dis1);
	    
//	    echo "<pre>";
//	    print_R($update_single_pm);
	     
	    
//	    exit;
	    
	    $standby_issues_str="";
	    $discharge_issues_str = "";
	    foreach($per_ipid as $pipdi =>$pda){
	        if(($pda['adm'] - $pda['dis']) > 1 ){
	             
	            $per_ipid[$pipdi]['ISSUE'] = "1";
	            
	            if( ! isset( $pda['dis'])){
	               $per_ipid[$pipdi]['STANDBY -ISSUE'] = "1";
	               $standby_issues[] = $pipdi;
	               $standby_issues_str .= '"'.$pipdi.'", ';
	            } else{
	               $discharge_issues[] = $pipdi;
	               $discharge_issues_str .= '"'.$pipdi.'", ';
	            }
	            
	        } else{
	            
	            //unset($per_ipid[$pipdi]);
	        }
	    }

	    echo "<pre>";
	   // print_r("discharge: \n ");
	   // echo $discharge_issues_str;
	  //  print_r($discharge_issues);
	    
	    
// 	    if(!empty($standby_issues)){
	        
// 	        $pat_details = Doctrine_Query::create()
// 	        ->select('ipid, admission_date')
// 	        ->from('PatientMaster')
// 	        ->whereIn('ipid', $standby_issues);
// 	        $pat_details_res = $pat_details->fetchArray();
// 	         dd($pat_details_res);
	        
	        
// 	         $readm_qall = Doctrine_Query::create()
// 	         ->select('id,date,date_type,ipid, create_date')
// 	         ->from('PatientReadmission')
// 	         ->whereIn('ipid', $standby_issues)
// 	         ->orderBy('date ASC');
// 	         $readm_all_arr = $readm_qall->fetchArray();
	        
	        
// 	    }
	    
	    
	    
	    
	    echo "<pre>";
	    print_r($per_ipid);
	    exit;
	    echo "<br/>";
	    echo $update_single;
	    
	    echo "<br/>";
	    echo $update_single_ipids;

	    exit;
	     
	}
	
	
	public function getipiddatadAction(){
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $_REQUEST['ipid'] = "955d824b5e7b204328f93b7f10c9b3e545c49e26";
// 	    $_REQUEST['ipid'] = "11b7199255015504d388471b943a7d5f1347d2e0";
// 	    $_REQUEST['ipid'] = "bb753db699aa472379a3400f03c7c41509ba5eaf";
	    
	    if(!empty($_REQUEST['ipid'])){
	        $ipid = $_REQUEST['ipid'];
	        
	        
	        $patientmaster = new PatientMaster();
	        $patient_falls_master = $patientmaster->patient_falls($ipid);
	        $details['falls']  = $patient_falls_master['falls'];

	        $details['master'] = Doctrine_Query::create()
	        ->select('id,ipid,admission_date, isstandby,isstandbydelete,isdischarged')
	        ->from('PatientMaster')
	        ->WhereIn('ipid', array($ipid))
	        ->fetchArray();
	        
	        
	        $details['discharge'] = Doctrine_Query::create()
	        ->select('id,ipid,discharge_date,isdelete')
	        ->from('PatientDischarge')
	        ->WhereIn('ipid', array($ipid))
	        ->fetchArray();
	        
	        $details['readmission'] = Doctrine_Query::create()
	        ->select('id,ipid,date,date_type')
	        ->from('PatientReadmission')
	        ->WhereIn('ipid', array($ipid))
	        ->orderBy('date ASC')
	        ->fetchArray();
	        
	        $details['standbyDetails'] = Doctrine_Query::create()
	        ->select('id,ipid,date,date_type,comment')
	        ->from('PatientStandbyDetails')
	        ->WhereIn('ipid', array($ipid))
	        ->orderBy('date ASC')
	        ->fetchArray();
	        
	        
	        
	        dd($details);
	    } else{
	        echo "no ipid";
	        exit;
	    }
	}
	
	
	
	/**
	 * Function created to export clients that use a specific module
	 * 29.08.2018
	 */
	
	public function exportclients2moduleAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if ($logininfo->usertype != 'SA') {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if (empty($_REQUEST['module'])) {
            
            die(" no module ");
        }
        
        $module_id = $_REQUEST['module'];
        
        $module_data = Doctrine_Query::create()
            ->select("*")
            ->from('Modules')
            ->where('isdelete=0')
            ->andWhere('id =?', $module_id)
            ->fetchOne();
        
        if (! empty($module_data)) {
            echo $module_data['module'];
            echo "<br/>";
            echo "<br/>";
            echo "_______________________________________";
            echo "<br/>";
        }
        
        $module_arr = Modules::clients2modules(array(
            $module_id
        ));
        
        if (empty($module_arr)) {
            
            die(" no clients for module " . $module_id);
        }
        
        $clist = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
            ->from('Client')
            ->where('isdelete=0')
            ->andWhereIn('id', $module_arr)
            ->fetchArray();
        
        if (empty($clist)) {
            die(" no clients info");
        }
        
        $list = array();
        foreach ($clist as $k => $cli_data) {
            $list[] = $cli_data['id'] . '         ' . $cli_data['client_name'];
        }
        
        echo implode('<br/>', $list);
        
        exit();
    }
    
	public function exportclients2modulenewAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if ($logininfo->usertype != 'SA') {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if (empty($_REQUEST['module'])) {
            
            die(" no module ");
        }
        
        $module_id = $_REQUEST['module'];
        
        $module_data = Doctrine_Query::create()
            ->select("*")
            ->from('Modules')
            ->where('isdelete=0')
            ->andWhere('id =?', $module_id)
            ->fetchOne();
        
        if (! empty($module_data)) {
            echo $module_data['module'];
            echo "<br/>";
            echo "<br/>";
            echo "_______________________________________";
            echo "<br/>";
        }
        
        $module_arr = Modules::clients2modules(array(
            $module_id
        ));
        
        if (empty($module_arr)) {
            
            die(" no clients for module " . $module_id);
        }
        
        $clist = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
            ->from('Client')
            ->where('isdelete=0')
            ->andWhereIn('id', $module_arr)
            ->fetchArray();
        
        if (empty($clist)) {
            die(" no clients info");
        }
        
        $list = array();
        foreach ($clist as $k => $cli_data) {
            $list[] = $cli_data['id'] . ' | ' . $cli_data['client_name'];
        }
        
        echo implode('<br/>', $list);
        
        exit();
    }
    
    public function notfallmessagesclientsettingsAction(){
    	//exit;
    	$this->_helper->layout->setLayout('layout');
    	$this->_helper->viewRenderer->setNoRender();
    
    	// get all clients
    	$clt = Doctrine_Query::create()
    	->select("*")
    	->from('Client')
    	// 		->whereIn('id', array(1, 121))
    	;
    	//->whereNotIn('id', array(1, 121));
    	//->orderBy("id ASC");
    	$cltarray = $clt->fetchArray();
    	//var_dump($cltarray);exit;
    	foreach($cltarray as  $cl_data)
    	{
    		$clid_arr[] = $cl_data['id'];
    	}
    	$master_group_array = array("9");
    	$client_groups = new Usergroup();
    	$client_groups_array = $client_groups->getUserGroups($master_group_array, $clid_arr);
    	foreach($client_groups_array as $kclg=>$vclg)
    	{
    		$client_notfall_messages_settings[$vclg['clientid']][] = $vclg['id'];
    	}
    	//var_dump($client_notfall_messages_settings);exit;
    	 
    	foreach($cltarray as  $cl_data)
    	{
    		$modules =  new Modules();
    		$clientModulesset = $modules->get_client_modules($cl_data['id']);    		
    		
    		if($clientModulesset['123'])
    		{
    			$cust = Doctrine::getTable('Client')->find($cl_data['id']);
	    		$cust->notfall_messages_settings = $client_notfall_messages_settings[$cl_data['id']];
	    		$cust->save();
    		}
    	}
    	exit;
    }
	
    //ISPC_vs_Clinic_models
    public function ispcvsclinicmodelsAction()
    {
        exit;
         
        $dir = "/home/www/ispc2017_08/application/models2/nico/versorger_basetabs";
        $cdir = scandir($dir);
         
        $WE_extra = $NICO_extra = [];
         
        foreach ($cdir as $key => $value)
        {
            if(is_file($dir . DIRECTORY_SEPARATOR . $value) && preg_match('/Base(.*).php/', $value, $matches))
            {
                $class = $matches[1];
                $baseclass = "Base" .$class;
    
                $classEntity= new $class();
                $columnsISPC = $classEntity->getTable()->getColumns();
    
    
                $class2 = $class . "2";
                $baseclass2 = "Base" . $class . "2";
    
                //
                $file = file_get_contents($dir . DIRECTORY_SEPARATOR . $value);
                $file = str_replace(["<?php", "<?" , "?>"], "", $file);
                $file = str_replace($baseclass, $baseclass2, $file);
                 
                eval($file);
                 
                eval ("class {$class2} extends {$baseclass2} {}");
                 
                $classEntity2= new $class2();
                $columnsCLINIC = $classEntity2->getTable()->getColumns();
                 
                unset ($columnsISPC['isdelete'], $columnsCLINIC['isdelete']);
// 	           dd($class, $columnsISPC, $columnsCLINIC);
    
                $ispc_extra = [];
                foreach ($columnsISPC as $name => $options) {
                    if (! isset($columnsCLINIC[$name])) {
                        $ispc_extra[$name] = $options;
            
                    } else {
            
                        $diff = array_diff($options, $columnsCLINIC[$name]);
            
                            if ( ! empty($diff)) {
                                $ispc_extra[$name] = $options;
                            }
                        // 	                   if(array_di$options['type'] || $options['length'])
                    }
                }
    
                if ( ! empty($ispc_extra)) {
                    $WE_extra[$class] = $ispc_extra;
                    // 	               ddecho("ispc_extra" , $class, $ispc_extra);
                }
    
    
                $clinic_extra = [];
                foreach ($columnsCLINIC as $name => $options) {
                    if (! isset($columnsISPC[$name])) {
                        $clinic_extra[$name] = $options;
            
                    } else {
            
                        $diff = array_diff($options, $columnsISPC[$name]);
                
                        if ( ! empty($diff)) {
                            $clinic_extra[$name] = $options;
                        }
                        // 	                   if(array_di$options['type'] || $options['length'])
                    }
                }
    
                if ( ! empty($clinic_extra)) {
                    $NICO_extra[$class] = $clinic_extra;
                        // 	               ddecho("clinic_extra" , $class, $clinic_extra);
                }
    
    
            }
        }
             
        dd("I HAVE EXCLUDED `isdelete` field, cause we use SoftDeleteListener, so different types ",
        "ISPC EXTRA FIELDS OR different options:" , $WE_extra,
        "Nico's CLINIC EXTRA FIELDS OR different options:" ,$NICO_extra);
    }

	public function fixindropAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $indrop_tables = [
	        "Churches" => "PatientChurches",
	        "Homecare" => "PatientHomecare",
	        "Hospiceassociation" => "PatientHospiceassociation",
	        "Pflegedienstes" => "PatientPflegedienste",
	        "Pharmacy" => "PatientPharmacy",
	        "Physiotherapists" => "PatientPhysiotherapist",
// 	        "Specialists" => "PatientSpecialists", //this uses a different connection, it was started below..,, but on test it has 0 problems.. so no update for it
	        "Suppliers" => "PatientSuppliers",
	        "Supplies" => "PatientSupplies",
	        "Voluntaryworkers" => "PatientVoluntaryworkers",
	    ];
	    
	    
	    $conn = Doctrine_Manager::getInstance()->getConnection('SYSDAT');
	    $connIDAT = Doctrine_Manager::getInstance()->getConnection('IDAT');
	    
	    $step = $this->getRequest()->getQuery('step', 1);
	    
	    
	    $cnt = 0;
	    foreach ($indrop_tables as $relationModel => $patientModel) 
	    {
	        $cnt++;
	        
	        if ($cnt != $step ) {
	            continue;
	        }
	        
	        $localField = null;
	        $foreignField = null;
	        
	        if ($relation = Doctrine_Core::getTable($patientModel)->getRelation($relationModel, false)) {
	            $relation = $relation->toArray();
	            $localField = $relation['local'];
	            $foreignField = $relation['foreign'];
	        }
	        
	        
	        if (empty($localField) || empty($foreignField)) {
	            echo "<h1>cannot link {$relationModel} => {$patientModel}</h1>";
	            continue;
	        }
	        

	        $patient_table = Doctrine_Core::getTable($patientModel)->getTableName();
	        $relation_table = Doctrine_Core::getTable($relationModel)->getTableName();
	        
	        
	        $sql_bkp = "CREATE TABLE IF NOT EXISTS `fixindrop_{$patient_table}_10_2018` LIKE `{$patient_table}`";
	        $conn->execute($sql_bkp);
	            
	        
    	    $sql_txt = "SELECT ot.*, pt.id as pt_id , pt.ipid as pt_ipid
    	        FROM `%s` AS pt 
    	        INNER JOIN `%s` ot 
    	           ON ot.`%s` = pt.`%s` AND ot.indrop=0  
    	        WHERE pt.isdelete=0;";	 

    	    
//     	   echo(sprintf($sql_txt, $patient_table, $relation_table, $foreignField, $localField)) . "<br><br>";
//     	    continue;
    	    $collection = $conn->execute(sprintf($sql_txt, $patient_table, $relation_table, $foreignField, $localField));
    	    
    	    
    	    $cnt_fixed = 0;
    	    
    	    while ($row = $collection->fetch(PDO::FETCH_ASSOC)) 
    	    {
    	        
    	        $row_orig = $row;
    	        if ( ! isset($row['indrop']) || $row['indrop'] != 0) {
    	            continue;
    	        }
    	       
    	        
//     	        if (isset($row['change_user'])) {
//     	            $row['change_user'] = -1;
//     	        }
    	        
    	        $row['indrop'] = 1;
    	        
    	        $ot_id = $row['id'];
    	        $pt_id = $row['pt_id'];
    	        $pt_ipid = $row['pt_ipid'];
    	        
    	        unset($row['pt_id'], $row['pt_ipid']);
    	        $sql_bkp = "INSERT IGNORE INTO `%s` ( `id` , `ipid` , `{$localField}`) VALUES ( ?, ?, ? )";
    	        $stmt = $conn->execute(sprintf($sql_bkp, "fixindrop_{$patient_table}_10_2018") , [$pt_id, $pt_ipid, $row['id']]);
    	        $stmt->closeCursor();
    	        
    	        
    	        unset($row['id']); //Remove key from array
    	        
    	        $sql_insert = "INSERT INTO `%s` ";
    	        $sql_insert .= " ( " .implode(", ",array_keys($row)).") ";
//     	        $sql_insert .= " VALUES ('".implode("', '",array_values($row)). "')";
    	        $sql_insert .= " VALUES (". str_repeat ('?, ',  count ($row) - 1) . '?' . ")";
    	        
    	        
    	        
    	        
    	        
    	        $stmt = $conn->execute(sprintf($sql_insert, $relation_table) , array_values($row));
    	        
    	        $lastInsertId = $conn->lastInsertId();
    	        
    	        if ( ! ((int)$lastInsertId > 0)) {
    	            
    	            echo "<h2>VERY WRONG on {$relation_table} {$ot_id} => {$patient_table} {$pt_id} , no update, just continue</h2><br>";
    	            continue;
    	            
    	            
    	        }
    	        
    	        
    	        $sql_update = "UPDATE `%s` SET `%s` = :new_relation_id WHERE `id`= :pt_id";
    	        $params = array(
    	            'pt_id'            => $pt_id,
    	            'new_relation_id'  => $lastInsertId,
    	        );
    	        $stmt = $conn->execute(sprintf($sql_update, $patient_table, $localField), $params);
    	        
    	        
    	        $sql_update_contact_phone = "UPDATE `patient_contactphone` SET `table_id` = :new_relation_id
    	        WHERE `ipid`= :pt_ipid
    	        AND `parent_table` = :parent_table
    	        AND `table_id` = :table_id
    	        AND `isdelete` = 0
    	        ";
    	        $params = array(
    	            'pt_ipid'          => $pt_ipid,
    	            'new_relation_id'  => $lastInsertId,
    	            'parent_table'     => $relationModel,
    	            'table_id'         => $ot_id,
    	        );
    	        $stmt = $connIDAT->execute($sql_update_contact_phone, $params);
    	        
    	        
    	        $stmt->closeCursor();
    	        $cnt_fixed++;
    	         
    	        $row = null;
    	        unset($row);
    	        
//     	        echo "<pre>";
//     	        print_r($relationModel);
//     	        print_r($row_orig);
    	        
//     	        die("test");
    	    }
    	    
    	    $collection = null;
    	    unset($collection);
    	    
    	    echo "<h1>indrop for {$relationModel} => {$patientModel}  - updated {$cnt_fixed}</h1><br>";
    	    
     
	        if ($step != 'NinjaTurtle' ) {
	            die("Step {$step} Finisded, <a href='?step=" . ++$step . "' target='_self' >click here for [STEP {$step}]  </a>");
	        }
	         
    	   
	    }
	    
	    
	     
	    
// 	    $sql_txt = "SELECT * FROM `patient_specialists`";
// 	    $collection = $connIDAT->execute($sql_txt);
// 	    $cnt_fixed = 0;
// 	    $coll =  $collection->fetchAll(PDO::FETCH_ASSOC);
	    
// 	    $sp_ids = array_column($coll, 'sp_id') ;
	    
	    
// 	    $sp_ids = array_values(array_unique($sp_ids));
	    
// 	    $sql_txt = "SELECT * FROM `specialists` WHERE `id` IN (". str_repeat ('?, ',  count ($sp_ids) - 1) . '?' .") AND `isdelete` = 0 AND `indrop` = 0";
// 	    $collection2 = $conn->execute($sql_txt, $sp_ids);
// 	    $coll2 =  $collection2->fetchAll(PDO::FETCH_ASSOC);
	     
	    
	    
	    
	    die("The NinjaTurtle has crossed the Finish line");
	    exit;
	}
	
	
	
     /**
     * Ancuta 21.11.2018
     * 
     * TODO-1890 
     * Update patient germination 
     * 
     */
	public function updatepatientgerminationAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    
	    // select germination
	    
	    $q = Doctrine_Query::create()
	    ->select('*')
	    ->from('PatientGermination')
	    ->where('isdelete = 0 ')
// 	    ->andWhere('ipid="4dea5dea486d0dc5e814fdb015cd3c643bfc3219" ')
	    ->fetchArray();
	    echo "<pre>";
	    print_r($q);

	    
	    foreach($q as $k=>$pg){
	        
	        $iso_cbox = 0;
	        if($pg['germination_cbox'] == "1"){
	            $iso_cbox = 1;
	        }
	        
	        $germination_cbox = 0;
	        if(strlen($pg['germination_text']) > 0 ){
	            $germination_cbox = 1;
	        }

	        $update = Doctrine_Query::create()
	        ->update('PatientGermination')
	        ->set('iso_cbox', '?', $iso_cbox) // set new field with the value from existing checkbox - to show icon
	        ->set('germination_cbox', '?', $germination_cbox)
			->set('change_user', "?", $pg['change_user'])
			->set('change_date',"?", $pg['change_date'])
	        ->where('id = ?',$pg['id'] )
	        ->andWhere('ipid = ?',$pg['ipid'] )
	        ->execute();
	        
	    }
	    
	    
	    $qs = Doctrine_Query::create()
	    ->select('*')
	    ->from('PatientGermination')
	    ->where('isdelete = 0 ')
// 	    ->andWhere('ipid="4dea5dea486d0dc5e814fdb015cd3c643bfc3219" ')
	    ->fetchArray();
	    
	    print_r("\n---------------------------------------------------------------------- \n");
	    print_r("FINAL");
	    print_r("\n---------------------------------------------------------------------- \n");
	    
	    print_r($q);
	    echo "DONE"; exit;
	}
	
	
	
	
	
	
	public function automodelAction()
	{
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	     
	    
	    $table =  $this->getRequest()->getParam('table', null);
	    $task =  $this->getRequest()->getParam('task', null);
	    
	    if (empty($table)) {
            echo "<h2>Usage: ?table=mynew_table</h2>";
	        exit();
	    }
	    
	    $_options = array('packagesPrefix'        =>  'Package',
	        'packagesPath'          =>  '',
	        'packagesFolderName'    =>  'packages',
	        'suffix'                =>  '.php',
	        'generateBaseClasses'   =>  true,
	        'generateTableClasses'  =>  true, // we donit use tables yet... the entity model is 'fauth' to be the table and not the record that it extends
	        'generateAccessors'     =>  true,
	        'buildAccessors'     =>  true,
	    
	         
	        'indexes' => true, //this is a reminder, not an option... to know i changed listIndexes and listTriggers
	    
	        'baseClassPrefix'       =>  'Base',
	        'baseClassesDirectory'  =>  'generated',
	        'baseClassName'         =>  'Pms_Doctrine_Record', // 'Doctrine_Record' is extended since 11.01.2018
	        'baseTableClassName'         =>  'Pms_Doctrine_Table', // 'Doctrine_Table' is extended since 13.09.2018
	    
	        'pearStyle'             => true,
	        'classPrefix'           => '',
	        'classPrefixFiles'      => false,
	        'phpDocPackage'		=> isset($task) ? 'ISPC'.' ['.$task.']' : 'ISPC' ,
	        'phpDocSubpackage'	=> 'Application ('. date("Y-m-d").')',
	        'phpDocName'		=> 'Ancuta',
	        'phpDocEmail'		=> 'office@originalware.com',
	        //hardcoded by @claudiu @date 20.11.2017
	        'only_this_tables' => array($table),
	    
	    );
	    
	    
	    
	    Doctrine_Core::generateModelsFromDb(
	        '/home/www/ISPCGIT/application/models2',
	        array('MDAT', 'SYSDAT', 'IDAT'),
	        $_options
	    );
	    
	    
	    die("The NinjaTurtle has crossed the Finish line");
	    exit;
	     
	}
	
	
	public function hl7ft1Action()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    
		$message = new Net_HL7_Message(file_get_contents('/home/www/ispc2017_08/public/socketserver_essen/testmessages/tmsg_4.txt'));
		
		$PV1 = $message->getSegmentsByName('PV1')[0];
		$PV1_1 = clone $PV1;
		$PV1_2 = clone $PV1;
		
		
		$PV1_1->setSetID(1);
		$PV1_2->setSetID(2);
				
		$MSH = $message->getSegmentsByName('MSH')[0];
		$MSH->setSendingApplication('ISPC');
		$MSH->setSendingFacility('DevTeam');
		$MSH->setDateTimeOfMessage();
		
		
		$message = new Net_HL7_Message();
		$message->addSegment($MSH);
		$message->addSegment($PV1_1);
		$message->addSegment($PV1_2);
		
		
		$currency = ['EUR', 'USD', 'GBP'];
		
		for($i=0;$i<10;$i++) {
		
			$rand = mt_rand(10*1000000,1000*1000000)/1000000;
			
		    $segmentFT1 = new Net_HL7_Segments_FT1();
		    $segmentFT1->setTransactionAmountUnit( [[$rand, $currency[array_rand($currency)]]]);
		    $segmentFT1->setTransactionType('CD');
		    // 		$segmentFT1->setTransactionCode('DKGNT2004');
		    
		    $message->addSegment($segmentFT1);
		    
		}
		
		
		dd("sss", $message->toString(1), get_class_methods($PV1), $PV1);
		
		
		$message->toString(1);
		
		dd("sss", $message->toString(1));
	}
	
	/**
	 * @cla on 15.02.2019
	 * populate the newly created PatientVisitnumberTable
	 */
	public function hl7pv1fixAction()
	{
	    $this->deny_messages_older_than = 60 * 60 * 24 * 80; // 80days
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    
	    $allfalls = [];
	    
	    
	    $allreceived = Doctrine_Query::create()
	    ->select('hl7_mp.*, hl7_mr.*')
	    ->from('Hl7MessagesProcessed hl7_mp')
	    ->leftJoin("hl7_mp.Hl7MessagesReceived hl7_mr")
	    ->where("ipid IS NOT NULL")
	    ->fetchArray()
	    ;
	    
	    
	    $fallsperq = [];
	    
	    $msgTypes_with_PV1 = [];
	    $msgTypes_all = [];
	    
	    foreach ($allreceived as $ht7Text) {
	        $message = new Net_HL7_Message(trim($ht7Text['Hl7MessagesReceived']['message']));
	        $PV1 = $message->getSegmentsByName('PV1')[0];
	        $PID = $message->getSegmentsByName('PID')[0];
	        
	        
	        
	        $msgType = $message->getSegmentFieldAsString(0, 9); // Example: "ADT^A08"
	        
	        $msgDate = $message->getSegmentFieldAsString(0, 7);
	        
	        $zbe = $message->getSegmentsByName("ZBE");
	        
	        if (sizeof($zbe) > 0) {
	        
	            $zbe = $zbe[0];
	            $zdate = $zbe->getField(2);
	            $zbe = $zbe->getField(4);
	        
	            if ($zdate && strlen($zdate) == 14) {
	                if (abs(strtotime($msgDate) - strtotime($zdate)) > $this->deny_messages_older_than) {
	                    //dd('Message seems to be outdated. Message not processed.');
	                    //echo $ipid . $message->toString(1) . "<br><hr><br>";
	                    $msgType = "ERROR";
	                    continue;
	                }
                }
            }
	        
	        
	        
// 	        if ( ! empty($PV1) && $msgType == "ADT^A04") {
	        if ( ! empty($PV1)) {
	           
	            $obj = PatientVisitnumberTable::getInstance()->findOrCreateOneBy(
	                //search fields
	                ['ipid', 'visit_number', 'admit_date'],
	                
	                //search values
	                [$ht7Text['ipid'], $PV1->getVisitNumber()->id[0], $PV1->getAdmitDate()],
	                 
	                [
	                    //data
    	                "ipid"                 => $ht7Text['ipid'],
    	                "visit_number"         => $PV1->getVisitNumber()->id[0],
    	                "admit_date"           => $PV1->getAdmitDate(),
    	                "messages_received_id" => $ht7Text['messages_received_ID'],
	                ]
                );
	        }
	        
	    }
	    
	    die("The NinjaTurtle has crossed the Finish line, check idat.patient_visitnumber");
	     
	    
	}
	
	
	/**
	 * @cla on 13.02.2019
	 * populate the newly created property $ipid on Model Hl7MessagesProcessed
	 * fix missing FamilyDoctor
	 */
	public function hl7addipidfixAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    
	    $this->deny_messages_older_than = 60 * 60 * 24 * 80; // 80days
	    
	    $clientarray = Doctrine::getTable('Client')->findOneBy('id', 252, Doctrine_Core::HYDRATE_ARRAY);
	    
	    /*stop PatientUpdateListener*/	     
	    $listenerChain = Doctrine_Core::getTable('PatientMaster')->getRecordListener();
        $i = 0;
        while ($listener = $listenerChain->get($i))
        {
            $i++;
            if ($listener instanceof PatientUpdateListener) {
                $listener->setOption('disabled', true);
            }
        }
        
	    function setFamilydoctor($pv1, $old_familydoc_id = null)
	    {
	    
	        $fdocId = null; //return
	        
	        $allreadySaved = false;
	        
	        // BEGIN Family Doctor
	        $fdocfield = $pv1->getField(9);
	    
	        if ($fdocfield[1]) {
	            $lastname = $fdocfield[1];
	            if (is_array($lastname)) {
	                $lastname = $lastname[0];
	            }
	            $firstname = (string)$fdocfield[2];
	            $street = (string)$fdocfield[9];
	            $zip = (string)$fdocfield[7];
	            $city = (string)$fdocfield[8];
	            $phone = (string)$fdocfield[20];
	            $fax = (string)$fdocfield[21];
	    
	            $fdocfind = Doctrine_Query::create()
	            ->select('*')
	            ->from('FamilyDoctor indexBy id')
	            ->where("first_name = ?", $firstname)
	            ->andwhere("last_name = ?", $lastname)
	            ->andwhere("zip = ?", $zip)
	            ->andWhere("clientid = ?", 252)
	            ->andWhere("isdelete = 0")
	            ->fetchArray()
	            ;
	            
	            
	            if ( ! empty($fdocfind)) {
	                
	                if ( ! empty($old_familydoc_id) && isset($fdocfind[$old_familydoc_id])) {
	                    
	                    //this is allready set as patient;s doctor, nothing more to do, just bypass newdoctor
	                    $allreadySaved =  true;
	                    
	                } else {
	                    
                        //check if any of this doctrors is from indrop=0... we don't add doctors from other patients
	                    
	                    $fdocfind = array_filter($fdocfind, function($i) {return $i['indrop'] == 0;});
	                    $fdocfind = reset($fdocfind);
	                    
	                    if ( ! empty($fdocfind) && $fdocfind['id']) 
	                    {
	                        //duplicate this doctor
	                        unset($fdocfind['id'], $fdocfind['create_date'], $fdocfind['change_date'], $fdocfind['create_user'], $fdocfind['change_user']);
	                        $fdocfind['indrop'] = 1;
	                        
	                        $newFdoc = Doctrine_Core::getTable('FamilyDoctor')->create();
	                        $newFdoc->fromArray($fdocfind);
	                        $newFdoc->save();
	                        
	                        if ($newFdoc) {
	                            $fdocId = $newFdoc->id;
	                        }
	                    }
	                } 
	                
	            } 
	            
	            if (empty($fdocId) && ! $allreadySaved) {
	                
	                //create as new doctor
	                $fdoc = new FamilyDoctor();
	                $fdoc->clientid = 252;
	                $fdoc->first_name = $firstname;
	                $fdoc->last_name = $lastname;
	                $fdoc->street1 = $street;
	                $fdoc->title = $fdocfield[6];
	                $fdoc->zip = $zip;
	                $fdoc->city = $city;
	                $fdoc->indrop = 1;
	                $fdoc->phone_practice = $phone[0];
	                $fdoc->fax = $fax[0];
	                $fdoc->doctornumber = $fdocfield[0];
	                $fdoc->save();
	                
	                $fdocId = $fdoc->id;
	                
	                //send message that a new familydoc was added ?
	            }
	        }
	        
	        return $fdocId;
	    }
	    
	    $allreceived = Doctrine_Query::create()
	    ->select('*, AES_DECRYPT(hl7_mr.message, :local_key) as message')
	    ->from('Hl7MessagesProcessed hl7_mp')
	    ->leftJoin("hl7_mp.Hl7MessagesReceived hl7_mr")
	    ->where("ipid IS NULL")
	    ->fetchArray(array(
	        "local_key" => Zend_Registry::get('salt')  
	    ))
	    ;
	    
	    $wasEmptyFamilydoc = [];
	    
	    foreach ($allreceived as $received) {
	        
	        $message = new Net_HL7_Message(trim($received['message']));
	        
	        $pid = $message->getSegmentsByName("PID");
	        
	        if (sizeof($pid) != 1) {
	            continue;
	        }
	        $pid = $pid[0];
	        $epid_chars = $clientarray['epid_chars'];
	        $epid_no = $pid->getField(3);
	        $epid_no = $epid_no[0];
	        $epid_no = $epid_no; // ??
	        $epid = $epid_chars . $epid_no;
	        
	        $ipid = EpidIpidMapping::getIpidFromEpidAndClientid($epid, 252);
	        
	        if ( ! empty($ipid)) {
	            
	           $q = Doctrine_Query::create()
	           ->update('Hl7MessagesProcessed')
	           ->set('ipid', '?' , $ipid)
	           ->where('messages_processed_ID = ? ', $received['messages_processed_ID'])
	           ->execute()
	           ;	           
	        
	           
	           $msgType = $message->getSegmentFieldAsString(0, 9); // Example: "ADT^A08"
	           
	           $msgDate = $message->getSegmentFieldAsString(0, 7);
	           
	           $zbe = $message->getSegmentsByName("ZBE");
	           
	           if (sizeof($zbe) > 0) {
	           
	               $zbe = $zbe[0];
	               $zdate = $zbe->getField(2);
	               $zbe = $zbe->getField(4);
	           
	               if ($zdate && strlen($zdate) == 14) {
	                   if (abs(strtotime($msgDate) - strtotime($zdate)) > $this->deny_messages_older_than) {
	                       //dd('Message seems to be outdated. Message not processed.');
	                       //echo $ipid . $message->toString(1) . "<br><hr><br>";
	                       $msgType = "ERROR";
	                   }
	               }
	           }
	        
    	        
    
    	        $pv1 = $message->getSegmentsByName("PV1");
    	        if (sizeof($pv1) > 0) {
    	            $pv1 = $pv1[0];
    	        } else {
    	            throw new Exception("PV1-Segement not found");
    	        }
    	        
    	        switch($msgType) {
    	            case "ADT^A02":
        	        case "ADT^A04":
        	        case "ADT^A02":
        	        case "ADT^A06":
        	        case "ADT^A07":
    	            case "ADT^A01":
    	                
    	                if ($patientMaster = Doctrine_Core::getTable('PatientMaster')->findOneBy('ipid', $ipid)) {
        	                
    	                    if (empty($patientMaster->familydoc_id) || $wasEmptyFamilydoc[$ipid] === true) {
    	                        
    	                        $wasEmptyFamilydoc[$ipid] = true;
    	                        
            	                if ($fdocID = setFamilydoctor($pv1 , $patientMaster->familydoc_id)) {
            	                    $patientMaster->familydoc_id = $fdocID;
            	                    $patientMaster->save();
            	                    
            	                    echo ("was empty: " . $ipid . " : " . $fdocID . "<br>\n");
            	                }
    	                    }
    	                }
    	                
    	                break;
    	        }
    	        
	        
	        
	        }
	        
	        
	        
	    }
	    
	    
	    
	    die("The NinjaTurtle has crossed the Finish line, messages processed:" . count($allreceived));
	     
	}

	public function addmissionboxesclientsettingsAction(){
		//ISPC-1757
		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();
	
		// get all clients
		$clt = Doctrine_Query::create()
		->select("*")
		->from('Client')
		// 		->whereIn('id', array(1, 121))
		;
		//->whereNotIn('id', array(1, 121));
		//->orderBy("id ASC");
		$cltarray = $clt->fetchArray();
		//var_dump($cltarray);exit;
	
		foreach($cltarray as  $cl_data)
		{
			$modules =  new Modules();
			$clientModulesset = $modules->get_client_modules($cl_data['id']);
	
			//Erstkontackt durch - extra_form_id - 58
			if($clientModulesset['122'])
			{
				$data['clientid'] = $cl_data['id'];
				$data['formid'] = '58';
	
				$entity = ExtraFormsClientTable::getInstance()->createIfNotExistsOneBy(['clientid', 'formid'], [$cl_data['id'], '58'], $data);
			}
	
			//DGP block - extra_form_id - 59
			if($clientModulesset['125'])
			{
				$data['clientid'] = $cl_data['id'];
				$data['formid'] = '59';
				$entity = ExtraFormsClientTable::getInstance()->createIfNotExistsOneBy(['clientid', 'formid'], [$cl_data['id'], '59'], $data);
			}
				
			$data['clientid'] = $cl_data['id'];
			$data['formid'] = '57';
			$entity = ExtraFormsClientTable::getInstance()->createIfNotExistsOneBy(['clientid', 'formid'], [$cl_data['id'], '57'], $data);
	
		}
		exit;
	}
	
	
	public function patientactivedaysAction(){
	    $this->_helper->layout->setLayout('layout');
	    $this->_helper->viewRenderer->setNoRender();
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	
	            $sql = 'e.epid, p.ipid, e.ipid,';
	            $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
	            $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
	            $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
	            $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
	            $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
	            $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
	            $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
	            $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
	            $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
	            	
	            
	    $conditions['periods'][0]['start'] = '2018-01-01';
	    $conditions['periods'][0]['end'] = '2018-12-31';
	    $conditions['include_standby'] = false;
	    $conditions['client'] = $clientid;
	     
	    $patient_days = Pms_CommonData::patients_days($conditions,$sql);
	
	    $ipids = array_keys($patient_days);
	    
// 	    dd($patient_days);
	    $invoicesdd = Doctrine_Query::create()
	    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
	    ->from('HospizInvoices INDEXBY id' )
	    ->where("client= ?",$clientid)
	    ->andWhere('storno = 1');
	    $storno_invoices_res = $invoicesdd->fetchArray();
	    $stornos = array();
	    foreach($storno_invoices_res as $k=>$invdet){
    	    $stornos[] = $invdet['record_id'];
	    }
	    
	    $invoices = Doctrine_Query::create()
	    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
	    ->from('HospizInvoices INDEXBY id' )
	    ->where("client= ?",$clientid)
	    ->andWhereIn('ipid',$ipids)
	    ->andWhere('isdelete = 0')
	    ->andWhere('storno != 1')
        ->andWhereNotIn('status',array('1',4));
	    if(!empty($stornos)){
           $invoices->andWhereNotIn('id',$stornos);
	    }
	    
	    $invoices_res = $invoices->fetchArray();
	    $invoices_ids = array();
	    foreach($invoices_res as $inv_id=>$invoice_data){
	        $invoices_ids[] = $invoice_data['id'];
	    }
	    
	    if(!empty($invoices_ids)){
	        $hospiz_invoice_items = new HospizInvoiceItems();
    	    $invoice_items = $hospiz_invoice_items->getInvoicesItems($invoices_ids);
    	    foreach($invoice_items as $inv_ids=>$items){
    	        foreach($items as $k=>$itm_Vals){
    	            if($itm_Vals['shortcut'] == "hospiz_pv_pat"){
        	            $invoice2days[$itm_Vals['invoice']] = $itm_Vals['qty'];
    	            }
    	        }
    	        
    	        $invoices_res[$inv_ids]['items'] = $items; 
    	    }
	    }
	    $overall_billed_days = 0;
	    foreach($invoices_res as $inv_id=>$invoice_data){
	        if(!in_array($invoice_data['id'],$stornos)){
	            
//     	        $inv2patient[$invoice_data['ipid']][] = date('d.m.Y',strtotime($invoice_data['start_active'])).' - '. date('d.m.Y',strtotime($invoice_data['end_active'])).' ( '.$invoice2days[$invoice_data['id']].' )'.$invoice_data['id'];
    	        $inv2patient[$invoice_data['ipid']][] = date('d.m.Y',strtotime($invoice_data['start_active'])).' - '. date('d.m.Y',strtotime($invoice_data['end_active'])).' (Items: '.$invoice2days[$invoice_data['id']].')';
    	        $billed_days[$invoice_data['ipid']] += $invoice2days[$invoice_data['id']];
    	        $overall_billed_days += $invoice2days[$invoice_data['id']];
	        }
	    }
	    
	    
	    
	    $total = array();
	    $html="";
	    $html .= "Period:". date("d.m.Y",strtotime($conditions['periods'][0]['start'])).' - '. date("d.m.Y",strtotime($conditions['periods'][0]['end']));
	    $html .='<br/><table border="1" width="100%" cellpadding="5">';
	        
	    $html .='<tr>';
	    $html .='<th width="10%">Epid</th>';
// 	    $html .='<th>Last name</th>';
// 	    $html .='<th>First name</th>';
// 	    $html .='<th>Active periods</th>';
	    $html .='<th width="10%">Active days</th>';
	    $html .='<th width="10%">Hospital</th>';
	    $html .='<th width="10%">Hospiz</th>';
	    $html .='<th width="10%">Treatment days</th>';
	    $html .='<th width="30%">Invoices</th>';
	    $html .='<th width="20%"> Hospizbedarfssatz per patient </th>';
	    $html .='</tr>';
	    
	    foreach($patient_days as $ipid=>$pdata){
        $html .= "<tr>";            	           
        
        $html .= "<td>";            	           
        $html .= $pdata['details']['epid'];            	           
        $html .= "</td>";
        
//         $html .= "<td>";            	           
//         $html .= $pdata['details']['last_name'];            	           
//         $html .= "</td>";
        
//         $html .= "<td>";            	           
//         $html .= $pdata['details']['first_name'];            	           
//         $html .= "</td>";
         
        
//         $html .= "<td>";            	           
         
//         $pers ="";
         
//         foreach($pdata['patient_active'] as $per_id =>$details){
//             $pers .= date("d.m.Y",strtotime($details['start']));
//             $pers .= ' - ';
//             $pers .=  $details['end'] != "0000-00-00" ? date("d.m.Y",strtotime($details['end'])) : " / ";
//             $pers .= '<br/>';
//         }
//         $html .= $pers ;            	           
//         $html .= "</td>";
        
        
        
        $html .= "<td align='center'>";            	           
        $html .= $pdata['real_active_days_no'];            	           
        $html .= "</td>"; 
         
        $html .= "<td align='center'>";            	           
        $html .= $pdata['hospital']['real_days_cs_no'];            	           
        $html .= "</td>"; 
        $html .= "<td align='center'>";            	           
        $html .= $pdata['hospiz']['real_days_cs_no'];            	           
        $html .= "</td>"; 
        
        $html .= "<td align='center'>";            	           
        $html .= $pdata['treatment_days_no'];            	           
        $html .= "</td>";
         
        $html .= "<td>";            	           
        $html .= !empty($inv2patient[$ipid]) ? implode("<br/>",$inv2patient[$ipid]) : "No invoice";            	           
        $html .= "</td>";
         
        $html .= "<td>";            	           
        $html .= !empty($billed_days[$ipid]) ?  "  ".$billed_days[$ipid] : "";            	           
        $html .= "</td>";
         
        $html .= "</tr>";
        $total['hospiz_real_days_cs_no'] += $pdata['hospiz']['real_days_cs_no'];
        $total['hospital_real_days_cs_no'] += $pdata['hospital']['real_days_cs_no'];
        $total['real_active_days_no'] += $pdata['real_active_days_no'];
        $total['treatment_days_no'] += $pdata['treatment_days_no'];
        
	    }
	    
	    $html .="<tr>";
	    $html .="<th> </th>";
// 	    $html .="<th> </th>";
// 	    $html .="<th> </th>";
// 	    $html .="<th> </th>";
	    $html .="<th>".$total['real_active_days_no']."</th>";
	    $html .="<th>".$total['hospital_real_days_cs_no']."</th>";
	    $html .="<th>".$total['hospiz_real_days_cs_no']."</th>";
	    $html .="<th>".$total['treatment_days_no']."</th>";
	    $html .="<th> </th>";
	    $html .="<th>Hospizbedarfssatz: ".$overall_billed_days."</th>";
	    $html .="</tr>";
	    
	    $html .= "</table>";
	   
	
	
	
	$pdf = new Pms_PDF('L', 'mm', 'A4', true, 'UTF-8', false);
	$pdf->setDefaults(true); //defaults with header
	$pdf->setImageScale(1.6);
	$pdf->SetMargins(10, 5, 10); //reset margins
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->setHTML($html);
	$pdfname = "hospix";	
	ob_end_clean();
	ob_start();
	$pdf->toBrowser($pdfname . '.pdf', "d");

				exit;
	
	
	}
	
	

	/**
	 * @author Ancuta // Maria:: Migration ISPC to CISPC 08.08.2020
	 * Copy of pricelistnordrhein
	 * ISPC-2461
	 * 01.10.2019
	 */
	public function pricelistdemstepcareAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	
	    $p_lists = new PriceList();
	
	    	
	    if($clientid == '0')
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	    }
	    	
	    if($_REQUEST['list'])
	    {
	        //check if the list belongs to this client
	        $p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
	        	
	        if($p_lists_check)
	        {
	            $list = $_REQUEST['list'];
	        }
	        else
	        {
	            //get user out of here if the list does not belong to current client;
	            $list = false; //just to be sure
	            $this->_redirect(APP_BASE . "error/previlege");
	            exit;
	        }
	    }
	    	
	    if($this->getRequest()->isPost())
	    {
	        $has_edit_permissions = Links::checkLinkActionsPermission();
	        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	        {
	            $this->_redirect(APP_BASE . "error/previlege");
	            exit;
	        }
	        	
	        	
	        $form = new Application_Form_ClientPriceList();
	
	        if($_POST['save_prices'])
	        {
	            	
	            foreach($_POST as $key_table => $post_data)
	            {
	                if($key_table != 'save_prices')
	                {
	                    $save_function = 'save_prices_' . $key_table;
	                    	
	                    $insert = $form->$save_function($post_data, $list);
	                }
	            }
	            //avoid resubmit post
	            $this->_redirect(APP_BASE . "misc/pricelistdemstepcare?list=" . $list);
	            exit;
	        }
	    }
	    	
	
	    $invoice_type = "demstepcare_invoice";
	    $invoice_settings = new InvoiceSystem();
	    $invoice_settings_dta = new PriceDemstepcare();
	    
	    $sp_products = DemstepcareProductsTable::findPruductsByClient($clientid);
// 	    dd($sp_products);
	    
	    $data['invoice_products'] = $invoice_settings->invoice_products($invoice_type);
	    
	    foreach($sp_products as $k=>$prod_details){
	        if(in_array($prod_details['shortcut'],$data['invoice_products'])){
	            $data['products'][$prod_details['shortcut']] = $prod_details['product_name'];
	        }
	    }
	    
	    
	    $data['default_price_list'] = $invoice_settings->invoice_products_default_prices($invoice_type);
	    $data['locations']= $invoice_settings->invoice_locations_mapping($invoice_type);
	    	
	
	
	    $data['price_list'] = $invoice_settings_dta->get_prices($list, $clientid);
	
	    if( empty($data['price_list'])){
	        $data['price_list'] = $data['default_price_list'];
	    }
	    	
	    $this->view->data = $data;
	    $this->view->listid = $list;
	}
	
	/*
	 * ISPC-2748 Lore 17.11.2020
	 */
	public function clientpricelistAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    if($clientid == '0')
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	    }
	    
	    $all_clients_invoices_details = Pms_CommonData::clients_invoices_details();
	    
	    //get client allowed invoice
	    $allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
	    $allowed_invoices = ClientInvoiceMultiplePermissions::get_client_allowed_invoices($clientid);
	    $this->view->allowed_invoice = $all_clients_invoices_details[$allowed_invoice[0]]['name'];
	    
	    $allowed_invoices_array = array();
	    if(isset($_REQUEST['invoice_type']) ){
	        $allowed_invoices_array[$_REQUEST['invoice_type']] = $all_clients_invoices_details[$_REQUEST['invoice_type']]['name'];
	    } else{
    	    foreach($allowed_invoices as $inv_type=>$invt){
    	        $allowed_invoices_array[$inv_type] = $all_clients_invoices_details[$inv_type]['name'];
    	    }
	    }
	    
	    $this->view->allowed_invoices_array = $allowed_invoices_array ;
	    
	    
	    
	    $p_lists = new PriceList();
	    $p_addmission = new PriceAdmission();
	    $p_daily = new PriceDaily();
	    $p_visits = new PriceVisits();
	    $p_sh_report = new PriceShReport();
	    
	    $shortcuts = Pms_CommonData::get_prices_shortcuts();
	    
	    if($_REQUEST['list'])
	    {
	        //check if the list belongs to this client
	        $p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
	        
	        if($p_lists_check)
	        {
	            $list = $_REQUEST['list'];
	        }
	        else
	        {
	            //get user out of here if the list does not belong to current clientid;
	            $list = false; //just to be sure
	            $this->_redirect(APP_BASE . "misc/clientpricelist");
	        }
	    }
	    else
	    {
	        $list_details = $p_lists->get_last_list($clientid);
	        $list = $list_details[0]['id'];
	    }
	    
	    $this->view->listid = $list;
	    $this->view->shortcuts_admission = $shortcuts['admission'];
	    $this->view->shortcuts_daily = $shortcuts['daily'];
	    $this->view->shortcuts_visits = $shortcuts['visits'];
	    
	    if($_REQUEST['op'] == 'del' && $list)
	    {
	        $has_edit_permissions = Links::checkLinkActionsPermission();
	        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	        {
	            $this->_redirect(APP_BASE . "error/previlege");
	            exit;
	        }
	        
	        $form_list = new Application_Form_ClientPriceList();
	        $delete_list = $form_list->delete_price_list($list);
	        
	        $this->_redirect(APP_BASE . "misc/clientpricelist");
	        exit;
	    }
	    
	    if($this->getRequest()->isPost())
	    {
	        $has_edit_permissions = Links::checkLinkActionsPermission();
	        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	        {
	            $this->_redirect(APP_BASE . "error/previlege");
	            exit;
	        }
	        
	        $form = new Application_Form_ClientPriceList();
	        if($form->validate_period($_POST))
	        {
	            if($_REQUEST['op'] == 'edit' && $_POST['edit_period'] == '1')
	            {
	                $returned_list_id = $form->edit_list($_POST, $list);
	            }
	            else
	            {
	                $returned_list_id = $form->save_price_list($_POST);
	                
	                if($returned_list_id)
	                {
	                    $default_price_list = Pms_CommonData::get_default_price_shortcuts();
	                    
	                    foreach($default_price_list['performance'] as $k_price => $v_price)
	                    {
	                        $default_prices[$k_price]['price'] = $v_price;
	                    }
	                }
	            }
	            
	            $this->_redirect(APP_BASE . "misc/clientpricelist");
	            exit;
	        }
	        else
	        {
	            $form->assignErrorMessages();
	        }
	    }
	    
	    $price_admissions = $p_addmission->get_prices($list, $clientid, $this->view->shortcuts_admission);
	    $price_daily = $p_daily->get_prices($list, $clientid, $this->view->shortcuts_daily);
	    $price_visits = $p_visits->get_prices($list, $clientid, $this->view->shortcuts_visits);
	    $price_lists = $p_lists->get_lists($clientid);
	    $price_sh_report = $p_sh_report->get_prices($list, $clientid, $this->view->shortcuts_sh_report);
	    
	    $this->view->price_list = $price_lists;
	    $this->view->price_admissions = $price_admissions;
	    $this->view->price_daily = $price_daily;
	    $this->view->price_visits = $price_visits;
	    $this->view->price_sh_report = $price_shreport;
	}
	
	/*
	 * ISPC-2748 Lore 17.11.2020
	 */
	public function clientpricelistdetailsAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    if($clientid == '0')
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	    }
	    
	    //get client allowed invoice
	    $allowed_invoice = ClientInvoiceMultiplePermissions::get_client_allowed_invoices($clientid);
	    //$allowed_invoice[0]= "bw_sgbv_invoice";
	    if(isset($_REQUEST['invoice_type']) ){
	        $allowed_invoice[0] = $_REQUEST['invoice_type'];
	    } 
	    
	    
	    $all_clients_invoices_arr = Pms_CommonData::clients_invoices_details();	    
	    $all_clients_invoices_details = $all_clients_invoices_arr[$allowed_invoice[0]];
	    $this->view->allowed_invoice = $all_clients_invoices_details['name'];
	    $this->view->allowed_invoice_details = $all_clients_invoices_details;//TODO-3689 Ancuta 16.12.2020
	    
	    $invoice_type = $allowed_invoice[0];
	    
	    
	    
	    $p_lists = new PriceList();
	    
	    if($_REQUEST['list'])
	    {
	        //check if the list belongs to this client
	        $p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
	        
	        if($p_lists_check)
	        {
	            $list = $_REQUEST['list'];
	        }
	        else
	        {
	            //get user out of here if the list does not belong to current client;
	            $list = false; //just to be sure
	            $this->_redirect(APP_BASE . "error/previlege");
	            exit;
	        }
	    }
	    $this->view->listid = $list;
	    
	    $price_lists = $p_lists->get_lists($clientid);
	    
	    $shortcuts = Pms_CommonData::get_prices_shortcuts();
	    $default_price_list = Pms_CommonData::get_default_price_shortcuts();
	    $default_dta_ids = Pms_CommonData::get_he_dta_default_ids();
	    
	    
	    $p_sh_internal_user_shifts = new PriceShInternalUserShifts();
	    $internal_price_user_groups = $p_sh_internal_user_shifts->internal_price_user_groups();
	    $this->view->internal_price_user_groups = $internal_price_user_groups;
	    
	    
	    $medipumps = new Medipumps();
	    $medipumpslist = $medipumps->getMedipumps($clientid);
	    $medipump_details = array();
	    foreach($medipumpslist as $key => $med){
	        $medipump_details[$med['id']]['id'] = $med['id'];
	        $medipump_details[$med['id']]['medipump'] = $med['medipump'];
	        $medipump_details[$med['id']]['shortcut'] = $med['shortcut'];
	    }
	    $this->view->medipumps_details = $medipump_details;
	    
	    
	    $bw_location_types =  Pms_CommonData::get_default_bw_price_location_types();
	    $this->view->bw_location_types = $bw_location_types;
	    
	    $dta_locations = DtaLocations::get_client_dta_locations($clientid);
	    $this->view->dta_locations = $dta_locations;
	    
	    
	    //members_invoice
	    $client_memberships = Memberships::get_memberships($clientid);
	    $this->view->memberships = $client_memberships;
	    $membership_details = array();
	    foreach($client_memberships as $key => $med) {
	        $membership_details[$med['id']]['id'] = $med['id'];
	        $membership_details[$med['id']]['membership'] = $med['membership'];
	        $membership_details[$med['id']]['shortcut'] = $med['shortcut'];
	    }
	    $this->view->membership_details = $membership_details;
	    
	    //xbdtActions
	    $xbdtactions_m = new XbdtActions();
	    $xbdtactions_array= $xbdtactions_m->client_xbdt_actions($clientid);
	    $action_list = array();
	    foreach($xbdtactions_array as $kl=>$kaction){
	        $action_list[$kaction['id']] = $kaction;
	    }
	    $this->view->actions_list = $action_list;
	    
	    //rp_dta_invoice
	    $rp_location_types = array("5"=>"home","3"=> "pflege", "2"=> "hospiz");
	    $rp_sapv_types = array("be","beko","tv","vv");
	    $this->view->rp_location_types = $rp_location_types;
	    $this->view->rp_sapv_types = $rp_sapv_types;
	    
	    //rlp_invoice_2018
	    $default_price_list['rlp'] = RlpInvoices::rlp_products_default_prices();
	    $rlp_products = RlpInvoices::rlp_products();
	    $this->view->rlp_products = $rlp_products;
	    $rlp_locations = RlpInvoices::rlp_locations();
	    $this->view->rlp_locations = $rlp_locations;
	    $this->view->client_rlp_products = RlpProductsTable::find_client_products($clientid);
	    

	    $invoice_settings = new InvoiceSystem();
	    //demstepcare_invoice
	    $shortcuts['demstepcare_invoice'] = $invoice_settings->invoice_products("demstepcare_invoice");
	    $default_price_list['demstepcare_invoice'] = $invoice_settings->invoice_products_default_prices("demstepcare_invoice");
	    $demstepcare_locations = $invoice_settings->invoice_locations_mapping("demstepcare_invoice");
	    $this->view->demstepcare_locations = $demstepcare_locations;
	    $dsc_products = DemstepcareProductsTable::findPruductsByClient($clientid);
	    $dsc_prod = array();
	    foreach($dsc_products as $k => $dsc_details){
	        if(in_array($dsc_details['shortcut'],$shortcuts['demstepcare_invoice'])){
	            $dsc_prod[$dsc_details['shortcut']] = $dsc_details['product_name'];
	        }
	    }
	    $this->view->demstepcare_products = $dsc_prod;
	    
	    //bre_kinder_invoice
	    $default_price_list['bre_kinder_invoice'] = $invoice_settings->invoice_products_default_prices("bre_kinder_invoice");
	    $bre_kinder_locations = $invoice_settings->invoice_locations_mapping("bre_kinder_invoice");
	    $this->view->bre_kinder_locations = $bre_kinder_locations;
	    $bre_kinder_products = $invoice_settings->invoice_products("bre_kinder_invoice");
	    $this->view->bre_kinder_products = $bre_kinder_products;
	    
	    //nr_invoice_2018
	    $default_price_list['nr_invoice'] = $invoice_settings->invoice_products_default_prices("nr_invoice");
	    $nr_invoice_locations = $invoice_settings->invoice_locations_mapping("nr_invoice");
	    $this->view->nr_invoice_locations = $nr_invoice_locations;
	    $nr_invoice_products = $invoice_settings->invoice_products("nr_invoice");
	    $this->view->nr_invoice_products = $nr_invoice_products;
	    
	    //form_blocks
	    $blocks_settings = new FormBlocksSettings();
	    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);
	    $period = array();
	    
	    if($price_lists[$list]['start'] != "0000-00-00 00:00:00") {
	        $period['start'] = date("Y-m-d",strtotime($price_lists[$list]['start']));
	    }
	    if($price_lists[$list]['end'] != "0000-00-00 00:00:00") {
	        $period['end'] = date("Y-m-d",strtotime($price_lists[$list]['end']));
	    }
	    
	    $blocks_settings_array_pr = $blocks_settings->get_blocks_settings_pr($clientid, $period);
	    $settings_array = array();
	    $settings_array_pr = array();
	    foreach($blocks_settings_array as $key => $value) {
	        $settings_array[$value['block']][] = $value;
	    }
	    foreach($blocks_settings_array_pr as $key => $value) {
	        $settings_array_pr[$value['block']][] = $value;
	    }
	    $blocks_master = array('ebm', 'ebmii', 'goa', 'goaii');
	    
	    $form_blocks_details = array();
	    $form_blocks_details_pr = array();
	    foreach($blocks_master as $block) {
	        $form_blocks_details[$block] = $settings_array[$block];
	        $form_blocks_details_pr[$block] = $settings_array_pr[$block];
	    }
	    $this->view->blocks_settings = $form_blocks_details_pr;
	    //.
	    
	    $bayern_invoice = new BayernInvoiceSettings();
	    $bayern_invoice_settings_arr = $bayern_invoice->get_list_invoice_settings($list, $clientid);
	    
	    
	    if($this->getRequest()->isPost())
	    {
	        $has_edit_permissions = Links::checkLinkActionsPermission();
	        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	        {
	            $this->_redirect(APP_BASE . "error/previlege");
	            exit;
	        }
	        
	        if($_POST['bayern_options'])
	        {
	            $bayern_invoice_settings = new Application_Form_BayernInvoiceSettings();
	            $insert = $bayern_invoice_settings->insert_data($_POST['bayern_options'], $list, $clientid);
	            unset($_POST['bayern_options']);
	        }
	        
	        $form = new Application_Form_ClientPriceList();
	        
	        if($_POST['save_prices'])
	        {
	            
	            foreach($_POST as $key_table => $post_data)
	            {
	                if($key_table != 'save_prices')
	                {
	                    $save_function = 'save_prices_' . $key_table;
	                    
	                    $insert = $form->$save_function($post_data, $list);
	                }
	            }
	            //avoid resubmit post
	            $this->_redirect(APP_BASE . "misc/clientpricelistdetails?invoice_type=".$invoice_type."&list=" . $list);
	            exit;
	        }
	    }
	    

	    
	    $p_array_list = array();
	    foreach($all_clients_invoices_arr as $key_ci => $val_ci){
	        foreach($val_ci['pricelist_used'] as $key_pu => $val_pu){
	           
	            if(!empty($val_pu)){
	                $p_pricelist_used = '$p_'.$val_pu;
	                
	                if(!in_array($p_pricelist_used, $p_array_list)){
	                    
	                    $p_array_list[] = '$p_'.$val_pu;
	                    
	                    if($val_pu == 'sh' || $val_pu == 'sh_report'){
	                        $this->view->shortcuts_sh = $shortcuts['shanlage14'];
	                        $this->view->shortcuts_sh_report = $shortcuts['shanlage14_report'];
	                        $this->view->shortcuts_sh_shifts_internal = $shortcuts['sh_shifts_internal'];
	                    }
	                    else {
	                        $this->view->{shortcuts_.$val_pu} = $shortcuts[$val_pu];
	                    }

	                    
	                    
	                    $model = $val_ci['price_model'][$key_pu];
	                    
	                    if($p_pricelist_used == '$p_medipumps'){
                            $price_medipumps = $model::get_prices($list, $clientid, $medipump_details);
                            $this->view->price_medipumps = $price_medipumps;
                        } 
                        elseif($p_pricelist_used == '$p_performancebylocation'){
                            $price_performancebylocation = $model::get_pricesbylocation_type($list, $clientid, $this->view->shortcuts_performance, $default_price_list['performancebylocation'], $bw_location_types);
                            $this->view->price_performancebylocation = $price_performancebylocation;
                        } 
                        elseif($p_pricelist_used == '$p_nr_anlage10'){
                            $price_nr_anlage10 = PriceNordrheinAnlage10Table::findListsPrices($list, $clientid, $default_price_list['nr_anlage10'])[$list];
                            $this->view->price_nr_anlage10 = $price_nr_anlage10;
                        } 
                        elseif($p_pricelist_used == '$p_demstepcare_invoice'){                            
                            $price_demstepcare = $model::get_prices($list, $clientid );
                            if(empty($price_demstepcare)){
                                $this->view->price_demstepcare = $default_price_list['demstepcare_invoice'];
                            }else{
                                $this->view->price_demstepcare = $price_demstepcare;
                            }
                        } 
                        elseif($p_pricelist_used == '$p_hessen_dta'){
                            $price_hessen_dta = $model::get_prices($list, $clientid, $shortcuts[$val_pu], $default_price_list[$val_pu], $default_dta_ids[$val_pu] );
                            $this->view->price_hessen_dta = $price_hessen_dta;
                        } 
                        elseif($p_pricelist_used == '$p_memberships'){          //TODO-3727 Lore 25.03.2021
                            $price_memberships = $model::get_prices($list, $clientid, $membership_details);
                            $this->view->price_memberships = $price_memberships;
                        } 
                        elseif($p_pricelist_used == '$p_xa'){
                            $price_xa = $model::get_prices($list, $clientid, $action_list, true);
                            $this->view->price_xa = $price_xa;
                        } 
                        elseif($p_pricelist_used == '$p_rp_dta'){
                            $price_rp_dta = $model::get_prices($list, $clientid, $shortcuts['rp'], $rp_location_types, $rp_sapv_types);
                            $this->view->price_rp_dta = $price_rp_dta;
                        }
                        elseif($p_pricelist_used == '$p_bre_kinder'){
                            $price_bre_kinder = $model::get_prices($list, $clientid );
                            if(empty($price_bre_kinder)){
                                $this->view->bre_kinder = $default_price_list['bre_kinder'];
                            }else{
                                $this->view->bre_kinder = $price_bre_kinder;
                            }
                        }
                        elseif($p_pricelist_used == '$p_rlp'){
                            $price_rlp = $model::get_prices($list, $clientid );
                            if(empty($price_rlp)){
                                $this->view->price_rlp = $default_price_list['rlp'];
                            }else{
                                $this->view->price_rlp = $price_rlp;
                            }
                        }
                        elseif($p_pricelist_used == '$p_nr_invoice'){
                            $price_nr_invoice = $model::get_prices($list, $clientid );
                            if(empty($price_nr_invoice)){
                                $this->view->price_nr_invoice = $default_price_list['nr_invoice'];
                            }else{
                                $this->view->price_nr_invoice = $price_nr_invoice;
                            }
                        }
                        elseif($p_pricelist_used == '$p_formblocks'){
                            $price_form_blocks = $model::get_prices($list, $clientid, $form_blocks_details, true);
                            $this->view->price_form_blocks = $price_form_blocks;
                        }
                        else {
                            //$price_.$val_pu = $model::get_prices($list, $clientid, $shortcuts[$val_pu], $default_price_list[$val_pu] );
                            $this->view->{price_.$val_pu} = $model::get_prices($list, $clientid, $shortcuts[$val_pu], $default_price_list[$val_pu] );
                        }

	                }
	            }
	        }
	         
	    }
	
	    $this->view->price_list      = $price_lists;
	    $this->view->price_invoice   = $price_model;
	    $this->view->pricelist_title = $all_clients_invoices_details['pricelist_title'];

	    
	    $this->view->shortcuts_admission = $shortcuts['admission'];
	    $price_admissions = PriceAdmission::get_prices($list, $clientid, $this->view->shortcuts_admission);
	    $this->view->price_admissions = $price_admissions;
	    
	    $this->view->shortcuts_daily = $shortcuts['daily'];
	    $price_daily = PriceDaily::get_prices($list, $clientid, $this->view->shortcuts_daily);
	    $this->view->price_daily = $price_daily;
	    
	    $this->view->shortcuts_visits = $shortcuts['visits'];
	    $price_visits = PriceVisits::get_prices($list, $clientid, $this->view->shortcuts_visits);
	    $this->view->price_visits = $price_visits;
	    
	    $this->view->shortcuts_care_level = $shortcuts['care_level'];
	    $this->view->price_care_level  = $price_care_level;
	    
	    $this->view->bayern_invoice_settings = $bayern_invoice_settings_arr;
	    
	    
	    
	}
	
	/**
	 * ISPC-2807 Ancuta 04.03.2021
	 */
	public function patientdeletiondatefixAction(){
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $pd_q = Doctrine_Query::create()
	    ->select('*')
	    ->from('Patient4Deletion')
	    ->where('final_delete = 0')
// 	    ->andWhere('last_action_model != "PatientCourse"')
	    ->limit(100)
	    ;
	    $patients2deletelist = $pd_q->fetchArray();

	    
	    foreach($patients2deletelist as $k => $patient){
	        $actions_results = Doctrine_Query::create()
	        ->select("ipid,change_date,create_date,course_date, IF(change_date = '0000-00-00 00:00:00', IF(create_date = '0000-00-00 00:00:00', course_date, create_date), IF(change_date = '1970-01-01 01:00:00', create_date, change_date)) as last_update,'PatientCourse' as model_name ")
	        ->from('PatientCourse')
	        ->where('ipid=? ',$patient['ipid'])
	        ->orderBy('last_update DESC')
	        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
            
            if($actions_results && $patient['last_action_date'] != $actions_results['last_update'] || $actions_results['last_update'] == "0000-00-00 00:00:00"){
	            if($actions_results['last_update'] == "0000-00-00 00:00:00"){
	                $actions_results['last_update'] = $actions_results['course_date'];
	            }
	            $frm1 = Doctrine::getTable('Patient4Deletion')->find($patient['id']);
	            if ($frm1 instanceof Patient4Deletion) {
	                $frm1->last_action_date = $actions_results['last_update'];
	                $frm1->last_action_model = 'PatientCourse';
	                $frm1->save();
	            }
	        }
	    }
	    echo 'done <pre> '.date('d.m.Y H:i:s');
	}
	
	
}
?>