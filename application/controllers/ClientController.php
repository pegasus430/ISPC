<?php

	class ClientController extends Pms_Controller_Action {

		public $act;

		public function init()
		{
			/* Initialize action controller here */
			
			//array_push($this->actions_with_js_file, "smtpsettings");
			
			$this
			->setActionsWithJsFile([
					/*
					 * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
			*/
					'smtpsettings',
					'clientprintsettings',
					'patientpermanentdeletion',
			]);
// 			//call template_init for selected action
// 			$this_action = $this->getRequest()->getActionName();
// 			if(in_array($this_action, self::$actions_with_js_file)) {
// // 				$this->template_init();
// 			}
			
// 			$logininfo = new Zend_Session_Namespace('Login_Info');
		}

		
// 		private function template_init()
// 		{
// 			setlocale(LC_ALL, 'de_DE.UTF-8');
		
// 			if ( (isset($_REQUEST['pdf_print_template']) && $_REQUEST['pdf_print_template']=="pdf_print_template")
// 					|| (isset($_REQUEST['bypass_template']) && $_REQUEST['bypass_template']== "1" )
// 			)
// 			{
// 				//pdf print template
// 				$this->_helper->viewRenderer->setNoRender(true);
		
// 			}
// 			elseif ( ! $this->getRequest()->isXmlHttpRequest()) {
// 				/* ------------- Include js file of this action --------------------- */
// 				$actionName = $this->getRequest()->getActionName();
// 				$controllerName = $this->getRequest()->getControllerName();
					
// 				//sanitize $js_file_name ?
// 				$actionName = Pms_CommonData::normalizeString($actionName);
// 				$controllerName = Pms_CommonData::normalizeString($controllerName);
		
// 				//this is only on pc... so remember to put the ipad version
// 				$pc_js_file =  PUBLIC_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";
					
// 				//$js_filename is for http ipad/pc
// 				$js_filename = RES_FILE_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";
					
// 				if (file_exists( $pc_js_file )) {
// 					$this->view->headScript()->appendFile($js_filename);
// 				}
		
// 			}
				
// 		}
		
		public function clientAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->view->new_client = '1';
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('client', $logininfo->userid, 'canadd');

			$this->view->regions = Pms_CommonData::getRegions();

			$this->view->districtarray = Pms_CommonData::getDistrict();


			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$client_form = new Application_Form_Client();

			$this->view->action = "client/client";
			$this->_helper->layout->setLayout('layout');

			if($this->getRequest()->isPost())
			{

			    $_POST['clientid'] = isset($_POST['clientid']) ? $_POST['clientid'] : 0;
			    
				if($client_form->validate($_POST))
				{
					$client_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
					$this->_redirect(APP_BASE . "client/clientlist?flg=suc");
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			if($logininfo->usertype == 'SA')
			{
				$this->view->comment = $disarray[0]['comment'];
				$this->view->commentbox = '<label id="lbl_userlimit" for="userlimit">' . $this->view->translate('UserAccounts') . '</label>
			<input type="text" name="userlimit" id="userlimit" value="' . $this->view->userlimit . '" >
			<br /> <label id="lbl_maintainance" for="maintainance">' . $this->view->translate('ismaintainance') . '</label>
			<input type="checkbox" name="maintainance" id="maintainance" value="1" >
			<br /><br /><label for="comment" id="lbl_Client_Kommentarte">' . $this->view->translate('comment') . '</label><textarea name="comment" id="txt_Client_Kommentarte" cols="35" rows="5">' . $disarray[0]['comment'] . '</textarea><div class="clearer"	></div><br />';
			}
			
			
			//ISPC-2161
			$this->view->teammeeting_settings_form = $client_form->create_form_teammeeting_settings();
			
			
		}

		public function clienteditAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($logininfo->usertype == 'SA')
			{
				if($_GET['id'] > 0)
				{
					$clientid = $_GET['id'];
				}
				else if($logininfo->clientid > 0)
				{
					$clientid = $logininfo->clientid;
				}
				else
				{
					$this->view->error_message = $this->view->translate("selectclient");
					$clientid = '0';
				}

				$this->view->action = APP_BASE . "client/clientedit?id=" . $clientid;
			}
			else
			{
				$this->view->action = APP_BASE . "client/clientedit?id=" . $logininfo->clientid;
				$clientid = $logininfo->clientid;
			}


			$modules = new Modules();
			$ppun_perms = $modules->checkModulePrivileges("88", $clientid);
			$this->view->ppun_perms = $ppun_perms;
			
			
			// ISPC-2452 Ancuta 21.11.2019// Maria:: Migration ISPC to CISPC 08.08.2020
			$hi_debitornumber_perms = $modules->checkModulePrivileges("204", $clientid);
			$this->view->hi_debitornumber_perms = $hi_debitornumber_perms;
			//--
		 
			if($modules->checkModulePrivileges("201", $clientid))
			{
			    $hospiz_sap_export_settings = true;
			}
			else
			{
			    $hospiz_sap_export_settings = false;
			}
			
			$this->view->hospiz_sap_export_settings = $hospiz_sap_export_settings ;
			
			//$this->view->client_modules = $modules->get_client_modules(); // you now have all the modules

			$this->_helper->viewRenderer('client');
			$this->view->regions = Pms_CommonData::getRegions();
			$this->view->districtarray = Pms_CommonData::getDistrict();


			//get client all users + sadmins ?
			$all_client_users = User::getUserByClientid($clientid, '1', true);
			$this->view->all_client_users = $all_client_users;

            //ISPC-2272 (07.11.2018) - company_number,cost_center
            // ISPC-2327 (23.01.2019) - working_schedule
            // ISPC-2452  (01.10.2019) - Rp Interface
			// ISPC-2171 Ancuta 08.01.2020 - added rlp_document_header_txt
			//ISPC-2806 Dragos 28.01.2021 - added c.ClientComplaintSettings ccs
			$cust = Doctrine_Query::create()
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
							AES_DECRYPT(lbg_institutskennzeichen,'" . Zend_Registry::get('salt') . "') as lbg_institutskennzeichen,
							AES_DECRYPT(sepa_iban,'" . Zend_Registry::get('salt') . "') as sepa_iban,
							AES_DECRYPT(sepa_bic,'" . Zend_Registry::get('salt') . "') as sepa_bic,
							AES_DECRYPT(sepa_ci,'" . Zend_Registry::get('salt') . "') as sepa_ci,
							AES_DECRYPT(company_number,'" . Zend_Registry::get('salt') . "') as company_number,
							AES_DECRYPT(cost_center,'" . Zend_Registry::get('salt') . "') as cost_center,
							AES_DECRYPT(working_schedule,'" . Zend_Registry::get('salt') . "') as working_schedule,
							AES_DECRYPT(rlp_past_revenue,'" . Zend_Registry::get('salt') . "') as rlp_past_revenue,
							AES_DECRYPT(rlp_hi_account_number,'" . Zend_Registry::get('salt') . "') as rlp_hi_account_number,
							AES_DECRYPT(rlp_pv_account_number,'" . Zend_Registry::get('salt') . "') as rlp_pv_account_number,
							AES_DECRYPT(rlp_terms_of_payment,'" . Zend_Registry::get('salt') . "') as rlp_terms_of_payment,
							AES_DECRYPT(rlp_document_header_txt,'" . Zend_Registry::get('salt') . "') as rlp_document_header_txt,
							AES_DECRYPT(hospiz_hi_cont,'" . Zend_Registry::get('salt') . "') as hospiz_hi_cont,
							AES_DECRYPT(hospiz_pv_cont,'" . Zend_Registry::get('salt') . "') as hospiz_pv_cont,
							AES_DECRYPT(hospiz_const_center,'" . Zend_Registry::get('salt') . "') as hospiz_const_center,
							ccs.*,				    
				")
				->from('Client c')
				->leftJoin('c.ClientComplaintSettings ccs')
				->where('id = ?',  $clientid);
			$cust->getSqlQuery();
			$disarray = $cust->fetchArray();

 
			if($disarray)
			{
				if($logininfo->usertype == 'SA')
				{
					$this->view->commentbox .= ' <label id="lbl_userlimit" for="userlimit">' . $this->view->translate('UserAccounts') . '</label>
				<input type="text" name="userlimit" id="userlimit" value="' . $disarray[0]['userlimit'] . '" >
				<br /> <label id="lbl_maintainance" for="maintainance">' . $this->view->translate('ismaintainance') . '</label>
				<input type="checkbox" name="maintainance" id="maintainance"';
					if($disarray[0]['maintainance'] == 1)
					{
						$this->view->commentbox.='checked="checked"';
					}
					$this->view->commentbox .= 'value="1"  ><div class="clearer"></div>
				<br /><label for="comment" id="lbl_Client_Kommentarte">' . $this->view->translate('comment') . '</label><textarea name="comment" id="txt_Client_Kommentarte" cols="35" rows="5">' . $disarray[0]['comment'] . '</textarea><br />';
				}
				if(count($disarray) > 0)
				{
					$this->retainValues($disarray[0]);
					if($disarray[0]['maxcontact'] < 1)
					{
						$this->view->maxcontact = 10;
					}
				}
			}

			$ug = new Usergroup();
			$grouparr = $ug->getClientGroups($clientid);

			foreach($grouparr as $k_group => $v_group)
			{
				$client_groups[$v_group['id']] = $v_group['groupname'];
			}

			$this->view->client_groups = $client_groups;
						
			$client_form = new Application_Form_Client(array(
					'_groups' => $client_groups //ISPC - 2271
			));

			//ISPC-2806 Dragos 27.01.2021
			$previleges = new Modules();
			if ($previleges->checkModulePrivileges('249', $clientid)) {
				$client_form->create_form_compliant_settings($disarray[0]['ClientComplaintSettings'],'complaint', (!empty($_POST['complaint']) ? $_POST['complaint'] : array()));
			}
			
			if($this->getRequest()->isPost())
			{
				if($_POST['save_btm_notification_users'] == '1')
				{
					$btm_notif_form = new Application_Form_BtmNotifications();
					$save_form = $btm_notif_form->insert_btm_notifications_users($clientid, $_POST);

					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				}

				if($_POST['save_btm_notification_users'] != '1')
				{
				    
				    $_POST['clientid'] = isset($_POST['clientid']) ? $_POST['clientid'] : $clientid;
				    
					if($client_form->validate($_POST))
					{
						$post = $_POST;
						$post['ppun_allowed'] = $ppun_perms;
						$post['hi_debitornumber_allowed'] = $hi_debitornumber_perms;//ISPC-2452 Ancuta 21.11.2019
						$client_form->UpdateData($post);

						//update client group
						$ins_groups = Application_Form_Anlage14::insert_groups($clientid, $_POST);
						
						//update client plans medi print
						$plans_medi = Application_Form_PlansMediPrint::insert_groups($clientid, $_POST);

						
						//ISPC-2593 Lore 19.05.2020
						#ISPC-2512PatientCharts 
						$head_option = ClientHeaderOptionTable::getInstance()->findOrCreateOneBy('clientid', array( $clientid), $post);
                        //.

						//ISPC-2806 Dragos 27.01.2021
						if ($post['complaint']['status'] == 'enabled'
							|| (
								$post['complaint']['status'] == 'disabled'
								&& $disarray[0]['ClientComplaintSettings']['status'] == 'enabled' //save new status to allow disabling feature
							)
						) {
							Doctrine::getTable('ClientComplaintSettings')->findOrCreateOneBy('clientid', $clientid,$post['complaint']);
						}
						
						$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
						$this->_redirect(APP_BASE . 'client/clientlist?flg=suc');
						exit;
					}
					else
					{
						$client_form->assignErrorMessages();
						$this->retainValues($_POST);
					}
				}
			}

			//get btm notification users
			$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');

			foreach($btm_notification_users as $k_val => $v_val)
			{
				$users_ids[] = $v_val['user'];
				$users_details_arr[] = trim(rtrim($all_client_users[$v_val['user']]));
			}

			$this->view->user_ids_arr = $users_ids;
			$this->view->user_ids_str = implode(',', $users_ids);
			$this->view->selected_usr_str = implode(', ', $users_details_arr);

			//get anlage14 special client groups
			$this->view->client_groups_data = Anlage14ClientGroups::get_anlage14_client_groups($clientid);
			
			
			 //===========medi plans print ISPC 1607============
			$previleges = new Modules();
			$tr = $previleges->checkModulePrivileges("85", $logininfo->clientid);
			$this->view->tr = $previleges->checkModulePrivileges("85", $logininfo->clientid);
			
			$schmerzepumpe = $previleges->checkModulePrivileges("54", $logininfo->clientid);///
			$this->view->schmerzepumpe = $schmerzepumpe;
			
			$plans = Pms_CommonData::get_medi_plans();
			
			if(!$schmerzepumpe)
			{
				unset($plans[4]);
				unset($plans[5]);
			}	
			if(!$tr)
			{
				unset($plans[6]);
			}
			$this->view->plan_medi = $plans;
			$this->view->client_plans_medi = PlansMediPrint::get_plans_medi_print($clientid);
			//===============================================
			
			//ISPC-2161
			$this->view->teammeeting_settings_form = $client_form->create_form_teammeeting_settings($disarray[0]['teammeeting_settings']);
			
			//ISPC-2271
			$show_notfall_settings = $previleges->checkModulePrivileges("123", $clientid);
			if($show_notfall_settings)
			{
				$this->view->notfall_messages_settings_form = $client_form->create_form_notfall_messages_settings($disarray[0]['notfall_messages_settings']);
			}
			else
			{
				$this->view->notfall_messages_settings_form = "";
			}
			
			$this->view->tourenplanung_settings = $client_form->create_form_tourenplanung_settings($disarray[0]['tourenplanung_settings']);
			
			//ISPC-2417 Lore 29.08.2019
			$show_days_after_todo = $previleges->checkModulePrivileges("195", $clientid);
			if($show_days_after_todo)
			{
			    $this->view->days_after_todo_form = $client_form->create_form_days_after_todo($disarray[0]['days_after_todo']);
			}
			else
			{
			    $this->view->days_after_todo_form = "";
			}
			
			//ISPC-2311
			if($disarray[0]['patient_course_settings'])
			{
				$patient_course_settings = $disarray[0]['patient_course_settings'];
			}
			else 
			{
				$patient_course_settings = [
						"v_color" 		=> 	"#33CC66",
						"v_text_color"	=>	"#000000",
						"xt_color" 		=> 	"#33CC66",
						"xt_text_color"	=>	"#000000",
						"u_color" 		=> 	"#33CC66",
						"u_text_color"	=>	"#000000",
				];
			}
			$this->view->patient_course_settings_form = $client_form->create_form_patient_course_settings($patient_course_settings);
				
			//ISPC-2163
			if($disarray[0]['activate_shortcut_v_settings'])
			{
				$activate_shortcut_v_settings = $disarray[0]['activate_shortcut_v_settings'];
			}
			$this->view->activate_shortcut_settings_form = $client_form->create_form_activate_shortcut_settings($activate_shortcut_v_settings);
			
			if($disarray[0]['activate_shortcut_v_settings']['activate_shortcut_v_yes_settings'])
			{
				$this->view->activate_shortcut_yes_settings = $disarray[0]['activate_shortcut_v_settings'];
			}
			
			//ISPC-2593 Lore 19.05.2020
			#ISPC-2512PatientCharts 
			$header_option = ClientHeaderOption::get_client_header_option($clientid);
			$this->view->client_header_option = $header_option;
			//.
			
			
			//ISPC-2636 Lore 29.07.2020	
			$this->view->client_medi_sort = $disarray[0]['client_medi_sort'];
			$this->view->user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];
			//.
			
			//ISPC-2769 Lore 06.01.2021
			$this->view->show_medi_times_when_given = $disarray[0]['show_medi_times_when_given'];
			
			
			//TODO-3365 Carmen 21.08.2020
			$this->view->pharmaindex_settings_form = $client_form->create_form_pharmaindex_settings($disarray[0]['pharmaindex_settings']);
			//--

			//ISPC-2806 Dragos 28.01.2021
			if ($previleges->checkModulePrivileges('249', $clientid)) {
				$this->view->compliant_settings_form = $client_form->getSubForm('complaint_settings');
			}
			
 
			//ISPC-2827 Ancuta 26.03.2021
			$this->view->efa_client = $disarray[0]['efa_client'];
		}

		public function clientlistAction()
		{
			$client = Doctrine::getTable('Client')->findAll();
			$this->view->clientarray = $client->toArray();

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function getjsondataAction()
		{

			$cust = Doctrine_Query::create()
				->select('c.*')
				->from('Client c')
				->where('c.isdelete = ?', 0);
			$track = $cust->execute();

			echo json_encode($track->toArray());
			exit;
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('client', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}


			$columnarray = array("pk" => "id", "cn" => "CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "')  using latin1)", "ctry" => "country", "fn" => "CONVERT(AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "')  using latin1)", "ln" => "CONVERT(AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "')  using latin1)", "em" => "CONVERT(AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "')  using latin1)", "ph" => "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "')  using latin1)");
			
			
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
	       	    $search_text = addslashes(strtolower(trim($_REQUEST['val'])));
			} else {
    			$search_text="";
			}
			
			
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$client = Doctrine_Query::create()
				->select('count(*)')
				->from('Client')
				->where('isdelete = 0');
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{    
			    $client->andWhere("(
			        trim(lower(convert(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(team_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(epid_chars)) like ?
			        or  id like ?
			        )",
			    		
			    	array("%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%")	);
			}
			$client->orderBy(strtolower($columnarray[$_REQUEST['clm']]) . " " . $_REQUEST['ord']);
			$clientexec = $client->execute();
			$clientarray = $clientexec->toArray();

			$limit = 50;
			$client->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
							AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
							,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax");
			$client->where('isdelete = 0');
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
			    $client->andWhere("(trim(lower(convert(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(team_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(convert(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1))) like ?
			        or  trim(lower(epid_chars)) like ?
			        or  id like ?
			        )",
			    	array("%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%","%" . $search_text . "%")	);
			}
			$client->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$client->limit($limit);
			$client->offset($_REQUEST['pgno'] * $limit);
			$this->view->{"style" . $_REQUEST['pgno']} = "active";


			$clientlimitexec = $client->execute();
			$clientlimit = $clientlimitexec->toArray();


			$grid = new Pms_Grid($clientlimit, 1, $clientarray[0]['count'], "listclient.html");
			$this->view->clientgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("clientnavigation.html", 5, $_REQUEST['pgno'], $limit);
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['clientlist'] = $this->view->render('client/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		private function retainValues($values = array())
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		public function clientdeleteAction()
		{
			$this->_helper->viewRenderer('clientlist');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('client', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				if(count($_POST['clientid']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 1;
				}

				if($error == 0)
				{
					if($logininfo->usertype == 'SA')
					{
						foreach($_POST['clientid'] as $key => $val)
						{

							$mod = Doctrine::getTable('Client')->find($val);
							$mod->isdelete = 1;
							$mod->save();
						}

						$this->view->error_message = $this->view->translate('recorddeletedsuccessfully');
					}
					else
					{
						$this->_redirect(APP_BASE . "error/previlege");
					}
				}
			}
		}

		public function setclientidAction()
		{
			
			
			$base_parts = parse_url(APP_BASE);
			
			$redirect_url = $base_parts['scheme'].'://'.$base_parts['host'].(($base_parts['port'] != '80' && $base_parts['port'] != '443') ? ':'.$base_parts['port'].'/' : '/').$_GET['url'];
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			if(($logininfo->usertype == 'SA' || $logininfo->sca == '1') && !empty($_GET['cid']))
			{

				$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
							AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
							,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax,AES_DECRYPT(fileupoadpass,'" . Zend_Registry::get('salt') . "') as fileupoadpass")
					->from('Client')
					->where('id= ?',  $_GET['cid']);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$logininfo->clientid = $_GET['cid'];
				$logininfo->filepass = $clientarray[0]['fileupoadpass'];
				//ISPC-2827 Ancuta 26.03.2021
				$logininfo->isEfaClient = $clientarray[0]['efa_client'];
				//--
				unset($_SESSION['filename']);
			}
			
			$logininfo->_clientModules = null;
			
			Zend_Session::namespaceUnset('Navigation_Menus');
			
			$this->_redirect($redirect_url);
			
		}

		public function assignmodulesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($_GET['id'] > 0)
			{
				$clientid = $_GET['id'];
			}
			elseif($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			if($this->getRequest()->isPost())
			{


				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('assignmodule', $logininfo->userid, 'canadd');

				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}

				$clientmods_form = new Application_Form_AssignModules();
				$clientmods_form->InsertData($_POST);
			}

			$mod = Doctrine_Query::create()
				->select('*')
				->from('Modules')
				->andWhere('isdelete = 0');

			$modexec = $mod->execute();
			$mod->getSqlQuery();

			$this->view->modarray = $modexec->toArray();

			$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax")
				->from("Client")
				->where('id =' . $clientid);
			$clientexec = $client->execute();
			$clientarray = $clientexec->toArray();

			$this->view->clientid = $clientarray[0]['id'];
			$this->view->client_name = $clientarray[0]['client_name'];
		}

		public function activeclientAction()
		{
			$this->_helper->viewRenderer('clientlist');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('client', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}


			if($_GET['id'] > 0)
			{

				$user = Doctrine::getTable('Client')->find($_GET['id']);

				if($_GET['flg'] == 'ina')
				{
					$user->isactive = 1;
				}
				else
				{

					$user->isactive = 0;
				}
				$user->save();
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
		}

		public function copyclientdataAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('copyclientdata', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');

				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('copyclientdata', $logininfo->userid, 'canadd');

				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}

				$client_form = new Application_Form_Client();
				if($client_form->copyclientvalidate($_POST))
				{
					$client_form->CopyclientData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isdelete = 0')
				->andWhere('id = ' . $logininfo->userid)
				->andWhere("usertype != 'SA'");
			$track = $user->execute();

			$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
				,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax")
				->from("Client")
				->where('isdelete=0');
			$clientexec = $client->execute();
			$client_array = array("0" => $this->view->translate('selectclient'));
			foreach($clientexec->toArray() as $key => $val)
			{
				$client_array[$val['id']] = $val['client_name'];
			}
			$this->view->clientarray = $client_array;

			$this->view->tablearray = array("1" => "Health Isurance", "2" => "Family Doctor", "3" => "Reffered By", "4" => "Triggers", "5" => "Symptomatology", "6" => "Course Shortcuts", "7" => "Discharge Location", "8" => "Discharge Method", "9" => "Navigation Menu", "10" => "Client Modules");
		}

		public function deleteclientpatientAction()
		{
			
		}

		public function folgeverordnunguploadAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$cl = new Client();
			$this->view->radioarr = $cl->getImageRadios();


			if($_GET['delid'] > 0)
			{

				if(file_exists("folgeverordnungupload/" . $clientid . "/" . $_GET['flname']))
				{
					unlink("folgeverordnungupload/" . $clientid . "/" . $_GET['flname']);
					$this->_redirect(APP_BASE . "client/folgeverordnungupload");
				}
			}

			if(!file_exists("folgeverordnungupload/" . $clientid))
			{
				mkdir("folgeverordnungupload/" . $clientid, 0700);
				$count = 0;
			}
			else
			{
				if($handle = opendir("folgeverordnungupload/" . $clientid))
				{
					$count = 0;
					$filearr = array();
					while(false !== ($file = readdir($handle)))
					{
						if($file != "." && $file != "..")
						{
							$filearr[] = $file;
							$count++;
						}
					}
					closedir($handle);
				}
			}
			$hidesubmit = 0;

			$smallimage = 0;
			$bigimage = 0;
			$docimage = 0;

			if(in_array("smallimage.jpg", $filearr))
			{
				$smallimage = 1;
			}

			if(in_array("bigimage.jpg", $filearr))
			{
				$bigimage = 1;
			}

			if(in_array("doctorletterimage.jpg", $filearr))
			{
				$docimage = 1;
			}

			$this->view->smallimage = $smallimage;
			$this->view->bigimage = $bigimage;
			$this->view->docimage = $docimage;

			$error_message = "No Image Uploaded";

			$hidesubmit = 0;
			if($clientid < 1)
			{
				$hidesubmit = 1;
				$error_message = "Please Select Client";
			}

			$this->view->error_message = $error_message;


			$this->view->hidesubmit = $hidesubmit;

			switch($_GET['radval'])
			{
				case 1 : $fln = "smallimage.jpg";
					break;
				case 2 : $fln = "bigimage.jpg";
					break;
				case 3 : $fln = "doctorletterimage.jpg";
					break;
				default : break;
			}

			if($_GET['upload'] == 1)
			{
				ini_set("upload_max_filesize", "10M");
				$filename = "uploadfile/" . $_FILES['qqfile']['name'];
				$_SESSION['filename'] = $_FILES['qqfile']['name'];
				move_uploaded_file($_FILES['qqfile']['tmp_name'], "folgeverordnungupload/" . $clientid . "/" . $fln);

				echo json_encode(array('success' => true, 'flcount' => $count, 'tm' => time(), 'filename' => $fln, 'radval' => $_GET['radval']));
				exit;
			}
		}

		public function clientcategoryfb3Action()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$clientcat = new ClientFb3categories();
			$categ = $clientcat->getClientFb3categories($clientid);
			$category_defaultarray = $clientcat->defaultClientFb3categories();


			foreach($category_defaultarray as $def)
			{
				$cats[$def['cid']]['cid'] = $def['cid'];
				$cats[$def['cid']]['default'] = $def['title'];
				foreach($categ as $val)
				{
					if($def['cid'] == $val['categoryid'])
					{
						$cats[$def['cid']]['newtitle'] = $val['category_title'];
					}
				}
			}
			$this->view->category_default = $cats;
		}

		public function clientcategoryfb3editAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$ctid = $_GET['ctid'];

			$clientcat = new ClientFb3categories();
			$category_default = $clientcat->defaultClientFb3categories();

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$catname = $category_default[$ctid]['title'];
			$this->view->category_default = $catname;

			$categ = $clientcat->getClientFb3categoriesBycat($clientid, $ctid);

			if($categ)
			{
				$this->view->category_title = $categ[0]['category_title'];
			}
			else
			{
				$this->view->category_title = $catname;
			}


			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_ClientFb3categories();
				$client_form->insertFb3categories($_POST, $ctid);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "client/clientcategoryfb3?flg=suc");
			}
		}

		public function assignclientsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();

			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			else if($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			if($this->getRequest()->isPost())
			{

				if(!empty($_POST['assigned_client'])) //adaugare daca nu are id grup
				{

					if(empty($_POST['edit_group'])) //new group
					{
						$new_group = $assoc_group->associated_groups_create(); //newly created groupid
					}
					else //edit group
					{
						$new_group = $_POST['edit_group'];
					}

					$add_clients = $assoc_clients->clients_group_add($_POST['assigned_client'], $new_group);

					if($add_clients)
					{
						$this->_redirect(APP_BASE . 'client/assignclients?flg=suc');
					}
					else
					{
						$this->_redirect(APP_BASE . 'client/assignclients?flg=err');
					}
				}
			}


			if($_REQUEST['act'] == 'del' && !empty($_REQUEST['gid']))
			{
				$delete_group = $assoc_group->associated_groups_mark_deleted($_REQUEST['gid']);
				$delete_clients_group = $assoc_clients->clients_group_set_delete($_REQUEST['gid']);
				$this->_redirect(APP_BASE . 'client/assignclients?flg=suc');
			}
			else if($_REQUEST['act'] == 'edit' && !empty($_REQUEST['gid']))
			{
				$group_clients = $assoc_clients->clients_groups_get($_REQUEST['gid']);
				$this->view->group_clients = $group_clients;
			}



			$agroups = $assoc_group->associated_groups_get();
			$this->view->agroups = $agroups;

			$groups_ids[] = '999999999999';
			foreach($agroups as $group)
			{
				$groups_ids[] = $group['id'];
			}

			$groups_clients = $assoc_clients->clients_groups_get($groups_ids);
			$this->view->groups_clients = $groups_clients;

			$sql = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
		AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
		AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
		AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
		AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
		AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
		AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
		AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
		AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
		AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
			//get rest clients
			$clients = Doctrine_Query::create()
				->select($sql)
				->from('Client')
				->where('isdelete = 0');
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_client => $v_client)
			{
				$clientsarray[$v_client['id']] = $v_client;
			}

			$this->view->clientsarray = $clientsarray;

			//get curently client name
			$this->view->client_name = $clientsarray[$clientid]['client_name'];
		}

		public function sharepatientAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();
			$linked_patients = new PatientsLinked();
			$client = new Client();


			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			else if($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}
			// Maria:: Migration ISPC to CISPC 08.08.2020
			//TODO-2686 Lore 29.11.2019
			//TODO-2686 Ancuta 19.12.2019
			if(($_REQUEST['cid'] > 0) && ($_REQUEST['cid'] != $logininfo->clientid)){
			    $clientid = $logininfo->clientid;
			    $this->_redirect(APP_BASE . 'client/sharepatient');//TODO-2686 Ancuta 19.12.2019
			    exit;
			}
			
			//get all client patients
			$client_pats = Doctrine_Query::create()
				->select("p.ipid,e.epid")
				->from('PatientMaster p')
				->Where('isdelete = 0');
			$client_pats->leftJoin("p.EpidIpidMapping e");
			$client_pats->andWhere('e.clientid = ' . $clientid);


			$client_patients = $client_pats->fetchArray();

			$client_patients_ipids[] = '9999999999';
			foreach($client_patients as $patient_data)
			{
				$client_patients_ipids[] = $patient_data['ipid'];
				$ipid2epid[$patient_data['ipid']] = $patient_data['EpidIpidMapping']['epid'];
				$ipid2encid[$patient_data['ipid']] = Pms_Uuid::encrypt($patient_data['id']);
				
			}
			
			//get all client connected patients 
			//TODO-2371
			$sp = Doctrine_Query::create()
			->select('*')
			->from('PatientsMarked')
			->where('source = ?', $clientid)
			->andWhereIn('ipid',$client_patients_ipids);
			$sp->andWhere('status != "c" ');  //TODO-2808 Ancuta 14.01.2020
			$s_patients = $sp->fetchArray();
			
			$ipids2client = array();
			$epids2client = array();
			foreach($s_patients as $kp=>$shared_p){
			    $ipids2client[$ipid2encid[$shared_p['ipid']]][]= $shared_p['target'];
			    $encrp2client[$ipid2encid[$shared_p['ipid']]][]= $shared_p['target'];
			    if ( ! in_array($shared_p['target'],$epids2client[$ipid2epid[$shared_p['ipid']]])){
    			    $epids2client[$ipid2epid[$shared_p['ipid']]][]= $shared_p['target'];
			    }
			}

			//set associated clients
			$this->view->connected_epids2client = $epids2client;
			$this->view->js_connected_epids2client= json_encode($epids2client);
				
			if(!empty($_REQUEST['st']) && !empty($_REQUEST['sid']))
			{
				$change_status = $marked_patients->change_status($_REQUEST['sid'], strtolower($_REQUEST['st']));

				if($change_status)
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=suc&case=status&cid=' . $clientid);
				}
				else
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=err&case=status&cid=' . $clientid);
				}
			}

			//processs post data
			if($this->getRequest()->isPost())
			{
				$source_ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_POST['patientid']));

				// Maria:: Migration ISPC to CISPC 08.08.2020
				//TODO-2686 Ancuta 19.12.2019
				// validate
				if(!isset($_POST['target_client']) || empty($source_ipid)){
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=no_target_client');
					exit;
				}
				
				//TODO-2808 Ancuta 14.01.2020 - do not calculate canceled - if canceled - then the connection can be allowed again
				$ipid2targetclient = array();
				$ipid2targetclient = Doctrine_Query::create()
				->select('*')
				->from('PatientsMarked')
				->where('source = ?', $clientid)
				->andWhere('target = ?', $_POST['target_client'])
				->andWhere('ipid =?',$source_ipid)
				->andWhere('status != "c" ')
				->fetchArray();
				

				//TODO-3979 Ancuta 23.02.2021				
				//check if ipid it is a target in PatientsLinked. If so - check if the source is in the tharget client from post .
 				$ipid2targetipidclient = array();
				$ipid2targetipidclient = Doctrine_Query::create()
				->select('*')
				->from('PatientsLinked')
				->where('target = ?', $source_ipid)
				->fetchArray();
				
				$link_pats = array();
				foreach($ipid2targetipidclient as $k=>$pl){
				    $link_pats[] = $pl['source'];
				}
				
				if(!empty($link_pats)){
    				$pepidss =  array();
    				$pepidss = Doctrine_Query::create()
    				->select('ipid,clientid')
    				->from('EpidIpidMapping')
    				->whereIn('ipid', $link_pats)
    				->fetchArray();
    				
    				foreach($pepidss as $k => $ep_data){
    				    if($ep_data['clientid'] == $_POST['target_client']){
    				        $ipid2targetclient[] = $ep_data;
    				    }
    				} 
				}
				//--
				
				if(!empty($ipid2targetclient)){
				    // 
				    $client_dets = $client->getClientDataByid($_POST['target_client']);
				    $target_client_details =$client_dets[0];
				    
				    $this->_redirect(APP_BASE . 'client/sharepatient?flg=patient_exists_in_target&p='.$_POST['patientsearch_share'].'&tc='.$target_client_details['client_name']);
					exit;
				}
				//-- 
				//processing incoming patient id encrypted
				$mark_patient = new PatientsMarked();
				$mark_patient->ipid = $source_ipid;
				$mark_patient->source = $clientid;
				$mark_patient->target = $_POST['target_client'];
				$mark_patient->copy = $_POST['allow_copy'];
				$mark_patient->copy_options = implode(',', $_POST['copy_options']);
				$mark_patient->copy_files = $_POST['copy_files'];
				$mark_patient->request = $_POST['request_share'];
				$mark_patient->shortcuts = implode(',', $_POST['shortcut']);
				$mark_patient->status = 'p';
				$mark_patient->save();

				if($mark_patient->id)
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=suc&case=save&cid=' . $clientid);
				}
				else
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=err&case=save&cid=' . $clientid);
				}
			}

			//get associated clients of current clientid
			$associated_clients = $assoc_clients->associated_clients_get($clientid, true);

			//set associated clients
			$this->view->associated_clients = $associated_clients;

			//array with clients ids for gathering client data
			if(is_numeric($clientid))
			{
				$asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
			}
			else
			{
				$asociated_clients_ids[] = '999999999';
			}

			foreach($associated_clients as $k_aclient => $v_aclient)
			{
				foreach($v_aclient as $asociated_client_id => $asociated_client_value)
				{
					$asociated_clients_ids[] = $asociated_client_id;
				}
			}

			$asociated_clients_ids = array_unique($asociated_clients_ids);
			asort($asociated_clients_ids);
			$asociated_clients_ids = array_values($asociated_clients_ids);

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $asociated_clients_ids);
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}

			//set clients details
			$this->view->clients_details = $clientsarray;

			//set curent client name
			$this->view->client_name = $clientsarray[$clientid]['client_name'];
			$this->view->clientid = $clientid;


			//get client verlauf shortcuts
			$allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');

			$cs = new Courseshortcuts();
			$ltrarray = $cs->getFilterCourseData();

			foreach($ltrarray as $k_letter => $v_letter)
			{
// 				if(in_array($v_letter['shortcut'], $allowed_shortcuts))
// 				{
					$final_letters[$v_letter['shortcut']] = $v_letter;
// 				}
			}

			ksort($final_letters);
			$this->view->sharing_shortcuts = $final_letters;


			$all_requests = array();
