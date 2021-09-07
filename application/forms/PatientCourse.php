<?php

require_once("Pms/Form.php");

class Application_Form_PatientCourse extends Pms_Form
{
	
	public $extra_params_mainpage =  array();
	
	public function validate($post) {
	    $logininfo = new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		
		$post_date_array = array();
		$day = "";
		$month = "";
		$year = "";
		
		$previleges = new Modules();
			
    	$special_date_shortcuts = array('U', 'V', 'KX'); //ISPC-2641, elena, 09.09.2020, KX added
		//Shortcut LNR module || Bavaria 
		$modulepriv = $previleges->checkModulePrivileges("55", $logininfo->clientid);
		$modulepriv_bav = $previleges->checkModulePrivileges("60", $logininfo->clientid);
		//Maria:: Migration CISPC to ISPC 20.08.2020 
        $modulepriv_g = $previleges->checkModulePrivileges("1010", $logininfo->clientid); //ISPC-2651, elena, 20.08.2020
		$modulepriv_hb = $previleges->checkModulePrivileges("121", $logininfo->clientid);
		
		$modulepriv_le = $previleges->checkModulePrivileges("128", $logininfo->clientid);
		$modulepriv_sd = 0; //$modulepriv_sd = $previleges->checkModulePrivileges("133", $logininfo->clientid); //deactivated on client request - Daniel
		$modulepriv_al = $previleges->checkModulePrivileges("134", $logininfo->clientid); //ISPC-1696

		$modulepriv_xs = $previleges->checkModulePrivileges("145", $logininfo->clientid); 
		$modulepriv_xe = $previleges->checkModulePrivileges("146", $logininfo->clientid); 

		if ($modulepriv || $modulepriv_bav)
		{
       		$special_date_shortcuts[] = 'XT';
		}
		if ($modulepriv_g ) //ISPC-2651, elena, 20.08.2020//Maria:: Migration CISPC to ISPC 20.08.2020 
		{
       		$special_date_shortcuts[] = 'G';
		}

		if ($previleges->checkModulePrivileges("182", $logininfo->clientid))
		{
       		$special_date_shortcuts[] = 'LS';
		}
		
		if ($modulepriv_hb)
		{
       		$special_date_shortcuts[] = 'HB';
		}
		
		if ($modulepriv_le)
		{
       		$special_date_shortcuts[] = 'LE';
		}
		if ($modulepriv_sd)
		{
			$special_date_shortcuts[] = 'SD'; 
		}
		
		if ($modulepriv_xs)
		{
			$special_date_shortcuts[] = 'XS'; 
		}
		
		if ($modulepriv_xe)
		{
			$special_date_shortcuts[] = 'XE'; 
		}
		
		//TODO-3897 Lore 15.03.2021
		if ($previleges->checkModulePrivileges("252", $logininfo->clientid))
		{
		    $special_date_shortcuts[] = 'WT';
		}
		
		if ($modulepriv_al)
		{
			//disabled for the moment
			//$special_date_shortcuts[] = 'AL'; 
		}		
		
		$module_companion_xt= $previleges->checkModulePrivileges("255", $logininfo->clientid);        //ISPC-2902 Lore 27.04.2021

		
		foreach($post['course_type'] as $k_course_type => $course_type)
		{
		    if(strtoupper($course_type) == 'SB')
			{
				$course_title_arr[$k_course_type] = explode(' |__| ', $post['course_title'][$k_course_type]);
				if($course_title_arr[$k_course_type][0] == '0' )
				{
					$this->error_message['shortcut'] = $Tr->translate('selectuser');
					$error = 1;
				}
				elseif(empty($course_title_arr[$k_course_type][1])  || $course_title_arr[$k_course_type][1] == "Sanitätshausbestellung" )
				{
					$this->error_message['shortcut'] = $Tr->translate('enter_order_content');
					$error = 1;
				}
			}
			elseif(strtoupper($course_type) == 'W') {
				
				$course_title_arr[$k_course_type] = explode(' |---------| ', $post['course_title'][$k_course_type]);
				if($course_title_arr[$k_course_type][1] == 'null' )
				{
					$this->error_message['shortcut'] = $Tr->translate('selectuser');
					$error = 1;
				}				
			}
		    elseif(strtoupper($course_type) == 'LE' && $modulepriv_le )
			{
				$course_title_arr[$k_course_type] = explode(' |____| ', $post['course_title'][$k_course_type]);
				if(strlen($course_title_arr[$k_course_type][0]) == '0' || empty($course_title_arr[$k_course_type][0])){
				    
    				$this->error_message['shortcut'] = $Tr->translate('no custom actions are allowed');
    				$error = 8;
				}
				
			}
			elseif(strtoupper($course_type) == 'AL' && $modulepriv_al)
			{
				$this_al = explode('|', $post['course_title'][$k_course_type]);
				array_walk($this_al, create_function('&$val', '$val = trim(rtrim($val));'));

				if ( (strlen(trim(rtrim($post['course_title'][$k_course_type]))) == 0)
				|| (empty($this_al[0]))
				|| (empty($this_al[1]))
				|| (empty($this_al[2]))
				)
				{
					$this->error_message['shortcut'] = $Tr->translate('settlement_services_error_insert');
					$error = 9;
				}else{
					//compare with dbf
					$ss = new SettlementServices();
					if (!$ss->validate_settlement_services($logininfo->clientid, $this_al[0],  $this_al[1])){
						$this->error_message['shortcut'] = $Tr->translate('settlement_services_error_insert');
						$error = 9;
					}
					
				}
			}
			else if(strtoupper($course_type) != 'S' && strtoupper($course_type) != 'P')
			{
				if((strlen(trim(rtrim($course_type))) > '0') && (strlen(trim(rtrim($post['course_title'][$k_course_type]))) == 0))
				{
					$this->error_message['shortcut'] = $Tr->translate('shortcuterror');
					$error = 1;
				}
			}
			
			
			if( in_array(strtoupper($course_type),$special_date_shortcuts))
			{

		        $course_title_arr[$k_course_type] = explode(' | ', $post['course_title'][$k_course_type]);
			    
			    if(strtoupper($course_type) == 'U')
			    {
			        $full_date_string[$k_course_type] = trim($course_title_arr[$k_course_type][3]); 
			        $full_date_array[$k_course_type] = explode(" ",trim($course_title_arr[$k_course_type][3]));
			        
			        $date[$k_course_type] = $full_date_array[$k_course_type][0]; 
			        $time[$k_course_type] = $full_date_array[$k_course_type][1];
			         
			    }
			    elseif(strtoupper($course_type) == 'LE' && $modulepriv_le )
			    {
			        $course_title_arr[$k_course_type] = explode('|____|', $post['course_title'][$k_course_type]);
			        
			        $full_date_string[$k_course_type] = trim($course_title_arr[$k_course_type][4]); 
			        $full_date_array[$k_course_type] = explode(" ",trim($course_title_arr[$k_course_type][4]));
			        
			        $date[$k_course_type] = $full_date_array[$k_course_type][0]; 
			        $time[$k_course_type] = $full_date_array[$k_course_type][1];
			         
			    }elseif(strtoupper($course_type) == 'AL' && $modulepriv_al )
			    {
					/*
					 * ISPC-1696 
					 * TODO future project request
					 */
			    }elseif(strtoupper($course_type) == 'KX') //ISPC-2641, elena, 09.09.2020
			    {
                    $full_date_string[$k_course_type] = trim($course_title_arr[$k_course_type][1]);
                    $full_date_array[$k_course_type] = explode(" ",trim($course_title_arr[$k_course_type][1]));

                    $date[$k_course_type] = $full_date_array[$k_course_type][0];
                    $time[$k_course_type] = $full_date_array[$k_course_type][1];
			    }
		/* 	    elseif(strtoupper($course_type) == 'XT' && $module_companion_xt)
			    {//ISPC-2902 Lore 27.04.2021
			        $full_date_string[$k_course_type] = trim($course_title_arr[$k_course_type][3]);
			        $full_date_array[$k_course_type] = explode(" ",trim($course_title_arr[$k_course_type][3]));
			        
			        $date[$k_course_type] = $full_date_array[$k_course_type][0];
			        $time[$k_course_type] = $full_date_array[$k_course_type][1];
			        
			    } */

			     else {
			        
			        $full_date_string[$k_course_type] = trim($course_title_arr[$k_course_type][2]); 
			        $full_date_array[$k_course_type] = explode(" ",trim($course_title_arr[$k_course_type][2]));
			        
			        $date[$k_course_type] = $full_date_array[$k_course_type][0]; 
			        $time[$k_course_type] = $full_date_array[$k_course_type][1];
			         
			    }
 
 
			    if(empty($date[$k_course_type]))
			    {
			        $this->error_message['date'] = $Tr->translate('date_is_mandatory');
			        $error = 2;
			    }
			    
			    if(strlen($date[$k_course_type])){
			        $post_date_array = explode(".",$date[$k_course_type]);
			        $day = $post_date_array[0];
			        $month = $post_date_array[1];
			        $year = $post_date_array[2];
			    }
			    	
			    if(checkdate($month,$day,$year) === false)
			    {
			        $this->error_message['date'] = $Tr->translate('date_error_invalid');
			        $error = 3;
			    }

			    if(date('Y', strtotime($date[$k_course_type])) < '2008')
			    {
			        $this->error_message['date'] = $Tr->translate('date_error_before_2008');
			        $error = 7;
			    }

    		     // ISPC-1486
			    if(
			    		strtoupper($course_type) == 'XT'
                        || (strtoupper($course_type) == 'G' && $modulepriv_g) // ISPC-2651, elena, 20.08.2020//Maria:: Migration CISPC to ISPC 20.08.2020 
                        || strtoupper($course_type) == 'LS'
                        || strtoupper($course_type) == 'KX'  // ISPC-2641, elena, 09.09.2020
			    		|| strtoupper($course_type) == 'U' // TODO-876
			            || strtoupper($course_type) == 'V' // TODO-2990 Lore 09.03.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
			    		|| strtoupper($course_type) == 'HB'  
			    		|| (strtoupper($course_type) == 'LE' && $modulepriv_le)  
			    		|| (strtoupper($course_type) == 'SD' && $modulepriv_sd)
			    		|| (strtoupper($course_type) == 'XS' && $modulepriv_xs)
			    		|| (strtoupper($course_type) == 'XE' && $modulepriv_xe)
			            || strtoupper($course_type) == 'WT'  //TODO-3897 Lore 15.03.2021
			        
			        )
			    {
			    
			   
    			    if(checkdate($month,$day,$year) && strtotime($date[$k_course_type]) > strtotime(date("d.m.Y", time())))
    			    {
    			        $this->error_message['date'] = $Tr->translate('err_datefuture');
    			        $error = 4;
    			    }
    			    
    			    if(preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $time[$k_course_type])){
    			        $time_arr = explode(":",$time[$k_course_type]);
    			        $full_date = mktime ( $time_arr[0], $time_arr[1],"0",  date("n",strtotime($date[$k_course_type])),date("j",strtotime($date[$k_course_type])), date("Y",strtotime($date[$k_course_type])) );
    			        if(checkdate($month,$day,$year) && $full_date > strtotime(date("d.m.Y H:i", time())) )
    			        {
    			            $this->error_message['date'] = $Tr->translate('err_datefuture');
    			            $error = 5;
    			        }
    			    }
    			    
			    }
			    
			    if(!preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $time[$k_course_type])){
			        $this->error_message['date'] = $Tr->translate('time_error_invalid');
			        $error = 6;
			    }
			}
		}
		
		if($error == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid = Pms_CommonData::getEpid($ipid);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$excluded_shortcuts = array('P', 'S');
		//TODO-3897 Lore 15.03.2021   --- added WT
		$special_shortcuts = array('U', 'V', 'XT','PB','KX',"HB","LE", "SD","AL","XS","XE", "LS", "WT");
		$tab_names = array("XT" => "phone_verlauf", "V"=>"koordination_verlauf", "U"=>"beratung_verlauf","KX"=>"comment_time","HB"=>"hb_verlauf","LE"=>"le_verlauf", "SD"=>"sd_verlauf" , "AL"=>"al_verlauf","XS"=>"xs","XE"=>"xe",
		    'LS' => 'lysocare_phone_verlauf',
		    'WT' => 'wt_phone_verlauf'
		);

		$previleges = new Modules();
		$modulepriv_le = $previleges->checkModulePrivileges("128", $logininfo->clientid);
		$modulepriv_sd = 0; //$modulepriv_sd = $previleges->checkModulePrivileges("133", $logininfo->clientid);//deactivated on client request - Daniel
		$modulepriv_al = $previleges->checkModulePrivileges("134", $logininfo->clientid);//ISPC-1696
		//Maria:: Migration CISPC to ISPC 20.08.2020 
        $modulepriv_g = $previleges->checkModulePrivileges("1010", $logininfo->clientid); //ISPC-2651, elena, 20.08.2020

        if ($modulepriv_g) //ISPC-2651,elena,20.08.2020
        {
            $special_shortcuts[] = 'G';
        }
		
  
        // Maria:: Migration ISPC to CISPC 08.08.2020
		$modulepriv_pk = $previleges->checkModulePrivileges("187", $logininfo->clientid);//ISPC-2387
		if ($modulepriv_pk)
		{
		    $special_shortcuts[] = 'PK';
		}
		$modulepriv_xn = $previleges->checkModulePrivileges("188", $logininfo->clientid);//ISPC-2387
		if ($modulepriv_xn)
		{
		    $special_shortcuts[] = 'XN';
		}
		
		
		$modulepriv_ml = $previleges->checkModulePrivileges("205", $logininfo->clientid);//TODO-2683
		if ($modulepriv_ml)
		{
		    $special_shortcuts[] = 'ML';
		}
		
		$modulepriv_rlp = $previleges->checkModulePrivileges("203", $logininfo->clientid);//ISPC-2486
		if ($modulepriv_rlp)
		{
		    $special_shortcuts[] = 'RK';
		    $special_shortcuts[] = 'RI';
		    $special_shortcuts[] = 'RO';
		    $special_shortcuts[] = 'RD';
		}
		
		
		$module209_demstepcare_special_sh = $previleges->checkModulePrivileges("209", $logininfo->clientid);//TODO-2749 Ancuta 13.12.2019
		if ($module209_demstepcare_special_sh)
		{
		    $special_shortcuts[] = 'DD';
		    $special_shortcuts[] = 'DC';
		}
		
		$module_xm = $previleges->checkModulePrivileges("217", $logininfo->clientid);//TODO-2942 Carmen 24.02.2020
		if ($module_xm)
		{
			$special_shortcuts[] = 'XM';
		}
		
		$module_xh = $previleges->checkModulePrivileges("218", $logininfo->clientid);//TODO-2942 Carmen 24.02.2020
		if ($module_xh)
		{
			$special_shortcuts[] = 'XH';
		}
		$module_xg = $previleges->checkModulePrivileges("219", $logininfo->clientid);//TODO-2942 Carmen 24.02.2020
		if ($module_xg)
		{
			$special_shortcuts[] = 'XG';
		}
		
		$module_companion_xt= $previleges->checkModulePrivileges("255", $logininfo->clientid);        //ISPC-2902 Ancuta 29.04.2021
		
		
		
		$usrs = new User();
		$postids = array();
		$post_users = $usrs->get_client_users($clientid);
		$usersdata = array();// TODO-3068 ISPC: TODO generate 09.04.2020
		foreach($post_users as $userdata) {
			$usersdata[$userdata['id']] = $userdata;
			$postids[] = $userdata['id'];
		}
		//print_R($post); exit;
		
		//patient info
		$patientmaster = new PatientMaster();
		$allpatientinfo = $patientmaster->getMasterData($decid,0);
		
		//notification settings for client users
		$notification = new Notifications;
		$notif_settings_users = $notification->get_notification_settings($postids);
			
		// get assigned users
		$qpas = new PatientQpaMapping();
		$patientqpa = $qpas->getAssignedUsers($ipid);
		
		$client_details_array = Client::getClientDataByid($logininfo->clientid);
		
		for ($i = 0; $i < sizeof($post['course_type']); $i++)
		{
			
			if (strlen($post['course_type'][$i]) > 0 && !in_array($post['course_type'][$i], $excluded_shortcuts))
			{
				if (in_array($post['course_type'][$i], $special_shortcuts))
				{
					if ($post['course_type'][$i] == 'W')
					{
						$course_title_arr = explode(' |---------| ', $post['course_title'][$i]);
						
					}
					else if ($post['course_type'][$i] == 'SB')
					{
						$course_title_arr = explode(' |__| ', $post['course_title'][$i]);
					}
					else if ($post['course_type'][$i] == 'LE' && $modulepriv_le )
					{
						$course_title_arr = explode(' |____| ', $post['course_title'][$i]);
					}
					else if ($post['course_type'][$i] == 'AL' && $modulepriv_al )
					{
						$course_title_arr = explode(' | ', $post['course_title'][$i]);
					}
					else
					{
						$course_title_arr = explode(' | ', $post['course_title'][$i]);
					}

					if (count($course_title_arr) > 0)
					{
						if(in_array($post['course_type'][$i], $special_shortcuts)) //if shortcut is a special one... get inserted date and time
						{
						    if(date('Y', strtotime($course_title_arr[count($course_title_arr) - 1])) != "1970"){
    							$done_date = date('Y-m-d H:i:s', strtotime($course_title_arr[count($course_title_arr) - 1]));
						    } else{
						        $done_date = date('Y-m-d H:i:s', time());
						    } 
							
						}
						else
						{
							$done_date = date('Y-m-d', strtotime($course_title_arr[count($course_title_arr) - 1]));
							$done_date = $done_date . ' ' . date('H:i:s', time());
						}

					}
					else
					{
						$done_date = date('Y-m-d H:i:s', time());
					}
				}
								
				// 1. get client shortcuts!
				$courses = new Courseshortcuts();
				$shortcut_id = $courses->getShortcutIdByLetter($post['course_type'][$i], $clientid);


				// 2. check if shortcut is shared
				$patient_share = new PatientsShare();
				$shared_data = $patient_share->check_shortcut($ipid, $shortcut_id);

				if ($shared_data)
				{
					foreach ($shared_data[$shortcut_id] as $shared)
					{
						// 3. salve to other patients
						$cust = new PatientCourse();
						$cust->ipid = $shared; //target ipid
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt($post['course_type'][$i]);
						$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['course_title'][$i]));
						$cust->user_id = $userid;
						$cust->source_ipid = $ipid;
						$cust->done_date = $done_date;
						$cust->done_name = $tab_names[$post['course_type'][$i]];
						$cust->save();
					}
				}

				
				
				if ($post['course_type'][$i] == 'LE' && $modulepriv_le)
				{
				    $post_action_user[$i] = "0" ;
				    $course_title_arrs = explode(' |____| ', $post['course_title'][$i]);
				    $post_action_user[$i] = trim($course_title_arrs[3]);
				    	
				    $post_action_id[$i] = trim($course_title_arrs[0]);
				    	
 
				    if(strlen($post_action_id[$i]) > 0 ){
				        	
				        $action[$i]= $post_action_id[$i];
				        
				        $xbdtact_insert = new PatientXbdtActions();
				        $xbdtact_insert->clientid = $logininfo->clientid;
				        $xbdtact_insert->userid = $post_action_user[$i];
				        if($post_action_user[$i] == "-1") {
				            $xbdtact_insert->team = "1";
				        } else {
				            $xbdtact_insert->team = "0";
				        }
				        $xbdtact_insert->ipid = $ipid;
				        $xbdtact_insert->action = $action[$i];
				        $xbdtact_insert->action_date = $done_date;;
				        $xbdtact_insert->save();
				
				        $xbdtaction_id = $xbdtact_insert->id;
				    }
				}
				
				if ($post['course_type'][$i] == 'W')
				{
					$course_title_arrs = explode(' |---------| ', $post['course_title'][$i]);
					$arrusers = explode(',', $course_title_arrs[1]);
					$groupuser = array();
					$singleuser = array();
					
					$alluser = 0;
					foreach($arrusers as $keyuser=>$arruser)
					{
						if(substr($arruser, 0, 1) == 'g')
						{
							$groupuser[$keyuser] = substr($arruser, 1);
						}
						elseif(substr($arruser, 0, 1) == 'u')
						{
							$singleuser[$keyuser] = substr($arruser, 1);
						}
						elseif(strpos($arruser, "pseudogroup_", 0) === 0)
						{
							//this elseif is 4fun so we bypass the default else
							//$pseudogroup[$keyuser] = substr($arruser, strlen('pseudogroup_'));							
						}
						else
						{
							$alluser = 1;
						}
					}				
					
					if($alluser == 1)
					{
						if(!empty($groupuser) || !empty($singleuser))
						{
	/* 						foreach($singleuser as $keyus=>$single)
							{
								unset($arrusers[$keyus]);
							}
							foreach($groupuser as $keyus=>$single)
							{
								unset($arrusers[$keyus]);
							} */
						}
						$course_title_arrs[1] = implode(',', $arrusers);
						$post['course_title'][$i] = implode(' |---------| ', $course_title_arrs);
					}
					
					elseif(!empty($groupuser) && !empty($singleuser))
					{
						foreach($singleuser as $keyus=>$single)
						{							
	/* 						if(in_array($usersdata[$single]['groupid'], $groupuser))
							{								
								unset($arrusers[$keyus]);
							} */
						}
							$course_title_arrs[1] = implode(',', $arrusers);
							$post['course_title'][$i] = implode(' |---------| ', $course_title_arrs);
					}
						
				}
				
				// !!!!!!!!!!!!!!!!!!!!!!
                // INSERT IN PATIENT COURSE 
				// !!!!!!!!!!!!!!!!!!!!!!
				
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt($post['course_type'][$i]);
				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['course_title'][$i]));
				$cust->user_id = $userid;
				if($modulepriv_le && $xbdtaction_id){
				    $cust->recordid = $xbdtaction_id;
				}
				$cust->done_date = $done_date;
				$cust->done_name = $tab_names[$post['course_type'][$i]];
				$cust->save();

				$insid = $cust->id;
				
				
				if ($xbdtaction_id && $modulepriv_le)
				{
				    $update_xbdtaction = Doctrine::getTable('PatientXbdtActions')->find($xbdtaction_id);
				    $update_xbdtaction->course_id = $insid;
				    $update_xbdtaction->save();
				}
				
				//ISPC-2902 Ancuta 29.04.2021
				if ($post['course_type'][$i] == 'XT' && $module_companion_xt){
				    if(isset($post['uSelect'][$i]) && $post['uSelect'][$i] != 0 ){
				        $vals[]=array(
				            'ipid'=>$ipid,
				            'patient_course_id'=>$insid,
				            'option_id'=>$post['uSelect'][$i],
				            'course_type'=>"XT"
				        );
				        $pco_insert = new PatientCourseOptions();
				        $pco_insert->ipid = $ipid;
				        $pco_insert->patient_course_id = $insid;
				        $pco_insert->option_id = $post['uSelect'][$i];
				        $pco_insert->course_type = "XT";
				        $pco_insert->save();
				    }
				}
				// --

				if ($post['course_type'][$i] == 'SB'){

					$post_sb_user[$i] = "0" ;
					$post_order_content[$i]="";
					$message_entry  ="";

					$course_title_arrs = explode(' |__| ', $post['course_title'][$i]);
					$post_sb_user[$i] = $course_title_arrs[0];
					$post_order_content[$i] = $course_title_arrs[1];

					// get Patient details for message informations
					$patientmaster = new PatientMaster();
					$allpatientinfo = $patientmaster->getMasterData($decid,0);

					$patname = $allpatientinfo['first_name'].', '.$allpatientinfo['last_name'];

					$pataddress = $allpatientinfo['street1'].', '.$allpatientinfo['zip'].' '.$allpatientinfo['city'];



					// get assigned users
					$qpas = new PatientQpaMapping();
					$patientqpa = $qpas->getAssignedUsers($ipid);

					// If selected user, not assigned, assign
					if(!in_array($post_sb_user[$i],$patientqpa['assignments'][$ipid])){

						$assign = new PatientQpaMapping();
						$assign->epid = $epid;
						$assign->userid = $post_sb_user[$i];
						$assign->clientid = $logininfo->clientid;
						$assign->assign_date = date("Y-m-d H:i:s",time());
						$assign->save();

						$vizibility = new PatientUsers();
						$vizibility->clientid = $logininfo->clientid;
						$vizibility->ipid = $ipid;
						$vizibility->userid = $post_sb_user[$i];
						$vizibility->create_date = date("Y-m-d H:i:s",time());
						$vizibility->save();
					}


					$message_entry .= "Sanitätshausbestellung an : \n " . $post_order_content[$i] . "\n";
					$message_entry .= "\n" . $patname . "\n" . $pataddress;

					
					
					
					
					
					
					
					
					
					//SEND MESSAGE
					if($post_sb_user[$i] != '0'){
						$sender = Doctrine::getTable('User')->find($logininfo->userid);
						$senderarray = $sender->toArray();

						if(count($senderarray)>0 && !empty($senderarray['last_name']))
						{
							$the_sender = $senderarray['first_name'].' '.$senderarray['last_name'];
						}
						$mail = new Messages();
						$mail->sender = $logininfo->userid;
						$mail->clientid = $logininfo->clientid;
						$mail->recipient = $post_sb_user[$i];
						$mail->msg_date = date("Y-m-d H:i:s", time());
						$mail->title = Pms_CommonData::aesEncrypt('Sanitätshausbestellung');
						$mail->content = Pms_CommonData::aesEncrypt($message_entry);
						$mail->create_date = date("Y-m-d", time());
						$mail->create_user = $logininfo->userid;
						$mail->read_msg = '0';
						$mail->save();

						
    					// ###################################
    					// ISPC-1600
    					// ###################################
    					$email_subject = $Tr->translate('youhavenewmailinispc').' - '.$the_sender.', '.date('d.m.Y H:i');
    					$email_text = "";
    					$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
    					// link to ISPC
    					//$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
    					// ISPC-2475 @Lore 31.10.2019
    					$email_text .= $Tr->translate('system_wide_email_text_login');
    					// client details
    					$client_details_array = Client::getClientDataByid($logininfo->clientid);
    					if(!empty($client_details_array)){
    					    $client_details = $client_details_array[0];
    					}
    					$client_details_string = "<br/>";
    					$client_details_string  .= "<br/> ".$client_details['team_name'];
    					$client_details_string  .= "<br/> ".$client_details['street1'];
    					$client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
    					$client_details_string  .= "<br/> ".$client_details['emailid'];
    					$email_text .= $client_details_string;
    					
						
    					//TODO-3164 Ancuta 08.09.2020
    					$email_data = array();
    					$email_data['client_info'] = $client_details_string;
    					$email_text = "";//overwrite
    					$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
    					//--
    					
						if ($mail->id > 0)
						{
							$user = Doctrine::getTable('User')->find($post_sb_user[$i]);
							$userarray = $user->toArray();

							if (count($userarray) > 0 && !empty($userarray['emailid']))
							{
								$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
								$mail = new Zend_Mail('UTF-8');
								$mail->setBodyHtml($email_text)
								->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
								->addTo($userarray['emailid'], $userarray['last_name'] . ' ' . $userarray['first_name'])
								->setSubject($email_subject)
								->setIpids($ipid)
								->send($mail_transport);
							}
						}
					}

				}
				
				if ($post['course_type'][$i] == 'W'){

					$message_entry  ="";
					
					$course_title_arrs = explode(' |---------| ', $post['course_title'][$i]);
					$post_w_users[$i] = explode(',', $course_title_arrs[1]);
					$post_w_content[$i] = $course_title_arrs[0];
				
					
					// get Patient details for message informations
					$patientmaster = new PatientMaster();
					$allpatientinfo = $patientmaster->getMasterData($decid,0);
// 					$patid = Pms_Uuid::encrypt($allpatientinfo['id']);;
					$patid = $_GET['id'];
					
					$patname = $allpatientinfo['first_name'].', '.$allpatientinfo['last_name'];
				
					$pataddress = $allpatientinfo['street1'].', '.$allpatientinfo['zip'].' '.$allpatientinfo['city'];
					
					//get users for groups assigned in todo and notification settings for users assigned in todo
					$post_w_userids = array();			
					$pseudogroup_ids =  array();
					
					foreach($post_w_users[$i] as $post_w_user) {
						
						if(substr(trim($post_w_user), 0, 1) != 'u' && strpos($post_w_user, ("pseudogroup_"), 0) === false ) {
							if(substr(trim($post_w_user), 0, 1) == 'g') {								
								foreach($post_users as $post_user) {
									if($post_user['groupid'] == substr(trim($post_w_user), 1)) {
										array_push($post_w_userids, $post_user['id']);
									}
								}								
							}
							else {								
								foreach($post_users as $post_user) {
									array_push($post_w_userids, $post_user['id']);
								}
								
							}
						}
						elseif (strpos($post_w_user, ("pseudogroup_"), 0) === 0) {
							$pseudogroup_ids[] = substr($post_w_user, strlen('pseudogroup_'));
						}
						else {
							array_push($post_w_userids, substr(trim($post_w_user), 1));
						}
					}
					
					if (! empty($pseudogroup_ids)) {
						//get the users from this pseudogroup
						$pgu_obj = new PseudoGroupUsers();
						$users_in_pseudogroups = $pgu_obj->get_users_by_groups($pseudogroup_ids);
						if ( ! empty($users_in_pseudogroups['all_user_id'])) {
							$post_w_userids = array_merge($post_w_userids , $users_in_pseudogroups['all_user_id']);
						}
					}
					//TODO-3280 Lore 15.07.2020
					$post_w_userids = array_unique($post_w_userids);
					
					// TODO-3068 ISPC: TODO generate 09.04.2020
					// corrected bug - in this foreach it was used : $userdata instead of $usersdata
					foreach($post_w_userids as $post_w_userid) {
						$mess = false;
						if($notif_settings_users[$post_w_userid]['todo'] != 'none') {
							if($notif_settings_users[$post_w_userid]['todo'] == 'assigned') {	
								if(in_array($post_w_userid, $patientqpa['assignments'][$ipid])){
									$mess = true;
								}
							}
							if($notif_settings_users[$post_w_userid]['todo'] == 'all') {
								$mess = true;
							}
						}
			
						$message_entry = "";          //TODO-3280 Lore 15.07.2020
						if($mess) {
// 							$message_entry .= $post_w_content[$i] . " für\n";
							//$message_entry .= " Ein neues TODO\n";
						    $message_entry .= " Ein neues TODO: ".$post['todo'][$i]." für\n";        //TODO-3280 Lore 15.07.2020
							$message_entry .= "\n" ;			
							$message_entry .= '<a href="'.APP_BASE.'patientcourse/patientcourse?id='.$patid.'">' . $patname.'</a>' ;			
							$message_entry .= "\n" . $pataddress;			
					
						
							//SEND MESSAGE
							if($post_w_userid != '0')
							{			
							    if(!empty($usersdata[$post_w_userid]['last_name']))
									{
									    $the_sender = $usersdata[$logininfo->userid]['first_name'].' '.$usersdata[$logininfo->userid]['last_name'];
								}
								
								$mail = new Messages();
								$mail->sender = $logininfo->userid;
								$mail->clientid = $logininfo->clientid;
								$mail->recipient = $post_w_userid;
								$mail->msg_date = date("Y-m-d H:i:s", time());
								$mail->title = Pms_CommonData::aesEncrypt('Ein neues TODO');
								$mail->content = Pms_CommonData::aesEncrypt($message_entry);
								$mail->create_date = date("Y-m-d", time());
								$mail->create_user = $logininfo->userid;
								$mail->read_msg = '0';
								$mail->save();
				
				
								// ###################################
								// ISPC-1600
								// ###################################
								$email_subject = $Tr->translate('youhavenewmailinispc'). ' ' . date('d.m.Y H:i');
								// TODO-3068 Ancuta 09.04.2020
								//$email_subject = $Tr->translate('youhavenewmailinispc').' - '.$the_sender.', '.date('d.m.Y H:i');
								$email_text = "";
								$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
								// link to ISPC
								//$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
								// ISPC-2475 @Lore 31.10.2019
								$email_text .= $Tr->translate('system_wide_email_text_login');
								// client details
						
								if(!empty($client_details_array)){
									$client_details = $client_details_array[0];
								}
								$client_details_string = "<br/>";
								$client_details_string  .= "<br/> ".$client_details['team_name'];
								$client_details_string  .= "<br/> ".$client_details['street1'];
								$client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
								$client_details_string  .= "<br/> ".$client_details['emailid'];
								$email_text .= $client_details_string;
							    
								//TODO-3164 Ancuta 08.09.2020
								$email_data = array();
								$email_data['client_info'] = $client_details_string;
								$email_text = "";//overwrite
								$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
								//-- 
								
								if ($mail->id > 0)
								{				
								    if (!empty($usersdata[$post_w_userid]['emailid']))
									{
										$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
										$mail = new Zend_Mail('UTF-8');
										$mail->setBodyHtml($email_text)
										->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
										->addTo($usersdata[$post_w_userid]['emailid'], $usersdata[$post_w_userid]['last_name'] . ' ' . $usersdata[$post_w_userid]['first_name'])
										->setSubject($email_subject)
										->setIpids($ipid)
										->send($mail_transport);
									}
								}
							}		
						}
					}
				}
				//ISPC-2163+2374
				if ($post['course_type'][$i] == 'V'){					
					
					$course_data = $course_title_arr;

					$call_date = date('Y-m-d H:i:00', strtotime($course_data[2]));
					$minutes = $course_data[0];

					$sp = new Sapsymptom();
					$sp->ipid = $ipid;
					//$sp->sapvalues = '999';
					$sp->sapvalues = '8,999';
					$sp->gesamt_zeit_in_minuten = $minutes;
					$sp->patient_course_id = $insid;
					$sp->save();
					
					if($sp->id)
					{
						$sp = Doctrine::getTable('Sapsymptom')->find($sp->id);
						$sp->create_date = $call_date; 
						$sp->save();
					}					
					
				}
			}
		}
		
		
		
		// ISPC-1619 
		$module_bielefeld_email = Modules::checkModulePrivileges("123", $logininfo->clientid);
		
		if($module_bielefeld_email)
		{
    		$mess = Messages::notdienst_action_messages($ipid,$userid);
		}
		
	}





	public function UpdateWrongEntry ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$clarr[] = $clientid;
		
		
		$grp = new Usergroup();
		$clgrp = $grp->get_clients_groups($clarr);
		
		$usrs = new User();
		$post_users = $usrs->get_client_users($clientid);
		foreach($post_users as $userdata) {
			$todousrsdata[$userdata['id']] = $userdata;
		}
		
		$pseudogroup_array = array();
		$all_user_id_with_todo = array();
		$all_groupid_with_todo = array();
		
		$exarr = explode(",", $post['ids']);

		foreach ($exarr as $key => $value)
		{
			$cust = Doctrine::getTable('PatientCourse')->find($value);
			$dead = array();
			if ($cust) {
				$dead = $cust->toArray();
			} else {
				continue;
			}


			$course_type = Pms_CommonData::aesDecrypt($dead['course_type']);
			
			//ISPC-2029 - delete vital-sign that was added from icon
			$decrypeted_course = Pms_CommonData::aesDecryptMultiple($dead);
			
			if( ($decrypeted_course['course_type'] == 'K' || $decrypeted_course['course_type'] == 'B')
					&& $decrypeted_course['done_name'] == 'vital_signs_icons' 
					&& $dead['done_id'] != 0 ) 
			{
				
				$update_vital_signs_obj = Doctrine_Query::create()
				->select('id')
				->from('FormBlockVitalSigns')
				->where('id =  ? ', $dead['done_id'])
				->andWhere('ipid = ? ' , $dead['ipid'])
				->andWhereIn("isdelete", array(0,1))
				->fetchOne();
				
				if( $post['val'] == '1' && $update_vital_signs_obj) {
					//delete the vitalsigns
					$update_vital_signs_obj->delete();
					
				} elseif($update_vital_signs_obj) {
					//restore the vitalsigns
					$update_vital_signs_obj->set("isdelete", "0")->save();				
				}
				
				$this->extra_params_mainpage['refresh'] = "vital_signs_icons";
				
				
			}
			
			
			if($course_type == 'W')
			{
				
				$course_title = explode('|---------|', Pms_CommonData::aesDecrypt($dead['course_title']));
				
				$todousrs = explode(',', trim($course_title[1]));
								
				foreach($todousrs as $key=>$todousr)
				{				
					$usrgrps[$key]['user_id'] = $todousr;			
					
				  	if(!is_numeric($usrgrps[$key]['user_id']))
				  	{
					//new data
					if(strpos($usrgrps[$key]['user_id'], "pseudogroup_", 0) === 0)
				  		{
				  			$pseudoid = substr($usrgrps[$key]['user_id'], strlen("pseudogroup_"));
				  			$pseudogroup_array[] = $pseudoid;
				  			
				  		}	
						elseif(substr($usrgrps[$key]['user_id'], 0, 1) != 'u')
						{
							if(substr($usrgrps[$key]['user_id'], 0, 1) != 'a')
							{
								$usrgrps[$key]['group_id'] = substr($usrgrps[$key]['user_id'], 1);
								$usrgrps[$key]['user_id'] = 0;
								$all_groupid_with_todo[] = $usrgrps[$key]['group_id'];
							}
							else 
							{
								foreach($clgrp as $clgr)
								{								
									$usrgrps[$key]['group_id'] = $clgr['id'];
									$usrgrps[$key]['user_id'] = 0;
									$all_groupid_with_todo[] = $usrgrps[$key]['group_id'];
									$key++;								
								}						
							}						
						}
						else 
						{
							$usrgrps[$key]['user_id'] = substr($usrgrps[$key]['user_id'], 1);
							$all_user_id_with_todo[] = $usrgrps[$key]['user_id'];
							if($course_title[3] == '0')
							{
								$usrgrps[$key]['group_id'] = 0;
							}
							else
							{
								$usrgrps[$key]['group_id'] = $todousrsdata[$usrgrps[$key]['user_id']]['groupid'];
							}
						}					
					}
					else 
					{
					//old data
						if($course_title[3] == '0')
						{
							$usrgrps[$key]['group_id'] = 0;
						}
						else
						{
							$usrgrps[$key]['group_id'] = $todousrsdata[$usrgrps[$key]['user_id']]['groupid'];
						}
					}				
				}
				if ( ! empty($pseudogroup_array)) {
								
					$pgu_obj = new PseudoGroupUsers();
					$users_in_pseudogroups = $pgu_obj->get_users_by_groups($pseudogroup_array);
					
					foreach($pseudogroup_array as $group) {
					
						foreach ($users_in_pseudogroups[$group] as $group_user) {
							
							if( ! in_array($group_user['user_id'], $all_user_id_with_todo) 
								&& ! in_array($todousrsdata[$group_user['user_id']]['groupid'], $all_groupid_with_todo))
							{
								$key++;
								$usrgrps[$key]['user_id'] = $group_user['user_id'];
								$usrgrps[$key]['group_id'] = 0;
								
							}
						}
					}
				}
				
				$todoarr['todo'] = $course_title[0];				
				$todoarr['todo'] = preg_replace('/Teambesprechung: /', '', $todoarr['todo']);
				$todoarr['until_date']= date("Y-m-d", strtotime($course_title[2]));
				$todoarr['ipid'] = $cust['ipid'];
				//$todoarr['client_id'] = $todousrsdata[$cust['user_id']]['clientid'];
				$todoarr['client_id'] = $clientid;
				
				if($post['val'] == '1')
				{				
					$get_todos_entry = Doctrine_Query::create()
					->select('*')
					->from('ToDos')
					->where('ipid = "' . $todoarr['ipid'] . '"')
					->andWhere('client_id = "' . $todoarr['client_id'] . '"')
					->andWhere('date(until_date) = "' . $todoarr['until_date'] . '"')
// 					->andwhere('todo = "' . $todoarr['todo'] . '"')
					->andWhere("isdelete = 0");
				
					$todos_entry = $get_todos_entry->fetchArray();

					if($todos_entry)
					{					
						foreach($todos_entry as $todo_entry)
						{
							if($todo_entry['todo'] == $todoarr['todo']) 
							{
								foreach($usrgrps as $usrgrp)
								{
									
    								if($usrgrp['user_id'] == $todo_entry['user_id'] && $usrgrp['group_id'] == $todo_entry['group_id'])
    								{	
    									$upd = Doctrine::getTable('ToDos')->findOneById($todo_entry['id']);
    									$upd->isdelete = 1;
    									$upd->save();
    								}
								}
							}
						}
					}
				}
				else
				{
					$get_todos_entry = Doctrine_Query::create()
					->select('*')
					->from('ToDos')
					->where('ipid = "' . $todoarr['ipid'] . '"')
					->andWhere('client_id = "' . $todoarr['client_id'] . '"')
					->andWhere('date(until_date) = "' . $todoarr['until_date'] . '"')
// 					->andwhere('todo = "' . $todoarr['todo'] . '"')
					->andWhere("isdelete = 1");
				
					$todos_entry = $get_todos_entry->fetchArray();

					if($todos_entry)
					{					
						foreach($todos_entry as $todo_entry)
						{
							foreach($usrgrps as $usrgrp)
							{
							    if($todo_entry['todo'] == $todoarr['todo']) {
    								if( $usrgrp['user_id'] == $todo_entry[user_id] && $usrgrp['group_id'] == $todo_entry[group_id] )
    								{	
    									$upd = Doctrine::getTable('ToDos')->findOneById($todo_entry['id']);
    									$upd->isdelete = 0;
    									$upd->save();
    								}
								}
							}
						}
					}
				}			
			}


			if($course_type == 'XT'){
				$course_title = explode('|', Pms_CommonData::aesDecrypt($dead['course_title']));
				$telefonat_minutes = $course_title[0];
				$telefonat_value = '6';

				$get_sapvs_entry = Doctrine_Query::create()
				->select('*')
				->from('Sapsymptom')
				->where('ipid = "' . $dead['ipid'] . '"')
				->andwhere("patient_course_id=" . $dead['id'])
				->andWhere("isdelete = 0");
				$sapv_entry = $get_sapvs_entry->fetchArray();

			}

			/*
			* This is added only for XT -  not needed for G
			if($course_type == 'G'){ //ISPC-2651, elena, 20.08.2020 'same as XT'
				$course_title = explode('|', Pms_CommonData::aesDecrypt($dead['course_title']));
				$telefonat_minutes = $course_title[0];
				$telefonat_value = '6';

				$get_sapvs_entry = Doctrine_Query::create()
				->select('*')
				->from('Sapsymptom')
				->where('ipid = "' . $dead['ipid'] . '"')
				->andwhere("patient_course_id=" . $dead['id'])
				->andWhere("isdelete = 0");
				$sapv_entry = $get_sapvs_entry->fetchArray();

			}
			*/
			if($course_type == 'V'){
				$course_title = explode('|', Pms_CommonData::aesDecrypt($dead['course_title']));
				$telefonat_minutes = $course_title[0];
				$telefonat_value = array('8', '999');
			
				$get_sapvs_entry = Doctrine_Query::create()
				->select('*')
				->from('Sapsymptom')
				->where('ipid = "' . $dead['ipid'] . '"')
				->andwhere("patient_course_id=" . $dead['id'])
				->andWhere("isdelete = 0");
				$sapv_entry = $get_sapvs_entry->fetchArray();
			
			}

			$cust->wrong = $post['val'];

			if ($post['val'] == 1)
			{
				// remove Patient death
				$q = Doctrine_Query::create()
				->update('PatientDeath')
				->set('isdelete', '1')
				->where('ipid = "' . $dead['ipid'] . '"')
				->andwhere("id=" . $dead['recordid']);
				$q->execute();

				// remove XT - Telefonat from sapvfb3 - Leistungserfassung
				if($course_type == 'XT'){

					$del_sapv_values = explode(",",$sapv_entry[0]['sapvalues']);

					$xt_value = array_search($telefonat_value,$del_sapv_values);
					unset($del_sapv_values[$xt_value]);
					asort($del_sapv_values);
					$del_sapv_values = array_values($del_sapv_values);

					// new sapv values with no telefonat - value
					$result_sapv_values = implode(',',$del_sapv_values);

					if(!empty($sapv_entry[0]['gesamt_zeit_in_minuten']) && $sapv_entry[0]['gesamt_zeit_in_minuten'] > $telefonat_minutes    ){
						$new_total_minutes = $sapv_entry[0]['gesamt_zeit_in_minuten'] - $telefonat_minutes;
					} else{
						$new_total_minutes = '';
					}

					$q = Doctrine_Query::create()
					->update('Sapsymptom')
					->set('sapvalues','"'.$result_sapv_values.'"' )
					->set('gesamt_zeit_in_minuten','"'.$new_total_minutes.'"' )
					->where('ipid = "' . $dead['ipid'] . '"')
					->andwhere("patient_course_id=" . $dead['id']);
					$q->execute();
				}

				/*
				* This is added only for XT -  not needed for G
				if($course_type == 'G'){ //ISPC-2651,elena, 20.08.2020, same as XT

					$del_sapv_values = explode(",",$sapv_entry[0]['sapvalues']);

					$xt_value = array_search($telefonat_value,$del_sapv_values);
					unset($del_sapv_values[$xt_value]);
					asort($del_sapv_values);
					$del_sapv_values = array_values($del_sapv_values);

					// new sapv values with no telefonat - value
					$result_sapv_values = implode(',',$del_sapv_values);

					if(!empty($sapv_entry[0]['gesamt_zeit_in_minuten']) && $sapv_entry[0]['gesamt_zeit_in_minuten'] > $telefonat_minutes    ){
						$new_total_minutes = $sapv_entry[0]['gesamt_zeit_in_minuten'] - $telefonat_minutes;
					} else{
						$new_total_minutes = '';
					}

					$q = Doctrine_Query::create()
					->update('Sapsymptom')
					->set('sapvalues','"'.$result_sapv_values.'"' )
					->set('gesamt_zeit_in_minuten','"'.$new_total_minutes.'"' )
					->where('ipid = "' . $dead['ipid'] . '"')
					->andwhere("patient_course_id=" . $dead['id']);
					$q->execute();
				}
				*/


				// remove V - Koordination from sapvfb3 - Leistungserfassung
				if($course_type == 'V'){
				
					$del_sapv_values = explode(",",$sapv_entry[0]['sapvalues']);
				
					//$xt_value = array_search($telefonat_value,$del_sapv_values);
					foreach($telefonat_value as $kv => $vv)
					{
							$xt_value = array_search($vv,$del_sapv_values);
							unset($del_sapv_values[$xt_value]);
					}
					asort($del_sapv_values);
					$del_sapv_values = array_values($del_sapv_values);
				
					// new sapv values with no koordination- value
					$result_sapv_values = implode(',',$del_sapv_values);
				
					if(!empty($sapv_entry[0]['gesamt_zeit_in_minuten']) && $sapv_entry[0]['gesamt_zeit_in_minuten'] > $telefonat_minutes    ){
						$new_total_minutes = $sapv_entry[0]['gesamt_zeit_in_minuten'] - $telefonat_minutes;
					} else{
						$new_total_minutes = '';
					}
				
					$q = Doctrine_Query::create()
					->update('Sapsymptom')
					->set('sapvalues','"'.$result_sapv_values.'"' )
					->set('gesamt_zeit_in_minuten','"'.$new_total_minutes.'"' )
					->where('ipid = "' . $dead['ipid'] . '"')
					->andwhere("patient_course_id=" . $dead['id']);
					$q->execute();
				}
				
				$cust->wrongcomment = $post['comment'];
			}
			else
			{
				// restore XT - Telefonat from sapvfb3 - Leistungserfassung
				if($course_type == 'XT'){
					$restore_sapv_values = array();
					if(!empty($sapv_entry[0]['sapvalues'])){
						$restore_sapv_values = explode(",",$sapv_entry[0]['sapvalues']);
					}

					array_push($restore_sapv_values,"6");


					asort($restore_sapv_values);
					$restore_sapv_values = array_values($restore_sapv_values);

					// new sapv values with no telefonat - value
					$result_sapv_values_restore = implode(',',$restore_sapv_values);

					$restore_total_minutes = $sapv_entry[0]['gesamt_zeit_in_minuten'] + $telefonat_minutes;

					$q = Doctrine_Query::create()
					->update('Sapsymptom')
					->set('sapvalues','"'.$result_sapv_values_restore.'"' )
					->set('gesamt_zeit_in_minuten','"'.$restore_total_minutes.'"' )
					->where('ipid = "' . $dead['ipid'] . '"')
					->andwhere("patient_course_id=" . $dead['id']);
					$q->execute();

				}
				/*
				* This is added only for XT -  not needed for G
				if($course_type == 'G'){ //ISPC-2651,elena, 20.08.2020, same as XT?
					$restore_sapv_values = array();
					if(!empty($sapv_entry[0]['sapvalues'])){
						$restore_sapv_values = explode(",",$sapv_entry[0]['sapvalues']);
					}

					array_push($restore_sapv_values,"6");


					asort($restore_sapv_values);
					$restore_sapv_values = array_values($restore_sapv_values);

					// new sapv values with no telefonat - value
					$result_sapv_values_restore = implode(',',$restore_sapv_values);

					$restore_total_minutes = $sapv_entry[0]['gesamt_zeit_in_minuten'] + $telefonat_minutes;

					$q = Doctrine_Query::create()
					->update('Sapsymptom')
					->set('sapvalues','"'.$result_sapv_values_restore.'"' )
					->set('gesamt_zeit_in_minuten','"'.$restore_total_minutes.'"' )
					->where('ipid = "' . $dead['ipid'] . '"')
					->andwhere("patient_course_id=" . $dead['id']);
					$q->execute();

				}
				*/
				// restore V - Koordination from sapvfb3 - Leistungserfassung
				if($course_type == 'V'){
					$restore_sapv_values = array();
					if(!empty($sapv_entry[0]['sapvalues'])){
						$restore_sapv_values = explode(",",$sapv_entry[0]['sapvalues']);
					}
				
					array_push($restore_sapv_values,"8,999");
				
				
					asort($restore_sapv_values);
					$restore_sapv_values = array_values($restore_sapv_values);
				
					// new sapv values with koordination - value
					$result_sapv_values_restore = implode(',',$restore_sapv_values);
				
					$restore_total_minutes = $sapv_entry[0]['gesamt_zeit_in_minuten'] + $telefonat_minutes;
				
					$q = Doctrine_Query::create()
					->update('Sapsymptom')
					->set('sapvalues','"'.$result_sapv_values_restore.'"' )
					->set('gesamt_zeit_in_minuten','"'.$restore_total_minutes.'"' )
					->where('ipid = "' . $dead['ipid'] . '"')
					->andwhere("patient_course_id=" . $dead['id']);
					$q->execute();
				
				}

				$cust->wrongcomment = "";

			}
			$cust->save();
		}

		return $cust;
	}

	public function UpdateData($post)
	{
		
	}

	public function InsertDiagnosisData($post)
	{

		$epid  = Pms_CommonData::getEpid($post['ipid']);

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		for($i=1;$i<=sizeof($post['diagnosis']);$i++)
		{
			$cust = new PatientCourse();
			$cust->ipid = $post['ipid'];
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type="D";
			$cust->course_title=$post['diagnosis'][$i];
			$cust->user_id = $userid;
			$cust->save();
		}
	}
	
	
	
	public function tracker2course()
	{
		$Tr = new Zend_View_Helper_Translate();
	
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		
   	    if(!empty($_REQUEST['id']) && strlen($_REQUEST['id'])>0 ){
   	        
    		$decid = Pms_Uuid::decrypt($_REQUEST['id']);
    		$ipid = Pms_CommonData::getIpid($decid);
    		$user_det = User::getUserDetails($userid);
    		
    		$cust = new PatientCourse();
    		$cust->ipid = $ipid;
    		$cust->course_date = date("Y-m-d H:i:s", time());
    		$cust->course_type = Pms_CommonData::aesEncrypt("ST");
    		$cust->course_title = Pms_CommonData::aesEncrypt($Tr->translate('unauthorized_opening') . $user_det['0']['first_name'].' '.$user_det['0']['last_name']);
    		$cust->user_id = $logininfo->userid;
    		$cust->done_date = date("Y-m-d H:i:s", time());
    		$cust->save();
        }
	}
}

?>