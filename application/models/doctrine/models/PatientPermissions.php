<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientPermissions', 'IDAT');

	class PatientPermissions extends BasePatientPermissions {

		public function getPatientPermisionsAll($userid, $ipid, $full = true)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientPermissions')
				->where('userid="' . $userid . '" and ipid = "' . $ipid . '" and (canview = "1" OR canedit = "1") and pat_nav_id != 0');
			$permisions = $q->fetchArray();

			if($permisions)
			{
				foreach($permisions as $p_k => $p_v)
				{
					if($full === true)
					{
						$permissions[$p_v['pat_nav_id']] = $p_v;
					}
					else
					{
						$permissions[$p_v['pat_nav_id']] = $p_v['pat_nav_id'];
					}
					ksort($permissions);
				}
				return $permissions;
			}
		}

		public function getPatientPermisionsRights($userid, $ipid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientPermissions')
				->where('userid="' . $userid . '" and ipid = "' . $ipid . '"');
			$permisions = $q->fetchArray();

			foreach($permisions as $p_k => $p_v)
			{
				$permissions[$p_v['pat_nav_id']]['canview'] = $p_v['canview'];
				$permissions[$p_v['pat_nav_id']]['canedit'] = $p_v['canedit'];
			}

			return $permissions;
		}

		public function verifyPermission($userid, $ipid, $navid, $permission = "canview")
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientPermissions')
				->where('userid = "' . $userid . '"')
				->andWhere('ipid = "' . $ipid . '"')
				->andWhere('pat_nav_id = "' . $navid . '"')
				->andWhere($permission . ' = 1');

			$haspermission = $q->fetchArray();
			if(!$haspermission)
			{
				$user_det = User::getUserDetails($userid);
				$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);

				$q = Doctrine_Query::create()
					->select('*')
					->from('PatientGroupPermissions')
					->where('groupid = "' . $group_id . '"')
					->andWhere('ipid = "' . $ipid . '"')
					->andWhere('pat_nav_id = "' . $navid . '"')
					->andWhere($permission . ' = 1');
				$haspermission = $q->fetchArray();

				if(!$haspermission)
				{
					$haspermission = GroupDefaultPermissions::verifyPermissionByGroupAndClient($group_id, $user_det[0]['clientid'], $navid, $permission);
				}
			}

			if(sizeof($haspermission) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function verifyMiscPermission($userid, $ipid, $misc, $permission = "canview")
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientPermissions')
				->where('userid = "' . $userid . '"')
				->andWhere('ipid = "' . $ipid . '"')
				->andWhere('misc_id = "' . $misc . '"')
				->andWhere($permission . ' = 1');
			$haspermission = $q->fetchArray();

			if(!$haspermission)
			{
				$user_det = User::getUserDetails($userid);
				$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);

				$q = Doctrine_Query::create()
					->select('*')
					->from('PatientGroupPermissions')
					->where('groupid = "' . $group_id . '"')
					->andWhere('ipid = "' . $ipid . '"')
					->andWhere('misc_id = "' . $misc . '"')
					->andWhere($permission . ' = 1');
				$haspermission = $q->fetchArray();

				if(!$haspermission)
				{
					$haspermission = GroupDefaultPermissions::verifyPermissionByGroupAndClient($group_id, $user_det[0]['clientid'], $misc, $permission, 'misc_id');
				}
			}

			if(sizeof($haspermission) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function verify_multiple_misc_permissions($userid, $ipids, $misc, $permission = "canview")
		{
			if(is_array($ipids))
			{
				$ipids[] = '999999999999999';
			}
			else
			{
				$ipids = array($ipids);
			}

			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientPermissions')
				->where('userid = "' . $userid . '"')
				->andWhereIn('ipid', $ipids)
				->andWhere('misc_id = "' . $misc . '"')
				->andWhere($permission . ' = 1');
			$haspermission = $q->fetchArray();

			if(!$haspermission)
			{
				$user_det = User::getUserDetails($userid);
				$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);

				$ipids[] = '999999999999';
				$q = Doctrine_Query::create()
					->select('*')
					->from('PatientGroupPermissions')
					->where('groupid = "' . $group_id . '"')
					->andWhereIn('ipid', $ipids)
					->andWhere('misc_id = "' . $misc . '"')
					->andWhere($permission . ' = 1');
				$haspermission = $q->fetchArray();

				if(!$haspermission)
				{
					$haspermission = GroupDefaultPermissions::verifyPermissionByGroupAndClient($group_id, $user_det[0]['clientid'], $misc, $permission, 'misc_id');
				}
			}

//			print_r($haspermission);
			if(sizeof($haspermission) > 0)
			{
				foreach($haspermission as $k_perm => $v_perm)
				{
					if(!empty($v_perm['ipid']))
					{
						$perms[$v_perm['ipid']] = $v_perm[$permission];
					}
					else
					{
						if($v_perm[$permission] == '1')
						{
							$perms = true;
						}
					}
				}

				return $perms;
			}
			else
			{
				return false;
			}
		}

		public function checkPermissionOnRun()
		{
			
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
			$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$post = Zend_Controller_Front::getInstance()->getRequest()->isPost();
				
			//custom stuff, stupid cow worshipers strike again
			if($action == 'patienttodoctor')
			{
				$decid = empty($_REQUEST['epid']) ? 0 : Pms_Uuid::decrypt($_REQUEST['epid']);
			}
			elseif($action == 'coursesession')
			{
				$decid = empty($_REQUEST['pid']) ? 0 : Pms_Uuid::decrypt($_REQUEST['pid']);
				
			} 
			else 
			{
				$decid =  empty($_REQUEST['id']) ? 0 : Pms_Uuid::decrypt($_REQUEST['id']);
			}
			
			//verify if ipid belongs to this clientid
			if ($decid != 0) {
				$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);			
				
				if (!$isclient)
				{
					//no patient , or patient belongs to another client';
					return false;
				}
			}

			$ipid = Pms_CommonData::getIpid($decid);
			
			
			$iskeyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
			$isadminvisible = PatientMaster::getAdminVisibility($ipid);
			$user_patients = PatientUsers::getUserPatients($logininfo->userid , true);


			//if user has no visibility, bail out
			if($ipid && !in_array($ipid, $user_patients['patients']) && $user_patients['bypass'] !== true)
			{
				$return = false;
				//$misc = 'No patient visibility';
				return false;
			}
			else
			{
			    
			    //TODO - chech if this is correct
			    $last_ipid_session = new Zend_Session_Namespace('last_ipid');
			    if ($last_ipid_session->ipid != $ipid) {
			        Zend_Session::namespaceUnset('last_ipid');
			        $last_ipid_session->ipid = $ipid;
			        $last_ipid_session->dec_id = $decid;
			    }
			     
				//linked actions that borrow rights
				$linked_actions = array(
					//Maria:: Migration CISPC to ISPC 22.07.2020
				    'patient/patientops' => array(
				        'patient/patientcasestatus'
                    ),
					'patient/patientmedication' => array(
						'patientform/newmediandschmerzpumpeplanpdf',
						'patientform/newmedikamentenplanpdf',
						'patientform/treatmentcareplanpdf',
						'patientform/medicationformnewpdf',
						'patient/openpdf',
						'patientform/medicationformpdf',
						'patientform/schmerzpumpeplanpdf',
// 						'patient/pharmacyorder',
// 						'patient/btmbuch'
						//'patientform/reassessment'
					),
					'patientmedication/overview' => array(
						'patientmedication/edit',
						'patientmedication/editblocks',
						
					),
					'patientformnew/treatmentweeks' => array(
						'patientformnew/braanlage5'
					),

					'patientform/anlage6billing' => array(
						'patientform/anlage6multiple'
					),
					'patient/doctorletter' => array(
						'patient/doctorletteradd',
						'patient/doctorletteredit',
						'patient/doctorlettercourse'
					),
				    
				    
				    // TODO-1746 Ancuta - added all actions from old stammdaten to the new one 
					'patientnew/patientdetails' => array(
						'patient/patienttodoctor',
						'patient/printpatientdetails',
						'patient/patientlocationlistedit',
						'patient/patientlocationadd',
						'patient/editcontactperson',
						'patient/patienthealthedit',
						'patient/sapvverordnungadd',
						'patient/sapvverordnungedit',
						'patient/familydocedit',
						'patient/vollversorgungedit',
						'patient/patientedit',
						'patient/pflegedienste',
						'patient/voluntaryworkers',
						'patient/pharmacyedit',
						'patient/patientstandbylist',
						'patient/patientboxhistory',
						'patient/patienthospiz',
						'patient/patientstandbydischargelist',
						'patient/fetchcalendarevents',
						'patient/calendar',
						'patient/savepatientevents',
						'patient/printpatientdetails',
						'patient/supplies',
						'patient/suppliers',
					    'patient/patienttoollistedit',
						// SGBV form
						'patient/sgbvverordnung',
						// Muster 63 form
// 						'patient/verordnungtp',
						// Hospizdienst
						'patient/hospiceassociation',
						//Specialists - in family doctor box
						'patient/specialists',
						// for DGP actions
						'patientform/dgpkernform',
						'patientform/dgpsapvform',
						'patient/physiotherapist',
						'patient/homecares',		
						'patient/church',		
						'patientnew/maintenancestage',

// 					    'patient/patientdetails',
					    'patient/savepatientneactions'
					    
					),
				    
					/*'patient/patientdetails' => array(
						 'patient/patienttodoctor',
						'patient/printpatientdetails',
						'patient/patientlocationlistedit',
						'patient/patientlocationadd',
						'patient/editcontactperson',
						'patient/patienthealthedit',
						'patient/sapvverordnungadd',
						'patient/sapvverordnungedit',
						'patient/familydocedit',
						'patient/vollversorgungedit',
						'patient/patientedit',
						'patient/pflegedienste',
						'patient/voluntaryworkers',
						'patient/pharmacyedit',
						'patient/patientstandbylist',
						'patient/patientboxhistory',
						'patient/patienthospiz',
						'patient/patientstandbydischargelist',
// 						'patient/fetchcalendarevents', // COMENTED FOR TODO-1746
// 						'patient/calendar',
						'patient/savepatientevents',
						'patient/printpatientdetails',
						'patient/supplies',
						'patient/suppliers',
					    'patient/patienttoollistedit',
						// SGBV form
						'patient/sgbvverordnung',
						// Muster 63 form
// 						'patient/verordnungtp',
						// Hospizdienst
						'patient/hospiceassociation',
						//Specialists - in family doctor box
						'patient/specialists',
						// for DGP actions
						'patientform/dgpkernform',
						'patientform/dgpsapvform',
						'patient/physiotherapist',
						'patient/homecares',		
						'patient/church',		
						'patientnew/maintenancestage'	 	
					),*/
				    
				    
					'patient/patientfileupload' => array(
						'patient/fetchpatientfile',
							'patient/fileupload',
					    'patient/uploadify2018',
					    
					),
					'patientcourse/patientcourse' => array(
						'patientcourse/printcourse',
						'patient/coursesession',
						'patient/patientcourse',
						'patientcourse/requestmedicationdata',
						// ISPC-1994
						'patientformnew/hospizregisterv3'
							
					),
					'patient/hospizv' => array(
						'patient/addhospizv',
						'patient/edithospizv'
					),
					'patientformnew/mappe'=>array(
						'patientformnew/shfilesmacro',
						
					),
					'patientformnew/listreceipts'=>array(
						'patientformnew/receiptpinew',
						'patientformnew/fetchreceipts',
						'patientformnew/listreceipts'
					),
					'patientformnew/muster13list'=>array(
						'patientformnew/muster13',
						'patientformnew/getmuster13list',
						'patientformnew/printmuster13',
						'patientformnew/duplicatemuster13',
						'patientformnew/deletemuster13',
						'patientformnew/muster13list',
					),
				    
				    //TODO-3654 Ancuta 03.12.2020
					'patientformnew/muster132020list'=>array(
						'patientformnew/muster132020',
						'patientformnew/getmuster132020list',
						'patientformnew/printmuster132020',
						'patientformnew/duplicatemuster132020',
						'patientformnew/deletemuster132020',
						'patientformnew/muster132020list',
					),
				    // --
				    
				    
					'patientformnew/mdkreport'=>array(
						'patientformnew/bermdkfiles',
					),
						
//					'patient/calendar' => array(
//						'calendar/calendars',
//						'calendar/savedoctorevents',
//						'calendar/fetchteamevents',
//						'calendar/fetchdoctorsevents'
//					)
//															,
//					'calendar/calendars' => array(
//						'patient/fetchcalendarevents',
//						'patient/calendar',
//						'patient/savepatientevents',
//						'calendar/calendars',
//						'calendar/savedoctorevents',
//						'calendar/fetchteamevents',
//						'calendar/fetchdoctorsevents'
//					)
		
					//TODO-3858 Ancuta 09.03.2021
// 					'patientnew/medication'=> array(
// 							'patientnew/datamatriximport',
							
// 					),
					//--
					'patientformnew/complaintform'=> array(
							'patientformnew/deletecomplaintform',
							
					),
				    
					//ISPC-2512
					'charts/overview'=> array(
							'charts/navigation',
							'charts/chartdata',
							
					),
				    //ISPC-2654 Ancuta 10.12.2020
					'patientdiagnosis/overview'=> array(
							'patientdiagnosis/tabsevents',
					)
				);

				//hack to allow usage for all
				$free_actions = array(
//					'calendar',
					'addressbook',
					'addressbookentries',
					'patientstandbylist',
					'patientstandbydischargelist',
					'allpatientlist',
					'patientoveralllist',
					'patientmasteradd', //this is handled by old user system
					'patientmasteredit', //this is handled by old user system
					    
				    'medicationeditblocks',
				    'medicationdeletededit',
				    'medicationdeleted',
				    'patientmedicationchange',
					
					'updatepatientinfo',
						
				    'hospizregister',
// 				    'hospizregisterv3',
				    'vitalsigns',
				    'printpdfcourse',// form in patientform -  from vital signs icon
				    'reciperequest',
				    
				    
				    // from controller patientmedication
				    'editblocks',
				    'deletededit',
				    'deleted',
                    'patienttoroom', //Maria:: Migration CISPC to ISPC 20.08.2020
				    
				    // from controller pateint- used also from ICON - even if no permisions to medications
				    'btmbuch', //TODO-2460 - Moved to free action by Ancuta on 06.08.2019
				    
				    
				    'pharmacyorder',//TODO-2501 - Moved to free action by Ancuta on 15.08.2019
				    
				    'medicationinfo', //TODO-2643 Lore 12.11.2019
				    
				    'saveemptyform',//TODO-2825 Ancuta ISPC : Gesamtpunktzahl GDS 15 is not saved 17.01.2020
				    
				    'requestchanges',//ISPC-2507
				    
				    'psychosocialstatusreport',
				    
				    //TODO-3858 Ancuta 09.03.2021
				    'datamatriximport',
				);

 
				//ISPC-2827 Ancuta 31.03.2021
				if ($logininfo->isEfaClient == '1' && $logininfo->isEfaUser == '1')
				{

				    $efa_free_actions = array(
				        
				    //ISPC-2827
				    'ambulatorycurve',
				    'efamedication',
				    'diagnosis',
				    'patientfileupload',
				    'interventions',
				    'reactions',
				    'vaccinations',
				    'history',
				    'fetchallCalendarTypes',
				    'previlege',
				    'fetchpatientfile',
				    'uploadify2018',
				     'tabsevents',//ISPC-2831 Ancuta 20.04.2021
				     'anlage2kinder',//ISPC-2882  Ancuta 22.04.2021
				     'aidsuppliers',//ISPC-2892  Ancuta 27.04.2021
				     'beatmung',//ISPC-2891  Ancuta 29.04.2021
				        'patientdetails',//ISPC-2880 Ancuta - 07.05.2021
				        'versorgerreport'//ISPC-2880 Ancuta - 07.05.2021
				    );
				    
				    $free_actions = array_merge($free_actions, $efa_free_actions);
				    
				}
				
				
				
				//hack to prevent editing
				$editing_actions = array(
					'patientedit',
					'familydocedit',
					'sapvverordnungedit',
					'sapvverordnungadd',
					'patienthealthedit',
					'editcontactperson',
					'pflegedienste',
					'supplies',
					'voluntaryworkers',
					'patientlocationadd',
// 					'changetraffic', > Moved in $editing_patientnew_actions :: 16.08.2018 TODO-1746 Ancuta
					//  missing edit actions 
					'specialists',
// 					'patientlocationlistedit', > Moved in $editing_patientnew_actions :: 16.08.2018 TODO-1746 Ancuta
					'patienttoollistedit',
					'pharmacyedit',
					'suppliers',
					'physiotherapist',
					'church',
// 					'sgbvverordnung', > Moved in $editing_patientnew_actions :: 16.08.2018 TODO-1746 Ancuta
					'homecares',
						
				);

				//move editing under medis
				$editing_medi_actions = array (
					'patientmedicationadd',
					'patientmedicationedit'
// 					'patientmedicationchange'
				);
				
				//move editing under medis NEW ISPC 1624
				$editing_medi_actions_new = array (
					'medicationedit',
					'medicationhistory',
				    'medicationshared',
					'medicationprint',
				    //'datamatriximport',//TODO-3858 Ancuta 09.03.2021
					'medicationsets',
					'medicationsetitems',
				);

				$editing_patient_medi_actions_new = array (
					'edit',
					'history',
				    'shared',
					'print',
				    'datamatriximport',//TODO-3858 Ancuta 09.03.2021
					'sets',
					'setitems',
				);
				

				//move editing under diagno
				$editing_diagno_actions = array (
					'patdiagnoremove',
					'patdiagnometaremove'
				);
				
				
				//move editing under hospiz
				$editing_hospizv_actions = array (
					'deletehospizv'
				);
				
				//move editing under patientfile
				$editing_patientfile_actions = array (
					'patientfileremove',
						'fileupload'
				);
				
				
				//move editing under organisations / path
				$editing_paths_actions = array (
					'saveorganisationchart' // path- controller
				);
				
				
				//move editing under receipt new
				$editing_newreceipt_actions = array (
					'receiptsremove' 
				);
				
				
				//hack admin to prevent editing
				$admin_editing_actions = array(
					'patientedit',
					'editcontactperson'
				);

				//move editing for HospizregisterV3  under patient course
				$editing_patientcourse_actions = array (
						'hospizregisterv3'
				);

				
				//move editing for actions in NEW stammdatem  under patient new :: 16.08.2018 TODO-1746 Ancuta
				$editing_patientnew_actions = array (
			        'sgbvverordnung',
				    'changetraffic',
				    'patientlocationlistedit',
// 				    'patientlocationadd',
// 				    'patientdetails',
				    'savepatientneactions',
				);
				
				
				if(in_array($action, $admin_editing_actions))
				{
					//$action = 'patientdetails';
					$noadmin = true;
				}

				/* if(in_array($action, $editing_actions))
				{
					$action = 'patientdetails';
					$post = true;
				} */
				
				if(in_array($action, $editing_medi_actions))
				{
					$action = 'patientmedication';
					$post = true;
				}
				
				if(in_array($action, $editing_medi_actions_new))
				{
					$action = 'medication';
					$post = true;
				}
				
				if(in_array($action, $editing_patient_medi_actions_new) && $controller == 'patientmedication')
				{
					$action = 'overview'; // ISPC-2329
					$post = true;
				}

				if(in_array($action, $editing_diagno_actions))
				{
					$action = 'patdiagnoedit';
					$post = true;
				}
				
				
				if(in_array($action, $editing_hospizv_actions))
				{
					$action = 'addhospizv';
					$post = true;
				}
				
				if(in_array($action, $editing_patientfile_actions))
				{
					$action = 'patientfileupload';
					$post = true;
				}
				
				if(in_array($action, $editing_paths_actions))
				{
					$action = 'organisation';
					$post = true;
				}

				
				if(in_array($action, $editing_newreceipt_actions))
				{
					$action = 'listreceipts';
					$post = true;
				}
				
				if(in_array($action, $editing_patientcourse_actions))
				{
					$controller = 'patientcourse';
					$action = 'patientcourse';
				}
				
				if(in_array($action, $editing_patientnew_actions))
				{
					$controller = 'patientnew';
					$action = 'patientdetails';
				}
				
	 
				if($ipid && $logininfo->usertype != 'SA' && $logininfo->usertype != 'CA' && !$iskeyuser && !in_array($action, $free_actions))
				{
					$link = $controller . '/' . $action;

					foreach($linked_actions as $allowed => $actions)
					{
						if(in_array($link, $actions))
						{
							$link = $allowed;
						}
					}
					
					$navid = TabMenus::getMenubyLink($link);
					if($navid)
					{
						if($post)
						{
							$privilege = 'canedit';
						}
						else
						{
							$privilege = 'canview';
						}
						$haspermission = PatientPermissions::verifyPermission($logininfo->userid, $ipid, $navid[0]['id'], $privilege);

						if(!$haspermission)
						{
							$return = false;
						}
						else
						{
							$return = true;
						}
					}
					else
					{
						$return = false;
					}
				}
				elseif($logininfo->usertype == 'SA' && !$isadminvisible)
				{
					//no editing for obscured admin
					if($noadmin)
					{
						$return = false;
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					$return = true; //no ipid, we should not handle this
				}
			}
			if($return === false)
			{
//				echo $controller.'   '.$action.' '.$link.' '.$privilege.' '.$navid[0]['id'].' '.$haspermission;
//				exit;
				PatientPermissions::LogRightsError(false, $misc);
			}

			return $return;
		}

		public static function LogRightsError($navi = false, $misc = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			if($navi === false && $_REQUEST['id'])
			{
				$ipid = Pms_CommonData::getIpid($decid);
				$epid = Pms_CommonData::getEpid($ipid);
			}
			
			
// 			$message .= "\n\nPage name :" . $_SERVER['REQUEST_URI'] . "\n";
// 			$message .= "From :" . $_SERVER['HTTP_REFERER'] . "\n";
// 			$message .= "Browser :" . $_SERVER['HTTP_USER_AGENT'] . "\n";
// 			if($navi != 2)
// 			{
// 				$message .= "Controller: " . Zend_Controller_Front::getInstance()->getRequest()->getControllerName() . " \n";
// 				$message .= "Action: " . Zend_Controller_Front::getInstance()->getRequest()->getActionName() . " \n";
// 			}
			$message .= "Patient EPID: " . $epid . " \n";
			$message .= "Patient IPID: " . $ipid . " \n";
// 			$message .= "Date :" . date("d.m.Y H:i:s", time()) . "\n";
// 			$message .= "Username :" . $logininfo->username . "\n";
// 			$message .= "Userype :" . $logininfo->usertype . "\n";
// 			$message .= "IP-Address :" . $_SERVER['REMOTE_ADDR'] . "\n";
			if($misc != false)
			{
				$message .= "\n" . $misc . "\n";
			}
			if($navi !== false)
			{
				$message .= "NAV Permission Error" . "\n";
			}
			$message .= "\n\n======================================================================== \n\n";

			
			self::_log_error($message);
			
			return;
// 			$logger = Zend_Registry::get('logger');
// 			$logger->rights($message);
// 			$logger->rightsmail($message);
			/*
			$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/rights.log');
			$log = new Zend_Log($writer);
			if($log)
			{
				$log->crit($message);
			}
			*/
		}
        /**
         *  ISPC-2482 
         *  Function changed- so group visibility also checks for user group, not only master group 
         *  Loredana 22.11.2019
         *  Ancuta 12.12.2019
         * @return boolean
         */
		public function checkUserAccess()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$group_id = $logininfo->groupid;
			
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			
			$master_group_id = Usergroup::getMasterGroup($group_id);
			$groupbypass = GroupDefaultVisibility::getClientVisibilityByGroup($master_group_id , $logininfo->clientid, $group_id );
			

			$has_acces = false;

			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')
			{
				$patuser = Doctrine_Query::create()
					->select('x.ipid')
					->from('EpidIpidMapping x')
					->where('x.clientid = ' . $logininfo->clientid);
				$patients_q_array = $patuser->fetchArray();

				foreach($patients_q_array as $k => $pat_ipid)
				{
					$patients_ipids[] = $pat_ipid ['ipid'];
				}
			}
			else
			{
				$pat_epids = Doctrine_Query::create()
					->select('epid')
					->from('PatientQpaMapping')
					->where('userid = ' . $logininfo->userid);
				$patients_epid_array = $pat_epids->fetchArray();

				foreach($patients_epid_array as $k => $pat_epid)
				{
					$patients_epids[] = $pat_epid ['epid'];
				}
				$patients_epids[] = "XXXXXX";
				$patients_epids = array_unique($patients_epids);

				$pat_ipids = Doctrine_Query::create()
					->select('x.ipid')
					->from('EpidIpidMapping x')
					->where('x.clientid = ' . $logininfo->clientid)
					->andWhereIn('x.epid', $patients_epids);
				$patients_q_array = $pat_ipids->fetchArray();

				foreach($patients_q_array as $k => $pat_ipid)
				{
					$patients_ipids[] = $pat_ipid ['ipid'];
				}
				$patients_ipids[] = "XXXXXX";
			}

			if(in_array($ipid, $patients_ipids))
			{
				$has_acces = true;
			}
			else
			{
				$has_acces = false;
			}
			return $has_acces;
		}

		/**
		 *  ISPC-2482
		 *  Function changed- so group visibility also checks for user group, not only master group
		 *  Loredana 22.11.2019
		 *  Ancuta 12.12.2019
		 * @return boolean
		 */
		public static function document_user_acces()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$group_id = $logininfo->groupid;

			$previleges = new Modules();
			$modulepriv = $previleges->checkModulePrivileges("80", $logininfo->clientid);

			//bypass if master group has permission to skip SecrecyTracker verlauf check
			$master_group_id = Usergroup::getMasterGroup($group_id);

			$groupbypass = GroupSecrecyVisibility::getClientVisibilityByGroup($master_group_id, $clientid, $group_id);
			

			if($modulepriv && !$groupbypass)
			{
				$user_privileges = PatientPermissions::checkUserAccess();

				if(!$user_privileges)
				{
					// write in patient course;
					$write2course = new Application_Form_PatientCourse();
					$write2course->tracker2course();
				}
			}
		}

		
		public function MedicationLogRightsError($navi = false, $misc = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $decid = Pms_Uuid::decrypt($_REQUEST['id']);
		    if($navi === false && $_REQUEST['id'])
		    {
		        $ipid = Pms_CommonData::getIpid($decid);
		        $epid = Pms_CommonData::getEpid($ipid);
		    }
		    $message .= "\n\nPage name :" . $_SERVER['REQUEST_URI'] . "\n";
		    $message .= "From :" . $_SERVER['HTTP_REFERER'] . "\n";
		    $message .= "Browser :" . $_SERVER['HTTP_USER_AGENT'] . "\n";
 
		    $message .= "Patient: " . $epid . " \n";
		    $message .= "Date :" . date("d.m.Y H:i:s", time()) . "\n";
		    $message .= "Username :" . $logininfo->username . "\n";
		    $message .= "Userype :" . $logininfo->usertype . "\n";
		    $message .= "IP-Address :" . $_SERVER['REMOTE_ADDR'] . "\n";
		    if($misc != false)
		    {
		        $message .= "\n" . $misc . "\n";
		    }
		    if($navi !== false)
		    {
		        $message .= "NAV Permission Error" . "\n";
		    }
		    $message .= "\n\n======================================================================== \n\n";
		
		    $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/medication.log');
		    $log = new Zend_Log($writer);
		    if($log)
		    {
		        $log->crit($message);
		    }
		}

		
		
		/**
		 * @cla on 30.06.2018  added static... cause thaty is how was used
		 */
		public static function checkSpecificPermission($link = false,$edit = false)
		{
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($decid);
			$iskeyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
			$isadminvisible = PatientMaster::getAdminVisibility($ipid);
			$user_patients = PatientUsers::getUserPatients($logininfo->userid);
		
				
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
			$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
 
		
			//if user has no visibility, bail out
			if($ipid && !in_array($ipid, $user_patients['patients']) && $user_patients['bypass'] !== true)
			{
				$return = false;
				//$misc = 'No patient visibility';
				return false;
			}
			else
			{
		
				if($ipid && $logininfo->usertype != 'SA' && $logininfo->usertype != 'CA' && !$iskeyuser )
				{
					if(!$link){
						$link = $controller . '/' . $action;
					}
					
					$navid = TabMenus::getMenubyLink($link);
					if($navid)
					{
						if($edit)
						{
							$privilege = 'canedit';
						}
						else
						{
							$privilege = 'canview';
						}
						$haspermission = PatientPermissions::verifyPermission($logininfo->userid, $ipid, $navid[0]['id'], $privilege);
		
						if(!$haspermission)
						{
							$return = false;
						}
						else
						{
							$return = true;
						}
					}
					else
					{
						$return = false;
					}
				}
				elseif($logininfo->usertype == 'SA' && !$isadminvisible)
				{
					//no editing for obscured admin
					if($noadmin)
					{
						$return = false;
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					$return = true; //no ipid, we should not handle this
				}
			}
			if($return === false)
			{
				PatientPermissions::LogRightsError(false, $misc);
			}
		
			return $return;
		}
		
		
	/**
	 *
	 * @param string $message
	 */
	protected static function _log_info($message)
	{
	    parent::_log_info($message, 12);
	}
	
	/**
	 *
	 * @param string $message
	 */
	protected static function _log_error($message)
	{
	    parent::_log_error($message, 13);
	}
}

?>