//moved to own fetch function 20.07.2015 - RWH - ISPC-1378 - fetchsentrequestAction
//			//get "shared to" patients
//			$sent_requests = $marked_patients->sent_requests_get($clientid);
//
//			foreach($sent_requests as $k_shared_pat => $v_sent_requests)
//			{
//				//ipids for gathering data
//				$all_requests[] = $v_sent_requests['ipid'];
//			}
//
//			$this->view->sent_requests = $sent_requests;

//moved to own fetch function 20.07.2015 - RWH - ISPC-1378 - fetchreceivedrequestAction
//			//get "shared from" patients
//			$received_requests = $marked_patients->received_requests_get($clientid);
//
//			foreach($received_requests as $k_received_pat => $v_received_requests)
//			{
//				//ipids for gathering data
//				$all_requests[] = $v_received_requests['ipid'];
//			}
//
//			$this->view->received_requests = $received_requests;

			//get patients data
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

//			//shared patients linked
//			$cspl = $linked_patients->client_sent_linked_patients($client_patients_ipids);
//			$this->view->client_sent_patients_linked = $cspl;
//
//
//			foreach($cspl as $link)
//			{
//				$linked_ipids[] = $link['source'];
//				$linked_ipids[] = $link['target'];
//
//				//ipids for gathering data
//				$all_requests[] = $link['source'];
//				$all_requests[] = $link['target'];
//			}

			//received patients linked
			$crpl = $linked_patients->client_received_linked_patients($client_patients_ipids);
			$this->view->client_received_patients_linked = $crpl;


			foreach($crpl as $link)
			{
				$linked_ipids[] = $link['source'];
				$linked_ipids[] = $link['target'];

				//ipids for gathering data
				$all_requests[] = $link['source'];
				$all_requests[] = $link['target'];
			}

			$all_requests = array_unique($all_requests);

			if(empty($all_requests))
			{
				$all_requests[] = '999999999999';
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $all_requests)
				->leftJoin("p.EpidIpidMapping e");

			$pat_details = $patient->fetchArray();

			foreach($pat_details as $k_pat_details => $v_pat_details)
			{
				$patient_details[$v_pat_details['ipid']] = $v_pat_details;
			}

			$this->view->patient_details = $patient_details;

			$cd = $client->getClientData();

			foreach($cd as $k_client => $v_client)
			{
				$clients_data[$v_client['id']] = $v_client;
			}
			$this->view->clients_data = $clients_data;
		}
		
		public function fetchsentrequestAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();
			$linked_patients = new PatientsLinked();
			$client = new Client();

			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;

			//get associated clients of current clientid
			$associated_clients = $assoc_clients->associated_clients_get($clientid, true);

			//array with clients ids for gathering client data
			if(is_numeric($clientid))
			{
				$asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
			}
			else
			{
				$asociated_clients_ids[] = '999999999';
			}

			foreach($associated_clients as $k_aclient => $v_aclient)
			{
				foreach($v_aclient as $asociated_client_id => $asociated_client_value)
				{
					$asociated_clients_ids[] = $asociated_client_id;
				}
			}

			$asociated_clients_ids = array_values(array_unique($asociated_clients_ids));

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $asociated_clients_ids);
			$clients_arrays = $clients->fetchArray();
			
			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}

			//get "shared to" patients
			$all_requests = array();
			$sent_requests = $marked_patients->sent_requests_get($clientid);
			
			foreach($sent_requests as $k_shared_pat => $v_sent_requests)
			{
				//ipids for gathering data
				$all_requests[] = $v_sent_requests['ipid'];
			}

			//get involved patients data
			//get patients data
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$all_requests = array_unique($all_requests);

			if(empty($all_requests))
			{
				$all_requests[] = '999999999999';
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
// 				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $all_requests)
				->leftJoin("p.EpidIpidMapping e");

			$pat_details = $patient->fetchArray();

			foreach($pat_details as $k_pat_details => $v_pat_details)
			{
				$patient_details[$v_pat_details['ipid']] = $v_pat_details;
			}

			$this->view->patient_details = $patient_details;
			
			$requests_count = count($sent_requests);
			$grid = new Pms_Grid($sent_requests,1,$requests_count,"list_share_sent_requests.html");
			$grid->appbase = APP_BASE;
			$grid->res_filepath = RES_FILE_PATH;
			$grid->client_details = $clientsarray;
			$this->view->sent_requests_list_grid = $grid->renderGrid();
//			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			if(!empty($sent_requests))
			{
				$response['callBackParameters']['sent_requests_list'] =$this->view->render('client/fetchsentrequest.html');
			}
			else
			{
				$response['callBackParameters']['sent_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate("no_sent_requests").'</td>
				</tr>';
				
				
			}

			echo json_encode($response);
			exit;
		}
		
		public function fetchreceivedrequestAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();
			$linked_patients = new PatientsLinked();
			$client = new Client();

			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			
			
			//get associated clients of current clientid
			$associated_clients = $assoc_clients->associated_clients_get($clientid, true);
			
			//array with clients ids for gathering client data
			if(is_numeric($clientid))
			{
				$asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
			}
			else
			{
				$asociated_clients_ids[] = '999999999';
			}

			foreach($associated_clients as $k_aclient => $v_aclient)
			{
				foreach($v_aclient as $asociated_client_id => $asociated_client_value)
				{
					$asociated_clients_ids[] = $asociated_client_id;
				}
			}

			$asociated_clients_ids = array_values(array_unique($asociated_clients_ids));

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $asociated_clients_ids);
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}	

			//get "shared from" patients
			$all_requests = array();
			$received_requests = $marked_patients->received_requests_get($clientid);

			foreach($received_requests as $k_received_pat => $v_received_requests)
			{
				//ipids for gathering data
				$all_requests[] = $v_received_requests['ipid'];
			}

			//get involved patients data
			//get patients data
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$all_requests = array_unique($all_requests);

			if(empty($all_requests))
			{
				$all_requests[] = '999999999999';
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
// 				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $all_requests)
				->leftJoin("p.EpidIpidMapping e");

			$pat_details = $patient->fetchArray();

			$patient_details = array();
			$client_patients_ipids = array();
			foreach($pat_details as $k_pat_details => $v_pat_details)
			{
				$patient_details[$v_pat_details['ipid']] = $v_pat_details;
				$client_patients_ipids[] = $v_pat_details['ipid'];      //ISPC-2592 Lore 23.06.2020
			}
			
			$this->view->patient_details = $patient_details;
			
			//ISPC-2592 Lore 23.06.2020
			$cspl = $linked_patients->client_sent_linked_patients($client_patients_ipids);
			$cspl_ipids = array();
			$target_ipids = array();
			foreach($cspl as $k_cspl => $v_cspl)
			{
			    $cspl_ipids[] = $v_cspl['target']; 
			    $target_ipids[$v_cspl['source']] = $v_cspl['target'];
			}
			if(!empty($cspl_ipids)){//TODO-3905 Ancuta 25.02.2021
				$patient_cspl = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				// 				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $cspl_ipids)
				->leftJoin("p.EpidIpidMapping e");
				$pat_details_cspl = $patient_cspl->fetchArray();
			}
			$patient_details_cspl = array();
			foreach($pat_details_cspl as $k_pat_details_cspl => $v_pat_details_cspl)
			{
			    $patient_details_cspl[$v_pat_details_cspl['ipid']] = $v_pat_details_cspl;
			}
			$this->view->target_ipids = $target_ipids;
			$this->view->pat_details_cspl = $patient_details_cspl;
			//.
			
			
			$requests_count = count($received_requests);
			$grid = new Pms_Grid($received_requests,1,$requests_count,"list_share_received_requests.html");
			$grid->appbase = APP_BASE;
			$grid->res_filepath = RES_FILE_PATH;
			$grid->client_details = $clientsarray;
			$this->view->received_requests_list_grid = $grid->renderGrid();
//			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			if(!empty($received_requests))
			{
				$response['callBackParameters']['received_requests_list'] =$this->view->render('client/fetchreceivedrequest.html');
			}
			else
			{
				$response['callBackParameters']['received_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate('no_received_requests').'</td>
				</tr>';
			}

			echo json_encode($response);
			exit;
		}
		
		public function fetchsharedrequestAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();
			$linked_patients = new PatientsLinked();
			$client = new Client();

			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			
			
			//get associated clients of current clientid
			$associated_clients = $assoc_clients->associated_clients_get($clientid, true);
			
			//array with clients ids for gathering client data
			if(is_numeric($clientid))
			{
				$asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
			}
			else
			{
				$asociated_clients_ids[] = '999999999';
			}

			foreach($associated_clients as $k_aclient => $v_aclient)
			{
				foreach($v_aclient as $asociated_client_id => $asociated_client_value)
				{
					$asociated_clients_ids[] = $asociated_client_id;
				}
			}

			$asociated_clients_ids = array_values(array_unique($asociated_clients_ids));

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $asociated_clients_ids);
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}
			
			//get all client patients
			$client_pats = Doctrine_Query::create()
				->select("p.ipid")
				->from('PatientMaster p')
