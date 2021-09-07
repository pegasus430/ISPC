<?php

	class DtaController extends Zend_Controller_Action {

	    public $invoices_system_data = null; 
	    
		public function init()
		{
			
		}

		public function listdtainvoicesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$storned_invoices = BreInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			
			//construct months array in which the curent client has bre_invoices completed, not paid
			$months_q = Doctrine_Query::create()
				->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
				->from('BreInvoices')
				->where("isdelete = 0")
				->andWhere('completed_date != "0000-00-00 00:00:00"')
				->andWhere("storno = 0 " . $where)
				->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
				->andWhereIN("status",$unpaid_status) // display only unpaid
				->orderBy('DISTINCT DESC');
			$months_res = $months_q->fetchArray();

			if($months_res)
			{
				//current month on top
				$months_array[date('Y-m', time())] = date('m-Y', time());
				foreach($months_res as $k_month => $v_month)
				{
					$months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
				}

				$months_array = array_unique($months_array);
			}

			if(strlen($_REQUEST['search']) > '0')
			{
				$selected_period['start'] = $_REQUEST['search'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
			}

			$this->view->months_array = $months_array;

			if($this->getRequest()->isPost())
			{
				$post = $_POST;

				$dta_data = $this->gather_dta_data($clientid, $userid, $post);
				$this->generate_dta_xml($dta_data);
				exit;
			}
		}

		public function fetchdtainvoiceslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			$columnarray = array(
				"pat" => "epid_num",
				"invnr" => "invoice_number",
				"invstartdate" => "invoice_start",
				"invdate" => "completed_date_sort",
				"invtotal" => "invoice_total",
				"invkasse" => "company_name", // used in first order of health insurances
			);

			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$client_users_res = User::getUserByClientid($clientid, 0, true);

			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
			}

			$this->view->client_users = $client_users;

			//get patients data used in search and list
			$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
			$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";

			/* // if super admin check if patient is visible or not
			  if($logininfo->usertype == 'SA')
			  {
			  $sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
			  $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
			  $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
			  $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
			  $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
			  $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
			  } */

			$f_patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete =0")
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);

			if($_REQUEST['clm'] == 'pat')
			{
				$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}

			$f_patients_res = $f_patient->fetchArray();

			$f_patients_ipids[] = '9999999999999';
			foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
			{
				$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
				$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
			}

			$this->view->client_patients = $client_patients;

			if(strlen($_REQUEST['val']) > '0')
			{
				$selected_period['start'] = $_REQUEST['val'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}
			else
			{
				$selected_period['start'] = date('Y-m-01', time());
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}

			//order by health insurance
			if($_REQUEST['clm'] == "invkasse")
			{
				$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];

				$drop = Doctrine_Query::create()
					->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
					->from('PatientHealthInsurance')
					->whereIn("ipid", $f_patients_ipids)
					->orderBy($orderby);
				$droparray = $drop->fetchArray();

				$f_patients_ipids = array();
				foreach($droparray as $k_pat_hi => $v_pat_hi)
				{
					$f_patients_ipids[] = $v_pat_hi['ipid'];
				}
			}

			
			$storned_invoices = BreInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			
			$fdoc = Doctrine_Query::create()
				->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BreInvoices')
				->where("isdelete = 0 " . $where)
				->andWhere("storno = '0'")
				->andWhere('completed_date != "0000-00-00 00:00:00"')
				->andWhereIn('ipid', $f_patients_ipids)
				->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
				->andWhereIN("status",$unpaid_status) // display only unpaid
				->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
			if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
			{
				$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
			else
			{
				//sort by patient sorted ipid order
				$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
			}

			//used in pagination of search results
			$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
//			print_r($fdoc->getSqlQuery());
//			exit;
			$fdocarray = $fdoc->fetchArray();
			$limit = 500;
			$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
			$fdoc->where("isdelete = 0 " . $where . "");
			$fdoc->andWhere("storno = '0'");
			$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
			$fdoc->andWhereIn('ipid', $f_patients_ipids);
			$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
			$fdoc->andWhereIN("status",$unpaid_status);  // display only unpaid
			$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			if($_REQUEST['dbgq'])
			{
				print_r($fdoc->getSqlQuery());
				print_r($fdoc->fetchArray());

				exit;
			}
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());

			//get ipids for which we need health insurances
			foreach($fdoclimit as $k_inv => $v_inv)
			{
				$inv_ipids[] = $v_inv['ipid'];
			}

			$inv_ipids[] = '99999999999999';


//6. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);

			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;

				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}

			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);

			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];

					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}

				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
			}
			$this->view->healthinsurances = $healthinsu;


			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtainvoiceslist.html");
				$this->view->templates_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("dtainvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtainvoiceslist.html');

			echo json_encode($response);
			exit;
		}

		/*
		 * required data from invoices:
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) IK number of client
		 *
		 * (X) invoice ammount
		 *
		 * (X) Administration -> Mandanten -> Team Name
		 * (X) Administration -> Mandanten -> first name last name
		 * (X) Administration -> Mandante -> Telefon
		 * (X) Administration -> Mandante -> Mobile phone (fax instead)
		 *
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) Invoice number followed by ":0"
		 * (X) invoice date (YYYYMMDD)
		 *
		 * (X) health insurance number (Versichertennummer) of patient
		 *
		 * (X) last name patient
		 * (X) first name patient
		 * (X) birthday patient (YYYYMMDD)
		 * (X) street and no. patients address
		 * (X) ZIP patients address
		 * (X) City patients address
		 *
		 * (X) month of delivery (YYYYMM)
		 * (X) Pflegestufe of patients stammdaten
		 *
		 * (X) day of month from the invoiced action (01-31) or 99 if action is a flat action
		 * (X) start time of billable performance (HHMM)
		 * (X) single price of action
		 * (X) end time of billable performance (HHMM)
		 * (X) ammount of action (Format: 9999,99) (normally "001,00")
		 * (X) overall invoice ammount
		 * (X) overall invoice ammount
		 *
		 *
		 * L.E:
		 * 10.10.2014 - changed to load dta_id, dta_name based on action(visit) dta_location(mapped with patient location)
		 *
		 * L.E:
		 * 16.10.2014 - added user BSNR (Betriebsstättennummer), Genehmigungskennzeichen, Datum der Genehmigung (from verordnung)
		 *
		 * L.E TODO->:
		 * 31.10.2014 - added date of first admision, sapv (start, end, created) date, sapv GENEHMIUNGSNUMMER and date, icd (code and text) main, healthinsurance name
		 *
		 * L.E.:
		 * 07.04.2015 - changed Betriebsstättennummer to use the client one not tht patient one(bug ISPC-1012)
		 */

		private function gather_dta_data($clientid, $userid, $post)
		{
			//1. get all selected invoices data
			$bre_invoices = new BreInvoices();
			$bre_invoices_data = $bre_invoices->get_multiple_bre_invoices($post['invoices']['bre']);

			if($bre_invoices_data === false){
				return array();
			}
			
			foreach($bre_invoices_data as $k_inv => $v_inv)
			{
				$invoices_patients[] = $v_inv['ipid'];

				$invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
				$invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
				
				$patients_invoices_periods[$v_inv['ipid']] = $invoice_period;
			}
			
			//2. get all required client data
			$clientdata = Pms_CommonData::getClientData($clientid);
			$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
			$client_data['client']['team_name'] = $clientdata[0]['team_name'];
			$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
			$client_data['client']['phone'] = $clientdata[0]['phone'];
			$client_data['client']['fax'] = $clientdata[0]['fax'];



			//3. get pflegestuffe in current period
			$pflege = new PatientMaintainanceStage();
			$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);

			foreach($pflege_arr as $k_pflege => $v_pflege)
			{
				$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
			}

			foreach($grouped_pflege as $k_gpflege => $v_gpflege)
			{
				$last_pflege = end($v_gpflege);

				if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
				{
					//$k_gpflege = patient epid
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
				}
				else
				{
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
				}
			}

			//4. get all involved patients required data
			$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);

			foreach($patient_details as $k_pat_ipid => $v_pat_details)
			{
				$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
				$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
				$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
				$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
				$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
				$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
				$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
			}

			//4.1 get patients readmission details
			$conditions['periods'][0]['start'] = '2009-01-01';
			$conditions['periods'][0]['end'] = date('Y-m-d');
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);

			foreach($patient_days as $k_patd_ipid => $v_pat_details)
			{
				$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
			}

			//5. pricelist
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($invoice_period['start'], $invoice_period['end']); //get bra sapv pricelist and then shortcuts
			$curent_pricelist = $master_price_list[$invoice_period['start']][0];

			//6. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);

			$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
			// ispc = M => 1 = Versicherungspflichtige und -berechtigte
			// ispc = F => 3 = Familienversicherte
			// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
			//TODO-3528 Lore 12.11.2020
			$modules = new Modules();
			$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
			if($extra_healthinsurance_statuses){
			    $status_int_array += array(
			        "00" => "00",          //"Gesamtsumme aller Stati",
			        "11" => "11",          //"Mitglieder West",
			        "19" => "19",          //"Mitglieder Ost",
			        "31" => "31",          //"Angehörige West",
			        "39" => "39",          //"Angehörige Ost",
			        "51" => "51",          //"Rentner West",
			        "59" => "59",          //"Rentner Ost",
			        "99" => "99",          //"nicht zuzuordnende Stati",
			        "07" => "07",          //"Auslandsabkommen"
			    );
			}
			//.
			
			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;

				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}

			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
			
			
			//RWH Start
				//get health insurance subdivizions
				$symperm = new HealthInsurancePermissions();
				$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
				
				if($divisions)
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
						->whereIn("company_id", $company_ids)
						->andWhereIn("ipid", $invoices_patients);
					$hi2s_arr = $hi2s->fetchArray();
				}
				
				if($hi2s_arr)
				{
					foreach($hi2s_arr as $k_subdiv => $v_subdiv)
					{
						if($v_subdiv['subdiv_id'] == "3")
						{
							$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
						}
					}
				}
			//RWH End

			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];

					if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
					{
						$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
					}

					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}

				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;

				//Versichertennummer
				$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];

				//Institutskennzeichen
				$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
				
				//Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
				$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
				
				// Health insurance status - ISPC- 1368 // 150611
				$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
			}

// 			$visits_data = $this->get_bre_related_visits_150623($clientid, $invoices_patients, $invoice_period,$patients_invoices_periods);
			$visits_data = $this->get_bre_related_visits($clientid, $invoices_patients, $invoice_period,$patients_invoices_periods);
			if($_REQUEST['dbgz'])
			{
				print_r($visits_data);
				exit;
			}

			//7. get (HD) main diagnosis
			$main_abbr = "'HD'";
			$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);

			foreach($main_diag as $key => $v_diag)
			{
				$type_arr[] = $v_diag['id'];
			}

			$pat_diag = new PatientDiagnosis();
			$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids

			foreach($dianoarray as $k_diag => $v_diag)
			{
				//append diagnosis in patient data
				$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
				//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
				//ISPC-2489 Lore 26.11.2019
				$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
				
				$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
			}

//			print_r($dianoarray);
//			print_r($diano_arr);
			//8. get user data
			$user_details = User::getUserDetails($userid);

			//reloop the invoices data array
			foreach($bre_invoices_data as $k_invoice => $v_invoice)
			{
				if(!$master_data['invoice_' . $k_invoice])
				{
					$master_data['invoice_' . $k_invoice] = array();
				}

//				$client_data['healthinsurance_ik'] = $v_invoice['healthinsurance_ik'];
				$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);

				$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
				$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
				$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
				$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
				$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
				$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
				
//				$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $user_details[0]['betriebsstattennummer'];
				$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];

				$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_start_date'];
				$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_end_date'];

				$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_date'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_nr'];

				$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_create_date'];

				$inv_items = array();
				foreach($v_invoice['items'] as $k_item => $v_item)
				{
					$inv_actions = array();
//				moved down to be loaded per action(visit) not per item (action total per day)
//				$inv_items['dta_id'] = $curent_pricelist[$v_item['shortcut']]['dta_id'];
//				$inv_items['dta_name'] = $curent_pricelist[$v_item['shortcut']]['dta_name'];
//				if patient was edited the items amount wont corelate with existing visits calculated data
//				(uncomment to se all data)
//				if(count($visits_data[$v_invoice['ipid']][$v_item['shortcut']]) == $v_item['qty'])
//				{
					$inv_actions = array();
					foreach($visits_data[$v_invoice['ipid']][$v_item['shortcut']] as $k_action => $v_action)
					{
						$inv_actions['dta_id'] = $v_action['dta_pricelist']['dta_id'];
						$inv_actions['dta_name'] = $v_action['dta_pricelist']['dta_name'];
						$inv_actions['dta_location_name'] = $v_action['dta_location']['name'];
//					$inv_actions['price'] = number_format($v_item['price'], '2', ',', '');
						$inv_actions['price'] = number_format($v_action['dta_pricelist']['price'], '2', ',', '');
						$inv_actions['ammount'] = str_pad(number_format("1.00", '2', ',', ''), "7", "0", STR_PAD_LEFT);
						$inv_actions['day'] = date('Ymd', strtotime($v_action['start']));
						$inv_actions['start_time'] = date('Hi', strtotime($v_action['start']));

						if(strlen($v_action['end']) > '0')
						{
							$inv_actions['end_time'] = date('Hi', strtotime($v_action['end']));
						}

						$inv_items['actions']['action_' . $k_action] = $inv_actions;
					}
//				}
					$master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
					$inv_actions = array();
					$inv_items = array();
				}
				$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
			}

			return $master_data;
		}
		private function get_bre_related_visits_150623($clientid, $invoices_patients, $selected_period)
		{
			$patientmaster = new PatientMaster();
			//Client Hospital Settings START
			$conditions['periods'][0]['start'] = $selected_period['start'];
			$conditions['periods'][0]['end'] = $selected_period['end'];
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);
		
			$current_period_days = $patientmaster->getDaysInBetween($selected_period['start'], $selected_period['end']);
		
			//find if there is a sapv for current period START!
			$dropSapv = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->whereIn('ipid', $invoices_patients)
			->andWhere('"' . date('Y-m-d', strtotime($selected_period['start'])) . '" <= verordnungbis')
			->andWhere('"' . date('Y-m-d', strtotime($selected_period['end'])) . '" >= verordnungam')
			->andWhere('verordnungam != "0000-00-00 00:00:00"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00"')
			->andWhere('isdelete=0')
			->andWhere('status != 1 ')
			->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
		
			$all_sapv_days = array();
			$temp_sapv_days = array();
			foreach($droparray as $k_sapv => $v_sapv)
			{
				$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
				$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
				$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		
				//aditional data from sapv which was added on 16.10.2014
				if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
				{
					$sapv_data[$v_sapv['ipid']]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
					$sapv_data[$v_sapv['ipid']]['approved_number'] = $v_sapv['approved_number'];
				}
		
				//aditional data from sapv which was added on 31.10.2014
				$sapv_data[$v_sapv['ipid']]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
				$sapv_data[$v_sapv['ipid']]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
				$sapv_data[$v_sapv['ipid']]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		
				foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
				{
					if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
					{
						$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
					}
		
					$current_verordnet = explode(',', $v_sapv['verordnet']);
					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		
					asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
				}
			}
		
			//get client users
			$user = new User();
			$c_users = $user->getUserByClientid($clientid, 0, true);
		
			foreach($c_users as $k_c_users => $v_c_users)
			{
				$client_users[$v_c_users['id']] = $v_c_users;
			}
		
			//get contact forms START
			//get deleted cf from patient course
			$deleted_cf = Doctrine_Query::create()
			->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
			->from('PatientCourse')
			->where('wrong=1')
			->andWhereIn("ipid", $invoices_patients)//TODO-3761 Ancuta 19.01.2021  - wrong query syntax andWhere instead of andWhereIn
			->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
			->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'")
			->andWhere('source_ipid = ""');
			$deleted_cf_array = $deleted_cf->fetchArray();
		
			$excluded_cf_ids[] = '99999999999';
			foreach($deleted_cf_array as $k_dcf => $v_dcf)
			{
				$excluded_cf_ids[] = $v_dcf['recordid'];
			}
		
			//get cf in period - deleted cf
			$cf = new ContactForms();
			$p_contactforms = $cf->get_multiple_contact_form_period($invoices_patients, false, $excluded_cf_ids);
		
			$contact_forms_ids[] = '9999999999999';
			foreach($p_contactforms as $kk_cf => $vv_cf)
			{
				$contact_forms_ids[] = $vv_cf['id'];
			}
		
			$cnt = array();
			foreach($p_contactforms as $k_ccf => $v_ccf)
			{
				foreach($v_ccf as $k_cf => $v_cf)
				{
		
					$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
		
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['id'] = $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['start_date'] = $v_cf['start_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['end_date'] = $v_cf['end_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['del_id'] = 'cf_' . $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['date'] = $v_cf['billable_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['extra_forms'] = '0';
		
					if(count($cnt[$k_ccf][$visit_date]) >= '3')
					{
						$contact_forms2dates[$k_ccf][$visit_date]['2']['extra_forms'] += '1'; //show the remaining
					}
		
					foreach($contact_forms2dates[$k_ccf] as $k_cf_dates => $v_cf_dates)
					{
						$contact_forms2dates[$k_ccf][$k_cf_dates] = array_values($v_cf_dates);
					}
		
					$cnt[$k_ccf][$visit_date][] = $v_cf['id'];
				}
			}
		
			//get contact forms END
			$visits_array = $contact_forms2dates;
		
			//get shortcuts and saved pricelist or default pricelist
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($selected_period['start'], $selected_period['end']); //get bra sapv pricelist and then shortcuts
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
		
			//get form saved data
			$bre_sapv = new BreSapvControl();
			$bre_sapv_data = $bre_sapv->get_bre_sapv_controlsheetnew($invoices_patients, $selected_period['start'], $master_price_list, false, false, false, $patient_days);
		
			//get patient locations
			$patients_locations = PatientLocation::get_valid_patients_locations($invoices_patients, true);
		
			//get patients admissions with times
			$patient_admissions = PatientReadmission::get_patient_admissions($invoices_patients);
			
			//returned location_master_id => dta_location_id
			$dta_locations_master = DtaLocations::get_dta_locations($clientid, '3');
			$dta_locations = DtaLocations::get_dta_locations($clientid, '4');
			//			print_r($master_price_list);
			//			print_r($dta_locations);
			//			print_r($dta_locations_master);
			//			exit;
			//map patient locations with dta_locations
			//returns day=>dta_location_id foreach patient ipid
			$mapped_dta_locations = $this->map_locations2dta($invoices_patients, $patients_locations, $dta_locations);
		
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				if($bre_sapv_data[$v_ipid]) //read saved data
				{
					$master_data = $bre_sapv_data[$v_ipid];
					//get shortcut trigered for saved data events
					foreach($bre_sapv_data[$v_ipid] as $k_short => $days_values)
					{
						foreach($days_values as $k_day => $day_values)
						{
							if($day_values['qty'] > '0' && count($visits_array[$v_ipid][$k_day]) > '0' && $k_short != 'vv' && $k_short != 'tv')
							{
								$triggered_v = array();
								$triggered_sapv = array();

								$triggered_v['start'] = $day_values['date'];
								$triggered_v['end'] = "";
								$triggered_v['short'] = $k_short;
								$triggered_v['type'] = 'admission';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_day][0]['dta'][$k_short][$mapped_dta_locations[$v_ipid][$k_day]];
		
								$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
								$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
								$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
		
								$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
								$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
		
								$triggered[$v_ipid][$k_short][] = $triggered_v;
								$triggered[$v_ipid]['sapv'] = $triggered_sapv;
							}
							else if($day_values['qty'] > '0' && count($visits_array[$v_ipid][$k_day]) > '0' && ($k_short == 'vv' || $k_short == 'tv'))
							{
								$triggered_v = array();
								$triggered_v['id'] = $visits_array[$v_ipid][$k_day][0]['id'];
								$triggered_v['start'] = $visits_array[$v_ipid][$k_day][0]['start_date'];
								$triggered_v['end'] = $visits_array[$v_ipid][$k_day][0]['end_date'];
								$triggered_v['short'] = $k_short;
								$triggered_v['type'] = 'visit';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_day][0]['dta'][$k_short][$mapped_dta_locations[$v_ipid][$k_day]];
		
								$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
								$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
								$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
		
								$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
								$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
		
								$triggered[$v_ipid][$k_short][] = $triggered_v;
								$triggered[$v_ipid]['sapv'] = $triggered_sapv;
							}
						}
					}
				}
				else //load system wide data
				{
					//get shortcut trigered for non-saved data events
					foreach($shortcuts['bre_sapv'] as $k_short => $v_short)
					{
						foreach($current_period_days as $k_c_day => $v_c_day)
						{
							$day_formated = date('d.m.Y', strtotime($v_c_day));
		
							//Assessment, Beratung und Koordination
							if($v_short == 'abk' &&
									in_array($day_formated, $patient_days[$v_ipid]['admission_days']) &&
									in_array($day_formated, $patient_days[$v_ipid]['treatment_days'])
							)
							{
								$triggered_v = array();
								$triggered_sapv = array();
								
								$triggered_v['start'] = date('Y-m-d H:i:s', strtotime($patient_admissions[$v_ipid][$v_c_day]));
								$triggered_v['end'] = "";
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'admission';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$v_c_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$v_c_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$v_c_day]];
		
								$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
								$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
								$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
		
								$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
								$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
		
								$triggered[$v_ipid][$v_short][] = $triggered_v;
								$triggered[$v_ipid]['sapv'] = $triggered_sapv;
							}
		
							//Beratung und Koordination
							if($v_short == 'bk' &&
									in_array($day_formated, $patient_days[$v_ipid]['admission_days']) &&
									in_array($day_formated, $patient_days[$v_ipid]['treatment_days'])
							)
							{
								$triggered_v = array();
								$triggered_sapv = array();
								
								$triggered_v['start'] = date('Y-m-d H:i:s', strtotime($patient_admissions[$v_ipid][$v_c_day]));
								$triggered_v['end'] = "";
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'admission';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$v_c_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$v_c_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$v_c_day]];
		
								$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
								$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
								$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
		
								$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
								$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
		
								$triggered[$v_ipid][$v_short][] = $triggered_v;
								$triggered[$v_ipid]['sapv'] = $triggered_sapv;
							}
						}
		
						foreach($days2verordnet[$v_ipid] as $k_vv_day => $v_vv_values)
						{
							$k_vv_day_alt = date('d.m.Y', strtotime($k_vv_day));
							//Additiv unterstützte Teilversorgung
							//tv reqired
							if($v_short == 'aut' &&
									in_array('3', $v_vv_values) &&
									!in_array('4', $v_vv_values) &&
									in_array($k_vv_day, $current_period_days) &&
									in_array($k_vv_day_alt, $patient_days[$v_ipid]['active_days']) &&
									!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospital']['real_days_cs']) &&
									!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospiz']['real_days_cs']) &&
									count($visits_array[$v_ipid][$k_vv_day]) > '0'
							)
							{
								$triggered_v = array();
								$triggered_sapv = array();
								
								$triggered_v['id'] = $visits_array[$v_ipid][$k_vv_day][0]['id'];
								$triggered_v['start'] = $visits_array[$v_ipid][$k_vv_day][0]['start_date'];
								$triggered_v['end'] = $visits_array[$v_ipid][$k_vv_day][0]['end_date'];
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'visit';
		
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_vv_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_vv_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$k_vv_day]];
		
								$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
								$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
								$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
		
								$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
								$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
		
								$triggered[$v_ipid][$v_short][] = $triggered_v;
								$triggered[$v_ipid]['sapv'] = $triggered_sapv;
							}
		
							//Vollständige Versorgung
							//vv required
							if($v_short == 'vv' &&
									in_array('4', $v_vv_values) &&
									in_array($k_vv_day, $current_period_days) &&
									in_array($k_vv_day_alt, $patient_days[$v_ipid]['active_days']) &&
									!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospital']['real_days_cs']) &&
									!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospiz']['real_days_cs']) &&
									count($visits_array[$v_ipid][$k_vv_day]) > '0'
							)
							{
								$triggered_v = array();
								$triggered_sapv = array();
								
								$triggered_v['id'] = $visits_array[$v_ipid][$k_vv_day][0]['id'];
								$triggered_v['start'] = $visits_array[$v_ipid][$k_vv_day][0]['start_date'];
								$triggered_v['end'] = $visits_array[$v_ipid][$k_vv_day][0]['end_date'];
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'visit';
		
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_vv_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_vv_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$k_vv_day]];
		
								$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
								$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
								$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
		
								$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
								$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
		
								$triggered[$v_ipid][$v_short][] = $triggered_v;
								$triggered[$v_ipid]['sapv'] = $triggered_sapv;
							}
						}
					}
				}
			}
		
			return $triggered;
		}
		
		private function get_bre_related_visits($clientid, $invoices_patients, $selected_period,$patients_invoices_periods)
		{
			$patientmaster = new PatientMaster();
			//Client Hospital Settings START
			$conditions['periods'][0]['start'] = $selected_period['start'];
			$conditions['periods'][0]['end'] = $selected_period['end'];
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);

			$current_period_days = $patientmaster->getDaysInBetween($selected_period['start'], $selected_period['end']);

			//find if there is a sapv for current period START!
			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $invoices_patients)
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
 
			$all_sapv_days = array();
			$temp_sapv_days = array();
			//4920e7f7c56bcb600b00948f709f46252fd3ff6f
			$s=0;
			foreach($droparray as $k_sapv => $v_sapv)
			{
				$r1['start'][$v_sapv['ipid']][$s] = "";
				$r1['end'][$v_sapv['ipid']][$s] = "";
					
				$r2['start'][$v_sapv['ipid']][$s] = "";
				$r2['end'][$v_sapv['ipid']][$s] = "";
				
				
				
				if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
					// no sapv taken here - becouse it is considered to be fully denied	
				} 
				else
				{
					$r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
					$r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
						
					$r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
					$r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
						
					if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
					{

						//aditional data from sapv which was added on 16.10.2014
						if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
						{
							$sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
						} 
						
						if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
							$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
						} else{
							$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
						}
						
						
						if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
						{
							$v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
						}
						
						
						$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
						$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
						
						$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
						
						
						//aditional data from sapv which was added on 31.10.2014
						$sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
						$sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
						$sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
						$sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		
						foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
						{
							if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
							{
								$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
							}
		
							$current_verordnet = explode(',', $v_sapv['verordnet']);
							$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		
							asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
							$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
						}
						
						$s++;
					}
				}
			}
//			print_r("days2verordnet\n");
//			print_r($days2verordnet);
//			exit;
			
			//get client users
			$user = new User();
			$c_users = $user->getUserByClientid($clientid, 0, true);

			foreach($c_users as $k_c_users => $v_c_users)
			{
				$client_users[$v_c_users['id']] = $v_c_users;
			}

			//get contact forms START
			//get deleted cf from patient course
			$deleted_cf = Doctrine_Query::create()
				->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('wrong=1')
				->andWhereIn("ipid", $invoices_patients)//TODO-3761 Ancuta 19.01.2021  
				->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
				->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'")
				->andWhere('source_ipid = ""');
			$deleted_cf_array = $deleted_cf->fetchArray();

			$excluded_cf_ids[] = '99999999999';
			foreach($deleted_cf_array as $k_dcf => $v_dcf)
			{
				$excluded_cf_ids[] = $v_dcf['recordid'];
			}

			//get cf in period - deleted cf
			$cf = new ContactForms();
			$p_contactforms = $cf->get_multiple_contact_form_period($invoices_patients, false, $excluded_cf_ids, 'start_date', 'ASC');

			$contact_forms_ids[] = '9999999999999';
			foreach($p_contactforms as $kk_cf => $vv_cf)
			{
				$contact_forms_ids[] = $vv_cf['id'];
			}

			$cnt = array();
			foreach($p_contactforms as $k_ccf => $v_ccf)
			{
				foreach($v_ccf as $k_cf => $v_cf)
				{

					$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));

					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['id'] = $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['start_date'] = $v_cf['start_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['end_date'] = $v_cf['end_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['del_id'] = 'cf_' . $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['date'] = $v_cf['billable_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['extra_forms'] = '0';

					if(count($cnt[$k_ccf][$visit_date]) >= '3')
					{
						$contact_forms2dates[$k_ccf][$visit_date]['2']['extra_forms'] += '1'; //show the remaining
					}

					foreach($contact_forms2dates[$k_ccf] as $k_cf_dates => $v_cf_dates)
					{
						$contact_forms2dates[$k_ccf][$k_cf_dates] = array_values($v_cf_dates);
					}

					$cnt[$k_ccf][$visit_date][] = $v_cf['id'];
				}
			}

			//get contact forms END
			$visits_array = $contact_forms2dates;
			if($_REQUEST['dbgz'])
			{
				print_r("visits_array\n");
				print_r($visits_array);
				print_r("contact_forms2dates\n");
				print_r($contact_forms2dates);
			}
			
			//get shortcuts and saved pricelist or default pricelist
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($selected_period['start'], $selected_period['end']); //get bra sapv pricelist and then shortcuts
			$shortcuts = Pms_CommonData::get_prices_shortcuts();

			//get form saved data
			$bre_sapv = new BreSapvControl();
			$bre_sapv_data = $bre_sapv->get_bre_sapv_controlsheetnew($invoices_patients, $selected_period['start'], $master_price_list, false, false, false, $patient_days);

			//get patient locations
			$patients_locations = PatientLocation::get_valid_patients_locations($invoices_patients, true);
		
			//get patients admissions with times
			$patient_admissions = PatientReadmission::get_patient_admissions($invoices_patients);
			
			//returned location_master_id => dta_location_id
			$dta_locations_master = DtaLocations::get_dta_locations($clientid, '3');
			$dta_locations = DtaLocations::get_dta_locations($clientid, '4');
//			print_r($master_price_list);
//			print_r($dta_locations);
//			print_r($dta_locations_master);
//			exit;
			//map patient locations with dta_locations
			//returns day=>dta_location_id foreach patient ipid
			$mapped_dta_locations = $this->map_locations2dta($invoices_patients, $patients_locations, $dta_locations);

			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				if($bre_sapv_data[$v_ipid]) //read saved data
				{
					$master_data = $bre_sapv_data[$v_ipid];
					//get shortcut trigered for saved data events
					foreach($bre_sapv_data[$v_ipid] as $k_short => $days_values)
					{
						foreach($days_values as $k_day => $day_values)
						{
//							if($day_values['qty'] > '0' && count($visits_array[$v_ipid][$k_day]) > '0' && $k_short != 'vv' && $k_short != 'tv')
							if($day_values['qty'] > '0' && $k_short != 'vv' && $k_short != 'tv')
							{
								$triggered_v = array();
								
								$triggered_v['start'] = $day_values['date'];
								$triggered_v['end'] = "";
								$triggered_v['short'] = $k_short;
								$triggered_v['type'] = 'admission';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_day][0]['dta'][$k_short][$mapped_dta_locations[$v_ipid][$k_day]];
								
								$triggered[$v_ipid][$k_short][] = $triggered_v;
								
								$days_actions[$v_ipid][] = date('d.m.Y',strtotime($k_day));
								
							}
//							else if($day_values['qty'] > '0' && count($visits_array[$v_ipid][$k_day]) > '0' && ($k_short == 'vv' || $k_short == 'tv'))
							else if($day_values['qty'] > '0' && ($k_short == 'vv' || $k_short == 'tv'))
							{
								$triggered_v = array();
								$vis = $visits_array[$v_ipid][$k_vv_day][0];
								
								
								$triggered_v['id'] = $vis['id'];
								if($vis['start_date'])
								{
									$triggered_v['start'] = date('Y-m-d 00:00:00', strtotime($vis['start_date']));
									$triggered_v['end'] = date('Y-m-d 00:01:00', strtotime($vis['end_date']));
								}
								else
								{
									$triggered_v['start'] = date('Y-m-d 00:00:00', strtotime($day_values['date']));
									$triggered_v['end'] = date('Y-m-d 00:01:00', strtotime($day_values['date']));
								}
								
//								$triggered_v['start'] = '0000';
//								$triggered_v['end'] = "0001";
 
								$triggered_v['short'] = $k_short;
								$triggered_v['type'] = 'visit';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_day][0]['dta'][$k_short][$mapped_dta_locations[$v_ipid][$k_day]];

								$triggered[$v_ipid][$k_short][] = $triggered_v;
								
								$days_actions[$v_ipid][] = date('d.m.Y',strtotime($k_day));
								
							}
						}
					}
				}
				else //load system wide data
				{
					//get shortcut trigered for non-saved data events
					foreach($shortcuts['bre_sapv'] as $k_short => $v_short)
					{
						foreach($current_period_days as $k_c_day => $v_c_day)
						{
							$day_formated = date('d.m.Y', strtotime($v_c_day));
							
							//Assessment, Beratung und Koordination
							if($v_short == 'abk' &&
								in_array($day_formated, $patient_days[$v_ipid]['admission_days']) &&
								in_array($day_formated, $patient_days[$v_ipid]['treatment_days'])
							)
							{
								$triggered_v = array();
								
								$triggered_v['start'] = date('Y-m-d H:i:s', strtotime($patient_admissions[$v_ipid][$v_c_day]));
								$triggered_v['end'] = "";
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'admission';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$v_c_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$v_c_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$v_c_day]];

								$triggered[$v_ipid][$v_short][] = $triggered_v;
								
								$days_actions[$v_ipid][] = date('d.m.Y',strtotime($v_c_day));
								
							}

							//Beratung und Koordination
							if($v_short == 'bk' &&
								in_array($day_formated, $patient_days[$v_ipid]['admission_days']) &&
								in_array($day_formated, $patient_days[$v_ipid]['treatment_days'])
							)
							{
								$triggered_v = array();
								
								$triggered_v['start'] = date('Y-m-d H:i:s', strtotime($patient_admissions[$v_ipid][$v_c_day]));
								$triggered_v['end'] = "";
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'admission';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$v_c_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$v_c_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$v_c_day]];
								
								$triggered[$v_ipid][$v_short][] = $triggered_v;
								
								$days_actions[$v_ipid][] = date('d.m.Y',strtotime($v_c_day));
							}
						}

						
						foreach($days2verordnet[$v_ipid] as $k_vv_day => $v_vv_values)
						{
							$k_vv_day_alt = date('d.m.Y', strtotime($k_vv_day));
							
							//Additiv unterstützte Teilversorgung
							//tv reqired
							if($v_short == 'aut' &&
								in_array('3', $v_vv_values) &&
								!in_array('4', $v_vv_values) &&
								in_array($k_vv_day, $current_period_days) &&
								in_array($k_vv_day_alt, $patient_days[$v_ipid]['active_days']) &&
								!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospital']['real_days_cs']) &&
								!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospiz']['real_days_cs']) &&
								count($visits_array[$v_ipid][$k_vv_day]) > '0'
							)
							{
								$triggered_v = array();
								$vis = $visits_array[$v_ipid][$k_vv_day][0];
								
								$triggered_v['id'] = $vis['id'];
								$triggered_v['start'] = $vis['start_date'];
								$triggered_v['end'] = $vis['end_date'];
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'visit';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_vv_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_vv_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$k_vv_day]];

								$triggered[$v_ipid][$v_short][] = $triggered_v;
								
								$days_actions[$v_ipid][] = date('d.m.Y',strtotime($k_vv_day));
								
							}

							//Vollständige Versorgung
							//vv required
							if($v_short == 'vv' &&
								in_array('4', $v_vv_values) &&
								in_array($k_vv_day, $current_period_days) &&
								in_array($k_vv_day_alt, $patient_days[$v_ipid]['active_days']) &&
								!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospital']['real_days_cs']) &&
								!in_array($k_vv_day_alt, $patient_days[$v_ipid]['hospiz']['real_days_cs']) &&
								count($visits_array[$v_ipid][$k_vv_day]) > '0'
							)
							{
								$triggered_v = array();
								$vis = $visits_array[$v_ipid][$k_vv_day][0];
								
								$triggered_v['id'] = $vis['id'];
								$triggered_v['start'] = $vis['start_date'];
								$triggered_v['end'] = $vis['end_date'];
								$triggered_v['short'] = $v_short;
								$triggered_v['type'] = 'visit';
								$triggered_v['dta_location'] = $dta_locations_master[$mapped_dta_locations[$v_ipid][$k_vv_day]];
								$triggered_v['dta_pricelist'] = $master_price_list[$k_vv_day][0]['dta'][$v_short][$mapped_dta_locations[$v_ipid][$k_vv_day]];
								
								$triggered[$v_ipid][$v_short][] = $triggered_v;
								
								$days_actions[$v_ipid][] = date('d.m.Y',strtotime($k_vv_day));
								
							}
						}
					}
				}
				
				
				$action_days[$v_ipid]['actions'] = array_unique($days_actions[$v_ipid]);
				
				
				$patient_action_days[$v_ipid]['actions'] = array_values($action_days[$v_ipid]['actions']);
				array_walk($patient_action_days[$v_ipid]['actions'], function(&$value) {
					$value = strtotime($value);
				});
				
				asort($patient_action_days[$v_ipid]['actions']);
				array_walk($patient_action_days[$v_ipid]['actions'], function(&$valuez) {
					$valuez = date('d.m.Y',$valuez);
				});
				
				$final_patient_action_days[$v_ipid]['actions'] = array_values($patient_action_days[$v_ipid]['actions']);
				
// 				$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
// 				$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
// 				$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
				
// 				if($sapv_data[$v_ipid]['approved_date']){
// 					$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
// 				} else{
// 					$triggered_sapv['sapv_approved_date'] = $final_patient_action_days[$v_ipid]['actions'][0];
// 				}
// 				$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
			
// 				$triggered[$v_ipid]['sapv'] = $triggered_sapv;
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($sapv_data[$v_ipid] as $ks => $s_data)
				{
					foreach($final_patient_action_days[$v_ipid]['actions'] as $k=>$act_days){
			
						$r1start = strtotime(date('Y-m-d', strtotime($s_data['sapv_start'])));
						$r1end = strtotime(date('Y-m-d', strtotime($s_data['sapv_end'])));
							
						$r2start = strtotime($act_days);
						$r2end = strtotime($act_days);
							
						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)  )
						{
							if(!in_array($act_days,$sapv_data[$v_ipid][$ks]['actions'])){
								$sapv_data[$v_ipid][$ks]['actions'][] = $act_days;
							}
						}
					}
				}
			}
				
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($sapv_data[$v_ipid] as $ks => $s_data)
				{
					$s_data['actions'] = array_unique($s_data['actions']);
					asort($s_data['actions']);
						
					if( empty($s_data['approved_date'])   || strlen($s_data['approved_date']) == 0 ){
						$sapv_data[$v_ipid][$ks]['approved_date'] = $s_data['actions'][0];
					}
				}
			}
			
			foreach($sapv_data as $k_ipid =>$sapvdata){
				$sapv_data[$k_ipid] = end($sapvdata);
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				$triggered_sapv['sapv_start_date'] = $sapv_data[$v_ipid]['sapv_start'];
				$triggered_sapv['sapv_end_date'] = $sapv_data[$v_ipid]['sapv_end'];
				$triggered_sapv['sapv_create_date'] = $sapv_data[$v_ipid]['create_date'];
				$triggered_sapv['sapv_approved_date'] = $sapv_data[$v_ipid]['approved_date'];
				$triggered_sapv['sapv_approved_nr'] = $sapv_data[$v_ipid]['approved_number'];
				
				$triggered[$v_ipid]['sapv'] = $triggered_sapv;
			}
			
			return $triggered;
		}
		


		private function get_bre_hospiz_related_actions_150623($clientid, $invoices_patients, $selected_period)
		{
			$patientmaster = new PatientMaster();
			//Client Hospital Settings START
			$conditions['periods'][0]['start'] = $selected_period['start'];
			$conditions['periods'][0]['end'] = $selected_period['end'];
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);
		
			$current_period_days = $patientmaster->getDaysInBetween($selected_period['start'], $selected_period['end']);
		
			/* ----------------------- get patients sapv days START ---------------------------*/
			$dropSapv = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->whereIn('ipid', $invoices_patients)
			->andWhere('"' . date('Y-m-d', strtotime($selected_period['start'])) . '" <= verordnungbis')
			->andWhere('"' . date('Y-m-d', strtotime($selected_period['end'])) . '" >= verordnungam')
			->andWhere('verordnungam != "0000-00-00 00:00:00"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00"')
			->andWhere('isdelete=0')
			->andWhere('status != 1 ')
			->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
		
			foreach($droparray as $keys => $sapv)
			{
				if($sapv['status'] == '1' && $sapv['verorddisabledate'] != '0000-00-00 00:00:00' && $sapv['verorddisabledate'] != '1970-01-01 00:00:00')
				{
					$verordisablenext = strtotime('+1 day', strtotime($sapv['verorddisabledate']));
					$sapv ['verordnungbis'] = date('Y-m-d', $verordisablenext);
				}
				$patients_sapv_data [$sapv ['ipid']] ['sapv_days'] [] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($sapv ['verordnungam'])), date("Y-m-d", strtotime($sapv ['verordnungbis'])), false);
		
				//aditional data from sapv which was added on 16.10.2014
				if($sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($sapv['approved_date'])) != '1970-01-01' && $sapv['status'] == '2')
				{
					$sapv_data[$sapv['ipid']]['approved_date'] = date('d.m.Y', strtotime($sapv['approved_date']));
					$sapv_data[$sapv['ipid']]['approved_number'] = $sapv['approved_number'];
				}
		
				//aditional data from sapv which was added on 31.10.2014
				$sapv_data[$sapv['ipid']]['sapv_start'] = date('d.m.Y', strtotime($sapv['verordnungam']));
				$sapv_data[$sapv['ipid']]['sapv_end'] = date('d.m.Y', strtotime($sapv['verordnungbis']));
				$sapv_data[$sapv['ipid']]['create_date'] = date('d.m.Y', strtotime($sapv['create_date']));
			}
				
			foreach($patients_sapv_data as $ipid => $sapvdata)
			{
				foreach($sapvdata ['sapv_days'] as $group => $spvdata)
				{
					foreach($spvdata as $key => $daysapv)
					{
						if(in_array(date('d.m.Y', strtotime($daysapv)), $patient_days [$ipid]['real_active_days'])){
							$patients_sapv_days [$ipid] [] = $daysapv;
						}
					}
				}
			}
				
				
			if($_REQUEST['dbgz']){
				print_r(" \n patients sapv days \n ");
				print_r($patients_sapv_days);
				print_r(" \n  \n ");
			}
				
			/* ----------------------- get patients sapv days END ---------------------------*/
			 	
			/* ----------------------- get client users START ---------------------------*/
			$user = new User();
			$c_users = $user->getUserByClientid($clientid, 0, true);
		
			foreach($c_users as $k_c_users => $v_c_users)
			{
				$client_users[$v_c_users['id']] = $v_c_users;
			}
		
			/* ----------------------- get shortcuts and saved pricelist or default pricelist START ---------------------------*/
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($selected_period['start'], $selected_period['end']); //get bre hospiz pricelist and then shortcuts
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			/* ----------------------- get shortcuts and saved pricelist or default pricelist END ---------------------------*/
			/* ----------------------- get contact forms START ---------------------------*/
				
			//get deleted cf from patient course
			$deleted_cf = Doctrine_Query::create()
			->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
			->from('PatientCourse')
			->where('wrong=1')
			->andWhereIn("ipid", $invoices_patients)//TODO-3761 Ancuta 19.01.2021  
			->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
			->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'")
			->andWhere('source_ipid = ""');
			$deleted_cf_array = $deleted_cf->fetchArray();
		
			$excluded_cf_ids[] = '99999999999';
			foreach($deleted_cf_array as $k_dcf => $v_dcf)
			{
				$excluded_cf_ids[] = $v_dcf['recordid'];
			}
		
			//get cf in period - deleted cf
			$cf = new ContactForms();
			$p_contactforms = $cf->get_multiple_contact_form_period($invoices_patients, false, $excluded_cf_ids);
		
			$contact_forms_ids[] = '9999999999999';
			foreach($p_contactforms as $kk_cf => $vv_cf)
			{
				$contact_forms_ids[] = $vv_cf['id'];
			}
		
			$cnt = array();
			foreach($p_contactforms as $k_ccf => $v_ccf)
			{
				foreach($v_ccf as $k_cf => $v_cf)
				{
					$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
		
					$actions[$k_ccf][$v_cf['start_date']] = $v_cf;
					$actions[$k_ccf][$v_cf['start_date']]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($v_cf['start_date']))][0]['visit']['dta_id'];
					$actions[$k_ccf][$v_cf['start_date']]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($v_cf['start_date']))][0]['visit']['price'];
					$actions[$k_ccf][$v_cf['start_date']]['price'] = $master_price_list[date('Y-m-d', strtotime($v_cf['start_date']))][0]['visit']['price'];
					$actions[$k_ccf][$v_cf['start_date']]['name'] = $this->view->translate("shortcut_name_visit");
					$actions[$k_ccf][$v_cf['start_date']]['action_type'] = "visit";
						
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['id'] = $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['start_date'] = $v_cf['start_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['end_date'] = $v_cf['end_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['del_id'] = 'cf_' . $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['date'] = $v_cf['billable_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['extra_forms'] = '0';
		
					if(count($cnt[$k_ccf][$visit_date]) >= '3')
					{
						$contact_forms2dates[$k_ccf][$visit_date]['2']['extra_forms'] += '1'; //show the remaining
					}
		
					foreach($contact_forms2dates[$k_ccf] as $k_cf_dates => $v_cf_dates)
					{
						$contact_forms2dates[$k_ccf][$k_cf_dates] = array_values($v_cf_dates);
					}
		
					$cnt[$k_ccf][$visit_date][] = $v_cf['id'];
						
				}
			}
			$visits_array = $contact_forms2dates;
			/* ----------------------- get contact forms END ---------------------------*/
		
			/* ----------------------- get Telefon START ---------------------------*/
				
			$tel = Doctrine_Query::create()
			->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
			->from('PatientCourse')
			->where('wrong!=1')
			->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("XT")) . '"')
			->andWhereIn('ipid', $invoices_patients)
			->andWhere('source_ipid = ""')
			->orderBy("convert(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
			$tel_array = $tel->fetchArray();
				
			$cnts = array();
			foreach($tel_array as $k_ph => $v_ph)
			{
				$phone_date = date('Y-m-d', strtotime($v_ph['done_date']));
		
				if(in_array($phone_date,$current_period_days)){
						
					$actions[$v_ph['ipid']][$v_ph['done_date']] = $v_ph;
					$actions[$v_ph['ipid']][$v_ph['done_date']]['start_date'] = $v_ph['done_date'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($v_ph['done_date']))][0]['phone']['dta_id'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($v_ph['done_date']))][0]['phone']['price'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['price'] = $master_price_list[date('Y-m-d', strtotime($v_ph['done_date']))][0]['phone']['price'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['name'] = $this->view->translate("shortcut_name_phone");
					$actions[$v_ph['ipid']][$v_ph['done_date']]['action_type'] = "phone";
						
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['id'] = $v_ph['id'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['start_date'] = $v_ph['done_date'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['end_date'] = $v_ph['done_date'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['create_date'] = $v_ph['create_date'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['course_title'] = $v_ph['course_title'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['extra_forms'] = '0';
		
					if(count($cnts[$v_ph['ipid']][$phone_date]) >= '3')
					{
						$phones2dates[$v_ph['ipid']][$phone_date]['2']['extra_forms'] += '1'; //show the remaining
					}
		
					foreach($phones2dates[$v_ph['ipid']] as $k_ph_dates => $v_ph_dates)
					{
						$phones2dates[$v_ph['ipid']][$k_ph_dates] = array_values($v_ph_dates);
					}
		
					$cnts[$v_ph['ipid']][$phone_date][] = $v_ph['id'];
		
				}
			}
			/* ----------------------- get Telefon  END ---------------------------*/
		
			/* ----------------------- get form saved data START---------------------------*/
			$hospiz_control_data = HospizControl::get_multiple_hospiz_controlsheet($invoices_patients, $selected_period['start']);
			/* ----------------------- get form saved data END---------------------------*/
			 	
			/* ----------------------- get Assesment days Start ---------------------------*/
			foreach($invoices_patients as $k=> $p_ipid){
				$patient_dates[$p_ipid]['app_admissions'] = $patient_days[$p_ipid]['admission_days'];
				$patient_dates[$p_ipid]['hopsiz_admission'] = $patient_days[$p_ipid]['hospiz']['admission'];
					
				if(!empty($hospiz_control_data[$p_ipid]['assessment'])){
					foreach($hospiz_control_data[$p_ipid]['assessment'] as $day =>$value){
						if($value == "1"){
							$saved_assesment_days[$p_ipid][] = $day;
							$assessment_date = date('d.m.Y', strtotime($day));
							$ass_actions[$p_ipid][$assessment_date]['start_date'] = $day;
							$ass_actions[$p_ipid][$assessment_date]['end_date'] = date('Y-m-d 23:59:59',strtotime($day));
							$ass_actions[$p_ipid][$assessment_date]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($day))][0]['assessment']['dta_id'];
							$ass_actions[$p_ipid][$assessment_date]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($day))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$assessment_date]['price'] = $master_price_list[date('Y-m-d', strtotime($day))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$assessment_date]['name'] = $this->view->translate("shortcut_name_assessment");
							$ass_actions[$p_ipid][$assessment_date]['action_type'] = "assessment";
						}
					}
				} else{
					foreach($patient_days[$p_ipid]['hospiz']['admission'] as $k=> $h_adates){
						if(in_array($h_adates,$patient_days[$p_ipid]['admission_days'])){
							$system_assesment_days[$p_ipid][] = $h_adates;
							$sys_assessment_date = date('d.m.Y', strtotime($h_adates));
							$ass_actions[$p_ipid][$sys_assessment_date]['start_date'] = $h_adates;
							$ass_actions[$p_ipid][$sys_assessment_date]['end_date'] = date('Y-m-d 23:59:59',strtotime($h_adates));
							$ass_actions[$p_ipid][$sys_assessment_date]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($h_adates))][0]['assessment']['dta_id'];
							$ass_actions[$p_ipid][$sys_assessment_date]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($h_adates))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$sys_assessment_date]['price'] = $master_price_list[date('Y-m-d', strtotime($h_adates))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$sys_assessment_date]['name'] = $this->view->translate("shortcut_name_assessment");
							$ass_actions[$p_ipid][$sys_assessment_date]['action_type'] = "assessment";
						}
					}
				}
			}
			/* ----------------------- get Assesment days End ---------------------------*/
		
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				ksort($actions[$v_ipid]);
			}
				
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($actions[$v_ipid] as $date => $action){
					if( in_array(date('Y-m-d',strtotime($date)), $current_period_days) &&
							in_array(date('Y-m-d',strtotime($date)), $patients_sapv_days[$v_ipid]) &&
							in_array(date('d.m.Y',strtotime($date)), $patient_days[$v_ipid]['hospiz']['real_days'])) {
									
								$all_actions[$v_ipid][date('d.m.Y',strtotime($date))][] = $action;
		
								if(count($days_actions[$v_ipid][date('d.m.Y',strtotime($date))]) < 3 ){
									$days_actions[$v_ipid][date('d.m.Y',strtotime($date))][] = $action;
								}
							}
				}
			}
		
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($ass_actions[$v_ipid] as $as_date => $as_action){
					if( in_array(date('Y-m-d',strtotime($as_date)),$current_period_days) &&
							in_array(date('Y-m-d',strtotime($as_date)), $patients_sapv_days[$v_ipid]) &&
							in_array(date('d.m.Y',strtotime($as_date)), $patient_days[$v_ipid]['hospiz']['real_days'])) {
									
								$all_actions[$v_ipid][date('d.m.Y',strtotime($as_date))][] = $as_action;
								$days_actions[$v_ipid][date('d.m.Y',strtotime($as_date))][] = $as_action;
							}
				}
			}
				
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				ksort($days_actions[$v_ipid]);
			}
				
			$patient_data['actions'] = $days_actions;
			$patient_data['sapv'] = $sapv_data;
		
			return $patient_data;
		}
		
		private function get_bre_hospiz_related_actions($clientid, $invoices_patients, $selected_period,$patients_invoices_periods)
		{
			$patientmaster = new PatientMaster();
			//Client Hospital Settings START
			$conditions['periods'][0]['start'] = $selected_period['start'];
			$conditions['periods'][0]['end'] = $selected_period['end'];
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);

			$current_period_days = $patientmaster->getDaysInBetween($selected_period['start'], $selected_period['end']);

			/* ----------------------- get patients sapv days START ---------------------------*/
			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $invoices_patients)
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();

			$s = 0;
			foreach($droparray as $keys => $sapv)
			{
				
				$r1['start'][$sapv['ipid']][$s] = "";
				$r1['end'][$sapv['ipid']][$s] = "";
					
				$r2['start'][$sapv['ipid']][$s] = "";
				$r2['end'][$sapv['ipid']][$s] = "";
				
				
				
				if($sapv['status'] == 1 && ($sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
					// no sapv taken here - becouse it is considered to be fully denied
				}
				else
				{
					
					
					$r1['start'][$sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($sapv['verordnungam'])));
					$r1['end'][$sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($sapv['verordnungbis'])));
					
					$r2['start'][$sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $sapv['ipid'] ]['start']);
					$r2['end'][$sapv['ipid']][$s] = strtotime($patients_invoices_periods[$sapv['ipid']]['end']);
					
					if(Pms_CommonData::isintersected($r1['start'][$sapv['ipid']][$s], $r1['end'][$sapv['ipid']][$s], $r2['start'][$sapv['ipid']][$s] , $r2['end'][$sapv['ipid']][$s])  )
					{
						
					if($sapv['status'] == '1' && $sapv['verorddisabledate'] != '0000-00-00 00:00:00' && $sapv['verorddisabledate'] != '1970-01-01 00:00:00')
					{
						$sapv ['verordnungbis'] = date('Y-m-d', strtotime($sapv['verorddisabledate']));
					} 
					
					$patients_sapv_data [$sapv ['ipid']] ['sapv_days'] [] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($sapv ['verordnungam'])), date("Y-m-d", strtotime($sapv ['verordnungbis'])), false);

					//aditional data from sapv which was added on 16.10.2014
					if($sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($sapv['approved_date'])) != '1970-01-01' && $sapv['status'] == '2')
					{
						$sapv_data[$sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($sapv['approved_date']));
					}
					
					if(strlen($sapv['approved_number']) > 0 && $sapv['status'] != 1 ){
						$sapv_data[$sapv['ipid']][$s]['approved_number'] = $sapv['approved_number'];
					} else{
						$sapv_data[$sapv['ipid']][$s]['approved_number'] = "99999";
					}
					
					
					//aditional data from sapv which was added on 31.10.2014
					$sapv_data[$sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($sapv['verordnungam']));
					$sapv_data[$sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($sapv['verordnungbis']));
					$sapv_data[$sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($sapv['create_date']));
					
					$s++;
					
					}
				}
			}
					
			foreach($patients_sapv_data as $ipid => $sapvdata)
			{
				foreach($sapvdata ['sapv_days'] as $group => $spvdata)
				{
					foreach($spvdata as $key => $daysapv)
					{
						if(in_array(date('d.m.Y', strtotime($daysapv)), $patient_days [$ipid]['real_active_days'])){
							$patients_sapv_days [$ipid] [] = $daysapv;
						}
					}
				}
			}
			
			
			if($_REQUEST['dbgz']){
				print_r(" \n patients sapv days \n ");
				print_r($patients_sapv_days);
				print_r(" \n  \n ");
			}
			
			/* ----------------------- get patients sapv days END ---------------------------*/
			
			/* ----------------------- get client users START ---------------------------*/
			$user = new User();
			$c_users = $user->getUserByClientid($clientid, 0, true);

			foreach($c_users as $k_c_users => $v_c_users)
			{
				$client_users[$v_c_users['id']] = $v_c_users;
			}

			/* ----------------------- get shortcuts and saved pricelist or default pricelist START ---------------------------*/
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($selected_period['start'], $selected_period['end']); //get bre hospiz pricelist and then shortcuts
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			/* ----------------------- get shortcuts and saved pricelist or default pricelist END ---------------------------*/
			/* ----------------------- get contact forms START ---------------------------*/
			
			//get deleted cf from patient course
			$deleted_cf = Doctrine_Query::create()
				->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('wrong=1')
				->andWhereIn("ipid", $invoices_patients)//TODO-3761 Ancuta 19.01.2021  
				->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
				->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'")
				->andWhere('source_ipid = ""');
			$deleted_cf_array = $deleted_cf->fetchArray();

			$excluded_cf_ids[] = '99999999999';
			foreach($deleted_cf_array as $k_dcf => $v_dcf)
			{
				$excluded_cf_ids[] = $v_dcf['recordid'];
			}

			//get cf in period - deleted cf
			$cf = new ContactForms();
			$p_contactforms = $cf->get_multiple_contact_form_period($invoices_patients, false, $excluded_cf_ids);

			$contact_forms_ids[] = '9999999999999';
			foreach($p_contactforms as $kk_cf => $vv_cf)
			{
				$contact_forms_ids[] = $vv_cf['id'];
			}

			$cnt = array();
			foreach($p_contactforms as $k_ccf => $v_ccf)
			{
				foreach($v_ccf as $k_cf => $v_cf)
				{
					$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));

					$actions[$k_ccf][$v_cf['start_date']] = $v_cf;
					$actions[$k_ccf][$v_cf['start_date']]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($v_cf['start_date']))][0]['visit']['dta_id'];
					$actions[$k_ccf][$v_cf['start_date']]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($v_cf['start_date']))][0]['visit']['price'];
					$actions[$k_ccf][$v_cf['start_date']]['price'] = $master_price_list[date('Y-m-d', strtotime($v_cf['start_date']))][0]['visit']['price'];
					$actions[$k_ccf][$v_cf['start_date']]['name'] = $this->view->translate("shortcut_name_visit");
					$actions[$k_ccf][$v_cf['start_date']]['action_type'] = "visit";
					
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['id'] = $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['start_date'] = $v_cf['start_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['end_date'] = $v_cf['end_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['del_id'] = 'cf_' . $v_cf['id'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['date'] = $v_cf['billable_date'];
					$contact_forms2dates[$k_ccf][$visit_date][$v_cf['id']]['extra_forms'] = '0';

					if(count($cnt[$k_ccf][$visit_date]) >= '3')
					{
						$contact_forms2dates[$k_ccf][$visit_date]['2']['extra_forms'] += '1'; //show the remaining
					}

					foreach($contact_forms2dates[$k_ccf] as $k_cf_dates => $v_cf_dates)
					{
						$contact_forms2dates[$k_ccf][$k_cf_dates] = array_values($v_cf_dates);
					}

					$cnt[$k_ccf][$visit_date][] = $v_cf['id'];
					
				}
			}
			$visits_array = $contact_forms2dates;
			/* ----------------------- get contact forms END ---------------------------*/

			/* ----------------------- get Telefon START ---------------------------*/
			
			$tel = Doctrine_Query::create()
			->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
			->from('PatientCourse')
			->where('wrong!=1')
			->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("XT")) . '"')
			->andWhereIn('ipid', $invoices_patients)
			->andWhere('source_ipid = ""')
			->orderBy("convert(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
			$tel_array = $tel->fetchArray();
			
			$cnts = array();
			$course_title = array();
			$time = "0";
			foreach($tel_array as $k_ph => $v_ph)
			{
				$phone_date = date('Y-m-d', strtotime($v_ph['done_date']));

				if(in_array($phone_date,$current_period_days)){
					
					$course_title = explode("|", $v_ph['course_title']);
					if(count($course_title) == 3)
					{
					    //method implemented with 3 inputs
					    $time = $course_title[0];
					}
					else if(count($course_title) != 3 && count($course_title) < 3)
					{
					    //old method before anlage 10
					    $time = $course_title[0];
					}
					else if(count($course_title) != 3 && count($course_title) > 3)
					{
					    //new method (U) 3 inputs and 1 select newly added in verlauf
					    $time = $course_title[1];
					}
					
					$actions[$v_ph['ipid']][$v_ph['done_date']] = $v_ph;
					$actions[$v_ph['ipid']][$v_ph['done_date']]['start_date'] = $v_ph['done_date'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['end_date'] = date('Y-m-d H:i:s', strtotime('+'.$time.' minutes' ,strtotime($v_ph['done_date'])));
					$actions[$v_ph['ipid']][$v_ph['done_date']]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($v_ph['done_date']))][0]['phone']['dta_id'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($v_ph['done_date']))][0]['phone']['price'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['price'] = $master_price_list[date('Y-m-d', strtotime($v_ph['done_date']))][0]['phone']['price'];
					$actions[$v_ph['ipid']][$v_ph['done_date']]['name'] = $this->view->translate("shortcut_name_phone");
					$actions[$v_ph['ipid']][$v_ph['done_date']]['action_type'] = "phone";
					
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['id'] = $v_ph['id'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['start_date'] = $v_ph['done_date'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['end_date'] = $v_ph['done_date'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['create_date'] = $v_ph['create_date'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['course_title'] = $v_ph['course_title'];
					$phones2dates[$v_ph['ipid']][$phone_date][$v_ph['id']]['extra_forms'] = '0';
				
					if(count($cnts[$v_ph['ipid']][$phone_date]) >= '3')
					{
						$phones2dates[$v_ph['ipid']][$phone_date]['2']['extra_forms'] += '1'; //show the remaining
					}
				
					foreach($phones2dates[$v_ph['ipid']] as $k_ph_dates => $v_ph_dates)
					{
						$phones2dates[$v_ph['ipid']][$k_ph_dates] = array_values($v_ph_dates);
					}
				
					$cnts[$v_ph['ipid']][$phone_date][] = $v_ph['id'];
				
				}
			}
			/* ----------------------- get Telefon  END ---------------------------*/

			/* ----------------------- get form saved data START---------------------------*/
			$hospiz_control_data = HospizControl::get_multiple_hospiz_controlsheet($invoices_patients, $selected_period['start']);
			/* ----------------------- get form saved data END---------------------------*/
			
			/* ----------------------- get Assesment days Start ---------------------------*/
			foreach($invoices_patients as $k=> $p_ipid){
				$patient_dates[$p_ipid]['app_admissions'] = $patient_days[$p_ipid]['admission_days'];
				$patient_dates[$p_ipid]['hopsiz_admission'] = $patient_days[$p_ipid]['hospiz']['admission'];
					
				if(!empty($hospiz_control_data[$p_ipid]['assessment'])){
					foreach($hospiz_control_data[$p_ipid]['assessment'] as $day =>$value){
						if($value == "1"){
							$saved_assesment_days[$p_ipid][] = $day;
							$assessment_date = date('d.m.Y', strtotime($day)); 
							$ass_actions[$p_ipid][$assessment_date]['start_date'] = $day;
							$ass_actions[$p_ipid][$assessment_date]['end_date'] = date('Y-m-d 23:59:59',strtotime($day)); 
							$ass_actions[$p_ipid][$assessment_date]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($day))][0]['assessment']['dta_id'];
							$ass_actions[$p_ipid][$assessment_date]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($day))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$assessment_date]['price'] = $master_price_list[date('Y-m-d', strtotime($day))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$assessment_date]['name'] = $this->view->translate("shortcut_name_assessment");
							$ass_actions[$p_ipid][$assessment_date]['action_type'] = "assessment";
						}
					}
				} else{
					foreach($patient_days[$p_ipid]['hospiz']['admission'] as $k=> $h_adates){
						if(in_array($h_adates,$patient_days[$p_ipid]['admission_days'])){
							$system_assesment_days[$p_ipid][] = $h_adates;
							$sys_assessment_date = date('d.m.Y', strtotime($h_adates));
							$ass_actions[$p_ipid][$sys_assessment_date]['start_date'] = $h_adates; 
							$ass_actions[$p_ipid][$sys_assessment_date]['end_date'] = date('Y-m-d 23:59:59',strtotime($h_adates)); 
							$ass_actions[$p_ipid][$sys_assessment_date]['dta_id'] = $master_price_list[date('Y-m-d', strtotime($h_adates))][0]['assessment']['dta_id'];
							$ass_actions[$p_ipid][$sys_assessment_date]['dta_price'] = $master_price_list[date('Y-m-d', strtotime($h_adates))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$sys_assessment_date]['price'] = $master_price_list[date('Y-m-d', strtotime($h_adates))][0]['assessment']['price'];
							$ass_actions[$p_ipid][$sys_assessment_date]['name'] = $this->view->translate("shortcut_name_assessment");
							$ass_actions[$p_ipid][$sys_assessment_date]['action_type'] = "assessment";
						}
					}
				}
			}
			/* ----------------------- get Assesment days End ---------------------------*/

			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				ksort($actions[$v_ipid]); 
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($actions[$v_ipid] as $date => $action){
					if( in_array(date('Y-m-d',strtotime($date)), $current_period_days) &&
					    in_array(date('Y-m-d',strtotime($date)), $patients_sapv_days[$v_ipid]) && 
					    in_array(date('d.m.Y',strtotime($date)), $patient_days[$v_ipid]['hospiz']['real_days'])) {
					   	
						$all_actions[$v_ipid][date('d.m.Y',strtotime($date))][] = $action;

						if(count($days_actions[$v_ipid][date('d.m.Y',strtotime($date))]) < 3 ){
							$days_actions[$v_ipid][date('d.m.Y',strtotime($date))][] = $action;
						}
					} 
				}
			}

			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($ass_actions[$v_ipid] as $as_date => $as_action){
					if( in_array(date('Y-m-d',strtotime($as_date)),$current_period_days) &&
						in_array(date('Y-m-d',strtotime($as_date)), $patients_sapv_days[$v_ipid]) &&
						in_array(date('d.m.Y',strtotime($as_date)), $patient_days[$v_ipid]['hospiz']['real_days'])) {
							
						$all_actions[$v_ipid][date('d.m.Y',strtotime($as_date))][] = $as_action;
						$days_actions[$v_ipid][date('d.m.Y',strtotime($as_date))][] = $as_action;
					} 
				}
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				ksort($days_actions[$v_ipid]);
			}
			
			
			foreach($days_actions as $p_ipid => $adate){
				foreach ($adate as $key_date => $action_details){
					$final_patient_action_days[$p_ipid]['actions'][] = $key_date;
				}
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($sapv_data[$v_ipid] as $ks => $s_data)
				{
					foreach($final_patient_action_days[$v_ipid]['actions'] as $k=>$act_days){
							
						$r1start = strtotime(date('Y-m-d', strtotime($s_data['sapv_start'])));
						$r1end = strtotime(date('Y-m-d', strtotime($s_data['sapv_end'])));
							
						$r2start = strtotime($act_days);
						$r2end = strtotime($act_days);
							
						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)  )
						{
							if(!in_array($act_days,$sapv_data[$v_ipid][$ks]['actions'])){
								$sapv_data[$v_ipid][$ks]['actions'][] = $act_days;
							}
						}
					}
				}
			}
				
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($sapv_data[$v_ipid] as $ks => $s_data)
				{
					$s_data['actions'] = array_unique($s_data['actions']);
					asort($s_data['actions']);
						
					if( empty($s_data['approved_date'])   || strlen($s_data['approved_date']) == 0 ){
						$sapv_data[$v_ipid][$ks]['approved_date'] = $s_data['actions'][0];
					}
				}
			}
			
				
			foreach($sapv_data as $k_ipid =>$sapvdata){
				$sapv_data[$k_ipid] = end($sapvdata);
			}

			$patient_data['actions'] = $days_actions;
			$patient_data['sapv'] = $sapv_data;
			
			return $patient_data;
		}

		private function generate_dta_xml($data_array)
		{
			$xml_sapv = $this->toXml($data_array, null, null, 'data');
			$xml_string = $this->xmlpp($xml_sapv, false);

			//download xml
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: text/xml; charset=utf-8");
			header("Content-Disposition: attachment; filename=dta.xml");
 			ob_clean();// ISPC-2461 Added by Ancuta - 18.11.2019 - Check for issues !
			echo $xml_string;
		}

		private function toXml($data, $rootNodeName = 'data', $xml = null, $elem_root = 'element', $xsd_file = false)
		{
			// turn off compatibility mode as simple xml throws a wobbly if you don't.
			if(ini_get('zend.ze1_compatibility_mode') == 1)
			{
				ini_set('zend.ze1_compatibility_mode', 0);
			}

			if($xml == null)
			{
				$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><invoices/>');
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

				// replace anything not alpha numeric
				//find out if key is special
				$special_key = explode('_', $key);
				if(count($special_key) == '2' && is_numeric($special_key[1]))
				{
					$key = $special_key[0];
				}

				// if there is another array found recrusively call this function
				if(is_array($value))
				{
					$node = $xml->addChild($key);
					// recrusive call.
					DtaController::toXml($value, $rootNodeName, $node);
				}
				else
				{
					// add single node.
					$value = htmlspecialchars($value);
					$xml->addChild($key, $value);
				}
			}
			// pass back as string. or simple xml object if you want!
			return $xml->asXML();
		}

		/** Prettifies an XML string into a human-readable string
		 *  @param string $xml The XML as a string
		 *  @param boolean $html_output True if the output should be escaped (for use in HTML)
		 */
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
			return ($html_output) ? htmlspecialchars($xml) : $xml;
		}

		//DTA LOCATIONS (DTA ORTE)
		public function listlocationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$dta_location_form = new Application_Form_DtaLocations();

			if($_REQUEST['flg'] == 'suc')
			{
				$this->view->message_error = $this->view->translate('updatedsuccessfully');
			}
			else if($_REQUEST['flg'] == 'err')
			{
				$this->view->message_error = $this->view->translate('updateerror');
			}
			else if($_REQUEST['flg'] == 'delsuc')
			{
				$this->view->message_error = $this->view->translate('removedsuccessfully');
			}
			else if($_REQUEST['flg'] == 'delerr')
			{
				$this->view->message_error = $this->view->translate('removeerror');
			}

			//delete location procedure here
			if($_REQUEST['op'] == 'del' && trim(rtrim($_REQUEST['lid'])) > '0' && $clientid > '0')
			{
				$recordid = trim(rtrim($_REQUEST['lid']));
				if($dta_location_form->delete($clientid, $recordid))
				{
					$this->redirect(APP_BASE . 'dta/listlocations?flg=delsuc');
					exit;
				}
				else
				{
					$this->redirect(APP_BASE . 'dta/listlocations?flg=delerr');
					exit;
				}
			}
		}

		public function fetchlocationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			$location_types = Locations::getLocationTypes();
			$this->view->location_types = $location_types;

			$columnarray = array(
				"id" => "id",
				"name" => "name",
			);

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$fdoc = Doctrine_Query::create()
				->select("count(*)")
				->from('DtaLocations')
				->where("isdelete = 0 " . $where)
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			//used in pagination of search results
			$fdoc->andWhere("name LIKE '%" . $_REQUEST['val'] . "%'");
			$fdocarray = $fdoc->fetchArray();

			$limit = 50;
			$fdoc->select("*");
			$fdoc->where("isdelete = 0 " . $where . "");
			$fdoc->andWhere("name LIKE '%" . $_REQUEST['val'] . "%'");
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());

			$dta_ids[] = '99999999999';
			foreach($fdoclimit as $k_dta => $v_dta)
			{
				$dta_ids[] = $v_dta['id'];
			}

			if($fdocarray)
			{
				//get all dtas selected locations
				$dta_locations_selected = Locations2dta::get_location2dta_multiple($dta_ids);
				foreach($dta_locations_selected as $k_dta2loc => $v_dta2loc)
				{
					$dta2locations[$v_dta2loc['dta']][] = $location_types[$v_dta2loc['location']];
				}

				$this->view->dta2locations = $dta2locations;
			}

			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtalocationslist.html");
				$this->view->locations_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("dtalocationsnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->locations_grid = '<tr><td colspan="4" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['locationslist'] = $this->view->render('dta/fetchlocations.html');

			echo json_encode($response);
			exit;
		}

		public function addlocationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			$location_types = Locations::getLocationTypes();
			$this->view->location_types = $location_types;

			$client_sel_locations = DtaLocations::get_client_selected_locations();

			foreach($client_sel_locations as $k_dta => $v_dta)
			{
				$client_selected_locations[] = $v_dta['location'];
			}

			$this->view->selected_client_locations = $client_selected_locations;

			if($this->getRequest()->isPost())
			{
				$post = $_POST;
				$dta_location_form = new Application_Form_DtaLocations();

				if($dta_location_form->validate($post))
				{
					$dta_location_form->insert($clientid, $post);
					$this->redirect(APP_BASE . 'dta/listlocations?flg=suc');
					exit;
				}
				else
				{
					$this->retainValues($post);
				}
			}
		}

		public function editlocationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;
			$dta_locations = new DtaLocations();

			$location_types = Locations::getLocationTypes();
			$this->view->location_types = $location_types;

			if(trim(rtrim($_REQUEST['lid'])) > '0')
			{
				$recordid = $_REQUEST['lid'];
			}
			else
			{
				$this->redirect(APP_BASE . 'dta/listlocations?flg=invloc');
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$post = $_POST;
				$dta_location_form = new Application_Form_DtaLocations();

				if($dta_location_form->validate($post) && $recordid > '0')
				{
					$dta_location_form->update($recordid, $post);
					$this->redirect(APP_BASE . 'dta/listlocations?flg=suc');
					exit;
				}
				else
				{
					$this->retainValues($post);
				}
			}

			$location_data = $dta_locations->get_location($recordid);

			$client_sel_locations = $dta_locations->get_client_selected_locations();

			foreach($client_sel_locations as $k_dta => $v_dta)
			{
				$client_selected_locations[] = $v_dta['location'];
			}

			$this->view->selected_client_locations = $client_selected_locations;

			$curent_sel_loc = Locations2dta::get_dta_locations($recordid);

			foreach($curent_sel_loc as $k_loc => $v_loc)
			{
				$curent_sel_locations[] = $v_loc['location'];
			}
			$this->view->curent_sel_locations = $curent_sel_locations;

			if($location_data)
			{
				$this->retainValues($location_data);
			}
		}

		private function map_locations2dta($invoices_patients = false, $patients_locations = false, $dta_locations = false)
		{
			//print_r($patients_locations);
			//print_r($dta_locations);
			//exit;
			if($invoices_patients && $patients_locations && $dta_locations)
			{
				$patientmaster = new PatientMaster();

				foreach($invoices_patients as $k_ipid => $v_ipid)
				{
					foreach($patients_locations[$v_ipid] as $k_loc => $v_loc)
					{
						//fresh start
						$start = '';
						$end = '';

						$start = date('Y-m-d', strtotime($v_loc['valid_from']));

						if($v_loc['valid_till'] != '0000-00-00 00:00:00')
						{
							$end = date('Y-m-d', strtotime($v_loc['valid_till']));
						}
						else
						{
							$end = date('Y-m-d', time());
						}

						//fresh start every loop
						$pat_location_days[$v_ipid] = array();
						$pat_location_days[$v_ipid] = $patientmaster->getDaysInBetween($start, $end);

						foreach($pat_location_days[$v_ipid] as $k_incr => $v_day)
						{
							$patient_days_location_master[$v_ipid][$v_day] = $dta_locations[$v_loc['master_location_type']];
						}
					}
				}

				if($patient_days_location_master)
				{
					return $patient_days_location_master;
				}
				else
				{
					return false;
				}
			}
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		/* ----------- bre hospiz  DTA STUFF HERE --------------------- */

		public function listdtahospizinvoicesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
		
			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}
		
			
			$storned_invoices = BreHospizInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			
			//construct months array in which the curent client has bre_invoices completed, not paid
			$months_q = Doctrine_Query::create()
			->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
			->from('BreHospizInvoices')
			->where("isdelete = 0")
			->andWhere('completed_date != "0000-00-00 00:00:00"')
			->andWhere("storno = 0 " . $where)
			->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
			->andWhereIN("status",$unpaid_status) // display only unpaid
			->orderBy('DISTINCT DESC');
			$months_res = $months_q->fetchArray();
		
			if($months_res)
			{
				//current month on top
				$months_array[date('Y-m', time())] = date('m-Y', time());
				foreach($months_res as $k_month => $v_month)
				{
					$months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
				}
		
				$months_array = array_unique($months_array);
			}
		
			if(strlen($_REQUEST['search']) > '0')
			{
				$selected_period['start'] = $_REQUEST['search'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
			}
		
			$this->view->months_array = $months_array;
		
			if($this->getRequest()->isPost())
			{
				$post = $_POST;
		
				$dta_data = $this->gather_dta_hospiz_data($clientid, $userid, $post);
				$this->generate_dta_xml($dta_data);
				exit;
			}
		}
		
		public function fetchdtahospizinvoiceslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;
		
			$columnarray = array(
					"pat" => "epid_num",
					"invnr" => "invoice_number",
					"invstartdate" => "invoice_start",
					"invdate" => "completed_date_sort",
					"invtotal" => "invoice_total",
					"invkasse" => "company_name", // used in first order of health insurances
			);
		
			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}
		
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		
			$client_users_res = User::getUserByClientid($clientid, 0, true);
		
			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
			}
		
			$this->view->client_users = $client_users;
		
			//get patients data used in search and list
			$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
			$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
 
			$f_patient = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->where("p.isdelete =0")
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.clientid = ' . $clientid);
		
			if($_REQUEST['clm'] == 'pat')
			{
				$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
		
			$f_patients_res = $f_patient->fetchArray();
		
			$f_patients_ipids[] = '9999999999999';
			foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
			{
				$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
				$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
			}
		
			$this->view->client_patients = $client_patients;
		
			if(strlen($_REQUEST['val']) > '0')
			{
				$selected_period['start'] = $_REQUEST['val'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}
			else
			{
				$selected_period['start'] = date('Y-m-01', time());
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}
		
			//order by health insurance
			if($_REQUEST['clm'] == "invkasse")
			{
				$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		
				$drop = Doctrine_Query::create()
				->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
				->from('PatientHealthInsurance')
				->whereIn("ipid", $f_patients_ipids)
				->orderBy($orderby);
				$droparray = $drop->fetchArray();
		
				$f_patients_ipids = array();
				foreach($droparray as $k_pat_hi => $v_pat_hi)
				{
					$f_patients_ipids[] = $v_pat_hi['ipid'];
				}
			}
		
			
			$storned_invoices = BreHospizInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			
			$fdoc = Doctrine_Query::create()
			->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
			->from('BreHospizInvoices')
			->where("isdelete = 0 " . $where)
			->andWhere("storno = '0'")
			->andWhere('completed_date != "0000-00-00 00:00:00"')
			->andWhereIn('ipid', $f_patients_ipids)
			->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
			->andWhereIN("status",$unpaid_status) // display only unpaid
			->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
			if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
			{
				$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
			else
			{
				//sort by patient sorted ipid order
				$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
			}
		
			//used in pagination of search results
			$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
// 						print_r($fdoc->getSqlQuery());
// 						exit;
			$fdocarray = $fdoc->fetchArray();
			$limit = 500;
			$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
			$fdoc->where("isdelete = 0 " . $where . "");
			$fdoc->andWhere("storno = '0'");
			$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
			$fdoc->andWhereIn('ipid', $f_patients_ipids);
			$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
			$fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
			$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);
		
			if($_REQUEST['dbgq'])
			{
				print_r($fdoc->getSqlQuery());
				print_r($fdoc->fetchArray());
		
				exit;
			}
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		
			//get ipids for which we need health insurances
			foreach($fdoclimit as $k_inv => $v_inv)
			{
				$inv_ipids[] = $v_inv['ipid'];
			}
		
			$inv_ipids[] = '99999999999999';
		
		
			//6. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		
			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}
		
			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		
			
			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}
		
				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
			}
			$this->view->healthinsurances = $healthinsu;
		
		
			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtahospizinvoiceslist.html");
				$this->view->templates_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("dtahospizinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}
		
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtahospizinvoiceslist.html');
		
			echo json_encode($response);
			exit;
		}
		
		/*
		 * required data from invoices:
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) IK number of client
		 *
		 * (X) invoice ammount
		 *
		 * (X) Administration -> Mandanten -> Team Name
		 * (X) Administration -> Mandanten -> first name last name
		 * (X) Administration -> Mandante -> Telefon
		 * (X) Administration -> Mandante -> Mobile phone (fax instead)
		 *
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) Invoice number followed by ":0"
		 * (X) invoice date (YYYYMMDD)
		 *
		 * (X) health insurance number (Versichertennummer) of patient
		 *
		 * (X) last name patient
		 * (X) first name patient
		 * (X) birthday patient (YYYYMMDD)
		 * (X) street and no. patients address
		 * (X) ZIP patients address
		 * (X) City patients address
		 *
		 * (X) month of delivery (YYYYMM)
		 * (X) Pflegestufe of patients stammdaten
		 *
		 * (X) day of month from the invoiced action (01-31) or 99 if action is a flat action
		 * (X) start time of billable performance (HHMM)
		 * (X) single price of action
		 * (X) end time of billable performance (HHMM)
		 * (X) ammount of action (Format: 9999,99) (normally "001,00")
		 * (X) overall invoice ammount
		 * (X) overall invoice ammount
		 *
		 *
		 * L.E:
		 * 10.10.2014 - changed to load dta_id, dta_name based on action(visit) dta_location(mapped with patient location)
		 *
		 * L.E:
		 * 16.10.2014 - added user BSNR (Betriebsstättennummer), Genehmigungskennzeichen, Datum der Genehmigung (from verordnung)
		 *
		 * L.E TODO->:
		 * 31.10.2014 - added date of first admision, sapv (start, end, created) date, sapv GENEHMIUNGSNUMMER and date, icd (code and text) main, healthinsurance name
		 *
		 * L.E.:
		 * 07.04.2015 - changed Betriebsstättennummer to use the client one not tht patient one(bug ISPC-1012)
		 */
		
		private function gather_dta_hospiz_data($clientid, $userid, $post)
		{
			//1. get all selected invoices data
			$bre_hospiz_invoices = new BreHospizInvoices();
			$bre_hospiz_invoices_data = $bre_hospiz_invoices->get_multiple_bre_hospiz_invoices($post['invoices']['bre']);

			if ($bre_hospiz_invoices_data === false) {
				return array();
			}
			
			foreach($bre_hospiz_invoices_data as $k_inv => $v_inv)
			{
				$invoices_patients[] = $v_inv['ipid'];
		
				$invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
				$invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
				
				$patients_invoices_periods[$v_inv['ipid']] = $invoice_period;
				
			}
		
			//2. get all required client data
			$clientdata = Pms_CommonData::getClientData($clientid);
			$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
			$client_data['client']['team_name'] = $clientdata[0]['team_name'];
			$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
			$client_data['client']['phone'] = $clientdata[0]['phone'];
			$client_data['client']['fax'] = $clientdata[0]['fax'];
		
		
		
			//3. get pflegestuffe in current period
			$pflege = new PatientMaintainanceStage();
			$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		
			foreach($pflege_arr as $k_pflege => $v_pflege)
			{
				$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
			}
		
			foreach($grouped_pflege as $k_gpflege => $v_gpflege)
			{
				$last_pflege = end($v_gpflege);
		
				if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
				{
					//$k_gpflege = patient epid
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
				}
				else
				{
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
				}
			}
		
			//4. get all involved patients required data
			$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
		
			foreach($patient_details as $k_pat_ipid => $v_pat_details)
			{
				$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
				$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
				$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
				$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
				$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
				$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
				$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
			}
		
			//4.1 get patients readmission details
			$conditions['periods'][0]['start'] = '2009-01-01';
			$conditions['periods'][0]['end'] = date('Y-m-d');
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);
		
			foreach($patient_days as $k_patd_ipid => $v_pat_details)
			{
				$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
			}
		
			//5. pricelist
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($invoice_period['start'], $invoice_period['end']); //get bra sapv pricelist and then shortcuts
			$curent_pricelist = $master_price_list[$invoice_period['start']][0];
		
			//6. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
			
			$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
			// ispc = M => 1 = Versicherungspflichtige und -berechtigte
			// ispc = F => 3 = Familienversicherte
			// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
			//TODO-3528 Lore 12.11.2020
			$modules = new Modules();
			$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
			if($extra_healthinsurance_statuses){
			    $status_int_array += array(
			        "00" => "00",          //"Gesamtsumme aller Stati",
			        "11" => "11",          //"Mitglieder West",
			        "19" => "19",          //"Mitglieder Ost",
			        "31" => "31",          //"Angehörige West",
			        "39" => "39",          //"Angehörige Ost",
			        "51" => "51",          //"Rentner West",
			        "59" => "59",          //"Rentner Ost",
			        "99" => "99",          //"nicht zuzuordnende Stati",
			        "07" => "07",          //"Auslandsabkommen"
			    );
			}
			//.
			
			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}
		
			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		
			
			//RWH Start
				//get health insurance subdivizions
				$symperm = new HealthInsurancePermissions();
				$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
				
				if($divisions)
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
						->whereIn("company_id", $company_ids)
						->andWhereIn("ipid", $invoices_patients);
					$hi2s_arr = $hi2s->fetchArray();
				}
				
				if($hi2s_arr)
				{
					foreach($hi2s_arr as $k_subdiv => $v_subdiv)
					{
						if($v_subdiv['subdiv_id'] == "3")
						{
							$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
						}
					}
				}
			//RWH End
			
			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
					if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
					{
						$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
					}
		
					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}
		
				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		
				//Versichertennummer
				$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		
				//Institutskennzeichen
				$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
				
				//Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
				$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
				
				// Health insurance status - ISPC- 1368 // 150611
				$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
			}
		
// 			$patients_actions_data = $this->get_bre_hospiz_related_actions_150623($clientid, $invoices_patients, $invoice_period);
			$patients_actions_data = $this->get_bre_hospiz_related_actions($clientid, $invoices_patients, $invoice_period,$patients_invoices_periods);
			if($_REQUEST['dbgz'])
			{
				print_r($patients_actions_data);
				exit;
			}
			//7. get (HD) main diagnosis
			$main_abbr = "'HD'";
			$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		
			foreach($main_diag as $key => $v_diag)
			{
				$type_arr[] = $v_diag['id'];
			}
		
			$pat_diag = new PatientDiagnosis();
			$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		
			foreach($dianoarray as $k_diag => $v_diag)
			{
				//append diagnosis in patient data
				$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
				//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
				//ISPC-2489 Lore 26.11.2019
				$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
				
				$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
			}
		
			//			print_r($dianoarray);
			//			print_r($diano_arr);
 
			//8. get user data
			$user_details = User::getUserDetails($userid);
		
			//reloop the invoices data array
			foreach($bre_hospiz_invoices_data as $k_invoice => $v_invoice)
			{
				if(!$master_data['invoice_' . $k_invoice])
				{
					$master_data['invoice_' . $k_invoice] = array();
				}
		
				$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
		
				$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
				$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
				$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
				$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
				$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
				$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
				
				$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
				
				$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $patients_actions_data['sapv'][$v_invoice['ipid']]['sapv_start'];
				$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $patients_actions_data['sapv'][$v_invoice['ipid']]['sapv_end'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $patients_actions_data['sapv'][$v_invoice['ipid']]['approved_date'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $patients_actions_data['sapv'][$v_invoice['ipid']]['approved_number'];
				$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $patients_actions_data['sapv'][$v_invoice['ipid']]['create_date'];
				
				$k_item = 1;
				$inv_items = array();
						$inv_actions = array();
						$rd = 0;
						foreach($patients_actions_data['actions'][$v_invoice['ipid']] as $k_date => $v_actions){
							foreach($v_actions as $k_action => $v_action){
								$inv_actions['dta_id'] = $v_action['dta_id'];
								$inv_actions['dta_name'] = "";
								$inv_actions['name'] = $v_action['name'];
								$inv_actions['price'] = number_format($v_action['price'], '2', ',', '');
								$inv_actions['ammount'] = str_pad(number_format("1.00", '2', ',', ''), "7", "0", STR_PAD_LEFT);
								$inv_actions['day'] = date('Ymd', strtotime($v_action['start_date']));
								
// 								$inv_actions['month'] = date('m', strtotime($v_action['start_date']));
								
								$inv_actions['start_time'] = date('Hi', strtotime($v_action['start_date']));
			
								if(strlen($v_action['end_date']) > '0')
								{
									$inv_actions['end_time'] = date('Hi', strtotime($v_action['end_date']));
								} else{
									$inv_actions['end_time'] = "";
								}
// 								$inv_items['actions'][$k_date]['action_' . $k_date.'_'.$k_action] = $inv_actions;
								$inv_items['actions'][$k_date]['action_'.$k_action] = $inv_actions;
							}
							$master_data['invoice_' . $k_invoice]['items']['item_' . strtotime($k_date)]['actions'] = $inv_items['actions'][$k_date];
// 							$master_data['invoice_' . $k_invoice]['items']['day_' . $k_date] = $inv_items['actions'][$k_date];
							$rd++;	

						$inv_actions = array();
						}
						$inv_items = array();
				$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
			}
		
// 			print_R($master_data); 
// 			exit;
			
			return $master_data;
		}
		
		/* ----------- HE DTA STUFF HERE --------------------- */

		public function listdtaheinvoicesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$storned_invoices = HeInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			
			//construct months array in which the curent client has he_invoices completed, not paid
			$months_q = Doctrine_Query::create()
				->select('DISTINCT(DATE_FORMAT(completed_date, "%Y-%m")), DATE_FORMAT(completed_date, "%m-%Y") as completed_date_f')
				->from('HeInvoices')
				->where("isdelete = 0")
				->andWhere('completed_date != "0000-00-00 00:00:00"')
				->andWhere("storno = 0 " . $where)
				->andWhere('pricelist_type = "primar"')
				->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
				->andWhereIN("status",$unpaid_status) // display only unpaid
				->orderBy('DISTINCT DESC');
			
			$months_res = $months_q->fetchArray();
			
			if($months_res)
			{
				//current month on top
				$months_array[date('Y-m', time())] = date('m-Y', time());
				foreach($months_res as $k_month => $v_month)
				{
					$months_array[$v_month['DISTINCT']] = $v_month['completed_date_f'];
				}

				$months_array = array_unique($months_array);
			}

			if(strlen($_REQUEST['search']) > '0')
			{
				$selected_period['start'] = $_REQUEST['search'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
			}

			$this->view->months_array = $months_array;

			if($this->getRequest()->isPost())
			{
				$post = $_POST;

				$dta_data = $this->gather_he_dta_data($clientid, $userid, $post);
				$this->generate_dta_xml($dta_data);
				exit;
			}
		}

		public function fetchdtaheinvoiceslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			$columnarray = array(
				"pat" => "epid_num",
				"invnr" => "invoice_number",
				"invstartdate" => "invoice_start",
				"invdate" => "completed_date_sort",
				"invtotal" => "invoice_total",
				"invkasse" => "company_name", // used in first order of health insurances
			);

			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$client_users_res = User::getUserByClientid($clientid, 0, true);

			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
			}

			$this->view->client_users = $client_users;

			//get patients data used in search and list
			$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
			$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";

			$f_patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete =0")
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);

			if($_REQUEST['clm'] == 'pat')
			{
				$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}

			$f_patients_res = $f_patient->fetchArray();

			$f_patients_ipids[] = '9999999999999';
			foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
			{
				$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
				$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
			}

			$this->view->client_patients = $client_patients;

			if(strlen($_REQUEST['val']) > '0')
			{
				$selected_period['start'] = $_REQUEST['val'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}
			else
			{
				$selected_period['start'] = date('Y-m-01', time());
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}

			//order by health insurance
			if($_REQUEST['clm'] == "invkasse")
			{
				$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];

				$drop = Doctrine_Query::create()
					->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
					->from('PatientHealthInsurance')
					->whereIn("ipid", $f_patients_ipids)
					->orderBy($orderby);
				$droparray = $drop->fetchArray();

				$f_patients_ipids = array();
				foreach($droparray as $k_pat_hi => $v_pat_hi)
				{
					$f_patients_ipids[] = $v_pat_hi['ipid'];
				}
			}

			$storned_invoices = HeInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
				
			
			$fdoc = Doctrine_Query::create()
				->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
				->from('HeInvoices')
				->where("isdelete = 0 " . $where)
				->andWhere("storno = '0'")
				->andWhere('completed_date != "0000-00-00 00:00:00"')
				->andWhereIn('ipid', $f_patients_ipids)
				->andWhere("completed_date BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'")
				->andWhere('pricelist_type = "primar"')
				->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
				->andWhereIN("status",$unpaid_status) ;// display only unpaid
			
			if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
			{
				$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
			else
			{
				//sort by patient sorted ipid order
				$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
			}

			//used in pagination of search results
//			$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");

			$fdocarray = $fdoc->fetchArray();
			$limit = 500;
			$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
			$fdoc->where("isdelete = 0 " . $where . "");
			$fdoc->andWhere("storno = '0'");
			$fdoc->andWhere("DATE(completed_date) BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
			$fdoc->andWhereIn('ipid', $f_patients_ipids);
			$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
			$fdoc->andWhere('pricelist_type = "primar"');
			$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
			$fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			if($_REQUEST['dbgq'])
			{
				print_r($fdoc->getSqlQuery());
				print_r($fdoc->fetchArray());

				exit;
			}

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());

			//get ipids for which we need health insurances
			foreach($fdoclimit as $k_inv => $v_inv)
			{
				$inv_ipids[] = $v_inv['ipid'];
			}

			$inv_ipids[] = '99999999999999';


			//patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);

			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;

				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}

			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);

			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];

					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}

				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
			}
			$this->view->healthinsurances = $healthinsu;


			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtaheinvoiceslist.html");
				$this->view->templates_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("dtaheinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtaheinvoiceslist.html');

			echo json_encode($response);
			exit;
		}

		/*
		 * required data from invoices:
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) IK number of client
		 *
		 * (X) invoice ammount
		 *
		 * (X) Administration -> Mandanten -> Team Name
		 * (X) Administration -> Mandanten -> first name last name
		 * (X) Administration -> Mandante -> Telefon
		 * (X) Administration -> Mandante -> Mobile phone (fax instead)
		 *
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) Invoice number followed by ":0"
		 * (X) invoice date (YYYYMMDD)
		 *
		 * (X) health insurance number (Versichertennummer) of patient
		 *
		 * (X) last name patient
		 * (X) first name patient
		 * (X) birthday patient (YYYYMMDD)
		 * (X) street and no. patients address
		 * (X) ZIP patients address
		 * (X) City patients address
		 *
		 * (X) month of delivery (YYYYMM)
		 * (X) Pflegestufe of patients stammdaten
		 *
		 * (X) day of month from the invoiced action (01-31) or 99 if action is a flat action
		 * (X) start time of billable performance (HHMM)
		 * (X) single price of action
		 * (X) end time of billable performance (HHMM)
		 * (X) ammount of action (Format: 9999,99) (normally "001,00")
		 * (X) overall invoice ammount
		 * (X) overall invoice ammount
		 *
		 *
		 *
		 * L.E:
		 * 16.10.2014 - added user BSNR (Betriebsstättennummer), Genehmigungskennzeichen, Datum der Genehmigung (from verordnung)
		 *
		 * L.E TODO->:
		 * 31.10.2014 - added date of first admision, sapv (start, end, created) date, sapv GENEHMIUNGSNUMMER and date, icd (code and text) main, healthinsurance name
		 *
		 * L.E.:
		 * 07.04.2015 - changed Betriebsstättennummer to use the client one not tht patient one(bug ISPC-1012)
		 * 
		 * L.E.:
		 *  22.04.2015 - ISPC-1320 - HE DTA
		 * 
		 * L.E:
		 * 
		 */

		private function gather_he_dta_data($clientid, $userid, $post)
		{
			//1. get all selected invoices data
			$he_invoices = new HeInvoices();
			$he_invoices_data = $he_invoices->get_multiple_he_invoices($clientid,$post['invoices']['he']);

			if ($he_invoices_data === false) {
				return array();
			}
			foreach($he_invoices_data as $k_inv => $v_inv)
			{
				$invoices_patients[] = $v_inv['ipid'];

				$invoice_periods[$v_inv['ipid']] = array();

				$invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
				$invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));

				$patients_invoices_periods[$v_inv['ipid']] = $invoice_period;

				
				// ########################################################### 
				$items_sort = array();
				$items_sort_final = array();
				$first_item = 0;
				
				
				foreach($v_inv['items'] as $k_itm => $v_itm)
				{
					$items_sort[$v_itm['id']] = $v_itm['total'];	
				}	
				arsort($items_sort);
				$items_sort_final = $items_sort;
				$first_item = min($items_sort);

				
				$check = 0;
				$it_negative = 0;
				if($first_item < 0 ){
					foreach($items_sort as $it_key => $it){
						if($it < 0 ){
							$it_negative +=$it;
						}
					}
					$check = $it_negative;
					
					foreach($items_sort as $itt_key => $itt){
						if($itt > 0 ){
							$check = $itt + $check;
							if($check <= 0){
								$items_sort_final[$itt_key] = 0;
							} else{
								$items_sort_final[$itt_key] = $check;
								break;
							}
						}
					}
				}
				
				foreach($v_inv['items'] as $k_itm => $v_itm)
				{
					if($v_itm['custom'] != 2){
						
						$current_item['custom'] = $v_itm['custom'];
						$current_item['shortcut'] = array();
						$current_item['dta_shortcut'] = array();
						$current_item['shortcuts'] = array();
						$current_item['dta_shortcuts'] = array();
						
                        $current_item['dta_id'] = "dta_id_replace";
						$current_item['dta_name'] = html_entity_decode($v_itm['description'], ENT_QUOTES, "UTF-8");
						$string = $v_itm['description'];
	
						//some magic hack to extract the 4 multiplier from string like
						//&nbsp;PC12&nbsp;&nbsp;&nbsp;&nbsp;SAPV (Hospiz) &lt;=180 Tage ( 1 x 11.925,00)<br /> &nbsp;PC13&nbsp;&nbsp;&nbsp;&nbsp;SAPV (Hospiz) &gt; 180 Tage (+ 30 Tage) ( 4 x 1.800,00)
						$multiplier = "1";
						$multiplier = trim(substr($string, (strrpos($string, "(") + 1), (strrpos($string, "x") - strrpos($string, "(") - 1)));
	
						$current_item['dta_location'] = "";
	
// 						$current_item['price'] = number_format($v_itm['price'], "2", ",", "");
						$current_item['price'] = number_format($items_sort_final[$v_itm['id']], "2", ",", "");

						$current_item['ammount'] = str_pad(number_format($v_itm['qty'], "2", ",", ""), 7, "0", STR_PAD_LEFT);
						$current_item['percent'] = str_pad(number_format($v_itm['percent'], "2", ",", ""), 7, "0", STR_PAD_LEFT);
						
						
						if($v_itm['custom'] == 0){ // system generated
						    
							if(is_array($v_itm['from_date'])){
							    
    							if($v_itm['from_date'][0] != "0000-00-00 00:00:00"){
    								$current_item['day'] = date('Ymd', strtotime($v_itm['from_date'][0]));
    							} else {
    								$current_item['day'] = "";
    							}
    							
							} else {
							    
    							if($v_itm['from_date'] != "0000-00-00 00:00:00") {
    								$current_item['day'] = date('Ymd', strtotime($v_itm['from_date']));
    							} else {
    								$current_item['day'] = date('Ymd', strtotime($v_inv['invoice_start']));
    							}
    							
							}
							
						} else if($v_itm['custom'] == 1){ // added by user
							
							if($v_itm['from_date'] != "0000-00-00 00:00:00"){
								$current_item['day'] = date('Ymd', strtotime($v_itm['from_date']));
							}else{
								$current_item['day'] = date('Ymd', strtotime($v_inv['invoice_start']));
							}
							
						} else {
							$current_item['day'] = "";
						}
	 
						
						$current_item['start_time'] = "0000";
						$current_item['end_time'] = "2359";
	
						$actions_dates[$v_inv['ipid']][] = date('d.m.Y', strtotime($v_itm['from_date'][0])); 
						
						
						if(strpos($v_itm['shortcut'], "_") !== false)
						{
							$array_replacements = array("pa", "pc", "_");
							$short_number = str_replace($array_replacements, "", $v_itm['shortcut']);
	
							//find out if is pa or pc
							$prefix = "";
							if(strpos($v_itm['shortcut'], "pa") !== false)
							{
								$prefix = "pa";
								$prefix_dta = "a";
							}
							else if(strpos($v_itm['shortcut'], "pc") !== false)
							{
								$prefix = "pc";
								$prefix_dta = "c";
							}
	
	
							$current_item['multiplier'] = $multiplier;
							$current_item['shortcuts']['shortcut_0'] = $prefix . ($short_number - 1);
							$current_item['shortcuts']['shortcut_1'] = str_replace("_", "", $v_itm['shortcut']);
	
							$current_item['dta_shortcuts']['shortcut_0'] = $prefix_dta . ($short_number - 1);
							$current_item['dta_shortcuts']['shortcut_1'] = str_replace('p', '', str_replace("_", "", $v_itm['shortcut']));
						}
						else
						{
							$current_item['shortcut'] = $v_itm['shortcut'];
						}
	
						//remove "p" from shortcut string
						$current_item['dta_price'] = number_format($v_itm['price'], "2", ",", "");
	
						if(empty($current_item['dta_shortcuts']))
						{
							
							if($v_itm['custom'] == 1 && !empty($v_itm['related_shortcut']) && $v_itm['related_shortcut'] != "custom_dta"){
								$current_item['dta_shortcut'] = str_replace('p', '', $v_itm['related_shortcut']);
							} else{
								$current_item['dta_shortcut'] = str_replace('p', '', $v_itm['shortcut']);
							}
						}
						
						
                        $current_item['customdtaid'] = 0;
                        if($v_itm['custom'] == 1 && !empty($v_itm['related_shortcut']) && $v_itm['related_shortcut'] == "custom_dta")
                        {
                            $current_item['customdtaid'] = $v_itm['custom_dta_id'];
						}
						
						
						$invoice_items[$v_itm['invoice']][] = $current_item;
					}
				}
			}

			//2. get all required client data
			$clientdata = Pms_CommonData::getClientData($clientid);
			$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
			$client_data['client']['team_name'] = $clientdata[0]['team_name'];
			$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
			$client_data['client']['phone'] = $clientdata[0]['phone'];
			$client_data['client']['fax'] = $clientdata[0]['fax'];

			//3. get pflegestuffe in current period
			$pflege = new PatientMaintainanceStage();
			$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);

			foreach($pflege_arr as $k_pflege => $v_pflege)
			{
				$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
			}

			foreach($grouped_pflege as $k_gpflege => $v_gpflege)
			{
				$last_pflege = end($v_gpflege);

				if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
				{
					//$k_gpflege = patient epid
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
				}
				else
				{
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
				}
			}

			//4. get all involved patients required data
			$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);

			foreach($patient_details as $k_pat_ipid => $v_pat_details)
			{
				$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
				$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
				$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
				$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
				$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
				$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
				$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
			}

			//4.1 get patients readmission details
			$conditions['periods'][0]['start'] = '2009-01-01';
			$conditions['periods'][0]['end'] = date('Y-m-d');
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);

			foreach($patient_days as $k_patd_ipid => $v_pat_details)
			{
				$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
			}

			//5. pricelist
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($invoice_period['start'], $invoice_period['end']); //get bra sapv pricelist and then shortcuts
			$curent_pricelist = $master_price_list[$invoice_period['start']][0];

			//6. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);

			$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
			// ispc = M => 1 = Versicherungspflichtige und -berechtigte
			// ispc = F => 3 = Familienversicherte
			// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
			//TODO-3528 Lore 12.11.2020
			$modules = new Modules();
			$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
			if($extra_healthinsurance_statuses){
			    $status_int_array += array(
			        "00" => "00",          //"Gesamtsumme aller Stati",
			        "11" => "11",          //"Mitglieder West",
			        "19" => "19",          //"Mitglieder Ost",
			        "31" => "31",          //"Angehörige West",
			        "39" => "39",          //"Angehörige Ost",
			        "51" => "51",          //"Rentner West",
			        "59" => "59",          //"Rentner Ost",
			        "99" => "99",          //"nicht zuzuordnende Stati",
			        "07" => "07",          //"Auslandsabkommen"
			    );
			}
			//.
			
			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;

				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}

			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);

			//RWH Start
				//get health insurance subdivizions
				$symperm = new HealthInsurancePermissions();
				$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
				
				if($divisions)
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
						->whereIn("company_id", $company_ids)
						->andWhereIn("ipid", $invoices_patients);
					$hi2s_arr = $hi2s->fetchArray();
				}
				
				if($hi2s_arr)
				{
					foreach($hi2s_arr as $k_subdiv => $v_subdiv)
					{
						if($v_subdiv['subdiv_id'] == "3")
						{
							$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
						}
					}
				}
			//RWH End
			
			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];

					if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
					{
						$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
					}

					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}

				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;

				//Versichertennummer
				$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];

				//Institutskennzeichen
				$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
				
				//Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
				$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
				
				// Health insurance status - ISPC- 1368 // 150611
				$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
			}

			//7. get (HD) main diagnosis
			$main_abbr = "'HD'";
			$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);

			foreach($main_diag as $key => $v_diag)
			{
				$type_arr[] = $v_diag['id'];
			}

			$pat_diag = new PatientDiagnosis();
			//set last param true to accept a list of ipids
			$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true);

			foreach($dianoarray as $k_diag => $v_diag)
			{
				//append diagnosis in patient data
				$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
				//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
				//ISPC-2489 Lore 26.11.2019
				$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
				
				$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
			}

			//8.related invoices sapvs
// 			$sapv_data = $this->get_related_invoices_sapv_150623($clientid, $invoices_patients, $invoice_period);
			$sapv_data = $this->get_related_invoices_sapv($clientid, $invoices_patients, $patients_invoices_periods);
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				$action_days[$v_ipid]['actions'] = array_unique($actions_dates[$v_ipid]);
				
				$patient_action_days[$v_ipid]['actions'] = array_values($action_days[$v_ipid]['actions']);
				array_walk($patient_action_days[$v_ipid]['actions'], function(&$value) {
					$value = strtotime($value);
				});
						
				asort($patient_action_days[$v_ipid]['actions']);
				array_walk($patient_action_days[$v_ipid]['actions'], function(&$valuez) {
					$valuez = date('d.m.Y',$valuez);
				});
							
				$final_patient_action_days[$v_ipid]['actions'] = array_values($patient_action_days[$v_ipid]['actions']);
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($sapv_data[$v_ipid] as $ks => $s_data)
				{
					foreach($final_patient_action_days[$v_ipid]['actions'] as $k=>$act_days){
						
						$r1start = strtotime(date('Y-m-d', strtotime($s_data['sapv_start'])));
						$r1end = strtotime(date('Y-m-d', strtotime($s_data['sapv_end'])));
							
						$r2start = strtotime($act_days);
						$r2end = strtotime($act_days);
							
						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)  )
						{
							if(!in_array($act_days,$sapv_data[$v_ipid][$ks]['actions'])){
								$sapv_data[$v_ipid][$ks]['actions'][] = $act_days;
							}
						} 
					}
				}
			}
			
			foreach($invoices_patients as $k_pat => $v_ipid)
			{
				foreach($sapv_data[$v_ipid] as $ks => $s_data)
				{
					$s_data['actions'] = array_unique($s_data['actions']);
					asort($s_data['actions']);
					
					if( empty($s_data['approved_date'])   || strlen($s_data['approved_date']) == 0 ){
						$sapv_data[$v_ipid][$ks]['approved_date'] = $s_data['actions'][0];
					}
				}
			}
			
			foreach($sapv_data as $k_ipid =>$sapvdata){
				$sapv_data[$k_ipid] = end($sapvdata);
			}

			//reloop the invoices data array
			foreach($he_invoices_data as $k_invoice => $v_invoice)
			{
				if(!$master_data['invoice_' . $k_invoice])
				{
					$master_data['invoice_' . $k_invoice] = array();
				}

				$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);

				$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
				$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
				$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
				$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
				$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
				$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
				$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];

				//RELATED SAPVS
				$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $sapv_data[$v_invoice['ipid']]['sapv_start'];
				$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $sapv_data[$v_invoice['ipid']]['sapv_end'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $sapv_data[$v_invoice['ipid']]['approved_date'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $sapv_data[$v_invoice['ipid']]['approved_number'];
				$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $sapv_data[$v_invoice['ipid']]['create_date'];

				//INVOICE RELATED ITEMS
				$location_change[$v_invoice['id']] = false;
				$found_a[$v_invoice['id']] = false;
				$found_c[$v_invoice['id']] = false;

				foreach($invoice_items[$v_invoice['id']] as $k_item_id => $v_item)
				{
					if($v_item['custom'] == '0')
					{
						if(strpos($v_item['dta_shortcut'], 'a') !== false)
						{
							$found_a[$v_invoice['id']] = true;
						}

						if(strpos($v_item['dta_shortcut'], 'c') !== false)
						{
							$found_c[$v_invoice['id']] = true;
						}
					}
					
					if($found_a[$v_invoice['id']] && $found_c[$v_invoice['id']])
					{
						$location_change[$v_invoice['id']] = true;
					}
				}

				foreach($invoice_items[$v_invoice['id']] as $k_item_id => $v_item)
				{
					$v_item['dta_id'] = '';
					$v_item['dta_price'] = "";
					if(!empty($v_item['dta_shortcuts']))
					{
						foreach($v_item['dta_shortcuts'] as $k_short => $v_short)
						{
							if($v_short == "a12")
							{
								$v_item['dta_ids']['dta_id_0'] = $curent_pricelist['he_dta']['primar'][$v_short]['dta_id'];
								$v_item['dta_price'] += $curent_pricelist['he_dta']['primar'][$v_short]['price'];
							}
							else if($v_short == "a13")
							{
								$v_item['dta_ids']['dta_id_1'] = $curent_pricelist['he_dta']['primar'][$v_short]['dta_id'];
								$v_item['dta_price'] += ($v_item['multiplier'] * $curent_pricelist['he_dta']['primar'][$v_short]['price']);
							}
							
							if($v_short == "c12")
							{
								$v_item['dta_ids']['dta_id_0'] = $curent_pricelist['he_dta']['primar_change'][$v_short]['dta_id'];
								$v_item['dta_price'] += $curent_pricelist['he_dta']['primar_change'][$v_short]['price'];
							}
							else if($v_short == "c13")
							{
								$v_item['dta_ids']['dta_id_1'] = $curent_pricelist['he_dta']['primar_change'][$v_short]['dta_id'];
								$v_item['dta_price'] += ($v_item['multiplier'] * $curent_pricelist['he_dta']['primar_change'][$v_short]['price']);
							}
						}
					}
					else
					{
    					if(strpos($v_item['dta_shortcut'], 'a') !== false)
    					{
    						//primar case with two products (changed location)
    						$v_item['dta_id'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['dta_id'];
    						$v_item['dta_price'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['price'];
    					}
    					else if(strpos($v_item['dta_shortcut'], 'c') !== false)
    					{
    						if($location_change[$v_invoice['id']])
    						{
    							//primar case with two products (changed location)
    							$v_item['dta_id'] = $curent_pricelist['he_dta']['primar_change'][$v_item['dta_shortcut']]['dta_id'];
    							$v_item['dta_price'] = $curent_pricelist['he_dta']['primar_change'][$v_item['dta_shortcut']]['price'];
    						}
    						else
    						{
    							//primar case with two products (changed location)
    							$v_item['dta_id'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['dta_id'];
    							$v_item['dta_price'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['price'];
    						}
    						
    					}
    					else if(count($invoice_items[$v_invoice['id']]) >= "1" && (strpos($v_item['dta_shortcut'], 'c') !== false || strpos($v_item['dta_shortcut'], 'a') !== false))
    					{
    						//primar case with only one product(no location change)
    						$v_item['dta_id'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['dta_id'];
    						$v_item['dta_price'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['price'];
    					}
    					else if(count($invoice_items[$v_invoice['id']]) >= "1" && strpos($v_item['dta_shortcut'], 'b') !== false)
    					{
    						//sapvbe case with only one product
    						//$v_item['dta_id'] = $curent_pricelist['he_dta']['sapvbe'][$v_item['dta_shortcut']]['dta_id'];
    						//$v_item['dta_price'] = $curent_pricelist['he_dta']['sapvbe'][$v_item['dta_shortcut']]['price'];
    						
    						// ISPC-2341 Changed By Ancuta on 07.03.2019
    						$v_item['dta_id'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['dta_id']; //ISPC-2341
    						$v_item['dta_price'] = $curent_pricelist['he_dta']['primar'][$v_item['dta_shortcut']]['price'];//ISPC-2341
    					}

					}
					
					if($v_item['customdtaid'] !=  0 ){
    						$v_item['dta_id'] = $v_item['customdtaid'] ;
    						$v_item['dta_price'] =  number_format($v_item['price'], '2', ',', '');;
					}
					
					
					// do not show in dta generation
					unset($v_item['customdtaid']);
					
					$v_item['dta_price'] = number_format($v_item['dta_price'], '2', ',', '');
					$master_data['invoice_' . $k_invoice]['items']['item_' . $k_item_id]['actions']['action_0'] = $v_item;
				}

				$v_item['dta_price'] = ($dta_price_a+$dta_price_c);
				$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
			}
 
			return $master_data;
		}

		private function get_related_invoices_sapv_150623($clientid, $invoices_patients, $period)
		{
			$patientmaster = new PatientMaster();
		
			$dropSapv = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->whereIn('ipid', $invoices_patients)
			->andWhere('"' . date('Y-m-d', strtotime($period['start'])) . '" <= verordnungbis')
			->andWhere('"' . date('Y-m-d', strtotime($period['end'])) . '" >= verordnungam')
			->andWhere('verordnungam != "0000-00-00 00:00:00"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00"')
			->andWhere('isdelete=0')
			->andWhere('status != 1 ')
			->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
		
			foreach($droparray as $k_dr => $v_dr)
			{
				$sapvs_grouped[$v_dr['ipid']][] = $v_dr;
			}
		
			$all_sapv_days = array();
			foreach($droparray as $k_sapv => $v_sapv)
			{
				$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
				$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
		
				//aditional data from sapv which was added on 16.10.2014
				if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
				{
					$sapv_data[$v_sapv['ipid']]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
					$sapv_data[$v_sapv['ipid']]['approved_number'] = $v_sapv['approved_number'];
				}
		
				//aditional data from sapv which was added on 31.10.2014
				$sapv_data[$v_sapv['ipid']]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
				$sapv_data[$v_sapv['ipid']]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
				$sapv_data[$v_sapv['ipid']]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
			}
			return $sapv_data;
		}
		
		private function get_related_invoices_sapv($clientid, $invoices_patients, $invoice_period)
		{
			
			$patientmaster = new PatientMaster();
			
			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $invoices_patients)
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
			
			foreach($droparray as $k_dr => $v_dr)
			{
				$sapvs_grouped[$v_dr['ipid']][] = $v_dr;
			}

			$all_sapv_days = array();
			
			
			$s=0;
			foreach($droparray as $k_sapv => $v_sapv)
			{
				
				$r1['start'][$v_sapv['ipid']][$s] = "";
				$r1['end'][$v_sapv['ipid']][$s] = "";
					
				$r2['start'][$v_sapv['ipid']][$s] = "";
				$r2['end'][$v_sapv['ipid']][$s] = "";
						
				if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
					// no sapv taken here - because it is considered to be fully denied
				}
				else
				{
					$r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
					$r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
					
					$r2['start'][$v_sapv['ipid']][$s] = strtotime($invoice_period[ $v_sapv['ipid'] ]['start']);
					$r2['end'][$v_sapv['ipid']][$s] = strtotime($invoice_period[$v_sapv['ipid']]['end']);
					
					if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
					{
						
						if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') ){
							$v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
						}
						$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
						$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
						//aditional data from sapv which was added on 16.10.2014
						if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
						{
							$sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
						}
						
						if( strlen($v_sapv['approved_number']) > 0 && $v_sapv['status'] != 1  ){
							$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
						} else{
							$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999";
						}
		
						//aditional data from sapv which was added on 31.10.2014
						$sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
						$sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
						$sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
						
						$s++;
					}
				}
 
			}
			
			return $sapv_data;
		}

		
		/* ########################################################################################### */
		/* ####################################### DTA - Niedersachsen ############################### */
		/* ########################################################################################### */
		
		public function listdtandinvoicesAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		
		    if($clientid > 0)
		    {
		        $where = ' and client=' . $logininfo->clientid;
		    }
		    else
		    {
		        $where = ' and client=0';
		    }
		
		    $storned_invoices = HiInvoices::get_storned_invoices($clientid);
		    $unpaid_status = array("2","5");
		    	
		    //construct months array in which the curent client has bre_invoices completed, not paid
		    $months_q = Doctrine_Query::create()
		    ->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
		    ->from('HiInvoices')
		    ->where("isdelete = 0")
		    ->andWhere('completed_date != "0000-00-00 00:00:00"')
		    ->andWhere("storno = 0 " . $where)
		    ->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    ->andWhereIN("status",$unpaid_status) // display only unpaid
		    ->orderBy('DISTINCT DESC');
		    $months_res = $months_q->fetchArray();
		
		    if($months_res)
		    {
		        //current month on top
		        $months_array[date('Y-m', time())] = date('m-Y', time());
		        foreach($months_res as $k_month => $v_month)
		        {
		            $months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
		        }
		
		        $months_array = array_unique($months_array);
		    }
		
		    if(strlen($_REQUEST['search']) > '0')
		    {
		        $selected_period['start'] = $_REQUEST['search'] . "-01";
		        $selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
		    }
		
		    $this->view->months_array = $months_array;
		
		    if($this->getRequest()->isPost())
		    {
		        $post = $_POST;
		
		        $dta_data = $this->gather_dta_nd_data($clientid, $userid, $post);
		        $this->generate_dta_xml($dta_data);
		        exit;
		    }
		}
		

		public function fetchdtandinvoiceslistAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		    $user_type = $logininfo->usertype;
		
		    $columnarray = array(
		        "pat" => "epid_num",
		        "invnr" => "invoice_number",
		        "invstartdate" => "invoice_start",
		        "invdate" => "completed_date_sort",
		        "invtotal" => "invoice_total",
		        "invkasse" => "company_name", // used in first order of health insurances
		    );
		
		    if($clientid > 0)
		    {
		        $where = ' and client=' . $logininfo->clientid;
		    }
		    else
		    {
		        $where = ' and client=0';
		    }
		
		    $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		    $this->view->order = $orderarray[$_REQUEST['ord']];
		    $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		
		    $client_users_res = User::getUserByClientid($clientid, 0, true);
		
		    foreach($client_users_res as $k_user => $v_user)
		    {
		        $client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
		    }
		
		    $this->view->client_users = $client_users;
		
		    //get patients data used in search and list
		    $sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    $sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		
		
		    $f_patient = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientMaster p')
		    ->where("p.isdelete =0")
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhere('e.clientid = ' . $clientid);
		
		    if($_REQUEST['clm'] == 'pat')
		    {
		        $f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    }
		
		    $f_patients_res = $f_patient->fetchArray();
		
		    $f_patients_ipids[] = '9999999999999';
		    foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		    {
		        $f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		        $client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
		    }
		
		    $this->view->client_patients = $client_patients;
		
		    if(strlen($_REQUEST['val']) > '0')
		    {
		        $selected_period['start'] = $_REQUEST['val'] . "-01";
		        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    }
		    else
		    {
		        $selected_period['start'] = date('Y-m-01', time());
		        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    }
		
		    //order by health insurance
		    if($_REQUEST['clm'] == "invkasse")
		    {
		        $orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		
		        $drop = Doctrine_Query::create()
		        ->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
		        ->from('PatientHealthInsurance')
		        ->whereIn("ipid", $f_patients_ipids)
		        ->orderBy($orderby);
		        $droparray = $drop->fetchArray();
		
		        $f_patients_ipids = array();
		        foreach($droparray as $k_pat_hi => $v_pat_hi)
		        {
		            $f_patients_ipids[] = $v_pat_hi['ipid'];
		        }
		    }
		
		    	
		    $storned_invoices = HiInvoices::get_storned_invoices($clientid);
		    $unpaid_status = array("2","5");
		    	
		    $fdoc = Doctrine_Query::create()
		    ->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
		    ->from('HiInvoices')
		    ->where("isdelete = 0 " . $where)
		    ->andWhere("storno = '0'")
		    ->andWhere('completed_date != "0000-00-00 00:00:00"')
		    ->andWhereIn('ipid', $f_patients_ipids)
		    ->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    ->andWhereIN("status",$unpaid_status) // display only unpaid
		    ->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
		    if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
		    {
		        $fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    }
		    else
		    {
		        //sort by patient sorted ipid order
		        $fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
		    }
		
		    //used in pagination of search results
		    $fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
		    $fdocarray = $fdoc->fetchArray();
		    $limit = 500;
		    $fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
		    $fdoc->where("isdelete = 0 " . $where . "");
		    $fdoc->andWhere("storno = '0'");
		    $fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
		    $fdoc->andWhereIn('ipid', $f_patients_ipids);
		    $fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    $fdoc->andWhereIN("status",$unpaid_status);  // display only unpaid
		    $fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
		    $fdoc->limit($limit);
		    $fdoc->offset($_REQUEST['pgno'] * $limit);
		
		    if($_REQUEST['dbgq'])
		    {
		        print_r($fdoc->getSqlQuery());
		        print_r($fdoc->fetchArray());
		
		        exit;
		    }
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		
		    //get ipids for which we need health insurances
		    foreach($fdoclimit as $k_inv => $v_inv)
		    {
		        $inv_ipids[] = $v_inv['ipid'];
		    }
		
		    $inv_ipids[] = '99999999999999';
		
		
		    //6. patients health insurance
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		
		    $company_ids[] = '9999999999999';
		    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    {
		        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
		        if($v_healthinsu['companyid'] != '0')
		        {
		            $company_ids[] = $v_healthinsu['companyid'];
		        }
		    }
		
		    $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		
		    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    {
		        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		        {
		            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
		            if(strlen($healtharray['name']) > '0')
		            {
		                $ins_name = $healtharray['name'];
		            }
		            else if(strlen($v_health_insurance[0]['company_name']) > '0')
		            {
		                $ins_name = $v_health_insurance[0]['company_name'];
		            }
		        }
		
		        //health insurance name
		        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    }
		    $this->view->healthinsurances = $healthinsu;
		
		
		    $this->view->{"style" . $_GET['pgno']} = "active";
		    if(count($fdoclimit) > '0')
		    {
		        $grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtandinvoiceslist.html");
		        $this->view->templates_grid = $grid->renderGrid();
		        $this->view->navigation = $grid->dotnavigation("dtandinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
		    }
		    else
		    {
		        //no items found
		        $this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
		        $this->view->navigation = '';
		    }
		
		    $response['msg'] = "Success";
		    $response['error'] = "";
		    $response['callBack'] = "callBack";
		    $response['callBackParameters'] = array();
		    $response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtandinvoiceslist.html');
		
		    echo json_encode($response);
		    exit;
		}
		
		/*
		 * required data from invoices:
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) IK number of client
		 *
		 * (X) invoice ammount
		 *
		 * (X) Administration -> Mandanten -> Team Name
		 * (X) Administration -> Mandanten -> first name last name
		 * (X) Administration -> Mandante -> Telefon
		 * (X) Administration -> Mandante -> Mobile phone (fax instead)
		 *
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) Invoice number followed by ":0"
		 * (X) invoice date (YYYYMMDD)
		 *
		 * (X) health insurance number (Versichertennummer) of patient
		 *
		 * (X) last name patient
		 * (X) first name patient
		 * (X) birthday patient (YYYYMMDD)
		 * (X) street and no. patients address
		 * (X) ZIP patients address
		 * (X) City patients address
		 *
		 * (X) month of delivery (YYYYMM)
		 * (X) Pflegestufe of patients stammdaten
		 *
		 * (X) day of month from the invoiced action (01-31) or 99 if action is a flat action
		 * (X) start time of billable performance (HHMM)
		 * (X) single price of action
		 * (X) end time of billable performance (HHMM)
		 * (X) ammount of action (Format: 9999,99) (normally "001,00")
		 * (X) overall invoice ammount
		 * (X) overall invoice ammount
		 *
		 *
		 * L.E:
		 * 10.10.2014 - changed to load dta_id, dta_name based on action(visit) dta_location(mapped with patient location)
		 *
		 * L.E:
		 * 16.10.2014 - added user BSNR (Betriebsstättennummer), Genehmigungskennzeichen, Datum der Genehmigung (from verordnung)
		 *
		 * L.E TODO->:
		 * 31.10.2014 - added date of first admision, sapv (start, end, created) date, sapv GENEHMIUNGSNUMMER and date, icd (code and text) main, healthinsurance name
		 *
		 * L.E.:
		 * 07.04.2015 - changed Betriebsstättennummer to use the client one not tht patient one(bug ISPC-1012)
		 */
		
		private function gather_dta_nd_data($clientid, $userid, $post)
		{
		    // GET SELECTED MONTH DETAILS
		    $month['start'] = date('Y-m-d', strtotime($post['search'].'-01'));
		    $month['end'] = date("Y-m-t", strtotime($month['start']));
		    
// 		    1. get all selected invoices data
		    $nd_invoices = new HiInvoices();
		    $nd_invoices_data = $nd_invoices->get_multiple_invoices($post['invoices']['bre']);
		
		    if ($nd_invoices_data === false){
		    	return array();
		    }
		    
		    $all_invoices_dates = array();
		    $patient_invoice_days = array();
		    foreach($nd_invoices_data as $k_inv => $v_inv)
		    {
		        $invoices_patients[] = $v_inv['ipid'];
		
		        /* 
		        $invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		        $invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		        $patients_invoices_periods[$v_inv['ipid']] = $invoice_period; 
		        */

		        $patient_invoice_days[$v_inv['ipid']][] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		        $patient_invoice_days[$v_inv['ipid']][] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		        
		        $all_invoices_dates[] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		        $all_invoices_dates[] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    }

		    asort($all_invoices_dates);
		    $all_invoices_dates = array_values($all_invoices_dates);
		    
		    $invoice_period = array();
		    $invoice_period['start'] = $all_invoices_dates[0];
		    $invoice_period['end'] = end($all_invoices_dates);
		    
		    
		    $patients_invoices_periods = array();
		    foreach($patient_invoice_days as $pipid=>$inv_dates){
		    	asort($inv_dates);
		    	$inv_dates = array_values($inv_dates);
		    	$patients_invoices_periods[$pipid]['start'] = $inv_dates[0]; 
		    	$patients_invoices_periods[$pipid]['end'] = end($inv_dates); 
		    }
		    
		    //2. get all required client data
		    $clientdata = Pms_CommonData::getClientData($clientid);
		    $client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
		    $client_data['client']['team_name'] = $clientdata[0]['team_name'];
		    $client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
		    $client_data['client']['phone'] = $clientdata[0]['phone'];
		    $client_data['client']['fax'] = $clientdata[0]['fax'];
		
		
		
		    //3. get pflegestuffe in current period
		    $pflege = new PatientMaintainanceStage();
		    $pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		
		    foreach($pflege_arr as $k_pflege => $v_pflege)
		    {
		        $grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
		    }
		
		    foreach($grouped_pflege as $k_gpflege => $v_gpflege)
		    {
		        $last_pflege = end($v_gpflege);
		
		        if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
		        {
		            //$k_gpflege = patient epid
		            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
		        }
		        else
		        {
		            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
		        }
		    }
		
		    //4. get all involved patients required data
		    $patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
		
		    foreach($patient_details as $k_pat_ipid => $v_pat_details)
		    {
		        $patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
		        $patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
		        $patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
		        $patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
		        $patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
		        $patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
		        $patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
		    }
		
		    //4.1 get patients readmission details
		    $conditions['periods'][0]['start'] = '2009-01-01';
		    $conditions['periods'][0]['end'] = date('Y-m-d');
		    $conditions['client'] = $clientid;
		    $conditions['ipids'] = $invoices_patients;
		    $patient_days = Pms_CommonData::patients_days($conditions);
		
		    foreach($patient_days as $k_patd_ipid => $v_pat_details)
		    {
		        $patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
		    }
		
		    // 5 Get  patients sapv data 
		    $patientmaster = new PatientMaster();
		    $dropSapv = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->whereIn('ipid', $invoices_patients)
		    ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    ->andWhere('isdelete=0')
		    ->orderBy('verordnungam ASC');
		    $droparray = $dropSapv->fetchArray();
		    
		    $all_sapv_days = array();
		    $temp_sapv_days = array();
		    $s=0;
		    
		    foreach($droparray as $k_sapv => $v_sapv)
		    {
		        $r1['start'][$v_sapv['ipid']][$s] = "";
		        $r1['end'][$v_sapv['ipid']][$s] = "";
		         
		        $r2['start'][$v_sapv['ipid']][$s] = "";
		        $r2['end'][$v_sapv['ipid']][$s] = "";
		    
		        if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
		            // no sapv taken here - becouse it is considered to be fully denied
		        }
		        else
		        {
		            $r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
		            $r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
		    
		            $r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
		            $r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
		    
		            if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
		            {
		    
		                //aditional data from sapv which was added on 16.10.2014
		                if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
		                {
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
		                }
		    
		                if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
		                } else{
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
		                }
		    
		    
		                if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
		                {
		                    $v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
		                }
		    
		    
		                $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		                $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		    
		                $temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		    
		    
		                //aditional data from sapv which was added on 31.10.2014
		                $sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
		                $sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
		                $sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
		                $sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		    
		                foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
		                {
		                    if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
		                    {
		                        $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
		                    }
		    
		                    $current_verordnet = explode(',', $v_sapv['verordnet']);
		                    $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		    
		                    asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
		                    $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
		                }
		    
		                $s++;
		            }
		        }
		    }
// 		    print_R($sapv_data); exit;
		    
		    //6. pricelist
		    $p_list = new PriceList();
		    $master_price_list = $p_list->get_period_price_list($invoice_period['start'], $invoice_period['end']); //get bra sapv pricelist and then shortcuts
		    $curent_pricelist = $master_price_list[$invoice_period['start']][0];
		
		    
		    
		    //7. patients health insurance
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		
		    $status_int_array = array("M" => "1", "F" => "3", "R" => "5");
		    // ispc = M => 1 = Versicherungspflichtige und -berechtigte
		    // ispc = F => 3 = Familienversicherte
		    // ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
		    //TODO-3528 Lore 12.11.2020
		    $modules = new Modules();
		    $extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
		    if($extra_healthinsurance_statuses){
		        $status_int_array += array(
		            "00" => "00",          //"Gesamtsumme aller Stati",
		            "11" => "11",          //"Mitglieder West",
		            "19" => "19",          //"Mitglieder Ost",
		            "31" => "31",          //"Angehörige West",
		            "39" => "39",          //"Angehörige Ost",
		            "51" => "51",          //"Rentner West",
		            "59" => "59",          //"Rentner Ost",
		            "99" => "99",          //"nicht zuzuordnende Stati",
		            "07" => "07",          //"Auslandsabkommen"
		        );
		    }
		    //.
		    
		    $company_ids[] = '9999999999999';
		    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    {
		        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
		        if($v_healthinsu['companyid'] != '0')
		        {
		            $company_ids[] = $v_healthinsu['companyid'];
		        }
		    }
		
		    $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		    	
		    	
		    //7.1 get health insurance subdivizions
		    $symperm = new HealthInsurancePermissions();
		    $divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		
		    if($divisions)
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
									->whereIn("company_id", $company_ids)
									->andWhereIn("ipid", $invoices_patients);
		        $hi2s_arr = $hi2s->fetchArray();
		    }
		
		    if($hi2s_arr)
		    {
		        foreach($hi2s_arr as $k_subdiv => $v_subdiv)
		        {
		            if($v_subdiv['subdiv_id'] == "3")
		            {
		                $subdivisions[$v_subdiv['ipid']] = $v_subdiv;
		            }
		        }
		    }
		
		    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    {
		        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		        {
		            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
		            if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
		            {
		                $v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
		            }
		
		            if(strlen($healtharray['name']) > '0')
		            {
		                $ins_name = $healtharray['name'];
		            }
		            else if(strlen($v_health_insurance[0]['company_name']) > '0')
		            {
		                $ins_name = $v_health_insurance[0]['company_name'];
		            }
		        }
		
		        //health insurance name
		        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		
		        //Versichertennummer
		        $healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		
		        // Health insurance status - ISPC- 1368 // 150611
		        $healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];

		        
		        
		        //TODO-237  DTA ND -- QTZ 26.04.2016

		        ////Institutskennzeichen
		        //$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		        ////Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
		        //$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		        
		        if(!empty($subdivisions[$k_ipid_hi]))
		        {
		            $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $subdivisions[$k_ipid_hi]['iknumber'];
		            $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling']; // Billing IK - Abrechnungs IK -- RWH - ISPC-1405 // 150716
		        }
		        else
		        {
		            $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		            $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		        }
		    }
		
		    $visits_data = $this->get_nd_related_visits($clientid, $invoices_patients, $month, $patients_invoices_periods);
		    
		    //8. get (HD) main diagnosis
		    $main_abbr = "'HD'";
		    $main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		
		    foreach($main_diag as $key => $v_diag)
		    {
		        $type_arr[] = $v_diag['id'];
		    }
		
		    $pat_diag = new PatientDiagnosis();
		    $dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		
		    foreach($dianoarray as $k_diag => $v_diag)
		    {
		        //append diagnosis in patient data
		        $diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
		       // $diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
		        //ISPC-2489 Lore 26.11.2019
		        $diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
		        
		        $patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
		    }
		
		    //9. get user data
		    $user_details = User::getUserDetails($userid);
		    //reloop the invoices data array
		    foreach($nd_invoices_data as $k_invoice => $v_invoice)
		    {
		        if(!$master_data['invoice_' . $k_invoice])
		        {
		            $master_data['invoice_' . $k_invoice] = array();
		        }
		        
		        $inv_start[$k_invoice] = strtotime(date('Y-m-d',strtotime($v_invoice['invoice_start'])));
		        $inv_end[$k_invoice] = strtotime(date('Y-m-d',strtotime($v_invoice['invoice_end'])));;
		        
		        
		        $master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
		        $master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
		        $master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
		        $master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		        $master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
		        $master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
		        $master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
		        $master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
		        $master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
		        $master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		
		        $master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		
		        $sapv_details[$v_invoice['ipid']][$k_invoice] = end($sapv_data[$v_invoice['ipid']]);

		        $master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_start'];
		        $master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_end'];
		        $master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_date'];
		        $master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_number'];
		        $master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['create_date'];
		        
		
		        
		        $inv_items = array();
		        foreach($v_invoice['items'] as $k_item => $v_item)
		        {
		            if($v_item['qty'] > 0){
		        		$qty2item = 0 ;
		            	
		                if($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] === null){
                            //if item wass added on invoice - this means that item has no related data- so display it as it is
                            $inv_actions['dta_name'] = $v_item['shortcut'];
                            $inv_actions['shortcut'] = $v_item['shortcut'];
                            
                            if($v_item['shortcut'] == "B") {
                                $inv_actions['dta_id'] = $curent_pricelist['B0']['dta_id'];
                            } else {
                                $inv_actions['dta_id'] = $curent_pricelist[$v_item['shortcut']]['dta_id'];
                            }
                            
                            $inv_actions['day'] = date('Ymd', strtotime($v_invoice['create_date']));;
                            $inv_actions['start_time'] = '0000';
                            $inv_actions['end_time'] = '2359'; 
                            
                            if($v_item['shortcut'] == "B") {
								//$inv_actions['price'] = $curent_pricelist['B0']['dta_price'];
                                $inv_actions['price'] = number_format($curent_pricelist['B0']['dta_price'], '2', ',', '');
                            } else {
								// $inv_actions['price'] = $curent_pricelist[$v_item['shortcut']]['dta_price'];
                                $inv_actions['price'] = number_format($curent_pricelist[$v_item['shortcut']]['dta_price'], '2', ',', '');
                            }
                            
                            $inv_actions['custom'] = $v_item['1'];
                            //$inv_actions['price'] = number_format($v_item['price'], '2', ',', '');
                            //$inv_actions['ammount'] = $v_item['qty'];
                            
                            $inv_items['actions']['action_0'] = $inv_actions;
		                    
		                } else {
		                    
		                    $inv_actions = array();
		                    foreach($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
		                    {
		                    	$act_start[$k_action] = strtotime($v_action['day']);
		                    	$act_end[$k_action] = strtotime($v_action['day']);
		                    	
		                    	if(Pms_CommonData::isintersected($act_start[$k_action], $act_end[$k_action], $inv_start[$k_invoice], $inv_end[$k_invoice]) && $qty2item < $v_item['qty'])
		                    	{
			                        $inv_actions['dta_name'] = $v_item['shortcut'];
			                        $inv_actions['shortcut'] = $v_item['shortcut'];
			                        if($v_item['shortcut'] == "B"){
			                            $inv_actions['dta_id'] = $curent_pricelist['B0']['dta_id'];
			                        } else{
			                            $inv_actions['dta_id'] = $curent_pricelist[$v_item['shortcut']]['dta_id'];
			                        }
			                        $inv_actions['day'] = $v_action['day'];
			                        $inv_actions['start_time'] = $v_action['start_time'];
			                        $inv_actions['end_time'] = $v_action['end_time'];
			                        if($v_item['shortcut'] == "B"){
										//$inv_actions['price'] = $curent_pricelist['B0']['dta_price'];
			                            $inv_actions['price'] = number_format($curent_pricelist['B0']['dta_price'], '2', ',', '');
			                        } else{
										//$inv_actions['price'] = $curent_pricelist[$v_item['shortcut']]['dta_price'];
			                            $inv_actions['price'] = number_format($curent_pricelist[$v_item['shortcut']]['dta_price'], '2', ',', '');
			                        }
			                        $inv_items['actions']['action_' . $k_action] = $inv_actions;
			                        
			                        $qty2item++;
		                    	}
		                    }
		                }
		                
		                $master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
		                $inv_actions = array();
		                $inv_items = array();
		            }
		        }
		        
		        $master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    }
// 		    print_r($master_data); exit;
// 		        exit;
		    
		    return $master_data;
		}		

		private function get_nd_related_visits($clientid,$patients, $selected_month_details,$patient_invoice_periods)
		{
		    $patientmaster = new PatientMaster();
		    $anlage6 = new Anlage6();
		    
		    $clientdata = Pms_CommonData::getClientData($clientid);
		    $c_data['clientid'] = $clientdata[0]['id'];
		    $c_data['clientname'] = $clientdata[0]['client_name'];
		    $c_data['clientfax'] = $clientdata[0]['fax'];
		    $c_data['clientphone'] = $clientdata[0]['phone'];
		    $c_data['clientemail'] = $clientdata[0]['emailid'];
		    $c_data['clientteamname'] = $clientdata[0]['team_name'];
		
		    foreach($patient_invoice_periods as  $ipipd  =>$invoice_periods){
    		    $period_days_arr[$ipipd] = $patientmaster->getDaysInBetween($invoice_periods['start'], $invoice_periods['end']);
		    }
		    
		    $all_days = array();
		    foreach($period_days_arr as $ppipid => $period){
		        foreach($period as $k_per_day => $v_per_day){
    		        $period_days[$ppipid][$v_per_day] = array();
    		        if(!in_array($v_per_day,$all_days)){
	    		        $all_days[] = $v_per_day;
    		        }
		        }
		    }
		    if(!empty($all_days)){
		    	asort($all_days);
		    	$all_days = array_values($all_days);
		    }
		    else
		    {
				$all_days[] = $selected_month_details['start'];
		    	$all_days[] = $selected_month_details['end'];
		    }
		    
		    if(count($patients) > 0)
		    {
		        /* -------------------------------------------- 1. Get patients ipids from ids ------------------------------------------------------- */
		        $patient_ipids = $patients;
		        
		        foreach($patients as $k=>$pipid){
		            $master_patient_data[$pipid] = $period_days[$pipid];
		        }

		        /* ------------------------ 2. Get patients active days in selected period for all ipids + 3. get admissions ----------------------- */
		        $patient_treated_periods = $patientmaster->getTreatedDaysRealMultiple($patient_ipids, false);
		
		        //			check if patient has anlage6 saved!
		        $anlage6_res = $anlage6->get_all_anlage_shortcut($patient_ipids, 'e');
		    
		        foreach($anlage6_res as $k_anlage6 => $v_anlage6)
		        {
		            $anlage6_arr[$v_anlage6['ipid']][date('Y-m-d', strtotime($v_anlage6['date']))] = $v_anlage6;
		        }
		 
		        foreach($patient_treated_periods as $period_ipid => $period_details)
		        {
		            $active_days[$period_ipid] = array();
		            $active_days_per_admissions[$period_ipid] = array();
		            $admission_dates[$period_ipid] = array();
		            $discharge_dates_arr[$period_ipid] = array();
		            if(!empty($period_details['admissionDates']))
		            {
		                foreach($period_details['admissionDates'] as $key_adm => $v_adm)
		                {
		                    
		                    $admission_details[$period_ipid][strtotime(date('d-m-Y',strtotime($v_adm['date'])))]['day'] = date('Ymd',strtotime($v_adm['date']));
		                    $admission_details[$period_ipid][strtotime(date('d-m-Y',strtotime($v_adm['date'])))]['start_time'] = date('Hi',strtotime($v_adm['date']));
		                    $admission_details[$period_ipid][strtotime(date('d-m-Y',strtotime($v_adm['date'])))]['end_time'] = date('Hi',strtotime('+1 hour',strtotime($v_adm['date'])));
		                    
		                    
		                    if(!empty($period_details['dischargeDates'][$key_adm]['date']))
		                    {
		                        $start_with_discharge = date('Y-m-d', strtotime($period_details['admissionDates'][$key_adm]['date']));
		                        $end_with_discharge = date('Y-m-d', strtotime($period_details['dischargeDates'][$key_adm]['date']));
		                        $discharge_dates_arr[$period_ipid][] = $end_with_discharge;
		
		                        $active_days[$period_ipid] = array_merge($active_days[$period_ipid], $patientmaster->getDaysInBetween($start_with_discharge, $end_with_discharge));
		
		                        if(empty($active_days_per_admissions[$period_ipid][$key_adm]))
		                        {
		                            $active_days_per_admissions[$period_ipid][$key_adm] = array();
		                        }
		                        $active_days_per_admissions[$period_ipid][$key_adm] = array_merge($active_days_per_admissions[$period_ipid][$key_adm], $patientmaster->getDaysInBetween($start_with_discharge, $end_with_discharge));
		
		                        //discharge date is active!
		                        $active_days[$period_ipid][] = date('Y-m-d', strtotime($end));
		                    }
		                    else
		                    {
		                        $start_without_discharge = date('Y-m-d', strtotime($period_details['admissionDates'][$key_adm]['date']));
		                        if(!empty($period_details['discharge_date']))
		                        {
		                            $end_without_discharge = date('Y-m-d', strtotime($period_details['discharge_date']));
		                        }
		                        else
		                        {
		                            $end_without_discharge = date('Y-m-d', time());
		                        }
		                        $discharge_dates_arr[$period_ipid][] = $end_without_discharge;
		
		                        $active_days[$period_ipid] = array_merge($active_days[$period_ipid], $patientmaster->getDaysInBetween($start_without_discharge, $end_without_discharge));
		
		                        if(empty($active_days_per_admissions[$period_ipid][$key_adm]))
		                        {
		                            $active_days_per_admissions[$period_ipid][$key_adm] = array();
		                        }
		                        $active_days_per_admissions[$period_ipid][$key_adm] = array_merge($active_days_per_admissions[$period_ipid][$key_adm], $patientmaster->getDaysInBetween($start_without_discharge, $end_without_discharge));
		                    }
		
		                    if(empty($anlage6_arr[$period_ipid]))
		                    {
		                        //admission
		                        $a1start_formated = date('Y-m-d', strtotime($v_adm['date']));
		                        $a1start = strtotime(date('Y-m-d', strtotime($v_adm['date'])));
		                        $a1end = strtotime(date('Y-m-d', strtotime($v_adm['date'])));
		
		                        //selected period
		                        $p1start = strtotime($patient_invoice_periods[$period_ipid]['start']);
		                        $p1end = strtotime($patient_invoice_periods[$period_ipid]['end']);
		
		                        if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
		                        {
		                            $admission_dates[$period_ipid][$a1start_formated]['value'] = '1';
		                        }
		                    }
		                    else
		                    {
		                    	// TODO:1073 14.08.2017
							  
							    //$admission_dates[$period_ipid] = $anlage6_arr[$period_ipid];
								
								//admission
								$a1start_formated = date('Y-m-d', strtotime($v_adm['date']));
								$a1start = strtotime(date('Y-m-d', strtotime($v_adm['date'])));
								$a1end = strtotime(date('Y-m-d', strtotime($v_adm['date'])));

								//selected period
								$p1start = date('Y-m-d', strtotime($patient_invoice_periods[$period_ipid]['start']));
								$p1end = date('Y-m-d', strtotime($patient_invoice_periods[$period_ipid]['end']));
								
								$p1start_str = strtotime($patient_invoice_periods[$period_ipid]['start']);
								$p1end_str = strtotime($patient_invoice_periods[$period_ipid]['end']);
								
								ksort($anlage6_arr[$period_ipid]);
								$anlage_periods[$period_ipid]['days']=array_keys($anlage6_arr[$period_ipid]);
								$anlage_periods[$period_ipid]['start'] = $anlage_periods[$period_ipid]['days'][0]; 
								$anlage_periods[$period_ipid]['end'] = end($anlage_periods[$period_ipid]['days']);

								// if admission in anlage period check
								if(Pms_CommonData::isintersected($p1start, $p1end, $anlage_periods[$period_ipid]['start'], $anlage_periods[$period_ipid]['end']))
								{
									foreach($anlage6_arr[$period_ipid] as $e_date=>$e_values){
									
										if(Pms_CommonData::isintersected($e_date, $e_date, $p1start, $p1end))
										{
											if($e_values['value'] == "1"){
												$admission_dates[$period_ipid][$e_date]['value'] = '1';
											} else {
												$admission_dates[$period_ipid][$e_date]['value'] = '0';
											}
										}
									}
									
									if(Pms_CommonData::isintersected($a1start_formated, $a1start_formated, $p1start, $p1end) && !Pms_CommonData::isintersected($a1start_formated, $a1start_formated, $anlage_periods[$period_ipid]['start'], $anlage_periods[$period_ipid]['end']))
									{
										$admission_dates[$period_ipid][$a1start_formated]['value'] = '1';
									}
									
								} 
								else
								{
									if(Pms_CommonData::isintersected($a1start, $a1end, $p1start_str, $p1end_str))
									{
										$admission_dates[$period_ipid][$a1start_formated]['value'] = '1';
									}
								}
		                    }
		                }
		            }
		            else //old patients without data in readmission table
		            {
		                $admission_details[$period_ipid][strtotime(date('d-m-Y',strtotime($period_details['admission_date'])))]['day'] = date('Ymd',strtotime($period_details['admission_date']));
		                $admission_details[$period_ipid][strtotime(date('d-m-Y',strtotime($period_details['admission_date'])))]['start_time'] = date('Hi',strtotime($period_details['admission_date']));
		                $admission_details[$period_ipid][strtotime(date('d-m-Y',strtotime($period_details['admission_date'])))]['end_time'] = date('Hi',strtotime('+1 hour',strtotime($period_details['admission_date'])));
		                
		                $cycle_start_period = date('Y-m-d', strtotime($period_details['admission_date']));
		
		                if(empty($period_details['discharge_date']))
		                {
		
		                    $cycle_end_period = date('Y-m-d', time());
		                }
		                else
		                {
		                    $cycle_end_period = date('Y-m-d', strtotime($period_details['discharge_date']));
		                }
		
		
		                $active_days[$period_ipid] = array_merge($active_days[$period_ipid], $patientmaster->getDaysInBetween($cycle_start_period, $cycle_end_period));
		
		                $active_days[$period_ipid][] = $cycle_end_period;
		                $discharge_dates_arr[$period_ipid][] = $cycle_end_period;
		
		
		                if(empty($active_days_per_admissions[$period_ipid][0]))
		                {
		                    $active_days_per_admissions[$period_ipid][0] = array();
		                }
		                $active_days_per_admissions[$period_ipid][0] = array_merge($active_days_per_admissions[$period_ipid][0], $patientmaster->getDaysInBetween($cycle_start_period, $cycle_end_period));
		
		                if(empty($anlage6_arr[$period_ipid]))
		                {
		
		                    //admission
		                    $a2start = strtotime($cycle_start_period);
		                    $a2end = strtotime(date('Y-m-d', strtotime($cycle_start_period)));
		
		                    //selected period
		                    $p2start = strtotime($patient_invoice_periods[$period_ipid]['start']);
		                    $p2end = strtotime($patient_invoice_periods[$period_ipid]['end']);
		                    
		
		                    if(Pms_CommonData::isintersected($a2start, $a2end, $p2start, $p2end))
		                    {
		                        $admission_dates[$period_ipid][date('Y-m-d', strtotime($cycle_start_period))]['value'] = '1';
		                    }
		                }
		                else
		                {
		                
								// TODO:1073 14.08.2017
								//$admission_dates[$period_ipid] = $anlage6_arr[$period_ipid];
								
								//admission
								$a2start_formated = date('Y-m-d', strtotime($cycle_start_period));
								$a2start = strtotime($cycle_start_period);
								$a2end = strtotime(date('Y-m-d', strtotime($cycle_start_period)));

								//selected period
								$p2start = date('Y-m-d', strtotime($patient_invoice_periods[$period_ipid]['start']));
								$p2end = date('Y-m-d', strtotime($patient_invoice_periods[$period_ipid]['end']));
								
								$p2start_str = strtotime($patient_invoice_periods[$period_ipid]['start']);
								$p2end_str = strtotime($patient_invoice_periods[$period_ipid]['end']);
								
								ksort($anlage6_arr[$period_ipid]);
								$anlage_periods[$period_ipid]['days']=array_keys($anlage6_arr[$period_ipid]);
								$anlage_periods[$period_ipid]['start'] = $anlage_periods[$period_ipid]['days'][0]; 
								$anlage_periods[$period_ipid]['end'] = end($anlage_periods[$period_ipid]['days']);

								
								// if admission in anlage period check
								if(Pms_CommonData::isintersected($p2start, $p2end, $anlage_periods[$period_ipid]['start'], $anlage_periods[$period_ipid]['end']))
								{
									foreach($anlage6_arr[$period_ipid] as $e_date=>$e_values){
									
										if(Pms_CommonData::isintersected($e_date, $e_date, $p2start, $p2end))
										{
											if($e_values['value'] == "1"){
												$admission_dates[$period_ipid][$e_date]['value'] = '1';
											} else {
												$admission_dates[$period_ipid][$e_date]['value'] = '0';
											}
										}
									}
									
									if(Pms_CommonData::isintersected($a2start_formated, $a2start_formated, $p2start, $p2end) && !Pms_CommonData::isintersected($a2start_formated, $a2start_formated, $anlage_periods[$period_ipid]['start'], $anlage_periods[$period_ipid]['end']))
									{
										$admission_dates[$period_ipid][$a2start_formated]['value'] = '1';
									}
									
								} 
								else
								{
									if(Pms_CommonData::isintersected($a2start, $a2end, $p2start_str, $p2end_str))
									{
										$admission_dates[$period_ipid][$a2start_formated]['value'] = '1';
									}
								}
							
						
		                }
		            }
		
		
		            $active_days[$period_ipid] = array_values(array_intersect($period_days_arr[$period_ipid], $active_days[$period_ipid]));
		        }

		        
		        //admission dates array used in hospiz calculation of "B" shortcut
		        foreach($admission_dates as $k_ipid_dates => $v_adm_dates)
		        {
		            foreach($v_adm_dates as $k_adm_d => $v_adm_d)
		            {
		                $admission_dates_arr[$k_ipid_dates][] = $k_adm_d;
		                
		                if(!empty($anlage6_arr[$k_ipid_dates])){
		                    $saved_admission_date [$k_ipid_dates][$k_adm_d] = $v_adm_d['value'];
		                }
		            }
		        }
		
		        $disallowed_location_types = array('1', '2'); //hospital and hospiz
		        //ISPC-2612 Ancuta 27.06.2020 Locx
		        /*
		        $hospitalids = Doctrine_Query::create()
		        ->select("*, AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		        ->from('Locations')
		        ->where('client_id ="' . $clientid . '"')
		        //->andWhere('isdelete = 0')//ISPC-2612 Ancuta 27.06.2020 Locx
		        ->andWhereIn('location_type', $disallowed_location_types);
		        $hosparray = $hospitalids->fetchArray();
		         */
		        $loc_obj = new Locations();
		        $hosparray = $loc_obj->get_locationByClientAndTypes($clientid,$disallowed_location_types);
		        // --
		        
		        $hospital_ids[] = '999999999';
		        $hospiz_ids[] = '999999999';
		        foreach($hosparray as $hospital)
		        {
		            $hospital_ids[] = $hospital['id'];
		            $location_types[$hospital['location_type']][] = $hospital['id'];
		
		            if($hospital['location_type'] == '2') //hospiz
		            {
		                $hospiz_ids[] = $hospital['id'];
		            }
		        }
		        /* ------------------------- 4. Get hospital and hospiz and remove from active days of selected month ----------------------------- */
		
		        $patloc_all = Doctrine_Query::create()
		        ->select('*')
		        ->from('PatientLocation')
		        ->whereIn('ipid', $patient_ipids)
		        ->andWhere('isdelete="0"')
		        ->andWhere('location_id != "0"')
		        ->orderBy('valid_from,id ASC');
		        $pat_locations_res_all = $patloc_all->fetchArray();
		
		        foreach($pat_locations_res_all as $k_pat_loc => $v_pat_loc)
		        {
		            if(in_array($v_pat_loc['location_id'], $location_types['2']))
		            {
		                $hospiz_locations[$v_pat_loc['ipid']][$v_pat_loc['id']] = $v_pat_loc;
		            }
		        }

		        /* ------------------------- 4.1 Client Hospital Settings ----------------------------- */
		
		        //curent period
		        $conditions['periods'][0]['start'] = $all_days[0];
		        $conditions['periods'][0]['end'] = end($all_days);
		        $conditions['client'] = $clientid;
		        $conditions['ipids'] = $patient_ipids;
		
		        $patients_days = Pms_CommonData::patients_days($conditions);

		        /* ---------------------------------------- 5. Get SAPV for all ipids in selected month -------------------------------------------- */
		
		        $dropSapv = Doctrine_Query::create()
		        ->select('*')
		        ->from('SapvVerordnung')
		        ->whereIn('ipid', $patient_ipids)
		        ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		        ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		        ->andWhere('isdelete=0')
		        ->andWhere('status != 1 ')
		        ->orderBy('verordnungam ASC');
		        //get all sapvs
		        $all_sapv_res = $dropSapv->fetchArray();
		
		        foreach($all_sapv_res as $k_sapv_res => $v_sapv_res)
		        {
		            $all_s_start = date('Y-m-d', strtotime($v_sapv_res['verordnungam']));
		            $all_s_end = date('Y-m-d', strtotime($v_sapv_res['verordnungbis']));
		
		            if(empty($all_sapv_days_arr[$v_sapv_res['ipid']]))
		            {
		                $all_sapv_days_arr[$v_sapv_res['ipid']] = array();
		            }
		
		            $temp_all_sapv_days[$v_sapv_res['ipid']] = $patientmaster->getDaysInBetween($all_s_start, $all_s_end);
		            $all_sapv_days_arr[$v_sapv_res['ipid']] = array_merge($all_sapv_days_arr[$v_sapv_res['ipid']], $temp_all_sapv_days[$v_sapv_res['ipid']]);
		        }
		
		        //get only sapvs in period
		        $dropSapv->andWhere('"' . date('Y-m-d', strtotime($all_days[0])) . '" <= DATE(verordnungbis)');
		        $dropSapv->andWhere('"' . date('Y-m-d', strtotime(end($all_days))) . '" >= DATE(verordnungam)');
		        $droparray = $dropSapv->fetchArray();
		
		        $all_sapv_days = array();
		        $temp_sapv_days = array();
		
		        foreach($droparray as $k_sapv => $v_sapv)
		        {
		            if(count($all_sapv_days[$v_sapv['ipid']]) == 0)
		            {
		                $all_sapv_days[$v_sapv['ipid']] = array();
		            }
		
		
		            $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		            $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
		
		            $temp_sapv_days[$v_sapv['ipid']] = $patientmaster->getDaysInBetween($s_start, $s_end);
		
		            $all_sapv_days[$v_sapv['ipid']] = array_intersect($period_days_arr[$v_sapv['ipid']], array_merge($all_sapv_days[$v_sapv['ipid']], $temp_sapv_days[$v_sapv['ipid']]));
		        }
		
		        /* ------------------------------------------ 5b. Get  patient approved visits types for the selected month --------------------------------------------- */
		        $default_pavt = Pms_CommonData::default_approved_visit_type(); // if nothing saved -> get default
		        $pavt_mod = new PatientApprovedVisitTypes();
		        $existing_pavt_array = $pavt_mod->patients_approved_visits_overall($patient_ipids);
		        $pavt_array = $pavt_mod->patients_approved_visits_in_period($patient_ipids, $all_days[0],end($all_days));

		        foreach($patient_ipids as $ipid)
		        {
		            if($ipid != "99999999999999")
		            {
		                if(!$existing_pavt_array[$ipid])
		                {
		                    $pavt_array[$ipid][0]['ipid'] = $ipid;
		                    $pavt_array[$ipid][0]['visit_type'] = $default_pavt;
		                    $pavt_array[$ipid][0]['start_date'] = date('d.m.Y', strtotime($patient_invoice_periods[$ipid]['start'])); // start period
		                    $pavt_array[$ipid][0]['end_date'] = date('d.m.Y', strtotime($patient_invoice_periods[$ipid]['end'])); // end period
		                    $pavt_array[$ipid][0]['visit_type'] = $default_pavt;
		                }
		            }
		        }
		        $x = 0;
		
		        foreach($pavt_array as $p_ipid => $pavtvalue)
		        {
		            foreach($pavtvalue as $k => $tv)
		            {
		                $pavt[$tv['ipid']][$x]['visit_type'] = $tv['visit_type'];
		                $pavt[$tv['ipid']][$x]['start_date'] = $tv['start_date'];
		
		                if(empty($tv['end_date']) || $tv['end_date'] == "0000-00-00 00:00:00")
		                {
		                    $pavt[$tv['ipid']][$x]['end_date'] = date('Y-m-d H:i:s');
		                }
		                else
		                {
		                    $pavt[$tv['ipid']][$x]['end_date'] = $tv['end_date'];
		                }
		
		                $pavt_days[$tv['ipid']][$tv['visit_type']] = $patientmaster->getDaysInBetween(date('Y-m-d', strtotime($pavt[$tv['ipid']][$x]['start_date'])), date('Y-m-d', strtotime($pavt[$tv['ipid']][$x]['end_date'])));
		
		
		                if(empty($patient_vists_type_int[$tv['ipid']][$tv['visit_type']]))
		                {
		                    $patient_vists_type_int[$tv['ipid']][$tv['visit_type']] = array();
		                }
		                $patient_vists_type_int[$tv['ipid']][$tv['visit_type']] = array_merge($patient_vists_type_int[$tv['ipid']][$tv['visit_type']], $pavt_days[$tv['ipid']][$tv['visit_type']]);
		
		                $x++;
		            }
		        }
		
		        /* ------------------------------------------ 6. Get price list(s) for selected month --------------------------------------------- */
		        $shortcuts = Pms_CommonData::get_prices_shortcuts();
		
		        $this->view->shortcuts_admission = $shortcuts['admission'];
		        $this->view->shortcuts_daily = $shortcuts['daily'];
		        $this->view->shortcuts_visits = $shortcuts['visits'];
		        $this->view->used_shortcuts = array('E', 'EH', 'B', 'P1', 'P2', 'P3', 'A1', 'A2');
		
		
		        $p_list = new PriceList();
		        $master_price_list = $p_list->get_period_price_list($all_days[0], end($all_days));
		        
		        /* ----------------------- 7. Get doctor and nurse visits for all ipids and visit_date in selected month -------------------------- */
		
		        //CONTACT FORMS START
		        //get doctor and nurse users
		        //get all related users details
		        $master_groups_first = array('4', '5');
		
		        $client_user_groups_first = Usergroup::getUserGroups($master_groups_first);
		
		        foreach($client_user_groups_first as $k_group_f => $v_group_f)
		        {
		            $master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
		        }
		
		        $client_users = User::getClientsUsers($clientid);
		
		        $nurse_users = array();
		        $doctor_users = array();
		        foreach($client_users as $k_cuser_det => $v_cuser_det)
		        {
		            $master_user_details[$v_cuser_det['id']] = $v_cuser_det;
		            if(in_array($v_cuser_det['groupid'], $master2client['5']))
		            {
		                $nurse_users[] = $v_cuser_det['id'];
		            }
		            else if(in_array($v_cuser_det['groupid'], $master2client['4']))
		            {
		                $doctor_users[] = $v_cuser_det['id'];
		            }
		        }
		
		        //get curent contact forms
		        $contact_forms = $this->get_patients_contact_forms($patient_ipids, false, true); // retrive all contact froms -
 
		        $doctor_contact_forms = array();
		        $nurse_contact_forms = array();
		
		        foreach($contact_forms as $kcf => $day_cfs)
		        {
		            foreach($day_cfs as $k_dcf => $v_dcf)
		            {
		                if(in_array($v_dcf['create_user'], $doctor_users) || in_array($v_dcf['change_user'], $doctor_users))
		                {
		                    $doctor_contact_forms[] = $v_dcf;
		                }
		
		                if(in_array($v_dcf['create_user'], $nurse_users) || in_array($v_dcf['change_user'], $nurse_users))
		                {
		                    $nurse_contact_forms[] = $v_dcf;
		                }
		            }
		        }
		        //CONTACT FORMS END
		        //	get nurse visits from verlauf deleted
		        $nurse_from_course = Doctrine_Query::create()
		        ->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		        ->from('PatientCourse')
		        ->whereIn('ipid', $patient_ipids)
		        ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
		        ->andWhere("wrong = 1")
		        ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form'")
				->andWhere('source_ipid = ""')
		        ->orderBy('course_date ASC');
		        $nurse_v = $nurse_from_course->fetchArray();
		
		        $deleted_nurse_visits[] = '9999999999999999';
		        foreach($nurse_v as $k_nurse_k => $v_nurse_v)
		        {
		            $deleted_nurse_visits[] = $v_nurse_v['recordid'];
		        }
		
		        //			get doctor visits from verlauf deleted
		        $doc_from_course = Doctrine_Query::create()
		        ->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		        ->from('PatientCourse')
		        ->whereIn('ipid', $patient_ipids)
		        ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
		        ->andWhere("wrong = 1")
		        ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form'")
				->andWhere('source_ipid = ""')
		        ->orderBy('course_date ASC');
		        $doc_v = $doc_from_course->fetchArray();
		
		        $deleted_doctor_visits[] = '99999999999999999';
		        foreach($doc_v as $k_doc_v => $v_doc_v)
		        {
		            $deleted_doctor_visits[] = $v_doc_v['recordid'];
		        }
		        //these defined shortcuts have tstart and tend
		        $nurse_visit_shortcuts = array("P1", "P2", "P3");
		        $doctor_visit_shortcuts = array("A1", "A2");
		
		        /*				 * * NURSE VISITS ** */
		        $knurse_visits = Doctrine_Query::create()
		        ->select("*")
		        ->from("KvnoNurse")
		        ->whereIn('ipid', $patient_ipids)
		        ->andWhereNotIn('id', $deleted_nurse_visits)
// 		        ->andWhere('MONTH(vizit_date) = MONTH("' . $selected_month_details['start'] . '") AND YEAR(vizit_date) = YEAR("' . $selected_month_details['start'] . '") ')
		        ->andWhere('isdelete ="0"')
		        ->orderBy('kvno_begin_date_h, kvno_begin_date_m ASC');
		        $kvno_nurse_visits = $knurse_visits->fetchArray();

		
		        /* --------------------------------- Get nurse visits (contactform) --------------------------------------------------- */
		        foreach($nurse_contact_forms as $k_nurse_cf => $v_nurse_cf)
		        {
		            $nurse_visit_date_cf = date('Y-m-d', strtotime($v_nurse_cf['billable_date']));
		            $nurse_visit_date_cf_alt = date('d.m.Y', strtotime($v_nurse_cf['billable_date']));
		            $cf_nurse_visit = array();
		            $visit_type = '';
		            $duration = $v_nurse_cf['visit_duration'];
		
		            //nurse contact form source
		            $v_nurse_cf['source'] = 'nurse_cf';
		            $v_nurse_cf['duration'] = $duration;
		            $v_nurse_cf['vizit_date'] = $v_nurse_cf['billable_date'];
		
		            $kvno_nurse_visits[] = $v_nurse_cf;
		        }
		
		        if($_REQUEST['dbgg'])
		        {
		            print_r("Nurse visits (BF+CF)");
		            print_r($kvno_nurse_visits);
		        }
		
		        foreach($kvno_nurse_visits as $k_n_visit => $v_n_visit)
		        {
		            $vizit_duration = '0';
		            $vizit_date = date('Y-m-d', strtotime($v_n_visit['vizit_date']));
		            
		            if($v_n_visit['source'] == 'nurse_cf')
		            {
		                $vizit_duration = $v_n_visit['visit_duration'];
		                
    		            $visit_day[$k_n_visit] = date('Ymd', strtotime($v_n_visit['start_date']));
    		            $visit_start_time[$k_n_visit] = date('Hi', strtotime($v_n_visit['start_date']));
    		            $visit_start[$k_n_visit] = date('d.m.Y H:i', strtotime($v_n_visit['start_date']));
    		            $visit_end[$k_n_visit] =  date('d.m.Y H:i', strtotime($v_n_visit['end_date']));
    		            $visit_end_time[$k_n_visit] =  date('Hi', strtotime($v_n_visit['end_date']));
		            }
		            else
		            {
		                $vizit_duration = Pms_CommonData::calculate_visit_duration(str_pad($v_n_visit['kvno_begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_n_visit['kvno_end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_n_visit['kvno_begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_n_visit['kvno_end_date_m'], 2, "0", STR_PAD_LEFT), $v_n_visit['vizit_date']);

    		            $visit_day[$k_n_visit] = date('Ymd', mktime($v_n_visit['kvno_begin_date_h'],$v_n_visit['kvno_begin_date_m'],"0",date('n', strtotime($v_n_visit['vizit_date'])),date('j', strtotime($v_n_visit['vizit_date'])),date('Y', strtotime($v_n_visit['vizit_date']))));
    		            $visit_start_time[$k_n_visit] = date('Hi', mktime($v_n_visit['kvno_begin_date_h'],$v_n_visit['kvno_begin_date_m'],"0",date('n', strtotime($v_n_visit['vizit_date'])),date('j', strtotime($v_n_visit['vizit_date'])),date('Y', strtotime($v_n_visit['vizit_date']))));
    		            $visit_start[$k_n_visit] = date('d.m.Y H:i', mktime($v_n_visit['kvno_begin_date_h'],$v_n_visit['kvno_begin_date_m'],"0",date('n', strtotime($v_n_visit['vizit_date'])),date('j', strtotime($v_n_visit['vizit_date'])),date('Y', strtotime($v_n_visit['vizit_date']))));
    		            $visit_end[$k_n_visit] =  date('d.m.Y H:i', mktime($v_n_visit['kvno_end_date_h'],$v_n_visit['kvno_end_date_m'],"0",date('n', strtotime($v_n_visit['vizit_date'])),date('j', strtotime($v_n_visit['vizit_date'])),date('Y', strtotime($v_n_visit['vizit_date']))));
    		            $visit_end_time[$k_n_visit] =  date('Hi', mktime($v_n_visit['kvno_end_date_h'],$v_n_visit['kvno_end_date_m'],"0",date('n', strtotime($v_n_visit['vizit_date'])),date('j', strtotime($v_n_visit['vizit_date'])),date('Y', strtotime($v_n_visit['vizit_date']))));
		            
		            }
		
		            if(in_array($vizit_date, $patient_vists_type_int[$v_n_visit['ipid']]['p3']))
		            {// if P3 approved-> all visits are taken into consideration
		                foreach($nurse_visit_shortcuts as $k_shortcut_nurse => $v_shortcut_nurse)
		                {
		                    //visit date is between shortcut time and in active and sapv days and not in hospital/hospiz days
		                    $shortcut = $master_price_list[$vizit_date][0][$v_shortcut_nurse]['shortcut'];
		
		                    if(
		                        $vizit_duration >= $master_price_list[$vizit_date][0][$v_shortcut_nurse]['t_start'] 
		                        && $vizit_duration <= $master_price_list[$vizit_date][0][$v_shortcut_nurse]['t_end'] 
		                        && in_array($vizit_date, $active_days[$v_n_visit['ipid']]) 
		                        && in_array($vizit_date, $all_sapv_days[$v_n_visit['ipid']]) 
		                        && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospital']['real_days_cs']) 
		                        && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospiz']['real_days_cs']) 
		                        && $master_price_list[$vizit_date][0][$v_shortcut_nurse]['price'] != '0.00'
		                    )
		                    {
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '1';
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0][$v_shortcut_nurse]['price'];

		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['duration'] = $vizit_duration;
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['day'] = $visit_day[$k_n_visit];
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['start_time'] = $visit_start_time[$k_n_visit];
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['end_time'] = $visit_end_time[$k_n_visit];
		                    }
		                    else
		                    {
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '0';
		                        $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0][$v_shortcut_nurse]['price'];
		                    }
		                }
		            }
		            else if(in_array($vizit_date, $patient_vists_type_int[$v_n_visit['ipid']]['p2']))
		            {// if P2 approved -> the P3 visits are counted as P2
		                //						if(($vizit_duration >= $master_price_list[$vizit_date][0]['P2']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P2']['t_end'] || $vizit_duration >= $master_price_list[$vizit_date][0]['P3']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P3']['t_end'] ) && in_array($vizit_date, $active_days[$v_n_visit['ipid']]) && in_array($vizit_date, $all_sapv_days[$v_n_visit['ipid']]) && !in_array($vizit_date, $patients_hospital_days[$v_n_visit['ipid']]) && $master_price_list[$vizit_date][0]['P2']['price'] != '0.00')
		                if(
		                    ( $vizit_duration >= $master_price_list[$vizit_date][0]['P2']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P2']['t_end'] || $vizit_duration >= $master_price_list[$vizit_date][0]['P3']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P3']['t_end']
		                    ) && in_array($vizit_date, $active_days[$v_n_visit['ipid']]) && in_array($vizit_date, $all_sapv_days[$v_n_visit['ipid']]) && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospital']['real_days_cs']) && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospiz']['real_days_cs']) && $master_price_list[$vizit_date][0]['P2']['price'] != '0.00'
		                )
		                {
		                    $shortcut = 'P2';
		
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '1';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0]['P2']['price'];
		                    
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['duration'] = $vizit_duration;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['day'] = $visit_day[$k_n_visit];
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['start_time'] = $visit_start_time[$k_n_visit];
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['end_time'] = $visit_end_time[$k_n_visit];
		                }
		                else
		                {
		                    $shortcut = "P2";
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '0';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0]['P2']['price'];
		                }
		
		                if(
		                    $vizit_duration >= $master_price_list[$vizit_date][0]['P1']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P1']['t_end'] && in_array($vizit_date, $active_days[$v_n_visit['ipid']]) && in_array($vizit_date, $all_sapv_days[$v_n_visit['ipid']]) && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospital']['real_days_cs']) && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospiz']['real_days_cs']) && $master_price_list[$vizit_date][0]['P1']['price'] != '0.00'
		                )
		                {
		                    $shortcut = 'P1';
		
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '1';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0]['P1']['price'];
		                    
		                    
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['duration'] = $vizit_duration;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['day'] = $visit_day[$k_n_visit];
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['start_time'] = $visit_start_time[$k_n_visit];
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['end_time'] = $visit_end_time[$k_n_visit];
		                }
		                else
		                {
		                    $shortcut = "P1";
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '0';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0]['P1']['price'];
		                }
		            }
		            else if(in_array($vizit_date, $patient_vists_type_int[$v_n_visit['ipid']]['p1']))
		            {// if P1 approved -> the P3 visits are counted as P1
		                // if P1 approved -> the P2 visits are counted as P1
		                //						if(($vizit_duration >= $master_price_list[$vizit_date][0]['P1']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P1']['t_end'] || $vizit_duration >= $master_price_list[$vizit_date][0]['P2']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P2']['t_end'] || $vizit_duration >= $master_price_list[$vizit_date][0]['P3']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P3']['t_end'] ) && in_array($vizit_date, $active_days[$v_n_visit['ipid']]) && in_array($vizit_date, $all_sapv_days[$v_n_visit['ipid']]) && !in_array($vizit_date, $patients_hospital_days[$v_n_visit['ipid']]) && $master_price_list[$vizit_date][0]['P1']['price'] != '0.00')
		                if(
		                    (
		                        $vizit_duration >= $master_price_list[$vizit_date][0]['P1']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P1']['t_end'] || $vizit_duration >= $master_price_list[$vizit_date][0]['P2']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P2']['t_end'] || $vizit_duration >= $master_price_list[$vizit_date][0]['P3']['t_start'] && $vizit_duration <= $master_price_list[$vizit_date][0]['P3']['t_end']
		                    ) && in_array($vizit_date, $active_days[$v_n_visit['ipid']]) && in_array($vizit_date, $all_sapv_days[$v_n_visit['ipid']]) && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospital']['real_days_cs']) && !in_array(date('d.m.Y', strtotime($vizit_date)), $patients_days[$v_n_visit['ipid']]['hospiz']['real_days_cs']) && $master_price_list[$vizit_date][0]['P1']['price'] != '0.00'
		                )
		                {
		                    $shortcut = 'P1';
		
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '1';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0]['P1']['price'];


		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['duration'] = $vizit_duration;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['day'] = $visit_day[$k_n_visit];
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['start_time'] = $visit_start_time[$k_n_visit];
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['visit'][$k_n_visit]['end_time'] = $visit_end_time[$k_n_visit];
		                }
		                else
		                {
		                    $shortcut = 'P1';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['qty'] += '0';
		                    $master_patient_data[$v_n_visit['ipid']][$vizit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$vizit_date][0]['P1']['price'];
		                }
		            }
		        }
		
		        /* * * DOCTOR VISITS * * */
		        $hospital_alowed_shortcuts = array('A1', 'A2');
		        $kdoctor_visits = Doctrine_Query::create()
		        ->select("*")
		        ->from("KvnoDoctor")
		        ->whereIn('ipid', $patient_ipids)
		        ->andWhereNotIn('id', $deleted_doctor_visits)
// 		        ->andWhere('MONTH(vizit_date) = MONTH("' . $selected_month_details['start'] . '") AND YEAR(vizit_date) = YEAR("' . $selected_month_details['start'] . '") ')
		        ->orderBy('kvno_begin_date_h, kvno_begin_date_m ASC');
		        $kvno_doctor_visits = $kdoctor_visits->fetchArray();
		
		        /* --------------------------------- Get doctor visits (contactform) -------------------------------------------------- */
		        foreach($doctor_contact_forms as $k_doc_cf => $v_doc_cf)
		        {
		            $doctor_visit_date_cf = date('Y-m-d', strtotime($v_doc_cf['billable_date']));
		            $duration = $v_doc_cf['visit_duration'];
		            $visit_type = '';
		
		            $visit_day = date('Ymd', strtotime($v_doc_cf['start_date']));
		            $visit_start_time = date('Hi', strtotime($v_doc_cf['start_date']));
		            $visit_end_time = date('Hi', strtotime($v_doc_cf['end_date']));
		            
		            
		            //source doctor contactform
		            $v_doc_cf['source'] = 'doctor_cf';
		            $v_doc_cf['duration'] = $duration;
		            $v_doc_cf['vizit_date'] = $v_doc_cf['billable_date'];

		            $v_doc_cf['day'] = $visit_day;
		            $v_doc_cf['start_time'] = $visit_start_time;
		            $v_doc_cf['end_time'] = $visit_end_time;
		
		            $kvno_doctor_visits[] = $v_doc_cf;
		        }
		
		        
		        if($_REQUEST['dbgg'])
		        {
		            print_r("Doctor visits (BF+CF)");
		            print_r($kvno_doctor_visits);
		        }
		
		        foreach($kvno_doctor_visits as $k_d_visit => $v_d_visit)
		        {
		            $visit_duration_bf_cf = '0';
		            $visit_date = date('Y-m-d', strtotime($v_d_visit['vizit_date']));
		            
		            
		            
		            if($v_d_visit['source'] == 'doctor_cf')
		            {
		                $visit_duration_bf_cf = $v_d_visit['duration'];

		                $visit_day_bf_cf = $v_d_visit['day'];
    		            $visit_start_time_bf_cf = $v_d_visit['start_time'];
    		            $visit_end_time_bf_cf = $v_d_visit['end_time'];
		            }
		            else
		            {
		                $visit_duration_bf_cf = Pms_CommonData::calculate_visit_duration(str_pad($v_d_visit['kvno_begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_d_visit['kvno_end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_d_visit['kvno_begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_d_visit['kvno_end_date_m'], 2, "0", STR_PAD_LEFT), $v_d_visit['vizit_date']);

		                $visit_day_bf_cf = date('Ymd', mktime($v_d_visit['kvno_begin_date_h'],$v_d_visit['kvno_begin_date_m'],"0",date('n', strtotime($v_d_visit['vizit_date'])),date('j', strtotime($v_d_visit['vizit_date'])),date('Y', strtotime($v_d_visit['vizit_date']))));
    		            $visit_start_time_bf_cf = date('Hi', mktime($v_d_visit['kvno_begin_date_h'],$v_d_visit['kvno_begin_date_m'],"0",date('n', strtotime($v_d_visit['vizit_date'])),date('j', strtotime($v_d_visit['vizit_date'])),date('Y', strtotime($v_d_visit['vizit_date']))));
    		            $visit_start_bf_cf = date('d.m.Y H:i', mktime($v_d_visit['kvno_begin_date_h'],$v_d_visit['kvno_begin_date_m'],"0",date('n', strtotime($v_d_visit['vizit_date'])),date('j', strtotime($v_d_visit['vizit_date'])),date('Y', strtotime($v_d_visit['vizit_date']))));
    		            $visit_end_bf_cf =  date('d.m.Y H:i', mktime($v_d_visit['kvno_end_date_h'],$v_d_visit['kvno_end_date_m'],"0",date('n', strtotime($v_d_visit['vizit_date'])),date('j', strtotime($v_d_visit['vizit_date'])),date('Y', strtotime($v_d_visit['vizit_date']))));
    		            $visit_end_time_bf_cf =  date('Hi', mktime($v_d_visit['kvno_end_date_h'],$v_d_visit['kvno_end_date_m'],"0",date('n', strtotime($v_d_visit['vizit_date'])),date('j', strtotime($v_d_visit['vizit_date'])),date('Y', strtotime($v_d_visit['vizit_date']))));
		            
		            }
		            
		
		            foreach($doctor_visit_shortcuts as $k_shortcut_doctor => $v_shortcut_doctor)
		            {
		                $shortcut = $master_price_list[$visit_date][0][$v_shortcut_doctor]['shortcut'];
		
		
		                if(!in_array($shortcut, $hospital_alowed_shortcuts)) //skip doctor visits from hospital and sapv check
		                {
		
		                    if(
		                        $visit_duration_bf_cf >= $master_price_list[$visit_date][0][$v_shortcut_doctor]['t_start'] &&
		                        $visit_duration_bf_cf <= $master_price_list[$visit_date][0][$v_shortcut_doctor]['t_end'] &&
		                        in_array($visit_date, $active_days[$v_d_visit['ipid']]) &&
		                        in_array($visit_date, $all_sapv_days[$v_d_visit['ipid']]) &&
		                        !in_array(date('d.m.Y', strtotime($visit_date)), $patients_days[$v_d_visit['ipid']]['hospital']['real_days_cs']) &&
		                        $master_price_list[$visit_date][0][$v_shortcut_doctor]['price'] != '0.00'
		                    )
		                    {
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['qty'] += '1';
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$visit_date][0][$v_shortcut_doctor]['price'];
		                        
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['duration'] = $visit_duration_bf_cf;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['day'] = $visit_day_bf_cf;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['start_time'] = $visit_start_time_bf_cf;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['end_time'] = $visit_end_time_bf_cf;
		                    }
		                    else
		                    {
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['qty'] += '0';
		                    }
		                }
		                else
		                {
		                    if(
		                        $visit_duration_bf_cf >= $master_price_list[$visit_date][0][$v_shortcut_doctor]['t_start'] &&
		                        $visit_duration_bf_cf <= $master_price_list[$visit_date][0][$v_shortcut_doctor]['t_end'] &&
		                        in_array($visit_date, $active_days[$v_d_visit['ipid']]) &&
		                        in_array($visit_date, $all_sapv_days[$v_d_visit['ipid']]) &&
		                        $master_price_list[$visit_date][0][$v_shortcut_doctor]['price'] != '0.00'
		                    )
		                    {
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['qty'] += '1';
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$visit_date][0][$v_shortcut_doctor]['price'];
		                        
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['duration'] = $visit_duration_bf_cf;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['day'] = $visit_day_bf_cf;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['start_time'] = $visit_start_time_bf_cf;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['visit'][$k_d_visit]['end_time'] = $visit_end_time_bf_cf;
		                    }
		                    else
		                    {
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['qty'] += '0';
		                        $master_patient_data[$v_d_visit['ipid']][$visit_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$visit_date][0][$v_shortcut_doctor]['price'];
		                    }
		                }
		            }
		        }

		        /* ----------------------------------- 8. Setup E, EH and B shortcuts based on active days ----------------------------------------- */
		
		        foreach($active_days as $active_pat_ipid => $active_days_arr)
		        {
		            $shortcut_active_days = 'B';
		
		            foreach($active_days_arr as $k_act_day => $v_act_day)
		            {
		                $shortcut_active_price = $master_price_list[$v_act_day][0][$shortcut_active_days . '0']['price'];
		                //						if(in_array($v_act_day, $all_sapv_days[$active_pat_ipid]) && in_array($v_act_day, $period_days_arr) && !in_array($v_act_day, $hospital_days[$active_pat_ipid]) && !in_array($v_act_day, $hospiz_days_array[$active_pat_ipid]) && $shortcut_active_price != '0.00')
		                if(
		                    in_array($v_act_day, $all_sapv_days[$active_pat_ipid]) && in_array($v_act_day, $period_days_arr[$active_pat_ipid]) && !in_array(date('d.m.Y', strtotime($v_act_day)), $patients_days[$active_pat_ipid]['hospital']['real_days_cs']) && !in_array(date('d.m.Y', strtotime($v_act_day)), $patients_days[$active_pat_ipid]['hospiz']['real_days_cs']) && $shortcut_active_price != '0.00'
		                )
		                {
		
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['name'] = $shortcut_active_days;
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['qty'] += '1';
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['price'] = $shortcut_active_price;
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['details']['day'] =date('Ymd', strtotime($v_act_day));
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['details']['start_time'] = '0000';
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['details']['end_time'] = '2359';
		                }
		                else
		                {
		
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['name'] = $shortcut_active_days;
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['qty'] += '0';
		                    $master_patient_data[$active_pat_ipid][$v_act_day]['shortcuts'][$shortcut_active_days]['price'] = $shortcut_active_price;
		                }
		            }
		        }
		        if($_REQUEST['dbgg'])
		        {
		            print_r($master_patient_data);
		            exit;
		        }
		
		
		        $admission_dates_arr = array();
		        foreach($patient_ipids as $k_p_ipid => $v_p_ipid)
		        {
		            if($v_p_ipid != '99999999999999')
		            {
		                if(!empty($saved_admission_date[$v_p_ipid]))
		                {
		                    foreach($saved_admission_date[$v_p_ipid] as $sdate=>$svalue){
		                        if($svalue == "1"){
		                            $admission_dates_arr[$v_p_ipid][$sdate]['value'] = '1';
		                        }
		                    }
		                }
		                else
		                {
		                    
    		                foreach($active_days_per_admissions[$v_p_ipid] as $k_p_adm => $v_p_adm_arr)
    		                {
    		
    		                    foreach($v_p_adm_arr as $k_adm_day => $v_adm_day)
    		                    {
    		                        if(in_array($v_adm_day, $all_sapv_days_arr[$v_p_ipid]) && empty($triggered_admission[$v_p_ipid][$k_p_adm]))
    		                        {
    		                            $triggered_admission[$v_p_ipid][$k_p_adm] = $v_adm_day;
    		                            $admission_dates_arr[$v_p_ipid][$v_adm_day]['value'] = '1';
    		                        }
    		                    }
    		                }
		                }
		            }
		        }
             
		        foreach($pat_locations_res_all as $k_location_patient => $v_location_patient)
		        {
		            //check if admission day happens in this location
		            $admitted_date="";
		            $loc_admission_date="";
		            foreach($admission_dates_arr[$v_location_patient['ipid']] as $k_adm_loc => $v_adm_loc)
		            {
		
		                $shortcut = '';
		
		                //location start/end
		                if($v_location_patient['valid_till'] == '0000-00-00 00:00:00')
		                {
		                    $till = date('Y-m-d', time());
		                }
		                else
		                {
		                    $till = date('Y-m-d', strtotime($v_location_patient['valid_till']));
		                }
		
		                $r1start = strtotime(date('Y-m-d', strtotime($v_location_patient['valid_from'])));
		                $r1end = strtotime($till);
		
		                //admission start/end
		                $admitted_date = date('Y-m-d', strtotime($k_adm_loc)); //key is always date in both db and generated admissions array
		                $r2start = strtotime($admitted_date);
		                $r2end = strtotime($admitted_date);
		
		
		                $loc_admission_date = date('Y-m-d', strtotime($v_location_patient['valid_from']));
		
		                if( Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $v_adm_loc['value'] == 1 && in_array($admitted_date, $all_sapv_days[$v_location_patient['ipid']]))
		                {
		                    if(array_key_exists($v_location_patient['id'], $hospiz_locations[$v_location_patient['ipid']]))
		                    {
		                        $shortcut = 'EH';
		                    }
		                    else
		                    {
		                        $shortcut = 'E';
		                    }
		
		                    if(empty($master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]))
		                    {
		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut] = array();
		                    }
		                    
		                    if(!empty($saved_admission_date[$v_location_patient['ipid']])){
		                        	
		                        if($saved_admission_date[$v_location_patient['ipid']][$admitted_date] == "1" && $master_price_list[$admitted_date][0][$shortcut]['price'] != '0.00'){
		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['qty'] += 1;
		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$admitted_date][0][$shortcut]['price'];
		                            if(!empty($admission_details[$v_location_patient['ipid']][ strtotime(date("Ymd",strtotime($admitted_date)))])){
    		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['details'] =  $admission_details[$v_location_patient['ipid']][ strtotime(date("Ymd",strtotime($admitted_date)))];
		                            } else{
    		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['details']['day'] = date("Ymd",strtotime($admitted_date));
    		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['details']['start_time'] =  "0000";
    		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['details']['end_time'] =  "2359";
		                            }
		                            
		                            $admission_dates_arr[$v_location_patient['ipid']][$admitted_date]['value'] = '1';
		                            	
		                        } else{
		                            
		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['name'] = $shortcut;
		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['qty'] = 0;
		                            $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$admitted_date][0][$shortcut]['price'];
		                            $admission_dates_arr[$v_location_patient['ipid']][$admitted_date]['value'] = '0';
		                        }
		                        
		                    } else{
		                    
    		                    if($master_price_list[$admitted_date][0][$shortcut]['price'] != '0.00')
    		                    {
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['name'] = $shortcut;
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['qty'] += 1;
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$admitted_date][0][$shortcut]['price'];
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['details'] =  $admission_details[$v_location_patient['ipid']][ strtotime(date("Ymd",strtotime($admitted_date)))];
    		                        $admission_dates_arr[$v_location_patient['ipid']][$admitted_date]['value'] = '1';
    		                    }
    		                    else
    		                    {
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['name'] = $shortcut;
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['qty'] = 0;
    		                        $master_patient_data[$v_location_patient['ipid']][$admitted_date]['shortcuts'][$shortcut]['price'] = $master_price_list[$admitted_date][0][$shortcut]['price'];
    		                        //TODO-4146 Ancuta 24.05.2021 - Commented line - specialy when admission is in multiple locations 
    		                        //$admission_dates_arr[$v_location_patient['ipid']][$admitted_date]['value'] = '0';
    		                        //-- 
    		                    }
		                    }
		                }
		            }
		        }
		        
		        foreach($admission_dates_arr as $k_adm_ipid => $v_adm_days)
		        {
		            foreach($v_adm_days as $k_admission_day => $v_admission_value)
		            {
		                if($v_admission_value['value'] == '1')
		                {
		                    $shortcut = 'E';
		                    if(empty($master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]))
		                    {
		                        $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut] = array();
		                    }
		                    
		                    $shortcut_eh = 'EH';
		                    if(empty($master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut_eh]))
		                    {
		                        $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut_eh] = array();
		                    }
		
		                    if(in_array($k_admission_day, $all_sapv_days[$k_adm_ipid]) && (empty($master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut])) && empty($master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut_eh]) )
		                    {
		                        if($master_price_list[$k_admission_day][0][$shortcut]['price'] != '0.00')
		                        {
		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['name'] = $shortcut;
		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['qty'] += 1;
		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['price'] = $master_price_list[$k_admission_day][0][$shortcut]['price'];
// 		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['details']['day'] =date("Ymd",strtotime($k_admission_day));

                                    if(!empty($admission_details[$k_adm_ipid][ strtotime(date("Ymd",strtotime($k_admission_day)))]))
                                    {
                                        $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['details'] =  $admission_details[$k_adm_ipid][ strtotime(date("Ymd",strtotime($k_admission_day)))];
                                    } 
                                    else
                                    {
                                        $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['details']['day'] = date("Ymd",strtotime($k_admission_day));
                                        $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['details']['start_time'] = "0000";
                                        $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['details']['end_time'] = "2359";
                                    }
		                        }
		                        else
		                        {
		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['name'] = $shortcut;
		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['qty'] = 0;
		                            $master_patient_data[$k_adm_ipid][$k_admission_day]['shortcuts'][$shortcut]['price'] = $master_price_list[$k_admission_day][0][$shortcut]['price'];
		                        }
		                    }
		                    
		                }
		            }
		        }
		        
// 		        print_R($master_patient_data); exit;
		        /* ----------------------------------- 9. Get all ipids health insurance details ----------------------------------------- */
		        /* ----------------------------------- Changed to the new subdivision system ----------------------------------------- */
		        $hi_perms = new HealthInsurancePermissions();
		        $helath_insurance = new HealthInsurance();
		        $pats_helath_insurance = new PatientHealthInsurance();
		        $healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
		
		        $p_health_insurances = $pats_helath_insurance->get_multiple_patient_healthinsurance($patient_ipids);
		
		        foreach($p_health_insurances as $k_hi_pat => $v_hi_pat)
		        {
		            $company_ids[] = $v_hi_pat['companyid'];
		            $patient2company[$k_hi_pat] = $v_hi_pat['companyid'];
		        }
		        //get healthinsurance data
		        if(empty($company_ids))
		        {
		            $company_ids[] = "XXXXXX";
		        }
		        $healthinsu_master_arr = $helath_insurance->get_multiple_healthinsurances($company_ids);
		
		
		
		        //get client subdivisions
		        $divisions = $hi_perms->getClientHealthInsurancePermissions($clientid);
		        $hi_perms_divisions = $divisions;
		
		        //get subdivisions data
		        if($hi_perms_divisions)
		        {
		            $healthinsu_subdiv_arr = $healthinsu_subdiv->get_hi_subdivisions_multiple($company_ids);
		        }
		
		        //find out if there is a subdivision data, if not load old hi data
		        $patients_health_insurances[] = array();
		        foreach($patient_ipids as $k_ipidp => $v_ipidp)
		        {
		            if(strlen($healthinsu_subdiv_arr[$v_ipidp][3]['name']) > 0 || strlen($healthinsu_subdiv_arr[$v_ipidp][3]['city']) > 0)
		            {
		                $patients_health_insurances[$v_ipidp] = $healthinsu_subdiv_arr[$v_ipidp][3];
		            }
		            else
		            {
		                if(empty($p_health_insurances[$v_ipidp]['ins_street']))
		                {
		                    $p_health_insurances[$v_ipidp]['ins_street'] = $healthinsu_master_arr[$p_health_insurances[$v_ipidp]['companyid']]['street1'];
		                }
		                if(empty($p_health_insurances[$v_ipidp]['ins_zip']))
		                {
		                    $p_health_insurances[$v_ipidp]['ins_zip'] = $healthinsu_master_arr[$p_health_insurances[$v_ipidp]['companyid']]['zip'];
		                }
		                if(empty($p_health_insurances[$v_ipidp]['ins_city']))
		                {
		                    $p_health_insurances[$v_ipidp]['ins_city'] = $healthinsu_master_arr[$p_health_insurances[$v_ipidp]['companyid']]['city'];
		                }
		
		                $patients_health_insurances[$v_ipidp] = $p_health_insurances[$v_ipidp];
		            }
		        }
		
		        //LE: 06.11.2014 - ISPC-1069
		        /* -------------- 10 bis. Loop through the master patient data and limit P shortcuts to client limit---------------------- */
		        $client_data = new Client();
		        $client_details = $client_data->getClientDataByid($clientid);
		
		        $nurse_visit_shortcuts = array("P1", "P2", "P3");
		
		        krsort($nurse_visit_shortcuts);
		        $nurse_visit_shortcuts = array_values($nurse_visit_shortcuts);
		
		        foreach($master_patient_data as $k_patient_ipid => $patient_days_activity)
		        {
		            foreach($patient_days_activity as $k_day => $patient_activity)
		            {
		                $max_day_qty[$k_patient_ipid][$k_day] = $client_details[0]['max_nurse_visits'];
		                foreach($nurse_visit_shortcuts as $k_short => $v_short)
		                {
		                    if($patient_activity['shortcuts'][$v_short]['qty'] > '0')
		                    {
		                        if($patient_activity['shortcuts'][$v_short]['qty'] >= $max_day_qty[$k_patient_ipid][$k_day] && $max_day_qty[$k_patient_ipid][$k_day] != '0')
		                        {
		                            $value = $max_day_qty[$k_patient_ipid][$k_day];
		                            if(($max_day_qty[$k_patient_ipid][$k_day] - $patient_activity['shortcuts'][$v_short]['qty']) > '0')
		                            {
		                                $value = ($max_day_qty[$k_patient_ipid][$k_day] - $patient_activity['shortcuts'][$v_short]['qty']);
		                            }
		                            else
		                            {
		                                $value = '0';
		                            }
		
		                            $master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['qty'] = $max_day_qty[$k_patient_ipid][$k_day];
		                        }
		                        else if($max_day_qty[$k_patient_ipid][$k_day] == '0')
		                        {
		                            $value = $max_day_qty[$k_patient_ipid][$k_day];
		                            $master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['qty'] = $max_day_qty[$k_patient_ipid][$k_day];
		                        }
		                        else
		                        {
		                            $value = ($max_day_qty[$k_patient_ipid][$k_day] - $patient_activity['shortcuts'][$v_short]['qty']);
		
		                            $master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['qty'] = $patient_activity['shortcuts'][$v_short]['qty'];
		                        }
		
		                        $max_day_qty[$k_patient_ipid][$k_day] = $value;
		                    }
		                }
		            }
		        }
		
		        /* -------------------- TODO-229 21.04.2016 (Ancuta) Loop through the master patient data and remove visits acording to qty ------------------------------- */
		        foreach($master_patient_data as $k_patient_ipid => $patient_days_activity)
		        {
		            foreach($patient_days_activity as $k_day => $patient_activity)
		            {
		                foreach($nurse_visit_shortcuts as $k_short => $v_short)
		                {
		                    if(!empty($master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['visit']))
		                    {
		                        $master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['visit'] = array();
		                        
		                        foreach($patient_activity['shortcuts'][$v_short]['visit'] as $k => $v)
		                        {
		                            if(count($master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['visit']) <  $master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['qty'])
		                            {
		                                $master_patient_data[$k_patient_ipid][$k_day]['shortcuts'][$v_short]['visit'][] = $v;
		                            }
		                        }
		                    }
		                }
		            }
		        }
		        
		        /* -------------------- 10. Loop through the master patient data and construct final array ------------------------------- */

		        foreach($master_patient_data as $k_patient_ipid => $patient_days_activity)
		        {
		            foreach($patient_days_activity as $k_active_pat_day => $patient_activity)
		            {
		                foreach($this->view->used_shortcuts as $k_short => $v_short)
		                {
		                    if(!empty($patient_activity['shortcuts'][$v_short]))
		                    {
		                        $totals[$k_patient_ipid][$v_short]['name'] = $patient_activity['shortcuts'][$v_short]['name'];
		                        $totals[$k_patient_ipid][$v_short]['qty'] += $patient_activity['shortcuts'][$v_short]['qty'];
		                        $totals[$k_patient_ipid][$v_short]['price'] = $patient_activity['shortcuts'][$v_short]['price'];
		
		                        if(($patient_activity['shortcuts'][$v_short]['name'] == 'B' || $patient_activity['shortcuts'][$v_short]['name'] == 'E' || $patient_activity['shortcuts'][$v_short]['name'] == 'EH') && $patient_activity['shortcuts'][$v_short]['qty'] != 0)
		                        {
		                            $totals[$k_patient_ipid][$v_short]['total'] += $patient_activity['shortcuts'][$v_short]['price'] * $patient_activity['shortcuts'][$v_short]['qty'];
		                            $totals[$k_patient_ipid][$v_short]['actions'][] = $patient_activity['shortcuts'][$v_short]['details'];
		                        }
		                        else if($patient_activity['shortcuts'][$v_short]['name'] != 'B')
		                        {
		                            $totals[$k_patient_ipid][$v_short]['total'] += ($patient_activity['shortcuts'][$v_short]['qty'] * $patient_activity['shortcuts'][$v_short]['price']);

		                            foreach($patient_activity['shortcuts'][$v_short]['visit'] as $vday=>$visit_array){
    		                            $totals[$k_patient_ipid][$v_short]['actions'][] = $visit_array;
		                            }
		                        }

		                    }
		                    else
		                    {
		                        if($v_short == 'B')
		                        {
		                            $price = $master_price_list[$k_active_pat_day][0][$v_short . '0']['price'];
		                        }
		                        else
		                        {
		                            $price = $master_price_list[$k_active_pat_day][0][$v_short]['price'];
		                        }
		
		                        $totals[$k_patient_ipid][$v_short]['name'] = $v_short;
		                        $totals[$k_patient_ipid][$v_short]['qty'] += '0';
		                        if(!empty($price))
		                        {
		                            $totals[$k_patient_ipid][$v_short]['price'] = $price;
		                        }
		                        $totals[$k_patient_ipid][$v_short]['total'] += '0';
		                    }
		                }
		            }
		        }
		
		        foreach($totals as $k_ipid => $v_patient_shortcuts)
		        {
		            foreach($v_patient_shortcuts as $k_short => $v_short_details)
		            {
		                $totals[$k_ipid]['grand_total'] += $v_short_details['total'];
		                $invoices_ipids[] = $k_ipid;
		            }
		        }
		
		        $invoices_ipids = array_values(array_unique($invoices_ipids));
		
		        $invoices_details['ipids'] = $invoices_ipids;
		        $invoices_details['active_days'] = $active_days;
		        $invoices_details['client'] = $c_data;
		        $invoices_details['health_insurance'] = $patients_health_insurances;
		        $invoices_details['items'] = $totals;

		        if($_REQUEST['show_data']== "1" ){
    		        print_r($invoices_details); exit;
		        }
		        
		        return $invoices_details;
		    }
		    else
		    {
		        return false;
		    }
		}

		//used in ND invoices
		//TODO-3998 Ancuta 24.03.2021 - Added a new param $hide_no_billing_types  - default true 
		private function get_patients_contact_forms($ipids = false, $current_period=false, $duration = false,$hide_no_billing_types = true)
		{
		    
		    // TODO-3998 Ancuta 24.03.2021
		    if($hide_no_billing_types){
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		        
		        $form_types = new FormTypes();
		        $no_billing_types_array = $form_types->get_form_types($clientid, '200');//Ohne Berechnung
		        
		        $no_billing_types = array();
		        foreach($no_billing_types_array as $k => $type_data)
		        {
		            $no_billing_types[] = $type_data['id'];
		        }
		    }
		    // --
		    
		    if(is_array($ipids))
		    {
		        $ipids_arr = $ipids;
		    }
		    else
		    {
		        $ipids_arr[] = $ipids;
		    }
		    $contact_from_course = Doctrine_Query::create()
		    ->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		    ->from('PatientCourse')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
		    ->andWhere("wrong = 1")
		    ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
			->andWhere('source_ipid = ""')
		    ->orderBy('course_date ASC');
		
		    $contact_v = $contact_from_course->fetchArray();
		
		    $deleted_contact_forms[] = '9999999999999999';
		    foreach($contact_v as $k_contact_v => $v_contact_v)
		    {
		        $deleted_contact_forms[] = $v_contact_v['recordid'];
		    }
		
		    $contact_form_visits = Doctrine_Query::create()
		    ->select("*")
		    ->from("ContactForms")
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhereNotIn('id', $deleted_contact_forms);
		    if($current_period){
			    $contact_form_visits->andWhere('DATE(billable_date) BETWEEN ? AND ? ',array($current_period['start'], $current_period['end']) );
		    }
		    
		    //TODO-3998 Ancuta 24.03.2021
		    if($hide_no_billing_types && !empty($no_billing_types)){
		        $contact_form_visits->andWhereNotIN('form_type',$no_billing_types);
		    }
		    // --
		    $contact_form_visits->andWhere('isdelete ="0"')
		    ->andWhere('parent ="0"');
		
		    $contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');
		    $contact_form_visits_res = $contact_form_visits->fetchArray();
		
		    foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
		    {
		        if($duration)
		        {
// 		            $v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_contact_visit['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_m'], 2, "0", STR_PAD_LEFT), $v_contact_visit['date']);
		            $v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
		        }
		
		        $cf_visit_days[$v_contact_visit['ipid']][$v_contact_visit['id']] = $v_contact_visit;
		    }
		
		    return $cf_visit_days;
		}
		
		
		
		
		
		
		/* ########################################################################################### */
		/* ####################################### DTA Göttingen       ############################### */
		/* ########################################################################################### */
		//dta/listdtagottingeninvoices
		public function listdtagottingeninvoicesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
		
			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}
		
			$storned_invoices = HiInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			 
			//construct months array in which the curent client has bre_invoices completed, not paid
			$months_q = Doctrine_Query::create()
			->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
			->from('HiInvoices')
			->where("isdelete = 0")
			->andWhere('completed_date != "0000-00-00 00:00:00"')
			->andWhere("storno = 0 " . $where)
			->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
			->andWhereIN("status",$unpaid_status) // display only unpaid
			->orderBy('DISTINCT DESC');
			$months_res = $months_q->fetchArray();
		
			if($months_res)
			{
				//current month on top
				$months_array[date('Y-m', time())] = date('m-Y', time());
				foreach($months_res as $k_month => $v_month)
				{
					$months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
				}
		
				$months_array = array_unique($months_array);
			}
		
			if(strlen($_REQUEST['search']) > '0')
			{
				$selected_period['start'] = $_REQUEST['search'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
			}
		
			$this->view->months_array = $months_array;
		
			if($this->getRequest()->isPost() && !empty($_POST['invoices']))
			{
				$post = $_POST;
				
				$dta_data = $this->gather_dta_gottingen_data($clientid, $userid, $post);
				
				$this->generate_dta_xml($dta_data);
				exit;
			}
		}
		
		
		public function fetchdtagottingeninvoiceslistAction()
		{
			
			if ( ! $this->getRequest()->isXmlHttpRequest()) {
				die('!isXmlHttpRequest');
			}			
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;
		
			$columnarray = array(
					"pat" => "epid_num",
					"invnr" => "invoice_number",
					"invstartdate" => "invoice_start",
					"invdate" => "completed_date_sort",
					"invtotal" => "invoice_total",
					"invkasse" => "company_name", // used in first order of health insurances
			);
		
			if($clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}
		
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		
			
			$order_array_text = array("ASC" => "ASC", "DESC" => "DESC");
			//$_REQUEST['ord'] = $order_array_text[strtoupper($_REQUEST['ord'])];
			
			
			$client_users_res = User::getUserByClientid($clientid, 0, true);
		
			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
			}
		
			$this->view->client_users = $client_users;
		
			//get patients data used in search and list
			$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
			$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		
		
			$f_patient = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->where("p.isdelete =0")
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.clientid = ' . $clientid);
		
			if($_REQUEST['clm'] == 'pat')
			{
				$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
		
			$f_patients_res = $f_patient->fetchArray();
		
			$f_patients_ipids[] = '9999999999999';
			foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
			{
				$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
				$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
			}
		
			$this->view->client_patients = $client_patients;
		
			if(strlen($_REQUEST['val']) > '0')
			{
				$selected_period['start'] = $_REQUEST['val'] . "-01";
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}
			else
			{
				$selected_period['start'] = date('Y-m-01', time());
				$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
			}
		
			//order by health insurance
			if($_REQUEST['clm'] == "invkasse")
			{
				$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		
				$drop = Doctrine_Query::create()
				->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
				->from('PatientHealthInsurance')
				->whereIn("ipid", $f_patients_ipids)
				->orderBy($orderby);
				$droparray = $drop->fetchArray();
		
			
				$f_patients_ipids = array();
				foreach($droparray as $k_pat_hi => $v_pat_hi)
				{
					$f_patients_ipids[] = $v_pat_hi['ipid'];
				}
			}
		
			 
			$storned_invoices = HiInvoices::get_storned_invoices($clientid);
			$unpaid_status = array("2","5");
			 
			$fdoc = Doctrine_Query::create()
			->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
			->from('HiInvoices')
			->where("isdelete = 0 " . $where)
			->andWhere("storno = '0'")
			->andWhere('completed_date != "0000-00-00 00:00:00"')
			->andWhereIn('ipid', $f_patients_ipids)
			->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
			->andWhereIN("status",$unpaid_status) // display only unpaid
			->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
			if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
			{
				$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
			else
			{
				//sort by patient sorted ipid order
				$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
			}
		
			//used in pagination of search results
			$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
			$fdocarray = $fdoc->fetchArray();
			$limit = 500;
			$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
			$fdoc->where("isdelete = 0 " . $where . "");
			$fdoc->andWhere("storno = '0'");
			$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
			$fdoc->andWhereIn('ipid', $f_patients_ipids);
			$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
			$fdoc->andWhereIN("status",$unpaid_status);  // display only unpaid
			$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);
		
			if($_REQUEST['dbgq'])
			{
				print_r($fdoc->getSqlQuery());
				print_r($fdoc->fetchArray());
		
				exit;
			}
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		
			//get ipids for which we need health insurances
			foreach($fdoclimit as $k_inv => $v_inv)
			{
				$inv_ipids[] = $v_inv['ipid'];
			}
		
			$inv_ipids[] = '99999999999999';
		
		
			//6. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		
			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}
		
			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		
			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}
		
				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
			}
			$this->view->healthinsurances = $healthinsu;
		
		
			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtandinvoiceslist.html");
				$this->view->templates_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("dtandinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}
		
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtagottingeninvoiceslist.html');
		
			echo json_encode($response);
			exit;
		}
		
		
		/*
		 * required data from invoices:
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) IK number of client
		 *
		 * (X) invoice ammount
		 *
		 * (X) Administration -> Mandanten -> Team Name
		 * (X) Administration -> Mandanten -> first name last name
		 * (X) Administration -> Mandante -> Telefon
		 * (X) Administration -> Mandante -> Mobile phone (fax instead)
		 *
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) Invoice number followed by ":0"
		 * (X) invoice date (YYYYMMDD)
		 *
		 * (X) health insurance number (Versichertennummer) of patient
		 *
		 * (X) last name patient
		 * (X) first name patient
		 * (X) birthday patient (YYYYMMDD)
		 * (X) street and no. patients address
		 * (X) ZIP patients address
		 * (X) City patients address
		 *
		 * (X) month of delivery (YYYYMM)
		 * (X) Pflegestufe of patients stammdaten
		 *
		 * (X) day of month from the invoiced action (01-31) or 99 if action is a flat action
		 * (X) start time of billable performance (HHMM)
		 * (X) single price of action
		 * (X) end time of billable performance (HHMM)
		 * (X) ammount of action (Format: 9999,99) (normally "001,00")
		 * (X) overall invoice ammount
		 * (X) overall invoice ammount
		 *
		 *
		 * L.E:
		 * 10.10.2014 - changed to load dta_id, dta_name based on action(visit) dta_location(mapped with patient location)
		 *
		 * L.E:
		 * 16.10.2014 - added user BSNR (Betriebsstättennummer), Genehmigungskennzeichen, Datum der Genehmigung (from verordnung)
		 *
		 * L.E TODO->:
		 * 31.10.2014 - added date of first admision, sapv (start, end, created) date, sapv GENEHMIUNGSNUMMER and date, icd (code and text) main, healthinsurance name
		 *
		 * L.E.:
		 * 07.04.2015 - changed Betriebsstättennummer to use the client one not tht patient one(bug ISPC-1012)
		 * 
		 * //ispc-1916 
		 * patient_id = epid
		 * fallnummer = case number to be added at a later date
		 */
		
		private function gather_dta_gottingen_data($clientid, $userid, $post)
		{
			// GET SELECTED MONTH DETAILS
			$month['start'] = date('Y-m-d', strtotime($post['search'].'-01'));
			$month['end'] = date("Y-m-t", strtotime($month['start']));
		
			// 		    1. get all selected invoices data
			$nd_invoices = new HiInvoices();
			$nd_invoices_data = $nd_invoices->get_multiple_invoices($post['invoices']['bre']);
		
			if ($nd_invoices_data === false){
				return array();
			}
			
			$all_invoices_dates = array();
			$patient_invoice_days = array();
			foreach($nd_invoices_data as $k_inv => $v_inv)
			{
				$invoices_patients[] = $v_inv['ipid'];
				/* 
				$invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
				$invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
				$patients_invoices_periods[$v_inv['ipid']] = $invoice_period; 
				*/

				$patient_invoice_days[$v_inv['ipid']][] = date('Y-m-d', strtotime($v_inv['invoice_start']));
				$patient_invoice_days[$v_inv['ipid']][] = date('Y-m-d', strtotime($v_inv['invoice_end']));
				
				$all_invoices_dates[] = date('Y-m-d', strtotime($v_inv['invoice_start']));
				$all_invoices_dates[] = date('Y-m-d', strtotime($v_inv['invoice_end']));
			}

			asort($all_invoices_dates);
			$all_invoices_dates = array_values($all_invoices_dates);
			
			$invoice_period = array();
			$invoice_period['start'] = $all_invoices_dates[0];
			$invoice_period['end'] = end($all_invoices_dates);
			
			
			$patients_invoices_periods = array();
			foreach($patient_invoice_days as $pipid=>$inv_dates){
				asort($inv_dates);
				$inv_dates = array_values($inv_dates);
				$patients_invoices_periods[$pipid]['start'] = $inv_dates[0];
				$patients_invoices_periods[$pipid]['end'] = end($inv_dates);
			}
			
			//2. get all required client data
			$clientdata = Pms_CommonData::getClientData($clientid);
			$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
			$client_data['client']['team_name'] = $clientdata[0]['team_name'];
			$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
			$client_data['client']['phone'] = $clientdata[0]['phone'];
			$client_data['client']['fax'] = $clientdata[0]['fax'];
		
		
		
			//3. get pflegestuffe in current period
			$pflege = new PatientMaintainanceStage();
			$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		
			foreach($pflege_arr as $k_pflege => $v_pflege)
			{
				$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
			}
		
			foreach($grouped_pflege as $k_gpflege => $v_gpflege)
			{
				$last_pflege = end($v_gpflege);
		
				if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
				{
					//$k_gpflege = patient epid
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
				}
				else
				{
					$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
				}
			}
		
			//4. get all involved patients required data
			$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
			
			
			
			
			//get epid of all this ipids
			$ipid_epid = array();
			if (is_array($patient_details)) {
				$ipis_array = array_keys($patient_details);
				$EpidIpidMapping =  new EpidIpidMapping();
				$ipid_epid = $EpidIpidMapping->getIpidsEpids($ipis_array);

				// epid from PatientCourse
				/* Not needed right now
				 * $hospital_ids = $this->get_paients_hospital_epid($ipis_array);
				foreach($hospital_ids as $ipid => $course_data){
					asort($hospital_ids[$ipid]);
				} 
				foreach($hospital_ids as $sipid =>$cdata){
					$last_h_epid = end($cdata);
					$final_hepids[$sipid] = $last_h_epid['course_title'];
				} */
				
			}
			
			foreach($patient_details as $k_pat_ipid => $v_pat_details)
			{
				$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'] ;
				$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
				$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
				$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
				$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
				$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
				$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
				
			}
		
			//4.1 get patients readmission details
			$conditions['periods'][0]['start'] = '2009-01-01';
			$conditions['periods'][0]['end'] = date('Y-m-d');
			$conditions['client'] = $clientid;
			$conditions['ipids'] = $invoices_patients;
			$patient_days = Pms_CommonData::patients_days($conditions);
		
			foreach($patient_days as $k_patd_ipid => $v_pat_details)
			{
				$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
			}
		
			foreach($patient_data as $k_pat_ipid => $v)
			{
				$patient_data[$k_pat_ipid]['patient']['patient_id'] = $ipid_epid[$k_pat_ipid];
			}
			
			// 5 Get  patients sapv data
			$patientmaster = new PatientMaster();
			$dropSapv = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->whereIn('ipid', $invoices_patients)
			->andWhere('verordnungam != "0000-00-00 00:00:00"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00"')
			->andWhere('isdelete=0')
			->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
		
			$all_sapv_days = array();
			$temp_sapv_days = array();
			$s=0;
			$sapv_data = array();
			$sapv_data2id = array();
			foreach($droparray as $k_sapv => $v_sapv)
			{
				$r1['start'][$v_sapv['ipid']][$s] = "";
				$r1['end'][$v_sapv['ipid']][$s] = "";
				 
				$r2['start'][$v_sapv['ipid']][$s] = "";
				$r2['end'][$v_sapv['ipid']][$s] = "";
		
				if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
					// no sapv taken here - becouse it is considered to be fully denied
				}
				else
				{
					$r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
					$r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
		
					$r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
					$r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
		
					if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
					{
		
						//aditional data from sapv which was added on 16.10.2014
						if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
						{
							$sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
							$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
							
						}
		
						if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
							$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
							$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['approved_number'] = $v_sapv['approved_number'];;
						} else{
							$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
							$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['approved_number'] = $v_sapv['approved_number'];;
						}
		
		
						if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
						{
							$v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
						}
		
		
						$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
						$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
						$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		
		
						//aditional data from sapv which was added on 31.10.2014
						$sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
						$sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
						$sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
						$sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
						$sapv_data[$v_sapv['ipid']][$s]['case_number'] = $v_sapv['case_number'];
						
						
						
						$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['status'] = $v_sapv['status'];
						$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
						$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
						$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
						$sapv_data2id[$v_sapv['ipid']][$v_sapv['id']]['case_number'] = $v_sapv['case_number'];
		                  
						
						
						foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
						{
							if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
							{
								$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
							}
		
							$current_verordnet = explode(',', $v_sapv['verordnet']);
							$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		
							asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
							$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
						}
		
						$s++;
					}
				}
			}
			// 		    print_R($sapv_data); exit;
		
			//6. pricelist
			$p_list = new PriceList();
			$master_price_list = $p_list->get_period_price_list($invoice_period['start'], $invoice_period['end']); //get bra sapv pricelist and then shortcuts
			$curent_pricelist = $master_price_list[$invoice_period['start']][0];
		
		
		
			//7. patients health insurance
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		
			$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
			// ispc = M => 1 = Versicherungspflichtige und -berechtigte
			// ispc = F => 3 = Familienversicherte
			// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
			//TODO-3528 Lore 12.11.2020
			$modules = new Modules();
			$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
			if($extra_healthinsurance_statuses){
			    $status_int_array += array(
			        "00" => "00",          //"Gesamtsumme aller Stati",
			        "11" => "11",          //"Mitglieder West",
			        "19" => "19",          //"Mitglieder Ost",
			        "31" => "31",          //"Angehörige West",
			        "39" => "39",          //"Angehörige Ost",
			        "51" => "51",          //"Rentner West",
			        "59" => "59",          //"Rentner Ost",
			        "99" => "99",          //"nicht zuzuordnende Stati",
			        "07" => "07",          //"Auslandsabkommen" 
			    );
			}
			//.
			
			$company_ids[] = '9999999999999';
			foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			{
				$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
				if($v_healthinsu['companyid'] != '0')
				{
					$company_ids[] = $v_healthinsu['companyid'];
				}
			}
		
			$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
			 
			 
			//7.1 get health insurance subdivizions
			$symperm = new HealthInsurancePermissions();
			$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		
			if($divisions)
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
									->whereIn("company_id", $company_ids)
									->andWhereIn("ipid", $invoices_patients);
				$hi2s_arr = $hi2s->fetchArray();
			}
		
			if($hi2s_arr)
			{
				foreach($hi2s_arr as $k_subdiv => $v_subdiv)
				{
					if($v_subdiv['subdiv_id'] == "3")
					{
						$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
					}
				}
			}
		
			foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			{
				if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
				{
					$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
					if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
					{
						$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
					}
		
					if(strlen($healtharray['name']) > '0')
					{
						$ins_name = $healtharray['name'];
					}
					else if(strlen($v_health_insurance[0]['company_name']) > '0')
					{
						$ins_name = $v_health_insurance[0]['company_name'];
					}
				}
		
				//health insurance name
				$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		
				//Versichertennummer
				$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		
				// Health insurance status - ISPC- 1368 // 150611
				$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
		
		
		
				//TODO-237  DTA ND -- QTZ 26.04.2016
		
				////Institutskennzeichen
				//$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
				////Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
				//$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		
				if(!empty($subdivisions[$k_ipid_hi]))
				{
					$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $subdivisions[$k_ipid_hi]['iknumber'];
					$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling']; // Billing IK - Abrechnungs IK -- RWH - ISPC-1405 // 150716
				}
				else
				{
					$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
					$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $v_health_insurance[0]['institutskennzeichen'];
				}
			}
		
			$visits_data = $this->get_nd_related_visits($clientid, $invoices_patients, $month, $patients_invoices_periods);
		
			
			//8. get (HD) main diagnosis
			$main_abbr = "'HD'";
			$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		
			foreach($main_diag as $key => $v_diag)
			{
				$type_arr[] = $v_diag['id'];
			}
		
			$pat_diag = new PatientDiagnosis();
			$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		
			foreach($dianoarray as $k_diag => $v_diag)
			{
				//append diagnosis in patient data
				$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
				//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
				//ISPC-2489 Lore 26.11.2019
				$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
				
				$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
			}
		
			//9. get user data
			$user_details = User::getUserDetails($userid);
		
			//reloop the invoices data array
			foreach($nd_invoices_data as $k_invoice => $v_invoice)
			{
				if(!$master_data['invoice_' . $k_invoice])
				{
					$master_data['invoice_' . $k_invoice] = array();
				}
				$inv_start[$k_invoice] = strtotime(date('Y-m-d',strtotime($v_invoice['invoice_start'])));
				$inv_end[$k_invoice] = strtotime(date('Y-m-d',strtotime($v_invoice['invoice_end'])));;
				
				
				$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
				$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
				$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
				$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
				$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
				$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
				$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
				$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		
				$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		
// 				$sapv_data2id
                if(!empty($v_invoice['sapvid']) && !empty($sapv_data2id[$v_invoice['ipid']] [$v_invoice['sapvid']])){
        
    				$sapv_details[$v_invoice['ipid']][$k_invoice] = $sapv_data2id[$v_invoice['ipid']] [$v_invoice['sapvid']];
                } else {
    				$sapv_details[$v_invoice['ipid']][$k_invoice] = end($sapv_data[$v_invoice['ipid']]);
                }
				
		
				$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_start'];
				$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_end'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_date'];
				$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_number'];
				$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['create_date'];
				$master_data['invoice_' . $k_invoice]['sapv']['fallnummer'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['case_number']; //ispc-1916 - case number to be filled in at a later date
		
		
		
				$inv_items = array();
				foreach($v_invoice['items'] as $k_item => $v_item)
				{
					if($v_item['qty'] > 0){
						$qty2item = 0 ;
						if($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] === null){
							//if item wass added on invoice - this means that item has no related data- so display it as it is
							$inv_actions['dta_name'] = $v_item['shortcut'];
							$inv_actions['shortcut'] = $v_item['shortcut'];
		
							if($v_item['shortcut'] == "B") {
								$inv_actions['dta_id'] = $curent_pricelist['B0']['dta_id'];
							} else {
								$inv_actions['dta_id'] = $curent_pricelist[$v_item['shortcut']]['dta_id'];
							}
		
							$inv_actions['day'] = date('Ymd', strtotime($v_invoice['create_date']));;
							$inv_actions['start_time'] = '0000';
							$inv_actions['end_time'] = '2359';
		
							if($v_item['shortcut'] == "B") {
								//$inv_actions['price'] = $curent_pricelist['B0']['dta_price'];
								$inv_actions['price'] = number_format($curent_pricelist['B0']['dta_price'], '2', ',', '');
							} else {
								// $inv_actions['price'] = $curent_pricelist[$v_item['shortcut']]['dta_price'];
								$inv_actions['price'] = number_format($curent_pricelist[$v_item['shortcut']]['dta_price'], '2', ',', '');
							}
		
							$inv_actions['custom'] = $v_item['1'];
							//$inv_actions['price'] = number_format($v_item['price'], '2', ',', '');
							//$inv_actions['ammount'] = $v_item['qty'];
		
							$inv_items['actions']['action_0'] = $inv_actions;
		
						} else {
		
							$inv_actions = array();
							foreach($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
							{
								$act_start[$k_action] = strtotime($v_action['day']);
								$act_end[$k_action] = strtotime($v_action['day']);
								 
								if(Pms_CommonData::isintersected($act_start[$k_action], $act_end[$k_action], $inv_start[$k_invoice], $inv_end[$k_invoice]) && $qty2item < $v_item['qty'])
								{
									$inv_actions['dta_name'] = $v_item['shortcut'];
									$inv_actions['shortcut'] = $v_item['shortcut'];
									if($v_item['shortcut'] == "B"){
										$inv_actions['dta_id'] = $curent_pricelist['B0']['dta_id'];
									} else{
										$inv_actions['dta_id'] = $curent_pricelist[$v_item['shortcut']]['dta_id'];
									}
									$inv_actions['day'] = $v_action['day'];
									$inv_actions['start_time'] = $v_action['start_time'];
									$inv_actions['end_time'] = $v_action['end_time'];
									if($v_item['shortcut'] == "B"){
										//$inv_actions['price'] = $curent_pricelist['B0']['dta_price'];
										$inv_actions['price'] = number_format($curent_pricelist['B0']['dta_price'], '2', ',', '');
									} else{
										//$inv_actions['price'] = $curent_pricelist[$v_item['shortcut']]['dta_price'];
										$inv_actions['price'] = number_format($curent_pricelist[$v_item['shortcut']]['dta_price'], '2', ',', '');
									}
									$inv_items['actions']['action_' . $k_action] = $inv_actions;
									$qty2item++;
								}
							}
						}
		
						$master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
						$inv_actions = array();
						$inv_items = array();
					}
				}
				$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
			}
		
			return $master_data;
		}

		
		
		
		
		
		
		/* ########################################################################################### */
		/* ####################################### DTA - BW new########################################### */
		/* ########################################################################################### */
		public function listdtabwnewinvoicesAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		
		    if($clientid > 0)
		    {
		        $where = ' and client=' . $logininfo->clientid;
		    }
		    else
		    {
		        $where = ' and client=0';
		    }
		    //    get both bw2 invoices and bw invoices new
		    $storned_invoices = BwInvoicesNew::get_storned_invoices($clientid);
		    $unpaid_status = array("2","5");
		     
		    //construct months array in which the curent client has bre_invoices completed, not paid
		    $months_q = Doctrine_Query::create()
		    ->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
		    ->from('BwInvoicesNew')
		    ->where("isdelete = 0")
		    ->andWhere('completed_date != "0000-00-00 00:00:00"')
		    ->andWhere("storno = 0 " . $where)
		    ->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    ->andWhereIN("status",$unpaid_status) // display only unpaid
		    ->orderBy('DISTINCT DESC');
		    $months_res = $months_q->fetchArray();
		
		    if($months_res)
		    {
		        //current month on top
		        $months_array[date('Y-m', time())] = date('m-Y', time());
		        foreach($months_res as $k_month => $v_month)
		        {
		            $months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
		        }
		
		        $months_array = array_unique($months_array);
		    }
		
		    if(strlen($_REQUEST['search']) > '0')
		    {
		        $selected_period['start'] = $_REQUEST['search'] . "-01";
		        $selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
		    }
		
		    $this->view->months_array = $months_array;
		
		    if($this->getRequest()->isPost())
		    {
		        $post = $_POST;
		
		        $dta_data = $this->gather_dta_bw_data("new",$clientid, $userid, $post);
		        $this->generate_dta_xml($dta_data);
		        exit;
		    }
		}
		
		
		public function fetchdtabwnewinvoiceslistAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		    $user_type = $logininfo->usertype;
		
		    $columnarray = array(
		        "pat" => "epid_num",
		        "invnr" => "invoice_number",
		        "invstartdate" => "invoice_start",
		        "invdate" => "completed_date_sort",
		        "invtotal" => "invoice_total",
		        "invkasse" => "company_name", // used in first order of health insurances
		    );
		
		    if($clientid > 0)
		    {
		        $where = ' and client=' . $logininfo->clientid;
		    }
		    else
		    {
		        $where = ' and client=0';
		    }
		
		    $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		    $this->view->order = $orderarray[$_REQUEST['ord']];
		    $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		
		    $client_users_res = User::getUserByClientid($clientid, 0, true);
		
		    foreach($client_users_res as $k_user => $v_user)
		    {
		        $client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
		    }
		
		    $this->view->client_users = $client_users;
		
		    //get patients data used in search and list
		    $sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    $sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		
		
		    $f_patient = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientMaster p')
		    ->where("p.isdelete =0")
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhere('e.clientid = ' . $clientid);
		
		    if($_REQUEST['clm'] == 'pat')
		    {
		        $f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    }
		
		    $f_patients_res = $f_patient->fetchArray();
		
		    $f_patients_ipids[] = '9999999999999';
		    foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		    {
		        $f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		        $client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
		    }
		
		    $this->view->client_patients = $client_patients;
		
		    if(strlen($_REQUEST['val']) > '0')
		    {
		        $selected_period['start'] = $_REQUEST['val'] . "-01";
		        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    }
		    else
		    {
		        $selected_period['start'] = date('Y-m-01', time());
		        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    }
		
		    //order by health insurance
		    if($_REQUEST['clm'] == "invkasse")
		    {
		        $orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		
		        $drop = Doctrine_Query::create()
		        ->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
		        ->from('PatientHealthInsurance')
		        ->whereIn("ipid", $f_patients_ipids)
		        ->orderBy($orderby);
		        $droparray = $drop->fetchArray();
		
		        $f_patients_ipids = array();
		        foreach($droparray as $k_pat_hi => $v_pat_hi)
		        {
		            $f_patients_ipids[] = $v_pat_hi['ipid'];
		        }
		    }
		
		     
		    $storned_invoices = BwInvoicesNew::get_storned_invoices($clientid);
		    $unpaid_status = array("2","5");
		     
		    $fdoc = Doctrine_Query::create()
		    ->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
		    ->from('BwInvoicesNew')
		    ->where("isdelete = 0 " . $where)
		    ->andWhere("storno = '0'")
		    ->andWhere('completed_date != "0000-00-00 00:00:00"')
		    ->andWhereIn('ipid', $f_patients_ipids)
		    ->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    ->andWhereIN("status",$unpaid_status) // display only unpaid
		    ->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
		    if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
		    {
		        $fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    }
		    else
		    {
		        //sort by patient sorted ipid order
		        $fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
		    }
		
		    //used in pagination of search results
		    $fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
		    $fdocarray = $fdoc->fetchArray();
		    $limit = 500;
		    $fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
		    $fdoc->where("isdelete = 0 " . $where . "");
		    $fdoc->andWhere("storno = '0'");
		    $fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
		    $fdoc->andWhereIn('ipid', $f_patients_ipids);
		    $fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    $fdoc->andWhereIN("status",$unpaid_status);  // display only unpaid
		    $fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
		    $fdoc->limit($limit);
		    $fdoc->offset($_REQUEST['pgno'] * $limit);
		
		    if($_REQUEST['dbgq'])
		    {
		        print_r($fdoc->getSqlQuery());
		        print_r($fdoc->fetchArray());
		
		        exit;
		    }
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		
		    //get ipids for which we need health insurances
		    foreach($fdoclimit as $k_inv => $v_inv)
		    {
		        $inv_ipids[] = $v_inv['ipid'];
		    }
		
		    $inv_ipids[] = '99999999999999';
		
		
		    //6. patients health insurance
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		
		    $company_ids[] = '9999999999999';
		    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    {
		        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
		        if($v_healthinsu['companyid'] != '0')
		        {
		            $company_ids[] = $v_healthinsu['companyid'];
		        }
		    }
		
		    $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		
		    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    {
		        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		        {
		            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
		            if(strlen($healtharray['name']) > '0')
		            {
		                $ins_name = $healtharray['name'];
		            }
		            else if(strlen($v_health_insurance[0]['company_name']) > '0')
		            {
		                $ins_name = $v_health_insurance[0]['company_name'];
		            }
		        }
		
		        //health insurance name
		        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    }
		    $this->view->healthinsurances = $healthinsu;
		
		
		    $this->view->{"style" . $_GET['pgno']} = "active";
		    if(count($fdoclimit) > '0')
		    {
		        $grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtabwnewinvoiceslist.html");
		        $this->view->templates_grid = $grid->renderGrid();
		        $this->view->navigation = $grid->dotnavigation("dtabwnewinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
		    }
		    else
		    {
		        //no items found
		        $this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
		        $this->view->navigation = '';
		    }
		
		    $response['msg'] = "Success";
		    $response['error'] = "";
		    $response['callBack'] = "callBack";
		    $response['callBackParameters'] = array();
		    $response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtabwnewinvoiceslist.html');
		
		    echo json_encode($response);
		    exit;
		}
		
		
		/* ########################################################################################### */
		/* ####################################### DTA - BW ########################################### */
		/* ########################################################################################### */
		
		public function listdtabwinvoicesAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		
		    if($clientid > 0)
		    {
		        $where = ' and client=' . $logininfo->clientid;
		    }
		    else
		    {
		        $where = ' and client=0';
		    }
		//    get both bw2 invoices and bw invoices new
		    $storned_invoices = BwInvoices::get_storned_invoices($clientid);
		    $unpaid_status = array("2","5");
		    	
		    //construct months array in which the curent client has bre_invoices completed, not paid
		    $months_q = Doctrine_Query::create()
		    ->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
		    ->from('BwInvoices')
		    ->where("isdelete = 0")
		    ->andWhere('completed_date != "0000-00-00 00:00:00"')
		    ->andWhere("storno = 0 " . $where)
		    ->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    ->andWhereIN("status",$unpaid_status) // display only unpaid
		    ->orderBy('DISTINCT DESC');
		    $months_res = $months_q->fetchArray();
		
		    if($months_res)
		    {
		        //current month on top
		        $months_array[date('Y-m', time())] = date('m-Y', time());
		        foreach($months_res as $k_month => $v_month)
		        {
		            $months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
		        }
		
		        $months_array = array_unique($months_array);
		    }
		
		    if(strlen($_REQUEST['search']) > '0')
		    {
		        $selected_period['start'] = $_REQUEST['search'] . "-01";
		        $selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
		    }
		
		    $this->view->months_array = $months_array;
		
		    if($this->getRequest()->isPost())
		    {
		        $post = $_POST;
		
		        $dta_data = $this->gather_dta_bw_data("old",$clientid, $userid, $post);
		        $this->generate_dta_xml($dta_data);
		        exit;
		    }
		}
		

		public function fetchdtabwinvoiceslistAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		    $user_type = $logininfo->usertype;
		
		    $columnarray = array(
		        "pat" => "epid_num",
		        "invnr" => "invoice_number",
		        "invstartdate" => "invoice_start",
		        "invdate" => "completed_date_sort",
		        "invtotal" => "invoice_total",
		        "invkasse" => "company_name", // used in first order of health insurances
		    );
		
		    if($clientid > 0)
		    {
		        $where = ' and client=' . $logininfo->clientid;
		    }
		    else
		    {
		        $where = ' and client=0';
		    }
		
		    $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		    $this->view->order = $orderarray[$_REQUEST['ord']];
		    $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		
		    $client_users_res = User::getUserByClientid($clientid, 0, true);
		
		    foreach($client_users_res as $k_user => $v_user)
		    {
		        $client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
		    }
		
		    $this->view->client_users = $client_users;
		
		    //get patients data used in search and list
		    $sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    $sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		
		
		    $f_patient = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientMaster p')
		    ->where("p.isdelete =0")
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhere('e.clientid = ' . $clientid);
		
		    if($_REQUEST['clm'] == 'pat')
		    {
		        $f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    }
		
		    $f_patients_res = $f_patient->fetchArray();
		
		    $f_patients_ipids[] = '9999999999999';
		    foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		    {
		        $f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		        $client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
		    }
		
		    $this->view->client_patients = $client_patients;
		
		    if(strlen($_REQUEST['val']) > '0')
		    {
		        $selected_period['start'] = $_REQUEST['val'] . "-01";
		        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    }
		    else
		    {
		        $selected_period['start'] = date('Y-m-01', time());
		        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    }
		
		    //order by health insurance
		    if($_REQUEST['clm'] == "invkasse")
		    {
		        $orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		
		        $drop = Doctrine_Query::create()
		        ->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
		        ->from('PatientHealthInsurance')
		        ->whereIn("ipid", $f_patients_ipids)
		        ->orderBy($orderby);
		        $droparray = $drop->fetchArray();
		
		        $f_patients_ipids = array();
		        foreach($droparray as $k_pat_hi => $v_pat_hi)
		        {
		            $f_patients_ipids[] = $v_pat_hi['ipid'];
		        }
		    }
		
		    	
		    $storned_invoices = BwInvoices::get_storned_invoices($clientid);
		    $unpaid_status = array("2","5");
		    	
		    $fdoc = Doctrine_Query::create()
		    ->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
		    ->from('BwInvoices')
		    ->where("isdelete = 0 " . $where)
		    ->andWhere("storno = '0'")
		    ->andWhere('completed_date != "0000-00-00 00:00:00"')
		    ->andWhereIn('ipid', $f_patients_ipids)
		    ->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    ->andWhereIN("status",$unpaid_status) // display only unpaid
		    ->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
		    if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
		    {
		        $fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    }
		    else
		    {
		        //sort by patient sorted ipid order
		        $fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
		    }
		
		    //used in pagination of search results
		    $fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
		    $fdocarray = $fdoc->fetchArray();
		    $limit = 500;
		    $fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
		    $fdoc->where("isdelete = 0 " . $where . "");
		    $fdoc->andWhere("storno = '0'");
		    $fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
		    $fdoc->andWhereIn('ipid', $f_patients_ipids);
		    $fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    $fdoc->andWhereIN("status",$unpaid_status);  // display only unpaid
		    $fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
		    $fdoc->limit($limit);
		    $fdoc->offset($_REQUEST['pgno'] * $limit);
		
		    if($_REQUEST['dbgq'])
		    {
		        print_r($fdoc->getSqlQuery());
		        print_r($fdoc->fetchArray());
		
		        exit;
		    }
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		
		    //get ipids for which we need health insurances
		    foreach($fdoclimit as $k_inv => $v_inv)
		    {
		        $inv_ipids[] = $v_inv['ipid'];
		    }
		
		    $inv_ipids[] = '99999999999999';
		
		
		    //6. patients health insurance
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		
		    $company_ids[] = '9999999999999';
		    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    {
		        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
		        if($v_healthinsu['companyid'] != '0')
		        {
		            $company_ids[] = $v_healthinsu['companyid'];
		        }
		    }
		
		    $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		
		    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    {
		        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		        {
		            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
		            if(strlen($healtharray['name']) > '0')
		            {
		                $ins_name = $healtharray['name'];
		            }
		            else if(strlen($v_health_insurance[0]['company_name']) > '0')
		            {
		                $ins_name = $v_health_insurance[0]['company_name'];
		            }
		        }
		
		        //health insurance name
		        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    }
		    $this->view->healthinsurances = $healthinsu;
		
		
		    $this->view->{"style" . $_GET['pgno']} = "active";
		    if(count($fdoclimit) > '0')
		    {
		        $grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtabwinvoiceslist.html");
		        $this->view->templates_grid = $grid->renderGrid();
		        $this->view->navigation = $grid->dotnavigation("dtabwinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
		    }
		    else
		    {
		        //no items found
		        $this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
		        $this->view->navigation = '';
		    }
		
		    $response['msg'] = "Success";
		    $response['error'] = "";
		    $response['callBack'] = "callBack";
		    $response['callBackParameters'] = array();
		    $response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtabwinvoiceslist.html');
		
		    echo json_encode($response);
		    exit;
		}
		
		/*
		 * required data from invoices:
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) IK number of client
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) Institutskennzeichen (IK) of health insurance
		 * (X) IK number of client
		 *
		 * (X) invoice ammount
		 *
		 * (X) Administration -> Mandanten -> Team Name
		 * (X) Administration -> Mandanten -> first name last name
		 * (X) Administration -> Mandante -> Telefon
		 * (X) Administration -> Mandante -> Mobile phone (fax instead)
		 *
		 * (X) Institutskennzeichen (IK) of health insurance
		 *
		 * (X) Invoice number followed by ":0"
		 * (X) invoice date (YYYYMMDD)
		 *
		 * (X) health insurance number (Versichertennummer) of patient
		 *
		 * (X) last name patient
		 * (X) first name patient
		 * (X) birthday patient (YYYYMMDD)
		 * (X) street and no. patients address
		 * (X) ZIP patients address
		 * (X) City patients address
		 *
		 * (X) month of delivery (YYYYMM)
		 * (X) Pflegestufe of patients stammdaten
		 *
		 * (X) day of month from the invoiced action (01-31) or 99 if action is a flat action
		 * (X) start time of billable performance (HHMM)
		 * (X) single price of action
		 * (X) end time of billable performance (HHMM)
		 * (X) ammount of action (Format: 9999,99) (normally "001,00")
		 * (X) overall invoice ammount
		 * (X) overall invoice ammount
		 *
		 *
		 * L.E:
		 * 10.10.2014 - changed to load dta_id, dta_name based on action(visit) dta_location(mapped with patient location)
		 *
		 * L.E:
		 * 16.10.2014 - added user BSNR (Betriebsstättennummer), Genehmigungskennzeichen, Datum der Genehmigung (from verordnung)
		 *
		 * L.E TODO->:
		 * 31.10.2014 - added date of first admision, sapv (start, end, created) date, sapv GENEHMIUNGSNUMMER and date, icd (code and text) main, healthinsurance name
		 *
		 * L.E.:
		 * 07.04.2015 - changed Betriebsstättennummer to use the client one not tht patient one(bug ISPC-1012)
		 */
		
		private function gather_dta_bw_data($type = "old", $clientid, $userid, $post)
		{
		    // GET SELECTED MONTH DETAILS
		    $month['start'] = date('Y-m-d', strtotime($post['search'].'-01'));
		    $month['end'] = date("Y-m-t", strtotime($month['start']));
		    $patientmaster = new PatientMaster();
		    $form_types = new FormTypes();
		    $p_list = new PriceList();
		    $block_classification = new FormBlockClassification();
		    
		    
            //1. get all selected invoices data
            if($type == "old"){
                $bw_invoices = new BwInvoices();
                $bw_invoices_data = $bw_invoices->get_multiple_invoices($post['invoices']['bw']);
            } else{
                $bw_invoices = new BwInvoicesNew();
                $bw_invoices_data = $bw_invoices->get_multiple_invoices($post['invoices']['bw']);
            }

            if ($bw_invoices_data === false) {
            	return array();
            }
            
            $invoiced_days = array();
		    foreach($bw_invoices_data as $k_inv => $v_inv)
		    {
		        $invoices_patients[] = $v_inv['ipid'];
		        
		        $invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		        $invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		
		        $patients_invoices_periods[$v_inv['ipid']] = $invoice_period;
		        $invoiced_days[$v_inv['ipid']][$v_inv['id']] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		        $overall_invoiced_days[$v_inv['ipid']][] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		    }
		    
		    foreach($overall_invoiced_days as $pint =>$iperiods){
		        
                foreach($iperiods as $ik=>$pdates){
                    foreach($pdates as $k=>$date){
                        if(!in_array($date,$all_invoiced_dates)){
                            $all_invoiced_dates[] = $date;
                        }
                    }
                }
		    }
		    asort($all_invoiced_dates);
		    $all_invoices_dates = array_values($all_invoiced_dates);
		    
		    $overall_invoiced_period['start'] = $all_invoices_dates[0];
		    $overall_invoiced_period['end'] = end($all_invoices_dates);
		      
		    $current_period = $overall_invoiced_period;
		    
		    //2. get all required client data
		    $clientdata = Pms_CommonData::getClientData($clientid);
		    $client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
		    $client_data['client']['team_name'] = $clientdata[0]['team_name'];
		    $client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
		    $client_data['client']['phone'] = $clientdata[0]['phone'];
		    $client_data['client']['fax'] = $clientdata[0]['fax'];
		
		
		
		    //3. get pflegestuffe in current period
		    $pflege = new PatientMaintainanceStage();
		    $pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		
		    foreach($pflege_arr as $k_pflege => $v_pflege)
		    {
		        $grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
		    }
		
		    foreach($grouped_pflege as $k_gpflege => $v_gpflege)
		    {
		        $last_pflege = end($v_gpflege);
		
		        if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
		        {
		            //$k_gpflege = patient epid
		            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
		        }
		        else
		        {
		            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
		        }
		    }
		
		    //4. get all involved patients required data
		    
		    $sql = 'e.epid, p.ipid, e.ipid,';
		    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
		    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
		    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
		    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
		    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
		    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
		    
		    //4.1 get patients readmission details
		    $patient_details  = array();
		    $conditions['periods'][0]['start'] = '2009-01-01';
		    $conditions['periods'][0]['end'] = date('Y-m-d');
		    $conditions['client'] = $clientid;
		    $conditions['ipids'] = $invoices_patients;
		    $patient_details = Pms_CommonData::patients_days($conditions,$sql);
		    
		    $patient_days2locationtypes = array();
		    $hospital_days_cs_dmY = array();
		    $hospiz_days_cs_dmY = array();
		    
		    foreach($patient_details as $k_pat_ipid => $v_pat_details)
		    {
		        $patient_data[$k_pat_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
		        $patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['details']['first_name'];
		        $patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['details']['last_name'];
		        $patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['details']['birthd']);
		        $patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['details']['street1'];
		        $patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['details']['zip'];
		        $patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['details']['city'];
		        $patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
		        
		        $patient_data_n[$k_pat_ipid]['patient']['isdischarged'] = $v_pat_details['details']['isdischarged'];
		        
		        //hospital days cs
		        if(!empty($v_pat_details['hospital']['real_days_cs']))
		        {
		        	$hospital_days_cs_dmY[$k_pat_ipid] = $v_pat_details['hospital']['real_days_cs'];
		        }
		        
		        //hospiz days cs
		        if(!empty($v_pat_details['hospiz']['real_days_cs']))
		        {
		        	$hospiz_days_cs_dmY[$k_pat_ipid] = $v_pat_details['hospiz']['real_days_cs'];
		        }
		        
		        
		        
		        if(!empty($v_pat_details['discharge_details'])){
		        
		            foreach($v_pat_details['discharge_details'] as $dis_id => $dis_data) {
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates'][] = $dis_data['discharge_date'];
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates_sYmd'][date('Y-m-d',strtotime($dis_data['discharge_date']))] = $dis_data['discharge_date'];
		        
		                $patient_discharge_dates[$v_pat_details['details'] ['ipid']][] = $dis_data['discharge_date'];
		            }
		        } else {
		            if(!empty($v_pat_details['discharge'])){
		        
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates'] = $v_pat_details['discharge'];
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates_sYmd'][date('Y-m-d',strtotime($v_pat_details['discharge']))] = date("Y-m-d H:i:s",strtotime($v_pat_details['discharge']));
		                $patient_discharge_dates[$v_pat_details['details'] ['ipid']] = $v_pat_details['discharge'];
		            }
		        }
		        
		        // Location details - ISPC-2100 BW DTA
		        foreach($v_pat_details['locations'] as $pat_location_row_id => $pat_location_data)
		        {
		        	foreach($pat_location_data['days'] as $kl=>$lday)
		        	{
		        		if(in_array($lday,$v_pat_details['real_active_days'])  )
		        		{
		        			if( empty($pat_location_data['type'])){
		        				$pat_location_data['type'] = 0 ;
		        			}

		        			//ISPC-2100 Carmen 28.10.2020
		        			if($pat_location_data['type'] == "4" )
		        			{
		        				$patient_days2locationtypes[$k_pat_ipid][$lday][] = "3";
		        			}
		        			elseif($pat_location_data['type'] == "6" )
		        			{
		        				$patient_days2locationtypes[$k_pat_ipid][$lday][] = "5";
		        			}
		        			else
		        			{
		        				$patient_days2locationtypes[$k_pat_ipid][$lday][] = $pat_location_data['type'];
		        			}
		        			//--
		        		}
		        	}
		        }
		    }


		    foreach( $patient_days2locationtypes as $pipid=>$locdata){
		    	foreach($locdata as $loc_day => $day_loc_types){
		    		$del_val = "1";
		    		if ( ! in_array($loc_day,$hospital_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
		    			unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
		    		}
		    		
		    		/* 
		    		$del_val = "2";
		    		if ( ! in_array($loc_day,$hospiz_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
		    			unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
		    		} 
		    		*/
		    	}
		    }
		    foreach($patient_days2locationtypes as $pipid=>$locdata){
		    	foreach($locdata as $loc_day => $day_loc_types){
		    		$patient_days2locationtypes[$pipid][$loc_day] = end($day_loc_types);
		    	}
		    }
		    
		    
		    // 5 Get  patients sapv data 
		    $patientmaster = new PatientMaster();
		    $dropSapv = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->whereIn('ipid', $invoices_patients)
		    ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    ->andWhere('isdelete=0')
		    ->orderBy('verordnungam ASC');
		    $droparray = $dropSapv->fetchArray();
		    
		    $all_sapv_days = array();
		    $temp_sapv_days = array();
		    $s=0;
		    
		    foreach($droparray as $k_sapv => $v_sapv)
		    {
		        $r1['start'][$v_sapv['ipid']][$s] = "";
		        $r1['end'][$v_sapv['ipid']][$s] = "";
		         
		        $r2['start'][$v_sapv['ipid']][$s] = "";
		        $r2['end'][$v_sapv['ipid']][$s] = "";
		    
		        if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
		            // no sapv taken here - becouse it is considered to be fully denied
		        }
		        else
		        {
		            $r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
		            $r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
		    
		            $r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
		            $r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
		    
		            if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
		            {
		    
		                //aditional data from sapv which was added on 16.10.2014
		                if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
		                {
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
		                }
		    
		                if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
		                } else{
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
		                }
		    
		    
		                if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
		                {
		                    $v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
		                }
		    
		    
		                $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		                $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		    
		                $temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		    
		    
		                //aditional data from sapv which was added on 31.10.2014
		                $sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
		                $sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
		                $sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
		                $sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		    
		                foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
		                {
		                    if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
		                    {
		                        $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
		                    }
		    
		                    $current_verordnet = explode(',', $v_sapv['verordnet']);
		                    $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		    
		                    asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
		                    $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
		                }
		    
		                $s++;
		            }
		        }
		    }
		    
		    //6. pricelist
		    $p_list = new PriceList();
		    // ISPC-2100 BW DTA
			//$master_price_list = $p_list->get_period_price_list($overall_invoiced_period['start'], $overall_invoiced_period['end']); //get bra sapv pricelist and then shortcuts
		    $master_price_list = $p_list->get_period_price_list_specific($overall_invoiced_period['start'], $overall_invoiced_period['end']); //get bra sapv pricelist and then shortcuts
		    $curent_pricelist = $master_price_list[$overall_invoiced_period['start']][0];
		    
		    //7. patients health insurance
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		
		    $status_int_array = array("M" => "1", "F" => "3", "R" => "5");
		    // ispc = M => 1 = Versicherungspflichtige und -berechtigte
		    // ispc = F => 3 = Familienversicherte
		    // ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
		    //TODO-3528 Lore 12.11.2020
		    $modules = new Modules();
		    $extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
		    if($extra_healthinsurance_statuses){
		        $status_int_array += array(
		            "00" => "00",          //"Gesamtsumme aller Stati",
		            "11" => "11",          //"Mitglieder West",
		            "19" => "19",          //"Mitglieder Ost",
		            "31" => "31",          //"Angehörige West",
		            "39" => "39",          //"Angehörige Ost",
		            "51" => "51",          //"Rentner West",
		            "59" => "59",          //"Rentner Ost",
		            "99" => "99",          //"nicht zuzuordnende Stati",
		            "07" => "07",          //"Auslandsabkommen"
		        );
		    }
		    //.
		    
		    $company_ids[] = '9999999999999';
		    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    {
		        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		
		        if($v_healthinsu['companyid'] != '0')
		        {
		            $company_ids[] = $v_healthinsu['companyid'];
		        }
		    }
		
		    $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		    	
		    	
		    //7.1 get health insurance subdivizions
		    $symperm = new HealthInsurancePermissions();
		    $divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		
		    if($divisions)
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
									->whereIn("company_id", $company_ids)
									->andWhereIn("ipid", $invoices_patients);
		        $hi2s_arr = $hi2s->fetchArray();
		    }
		
		    if($hi2s_arr)
		    {
		        foreach($hi2s_arr as $k_subdiv => $v_subdiv)
		        {
		            if($v_subdiv['subdiv_id'] == "3")
		            {
		                $subdivisions[$v_subdiv['ipid']] = $v_subdiv;
		            }
		        }
		    }
		
		    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    {
		        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		        {
		            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		
		            if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
		            {
		                $v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
		            }
		
		            if(strlen($healtharray['name']) > '0')
		            {
		                $ins_name = $healtharray['name'];
		            }
		            else if(strlen($v_health_insurance[0]['company_name']) > '0')
		            {
		                $ins_name = $v_health_insurance[0]['company_name'];
		            }
		        }
		
		        //health insurance name
		        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		
		        //Versichertennummer
		        $healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		
		        // Health insurance status - ISPC- 1368 // 150611
		        $healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];

		        
		        
		        //TODO-237  DTA ND -- QTZ 26.04.2016

		        ////Institutskennzeichen
		        //$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		        ////Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
		        //$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		        
		        if(!empty($subdivisions[$k_ipid_hi]))
		        {
		            $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $subdivisions[$k_ipid_hi]['iknumber'];
		            $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling']; // Billing IK - Abrechnungs IK -- RWH - ISPC-1405 // 150716
		        }
		        else
		        {
		            $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		            $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		        }
		    }
		
		    
		    //8. get (HD) main diagnosis
		    $main_abbr = "'HD'";
		    $main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		
		    foreach($main_diag as $key => $v_diag)
		    {
		        $type_arr[] = $v_diag['id'];
		    }
		
		    $pat_diag = new PatientDiagnosis();
		    $dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		
		    foreach($dianoarray as $k_diag => $v_diag)
		    {
		        //append diagnosis in patient data
		        $diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
		        //$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
		        //ISPC-2489 Lore 26.11.2019
		        $diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
		        
		        $patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
		    }
		
		    //9. get user data
		    $user_details = User::getUserDetails($userid);
		    
		    
		    
		    //CALCULUS REQUIRED DATA START
		    //get all sets form types
		    $set_one = $form_types->get_form_types($clientid, '1');
		    foreach($set_one as $k_set_one => $v_set_one)
		    {
		        $set_one_ids[] = $v_set_one['id'];
		    }
		    
		    $set_two = $form_types->get_form_types($clientid, '2');
		    foreach($set_two as $k_set_two => $v_set_two)
		    {
		        $set_two_ids[] = $v_set_two['id'];
		    }
		    
		    $set_three = $form_types->get_form_types($clientid, '3');
		    foreach($set_three as $k_set_three => $v_set_three)
		    {
		        $set_three_ids[] = $v_set_three['id'];
		    }
		    
		    
		    $set_fourth = $form_types->get_form_types($clientid, '4');
		    foreach($set_fourth as $k_set_fourth => $v_set_fourth)
		    {
		        $set_fourth_ids[] = $v_set_fourth['id'];
		    }
		    
		    $set_ids['one'] = $set_one_ids;
		    $set_ids['two'] = $set_two_ids;
		    $set_ids['three'] = $set_three_ids;
		    $set_ids['fourth'] = $set_fourth_ids;
		    
		    //get active days array
		    $active_days = array();
		    foreach($patient_details as $k_ipid => $pat_details)
		    {
		        //active days "Y-m-d"
		        $active_days[$k_ipid] = $pat_details['active_days'];
		    
		        array_walk($active_days[$k_ipid], function(&$value) {
		            $value = date("Y-m-d", strtotime($value));
		        });
		    
		    
		            //hospital days cs
		            if(!empty($pat_details['hospital']['real_days_cs']))
		            {
		                $hospital_days_cs[$k_ipid] = $pat_details['hospital']['real_days_cs'];
		                array_walk($hospital_days_cs[$k_ipid], function(&$value) {
		                    $value = date("Y-m-d", strtotime($value));
		                });
		            }
		    
		            //hospiz days cs
		            if(!empty($pat_details['hospiz']['real_days_cs']))
		            {
		                $hospiz_days_cs[$k_ipid] = $pat_details['hospiz']['real_days_cs'];
		                array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
		                    $value = date("Y-m-d", strtotime($value));
		                });
		            }
		    
		            if(empty($hospital_days_cs[$k_ipid]))
		            {
		                $hospital_days_cs[$k_ipid] = array();
		            }
		    
		            if(empty($hospiz_days_cs[$k_ipid]))
		            {
		                $hospiz_days_cs[$k_ipid] = array();
		            }
		    
		            //joined hospital hospiz days cs
		            $hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
		    
		    
		            //used in flatrate
		            if(empty($patients_periods[$k_ipid]))
		            {
		                $patients_periods[$k_ipid] = array();
		            }
		    
		            array_walk_recursive($pat_details['active_periods'], function(&$value) {
		                $value = date("Y-m-d", strtotime($value));
		            });
		            $patients_periods[$k_ipid] = array_merge($patients_periods[$k_ipid], $pat_details['active_periods']);
		    }
		    
		    foreach($patients_periods as $k_ipid => $v_periods)
		    {
		        foreach($v_periods as $k_period => $v_period)
		        {
		            if(empty($patients_periods_days[$k_ipid]))
		            {
		                $patients_periods_days[$k_ipid] = array();
		            }
		    
		            $period_days = $patientmaster->getDaysInBetween($v_period['start'], $v_period['end']);
		            $patients_periods_days[$k_ipid] = array_merge($patients_periods_days[$k_ipid], $period_days);
		            $period_days = array();
		        }
		    }
		    
		    
		    //get sapv days cs
		    $sapv_days_cs = $this->get_period_sapvs($invoices_patients, $active_days, $hospital_hospiz_days_cs);
		    
		    //get no sapv days
		    $days_nosapv_cs = $this->nosapv_days($active_days, $hospital_hospiz_days_cs, $sapv_days_cs);
		    //flatrate required data end
		    
		    //get patients courses
		    $course_days_all = $this->get_patients_period_course($invoices_patients);
		    

		    foreach($invoices_patients as $k_ipid => $v_ipid)
		    {
		    
		        if(isset($patient_data_n[$v_ipid]['discharge_dates'])){
		    
		            //last discharge date
		            $patients_discharge_date[$v_ipid] = end($patient_data_n[$v_ipid]['discharge_dates']);
		    
		            foreach($course_days_all[$v_ipid] as $pd_done_date => $pc_shortcut_data)
		            {
		                if(in_array(date("Y-m-d",strtotime($pd_done_date)),array_keys($patient_data_n[$v_ipid]['discharge_dates_sYmd'])) && strtotime($pd_done_date) > strtotime($patient_data_n[$v_ipid]['discharge_dates_sYmd'][date("Y-m-d",strtotime($pd_done_date))]))
		                {
		                    unset($course_days_all[$v_ipid][$pd_done_date]);
		                }
		            }
		        }
		    
		        foreach($course_days_all[$v_ipid] as $pd_done_dates => $pc_shortcut_datad)
		        {
		            foreach($pc_shortcut_datad as $c_is=>$csh){
		                $course_days[$v_ipid][date('Y-m-d',strtotime($pd_done_dates))][$c_is] = $csh;
		            }
		        }
		    }
		    
		    
		    //get contact forms
		    $contact_forms_overall_days = $this->get_patients_period_cf($invoices_patients);
		    
		    $exclude_after_discharge_overall[] = "999999";
		    foreach($invoices_patients as $k_ipid => $v_ipid)
		    {
		        //last discharge date
		        $patients_discharge_date[$v_ipid] = end($patient_data_n[$v_ipid]['discharge_dates']);
		    
		        foreach($contact_forms_overall_days[$v_ipid] as $k_cf_day => $v_cf_data)
		        {
		            foreach($v_cf_data as $k_cfo => $v_cfo)
		            {
		    
		                if(is_numeric($k_cfo))
		                {
		                    if(strtotime($v_cfo['start_date']) > strtotime($patients_discharge_date[$v_ipid]) && $patient_data_n [$v_ipid]['patient']['isdischarged']== '1')
		                    {
		                        //excluded cf after last discharge
		                        $exclude_after_discharge_overall[] = $v_cfo['id'];
		                    }
		                    $contact_forms_ids_overall[] = $v_cfo['id'];
		                }
		            }
		        }
		        ksort($contact_forms_days[$v_ipid]);
		    }
		    // $current_period
		    
		    
		    $classification_data_overall = $block_classification->get_multiple_block_classification($invoices_patients, $contact_forms_ids_overall);
		    //CALCULUS REQUIRED DATA END
		    //CALCULUS START
		    //calculate flatrate for all provided patients
		    $flatrate = $this->multi_patients_flatrate_days_lag($invoices_patients, $clientid, $current_period, $active_days, $days_nosapv_cs, $hospital_hospiz_days_cs, $patient_details);
		    //calculate overall shortcuts for all provided patients
		    $overall_patients_shortcuts_nofl = $this->patients_performance_overall($clientid, $invoices_patients, $active_days, $set_ids, $flatrate, false, $master_price_list, $patients_periods_days, $hospital_hospiz_days_cs, $sapv_days_cs, $course_days, $contact_forms_overall_days, $classification_data_overall,$patient_days2locationtypes);
		    $flatrate_continued = $this->multi_patients_flatrate_days_continued_lag($clientid, $invoices_patients, $current_period, $active_days, $days_nosapv_cs, $hospital_hospiz_days_cs, $patient_details, $overall_patients_shortcuts_nofl);
		    $overall_patients_shortcuts = $this->patients_performance_overall_lag_bw_sapv($clientid, $invoices_patients, $active_days, $set_ids, $flatrate,$flatrate_continued, $master_price_list, $patients_periods_days, $hospital_hospiz_days_cs, $sapv_days_cs, $course_days, $contact_forms_overall_days, $classification_data_overall,$patient_discharge_dates,$exclude_after_discharge_overall,$patient_days2locationtypes);
		    
		    
		    
		    $visits_data['items'] = array();
 		    foreach($overall_patients_shortcuts as $ipid=>$details){
 		        
 		        if(!empty($flatrate[$ipid]['pay_days'])){
 		            foreach ($flatrate[$ipid]['pay_days'] as $k=>$fday){
 		                $visits_data['items'][$ipid]['37b1']['actions'][]= $details['extra'][$fday]['37b1'];
 		                $inv[$ipid][$fday]['37b1'] +=1;
 		            }
 		        }
 		        if(!empty($flatrate_continued[$ipid]['pay_days'])){
 		            foreach ($flatrate_continued[$ipid]['pay_days'] as $k=>$fcday){
 		                if( !in_array($fcday,$flatrate[$ipid]['pay_days']) ){
 		                    $visits_data['items'][$ipid]['37b1']['actions'][] = $details['extra'][$fcday]['37b1'];
 		                   $inv[$ipid][$fcday]['37b1cont'] +=1;
 		                }
 		            }
 		        }
 		        
 		        
 		        
                foreach($details['shortcuts_dates'] as $date => $date_shs){
                    foreach($date_shs as $k=>$sh){
                        if($sh != "37b1"){
                            if($sh == "37b2")
                            {
                                $visits_data['items'][$ipid][$sh]['actions'][] = $details['extra'][$date][$sh];
                                $inv[$ipid][$date][$sh] +=1;
                            } 
                            else 
                            { 
                                if(!empty($details['extra'][$date][$sh])){
                                    
                                    foreach($details['extra'][$date][$sh] as $iid => $item_details){
                                       $visits_data['items'][$ipid][$sh]['actions'][] = $item_details;
                                    } 
                                }
                               $inv[$ipid][$date][$sh] +=1;
                            }
                        }
                    }
                }
 		    }
 		    
 		    if($_REQUEST['xdg']){
	 		    echo "<pre>";
	 		    print_r($patient_days2locationtypes);
	 		    
	 		    print_r($flatrate);
	 		    print_r($flatrate_continued);
	 		    print_r($overall_patients_shortcuts);
	 		    print_r($details['shortcuts_dates']);
	 		    print_r($visits_data['items']);
	 		    
	 		    exit;
 		    }
		    //reloop the invoices data array
		    
 		    $added2xml = array();
		    foreach($bw_invoices_data as $k_invoice => $v_invoice)
		    {
		        if(!$master_data['invoice_' . $k_invoice])
		        {
		            $master_data['invoice_' . $k_invoice] = array();
		        }
		
		        $master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
		        $master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
		        $master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
		        $master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		        $master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
		        $master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
		        $master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
		        $master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
		        $master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
		        $master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		
		        $master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		
		        $sapv_details[$v_invoice['ipid']][$k_invoice] = end($sapv_data[$v_invoice['ipid']]);

		        $master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_start'];
		        $master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_end'];
		        $master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_date'];
		        $master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_number'];
		        $master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['create_date'];
		        
		        $inv_items = array();
		        $added2xml[$k_invoice] = array();
		        foreach($v_invoice['items'] as $k_item => $v_item)
		        {
		            if($v_item['qty'] > 0){
		            	
		                if($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] === null){
		                	
		                	$day_location_type = $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_invoice['create_date']))];
		                	
                            //if item wass added on invoice - this means that item has no related data- so display it as it is
                            $inv_actions['dta_name'] = $v_item['shortcut'];
                            $inv_actions['shortcut'] = $v_item['shortcut'];
                            $inv_actions['dta_id'] = $curent_pricelist[$day_location_type][$v_item['shortcut']]['dta_id'];
                            $inv_actions['day'] = date('Ymd', strtotime($v_invoice['create_date']));
                            $inv_actions['start_time'] = '0000';
                            $inv_actions['end_time'] = '2359'; 
                            $inv_actions['price'] = number_format($curent_pricelist[$day_location_type][$v_item['shortcut']]['dta_price'], '2', ',', '');
                            $inv_actions['custom'] = $v_item['1'];
                            
                            $inv_items['actions']['action_0'] = $inv_actions;
		                    
		                } else {
		                    
		                    $inv_actions = array();
		                    $actions_count = 1;
		                    $v_action = array();
		                    
		                    $ident = "";
		                    foreach($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
		                    {
		                        
		                    	$ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].$v_action['start_time'].$v_action['end_time'].$v_action['location_type'];
								if( ! in_array($ident,$added2xml[$k_invoice]) &&  $actions_count <= $v_item['qty']  && in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )  
										&& $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_action['day']))] == $v_action['location_type']
										){

									$inv_actions['dta_name'] = $this->view->translate('shortcut_description_'.$v_item['shortcut']);
									$inv_actions['shortcut'] = $v_item['shortcut'];
									$inv_actions['dta_id'] = $v_action['dta_id'];
									//$inv_actions['dta_price'] = $v_action['dta_price'];
									$inv_actions['day'] = $v_action['day'];
									
									//ISPC-2549 Carmen 21.02.2020 for all start_time should be 0:00 and end_time 23:59 for old bw
									if($type == "old"){
										/* $inv_actions['start_time'] = $v_action['start_time'];
										$inv_actions['end_time'] = $v_action['end_time']; */
									
										$inv_actions['start_time'] = '0000';
										$inv_actions['end_time'] = '2359';
									}
									else 
									{
										$inv_actions['start_time'] = $v_action['start_time'];
										$inv_actions['end_time'] = $v_action['end_time'];
									}
									
									$inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
									       		                            
									$inv_items['actions']['action_' . $k_action] = $inv_actions;
									$actions_count++; 
									$added2xml[$k_invoice][] = $ident ;
								}    
		                    }
		                }
		                
		                $master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
		                $inv_actions = array();
		                $inv_items = array();
		            }
		        }
		        $master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    }
		    
		    return $master_data;
		}		
		
		
    /**
     * 
     * @param string $type
     * @param unknown $clientid
     * @param unknown $userid
     * @param unknown $post
     * @param string $source
     * @return multitype:|string
     * copy by fn gather_dta_bw_data
     * Ancuta 22.08.2019
     */
		public function gather_dta_bw_data_full($type = "old", $clientid, $userid, $post,$source='dta')
		{
		    // GET SELECTED MONTH DETAILS
		    if($source == "invoicejournal"){
		        $month['start'] = date('Y-m-d', strtotime($post['start_date']));
		        $month['end'] = date("Y-m-d", strtotime($post['end_date']));
		    
		    } else{
		    
		        $month['start'] = date('Y-m-d', strtotime($post['search'].'-01'));
		        $month['end'] = date("Y-m-t", strtotime($month['start']));
		    }
		    
		    $patientmaster = new PatientMaster();
		    $form_types = new FormTypes();
		    $p_list = new PriceList();
		    $block_classification = new FormBlockClassification();
		    
		    
		    //1. get all selected invoices data
		    if($type == "old"){
		        $bw_invoices = new BwInvoices();
		        $bw_invoices_data = $bw_invoices->get_multiple_invoices($post['invoices']['bw']);
		    } else{
		        $bw_invoices = new BwInvoicesNew();
		        $bw_invoices_data = $bw_invoices->get_multiple_invoices($post['invoices']['bw']);
		    }
		    
		    if ($bw_invoices_data === false) {
		        return array();
		    }
		    
		    $invoiced_days = array();
		    foreach($bw_invoices_data as $k_inv => $v_inv)
		    {
		        $invoices_patients[] = $v_inv['ipid'];
		    
		        $invoice_period['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		        $invoice_period['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		        $invoice_period['c_month_end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    
		        $patients_invoices_periods[$v_inv['ipid']] = $invoice_period;
		        $invoiced_days[$v_inv['ipid']][$v_inv['id']] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		        $overall_invoiced_days[$v_inv['ipid']][] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		    }
		    
		    foreach($overall_invoiced_days as $pint =>$iperiods){
		    
		        foreach($iperiods as $ik=>$pdates){
		            foreach($pdates as $k=>$date){
		                if(!in_array($date,$all_invoiced_dates)){
		                    $all_invoiced_dates[] = $date;
		                }
		            }
		        }
		    }
		    asort($all_invoiced_dates);
		    $all_invoices_dates = array_values($all_invoiced_dates);
		    
		    $overall_invoiced_period['start'] = $all_invoices_dates[0];
		    $overall_invoiced_period['end'] = end($all_invoices_dates);
		    
		    $current_period = $overall_invoiced_period;
		    
		    //2. get all required client data
		    $clientdata = Pms_CommonData::getClientData($clientid);
		    $client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
		    $client_data['client']['team_name'] = $clientdata[0]['team_name'];
		    $client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
		    $client_data['client']['phone'] = $clientdata[0]['phone'];
		    $client_data['client']['fax'] = $clientdata[0]['fax'];
		    
		    
		    
		    //3. get pflegestuffe in current period
		    $pflege = new PatientMaintainanceStage();
		    $pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		    
		    foreach($pflege_arr as $k_pflege => $v_pflege)
		    {
		        $grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
		    }
		    
		    foreach($grouped_pflege as $k_gpflege => $v_gpflege)
		    {
		        $last_pflege = end($v_gpflege);
		    
		        if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
		        {
		            //$k_gpflege = patient epid
		            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
		        }
		        else
		        {
		            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
		        }
		    }
		    
		    //4. get all involved patients required data
		    
		    $sql = 'e.epid, p.ipid, e.ipid,';
		    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
		    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
		    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
		    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
		    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
		    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
		    
		    //4.1 get patients readmission details
		    $patient_details  = array();
		    $conditions['periods'][0]['start'] = '2009-01-01';
		    $conditions['periods'][0]['end'] = date('Y-m-d');
		    $conditions['client'] = $clientid;
		    $conditions['ipids'] = $invoices_patients;
		    $patient_details = Pms_CommonData::patients_days($conditions,$sql);
		    
		    $patient_days2locationtypes = array();
		    $hospital_days_cs_dmY = array();
		    $hospiz_days_cs_dmY = array();
		    
		    foreach($patient_details as $k_pat_ipid => $v_pat_details)
		    {
		        $patient_data[$k_pat_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
		        $patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['details']['first_name'];
		        $patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['details']['last_name'];
		        $patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['details']['birthd']);
		        $patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['details']['street1'];
		        $patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['details']['zip'];
		        $patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['details']['city'];
		        $patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
		    
		        $patient_data_n[$k_pat_ipid]['patient']['isdischarged'] = $v_pat_details['details']['isdischarged'];
		    
		        //hospital days cs
		        if(!empty($v_pat_details['hospital']['real_days_cs']))
		        {
		            $hospital_days_cs_dmY[$k_pat_ipid] = $v_pat_details['hospital']['real_days_cs'];
		        }
		    
		        //hospiz days cs
		        if(!empty($v_pat_details['hospiz']['real_days_cs']))
		        {
		            $hospiz_days_cs_dmY[$k_pat_ipid] = $v_pat_details['hospiz']['real_days_cs'];
		        }
		    
		    
		    
		        if(!empty($v_pat_details['discharge_details'])){
		    
		            foreach($v_pat_details['discharge_details'] as $dis_id => $dis_data) {
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates'][] = $dis_data['discharge_date'];
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates_sYmd'][date('Y-m-d',strtotime($dis_data['discharge_date']))] = $dis_data['discharge_date'];
		    
		                $patient_discharge_dates[$v_pat_details['details'] ['ipid']][] = $dis_data['discharge_date'];
		            }
		        } else {
		            if(!empty($v_pat_details['discharge'])){
		    
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates'] = $v_pat_details['discharge'];
		                $patient_data_n[$v_pat_details['details'] ['ipid']]['discharge_dates_sYmd'][date('Y-m-d',strtotime($v_pat_details['discharge']))] = date("Y-m-d H:i:s",strtotime($v_pat_details['discharge']));
		                $patient_discharge_dates[$v_pat_details['details'] ['ipid']] = $v_pat_details['discharge'];
		            }
		        }
		    
		        // Location details - ISPC-2100 BW DTA
		        foreach($v_pat_details['locations'] as $pat_location_row_id => $pat_location_data)
		        {
		            foreach($pat_location_data['days'] as $kl=>$lday)
		            {
		                if(in_array($lday,$v_pat_details['real_active_days'])  )
		                {
		                    if( empty($pat_location_data['type'])){
		                        $pat_location_data['type'] = 0 ;
		                    }
		    
		                    if($pat_location_data['type'] == "4" )
		                    {
		                        $patient_days2locationtypes[$k_pat_ipid][$lday][] = "3";
		                    }
		                    else
		                    {
		                        $patient_days2locationtypes[$k_pat_ipid][$lday][] = $pat_location_data['type'];
		                    }
		                }
		            }
		        }
		    }
		    
		    
		    foreach( $patient_days2locationtypes as $pipid=>$locdata){
		        foreach($locdata as $loc_day => $day_loc_types){
		            $del_val = "1";
		            if ( ! in_array($loc_day,$hospital_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
		                unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
		            }
		    
		            /*
		             $del_val = "2";
		             if ( ! in_array($loc_day,$hospiz_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
		             unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
		             }
		             */
		        }
		    }
		    foreach($patient_days2locationtypes as $pipid=>$locdata){
		        foreach($locdata as $loc_day => $day_loc_types){
		            $patient_days2locationtypes[$pipid][$loc_day] = end($day_loc_types);
		        }
		    }
		    
		    
		    // 5 Get  patients sapv data
		    $patientmaster = new PatientMaster();
		    $dropSapv = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->whereIn('ipid', $invoices_patients)
		    ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    ->andWhere('isdelete=0')
		    ->orderBy('verordnungam ASC');
		    $droparray = $dropSapv->fetchArray();
		    
		    $all_sapv_days = array();
		    $temp_sapv_days = array();
		    $s=0;
		    
		    foreach($droparray as $k_sapv => $v_sapv)
		    {
		        $r1['start'][$v_sapv['ipid']][$s] = "";
		        $r1['end'][$v_sapv['ipid']][$s] = "";
		         
		        $r2['start'][$v_sapv['ipid']][$s] = "";
		        $r2['end'][$v_sapv['ipid']][$s] = "";
		    
		        if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
		            // no sapv taken here - becouse it is considered to be fully denied
		        }
		        else
		        {
		            $r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
		            $r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
		    
		            $r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
		            $r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
		    
		            if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
		            {
		    
		                //aditional data from sapv which was added on 16.10.2014
		                if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
		                {
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
		                }
		    
		                if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
		                } else{
		                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
		                }
		    
		    
		                if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
		                {
		                    $v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
		                }
		    
		    
		                $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		                $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		    
		                $temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		    
		    
		                //aditional data from sapv which was added on 31.10.2014
		                $sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
		                $sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
		                $sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
		                $sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		    
		                foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
		                {
		                    if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
		                    {
		                        $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
		                    }
		    
		                    $current_verordnet = explode(',', $v_sapv['verordnet']);
		                    $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		    
		                    asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
		                    $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
		                }
		    
		                $s++;
		            }
		        }
		    }
		    
		    //6. pricelist
		    $p_list = new PriceList();
		    // ISPC-2100 BW DTA
		    //$master_price_list = $p_list->get_period_price_list($overall_invoiced_period['start'], $overall_invoiced_period['end']); //get bra sapv pricelist and then shortcuts
		    $master_price_list = $p_list->get_period_price_list_specific($overall_invoiced_period['start'], $overall_invoiced_period['end']); //get bra sapv pricelist and then shortcuts
		    $curent_pricelist = $master_price_list[$overall_invoiced_period['start']][0];
		    
		    $master_price_list2pat =array();
		    foreach($invoices_patients as $kmp_ipid => $vmp_ipid)
		    {
		        $master_price_list2pat[$vmp_ipid] = $p_list->get_period_price_list_specific($overall_invoiced_period['start'], $overall_invoiced_period['end']);
		    }
		    
		    //7. patients health insurance
		    
		    $ppun = new PpunIpid();
		    //used modules checks
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("88", $clientid))
		    {
		        $ppun_module = "1";
		    }
		    else
		    {
		        $ppun_module = "0";
		    }
		    
		    if($modules->checkModulePrivileges("90", $clientid))
		    {
		        $debtor_number_module = "1";
		    }
		    else
		    {
		        $debtor_number_module = "0";
		    }
		    
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		    
		    $status_int_array = array("M" => "1", "F" => "3", "R" => "5");
		    // ispc = M => 1 = Versicherungspflichtige und -berechtigte
		    // ispc = F => 3 = Familienversicherte
		    // ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
		    //TODO-3528 Lore 12.11.2020
		    $modules = new Modules();
		    $extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
		    if($extra_healthinsurance_statuses){
		        $status_int_array += array(
		            "00" => "00",          //"Gesamtsumme aller Stati",
		            "11" => "11",          //"Mitglieder West",
		            "19" => "19",          //"Mitglieder Ost",
		            "31" => "31",          //"Angehörige West",
		            "39" => "39",          //"Angehörige Ost",
		            "51" => "51",          //"Rentner West",
		            "59" => "59",          //"Rentner Ost",
		            "99" => "99",          //"nicht zuzuordnende Stati",
		            "07" => "07",          //"Auslandsabkommen"
		        );
		    }
		    //.
		    
		    $company_ids[] = '9999999999999';
		    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    {
		        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		    
		        if($v_healthinsu['companyid'] != '0')
		        {
		            $company_ids[] = $v_healthinsu['companyid'];
		        }
		    }
		    
		    $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		     
		     
		    //7.1 get health insurance subdivizions
		    $symperm = new HealthInsurancePermissions();
		    $divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		    
		    if($divisions)
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
		    							->whereIn("company_id", $company_ids)
		    							->andWhereIn("ipid", $invoices_patients);
		        $hi2s_arr = $hi2s->fetchArray();
		    }
		    
		    if($hi2s_arr)
		    {
		        foreach($hi2s_arr as $k_subdiv => $v_subdiv)
		        {
		            if($v_subdiv['subdiv_id'] == "3")
		            {
		                $subdivisions[$v_subdiv['ipid']] = $v_subdiv;
		            }
		        }
		    }
		    $ipid2debtor = array();
		    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    {
		        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		        {
		            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		    
		            if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
		            {
		                $v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
		            }
		    
		            if(strlen($healtharray['name']) > '0')
		            {
		                $ins_name = $healtharray['name'];
		            }
		            else if(strlen($v_health_insurance[0]['company_name']) > '0')
		            {
		                $ins_name = $v_health_insurance[0]['company_name'];
		            }
		        }
		    
		        //health insurance name
		        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    
		        //Versichertennummer
		        $healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		    
		        // Health insurance status - ISPC- 1368 // 150611
		        $healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
		    
		    
		    
		        //TODO-237  DTA ND -- QTZ 26.04.2016
		    
		        ////Institutskennzeichen
		        //$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		        ////Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
		        //$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		    
		        if(!empty($subdivisions[$k_ipid_hi]))
		        {
		            $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $subdivisions[$k_ipid_hi]['iknumber'];
		            $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling']; // Billing IK - Abrechnungs IK -- RWH - ISPC-1405 // 150716
		        }
		        else
		        {
		            $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		            $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		        }
		        
		        
		        
		        if($ppun_module == "1" && $v_health_insurance[0]['privatepatient'] == "1")
		        {
		            //get ppun (private patient unique number)
		            $ppun_number = $ppun->check_patient_ppun($k_ipid_hi, $clientid);
		            if($ppun_number)
		            {
		                $ipid2debtor[$k_ipid_hi]['debtor_number'] = $ppun_number['ppun'];
		                $ipid2debtor[$k_ipid_hi]['debitor_number'] = $ppun_number['ppun'];
		            }
		        }
		        	
		        if($debtor_number_module == "1" && $v_health_insurance[0]['privatepatient'] == "0")
		        {
		            //get debtor number from patient healthinsurance
		            if(strlen($v_health_insurance[0]['ins_debtor_number']) > '0')
		            {
		                $ipid2debtor[$k_ipid_hi]['debtor_number'] = $v_health_insurance[0]['ins_debtor_number'];
		                $ipid2debtor[$k_ipid_hi]['debitor_number'] = $v_health_insurance[0]['ins_debtor_number'];
		            }
		        }
		    }
		    
		    //8. get (HD) main diagnosis
		    $main_abbr = "'HD'";
		    $main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		    
		    foreach($main_diag as $key => $v_diag)
		    {
		        $type_arr[] = $v_diag['id'];
		    }
		    
		    $pat_diag = new PatientDiagnosis();
		    $dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		    
		    foreach($dianoarray as $k_diag => $v_diag)
		    {
		        //append diagnosis in patient data
		        $diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
		        //$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
		        //ISPC-2489 Lore 26.11.2019
		        $diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
		        
		        $patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
		    }
		    
		    //9. get user data
		    $user_details = User::getUserDetails($userid);
		    
		    
		    
		    //CALCULUS REQUIRED DATA START
		    //get all sets form types
		    $set_one = $form_types->get_form_types($clientid, '1');
		    foreach($set_one as $k_set_one => $v_set_one)
		    {
		        $set_one_ids[] = $v_set_one['id'];
		    }
		    
		    $set_two = $form_types->get_form_types($clientid, '2');
		    foreach($set_two as $k_set_two => $v_set_two)
		    {
		        $set_two_ids[] = $v_set_two['id'];
		    }
		    
		    $set_three = $form_types->get_form_types($clientid, '3');
		    foreach($set_three as $k_set_three => $v_set_three)
		    {
		        $set_three_ids[] = $v_set_three['id'];
		    }
		    
		    
		    $set_fourth = $form_types->get_form_types($clientid, '4');
		    foreach($set_fourth as $k_set_fourth => $v_set_fourth)
		    {
		        $set_fourth_ids[] = $v_set_fourth['id'];
		    }
		    
		    $set_ids['one'] = $set_one_ids;
		    $set_ids['two'] = $set_two_ids;
		    $set_ids['three'] = $set_three_ids;
		    $set_ids['fourth'] = $set_fourth_ids;
		    
		    //get active days array
		    $active_days = array();
		    foreach($patient_details as $k_ipid => $pat_details)
		    {
		        //active days "Y-m-d"
		        $active_days[$k_ipid] = $pat_details['active_days'];
		    
		        array_walk($active_days[$k_ipid], function(&$value) {
		            $value = date("Y-m-d", strtotime($value));
		        });
		    
		    
		            //hospital days cs
		            if(!empty($pat_details['hospital']['real_days_cs']))
		            {
		                $hospital_days_cs[$k_ipid] = $pat_details['hospital']['real_days_cs'];
		                array_walk($hospital_days_cs[$k_ipid], function(&$value) {
		                    $value = date("Y-m-d", strtotime($value));
		                });
		            }
		    
		            //hospiz days cs
		            if(!empty($pat_details['hospiz']['real_days_cs']))
		            {
		                $hospiz_days_cs[$k_ipid] = $pat_details['hospiz']['real_days_cs'];
		                array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
		                    $value = date("Y-m-d", strtotime($value));
		                });
		            }
		    
		            if(empty($hospital_days_cs[$k_ipid]))
		            {
		                $hospital_days_cs[$k_ipid] = array();
		            }
		    
		            if(empty($hospiz_days_cs[$k_ipid]))
		            {
		                $hospiz_days_cs[$k_ipid] = array();
		            }
		    
		            //joined hospital hospiz days cs
		            $hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
		    
		    
		            //used in flatrate
		            if(empty($patients_periods[$k_ipid]))
		            {
		                $patients_periods[$k_ipid] = array();
		            }
		    
		            array_walk_recursive($pat_details['active_periods'], function(&$value) {
		                $value = date("Y-m-d", strtotime($value));
		            });
		            $patients_periods[$k_ipid] = array_merge($patients_periods[$k_ipid], $pat_details['active_periods']);
		    }
		    
		    foreach($patients_periods as $k_ipid => $v_periods)
		    {
		        foreach($v_periods as $k_period => $v_period)
		        {
		            if(empty($patients_periods_days[$k_ipid]))
		            {
		                $patients_periods_days[$k_ipid] = array();
		            }
		    
		            $period_days = $patientmaster->getDaysInBetween($v_period['start'], $v_period['end']);
		            $patients_periods_days[$k_ipid] = array_merge($patients_periods_days[$k_ipid], $period_days);
		            $period_days = array();
		        }
		    }
		    
		    //get sapv days cs
		    $sapv_days_cs = $this->get_period_sapvs($invoices_patients, $active_days, $hospital_hospiz_days_cs);
		    
		    //get no sapv days
		    $days_nosapv_cs = $this->nosapv_days($active_days, $hospital_hospiz_days_cs, $sapv_days_cs);
		    //flatrate required data end
		    
		    //get patients courses
		    $course_days_all = $this->get_patients_period_course($invoices_patients);
		    
		    
		    foreach($invoices_patients as $k_ipid => $v_ipid)
		    {
		    
		        if(isset($patient_data_n[$v_ipid]['discharge_dates'])){
		    
		            //last discharge date
		            $patients_discharge_date[$v_ipid] = end($patient_data_n[$v_ipid]['discharge_dates']);
		    
		            foreach($course_days_all[$v_ipid] as $pd_done_date => $pc_shortcut_data)
		            {
		                if(in_array(date("Y-m-d",strtotime($pd_done_date)),array_keys($patient_data_n[$v_ipid]['discharge_dates_sYmd'])) && strtotime($pd_done_date) > strtotime($patient_data_n[$v_ipid]['discharge_dates_sYmd'][date("Y-m-d",strtotime($pd_done_date))]))
		                {
		                    unset($course_days_all[$v_ipid][$pd_done_date]);
		                }
		            }
		        }
		    
		        foreach($course_days_all[$v_ipid] as $pd_done_dates => $pc_shortcut_datad)
		        {
		            foreach($pc_shortcut_datad as $c_is=>$csh){
		                $course_days[$v_ipid][date('Y-m-d',strtotime($pd_done_dates))][$c_is] = $csh;
		            }
		        }
		    }
		    
		    
		    //get contact forms
		    $contact_forms_overall_days = $this->get_patients_period_cf($invoices_patients);
		    
		    $exclude_after_discharge_overall[] = "999999";
		    foreach($invoices_patients as $k_ipid => $v_ipid)
		    {
		        //last discharge date
		        $patients_discharge_date[$v_ipid] = end($patient_data_n[$v_ipid]['discharge_dates']);
		    
		        foreach($contact_forms_overall_days[$v_ipid] as $k_cf_day => $v_cf_data)
		        {
		            foreach($v_cf_data as $k_cfo => $v_cfo)
		            {
		    
		                if(is_numeric($k_cfo))
		                {
		                    if(strtotime($v_cfo['start_date']) > strtotime($patients_discharge_date[$v_ipid]) && $patient_data_n [$v_ipid]['patient']['isdischarged']== '1')
		                    {
		                        //excluded cf after last discharge
		                        $exclude_after_discharge_overall[] = $v_cfo['id'];
		                    }
		                    $contact_forms_ids_overall[] = $v_cfo['id'];
		                }
		            }
		        }
		        ksort($contact_forms_days[$v_ipid]);
		    }
		    // $current_period
		    
		    
		    $classification_data_overall = $block_classification->get_multiple_block_classification($invoices_patients, $contact_forms_ids_overall);
		    //CALCULUS REQUIRED DATA END
		    //CALCULUS START
		    //calculate flatrate for all provided patients
		    $flatrate = $this->multi_patients_flatrate_days_lag($invoices_patients, $clientid, $current_period, $active_days, $days_nosapv_cs, $hospital_hospiz_days_cs, $patient_details);
		    //calculate overall shortcuts for all provided patients
		    $overall_patients_shortcuts_nofl = $this->patients_performance_overall($clientid, $invoices_patients, $active_days, $set_ids, $flatrate, false, $master_price_list, $patients_periods_days, $hospital_hospiz_days_cs, $sapv_days_cs, $course_days, $contact_forms_overall_days, $classification_data_overall,$patient_days2locationtypes);
		    $flatrate_continued = $this->multi_patients_flatrate_days_continued_lag($clientid, $invoices_patients, $current_period, $active_days, $days_nosapv_cs, $hospital_hospiz_days_cs, $patient_details, $overall_patients_shortcuts_nofl);
		    $overall_patients_shortcuts = $this->patients_performance_overall_lag_bw_sapv($clientid, $invoices_patients, $active_days, $set_ids, $flatrate,$flatrate_continued, $master_price_list, $patients_periods_days, $hospital_hospiz_days_cs, $sapv_days_cs, $course_days, $contact_forms_overall_days, $classification_data_overall,$patient_discharge_dates,$exclude_after_discharge_overall,$patient_days2locationtypes);
		    if($source == "invoicejournal"){
		        $bw_pr = new BwPerformanceRecord();
		        $bw_data = $bw_pr->get_multiple_bw_performance_record_in_period($invoices_patients, $patients_periods_days, $master_price_list2pat, $patient_days2locationtypes);
		        //97b3ed43bf22b9469bb2a464a7d0a3578e5dd590
// 		    dd($bw_data);
		        foreach($invoices_patients as $k=>$pipid){
		            if(!empty($bw_data[$pipid])){
		                // get saved info for month - to see the number of days saved in each month
		                $saved_per_moth[$pipid] = array();
		                foreach($bw_data[$pipid] as $days=>$vals){
		                    foreach($vals as $sh=>$shd){
		                        $saved_per_moth[$pipid][date("mY",strtotime($days))][] = $sh;
		                    }
		                }
		                 
		                foreach($patients_periods_days[$pipid] as $k=>$act_day){
		                    if( isset($bw_data[$pipid][$act_day]) && !empty($bw_data[$pipid][$act_day]) ){ // check if more thant the flatrate from prevoius month is saved.
		                        if(count($saved_per_moth[$pipid][date("mY",strtotime($act_day))]) >=6 ){
		                            $final_data[$pipid][$act_day] = $bw_data[$pipid][$act_day];
		                        } else {
// 		                            $final_data[$pipid][$act_day] = array_merge($master_data[$pipid][$act_day],$bw_data[$pipid][$act_day]);
		                        }
		    
		                    } else {
// 		                        $final_data[$pipid][$act_day] = $master_data[$pipid][$act_day];
		                    }
		                     
		                    if(empty($final_data[$pipid][$act_day]))
		                    {
		                        unset($final_data[$pipid][$act_day]);
		                    }
		    
		                }
		                 
// 		                $master_data[$pipid] = array();
// 		                $master_data[$pipid] = $final_data[$pipid];
		            }
		        }
		    }
// 		    		    dd($master_data);
		    // 		    dd($invoices_patients,$patients_periods_days,$bw_data);
// 		    		    dd($overall_patients_shortcuts);
		    
// 		    		    dd($flatrate);
		    $visits_data['items'] = array();
		    foreach($overall_patients_shortcuts as $ipid=>$details){
		        $visits_data['items'][$ipid] = array();
		        if(!empty($flatrate[$ipid]['pay_days'])){
		            foreach ($flatrate[$ipid]['pay_days'] as $k=>$fday){
		                $visits_data['items'][$ipid]['37b1']['actions'][]= $details['extra'][$fday]['37b1'];
		                $inv[$ipid][$fday]['37b1'] +=1;
		            }
		        }
		        if(!empty($flatrate_continued[$ipid]['pay_days'])){
		            foreach ($flatrate_continued[$ipid]['pay_days'] as $k=>$fcday){
		                if( !in_array($fcday,$flatrate[$ipid]['pay_days']) ){
		                    $visits_data['items'][$ipid]['37b1']['actions'][] = $details['extra'][$fcday]['37b1'];
		                    $inv[$ipid][$fcday]['37b1cont'] +=1;
		                }
		            }
		        }
		         
		         
		         
		        foreach($details['shortcuts_dates'] as $date => $date_shs){
		            foreach($date_shs as $k=>$sh){
		                if($sh != "37b1"){
		                    if($sh == "37b2")
		                    {
		                        $visits_data['items'][$ipid][$sh]['actions'][] = $details['extra'][$date][$sh];
		                        $inv[$ipid][$date][$sh] +=1;
		                    }
		                    else
		                    {
		                        if(!empty($details['extra'][$date][$sh])){
		    
		                            foreach($details['extra'][$date][$sh] as $iid => $item_details){
		                                $visits_data['items'][$ipid][$sh]['actions'][] = $item_details;
		                            }
		                        }
		                        $inv[$ipid][$date][$sh] +=1;
		                    }
		                }
		            }
		        }
		    }
		     
		    // add or remove - in visit data  saved values
		     
// 		     		    dd($visits_data);
		     
		    //  		    $bw_pr = new BwPerformanceRecord();
		    //  		    $bw_data = $bw_pr->get_multiple_bw_performance_record_in_period($invoices_patients, $patients_periods_days, $master_price_list2pat, $patient_days2locationtypes);
		    //  		    dd($bw_data);
		    //  		    foreach($invoices_patients as $k=>$pipid){
		    //  		        if(!empty($bw_data[$ipid])){
		     
		    //  		        }
		    //  		    }
		     
		    //  		    dd($bw_invoices_data);
		     
		    if($_REQUEST['xdg']){
		        echo "<pre>";
		        print_r($flatrate);
		        print_r($flatrate_continued);
		        print_r($overall_patients_shortcuts);
		        print_r($details['shortcuts_dates']);
		        print_r($visits_data['items']);exit;
		    }
		    //  		    dd($visits_data);
		    
		    
// 		    dd($visits_data);
// 		    print_R($visits_data); exit;
// 		    dd($bw_invoices_data,$visits_data);
		    //reloop the invoices data array
		    if($source == "invoicejournal"){
		    
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $userid = $logininfo->userid;
		        
		        if($userid == "338xx"){
		            echo "<pre>";
		            print_r(" \n visits_data: ");
		            print_r($visits_data);
		            print_r(" \n patient_days2locationtypes: ");
		            print_r($patient_days2locationtypes);
		            print_r(" \n invoiced_days: ");
		            print_r($invoiced_days);
		            print_r(" \n bw_invoices_data: ");
		            print_r($bw_invoices_data);
                    //exit;
		        }
		        
		        
		        $added2xml = array();
		        foreach($bw_invoices_data as $k_invoice => $v_invoice)
		        {
		            if(!$master_data [  $k_invoice])
		            {
		                $master_data [  $k_invoice] = array();
		            }
		            $master_data [  $k_invoice]['invoice_start'] = $v_invoice['invoice_start'];
		            $master_data [  $k_invoice]['completed_date'] = $v_invoice['completed_date'];
		            $master_data [  $k_invoice]['storno'] = $v_invoice['storno'];
		            $master_data [  $k_invoice]['ipid'] = $v_invoice['ipid'];
		            $master_data [  $k_invoice]['invoice_number'] = $v_invoice['invoice_number'];
		            $master_data [  $k_invoice]['invoice_total'] = $v_invoice['invoice_total'];
		            $master_data [  $k_invoice]['invoice_id'] = $v_invoice['id'];
		            $inv_items = array();
		            $added2xml[$k_invoice] = array();
		            foreach($v_invoice['items'] as $k_item => $v_item)
		            {
		                if($v_item['qty'] > 0){

		                    if($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] === null){
		                         
		                        $day_location_type = $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_invoice['create_date']))];
		                         
		                        //if item wass added on invoice - this means that item has no related data- so display it as it is
		                        $inv_actions['name'] = $this->view->translate('shortcut_description_'.$v_item['shortcut']);
		                        $inv_actions['shortcut'] = $v_item['shortcut'];
		                        $inv_actions['day'] = date('d.m.Y', strtotime($v_invoice['create_date']));
// 		                        $inv_actions['price'] = number_format($curent_pricelist[$day_location_type][$v_item['shortcut']]['price'], '2', ',', '');
		                        $inv_actions['price'] = number_format($v_item['price'], '2', ',', '');
		                        $inv_actions['custom'] = '1';
		                        $inv_actions['booking_account'] = $curent_pricelist[$day_location_type][$v_item['shortcut']]['booking_account'];
		                        $inv_actions['debitor_number'] = $ipid2debtor[$v_invoice['ipid']]['debitor_number'];
		                        $inv_actions['ipid'] = $v_invoice['ipid'];
		                        $inv_actions['item_qty'] = $v_item['qty'];
		                        $inv_actions['item_id'] = $v_item['id'];
		                        $inv_actions['item_price'] = number_format($v_item['price'], '2', ',', '');
// 		                        $inv_items['actions']['action_0'] = $inv_actions;
		                        $inv_items[ ] = $inv_actions;
		    
		                    } else {
		    
		                        $inv_actions = array();
		                        $actions_count = 1;
		                        $v_action = array();
		    
		                        $ident = "";
		                        $act = 0 ;
		                        foreach($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
		                        {
		    
		                            $ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].$v_action['start_time'].$v_action['end_time'].$v_action['location_type'];
// 		                            print_r("<br/>");
// 		                            print_r($ident);
// 		                            print_r("<br/>");
		                            
		                            if( ! in_array($ident,$added2xml[$k_invoice]) 
		                                &&  $actions_count <= $v_item['qty']  
		                                && in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )
		                                && $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_action['day']))] == $v_action['location_type']
		                            ){
		    
		                                $inv_actions['name'] = $this->view->translate('shortcut_description_'.$v_item['shortcut']);
		                                $inv_actions['shortcut'] = $v_item['shortcut'];
		                                //     									$inv_actions['dta_id'] = $v_action['dta_id'];
		                                //$inv_actions['dta_price'] = $v_action['dta_price'];
		                                $inv_actions['day'] = date('d.m.Y', strtotime($v_action['day']));;
		                                //     									$inv_actions['start_time'] = $v_action['start_time'];
// 		                                    									$inv_actions['end_time'] = $v_action['end_time'];
// 		                                print_r("<br/>");
// 		                                print_r("\n -- \n ");
// 		                                print_r($v_action );
                                        if(!empty($v_action['price'])){
    		                                $inv_actions['price'] = number_format($v_action['price'], '2', ',', '.');
                                        }
		                                $inv_actions['booking_account'] = $v_action['booking_account'];
		                                $inv_actions['debitor_number'] = $ipid2debtor[$v_invoice['ipid']]['debitor_number'];
		                                $inv_actions['ipid'] = $v_invoice['ipid'];
		                                $inv_actions['item_qty'] = $v_item['qty'];
		                                $inv_actions['item_id'] = $v_item['id'];
		                                $inv_actions['item_price'] = number_format($v_item['price'], '2', ',', '');
		    
// 		                                $inv_items['date_' . $act] = $inv_actions;
		                                $inv_items[$act] = $inv_actions;
		                                $actions_count++;
		                                $act++;
		                                $added2xml[$k_invoice][] = $ident ;
		                            } 
		                            //THIS IS FOR JOURNAL ONLY - On order to show data - if items were created- for invoice and date - no longer available?!??!?! TODO-4013 
		                            elseif( ! in_array($ident,$added2xml[$k_invoice])
		                                &&  $actions_count <= $v_item['qty']
		                                && !in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )
		                                && $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_action['day']))] == $v_action['location_type']
		                                ){
		                                    
		                                    $inv_actions['name'] = $this->view->translate('shortcut_description_'.$v_item['shortcut']);
		                                    $inv_actions['shortcut'] = $v_item['shortcut'];
		                                    $inv_actions['day'] = date('d.m.Y', strtotime($v_invoice['invoice_start']));;
		   
		                                    if(!empty($v_action['price'])){
		                                        $inv_actions['price'] = number_format($v_action['price'], '2', ',', '.');
		                                    }
		                                    $inv_actions['booking_account'] = $v_action['booking_account'];
		                                    $inv_actions['debitor_number'] = $ipid2debtor[$v_invoice['ipid']]['debitor_number'];
		                                    $inv_actions['ipid'] = $v_invoice['ipid'];
		                                    $inv_actions['item_qty'] = $v_item['qty'];
		                                    $inv_actions['item_id'] = $v_item['id'];
		                                    $inv_actions['item_price'] = number_format($v_item['price'], '2', ',', '');
		                                    
		                                    $inv_items[$act] = $inv_actions;
		                                    $actions_count++;
		                                    $act++;
		                                    $added2xml[$k_invoice][] = $ident ;
		                            }
		                        }
		                    }
		    
// 		                    $master_data [ $k_invoice]['product_' . $k_item] = $inv_items;
		                    $master_data [ $k_invoice]['products'][] = $inv_items;
		                    $inv_actions = array();
		                    $inv_items = array();
		                }
		            }
		        }
// 		        dd($master_data);
		    } else{
		    
		        $added2xml = array();
		        foreach($bw_invoices_data as $k_invoice => $v_invoice)
		        {
		            if(!$master_data['invoice_' . $k_invoice])
		            {
		                $master_data['invoice_' . $k_invoice] = array();
		            }
		    
		            $master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
		            $master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
		            $master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
		            $master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		            $master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
		            $master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
		            $master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
		            $master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
		            $master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
		            $master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		    
		            $master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		    
		            $sapv_details[$v_invoice['ipid']][$k_invoice] = end($sapv_data[$v_invoice['ipid']]);
		    
		            $master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_start'];
		            $master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['sapv_end'];
		            $master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_date'];
		            $master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['approved_number'];
		            $master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $sapv_details[$v_invoice['ipid']][$k_invoice]['create_date'];
		    
		            $inv_items = array();
		            $added2xml[$k_invoice] = array();
		            foreach($v_invoice['items'] as $k_item => $v_item)
		            {
		                if($v_item['qty'] > 0){
		                     
		                    if($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] === null){
		                         
		                        $day_location_type = $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_invoice['create_date']))];
		                         
		                        //if item wass added on invoice - this means that item has no related data- so display it as it is
		                        $inv_actions['dta_name'] = $v_item['shortcut'];
		                        $inv_actions['shortcut'] = $v_item['shortcut'];
		                        $inv_actions['dta_id'] = $curent_pricelist[$day_location_type][$v_item['shortcut']]['dta_id'];
		                        $inv_actions['day'] = date('Ymd', strtotime($v_invoice['create_date']));
		                        $inv_actions['start_time'] = '0000';
		                        $inv_actions['end_time'] = '2359';
		                        $inv_actions['price'] = number_format($curent_pricelist[$day_location_type][$v_item['shortcut']]['dta_price'], '2', ',', '');
		                        $inv_actions['custom'] = $v_item['1'];
		    
		                        $inv_items['actions']['action_0'] = $inv_actions;
		    
		                    } else {
		    
		                        $inv_actions = array();
		                        $actions_count = 1;
		                        $v_action = array();
		    
		                        $ident = "";
		                        foreach($visits_data['items'][$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
		                        {
		    
		                            $ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].$v_action['start_time'].$v_action['end_time'].$v_action['location_type'];
		                            if( ! in_array($ident,$added2xml[$k_invoice]) &&  $actions_count <= $v_item['qty']  && in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )
		                                && $patient_days2locationtypes[$v_invoice['ipid']][date('d.m.Y',strtotime($v_action['day']))] == $v_action['location_type']
		                            ){
		    
		                                $inv_actions['dta_name'] = $this->view->translate('shortcut_description_'.$v_item['shortcut']);
		                                $inv_actions['shortcut'] = $v_item['shortcut'];
		                                $inv_actions['dta_id'] = $v_action['dta_id'];
		                                //$inv_actions['dta_price'] = $v_action['dta_price'];
		                                $inv_actions['day'] = $v_action['day'];
		                                $inv_actions['start_time'] = $v_action['start_time'];
		                                $inv_actions['end_time'] = $v_action['end_time'];
		                                $inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
		    
		                                $inv_items['actions']['action_' . $k_action] = $inv_actions;
		                                $actions_count++;
		                                $added2xml[$k_invoice][] = $ident ;
		                            }
		                        }
		                    }
		    
		                    $master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
		                    $inv_actions = array();
		                    $inv_items = array();
		                }
		            }
		            $master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		        }
		    
		    }
		    return $master_data;
		    
		}
		
		

		private function multi_patients_flatrate_days_continued_lag($clientid, $ipids, $current_period, $active_days_overall, $days_nosapv_overall, $hospital_overall_days, $patients_details, $allowed_flatrate_restart_days)
		{
		    //days where there is sapv and at least 4w from last product
		    $patientmaster = new PatientMaster();
		
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->whereIn("ipid", $ipids)
		    ->andWhere('isdelete=0')
		    ->andWhere("status != 1")
		    ->andWhere('verordnet LIKE "%4%" OR verordnet LIKE "%3%"')
		    ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    ->orderBy("verordnungam ASC");
		    $res = $drop->fetchArray();
		
		    //get patients discharges
		    $patients_discharge = PatientDischarge::get_patients_discharge($ipids);
		
		    //get client discharge methods
		    $discharge_methods = DischargeMethod::getDischargeMethod($clientid, 0);
		
		    //get only dead methods
		    foreach($discharge_methods as $k_dis_method => $v_dis_method)
		    {
		        if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
		        {
		            $death_methods[] = $v_dis_method['id'];
		        }
		    }
		
		    $death_methods = array_values(array_unique($death_methods));
		
		    //get discharged dead date
		    foreach($patients_discharge as $k_pat_dis => $v_pat_dis)
		    {
		        if(in_array($v_pat_dis['discharge_method'], $death_methods))
		        {
		            $discharge_dead_date[$v_pat_dis['ipid']] = date('Y-m-d', strtotime($v_pat_dis['discharge_date']));
		        }
		    }
		
		
		    foreach($res as $k_sapv => $v_sapv)
		    {
		        $s_days = $patientmaster->getDaysInBetween($v_sapv['verordnungam'], $v_sapv['verordnungbis']);
		
		        if(empty($sapv_days[$v_sapv['ipid']]))
		        {
		            $sapv_days[$v_sapv['ipid']] = array();
		        }
		
		        $sapv_days[$v_sapv['ipid']] = array_merge_recursive($s_days, $sapv_days[$v_sapv['ipid']]);
		    }
		    $sapv_days[$v_sapv['ipid']] = array_values(array_unique($sapv_days[$v_sapv['ipid']]));
		
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        $active_days_overall[$v_ipid] = array_diff($active_days_overall[$v_ipid], $hospital_overall_days[$v_ipid]);
		
		        foreach($sapv_days[$v_ipid] as $k_sapv_day => $v_sapv_day)
		        {
		            $curent_sapv_day_month = date('Ym', strtotime($v_sapv_day));
		            $curent_start_month = date('Ym', strtotime($current_period['start']));
		
		            //calculate overall
		            if(count($flatrate_starts[$v_ipid]['overall_period'][$curent_sapv_day_month]) < '7' && in_array($v_sapv_day, $active_days_overall[$v_ipid]))
		            {
		                $flatrate_starts[$v_ipid]['overall_period'][$curent_sapv_day_month][] = $v_sapv_day;
		            }
		        }
		
		        $sapv_days_active[$v_ipid] = array_intersect($active_days_overall[$v_ipid], $sapv_days[$v_ipid]);
		        $sapv_days_active[$v_ipid] = array_values(array_unique($sapv_days_active[$v_ipid]));
		        asort($sapv_days[$v_ipid]);
		
		        $sapv_days[$v_ipid] = array_values($sapv_days[$v_ipid]);
		
		        $last_sapv_day[$v_ipid] = $sapv_days_active[$v_ipid][0];
		        $last_sapv_day_ts[$v_ipid] = strtotime($last_sapv_day[$v_ipid]);
		
		        if(count($sapv_days_active[$v_ipid]) > '0')
		        {
		            $last_sapv_day[$v_ipid] = $sapv_days_active[$v_ipid][0];
		            $last_sapv_day_ts[$v_ipid] = strtotime($last_sapv_day[$v_ipid]);
		
		            $currtime[$v_ipid] = $last_sapv_day_ts[$v_ipid];
		            $discharge_date = end(end($patients_details[$v_ipid]['active_periods']));
		
		            $end_time[$v_ipid] = strtotime(date('Y-m-d', strtotime($discharge_date)));
		
		
		            $counted_days[$v_ipid] = array();
		            $dbg_count[$v_ipid] = array();
		            $i = '0';
		            $first_flatrate[$v_ipid] = true;
		
	                while($currtime[$v_ipid] <= $end_time[$v_ipid])
	                {
	                    $cur_time_formated[$v_ipid] = date('Y-m-d', $currtime[$v_ipid]);
	
	                    //not found in nosapv days(possible sapv) check if is not hospital
	                    if(!in_array($cur_time_formated[$v_ipid], $days_nosapv_overall[$v_ipid]) &&
	                        in_array($cur_time_formated[$v_ipid], $allowed_flatrate_restart_days[$v_ipid]['days_products']) &&
	                        in_array($cur_time_formated[$v_ipid], $sapv_days_active[$v_ipid]) &&
	                        !in_array($cur_time_formated[$v_ipid], $hospital_overall_days[$v_ipid])
	                    )
	                    {
	                        $flatrate_structured[$v_ipid]['pay_days'][] = $cur_time_formated[$v_ipid];
	
	                        //get curent fl start day key
	                        $fl_start_key[$v_ipid] = array_search($cur_time_formated[$v_ipid], $flatrate_structured[$v_ipid]['pay_days']);
	                        $flatrate_structured[$v_ipid][$fl_start_key[$v_ipid]][] = $cur_time_formated[$v_ipid];
	
	                        $i++;
	                    }
	
	                    $currtime[$v_ipid] = strtotime('+1 day', $currtime[$v_ipid]);
	                }
		    }
		
		    foreach($flatrate_structured[$v_ipid]['pay_days'] as $kk_flatrate => $vv_flatrate)
		    {
		        $start_fl_period_day[$v_ipid] = $vv_flatrate;
		        $i = 0;
		
		        while(count($flatrate_structured[$v_ipid][$kk_flatrate]) < '7')
		        {
		            if(!in_array($start_fl_period_day[$v_ipid], $hospital_overall_days[$v_ipid]) && in_array($start_fl_period_day[$v_ipid], $sapv_days_active[$v_ipid]) && !in_array($start_fl_period_day[$v_ipid], $flatrate_structured[$v_ipid][$kk_flatrate]))
		            {
		                $flatrate_structured[$v_ipid][$kk_flatrate][] = $start_fl_period_day[$v_ipid];
		            }
		            else
		            {
		                //make sure we reach 7 elements
		                $flatrate_structured[$v_ipid][$kk_flatrate][] = $vv_flatrate;
		            }
		            asort($flatrate_structured[$v_ipid][$kk_flatrate]);
		            $start_fl_period_day[$v_ipid] = date('Y-m-d', strtotime('+1 day', strtotime($start_fl_period_day[$v_ipid])));
		        }
		    }
		
		    foreach($flatrate_structured[$v_ipid]['pay_days'] as $k_flatrate => $v_flatrate)
		    {
		        $max_flatrate_day[$v_ipid][$k_flatrate] = strtotime('+6 days', strtotime($v_flatrate));
		
		        $flatrate_inperiod[$v_ipid]['pay_days'][$k_flatrate] = $v_flatrate;
		        $flatrate_structured_inperiod[$v_ipid]['pay_days'][$k_flatrate] = $v_flatrate;
		
		        foreach($flatrate_structured[$v_ipid][$k_flatrate] as $k_day_key => $v_day_value)
		        {
		            if(strtotime($v_day_value) <= $max_flatrate_day[$v_ipid][$k_flatrate] && ((in_array(date('Y-m-d', strtotime('-1 day', strtotime($v_day_value))), $flatrate_inperiod[$v_ipid]) && $v_day_value != $v_flatrate) || $v_day_value == $v_flatrate))
		            {
		                $flatrate_inperiod[$v_ipid][] = $v_day_value;
		                $flatrate_structured_inperiod[$v_ipid][$k_flatrate][] = $v_day_value;
		            }
		            else
		            {
		                $flatrate_inperiod[$v_ipid][] = $v_flatrate;
		                $flatrate_structured_inperiod[$v_ipid][$k_flatrate][] = $v_flatrate;
		            }
		        }
		    }
		
		    foreach($flatrate_structured_inperiod[$v_ipid]['pay_days'] as $k_fl_per => $v_fl_per)
		    {
		        $flatrate_inperiod_temp[$v_ipid] = $flatrate_structured_inperiod[$v_ipid][$k_fl_per];
		        $flatrate_inperiod_temp[$v_ipid] = array_values(array_unique($flatrate_inperiod_temp[$v_ipid]));
		
		
		        $last_day_flatrate[$v_ipid] = end($flatrate_inperiod_temp[$v_ipid]);
		        $ts_last_day_flatrate[$v_ipid] = strtotime($last_day_flatrate[$v_ipid]);
		        $next_flatrate_day[$v_ipid] = '';
		        while(count($flatrate_inperiod_temp[$v_ipid]) < '7')
		        {
		
		            if(strlen($next_flatrate_day[$v_ipid]) == '0')
		            {
		                $next_flatrate_day[$v_ipid] = strtotime('+1 day', $ts_last_day_flatrate[$v_ipid]);
		            }
		            else
		            {
		                $next_flatrate_day[$v_ipid] = strtotime('+1 day', $next_flatrate_day[$v_ipid]);
		            }
		
		            //added check for discharge dead in hospital day -> WHERE IS THE HOSPITAL DAY???
		            if($next_flatrate_day[$v_ipid] <= strtotime($current_period['end']))
		            {
		                if(in_array(date('Y-m-d', $next_flatrate_day[$v_ipid]), $active_days_overall[$v_ipid]) && !in_array(date('Y-m-d', $next_flatrate_day[$v_ipid]), $days_nosapv_overall[$v_ipid]))
		                //							if(in_array(date('Y-m-d', $next_flatrate_day[$v_ipid]), $active_days[$v_ipid]))
		                {
		                    $flatrate_inperiod_temp[$v_ipid][] = date('Y-m-d', $next_flatrate_day[$v_ipid]);
		                }
		            }
		            else
		            {
		                $flatrate_inperiod_temp[$v_ipid][] = $last_day_flatrate[$v_ipid]; //dummy control
		            }
		        }
		
		        if(empty($final_flatrate[$v_ipid]))
		        {
		            $final_flatrate[$v_ipid] = array();
		        }
		
		        $final_flatrate[$v_ipid] = array_merge_recursive($final_flatrate[$v_ipid], $flatrate_inperiod_temp[$v_ipid]);
		    }
		
		    foreach($final_flatrate[$v_ipid] as $k_fl_day => $v_fl_day)
		    {
		        $seached_key = '';
		        if(!in_array($v_fl_day, $sapv_days_active[$v_ipid]))
		        {
		            $seached_key = array_search($v_fl_day, $final_flatrate[$v_ipid]);
		
		            if(strlen($seached_key) > '0')
		            {
		                unset($final_flatrate[$v_ipid][$seached_key]);
		                array_values($final_flatrate[$v_ipid]);
		            }
		        }
		        unset($seached_key);
		    }
		
		    //get back pay days
		    $final_flatrate[$v_ipid]['pay_days'] = $flatrate_structured_inperiod[$v_ipid]['pay_days'];
		
		    //period in which the flatrate is not billed
		    $excluded_fl_period = Pms_CommonData::exclude_bw_flatrate($v_ipid);
		
		    if($excluded_fl_period)
		    {
		        $pre_final_days[$v_ipid] = $final_flatrate[$v_ipid];
		
		        unset($pre_final_days[$v_ipid]['pay_days']);
		
		        $temp_final_fl[$v_ipid]['days'] = array_diff($pre_final_days[$v_ipid], $excluded_fl_period);
		        $temp_final_fl[$v_ipid]['pay_days'] = array_diff($final_flatrate[$v_ipid]['pay_days'], $excluded_fl_period);
		
		
		        if(count($temp_final_fl[$v_ipid]['days']) == '0' || count($temp_final_fl[$v_ipid]['pay_days']) == '0')
		        {
		            $final_flatrate[$v_ipid] = array();
		        }
		    }
		}
		
		return $final_flatrate;
		}
		
		
		private function get_period_sapvs($ipids, $active_days, $hospital_hospiz_days)
		{
		    $patientmaster = new PatientMaster();
		    if(count($hospital_hospiz_days) == 0)
		    {
		        $hospital_hospiz_days[] = '999999999999';
		    }
		
		    if(is_array($ipids))
		    {
		        $ipids_arr = $ipids;
		    }
		    else
		    {
		        $ipids_arr[] = $ipids;
		    }
		
		    $dropSapv = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    ->andWhere('isdelete=0')
		    ->andWhere('status != 1 ')
		    ->orderBy('verordnungam ASC');
		    $droparray = $dropSapv->fetchArray();
		
		    $all_sapv_days = array();
		    $temp_sapv_days = array();
		
		    foreach($droparray as $k_sapv => $v_sapv)
		    {
		        $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		        $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
		        $temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		
		        foreach($temp_sapv_days as $k_tsapv => $v_tsapv)
		        {
		
		            if(in_array($v_tsapv, $active_days[$v_sapv['ipid']]) && !in_array($v_tsapv, $hospital_hospiz_days))
		            {
		                $temp_sapv_verordnet[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);
		
		                if(empty($all_sapv_days[$v_sapv['ipid']][$v_tsapv]))
		                {
		                    $all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array();
		                }
		                $all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_merge_recursive($all_sapv_days[$v_sapv['ipid']][$v_tsapv], $temp_sapv_verordnet[$v_sapv['ipid']]);
		
		                $all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_values(array_unique($all_sapv_days[$v_sapv['ipid']][$v_tsapv]));
		            }
		        }
		    }
		
		    foreach($all_sapv_days as $k_ipid => $v_sapv_days)
		    {
		        foreach($v_sapv_days as $k_s_day => $v_s_day)
		        {
		            if(in_array($k_s_day, $active_days[$k_ipid]) && !in_array($k_s_day, $hospital_hospiz_days))
		            {
		                $all_sapv_days_arr[$k_ipid][$k_s_day] = $v_s_day;
		            }
		            //					$all_sapv_days_arr['relevant_sapvs_days'][$k_ipid] = $relevant_sapvs_days[$k_ipid];
		        }
		    }
		
		    return $all_sapv_days_arr;
		}
		
		private function nosapv_days($active_days_in_period, $hospital_days, $sapv_days)
		{
		    foreach($active_days_in_period as $k_active_days_ipid => $v_active_days)
		    {
		        foreach($v_active_days as $k_active_day => $v_active_day)
		        {
		            if(in_array($v_active_day, $hospital_days[$k_active_days_ipid]) || !array_key_exists($v_active_day, $sapv_days[$k_active_days_ipid]))
		            {
		                $real_active_days[$k_active_days_ipid][] = $v_active_day;
		            }
		        }
		    }
		
		    return $real_active_days;
		}
		
 
		private function get_patients_period_course($ipids = false)
		{
		    if($ipids)
		    { 
		        $course = Doctrine_Query::create()
		        ->select("id, ipid, course_date, wrong, done_date, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
		        ->from('PatientCourse')
		        ->where("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'U' OR AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'V'")
		        ->andWhere("wrong = 0")
		        ->andWhere('source_ipid = ""')
		        ->andWhereIn('ipid', $ipids)
		        ->orderBy('course_date ASC');
		        $course_res = $course->fetchArray();
		
		        foreach($course_res as $k_course => $v_course)
		        {
		            $course_date = date('Y-m-d H:i:s', strtotime($v_course['done_date']));
		
		            $days_course[$v_course['ipid']][$course_date][$v_course['id']] = $v_course;
		        }
		
		        return $days_course;
		    }
		}
		
		private function get_patients_period_cf($ipids, $current_period = array())
		{
		    if(is_array($ipids))
		    {
		        $ipids_arr = $ipids;
		    }
		    else
		    {
		        $ipids_arr[] = $ipids;
		    }
		    $contact_from_course = Doctrine_Query::create()
		    ->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		    ->from('PatientCourse')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
		    ->andWhere("wrong = 1")
		    ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
			->andWhere('source_ipid = ""')
		    ->orderBy('course_date ASC');
		
		    $contact_v = $contact_from_course->fetchArray();
		
		    $deleted_contact_forms[] = '9999999999999999';
		    foreach($contact_v as $k_contact_v => $v_contact_v)
		    {
		        $deleted_contact_forms[] = $v_contact_v['recordid'];
		    }
		
		    $contact_form_visits = Doctrine_Query::create()
		    ->select("*")
		    ->from("ContactForms")
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhereNotIn('id', $deleted_contact_forms)
		    //				->andWhere('DATE(date) BETWEEN DATE("' . $current_period['start'] . '") and DATE("' . $current_period['end'] . '")')
		    ->andWhere('isdelete ="0"')
		    ->andWhere('parent ="0"');
		    $contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');
		    $contact_form_visits_res = $contact_form_visits->fetchArray();
		
		    foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
		    {
		        $contact_form_visit_date = date('Y-m-d', strtotime($v_contact_visit['billable_date']));
		
		        $cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date][] = $v_contact_visit;
		
		        $cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date]['form_types'][] = $v_contact_visit['form_type'];
		        $cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date]['form_types'] = array_unique($cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date]['form_types']);
		    }
		
		    return $cf_visit_days;
		}

		private function multi_patients_flatrate_days_lag($ipids, $clientid, $current_period, $active_days_overall, $days_nosapv_overall, $hospital_hospiz_days_cs, $patients_details)
		{
		    $patientmaster = new PatientMaster();
		
		    $ipids = array_unique($ipids);
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->whereIn("ipid", $ipids)
		    ->andWhere('isdelete=0')
		    ->andWhere("status != 1")
		    ->andWhere('verordnet LIKE "%4%" OR verordnet LIKE "%3%"')
		    ->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    ->orderBy("verordnungam ASC");
		    $res = $drop->fetchArray();
		
		    //get patients discharges
		    $patients_discharge = PatientDischarge::get_patients_discharge($ipids);
		
		    //get client discharge methods
		    $discharge_methods = DischargeMethod::getDischargeMethod($clientid, 0);
		
		    //get only dead methods
		    foreach($discharge_methods as $k_dis_method => $v_dis_method)
		    {
		        if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
		        {
		            $death_methods[] = $v_dis_method['id'];
		        }
		    }
		
		    $death_methods = array_values(array_unique($death_methods));
		
		    //get discharged dead date
		    foreach($patients_discharge as $k_pat_dis => $v_pat_dis)
		    {
		        if(in_array($v_pat_dis['discharge_method'], $death_methods))
		        {
		            $discharge_dead_date[$v_pat_dis['ipid']] = date('Y-m-d', strtotime($v_pat_dis['discharge_date']));
		        }
		    }
		
		
		    foreach($res as $k_sapv => $v_sapv)
		    {
		        $s_days = $patientmaster->getDaysInBetween($v_sapv['verordnungam'], $v_sapv['verordnungbis']);
		        //				$sapv_cycle_days[$v_sapv['id']] = $s_days;
		
		        if(empty($sapv_days[$v_sapv['ipid']]))
		        {
		            $sapv_days[$v_sapv['ipid']] = array();
		        }
		
		        $sapv_days[$v_sapv['ipid']] = array_merge_recursive($s_days, $sapv_days[$v_sapv['ipid']]);
		    }
		    $sapv_days[$v_sapv['ipid']] = array_values(array_unique($sapv_days[$v_sapv['ipid']]));
		
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        $active_days_overall[$v_ipid] = array_diff($active_days_overall[$v_ipid], $hospital_hospiz_days_cs[$v_ipid]);
		        foreach($sapv_days[$v_ipid] as $k_sapv_day => $v_sapv_day)
		        {
		            $curent_sapv_day_month = date('Ym', strtotime($v_sapv_day));
		            $curent_start_month = date('Ym', strtotime($current_period['start']));
		
		            //calculate overall
		            if(count($flatrate_starts[$v_ipid]['overall_period'][$curent_sapv_day_month]) < '7' && in_array($v_sapv_day, $active_days_overall[$v_ipid]))
		            {
		                $flatrate_starts[$v_ipid]['overall_period'][$curent_sapv_day_month][] = $v_sapv_day;
		            }
		        }
		
		        $sapv_days_active[$v_ipid] = array_intersect($active_days_overall[$v_ipid], $sapv_days[$v_ipid]);
		        $sapv_days_active[$v_ipid] = array_values(array_unique($sapv_days_active[$v_ipid]));
		        asort($sapv_days[$v_ipid]);
		
		        $sapv_days[$v_ipid] = array_values($sapv_days[$v_ipid]);
		
		        $last_sapv_day[$v_ipid] = $sapv_days_active[$v_ipid][0];
		        $last_sapv_day_ts[$v_ipid] = strtotime($last_sapv_day[$v_ipid]);
		
		        if(count($sapv_days_active[$v_ipid]) > '0')
		        {
		            $last_sapv_day[$v_ipid] = $sapv_days_active[$v_ipid][0];
		            $last_sapv_day_ts[$v_ipid] = strtotime($last_sapv_day[$v_ipid]);
		
		            $currtime[$v_ipid] = $last_sapv_day_ts[$v_ipid];
		            $discharge_date = end(end($patients_details[$v_ipid]['active_periods']));
		
		            $end_time[$v_ipid] = strtotime(date('Y-m-d', strtotime($discharge_date)));
		
		
		            $i = '0';
		            $first_flatrate[$v_ipid] = true;
		
		            //while from $last_sapv_day_ts +1 day and check if exists in in $days_nosapv_overall (increment $counted_days value till 28)
		            while($currtime[$v_ipid] <= $end_time[$v_ipid])
		            {
		                $cur_time_formated[$v_ipid] = date('Y-m-d', $currtime[$v_ipid]);
		
		                if(!in_array($cur_time_formated[$v_ipid], $days_nosapv_overall[$v_ipid]) && $first_flatrate[$v_ipid] && in_array($cur_time_formated[$v_ipid], $sapv_days_active[$v_ipid]) && !in_array($cur_time_formated[$v_ipid], $hospital_hospiz_days_cs[$v_ipid]))
		                {
		                    $flatrate_structured[$v_ipid]['pay_days'][] = $cur_time_formated[$v_ipid];
		
		                    $fl_start_key[$v_ipid] = array_search($cur_time_formated[$v_ipid], $flatrate_structured[$v_ipid]['pay_days']);
		                    $flatrate_structured[$v_ipid][$fl_start_key[$v_ipid]][] = $cur_time_formated[$v_ipid];
		
		                    $first_flatrate[$v_ipid] = false;
		                    $i++;
		                }
		
		                $currtime[$v_ipid] = strtotime('+1 day', $currtime[$v_ipid]);
		            }
		        }
		
		        foreach($flatrate_structured[$v_ipid]['pay_days'] as $kk_flatrate => $vv_flatrate)
		        {
		            $start_fl_period_day[$v_ipid] = $vv_flatrate;
		            $i = 0;
		
		            while(count($flatrate_structured[$v_ipid][$kk_flatrate]) < '7')
		            {
		                if(!in_array($start_fl_period_day[$v_ipid], $hospital_hospiz_days_cs[$v_ipid]) && in_array($start_fl_period_day[$v_ipid], $sapv_days_active[$v_ipid]) && !in_array($start_fl_period_day[$v_ipid], $flatrate_structured[$v_ipid][$kk_flatrate]))
		                {
		                    $flatrate_structured[$v_ipid][$kk_flatrate][] = $start_fl_period_day[$v_ipid];
		                }
		                else
		                {
		                    //make sure we reach 7 elements
		                    $flatrate_structured[$v_ipid][$kk_flatrate][] = $vv_flatrate;
		                }
		                asort($flatrate_structured[$v_ipid][$kk_flatrate]);
		                $start_fl_period_day[$v_ipid] = date('Y-m-d', strtotime('+1 day', strtotime($start_fl_period_day[$v_ipid])));
		            }
		        }
		
		        foreach($flatrate_structured[$v_ipid]['pay_days'] as $k_flatrate => $v_flatrate)
		        {
		            $max_flatrate_day[$v_ipid][$k_flatrate] = strtotime('+6 days', strtotime($v_flatrate));
		
		            $flatrate_inperiod[$v_ipid]['pay_days'][$k_flatrate] = $v_flatrate;
		            $flatrate_structured_inperiod[$v_ipid]['pay_days'][$k_flatrate] = $v_flatrate;
		
		            foreach($flatrate_structured[$v_ipid][$k_flatrate] as $k_day_key => $v_day_value)
		            {
		                if(strtotime($v_day_value) <= $max_flatrate_day[$v_ipid][$k_flatrate] && ((in_array(date('Y-m-d', strtotime('-1 day', strtotime($v_day_value))), $flatrate_inperiod[$v_ipid]) && $v_day_value != $v_flatrate) || $v_day_value == $v_flatrate))
		                {
		                    $flatrate_inperiod[$v_ipid][] = $v_day_value;
		                    $flatrate_structured_inperiod[$v_ipid][$k_flatrate][] = $v_day_value;
		                }
		                else
		                {
		                    $flatrate_inperiod[$v_ipid][] = $v_flatrate;
		                    $flatrate_structured_inperiod[$v_ipid][$k_flatrate][] = $v_flatrate;
		                }
		            }
		        }
		
		        foreach($flatrate_structured_inperiod[$v_ipid]['pay_days'] as $k_fl_per => $v_fl_per)
		        {
		            $flatrate_inperiod_temp[$v_ipid] = $flatrate_structured_inperiod[$v_ipid][$k_fl_per];
		            $flatrate_inperiod_temp[$v_ipid] = array_values(array_unique($flatrate_inperiod_temp[$v_ipid]));
		
		
		            $last_day_flatrate[$v_ipid] = end($flatrate_inperiod_temp[$v_ipid]);
		            $ts_last_day_flatrate[$v_ipid] = strtotime($last_day_flatrate[$v_ipid]);
		            $next_flatrate_day[$v_ipid] = '';
		            while(count($flatrate_inperiod_temp[$v_ipid]) < '7')
		            {
		
		                if(strlen($next_flatrate_day[$v_ipid]) == '0')
		                {
		                    $next_flatrate_day[$v_ipid] = strtotime('+1 day', $ts_last_day_flatrate[$v_ipid]);
		                }
		                else
		                {
		                    $next_flatrate_day[$v_ipid] = strtotime('+1 day', $next_flatrate_day[$v_ipid]);
		                }
		
		                //added check for discharge dead in hospital day -> WHERE IS THE HOSPITAL DAY???
		                if($next_flatrate_day[$v_ipid] <= strtotime($current_period['end']))
		                {
		                    if(in_array(date('Y-m-d', $next_flatrate_day[$v_ipid]), $active_days_overall[$v_ipid]) && !in_array(date('Y-m-d', $next_flatrate_day[$v_ipid]), $days_nosapv_overall[$v_ipid]))
		                    //							if(in_array(date('Y-m-d', $next_flatrate_day[$v_ipid]), $active_days[$v_ipid]))
		                    {
		                        $flatrate_inperiod_temp[$v_ipid][] = date('Y-m-d', $next_flatrate_day[$v_ipid]);
		                    }
		                }
		                else
		                {
		                    $flatrate_inperiod_temp[$v_ipid][] = $last_day_flatrate[$v_ipid]; //dummy control
		                }
		            }
		
		            if(empty($final_flatrate[$v_ipid]))
		            {
		                $final_flatrate[$v_ipid] = array();
		            }
		
		            $final_flatrate[$v_ipid] = array_merge_recursive($final_flatrate[$v_ipid], $flatrate_inperiod_temp[$v_ipid]);
		        }
		
		        foreach($final_flatrate[$v_ipid] as $k_fl_day => $v_fl_day)
		        {
		            $seached_key = '';
		            if(!in_array($v_fl_day, $sapv_days_active[$v_ipid]))
		            {
		                $seached_key = array_search($v_fl_day, $final_flatrate[$v_ipid]);
		
		                if(strlen($seached_key) > '0')
		                {
		                    unset($final_flatrate[$v_ipid][$seached_key]);
		                    array_values($final_flatrate[$v_ipid]);
		                }
		            }
		            unset($seached_key);
		        }
		
		        //get back pay days
		        $final_flatrate[$v_ipid]['pay_days'] = $flatrate_structured_inperiod[$v_ipid]['pay_days'];
		    }
		    return $final_flatrate;
		}	
		
		//copy of second_patients_performance_overall
		private function patients_performance_overall($clientid, $ipids, $active_days, $set_ids, $flatrate, $flatrate_continued = false, $master_price_list, $patients_periods_days, $hospital_days = false, $sapv_days = false, $course_days = false, $contact_forms_days = false, $classification_data = false,$patient_days2locationtypes = false)
		{
		    foreach($ipids as $k_ipid => $ipid)
		    {
		        foreach($active_days[$ipid] as $k_active_day => $v_active_day)
		        {
		            if(in_array($v_active_day, $patients_periods_days[$ipid]))
		            {
		                $active_days_in_period[$ipid][] = $v_active_day;
		            }
		
		            //initialize clasifizierung array -- check if curent day empty so we wont rewrite
		            if(empty($clasifizierung[$ipid][$v_active_day]['home_visit']))
		            {
		                $clasifizierung[$ipid][$v_active_day]['home_visit'] = '0';
		            }
		
		            if(empty($clasifizierung[$ipid][$v_active_day]['beratung']))
		            {
		                $clasifizierung[$ipid][$v_active_day]['beratung'] = '0';
		            }
		
		            if(empty($clasifizierung[$ipid][$v_active_day]['koordination']))
		            {
		                $clasifizierung[$ipid][$v_active_day]['koordination'] = '0';
		            }
		        }
		
		        foreach($course_days[$ipid] as $k_course_date => $v_course_details)
		        {
		            if(!in_array($k_course_date, $hospital_days[$ipid]))
		            {
		                foreach($v_course_details as $k_couse => $v_course)
		                {
		                    $course[$ipid][$k_course_date][] = $v_course['course_type'];
		                    
		                    
		                    if($v_course['course_type'] == 'U' && array_key_exists($k_course_date, $sapv_days[$ipid]) && in_array('1', $sapv_days[$ipid][$k_course_date]))
		                    {
		                        $clasifizierung[$ipid][$k_course_date]['beratung'] += 1;
		                    }
		                    else if($v_course['course_type'] == 'V' && array_key_exists($k_course_date, $sapv_days[$ipid]) && in_array('2', $sapv_days[$ipid][$k_course_date]))
		                    {
		                        $clasifizierung[$ipid][$k_course_date]['koordination'] += 1;
		                    }
		                }
		            }
		        }
		
		        $contact_forms_ids[$ipid][] = '999999999';
		        foreach($contact_forms_days[$ipid] as $k_cf_day => $v_cf_data)
		        {
		            foreach($v_cf_data as $k_cf => $v_cf)
		            {
		                if(is_numeric($k_cf))
		                {
		                    $contact_forms_ids[$ipid][] = $v_cf['id'];
		                }
		            }
		        }
		        ksort($contact_forms_days[$ipid]);
		
		        $set_one_ids = $set_ids['one'];
		        $set_two_ids = $set_ids['two'];
		        $set_three_ids = $set_ids['three'];
		        $set_fourth_ids = $set_ids['fourth'];
		
		        foreach($contact_forms_days[$ipid] as $k_cf_day => $v_cf_data)
		        {
		            foreach($v_cf_data as $k_cf => $v_cf)
		            {
		                if(is_numeric($k_cf) && array_key_exists($v_cf['id'], $classification_data) && in_array($k_cf_day, $active_days_in_period[$ipid]))
		                {
		                    if((in_array($v_cf['form_type'], $set_ids['fourth']) && $classification_data[$v_cf['id']]['intern'] == '0') || !in_array($v_cf['form_type'], $set_ids['fourth']))
		                    {
		                        $contact_forms_days_ids[$ipid][$k_cf_day][] = $v_cf['id'];
		                        $contact_forms2form_types[$ipid][$v_cf['id']][] = $v_cf['form_type'];
		                    }
		                }
		            }
		        }
		
		        if(empty($hospital_days[$ipid]))
		        {
		            $hospital_days[$ipid][] = "999999999999999999999999";
		        }
		        $patients_periods_days_no_h[$ipid] = array_diff($patients_periods_days[$ipid], $hospital_days[$ipid]);
		        $patients_periods_days_no_h[$ipid] = array_values(array_unique($patients_periods_days_no_h[$ipid]));
		
		        
		        $day_location_type= array();
		        foreach($patients_periods_days_no_h[$ipid] as $k_period_day => $v_period_day)
		        {
		            $day_shortcuts[$ipid] = array();
		            $day_location_type = $patient_days2locationtypes[$ipid][date('d.m.Y',strtotime($v_period_day))];
		            if(count($month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) == 0)
		            {
		                $month_shortcuts[$ipid] = array();
		            }
		
		            $master_data[$ipid][$v_period_day] = array();
		
		            //calculate set one form_type visits
		            //first get flatrate exception and set shortcut
		
		            $shortcut = '';
		            $fl_continued = false;//curent day NOT in flatrate continued
		            if($flatrate_continued && in_array($v_period_day, $flatrate_continued[$ipid]))
		            {
		                $fl_continued = true; //curent day is in flatrate continued
		            }
		            	
		            if((in_array($v_period_day, $flatrate[$ipid]) || $fl_continued === true) && !in_array($v_period_day, $hospital_days[$ipid]))
		            {
		                $shortcut = '37b1';
		                $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = $shortcut;
		            }
		            else if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued === false) && array_key_exists($v_period_day, $contact_forms_days[$ipid]) && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid])) //normal set conditions
		            {
		
		                //					we have visits=> check if there are required visits for this action set
		                $set_one_result[$ipid] = array_intersect($set_one_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		
		                if(count($set_one_result[$ipid]) != 0 && (in_array('3', $sapv_days[$ipid][$v_period_day]) || in_array('4', $sapv_days[$ipid][$v_period_day])))
		                {
		                    $shortcut = '37b2';
		                }
		            }
		
		            if(!empty($shortcut))
		            {
		                $day_shortcuts[$ipid][] = $shortcut;
		                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'] = $shortcut;
		                $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		            }
		
		            //sapv overwrite if formtype is set 4
		            //calculate set two form_type visits
		            //exclude calculation if following shortcuts are calculated on current day
		
		            if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued === false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid]))
		            {
		                $set_ones_result[$ipid] = array_intersect($set_one_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		                $set_two_result[$ipid] = array_intersect($set_two_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		
// 		                if((count($set_two_result[$ipid]) != 0 || in_array('U', $course_days[$ipid][$v_period_day])) && in_array('1', $sapv_days[$ipid][$v_period_day]) || (count($set_ones_result[$ipid]) != 0 && in_array('1', $sapv_days[$ipid][$v_period_day]) )
		                if((count($set_two_result[$ipid]) != 0 || in_array('U', $course[$ipid][$v_period_day])) && in_array('1', $sapv_days[$ipid][$v_period_day]) || (count($set_ones_result[$ipid]) != 0 && in_array('1', $sapv_days[$ipid][$v_period_day]) )
		                )
		                {
		                    foreach($contact_forms_days[$ipid][$v_period_day] as $k_cf => $v_cf)
		                    {
		                        if(is_numeric($k_cf))
		                        {
		                            $shortcut = '';
		                            if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && empty($flatrate[$ipid]['pay_days']) && empty($flatrate[$ipid]['pay_days']) && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && (!in_array($v_period_day, $flatrate[$ipid]['pay_days']) && $fl_continued == false))
		                            {
		                                $shortcut = '37b5';
		                            }
		
		                            if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b5', $day_shortcuts[$ipid]) && $shortcut != '37b5')
		                            {
		                                $shortcut = '37b6';
		                            }
		
		                            if(!empty($shortcut))
		                            {
		                                if($shortcut == '37b5')
		                                {
		                                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b5';
		                                }
		
		                                $day_shortcuts[$ipid][] = $shortcut;
		                            }
		
		
		                            if(!empty($shortcut))
		                            {
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                if($shortcut == '37b6' && $master_data[$ipid][$v_period_day]['37b6']['qty'] < '2' && in_array($v_cf['form_type'], $set_two_ids))
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                }
		                                else if($shortcut == '37b5')
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                }
		
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                            }
		                        }
		                    }
		
		                    foreach($course_days[$ipid][$v_period_day] as $k_day_course => $v_day_course)
		                    {
		                        $shortcut = '';
		
		                        if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && empty($flatrate[$ipid]['pay_days']) && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && (!in_array($v_period_day, $flatrate[$ipid]['pay_days'])  && $fl_continued == false))
		                        {
		                            $shortcut = '37b5';
		                        }
		
		                        if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b5', $day_shortcuts[$ipid]) && $shortcut != '37b5')
		                        {
		                            $shortcut = '37b6';
		                        }
		
		                        if(!empty($shortcut))
		                        {
		                            if($shortcut == '37b5')
		                            {
		                                $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b5';
		                            }
		
		                            $day_shortcuts[$ipid][] = $shortcut;
		                        }
		
		                        if(($shortcut == '37b6' || $shortcut == '37b5') && $master_data[$ipid][$v_period_day]['37b6']['qty'] < '2' && $v_day_course['course_type'] == 'U')
		                        {
		                            $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                            $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                            $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                            $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                            $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                            $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$shortcut]['price'];
		                            $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                        }
		                    }
		                }
		            }
		
		            //calculate set three form_type visits
		            //exclude calculation if following shortcuts are calculated on current day
		
		            if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued == false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid]))
		            {
		                $last_koord_day[$ipid] = end($last_koord_dates[$ipid]);
		                $set_three_result[$ipid] = array_intersect($set_three_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		                $set_ones_result[$ipid] = array_intersect($set_one_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		
		                if(
// 		                    (count($set_three_result[$ipid]) != 0 || in_array('V', $course_days[$ipid][$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day]) || (count($set_ones_result[$ipid]) != 0 || in_array('V', $course_days[$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day])
		                    (count($set_three_result[$ipid]) != 0 || in_array('V', $course[$ipid][$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day]) || (count($set_ones_result[$ipid]) != 0 || in_array('V', $course[$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day])
		                )
		                {
		
		                    foreach($contact_forms_days[$ipid][$v_period_day] as $k_cform => $v_cform)
		                    {
		                        if(is_numeric($k_cform))
		                        {
		                            $shortcut = '';
		
		                            if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && (!in_array($v_period_day, $flatrate[$ipid]['pay_days']) && $fl_continued == false))
		                            {
		                                if(in_array('4', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $shortcut = '37b8';
		                                    $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                }
		                                else if(empty($flatrate[$ipid]['pay_days']))
		                                {
		                                    $shortcut = '37b7';
		                                }
		                            }
		
		                            if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b7', $day_shortcuts[$ipid]) && $shortcut != '37b7')
		                            {
		                                $shortcut = '37b8';
		                                $week_number[$ipid] = date('W', strtotime($v_period_day));
		                            }
		
		                            if(!empty($shortcut))
		                            {
		                                if($shortcut == '37b7')
		                                {
		                                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b7';
		                                }
		
		                                $day_shortcuts[$ipid][] = $shortcut;
		                            }
		
		
		                            if(!empty($shortcut))
		                            {
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                if($shortcut == '37b8' && !in_array($week_number[$ipid], $week_numbers_arr[$ipid]) && in_array($v_cform['form_type'], $set_three_ids[$ipid]))
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                    $week_numbers_arr[$ipid][] = $week_number[$ipid];
		                                }
		                                else if($shortcut == '37b7')
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                }
		
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $last_koord_dates[$ipid][] = $v_period_day;
		                            }
		                        }
		                    }
		
		                    foreach($course_days[$ipid][$v_period_day] as $k_day_course => $v_day_course)
		                    {
		                        $shortcut = '';
		                        $last_koord_day[$ipid] = end($last_koord_dates[$ipid]);
		
		                        if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]))
		                        {
		                            if(in_array('4', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $shortcut = '37b8';
		                                $week_number[$ipid] = date('W', strtotime($v_period_day));
		                            }
		                            else if(empty($flatrate[$ipid]['pay_days']))
		                            {
		                                $shortcut = '37b7';
		                            }
		                        }
		
		                        if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b7', $day_shortcuts[$ipid]) && $shortcut != '37b7')
		                        {
		                            $shortcut = '37b8';
		                            $week_number[$ipid] = date('W', strtotime($v_period_day));
		                        }
		
		                        if(!empty($shortcut))
		                        {
		                            if($shortcut == '37b7')
		                            {
		                                $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b7';
		                            }
		
		                            $day_shortcuts[$ipid][] = $shortcut;
		                        }
		
		                        if(($shortcut == '37b7' || $shortcut == '37b8') && $v_day_course['course_type'] == 'V')
		                        {
		                            if($shortcut == '37b8' && !in_array($week_number[$ipid], $week_numbers_arr[$ipid]))
		                            {
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $week_numbers_arr[$ipid][] = $week_number[$ipid];
		                                $last_koord_dates[$ipid][] = $v_period_day;
		                            }
		                            else if($shortcut == '37b7')
		                            {
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $last_koord_dates[$ipid][] = $v_period_day;
		                            }
		                        }
		                    }
		                }
		            }
		
		
		            //calculate set 4 (ISPC-241)
		            $set_fourth_result[$ipid] = array_intersect($set_fourth_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		
		            if(count($set_fourth_result[$ipid]) != 0 && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid]))
		            {
		                //setup each form sapv based on classification block selection
		                foreach($contact_forms_days_ids[$ipid][$v_period_day] as $k_cf_day => $v_cf_day)
		                {
		                    if($classification_data[$v_cf_day]['intern'] != '1')
		                    {
		                        if($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '0')
		                        {
		                            $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '1';
		                        }
		                        else if($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1')
		                        {
		                            //switch between BE-KO if VV is not BE
		                            if(!in_array('1', $sapv_days[$ipid][$v_period_day]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '2';
		                            }
		                            else if(in_array('1', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '1';
		                            }
		                        }
		                        else if($classification_data[$v_cf_day]['beratung'] == '0' && $classification_data[$v_cf_day]['koordination'] == '1')
		                        {
		                            $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '2';
		                        }
		                    }
		                }
		
		                //U & V at the top
		                //beratung
		                if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued == false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array('1', $sapv_days[$ipid][$v_period_day]))
		                {
		                    foreach($contact_forms_days[$ipid][$v_period_day] as $k_cf => $v_cf)
		                    {
		                        //check if there are 2 beratung change sapv in koord
		                        if($master_data[$ipid][$v_period_day]['37b6']['qty'] == '2' && count($set_fourth_result[$ipid]) != 0 && $classification_data[$v_cf['id']]['beratung'] == '1' && $classification_data[$v_cf['id']]['koordination'] == '1')
		                        {
		                            $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf['id']]['contact_form_id']] = '2';
		                        }
		
		                        if(is_numeric($k_cf) && $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf['id']]['contact_form_id']] == '1')
		                        {
		                            $shortcut = '';
		                            if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && empty($flatrate[$ipid]['pay_days']) && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && (!in_array($v_period_day, $flatrate[$ipid]['pay_days']) && $fl_continued == false))
		                            {
		                                $shortcut = '37b5';
		                            }
		
		                            if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b5', $day_shortcuts[$ipid]) && $shortcut != '37b5') //only b5 or b6
		                            {
		                                $shortcut = '37b6';
		                            }
		
		                            if(!empty($shortcut))
		                            {
		                                if($shortcut == '37b5')
		                                {
		                                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b5';
		                                }
		
		                                $day_shortcuts[$ipid][] = $shortcut;
		                            }
		
		
		                            if(!empty($shortcut))
		                            {
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                if($shortcut == '37b6' && $master_data[$v_period_day]['37b6']['qty'] < '2' && in_array($v_cf['form_type'], $set_fourth_ids))
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                }
		                                else if($shortcut == '37b5')
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                }
		
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type;
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                            }
		                        }
		                    }
		                }
		
		                //koordination
		                $last_koord_days[$ipid] = end($last_koord_dates[$ipid]);
		                if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued == false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                {
		
		                    foreach($contact_forms_days[$ipid][$v_period_day] as $k_cform => $v_cform)
		                    {
		                        if(is_numeric($k_cform) && $sapv_status[$ipid][$v_period_day][$classification_data[$v_cform['id']]['contact_form_id']] == '2')
		                        {
		                            $shortcut = '';
		
		                            if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]))
		                            {
		                                if(in_array('4', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $shortcut = '37b8';
		                                    $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                }
		                                else if(empty($flatrate[$ipid]['pay_days']))
		                                {
		                                    $shortcut = '37b7';
		                                }
		                            }
		                            	
		                            	
		                            if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b7', $day_shortcuts[$ipid]) && $shortcut != '37b7')
		                            {
		                                $shortcut = '37b8';
		                                $week_number[$ipid] = date('W', strtotime($v_period_day));
		                            }
		
		                            if(!empty($shortcut))
		                            {
		                                if($shortcut == '37b7')
		                                {
		                                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b7';
		                                }
		                                $day_shortcuts[$ipid][] = $shortcut;
		                            }
		
		                            if(!empty($shortcut))
		                            {
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		
		                                if($shortcut == '37b8' && !in_array($week_number[$ipid], $week_numbers_arr[$ipid]) && in_array($v_cform['form_type'], $set_fourth_ids))
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                    $week_numbers_arr[$ipid][] = $week_number[$ipid];
		                                }
		                                else if($shortcut == '37b7')
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                }
		
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type;
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['price_list'] = $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['list'];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type][$shortcut]['price'];
		                                $last_koord_dates[$ipid][] = $v_period_day;
		                            }
		                        }
		                    }
		                }
		            }
		
		            if(array_key_exists($v_period_day, $sapv_days[$ipid]))
		            {
		                foreach($contact_forms_days_ids[$ipid][$v_period_day] as $k_cf_day => $v_cf_day)
		                {
		                    //check if contactform type for each days
		                    if(in_array($contact_forms2form_types[$ipid][$v_cf_day][0], $set_one_ids))
		                    {
		                        if(in_array('4', $sapv_days[$ipid][$v_period_day]) || in_array('3', $sapv_days[$ipid][$v_period_day]))
		                        {
		                            $clasifizierung[$ipid][$v_period_day]['home_visit'] += 1;
		                        }
		                    }
		                    else
		                    {
		                        //sum classifizierung data if form is not housebesuche type(1)
		                        if($clasifizierung[$ipid][$v_period_day]['beratung'] < '2' && in_array('1', $sapv_days[$ipid][$v_period_day]) &&
		                            (($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '0') || ($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1')))
		                        {
		                            $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                        }
		                        else if($clasifizierung[$ipid][$v_period_day]['beratung'] >= '2' && $classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1' && (in_array('1', $sapv_days[$ipid][$v_period_day]) || in_array('2', $sapv_days[$ipid][$v_period_day])))
		                        {
		                            if(!in_array('1', $sapv_days[$ipid][$v_period_day]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                            }
		                            else if(in_array('1', $sapv_days[$ipid][$v_period_day]) && !in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                            }
		                            else
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                            }
		                        }
		                        else if($clasifizierung[$ipid][$v_period_day]['beratung'] < '2' && $classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1' && (in_array('1', $sapv_days[$ipid][$v_period_day]) || in_array('2', $sapv_days[$ipid][$v_period_day])))
		                        {
		                            if(!in_array('1', $sapv_days[$ipid][$v_period_day]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                            }
		                            else if(in_array('1', $sapv_days[$ipid][$v_period_day]) && !in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                            }
		                            else
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                            }
		                        }
		                        else if($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '0' && in_array('1', $sapv_days[$ipid][$v_period_day]))
		                        {
		                            $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                        }
		                        else if(($classification_data[$v_cf_day]['beratung'] == '0' && $classification_data[$v_cf_day]['koordination'] == '1') && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                        {
		                            $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                        }
		                        else if(($classification_data[$v_cf_day]['beratung'] == '0' && $classification_data[$v_cf_day]['koordination'] == '0' && $classification_data[$v_cf_day]['koordination'] == '0') && in_array($contact_forms2form_types[$ipid][$v_cf_day][0], $set_one_ids))
		                        {
		                            if(in_array('1', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                            }
		                            else if(in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                            }
		                        }
		                    }
		                }
		            }
		        }
		
		        foreach($master_data[$ipid] as $k_day => $v_day_values)
		        {
		            foreach($v_day_values as $k_shortcut => $v_shortcut_values)
		            {
		                $overall_shortcuts[$ipid][] = $k_shortcut;
		                if($v_shortcut_values['qty'] > '0' && !empty($v_shortcut_values['qty']))
		                {
		                    $overall_shortcuts_dates[$ipid][$k_day][] = $k_shortcut;
		                }
		            }
		
		            if(count($v_day_values) > '0')
		            {
		                $days_products[$ipid][] = $k_day;
		            }
		        }
		
		        //get days in which sapv is 3 or 4
		        foreach($sapv_days[$ipid] as $ksapv_days => $vsapv_days)
		        {
		            if(in_array('3', $vsapv_days) || in_array('4', $vsapv_days))
		            {
		                $flatrate_sapv_days[$ipid][] = $ksapv_days;
		            }
		        }
		
		        //return only the products which have more than 28 days from last billable product
		        $pm = new PatientMaster();
		
		        foreach($days_products[$ipid] as $key_day => $v_day)
		        {
		            if($key_day > '0')
		            {
		                $prod_gap[$ipid][$v_day] = $pm->getDaysInBetween($days_products[$ipid][($key_day - 1)], $v_day);
		                //						$prod_gap[$ipid][$v_day] = array_intersect($prod_gap[$ipid][$v_day], $active_days[$ipid], $flatrate_sapv_days[$ipid]);
		                //						$prod_gap[$ipid][$v_day] = array_intersect($prod_gap[$ipid][$v_day], $active_days[$ipid]);
		
		                $prod_gap[$ipid][$v_day] = array_values(array_unique($prod_gap[$ipid][$v_day]));
		
		                if(count($prod_gap[$ipid][$v_day]) >= '30')
		                {
		                    $last_product_gap_days[$ipid][] = $v_day;
		                    $master_overall_data[$ipid]['shortcuts'][$v_day] = '37b1';
		                }
		            }
		        }
		
		            //calculate ministry calculated/non-calculated visits
		            foreach($clasifizierung[$ipid] as $k_s_day => $v_type_visits)
		            {
		                foreach($v_type_visits as $k_visit_type => $v_visit_ammount)
		                {
		                    if($v_visit_ammount > '0')
		                    {
		                        if(in_array('37b1', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                        {
		                            $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                            $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		
		                        if($k_visit_type == "home_visit")
		                        {
		                            //one per day only (calculated can be max 1 visit)
		                            if(in_array('37b2', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "1";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - 1);
		                            }
		                            else
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		                            }
		
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		
		                        if($k_visit_type == "beratung")
		                        {
		                            //one per day and one time only
		                            if(in_array('37b5', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "1";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - 1);
		                            }
		                            //can be max 2 per day
		                            else if(in_array('37b6', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                if($v_visit_ammount >= '2')
		                                {
		                                    $v_visit_ammount_limit = "2";
		                                }
		                                else
		                                {
		                                    $v_visit_ammount_limit = "1";
		                                }
		
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = $v_visit_ammount_limit;
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - $v_visit_ammount_limit);
		                            }
		                            else
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		                            }
		
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		
		                        if($k_visit_type == "koordination")
		                        {
		                            //b7 one per day and one time only
		                            //b8 one per day and one per week
		                            if(in_array('37b7', $overall_shortcuts_dates[$ipid][$k_s_day]) || in_array('37b8', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "1";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - 1);
		                            }
		                            else
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		                            }
		
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		                    }
		                    else
		                    {
		                        $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                        $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = "0";
		
		                        $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                        $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                    }
		                }
		            }
		
		            //sum all totals arrays
		            foreach($all_visits_status[$ipid] as $total_visit_type => $v_totals)
		            {
		                $all_calculated_visits[$ipid][$total_visit_type]['calculated'] = array_sum($v_totals['calculated']);
		                $all_calculated_visits[$ipid][$total_visit_type]['noncalculated'] = array_sum($v_totals['noncalculated']);
		            }
		
		            $master_overall_data[$ipid]['shortcuts'] = array_values(array_unique($overall_shortcuts[$ipid]));
		            $master_overall_data[$ipid]['shortcuts_dates'] = $overall_shortcuts_dates[$ipid];
		            $master_overall_data[$ipid]['last_koord_dates'] = array_values(array_unique($last_koord_dates[$ipid]));
		            $master_overall_data[$ipid]['days_products'] = array_values(array_unique($last_product_gap_days[$ipid]));
		            $master_overall_data[$ipid]['clasifizierung'] = $clasifizierung[$ipid];
		            //				$master_overall_data[$ipid]['visit_status'] = $visits_status[$ipid];
		            //				$master_overall_data[$ipid]['all_visit_status'] = $all_visits_status[$ipid];
		            $master_overall_data[$ipid]['all_visit_types_totals'] = $all_calculated_visits[$ipid];
		            }
		
		            
		            if($_REQUEST['dbg_vis'] == "y" && $flatrate_continued)
		            {
		                print_r($master_data);
		                exit;
		            }
		            if($_REQUEST['dbg_vis'] == "x" && $flatrate_continued)
		            {
		                print_r($overall_shortcuts_dates);
		                exit;
		            }
		
		            if($_REQUEST['dbg_vis'] == "3" && $flatrate_continued)
		            {
		                print_r($all_visits_status);
		                exit;
		            }
		            if($_REQUEST['dbg_vis'] == "2" && $flatrate_continued)
		            {
		                print_r($clasifizierung);
		                exit;
		            }
		            if($_REQUEST['dbg_vis'] == "1" && $flatrate_continued)
		            {
		                var_dump($visits_status);
		                exit;
		            }
		            //			print_r($master_overall_data);
		            //			exit;
		            return $master_overall_data;
		    }
			
		
		
		    //copy of second_patients_performance_overall
		    private function patients_performance_overall_lag_bw_sapv($clientid, $ipids, $active_days, $set_ids, $flatrate, $flatrate_continued = false, $master_price_list, $patients_periods_days, $hospital_days = false, $sapv_days = false, $course_days = false, $contact_forms_days = false, $classification_data = false,$patient_discharge_dates = false,$exclude_after_discharge_overall=false, $patient_days2locationtypes = false)
		    {
		        
		        if($_REQUEST['xdg']){
		            echo "<pre/>";
		            print_r($patient_days2locationtypes);
		            
		        }
		        
		        $course = array();
		        $ipids = array_unique($ipids);
		        foreach($ipids as $k_ipid => $ipid)
		        {
		            $lag_clasifizierung[$ipid] = array();
		             
		            foreach($sapv_days[$ipid] as $k_s_day => $v_s_day)
		            {
		                if(in_array($k_s_day, $patients_periods_days[$ipid]))
		                {
		                    $sapv_days_in_period[$ipid][] = $k_s_day;
		                }
		            }
		             
		            foreach($active_days[$ipid] as $k_active_day => $v_active_day)
		            {
		                if(in_array($v_active_day, $patients_periods_days[$ipid]))
		                {
		                    $active_days_in_period[$ipid][] = $v_active_day;
		                }
		    
		                //initialize clasifizierung array -- check if curent day empty so we wont rewrite
		                if(empty($clasifizierung[$ipid][$v_active_day]['home_visit']))
		                {
		                    $clasifizierung[$ipid][$v_active_day]['home_visit'] = '0';
		                }
		    
		                if(empty($clasifizierung[$ipid][$v_active_day]['beratung']))
		                {
		                    $clasifizierung[$ipid][$v_active_day]['beratung'] = '0';
		                }
		    
		                if(empty($clasifizierung[$ipid][$v_active_day]['koordination']))
		                {
		                    $clasifizierung[$ipid][$v_active_day]['koordination'] = '0';
		                }
		            }
		    
		            foreach($course_days[$ipid] as $k_course_date => $v_course_details)
		            {
		                if(!in_array($k_course_date, $hospital_days[$ipid]))
		                {
		                    
		                    foreach($v_course_details as $k_couse => $v_course)
		                    {
		                        $course[$ipid][$k_course_date][] = $v_course['course_type'];
		                        
		                        
		                        if($v_course['course_type'] == 'U' && array_key_exists($k_course_date, $sapv_days[$ipid]) && in_array('1', $sapv_days[$ipid][$k_course_date]))
		                        {
		                            $clasifizierung[$ipid][$k_course_date]['beratung'] += 1;
		                            $lag_clasifizierung[$ipid]['all']['beratung'][] = $k_course_date;
		                        }
		                        else if($v_course['course_type'] == 'V' && array_key_exists($k_course_date, $sapv_days[$ipid]) && in_array('2', $sapv_days[$ipid][$k_course_date]))
		                        {
		                            $clasifizierung[$ipid][$k_course_date]['koordination'] += 1;
		                            $lag_clasifizierung[$ipid]['all']['koordination'][] = $k_course_date;
		                        }
		                    }
		                }
		            }
		            
		            
		            $contact_forms_ids[$ipid][] = '999999999';
		            foreach($contact_forms_days[$ipid] as $k_cf_day => $v_cf_data)
		            {
		                foreach($v_cf_data as $k_cf => $v_cf)
		                {
		                    if(is_numeric($k_cf))
		                    {
		                        $contact_forms_ids[$ipid][] = $v_cf['id'];
		                    }
		                }
		            }
		            ksort($contact_forms_days[$ipid]);
		    
		            $set_one_ids = $set_ids['one'];
		            $set_two_ids = $set_ids['two'];
		            $set_three_ids = $set_ids['three'];
		            $set_fourth_ids = $set_ids['fourth'];
		    
		            foreach($contact_forms_days[$ipid] as $k_cf_day => $v_cf_data)
		            {
		                foreach($v_cf_data as $k_cf => $v_cf)
		                {
		                    if(is_numeric($k_cf) && array_key_exists($v_cf['id'], $classification_data) && in_array($k_cf_day, $active_days_in_period[$ipid])
		    
		                    )
		                    {
		                        if((in_array($v_cf['form_type'], $set_ids['fourth']) && $classification_data[$v_cf['id']]['intern'] == '0') || !in_array($v_cf['form_type'], $set_ids['fourth']))
		                        {
		                            $contact_forms_days_ids[$ipid][$k_cf_day][] = $v_cf['id'];
		                            $contact_forms2form_types[$ipid][$v_cf['id']][] = $v_cf['form_type'];
		                        }
		                    }
		                }
		            }
		    
		            if(empty($hospital_days[$ipid]))
		            {
		                $hospital_days[$ipid][] = "999999999999999999999999";
		            }
		            $patients_periods_days_no_h[$ipid] = array_diff($patients_periods_days[$ipid], $hospital_days[$ipid]);
		            $patients_periods_days_no_h[$ipid] = array_values(array_unique($patients_periods_days_no_h[$ipid]));
		    
		    
		            $used_cnt_forms[$ipid] = array();
		            $used_pc_data[$ipid] = array();
		            $pseudo_overall_shortcuts[$ipid] = array();
		    
		            $day_location_type = array();
		            foreach($patients_periods_days_no_h[$ipid] as $k_period_day => $v_period_day)
		            {
		                $day_shortcuts[$ipid] = array();
		                $day_location_type[$ipid.$v_period_day] =  $patient_days2locationtypes[$ipid][date('d.m.Y',strtotime($v_period_day))];
		       
		                
		                if(count($month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))]) == 0)
		                {
		                    $month_shortcuts[$ipid] = array();
		                }
		    
		                	
		                //$hospital_days_cs format is d.m.Y
		                $v_period_day_alt = date('d.m.Y', strtotime($v_period_day));
		                	
		                $master_data[$ipid][$v_period_day] = array();
		                //calculate set one form_type visits
		                //first get flatrate exception and set shortcut
		    
		                $shortcut = '';
		                $fl_continued = false;//curent day NOT in flatrate continued
		                if($flatrate_continued && in_array($v_period_day, $flatrate_continued[$ipid]))
		                {
		                    $fl_continued = true; //curent day is in flatrate continued
		                }
		                	
		                if((in_array($v_period_day, $flatrate[$ipid]) || $fl_continued === true) && !in_array($v_period_day, $hospital_days[$ipid]))
		                {
		                    $shortcut = '37b1';
		                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = $shortcut;
		                    $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                    $extra_data[$ipid][$v_period_day][$shortcut]['day'] = date('Ymd', strtotime($v_period_day)); 
		                    $extra_data[$ipid][$v_period_day][$shortcut]['start_time'] = "0000"; 
		                    $extra_data[$ipid][$v_period_day][$shortcut]['end_time'] = "2359";
		                    $extra_data[$ipid][$v_period_day][$shortcut]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                    $extra_data[$ipid][$v_period_day][$shortcut]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                    $extra_data[$ipid][$v_period_day][$shortcut]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                    $extra_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                    $extra_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                    
		    
		                }
		    
		                foreach($contact_forms_days[$ipid][$v_period_day] as $k_cf => $v_cf_one)
		                {
		                    if(!in_array($v_cf_one['id'], $exclude_after_discharge_overall) && in_array($v_cf_one['form_type'], $set_one_ids))
		                    {
		                        if(
		                            (!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued === false)
		                            && array_key_exists($v_period_day, $contact_forms_days[$ipid])
		                            && in_array($v_period_day, $active_days_in_period[$ipid])
		                            && !in_array($v_period_day, $hospital_days[$ipid]))
		                        {
		                            //						we have visits=> check if there are required visits for this action set
		                            if((in_array('4', $sapv_days[$ipid][$v_period_day]) || in_array('3', $sapv_days[$ipid][$v_period_day]) ) && !in_array($v_cf_one['id'],$used_cnt_forms[$ipid])  )
		                            {
		                                $shortcut = '37b2';
		                                $used_cnt_forms[$ipid][] = $v_cf_one['id'];
		                                $set_one_result[$ipid] = array_intersect($set_one_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		                                
		                                if(empty($extra_data[$ipid][$v_period_day][$shortcut])){ 
//     		                                $extra_data[$ipid][$v_period_day][$shortcut] = $contact_forms_days[$ipid][$v_period_day][$k_cf]; // only one per day
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['date']));
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['start_date']));
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['end_date']));
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
    		                                $extra_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                }
		                                
		                            }
		                        }
		                    }
		                }
		                	
		                	
		                if(!empty($shortcut))
		                {
		                    $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		    
		                    	
		                    $day_shortcuts[$ipid][] = $shortcut;
		                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut'] = $shortcut;
		                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                    $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                    $master_data[$ipid][$v_period_day][$shortcut]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                    $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                    $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                    
		                }
		    
		                //sapv overwrite if formtype is set 4
		                //calculate set two form_type visits
		                //exclude calculation if following shortcuts are calculated on current day
		    
		                if( (!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued === false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid]))
		                {
		                    //	 we have visits=> check if there are required visits for this action set
		                    $set_onez_result[$ipid] = array_intersect($set_one_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		                    $set_two_result[$ipid] = array_intersect($set_two_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		    
// 		                    if(((count($set_two_result[$ipid]) != 0 || in_array('U', $course_days[$ipid][$v_period_day])) && in_array('1', $sapv_days[$ipid][$v_period_day])) || (count($set_onez_result[$ipid]) != 0 && in_array('1', $sapv_days[$ipid][$v_period_day]) ))
		                    if(((count($set_two_result[$ipid]) != 0 || in_array('U', $course[$ipid][$v_period_day])) && in_array('1', $sapv_days[$ipid][$v_period_day])) || (count($set_onez_result[$ipid]) != 0 && in_array('1', $sapv_days[$ipid][$v_period_day]) ))
		                    {
		                        foreach($contact_forms_days[$ipid][$v_period_day] as $k_cf => $v_cf)
		                        {
		                            if(is_numeric($k_cf) &&  !in_array($v_cf['id'],$exclude_after_discharge_overall))
		                            {
		    
		                                $shortcut = '';
		                                if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                // 									    && empty($flatrate[$ipid]['pay_days'])
		                                // 									    && empty($flatrate[$ipid]['pay_days'])
		                                    && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b5', $pseudo_overall_shortcuts[$ipid])
		                                    && (!in_array($v_period_day, $flatrate[$ipid]['pay_days']) && $fl_continued == false))
		                                {
		                                    if(empty($flatrate[$ipid]['pay_days']) || (!empty($flatrate[$ipid]['pay_days']) && strtotime($v_period_day) < strtotime($flatrate[$ipid][0])) ) {
		                                        $shortcut = '37b5';
		                                    }
		                                }
		    
		                                if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b5', $day_shortcuts[$ipid]) && $shortcut != '37b5')
		                                {
		                                    $shortcut = '37b6';
		                                }
		    
		                                if(!empty($shortcut))
		                                {
		                                    if($shortcut == '37b5')
		                                    {
		                                        $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b5';
		                                        $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                                    }
		    
		                                    $day_shortcuts[$ipid][] = $shortcut;
		                                }
		    
		    
		                                if(!empty($shortcut))
		                                {
		                                    	
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		    
		                                    if($shortcut == '37b6' && $master_data[$ipid][$v_period_day]['37b6']['qty'] < '2' && (in_array($v_cf['form_type'], $set_two_ids) || in_array($v_cf['form_type'], $set_one_ids) ) && !in_array($v_cf['id'],$used_cnt_forms[$ipid]))
		                                    {
		                                        $used_cnt_forms[$ipid][] = $v_cf['id'];
		                                        $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                        $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        
		                                        if(count($extra_data[$ipid][$v_period_day][$shortcut]) < 2 ){ // only 2 per day allowed
		                                            
//     		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf] = $contact_forms_days[$ipid][$v_period_day][$k_cf];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['date']));
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['start_date']));
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['end_date']));
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
    		                                        //TODO-4013 Ancuta 14.06.2021
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['location_type'] = $day_location_type[$ipid.$v_period_day];
    		                                        //$extra_data[$ipid][$v_period_day][$shortcut]['location_type'] = $day_location_type[$ipid.$v_period_day];
    		                                        //-- 
    		                                        
		                                        }
		                                    }
		                                    else if($shortcut == '37b5' && !in_array($v_cf['id'],$used_cnt_forms[$ipid]))
		                                    {
		                                        $used_cnt_forms[$ipid][] = $v_cf['id'];
		                                        $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                        $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        
		                                        if(empty($extra_data[$ipid][$v_period_day][$shortcut])){ // only one per day
//     		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf] = $contact_forms_days[$ipid][$v_period_day][$k_cf];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['date']));
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['start_date']));
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['end_date']));
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
    		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                        }
		                                    }
		    
		                                    $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                    $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                }
		                            }
		                        }
		    
		                        foreach($course_days[$ipid][$v_period_day] as $k_day_course => $v_day_course)
		                        {
		                            if($v_day_course['course_type'] == 'U')
		                            {
		                            $shortcut = '';
		    
		                            if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                && !in_array('37b5', $pseudo_overall_shortcuts[$ipid])
		                                && (!in_array($v_period_day, $flatrate[$ipid]['pay_days'])  && $fl_continued == false))
		                            {
		                                if(empty($flatrate[$ipid]['pay_days']) || (!empty($flatrate[$ipid]['pay_days']) && strtotime($v_period_day) < strtotime($flatrate[$ipid][0])) ) {
		                                    $shortcut = '37b5';
		                                }
		    
		                            }
		    
		                            if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b5', $day_shortcuts[$ipid]) && $shortcut != '37b5')
		                            {
		                                $shortcut = '37b6';
		                            }
		    
		                            if(!empty($shortcut))
		                            {
		                                if($shortcut == '37b5')
		                                {
		                                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b5';
		                                    $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                                }
		    
		                                $day_shortcuts[$ipid][] = $shortcut;
		                            }
		    
		    
		                            if(($shortcut == '37b6' || $shortcut == '37b5') && $master_data[$ipid][$v_period_day]['37b6']['qty'] < '2' && !in_array($k_day_course,$used_pc_data[$ipid]))
		                            {
		                                $used_pc_data[$ipid][] = $k_day_course;
		                                $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                
		                                // calculate duration to find out end time and done date
		                                $duration = "";
		                                $coursearr = array();
		                                $coursearr = explode("|", $course_days[$ipid][$v_period_day][$k_day_course]['course_title']);
		                                
		                                if(count($coursearr) == 3)
		                                { //method implemented with 3 inputs
		                                $duration  = intval($coursearr[0]);
		                                
		                                }
		                                else if(count($coursearr) != 3 && count($coursearr) < 3)
		                                { //old method before anlage 10
		                                $duration  = intval($coursearr[0]);
		                                }
		                                else if(count($coursearr) != 3 && count($coursearr) > 3)
		                                { //new method (U) 3 inputs and 1 select newly added in verlauf
                                        $duration  = intval($coursearr[1]);
		                                }
		                                
		                                
		                                $course_days[$ipid][$v_period_day][$k_day_course]['date'] = date("Ymd",strtotime($coursearr[3]));
		                                $course_days[$ipid][$v_period_day][$k_day_course]['time_from'] = date("Hi",strtotime($coursearr[3]));
		                                $minutes = "";
		                                $minutes = "+".$duration." minutes";
		                                $course_days[$ipid][$v_period_day][$k_day_course]['time_till'] = date("Hi",strtotime($minutes, strtotime($coursearr[3])));
		                                
		                                
// 		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course] = $course_days[$ipid][$v_period_day][$k_day_course];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['day'] = $course_days[$ipid][$v_period_day][$k_day_course]['date'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['start_time'] = $course_days[$ipid][$v_period_day][$k_day_course]['time_from'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['end_time'] =  $course_days[$ipid][$v_period_day][$k_day_course]['time_till'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                
		                            }
		                          }
		                        }
		                    }
		                }
		    
		                	
		                //calculate set three form_type visits
		                //exclude calculation if following shortcuts are calculated on current day
		    
		                if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued == false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid]) )
		                {
		                    	
		                    $last_koord_day[$ipid] = end($last_koord_dates[$ipid]);
		                    $set_three_result[$ipid] = array_intersect($set_three_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		                    $set_ones_result[$ipid] = array_intersect($set_one_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		    
// 		                    if(((count($set_three_result[$ipid]) != 0 || in_array('V', $course_days[$ipid][$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day])) || (count($set_ones_result[$ipid]) != 0 || in_array('V', $course_days[$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                    if(((count($set_three_result[$ipid]) != 0 || in_array('V', $course[$ipid][$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day])) || (count($set_ones_result[$ipid]) != 0 || in_array('V', $course[$v_period_day])) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                    {
		    
		                        foreach($contact_forms_days[$ipid][$v_period_day] as $k_cform => $v_cform)
		                        {
		                            if(is_numeric($k_cform) &&  !in_array($v_cform['id'],$exclude_after_discharge_overall) )
		                            {
		                                $shortcut = '';
		    
		                                if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b7', $pseudo_overall_shortcuts[$ipid])
		                                    && (!in_array($v_period_day, $flatrate[$ipid]['pay_days']) && $fl_continued == false))
		                                {
		                                    if(in_array('4', $sapv_days[$ipid][$v_period_day]))
		                                    {
		                                        $shortcut = '37b8';
		                                        $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                    }
		                                    else if(empty($flatrate[$ipid]['pay_days']))
		                                    {
		                                        $shortcut = '37b7';
		                                    }
		                                }
		    
		                                if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b7', $day_shortcuts[$ipid]) && $shortcut != '37b7')
		                                {
		                                    $shortcut = '37b8';
		                                    $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                }
		    
		                                if(!empty($shortcut))
		                                {
		                                    if($shortcut == '37b7')
		                                    {
		                                        $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b7';
		                                        $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                                    }
		    
		                                    $day_shortcuts[$ipid][] = $shortcut;
		                                }
		    
		    
		                                if(!empty($shortcut))
		                                {
		                                    	
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                    if($shortcut == '37b8' && !in_array($week_number[$ipid], $week_numbers_arr[$ipid]) && in_array($v_cform['form_type'], $set_three_ids[$ipid])  && !in_array($v_cform['id'],$used_cnt_forms[$ipid]))
		                                    {
		                                        $used_cnt_forms[$ipid][] = $v_cform['id'];
		                                        $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                        $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $week_numbers_arr[$ipid][] = $week_number[$ipid];
		                                        
// 		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform] = $contact_forms_days[$ipid][$v_period_day][$k_cform];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['start_date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['end_date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                        
		                                    }
		                                    else if($shortcut == '37b7'  && !in_array($v_cform['id'],$used_cnt_forms[$ipid]))
		                                    {
		                                        $used_cnt_forms[$ipid][] = $v_cform['id'];
		                                        $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                        $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        
// 		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform] = $contact_forms_days[$ipid][$v_period_day][$k_cform];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['start_date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['end_date']));		                                        
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                        
		                                    }
		    
		                                    $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                    $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $last_koord_dates[$ipid][] = $v_period_day;
		                                }
		                            }
		                        }

		                        foreach($course_days[$ipid][$v_period_day] as $k_day_course => $v_day_course)
		                        {
		                            $shortcut = '';
		                            $last_koord_day[$ipid] = end($last_koord_dates[$ipid]);
		    
		                            if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                && !in_array('37b7', $pseudo_overall_shortcuts[$ipid])
		                            )
		                            {
		                                if(in_array('4', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $shortcut = '37b8';
		                                    $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                }
		                                else if(empty($flatrate[$ipid]['pay_days']))
		                                {
		                                    $shortcut = '37b7';
		                                }
		                            }
		    
		                            if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b7', $day_shortcuts[$ipid]) && $shortcut != '37b7')
		                            {
		                                $shortcut = '37b8';
		                                $week_number[$ipid] = date('W', strtotime($v_period_day));
		                            }
		    
		                            if(!empty($shortcut))
		                            {
		    
		    
		                                if($shortcut == '37b7')
		                                {
		                                    $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b7';
		                                    $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                                }
		    
		                                $day_shortcuts[$ipid][] = $shortcut;
		                            }
		    
		                            if(($shortcut == '37b7' || $shortcut == '37b8') && $v_day_course['course_type'] == 'V'  && !in_array($k_day_course,$used_pc_data[$ipid]))
		                            {
		                                
		                                // calculate duration to find out end time and done date
		                                $duration = "";
		                                $course_date = "";
		                                $coursearr = array();
		                                $coursearr = explode("|", $course_days[$ipid][$v_period_day][$k_day_course]['course_title']);
		                                
		                                
		                               
		                                if(count($coursearr) == 3)
		                                { //method implemented with 3 inputs
			                                $duration  = intval($coursearr[0]);
			                                $course_date = $coursearr[2]; 
				                            	    
		                                }
		                                else if(count($coursearr) != 3 && count($coursearr) < 3)
		                                { //old method before anlage 10
		                                	$duration  = intval($coursearr[0]);
			                                $course_date = $coursearr[2]; 
		                                }
		                                else if(count($coursearr) != 3 && count($coursearr) > 3)
		                                { //new method (U) 3 inputs and 1 select newly added in verlauf
		                                	$duration  = intval($coursearr[1]);
			                                $course_date = $coursearr[3]; 
		                                }
		                                
		                                $course_days[$ipid][$v_period_day][$k_day_course]['date'] = date("Ymd",strtotime($course_date));
		                                $course_days[$ipid][$v_period_day][$k_day_course]['time_from'] = date("Hi",strtotime($course_date));
		                                $minutes = "";
		                                $minutes = "+".$duration." minutes";
		                                $course_days[$ipid][$v_period_day][$k_day_course]['time_till'] = date("Hi",strtotime($minutes, strtotime($course_date)));
		                                
		                                
		                                if($shortcut == '37b8' && !in_array($week_number[$ipid], $week_numbers_arr[$ipid]))
		                                {
		                                    $used_pc_data[$ipid][] = $k_day_course;
		                                    $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                    $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $week_numbers_arr[$ipid][] = $week_number[$ipid];
		                                    $last_koord_dates[$ipid][] = $v_period_day;
		                                    
// 		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course] = $course_days[$ipid][$v_period_day][$k_day_course];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['day'] = $course_days[$ipid][$v_period_day][$k_day_course]['date'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['start_time'] = $course_days[$ipid][$v_period_day][$k_day_course]['time_from'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['end_time'] =  $course_days[$ipid][$v_period_day][$k_day_course]['time_till'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                    
		                                }
		                                else if($shortcut == '37b7')
		                                {
		                                    $used_pc_data[$ipid][] = $k_day_course;
		                                    $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                    $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                    $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                    $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $last_koord_dates[$ipid][] = $v_period_day;
		                                    
// 		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course] = $course_days[$ipid][$v_period_day][$k_day_course];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['day'] = $course_days[$ipid][$v_period_day][$k_day_course]['date'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['start_time'] = $course_days[$ipid][$v_period_day][$k_day_course]['time_from'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['end_time'] =  $course_days[$ipid][$v_period_day][$k_day_course]['time_till'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $extra_data[$ipid][$v_period_day][$shortcut]['pc'.$k_day_course]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                    
		                                }
		                            }
		                        }
		                    }
		                }
		    
		    
		                //calculate set 4 (ISPC-241)
		                $set_fourth_result[$ipid] = array_intersect($set_fourth_ids, $contact_forms_days[$ipid][$v_period_day]['form_types']);
		    
		                if(count($set_fourth_result[$ipid]) != 0 && in_array($v_period_day, $active_days_in_period[$ipid]) && !in_array($v_period_day, $hospital_days[$ipid]))
		                {
		                    //setup each form sapv based on classification block selection
		                    foreach($contact_forms_days_ids[$ipid][$v_period_day] as $k_cf_day => $v_cf_day)
		                    {
		                        if($classification_data[$v_cf_day]['intern'] != '1' &&  !in_array($v_cf_day['id'],$exclude_after_discharge_overall))
		                        {
		                            if($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '0')
		                            {
		                                $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '1';
		                            }
		                            else if($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1')
		                            {
		                                //switch between BE-KO if VV is not BE
		                                if(!in_array('1', $sapv_days[$ipid][$v_period_day]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '2';
		                                }
		                                else if(in_array('1', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '1';
		                                }
		                            }
		                            else if($classification_data[$v_cf_day]['beratung'] == '0' && $classification_data[$v_cf_day]['koordination'] == '1')
		                            {
		                                $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf_day]['contact_form_id']] = '2';
		                            }
		                        }
		                    }
		    
		                    //U & V at the top
		                    //beratung
		                    if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued == false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array('1', $sapv_days[$ipid][$v_period_day]))
		                    {
		                        foreach($contact_forms_days[$ipid][$v_period_day] as $k_cf => $v_cf)
		                        {
		                            if( is_numeric($k_cf) && !in_array($v_cf['id'],$exclude_after_discharge_overall))
		                            {
		                                //check if there are 2 beratung change sapv in koord
		                                if($master_data[$ipid][$v_period_day]['37b6']['qty'] == '2' && count($set_fourth_result[$ipid]) != 0 && $classification_data[$v_cf['id']]['beratung'] == '1' && $classification_data[$v_cf['id']]['koordination'] == '1')
		                                {
		                                    $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf['id']]['contact_form_id']] = '2';
		                                }
		                                 
		                                if( $sapv_status[$ipid][$v_period_day][$classification_data[$v_cf['id']]['contact_form_id']] == '1'
		                                //                                         && ( ($classification_data[$v_cf['id']]['beratung'] == '1' || $classification_data[$v_cf['id']]['koordination'] == '1') && (in_array($v_cf['form_type'], $set_fourth_ids)) || in_array($v_cf['form_type'], $set_one_ids) )
		                                )
		                                {
		                                    $shortcut = '';
		                                    if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                        && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                        && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                        && !in_array('37b5', $pseudo_overall_shortcuts[$ipid])
		                                        && (!in_array($v_period_day, $flatrate[$ipid]['pay_days']) && $fl_continued == false)
		                                    )
		                                    {
		                                        if(empty($flatrate[$ipid]['pay_days']) || (!empty($flatrate[$ipid]['pay_days']) && strtotime($v_period_day) < strtotime($flatrate[$ipid][0])) ) {
		                                            $shortcut = '37b5';
		                                        }
		                                    }
		    
		                                    if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b5', $day_shortcuts[$ipid]) && $shortcut != '37b5') //only b5 or b6
		                                    {
		                                        $shortcut = '37b6';
		                                    }
		    
		                                    if(!empty($shortcut))
		                                    {
		                                        if($shortcut == '37b5')
		                                        {
		                                            $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b5';
		                                            $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                                        }
		    
		                                        $day_shortcuts[$ipid][] = $shortcut;
		                                    }
		    
		    
		                                    if(!empty($shortcut))
		                                    {
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		                                        if($shortcut == '37b6' && $master_data[$ipid][$v_period_day]['37b6']['qty'] < '2' && (in_array($v_cf['form_type'], $set_fourth_ids) || in_array($v_cf['form_type'], $set_one_ids) ) && !in_array($v_cf['id'],$used_cnt_forms[$ipid]) )
		                                        //     										if($shortcut == '37b6' && $master_data[$ipid][$v_period_day]['37b6']['qty'] < '2' && (in_array($v_cf['form_type'], $set_fourth_ids))  && !in_array($v_cf['id'],$used_cnt_forms[$ipid]) )
		                                        {
		                                            $used_cnt_forms[$ipid][] = $v_cf['id'];
		                                            $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                            $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                            $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                            
		                                            
// 		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf] = $contact_forms_days[$ipid][$v_period_day][$k_cf];
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['date']));
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['start_date']));
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['end_date']));
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                           $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                            
		                                        }
		                                        else if($shortcut == '37b5' && !in_array($v_cf['id'],$used_cnt_forms[$ipid]))
		                                        {
		                                            $used_cnt_forms[$ipid][] = $v_cf['id'];
		                                            $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                            $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                            $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                            
// 		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf] = $contact_forms_days[$ipid][$v_period_day][$k_cf];
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['date']));
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['start_date']));
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cf]['end_date']));
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                            $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cf]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                            
		                                        }
		                                        $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                        $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    }
		                                }
		                            }
		                        }
		                    }
		    
		    
		                    //koordination
		                    $last_koord_days[$ipid] = end($last_koord_dates[$ipid]);
		                    if((!in_array($v_period_day, $flatrate[$ipid]) && $fl_continued == false) && !in_array('37b2', $day_shortcuts[$ipid]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                    {
		                        foreach($contact_forms_days[$ipid][$v_period_day] as $k_cform => $v_cform)
		                        {
		                            if(is_numeric($k_cform) && $sapv_status[$ipid][$v_period_day][$classification_data[$v_cform['id']]['contact_form_id']] == '2' && !in_array($v_cform['id'],$exclude_after_discharge_overall))
		                            {
		                                $shortcut = '';
		    
		                                if(!in_array('37b1', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b5', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b7', $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))])
		                                    && !in_array('37b7', $pseudo_overall_shortcuts[$ipid])
		                                )
		                                	
		                                {
		                                    if(in_array('4', $sapv_days[$ipid][$v_period_day]))
		                                    {
		                                        $shortcut = '37b8';
		                                        $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                    }
		                                    else if(empty($flatrate[$ipid]['pay_days']))
		                                    {
		                                        $shortcut = '37b7';
		                                    }
		                                }
		                                	
		                                	
		                                if(!in_array('37b2', $day_shortcuts[$ipid]) && !in_array('37b7', $day_shortcuts[$ipid]) && $shortcut != '37b7')
		                                {
		                                    $shortcut = '37b8';
		                                    $week_number[$ipid] = date('W', strtotime($v_period_day));
		                                }
		    
		                                if(!empty($shortcut))
		                                {
		                                    if($shortcut == '37b7')
		                                    {
		                                        $month_shortcuts[$ipid][date('Ym', strtotime($v_period_day))][] = '37b7';
		                                        $pseudo_overall_shortcuts[$ipid][] = $shortcut;
		                                    }
		                                    $day_shortcuts[$ipid][] = $shortcut;
		                                }
		    
		                                if(!empty($shortcut))
		                                {
		                                    $master_data[$ipid][$v_period_day][$shortcut]['shortcut'][] = $shortcut;
		    
		                                    if($shortcut == '37b8' && !in_array($week_number[$ipid], $week_numbers_arr[$ipid]) && in_array($v_cform['form_type'], $set_fourth_ids)  && !in_array($v_cform['id'],$used_cnt_forms[$ipid]) )
		                                    {
		                                        $used_cnt_forms[$ipid][] = $v_cform['id'];
		                                        $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                        $master_data[$ipid][$v_period_day][$shortcut]['qty'] += '1';
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $week_numbers_arr[$ipid][] = $week_number[$ipid];
		                                        
// 		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform] = $contact_forms_days[$ipid][$v_period_day][$k_cform];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['start_date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['end_date']));		                                        
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                        
		                                    }
		                                    else if($shortcut == '37b7' && !in_array($v_cform['id'],$used_cnt_forms[$ipid]))
		                                    {
		                                        $used_cnt_forms[$ipid][] = $v_cform['id'];
		                                        $lag_clasifizierung[$ipid]['billable'][$shortcut][] = $v_period_day;
		                                        $master_data[$ipid][$v_period_day][$shortcut]['qty'] = '1';
		                                        $master_data[$ipid][$v_period_day][$shortcut]['shortcut_total'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        
// 		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform] = $contact_forms_days[$ipid][$v_period_day][$k_cform];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['day'] = date("Ymd",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['start_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['start_date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['end_time'] = date("Hi",strtotime($contact_forms_days[$ipid][$v_period_day][$k_cform]['end_date']));
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['dta_id'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['dta_id'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['booking_account'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['booking_account'];
		                                        $extra_data[$ipid][$v_period_day][$shortcut]['cf'.$k_cform]['location_type'] = $day_location_type[$ipid.$v_period_day];
		                                    }
		    
		                                    $master_data[$ipid][$v_period_day][$shortcut]['price'] = $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $master_data[$ipid][$v_period_day][$shortcut]['location_type'] =$day_location_type[$ipid.$v_period_day];
		                                    $totals[$ipid][$shortcut] += $master_price_list[$v_period_day][0][$day_location_type[$ipid.$v_period_day]][$shortcut]['price'];
		                                    $last_koord_dates[$ipid][] = $v_period_day;
		                                }
		                            }
		                        }
		                    }
		                }
		    
		                	
		                	
		                if(array_key_exists($v_period_day, $sapv_days[$ipid]))
		                {
		                    foreach($contact_forms_days_ids[$ipid][$v_period_day] as $k_cf_day => $v_cf_day)
		                    {
		                        //check if contactform type for each days
		                        if(in_array($contact_forms2form_types[$ipid][$v_cf_day][0], $set_one_ids) && (in_array('4', $sapv_days[$ipid][$v_period_day]) || in_array('3', $sapv_days[$ipid][$v_period_day])) )
		                        {
		                            $clasifizierung[$ipid][$v_period_day]['home_visit'] += 1;
		                            $lag_clasifizierung[$ipid]['all']['home_visit'][] = $v_period_day;
		                            
		                            $extra_details[$ipid][$v_period_day]['home_visit'] []= $contact_forms_days_details[$v_period_day][$k_cf_day];;
		                            
		                        }
		                        else
		                        {
		                            //sum classifizierung data if form is not housebesuche type(1)
		                            if($clasifizierung[$ipid][$v_period_day]['beratung'] < '2' && in_array('1', $sapv_days[$ipid][$v_period_day]) && (($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '0') || ($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1')))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                                $lag_clasifizierung[$ipid]['all']['beratung'][] = $v_period_day;
		                                
		                                $extra_details[$ipid][$v_period_day]['beratung'] []= $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                	
		                            }
		                            else if($clasifizierung[$ipid][$v_period_day]['beratung'] >= '2'  && $classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1' && (in_array('1', $sapv_days[$ipid][$v_period_day]) || in_array('2', $sapv_days[$ipid][$v_period_day])))
		                            {
		                                if(!in_array('1', $sapv_days[$ipid][$v_period_day]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['koordination'][] = $v_period_day;
		                                    
		                                    $extra_details[$ipid][$v_period_day]['koordination'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                                else if(in_array('1', $sapv_days[$ipid][$v_period_day]) && !in_array('2', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['beratung'][] = $v_period_day;
		                                    
		                                    $extra_details[$ipid][$v_period_day]['beratung'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                                else
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['koordination'][] = $v_period_day;
		                                    
		                                    $extra_details[$ipid][$v_period_day]['koordination'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                            }
		                            else if($clasifizierung[$ipid][$v_period_day]['beratung'] < '2' && $classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '1' && (in_array('1', $sapv_days[$ipid][$v_period_day]) || in_array('2', $sapv_days[$ipid][$v_period_day])))
		                            {
		                                if(!in_array('1', $sapv_days[$ipid][$v_period_day]) && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['koordination'][] = $v_period_day;
		                                    
		                                    $extra_details[$ipid][$v_period_day]['koordination'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                                else if(in_array('1', $sapv_days[$ipid][$v_period_day]) && !in_array('2', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['beratung'][] = $v_period_day;
		                                    $extra_details[$ipid][$v_period_day]['beratung'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                                else
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['beratung'][] = $v_period_day;
		                                    $extra_details[$ipid][$v_period_day]['beratung'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                            }
		                            else if($classification_data[$v_cf_day]['beratung'] == '1' && $classification_data[$v_cf_day]['koordination'] == '0' && in_array('1', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                                $lag_clasifizierung[$ipid]['all']['beratung'][] = $v_period_day;
		                                $extra_details[$ipid][$v_period_day]['beratung'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                            }
		                            else if(($classification_data[$v_cf_day]['beratung'] == '0' && $classification_data[$v_cf_day]['koordination'] == '1') && in_array('2', $sapv_days[$ipid][$v_period_day]))
		                            {
		                                $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                                $lag_clasifizierung[$ipid]['all']['koordination'][] = $v_period_day;
		                                
		                                $extra_details[$ipid][$v_period_day]['koordination'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                            }
		                            else if(($classification_data[$v_cf_day]['beratung'] == '0'  && $classification_data[$v_cf_day]['koordination'] == '0') && in_array($contact_forms2form_types[$ipid][$v_cf_day][0], $set_one_ids))
		                            {
		                                if(in_array('1', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['beratung'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['beratung'][] = $v_period_day;
		                                    $extra_details[$ipid][$v_period_day]['beratung'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                                else if(in_array('2', $sapv_days[$ipid][$v_period_day]))
		                                {
		                                    $clasifizierung[$ipid][$v_period_day]['koordination'] += '1';
		                                    $lag_clasifizierung[$ipid]['all']['koordination'][] = $v_period_day;
		                                    
		                                    $extra_details[$ipid][$v_period_day]['koordination'] [] = $contact_forms_days_details[$v_period_day][$k_cf_day];
		                                }
		                            }
		                        }
		                }
		            }
		        }
		    
		        foreach($master_data[$ipid] as $k_day => $v_day_values)
		        {
		            foreach($v_day_values as $k_shortcut => $v_shortcut_values)
		            {
		                $overall_shortcuts[$ipid][] = $k_shortcut;
		                if($v_shortcut_values['qty'] > '0' && !empty($v_shortcut_values['qty']))
		                {
		                    $overall_shortcuts_dates[$ipid][$k_day][] = $k_shortcut;
		                }
		            }
		    
		            if(count($v_day_values) > '0')
		            {
		                $days_products[$ipid][] = $k_day;
		            }
		        }
		    
		        //get days in which sapv is 3 or 4
		        foreach($sapv_days[$ipid] as $ksapv_days => $vsapv_days)
		        {
		            if(in_array('3', $vsapv_days) || in_array('4', $vsapv_days))
		            {
		                $flatrate_sapv_days[$ipid][] = $ksapv_days;
		            }
		        }
		    
		        //return only the products which have more than 28 days from last billable product
		        $pm = new PatientMaster();
		    
		        foreach($days_products[$ipid] as $key_day => $v_day)
		        {
		            if($key_day > '0')
		            {
		                $prod_gap[$ipid][$v_day] = $pm->getDaysInBetween($days_products[$ipid][($key_day - 1)], $v_day);
		                //						$prod_gap[$ipid][$v_day] = array_intersect($prod_gap[$ipid][$v_day], $active_days[$ipid], $flatrate_sapv_days[$ipid]);
		                //						$prod_gap[$ipid][$v_day] = array_intersect($prod_gap[$ipid][$v_day], $active_days[$ipid]);
		    
		                $prod_gap[$ipid][$v_day] = array_values(array_unique($prod_gap[$ipid][$v_day]));
		    
		                if(count($prod_gap[$ipid][$v_day]) >= '30')
		                {
		                    $last_product_gap_days[$ipid][] = $v_day;
		                    $master_overall_data[$ipid]['shortcuts'][$v_day] = '37b1';
		                }
		            }
		        }
		    
		            //calculate ministry calculated/non-calculated visits
		            foreach($clasifizierung[$ipid] as $k_s_day => $v_type_visits)
		            {
		                foreach($v_type_visits as $k_visit_type => $v_visit_ammount)
		                {
		                    if($v_visit_ammount > '0')
		                    {
		                        if(in_array('37b1', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                        {
		                            $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                            $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		    
		    
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		    
		                        if($k_visit_type == "home_visit")
		                        {
		                            //one per day only (calculated can be max 1 visit)
		                            if(in_array('37b2', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "1";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - 1);
		                            }
		                            else
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		                            }
		    
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		    
		                        if($k_visit_type == "beratung")
		                        {
		                            //one per day and one time only
		                            if(in_array('37b5', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "1";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - 1);
		                            }
		                            //can be max 2 per day
		                            else if(in_array('37b6', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                if($v_visit_ammount >= '2')
		                                {
		                                    $v_visit_ammount_limit = "2";
		                                }
		                                else
		                                {
		                                    $v_visit_ammount_limit = "1";
		                                }
		    
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = $v_visit_ammount_limit;
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - $v_visit_ammount_limit);
		                            }
		                            else
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		                            }
		    
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		    
		                        if($k_visit_type == "koordination")
		                        {
		                            //b7 one per day and one time only
		                            //b8 one per day and one per week
		                            if(in_array('37b7', $overall_shortcuts_dates[$ipid][$k_s_day]) || in_array('37b8', $overall_shortcuts_dates[$ipid][$k_s_day]))
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "1";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = ($v_visit_ammount - 1);
		                            }
		                            else
		                            {
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                                $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = $v_visit_ammount;
		                            }
		    
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                            $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                        }
		                    }
		                    else
		                    {
		                        $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'] = "0";
		                        $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'] = "0";
		    
		                        $all_visits_status[$ipid][$k_visit_type . "_total"]['calculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['calculated'];
		                        $all_visits_status[$ipid][$k_visit_type . "_total"]['noncalculated'][$k_s_day] = $visits_status[$ipid][$k_s_day][$k_visit_type]['noncalculated'];
		                    }
		                }
		            }
		    
		            //sum all totals arrays
		            foreach($all_visits_status[$ipid] as $total_visit_type => $v_totals)
		            {
		                $all_calculated_visits[$ipid][$total_visit_type]['calculated'] = array_sum($v_totals['calculated']);
		                $all_calculated_visits[$ipid][$total_visit_type]['noncalculated'] = array_sum($v_totals['noncalculated']);
		            }
		    
		    
		            $master_overall_data[$ipid]['shortcuts'] = array_values(array_unique($overall_shortcuts[$ipid]));
		            $master_overall_data[$ipid]['shortcuts_dates'] = $overall_shortcuts_dates[$ipid];
// 		            $master_overall_data[$ipid]['last_koord_dates'] = array_values(array_unique($last_koord_dates[$ipid]));
// 		            $master_overall_data[$ipid]['days_products'] = array_values(array_unique($last_product_gap_days[$ipid]));
// 		            $master_overall_data[$ipid]['clasifizierung'] = $clasifizierung[$ipid];
// 		            $master_overall_data[$ipid]['all_visit_types_totals'] = $all_calculated_visits[$ipid];
// 		            $master_overall_data[$ipid]['lag_details'] = $visits_status[$ipid];
// 		            $master_overall_data[$ipid]['lag_data'] = $lag_clasifizierung[$ipid];
		            
		            $master_overall_data[$ipid]['extra'] = $extra_data[$ipid];
		            }
		    
		            if($_REQUEST['xdg']){
		                echo "<pre/>";
		                print_r($master_data);
		                
		            }
		            
		            if($_REQUEST['dbg_vis'] == "y" && $flatrate_continued)
		            {
		                print_r($master_data);
		                exit;
		            }
		            if($_REQUEST['dbg_vis'] == "x" && $flatrate_continued)
		            {
		                print_r($overall_shortcuts_dates);
		                exit;
		            }
		    
		            if($_REQUEST['dbg_vis'] == "3" && $flatrate_continued)
		            {
		                print_r($all_visits_status);
		                exit;
		            }
		            if($_REQUEST['dbg_vis'] == "2" && $flatrate_continued)
		            {
		                print_r($clasifizierung);
		                exit;
		            }
		            if($_REQUEST['dbg_vis'] == "1" && $flatrate_continued)
		            {
		                var_dump($visits_status);
		                exit;
		            }
		            //			print_r($master_overall_data);
		            //			exit;
		            return $master_overall_data;
		    }
		    
		

		    

		    private function get_paients_hospital_epid($ipids = false)
		    {
		    	if($ipids)
		    	{
		    		$course = Doctrine_Query::create()
		    		->select("id, ipid, course_date, wrong, done_date, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type")
		    		->from('PatientCourse')
		    		->where("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'ID'")
		    		->andWhere("wrong = 0")
		    		->andWhereIn('ipid', $ipids)
					->andWhere('source_ipid = ""')
		    		->orderBy('course_date ASC');
		    		$course_res = $course->fetchArray();
		    
		    		foreach($course_res as $k_course => $v_course)
		    		{
		    			$course_date = date('Y-m-d H:i:s', strtotime($v_course['course_date']));
		    
		    			$days_course[$v_course['ipid']][$course_date] = $v_course;
		    		}
		    
		    		return $days_course;
		    	}
		    }
		    
		    
		
	/*
 	* RP INVOICES  
 	*/
		    

		    public function listdtarpinvoicesAction()
		    {
		    	$logininfo = new Zend_Session_Namespace('Login_Info');
		    	$userid = $logininfo->userid;
		    	$clientid = $logininfo->clientid;
		    
		    	if($clientid > 0)
		    	{
		    		$where = ' and client=' . $logininfo->clientid;
		    	}
		    	else
		    	{
		    		$where = ' and client=0';
		    	}
		    
		    	$storned_invoices = RpInvoices::get_storned_invoices($clientid);
		    	$unpaid_status = array("2","5");
		    		
		    	//construct months array in which the curent client has bre_invoices completed, not paid
		    	$months_q = Doctrine_Query::create()
		    	->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
		    	->from('RpInvoices')
		    	->where("isdelete = 0")
		    	->andWhere('completed_date != "0000-00-00 00:00:00"')
		    	->andWhere("storno = 0 " . $where)
		    	->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    	->andWhereIN("status",$unpaid_status) // display only unpaid
		    	->orderBy('DISTINCT DESC');
		    	$months_res = $months_q->fetchArray();

		    	if($months_res)
		    	{
		    		//current month on top
		    		$months_array[date('Y-m', time())] = date('m-Y', time());
		    		foreach($months_res as $k_month => $v_month)
		    		{
		    			$months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
		    		}
		    
		    		$months_array = array_unique($months_array);
		    	}
		    
		    	if(strlen($_REQUEST['search']) > '0')
		    	{
		    		$selected_period['start'] = $_REQUEST['search'] . "-01";
		    		$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
		    	}
		    
		    	$this->view->months_array = $months_array;
		    
		    	if($this->getRequest()->isPost())
		    	{
		    		$post = $_POST;
		    
		    		$dta_data = $this->gather_dta_rp_data($clientid, $userid, $post);
		    		$this->generate_dta_xml($dta_data);
		    		exit;
		    	}
		    }
		    
		    public function fetchdtarpinvoiceslistAction()
		    {
		    	$logininfo = new Zend_Session_Namespace('Login_Info');
		    	$hidemagic = Zend_Registry::get('hidemagic');
		    	$userid = $logininfo->userid;
		    	$clientid = $logininfo->clientid;
		    	$user_type = $logininfo->usertype;
		    
		    	$columnarray = array(
		    			"pat" => "epid_num",
		    			"invnr" => "invoice_number",
		    			"invstartdate" => "invoice_start",
		    			"invdate" => "completed_date_sort",
		    			"invtotal" => "invoice_total",
		    			"invkasse" => "company_name", // used in first order of health insurances
		    	);
		    
		    	if($clientid > 0)
		    	{
		    		$where = ' and client=' . $logininfo->clientid;
		    	}
		    	else
		    	{
		    		$where = ' and client=0';
		    	}
		    
		    	$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		    	$this->view->order = $orderarray[$_REQUEST['ord']];
		    	$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		    
		    	$client_users_res = User::getUserByClientid($clientid, 0, true);
		    
		    	foreach($client_users_res as $k_user => $v_user)
		    	{
		    		$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
		    	}
		    
		    	$this->view->client_users = $client_users;
		    
		    	//get patients data used in search and list
		    	$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    	$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    	$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    	$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    	$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    	$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		    
		    	/* // if super admin check if patient is visible or not
		    	 if($logininfo->usertype == 'SA')
		    	 {
		    	 $sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    	 $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
		    	 $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
		    	 $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
		    	 $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
		    	 $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
		    	 } */
		    
		    	$f_patient = Doctrine_Query::create()
		    	->select($sql)
		    	->from('PatientMaster p')
		    	->where("p.isdelete =0")
		    	->leftJoin("p.EpidIpidMapping e")
		    	->andWhere('e.clientid = ' . $clientid);
		    
		    	if($_REQUEST['clm'] == 'pat')
		    	{
		    		$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    	}
		    
		    	$f_patients_res = $f_patient->fetchArray();
		    
		    	$f_patients_ipids[] = '9999999999999';
		    	foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		    	{
		    		$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		    		$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
		    	}
		    
		    	$this->view->client_patients = $client_patients;
		    
		    	if(strlen($_REQUEST['val']) > '0')
		    	{
		    		$selected_period['start'] = $_REQUEST['val'] . "-01";
		    		$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    	}
		    	else
		    	{
		    		$selected_period['start'] = date('Y-m-01', time());
		    		$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    	}
		    
		    	//order by health insurance
		    	if($_REQUEST['clm'] == "invkasse")
		    	{
		    		$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		    
		    		$drop = Doctrine_Query::create()
		    		->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
		    		->from('PatientHealthInsurance')
		    		->whereIn("ipid", $f_patients_ipids)
		    		->orderBy($orderby);
		    		$droparray = $drop->fetchArray();
		    
		    		$f_patients_ipids = array();
		    		foreach($droparray as $k_pat_hi => $v_pat_hi)
		    		{
		    			$f_patients_ipids[] = $v_pat_hi['ipid'];
		    		}
		    	}
		    
		    		
		    	$storned_invoices = RpInvoices::get_storned_invoices($clientid);
		    	$unpaid_status = array("2","5");
		    		
		    	$fdoc = Doctrine_Query::create()
		    	->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
		    	->from('RpInvoices')
		    	->where("isdelete = 0 " . $where)
		    	->andWhere("storno = '0'")
		    	->andWhere('completed_date != "0000-00-00 00:00:00"')
		    	->andWhereIn('ipid', $f_patients_ipids)
		    	->andWhereNotIN("id",$storned_invoices) // remove storned invoices from list
		    	->andWhereIN("status",$unpaid_status) // display only unpaid
		    	->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
		    	if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
		    	{
		    		$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    	}
		    	else
		    	{
		    		//sort by patient sorted ipid order
		    		$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
		    	}
		    
		    	//used in pagination of search results
		    	$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
		    	//			print_r($fdoc->getSqlQuery());
		    	//			exit;
		    	$fdocarray = $fdoc->fetchArray();
		    	$limit = 500;
		    	$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
		    	$fdoc->where("isdelete = 0 " . $where . "");
		    	$fdoc->andWhere("storno = '0'");
		    	$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
		    	$fdoc->andWhereIn('ipid', $f_patients_ipids);
		    	$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	$fdoc->andWhereIN("status",$unpaid_status);  // display only unpaid
		    	$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
		    	$fdoc->limit($limit);
		    	$fdoc->offset($_REQUEST['pgno'] * $limit);
		    
		    	if($_REQUEST['dbgq'])
		    	{
		    		print_r($fdoc->getSqlQuery());
		    		print_r($fdoc->fetchArray());
		    
		    		exit;
		    	}
		    	$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		    
		    	//get ipids for which we need health insurances
		    	foreach($fdoclimit as $k_inv => $v_inv)
		    	{
		    		$inv_ipids[] = $v_inv['ipid'];
		    	}
		    
		    	$inv_ipids[] = '99999999999999';
		    
		    
		    	//6. patients health insurance
		    	$phelathinsurance = new PatientHealthInsurance();
		    	$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		    
		    	$company_ids[] = '9999999999999';
		    	foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    	{
		    		$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		    
		    		if($v_healthinsu['companyid'] != '0')
		    		{
		    			$company_ids[] = $v_healthinsu['companyid'];
		    		}
		    	}
		    
		    	$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		    
		    	foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    	{
		    		if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		    		{
		    			$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		    
		    			if(strlen($healtharray['name']) > '0')
		    			{
		    				$ins_name = $healtharray['name'];
		    			}
		    			else if(strlen($v_health_insurance[0]['company_name']) > '0')
		    			{
		    				$ins_name = $v_health_insurance[0]['company_name'];
		    			}
		    		}
		    
		    		//health insurance name
		    		$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    	}
		    	$this->view->healthinsurances = $healthinsu;
		    
		    
		    	$this->view->{"style" . $_GET['pgno']} = "active";
		    	if(count($fdoclimit) > '0')
		    	{
		    		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtarpinvoiceslist.html");
		    		$this->view->templates_grid = $grid->renderGrid();
		    		$this->view->navigation = $grid->dotnavigation("dtarpinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
		    	}
		    	else
		    	{
		    		//no items found
		    		$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
		    		$this->view->navigation = '';
		    	}
		    
		    	$response['msg'] = "Success";
		    	$response['error'] = "";
		    	$response['callBack'] = "callBack";
		    	$response['callBackParameters'] = array();
		    	$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtarpinvoiceslist.html');
		    
		    	echo json_encode($response);
		    	exit;
		    }
		    

		    private function gather_dta_rp_data($clientid, $userid, $post)
		    {
		    	$patientmaster = new PatientMaster();
		    	
		    	//1. get all selected invoices data
		    	$rp_invoices = new RpInvoices();
		    	$rp_invoices_data = $rp_invoices->get_multiple_rp_invoices($post['invoices']['rp'],false,true);
				
		    	if($rp_invoices_data === false){
		    		return array();
		    	}
		    	
		    	$shortcuts = Pms_CommonData::get_prices_shortcuts();
		    	$rp_shs = array_values($shortcuts['rp']);
		    	
		    	
// 		    	foreach($rp_invoices_data as $inv_idd=>$inv_data){
// // 		    		$rp_invoices_data_alter[$inv_idd] = $inv_data;
// 		    		foreach($rp_shs as $odr=>$rpsh){
// 		    			if($inv_data['items'][$rpsh]){
// 			    			$rp_invoices_data_alter[$inv_idd]['items'][$rpsh] = $inv_data['items'][$rpsh] ;
// 		    			}  
// 		    		}
// 		    	}
// 		    	print_R($rp_invoices_data);
// 		    	print_R("ALTWER");
// 		    	print_R($rp_invoices_data_alter);
// 		    	exit;
		    	
		    	
		    	
		    	$invoiced_days = array();
		    	$patients_invoices_sapv_periods = array();
		    	$patients_invoices_periods = array();
		    	$patients2invoices = array();
		    		
		    	foreach($rp_invoices_data as $k_inv => $v_inv)
		    	{
		    		$invoices_patients[] = $v_inv['ipid'];
		    		$patients2invoices[$v_inv['id']] = $v_inv['ipid'];
		    
		    		// invoice periods per pateint
		    		$invoice_period_patient[$v_inv['id']]['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		    		$invoice_period_patient[$v_inv['id']]['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    		$patients_invoices_periods[$v_inv['ipid']][$v_inv['id']] = $invoice_period_patient[$v_inv['id']];
		    		
		    		// overall 
		    		$invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		    		$invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    
		    		
		    		
		    		
		    		// sapv periods on invoice per patient
		    		$invoice_period_sapv[$v_inv['id']]['start'] = date('Y-m-d', strtotime($v_inv['sapv_start']));
		    		$invoice_period_sapv[$v_inv['id']]['end'] = date('Y-m-d', strtotime($v_inv['sapv_end']));
		    		$patients_invoices_sapv_periods[$v_inv['ipid']][$v_inv['id']] = $invoice_period_sapv[$v_inv['id']];

		    		
		    		// invoice items
		    		
		    		foreach($rp_shs as $odr=>$rpsh){
		    			if($v_inv['items'][$rpsh]){
		    				$rp_invoices_data[$k_inv]['items_nosh'][] = $v_inv['items'][$rpsh] ;
		    			}
		    		}
		    		
// 		    		foreach($v_inv['items'] as $sh_item_data=>$itm){
// 		    			$rp_invoices_data[$k_inv]['items_nosh'][] = $itm;
// 		    		}
		    		
		    		// invoice days per patient per invoice
		    		$invoiced_days[$v_inv['ipid']][$v_inv['id']] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		    		
		    	}
		    	
		    	
		    	asort($invoice_periods);
		    	$invoice_periods_date = array_values($invoice_periods);
		    	$invoice_period['start'] = $invoice_periods_date[0];
		    	$invoice_period['end'] = end($invoice_periods_date);
		    		
		    	//2. get all required client data
		    	$clientdata = Pms_CommonData::getClientData($clientid);
		    	$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
		    	$client_data['client']['team_name'] = $clientdata[0]['team_name'];
		    	$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
		    	$client_data['client']['phone'] = $clientdata[0]['phone'];
		    	$client_data['client']['fax'] = $clientdata[0]['fax'];
		    
		    
		    
		    	//3. get pflegestuffe in current period
		    	$pflege = new PatientMaintainanceStage();
		    	$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		    
		    	foreach($pflege_arr as $k_pflege => $v_pflege)
		    	{
		    		$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
		    	}
		    
		    	foreach($grouped_pflege as $k_gpflege => $v_gpflege)
		    	{
		    		$last_pflege = end($v_gpflege);
		    
		    		if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
		    		{
		    			//$k_gpflege = patient epid
		    			$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
		    		}
		    		else
		    		{
		    			$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
		    		}
		    	}
		    
		    	//4. get all involved patients required data
		    	$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
		    
		    	foreach($patient_details as $k_pat_ipid => $v_pat_details)
		    	{
		    		$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
		    		$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
		    		$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
		    		$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
		    		$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
		    		$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
		    		$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
		    	}
		    
		    	//4.1 get patients readmission details
		    	$conditions['periods'][0]['start'] = '2009-01-01';
		    	$conditions['periods'][0]['end'] = date('Y-m-d');
		    	$conditions['client'] = $clientid;
		    	$conditions['ipids'] = $invoices_patients;
		    	$patient_days = Pms_CommonData::patients_days($conditions);
		    
		    	foreach($patient_days as $k_patd_ipid => $v_pat_details)
		    	{
		    		$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
		    	}
		    
		    	//5. pricelist
		    	$p_list = new PriceList();
		    	$master_price_list = $p_list->get_period_price_list($invoice_period['start'], $invoice_period['end']); //get bra sapv pricelist and then shortcuts
		    	$curent_pricelist = $master_price_list[$invoice_period['start']][0];
		    
		    	//6. patients health insurance
		    	$phelathinsurance = new PatientHealthInsurance();
		    	$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		    
		    	$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
		    	// ispc = M => 1 = Versicherungspflichtige und -berechtigte
		    	// ispc = F => 3 = Familienversicherte
		    	// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
		    	//TODO-3528 Lore 12.11.2020
		    	$modules = new Modules();
		    	$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
		    	if($extra_healthinsurance_statuses){
		    	    $status_int_array += array(
		    	        "00" => "00",          //"Gesamtsumme aller Stati",
		    	        "11" => "11",          //"Mitglieder West",
		    	        "19" => "19",          //"Mitglieder Ost",
		    	        "31" => "31",          //"Angehörige West",
		    	        "39" => "39",          //"Angehörige Ost",
		    	        "51" => "51",          //"Rentner West",
		    	        "59" => "59",          //"Rentner Ost",
		    	        "99" => "99",          //"nicht zuzuordnende Stati",
		    	        "07" => "07",          //"Auslandsabkommen"
		    	    );
		    	}
		    	//.
		    	
		    	$company_ids[] = '9999999999999';
		    	foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    	{
		    		$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		    
		    		if($v_healthinsu['companyid'] != '0')
		    		{
		    			$company_ids[] = $v_healthinsu['companyid'];
		    		}
		    	}
		    
		    	$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		    		
		    		
		    	//get health insurance subdivizions
		    	$symperm = new HealthInsurancePermissions();
		    	$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		    
		    	if($divisions)
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
		    							->whereIn("company_id", $company_ids)
		    							->andWhereIn("ipid", $invoices_patients);
		    		$hi2s_arr = $hi2s->fetchArray();
		    	}
		    
		    	if($hi2s_arr)
		    	{
		    		foreach($hi2s_arr as $k_subdiv => $v_subdiv)
		    		{
		    			if($v_subdiv['subdiv_id'] == "3")
		    			{
		    				$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
		    			}
		    		}
		    	}
		    
		    	foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    	{
		    		if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		    		{
		    			$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		    
		    			if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
		    			{
		    				$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
		    			}
		    
		    			if(strlen($healtharray['name']) > '0')
		    			{
		    				$ins_name = $healtharray['name'];
		    			}
		    			else if(strlen($v_health_insurance[0]['company_name']) > '0')
		    			{
		    				$ins_name = $v_health_insurance[0]['company_name'];
		    			}
		    		}
		    
		    		//health insurance name
		    		$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    
		    		//Versichertennummer
		    		$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		    
		    		//Institutskennzeichen
		    		$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		    
		    		//Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
		    		$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		    
		    		// Health insurance status - ISPC- 1368 // 150611
		    		$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
		    	}

		    	
				// $visits_data = $this->get_rp_related_visits($clientid, $invoices_patients, $invoice_period,$patients_invoices_periods,$patients_invoices_sapv_periods);
		    	$visits_data = $this->get_rp_related_visits($clientid, $patients2invoices, $invoice_period,$patients_invoices_periods,$patients_invoices_sapv_periods);
		    	
		    	if($_REQUEST['dbgz'])
		    	{
		    		print_r($visits_data);
		    		exit;
		    	}

		    	//7. get (HD) main diagnosis
		    	$main_abbr = "'HD'";
		    	$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		    
		    	foreach($main_diag as $key => $v_diag)
		    	{
		    		$type_arr[] = $v_diag['id'];
		    	}
		    
		    	$pat_diag = new PatientDiagnosis();
		    	$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		    
		    	foreach($dianoarray as $k_diag => $v_diag)
		    	{
		    		//append diagnosis in patient data
		    		$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
		    		//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
		    		//ISPC-2489 Lore 26.11.2019
		    		$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
		    		
		    		$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
		    	}
		    
		    	//8. get user data
		    	$user_details = User::getUserDetails($userid);
		    
		    	
		    	//reloop the invoices data array
		    	$added2xml = array();
//  print_r($rp_invoices_data); exit;
		    	
		    	foreach($rp_invoices_data as $k_invoice => $v_invoice)
		    	{
		    		if(!$master_data['invoice_' . $k_invoice])
		    		{
		    			$master_data['invoice_' . $k_invoice] = array();
		    		}

		    		
		    		//Invoice,Client and pateint details 
		    		$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);

		    		// Invoice details
		    		$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
		    		$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
		    		$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    		$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
		    		
		    		//Health insurance info 
		    		$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
		    		$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		    		// Client info
		    		$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		    
		    		// Sapv details
		    		$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $visits_data[ $v_invoice['ipid'] ][$v_invoice['id']]['sapv']['sapv_start_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $visits_data[ $v_invoice['ipid'] ][$v_invoice['id']]['sapv']['sapv_end_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $visits_data[ $v_invoice['ipid'] ][$v_invoice['id']]['sapv']['sapv_approved_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $visits_data[ $v_invoice['ipid'] ][$v_invoice['id']]['sapv']['sapv_approved_nr'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $visits_data[ $v_invoice['ipid'] ][$v_invoice['id']]['sapv']['sapv_create_date'];
		    
		    		// Items details
		    		$inv_items = array();
		    		$vitem_total = 0 ;
		    		foreach($v_invoice['items_nosh'] as $k_item => $v_item)
		    		{
		    				$inv_actions = array();
		    				$inv_actions = array();
		    				if($visits_data[ $v_invoice['ipid'] ][$v_invoice['id']][$v_item['shortcut']]){
		    					
		    					$actions_count[$v_item['shortcut']] = 1;
		    					$vitem_total = $v_item['qty_home'] + $v_item['qty_nurse']+ $v_item['qty_hospiz'];
			    				foreach($visits_data[ $v_invoice['ipid'] ][$v_invoice['id']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
			    				{
			    					$ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].' '.$v_action['start'].'-'.$v_action['end'];
			    					if( ! in_array($ident,$added2xml[$k_invoice])
			    							&&  $actions_count[$v_item['shortcut']] <=  $vitem_total 
			    							&& in_array(date("Y-m-d",strtotime($v_action['start'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )  ){
			    					
				    					$inv_actions['dta_id'] =  $v_action['dta_id'];
				    					$inv_actions['dta_name'] = trim($v_action['dta_name']);
				    					$inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
				    					$inv_actions['ammount'] = str_pad(number_format($v_action['qty'], '2', ',', ''), "7", "0", STR_PAD_LEFT);
				    					$inv_actions['day'] = date('Ymd', strtotime($v_action['start']));
				    					$inv_actions['start_time'] = date('Hi', strtotime($v_action['start']));
				    
				    					if(strlen($v_action['end']) > '0')
				    					{
				    						$inv_actions['end_time'] = date('Hi', strtotime($v_action['end']));
				    					}
				    
				    					$inv_items['actions']['action_' . $k_action] = $inv_actions;
				    					$added2xml[$k_invoice][] = $ident ;
				    					$actions_count[$v_item['shortcut']]++;
			    					}
			    					
			    				}
			    				$master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
			    				$inv_actions = array();
			    				$inv_items = array();
		    				}
		    		}
		    		$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    	}
		    	return $master_data;
		    }	    

		    
		    private function get_rp_related_visits($clientid, $invoices_patients, $selected_period,$patients_invoices_periods,$patients_invoices_sapv_periods)
		    {
		    	$patientmaster = new PatientMaster();
		    	//Client Hospital Settings START
		    	$conditions['periods'][0]['start'] = $selected_period['start'];
		    	$conditions['periods'][0]['end'] = $selected_period['end'];
		    	$conditions['client'] = $clientid;
		    	$patient2invoices = array();
		    	$patient2invoices = $invoices_patients;
		    	$invoices_patients = array_values($invoices_patients);
		    	
		    	$conditions['ipids'] = $invoices_patients;
		    	$patient_days = Pms_CommonData::patients_days($conditions);
		    
		    	$current_period_days = $patientmaster->getDaysInBetween($selected_period['start'], $selected_period['end']);
		    

		    	$invoice_period_days = array();
		    	foreach($patients_invoices_periods as $patients_ipids => $invoice_periods){
		    		foreach($invoice_periods as $k_invoice_id=>$invper){
		    		    //TODO-2908 Ancuta 11.02.2020 - comented line: 
		    			//$invoice_period_days[$k_invoice_id] = $patientmaster->getDaysInBetween($invper['start'], $invper['end']);
		    			// changed to: 
		    			$invoice_period_days_arr[$k_invoice_id] = $patientmaster->getDaysInBetween($invper['start'], $invper['end']);
		    			foreach($invoice_period_days_arr[$k_invoice_id] as $day ){
		    			    if(in_array(date("d.m.Y",strtotime($day)),$patient_days[$patients_ipids]['real_active_days'])){
		    			        $invoice_period_days[$k_invoice_id][] = date("Y-m-d",strtotime($day));
		    			    }
		    			}
		    			// --
		    		}
		    	}
		 
		    	
		    	$sapv_invoiced_period_days = array();
		    	
		    	foreach($invoices_patients as $iipid)
		    	{
		    		foreach($patients_invoices_sapv_periods[$iipid] as $k_invoice_id => $speriod)
		    		{
		    			if(! is_array($sapv_invoiced_period_days[$iipid])){
		    				$sapv_invoiced_period_days[$iipid] = array();
		    			}
			    		$sapv_invoiced_period_days[$iipid] = array_merge($sapv_invoiced_period_days[$iipid],$patientmaster->getDaysInBetween($speriod['start'], $speriod['end']));

		    			if(! is_array($sapv_invoiced_period_days2invoice[$iipid][$k_invoice_id])){
		    				$sapv_invoiced_period_days2invoice[$iipid][$k_invoice_id] = array();
		    			}
			    		$sapv_invoiced_period_days2invoice[$iipid][$k_invoice_id] = array_merge($sapv_invoiced_period_days2invoice[$iipid][$k_invoice_id],$patientmaster->getDaysInBetween($speriod['start'], $speriod['end']));
		    		}
		    	}
		    	
		    	foreach($invoices_patients as $iipid){
		    		$sapv_invoiced_period_days[$iipid] = array_unique($sapv_invoiced_period_days[$iipid] );
		    	}
		    	
		    	foreach($patient2invoices as $inid=>$pipidd){
		    		$sapv_invoiced_period_days2invoice[$pipidd][$inid] = array_unique($sapv_invoiced_period_days2invoice[$pipidd][$inid]); 
		    	}
		    	
// 		    	find if there is a sapv for current period START!
		    	$dropSapv = Doctrine_Query::create()
		    	->select('*')
		    	->from('SapvVerordnung')
		    	->whereIn('ipid', $invoices_patients)
		    	->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    	->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    	->andWhere('isdelete=0')
		    	->orderBy('verordnungam ASC');
		    	$droparray = $dropSapv->fetchArray();
		    
		    	$all_sapv_days = array();
		    	$temp_sapv_days = array();

		    	$s=0;
		    	foreach($droparray as $k_sapv => $v_sapv)
		    	{
		    		$r1['start'][$v_sapv['ipid']][$s] = "";
		    		$r1['end'][$v_sapv['ipid']][$s] = "";
		    			
		    		$r2['start'][$v_sapv['ipid']][$s] = "";
		    		$r2['end'][$v_sapv['ipid']][$s] = "";
		    
 
		 
		    		if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
		    			// no sapv taken here - becouse it is considered to be fully denied
		    		}
		    		else
		    		{
		    			
		    			foreach($patients_invoices_periods[$v_sapv['ipid']] as $pr_id =>$inv_per_data){
		    				
		    		 
		    			
			    			$r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
			    			$r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
			    
			    			$r2['start'][$v_sapv['ipid']][$s] = strtotime($inv_per_data['start']);
			    			$r2['end'][$v_sapv['ipid']][$s] = strtotime($inv_per_data['end']);
			    
		    				$invoiced_sapv_type[$v_sapv['ipid']][] = $v_sapv;
		    				
		    				$sapv_period2type_arr[$v_sapv['ipid']][date('Y-m-d',strtotime($v_sapv['verordnungam']))] = $v_sapv['verordnet'];
		    				
			    			if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
			    			{
			    				$invoiced_sapv[$v_sapv['ipid']] = $v_sapv['id'];
			    				
			    				//aditional data from sapv which was added on 16.10.2014
			    				if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
			    				{
			    					$sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
			    				}
			    
			    				if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
			    					$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
			    				} else{
			    					$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
			    				}
			    
			    
			    				if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
			    				{
			    					$v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
			    				}
			    
			    
			    				$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
			    				$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
			    
			    				$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
			    
			    
			    				//aditional data from sapv which was added on 31.10.2014
			    				$sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
			    				$sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
			    				$sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
			    				$sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
			    
			    				foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
			    				{
			    					if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
			    					{
			    						$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
			    					}
			    
			    					$current_verordnet = explode(',', $v_sapv['verordnet']);
			    					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
			    
			    					asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
			    					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
			    				}
			    
			    				$s++;
			    			}
		    			}
		    			
		    			
		    		}
		    	}
	 
		    	$sapv_on_invoice = array();
		    	foreach($invoiced_sapv_type as $k=>$sd){
					foreach($sd as $k=>$sdata){
						
						foreach($patients_invoices_periods[ $sdata['ipid']] as $invoice_id => $periods){
							
							$r2['start'][$sdata['ipid']]  = strtotime($periods['start']);
							$r2['end'][$sdata['ipid']]  = strtotime($periods['end']);
						
							if(Pms_CommonData::isintersected(strtotime(date('Y-m-d', strtotime($sdata['verordnungam']))),strtotime(date('Y-m-d', strtotime($sdata['verordnungbis']))), $r2['start'][$sdata['ipid']] , $r2['end'][$sdata['ipid']])  )
							{
				    			if($sdata['verordnet'] == "1"){
					    			$sapv_on_invoice[$invoice_id][$sdata['ipid']]['only_be']= '1';
				    			} else{
					    			$sapv_on_invoice[$invoice_id][$sdata['ipid']]['only_be']= '0';
				    			}
	
				    			$sapv_on_invoice[$invoice_id][$sdata['ipid']]['sapv_start']= date('d.m.Y',strtotime($sdata['verordnungam']));
				    			$sapv_on_invoice[$invoice_id][$sdata['ipid']]['sapv_end']= date('d.m.Y',strtotime($sdata['verordnungbis']));
				    			$sapv_on_invoice[$invoice_id][$sdata['ipid']]['create_date']= date('d.m.Y',strtotime($sdata['create_date']));
				    			
				    			//aditional data from sapv which was added on 16.10.2014
				    			if($sdata['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($sdata['approved_date'])) != '1970-01-01' && $sdata['status'] == '2')
				    			{
				    				
				    				$sapv_on_invoice[$invoice_id][$sdata['ipid']]['approved_date'] = date('d.m.Y', strtotime($sdata['approved_date']));
				    				$sapv_on_invoice[$invoice_id][$sdata['ipid']]['approved_number'] = $sdata['approved_number'];
				    			}
				    		}
						}
					}    		
		    	}
		    	
		    	$only_be_before = array();
		    	$execpt_be = array();
		    	foreach($patient2invoices as $invidd=>$pat_ipid){
		    		
			    	foreach($sapv_period2type_arr as $sv_ipid => $sapv_period2type  ){
			    		foreach($sapv_period2type as $per_start => $per_type )
			    		{
			    			if(strtotime($per_start)  < strtotime($sapv_on_invoice[$invidd][$sv_ipid]['sapv_start'])  && $per_type == "1")
			    			{
			    				$only_be_before[$invidd][$sv_ipid][] =  $per_start ;
			    			} else {
			    				$execpt_be[$invidd][$sv_ipid][] =  $per_start ;
			    			}
			    		}
			    	}
		    	}
		    	
		    	$days2sapv_type  = array();
		    	foreach($days2verordnet as $pipid=>$s_dates){
		    		foreach($s_dates as $sday=>$statuses_arr){
		    			if(in_array($sday,$sapv_invoiced_period_days[$pipid])){

		    				$days2verordnet_highest[$pipid][$sday] = max($statuses_arr);
			    			
			    			if($days2verordnet_highest[$pipid][$sday] == "4"){
			    				$days2sapv_type[$pipid][$sday] = "vv";
			    			}
			    			elseif($days2verordnet_highest[$pipid][$sday] == "3"){
			    				$days2sapv_type[$pipid][$sday] = "tv";
			    			}
			    			elseif($days2verordnet_highest[$pipid][$sday] == "2"){
			    				if(in_array("1",$statuses_arr)){
				    				$days2sapv_type[$pipid][$sday] = "beko";
			    				}
			    			}
			    			elseif($days2verordnet_highest[$pipid][$sday] == "1"){
			    				$days2sapv_type[$pipid][$sday] = "be";
			    			}
		    			}
		    		}
		    	}

		    	//get shortcuts and saved pricelist or default pricelist
		    	$shortcuts = Pms_CommonData::get_prices_shortcuts();
		    	$master_price_list = PriceList::get_period_dta_price_list($selected_period['start'], $selected_period['end']); 

		    	
		    	// location mapping 
		    	$location_type_match = Pms_CommonData::get_rp_price_mapping();

		    	
		    	//get patient locations  
		    	$pat_locations = PatientLocation::get_multiple_period_locations($invoices_patients, $selected_period);
		    	$pat_days2loctype = array();
		    	foreach($pat_locations as $k_pat => $v_pat)
		    	{
		    		if($v_pat['discharge_location'] == "0")
		    		{
			    		foreach($v_pat['all_days'] as $k_day => $v_day)
			    		{
			    			//TODO-3366 Carmen 03.11.2020
    			    			if($v_pat['master_details']['location_type'] == "3" || $v_pat['master_details']['location_type'] == "4"){
    				    			$pat_days2loctype[$v_pat['ipid']][$v_day][] = "3";
    			    			} else{
    			    			    /* if($v_pat['master_details']['location_type'] == 1 && in_array(date("d.m.Y",strtotime($v_day)),$patient_days[$v_pat['ipid']]['hospital']['real_days_cs'])){
        				    			$pat_days2loctype[$v_pat['ipid']][$v_day] = "";
    			    			    } */
    			    			    //else{
        				    			$pat_days2loctype[$v_pat['ipid']][$v_day][] = $v_pat['master_details']['location_type'];
    			    			    //}
    			    			}
    			    			//--
			    		    
			    		}
		    		}
		    	}
		    	
				//TODO-3366 Carmen 03.11.2020
				foreach($pat_days2loctype as $kipid => &$pat_days2loc)
				{
					foreach($pat_days2loc  as $loc_day => $day_loc_types ){
						 
						$del_val = "1";
						if (  ! in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$kipid]['hospital']['real_days_cs']) && ($key = array_search($del_val, $day_loc_types)) !== false) {
							unset($pat_days2loctype[$kipid][$loc_day][$key]);
						}
						 
						$del_val = "2";
						if (  ! in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$kipid]['hospiz']['real_days_cs']) && ($key = array_search($del_val, $day_loc_types)) !== false) {
							unset($pat_days2loctype[$kipid][$loc_day][$key]);
						}
					}
					
					foreach($pat_days2loc as $loc_day => $day_loc_types){
						if (in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$kipid]['hospital']['real_days_cs']) ) {
							$pat_days2loctype[$kipid][$loc_day] = '';
						}
						elseif (in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$kipid]['hospiz']['real_days_cs']) ) {
							$pat_days2loctype[$kipid][$loc_day] = '2';
						} else{
							$pat_days2loctype[$kipid][$loc_day] = end($day_loc_types);
						}
						 
					}
				}
				//--
				
		    	if($_REQUEST['location'] =="1")
		    	{
		    	    echo "<pre/>";
		    		var_Dump(" pat_days2loctype");
		    		print_r($pat_days2loctype);
		    		
		    		var_Dump(" days2sapv_type");
		    		print_r($days2sapv_type);
		    		print_r($patient_days);
		    		var_Dump(" LOCATIONS");
		    		print_r($pat_locations);
		    		exit;
		    	}
		    	
		    	
		    	
		    	//get patients admissions with times
		    	$patient_admissions = PatientReadmission::get_patient_admissions($invoices_patients);
		    	
		    	//get_patients_completed_rpassessment
		    	$patients_rp_asses = Rpassessment::get_patients_completed_rpassessment($invoices_patients,$sapv_invoiced_period_days);
		    	
		    	//get used form types
		    	$set_one = FormTypes::get_form_types($clientid, '1');
		    	foreach($set_one as $k_set_one => $v_set_one)
		    	{
		    		$set_one_ids[] = $v_set_one['id'];
		    	}
		    	
		    	// Get doctor and nurse details
		    	$master_groups_first = array('4', '5');
		    	$client_user_groups_first = Usergroup::getUserGroups($master_groups_first);
		    	
		    	foreach($client_user_groups_first as $k_group_f => $v_group_f)
		    	{
		    		$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
		    	}
		    	
		    	$client_users = User::getClientsUsers($clientid);
		    	
		    	$nurse_users = array();
		    	$doctor_users = array();
		    	foreach($client_users as $k_cuser_det => $v_cuser_det)
		    	{
		    		$master_user_details[$v_cuser_det['id']] = $v_cuser_det;
		    		if(in_array($v_cuser_det['groupid'], $master2client['5']))
		    		{
		    			$nurse_users[] = $v_cuser_det['id'];
		    		}
		    		else if(in_array($v_cuser_det['groupid'], $master2client['4']))
		    		{
		    			$doctor_users[] = $v_cuser_det['id'];
		    		}
		    	}
		    	
		    	// discharge details
		    	$discharge_dead_methods = DischargeMethod::get_client_discharge_method($clientid,true );
		    	$patients_discharge_details = PatientDischarge::get_patients_discharge($invoices_patients);
		    	foreach($patients_discharge_details as $k=>$dis_data){
		    	
		    		if(in_array($dis_data['discharge_method'], $discharge_dead_methods))
		    		{
		    			$discharge_dead_date[$dis_data['ipid']] = date('Y-m-d H:i', strtotime($dis_data['discharge_date']));
		    		}
		    	}
		    	// 21.12.2016
		    	
		    	
		    	//get contact forms START
		    	//get deleted cf from patient course
		    	$deleted_cf = Doctrine_Query::create()
		    	->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		    	->from('PatientCourse')
		    	->where('wrong=1')
		    	->andWhereIn("ipid", $invoices_patients)//TODO-3761 Ancuta 19.01.2021  
		    	->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
		    	->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'")
				->andWhere('source_ipid = ""');
		    	$deleted_cf_array = $deleted_cf->fetchArray();
		    	
		    	$excluded_cf_ids[] = '99999999999';
		    	foreach($deleted_cf_array as $k_dcf => $v_dcf)
		    	{
		    		$excluded_cf_ids[] = $v_dcf['recordid'];
		    	}
		    	
		    	//get cf in period - deleted cf
		    	$p_contactforms = ContactForms::get_multiple_contact_form_period($invoices_patients,false, $excluded_cf_ids,'begin_date_h');
		    	
		    	
		    	
		    	
		    	if($_REQUEST['dbgz'])
		    	{
		    		print_r($p_contactforms);
		    	}
		    	
		    	foreach($p_contactforms as $k_cf_ipid => $all_contact_forms)
		    	{
			    	foreach($all_contact_forms as $k_cf => $v_cf)
			    	{
			    		
			    		//visit date formated
			    		$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
			    			
			    		//switch shortcut_type based on patient location for *visit* date
			    		$vday_matched_loc_price_type = $pat_days2loctype[$v_cf['ipid']][$visit_date];
			    		$vday_matched_sapv_price_type = $days2sapv_type[$v_cf['ipid']][$visit_date];

			    		//switch shortcut doctor/nurse
			    		$shortcut_switch = false;
			    		if(in_array($v_cf['create_user'], $doctor_users))
			    		{
			    			$shortcut_switch = 'doc';
			    		}
			    		else if(in_array($v_cf['create_user'], $nurse_users))
			    		{
			    			$shortcut_switch = 'nur';
			    		}
			    		//create products (doc||nurse)
			    		if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
			    		{
							if(!empty($discharge_dead_date[$v_cf['ipid']])) {
								if(strtotime(date("Y-m-d H:i",strtotime($v_cf['start_date']))) <=  strtotime($discharge_dead_date[$v_cf['ipid']])) {
				    				$contact_forms2date[$v_cf['ipid']][date('Y-m-d', strtotime($v_cf['billable_date']))][] = $v_cf;
								} 
							} else {
		    					$contact_forms2date[$v_cf['ipid']][date('Y-m-d', strtotime($v_cf['billable_date']))][] = $v_cf;
							}
			    		}
			    	}
		    	}
		    	//RPRL10315  LOCAL TODO-2177
		    	//get form saved data
		    	$rp_saved_data = RpControl::get_rp_multiple_controlsheet($invoices_patients,$sapv_invoiced_period_days);
		    	
		    	// daca este only be
		    	// daca inainte am avut only be
		    	// admission date
		    	if($_REQUEST['dbgz'])
		    	{
		    		 
		    		print_r($rp_saved_data);
		    		print_r("\npatientsz");
		    		print_r($invoices_patients);
		    		print_r("\n sapv");
		    		print_r($sapv_on_invoice);
		    	}
		    	
		    	foreach($patient2invoices as $invoice_id => $patient)
		    	{
		    		
			    	$bill_assessment[$invoice_id][$patient] = 1;
			    	$bill_secondary_assessment[$invoice_id][$patient] = 0;
			    	if(isset($only_be_before[$invoice_id][$patient]) && !empty($only_be_before[$invoice_id][$patient])){
			    		$admission_days[$patient] = $patient_days[$patient]['admission_days'];
			    			
			    		$last_only_be[$patient] = end($only_be_before[$invoice_id][$patient]);
			    		$last_admission_date[$patient]  = end($admission_days[$patient]);
			    	
			    		if(strtotime($last_only_be[$patient]) < strtotime($last_admission_date[$patient])){
			    			$from_sapv_be2patient_admision[$patient] = $patientmaster->getDaysInBetween($last_only_be[$patient], $last_admission_date[$patient]);
			    			if(count($from_sapv_be2patient_admision[$patient]) < 28 ){
			    				// if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
			    				$bill_assessment[$invoice_id][$patient]= 0;
			    				$bill_secondary_assessment[$invoice_id][$patient] = 0;
			    	
			    			} else {
			    				//if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed
			    				$bill_assessment[$invoice_id][$patient] = 0;
			    				$bill_secondary_assessment[$invoice_id][$patient] = 1;
			    			}
			    		}
			    	}
			    	 
			    	if($rp_saved_data && !empty($rp_saved_data[$patient])) // in period
			    	{
			    		//reconstruct array
			    		foreach($rp_saved_data[$patient] as $k_shortcut => $v_sv_data)
			    		{
			    			 
			    			foreach($v_sv_data as $k_date => $v_qty)
			    			{
			    				if(in_array($k_date,$invoice_period_days[$invoice_id]))
				    			{
				    				 
				    				$day_location_status = "";
				    				$day_sapv_status = "";
				    				 
				    				$day_location_status = $pat_days2loctype[$patient][$k_date];
				    				$day_sapv_status = $days2sapv_type[$patient][$k_date];
				    				 
				    				
				    				
				    				
				    				
				    				if($master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'] != '0.00')
				    				{

				    					$qty = $v_qty['p_home'] + $v_qty['p_nurse'] + $v_qty['p_hospiz'];
				    					
				    					if($qty != 0){
				    						
				    						$qty_per_day[$k_shortcut][$k_date] = $qty;

				    						
				    						if($k_shortcut == "rp_eb_1"){ // no contact from needed
				    							
				    							for($rp_eb_1 = 1; $rp_eb_1<= $qty; $rp_eb_1++){
				    								
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['qty'] = 1;
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['start'] = $k_date.' 10:00:00';
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_1]['end'] = $k_date.' 10:30:00';
				    							}
				    						}
				    						elseif($k_shortcut == "rp_eb_2"){ // no contact from needed
				    							
				    							for($rp_eb_2 = 1; $rp_eb_2<= $qty; $rp_eb_2++){
				    								
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['qty'] = 1;
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['start'] = $k_date.' 10:00:00';
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_2]['end'] = $k_date.' 10:30:00';
				    							}
				    						}
				    						elseif($k_shortcut == "rp_eb_3"){ // no contact from needed
				    							
				    							for($rp_eb_3 = 1; $rp_eb_3<= $qty; $rp_eb_3++){
				    								
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['qty'] = 1;
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
													$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['start'] = $k_date.' 00:00:00';
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_eb_3]['end'] =$k_date.' 23:59:00';
				    							}
				    							
				    						}
				    						elseif($k_shortcut == "rp_pat_dead"){ // no contact from needed
				    							
				    							for($rp_pat_dead = 1; $rp_pat_dead<= $qty; $rp_pat_dead++){
				    								
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['qty'] = 1;
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
					    							$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['start'] = $k_date.' 10:00:00';
				    								$saved_data_arr[$invoice_id][$patient][$k_shortcut][$k_date.$rp_pat_dead]['end'] = $k_date.' 10:30:00';
				    							}
				    						}
				    						elseif(!in_array($k_shortcut, array('rp_eb_1', 'rp_eb_2', 'rp_eb_3','rp_pat_dead'))){
				    			    	
				    							$qty_sh_date = 0 ;
				    							$qty_sh_date = $v_qty['p_home'] + $v_qty['p_nurse'] + $v_qty['p_hospiz'];
				    							
				    							if($k_shortcut == "rp_doc_1")
				    							{
													$rp_doc_1[$k_date]= 0;
													
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
					    								if(!empty($discharge_dead_date[$v_cf['ipid']])) {
					    								
					    									$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
					    									$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
					    										
					    									$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    									$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    										
					    									if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
					    									{
					    										$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
					    									}
					    								}
				    								
					    								if(in_array($v_cf['create_user'], $doctor_users) && $rp_doc_1[$k_date] <= $qty_sh_date)
					    								{
					    								
						    								if($v_cf['visit_duration'] >= '0')
						    								{
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['source'][$v_cf['id']] = $v_cf['id'] ;
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['start'] = $v_cf['start_date'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['end'] =$v_cf['end_date'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['qty'] = 1;
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['total'] += (1* $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$v_cf['id']]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
						    										
						    									$rp_doc_1[$k_date]++;
						    								}
					    								}
				    								}
				    								
				    								if($rp_doc_1[$k_date] < $qty_sh_date ){
				    										
				    									$remaining[$k_date] = $qty_sh_date - $rp_doc_1[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    								
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM" ;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_1'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_doc_2")
				    							{
				    								$rp_doc_2[$k_date] = 0;
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
				    										
				    									if(in_array($v_cf['create_user'], $doctor_users) && $rp_doc_2[$k_date] <= $qty_sh_date)
				    									{
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['source'][$v_cf['id']] = $v_cf['id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['start'] = $v_cf['start_date'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['end'] =$v_cf['end_date'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$v_cf['id']]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    										$rp_doc_2[$k_date]++;
				    									}
				    								}
				    							
				    								if($rp_doc_2[$k_date] < $qty_sh_date ){
				    										
				    									$remaining[$k_date] = $qty_sh_date - $rp_doc_2[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ )
				    									{
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_2'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_doc_3")
				    							{
													$rp_doc_3[$k_date]= 0;
													
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
					    								if(!empty($discharge_dead_date[$v_cf['ipid']])) {
					    								
					    									$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
					    									$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
					    										
					    									$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    									$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    										
					    									if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
					    									{
					    										$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
					    									}
					    								}
				    								
					    								if(in_array($v_cf['create_user'], $doctor_users) && $rp_doc_3[$k_date] <= $qty_sh_date)
					    								{
					    								
						    								if($v_cf['visit_duration'] > '45')
						    								{
						    									$multiplier[$v_cf['id']] = 0 ;
						    									$multiplier[$v_cf['id']] = ceil(($v_cf['visit_duration'] - 45) / 15);
						    									
						    									for($qsh_m = 0 ; $qsh_m<$multiplier[$v_cf['id']]; $qsh_m++){
						    										
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['source'][$v_cf['id']] = $v_cf ;
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['start'] = $v_cf['start_date'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['end'] =$v_cf['end_date'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['qty'] = 1;
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['total'] += (1* $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
							    										
							    									$rp_doc_3[$k_date]++;
						    									}
						    								}
					    								}
				    								}
				    								
				    								if($rp_doc_3[$k_date] < $qty_sh_date ){
				    										
				    									$remaining[$k_date] = $qty_sh_date - $rp_doc_3[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    								
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM" ;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_3'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_doc_4")
				    							{
				    								$rp_doc_4[$k_date]= 0;
				    									
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
				    									if(!empty($discharge_dead_date[$v_cf['ipid']])) {
				    										 
				    										$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
				    										$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
				    											
				    										$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
				    										$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
				    											
				    										if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
				    										{
				    											$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
				    										}
				    									}
				    							
				    									if(in_array($v_cf['create_user'], $doctor_users) && $rp_doc_4[$k_date] <= $qty_sh_date)
				    									{
				    										 
				    										if($v_cf['visit_duration'] < '20')
				    										{
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['source'][$v_cf['id']] = $v_cf['id'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['start'] = $v_cf['start_date'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['end'] =$v_cf['end_date'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['qty'] = 1;
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['total'] += (1* $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$v_cf['id']]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    							
				    											$rp_doc_4[$k_date]++;
				    										}
				    									}
				    								}
				    							
				    								if($rp_doc_4[$k_date] < $qty_sh_date ){
				    							
				    									$remaining[$k_date] = $qty_sh_date - $rp_doc_4[$k_date];
				    									for($qty_sh_date_remaining = 0; $qty_sh_date_remaining < $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    							
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_doc_4'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_nur_1")
				    							{
													$rp_nur_1[$k_date]= 0;
													
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
					    								if(!empty($discharge_dead_date[$v_cf['ipid']])) {
					    								
					    									$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
					    									$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
					    										
					    									$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    									$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    										
					    									if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
					    									{
					    										$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
					    									}
					    								}
				    								
					    								if(in_array($v_cf['create_user'], $nurse_users) && $rp_nur_1[$k_date] <= $qty_sh_date)
					    								{
					    								
						    								if($v_cf['visit_duration'] >= '0')
						    								{
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['source'][$v_cf['id']] = $v_cf['id'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['start'] = $v_cf['start_date'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['end'] =$v_cf['end_date'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['qty'] = 1;
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['total'] += (1* $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
						    									$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$v_cf['id']]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
						    										
						    									$rp_nur_1[$k_date]++;
						    								}
					    								}
				    								}
				    								
				    								if($rp_nur_1[$k_date] < $qty_sh_date ){
				    										
				    									$remaining[$k_date] = $qty_sh_date - $rp_nur_1[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    								
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_1'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_nur_2")
				    							{
				    								$rp_nur_2[$k_date] = 0;
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
				    										
				    									if(in_array($v_cf['create_user'], $nurse_users) && $rp_nur_2[$k_date] <= $qty_sh_date)
				    									{
    														$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['source'][$v_cf['id']] = $v_cf['id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['start'] = $v_cf['start_date'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['end'] =$v_cf['end_date'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$v_cf['id']]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    										$rp_nur_2[$k_date]++;
				    									}
				    								}
				    							
				    								if($rp_nur_2[$k_date] < $qty_sh_date ){
				    										
				    									$remaining[$k_date] = $qty_sh_date - $rp_nur_2[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    							
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_2'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_nur_3")
				    							{
													$rp_nur_3[$k_date]= 0;
													
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
					    								if(!empty($discharge_dead_date[$v_cf['ipid']])) {
					    								
					    									$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
					    									$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
					    										
					    									$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    									$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
					    										
					    									if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
					    									{
					    										$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
					    									}
					    								}
				    								
					    								if(in_array($v_cf['create_user'], $nurse_users) && $rp_nur_3[$k_date] <= $qty_sh_date)
					    								{
					    								
						    								if($v_cf['visit_duration'] > '45')
						    								{
						    									$multiplier[$v_cf['id']] = 0 ;
						    									$multiplier[$v_cf['id']] = ceil(($v_cf['visit_duration'] - 45) / 15);
						    									
						    									// add multiplier
						    									for($qsh_m = 0 ; $qsh_m<$multiplier[$v_cf['id']]; $qsh_m++){
						    										
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['source'][$v_cf['id']] = $v_cf['id'] ;
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['start'] = $v_cf['start_date'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['end'] =$v_cf['end_date'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['qty'] = 1;
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['total'] += (1* $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
							    									$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$v_cf['id'].$qsh_m]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
							    										
							    									$rp_nur_3[$k_date]++;
						    									}
						    								}
					    								}
				    								}
				    								
				    								if($rp_nur_3[$k_date] < $qty_sh_date ){
				    										
				    									$remaining[$k_date] = $qty_sh_date - $rp_nur_3[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    								
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['source']  = "CUSTOM" ;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_3'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							elseif($k_shortcut == "rp_nur_4")
				    							{
				    								$rp_nur_4[$k_date]= 0;
				    									
				    								foreach($contact_forms2date[$patient][$k_date] as $kk=>$v_cf){
				    									if(!empty($discharge_dead_date[$v_cf['ipid']])) {
				    										 
				    										$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
				    										$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
				    											
				    										$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
				    										$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
				    											
				    										if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
				    										{
				    											$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
				    										}
				    									}
				    							
				    									if(in_array($v_cf['create_user'], $nurse_users) && $rp_nur_4[$k_date] <= $qty_sh_date)
				    									{
				    										 
				    										if($v_cf['visit_duration'] < '20')
				    										{
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['source'][$v_cf['id']] = $v_cf[['id']] ;
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['start'] = $v_cf['start_date'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['end'] =$v_cf['end_date'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['qty'] = 1;
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['total'] += (1* $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    											$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$v_cf['id']]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    							
				    											$rp_nur_4[$k_date]++;
				    										}
				    									}
				    								}
				    							
				    								if($rp_nur_4[$k_date] < $qty_sh_date ){
				    							
				    									$remaining[$k_date] = $qty_sh_date - $rp_nur_4[$k_date];
				    									for($qty_sh_date_remaining = 1; $qty_sh_date_remaining <= $remaining[$k_date]; $qty_sh_date_remaining++ ){
				    							
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['source'] = "CUSTOM" ;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['start'] = $k_date." 10:00:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['end'] = $k_date." 10:30:00";
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['qty'] = 1;
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['dta_price'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['total'] += (1 * $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_price']);
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['dta_id'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_id'];
				    										$saved_data_arr[$invoice_id][$patient]['rp_nur_4'][$k_date.' - '.$qty_sh_date_remaining]['dta_name'] = $master_price_list[$k_date][0][$k_shortcut][$day_location_status][$day_sapv_status]['dta_name'];
				    									}
				    								}
				    							}
				    							else
				    							{
				    							}
				    						}
				    					}
				    				}
				    			}
			    			}
			    		}

			    		$products[$invoice_id][$patient] = $saved_data_arr[$invoice_id][$patient];
			    	}
		    		else
			    	{
						// #############
						// NO SAVED DATA 
						// #############
						
		    			if($sapv_on_invoice[$patient]['only_be'] == "1"){
		    				//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
		    				if($patients_rp_asses && !empty($patients_rp_asses[$patient])){
		    					// only one assessment
		    					$v_assessment = $patients_rp_asses[$patient][0];

		    					$location_matched_price = $location_type_match[$pat_days2loctype[$patient][$v_assessment['completed_date']]];
	    						$vday_matched_loc_price_type = $pat_days2loctype[$patient][$v_assessment['completed_date']];
	    						$vday_matched_sapv_price_type = $days2sapv_type[$patient][$v_assessment['completed_date']];
	    						 
	    						
	    						if( ! empty($master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type])
	    							&& in_array(date("Y-m-d",strtotime($v_assessment['completed_date']),$invoice_period_days[$invoice_id]))
	    						)
	    						{
	    							//no saved data, load system data instead
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['qty'] += 1;
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['dta_price'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_price'];
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['dta_id'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_id'];
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['dta_name'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_name'];
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['start'] = $v_assessment['completed_date'].' 00:00:00';
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['end'] = $v_assessment['completed_date'].' 23:59:59';
	    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['source'][$v_assessment['completed_date']] += '1';
	    						}
		    				}
		    				

		    				//DOCTOR and NURSE VISITS - all
		    				foreach($p_contactforms[$patient] as $k_cf => $v_cf)
		    				{
		    					//visit date formated
		    					$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
		    					 
		    					if(in_array($visit_date,$invoice_period_days[$invoice_id])){
			    					
			    					//switch shortcut_type based on patient location for *visit* date
			    					$cf_day_matched_loc_price_type = $pat_days2loctype[$patient][$visit_date];
			    					$cf_day_matched_sapv_price_type = $days2sapv_type[$patient][$visit_date];
			    				
			    					//switch shortcut doctor/nurse
			    					$shortcut_switch = false;
			    					if(in_array($v_cf['create_user'], $doctor_users))
			    					{
			    						$shortcut_switch = 'doc';
			    					}
			    					else if(in_array($v_cf['create_user'], $nurse_users))
			    					{
			    						$shortcut_switch = 'nur';
			    					}
			    					 
			    					//create products (doc||nurse)
			    					if(strlen($cf_day_matched_loc_price_type) > 0 && strlen($cf_day_matched_sapv_price_type) > 0   && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids)
			    					)
			    					{
			    						//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
			    						if(empty($products_cnt[$patient][$shortcut_switch]['billed_visit'])){
			    						
			    							$products_cnt[$patient][$shortcut_switch]['billed_visit'][] = $v_cf['id'];
			    							
				    						if($master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] != '0.00')
				    						{
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['qty'] += 1;
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['dta_price'] = $master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'];
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['dta_id'] = $master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'];
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['dta_name'] = $master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'];
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['start'] = $v_cf['start_date'];
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['end'] = $v_cf['end_date'];
				    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['source'][$v_cf['id']] = $v_cf ;
				    				
				    							$shortcut = '';
				    							$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = '';
				    							
				    						}
			    						}
	
	
	
			    						if(!empty($discharge_dead_date[$v_cf['ipid']])) {
			    						
			    							$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
			    							$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
			    								
			    							$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
			    							$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
			    								
			    							if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
			    							{
			    								$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
			    							}
			    						}
			    						
			    						
				    					//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
			    						if($v_cf['visit_duration'] >= '0')
			    						{
			    							if(in_array($v_cf['id'],$products_cnt[$patient][$shortcut_switch]['billed_visit']))
			    							{
				    							$shortcut = 'rp_' . $shortcut_switch . '_1';
				    											    							 
				    							if($shortcut && $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]  != '0.00')
				    							{
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['qty'] += 1;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_price'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] ;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_id'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'] ;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_name'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'] ;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['start'] = $v_cf['start_date'];
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['end'] = $v_cf['end_date'];
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['source'][$v_cf['id']] = $v_cf ;
				    							}
			    							}
			    						}
			    						 
				    					//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 46 this product is added. (doctor) (rp_doc_3||rp_nur_3)
			    						if($v_cf['visit_duration'] > '45')
			    						{
			    							if(in_array($v_cf['id'],$products_cnt[$patient][$shortcut_switch]['billed_visit'])){
			    								// calculate multiplier of 15 minutes after 60 min (round up)
			    								// ISPC-2006 :: From 60 was changed to 45
			    								// calculate multiplier of 15 minutes after 45 min (round up)
				    							$shortcut = 'rp_' . $shortcut_switch . '_3';
				    							$multiplier = "" ;
				    							$multiplier[$v_cf['id']] = ceil(($v_cf['visit_duration'] - 45) / 15);
				    		 
				    							
				    							$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = $multiplier[$v_cf['id']]; //multiplier value
				    							 
				    							if($shortcut && $qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] && $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] != '0.00')
				    							{
				    								for($iqty =1; $iqty <= $qty[$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]; $iqty++ ){
				    									
// 					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['qty'] += $qty[$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type];
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['qty'] += 1;
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['dta_price'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] ;
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['dta_id'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'] ;
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['dta_name'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'];
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['start'] = $v_cf['start_date'];
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['end'] = $v_cf['end_date'];
					    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['source'][$v_cf['id']] = $v_cf ;
				    								}
				    								
				    				
				    							}
			    							}
			    						}
			    						 
			    						//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
			    						if($v_cf['visit_duration'] < '20')
			    						{
			    							if(in_array($v_cf['id'],$products_cnt[$patient][$shortcut_switch]['billed_visit'])){
			    								
				    							$shortcut = 'rp_' . $shortcut_switch . '_4';
				    							//$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = 1;
				    							 
				    							if($shortcut && $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] != '0.00')
				    							{
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['qty'] += 1;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_price'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] ;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_id'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'] ;
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_name'] =  $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'];
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['start'] = $v_cf['start_date'];
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['end'] = $v_cf['end_date'];
				    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['source'][$v_cf['id']] = $v_cf ;
				    							}
			    							}
		    							}
			    					}
			    				}
		    				}
		    				
		    			}
			    		else
			    		{
			    			// ############
			    			// NOT ONLY BE
			    			// ############	
			    			
			    				
			    			//GATHER INVOICE ITEMS START
			    			//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
			    			
			    				
		    				if($_REQUEST['dbgz'])
		    				{
		    					 
		    					var_Dump($patients_rp_asses);
		    				}	
			    				
			    			if($patients_rp_asses && !empty($patients_rp_asses[$patient])){
			    			
			    				if($bill_assessment[$invoice_id][$patient] == "1"){
				    				foreach($patients_rp_asses[$patient] as $k_assessment => $v_assessment)
				    				{
				    					$location_matched_price = $location_type_match[$pat_days2loctype[$patient][$v_assessment['completed_date']]];
				    					 
				    					$vday_matched_loc_price_type = $pat_days2loctype[$patient][$v_assessment['completed_date']];
				    					$vday_matched_sapv_price_type = $days2sapv_type[$patient][$v_assessment['completed_date']];
				    					
				    					
				    					//if(strlen($master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]) > 0) // THIS RETURNS FALSE ~ 
				    					if( ! empty($master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type])
				    						&& in_array(date("Y-m-d",strtotime($v_assessment['completed_date'])),$invoice_period_days[$invoice_id])
				    					)
				    					{
				    						//no saved data, load system data instead
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['qty'] += '1';
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['dta_price'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_price'];
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['dta_id'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_id'];
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['dta_name'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_1'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_name'];
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['start'] = $v_assessment['completed_date'].' 00:00:00';
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['end'] = $v_assessment['completed_date'].' 23:59:59';
			    							$products[$invoice_id][$patient]['rp_eb_1'][$v_assessment['completed_date']]['source'][$v_assessment['completed_date']] += '1';
				    					}
				    				}
			    				}
			    				else
			    				{
			    					if($bill_secondary_assessment[$invoice_id][$patient] == "1"){
			    						
			    						//Ebene 1 (reduziertes Assessment) - Not used yet (saved data for this shortcut as is not calculated by system)
			    						foreach($patients_rp_asses[$patient] as $k_assessment => $v_assessment)
			    						{
			    							$location_matched_price = $location_type_match[$pat_days2loctype[$patient][$v_assessment['completed_date']]];
			    							 
			    							$vday_matched_loc_price_type = $pat_days2loctype[$patient][$v_assessment['completed_date']];
			    							$vday_matched_sapv_price_type = $days2sapv_type[$patient][$v_assessment['completed_date']];
			    						
			    						
			    							//if(strlen($master_price_list[$v_assessment['completed_date']][0]['rp_eb_2'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]) > 0) // THIS RETURNS FALSE
			    							if( ! empty($master_price_list[$v_assessment['completed_date']][0]['rp_eb_2'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type])
			    								&& in_array(date("Y-m-d",strtotime($v_assessment['completed_date'])),$invoice_period_days[$invoice_id])
			    							)
			    							{
			    								//no saved data, load system data instead
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['qty'] += '1';
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['dta_price'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_2'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_price'];
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['dta_id'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_2'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_id'];
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['dta_name'] = $master_price_list[$v_assessment['completed_date']][0]['rp_eb_2'][$vday_matched_loc_price_type][$vday_matched_sapv_price_type]['dta_name'];
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['start'] = $v_assessment['completed_date'].' 00:00:00';
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['end'] = $v_assessment['completed_date'].' 23:59:59';
			    								$products[$invoice_id][$patient]['rp_eb_2'][$v_assessment['completed_date']]['source'][$v_assessment['completed_date']] += '1';
			    							}
			    						}
			    					}
			    					
			    					
			    					
			    				}
			    			}
	 
			    			if($_REQUEST['dbgz'])
			    			{
			    				print_r('dapv on period');
			    			}
			    			
			    			
			    			//Ebene 2 - the daily added price when patient is active and has Verordnung
			    			foreach($sapv_invoiced_period_days2invoice[$patient][$invoice_id] as $k_sapv_day => $v_sapv_day)
			    			{
			    				
			    				$day_matched_loc_price_type[$patient][$k_sapv_day] = $pat_days2loctype[$patient][$v_sapv_day];
			    				$day_matched_sapv_price_type[$patient][$k_sapv_day] = $days2sapv_type[$patient][$v_sapv_day];
			    				
			    				$sapvday_loc_matched_price = $location_type_match[$pat_days2loctype[$v_sapv_day]];
	 
			    				if( $master_price_list[$v_sapv_day][0]['rp_eb_3'][$day_matched_loc_price_type[$patient][$k_sapv_day]][$day_matched_sapv_price_type[$patient][$k_sapv_day]]['dta_price'] && $master_price_list[$v_sapv_day][0]['rp_eb_3'][$day_matched_loc_price_type[$patient][$k_sapv_day]][$day_matched_sapv_price_type[$patient][$k_sapv_day]]['dta_price'] != '0.00'
									&& ! array_key_exists($v_sapv_day,$products[$invoice_id][$patient]['rp_eb_3'])	
			    					&& in_array(date("Y-m-d",strtotime($v_sapv_day)),$invoice_period_days[$invoice_id])
			    					)
			    				{
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['qty'] += 1;
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['dta_price'] = $master_price_list[$v_sapv_day][0]['rp_eb_3'][$day_matched_loc_price_type[$patient][$k_sapv_day]][$day_matched_sapv_price_type[$patient][$k_sapv_day]]['dta_price'];
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['dta_id'] = $master_price_list[$v_sapv_day][0]['rp_eb_3'][$day_matched_loc_price_type[$patient][$k_sapv_day]][$day_matched_sapv_price_type[$patient][$k_sapv_day]]['dta_id'];
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['dta_name'] = $master_price_list[$v_sapv_day][0]['rp_eb_3'][$day_matched_loc_price_type[$patient][$k_sapv_day]][$day_matched_sapv_price_type[$patient][$k_sapv_day]]['dta_name'];
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['start'] = $v_sapv_day.' 00:00:00';
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['end'] = $v_sapv_day.' 23:59:59';
		    						$products[$invoice_id][$patient]['rp_eb_3'][$v_sapv_day]['source'][$day_matched_loc_price_type[$patient][$k_sapv_day]][$day_matched_sapv_price_type[$patient][$k_sapv_day]][$v_sapv_day] += '1';
			    				}
			    			}
			    			
			    			//DOCTOR and NURSE VISITS - all
				    		foreach($p_contactforms[$patient] as $k_cf => $v_cf)
			    			{
			    				//visit date formated
			    				$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
			    			
			    				if(in_array($visit_date,$invoice_period_days[$invoice_id])){
				    				
				    				//switch shortcut_type based on patient location for *visit* date
				    				$cf_day_matched_loc_price_type = $pat_days2loctype[$patient][$visit_date];
				    				$cf_day_matched_sapv_price_type = $days2sapv_type[$patient][$visit_date];
				    				
				    				//switch shortcut doctor/nurse
				    				$shortcut_switch = false;
				    				if(in_array($v_cf['create_user'], $doctor_users))
				    				{
				    					$shortcut_switch = 'doc';
				    				}
				    				else if(in_array($v_cf['create_user'], $nurse_users))
				    				{
				    					$shortcut_switch = 'nur';
				    				}
				    			
				    				//create products (doc||nurse)
				    				if(strlen($cf_day_matched_loc_price_type) > 0 && strlen($cf_day_matched_sapv_price_type) > 0   && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids)
				    				)
				    				{
				    					//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
				    					if($master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] != '0.00')
				    					{
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['qty'] += 1;
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['dta_price'] = $master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'];
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['dta_id'] = $master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'];
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['dta_name'] =  $master_price_list[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'];
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['start'] = $v_cf['start_date'];
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['end'] = $v_cf['end_date'];
			    							
			    							$products[$invoice_id][$patient]['rp_' . $shortcut_switch . '_2'][$visit_date.$v_cf['id']]['source'][$v_cf['id']] = $v_cf ;
			    			
				    						$shortcut = '';
				    						$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = '';
				    					}
				    			
				    					
		
				    					if(!empty($discharge_dead_date[$v_cf['ipid']])) {
				    					
				    						$a1start = strtotime(date('Y-m-d H:i', strtotime($v_cf['start_date'])));
				    						$a1end = strtotime(date('Y-m-d H:i', strtotime($v_cf['end_date'])));
				    							
				    						$p1start = strtotime($discharge_dead_date[$v_cf['ipid']]);
				    						$p1end = strtotime($discharge_dead_date[$v_cf['ipid']]);
				    							
				    						if(Pms_CommonData::isintersected($a1start, $a1end, $p1start, $p1end))
				    						{
				    							$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$discharge_dead_date[$v_cf['ipid']]);
				    						}
				    					}
				    					
				    					
				    					//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 45 (rp_doc_1||rp_nur_1)
				    					if($v_cf['visit_duration'] >= '0')
				    					{
				    						$shortcut = 'rp_' . $shortcut_switch . '_1';
				    						//$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = 1;
				    			
				    						if($shortcut && $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price']  != '0.00')
				    						{
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['qty'] += 1;
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_price'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] ;
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_id'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'] ;
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_name'] =  $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'] ;
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['start'] = $v_cf['start_date'];
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['end'] = $v_cf['end_date'];
			    								
			    								$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['source'][$v_cf['id']] = $v_cf ;
				    						}
				    					}
				    			
				    					//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 46 this product is added. (doctor) (rp_doc_3||rp_nur_3)
				    					if($v_cf['visit_duration'] > '45')
				    					{
											// calculate multiplier of 15 minutes after 60 min (round up)
											// ISPC-2006 :: From 60 was changed to 45
											// calculate multiplier of 15 minutes after 45 min (round up)
											
				    						$shortcut = 'rp_' . $shortcut_switch . '_3';
				    						$multiplier[$v_cf['id']] = ceil(($v_cf['visit_duration'] - 45) / 15);
				    						$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = $multiplier[$v_cf['id']]; //multiplier value
	
				    						if(
				    						 $shortcut 
				    						 && $qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] 
				    						 && $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] != '0.00'
				    						)
				    						{
				    							
				    							
				    							for($iqty =1; $iqty <= $qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]; $iqty++ ){
				    									
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['qty'] += 1;
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['dta_price'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] ;
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['dta_id'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'] ;
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['dta_name'] =  $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'];
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['start'] = $v_cf['start_date'];
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['end'] = $v_cf['end_date'];
													$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id'].$iqty]['source'][$v_cf['id']] = $v_cf ;
				    							}
												
												
				    								
				    						}
				    					}
				    			
				    					//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
				    					if($v_cf['visit_duration'] < '20')
				    					{
				    						$shortcut = 'rp_' . $shortcut_switch . '_4';
				    						//$qty[$invoice_id][$patient][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type] = 1;
				    			
				    						if($shortcut && $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] != '0.00')
				    						{
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['qty'] += 1;
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_price'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_price'] ;
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_id'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_id'] ;
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['dta_name'] = $master_price_list[$visit_date][0][$shortcut][$cf_day_matched_loc_price_type][$cf_day_matched_sapv_price_type]['dta_name'];
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['start'] = $v_cf['start_date'];
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['end'] = $v_cf['end_date'];
				    							$products[$invoice_id][$patient][$shortcut][$visit_date.$v_cf['id']]['source'][$v_cf['id']] = $v_cf ;
				    						}
				    					}
				    				}
				    			}
			    			}
			    			//Fallabschluss - patient death coordination. added once (rp_pat_dead)
			    			if(strlen($discharge_dead_date[$patient]) > 0)
			    			{
			    				$shortcut = 'rp_pat_dead';
			    				//visit date formated
			    				$death_date = date('Y-m-d', strtotime($discharge_dead_date[$patient]));
	
			    				$dis_day_matched_loc_price_type = $pat_days2loctype[$patient][$death_date];
			    				$dis_day_matched_sapv_price_type = $days2sapv_type[$patient][$death_date];
			    				//$qty[$invoice_id][$patient]['rp_pat_dead'][$dis_day_matched_loc_price_type][$dis_day_matched_sapv_price_type] = '1';

			    				/* if($_REQUEST['dbgz_print'])
			    				{
			    					print_r("dis_day_matched_loc_price_type");
			    					print_r($dis_day_matched_loc_price_type);
			    					
			    					print_r("<br/>");
			    					
			    					print_r("dis_day_matched_sapv_price_type ");
			    					print_r($dis_day_matched_sapv_price_type);
			    					
			    					print_r("<br/>");
			    					print_r("qtx");
			    					print_r($qty['rp_pat_dead'][$dis_day_matched_loc_price_type][$dis_day_matched_sapv_price_type]);

			    					print_r("<br/>");
			    					print_r("shortcut");
			    					print_r($shortcut);

			    					
			    					
			    					
			    					print_r("<br/>");
			    					print_r("priceeeeeeeeee");
			    					print_r(
			    					$master_price_list[$death_date][0][$shortcut]
			    					);
			    					
			    					print_r("<br/>");
			    					
			    					
			    					
			    				} */
			    				
			    				
			    				if($master_price_list[$death_date][0][$shortcut][$dis_day_matched_loc_price_type][$dis_day_matched_sapv_price_type])
			    				{
		    						$products[$invoice_id][$patient]['rp_pat_dead'][$death_date]['qty'] = 1;
		    						$products[$invoice_id][$patient]['rp_pat_dead'][$death_date]['dta_price'] = $master_price_list[$death_date][0]['rp_pat_dead'][$dis_day_matched_loc_price_type][$dis_day_matched_sapv_price_type]['dta_price'];
		    						$products[$invoice_id][$patient]['rp_pat_dead'][$death_date]['dta_id'] = $master_price_list[$death_date][0]['rp_pat_dead'][$dis_day_matched_loc_price_type][$dis_day_matched_sapv_price_type]['dta_id'];
		    						$products[$invoice_id][$patient]['rp_pat_dead'][$death_date]['dta_name'] = $master_price_list[$death_date][0]['rp_pat_dead'][$dis_day_matched_loc_price_type][$dis_day_matched_sapv_price_type]['dta_name'];
		    						
		    						$products[$invoice_id][$patient]['rp_pat_dead'][$death_date]['start'] = $discharge_dead_date[$patient];
		    						$products[$invoice_id][$patient]['rp_pat_dead'][$death_date]['end'] = $discharge_dead_date[$patient];
			    				}
			    				//GATHER INVOICE ITEMS END
			    			}
			    		}
			    	}
		    	}
		    	
		    	
		    	if($_REQUEST['dbgz_print'])
		    	{
			    	echo "<pre>";
			    	print_R($discharge_dead_date);
			    	print_R($products);
			    	exit;
		    	}
		    	
		    	if($_REQUEST['dbgz'])
		    	{
		    		 
		    		var_dump($products);
		    	}
		    	
		    	foreach($patient2invoices as $invoice_id => $patient)
		    	{
		    		foreach($products[$invoice_id][$patient] as $shortcut_key=>$date_entry)
		    		{
	    				foreach($date_entry as $Ymd=>$date_details)
	    				{
	    					$data[$patient][$invoice_id][$shortcut_key]['actions'][] = $date_details;
	    				}
		    		}
		    		
		    	}
 
		    	foreach($patient2invoices as $invoice_pat => $v_ipid)
		    	{	
		    		$triggered[$v_ipid][$invoice_pat] = $data[$v_ipid][$invoice_pat];
		    		
		    		$triggered_sapv[$invoice_pat][$v_ipid]['sapv_start_date'] = $sapv_on_invoice[$invoice_pat][$v_ipid]['sapv_start'];
		    		$triggered_sapv[$invoice_pat][$v_ipid]['sapv_end_date'] = $sapv_on_invoice[$invoice_pat][$v_ipid]['sapv_end'];
		    		$triggered_sapv[$invoice_pat][$v_ipid]['sapv_create_date'] = $sapv_on_invoice[$invoice_pat][$v_ipid]['create_date'];
		    		$triggered_sapv[$invoice_pat][$v_ipid]['sapv_approved_date'] = $sapv_on_invoice[$invoice_pat][$v_ipid]['approved_date'];
		    		$triggered_sapv[$invoice_pat][$v_ipid]['sapv_approved_nr'] = $sapv_on_invoice[$invoice_pat][$v_ipid]['approved_number'];
		   
		    		$triggered[$v_ipid][$invoice_pat]['sapv'] = $triggered_sapv[$invoice_pat][$v_ipid];
		    	}
		    		
		    	return $triggered;
		    }
		    

		    //#####################################################
		    //#####################################################
		    //#########NEW RLP NILLING ISPC - 2143 ########################
		    //#####################################################
		    //#####################################################
		    
		    
		    public function listdtarlpinvoicesAction()
		    {
		    	$logininfo = new Zend_Session_Namespace('Login_Info');
		    	$userid = $logininfo->userid;
		    	$clientid = $logininfo->clientid;
		    
		    	if($clientid > 0)
		    	{
		    		$where = ' and client=' . $logininfo->clientid;
		    	}
		    	else
		    	{
		    		$where = ' and client=0';
		    	}
		    
		    	$storned_invoices = RlpInvoices::get_storned_invoices($clientid);
		    	$unpaid_status = array("2","5");
		    
		    	//construct months array in which the curent client has bre_invoices completed, not paid
		    	$months_q = Doctrine_Query::create()
		    	->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
		    	->from('RlpInvoices')
		    	->where("isdelete = 0")
		    	->andWhere('completed_date != "0000-00-00 00:00:00"')
		    	->andWhere("storno = 0 " . $where);
		    	if(!empty($storned_invoices)){
			    	$months_q->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	}
		    	$months_q->andWhereIN("status",$unpaid_status) // display only unpaid
		    	->orderBy('DISTINCT DESC');
		    	$months_res = $months_q->fetchArray();
		    
		    	if($months_res)
		    	{
		    		//current month on top
		    		$months_array[date('Y-m', time())] = date('m-Y', time());
		    		foreach($months_res as $k_month => $v_month)
		    		{
		    			$months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
		    		}
		    
		    		$months_array = array_unique($months_array);
		    	}
		    
		    	if(strlen($_REQUEST['search']) > '0')
		    	{
		    		$selected_period['start'] = $_REQUEST['search'] . "-01";
		    		$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
		    	}
		    
		    	$this->view->months_array = $months_array;
		    
		    	if($this->getRequest()->isPost())
		    	{
		    		$post = $_POST;
		    
		    		$dta_data = $this->gather_dta_rlp_data($clientid, $userid, $post);
		    		$this->generate_dta_xml($dta_data);
		    		exit;
		    	}
		    }
		    
		    

		    public function fetchdtarlpinvoiceslistAction()
		    {
		    	$logininfo = new Zend_Session_Namespace('Login_Info');
		    	$hidemagic = Zend_Registry::get('hidemagic');
		    	$userid = $logininfo->userid;
		    	$clientid = $logininfo->clientid;
		    	$user_type = $logininfo->usertype;
		    
		    	$columnarray = array(
		    			"pat" => "epid_num",
		    			"invnr" => "invoice_number",
		    			"invstartdate" => "invoice_start",
		    			"invdate" => "completed_date_sort",
		    			"invtotal" => "invoice_total",
		    			"invkasse" => "company_name", // used in first order of health insurances
		    	);
		    
		    	if($clientid > 0)
		    	{
		    		$where = ' and client=' . $logininfo->clientid;
		    	}
		    	else
		    	{
		    		$where = ' and client=0';
		    	}
		    
		    	$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		    	$this->view->order = $orderarray[$_REQUEST['ord']];
		    	$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		    
		    	$client_users_res = User::getUserByClientid($clientid, 0, true);
		    
		    	foreach($client_users_res as $k_user => $v_user)
		    	{
		    		$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
		    	}
		    
		    	$this->view->client_users = $client_users;
		    
		    	
		    	
		    	//get patients data used in search and list
		    	$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    	$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    	$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    	$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    	$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    	$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
 
		    	$f_patient = Doctrine_Query::create()
		    	->select($sql)
		    	->from('PatientMaster p')
		    	->where("p.isdelete =0")
		    	->leftJoin("p.EpidIpidMapping e")
		    	->andWhere('e.clientid = ' . $clientid);
		    
		    	if($_REQUEST['clm'] == 'pat')
		    	{
		    		$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    	}
		    
		    	$f_patients_res = $f_patient->fetchArray();
		    
		    	foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		    	{
		    		$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		    		$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
		    	}
		    
		    	$this->view->client_patients = $client_patients;
		    
		    	if(strlen($_REQUEST['val']) > '0')
		    	{
		    		$selected_period['start'] = $_REQUEST['val'] . "-01";
		    		$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    	}
		    	else
		    	{
		    		$selected_period['start'] = date('Y-m-01', time());
		    		$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    	}
		    
		    	
		    	
		    	//order by health insurance
		    	if($_REQUEST['clm'] == "invkasse" && ! empty($f_patients_ipids))
		    	{
		    		$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		    
		    		$drop = Doctrine_Query::create()
		    		->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
		    		->from('PatientHealthInsurance')
		    		->whereIn("ipid", $f_patients_ipids)
		    		->orderBy($orderby);
		    		$droparray = $drop->fetchArray();
		    
		    		$f_patients_ipids = array();
		    		foreach($droparray as $k_pat_hi => $v_pat_hi)
		    		{
		    			$f_patients_ipids[] = $v_pat_hi['ipid'];
		    		}
		    	}
		    
		    	
		    
		    	$storned_invoices = RlpInvoices::get_storned_invoices($clientid);
		    	$unpaid_status = array("2","5");
		    
		    	$fdoc = Doctrine_Query::create()
		    	->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
		    	->from('RlpInvoices')
		    	->where("isdelete = 0 " . $where)
		    	->andWhere("storno = '0'")
		    	->andWhere('completed_date != "0000-00-00 00:00:00"');
		    	if(! empty($f_patients_ipids)){
			    	$fdoc->andWhereIn('ipid', $f_patients_ipids);
		    	}
		    	if(!empty($unpaid_status)){
			    	$fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
		    	}
		    	if(!empty($storned_invoices)){
		    		$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	}
		    	$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
		    	if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
		    	{
		    		$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    	}
		    	else
		    	{
		    		//sort by patient sorted ipid order
		    		$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
		    	}
		    
		    	
		    	
		    	//used in pagination of search results
		    	$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
		    	$fdocarray = $fdoc->fetchArray();
		    	$limit = 500;
		    	$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
		    	$fdoc->where("isdelete = 0 " . $where . "");
		    	$fdoc->andWhere("storno = '0'");
		    	$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
		    	if(! empty($f_patients_ipids)){
		    		$fdoc->andWhereIn('ipid', $f_patients_ipids);
		    	}
		    	if(!empty($unpaid_status)){
		    		$fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
		    	}
		    	if(!empty($storned_invoices)){
		    		$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	}
		    	$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
		    	$fdoc->limit($limit);
		    	$fdoc->offset($_REQUEST['pgno'] * $limit);
		    	$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		    
		    	//get ipids for which we need health insurances
		    	$inv_ipids = array();
		    	foreach($fdoclimit as $k_inv => $v_inv)
		    	{
		    		$inv_ipids[] = $v_inv['ipid'];
		    	}
		    
		    

		    	//6. patients health insurance
		    	$healthinsu_array = array();
		    	$healthinsu = array();
		    	
		    	if(!empty($inv_ipids)){
			    	$phelathinsurance = new PatientHealthInsurance();
			    	$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		    	}
		    
		    	
		    	if( ! empty($healthinsu_array)){
		    		
		    		$company_ids = array();
			    	foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			    	{
			    		$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
			    
			    		if($v_healthinsu['companyid'] != '0')
			    		{
			    			$company_ids[] = $v_healthinsu['companyid'];
			    		}
			    	}
			    
			    	$health_insurance_master = array();
			    	if( ! empty($company_ids)){
				    	$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
			    	}
			    
			    	foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			    	{
			    		if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
			    		{
			    			$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
			    
			    			if(strlen($healtharray['name']) > '0')
			    			{
			    				$ins_name = $healtharray['name'];
			    			}
			    			else if(strlen($v_health_insurance[0]['company_name']) > '0')
			    			{
			    				$ins_name = $v_health_insurance[0]['company_name'];
			    			}
			    		}
			    
			    		//health insurance name
			    		$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
			    	}
		    	}
		    	$this->view->healthinsurances = $healthinsu;
		    
		    
		    	
		    	
		    	$this->view->{"style" . $_GET['pgno']} = "active";
		    	if(count($fdoclimit) > '0')
		    	{
		    		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtarlpinvoiceslist.html");
		    		$this->view->templates_grid = $grid->renderGrid();
		    		$this->view->navigation = $grid->dotnavigation("dtarlpinvoicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
		    	}
		    	else
		    	{
		    		//no items found
		    		$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
		    		$this->view->navigation = '';
		    	}
		    
		    	$response['msg'] = "Success";
		    	$response['error'] = "";
		    	$response['callBack'] = "callBack";
		    	$response['callBackParameters'] = array();
		    	$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtarlpinvoiceslist.html');
		    
		    	echo json_encode($response);
		    	exit;
		    }
		    
		    

		    private function gather_dta_rlp_data($clientid, $userid, $post)
		    {
		    	
		    	$patientmaster = new PatientMaster();
		    	
		    	//1. get all selected invoices data
		    	$rlp_invoices = new RlpInvoices();
		    	$rlp_invoices_data = $rlp_invoices->get_multiple_rlp_invoices($post['invoices']['rlp'],false,true);
		    
		    	if($rlp_invoices_data === false){
		    		return array();
		    	}
		    
		    	$invoices_patients = array();
		    	$invoiced_days = array();
		    	foreach($rlp_invoices_data as $k_inv => $v_inv)
		    	{
		    		$invoices_patients[] = $v_inv['ipid'];
		    
		    		$invoice_period_patient['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		    		$invoice_period_patient['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    
		    		$invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		    		$invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    
		    		$patients_invoices_periods[$v_inv['ipid']] = $invoice_period_patient;
		    
		    		$invoice_period_sapv['start'] = date('Y-m-d', strtotime($v_inv['sapv_start']));
		    		$invoice_period_sapv['end'] = date('Y-m-d', strtotime($v_inv['sapv_end']));
		    
		    		
		    		$invoiced_days[$v_inv['ipid']][$v_inv['id']] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		    		
		    		$patients_invoices_sapv_periods[$v_inv['ipid']] = $invoice_period_sapv;
		    
		    		foreach($v_inv['items'] as $sh_item_data=>$items){
		    			foreach($items as $k=>$itm){
			    			$rlp_invoices_data[$k_inv]['items_nosh'][] = $itm;
		    			}
		    		}
		    	}
		    	 
// 		    	dd($invoiced_days); 
		    	
		    	asort($invoice_periods);
		    	$invoice_periods_date = array_values($invoice_periods);
		    	$invoice_period['start'] = $invoice_periods_date[0];
		    	$invoice_period['end'] = end($invoice_periods_date);
		    
		    	//2. get all required client data
		    	$clientdata = Pms_CommonData::getClientData($clientid);
		    	$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
		    	$client_data['client']['team_name'] = $clientdata[0]['team_name'];
		    	$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
		    	$client_data['client']['phone'] = $clientdata[0]['phone'];
		    	$client_data['client']['fax'] = $clientdata[0]['fax'];
		    
		    
		    	//3. get pflegestuffe in current period
		    	$pflege = new PatientMaintainanceStage();
		    	$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		    
		    	foreach($pflege_arr as $k_pflege => $v_pflege)
		    	{
		    		$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
		    	}
		    
		    	foreach($grouped_pflege as $k_gpflege => $v_gpflege)
		    	{
		    		$last_pflege = end($v_gpflege);
		    
		    		if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
		    		{
		    			//$k_gpflege = patient epid
		    			$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
		    		}
		    		else
		    		{
		    			$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
		    		}
		    	}
		    
		    	//4. get all involved patients required data
		    	$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
		    
		    	foreach($patient_details as $k_pat_ipid => $v_pat_details)
		    	{
		    		$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
		    		$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
		    		$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
		    		$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
		    		$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
		    		$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
		    		$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
		    	}
		    
		    	//4.1 get patients readmission details
		    	$conditions['periods'][0]['start'] = '2009-01-01';
		    	$conditions['periods'][0]['end'] = date('Y-m-d');
		    	$conditions['client'] = $clientid;
		    	$conditions['ipids'] = $invoices_patients;
		    	$patient_days = Pms_CommonData::patients_days($conditions);
		    
		    	foreach($patient_days as $k_patd_ipid => $v_pat_details)
		    	{
		    		$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
		    	}
		    
 
		    	//5. patients health insurance
		    	$phelathinsurance = new PatientHealthInsurance();
		    	$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		    
		    	$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
		    	// ispc = M => 1 = Versicherungspflichtige und -berechtigte
		    	// ispc = F => 3 = Familienversicherte
		    	// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
		    	//TODO-3528 Lore 12.11.2020
		    	$modules = new Modules();
		    	$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
		    	if($extra_healthinsurance_statuses){
		    	    $status_int_array += array(
		    	        "00" => "00",          //"Gesamtsumme aller Stati",
		    	        "11" => "11",          //"Mitglieder West",
		    	        "19" => "19",          //"Mitglieder Ost",
		    	        "31" => "31",          //"Angehörige West",
		    	        "39" => "39",          //"Angehörige Ost",
		    	        "51" => "51",          //"Rentner West",
		    	        "59" => "59",          //"Rentner Ost",
		    	        "99" => "99",          //"nicht zuzuordnende Stati",
		    	        "07" => "07",          //"Auslandsabkommen"
		    	    );
		    	}
		    	//.
		    	
		    	foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    	{
		    		$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		    
		    		if($v_healthinsu['companyid'] != '0')
		    		{
		    			$company_ids[] = $v_healthinsu['companyid'];
		    		}
		    	}
		    
		    	if(!empty($company_ids)){
			    	$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		    	}
		    
		    
		    	//get health insurance subdivizions
		    	$symperm = new HealthInsurancePermissions();
		    	$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		    
		    	if($divisions)
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
		    		->from("PatientHealthInsurance2Subdivisions");
		    		if(!empty($company_ids)){
		    			$hi2s->whereIn("company_id", $company_ids);
		    		}
					$hi2s->andWhereIn("ipid", $invoices_patients);
		    		$hi2s_arr = $hi2s->fetchArray();
		    	}
		    
		    	if($hi2s_arr)
		    	{
		    		foreach($hi2s_arr as $k_subdiv => $v_subdiv)
		    		{
		    			if($v_subdiv['subdiv_id'] == "3")
		    			{
		    				$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
		    			}
		    		}
		    	}
		    
		    	foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    	{
		    		if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		    		{
		    			$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		    
		    			if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
		    			{
		    				$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
		    			}
		    
		    			if(strlen($healtharray['name']) > '0')
		    			{
		    				$ins_name = $healtharray['name'];
		    			}
		    			else if(strlen($v_health_insurance[0]['company_name']) > '0')
		    			{
		    				$ins_name = $v_health_insurance[0]['company_name'];
		    			}
		    		}
		    
		    		//health insurance name
		    		$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    
		    		//Versichertennummer
		    		$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		    
		    		//Institutskennzeichen
		    		$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		    
		    		//Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
		    		$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		    
		    		// Health insurance status - ISPC- 1368 // 150611
		    		$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
		    	}
		    
		    	 
		    	 
		    	//6. get all billable rlp actions - (saved or system)
		    	$visits_data = $this->get_rlp_related_visits($clientid, $invoices_patients, $invoice_period,$patients_invoices_periods);

		    	if($_REQUEST['dbgz'])
		    	{
		    		print_r($visits_data);
		    		exit;
		    	}
		    	
		    	//7. get (HD) main diagnosis
		    	$main_abbr = "'HD'";
		    	$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		    
		    	foreach($main_diag as $key => $v_diag)
		    	{
		    		$type_arr[] = $v_diag['id'];
		    	}
		    
		    	$pat_diag = new PatientDiagnosis();
		    	$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		    
		    	foreach($dianoarray as $k_diag => $v_diag)
		    	{
		    		//append diagnosis in patient data
		    		$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
		    		//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
		    		//ISPC-2489 Lore 26.11.2019
		    		$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
		    		
		    		$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
		    	}
		    	
		    	//8. get user data
		    	$user_details = User::getUserDetails($userid);
		    
		    	//9. reloop the invoices data array to create final array! 
		    	foreach($rlp_invoices_data as $k_invoice => $v_invoice)
		    	{
		    		if(!$master_data['invoice_' . $k_invoice])
		    		{
		    			$master_data['invoice_' . $k_invoice] = array();
		    		}
		    
		    		$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
		    
		    		$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
		    		$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
		    		$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    		$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
		    		$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
		    		$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		    		$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		    
		    		$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_start_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_end_date'];
		    
		    		$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_nr'];
		    
		    		$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_create_date'];
		    
		    		$inv_items = array();
		    		foreach($v_invoice['items_nosh'] as $k_item => $v_item)
		    		{
		    			$inv_actions = array();
		  
		    				$inv_actions = array();
		    				if($visits_data[$v_invoice['ipid']][$v_item['shortcut']]){
		    					$ident = "";
		    					foreach($visits_data[$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
		    					{
		    						$ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].' '.$v_action['start_time'].'-'.$v_action['end_time'];
		    						
		    						if( ! in_array($ident,$added2xml[$k_invoice]) 
		    								&&  $actions_count <= $v_item['qty']  
		    								&& in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )  ){
			    						$inv_actions['dta_id'] = $v_action['dta_id'];
			    						$inv_actions['dta_name'] = $v_action['dta_name'];
			    						$inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
			    						$inv_actions['ammount'] = str_pad(number_format($v_action['qty'], '2', ',', ''), "7", "0", STR_PAD_LEFT);
			    						$inv_actions['day'] = date('Ymd', strtotime($v_action['day']));
			    						$inv_actions['start_time'] = $v_action['start_time'];
			    						 
			    						if(strlen($v_action['end']) > '0')
			    						{
			    							$inv_actions['end_time'] = date('Hi', strtotime($v_action['end']));
			    						} else{
				    						$inv_actions['end_time'] = $v_action['end_time'];
			    						}
			    						 
			    						$inv_items['actions']['action_' . $k_action] = $inv_actions;
			    						
			    						$added2xml[$k_invoice][] = $ident ;
		    						}
		    					}
		    					$master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
		    					$inv_actions = array();
		    					$inv_items = array();
		    				}
		    		}
		    		$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    	}
		    
		    	
		    	return $master_data;
		    }
		    
		    
		    
		    private function get_rlp_related_visits($clientid, $invoices_patients, $selected_period,$patients_invoices_periods)
		    {
		    	
		    	$patientmaster = new PatientMaster();
		    	//Client Hospital Settings START
		    	$conditions['periods'][0]['start'] = $selected_period['start'];
		    	$conditions['periods'][0]['end'] = $selected_period['end'];
		    	$conditions['client'] = $clientid;
		    	$conditions['ipids'] = $invoices_patients;
		    	$patient_days = Pms_CommonData::patients_days($conditions);
		    
		    
		    	$pateint_invoiced_period_days = array();
		    	$sapv_invoiced_period_days = array();
		    	foreach($invoices_patients as $iipid){
		    		$sapv_invoiced_period_days[$iipid] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end']);
		    		$pateint_invoiced_period_days [$iipid] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end']);
		    		
		    		
		    		array_walk($pateint_invoiced_period_days [$iipid], function(&$value) {
		    			$value = date("d.m.Y",strtotime($value));
		    		});
		    	}
		    	
		    	$patient_action_days[$v_ipid]['actions'] = array_values($action_days[$v_ipid]['actions']);
		    	array_walk($patient_action_days[$v_ipid]['actions'], function(&$value) {
		    		$value = strtotime($value);
		    	});
		    	
		    	//find if there is a sapv for current period START!
		    	$dropSapv = Doctrine_Query::create()
		    	->select('*')
		    	->from('SapvVerordnung')
		    	->whereIn('ipid', $invoices_patients)
		    	->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    	->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    	->andWhere('isdelete=0')
		    	->orderBy('verordnungam ASC');
		    	$droparray = $dropSapv->fetchArray();
		    
		    	$all_sapv_days = array();
		    	$temp_sapv_days = array();
		    	$s=0;
		    	foreach($droparray as $k_sapv => $v_sapv)
		    	{
		    		$r1['start'][$v_sapv['ipid']][$s] = "";
		    		$r1['end'][$v_sapv['ipid']][$s] = "";
		    		 
		    		$r2['start'][$v_sapv['ipid']][$s] = "";
		    		$r2['end'][$v_sapv['ipid']][$s] = "";
		    
		    
		    			
		    		if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
		    			// no sapv taken here - becouse it is considered to be fully denied
		    		}
		    		else
		    		{
		    			$r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
		    			$r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
		    
		    			$r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
		    			$r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
		    
		    			$invoiced_sapv_type[$v_sapv['ipid']][] = $v_sapv;
		    			 
		    			$sapv_period2type_arr[$v_sapv['ipid']][date('Y-m-d',strtotime($v_sapv['verordnungam']))] = $sv_data['verordnet'];
		    			 
		    			if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
		    			{
		    				$invoiced_sapv[$v_sapv['ipid']] = $v_sapv['id'];
		    				// 		    				$invoiced_sapv_type[$v_sapv['ipid']]['current'][$v_sapv['id']] = $v_sapv['verordnet'];
		    
		    				//aditional data from sapv which was added on 16.10.2014
		    				if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
		    				{
		    					$sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
		    				}
		    
		    				if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
		    					$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
		    				} else{
		    					$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
		    				}
		    
		    
		    				if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
		    				{
		    					$v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
		    				}
		    
		    
		    				$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		    				$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		    
		    				$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		    
		    
		    				//aditional data from sapv which was added on 31.10.2014
		    				$sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
		    				$sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
		    				$sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
		    				$sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		    
		    				foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
		    				{
		    					if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
		    					{
		    						$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
		    					}
		    
		    					$current_verordnet = explode(',', $v_sapv['verordnet']);
		    					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		    
		    					asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
		    					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
		    				}
		    
		    				$s++;
		    			}
		    		}
		    	}
		    	
		    	$sapv_on_invoice = array();
		    	foreach($invoiced_sapv_type as $k=>$sd){
		    		foreach($sd as $k=>$sdata){
		    
		    			$r2['start'][$sdata['ipid']]  = strtotime($patients_invoices_periods[ $sdata['ipid'] ]['start']);
		    			$r2['end'][$sdata['ipid']]  = strtotime($patients_invoices_periods[$sdata['ipid']]['end']);
		    
		    
		    			if(Pms_CommonData::isintersected(strtotime(date('Y-m-d', strtotime($sdata['verordnungam']))),strtotime(date('Y-m-d', strtotime($sdata['verordnungbis']))), $r2['start'][$sdata['ipid']] , $r2['end'][$sdata['ipid']])  )
		    			{
		    
		    				if($sdata['verordnet'] == "1"){
		    					$sapv_on_invoice[$sdata['ipid']]['only_be']= '1';
		    				} else{
		    					$sapv_on_invoice[$sdata['ipid']]['only_be']= '0';
		    				}
		    
		    				$sapv_on_invoice[$sdata['ipid']]['sapv_start']= date('d.m.Y',strtotime($sdata['verordnungam']));
		    				$sapv_on_invoice[$sdata['ipid']]['sapv_end']= date('d.m.Y',strtotime($sdata['verordnungbis']));
		    				$sapv_on_invoice[$sdata['ipid']]['create_date']= date('d.m.Y',strtotime($sdata['create_date']));
		    
		    				//aditional data from sapv which was added on 16.10.2014
		    				if($sdata['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($sdata['approved_date'])) != '1970-01-01' && $sdata['status'] == '2')
		    				{
		    			   
		    					$sapv_on_invoice[$sdata['ipid']]['approved_date'] = date('d.m.Y', strtotime($sdata['approved_date']));
		    					$sapv_on_invoice[$sdata['ipid']]['approved_number'] = $sdata['approved_number'];
		    				}
		    			}
		    		}
		    	}
		 
		    	
		    	//get RLP products - system generated and saved
		    	$rlp_control = new RlpControl ();
		    	$rlp_products = $rlp_control->rlp_actions($invoices_patients,$pateint_invoiced_period_days);

 		    	if($_REQUEST['dbgz'])
		    	{
		    		print_r($rlp_products);
		    		print_r("\npatientsz");
		    		print_r($invoices_patients);
		    		print_r("\n sapv");
		    		print_r($sapv_on_invoice);
		    	}
		    	 
		    	
		    	$rlp_lang = $this->view->translate('rlp_invoice_lang');
		    	
// 		    	dd($rlp_lang);
		    	

		    	//TODO-2058 p2 Ancuta 31.01.2019
		    	$client_products = array();
		    	$client_products = RlpProductsTable::find_client_products($clientid);
		    	 // --
		    	
		    	
		    	
		    	
		    	$products = array();
		    	foreach($invoices_patients as $patient)
		    	{
		    		
		    		foreach($rlp_products[$patient] as $shortcut=>$sh_days_array){
		    			
		    			foreach($sh_days_array as $action_date => $sh_value){
		    				
		    				$products[$patient][$shortcut][$action_date]['qty'] = $sh_value['qty'];
// 		    				$products[$patient][$shortcut][$action_date]['dta_name'] = $shortcut.' '.$action_date;
		    				$products[$patient][$shortcut][$action_date]['dta_name'] = $rlp_lang['products'][$shortcut.'_label'];
		    				//TODO-2058 p2 Ancuta 31.01.2019
		    				if( !empty($client_products[$shortcut])){
    		    				$products[$patient][$shortcut][$action_date]['dta_name'] .= ' - '.$client_products[$shortcut];
		    				}
		    				// -- 
		    				$products[$patient][$shortcut][$action_date]['dta_price']= $sh_value['dta_price'];
		    				$products[$patient][$shortcut][$action_date]['dta_id']= $sh_value['dta_id'];
		    				$products[$patient][$shortcut][$action_date]['day']= $action_date;
		    				$products[$patient][$shortcut][$action_date]['start']= $action_date.' 00:00:00';
		    				$products[$patient][$shortcut][$action_date]['start_time']= date('Hi', strtotime($action_date.' 00:00:00'));
		    				$products[$patient][$shortcut][$action_date]['end']= $action_date.' 23:59:59';
		    				$products[$patient][$shortcut][$action_date]['end_time']= date('Hi', strtotime($action_date.' 23:59:59'));
		    			}
		    		}
		    	}
		 
		    	
	    		if($_REQUEST['dbgz'])
	    		{
	    			var_dump($products);
	    		}
	    
	    		foreach($products as $k_patient_ipid => $patient_sh_details)
	    		{
	    			foreach($patient_sh_details as $sh=>$date_entry){
	    				foreach($date_entry as $Ymd=>$date_details){
	    					$data[$k_patient_ipid][$sh]['actions'][] = $date_details;
	    				}
	    			}
	    		}
	    		 
	    		foreach($sapv_data as $k_ipid =>$sapvdata){
	    			$sapv_data[$k_ipid] = end($sapvdata);
	    		}
	    
	    		foreach($invoices_patients as $k_pat => $v_ipid)
	    		{
	    			$triggered[$v_ipid] = $data[$v_ipid];
	    
	    			$triggered_sapv['sapv_start_date'] = $sapv_on_invoice[$v_ipid]['sapv_start'];
	    			$triggered_sapv['sapv_end_date'] = $sapv_on_invoice[$v_ipid]['sapv_end'];
	    			$triggered_sapv['sapv_create_date'] = $sapv_on_invoice[$v_ipid]['create_date'];
	    			$triggered_sapv['sapv_approved_date'] = $sapv_on_invoice[$v_ipid]['approved_date'];
	    			$triggered_sapv['sapv_approved_nr'] = $sapv_on_invoice[$v_ipid]['approved_number'];
	    
	    			$triggered[$v_ipid]['sapv'] = $triggered_sapv;
	    		}

	    		
	    		return $triggered;
	    	}

		    //#####################################################
		    //#####################################################
		    //#########NEW BRE KINDER BILLING   ISPC-2214 ########
		    //#####################################################
		    //#####################################################
		    
		    
		    public function listdtainvoicesystemAction()
		    {
		    	$logininfo = new Zend_Session_Namespace('Login_Info');
		    	$userid = $logininfo->userid;
		    	$clientid = $logininfo->clientid;
		    
		    	//get allowed client invoices
		    	$client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
// 		    	$this->view->invoice_type = $invoice_type = $client_allowed_invoice[0];
		    	$this->view->invoice_type = $invoice_type = "bre_kinder_invoice";
		    	// bre_kinder_invoice
 
		    	
		    	 
		    	$storned_invoices = InvoiceSystem::get_storned_invoices($invoice_type,$clientid);
		    	$unpaid_status = array("2","5");
		    
		    	//construct months array in which the curent client has bre_invoices completed, not paid
		    	$months_q = Doctrine_Query::create()
		    	->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
		    	->from('InvoiceSystem is')
		    	->where("isdelete = 0")
		    	->andWhere('client = ?', $clientid)
		    	->andWhere('completed_date != "0000-00-00 00:00:00"')
		    	->andWhere('invoice_type = ?',$invoice_type)
		    	->andWhere("storno = 0 " . $where);
		    	if(!empty($storned_invoices)){
			    	$months_q->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	}
		    	$months_q->andWhereIN("status",$unpaid_status) // display only unpaid
		    	->orderBy('DISTINCT DESC');
		    	$months_res = $months_q->fetchArray();
		    
		    	if($months_res)
		    	{
		    		//current month on top
		    		$months_array[date('Y-m', time())] = date('m-Y', time());
		    		foreach($months_res as $k_month => $v_month)
		    		{
		    			$months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
		    		}
		    
		    		$months_array = array_unique($months_array);
		    	}
		    
		    	if(strlen($_REQUEST['search']) > '0')
		    	{
		    		$selected_period['start'] = $_REQUEST['search'] . "-01";
		    		$selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
		    	}
		    
		    	$this->view->months_array = $months_array;
		    
		    	if($this->getRequest()->isPost())
		    	{
		    		$post = $_POST;
		    
		    		$dta_data = $this->gather_dta_invoicesystem_data($clientid, $userid, $post);
		    		$this->generate_dta_xml($dta_data);
		    		exit;
		    	}
		    }
		    
		    

		    public function fetchdtainvoicesystemlistAction()
		    {
		    	$logininfo = new Zend_Session_Namespace('Login_Info');
		    	$hidemagic = Zend_Registry::get('hidemagic');
		    	$userid = $logininfo->userid;
		    	$clientid = $logininfo->clientid;
		    	$user_type = $logininfo->usertype;
		    
		    	$columnarray = array(
		    			"pat" => "epid_num",
		    			"invnr" => "invoice_number",
		    			"invstartdate" => "invoice_start",
		    			"invdate" => "completed_date_sort",
		    			"invtotal" => "invoice_total",
		    			"invkasse" => "company_name", // used in first order of health insurances
		    	);
		    
		    	if($clientid > 0)
		    	{
		    		$where = ' and client=' . $logininfo->clientid;
		    	}
		    	else
		    	{
		    		$where = ' and client=0';
		    	}
		    
		    	$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		    	$this->view->order = $orderarray[$_REQUEST['ord']];
		    	$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
		    
		    	$client_users_res = User::getUserByClientid($clientid, 0, true);
		    
		    	foreach($client_users_res as $k_user => $v_user)
		    	{
		    		$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
		    	}
		    
		    	$this->view->client_users = $client_users;
		    
		    	
		    	
		    	//get patients data used in search and list
		    	$sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		    	$sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    	$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    	$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    	$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    	$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
 
		    	$f_patient = Doctrine_Query::create()
		    	->select($sql)
		    	->from('PatientMaster p')
		    	->where("p.isdelete =0")
		    	->leftJoin("p.EpidIpidMapping e")
		    	->andWhere('e.clientid = ' . $clientid);
		    
		    	if($_REQUEST['clm'] == 'pat')
		    	{
		    		$f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    	}
		    
		    	$f_patients_res = $f_patient->fetchArray();
		    
		    	foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		    	{
		    		$f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		    		$client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
		    	}
		    
		    	$this->view->client_patients = $client_patients;
		    
		    	if(strlen($_REQUEST['val']) > '0')
		    	{
		    		$selected_period['start'] = $_REQUEST['val'] . "-01";
		    		$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    	}
		    	else
		    	{
		    		$selected_period['start'] = date('Y-m-01', time());
		    		$selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
		    	}
		    
		    	
		    	
		    	//order by health insurance
		    	if($_REQUEST['clm'] == "invkasse" && ! empty($f_patients_ipids))
		    	{
		    		$orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
		    
		    		$drop = Doctrine_Query::create()
		    		->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
		    		->from('PatientHealthInsurance')
		    		->whereIn("ipid", $f_patients_ipids)
		    		->orderBy($orderby);
		    		$droparray = $drop->fetchArray();
		    
		    		$f_patients_ipids = array();
		    		foreach($droparray as $k_pat_hi => $v_pat_hi)
		    		{
		    			$f_patients_ipids[] = $v_pat_hi['ipid'];
		    		}
		    	}
		    
		    	
		    
		    	$storned_invoices = InvoiceSystem::get_storned_invoices($invoice_type,$clientid);
		    	$unpaid_status = array("2","5");
		    
		    	$fdoc = Doctrine_Query::create()
		    	->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
		    	->from('InvoiceSystem')
		    	->where("isdelete = 0 ")
		    	->andWhere('client = ?', $clientid)
		    	->andWhere('invoice_type = ?',$invoice_type)
		    	->andWhere("storno = '0'")
		    	->andWhere('completed_date != "0000-00-00 00:00:00"');
		    	if(! empty($f_patients_ipids)){
			    	$fdoc->andWhereIn('ipid', $f_patients_ipids);
		    	}
		    	if(!empty($unpaid_status)){
			    	$fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
		    	}
		    	if(!empty($storned_invoices)){
		    		$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	}
		    	$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
		    	if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
		    	{
		    		$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		    	}
		    	else
		    	{
		    		//sort by patient sorted ipid order
		    		$fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
		    	}
		    
		    	
		    	
		    	//used in pagination of search results
		    	$fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
		    	$fdocarray = $fdoc->fetchArray();
		    	$limit = 500;
		    	$fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
		    	$fdoc->where("isdelete = 0 " . $where . "");
		    	$fdoc->andWhere("storno = '0'");
		    	$fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
		    	if(! empty($f_patients_ipids)){
		    		$fdoc->andWhereIn('ipid', $f_patients_ipids);
		    	}
		    	if(!empty($unpaid_status)){
		    		$fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
		    	}
		    	if(!empty($storned_invoices)){
		    		$fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
		    	}
		    	$fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
		    	$fdoc->limit($limit);
		    	$fdoc->offset($_REQUEST['pgno'] * $limit);
		    	$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
		    
		    	//get ipids for which we need health insurances
		    	$inv_ipids = array();
		    	foreach($fdoclimit as $k_inv => $v_inv)
		    	{
		    		$inv_ipids[] = $v_inv['ipid'];
		    	}
		    
		    

		    	//6. patients health insurance
		    	$healthinsu_array = array();
		    	$healthinsu = array();
		    	
		    	if(!empty($inv_ipids)){
			    	$phelathinsurance = new PatientHealthInsurance();
			    	$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
		    	}
		    
		    	
		    	if( ! empty($healthinsu_array)){
		    		
		    		$company_ids = array();
			    	foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
			    	{
			    		$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
			    
			    		if($v_healthinsu['companyid'] != '0')
			    		{
			    			$company_ids[] = $v_healthinsu['companyid'];
			    		}
			    	}
			    
			    	$health_insurance_master = array();
			    	if( ! empty($company_ids)){
				    	$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
			    	}
			    
			    	foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
			    	{
			    		if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
			    		{
			    			$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
			    
			    			if(strlen($healtharray['name']) > '0')
			    			{
			    				$ins_name = $healtharray['name'];
			    			}
			    			else if(strlen($v_health_insurance[0]['company_name']) > '0')
			    			{
			    				$ins_name = $v_health_insurance[0]['company_name'];
			    			}
			    		}
			    
			    		//health insurance name
			    		$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
			    	}
		    	}
		    	$this->view->healthinsurances = $healthinsu;
		    
		    
		    	
		    	$this->view->{"style" . $_GET['pgno']} = "active";
		    	if(count($fdoclimit) > '0')
		    	{
		    		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtainvoicesystemlist.html");
		    		$this->view->templates_grid = $grid->renderGrid();
		    		$this->view->navigation = $grid->dotnavigation("dtainvoicesystemnavigation.html", 5, $_REQUEST['pgno'], $limit);
		    	}
		    	else
		    	{
		    		//no items found
		    		$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
		    		$this->view->navigation = '';
		    	}
		    
		    	$response['msg'] = "Success";
		    	$response['error'] = "";
		    	$response['callBack'] = "callBack";
		    	$response['callBackParameters'] = array();
		    	$response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtainvoicesystemlist.html');
		    
		    	echo json_encode($response);
		    	exit;
		    }
		    
		    

		    private function gather_dta_invoicesystem_data($clientid, $userid, $post)
		    {
		    	
		    	$patientmaster = new PatientMaster();
		    	
		    	$client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
		    	// 		    	$this->view->invoice_type = $invoice_type = $client_allowed_invoice[0];
		    	$this->view->invoice_type = $invoice_type = "bre_kinder_invoice";
		    	
		    	//1. get all selected invoices data
		    	$invoice_system_obj = new InvoiceSystem();
		    	$invoice_system_data = $invoice_system_obj->get_multiple_invoices($invoice_type,$post['invoices'][$invoice_type],false,true);

		    	if($invoice_system_data === false){
		    		return array();
		    	}
		    
		    	$invoices_patients = array();
		    	$invoiced_days = array();
		    	foreach($invoice_system_data as $k_inv => $v_inv)
		    	{
		    		$invoices_patients[] = $v_inv['ipid'];
		    
		    		$invoice_period_patient['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		    		$invoice_period_patient['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    
		    		$invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_start']));
		    		$invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_end']));
		    
		    		$patients_invoices_periods[$v_inv['ipid']] = $invoice_period_patient;
		    
		    		$invoice_period_sapv['start'] = date('Y-m-d', strtotime($v_inv['sapv_start']));
		    		$invoice_period_sapv['end'] = date('Y-m-d', strtotime($v_inv['sapv_end']));
		    
		    		
		    		$invoiced_days[$v_inv['ipid']][$v_inv['id']] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
		    		
		    		$patients_invoices_sapv_periods[$v_inv['ipid']] = $invoice_period_sapv;
		    
// 		    		foreach($v_inv['items'] as $sh_item_data=>$items){
// 		    			foreach($items as $k=>$itm){
// 			    			$invoice_system_data[$k_inv]['items_nosh'][] = $itm;
// 		    			}
// 		    		}
		    	}
		    	 
// 		    	dd($invoiced_days); 
		    	
		    	asort($invoice_periods);
		    	$invoice_periods_date = array_values($invoice_periods);
		    	$invoice_period['start'] = $invoice_periods_date[0];
		    	$invoice_period['end'] = end($invoice_periods_date);
		    
		    	//2. get all required client data
		    	$clientdata = Pms_CommonData::getClientData($clientid);
		    	$client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
		    	$client_data['client']['team_name'] = $clientdata[0]['team_name'];
		    	$client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
		    	$client_data['client']['phone'] = $clientdata[0]['phone'];
		    	$client_data['client']['fax'] = $clientdata[0]['fax'];
		    
		    
		    	//3. get pflegestuffe in current period
		    	$pflege = new PatientMaintainanceStage();
		    	$pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
		    
		    	foreach($pflege_arr as $k_pflege => $v_pflege)
		    	{
		    		$grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
		    	}
		    
		    	foreach($grouped_pflege as $k_gpflege => $v_gpflege)
		    	{
		    		$last_pflege = end($v_gpflege);
		    
		    		if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
		    		{
		    			//$k_gpflege = patient epid
		    			$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
		    		}
		    		else
		    		{
		    			$pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
		    		}
		    	}
		    
		    	//4. get all involved patients required data
		    	$patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
		    
		    	foreach($patient_details as $k_pat_ipid => $v_pat_details)
		    	{
		    		$patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
		    		$patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
		    		$patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
		    		$patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
		    		$patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
		    		$patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
		    		$patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'];
		    	}
		    
		    	//4.1 get patients readmission details
		    	$conditions['periods'][0]['start'] = '2009-01-01';
		    	$conditions['periods'][0]['end'] = date('Y-m-d');
		    	$conditions['client'] = $clientid;
		    	$conditions['ipids'] = $invoices_patients;
		    	$patient_days = Pms_CommonData::patients_days($conditions);
		    
		    	foreach($patient_days as $k_patd_ipid => $v_pat_details)
		    	{
		    		$patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
		    	}
		    
 
		    	//5. patients health insurance
		    	$phelathinsurance = new PatientHealthInsurance();
		    	$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
		    
		    	$status_int_array = array("M" => "1", "F" => "3", "R" => "5");
		    	// ispc = M => 1 = Versicherungspflichtige und -berechtigte
		    	// ispc = F => 3 = Familienversicherte
		    	// ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
		    	//TODO-3528 Lore 12.11.2020
		    	$modules = new Modules();
		    	$extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
		    	if($extra_healthinsurance_statuses){
		    	    $status_int_array += array(
		    	        "00" => "00",          //"Gesamtsumme aller Stati",
		    	        "11" => "11",          //"Mitglieder West",
		    	        "19" => "19",          //"Mitglieder Ost",
		    	        "31" => "31",          //"Angehörige West",
		    	        "39" => "39",          //"Angehörige Ost",
		    	        "51" => "51",          //"Rentner West",
		    	        "59" => "59",          //"Rentner Ost",
		    	        "99" => "99",          //"nicht zuzuordnende Stati",
		    	        "07" => "07",          //"Auslandsabkommen"
		    	    );
		    	}
		    	//.
		    	
		    	foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
		    	{
		    		$patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
		    
		    		if($v_healthinsu['companyid'] != '0')
		    		{
		    			$company_ids[] = $v_healthinsu['companyid'];
		    		}
		    	}
		    
		    	if(!empty($company_ids)){
			    	$health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
		    	}
		    
		    
		    	//get health insurance subdivizions
		    	$symperm = new HealthInsurancePermissions();
		    	$divisions = $symperm->getClientHealthInsurancePermissions($clientid);
		    
		    	if($divisions)
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
		    		->from("PatientHealthInsurance2Subdivisions");
		    		if(!empty($company_ids)){
		    			$hi2s->whereIn("company_id", $company_ids);
		    		}
					$hi2s->andWhereIn("ipid", $invoices_patients);
		    		$hi2s_arr = $hi2s->fetchArray();
		    	}
		    
		    	if($hi2s_arr)
		    	{
		    		foreach($hi2s_arr as $k_subdiv => $v_subdiv)
		    		{
		    			if($v_subdiv['subdiv_id'] == "3")
		    			{
		    				$subdivisions[$v_subdiv['ipid']] = $v_subdiv;
		    			}
		    		}
		    	}
		    
		    	foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
		    	{
		    		if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
		    		{
		    			$healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
		    
		    			if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
		    			{
		    				$v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
		    			}
		    
		    			if(strlen($healtharray['name']) > '0')
		    			{
		    				$ins_name = $healtharray['name'];
		    			}
		    			else if(strlen($v_health_insurance[0]['company_name']) > '0')
		    			{
		    				$ins_name = $v_health_insurance[0]['company_name'];
		    			}
		    		}
		    
		    		//health insurance name
		    		$healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
		    
		    		//Versichertennummer
		    		$healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
		    
		    		//Institutskennzeichen
		    		$healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
		    
		    		//Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
		    		$healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
		    
		    		// Health insurance status - ISPC- 1368 // 150611
		    		$healthinsu[$k_ipid_hi]['health_insurance_status'] = $status_int_array[$v_health_insurance[0]['insurance_status']];
		    	}
		    
		    	 
		    	 
		    	//6. get all billable actions - (saved or system)
		    	
		    	$visits_data = array();
		    	if($invoice_type == "bre_kinder_invoice"){
    		    	$visits_data = $this->get_brekinder_related_visits($clientid, $invoices_patients, $invoice_period,$patients_invoices_periods);
		    	}

		    	if($_REQUEST['dbgz'])
		    	{
		    		print_r($visits_data);
		    		exit;
		    	}
		    	
		    	//7. get (HD) main diagnosis
		    	$main_abbr = "'HD'";
		    	$main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
		    
		    	foreach($main_diag as $key => $v_diag)
		    	{
		    		$type_arr[] = $v_diag['id'];
		    	}
		    
		    	$pat_diag = new PatientDiagnosis();
		    	$dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
		    
		    	foreach($dianoarray as $k_diag => $v_diag)
		    	{
		    		//append diagnosis in patient data
		    		$diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
		    		//$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
		    		//ISPC-2489 Lore 26.11.2019
		    		$diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
		    		
		    		$patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
		    	}
		    	
		    	//8. get user data
		    	$user_details = User::getUserDetails($userid);
		    
		    	//9. reloop the invoices data array to create final array!
		    	foreach($invoice_system_data as $k_invoice => $v_invoice)
		    	{
		    		if(!$master_data['invoice_' . $k_invoice])
		    		{
		    			$master_data['invoice_' . $k_invoice] = array();
		    		}
		    
		    		$master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
		    
		    		$master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
		    		$master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
		    		$master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    		$master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
		    		$master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
		    		$master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
		    		$master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
		    		$master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
		    
		    		$master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_start_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_end_date'];
		    
		    		$master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_date'];
		    		$master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_nr'];
		    
		    		$master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_create_date'];
		    
		    		$inv_items = array();
		    		foreach($v_invoice['items'] as $k_item => $v_item)
		    		{
		    			$inv_actions = array();
		  
		    				$inv_actions = array();
		    				if($visits_data[$v_invoice['ipid']][$v_item['shortcut']]){
		    					$ident = "";
		    					foreach($visits_data[$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
		    					{
		    						$ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].' '.$v_action['start_time'].'-'.$v_action['end_time'];
		    						
		    						if( ! in_array($ident,$added2xml[$k_invoice]) 
		    								&&  $actions_count <= $v_item['qty']  
		    								&& in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )  ){
			    						$inv_actions['dta_id'] = $v_action['dta_id'];
			    						$inv_actions['dta_name'] = $v_action['dta_name'];
			    						$inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
			    						$inv_actions['ammount'] = str_pad(number_format($v_action['qty'], '2', ',', ''), "7", "0", STR_PAD_LEFT);
			    						$inv_actions['day'] = date('Ymd', strtotime($v_action['day']));
			    						$inv_actions['start_time'] = $v_action['start_time'];
			    						 
			    						if(strlen($v_action['end']) > '0')
			    						{
			    							$inv_actions['end_time'] = date('Hi', strtotime($v_action['end']));
			    						} else{
				    						$inv_actions['end_time'] = $v_action['end_time'];
			    						}
			    						 
			    						$inv_items['actions']['action_' . $k_action] = $inv_actions;
			    						
			    						$added2xml[$k_invoice][] = $ident ;
		    						}
		    					}
		    					$master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
		    					$inv_actions = array();
		    					$inv_items = array();
		    				}
		    		}
		    		$master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
		    	}
		    	return $master_data;
		    }
		    
		    
		    private function get_brekinder_related_visits($clientid, $invoices_patients, $selected_period,$patients_invoices_periods)
		    {
		    	
		    	$patientmaster = new PatientMaster();
		    	//Client Hospital Settings START
		    	$conditions['periods'][0]['start'] = $selected_period['start'];
		    	$conditions['periods'][0]['end'] = $selected_period['end'];
		    	$conditions['client'] = $clientid;
		    	$conditions['ipids'] = $invoices_patients;
		    	$patient_days = Pms_CommonData::patients_days($conditions);
		    
		    
		    	$pateint_invoiced_period_days = array();
		    	$sapv_invoiced_period_days = array();
		    	foreach($invoices_patients as $iipid){
		    		$sapv_invoiced_period_days[$iipid] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end']);
		    		$pateint_invoiced_period_days [$iipid] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end']);
		    		
		    		
		    		array_walk($pateint_invoiced_period_days [$iipid], function(&$value) {
		    			$value = date("d.m.Y",strtotime($value));
		    		});
		    	}
		    	
		    	$patient_action_days[$v_ipid]['actions'] = array_values($action_days[$v_ipid]['actions']);
		    	array_walk($patient_action_days[$v_ipid]['actions'], function(&$value) {
		    		$value = strtotime($value);
		    	});
		    	
		    	//find if there is a sapv for current period START!
		    	$dropSapv = Doctrine_Query::create()
		    	->select('*')
		    	->from('SapvVerordnung')
		    	->whereIn('ipid', $invoices_patients)
		    	->andWhere('verordnungam != "0000-00-00 00:00:00"')
		    	->andWhere('verordnungbis != "0000-00-00 00:00:00"')
		    	->andWhere('isdelete=0')
		    	->orderBy('verordnungam ASC');
		    	$droparray = $dropSapv->fetchArray();
		    
		    	$all_sapv_days = array();
		    	$temp_sapv_days = array();
		    	$s=0;
		    	foreach($droparray as $k_sapv => $v_sapv)
		    	{
		    		$r1['start'][$v_sapv['ipid']][$s] = "";
		    		$r1['end'][$v_sapv['ipid']][$s] = "";
		    		 
		    		$r2['start'][$v_sapv['ipid']][$s] = "";
		    		$r2['end'][$v_sapv['ipid']][$s] = "";
		    
		    
		    			
		    		if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
		    			// no sapv taken here - becouse it is considered to be fully denied
		    		}
		    		else
		    		{
		    			$r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
		    			$r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
		    
		    			$r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
		    			$r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
		    
		    			$invoiced_sapv_type[$v_sapv['ipid']][] = $v_sapv;
		    			 
		    			$sapv_period2type_arr[$v_sapv['ipid']][date('Y-m-d',strtotime($v_sapv['verordnungam']))] = $sv_data['verordnet'];
		    			 
		    			if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
		    			{
		    				$invoiced_sapv[$v_sapv['ipid']] = $v_sapv['id'];
		    				// 		    				$invoiced_sapv_type[$v_sapv['ipid']]['current'][$v_sapv['id']] = $v_sapv['verordnet'];
		    
		    				//aditional data from sapv which was added on 16.10.2014
		    				if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
		    				{
		    					$sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
		    				}
		    
		    				if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
		    					$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
		    				} else{
		    					$sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
		    				}
		    
		    
		    				if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
		    				{
		    					$v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
		    				}
		    
		    
		    				$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
		    				$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		    
		    				$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		    
		    
		    				//aditional data from sapv which was added on 31.10.2014
		    				$sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
		    				$sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
		    				$sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
		    				$sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
		    
		    				foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
		    				{
		    					if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
		    					{
		    						$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
		    					}
		    
		    					$current_verordnet = explode(',', $v_sapv['verordnet']);
		    					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
		    
		    					asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
		    					$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
		    				}
		    
		    				$s++;
		    			}
		    		}
		    	}
		    	
		    	$sapv_on_invoice = array();
		    	foreach($invoiced_sapv_type as $k=>$sd){
		    		foreach($sd as $k=>$sdata){
		    
		    			$r2['start'][$sdata['ipid']]  = strtotime($patients_invoices_periods[ $sdata['ipid'] ]['start']);
		    			$r2['end'][$sdata['ipid']]  = strtotime($patients_invoices_periods[$sdata['ipid']]['end']);
		    
		    
		    			if(Pms_CommonData::isintersected(strtotime(date('Y-m-d', strtotime($sdata['verordnungam']))),strtotime(date('Y-m-d', strtotime($sdata['verordnungbis']))), $r2['start'][$sdata['ipid']] , $r2['end'][$sdata['ipid']])  )
		    			{
		    
		    				if($sdata['verordnet'] == "1"){
		    					$sapv_on_invoice[$sdata['ipid']]['only_be']= '1';
		    				} else{
		    					$sapv_on_invoice[$sdata['ipid']]['only_be']= '0';
		    				}
		    
		    				$sapv_on_invoice[$sdata['ipid']]['sapv_start']= date('d.m.Y',strtotime($sdata['verordnungam']));
		    				$sapv_on_invoice[$sdata['ipid']]['sapv_end']= date('d.m.Y',strtotime($sdata['verordnungbis']));
		    				$sapv_on_invoice[$sdata['ipid']]['create_date']= date('d.m.Y',strtotime($sdata['create_date']));
		    
		    				//aditional data from sapv which was added on 16.10.2014
		    				if($sdata['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($sdata['approved_date'])) != '1970-01-01' && $sdata['status'] == '2')
		    				{
		    			   
		    					$sapv_on_invoice[$sdata['ipid']]['approved_date'] = date('d.m.Y', strtotime($sdata['approved_date']));
		    					$sapv_on_invoice[$sdata['ipid']]['approved_number'] = $sdata['approved_number'];
		    				}
		    			}
		    		}
		    	}
		 
		    	
		    	//get products - system generated and saved
		    	$brekinderperformance_obj = new BreKinderPerformance();
		    	$is_products = $brekinderperformance_obj->bre_actions($invoices_patients,$pateint_invoiced_period_days);
		    	
		    	$lang = $this->view->translate('brekinder_invoice_lang');
		    	
		    	$products = array();
		    	foreach($invoices_patients as $patient)
		    	{
		    		
		    		foreach($is_products[$patient] as $shortcut=>$sh_days_array){
		    			
		    			foreach($sh_days_array as $action_date => $sh_value){
		    				
		    				$products[$patient][$shortcut][$action_date]['qty'] = $sh_value['qty'];
		    				$products[$patient][$shortcut][$action_date]['dta_name'] = $lang ['products'][$shortcut.'_label'];
		    				$products[$patient][$shortcut][$action_date]['dta_price']= $sh_value['dta_price'];
		    				$products[$patient][$shortcut][$action_date]['dta_id']= $sh_value['dta_id'];
		    				$products[$patient][$shortcut][$action_date]['day']= $action_date;
		    				$products[$patient][$shortcut][$action_date]['start']= $action_date.' 00:00:00';
		    				$products[$patient][$shortcut][$action_date]['start_time']= date('Hi', strtotime($action_date.' 00:00:00'));
		    				$products[$patient][$shortcut][$action_date]['end']= $action_date.' 23:59:59';
		    				$products[$patient][$shortcut][$action_date]['end_time']= date('Hi', strtotime($action_date.' 23:59:59'));
		    			}
		    		}
		    	}
		    	
	    		if($_REQUEST['dbgz'])
	    		{
	    			var_dump($products);
	    		}
	    
	    		foreach($products as $k_patient_ipid => $patient_sh_details)
	    		{
	    			foreach($patient_sh_details as $sh=>$date_entry){
	    				foreach($date_entry as $Ymd=>$date_details){
	    					$data[$k_patient_ipid][$sh]['actions'][] = $date_details;
	    				}
	    			}
	    		}
	    		 
	    		foreach($sapv_data as $k_ipid =>$sapvdata){
	    			$sapv_data[$k_ipid] = end($sapvdata);
	    		}
	    
	    		foreach($invoices_patients as $k_pat => $v_ipid)
	    		{
	    			$triggered[$v_ipid] = $data[$v_ipid];
	    
	    			$triggered_sapv['sapv_start_date'] = $sapv_on_invoice[$v_ipid]['sapv_start'];
	    			$triggered_sapv['sapv_end_date'] = $sapv_on_invoice[$v_ipid]['sapv_end'];
	    			$triggered_sapv['sapv_create_date'] = $sapv_on_invoice[$v_ipid]['create_date'];
	    			$triggered_sapv['sapv_approved_date'] = $sapv_on_invoice[$v_ipid]['approved_date'];
	    			$triggered_sapv['sapv_approved_nr'] = $sapv_on_invoice[$v_ipid]['approved_number'];
	    
	    			$triggered[$v_ipid]['sapv'] = $triggered_sapv;
	    		}

	    		
	    		return $triggered;
	    	}
		    	    

	    	
	    	
	    	
	    	
	    	


	    	//#####################################################
	    	//#####################################################
	    	//######### NEW NR BILLING ISPC - 2143 ########################
	    	//######### + NEW Demstepcare BILLING ISPC-2461 17.11.2019  ##############
	    	//#####################################################
	    	//#####################################################
	    	
	    	
	    	public function listdtainvoicessystemAction()
	    	{
 	    	    
	    	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    	    $userid = $logininfo->userid;
	    	    $clientid = $logininfo->clientid;
	    	
	    	    if($clientid > 0)
	    	    {
	    	        $where = ' and client=' . $logininfo->clientid;
	    	    }
	    	    else
	    	    {
	    	        $where = ' and client=0';
	    	    }

	    	    //ISPC-2461
	    	    $client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
	    	    if(!$client_allowed_invoice[0])
	    	    {
	    	        echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>'.$this->view->translate('There is no client invoice type set').'</b></div>';
	    	        return;
	    	    }
	    	    $invoice_type = $client_allowed_invoice[0];
	    	    $this->view->invoice_type = $invoice_type;

	    	    
	    	    //-
	    	    
	    	    // Commented by Ancuta - ISPC-2461 - in order to allow multiple invoices types 
	    	    /* 
	    	    if(!empty($_REQUEST['invoice_type']))
	    	    {
	    	        $invoice_type = $_REQUEST['invoice_type'];
	    	    }
	    	    else
	    	    {
	    	        $invoice_type = 'nr_invoice';
	    	    }
	    	     */
	    	    $storned_invoices = InvoiceSystem::get_storned_invoices($invoice_type,$clientid);
	    	    $unpaid_status = array("2","5");
	    	
	    	    //construct months array in which the curent client has bre_invoices completed, not paid
	    	    $months_q = Doctrine_Query::create()
	    	    ->select('DISTINCT(DATE_FORMAT(invoice_start, "%Y-%m")), DATE_FORMAT(invoice_start, "%m-%Y") as invoice_start_f')
	    	    ->from('InvoiceSystem')
	    	    ->where("isdelete = 0")
	    	    ->andWhere("invoice_type = ? ",$invoice_type)
	    	    ->andWhere('completed_date != "0000-00-00 00:00:00"')
	    	    ->andWhere("storno = 0 " . $where);
	    	    if(!empty($storned_invoices)){
	    	        $months_q->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
	    	    }
	    	    $months_q->andWhereIN("status",$unpaid_status) // display only unpaid
	    	    ->orderBy('DISTINCT DESC');
	    	    $months_res = $months_q->fetchArray();
	    	
	    	    if($months_res)
	    	    {
	    	        //current month on top
	    	        $months_array[date('Y-m', time())] = date('m-Y', time());
	    	        foreach($months_res as $k_month => $v_month)
	    	        {
	    	            $months_array[$v_month['DISTINCT']] = $v_month['invoice_start_f'];
	    	        }
	    	
	    	        $months_array = array_unique($months_array);
	    	    }
	    	
	    	    if(strlen($_REQUEST['search']) > '0')
	    	    {
	    	        $selected_period['start'] = $_REQUEST['search'] . "-01";
	    	        $selected_period['end'] = date('Y-m-d', strtotime("+1 month", strtotime("-1 day", strtotime($selected_period['start']))));
	    	    }
	    	
	    	    $this->view->months_array = $months_array;
	    	    $this->view->invoice_type = $invoice_type;
	    	
	    	    if($this->getRequest()->isPost()) {
                    $post = $_POST;

                    $dta_data = $this->gather_dta_is_data($clientid, $invoice_type, $userid, $post);


                    if ($this->view->invoice_type == "demstepcare_invoice") {
                        $this->view->demstep_error=[];
                        //only one invoice at a time

                        //ISPC-2598-Hotfix Nico 15.03.2021
                        if(count($dta_data)==0){
                            $this->view->demstep_error[]="Es gibt keinen Rechnungsposten.";
                            return;
                        }

                        $my_data=array_pop($dta_data);

                        //ISPC-2598-Hotfix Nico 15.03.2021
                        if(!isset($my_data['items']) || count($my_data['items'])<1){
                            $this->view->demstep_error[]="Es gibt keinen Rechnungsposten.";
                            return;
                        }

                        $abr_first_day="0";
                        $abr_last_day="0";
                        $abr=[];
                        foreach ($my_data['items'] as $abr_items){
                            foreach($abr_items['actions'] as $abr_item){
                                $abr_entry=[];
                                $abr_entry['gebuehrennummer']=$abr_item['dta_id'];
                                $abr_entry['description']=$abr_item['dta_name'];
                                $abr_entry['price']=$abr_item['price'];
                                $abr_entry['ammount']=intval(str_replace(',','.',$abr_item['ammount']));
                                $abr_entry['day']=$abr_item['day'];
                                $abr[]=$abr_entry;
                                if($abr_first_day=="0" || $abr_entry['day']<$abr_first_day){
                                    $abr_first_day = $abr_entry['day'];
                                }
                                if($abr_last_day=="0" || $abr_entry['day']>$abr_last_day){
                                    $abr_last_day = $abr_entry['day'];
                                }
                            }
                        }


                        $form_details = PatientDemstepcareTable::find_form_By_patient($my_data['ipid']);
                        if(!empty($form_details)){
                            $form_id = $form_details['id'];
                        }

                        $demstep_form_values = PatientDemstepcareTable::find_patient_form_By_Form_Id($my_data['ipid'],$form_id);

                        $demstep_to_icd=[
                            1=>"F00.0",
                            2=>"F00.1",
                            3=>"F00.2",
                            4=>"F01.0",
                            5=>"F01.1",
                            6=>"F01.2",
                            7=>"F01.3",
                            8=>"F01.8",
                            9=>"F02.0",
                            10=>"F02.2",
                            11=>"F02.3",
                            12=>"F02.8"
                        ];

                        $demstep_icd=$demstep_to_icd[$demstep_form_values['dementia_diagnosis']];

                        $leading_users_array = PatientQpaLeading::get_current_leading_users($my_data['ipid']);

                        $leading_users = array();
                        foreach($leading_users_array as $k => $ld){
                            $leading_users[] = $ld['userid'];
                        }
                        if(count($leading_users)!==1){
                            $this->view->demstep_error[]="Dem Patienten muss genau ein Arzt zugewiesen sein.";
                            return;
                        }

                        $u=new User();
                        $doc_details=$u->getUserDetails($leading_users[0]);

                        $doc_llanr=$doc_details[0]['LANR'];
                        $doc_bsnr=$doc_details[0]['betriebsstattennummer'];

                        //ISPC-2598-Hotfix Nico 15.03.2021
                        $has_err=0;
                        if(strlen($doc_llanr)!==9){
                            $this->view->demstep_error[]="Die LLANR des Arztes muss genau 9 Zeichen haben.";
                            $has_err=1;
                        }
                        if(strlen($doc_bsnr)!==9){
                            $this->view->demstep_error[]="Die Betriebsstättennummer des Arztes muss genau 9 Zeichen haben.";
                            $has_err=1;
                        }
                        if(strlen($my_data['client']['ik'])!==9){
                            $this->view->demstep_error[]="Die IK-Nummer des Mandanten muss genau 9 Zeichen haben.";
                            $has_err=1;
                        }
                        if(strlen($my_data['hi_insurance_ik'])!==9){
                            $this->view->demstep_error[]="Die IK-Nummer der Krankenversicherung muss genau 9 Zeichen haben.";
                            $has_err=1;
                        }
                        if(strlen($my_data['hi_insurance_no'])<4){
                            $this->view->demstep_error[]="Die Versichertennummer des Patienten fehlt.";
                            $has_err=1;
                        }
                        if(strlen($abr_first_day)!==8 || strlen($abr_last_day)!==8 ){
                            $this->view->demstep_error[]="Es fehlt der Abrechnungszeitraum.";
                            $has_err=1;
                        }
                        if($has_err){
                            return;
                        }

                        $vertragskennzeichen="IR140";

                        $billid=$post['invoices']['demstepcare_invoice'][0];
                        $elog=EdifactLog::create_entry('itsg', $clientid, $my_data['client']['ik'], $my_data['hi_insurance_ik'], $billid);

                        $continuing_file_number_ik=$elog->fileno_ik;
                        $continuing_file_number_trust=$elog->fileno_trust;

                        $testdata=0;
                        if(isset($post['testdata']) && $post['testdata']=="test"){
                            $testdata=1;
                        }


                        $o=new Net_EDIFACT_Billing();
                        $o->create_billing_demstepcare(
                            $my_data['client']['ik'], //sender_ik
                            $my_data['hi_insurance_ik'],//$insurance_ik,
                            "660640162",//$datenannahmestelle_ik = ITSG
                            $doc_llanr,
                            $doc_bsnr,
                            $vertragskennzeichen,
                            $my_data['hi_insurance_status'],//$ins_status,
                            "00",//$ins_besondere_gruppe,
                            "00",//$ins_dmp,
                            $my_data['hi_insurance_no'],//$ins_vers_nummer,
                            $my_data['patient']['last_name'],//$ins_lastname,
                            $my_data['patient']['first_name'],//$ins_firstname,
                            $my_data['patient']['birthday'],//$ins_birth,
                            $my_data['patient']['sex'],
                            $demstep_icd,//diag_icd
                            $demstep_form_values['form_date'],//diag_date
                            $abr,
                            $my_data['overall_amount'],
                            $abr_first_day,
                            $abr_last_day,
                            $my_data['number'],
                            $my_data['date'],
                            $continuing_file_number_ik,
                            $continuing_file_number_trust,
                            $testdata
                        );

                        $msg=$o->get_edifact_string();
                        $e=$o->errors;
                        if(count($e)) {
                            foreach ($e as $err) {
                                $this->view->demstep_error[] = $err;
                            }
                            $elog->delete();
                            return;
                        }
                        $certinfo=EdifactCerts::get_private_key('itsg',$my_data['client']['ik']);
                        if(!isset($certinfo[0]['cert'])){
                            $this->view->demstep_error[]="Eigenes Zertifikat fehlt";
                            $elog->delete();
                            return;
                        }
                        if(!isset($certinfo[0]['cert'])){
                            $this->view->demstep_error[]="Privater Schlüssel fehlt";
                            $elog->delete();
                            return;
                        }
                        $ikcert=EdifactCerts::get_ik_public_key('itsg',$my_data['hi_insurance_ik']);
                        if($ikcert===false){
                            $this->view->demstep_error[]="Empfängerzertifikat für IK".$my_data['hi_insurance_ik']." fehlt";
                            $elog->delete();
                            return;
                        }

                        $pkcs=new Net_EDIFACT_PKCS();
                        if(!$pkcs->sign_message($msg, $o->physical_filename,$certinfo[0]['cert'],$certinfo[1]['cert'], $ikcert)){
                            $this->view->demstep_error[]="Beim Signieren und Verschlüsseln ist ein Fehler aufgetreten.";
                            $elog->delete();
                            return;
                        }
                        $auf=$o->auftragssatz;
                        $auf['size1']=str_pad(strlen($msg), 12, "0", STR_PAD_LEFT);
                        $auf['size2']=str_pad(strlen($pkcs->enccontent), 12, "0", STR_PAD_LEFT);
                        $auf=implode('',$auf);
                        if(!strlen($auf)==348){
                            $this->view->demstep_error[]="Beim erstellen des Auftragssatzes ist ein Fehler aufgetreten.";
                            $elog->delete();
                            return;
                        }
                        $pkcs->add_auffile($auf);
                        if(!$pkcs->zip()){
                            $this->view->demstep_error[]="Beim Zippen ist ein Fehler aufgetreten.";
                            $elog->delete();
                            return;
                        }
                        $enc_zip=$pkcs->zipfile;
                        $elog->msg=$msg;
                        $elog->file=base64_encode($enc_zip);
                        $elog->save();
                        $pkcs->to_browser();

                    } else {
                        $this->generate_dta_xml($dta_data);
                    }
	    	        exit;
	    	    }
	    	}


	    	public function edifactadminAction(){
                $logininfo = new Zend_Session_Namespace('Login_Info');
                $hidemagic = Zend_Registry::get('hidemagic');
                $userid = $logininfo->userid;
                $clientid = $logininfo->clientid;
                $this->view->clientid=$clientid;
                $this->view->no_errors=false;
                $ik=EdifactCerts::get_client_ik($clientid);

                $this->view->serror="";

                if(strlen($ik)!=9){
                    echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>Der Mandant benötigt eine gültige IK-Nummer (9-Stellig)</b></div>';#
                    return;
                }
                $this->view->ik=$ik;

                $client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
                if(!$client_allowed_invoice[0])
                {
                    echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>Der Mandant benötigt eine IK-Nummer</b></div>';
                    return;
                }
                $this->view->no_errors=true;

                $invoice_type = $client_allowed_invoice[0];
                $this->view->invoice_type = $invoice_type;

                $this->view->trustcenter=['itsg'=>[
                                'key'=>'itsg',
                                'name'=>'Rechnungsversand über ITSG',
                                'keysource'=>'<a href="https://www.itsg.de/oeffentliche-services/trust-center/oeffentliche-schluesselverzeichnisse-le/">Hier kann die aktuelle Zeertifikastsliste geladen werden (Datei annahme-rsa4096.key)</a>'
                                ]
                ];

                if ($this->view->invoice_type == "demstepcare_invoice") {
                    $selected_trustcenter='itsg';
                }
                $this->view->selected_trustcenter=$selected_trustcenter;

                if($selected_trustcenter=='itsg'){
                    if(isset($_FILES['ik_keyfile'])){
                        $in=file_get_contents($_FILES['ik_keyfile']['tmp_name']);
                        $p=EdifactCerts::parse_keylist_itsg($in);
                        if($_POST['step']==="1") {
                            $this->_helper->viewRenderer->setNoRender();
                            $this->_helper->layout->setLayout('layout_ajax');
                            echo json_encode($p);
                            exit();
                        }
                    }
                    if(isset($_POST['step']) && $_POST['step']==="2" && strlen($_POST['keys'])){
                        $p=json_decode($_POST['keys'],1);
                        foreach($p as $item){
                            EdifactCerts::add_public_key($selected_trustcenter, $item['key']);
                        }
                    }

                    if(isset($_FILES['certfile'])){
                        $in=file_get_contents($_FILES['certfile']['tmp_name']);
                        $parsed=openssl_x509_parse($in);
                        if(isset($parsed['validTo_time_t'])) {
                            if (date('Y-m-d', $parsed['validTo_time_t']) > date('Y-m-d')) {
                                EdifactCerts::add_public_cert($selected_trustcenter, $ik, $in);
                            } else {
                                $this->view->serror = "Zertifikat ist abgelaufen.";
                            }
                        }else{
                            $this->view->serror = "Zertifikat ist nicht lesbar.<br><div>Vielleicht muss das Zertifikat konvertiert werden?<pre>openssl pkcs7 -inform der -in quelle.p7c -out cert.cer\nopenssl pkcs7 -print_certs -in cert.cer -out cert2.cer</pre></div>";
                        }
                    }

                    if(isset($_FILES['pkeyfile'])){
                        $in=file_get_contents($_FILES['pkeyfile']['tmp_name']);
                        if(strpos($in,'--BEGIN PRIVATE KEY--')>=0 && strpos($in,'--END PRIVATE KEY--')>0){
                            EdifactCerts::add_private_key($selected_trustcenter, $ik, $in);
                        }else{
                            $this->view->serror = "Die Datei enthält keinen Schlüssel.";
                        }

                    }
                }


                $this->view->ikpublickeys=EdifactCerts::list_ik_public_keys($selected_trustcenter);
                $this->view->privcerts=EdifactCerts::get_private_key($selected_trustcenter, $ik);

            }
	    	
	    	
	    	
	    	public function fetchdtainvoicessystemlistAction()
	    	{
	    	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    	    $hidemagic = Zend_Registry::get('hidemagic');
	    	    $userid = $logininfo->userid;
	    	    $clientid = $logininfo->clientid;
	    	    $user_type = $logininfo->usertype;
	    	
	    	    if(!empty($_REQUEST['invoice_type']))
	    	    {
                    $invoice_type = $_REQUEST['invoice_type'];	    	    
	    	    }
	    	    else
	    	    {
                    $invoice_type = 'nr_invoice';	    	    
	    	    }
                
	    	    $columnarray = array(
	    	        "pat" => "epid_num",
	    	        "invnr" => "invoice_number",
	    	        "invstartdate" => "invoice_start",
	    	        "invdate" => "completed_date_sort",
	    	        "invtotal" => "invoice_total",
	    	        "invkasse" => "company_name", // used in first order of health insurances
	    	    );
	    	
	    	    if($clientid > 0)
	    	    {
	    	        $where = ' and client=' . $logininfo->clientid;
	    	    }
	    	    else
	    	    {
	    	        $where = ' and client=0';
	    	    }
	    	
	    	    $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
	    	    $this->view->order = $orderarray[$_REQUEST['ord']];
	    	    $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
	    	
	    	    $client_users_res = User::getUserByClientid($clientid, 0, true);
	    	
	    	    foreach($client_users_res as $k_user => $v_user)
	    	    {
	    	        $client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
	    	    }
	    	
	    	
	    	    $this->view->client_users = $client_users;
	    	    $this->view->invoice_type = $invoice_type;
	    	
	    	     
	    	     
	    	    //get patients data used in search and list
	    	    $sql = "e.ipid,e.epid, e.epid_num,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
	    	    $sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
	    	    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
	    	    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
	    	    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
	    	    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
	    	
	    	    $f_patient = Doctrine_Query::create()
	    	    ->select($sql)
	    	    ->from('PatientMaster p')
	    	    ->where("p.isdelete =0")
	    	    ->leftJoin("p.EpidIpidMapping e")
	    	    ->andWhere('e.clientid = ' . $clientid);
	    	
	    	    if($_REQUEST['clm'] == 'pat')
	    	    {
	    	        $f_patient->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
	    	    }
	    	
	    	    $f_patients_res = $f_patient->fetchArray();
	    	
	    	    foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
	    	    {
	    	        $f_patients_ipids[$v_f_pat_res['EpidIpidMapping']['epid_num']] = $v_f_pat_res['EpidIpidMapping']['ipid'];
	    	        $client_patients[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res['EpidIpidMapping']['epid'] . ' - ' . $v_f_pat_res['lastname'] . ', ' . $v_f_pat_res['firstname'];
	    	    }
	    	
	    	    $this->view->client_patients = $client_patients;
	    	
	    	    if(strlen($_REQUEST['val']) > '0')
	    	    {
	    	        $selected_period['start'] = $_REQUEST['val'] . "-01";
	    	        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
	    	    }
	    	    else
	    	    {
	    	        $selected_period['start'] = date('Y-m-01', time());
	    	        $selected_period['end'] = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($selected_period['start']))));
	    	    }
	    	
	    	     
	    	     
	    	    //order by health insurance
	    	    if($_REQUEST['clm'] == "invkasse" && ! empty($f_patients_ipids))
	    	    {
	    	        $orderby = 'CONVERT(CONVERT(TRIM(AES_DECRYPT(' . $columnarray[$_REQUEST['clm']] . ', "' . Zend_Registry::get('salt') . '")) using utf8) using latin1) COLLATE latin1_german2_ci ' . $_REQUEST['ord'];
	    	
	    	        $drop = Doctrine_Query::create()
	    	        ->select('*,AES_DECRYPT(`' . $columnarray[$_REQUEST['clm']] . '`, "' . Zend_Registry::get('salt') . '") as company_name')
	    	        ->from('PatientHealthInsurance')
	    	        ->whereIn("ipid", $f_patients_ipids)
	    	        ->orderBy($orderby);
	    	        $droparray = $drop->fetchArray();
	    	
	    	        $f_patients_ipids = array();
	    	        foreach($droparray as $k_pat_hi => $v_pat_hi)
	    	        {
	    	            $f_patients_ipids[] = $v_pat_hi['ipid'];
	    	        }
	    	    }
	    	
	    	     
	    	
	    	    $storned_invoices = InvoiceSystem::get_storned_invoices($invoice_type,$clientid);
	    	    $unpaid_status = array("2","5");
	    	
	    	    $fdoc = Doctrine_Query::create()
	    	    ->select("count(*), IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort")
	    	    ->from('InvoiceSystem')
	    	    ->where("isdelete = 0 " . $where)
	    	    ->andWhere("invoice_type = ? ",$invoice_type)
	    	    ->andWhere("storno = '0'")
	    	    ->andWhere('completed_date != "0000-00-00 00:00:00"');
	    	    if(! empty($f_patients_ipids)){
	    	        $fdoc->andWhereIn('ipid', $f_patients_ipids);
	    	    }
	    	    if(!empty($unpaid_status)){
	    	        $fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
	    	    }
	    	    if(!empty($storned_invoices)){
	    	        $fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
	    	    }
	    	    $fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "'");
	    	    if($_REQUEST['clm'] != 'pat' && $_REQUEST['clm'] != 'invkasse')
	    	    {
	    	        $fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
	    	    }
	    	    else
	    	    {
	    	        //sort by patient sorted ipid order
	    	        $fdoc->orderBy('FIELD(ipid, "' . implode('","', $f_patients_ipids) . '") ');
	    	    }
	    	
	    	     
	    	     
	    	    //used in pagination of search results
	    	    $fdoc->andWhere("invoice_start BETWEEN DATE('" . $selected_period['start'] . "') and DATE('" . $selected_period['end'] . "')");
	    	    $fdocarray = $fdoc->fetchArray();
	    	    $limit = 500;
	    	    $fdoc->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '0000-00-00 00:00:00', create_date, completed_date)) as completed_date_sort");
	    	    $fdoc->where("isdelete = 0 " . $where . "");
	    	    $fdoc->andWhere("invoice_type = ? ",$invoice_type);
	    	    $fdoc->andWhere("storno = '0'");
	    	    $fdoc->andWhere("invoice_start BETWEEN '" . $selected_period['start'] . "' AND '" . $selected_period['end'] . "' ");
	    	    if(! empty($f_patients_ipids)){
	    	        $fdoc->andWhereIn('ipid', $f_patients_ipids);
	    	    }
	    	    if(!empty($unpaid_status)){
	    	        $fdoc->andWhereIN("status",$unpaid_status); // display only unpaid
	    	    }
	    	    if(!empty($storned_invoices)){
	    	        $fdoc->andWhereNotIN("id",$storned_invoices); // remove storned invoices from list
	    	    }
	    	    $fdoc->andWhere('completed_date != "0000-00-00 00:00:00"');
	    	    $fdoc->limit($limit);
	    	    $fdoc->offset($_REQUEST['pgno'] * $limit);
	    	    $fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
	    	
	    	    //get ipids for which we need health insurances
	    	    $inv_ipids = array();
	    	    foreach($fdoclimit as $k_inv => $v_inv)
	    	    {
	    	        $inv_ipids[] = $v_inv['ipid'];
	    	    }
	    	
	    	
	    	
	    	    //6. patients health insurance
	    	    $healthinsu_array = array();
	    	    $healthinsu = array();
	    	     
	    	    if(!empty($inv_ipids)){
	    	        $phelathinsurance = new PatientHealthInsurance();
	    	        $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($inv_ipids);
	    	    }
	    	
	    	     
	    	    if( ! empty($healthinsu_array)){
	    	
	    	        $company_ids = array();
	    	        foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
	    	        {
	    	            $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
	    	             
	    	            if($v_healthinsu['companyid'] != '0')
	    	            {
	    	                $company_ids[] = $v_healthinsu['companyid'];
	    	            }
	    	        }
	    	         
	    	        $health_insurance_master = array();
	    	        if( ! empty($company_ids)){
	    	            $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
	    	        }
	    	         
	    	        foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
	    	        {
	    	            if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
	    	            {
	    	                $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
	    	                 
	    	                if(strlen($healtharray['name']) > '0')
	    	                {
	    	                    $ins_name = $healtharray['name'];
	    	                }
	    	                else if(strlen($v_health_insurance[0]['company_name']) > '0')
	    	                {
	    	                    $ins_name = $v_health_insurance[0]['company_name'];
	    	                }
	    	            }
	    	             
	    	            //health insurance name
	    	            $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
	    	        }
	    	    }
	    	    $this->view->healthinsurances = $healthinsu;
	    	
	    	
	    	     
	    	     
	    	    $this->view->{"style" . $_GET['pgno']} = "active";
	    	    if(count($fdoclimit) > '0')
	    	    {
	    	        $grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "dtainvoicesystemlist.html");
	    	        $this->view->templates_grid = $grid->renderGrid();
	    	        $this->view->navigation = $grid->dotnavigation("dtainvoicesystemnavigation.html", 5, $_REQUEST['pgno'], $limit);
	    	    }
	    	    else
	    	    {
	    	        //no items found
	    	        $this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
	    	        $this->view->navigation = '';
	    	    }
	    	
	    	    $response['msg'] = "Success";
	    	    $response['error'] = "";
	    	    $response['callBack'] = "callBack";
	    	    $response['callBackParameters'] = array();
	    	    $response['callBackParameters']['templateslist'] = $this->view->render('dta/fetchdtainvoicessystemlist.html');
	    	
	    	    echo json_encode($response);
	    	    exit;
	    	}
	    	
	    	
	    	
	    	public function gather_dta_is_data($clientid,$invoice_type, $userid, $post)
	    	{
	    	    $patientmaster = new PatientMaster();
	    	     
	    	    //1. get all selected invoices data
	    	    $invoices_system = new InvoiceSystem();
	    	    $invoices_system_data = $invoices_system->get_multiple_invoices($invoice_type, $post['invoices'][$invoice_type]);
// 	    	dd($invoices_system_data);
	    	    if($invoices_system_data === false){
	    	        return array();
	    	    }
	    	
	    	    $invoices_patients = array();
	    	    $invoiced_days = array();
	    	    foreach($invoices_system_data as $k_inv => $v_inv)
	    	    {
	    	        $invoices_patients[] = $v_inv['ipid'];
	    	
	    	        $invoice_period_patient['start'] = date('Y-m-d', strtotime($v_inv['invoice_start']));
	    	        $invoice_period_patient['end'] = date('Y-m-d', strtotime($v_inv['invoice_end']));
	    	
	    	        $invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_start']));
	    	        $invoice_periods[] = date('Y-m-d', strtotime($v_inv['invoice_end']));
	    	
	    	        $patients_invoices_periods[$v_inv['ipid']] = $invoice_period_patient;
	    	
	    	        $invoice_period_sapv['start'] = date('Y-m-d', strtotime($v_inv['sapv_start']));
	    	        $invoice_period_sapv['end'] = date('Y-m-d', strtotime($v_inv['sapv_end']));
	    	
	    	
	    	        $invoiced_days[$v_inv['ipid']][$v_inv['id']] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_inv['invoice_start'])), date("Y-m-d", strtotime($v_inv['invoice_end'])), false);
	    	
	    	        $patients_invoices_sapv_periods[$v_inv['ipid']] = $invoice_period_sapv;
	    	        $invoices_system_data[$k_inv]['items_nosh'] = $v_inv['items'];
// 	    	        foreach($v_inv['items'] as $sh_item_data=>$items){
// 	    	            foreach($items as $k=>$itm){
// 	    	                $invoices_system_data[$k_inv]['items_nosh'][] = $itm;
// 	    	            }
// 	    	        }
	    	    }
	    	    $this->invoices_system_data = $invoices_system_data;
	    	     
	    	    asort($invoice_periods);
	    	    $invoice_periods_date = array_values($invoice_periods);
	    	    $invoice_period['start'] = $invoice_periods_date[0];
	    	    $invoice_period['end'] = end($invoice_periods_date);
	    	
	    	    //2. get all required client data
	    	    $clientdata = Pms_CommonData::getClientData($clientid);
	    	    $client_data['client']['ik'] = $clientdata[0]['institutskennzeichen'];
	    	    $client_data['client']['team_name'] = $clientdata[0]['team_name'];
	    	    $client_data['client']['name'] = $clientdata[0]['firstname'] . ' ' . $clientdata[0]['lastname'];
	    	    $client_data['client']['phone'] = $clientdata[0]['phone'];
	    	    $client_data['client']['fax'] = $clientdata[0]['fax'];
	    	
	    	
	    	    //3. get pflegestuffe in current period
	    	    $pflege = new PatientMaintainanceStage();
	    	    $pflege_arr = $pflege->get_multiple_patatients_mt_period($invoices_patients, $invoice_period['start'], $invoice_period['end']);
	    	
	    	    foreach($pflege_arr as $k_pflege => $v_pflege)
	    	    {
	    	        $grouped_pflege[$v_pflege['ipid']][] = $v_pflege;
	    	    }
	    	
	    	    foreach($grouped_pflege as $k_gpflege => $v_gpflege)
	    	    {
	    	        $last_pflege = end($v_gpflege);
	    	
	    	        if(strlen(trim($last_pflege['stage'])) != '0' && (is_numeric($last_pflege['stage']) || $last_pflege['stage'] == "3+"))
	    	        {
	    	            //$k_gpflege = patient epid
	    	            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = $last_pflege['stage'];
	    	        }
	    	        else
	    	        {
	    	            $pflegestuffe_data[$k_gpflege]['pat_pflegestuffe'] = '';
	    	        }
	    	    }
	    	
	    	    //4. get all involved patients required data
	    	    $patient_details = PatientMaster::get_multiple_patients_details_dta($invoices_patients);
	    	
	    	    foreach($patient_details as $k_pat_ipid => $v_pat_details)
	    	    {
	    	        $patient_data[$k_pat_ipid]['patient']['first_name'] = $v_pat_details['first_name'];
	    	        $patient_data[$k_pat_ipid]['patient']['last_name'] = $v_pat_details['last_name'];
	    	        $patient_data[$k_pat_ipid]['patient']['birthday'] = str_replace('-', '', $v_pat_details['birthd']);
	    	        $patient_data[$k_pat_ipid]['patient']['address'] = $v_pat_details['street1'];
	    	        $patient_data[$k_pat_ipid]['patient']['zip'] = $v_pat_details['zip'];
	    	        $patient_data[$k_pat_ipid]['patient']['city'] = $v_pat_details['city'];
	    	        $patient_data[$k_pat_ipid]['patient']['pflegestuffe'] = !empty($pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe']) ? $pflegestuffe_data[$k_pat_ipid]['pat_pflegestuffe'] : "";
                    $patient_data[$k_pat_ipid]['patient']['sex'] = $v_pat_details['sex'];//needed for demstepcare EDIFACT billing
                    $patient_data[$k_pat_ipid]['ipid']=$v_pat_details['ipid'];//needed for demstepcare EDIFACT billing
	    	    }
	    	
	    	    //4.1 get patients readmission details
	    	    $conditions['periods'][0]['start'] = '2009-01-01';
	    	    $conditions['periods'][0]['end'] = date('Y-m-d');
	    	    $conditions['client'] = $clientid;
	    	    $conditions['ipids'] = $invoices_patients;
	    	    $patient_days = Pms_CommonData::patients_days($conditions);
	    	
	    	    foreach($patient_days as $k_patd_ipid => $v_pat_details)
	    	    {
	    	        $patient_data[$k_patd_ipid]['patient']['admission_date'] = $v_pat_details['admission_days'][0];
	    	    }
	    	
	    	
	    	    //5. patients health insurance
	    	    $phelathinsurance = new PatientHealthInsurance();
	    	    $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($invoices_patients);
	    	
	    	    $status_int_array = array("M" => "1", "F" => "3", "R" => "5");
	    	    // ispc = M => 1 = Versicherungspflichtige und -berechtigte
	    	    // ispc = F => 3 = Familienversicherte
	    	    // ispc = R => 5 = Rentner in der Krankenversicherung der Rentner und deren familienversicherten Angehörige
	    	    //TODO-3528 Lore 12.11.2020
	    	    $modules = new Modules();
	    	    $extra_healthinsurance_statuses = $modules->checkModulePrivileges("247", $clientid);
	    	    if($extra_healthinsurance_statuses){
	    	        $status_int_array += array(
	    	            "00" => "00",          //"Gesamtsumme aller Stati",
	    	            "11" => "11",          //"Mitglieder West",
	    	            "19" => "19",          //"Mitglieder Ost",
	    	            "31" => "31",          //"Angehörige West",
	    	            "39" => "39",          //"Angehörige Ost",
	    	            "51" => "51",          //"Rentner West",
	    	            "59" => "59",          //"Rentner Ost",
	    	            "99" => "99",          //"nicht zuzuordnende Stati",
	    	            "07" => "07",          //"Auslandsabkommen"
	    	        );
	    	    }
	    	    //.
	    	    
	    	    foreach($healthinsu_array as $k_healthinsu => $v_healthinsu)
	    	    {
	    	        $patients_healthinsu[$v_healthinsu['ipid']][] = $v_healthinsu;
	    	
	    	        if($v_healthinsu['companyid'] != '0')
	    	        {
	    	            $company_ids[] = $v_healthinsu['companyid'];
	    	        }
	    	    }
	    	
	    	    if(!empty($company_ids)){
	    	        $health_insurance_master = HealthInsurance::get_multiple_healthinsurances($company_ids);
	    	    }
	    	
	    	
	    	    //get health insurance subdivizions
	    	    $symperm = new HealthInsurancePermissions();
	    	    $divisions = $symperm->getClientHealthInsurancePermissions($clientid);
	    	
	    	    if($divisions)
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
	    								->from("PatientHealthInsurance2Subdivisions");
	    	        if(!empty($company_ids)){
	    	            $hi2s->whereIn("company_id", $company_ids);
	    	        }
	    	        $hi2s->andWhereIn("ipid", $invoices_patients);
	    	        $hi2s_arr = $hi2s->fetchArray();
	    	    }
	    	
	    	    if($hi2s_arr)
	    	    {
	    	        foreach($hi2s_arr as $k_subdiv => $v_subdiv)
	    	        {
	    	            if($v_subdiv['subdiv_id'] == "3")
	    	            {
	    	                $subdivisions[$v_subdiv['ipid']] = $v_subdiv;
	    	            }
	    	        }
	    	    }
	    	
	    	    foreach($patients_healthinsu as $k_ipid_hi => $v_health_insurance)
	    	    {
	    	        if(!empty($v_health_insurance[0]['companyid']) && $v_health_insurance[0]['companyid'] != 0)
	    	        {
	    	            $healtharray = $health_insurance_master[$v_health_insurance[0]['companyid']];
	    	
	    	            if(strlen($v_health_insurance[0]['institutskennzeichen']) == 0)
	    	            {
	    	                $v_health_insurance[0]['institutskennzeichen'] = $healtharray['iknumber'];
	    	            }
	    	
	    	            if(strlen($healtharray['name']) > '0')
	    	            {
	    	                $ins_name = $healtharray['name'];
	    	            }
	    	            else if(strlen($v_health_insurance[0]['company_name']) > '0')
	    	            {
	    	                $ins_name = $v_health_insurance[0]['company_name'];
	    	            }
	    	        }
	    	
	    	        //health insurance name
	    	        $healthinsu[$k_ipid_hi]['company_name'] = $ins_name;
	    	
	    	        //Versichertennummer
	    	        $healthinsu[$k_ipid_hi]['health_insurance_no'] = $v_health_insurance[0]['insurance_no'];
	    	
	    	        //Institutskennzeichen
	    	        $healthinsu[$k_ipid_hi]['health_insurance_ik'] = $v_health_insurance[0]['institutskennzeichen'];
	    	
	    	        //Billing IK - Abrechnungs IK -- RWH - ISPC-1345 // 150716
	    	        $healthinsu[$k_ipid_hi]['health_insurance_billing_ik'] = $subdivisions[$k_ipid_hi]['ikbilling'];
	    	
	    	        // Health insurance status - ISPC- 1368 // 150611
	    	        $healthinsu[$k_ipid_hi]['health_insurance_status'] = !empty($v_health_insurance[0]['insurance_status']) ? $status_int_array[$v_health_insurance[0]['insurance_status']]: "";
	    	    }
	    	
	    	
	    	
	    	    //6. get all billable nr actions - (saved or system)
	    	    $visits_data = $this->get_is_related_visits($clientid, $invoice_type, $invoices_patients, $invoice_period,$patients_invoices_periods);
	    	    
	    	    
	    	    
	    	    if($_REQUEST['dbgz'])
	    	    {
	    	        print_r($visits_data);
	    	        exit;
	    	    }
	    	     
	    	    //7. get (HD) main diagnosis
	    	    $main_abbr = "'HD'";
	    	    $main_diag = DiagnosisType::getDiagnosisTypes($clientid, $main_abbr);
	    	
	    	    foreach($main_diag as $key => $v_diag)
	    	    {
	    	        $type_arr[] = $v_diag['id'];
	    	    }
	    	
	    	    $pat_diag = new PatientDiagnosis();
	    	    $dianoarray = $pat_diag->getFinalData('"' . implode('","', $invoices_patients) . '"', '"' . implode('","', $type_arr) . '"', true); //set last param true to accept a list of ipids
	    	
	    	    foreach($dianoarray as $k_diag => $v_diag)
	    	    {
	    	        //append diagnosis in patient data
	    	        $diano_arr[$v_diag['ipid']]['icdcode_' . $k_diag] = $v_diag['icdnumber'];
	    	//        $diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode($v_diag['diagnosis'], NULL, "utf-8");
	    	        //ISPC-2489 Lore 26.11.2019
	    	        $diano_arr[$v_diag['ipid']]['icdname_' . $k_diag] = html_entity_decode(mb_substr($v_diag['diagnosis'],0,70,'HTML-ENTITIES'), NULL, "utf-8");
	    	        
	    	        $patient_data[$v_diag['ipid']]['diagnosis'] = $diano_arr[$v_diag['ipid']];
	    	    }
	    	     
	    	    //8. get user data
	    	    $user_details = User::getUserDetails($userid);
	    	
	    	    //9. reloop the invoices data array to create final array!
	    	    $master_data = array();
	    	    foreach($invoices_system_data as $k_invoice => $v_invoice)
	    	    {
	    	        if(!$master_data['invoice_' . $k_invoice])
	    	        {
	    	            $master_data['invoice_' . $k_invoice] = array();
	    	        }
	    	
	    	        $master_data['invoice_' . $k_invoice] = array_merge($master_data['invoice_' . $k_invoice], $client_data, $patient_data[$v_invoice['ipid']]);
	    	
	    	        $master_data['invoice_' . $k_invoice]['number'] = $v_invoice['prefix'] . $v_invoice['invoice_number'] . ':0';
	    	        $master_data['invoice_' . $k_invoice]['date'] = date('Ymd', strtotime($v_invoice['create_date']));
	    	        $master_data['invoice_' . $k_invoice]['ammount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
	    	        $master_data['invoice_' . $k_invoice]['month'] = date('Ym', time());
	    	        $master_data['invoice_' . $k_invoice]['hi_name'] = $healthinsu[$v_invoice['ipid']]['company_name'];
	    	        $master_data['invoice_' . $k_invoice]['hi_insurance_no'] = $healthinsu[$v_invoice['ipid']]['health_insurance_no'];
	    	        $master_data['invoice_' . $k_invoice]['hi_insurance_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_ik'];
	    	        $master_data['invoice_' . $k_invoice]['hi_recipient_ik'] = $healthinsu[$v_invoice['ipid']]['health_insurance_billing_ik'];
	    	        $master_data['invoice_' . $k_invoice]['hi_insurance_status'] = $healthinsu[$v_invoice['ipid']]['health_insurance_status'];
	    	        $master_data['invoice_' . $k_invoice]['betriebsstattennummer'] = $clientdata[0]['betriebsstattennummer'];
	    	
	    	        $master_data['invoice_' . $k_invoice]['sapv']['start_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_start_date'];
	    	        $master_data['invoice_' . $k_invoice]['sapv']['end_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_end_date'];
	    	
	    	        $master_data['invoice_' . $k_invoice]['sapv']['approved_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_date'];
	    	        $master_data['invoice_' . $k_invoice]['sapv']['approved_nr'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_approved_nr'];
	    	
	    	        $master_data['invoice_' . $k_invoice]['sapv']['created_date'] = $visits_data[$v_invoice['ipid']]['sapv']['sapv_create_date'];
	    	
	    	        $inv_items = array();
	    	        foreach($v_invoice['items_nosh'] as $k_item => $v_item)
	    	        {
	    	            $inv_actions = array();
	    	
	    	            $inv_actions = array();
	    	            if( $v_item['custom'] == 0 && $visits_data[$v_invoice['ipid']][$v_item['shortcut']]){
	    	                
	    	                if($invoice_type =="demstepcare_invoice"){
    	    	                $ident = "";
    	    	                foreach($visits_data[$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
    	    	                {
    	    	                    $ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].' '.$v_action['start_time'].'-'.$v_action['end_time'];
    	    	                      
    	    	                    if( ! in_array($ident,$added2xml[$k_invoice])
    	    	                        &&  $actions_count <= $v_item['qty']
    	    	                        && in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )  
    	    	                        ){
    	    	                        
    	    	                        
    	    	                        $inv_actions['dta_id'] = $v_action['dta_id'];
    	    	                        $inv_actions['dta_name'] = $v_action['dta_name'];
    	    	                        $inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
    	    	                        $inv_actions['ammount'] = str_pad(number_format($v_action['qty'], '2', ',', ''), "7", "0", STR_PAD_LEFT);
    	    	                        $inv_actions['day'] = date('Ymd', strtotime($v_action['day']));
    	    	                        $inv_actions['start_time'] = $v_action['start_time'];
    	    	
    	    	                        if(strlen($v_action['end']) > '0')
    	    	                        {
    	    	                            $inv_actions['end_time'] = date('Hi', strtotime($v_action['end']));
    	    	                        } else{
    	    	                            $inv_actions['end_time'] = $v_action['end_time'];
    	    	                        }
    	    	
    	    	                        $inv_items['actions']['action_' . $k_action] = $inv_actions;
    	    	                         
    	    	                        $added2xml[$k_invoice][] = $ident ;
    	    	                        
    	    	                        
    	    	                        
    	    	                    }
    	    	                }
	    	                    
	    	                } else{
	    	                    
    	    	                $ident = "";
    	    	                foreach($visits_data[$v_invoice['ipid']][$v_item['shortcut']]['actions'] as $k_action => $v_action)
    	    	                {
    	    	                    $ident = $k_action.'-'.$v_item['shortcut'].$v_action['day'].' '.$v_action['start_time'].'-'.$v_action['end_time'];
    	    	                      
    	    	                    if( ! in_array($ident,$added2xml[$k_invoice])
    	    	                        &&  $actions_count <= $v_item['qty']
    	    	                        && in_array(date("Y-m-d",strtotime($v_action['day'])),$invoiced_days[$v_invoice['ipid']][$v_invoice['id']] )  
    	    	                        && ($v_action['location_type'] == $v_item['location_type']) 
    	    	                        ){
    	    	                        
    	    	                        
    	    	                        $inv_actions['dta_id'] = $v_action['dta_id'];
    	    	                        $inv_actions['dta_name'] = $v_action['dta_name'];
    	    	                        $inv_actions['price'] = number_format($v_action['dta_price'], '2', ',', '');
    	    	                        $inv_actions['ammount'] = str_pad(number_format($v_action['qty'], '2', ',', ''), "7", "0", STR_PAD_LEFT);
    	    	                        $inv_actions['day'] = date('Ymd', strtotime($v_action['day']));
    	    	                        $inv_actions['start_time'] = $v_action['start_time'];
    	    	
    	    	                        if(strlen($v_action['end']) > '0')
    	    	                        {
    	    	                            $inv_actions['end_time'] = date('Hi', strtotime($v_action['end']));
    	    	                        } else{
    	    	                            $inv_actions['end_time'] = $v_action['end_time'];
    	    	                        }
    	    	
    	    	                        $inv_items['actions']['action_' . $k_action] = $inv_actions;
    	    	                         
    	    	                        $added2xml[$k_invoice][] = $ident ;
    	    	                        
    	    	                        
    	    	                        
    	    	                    }
    	    	                }
                            }
	    	                $master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
	    	                $inv_actions = array();
	    	                $inv_items = array();
	    	                
	    	            } elseif($v_item['custom'] == 1) {
	    	                
                            $custom_ident = $v_item['id'];
	    	                  
                            if( ! in_array($custom_ident,$added_custom2xml[$k_invoice])){
                                
                                $action_date = date('Y-m-d', strtotime($v_invoice['completed_date']));
                                $inv_actions['dta_id'] = "";
                                $inv_actions['dta_name'] = $v_item['description'];
                                $inv_actions['price'] = number_format($v_item['price'], '2', ',', '');
                                $inv_actions['ammount'] = str_pad(number_format($v_item['qty'], '2', ',', ''), "7", "0", STR_PAD_LEFT);
                                $inv_actions['day'] = date('Ymd', strtotime($v_invoice['completed_date']));
                                $inv_actions['start_time'] = date('Hi', strtotime($action_date.' 00:00:00'));
                                $inv_actions['end_time'] = date('Hi', strtotime($action_date.' 23:59:59'));
                                
                                $inv_items['actions']['action_' . $k_action] = $inv_actions;
                                 
                                $added_custom2xml[$k_invoice][] = $custom_ident ;
                            }
                            
                            $master_data['invoice_' . $k_invoice]['items']['item_' . $k_item] = $inv_items;
                            $inv_actions = array();
                            $inv_items = array();
	    	                
	    	            }
	    	        }
	    	        $master_data['invoice_' . $k_invoice]['overall_amount'] = number_format($v_invoice['invoice_total'], '2', ',', '');
	    	    }

	    	    return $master_data;
	    	}
	    	
	    	
	    	
	    	private function get_is_related_visits($clientid, $invoice_type, $invoices_patients, $selected_period,$patients_invoices_periods)
	    	{
	    	     
	    	    $patientmaster = new PatientMaster();
	    	    //Client Hospital Settings START
	    	    $conditions['periods'][0]['start'] = $selected_period['start'];
	    	    $conditions['periods'][0]['end'] = $selected_period['end'];
	    	    $conditions['client'] = $clientid;
	    	    $conditions['ipids'] = $invoices_patients;
	    	    $patient_days = Pms_CommonData::patients_days($conditions);
	    	
	    	
	    	    $pateint_invoiced_period_days = array();
	    	    $pateint_invoiced_period_days_arr = array();
	    	    $sapv_invoiced_period_days = array();
	    	    foreach($invoices_patients as $iipid){
	    	        $sapv_invoiced_period_days[$iipid] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end']);
	    	        $pateint_invoiced_period_days [$iipid] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end']);
	    	        $pateint_invoiced_period_days_arr [$iipid]['days'] = $patientmaster->getDaysInBetween($patients_invoices_periods[$iipid]['start'], $patients_invoices_periods[$iipid]['end'],false,"d.m.Y");
	    	        $pateint_invoiced_period_days_arr [$iipid]['start'] = $patients_invoices_periods[$iipid]['start'];
	    	        $pateint_invoiced_period_days_arr [$iipid]['end'] = $patients_invoices_periods[$iipid]['end'];
	    	
	    	        array_walk($pateint_invoiced_period_days [$iipid], function(&$value) {
	    	            $value = date("d.m.Y",strtotime($value));
	    	        });
	    	    }
	    	    
	    	    //TODO-2904 Ancuta 14.02.2020
	    	    $modules = new Modules();
	    	    if($modules->checkModulePrivileges("199", $clientid))
	    	    {
	    	        $has_bill_zero_option = "1";
	    	    }
	    	    else
	    	    {
	    	        $has_bill_zero_option = "0";
	    	    }
	    	    
	    	    
	    	    $healthinsu_multi_array = array();
	    	    if(!empty($invoices_patients)){
	           	    $phelathinsurance = new PatientHealthInsurance();
    	    	    $healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($invoices_patients, true);
	    	    }
	    	    // --
	    	    
	    	    
	    	    $patient_action_days[$v_ipid]['actions'] = array_values($action_days[$v_ipid]['actions']);
	    	    array_walk($patient_action_days[$v_ipid]['actions'], function(&$value) {
	    	        $value = strtotime($value);
	    	    });
	    	         
	    	        //find if there is a sapv for current period START!
	    	        $dropSapv = Doctrine_Query::create()
	    	        ->select('*')
	    	        ->from('SapvVerordnung')
	    	        ->whereIn('ipid', $invoices_patients)
	    	        ->andWhere('verordnungam != "0000-00-00 00:00:00"')
	    	        ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
	    	        ->andWhere('isdelete=0')
	    	        ->orderBy('verordnungam ASC');
	    	        $droparray = $dropSapv->fetchArray();
	    	
	    	        $all_sapv_days = array();
	    	        $temp_sapv_days = array();
	    	        $s=0;
	    	        foreach($droparray as $k_sapv => $v_sapv)
	    	        {
	    	            $r1['start'][$v_sapv['ipid']][$s] = "";
	    	            $r1['end'][$v_sapv['ipid']][$s] = "";
	    	             
	    	            $r2['start'][$v_sapv['ipid']][$s] = "";
	    	            $r2['end'][$v_sapv['ipid']][$s] = "";
	    	
	    	
	    	             
	    	            if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
	    	                // no sapv taken here - becouse it is considered to be fully denied
	    	            }
	    	            else
	    	            {
	    	                $r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
	    	                $r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
	    	
	    	                $r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
	    	                $r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
	    	
	    	                $invoiced_sapv_type[$v_sapv['ipid']][] = $v_sapv;
	    	
	    	                $sapv_period2type_arr[$v_sapv['ipid']][date('Y-m-d',strtotime($v_sapv['verordnungam']))] = $sv_data['verordnet'];
	    	
	    	                if(Pms_CommonData::isintersected($r1['start'][$v_sapv['ipid']][$s], $r1['end'][$v_sapv['ipid']][$s], $r2['start'][$v_sapv['ipid']][$s] , $r2['end'][$v_sapv['ipid']][$s])  )
	    	                {
	    	                    $invoiced_sapv[$v_sapv['ipid']] = $v_sapv['id'];
	    	                    // 		    				$invoiced_sapv_type[$v_sapv['ipid']]['current'][$v_sapv['id']] = $v_sapv['verordnet'];
	    	
	    	                    //aditional data from sapv which was added on 16.10.2014
	    	                    if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
	    	                    {
	    	                        $sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
	    	                    }
	    	
	    	                    if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
	    	                        $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
	    	                    } else{
	    	                        $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
	    	                    }
	    	
	    	
	    	                    if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
	    	                    {
	    	                        $v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
	    	                    }
	    	
	    	
	    	                    $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
	    	                    $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
	    	
	    	                    $temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
	    	
	    	
	    	                    //aditional data from sapv which was added on 31.10.2014
	    	                    $sapv_data[$v_sapv['ipid']][$s]['status'] = $v_sapv['status'];
	    	                    $sapv_data[$v_sapv['ipid']][$s]['sapv_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
	    	                    $sapv_data[$v_sapv['ipid']][$s]['sapv_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
	    	                    $sapv_data[$v_sapv['ipid']][$s]['create_date'] = date('d.m.Y', strtotime($v_sapv['create_date']));
	    	
	    	                    foreach($temp_sapv_days as $k_tmp_sapv => $v_tmp_sapv)
	    	                    {
	    	                        if(!$days2verordnet[$v_sapv['ipid']][$v_tmp_sapv])
	    	                        {
	    	                            $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array();
	    	                        }
	    	
	    	                        $current_verordnet = explode(',', $v_sapv['verordnet']);
	    	                        $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_merge($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv], $current_verordnet);
	    	
	    	                        asort($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]);
	    	                        $days2verordnet[$v_sapv['ipid']][$v_tmp_sapv] = array_values(array_unique($days2verordnet[$v_sapv['ipid']][$v_tmp_sapv]));
	    	                    }
	    	
	    	                    $s++;
	    	                }
	    	            }
	    	        }
	    	         
	    	        $sapv_on_invoice = array();
	    	        foreach($invoiced_sapv_type as $k=>$sd){
	    	            foreach($sd as $k=>$sdata){
	    	
	    	                $r2['start'][$sdata['ipid']]  = strtotime($patients_invoices_periods[ $sdata['ipid'] ]['start']);
	    	                $r2['end'][$sdata['ipid']]  = strtotime($patients_invoices_periods[$sdata['ipid']]['end']);
	    	
	    	
	    	                if(Pms_CommonData::isintersected(strtotime(date('Y-m-d', strtotime($sdata['verordnungam']))),strtotime(date('Y-m-d', strtotime($sdata['verordnungbis']))), $r2['start'][$sdata['ipid']] , $r2['end'][$sdata['ipid']])  )
	    	                {
	    	
	    	                    if($sdata['verordnet'] == "1"){
	    	                        $sapv_on_invoice[$sdata['ipid']]['only_be']= '1';
	    	                    } else{
	    	                        $sapv_on_invoice[$sdata['ipid']]['only_be']= '0';
	    	                    }
	    	
	    	                    $sapv_on_invoice[$sdata['ipid']]['sapv_start']= date('d.m.Y',strtotime($sdata['verordnungam']));
	    	                    $sapv_on_invoice[$sdata['ipid']]['sapv_end']= date('d.m.Y',strtotime($sdata['verordnungbis']));
	    	                    $sapv_on_invoice[$sdata['ipid']]['create_date']= date('d.m.Y',strtotime($sdata['create_date']));
	    	
	    	                    //aditional data from sapv which was added on 16.10.2014
	    	                    if($sdata['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($sdata['approved_date'])) != '1970-01-01' && $sdata['status'] == '2')
	    	                    {
	    	
	    	                        $sapv_on_invoice[$sdata['ipid']]['approved_date'] = date('d.m.Y', strtotime($sdata['approved_date']));
	    	                        $sapv_on_invoice[$sdata['ipid']]['approved_number'] = $sdata['approved_number'];
	    	                    }
	    	                }
	    	            }
	    	        }
	    	        	
	    	         
	    	        //get products - system generated and saved
	    	        $products_array = array();
	    	        if($invoice_type == "nr_invoice"){
    	    	        $control_table = new NordrheinBilling ();
    	    	        $products_nr = $control_table->nr_billable_actions($clientid, $invoices_patients,$selected_period,$pateint_invoiced_period_days_arr);
    	    	        if(!empty($products_nr)){
    	    	            foreach($products_nr['invoices'] as $pipid=>$produc_data){
    	    	                foreach($produc_data as $date=>$sh_data){
    	    	                    foreach($sh_data as $shortcut=>$shd){
    	    	                        if($shd['qty']!=0){
//         	    	                      $products_array[$pipid][$shortcut.'-'.$shd['location_type']][$date] = $shd;
        	    	                      $products_array[$pipid][$shortcut][$date] = $shd;
    	    	                        }
    	    	                        
    	    	                    }
    	    	                }
    	    	            }
    	    	        }
	    	        }
	    	        //ISPC-2461
	    	        elseif($invoice_type == "demstepcare_invoice"){
 
    	    	        $dsc_billing_obj = new DemstepcareControl();
    	    	        $dsc_billable_actions = $dsc_billing_obj->dsc_actions($invoices_patients,$pateint_invoiced_period_days_arr,"invoice");
    	    	         
    	    	        $demstepcare_data = array();
    	    	        foreach($dsc_billable_actions as $nr_ipid=>$nr_dates ){
    	    	            foreach($nr_dates  as $date => $sh_vals){
    	    	                foreach($sh_vals as $sh=>$sh_Data){
    	    	                    $products_array[$nr_ipid][$sh][$date] = $sh_Data;
    	    	                }
    	    	            }
    	    	        }
	    	        }
	    	        

	    	 		    	if($_REQUEST['dbgz'])
	    	 		    	{
	    	 		    	    print_r($products_array);
	    	 		    	    print_r("\npatientsz");
	    	 		    	    print_r($invoices_patients);
	    	 		    	    print_r("\n sapv");
	    	 		    	    print_r($sapv_on_invoice);
	    	 		    	}
	    	
	    	 		    	 
	    	 		    	$nr_lang = $this->view->translate('nr_invoice_lang');
	    	 		    	 
	    	 		    	// 		    	dd($nr_lang);
	    	 		    	 
	    	 		    	$products = array();
	    	 		    	foreach($invoices_patients as $patient)
	    	 		    	{
	    	
	    	 		    	    foreach($products_array[$patient] as $shortcut=>$sh_days_array){
	    	 		    	         
	    	 		    	        foreach($sh_days_array as $action_date => $sh_value){
	    	 		    	            if($invoice_type == "demstepcare_invoice"){
	    	 		    	                $sh_value['quarterly_date'] = date("Ymd",strtotime($sh_value['quarterly_date']));
    	    	 		    	            $products[$patient][$shortcut][$action_date]['location_type'] = $sh_value['location_type'];
    	    	 		    	            
    	    	 		    	            $products[$patient][$shortcut][$action_date]['qty'] = $sh_value['qty'];
    	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_name'] = $sh_value['description'];
    	    	 		    	            
        	    	 		    	        $products[$patient][$shortcut][$action_date]['dta_price']= $sh_value['dta_price'];
    	    	 		    	            //TODO-2904 Ancuta 14.02.2020 
        	    	 		    	        if($has_bill_zero_option == 1 && !empty($healthinsu_multi_array[$patient]) && !empty($healthinsu_multi_array[$patient]['company']) && $healthinsu_multi_array[$patient]['company']['demstepcare_billing'] == "yes" ){
        	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_price']= "0.00";
    	    	 		    	            }else{
        	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_price']= $sh_value['dta_price'];
    	    	 		    	            }
    	    	 		    	            // --
    	    	 		    	            
    	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_id']= $sh_value['dta_id'];
    	    	 		    	            $products[$patient][$shortcut][$action_date]['day']=   $sh_value['quarterly_date'];
    
    	    	 		    	            $products[$patient][$shortcut][$action_date]['start']= (!empty($sh_value['quarterly_date'])) ? $sh_value['quarterly_date'] :     $sh_value['quarterly_date'].' 00:00:00';
    	    	 		    	            $products[$patient][$shortcut][$action_date]['start_time']= (!empty($sh_value['time_from'])) ? $sh_value['time_from'] :    date('Hi', strtotime($sh_value['quarterly_date'].' 00:00:00'));
    	    	 		    	            
    	    	 		    	            $products[$patient][$shortcut][$action_date]['end']= (!empty($sh_value['end_date'])) ? $sh_value['end_date'] :     $sh_value['quarterly_date'].' 23:59:59';
    	    	 		    	            $products[$patient][$shortcut][$action_date]['end_time']= (!empty($sh_value['time_till'])) ? $sh_value['time_till'] :   date('Hi', strtotime($sh_value['quarterly_date'].' 23:59:59'));

	    	 		    	                
	    	 		    	            } else{
	    	 		    	                
    	    	 		    	            $products[$patient][$shortcut][$action_date]['location_type'] = $sh_value['location_type'];
    	    	 		    	            
    	    	 		    	            $products[$patient][$shortcut][$action_date]['qty'] = $sh_value['qty'];
    	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_name'] = $nr_lang['products'][$shortcut.'_label'];
    	    	 		    	            
    	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_price']= $sh_value['dta_price'];
    	    	 		    	            $products[$patient][$shortcut][$action_date]['dta_id']= $sh_value['dta_id'];
    	    	 		    	            $products[$patient][$shortcut][$action_date]['day']= $action_date;
    
    	    	 		    	            $products[$patient][$shortcut][$action_date]['start']= (!empty($sh_value['start_date'])) ? $sh_value['start_date'] :     $action_date.' 00:00:00';
    	    	 		    	            $products[$patient][$shortcut][$action_date]['start_time']= (!empty($sh_value['time_from'])) ? $sh_value['time_from'] :    date('Hi', strtotime($action_date.' 00:00:00'));
    	    	 		    	            
    	    	 		    	            $products[$patient][$shortcut][$action_date]['end']= (!empty($sh_value['end_date'])) ? $sh_value['end_date'] :     $action_date.' 23:59:59';
    	    	 		    	            $products[$patient][$shortcut][$action_date]['end_time']= (!empty($sh_value['time_till'])) ? $sh_value['time_till'] :   date('Hi', strtotime($action_date.' 23:59:59'));
	    	 		    	            }
	    	 		    	        }
	    	 		    	    }
	    	 		    	}
	    	 		    		
	    	 		    	if($_REQUEST['dbgz'])
	    	 		    	{
	    	 		    	    var_dump($products);
	    	 		    	}
	    	 		    	 
	    	 		    	foreach($products as $k_patient_ipid => $patient_sh_details)
	    	 		    	{
	    	 		    	    foreach($patient_sh_details as $sh=>$date_entry){
	    	 		    	        foreach($date_entry as $Ymd=>$date_details){
	    	 		    	            $data[$k_patient_ipid][$sh]['actions'][] = $date_details;
	    	 		    	        }
	    	 		    	    }
	    	 		    	}
	    	
	    	 		    	foreach($sapv_data as $k_ipid =>$sapvdata){
	    	 		    	    $sapv_data[$k_ipid] = end($sapvdata);
	    	 		    	}
	    	 		    	 
	    	 		    	foreach($invoices_patients as $k_pat => $v_ipid)
	    	 		    	{
	    	 		    	    $triggered[$v_ipid] = $data[$v_ipid];
	    	 		    	     
	    	 		    	    $triggered_sapv['sapv_start_date'] = !empty($sapv_on_invoice[$v_ipid]['sapv_start']) ? $sapv_on_invoice[$v_ipid]['sapv_start'] :"";
	    	 		    	    $triggered_sapv['sapv_end_date'] =  !empty($sapv_on_invoice[$v_ipid]['sapv_end']) ? $sapv_on_invoice[$v_ipid]['sapv_end'] :"";
	    	 		    	    $triggered_sapv['sapv_create_date'] =  !empty($sapv_on_invoice[$v_ipid]['create_date']) ? $sapv_on_invoice[$v_ipid]['create_date'] :"";
	    	 		    	    $triggered_sapv['sapv_approved_date'] =  !empty($sapv_on_invoice[$v_ipid]['approved_date']) ? $sapv_on_invoice[$v_ipid]['approved_date'] :"";
	    	 		    	    $triggered_sapv['sapv_approved_nr'] =  !empty($sapv_on_invoice[$v_ipid]['approved_number']) ? $sapv_on_invoice[$v_ipid]['approved_number'] :"";
	    	 		    	     
	    	 		    	    $triggered[$v_ipid]['sapv'] = $triggered_sapv;
	    	 		    	}
	    	
	    	 		    	 
	    	 		    	return $triggered;
	    	}
	    	
	    	
	    	
	    	
	    	
}