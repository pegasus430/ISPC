<?php
// use Dompdf\Dompdf;
// use Dompdf\Options;

class CalendarController extends Pms_Controller_Action //Zend_Controller_Action 
{

		public function init()
		{
		    array_push($this->actions_with_js_file, "calendars");
		    
		}

		public function calendarsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			/* ######################################################### */

			$docquery = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid=' . $logininfo->clientid . ' and isdelete=0 and isactive=1');
			$groups = $docquery->fetchArray();

			$groupsStr = "'99999999'";
			$comma = ",";
			foreach($groups as $group)
			{
				$groupsStr .= $comma . "'" . $group['id'] . "'";
				$comma = ",";

				$groupsFinal[$group['id']] = $group;
			}
			$this->view->groups = $groupsFinal;

			$users = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isactive=0 and isdelete=0 and groupid IN (' . $groupsStr . ')')
				->orderBy('last_name ASC');
			$groupsUsers = $users->fetchArray();

			foreach($groupsUsers as $user)
			{
				$groupsUsersFinal[$user['groupid']][0] = "";
				$groupsUsersFinal[$user['groupid']][$user['id'] . "-" . $user['groupid']] = $user['last_name'] . ',' . $user['first_name'];
			}
			$this->view->groupUsers = $groupsUsersFinal;


			$user_patients = PatientUsers::getUserPatients($logininfo->userid); //get user's patients by permission

			$patient = Doctrine_Query::create()
				->select("p.ipid, e.ipid as ipid,e.epid as epidpatient, CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name, CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name")
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
// 				->leftJoin("e.PatientQpaMapping q") // TODO-1674
				->where("p.isdischarged = 0 and p.isdelete = 0 and p.isstandby = 0 and isstandbydelete = 0")
				->andWhere('e.clientid = ?', $logininfo->clientid)
				->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')');
			
			//TODO-1674 05.07.2018
			/* 
			if($logininfo->usertype != 'SA')
			{
			    //TODO-1439 calendar bug?
// 				$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $logininfo->clientid . ' and q.userid = ' . $logininfo->userid);
				$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $logininfo->clientid );
			}
			else
			{
				$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $logininfo->clientid);
			}
 */

			$patienidtarray = $patient->fetchArray();

			$patientarray[0]['count'] = sizeof($patienidtarray);
			$patienidtarray[9999999] = "xx";


			$patientsArray = $patient->fetchArray();
			$patientsFinalArray[0] = "Patienten wählen";
			foreach($patientsArray as $patient)
			{
				$patientsFinalArray[$patient['ipid']] = $patient['last_name'] . ", " . $patient['first_name'] . " - " . $patient['epidpatient'];
			}

			$this->view->patientsSelect = $patientsFinalArray;
			

			$nh = new NationalHolidays();
			$national_holiday = $nh->getNationalHoliday($clientid, false);
			
			foreach($national_holiday as $k_holiday => $v_holiday)
			{
			    $holiday_dates[] = date('Y-m-d', strtotime($v_holiday['NationalHolidays']['date']));
			}
			$this->view->national_holidays = $holiday_dates;
			$this->view->national_holidays_js = json_encode($holiday_dates);
		}

		
		/*
		 * @claudiu on 21.02.2018 for ISPC-2159 added extra param
		 * $returnArray =  true
		 * $exclude_DoctorCustomEvents = false
		 * 
		 * this fn is called from fetchallcalendartypesAction
		 * 
		 * @param string $returnArray
		 */
		public function fetchdoctorseventsAction($returnArray =  false, $exclude_DoctorCustomEvents = false)
		{

			$this->_helper->viewRenderer->setNoRender(true);
			$this->_helper->layout->disableLayout();

			$eventsArray =  array();// this is the result
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$user_type = $logininfo->user_type;
			$this->view->userid = $userid;
			$this->view->groupid = $groupid;
			$this->view->user_type = $user_type;
			$hidemagic = Zend_Registry::get('hidemagic');
			
			
			//ISPC-2311 - custom color/client for patient course entries
			$clt = new Client();
			$cl_data = $clt->getClientDataByid($clientid);
				
			if($cl_data[0]['patient_course_settings'])
			{
				$patient_course_settings = $cl_data[0]['patient_course_settings'];
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
			//print_r($patient_course_settings);
			
			//get client ipids to avoid multi client users to see patients in other clients
			$sql = "*,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.traffic_status,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
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
				$sql = "*,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.isadminvisible,p.traffic_status,";
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

			$clientpatients = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where('isdelete = 0')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$client_ipids_res = $clientpatients->fetchArray();

			$client_ipids_arr[] = '999999999';
			foreach($client_ipids_res as $k_cipid => $v_cipid)
			{
				$client_ipids_arr[] = $v_cipid['ipid'];
				$patients_details[$v_cipid['ipid']] = $v_cipid;
				$patients_details[$v_cipid['ipid']]['epid'] = $v_cipid['EpidIpidMapping']['epid'];
			}

			// get assesment completed -> reeval date-> to show Re-assesment START
			$reassesmentprv = new Modules();
			$reass_mod = $reassesmentprv->checkModulePrivileges("56", $logininfo->clientid);
			if($reass_mod)
			{
				//get user type
				$usergroup = new Usergroup();
				$MasterGroups = array("6"); // Koordinator
				$usersgroups = $usergroup->getUserGroups($MasterGroups);
				if(count($usersgroups) > 0)
				{
					foreach($usersgroups as $group)
					{
						$groupsarray[] = $group['id'];
					}
				}
				$usrs = new User();
				$KoordArray = $usrs->getuserbyGroupId($groupsarray, $clientid);

				foreach($KoordArray as $user)
				{
					$Koords[] = $user['id'];
				}

				// get ipids if user is Koordinator
				$allpatk = Doctrine_Query::create()
					->select('pm.ipid')
					->from('PatientMaster pm')
					->where('pm.isdelete = 0 and pm.isstandbydelete = 0 and pm.isstandby = 0')
					->andWhere('isdischarged = 0')
					->leftJoin('pm.EpidIpidMapping ep')
					->andWhere('ep.clientid=' . $clientid)
					->andWhere('ep.ipid=pm.ipid');
				$allpatkoor = $allpatk->fetchArray();

				$comma = ",";
				$ipidkores = "'0'";
				foreach($allpatkoor as $key => $val)
				{
					$ipidkores .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}


				// get ipids of all assigned patients
				$assign = Doctrine_Query::create();
				$assign->select("*")
					->from('PatientQpaMapping')
					->where("clientid='" . $logininfo->clientid . "'")
					->andWhere("userid='" . $logininfo->userid . "'");
				$assignarray = $assign->fetchArray();

				$reassignedPatients = $assignarray;
				$comma = ",";
				$reepidval = "'99999999'";
				foreach($reassignedPatients as $aPatient)
				{
					$reepidval .= $comma . "'" . $aPatient['epid'] . "'";
					$comma = ",";
				}

				$q = Doctrine_Query::create();
				$q->select("ipid")
					->from('EpidIpidMapping')
					->where("clientid='" . $logininfo->clientid . "'");
				$q->andWhere("epid IN (" . $reepidval . ")");
				$reipidarray = $q->fetchArray();

				$comma = ",";
				$ipidvalres = "'0'";
				foreach($reipidarray as $key => $val)
				{
					$ipidvalres .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}

				$hidemagic = "XXXXXXXXXX";


				$patientq = Doctrine_Query::create();

				if($logininfo->usertype == 'SA')
				{
					$sqlq = 'p.id as patient_id,p.ipid, e.epid as patient_epid,p.isadminvisible,';
					$sqlq .= "IF(p.isadminvisible = '1',CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sqlq .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					$patientq->select($sqlq);
				}
				else
				{
					$patientq->select('p.id as patient_id,p.ipid, e.epid as patient_epid,' . "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name");
				}
				$patientq->from('PatientMaster p')
					->where('p.ipid IN (' . $ipidvalres . ')')
					->orwhere('p.ipid IN (' . $ipidkores . ')')
					->andwhere('p.isdelete = 0')
					->andWhere('p.isarchived = 0')
					->andWhere('p.isstandby = 0')
					->andWhere('p.isstandbydelete = 0');
				$patientq->leftJoin("p.EpidIpidMapping e");
				$patientq->andWhere('e.clientid = ' . $logininfo->clientid);

				$patientsArray = $patientq->fetchArray();
				foreach($patientsArray as $patientSel)
				{
					$patientsFinal[$patientSel['ipid']] = $patientSel;
				}

				$ipidrrs = array();
				if(in_array($userid, $Koords))
				{
					$ipidrrs = $allpatkoor;
				}
				else
				{
					$ipidrrs = $reipidarray;
				}
				$comma = ",";
				$reass_str = "'0'";
				foreach($ipidrrs as $key => $val)
				{
					$reass_str .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}


				$reass = new KvnoAssessment();
				$reassarray = $reass->getAllAssesmentsInPeriod($reass_str, $_REQUEST['start'], $_REQUEST['end']);
				//check to see if no standby etc
				foreach($reassarray as $reassesment)
				{
					$fullName = "";
					if(!empty($patientsFinal[$reassesment['ipid']]['last_name']))
					{
						$fullName .= $patientsFinal[$reassesment['ipid']]['last_name'] . ",";
					}
					if(!empty($patientsFinal[$reassesment['ipid']]['first_name']))
					{
						$fullName .= $patientsFinal[$reassesment['ipid']]['first_name'];
					}
					$eventsArray[] = array(
							'id' => $reassesment['id'],
							'title' => "Re-Assessment\n " . $fullName . "",
							'start' => date("Y-m-d", strtotime($reassesment['reeval'])),
							'editable' => false,
							'color' => "#008080", //Teal
							'textColor' => '#000',
							'eventType' => "13", //re-assesment
							'url' => 'patientform/reassessment?id=' . Pms_Uuid::encrypt($patientsFinal[$reassesment['ipid']]['patient_id'])
					);
				}
			}
			// get assesment completed -> reeval date-> to show Re-assesment END
			//		get doctor/nurse visits

			$visits_form_course = Doctrine_Query::create()
				->select("id,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where("create_user= ?", $userid)
				->andWhere(' course_date BETWEEN "' . date('Y-m-d', $_REQUEST['start']) . '" AND "' . date('Y-m-d', $_REQUEST['end']) . '"')
				->andWhere('course_type = AES_ENCRYPT("F", "' . Zend_Registry::get('salt') . '")')
				->andWhere('tabname = AES_ENCRYPT("kvno_doctor_form", "' . Zend_Registry::get('salt') . '") OR tabname = AES_ENCRYPT("kvno_nurse_form", "' . Zend_Registry::get('salt') . '") OR tabname = AES_ENCRYPT("contact_form", "' . Zend_Registry::get('salt') . '") OR tabname = AES_ENCRYPT("visit_koordination_form", "' . Zend_Registry::get('salt') . '")   ')
// 				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
// 				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form' OR AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form' OR AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form' OR AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'visit_koordination_form'")
				->andWhereIn('ipid', $client_ipids_arr)
				->andWhere('source_ipid = ""')
				->orderBy('course_date ASC');
// 			echo $visits_form_course->getSqlQuery();exit;
			$visits_course = $visits_form_course->fetchArray();


			$deleted_visits['kvno_doctor_form'][] = '999999999';
			$deleted_visits['kvno_nurse_form'][] = '999999999';
			$deleted_visits['contact_form'][] = '999999999';
			$deleted_visits['visit_koordination_form'][] = '999999999';
			foreach($visits_course as $visit)
			{
				$deleted_visits[$visit['tabname']][] = $visit['recordid'];
			}


// dd($deleted_visits);


			$kvnodoc = new KvnoDoctor();
			$kvnodoctorarray = $kvnodoc->getAllDoctorVisitsInPeriod($userid, $_REQUEST['start'], $_REQUEST['end'], $deleted_visits['kvno_doctor_form'], $client_ipids_arr);

			$kvnonurse = new KvnoNurse();
			$kvnonursearray = $kvnonurse->getAllNurseVisitsInPeriod($userid, $_REQUEST['start'], $_REQUEST['end'], $deleted_visits['kvno_nurse_form'], $client_ipids_arr);

			//08.05.2013 added contactforms to calendar as normal visit
			$contactforms = new ContactForms();
			$contactforms_array = $contactforms->get_calendar_contact_form($userid, $_REQUEST['start'], $_REQUEST['end'], $deleted_visits['contact_form'], $client_ipids_arr);

			//20.05.2013 added koordination visits
			$koord_visits = new VisitKoordination();
			$koord_visits_array = $koord_visits->getAllKoordinationVisitsInPeriod($userid, $_REQUEST['start'], $_REQUEST['end'], $deleted_visits['visit_koordination_form'], $client_ipids_arr);

			//get doctors visits ipids
			$ipidsdoc = "'999999999'";
			$comma = ",";
			foreach($kvnodoctorarray as $dvisit)
			{
				$ipidsdoc .= $comma . "'" . $dvisit['ipid'] . "'";
				$comma = ",";
			}


			//get nurse visits ipids
			$ipidsnurse = "'999999999'";
			$comma = ",";
			foreach($kvnonursearray as $nvisit)
			{
				$ipidsnurse .= $comma . "'" . $nvisit['ipid'] . "'";
				$comma = ",";
			}


			//get CF ipids
			$cf_ipids[] = '999999999';
			foreach($contactforms_array as $k_cf => $v_cf)
			{
				$cf_ipids[] = $v_cf['ipid'];
			}

			//get koord visit ipids
			$koord_ipids[] = '999999999';
			foreach($koord_visits_array as $k_cf => $v_cf)
			{
				$koord_ipids[] = $v_cf['ipid'];
			}


			$patientsipidepid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where('ipid IN (' . $ipidsdoc . ')')
				->orWhere('ipid IN (' . $ipidsnurse . ')')
				->orWhereIn('ipid', $cf_ipids)
				->orWhereIn('ipid', $koord_ipids);
			$patientsepids = $patientsipidepid->fetchArray();
			foreach($patientsepids as $pat)
			{
				$patientsEpidsFinal[$pat['ipid']] = $pat;
				$patientVisitsIpids[] = $pat['ipid'];
			}
			$patientVisitsIpids[] = '999999999';

			$vPatientDetails = Doctrine_Query::create()
				->select('*, ' . "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name")
				->from('PatientMaster p')
				->where('p.isdelete = 0')
				->andWhereIn('p.ipid', $patientVisitsIpids);

			$vPatientsArray = $vPatientDetails->fetchArray();
			foreach($vPatientsArray as $patient)
			{
				$visitPatientsArr[$patient['ipid']] = $patient;
			}


			//		DOCTOR VISITS
			foreach($kvnodoctorarray as $k_doc => $v_doc)
			{
				$visits[$k_doc]['id'] = $v_doc['id'];
				$visits[$k_doc]['start'] = $v_doc['start_date'];
				$visits[$k_doc]['end'] = $v_doc['end_date'];
				$visits[$k_doc]['create_user'] = $v_doc['create_user'];
			}

			foreach($kvnodoctorarray as $k_doc => $docvisit)
			{

				$r1start = strtotime($docvisit['start_date']);
				$r1end = strtotime($docvisit['end_date']);
				$u1 = $docvisit['create_user'];


				foreach($visits as $key_vizit => $value_vizit)
				{
					if($value_vizit['id'] != $docvisit['id'])
					{
						$r2start = strtotime($value_vizit['start']);
						$r2end = strtotime($value_vizit['end']);
						$u2 = $value_vizit['create_user'];


						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
						{
							//overlapped!
							$intersected[] = $value_vizit['id'];
							$intersected[] = $docvisit['id'];
						}
					}
				}

				$intersected_doc_viz = array_unique($intersected);

				if(in_array($docvisit['id'], $intersected_doc_viz))
				{
					$is_inters = 'intersected_event';
				}
				else
				{
					$is_inters = '';
				}

				$eventsArray[] = array(
						'id' => $docvisit['id'],
						'title' => "Besuch Arzt \n " . $visitPatientsArr[$docvisit['ipid']]['last_name'] . ", " . $visitPatientsArr[$docvisit['ipid']]['first_name'] . "",
						'start' => $docvisit['start_date'],
						'end' => $docvisit['end_date'],
						'allDay' => false,
						'resizable' => false,
						'color' => 'gold',
						'textColor' => 'black',
						'ipid' => $docvisit['ipid'],
						'createDate' => $docvisit['create_date'],
						'eventType' => '1', //doctor vizit
						'className' => $is_inters
				);
			}

			//KOORD ViSITS
			foreach($koord_visits_array as $k_doc => $v_doc)
			{
				$visits_k[$k_doc]['id'] = $v_doc['id'];
				$visits_k[$k_doc]['start'] = $v_doc['start_date'];
				$visits_k[$k_doc]['end'] = $v_doc['end_date'];
				$visits_k[$k_doc]['create_user'] = $v_doc['create_user'];
			}

			foreach($koord_visits_array as $k_doc => $docvisit)
			{

				$r1start = strtotime($docvisit['start_date']);
				$r1end = strtotime($docvisit['end_date']);
				$u1 = $docvisit['create_user'];


				foreach($visits_k as $key_vizit => $value_vizit)
				{
					if($value_vizit['id'] != $docvisit['id'])
					{
						$r2start = strtotime($value_vizit['start']);
						$r2end = strtotime($value_vizit['end']);
						$u2 = $value_vizit['create_user'];


						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
						{
							//overlapped!
							$intersected[] = $value_vizit['id'];
							$intersected[] = $docvisit['id'];
						}
					}
				}

				$intersected_doc_viz = array_unique($intersected);

				if(in_array($docvisit['id'], $intersected_doc_viz))
				{
					$is_inters = 'intersected_event';
				}
				else
				{
					$is_inters = '';
				}

				$eventsArray[] = array(
						'id' => $docvisit['id'],
						'title' => "Besuch Koordination \n " . $visitPatientsArr[$docvisit['ipid']]['last_name'] . ", " . $visitPatientsArr[$docvisit['ipid']]['first_name'] . "",
						'start' => $docvisit['start_date'],
						'end' => $docvisit['end_date'],
						'allDay' => false,
						'resizable' => false,
						'color' => 'yellow',
						'textColor' => 'black',
						'ipid' => $docvisit['ipid'],
						'createDate' => $docvisit['create_date'],
						'eventType' => '17', //doctor vizit
						'className' => $is_inters
				);
			}


			//NURSE VISITS
			foreach($kvnonursearray as $k_nurse => $v_nurse)
			{
				$visits_n[$k_nurse]['id'] = $v_nurse['id'];
				$visits_n[$k_nurse]['start'] = $v_nurse['start_date'];
				$visits_n[$k_nurse]['end'] = $v_nurse['end_date'];
				$visits_n[$k_nurse]['create_user'] = $v_nurse['create_user'];
			}

			foreach($kvnonursearray as $nursevizit)
			{

				$r1start = strtotime($nursevizit['start_date']);
				$r1end = strtotime($nursevizit['end_date']);
				$u1 = $nursevizit['create_user'];


				foreach($visits_n as $key_vizit_n => $value_vizit_n)
				{
					if($value_vizit_n['id'] != $nursevizit['id'])
					{
						$r2start = strtotime($value_vizit_n['start']);
						$r2end = strtotime($value_vizit_n['end']);
						$u2 = $value_vizit_n['create_user'];


						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
						{
							//overlapped!
							$intersected_n[] = $value_vizit_n['id'];
							$intersected_n[] = $nursevizit['id'];
						}
					}
				}

				$intersected_n_viz = array_unique($intersected_n);

				if(in_array($nursevizit['id'], $intersected_n_viz))
				{
					$is_inters_n = 'intersected_event';
				}
				else
				{
					$is_inters_n = '';
				}

				$eventsArray[] = array(
						'id' => $nursevizit['id'],
						'title' => "Besuch Pflege \n" . $visitPatientsArr[$nursevizit['ipid']]['last_name'] . ", " . $visitPatientsArr[$nursevizit['ipid']]['first_name'] . "",
						'start' => $nursevizit['start_date'],
						'end' => $nursevizit['end_date'],
						'allDay' => false,
						'resizable' => false,
						'ipid' => $nursevizit['ipid'],
						'createDate' => $nursevizit['create_date'],
						'eventType' => '2', //nurse vizit
						'className' => $is_inters_n
				);
			}

			//CONTACT FORM VISITS

			foreach($contactforms_array as $k_cf => $v_cf)
			{
				$visits_cf[$k_cf]['id'] = $v_cf['id'];
				$visits_cf[$k_cf]['start'] = $v_cf['start_date'];
				$visits_cf[$k_cf]['end'] = $v_cf['end_date'];
				$visits_cf[$k_cf]['create_user'] = $v_cf['create_user'];
			}

			foreach($contactforms_array as $k_contactforms => $v_contactforms)
			{
				$r1start = strtotime($v_contactforms['start_date']);
				$r1end = strtotime($v_contactforms['end_date']);
				$u1 = $v_contactforms['create_user'];

				foreach($visits_cf as $key_cf_vizit => $value_cf_vizit)
				{
					if($value_cf_vizit['id'] != $v_contactforms['id'])
					{
						$r2start = strtotime($value_cf_vizit['start']);
						$r2end = strtotime($value_cf_vizit['end']);
						$u2 = $value_cf_vizit['create_user'];


						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
						{
							//overlapped!
							$intersected[] = $v_contactforms['id'];
							$intersected[] = $value_cf_vizit['id'];
						}
					}
				}

				$intersected_cf_viz = array_unique($intersected);

				if(in_array($v_contactforms['id'], $intersected_cf_viz))
				{
					$is_inters = 'intersected_event';
				}
				else
				{
					$is_inters = '';
				}

				$eventsArray[] = array(
						'id' => $v_contactforms['id'],
						'title' => "Besuch " . $v_contactforms['form_type_name'] . " \n " . $visitPatientsArr[$v_contactforms['ipid']]['last_name'] . ", " . $visitPatientsArr[$v_contactforms['ipid']]['first_name'] . "",
						'start' => $v_contactforms['start_date'],
						'end' => $v_contactforms['end_date'],
						'allDay' => false,
						'resizable' => false,
						'color' => 'gold',
						'textColor' => 'black',
						'ipid' => $v_contactforms['ipid'],
						'createDate' => $v_contactforms['create_date'],
						'eventType' => '13', //doctor vizit
						'className' => $is_inters
				);
			}


			$user_group = new Usergroup();
			$master_groups = array("6"); //Koordination master group
			$users_groups = $user_group->getUserGroups($master_groups);
			$users_groups_ids[] = '9999999999';
			foreach($users_groups as $group_details)
			{
				$users_groups_ids[] = $group_details['id'];
			}

			//get client coord groups
			$usergroup = new Usergroup();
			$MasterGroups = array("6"); // Koordinator
			$coord_groups[] = '999999999';
			$usersgroups = $usergroup->getUserGroups($MasterGroups);
			if(count($usersgroups) > 0)
			{
				foreach($usersgroups as $group)
				{
					$coord_groups[] = $group['id'];
				}
			}


			$user = new User();
			$client_users = $user->getUserByClientid($clientid, 0, true);

			$users2groups[] = '999999999';
			foreach($client_users as $k_user => $v_user)
			{
				$todo_users[$v_user['id']] = $v_user;
				$users2groups[$v_user['id']] = $v_user['groupid'];
				$groups2users[$v_user['groupid']][] = $v_user['id'];
			}


			$current_user_group_asignees = $groups2users[$groupid];
			$current_user_group_asignees[] = '999999999';
			//		end doctor/nurse visits
			//		get todos user id
			$all_client_patients_q = Doctrine_Query::create()
			->select('pm.ipid,ep.epid')
			->from('PatientMaster pm')
			->where('pm.isdelete = 0')
			->leftJoin('pm.EpidIpidMapping ep')
			->andWhere('ep.clientid=' . $logininfo->clientid)
			->andWhere('ep.ipid=pm.ipid');
			$all_clipids = $all_client_patients_q->fetchArray();
			
			$all_client_ipids_arr[] = "999999999";
			foreach($all_clipids as $clipi)
			{
			    $all_client_ipids_arr[] = $clipi['ipid'];
			}
			
			
			$todo = Doctrine_Query::create()
				->select("*")
				->from('ToDos')
				->where('client_id="' . $clientid . '"')
				->andWhere('isdelete="0"')
				->andWhere('iscompleted="0"')
			    ->andWhereIn('ipid',$all_client_ipids_arr)
// 				->andWhere('create_date BETWEEN "' . date("Y-m-d H:i:s", $_REQUEST['start']) . '" AND "' . date("Y-m-d H:i:s", $_REQUEST['end']) . '"')
				->andWhere('create_date BETWEEN ? AND ?' , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])))
				->andWhere('triggered_by != "system"');
			if($user_type != 'SA')
			{
				if(!in_array($groupid, $coord_groups))
				{
					$todo->andWhere('triggered_by !="system_medipumps"');
				}

				if($groupid > 0)
				{
					$sql_group = ' OR group_id = "' . $groupid . '"';
				}
				$todo->andWhere('user_id IN("' . implode(', ', $current_user_group_asignees) . '") ' . $sql_group . '');
			}
			$todoarray = $todo->fetchArray();
			
			
			$todoipidstr = "'999999999'";
			$comma = ",";
			foreach($todoarray as $todo)
			{
				$todoipidstr .= $comma . "'" . $todo['ipid'] . "'";
				$comma = ",";
			}

			$patientipidepid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where('ipid IN (' . $todoipidstr . ')');

			$patientepids = $patientipidepid->fetchArray();

			foreach($patientepids as $patient)
			{
				$patdetails[$patient['ipid']] = $patient;
			}

			foreach($todoarray as $todo)
			{

				$patient_name = $patients_details[$todo['ipid']]['last_name'] . ', ' . $patients_details[$todo['ipid']]['first_name'] . ' ';

				$to_do_prefix = "";
				if($todo['triggered_by'] == 'system_medipumps')
				{
					$to_do_prefix = "TO DO: \n" . strtoupper($patdetails[$todo['ipid']]['epid']) . "\n";
				}
				else if($todo['triggered_by'] == "newreceipt_1" || $todo['triggered_by'] == "newreceipt_2")
				{
					preg_match('/<a href="([^"]*)">/', $todo['todo'], $matches);
					$todo_url = $matches[1];
					$todo['todo'] = strip_tags($todo['todo']);
				}
				else if($todo['triggered_by'] == "teammeeting_completed" )
				{
				    
				    $to_do_prefix = "TO DO: \n Teambesprechung:\n" . $patient_name . "\n";
				    $todo['todo'] = strip_tags($todo['todo']);
				}
				else
				{
					$to_do_prefix = "TO DO: \n" . $patient_name . "\n";
				}

				if($todo['triggered_by'] == "newreceipt_1" || $todo['triggered_by'] == "newreceipt_2")
				{
				    
					$eventsArray[] = array(
						'id' => $todo['id'],
						'title' => $todo['todo'],
						'titlePrefix' => $to_do_prefix,
						'start' => date("Y-m-d", strtotime($todo['until_date'])),
						'color' => 'orange',
						'textColor' => 'black',
						'allDay' => true,
						'editable'=>false,
						'resizable' => false,
						'url' =>$todo_url,
						'event_source' => $todo['triggered_by'], 
						'eventType' => '3' //todo
					);
				}
				else
				{
				    
				    
				    $todo['todo'] = str_replace(array('<b>','</b>'),array('',''), $todo['todo']);
				    
				    
					$eventsArray[] = array(
						'id' => $todo['id'],
						'title' => $todo['todo'],
						'titlePrefix' => $to_do_prefix,
						'start' => date("Y-m-d", strtotime($todo['until_date'])),
						'color' => 'orange',
						'textColor' => 'black',
						'allDay' => true,
						'resizable' => false,
						'event_source' => 'todo',
						'eventType' => '3' //todo
					);
				}
			}
			//		end get todos by user id
			//		get duty roster
			$dutyset = Doctrine_Query::create()
				->select('*')
				->from('Roster')
