<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMaster', 'IDAT');

	class PatientMaster extends BasePatientMaster {

		//public $formname="patientmaster";
		public $triggerformid = 1;
		public $triggerformname = "frmpatient";
		
		
		protected $_encypted_columns = array(
		    'first_name',
		    'middle_name',
		    'last_name',
		    'birth_name',
		    'birth_city',
		    'title',
		    'salutation',
		    'street1',
		    'street2',
		    'zip',
		    'city',
		    'phone',
		    'kontactnumber',
		    'kontactnumbertype',
		    'mobile',
		    'email',
		    'sex',
		    'height'
		);
		
		/**
		 * @var array will hold the array from $this->getMasterData(pid,0)
		 * 
		 * @see $this->get_patientMasterData , $this->set_patientMasterData
		 * 
		 * @see in controllers Pms_Controller_Action->getPatientMasterData() , Pms_Controller_Action->setPatientMasterData()
		 */
		protected $_patientMasterData = null;
		
		/** 
		 * getter
		 * after the call to this->getMasterData($decid, 1) ,
		 * there is no need to re-call $this->getMasterData($decid, 0); (UNLESS you think db records have changed, then please do call it again)
		 * this function was created instead, to save us from select(*) Usergroup, User , DischargeMethod, PatientMaster, if[User, PatientDischarge], User
		 */
		public function get_patientMasterData ( $key = null) {
		     
		    if (is_null($key)) {
		        return $this->_patientMasterData;
		    } else {
		        return isset($this->_patientMasterData[$key]) ? $this->_patientMasterData[$key] : null;
		    }
		}
		
		/**
		 * setter , change to public if you need
		 * @param string|array $data
		 * @param string $key
		 */
		private function set_patientMasterData(  $data , $key = null )
		{
			
		    if ( ! is_array($this->_patientMasterData) ) {
		        $this->_patientMasterData = ! empty($this->_patientMasterData) ? array($this->_patientMasterData) : array();
		    }
		    
			if ( is_null($key) && is_array($data)) {
				foreach ($data as $k => $v) {
				    $this->_patientMasterData[$k] =  $v;
				}
			} elseif ( ! is_null($key)) {
				$this->_patientMasterData[ $key ] = $data;
			}
		}
		
		
	
		public function getMasterData($pid, $istemplate = false, $showinf = NULL, $ipid = NULL, $isprint = null, $clone = false,$is_pdf_template = NULL,$print_target = 'html')
		{ // isprint=1 hides images for printing only
			if( empty($pid) && empty($ipid)) {
				return;
			}
			
// 			if($ipid != NULL)
// 			{
// 				$selectid = "ipid='" . $ipid . "'";
// 			}
// 			else
// 			{
// 				$selectid = "id=" . $pid;
// 			}

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$groupid = $logininfo->groupid;
			$modules = new Modules();
			
			
			$ugroup = new Usergroup();
			$master_group = $ugroup->getMasterGroup($groupid);
		
			$usr = new User();
			$userdata = $usr->getUserDetails($logininfo->userid, true);
// 			print_r($userdata); exit;
			//$this->view->username = $userdata[0]['last_name'].', '.$userdata[0]['first_name'];
			
			// incearca asa daca nu incercam si altceva
			
			
			$dm_dead = Doctrine_Query::create()
// 				->select("*")
				->select("id")
				->from('DischargeMethod')
				->where("clientid = ?", $clientid)
				->andwhere("abbr='TOD' OR abbr='tod' OR abbr='Verstorben' OR abbr='verstorben'  OR abbr='VERSTORBEN' OR abbr='Tod' OR abbr='TODNA'")
				->andwhere('isdelete = 0');
			$dm_deadarray = $dm_dead->fetchArray();

// 			$dm_deadfinal[] = '999999999';
			$dm_deadfinal =  array();
			foreach($dm_deadarray as $key => $val)
			{
				$dm_deadfinal[] = $val['id'];
			}

			$hidemagic = Zend_Registry::get('hidemagic');
			$salt = Zend_Registry::get('salt');

			$sql = "*,AES_DECRYPT(first_name,'" . $salt . "') as first_name";
			$sql .=",AES_DECRYPT(middle_name,'" . $salt . "') as middle_name";
			$sql .= ",AES_DECRYPT(last_name,'" . $salt . "') as last_name";
			$sql .= ",AES_DECRYPT(birth_name,'" . $salt . "') as birth_name";
			$sql .= ",AES_DECRYPT(birth_city,'" . $salt . "') as birth_city";
			$sql .= ",AES_DECRYPT(title,'" . $salt . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . $salt . "') as salutation";
			$sql .= ",AES_DECRYPT(street1,'" . $salt . "') as street1";
			$sql .= ",AES_DECRYPT(street2,'" . $salt . "') as street2";
			$sql .= ",AES_DECRYPT(zip,'" . $salt . "') as zip";
			$sql .= ",AES_DECRYPT(city,'" . $salt . "') as city";
			$sql .= ",AES_DECRYPT(phone,'" . $salt . "') as phone";			
			$sql .= ",AES_DECRYPT(kontactnumber,'" . $salt . "') as kontactnumber";
			$sql .= ",kontactnumbertype as kontactnumbertype";
			$sql .= ",AES_DECRYPT(kontactnumber,'" . $salt . "') as kontactnumber_dec";
			$sql .= ",AES_DECRYPT(mobile,'" . $salt . "') as mobile";
			$sql .= ",AES_DECRYPT(email,'" . $salt . "') as email";
			$sql .= ",AES_DECRYPT(sex,'" . $salt . "') as sex";
			$sql .= ",AES_DECRYPT(height,'" . $salt . "') as height";

			$isadmin = 0;
			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA' && $clone === false)
			{
				$sql = "*,";
				$sql .= "AES_DECRYPT(email,'" . $salt . "') as emailhidden,"; //ISPC - 2304
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . $salt . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . $salt . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . $salt . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . $salt . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . $salt . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . $salt . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(kontactnumber,'" . $salt . "') using latin1),'" . $hidemagic . "') as kontactnumber, ";
				$sql .= "AES_DECRYPT(kontactnumber, '" . $salt . "') as kontactnumber_dec,";
				$sql .= "AES_DECRYPT(kontactnumber, '" . $salt . "') as kontactnumber,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . $salt . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . $salt . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . $salt . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(height,'" . $salt . "') using latin1),'" . $hidemagic . "') as height, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(birth_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as birth_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(birth_city,'" . $salt . "') using latin1),'" . $hidemagic . "') as birth_city, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(email,'" . $salt . "') using latin1),'" . $hidemagic . "') as email ";
			}

			$pt = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster pm');
