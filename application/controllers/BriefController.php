<?php

	class BriefController extends Zend_Controller_Action {

		public function init()
		{
			/* Initialize action controller here */

//			autoload phpdocxpdf only for brief controller
			//spl_autoload_register(array('AutoLoader', 'autoloadPdf'));  // Alex+Ancuta- commented on 18.11.2019- New phpdocx 9.5 added

			//ISPC-791 secrecy tracker
			$user_access = PatientPermissions::document_user_acces();

			//Check patient permissions on controller and action
			$patient_privileges = PatientPermissions::checkPermissionOnRun();

			if(!$patient_privileges)
			{
				$this->_redirect(APP_BASE . 'error/previlege');
			}
		}

		public function addtemplateAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			$this->view->default_recipient_values = Pms_CommonData::template_default_recipients();

			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_BriefTemplate();

				$post = $_POST;
				$post['template_filename'] = $_SESSION['template_filename'];
				$post['template_filetype'] = $_SESSION['template_filetype'];
				$post['template_filepath'] = $_SESSION['template_filepath'];

				if($upload_form->validate($post))
				{
					$upload_form->insert_template_data($post);
					$this->_redirect(APP_BASE . 'brief/listtemplates?flg=suc_add');
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}

				$this->resetuploadvars();
			}
		}

		public function edittemplateAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$upload_form = new Application_Form_BriefTemplate();
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->view->default_recipient_values = Pms_CommonData::template_default_recipients();

			if($_REQUEST['tid'] > '0')
			{
				$template_id = trim(rtrim($_REQUEST['tid']));

				if($this->getRequest()->isPost())
				{
					$post = $_POST;
					$post['template_id'] = $template_id;

					//used to cleanup in edit mode(file uploaded but check was deselected)
					$post['template_filepath'] = $_SESSION['template_filepath'];
					$post['template_filetype'] = $_SESSION['template_filetype'];

					//reset upload vars(if any) if change template is not checked
					if($post['change_file'] != '1')
					{
						$this->resetuploadvars();
					}

					if($upload_form->validate($post))
					{
						$upload_form->update_template_data($clientid, $post);
						$this->_redirect(APP_BASE . 'brief/listtemplates?flg=suc_edt');
						exit;
					}
					else
					{
						$this->retain_values($post);
					}
					$this->resetuploadvars();
				}


				//load data
				$template_data = BriefTemplates::get_template($clientid, $template_id);
				if($template_data)
				{
					$this->retain_values($template_data[0]);
				}
				else
				{
					$this->redirect(APP_BASE . 'brief/listtemplates?flg=inv');
					exit;
				}
			}
			else
			{
				$this->redirect(APP_BASE . 'brief/listtemplates?flg=inv');
				exit;
			}
		}

		public function deletetemplateAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->viewRenderer->setNoRender();

			$fdoc = Doctrine::getTable('BriefTemplates')->findOneByIdAndClientid($_REQUEST['tid'], $clientid);
			if($fdoc)
			{
				$fdoc->isdeleted = 1;
				$fdoc->save();

				$this->redirect(APP_BASE . 'brief/listtemplates?flg=del_suc');
				exit;
			}
			else
			{
				$this->redirect(APP_BASE . 'brief/listtemplates?flg=del_err');
				exit;
			}
		}

		public function listtemplatesAction()
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
		}

		public function fetchtemplatelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			$this->view->default_recipient_values = Pms_CommonData::template_default_recipients();

			$columnarray = array(
				"crd" => "create_date",
				"id" => "id",
				"ti" => "title",
				"ft" => "file_type"
			);

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$client_users_res = User::getUserByClientid($clientid, 0, true);

			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
			}

			$this->view->client_users = $client_users;

			if($clientid > 0)
			{
// 				$where = ' and clientid=' . $logininfo->clientid;
				$clientid = $logininfo->clientid;
			}
			else
			{
// 				$where = ' and clientid=0';
				$clientid = "0";
			}

			if($user_type == "CA" || $user_type == "SA")
			{
				$this->view->reveal_actions_col = '1';
			}
			else
			{
				$this->view->reveal_actions_col = '0';
			}

			$fdoc = Doctrine_Query::create()
				->select('count(*)')
				->from('BriefTemplates')
				->where("isdeleted = ? ", 0)
				->andWhere("clientid = ?", $clientid)
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

			//used in pagination of search results
			if(!empty($_REQUEST['val']))
			{
				$fdoc->andWhere("(title != '' OR file_type != '')");
				$fdoc->andWhere("(title like '%" . trim($_REQUEST['val']) . "%' OR file_type like '%" . trim($_REQUEST['val']) . "%')");
			}
			$fdocarray = $fdoc->fetchArray();

			$limit = 50;
			$fdoc->select('id,title,file_type,recipient,create_user,create_date');
			$fdoc->where("isdeleted = ? ", 0);
			$fdoc->andWhere("clientid = ?", $clientid);
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("(title != '' or file_type != '')");
			  //$fdoc->andWhere("(title like '%" . trim($_REQUEST['val']) . "%' OR file_type like '%" . trim($_REQUEST['val']) . "%')");
				$fdoc->andWhere("(title like  ? OR file_type like ?)", array("%".trim($_REQUEST['val'])."%","%".trim($_REQUEST['val'])."%" ) );
			}
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);
			
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());


			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "templateslist.html");
				$this->view->templates_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("templatesnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->templates_grid = '<tr><td colspan="5" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['templateslist'] = $this->view->render('brief/fetchtemplatelist.html');

			echo json_encode($response);
			exit;
		}

		public function gettemplateAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($_REQUEST['tid'])
			{
				$template_id = trim(rtrim($_REQUEST['tid']));
				$template_data = BriefTemplates::get_template($clientid, $template_id, '1');
				$file_check_path = 'brief_templates/' . $template_data['0']['file_path'];

				if($template_data && is_file($file_check_path))
				{
					$this->_redirect(APP_BASE . 'brief_templates/' . $template_data['0']['file_path']);
					exit;
				}
				else
				{
					$this->_redirect(APP_BASE . "error/nofile");
				}
			}
		}

		public function templateuploadAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($_REQUEST['op'] == 'brieftemplate')
			{
				$this->resetuploadvars();
			}

			$extension = explode(".", $_FILES['qqfile']['name']);

			if($_REQUEST['op'] == 'brieftemplate')
			{
				$timestamp_filename = time() . "_file";
				$path = BRIEF_TEMPLATE_PATH;
				$dir = $clientid;

				//create first directory in /public
				while(!is_dir($path))
				{
					mkdir($path);
					chmod($path, "0755");
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}

				//create second client directory in first dir /public/first_dir/clientid
				while(!is_dir($path . '/' . $dir))
				{
					mkdir($path . '/' . $dir);
					chmod($path, "0755");
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}


			$folderpath = $dir;

			$filename = $path . "/" . $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];

			//file name
			$_SESSION['template_filename'] = trim($timestamp_filename) . '.' . $extension[count($extension) - 1];

			//file path
			$_SESSION['template_filepath'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];

			//file extension
			$_SESSION['template_filetype'] = $extension[count($extension) - 1];

			if(move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename))
			{
				echo json_encode(array('success' => true));
				exit;
			}
		}

		public function createletterAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			$patientmaster = new PatientMaster();
			$tm = new TabMenus();
			$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
			$this->view->tabmenus = $tm->getMenuTabs();

			$clientdata = Pms_CommonData::getClientData($clientid);

			//ADDRESSBOOK DATA
		
			// Hausarzt
			$famdoc = FamilyDoctor::getFamilyDoctors($ipid);
			$this->view->fam_doctor = sizeof($famdoc);
			$fam_doctor['practice'] = $famdoc[0]['practice'];
			$fam_doctor['first_name'] = $famdoc[0]['first_name'];
			$fam_doctor['last_name'] = $famdoc[0]['last_name'];
			$fam_doctor['salutation'] = $famdoc[0]['salutation'];
			$fam_doctor['title'] = $famdoc[0]['title'];
			$fam_doctor['street1'] = $famdoc[0]['street1'];
			$fam_doctor['zip'] = $famdoc[0]['zip'];
			$fam_doctor['city'] = $famdoc[0]['city'];
			$fam_doctor['phone_practice'] = $famdoc[0]['phone_practice'];
			$fam_doctor['phone_cell'] = $famdoc[0]['phone_cell'];
			$fam_doctor['fax'] = $famdoc[0]['fax'];
			$fam_doctor['email'] = $famdoc[0]['email'];
			$fam_doctor['doctornumber'] = $famdoc[0]['doctornumber'];
			$fam_doctor['medical_speciality'] = $famdoc[0]['medical_speciality'];
			$fam_doctor['comments'] = $famdoc[0]['comments'];
			$this->view->family_doctor = $fam_doctor;
			$this->view->fam_doctor_id = $famdoc[0]['id'];
			
			//Ansprechpartner
			$pc = new ContactPersonMaster();
			$pcs = $pc->getPatientContact($ipid);

			$familydegree = new FamilyDegree();
			$cnt_degree_array = $familydegree->getFamilyDegrees(1);

			$this->view->degree = $cnt_degree_array;

			$this->view->patient_contacts = $pcs;

			//Pflegedienst
			$pfleg = Pflegedienstes::getPflegedienstes($ipid);
			if($pfleg > 0)
			{
				$this->view->pat_pfleg = $pfleg;
			}
				
			// Pharmacy
			$pharmacy = new PatientPharmacy();
			$pharm_pat = $pharmacy->getPatientPharmacy($ipid);

			// TODO-2111 ISPC: Lettertoken shows random recipient Ancuta 04.02.2019
			$pharm_pat_arr = array();
			foreach($pharm_pat as $k=>$ph_data){
			    $pharm_pat_arr[$ph_data['pharmacy_id']] =  $ph_data; 
			    $pharm_pat_arr[$ph_data['pharmacy_id']]['id'] =  $ph_data['pharmacy_id']; 
			}
			//--
			if(sizeof($pharm_pat_arr) > 0){
				$this->view->pat_pharmacy = $pharm_pat_arr;
			}
				
			
				
			//Krankenkassen
			$ph = new PatientHealthInsurance();
			$phi = $ph->getPatientHealthInsurance($ipid);
			$this->view->patient_healthinsurance = $phi;
			
				
			//Facharzt
			$m_specialists_types = new SpecialistsTypes();
			$specialists_types  =$m_specialists_types->get_specialists_types($logininfo->clientid);
				
			if(!empty($specialists_types)){
				foreach($specialists_types as $k=>$tp){
					$s_type[$tp['id']] = $tp['name'];
				}
			}
			$this->view->s_type =$s_type;
			$specialists = new PatientSpecialists();
			$specialists_arr = $specialists->get_patient_specialists($ipid, true);
				
			if(count($specialists_arr))
			{
				$this->view->patient_specialists = $specialists_arr;
			}
			
			
			//Sanitatshauser
			$m_supplies =  new PatientSupplies();
			$p_spupplies =$m_supplies->getPatientSupplies($ipid);
			if(count($p_spupplies))
			{
				$this->view->patient_spupplies = $p_spupplies;
			}
				
			//sonst. Versorger
			$suppliers = new PatientSuppliers();
			$pat_suppliers = $suppliers->getPatientSuppliers($ipid);
			if(sizeof($pat_suppliers) > 0)
			{
				$this->view->patient_suppliers = $pat_suppliers;
			}
				
			//Physiotherapist
			$physiotherapists = new PatientPhysiotherapist();
			$pat_physio = $physiotherapists->getPatientPhysiotherapist($ipid);
			if(sizeof($pat_physio) > 0)
			{
				$this->view->patient_physioterapeuten = $pat_physio;
			}
			
			//Homecare
			$m_homecare = new PatientHomecare();
			$pat_homecare = $m_homecare->getPatientHomecare($ipid);
			if(sizeof($pat_homecare) > 0)
			{
				$this->view->patient_homecare = $pat_homecare;
			}
			
			
			$brief_templates = new BriefTemplates();
			
			//ADDRESSBOOK DATA

			if($this->getRequest()->isPost())
			{
				$this->_helper->viewRenderer->setNoRender(true);
				$this->_helper->layout()->disableLayout();
				
				if($_POST['template_id'] > '0')
				{
					$brief_form = new Application_Form_BriefTemplate();

					$post = $_POST;

					//get template details
					$template_id = trim(rtrim($post['template_id']));
					$template_data = $brief_templates->get_template($clientid, $template_id, '1');

					if($_POST['generate_pdf'])
					{
						$file_type = 'pdf';
					}
					else
					{
						$file_type = 'docx';
					}

					//SYSTEM VARS
					$patient_vars = $brief_templates->get_patient_details($ipid);
					$patient_vars = (!empty($patient_vars) ? $patient_vars : array());

					$pat_sapv_vars = $brief_templates->get_patient_sapv_details($ipid);
					$pat_sapv_vars = (!empty($pat_sapv_vars) ? $pat_sapv_vars : array());

					$pat_family_doc_vars = $brief_templates->get_patient_familydoctor($ipid);
					$pat_family_doc_vars = (!empty($pat_family_doc_vars) ? $pat_family_doc_vars : array());

					$client_details_vars = $brief_templates->get_client_details($clientid);
					$client_details_vars = (!empty($client_details_vars) ? $client_details_vars : array());

					$user_details_vars = $brief_templates->get_user_details($userid);
					$user_details_vars = (!empty($user_details_vars) ? $user_details_vars : array());

					$health_insurance_vars = $brief_templates->get_patient_hi_details($ipid);
					$health_insurance_vars = (!empty($health_insurance_vars) ? $health_insurance_vars : array());

					$patient_diagnosis_vars = $brief_templates->get_patient_diagnosis($clientid, $ipid);
					$patient_diagnosis_vars = (!empty($patient_diagnosis_vars) ? $patient_diagnosis_vars : array());
					
					$patient_diagnosis_table_vars = $brief_templates->get_patient_diagnosis_table($clientid, $ipid);
					$patient_diagnosis_table_vars = (!empty($patient_diagnosis_table_vars) ? $patient_diagnosis_table_vars : array());
// 					print_R($patient_diagnosis_table_vars); exit;
					
					

//				ISPC-911#13036 not used anymore, we use the symptomatics from visit(kvnodoc, kvnonurse, contactform)
//					$patient_sym_vars = $brief_templates->get_patient_symptomatology($ipid);
//					$patient_sym_vars = (!empty($patient_sym_vars) ? $patient_sym_vars : array());

					$patient_medication_vars = $brief_templates->get_patient_medications($ipid);
					$patient_medication_vars = (!empty($patient_medication_vars) ? $patient_medication_vars : array());

					$patient_medication_table_vars = $brief_templates->get_patient_medications_table($ipid);
					$patient_medication_table_vars = (!empty($patient_medication_table_vars) ? $patient_medication_table_vars : array());

					$patient_discharge_vars = $brief_templates->get_patient_discharge($clientid, $ipid);
					$patient_discharge_vars = (!empty($patient_discharge_vars) ? $patient_discharge_vars : array());

//				ISPC-1055 added new tokens
					$patient_last_location = $brief_templates->get_valid_last_location($ipid);
					$patient_last_location = (!empty($patient_last_location) ? $patient_last_location : array());
//ISPC-1236
					$patient_last_location_adress = $brief_templates->get_valid_last_location_adress($ipid);
					$patient_last_location_adress = (!empty($patient_last_location_adress) ? $patient_last_location_adress : array());
					
					$patient_contact_persons = $brief_templates->get_contact_persons($ipid);
					$patient_contact_persons = (!empty($patient_contact_persons) ? $patient_contact_persons : array());
//				ISPC-1055
//				ISPC-1092 - new tokens pflegedienst and facharzt
					$patient_nursing = $brief_templates->get_patient_nursing($ipid);
					$patient_nursing = (!empty($patient_nursing) ? $patient_nursing : array());
//              ISPC-1236 TOKEN ALL TIME TASK - 10.07.2019 Lore
					$patient_nursing_multiple = $brief_templates->get_patient_nursing_multiple($ipid);
					$patient_nursing_multiple = (!empty($patient_nursing_multiple) ? $patient_nursing_multiple : array());
					
					$patient_specialists = $brief_templates->get_patient_specialists($ipid);
					$patient_specialists = (!empty($patient_specialists) ? $patient_specialists : array());
//				ISPC-1092 - new tokens pflegedienst and facharzt
//				ISPC-1146 - new token fax number of apotheke
//				ISPC-1237 - added all pharmacy tokens
					$patient_pharmacy = $brief_templates->get_patient_pharmacy($ipid);
					$patient_pharmacy = (!empty($patient_pharmacy) ? $patient_pharmacy : array());
//              ISPC-1236 TOKEN ALL TIME TASK - 10.07.2019 Lore
					$patient_pharmacy_multiple = $brief_templates->get_patient_pharmacy_multiple($ipid);
					$patient_pharmacy_multiple = (!empty($patient_pharmacy_multiple) ? $patient_pharmacy_multiple : array());

//				ISPC-1237 - added tokens for Sanitätshäuser, Ehrenamtliche, sonst. Versorger + salutation token
					$patient_voluntaryworkers = $brief_templates->get_patient_voluntaryworkers($ipid);
					$patient_voluntaryworkers = (!empty($patient_voluntaryworkers) ? $patient_voluntaryworkers : array());

					$patient_supplies = $brief_templates->get_patient_supplies($ipid);
					$patient_supplies = (!empty($patient_supplies) ? $patient_supplies : array());
//              ISPC-1236 TOKEN ALL TIME TASK - 10.07.2019 Lore
					$patient_supplies_multiple = $brief_templates->get_patient_supplies_multiple($ipid);
					$patient_supplies_multiple = (!empty($patient_supplies_multiple) ? $patient_supplies_multiple : array());
					
					$patient_suppliers = $brief_templates->get_patient_suppliers($ipid);
					$patient_suppliers = (!empty($patient_suppliers) ? $patient_suppliers : array());
					//TODO-2650 Lore 14.11.2019
					$patient_suppliers_multiple = $brief_templates->get_patient_suppliers_multiple($ipid);
					$patient_suppliers_multiple = (!empty($patient_suppliers_multiple) ? $patient_suppliers_multiple : array());
					
					$patient_physiotherapists = $brief_templates->get_patient_physiotherapists($ipid);
					$patient_physiotherapists = (!empty($patient_physiotherapists) ? $patient_physiotherapists : array());

					$patient_homecare = $brief_templates->get_patient_homecares($ipid);
					$patient_homecare = (!empty($patient_homecare) ? $patient_homecare : array());
//              ISPC-1236 TOKEN ALL TIME TASK - 10.07.2019 Lore
					$patient_homecare_multiple = $brief_templates->get_patient_homecares_multiple($ipid);
					$patient_homecare_multiple = (!empty($patient_homecare_multiple) ? $patient_homecare_multiple : array());
					
					$patient_hospice_assoc = $brief_templates->get_patient_hospice_assoc($ipid);
					$patient_hospice_assoc = (!empty($patient_hospice_assoc) ? $patient_hospice_assoc : array());
					
//				ISPC-1741 - added tokens for * Verlauf Anamnese * Verlauf Befund * Verlauf Therapie
					$patient_course = $brief_templates->get_patient_course($ipid);
					$patient_course = (!empty($patient_course) ? $patient_course: array());
					
					$patient_assigned_users = $brief_templates->get_patient_assigned_users($ipid);
					$patient_assigned_users = (!empty($patient_assigned_users) ? $patient_assigned_users: array());
					
					// ISPC-1236 24.07.2018 Ancuta
					$patient_anlage4_next_date = $brief_templates->get_Anlage4_next_date($ipid);
					$patient_anlage4_next_date = (!empty($patient_anlage4_next_date) ? $patient_anlage4_next_date: array());
					
					// ISPC-1236 31.07.2019 Lore
					$patient_familienstand = $brief_templates->get_patient_familienstand($ipid);
					$patient_familienstand = (!empty($patient_familienstand) ? $patient_familienstand: array());
					
					// ISPC-1236 31.07.2019 Lore
					$patient_pflegegrad = $brief_templates->get_patient_pflegegrad($ipid);
					$patient_pflegegrad = (!empty($patient_pflegegrad) ? $patient_pflegegrad: array());
					
					// ISPC-1236 31.07.2019 Lore
					$patient_patientenvollmach = $brief_templates->get_patient_patientenvollmach($ipid);
					$patient_patientenvollmach = (!empty($patient_patientenvollmach) ? $patient_patientenvollmach: array());

					// ISPC-1236 31.07.2019 Lore
					$patient_vorsogevollmacht = $brief_templates->get_patient_vorsogevollmacht($ipid);
					$patient_vorsogevollmacht = (!empty($patient_vorsogevollmacht) ? $patient_vorsogevollmacht: array());
					
					// ISPC-1236 31.07.2019 Lore
					$patient_gesetzlicher_betreuer = $brief_templates->get_patient_gesetzlicher_betreuer($ipid);
					$patient_gesetzlicher_betreuer = (!empty($patient_gesetzlicher_betreuer) ? $patient_gesetzlicher_betreuer: array());
					
					// ISPC-1236 31.07.2019 Lore
					$patient_memo = $brief_templates->get_patient_memo($ipid);
					$patient_memo = (!empty($patient_memo) ? $patient_memo: array());
					
					// ISPC-1236 28.11.2019 Lore
					$patient_admission_details = $brief_templates->get_patient_admission_details($ipid);
					$patient_admission_details = (!empty($patient_admission_details) ? $patient_admission_details: array());
					
					//ISPC-1236 Lore 19.12.2019
					$patient_religions = $brief_templates->get_patient_religions($ipid);
					$patient_religions = (!empty($patient_religions) ? $patient_religions : array());
					
					//ISPC-1236 Lore 13.05.2020
					$patient_lives = $brief_templates->get_patient_lives($ipid);
					$patient_lives = (!empty($patient_lives) ? $patient_lives : array());
					
					$vars = array();
					$vars = array_merge($vars, $patient_vars, $pat_sapv_vars, $pat_family_doc_vars, $client_details_vars, $user_details_vars, $health_insurance_vars, $patient_diagnosis_vars,$patient_diagnosis_table_vars, $patient_medication_vars, $patient_medication_table_vars, $patient_discharge_vars, $patient_last_location, $patient_last_location_adress, $patient_contact_persons, $patient_nursing, $patient_nursing_multiple, $patient_specialists, $patient_pharmacy, $patient_pharmacy_multiple, $patient_voluntaryworkers, $patient_supplies, $patient_supplies_multiple, $patient_suppliers, $patient_suppliers_multiple, $patient_physiotherapists, $patient_homecare, $patient_homecare_multiple, $patient_hospice_assoc, $patient_course, $patient_assigned_users, $patient_anlage4_next_date, $patient_familienstand, $patient_pflegegrad, $patient_patientenvollmach, $patient_vorsogevollmacht, $patient_gesetzlicher_betreuer, 
					    $patient_memo, $patient_admission_details, $patient_religions, $patient_lives );
                    //print_r($vars);exit();
					//CUSTOM VARS
					$vars['aktuelles_datum'] = date('d.m.Y', time());

					//VISIT VARS
					if($post['visit_id'] > '0' && strlen($post['visit_id']) > '0')
					{
						//visit details
						$visit_credentials['id'] = trim(rtrim($post['visit_id']));
						$visit_credentials['clientid'] = $clientid;
						$visit_credentials['patient'] = $ipid;

						//cf - contact form , knur - kvno nurse, kdoc, bayern_doctorvisit,
						$visit_credentials['type'] = trim(rtrim($post['visit_type']));

						// n-> NUmbers Scale(0-10);  a -> Attributes scale (none/weak/averge/strong)
						$visit_credentials['symptomatology_scale'] = $clientdata[0]['symptomatology_scale'];

						$visit_data = $brief_templates->get_visit($visit_credentials);

						if(count($visit_data) > '0')
						{
							if($visit_data[0]['date'] != '0000-00-00 00:00:00')
							{
								$vars['visit_datum'] = date('d.m.Y', strtotime($visit_data[0]['date']));
							}
							else
							{
								$vars['visit_datum'] = '';
							}

							$vars['visit_start_time'] = date('H:i', strtotime($visit_data[0]['start_date']));
							$vars['visit_end_time'] = date('H:i', strtotime($visit_data[0]['end_date']));

							//contact forms
							if(strlen(trim(rtrim($visit_data['0']['comment']))) > '0')
							{
								$vars['visit_comment'] = html_entity_decode($visit_data['0']['comment'], ENT_QUOTES, 'utf-8');
//								$vars['visit_comment'] = str_replace("\r", "", $vars['visit_comment']);
							}
							elseif(strlen(trim(rtrim($visit_data['0']['sonstiges']))) > '0')
							{
								$vars['visit_comment'] = html_entity_decode($visit_data['0']['sonstiges'], ENT_QUOTES, 'utf-8');
//								$vars['visit_comment'] = str_replace("\r", "", $vars['visit_comment']);
							}
							elseif(strlen(trim(rtrim($visit_data['0']['kvno_sonstiges']))) > '0')
							{
								$vars['visit_comment'] = html_entity_decode($visit_data['0']['kvno_sonstiges'], ENT_QUOTES, 'utf-8');
//								$vars['visit_comment'] = str_replace("\r", "", $vars['visit_comment']);
							}
							elseif(strlen(trim(rtrim($visit_data['0']['visit_comment']))) > '0')
							{
								$vars['visit_comment'] = html_entity_decode($visit_data['0']['visit_comment'], ENT_QUOTES, 'utf-8');
//								$vars['visit_comment'] = str_replace("\r", "", $vars['visit_comment']);
							}
							else
							{
								$vars['visit_comment'] = '';
							}

							if(strlen($visit_data['0']['internal_comment']) > '0')
							{
								$vars['visit_internal_comment'] = html_entity_decode($visit_data['0']['internal_comment'], ENT_QUOTES, 'utf-8');
//								$vars['visit_internal_comment'] = str_replace("\r", "", $vars['visit_internal_comment']);
							}
							else
							{
								$vars['visit_internal_comment'] = '';
							}

							if($visit_data['0']['comment_apotheke'])
							{
								//bayern doc visit, kvno doc & nurse, contact forms
								$vars['visit_comment_apotheke'] = html_entity_decode($visit_data['0']['comment_apotheke'], ENT_QUOTES, 'utf-8');
								//								$vars['visit_comment_apotheke'] = str_replace("\r", "", $vars['visit_comment_apotheke']);
							}
							else
							{
								$vars['visit_comment_apotheke'] = '';
							}

							$vars['symptomatik'] = utf8_encode(html_entity_decode($visit_data[0]['symptomatik'], ENT_QUOTES, 'utf-8'));
							if(is_array($visit_data[0]['zapv_symptomatik']) && count($visit_data[0]['zapv_symptomatik']) > 0  ) //TODO-1591 05.06.2018
							{
								$vars = array_merge($vars, $visit_data[0]['zapv_symptomatik']);
							}
							
							

							if(strlen($visit_data['0']['case_history']) > '0')
							{
							    $vars['Anamnese_Besuch'] = html_entity_decode($visit_data['0']['case_history'], ENT_QUOTES, 'utf-8');
							}
							else
							{
							    $vars['Anamnese_Besuch'] = '';
							}

							
							if(strlen($visit_data['0']['befund_txt']) > '0')
							{
							    $vars['Befund_Besuch'] = html_entity_decode($visit_data['0']['befund_txt'], ENT_QUOTES, 'utf-8');
							}
							else
							{
							    $vars['Befund_Besuch'] = '';
							}
							
							if(strlen($visit_data['0']['therapy_txt']) > '0')
							{
							    $vars['Therapie_Besuch'] = html_entity_decode($visit_data['0']['therapy_txt'], ENT_QUOTES, 'utf-8');
							}
							else
							{
							    $vars['Therapie_Besuch'] = '';
							}
							
							
							// create new token  - get the client symptom block, userd in selected visit
// 							$vars['symptomatik_II'] = utf8_encode(html_entity_decode($visit_data[0]['symptomatik_II'], ENT_QUOTES, 'utf-8'));
							$vars['symptomatik_II'] = $visit_data[0]['symptomatik_II'];
							
							// ISPC-1236 - 19.10.2017
							$vars['Maßnahmen'] = $visit_data[0]['Maßnahmen'];
							
							
							//ISPC-1236 20.01.2020 Lore
							//$vars['symptomatik_II_new_table'] = $visit_data[0]['symptomatik_II_table'];
							if(count($visit_data[0]['symptomatik_II_table'])>0){
							    $cell_style = 'style="border: 1px solid; padding: 0; margin-top: 5px; margin-bottom: 5px;margin-left: 2px;margin-right: 2px;"';

							    $vars['SymptomatikII_Tabelle'] = 'Date : '.date('d.m.Y H:m', strtotime($visit_data[0]['symptomatik_II_table'][0]['date']));
							    $vars['SymptomatikII_Tabelle'] .='<table style="width: 100%; border-collapse:collapse; table-layout:fixed;" cellpadding="0" cellspacing="0">';
							    $vars['SymptomatikII_Tabelle'] .='<thead>';
							    $vars['SymptomatikII_Tabelle'] .='<tr>';
							    $vars['SymptomatikII_Tabelle'] .='<th ' . $cell_style . ' > Symptom</th>';        //ISPC-1236 Lore 05.02.2020
							    $vars['SymptomatikII_Tabelle'] .='<th ' . $cell_style . ' > Wert</th>';          //ISPC-1236 Lore 05.02.2020
							    $vars['SymptomatikII_Tabelle'] .='</tr>';
							    $vars['SymptomatikII_Tabelle'] .='</thead>';
							    $vars['SymptomatikII_Tabelle'] .='</tbody>';
							    
							    foreach($visit_data[0]['symptomatik_II_table'][0] as $ky=>$kv){
							        if($ky != 'date'){
							            $vars['SymptomatikII_Tabelle'] .='<tr>';
							            $vars['SymptomatikII_Tabelle'] .='<td ' . $cell_style . ' >'.$kv['name'].'</td>';
							            $vars['SymptomatikII_Tabelle'] .='<td ' . $cell_style . ' >'.$kv['value'].'</td>';
							            $vars['SymptomatikII_Tabelle'] .='</tr>';
							        }
							    }
							    $vars['SymptomatikII_Tabelle'] .='</body>';
							    $vars['SymptomatikII_Tabelle'] .='</table>';
							    
							} else {
							    $vars['SymptomatikII_Tabelle']="";
							}
							
							
							//ISPC-1236 Lore 28.02.2020 ($Vitalwerte_aktuell$)
							$vars['Vitalwerte_aktuell'] = $visit_data[0]['Vitalwerte_aktuell'];
							
						}
					}
					else
					{
						//create default vars for visits so we dont send the $token$ text to generated document
						$vars['visit_datum'] = '';
						$vars['visit_start_time'] = '';
						$vars['visit_end_time'] = '';
						$vars['visit_comment'] = '';
						$vars['visit_internal_comment'] = '';
						$vars['visit_comment_apotheke'] = '';
						$vars['symptomatik'] = '';
						$vars['symptomatik_II'] = '';
						$vars['zapv_symptomatik'] = '';
						$vars['Maßnahmen'] = '';
						
						$vars['Anamnese_Besuch'] = '';
						$vars['Befund_Besuch'] = '';
						$vars['Therapie_Besuch'] = '';
						
						$vars['symptomatik_II_new_table'] = "";
						
						$vars['Vitalwerte_aktuell'] = '';
						
					}
//				print_r($vars);
// 		 		exit;
					//all visits
					$s = array("\r\n", "\n\r", "\n", "\r");
					$r = array('<stupidbreak/>');
					$vars['recipient'] = str_replace($s, $r, $post['recipient_address']);
					$vars['recipient'] = str_replace('<stupidbreak/>', '\n\r', $vars['recipient']);

					
					
					if(strlen($post['recipient_last_name']) > 0 ){
    					$vars['nachname_empfaenger'] = $post['recipient_last_name'];
					} else{
    					$vars['nachname_empfaenger'] = "";
					}
					
					
					if(strlen($post['recipient_first_name']) > 0 ){
    					$vars['vorname_empfaenger'] = $post['recipient_first_name'];
					} else{
    					$vars['vorname_empfaenger'] = "";
					}
					
					
					if(strlen($post['recipient_salutation']) > 0 ){
    					$vars['anrede_empfaenger'] = $post['recipient_salutation'];
					} else{
    					$vars['anrede_empfaenger'] = "";
					}
					
					
					// ISPC-1236 - 19.10.2017
					if(strlen($post['recipient_fax']) > 0 ){
    					$vars['recipient_fax'] = $post['recipient_fax'];
					} else{
    					$vars['recipient_fax'] = "";
					}
					
					
					$vars['visit_comment'] = str_replace($s, $r, $vars['visit_comment']);
					$vars['visit_comment'] = str_replace('<stupidbreak/>', '\n\r', $vars['visit_comment']);

					$vars['visit_internal_comment'] = str_replace($s, $r, $vars['visit_internal_comment']);
					$vars['visit_internal_comment'] = str_replace('<stupidbreak/>', '\n\r', $vars['visit_internal_comment']);

					$vars['visit_comment_apotheke'] = str_replace($s, $r, $vars['visit_comment_apotheke']);
					$vars['visit_comment_apotheke'] = str_replace('<stupidbreak/>', '\n\r', $vars['visit_comment_apotheke']);

					
					$vars['Anamnese_Besuch'] = str_replace($s, $r, $vars['Anamnese_Besuch']);
					$vars['Anamnese_Besuch'] = str_replace('<stupidbreak/>', '\n\r', $vars['Anamnese_Besuch']);
					
					$vars['Befund_Besuch'] = str_replace($s, $r, $vars['Befund_Besuch']);
					$vars['Befund_Besuch'] = str_replace('<stupidbreak/>', '\n\r', $vars['Befund_Besuch']);
					
					$vars['Therapie_Besuch'] = str_replace($s, $r, $vars['Therapie_Besuch']);
					$vars['Therapie_Besuch'] = str_replace('<stupidbreak/>', '\n\r', $vars['Therapie_Besuch']);

					//ISPC-2292
					$vars['mambo_feedback'] = AssessmentProblemsTable::fetchTokenMamboFeedback($ipid);
					
					
					if($_REQUEST['dbgv'])
					{
					
						//SYSTEM VARS
						print_r(" \n client_details_vars\n ");
						print_r($client_details_vars );
						
						print_r(" \n user_details_vars\n ");
						print_r($user_details_vars ); 

						print_r($vars);
						
						print_r(" \n patient_vars\n ");
						print_r($patient_vars ); 
						
						print_r(" \n pat_sapv_vars\n ");
						print_r($pat_sapv_vars );
						
						print_r(" \n patient_discharge_vars\n ");
						print_r($patient_discharge_vars );
						
						print_r(" \n patient_last_location\n ");
						print_r($patient_last_location );
						
						print_r(" \n patient_last_location_adress\n ");
						print_r($patient_last_location_adress );
						
						print_r(" \n patient_medication_vars\n ");
						print_r($patient_medication_vars );
						
						print_r(" \n patient_medication_table_vars\n ");
						print_r($patient_medication_table_vars );
						
						
						print_r(" \n patient_diagnosis_vars\n ");
						print_r($patient_diagnosis_vars );
							
						print_r(" \n patient_diagnosis_table_vars\n ");
						print_r($patient_diagnosis_table_vars );
						
						
						
						print_r(" \n pat_family_doc_vars\n ");
						print_r($pat_family_doc_vars );
						
						
						print_r(" \n health_insurance_vars\n ");
						print_r($health_insurance_vars );
						
				
						
						print_r(" \n patient_contact_persons\n ");
						print_r($patient_contact_persons );

						print_r(" \n patient_nursing\n ");
						print_r($patient_nursing );
						
						print_r(" \n patient_specialists\n ");
						print_r($patient_specialists ); 

						print_r(" \n patient_pharmacy\n ");
						print_r($patient_pharmacy ); 
						
						print_r(" \n patient_voluntaryworkers\n ");
						print_r($patient_voluntaryworkers );
						
						print_r(" \n patient_supplies\n ");
						print_r($patient_supplies ); 
						
						print_r(" \n patient_suppliers\n ");
						print_r($patient_suppliers );
						
						print_r(" \n patient_physiotherapists\n ");
						print_r($patient_physiotherapists ); 
						
						print_r(" \n patient_homecare\n ");
						print_r($patient_homecare ); 
						
						print_r(" \n patient_hospice_assoc\n ");
						print_r($patient_hospice_assoc ); 
							
						print_r(" \n patient_course\n ");
						print_r($patient_course ); 
							
						print_r(" \n patient_assigned_users\n ");
						print_r($patient_assigned_users );
						
						print_r("Visit credentials");
						print_r($visit_credentials);
						
						print_r("Visit data");
						print_r($visit_data);
						
						print_r("patient_familienstand");
						print_r($patient_familienstand);
						
						print_r("patient_pflegegrad");
						print_r($patient_pflegegrad);
						
						print_r("patient_patientenvollmach");
						print_r($patient_patientenvollmach);
						
						print_r("patient_vorsogevollmacht");
						print_r($patient_vorsogevollmacht);

						print_r("patient_gesetzlicher_betreuer");
						print_r($patient_gesetzlicher_betreuer);
						
						print_r("patient_memo");
						print_r($patient_memo);
						
						print_r("patient_admission_details");
						print_r($patient_admission_details);
						
						print_r("patient_religions");
						print_r($patient_religions);
						
						print_r("patient_lives");
						print_r($patient_lives);
					   exit;
					}
					   
					//print_r($template_data);exit;
					$vars = array_map('trim', $vars);
					$this->generate_letter($ipid, $template_data[0], $vars, $file_type);
				}
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

		private function resetuploadvars()
		{
			//clear failed/other upload session vars
			$_SESSION['template_filename'] = '';
			unset($_SESSION['template_filename']);

			$_SESSION['template_filepath'] = '';
			unset($_SESSION['template_filepath']);

			$_SESSION['template_filetype'] = '';
			unset($_SESSION['template_filetype']);
		}

		private function generate_letter($ipid, $template_data = false, $vars = false, $export_file_type = 'docx')
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			ob_end_clean();
			if($template_data)
			{
				$template_path = BRIEF_TEMPLATE_PATH . '/' . $template_data['file_path'];
				
				
				/*
				 * @cla on 19.02.2019
				 * development.alltokens.docx
				 * on development... if you are missing the correct template, load a docx that contains all the tokens so you can test easier
				 * TODO: add new tokens to this public/brief_templates/1/development.alltokens.docx
				 * 
				 */
				if (APPLICATION_ENV=='development' && ! file_exists($template_path)) {
				    $template_path = BRIEF_TEMPLATE_PATH . '/1/development.alltokens.docx';
				}
				
				$docx = new CreateDocxFromTemplate($template_path);
				
				// remove html tokens from vars
				$html_tokens = array('medikation_als_Tabelle','bedarf_als_Tabelle','iv_als_Tabelle','pumpe_als_Tabelle','bp_als_Tabelle','Ernährung_als_Tabelle','Diagnose_Tabelle','Hauptdiagnose_Tabelle','Nebendiagnose_Tabelle', 'SymptomatikII_Tabelle'); //ISPC-1236 Lore 20.01.2020
				foreach($vars as $token =>$value){
				    if(!in_array($token,$html_tokens)){
        				$text_vars[$token] = $value;
				    } 
				    else
				    {
        				$html_vars[$token] = $value;
				    }
				}
 
				
				$options = array('parseLineBreaks' => true);
				$docx->replaceVariableByText($text_vars, $options);
				$docx->replaceVariableByText($text_vars, array('parseLineBreaks' => true, 'target' => 'header'));
				$docx->replaceVariableByText($text_vars, array('parseLineBreaks' => true, 'target' => 'footer'));
				

				// replace the html tokens
				if($html_vars)
				{
			        foreach($html_tokens as $k_html => $token_html)
			        {
			            //unset the html variable from tokens $vars to avoid errors
			            if(strlen(trim(rtrim($html_vars[$token_html]))) > '0')
			            {
			                //set html options
			                $html_options = array('isFile' => false, 'parseDivsAsPs' => false, 'downloadImages' => false, "strictWordStyles" => false);
			    
			                //cleanup token html entities
			                $html = html_entity_decode($html_vars[$token_html], ENT_COMPAT, 'UTF-8');
			    
			    
			                if($token_html == "comment")
			                {
			                    $type = "block";
			    
// 			                    //get token fonts only for inline tokens
// 			                    $docx_tmp = new CreateDocxFromTemplate($template_path);
// 			                    $docx_tmp->replaceVariableByHTML($token_html, $type, $html, $html_options);
// 			                    //$token_fonts = $docx_tmp->getTokenFont();
// 			                    $token_fonts = array();
			    
// 			                    //convert inline_html_tokens to string_tokens
// 			                    $new_tokens[] = $token_html;
			    
			                    $html_vars[$token_html . '_text'] = strip_tags(html_entity_decode($html_vars[$token_html], ENT_COMPAT, 'UTF-8'), "<br>");
			                    $html_vars[$token_html . '_text'] = str_replace(array('<br/>', '<br />', '<br>'), '\n\r', $html_vars[$token_html . '_text']);
			                    
			                    
			                    if ($res = Pms_DocUtil::process_html_token($docx, $token_html, $html)) {
			                    	$html = $res;
			                    }

			                }
			                else
			                {
			                    $type = "block";
			                }
			    
			                //set each token font
// 			                if($type == "inline" && count($token_fonts[$token_html]) > '0')
// 			                {
// 			                    $css_style = array();
// 			                    if(strlen($token_fonts[$token_html]['font']['name']) > '0')
// 			                    {
// 			                        $css_style[] = 'font-family:' . $token_fonts[$token_html]['font']['name'];
// 			                    }
			    
// 			                    if(strlen($token_fonts[$token_html]['font']['size']) > '0')
// 			                    {
// 			                        $css_style[] = 'font-size:' . $token_fonts[$token_html]['font']['size'] . 'pt';
// 			                    }
			    
// 			                    if(strlen($token_fonts[$token_html]['font']['color']) > '0')
// 			                    {
// 			                        $css_style[] = 'color:#' . $token_fonts[$token_html]['font']['color'];
// 			                    }
			    
// 			                    if($token_fonts[$token_html]['font']['isbold'] == '1')
// 			                    {
// 			                        $css_style[] = 'font-weight:bold';
// 			                    }
			    
// 			                    if($token_fonts[$token_html]['font']['isitalic'] == '1')
// 			                    {
// 			                        $css_style[] = 'font-style:italic';
// 			                    }
			    
// 			                    if($token_fonts[$token_html]['font']['isunderline'] == "1")
// 			                    {
// 			                        $css_style[] = 'text-decoration:underline';
// 			                    }
			    
// 			                    //dummy css control
// 			                    if(!empty($css_style))
// 			                    {
// 			                        $css_style[] = '';
// 			                    }
			    
// 			                    $html = html_entity_decode('<p style="' . implode(';', $css_style) . '">' . strip_tags($html_vars[$token_html], '<br>') . '</p>', ENT_COMPAT, 'UTF-8');
// 			                }
			                
			                
			                //force change utf-8 in html entities, because on one server it did not return corectly utf-8
			                $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
			                
			                $docx->replaceVariableByHTML($token_html, $type, $html, $html_options);
			                unset($html_vars[$token_html]);
			            }
			            else
			            {
			                $html_vars[$token_html] = '';
			                $html_vars[$token_html . '_text'] = '';
			            }
			        }
			    
			        //parse header
			        $docx->replaceVariableByText($html_vars, array('parseLineBreaks' => true, 'target' => 'header'));
			    
			        //parse body
			        $options = array('parseLineBreaks' => true);
			        $docx->replaceVariableByText($html_vars, $options);
			    
			        //parse footer
			        $docx->replaceVariableByText($html_vars, array('parseLineBreaks' => true, 'target' => 'footer'));
			    }
				    
				if(!is_dir(PDFDOCX_PATH))
				{
					while(!is_dir(PDFDOCX_PATH))
					{
						mkdir(PDFDOCX_PATH);
						if($i >= 50)
						{
							exit; //failsafe
						}
						$i++;
					}
				}

				if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
				{
					while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
					{
						mkdir(PDFDOCX_PATH . '/' . $clientid);
						if($i >= 50)
						{
							exit; //failsafe
						}
						$i++;
					}
				}

				$suffix = $vars['patienten_id'];
				$filename = PDFDOCX_PATH . '/' . $clientid . '/brief_' . $suffix;

				if($export_file_type == 'docx')
				{
					//$docx->enableCompatibilityMode();
					$docx->createDocxAndDownload($filename);
					unlink($filename . '.docx');
					exit;
				}
				else
				{
					$docx->createDocx($filename);
					$other_filename = PDFDOCX_PATH . '/' . $clientid . '/brief_final_' . $suffix . '.' . $export_file_type;

					//$docx->enableCompatibilityMode();
					$docx->transformDocument($filename . '.docx', $other_filename);
	
					//$this->system_file_upload($clientid, $ipid, $other_filename, "newletterfile");
					$this->system_file_upload($clientid, $ipid, $other_filename, $template_data['title']);
					unlink($filename . '.docx');

					header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
					header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
					header("Cache-Control: no-store, no-cache, must-revalidate");
					header("Cache-Control: post-check=0, pre-check=0", false);
					header("Pragma: no-cache");

					switch($export_file_type)
					{
						case 'pdf':
							header('Content-type: application/pdf');
							break;
						case 'doc':
							header('Content-type: application/vnd.ms-word');
							break;
						case 'rtf':
							header("Content-type: application/rtf");
							break;
						case 'odt':
							header('Content-type: application/vnd.oasis.opendocument.text');
							break;
						default:
							exit;
							break;
					}
					header('Content-Disposition: attachment; Filename="brief_' . $vars['patienten_id'] . '.' . $export_file_type . '"');
					readfile($other_filename);
					unlink($other_filename);
					exit;
				}
			}
			else
			{
				return false;
			}
		}

		private function system_file_upload($clientid, $ipid, $source_path = false, $file_title = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($source_path)
			{
				//prepare unique upload folder
				$tmpstmp = $this->uniqfolder(PDF_PATH);

				//get upload folder name
				$tmpstmp_filename = basename($tmpstmp);

				//get original file name
				$file_name_real = basename($source_path);
				$source_path_info = pathinfo($source_path);


				//construct upload folder, file destination
				$destination_path = PDF_PATH . "/" . $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
				$db_filename_destination = $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];

				//do a copy (from place where the pdf is generated to upload folder
				copy($source_path, $destination_path);

				//prepare cmd for folder zip
// 				$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
				//execute - zip the folder
// 				exec($cmd);

				$zipname = $tmpstmp . ".zip";
				$filename = "uploads/" . $tmpstmp . ".zip";

				/*
				//connect
				$con_id = Pms_FtpFileupload::ftpconnect();
				if($_REQUEST['zzz'])
				{
					print_r("Connection ID:");
					var_dump($con_id);
					print_r("\n\n");
					exit;
				}
				if($con_id)
				{
					//do upload
					$upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
					//close connection
					Pms_FtpFileupload::ftpconclose($con_id);
				}
				*/
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ($destination_path, "uploads" );
				

				if($file_title === false)
				{
					$file_title = 'nonamedocumentfile';
				}

				//add pdf to patient files table
				if(strlen($filename) > 0)
				{
					$cust = new PatientFileUpload();
					$cust->title = Pms_CommonData::aesEncrypt($this->view->translate($file_title));
					$cust->ipid = $ipid;
					$cust->file_name = Pms_CommonData::aesEncrypt($db_filename_destination);
					$cust->file_type = Pms_CommonData::aesEncrypt($source_path_info['extension']);
					$cust->system_generated = "0";
					$cust->save();

					$file_id = $cust->id;

					/* if($file_title == "newletterfile")
					  {
					  //insert system file tags
					  $insert_tag = Application_Form_PatientFile2tags::insert_file_tags($file_id, array('7'));
					  } */
					//insert system file tags
					$insert_tag = Application_Form_PatientFile2tags::insert_file_tags($file_id, array('7'));
				}
			}
		}

		private function uniqfolder($path)
		{
			$i = 0;
			$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
			while(!is_dir($path . '/' . $dir))
			{
				$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
				mkdir($path . '/' . $dir);
				if($i >= 50)
				{
					exit; //failsafe
				}
				$i++;
			}

			return $dir;
		}
		
		public function process_html_token( CreateDocxFromTemplate $docx, $token, $html )
		{
		    if ( !($docx instanceof CreateDocxFromTemplate) )
		    {
		        return false;
		    }
		
		    $found_fonts_attrs = array();
		
		    $dom = $docx->getDOMDocx();
		    $docXPath = new DOMXPath($dom);
		
		    //$search = $docx->getTemplateSymbol(). $token . $docx->getTemplateSymbol();
		    $search = $token;
		
		    $query = '//w:p/w:r[w:t[text()[contains(., "' . $search . '")]]]';
		
		    //$query = '//w:p/w:r';
		
		    $foundNodes = $docXPath->query($query);
		
		    //pr( $foundNodes );
		
		    foreach ($foundNodes as $node)
		    {
		        $nodeText = $node->ownerDocument->saveXML($node);
		        $cleanNodeText = strip_tags($nodeText);
		        if (strpos($cleanNodeText, $search) !== false || strpos($cleanNodeText, $token) !== false )
		        {
		
		            //prepare node token xml
		            $docDOM_node = new DOMDocument();
		            $docDOM_node->loadXML('<w:root xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"
                                               xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
                                               xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
                                               xmlns:mv="urn:schemas-microsoft-com:mac:vml"
                                               xmlns:o="urn:schemas-microsoft-com:office:office"
                                               xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
                                               xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
                                               xmlns:v="urn:schemas-microsoft-com:vml"
                                               xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"
                                               xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
                                               xmlns:w10="urn:schemas-microsoft-com:office:word"
                                               xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
                                               xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
                                               xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"
                                               xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"
                                               xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
                                               xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"
                                               mc:Ignorable="w14 wp14">' . $nodeText . '</w:root>');
		            $docXpath_node = new DOMXPath($docDOM_node);
		
		            //$ret = $docDOM_node->saveXML();
		
		            //pr ( $ret );
		
		            //get curent token block original font attributes
		            $font_query = '//w:rFonts';
		            $xmlfontNodesFont = $docXpath_node->query($font_query)->item(0);
		            $font_allowed_attributes = array('ascii','hAnsi','cs');
		
		            if($xmlfontNodesFont)
		            {
		                foreach($xmlfontNodesFont->attributes as $attribute_name => $attribute_node)
		                {
		                    $found_fonts_attrs[$token]['font_data'][$attribute_name] = $attribute_node->nodeValue;
		
		                    if(in_array($attribute_name, $font_allowed_attributes))
		                    {
		                        $found_fonts_attrs[$token]['font']['name'] = $attribute_node->nodeValue;
		                    }
		                }
		            }
		
		
		            //get curent token block original font color
		            $font_color_query = '//w:color';
		            $xmlfontNodesColor = $docXpath_node->query($font_color_query)->item(0);
		
		            //pr( $xmlfontNodesColor);
		
		            if($xmlfontNodesColor)
		            {
		                foreach ($xmlfontNodesColor->attributes as $attribute_name => $attribute_node)
		                {
		                    $found_fonts_attrs[$token]['font']['color'] = $attribute_node->nodeValue;
		                }
		            }
		            	
		            //get curent token block original font decorations [bold]
		            $font_bold_query = '//w:b';
		            $xmlfontNodesBold = $docXpath_node->query($font_bold_query)->item(0);
		
		            if($xmlfontNodesBold)
		            {
		                //foreach ($xmlfontNodesBold->attributes as $attribute_name => $attribute_node)
		                //{
		                $found_fonts_attrs[$token]['font']['isbold'] = '1';
		                //}
		                }
		                	
		                //get curent token block original font decorations [underline]
		                $font_underline_query = '//w:u';
		                $xmlfontNodesUnderline = $docXpath_node->query($font_underline_query)->item(0);
		
		                //pr( $xmlfontNodesUnderline );
		
		                if($xmlfontNodesUnderline)
		                {
		                    foreach ($xmlfontNodesUnderline->attributes as $attribute_name => $attribute_node)
		                    {
		                        $found_fonts_attrs[$token]['font']['isunderline'] = '1';
		                    }
		                }
		                	
		                //get curent token block original font decorations [italic]
		                $font_italic_query = '//w:i';
		                $xmlfontNodesItalic = $docXpath_node->query($font_italic_query)->item(0);
		                	
		                //pr( $xmlfontNodesItalic);
		
		                if($xmlfontNodesItalic)
		                {
		                    //foreach ($xmlfontNodesItalic->attributes as $attribute_name => $attribute_node)
		                    //{
		                    $found_fonts_attrs[$token]['font']['isitalic'] = '1';
		                    //}
		                    }
		
		                    //get curent token block original font size
		                    $font_size_query = '//w:sz';
		                    $xmlfontNodesSize = $docXpath_node->query($font_size_query)->item(0);
		
		
		                    if($xmlfontNodesSize)
		                    {
		                        foreach ($xmlfontNodesSize->attributes as $attribute_name => $attribute_node)
		                        {
		                            //pr( $attribute_name );
		                            //pr( $attribute_node );
		
		                            $found_fonts_attrs[$token]['font']['size'] = $attribute_node->nodeValue / 2;
		                            	
		                        }
		                    }
		                }
		            }
		
		            	
		            //pr( $found_fonts_attrs);
		
		            $token_fonts = $found_fonts_attrs;
		            $token_html = $token;
		
		            $css_style = array();
		            if( isset($token_fonts[$token_html]['font']['name']) && strlen($token_fonts[$token_html]['font']['name']) > '0')
		            {
		                $css_style[] = 'font-family:' . $token_fonts[$token_html]['font']['name'];
		            }
		
		            if( isset($token_fonts[$token_html]['font']['size']) && strlen($token_fonts[$token_html]['font']['size']) > '0')
		            {
		                $css_style[] = 'font-size:' . $token_fonts[$token_html]['font']['size'] . 'pt';
		                $css_style[] = 'line-height:' . $token_fonts[$token_html]['font']['size'] . 'pt';
		            }
		
		            if( isset($token_fonts[$token_html]['font']['color']) && strlen($token_fonts[$token_html]['font']['color']) > '0')
		            {
		                $css_style[] = 'color:#' . $token_fonts[$token_html]['font']['color'];
		            }
		
		            if( isset($token_fonts[$token_html]['font']['isbold']) && $token_fonts[$token_html]['font']['isbold'] == '1')
		            {
		                $css_style[] = 'font-weight:bold';
		            }
		
		            if( isset($token_fonts[$token_html]['font']['isitalic']) && $token_fonts[$token_html]['font']['isitalic'] == '1')
		            {
		                $css_style[] = 'font-style:italic';
		            }
		
		            if( isset($token_fonts[$token_html]['font']['isunderline']) && $token_fonts[$token_html]['font']['isunderline'] == "1")
		            {
		                $css_style[] = 'text-decoration:underline';
		            }
		
		            //dummy css control
		            if(!empty($css_style))
		            {
		                $css_style[] = '';
		            }
		
		            $html = html_entity_decode('<div style="' . implode(';', $css_style) . '">' . $html . '</div>', ENT_COMPAT, 'UTF-8');
		
		
		            return $html;
		        }

	}

?>
