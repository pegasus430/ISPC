<?php

class UserController extends Pms_Controller_Action 
{
    public function init()
    {
    	/* Initialize action controller here */
        $this->setActionsWithJsFile('elviroom');
    }

		public function adduserAction()
		{
		    $this->_helper->layout->setLayout('layout');
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($_GET['id'] > 0)
			{
				$clientid = $_GET['id'];
			}
			else
			{
				$clientid = $logininfo->clientid;
			}
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			if(isset($_GET['id']))
			{
				$get = "?id=" . $_GET['id'];
			}
			$this->view->act = "user/adduser" . $get;
			$this->view->titlearray = array("" => "", $this->view->translate('mr') => $this->view->translate('mr'), $this->view->translate('mrs') => $this->view->translate('mrs'), $this->view->translate('miss') => $this->view->translate('miss'));

			$this->view->logininfo_usertype = $logininfo->usertype ;

			//ISPC-2827 Ancuta 26.03.2021
			$this->view->logininfo_isEfaClient = $logininfo->isEfaClient ;
			//--
			
			if($clientid > 0)
			{
				$returnval = Pms_CommonData::getClientuser($clientid);

				if(!$returnval)
				{
					$this->_redirect(APP_BASE . "error/userlimit");
				}
			}

			$modules_obj = new Modules();
			$clientModules =
			$this->view->clientModules =  $modules_obj->get_client_modules($clientid);
			
			if($this->getRequest()->isPost())
			{
				$user_form = new Application_Form_User();
				if($user_form->validate($_POST))
				{
					$userEntity = $user_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
					
					//ispc-2060
					if ( ! empty($clientModules) && $clientModules[175]) {
					
					    if ($post_elvi = $this->getRequest()->getPost('elvi')) { //elVi user login credentials
					
					        $a_f_ue = new Application_Form_ElviUsers();
					        if ($a_f_ue->validate($post_elvi)) {
					            $a_f_ue->save($post_elvi, $userEntity);
					        }
					    }
					}
					
					
					$this->_redirect(APP_BASE . "user/listuser?flg=suc");
				}
				else
				{
					$user_form->assignErrorMessages();
					$this->retainValues($_POST);
					$this->view->title = $_POST['title'];
					$this->view->groupid = $_POST['groupname'];
				}
			}

			if($logininfo->usertype == 'SA')
			{
				$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
					->from('Client')
					->where('id = ?', $clientid);

				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<div class="dlabel"><label for="client_name">' . $this->view->translate('client_name') . '</label></div>'
				    . '<div class="dinput">'
			        . $clientarray[0]['client_name']
				    . '<input name="client_name" type="hidden" value="' . $clientarray[0]['client_name'] . '" >'
	                . '<input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />'
                    . '</div>';
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			$usergrp = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid = ?', $clientid )
				->andWhere('isdelete = ?',0);
			$usergrpexec = $usergrp->execute();
			$grouparray = $usergrpexec->toArray();
			$group = array("0" => "");
			foreach($grouparray as $key => $val)
			{
				$group[$val['id']] = $val['groupname'];
				$grouparray_all[$val['id']] = $val;
			}
			$this->view->grouparray = $group;
			$this->view->grouparray_all = $grouparray_all;
			$this->view->clientid = $clientid;
			
			//ISPC-2018
			//ISPC-2138
			$this->view->pat_file_tags_rights_values = array(
			    'create'=> $this->view->translate('may create labels'), 
			    'use'=> $this->view->translate('may use labels')
			);
			$this->view->pat_file_tags_rights = null;
			
			//ISPC-2162
			$saved_receipt_profiles = ReceiptPrintSettingsTable::findAllClientReceiptPrintSettings($this->logininfo->clientid);
			//var_dump($saved_receipt_profiles); exit;
			foreach($saved_receipt_profiles as $kr => $vr)
			{
				$client_receipt_profiles[$vr['id']] = $vr['profile_name'];
			}
			//var_dump($client_receipt_profiles); exit;
			//$this->view->client_receipt_profiles = $client_receipt_profiles;
				
			$printsettings_form = new Application_Form_ClientPrintSettings(array(
					'_block_name'           => null,
					'_client_receipt_profiles' => $client_receipt_profiles,
						
			));
			
			$this->view->receipt_print_settings_form = $printsettings_form->create_form_userselectreceiptprintsettings($receipt_print_settings_arr[0]);
			
			//ISPC-1835
			$this->view->user_settings = null;
            
			//ISPC-2513 Lore 13.04.2020
			// #ISPC-2512PatientCharts
			$this->view->header_type = 'default_ispc_header';
			
			//ISPC-2138 p.6
			$this->view->verlauf_newest = 't'; //VERLAUF TOP
			$this->view->verlauf_fload = 'n';
		    $this->view->verlauf_action = 'd'; //SORTED CHRONOLOGICALLY
			
		}

		public function edituserAction()
		{ 
		    
		    $this->_helper->viewRenderer('adduser');
		    
		    $logininfo = $this->logininfo;
		    
		    if ( ! empty($_GET['id'])) {
		        
		        $userid = $_GET['id'];
		        
		        $this->view->act = "user/edituser" . "?id=" . $_GET['id'] . "&cid=" . $_GET['cid']; // form action
		        
		        $redirect_location = 'user/listuser';
		        
		    } elseif ($this->getParam('action') == 'editprofile') {//ISPC-2138 forward from editprofileAction
		        
		        /*
		         * ISPC-2060
		         * i've piggybacked this action's rights so i can keep a dev elviroomAction
		         */
		        if ($this->getParam('pseudo_action') == 'elviroom') {
		            return $this->elviroomAction();
		            exit; // for readability 
		        }
// 		        $this->getParam('action') == 'editprofile');
		        
		        $userid = $this->getParam('id');
		        
		        $clientid = $this->getParam('clientid');
		        
		        $this->view->act = "user/editprofile"; // form action
		        
		        $this->view->editprofile_page = 1;//a marker to know we are from action=editprofile
		        
		        $redirect_location = 'user/editprofile';
		    }
		    
		  
		    if ( ! empty($_GET['cid']) && ! empty($_GET['id'])) {

		        $clientid = $_GET['cid'];
		        
		    } else {
		        
		        if ($userid == $this->logininfo->userid && $this->logininfo->usertype == 'SA') {
		            
		            $clientid = 0; //set to 0 so we can edit SA's
		            
		        } elseif ($this->logininfo->usertype == 'CA' || $this->logininfo->usertype == 'SA') {   

		            $clientid = $this->logininfo->clientid; //set CA's cleint so we edit only users from this
		            
		        }
		        
		    }
		    
		    if (empty($userid)) {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    
		    if ($userid != $logininfo->userid) { //check permission cause you are editing a different user than yourself 
		    	
		        $has_edit_permissions = Links::checkLinkActionsPermission();
		        
    			if( ! $has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    			{
    				$this->_redirect(APP_BASE . "error/previlege");
    				exit;
    			}
		    }
		    
		    if ( $this->logininfo->usertype == 'SA') {
		        //SA's canedit any user 
		        $user = Doctrine::getTable('User')->find($userid);
		        
		    } else {
		        
		        $user = Doctrine::getTable('User')->findOneByIdAndClientid($userid, $clientid);
		    }
		    
		    
		    
		    if (empty($user)) {
		        
		        //this is NOT your user, you have no permission to edit other client's users 
		        
		        $this->redirect(APP_BASE . "error/previlege", array(
		            "exit" => true
		        ));
		        
		        exit;
		    }
		    
		    $userEntity = $user;
		    
		    $clientid = $user->clientid;// PLEASE CHECK
		    
		    
		    $modules_obj = new Modules();
		    $clientModules =
		    $this->view->clientModules =  $modules_obj->get_client_modules($clientid);
		    

		    // ISPC-2285 ( Added by Ancuta 13.12.2018)
		    $clientarr = Pms_CommonData::getClientData($this->logininfo->clientid);
		    $client_last_xx_entries = $clientarr[0]['maxcontact'];
		    $this->view->client_last_xx_entries = $client_last_xx_entries;
		    // -- 	
		    
// 		    $userarray = $user->toArray();
		    
// 			if(strlen($_GET['id']) > 0)
// 			{
// 				$cid = $_GET['id'];
// 				$edit = "?id=" . $_GET['id'] . "&cid=" . $_GET['cid'];
// 			}
// 			else
// 			{
// 				$cid = $logininfo->userid;
// 			}

// 			if($_REQUEST['cid'] > '0' && $_REQUEST['id'] > '0')
// 			{
// 				$clientid = $_REQUEST['cid'];
// 			}
// 			else
// 			{
// 				$clientid = $logininfo->clientid;
// 			}

			if($_GET['flg'] == 'esuc') {
				$this->view->error_message = $this->view->translate("emailupdatedsucessfully");
			}

			if($_GET['flg'] == 'suc') {
			    $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
			
// 			$this->view->act = "user/edituser" . $edit;
			$this->view->titlearray = array(
			    "" => "", 
			    $this->view->translate('mr') => $this->view->translate('mr'), 
			    $this->view->translate('mrs') => $this->view->translate('mrs'), 
			    $this->view->translate('miss') => $this->view->translate('miss')
			);
			
			//fn checkPrevilege is disabled
// 			$previleges = new Pms_Acl_Assertion();
// 			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');

// 			if(!$return)
// 			{
// 				$this->_redirect(APP_BASE . "error/previlege");
// 			}
			
// 			$this->view->pat_file_tags_rights_values = array('1'=>$this->view->translate('create'), '2'=>$this->view->translate('use'));
			//ISPC-2018
			//ISPC-2138
			$this->view->pat_file_tags_rights_values = array(
			    'create'=> $this->view->translate('may create labels'),
			    'use'=> $this->view->translate('may use labels')
			);

			//ISPC-2513 Lore 13.04.2020
			// #ISPC-2512PatientCharts
			$this->view->header_type = $user['header_type'];
			
 
			$this->view->logininfo_usertype = $logininfo->usertype ;
			
			//ISPC-2827 Ancuta 26.03.2021
			$this->view->logininfo_isEfaClient = $logininfo->isEfaClient ;
			//-- 
			
			
			if($this->getRequest()->isPost())
			{
			    
// 			    dd($this->getRequest()->getPost());
			    
    			$validate_post_failed = false;  
			    
			    //fn checkPrevilege is disabled
// 				$previleges = new Pms_Acl_Assertion();
// 				$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canedit');

// 				if(!$return)
// 				{
// 					$this->_redirect(APP_BASE . "error/previlege");
// 				}

				$client_form = new Application_Form_User();
                //ISPC-2913,Elena,11.05.2021
				$user_to_deactivate = false;
				if(strval($_POST['isactive']) == '1'){
                    $user_to_deactivate = true;
                }

// 				if($_GET['id'] > 0)
// 				{
					if($client_form->validate(array_merge($_POST, array('edit_userid'=>$userid))))
					{
					   
						$user_association_form = new Application_Form_UsersAssociation();
						$user_association_form->add_association_useredit($_POST, $clientid, $userid);

						if($_POST['has_invoice_number'] == "1" && strlen($_POST['invoice_start']))
						{
							$user_invoice_settings_form = new Application_Form_InternalInvoiceSettings();
							$user_invoice_settings_form->insert_invoice_settings($_POST, $clientid, $userid);
						}
						
						
						
						
						//$change = $client_form->UpdateData($_POST);
						$userEntity = $client_form->validateeditProfile($_POST, $userid);
                        //ISPC-2913,Elena,11.05.2021
						if($user_to_deactivate){
						    $internal_text = 'Folgende/r Benutzer/in wurde deaktiviert:<br>';
						    $internal_text .= 'Vorname: ' . $user->first_name . '<br>';
                            $internal_text .= 'Lastname: ' . $user->last_name . '<br>';
						    $internal_text .= 'Benutzername: ' . $user->username . '<br>';

						    $ur = new UserRequest();
						    $userrequests = $ur->findDeactivateRequestsForUser($userid);

						    foreach($userrequests as $urequest){
						        $requester_id = $urequest['create_user'];
						        $request_id = $urequest['id'];
                                //ISPC-2913,Elena,11.05.2021
                                $msg = new Messages();
                                $title = 'Benutzer deaktiviert: Ihre Anfrage';

                                $msg->sender = 0; // system message
                                $msg->clientid = $clientid;
                                $msg->recipient = $requester_id ;
                                //$msg->ipid = $todo['ipid'];
                                $msg->msg_date = date("Y-m-d H:i:s", time());
                                $msg->title = Pms_CommonData::aesEncrypt($title);
                                $msg->content = Pms_CommonData::aesEncrypt($internal_text);
                                $msg->source = 'userrequest_client';
                                $msg->create_date = date("Y-m-d", time());
                                $msg->create_user = 0;
                                $msg->read_msg = '0';
                                $msg->save();
                                $ur->markSolved($request_id);
                            }
                        }
						$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
						
						$flag = "suc";
// 						if($change == 'change')
// 						{
// 							$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
// 							$this->_redirect(APP_BASE . 'user/listuser');
// 						}
// 						else
// 						{
							
							
						if ( ! empty($clientModules) && $clientModules[155]) {
						    
						    $post_user_offline = $this->getRequest()->getPost('user_offline');
						    if ( ! empty($post_user_offline)) {
						
						        $post_user_offline['username'] = mb_convert_case(trim($post_user_offline['username']), MB_CASE_LOWER);
						        $post_user_offline['clientid'] = $clientid;
						        $post_user_offline['userid'] = $userid;
						        						        
    						    $offline_user_form = new Application_Form_UserOffline();
    						    if($offline_user_form->validate_offline_user($post_user_offline)) {
    						        $insert = $offline_user_form->save_offline_user($post_user_offline);
    						    } else {
    						        $this->view->error_message .= "<br/>" .implode("<br/>", $offline_user_form->getErrorMessages());
    						        $offline_user_form->assignErrorMessages();
    						        $flag = "err";
    						        
    						    }
						    }
						    
						    $post_certified_devices = $this->getRequest()->getPost('certified_devices');
						    if ( ! empty($post_certified_devices)) {
						        
						        $certified_devices_form = new Application_Form_CertifiedDevices();
					            $post_certified_devices['clientid'] = $clientid;
					            $post_certified_devices['userid'] = $userid;
					            $post_certified_devices['create_date'] = date('Y-m-d H:i:s');
						        
						        if (isset($post_certified_devices['deviceid']) && ! empty($post_certified_devices['deviceid'])) {
						            $certified_devices_form->save_certified_device($post_certified_devices);
						        }
						        
						        if (isset($post_certified_devices['remove']) && ! empty($post_certified_devices['remove'])) {
						            $certified_devices_form->delete_certified_device($post_certified_devices);
						        }   						        
						    }
						}
						
						
					    //ispc-2060
						if ( ! empty($clientModules) && $clientModules[175]) {

					        if ($post_elvi = $this->getRequest()->getPost('elvi')) { //elVi user login credentials

					            $a_f_ue = new Application_Form_ElviUsers();
					            if ($a_f_ue->validate($post_elvi)) {
					                $a_f_ue->save($post_elvi, $userEntity);
					            }
					        }
						}
						
							
// 							if(isset($_GET['cid'])) {
// 								$get = "&id=" . $_GET['cid'];
// 							}
// 							$this->_redirect(APP_BASE . 'user/listuser?flg=suc' . $get);

						if ($flag == 'suc') {
						    
						    $NavigationMenus = new Zend_Session_Namespace('Navigation_Menus');
						    $NavigationMenus->menus = null; //reset for top menu settings
						    
							$this->redirect(APP_BASE . $redirect_location . '?flg=' . $flag, array(
							    "exit" => true
							));
						}
// 						}
					}
					else
					{
						$client_form->assignErrorMessages();
						$validate_post_failed = true;
						
// 						$this->retainValues($_POST);
						$this->view->title = trim($_POST['title']);
						$this->view->groupid = $_POST['groupname'];
// 						return;
					}
// 				}
// 				else
// 				{

// 					if($client_form->validate($_POST))
// 					{

// 						$change = $client_form->UpdateData($_POST);
// 						if($change == 'change')
// 						{
// 							$this->view->error_message = $this->view->translate("verificaionmailsentonnewmailidrecordupdatesucessfully");
// 							$this->retainValues($_POST);
// 							$this->view->title = $_POST['title'];
// 							$this->view->groupid = $_POST['groupname'];
// 						}
// 						else
// 						{
// 							$this->retainValues($_POST);
// 							$this->view->title = $_POST['title'];
// 							$this->view->groupid = $_POST['groupname'];
// 							$this->_redirect(APP_BASE . 'user/listuser?flg=suc');
// 							$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
// 						}
// 					}
// 					else
// 					{
// 						$client_form->assignErrorMessages();

// 						$this->retainValues($_POST);
// 						$this->view->title = $_POST['title'];
// 						$this->view->groupid = $_POST['groupname'];
// 					}
// 				}
			}

// 			if($userid > 0)
// 			{

				$client_user_array = User::get_client_users($clientid, 1);
				$this->view->client_user_array = $client_user_array;

				$associated_users = UsersAssociation::get_associated_user($userid);
				$this->view->associated_user = $associated_users[$userid];

				$invoice_settings = InternalInvoiceSettings::getUserInternalInvoiceSettings($userid, $clientid);

				if($invoice_settings)
				{
					$this->view->invoice_prefix = $invoice_settings[$userid]['invoice_prefix'];
					$this->view->invoice_start = $invoice_settings[$userid]['invoice_start'];
					$this->view->invoice_pay_days = $invoice_settings[$userid]['invoice_pay_days'];
				}

// 				$user = Doctrine::getTable('User')->find($userid);
				$this->retainValues($user->toArray());
				$userarray = $user->toArray();
				
				$clientid = $userarray['clientid'];
				
				if(strlen($_POST['title']) > 0)
				{
					$this->view->title = $_POST['title'];
				}
				else
				{
					$this->view->title = $userarray['title'];
				}

				if(strlen($_POST['groupname']) > 0)
				{
					$this->view->groupid = $_POST['groupname'];
				}
				else
				{
					$this->view->groupid = $userarray['groupid'];
				}
// 			}
// 			else
// 			{
// 				$client = Doctrine_Query::create()
// 					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
// 					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
// 					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
// 					->from('Client')
// 					->where('id= ?', $clientid);
// 				$clientexec = $client->execute();
// 				$clientarray = $clientexec->toArray();
// 				$this->view->client_name = $clientarray[0]['client_name'];
// 			}

			if($logininfo->usertype == 'SA')
			{
// 				if($_GET['id'] > 0)
// 				{
					$usergrp = Doctrine_Query::create()
						->select('*')
						->from('Usergroup')
						->where('clientid= ?', $clientid)
						->andWhere('isdelete= ?',0);
					$usergrpexec = $usergrp->execute();
					$grouparray = $usergrpexec->toArray();
					$group = array("0" => "");
					foreach($grouparray as $key => $val)
					{
						$group[$val['id']] = $val['groupname'];
						$grouparray_all[$val['id']] = $val;
					}
					$this->view->grouparray = $group;
					$this->view->grouparray_all = $grouparray_all;
// 				}
				$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
					->from('Client')
					->where('id = ?', $clientid);

				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
// 				if($_GET['id'] > 0)
// 				{
					$this->view->client_name = $clientarray[0]['client_name'];
// 					$this->view->inputbox = '<label id="client_name_readonly" for="client_name" >' . $this->view->translate('client_name') . '</label><input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly" class="w400"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" /><br />';
					
					$this->view->inputbox = '<div class="dlabel">'
					    .' <label for="client_name">' . $this->view->translate('client_name') . '</label>'
				        .'</div>'
					    . '<div class="dinput">'
				        . $clientarray[0]['client_name']
				        . '<input name="client_name" type="hidden" value="' . $clientarray[0]['client_name'] . '" >'
			            . '<input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />'
		                . '</div>';
					
// 				}
			}
			else
			{

				$usergrp = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->where('clientid= ?', $logininfo->clientid)
					->andWhere('isdelete= ?',0);
				$usergrpexec = $usergrp->execute();
				$grouparray = $usergrpexec->toArray();
				$group = array("0" => "");
				foreach($grouparray as $key => $val)
				{
					$group[$val['id']] = $val['groupname'];
					$grouparray_all[$val['id']] = $val;
				}
				$this->view->grouparray = $group;
				$this->view->grouparray_all = $grouparray_all;
				
				$clientarray = Client::getClientDataByid($logininfo->clientid);
				
				$this->view->inputbox = '<div class="dlabel"><label for="client_name">' . $this->view->translate('client_name') . '</label></div>'
				    . '<div class="dinput">'
			        . $clientarray[0]['client_name']
	                . '</div>';
				 
			}
			
// 			if($_GET['id'] > 0) {
// 				$cid = $_GET['id'];
// 				$user = Doctrine::getTable('User')->find($cid);
// 				$this->retainValues($user->toArray());
// 				$userarray = $user->toArray();
			     $userarray= $user->toArray();
			     $this->retainValues($userarray);
				
				//ISPC-2018
				if($userarray['patient_file_tag_rights'] != '')
				{
					$pat_file_tags_rights_arr = explode(',',$userarray['patient_file_tag_rights']);
				}
				
				if($pat_file_tags_rights_arr)
				{
					$this->view->pat_file_tags_rights = $pat_file_tags_rights_arr;
				}
				else 
				{
					$this->view->pat_file_tags_rights = null;
				}
				
				//ISPC-2162	
				if ($userid == $this->logininfo->userid && $this->logininfo->usertype == 'SA') {
					$saved_receipt_profiles = ReceiptPrintSettingsTable::findAllClientReceiptPrintSettings($logininfo->clientid);
				}
				else {
					$saved_receipt_profiles = ReceiptPrintSettingsTable::findAllClientReceiptPrintSettings($clientid);
				}
				
				//var_dump($saved_receipt_profiles); exit;
				if($saved_receipt_profiles)
				{
					foreach($saved_receipt_profiles as $kr => $vr)
					{
						$client_receipt_profiles[$vr['id']] = $vr['profile_name'];
					}
					//var_dump($client_receipt_profiles); exit;
					//$this->view->client_receipt_profiles = $client_receipt_profiles;
						
					$printsettings_form = new Application_Form_ClientPrintSettings(array(
							'_block_name'           => null,
							'_client_receipt_profiles' => $client_receipt_profiles,
								
					));
					
					
					$receipt_print_settings_arr = $userarray['receipt_print_settings'];
					
					
					$this->view->receipt_print_settings_form = $printsettings_form->create_form_userselectreceiptprintsettings($receipt_print_settings_arr);
				}
				
				//ISPC-2239
				if ($user->usertype != "SA") {
				    
				    $getTopParentMenus = Menus::getTopParentMenus(null, null, $user);
    				$getLeftParentMenus = Menus::getLeftParentMenus(null, $user);
    				$getAllLeftSubMenus = Menus::getAllLeftSubMenus($user);
    				
    				
//     				dd($getAllLeftSubMenus);
    				
    				$this->view->all_navigation_menus = [
    				    "TopParentMenus"    => $getTopParentMenus,
    				    "LeftParentMenus"   => $getLeftParentMenus,
    				    "LeftSubMenus"      => $getAllLeftSubMenus,
    				];
    				
    				$default_topmenu = array_column($getTopParentMenus, 'id'); 
    				
				}	
				
				
				//ISPC-1835
				$ust = new UserSettings();
				$ustarr = $ust->getUserSettings($userid);
				
				$ustarr["__defaults"] = [
				    'patient_contactphone' => $ust->defaults_patient_contactphone(),
				    'group_allowed_icons' => 'this is set below, in his own var',
				    
				    'topmenu' => [
				        'topmenu_max_checked' => count($default_topmenu),
				        'Menus' => $default_topmenu,
				        'TabMenus' => [],
				    ]
				];
				
				if (empty($ustarr['topmenu']['Menus'])) {
				    //user cannot have empty menu, we add all
				    $ustarr['topmenu'] = ['Menus' => $default_topmenu];
				}
				
				
// 				$user_settings = array();
// 				$user_settings['id'] = $ustarr['id'];
// 				$user_settings['calendar_visit_color'] = $ustarr['calendar_visit_color'];
// 				$user_settings['calendar_visit_text_color'] = $ustarr['calendar_visit_text_color'];

				$this->view->user_settings = $ustarr;
				
				$stamp_users= $userarray['default_stampusers'];
				$stamp_id= $userarray['default_stampid'];
				
				if(empty($stamp_id))
				{
				
				}
					
				if(!empty($stamp_users) && !empty($stamp_id))
				{
					$stemp = new UserStamp();
					$stemp_detail = $stemp->getUserStampById($stamp_users, $stamp_id);
					//print_r($stemp_detail);exit;
					$stemp_form1 = "\n".$stemp_detail[0]['row1']."\n".$stemp_detail[0]['row2']."\n".$stemp_detail[0]['row3']."\n".$stemp_detail[0]['row4']."\n".$stemp_detail[0]['row5']."\n".$stemp_detail[0]['row6']."\n".$stemp_detail[0]['row7'];
				
				}elseif (!empty($stamp_users))
				{
					$stemp = new UserStamp();
					$stemp_detail = $stemp->getLastUserStamp($stamp_users);
					//print_r($stemp_detail);exit;
					$stemp_form1 = "\n".$stemp_detail[0]['row1']."\n".$stemp_detail[0]['row2']."\n".$stemp_detail[0]['row3']."\n".$stemp_detail[0]['row4']."\n".$stemp_detail[0]['row5']."\n".$stemp_detail[0]['row6']."\n".$stemp_detail[0]['row7'];
				}
				
				$this->view->stamp_form1 = $stemp_form1;
				
				$client_user_array = User::get_client_users($clientid, 1);
					
				$this->view->client_user_array = $client_user_array;
					
				$associated_users = UsersAssociation::get_associated_user($userid);
				$this->view->associated_user = $associated_users[$userid];
				
				
				//=================stampel===================================
				/* --------------------Check for MultipleArzstemple----------------------------- */
					
				$multiplestamps_option = ! empty($clientModules) && $clientModules[64] ? true : false;
				
				$this->view->multiplestamps_option = $multiplestamps_option;
					
				if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')
				{
					$isadmin = '1';
				}
				if($isadmin == 1)
				{
					$showselect = 1;
				}
				else
				{
					$showselect = 1; // show select to all
				}
					
				$this->view->showselect = $showselect;
				
    			$users = new User();
    			$userarray = $users->getUserByClientid($clientid);
    			$userarraylast = array();
    			$userarraylast[] = $this->view->translate('selectuser');
    			$userarraylast_ids = array();
    				
    			foreach($userarray as $user)
    			{
    				$userarraylast[$user['id']] = trim($user['user_title']) . " " . trim($user['last_name']) . ", " . trim($user['first_name']);
    				$userarraylast_ids[] = $user['id'];
    			
    			
    			}
    			$this->view->users = $userarraylast;
    				
    				
    			$ustamp = new UserStamp();
    			$multipleuser_stamp = $ustamp->getAllUsersActiveStamps($userarraylast_ids);
    			
    			foreach($multipleuser_stamp as $k=>$sd){
    				$u_stamps[$sd['userid']][] = $sd;
    			}
    				
    			//false checkModulePrivileges("64", $clientid)) => multiplestamps_option 	
    			foreach($userarray as $user)
    			{
    			    // append to select only the users that have stamps defined in user/userstamplist
    			    if ( ! isset($u_stamps[$user['id']])) 
    			        continue; 
    			    
    				$last_stamps[$user['id']] = end($u_stamps[$user['id']]);
    				$last_stamps[$user['id']]['username'] = trim($user['user_title']) . " " . trim($user['last_name']) . ", " . trim($user['first_name']);
    			}
    			$this->view->stamp_data = $last_stamps;
    				
    			//true checkModulePrivileges("64", $clientid)) => multiplestamps_option 	
    			foreach($multipleuser_stamp as $ks => $uspamp)
    			{
    				$users_mstamps[$uspamp['userid']]['user_id'] = $uspamp['userid'];
    				$users_mstamps[$uspamp['userid']]['user_name'] = $userarraylast[$uspamp['userid']];
    				$users_mstamps[$uspamp['userid']]['user_stamps'][$uspamp['id']] = $uspamp['stamp_name'];
//     				$users_mstamps[$uspamp['userid']]['user_stampss'][$uspamp['id']] = $uspamp['stamp_name'];
    			}
    			$this->view->users_mstamps = $users_mstamps;
				
				
				
				
    			if ( ! empty($clientModules) && $clientModules[175]) { 
    				//get elVi user settings
    				$elviUser = ElviUsersTable::getInstance()
    				->findOneBy('user_id', $userid, Doctrine_Core::HYDRATE_RECORD);
    				
    				$this->view->elvi = null;
    				
    				if ($elviUser) {
    				    
    				    //fetch latest infos about the state.. maybe is pending or denied
        				$elviManager = new ElviService();
        				
        				if ($elviUser->state == "PENDING") {
        				
        				    $elviManager->_user_info($elviUser, $userEntity);
        				
        				    if ($elviUser->state == "ACCEPTED") {
        				
        				        $elviManager->_group_addMember();
        				        $elviManager->_group_addViewer();
        				    }
        				}
        				
        				$this->view->elvi = $elviUser->toArray();
        				
    				}
    				
				}
				
				
				
				/*
				 * ISPC-2138
				 * 2) add a section to the USERS SETTINGS (https://www.ispc-login.de/user/editprofile) where all available icons are listed.
				 * let the user select which icons he wants to HIDE. (means all are actively selected and user can unselect them)
				 *
				 * @author claudiu on 01.02.2018
				 * this setting will be for non-SA users (SA belongs to groupid=0)
				 */
					
				$group_allowed_icons = GroupIconsDefaultPermissions::getDetailedInfo($userid);
				$this->view->group_allowed_icons = $group_allowed_icons;
				
				
// 			}

			if ($logininfo->usertype != 'SA') {
				$this->view->readonly = 'readonly="readonly"';
			}
			
			$pseudogroup_of_user = false;
			if ($logininfo->usertype != 'SA' || $userid != $logininfo->userid) {
			    //get also pseudogroup
			    $pseudogroup_of_user = PseudoGroupUsers::get_user_pseudogroup($userid);
			    
			    

			    if ( ! empty($clientModules) && $clientModules[155]) {
			        //fetch offline app users + devices
			         
			        $offline_user = UserOffline::fetch_user($userid, $clientid);
			        $offline_devices = CertifiedDevices::fetch_devices($userid, $clientid);

			        $this->view->offline_settings = ["UserOffline" => $offline_user, "CertifiedDevices" => $offline_devices];
			    }
			    
			}
			$this->view->pseudogroup_of_user = $pseudogroup_of_user;
			
		
			
			
			if($this->getRequest()->isPost() && $validate_post_failed)
			{
			    $this->retainValues($_POST);
			    $this->view->title = trim($_POST['title']);
			    $this->view->groupid = $_POST['groupname'];
			    
			    $errors = $client_form->getErrorMessages();
			    if (! empty($errors)) {
			        array_unshift($errors, $this->translate('message_info_err'));
			        $this->view->error_message = implode("<br/>", $errors);
			    }
			}
			
		}

		
		/**
		 * @author claudiu on 05.02.2018 :
		 * editprofile == edituser($loginfo->userid), so i moved it there     
		 * editprofileAction forwards to self::edituserAction 
		 * future edits to the profile must be done there
		 * 
		 * @cla on 08.10.2018
		 * we piggyback this, so we can access elviroomAction, and we do not add other rights to elviroomAction  
		 * 
		 * @deprecated
		 */
		public function editprofileAction()
		{

		    $this->forward('edituser', null, null, array(
		        'id'          => $this->logininfo->userid,
		        'clientid'    => $this->logininfo->clientid,
		    ));
		    
		    return;
		    
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$cid = $logininfo->userid;
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($_GET['flg'] == 'esuc')
			{
				$this->view->error_message = $this->view->translate("emailupdatedsucessfully");
			}

			$this->view->act = "user/editprofile";
			$this->view->titlearray = array("" => "", $this->view->translate('mr') => $this->view->translate('mr'), $this->view->translate('mrs') => $this->view->translate('mrs'), $this->view->translate('miss') => $this->view->translate('miss'));

			if($this->getRequest()->isPost())
			{
              
				$client_form = new Application_Form_User();
				if($client_form->editvalidate($_POST))
				{

					$change = $client_form->validateeditProfile($_POST);
					if($change == 'change')
					{
						$this->view->error_message = $this->view->translate("verificaionmailsentonnewmailidrecordupdatesucessfully");
						$this->retainValues($_POST);
						$this->view->title = $_POST['title'];
					}
					else
					{
						$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
						$this->retainValues($_POST);
						$this->view->title = $_POST['title'];
					}

					$user_association_form = new Application_Form_UsersAssociation();
					$user_association_form->add_association($_POST);

					if(($_POST['has_invoice_number'] == "1" && strlen($_POST['invoice_start'])) || strlen($_POST['invoice_pay_days']) > 0)
					{
						$user_invoice_settings_form = new Application_Form_InternalInvoiceSettings();
						$user_invoice_settings_form->insert_invoice_settings($_POST, $clientid, $userid);
					}
				}
				else
				{

					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
					$this->view->title = $_POST['title'];
				}
			}
			$user = Doctrine::getTable('User')->find($userid);
			$this->retainValues($user->toArray());
			$userarray = $user->toArray();

			//ISPC-1835
			$ust = new UserSettings();
			$ustarr = $ust->getUserSettings($userid);
			
			$this->view->user_settings = $ustarr;
// 			if($ustarr)
// 			{
// 				$user_settings['id'] = $ustarr['id'];
// 				$user_settings['calendar_visit_color'] = $ustarr['calendar_visit_color'];
// 				$user_settings['calendar_visit_text_color'] = $ustarr['calendar_visit_text_color'];
			
// 				$this->view->user_settings = $user_settings;
// 			}
// 			else 
// 			{
// 				$this->view->user_settings = null;
// 			//}

			$stamp_users= $userarray['default_stampusers'];
			$stamp_id= $userarray['default_stampid'];
			
			if(empty($stamp_id))
			{
				
			}
			
			if(!empty($stamp_users) && !empty($stamp_id))
			{
				$stemp = new UserStamp();
				$stemp_detail = $stemp->getUserStampById($stamp_users, $stamp_id);
				//print_r($stemp_detail);exit;
				 $stemp_form1 = "\n".$stemp_detail[0]['row1']."\n".$stemp_detail[0]['row2']."\n".$stemp_detail[0]['row3']."\n".$stemp_detail[0]['row4']."\n".$stemp_detail[0]['row5']."\n".$stemp_detail[0]['row6']."\n".$stemp_detail[0]['row7'];
				
			}elseif (!empty($stamp_users))
			{
				$stemp = new UserStamp();
				$stemp_detail = $stemp->getLastUserStamp($stamp_users);
				//print_r($stemp_detail);exit;
				$stemp_form1 = "\n".$stemp_detail[0]['row1']."\n".$stemp_detail[0]['row2']."\n".$stemp_detail[0]['row3']."\n".$stemp_detail[0]['row4']."\n".$stemp_detail[0]['row5']."\n".$stemp_detail[0]['row6']."\n".$stemp_detail[0]['row7'];
			}

			$this->view->stamp_form1 = $stemp_form1;
						
			$client_user_array = User::get_client_users($clientid, 1);

			$this->view->client_user_array = $client_user_array;

			$associated_users = UsersAssociation::get_associated_user($cid);
			$this->view->associated_user = $associated_users[$cid];

			$invoice_settings = InternalInvoiceSettings::getUserInternalInvoiceSettings($userid, $clientid);
			if($invoice_settings)
			{
				$this->view->invoice_prefix = $invoice_settings[$userid]['invoice_prefix'];
				$this->view->invoice_start = $invoice_settings[$userid]['invoice_start'];
				$this->view->invoice_pay_days = $invoice_settings[$userid]['invoice_pay_days'];
			}

			$clientid = $userarray['clientid'];
			if(strlen($_POST['title']) > 0)
			{
				$this->view->title = $_POST['title'];
			}
			else
			{
				$this->view->title = $userarray['title'];
			}
			$groupid = $userarray['groupid'];

			if($logininfo->usertype != 'SA')
			{
				$this->view->readonly = 'readonly="readonly"';
				$this->view->userinput = "&nbsp;&nbsp;" . $this->view->username;
				$grouparray = array();
				if ($usergrp = Doctrine::getTable('Usergroup')->find($groupid)) {
				    //ISPC-790 - user duplicate -  if parent-GROUP does not exist in the target, your new user has groupid=0
    				$grouparray = $usergrp->toArray();
				}
				$this->view->groupname = "&nbsp;&nbsp;" . $grouparray['groupname'];

				$this->view->groupbox = '<div class="dlabel"><label for="groupname">' . $this->view->translate('usergroup') . '</label></div><div class="dinput">' . $this->view->groupname . '</div><br />';
			}
			else
			{
				$this->view->userinput = '<input type="text" name="username" id="username"  value="' . $this->view->username . '" >';
				$this->view->groupbox = '<div class="dlabel"><label for="groupname">' . $this->view->translate('usergroup') . '</label></div>
			<div class="dinput"><input name="groupname" id="groupname" type="text" value="' . $this->view->groupname . '" /></div>';
			}


			setcookie("openmenu", "test", "", "/", "www.ispc-login.de");
			
			//=================stampel===================================
			/* --------------------Check for MultipleArzstemple----------------------------- */
			$multiplestamps_previleges = new Modules();
			
			if($multiplestamps_previleges->checkModulePrivileges("64", $logininfo->clientid))
			{
				$multiplestamps_option = true;
			}
			else
			{
				$multiplestamps_option = false;
			}
			
			$this->view->multiplestamps_option = $multiplestamps_option;
			
			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')
			{
				$isadmin = '1';
			}
			if($isadmin == 1)
			{
				$showselect = 1;
			}
			else
			{
				$showselect = 1; // show select to all
			}
			
			$this->view->showselect = $showselect;
			
			$users = new User();
			$userarray = $users->getUserByClientid($logininfo->clientid);
			$userarraylast[] = $this->view->translate('selectuser');
			$userarraylast_ids = array();
			
			foreach($userarray as $user)
			{
				$userarraylast[$user['id']] = trim($user['user_title']) . " " . trim($user['last_name']) . ", " . trim($user['first_name']);
				$userarraylast_ids[] = $user['id'];
				
				
			}
			$this->view->users = $userarraylast;
			
			
			$ustamp = new UserStamp();
			$multipleuser_stamp = $ustamp->getAllUsersActiveStamps($userarraylast_ids);
		
			foreach($multipleuser_stamp as $k=>$sd){
				$u_stamps[$sd['userid']][] = $sd;
			}
			
			
			foreach($userarray as $user)
			{
				$last_stamps[$user['id']] = end($u_stamps[$user['id']]);
				$last_stamps[$user['id']]['username'] = trim($user['user_title']) . " " . trim($user['last_name']) . ", " . trim($user['first_name']);
			}
			
			$this->view->stamp_data = $last_stamps;
			//print_r($this->view->stamp_data); exit;
			
			foreach($multipleuser_stamp as $ks => $uspamp)
			{
				$users_mstamps[$uspamp['userid']]['user_id'] = $uspamp['userid'];
				$users_mstamps[$uspamp['userid']]['user_name'] = $userarraylast[$uspamp['userid']];
				$users_mstamps[$uspamp['userid']]['user_stamps'][$uspamp['id']] = $uspamp['stamp_name'];
				$users_mstamps[$uspamp['userid']]['user_stampss'][$uspamp['id']] = $uspamp['stamp_name'];

				
				
			}
			
 			//print_r($stamp_data); exit;
			$this->view->users_mstamps = $users_mstamps;
			
			
			
			/*
			 * ISPC-2138
			 * 2) add a section to the USERS SETTINGS (https://www.ispc-login.de/user/editprofile) where all available icons are listed. 
			 * let the user select which icons he wants to HIDE. (means all are actively selected and user can unselect them)
			 * 
			 * @author claudiu on 01.02.2018
			 * this setting will be for non-SA (sa belongs to groupid=0)
			 */
            if($this->logininfo->usertype != 'SA' && $this->logininfo->groupid > 0) {
                $group_allowed_icons = GroupIconsDefaultPermissions::getDetailedInfo();
                
                $this->view->group_allowed_icons = $group_allowed_icons;
                
            }
		}

		public function listuserAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('User', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function listuserstampAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			$multiplestamps_previleges = new Modules();
			if($multiplestamps_previleges->checkModulePrivileges("64", $clientid))
			{
				$this->view->multiplestamps_option = true;
			}
			else
			{
				$this->view->multiplestamps_option = false;
			}

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('User', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function listonlineuserAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('User', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function fetchonlinelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$columnarray = array("pk" => "id", "un" => "username", "pwd" => "password", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray['ASC'];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where(" isdelete = 0 and logintime > DATE_SUB('" . date("Y-m-d H:i:s") . "',INTERVAL 30 MINUTE)")
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$userexec = $user->execute();
			$userarray = $userexec->toArray();

			$limit = 500;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_GET['pgno'] * $limit);

			$userlimitexec = $user->execute();
			$usserlimit = $userlimitexec->toArray();


			$clnt = new Client();
			$clientdata = $clnt->getClientData();
			foreach($clientdata as $ku => $cv)
			{
				$client_details[$cv['id']]['client_name'] = $cv['client_name'];
			}
			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "listonlineuser.html");
			$grid->client_details = $client_details;
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("onlineusernavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchonlinelist.html');
			echo json_encode($response);
			exit;
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_REQUEST['id'] > 0)
			{
				$clientid = $_REQUEST['id'];
			}
			else
			{
				$clientid = $logininfo->clientid;
			}


			
			$columnarray = array("pk" => "id", "un" => "username", "ut" => "user_title","pwd" => "password", "fn" => "first_name", "ln" => "last_name");

			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
			    $search_text = addslashes(strtolower(trim($_REQUEST['val'])));
			} else {
			    $search_text="";
			}
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			$this->view->order = $orderarray['ASC'];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('id != ?', $logininfo->userid)
				->andWhere('usertype != ?', 'SA');
				if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
				{
				    if($search_text =="admin"){
				        $user->andWhere('isadmin = 1');
				    } else{
				        
    				    $user->andWhere("trim(lower( CONCAT( first_name 
    			        , id , '_' 
    			        , last_name , '_'  
    			        , username , '_' 
    			        , emailid , '_'  
    			        , zip , '_' 
    			        , city , '_'  
    			        , street1 , '_'  
    			        , phone , '_' ))) like ? " , "%". $search_text ."%");
				    }
				}
				
				if(strlen($columnarray[$_REQUEST['clm']]) > 0 && strlen($_REQUEST['ord']) > 0 )
				{
    				$user->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
				} 
				else 
				{ 
    				$user->orderBy('username ASC');
				}
				
			$userexec = $user->execute();
			$userarray = $userexec->toArray();

			$limit = 50;
			$user->select('*');
			
			$user->limit($limit);
			$user->offset($_REQUEST['pgno'] * $limit);

			$userlimitexec = $user->execute();
			$usserlimit = $userlimitexec->toArray();


			foreach($usserlimit as $k => $us_details)
			{
				$users_ids[] = $us_details['id'];
			}
			$connceted_clients = Usersa2Client::getusersaclients_multiple($users_ids);
            
            if(empty($users_ids)){
                $users_ids[] = "99999999999";
            }

			$query = Doctrine_Query::create()
			->select('count(*),user_id')
			->from('UserDefaultPermissions')
			->whereIn('user_id',$users_ids)
			->andWhere('clientid = ?', $logininfo->clientid)
			->groupBy('user_id');	
			$rights_user = $query->fetchArray();
			
			//print_r($rights_user); exit;
			
			foreach ($rights_user as $key_r => $val_r)
			{
				$count_r[$val_r['user_id']] =$val_r['count']; 
			}
				
			foreach($users_ids as $user){
				if($count_r[$user])
				{
					$predefined_rights[$user] = '1';
				} 
				else
				{
					$predefined_rights[$user] = '0';
				}
			}
			
			//print_r($predefined_rights); exit;
			
			
			$multiplestamps_previleges = new Modules();
			if($multiplestamps_previleges->checkModulePrivileges("243", $clientid))
			{
			    $this->view->multiplestamps_option = true;
			}
			else
			{
			    $this->view->multiplestamps_option = false;
			}
			
			
			
			$this->view->right_user = $predefined_rights;
			
			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "listuser.html");
			$grid->connceted_clients = $connceted_clients;
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("usernavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function fetchstamplistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['id'] > 0)
			{
				$clientid = $_GET['id'];
			}
			else
			{
				$clientid = $logininfo->clientid;
			}


			$multiplestamps_previleges = new Modules();
			if($multiplestamps_previleges->checkModulePrivileges("64", $clientid))
			{
				$this->view->multiplestamps_option = true;
			}
			else
			{
				$this->view->multiplestamps_option = false;
			}

			$columnarray = array("pk" => "id", "un" => "username", "pwd" => "password", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('usertype != ?', 'SA')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$userexec = $user->execute();
			$userarray = $userexec->toArray();

			$limit = 50;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_GET['pgno'] * $limit);

			$userlimitexec = $user->execute();
			$usserlimit = $userlimitexec->toArray();

			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "listuserstamp.html");
			$this->view->usergrid = $grid->renderGrid();
// 		$this->view->navigation = $grid->dotnavigation("usernavigation.html", 5, $_GET['pgno'], $limit);
			$this->view->navigation = $grid->dotnavigation("userstampnavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchstamplist.html');
			echo json_encode($response);
			exit;
		}

		public function getjsondataAction()
		{
			if($_GET['cid'] > 0)
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('clientid= ? ', $_GET['cid'])
					->andWhere('isdelete= ?', 0);

				$track = $user->execute();
				echo json_encode($track->toArray());
				exit;
			}
			else
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('isdelete = ?', 0)
					->andWhere('usertype != ?', 'SA');
				$track = $user->execute();
				echo json_encode($track->toArray());
				exit;
			}
		}
		