// 				->where($selectid);
			
			if( $ipid != NULL) {
				$pt->where('ipid = ?', $ipid);
			} else {
				$pt->where('id = ?', $pid);
			}
			
			
			if( $istemplate == 1 || $is_pdf_template == 1)
			{
				//please join all IDAT connected tables here to fetch all data in one-pass

				//join PatientContactphone
// 				example : $pt->leftJoin("pm.PatientContactphone pcp ON pm.ipid = pcp.ipid AND pcp.isdelete = 0"); 
//				the JOIN ON is added in the table definition by the hasOne() or hasManny()
//				the isdelete is added by the listener Softdelete also added on the models table deffinition
//				so we only need the next line:
			    /**
			     * this was before ISPC-2166
			     * $pt->leftJoin("pm.PatientContactphone pcp ON pm.ipid = pcp.ipid AND pcp.isdelete IN ('0','1')");
				 */
			    /**
			     * after ISPC-2166
			     * $pt->leftJoin("pm.PatientContactphone pcp");
			     */
			    $pt->leftJoin("pm.PatientContactphone pcp");
			    $pt->addSelect("pcp.*");
			     

			}
			
			$patexec = $pt->execute();

			if($patexec)
			{
				$ptarray = $patexec->toArray();
				
				
				$ptarray[0]['ModulePrivileges'] = $modules->get_client_modules($clientid);
				// 157 - use or not patient menu scroll 
				
				
				
				
				//save to session so we have the isadminvisible
				$last_ipid_session = new Zend_Session_Namespace('last_ipid');
				$last_ipid_session->isadminvisible =  ($logininfo->usertype == 'SA' && $clone === false) ? $ptarray[0]['isadminvisible'] : 1;
				$last_ipid_session->ipid =  $ptarray[0]['ipid'];
				$last_ipid_session->dec_id =  $ptarray[0]['id'];
				
				
				$ptarray[0]['hours'] = Pms_CommonData::getHours();
				$ptarray[0]['minutes'] = Pms_CommonData::getMinutes();
				$ptarray[0]['username']= $userdata[0]['last_name'].', '.$userdata[0]['first_name'];
				
// 				$epid = Pms_CommonData::getEpid($ptarray[0]['ipid']);
				$epid_array = Pms_CommonData::getEpidcharsandNum($ptarray[0]['ipid']);
				$epid = $epid_array['epid'];
				if( is_null($ipid)) {
					$ipid = $epid_array['ipid'];
				}
				

// 				$uarr = Pms_CommonData::getUserData($ptarray[0]['create_user']);

				//TODO-3375 Ancuta 26.08.2020
				$current_recording_user = $ptarray[0]['create_user'];
				$pp = new PatientReadmission();
				$p_readm_info  = $pp->getPatientReadmission($ipid,'1');
				foreach($p_readm_info  as $k=>$rdm){
				    if($rdm['date'] == $ptarray[0]['admission_date']){
				        $current_recording_user = $rdm['create_user'];
				    }
				} 
				// -- 
				
				$pt = Doctrine_Query::create()
// 					->select('*')
					->select('id, user_title, first_name, last_name')
					->from('User')
					->where(" id = ? " , $current_recording_user )				//TODO-3375 Ancuta 26.08.2020
					->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
				
// 				$ptexec = $pt->execute();
// 				if($ptexec)
// 				{
// 					$uarr = $ptexec->toArray();
// 				}

// 				if($uarr)
// 				{
// 					$ptarray[0]['user_tname'] = $uarr[0]['user_title'];
// 					$ptarray[0]['user_fname'] = $uarr[0]['first_name'];
// 					$ptarray[0]['user_lname'] = $uarr[0]['last_name'];
// 				}
				if(!empty($pt))
				{
					$ptarray[0]['user_tname'] = $pt['user_title'];
					$ptarray[0]['user_fname'] = $pt['first_name'];
					$ptarray[0]['user_lname'] = $pt['last_name'];
				}
				
				if($epid)
				{
					$ptarray[0]['epid'] = $epid;
					$ptarray[0]['EpidIpidMapping'] = $epid_array;
				}
				
				$this->beautifyName($ptarray);// append [nice_name]	[nice_name_epid] [nice_address]
				
				$ptarray[0]['today_is_patient_birthday'] = 0;       //ISPC-2692 Lore 03.11.2020
				
				if($ptarray[0]['birthd'] != '0000-00-00')
				{
								    
					$todpatientarray =  array();
					if (! empty($dm_deadfinal)) {
// 						$todpatients = Doctrine_Query::create()
						$todpatientarray = Doctrine_Query::create()
// 							->select('*')
							->select('*')
							->from('PatientDischarge ')
// 							->where("ipid LIKE '" . $ptarray[0]['ipid'] . "'")
							->where("ipid = ? " , $ptarray[0]['ipid'] )
							->andWhereIn('discharge_method', $dm_deadfinal)
							->andWhere('isdelete = 0')
							->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
// 						$todpatientarray = $todpatients->fetchArray();
					}
					
					if( ! empty($todpatientarray))
					{
						$patient_age_date = date("Y-m-d", strtotime($todpatientarray['discharge_date']));
					}
					else
					{
						$patient_age_date = date("Y-m-d", time());
						//ISPC-2692 Lore 03.11.2020
						if(date("m-d", time()) == date("m-d", strtotime($ptarray[0]['birthd'])) ){
						    $ptarray[0]['today_is_patient_birthday'] = 1;
						}
					}
					$age = $this->GetAge($ptarray[0]['birthd'], $patient_age_date);
					$ptarray[0]['age'] = $age;
					//ISPC-2513 Ancuta - return age  with year and months 
					$age_ym = $this->GetAge($ptarray[0]['birthd'], $patient_age_date,false,true); 
					$ptarray[0]['age_yearsAndMonths'] = $age_ym;
					// --
					$ptarray[0]['birthd'] = date('d.m.Y', strtotime($ptarray[0]['birthd']));
				}
				else
				{
					$ptarray[0]['birthd'] = "-";
				}
				//$ptarray[0]['birthd'] = Pms_CommonData::hideInfo($ptarray[0]['birthd'], $ptarray[0]['isadminvisible']);
				//TODO-2468 Ancuta 31.07.2019
				if($logininfo->usertype == 'SA' && $clone === false)
				{
				    $ptarray[0]['birthd'] = Pms_CommonData::hideInfo($ptarray[0]['birthd'], $ptarray[0]['isadminvisible']);
				} else{
				    $ptarray[0]['birthd'] = $ptarray[0]['birthd'];
				}

				if($ptarray[0]['change_date'] != '0000-00-00 00:00:00')
				{
					$ptarray[0]['change_date'] = date('d.m.Y', strtotime($ptarray[0]['change_date']));
				}
				else
				{
					$ptarray[0]['change_date'] = "-";
				}

				if($ptarray[0]['recording_date'] != '0000-00-00 00:00:00')
				{
					$ptarray[0]['recording_date'] = date('d.m.Y', strtotime($ptarray[0]['recording_date']));
				}
				else
				{
					$ptarray[0]['recording_date'] = "-";
				}

				if($ptarray[0]['admission_date'] != '0000-00-00 00:00:00')
				{
					$ptarray[0]['admission_date'] = date('d.m.Y H:i:s', strtotime($ptarray[0]['admission_date']));
				}
				else
				{
					$ptarray[0]['admission_date'] = "-";
				}

				if($ptarray[0]['last_update'] != '0000-00-00 00:00:00')
				{
					$ptarray[0]['last_update'] = date('d.m.Y H:i', strtotime($ptarray[0]['last_update']));
				}
				else
				{
					$ptarray[0]['last_update'] = "-";
				}
				$ptarray[0]['opencases'] = PatientCaseStatus::get_list_patient_status_open($ptarray[0]['ipid'] , $clientid);//Maria:: Migration CISPC to ISPC 22.07.2020
				//$ptarray['birthd'] = date('d.m.Y',strtotime($ptarray['birthd']));

				$xx = $patarray[0]['vollversorgung'];
				$patarray[0]['xx'] = $xx;

				$lastupdateuser = Pms_CommonData::getUserData($ptarray[0]['last_update_user']);
				$ptarray[0]['lastupdateuser_tname'] = $lastupdateuser[0]['user_title'];
				$ptarray[0]['lastupdateuser_fname'] = $lastupdateuser[0]['first_name'];
				$ptarray[0]['lastupdateuser_lname'] = $lastupdateuser[0]['last_name'];


				if (isset($ptarray[0]['id'])) {
				    $ptarray[0]['id_encrypted'] = Pms_Uuid::encrypt($ptarray[0]['id']);
				}
				$this->set_patientMasterData($ptarray[0]);
				
				//this is extra array, used for the forms
				$ptarray_copy = $ptarray;
				unset($ptarray_copy[0]['PatientContactphone'], $ptarray_copy[0]['ModulePrivileges'], $ptarray_copy[0]['hours'], $ptarray_copy[0]['minutes']);
				$this->set_patientMasterData($ptarray_copy, "PatientMaster");
				
				
				
				if($istemplate == 1 || $is_pdf_template == 1)
				{
					
					$translator = new Zend_View_Helper_Translate();
					
					if( empty($epid)) {
						$epid = Pms_CommonData::getEpidFromId($pid);
					}
					if( is_null($ipid)) {
						$ipid = Pms_CommonData::getIpid($pid);
					}

					$qparr = Doctrine_Query::create()
						->select('*')
						->from('PatientQpaMapping')
						->where('epid= ?', $epid )
						->fetchArray();
					
					$this->set_patientMasterData($qparr, "PatientQpaMapping");
					
					
					//ISPC-2612 Ancuta 27.06.2020
					$client_is_follower_ref = ConnectionMasterTable::_check_client_connection_follower('PatientReferredBy',$clientid);
						
					//referred name
					$referredarray_q = Doctrine_Query::create()
						->select('*')
						->from('PatientReferredBy')
						->where('id= ? ', $ptarray[0]['referred_by']);
						if($client_is_follower_ref){//ISPC-2612 Ancuta 29.06.2020
						    $referredarray_q->andWhere('connection_id is NOT null');
						    $referredarray_q->andWhere('master_id is NOT null');
						}
						$referredarray_q->andWhere("clientid= ?", $clientid);
						$referredarray = $referredarray_q->fetchArray();		
					
					$this->set_patientMasterData($referredarray, "PatientReferredBy");

					
					
					
					$ptarray[0]['referred_name'] = $referredarray[0]['referred_name'];
					
					//icon system checks start
					$sys_icons = new IconsMaster();
					$pat_icons = new IconsPatient();
					$icons_client = new IconsClient();


					//L.E: 22.10.2014. added permission checks
					//get user icons permissions based on usergroup
					if($logininfo->usertype != 'SA')
					{
						//use mastergroupid not groupid
						$allowed_icons = GroupIconsDefaultPermissions::getGroupAllowedIcons($master_group, $clientid);

						//ISPC-2138 p.2 filter icons by user settings
						if ( ! empty($allowed_icons) 
						    && ! empty($userdata[0]['UserSettings']) 
						    && ! empty($userdata[0]['UserSettings']['hidden_patient_icons'])) 
						{

						        $system_icons_unchecked = is_array($userdata[0]['UserSettings']['hidden_patient_icons']['system']) ? $userdata[0]['UserSettings']['hidden_patient_icons']['system']: array();
						        $custom_icons_unchecked = is_array($userdata[0]['UserSettings']['hidden_patient_icons']['custom']) ? $userdata[0]['UserSettings']['hidden_patient_icons']['custom']: array();
						        	
						        	
						        if ( ! empty($allowed_icons['system'])) {						          
						            $allowed_icons['system'] = array_diff($allowed_icons['system'], $system_icons_unchecked);
						        }
						        if ( ! empty($allowed_icons['custom'])) {						          
						            $allowed_icons['custom'] = array_diff($allowed_icons['custom'], $custom_icons_unchecked);
						        }
						        
						    
						    
						}
						
					}
					else
					{
// 						$allowed_icons = false;
						$allowed_icons = array(
						    'system' => false,
						    'custom' => false
						);
					}
					
					$this->set_patientMasterData($allowed_icons, "UserSettings_AllowedIcons");

					/**
					 * @cla on 05.07.2018
					 * 
					 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					 * TODO : #mapPatIcon2Boxes map system icons ID to stammdaten box ID, to fecth only the allowed ones
					 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					 * 
					 * for now I changed to fectch all icons and perform fetchData on all of them, to populate my array
					 */
					//system icons
// 					$sys_icon_details = $sys_icons->get_system_icons($clientid, $allowed_icons['system']);
					$sys_icon_details = $sys_icons->get_system_icons($clientid, false);
					
					//get groups visit icons custom details
					$group_visit_forms = new GroupsVisitForms();
					$get_group_tabnames = $group_visit_forms->get_groups_links($clientid);

					$get_group_tabnames_arr = array();
					
					foreach($get_group_tabnames as $k_group_icon_details => $v_group_icon_details)
					{
						$get_group_tabnames_arr[$v_group_icon_details['groupid']] = $v_group_icon_details;
					}

					//assigned patient icons
					$patient_icons = $pat_icons->get_patient_icons($ipid);

					$patient_icons_ids = array();
					foreach($patient_icons as $patient_icon)
					{
						$patient_icons_ids[] = $patient_icon['icon_id'];
					}
					$patient_icons[] = '24';
					//custom icons all
					$client_icons = $icons_client->get_client_icons($clientid, $allowed_icons['custom']);

					//make hidden available icons_ids array
					foreach($client_icons as $k_client_icon => $v_client_icon)
					{
						$client_icons_list[$k_client_icon] = $v_client_icon;
						if(in_array($v_client_icon['id'], $patient_icons_ids))
						{
							$client_icons_list[$v_client_icon['id']]['visible'] = '0';
						}
						else
						{
							$client_icons_list[$v_client_icon['id']]['visible'] = '1';
						}
					}

//				// custom icons minus assigned ones
//				$custom_available_icons = $icons_client->get_client_icons($clientid, $patient_icons_ids);
					//patient icons list
					foreach($client_icons as $k_icon => $v_icon)
					{
						if(in_array($v_icon['id'], $patient_icons_ids))
						{
							$patient_icons_list[] = $v_icon;
						}
					}
					
					$system_icon_details = array();

					foreach($sys_icon_details as $k_icon_det => $v_icon_det)
					{
						if(!empty($v_icon_det['custom']))
						{ //load custom set
							$system_icon_details[$k_icon_det] = $v_icon_det['custom'];
							$system_icon_details[$k_icon_det]['name'] = $v_icon_det['name'];
							$system_icon_details[$k_icon_det]['function'] = $v_icon_det['function'];
							$system_icon_details[$k_icon_det]['menu_link'] = $v_icon_det['menu_link'];
						}
						else
						{//load default sets
							$system_icon_details[$v_icon_det['id']] = $v_icon_det;
						}

						if($v_icon_det['function'] == 'go_to_visitform' && strlen($master_group) > 0)
						{
							$system_icon_details[$k_icon_det]['custom'] = $get_group_tabnames_arr[$master_group];
						}
					}
					
					foreach($system_icon_details as $k_icon => $v_icon)
					{			
						//verify in function name from df exists in our class		
						if (method_exists($pat_icons, $v_icon['function'])) {

						    $data[$k_icon] = $pat_icons->{$v_icon['function']}($ipid);
							
							$data[$k_icon]['__function'] = $v_icon['function'];
						
						} else {
						    
						    $this->_log_error('ICON function missing => ' . $v_icon['function'] ." >> " . __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__ . "\n");
						    
// 							$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
// 							$log = new Zend_Log($writer);
// 							$log->info('ICON function missing => ' . $v_icon['function'] ." >> " . __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__ . "\n");
						}
						
						if(in_array($ipid, (array)$data[$k_icon]['ipids']))
						{

							if($k_icon == '24' && !empty($data[$k_icon]['group_icon']))
							{

								$system_icon_details[$k_icon]['visible'] = '1';
								$system_icon_details[$k_icon]['menu_title'] = $data[$k_icon]['group_icon']['menu_title'];
								$system_icon_details[$k_icon]['menu_link'] = $data[$k_icon]['group_icon']['menu_link'];
								$system_icon_details[$k_icon]['form_type'] = $data[$k_icon]['group_icon']['form_type'];
							}
							else if($k_icon != '24')
							{
								$system_icon_details[$k_icon]['visible'] = '1';
							}


							if($k_icon != '6' && $k_icon != '24')
							{
								$patient_sys_icons[] = $k_icon;
							}
						}
					}
					
					
					/**
					 * this IF is linked with TODO #mapPatIcon2Boxes
					 */
					if ( is_array($allowed_icons['system'])) {
					    $system_icon_details = array_intersect_key($system_icon_details, array_flip(array_values($allowed_icons['system'])));
					}
					
					
					/*
					 * this fn's are NOT yet implemented as IconsPatient... 
					 * so we manualy call the fn
					 * TODO: create an icon for this fn
					 */
					$data['get_patient_churches'] = $pat_icons->get_patient_churches($ipid);
					$data['get_patient_churches']['__function'] = 'get_patient_churches';
					
					$data['get_patient_hospiceassociation'] = $pat_icons->get_patient_hospiceassociation($ipid);
					$data['get_patient_hospiceassociation']['__function'] = 'get_patient_hospiceassociation';
					
					$data['get_patient_remedies'] = $pat_icons->get_patient_remedies($ipid);
					$data['get_patient_remedies']['__function'] = 'get_patient_remedies';
					
					
					
					
					foreach ($data as $k => $icon_data) {    
					    
					    switch ($icon_data["__function"]) {
					        
					        case "family_doctor":
					            if (isset($icon_data['family_doc_data'][$ipid])) {
					                FamilyDoctor::beautifyName($icon_data['family_doc_data'][$ipid]);
					                $this->set_patientMasterData(array($icon_data['family_doc_data'][$ipid]), "FamilyDoctor");
					            }
				            break;
				            
				            
					        case "get_patient_healthinsurance";
					        
    					        if (isset($icon_data['patient_hi_data'][$ipid])) {
    					            $this->set_patientMasterData($icon_data['patient_hi_data'][$ipid], "PatientHealthInsurance");
    					        }
    					        
    					        
    					        if (isset($icon_data['subdivizion_details'])) {
        					            
        					        if (isset($icon_data['subdivizion_details'][$ipid])) {
        					            
        					            
        					            
        					            if (isset($icon_data['subdivizion_details']['divisions'])) {
        					                foreach ($icon_data['subdivizion_details'][$ipid] as $key => $row) {   
        					                    $icon_data['subdivizion_details'][$ipid][$key] += array(
        					                        'HealthInsuranceSubdivisions' => $icon_data['subdivizion_details']['divisions'][$row['subdiv_id']]
        					                    );
        					                }
        					                    
    					                    foreach ($icon_data['subdivizion_details']['divisions'] as $key => $row) {
    					                        if ( ! isset($icon_data['subdivizion_details'][$ipid][$key]))
                					                $icon_data['subdivizion_details'][$ipid][$key] = array(
                					                    'HealthInsuranceSubdivisions' => $icon_data['subdivizion_details']['divisions'][$row['subdiv_id']]
                					                );
    					                    }
        					            }
        					            
        					                    
        					        } else {
        					            
        					            if (isset($_COOKIE['developer']) && $_COOKIE['developer'] == '@cla') {
        					                require_once '/var/www/html/ispc2017/public/debug_function.php';
        					                dd($icon_data['subdivizion_details']);
        					            }
        					            
        					            if (isset($icon_data['subdivizion_details']['divisions'])) 
            					            foreach ($icon_data['subdivizion_details']['divisions'] as $key => $row)
            					                $icon_data['subdivizion_details'][$ipid][$key] = array(
            					                    'HealthInsuranceSubdivisions' => $icon_data['subdivizion_details']['divisions'][$row['subdiv_id']]
            					                );
					                }

        					        $this->set_patientMasterData($icon_data['subdivizion_details'][$ipid], "PatientHealthInsurance2Subdivisions");
    					        }
					        break;
					        
					        
					        case "get_patient_specialists";
    					        if (isset($icon_data['patient_specialists_data'][$ipid])) {
    					            PatientSpecialists::beautifyName($icon_data['patient_specialists_data'][$ipid]);
    					            $this->set_patientMasterData($icon_data['patient_specialists_data'][$ipid], "PatientSpecialists");
    					        }
					        break;
					        
					        
					        case "get_patient_suppliers";
    					        if (isset($icon_data['patient_supplier_data'][$ipid])) {
    					            $patientSupplies = $icon_data['patient_supplier_data'][$ipid];    					            
    					            unset($patientSupplies['logo']);
    					            PatientSupplies::beautifyName($patientSupplies);
    					            $this->set_patientMasterData($patientSupplies, "PatientSupplies");
    					        }
					        break;
					        
					        
					        case "get_patient_pharmacy";
    					        if (isset($icon_data['patient_pharmacy_data'][$ipid])) {
    					            $patientPharmacys = $icon_data['patient_pharmacy_data'][$ipid];
    					            //this icon comes in a new wacky, so we can have all data
    					            $patientPharmacys_formated = array();
    					            foreach($patientPharmacys as $pdata) {
    					                $patientPharmacys_formated[$pdata['PatientPharmacy']['id']]=$pdata['PatientPharmacy'];
    					            }
    					            PatientPharmacy::beautifyName($patientPharmacys_formated);
    					            $this->set_patientMasterData($patientPharmacys_formated, "PatientPharmacy");
    					        }
					        break;
					        
					        
					        case "get_patient_versorger";
    					        if (isset($icon_data['patient_supplyer_data'][$ipid])) {
    					            $patientSuppliers = $icon_data['patient_supplyer_data'][$ipid];
    					            //this icon comes in a new wacky, so we can have all data
    					            $patientSuppliers_formated = array();
    					            foreach($patientSuppliers as $pdata) {
    					                $patientSuppliers_formated[$pdata['PatientSuppliers']['id']]=$pdata['PatientSuppliers'];
    					            }
    					            
    					            PatientSuppliers::beautifyName($patientSuppliers_formated);
    					            $this->set_patientMasterData($patientSuppliers_formated, "PatientSuppliers");
    					        }
					        break;
					        
					        
					        case "get_homecare_patients";
    					        if (isset($icon_data['patient_homes'][$ipid])) {
    					            PatientHomecare::beautifyName($icon_data['patient_homes'][$ipid]);
    					            $this->set_patientMasterData($icon_data['patient_homes'][$ipid], "PatientHomecare");
    					        }
					        break;
					        
					        
					        case "get_physiotherapist_patients";
    					        if (isset($icon_data['patient_physio'][$ipid])) {
    					            PatientPhysiotherapist::beautifyName($icon_data['patient_physio'][$ipid]);
    					            $this->set_patientMasterData($icon_data['patient_physio'][$ipid], "PatientPhysiotherapist");
    					        }
					        break;
					        
					        case "get_workers_patients";
    					        if (isset($icon_data['all_workers_res'][$ipid])) {
    					            Voluntaryworkers::beautifyName($icon_data['all_workers_res'][$ipid]);
    					            $this->set_patientMasterData($icon_data['all_workers_res'][$ipid], "PatientVoluntaryworkers");
    					        }
					        break;
					        
					        case "get_patient_churches";
    					        if (isset($icon_data['patient_churches_data'][$ipid])) {
    					            PatientChurches::beautifyName($icon_data['patient_churches_data'][$ipid]);
    					            $this->set_patientMasterData($icon_data['patient_churches_data'][$ipid], "PatientChurches");
    					        }
					        break;
					        
					        case "get_patient_hospiceassociation";
    					        if (isset($icon_data['patient_hospiceassociation_data'][$ipid])) {
    					            PatientHospiceassociation::beautifyName($icon_data['patient_hospiceassociation_data'][$ipid]);
    					            $this->set_patientMasterData($icon_data['patient_hospiceassociation_data'][$ipid], "PatientHospiceassociation");
    					        }
					        break;
					        
					        case "get_patient_remedies";
					        
    					        if (isset($icon_data['patient_remedies_data'][$ipid])) {
    					            
    					            /*
    					             * !!! this works on the assumption that $patientSupplies is before this !!! 
    					             * .. if you don't change the table order is ok
    					             */
    					            foreach ($icon_data['patient_remedies_data'][$ipid] as $k => $row) {
    					                
    					                $patSupplies_id = filter_var($row['supplier'], FILTER_SANITIZE_NUMBER_INT);
    					                
    					                foreach ($patientSupplies as $singlePatSup) {
    					                    if ($singlePatSup['supplier_id'] == $patSupplies_id) {
    					                        
    					                        $singleSup = array($singlePatSup['Supplies']);    					                        
    					                        PatientSupplies::beautifyName($singleSup);
    					                        
    					                        $icon_data['patient_remedies_data'][$ipid][$k]['Supplies'] = $singleSup[0];
    					                        
    					                    }
    					                }
    					            }
    					            
    					            $this->set_patientMasterData($icon_data['patient_remedies_data'][$ipid], "PatientRemedies");
    					        }
					        break;
                            case 'get_reassessment_data':
								//Maria:: Migration CISPC to ISPC 22.07.2020
                                $this->set_patientMasterData($icon_data['patient_reassessments_data'][$ipid], "Reassessment");


                            break;
					        
					        
					        case "";
					        break;
					    }
					    
					    
					    
					}
					
					//ISPC-2667 Lore 21.09.2020
					$pci = new PatientCareInsurance();
					$pciss = $pci->getPatientCareInsurance($ipid);
					foreach($pciss as $k=>$fs){
					    if($fs['kind_ins_legally'] == '1'){
					        $pciss[$k]['kind_of_insurance_x_arr'][] = $translator->translate("kind_ins_legally");
					    }
					    if($fs['kind_ins_private'] == '1'){
					        $pciss[$k]['kind_of_insurance_x_arr'][] = $translator->translate("kind_ins_private"); 
					    }
					    if($fs['kind_ins_no'] == '1'){
					        $pciss[$k]['kind_of_insurance_x_arr'][] =  $translator->translate("kind_ins_no"); 
					    }
					    if($fs['kind_ins_others'] == '1'){
					        $kind_ins_others = $translator->translate("kind_ins_others");
					        if(!empty($fs['kind_ins_others_text'])){
					            $kind_ins_others .= ' ('.$fs['kind_ins_others_text'].')';
					        }
					        $pciss[$k]['kind_of_insurance_x_arr'][] =  $kind_ins_others; 
					    }
					    
					    $pciss[$k]['kind_of_insurance_x'] = implode(',',$pciss[$k]['kind_of_insurance_x_arr']);
					}
					$this->set_patientMasterData($pciss, "PatientCareInsurance");	
					//.
					
					//ISPC-2672 Lore 22.10.2020
					$ppg = new PatientPlaygroup();
					$ppg_arr = $ppg->getPatientPlaygroup($ipid);
					$this->set_patientMasterData($ppg_arr, "PatientPlaygroup");
					//.
					
					//ISPC-2672 Lore 23.10.2020
					$psch = new PatientSchool();
					$psch_arr = $psch->getPatientSchool($ipid);
					$type_sch = $psch->getTypeSchool();
					foreach($psch_arr as $k => $fs){
					    if(!empty($fs['type'])){
					        $type_option_arr = explode(",", $fs['type']);
					        foreach($type_option_arr as $key => $val){
					            $psch_arr[$k]['type_option'] .= $type_sch[$val].', ';
					        }
					    } else {
					        $psch_arr[$k]['type_option'] = '-' ;
					    }
					}
					//dd($psch_arr);
					$this->set_patientMasterData($psch_arr, "PatientSchool");
					//.
					
					//ISPC-2672 Lore 26.10.2020
					$pwdp = new PatientWorkshopDisabledPeople();
					$pwdp_arr = $pwdp->getPatientWorkshopDisabledPeople($ipid);
					$this->set_patientMasterData($pwdp_arr, "PatientWorkshopDisabledPeople");
					
					//ISPC-2672 Lore 26.10.2020
					$poth = new PatientOtherSuppliers();
					$poth_arr = $poth->getPatientOtherSuppliers($ipid);
					$this->set_patientMasterData($poth_arr, "PatientOtherSuppliers");
					
					//ISPC-2672 Lore 26.10.2020
					$pch = new PatientChildrensHospice();
					$pch_arr = $pch->getPatientChildrensHospice($ipid);
					$this->set_patientMasterData($pch_arr, "PatientChildrensHospice");
					
					//ISPC-2672 Lore 26.10.2020
					$pfss = new PatientFamilySupportService();
					$pfss_arr = $pfss->getPatientFamilySupportService($ipid);
					$this->set_patientMasterData($pfss_arr, "PatientFamilySupportService");
					
					//ISPC-2672 Lore 26.10.2020
					$pst = new PatientSapvTeam();
					$pst_arr = $pst->getPatientSapvTeam($ipid);
					$this->set_patientMasterData($pst_arr, "PatientSapvTeam");
					
					//ISPC-2669 Lore 24.09.2020
					$phc = new PatientHandicappedCard();
					$phcss = $phc->getPatientHandicappedCard($ipid);
					$marks_handicapped = $phc->getMarksHandicapped();
					
					foreach($phcss as $k=>$fs){
				        $phcss[$k]['since_date_show'][] = empty($fs['since_date']) || $fs['since_date'] == "0000-00-00 00:00:00" || $fs['since_date'] == "1970-01-01 00:00:00" ? "-" : date('d.m.Y', strtotime($fs['since_date']));
				        $approved_option_date = empty($fs['approved_date']) || $fs['approved_date'] == "0000-00-00 00:00:00" || $fs['approved_date'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($fs['approved_date']));
				        if(!empty($fs['approved_option'])){
				            $phcss[$k]['approved_option_x'][] = ($fs['approved_option'] == 1 ? $translator->translate("unlimited") : $translator->translate("expiry_date").' '.$approved_option_date);
				        }else {
				            $phcss[$k]['approved_option_x'][] = '-';
				        }
				        if(!empty($fs['marks_option'])){
				            $marks_option_arr = explode(",", $fs['marks_option']);
				            foreach($marks_option_arr as $key => $val){
				                $phcss[$k]['marks_option_x'][] .= $marks_handicapped[$val].', ';
				            }
				        } else {
				            $phcss[$k]['marks_option_x'][] = '-' ;
				        }
					}
					$this->set_patientMasterData($phcss, "PatientHandicappedCard");
					//.
					
					//ISPC-2670 Lore 24.09.2020
					$pevn = new PatientEvn();
					$pevn_arr = $pevn->getPatientEvn($ipid);
					$evn_option_arr = $pevn->getEvnoptions();
					$evn_text = '';
					
					if(!empty($pevn_arr)){
					    if($pevn_arr[0]['evn_option'] == 1){
					        $evn_option = 'ja';
    					}
    					if($pevn_arr[0]['evn_option'] == '2' || $pevn_arr[0]['evn_option'] == '3' || $pevn_arr[0]['evn_option'] == '4' ){
    					    
    					    $evn_option = 'nein';
    					    $evn_text = $evn_option_arr[$pevn_arr[0]['evn_option']];    					    
    					}
    					if($pevn_arr[0]['evn_option'] == 5 ){
    					    $evn_option = 'Sonstiges';
    					    $evn_text = $pevn_arr[0]['evn_text'];
    					}
    					$ptarray[0]['evn_option'] = $evn_option;
    					$ptarray[0]['evn_text'] = $evn_text;
					}
					//
					
					
					//ISPC-2673 Lore 30.09.2020
					$fbr = new FormBlockResources();
					$fbr_arr = $fbr->getResources($ipid);   
					$this->set_patientMasterData($fbr_arr, "FormBlockResources");
					//.
					
					//ISPC-2672 Carmen 21.10.2020
					$pcks = PatientKindergartenTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
				
					foreach($pcks as $k=>&$fs){
						$fs['last_visit_type'] = $fs['last_visit']['last_visit_type'];
						$fs['last_visit_date'] = $fs['last_visit']['last_visit_date'];
						$fs['last_visit_other'] = $fs['last_visit']['last_visit_other'];
						if($fs['picked_up_brought_home']['picked_up_brought_home_yes_no'])
						{
							$fs['picked_up_brought_home_yes_no'] = $fs['picked_up_brought_home']['picked_up_brought_home_yes_no'];
						}
						$fs['picked_up_brought_home_other'] = $fs['picked_up_brought_home']['picked_up_brought_home_other'];
						if($fs['accompaniment_required']['accompaniment_required_yes_no'])
						{
							$fs['accompaniment_required_yes_no'] = $fs['accompaniment_required']['accompaniment_required_yes_no'];
						}
						$fs['accompaniment_required_other'] = $fs['accompaniment_required']['accompaniment_required_other'];
						if($fs['accompaniment_required_in_kindergarten']['accompaniment_required_in_kindergarten_yes_no'])
						{
							$fs['accompaniment_required_in_kindergarten_yes_no'] = $fs['accompaniment_required_in_kindergarten']['accompaniment_required_in_kindergarten_yes_no'];
						}
						$fs['accompaniment_required_in_kindergarten_other'] = $fs['accompaniment_required_in_kindergarten']['accompaniment_required_in_kindergarten_other'];
						if($fs['aids_available_in_kindergarten']['aids_available_in_kindergarten_yes_no'])
						{
							$fs['aids_available_in_kindergarten_yes_no'] = $fs['aids_available_in_kindergarten']['aids_available_in_kindergarten_yes_no'];
						}
						$fs['aids_available_in_kindergarten_other'] = $fs['aids_available_in_kindergarten']['aids_available_in_kindergarten_other'];
						unset($fs['last_visit']);
						unset($fs['picked_up_brought_home']);
						unset($fs['accompaniment_required']);
						unset($fs['accompaniment_required_in_kindergarten']);
						unset($fs['aids_available_in_kindergarten']);
					}
					
					$this->set_patientMasterData($pcks, "PatientKindergarten");
					//.
					//ISPC-2672 Carmen 26.10.2020
					$pcachs = PatientAmbulantChildrenHospiceServiceTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
					$this->set_patientMasterData($pcachs, "PatientAmbulantChildrenHospiceService");
					
					$pcywo = PatientYouthWelfareOfficeTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
					$this->set_patientMasterData($pcywo, "PatientYouthWelfareOffice");
					
					$pcia = PatientIntegrationAssistanceTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
					$this->set_patientMasterData($pcia, "PatientIntegrationAssistance");
					//.
					
					//ISPC-2773 Lore 14.12.2020
					$pfi = new PatientFamilyInfo();
					$pfi_arr = $pfi->getPatientFamilyInfo($ipid);
					$marital_status = $pfi->getMaritalStatus();
					$child_residing= $pfi->getChildResiding();
					foreach($pfi_arr as $k=>$fi){
					    if(!empty($fi['parents_marital_status_opt'])){
					        $ms_option_arr = explode(",", $fi['parents_marital_status_opt']);
					        foreach($ms_option_arr as $key => $val){
					            $pfi_arr[$k]['parents_marital_status_x'][] .= $marital_status[$val];
					            if($val == '4' && !empty($fi['divorced_text']) ){
				                    $pfi_arr[$k]['parents_marital_status_x'][] .= '('.$fi['divorced_text'].')';
				                }
				                if($val == '5' && !empty($fi['widowed_text']) ){
				                    $pfi_arr[$k]['parents_marital_status_x'][] .= '('.$fi['widowed_text'].')';
				                }
				                if($val == '6' && !empty($fi['other_text']) ){
				                    $pfi_arr[$k]['parents_marital_status_x'][] .= '('.$fi['other_text'].')';
				                }
					            $pfi_arr[$k]['parents_marital_status_x'][] .= ';';
					        }
					    } else {
					        $pfi_arr[$k]['parents_marital_status_x'][] = '-' ;
					    }
					   
					    if(!empty($fi['parental_consanguinity'])){
					        $pc_arr = array('1' => 'Nein', '2' => 'Neutral', '3' => 'Ja');
					        $pfi_arr[$k]['parental_consanguinity_x'][] = $pc_arr[$fi['parental_consanguinity']];
					    } else {
					        $pfi_arr[$k]['parental_consanguinity_x'][] = '-' ;
					    }
					    
					    if(!empty($fi['child_residing'])){
					        $pfi_arr[$k]['child_residing_x'][] = $child_residing[$fi['child_residing']];
					        if($fi['child_residing'] == '10' && !empty($fi['child_residing_text'])){
					            $pfi_arr[$k]['child_residing_x'][] .= '('.$fi['child_residing_text'].')';
					        }
					    } else {
					        $pfi_arr[$k]['child_residing_x'][] = '-' ;
					    }
					}
					$this->set_patientMasterData($pfi_arr, "PatientFamilyInfo");
					//.
					
					//ISPC-2776 Lore 15.12.2020
					$pcd = new PatientChildrenDiseases();
					$pcd_arr = $pcd->getChildrenDiseases($ipid);
					$this->set_patientMasterData($pcd_arr, "PatientChildrenDiseases");
					//.
					
					//ISPC-2788 Lore 08.01.2021
					$pni = new PatientNutritionInfo();
					$pni_arr = $pni->getNutritionInformation($ipid);
					$this->set_patientMasterData($pni_arr, "PatientNutritionInfo");
					//.
					
					//ISPC-2787 Lore 11.01.2021
					$psi = new PatientStimulatorsInfo();
					$psi_arr = $psi->getStimulatorsInformation($ipid);
					$this->set_patientMasterData($psi_arr, "PatientStimulatorsInfo");
					//.
					
					//ISPC-2790 Lore 12.01.2021
					$pfp = new PatientFinalPhase();
					$pfp_arr = $pfp->getFinalPhase($ipid);
					$this->set_patientMasterData($pfp_arr, "PatientFinalPhase");
					//.
					
					//ISPC-2791 Lore 13.01.2021
					$pei = new PatientExcretionInfo();
					$pei_arr = $pei->getExcretionInfo($ipid);
					$this->set_patientMasterData($pei_arr, "PatientExcretionInfo");
					//.
					
					//ISPC-2792 Lore 15.01.2021
					$pph = new PatientPersonalHygiene();
					$pph_arr = $pph->getPersonalHygiene($ipid);
					$this->set_patientMasterData($pph_arr, "PatientPersonalHygiene");
					//.
					
					//ISPC-2793 Lore 18.01.2021
					$pce = new PatientCommunicationEmployment();
					$pce_arr = $pce->getCommunicationEmployment($ipid);
					$this->set_patientMasterData($pce_arr, "PatientCommunicationEmployment");
					//.
					
				//print_r($data);
//				print_r($system_icon_details);
//				print_r($patient_sys_icons);
//				exit;
					$register_module = Modules::checkModulePrivileges("126", $logininfo->clientid);
					if(!$register_module){
					    
// 					    foreach($system_icon_details as $sid=>$sidata ){
// 					        if($sid == "42"){
// 					           unset($system_icon_details['42']); // do not show register        
// 					        }					        
// 					    }
					}

					//Maria:: Migration CISPC to ISPC 22.07.2020
                    $visit_links_container_visible = false;
					foreach($system_icon_details as $sid=>$sidata ){
					        if($sid == "24" && strlen($sidata['menu_link'])>0 ){
                                $visit_links_container_visible = true;
 					        }
 					}
                    $ptarray[0]['visit_links_container_visible']=$visit_links_container_visible;
                    if ($modules->checkModulePrivileges("1003", $clientid))
                    {

                        //Client has four status-icons
                        $ptarray[0]['four_status_icons']=true;
                    }
					//--

					$diagno_act_module = Modules::checkModulePrivileges("127", $logininfo->clientid);
					if($diagno_act_module ){
    					$ptarray[0]['diagno_act_module'] = '1';
					} else{
    					$ptarray[0]['diagno_act_module'] = '2';
					}
					$ptarray[0]['system_icon_data'] = $data;

					$ptarray[0]['system_icon_details'] = $system_icon_details;
					$ptarray[0]['count_sys_icons'] = count($system_icon_details);
					$ptarray[0]['patient_sys_icons'] = $patient_sys_icons;


					$ptarray[0]['patient_icons_details'] = $patient_icons_list;
					$ptarray[0]['count_patient_icons'] = count($patient_icons_list);

//				$ptarray[0]['custom_icon_details'] = $custom_available_icons;
					$ptarray[0]['custom_icon_details'] = $client_icons_list;
					$ptarray[0]['count_cust_icons'] = count($client_icons);


					//icon system checks end


					$client_fotm_types = FormTypes::get_form_types($clientid);
					foreach($client_fotm_types as $k =>$ft){
					    $form_types[]  = $ft['id'];
					}
					$ptarray[0]['client_form_types'] = $form_types;
					
					
					$assignuser = Doctrine_Query::create()
						->select('*')
						->from('User')
						->where('clientid= ?', $logininfo->clientid)
// 						->andWhere('isdelete=0 and isactive=0')
						->andWhere('isdelete=0')
						->orderBy('last_name ASC');
					$assignuserarray = $assignuser->fetchArray();
					
					User::beautifyName($assignuserarray);
					
					$this->set_patientMasterData($assignuserarray, "User");
					
					$inactive_users = array();
// 					$translator = new Zend_View_Helper_Translate();
					foreach($assignuserarray as $k => $uvalues)
					{
					    
					    if($uvalues['isactive'] == "0"){
					        
    						$user_details[$uvalues['id']]['name'] = $uvalues['user_title'] . " " . $uvalues['last_name'] . ', ' . $uvalues['first_name'];
    						
    						$user_info_text = "";
    						if(strlen($uvalues['phone']) > 0 ){
    						  $user_info_text .= "Telefon: ".$uvalues['phone'];
    						} 
    						
    						if(strlen($uvalues['fax']) > 0 ){
    						  $user_info_text .= "<br/> Fax: " . $uvalues['fax'];
    						}  
    						
    						if(strlen($uvalues['mobile']) > 0 ){
    						  $user_info_text .= "<br/> Mobiltelefon: " . $uvalues['mobile'];
    						}  
    						
    						if(strlen(trim($uvalues['comment'])) > 0 ){
//     							$user_info_text .= "<br/>".$translator->translate("comment")." : " . nl2br(htmlentities($uvalues['comment']));
    							$user_info_text .= "<br/>".$translator->translate("comment")." : " . nl2br($uvalues['comment']);
    						}
    						    						
    						if(strlen($user_info_text) > 0 ){
        						$user_details[$uvalues['id']]['info'] = $user_info_text;
    						} else {
        						$user_details[$uvalues['id']]['info'] = "Keine Information";
    						}
    						
    						
    						
    						$user_details[$uvalues['id']]['user_id'] = $uvalues['id'];
    						$user_details[$uvalues['id']]['type'] = "user";
					    }
					    else{
					        $inactive_users[] = $uvalues['id'];
					    }
					}

					foreach($qparr as $k => $ud)
					{
						//$assigned_users[$ud['userid']]['username'] = $user_details[$ud['userid']]['name'];
						$us[]=$ud['userid'];
					}
					//print_r($assigned_users); exit;
//2.----------------------------------------------------------------------------------------------------------

				   $us_array = PseudoGroupUsers::get_usersgroup();
				   $this->set_patientMasterData($us_array, "PseudoGroupUsers");
				   foreach($us_array as $key_us => $val_us)
				   {
					  $user_id[$val_us['pseudo_id']][$key_us]=$val_us['user_id'];
					}
					$result=$us;
					foreach($user_id as $k_us => $v_us)
					{
					   foreach($v_us as $key_2 => $val_2)
					    {
							$user2_pseudo[$k_us][]=$val_2;
					    }
					  
					    if(count(array_intersect($user2_pseudo[$k_us],$us)) == count($user2_pseudo[$k_us]))
					    {
							$used_ps =$k_us;
							//echo $k_us;
							$user_per_pseudo=$user2_pseudo[$k_us];
							$pseudo_name_array = UserPseudoGroup::get_service($k_us);
							foreach($pseudo_name_array as $kps => $valps)
							{
								$name[ $k_us] = $valps['servicesname'];
								
								$group_info = "";
								if(strlen($valps['phone']) > 0 ){
								    $group_info .= "Telefon: ".$valps['phone'];
								}
								
								if(strlen($valps['fax']) > 0 ){
								    $group_info .= "<br/> Fax: " . $valps['fax'];
								}
								
								if(strlen($valps['mobile']) > 0 ){
								    $group_info .= "<br/> Mobiltelefon: " . $valps['mobile'];
								}
								
								if(strlen($valps['email']) > 0 ){
								    $group_info .= "<br/> Email Adresse: " . $valps['email'];
								}
								
								if(strlen($group_info) > 0 ){
								    $group_details[$k_us]['info'] = $group_info;
								} else {
								    $group_details[$k_us]['info'] = "Keine Information";
								}
								
								
							}
							$assigned_users[$k_us]['username'] = $name[ $k_us];
							$assigned_users[$k_us]['type'] = "group";
							$assigned_users[$k_us]['info'] = $group_details[$k_us]['info'];
							$assigned_users[$k_us]['group_user_id'] = "group_".$k_us;
							
							//$result= array_diff($us,$user2_pseudo[$k_us]);
							foreach($user_per_pseudo as $k => $v)
							{
							  $k_arr= array_search($v,$us);
							  unset($us[$k_arr]);
							  $result = $us;
							}
						
					     }
					}
					
					//exit;
					foreach($result as $k1 => $val1)
					{
						//$assigned_users[$val1]['username'] = $user_details[$val1]['name'];
						$assigned_users[$val1]['username'] = $user_details[$val1]['name'];
						$assigned_users[$val1]['info'] = $user_details[$val1]['info'];
						$assigned_users[$val1]['user_id'] = $user_details[$val1]['user_id'];
						$assigned_users[$val1]['type'] = $user_details[$val1]['type'];
							
					}
				//print_r($result);exit;
					
					$leading_users_module = $modules->checkModulePrivileges("119", $logininfo->clientid);
					
					
					if($leading_users_module){
    					// get leading users
    					$leading_users_array = PatientQpaLeading::get_current_leading_users($ptarray[0]['ipid']);
    					$this->set_patientMasterData($leading_users_array, "PatientQpaLeading");
    					$leadding_users = array();
    					foreach($leading_users_array as $k => $ld){
    					    $leadding_users[] = $ld['userid'];
    					}
					} 
					else
					{
    					$leadding_users = array();
					}
					
					foreach($assigned_users as $user_id => $udata){
					    if(in_array($user_id,$leadding_users)){
					        $assigned_users[$user_id]['leading'] = "1";
					    } else{
					        $assigned_users[$user_id]['leading'] = "0";
					    }
					}
					
					$users_info_module = $modules->checkModulePrivileges("120", $logininfo->clientid);
					
					if($users_info_module)
					{
                        $user_info = "1";
					} 
					else
					{
                        $user_info = "0";
					}
					$ptarray[0]['user_info'] = $user_info;
					

					foreach($assigned_users as $user_id => $udata)
					{
				        $assigned_users[$user_id]['user_info'] = $user_info;
					}
					
					
					foreach($assigned_users as $user_id => $udata)
					{
					    if(in_array( $user_id ,$inactive_users)){
                            unset($assigned_users[$user_id]);					        
					    }
					}
					
					
					
					
					$grid = new Pms_Grid($assigned_users, 1, count($assigned_users), "listassignedusers.html");
					$grid->user_details = $user_details;
					$grid->user_info = $user_info;
					$usergrid = $grid->renderGrid();
					$ptarray[0]['usergrid'] = $usergrid;

					/* ################### OLD WAY TO GET VERLAUF  C - CAVE ##### */
					//$ptheadercls = new application_Triggers_WritePatientHeaderText();
					//$ptheader = $ptheadercls->triggerWritePatientHeaderText(3, $ipid);
					/* ########################################################## */
					
					
					$cave_data = PatientCourse::getCourseDataByShortcut($ipid,"C",false,true);

					foreach($cave_data as $k =>  $cave_entry){
						$cave_user[] = $cave_entry['user_id'];
					}
					
					if(!empty($cave_user)){
						$all_cave_users = User::getMultipleUserDetails($cave_user);
						
						foreach($all_cave_users  as $uk =>$udata_c ){
							$user_name_cave[$udata_c['id']] = $udata_c['last_name'].', '.$udata_c['first_name'];
						}
						
					}
					
					
					$all_client_users = User::get_client_users($logininfo->clientid);
					
					foreach($all_client_users  as $uk =>$udata ){
						$user_name[$udata['id']] = $udata['last_name'].', '.$udata['first_name'];
					}
					

					$cs = new Courseshortcuts();
					$csarr = $cs->getCourseDataByShortcut("C");

					if($csarr[0]['isbold'] == 1)
					{
						$bold = "bold";
					}
					else
					{
						$bold = "";
					}

					$ptheader_str="";
					foreach($cave_data as $k =>  $cave_entry){
						$cave_entry_data[$k] = $user_name_cave[$cave_entry['user_id']]." ".date('d.m.Y',strtotime($cave_entry['course_date'])).":<br/><i>".$cave_entry['course_title']."</i>"; //ISPC-2513 Ancuta Added <i>
						$delete_link[$k] = '<a href="javascript:void(0);" class="remove_cave" id="centry_'.$cave_entry['id'].'" rel="'.$cave_entry['id'].'" ></a>';
						
						$ptheader_str .= '<div class="info cave-box cave-red" id="cave_'.$cave_entry['id'].'"><span>'.$cave_entry_data[$k].' </span> '.$delete_link[$k].'</div>';//ISPC-2513 Ancuta Added info class
					}
					$ptheader = $ptheader_str;
					
// 					$ptheader = '<font color="#' . $csarr[0]['font_color'] . '" style="font-style:[[isitalic]];font-weight:' . $bold . '; text-decoration:[[isunderline]];">' . $ptheader . '</font>';
					$ptarray[0]['ptheader'] = $ptheader;

					/*					 * ****************** SHOW DETAILS FOR TESTING - later used for icon system ****************** */
					$showinfo = new Modules();
					$shinfo = $showinfo->checkModulePrivileges("58", $logininfo->clientid);
					if($shinfo)
					{
						$ptarray[0]['display_info'] = '1';
					}
					else
					{
						$ptarray[0]['display_info'] = '0';
					}

					
					/*					 * ******** Height in patinet details ******************** */
					$show_height_detail = $showinfo->checkModulePrivileges("132", $logininfo->clientid);
					
					if($show_height_detail)
					{
						$ptarray[0]['show_height_detail'] = 1;
					}
					else
					{
						$ptarray[0]['show_height_detail'] = 0;
					}
					
					
					
					/*					 * ******** Debtor number in health insurance ******************** */
					$show_debtor_number_m = $showinfo->checkModulePrivileges("90", $logininfo->clientid);

					if($show_debtor_number_m)
					{
						$ptarray[0]['show_debtor_number'] = 1;
					}
					else
					{
						$ptarray[0]['show_debtor_number'] = 0;
					}

					/*					 * ******* Patient History ************ */

					$first_admission = date('Y-m-d H:i:s', strtotime($ptarray[0]['admission_date']));
					$lastdischarge = PatientDischarge::getPatientLastDischarge($ipid);
					$inactivedischarge_arr = PatientDischarge::getPatientInactiveDischarge($ipid);
					if(!empty($inactivedischarge_arr)){
						$last_inactive_discharge  = end($inactivedischarge_arr);
						$ptarray[0]['last_inactive_discharge'] = date('d.m.Y', strtotime('+1 day', strtotime($last_inactive_discharge['discharge_date'])));
					}

					$readmission_dates = new PatientReadmission();
					$admisiondatesarray = $readmission_dates->getPatientReadmissionAll($ipid);

					foreach($admisiondatesarray as $adm_item)
					{
						$patient_history[$adm_item['date']] = $adm_item['date_type'];
					}

					$patient_history[$first_admission] = '1';
					if($lastdischarge)
					{
						$ptarray[0]['last_discharge'] = date('d.m.Y', strtotime('+1 day', strtotime($lastdischarge[0]['discharge_date'])));
						$patient_history[$lastdischarge[0]['discharge_date']] = '2';
					}

					ksort($patient_history, SORT_STRING);
					$ptarray[0]['patient_adm_history'] = $patient_history;


					$pl = new PatientLocation();
					$location = $pl->getActiveLocationPatInfo($ipid);

					if(!empty($location))
					{

						foreach($location as $valuel)
						{
							$llocation = $valuel['location'];
							$lstreet = $valuel['street'];
							$lzipcode = $valuel['zip'];
							$lcity = $valuel['city'];
							$lcomment = $valuel['pat_loc_comment'];

							if(!empty($valuel['station']))
							{
								$ptarray[0]['station'] = '1';
								$ptarray[0]['station_name'] = $valuel['station']['station'];
								$ptarray[0]['station_phone'] = $valuel['station']['phone1'];
								$ptarray[0]['station_phone_s'] = $valuel['station']['phone2'];
							}
							else
							{
								$ptarray[0]['station'] = '0';
							}
						}

						if($location[0]['location_type'] != 5)
						{
							$ptarray[0]['location'] = $llocation;
							$ptarray[0]['locstreet'] = $lstreet;
							$ptarray[0]['loczip'] = $lzipcode;
							$ptarray[0]['loccity'] = $lcity;
							$ptarray[0]['loccomment'] = $lcomment;
					   }
						else
						{
							$ptarray[0]['location'] = "zu Hause";
							$ptarray[0]['locstreet'] = $ptarray[0]['street1'];
							$ptarray[0]['loczip'] = $ptarray[0]['zip'];
							$ptarray[0]['loccity'] = $ptarray[0]['city'];
							$ptarray[0]['loccomment'] = $lcomment;
						}
					}
					$cpr = new ContactPersonMaster();
					$cprarr = $cpr->getPatientContact($ipid);
					$familydegree = new FamilyDegree();
					$cnt_degree_array = $familydegree->getFamilyDegrees(1);

					ContactPersonMaster::beautifyName($cprarr);
					
					$this->set_patientMasterData($cprarr, "ContactPersonMaster");
					$this->set_patientMasterData($cnt_degree_array, "FamilyDegree");
					
					foreach($cprarr as $k_cp => $v_cp)
					{
					    if(!empty($v_cp['cnt_phone'])){
    						$contact_persons_degrees[$v_cp['cnt_phone']]['fam_degree'] = $cnt_degree_array[$v_cp['cnt_familydegree_id']];
					    } else{
    						$contact_persons_degrees[$v_cp['cnt_mobile']]['fam_degree'] = $cnt_degree_array[$v_cp['cnt_familydegree_id']];
					    }
					}

					/*
					 * what is this ?
					 * @cla on 25.06.2018 removerd
					 */
					/*
					$pfle = new PatientPflegedienste();
					$last_pfle = $pfle->getPatientLastPflegedienste($ptarray[0]['ipid']);
					$ps = new Pflegedienstes();
					$psarr = $ps->getPflegedienste($last_pfle[0]['pflid']);
					
					$pfle = new PatientPflegedienste();
					$plefs = $pfle->getPatientPflegedienste($ptarray[0]['ipid']);
					*/
					
					$pfle = new PatientPflegedienste();					
					$plefs = $pfle->gatAllPatientPflegedienstes($ipid);
					
					
					$this->set_patientMasterData($plefs, "PatientPflegedienste");					

					$pfles_phones = array();
					if(!empty($plefs))
					{
						foreach($plefs as $plef_ph)
						{
							array_push($pfles_phones, $plef_ph['Pflegedienstes']['phone_practice']);

							if($is_pdf_template == 1)
							{
    							 $allplefs .= '<tr><td>' . $plef_p['Pflegedienstes']['nursing'] . '<span class="pflecomment">' . (! empty($plef_ph['pflege_comment']) ? $plef_ph['pflege_comment'] : $plef_ph['Pflegedienstes']['comments']) . '</span></td></tr>';
							} else{
							     $allplefs .= '<li>' . $plef_ph['Pflegedienstes']['nursing'] . '<span class="pflecomment">' . (! empty($plef_ph['pflege_comment']) ? $plef_ph['pflege_comment'] : $plef_ph['Pflegedienstes']['comments']) . '</span></li>';
							}
						}
					}
					$ptarray[0]['pflegedienste'] = $allplefs;



					$real_contact_number = $ptarray[0]['kontactnumber_dec'];



					$locid = substr($location[0]['location_id'], 0, 4);
					if($locid == "8888")
					{
						$cpr = new ContactPersonMaster();
						$cprarr = $cpr->getPatientContact($ipid, false);
						$cnt_number = 1;
						$z = 1;
						foreach($cprarr as $value)
						{
							if($value['isdelete'] == '0')
							{
								$locationsarray['8888' . $z] = 'bei Kontaktperson ' . $cnt_number . '(' . $value['cnt_last_name'] . ', ' . $value['cnt_first_name'] . ')';
								$cnt_number++;
							}

							$z++;
						}

						$location_name = $locationsarray[$location[0]['location_id']];
						$location_nr = $cprarr[$cnt]['cnt_phone'];
					}
					else
					{
						$locationnr = Doctrine_Query::create()
							->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
							->from('Locations')
							->where("client_id = ? " , $clientid )
							->andWhere("phone1 = ? OR phone2 = ? ", array($real_contact_number, $real_contact_number))
							//	->andWhere('isdelete = 0') //ISPC-2612 Ancuta 24.06.2020
						;
						$locname = $locationnr->fetchArray();

						if(!empty($locname[0]['phone1']))
						{
							$location_nr = $locname[0]['phone1'];
						}
						else
						{
							$location_nr = $locname[0]['phone2'];
						}

						$location_name = $locname[0]['location'];
					}

					$patient_last_location = $pl->getLastLocationData($ipid);

					if($patient_last_location[0]['station'] != '0')
					{
						$loc_stat = new LocationsStations();
						$location_station = $loc_stat->getLocationsStationsById($clientid, false, $patient_last_location[0]['station']);
					}

					//contact person
					//0. patient, 1.location/stations  2. contact person, 3.pflegedienst
					$the_contact = "";
					if($ptarray[0]['kontactnumbertype'] == '0' || ($ptarray[0]['kontactnumbertype'] && $location[0]['location_type'] == '5' && $location[0] && $patient_last_location[0]['valid_till'] == '0000-00-00 00:00:00') && $ptarray[0]['kontactnumbertype'] == '1')
					{
						$the_contact = $ptarray[0]['last_name'] . ', ' . $ptarray[0]['first_name'];
					}
					elseif($ptarray[0]['kontactnumbertype'] == '2') //contact person
					{
						$the_contact = $contact_persons_degrees[$ptarray[0]['kontactnumber_dec']]['fam_degree'];
					}
					
					elseif(in_array($ptarray[0]['kontactnumber_dec'], $pfles_phones) && $ptarray[0]['kontactnumbertype'] == '3') //pflegedienst contact number
					{
						$the_contact = "Pflegedienst";
					}
					elseif($ptarray[0]['kontactnumber_dec'] == $location_nr && $ptarray[0]['kontactnumbertype'] == '1') //location contact number
					{
						$the_contact = $location_name;
//					var_dump($ptarray[0]['kontactnumber_dec']);
					}
					else if(!empty($location_station) && ($ptarray[0]['kontactnumber_dec'] == $location_station[0]['phone1'] || $ptarray[0]['kontactnumber_dec'] == $location_station[0]['phone2']) && $ptarray[0]['kontactnumbertype'] == '1') //location station contact number
					{
						$the_contact = $location_station[0]['station'];
					}
									
					
					foreach($cprarr as $kp=>$valp)
					{
						if(!empty($ptarray[0]['kontactnumber_dec']) && $ptarray[0]['kontactnumbertype'] == '2' && ($ptarray[0]['kontactnumber_dec'] == $valp['cnt_phone'] || $ptarray[0]['kontactnumber_dec'] == $valp['cnt_mobile']) )
						{
						    if(strlen($valp['cnt_mobile']) > 0 ){
    						    if($valp['cnt_mobile'] != $ptarray[0]['kontactnumber_dec'])
    						    {
        							$ptarray[0]['kontactnumber2'] = $valp['cnt_mobile'];
    						    } 
    						    else 
    						    {
        							$ptarray[0]['kontactnumber2'] = "";
    						    }
						    }
						    else 
						    {
    							$ptarray[0]['kontactnumber2'] = "";
						    }
							$ptarray[0]['custody_cnt'] = $valp['cnt_custody'];
							$ptarray[0]['comment_cnt'] = $valp['cnt_comment'];
							$ptarray[0]['fullname_cnt'] = $valp['cnt_last_name'].', '.$valp['cnt_first_name'];
							
						}
					}
					

					if(!empty($ptarray[0]['kontactnumber_dec']))
					{
						if(!empty($the_contact))
						{
							$new_br_line = '<br/>';
						}
						$ptarray[0]['real_contact_number'] = $the_contact . $new_br_line . $ptarray[0]['kontactnumber_dec'];
					}
//old sapv header data END
//new sapv header data START

					$sapv = new SapvVerordnung();
					$patient_sapv_data = $sapv->get_today_active_highest_sapv($ipid);
					$today_high_sapv = $patient_sapv_data['last'][$ipid];

					if($today_high_sapv)
					{
						if($today_high_sapv['verordnungbis'] != '0000-00-00 00:00:00' || date('Y-m-d', strtotime($today_high_sapv['verordnungbis'])) != '1970-01-01')
						{
							$ptarray[0]['verordnungbis'] = date('d.m.Y', strtotime($today_high_sapv['verordnungbis']));
						}
						else
						{
							$ptarray[0]['verordnungbis'] = " - ";
						}

						$ptarray[0]['patinfoverordnet'] = $today_high_sapv['max_verordnet_patientinfo'];
						$ptarray[0]['sapvstatus'] = $today_high_sapv['sapv_status'] . ' ' . $today_high_sapv['sapv_extra_status'];
					}
					else
					{
					    
				        $ptarray[0]['verordnungbis'] = '';
				        $ptarray[0]['patinfoverordnet'] = '';
				        $ptarray[0]['sapvstatus'] = '';
					    
					    //TODO-1396
					    //added like this because was requested just in the viewer
					    //put directly into $ptarray[0]['verordnungbis'] if you need elswere
					    $ptarray[0]['verordnung_expired'] = null;
					    
					    if (( $get_patient_last_sapv  = $sapv->get_patient_last_sapv($ipid)) != false) {
					        
					        $get_patient_last_sapv = $get_patient_last_sapv[0];
					        
					        if($get_patient_last_sapv['verordnungbis'] != '0000-00-00 00:00:00' || date('Y-m-d', strtotime($today_high_sapv['verordnungbis'])) != '1970-01-01') {
					            $get_patient_last_sapv['verordnungbis'] = date('d.m.Y', strtotime($get_patient_last_sapv['verordnungbis']));
					        } else {
					            $get_patient_last_sapv['verordnungbis'] = " - ";
					        }
					        
					        $ptarray[0]['verordnung_expired'] = $get_patient_last_sapv;
					        
					        $verordnet = explode(',', $get_patient_last_sapv['verordnet']);
					        $high_verordnet = max($verordnet);
					        
					        if (! empty($high_verordnet)) {
					           $sapv_verordnets = Pms_CommonData::get_sapv_verordnets();				        
					           $ptarray[0]['verordnung_expired']['patinfoverordnet'] = $sapv_verordnets[$high_verordnet];
					        }   
					    }//eo TODO-1396
					    
					    
					}
//new sapv header data END

					$cp = new ContactPersonMaster();
					$cparr = $cp->getVerPatientContact($ipid);
                    //ISPC-2590,elena,22.10.2020
                    //we can have more persons, that's why we need an array
                    $contactpersonname = [];
                    foreach($cparr as $cp){
                        if($cp['cnt_hatversorgungsvollmacht'] == 1){
                            $contactpersonname[] = Pms_CommonData::hideInfo($cp['cnt_last_name'] . ", " . $cp['cnt_first_name'], $ptarray[0]['isadminvisible']);
                        }
                    }
                    if(count($contactpersonname) == 0){
                        $contactpersonname = "no";
                    }


/* 					if($cparr[0]['cnt_hatversorgungsvollmacht'] == 1)
					{
						$contactpersonname = Pms_CommonData::hideInfo($cparr[0]['cnt_last_name'] . ", " . $cparr[0]['cnt_first_name'], $ptarray[0]['isadminvisible']);
					}
					else
					{
						$contactpersonname = "no";
					} */
					//ISPC-2590 Lore 05.11.2020
					if(!empty($cparr)){
					    foreach($cparr as $key => $vals){
					        if($vals['cnt_hatversorgungsvollmacht'] == 1) {
					            $contactpersonname[$key] = $vals['cnt_last_name'] . ", " . $vals['cnt_first_name'];
					        }
					    }
					} else {
					    $contactpersonname = "no";
					}
					//.
					
					$ptarray[0]['contactpersonname'] = $contactpersonname;

					$cpl = new ContactPersonMaster();
					$cpleg = $cpl->getPatientLegalguardian($ipid);
                    //ISPC-2590,elena,22.10.2020
                    $legalguardianname = [];
                    foreach($cpleg as $cpl){
                        if($cpl['cnt_legalguardian'] == 1){
                            $legalguardianname[] = Pms_CommonData::hideInfo($cpl['cnt_last_name'] . ", " . $cpl['cnt_first_name'], $ptarray[0]['isadminvisible']);
                        }
                    }
                    if(count($legalguardianname) == 0){
                        $legalguardianname = "no";
                    }


/* 					if($cpleg[0]['cnt_legalguardian'] == 1)
					{
						$legalguardianname = Pms_CommonData::hideInfo($cpleg[0]['cnt_last_name'] . ", " . $cpleg[0]['cnt_first_name'], $ptarray[0]['isadminvisible']);
					}
					else
					{
						$legalguardianname = "no";
					} */
					//ISPC-2590 Lore 05.11.2020
					if(!empty($cpleg)){
					    foreach($cpleg as $key => $vals){
					        if($vals['cnt_legalguardian'] == 1) {
					            $legalguardianname[$key] = $vals['cnt_last_name'] . ", " . $vals['cnt_first_name'];
					        }
					    }
					} else {
					    $legalguardianname = "no";
					}
					//.
					$ptarray[0]['legalguardianname'] = $legalguardianname;

					$therapy_obj = new PatientTherapieplanung();
					$therapy_values = $therapy_obj->getTherapieplanung($ptarray[0]['ipid']);
					
					$excolumns = array('id','ipid','create_date','create_user','change_date','change_date','change_user');
					
					$therapy_val = 0;
					$therapy_texts = "";
					if( ! empty($therapy_values)){
						foreach($therapy_values as $colmun=>$value){
							if( !in_array($colmun,$excolumns) && strlen($value)> 0){
								//$therapy_val++;
								if($colmun == "freetext"){
									$therapy_texts .= $value.'<br/>';
									$therapy_val++;
								} else{
									if($value != 0 ){
										$therapy_val++;
										$therapy_texts .= $translator->translate($colmun).'<br/>';
									}
								}
							}
						}
					}
					//ISPC - 2096 - add in patient info information from notfallplan24 formular
					$therapy_obj_emergencyplan_sapv24 = new EmergencyPlanSapv24();
					$therapy_values_emergencyplan_sapv24 = $therapy_obj_emergencyplan_sapv24->get_emergency_plan_sapv24($ptarray[0]['ipid']);
					
					$searchcolumns = array (
							'resuscitation',
							'hosp_required',
							'crises',
							'artificial_food',
							'antibiotic_therapy',
							'transfusion',
							'infusion',
							'palliative_sedation'
					);
// 						var_dump($therapy_values_emergencyplan_sapv24);exit;
					if( ! empty($therapy_values_emergencyplan_sapv24)){
						foreach($therapy_values_emergencyplan_sapv24 as $colmun=>$value){
							if( in_array($colmun,$searchcolumns) && strlen($value)> 0){
								//$therapy_val++;
								if($colmun == "freetext"){
									$therapy_texts .= $value.'<br/>';
									$therapy_val++;
								} else{
									if($value == '0' ){
										$therapy_val++;
										$therapy_texts .= 'Keine '.$translator->translate($colmun).'<br/>';
									}
								}
							}
						}
					}
					//ISPC - 2096 - add in patient info information from notfallplan24 formular
					$ptarray[0]['therapy_texts'] = $therapy_texts;
					if($therapy_val > 0 ){
						$ptarray[0]['therapy'] = 1;
					}
					
// 					$pms = new PatientMaster();

					if($patarray[0]['vollversorgung_date'] != '0000-00-00 00:00:00')
					{

						/* $statusvolldate = $pms->getDaysDiff($ptarray[0]['vollversorgung_date'], date('Y-m-d h:i:s')); */
						$vv_history = new VollversorgungHistory();
						$statusvolldate = $vv_history->getVollversorgungDays($ipid);
						$ptarray[0]['nr'] = $statusvolldate;
					}
					else
					{
						$ptarray[0]['nr'] = '--';
					}

					//$this->set_patientMasterData($plefs, "PatientPflegedienste");
					
					

					if($ptarray[0]['isdischarged'] == 1)
					{
// 						$dism = Doctrine::getTable('PatientDischarge')->findBy('ipid', $ptarray[0]['ipid']);
						$pdischarge_model  = new PatientDischarge();
						$dism = $pdischarge_model->getPatientDischarge($ptarray[0]['ipid']);
						
						$dismet = new DischargeMethod();
						$disemetdet = $dismet->getDischargeMethodById($dism[0]['discharge_method']);
						
						$dead_abbr = array("tod","verstorben","todna");
						//if discharge method is "tod" the traffic status is none
						if(in_array(strtolower($disemetdet[0]['abbr']),$dead_abbr ))
						{
							$ptarray[0]['traffic_status'] = 0;
							$ptarray[0]['death_dis'] = '1';
							$ptarray[0]['death_date'] = date("d.m.Y H:i", strtotime($dism[0]['discharge_date']));
						}
						$ptarray[0]['discarge_mth'] = $disemetdet[0]['description'];
						$pdeath = new PatientDeath();
						$patientdeatharray = $pdeath->getPatientDeath($ipid);
						if(count($patientdeatharray) > 0 && !in_array(strtolower($disemetdet[0]['abbr']),$dead_abbr))
						{
							$ptarray[0]['death_btn'] = '1';
							$ptarray[0]['death_date'] = date("d.m.Y H:i", strtotime($patientdeatharray[0]['death_date']));
						}
					}

					if($ptarray[0]['isstandby'] == 1)
					{
						$ptarray[0]['traffic_status'] = 0;
						$ptarray[0]['is_standby'] = '1';
					}

					if($ptarray[0]['isstandbydelete'] == 1)
					{
						$ptarray[0]['traffic_status'] = 0;

						$ptarray[0]['is_standbydelete'] = '1';
					}

					if($ptarray[0]['ishospiz'] == 1)
					{
						$ptarray[0]['is_hospiz'] = '1';
					}
					if($ptarray[0]['ishospizverein'] == 1)
					{
						$ptarray[0]['is_hospizverein'] = '1';
					}
					if($ptarray[0]['isarchived'] == 1)
					{
						$ptarray[0]['is_archived'] = '1';
					}

					$ptarray[0]['iskeyuser'] = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
					//no key/create user but superadmin
					$ptarray[0]['iscreateuser'] = false;

					//Added by radu print case no images and links START
					if($isprint != NULL)
					{
						$ptarray[0]['print'] = $isprint;
					}
					else
					{
						$ptarray[0]['print'] = 0;
					}
					//Added by radu print case no images and links END
					
					//check memo old module and misc module permissions START
					$mo = $modules->checkModulePrivileges("61", $logininfo->clientid);

					// weight chart
					/*module 105 no longer in use*/
// 					$show_weight = $modules->checkModulePrivileges("105", $logininfo->clientid);

					if($show_weight)
					{
					    $ptarray[0]['show_weight'] = '1';
					} else
					{
					    $ptarray[0]['show_weight'] = '0';
					}
					if($mo || ($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA'))
					{
						if(!($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA'))
						{
							$has_edit_permission = PatientPermissions::verifyMiscPermission($logininfo->userid, $ipid, '1', 'canedit');

							if(!$has_edit_permission)
							{
								$has_view_permission = PatientPermissions::verifyMiscPermission($logininfo->userid, $ipid, '1', 'canview');
								if($has_view_permission)
								{
									$ptarray[0]['permission_level'] = '2'; //view
								}
								else
								{
									$ptarray[0]['permission_level'] = '0'; //zbuf!! don`t show
								}
							}
							else
							{
								$ptarray[0]['permission_level'] = '1'; //edit
							}
						}
						else
						{
							$ptarray[0]['permission_level'] = '1'; //edit
						}

						$memos = new PatientMemo();
						$memo = $memos->getpatientMemo($ipid);

						$ptarray[0]['memo'] = $memo[0]['memo'];
						
						//Maria:: Migration CISPC to ISPC 22.07.2020
                        // IM-5 whitebox
						$whitebox_module = Modules::checkModulePrivileges("1007", $logininfo->clientid);
                        if($whitebox_module){
                            $whiteboxes = new PatientWhitebox();
                            $whitebox = $whiteboxes->getCurrentPatientWhitebox($ipid);

                            $oWhitebox = json_decode($whitebox[0]['whitebox']);
                            $ptarray[0]['whitebox'] = $oWhitebox->whitebox;

                        }
						//--
					}
					else
					{
						$ptarray[0]['permission_level'] = '0';
					}

					//check memo old module and misc module permissions END
					//get BTM icon perms START
					$btm = $modules->checkModulePrivileges("75", $logininfo->clientid);

					if($btm)
					{
						$ptarray[0]['btm_perm'] = '1';
					}
					else
					{
						$ptarray[0]['btm_perm'] = '0';
					}

					//check user permision
					$btm_perms = new BtmGroupPermissions();

					if($logininfo->usertype != 'SA')
					{
						$btm_permisions = $btm_perms->get_group_permissions($clientid, Usergroup::getMasterGroup($groupid));
						if($btm_permisions['use'] == '1')
						{
							$has_btm_permission = '1';
						}
						else
						{
							$has_btm_permission = '0';
						}
					}
					else
					{
						$has_btm_permission = '1';
					}
					$ptarray[0]['has_btm_permission'] = $has_btm_permission;
					//get BTM icon perms END
                    //ISPC-2912,Elena,25.05.2021
                    //show btm
                    $btm_show = $modules->checkModulePrivileges("1021", $logininfo->clientid);

                    if($btm_show)
                    {
                        $ptarray[0]['btm_show'] = '1';
                    }
                    else
                    {
                        $ptarray[0]['btm_show'] = '0';
                    }
					
					
					// patient navigation START - ISPC-1717
					
					$show_navigation = $modules->checkModulePrivileges("130", $logininfo->clientid);

					if($show_navigation)
					{
					    $ptarray[0]['show_navigation'] = '1';
					    
    					/* ---------  get user's patients by permission   ---------------------------- */
    					$user_patients = PatientUsers::getUserPatients($logininfo->userid);
    					
    					/* --------- get initial ipids and apply patient master filters ---------------------------- */
    					
    					
    					

    					$hidemagic = Zend_Registry::get('hidemagic');
    					
    					$sql = "p.ipid,e.epid,p.familydoc_id,p.isstandby,p.isdischarged,p.isstandbydelete, p.traffic_status, p.birthd,";       //ISPC-2536 Lore 27.03.2020
    					$sql .= "AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
    					$sql .= "AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
    					//ISPC-2513 Ancuta 15.05.2020
    					$sql .= "AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
    					
    					$isadmin = 0;
    					// if super admin check if patient is visible or not
    					if($logininfo->usertype == 'SA' && $clone === false)
    					{
    					    $sql = "p.ipid,e.epid,p.familydoc_id,p.isstandby,p.isdischarged,p.isstandbydelete, p.traffic_status, p.birthd,";       //ISPC-2536 Lore 27.03.2020
    					    $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
    					    $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
    					    //ISPC-2513 Ancuta 15.05.2020
    					    $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
    					}
    					
    					$patient = Doctrine_Query::create()
    					->select($sql)
    					->from('PatientMaster p')
    					->where('p.isdelete = 0')
    					->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')')
    					->andWhere('p.isdelete = 0')
    					->andWhere('p.isstandbydelete=0')
    					//->andWhere('p.isstandby = 0 ')       //ISPC-2093 @Lore 07.10.2019
    					->andWhere('p.isdischarged = 0')
    					->andWhere('p.isarchived = 0');
    					$patient->leftJoin("p.EpidIpidMapping e");
    				    $patient->andWhere('e.clientid = ' . $logininfo->clientid);
    				    $patient->orderBy('field(isstandby,"0","1"), TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci ASC ');
    					$patienidt_array = $patient->fetchArray();
//     					dd($patienidt_array);
    					
    					$current_patient_ipid = $ptarray[0]['ipid'];
    					foreach ($patienidt_array as $patient_key => $pdetails){
    					        
                            if($pdetails['ipid'] == $current_patient_ipid){
                                $prev_key = $patient_key - 1;
                                
                                //ISPC-2093 @Lore 07.10.2019 
                                $curent_actiornot = $patienidt_array[$patient_key]['isstandby'];
                                
                                if(!empty($patienidt_array[$prev_key ]) && ($patienidt_array[$prev_key ]['isstandby'] == $curent_actiornot)){
            					   $ptarray[0]['nav']['previous']['id'] = $patienidt_array[$prev_key ]['id']; 
            					   //$ptarray[0]['nav']['previous']['details'] = $patienidt_array[$prev_key]['EpidIpidMapping']['epid'].' - '.$patienidt_array[$prev_key]['last_name'].', '.$patienidt_array[$prev_key]['first_name']; 
            					   //$ptarray[0]['nav']['previous']['details'] = $patienidt_array[$prev_key]['EpidIpidMapping']['epid'].' - '.$patienidt_array[$prev_key]['last_name'].', '.$patienidt_array[$prev_key]['first_name'].' *'.date('d.m.Y', strtotime($patienidt_array[$prev_key]['birthd']));  //ISPC-2536 Lore 27.03.2020
            					   //ISPC-2513 pct.d,g Lore 18.05.2020
            					   $ptarray[0]['nav']['previous']['details'] = $patienidt_array[$prev_key]['last_name'].', '.$patienidt_array[$prev_key]['first_name'].' *'.date('d.m.Y', strtotime($patienidt_array[$prev_key]['birthd'])).' ('.$this->GetAge($patienidt_array[$prev_key]['birthd'], $patient_age_date,false,true).')' ;  
            					   
                                }
                                
                                $ptarray[0]['nav']['current'] = $pdetails['id'];  
    					     
    				            $next_key = $patient_key + 1;
        				            
    				            if(!empty($patienidt_array[$next_key ]) && ($patienidt_array[$next_key ]['isstandby'] == $curent_actiornot)){
    					           $ptarray[0]['nav']['next']['id'] = $patienidt_array[$next_key ]['id'];
    					           //$ptarray[0]['nav']['next']['details'] = $patienidt_array[$next_key]['EpidIpidMapping']['epid'].' - '.$patienidt_array[$next_key]['last_name'].', '.$patienidt_array[$next_key]['first_name'];
    					           //$ptarray[0]['nav']['next']['details'] = $patienidt_array[$next_key]['EpidIpidMapping']['epid'].' - '.$patienidt_array[$next_key]['last_name'].', '.$patienidt_array[$next_key]['first_name'].' *'.date('d.m.Y', strtotime($patienidt_array[$next_key]['birthd']));  //ISPC-2536 Lore 27.03.2020
    					           //ISPC-2513 pct.d,g Lore 18.05.2020
    					           $ptarray[0]['nav']['next']['details'] = $patienidt_array[$next_key]['last_name'].', '.$patienidt_array[$next_key]['first_name'].' *'.date('d.m.Y', strtotime($patienidt_array[$next_key]['birthd'])).' ('.$this->GetAge($patienidt_array[$next_key]['birthd'], $patient_age_date,false,true).')' ;  
    				            }
                                
    					    } 
    					    
    					    if($pdetails['isstandby']==0){
        					    //$ptarray[0]['nav']['dropdown']['Aktiv'][$pdetails['id']] = $pdetails['EpidIpidMapping']['epid'].' - '.$pdetails['last_name'].', '.$pdetails['first_name'];
    					        //$ptarray[0]['nav']['dropdown']['Aktiv'][$pdetails['id']] = $pdetails['EpidIpidMapping']['epid'].' - '.$pdetails['last_name'].', '.$pdetails['first_name'].' *'.date('d.m.Y', strtotime($pdetails['birthd']));  //ISPC-2536 Lore 27.03.2020
    					        //ISPC-2513 pct.d,g Lore 18.05.2020
    					        $ptarray[0]['nav']['dropdown']['Aktiv'][$pdetails['id']] = $pdetails['last_name'].', '.$pdetails['first_name'].' *'.date('d.m.Y', strtotime($pdetails['birthd'])).' ('.$this->GetAge($pdetails['birthd'], $patient_age_date,false,true).')';  
    					        
    					    } else{
        					    //$ptarray[0]['nav']['dropdown']['Standby'][$pdetails['id']] = $pdetails['EpidIpidMapping']['epid'].' - '.$pdetails['last_name'].', '.$pdetails['first_name'];
    					        //$ptarray[0]['nav']['dropdown']['Standby'][$pdetails['id']] = $pdetails['EpidIpidMapping']['epid'].' - '.$pdetails['last_name'].', '.$pdetails['first_name'].' *'.date('d.m.Y', strtotime($pdetails['birthd']));  //ISPC-2536 Lore 27.03.2020
    					        //ISPC-2513 pct.d,g Lore 18.05.2020
    					        $ptarray[0]['nav']['dropdown']['Standby'][$pdetails['id']] = $pdetails['last_name'].', '.$pdetails['first_name'].' *'.date('d.m.Y', strtotime($pdetails['birthd'])).' ('.$this->GetAge($pdetails['birthd'], $patient_age_date,false,true).')';  
    					    }
    					    // ISPC-2513 Ancuta 15.05.2020
    					    if($pdetails['sex'] == '1'){
    					       $ptarray[0]['nav']['gender'][$pdetails['id']] = 'masculine';
    					    }
    					    elseif($pdetails['sex'] == '2'){
    					       $ptarray[0]['nav']['gender'][$pdetails['id']] = 'femenine';
    					    } 
    					    elseif($pdetails['sex'] == '0'){
    					       $ptarray[0]['nav']['gender'][$pdetails['id']] = 'genders';
    					    }
    					    else
    					    {
    					       $ptarray[0]['nav']['gender'][$pdetails['id']] = '';
    					    }
    			 
    					    // --
    					}

					} else
					{
					    $ptarray[0]['show_navigation'] = '0';
					}
					
					//Maria:: Migration CISPC to ISPC 22.07.2020
                    // patient navigation for clinic IM-19
                    $show_clinic_patient_switcher = $modules->checkModulePrivileges("1004", $logininfo->clientid);
                    if($show_clinic_patient_switcher){
                        $ptarray[0]['show_clinic_patient_switcher'] = '1';
                    }
                    else{
                        $ptarray[0]['show_clinic_patient_switcher'] = '0';
                    }
					//--
					$ptarray[0]['print_target'] = $print_target;
					$this->set_patientMasterData($ptarray[0]);

					//Kontakt-Telefonnummer ISPC-2045
					/**
					 * we fetch also deleted phones
					 * if you ever had a 'phone' added to this table, we no longer use the details from patientmaster(in case u delete all the 'phones')
					 * below we exclude deleted phones from our grid
					 * 
					 * + changes 26.03.2018 : deleted are no longer fetched
					 */
					$PatientContactphone = array();
					
					/**
					 * this was before ISPC-2166
					 * $ptarray[0]['has_PatientContactphone'] =  count($ptarray[0]['PatientContactphone']);
					 * 
					 * after ISPC-2166
					 * deleted are no longer fetched
					 * has_PatientContactphone was/is used in the html, you can remove from there if you like
					 */
					$ptarray[0]['has_PatientContactphone'] = 1;
					
					/**
					 * extra fields, comments and address, are displayed based on user settings
					 */
					$extra_display_settings = ! empty($userdata[0]['UserSettings']) && ! empty($userdata[0]['UserSettings']['patient_contactphone']) ? $userdata[0]['UserSettings']['patient_contactphone'] : null; 
					
					foreach($ptarray[0]['PatientContactphone'] as $row) {
						
					    if ($row['isdelete'] == 1) continue;
					    
						$phone_name = ! empty($row['last_name']) ? $row['last_name'] . ", " : "";
						$phone_name .= ! empty($row['first_name']) ? $row['first_name'] : "";
												
						$PatientContactphone[$row['parent_table'] . $row['table_id']] = array(
								'id'            => $row['id'],
								'name'          => $phone_name,
								'other_name'    => $row['other_name'],
								'phone'         => $row['phone'],
								'mobile'        => $row['mobile'] ,
						        'fax'           => $row['fax'] ,          //ISPC-2550 Lore 17.02.2020
						        'parent_table_original'  => $row['parent_table'],
								'parent_table'  => $translator->translate($row['parent_table']) ,
								'extra'         => ! is_null($extra_display_settings) && ! empty($row['extra']) ? unserialize($row['extra']) : null,
								'extra_display_settings' => $extra_display_settings,
						      
						);
					}
					$ptarray[0]['PatientContactphone_table'] = $PatientContactphone; //Kontakt-Telefonnummer ISPC-2045 as array
					
					$grid = new Pms_Grid($PatientContactphone, 1, count($PatientContactphone), "listcontactphones.html");
					$PatientContactphoneGrid = $grid->renderGrid();
					$ptarray[0]['PatientContactphoneGrid'] = $PatientContactphoneGrid;//Kontakt-Telefonnummer ISPC-2045 as html
					
					//ISPC-2497 Lore 09.01.2020
					$dl = new DischargeLocation();
					$discharge_locations_arr = $dl->getDischargeLocation($clientid, 1, true);
					$ptarray[0]['discharge_locations_arr'] = $discharge_locations_arr ;
					//--
					
					
					// ISPC-2593 Lore 20.05.2020  - weight and height
					$ptarray[0]['weight'] ="-";
					$ptarray[0]['height'] ="-";
					$latest_vital_signs = FormBlockVitalSigns::get_patients_chart_last_values($ipid);
					if(!empty($latest_vital_signs[$ipid])){
					    if(!empty($latest_vital_signs[$ipid]['weight'])){
					        $ptarray[0]['weight'] = str_replace('.', ',', $latest_vital_signs[$ipid]['weight']).' kg';
					    }
					    if(!empty($latest_vital_signs[$ipid]['height'])){
					        $ptarray[0]['height'] = str_replace('.', ',', $latest_vital_signs[$ipid]['height']).' cm';
					    }
					    
					}
					
					//ISPC-2661 pct.12 Carmen 14.09.2020
					//$admission_vital_signs = FormBlockVitalSigns::get_patients_chart_admission_values($ipid, false, false);
					$admission_vital_signs = FormBlockVitalSigns::get_patients_chart_admission_values($ipid);
					$ptarray[0]['admission_weight'] ="-";
					if(!empty($admission_vital_signs[$ipid])){
						if(!empty($admission_vital_signs[$ipid]['weight'])){
							$ptarray[0]['admission_weight'] = str_replace('.', ',', $admission_vital_signs[$ipid]['weight']).' kg';
							$ptarray[0]['date_admission_weight'] = date('d.m.Y', strtotime($admission_vital_signs[$ipid]['date']));
						}	
					}
					
					//--
					//ISPC-2513 Ancuta 
					// #ISPC-2512PatientCharts
					if($userdata[0]['header_type'] == 'type_2_header'){
/*     					// weight and height
    					$ptarray[0]['weight'] ="-";
    					$ptarray[0]['height'] ="-";
    					$latest_vital_signs = FormBlockVitalSigns::get_patients_chart_last_values($ipid);
    					if(!empty($latest_vital_signs[$ipid])){
    					    if(!empty($latest_vital_signs[$ipid]['weight'])){
        					    $ptarray[0]['weight'] = str_replace('.', ',', $latest_vital_signs[$ipid]['weight']).' kg';
    					    }
    					    if(!empty($latest_vital_signs[$ipid]['height'])){
        					    $ptarray[0]['height'] = str_replace('.', ',', $latest_vital_signs[$ipid]['height']).' cm';
    					    }
    					        
    					} */
    					
    					
    					//Main Diagnosis data 
    					$dg = new DiagnosisType();
    					$abb2 = "'HD'";
    					$ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
    					$comma = ",";
    					$typeid = "'0'";
    					
    					foreach($ddarr2 as $key => $valdia)
    					{
    					    $typeid .=$comma . "'" . $valdia['id'] . "'";
    					    $comma = ", ";
    					    $type_details[$valdia['id']] = $valdia['abbrevation'];
    					    $type_array[] = $valdia['id'];
    					}
    					
    					$patdia = new PatientDiagnosis();
    					$patient_main_diagnosis = array();
    					$patient_main_diagnosis = $patdia->get_multiple_patients_diagnosis(array($ipid), $type_array);
    					
    					$main_diagno_texts = "";
    					if(!empty($patient_main_diagnosis)){
    					   $main_diagno_texts_arr = array_column($patient_main_diagnosis , 'diagnosis');
    					   $main_diagno_texts = implode(', ',$main_diagno_texts_arr);  
    					}
    					$ptarray[0]['main_diagno_texts'] = $main_diagno_texts;
    					
    					
    					
    					// get admission reasons and type 
    					$latest_readmission_data = array_filter($admisiondatesarray, function($adm) use ($ptarray) {
    					    return ($adm['date_type'] == '1') && ( date('d.m.Y H:i:s',strtotime($adm['date'])) == $ptarray[0]['admission_date']) ;
    					});
    					
    				    $latest_readmission_id  =  0 ;
    				    if(!empty($latest_readmission_data)){
    				        $latest_readmission_data = array_values($latest_readmission_data);
    				        $latest_readmission_id  = $latest_readmission_data[0]['id'];
    				    }
    				    $admission_details = array();
    				    if(!empty($latest_readmission_id )){
    				        //$latest_readmission_id
    				        $admission_details= Doctrine_Query::create()
    				        ->select('*')
    				        ->from('PatientReadmissionDetails p')
    				        ->where('ipid =?', $ipid)
    				        ->andWhere('readmission_id = ?',$latest_readmission_id)
    				        ->andWhere('isdelete = 0')
    				        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    				    }
    				    $ptarray[0]['admission_type'] = "-";
    				    if(!empty($admission_details) && !empty($admission_details['admission_type'])){
    				        $ptarray[0]['admission_type'] = $translator->translate('adm_type_'.$admission_details['admission_type']);
    				    }
    				    $ptarray[0]['admission_reason'] = "-";
    				    if(!empty($admission_details) && !empty($admission_details['admission_reason'])){
    				        $ptarray[0]['admission_reason'] =  $admission_details['admission_reason'];
    				    }
    					
    					//artificial entries/exists
    				    $client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($clientid, Doctrine_Core::HYDRATE_ARRAY);
    				    
    				    $patientsart = Doctrine_Query::create()
    				    ->select("*")
    				    ->from('PatientArtificialEntriesExits')
    				    ->whereIn('ipid', array($ipid))
    				    ->andWhere('isremove  = "0"')
    				    ->fetchArray();
    				    
    				    $artificial_items = array();
    				    foreach($patientsart as $k=>$p_art){
    				        $optkey = array_search($p_art['option_id'], array_column($client_options, 'id'));
    				        if($optkey !== false)
    				        {
    				            $artificial_items[] = $client_options[$optkey]['name'];
    				        }
    				    }
    				    
    				    
    				    if(!empty($artificial_items)){
    				        $ptarray[0]['artificial_entries'] = implode(', ',$artificial_items);
    				    }
    				    
    					// Living will 
    					$acp_m = new PatientAcp();
    					$acp_ipids_data= $acp_m->getByIpid(array($ipid));
    					
    				    $has_living_will = false;
    				    $living_will_file = array();;
    				    foreach($acp_ipids_data[$ipid] as $k=> $ldata){
    				        if($ldata['division_tab'] == 'living_will' && $ldata['active'] == "yes"){
    				            $has_living_will = true;
    				            if(!empty( $ldata['files'])){
    				                $living_will_file = $ldata['files'][0];
    				            }
    				        }
    				    }
    				    $ptarray[0]['has_living_will'] = $has_living_will;
    				    $ptarray[0]['living_will_file'] = $living_will_file;
					}
					//-- 
					
					//ISPC-2593 Lore 19.05.2020
					$header_option = ClientHeaderOption::get_client_header_option($clientid);
					$ptarray[0]['header_option'] = $header_option;
					//.
					

					//ISPC-2517
				    $ptarray[0]['has_events_add_button'] = 0;
				    
					if($ptarray[0]['ModulePrivileges']['226']){
					    $ptarray[0]['has_events_add_button'] = 1;
					
   		                $events_master_group = $master_group;
    		            if($logininfo->usertype == "SA"){
    					    $events_master_group = 4;// hardcode SA to Arzt group
    		            }
    		            
    					// get saved values
    					$saved_events  = array();
    					$saved_events = Doctrine_Query::create()
    					->select('*')
    					->from('ClientEvents2groups')
    					->where('clientid= ?', $clientid)
    					->andWhere('master_group_id= ?', $events_master_group )
    					->andWhere('isdelete =?','0')
    					->orderBy('sort_order ASC')
    					->fetchArray();
    				    
    					if(empty($saved_events)){
    					   $ptarray[0]['has_events_add_button'] ='0';
    					}  
					}
					
					
					// -- 
					
					//ISPC-2583 Carmen 27.04.2020
					$color_map = array(
							'given' => 'green',
							'not_given' => 'red',
							'given_different_dosage' => 'blue',
							'not_taken_by_patient' => 'yellow',
					);
					$ptarray[0]['color_map'] = $color_map;
					$ptarray[0]['color_map_js'] = json_encode($color_map);
					//--
					
					if($is_pdf_template == 1)
					{
    					$temps = Pms_Template::createTemplate($ptarray[0],"templates/patientinfopdf.html");
    					return($temps);
					} 
					else 
					{
					    //ISPC-2513 Ancuta
					    if($userdata[0]['header_type'] == 'type_2_header'){
    					   $temps = Pms_Template::createTemplate(array_merge($ptarray[0], ['protected_patientMasterData' => $this->get_patientMasterData()]) , "templates/patientinfochart.html");
					    } else{
         					$temps = Pms_Template::createTemplate(array_merge($ptarray[0], ['protected_patientMasterData' => $this->get_patientMasterData()]) , "templates/patientinfo.html");
					    }
    					return($temps);
					}

				}
				else
				{
					return $ptarray[0];
				}
			}
		}

		public static function getClientPatients($cid)
		{
			$epid = Doctrine_Core::getTable('EpidIpidMapping')->findBy('clientid', $cid);

			if(!$epid)
				return array();

			foreach($epid->toArray() as $key => $val)
			{
				$p = Doctrine_Core::getTable('PatientMaster')->findBy('ipid', $val['ipid']);
				$parr = $p->toArray();
				$parr = $parr[0];
			}
		}

		/**
         * ISPC-2391, elena, 03.09.2020
         * @param $cid
         * @return array
         */
		public static function getClientIpids($cid)
        {
            $epid = Doctrine_Core::getTable('EpidIpidMapping')->findBy('clientid', $cid);

            if(!$epid)
                return array();

            $retVal = [];
            foreach($epid->toArray() as $key => $val)
            {
                $p = Doctrine_Core::getTable('PatientMaster')->findBy('ipid', $val['ipid']);
                $parr = $p->toArray();
                $parr = $parr[0];
                $retVal[] = $parr['ipid'];
            }
            return $retVal;
        }


        /**
		 * 
		 * @param unknown $Birthdate
		 * @param unknown $agedate
		 * @param boolean $numeric
		 * @param boolean $year_months < Added new param for ISPC-2513 
		 * @return string|number
		 */
		public function GetAge($Birthdate, $agedate, $numeric = false, $year_months = false)
		{
			list($BirthYear, $BirthMonth, $BirthDay) = explode("-", $Birthdate);
			list($AgeYear, $AgeMonth, $AgeDay) = explode("-", $agedate);

			$bdt_time = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);
			$age_time = mktime(0, 0, 0, $AgeMonth, $AgeDay, $AgeYear);
			/* $curr_time = mktime(0, 0, 0, date("m"), date("d"), date("y")); */
			$years = $this->GetTreatedDays(date("Y-m-d", $bdt_time), date("Y-m-d", $age_time), true);

			//ISPC-2513 Ancuta 
			if($year_months){
			    $return_value = $years['years'] . " Jahre";
			    if(!empty($years['months'])){
                    $return_value .= ' '.$years['months'] . " Monate";
			    }
			    
			    return $return_value;
			}
			//-- 
			
			if($numeric)
			{
				return $years['years'];
			}
			else
			{
				return $years['years'] . " Jahre";
			}
		}

		public function GetTreatedDays($admission, $discharge, $isArray = NULL)
		{
			$Tr = new Zend_View_Helper_Translate();
			$trans_days = $Tr->translate('days');
			$trans_months = $Tr->translate('months');
			$trans_year = $Tr->translate('year');
			list($addYear, $addMonth, $addDay) = explode("-", date("Y-m-d", strtotime($admission)));

			list($disYear, $disMonth, $disDay) = explode("-", date("Y-m-d", strtotime($discharge)));

			if($disMonth < 1)
			{
				$diffstr = '-';
				return $diffstr;
			}

			$distime = mktime(23, 59, 59, $disMonth, $disDay, $disYear);
			$addtime = mktime(0, 0, 0, $addMonth, $addDay, $addYear);
			$years = (int) ($months / 12);

			if($addDay > $disDay)
			{
				$months = $this->diff_in_months($admission, $discharge, -1);
				$months = $months < 0 ? 0 : $months;
				$years = (int) ($months / 12);
				$months = $months % 12;
				$newtime = mktime(0, 0, 0, $addMonth + $months, $addDay, $addYear + $years);
				$days = date("t", $newtime) - date("d", $newtime) + date("d", $distime) + 1;
			}
			else
			{
				$months = $this->diff_in_months($admission, $discharge);
				$months = $months < 0 ? 0 : $months;
				$years = (int) ($months / 12);
				$months = $months % 12;
				$newtime = mktime(0, 0, 0, $addMonth + $months, $addDay, $addYear + $years);
				$days = date("d", $distime) - date("d", $newtime);
			}

			if($addDay == $disDay)
			{
				$days++;
			}

			$diffarr = array();
			$diffarr['years'] = 0;
			$diffarr['months'] = 0;
			$diffarr['days'] = 0;
			if($years > 0)
			{
				$diffstr .= $years . " " . $trans_year . " ,";
				$diffarr['years'] = $years;
			}
			if($months > 0)
			{
				$diffstr .= $months . " " . $Tr->translate('months') . " ,";
				$diffarr['months'] = $months;
			}

			if($days > 0)
			{
				$diffstr .= ($days + 1) . " " . $trans_days;
				$diffarr['days'] = ($days + 1);
			}

			if($isArray == 1)
			{
				return $diffarr;
			}


			return $diffstr; // ."&nbsp;Jahre";
		}

		static public function getDaysInBetween($start, $end, $returnType = false, $format = "Y-m-d")
		{
//		return type = false => returned as string else returned as timestamp
			// Vars
			$day = 86400; // Day in seconds
//			$format = ''; // Output format (see PHP date funciton)
			$sTime = strtotime($start); // Start as time
			$eTime = strtotime($end); // End as time
			$numDays = round(($eTime - $sTime) / $day) + 1;
			$days = array();

			$currtime = $sTime;
			while($currtime <= $eTime)
			{
				$days[] = date($format, $currtime);
				$currtime = strtotime('+1 day', $currtime);
			}

			// Return days
			if($returnType == 'number')
			{
				//return number
				$num_days = ceil(($eTime - $sTime) / $day) + 1; //including end day

				return $num_days;
			}
			else
			{
				//return array with days
				return $days;
			}
		}

		static public function getDaysDiff($start, $end)
		{
			// Vars
			$day = 86400; // Day in seconds
//		$format = 'Y-m-d'; // Output format (see PHP date funciton)


			$gd_a = getdate(strtotime($start));
			$gd_b = getdate(strtotime($end));

			$sTime = mktime(12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year']);
			$eTime = mktime(12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year']);

			$numDays = round(($eTime - $sTime) / $day) + 1;

			return $numDays;
		}

		private function diff_in_months($start_date, $end_date, $additional_months = 0)
		{
			$start_year = date("Y", strtotime($start_date));
			$start_month = date("n", strtotime($start_date));

			$end_year = date("Y", strtotime($end_date));
			$end_month = date("n", strtotime($end_date));

			$diff_in_months = (($end_year - $start_year) * 12 ) - $start_month + $end_month + $additional_months;


			return $diff_in_months;
		}

		public function getTreatedDaysRealMultiple($ipids, $discharged = true)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$days = array();
			$ipid_str = "'999999999',";
			$ipids[] = '999999999';
			foreach($ipids as $ipid)
			{
				$ipid_str .= '"' . $ipid . '",';
			}
			if($discharged)
			{
				$q_discharged = "and p.isdischarged = 1";
			}
			else
			{
				$q_discharged = "";
			}
			$patient = Doctrine_Query::create()
				->select('p.ipid, p.admission_date, e.epid')
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->whereIn('p.ipid', $ipids)
				->andWhere('p.isdelete = 0');
			$patient->andWhere('e.clientid = ' . $logininfo->clientid);
			$admission_data = $patient->fetchArray();

			foreach($admission_data as $adm_item)
			{
				$admision_date_new[$adm_item['ipid']] = $adm_item;
			}


			$discharge = Doctrine_Query::create()
				->select("*")
				->from('PatientDischarge d')
//			->where('d.ipid IN ('.substr($ipid_str,0,-1).') AND d.isdelete="0"');
				->whereIn('d.ipid', $ipids)
				->andWhere('d.isdelete = 0');
			$discharge_data = $discharge->fetchArray();

			foreach($discharge_data as $dis_item)
			{
				$discharge_data_new[$dis_item['ipid']] = $dis_item;
			}

			//get all admision/readmision(1)  discharged/redischarged(2) data
			$readmission_dates = new PatientReadmission();
			$admisiondatesarray = $readmission_dates->getPatientReadmission($ipids, "1");


			foreach($admisiondatesarray as $readm_item)
			{
				if($adm_ipid != $readm_item['ipid'])
				{
					$adm_ipid = $readm_item['ipid'];
				}
				$admisiondatesarray_new[$adm_ipid][] = $readm_item;
			}

			$dischargedatesarray = $readmission_dates->getPatientReadmission($ipids, "2");

			foreach($dischargedatesarray as $redis_item)
			{
				if($dis_ipid != $redis_item['ipid'])
				{
					$dis_ipid = $redis_item['ipid'];
				}
				$dischargedatesarray_new[$dis_ipid][] = $redis_item;
			}

			foreach($admision_date_new as $patient_adm)
			{
				$cipid = $patient_adm['ipid'];
				if(empty($discharge_data_new[$cipid]['discharge_date']))
				{
					$discharge_date = date("d.m.Y");
				}
				else
				{
					$discharge_date = $discharge_data_new[$patient_adm['ipid']]['discharge_date'];
				}

				if($admisiondatesarray_new[$cipid][0]['date_type'] == "1" && strtotime($admisiondatesarray[$cipid][0]['date']) < strtotime($patient_adm['admission_date']))
				{ //we have a first time admision in db
					$admision_date = $admisiondatesarray_new[$cipid][0]['date'];
				}
				else
				{ // we don`t have first time admision
					$admision_date = $patient_adm['admission_date'];
				}

				unset($key_d);
				unset($nextkey);
				unset($val_d);
				unset($start_date);
				unset($end_date);
				unset($daysDischarged);
				unset($daystreated);

				foreach($dischargedatesarray_new[$cipid] as $key_d => $val_d)
				{
					$pms = new PatientMaster();
					$nextkey = $key_d;
					$nextkey++;
					$day = 86400;
					$start_date = date("d.m.Y", strtotime($val_d['date']) + $day);
					$end_date = date("d.m.Y", strtotime($admisiondatesarray_new[$cipid][$nextkey]['date']) - $day);

					if(strlen($admisiondatesarray_new[$cipid][$nextkey]['date']) > 0)
					{
						$daysDischarged += $this->getDaysDiff($start_date, $end_date);
					}
				}

				$daystreated = $this->getDaysDiff($admision_date, $discharge_date);

				$days[$cipid]['realActiveDays'] = ($daystreated - $daysDischarged);
				$days[$cipid]['ActiveDays'] = $daystreated;
				$days[$cipid]['DischargedDays'] = $daysDischarged;
				$days[$cipid]['admissionDates'] = $admisiondatesarray_new[$cipid];
				$days[$cipid]['dischargeDates'] = $dischargedatesarray_new[$cipid];
				$days[$cipid]['admission_date'] = $admision_date;
				$days[$cipid]['discharge_date'] = $discharge_date;
			}

			return $days;
		}

		public function getTreatedDaysReal($ipid)
		{
			//get master data
			$parr = $this->getMasterData(NULL, 0, NULL, $ipid);
			//get old discharged data
			$doc = Doctrine::getTable('PatientDischarge')->findBy('ipid', $ipid);
			$patientarray = $doc->toArray();

			//get all admision/readmision(1)  discharged/redischarged(2) data
			$readmission_dates = new PatientReadmission();
			$admisiondatesarray = $readmission_dates->getPatientReadmission($ipid, "1");
			$dischargedatesarray = $readmission_dates->getPatientReadmission($ipid, "2");

			if(empty($patientarray[0]['discharge_date']))
			{
				$discharge = date("d.m.Y");
			}
			else
			{
				$discharge = $patientarray[0]['discharge_date'];
			}

			if($admisiondatesarray[0]['date_type'] == "1" && strtotime($admisiondatesarray[0]['date']) < strtotime($parr['admission_date']))
			{
				//we have a first time admision in db
				$admision_date = $admisiondatesarray[0]['date'];
			}
			else
			{
				// we don`t have first time admision
				$admision_date = $parr['admission_date'];
			}


			foreach($dischargedatesarray as $key_d => $val_d)
			{
				$pms = new PatientMaster();
				$nextkey = $key_d;
				$nextkey++;
				$day = 86400;
				$start_date = date("d.m.Y", strtotime($val_d['date']) + $day);
				$end_date = date("d.m.Y", strtotime($admisiondatesarray[$nextkey]['date']) - $day);

				if(strlen($admisiondatesarray[$nextkey]['date']) > 0)
				{
					$daysDischarged += $this->getDaysDiff($start_date, $end_date);
				}
			}

			$daystreated = $this->getDaysDiff($admision_date, $discharge);

			$days = array();
			$days['realActiveDays'] = ($daystreated - $daysDischarged);
			$days['ActiveDays'] = $daystreated;
			$days['DischargedDays'] = $daysDischarged;
			$days['admissionDates'] = $admisiondatesarray;
			$days['dischargeDates'] = $dischargedatesarray;
			return $days;
		}

		public function getkrisepatients($ipids)
		{
			$hidemagic = Zend_Registry::get('hidemagic');
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
			$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
			$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
			$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
			$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
			$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
			$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

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
			}

			$qur = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster')
				->where("ipid in(" . $ipids . ") and traffic_status = 3  and isstandby=0 and isdelete=0 ")
				->andWhere("isstandbydelete = 0")
				->groupBy(ipid);
			$droparray = $qur->fetchArray();
			if($droparray)
			{

				return $droparray;
			}
		}

		public static function getAdminVisibility($ipid = '')
		{
			$qur = Doctrine_Query::create()
				->select('isadminvisible')
				->from('PatientMaster')
				->where("ipid = '" . $ipid . "'");
			$check = $qur->fetchArray();
			if($check)
			{
				return $check[0]['isadminvisible'];
			}
		}

		public function getAdminMultiPatientVisibility($ipids)
		{
			$qur = Doctrine_Query::create()
				->select('isadminvisible')
				->from('PatientMaster')
				->whereIn("ipid", $ipids);
			$check = $qur->fetchArray();
			if($check)
			{
				foreach($check as $k_check => $v_check)
				{
					$ret_perms[$v_check['ipid']] = $v_check['isadminvisible'];
				}
				return $ret_perms;
			}
		}
		//ISPC-2614 Ancuta 16-17.07.2020 - added new param
		public function clone_record($source = false, $client_target = false,$intense_connection = false)
		{

			$master_data = $this->getMasterData(null, 0, null, $source, null, true);
 
			if($source && $client_target)
			{
				$ipid = Pms_Uuid::GenerateIpid();
				$cust = new PatientMaster();
				$cust->ipid = $ipid;
				$cust->referred_by = '0';
				$cust->recording_date = date('Y-m-d H:i:s');
				$cust->first_name = Pms_CommonData::aesEncrypt($master_data['first_name']);
				$cust->middle_name = Pms_CommonData::aesEncrypt($master_data['middle_name']);
				$cust->last_name = Pms_CommonData::aesEncrypt($master_data['last_name']);
				$cust->title = Pms_CommonData::aesEncrypt($master_data['title']);
				$cust->salutation = Pms_CommonData::aesEncrypt($master_data['salutation']);
				$cust->street1 = Pms_CommonData::aesEncrypt($master_data['street1']);
				$cust->street2 = Pms_CommonData::aesEncrypt($master_data['street2']);
				$cust->zip = Pms_CommonData::aesEncrypt($master_data['zip']);
				$cust->city = Pms_CommonData::aesEncrypt($master_data['city']);
				$cust->phone = Pms_CommonData::aesEncrypt($master_data['phone']);
				$cust->mobile = Pms_CommonData::aesEncrypt($master_data['mobile']);
				$cust->kontactnumber = $master_data['kontactnumber'];
				$cust->kontactnumbertype = $master_data['kontactnumbertype'];
				$cust->birthd = date('Y-m-d', strtotime($master_data['birthd']));
				$cust->sex = Pms_CommonData::aesEncrypt($master_data['sex']);
				$cust->height = Pms_CommonData::aesEncrypt($master_data['height']);
				if($intense_connection){
				    $admission_date = date('Y-m-d H:i:s', strtotime($master_data['admission_date']));
				    $cust->isstandby = $master_data['isstandby'];
				    $cust->isstandbydelete = $master_data['isstandbydelete'];
				    $cust->isdischarged = $master_data['isdischarged'];
				} else {
    				$admission_date = date('Y-m-d H:i:s');
				    $cust->isstandby = '1';
				}
				$cust->admission_date = $admission_date;
				$cust->nation = $master_datat['nation'];
				$cust->comment = $master_data['comment'];
				$cust->living_will = $master_data['living_will'];
				$cust->living_will_from = $master_data['living_will_from'];
				$cust->living_will_deposited = $master_data['living_will_deposited'];
				//TODO-3792 Ancuta 28.01.2020
				$cust->email = Pms_CommonData::aesEncrypt($master_data['email']);
				//--
				$cust->save();

				
				
				
				if($cust)
				{
				    if(!$intense_connection){
    					$patient_standby_details = new PatientStandbyDetails();
    					$patient_standby_details->ipid = $ipid;
    					$patient_standby_details->date = $admission_date;
    					$patient_standby_details->date_type = "1";
    					$patient_standby_details->comment = "Patient SHARE";
    					$patient_standby_details->save();
    					
    					$patient_standby_details = new PatientStandby();
    					$patient_standby_details->ipid = $ipid;
    					$patient_standby_details->start = date('Y-m-d',strtotime($admission_date));
    					$patient_standby_details->save();
				    }
									
					$data['familydoc_id'] = $master_data['familydoc_id'];
					$data['familydoc_id_qpa'] = $master_data['familydoc_id_qpa'];
					$data['ipid'] = $ipid;
					return $data;
				}
				else
				{
					return false; //abort cloning
				}
			}
		}

		public function check_patients_exists($patients)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if (empty($patients) || empty($clientid)) {
			    return;
			}
			
			$sql_names = '';
			foreach($patients as $k_pat => $v_pat)
			{
				$sql_names .=" (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_pat['first_name'] . "'))
and trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_pat['last_name'] . "'))
and p.birthd ='" . date('Y-m-d', strtotime($v_pat['dob'])) . "') OR
(trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_pat['last_name'] . "'))
and trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_pat['first_name'] . "'))
and p.birthd ='" . date('Y-m-d', strtotime($v_pat['dob'])) . "') OR ";
			}

			$sql_q = "SELECT *, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
				AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,
				AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
				AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,
				AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
				AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
				AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip
				,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
				,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone
				,AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile
				,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex
				FROM patient_master p
				LEFT JOIN epid_ipid e ON p.ipid = e.ipid
				WHERE (p.isdelete = '0' and e.clientid='" . $logininfo->clientid . "')
				AND (" . substr($sql_names, 0, -3) . ")";


			$conn = Doctrine_Manager::getInstance()->getConnection('IDAT');
			$r = $conn->execute($sql_q);

			$patarr = $r->fetchAll();

			foreach($patients as $k_in_pat => $v_in_pat)
			{
				foreach($patarr as $k_out_pat => $v_out_pat)
				{
					if(($v_in_pat['first_name'] == $v_out_pat['first_name'] || $v_in_pat['first_name'] == $v_out_pat['last_name'] || $v_out_pat['last_name'] == $v_in_pat['last_name']) && strtotime($v_in_pat['dob']) == strtotime($v_out_pat['birthd']))
					{
						$found_patients[$k_in_pat] = $v_out_pat;
					}
				}
			}

			return $found_patients;
		}

		public function import_patient($patient_import_details)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$pm = new PatientMaster();


			foreach($patient_import_details as $k_patient => $v_patient)
			{
				$patients_verification = array();
				$patient_exists = '';

				$patients_verification[$k_patient]['first_name'] = $v_patient['PatName']['Vorname'];
				$patients_verification[$k_patient]['last_name'] = $v_patient['PatName']['Nachname'];
				$patients_verification[$k_patient]['dob'] = $v_patient['PatDetail']['GebDat'];

				$patient_exists = $pm->check_patients_exists($patients_verification);

				if(!$patient_exists)
				{
					//				array_cleanup
					foreach($v_patient as $k_key => $v_value)
					{
						foreach($v_value as $k_key_v => $v_val_v)
						{
							if(!empty($v_val_v))
							{
								$vv_patient[$k_key][$k_key_v] = $v_val_v;
							}
						}
					}

					if($v_patient['PatDetail']['Geschlecht'] == 'M')
					{
						$sex = '1';
					}
					else if($v_patient['PatDetail']['Geschlecht'] == 'W')
					{
						$sex = '2';
					}
					else
					{
					    $sex = ''; //ISPC-2442 @Lore   30.09.2019
					}

//					1. new ipid and epid + Patienten Nummer Import : xxxxxxxx
					$ipid = Pms_Uuid::GenerateIpid(); //generate new patient ipid
					$epid = Pms_Uuid::GenerateEpid($clientid);
					$sortepid = Pms_Uuid::GenerateSortEpid($clientid);


					$res = new EpidIpidMapping();
					$res->clientid = $clientid;
					$res->ipid = $ipid;
					$res->epid = $epid;
					$res->epid_chars = $sortepid['epid_chars'];
					$res->epid_num = $sortepid['epid_num'];
					$res->save();

					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt('K');
					$cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Patienten Nummer Import :' . $v_patient['Header']['PatNr']));
					$cust->tabname = Pms_CommonData::aesEncrypt(addslashes('imported_patient'));
					$cust->user_id = $userid;
					$cust->save();

//					1.1. Family doctor
					if(!empty($vv_patient['HAName']) && !empty($vv_patient['HAAdresse']))
					{
						$fdoc = new FamilyDoctor();
						$fdoc->clientid = $clientid;
						if(!empty($v_patient['HAName']['Nachname']))
						{
							$fdoc->last_name = $v_patient['HAName']['Nachname'];
						}

						if(!empty($v_patient['HAName']['Vorname']))
						{
							$fdoc->first_name = $v_patient['HAName']['Vorname'];
						}

						if(!empty($v_patient['HAAdresse']['Strasse']))
						{
							$fdoc->street1 = $v_patient['HAAdresse']['Strasse'];
						}

						if(!empty($v_patient['HAAdresse']['PLZ']))
						{
							$fdoc->zip = $v_patient['HAAdresse']['PLZ'];
						}


						$fdoc->indrop = '1';

						if(!empty($v_patient['HAAdresse']['Wohnort']))
						{
							$fdoc->city = $v_patient['HAAdresse']['Wohnort'];
						}

						if(!empty($v_patient['HAAdresse']['Telefon']))
						{
							$fdoc->phone_practice = $v_patient['HAAdresse']['Telefon'];
						}
						$fdoc->save();
						$fam_doc_id = $fdoc->id;
					}


//					1.2 Patient master insert
					$cust = new PatientMaster();
					$cust->ipid = $ipid;
					$cust->referred_by = '0';
					$cust->recording_date = date('Y-m-d H:i:s');
					if(!empty($v_patient['PatName']['Vorname']))
					{
						$cust->first_name = Pms_CommonData::aesEncrypt($v_patient['PatName']['Vorname']);
					}

					if(!empty($v_patient['PatName']['Nachname']))
					{
						$cust->last_name = Pms_CommonData::aesEncrypt($v_patient['PatName']['Nachname']);
					}

					if(!empty($v_patient['PatAdresse']['Strasse']))
					{
						$cust->street1 = Pms_CommonData::aesEncrypt($v_patient['PatAdresse']['Strasse']);
					}

					if(!empty($v_patient['PatAdresse']['PLZ']))
					{
						$cust->zip = Pms_CommonData::aesEncrypt($v_patient['PatAdresse']['PLZ']);
					}

					if(!empty($v_patient['PatAdresse']['Wohnort']))
					{
						$cust->city = Pms_CommonData::aesEncrypt($v_patient['PatAdresse']['Wohnort']);
					}

					if(!empty($v_patient['PatAdresse']['Telefon']))
					{
						$cust->phone = Pms_CommonData::aesEncrypt($v_patient['PatAdresse']['Telefon']);
					}

					$cust->admission_date = date('Y-m-d H:i:s', time());
					$cust->birthd = date('Y-m-d', strtotime($v_patient['PatDetail']['GebDat']));

					$cust->familydoc_id = $fam_doc_id;
					$cust->sex = Pms_CommonData::aesEncrypt($sex);
					$cust->isstandby = '1';
					$cust->save();
					$patient_id = $cust->id;

//					2.Stammdaten
					$stamm = new Stammdatenerweitert();
					$stamm->ipid = $ipid;

					if(!empty($v_patient['PatDetail']['Nationalitaet']))
					{
						if(strtolower($v_patient['PatDetail']['Nationalitaet']) == 'de')
						{
							$stamm->stastszugehorigkeit = '1';
						}
						else
						{
							$stamm->stastszugehorigkeit = '2';
							$stamm->anderefree = $v_patient['PatDetail']['Nationalitaet'];
						}
					}
					$stamm->save();

//					3.Contact person
					if(!empty($vv_patient['AngAdresse']) && !empty($vv_patient['AngName']))
					{
						$cust = new ContactPersonMaster();
						$cust->ipid = $ipid;
						if(!empty($v_patient['AngName']['Vorname']))
						{
							$cust->cnt_first_name = Pms_CommonData::aesEncrypt($v_patient['AngName']['Vorname']);
						}

						if(!empty($v_patient['AngName']['Nachname']))
						{
							$cust->cnt_last_name = Pms_CommonData::aesEncrypt($v_patient['AngName']['Nachname']);
						}

						if(!empty($v_patient['AngAdresse']['Strasse']))
						{
							$cust->cnt_street1 = Pms_CommonData::aesEncrypt($v_patient['AngAdresse']['Strasse']);
						}

						if(!empty($v_patient['AngAdresse']['PLZ']))
						{
							$cust->cnt_zip = Pms_CommonData::aesEncrypt($v_patient['AngAdresse']['PLZ']);
						}

						if(!empty($v_patient['AngAdresse']['Wohnort']))
						{
							$cust->cnt_city = Pms_CommonData::aesEncrypt($v_patient['AngAdresse']['Wohnort']);
						}

						if(!empty($v_patient['AngAdresse']['Telefon']))
						{
							$cust->cnt_phone = Pms_CommonData::aesEncrypt($v_patient['AngAdresse']['Telefon']);
						}

						$cust->save();
					}


//					4. diagnosis
					//get diagnosis types
					$abb = "'HD','ND'";
					$dg = new DiagnosisType();
					$darr = $dg->getDiagnosisTypes($clientid, $abb);


					foreach($darr as $diag_types_arr)
					{
						$type_arr[$diag_types_arr['abbrevation']] = $diag_types_arr['id'];
					}

					//4.1 side diagnosis
					if(array_key_exists('0', $v_patient['NebenDiagnose'])) //we have multiple diagnosis
					{
						foreach($v_patient['NebenDiagnose'] as $k_nb_diag => $v_nb_diag)
						{
							$inserted_nb_diag = '';

							if(!empty($v_nb_diag['Bemerkung']) && !empty($v_nb_diag['ICD']))
							{
								$nb_diag = new DiagnosisText();
								$nb_diag->clientid = $clientid;
								$nb_diag->sys_id = '0';
								$nb_diag->icd_primary = strtoupper(trim($v_nb_diag['ICD']));
								$nb_diag->free_name = $v_nb_diag['Bemerkung'];
								$nb_diag->save();

								$inserted_nb_diag = $nb_diag->id;

								$pat_nb_diag = new PatientDiagnosis();
								$pat_nb_diag->ipid = $ipid;
								$pat_nb_diag->diagnosis_type_id = $type_arr['ND']; //side diagnosis
								$pat_nb_diag->diagnosis_id = $inserted_nb_diag;
								$pat_nb_diag->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
								$pat_nb_diag->save();

								$cust = new PatientCourse();
								$cust->ipid = $ipid;
								$cust->course_date = date("Y-m-d H:i:s", time());
								$cust->course_type = Pms_CommonData::aesEncrypt('D');
								$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($v_nb_diag['Bemerkung']));
								$cust->user_id = $userid;
								$cust->save();
							}
						}
					}
					else //just one diagnosis
					{
						$inserted_nb_diag = '';

						if(!empty($v_patient['NebenDiagnose']['Bemerkung']) && !empty($v_patient['NebenDiagnose']['ICD']))
						{
							$nb_diag = new DiagnosisText();
							$nb_diag->clientid = $clientid;
							$nb_diag->sys_id = '0';
							$nb_diag->icd_primary = strtoupper(trim($v_patient['NebenDiagnose']['ICD']));
							$nb_diag->free_name = $v_patient['NebenDiagnose']['Bemerkung'];
							$nb_diag->save();

							$inserted_nb_diag = $nb_diag->id;

							$pat_nb_diag = new PatientDiagnosis();
							$pat_nb_diag->ipid = $ipid;
							$pat_nb_diag->diagnosis_type_id = $type_arr['ND']; //side diagnosis
							$pat_nb_diag->diagnosis_id = $inserted_nb_diag;
							$pat_nb_diag->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
							$pat_nb_diag->save();

							$cust = new PatientCourse();
							$cust->ipid = $ipid;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt('D');
							$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($v_patient['NebenDiagnose']['Bemerkung']));
							$cust->user_id = $userid;
							$cust->save();
						}
					}



					//4.2 main diagnosis #1
					if(array_key_exists('0', $v_patient['FAHauptDiagnose'])) //we have multiple diagnosis
					{
						foreach($v_patient['FAHauptDiagnose'] as $k_fah_diag => $v_fah_diag)
						{
							$inserted_fah_diag = '';
							if(!empty($v_fah_diag['Bemerkung']) && !empty($v_fah_diag['ICD']))
							{
								$fah_diag = new DiagnosisText();
								$fah_diag->clientid = $clientid;
								$fah_diag->sys_id = '0';
								$fah_diag->icd_primary = strtoupper(trim($v_fah_diag['ICD']));
								$fah_diag->free_name = $v_fah_diag['Bemerkung'];
								$fah_diag->save();

								$inserted_fah_diag = $fah_diag->id;

								$pat_fah_diag = new PatientDiagnosis();
								$pat_fah_diag->ipid = $ipid;
								$pat_fah_diag->diagnosis_type_id = $type_arr['HD']; //main diagnosis
								$pat_fah_diag->diagnosis_id = $inserted_fah_diag;
								$pat_fah_diag->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
								$pat_fah_diag->save();

								$cust = new PatientCourse();
								$cust->ipid = $ipid;
								$cust->course_date = date("Y-m-d H:i:s", time());
								$cust->course_type = Pms_CommonData::aesEncrypt('H');
								$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($v_fah_diag['Bemerkung']));
								$cust->user_id = $userid;
								$cust->save();
							}
						}
					}
					else //just one diagnosis
					{
						$inserted_fah_diag = '';
						if(!empty($v_patient['FAHauptDiagnose']['Bemerkung']) && !empty($v_patient['FAHauptDiagnose']['ICD']))
						{
							$fah_diag = new DiagnosisText();
							$fah_diag->clientid = $clientid;
							$fah_diag->sys_id = '0';
							$fah_diag->icd_primary = strtoupper(trim($v_patient['FAHauptDiagnose']['ICD']));
							$fah_diag->free_name = $v_patient['FAHauptDiagnose']['Bemerkung'];
							$fah_diag->save();

							$inserted_fah_diag = $fah_diag->id;

							$pat_nb_diag = new PatientDiagnosis();
							$pat_nb_diag->ipid = $ipid;
							$pat_nb_diag->diagnosis_type_id = $type_arr['HD']; //main diagnosis
							$pat_nb_diag->diagnosis_id = $inserted_fah_diag;
							$pat_nb_diag->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
							$pat_nb_diag->save();

							$cust = new PatientCourse();
							$cust->ipid = $ipid;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt('H');
							$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($v_patient['FAHauptDiagnose']['Bemerkung']));
							$cust->user_id = $userid;
							$cust->save();
						}
					}

					//4.3 main diagnosis #2
					//+ if this is same as FAHauptDiagnose no need to add this one
					if(array_key_exists('0', $v_patient['KHHauptDiagnose'])) //we have multiple diagnosis
					{
						foreach($v_patient['KHHauptDiagnose'] as $k_khh_diag => $v_khh_diag)
						{
							$inserted_khh_diag = '';
							if(!empty($v_khh_diag['Bemerkung']) && !empty($v_khh_diag['ICD']))
							{
								$khh_diag = new DiagnosisText();
								$khh_diag->clientid = $clientid;
								$khh_diag->sys_id = '0';
								$khh_diag->icd_primary = strtoupper(trim($v_khh_diag['ICD']));
								$khh_diag->free_name = $v_khh_diag['Bemerkung'];
								$khh_diag->save();

								$inserted_khh_diag = $khh_diag->id;

								$pat_khh_diag = new PatientDiagnosis();
								$pat_khh_diag->ipid = $ipid;
								$pat_khh_diag->diagnosis_type_id = $type_arr['HD']; //main diagnosis
								$pat_khh_diag->diagnosis_id = $inserted_khh_diag;
								$pat_khh_diag->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
								$pat_khh_diag->save();

								$cust = new PatientCourse();
								$cust->ipid = $ipid;
								$cust->course_date = date("Y-m-d H:i:s", time());
								$cust->course_type = Pms_CommonData::aesEncrypt('H');
								$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($v_khh_diag['Bemerkung']));
								$cust->user_id = $userid;
								$cust->save();
							}
						}
					}
					else //just one diagnosis
					{
						if($v_patient['FAHauptDiagnose']['ICD'] != $v_patient['KHHauptDiagnose']['ICD'] &&
							$v_patient['FAHauptDiagnose']['Bemerkung'] != $v_patient['KHHauptDiagnose']['Bemerkung'] && count($v_patient['FAHauptDiagnose']) == count($v_patient['KHHauptDiagnose']))
						{


							$inserted_khh_diag = '';
							if(!empty($v_patient['KHHauptDiagnose']['Bemerkung']) && !empty($v_patient['KHHauptDiagnose']['ICD']))
							{
								$khh_diag = new DiagnosisText();
								$khh_diag->clientid = $clientid;
								$khh_diag->sys_id = '0';
								$khh_diag->icd_primary = strtoupper(trim($v_patient['KHHauptDiagnose']['ICD']));
								$khh_diag->free_name = $v_patient['KHHauptDiagnose']['Bemerkung'];
								$khh_diag->save();

								$inserted_khh_diag = $khh_diag->id;

								$pat_khh_diag = new PatientDiagnosis();
								$pat_khh_diag->ipid = $ipid;
								$pat_khh_diag->diagnosis_type_id = $type_arr['HD']; //main diagnosis
								$pat_khh_diag->diagnosis_id = $inserted_khh_diag;
								$pat_khh_diag->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
								$pat_khh_diag->save();

								$cust = new PatientCourse();
								$cust->ipid = $ipid;
								$cust->course_date = date("Y-m-d H:i:s", time());
								$cust->course_type = Pms_CommonData::aesEncrypt('H');
								$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($v_patient['KHHauptDiagnose']['Bemerkung']));
								$cust->user_id = $userid;
								$cust->save();
							}
						}
					}


					$returned_data[$epid]['id'] = $patient_id;
					$returned_data[$epid]['first_name'] = $v_patient['PatName']['Vorname'];
					$returned_data[$epid]['last_name'] = $v_patient['PatName']['Nachname'];
				}
			}

			return $returned_data;
		}

		public function patient_valid_sapv($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$patientmaster = new PatientMaster();

			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('verordnungbis !="000-00-00 00:00:00" ')
				->andWhere('verordnungam !="000-00-00 00:00:00" ')
				->andWhere('isdelete=0')
				->andWhere('status != 1 ')
				->orderBy('verordnungam ASC');
			$sapv_array = $dropSapv->fetchArray();

			$s = 1;
			$sapv['sapv_intervals'] = array();
			foreach($sapv_array as $sapvkey => $sapvvalue)
			{

				$sapv['patient_sapv'][$sapvvalue['id']]['all_types'] = explode(',', $sapvvalue['verordnet']);

				$sapv['patient_sapv'][$sapvvalue['id']]['type'] = $sapvvalue['verordnet'];
				$sapv['patient_sapv'][$sapvvalue['id']]['from'] = $sapvvalue['verordnungam'];
				$sapv['patient_sapv'][$sapvvalue['id']]['till'] = $sapvvalue['verordnungbis'];

				$sapv['sapv_start_days'][] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));

				$sapv['sapv_intervals'][$s]['start'] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));
				$sapv['sapv_intervals'][$s]['end'] = date('Y-m-d', strtotime($sapvvalue['verordnungbis']));

				$patient_active_sapv[] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);

				if(in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details['tv_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
				}
				if(in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details['vv_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
				}


				$s++;
			}
			asort($sapv['sapv_start_days']);


			foreach($sapv_details['tv_days'] as $ktvs => $tv_intervals)
			{
				foreach($tv_intervals as $tv_days)
				{
					$sapv['tv_days'][] = $tv_days;
				}
			}
			asort($sapv['tv_days']);
			$sapv['tv_days'] = array_unique($sapv['tv_days']);

			foreach($sapv_details['vv_days'] as $kvvs => $vv_intervals)
			{
				foreach($vv_intervals as $vv_days)
				{
					$sapv['vv_days'][] = $vv_days;
				}
			}
			asort($sapv['vv_days']);
			$sapv['vv_days'] = array_unique($sapv['vv_days']);

			foreach($patient_active_sapv as $sinter => $sinterval_days)
			{
				foreach($sinterval_days as $sdays)
				{
					$sapv['sapv_days_overall'][] = $sdays;
				}
			}
			asort($sapv['sapv_days_overall']);
			$sapv['sapv_days'] = array_unique($sapv['sapv_days_overall']);

			return $sapv;
		}

		/**
		 * 
		 * Jul 19, 2017 @claudiu
		 * - missleading name, this is only a setter
		 * - unset() at the end of fn... someone not trusting the gc or other developers to not fillup the entire memory 
		 * - read section 8.5  <like predicate> http://www.contrib.andrew.cmu.edu/~shadow/sql/sql1992.txt
		 * - to understand the difference between 'LIKE' and '=' 
		 * 
		 * @param string $ipid
		 */
		public function get_patient_admissions($ipid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientReadmission')
				->where('ipid LIKE "' . $ipid . '"')
				->orderBy('date ASC');
			$q_res = $q->fetchArray();

			//"new" patient - data in readmission
			if($q_res)
			{
				$incr = '0';
				foreach($q_res as $k_patient_date => $v_patient_date)
				{
					//date_type switcher
					if($v_patient_date['date_type'] == '1')
					{
						$type = 'start';
					}
					else
					{
						$type = 'end';
					}

					$patient_admission[$incr][$type] = $v_patient_date['date'];

					//check next item (which is supposed to be discharge) exists
					if($v_patient_date['date_type'] == '1' && !array_key_exists(($k_patient_date + 1), $q_res))
					{
						$patient_admission[$incr]['end'] = '';
					}

					//increment when reaching end dates(date_type=2)
					if($v_patient_date['date_type'] == '2')
					{
						$incr++;
					}
				}
			}
			else
			{
				//"old patient" - check if patient has admission(PM)/discharge(PD)
				$q_pm = Doctrine_Query::create()
					->select('*')
					->from('PatientMaster')
					->andWhere('ipid LIKE "' . $ipid . '"');
				$q_pm_res = $q_pm->fetchArray();

				if($q_pm_res)
				{
					$patient_admission[0]['start'] = $q_pm_res[0]['admission_date'];

					if($q_pm_res[0]['isdischarged'] == '1')
					{
						$q_pd = Doctrine_Query::create()
							->select('*')
							->from('PatientDischarge')
							->where('ipid LIKE "' . $ipid . '"')
							->andWhere('isdelete = "0"');
						$q_pd_res = $q_pd->fetchArray();

						if($q_pd_res)
						{
							$patient_admission[0]['end'] = $q_pd_res[0]['discharge_date'];
						}
					}
					else
					{
						$patient_admission[0]['end'] = '';
					}
				}
			}


			if(count($patient_admission) > '0')
			{
				$del_old_active_data = Doctrine_Query::create()
					->delete('PatientActive pa')
					->where('pa.ipid LIKE "' . $ipid . '"');
				$del_old_active_data->execute();

				foreach($patient_admission as $k_adm_cycle => $v_cycle_data)
				{
					if(strlen($v_cycle_data['end']) == '0')
					{
						$end_date = '0000-00-00';
					}
					else
					{
						$end_date = date('Y-m-d', strtotime($v_cycle_data['end']));
					}

					$cycle_records[] = array(
						'ipid' => $ipid,
						'start' => date('Y-m-d', strtotime($v_cycle_data['start'])),
						'end' => $end_date
					);
				}

				$collection = new Doctrine_Collection('PatientActive');
				$collection->fromArray($cycle_records);
				$collection->save();
			}
			unset($cycle_records);
			unset($patient_admission);
			unset($q_res);
			unset($q_pm_res);
			unset($q_pd_res);
			unset($del_old_active_data);

//		return $patient_admission;
		}

		public function update_all_patients_second()
		{
			exit;
			set_time_limit(0);
			//get all patients
			$q_pats = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster pm')
				->orderBy('id DESC');
			$q_pats_res = $q_pats->fetchArray();

			foreach($q_pats_res as $k_pat => $v_pat)
			{
				PatientMaster::get_patient_admissions($v_pat['ipid']);
			}
		}

		public function update_all_patients()
		{
			//get all patients
			$q_pats = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster pm')
				->orderBy('id DESC');
			$q_pats_res = $q_pats->fetchArray();

			$i = 1;
			$pat_ipids[] = '9999999999999';
			foreach($q_pats_res as $k_pat => $v_pat)
			{
				if($i <= '20000')
				{
					$pat_ipids[] = $v_pat['ipid'];
					$pat_data[$v_pat['ipid']] = $v_pat;
				}
				$i++;
			}

			//get readmissions for all
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientReadmission')
				->whereIn('ipid', $pat_ipids)
				->orderBy('date ASC');
			$q_res = $q->fetchArray();


			foreach($q_res as $k_pat_readm => $v_pat_readm)
			{
				$q_res_readmissions[$v_pat_readm['ipid']][] = $v_pat_readm;
			}


			//get discharges for all
			$q_pd = Doctrine_Query::create()
				->select('*')
				->from('PatientDischarge')
				->whereIn('ipid', $pat_ipids)
				->andWhere('isdelete = "0"');
			$q_pd_res = $q_pd->fetchArray();

			foreach($q_pd_res as $k_pat_dis => $v_pat_dis)
			{
				$q_pd_res_arr[$v_pat_dis['ipid']] = $v_pat_dis;
			}

			foreach($pat_data as $k_patient => $v_patient)
			{
				if(count($q_res_readmissions[$v_patient['ipid']]) > '0') //new patient with data in admission/readmission
				{
					$incr = '0';
					foreach($q_res_readmissions[$v_patient['ipid']] as $k_patient_date => $v_patient_date)
					{
						$patient_admission[$v_patient['ipid']][$incr]['type'] = 'R';
						//date_type switcher
						if($v_patient_date['date_type'] == '1')
						{
							$type = 'start';
						}
						else
						{
							$type = 'end';
						}

						$patient_admission[$v_patient['ipid']][$incr][$type] = $v_patient_date['date'];

						//check next item (which is supposed to be discharge) exists
						if($v_patient_date['date_type'] == '1' && !array_key_exists(($k_patient_date + 1), $q_res_readmissions[$v_patient['ipid']]))
						{
							$patient_admission[$v_patient['ipid']][$incr]['end'] = '';
						}

						//increment when reaching end dates(date_type=2)
						if($v_patient_date['date_type'] == '2')
						{
							$incr++;
						}
					}
				}
				else //old patient no data in admission/readmission
				{
					$patient_admission[$v_patient['ipid']][0]['type'] = 'A';
					$patient_admission[$v_patient['ipid']][0]['start'] = $v_patient['admission_date'];

					if($v_patient['isdischarged'] == '1')
					{

						if($q_pd_res_arr[$v_patient['ipid']])
						{
							$patient_admission[$v_patient['ipid']][0]['end'] = $q_pd_res_arr['discharge_date'];
						}
					}
					else
					{
						$patient_admission[$v_patient['ipid']][0]['end'] = '';
					}
				}
			}

			if(count($patient_admission) > '0')
			{
				$del_ipids = array_keys($patient_admission);
				$del_ipids[] = '999999999';
				$del_old_active_data = Doctrine_Query::create()
					->delete('PatientActive pa')
					->whereIn('pa.ipid', $del_ipids);
				$del_old_active_data->execute();

				foreach($patient_admission as $k_adm_cycle => $v_cycle_data)
				{
					foreach($v_cycle_data as $k_cycle => $v_cycle)
					{
						if(strlen($v_cycle['end']) == '0')
						{
							$end_date = '0000-00-00';
						}
						else
						{
							$end_date = date('Y-m-d', strtotime($v_cycle['end']));
						}

						$cycle_records[] = array(
							'ipid' => $k_adm_cycle,
							'start' => date('Y-m-d', strtotime($v_cycle['start'])),
							'end' => $end_date
						);
					}
				}



				$collection = new Doctrine_Collection('PatientActive');
				$collection->fromArray($cycle_records);
				$collection->save();
			}

//		return $patient_admission;
		}

		public function get_treatment_days()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(empty($ipids))
			{
				$ipids[] = '999999999';
			}
			$patient = Doctrine_Query::create()
				->select("sum(datediff(if(a.end != '0000-00-00',a.end,now()), a.start) + 1) as dot, a.ipid")
				->from('PatientActive a')
				//->whereIn('a.ipid', $ipids)
				->leftJoin("a.EpidIpidMapping e")
				->where('e.clientid = "' . $clientid . '"')
				->groupBy('a.ipid');


			$admission_data = $patient->fetchArray();

			if($admission_data)
			{
				foreach($admission_data as $key => $patient_details)
				{
					$patient_dot[$patient_details['ipid']]['days_of_treatment'] = $patient_details['dot'];
				}
				return $patient_dot;
			}
			else
			{
				return false;
			}
		}

		public function getAllDaysInBetween($start, $end, $returnType = false)
		{
//		return type = false => returned as string else returned as timestamp
			// Vars
			$day = 86400; // Day in seconds
			$format = 'Y-m-d'; // Output format (see PHP date funciton)
			$sTime = strtotime(date($format, strtotime($start))); // Start as time
			$eTime = strtotime(date($format, strtotime($end))); // End as time
			$numDays = round(($eTime - $sTime) / $day) + 1;
			$days = array();

			$currtime = $sTime;
			while($currtime <= $eTime)
			{
				$days[] = date($format, $currtime);
				$currtime = strtotime('+1 day', $currtime);
			}

			// Return days
			if($returnType == 'number')
			{
				//return number
				$num_days = ceil(($eTime - $sTime) / $day) + 1; //including end day

				return $num_days;
			}
			else
			{
				//return array with days
				return $days;
			}
		}

		
		//used for cronjobs and eventTriggers
		public function get_multiple_patients_details($ipids = array(), $full = false)
		{
			if( empty($ipids) || ! is_array($ipids)) {
				return false;
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$groupid = $logininfo->groupid;

// 			if(count($ipids) == 0)
// 			{
// 				$ipids[] = '99999999999';
// 			}
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

			$hidemagic = Zend_Registry::get('hidemagic');
			$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
			$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
			$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
			$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
			$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
			$sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber";
			$sql .= ",kontactnumbertype as kontactnumbertype";
			$sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber_dec";
			$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
			$sql .= ",AES_DECRYPT(email,'" . Zend_Registry::get('salt') . "') as email";
			$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
			$sql .= ",AES_DECRYPT(height,'" . Zend_Registry::get('salt') . "') as height";

			$isadmin = 0;
			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA' && $controller != 'cron')
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
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as kontactnumber, ";
				$sql .= "AES_DECRYPT(kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber_dec,";
				$sql .= "AES_DECRYPT(kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(email,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as email, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(height,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as height, ";
			}


			
// 			if($full){
			    
// 			    $patient = Doctrine_Query::create()
// 			    ->select($sql.", e.* ")
// 			    ->from('PatientMaster p')
// 			    ->where('p.isdelete = 0')
// 			    ->whereIn('p.ipid', $ipids);
// 			    $patient->leftJoin("p.EpidIpidMapping e");
// 			    $patients_res = $patient->fetchArray();
			    
// 			} else {
			    
//     			$pt = Doctrine_Query::create()
//     				->select($sql)
//     				->from('PatientMaster')
//     				->whereIn('ipid', $ipids);
//     			$patients_res = $pt->fetchArray();
// 			}
			
			$patient = Doctrine_Query::create()
			->select($sql.", e.* ")
			->from('PatientMaster p')
			->whereIn('p.ipid', $ipids);
			$patient->leftJoin("p.EpidIpidMapping e");
			
			if( $full) {
				$patient->andWhere('p.isdelete = 0');
			}
			
			$patients_res = $patient->fetchArray();

			
			
			if($patients_res)
			{
				foreach($patients_res as $k_pat => $v_pat)
				{
					$patient_details[$v_pat['ipid']] = $v_pat;
				}

				return $patient_details;
			}
			else
			{
				return false;
			}
		}

		public function get_patients_ipids($pids)
		{
			if(is_array($pids))
			{
				$pat_ids = $pids;
			}
			else
			{
				$pat_ids = array($pids);
			}
			$pat_ids = array_values($pat_ids);
			$pat_ids[] = '9999999999';

			$pt = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster')
				->whereIn('id', $pat_ids);
			$patients_res = $pt->fetchArray();

			if($patients_res)
			{
				foreach($patients_res as $k_pat => $v_pat)
				{
					$patient_details[$v_pat['id']] = $v_pat['ipid'];
				}

				return $patient_details;
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_patients_details_dta($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$groupid = $logininfo->groupid;

			if(count($ipids) == 0)
			{
				$ipids[] = '99999999999';
			}


			$hidemagic = Zend_Registry::get('hidemagic');
			$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
			$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
			$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
			$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
			$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
			$sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber";
			$sql .= ",kontactnumbertype as kontactnumbertype";
			$sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber_dec";
			$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
			$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			/* $isadmin = 0;
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
			  $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as kontactnumber, ";
			  $sql .= "AES_DECRYPT(kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber_dec,";
			  $sql .= "AES_DECRYPT(kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,";
			  $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
			  $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
			  $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			  } */

			$pt = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster')
				->whereIn('ipid', $ipids);
			$patients_res = $pt->fetchArray();

			if($patients_res)
			{
				foreach($patients_res as $k_pat => $v_pat)
				{
					$patient_details[$v_pat['ipid']] = $v_pat;
				}

				return $patient_details;
			}
			else
			{
				return false;
			}
		}

		public function quick_readmission($ipid, $date_time, $transition = "active")
		{
			
			
			$patient_data_q = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete = "0"');
			$patient_data_res = $patient_data_q->fetchArray();
			
			if($patient_data_res[0])
			{
				$readmit = false;
				//we have patient
				if($patient_data_res[0]['isdischarged'] == "1" && $patient_data_res[0]['isstandby'] == "0")
				{
 
				    /* --------------- get discharge methods  ------------------------------------ */
				    $logininfo = new Zend_Session_Namespace('Login_Info');
				    $clientid = $logininfo->clientid;
				    $dis = new DischargeMethod();
				    $discharge_methods = $dis->getDischargeMethod($clientid, 0);
				    
				    foreach($discharge_methods as $dischargeM)
				    {
				        if($dischargeM['abbr'] == "TOD" || $dischargeM['abbr'] == "TODNA")
				        {
				            $death_methods[] = $dischargeM['id'];
				        }
				    }
				    $death_methods = array_values(array_unique($death_methods));
					
				    $patient_discharge = PatientDischarge::getPatientDischarge($ipid);
				    
				    $disddata = new PatientDeath();
				    $patientdeath = $disddata->getPatientDeath($ipid); //check out if is dead by death button
				    
				    if(!in_array($patient_discharge[0]['discharge_method'], $death_methods)  && !$patientdeath ) //discharged but not dead
				    {
				    	if($transition == "standby"){
	    					//move to standby discharged patient
	    					$readmit = Application_Form_PatientMaster::move_to_standby_discharged_patient($ipid, $date_time,'');
				    	} else {
	    					//readmit discharged patient
	    					$readmit = Application_Form_PatientMaster::readmit_discharged_patient($ipid, $date_time);
				    	}
				    }
					 else{
    					$readmit = false;
					 }
					
				}
				else if($patient_data_res[0]['isdischarged'] == "0" && $patient_data_res[0]['isstandby'] == "1")
				{
					//readmit standby patient
					$commet = "Move to active - activate patient from standby icon";
					$readmit = Application_Form_PatientMaster::readmit_standby_patient($ipid, $date_time,$commet);
				}
				else
				{
					$readmit = false;
				}

				return $readmit;
			}
			else
			{
				return false;
			}
		}

		
		public function ecog_values($ipid, $first = false ,$last =false){
		    
		    /* ISPC-1775,ISPC-1678 */
		    
		    /* ----------------- Patient Details - Deleted visits ----------------------------------------- */
		    $deleted_visits = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		    ->from('PatientCourse')
		    ->where("ipid LIKE  '" . $ipid . "' ")
		    ->andWhere('wrong=1')
		    ->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
		    ->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('visit_koordination_form')) . "'" . ' OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_doctor_form")) . '" OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_nurse_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_doctor_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_nurse_form")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("bayern_doctorvisit")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("contact_form")) . '"  ');
		    $deleted_visits_array = $deleted_visits->fetchArray();
		     
		    $del_visits['kvno_doctor_form'][] = '999999999999';
		    $del_visits['bayern_doctorvisit'][] = '999999999999';
		    $del_visits['contact_form'][] = '999999999999';
		     
		    foreach($deleted_visits_array as $k_del_visit => $v_del_visit)
		    {
		        $del_visits[$v_del_visit['tabname']][] = $v_del_visit['recordid'];
		    }
		
		
		    $kvnodoc = Doctrine_Query::create()
		    ->select("*,kvno_ecog as ecog,start_date as date")
		    ->from('KvnoDoctor')
		    ->where("ipid='" . $ipid . "'")
		    ->andWhere('isdelete = "0"')
		    ->andWhereNotIn('id',$del_visits['kvno_doctor_form'])
		    ->orderBy('start_date ASC');
		    $kvnodocarray = $kvnodoc->fetchArray();
		
		    foreach($kvnodocarray as $k=>$vdata){
		        $visits['kd'.$vdata['id']] = $vdata;
		    }
		
		
		    $bay_q = Doctrine_Query::create()
		    ->select("*,start_date as date")
		    ->from('BayernDoctorVisit')
		    ->where("ipid='" . $ipid . "'")
		    ->andWhereNotIn('id',$del_visits['bayern_doctorvisit'])
		    ->orderBy('start_date ASC');
		    $bay_arr = $bay_q->fetchArray();
		
		    foreach($bay_arr as $k=>$vdata){
		        $visits['bay'.$vdata['id']] = $vdata;
		    }
		
		    $cf_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('ContactForms')
		    ->where("ipid='" . $ipid . "'")
		    ->andWhere('isdelete = "0"')
		    ->andWhereNotIn('id',$del_visits['contact_form'])
		    ->orderBy('start_date ASC');
		    $cf_arr = $cf_q->fetchArray();
		
		    foreach($cf_arr as $k=>$vdata){
		        $visits['cf'.$vdata['id']] = $vdata;
		    }
		    foreach( $visits as $kv=>$values ){
		        $new[$values['date']] = $values['ecog'];
		    }
		    
		    
		    
		    if($first){
		        $ecog= reset($new);
		        $ecog_array['first'] = reset($new);  
		    } elseif ($last){
		        $ecog = end($new); 
		        $ecog_array['last'] = end($new);  
		    } else {
		        $ecog = false;
		    }
		    
		    return $ecog ;
		}
		
		
		public function findEcogValuesInPeriod($ipid = '',   $start_date = '', $end_date = '')
		{
		
		    if (empty($ipid) 
		        || empty($start_date) 
		        || empty($end_date) 
		    ) {
		        return;
		    }
		    $start_date = date('Y-m-d H:i:s', strtotime($start_date));
		    $end_date = date('Y-m-d H:i:s', strtotime($end_date));
		    
		    /* ----------------- Patient Details - Deleted visits ----------------------------------------- */
		    //you need deleted every fucking single time cause you don't have a triggers on PatientCourse... 
		    $deleted_visits_array = Doctrine_Query::create()
		    ->select("
		        recordid,
		        AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname
		        ")
		    ->from('PatientCourse')
		    ->where("ipid LIKE ?" , $ipid)
		    ->andWhere('wrong = 1')
		    ->andWhere('course_type = ? ', Pms_CommonData::aesEncrypt("F"))
		    ->andWhereIn("tabname", Pms_CommonData::aesEncryptMultiple(array(
		        'visit_koordination_form',
		        'kvno_doctor_form',
		        'kvno_nurse_form',
		        'wl_doctor_form',
		        'wl_nurse_form',
		        'bayern_doctorvisit',
		        'contact_form'
		    )))
		    ->fetchArray();
		    
		    $del_visits = array(
		        'kvno_doctor_form' => array(),
		        'bayern_doctorvisit' => array(),
		        'contact_form' => array()
		    );
		    		     
		    foreach ($deleted_visits_array as $row) {
		        $del_visits[$row['tabname']][] = $row['recordid'];
		    }
		
		
		    $kvnodoc = Doctrine_Query::create()
// 		    ->select("*, kvno_ecog as ecog, start_date as date")
		    ->select("kvno_ecog as ecog, start_date as date")
		    ->from('KvnoDoctor')
		    ->where("ipid = ? ", $ipid)
		    ->andWhere('isdelete = 0')
		    ->andWhere('start_date BETWEEN ? AND ? ', array($start_date, $end_date))
		    ->orderBy('start_date ASC');
		    
		    if ( ! empty($del_visits['kvno_doctor_form'])) {
		        $kvnodoc->andWhereNotIn('id', $del_visits['kvno_doctor_form']);
		    }
		    
		    $kvnodocarray = $kvnodoc->fetchArray();
		
		    $visits = array();
		    
		    foreach ($kvnodocarray as $vdata) {
		        $visits['kd'.$vdata['id']] = $vdata;
		    }
		
		
		    $bay_q = Doctrine_Query::create()
// 		    ->select("*, start_date as date")
		    ->select("ecog, start_date as date")
		    ->from('BayernDoctorVisit')
		    ->where("ipid = ?", $ipid)
		    ->andWhere('start_date BETWEEN ? AND ? ', array($start_date, $end_date))
		    ->orderBy('start_date ASC');
		    
		    if ( ! empty($del_visits['bayern_doctorvisit'])) {
		        $bay_q->andWhereNotIn('id', $del_visits['bayern_doctorvisit']);
		    }
		    
		    $bay_arr = $bay_q->fetchArray();
		
		    foreach ($bay_arr as $vdata) {
		        $visits['bay'.$vdata['id']] = $vdata;
		    }
		
		    
		    
		    $cf_q = Doctrine_Query::create()
// 		    ->select("*")
		    ->select("ecog, date")
		    ->from('ContactForms')
		    ->where("ipid = ?", $ipid)
		    ->andWhere('isdelete = "0"')
		    ->andWhere('start_date BETWEEN ? AND ? ', array($start_date, $end_date))
		    ->orderBy('start_date ASC');
		    
		    if ( ! empty($del_visits['contact_form'])) {
		        $cf_q->andWhereNotIn('id', $del_visits['contact_form']);
		    }
		    
		    $cf_arr = $cf_q->fetchArray();
		    
		    foreach ($cf_arr as $vdata) {
		        $visits['cf'.$vdata['id']] = $vdata;
		    }
		    
		    $result = array();
		    
		    foreach ($visits as $kv => $values) {
		        $result [ $values['date'] ] = $values['ecog'];
		    }
		     ksort($result);
		    
		    return $result;
		}
		
		
	
		public static function get_multiple_patients_details_customcolumns($ipids , $normal_columns = array("id"), $decrypt_columns = array("first_name"))
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$groupid = $logininfo->groupid;
			$hidemagic = Zend_Registry::get('hidemagic');
			
		
			if ( ! is_array($ipids) || count($ipids) == 0)
			{
				return false; //premature exit
			}
		
		
			$normal_columns[] = "id";
			$normal_columns[] = "ipid";
			
			$normal_columns = array_unique($normal_columns);
			$decrypt_columns = array_unique($decrypt_columns);
			
			foreach($normal_columns as $col){
				$sql [] = $col;	
			}
							
			if($logininfo->usertype == 'SA'){
				foreach($decrypt_columns as $col){
					$sql [] = "IF(isadminvisible = 1, AES_DECRYPT(".$col.",'" . Zend_Registry::get('salt') . "') , '" . $hidemagic . "') AS ".$col;
				}
			}else{
				foreach($decrypt_columns as $col){
					//$sql [] = $col;
					$sql [] = "AES_DECRYPT(".$col.", '" . Zend_Registry::get('salt') . "') AS ".$col;
				}
			}
			
			$sql = implode(" , " ,  $sql);
			
	
			$pt = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p INDEXBY ipid')
			->whereIn('ipid', $ipids);
			
			//ISPC-2045
			$pt->leftJoin("p.PatientContactphone pcp");
			$pt->addSelect("pcp.*");

			$patients_res = $pt->fetchArray();
		
			if($patients_res)
			{
				return $patients_res;
			}
			else
			{
				return false;
			}
		}
	
		/**
		 * 
		 * @param array $ipid_arr
		 * @return boolean|Ambigous <string, multitype:, Doctrine_Collection>
		 */
		public static function getPatientsNiceName(array $ipid_arr, $_hidemagic = true)
		{
			
			if (empty($ipid_arr)) {
				return false;
			}
			$ipid_arr = array_values($ipid_arr);
						
			$hidemagic = Zend_Registry::get('hidemagic');
			
			$salt = Zend_Registry::get('salt');
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$sql_decrypt = "";
						
			if($_hidemagic == true && $logininfo->usertype == 'SA')
			{
				$sql_decrypt .= "IF(isadminvisible = 1, CONVERT(AES_DECRYPT( pm.first_name, '".$salt."' ) using latin1), '".$hidemagic."' ) as first_name , ";
				$sql_decrypt .= "IF(isadminvisible = 1, CONVERT(AES_DECRYPT( pm.last_name, '".$salt."'  ) using latin1), '".$hidemagic."' ) as last_name , ";
				
			} else {
				$sql_decrypt .= "CONVERT(AES_DECRYPT(pm.first_name, '".$salt."'  ) using latin1) as first_name ," ;
				$sql_decrypt .= "CONVERT(AES_DECRYPT(pm.last_name, '".$salt."'  ) using latin1) as last_name," ;
			}
			
	
			$ipid_details_arr = Doctrine_Query::create()
			->select('pm.id,
					pm.ipid,' 
					. $sql_decrypt .
					'ep.epid'
			)
			->from('PatientMaster pm')
			->whereIn('pm.ipid', $ipid_arr)
			->leftJoin('pm.EpidIpidMapping ep')
			->andWhere('ep.ipid=pm.ipid')
			->fetchArray();
						
			self::beautifyName($ipid_details_arr);
			
			foreach ( $ipid_details_arr as $v ) {
				$ipid_details_arr[$v['ipid']] = $v;
			}
			
			return $ipid_details_arr;
		}
	
		public static function beautifyName( &$usrarray )
		{
			//mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
			if ( empty($usrarray) || ! is_array($usrarray)) {
				return;
			}
			foreach ( $usrarray as &$k ) 
			{			
				if ( ! is_array($k) || isset($k['nice_name'])) {
					continue; // varaible allready exists, use another name for the variable
				}
				
				
				$k ['nice_name']   = trim($k['last_name']);
				$k ['nice_name']  .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
				
				$k ['nice_name_epid']  = $k ['nice_name'] . " - " . (isset($k['epid']) ? strtoupper($k['epid']) : strtoupper($k ['EpidIpidMapping']['epid']));
				
				
				$k ['nice_address']  = trim($k['street1']) . ",";
				$k ['nice_address'] .= empty($k['zip']) ? "" : ' ' . trim($k['zip']);
				$k ['nice_address'] .= empty($k['city']) ? "" : ' ' . trim($k['city']);
				
				if( ! isset($k['epid'])){
					$k ['epid'] = strtoupper($k['EpidIpidMapping']['epid']);
				}
				
			}
		}
		
		
		
		/**
		 * @claudiu update 2017.12: $search_string must be preg_quoted by you !
		 * 
		 * function was introduced in ISPC-1977 for the vw datatable column search after patient
		 * @param string $search_string = pattern for regex
		 * @param number $clientid
		 * @return array <multitype:, Doctrine_Collection>
		 * 
		 * use as is, or extend the return
		 * @claudiu update 2018.03 @param $include_isdelete was added so you can ignore p.isstandby = 0 AND p.isdischarged = 0 AND p.isdelete = 0 AND p.isstandbydelete = 0
		 * @param $include_isdelete missleading name
		 */
		public static function search_patients( $search_string = "", $filters = array(
		    'clientid'        => 0, 
		    'isstandby'       => 0 , 
		    'isdischarged'    => 0 , 
		    'isstandbydelete' => 0))
		{		    
				
		    $clientid = (int)$filters['clientid'];
		    
		    if (empty($clientid)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		        $userid = $logininfo->userid;
		    }
		    
			if (trim($search_string) == "" || empty($clientid)) {
				return;
			}
			
			
			$not_regex = "";
			$or_and =  ' OR ';
			
			if (substr($search_string, 0, 3) == "!=^") {
				$search_string = substr($search_string, 2, strlen($search_string));
			 	$not_regex = " NOT ";
			 	$or_and = " AND ";
			}
			
			
			$search_string = trim($search_string);
			Pms_CommonData::value_patternation($search_string, false, false, true);
				
// 			$regexp = mb_strtolower($search_string, 'UTF-8');
			//@claudiu 12.2017, changed Pms_CommonData::value_patternation
			$regexp = $search_string;
			// 			$regexp = mb_convert_case(mb_strtolower($search_string, 'UTF-8'), MB_CASE_TITLE, "UTF-8");

			$salt = Zend_Registry::get('salt');
			
			$include_isstandby_isdischarged_isstandbydelete_sql = '';
			
			if ( ! isset($filters['isstandby'])) {
			    $include_isstandby_isdischarged_isstandbydelete_sql .=  ' AND p.isstandby = 0 ';
			} elseif ( is_numeric($filters['isstandby'])) {
			    $include_isstandby_isdischarged_isstandbydelete_sql .=  ' AND p.isstandby = '. (int)$filters['isstandby'] . ' ';
			}
			
			if ( ! isset($filters['isdischarged'])) {
			    $include_isstandby_isdischarged_isstandbydelete_sql .=  ' AND p.isdischarged = 0 ';
			} elseif ( is_numeric($filters['isdischarged'])) {
			    $include_isstandby_isdischarged_isstandbydelete_sql .=  ' AND p.isdischarged = '. (int)$filters['isdischarged'] . ' ';
			}
			
			if ( ! isset($filters['isstandbydelete'])) {
			    $include_isstandby_isdischarged_isstandbydelete_sql .=  ' AND p.isstandbydelete = 0 ';
		    } elseif ( is_numeric($filters['isstandbydelete'])) {
		        $include_isstandby_isdischarged_isstandbydelete_sql .=  ' AND p.isstandbydelete = '. (int)$filters['isstandbydelete'] . ' ';
		    }
			
			
			$query = Doctrine_Query::create()
			->select("p.ipid,
    				e.epid,
    				AES_DECRYPT(p.first_name, '{$salt}') as first_name,
    				AES_DECRYPT(p.last_name, '{$salt}') as last_name
    				")
			->from('EpidIpidMapping e')
			->leftJoin("e.PatientMaster p ON (e.ipid = p.ipid AND p.isdelete = 0 {$include_isstandby_isdischarged_isstandbydelete_sql})")
// 			->leftJoin('e.PatientMaster p')
// 			->where('e.ipid = p.ipid')
			
// 			->andWhere('e.clientid = ? ', $clientid )
// 			->andWhere('p.isstandby = "0"')
// 			->andWhere('p.isdischarged = "0"')
// 			->andWhere('p.isdelete = "0"')
// 			->andWhere('p.isstandbydelete = "0"')
			
			->where("e.clientid = ?", $clientid)
			
			
			->andWhere("(e.epid {$not_regex} REGEXP ? 
							{$or_and} (CONVERT(AES_DECRYPT(p.last_name, '{$salt}')  USING utf8) {$not_regex} REGEXP ? )
							{$or_and} (CONVERT(AES_DECRYPT(p.first_name, '{$salt}')  USING utf8)  {$not_regex} REGEXP ? )
							{$or_and} (CONVERT(CONCAT_WS(' ', AES_DECRYPT(p.last_name, '{$salt}'), AES_DECRYPT(p.first_name, '{$salt}')) USING utf8)  {$not_regex} REGEXP ?)

						)", 
						array($regexp, $regexp, $regexp, $regexp))		
			;			
			
			if ( ! empty($filters) && is_array($filters)) {
			    
			    foreach ($filters  as $row) {
			        
			        if ( ! is_array($row)) {
			            continue;
			        }
			        
			
			        if ( ! empty($row['where']) && is_string($row['where'])) {
			
			            $query->andWhere($row['where'], $row['params']); // i used only string
			
			        }
			
			        elseif ( ! empty($row['whereIn']) && is_array($row['params'])) {
			
			            $query->andWhereIn($row['whereIn'], array_values(array_unique($row['params'])));
			
			        }
			
			        elseif ( ! empty($row['whereNotIn']) && is_array($row['params'])) {
			
			            $query->andWhereNotIn($row['whereIn'], array_values(array_unique($row['params'])));
			
			        }
			
			        elseif ( ! empty($row['limit'])) {
			
			            $query->limit($row['limit']); //please sanitize in your script
			        }
			
			        elseif ( ! empty($row['offset'])) {
			
			            $query->offset($row['offset']); //please sanitize in your script
			        }
			
			        elseif ( ! empty($row['orderBy'])) {
			            $customer_orderBy =  true;
			            $query->orderBy($row['orderBy']);//please sanitize in your script
			        }
			
			    }
			}
			
			$patietns_act_search_q = $query->fetchArray();
			
			return $patietns_act_search_q;
	
		
		}
		
		
		
		
		

		public function get_patient_standby_admissions($ipid)
		{
			$q = Doctrine_Query::create()
			->select('*')
			->from('PatientStandbyDetails')
			->where('ipid LIKE "' . $ipid . '"')
			->orderBy('date ASC');
			$q_res = $q->fetchArray();
		
			//"new" patient - data in readmission
			if(!$q_res)
			{
				$del_old_active_data = Doctrine_Query::create()
				->delete('PatientStandby pa')
				->where('pa.ipid LIKE "' . $ipid . '"');
				$del_old_active_data->execute();
			}
			else
			{
				$incr = '0';
				foreach($q_res as $k_patient_date => $v_patient_date)
				{
					//date_type switcher
					if($v_patient_date['date_type'] == '1')
					{
						$type = 'start';
					}
					else
					{
						$type = 'end';
					}
		
					$patient_admission[$incr][$type] = $v_patient_date['date'];
		
					//check next item (which is supposed to be discharge) exists
					if($v_patient_date['date_type'] == '1' && !array_key_exists(($k_patient_date + 1), $q_res))
					{
						$patient_admission[$incr]['end'] = '';
					}
		
					//increment when reaching end dates(date_type=2)
					if($v_patient_date['date_type'] == '2')
					{
						$incr++;
					}
				}
			}
 
		
		
			if(count($patient_admission) > '0')
			{
				$del_old_active_data = Doctrine_Query::create()
				->delete('PatientStandby pa')
				->where('pa.ipid LIKE "' . $ipid . '"');
				$del_old_active_data->execute();
		
				foreach($patient_admission as $k_adm_cycle => $v_cycle_data)
				{
					if(strlen($v_cycle_data['end']) == '0')
					{
						$end_date = '0000-00-00';
					}
					else
					{
						$end_date = date('Y-m-d', strtotime($v_cycle_data['end']));
					}
		
					$cycle_records[] = array(
							'ipid' => $ipid,
							'start' => date('Y-m-d', strtotime($v_cycle_data['start'])),
							'end' => $end_date
					);
				}
		
				$collection = new Doctrine_Collection('PatientStandby');
				$collection->fromArray($cycle_records);
				$collection->save();
			}
			unset($cycle_records);
			unset($patient_admission);
			unset($q_res);
			unset($q_pm_res);
			unset($q_pd_res);
			unset($del_old_active_data);
		
			//		return $patient_admission;
		}
		
		public function get_patient_standbydelete_admissions($ipid)
		{
			$q = Doctrine_Query::create()
			->select('*')
			->from('PatientStandbyDeleteDetails')
			->where('ipid LIKE "' . $ipid . '"')
			->orderBy('date ASC');
			$q_res = $q->fetchArray();
		
			//"new" patient - data in readmission
			if(!$q_res)
			{
				$del_old_active_data = Doctrine_Query::create()
				->delete('PatientStandbyDelete pa')
				->where('pa.ipid LIKE "' . $ipid . '"');
				$del_old_active_data->execute();
			}
			else
			{
				$incr = '0';
				foreach($q_res as $k_patient_date => $v_patient_date)
				{
					//date_type switcher
					if($v_patient_date['date_type'] == '1')
					{
						$type = 'start';
					}
					else
					{
						$type = 'end';
					}
		
					$patient_admission[$incr][$type] = $v_patient_date['date'];
		
					//check next item (which is supposed to be discharge) exists
					if($v_patient_date['date_type'] == '1' && !array_key_exists(($k_patient_date + 1), $q_res))
					{
						$patient_admission[$incr]['end'] = '';
					}
		
					//increment when reaching end dates(date_type=2)
					if($v_patient_date['date_type'] == '2')
					{
						$incr++;
					}
				}
			}
 
		
		
			if(count($patient_admission) > '0')
			{
				$del_old_active_data = Doctrine_Query::create()
				->delete('PatientStandbyDelete pa')
				->where('pa.ipid LIKE "' . $ipid . '"');
				$del_old_active_data->execute();
		
				foreach($patient_admission as $k_adm_cycle => $v_cycle_data)
				{
					if(strlen($v_cycle_data['end']) == '0')
					{
						$end_date = '0000-00-00';
					}
					else
					{
						$end_date = date('Y-m-d', strtotime($v_cycle_data['end']));
					}
		
					$cycle_records[] = array(
							'ipid' => $ipid,
							'start' => date('Y-m-d', strtotime($v_cycle_data['start'])),
							'end' => $end_date
					);
				}
		
				$collection = new Doctrine_Collection('PatientStandbyDelete');
				$collection->fromArray($cycle_records);
				$collection->save();
			}
			unset($cycle_records);
			unset($patient_admission);
			unset($q_res);
			unset($q_pm_res);
			unset($q_pd_res);
			unset($del_old_active_data);
		
			//		return $patient_admission;
		}
		
		/* 
		 * 
		 * Patient falls used in patient details and in Ajax - for edit and delete  falls
		 * 
		 *  
		 */
		
		public function patient_falls($ipid){
			$patientdetails = $this->getMasterData(NULL, 0, NULL, $ipid);
			
			
			$first_admission = date('Y-m-d H:i:s', strtotime($patientdetails['admission_date']));
			$lastdischarge = PatientDischarge::getPatientLastDischarge($ipid);
			
				
			/*------------------------- STANDBY DETAILS ------------------------------------      */
			$standby_details_m = new PatientStandbyDetails();
			$standbyarray_bydate = $standby_details_m->get_all_standby_details($ipid);
			$standbyarray = $standby_details_m->get_patient_standby_details_all_sorted($ipid);

			$not_continuu = 0;
			if(!empty($standbyarray_bydate))
			{
					
				foreach($standbyarray_bydate as $ak=>$adates){
					if($adates['date_type'] == "1" && $adates['date_type'] == $standbyarray_bydate[$ak+1]['date_type']){
						$not_continuu += 1;
					}
						
					if($adates['date_type'] == "2" && $adates['date_type'] == $standbyarray_bydate[$ak+1]['date_type']){
						$not_continuu += 1;
					}
				}
			}
			
				
			if(!empty($standbyarray))
			{
				$fall_st = 1;
			
				if(!$fall_ov){
					$fall_ov = 1;
				} else{
					$fall_ov = $fall_ov;
				}
					
				foreach($standbyarray as $adm_item)
				{
					if($adm_item['date_type'] == "1"){
						$st_admissions[] = $adm_item['date'];
					} else{
						$st_discharges[] = $adm_item['date'];
					}
						
					$patient_falls_st[$fall_st][$adm_item['date_type']] = $adm_item['date'];
					if(count($patient_falls_st[$fall_st]) == "2"){
						$fall_st++;
					}
						
					$patient_falls_overall[$fall_ov][$adm_item['date_type']] = $adm_item['date'];
					$patient_falls_overall[$fall_ov][0] = 'standby';
					if(count($patient_falls_overall[$fall_ov]) == "3"){
						$fall_ov++;
					}
			
					$patient_history_st[$adm_item['date']] = $adm_item['date_type'];
					$standby_date_array[] = date("d.m.Y",strtotime($adm_item['date']));
				}
					
				$first_admission_ever = $st_admissions[0];
					
			}
			else
			{
				if($patientdetails['isstandby'] == "1"){
					
					if(!$fall_ov){
						$fall_ov = 1;
					} else{
						$fall_ov = $fall_ov;
					}
						
					$patient_falls_overall[$fall_ov][1] = $first_admission;
					$patient_falls_overall[$fall_ov][0] = 'standby';
					$standby_date_array[] = date("d.m.Y",strtotime($first_admission));
				}
			}
			
			
			if($_REQUEST['pfalls'] == "1")
			{
				print_R("\n");
				print_R('standby');
				print_R($patient_falls_overall);
				print_R("\n");
			}
			/*------------------------- STANDBYDELETE DETAILS ------------------------------------      */
		/* 	$standbydelete_details = new PatientStandbyDeleteDetails();
			$standbyarray_del_bydate = $standbydelete_details->get_all_standby_details($ipid);
			$standbyarray_del = $standbydelete_details->get_patient_standby_details_all_sorted($ipid);
			
			if(!empty($standbyarray_del))
			{
				$fall_std = 1;
					
				if(!$fall_ov){
					$fall_ov = 1;
				} else{
					$fall_ov = $fall_ov+100;
				}
				
				$std_admissions = array();
				$std_discharges = array();
				foreach($standbyarray_del as $adm_item)
				{
					if($adm_item['date_type'] == "1"){
						$std_admissions[] = $adm_item['date'];
					} else{
						$std_discharges[] = $adm_item['date'];
					}
						
					$patient_falls_st[$fall_std][$adm_item['date_type']] = $adm_item['date'];
					if(count($patient_falls_st[$fall_std]) == "2"){
						$fall_std++;
					}
						
					$patient_falls_overall[$fall_ov][$adm_item['date_type']] = $adm_item['date'];
					$patient_falls_overall[$fall_ov][0] = 'standbydelete';
					if(count($patient_falls_overall[$fall_ov]) == "3"){
						$fall_ov++;
					}
			
					$patient_history_st[$adm_item['date']] = $adm_item['date_type'];
					$standbydelete_date_array[] = date("d.m.Y",strtotime($adm_item['date']));
				}
					
				$first_admission_ever = $std_admissions[0];
					
			}
			else
			{
				if($patientdetails['isstandbydelete'] == "1"){
					
					if(!$fall_ov){
						$fall_ov = 1;
					} else{
						$fall_ov = $fall_ov+100;
					}
			
					$patient_falls_overall[$fall_ov][1] = $first_admission;
					$patient_falls_overall[$fall_ov][0] = 'standbydelete';
					$standbydelete_date_array[] = date("d.m.Y",strtotime($first_admission));
				}
			}
				
				
			if($_REQUEST['pfalls'] == "1")
			{
				print_R("\n");
				print_R('standbydelete');
				print_r($patient_falls_overall);// exit;
				print_r('before readmission');// exit;
			}
				 */
			/*------------------------- READMISSION DETAILS -----------------------------------      */
			$readmission_dates = new PatientReadmission();
			$admisiondatesarray_bydate = $readmission_dates->getPatientReadmissionAll($ipid);
			$admisiondatesarray = $readmission_dates->get_patient_readmission_all($ipid);
				
				
			if(!empty($admisiondatesarray_bydate))
			{
				 
				foreach($admisiondatesarray_bydate as $ak=>$adates){
					if($adates['date_type'] == "1" && $adates['date_type'] == $admisiondatesarray_bydate[$ak+1]['date_type']){
						$not_continuu += 1;
					}
			
					if($adates['date_type'] == "2" && $adates['date_type'] == $admisiondatesarray_bydate[$ak+1]['date_type']){
						$not_continuu += 1;
					}
				}
			}
	
			

			if($_REQUEST['pfalls'] == "1")
			{
				print_r("\n");
			
				print_r($admisiondatesarray);
				print_r("\n");
			}
				
			
			if(!empty($admisiondatesarray))
			{
				$fall = 1;
				 
				if(!$fall_ov){
					$fall_ov = 1;
				} else{
					$fall_ov = $fall_ov+1000;
			
				}
				 
				foreach($admisiondatesarray as $adm_item)
				{
					if($adm_item['date_type'] == "1"){
						$admissions[] = $adm_item['date'];
					} else{
						$discharges[] = $adm_item['date'];
					}
			
					$patient_falls[$fall][$adm_item['date_type']] = $adm_item['date'];
					if(count($patient_falls[$fall]) == "2"){
						$fall++;
					}
						
					if($_REQUEST['pfalls'] == "1")
					{
						print_R("\n");
						/* print_R($adm_item['date']);
						print_R("\n");
						var_dump(in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array));
						var_dump(in_array(date('d.m.Y',strtotime($adm_item['date'])), $standbydelete_date_array));
						var_dump($adm_item['date'] != $admissions[0]);
						var_dump($patientdetails['isstandbydelete'] == '1');
						var_dump( in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array) && ( $patientdetails['isstandbydelete'] == '1') );
						
						
						var_dump( 
								
								in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array) || in_array(date('d.m.Y',strtotime($adm_item['date'])), $standbydelete_date_array) 

								&& ( $patientdetails['isstandbydelete'] == '1') );
						 */
						
						print_R("\n");
						print_R("first: ");
						print_R($admissions[0]);
						print_R("\n");
						print_R("last: ");
						print_R($first_admission);
						print_R("\n");
						
						
						
						
					}
					// daca admisia este in inceputul de standby, - nu o adaugam la active ??? 
					

// 					if(in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array) && ( ($adm_item['date'] == $admissions[0]  ||  in_array($adm_item['date'],$st_admissions) ) && ($patientdetails['isstandby'] == '1') || $patientdetails['isstandbydelete'] == '1')){
			
// 					} 
// 					else
// 					{
			
						$patient_falls_overall[$fall_ov][$adm_item['date_type']] = $adm_item['date'];
						
						// in_array($adm_item['date'], $admissions) > added for TODO-1763  ISPC: Fallhistorie can not correct insert (22.08.2018 Ancuta)
						// removed && $patientdetails['isstandby']!= '0'
						// if(in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array) && ($adm_item['date'] != $first_admission && $patientdetails['isstandby']!= '0')){
						if( in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array) 
						    && ($adm_item['date'] != $first_admission)
						    && in_array($adm_item['date'], $admissions) 
						    ){
							$patient_falls_overall[$fall_ov][0] = 'standby';
						} else{
			
							if($adm_item['date'] == $first_admission && $patientdetails['isstandby'] == '1'){
								$patient_falls_overall[$fall_ov][0] = 'standby';
							}
							elseif($adm_item['date'] == $first_admission && $patientdetails['isstandbydelete'] == '1'){
								$patient_falls_overall[$fall_ov][0] = 'standby';
							} else{
								$patient_falls_overall[$fall_ov][0] = 'active';
				    	
							}
						}
						if(count($patient_falls_overall[$fall_ov]) == "3"){
							$fall_ov++;
						}
			
						$patient_history[$adm_item['date']] = $adm_item['date_type'];
						$date_array[] = $adm_item['date'];
// 					}
				}
				 
				$first_admission_ever = $admissions[0];
				 
			}
			else
			{
				if(!$fall_ov){
					$fall_ov = 1;
				} else{
					$fall_ov = $fall_ov+1000;
				}
			
				$patient_falls[1][1] = $first_admission;
				if(in_array(date('d.m.Y',strtotime($adm_item['date'])), $standby_date_array) && ( ($adm_item['date'] == $admissions[0]  ||  in_array($adm_item['date'],$st_admissions) ) && ($patientdetails['isstandby'] == '1') || $patientdetails['isstandbydelete'] == '1')){
						
				} else {
					
					$patient_falls_overall[$fall_ov][1] = $first_admission;
					 
					 
					if($patientdetails['isstandby'] == '1'){
						$patient_falls_overall[$fall_ov][0] = 'standby';
					}
					elseif($patientdetails['isstandbydelete'] == '1'){
						$patient_falls_overall[$fall_ov][0] = 'standby';
					} else{
						$patient_falls_overall[$fall_ov][0] = 'active';
						 
					}
					 
					$first_admission_ever = $first_admission;
					if($lastdischarge){
						$patient_falls[1][2] = $lastdischarge[0]['discharge_date'];
						$patient_falls_overall[$fall_ov][2] = $lastdischarge[0]['discharge_date'];
					}
					$date_array[] = $first_admission;
				}
			}
			if($_REQUEST['pfalls'] == "1")
			{
				print_r("admmm");
				print_r($patient_falls);
				
				
				print_r($patient_falls_overall);
			}
			
				
			foreach($patient_falls_overall as $per_id=>$per_arr){
				ksort($per_arr);
				$patient_falls_overall_final[$per_arr[1]] =  $per_arr;
			}
			ksort($patient_falls_overall_final);
			
			
			if($_REQUEST['pfalls'] == "1")
			{
				print_r("\n  ");
				print_r("date as key ");
				print_r($patient_falls_overall_final);
				print_r("\n  ");
			}
			
			foreach($patient_falls_overall_final as $sort_date => $period_fall_data){
				$patient_falls_final[] = $period_fall_data;
			}
			
			if($_REQUEST['pfalls'] == "1")
			{
				print_r("before discharge ");
				print_r($patient_falls_final);
			}	
			$d = 0 ;
			foreach($patient_falls_final as $fal_nr=>$fall_dates)
			{
			
				if($patient_falls_final[$fal_nr+1][1] &&  strtotime(date('d.m.Y',strtotime($patient_falls_final[$fal_nr][2]))) < strtotime(date('d.m.Y',strtotime($patient_falls_final[$fal_nr+1][1]))))
				{
						
					$discharge_arr[$d][0] = "discharge";
					$discharge_arr[$d][1] = $patient_falls_final[$fal_nr][2];
					$discharge_arr[$d][2] = $patient_falls_final[$fal_nr+1][1];
						
				}
			
				if(count($discharge_arr[$d]) == "3"){
					$d++;
				}
			}
				
				
			foreach($discharge_arr as $per_id=>$per_arr){
				ksort($per_arr);
				$patientd_falls_overall_final[$per_arr[1]] =  $per_arr;
			}
				
				
			if(!empty($patientd_falls_overall_final)){
				$patient_falls_overall_final = array_merge($patient_falls_overall_final, $patientd_falls_overall_final);
			}
			
			
			ksort($patient_falls_overall_final);
			$k=1;
			foreach($patient_falls_overall_final as $sort_date => $period_fall_data){
				$patient_falls_overall_final_sorted[$k] = $period_fall_data;
				$k++;
			}
			
			if($_REQUEST['pfalls'] == "1")
			{
				print_r('including discharge');
				print_r($patient_falls_overall_final_sorted);
				print_r($ipid);
				exit;
			}
				
				
			$patient_falls_final = $patient_falls_overall_final_sorted;
			
			
			$patient_final['not_continuu']  = $not_continuu ;
			$patient_final['first_admission_ever'] = $first_admission_ever; 
			$patient_final['falls'] = $patient_falls_final; 
			