// 				->Where('isdelete = 0')
			;
			$client_pats->leftJoin("p.EpidIpidMapping e");
			$client_pats->andWhere('e.clientid = ' . $clientid);
			$client_patients = $client_pats->fetchArray();

			$client_patients_ipids[] = '9999999999';
			foreach($client_patients as $patient_data)
			{
				$client_patients_ipids[] = $patient_data['ipid'];
			}

			//shared patients linked
			$all_requests = array();
			$cspl = $linked_patients->client_sent_linked_patients($client_patients_ipids);
			$this->view->client_sent_patients_linked = $cspl;

			foreach($cspl as $k_link => $link)
			{
				//ipids for gathering data
				$all_requests[] = $link['source'];
				$all_requests[] = $link['target'];
			}

			//get involved patients data
			//get patients data
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$all_requests = array_unique($all_requests);

			if(empty($all_requests))
			{
				$all_requests[] = '999999999999';
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
// 				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $all_requests)
				->leftJoin("p.EpidIpidMapping e");

			$pat_details = $patient->fetchArray();

			foreach($pat_details as $k_pat_details => $v_pat_details)
			{
				$patient_details[$v_pat_details['ipid']] = $v_pat_details;
			}

			$this->view->patient_details = $patient_details;
			
			$requests_count = count($cspl);
			$grid = new Pms_Grid($cspl,1,$requests_count,"list_share_shared_requests.html");
			$grid->appbase = APP_BASE;
			$grid->res_filepath = RES_FILE_PATH;
			$grid->client_details = $clientsarray;
			$this->view->shared_requests_list_grid = $grid->renderGrid();
//			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			if(!empty($cspl))
			{
				$response['callBackParameters']['shared_requests_list'] =$this->view->render('client/fetchsharedrequest.html');
			}
			else
			{
				$response['callBackParameters']['shared_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate('no_shared_patients').'</td>
				</tr>';
			}

			echo json_encode($response);
			exit;
		}
		
		public function fetchreceivedsharedrequestAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();
			$linked_patients = new PatientsLinked();
			$client = new Client();

			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			
			
			//get associated clients of current clientid
			$associated_clients = $assoc_clients->associated_clients_get($clientid, true);
			
			//array with clients ids for gathering client data
			if(is_numeric($clientid))
			{
				$asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
			}
			else
			{
				$asociated_clients_ids[] = '999999999';
			}

			foreach($associated_clients as $k_aclient => $v_aclient)
			{
				foreach($v_aclient as $asociated_client_id => $asociated_client_value)
				{
					$asociated_clients_ids[] = $asociated_client_id;
				}
			}

			$asociated_clients_ids = array_values(array_unique($asociated_clients_ids));

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $asociated_clients_ids);
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}
			
			//get all client patients
			$client_pats = Doctrine_Query::create()
				->select("p.ipid")
				->from('PatientMaster p')