		public function getjsondatav2Action()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
			if (!$this->getRequest()->isXmlHttpRequest()) {
        		die('!isXmlHttpRequest');
        	}
        	
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$final = User::get_all_visiting_users_and_groups( $clientid );
			
			echo json_encode($final);
			exit;
			
		}

		public function userprevilegesnew150701Action()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
					
				if($clientid > 0)
				{
					$group_form = new Application_Form_UserDefault();
					$group_form->InsertData($_POST);
				}

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "user/listuser");
			}


			$this->view->theclientid = $clientid;

			$user = Doctrine::getTable("User")->find($_GET['id']);
			$userarray = $user->toArray();
			$clintid = $userarray['clientid'];
			$this->view->username = $userarray['username'];
			$clgrp = Doctrine_Query::create()
				->select('*')
				->from('MenuClient')
				->andWhere('clientid = ?', $logininfo->clientid);
			$perms = $clgrp->fetchArray();

			$comma = ",";
			$menustr .="999999999";
			foreach($perms as $val)
			{
				$menustr .= $comma . $val['menu_id'];
				$comma = ",";
			}

			$onlyadmin = Doctrine_Query::create()
				->select('x.id')
				->from('Menus x')
				->where('x.foradmin=1 OR x.forsuperadmin=1')
				->andWhere('isdelete=0');

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus m')
				->where('m.isdelete = "0"')
				->andWhere("m.id in (" . $menustr . ")")
				->andWhere('m.isdelete = 0')
				->andWhere('m.forsuperadmin = 0')
				->andWhere('m.foradmin = 0 and m.parent_id not in (' . $onlyadmin->getDql() . ')')
				->orderBy('m.sortorder ASC');
			$trk = $fdoc->fetchArray();

			foreach($trk as $menu_item)
			{
				$menuz[$menu_item['id']] = $menu_item;
			}

			foreach($menuz as $menu_item)
			{
				if($menu_item['parent_id'] == 0)
				{
					$realsort = $menu_item['sortorder'];
				}
				elseif($menu_item['top_position'] == 1 && $menu_item['left_position'] != 1)
				{
					$realsort = 99999 + $menu_item['sortorder_top'] / 100;
				}
				else
				{
					$realsort = $menuz[$menu_item['parent_id']]['sortorder'] + $menu_item['sortorder'] / 100;
				}

				$menu_item['realsort'] = $realsort;
				$sortbyarray[] = $realsort;
				$sortablemenus[$menu_item['id']] = $menu_item;
			}

			array_multisort($sortbyarray, SORT_ASC, $sortablemenus);


			$this->view->permisions = $sortablemenus;

			$query = Doctrine_Query::create()
				->select('*')
				->from('UserDefaultPermissions')
				->where('user_id= ?', $_GET['id'])
				->andWhere('menu_id IN ( ' . $menustr . ' )')
				->andWhere('clientid = ?', $logininfo->clientid);

			$prearray = $query->fetchArray();

			if(count($prearray) > 0)
			{
				foreach($prearray as $menup)
				{
					$menuarray[$menup['menu_id']] = $menup;
				}
			}
			else
			{ //get user group permisions
				//get master group from user group
				$gmaster = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->where('clientid = "' . $logininfo->clientid . '"')
					->andWhere('id = "' . $userarray['groupid'] . '"')
					->andWhere('isdelete = 0');

				$mastergrArray = $gmaster->fetchArray();


				//get group permisions by mastergroupid
				$q = Doctrine_Query::create()
					->select('*')
					->from('GroupDefaultPermissions')
					->where('master_group_id= ?', $mastergrArray[0]['groupmaster'])
					->andWhere('menu_id IN ( ' . $menustr . ' )')
					->andWhere('clientid = ?', $logininfo->clientid);
				$gPermsArray = $q->fetchArray();

				foreach($gPermsArray as $menup)
				{
					$menuarray[$menup['menu_id']] = $menup;
				}
			}
			$this->view->menuarray = $menuarray;


			$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
				,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
				->from("Client")
				->where('id= ?', $userarray['clientid']);
			$clientarray = $client->fetchArray();
			$this->view->client_name = $clientarray[0]['client_name'];
		}

		public function userprevilegesnewAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($this->getRequest()->isPost())
			{
				
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
					if($clientid > 0)
					{
						if($_POST['status'] == 'reset')
						{
							
							$group_form = new Application_Form_UserDefault();
							$group_form->resetData($_POST);
							
						}
						else {
							$group_form = new Application_Form_UserDefault();
							$group_form->InsertData($_POST);
						}
						
					}
				
				

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "user/listuser");
			}


			$this->view->theclientid = $clientid;

			
			$user = Doctrine::getTable("User")->find($_GET['id']);
			$userarray = $user->toArray();
			$clintid = $userarray['clientid'];
			$this->view->username = $userarray['username'];
			
						
			$clgrp = Doctrine_Query::create()
				->select('*')
				->from('MenuClient')
				->andWhere('clientid = ?', $logininfo->clientid);
			$perms = $clgrp->fetchArray();

			
			$comma = ",";
			$menustr .="999999999";
			foreach($perms as $val)
			{
				$menustr .= $comma . $val['menu_id'];
				$comma = ",";
			}

			$onlyadmin = Doctrine_Query::create()
				->select('x.id')
				->from('Menus x')
				->where('x.forsuperadmin=1')
				->andWhere('isdelete=0');

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus m')
				->where('m.isdelete = "0"')
				->andWhere("m.id in (" . $menustr . ")")
				->andWhere('m.isdelete = 0')
				->andWhere('m.forsuperadmin = 0')
				->andWhere('m.parent_id not in (' . $onlyadmin->getDql() . ')')
				->orderBy('m.sortorder ASC');
			$trk = $fdoc->fetchArray();

		foreach($trk as $menu_item)
			{
				$menuz[$menu_item['id']] = $menu_item;

				if($menu_item['parent_id'] == 0 && $menu_item['top_position'] == '0'){
					
					$parent_menus[$menu_item['id']] = $menu_item;
				}
			}
			foreach($menuz as $menu_item)
			{
				if($menu_item['left_position'] == "1"){
	
					if($menu_item['parent_id'] != 0){
						$left_menus[$menu_item['parent_id']][$menu_item['id']] = $menu_item;
					}
				} else{
					$top_menus[0][$menu_item['id']] = $menu_item;
				} 
				
			}
			
		$this->view->parent_menus = $parent_menus ;
		$this->view->left_menus = $left_menus ;
		$this->view->top_menus = $top_menus ;
		$this->view->menus = $menuz ;

			$query = Doctrine_Query::create()
				->select('*')
				->from('UserDefaultPermissions')
				->where('user_id= ?', $_GET['id'])
				->andWhere('menu_id IN ( ' . $menustr . ' )')
				->andWhere('clientid = "' . $logininfo->clientid . '"');

			$prearray = $query->fetchArray();

			if(count($prearray) > 0)
			{
				foreach($prearray as $menup)
				{
					$menuarray[$menup['menu_id']] = $menup;
				}
			}
			else
			{ //get user group permisions
				//get master group from user group
				$gmaster = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->where('clientid = "' . $logininfo->clientid . '"')
					->andWhere('id = "' . $userarray['groupid'] . '"')
					->andWhere('isdelete = 0');

				$mastergrArray = $gmaster->fetchArray();

				//get group permisions by mastergroupid
				$q = Doctrine_Query::create()
					->select('*')
					->from('GroupDefaultPermissions')
					->where('master_group_id= ?', $mastergrArray[0]['groupmaster'])
					->andWhere('menu_id IN ( ' . $menustr . ' )')
					->andWhere('clientid = "' . $logininfo->clientid . '"');
				$gPermsArray = $q->fetchArray();

				foreach($gPermsArray as $menup)
				{
					$menuarray[$menup['menu_id']] = $menup;
				}
			}
			$this->view->navarray = $menuarray;


			$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
				,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
				->from("Client")
				->where('id=' . $userarray['clientid']);
			$clientarray = $client->fetchArray();
			$this->view->client_name = $clientarray[0]['client_name'];
			
			
			//============================================
			$query = Doctrine_Query::create()
			->select('count(*),user_id')
			->from('UserDefaultPermissions')
			->whereIn('user_id',$_GET['id'])
			->andWhere('clientid = "' . $logininfo->clientid . '"')
			->groupBy('user_id');
			$rights_user = $query->fetchArray();
			if(!empty($rights_user))
			{
				$this->view->reset = "1";
			}else{
				$this->view->reset = "0";
			}
			
		    //============================
			
		}

		public function userprevilegesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');

				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}

				if($_POST['copyuserid'] > 0)
				{
					$user_form = new Application_Form_Userprevileges();
					$user_form->CopypermissionData($_POST);
				}
				else
				{

					$user_form = new Application_Form_Userprevileges();
					$user_form->InsertData($_POST);
				}
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}

			$user = Doctrine::getTable("User")->find($_GET['id']);
			$userarray = $user->toArray();
			$clintid = $userarray['clientid'];
			$this->view->username = $userarray['username'];

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isdelete = 0')
				->andWhere('id = ' . $logininfo->userid)
				->andWhere("usertype != 'SA'");
			$track = $user->execute();

			if(count($track->toArray()) < 1)
			{
				$clntmod = Doctrine_Query::create()
					->select('*')
					->from('ClientModules')
					->where('canaccess =1 and clientid = ' . $clintid);
				$clntmodexec = $clntmod->execute();
				$comma = "";
				$modid = "'0'";

				foreach($clntmodexec->toArray() as $key => $val)
				{

					$modid .=$comma . "'" . $val['moduleid'] . "'";
					$comma = ",";
				}

				$mod = Doctrine_Query::create()
					->select('*')
					->from('Modules')
					->where('id in (' . $modid . ')')
					->andWhere('isdelete = 0');

				$modexec = $mod->execute();
				$this->view->modarray = $modexec->toArray();
			}
			else
			{

				$clntmod = Doctrine_Query::create()
					->select('*')
					->from('ClientModules')
					->where('canaccess =1 and clientid = ' . $clintid);

				$clntmodexec = $clntmod->execute();
				$comma = "";
				$modid = "'0'";

				foreach($clntmodexec->toArray() as $key => $val)
				{
					$comma = ",";
					$modid .=$comma . "'" . $val['moduleid'] . "'";
				}

				$mod = Doctrine_Query::create()
					->select('*')
					->from('Modules')
					->where('id in (' . $modid . ')')
					->andWhere('isdelete = 0');

				$modexec = $mod->execute();
				$this->view->modarray = $modexec->toArray();
			}

			$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
				,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
				->from("Client")
				->where('id=' . $userarray['clientid']);
			$clientexec = $client->execute();
			$clientarray = $clientexec->toArray();
			$this->view->clientid = $clientarray[0]['id'];
			$this->view->client_name = $clientarray[0]['client_name'];


			$copyuser = Doctrine::getTable("User")->findBy('clientid', $userarray['clientid']);
			$cpuserarray = $copyuser->toArray();
			$userid = array("" => "");
			foreach($cpuserarray as $key => $val)
			{
				$userid[$val['id']] = $val['username'];
			}
			$this->view->copyuserarray = $userid;
		}

		public function getmodulejsonAction()
		{
			$mod = Doctrine::getTable('Modules')->findAll();

			echo json_encode($mod->toArray());
			exit;
		}

		private function retainValues($values, $prefix = '')
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

		public function deleteuserAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			if($this->getRequest()->isPost())
			{
				if(count($_POST['user_id']) < 1)
				{
					$this->view->error_message = $this->view->translate("selectatlestone");
					$error = 1;
				}

				if($error == 0)
				{
					if($logininfo->usertype == 'SA')
					{
						foreach($_POST['user_id'] as $key => $val)
						{

							$mod = Doctrine::getTable('User')->find($val);
							$mod->isdelete = 1;
							$mod->save();
						}

						$this->view->error_message = $this->view->translate('userdeletedsuccessfully');
					}
					else
					{
						foreach($_POST['user_id'] as $key => $val)
						{
							if($logininfo->usertype == 'CA')
							{
								$where = "id=" . $val . " and clientid=" . $logininfo->clientid;
							}
							else
							{
								$where = "id=" . $val . " and clientid=" . $logininfo->clientid . " and usertype!='CA'";
							}

							$query = Doctrine_Query::create()
								->select('*')
								->from('User')
								->where($where);

							$previlege = $query->execute();
							if($previlege->toArray())
							{
								$mod = Doctrine::getTable('User')->find($val);
								$mod->isdelete = 1;
								$mod->save();

								$this->view->error_message = $this->view->translate("userdeletedsuccessfully");
							}
							else
							{
								$this->_redirect(APP_BASE . "error/previlege");
							}
						}
					}
				}
			}

			$this->_helper->viewRenderer('listuser');
		}

		public function verifyAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(strlen($_GET['id']) > 0)
			{
				$urlid = base64_decode($_GET['id']);

				$temp = Doctrine_Query::create()
					->select("*,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid")
					->from('TempUser')
					->where('id = ' . $urlid);
				$tempexec = $temp->execute();
				if($tempexec)
				{
					$temparray = $tempexec->toArray();
				}

				if(count($temparray[0]) > 0)
				{
					$userupdate = Doctrine::getTable('User')->find($temparray[0]['userid']);
					$userupdate->emailid = $temparray[0]['emailid'];
					$userupdate->save();

					$user = Doctrine::getTable('User')->find($temparray[0]['userid']);
					$userarray = $user->toArray();

					$message = '<label>Name : ' . $userarray['last_name'] . ', ' . $userarray['first_name'] . '</label><br>
				<label>Emailid : ' . $userarray['emailid'] . '</label><br>';

					$mail = new Zend_Mail();
					$mail->setBodyHtml($message)
						->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
						->addTo(ISPC_ERRORMAILTO, ISPC_ERRORSENDERNAME)
						->setSubject("Update mail")
						->send();

					$doc = Doctrine_Query::create()
						->delete('TempUser')
						->where('id = ?', base64_decode($_GET['id']) );
					$doc->execute();
					$this->view->error_message = $this->view->translate('emailupdatedsucessfully');
					$this->_redirect(APP_BASE . 'user/edituser?flg=esuc');
				}
				else
				{
					$this->view->error_message = $this->view->translate("errorwhileupdatingemailid");
				}
			}
		}

		public function refreshsessionAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			echo json_encode(1);
			exit;
		}

		public function activeusersAction()
		{
			$this->_helper->viewRenderer('listuser');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($_REQUEST['id'] > 0)
			{			
				$user = Doctrine::getTable('User')->findbyIdAndClientid( (int)$_REQUEST['id'] , $logininfo->clientid );
				$user = $user{0};
				if ( isset($user->id) && (int)$user->id > 1){
					if($_REQUEST['flg'] == 'ina')
					{
						$user->isactive = 1;
					}
					else
					{
					
						$user->isactive = 0;
					}	
					
					$dateObject = new Zend_Date($_REQUEST['isactive_date'], 'dd.MM.yyyy');		
					$user->isactive_date = $dateObject->get('YYYY-MM-dd');
					
					$user->save();
				}	
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . "user/listuser?flg=suc");
			}
		}

		public function userstampaddAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$clientid = $logininfo->clientid;
			$multiplestamps_previleges = new Modules();

			if($multiplestamps_previleges->checkModulePrivileges("64", $clientid))
			{
				$multiplestamps_option = true;
			}
			else
			{
				$multiplestamps_option = false;
			}

			$this->view->multiplestamps_option = $multiplestamps_option;


			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA")
			{
				$userid = $_GET['userid'];
				if($userid < 1)
				{
					$this->_redirect(APP_BASE . 'user/listuserstamp');
				}

				$uc = Pms_CommonData::getUserClient($userid, $logininfo->clientid);
				if(!$uc)
				{
					$this->_redirect(APP_BASE . 'user/listuserstamp');
				}
			}



			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA")
			{
			    $userid = $_GET['userid'];
			    if($userid < 1)
			    {
			        $this->_redirect(APP_BASE . 'user/listuserstamp');
			    }
			    
			    $uc = Pms_CommonData::getUserClient($userid, $logininfo->clientid);
			    if(!$uc)
			    {
			        $this->_redirect(APP_BASE . 'user/listuserstamp');
			    }
			}
			else
			{
				$userid = $logininfo->userid;
				if($multiplestamps_option === true && $_REQUEST['multiple'] != 1)
				{
					$this->_redirect(APP_BASE . 'user/userstamplist');
				}
			}

			if($multiplestamps_option === true && $_REQUEST['multiple'] == 1)
			{

				if($this->getRequest()->isPost())
				{
					$a_post = $_POST;

					$a_post['userid'] = $userid;
					$userform = new Application_Form_UserStamp();

					if($userform->validate($a_post))
					{
						$userform->InsertDataMultiple($a_post);
						$this->_redirect(APP_BASE . "user/userstamplist?userid=" . $_REQUEST['userid']);
					}
					else
					{
						$userform->assignErrorMessages();
						$this->retainValues($_POST);
					}
				}
			}
			else
			{
				if($this->getRequest()->isPost())
				{
					$a_post = $_POST;
					$a_post['userid'] = $userid;
					$userform = new Application_Form_UserStamp();
					$userform->InsertData($a_post);

					if($logininfo->usertype == "SA")
					{
						$this->_redirect(APP_BASE . "user/listuserstamp");
					}
				}
				$st = new UserStamp();
				$stamparr = $st->getLastUserStamp($userid);

				$this->retainValues($stamparr[0]);
			}
		}

		public function userstampeditAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$multiplestamps_previleges = new Modules();

			if($multiplestamps_previleges->checkModulePrivileges("64", $clientid))
			{
				$multiplestamps_option = true;
			}
			else
			{
				$multiplestamps_option = false;
			}

			$this->view->multiplestamps_option = $multiplestamps_option;


			$stamp_id = $_REQUEST['stampid'];



			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA")
			{
				$userid = $_REQUEST['userid'];
				if($userid < 1)
				{
					$this->_redirect(APP_BASE . 'user/listuserstamp');
				}

				$uc = Pms_CommonData::getUserClient($userid, $logininfo->clientid);
				if(!$uc)
				{
					$this->_redirect(APP_BASE . 'user/listuserstamp');
				}
			}

			if($logininfo->usertype == "SA"  || $logininfo->usertype == "CA")
			{
				$userid = $_REQUEST['userid'];
				if($userid < 1)
				{
				    $this->_redirect(APP_BASE . 'user/listuserstamp');
				}
				
				$uc = Pms_CommonData::getUserClient($userid, $logininfo->clientid);
				if(!$uc)
				{
				    $this->_redirect(APP_BASE . 'user/listuserstamp');
				}
			}
			else
			{
				$userid = $logininfo->userid;
			}

			if($this->getRequest()->isPost())
			{
				$a_post = $_POST;

				$a_post['userid'] = $userid;
				$a_post['stamp_id'] = $stamp_id;
				$userform = new Application_Form_UserStamp();

				if($userform->validate($a_post))
				{

					$userform->UpdateDataStamp($a_post);
					$this->_redirect(APP_BASE . "user/userstamplist?userid=" . $_REQUEST['userid']);
				}
				else
				{
					$userform->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			$st = new UserStamp();
			$stamparr = $st->getUserStampById($userid, $stamp_id);

			$this->retainValues($stamparr[0]);
		}

		public function userstamplistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA")
			{
				$userid = $_REQUEST['userid'];
				if($userid < 1)
				{
				    $this->_redirect(APP_BASE . 'user/listuserstamp');
				}
				
				$uc = Pms_CommonData::getUserClient($userid, $logininfo->clientid);
				if(!$uc)
				{
				    $this->_redirect(APP_BASE . 'user/listuserstamp');
				}
			}
			else
			{
				$userid = $logininfo->userid;
			}

			$st = new UserStamp();
			$stamparr = $st->getAllActiveUserStamp($userid);

			$this->view->user_stamps = $stamparr;


			if($this->getRequest()->isPost())
			{
				if($_POST['delete_id'] > 0 && !empty($_POST['delete_id']))
				{
					$drop = Doctrine_Query::create()
						->update('UserStamp')
						->set('isdelete', '1')
						->where('userid= ?', $userid)
						->andWhere('id= ?', $_POST['delete_id']);
					$dropexec = $drop->execute();

					$this->_redirect(APP_BASE . 'user/userstamplist?userid=' . $_REQUEST['userid']);
				}
			}
		}

		public function messagecenterAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$cid = $logininfo->userid;

			if($_GET['flg'] == 'esuc')
			{
				$this->view->error_message = $this->view->translate("emailupdatedsucessfully");
			}

			$modules = new Modules();
			if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
			{
			    $acknowledge = "1";
			    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
			     
			    if(in_array($cid,$approval_users)){
			        $this->view->approval_rights = "1";
			    }
			    else
			    {
			        $this->view->approval_rights = "0";
			    }
			}
			else
			{
			    $acknowledge = "0";
			}
			$this->view->acknowledge = $acknowledge;
				
			
			if($modules->checkModulePrivileges("143", $clientid)) 
			{
                $scheduled_medications = "1";
                			     
			} else {
			    
                $scheduled_medications = "0";			     
			}
	        $this->view->scheduled_medications = $scheduled_medications;
			
	        $this->view->ModulePrivileges = $modules->get_client_modules($clientid);
	        //150 = MEDICATION :: Rezept-Anforderung 
	        //156 = Dashboard :: Grouped actions by patient 
			
			
			
			if($this->getRequest()->isPost())
			{
				$user_form = new Application_Form_User();
				$change = $user_form->update_notification_settings($_POST);
			}

			$notifications = new Notifications();
			$user = Doctrine::getTable('User')->find($cid);

			$userarray = $user->toArray();
			$this->retainValues($userarray);


			$notifications_settings = $notifications->get_notification_settings($cid);
			$this->view->notification_enum_options = array("none" => $this->view->translate("None"), "assigned" => $this->view->translate("Assigned"), "all" => $this->view->translate("All"));
			if(count($notifications_settings[$cid]) != '0')
			{
				$this->view->notifications = $userarray['notification'];
				$this->view->admission = $notifications_settings[$cid]['admission'];
				$this->view->discharge = $notifications_settings[$cid]['discharge'];
				$this->view->sixweeks = $notifications_settings[$cid]['sixweeks'];
				$this->view->fourwnote = $notifications_settings[$cid]['fourwnote'];
				$this->view->krise = $notifications_settings[$cid]['krise'];
				$this->view->wlvollversorgung = $notifications_settings[$cid]['wlvollversorgung'];
				$this->view->wlvollversorgung_25days = $notifications_settings[$cid]['wlvollversorgung_25days'];
				$this->view->sapv_enabled = $notifications_settings[$cid]['sapv_enabled'];
				$this->view->sapv_popup = $notifications_settings[$cid]['sapv_popup'];
				$this->view->sapv_noinf_enabled = $notifications_settings[$cid]['sapv_noinf_enabled']; //ISPC - 2125 - alerts if a verordnung is after XX days still in mode Keine Angabe
				$this->view->sapv_noinf_popup = $notifications_settings[$cid]['sapv_noinf_popup']; //ISPC - 2125 - alerts if a verordnung is after XX days still in mode Keine Angabe
				$this->view->dashboard_display_patbirthday = $notifications_settings[$cid]['dashboard_display_patbirthday'];
				$this->view->medication_acknowledge = $notifications_settings[$cid]['medication_acknowledge'];
				$this->view->todo = $notifications_settings[$cid]['todo'];
				$this->view->medication_interval = $notifications_settings[$cid]['medication_interval'];
				$this->view->medication_doctor_receipt = $notifications_settings[$cid]['medication_doctor_receipt'];
				$this->view->dashboard_grouped= $notifications_settings[$cid]['dashboard_grouped'];
				// Maria:: Migration ISPC to CISPC 08.08.2020
				$this->view->mePatient_device_uploads= $notifications_settings[$cid]['mePatient_device_uploads'];// ISPC-2432
				$this->view->patient_hospital_admission = $notifications_settings[$cid]['patient_hospital_admission'];//ISPC-1547
				$this->view->patient_hospital_discharge = $notifications_settings[$cid]['patient_hospital_discharge'];//ISPC-1547
				
			}
			else
			{
				$this->view->notifications = '1';
				$this->view->admission = 'none';
				$this->view->discharge = 'none';
				$this->view->sixweeks = 'none';
				$this->view->fourwnote = 'none';
				$this->view->krise = 'none';
				$this->view->wlvollversorgung = 'none';
				$this->view->wlvollversorgung_25days = 'all';
				$this->view->sapv_enabled = '0'; //default 0
				$this->view->sapv_popup = '';
				$this->view->sapv_noinf_enabled = '0'; //default 0 ISPC - 2125 - alerts if a verordnung is after XX days still in mode Keine Angabe
				$this->view->sapv_noinf_popup = ''; //ISPC - 2125 - alerts if a verordnung is after XX days still in mode Keine Angabe
				$this->view->dashboard_display_patbirthday = 'none';
				$this->view->medication_acknowledge = 'none';
				$this->view->todo = 'none';
				$this->view->medication_interval = 'none';
				$this->view->medication_doctor_receipt = 'none';
				$this->view->dashboard_grouped = '0'; //default 0
				$this->view->mePatient_device_uploads = 'none';//ISPC-2432
				$this->view->patient_hospital_admission = 'none';//ISPC-1547
				$this->view->patient_hospital_discharge = 'none';//ISPC-1547
			}
		}

		public function vacationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$hidemagic = Zend_Registry::get('hidemagic');
			$clientid = $logininfo->clientid;
			$usertype = $logininfo->usertype;

			$users = new User();
			$vacations = new UserVacations();


			if(($usertype == 'SA' || $usertype == 'CA') && $_REQUEST['u_id'])
			{
				$userid = trim(rtrim($_REQUEST['u_id']));
			}
			else
			{
				$userid = $logininfo->userid;
			}

			if($_REQUEST['mode'] == 'del')
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				if(!empty($_REQUEST['v_id']))
				{
					$vacation = $_REQUEST['v_id'];

					$vacations_form = new Application_Form_Vacations();
					$del_vacation = $vacations_form->delete_vacation($vacation);

					echo json_encode($del_vacation);
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
				
				$vacations_form = new Application_Form_Vacations();

				$validate = $vacations_form->validate_timeline($_POST);
				$overlapped = $vacations_form->is_overlapped($userid, $_POST['start_date'], $_POST['end_date']);


				$post = $_POST;
				$post['userid'] = $userid;
				if(!$overlapped && $validate)
				{
					$save_vacation = $vacations_form->save_vacation($post);

					if($save_vacation)
					{
						//redirect to the replacements page!
						$this->_redirect(APP_BASE . "user/vacationreplacements?v_id=" . $save_vacation . "&u_id=" . $userid);
					}
				}
			}



			$user_vacations = $vacations->get_user_vacations($userid, false);

			foreach($user_vacations as $k_vacation => $v_vacation)
			{

				if(empty($vacation_days_arr))
				{
					$vacation_days_arr = array();
				}
				$vacation_days_arr = array_merge($vacation_days_arr, PatientMaster::getDaysInBetween(date('Y-m-d', strtotime($v_vacation['start'])), date('Y-m-d', strtotime($v_vacation['end']))));
			}

			array_walk($vacation_days_arr, function(&$value) {
				$value = date('d.m.Y', strtotime($value));
			});

			//dummy value
			$vacation_days_arr[] = '01.01.1970';

			$this->view->json_vacation_days = json_encode($vacation_days_arr);
			$this->view->user_vacations = $user_vacations;

			$user_details = $users->getUserDetails($userid);
			$this->view->current_username = $user_details[0]['username'];
		}

		public function vacationreplacementsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$clientid = $logininfo->clientid;
			$usertype = $logininfo->usertype;

			$uv = new UserVacations();
			$repls = new VacationsReplacements();


			if(($usertype == 'SA' || $usertype == 'CA') && $_REQUEST['u_id'])
			{
				$userid = trim(rtrim($_REQUEST['u_id']));
				$vacation = $_REQUEST['v_id'];
			}
			else
			{
				$userid = $logininfo->userid;
				$vacation = $_REQUEST['v_id'];
			}
			$this->view->userid = $userid;
			//get vacation details
			$vacation_details = $uv->get_vacation_details($vacation, $userid);

			if($vacation_details === false)
			{
				//vacationid, userid wrong combination
				//prepare redirect to the page of actual user
				if($usertype != 'CA' && $usertype != 'SA')
				{
					//SA, CA can browse other users vacations
					$this->_redirect(APP_BASE . 'user/vacation?u_id=' . $logininfo->userid);
				}
				else
				{
					$this->_redirect(APP_BASE . 'team/teamlist'); //client admin goes back to pick a user from list
				}
			}

			//get current user details
			$user_det = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('id=' . $userid);
			$user_array = $user_det->fetchArray();



			//get rest of users from current user group
			$groupid = $user_array[0]['groupid'];

			$gr_users = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('groupid = "' . $groupid . '"')
				->andwhere('isdelete = 0')
				->andWhere('clientid = "' . $clientid . '"')
				->orderBy('last_name ASC');
			$group_users = $gr_users->fetchArray();

			$users_gr_ids[] = '999999999999';
			foreach($group_users as $k_gr_u => $v_gr_u)
			{

				$users_gr_ids[] = $v_gr_u['id'];

				if($v_gr_u['id'] == $userid) //set current user in another var
				{
					$current_user_details = $v_gr_u;
				}
			}

			//get assigned patients
			$sql = "*,e.ipid,e.epid_num,e.epid,birthd,admission_date,change_date,last_update,p.id as patientid,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
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
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as gensex";

			if($usertype == 'SA')
			{
				$sql = "*,e.ipid,e.epid_num,e.epid as epid,p.isadminvisible,p.id as patientid,";
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
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as gensex, ";
			}


	/* 		$qpa = Doctrine_Query::create()
				->select($sql)
				->from('PatientQpaMapping q')
				->where("q.clientid='" . $clientid . "'")
				->andWhere("q.userid='" . $userid . "'")
				->andWhere('e.epid != ""')
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isdischarged = "0"')
				->andWhere('p.isstandbydelete="0"')
				->leftJoin('q.EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->orderby('e.epid_num ASC');
			$assigned_pats = $qpa->fetchArray();

			foreach($assigned_pats as $k_a_pat => $v_a_pat)
			{
				$assigned_patients[$v_a_pat['patientid']] = $v_a_pat;
				$assigned_patients[$v_a_pat['patientid']]['ipid'] = $v_a_pat['EpidIpidMapping']['ipid'];

				$pat_ipid_id[$v_a_pat['EpidIpidMapping']['ipid']] = $v_a_pat['patientid'];
			}
			$this->view->user_assigned_patients = $assigned_patients;

			 */

			
			$qpad = Doctrine_Query::create()
			->select($sql)
			->from('PatientQpaMapping q')
			->where("q.clientid= ?", $clientid)
			->andWhere("q.userid=?",$userid)
			->andWhere('e.epid != ""')
			->andWhere('p.isdelete = "0"')
			->leftJoin('q.EpidIpidMapping e')
			->leftJoin('e.PatientMaster p')
			->orderby('e.epid_num ASC');
			$assigned_patsd = $qpad->fetchArray();
				
			$epid2user  =  array();
			$ipids =  array();
			foreach($assigned_patsd as $k=>$ask){
			    $epid2user[$ask['userid']][] = $ask['epid'];
			    $ipids[] = $ask['EpidIpidMapping']['ipid'];
			}
				
			if(!empty($ipids)){
    			//patient days
    			$conditions['client'] = $clientid;
    			$conditions['ipids'] = $ipids;
    			$conditions['periods'][0]['start'] =  date('Y-m-d',strtotime($vacation_details[0]['start']));
    			$conditions['periods'][0]['end'] = date('Y-m-d',strtotime($vacation_details[0]['end']));
    				
    			$sqls = 'e.epid, p.ipid, e.ipid,';
    			$sqls .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    			$sqls .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    			$sqls .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    			$sqls .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    			$sqls .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    			$sqls .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    				
    			//be aware of date d.m.Y format here
    			$active_patients = array();
    			$active_patients = Pms_CommonData::patients_days($conditions, $sqls);
    			
    			$assigned_patients  = array();
    			foreach($active_patients as $ipid => $pat_data){
    			    $assigned_patients[$pat_data['details']['id']] = $pat_data['details'];
    			    $assigned_patients[$pat_data['details']['id']] = $pat_data['details'];
    			    $assigned_patients[$pat_data['details']['id']]['ipid'] = $pat_data['details']['ipid'];
    			    $assigned_patients[$pat_data['details']['id']]['patientid'] = $pat_data['details']['id'];
    			    $pat_ipid_id[$pat_data['details']['ipid']] = $pat_data['details']['id'];
    			}
			}
				
			$this->view->user_assigned_patients = $assigned_patients;
			

			//setting up replacements select values
			$vacation_users = $uv->get_vacation_users($vacation_details[0]['start'], $vacation_details[0]['end'], $users_gr_ids);

			$user_replacements['0'] = $this->view->translate('no_replacements');
			foreach($group_users as $k_group => $v_group)
			{
				if(!in_array($v_group['id'], $vacation_users) && $v_group['id'] != $userid)
				{
					$user_replacements[$v_group['id']] = $v_group['last_name'] . ", " . $v_group['first_name'];
				}
			}

			$this->view->user_replacements = $user_replacements;

			//set current user details
			$this->view->current_username = $current_user_details['username'];
			$this->view->curent_vacation_period_start = date('d.m.Y', strtotime($vacation_details['0']['start']));
			$this->view->curent_vacation_period_end = date('d.m.Y', strtotime($vacation_details['0']['end']));


			if($this->getRequest()->isPost())
			{
				foreach($_POST['replacement'] as $patient_id => $replacement_id)
				{
					$replacements[$assigned_patients[$patient_id]['ipid']] = $replacement_id;
				}

				$vacations_form = new Application_Form_Vacations();

				if($_POST['edit_period'] == '1')
				{
					$vacations_form->edit_period($vacation, $_POST);
				}

				$assign_replacements = $vacations_form->save_vacation_replacements($userid, $vacation, $replacements);
				$this->_redirect(APP_BASE . 'user/vacationreplacements?v_id=' . $vacation . '&u_id=' . $userid);
				exit;
			}


			$usr_repl = $repls->get_user_vacation_replacements($userid, $vacation);

			foreach($usr_repl as $key => $value)
			{
				$replacements_values[$pat_ipid_id[$value['ipid']]] = $value['replacement'];
			}
			ksort($replacements_values);

			$this->view->replacements_values = $replacements_values;

			if($_REQUEST['dbg'])
			{
				print_r($vacation_details); //current user details
				print_r($assigned_patients); //assigned patients to the source user
				print_r($group_users); //select users from same group as source user
				print_r($user_array); //source user


				if($_REQUEST['dbg'] == 'test')
				{
					$current_vacations_replacements = $uv->get_current_vacation_replacements($userid);
					print_r($current_vacations_replacements);
				}
				exit;
			}
		}

		public function usersvacationsAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
				

			$user = Doctrine_Query::create()
			->select('u.*,uv.*,vr.*')
			->from('User as u')
			->where('isdelete = 0')
			->andWhere('clientid = ?', $clientid)
			->leftJoin('u.UserVacations uv')
			->leftJoin('u.VacationsReplacements vr');
			$vacations_array = $user->fetchArray();
			
			foreach($vacations_array as $k=>$usetails){
				$usar[$usetails['id']]['username'] = $usetails['username'];
				$usar[$usetails['id']]['name'] = $usetails['last_name'].', '.$usetails['first_name'];
				if(!empty($usetails['VacationsReplacements'])){
					foreach($usetails['VacationsReplacements'] as $k=>$vrdata){
						$ipids[]	= $vrdata['ipid'];	
					}
				}
			}
			
			$patietns_act_search_q = array();
		 	if(!empty($ipids)){
		 	
				$salt = Zend_Registry::get('salt');
				$query = Doctrine_Query::create()
				->select("p.ipid,
	    				e.epid,
	    				e.ipid,
	    				AES_DECRYPT(p.first_name, '{$salt}') as first_name,
	    				AES_DECRYPT(p.last_name, '{$salt}') as last_name
	    				")
				->from('EpidIpidMapping e')
				->leftJoin('e.PatientMaster p')
				->where('e.ipid = p.ipid')
				->andWhereIn('e.ipid',$ipids)
				->andWhere('e.clientid = ? ', $clientid )
				->andWhere('p.isdelete = "0"');
				$patietns_act_search_q = $query->fetchArray();
				foreach($patietns_act_search_q as $k=>$ep){
					$patient_details[$ep['ipid']] = $ep['epid'].' - '.$ep['last_name'].', '.$ep['first_name'];
				}
		 	}
					
			foreach($vacations_array as $k=>$usetails){
				if(!empty($usetails['UserVacations'])){
					foreach($usetails['UserVacations'] as $k=>$vdata){
						$usar[$usetails['id']]['vacation'][$vdata['id']]['period'] = date("d.m.Y",strtotime($vdata['start'])).'-'.date("d.m.Y",strtotime($vdata['end'])); 
					}
				}
				
				if(!empty($usetails['VacationsReplacements'])){
					foreach($usetails['VacationsReplacements'] as $k=>$vrdata){
						$usar[$usetails['id']]['vacation'][$vrdata['vacation']]['rpl'][$vrdata['id']]['ipid'] = $vrdata['ipid'];
						$usar[$usetails['id']]['vacation'][$vrdata['vacation']]['rpl'][$vrdata['id']]['patient'] = $patient_details[$vrdata['ipid']];
						$usar[$usetails['id']]['vacation'][$vrdata['vacation']]['rpl'][$vrdata['id']]['replacement'] = $vrdata['replacement'];
						$usar[$usetails['id']]['vacation'][$vrdata['vacation']]['rpl'][$vrdata['id']]['replacement_user'] = $usar[$vrdata['replacement']]['name'];
					}
				}
			}
			
			$this->view->uvr = $usar;
		}
		public function userclientlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$user = new User();
			$umc = new UserMessageClient();
			$umc_form = new Application_Form_UserMessageClient();
			$client = new Client();


			if($_REQUEST['id'])
			{
				$userid = $_REQUEST['id'];
			}
			else
			{
				$this->_redirect(APP_BASE . 'user/listuser');
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
				$post['userid'] = $_REQUEST['id'];

				$save_umc = $umc_form->insert_data($post);
				$this->_redirect(APP_BASE . 'user/userclientlist?id=' . $userid);
			}


			$umc_data = $umc->getUserMessageClientData($userid);
			$this->view->umc_data = $umc_data;
			$user_details = $user->getUserDetails($userid, false);
			if($_REQUEST['dbg'])
			{
				print_r($user_details);
			}
			$this->view->clients = $client->getClientData();

			$this->view->user_details = $user_details;
			$this->view->user_client = $user_details[0]['clientid'];
		}

		public function connectuser2clientAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$uid = $_GET['id'];



			if($uid > 0)
			{

				$user = Doctrine::getTable('User')->find($uid);
				$userarray = $user->toArray();
				$user_details = $userarray;
				$this->view->user_details = $user_details;

				// if user super admin and connected to other clients.
				$connected_admins = Usersa2Client::getusersaclients($user_details['id']);

				if(!empty($connected_admins) && $user_details['issuperclientadmin'] == 1)
				{
					$this->view->old_admin_connection = "1";
				}
				else
				{
					$this->view->old_admin_connection = "0";
				}

				$all_clients_array = Client::getClientData();

				foreach($all_clients_array as $k => $cl_data)
				{
					$clients[$cl_data['id']] = $cl_data['client_name'];
				}
				asort($clients);
				$this->view->clients = $clients;

				$usergrp = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->where('clientid=' . $clientid . ' and isdelete=0');
				$usergrpexec = $usergrp->execute();
				$grouparray = $usergrpexec->toArray();

				$group = array("0" => "");
				foreach($grouparray as $key => $val)
				{
					$group[$val['id']] = $val['groupname'];
					$group_master[$val['id']] = $val['groupmaster'];
				}

				$this->view->grouparray = $group;
				$this->view->group_master_array = $group_master;



				$connected_clients_array = User2Client::getuserclients($uid);

				foreach($connected_clients_array as $k => $ccv)
				{
					$connected_clients[] = $ccv['client'];
				}
				$this->view->connected_clients = $connected_clients;

				// get already duplicated users of master user
				$duplicated_users = User::get_duplicated_users($uid);


				$user_form = new Application_Form_User();
				// POST
				if($this->getRequest()->isPost() && $logininfo->usertype == 'SA')
				{
					$_POST['connected_clients'][] = $user_details['clientid'];
					$removed_clients = array_diff($connected_clients, $_POST['connected_clients']);


					if(!empty($_POST['connected_clients']))
					{

						foreach($_POST['connected_clients'] as $k => $target_client)
						{
							if($target_client != $user_details['clientid'])
							{
								if(!$duplicated_users || empty($duplicated_users[$uid][$target_client]))
								{
									$new_user_id = User::duplicate_user($uid, $target_client, $group_master[$user_details['groupid']]); // DUPLICATE USER
								}
								else
								{
									// reactivate user
									$user_form->reactivate_user($duplicated_users[$uid][$target_client]);
								}
							}

							// add
							if(!in_array($target_client, $connected_clients))
							{
								$user2adm = new User2Client();
								$user2adm->user = $uid;
								$user2adm->client = $target_client;
								$user2adm->save();
							}
						}

						if(!empty($removed_clients))
						{

							foreach($removed_clients as $k => $rem_client_id)
							{

								// 	remove conected clients
								$qa = Doctrine_Query::create()
									->update('User2Client')
									->set('isdelete', "1")
									->where("user = ?", $uid)
									->andWhere("client = ?", $rem_client_id);
								$qa->execute();

								$user_form->deactivate_user($duplicated_users[$uid][$rem_client_id]);
							}
						}
					}

					$this->_redirect(APP_BASE . "user/connectuser2client?id=" . $uid);
				}
			}
		}

		/**
		 * @deprecated see User::edituserAction() ->
		 */
		public function offlineuserAction()
		{
			$offline_user_form = new Application_Form_UserOffline();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;


			$this->view->user_type = $logininfo->usertype == 'SA';

			//see if we have a offline user
			$offline_user_data = UserOffline::get_offline_user($userid, $clientid);

			if($offline_user_data)
			{
				$has_offline_user = '1';
			}
			else
			{
				$has_offline_user = '0';
			}
			$this->view->has_offline_user = $has_offline_user;


			if($has_offline_user)
			{
				$this->view->username = $offline_user_data[0]['username'];
			}
			if($_REQUEST['flg'] == 'suc')
			{
				$this->view->update_text = 1;
			}

			if($this->getRequest()->isPost() && $_POST['submit'])
			{
				$post = $_POST;
				$post['has_offline_user'] = $has_offline_user;

				if($offline_user_form->validate($post))
				{
					$insert = $offline_user_form->insert_offline_user($post);
					$this->redirect(APP_BASE . 'user/offlineuser?flg=suc');
					exit;
				}
				else
				{
					$offline_user_form->assignErrorMessages();
					$this->retainValues($_POST);
					$this->view->update_text = '0';
				}
			}
		}

		public function addpseudouserAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			$users = new User();
			$user_form = new Application_Form_PseudoUser();
			$client = $logininfo->clientid;

			$client_users = $users->getUserByClientid($client, true, false);
			unset($client_users[-1]);

			$this->view->client_users = $client_users;


			if($this->getRequest()->isPost())
			{
				$post = $_POST;

				if($user_form->validate($post))
				{
					$insert = $user_form->insert_user($post);
					$this->redirect(APP_BASE . 'user/listpseudousers?flg=suc');
					exit;
				}
				else
				{
					$user_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function editpseudouserAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			$users = new User();
			$pseudo_users = new PseudoUsers();
			$user_form = new Application_Form_PseudoUser();
			$client = $logininfo->clientid;
			$this->_helper->viewRenderer('addpseudouser');

			if($_REQUEST['id'] > '0' && is_numeric($_REQUEST['id']))
			{
				$client_users = $users->getUserByClientid($client, true, false);
				unset($client_users[-1]);

				$this->view->client_users = $client_users;

				$form_data = $pseudo_users->get_pseudo_user_data($client, $_REQUEST['id']);

				if($form_data)
				{
					$this->retainValues($form_data);
				}

				if($this->getRequest()->isPost())
				{
					$post = $_POST;
					$post['pseudo_user_id'] = $_REQUEST['id'];

					if($user_form->validate($post))
					{
						$insert = $user_form->update_user($post);
						if($insert)
						{
							$this->redirect(APP_BASE . 'user/listpseudousers?flg=suc');
							exit;
						}
						else
						{
							$user_form->assignErrorMessages();
							$this->retainValues($_POST);
						}
					}
					else
					{
						$user_form->assignErrorMessages();
						$this->retainValues($_POST);
					}
				}
			}
		}

		public function listpseudousersAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('PseudoUser', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
			
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->view->has_edit_permissions = 0;
			} else{
				$this->view->has_edit_permissions = 1;
			}
			
			
		}

		public function fetchpseudousersAction()
		{
			$users = new User();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if(!$logininfo->clientid)
			{
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

//			$previleges = new Pms_Acl_Assertion();
//			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');
//
//			if(!$return)
//			{
//				$this->_redirect(APP_BASE . "error/previlege");
//			}


			$client_users = $users->getUserByClientid($clientid, true, false);
			unset($client_users[-1]);

			$this->view->client_users = $client_users;

			$columnarray = array("ulu" => "CONCAT_WS(', ', `last_name`, `first_name`)", "utl" => "title", "ufn" => "first_name", "uln" => "last_name", "usn" => "shortname");
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->{"style" . $_REQUEST['pgno']} = "active";

			$this->view->order = $orderarray['ASC'];

			foreach($columnarray as $k_elem => $v_elem)
			{
				$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
			}
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];


			//get sort order of users START
			if($_REQUEST['clm'] == 'ulu')
			{
				$user = Doctrine_Query::create()
					->select('*, ' . $columnarray[$_REQUEST['clm']])
					->from('User')
					->where('clientid = "' . $clientid . '"')
					->andWhere('isdelete="0"')
					->andWhere('isactive="0"')
					->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
				$users_res = $user->fetchArray();


				if($users_res)
				{
					foreach($users_res as $k_usr_res => $v_usr_res)
					{
						$sorted_users[] = $v_usr_res['id'];
					}
				}
			}
			if(empty($sorted_users))
			{
				$sorted_users[] = "99999999999999999";
			}
			//get sort order of users END

			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('PseudoUsers')
				->where('isdelete = 0')
				->andWhere('client = "' . $clientid . '"');

			if($_REQUEST['clm'] == 'ulu')
			{
				$user->orderBy('FIELD(user, "' . implode('","', $sorted_users) . '")');
			}
			else
			{
				$user->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}

			$userarray = $user->fetchArray();

			$limit = 50;
			$user->select('*');
			$user->limit($limit);
			if($_REQUEST['clm'] == 'ulu')
			{
				$user->orderBy('FIELD(user, "' . implode('","', $sorted_users) . '")');
			}
			else
			{
				$user->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
			$user->offset($_REQUEST['pgno'] * $limit);

			$usserlimit = $user->fetchArray();

			foreach($usserlimit as $k => $us_details)
			{
				$users_ids[] = $us_details['id'];
			}

			if($userarray[0]['count'])
			{
				$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "listpseudousers.html");
				$this->view->pseudousersgrid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("pseudousers_navigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				$this->view->pseudousersgrid = '<tr><td colspan="7">' . $this->view->translate('pseudousers_no_results') . '</td></tr>';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['pseudouserlist'] = $this->view->render('user/fetchpseudousers.html');

			echo json_encode($response);
			exit;
		}

		public function deletepseudouserAction()
		{
			$user_form = new Application_Form_PseudoUser();

			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			if(!$logininfo->clientid)
			{
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$status = "0";
			if(is_numeric($_REQUEST['id']) && $_REQUEST['id'] > '0')
			{
				$user_del = $user_form->delete_pseudo_user($clientid, $_REQUEST['id']);

				if($user_del)
				{
					$status = "1";
				}
				else
				{
					$status = "0";
				}
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "delCallback";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['status'] = $status;

			echo json_encode($response);
			exit;
		}

		public function printuserslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$users_form = new Application_Form_User();

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$post = $_POST;

				$users_form->save_print_users($post);
				$this->redirect(APP_BASE . 'user/printuserslist');
				exit;
			}
		}

		public function fetchprintuserslistAction()
		{
			$added_users = new PrintUsers();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('printusers', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			//active page in nav
			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			//first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
			$this->view->order = $orderarray['ASC'];
			foreach($columnarray as $k_elem => $v_elem)
			{
				$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
			}

			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('usertype != ?', 'SA')
				->andWhere('isactive="0"')
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$userarray = $user->fetchArray();

			$limit = 0;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_REQUEST['pgno'] * $limit);
			$usserlimit = $user->fetchArray();

			$added_users_res = $added_users->get_print_users($clientid);
			
			if(!empty($added_users_res))
			{
				$this->view->selected_users = $added_users_res;
			}
			
			foreach($usserlimit as $k_user => $v_user)
			{
				$users_ids[] = $v_user['id'];
			}
			
			$pusers_assigned = new PrintUsersAssigned();
			$assigned_users = $pusers_assigned->check_assigned_users($clientid, $users_ids);
			
			$this->view->assigned_users = $assigned_users;

			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "printuserslist.html");
			$grid->connceted_clients = $connceted_clients;
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("printusersnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchprintuserslist.html');

			echo json_encode($response);
			exit;
		}
		
		public function faxuserslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$users_form = new Application_Form_User();

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				$post = $_POST;

				$users_form->save_fax_users($post);
				$this->redirect(APP_BASE . 'user/faxuserslist');
				exit;
			}
		}

		public function fetchfaxuserslistAction()
		{
			$added_users = new FaxUsers();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('printusers', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			//active page in nav
			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			//first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
			$this->view->order = $orderarray['ASC'];
			foreach($columnarray as $k_elem => $v_elem)
			{
				$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
			}

			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('usertype != ?', 'SA')
				->andWhere('isactive="0"')
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$userarray = $user->fetchArray();

			$limit = 0;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_REQUEST['pgno'] * $limit);
			$usserlimit = $user->fetchArray();

			$added_users_res = $added_users->get_fax_users($clientid);
			
			if(!empty($added_users_res))
			{
				$this->view->selected_users = $added_users_res;
			}
			
			foreach($usserlimit as $k_user => $v_user)
			{
				$users_ids[] = $v_user['id'];
			}
			
			$fusers_assigned = new FaxUsersAssigned();
			$assigned_users = $fusers_assigned->check_assigned_users($clientid, $users_ids);
			
			$this->view->assigned_users = $assigned_users;

			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "faxuserslist.html");
			$grid->connceted_clients = $connceted_clients;
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("faxusersnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchfaxuserslist.html');

			echo json_encode($response);
			exit;
		}
		
		
		public function shinternaluserslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$users_form = new Application_Form_User();

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				$post = $_POST;
				$users_form->save_sh_internal_users($post);
				$this->redirect(APP_BASE . 'user/shinternaluserslist');
				exit;
			}
		}
		
		public function fetchshinternaluserslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('printusers', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			//active page in nav
			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			//first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
			$this->view->order = $orderarray['ASC'];
			foreach($columnarray as $k_elem => $v_elem)
			{
				$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
			}

			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('usertype != ?', 'SA')
				->andWhere('isactive="0"')
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$userarray = $user->fetchArray();

			$limit = 0;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_REQUEST['pgno'] * $limit);
			$usserlimit = $user->fetchArray();

			$get_selected_users = ShInternalUsers::get_shinternal_users($clientid);

			if(!empty($get_selected_users))
			{
				$this->view->selected_users = $get_selected_users;
			}
			
			foreach($usserlimit as $k_user => $v_user)
			{
				$users_ids[] = $v_user['id'];
			}

			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "printshinternaluserslist.html");
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("printusersnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchshinternaluserslist.html');

			echo json_encode($response);
			exit;
		}
		
		
		// ISPC-2257
		public function shshiftsinternaluserslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$users_form = new Application_Form_User();

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				$post = $_POST;
				$users_form->save_sh_shifts_internal_users($post);
				$this->redirect(APP_BASE . 'user/shshiftsinternaluserslist');
				exit;
			}
		}
		
		public function fetchshshiftsinternaluserslistAction()
		{
			// Maria:: Migration ISPC to CISPC 08.08.2020
			//ISPC-2510 carmen 26.02.2020 add group/mastergroup in list
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('printusers', $logininfo->userid, 'canview');

			$groupms = new GroupMaster();
			$groupmaster = $groupms->getGroupMaster();
			$groupmaster_ids = array_keys($groupmaster);
			
			$usergroup = new Usergroup();
			$clientGroups = array();
			$clgr_arr = $usergroup->getUserGroups($groupmaster_ids, $clientid);

			foreach($clgr_arr as $kr => $vr)
			{
				$vr['mastergroup_name'] = $groupmaster[$vr['groupmaster']];
				$clientGroups[$vr['id']] = $vr;
			}
			
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name", 'gn' => 'group_mastergroup');

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			//active page in nav
			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			//first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
			$this->view->order = $orderarray['ASC'];
			foreach($columnarray as $k_elem => $v_elem)
			{
				$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
			}

			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('usertype != ?', 'SA')
				->andWhere('isactive="0"');
			if($_REQUEST['clm'] != 'gn')
			{
				$user->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			}
			$userarray = $user->fetchArray();

			$limit = 0;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_REQUEST['pgno'] * $limit);
			$usserlimit = $user->fetchArray();
			
			$userwithgroupandmastergroup = $usserlimit;
			foreach($userwithgroupandmastergroup as $kr => $vr)
			{
				if($vr['groupid'] != '0')
				{
					$usserlimit[$kr]['group_mastergroup'] = $clientGroups[$vr['groupid']]['groupname'] . "(" . $clientGroups[$vr['groupid']]['mastergroup_name'] . ")";
				}
				else 
				{
					$usserlimit[$kr]['group_mastergroup'] = "";
				}
			}
			
			if($_REQUEST['clm'] == 'gn')
			{
				if($orderarray[$_REQUEST['ord']] == 'ASC')
				{
					array_multisort(array_column($usserlimit, 'group_mastergroup'), SORT_ASC, $usserlimit);
				}
				else
				{
					array_multisort(array_column($usserlimit, 'group_mastergroup'), SORT_DESC, $usserlimit);
				}
			}
			
			$get_selected_users = ShShiftsInternalUsers::get_shinternal_users($clientid);

			if(!empty($get_selected_users))
			{
				$this->view->selected_users = $get_selected_users;
			}
			
			foreach($usserlimit as $k_user => $v_user)
			{
				$users_ids[] = $v_user['id'];
			}

			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "printshshiftsinternaluserslist.html");
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("printusersnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('user/fetchshshiftsinternaluserslist.html');

			echo json_encode($response);
			exit;
		}
		
    
    /* #################################### */

    public function savetablesettingsAction()
        {// save columns
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

    
        //$tab = 0; // this neeeds to be changed
        $tab = !empty($_REQUEST['tab']) ? (int)$_REQUEST['tab'] : 0; 
        $page = $_REQUEST['page'];
        $column = $_REQUEST['column'];
        $visible = $_REQUEST['visible'] == 'false' ? 'no' : 'yes';
    
        //find previous state
        $prev_info = UserTableSettings::user_saved_settings($userid,$page,$column, $tab);
        
        
        if($prev_info){
            
            $uts = Doctrine_Query::create()
            ->update("UserTableSettings")
            ->set('visible','?',$visible)
            ->where('user  = ?', $userid)
            ->andwhere('client  = ?', $clientid)
            ->andWhere('page = ?', $page)
            ->andWhere('column_id = ?', $column)
            ->andWhere('tab = ?', $tab);
            $uts_arr = $uts->execute();
            
        } else {
            // insert
            $save_column = new UserTableSettings();
            $save_column->user = $userid;
            $save_column->client = $clientid;
            $save_column->page = $page;
            $save_column->tab = $tab;
            $save_column->column_id = $column;
            $save_column->visible = $visible;
            $save_column->save();
            
        }
        
        echo 1;
    }
    
    
    public function savetablesettingsorderAction()
    {// save columns
    $this->_helper->viewRenderer->setNoRender();
    $this->_helper->layout->setLayout('layout_ajax');
    
    $logininfo = new Zend_Session_Namespace('Login_Info');
    $clientid = $logininfo->clientid;
    $userid = $logininfo->userid;
    
    
    //$tab = 0; // this neeeds to be changed
    $tab = !empty($_REQUEST['tab']) ? (int)$_REQUEST['tab'] : 0;
    $page = $_REQUEST['page'];
    $columns_array= json_decode($_REQUEST['columns'],true);
    
    
    if(!empty($columns_array)){
        
 
        foreach($columns_array as $o=>$values){
            $column = $values['c'];
            $column_order = $values['o'];
            $prev_info = UserTableSettings::user_saved_settings($userid,$page,$column, $tab);

            if($prev_info){
                
                $uts = Doctrine_Query::create()
                ->update("UserTableSettings")
                ->set('column_order','?',$column_order)
                ->where('user  = ?', $userid)
                ->andwhere('client  = ?', $clientid)
                ->andWhere('page = ?', $page)
                ->andWhere('column_id = ?', $column)
                ->andWhere('tab = ?', $tab);
                $uts_arr = $uts->execute();
            } 
            else 
            {
                 
                
                
                // insert
                $save_column = new UserTableSettings();
                $save_column->user = $userid;
                $save_column->client = $clientid;
                $save_column->page = $page;
                $save_column->tab = $tab;
                $save_column->column_id = $column;
                $save_column->column_order = $column_order;
                if($page == "voluntaryworkers" && $column == 4){
                    $save_column->visible = "no";
                } else {
                    $save_column->visible = "yes";
                }
                $save_column->save();
            }
        }
    }
    
    echo 1;
    }
 
    public function savetablesettingslengthAction()
    {// save search length
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    
	    
	    //$tab = 0; // this neeeds to be changed
	    $tab = !empty($_REQUEST['tab']) ? (int)$_REQUEST['tab'] : 0;
	    $page = $_REQUEST['page'];
	    $length = $_REQUEST['length'];    
	    
	    if(!empty($length)){
	    
	   		$prev_info = UserPageResults :: get_page_result($userid, $clientid, $page, $tab);
	    
	    	if($prev_info){
	    
	    			$uts = Doctrine_Query::create()
	    			->update("UserPageResults")
	    			->set('results','?', $length)
	    			->where('user  = ?', $userid)
	    			->andwhere('client  = ?', $clientid)
	    			->andWhere('page = ?', $page)
	    			->andWhere('tab = ?', $tab);
	    			$uts_arr = $uts->execute();
	    	}
	    	else
	    	{
		    	// insert
	   			$save_column = new UserPageResults();
	   			$save_column->user = $userid;
	   			$save_column->client = $clientid;
	   			$save_column->page = $page;
	   			$save_column->tab = $tab;
	   			$save_column->results = $length;
	   			$save_column->save();
	   		}
	   }
    
    echo 1;
    }
    

    

    public function loadtablepreferencesAction()
    {
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    
	    
        $page = $_REQUEST['page'];
        $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $columns = $_REQUEST['columns'];
        
        $load_sorting = false;
        $col_data = true;
    
        if ( isset($_REQUEST['load_sorting']) && $_REQUEST['load_sorting'] == "yes" )
        {
            $load_sorting = true;
        }
 
    
//         $data['time'] = time();
        $data['time'] = "'".time()."'";
        $data['length'] = "10";
        $data['start'] = "0";
    
        
        for( $i=0; $i<= $columns; $i++ )
        {
//             $data['columns'][] = array( 'visible' => true );
        }
        
 
        if ( $load_sorting )
        {
            // get saved data
            $table_settings = UserTableSorting::user_saved_sorting($userid,$page,$ipid);
             
            
            if( !empty($table_settings) )
            {
                foreach ( $table_settings as $k=>$saved_info )
                {
                    switch( $saved_info['name'] )
                    {
                        case 'length':
                            $data['length'] = (int)$saved_info['value'];
                            break;
                            	
                        case 'order':
                            $data['order'] = unserialize( $saved_info['value'] );
                            break;
                            
                        case 'columns':
                            $data['columns'] = unserialize( $saved_info['value'] );
                            break;
                    }
                }
            }
        }
        else
        {
 
        }
    
        $data['columns'][] = array('visible'=>false);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>false);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>false);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>false);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>false);
        $data['columns'][] = array('visible'=>true);
        $data['columns'][] = array('visible'=>true);
        
         
        
        $return_data = $data;

        echo json_encode( $return_data );
        
    }
    
    
    public function savemedicationsortingAction()
    {// save columns
    $this->_helper->viewRenderer->setNoRender();
    $this->_helper->layout->setLayout('layout_ajax');
    
    $logininfo = new Zend_Session_Namespace('Login_Info');
    $clientid = $logininfo->clientid;
    $userid = $logininfo->userid;
    
    
    if($this->getRequest()->isPost()){
        
//         dd($_REQUEST);
        //$tab = 0; // this neeeds to be changed
        $page = $_REQUEST['page'];
    
        $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
        $ipid = Pms_CommonData::getIpid($decid);
    
        $save_sorting = false;
    
        if(isset($_REQUEST['save_sorting']) && $_REQUEST['save_sorting'] == "yes" )
        {
            $save_sorting = true;
        }
    
        if($save_sorting){
            $prev_info = UserTableSorting::user_saved_sorting($userid,$page,$ipid,"order");
            
            if($_REQUEST['source'] == 'new_medi_page'){
                $extra_info = array();
                $extra_info[0][] =  $_REQUEST['column']; 
                $extra_info[0][] =  $_REQUEST['order']; 
                
                if(!isset($_REQUEST['extra_info'])){
                    $_REQUEST['extra_info'] = $extra_info;
                }
                
                if(isset($_REQUEST['extra_info'])){
                	$_REQUEST['order']['extra_info'] = $_REQUEST['extra_info'];
                }
                $order = array();
                $order[] = $_REQUEST['order']; 
                $order[extra_info] = $extra_info;
                 
                //$val = serialize($_REQUEST['order']);
                $val = serialize($extra_info);
                
            } else {
                
                if(isset($_REQUEST['extra_info'])){
                	$_REQUEST['order']['extra_info'] = $_REQUEST['extra_info'];
                }
    
                $val = serialize($_REQUEST['order']);
            }

            if($prev_info) {
            	
                $uts = Doctrine_Query::create()
                ->update("UserTableSorting")
                ->set("value",'?',$val)
                ->where('user  = ?', $userid)
                ->andwhere('client  = ?', $clientid)
                ->andwhere('ipid = ?', $ipid)
                ->andWhere('page = ?', $page )
                ->andWhere('name = "order"');
                $uts_arr = $uts->execute();
            }
            else
            {
                // insert
                $save_column = new UserTableSorting();
                $save_column->user = $userid;
                $save_column->client = $clientid;
                $save_column->ipid = $ipid;
                $save_column->page = $page;
                $save_column->name = "order";
                $save_column->value = $val;
                $save_column->save();
            }
            
			// Maria:: Migration ISPC to CISPC 08.08.2020
            //ISPC-2636 Lore 29.07.2020
            $client_sett = Doctrine_Query::create()
            ->select("user_overwrite_medi_sort_option")
            ->from('Client')
            ->where('id = ?',  $clientid);
            $client_sett->getSqlQuery();
            $client_settings_overwrite = $client_sett->fetchArray();
            $user_overwrite_medi_sort = $client_settings_overwrite[0]['user_overwrite_medi_sort_option'];
            
            if($user_overwrite_medi_sort == '1'){
                
                $save_usms = new UserSettingsMediSort();
                $save_usms->user_id = $userid;
                $save_usms->clientid = $clientid;
                $save_usms->ipid = $ipid;
                $save_usms->medication_block = $page;
                $save_usms->sort_column = $_REQUEST['column'];
                $save_usms->save();
            }
            //.
            
        }
    }
    
    echo 1;
    }
    
    
    
    
    
    
    
    
    
    
    
    public function savetablepreferencesAction()
    {// save columns
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        

        if($this->getRequest()->isPost()){
            
            //$tab = 0; // this neeeds to be changed
            $page = $_REQUEST['page'];
    
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            $save_sorting = false;

            if(isset($_REQUEST['save_sorting']) && $_REQUEST['save_sorting'] == "yes" )
            {
                $save_sorting = true;
            }
            
            if($save_sorting){
                $prev_info = UserTableSorting::user_saved_sorting($userid,$page,$ipid,"order");
                if($prev_info){
                     $val = serialize($_REQUEST['data']['order']);
                    
                     $uts = Doctrine_Query::create()
                    ->update("UserTableSorting")
                    ->set("value",'?',$val)
                    ->where('user  = ?', $userid)
                    ->andwhere('client  = ?', $clientid)
                    ->andwhere('ipid = ?', $ipid)
                    ->andWhere('page = ?', $page)
                    ->andWhere('name = "order"');
                    $uts_arr = $uts->execute();
                } 
                else
                {
                    // insert
                    $save_column = new UserTableSorting();
                    $save_column->user = $userid;
                    $save_column->client = $clientid;
                    $save_column->ipid = $ipid;
                    $save_column->page = $page;
                    $save_column->name = "order";
                    $save_column->value = serialize($_REQUEST['data']['order']);
                    $save_column->save();
                }
            }
            
            // save columns
            $prev_info_columns = UserTableSorting::user_saved_sorting($userid,$page,$ipid,"columns");
                if($prev_info_columns){
                        $cols = serialize($_REQUEST['data']['columns']);
//                     $uts = Doctrine_Query::create()
//                     ->update("UserTableSorting")
//                     ->set("value")
//                     ->where('user  = "' . $userid . '" ')
//                     ->andwhere('client  = "' . $clientid . '" ')
//                     ->andwhere('ipid = "' . $ipid . '" ')
//                     ->andWhere('page = "' . $page . '"')
//                     ->andWhere('tab = "' . $tab . '"');
//                     $uts_arr = $uts->execute();
                } 
                else
                {
                    // insert
                    $save_column = new UserTableSorting();
                    $save_column->user = $userid;
                    $save_column->client = $clientid;
                    $save_column->ipid = $ipid;
                    $save_column->page = $page;
                    $save_column->name = "columns";
                    $save_column->value = serialize($_REQUEST['data']['columns']);
                    $save_column->save();
                }
            
                
                
                
               
            
        }
        
        echo 1;
    }
    

    public function savetablesortingoldAction()
    {// save columns
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        
        print_r($_REQUEST);exit;
        //$tab = 0; // this neeeds to be changed
        $tab = $_REQUEST['tab'];
        $page = $_REQUEST['page'];
        $column = $_REQUEST['column_id'];
        $column_name = $_REQUEST['column_name'];
        $direction = $_REQUEST['order_direction'];
        
        $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        //find previous state
        $prev_info = UserTableSorting::user_saved_sorting($userid,$page,$column, $tab);
        
        
        if($prev_info){
        // clear data for user and pateint and tab / block

            
            $uts = Doctrine_Query::create()
            ->delete("UserTableSorting")
            ->where('user  = "' . $userid . '" ')
            ->andwhere('client  = "' . $clientid . '" ')
            ->andwhere('ipid = "' . $ipid . '" ')
            ->andWhere('page = "' . $page . '"')
            ->andWhere('tab = "' . $tab . '"');
            $uts_arr = $uts->execute();
        }
    
        // insert
        $save_column = new UserTableSorting();
        $save_column->user = $userid;
        $save_column->client = $clientid;
        $save_column->ipid = $ipid;
        $save_column->page = $page;
        $save_column->tab = $tab;
        $save_column->column_id = $column;
        $save_column->column_name = $column_name;
        $save_column->order_direction = $direction;
        $save_column->save();
    
        
        echo 1;
    }
    
    
    
    public function loadtableprefAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $page = $_REQUEST['page'];
        $tab = !empty($_REQUEST['tab']) ? (int)$_REQUEST['tab'] : 0;
        
        $data['time'] = "'".time()."'";
    
		// Maria:: Migration ISPC to CISPC 08.08.2020		
        $coumns_number["voluntaryworkers"] = 30; //ISPC-2884,Elena,14.04.2021//ISPC-2616 Carmen 03.08.2020
        $coumns_number["dosageformlist"] = 2;
        $coumns_number["medindicationlist"] = 2;
        $coumns_number["medtypelist"] = 2;
        $coumns_number["medunitlist"] = 2;
        $coumns_number["nutritionformularlist"] = 2;
        $coumns_number["btmbuchhistory"] = 6;
        
        if ($page == 'member'){
        	$tabColumns = new MemberColumnslist();
        	$tabCols = $tabColumns->getAllColumns();
			//TODO-3302 Ancuta 03.08.2020 
        	$memberColumnsIds  =array();
        	$memberColumnsIds = array_column($tabCols,'id'); 
			//--
        	$total_rows = count($tabCols) + 2;
        	$coumns_number["member"] = $total_rows;
        }
        
        
        $coumns_number["clientsymptomlist"] = 3;
        $coumns_number["clientsymptomgroupslist"] = 2;
        // ISPC-2160
        $coumns_number["todos"] = 8;
        
        
        for($i = 1; $i <= $coumns_number[$page]; $i++){

            if($page == "voluntaryworkers" && $i == 5){
                $data['columns'][] = array('visible' => false);// 5
            } else{
                $data['columns'][] = array('visible' => true);// 5
            }
            $data['colOrder'][$i-1] = ($i-1);
        }
      
        /* 
        $data['columns'][] = array('visible' => true); //1
        $data['columns'][] = array('visible' => true);// 2
        $data['columns'][] = array('visible' => true);//3
        $data['columns'][] = array('visible' => true);// 4
        
        if($page == "voluntaryworkers"){
            $data['columns'][] = array('visible' => false);// 5
        } else{
            $data['columns'][] = array('visible' => true);// 5
        }
        $data['columns'][] = array('visible' => true);// 6
        $data['columns'][] = array('visible' => true);// 7
        $data['columns'][] = array('visible' => true);// 8
        $data['columns'][] = array('visible' => true);// 9
        $data['columns'][] = array('visible' => true);// 10
        $data['columns'][] = array('visible' => true);// 11
        $data['columns'][] = array('visible' => true);// 12
        $data['columns'][] = array('visible' => true);// 13
        $data['columns'][] = array('visible' => true);// 14
        $data['columns'][] = array('visible' => true);// 15
        $data['columns'][] = array('visible' => true);// 16
        $data['columns'][] = array('visible' => true);// 17
        $data['columns'][] = array('visible' => true);// 18
        $data['columns'][] = array('visible' => true);// 19
        $data['columns'][] = array('visible' => true);// 20
        $data['columns'][] = array('visible' => true);// 21
        $data['columns'][] = array('visible' => true);// 22
        
        $data['columns'][] = array('visible' => true);// 23
        $data['columns'][] = array('visible' => true);// 24 */
        
        
        //find previous state
        $prev_info = UserTableSettings::user_saved_settings($userid, $page, false, $tab);

        //print_r($prev_info);
        if( !empty($prev_info) )
        {
            foreach( $prev_info as $k => $row )
            {
                //TODO-3302 Ancuta 03.08.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
                if($page == 'member'){
                    if(in_array($row['column_id'],$memberColumnsIds)){
                        $data['columns'][ $row['column_id'] ] = array('visible' => $row['visible'] == 'yes' ? true : false);
                        $data['colOrder'][ $row['column_order']] = $row['column_id'];
                    }
                } else{
                    $data['columns'][ $row['column_id'] ] = array('visible' => $row['visible'] == 'yes' ? true : false);
                    $data['colOrder'][ $row['column_order']] = $row['column_id'];
	            }
            }
        }
        elseif($page == 'member'){
        	//set defaults
       	
        	//here the tabs are saved from 1,2,3..., not by starting index = 0
        	$tabsColumnsSource = new MemberColumns2tabs();
        	
        	
        	$allTabsColumns = $tabsColumnsSource->getTabsColumns($tab+1);
        	if(is_array($allTabsColumns)){
        		$allTabsColumns = array_keys($allTabsColumns[$tab+1]);

        		foreach($tabCols as $col){
        			if(!in_array($col['id'] , $allTabsColumns)){
        				$data['columns'][ $col['id'] ] = array('visible' => false); 
        			}
        		}
        	}
        	foreach($data['columns'] as $k=>$v){
        		// insert
        		$save_column = new UserTableSettings();
        		$save_column->user = $userid;
        		$save_column->client = $clientid;
        		$save_column->page = $page;
        		$save_column->tab = $tab;
        		$save_column->column_id = $k;
        		$save_column->visible = $v['visible'] ? 'yes' : 'no' ;
        		$save_column->save();
        	}
        } 
        $data['colOrder'][0] = 0;
        ksort($data['colOrder']);

        if ($page != 'member' && $page != 'todos'){
        	$tab = false;
        }
        $page_results_array =  UserPageResults :: get_page_result($userid,$clientid,$page, $tab);
        if(!empty($page_results_array ))
        {
            foreach($page_results_array as $k=>$res_data){
                $data['length'] = $res_data['results'];
            }
        }
        
        $return_data = $data;
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode( $return_data );
        exit;
 
    }

    
    public function loadtablepreforderAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $page = $_REQUEST['page'];
    
        $data['time'] = "'".time()."'";
    
        $coumns_number["voluntaryworkers"] = 26;
        $coumns_number["dosageformlist"] = 2;
        $coumns_number["medindicationlist"] = 2;
        $coumns_number["medtypelist"] = 2;
        $coumns_number["medunitlist"] = 2;
        $coumns_number["nutritionformularlist"] = 2;
        
        
        for($i = 1; $i <= $coumns_number[$page]; $i++){

            if($page == "voluntaryworkers" && $i == 5){
                $data['columns'][] = array('visible' => false);// 5
            } else{
                $data['columns'][] = array('visible' => true);// 5
            }
        }
      
        //find previous state
        $prev_info = UserTableSettings::user_saved_settings($userid,$page,false,false,true);
    
        if( !empty($prev_info) )
        {
            foreach( $prev_info as $k => $row )
            {
                $data['columns'][ $row['column_id'] ] = array('visible' => $row['visible'] == 'yes' ? true : false);
            }
        } 
        print_R($data['columns']); exit;
        
        $page_results_array =  UserPageResults :: get_page_result($userid,$clientid,$page);
        
        if(!empty($page_results_array ))
        {
            foreach($page_results_array as $k=>$res_data){
                $data['length'] = $res_data['results'];
            }
        }
        
        $return_data = $data;
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode( $return_data );
        exit;
 
    }
    

    public function medchangeuserslistAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $users_form = new Application_Form_User();
    
        if($this->getRequest()->isPost())
        {
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
    
            $post = $_POST;
    
            $users_form->save_med_change_users($post);
            $this->redirect(APP_BASE . 'user/medchangeuserslist');
            exit;
        }
    }
    
    public function fetchmedchangeuserslistAction()
    {
        $added_users = new MedicationChangeUsers();
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
    
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('medicationchange', $logininfo->userid, 'canview');
    
        if(!$return)
        {
            $this->_redirect(APP_BASE . "error/previlege");
        }
    
        $columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");
    
        $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
        //active page in nav
        $this->view->{"style" . $_REQUEST['pgno']} = "active";
        //first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
        $this->view->order = $orderarray['ASC'];
        foreach($columnarray as $k_elem => $v_elem)
        {
            $this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
        }
    
        $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
    
        
        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
        if(empty($approval_users)){
            $approval_users[] ="999999999";
        }
        
        $user = Doctrine_Query::create()
        ->select('count(*)')
        ->from('User')
        ->where('isdelete = 0')
        ->andWhere('clientid = ?', $clientid)
        ->andWhere('usertype != ?', 'SA')
        ->andWhere('isactive="0"')
        ->andWhereNotIn('id',$approval_users)
        ->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
        $userarray = $user->fetchArray();
    
        $limit = 0;
        $user->select('*');
        $user->limit($limit);
        $user->andWhereNotIn('id',$approval_users);
        $user->offset($_REQUEST['pgno'] * $limit);
        $usserlimit = $user->fetchArray();
    
        $added_users_res = $added_users->get_medication_change_users($clientid);
        	
        if(!empty($added_users_res))
        {
            $this->view->selected_users = $added_users_res;
        }
    
        $grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "medchangeuserslist.html");
        $grid->connceted_clients = $connceted_clients;
        $this->view->usergrid = $grid->renderGrid();
        $this->view->navigation = $grid->dotnavigation("medchangeusersnavigation.html", 5, $_REQUEST['pgno'], $limit);
    
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        $response['callBackParameters']['medchangeuserlist'] = $this->view->render('user/fetchmedchangeuserslist.html');
    
        echo json_encode($response);
        exit;
    }
    
    

    public function medapprovaluserslistAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $users_form = new Application_Form_User();
    
        if($this->getRequest()->isPost())
        {
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
    
            $post = $_POST;
    
            $users_form->save_med_approval_users($post);
            $this->redirect(APP_BASE . 'user/medapprovaluserslist');
            exit;
        }
    }