// 			print_r($patient_final); exit;
			
			return $patient_final;
			
			
		}
		
		
		/**
		 * $filters_discharge['last x days']
		 * $filters_discharge['x days ago']
		 * 
		 * Aug 9, 2017 @claudiu 
		 * 
		 * @param number $clientid
		 * @param unknown $filters
		 * @return multitype:
		 */
		public function get_discharged_ipids( $clientid = 0, $filters_discharge = null)
		{
					
			$q = $this->getTable()->createQuery('pm')  
// 				->select("pm.*, pd.*, eim.*")
				->select("pm.ipid")
				->Where('isdelete = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere('isdischarged = 1')
				->andWhere('isarchived = 0')
				->leftJoin("pm.EpidIpidMapping eim")
				->andWhere('eim.clientid = ?', $clientid)
				->leftJoin("pm.PatientDischarge pd");
			
			if( ! empty($filters_discharge['x days ago'])) {
				$q->andWhere('DATE(pd.discharge_date) = (CURRENT_DATE() - INTERVAL ? DAY)' , (int)$filters_discharge['x days ago'] );
			}
			
			if( ! empty($filters_discharge['last x days'])) {
				$q->andWhere("DATE(pd.discharge_date) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY) AND CURRENT_DATE() ",  (int)$filters_discharge['last x days']);
			}
			
            //ISPC-2391.Elena,11.02.2021
			if( ! empty($filters_discharge['for year'])) {
				$q->andWhere("YEAR(pd.discharge_date)=?",  (int)$filters_discharge['for year']);
			}


			$ipid_array = $q->fetchArray();
			
			return $ipid_array;	
			
		}
		
		/**
		 * $filters_survey
		 * // Maria:: Migration ISPC to CISPC 08.08.2020
		 * Aug 22, 2019 @carmen
		 *
		 * @param number $clientid
		 * @param unknown $filters
		 * @return multitype:
		 */
		public function get_survey_ipids( $clientid = 0)
		{
				
			$q = $this->getTable()->createQuery('pm indexBy ipid')
			->select("pm.ipid, ips.*")
			->Where('isdelete = 0')
			/* ->andWhere('isstandbydelete = 0')
			->andWhere('isdischarged = 0')
			->andWhere('isarchived = 0') */
			->leftJoin("pm.EpidIpidMapping eim")
			->andWhere('eim.clientid = ?', $clientid)
			->leftJoin("pm.PatientSurveySettings ips")
			->andWhere('pm.ipid = ips.ipid')
			->andWhere('ips.status = "enabled"');
			
			$ipid_array = $q->fetchArray();
				
			return $ipid_array;
				
		}

    /**
     *
     * @author Ancuta
     *         12.09.2019
     * @param number $clientid            
     * @return Ambigous <multitype:, Doctrine_Collection>
     */
    public function get_today_surveys_patients($clientid = 0)
    {
        if(empty($clientid)){
            return;
        }
        
        $date = date("Y-m-d");
        $sqlHaving = "`sendnow` = 0";
        
        $q = $this->getTable()
            ->createQuery('pm indexBy ipid')
            ->select('pm.ipid,                       
                        AES_DECRYPT(pm.first_name,"' . Zend_Registry::get("salt") . '") as first_name,
                        AES_DECRYPT(pm.last_name,"' . Zend_Registry::get("salt") . '") as last_name,pm.birthd,
			    ips.*,(	DATEDIFF( "' . $date . '"  , ips.start_date ) % ips.interval_days) AS `sendnow`,ips.id AS patient_id')
            ->Where('isdelete = 0')
            ->andWhere('isdischarged = 0')//TODO-3275 Ancuta 09.07.2020
            ->leftJoin("pm.EpidIpidMapping eim")
            ->andWhere('eim.clientid = ?', $clientid)
            ->leftJoin("pm.PatientSurveySettings ips")
            ->andWhere('pm.ipid = ips.ipid')
            ->andWhere('ips.status = "enabled"')
            ->andWhere('ips.table_id != "0"')
            ->having($sqlHaving);
        
        $ipid_array = $q->fetchArray();
        
        return $ipid_array;
    }
    
    
    public function get_today_surveys2patient($clientid = 0,$ipid=0)
    {
        
        if(empty($ipid) || empty($clientid)){
            return;
        }
        
        $date = date("Y-m-d");
        $sqlHaving = "`sendnow` = 0";
        
        $q = $this->getTable()
            ->createQuery('pm indexBy ipid')
            ->select('pm.ipid,                       
                        AES_DECRYPT(pm.first_name,"' . Zend_Registry::get("salt") . '") as first_name,
                        AES_DECRYPT(pm.last_name,"' . Zend_Registry::get("salt") . '") as last_name,pm.birthd,
			    ips.*,(	DATEDIFF( "' . $date . '"  , ips.start_date ) % ips.interval_days) AS `sendnow`,ips.id AS patient_id')
            ->Where('isdelete = 0')
            ->leftJoin("pm.EpidIpidMapping eim")
            ->andWhere('eim.clientid = ?', $clientid)
            ->leftJoin("pm.PatientSurveySettings ips")
            ->andWhere('pm.ipid = ips.ipid')
            ->andWhere('pm.ipid = "'.$ipid.'" ')
            ->andWhere('ips.status = "enabled"')
            ->andWhere('ips.table_id != "0"')
            ->having($sqlHaving);
        $ipid_array = $q->fetchArray();
        
        return $ipid_array;
    }
	

    /**
     * @claudiu
     * @param string $fieldName
     * @param unknown $value
     * @param array $data
     * @param unknown $hydrationMode
     * @return Doctrine_Record
     * 
     * (non-PHPdoc)
	 * @see Pms_Doctrine_Record::findOrCreateOneBy()
     * @deprecated
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
    
    /**
     * 
     * @param unknown $data
     * @deprecated
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
    
    /**
     * extra should be called after getMasterData()
     * 
     * @param string $ipid
     */
    public function getMasterData_extradata($ipid = null , $onlyThisModel = null)
    {
        if (empty($ipid)) {
            return;
        }
        
        $main = $this->get_patientMasterData();

        if( ! empty($main) && $main['ipid'] != $ipid) {
            
            return;//fail-safe
            
        } elseif ( ! empty($main['ipid'])) {
            
            $this->ipid = $main['ipid'];
            
        } else {
            $this->ipid = $ipid;            
        }
        
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientOrientation") && $this->get_patientMasterData('PatientOrientation') === null) {
            $entity = new PatientOrientation();
            $saved = $entity->findByIpid($this->ipid);
            $this->set_patientMasterData($saved, 'PatientOrientation');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientMobility2") && $this->get_patientMasterData('PatientMobility2') === null) {
            $entity = new PatientMobility2();
            $saved = $entity->findByIpid($this->ipid);
            $this->set_patientMasterData($saved, 'PatientMobility2');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientReligions") && $this->get_patientMasterData('PatientReligions') === null) {
            $entity = new PatientReligions();
            $saved = $entity->getTable()->findOneBy('ipid', $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
            $this->set_patientMasterData([$saved], 'PatientReligions');
        }
         
        

        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientMoreInfo") && $this->get_patientMasterData('PatientMoreInfo') === null) {
            $entity = new PatientMoreInfo();
            $saved = $entity->getpatientMoreInfoData($this->ipid);
            $this->set_patientMasterData(isset($saved[0]) ? $saved[0] : null, 'PatientMoreInfo');
        }
        
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "Stammdatenerweitert") && $this->get_patientMasterData('Stammdatenerweitert') === null) {
            $entity = new Stammdatenerweitert();
            $saved = $entity->getStammdatenerweitert($this->ipid);
            
            $saved = ! empty($saved) ? $saved : [];
            
            $this->set_patientMasterData(isset($saved[0]) ? $saved[0] : null, 'Stammdatenerweitert');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientSupply") && $this->get_patientMasterData('PatientSupply') === null) {
            $entity = new PatientSupply();
            $saved = $entity->getpatientSupplyData($this->ipid);
            $this->set_patientMasterData($saved, 'PatientSupply');
        }
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientMobility") && $this->get_patientMasterData('PatientMobility') === null) {
            $entity = new PatientMobility();
            $saved = $entity->getpatientMobilityData($this->ipid);
            $this->set_patientMasterData($saved, 'PatientMobility');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientLives") && $this->get_patientMasterData('PatientLives') === null) {
            $entity = new PatientLives();
            $saved = $entity->getpatientLivesData($this->ipid);
            $this->set_patientMasterData($saved, 'PatientLives');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientGermination") && $this->get_patientMasterData('PatientGermination') === null) {
            $entity = new PatientGermination();
            $saved = $entity->getPatientGermination($this->ipid);
            $saved = isset($saved[$this->ipid]) ? [$saved[$this->ipid]] : [];
            $this->set_patientMasterData($saved, 'PatientGermination');
        }
 //ISPC-2400
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientCrisisHistory") && $this->get_patientMasterData('PatientCrisisHistory') === null) {
            $entity = new PatientCrisisHistory();
            $saved = $entity->getPatientCrisisHistory($this->ipid);    
            $this->set_patientMasterData($saved, 'PatientCrisisHistory');
        }
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientMaintainanceStage") && $this->get_patientMasterData('PatientMaintainanceStage') === null) {
            $entity = new PatientMaintainanceStage();
            $saved = $entity->getpatientMaintainanceStage($this->ipid);
             
            array_walk($saved, function(&$item) {
                 
                $item['__stage'] = "PG" . $item['stage'];
                 
                if ($item['erstantrag'] == 0) {
                    $item['erstantrag'] = null;
                    $item['e_fromdate'] = null;
                }
                 
                if ($item['horherstufung'] == 0) {
                    $item['horherstufung'] = null;
                    $item['h_fromdate'] = null;
                }
            });
                 
                 
            $this->set_patientMasterData($saved, 'PatientMaintainanceStage');
        }

        
        //todo