// 				->Where('isdelete = 0')
			;
			$client_pats->leftJoin("p.EpidIpidMapping e");
			$client_pats->andWhere('e.clientid = ' . $clientid);
			$client_patients = $client_pats->fetchArray();

			$client_patients_ipids[] = '9999999999';
			foreach($client_patients as $patient_data)
			{
				$client_patients_ipids[] = $patient_data['ipid'];
			}

			//received shared patients linked
			$crpl = $linked_patients->client_received_linked_patients($client_patients_ipids);
			$this->view->client_received_patients_linked = $crpl;


			foreach($crpl as $link)
			{
				$linked_ipids[] = $link['source'];
				$linked_ipids[] = $link['target'];

				//ipids for gathering data
				$all_requests[] = $link['source'];
				$all_requests[] = $link['target'];
			}

			//get involved patients data
			//get patients data
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$all_requests = array_unique($all_requests);

			if(empty($all_requests))
			{
				$all_requests[] = '999999999999';
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
// 				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $all_requests)
				->leftJoin("p.EpidIpidMapping e");

			$pat_details = $patient->fetchArray();

			foreach($pat_details as $k_pat_details => $v_pat_details)
			{
				$patient_details[$v_pat_details['ipid']] = $v_pat_details;
			}

			$this->view->patient_details = $patient_details;
			
			$requests_count = count($crpl);
			$grid = new Pms_Grid($crpl,1,$requests_count,"list_share_shared_requests.html");
			$grid->appbase = APP_BASE;
			$grid->res_filepath = RES_FILE_PATH;
			$grid->client_details = $clientsarray;
			$this->view->received_shared_requests_list_grid = $grid->renderGrid();
//			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			if(!empty($crpl))
			{
				$response['callBackParameters']['received_shared_requests_list'] =$this->view->render('client/fetchreceivedsharedrequest.html');
			}
			else
			{
				$response['callBackParameters']['received_shared_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate('no_shared_patients').'</td>
				</tr>';
			}

			echo json_encode($response);
			exit;
		}

		public function patientsearchAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$clientid = $logininfo->clientid;
			if(strlen($_REQUEST['ltr']) > 2)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("clientid = ?", $clientid)
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$patient_epids[$val['ipid']] = strtoupper($val['epid']);
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
					}
				}

				$user_patients = PatientUsers::getUserPatients($logininfo->userid);
				if(count($droparray) > 0)
				{
					$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
					$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
					$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
					$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
					$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
					$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
					$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
					$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
					$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
					$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
					$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
					$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "*,";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
						$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					}

					$patient = Doctrine_Query::create()
						->select($sql)
						->from('PatientMaster p')
						->where("p.ipid in(" . $ipidval . ")  and p.ipid IN (" . $user_patients['patients_str'] . ") and p.isdelete = 0");
					$patient->leftJoin("p.EpidIpidMapping e");
					$patient->andwhere("e.clientid = ?",$logininfo->clientid );
					$patient->andwhere("trim(lower(e.epid)) like ? or 
							    (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
							    trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
							    concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)"
							,array(trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
									trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
									trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
									"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
									"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
									"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
									"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%"));

					if($logininfo->hospiz == 1)
					{
						$patient->andwhere('ishospiz = 1');
					}

					$patient->orderby('status,ipid');
					$droparray1 = $patient->fetchArray();
				}
				elseif($logininfo->showinfo == 'show')
				{

					$fndrop = Doctrine_Query::create()
						->select('*')
						->from('EpidIpidMapping')
						->where("clientid = '" . $clientid . "'");
					$fndroparray = $fndrop->fetchArray();

					if($fndroparray)
					{
						$comma = ",";
						$fnipidval = "'0'";
						foreach($fndroparray as $key => $val)
						{
							$fnipidval .= $comma . "'" . $val['ipid'] . "'";
							$comma = ",";
						}
					}


					$patient1 = Doctrine_Query::create()
						->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
								AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,
								AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
								AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as oll,
								AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
								AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,
								AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
								AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
								AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip
								,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
								,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone
								,AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,
								IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status
								,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
						->from('PatientMaster')
						->where("isdelete = 0 ")
						->andWhere("ipid in(" . $fnipidval . ")")
// 						->andWhere("(trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_REQUEST['ltr'] . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_REQUEST['ltr'] . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')) or
// 								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')) or
// 								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')) or
// 								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')))")
								
						->andWhere("(
								trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_REQUEST['ltr'] . "%')) or 
								trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_REQUEST['ltr'] . "%'))  or 
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')) or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')) or
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')) or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['ltr'] . "%')))",
								array(
								trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
								trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
								"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
								"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
								"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",
								"%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%")
								)
						->orderby('status');

					$droparray2 = $patient1->fetchArray();
				}
			}
			
			
			if(is_array($droparray2) || is_array($droparray1))
			{
				$res = array_merge((array) $droparray2, (array) $droparray1);

				for($i = 0; $i < count($res); $i++)
				{
					$res[$i]['status'] = $res[$i]['status'];


					if(strlen($res[$i]['middle_name']) > 0)
					{
						$res[$i]['middle_name'] = $res[$i]['middle_name'];
					}
					else
					{
						$res[$i]['middle_name'] = " ";
					}

					$res[$i]['epid'] = $patient_epids[$res[$i]['ipid']];

					if($res[$i]['admission_date'] != '0000-00-00 00:00:00')
					{
						$res[$i]['admission_date'] = date('d.m.Y', strtotime($res[$i]['admission_date']));
					}
					else
					{
						$res[$i]['recording_date'] = "-";
					}

					if($res[$i]['recording_date'] != '0000-00-00 00:00:00')
					{
						$res[$i]['recording_date'] = date('d.m.Y', strtotime($res[$i]['recording_date']));
					}
					else
					{
						$res[$i]['recording_date'] = "-";
					}

					if($res[$i]['birthd'] != '0000-00-00 00:00:00')
					{
						$res[$i]['birthd'] = date('d.m.Y', strtotime($res[$i]['birthd']));
					}
					else
					{
						$res[$i]['birthd'] = "-";
					}

					$res[$i]['birthd'] = Pms_CommonData::hideInfo($res[$i]['birthd'], $res[$i]['isadminvisible']);

					$res[$i]['id'] = Pms_Uuid::encrypt($res[$i]['id']);
				}
			}
			else
			{
				$res = array();
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "searchdropdiv_share";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['refs'] = $res;

			echo json_encode($response);
			exit;
		}

		public function processpatientAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$shareid = $_REQUEST['sid'];

			$hidemagic = Zend_Registry::get('hidemagic');

			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			else if($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();
			$share_patients = new PatientsShare();
			
			$share_details = $marked_patients->share_get($_REQUEST['sid'], $clientid);
			if(!empty($share_details)){
			    $s_pid= $share_details['0']['ipid'];
			    
    			$linked_patients = new PatientsLinked();
    			$existing_connections = $linked_patients->linked_patients($s_pid);
//     			dd($existing_connections);
			}
			
			
			
			if($this->getRequest()->isPost())
			{
				if(!empty($_REQUEST['sid']))
				{
				    //get shared(marked) request
				    $marked_patient = $marked_patients->share_get($_REQUEST['sid']);
				    $copy_options = explode(',', $marked_patient[0]['copy_options']);
				    $medications = new PatientDrugPlan();
				    
					//copy patient data procedure
					if($_POST['combine'] === '0')
					{
						//load required models
						$patient_master = new PatientMaster();
						$epid_ipid = new EpidIpidMapping();
						$familydoc = new FamilyDoctor();
						$sapvverordnung = new SapvVerordnung();
						$contact_person = new ContactPersonMaster();
						$patient_health_insurance = new PatientHealthInsurance();
						$patient_pflegedienste = new PatientPflegedienste();
						$patient_voluntary = new PatientVoluntaryworkers();
						$patient_pharmacy = new PatientPharmacy();
						$patient_therapy = new PatientTherapieplanung();
						$patient_mobility = new PatientMobility();
						$patient_lives = new PatientLives();
						$patient_rel = new PatientReligions();
						$patient_master_data = new Stammdatenerweitert();
						$patient_more_info = new PatientMoreInfo();
						$patient_maintainance_stage = new PatientMaintainanceStage();
						$patient_supply = new PatientSupply();

					
						$diagnosis = new PatientDiagnosis();



						//Patient Master general copy
						$save_pm = $patient_master->clone_record($marked_patient[0]['ipid'], $marked_patient[0]['target']);

						//Create patient new epid based on client id and save it in epid_ipid
						$target_epid = Pms_Uuid::GenerateEpid($marked_patient[0]['target']);

						$data['epid'] = $target_epid;
						$data['ipid'] = $save_pm['ipid'];
						$save_epid = $epid_ipid->epid_cloned_patient($data, $marked_patient[0]['target']);

						if(in_array('1', $copy_options))  //copy stammdaten data
						{
							// Family Doctor copy
//							$family_doctor = $familydoc->clone_record($save_pm['familydoc_id'], $save_pm['ipid'], $marked_patient[0]['target']); //to be updated in PM!!! DONE
							$family_doctor = $familydoc->clone_record($save_pm['familydoc_id'], $marked_patient[0]['target']); //TODO-2413 remove ipid as param - not needed in cline function 
							//SAPV Verordung copy
							$sapvs = $sapvverordnung->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);

							//ContactPerson copy
							$contact_persons = $contact_person->clone_records($marked_patient[0]['ipid'], $save_pm['ipid']);

							//Patient health insurance copy
							$phi = $patient_health_insurance->clone_record($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);

							//Pflegedienste
							$pflege = $patient_pflegedienste->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);

							//Ehrenamtliche
							$volunteer = $patient_voluntary->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);

							//Apotheke
							$pharmacy = $patient_pharmacy->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);

							//Therapieplanung
							$therapy = $patient_therapy->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//Mobility
							$mobility = $patient_mobility->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//Patient lives
							$patients_lives_status = $patient_lives->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//religion
							$patient_religion = $patient_rel->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//Familienstand (stammdaten erweitert)
							$pmaster_data = $patient_master_data->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//Patient more info
							$pat_more_info = $patient_more_info->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//patient maintainance stage
							$patient_maintainance = $patient_maintainance_stage->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//patient supply (missing box)
							$patient_supply_copy = $patient_supply->clone_record($marked_patient[0]['ipid'], $save_pm['ipid']);

							//Update PM with follownig data:
							$p_master = Doctrine::getTable('PatientMaster')->findOneByIpid($save_pm['ipid']);

							//#1 Family Doctor
							$p_master->familydoc_id = $family_doctor;

							//save
							$p_master->save();
						}

						if(in_array('2', $copy_options))  //copy diagnosis data
						{
							//Diagnosis
							$diag = $diagnosis->clone_records($marked_patient[0]['ipid'], $marked_patient[0]['source'], $save_pm['ipid'], $marked_patient[0]['target']);
						}

						if(in_array('3', $copy_options))  //copy medications data
						{
							//Medications
							$medis = $medications->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target'], $marked_patient[0]['source']);
							  // insert in patient_drugplan_share - medication from  source, and target
    						  $source_medication = $medications->getPatientAllDrugs($marked_patient[0]['ipid']);
    						  
    						  if(!empty($source_medication)){
                                    foreach($source_medication as $k=>$dr){
                                        $insert_source_meds = new PatientDrugPlanShare();
                                        $insert_source_meds->ipid = $marked_patient[0]['ipid'];
                                        $insert_source_meds->drugplan_id = $dr['id'];
                                        $insert_source_meds->create_date = date("Y-m-d H:i:s",time());
                                        $insert_source_meds->save();
                                    }        						      
    						  }							
						}


						//save shortcuts in copy mode (non combine)
						$save_shared_shortcuts = $share_patients->save_shortcuts($_REQUEST['sid'], $_POST, $save_pm['ipid']);
						
						
						
						// COPY FILES
						if($marked_patient['0']['copy_files'] == "1"){
						      
// 						    $copy_files = $share_patients->save_files($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);

						    // copy_files 
						    // selectat toate fisierele pacientului sursa
						    
						    // copy in target patient and chack with source id if patient file was transfered alredy.
						    
						    
						    // de facut un cron
						    // - luam toti pacienti cu fisiere "sharate"
						    // si pt fieecare copiem fisierele de la soursa la target
						    
						    //  
						    
						}
					}
					else
					{
                         if(!empty($_POST['patientid'])){
    						  //save shortcuts in share mode (combine mode)
    						  $save_shared_shortcuts = $share_patients->save_shortcuts($_REQUEST['sid'], $_POST); //matched pat id is in the post>ipid to match
       						  $target_ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_POST['patientid']));
       						  
       						  // check if source i
       						  
       				
//        						  dd($existing_connections);
    						  
    						  if(in_array('3', $copy_options))  //copy medications data
    						  {
        						  // insert in patient_drugplan_share - medication from  source, and target
        						  $target_ipid_med = $target_ipid;
        						  $meds_ipids = array($marked_patient[0]['ipid'],$target_ipid_med );
        						  
        						  $share_drug_src = Doctrine_Query::create()
        						  ->select("*")
        						  ->from('PatientDrugPlanShare')
        						  ->whereIn('ipid',$meds_ipids);
        						  $share_drug_src_arra = $share_drug_src->fetchArray();
        						  
        						  foreach($share_drug_src_arra as $k=>$s)
        						  {
        						      $existing_inshare_med[$s['ipid']][] = $s['ipid'].$s['drugplan_id'];
        						  }
        						  
        						  $source_medication = $medications->getPatientAllDrugs($marked_patient[0]['ipid']);
        						  if(!empty($source_medication)){
                                        foreach($source_medication as $k=>$dr){
                                            // check if data already exists in
                                            $ident_src = $marked_patient[0]['ipid'].$dr['id'];
                                            if( !in_array($ident_src,$existing_inshare_med[$marked_patient[0]['ipid']])){
                                                $insert_source_meds = new PatientDrugPlanShare();
                                                $insert_source_meds->ipid = $marked_patient[0]['ipid'];
                                                $insert_source_meds->drugplan_id = $dr['id'];
                                                $insert_source_meds->create_date = date("Y-m-d H:i:s",time());
                                                $insert_source_meds->save();
                                            }
                                        }        						      
        						  }
        						  
        						  $target_medication = $medications->getPatientAllDrugs($target_ipid_med);
        						  if(!empty($target_medication)){
                                        foreach($target_medication as $tk=>$tdr){
                                           // check if data already exists in
                                           $ident_tr = $target_ipid_med.$tdr['id'];
                                           if( !in_array($ident_tr,$existing_inshare_med[$target_ipid_med])){
                                                $insert_target_meds = new PatientDrugPlanShare();
                                                $insert_target_meds->ipid = $target_ipid_med;
                                                $insert_target_meds->drugplan_id = $tdr['id'];
                                                $insert_target_meds->create_date = date("Y-m-d H:i:s",time());
                                                $insert_target_meds->save();
                                            }
                                        }        						      
        						  }
    						  }
    						  
                         } else{
    						$this->_redirect(APP_BASE . 'client/processpatient?flg=err&sid=' . $_REQUEST['sid']);
                         }
					}
					if($save_shared_shortcuts)
					{
						$this->_redirect(APP_BASE . 'client/sharepatient?flg=suc&case=ssave&cid=' . $clientid);
					}
					else
					{
						$this->_redirect(APP_BASE . 'client/sharepatient?flg=err&case=ssave&cid=' . $clientid);
					}
				}
			}

			//get marked for share
			$copy_options_master = array('1' => 'Stammdaten', '2' => 'Diagnosen', '3' => 'Medikamente');//TODO-3838 CRISTI C. 08.02.2021


			$coptions = explode(',', $share_details[0]['copy_options']);
			foreach($coptions as $option)
			{
				$copied_data[] = $copy_options_master[$option];
			}
			$this->view->copied_options = $copied_data;
			$this->view->share_details = $share_details;

			//array with clients ids for gathering client data (source and target)
			$required_clients_ids = array($share_details[0]['source'], $share_details[0]['target']);

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $required_clients_ids);
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}

			//set clients details
			$this->view->clients_details = $clientsarray;

			//set curent client name
			$this->view->client_name = $clientsarray[$clientid]['client_name'];
			$this->view->source_client_name = $clientsarray[$share_details[0]['source']]['client_name'];


			$shortcuts = $marked_patients->allowed_target_shortcuts($share_details[0]['source'], $share_details[0]['target'], explode(',', $share_details['0']['shortcuts']));
            
			$this->view->source_shortcuts = $shortcuts['source_shortcuts'];
			$this->view->target_shortcuts = $shortcuts['target_shortcuts'];
			$this->view->allowed_shortcuts = $shortcuts['allowed_shortcuts'];

			//get patient data by ipid
			$sql = "p.id, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, p.id, e.epid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete = 0")
				->andWhere('p.ipid LIKE "' . $share_details[0]['ipid'] . '"')
				->leftJoin("p.EpidIpidMapping e")
			;

			$pat_details = $patient->fetchArray();
			$this->view->patient_details = $pat_details[0]['EpidIpidMapping']['epid'] . ', ' . $pat_details[0]['first_name'] . ' ' . $pat_details[0]['last_name'];
		}

		//edit share in patient_marked untill is accepted
		public function editshareAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$assoc_group = new AssociatedGroups();
			$assoc_clients = new GroupAssociatedClients();
			$marked_patients = new PatientsMarked();

			$marked = $marked_patients->share_get($_REQUEST['sid'], false);

			$this->view->marked = $marked;

			if(in_array($marked[0]['status'],['a','c'])) //ISPC ISPC-2591 Dragos 18.01.2021
			{
				$this->_redirect(APP_BASE . 'client/sharepatient?flg=err&case=edit&cid=' . $clientid);
			}


			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			else if($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			//get associated clients of current clientid
			$associated_clients = $assoc_clients->associated_clients_get($clientid, true);

			//set associated clients
			$this->view->associated_clients = $associated_clients;
			//array with clients ids for gathering client data
			if(is_numeric($clientid))
			{
				$asociated_clients_ids[] = $clientid; //always we need to gather curent client data
			}
			else
			{
				$asociated_clients_ids[] = '999999999';
			}

			foreach($associated_clients as $k_aclient => $v_aclient)
			{
				foreach($v_aclient as $asociated_client_id => $asociated_client_value)
				{
					$asociated_clients_ids[] = $asociated_client_id;
				}
			}

			if($this->getRequest()->isPost())
			{
				$update_marked = $marked_patients->share_update($_REQUEST['sid'], $_POST);
				if($update_marked)
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=suc&case=edit&cid=' . $clientid);
				}
				else
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=err&case=edit&cid=' . $clientid);
				}
			}

			$asociated_clients_ids = array_unique($asociated_clients_ids);
			asort($asociated_clients_ids);
			$asociated_clients_ids = array_values($asociated_clients_ids);

			$sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			//get clients data
			$clients = Doctrine_Query::create()
				->select($sql_c)
				->from('Client')
				->where('isdelete = 0')
				->andWhereIn('id', $asociated_clients_ids);
			$clients_arrays = $clients->fetchArray();

			foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
			{
				$clientsarray[$v_clients_arr['id']] = $v_clients_arr;
			}

			//set clients details
			$this->view->clients_details = $clientsarray;

			//set curent client name
			$this->view->client_name = $clientsarray[$clientid]['client_name'];


			//get client verlauf shortcuts
// 			$allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');

			$cs = new Courseshortcuts();
			$ltrarray = $cs->getFilterCourseData();

			foreach($ltrarray as $k_letter => $v_letter)
			{
// 				if(in_array($v_letter['shortcut'], $allowed_shortcuts))
// 				{
					$final_letters[$v_letter['shortcut']] = $v_letter;
// 				}
			}

			ksort($final_letters);
			$this->view->sharing_shortcuts = $final_letters;

			$all_shared_patients = array_merge(array($marked[0]['ipid']), array($received_patients_ids));

			//get patients data
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $all_shared_patients)
				->leftJoin("p.EpidIpidMapping e");
			$pat_details = $patient->fetchArray();

			$this->view->patient_details = $pat_details;
		}

		//edit patient shortcuts before share is accepted
		public function editsharedshortcutsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$linked_patients = new PatientsLinked();
			$client = new Client();
			$course_shortcuts = new Courseshortcuts();
			$patient_share = new PatientsShare();

			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			else if($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			//execute post data
			if($this->getRequest()->isPost())
			{
				//			1. get link data
				$p_link_data = $linked_patients->get_link_data($_REQUEST['lid']);

				//			2. get source ipid by decrypting patient id and then get ipid
				$source = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_REQUEST['patient_id']));

				//			3.find what is the target
				if($p_link_data[0]['source'] == $source)
				{
					$target = $p_link_data[0]['target'];
				}
				else
				{
					$target = $p_link_data[0]['source'];
				}

				//			4. delete all shortcuts from requested link id and above ipid as source
				$del_shortcuts = $patient_share->delete_shortcuts($_REQUEST['lid'], $source);

				
				// edit copy files data
				if(isset($_POST['disable_files_share'])){ 				
    				
    				$mod = Doctrine::getTable('PatientsLinked')->find($_REQUEST['lid']);
    				$mod->copy_files = $_POST['disable_files_share'];
    				$mod->save();
    				
				}
				 
				if(isset($_POST['disable_medis_share'])){ 				
    				
    				$mod = Doctrine::getTable('PatientsLinked')->find($_REQUEST['lid']);
    				$mod->copy_meds = $_POST['disable_medis_share'];
    				$mod->save();
    				
				} 
				
				
				//			5. Insert new submited shortcuts if there are any
				if(!empty($_REQUEST['shortcut']))
				{
					$ins_shortcuts = $patient_share->insert_new_shortcuts($source, $target, $_REQUEST['lid'], $_REQUEST['shortcut']);

					if($ins_shortcuts)
					{
						$this->_redirect(APP_BASE . 'client/sharepatient?flg=suc&op=sedit');
					}
				}
				else
				{
					$this->_redirect(APP_BASE . 'client/sharepatient?flg=suc&op=sedit');
				}
			}


			//get client data
			$client_dets = $client->getClientDataByid($clientid);

			$this->view->client_name = $client_dets['0']['client_name'];
			$this->view->clientid = $clientid;

			//get link data
			$link_data = $linked_patients->get_link_data($_REQUEST['lid']);

			$link_ipids[] = '9999999999';
			foreach($link_data as $link)
			{
				$link_ipids[] = $link['source'];
				$patient_source =  $link['source'];
				$link_ipids[] = $link['target'];
				$patient_target =  $link['target'];
				$copy_files = $link['copy_files'];
				$copy_meds = $link['copy_meds'];
			}

			$this->view->copy_files = $copy_files;
			$this->view->copy_meds = $copy_meds;
			//get all client shortcuts
			$shortcuts = $course_shortcuts->getClientShortcuts($clientid);
			foreach($shortcuts as $shortcut)
			{
				//			$client_shortcuts[$shortcut['shortcut_id']] = $shortcut;
				$client_shortcuts_letter[$shortcut['shortcut']] = $shortcut;

				$client_shortcuts_ids[] = $shortcut['shortcut_id'];
			}
			ksort($client_shortcuts_letter);

			//		filter client shortcuts
			
			
			// DE SCOS CELE PE CARE NU LE ARE CLIENTUL SURSA
			
			$allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');
			foreach($client_shortcuts_letter as $k_shortcut => $shortcut)
			{
// 				if(in_array($k_shortcut, $allowed_shortcuts))
// 				{
					$client_shortcuts_allowed[$k_shortcut] = $shortcut;
// 				}
			}