// 				->where("duty_date BETWEEN '" . date("Y-m-d H:i:s", $_REQUEST['start']) . "' AND '" . date("Y-m-d H:i:s", $_REQUEST['end']) . "'")
				->where("duty_date BETWEEN ? AND ?" , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])) )
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('userid = "' . $userid . '"')
				->andWhere('isdelete = "0"');
			$dutyarray = $dutyset->fetchArray();

			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);

			foreach($dutyarray as $duty)
			{
				$event_prefix = $client_shifts[$duty['shift']]['name'];
				$eventsArray[] = array(
						'id' => $duty['id'],
						'title' => $event_prefix,
						'start' => date("Y-m-d", strtotime($duty['duty_date'])),
						'color' => 'red',
						'textColor' => 'black',
						'allDay' => true,
						'resizable' => false,
						'eventType' => '4' //duty rooster
				);
			}

			
			
		
			$events_DoctorCustomEvents = array();
			
		        			
			$docCustomEv = new DoctorCustomEvents();
			$customEvents = $docCustomEv->getDoctorCustomEvents($userid, $clientid);



			$aUsers = new User();
			$allUsers = $aUsers->getUserByClientid($clientid);
			foreach($allUsers as $kUsr => $vUsr)
			{
				$finalUsersArr[$vUsr['id']] = $vUsr;
			}
			
			
			
			
			foreach($customEvents as $cEvent)
			{
			    
			    if ($exclude_DoctorCustomEvents === true && ! empty($cEvent['ipid'])) {
			        continue;    
			    }
			    
			    
				if(($cEvent['viewForAll'] == 1 && $cEvent['clientid'] == $clientid) || ($cEvent['viewForAll'] == 0 && $cEvent['userid'] == $userid))
				{
					if($cEvent['allDay'] == 1)
					{
						$allDay = true;
					}
					else
					{
						$allDay = false;
					}

					if($userid != $cEvent['userid'])
					{
						$titlePrefix = "Termin von Benutzer " . $finalUsersArr[$cEvent['userid']]['last_name'] . ", " . $finalUsersArr[$cEvent['userid']]['first_name'] . "\n";
						$title = $cEvent['eventTitle'];
					}
					else
					{
						$titlePrefix = "";
						$title = $cEvent['eventTitle'];
					}
					if($cEvent['dayplan_inform'] == 1)
					{
						$dayplan_inform = true;
					}
					else
					{
						$dayplan_inform = false;
					}

					$events_DoctorCustomEvents[] =
					$eventsArray[] = array(
							'id' => $cEvent['id'],
							'titlePrefix' => $titlePrefix,
							'title' => $title, 
					    //. "\n". print_r($cEvent, true),
							'start' => date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
							'end' => date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
							'color' => '#33CCFF',
							'textColor' => 'black',
							'allDay' => $allDay,
							'viewForAll' => $cEvent['viewForAll'],
							'resizable' => true,
							'eventType' => $cEvent['eventType'], //custom event 10-11
							'dayplan_inform' => $dayplan_inform,
					    
					    'comments' => $cEvent['comments'], // this are the original so user can edit
					    'comments_qtip' => nl2br($this->view->escape($cEvent['comments'])), // this are displayed as qtip
					    					
					);
				}
			}

		
			
			
			$wlprevileges = new Modules();
			$wl = $wlprevileges->checkModulePrivileges("51", $logininfo->clientid);


			//get patients with activ hospiz location // ISPC-2062
			
			//ISPC-2612 Ancuta 27.06.2020 Locx
			/*
			$fdoc = Doctrine_Query::create()
			->select("id")
			->from('Locations')
			->where("location_type=2")
			//->andWhere('isdelete=0') // ISPC-2612 Ancuta 27.06.2020 Locx
			->andWhere("client_id=?", $logininfo->clientid)
			->orderBy('location ASC');
			$lochospizarr = $fdoc->fetchArray();
			 */
			$disallowed_location_types = array('2');
			$loc_obj = new Locations();
			$lochospizarr = $loc_obj->get_locationByClientAndTypes($clientid,$disallowed_location_types);
			// --
			
			
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
				//->andWhereIn('ipid', $ipidarrays) //all ipids
				->andWhereIn('location_id', $locid_hospiz)
				->andWhere("valid_till='0000-00-00 00:00:00'")
				->orderBy('id DESC');
				$patloc_hospizarr = $patlocs->fetchArray();
			
				foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
				{
					$ipids_hospiz[] = $v_pathospiz['ipid'];
				}
			}
			
			
			//get private patients from a ipid list

			if($wl)
			{//+6week continuous
				$cust = Doctrine_Query::create()
					->select("id, onlyAssignedPatients")
					->from('User')
					->where('id = ' . $logininfo->userid);
				$userSettings = $cust->fetchArray();


				if($userSettings[0]['onlyAssignedPatients'] == 1)
				{ //select only assigned patients
					$q = Doctrine_Query::create();
					$q->select("*")
						->from('PatientQpaMapping')
						->where("clientid='" . $logininfo->clientid . "'")
						->andWhere("userid='" . $logininfo->userid . "'");
					$qarray = $q->fetchArray();
					$assignedPatients = $qarray;
					$comma = ",";
					$epidval = "'999999999'";
					foreach($assignedPatients as $aPatient)
					{
						$epidval .= $comma . "'" . $aPatient['epid'] . "'";
						$comma = ",";
					}
				}

				$q = "";
				$q = Doctrine_Query::create();
				$q->select("*")
					->from('EpidIpidMapping')
					->where("clientid='" . $logininfo->clientid . "'");
				if($userSettings[0]['onlyAssignedPatients'] == 1)
				{
					$q->andWhere("epid IN (" . $epidval . ")");
				}

				$ipidarray = $q->fetchArray();



				$ipidarrays[] = '99999999';
				foreach($ipidarray as $key => $val)
				{
					$ipidarrays[] = $val['ipid'];
				}

				//exclude the private patients
				$health = Doctrine_Query::create();
				$health->select("*")
					->from('PatientHealthInsurance')
					->whereIn('ipid', $ipidarrays)
					->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();

				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}
				
				$comma = ",";
				$ipidval = "'0'";
				$ipidarr[] = '99999999';
				foreach($ipidarrays as $k_ipid => $v_ipid)
				{
					if(!in_array($v_ipid, $privat_patient) && !in_array($v_ipid, $ipids_hospiz))
					{
						$ipidval .= $comma . "'" . $v_ipid . "'";
						$ipidarr[] = $v_ipid;
						$comma = ",";
					}
				}


				//get 6weeks patients recheck
				$start_calendar = date('Y-m-d', strtotime('-1 day', $_REQUEST['start']));
				$end_calendar = date('Y-m-d', strtotime('+1 day', $_REQUEST['end']));


				$sql = '';
				$sql .= "*,";
				$sql .= "e.epid,";

				// if super admin check if patient is visible or not
				$hidemagic = "XXXXXXXXXX";


				if($logininfo->usertype == 'SA')
				{
					$sql .= "IF(p.isadminvisible = '1',CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as d_first_name, ";
					$sql .= "IF(p.isadminvisible = '1',CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as d_last_name, ";
				}
				else
				{
					$sql .= "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1) as d_first_name, ";
					$sql .= "CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as d_last_name, ";
				}

				$sql .= "DATEDIFF('" . $start_calendar . "', p.admission_date) as month_start_days,";
				$sql .= "DATEDIFF('" . $end_calendar . "', p.admission_date) as month_end_days,";
				$sql .= "DATEDIFF('" . $start_calendar . "', p.admission_date)/56 as month_start,";
				$sql .= "DATEDIFF('" . $end_calendar . "', p.admission_date)/56 as month_end,";
				$sql .= "ADDDATE( p.admission_date, (ceil( DATEDIFF( '" . $start_calendar . "', p.admission_date ) /56 ) *56 )) AS event_day,";
				$sql .= "(floor(DATEDIFF('" . $end_calendar . "', p.admission_date)/56) - floor(DATEDIFF('" . $start_calendar . "', p.admission_date)/56)) as have_value";

				$patientq = Doctrine_Query::create();
				$patientq->select($sql)
					->from('PatientMaster p')
					->where('p.isdelete = 0')
					->andWhere('p.isarchived = 0')
					->andWhere('p.isstandby = 0')
					->andWhere('p.isstandbydelete = 0')
					->andWhereIn('ipid', $ipidarr);
				$patientq->leftJoin("p.EpidIpidMapping e");
				$patientq->andWhere('e.clientid = ' . $logininfo->clientid);
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
					$patient_name = "";
					if(!empty($v_patient['d_last_name']))
					{
						$patient_name .= $v_patient['d_last_name'] . ",";
					}

					if(!empty($v_patient['d_first_name']))
					{
						$patient_name .= $v_patient['d_first_name'];
					}

					if($v_patient['isdischarged'] == '1')
					{
						//get last discharge date
						$dis_arr = end($patientInfo[$k_ipid]['dischargeDates']);

						//					last_allowed_date = discharge_date
						$v_patient['last_allowed_date'] = date('Y-m-d', strtotime($dis_arr['date']));
					}
					else
					{
						//					last_allowed_date = end of view in calendar
						$v_patient['last_allowed_date'] = $end_calendar;
					}



					if(
						strtotime($v_patient['event_day']) <= strtotime($v_patient['last_allowed_date']) && strtotime($v_patient['admission_date']) < strtotime($v_patient['event_day']))
					{

						$eventsArray[] = array(
								'id' => $k_ipid,
								'title' => "Prüfung Anlage 4 \n " . $patient_name,
								'start' => date("Y-m-d", strtotime($v_patient['event_day'])),
								'editable' => false,
								'color' => "red",
								'textColor' => 'black',
								'eventType' => "6", //anlage4wl recheck
								'url' => 'patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_patient['id'])
						);
					}
				}
			}


			if($wl)
			{//+4week vollversorgung
				$cust = Doctrine_Query::create()
					->select("id, onlyAssignedPatients")
					->from('User')
					->where('id = ' . $logininfo->userid);
				$userSettings = $cust->fetchArray();

				if($userSettings[0]['onlyAssignedPatients'] == 1)
				{ //select only assigned patients
					$q = Doctrine_Query::create();
					$q->select("*")
						->from('PatientQpaMapping')
						->where("clientid='" . $logininfo->clientid . "'")
						->andWhere("userid='" . $logininfo->userid . "'");
					$qarray = $q->fetchArray();
					$assignedPatients = $qarray;
					$comma = ",";
					$epidval = "'999999999'";
					foreach($assignedPatients as $aPatient)
					{
						$epidval .= $comma . "'" . $aPatient['epid'] . "'";
						$comma = ",";
					}
				}

				$q = "";
				$q = Doctrine_Query::create();
				$q->select("*")
					->from('EpidIpidMapping')
					->where("clientid='" . $logininfo->clientid . "'");
				if($userSettings[0]['onlyAssignedPatients'] == 1)
				{
					$q->andWhere("epid IN (" . $epidval . ")");
				}

				$ipidarray = $q->fetchArray();



				$ipidarrays[] = '99999999';
				foreach($ipidarray as $key => $val)
				{
					$ipidarrays[] = $val['ipid'];
				}

				//exclude the private patients
				$health = Doctrine_Query::create();
				$health->select("*")
					->from('PatientHealthInsurance')
					->whereIn('ipid', $ipidarrays)
					->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();

				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}

				$comma = ",";
				$ipidval = "'0'";
				$ipidarr[] = '99999999';
				foreach($ipidarrays as $k_ipid => $v_ipid)
				{
					if(!in_array($v_ipid, $privat_patient) && !in_array($v_ipid, $ipids_hospiz))
					{
						$ipidval .= $comma . "'" . $v_ipid . "'";
						$ipidarr[] = $v_ipid;
						$comma = ",";
					}
				}
				/* print_r($ipidarr);exit; */

				//get hospiz location

				//ISPC-2612 Ancuta 27.06.2020 Locx
				/*
				$fdoc = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
					->from('Locations')
					->where("location_type=2")
					->andWhere('isdelete=0')
					->orderBy('location ASC');
				$lochospizarr = $fdoc->fetchArray();
				 */
				$disallowed_location_types = array('2');
				$loc_obj = new Locations();
				$lochospizarr = $loc_obj->get_locationByClientAndTypes($clientid,$disallowed_location_types);
				// --
				
				$locid_hospiz[] = '999999999';

				foreach($lochospizarr as $k_hospiz => $v_hospiz)
				{
					$locid_hospiz[] = $v_hospiz['id'];
				}

				//get patient with location active Hospiz
				$patlocs = Doctrine_Query::create()
					->select('location_id,ipid')
					->from('PatientLocation')
					->where('isdelete="0"')
					->andWhereIn('ipid', $ipidarrays)
					->andWhereIn('location_id', $locid_hospiz)
					->andWhere("valid_till='0000-00-00 00:00:00'")
					->orderBy('id DESC');

				$patloc_hospizarr = $patlocs->fetchArray();

				$ipids_hospiz[] = '999999999';
				foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
				{
					$ipids_hospiz[] = $v_pathospiz['ipid'];
				}
				/* print_r($ipids_hospiz);exit; */
				$comma = ",";
				$ipidval = "'0'";
				$ipidarr[] = '99999999';
				foreach($ipidarr as $k_ipidf => $v_ipidf)
				{
					if(!in_array($v_ipidf, $ipids_hospiz))
					{
						$ipidval .= $comma . "'" . $v_ipidf . "'";
						$ipidsfinalarray[] = $v_ipidf;
						$comma = ",";
					}
				}
				/* print_r($ipidsfinalarray);exit; */

				//======================================================
				$start_calendar = date('Y-m-d', strtotime('-1 day', $_REQUEST['start']));
				$end_calendar = date('Y-m-d', strtotime('+1 day', $_REQUEST['end']));


				$sql = '';
				$sql .= "*,";
				$sql .= "e.epid,";

				// if super admin check if patient is visible or not
				$hidemagic = "XXXXXXXXXX";


				if($logininfo->usertype == 'SA')
				{
					$sql .= "IF(p.isadminvisible = '1',CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as d_first_name, ";
					$sql .= "IF(p.isadminvisible = '1',CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as d_last_name, ";
				}
				else
				{
					$sql .= "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1) as d_first_name, ";
					$sql .= "CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as d_last_name, ";
				}

				$sql .= "DATEDIFF('" . $start_calendar . "', p.vollversorgung_date) as month_start_days,";
				$sql .= "DATEDIFF('" . $end_calendar . "', p.vollversorgung_date) as month_end_days,";
				$sql .= "DATEDIFF('" . $start_calendar . "', p.vollversorgung_date)/28 as month_start,";
				$sql .= "DATEDIFF('" . $end_calendar . "', p.vollversorgung_date)/28 as month_end,";
				$sql .= "ADDDATE( p.vollversorgung_date, (ceil( DATEDIFF( '" . $start_calendar . "', p.vollversorgung_date ) /28 ) *28 )) AS event_day,";
				$sql .= "(floor(DATEDIFF('" . $end_calendar . "', p.vollversorgung_date)/28) - floor(DATEDIFF('" . $start_calendar . "', p.vollversorgung_date)/28)) as have_value";

				$patientqry = Doctrine_Query::create();
				$patientqry->select($sql)
					->from('PatientMaster p')
					->where('p.isdelete = 0')
					->andWhere('p.isarchived = 0')
					->andWhere('p.isstandby = 0')
					->andWhere('p.isstandbydelete = 0')
					->andWhere('p.vollversorgung = 1')
					->andWhereIn('ipid', $ipidsfinalarray);
				$patientqry->leftJoin("p.EpidIpidMapping e");
				$patientqry->andWhere('e.clientid = ' . $logininfo->clientid);
				$patientqry->having('have_value > 0');



				$patients_Array = $patientqry->fetchArray();

				$ipids_final = array();
				$patients_final = array();
				$ipids_final[] = '999999999';
				foreach($patients_Array as $patientSel)
				{
					$ipids_final[] = $patientSel['ipid'];
					$patients_final[$patientSel['ipid']] = $patientSel;
				}
				$pm = new PatientMaster();
				$patientInfo = $pm->getTreatedDaysRealMultiple($ipids_final, false);
				foreach($patients_final as $k_ipid => $v_patient)
				{
					$patient_name = "";
					if(!empty($v_patient['d_last_name']))
					{
						$patient_name .= $v_patient['d_last_name'] . ",";
					}

					if(!empty($v_patient['d_first_name']))
					{
						$patient_name .= $v_patient['d_first_name'];
					}

					if($v_patient['isdischarged'] == '1')
					{
						//get last discharge date
						$dis_arr = end($patientInfo[$k_ipid]['dischargeDates']);

						//					last_allowed_date = discharge_date
						$v_patient['last_allowed_date'] = date('Y-m-d', strtotime($dis_arr['date']));
					}
					else
					{
						//					last_allowed_date = end of view in calendar
						$v_patient['last_allowed_date'] = $end_calendar;
					}


					if(strtotime($v_patient['event_day']) <= strtotime($v_patient['last_allowed_date']) &&
						strtotime($v_patient['admission_date']) < strtotime($v_patient['event_day']))
					{

						$eventsArray[] = array(
								'id' => $k_ipid,
								'title' => "Anlage 4a WL *  \n " . $patient_name,
								'start' => date("Y-m-d", strtotime($v_patient['event_day'])),
								'editable' => false,
								'color' => "#999999",
								'textColor' => 'black',
								'eventType' => "6", //anlage4wl recheck
								'url' => 'patient/anlage4awl?id=' . Pms_Uuid::encrypt($v_patient['id'])
						);
					}
				}
			}

			//start user calendar verlauf entries
			$shortcuts = array('U', 'XT'); // remove "Koordination" entries in Calendar TAB "Benutzer"


			$shortcuts_arr[] = '999999999';
			foreach($shortcuts as $shortcut)
			{
				$shortcuts_arr[] = Pms_CommonData::aesEncrypt($shortcut);
			}

			$q = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
				AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->whereIn("ipid", $client_ipids_arr)
				->andWhereIn('course_type', $shortcuts_arr)
				->andWhere('create_user = "' . $userid . '" or change_user="' . $userid . '"')
				->andWhere('source_ipid = ""')
				->andWhere('wrong="0"');
			$sel_verlauf_entries = $q->fetchArray();

			$p1_start = $_REQUEST['start'];
			$p1_end = $_REQUEST['end'];


			foreach($sel_verlauf_entries as $k_verlauf_entries => $v_verlauf_entries)
			{
				$arr_course = explode('|', $v_verlauf_entries['course_title']);
				if($v_verlauf_entries['course_type'] == "V" || $v_verlauf_entries['course_type'] == 'XT')
				{
					$course_date = $arr_course['2'];
				}
				else if($v_verlauf_entries['course_type'] == 'U')
				{
					$course_date = $arr_course['3'];
				}

				$p2_start = $p2_end = strtotime(date('Y-m-d', strtotime($course_date)));


				if(Pms_CommonData::isintersected($p1_start, $p1_end, $p2_start, $p2_end))
				{
					$patient_data = $patients_details[$v_verlauf_entries['ipid']]['last_name'] . ', ' . $patients_details[$v_verlauf_entries['ipid']]['first_name'];

					if($v_verlauf_entries['course_type'] == "V")
					{
						$title = "Koordination: \n" . $patient_data . "\n" . $arr_course[0] . "minuten";
						$event_id = '9';
						$back_color = $patient_course_settings['v_color'];
						$text_color = $patient_course_settings['v_text_color'];
						$event_date = $arr_course[2];
						$minutes = $arr_course[0];
					}
					else if($v_verlauf_entries['course_type'] == 'XT')
					{
						$title = "Telefon: \n" . $patient_data . "\n" . $arr_course[0] . "minuten";
						$event_id = '16';
						$back_color = $patient_course_settings['xt_color'];
						$text_color = $patient_course_settings['xt_text_color'];
						$event_date = $arr_course[2];
						$minutes = $arr_course[0];
					}
					else if($v_verlauf_entries['course_type'] == 'U')
					{
						$title = "Beratung: " . $patient_data . "\n" . $arr_course[1] . "minuten";
						$event_id = '15';
						$back_color = $patient_course_settings['u_color'];
						$text_color = $patient_course_settings['u_text_color'];
						$event_date = $arr_course[3];
						$minutes = $arr_course[1];
					}

					$eventsArray[] = array(
						'id' => $v_verlauf_entries['id'],
						'editable' => true,
						'resizable' => false,
						'title' => $title,
						'start' => date("Y-m-d H:i:s", strtotime($event_date)),
						//'color' => "#33CC66",
						//'textColor' => 'black',
						//ISPC - 2311
						'color' => $back_color,
						'textColor' => $text_color,
						'eventType' => $event_id, //koord verlauf entry
						'allDay' => false,
						'url' => ''
					);
				}
			}


			if ($returnArray === true) {
			    return $eventsArray;
			} else {
			    $this->_helper->getHelper('json')->sendJson($eventsArray);
			    exit; //for readability
			}
		}

		public function savedoctoreventsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			//		all events
			//		0.custom doctor event(edit)
			//		1.docvizit
			//		2.nursevizit
			//		3.todo
			//		4.dutyrooster
			//		5.assessment
			//		6.anlage4wl
			//		7.sapvfb3
			//		8.treatment??
			//		9.verlauf koordination
			//		10. doc->patient related (tremin)
			//		11. only doctor(notiz)
			//		12. hollydays (team)
			//		13. contactform

			$eventid = $_POST['eventId']; //existing = edit event  / empty = new event
			$eventTitle = $_POST['eventTitle']; //existing = edit event  / empty = new event
			$startDate = $_POST['startDate'];
			$endDate = $_POST['endDate'];
			$eventType = $_POST['eventType'];
			$allDayEvent = $_POST['allDay']; //1-true, 0-false
			$viewForAll = $_POST['viewForAll']; //1-visible for all users
			$patientSelected = $_POST['patientSelected'];
			$cDate = $_POST['cDate'];
			$dayplan_inform = (int)$_POST['dayplan_inform'];
			
			$comments = $_POST['comments']; //ISPC-2175
			
			switch($eventType):
				case "1": //docvizit
					if($eventid > 0)
					{
						$startDateArray = explode(" ", $startDate);
						$endDateArray = explode(" ", $endDate);

						$startTimeArray = explode(":", $startDateArray[1]);
						$endTimeArray = explode(":", $endDateArray[1]);

						$stamq = Doctrine_Core::getTable('KvnoDoctor')->findOneById($eventid);
						// get old values from db
						$old_start_date = $stamq->start_date;
						$old_end_date = $stamq->end_date;
						$old_vizit_date = $stamq->vizit_date;
						$old_kvno_begindate_h = date('H', strtotime($stamq->start_date));
						$old_kvno_begindate_m = date('i', strtotime($stamq->start_date));
						$old_kvno_enddate_h = date('H', strtotime($stamq->end_date));
						$old_kvno_enddate_m = date('i', strtotime($stamq->end_date));

						$stamq->kvno_begin_date_h = $startTimeArray[0];
						$stamq->kvno_begin_date_m = $startTimeArray[1];
						$stamq->kvno_end_date_h = $endTimeArray[0];
						$stamq->kvno_end_date_m = $endTimeArray[1];
						$stamq->vizit_date = $startDate;
						/* Visit START DATE and END DATE */
						$stamq->start_date = $startDate;
						$stamq->end_date = $endDate;
						/* ---------------------------- */

						$stamq->save();
						//verlauf
						$done_date = date('Y-m-d H:i:s', strtotime($startDate));
						$cust = new PatientCourse();
						$cust->ipid = $patientSelected; //TO DO: after moving the calendars to navi left get this via post *DONE
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");

						$cust->recordid = $eventid;
						$cust->user_id = $userid;
						$cust->save();

						if($startDate != $old_start_date || $endDate != $old_end_date)
						{
							$old_startdate = $old_kvno_begindate_h . ':' . $old_kvno_begindate_m . ' - ' . $old_kvno_enddate_h . ':' . $old_kvno_enddate_m . ' ' . date('d.m.Y', strtotime($old_start_date));
							$new_startDate = $startTimeArray[0] . ':' . $startTimeArray[1] . ' - ' . $endTimeArray[0] . ':' . $endTimeArray[1] . ' ' . date('d.m.Y', strtotime($startDate));

							//edited contact form date verlauf entry
							$cust = new PatientCourse();
							$cust->ipid = $patientSelected;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt("Besuchszeit: " . $old_startdate . ' -> ' . $new_startDate);
							$cust->user_id = $userid;

							$cust->done_name = Pms_CommonData::aesEncrypt("kvno_doctor_visit");
							$cust->done_id = $eventid;
							//print_r($patientSelected);
							$cust->save();
						}
					}
					break;
				case "2"://nursevizit
					if($eventid > 0)
					{
						$startDateArray = explode(" ", $startDate);
						$endDateArray = explode(" ", $endDate);

						$startTimeArray = explode(":", $startDateArray[1]);
						$endTimeArray = explode(":", $endDateArray[1]);

						$stamq = Doctrine_Core::getTable('KvnoNurse')->findOneById($eventid);
						// get old values from db
						$old_start_date = $stamq->start_date;
						$old_end_date = $stamq->end_date;
						$old_vizit_date = $stamq->vizit_date;
						$old_kvno_begindate_h = date('H', strtotime($stamq->start_date));
						$old_kvno_begindate_m = date('i', strtotime($stamq->start_date));
						$old_kvno_enddate_h = date('H', strtotime($stamq->end_date));
						$old_kvno_enddate_m = date('i', strtotime($stamq->end_date));

						$stamq->kvno_begin_date_h = $startTimeArray[0];
						$stamq->kvno_begin_date_m = $startTimeArray[1];
						$stamq->kvno_end_date_h = $endTimeArray[0];
						$stamq->kvno_end_date_m = $endTimeArray[1];
						$stamq->vizit_date = $startDate;
						/* Visit START DATE and END DATE */
						$stamq->start_date = $startDate;
						$stamq->end_date = $endDate;
						/* ---------------------------- */
						$stamq->save();

						$done_date = date('Y-m-d H:i:s', strtotime($startDate));
						$cust = new PatientCourse();
						$cust->ipid = $patientSelected; //TO DO: after moving the calendars to navi left get this via post
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");

						$cust->recordid = $eventid;
						$cust->user_id = $userid;
						$cust->save();

						if($startDate != $old_start_date || $endDate != $old_end_date)
						{
							$old_startdate = $old_kvno_begindate_h . ':' . $old_kvno_begindate_m . ' - ' . $old_kvno_enddate_h . ':' . $old_kvno_enddate_m . ' ' . date('d.m.Y', strtotime($old_start_date));
							$new_startDate = $startTimeArray[0] . ':' . $startTimeArray[1] . ' - ' . $endTimeArray[0] . ':' . $endTimeArray[1] . ' ' . date('d.m.Y', strtotime($startDate));

							//edited contact form date verlauf entry
							$cust = new PatientCourse();
							$cust->ipid = $patientSelected;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt("Besuchszeit: " . $old_startdate . ' -> ' . $new_startDate);
							$cust->user_id = $userid;

							$cust->done_name = Pms_CommonData::aesEncrypt("kvno_nurse_visit");
							$cust->done_id = $eventid;
							//print_r($patientSelected);
							$cust->save();
						}
					}
					break;
				case "3": //todo
					if($eventid > 0)
					{
						$todo = Doctrine::getTable('ToDos')->find($eventid);
						if(!empty($eventTitle))
						{
							$todo->todo = $eventTitle;
						}
						$todo->until_date = $startDate;
						$todo->save();
					}
					break;
				case "4": // dutyrooster
					if($eventid > 0)
					{
						$roster = Doctrine::getTable('Roster')->find($eventid);
						$roster->duty_date = $startDate;
						$roster->save();
					}

					break;
				case "9": // Koord Verlauf
					if($eventid > 0)
					{
						$qpa1 = Doctrine_Query::create()
							->select("*,
						AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
							->from('PatientCourse')
							->where("id='" . $eventid . "'")
							->andWhere('source_ipid = ""');
						$qp1 = $qpa1->fetchArray();

						if($qp1)
						{
							$rem = explode("|", $qp1[0]['course_title']);

							if(count($rem == 3))
							{
								$newCourseTitle = $rem[0] . " | " . $rem[1] . " | " . date("d.m.Y H:i", strtotime($startDate));
							}
						}

						$stamq = Doctrine_Core::getTable('PatientCourse')->findOneById($eventid);
						$stamq->course_title = Pms_CommonData::aesEncrypt($newCourseTitle);
						$stamq->done_date = date('Y-m-d H:i:s', strtotime($startDate));
						$stamq->save();
					}
					break;
					
				case "14":
				case "10":
					if(empty($eventid))
					{
						$docEvent = new DoctorCustomEvents();
						$docEvent->userid = $userid;
						$docEvent->clientid = $clientid;
						$docEvent->ipid = $patientSelected; //TO DO: after moving the calendars to navi left get this via post
						$docEvent->eventTitle = $eventTitle;
						$docEvent->startDate = $startDate;
						$docEvent->endDate = $endDate;
						$docEvent->eventType = $eventType;
						$docEvent->allDay = $allDayEvent;
						$docEvent->viewForAll = $viewForAll;
						$docEvent->dayplan_inform = $dayplan_inform;
						$docEvent->comments = $comments;
						
						$docEvent->save();
						
					}
					else
					{
						if ($stamq = Doctrine_Core::getTable('DoctorCustomEvents')->findOneById($eventid)) {						    
    						$stamq->eventTitle = $eventTitle;
    						$stamq->startDate = $startDate;
    						$stamq->endDate = $endDate;
    						$stamq->allDay = $allDayEvent;
    						$stamq->viewForAll = $viewForAll;
    						$stamq->dayplan_inform = $dayplan_inform;
    						$stamq->comments = $comments;
    						$stamq->save();
						}
					}
					break;

				
					
					
				case "11":
					if(empty($eventid))
					{
						$docEvent = new DoctorCustomEvents();
						$docEvent->userid = $userid;
						$docEvent->clientid = $clientid;
						$docEvent->eventTitle = $eventTitle;
						$docEvent->startDate = $startDate;
						$docEvent->endDate = $endDate;
						$docEvent->eventType = $eventType;
						$docEvent->allDay = $allDayEvent;
						$docEvent->viewForAll = $viewForAll;
						$docEvent->dayplan_inform = $dayplan_inform;
						$docEvent->comments = $comments;
						$docEvent->save();
					}
					else
					{
						if ($stamq = Doctrine_Core::getTable('DoctorCustomEvents')->findOneById($eventid)) {
    						$stamq->eventTitle = $eventTitle;
    						$stamq->startDate = $startDate;
    						$stamq->endDate = $endDate;
    						$stamq->allDay = $allDayEvent;
    						$stamq->viewForAll = $viewForAll;
    						$stamq->dayplan_inform = $dayplan_inform;
    						$stamq->comments = $comments;
    						$stamq->save();
						}
					}
					break;

				case "13":
					if($eventid > 0)
					{
						$startDateArray = explode(" ", $startDate);
						$endDateArray = explode(" ", $endDate);

						$startTimeArray = explode(":", $startDateArray[1]);
						$endTimeArray = explode(":", $endDateArray[1]);

						$stamq = Doctrine_Core::getTable('ContactForms')->findOneById($eventid);
						//get old values from contact form 
						$old_start_date = $stamq->start_date;
						$old_end_date = $stamq->end_date;
						$old_billable_date = $stamq->billable_date;
						$old_begindate_h = $stamq->begin_date_h;
						$old_begindate_m = $stamq->begin_date_m;
						$old_enddate_h = $stamq->end_date_h;
						$old_enddate_m = $stamq->end_date_m;

						$stamq->begin_date_h = $startTimeArray[0];
						$stamq->begin_date_m = $startTimeArray[1];
						$stamq->end_date_h = $endTimeArray[0];
						$stamq->end_date_m = $endTimeArray[1];
	
						/* Visit START DATE and END DATE */
						$stamq->date = $startDate;
						$stamq->start_date = $startDate;
						$stamq->end_date = $endDate;
						
						if($old_billable_date == $old_start_date ){
							$stamq->billable_date = $startDate;
						}elseif($old_billable_date == $old_end_date ){
							$stamq->billable_date = $startDate;
						}
						
						/* ---------------------------- */
						$stamq->save();

						$update_old_link = Doctrine_Query::create()
							->update('PatientCourse')
// 							->set('tabname', "'" . Pms_CommonData::aesEncrypt("contact_form_no_link") . "'")
							->set('tabname','?', Pms_CommonData::aesEncrypt("contact_form_no_link"))
							->where('ipid LIKE "' . $patientSelected . '"')
							->andWhere('tabname="' . Pms_CommonData::aesEncrypt("contact_form") . '"')
							->andWhere('recordid  = "' . $eventid . '"')
							->andWhere('source_ipid = ""');
						$update_old_link->execute();
						//verlauf
						$done_date = $startDate;
						$cust = new PatientCourse();
						$cust->ipid = $patientSelected;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($old_start_date)) . " wurde editiert");
						$cust->tabname = Pms_CommonData::aesEncrypt("contact_form");
						$cust->recordid = $eventid;
						$cust->done_date = $done_date;
						$cust->user_id = $userid;
						$cust->save();

						if($startDate != $old_start_date)
						{
							//$done_date = date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTimeArray[0] . ':' . $startTimeArray[1] . ':00'));
							$done_date = $startDate;
							$old_startdate = date('d.m.Y', strtotime($old_start_date));
							$new_startDate = date('d.m.Y', strtotime($startDate));
							//edited contact form date verlauf entry
							$cust = new PatientCourse();
							$cust->ipid = $patientSelected;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt('Datum: ' . $old_startdate . ' --> ' . $new_startDate);
							$cust->user_id = $userid;
							$cust->done_date = $done_date;
							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
							$cust->done_id = $eventid;
							$cust->save();
						}
						if($startTimeArray[0] != $old_begindate_h || $startTimeArray[1] != $old_begindate_m)
						{
							$old_start_hm = $old_begindate_h . ':' . $old_begindate_m;
							$start_hm = $startTimeArray[0] . ':' . $startTimeArray[1];

							//$done_date = date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTimeArray[0] . ':' . $startTimeArray[1] . ':00'));
							$done_date = $startDate;
							//edited contact form date verlauf entry
							$cust = new PatientCourse();
							$cust->ipid = $patientSelected;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt('Beginn: ' . $old_start_hm . ' --> ' . $start_hm);
							$cust->user_id = $userid;
							$cust->done_date = $done_date;
							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
							$cust->done_id = $eventid;
							$cust->save();
						}
						if($endTimeArray[0] != $old_enddate_h || $endTimeArray[1] != $old_enddate_m)
						{
							$old_end_hm = $old_enddate_h . ':' . $old_enddate_m;
							$end_hm = $endTimeArray[0] . ':' . $endTimeArray[1];

							//$done_date = date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTimeArray[0] . ':' . $startTimeArray[1] . ':00'));
							$done_date = $startDate;
							//edited contact form date verlauf entry
							$cust = new PatientCourse();
							$cust->ipid = $patientSelected;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt('Ende: ' . $old_end_hm . ' --> ' . $end_hm);
							$cust->user_id = $userid;
							$cust->done_date = $done_date;
							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
							$cust->done_id = $eventid;
							$cust->save();
						}
					}
					break;

				case "15": // U Verlauf
					if($eventid > 0)
					{
						$qpa1 = Doctrine_Query::create()
							->select("*,
						AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
							->from('PatientCourse')
							->where("id='" . $eventid . "'")
							->andWhere('source_ipid = ""');
						$qp1 = $qpa1->fetchArray();

						if($qp1)
						{
							$rem = explode("|", $qp1[0]['course_title']);

							if(count($rem == 4))
							{
								$newCourseTitle = $rem[0] . "|" . $rem[1] . "|" . $rem[2] . "|" . date("d.m.Y H:i", strtotime($startDate));
							}
						}

						$stamq = Doctrine_Core::getTable('PatientCourse')->findOneById($eventid);
						$stamq->course_title = Pms_CommonData::aesEncrypt($newCourseTitle);
						$stamq->done_date = date('Y-m-d H:i:s', strtotime($startDate));
						$stamq->save();
					}
					break;
				case "16": // XT Verlauf
					if($eventid > 0)
					{
						$qpa1 = Doctrine_Query::create()
							->select("*,
						AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
							->from('PatientCourse')
							->where("id='" . $eventid . "'")
							->andWhere('source_ipid = ""');
						$qp1 = $qpa1->fetchArray();

						if($qp1)
						{
							$rem = explode("|", $qp1[0]['course_title']);

							if(count($rem == 3))
							{
								$newCourseTitle = $rem[0] . "|" . $rem[1] . "|" . date("d.m.Y H:i", strtotime($startDate));
							}
						}

						$stamq = Doctrine_Core::getTable('PatientCourse')->findOneById($eventid);
						$stamq->course_title = Pms_CommonData::aesEncrypt($newCourseTitle);
						$stamq->done_date = date('Y-m-d H:i:s', strtotime($startDate));
						$stamq->save();
					}
					break;
				case "17": // Visit koord
					if($eventid > 0)
					{
						$startDateArray = explode(" ", $startDate);
						$endDateArray = explode(" ", $endDate);

						$startTimeArray = explode(":", $startDateArray[1]);
						$endTimeArray = explode(":", $endDateArray[1]);

						$stamq = Doctrine_Core::getTable('VisitKoordination')->findOneById($eventid);
						$stamq->visit_begin_date_h = $startTimeArray[0];
						$stamq->visit_begin_date_m = $startTimeArray[1];
						$stamq->visit_end_date_h = $endTimeArray[0];
						$stamq->visit_end_date_m = $endTimeArray[1];
						$stamq->visit_date = $startDate;
						/* Visit START DATE and END DATE */
						$stamq->start_date = $startDate;
						$stamq->end_date = $endDate;
						/* ---------------------------- */
						$stamq->save();
						
						//verlauf
						$cust = new PatientCourse();
						$cust->ipid = $patientSelected; //TO DO: after moving the calendars to navi left get this via post *DONE
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($cDate)) . " wurde editiert");
						$cust->recordid = $eventid;
						$cust->user_id = $userid;
						$cust->save();
					}
					break;
				default:
					break;
			endswitch;
			exit;
		}

		
		/*
		 * @claudiu on 21.02.2018 for ISPC-2159 added extra param
		 * $returnArray =  true
		 * this fn is called from fetchallcalendartypesAction
		 *
		 * @param string $returnArray
		 */
		public function fetchteameventsAction($returnArray = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;

			$eventsArray =  array();// this is the result

			/* 
			 * ISPC-2270
			 * removed the below block and moved-it in $this->_fetchteamshifts()
			 */
			/*
			//		get duty roster
			$dutyset = Doctrine_Query::create()
				->select('*')
				->from('Roster')
// 				->where("duty_date BETWEEN '" . date("Y-m-d H:i:s", $_REQUEST['start']) . "' AND '" . date("Y-m-d H:i:s", $_REQUEST['end']) . "'")
				->where("duty_date BETWEEN ? AND ?" , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])) )
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('isdelete = "0"');


			$dutyarray = $dutyset->fetchArray();

			$userids = "'99999999'";
			$groupids = "'99999999'";
			$comma = ",";
			foreach($dutyarray as $dutyevent)
			{
				$userids .= $comma . "'" . $dutyevent['userid'] . "'";
				$groupids .= $comma . "'" . $dutyevent['user_group'] . "'";
				$comma = ",";
			}

			$docusers = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isactive=0 and isdelete=0 and id IN(' . $userids . ') and usertype!="SA" and clientid=' . $logininfo->clientid);
			$docarray = $docusers->fetchArray();

			foreach($docarray as $doc)
			{  // ISPC - 1164
				//$doctorsFinal[$doc['id']] = $doc['last_name'] . ", " . $doc['first_name'];
				if(!empty($doc['roster_shortcut']))
				{
					$doctorsFinal[$doc['id']] = $doc['roster_shortcut'];
				}
				else
				{
					$doctorsFinal[$doc['id']] = $doc['user_title']. " " . $doc['last_name'] . ", " . $doc['first_name'];
				}
				$doctorsColorsFinal[$doc['id']] = $doc['usercolor'];
			}

			$grpquery = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid=' . $logininfo->clientid . ' and isdelete=0 and isactive=1');
			$groups = $grpquery->fetchArray();

			foreach($groups as $group)
			{
				$groupsFinal[$group['id']] = $group;
			}

			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);
			
			$shi = 0;
			foreach($client_shifts as $k=> $shift_data){
				
				$shifts[$shi] = $shift_data;
				$shifts[$shi]['start_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['start']));
				$shifts[$shi]['end_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['end']));
// 				$shifts[$shift_data['id']] = $shift_data;
// 				$shifts[$shift_data['id']]['start_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['start']));
// 				$shifts[$shift_data['id']]['end_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['start']));
				
				$shi++;
			}
			
			
			$sortarr = 'start_time';
			$shifts_sorted = $this->array_sort($shifts, $sortarr, SORT_ASC);

			$shifts_sorted = array_values($shifts_sorted);
			
			foreach($shifts_sorted as $s_nr => $sh_data){
				$shift_correspondent[$sh_data['id']] = $s_nr; 
			}
			
			
			foreach($dutyarray as $duty)
			{

				if($duty['fullShift'] == 1)
				{
					$startDutyDate = date("Y-m-d", strtotime($duty['duty_date']));
					$endDutyDate = "";
					$allDay = true;
				}
				else
				{
					$startDutyDate = date("Y-m-d H:i:s", strtotime($duty['shiftStartTime']));
					$endDutyDate = date("Y-m-d H:i:s", strtotime($duty['shiftEndTime']));
					$allDay = false;
				}

				if($duty['shift'] != '0')
				{
					$color = '#' . $client_shifts[$duty['shift']]['color'];
					$start_hours = date('H:i:s', strtotime($client_shifts[$duty['shift']]['start']));
					$end_hours = date('H:i:s', strtotime($client_shifts[$duty['shift']]['end']));


					if(strtotime($client_shifts[$duty['shift']]['start']) < strtotime($client_shifts[$duty['shift']]['end']))
					{
						$start_date = $duty['duty_date'];
						$end_date = $duty['duty_date'];
					}
					else
					{
						$start_date = $duty['duty_date'];
						$end_date = date('Y-m-d', strtotime('+1 day', strtotime($duty['duty_date']))); //next day overnight
					}

					$startDutyDate = date("Y-m-d", strtotime($start_date)) . ' ' . $start_hours;
					$endDutyDate = date("Y-m-d", strtotime($end_date)) . ' ' . $end_hours;

					$event_prefix = $client_shifts[$duty['shift']]['name'];
					$event_shift_correspondent = $shift_correspondent[$duty['shift']];
				}
				else
				{
					$color = '#98FB98';
					$event_prefix = 'Dienst';
				}
				
				$day_hour = date('H',strtotime($client_shifts[$duty['shift']]['start']));
						
				if($day_hour < '06' || $day_hour > '20')
				{
					$isNight = true;
				}
				else
				{
					$isNight = false;
				}

				if($client_shifts[$duty['shift']]['show_time'] == "1")
				{
					$allDay = false;
				}
				else
				{
					$allDay = true;
				}

				$eventsArray[] = array(
						'id' => $duty['id'],
						'title' => $event_prefix . ': ' . $doctorsFinal[$duty['userid']],
						'start' => $startDutyDate,
						'end' => $endDutyDate,
						'color' => $color,
						'textColor' => 'black',
						'allDay' => $allDay,
						'eventType' => '4', //duty rooster
						'isNight' => $isNight, //night shift
						'shift_id' => $duty['shift'], // shift id
						'shift_order' => $event_shift_correspondent  // shift - order- corespondent
				);
			}
			*/
			

			$tmCustomEv = new TeamCustomEvents();
			$customEvents = $tmCustomEv->getTeamCustomEvents($clientid);

			$event_types = array('12' => 'Ferien: ', '13' => 'Team Sitzungen: ', '14' => 'Fortbildung: ', '15' => 'Supervision: ', '16' => 'Kongress: ', '17' => 'Rufbereitschaft: ', '18' => 'Urlaub / Vertretung: ', '20' => 'Einsatzleitung: ', '21' => 'Termin: ', '22'=>'');
			foreach($customEvents as $cEvent)
			{
				if($cEvent['allDay'] == 1)
				{
					$allDay = true;
				}
				else
				{
					$allDay = false;
				}
				
				$day_hour_cust = date('H',strtotime($cEvent['startDate']));
				
				if($day_hour_cust < '06' || $day_hour_cust > '20')
				{
					$isNight = true;
				}
				else
				{
					$isNight = false;
				}

				if($cEvent['dayplan_inform'] == 1)
				{
					$dayplan_inform = true;
				}
				else
				{
					$dayplan_inform = false;
				}
				
				$eventsArray[] = array(
					'id' => $cEvent['id'],
					'title' => $cEvent['eventTitle'],
					'titlePrefix' => $event_types[$cEvent['eventType']],
					'start' => date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
					'end' => date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
					'color' => '#33CCFF',
					'textColor' => 'black',
					'allDay' => $allDay,
					'resizable' => true,
					'eventType' => $cEvent['eventType'], //custom event Holidays team
					'isNight' => $isNight, //night event -- to be sorted properly 
					'dayplan_inform' => $dayplan_inform, //show in tagesplanung a notification thgat u have team event at this time

				    'comments' => $cEvent['comments'], // this are the original so user can edit
    	            'comments_qtip' => nl2br($this->view->escape($cEvent['comments'])), // this are displayed as qtip
				);
			}

			//Vacation Events
			$uv = new UserVacations();

			$start_cal_date = date('Y-m-d H:i:s', $_REQUEST['start']);
			$end_cal_date = date('Y-m-d H:i:s', $_REQUEST['end']);

			$vacations_events = $uv->get_client_vacations($clientid, $start_cal_date, $end_cal_date);

			foreach($vacations_events as $k_vacation => $v_vacation)
			{
				$vacation_url = '';
				$editable = false; //includes drag and resize

				if($usertype == 'CA' || $usertype == 'SA')
				{
					$vacation_url = APP_BASE . 'user/vacationreplacements?v_id=' . $v_vacation['id'] . '&u_id=' . $v_vacation['userid'];
					$editable = true;
				}
				else
				{
					//make event linkable only if current user is same as the one event user id
					if($userid == $v_vacation['userid'])
					{
						$vacation_url = APP_BASE . 'user/vacationreplacements?v_id=' . $v_vacation['id'] . '&u_id=' . $v_vacation['userid'];
						$editable = true;
					}
					else
					{
						$vacation_url = '';
						$editable = false;
					}
				}

				if($editable)
				{
					$class_name = 'event_editable';
				}
				else
				{
					$class_name = '';
				}

				$eventsArray[] = array(
					'id' => 'v_' . $v_vacation['id'],
					'title' => $v_vacation['user_details']['last_name'] . ', ' . $v_vacation['user_details']['first_name'],
					'titlePrefix' => $event_types['18'],
					'start' => date("Y-m-d H:i:s", strtotime($v_vacation['start'])),
					'end' => date("Y-m-d H:i:s", strtotime($v_vacation['end'])),
					'color' => '#00FF77',
					'textColor' => 'black',
					'allDay' => true,
					'editable' => $editable,
					'resizable' => $editable,
					'eventType' => '18', //vacation event
					'url' => $vacation_url,
					'className' => $class_name
				);
			}

			
			if ($returnArray === true) {
			    return $eventsArray;
			} else {
			    $this->_helper->getHelper('json')->sendJson($eventsArray);
			    exit; //for readability
			}
		}

		public function saveteameventsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$eventid = $_POST['eventId']; //existing = edit event  / empty = new event
			$eventTitle = $_POST['eventTitle']; //existing = edit event  / empty = new event
			$startDate = $_POST['startDate'];
			$endDate = $_POST['endDate'];
			$eventType = $_POST['eventType'];
			$allDayEvent = $_POST['allDay']; //1-true, 0-false (on event type 4 this is fullshift -- changed now uses show time shift settings)
			$selectedUsers = $_POST['selectedUsers'];
			$dayplan_inform = $_POST['dayplan_inform'];
			$comments = $_POST['comments'];

			switch($eventType):
				case "12": //holidays
				case "13": //Team Sitzungen
				case "14": //Fortbildung
				case "15": //Supervision
				case "16": //Kongress
				case "17": //Rufbereitschaft
				case "20": //Einsatzleitung
				case "21": //Termin
				case "22": //Freier Termin 
					if(empty($eventid))
					{
						//add new
						$tmEvent = new TeamCustomEvents();
						$tmEvent->userid = $userid;
						$tmEvent->clientid = $clientid;
						$tmEvent->eventTitle = $eventTitle;
						$tmEvent->startDate = $startDate;
						$tmEvent->endDate = $endDate;
						$tmEvent->eventType = $eventType;
						$tmEvent->allDay = $allDayEvent;
						$tmEvent->dayplan_inform = $dayplan_inform;
						$tmEvent->comments = $comments;
						
						$tmEvent->save();
					}
					else
					{
						//update
						if (($tmEvent = Doctrine_Core::getTable('TeamCustomEvents')->findOneById($eventid))) {
    						$tmEvent->eventTitle = $eventTitle;
    						$tmEvent->startDate = $startDate;
    						$tmEvent->endDate = $endDate;
    						$tmEvent->allDay = $allDayEvent;
    						$tmEvent->dayplan_inform = $dayplan_inform;
    						$tmEvent->comments = $comments;
    						$tmEvent->save();
						}
					}
					break;

				case "4": //duty rooster
					if(empty($eventid))
					{ //add new
						$users = json_decode($selectedUsers, true);
						$checks = json_decode($_POST['checked'], true);
						$startDates = json_decode($_POST['startShiftDate'], true);
						$startTimes = json_decode($_POST['startShiftTime'], true);

						$endDates = json_decode($_POST['endShiftDate'], true);
						$endTimes = json_decode($_POST['endShiftTime'], true);

						foreach($startDates as $keyst => $sDates)
						{
							if(!empty($startTimes[$keyst]))
							{
								$sTime = " " . $startTimes[$keyst] . ":00"; //H:m  +:00
							}
							else
							{
								$sTime = " 00:00:00";
							}

							if(!empty($sDates))
							{
								$startDateTime[$keyst] = date("Y-m-d H:i:s", strtotime($sDates . $sTime));
							}
						}

						foreach($endDates as $keyend => $endDate)
						{
							if(!empty($endTimes[$keyend]))
							{
								$eTime = " " . $endTimes[$keyend] . ":00"; //H:m  +:00
							}
							else
							{
								$eTime = " 00:00:00";
							}

							if(!empty($endDate))
							{
								$endDateTime[$keyend] = date("Y-m-d H:i:s", strtotime($endDate . $eTime));
							}
						}

						foreach($users as $key => $user)
						{
							$ids = explode("-", $user); //[0] = uid, [1] = gid
							//if fullshift = 1 we use the duty date else we use the start time and end time
							if($ids[0] != 0)
							{
								$roster = new Roster();
								$roster->userid = $ids[0];
								$roster->duty_date = $startDate;
								$roster->user_group = $ids[1];
								$roster->clientid = $clientid;
								$roster->fullShift = $checks[$key]; //1= full shift, 0=custom shift
								$roster->shiftStartTime = $startDateTime[$key];
								$roster->shiftEndTime = $endDateTime[$key];
								$roster->save();
							}
						}
					}
					else
					{
						//update
						if($allDay == 1)
						{
							$roster = Doctrine::getTable('Roster')->find($eventid);
							$roster->duty_date = $startDate;
							$roster->fullShift = $allDayEvent;
							$roster->save();
						}
						else
						{
							$roster = Doctrine::getTable('Roster')->find($eventid);
							$roster->duty_date = $startDate;
							$roster->shiftStartTime = $startDate;
							$roster->shiftEndTime = $endDate;
							$roster->fullShift = $allDayEvent;
							$roster->save();
						}
					}
					break;
				case '18':
					$vacation_form = new Application_Form_Vacations();
					if(!empty($eventid))
					{
						//edit
						$vacation = explode('_', $eventid);
						$data['start'] = $startDate;
						$data['end'] = $endDate;

						$save_vacation = $vacation_form->edit_period($vacation[1], $data);
					}
					break;
			endswitch;
			exit;
		}

		
		
		/*
		 * @claudiu on 21.02.2018 for ISPC-2159 added extra param
		 * $returnArray =  true
		 * this fn is called from fetchallcalendartypesAction
		 *
		 * @param string $returnArray
		 */
		private function _fetchteamshifts($returnArray = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $usertype = $logininfo->usertype;
		
		    $eventsArray =  array();// this is the result
		
		    //		get duty roster
		    $dutyset = Doctrine_Query::create()
		    ->select('*')
		    ->from('Roster')
		    // 				->where("duty_date BETWEEN '" . date("Y-m-d H:i:s", $_REQUEST['start']) . "' AND '" . date("Y-m-d H:i:s", $_REQUEST['end']) . "'")
		    ->where("duty_date BETWEEN ? AND ?" , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])) )
		    ->andWhere('clientid="' . $clientid . '"')
		    ->andWhere('isdelete = "0"');
		
		
		    $dutyarray = $dutyset->fetchArray();
		
		    $userids = "'99999999'";
		    $groupids = "'99999999'";
		    $comma = ",";
		    foreach($dutyarray as $dutyevent)
		    {
		        $userids .= $comma . "'" . $dutyevent['userid'] . "'";
		        $groupids .= $comma . "'" . $dutyevent['user_group'] . "'";
		        $comma = ",";
		    }
		
		    $docusers = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where('isactive=0 and isdelete=0 and id IN(' . $userids . ') and usertype!="SA" and clientid=' . $logininfo->clientid);
		    $docarray = $docusers->fetchArray();
		
		    foreach($docarray as $doc)
		    {  // ISPC - 1164
		        //$doctorsFinal[$doc['id']] = $doc['last_name'] . ", " . $doc['first_name'];
		        if(!empty($doc['roster_shortcut']))
		        {
		            $doctorsFinal[$doc['id']] = $doc['roster_shortcut'];
		        }
		        else
		        {
		            $doctorsFinal[$doc['id']] = $doc['user_title']. " " . $doc['last_name'] . ", " . $doc['first_name'];
		        }
		        $doctorsColorsFinal[$doc['id']] = $doc['usercolor'];
		    }
		
		    $grpquery = Doctrine_Query::create()
		    ->select('*')
		    ->from('Usergroup')
		    ->where('clientid=' . $logininfo->clientid . ' and isdelete=0 and isactive=1');
		    $groups = $grpquery->fetchArray();
		
		    foreach($groups as $group)
		    {
		        $groupsFinal[$group['id']] = $group;
		    }
		
		    $c_shifts = new ClientShifts();
		    $client_shifts = $c_shifts->get_client_shifts($clientid);
		    	
		    $shi = 0;
		    foreach($client_shifts as $k=> $shift_data){
		
		        $shifts[$shi] = $shift_data;
		        $shifts[$shi]['start_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['start']));
		        $shifts[$shi]['end_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['end']));
		        // 				$shifts[$shift_data['id']] = $shift_data;
		        // 				$shifts[$shift_data['id']]['start_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['start']));
		        // 				$shifts[$shift_data['id']]['end_time'] = date("Y-m-d", time()) . ' ' .date("H:i:s",strtotime($shift_data['start']));
		
		        $shi++;
		    }
		    	
		    	
		    $sortarr = 'start_time';
		    $shifts_sorted = $this->array_sort($shifts, $sortarr, SORT_ASC);
		
		    $shifts_sorted = array_values($shifts_sorted);
		    	
		    foreach($shifts_sorted as $s_nr => $sh_data){
		        $shift_correspondent[$sh_data['id']] = $s_nr;
		    }
		    	
		    	
		    foreach($dutyarray as $duty)
		    {
		
		        if($duty['fullShift'] == 1)
		        {
		            $startDutyDate = date("Y-m-d", strtotime($duty['duty_date']));
		            $endDutyDate = "";
		            $allDay = true;
		        }
		        else
		        {
		            $startDutyDate = date("Y-m-d H:i:s", strtotime($duty['shiftStartTime']));
		            $endDutyDate = date("Y-m-d H:i:s", strtotime($duty['shiftEndTime']));
		            $allDay = false;
		        }
		
		        if($duty['shift'] != '0')
		        {
		            $color = '#' . $client_shifts[$duty['shift']]['color'];
		            $start_hours = date('H:i:s', strtotime($client_shifts[$duty['shift']]['start']));
		            $end_hours = date('H:i:s', strtotime($client_shifts[$duty['shift']]['end']));
		
		
		            if(strtotime($client_shifts[$duty['shift']]['start']) < strtotime($client_shifts[$duty['shift']]['end']))
		            {
		                $start_date = $duty['duty_date'];
		                $end_date = $duty['duty_date'];
		            }
		            else
		            {
		                $start_date = $duty['duty_date'];
		                $end_date = date('Y-m-d', strtotime('+1 day', strtotime($duty['duty_date']))); //next day overnight
		            }
		
		            $startDutyDate = date("Y-m-d", strtotime($start_date)) . ' ' . $start_hours;
		            $endDutyDate = date("Y-m-d", strtotime($end_date)) . ' ' . $end_hours;
		
		            $event_prefix = $client_shifts[$duty['shift']]['name'];
		            $event_shift_correspondent = $shift_correspondent[$duty['shift']];
		        }
		        else
		        {
		            $color = '#98FB98';
		            $event_prefix = 'Dienst';
		        }
		
		        $day_hour = date('H',strtotime($client_shifts[$duty['shift']]['start']));
		
		        if($day_hour < '06' || $day_hour > '20')
		        {
		            $isNight = true;
		        }
		        else
		        {
		            $isNight = false;
		        }
		
		        if($client_shifts[$duty['shift']]['show_time'] == "1")
		        {
		            $allDay = false;
		        }
		        else
		        {
		            $allDay = true;
		        }
		
		        $eventsArray[] = array(
		            'id' => $duty['id'],
		            'title' => $event_prefix . ': ' . $doctorsFinal[$duty['userid']],
		            'start' => $startDutyDate,
		            'end' => $endDutyDate,
		            'color' => $color,
		            'textColor' => 'black',
		            'allDay' => $allDay,
		            'eventType' => '4', //duty rooster
		            'isNight' => $isNight, //night shift
		            'shift_id' => $duty['shift'], // shift id
		            'shift_order' => $event_shift_correspondent  // shift - order- corespondent
		        );
		    }
		
		    /*
		    $tmCustomEv = new TeamCustomEvents();
		    $customEvents = $tmCustomEv->getTeamCustomEvents($clientid);
		
		    $event_types = array('12' => 'Ferien: ', '13' => 'Team Sitzungen: ', '14' => 'Fortbildung: ', '15' => 'Supervision: ', '16' => 'Kongress: ', '17' => 'Rufbereitschaft: ', '18' => 'Urlaub / Vertretung: ', '20' => 'Einsatzleitung: ', '21' => 'Termin: ', '22'=>'');
		    foreach($customEvents as $cEvent)
		    {
		        if($cEvent['allDay'] == 1)
		        {
		            $allDay = true;
		        }
		        else
		        {
		            $allDay = false;
		        }
		
		        $day_hour_cust = date('H',strtotime($cEvent['startDate']));
		
		        if($day_hour_cust < '06' || $day_hour_cust > '20')
		        {
		            $isNight = true;
		        }
		        else
		        {
		            $isNight = false;
		        }
		
		        if($cEvent['dayplan_inform'] == 1)
		        {
		            $dayplan_inform = true;
		        }
		        else
		        {
		            $dayplan_inform = false;
		        }
		
		        $eventsArray[] = array(
		            'id' => $cEvent['id'],
		            'title' => $cEvent['eventTitle'],
		            'titlePrefix' => $event_types[$cEvent['eventType']],
		            'start' => date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
		            'end' => date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
		            'color' => '#33CCFF',
		            'textColor' => 'black',
		            'allDay' => $allDay,
		            'resizable' => true,
		            'eventType' => $cEvent['eventType'], //custom event Holidays team
		            'isNight' => $isNight, //night event -- to be sorted properly
		            'dayplan_inform' => $dayplan_inform, //show in tagesplanung a notification thgat u have team event at this time
		
		            'comments' => $cEvent['comments'], // this are the original so user can edit
		            'comments_qtip' => nl2br($this->view->escape($cEvent['comments'])), // this are displayed as qtip
		        );
		    }
		
		    //Vacation Events
		    $uv = new UserVacations();
		
		    $start_cal_date = date('Y-m-d H:i:s', $_REQUEST['start']);
		    $end_cal_date = date('Y-m-d H:i:s', $_REQUEST['end']);
		
		    $vacations_events = $uv->get_client_vacations($clientid, $start_cal_date, $end_cal_date);
		
		    foreach($vacations_events as $k_vacation => $v_vacation)
		    {
		        $vacation_url = '';
		        $editable = false; //includes drag and resize
		
		        if($usertype == 'CA' || $usertype == 'SA')
		        {
		            $vacation_url = APP_BASE . 'user/vacationreplacements?v_id=' . $v_vacation['id'] . '&u_id=' . $v_vacation['userid'];
		            $editable = true;
		        }
		        else
		        {
		            //make event linkable only if current user is same as the one event user id
		            if($userid == $v_vacation['userid'])
		            {
		                $vacation_url = APP_BASE . 'user/vacationreplacements?v_id=' . $v_vacation['id'] . '&u_id=' . $v_vacation['userid'];
		                $editable = true;
		            }
		            else
		            {
		                $vacation_url = '';
		                $editable = false;
		            }
		        }
		
		        if($editable)
		        {
		            $class_name = 'event_editable';
		        }
		        else
		        {
		            $class_name = '';
		        }
		
		        $eventsArray[] = array(
		            'id' => 'v_' . $v_vacation['id'],
		            'title' => $v_vacation['user_details']['last_name'] . ', ' . $v_vacation['user_details']['first_name'],
		            'titlePrefix' => $event_types['18'],
		            'start' => date("Y-m-d H:i:s", strtotime($v_vacation['start'])),
		            'end' => date("Y-m-d H:i:s", strtotime($v_vacation['end'])),
		            'color' => '#00FF77',
		            'textColor' => 'black',
		            'allDay' => true,
		            'editable' => $editable,
		            'resizable' => $editable,
		            'eventType' => '18', //vacation event
		            'url' => $vacation_url,
		            'className' => $class_name
		        );
		    }
		    */
		
		    	
		    if ($returnArray === true) {
		        return $eventsArray;
		    } else {
		        $this->_helper->getHelper('json')->sendJson($eventsArray);
		        exit; //for readability
		    }
		}
		
		
		
		function printteamcalendarAction()
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->layout->setLayout('layout_teamcalendar');
		}

		function printdoctorcalendarAction()
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->layout->setLayout('layout_teamcalendar');
		}

		function printpatientcalendarAction()
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->layout->setLayout('layout_teamcalendar');
		}


		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();
			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{
								if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'day' || $on == 'assessment_completed_date' || $on == 'visit_date' || $on == 'contact_form_date' || $on == 'first_sapv_active_day' || $on == 'patient_discharge_date' || $on == 'death_date' || $on == 'entry_date')
								{
		
									if($on == 'birthdyears')
									{
										$v2 = substr($v2, 0, 10);
									}
									$sortable_array[$k] = strtotime($v2);
								}
								elseif($on == 'epid')
								{
									$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
								}
								elseif($on == 'percentage')
								{
									$sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'day' || $on == 'assessment_completed_date' || $on == 'visit_date' || $on == 'contact_form_date' || $on == 'first_sapv_active_day' || $on == 'patient_discharge_date' || $on == 'death_date')
						{
							if($on == 'birthdyears')
							{
								$v = substr($v, 0, 10);
							}
							$sortable_array[$k] = strtotime($v);
						}
						elseif($on == 'epid' || $on == 'percentage')
						{
							$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
						}
						elseif($on == 'percentage')
						{
							$sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}
				switch($order)
				{
					case SORT_ASC:
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
		
					case SORT_DESC:
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
		
						break;
				}
		
				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}
		
			return $new_array;
		}
		
		
		

	/**
	 * all events in this tab are NOT editable @editable = false
	 * we don't check in patientCourse to see if you marked a entry as wrong, you have isdeleted for that 
	 * 
	 * @param string $returnArray
	 * @return multitype:multitype:string boolean unknown NULL  multitype:string boolean unknown Ambigous <>
	 */
	public function fetchalltodosAction($returnArray = false)
	{
	    if ( ! $this->getRequest()->isXmlHttpRequest()  && APPLICATION_ENV != 'development') {
	        throw new Exception('!isXmlHttpRequest', 0);
	    }
	
	    $this->_helper->viewRenderer->setNoRender(true);
	    $this->_helper->layout->disableLayout();
	
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $groupid = $logininfo->groupid;
	    $user_type = $logininfo->user_type;
	    $this->view->userid = $userid;
	    $this->view->groupid = $groupid;
	    $this->view->user_type = $user_type;
	    $hidemagic = Zend_Registry::get('hidemagic');
	
	    
	    $request_startTimestamp = $this->getRequest()->getParam('start');
	    $request_endTimestamp = $this->getRequest()->getParam('end');
	    
	    $request_startTimestamp = strtotime("midnight", $request_startTimestamp) ;
	    $request_endTimestamp   = strtotime("tomorrow", $request_endTimestamp) - 1;
	    
	    $request_start_Datetime = date("Y-m-d H:i:s", $request_startTimestamp);
	    $request_end_Datetime = date("Y-m-d H:i:s", $request_endTimestamp);
	    
	    $eventsArray =  array();// this is the result
	   
	    
	    //get client ipids to avoid multi client users to see patients in other clients
	    $sql = "e.id, p.id, e.ipid, e.epid, p.birthd, p.admission_date, p.change_date, p.traffic_status,";
	    $sql .= "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)  as first_name,";
	    $sql .= "CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
	    $sql .= "CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
	    $sql .= "CONVERT(AES_DECRYPT(p.title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
	    $sql .= "CONVERT(AES_DECRYPT(p.salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
	    $sql .= "CONVERT(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
	    $sql .= "CONVERT(AES_DECRYPT(p.street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
	    $sql .= "CONVERT(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
	    $sql .= "CONVERT(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
	    $sql .= "CONVERT(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
	    $sql .= "CONVERT(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
	    $sql .= "CONVERT(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
	
	    // if super admin check if patient is visible or not
	
	    if($logininfo->usertype == 'SA')
	    {
	        $sql = "*,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.isadminvisible,p.traffic_status,";
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
	    
	    $user_patients = PatientUsers::getUserPatients($logininfo->userid); //get user's patients by permission
	    
	    $allowed_patients = Doctrine_Query::create()
	    ->select($sql)
	    ->from('EpidIpidMapping e')
	    ->leftJoin('e.PatientMaster p')
	    ->where('e.clientid = ?', $clientid)
	    ->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
// 	    ->leftJoin("e.PatientQpaMapping q")
	    ->andWhere("p.isdischarged = 0 AND p.isdelete = 0 AND p.isstandby = 0 AND p.isstandbydelete = 0")
	    ->fetchArray()
	    ;
	    
	    if ( ! empty($allowed_patients)) {
    	    PatientMaster::beautifyName($allowed_patients);
    	    
    	    $patients_details = array();
    	    
    	    foreach ($allowed_patients as $v_cipid) {
    	        $patients_details[$v_cipid['ipid']] = $v_cipid;
    	    }
    	    
    	    
    	    $todo = Doctrine_Query::create()
    	    ->select("*")
    	    ->from('ToDos')
    	    ->where('client_id= ?', $clientid )
    	    ->andWhere('isdelete=0')
    	    ->andWhere('iscompleted=0')
    	    ->andWhereIn('ipid', array_keys($patients_details))
    
    	    
    	    // 	    ->andWhere('create_date BETWEEN ? AND ?' , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])))
//     	    ->andWhere('until_date BETWEEN ? AND ?' , array(date("Y-m-d H:i:s", $request_startTimestamp) , date("Y-m-d H:i:s", $request_endTimestamp)))
    	    ->andWhere('until_date BETWEEN ? AND ?' , array($request_start_Datetime , $request_end_Datetime))
//     	    ->andWhere('((until_date BETWEEN ? AND ?) OR (create_date BETWEEN ? AND ? ))' , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end']), date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])))
//     	    ->andWhere(new Doctrine_Expression('(until_date BETWEEN ? AND ?) OR (create_date BETWEEN ? AND ?)') , array(date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end']), date("Y-m-d H:i:s", $_REQUEST['start']) , date("Y-m-d H:i:s", $_REQUEST['end'])))
    	    ->andWhere('triggered_by != "system"');
    	    /*
    	    if($user_type != 'SA')
    	    {
    	        if(!in_array($groupid, $coord_groups))
    	        {
    	            $todo->andWhere('triggered_by !="system_medipumps"');
    	        }
    	
    	        if($groupid > 0)
    	        {
    	            $sql_group = ' OR group_id = "' . $groupid . '"';
    	        }
    	        $todo->andWhere('user_id IN("' . implode(', ', $current_user_group_asignees) . '") ' . $sql_group . '');
    	    }
    	    */

    	    
    	    $todoarray = $todo->fetchArray();
    
        	$my_fake_groupby_array = array(); //TODO: check if this logic is correct
        	$my_fake_groupby_key = null;
        	
        	//TODO this fn MUST be changed.... awaiting response what to do is user is inakive or deleted
        	if ( ! empty($todoarray)) {
        	   $todo_ers_details = Pms_CommonData::get_nice_name_multiselect();
        	   
        	   $todo_ers_prefix =  Pms_CommonData::get_users_selectbox_separator_string();
        	}
        	
        	
        	
    	    foreach ($todoarray as $todo) {
    	        
    	        if ($todo['record_id'] > 0) {
    	            $my_fake_groupby_key = 'record_id_' . $todo['record_id'] . $todo['ipid'];
    	        } elseif ($todo['course_id'] > 0) {
    	            $my_fake_groupby_key = 'course_id_' . $todo['course_id'] . $todo['ipid'];
    	        } else {
    	            $my_fake_groupby_key = 'empty_' . $todo['todo'] . $todo['triggered_by'] . date('YmdH', strtotime($todo['until_date'])) . $todo['ipid'];
    	        }
    	        
    	        if (isset($my_fake_groupby_array[$my_fake_groupby_key]))
    	            continue;
    
    	        $my_fake_groupby_array[$my_fake_groupby_key] = true;
    	        
    	        //$patient_name = $patients_details[$todo['ipid']]['nice_name_epid'] ;
    	        $patient_name = $patients_details[$todo['ipid']]['nice_name'] ;
    	        $patient_epid = $patients_details[$todo['ipid']]['epid'] ;
    	        
    	        
    	        $to_do_prefix = "";
    	        if($todo['triggered_by'] == 'system_medipumps')
    	        {
    	            $to_do_prefix =  strtoupper($patient_epid) . "\n";
    	        }
    	        else if($todo['triggered_by'] == "newreceipt_1" || $todo['triggered_by'] == "newreceipt_2")
    	        {
    	            preg_match('/<a href="([^"]*)">/', $todo['todo'], $matches);
    	            $todo_url = $matches[1];
    	            $todo['todo'] = strip_tags($todo['todo']);
    	        }
    	        else if($todo['triggered_by'] == "teammeeting_completed" )
    	        {
    	
    	            $to_do_prefix = "Teambesprechung:\n" . $patient_name . "\n";
    	            $todo['todo'] = strip_tags($todo['todo']);
    	        }
    	        else
    	        {
    	            $to_do_prefix = $patient_name . "\n";
    	        }
    	
//     	        $to_do_prefix .= "Create: ". date('d.m.Y', strtotime($todo['create_date'])) ."\n";
//     	        $to_do_prefix .= $this->translate('untildate') ."\n". date('d.m.Y', strtotime($todo['until_date'])) ."\n";
    	        
    	        $to_do_prefix .= date('d.m', strtotime($todo['create_date'])) ." => " . date('d.m', strtotime($todo['until_date'])) ."\n";
    	        
    	        $todo_ers_italic = array(); 
    	        
    	        if ( ! empty($todo['additional_info'])) {
    	            
    	            $todo_ers = explode(';', $todo['additional_info']);
    	            
    	            foreach($todo_ers as $todoer) {
    	                //$todo_ers_prefix['glue_on_view'];
    	                
    	                switch (substr($todoer, 0, 1)) {
    	                    
    	                    case $todo_ers_prefix['all'] :
    	                        //alle
    	                        $todo_ers_italic[] = 'alle';
    	                        break; // not break 2... so it's like in verlauf
    	                        
    	                    case $todo_ers_prefix['user'] :
    	                        $todo_ers_italic[] = $todo_ers_details[$this->translate('users')][$todoer];
    	                        break;
    	                        
    	                    case $todo_ers_prefix['group'] :
    	                        $todo_ers_italic[] = $todo_ers_details[$this->translate('group_name')][$todoer];
    	                        break;
    	                        
    	                    default:
    	                        if (substr($todoer, 0, strlen($todo_ers_prefix['pseudogroup'])) == $todo_ers_prefix['pseudogroup']) {
    	                            $todo_ers_italic[] = $todo_ers_details[$this->translate('liste_user_pseudo_group')][$todoer];
    	                        } 
    	                    
    	                }
    	            }    	            
    	            
    	        } else {
    	            //[user_id] => 0
    	            //[group_id] => 66
    	        }
    	        
    	        if($todo['triggered_by'] == "newreceipt_1" || $todo['triggered_by'] == "newreceipt_2")
    	        {
    	
    	            $eventsArray[] = array(
    	                'id' => $todo['id'],
    	                'title' => $todo['todo'],
    	                'titlePrefix' => $to_do_prefix,
    	                'start' => date("Y-m-d", strtotime($todo['until_date'])),
    	                'color' => 'orange',
    	                'textColor' => 'black',
    	                'allDay' => true,
    	                'resizable' => false,
    	                'url' =>$todo_url,
    	                'event_source' => $todo['triggered_by'],
    	                'eventType' => '3', //todo
    	                    
    	                'patient_id' => Pms_Uuid::encrypt($patients_details[$todo['ipid']] ['PatientMaster'] ['id']),
    	                
    	                'editable' => false,
    	                'escapeEventTitle' => false,
    	                'escapeEventTitlePrefix' => true,
    	                
    	            );
    	        }
    	        else
    	        {
    	
    	
    	            $todo['todo'] = str_replace(array('<b>','</b>'),array('',''), $todo['todo']);
    	
    	
    	            $eventsArray[] = array(
    	                'id' => $todo['id'],
    	                'title' =>  '<b>' . $todo['todo'] . '</b>'
    	                   . '<br>'
    	                   . "<span class='italic'>". implode($todo_ers_prefix['glue_on_view'], $todo_ers_italic) . "</span>",
    	                
    	                'titlePrefix' => $to_do_prefix,
    	                'start' => date("Y-m-d", strtotime($todo['until_date'])),
    	                'color' => 'orange',
    	                'textColor' => 'black',
    	                'allDay' => true,
    	                'resizable' => false,
    	                'event_source' => 'todo',
    	                'eventType' => '3', //todo
    	                    
    	                'patient_id' => Pms_Uuid::encrypt($patients_details[$todo['ipid']] ['PatientMaster'] ['id']),
    	                
    	                'editable' => false,
    	                'escapeEventTitle' => false,
    	                'escapeEventTitlePrefix' => true,
    	            );
    	        }
    	    }
    	    //		end get todos by user id
	    } //endif ( ! empty($allowed_patients)) 
	    
	    if ($returnArray === true) {
	        return $eventsArray;
	    } else {
    	    $this->_helper->getHelper('json')->sendJson($eventsArray);
    	    exit; //for readability	        
	    }
	
	}
	
	

	/**
	 * 
	 * @param string $returnArray
	 * @throws Exception
	 * @return multitype:string boolean unknown Ambigous <>
	 */
    public function fetchallpatientscustomAction($returnArray = false) 
    {

        if ( ! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Exception('!isXmlHttpRequest', 0);
        }

        
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;
        $user_type = $logininfo->user_type;
        $this->view->userid = $userid;
        $this->view->groupid = $groupid;
        $this->view->user_type = $user_type;
        $hidemagic = Zend_Registry::get('hidemagic');
        
         
        $eventsArray =  array();// this is the result
        $request_startTimestamp = $this->getRequest()->getParam('start');
        $request_endTimestamp = $this->getRequest()->getParam('end');

        $request_startTimestamp = strtotime("midnight", $request_startTimestamp) ;
        $request_endTimestamp   = strtotime("tomorrow", $request_endTimestamp) - 1;
         
        $request_start_Datetime = date("Y-m-d H:i:s", $request_startTimestamp);
        $request_end_Datetime = date("Y-m-d H:i:s", $request_endTimestamp);
           
        
        //get client ipids to avoid multi client users to see patients in other clients
        $sql = "e.id, p.id, e.ipid, e.epid, p.birthd, p.admission_date, p.change_date, p.traffic_status, p.isstandby, ";
        $sql .= "CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)  as first_name,";
        $sql .= "CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
        $sql .= "CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
        $sql .= "CONVERT(AES_DECRYPT(p.title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
        $sql .= "CONVERT(AES_DECRYPT(p.salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
        $sql .= "CONVERT(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
        $sql .= "CONVERT(AES_DECRYPT(p.street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
        $sql .= "CONVERT(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
        $sql .= "CONVERT(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
        $sql .= "CONVERT(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
        $sql .= "CONVERT(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
        $sql .= "CONVERT(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
        
        // if super admin check if patient is visible or not
        
        if($logininfo->usertype == 'SA')
        {
            $sql = "*,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.isadminvisible,p.traffic_status, p.isstandby, ";
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
         
        $user_patients = PatientUsers::getUserPatients($logininfo->userid); //get user's patients by permission
        
         
        $allowed_patients = Doctrine_Query::create()
        ->select($sql)
        ->from('EpidIpidMapping e')
        ->leftJoin('e.PatientMaster p')
        ->where('e.clientid = ?', $clientid)
        ->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
        // 	    ->leftJoin("e.PatientQpaMapping q")
        //->andWhere("p.isdischarged = 0 AND p.isdelete = 0 AND p.isstandby = 0 AND p.isstandbydelete = 0")
        ->andWhere("p.isdischarged = 0 AND p.isdelete = 0 AND p.isstandbydelete = 0")
        ;

        /*
         * ISPC-2332
         * can you show events from STANDBY patients also
         * only for Ligetis = module 183
         */
        $this->_clientModules = (new Modules())->get_client_modules($this->logininfo->clientid);
        if ( ! $this->_clientModules[183]) {
            $allowed_patients->andWhere("p.isstandby = 0");
        }
        
        
        $allowed_patients = $allowed_patients->fetchArray();
        
        if ( ! empty($allowed_patients)) {
            
            PatientMaster::beautifyName($allowed_patients);
             
            $patients_details = array();
             
            foreach ($allowed_patients as $v_cipid) {
                $patients_details[$v_cipid['ipid']] = $v_cipid;
            }
            
            $ipids_allowed =  array_keys($patients_details);
            
            $q = Doctrine_Query::create()->select("*")
            ->from('DoctorCustomEvents')
            ->where("clientid = ?", $clientid)
            ->andWhereIn("ipid", $ipids_allowed)
            ->andWhere('(startDate >= ? AND startDate <= ?) OR (startDate < ? AND endDate >= ?)', array($request_start_Datetime, $request_end_Datetime, $request_start_Datetime ,  $request_start_Datetime) )
            //->fetchArray()
            ;
            $customEvents = $q->fetchArray();
            
            if ( ! empty($customEvents)) {
                
                $users_ids_arr = array_unique(array_merge(array_column($customEvents, 'userid'), array_column($customEvents, 'create_user') ));
                
            	$aUsers = new User();
            	$allUsers = $aUsers->getUserByClientid($clientid, 0, false, false, $users_ids_arr); //user details of the ones that created the custom events
            	
            	foreach ($allUsers as $kUsr => $vUsr) {
            	    $finalUsersArr[$vUsr['id']] = $vUsr;
            	}
            	
            	foreach ($customEvents as $cEvent) {
            	    
            	    if (empty($cEvent['userid'])) {
            	        $cEvent['userid'] = $cEvent['create_user'];
            	    }
            	    
            	    //NOTICE THE 1 !!!!! i forgot why this was added
            	    if( 1 
            	        || ($cEvent['viewForAll'] == 1 && $cEvent['clientid'] == $clientid) 
            	        || ($cEvent['viewForAll'] == 0 && $cEvent['userid'] == $userid))
            	    {
            	        
            	        if ($cEvent['allDay'] == 1) {
            	            $allDay = true;
            	        } else {
            	            $allDay = false;
            	        }
            	
            	        if ($userid != $cEvent['userid']) {
            	            $titlePrefix = "Termin von Benutzer " . $finalUsersArr[$cEvent['userid']]['last_name'] . ", " . $finalUsersArr[$cEvent['userid']]['first_name'] . "\n";
            	            $title = $cEvent['eventTitle'];
            	        } else {
            	            $titlePrefix = "";
            	            $title = $cEvent['eventTitle'];
            	        }
            	        
            	        if ($cEvent['dayplan_inform'] == 1) {
            	            $dayplan_inform = true;
            	        } else {
            	            $dayplan_inform = false;
            	        }
            	
            	        //$patient_name = $patients_details[$cEvent['ipid']]['nice_name_epid'] ;
            	        $patient_name = $patients_details[$cEvent['ipid']]['nice_name'] ;
            	        $patient_epid = $patients_details[$cEvent['ipid']]['epid'] ;
        
            	        $titlePrefix .= $patient_name . "\n";
            	         
            	        
            	        
            	        $eventsArray[] = array(
            	            'id' => $cEvent['id'],
            	            'titlePrefix' => $titlePrefix,
            	            'title' => $title,
            	            'start' =>  strtotime($cEvent['startDate']),// date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
            	            'end' =>  strtotime($cEvent['endDate']), //date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
            	            'startHumanReadable' => date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
            	            'endHumanReadable' => date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
            	            
            	            'color' => '#33CCFF',
            	            'textColor' => 'black',
            	            'allDay' => $allDay,
            	            'viewForAll' => $cEvent['viewForAll'],
            	            'resizable' => true,
            	            'eventType' => $cEvent['eventType'], //custom event 10-11
            	            'dayplan_inform' => $dayplan_inform,
            	            
            	            'patient_id' => Pms_Uuid::encrypt($patients_details[$cEvent['ipid']] ['PatientMaster'] ['id']),
            	            
            	            'comments' => $cEvent['comments'], // this are the original so user can edit
            	            'comments_qtip' => nl2br($this->view->escape($cEvent['comments'])), // this are displayed as qtip
            	            '_patient' => [// this may be displayed on qtip
            	                'isstandby' => $patients_details[$cEvent['ipid']]['PatientMaster']['isstandby'],
            	                'traffic_status' => $patients_details[$cEvent['ipid']]['PatientMaster']['traffic_status'],
            	            ], 
            	        );
            	    }
            	}
            	
            }//endif ( ! empty($customEvents))
             
        } //endif ( ! empty($allowed_patients))
    	
    	
    	if ($returnArray === true) {
    	    return $eventsArray;
    	} else {
    	    $this->_helper->getHelper('json')->sendJson($eventsArray);
    	    exit; //for readability	 
    	}
    	
	}
	
	
	/**
	 * @author claudiu
	 * Feb 21, 2018
	 * extra @calendarName is added events
	 * 
	 * @param string $returnArray
	 * @throws Exception
	 * @return multitype:array|JsonSerializable
	 */
	public function fetchallcalendartypesAction($returnArray = false) 
	{

	    if ( ! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
	        throw new Exception('!isXmlHttpRequest', 0);
	    }
	    
	    $this->_helper->layout->setLayout('layout_ajax');
	    $this->_helper->viewRenderer->setNoRender();
	    
	    $eventsArray   = array();// this is the result
	    $tabEvents     = array();
	    
	    $start = $this->getRequest()->getParam('start');
	    $end   = $this->getRequest()->getParam('end');
	    $tabs  = $this->getRequest()->getParam('tabs');
	    
	    
	    if ( ! empty($tabs)) { 
	        
	        
            if (in_array('calendar', $tabs)) {
    	        //add all that was on #calendar, aka Benutzer tab
                $tabEvents = $this->fetchdoctorseventsAction( true , $exclude_DoctorCustomEvents = in_array('allPatientsCalendar', $tabs) );
                array_walk($tabEvents, function(&$value){
                    $value['calendarName'] = 'calendar';
                });
                $eventsArray = array_merge($eventsArray, $tabEvents);    
    	    } 
    	    
    	    if (in_array('allPatientsCalendar', $tabs)) {
    	        //add all that was on #allPatientsCalendar, aka All Patients tab
    	        $tabEvents = $this->fetchallpatientscustomAction( true );
    	        array_walk($tabEvents, function(&$value){
    	            $value['calendarName'] = 'allPatientsCalendar';
    	        });
    	        $eventsArray = array_merge($eventsArray, $tabEvents);
    	    }
    	    
    	    if (in_array('teamCalendar', $tabs)) {
    	        //add all that was on #teamCalendar, aka Team tab
    	        $tabEvents = $this->fetchteameventsAction( true );
    	        array_walk($tabEvents, function(&$value){
    	            $value['calendarName'] = 'teamCalendar';
    	        });
    	        $eventsArray = array_merge($eventsArray, $tabEvents);
    	    }
    	    
    	    if (in_array('teamShiftsCalendar', $tabs)) {
    	        //add all that was on #teamCalendar, aka Team tab
    	        $tabEvents = $this->_fetchteamshifts( true );
    	        array_walk($tabEvents, function(&$value){
    	            $value['calendarName'] = 'teamShiftsCalendar';
    	        });
    	        $eventsArray = array_merge($eventsArray, $tabEvents);
    	    }
    	    
    	    if (in_array('todosFullCalendar', $tabs)) {
    	        //add all that was on #todosFullCalendar, aka Todo tab
    	        $tabEvents = $this->fetchalltodosAction( true );
    	        array_walk($tabEvents, function(&$value){
    	            $value['calendarName'] = 'todosFullCalendar';
    	        });
    	        $eventsArray = array_merge($eventsArray, $tabEvents);
    	    }
    	    
    	    
	    }
	    

	    if ($returnArray === true) {
	        return $eventsArray;
	    } else {
	        $this->_helper->getHelper('json')->sendJson($eventsArray);
	        exit; //for readability
	    }
	}
	
	
	public function printallcalendartypesAction()
	{

	    $_startYear    = $this->getRequest()->getParam('y');
	    $_startMonth   = $this->getRequest()->getParam('m');
	    $_startDay     = $this->getRequest()->getParam('d');
	    
	    $this->view->startYear     = ! is_null($_startYear) ? $_startYear : date('Y');
	    $this->view->startMonth    = ! is_null($_startMonth) ? $_startMonth : date('m');
	    $this->view->startDay      = ! is_null($_startDay) ? $_startDay : date('d');
	    
	    $this->view->tabs          = $this->getRequest()->getParam('tabs');
	    $this->view->tabs_json     = json_encode($this->getRequest()->getParam('tabs'));
	    

	    $this->_helper->layout->setLayout('layout_teamcalendar');

	      
	   
	    //this is NOT in use ! it's for this js fn: calendarToPdf();
	    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('pdf')) {
	        
	        $canvas_base64 = $this->getRequest()->getParam('canvas64');
	        $tmp_file = $this->temporary_image_create($canvas_base64, 'base64');
	        echo ($tmp_file);
	        exit;
	    }
	    
	    
	    
	    
	}
	
	private function temporary_files_delete($folder, $age = '86400')
	{
	    if($handle = opendir($folder))
	    {
	        while(false !== ($entry = readdir($handle)))
	        {
	            $filename = $folder . '/' . $entry;
	            $mtime = @filemtime($filename);
	            if(is_file($filename) && $mtime && (time() - $mtime > $age))
	            {
	                @unlink($filename);
	            }
	        }
	        closedir($handle);
	    }
	}
	private function temporary_image_create($data, $type = 'svg', $stype = 'human')
	{
	    $tmp_file = uniqid('img' . rand(1000, 9999));
	    $tmp_file_path = APPLICATION_PATH . '/../public/temp/' . $tmp_file . '.png';
	    $tmp_folder = APPLICATION_PATH . '/../public/temp';
	    $this->temporary_files_delete($tmp_folder, '7200'); //delete all files older than 2 hours
	
	    switch($type)
	    {
	        case 'svg':
	            if(get_magic_quotes_gpc())
	            {
	                $data = stripslashes($data);
	            }
	
	            $data = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . $data;
	
	            $handle = fopen($tmp_file_path, 'w+');
	            fclose($handle);
	
	            $im = new Imagick();
	            $im->readImageBlob($data);
	            $im->setImageFormat("jpeg");
	            $im->writeImage($tmp_file_path);
	            $im->clear();
	            $im->destroy();
	
	            break;
	
	        case 'base64':
	            $data = substr($data, stripos($data, '64,') + 3);
	            $data = base64_decode($data);
	            //transparent answer image
	            $im = @imagecreatefromstring($data);
	            $rgb = imagecolorat($im, 1, 1);
	            $colors = imagecolorsforindex($im, $rgb);
	
	            if($colors['alpha'] > 0 && $colors['red'] == 0)
	            {
	                //stupid hack CHANGE THIS!!!!!
	                imagecolortransparent($im, imagecolorallocatealpha($im, 0, 0, 0, 127));
	            }
	            elseif($colors['red'] == 255)
	            {
	                imagecolortransparent($im, imagecolorallocatealpha($im, 255, 255, 255, 127));
	            }
	
	            imagepng($im, $tmp_file_path);
	            imagedestroy($im);
	
	            break;
	
	        default:
	            break;
	    }
	
	    if(is_readable($tmp_file_path))
	    {
	        return $tmp_file_path;
	    }
	    else
	    {
	        return false;
	    }
	}
	
}

?>