//         if ($this->get_patientMasterData('ContactPersonMaster') === null){
//             $this->set_patientMasterData($saved, 'ContactPersonMaster');
//         }
    
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientAcp") && $this->get_patientMasterData('PatientAcp') === null) {
            
            $cnt_persons = $this->get_patientMasterData('ContactPersonMaster');
            
            $contact_persons_arr =  [];
            foreach ($cnt_persons as $row) {
                $contact_persons_arr[$row['id']] = $row['nice_name'];
            }
                         
            $entity = new PatientAcp();
            $saved = $entity->getByIpid($this->ipid);
            $saved_PatientAcp = [ 'contact_persons_arr' => $contact_persons_arr];
            foreach($saved[$this->ipid] as $row) {
                //ISPC-2565,Elena,26.02.2021
                if($row['division_tab'] == 'healthcare_proxy' || $row['division_tab'] == 'care_orders'){
                    if(($row['active'] == 'yes') && (!empty($row['contactperson_master_id']))) {
                        $saved_PatientAcp[$row['division_tab']]['contacts'][$row['contactperson_master_id']] = $row;
                        //for older records
                        $saved_PatientAcp[$row['division_tab']]['files'] = $row['files'];
                    }elseif($row['contactperson_master_id'] == 0){
                        $saved_PatientAcp[$row['division_tab']]['active'] = $row['active'];
                        $saved_PatientAcp[$row['division_tab']]['comments'] = $row['comments'];
                        $saved_PatientAcp[$row['division_tab']]['files'] = $row['files'];

                    }
                }else{
                    if(!empty($row['division_tab']) /*&& (!empty($row['contactperson_master_id']))*/){
                $saved_PatientAcp[$row['division_tab']] =  $row;
            }
            
                }

            }

            
            //ISPC - 2129
            $emplf = new PatientFileUpload();
            $emergencyfiles = $emplf->get_emergency_files($this->ipid);
            
            foreach($emergencyfiles as $k=>$row) {            	
            	$saved_PatientAcp['emergencyplan']['files'][$k]['id'] =  $row['PatientFileUpload']['id'];
            	$saved_PatientAcp['emergencyplan']['files'][$k]['title'] =  $row['PatientFileUpload']['title'];
            	$saved_PatientAcp['emergencyplan']['files'][$k]['file_name'] =  $row['PatientFileUpload']['file_name'];
            	$saved_PatientAcp['emergencyplan']['files'][$k]['file_type'] =  $row['PatientFileUpload']['file_type'];
            	$saved_PatientAcp['emergencyplan']['files'][$k]['file_date'] =  ($row['PatientFileUpload']['file_date'] ? $row['PatientFileUpload']['file_date'] : $row['PatientFileUpload']['create_date']) ;
            	$saved_PatientAcp['emergencyplan']['files'][$k]['active_version'] =  $row['PatientFileUpload']['PatientFileVersion']['active_version'];
            }
            
            foreach ($saved_PatientAcp['emergencyplan']['files'] as $key => $row) {
            	$actv[$key]  = $row['active_version'];
            	$fdatev[$key] = strtotime($row['file_date']);
            }
            
            array_multisort($actv, SORT_DESC, $fdatev, SORT_ASC, $saved_PatientAcp['emergencyplan']['files']);
            //ISPC - 2129
            
            $this->set_patientMasterData($saved_PatientAcp, 'PatientAcp');
        }
         
         
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientTherapieplanung") && $this->get_patientMasterData('PatientTherapieplanung') === null) {
            $entity = new PatientTherapieplanung();
            $saved = $entity->getTherapieplanungData($this->ipid);
            $this->set_patientMasterData($saved, 'PatientTherapieplanung');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientHospizverein") && $this->get_patientMasterData('PatientHospizverein') === null) {
            $entity = new PatientHospizverein();
            $saved = $entity->getHospizvereinData($this->ipid);
            $this->set_patientMasterData($saved, 'PatientHospizverein');
        }

        //fetch Medipumps for PatientMedipumps also
        if ((is_null($onlyThisModel) || $onlyThisModel == "Medipumps" || $onlyThisModel == "PatientMedipumps") && $this->get_patientMasterData('Medipumps') === null) {
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            $entity = new Medipumps();
            $saved = $entity->getMedipumps($clientid);
            $this->set_patientMasterData($saved, 'Medipumps');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientMedipumps") && $this->get_patientMasterData('PatientMedipumps') === null) {
            $entity = new PatientMedipumps();
            $saved = $entity->get_patient_medipumps($this->ipid);
            $this->set_patientMasterData($saved, 'PatientMedipumps');
        }
         
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientLocation") && $this->get_patientMasterData('PatientLocation') === null) {
            $entity = new PatientLocation();
            $saved = $entity->getIpidAllLocationDetails($this->ipid);
            $this->set_patientMasterData($saved, 'PatientLocation');
        }
        
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "SapvVerordnung") && $this->get_patientMasterData('SapvVerordnung') === null) {
            $entity = new SapvVerordnung();
            $saved = $entity->fetch_SapvVerordnung($this->ipid);            
            $this->set_patientMasterData($saved, 'SapvVerordnung');
        }
         
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientEmploymentSituation") && $this->get_patientMasterData('PatientEmploymentSituation') === null) {
            $entity = new PatientEmploymentSituation();
            $saved = $entity->fetch_oneByIpid($this->ipid);
            $this->set_patientMasterData($saved, 'PatientEmploymentSituation');
        }
        
		//ISPC-2508 Carmen 27.01.2020
		//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientArtificialEntriesExits") && $this->get_patientMasterData('PatientArtificialEntriesExits') === null) {
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$clientid = $logininfo->clientid;
        	
        	$saved = PatientArtificialEntriesExitsTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        	$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($clientid, Doctrine_Core::HYDRATE_ARRAY);
        	$client_options_byid = array();
        
        	foreach($client_options as $ko => $vo)
        	{
        		$client_options_byid[$vo['id']] = $vo;
        	}
        	foreach($saved as $kr => &$vr)
        	{
        		if($vr['isremove'] == 1)
        		{
        			unset($saved[$kr]);
        		}
        		else 
        		{
	        		$vr['option_name'] = $client_options_byid[$vr['option_id']]['name'];
	        		$vr['option_type'] = $client_options_byid[$vr['option_id']]['type'];
        		}
        	}
        	$saved = array_values($saved);

        	$this->set_patientMasterData($saved, 'PatientArtificialEntriesExits');
        }
        
        //ISPC-2694,elena,08.01.2020
        if ((is_null($onlyThisModel) || $onlyThisModel == "Anamnese") && $this->get_patientMasterData('Anamnese') === null) {
            $entity = new Anamnese();
            $saved = $entity->getLastBlockValues($this->ipid);
            $this->set_patientMasterData($saved, 'Anamnese');
        }


        //ISPC-2774 Carmen 16.12.2020
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientTherapy") && $this->get_patientMasterData('PatientTherapy') === null) {
        	
        	$saved = PatientTherapyTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        	//TODO-3830 Ancuta 09.02.2021
        	foreach($saved as $k=>$data){
        	    $saved[$k]['extratherapy_needed'] = '<span class="therapy_img_'.$data['extratherapy']['needed'].'"></span>';
        	    $saved[$k]['extratherapy_more_text'] =  strlen($data['extratherapy']['extratherapy_more'])>0 ?  $data['extratherapy']['extratherapy_more'] :"";
        	}
        	//--
        	$this->set_patientMasterData($saved, 'PatientTherapy');
        }
        //-- 
        //ISPC-2381 Carmen 12.01.2021
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientAids") && $this->get_patientMasterData('PatientAids') === null) {
        	 
        	$saved = PatientAidsTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        	 
        	$this->set_patientMasterData($saved, 'PatientAids');
        }
        //--
        /**
         * the next ones are the OLD ones, not written with the use of Zend_Form
         */
         
    
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientVisitsSettings") && $this->get_patientMasterData('PatientVisitsSettings') === null) {
            $entity = new PatientVisitsSettings();
            $saved = $entity->fetch_PatientVisitsSettings($this->ipid);
            $this->set_patientMasterData($saved, 'PatientVisitsSettings');
        } 
        
        
        if ((is_null($onlyThisModel) || $onlyThisModel == "PatientSurveySettings") && $this->get_patientMasterData('PatientSurveySettings') === null) {
        	
        	$patient_emails_arr =  array();
        	if($this->get_patientMasterData()['email'] != '')
        	{
        		$patient_emails_arr['patient'] = array();
        		$patient_emails_arr['patient']['p'.$this->get_patientMasterData()['id']] = $this->get_patientMasterData()['last_name'] . ' ' . $this->get_patientMasterData()['first_name'] . ' - ' . $this->get_patientMasterData()['email'];
        	}
        
        	$cnt_persons = $this->get_patientMasterData('ContactPersonMaster');
        	if(count($cnt_persons) > 0)
        	{
        		$patient_emails_arr['contact_person'] = array();
	        	foreach ($cnt_persons as $row) {
	        		if($row['cnt_email'] != '')
	        		{
	        			$patient_emails_arr['contact_person']['c'.$row['id']] = $row['cnt_last_name'] .' '. $row['cnt_first_name'] . ' - ' . $row['cnt_email'];
	        		}
	        	}
        	}
        
        	//var_dump($patient_emails_arr); exit; 
        	
        	$saved = PatientSurveySettingsTable::findByIpid($ipid)[0];
        	if($saved['parent_table'] == 'PatientMaster')
        	{
        		$saved['receiver'] = 'p'.$saved['table_id'];
        	}
        	else if($saved['parent_table'] == 'ContactPersonMaster')
        	{
        		$saved['receiver'] = 'c'.$saved['table_id'];
        	}
        	
        	$saved [ 'patient_emails_arr'] = $patient_emails_arr;
        
        	$this->set_patientMasterData($saved, 'PatientSurveySettings');
        }
         
        
        //ISPC-2432 Ancuta 13.01.2020
        if ((is_null($onlyThisModel) || $onlyThisModel == "MePatientDevices") && $this->get_patientMasterData('MePatientDevices') === null) {
            $translator = new Zend_View_Helper_Translate();
            
            $entity = new MePatientDevices();
            $devices_arr = array();
            $devices_arr = MePatientDevicesTable::find_patient_devices($this->ipid);
            
            // get client surveys 
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $client_surveys = MePatientSurveysTable::find_survey_ByClientid($clientid);
            
            $surveys = array();
            if(!empty($client_surveys)){
                foreach($client_surveys as $survey_id => $survey_details ){
                    $surveys[$survey_details['id']] = $survey_details['survey_name'];
                }
            }
            
            
            $save_devices_arr = array();
            if(!empty($devices_arr)){
                foreach($devices_arr as $k=>$pdevice){
                    $save_devices_arr[$pdevice['id']]['id'] = $pdevice['id'];
                    $save_devices_arr[$pdevice['id']]['device_name'] = $pdevice['device_name'];
                    $save_devices_arr[$pdevice['id']]['device_internal_id'] = $pdevice['device_internal_id'];
                    $save_devices_arr[$pdevice['id']]['device_type'] = $pdevice['device_type'];
                    $save_devices_arr[$pdevice['id']]['device_type_name'] = $translator->translate($pdevice['device_type'].'_radio');
                    $save_devices_arr[$pdevice['id']]['allow_photo_upload'] = $pdevice['allow_photo_upload'];
                    $save_devices_arr[$pdevice['id']]['active'] = $pdevice['active'];
                    $save_devices_arr[$pdevice['id']]['active_name'] = $translator->translate($pdevice['active'].'_radio');
                    if(!empty($pdevice['MePatientDevicesSurveys'])){
                        foreach($pdevice['MePatientDevicesSurveys'] as $kds=>$devices_survey){
                            $save_devices_arr[$pdevice['id']]['device_surveys'][] = $devices_survey['survey_id'];
                            $save_devices_arr[$pdevice['id']]['device_surveys_name'][] = $surveys[ $devices_survey['survey_id']] ;
                        }
                    }
                }
            }
            $this->set_patientMasterData($save_devices_arr, 'MePatientDevices');
        }
        //ISPC-2432 Ancuta 22.01.2020
        if ((is_null($onlyThisModel) || $onlyThisModel == "MePatientDevicesNotifications") && $this->get_patientMasterData('MePatientDevicesNotifications') === null) {
            $translator = new Zend_View_Helper_Translate();
            $devices_all_arr = array();
            $devices_all_arr = MePatientDevicesTable::find_patient_devices($this->ipid);
            
            
            $saved = MePatientDevicesNotificationsTable::findByIpid($this->ipid)[0];
            
            if(empty($saved)){
                $saved['status'] = 'disabled'; // Force displayed data - if deleted  or empty 
            }
            $saved['status_name'] = !empty($saved['status']) ? $translator->translate( $saved['status'].'_name'):'';
            //ISPC-2432 Carmen 03.06.2020
            if($saved['send_interval'] == 'custom')
            {
            	$saved['send_interval_name'] = !empty($saved['send_interval']) ? str_replace('X', $saved['send_interval_options'], $translator->translate( 'scheduled_'.$saved['send_interval'])):'';
            }
            else 
            {
            	$saved['send_interval_name'] = !empty($saved['send_interval']) ? $translator->translate( 'scheduled_'.$saved['send_interval']):'';
            }
            //--
            $saved['start_date'] = !empty($saved['start_date']) && $saved['start_date'] != "0000-00-00" ? $saved['start_date']:'';
            if($saved['status'] == 'enabled'){
                //$saved['send_now_button'] = '<button onclick="send_push_now(this)" >sende Nachrich</span>';
            } 
            
            foreach($devices_all_arr as $k=>$dev){
                if($dev['active'] == 'yes'){
                    $saved [ 'devices_arr'][] = $dev;
                }
            }
            
            $this->set_patientMasterData($saved, 'MePatientDevicesNotifications');
        }
        
        
        
         
    }
  
    
    
    
    
    
    
    /**
     * ISPC-2254
     * 
     * DO NOT USE IN YOUR CODE IN ISPC !
     * IT IS FROM NICO, AND MUST BE USED EXCLISIVELY FOR THE SYNC HE MADE
     * 
     * @param unknown $ipid
     * @return multitype:string unknown Ambigous <string, multitype:number > |multitype:
     */
    public static function get_Masterdata_quick($ipid){
    
        if ($ipid){
            $pt = Doctrine_Query::create()
            ->select("  ipid,
                        AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
                        AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
                        birthd"
            )
            ->from('PatientMaster')
            ->where("ipid =?", $ipid);
            $q_array = $pt->fetchArray();
    
            $returnarray=array();
    
    
            if(count($q_array)==1) {
                $patient=$q_array[0];
                $returnarray['name'] = $patient['last_name'] . ", " . $patient['first_name'];
                $returnarray['name2'] = $patient['first_name'] . " " . $patient['last_name'];
                $returnarray['first_name'] = $patient['first_name'];
                $returnarray['last_name'] = $patient['last_name'];
    
    
    
                list($BirthYear, $BirthMonth, $BirthDay) = explode("-", $patient['birthd']);
                $bdt_time = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);
                $curr_time = mktime(0, 0, 0, date("m"), date("d"), date("y"));
                $pm  = new PatientMaster();
                $years = $pm->GetTreatedDays(date("Y-m-d", $bdt_time), date("Y-m-d", $curr_time), true);
                $returnarray['age'] = $years;
                $returnarray['dob'] = date("d.m.Y", $bdt_time);
                $returnarray['dobtext'] = $returnarray['dob'] . "(" . $years . " Jahre)";
            }
    
    
            return $returnarray;
    
        } else{
            return array();
        }
    }
    
    
    
    /**
     * AncutaISPC-2432
     * @param number $clientid
     * @return void|array|Doctrine_Collection
     */
    public function get_today_mePatient_pushNotifications_patients($clientid = 0)
    {
        if(empty($clientid)){
            return;
        }
        
        $date = date("Y-m-d");
        $sqlHaving = "`sendnow` = 0";
        
        $q = $this->getTable()
        ->createQuery('pm indexBy ipid')
        ->select('pm.ipid,
                        AES_DECRYPT(pm.first_name,"' . Zend_Registry::get("salt") . '") as first_name,
                        AES_DECRYPT(pm.last_name,"' . Zend_Registry::get("salt") . '") as last_name,pm.birthd,
			    pdn.*,(	DATEDIFF( "' . $date . '"  , pdn.start_date ) % pdn.send_interval_options) AS `sendnow`,
                eim.ipid,dev.*')
			    ->Where('isdelete = 0')
			    ->leftJoin("pm.EpidIpidMapping eim")
			    ->andWhere('eim.clientid = ?', $clientid)
			    ->InnerJoin("eim.MePatientDevices dev")
			    ->andWhere('eim.ipid = dev.ipid')
			    ->andWhere('dev.isdelete = 0')
			    ->andWhere('dev.active = "yes"')
			    ->andWhere('dev.registration_id != "" AND dev.registration_id NOT LIKE "%notifications_disabled%" ')
			    ->leftJoin("pm.MePatientDevicesNotifications pdn")
			    ->andWhere('pm.ipid = pdn.ipid')
			    ->andWhere('pdn.status = "enabled"')
			    ->having($sqlHaving);
			    
			    $ipid_array = $q->fetchArray();
			    
			    return $ipid_array;
    }

    /**
     * ISPC-2614 Ancuta 16-17.07.2020
     * @param unknown $source_ipid
     * @param unknown $target_ipid
     */
    public function intense_connection_patient_admissions($source_ipid,$target_ipid){

        $source_patient_data = $this->getMasterData(NULL, 0, NULL, $source_ipid);
        
        $patients = array();
        // get data from patient active
        $pa = new PatientActive();
        $patients['source']['PatientActive'] = $pa->get_patient_fall($source_ipid);
        $patients['target']['PatientActive'] = $pa->get_patient_fall($target_ipid);

        
        // get data from patient patient readmission
        $pp = new PatientReadmission();
        $patients['source']['PatientReadmission'] = $pp->getPatientReadmissionAll($source_ipid);
        $patients['target']['PatientReadmission'] = $pp->getPatientReadmissionAll($target_ipid);
        
        
        // get data from patient patient discharge
        $discharge_data  = array();
        $discharge_data = Doctrine_Query::create()
        ->select('*')
        ->from('PatientDischarge')
        ->whereIn('ipid', array($source_ipid,$target_ipid))
        ->fetchArray();
        
        foreach($discharge_data  as $k=>$dis){
            if($dis['ipid'] == $source_ipid){
                $patients['source']['PatientDischarge'][] = $dis;
            } else if($dis['ipid'] == $target_ipid){
                $patients['target']['PatientDischarge'][] = $dis;
            }
        }
        
        //PatientStandby
        $standby_periods = array();
        $standby_periods = Doctrine_Query::create()
        ->select('*')
        ->from('PatientStandby')
        ->whereIn('ipid', array($source_ipid,$target_ipid))
        ->fetchArray();
        
        foreach($standby_periods  as $k=>$pst){
            if($pst['ipid'] == $source_ipid){
                $patients['source']['PatientStandby'][] = $pst;
            } else if($pst['ipid'] == $target_ipid){
                $patients['target']['PatientStandby'][] = $pst;
            }
        }
        //PatientStandbyDetails
        $psd = new PatientStandbyDetails();
        $patients['source']['PatientStandbyDetails'] = $psd->get_all_standby_details($source_ipid);
        $patients['target']['PatientStandbyDetails'] = $psd->get_all_standby_details($target_ipid);

        
        //PatientStandbyDelete
        $psdel = new PatientStandbyDelete();
        $patients['source']['PatientStandbyDelete'] = $psdel->get_patient_standby_fall($source_ipid);
        $patients['target']['PatientStandbyDelete'] = $psdel->get_patient_standby_fall($target_ipid);
        
        
        //PatientStandbyDelete
        $psdeld = new PatientStandbyDeleteDetails();
        $patients['source']['PatientStandbyDeleteDetails'] = $psdeld->get_all_standby_details($source_ipid);
        $patients['target']['PatientStandbyDeleteDetails'] = $psdeld->get_all_standby_details($target_ipid);
        
        // if no data in source, but in target - remove from target
        // THis is the case when the discharge was deleted.
        if(count($patients['source']['PatientDischarge']) < count($patients['target']['PatientDischarge'])){
            if(!empty($patients['target']['PatientDischarge'])){
                foreach($patients['target']['PatientDischarge'] as $k=>$tdata){
                    $dism = Doctrine::getTable('PatientDischarge')->findByIpidAndId($tdata['ipid'], $tdata['id']);
                    if($dism){
                        $dism->delete();
                    }
                }
            }
        }
        
        foreach($patients['source'] as $model =>$data){
            // remove data from target 
            // create log listner
            if(!empty($patients['target'][$model])){
                foreach($patients['target'][$model] as $k=>$tdata){
                    $dism = Doctrine::getTable($model)->findByIpidAndId($tdata['ipid'], $tdata['id']);
                    if($dism){
//                         $dis_dasss[] = $dism->toArray();
                        $dism->delete();
                    }
                    
                }
            }
     
            
            foreach($data as $k=>$entries){
                //ISPC-2614 + TODO-3481 Ancuta 06.10.2020
                if($model == 'PatientDischarge'){
                    $_POST['clone'] = 1;
                }
                // -- 
                $insert_obj = new $model();
                foreach($entries as $column =>$value){
                    $insert_obj->ipid = $target_ipid;
                    if(!in_array($column,array('id','ipid','change_date','change_user','status_period'))){
                        $insert_obj->{$column} = $value;
                    }
                }
                $insert_obj->save();
                //ISPC-2614 + TODO-3481 Ancuta 06.10.2020
                if($model == 'PatientDischarge'){
                    unset($_POST['clone']);
                }
                //-- 
            }
        }
        
//         dd($dis_dasss);
        // UPDATE TARGET PATIENT
//         $target_patient_data = Doctrine_Core::getTable('PatientMaster')->findByIpid($target_ipid);
        $target_patient_data = Doctrine::getTable('PatientMaster')->findOneByIpid($target_ipid);
        if($target_patient_data){
            $target_patient_data->admission_date = date('Y-m-d H:i:s', strtotime($source_patient_data['admission_date']));
            $target_patient_data->isdischarged = $source_patient_data['isdischarged'];
            $target_patient_data->isstandby = $source_patient_data['isstandby'];
            $target_patient_data->isstandbydelete = $source_patient_data['isstandbydelete'];
            $target_patient_data->traffic_status = $source_patient_data['traffic_status'];
            $target_patient_data->save();
        }
        
    }
    
    
    
    /**
     * Auth Ancuta 
     * ISPC-2614 19.07.2020
     */
    public function copy_source2target($params)
    {
//     public function copy_source2target($ComponentName,$excluded_columns = array(),$source_ipid, $target_ipid,$target_client,$target_)
        if(empty($params) ){
            return;
        }
        
        if(empty($params['source_ipid']) || empty($params['target_ipid'])){
            return;
        }
        
        if ( ! Doctrine_Core::isValidModelClass($params['model_name']) ) {
            return;
        }
        
        if(empty($params['excluded_columns'])){
            $params['excluded_columns'] = array('id','ipid','change_date','change_user');
        }
        
        
        $obj = new $params['model_name']();
	    $obj_columns = $obj->getTable()->getColumns();
        
        $source_data = Doctrine::getTable($params['model_name'])->findByIpid($params['source_ipid']);
        
        if($source_data)
        {
            $source_data_array = $source_data->toArray();
            
            $target_data = array();
            foreach($source_data_array as $k => $rows)
            {
                // 	            $entity = new $params['model_name']();
                foreach($rows as $column_name => $column_value){
                    if( ! in_array($column_name,  $params['excluded_columns']) ){
                        // 	                    $entity-> $column_name = $column_value;
                        $target_data[$k][$column_name] = $column_value;
                    }
                    $target_data[$k]['ipid'] = $params['target_ipid'];
                    
                    if(isset($params['target_client']) && array_key_exists('clientid', $obj_columns)){
                        $target_data[$k]['clientid'] = $params['target_client'];
                    }
                    // 	                $entity->ipid = $target_ipid;
                }
                // 	            $entity->save();
            }
            
            
            if(!empty($target_data)){
                $pph = new $params['model_name']();
				$pc_listener = $pph->getListener()->get('IntenseConnectionListener');
				if($pc_listener){
    				$pc_listener->setOption('disabled', true);
				}
				
				
                $collection = new Doctrine_Collection($params['model_name']);
                $collection->fromArray($target_data);
                $collection->save();

                $pphx = new $params['model_name']();
				$pcs_listener = $pphx->getListener()->get('IntenseConnectionListener');
				if($pcs_listener){
    				$pcs_listener->setOption('disabled', false);
				}
            }
            
        }
        else
        {
            return false;
        }
    }



		//Maria:: Migration CISPC to ISPC 22.07.2020    
        public static function getClientStandByPatients ( $cid )
        {
            $patient = Doctrine_Query::create()
                ->select('p.ipid, p.admission_date, e.epid')
                ->from('PatientMaster p')
                ->leftJoin("p.EpidIpidMapping e")
//			->where('p.ipid IN ('.substr($ipid_str,0,-1).') '.$q_discharged.' and p.isdelete = 0')
                ->where('p.isdelete = 0')
                ->andwhere('p.isstandby = 1')
                ->andWhere('e.clientid = ' . $cid);
            $admission_data = $patient->fetchArray();


            if (!$admission_data)
                return array();

            $ipids=array_column($admission_data, 'ipid');
            return $ipids;
        }

        /** Get the Age of a Patient
         *
         * @param $ipids        List of Patient-Ipids
         * @param $deathdate    Array of deathdate for Patients (ipid, deathdate).
         *
         * If no deathdate is in the array, the current date is used for calculation of the
         * patients age.
         * Maria:: Migration CISPC to ISPC 22.07.2020
         */
        public static function getPatientAges($ipids, $deathdate)
        {
            if (!is_array($ipids)) {
                $ipids = array($ipids);
            }
            $ipidstr = "";
            foreach ($ipids as $ipid) {
                if ($ipidstr) {
                    $ipidstr = $ipidstr . " ,";
                }
                $ipidstr .= "'" . $ipid . "'";
            }

            if ($ipidstr) {
                $pt = Doctrine_Query::create()
                    ->select("  ipid,birthd")
                    ->from('PatientMaster')
                    ->where("ipid IN (" . $ipidstr . ")");
                $q_array = $pt->fetchArray();

                $pm = new PatientMaster();

                $pats = array();

                foreach ($q_array as $pat) {
                    $deathdate_patient = '';
                    $key = array_search($pat['ipid'], array_column($deathdate, 'ipid'));
                    if($key !== false)
                        $deathdate_patient = $deathdate[$key]['death_date'];
                    else
                        $deathdate_patient = date("Y-m-d", time());
                    $pats[$pat['ipid']] = $pm->GetAge($pat['birthd'], $deathdate_patient);
                }

                return $pats;
            } else {
                return array();
            }
        }

		//Maria:: Migration CISPC to ISPC 22.07.2020
        public static function getPatientNames($ipids, $mode=1){
            if(!is_array($ipids)){
                $ipids=array($ipids);
            }
            $ipidstr="";
            foreach ($ipids as $ipid){
                if ($ipidstr) {
                    $ipidstr=$ipidstr." ,";
                }
                $ipidstr.= "'" . $ipid ."'";
            }

            if ($ipidstr){
                $pt = Doctrine_Query::create()
                    ->select("  ipid,
                        AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
                        AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name")
                    ->from('PatientMaster')
                    ->where("ipid IN (".$ipidstr.")");
                $q_array = $pt->fetchArray();

                $returnarray=array();

                foreach ($q_array as $patient){
                    if($mode==2){
                        $returnarray[$patient['ipid']] = $patient['last_name']. ", ". $patient['first_name'];
                    }elseif ($mode==3) {//ISPC-2804,Elena,18.02.2021
                        $returnarray[$patient['ipid']] = array($patient['first_name'] , $patient['last_name']);
                    }else {
                        $returnarray[$patient['ipid']] = $patient['first_name'] . " " . $patient['last_name'];
                    }
                }

                return $returnarray;
            } else{
                return array();
            }
        }

        public static function getPatientStatus($ipids){
            if(!is_array($ipids)){
                $ipids=array($ipids);
            }
             $pt = Doctrine_Query::create()
                    ->select('ipid, traffic_status')
                    ->from('PatientMaster')
                    ->whereIn('ipid', $ipids);
                $q_array = $pt->fetchArray();

                $returnarray=array();
                $translator = new Zend_View_Helper_Translate();
                foreach ($q_array as $patient){
                    $status = ($patient['traffic_status'] != '') ? $patient['traffic_status'] :'';
                    $returnarray[$patient['ipid']]['status'] = $status;
                    if($status == 1)
                        $returnarray[$patient['ipid']]['status_name'] = $translator->translate('traffic_status_name_green');
                    elseif($status == 2)
                        $returnarray[$patient['ipid']]['status_name'] = $translator->translate('traffic_status_name_yellow');
                    elseif($status == 3)
                        $returnarray[$patient['ipid']]['status_name'] = $translator->translate('traffic_status_name_red');
                    else
                        $returnarray[$patient['ipid']]['status_name'] = '';
                }

                return $returnarray;

        }

        public static function get_patients_death_dates($clientid, $ipids){
            $pd=new PatientDischarge();
            $deaths=$pd->getPatientsDeathDate($clientid,$ipids);

            $pdeath = new PatientDeath();
            $patientdeatharray = $pdeath->get_patients_death($ipids);

            if($patientdeatharray!=null) {
                foreach ($patientdeatharray as $entry) {
                    $deaths[$entry['ipid']] = $entry['death_date'];
                }
            }

            return $deaths;


        }

        /**
         * ISPC-2459 Ancuta 04.08.2020
         * @param array $clientids
         * @return void|array|Doctrine_Collection
         */
        public function get_today_hl7_activation_patients($clientids = array())
        {
            if (empty($clientids)) {
                return;
            }
            
            $q = $this->getTable()
            ->createQuery('pm indexBy ipid')
            ->select('pm.ipid,
                        AES_DECRYPT(pm.first_name,"' . Zend_Registry::get("salt") . '") as first_name,
                        AES_DECRYPT(pm.last_name,"' . Zend_Registry::get("salt") . '") as last_name,
                        pm.birthd,
                        pm.admission_date,
                        eim.ipid,
                        eim.clientid,
                        piv.*')
                        ->Where('isdelete = 0')
                        ->andWhere('pm.isdischarged = 0')
                        ->andWhere('pm.isstandby = 0')
                        ->andWhere('pm.isstandbydelete = 0')
                        ->leftJoin("pm.EpidIpidMapping eim")
                        ->andWhereIn('eim.clientid', $clientids)
                        ->InnerJoin("eim.PatientVisitnumber piv")
                        ->andWhere('eim.ipid = piv.ipid')
                        ->andWhere('piv.isdelete = 0');
                        $ipid_array = $q->fetchArray();
                        
                        return $ipid_array;
        }
		 //Maria:: Migration CISPC to ISPC 20.08.2020
        public static function getPatientAddress($ipid){

            if ($ipid){
                $pt = Doctrine_Query::create()
                    ->select("  ipid,
                        AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
                        AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
                        AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street,
                        AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,
                        AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
                        AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
                    ->from('PatientMaster')
                    ->where("ipid = ?", $ipid);
                $q_array = $pt->fetchArray();
            }
            $return="";
            $salutation="Sehr geehrter Patient,";
            if ($q_array){
                $return = $q_array[0]['first_name'] . " " . $q_array[0]['last_name'];
                $return.= "\n" . $q_array[0]['street'];
                $return.= "\n" . $q_array[0]['zip'] ." " . $q_array[0]['city'];
                $salutation="Sehr geehrter Herr ";
                if ($q_array[0]['sex']!=1){
                    $salutation="Sehr geehrte Frau ";
                }
                $salutation.=$q_array[0]['last_name'] .",";
            }
            return array($return,$salutation);
        }
        
        
        
        /**
         * ISPC-2411 + MEpatient 
         * Ancuta 17.09.2020
         * @param array $ipids
         * @param boolean $full
         * @return boolean|array[]|Doctrine_Collection[]
         */
        public static function get_patients_details_By_Ipids($ipids = array(), $full = false)
        {
        if (empty($ipids) || ! is_array($ipids)) {
            return false;
        }

        $sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
        $sql .= ",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
        $sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
        $sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
        $sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
        $sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
        $sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
        $sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
        $sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
        $sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
        $sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber";
        $sql .= ",kontactnumbertype as kontactnumbertype";
        $sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber_dec";
        $sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
        $sql .= ",AES_DECRYPT(email,'" . Zend_Registry::get('salt') . "') as email";
        $sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
        $sql .= ",AES_DECRYPT(height,'" . Zend_Registry::get('salt') . "') as height";
        $sql .= ",AES_DECRYPT(birth_name,'" . Zend_Registry::get('salt') . "') as birth_name";        //ISPC-2807 Lore 24.02.2021
        $sql .= ",AES_DECRYPT(birth_city,'" . Zend_Registry::get('salt') . "') as birth_city";        //ISPC-2807 Lore 24.02.2021
        $patient = Doctrine_Query::create()->select($sql . ",e.epid as epid, e.* ")
            ->from('PatientMaster p')
            ->whereIn('p.ipid', $ipids);
        $patient->leftJoin("p.EpidIpidMapping e");

        if ($full) {
            $patient->andWhere('p.isdelete = 0');
        }

        $patients_res = $patient->fetchArray();

        if ($patients_res) {
            $patient_details  =array();
            foreach ($patients_res as $k_pat => $v_pat) {
                $patient_details[$v_pat['ipid']] = $v_pat;
            }

            return $patient_details;
        } else {
            return false;
        }
    }

    
    /**
     * ISPC-2474 Ancuta 23.10.2020
     * @param number $clientid
     * @param unknown $filters_discharge
     * @return array|Doctrine_Collection
     * TODO-3737 Ancuta 19.01.2021 - remove isarchived condition
     */
    public function get_discharged_patients( $clientid = 0, $filters_discharge = null,$limit = 100)
    {
        if( ! isset ($limit)){
            $limit = 100;
        }
        
        $q = $this->getTable()->createQuery('pm')
		->select("pm.ipid,pm.last_update, pd.discharge_date, eim.epid")
//         ->select("pm.ipid")
        ->Where('isdelete = 0')
        ->andWhere('isstandbydelete = 0')
        ->andWhere('isdischarged = 1')
        //->andWhere('isarchived = 0')//TODO-3737 Ancuta 19.01.2021 - remove isarchived condition
        ->leftJoin("pm.EpidIpidMapping eim")
        ->andWhere('eim.clientid = ?', $clientid)
        ->leftJoin("pm.PatientDischarge pd")
        ->leftJoin("pm.Patient4Deletion p4d")
        ->andWhere("p4d.id is NULL ");
        
        if( ! empty($filters_discharge['x days before'])) {
            $q->andWhere('DATE(pd.discharge_date) < (CURRENT_DATE() - INTERVAL ? DAY)' , (int)$filters_discharge['x days before'] );
        }
        
        if( ! empty($filters_discharge['x days ago'])) {
            $q->andWhere('DATE(pd.discharge_date) = (CURRENT_DATE() - INTERVAL ? DAY)' , (int)$filters_discharge['x days ago'] );
        }
        
        if( ! empty($filters_discharge['last x days'])) {
            $q->andWhere("DATE(pd.discharge_date) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY) AND CURRENT_DATE() ",  (int)$filters_discharge['last x days']);
        }
        
        $q->limit($limit);
        $ipid_array = $q->fetchArray();
        
        return $ipid_array;
        
    }


        /**
         * IM-153 Nico 2020-09-30 [USED IN IM-147 (Comment added by Maria 25.02.2021)]
         * Code moved here from PatientMaster::changetrafficAction()
         * give ipid and new status to set patients current status
         * @param $status_id
         * @param $ipid
         * @throws Doctrine_Connection_Exception
         * @throws Doctrine_Record_Exception
         * @throws Zend_Session_Exception
         */
        public static function change_traffic_status($status_id, $ipid)
        {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $userid = $logininfo->userid;

            $existing_patient_data = array();
            $decid=Pms_CommonData::getIdfromIpid($ipid);
            $patid = Pms_Uuid::encrypt($decid);
            $cust = Doctrine::getTable('PatientMaster')->find($decid);

            $cust->traffic_status = $status_id;
            $cust->save();

            $status_array = array("1" => "normal, keine Krise", "2" => "Achtung, instabil", "3" => "Krise ", "4" => "Sterbend ");
            $comment = 'Der Status des Patienten wurde auf ' . $status_array[$status_id] . ' gesetzt';

            //@TODO	Add email for krise. // new Krise patient . send / dont send (assigned / all)
            if ($status_id == "3") {
                //SEND MESSAGE$decid
                $messages = new Messages();
                //$krise_notification = $messages->krise_notification($_REQUEST['patienttrid'], $ipid);
                $krise_notification = $messages->krise_notification($patid, $ipid);
            }

            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->course_type = Pms_CommonData::aesEncrypt("K");
            $cust->course_title = Pms_CommonData::aesEncrypt($comment);
            $cust->user_id = $userid;
            $cust->save();
            /*
            // Check if patient already has
            $pch = Doctrine::getTable('PatientCrisisHistory')->find($ipid);

            if(empty($pch)){
                // get current and save
                $cust_psh = new PatientCrisisHistory();
                $cust_psh->ipid = $ipid;
                $cust_psh->status_date = $existing_patient_data['admission_date'];
                $cust_psh->crisis_status = $existing_patient_data['traffic_status'];
                $cust_psh->status_create_user = $userid;
                $cust_psh->save();
            }
            */
            // ISPC-2400
            $cust_psh = new PatientCrisisHistory();
            $cust_psh->ipid = $ipid;
            $cust_psh->status_date = date("Y-m-d H:i:s", time());
            $cust_psh->crisis_status = $status_id;
            $cust_psh->status_create_user = $userid;
            $cust_psh->save();


            // ISPC-2491 Ancuta 29.11.2019
            $clientid = $logininfo->clientid;
            $previleges = new Modules();
            $module208_allow_todos = $previleges->checkModulePrivileges("208", $clientid);

            if ($module208_allow_todos) {
                $client_todos_obj = new ClientTodos();
                $send_todos = $client_todos_obj->send_statusChange_todos($clientid, $userid, $ipid, "patient_status_change");
            }
        }
    
}
?>