/*
    public function ldapajaxsearchAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
    
        $username=$_GET['username'];
        $return=array();
        if(strlen($username)>1) {
            $ldap = new Net_Ldap();
            $return = $ldap->get_userinfo($username, 1);
        }
        echo (json_encode($return));
    }
    
    public function ldapusermgmtAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;
    
        $ldap=new Net_Ldap();
        $this->view->ldapsearchmode=$ldap->get_ldap_searchmode();
    
        if ($this->getRequest()->isPost())
        {
            //add never used password
            $post=$_POST;
            $post['paswdpassword']=rand(100000000,900000000);
            $post['cfmpassword']=$post['paswdpassword'];
    
            $user_form = new Application_Form_User();
            //double_username_check
            $user_already_exists=true;
            if(strlen($post['username'])>0){
                $user = Doctrine::getTable('User')->findOneByUsername($post['username']);
                if(!$user){
                    $user_already_exists=false;
                }
            }
    
            if ($user_form->validate($post) && !$user_already_exists)
            {
                $user_form->InsertData($post);
                $this->view->error_message = $this->view->translate("recordinsertsucessfully");
                $new_user = Doctrine::getTable('User')->findOneByUsername($post['username']);
                $this->view->inserted_id=$new_user->id;
            }
            else
            {
                $user_form->assignErrorMessages();
                $this->view->error_message = "Der Benutzer konnte nicht angelegt werden.";
                if($user_already_exists){
                    $this->view->error_message = "Der Benutzername ist schon vergeben.";
                }
            }
        }
    
    
        //get local users
        $usersql = Doctrine_Query::create()
        ->select('*')
        ->from('User u')
        ->where('u.isdelete = 0')
        ->andWhere('u.clientid = ?', $clientid)
        ->andWhere('u.usertype != ?', 'SA')
        ->orderBy('u.last_name');
        $this->view->local_users = $usersql->fetchArray();
    
        //and their groupnames
        $usergrp = Doctrine_Query::create()
        ->select('*')
        ->from('Usergroup')
        ->where('clientid=' . $clientid . ' and isdelete=0');
        $usergrpexec = $usergrp->execute();
        $grouparr = $usergrpexec->toArray();
        $this->view->groups = array("0" => "");
        foreach ($grouparr as $key => $val)
        {
            $this->view->groups[$val['id']] = $val['groupname'];
        }
    }
    */
    public function fetchmedapprovaluserslistAction()
    {
        $added_users = new MedicationApprovalUsers();
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
    
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('medicationchange', $logininfo->userid, 'canview');
    
        if(!$return)
        {
            $this->_redirect(APP_BASE . "error/previlege");
        }
    
        $columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");
    
        $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
        //active page in nav
        $this->view->{"style" . $_REQUEST['pgno']} = "active";
        //first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
        $this->view->order = $orderarray['ASC'];
        foreach($columnarray as $k_elem => $v_elem)
        {
            $this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
        }
    
        $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
    
        $user = Doctrine_Query::create()
        ->select('count(*)')
        ->from('User')
        ->where('isdelete = 0')
        ->andWhere('clientid = ?', $clientid)
        ->andWhere('usertype != ?', 'SA')
        ->andWhere('isactive="0"')
        ->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
        $userarray = $user->fetchArray();
    
        $limit = 0;
        $user->select('*');
        $user->limit($limit);
        $user->offset($_REQUEST['pgno'] * $limit);
        $usserlimit = $user->fetchArray();
    
        $added_users_res = $added_users->get_medication_approval_users($clientid);
        	
        if(!empty($added_users_res))
        {
            $this->view->selected_users = $added_users_res;
        }
    
        $grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "medapprovaluserslist.html");
        $grid->connceted_clients = $connceted_clients;
        $this->view->usergrid = $grid->renderGrid();
        $this->view->navigation = $grid->dotnavigation("medapprovalusersnavigation.html", 5, $_REQUEST['pgno'], $limit);
    
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        $response['callBackParameters']['medapprovaluserlist'] = $this->view->render('user/fetchmedapprovaluserslist.html');
    
        echo json_encode($response);
        exit;
    }
    
	    //ISPC-2018
    	public function patfilestagsrightslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$users_form = new Application_Form_User();
			
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			
				$post = $_POST;
			
				$users_form->save_pat_file_tags_rights($post);
				$this->redirect(APP_BASE . 'user/patfilestagsrightslist');
				exit;
			}
				
	    }
	    
	    public function fetchpatfilestagsrightslistAction()
	    {
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid;
	    
	    	/*$previleges = new Pms_Acl_Assertion();
	    	$return = $previleges->checkPrevilege('medicationchange', $logininfo->userid, 'canview');
	    
	    	if(!$return)
	    	{
	    		$this->_redirect(APP_BASE . "error/previlege");
	    	}*/
	    
	    	$columnarray = array("un" => "username","ut" => "user_title", "ln" => "last_name");
	    
	    	$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
	    	//active page in nav
	    	$this->view->{"style" . $_REQUEST['pgno']} = "active";
	    	//first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
	    	$this->view->order = $orderarray['ASC'];
	    	foreach($columnarray as $k_elem => $v_elem)
	    	{
	    		$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
	    	}
	    
	    	$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
	    
	    	$user = Doctrine_Query::create()
	    	->select('count(*)')
	    	->from('User')
	    	->where('isdelete = 0')
	    	->andWhere('clientid = ?', $clientid)
	    	//->andWhere('id != ?', $logininfo->userid) // asta nu stiu daca sa ramana, NU
	    	
	    	->andWhere('usertype != ?', 'SA')
	    	->andWhere('isactive="0"')
	    	->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
	    	$userarray = $user->fetchArray();
	    
	    	$limit = 0;
	    	$user->select('*');
	    	$user->limit($limit);
	    	$user->offset($_REQUEST['pgno'] * $limit);
	    	$usserlimit = $user->fetchArray();
	   
	    	$this->view->totalrows = $userarray[0]['count'];
	    	//var_dump($userarray[0]['count']);
	    	if($usserlimit)
	    	{
	    		foreach($usserlimit as $ku=>$vu)
	    		{
	    			//$user_pat_files_tags_rights[$ku]['rights'] = $vu['patient_file_tag_rights'];
	    			if($vu['patient_file_tag_rights'] != '')
	    			{
	    				if (strpos($vu['patient_file_tag_rights'], 'create') !== false)
	    				{
	    					$user_pat_files_tags_cre[$ku] = $vu['id'];
	    				}
	    				
	    				if (strpos($vu['patient_file_tag_rights'], 'use') !== false)
	    				{
	    					$user_pat_files_tags_use[$ku] = $vu['id'];
	    				}
	    			}
	    		}
	    	}
	    
	    	if(!empty($user_pat_files_tags_cre))
	    	{
	    		$this->view->users_pat_files_tags_cre = $user_pat_files_tags_cre;
	    		if(count($usserlimit) == count($user_pat_files_tags_cre))
	    		{
	    			$this->view->allcre = "checked";
	    		}
	    	}
	    	
	    	if(!empty($user_pat_files_tags_use))
	    	{
	    		$this->view->users_pat_files_tags_use = $user_pat_files_tags_use;
	    		if(count($usserlimit) == count($user_pat_files_tags_use))
	    		{
	    			$this->view->alluse = "checked";
	    		}
	    	}	    	 
	    
	    	$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "patfilestagsrightslist.html");
	    	//$grid->connceted_clients = $connceted_clients;
	    	$this->view->usergrid = $grid->renderGrid();
	    	$this->view->navigation = $grid->dotnavigation("patfilestagsrightsnavigation.html", 5, $_REQUEST['pgno'], $limit);
	    
	    	$response['msg'] = "Success";
	    	$response['error'] = "";
	    	$response['callBack'] = "callBack";
	    	$response['callBackParameters'] = array();
	    	$response['callBackParameters']['patfilestagsrightslist'] = $this->view->render('user/fetchpatfilestagsrightslist.html');
	    
	    	echo json_encode($response);
	    	exit;
	    }
    
	/**
	 *
	 * ISPC-2060
	 * direct access to this controller/action is for SA only
	 * 
	 * normal user will piggyback editprofileAction, so no need to add extra rights
	 * 
	 * elviroomAction
	 * 
	 * login a user into his elvi room
	 * 
	 */
    public function elviroomAction() 
    {
        
        if ( ! Modules::checkModulePrivileges(175, $this->logininfo->clientid)) {
            
            $resultArray = ['success' => false];
            $resultArray['__ispc']['message'] = $this->translate('You are not allowed to perform this action, Module elVi disabled');
            
            Zend_Json::$useBuiltinEncoderDecoder;
            $this->_helper->json->sendJson($resultArray);
            exit;//for readability
        }
        
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
        
        //on dev or if SA we can test all
        if (APPLICATION_ENV != 'production' || $this->logininfo->usertype == 'SA') {
            
            $actions = ElviTransactionsTable::getInstance()->getEnumValues('action');
            $actions = array_combine($actions, $actions);
            $allowed_actions = $actions;
            
        } else {
            
            $allowed_actions = ['user.login'];
            
        }
        
        
        if ($this->getRequest()->isXmlHttpRequest() 
            && $this->getRequest()->isPost() 
            && $this->getRequest()->getPost('perform_action')
            && $action = $this->getRequest()->getPost('action')
            ) 
        {

            $this->_helper->layout->setLayout('layout_ajax');
            $this->_helper->viewRenderer->setNoRender();
            
            $resultArray = ['success' => false];
            

            if ( ! in_array($action, $allowed_actions)) {
                
                $resultArray['__ispc']['message'] = $this->translate('You are not allowed to perform this action');
                Zend_Json::$useBuiltinEncoderDecoder;
                $this->_helper->json->sendJson($resultArray);
                exit;//for readability
            }
            
            
            $eu = ElviUsersTable::getInstance()->findOneBy('user_id', $this->logininfo->userid, Doctrine_Core::HYDRATE_RECORD);
            $iu = Doctrine_Core::getTable('User')->find($this->logininfo->userid);
            
            $actionMethod =  "_". str_replace([".", "&"], "_", $action);
            
            $em = new ElviService();
            if ($iu && method_exists($em, $actionMethod)) {
                $resultArray = $em->$actionMethod($eu, $iu);
            }
            
            Zend_Json::$useBuiltinEncoderDecoder;
            $this->_helper->json->sendJson($resultArray);
            exit;//for readability
        
        } else {
            
            $ea = "ElviTransactionsTable.action";
            $this->view->$ea = $actions;
            
        }
        
    }
    
    
    
   
      
    /*
     * ISPC- 2157 02.03.2018 
     * */
    


    public function complaintmanagementusersAction()
    {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	$userid = $logininfo->userid;
    	$users_form = new Application_Form_User();
    
    	if($this->getRequest()->isPost())
    	{
    		$has_edit_permissions = Links::checkLinkActionsPermission();
    		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    		{
    			$this->_redirect(APP_BASE . "error/previlege");
    			exit;
    		}
    
    		$post = $_POST;
    
    		$users_form->save_complaint_users($post);
    		$this->redirect(APP_BASE . 'user/complaintmanagementusers');
    		exit;
    	}
    }
    
    public function fetchcomplaintusersAction()
    {
    	$cp_users = new ComplaintUsers();
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;

    
    	$columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");
    
    	$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
    	//active page in nav
    	$this->view->{"style" . $_REQUEST['pgno']} = "active";
    	//first sort order(first loaded data is ascending then all links should have order DESC which is $orderarray['ASC'])
    	$this->view->order = $orderarray['ASC'];
    	foreach($columnarray as $k_elem => $v_elem)
    	{
    		$this->view->{$k_elem . "order"} = $orderarray[$_REQUEST['ord']];
    	}
    
    	$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
    
    	$user = Doctrine_Query::create()
    	->select('count(*)')
    	->from('User')
    	->where('isdelete = 0')
    	->andWhere('clientid = ?', $clientid)
    	->andWhere('usertype != ?', 'SA')
    	->andWhere('isactive="0"')
    	->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
    	$userarray = $user->fetchArray();
    
    	$limit = 0;
    	$user->select('*');
    	$user->limit($limit);
    	$user->offset($_REQUEST['pgno'] * $limit);
    	$usserlimit = $user->fetchArray();
 
    	$cp_users_array= $cp_users->get_complaint_users($clientid,true);
    	 
    	if(!empty($cp_users_array))
    	{
    		$this->view->selected_users = $cp_users_array;
    	}
    
    	$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "complaintmangusers.html");
    	$grid->connceted_clients = $connceted_clients;
    	$this->view->usergrid = $grid->renderGrid();
    	$this->view->navigation = $grid->dotnavigation("complaintmangusersnavigation.html", 5, $_REQUEST['pgno'], $limit);
    
    	$response['msg'] = "Success";
    	$response['error'] = "";
    	$response['callBack'] = "callBack";
    	$response['callBackParameters'] = array();
    	$response['callBackParameters']['complaintmangusers'] = $this->view->render('user/fetchcomplaintusers.html');
    
    	echo json_encode($response);
    	exit;
    }
    
    
    public function informaboutfamilydoctorAction(){
        
        $modules = new Modules();
        
        if( ! $modules->checkModulePrivileges(164)) { //164 = Family Doc manually added -> send message
            
            $this->redirect(APP_BASE . "error/previlege", array(
                "exit" => true
            ));
            
            exit; // for readbility
        }
        
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('fetchtable') == 1) {
            //this is a xhr to fetch the html table with the users
            $this->_fetchinformaboutfamilydoctor();
        }
        elseif($this->getRequest()->isPost()) {
            //this is a post to save the form
            $users_form = new Application_Form_User();
            $users_form->save_form_informaboutfamilydoctor( $this->getRequest()->getParams());
             
        } else {
            //add your logic
        }
        
    }
    
    
    private function _fetchinformaboutfamilydoctor()
    {
          
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();

        $columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");//allowed columns to order by
        $orderarray = array("ASC", "DESC"); //allowed sort orders
    
        $column = $this->getRequest()->getParam('clm');
        $order = $this->getRequest()->getParam('ord');
        
        $column = isset($columnarray[$column]) ? $columnarray[$column] : 'last_name';
        $order = in_array($order, $orderarray) ? $order : 'DESC';
        
        $users_form = new Application_Form_User();
        $form_informaboutfamilydoctor = $users_form->create_form_informaboutfamilydoctor(null, null, array('column' => $column, 'order' => $order));
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        $response['callBackParameters']['informaboutfamilydoctor_table'] = $form_informaboutfamilydoctor->__toString();
    
        echo json_encode($response);
        exit;
        


        //TODO: REMOVE create_form_informaboutfamilydoctor and just return next as json to a datatable
        /*
         $users = User::get_AllByClientid($this->logininfo->clientid, array('us.*', 'username'));
        
         //remove inactive and deleted
         $users = array_filter($users, function($user) {
         return ( ! $user['isdelete']) && ( ! $user['isactive']);
         });
        
         //sort by column
         if ( ! empty($users) && ! empty($sort_order) ) {
        
         usort($users, array(new Pms_Sorter($sort_order ['column']), "_strnatcasecmp"));
        
         if ($sort_order ['order'] == 'DESC') {
         $users = array_reverse($users);
         }
        
         }
         */
        
    }


    /**
     * @author nico
     * This search is used on ldapusermgmt-page //Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public function ldapajaxsearchAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');

        $search=$_GET['search'];

        $return=array();
        if(strlen($search)>1) {

            if(isset($_GET['search2'])){
                $search=[$search, $_GET['search2']];
            }

            $ldap = new Net_Ldap();
            $return = $ldap->get_userinfo($search);
        }
        echo (json_encode($return));
    }

    /**
     * @author nico
     * Admin-Page to create users with ldap-support //Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public function ldapusermgmtAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;

        $ldap=new Net_Ldap();
        $this->view->ldapsearchmode=$ldap->get_ldap_searchmode();
        $this->view->searchlabel=$ldap->search_label;
        $this->view->second_input=$ldap->search_by2;

        $this->view->ldapid===false;
        if($ldap->login_usercolumn!=="username"){
            $this->view->ldapid=$ldap->login_usercolumn;
        }




        if ($this->getRequest()->isPost())
        {
            //add never used password
            $post=$_POST;
            $post['paswdpassword']=rand(100000000,900000000);
            $post['cfmpassword']=$post['paswdpassword'];

            $user_form = new Application_Form_User();
            //double_username_check
            $user_already_exists=true;
            if(strlen($post['username'])>0){
                $user = Doctrine::getTable('User')->findOneByUsername($post['username']);
                if(!$user){
                    $user_already_exists=false;
                }
            }

            if ($user_form->validate($post) && !$user_already_exists)
            {
                $user_form->InsertData($post);
                $this->view->error_message = $this->view->translate("recordinsertsucessfully");
                $new_user = Doctrine::getTable('User')->findOneByUsername($post['username']);
                if(isset($post['ldapid'])) {
                    $new_user->ldapid = $post['ldapid'];
                    $new_user->save();
                }
                $this->view->inserted_id=$new_user->id;
            }
            else
            {
                $user_form->assignErrorMessages();
                $this->view->error_message = "Der Benutzer konnte nicht angelegt werden.";
                if($user_already_exists){
                    $this->view->error_message = "Der Benutzername ist schon vergeben.";
                }
            }
        }

        //get local users
        $usersql = Doctrine_Query::create()
            ->select('*')
            ->from('User u')
            ->where('u.isdelete = 0')
            ->andWhere('u.clientid = ?', $clientid)
            ->andWhere('u.usertype != ?', 'SA')
            ->orderBy('u.last_name');
        $this->view->local_users = $usersql->fetchArray();

        //and their groupnames
        $usergrp = Doctrine_Query::create()
            ->select('*')
            ->from('Usergroup')
            ->where('clientid=' . $clientid . ' and isdelete=0');
        $usergrpexec = $usergrp->execute();
        $grouparr = $usergrpexec->toArray();
        $this->view->groups = array("0" => "");
        foreach ($grouparr as $key => $val)
        {
            $this->view->groups[$val['id']] = $val['groupname'];
        }
    }
    
    
    /**
     * TODO-3462 Ancuta 19.10.2020
     */
    public function informaboutnotacceptedrequestAction(){
        
        $modules = new Modules();
        
        if( ! $modules->checkModulePrivileges(242)) { //242 = DemStepCare:: Sent message to client specific users, if request not accepted
            
            $this->redirect(APP_BASE . "error/previlege", array(
                "exit" => true
            ));
            
            exit; // for readbility
        }
        
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('fetchtable') == 1) {
            //this is a xhr to fetch the html table with the users
            $this->_fetchinformaboutnotacceptedrequest();
        }
        elseif($this->getRequest()->isPost()) {
            //this is a post to save the form
            $users_form = new Application_Form_User();
//             $users_form->save_form_informaboutfamilydoctor( $this->getRequest()->getParams());
            $users_form->save_form_informaboutnotacceptedrequest( $this->getRequest()->getParams());
            
        } else {
            //add your logic
        }
        
    }
    
    /**
     * TODO-3462 Ancuta 19.10.2020
     * 
     * @throws Exception
     */
    private function _fetchinformaboutnotacceptedrequest()
    {
        
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $columnarray = array("un" => "username","ut" => "user_title", "fn" => "first_name", "ln" => "last_name");//allowed columns to order by
        $orderarray = array("ASC", "DESC"); //allowed sort orders
        
        $column = $this->getRequest()->getParam('clm');
        $order = $this->getRequest()->getParam('ord');
        
        $column = isset($columnarray[$column]) ? $columnarray[$column] : 'last_name';
        $order = in_array($order, $orderarray) ? $order : 'DESC';
        
        $users_form = new Application_Form_User();
        $form_informaboutnotacceptedrequest = $users_form->create_form_informaboutnotacceptedrequest(null, null, array('column' => $column, 'order' => $order));
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        $response['callBackParameters']['informaboutnotacceptedrequest_table'] = $form_informaboutnotacceptedrequest->__toString();
        
        echo json_encode($response);
        exit;
        
    }
    
 /**
     * ISPC-2578, elena, 15.09.2020
     *
     * Request create new User
     *
     * @throws Zend_Mail_Exception
     */
    public function newuserAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        //ISPC-2578, elena, 12.11.2020
        $user = Doctrine::getTable('User')->find($userid);
        $userarray = $user->toArray();
        $user_email = $userarray['emailid'];
        $clientid = $logininfo->clientid;
        $client = Client::getClientDataByid($clientid);
        //print_r($client);
        $usergroups = new Usergroup();
        $client_usergroups_array = $usergroups->getClientGroups($clientid);
        $this->view->client_usergroups_array = $client_usergroups_array;
        //print_r($client_usergroups_array);
        if($this->getRequest()->isPost()){
            $post = $this->getRequest()->getPost();

            $newview = new Zend_View();
            //$newview->pdf = $pdf;

            foreach ($post as $key=>$value){
                $newview->$key = $value;
            }
            $newview->client_name = $client[0]['client_name'];
            //ISPC-2578, elena, 12.11.2020
            //ISPC-2913,Elena,11.05.2021
            //$newview->user_email = $user_email;

            $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
            $html = $newview->render('emailnewuser.html');
            //ISPC-2913,Elena,11.05.2021
            $internal_text = $newview->render('email_newuser_sent.html');

            $mail = new Zend_Mail('UTF-8');

            $mail->setBodyHtml($html)
                ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
                ->addTo(ISPC_SENDER, 'Smart-Q')
                ->setSubject("Benutzer anlegen: Anfrage")
                ->send();

            //ISPC-2913,Elena,11.05.2021
            $msg = new Messages();
            $title = 'Benutzeranmeldung: Ihre Anfrage';

            $msg->sender = 0; // system message
            $msg->clientid = $clientid;
            $msg->recipient = $userid;
            //$msg->ipid = $todo['ipid'];
            $msg->msg_date = date("Y-m-d H:i:s", time());
            $msg->title = Pms_CommonData::aesEncrypt($title);
            $msg->content = Pms_CommonData::aesEncrypt($internal_text);
            $msg->source = 'userrequest_client';
            $msg->create_date = date("Y-m-d", time());
            $msg->create_user = 0;
            $msg->read_msg = '0';
            $msg->save();

            $this->_redirect(APP_BASE . 'user/newuser?flg=suc');



        }

    }

 /**
     * ISPC-2578, elena, 15.09.2020
     *
     * Request deactivate existing User
     *
     * @throws Doctrine_Query_Exception
     * @throws Zend_Mail_Exception
     */
    public function deactivateuserAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        //ISPC-2913,Elena,11.05.2021
        $ur = new UserRequest();
        //print_r($ur->getSql());
        //ISPC-2578, elena, 12.11.2020
        $user = Doctrine::getTable('User')->find($userid);
        $userarray = $user->toArray();
        $user_email = $userarray['emailid'];
        $clientid = $logininfo->clientid;
        $client = Client::getClientDataByid($clientid);
        //get local users
        $usersql = Doctrine_Query::create()
            ->select('*')
            ->from('User u')
            ->where('u.isdelete = 0')
            ->andWhere('u.isactive="0"')
            ->andWhere('u.clientid = ?', $clientid)
            ->andWhere('u.usertype != ?', 'SA')
            ->orderBy('u.last_name');
        $this->view->local_users = $usersql->fetchArray();

        //and their groupnames
        $usergrp = Doctrine_Query::create()
            ->select('*')
            ->from('Usergroup')
            ->where('clientid=' . $clientid . ' and isdelete=0');
        $usergrpexec = $usergrp->execute();
        $grouparr = $usergrpexec->toArray();
        $this->view->groups = array("0" => "");
        foreach ($grouparr as $key => $val)
        {
            $this->view->groups[$val['id']] = $val['groupname'];
        }

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            $newview = new Zend_View();
            //$newview->pdf = $pdf;
            $aIds = [];

            foreach ($post as $key=>$value){
                $newview->$key = $value;

                if(strstr($key, 'deakt_' )){
//ISPC-2913,Elena,11.05.2021
                    $deakt_user_id = intval(str_replace('deakt_', '', $key));
//ISPC-2913,Elena,11.05.2021
                    $aIds[] = $deakt_user_id;
                }
                }
            //ISPC-2913,Elena,11.05.2021
            $abmeldedatum = $post['abmeldedatum'];
            $abmeldedatum_as_date = date_create_from_format('d.m.Y', $abmeldedatum);
            $abmeldedatum_for_db = date_format($abmeldedatum_as_date, 'Y-m-d H:i:s');
            $note = $post['notes'];
            foreach($aIds as $id_to_deactivate){

                $ur->saveDeactivateRequest($id_to_deactivate, $abmeldedatum_for_db, $note, $clientid);
            }


            $newview->deactivate_ids = $aIds;
            $newview->local_users = $this->view->local_users ;
            $newview->groups = $this->view->groups;
            $newview->client_name = $client[0]['client_name'];
            //ISPC-2578, elena, 12.11.2020
            $newview->user_email = $user_email;

            $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
            $html = $newview->render('emaildeactivateuser.html');
            //ISPC-2913,Elena,11.05.2021
            $internal_text = $newview->render('emaildeactivateuser_msg.html');

            $mail = new Zend_Mail('UTF-8');

            $mail->setBodyHtml($html)
                ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
                ->addTo(ISPC_SENDER, 'Smart-Q')
                ->setSubject("Benutzer deaktivieren: Anfrage")
                ->send();
            //ISPC-2913,Elena,11.05.2021
            $msg = new Messages();
            $title = 'Benutzer deaktivieren: Ihre Anfrage';

            $msg->sender = 0; // system message
            $msg->clientid = $clientid;
            $msg->recipient = $logininfo->userid;
            //$msg->ipid = $todo['ipid'];
            $msg->msg_date = date("Y-m-d H:i:s", time());
            $msg->title = Pms_CommonData::aesEncrypt($title);
            $msg->content = Pms_CommonData::aesEncrypt($internal_text);
            $msg->source = 'userrequest_client';
            $msg->create_date = date("Y-m-d", time());
            $msg->create_user = 0;
            $msg->read_msg = '0';
            $msg->save();

            $this->_redirect(APP_BASE . 'user/deactivateuser?flg=suc');

        }

    }
  
}

?>