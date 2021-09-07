<?php

	require_once("Pms/Form.php");

	class Application_Form_User extends Pms_Form {

		public function validate($post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			
			$appInfo = Zend_Registry::get('appInfo');
			$username_chars_limit = isset($appInfo['UsernameChars']) && !empty($appInfo['UsernameChars'] ) ? $appInfo['UsernameChars'] : "6";
			$use_password  = 	isset($appInfo['UsePassword']) && $appInfo['UsePassword'] == "1" ?  true : false;

			$err_arr = array();
			if ($_GET['id'] > 1 
			    || (isset($post['edit_userid']) && $post['edit_userid'] > 1 && isset($post['username'])))
			{
				if(!$val->isstring($post['username']))
				{
				    $err_arr[] =
				    $this->error_message['username'] = $Tr->translate('enterusername');
					$error = 1;
				}
				if(strlen($post['username']) < $username_chars_limit)
				{
				    $err_arr[] =
					$this->error_message['username'] = $Tr->translate('enter'.$username_chars_limit.'charusername');
					$error = 1;
				}

				if($val->isstring($post['paswdpassword']))
				{
				    //ISPC-2099 
// 					if(!$val->isstring($post['cfmpassword']))
// 					{
// 						$this->error_message['cfmpassword'] = $Tr->translate('enterconfirmpassword');
// 						$error = 3;
// 					}
// 					if(strcmp($post['paswdpassword'], $post['cfmpassword']) != 0)
// 					{
// 						$this->error_message['cfmpassword'] = $Tr->translate('confirmpassworddoesnotmatch');
// 						$error = 3;
// 					}
				}
			}
			elseif ( ! isset($post['edit_userid'])) {
			    
				if(!$val->isstring($post['username']))
				{
				    $err_arr[] =
				    $this->error_message['username'] = $Tr->translate('enterusername');
					$error = 1;
				}
				if(strlen($post['username']) < $username_chars_limit)
				{
				    $err_arr[] =
				    $this->error_message['username'] = $Tr->translate('enter'.$username_chars_limit.'charusername');
					$error = 1;
				}
				
				// TODO-1330
			/* 	if(!$val->isstring($post['paswdpassword']))
				{
					$this->error_message['password'] = $Tr->translate('enterpassword');
					$error = 2;
				}
				if(strlen($post['paswdpassword']) < 6)
				{
					$this->error_message['password'] = $Tr->translate('minimum6charspassword');
					$error = 2;
				} */
				//ISPC-2099
// 				if(!$val->isstring($post['cfmpassword']))
// 				{
// 					$this->error_message['cfmpassword'] = $Tr->translate('enterconfirmpassword');
// 					$error = 3;
// 				}
// 				if(strcmp($post['paswdpassword'], $post['cfmpassword']) != 0)
// 				{
// 					$this->error_message['cfmpassword'] = $Tr->translate('confirmpassworddoesnotmatch');
// 					$error = 3;
// 				}
			}
			if(!$val->isstring($post['first_name']))
			{
			    $err_arr[] =
				$this->error_message['first_name'] = $Tr->translate('enterfirstname');
				$error = 5;
			}

			if(!$val->isstring($post['last_name']))
			{
			    $err_arr[] =
				$this->error_message['last_name'] = $Tr->translate('enterlastname');
				$error = 5;
			}
			
			
			if ($logininfo->usertype == 'SA' && $post['edit_userid'] == $logininfo->userid) {
			    //this is a SA that is editing himself... SA editing other SA will fail because of no group
			    
			} elseif ( ! $val->isstring($post['groupname'])) {
			    $err_arr[] =
				$this->error_message['groupname'] = $Tr->translate('selectusergroup');
				$error = 5;
			}

			
			// ANCUTA
			// Added app setting to use and validate password 
			// 28.01.2019
			// meaning reverse ISPC-2099 
			if ( $use_password ){
			    if ( ! isset($post['edit_userid'])) {
			        
			        if(!$val->isstring($post['paswdpassword']))
			        {
			            $this->error_message['password'] = $Tr->translate('enterpassword');
			            $error = 2;
			        }
			        if(strlen($post['paswdpassword']) < 6)
			        {
			            $this->error_message['password'] = $Tr->translate('minimum6charspassword');
			            $error = 2;
			        } 
			        
			    }
			    
			    if($val->isstring($post['paswdpassword']))
			    {
			        //ISPC-2099
					if(!$val->isstring($post['cfmpassword']))
    				{
    					$this->error_message['cfmpassword'] = $Tr->translate('enterconfirmpassword');
    					$error = 3;
    				}
    				if(strcmp($post['paswdpassword'], $post['cfmpassword']) != 0)
        			{
        				$this->error_message['cfmpassword'] = $Tr->translate('confirmpassworddoesnotmatch');
        				$error = 3;
        			}
                }
			}
			
			
			
			
			if(isset($post['edit_userid']) && $post['edit_userid'] > 1 && isset($post['username']))
			{
				$user = Doctrine_Query::create()
					->select('id')
					->from('User')
					->where("clientid = :clientid")
					->andWhere("isdelete = 0")
					->andWhere("id != :id")
					->andWhere("username = :username")
                    ->fetchOne(array(
                        'id'        => $post['edit_userid'],
                        'username'  => $post['username'],
                        'clientid'  => $logininfo->clientid
                    ));

				if($user !== false) {
					$this->error_message['username'] = $Tr->translate("usernamealreadyexists");
					$error = 7;
				}
			}
			elseif (isset($post['username']))
			{
				$user = Doctrine_Query::create()
					->select('id')
					->from('User')
					//ISPC-2232 - removed
// 					->where("clientid = :clientid") 
					->andWhere("isdelete = 0")
					->andWhere("username = :username")
					->fetchOne(array(
    				    'username'  => $post['username'],
//     				    'clientid'  => $logininfo->clientid
				));
				if($user !== false) {
				    $err_arr[] =
					$this->error_message['username'] = $Tr->translate("usernamealreadyexists");
					$error = 7;
				}
			}
			
			if($logininfo->usertype == 'SA' && $logininfo->clientid == 0)
			{
				if(!$val->isstring($post['client_name']))
				{
					$this->error_message['client_name'] = $Tr->translate("selectclient");
					$error = 7;
				}
			}

			if($logininfo->usertype != 'SA')
			{
				if(!$val->email($post['emailid']))
				{
					//Maria:: Migration CISPC to ISPC 22.07.2020
				    if( !isset($post['ldapid'])) { //ldap-users dont provide mailaddress
					    $err_arr[] =
						$this->error_message['emailid'] = $Tr->translate('enteremailid');
						$error = 5;
					}
				}
			}

			
			if(isset($post['patient_deletion_allowed']) && $post['patient_deletion_allowed'] == 1 ){
			    if($post['patient_deletion_allowed'] == 1){
			        
			    }
			    
			    //ISPC-2474 Lore 04.11.2020
			    if(isset($post['patient_deletion_password_change']) && $post['patient_deletion_password_change'] == 1 ){
			        if(strlen($post['patient_deletion_password']) < 6)
			        {
			            $this->error_message['patient_deletion_password'] = $Tr->translate('minimum6charspassword');
			            $error = 2;
			        }
			    }
			    //.
			    
/* 			    if(strlen($post['patient_deletion_password']) < 6)
			    {
			        $this->error_message['patient_deletion_password'] = $Tr->translate('minimum6charspassword');
			        $error = 2;
			    } */
			}
			
			
			//echo '<div class="err">'.implode("<br/>", $err_arr).'</div>';
			
			if($error == 0)
			{
				return true;
			}

			return false;
		}

		//ISPC-2138: fn editvalidate is no longer in use ...
		public function editvalidate($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$cid = $logininfo->userid;
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if($logininfo->usertype == 'SA')
			{
				if(!$val->isstring($post['username']))
				{
					$this->error_message['username'] = $Tr->translate('enterusername');
					$error = 1;
				}
				if(strlen($post['username']) < 6)
				{
					$this->error_message['username'] = $Tr->translate('enter6charusername');
					$error = 1;
				}
			}
			if($val->isstring($post['paswdpassword']))
			{
			    //ISPC-2099
// 				if(!$val->isstring($post['cfmpassword']))
// 				{
// 					$this->error_message['cfmpassword'] = $Tr->translate('enterconfirmpassword');
// 					$error = 3;
// 				}
// 				if(strcmp($post['paswdpassword'], $post['cfmpassword']) != 0)
// 				{
// 					$this->error_message['cfmpassword'] = $Tr->translate('confirmpassworddoesnotmatch');
// 					$error = 3;
// 				}
			}

			if(!$val->isstring($post['first_name']))
			{
				$this->error_message['first_name'] = $Tr->translate('enterfirstname');
				$error = 5;
			}
			if(!$val->email($post['emailid']))
			{
				$this->error_message['emailid'] = $Tr->translate('enteremailid');
				$error = 5;
			}
			if(!$val->isstring($post['last_name']))
			{
				$this->error_message['last_name'] = $Tr->translate('enterlastname');
				$error = 5;
			}

			if($post['km_calculation_settings'] == "user")
			{
				if(!$val->isstring($post['street1']))
				{
					$this->error_message['street1'] = $Tr->translate('enterstreetname');
					$error = 5;
				}
				if(!$val->isstring($post['zip']))
				{
					$this->error_message['zip'] = $Tr->translate('enterpostcode');
					$error = 5;
				}
				if(!$val->isstring($post['city']))
				{
					$this->error_message['city'] = $Tr->translate('entercityname');
					$error = 5;
				}
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validateeditProfile($post, $cid = null)
		{
			$user_stemp = explode('-', $post['stampusers']);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if(empty($cid)) {
				$cid = $logininfo->userid;
			}

			$error = 0;
			$val = new Pms_Validation();

			$user = Doctrine::getTable('User')->find($cid);
			$userarray = $user->toArray();
			
// 			$user = Doctrine::getTable('User')->find($cid);
			if(strlen($post['paswdpassword']) > 0)
			{
				$user->password = md5($post['paswdpassword']);
			}
			$user->title = $post['title'];
			$user->user_title = $post['user_title'];
			$user->first_name = $post['first_name'];
			$user->last_name = $post['last_name'];
			$user->mobile = $post['mobile'];
			$user->phone = $post['phone'];
			$user->street1 = $post['street1'];
			$user->street2 = $post['street2'];
			$user->zip = $post['zip'];
			$user->city = $post['city'];
			$user->fax = $post['fax'];
			$user->emailid = $post['emailid'];
			$user->betriebsstattennummer = $post['betriebsstattennummer'];
			$user->LANR = $post['LANR'];
			$user->private_phone = $post['private_phone'];
			$user->notification = $post['notification'];
			$user->no10contactsbox = $post['no10contactsbox'];
			if(empty($post['onlyAssignedPatients']))
			{
				$user->onlyAssignedPatients = "0";
			}
			else
			{
				$user->onlyAssignedPatients = $post['onlyAssignedPatients'];
			}
			$user->sixwnote = $post['sixwnote'];
			$user->fourwnote = $post['fourwnote']; // 4 weeks notification for patients discharged with stti(BEHANDLUNGSUNTERBRECHUNG)
			$user->shortname = $post['shortname'];
			$user->usercolor = $post['usercolor'];
			$user->user_status = $post['user_status'];

			$user->verlauf_newest = $post['verlauf_newest'];
			$user->verlauf_fload = $post['verlauf_fload'];

			$user->verlauf_action = $post['verlauf_action'];

			if(empty($post['verlauf_entries']))
			{
				$post['verlauf_entries'] = '0';
			}

			$user->verlauf_entries = $post['verlauf_entries'];
			// ISPC-2018
			//@author claudiu on 05.02.2018, only SA,CA can change this setting
/* 			if($post['pat_file_tags_rights'] && ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA'))
			{
			    
				$user->patient_file_tag_rights = implode(',',$post['pat_file_tags_rights']);
			}
			else
			{
				if($post['edit_type'] == 'edituser')
				{
					$user->patient_file_tag_rights = '';
				}
			} */
			
		
			//TODO-2631   @Lore 31.10.2019
			if( ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA'))
			{
			    if (!empty($post['pat_file_tags_rights'])){
			        $user->patient_file_tag_rights = implode(',',$post['pat_file_tags_rights']);
			    } else {
			        $user->patient_file_tag_rights = '';
			    }
			}
			
			
			$user->receipt_print_settings = $post['receipt_print_settings'];
			

			//bank account details
			$user->bank_name = $post['bank_name'];
			$user->bank_account_number = $post['bank_account_number'];
			$user->bank_number = $post['bank_number'];
			$user->iban = $post['iban'];
			$user->bic = $post['bic'];
			$user->ikusernumber = $post['ikusernumber'];
			$user->dashboard_limit = $post['dashboard_limit'];

			if(empty($post['show_custom_events']))
			{
				$user->show_custom_events = '0';
			}
			else
			{
				$user->show_custom_events = $post['show_custom_events'];
			}

			$user->allow_own_list_discharged = $post['allow_own_list_discharged'];
			$user->assigned_standby = $post['assigned_standby'];
			$user->km_calculation_settings = $post['km_calculation_settings'];
			$user->control_number = $post['control_number'];
			if(!empty($post['meeting_attendee']))
			{
				$user->meeting_attendee = $post['meeting_attendee'];
			}
			else
			{
				$user->meeting_attendee = '0';
			}
			
			
			if($logininfo->usertype == 'SA' || ($logininfo->usertype == 'CA' && $cid != $logininfo->userid))
			{
			    if($userarray['usertype'] != "SA"){ // do not edit for sadmin
    			    $user->isadmin = $post['isadmin'];
    			    
        			if($post['isadmin'] == 1)
        			{
        			    $user->usertype = "CA";
        			} 
        			else
        			{
        			    $user->usertype = "";
        			}
			    }
			}
			
			//@author claudiu on 05.02.2018, only SA,CA can change this setting
			if(isset($post['groupname']) && ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA'))
			{
    			$user->groupid = $post['groupname'];
			}
			
			//ispc-1802
			//@author claudiu on 05.02.2018, only SA,CA can change this setting
			if(isset($post['isactive'],$post['isactive_date']) && ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')){
				if( $post['isactive'] == 0 ){
					$user->isactive = 0;
					$user->isactive_date = '';
				}
				else{
					$user->isactive = 1;
					if (empty($post['isactive_date'])){
						$user->isactive_date = '';
					}else{
						$user->isactive_date = date("Y-m-d", strtotime($post['isactive_date']));
					}
				}
			}
			//ispc-1533
			//@author claudiu on 05.02.2018, only SA,CA can change this setting
			if(isset($post['makes_visits']) && ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')){
				$user->makes_visits = (int)$post['makes_visits']; 
			}
			
			//ispc-1835
// 			if($post['user_settings']['id'] != '')
// 			{
                $post['user_settings']['userid'] = $cid;
                
				$ust = new Application_Form_UserSettings();
				$ust->UpdateData($post['user_settings']);
			
// 			}
// 			else
// 			{
// 				$ust = new Application_Form_UserSettings();
// 				$ust->InsertData($post['user_settings'], $user->id);
// 			}
			
//			$user->mmi_n=$post['mmi_n'];
//			$user->mmi_k=$post['mmi_k'];
			$user->roster_shortcut = $post['roster_shortcut'];
			$user->default_stampusers = $user_stemp[0];
			$user->default_stampid = $user_stemp[1];
			
			
			//ISPC-2272 (@ancuta 23.10.2018)
			$user->debitor_number = $post['debitor_number'];
			//---
					
			
			//ISPC-2272 Ancuta 30-31.03.2020
			$user->user_specific_account = $post['user_specific_account'];
			//---
			
			//ISPC-2513 Lore 13.04.2020
			//#ISPC-2512PatientCharts
			$user->header_type = $post['header_type'];
			//.
			
			//ISPC-2474 Ancuta 23.10.2020
			$user->patient_deletion_allowed = $post['patient_deletion_allowed'];
/* 			if($post['patient_deletion_allowed'] == '1' && strlen($post['patient_deletion_password']) > 0) {
			
			    $user->patient_deletion_password = md5($post['patient_deletion_password']);
		    } */
			//ISPC-2474 Lore 04.11.2020
			if($post['patient_deletion_allowed'] == 1 && $post['patient_deletion_password_change'] == 1 && strlen($post['patient_deletion_password']) > 0) {
			    $user->patient_deletion_password = md5($post['patient_deletion_password']);
			}
		    //--

			
			//ISPC-2827 Ancuta 26.03.2021
			$user->efa_user = $post['efa_user'];
			//.
			
			
			$user->save();
			
// 			return $emailchg; //yet another flower-power,  who is this?
			return $user; 
		}

		public function InsertData($post)
		{
			$user_stemp = explode('-', $post['stampusers']);
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(strlen($post['emailid']) < 1)
			{
				if($logininfo->usertype == "SA")
				{
					$clarr = Pms_CommonData::getClientData($logininfo->clientid);
					$post['emailid'] = $clarr['0']['emailid'];
				}
			}

			$appInfo = Zend_Registry::get('appInfo');
			$use_password  = 	isset($appInfo['UsePassword']) ? $appInfo['UsePassword'] : "false";
			
			$user = new User();
			$user->username = $post['username'];
			if($use_password){
			    if(strlen($post['paswdpassword'])){
			        $user->password = md5($post['paswdpassword']);
			    }
			    
			} else{
			
    			if(strlen($post['paswdpassword'])){
    				$user->password = md5($post['paswdpassword']);
    			} else{
    				$user->password = md5($post['username']);
    			}
			}
			$user->title = $post['title'];
			$user->user_title = $post['user_title'];
			$user->first_name = $post['first_name'];
			$user->last_name = $post['last_name'];
			$user->mobile = $post['mobile'];
			$user->phone = $post['phone'];
			$user->fax = $post['fax'];
			$user->street1 = $post['street1'];
			$user->street2 = $post['street2'];
			$user->zip = $post['zip'];
			$user->city = $post['city'];
			$user->emailid = $post['emailid'];
			$user->private_phone = $post['private_phone'];
			$user->betriebsstattennummer = $post['betriebsstattennummer'];
			$user->LANR = $post['LANR'];
			$user->isadmin = $post['isadmin'];
			$user->notification = $post['notification'];
			$user->no10contactsbox = $post['no10contactsbox'];
			$user->shortname = $post['shortname'];
			$user->usercolor = $post['usercolor'];
			$user->user_status = $post['user_status'];

			if(!empty($post['verlauf_newest']))
			{
				$user->verlauf_newest = $post['verlauf_newest'];
			}
			if(!empty($post['verlauf_fload']))
			{
				$user->verlauf_fload = $post['verlauf_fload'];
			}
			if(!empty($post['verlauf_action']))
			{
				$user->verlauf_action = $post['verlauf_action'];
			}

			if($post['isadmin'] == 1)
			{
				$user->usertype = "CA";
			}
			if($logininfo->usertype != 'SA')
			{
				$user->clientid = $logininfo->clientid;
			}
			else
			{
				if($_GET['id'] > 0)
				{
					$user->clientid = $_GET['id'];
				}
				else
				{
					$user->clientid = $logininfo->clientid;
				}
			}

			$user->parentid = $logininfo->userid;
			$user->groupid = $post['groupname'];
			$user->bank_name = $post['bank_name'];
			$user->bank_account_number = $post['bank_account_number'];
			$user->bank_number = $post['bank_number'];
			$user->iban = $post['iban'];
			$user->bic = $post['bic'];
			$user->ikusernumber = $post['ikusernumber'];
			$user->control_number = $post['control_number'];
			if(!empty($post['meeting_attendee']))
			{
				$user->meeting_attendee = $post['meeting_attendee'];
			}
			else
			{
				$user->meeting_attendee = '0';
			}
			$user->roster_shortcut = $post['roster_shortcut'];
			$user->default_stampusers = $user_stemp[0];
			$user->default_stampid = $user_stemp[1];
			
			//ispc-1802
			if(isset($post['isactive'],$post['isactive_date'])){
				if( $post['isactive'] == 0 ){
					$user->isactive = 0;
					$user->isactive_date = '';
				}
				else{
					$user->isactive = 1;
					if (empty($post['isactive_date'])){
						$user->isactive_date = '';
					}else{
						$user->isactive_date = date("Y-m-d", strtotime($post['isactive_date']));
					}				
				}
			}
			//ispc-1533
			if(isset($post['makes_visits'])){
				$user->makes_visits = (int)$post['makes_visits']; 
			}
			
			//ISPC-2272 (@ancuta 23.10.2018)
			$user->debitor_number = $post['debitor_number'];
			//---
					
			//TODO-2507 Lore 19.08.2019
			$user->patient_file_tag_rights = implode(',', $post['pat_file_tags_rights']);
			//--		    
			
			
			//ISPC-2272 Ancuta 30-31.03.2020
			$user->user_specific_account = $post['user_specific_account'];
			//---
			
			//ISPC-2513 Lore 13.04.2020
			//#ISPC-2512PatientCharts
			$user->header_type = $post['header_type'];
			//.
			
			
			//ISPC-2827 Ancuta 26.03.2021
			$user->efa_user = $post['efa_user'];
			//.
			
			$user->save();

			//ispc-1835
			if($post['user_settings'] && $user->id)
			{
			    $post['user_settings']['userid'] = $user->id;
			    
			    //hidden_patient_icons=0 <=> all allowed
			    $post['user_settings']['hidden_patient_icons'] = 0;
			    
				$ust = new Application_Form_UserSettings();
				$ust->UpdateData($post['user_settings']);				
			}
	
			$this->view->logininfo_usertype = $logininfo->usertype ;
			
			return $user;
		}

		public function UpdateData($post)
		{
			$user_stemp = explode('-', $post['stampusers']);
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(strlen($post['emailid']) < 1)
			{
				if($logininfo->usertype == "SA")
				{
					$clarr = Pms_CommonData::getClientData($logininfo->clientid);
					$post['emailid'] = $clarr['0']['emailid'];
				}
			}


			$Tr = new Zend_View_Helper_Translate();
			if($_GET['id'] > 0)
			{
				$cid = $_GET['id'];
			}
			else
			{
				$cid = $logininfo->userid;
			}

			$tempuser = Doctrine::getTable('User')->find($cid);
			$temparray = $tempuser->toArray();

			$user = Doctrine::getTable('User')->find($cid);
			$user->username = $post['username'];
			if(strlen($post['paswdpassword']) > 0)
			{
				$user->password = md5($post['paswdpassword']);
			}
			$user->title = $post['title'];
			$user->user_title = $post['user_title'];
			$user->first_name = $post['first_name'];
			$user->last_name = $post['last_name'];
			$user->mobile = $post['mobile'];
			$user->fax = $post['fax'];
			$user->phone = $post['phone'];
			$user->street1 = $post['street1'];
			$user->street2 = $post['street2'];
			$user->zip = $post['zip'];
			$user->city = $post['city'];
			$user->emailid = $post['emailid'];
			$user->betriebsstattennummer = $post['betriebsstattennummer'];
			$user->LANR = $post['LANR'];
			$user->private_phone = $post['private_phone'];
			$user->usercolor = $post['usercolor'];
			$user->user_status = $post['user_status'];

			if(!empty($post['verlauf_newest']))
			{
				$user->verlauf_newest = $post['verlauf_newest'];
			}
			if(!empty($post['verlauf_fload']))
			{
				$user->verlauf_fload = $post['verlauf_fload'];
			}
			if(!empty($post['verlauf_action']))
			{
				$user->verlauf_action = $post['verlauf_action'];
			}

			$user->isadmin = $post['isadmin'];
			
			if($post['isadmin'] == 1)
			{
				$user->usertype = "CA";
			}
			else
			{
				$user->usertype = "";
			}
 

			$user->groupid = $post['groupname'];
			$user->notification = $post['notification'];
			$user->no10contactsbox = $post['no10contactsbox'];
			
			
			if(strlen($post['shortname']) > 2)
			{
    			$user->shortname = mb_substr($post['shortname'], 0, 2, "UTF-8"); ;
			} 
			else 
			{
    			$user->shortname = $post['shortname'];
			}

			$user->bank_name = $post['bank_name'];
			$user->bank_account_number = $post['bank_account_number'];
			$user->bank_number = $post['bank_number'];
			$user->iban = $post['iban'];
			$user->bic = $post['bic'];
			$user->ikusernumber = $post['ikusernumber'];
			$user->dashboard_limit = $post['dashboard_limit'];
			$user->control_number = $post['control_number'];
			if(!empty($post['meeting_attendee']))
			{
				$user->meeting_attendee = $post['meeting_attendee'];
			}
			else
			{
				$user->meeting_attendee = '0';
			}
			$user->roster_shortcut = $post['roster_shortcut'];
			$user->default_stampusers = $user_stemp[0];
			$user->default_stampid = $user_stemp[1];
			
			//ispc-1802
			if(isset($post['isactive'],$post['isactive_date'])){
				if( $post['isactive'] == 0 ){
					$user->isactive = 0;
					$user->isactive_date = '';
				}
				else{
					$user->isactive = 1;
					if (empty($post['isactive_date'])){
						$user->isactive_date = '';
					}else{
						$user->isactive_date = date("Y-m-d", strtotime($post['isactive_date']));
					}
				}
			}
			//ispc-1533
			if(isset($post['makes_visits'])){
				$user->makes_visits = (int)$post['makes_visits']; 
			}
			
			//ISPC-2272 (@ancuta 23.10.2018)
			$user->debitor_number = $post['debitor_number'];
			//---
					
			
			//ISPC-2272 Ancuta 30-31.03.2020
			$user->user_specific_account = $post['user_specific_account'];
			//---
			
			$user->save();
			return $emailchg;
		}

		public function update_notification_settings($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_id = $logininfo->userid;

			if(empty($post['sapv_enabled']))
			{
				$post['sapv_enabled'] = '0';
			}
			
			if(empty($post['sapv_noinf_enabled']))
			{
				$post['sapv_noinf_enabled'] = '0';
			}

			$user = Doctrine::getTable('User')->find($user_id);
			$user->notification = $post['notification'];
			$user->save();

			$conn = Doctrine_Manager::getInstance()->getConnection('SYSDAT');
			//ISPC-1547 added  patient_hospital_admission and  patient_hospital_discharge: 13.08.2019
			//ISPC-2432
			$q = 'INSERT INTO `notification_settings` (`user_id`, `admission`, `discharge`, `sixweeks`, `fourwnote`, `krise`, `wlvollversorgung`, `wlvollversorgung_25days`,`dashboard_display_patbirthday`, `sapv_enabled`, `sapv_popup`, `sapv_noinf_enabled`, `sapv_noinf_popup`, `medication_acknowledge`, `todo`,`medication_interval`, `medication_doctor_receipt`,`dashboard_grouped`,`mePatient_device_uploads`,`patient_hospital_admission`,`patient_hospital_discharge`) VALUES
			("' . $user_id . '", "' . $post['admission'] . '",
				"' . addslashes($post['discharge']) . '",
				"' . addslashes($post['sixweeks']) . '",
				"' . addslashes($post['fourwnote']) . '",
				"' . addslashes($post['krise']) . '",
				"' . addslashes($post['wlvollversorgung']) . '",
				"' . addslashes($post['wlvollversorgung_25days']) . '",
				"' . addslashes($post['dashboard_display_patbirthday']) . '",
				"' . addslashes($post['sapv_enabled']) . '",
				"' . addslashes($post['sapv_popup']) . '",
				"' . addslashes($post['sapv_noinf_enabled']) . '",
				"' . addslashes($post['sapv_noinf_popup']) . '",
				"' . addslashes($post['medication_acknowledge']) . '",
				"' . addslashes($post['todo']) . '",
				"' . addslashes($post['medication_interval']) . '",
				"' . addslashes($post['medication_doctor_receipt']) . '",
				"' . addslashes($post['dashboard_grouped']) . '",
				"' . addslashes($post['mePatient_device_uploads']) . '",
				"' . addslashes($post['patient_hospital_admission']) . '",
				"' . addslashes($post['patient_hospital_discharge']) . '")
			ON DUPLICATE KEY UPDATE
				admission="' . addslashes($post['admission']) . '",
				discharge = "' . addslashes($post['discharge']) . '",
				sixweeks = "' . addslashes($post['sixweeks']) . '",
				fourwnote = "' . addslashes($post['fourwnote']) . '",
				krise = "' . addslashes($post['krise']) . '",
				wlvollversorgung = "' . addslashes($post['wlvollversorgung']) . '",
				wlvollversorgung_25days = "' . addslashes($post['wlvollversorgung_25days']) . '",
				dashboard_display_patbirthday = "' . addslashes($post['dashboard_display_patbirthday']) . '",
				sapv_enabled = "' . addslashes($post['sapv_enabled']) . '",
				sapv_popup = "' . addslashes($post['sapv_popup']) . '",
				sapv_noinf_enabled = "' . addslashes($post['sapv_noinf_enabled']) . '",
				sapv_noinf_popup = "' . addslashes($post['sapv_noinf_popup']) . '",
				medication_acknowledge = "' . addslashes($post['medication_acknowledge']) . '",
				todo = "' . addslashes($post['todo']) . '",
				medication_interval = "' . addslashes($post['medication_interval']) . '",		
				medication_doctor_receipt = "' . addslashes($post['medication_doctor_receipt']) . '",		
				dashboard_grouped = "' . addslashes($post['dashboard_grouped']) . '",		
                mePatient_device_uploads = "' . addslashes($post['mePatient_device_uploads']) . '",
                patient_hospital_admission = "' . addslashes($post['patient_hospital_admission']) . '",
                patient_hospital_discharge = "' . addslashes($post['patient_hospital_discharge']) . '"
				    ;';
			$r = $conn->execute($q);
		}

		public function deactivate_user($userid)
		{
			$user = Doctrine::getTable('User')->find($userid);
			$user->isactive = "1";
			$user->isdelete = "1";
			$user->save();
		}

		public function reactivate_user($userid)
		{
			$user = Doctrine::getTable('User')->find($userid);
			$user->isactive = "0";
			$user->isdelete = "0";
			$user->save();
		}

		//medication change  users
		public function save_med_change_users($post)
		{
			$clear_med_change_users = self::clear_med_change_users();
			$save_selected_users = self::insert_med_change_users($post);
		}
		//medication approval  users
		public function save_med_approval_users($post)
		{
			$clear_med_approval_users = self::clear_med_approval_users();
			$save_selected_users = self::insert_med_approval_users($post);
		}
		//print users
		public function save_print_users($post)
		{
			$clear_print_users = self::clear_print_users();
			$save_selected_users = self::insert_print_users($post);
		}

		public function check_printuser_assigned($client = false, $user = false)
		{
			//retur true if user is assigned
			if($client && $user)
			{
				$check = Doctrine_Query::create()
					->select("*")
					->from("PrintUsersAssigned")
					->where('isdelete="0"')
					->andWhere('client="' . $client . '"')
					->andWhere('user="' . $user . '"');
				$check_res = $check->fetchArray();

				if($check_res)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public function insert_print_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['user_id']))
			{
				$post['user_id'] = array_values(array_unique($post['user_id']));

				foreach($post['user_id'] as $k_usr_id => $v_usr_id)
				{
					$insert_data[] = array(
						'client' => $clientid,
						'user' => $v_usr_id,
						'isdelete' => "0"
					);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('PrintUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}

		public function insert_med_change_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['user_id']))
			{
				$post['user_id'] = array_values(array_unique($post['user_id']));

				foreach($post['user_id'] as $k_usr_id => $v_usr_id)
				{
					$insert_data[] = array(
						'client' => $clientid,
						'user' => $v_usr_id,
						'isdelete' => "0"
					);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('MedicationChangeUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}
		public function insert_med_approval_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['user_id']))
			{
				$post['user_id'] = array_values(array_unique($post['user_id']));

				foreach($post['user_id'] as $k_usr_id => $v_usr_id)
				{
					$insert_data[] = array(
						'client' => $clientid,
						'user' => $v_usr_id,
						'isdelete' => "0"
					);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('MedicationApprovalUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}

		public function clear_print_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('PrintUsers')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}
		public function clear_med_change_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('MedicationChangeUsers')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}
		
		public function clear_med_approval_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('MedicationApprovalUsers')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function assign_print_users($post)
		{
			$receipt_log = new Application_Form_ReceiptLog();
			$receipts_form = new Application_Form_Receipts();
			$assigned_users_mdl = new PrintUsersAssigned();
			$receipts_mdl = new Receipts();

			if($post['receipt'] > '0' && !empty($post['receipt']))
			{
				//get previously assigned users
				$assigned_users = $assigned_users_mdl->get_receipt_assigned_users($post['client'], $post['receipt']);

				//do some magic to find out what was unassigned
				//1.if we have saved(assigned) users and we have submitted users then make a diff to see who will be unassigned
				if(!empty($assigned_users) && !empty($post['assign_users']))
				{
					$unassigned_users_arr = array_diff($assigned_users[$post['receipt']], $post['assign_users']);
					$unassigned_users_arr = array_values($unassigned_users_arr);

					//if submitted users are not in the old assigned users then, and only then the user is newly assigned
					foreach($post['assign_users'] as $k_usr => $v_user_id)
					{
						if(!in_array($v_user_id, $assigned_users[$post['receipt']]))
						{
							$assigned_users_arr[] = $v_user_id;
						}
					}
				}

				//2.if there are no assigned(saved) and submitted users exists then nobody was unassigned (all submitted users are assigned)
				if(empty($assigned_users) && !empty($post['assign_users']))
				{
					$assigned_users_arr = $post['assign_users'];
				}

				//3.if there are no submitted users then nobody was assigned (all saved(assigned) users are now to be unassigned)
				if(empty($post['assign_users']) && !empty($assigned_users))
				{
					$unassigned_users_arr = $assigned_users;
				}

				//write in log both assigned and unassigned values
				if(!empty($assigned_users_arr))
				{
					//write assigned users in log
					$ipid = $post['ipid'];
					$client = $post['client'];
					$data['user'] = $post['user'];
					$data['receipt'] = $post['receipt'];
					$data['date'] = date('Y-m-d H:i:s', time());
					$data['operation'] = "assign";
					$data['involved_users'] = $assigned_users_arr;
					$data['assign_type'] = $post['assign_users_frm'];

					//update status on assign
					$post_data_result = $receipts_mdl->get_receipt($post['receipt']);

					$receipt_log = new Application_Form_ReceiptLog();
//					$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
					//assign print_users update status with red
					if(substr($post_data_result['receipt_status'], '1', '1') == "w")
					{
						$upd_status['status'] = substr($post_data_result['receipt_status'], '0', '1') . "r" . substr($post_data_result['receipt_status'], '2', '1');
					}

					$upd_status['receipt'] = $post['receipt'];
					$upd_status['silent'] = "1";
					$update_status = $receipts_form->update_receipt_status($upd_status);
					//save log
					$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
				}

				if(!empty($unassigned_users_arr))
				{
					//write unassigned users in log
					$ipid = $post['ipid'];
					$client = $post['client'];
					$data['user'] = $post['user'];
					$data['receipt'] = $post['receipt'];
					$data['date'] = date('Y-m-d H:i:s', time());
					$data['operation'] = "unassign";
					$data['involved_users'] = $unassigned_users_arr;
					$data['assign_type'] = $post['assign_users_frm'];

					//save log
					$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
				}

				//proceed to clear all previously assigned users and add submitted ones
				$clear_old_assignations = self::clear_assigned_print_users($post['receipt']);
				$add_new_assignations = self::add_new_assignations($post);
			}
		}

		public function clear_assigned_print_users($receipt)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('PrintUsersAssigned')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('receipt = "' . $receipt . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function add_new_assignations($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($post['receipt'] > '0')
			{
				foreach($post['assign_users'] as $k_assign => $v_assign)
				{
					$ins_assigned[] = array(
						'client' => $clientid,
						'user' => $v_assign,
						'receipt' => $post['receipt'],
						'isdelete' => "0"
					);
				}

				if(!empty($ins_assigned))
				{
					$collection = new Doctrine_Collection('PrintUsersAssigned');
					$collection->fromArray($ins_assigned);
					$collection->save();
				}
			}
		}

		//fax users
		public function save_fax_users($post)
		{
			$clear_fax_users = self::clear_fax_users();
			$save_selected_users = self::insert_fax_users($post);
		}

		public function check_faxuser_assigned($client = false, $user = false)
		{
			//retur true if user is assigned
			if($client && $user)
			{
				$check = Doctrine_Query::create()
					->select("*")
					->from("FaxUsersAssigned")
					->where('isdelete="0"')
					->andWhere('client="' . $client . '"')
					->andWhere('user="' . $user . '"');
				$check_res = $check->fetchArray();

				if($check_res)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public function insert_fax_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['user_id']))
			{
				$post['user_id'] = array_values(array_unique($post['user_id']));

				foreach($post['user_id'] as $k_usr_id => $v_usr_id)
				{
					$insert_data[] = array(
						'client' => $clientid,
						'user' => $v_usr_id,
						'isdelete' => "0"
					);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('FaxUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}

		public function clear_fax_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('FaxUsers')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function assign_fax_users($post)
		{
			$receipt_log = new Application_Form_ReceiptLog();
			$assigned_users_mdl = new FaxUsersAssigned();
			$receipts_mdl = new Receipts();
			$receipts_form = new Application_Form_Receipts();


			if($post['receipt'] > '0' && !empty($post['receipt']))
			{
				//get previously assigned users
				$assigned_users = $assigned_users_mdl->get_receipt_assigned_users($post['client'], $post['receipt']);

				//do some magic to find out what was unassigned
				//1.if we have saved(assigned) users and we have submitted users then make a diff to see who will be unassigned
				if(!empty($assigned_users) && !empty($post['assign_users']))
				{
					$unassigned_users_arr = array_diff($assigned_users[$post['receipt']], $post['assign_users']);
					$unassigned_users_arr = array_values($unassigned_users_arr);

					//if submitted users are not in the old assigned users then, and only then the user is newly assigned
					foreach($post['assign_users'] as $k_usr => $v_user_id)
					{
						if(!in_array($v_user_id, $assigned_users[$post['receipt']]))
						{
							$assigned_users_arr[] = $v_user_id;
						}
					}
				}

				//2.if there are no assigned(saved) and submitted users exists then nobody was unassigned (all submitted users are assigned)
				if(empty($assigned_users) && !empty($post['assign_users']))
				{
					$assigned_users_arr = $post['assign_users'];
				}

				//3.if there are no submitted users then nobody was assigned (all saved(assigned) users are now to be unassigned)
				if(empty($post['assign_users']) && !empty($assigned_users))
				{
					$unassigned_users_arr = $assigned_users;
				}

				//write in log both assigned and unassigned values
				if(!empty($assigned_users_arr))
				{
					//write assigned users in log
					$ipid = $post['ipid'];
					$client = $post['client'];
					$data['user'] = $post['user'];
					$data['receipt'] = $post['receipt'];
					$data['date'] = date('Y-m-d H:i:s', time());
					$data['operation'] = "assign";
					$data['involved_users'] = $assigned_users_arr;
					$data['assign_type'] = $post['assign_users_frm'];

					//update status on assign
					$post_data_result = $receipts_mdl->get_receipt($post['receipt']);

					$receipt_log = new Application_Form_ReceiptLog();
//					$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
					//assign print_users update status with red
					if(substr($post_data_result['receipt_status'], '2', '1') == "w")
					{
						$upd_status['status'] = substr($post_data_result['receipt_status'], '0', '1') . substr($post_data_result['receipt_status'], '1', '1') . "r";
					}

					$upd_status['receipt'] = $post['receipt'];
					$upd_status['silent'] = "1";
					$update_status = $receipts_form->update_receipt_status($upd_status);

					//save log
					$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
				}

				if(!empty($unassigned_users_arr))
				{
					//write unassigned users in log
					$ipid = $post['ipid'];
					$client = $post['client'];
					$data['user'] = $post['user'];
					$data['receipt'] = $post['receipt'];
					$data['date'] = date('Y-m-d H:i:s', time());
					$data['operation'] = "unassign";
					$data['involved_users'] = $unassigned_users_arr;
					$data['assign_type'] = $post['assign_users_frm'];

					//save log
					$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
				}

				//proceed to clear all previously assigned users and add submitted ones
				$clear_old_assignations = self::clear_assigned_fax_users($post['receipt']);
				$add_new_assignations = self::add_new_fax_assignations($post);
			}
		}

		public function clear_assigned_fax_users($receipt)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('FaxUsersAssigned')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('receipt = "' . $receipt . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function add_new_fax_assignations($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($post['receipt'] > '0')
			{
				foreach($post['assign_users'] as $k_assign => $v_assign)
				{
					$ins_assigned[] = array(
						'client' => $clientid,
						'user' => $v_assign,
						'receipt' => $post['receipt'],
						'isdelete' => "0"
					);
				}

				if(!empty($ins_assigned))
				{
					$collection = new Doctrine_Collection('FaxUsersAssigned');
					$collection->fromArray($ins_assigned);
					$collection->save();
				}
			}
		}

		public function save_sh_internal_users($post)
		{
			$clear_shinternal_ers = self::clear_shinternal_users();
			$save_selected_users = self::insert_shinternal_users($post);
		}

		public function clear_shinternal_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('ShInternalUsers')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function insert_shinternal_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['user_id']))
			{
				$post['user_id'] = array_values(array_unique($post['user_id']));

				foreach($post['user_id'] as $k_usr_id => $v_usr_id)
				{
					$insert_data[] = array(
						'client' => $clientid,
						'user' => $v_usr_id,
						'isdelete' => "0"
					);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('ShInternalUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}
		
		// ISPC-2257
		public function save_sh_shifts_internal_users($post)
		{
			$clear_shinternal_ers = self::clear_shshiftsinternal_users();
			$save_selected_users = self::insert_shshiftsinternal_users($post);
		}

		public function clear_shshiftsinternal_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());

			$q = Doctrine_Query::create()
				->update('ShShiftsInternalUsers')
				->set('isdelete', "1")
				->set('change_user', $userid)
				->set('change_date', '"' . $change_date . '"')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

		public function insert_shshiftsinternal_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['user_id']))
			{
				$post['user_id'] = array_values(array_unique($post['user_id']));

				foreach($post['user_id'] as $k_usr_id => $v_usr_id)
				{
					$insert_data[] = array(
						'client' => $clientid,
						'user' => $v_usr_id,
						'isdelete' => "0"
					);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('ShShiftsInternalUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}
		// ISPC-2257 end
		

		public function update_user_group($post)
		{
			if($post['user_id']){
				
				$user = Doctrine::getTable('User')->find($post['user_id']);
				$user->groupid = $post['group_id'];
				$user->save();
				return true;
			} else{
				return false;
			}
			
		}
		
		//ispc 1817
		// in team/teamlist add a comment for each user
		public function add_user_comment($userid = false, $clientid = false, $comment = '')
		{
			$user = Doctrine::getTable('User')->findbyIdAndClientid($userid, $clientid);
			if ($user{0}['id']){
				$user = $user{0};
				$user->comment = $comment;
				$user->save();
				
				return true;
			}

			return false;
			
		}
		// ISPC-2018
		public function save_pat_file_tags_rights($post)
		{
			//var_dump($post); exit;
			foreach($post['user_id'] as $ku=>$vu)
			{
				if($vu != '')
				{
					$userfilestagsrigts = implode(',', $post['userrights_id'][$vu]);
					if($userfilestagsrigts == null)
					{
						$userfilestagsrigts = '';
					}
			
					$user = Doctrine::getTable('User')->find($vu);
			
					if($user->patient_file_tag_rights != $userfilestagsrigts)
					{
						$user->patient_file_tag_rights = $userfilestagsrigts;
						$user->save();
					}
				}
			}
		}

		
		// ISPC-2157
 		// complaint mana
		public function save_complaint_users($post)
		{
			$clear_compaiment_users = self::clear_complaint_management_users();
			$save_selected_users = self::insert_complaint_users($post);
		}
		
		public function insert_complaint_users($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
// 		dd($post);
			if(!empty($post['user']))
			{
				foreach($post['user'] as $k_usr_id => $v_usr_case)
				{   
					$open_case= 0;
					$close_case= 0;
					
					if($v_usr_case['open_case'] == "1"){
						$open_case= 1;
					}
					
					if($v_usr_case['close_case'] == "1"){
						$close_case= 1;
					}
						$insert_data[] = array(
								'clientid' => $clientid,
								'isdelete' => "0",
								'userid' => $k_usr_id,
								'open_case' => $open_case,
								'close_case' => $close_case
						);
				}

				if(!empty($insert_data))
				{
					$collection = new Doctrine_Collection('ComplaintUsers');
					$collection->fromArray($insert_data);
					$collection->save();
				}
			}
		}
		public function clear_complaint_management_users()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$change_date = date('Y-m-d H:i:s', time());
		
			$q = Doctrine_Query::create()
			->update('ComplaintUsers')
			->set('isdelete', "1")
			->set('change_user', $userid)
			->set('change_date', '"' . $change_date . '"')
			->where('clientid = "' . $clientid . '"')
			->andWhere('isdelete = "0"');
			$q->execute();
		}
		
		
		
		
		public function create_form_informaboutfamilydoctor( $options = array(), $elementsBelongTo = null , $sort_order = array())
		{
		    
		    $subform = $this->subFormTable(array(
		        'columns' => array(
		            'cnt' => '#',
		            'un' => $this->translate('username'),
		            'ut' => $this->translate('title'),
		            'ln' => $this->translate('last_name'),
		            'fn' => $this->translate('first_name'),
		            'all_click' => "<input type='checkbox' />"
	            ),
		        'class' => 'informaboutfamilydoctorTable datatable',
		    ));
		    
		    $subform->setAttrib("class", "label_same_size_auto");
// 		    $subform->setAttrib("class", "label_same_size");
		    
		    $subform->removeDecorator('Fieldset');
		    //$subform->setLegend($this->translate('Family doctor settings'));
		
		    if ( ! is_null($elementsBelongTo)) {
		        $subform->setOptions(array(
		            'elementsBelongTo' => $elementsBelongTo
		        ));
		    }
		     
		    
		    $users = User::get_AllByClientid($this->logininfo->clientid, array('us.*', 'username'));
		    
		    //remove inactive and deleted, and the ones with clientid=0
		    $users = array_filter($users, function($user) {
		        return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0);
		    });
		    
	        //sort by column
		    if ( ! empty($users) && ! empty($sort_order) ) {
		        
		        usort($users, array(new Pms_Sorter($sort_order ['column']), "_strnatcasecmp"));
		        
		        if ($sort_order ['order'] == 'DESC') {
		            $users = array_reverse($users);
		        }
		        
		    }
		    
		    
		    $cnt = 0;
		    foreach ($users as $user) {

		        $cnt++;
		        
		        $subform->addElement('note',  'cnt_'.$user['id'], array(
		            'value'        => $cnt,
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'username_'.$user['id'], array(
		            'value'        => $user['username'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'user_title_'.$user['id'], array(
		            'value'        => $user['user_title'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'last_name_'.$user['id'], array(
		            'value'        => $user['last_name'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'first_name_'.$user['id'], array(
		            'value'        => $user['first_name'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        
		        $subform->addElement('checkbox',  $user['id'], array(
		            //'isArray'      => true,
		            //'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
		            'value'        => $user['UserSettings']['manual_familydoc_message'] == 'yes' ? 'yes' : 'no',
		            'multiOptions' => array( 'yes' , 'no'),
		            'checkedValue' => 'yes',
		            'uncheckedValue' => 'no',
		            'required'     => false,
		            'filters'      => array('StringTrim'),
		            'validators'   => array('NotEmpty'),
		            'decorators' => array(
		                'ViewHelper',
		                array('Errors'),
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		            ),
		            'separator' => PHP_EOL,
		
		            'belongsTo' => 'manual_familydoc_message',
		
		           // 'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('tr')).show()} else {\$('.show_hide', \$(this).parents('tr')).hide()}",
		        ));
		         
		    }
		     
		     
		    return $subform;
		}
		
		
		public function save_form_informaboutfamilydoctor($data = array())
		{
		    if (empty($data['manual_familydoc_message']) || ! is_array($data['manual_familydoc_message'])) {
		        return; //nothing to save 
		    } 
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $user_IDs = array_keys($data['manual_familydoc_message']);
		    
		    if ( ! User::assert_User_belongsTo_Client($user_IDs)) {
    	        throw new Exception('Contact Admin, formular cannot be saved. #assert_User_belongsTo_Client', 0);
		    }
		    $af_us = new Application_Form_UserSettings();
		    foreach ($data['manual_familydoc_message'] as $userid => $manual_familydoc_message) {
		        
		        $save_data = array(
		            'hidden_patient_icons' => 0, // so we don't update the icons settings
		            'userid'  => $userid,
		            'manual_familydoc_message'  => $manual_familydoc_message == 'yes' ? 'yes' : 'no',
		        );
		        
		        $af_us->UpdateData($save_data);
		    }
		    
		    
		    
		    //verify that the users you want to update are yours and not from other client
		    return true;
		
		}	
		
 
		
		/**
		 * TODO-3462 Ancuta 19.10.2020
		 * @param array $options
		 * @param unknown $elementsBelongTo
		 * @param array $sort_order
		 * @return Zend_Form_SubForm
		 */
		public function create_form_informaboutnotacceptedrequest( $options = array(), $elementsBelongTo = null , $sort_order = array())
		{
		    
		    $subform = $this->subFormTable(array(
		        'columns' => array(
		            'cnt' => '#',
		            'un' => $this->translate('username'),
		            'ut' => $this->translate('title'),
		            'ln' => $this->translate('last_name'),
		            'fn' => $this->translate('first_name'),
		            'all_click' => "<input type='checkbox' />"
	            ),
		        'class' => 'informaboutnotacceptedrequest datatable',
		    ));
		    
		    $subform->setAttrib("class", "label_same_size_auto");
// 		    $subform->setAttrib("class", "label_same_size");
		    
		    $subform->removeDecorator('Fieldset');
		    //$subform->setLegend($this->translate('Family doctor settings'));
		
		    if ( ! is_null($elementsBelongTo)) {
		        $subform->setOptions(array(
		            'elementsBelongTo' => $elementsBelongTo
		        ));
		    }
		     
		    
		    $users = User::get_AllByClientid($this->logininfo->clientid, array('us.*', 'username'));
		    
		    //remove inactive and deleted, and the ones with clientid=0
		    $users = array_filter($users, function($user) {
		        return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0);
		    });
		    
	        //sort by column
		    if ( ! empty($users) && ! empty($sort_order) ) {
		        
		        usort($users, array(new Pms_Sorter($sort_order ['column']), "_strnatcasecmp"));
		        
		        if ($sort_order ['order'] == 'DESC') {
		            $users = array_reverse($users);
		        }
		        
		    }
		    
		    
		    $cnt = 0;
		    foreach ($users as $user) {

		        $cnt++;
		        
		        $subform->addElement('note',  'cnt_'.$user['id'], array(
		            'value'        => $cnt,
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'username_'.$user['id'], array(
		            'value'        => $user['username'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'user_title_'.$user['id'], array(
		            'value'        => $user['user_title'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'last_name_'.$user['id'], array(
		            'value'        => $user['last_name'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        $subform->addElement('note',  'first_name_'.$user['id'], array(
		            'value'        => $user['first_name'],
		            'required'     => false,
		            'decorators' => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            ),
		            'separator' => PHP_EOL,
		        ));
		        
		        $subform->addElement('checkbox',  $user['id'], array(
		            //'isArray'      => true,
		            //'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
		            'value'        => $user['UserSettings']['manual_not_accepted_req_message'] == 'yes' ? 'yes' : 'no',
		            'multiOptions' => array( 'yes' , 'no'),
		            'checkedValue' => 'yes',
		            'uncheckedValue' => 'no',
		            'required'     => false,
		            'filters'      => array('StringTrim'),
		            'validators'   => array('NotEmpty'),
		            'decorators' => array(
		                'ViewHelper',
		                array('Errors'),
		                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		            ),
		            'separator' => PHP_EOL,
		
		            'belongsTo' => 'manual_not_accepted_req_message',
		
		           // 'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('tr')).show()} else {\$('.show_hide', \$(this).parents('tr')).hide()}",
		        ));
		         
		    }
		     
		     
		    return $subform;
		}
		
		
        /**
         * TODO-3462 Ancuta 19.10.2020
         * @param array $data
         * @throws Exception
         * @return void|boolean
         */	
		public function save_form_informaboutnotacceptedrequest($data = array())
		{
		    if (empty($data['manual_not_accepted_req_message']) || ! is_array($data['manual_not_accepted_req_message'])) {
		        return; //nothing to save 
		    } 
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $user_IDs = array_keys($data['manual_not_accepted_req_message']);
		    
		    if ( ! User::assert_User_belongsTo_Client($user_IDs)) {
    	        throw new Exception('Contact Admin, formular cannot be saved. #assert_User_belongsTo_Client', 0);
		    }
		    $af_us = new Application_Form_UserSettings();
		    foreach ($data['manual_not_accepted_req_message'] as $userid => $manual_not_accepted_req_message) {
		        
		        $save_data = array(
		            'hidden_patient_icons' => 0, // so we don't update the icons settings
		            'userid'  => $userid,
		            'manual_not_accepted_req_message'  => $manual_not_accepted_req_message == 'yes' ? 'yes' : 'no',
		        );
		        
		        $af_us->UpdateData($save_data);
		    }
		    
		    
		    
		    //verify that the users you want to update are yours and not from other client
		    return true;
		
		}	
	}
	

?>