// print_r($client_shortcuts_allowed); exit;
			$this->view->client_shortcuts = $client_shortcuts_allowed;
			//get all link shortcuts
			$link_shortcuts = $linked_patients->get_link_shortcuts($_REQUEST['lid']);
			
			$this->view->link_shortcuts = $link_shortcuts[$patient_source];
// 			$this->view->link_shortcuts = "mamaliga";
// print_r($link_shortcuts); exit;
			foreach($link_shortcuts as $pipid => $link_shortcut_data)
			{
			    foreach($link_shortcut_data as $link_shortcut ){
			        
    				if(in_array($link_shortcut['shortcut'], $client_shortcuts_ids))
    				{
    					$client_link_shortcuts_ids[] = $link_sortcut['shortcut'];
    				}
			    }
			}

			$this->view->client_link_shortcuts_ids = $client_link_shortcuts_ids;

			//get curent patient details to be used as source when inserting
			$sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
			$sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
			$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
			$sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
			$sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
			$sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
			$sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
			$sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
			$sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
			$sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
			$sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
			$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.epid, e.clientid,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete = 0")
				->andWhereIn('p.ipid', $link_ipids)
				->leftJoin("p.EpidIpidMapping e");
			$pat_details = $patient->fetchArray();

			foreach($pat_details as $patient)
			{
				if($patient['EpidIpidMapping']['clientid'] == $clientid)
				{
					$shortcuts_source_patient = $patient['id'];
					$patients_link_data['source'] = $patient['ipid'];
				}
				else
				{
					$patients_link_data['target'] = $patient['ipid'];
				}

				$patients_details[$patient['ipid']] = $patient;
			}

			$this->view->patients_details = $patients_details;
			$this->view->shortcut_source_patient = Pms_Uuid::encrypt($shortcuts_source_patient);
			$this->view->direction = $patients_link_data; //source and target ipids(aka share direction)
		}

		public function orderadmissionoldAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$Kontaktaufnahmecprevileges = new Modules();
			$Kontaktaufnahme = $Kontaktaufnahmecprevileges->checkModulePrivileges("62", $logininfo->clientid);

			if($Kontaktaufnahme)
			{
				$this->view->Kontaktaufnahme_visibility = 1;
			}
			else
			{
				$this->view->Kontaktaufnahme_visibility = 0;
			}

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				if(!empty($_POST['orderer']))
				{
					$user_form = new Application_Form_OrderAdmission();
					$user_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("Success! Kontaktaufnahme was added");
				}
				else
				{
					$this->view->error_message = $this->view->translate("Kontaktaufnahmerequierd");
				}
			}

			$ordererquery = Doctrine_Query::create()
				->select('*')
				->from('OrderAdmission')
				->where('clientid=' . $clientid . ' and isdelete=0');
			$orderearray = $ordererquery->fetchArray();
			$this->view->orderearray = $orderearray;
		}

		public function editorderadmissionAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$orderer_id = $_REQUEST['ordererid'];
			$this->view->orderer_id = $orderer_id;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				if(!empty($_POST['orderer']))
				{
					$user_form = new Application_Form_OrderAdmission();
					$user_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . "client/orderadmission?flg=succ&mes=".urlencode($this->view->error_message));
				}
				else
				{
					$this->view->error_message = $this->view->translate("Kontaktaufnahmerequierd");
				}
			}

			if(!empty($_REQUEST['ordererid']))
			{
				$ordererquery = Doctrine_Query::create()
					->select('*')
					->from('OrderAdmission')
					->where('clientid= ?', $clientid)
					->andWhere('isdelete= ? ', "0")
					->andWhere('id= ?' ,  $orderer_id);
				$orderearray = $ordererquery->fetchArray();

				$this->view->orderer = $orderearray[0]['orderer'];
				$this->view->ordererid = $orderearray[0]['id'];
			}
		}
		
		public function addorderadmissionAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer('editorderadmission');
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				if(!empty($_POST['orderer']))
				{
					$user_form = new Application_Form_OrderAdmission();
					$user_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$user_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function deleteordererAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			//$this->_helper->viewRenderer('ordereradmision');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			//delete
			$del_stat = Doctrine_Query::create()
				->update('OrderAdmission')
				->set('isdelete', '1')
				->where('id = ?' , $_REQUEST['ordererid'])
				->andWhere('clientid = ?', $clientid);
			$rows = $del_stat->execute();

			//$this->_redirect(APP_BASE . 'client/orderadmission');
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . "client/orderadmission?flg=succ&mes=".urlencode($this->view->error_message));
		}

		public function letterstextblocksAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$boxes = new LettersTextBoxes();
			$letter_boxes_details = $boxes->client_letter_boxes($clientid);

			$this->view->letter_boxes_details = $letter_boxes_details[0];
		}

		public function letterstextblockeditAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$boxes = new LettersTextBoxes();
			$letter_boxes_details = $boxes->client_letter_boxes($clientid);

			$this->view->letter_boxes_details = $letter_boxes_details[0];


			if($this->getRequest()->isPost())
			{
				$user_form = new Application_Form_LettersTextBoxes();
				if(!empty($letter_boxes_details))
				{
					$_POST['item_id'] = $letter_boxes_details[0]['id'];
					$user_form->UpdateData($_POST, $item_id);
				}
				else
				{
					$user_form->InsertData($_POST);
				}
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "client/letterstextblocks?flg=suc");
			}
		}

		public function clientsettingsAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$treatment_days_options = Pms_CommonData::treatment_days_options();
			$client_settings = new ClientHospitalSettings();
			$client_settings_form = new Application_Form_ClientHospitalSettings();


			//process post data
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$post = $_POST;
				$save_settings = $client_settings_form->insert_data($clientid, $post);

				if($save_settings)
				{
					$this->_redirect(APP_BASE . 'client/clientsettings?flg=suc');
					exit;
				}
			}
			else
			{
				$this->retainValues($post);
			}

			//load data
			$hospital_settings = $client_settings->getClientSetting($clientid);
			if(empty($_POST))
			{
				$this->retainValues($hospital_settings);
			}

			//send data to the view
			$this->view->client_id = $clientid;
			$this->view->treatment_days_options = $treatment_days_options;
		}

		public function setclientiduseridAction()
		{
			
			$base_parts = parse_url(APP_BASE);
				
			$redirect_url = $base_parts['scheme'].'://'.$base_parts['host'].(($base_parts['port'] != '80' && $base_parts['port'] != '443') ? ':'.$base_parts['port'].'/' : '/').$_GET['url'];
			
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//ISPC-2615 Carmen 15.07.2020
			if($logininfo->usertype != 'SA')
			{
			//--
				$users_settings = User::get_connected_user_settings($logininfo->userid);
	
				if($users_settings[$logininfo->userid]['status'] == "slave")
				{
					$parent_id = $users_settings[$logininfo->userid]['parent'];
					$required_user = User::get_duplicated_users_on_client($parent_id, $_GET['cid']);
				}
				else
				{
					$required_user = User::get_duplicated_users_on_client($logininfo->userid, $_GET['cid']);
				}
			//ISPC-2615 Carmen 15.07.2020
			}
			else 
			{
				$required_user = $logininfo->userid;
			}
			//--
			
			if(empty($required_user)){
			    $this->_redirect($redirect_url);
			    exit;
			}
			

			$new_user_det = User::getUsersDetails($required_user);
			$this->_helper->viewRenderer->setNoRender();

			if(($logininfo->multiple_clients == '1' && !empty($_GET['cid'])) || ($logininfo->usertype == 'SA' && !empty($_GET['cid']))) //ISPC-2615 Carmen 15.07.2020
			{
				$_SESSION['Login_Info']['userid'] = $required_user;
				$_SESSION['Login_Info']['groupid'] = $new_user_det[$required_user]['groupid'];
				$logininfo->usertype = $new_user_det[$required_user]['usertype'];
				$logininfo->isadmin = $new_user_det[$required_user]['isadmin'];
				
				//ISPC-2827 Ancuta 26.03.2021
				$logininfo->isEfaUser = $new_user_det[$required_user]['efa_user'];
                //
				
				$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax,AES_DECRYPT(fileupoadpass,'" . Zend_Registry::get('salt') . "') as fileupoadpass")
					->from('Client')
					->where('id= ?', $_GET['cid']);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$logininfo->clientid = $_GET['cid'];
				$logininfo->filepass = $clientarray[0]['fileupoadpass'];
				//ISPC-2827 Ancuta 26.03.2021
				$logininfo->isEfaClient = $clientarray[0]['efa_client'];
				//--
				
				unset($_SESSION['filename']);
			}
			
			$logininfo->_clientModules = null;
			
			Zend_Session::namespaceUnset('Navigation_Menus');
			
			$this->_redirect($redirect_url);
			
		}

		public function clienttagsoldAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$tag_forms = new Application_Form_PatientFile2tags();

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$post = $_POST;

				if($post['tag_edit'] == '1' && $post['tag_id'] != '0' && strlen($post['tag_name']) > '0')
				{
					$tag_forms->edit_tag($post);
				}
				$this->_redirect(APP_BASE . 'client/clienttags?flg=suc');
				exit;
			}
			else if($_REQUEST['mode'] == 'deltag')
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$tag_forms->delete_tag($_REQUEST['tagid']);
			}
		}

		public function fetchclienttagsAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$columnarray = array(
				"tag" => "tag",
			);

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$where = '';
			if($clientid > 0)
			{
				$where = 'client=' . $logininfo->clientid;
			}

			$fdoc = Doctrine_Query::create()
				->select('count(*)')
				->from('PatientFileTags');
			if(strlen($where) > '0')
			{
				$fdoc->where($where);
			}
			$fdoc->andWhere('isdelete = "0"');
			$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

			//used in pagination of search results
			if(!empty($_REQUEST['val']))
			{
				$fdoc->andWhere("(tag != '')");
// 				$fdoc->andWhere("(tag like '%" . trim($_REQUEST['val']) . "%')");
				$fdoc->andWhere("tag like ?","%" . trim($_REQUEST['val']) . "%");
			}
			$fdocarray = $fdoc->fetchArray();

			$limit = 50;
			$fdoc->select('*, 
				(SELECT count(*) FROM PatientFile2tags pf2t where pft.id = pf2t.tag) as counted_files, 
				(SELECT GROUP_CONCAT(file) FROM PatientFile2tags pf2t2 WHERE pft.id = pf2t2.tag) as files');
			$fdoc->from('PatientFileTags pft');

			if(strlen($where) > '0')
			{
				$fdoc->where($where);
			}
			$fdoc->andWhere("(pft.isdelete = '0')");
			$fdoc->andWhere("(pft.tag != '')");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
// 				$fdoc->andWhere("(pft.tag like '%" . trim($_REQUEST['val']) . "%')");
				$fdoc->andWhere("pft.tag like ?","%" . trim($_REQUEST['val']) . "%");
			}
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());

			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{

				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "tagslist.html");
				$this->view->tags_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("tagsnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->tags_grid = '<tr><td colspan="5" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['tagslist'] = $this->view->render('client/fetchclienttags.html');

			echo json_encode($response);
			exit;
		}

		public function contactforms2formsAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$mapped_forms = Pms_CommonData::mapped_forms();
			$this->view->mapped_forms = $mapped_forms;
		}

		public function assigncf2formitemsAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$mapped_forms_declared = Pms_CommonData::mapped_forms();

			if($_REQUEST['mfid'])
			{
				$mapped_form = array($mapped_forms_declared[$_REQUEST['mfid']]);
			}
			else
			{
				$mapped_form = false;
				$this->_redirect(APP_BASE . 'client/contactforms2forms?icf=1');
				exit;
			}

			if($mapped_form)
			{

				$formtypes = new FormTypes();
				$formsitems = new FormsItems();

				//get client contact form types
				$form_types = $formtypes->get_form_types($clientid);
				$forms_items = $formsitems->get_all_form_items($clientid, $mapped_form, 'v');

//				print_r("mapped_form\n");
//				print_r($mapped_form);
//
//				print_r("form_types\n");
//				print_r($form_types);
//
//				print_r("form_items\n");
//				print_r($forms_items);

				$this->view->mapped_forms = $mapped_forms_declared;
				$this->view->form_types = $form_types;
				$this->view->forms_items = $forms_items[$mapped_form[0]];

				$cf2forms = new Application_Form_Contactforms2items();

				if($this->getRequest()->isPost())
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					} 
					
					$post = $_POST;
					$post['clientid'] = $clientid;

					if($_POST['save'])
					{
						$save_data = $cf2forms->insert_data($post);
						$this->_redirect(APP_BASE . 'client/assigncf2formitems?mfid=' . $_REQUEST['mfid'] . '&flg=suc');
						exit;
					}
				}

				//load data
				$forms2items_data = Forms2Items::get_form_items($clientid);
				$this->view->forms2item_data = $forms2items_data;
			}
		}

		public function clientinvoicepermissionsAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//estabilish the clientid
			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			elseif($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			//get client details
			$sql_client = "*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
				AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
				AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
				AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
				AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
				AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
				AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
				AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
				AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			$client = Doctrine_Query::create()
				->select($sql_client)
				->from("Client")
				->where("id = ?",$clientid);
			$client_res = $client->fetchArray();

			$this->view->client_name = $client_res[0]['client_name'];

			//get all invoices tabnames
			$invoice_tabnames = Pms_CommonData::allinvoices(true);
			$this->view->invoice_tabnames = $invoice_tabnames;

			//get all client permissions
			$invoice_permissions = ClientInvoicePermissions::get_client_invoice_perms($clientid);
			$this->view->invoice_permissions = $invoice_permissions;
			
			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_ClientInvoicePermissions();

				$post = $_POST;
				$post['clientid'] = $clientid;

				$client_form->insert_invoice_permissions($post);
				$this->_redirect(APP_BASE . "client/clientinvoicepermissions?cid=" . $_REQUEST['cid'] . "&flg=suc");
			}
		}
		
		/**
		 * ISPC-2312 Ancuta 08.12.2020
		 */
		public function clientinvoicemultiplepermissionsAction()
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//estabilish the clientid
			if($_REQUEST['cid'] > 0)
			{
				$clientid = $_REQUEST['cid'];
			}
			elseif($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->client_name = "Select Client";
				$clientid = '0';
			}

			//get client details
			$sql_client = "*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
				AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
				AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
				AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
				AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
				AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
				AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
				AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
				AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";

			$client = Doctrine_Query::create()
				->select($sql_client)
				->from("Client")
				->where("id = ?",$clientid);
			$client_res = $client->fetchArray();

			$this->view->client_name = $client_res[0]['client_name'];

			//get all invoices tabnames
			$invoice_tabnames = Pms_CommonData::allinvoicesmultiple(true);
			$this->view->invoice_tabnames = $invoice_tabnames;

			//get all client permissions
			$invoice_permissions = ClientInvoiceMultiplePermissions::get_client_invoice_perms($clientid);
			$this->view->invoice_permissions = $invoice_permissions;
			
			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_ClientInvoiceMultiplePermissions();

				$post = $_POST;
				$post['clientid'] = $clientid;

				$client_form->insert_invoice_permissions($post);
				$this->_redirect(APP_BASE . "client/clientinvoicemultiplepermissions?cid=" . $_REQUEST['cid'] . "&flg=suc");
			}
		}


		public function assignvwclientsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $vw_assoc_group = new VwAssociatedGroups();
		    $vw_assoc_group_form = new Application_Form_VwAssociatedGroups();
		    $vw_assoc_clients = new VwGroupAssociatedClients();
		    $vw_assoc_clients_form = new Application_Form_VwGroupAssociatedClients();
		
		    if($_REQUEST['cid'] > 0)
		    {
		        $clientid = $_REQUEST['cid'];
		    }
		    else if($logininfo->clientid > 0)
		    {
		        $clientid = $logininfo->clientid;
		    }
		    else
		    {
		        $this->view->client_name = "Select Client";
		        $clientid = '0';
		    }
		
		    if($this->getRequest()->isPost())
		    {
// 		print_r($_POST); exit;
		        if(!empty($_POST['assigned_client'])) //adaugare daca nu are id grup
		        {
		
		            if(empty($_POST['edit_group'])) //new group
		            {
		                $new_group = $vw_assoc_group_form->associated_groups_create("Ehrenamtliche - Mandanten"); //newly created groupid
		            }
		            else //edit group
		            {
		                $new_group = $_POST['edit_group'];
		            }
		
		            $add_clients = $vw_assoc_clients_form->clients_group_add($_POST['assigned_client'], $new_group);
		
		            if($add_clients)
		            {
		                $this->_redirect(APP_BASE . 'client/assignvwclients?flg=suc');
		            }
		            else
		            {
		                $this->_redirect(APP_BASE . 'client/assignvwclients?flg=err');
		            }
		        }
		    }
		
		
		    if($_REQUEST['act'] == 'del' && !empty($_REQUEST['gid']))
		    {
		        $delete_group = $vw_assoc_group_form->associated_groups_mark_deleted($_REQUEST['gid']);
		        $delete_clients_group = $vw_assoc_clients_form->clients_group_set_delete($_REQUEST['gid']);
		        $this->_redirect(APP_BASE . 'client/assignvwclients?flg=suc');
		    }
		    else if($_REQUEST['act'] == 'edit' && !empty($_REQUEST['gid']))
		    {
		        $group_clients = $vw_assoc_clients->clients_groups_get($_REQUEST['gid']);
		        $this->view->group_clients = $group_clients;

		        $parent_group_clients = $vw_assoc_clients->parent_client_groups_get($_REQUEST['gid']);
		        $this->view->parent_group_clients = $parent_group_clients;
		    }
		
		    $agroups = $vw_assoc_group->associated_groups_get();
		    $this->view->agroups = $agroups;
		
		    $groups_ids[] = '999999999999';
		    foreach($agroups as $group)
		    {
		        $groups_ids[] = $group['id'];
		    }
		
		    $saved_parent_group_clients = $vw_assoc_clients->parent_client_groups_get($groups_ids);
		    $this->view->saved_parent_group_clients = $saved_parent_group_clients;
		    
		    $saved_groups_clients = $vw_assoc_clients->clients_groups_get($groups_ids);
		    $this->view->saved_groups_clients = $saved_groups_clients;
		    
		    // Remove associated clients from list
		    if(empty($_REQUEST['gid'])){
		        // get all clients involved in a connection
                foreach($saved_groups_clients as $grid=>$cl_data){
                    foreach($cl_data as $kl =>$clid ){
        		        $used_clients[] =  $clid;
                    }
                }
		    } else{
                foreach($saved_groups_clients as $grid=>$cl_data){
                    foreach($cl_data as $kl =>$clid ){
                        if(!in_array($clid,$group_clients[$_REQUEST['gid']])){
        		        $used_clients[] =  $clid;
                        }
                    }
                }
		    }
		    
		    $this->view->used_clients = $used_clients;
		    
		    
		    $sql = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
		        AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name_ord,
		AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
		AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
		AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
		AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
		AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
		AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
		AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
		AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
		AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
		    //get rest clients
		    $clients = Doctrine_Query::create()
		    ->select($sql)
		    ->from('Client')
		    ->where('isdelete = 0')
		    ->orderBy("client_name_ord ASC");
		    $clients_arrays = $clients->fetchArray();
		
		    foreach($clients_arrays as $k_client => $v_client)
		    {
		        $clientsarray[$v_client['id']] = $v_client;
		        $clientsarray_details[$v_client['id']] = $v_client;
		        
		    }

		    $this->view->clientsarray_details = $clientsarray_details;
		    $this->view->clientsarray = $clientsarray;
    		$this->view->not_available_clientsarray = $not_available_clientsarray;
 
		}
		

		public function contactformsettingsAction()
		{
		    //init
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $Tr = new Zend_View_Helper_Translate();
		    
		    $tr_options[''] = $Tr->translate('select_date_type');
		    $tr_options['start_date'] = $Tr->translate('start_date');
		    $tr_options['end_date'] = $Tr->translate('end_date');
		    $tr_options['greater_duration'] = $Tr->translate('greater_duration');
		    
		    $date_options = $tr_options;
		    
		    
		    $client_settings = new ClientContactFormSettings();
		    $client_settings_form = new Application_Form_ClientContactFormSettings();
		
		
		    //process post data
		    if($this->getRequest()->isPost())
		    {
		        $has_edit_permissions = Links::checkLinkActionsPermission();
		        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		        {
		            $this->_redirect(APP_BASE . "error/previlege");
		            exit;
		        }
		
		        $post = $_POST;
		        $save_settings = $client_settings_form->insert_data($clientid, $post);
		
		        if($save_settings)
		        {
		            $this->_redirect(APP_BASE . 'client/contactformsettings?flg=suc');
		            exit;
		        }
		    }
		    else
		    {
		        $this->retainValues($post);
		    }
		
		    //load data
		    $hospital_settings = $client_settings->getClientContactFormSetting($clientid);
		    if(empty($_POST))
		    {
		        $this->retainValues($hospital_settings);
		    }
		
		    //send data to the view
		    $this->view->client_id = $clientid;
		    $this->view->date_options = $date_options;
		}
		
		
		public function smtpsettingsAction()
		{
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			if($logininfo->clientid > 0){
				$clientid = $logininfo->clientid;
			}
			else
			{
				$this->view->error_message = $this->view->translate("selectclient") . "<br>";
				$clientid = 0;
			}
			
			//save the form
			if($this->getRequest()->isPost() && $clientid > 0 )
			{

				$client_form = new Application_Form_Client();
				//update smpt settings
				$smtp_update = $client_form->UpdateSMTPData($clientid,	$_POST['smtp_settings']);
				
				//append smpt validation error
				$smtp_flag = "";
				if ($smtp_update !== true) {
					$smtp_flag = "smtp_flag=".urlencode($this->view->translate($smtp_update));
					$this->view->error_message .= $this->view->translate($smtp_update);
				}
				
				$this->redirect(APP_BASE . 'client/smtpsettings?flg=suc&'. $smtp_flag);
				
			} elseif( $clientid > 0 ) {
				
				if($_GET['flg'] == 'suc')
				{
					$this->view->error_message .= $this->view->translate("recordupdatedsuccessfully");
				}
				if( ! empty($_GET['smtp_flag']))
				{
					$this->view->error_message .= "<br>SMTP: " . $this->view->translate(urldecode($_GET['smtp_flag']));
				}
				
				$client_details = Client::getClientDataByid($clientid);
				$this->view->client_details = $client_details[0];
				
				//get smtp settings ispc 1957
				$c_smpt_s = new ClientSMTPSettings();
				$smtp_settings = $c_smpt_s->get_smtp_settings($clientid);
				$this->view->smtp_settings = $smtp_settings;
			}
		}
		
		public function invoicestextsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			$boxes = new LettersTextBoxes();
			$letter_boxes_details = $boxes->client_letter_boxes($clientid);
		
			if(empty($letter_boxes_details)){
				$letter_boxes_details[0]['nd_invoice_footer'] ='
					Wir bitten um Erstattung der angegebenen Summe innerhalb von <b>3 Wochen</b> auf unten stehende Kontoverbindung. Für Rückfragen stehen wir gerne zur Verfügung.
					<br />
					<br />
					Mit freundlichen Grüßen';
			}
			$this->view->letter_boxes_details = $letter_boxes_details[0];
		
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
			
			if($this->getRequest()->isPost())
			{
				$user_form = new Application_Form_LettersTextBoxes();
				if(!empty($letter_boxes_details) && !empty($letter_boxes_details[0]['id']))
				{
					$_POST['item_id'] = $letter_boxes_details[0]['id'];
					$user_form->UpdateData($_POST, $item_id);
				}
				else
				{
					$user_form->InsertData($_POST);
				}
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "client/invoicestexts?flg=suc");
			}
		}
		
		//get view list orderer
		public function orderadmissionAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
			
			$Kontaktaufnahmecprevileges = new Modules();
			$Kontaktaufnahme = $Kontaktaufnahmecprevileges->checkModulePrivileges("62", $logininfo->clientid);
			
			if($Kontaktaufnahme)
			{
				$this->view->Kontaktaufnahme_visibility = 1;
				//populate the datatables
				if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
					$this->_helper->layout()->disableLayout();
					$this->_helper->viewRenderer->setNoRender(true);
					if(!$_REQUEST['length']){
						$_REQUEST['length'] = "25";
					}
					$limit = (int)$_REQUEST['length'];
					$offset = (int)$_REQUEST['start'];
					$search_value = addslashes($_REQUEST['search']['value']);
				
					$columns_array = array(
							"0" => "orderer"
					);
					$columns_search_array = $columns_array;
					
					if(isset($_REQUEST['order'][0]['column']))
					{
						$order_column = $_REQUEST['order'][0]['column'];
						$order_dir = $_REQUEST['order'][0]['dir'];
					}
					else
					{
						array_push($columns_array, "id");
						$nrcol = array_search ('id', $columns_array);
						$order_column = $nrcol;
						$order_dir = "ASC";
					}
					
					$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
					// ########################################
					// #####  Query for count ###############					
					$fdoc1 = Doctrine_Query::create();
					$fdoc1->select('count(*)');
					$fdoc1->from('OrderAdmission');
					$fdoc1->where("clientid = ?", $clientid);
					$fdoc1->andWhere("isdelete = 0 ");
				
					$fdocarray = $fdoc1->fetchArray();
					$full_count  = $fdocarray[0]['count'];
						
					/* ------------- Search options ------------------------- */
					if (isset($search_value) && strlen(trim($search_value)) > 0)
					{
						$comma = '';
						$filter_string_all = '';
						
						foreach($columns_search_array as $ks=>$vs)
						{
							$filter_string_all .= $comma.$vs;
							$comma = ',';
						}			
					
						$regexp = trim($search_value);
						Pms_CommonData::value_patternation($regexp);
						
						$searchstring = mb_strtolower(trim($search_value), 'UTF-8');
						$searchstring_input = trim($search_value);
						if(strpos($searchstring, 'ae') !== false || strpos($searchstring, 'oe') !== false || strpos($searchstring, 'ue') !== false)
						{
							if(strpos($searchstring, 'ss') !== false)
							{
								$ss_flag = 1;
							}
							else
							{
								$ss_flag = 0;
							}
							$regexp = Pms_CommonData::complete_patternation($searchstring_input, $regexp, $ss_flag);
						}
						
						$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \','.$filter_string_all.' ) USING utf8 ) REGEXP ?';
						$regexp_arr[] = $regexp;
						
						//var_dump($regexp_arr);
						$fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
						//$search_value = strtolower($search_value);
						//$fdoc1->andWhere("(lower(orderer) like ?)", array("%" . trim($search_value) . "%"));
					}
						
					$fdocarray = $fdoc1->fetchArray();
					$filter_count  = $fdocarray[0]['count'];
						
					// ########################################
					// #####  Query for details ###############
					$fdoc1->select('*');
					
					$fdoc1->orderBy($order_by_str);
					$fdoc1->limit($limit);
					$fdoc1->offset($offset);
						
					$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
						
					$report_ids = array();
					$fdoclimit_arr = array();
					foreach ($fdoclimit as $key => $report)
					{
						$fdoclimit_arr[$report['id']] = $report;
						$report_ids[] = $report['id'];
					}
						
					$row_id = 0;
					$link = "";
						
					$resulted_data = array();
					foreach($fdoclimit_arr as $report_id =>$mdata)
					{
						$link = '%s';
						$resulted_data[$row_id]['orderer'] = sprintf($link,$mdata['orderer']);
				
						$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'client/editorderadmission?ordererid='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
						$row_id++;
					}
				
					$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
					$response['recordsTotal'] = $full_count;
					$response['recordsFiltered'] = $filter_count; // ??
					$response['data'] = $resulted_data;
				
					$this->_helper->json->sendJson($response);
				}
			}
			else
			{
				$this->view->Kontaktaufnahme_visibility = 0;
				$this->view->error_message = $this->view->translate('This option is not active - please contact the adimistrator');
			}
		
		}
		
		//get view list client tags
		public function clienttagsAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$tag_forms = new Application_Form_PatientFile2tags();
			
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
				
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "25";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
				
				$columns_array = array(
						"0" => "tag",
						"1" => "counted_files"
				);
				$columns_search_array = $columns_array;
				
				if(isset($_REQUEST['order'][0]['column']))
				{
					$order_column = $_REQUEST['order'][0]['column'];
					$order_dir = $_REQUEST['order'][0]['dir'];
				}
				else
				{
					array_push($columns_array, "id");
					$nrcol = array_search ('id', $columns_array);
					$order_column = $nrcol;
					$order_dir = "ASC";
				}
				
				if($order_column == '1')
				{
					$order_by_str = $columns_array[$order_column] . " " .$order_dir;
				}
				else 
				{
					$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
				}
				
				// ########################################
				// #####  Query for count ###############
				$fdoc1 = Doctrine_Query::create();
				$fdoc1->select('count(*)');
				$fdoc1->from('PatientFileTags');
				$fdoc1->where("client = ?", $clientid);
				$fdoc1->andWhere("isdelete = 0 ");
	
				$fdocarray = $fdoc1->fetchArray();
				$full_count  = $fdocarray[0]['count'];
	
				// ########################################
				// #####  Query for details ###############
				
				$fdoc1->select('*,
				(SELECT count(*) FROM PatientFile2tags pf2t where pft.id = pf2t.tag) as counted_files,
				(SELECT GROUP_CONCAT(file) FROM PatientFile2tags pf2t2 WHERE pft.id = pf2t2.tag) as files');
				//$fdoc1->select('*');
				$fdoc1->from('PatientFileTags pft');
				$fdoc1->Where("pft.client = ".$clientid);
				$fdoc1->andWhere("pft.isdelete = 0  ");
	
				$fdoc1->orderBy($order_by_str);
				
				$fdoclimit = $fdoc1->fetchArray();
				
				if(trim($search_value) != "")
				{
					$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp);
					
					foreach($columns_search_array as $ks=>$vs)
					{
						$pairs[$vs] = trim(str_replace('\\', '',$regexp));
						
					}
					//var_dump($pairs);
					$fdocsearch = array();
					foreach ($fdoclimit as $skey => $sval) {
						foreach ($pairs as $pkey => $pval) {							
							$pval_arr = explode('|', $pval);
						
							foreach($pval_arr as $kpval=>$vpval)
							{
								if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) { 
									$fdocsearch[$skey] = $sval;
									break;
								}
							}
							
						}
					
						}
					
					 
					$fdoclimit = $fdocsearch;
				}
				$filter_count  = count($fdoclimit);
				
				if($limit != "")
				{
					$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
				}
				$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	
				$report_ids = array();
				$fdoclimit_arr = array();
				foreach ($fdoclimit as $key => $report)
				{
					$fdoclimit_arr[$report['id']] = $report;
					$report_ids[] = $report['id'];
				}
	
				$row_id = 0;
				$link = "";
	
				$resulted_data = array();
				foreach($fdoclimit_arr as $report_id =>$mdata)
				{
					$link = '%s';
					$resulted_data[$row_id]['tag'] = sprintf($link,$mdata['tag']);
					$resulted_data[$row_id]['counted_files'] = sprintf($link,$mdata['counted_files']);
					
					$resulted_data[$row_id]['actions'] = '<a href="javascript:void(0);" rel="'.$mdata['id'] . '" relvalue="'. $mdata['tag'] . '" class="edit"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
	
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
	
				$this->_helper->json->sendJson($response);
			}
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			
				$post = $_POST;
			
				if($post['tag_edit'] == '1' && $post['tag_id'] != '0' && strlen($post['tag_name']) > '0')
				{
					$tag_forms->edit_tag($post);
				}
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'client/clienttags?flg=suc&mes='.urlencode($this->view->error_message));
			}
			
			if($_REQUEST['mode'] == 'deltag')
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			
				$tag_forms->delete_tag($_REQUEST['tagid']);
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'client/clienttags?mes='.urlencode($this->view->error_message));
			}
		
		}
		
		
		
	/**
	 * fn save is done here, TODO add a Application_FORM for it
	 */	
	public function associateclientfilesAction()
	{
	    $clientid =  $this->logininfo->clientid;
    
	    $associated_clients = new AssociatedClientFiles();
	    	
	    if ($this->getRequest()->isPost()) {
	
	        if ($assigned_clients = $this->getRequest()->getParam('assigned_client')) {
	
	            if(($group_id = $this->getRequest()->getParam('edit_group')) != null) {

	                //edit group
	                $group_id = (int)$group_id;
	                 
	                //delete all the old values
	                Doctrine_Query::create()
	                ->delete('AssociatedClientFiles')
	                ->where('group_id = ? ',  $group_id)
	                ->execute();
	            } else {
	                
	                //new group
	                $new_group = $associated_clients->findOrCreateOneBy('group_id', null, array());
	                 
	                $group_id = $new_group->id;
	                
	                $new_group->delete();
	                
	            }

	            $ismaster = $this->getRequest()->getParam('ismaster');
	            
	            $data = array();
	            foreach ($assigned_clients as $clientid) {
	                $data[] =  array(
	                    'group_id' => $group_id,
	                    'clientid' => $clientid,
	                    'ismaster' => $ismaster == $clientid ? 'yes' : 'no',
	                );
	            }
	            
	            //insert all
	            $collection = new Doctrine_Collection('AssociatedClientFiles');
	            $collection->fromArray($data);
	            $collection->save();
	            
	            //dd($group_id, $data , $collection->getPrimaryKeys());
	            	
	            $flag = $collection->getPrimaryKeys() ? 'suc' : 'err';
	            
	            $redirect_location = APP_BASE . 'client/associateclientfiles?flg=' . $flag;
	            
                $this->redirect($redirect_location , array(
                    "exit" => true
                ));
                
                exit; //for readability
	                
	        }
	        
	    } else {
	        //this is NOT a post
	        if($action = $this->getRequest()->getParam('act')) {
	        
	            switch ($action) {
	                case "edit":
	                    break;
	                    
	                case "del":
	                    
	                    $redirect_location = APP_BASE . 'client/associateclientfiles?flg=suc';
	                    
	                    $group_id =  $this->getRequest()->getParam('gid');
	                    $group_id =  (int)$group_id;
	                    
	                    //delete all the values from this group
	                    Doctrine_Query::create()
	                    ->delete('AssociatedClientFiles')
	                    ->where('group_id = ? ',  $group_id)
	                    ->execute();
	                    
	                    $this->redirect($redirect_location, array(
	                        "exit" => true
	                    ));
	                    
	                    exit; //for readability
	                    
	                    break;       
	            }   
	        }  

	        
    	    $this->view->groups = $associated_clients->fetchAssociatedClients();
    	
    	    $clientsarray = Client::get_all_clients();
    
    	    $this->view->clientsarray = $clientsarray;
    	
    	    //get curently client name
    	    $this->view->client_name = $clientsarray[$clientid]['client_name'];
	        
	    }
	
	}
	
	public function clientprintsettingsAction () {
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$saved_plans = PlansMediPrintSettingsTable::findAllClientPlansMediPrintSettings($this->logininfo->clientid);
		$saved_plans_ids = array_map(function($plan) {
			return $plan['plansmedi_id'];
		}, $saved_plans);
			$all_plans = [
					'medication' => $this->translate("pdf_medicationplan"),
					'medication_plan_patient' => $this->translate("pdf_patient_medicationplan"),
					'medication_plan_patient_active_substance' => $this->translate("pdf_patient_medicationplan") ." ". $this->translate("medication_drug"),
					'medication_plan_bedarfsmedication' => $this->translate("pdf_patient_bedarfsmedication"),
					'medication_plan_applikation' => $this->translate("pdf_patient_applikation")
			];
			$unset_plans = array();
		
			foreach($all_plans as $key=>$val)
			{
				if(!in_array($key, $saved_plans_ids))
				{
					$unset_plans[$key] = $val;
				}
			}
			if(empty($unset_plans))
			{
				$this->view->dontShow = true;
			}
			
			if($_REQUEST['action'] == 'delete')
			{
				if($_REQUEST['profile'] == 'plansmedi')
				{
					$mod = Doctrine::getTable('PlansMediPrintSettings')->find($_REQUEST['id']);
				}
				else 
				{
					$mod = Doctrine::getTable('ReceiptPrintSettings')->find($_REQUEST['id']);
				}
				$mod->delete();
			}
		
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			
			if($_REQUEST['settingstable'] == 'plans_medi_print_settings')
			{
				
				$this->getplansmediprintsettings();
			}
			else
			{
				$this->getreceiptprintsettings();
			}	
			
		}
	}
	
	private function getplansmediprintsettings()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if(!$_REQUEST['length'])
		{
			$_REQUEST['length'] = "10";
		}
		 
		$limit = $_REQUEST['length'];
		$offset = $_REQUEST['start'];
		$search_value = $_REQUEST['search']['value'];
		 
		$columns_array = array(
				"0" => "plan_medi",
				"1" => "plan_font_size"
		);
		$columns_search_array = $columns_array;
		 
		if(isset($_REQUEST['order'][0]['column']))
		{
			$order_column = $_REQUEST['order'][0]['column'];
			$order_dir = $_REQUEST['order'][0]['dir'];
		}
		else
		{
			array_push($columns_array, "id");
			$nrcol = array_search ('id', $columns_array);
			$order_column = $nrcol;
			$order_dir = "ASC";
		}

		//get plans medi
		$plansmedi = [
				'medication' => $this->translate("pdf_medicationplan"),
				'medication_plan_patient' => $this->translate("pdf_patient_medicationplan"),
				'medication_plan_patient_active_substance' => $this->translate("pdf_patient_medicationplan") ." ". $this->translate("medication_drug"),
				'medication_plan_bedarfsmedication' => $this->translate("pdf_patient_bedarfsmedication"),
				'medication_plan_applikation' => $this->translate("pdf_patient_applikation")
		];
		
		$fdoclimit = PlansMediPrintSettingsTable::findAllClientPlansMediPrintSettings($clientid);
	
		foreach($fdoclimit as $fkey=>&$fval)
		{
			$fval['plan_medi'] = $plansmedi[$fval['plansmedi_id']];
			$fval['plan_font_size'] = $fval['plansmedi_settings']['plan_font_size'];
		}
		//var_dump($fdoclimit);
		$full_count = count($fdoclimit);
		
		if(trim($search_value) != "")
		{
			$regexp = trim($search_value);
			Pms_CommonData::value_patternation($regexp);

			foreach($columns_search_array as $ks=>$vs)
			{
				$pairs[$vs] = trim(str_replace('\\', '',$regexp));

			}
			//var_dump($pairs);
			$fdocsearch = array();
			foreach ($fdoclimit as $skey => $sval) {
				foreach ($pairs as $pkey => $pval) {
					if($pkey == 'create_date')
					{
						$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
					}

					$pval_arr = explode('|', $pval);

					foreach($pval_arr as $kpval=>$vpval)
					{
						if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
							$fdocsearch[$skey] = $sval;
							break;
						}
					}

				}
			}


			$fdoclimit = $fdocsearch;
		}
		$filter_count  = count($fdoclimit);
		 
		if($order_column != '')
		{
			$sort_col = array();
			foreach ($fdoclimit as $key=> $row)
			{
				
					$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
					$fdoclimit[$key] = $row;
					$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
				
			}
			if($order_dir == 'desc')
			{
				$dir = SORT_DESC;
			}
			else
			{
				$dir = SORT_ASC;
			}
			array_multisort($sort_col, $dir, $fdoclimit);

			$keyw = $columns_array[$order_column].'_tr';
			array_walk($fdoclimit, function (&$v) use ($keyw) {
				unset($v[$keyw]);
			});
		}
		 
		if($limit != "")
		{
			$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
		}
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);

		$report_ids = array();
		$fdoclimit_arr = array();
		foreach ($fdoclimit as $key => $report)
		{
			$fdoclimit_arr[$report['id']] = $report;
			$report_ids[] = $report['id'];
		}

		$row_id = 0;
		$link = "";
		$resulted_data = array();
		
		foreach($fdoclimit_arr as $report_id =>$mdata)
		{
			$link = '%s ';
			 
			$resulted_data[$row_id]['plan_medi'] = sprintf($link, $mdata['plan_medi']);
			$resulted_data[$row_id]['plan_font_size'] = $mdata['plan_font_size'];		
			$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'client/addclientprintsettings?id='.$mdata['id'].'&profile=plansmedi"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" profile="plansmedi" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		
			$row_id++;
		}
		 
		$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $filter_count; // ??
		$response['data'] = $resulted_data;
		 
		$this->_helper->json->sendJson($response);
	}
	
	private function getreceiptprintsettings()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
	
		if(!$_REQUEST['length'])
		{
			$_REQUEST['length'] = "10";
		}
			
		$limit = $_REQUEST['length'];
		$offset = $_REQUEST['start'];
		$search_value = $_REQUEST['search']['value'];
			
		$columns_array = array(
				"0" => "profile_name",
				"1" => "margin_top",
				//"2" => "margin_bottom",
				"2" => "margin_left",
				//"4" => "margin_right",
		);
		$columns_search_array = $columns_array;
			
		if(isset($_REQUEST['order'][0]['column']))
		{
			$order_column = $_REQUEST['order'][0]['column'];
			$order_dir = $_REQUEST['order'][0]['dir'];
		}
		else
		{
			array_push($columns_array, "id");
			$nrcol = array_search ('id', $columns_array);
			$order_column = $nrcol;
			$order_dir = "ASC";
		}
	
		$fdoclimit = ReceiptPrintSettingsTable::findAllClientReceiptPrintSettings($clientid);
		
		foreach($fdoclimit as $fkey=>&$fval)
		{
			$fval['margin_top'] = $fval['profile_settings']['margin_top'];
			//$fval['margin_bottom'] = $fval['profile_settings']['margin_bottom'];
			$fval['margin_left'] = $fval['profile_settings']['margin_left'];
			//$fval['margin_right'] = $fval['profile_settings']['margin_right'];
		}
		//var_dump($fdoclimit);
		$full_count = count($fdoclimit);
	
		if(trim($search_value) != "")
		{
			$regexp = trim($search_value);
			Pms_CommonData::value_patternation($regexp);
	
			foreach($columns_search_array as $ks=>$vs)
			{
				$pairs[$vs] = trim(str_replace('\\', '',$regexp));
	
			}
			//var_dump($pairs);
			$fdocsearch = array();
			foreach ($fdoclimit as $skey => $sval) {
				foreach ($pairs as $pkey => $pval) {
					if($pkey == 'create_date')
					{
						$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
					}
	
					$pval_arr = explode('|', $pval);
	
					foreach($pval_arr as $kpval=>$vpval)
					{
						if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
							$fdocsearch[$skey] = $sval;
							break;
						}
					}
	
				}
			}
	
	
			$fdoclimit = $fdocsearch;
		}
		$filter_count  = count($fdoclimit);
			
		if($order_column != '')
		{
			$sort_col = array();
			foreach ($fdoclimit as $key=> $row)
			{
				
					$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
					$fdoclimit[$key] = $row;
					$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
				
			}
			if($order_dir == 'desc')
			{
				$dir = SORT_DESC;
			}
			else
			{
				$dir = SORT_ASC;
			}
			array_multisort($sort_col, $dir, $fdoclimit);
	
			$keyw = $columns_array[$order_column].'_tr';
			array_walk($fdoclimit, function (&$v) use ($keyw) {
				unset($v[$keyw]);
			});
		}
			
		if($limit != "")
		{
			$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
		}
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	
		$report_ids = array();
		$fdoclimit_arr = array();
		foreach ($fdoclimit as $key => $report)
		{
			$fdoclimit_arr[$report['id']] = $report;
			$report_ids[] = $report['id'];
		}
	
		$row_id = 0;
		$link = "";
		$resulted_data = array();
			
		foreach($fdoclimit_arr as $report_id =>$mdata)
		{
			$link = '%s ';
	
			$resulted_data[$row_id]['profile_name'] = sprintf($link, $mdata['profile_name']);
			$resulted_data[$row_id]['margin_top'] = $mdata['margin_top'];
			//$resulted_data[$row_id]['margin_bottom'] = $mdata['margin_bottom'];
			$resulted_data[$row_id]['margin_left'] = $mdata['margin_left'];
			//$resulted_data[$row_id]['margin_right'] = $mdata['margin_right'];
			$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'client/addclientprintsettings?id='.$mdata['id'].'&profile=receipt"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" profile="receipt" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	
			$row_id++;
		}
		
		$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $filter_count; // ??
		$response['data'] = $resulted_data;
			
		$this->_helper->json->sendJson($response);
	}
	
	public function addclientprintsettingsAction()
	{
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
	
		if($_REQUEST['id'])
		{
			$id = $_REQUEST['id'];
		}
		
		if($_REQUEST['profile'])
		{
			switch($_REQUEST['profile'])
			{
				case 'plansmedi':
					$blockname = 'PLANSMEDIPRINTSETTINGS';
					break;
				case 'receipt':
					$blockname = 'RECEIPTPRINTSETTINGS';
					break;
				default:
					break;
			}
			$saved_values = $this->_clientprintsettings_GatherDetails($id, $blockname);
		}
		//print_R($saved_values); exit;
		$form = new Application_Form_ClientPrintSettings(array(
				'_block_name'           => $blockname
		));
	
		//print_r($saved_values); exit;
		//
		$form->create_form_addclientprintsettings($saved_values);
	
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
	
		if($this->getRequest()->isPost())
		{
			//var_dump($form->isValid($this->getRequest()->getPost())); exit;
			if ($form->isValid($this->getRequest()->getPost())) {
				$savedprintsettings  = $form->save_form_clientprintsettings($_POST);
			}
			
			if($_POST['id'])
			{
				if ($form->isValid($this->getRequest()->getPost())) {
					$this->_redirect(APP_BASE . "client/clientprintsettings");
				}
				$this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
				switch ($_POST['block_name'])
				{
					case 'PLANSMEDIPRINTSETTINGS':
				
						if (!$form->isValid($this->getRequest()->getPost()))
						{							
							$this->_redirect(APP_BASE . "client/addclientprintsettings?id=".$_POST['id']."&profile=plansmedi");
						}
						break;
					case 'RECEIPTPRINTSETTINGS':
					
						if (!$form->isValid($this->getRequest()->getPost()))
						{
							$this->_redirect(APP_BASE . "client/addclientprintsettings?id=".$_POST['id']."&profile=receipt");
						}
						break;
				}
			}
			else 
			{
				switch ($_POST['block_name'])
				{
					case 'PLANSMEDIPRINTSETTINGS':
						if($_POST['unset_plans_nr'] == '1')
						{
							if ($form->isValid($this->getRequest()->getPost())) {
								$this->_redirect(APP_BASE . "client/clientprintsettings");
							}
							else
							{
								$this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
								$this->_redirect(APP_BASE . "client/addclientprintsettings?profile=plansmedi");
							}
							//$this->_redirect(APP_BASE . "client/clientprintsettings");
						}
						else 
						{
							if (!$form->isValid($this->getRequest()->getPost())) {
								$this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
							}
							$this->_redirect(APP_BASE . "client/addclientprintsettings?profile=plansmedi");
						}
						break;
					case 'RECEIPTPRINTSETTINGS':
						if (!$form->isValid($this->getRequest()->getPost())) {
							$this->_helper->flashMessenger->addMessage( $this->translate('only number are accepted'),  'ErrorMessages');
						}
						$this->_redirect(APP_BASE . "client/addclientprintsettings?profile=receipt");
				}
			}
				
		}
	}

	private function _clientprintsettings_GatherDetails( $id = null, $blockname)
	{
		switch($blockname)
		{
			case 'PLANSMEDIPRINTSETTINGS':
				$table = PlansMediPrintSettingsTable;
				break;
			case 'RECEIPTPRINTSETTINGS':
				$table = ReceiptPrintSettingsTable;
				break;
		}
		
		if($id)
		{
			$saved_formular = $table::getInstance()->findOneBy('id', $id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD);
		}
		if(!$saved_formular)
		{
			$saved_formular = $table::getInstance()->getFieldNames();
	
			foreach($saved_formular as $kcol=>$vcol)
			{
				$saved_formular_final[$vcol]['colprop'] = $table::getInstance()->getDefinitionOf($vcol);
				$saved_formular_final[$kcol]['value'] = null;
			}
		}
		else
		{
			foreach($saved_formular as $kcol=>$vcol)
			{
				$saved_formular_final[$kcol]['colprop'] = $table::getInstance()->getDefinitionOf($kcol);
				$saved_formular_final[$kcol]['value'] = $vcol;
			}
		}
		return $saved_formular_final;
	}
	//Maria:: Migration CISPC to ISPC 22.07.2020	

    /**
     * Admin-Functionality for edit the client-configuration for ISPC-Clinic-Clients. (IM-54)
     * Ccreate a small solution at first. So we only give's an opportunity for manipulate the json-file in View.
     * Call the view: /public/client/clinicconfig.
     * Thee is no link for the view so far...
     *
     * By call the first time for a client, there will use default-values as a skeleton.
     *
     * @author baerbel
     * @throws Zend_Session_Exception
     */
	public function clinicconfigAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');

        if($logininfo->usertype != 'SA')
        {
            $this->redirect(APP_BASE . "error/previlege");
            exit;
        }
        $clientid = $logininfo->clientid;

        //save the changes
        if ($this->getRequest()->isPost()) {
            if ($_POST['clinicconfic']) {
                $config = $_POST['clinicconfic'];
                $content = json_decode($config);
                //https://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
                $error = json_last_error();
                if ($error === JSON_ERROR_NONE) {
                    ClientConfig::saveConfig($clientid, 'clinicconfic', $content);
                    $this->view->message = $this->view->translate('clinic_config_success');
                }
                else{
                    $this->view->message = $this->view->translate('clinic_config_error'). json_last_error_msg();
                }
                $this->view->config = $config;
            }
        }

        if ($this->getRequest()->isGet()) {

            //get the configuration for this client
            $config = ClientConfig::getConfig($clientid, 'clinicconfic');

            // if there is no configuration, use the default
            if (!$config) {
                $config = Client::get_clinic_default_config();
            }
            $config = json_encode($config, JSON_PRETTY_PRINT);
            //$config = json_encode($config);

            $this->view->config = $config;
        }


    }



    /**
     * Config Tags that can be sent with hl7
     * @author Nico
     */

    public function clientsendabletagsAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        if(!$logininfo->clientid)
        {
            //redir to select client error
            $this->_redirect(APP_BASE . "error/noclient");
            exit;
        }


        if($_POST){
            if(isset($_POST['new_tag'])) {
                $pfnt = new PatientFileTags();
                $pfnt->client = $clientid;
                $pfnt->tag = $_POST['new_tag'];
                $pfnt->save();
            }elseif(isset($_POST['hl7_create_final_reports_users'])){
                $new=[];
                foreach($_POST['hl7_create_final_reports_users'] as $k => $v){
                    if(strlen($v)>0 && $v!=="0") {
                        $new[] = $v;
                    }
                }
                ClientConfig::saveConfig($clientid, 'hl7_create_final_reports_users', $new);
            }else {
                $cconfig = [];
                foreach ($_POST['tag'] as $entry) {
                    $tag = Doctrine::getTable('PatientFileTags')->findOneById(intval($entry['id']));

                    if ($tag->restricted && !isset($entry['restricted'])) {
                        $tag->restricted = 0;
                    }
                    if (isset($entry['restricted']) && $tag->restricted == 0) {
                        $tag->restricted = 1;
                    }
                    $tag->save();

                    if ($entry['sendable'] !== "") {
                        $cconfig[$entry['id']] = $entry;
                    }
                }

                ClientConfig::saveConfig($clientid, 'sendabletags', $cconfig);
            }
        }

        $this->view->cusers=User::getUsersWithGroupnameFast($clientid);
        $this->view->hl7_create_final_reports_users=ClientConfig::getConfig($clientid, 'hl7_create_final_reports_users');

        $cconfig=ClientConfig::getConfig($clientid, 'sendabletags');
        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('*');
        $fdoc1->from('PatientFileTags pft INDEXBY pft.id');
        $fdoc1->Where("pft.client = ".$clientid);
        $fdoc1->andWhere("pft.isdelete = 0  ");

        $this->view->available_tags = Hl7DocSend::get_sendable_tags($clientid, true);


        $form_types=new FormTypes();
        $client_form_types = $form_types->get_form_types($clientid);
        array_unshift($client_form_types, ['id'=>'','name'=>'']);
        $ft=array_combine(array_column($client_form_types,'id'), array_column($client_form_types,'name'));

       // $ft=array_merge([''=>''],$ft);
        $this->view->formtypes = $ft;

    }






	//Lore 27.11.2019// Maria:: Migration ISPC to CISPC 08.08.2020
	public function permissionstoclientsAction()
	{
        $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	   
	    
	    // client details
	    $client_data = array();
	    $client = Doctrine_Query::create()
	    ->select(" id,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
	    ->from('Client indexBy id')
	    ->orderBy('client_name ASC');
 
	    $client_data = $client->fetchArray();
	    
	    // Reportrss
	    $report = Doctrine_Query::create()
	    ->select('*')
	    ->from('Reportsnew indexBy id')
	    ->where('isdelete = 0');
	    $reportarray = $report->fetchArray();
	    $this->view->permreportarray = $reportarray;
	    
	    
	    // Report permissions
	    $report_perms = Doctrine_Query::create()
	    ->select('*')
	    ->from('ReportPermission')
	    ->where('isdelete = 0');
	    $report_perms_array = $report_perms->fetchArray();
	    
	    $cl_perms = array();
	    foreach($reportarray as $k_id => $k_vals)
	    {
	        foreach($report_perms_array as $k_pm_ids => $k_pm_vals){
	            
	            $cl_rep = $k_pm_vals['clientid'];
	            $report_id_str = $k_pm_vals['report_id'];
	            
	            if(array_key_exists( $report_id_str, $cl_perms)){
	                
	                $array_key = $k_pm_vals["clientid"];
	                $array_vals = $client_data[$k_pm_vals["clientid"]]["client_name"];
	                $cl_perms[$report_id_str] += [$cl_rep => $array_vals];
	                
	            }else{
	                $cl_perms[$report_id_str][$k_pm_vals['clientid']] = $client_data[$k_pm_vals['clientid']]['client_name'];
	            }
	        }
	    }
	   
	    $this->view->permreportclarray = $cl_perms;
	}
	
	public function assignsclientsAction()
	{
        $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $report_id = $_REQUEST['value'];
	    
	    if($this->getRequest()->isPost())
	    {
	        if(!empty($report_id)){
	            // marchez tot ce am in db, pentru report_id-ul meu, ca isdelete = 1
	            $q = Doctrine_Query::create()
	            ->update('ReportPermission')
	            ->set('isdelete',"1")
	            ->where('report_id = ?', $report_id);
	            $q->execute();
	                        
	            foreach($_POST['clientid'] as $key => $valus ){
	                
	                $grp = new ReportPermission();
	                $grp->clientid = $valus;
	                $grp->report_id = $report_id;
	                $grp->save();
	            }
	        }
	        
	        $this->_redirect(APP_BASE . "client/permissionstoclients");

	    }
	    // client details
	    $client_data = array();
	    $client = Doctrine_Query::create()
	    ->select(" id, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
	    ->from('Client indexBy id')
	    ->where('isdelete = 0')
	    ->orderBy("AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "')  ASC" );
	    	    
	    $client_data = $client->fetchArray();
	    $this->view->clientdataarray = $client_data;
	     
	     $clients_repo = Doctrine_Query::create()
	    ->select('*')
	    ->from('ReportPermission indexBy clientid')
	    ->where('isdelete = 0')
	    ->andWhere('report_id = ?', $report_id);
	    $clients_repo_arr = array_keys($clients_repo->fetchArray());

	    $this->view->clientsrepo = $clients_repo_arr;
 
	}
	
	
	
	/**
	 * ISPC-2474  Ancuta 23.10.2020
	 */
	public function patientpermanentdeletionAction(){

	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $modules = new Modules();
	    $allow_delete = $modules->checkModulePrivileges("243", $clientid);
	    if(!$allow_delete){
	        $this->_redirect(APP_BASE . "error/previlege");
	    }
	    
	    
	    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
	    
	    $this->_populateCurrentMessages(); // this will not show the success message caue i used ajax on success
	    
	    if ( $this->getRequest()->isPost() ) {
	        
	        switch ($step = $this->getRequest()->getPost('step')) {
	            
	            case "_fetch_patients_list":
	                $result = $this->_fetch_patients_list();
	                //this result is ech
	                break;
	                
	            case "validate_user_pass" :
	                    
   	                $user_password = $this->getRequest()->getPost('user_password');
   	                
   	                $result = array();
   	                
   	                $result['error'] = 1;
   	                $result['success'] = 0;
   	                $result['message'] ="";
   	                
   	                if( empty($user_password)){
   	                    
   	                    $result['error'] = 1;
   	                    $result['message'] = $this->view->translate("Please enter password");
   	                }
   	                else
   	                {
       	                $validate_pass = $this->_validate_user_passwod($user_password);
       	                if( ! $validate_pass){
       	                    
       	                    $result['error'] = 2;
       	                    $result['message'] = $this->view->translate("Password is incorrect");
       	                    
       	                } else{
       	                    
       	                    $result['error'] = 0;
       	                    $result['success'] = 1;
       	                }
   	                }
	                break;
	        
	            case "schedule_delete" :
	                
	                $enc_ids = $this->getRequest()->getPost('idpd');
	                $ids = array();
	                
	                foreach ($enc_ids as $enc_id) {
	                    $ids[] = Pms_Uuid::decrypt($enc_id);
	                }
	                
	                $enc_ids_un = $this->getRequest()->getPost('idpd_uncheck');
	                $unchecked = array();
	                
	                foreach ($enc_ids_un as $enc_idu) {
	                    $unchecked[] = Pms_Uuid::decrypt($enc_idu);
	                }
	                
	                
	                $result = $this->_schedule_delete($ids,$unchecked);
	                
	                break;
	        }
	        
	        $this->_helper->json($result);
	        
	        exit; //for readability
	    }
	}
	
	 
	/**
	 * ISPC-2474  Ancuta 23.10.2020
	 * @return void|number[]|array[]
	 */
	private function _fetch_patients_list()
	{
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setNoRender(true); // disable view rendering
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        // get all from, Patient4deletion Where final_delete =0

        $limit = intval($this->getRequest()->getPost('iDisplayLength', 10));
        $offset = intval($this->getRequest()->getPost('iDisplayStart', 0));

        $orderColId = intval($this->getRequest()->getPost('iSortCol_0', 0));
        $orderCol = $this->getRequest()->getPost("mDataProp_{$orderColId}", "last_name");
        $orderDir = strtoupper($this->getRequest()->getPost("sSortDir_0", "ASC"));

        $stringSearch = trim($this->getRequest()->getPost("sSearch"));

        if (! in_array($orderCol, array(
            'epid',
            'last_name',
            'first_name'
        ))) {
            $orderCol = "last_name"; // fail-safe
        }

        if (! in_array($orderDir, array(
            'ASC',
            'DESC'
        ))) {
            $orderDir = "ASC"; // fail-safe
        }

        $sorting = array(
            "limit" => $limit,
            "offset" => $offset,
            "orderCol" => $orderCol,
            "orderDir" => $orderDir,
            "stringSearch" => $stringSearch
        );

        $wanted_ipids = array();
        $qr = Doctrine_Query::create()->select("
	        p.ipid,
            e.epid as epid,
            p.birthd,
            p.admission_date,
            p.change_date,
            p.last_update,
            CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,
            CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,
            CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,
            CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,
            CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,
            CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,
            CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,
            CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,
            CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,
            CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,
            CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,
            CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex,
            p4d.*
	        ")
            ->
        from('PatientMaster p')
            ->leftJoin("p.EpidIpidMapping e")
            ->leftJoin("p.Patient4Deletion p4d")
            ->where('e.clientid = ?', $clientid)
            ->andWhere('p.isdelete = 0')
            ->andWhere("p.isstandbydelete = 0")
            ->andWhere("p4d.final_delete = 0");

        if (! empty($sorting['stringSearch'])) {

            Pms_CommonData::value_patternation($sorting['stringSearch']);
            $qr->andWhere("CONVERT( CONCAT_WS(' ', AES_DECRYPT(p.first_name, '" . Zend_Registry::get('salt') . "'), AES_DECRYPT(p.last_name, '" . Zend_Registry::get('salt') . "'), e.epid  ) USING utf8 ) REGEXP ?", $sorting['stringSearch']);
        }

        if (! empty($sorting['orderCol'])) {

            if (in_array($sorting['orderCol'], array(
                'first_name',
                'last_name'
            ))) {
                $sorting['orderCol'] = "AES_DECRYPT(p.{$sorting['orderCol']}, '" . Zend_Registry::get('salt') . "')";
            } elseif ($sorting['orderCol'] == 'epid') {
                $sorting['orderCol'] = "e.epid";
            } else {
                // fail-safe here.. what column did you sent ? is this a new one?
                $sorting['orderCol'] = "e.epid";
            }

            // re-sanitize $sorting['orderDir']
            $sorting['orderDir'] = strtoupper($sorting['orderDir']) == 'ASC' ? 'ASC' : 'DESC';

            $qr->orderBy("{$sorting['orderCol']} {$sorting['orderDir']}");
        }

        $patients_details_nl = $qr->fetchArray();
        $all_ipids = array_column($patients_details_nl, 'ipid');
        
        if ($sorting['limit'] > 0) {

            $qr->limit($sorting['limit']);

            if ($sorting['offset'] > 0) {
                $qr->offset($sorting['offset']);
            }
        }
        $patients_details = $qr->fetchArray();

        if (empty($patients_details)) {
            $this->returnDatatablesEmptyAndExit();
            return; // this has no patients for dgp
        }

        $user_data = User::getUserByClientid($clientid, '1', true);
        
        $data = array();
        foreach ($patients_details as $patient) {

            $wanted_ipids = $patient['ipid'];

            $selected4deletion = "";
            $scheduled = false;
            if ($patient['Patient4Deletion']['scheduled_deletion'] == '1') {
                $selected4deletion = 'checked="checked"';
                $scheduled = true;
            }

            $discharge_date = $patient['Patient4Deletion']['discharge_date'];
            $discharge_date_plus_one_year =  date("Y-m-d H:i:s",strtotime("+ 1 year",strtotime($patient['Patient4Deletion']['discharge_date'])));
            $last_date = $patient['Patient4Deletion']['last_action_date'];
            
            if($last_date > $discharge_date_plus_one_year){
                $last_update_info = '<i style="color:red">'.date('d.m.Y H:i', strtotime($patient['Patient4Deletion']['last_action_date'])).'</i>'; 
            } else{
                $last_update_info = date('d.m.Y H:i', strtotime($patient['Patient4Deletion']['last_action_date'])); 
            }
            
            
            
            
            $pat = array(
                'idpd' => Pms_Uuid::encrypt($patient['id']),
                'debug' => '',
                'auto-increment' => $patient['epid'],
                'scheduled' => $scheduled,
                'epid' => $patient['epid'],
                'last_name' => $patient['last_name'],
                'first_name' => $patient['first_name'],
                'discharge_date' => isset($patient['Patient4Deletion']['discharge_date']) ? date('d.m.Y H:i', strtotime($patient['Patient4Deletion']['discharge_date'])) : "", 
                'last_action_date' => $last_update_info,  
//                 'last_action_date' => isset($patient['Patient4Deletion']['last_action_date']) ? date('d.m.Y H:i', strtotime($patient['Patient4Deletion']['last_action_date'])) : "",  
                'scheduled_deletion' => '<input type="checkbox" value="' . Pms_Uuid::encrypt($patient['id']) . '" name="scheduled_deletion[]" ' . $selected4deletion . ' >',
                'scheduled_date' => isset($patient['Patient4Deletion']['scheduled_date']) && $patient['Patient4Deletion']['scheduled_date'] != "0000-00-00 00:00:00" ? date('d.m.Y H:i', strtotime($patient['Patient4Deletion']['scheduled_date'])) : "",  
                'scheduled_user' => isset($patient['Patient4Deletion']['scheduled_user']) && !empty($patient['Patient4Deletion']['scheduled_user']) ? $user_data[$patient['Patient4Deletion']['scheduled_user']] : ""
                
            );
            
            array_push($data, $pat);
        }

        $response = array(
            'draw' => (int) $this->getRequest()->getParam('draw'),
            'recordsTotal' => count($all_ipids),
            'recordsFiltered' => count($all_ipids),
            'data' => $data
        );

        return $response;
    }
	/**
	 * ISPC-2474  Ancuta 23.10.2020
	 * @param unknown $user_pass
	 * @return boolean
	 */
    private function _validate_user_passwod($user_pass)
    {     
        if(empty($user_pass)){
            return false;
        }
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $user = Doctrine_Query::create()
        ->select('*')
        ->from('User')
        ->where("id = ? and patient_deletion_password=? and isdelete=0 and isactive=0 and patient_deletion_allowed=1", array($userid, md5($user_pass) ));
        $userexec = $user->execute();
	    $userarray = $userexec->toArray();
	    
	    if( ! empty($userarray) ) {
	        return true;
	    }  else {
	        return false;
	    }
	    
    }
	
	/**
	 * ISPC-2474  Ancuta 23.10.2020
	 * {@inheritDoc}
	 * @see Pms_Controller_Action::returnDatatablesEmptyAndExit()
	 */
	protected function returnDatatablesEmptyAndExit()
	{
	    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
	    $viewRenderer->setNoRender(true); // disable view rendering
	    
	    $response = array();
	    $response['draw'] = (int)$this->getRequest()->getParam('draw');  
	    $response['recordsTotal'] = 0;
	    $response['recordsFiltered'] = 0; 
	    $response['data'] = array();
	    
	    ob_end_clean();
	    ob_start();
	    
	    $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
	    $json->sendJson($response);
	    
	    exit;
	}
	/**
	 * ISPC-2474  Ancuta 23.10.2020
	 * @param unknown $patient_ids
	 * @return boolean[]|string[]
	 */
	private function _schedule_delete($patient_ids,$patient_ids_unchecked = array())
	{
	    
// 	    dd(func_get_args());
	    $result = array(
	        'success' => false,
	        'message' => $this->translate("[No patients  selected for deletion]") . " (error : 1)"
	    );
	    
	    if (empty($patient_ids) || ! is_array($patient_ids)) {
	        
	        return $result;//fail-safe
	    }
	    
	    $patients_array = $patient_ids;
	    if(!empty($patient_ids_unchecked)){
	        $patients_array = array_merge($patients_array,$patient_ids_unchecked);
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    
	    $patients = Doctrine_Query::create()
	    ->select("p.id, p.ipid")
	    ->from('PatientMaster p')
	    ->leftJoin("p.EpidIpidMapping e")
	    ->whereIn('p.id', $patients_array)
	    ->andWhere("e.clientid = ?" , $clientid) //enforced
	    ->fetchArray()  ;
	    
	    if (empty($patients)) {
	        return $result;//fail-safe
	    }
	    
	    $ipid2id = array();
	    foreach($patients as $k=>$pdata){
	        $ipid2id[$pdata['ipid']] =$pdata['id'];
	    }
	    
	    $ipids = array_column($patients, 'ipid');

	    foreach($ipids as $k=>$ipid){
	        $p_4del = Doctrine::getTable('Patient4Deletion')->findOneByIpid($ipid);
	        if($p_4del){
	            $data =$p_4del->toArray();

	            // unset already selected
	            if($data['scheduled_deletion'] == '1' && in_array($ipid2id[$ipid],$patient_ids_unchecked) ){
    	            $p_4del->scheduled_deletion = 0;
    	            $p_4del->scheduled_user = 0;
    	            $p_4del->scheduled_date = "0000-00-00 00:00:00";
    	            $p_4del->save();
	            } else {
	                if(in_array($ipid2id[$ipid],$patient_ids)){
	                    
        	            $p_4del->scheduled_deletion = 1;
        	            $p_4del->scheduled_user = $userid;
        	            $p_4del->scheduled_date = date('Y-m-d H:i:s', time());
        	            $p_4del->save();
	                }
	            }
	        }
	    }
	    
// 	    $this->__StartPatientDeletion();
	    
	    $result = array(
	        'success' => true,
	        'message' =>  $this->translate("[Patients scheduleds for deletion]") . ""
	    );
	    
	    
	    return $result;
	}
	
	public function __StartPatientDeletion(){
	    $appInfo = Zend_Registry::get('appInfo');
	    $app_path  = 	isset($appInfo['appCronPath']) && !empty($appInfo['appCronPath']) ? $appInfo['appCronPath'] : false;
	    
	    $function_path = $app_path.'/cron/processpatientdeletion';
	    popen('curl -s '.$function_path.' &', 'r');
	}
	
	
	/**
	 * ISPC-2474  Ancuta 23.10.2020
	 */
	private function _populateCurrentMessages()
	{
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	        );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	        );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	}
	
	
}

?>
