<?php
class PatienttempController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
	}

	public function uploadtestfileAction()
	{
		print_r($_FILES);
		$folderpath = time();
		mkdir("uploads/umluts");
		$filename = "uploads/umluts/" . trim(utf8_decode($_FILES['filename']['name']));
		$_SESSION['filename'] = "umluts/" . trim($_FILES['filename']['name']);
		move_uploaded_file($_FILES['filename']['tmp_name'], $filename);
	}

	public function patientmasteraddAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patient', $logininfo->userid, 'canadd');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->view->act = "patient/patientmasteradd";
		$this->_helper->layout->setLayout('layout');
		$this->view->recording_date = date("d-m-Y");
		$this->view->errorclass = "ErrorDivHide";
		$this->view->terminals = array("" => "Select Terminal", "0" => "Terminal Key Number", "1" => "NonTerminal Key Number");
		$this->view->verordnetarray = Pms_CommonData::getSapvCheckBox();
		$this->view->salutations = Pms_CommonData::getSalutation();
		$this->view->genders = Pms_CommonData::getGender();
		$this->view->regions = Pms_CommonData::getRegions();
		$this->view->hours = Pms_CommonData::getHours();
		$this->view->minutes = Pms_CommonData::getMinutes();

		$lc = new Locations();
		$this->view->locationarray = $lc->getLocations($clientid, 1);

		$cl = new Client();
		$clarr = Pms_CommonData::getClientData($clientid);

		$dm = new DiagnosisIcd();
		$this->view->icddiagnosisarr = $dm->getDiagnosisData(1);

		$dm = new DiagnosisMeta();
		$this->view->diagnosismeta = $dm->getDiagnosisMetaData(1);
		$this->view->preselectregion = $clarr[0]['preregion'];

		if(!$this->getRequest()->isPost())
		{
			$this->view->admission_date = date("d.m.Y H:i:s", time());
			$this->view->adm_timeh = date("H");
			$this->view->adm_timem = date("i");
		}
		else
		{
			$this->view->adm_timeh = $_POST['rec_timeh'];
			$this->view->adm_timem = $_POST['rec_timem'];
		}

		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->clientid)
		{
			$clientid = $logininfo->clientid;
		}

		$a_diagno = array();
		if(is_array($_POST['hidd_diagnosis']))
		{
			foreach($_POST['hidd_diagnosis'] as $key => $val)
			{
				$a_diagno[$key]['diagnosis'] = $_POST['diagnosis'][$key];
				$a_diagno[$key]['icdnumber'] = $_POST['icdnumber'][$key];
				$a_diagno[$key]['hidd_diagnosis'] = $_POST['hidd_diagnosis'][$key];
				$a_diagno[$key]['icd'] = $_POST['icd'][$key];
			}
		}
		else
		{
			for($i = 0; $i < 3; $i++)
			{
				$a_diagno[$i] = array('cnt' => $i);
			}
		}

		$this->view->jscount = count($a_diagno);
		$grid = new Pms_Grid($a_diagno, 1, count($a_diagno), "listadmissiondiagnosis.html");
		$this->view->diagno = $grid->renderGrid();
		$this->view->rowcount = count($a_diagno);

		if(is_array($_POST['hidd_cid']))
		{
			$ipid = Doctrine_Query::create()
				->select('*')
				->from('ContactPersonTempMaster')
				->where('id in(' . join(",", $_POST['hidd_cid']) . ')');
			$track = $ipid->execute();
			$a_cnts = $track->toArray();

			$grid = new Pms_Grid($a_cnts, 1, count($a_cnts), "contacttemp.html");
			$this->view->contactgrid = $grid->renderGrid();
			$this->view->rowcount = count($a_cnts);
		}

		$a_medic = array();
		if(is_array($_POST['hidd_medication']))
		{
			foreach($_POST['hidd_medication'] as $key => $val)
			{
				$a_medic[$key]['medication'] = $_POST['medication'][$key];
				$a_medic[$key]['hidd_medication'] = $_POST['hidd_medication'][$key];
			}
		}
		else
		{
			for($i = 0; $i < 6; $i++)
			{
				$a_medic[$i] = array('cnt' => $i);
			}
		}

		$this->view->jsmedcount = count($a_medic);
		$grid = new Pms_Grid($a_medic, 1, count($a_medic), "medicationgrid.html");
		$this->view->medicgrid = $grid->renderGrid();
		$this->view->rowcount = count($a_medic);

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$clientname = "<span class = 'err'>Please Select Client Before Filling Form</span>";

		if($clientid > 0)
		{
			$clientarr = Pms_CommonData::getClientData($clientid);
			$clientname = $clientarr[0]['client_name'];
		}
		$this->view->clientname = $clientname;


		$fdoc = Doctrine_Query::create()
			->select('*')
			->from('ExtraformsClient')
			->where('clientid =' . $clientid . '')
			->andWhere('formid =1');
		$mncd = $fdoc->execute();

		if($mncd)
		{
			$fcarr = $mncd->toArray();

			if(count($fcarr) > 0)
			{
				$allowedform = "allowed";
			}
			else
			{
				$allowedform = "";
			}
		}
		$this->view->allowedform = $allowedform;



		if($this->getRequest()->isPost())
		{
			$patient_form = new Application_Form_PatientMaster();
			$contact_form = new Application_Form_ContactPersonMaster();
			$patient_caseform = new Application_Form_PatientCase();
			$patient_locationform = new Application_Form_PatientLocation();
			$patient_diagnosis = new Application_Form_PatientDiagnosis();
			$symptomaster_form = new Application_Form_SymptomatologyMaster();
			$symptomatology_form = new Application_Form_Symptomatology();
			$patient_insurance_form = new Application_Form_PatientHealthInsurance();
			$patient_epidipid_form = new Application_Form_EpidIpidMapping();
			$patient_medication_form = new Application_Form_Medication();
			$patient_medic_form = new Application_Form_PatientDrugPlan();
			$diagno_text = new Application_Form_DiagnosisText();
			$patientcourse = new Application_Form_PatientCourse();
			$patdiagnometa = new Application_Form_PatientDiagnosisMeta();
			$sapvver = new Application_Form_SapvVerordnung();

			/* extra forms */
			$pat_lives = new Application_Form_PatientLives();
			$pat_supply = new Application_Form_PatientSupply();
			$pat_mobility = new Application_Form_PatientMobility();
			$pat_moreinfo = new Application_Form_PatientMoreInfo();
			$pat_maintainance = new Application_Form_PatientMaintainanceStage();

			$a_post = $_POST;
			$a_post['clientid'] = $clientid;

			$this->ptnval = $patient_form->validate($a_post);
			$this->caselocation = $patient_locationform->validate($a_post);
			$this->insurance = $patient_insurance_form->validate($a_post);
			$this->diagnosis = $patient_diagnosis->validate($a_post);
			$this->medic = $patient_medic_form->validate($a_post);
			$this->casef = $patient_caseform->validate($a_post);

			if($this->ptnval && $this->caselocation && $this->casef && $this->insurance)
			{

				$patient = $patient_form->InsertData($_POST);
				$a_post['ipid'] = $patient->ipid;
				$a_post['cnts'] = $a_cnts;
				$ver = $sapvver->InsertData($a_post);
				$contact_form->InsertData($a_post);
				$patient_insurance_form->InsertData($a_post);

				$pcase = $patient_caseform->InsertData($a_post);
				$a_post['epid'] = $pcase->epid;

				$patient_epidipid_form->InsertData($a_post);
				$patient_locationform->InsertData($a_post);

				for($i = 0; $i <= sizeof($_POST['diagnosis']); $i++)
				{
					if(strlen($_POST['diagnosis'][$i]) > 0 && strlen($_POST['hidd_diagnosis'][$i]) < 1 && strlen($_POST['icd'][$i]) < 1)
					{
						$a_post['newdiagnosis'][] = $_POST['diagnosis'][$i];
						$a_post['newdiagnosistype'][] = $_POST['dtype'][$i];
					}
				}

				if(is_array($a_post['newdiagnosis']))
				{
					$dt = $diagno_text->InsertData($a_post);

					for($i = 0; $i < sizeof($dt); $i++)
					{
						$a_post['newhidd_diagnosis'][] = $dt[$i]['id'];
					}
				}

				for($i = 0; $i <= sizeof($_POST['medication']); $i++)
				{
					if(strlen($_POST['medication'][$i]) > 0 && strlen($_POST['hidd_medication'][$i]) < 1)
					{
						$a_post['newmedication'][] = $_POST['medication'][$i];
					}
				}

				if(is_array($a_post['newmedication']))
				{
					$dts = $patient_medication_form->InsertNewData($a_post);

					foreach($dts as $key => $dt)
					{
						$a_post['newhidd_medication'][] = $dt->id;
					}
				}

				$a_post['diagno_abb'] = "'AD'";

				$patient_medic_form->InsertData($a_post);
				$patient_diagnosis->insertMetaData($a_post);
				$patdiagnometa->InsertData($a_post);
				Pms_Triggers::addMetaDiagnosistocourse($a_post);
				$patient_diagnosis->InsertData($a_post);

				$pat_lives->InsertData($a_post);
				$pat_supply->InsertData($a_post);
				$pat_mobility->InsertData($a_post);
				$pat_moreinfo->InsertData($a_post);
				$pat_maintainance->InsertData($a_post);

				$userdata = Pms_CommonData::getUserData($logininfo->userid);
				$groupid = $userdata[0]['groupid'];
				$ug = new Usergroup();
				$ugdata = $ug->getUserGroupData($groupid);
				$groupname = $ugdata[0]['groupname'];

				if(trim($groupname == "Doctor") || trim($groupname == "Doktor") || trim($groupname == "Arzt") || trim(strtolower($groupname) == "qpa") || $logininfo->usertype == "SA")
				{
					$this->_redirect(APP_BASE . "patient/patientcourse?id=" . Pms_Uuid::encrypt($patient->id));
				}
				else
				{
					$this->_redirect(APP_BASE . "patient/assignpatient");
				}
			}
			else
			{
				$patient_form->assignErrorMessages();
				$patient_caseform->assignErrorMessages();
				$patient_locationform->assignErrorMessages();
				$patient_diagnosis->assignErrorMessages();
				$patient_insurance_form->assignErrorMessages($_POST);
				$this->view->errorclass = "err";
				$this->retainValues($_POST);
			}
		}

		$drop = Doctrine_Query::create()
			->select('*')
			->from('PatientReferredBy')
			->where("clientid =" . $logininfo->clientid)
			->andWhere('isdelete=0')
			->orderBy('referred_name ASC');
		$dropexec = $drop->execute();
		$referedby = array("" => "");
		
		foreach($dropexec->toArray() as $key => $val)
		{
			$referedby[$val['id']] = $val['referred_name'];
		}
		$this->view->referredbyarray = $referedby;

		$insurancedrop = Doctrine_Query::create()
			->select('*')
			->from('KbvKeytabs')
			->where("valid=0 and sn='S_KBV_VERSICHERTENSTATUS'")
			->orderBy('dn ASC');
		$statusdropexec = $insurancedrop->execute();
		$dropoid = array("" => $this->view->translate("pleaseselect"));
		foreach($statusdropexec->toArray() as $key => $val)
		{
			$dropoid[$val['v']] = $val['dn'];
		}
		$this->view->status_array = $dropoid;
		setcookie("openmenu", "m18_menu", "", "/", "www.ispc-login.de");
	}

	public function patientcourseAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);
		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$isdicharged = PatientDischarge::isDischarged($decid);
		$this->view->isdischarged = 0;
		if($isdicharged)
		{
			$this->view->isdischarged = 1;
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->view->style = 'none;';
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canadd');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		}
		else
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canadd');
			if(!$return)
			{
				$this->view->coursestyle = 'none;';
			}
		}


		$this->view->patcrclass = "active";
		$this->view->act = "patient/patientcourse?id=" . $_GET['id'];
		$this->view->callcourse = "";
		$this->view->curr_date = date("d.m.Y", time());
		$this->view->pid = $_GET['id'];

		if($this->getRequest()->isPost())
		{
			$course_form = new Application_Form_PatientCourse();
			if($course_form->validate($_POST))
			{
				$course_form->InsertData($_POST);
				$datainserted = 1;
				$courseSession = new Zend_Session_Namespace('courseSession');
				$courseSession->coursetype = array();
			}
			else
			{
				$datainserted = 0;
				$course_form->assignErrorMessages();
				$this->retainValues($_POST);
			}

			$courseSession = new Zend_Session_Namespace('courseSession');
			$courseSession->coursetype = array();
		}

		$ipid = Pms_CommonData::getIpid($decid);
		$epid = Pms_CommonData::getEpid($ipid);
		$cs = new Courseshortcuts();
		$ltrarray = $cs->getFilterCourseData();

		$letterarray = array();
		$lettersforjs = array();

		foreach($ltrarray as $key => $value)
		{
			$letterarray[$value['shortcut']] = $value['course_fullname'];
		}

		$js = new Courseshortcuts();
		$jsarr = $js->getFilterCourseData();

		foreach($jsarr as $key => $value)
		{
			$lettersforjs[] = $value['shortcut'];
		}

		$this->view->ltrjs = json_encode($lettersforjs);

		$patient = Doctrine_Query::create()
			->select("distinct(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'))")
			->from('PatientCourse')
			->where('ipid ="' . $ipid . '"')
			->orderBy('course_date ASC');
		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();

		$finalarr = array();
		$hotkeys = array();
		$postdisplay = array();
		$postcnt = 0;
		foreach($patientarray as $key => $value)
		{
			$distval = $value['distinct'];
			// echo $distval."<br>";
			$shorts = new Courseshortcuts();
			$coursearr = $shorts->getCourseDataByShortcut($distval);

			if($_POST[$distval] == 1)
			{
				$postcnt++;
				$chk = 1;
				$chkval.= $comma . "'" . $distval . "'";
				$comma = ",";
				$postdisplay[$distval] = 1;
			}
			else
			{
				$chk = 0;
				$postdisplay[$distval] = 0;
			}

			if($coursearr[0]['isfilter'] == 1)
			{
				array_push($finalarr, array(
					'cletter' => $distval,
					'ctype' => $letterarray[$distval],
					'font_color' => $coursearr[0]['font_color'],
					'isbold' => $coursearr[0]['isbold'],
					'isitalic' => $coursearr[0]['isitalic'],
					'isunderline' => $coursearr[0]['isunderline'],
					'chk' => $chk
				));
			}

			$hotkeys[] = $distval;
		}

		//print_r($finalarr);
		$this->view->hotkeysjs = json_encode($hotkeys);
		$this->view->checkcounter = count($finalarr);
		$grid = new Pms_Grid($finalarr, 1, count($finalarr), "listcoursechecks.html");
		$this->view->gridchecks = $grid->renderGrid();

		$pcourse = new PatientCourse();
		$allblocks = $pcourse->getCourseData($decid, 0);

		$grid = new Pms_Grid($allblocks, 1, count($allblocks), "listpatientcourse.html");
		$this->view->gridcourse = $grid->renderGrid();

		if($postcnt > 0)
		{
			$this->view->callcheck2 = "check2()";
		}
		else
		{
			$this->view->callcheck2 = '""';
		}

		$courseSession = new Zend_Session_Namespace('courseSession');
		if($courseSession->patientId != $decid)
		{
			$courseSession->patientId = $decid;
			$courseSession->coursetype = array();
		}

		if(is_array($_POST['course_type']) && $datainserted == 0)
		{
			foreach($_POST['course_type'] as $key => $val)
			{
				$courses[$key]['course_type'] = $_POST['course_type'][$key];
				$courses[$key]['course_title'] = $_POST['course_title'][$key];
			}
		}
		else
		{
			if(!empty($courseSession->coursetype))
			{
				$courses = $courseSession->coursetype;
			}
			else
			{
				for($i = 0; $i < 1; $i++)
				{
					$courses[$i]['course_type'] = "";
					$courses[$i]['course_title'] = "";
				}
			}
		}
		
		$this->view->coursecnt = count($courses);
		$grid1 = new Pms_Grid($courses, 1, count($courses), "listcourseSession.html");
		$this->view->gridcoursetaks = $grid1->renderGrid();


		$cs = new Courseshortcuts();
		$shortcutarray = $cs->getCourseData();
		$shortgrid = new Pms_Grid($shortcutarray, 1, count($shortcutarray), "CourseShortcuts.html");
		$this->view->cshortcuts = $shortgrid->renderGrid();

		/* ######################## Patient Information ################################# */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function coursesessionAction()
	{
		$courseSession = new Zend_Session_Namespace('courseSession');
		$courseSession->patientId = Pms_Uuid::decrypt($_GET['pid']);
		$courseSession->coursetype = array();

		foreach($_GET['ctp'] as $key => $value)
		{
			array_push($courseSession->coursetype, array('course_type' => $value, 'course_title' => $_GET['ctt'][$key]));
		}
		array_push($courseSession->coursetype, array('course_type' => "", 'course_title' => ""));
	}

	public function patientdetailsAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$this->view->patid = $decid;
		$ipid = Pms_CommonData::getIpid($decid);
		$pid = $this->view->$_GET['id'];
		$fd = new FamilyDegree();
		$this->view->familydegree = $fd->getFamilyDegrees(1);
		$verordnetarray = Pms_CommonData::getSapvCheckBox();
		$this->view->verordnetarray = $verordnetarray;

		$logininfo = new Zend_Session_Namespace('Login_Info');

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		/* ######################################################### */

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientdetails', $logininfo->userid, 'canview');
		$this->view->patmclass = "active";
		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}

		$this->view->act = "patient/patientdetails?id=" . $_GET['id'];
		$this->view->pid = $_GET['id'];
		
		/* ######################## Patient Information ################################# */
		$patientmaster = new PatientMaster();
		$patientdetails = $patientmaster->getMasterData($decid, 0);

		if($patientdetails)
		{
			$this->retainValues($patientdetails);
		}
		$this->view->sex = Pms_CommonData::getGenderById($patientdetails['sex']);

		/* ######################## Family Doctor ################################# */
		if($patientdetails['familydoc_id'])
		{
			$fdoc = new FamilyDoctor();
			$docarray = $fdoc->getFamilyDoc($patientdetails['familydoc_id']);

			$this->view->doc_firstname = utf8_decode($docarray[0]['first_name']);
			$this->view->doc_lastname = $docarray[0]['last_name'];
			$this->view->doc_phone_practice = $docarray[0]['phone_practice'];
			$this->view->doc_phone_private = $docarray[0]['phone_private'];
			$this->view->doc_fax = $docarray[0]['fax'];
			$this->view->isdoc = 1;
		}
		else
		{
			$this->view->isdoc = 0;
			$this->view->fdocmsg = $this->view->translate("nofamilydoctor");
		}

		$sav = new SapvVerordnung();
		$savarr = $sav->getLastSapvVerordnungData($ipid);

		if($savarr[0]['verordnet_von'])
		{
			$fdoc = new FamilyDoctor();
			$docarray = $fdoc->getFamilyDoc($savarr[0]['verordnet_von']);
			$this->view->verordnet_von = $docarray[0]['last_name'] . " " . $docarray[0]['first_name'];
			$this->view->vercount = 1;
			if($savarr[0]['verordnungam'] != '0000-00-00 00:00:00')
			{
				$this->view->verordnungam = date('d.m.Y', strtotime($savarr[0]['verordnungam']));
			}
			if($savarr[0]['verordnungbis'] != '0000-00-00 00:00:00')
			{
				$this->view->verordnungbis = date('d.m.Y', strtotime($savarr[0]['verordnungbis']));
			}

			$this->view->verordnet = $verordnetarray[$savarr[0]['verordnet']];
			$this->view->vid = $savarr[0]['id'];
		}
		else
		{
			$this->view->vercountmsg = $this->view->translate("sapv_error");
		}


		/* ######################## Sapv Verordnung ################################# */
		$savarr = $sav->getSapvVerordnungData($ipid);
		$this->view->savpcount = count($savarr);

		if($savarr[0]['verordnet_von'] > 0)
		{
			$grid = new Pms_Grid($savarr, 1, count($savarr), "sapvverordnunglist.html");
			$this->view->sapvverordnunglist = $grid->renderGrid();
		}

		/* ######################## Health Insurance ################################# */
		$ph = new PatientHealthInsurance();
		$phi = $ph->getPatientHealthInsurance($ipid);

		if($phi)
		{
			$this->retainValues($phi[0]);
		}
		
		/* ######################## Health Insurance ################################# */
		$loca = Doctrine::getTable('PatientLocation')->findBy('ipid', $ipid);
		$locaarray = $loca->toArray();

		$grid = new Pms_Grid($locaarray, 1, count($locaarray), "listvalidlocation.html");
		$this->view->locations = $grid->renderGrid();

		/* ######################## Patient Contact ################################# */
		$this->view->openhidediv = "openhidediv('op')";
		if($this->getRequest()->isPost())
		{
			$contact_form = new Application_Form_ContactPersonMaster();
			$a_post = $_POST;
			$a_post['ipid'] = $ipid;

			if($contact_form->validate($_POST))
			{
				$contact_form->InsertDataSingle($a_post);
				$this->view->openhidediv = "openhidediv('op')";
				$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_GET['id']);
			}
			else
			{
				$contact_form->assignErrorMessages();
				$this->view->openhidediv = "openhidediv('opn')";
				$this->view->error_message = $this->view->translate('missedsthtofill');
				$this->retainValues($_POST);
			}
		}
		$pc = new ContactPersonMaster();
		$pcs = $pc->getPatientContact($ipid);

		$contactgrid = new Pms_Grid($pcs, 1, count($pcs), "PatientContacts.html");
		$this->view->patient_contacts = $contactgrid->renderGrid();

		/* ######################## Patient Information ################################# */
		$fdoc = Doctrine_Query::create()
			->select('*')
			->from('ExtraformsClient')
			->where('clientid =' . $logininfo->clientid . '')
			->andWhere('formid =1');
		$mncd = $fdoc->execute();

		if($mncd)
		{
			$fcarr = $mncd->toArray();

			if(count($fcarr) > 0)
			{
				$allowedform = "allowed";

				/* ######################## Patient Lives ################################# */
				$pl = new PatientLives();
				$pat_lives = $pl->getpatientLivesData($ipid);
				$this->retainValues($pat_lives[0]);

				$pm = new PatientMobility();
				$pat_mob = $pm->getpatientMobilityData($ipid);
				$this->retainValues($pat_mob[0]);

				$ps = new PatientSupply();
				$pat_supply = $ps->getpatientSupplyData($ipid);
				$this->retainValues($pat_supply[0]);

				$pmf = new PatientMoreInfo();
				$pat_moreinfo = $pmf->getpatientMoreInfoData($ipid);
				$this->retainValues($pat_moreinfo[0]);

				$pms = new PatientMaintainanceStage();
				$pat_pms = $pms->getLastpatientMaintainanceStage($ipid);
				$this->retainValues($pat_pms[0]);
			}
			else
			{
				$allowedform = "";
			}
		}
		$this->view->allowedform = $allowedform;
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$pat_pmsinfo = $pms->getpatientMaintainanceStage($ipid);
		$grid = new Pms_Grid($pat_pmsinfo, 1, count($pat_pmsinfo), "carelevellist.html");
		$this->view->carelevellist = $grid->renderGrid();

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function patienteditAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$isdicharged = PatientDischarge::isDischarged($decid);
		$this->view->isdischarged = 0;
		if($isdicharged)
		{
			$this->view->isdischarged = 1;
		}
		/* ######################################################### */

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patientmaster', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$ipid = Pms_CommonData::getIpid($decid);

		$this->view->patmclass = "active";
		$this->_helper->layout->setLayout('layout');
		$this->view->salutations = Pms_CommonData::getSalutation();
		$this->view->genders = Pms_CommonData::getGender();
		$this->view->regions = Pms_CommonData::getRegions();
		$this->view->hours = Pms_CommonData::getHours();
		$this->view->minutes = Pms_CommonData::getMinutes();

		$lc = new Locations();
		$this->view->locationarray = $lc->getLocations($clientid, 1);

		$pt = new PatientLocation();
		$this->view->reasons = $pt->getReasons();
		$this->view->hospdocs = $pt->getHospDocs();

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientmaster', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$patient_form = new Application_Form_PatientMaster();
			$patloc_form = new Application_Form_PatientLocation();

			if($patient_form->validate($_POST))
			{

				$patient_form->UpdateData($_POST);

				$a_post = $_POST;
				$a_post['ipid'] = $ipid;
				$patloc_form->UpdateData($a_post);
				$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_GET['id']);
			}
			else
			{
				$patient_form->assignErrorMessages();
				$this->retainValues($_POST);

				$this->view->locstat = "";

				if($_POST['locs'] == trim("Krankenhaus"))
				{
					$this->view->locstat = "checkLocationStatus();";
				}
			}
		}

		$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
				,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
			->from('PatientMaster')
			->where('id =' . $decid);
		$patexec = $patient->execute();

		if($patexec)
		{
			$patientarray = $patexec->toArray();

			$patientarray[0]['recording_date'] = date('d-m-Y H:i:s', strtotime($patientarray[0]['recording_date']));
			$patientarray[0]['birthd'] = date('d-m-Y', strtotime($patientarray[0]['birthd']));

			$this->view->sex = $patientarray[0]['sex'];
			$this->view->nation = $patientarray[0]['nation'];
			if($patientarray[0])
			{
				$this->retainValues($patientarray[0]);
			}
			$this->view->hidd_referred_by = $patientarray[0]['referred_by'];
			$this->view->hidd_docid = $patientarray[0]['familydoc_id'];

			/* ######################################################### */
			if(strlen($_POST['rec_timeh']) < 1 || strlen($_POST['rec_timem']) < 1)
			{
				$hrtms = explode(" ", $patientarray[0]['recording_date']);
				$hrtm1 = explode(":", $hrtms[1]);
				$this->view->rec_timeh = $hrtm1[0];
				$this->view->rec_timem = $hrtm1[1];
			}
			else
			{
				$this->view->rec_timeh = $_POST['rec_timeh'];
				$this->view->rec_timem = $_POST['rec_timem'];
			}
			/* ######################################################### */
			
			$referred = Doctrine::getTable('PatientReferredBy')->find($patientarray[0]['referred_by']);
			if($referred)
			{
				$refarray = $referred->toArray();

				$this->view->referred_by = $referred['id'];
			}

			/* ######################################################### */
			$familydoc = Doctrine::getTable('FamilyDoctor')->findBy('id', $patientarray[0]['familydoc_id']);

			if($familydoc)
			{
				$docarray = $familydoc->toArray();
				$this->view->familydoc_id = $docarray[0]['last_name'];
			}
			/* ######################################################### */

			$pl = new PatientLocation();
			$patlocarray = $pl->getLastLocation($decid);

			if(count($patlocarray) > 0)
			{
				$drop = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
					->from('Locations')
					->where("id='" . $patlocarray[0]['location_id'] . "'")
					->orderBy('location ASC');
				$loc = $drop->execute();
				if($loc)
				{
					$loca = $loc->toArray();

					$this->view->location_id = $loca[0]['id'];
					$this->view->hidd_location_id = $patlocarray[0]['location_id'];
					$this->view->reason = $patlocarray[0]['reason'];
					$this->view->hospdoc = $patlocarray[0]['hospdoc'];
					$this->view->locstat = "checkLocationStatus();";
				}
			}
		}

		/* ########################## Patient Information ############################### */		
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		/* ######################################################### */		
		$drop = Doctrine_Query::create()
			->select('*')
			->from('PatientReferredBy')
			->where("clientid =" . $logininfo->clientid)
			->orderBy('referred_name ASC');

		$dropexec = $drop->execute();
		$referedby = array("" => "");
		foreach($dropexec->toArray() as $key => $val)
		{
			$referedby[$val['id']] = $val['referred_name'];
		}

		$this->view->referredbyarray = $referedby;
	}

	public function contacteditAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Contact', $logininfo->userid, 'canedit');
		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		/* ########################## Patient Information ############################### */		
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($_GET['id'], 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->act = "patient/contactedit?id=" . $_GET['id'];
		$this->_helper->layout->setLayout('layout');

		$fd = new FamilyDegree();
		$this->view->familydegree = $fd->getFamilyDegrees(1);
		$this->view->salutations = Pms_CommonData::getSalutation();
		$this->view->genders = Pms_CommonData::getGender();
		$this->view->regions = Pms_CommonData::getRegions();

		if($this->getRequest()->isPost())
		{
			$contact_form = new Application_Form_ContactPersonMaster();
			$this->cntval = $contact_form->validate($_POST);

			if($this->cntval)
			{
				if($_GET['cid'] > 0)
				{
					$contact_form->UpdateData($_POST);
				}
				else
				{
					$contact_form->InsertData($_POST);
				}
			}
			else
			{
				$contact_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$contact = Doctrine::getTable('ContactPersonMaster')->find($_GET['cid']);

		if($contact)
		{
			$contactarray = $contact->toArray();
			$contactarray['cnt_birthd'] = date('d-m-Y', strtotime($contactarray['cnt_birthd']));
			$this->retainValues($contactarray);
		}
	}

	public function patienthealtheditAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		/* ######################################################### */

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('HealthInsurance', $logininfo->userid, 'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->view->healthclass = "active";
		$this->view->act = "patient/patienthealthedit?id=" . $_GET['id'];
		$this->_helper->layout->setLayout('layout');
		$this->view->regions = Pms_CommonData::getRegions();

		if($this->getRequest()->isPost())
		{

			$patient_insurance_form = new Application_Form_PatientHealthInsurance();
			$this->insurance = $patient_insurance_form->validate($_POST);

			if($this->insurance)
			{
				$patient_insurance_form->UpdateData($_POST);
				$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_GET['id']);
			}
			else
			{
				$patient_insurance_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$ipid = Pms_CommonData::getIpid($decid);

		$health = Doctrine_Query::create()
			->select("*,AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status
						,AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') as status_added
						,AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') as ins_first_name
						,AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name
						,AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') as ins_middle_name
						,AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') as ins_last_name
						,AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip
						,AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') as ins_city
						,AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') as help1
						,AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') as help2
						,AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') as help3
						,AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') as help4")
			->from('PatientHealthInsurance')
			->where('ipid="' . $ipid . '"');
		$healthexec = $health->execute();

		if($healthexec)
		{
			$healtharray = $healthexec->toArray();

			$healtharray[0]['cardentry_date'] = date('d-m-Y', strtotime($healtharray[0]['cardentry_date']));
			$healtharray[0]['date_of_birth'] = date('d-m-Y', strtotime($healtharray[0]['date_of_birth']));
			$healtharray[0]['card_valid_till'] = date('d-m-Y', strtotime($healtharray[0]['card_valid_till']));
			$this->retainValues($healtharray[0]);
		}

		/* ########################### Patient Information ############################## */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		/* ######################################################### */
	}

	public function locationeditAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientlocation', $logininfo->userid, 'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->view->locclass = "active";
		
		/* ########################### Patient Information ############################## */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($_GET['id'], 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->act = "patient/locationedit?id=" . $_GET['id'];
		$this->_helper->layout->setLayout('layout');

		if($this->getRequest()->isPost())
		{
			$patient_form = new Application_Form_PatientLocation();
			$this->cntval = $patient_form->validate($_POST);

			if($this->cntval)
			{
				$patient_form->InsertData($_POST);
			}
			else
			{
				$patient_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$epid = Pms_CommonData::getEpidFromId($_GET['id']);
		$patient = Doctrine::getTable('PatientLocation')->findBy('epid', $epid);

		if($patient)
		{
			$locationarray = $patient->toArray();
			$locationarray['valid_from'] = date('d-m-Y', strtotime($locationarray['valid_from']));
			$locationarray['valid_till'] = date('d-m-Y', strtotime($locationarray['valid_till']));
			if(count($locationarray[0]) > 0)
			{
				$this->retainValues($locationarray[0]);
			}
		}
	}

	public function patientlocationaddAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$clientid = $logininfo->clientid;

		if($this->getRequest()->isPost())
		{
			$locations_form = new Application_Form_Locations();
			if($locations_form->validate($_POST))
			{
				$locations_form->InsertData($_POST);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "patient/patientlocation?id=" . $_GET['id']);
			}
			else
			{
				$locations_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}
	
	public function patientsymptomatologyAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$this->view->satclass = "active";
		$ipid = Pms_CommonData::getIpid($decid);

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientsymptomatology', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$a_post = $_POST;
			$a_post['ipid'] = $ipid;
			$patient_form = new Application_Form_PatientSymptomatology();
			$patient_form->InsertData($a_post);
		}

		$sm = new SymptomatologyMaster();
		$symarr = $sm->getSymptpomatology($clientid);

		$symgrid = new Pms_Grid($symarr, 1, count($symarr), "patsymptomatology.html");
		$this->view->patientsyms = $symgrid->renderGrid();
		$this->view->curr_date = date('d.m', time());

		$patsym = new Symptomatology();
		$patsymarr = $patsym->getPatientSymptpomatology($ipid);
		$cntr = 0;
		$newdatearr = array();
		foreach($patsymarr as $key => $val)
		{
			if($val['symptomid'] == $patsymarr[$key + 1]['symptomid'])
			{
				$newdatearr[$cntr]['date'] = $val['entry_date'];
				$cntr++;
			}
			else
			{
				$newdatearr[$cntr]['date'] = $val['entry_date'];
				$cntr++;
				break;
			}
		}

		$patdtgrid = new Pms_Grid($newdatearr, 1, count($newdatearr), "patsymptomatologydates.html");
		$this->view->patdates = $patdtgrid->renderGrid();

		$patsymgrid = new Pms_Grid($patsymarr, $cntr, count($patsymarr), "patsymptomatologycols.html");
		$this->view->patsym = $patsymgrid->renderGrid();

		$lastgrids = new Pms_Grid($symarr, 1, count($symarr), "patsymptomatologylast.html");
		$this->view->lastgrid = $lastgrids->renderGrid();
		$this->view->totalcount = count($symarr);

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function patientcaseeditAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientcase ', $logininfo->userid, 'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		
		$this->view->caseclass = "active";
		$this->view->act = "patient/patientcaseedit?id=" . $_GET['id'];
		$this->_helper->layout->setLayout('layout');

		/* Hours Dropdown */
		for($i = 0; $i < 24; $i++)
		{
			if($i < 10)
			{
				$app = "0";
			}
			else
			{
				$app = "";
			}
			$hrs[] = $app . $i;
		}
		$this->view->hours = $hrs;

		/* Minutes Dropdown */
		for($i = 0; $i < 60; $i++)
		{
			if($i < 10)
			{
				$app = "0";
			}
			else
			{
				$app = "";
			}
			$minutes[] = $app . $i;
		}
		$this->view->minutes = $minutes;

		if($this->getRequest()->isPost())
		{

			$patient_form = new Application_Form_PatientCase();


			$this->cntval = $patient_form->validate($_POST);



			if($this->cntval)
			{

				$patient_form->UpdateData($_POST);
			}
			else
			{

				$patient_form->assignErrorMessages();

				$this->retainValues($_POST);
			}
		}

		$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(comments,'" . Zend_Registry::get('salt') . "') as comments")
			->from('PatientCase')
			->where('id="' . $_GET['id'] . '"');
		$pat = $patient->execute();
		if($patient)
		{
			$locationarray = $pat->toArray();

			$hrtm = explode(" ", $locationarray[0]['admission_date']);

			$hrtm1 = explode(":", $hrtm[1]);
			$this->view->adm_timeh = $hrtm1[0];
			$this->view->adm_timem = $hrtm1[1];

			$locationarray['admission_date'] = date('d-m-Y', strtotime($locationarray[0]['admission_date']));
			$this->retainValues($locationarray[0]);
		}
		
		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($_GET['id'], 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function diagnosiseditAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->view->act = "patient/diagnosisedit?id=" . $_GET['id'];
		$this->_helper->layout->setLayout('layout');
		$this->view->genders = array("" => "Select Gender", "0" => "Without", "1" => "Male", "2" => "Female");
		$this->view->terminals = array("" => "Select Terminal", "0" => "Terminal Key Number", "1" => "NonTerminal Key Number");

		if($this->getRequest()->isPost())
		{
			$patient_form = new Application_Form_Diagnosis();
			$this->cntval = $patient_form->validate($_POST);

			if($this->cntval)
			{
				$patient_form->UpdateData($_POST);
			}
			else
			{
				$patient_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$patient = Doctrine::getTable('Diagnosis')->find($_GET['id']);

		if($patient)
		{
			$locationarray = $patient->toArray();
			$this->retainValues($locationarray);
		}
	}

	public function patdiagnoeditAction()
	{

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		/* Patient Information */
		$this->view->patclass = "active";
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patientdiagnosis', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($logininfo->clientid)
		{
			$clientid = $logininfo->clientid;
		}

		$ipid = Pms_CommonData::getIpid($decid);

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientdiagnosis', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$pat_diagnosis = new Application_Form_PatientDiagnosis();
			$diagno_text = new Application_Form_DiagnosisText();

			$a_post = $_POST;
			$a_post['clientid'] = $clientid;
			$a_post['ipid'] = $ipid;

			for($i = 0; $i <= sizeof($_POST['diagnosis']); $i++)
			{
				if(strlen($_POST['diagnosis'][$i]) > 0 && strlen($_POST['hidd_diagnosis'][$i]) < 1 && strlen($_POST['icd'][$i]) < 1)
				{
					$a_post['newdiagnosis'][] = $_POST['diagnosis'][$i];
					$a_post['newdiagnosistype'][] = $_POST['dtype'][$i];
				}
			}

			if(is_array($a_post['newdiagnosis']))
			{
				$dt = $diagno_text->InsertData($a_post);

				for($i = 0; $i < sizeof($dt); $i++)
				{
					$a_post['newhidd_diagnosis'][] = $dt[$i]['id'];
				}
			}
			$pat_diagnosis->UpdateData($a_post);

			$this->_redirect(APP_BASE . "patient/patientcourse?id=" . $_GET['id']);
		}

		$abb = "'HD','ND'";
		$dg = new DiagnosisType();
		$darr = $dg->getDiagnosisTypes($clientid, $abb);
		$this->view->dtypearray = $darr;
		$this->view->jdarr = json_encode($darr);

		$a_diagno = array();

		if(is_array($_POST['hidd_diagnosis']))
		{
			foreach($_POST['hidd_diagnosis'] as $key => $val)
			{
				$a_diagno[$key]['hidd_icdnumber'] = $_POST['hidd_icdnumber'][$key];
				$a_diagno[$key]['icdnumber'] = $_POST['icdnumber'][$key];
				$a_diagno[$key]['diagnosis'] = $_POST['diagnosis'][$key];
				$a_diagno[$key]['tabname'] = $_POST['tabname'][$key];
				$a_diagno[$key]['hidd_diagnosis'] = $_POST['hidd_diagnosis'][$key];
			}
		}
		else
		{
			$comma = ",";
			$ipidval = "'0'";

			if(is_array($darr))
			{
				foreach($darr as $key => $val)
				{
					$ipidval .= $comma . "'" . $val['id'] . "'";
					$comma = ",";
				}
			}

			$ipid = Pms_CommonData::getIpid($decid);
			$diagns = new PatientDiagnosis();
			$a_diagno = $diagns->getFinalData($ipid, $ipidval);

			$diagno_cnt = count($a_diagno);

			if($diagno_cnt < 6)
			{
				for($i = ($diagno_cnt + 1); $i <= 6; $i++)
				{
					$a_diagno[$i]['tabname'] = "";
					$a_diagno[$i]['icdnumber'] = "";
					$a_diagno[$i]['diagnosis'] = "";
					$a_diagno[$i]['pdid'] = "";
					$a_diagno[$i]['hidd_diagnosis'] = "";
					$a_diagno[$i]['diagnosis_type_id'] = "";
				}
			}
		}

		$this->view->jscount = count($a_diagno);
		$grid = new Pms_Grid($a_diagno, 1, count($a_diagno), "listdiagnosis.html");
		$grid->gridview->dtypearray = $darr;
		$this->view->diagno = $grid->renderGrid();
		$this->view->rowcount = count($a_diagno);

		$aabb = "'AD'";
		$dg = new DiagnosisType();
		$adarr = $dg->getDiagnosisTypes($clientid, $aabb);
		
		if(!$adarr[0]['id'])
		{
			$adarr[0]['id'] = 0;
		}

		$b_diagno = array();
		$diagns = new PatientDiagnosis();
		$b_diagno = $diagns->getFinalData($ipid, $adarr[0]['id']);


		$grid = new Pms_Grid($b_diagno, 1, count($b_diagno), "list_admissiondiagnosis.html");
		$this->view->admissiondiagnogrid = $grid->renderGrid();

		$aabb = "'DD'";
		$dg = new DiagnosisType();
		$ddarr = $dg->getDiagnosisTypes($clientid, $aabb);
		if(!$ddarr[0]['id'])
		{
			$ddarr[0]['id'] = 0;
		}
		$d_diagno = array();
		$diagns = new PatientDiagnosis();
		$d_diagno = $diagns->getFinalData($ipid, $ddarr[0]['id']);
		$grid = new Pms_Grid($d_diagno, 1, count($d_diagno), "list_dischargediagnosis.html");
		$this->view->dischargediagnogrid = $grid->renderGrid();
	}

	public function changepatdiagnoeditAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$getdid = $_GET['did'];
		$ipid = Pms_CommonData::getIpid($decid);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patientdiagnosis', $logininfo->userid, 'canview');
		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientdiagnosis', $logininfo->userid, 'canedit');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$diagno_text = new Application_Form_DiagnosisText();
			$a_post = $_POST;

			for($i = 0; $i <= count($_POST['diagnosis']); $i++)
			{
				if(strlen($_POST['diagnosis'][$i]) > 0 && strlen($_POST['hidd_diagnosis'][$i]) < 1)
				{
					$a_post['newdiagnosis'][] = $_POST['diagnosis'][$i];
					$a_post['newdiagnosistype'][] = $_POST['dtype'][$i];
				}
			}


			if(is_array($a_post['newdiagnosis']))
			{
				$dt = $diagno_text->InsertData($a_post);

				for($i = 0; $i < sizeof($dt); $i++)
				{
					$a_post['newhidd_diagnosis'][] = $dt[$i]['id'];
				}
			}

			$dform = new Application_Form_PatientDiagnosis();
			$dform->updatePatDiagnosis($a_post);
			$this->_redirect(APP_BASE . "patient/patdiagnoedit?id=" . $_GET['id']);
		}


		$patdiago = Doctrine_Query::create()
			->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
			->from('PatientDiagnosis')
			->where('id = "' . $getdid . '"');
		$pt = $patdiago->execute();



		if($pt)
		{
			$ptarr = $pt->toArray();

			$diag = new PatientDiagnosis();
			$diagarr = $diag->getFinalData($ipid, $ptarr[0]['diagnosis_type_id']);


			foreach($diagarr as $key => $val)
			{

				if($val['pdid'] == $getdid)
				{
					$this->view->icdnumber = $val['icdnumber'];
					$this->view->diagnosis = $val['diagnosis'];
					$this->view->hidd_diagnosis = $val['hidd_diagnosis'];
					$this->view->hidd_tab = $val['tabname'];
					$this->view->hidd_icdnumber1 = $val['hidd_icdnumber'];
				}
			}
		}


		$patientmaster = new PatientMaster();

		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function patientlistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
	}

	public function dischargepatientlistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientdischarge', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if(strlen($_GET['acid']) > 0)
		{

			$decid = Pms_Uuid::decrypt($_GET['acid']);
			$ipid = Pms_CommonData::getIpId($decid);

			$pt = Doctrine_Core::getTable('PatientMaster')->find($decid);
			$pt->isdischarged = 0;
			$pt->save();

			$dism = Doctrine::getTable('PatientDischarge')->findBy('ipid', $ipid);

			if($dism)
			{
				$dmarr = $dism->toArray();
			}

			if(count($dmarr) > 0)
			{
				$dis = Doctrine::getTable('PatientDischarge')->find($dmarr[0]['id']);
				$dis->delete();
			}
		}

		if($_GET['flg'] == 'sdel')
		{
			$this->view->error_message = $this->view->translate("recorddeletedsuccessfully");
		}
		else if($_GET['flg'] == 'edel')
		{
			$this->view->error_message = $this->view->translate('selectatleastone');
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
		$return = $previleges->checkPrevilege('patient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($logininfo->usertype != 'SA')
		{
			$eipd = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
				->where('userid =' . $logininfo->userid);
			$epidexec = $eipd->execute();
			$epidarray = $epidexec->toArray();

			$comma = ",";
			$epidval = "'0'";
			foreach($epidarray as $key => $val)
			{
				$epidval .= $comma . "'" . $val['epid'] . "'";
				$comma = ",";
			}
		}
		else
		{

			if($logininfo->clientid > 0)
			{
				$eipd = Doctrine_Query::create()
					->select('*')
					->from('PatientQpaMapping')
					->where('clientid =' . $logininfo->clientid);
				$epidexec = $eipd->execute();
				$epidarray = $epidexec->toArray();

				$comma = ",";
				$epidval = "'0'";
				foreach($epidarray as $key => $val)
				{
					$epidval .= $comma . "'" . $val['epid'] . "'";
					$comma = ",";
				}
			}
			else
			{
				$epidval = "'0'";
			}
		}

		$ipid = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping')
			->where("epid in (" . $epidval . ")")
			->orderby('id DESC');
		$ipid->getSqlQuery();
		$ipidexec = $ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma = ",";
		$ipidval = "'0'";

		foreach($ipidarray as $key => $val)
		{
			$ipidval .= $comma . "'" . $val['ipid'] . "'";
			$comma = ",";
		}
		$columnarray = array("pk" => "id", "fn" => "p__0", "ln" => "p__2", "ad" => "p__admission_date", "ledt" => "p__change_date", "bd" => "p__birthd", 'ed' => 'e__epid');
		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");

		$this->view->order = $orderarray[$_GET['ord']];

		$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
		$patient = Doctrine_Query::create()
			->select('count(*)')
			->from('PatientMaster p')
			->leftJoin("p.EpidIpidMapping e")
			->leftJoin("e.PatientQpaMapping q")
			->where("p.isdischarged = 0 and p.isdelete = 0");
		if($logininfo->usertype != 'SA')
		{
			$patient->andWhere('q.userid = ' . $logininfo->userid);
		}
		else
		{
			$patient->andWhere('q.clientid = ' . $logininfo->clientid);
		}
		
		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();
		
		$limit = 50;
		$patient->select("ipid,e.epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,
		CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,
		CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,
		CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,
		CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,
		CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,
		CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,
		CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip
		,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city
		,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone
		,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile
		,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as gensex");
		$patient->limit($limit);
		$patient->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
		$patient->offset($_GET['pgno'] * $limit);
		$patientlimitexec = $patient->execute();
		$patientlimit = $patientlimitexec->toArray();

		$grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "listpatient.html");
		$this->view->patientgrid = $grid->renderGrid();
		
		$this->view->navigation = $grid->dotnavigation("patientnavigation.html", 5, $_GET['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['patientlist'] = $this->view->render('patient/fetchlist.html');


		echo json_encode($response);
		exit;
	}


	public function dischargelistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientdischarge', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($logininfo->usertype != 'SA')
		{
			$where = 'ipid in (select ipid from epid_ipid where clientid=' . $logininfo->clientid . ')';
		}
		else
		{
			$where = 'ipid in (select ipid from epid_ipid where clientid=' . $logininfo->clientid . ')';
		}

		$ipid = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping')
			->where($where);
		$ipidexec = $ipid->execute();
		$ipidarray = $ipidexec->toArray();
		//print_r($ipidarray);
		
		$comma = ",";
		$ipidval = "'0'";
		foreach($ipidarray as $key => $val)
		{
			$ipidval .= $comma . "'" . $val['ipid'] . "'";
			$comma = ",";
		}

		$columnarray = array("pk" => "id", "fn" => "p__0", "ln" => "p__2", "rd" => "p__admission_date", "ledt" => "p__change_date", "bd" => "p__birthd");
		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];


		$patient = Doctrine_Query::create()
			->select('count(*)')
			->from('PatientMaster')
			->where('ipid in (' . $ipidval . ') and isdelete = 0 and isdischarged = 1');
		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();

		$limit = 50;		
		$patient->select("ipid,birthd,admission_date,change_date,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,
							CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,
							CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,
							CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,
							CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,
							CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,
							CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,
							CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip
							,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city
							,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone
							,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile
							,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex");
		$patient->limit($limit);
		$patient->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
		$patient->offset($_GET['pgno'] * $limit);
		
		$patientlimitexec = $patient->execute();
		$patientlimit = $patientlimitexec->toArray();

		$grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "listdischargepatient.html");
		$this->view->patientgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("patientnavigation.html", 5, $_GET['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['patientlist'] = $this->view->render('patient/dischargelist.html');

		echo json_encode($response);
		exit;
	}

	public function fetchdropdownAction()
	{
		$this->_helper->viewRenderer('patientmasteradd');
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if(strlen($_GET['ltr']) > 0)
		{

			$drop = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->where("isdelete=0 and clientid='" . $clientid . "' and  (trim(lower(last_name)) like trim(lower('" . $_GET['ltr'] . "%'))) or (trim(lower(first_name)) like trim(lower('" . $_GET['ltr'] . "%')))")
				->andWhere('clientid = "' . $clientid . '"')
				->andWhere("indrop = 0")
				->orderBy('last_name ASC');

			$dropexec = $drop->execute();

			$droparray = $dropexec->toArray();
			$drop_array = $droparray;
			foreach($dropexec->toArray() as $key => $val)
			{
				$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
			}
			$droparray = $drop_array;
		}
		else
		{
			$droparray = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "docdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['doctors'] = $droparray;

		echo json_encode($response);
		exit;
	}

	public function locationdropdownAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if(strlen($_GET['ltr']) > 0)
		{
			$where = "trim(lower(convert(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('" . $_GET['ltr'] . "%'))";

			$drop = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where($where)
				->andWhere("client_id='" . $clientid . "'")
				->andWhere("isdelete = 0")
				->orderBy('location ASC');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();
		}
		else
		{
			$droparray = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "locdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['locations'] = $droparray;

		echo json_encode($response);
		exit;
	}

	public function healthinsdropdownAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if(strlen($_GET['ltr']) > 0)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('HealthInsurance')
				->where('trim(lower(name)) like trim(lower("' . $_GET['ltr'] . '%"))')
				->andWhere("isdelete='0'")
				->orderBy('name ASC');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			$drop_array = $droparray;
			foreach($dropexec->toArray() as $key => $val)
			{
				$drop_array[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "utf-8");
			}
			$droparray = $drop_array;
		}
		else
		{
			$droparray = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "healthdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['healthinsurance'] = $droparray;

		echo json_encode($response);
		exit;
	}

	public function fetchreferreddropdownAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$this->_helper->viewRenderer('patientmasteradd');

		if(strlen($_GET['ltr']) > 0)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientReferredBy')
				->where("trim(lower(referred_name)) like trim(lower('" . $_GET['ltr'] . "%'))")
				->andWhere("clientid='" . $clientid . "'")
				->orderBy('referred_name ASC');

			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();
		}
		else
		{
			$droparray = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "refdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['refs'] = $droparray;

		echo json_encode($response);
		exit;
	}

	private function retainValues($values)
	{
		foreach($values as $key => $val)
		{
			if(!is_array($val))
			{
				$this->view->$key = $val;
			}
		}
	}

	public function searchpatientAction()
	{
		$this->view->style = "none";

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$this->view->clientid = $clientid;

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Searchpatient', $logininfo->userid, 'canview');
		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$where = "";
			if($logininfo->usertype != 'SA')
			{
				$where = "clientid=" . $logininfo->clientid;
			}
			else
			{
				if($logininfo->clientid > 0)
				{
					$where = "clientid=" . $logininfo->clientid;
				}
				else
				{
					$where = '1';
				}
			}

			if(strlen($_POST['epid']) > 0)
			{

				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where($where . " and trim(lower(epid))=trim(lower('" . $_POST['epid'] . "'))");
				$dropexec = $drop->execute();
				$ipidval = "'0'";
				if($dropexec)
				{
					$droparray = $dropexec->toArray();

					foreach($droparray as $key => $val)
					{
						$comma = ",";
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
					}
				}
			}

			if($_POST['died'] == 1)
			{

				if(strlen($_POST['epid']) > 0)
				{
					$where.= " and trim(lower(epid))= trim(lower('" . $_POST['epid'] . "'))";
				}
				$ipid = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where($where);
				$ipidexec = $ipid->execute();
				$ipidarray = $ipidexec->toArray();

				$comma = ",";
				$disipidval = "'0'";
				foreach($ipidarray as $key => $val)
				{
					$disipidval .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}

				$dis = Doctrine_Query::create()
					->select("*")
					->from('DischargeMethod')
					->where("clientid='" . $logininfo->clientid . "' and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod')");
				$disexec = $dis->execute();
				$disarray = $disexec->toArray();

				if(count($disarray) > 0)
				{
					$todid = $disarray[0]['id'];
				}

				$dispat = Doctrine_Query::create()
					->select("*")
					->from("PatientDischarge")
					->where("ipid in (" . $disipidval . ") and discharge_method=" . $todid);
				$dispatexec = $dispat->execute();
				//echo $dispat->getSqlQuery();
				$disipidarray = $dispatexec->toArray();
				//print_r($disipidarray);
				$comma = ",";
				$ipidval = "'0'";
				foreach($disipidarray as $key => $val)
				{
					$ipidval .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}
			}

			if(strlen($ipidval) < 1)
			{
				$ipid = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where($where);
				//echo $ipid->getSqlQuery();
				$ipidexec = $ipid->execute();
				$ipidarray = $ipidexec->toArray();

				$comma = ",";
				$ipidval = "'0'";
				foreach($ipidarray as $key => $val)
				{
					$ipidval .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}
			}

			if($todid > 0)
			{
				$where = " and isdischarged = 1 and isdelete=0";
			}
			else
			{
				$where = " and isdelete=0";
			}
			if(strlen($_POST['first_name']) > 0)
			{
				$where .=" and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['first_name'] . "%')) or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['first_name'] . "%')) or 
concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['first_name'] . "%')) or 
concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['first_name'] . "%')) or 
concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['first_name'] . "%')))";
			}

			if(strlen($_POST['last_name']) > 0)
			{
				$where .=" and (trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['last_name'] . "%')) or concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['last_name'] . "%')) or 
concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['last_name'] . "%')) or 
concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['last_name'] . "%')) or 
concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_POST['last_name'] . "%')))";
			}

			if(strlen($_POST['birthd']) > 0)
			{
				$bdate = date("Y-m-d", strtotime($_POST['birthd']));
				$where .=" and birthd LIKE '" . $bdate . "%'";
			}

			if(strlen($_POST['admission_date']) > 0)
			{
				$admidate = date("Y-m-d", strtotime($_POST['admission_date']));
				$where .=" and admission_date LIKE '" . $admidate . "%'";
			}

			$patient = Doctrine_Query::create()
				->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
							,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
				->from('PatientMaster')
				->where("ipid in (" . $ipidval . ") " . $where);
			//echo $patient->getSqlQuery();
			$patientexec = $patient->execute();
			$patientarray = $patientexec->toArray();


			$grid = new Pms_Grid($patientarray, 1, count($patientarray), "listpatientsearch.html");
			$this->view->patientgrid = $grid->renderGrid();
			$this->view->style = "";
		}
	}

	public function deletepatientAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patient', $logininfo->userid, 'candelete');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			if(count($_POST['patient_id']) < 1)
			{
				if($_GET['flg'] == 'dis')
				{
					$this->_redirect(APP_BASE . 'patient/dischargepatientlist?flg=edel');
				}
				$this->view->error_message = $this->view->translate("selectatlestone");
				$error = 1;
			}
			if($error == 0)
			{
				if($logininfo->usertype == 'SA')
				{
					foreach($_POST['patient_id'] as $key => $val)
					{
						$mod = Doctrine::getTable('PatientMaster')->find($val);
						$mod->isdelete = 1;
						$mod->save();
					}

					$this->view->error_message = $this->view->translate("recorddeletedsuccessfully");
					if($_GET['flg'] == 'dis')
					{
						$this->_redirect(APP_BASE . 'patient/dischargelist?clm=fn&ord=ASC&pgno=0&flg=sdel');
					}
				}
				else
				{

					foreach($_POST['patient_id'] as $key => $val)
					{
						$query = Doctrine_Query::create()
							->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
									,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
							->from('PatientMaster')
							->where('id=' . $val);
						$previlege = $query->execute();

						if($previlege->toArray())
						{
							$mod = Doctrine::getTable('PatientMaster')->find($val);
							$mod->isdelete = 1;
							$mod->save();

							$this->view->error_message = $this->view->translate("recorddeletedsuccessfully");
							if($_GET['flg'] == 'dis')
							{
								$this->_redirect(APP_BASE . 'patient/dischargepatientlist?clm=fn&ord=ASC&pgno=0&flg=sdel');
							}
						}
						else
						{
							$this->_redirect(APP_BASE . "error/previlege");
						}
					}
				}
			}
		}
		
		if($_GET['flg'] == 'dis')
		{
			$this->_redirect('patient/dischargelist?clm=fn&ord=ASC&pgno=0');
		}
		else
		{
			$this->_helper->viewRenderer('patientlist');
		}
	}

	public function allpatientlistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
	}

	public function fetchalllistAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$limit = 50;
		$columnarray = array("pk" => "id", "fn" => "p__0", "ln" => "p__2", "rd" => "p__admission_date", "ledt" => "p__change_date", "bd" => "p__birthd", 'ed' => 'e__epid');
		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

		$logininfo = new Zend_Session_Namespace('Login_Info');

		if($logininfo->usertype == 'SA')
		{
		}
		else
		{
		}
		
		$patient = Doctrine_Query::create()
			->select('count(*)')
			->from('PatientMaster p')
			->where('isdelete = ?', 0)
			->andWhere('isdischarged = ?', 0);
		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere('e.clientid = ' . $logininfo->clientid);

		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();


		$patient->select("e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,
							CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,
							CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,
							CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,
							CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,
							CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,
							CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,
							CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip
							,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city
							,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone
							,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile
							,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex");

		$patient->limit($limit);
		$patient->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
		$patient->offset($_GET['pgno'] * $limit);
		$patientlimitexec = $patient->execute();
		$patientlimit = $patientlimitexec->toArray();
		$newpatientlimit = array();
		//print_r($patientlimit);
		//exit;

		$grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "listallpatient.html");
		$this->view->patientgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("allpatientnavigation.html", 5, $_GET['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['patientlist'] = $this->view->render('patient/fetchalllist.html');

		echo json_encode($response);
		exit;
	}

	public function assignpatientAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('assignpatient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		$this->_helper->layout->setLayout('layout');


		$qpa = Doctrine::getTable('PatientQpaMapping')->findAll();
		$qpaarray = $qpa->toArray();
		foreach($qpaarray as $key => $val)
		{
			$mapqpa .= $comma . "'" . $val['epid'] . "'";
			$comma = ",";
		}

		if($logininfo->usertype != 'SA')
		{
			$where = "e.epid not in (" . $mapqpa . ") and e.clientid=" . $logininfo->clientid;
		}
		else
		{
			if($logininfo->usertype == 'SA' && $logininfo->clientid > 0)
			{
				$where = "e.epid not in (" . $mapqpa . ") and e.clientid=" . $logininfo->clientid;
			}
			else
			{
				$where = "e.epid not in (" . $mapqpa . ")";
			}
		}

		$ipid = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping e')
			->where($where);

		$ipidexec = $ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma = ",";
		$ipidval = "'0'";
		foreach($ipidarray as $key => $val)
		{
			$ipidval .= $comma . "'" . $val['ipid'] . "'";
			$comma = ",";
		}

		$columnarray = array("pk" => "id", "fn" => "first_name", "ln" => "last_name", "rd" => "recording_date", "bd" => "birthd");

		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

		$patient = Doctrine_Query::create()
			->select("count(*)")
			->from('PatientMaster')
			->where('ipid in (' . $ipidval . ') and isdelete=0 and isdischarged=0');
		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();

		$limit = 50;
		$patient->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex");
		$patient->limit($limit);
		$patient->offset($_GET['pgno'] * $limit);

		$patientlimitexec = $patient->execute();
		$patientlimit = $patientlimitexec->toArray();

		$grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "listunassignpatient.html");
		$this->view->patientgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("patientnavigation.html", 5, $_GET['pgno'], $limit);


		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('assignpatient', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if(strlen($_POST['epid']) < 1)
			{
				$this->view->error_epid = $this->view->translate("pleaseentervalidepid");
				$error = 1;
			}
			$dbepid = Doctrine::getTable('EpidIpidMapping')->findBy('epid', $_POST['epid']);
			$eparray = $dbepid->toArray();
			if(count($eparray) < 1)
			{
				$this->view->error_epid = $this->view->translate("pleaseentervalidepid");
				$error = 2;
			}
			else
			{

				$patient = Doctrine_Query::create()
					->select("*")
					->from('PatientMaster')
					->where("ipid ='" . $eparray[0]['ipid'] . "' and isdelete=0 and isdischarged=0");

				$patientexec = $patient->execute();
				$patientarray = $patientexec->toArray();

				if(count($patientarray) < 1)
				{
					$this->view->error_epid = $this->view->translate("pleaseentervalidpatient");
					$error = 2;
				}
			}

			if($error == 0)
			{
				$this->_redirect(APP_BASE . "patient/patienttodoctor?epid=" . Pms_Uuid::encrypt($_POST['epid']));
			}
		}
	}

	public function fetchpatientdropdownAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$this->_helper->viewRenderer('assignpatient');

		if(strlen($_GET['ltr']) > 0)
		{
			$fndrop = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where("clientid = '" . $clientid . "'");
			$fndropexec = $fndrop->execute();
			
			if($fndropexec)
			{
				$fndroparray = $fndropexec->toArray();
				$comma = ",";
				$fnipidval = "'0'";

				foreach($fndroparray as $key => $val)
				{
					$fnipidval .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}
			}

			$drop = Doctrine_Query::create()
				->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
				->from('PatientMaster p')
				->where("ipid in (" . $fnipidval . ") and isdischarged = 0 and isdelete=0")
				->andWhere("trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('" . $_GET['ltr'] . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('" . $_GET['ltr'] . "%'))")
				->orderBy('last_name ASC');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();
		}
		else
		{
			$droparray = array();
		}

		$comma = ",";
		$ipidval = "'0'";
		foreach($droparray as $key => $val)
		{
			$ipidval .= $comma . "'" . $val['ipid'] . "'";
			$comma = ",";
		}

		$epid = Doctrine_Query::create()
			->select('*')
			->from('EpidIpidMapping')
			->where('ipid in (' . $ipidval . ')');
		$epidexec = $epid->execute();

		$epidarray = $epidexec->toArray();

		$dropepidarray = array("droparray" => $droparray, "epidarray" => $epidarray);

		foreach($droparray as $dropkey => $val)
		{
			$key = Pms_DataTable::search($epidarray, $val['ipid'], 'ipid');
			$droparray[$dropkey]['epid'] = $epidarray[$key]['epid'];
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "patientdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['patient'] = $droparray;

		echo json_encode($response);
		exit;
	}

	public function patientdischargeAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$ipid = Pms_CommonData::getIpid($decid);
		$this->view->discharge_date = date("d-m-Y");
		$this->view->rec_timeh = date("H");
		$this->view->rec_timem = date("i");
		$this->view->disclass = "active";
		
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientdischarge', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->act = "patient/patientdischarge?id=" . $_GET['id'];
		$this->_helper->layout->setLayout('layout');
		$this->view->hours = Pms_CommonData::getHours();
		$this->view->minutes = Pms_CommonData::getMinutes();

		$logininfo = new Zend_Session_Namespace('Login_Info');
		if($logininfo->clientid)
		{
			$clientid = $logininfo->clientid;
		}

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientdischarge', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$patient_form = new Application_Form_PatientDischarge();
			$patdigno = new Application_Form_PatientDiagnosis();
			$diagno_text = new Application_Form_DiagnosisText();

			$this->cntval = $patient_form->validate($_POST);

			if($this->cntval)
			{
				$patient_form->InsertData($_POST);
				$a_post = $_POST;
				$a_post['diagno_abb'] = "'DD'";
				$a_post['ipid'] = $ipid;

				for($i = 0; $i <= sizeof($_POST['diagnosis']); $i++)
				{
					if(strlen($_POST['diagnosis'][$i]) > 0 && strlen($_POST['hidd_diagnosis'][$i]) < 1)
					{
						$a_post['newdiagnosis'][] = $_POST['diagnosis'][$i];
						$a_post['newdiagnosistype'][] = $_POST['dtype'][$i];
					}
				}

				if(is_array($a_post['newdiagnosis']))
				{
					$dt = $diagno_text->InsertData($a_post);

					for($i = 0; $i < sizeof($dt); $i++)
					{
						$a_post['newhidd_diagnosis'][] = $dt[$i]['id'];
					}
				}

				$patdigno->InsertData($a_post);

				$this->_redirect(APP_BASE . "patient/patientcourse?id=" . $_GET['id']);
			}
			else
			{
				$patient_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$dis = new DischargeMethod();
		$this->view->discharge_methods = $dis->getDischargeMethod($clientid, 1);

		$dl = new DischargeLocation();
		$this->view->discharge_locations = $dl->getDischargeLocation($clientid, 1);

		$pd = new PatientDischarge();
		$patientdischargearray = $pd->getPatientDischarge($ipid);

		if(count($patientdischargearray) > 0)
		{
			$this->retainValues($patientdischargearray[0]);
			$this->view->discharge_date = date("d.m.Y", strtotime($patientdischargearray[0]['discharge_date']));
			$this->view->rec_timeh = date("H", strtotime($patientdischargearray[0]['discharge_date']));
			$this->view->rec_timem = date("i", strtotime($patientdischargearray[0]['discharge_date']));
		}


		$a_diagno = array();

		if(is_array($_POST['hidd_diagnosis']))
		{
			foreach($_POST['hidd_diagnosis'] as $key => $val)
			{
				$a_diagno[$key]['icdnumber'] = $_POST['icdnumber'][$key];
				$a_diagno[$key]['diagnosis'] = $_POST['diagnosis'][$key];
				$a_diagno[$key]['hidd_diagnosis'] = $_POST['hidd_diagnosis'][$key];
				$a_diagno[$key]['icd'] = $_POST['icd'][$key];
			}
		}
		else
		{
			for($i = 0; $i < 6; $i++)
			{
				$a_diagno[$i] = array('cnt' => $i);
			}
		}

		$this->view->jscount = count($a_diagno);
		$grid = new Pms_Grid($a_diagno, 1, count($a_diagno), "listdischargediagnosis.html");
		$this->view->diagno = $grid->renderGrid();
		$this->view->rowcount = count($a_diagno);
	}

	public function patienttodoctorAction()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('assignpatient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if(strlen($_GET['epid']) > 0)
		{
			$getepid = Pms_Uuid::decrypt($_GET['epid']);
			$syspid = Doctrine::getTable('EpidIpidMapping')->findBy('epid', $getepid);
			$syspidarray = $syspid->toArray();
			$getipid = $syspidarray[0]['ipid'];
		}

		if(isset($_POST['btnsubmit']))
		{
			$getepid = $_POST['epid'];
			$syspid = Doctrine::getTable('EpidIpidMapping')->findBy('epid', $getepid);
			$syspidarray = $syspid->toArray();
			$getipid = $syspidarray[0]['ipid'];
		}


		if($_GET['flg'] == 'ass')
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('assignpatient', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$assign = new PatientQpaMapping();
			$assign->epid = $getepid;
			$assign->userid = Pms_Uuid::decrypt($_GET['id']);
			$assign->clientid = $logininfo->clientid;
			$assign->assign_date = date("Y-m-d H:i:s", time());
			$assign->save();

			$this->_redirect('patient/patienttodoctor?epid=' . $_GET['epid']);
		}

		if($_GET['flg'] == 'del')
		{

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('assignpatient', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}


			$q = Doctrine_Query::create()
				->delete('PatientQpaMapping')
				->where("userid='" . Pms_Uuid::decrypt($_GET['id']) . "' and epid='" . $getepid . "'");
			$q->execute();

			$this->_redirect('patient/patienttodoctor?epid=' . $_GET['epid']);
		}

		$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
			->from('PatientMaster')
			->where("ipid ='" . $getipid . "'");
		$patient->getSqlQuery();

		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();

		$this->view->name = $patientarray[0]['last_name'];
		$this->view->firstname = $patientarray[0]['first_name'];
		$this->view->pid = Pms_Uuid::encrypt($patientarray[0]['id']);
		$this->view->birthd = "-";
		$this->view->recoding_date = "-";
		if($patientarray[0]['birthd'] != '0000-00-00')
		{
			$this->view->birthd = date('d.m.Y', strtotime($patientarray[0]['birthd']));
		}

		if($patientarray[0]['recording_date'] != '0000-00-00 00:00:00')
		{
			$this->view->recoding_date = date('d.m.Y', strtotime($patientarray[0]['recording_date']));
		}
		$this->view->getepid = $getepid;
		$this->view->getipid = $getipid;

		$epid = Doctrine::getTable('EpidIpidMapping')->findBy('epid', "'" . $getepid . "'");
		$epidarray = $epid->toArray();

		$assignid = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid = '" . $getepid . "'");

		$assignidexec = $assignid->execute();
		$assignidarray = $assignidexec->toArray();

		$comma = ",";
		$userid = "'0'";
		foreach($assignidarray as $key => $val)
		{
			$userid.= $comma . "'" . $val['userid'] . "'";
			$comma = ",";
		}

		$assignuser = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('id in (' . $userid . ') and clientid=' . $logininfo->clientid)
			->andWhere('isdelete=0 and isactive=0');
		//echo $assignuser->getSqlQuery();
		$assignuserexec = $assignuser->execute();
		$assignuserarray = $assignuserexec->toArray();

		$grid = new Pms_Grid($assignuserarray, 1, count($assignuserarray), "listassigndoctor.html");
		$this->view->assigndoctorgrid = $grid->renderGrid();

		$comma = ",";
		$userid = "'0'";
		foreach($assignidarray as $key => $val)
		{
			$userid.= $comma . "'" . $val['userid'] . "'";
			$comma = ",";
		}

		$user = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('id not in (' . $userid . ') and clientid=' . $logininfo->clientid)
			->andWhere('isdelete=0 and isactive=0');
		//echo $user->getSqlQuery();	  		
		$userexec = $user->execute();

		$userarray = $userexec->toArray();
		//print_r($userarray);
		$grid = new Pms_Grid($userarray, 1, count($userarray), "listunassigndoctor.html");
		$this->view->doctorgrid = $grid->renderGrid();
	}

	public function notifyAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('patienttodoctor');
		if($this->getRequest()->isPost())
		{
			$usr = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
				->where("epid = '" . $_GET['epid'] . "' and assign_date BETWEEN '" . date("Y-m-d H:00:00") . "' AND '" . date("Y-m-d H:59:59") . "'");
			$usrexec = $usr->execute();

			$usrarray = $usrexec->toArray();
			foreach($usrarray as $key => $val)
			{
				$mail = new Messages();
				$mail->sender = $logininfo->userid;
				$mail->clientid = $logininfo->clientid;
				$mail->recipient = $val['userid'];
				$mail->msg_date = date("Y-m-d H:i:s", time());
				$mail->title = Pms_CommonData::aesEncrypt('Neue(r) Patient(in) in der Patientenliste');
				$mail->content = Pms_CommonData::aesEncrypt(utf8_encode('Ihnen wurde eine neuer Patient zugewiesen. Bitte nehmen Sie zeitnah mit den Angeh�rigen kontakt auf.'));
				$mail->create_date = date("Y-m-d", time());
				$mail->create_user = $logininfo->userid;
				$mail->read_msg = '1';
				$mail->save();

				if($mail->id > 0)
				{
					$user = Doctrine::getTable('User')->find($val['userid']);
					$userarray = $user->toArray();
					$this->view->msgnotify = '$.jGrowl("Neue(r) Patient(in) in der Patientenliste Ihnen wurde eine neuer Patient zugewiesen. Bitte nehmen Sie zeitnah mit den Angeh�rigen kontakt auf.", { sticky: true });';
				}
			}
		}

		$logininfo = new Zend_Session_Namespace('Login_Info');


		if(isset($_GET))
		{
			$getepid = $_GET['epid'];
			$syspid = Doctrine::getTable('EpidIpidMapping')->findBy('epid', $getepid);
			$syspidarray = $syspid->toArray();
			//print_r($syspidarray);
			$getipid = $syspidarray[0]['ipid'];
		}

		if($_GET['flg'] == 'ass')
		{
			$assign = new PatientQpaMapping();
			$assign->epid = $getepid;
			$assign->userid = $_GET['id'];
			$assign->clientid = $logininfo->clientid;
			$assign->assign_date = date("Y-m-d H:i:s", time());
			$assign->save();
		}

		if($_GET['flg'] == 'del')
		{
			$q = Doctrine_Query::create()
				->delete('PatientQpaMapping')
				->where("userid='" . $_GET['id'] . "' and epid='" . $getepid . "'");
			$q->execute();
		}

		$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
			->from('PatientMaster')
			->where('ipid', $getipid);
		$patientexec = $patient->execute();
		$patientarray = $patientexec->toArray();
		//print_r($patientarray);
		$this->view->name = $patientarray[0]['last_name'];
		$this->view->firstname = $patientarray[0]['first_name'];
		$this->view->birthd = "-";
		$this->view->recoding_date = "-";
		if($patientarray[0]['birthd'] != '0000-00-00')
		{
			$this->view->birthd = date('d.m.Y', strtotime($patientarray[0]['birthd']));
		}

		if($patientarray[0]['recording_date'] != '0000-00-00 00:00:00')
		{
			$this->view->recoding_date = date('d.m.Y', strtotime($patientarray[0]['recording_date']));
		}
		$this->view->getepid = $getepid;
		$this->view->getipid = $getipid;

		$epid = Doctrine::getTable('EpidIpidMapping')->findBy('epid', "'" . $getepid . "'");
		$epidarray = $epid->toArray();

		$assignid = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid = '" . $getepid . "'");

		$assignidexec = $assignid->execute();
		$assignidarray = $assignidexec->toArray();

		$comma = ",";
		$userid = "'0'";
		foreach($assignidarray as $key => $val)
		{
			$userid.= $comma . "'" . $val['userid'] . "'";
			$comma = ",";
		}

		$assignuser = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('id in (' . $userid . ') and clientid=' . $logininfo->clientid);

		$assignuserexec = $assignuser->execute();
		$assignuserarray = $assignuserexec->toArray();

		$grid = new Pms_Grid($assignuserarray, 1, count($assignuserarray), "listassigndoctor.html");
		$this->view->assigndoctorgrid = $grid->renderGrid();

		$comma = ",";
		$userid = "'0'";
		foreach($assignidarray as $key => $val)
		{
			$userid.= $comma . "'" . $val['userid'] . "'";
			$comma = ",";
		}

		$user = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('id not in (' . $userid . ') and clientid=' . $logininfo->clientid);

		$userexec = $user->execute();
		$userarray = $userexec->toArray();

		$grid = new Pms_Grid($userarray, 1, count($userarray), "listunassigndoctor.html");
		$this->view->doctorgrid = $grid->renderGrid();
	}

	public function familydoceditAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$isdicharged = PatientDischarge::isDischarged($decid);
		$this->view->isdischarged = 0;
		if($isdicharged)
		{
			$this->view->isdischarged = 1;
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('familydoctor', $logininfo->userid, 'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->view->act = "patient/familydocedit?id=" . $_GET['id'];

		if($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_PatientMaster();
			$a_post = $_POST;
			$fdoctor_form->UpdateFamilydoc($a_post);
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_GET['id']);
		}


		$patm = new PatientMaster();
		$patmastarr = $patm->getMasterData($decid, 0);

		if($patmastarr['familydoc_id'] > 0)
		{
			$fdoc = Doctrine::getTable('FamilyDoctor')->find($patmastarr['familydoc_id']);

			if($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$this->retainValues($fdocarray);
				$this->view->familydoc_id = $fdocarray['last_name'];
				$this->view->fdoc_caresalone = $patmastarr['fdoc_caresalone'];
				$this->view->hidd_docid = $fdocarray['id'];
			}
		}

		/* Patient Information */

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function sapvverordnungeditAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$this->view->verordnetarray = Pms_CommonData::getSapvCheckBox();

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('familydoctor', $logininfo->userid, 'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$a_post = $_POST;
			if($_POST['hidd_verordnet_von'] < 1)
			{
				$docform = new Application_Form_Familydoctor();
				$a_post['last_name'] = $_POST['verordnet_von'];
				$a_post['indrop'] = 1;
				$docinfo = $docform->InsertData($a_post);
				$a_post['hidd_verordnet_von'] = $docinfo->id;
			}

			$sav_form = new Application_Form_SapvVerordnung();
			$a_post['ipid'] = $ipid;
			$sav_form->UpdateData($a_post);
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_GET['id']);
		}

		$sav = new SapvVerordnung();
		$savarr = $sav->getSapvVerordnungById($_GET['vid']);

		if($savarr[0]['verordnet_von'])
		{
			$fdoc = new FamilyDoctor();
			$docarray = $fdoc->getFamilyDoc($savarr[0]['verordnet_von']);
			$this->view->verordnet_von = $docarray[0]['last_name'] . " " . $docarray[0]['first_name'];
			$this->view->vercount = 1;
			if($savarr[0]['verordnungam'] != '0000-00-00 00:00:00')
			{
				$this->view->verordnungam = date('d.m.Y', strtotime($savarr[0]['verordnungam']));
			}
			if($savarr[0]['verordnungbis'] != '0000-00-00 00:00:00')
			{
				$this->view->verordnungbis = date('d.m.Y', strtotime($savarr[0]['verordnungbis']));
			}

			$this->view->hidd_verordnet_von = $savarr[0]['verordnet_von'];
			$this->view->verordnet = $savarr[0]['verordnet'];
		}

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function sapvverordnungaddAction()
	{
		$this->_helper->viewRenderer('sapvverordnungedit');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$this->view->verordnetarray = Pms_CommonData::getSapvCheckBox();

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('familydoctor', $logininfo->userid, 'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$a_post = $_POST;
			if($_POST['hidd_verordnet_von'] < 1)
			{
				$docform = new Application_Form_Familydoctor();
				$a_post['last_name'] = $_POST['verordnet_von'];
				$a_post['indrop'] = 1;
				$docinfo = $docform->InsertData($a_post);
				$a_post['hidd_verordnet_von'] = $docinfo->id;
			}
			$sav_form = new Application_Form_SapvVerordnung();

			$a_post['ipid'] = $ipid;
			$sav_form->InsertData($a_post);
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_GET['id']);
		}

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}
	
	public function patientfileuploadAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientfileupload', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->view->style = 'none;';
			$previleges = new Pms_Acl_Assertion();
			$returnadd = $previleges->checkPrevilege('patientfileupload', $logininfo->userid, 'canadd');

			if(!$returnadd)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		}
		else
		{

			$previleges = new Pms_Acl_Assertion();
			$returnadd = $previleges->checkPrevilege('patientfileupload', $logininfo->userid, 'canadd');

			if(!$returnadd)
			{
				$this->view->styleadd = 'none;';
			}
		}


		$logininfo = new Zend_Session_Namespace('Login_Info');
		ini_set("upload_max_filesize", "10M");
		$this->view->pid = $_GET['id'];
		$this->view->act = "patient/patientfileupload?id=" . $_GET['id'];
		$this->view->fupclass = "active";
		$ipid = Pms_CommonData::getIpid($decid);

		/* Deletefile */
		if($_GET['did'] > 0)
		{
			$previleges = new Pms_Acl_Assertion();
			$returnadd = $previleges->checkPrevilege('patientfileupload', $logininfo->userid, 'candelete');

			if(!$returnadd)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$upload_form = new Application_Form_PatientFileUpload();
			$upload_form->deleteFile($_GET['did']);
		}

		if($_GET['doc_id'] > 0)
		{
			$patient = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('id="' . $_GET['doc_id'] . '"');
			$fl = $patient->execute();

			if($fl)
			{
				$flarr = $fl->toArray();

				$explo = explode("/", $flarr[0]['file_name']);
				$fdname = $explo[0];
				$flname = $explo[1];
			}

			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::filedownload($con_id, 'uploads/' . $fdname . '.zip', 'uploads/' . $fdname . '.zip');
				Pms_FtpFileupload::ftpconclose($con_id);
			}


			$cmd = "unzip -P " . $logininfo->filepass . " uploads/" . $fdname . ".zip;";
			exec($cmd);

			$file = file_get_contents("uploads/" . $fdname . "/" . $flname);

			ob_end_clean();

			header('content-type: application/' . filetype("uploads/" . $fdname . "/" . $flname));
			header('Content-Disposition: attachment; filename="' . $flname . '"');
			echo $file;
			$delcmd = "rm -r  uploads/" . $fdname . "; rm -r uploads/" . $fdname . ".zip;";
			exec($delcmd);
			exit;
		}

		if($this->getRequest()->isPost())
		{
			$ftype = $_SESSION['filetype'];
			if($ftype)
			{
				$filetypearr = explode("/", $ftype);
				if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
				{
					$filetype = "XLSX";
				}
				else
				{
					$filetype = $filetypearr[1];
				}
			}


			$upload_form = new Application_Form_PatientFileUpload();
			$a_post = $_POST;
			$a_post['ipid'] = $ipid;
			$a_post['filetype'] = $filetype;

			if($upload_form->validate($a_post))
			{
				$upload_form->insertData($a_post);
			}
			else
			{
				$upload_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$files = new PatientFileUpload();
		$filearray = $files->getFileData($ipid);

		$grid = new Pms_Grid($filearray, 1, count($filearray), "listpatientfiles.html");
		$this->view->patientfiles = $grid->renderGrid();

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function patientmedicationAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientmedication', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->view->pid = $_GET['id'];
		$this->view->caseclass = "active";

		if($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		
		if(strlen($_GET['mid']) > 0)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientmedication', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$mid = $_GET['mid'];
			$mod = Doctrine::getTable('PatientDrugPlan')->find($mid);
			$mod->isdelete = 1;
			$mod->save();
			$this->view->error_message = $this->view->translate("medicationdeletedsuccessfully");
			$this->_redirect("patient/patientmedication?id=" . $_GET['id']);
		}

		$medic = new PatientDrugPlan();
		$medicarr = $medic->getMedicationPlan($decid);

		$grid = new Pms_Grid($medicarr, 1, count($medicarr), "listpatientmedication.html");
		$this->view->medications = $grid->renderGrid();
		$this->view->isrecords = count($medicarr);

		$medicarr2 = $medic->getDeletedMedication($decid);
		$grid = new Pms_Grid($medicarr2, 1, count($medicarr2), "listdelpatientmedication.html");
		$this->view->delmedications = $grid->renderGrid();
	}

	public function openpdfAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->generatepdf($decid);
	}

	public function patientmedicationaddAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientmedication', $logininfo->userid, 'canadd');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		if($this->getRequest()->isPost())
		{
			$med_form = new Application_Form_PatientDrugPlan();
			$patient_medication_form = new Application_Form_Medication();

			$a_post = $_POST;
			$a_post['ipid'] = $ipid;

			if(strlen($_POST['medication']) > 0 && $_POST['hidd_medication'] < 1)
			{
				$a_post['newmedication'][] = $_POST['medication'];
			}

			if(is_array($a_post['newmedication']))
			{

				$dts = $patient_medication_form->InsertNewData($a_post);

				foreach($dts as $key => $dt)
				{
					$a_post['newhidd_medication'] = $dt->id;
				}
			}

			$med_form->InsertNewData($a_post);
			$this->_redirect(APP_BASE . 'patient/patientmedication?flg=suc&id=' . $_GET['id']);
		}
		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function patientmedicationeditAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientmedication', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$this->view->act = "patient/patientmedicationedit?id=" . $_GET['id'];

		$ipid = Pms_CommonData::getIpid($decid);

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientmedication', $logininfo->userid, 'canedit');
			$patient_medication_form = new Application_Form_Medication();

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}


			$a_post = $_POST;
			for($i = 1; $i <= count($_POST['medication']); $i++)
			{
				if(strlen($_POST['medication'][$i]) > 0 && $_POST['hidd_medication'][$i] < 1)
				{
					$a_post['newmids'][] = $_POST['drid'][$i];
					$a_post['newmedication'][] = $_POST['medication'][$i];
				}
			}

			$med_form = new Application_Form_PatientDrugPlan();
			$med_form->UpdateMultiData($a_post);
			$this->_redirect(APP_BASE . 'patient/patientmedication?flg=suc&id=' . $_GET['id']);
		}

		$pts = Doctrine::getTable('PatientDrugPlan')->find($_GET['mid']);
		if($pts)
		{
			$med = $pts->toArray();
			$this->retainValues($med);

			$medication_master_id = $med['medication_master_id'];

			$meds = Doctrine::getTable('Medication')->find($medication_master_id);
			if($meds)
			{
				$medarr = $meds->toarray();

				$this->view->medication = $medarr['name'];
				$this->view->hidd_medication = $medarr['id'];
			}
		}

		$this->view->pid = $_GET['id'];
		$this->view->caseclass = "active";
		$medic = new PatientDrugPlan();
		$medicarr = $medic->getMedicationPlan($decid);

		$grid = new Pms_Grid($medicarr, 1, count($medicarr), "listpatientmedicationedit.html");
		$this->view->medications = $grid->renderGrid();

		$this->view->cntr = count($medicarr);
		$medicarr2 = $medic->getDeletedMedication($decid);
		$grid = new Pms_Grid($medicarr2, 1, count($medicarr2), "listdelpatientmedicationedit.html");
		$this->view->delmedications = $grid->renderGrid();

		/* Patient Information */

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function removepatientAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->view->usertype = $logininfo->usertype;
		$this->view->errorclass = "ErrorDivHide";
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('removepatient', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}


		if($this->getRequest()->isPost())
		{
			if($logininfo->usertype == 'SA')
			{
				if(strlen($_POST['clientid']) < 1)
				{
					$this->view->error_clientid = "Select Client ";
					$error = 1;
				}
				if(strlen($_POST['epid']) < 1)
				{
					$this->view->error_epid = "Enter Epid ";
					$error = 1;
				}
			}
			else
			{


				if(strlen($_POST['epid']) < 1)
				{
					$this->view->error_epid = "Enter Epid ";
					$error = 1;
				}
			}

			if($error == 0)
			{
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('removepatient', $logininfo->userid, 'candelete');

				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}

				if($logininfo->usertype == 'SA')
				{
					$client = $_POST['clientid'];
				}
				else
				{
					$client = $logininfo->clientid;
				}

				$ipid = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("epid ='" . $_POST['epid'] . "'")
					->andWhere('clientid=' . $client);
				$clistexec = $ipid->execute();
				$ipid->getSqlQuery();
				$ipidarray = $clistexec->toArray();

				if(count($ipidarray) > 0)
				{
					$remove = Doctrine_Query::create()
						->update('PatientMaster')
						->set('isdelete', 1)
						->where("ipid ='" . $ipidarray[0]['ipid'] . "'");

					if($remove->execute())
					{
						$this->view->error_message = $this->view->translate("recorddeletedsuccessfully");
						$this->view->errorclass = "err";
					}
					else
					{
						$this->view->error_message = $this->view->translate('errorwhiledeletingpatient');
						$this->view->errorclass = "err";
					}
				}
				else
				{
					$this->view->error_message = $this->view->translate('invalidepiderrorwhiledeletingpatient');
					$this->view->errorclass = "err";
				}
			}
		}
	}

	public function directpatientsearchAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		if(strlen($_GET['ltr']) > 0)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where("clientid = '" . $clientid . "'")
				->andWhere("trim(lower(epid)) like trim(lower('" . $_GET['ltr'] . "%'))");
			$dropexec = $drop->execute();
			if($dropexec)
			{
				$droparray = $dropexec->toArray();

				foreach($droparray as $key => $val)
				{
					$ipidval .= $comma . "'" . $val['ipid'] . "'";
					$comma = ",";
				}
			}

			if(count($droparray) > 0)
			{
				$patient = Doctrine_Query::create()
					->select("*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
					->from('PatientMaster')
					->where('ipid in(' . $ipidval . ')')
					->andWhere('isdelete = 0')
					->andwhere('isdischarged = 0');
				$dropexec1 = $patient->execute();
				$droparray1 = $dropexec1->toArray();
			}

			$fndrop = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where("clientid = '" . $clientid . "'");
			$fndropexec = $fndrop->execute();
			if($fndropexec)
			{
				$fndroparray = $fndropexec->toArray();

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
							AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
							AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,
							AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
							AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
							AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip
							,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city
							,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone
							,AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile
							,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
				->from('PatientMaster')
				->where("isdelete = 0 and isdischarged = 0 and ipid in(" . $fnipidval . ") and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_GET['ltr'] . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_GET['ltr'] . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_GET['ltr'] . "%')) or 
concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_GET['ltr'] . "%')) or 
concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_GET['ltr'] . "%')) or 
concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_GET['ltr'] . "%')))");			
			$dropexec2 = $patient1->execute();
			//echo $patient1->getSqlQuery();
			$droparray2 = $dropexec2->toArray();
			//print_r($droparray2);					
		}
		
		if(is_array($droparray2) || is_array($droparray1))
		{
			$res = array_merge((array) $droparray2, (array) $droparray1);

			for($i = 0; $i < count($res); $i++)
			{
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

				$res[$i]['id'] = Pms_Uuid::encrypt($res[$i]['id']);
			}
		}
		else
		{
			$res = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "searchdropdiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['refs'] = $res;

		echo json_encode($response);
		exit;
	}

	public function patientlocationAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientlocation', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$epid = Pms_CommonData::getEpidFromId($decid);
		$ipid = Pms_CommonData::getIpId($decid);

		$loca = Doctrine::getTable('PatientLocation')->findBy('ipid', $ipid);
		$locaarray = $loca->toArray();

		$grid = new Pms_Grid($locaarray, 1, count($locaarray), "listvalidlocation.html");
		$this->view->location = $grid->renderGrid();

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function doctorletterAction()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('doctorletter', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($_GET['pdf'] == 1)
		{
			$filename = $this->generateLetterPdf(2, $_GET['lid']);
			$this->_redirect(APP_BASE . "patient/doctorletter?id=" . $_GET['id'] . "");
		}


		$ipid = Pms_CommonData::getIpid($decid);
		$loca = Doctrine_Query::create()
			->select("*,AES_DECRYPT(subject,'" . Zend_Registry::get('salt') . "') as subject,
				AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content,
				AES_DECRYPT(address,'" . Zend_Registry::get('salt') . "') as address")
			->from('DoctorLetter')
			->where("ipid='" . $ipid . "'");
		$locaexec = $loca->execute();
		if($locaexec)
		{
			$locaarray = $locaexec->toArray();
		}

		$grid = new Pms_Grid($locaarray, 1, count($locaarray), "listdoctorletter.html");
		$this->view->location = $grid->renderGrid();
		$this->view->docletterclass = "active";

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function doctorletteraddAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);
		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		/* ######################################################### */

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('doctorletter', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$ipid = Pms_CommonData::getIpid($decid);

		$pm = new PatientMaster();
		$ptarr = $pm->getMasterData($decid, 0);
		$this->view->errorclass = "ErrorDivHide";
		$this->view->subject = $this->view->translate('lettersubject') . $ptarr['first_name'] . " " . $ptarr['last_name'] . " (" . $ptarr['birthd'] . ")";
		$this->view->docletterclass = "active";

		if($ptarr['familydoc_id'] > 0 && !$this->getRequest()->isPost())
		{
			$fdoc = Doctrine_Core::getTable('FamilyDoctor')->find($ptarr['familydoc_id']);
			if($fdoc)
			{
				$docarr = $fdoc->toArray();
				$doc_title = "";
				$doc_salutation = "";
				$doc_title = $docarr['title'];
				$doc_salutation = $docarr['salutation'];				
				$doccity = $docarr['city'] . ", ";

				$this->view->address = "practice," . "\n" . $docarr['salutation'] . "" . $doc_title . "" . $doc_salutation . "" . $docarr['last_name'] . "\n" . $docarr['street1'] . "\n" . $docarr['zip'] . " " . $docarr['city'];
			}
		}
		$this->view->letter_date = $doccity . date('d.m.Y', time());
		$this->view->pid = $_GET['id'];
		$pc = new PatientCase();
		$casedata = $pc->getPatientCaseData($ipid);
		$admdate = date('d.m.Y', strtotime($casedata[0]['admission_date']));
		$this->view->content = str_replace("%s", $admdate, $this->view->translate('editorcontent'));

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('doctorletter', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if(isset($_POST['previewbutton']))
			{
				$this->generateLetterPdf(2, $_POST);
			}
			else
			{
				$ltrform = new Application_Form_DoctorLetter();

				if($ltrform->validate($_POST))
				{

					$chk = $ltrform->insertData($_POST);

					if($chk->status == 1)
					{
						$this->generateLetterPdf(1, $chk->id);
					}

					$this->_redirect(APP_BASE . "patient/doctorletter?id=" . $_GET['id'] . "");
				}
				else
				{
					$ltrform->assignErrorMessages();
					$this->retainValues($_POST);
					$this->view->errorclass = "ErrorDiv";
				}
			}
		}

		$cs = new Courseshortcuts();
		$shrtarr = $cs->getCourseData();
		$grid = new Pms_Grid($shrtarr, 1, count($shrtarr), "doclettershortcutchecks.html");
		$this->view->gridchecks = $grid->renderGrid();

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function doctorlettereditAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$iscl = Pms_Plugin_Acl::getClientTabmenuAccess($logininfo->clientid);
		if(!$iscl)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('doctorletter', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$ipid = Pms_CommonData::getIpid($decid);
		$this->_helper->viewRenderer('doctorletteradd');
		$this->view->errorclass = "ErrorDivHide";
		$this->view->docletterclass = "active";
		$this->view->act = "patient/doctorletteredit?id=" . $_GET['id'] . "&lid=" . $_GET['lid'];

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('doctorletter', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			if(isset($_POST['previewbutton']))
			{
				$this->generateLetterPdf(2, $_POST);
			}
			else
			{

				$ltrform = new Application_Form_DoctorLetter();

				if($ltrform->validate($_POST))
				{

					$chk = $ltrform->updateData($_POST);
					if($chk->status == 1)
					{
						$this->generateLetterPdf(1, $chk->id);
					}
					$this->_redirect(APP_BASE . "patient/doctorletter?id=" . $_GET['id'] . "");
				}
				else
				{
					$ltrform->assignErrorMessages();
					$this->view->errorclass = "ErrorDiv";
					$this->retainValues($_POST);
				}
			}
		}

		$referred = Doctrine_Query::create()
			->select("*,AES_DECRYPT(subject,'" . Zend_Registry::get('salt') . "') as subject,
				AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content,
				AES_DECRYPT(address,'" . Zend_Registry::get('salt') . "') as address")
			->from('DoctorLetter')
			->where("id='" . $_GET['lid'] . "'"); //Doctrine::getTable('DoctorLetter')->find($_GET['lid']);
		$refexec = $referred->execute();
		if($refexec)
		{
			$refarr = $refexec->toArray();

			$this->view->selectedchecks = $refarr['selectedchecks'];
			if($refarr['status'] == 0)
			{
				$this->view->ischeckedr = "checked='checked'";
			}
			
			if($refarr['status'] == 1)
			{
				$this->view->ischeckedc = "checked='checked'";
			}
			$this->retainValues($refarr[0]);
		}

		$cs = new Courseshortcuts();
		$shrtarr = $cs->getCourseData();
		$grid = new Pms_Grid($shrtarr, 1, count($shrtarr), "doclettershortcutchecks.html");
		$this->view->gridchecks = $grid->renderGrid();

		/* Patient Information */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
	}

	public function doctorlettercourseAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$epid = Pms_CommonData::getEpidFromId($decid);

		$pcourse = new PatientCourse();
		$allblocks = $pcourse->getCourseData($decid, $_GET['shrt']);

		$cs = new Courseshortcuts();
		$shrtarr = $cs->getCourseDataByShortcut($_GET['shrt']);
		//  print_r($allblocks);
		$grid = new Pms_Grid($allblocks, 1, count($allblocks), "doctorlettercourselist.html");
		$grid->gridview->course_fullname = $shrtarr[0]['course_fullname'];
		$this->view->patientgrid = $grid->renderGrid();
		//echo utf8_decode($grid->renderGrid());
		
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "doctletter";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['refs'] = $grid->renderGrid();
		$response['callBackParameters']['selectedchecks'] = $_GET['shrt'];
		$response['callBackParameters']['countblocks'] = count($allblocks);

		echo json_encode($response);
		exit;
	}

	private function generatePdf($decid)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$medic = new PatientDrugPlan();
		$medicarr = $medic->getPatientDrugPlan($decid);

		$grid = new Pms_Grid($medicarr, 1, count($medicarr), "listpatientmedicationpdf.html");
		$this->view->pdfmedications = $grid->renderGrid();

		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->tablepatientinfo = Pms_Template::createTemplate($parr, 'templates/tablepatientinfo.html');

		$htmlform = $this->view->render('patient/patientmedicationpdf.html');


		$pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('IPSC');
		$pdf->SetAuthor('ISPC');
		$pdf->SetTitle('ISPC');
		$pdf->SetSubject('ISPC');
		$pdf->SetKeywords('ISPC');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray($l);

		// set font
		$pdf->SetFont('times', '', 10);
		// add a page
		$pdf->AddPage('P', 'A4');

		//	echo $htmlform;

		$pdf->writeHTML($htmlform, true, 0, true, 0);

		$tmstmp = time();
		mkdir("uploads/" . $tmstmp);
		$pdf->Output('uploads/' . $tmstmp . '/medication' . $tmstmp . '.pdf', 'F');

		$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmstmp . ".zip uploads/" . $tmstmp . "; rm -r uploads/" . $tmstmp;

		exec($cmd);

		$zipname = $tmstmp . ".zip";
		$filename = "uploads/" . $tmstmp . ".zip";
		$con_id = Pms_FtpFileupload::ftpconnect();
		if($con_id)
		{
			$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
			Pms_FtpFileupload::ftpconclose($con_id);
		}

		$ipid = Doctrine::getTable('PatientMaster')->find($decid);
		$ipidarray = $ipid->toArray();

		if(strlen($filename) > 0)
		{
			$cust = new PatientFileUpload();
			$cust->title = Pms_CommonData::aesEncrypt($this->view->translate("medicationplan"));
			$cust->ipid = $ipidarray['ipid'];
			$cust->file_name = Pms_CommonData::aesEncrypt($tmstmp . '/medication' . $tmstmp . '.pdf');
			$cust->file_type = Pms_CommonData::aesEncrypt('pdf');
			$cust->save();
			$this->view->error_message = $this->view->translate("pdfsavesuccessfully");
		}

		ob_end_clean();
		header('content-type: application/pdf');
		$pdf->Output('medication.pdf', 'I');
		exit;
	}

	public function uploadifyAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$_SESSION['filetype'] = $_FILES['qqfile']['type'];

		$folderpath = time();
		mkdir("uploads/" . $folderpath);
		$filename = "uploads/" . $folderpath . "/" . trim($_FILES['qqfile']['name']);
		$_SESSION['filename'] = $folderpath . "/" . trim($_FILES['qqfile']['name']);
		move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);

		$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $folderpath . ".zip  uploads/" . $folderpath . "; rm -r uploads/" . $folderpath . ";";
		exec($cmd);
		$_SESSION['zipname'] = $folderpath . ".zip";
		
		echo json_encode(array(success => true));
		exit;
	}

	public function uploadfileAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$_SESSION['filetype'] = $_FILES['qqfile']['type'];
		
		$folderpath = time();
		//mkdir("uploadfile/".$folderpath);
		$filename = "uploadfile/" . trim($_FILES['qqfile']['name']);
		$_SESSION['filename'] = trim($_FILES['qqfile']['name']);
		move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);
		$_SESSION['zipname'] = $folderpath . ".zip";
		
		echo json_encode(array(success => true));
		exit;
	}

	public function importpatientAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = 21;

		if($_GET['del'] == 1)
		{
			$mdattabname = array("DoctorLetter", "PatientCourse", "PatientDiagnosis", "PatientDiagnosisMeta", "PatientDischarge", "PatientDrugPlan", "PatientFileUpload", "Sapsymptom", "Symptomatology");
			$idattabname = array("ContactPersonMaster", "PatientHealthInsurance", "PatientLives", "PatientLocation", "PatientMaintainanceStage", "PatientMaster", "PatientMobility", "PatientMoreInfo", "PatientSupply", "SapvVerordnung");
			$sysdattabname = array("PatientCase");

			$ipidval = Pms_CommonData::getclientIpid($clientid);
			$epidval = Pms_CommonData::getclientEpid($clientid);
			foreach($mdattabname as $key => $val)
			{
				$remove = Doctrine_Query::create()
					->delete($val)
					->where("ipid in (" . $ipidval . ")");
				$remove->execute();
			}

			$remove = Doctrine_Query::create()
				->delete('PatientQpaMapping')
				->where("epid in (" . $epidval . ")");
			$remove->execute();

			foreach($idattabname as $key1 => $val1)
			{
				$remove = Doctrine_Query::create()
					->delete($val1)
					->where("ipid in (" . $ipidval . ")");
				$remove->execute();
			}
		}

		if($_GET['c'] == 1)
		{
			$remove = Doctrine_Query::create()
				->delete('PatientCase')
				->where("clientid=" . $clientid);
			$remove->execute();

			$remove1 = Doctrine_Query::create()
				->delete('EpidIpidMapping')
				->where("clientid=" . $clientid);
			$remove1->execute();
		}

		if($this->getRequest()->isPost())
		{

			for($lopname = 1610; $lopname < 1670; $lopname++)
			{
				$xml = "";
				$coursearrsymp = "";
				$coursearraysymp = "";
				$filename = "forniraj/xml/" . $lopname . ".xml"; //".$lopname.".$_SESSION['filename'];
				if(!file_exists($filename))
				{
					continue;
				}
				echo "newxml/" . $lopname . ".xml";
				$error = 0;
				if($error == 0)
				{
					$xml = simplexml_load_file($filename, 'SimpleXMLElement', LIBXML_NOCDATA);

					if(strlen($xml->medicaldata->admission->user) > 0)
					{
						$asuser = Doctrine_Query::create()
							->select('*')
							->from('User')
							->where("username='" . $xml->medicaldata->admission->user . "' and clientid='" . $clientid . "'");
						$asuseexec = $asuser->execute();
						$uarray = $asuseexec->toArray();
						if(count($uarray) > 0)
						{
							$aduser = $uarray[0]['id'];
						}
					}

					$cust = new PatientMaster();
					$cust->ipid = Pms_Uuid::GenerateIpid();
					$cust->admission_date = $xml->medicaldata->admission->date;
					$cust->last_name = Pms_CommonData::aesEncrypt($xml->patientdetails->lastname);
					$cust->first_name = Pms_CommonData::aesEncrypt($xml->patientdetails->firstname);
					$cust->birthd = $xml->patientdetails->birthdate;
					$cust->street1 = Pms_CommonData::aesEncrypt($xml->patientdetails->street);
					$cust->zip = Pms_CommonData::aesEncrypt($xml->patientdetails->zip);
					$cust->city = Pms_CommonData::aesEncrypt($xml->patientdetails->city);
					$cust->phone = Pms_CommonData::aesEncrypt($xml->patientdetails->phone);
					$cust->sex = Pms_CommonData::aesEncrypt($xml->patientdetails->sex);
					$cust->create_user = $aduser;
					$cust->create_date = $xml->medicaldata->admission->date;
					$cust->save();
					$insertid = $cust->id;
					$ipid = $cust->ipid;

					/* patient health insurance */
					if(strlen($xml->patientdetails->healthinsurance->kvname) > 0 || strlen($xml->patientdetails->healthinsurance->patientsnumber) > 0)
					{
						$pque = Doctrine_Query::create()
							->select('*')
							->from('HealthInsurance')
							->where("name='" . $xml->patientdetails->healthinsurance->kvname . "'");
						$pqueexec = $pque->execute();
						$pquearray = $pqueexec->toArray();

						$pheath = new PatientHealthInsurance();
						$pheath->ipid = $ipid;
						$pheath->insurance_no = $xml->patientdetails->healthinsurance->patientsnumber;
						$pheath->insurance_status = Pms_CommonData::aesEncrypt($xml->patientdetails->healthinsurance->patientstatus);
						$pheath->company_name = Pms_CommonData::aesEncrypt($xml->patientdetails->healthinsurance->kvname);
						$pheath->companyid = $companyid;
						$pheath->ins_first_name = Pms_CommonData::aesEncrypt($xml->patientdetails->firstname);
						$pheath->ins_last_name = Pms_CommonData::aesEncrypt($xml->patientdetails->lastname);
						$pheath->save();
					}


					/* Patient Case */
					$case = new PatientCase();
					$case->admission_date = $xml->medicaldata->admission->date;
					$case->clientid = $clientid;
					$case->create_user = $aduser;
					$case->create_date = $xml->medicaldata->admission->date;
					$case->save();

					$epid = Pms_Uuid::GenerateEpid($clientid, $case->id);
					$case = Doctrine::getTable('PatientCase')->find($case->id);
					$case->epid = $epid;
					$case->save();

					/* Patient Ipid-Epid Mapping */
					$res = new EpidIpidMapping();
					$res->clientid = $clientid;
					$res->ipid = $cust->ipid;
					$res->epid = $case->epid;
					$res->create_user = $aduser;
					$res->create_date = $xml->medicaldata->admission->date;
					$res->save();

					/* family doctor */
					$fdoq = Doctrine_Query::create()
						->select('*')
						->from('FamilyDoctor')
						->where("first_name='" . $xml->patientdetails->familydoctor->firstname . "' and last_name='" . $xml->patientdetails->familydoctor->lastname . "' and city='" . $xml->patientdetails->familydoctor->city . "' and clientid='" . $clientid . "'");
					$fdoqexec = $fdoq->execute();
					$fdoqarray = $fdoqexec->toArray();

					if(count($fdoqarray) < 1)
					{
						if(strlen($xml->patientdetails->familydoctor->lastname) > 0 || strlen($xml->patientdetails->familydoctor->firstname) > 0)
						{
							$fdoc = new FamilyDoctor();
							$fdoc->clientid = $clientid;
							$fdoc->practice = $xml->patientdetails->familydoctor->practice;
							$fdoc->title = $xml->patientdetails->familydoctor->title;
							$fdoc->last_name = $xml->patientdetails->familydoctor->lastname;
							$fdoc->first_name = $xml->patientdetails->familydoctor->firstname;
							$fdoc->street1 = $xml->patientdetails->familydoctor->street;
							$fdoc->zip = $xml->patientdetails->familydoctor->zip;
							$fdoc->city = $xml->patientdetails->familydoctor->city;
							$fdoc->phone_practice = $xml->patientdetails->familydoctor->phone;
							$fdoc->fax = $xml->patientdetails->familydoctor->fax;
							$fdoc->create_user = $aduser;
							$fdoc->create_date = $xml->medicaldata->admission->date;
							$fdoc->save();
							$fmdocid = $fdoc->id;

							$q = Doctrine_Query::create()
								->update('PatientMaster')
								->set('familydoc_id', $fmdocid)
								->where("ipid='" . $ipid . "'");
							$qexec = $q->execute();
						}
					}
					else
					{

						$q = Doctrine_Query::create()
							->update('PatientMaster')
							->set('familydoc_id', $fdoqarray[0]['id'])
							->where("ipid='" . $ipid . "'");
						$qexec = $q->execute();
					}

					/* patient lives */
					$frm = new PatientLives();
					$frm->ipid = $cust->ipid;
					$frm->alone = $xml->patientdetails->patientinfo->patientlives->alone;
					$frm->house_of_relatives = $xml->patientdetails->patientinfo->patientlives->homeofrelatives;
					$frm->apartment = $xml->patientdetails->patientinfo->patientlives->flat;
					$frm->home = $xml->patientdetails->patientinfo->patientlives->home;
					$frm->create_user = $aduser;
					$frm->create_date = $xml->medicaldata->admission->date;
					$frm->save();

					/* patient stage */
					if(strlen($xml->patientdetails->patientinfo->port) > 0 || strlen($xml->patientdetails->patientinfo->peg) > 0 || strlen($xml->patientdetails->patientinfo->zvk) > 0 || strlen($xml->patientdetails->patientinfo->pumps) > 0 || strlen($xml->patientdetails->patientinfo->dk) > 0)
					{
						if(strlen($xml->patientdetails->patientinfo->port) > 0)
						{
							$stage = $xml->patientdetails->patientinfo->port;
						}
						if(strlen($xml->patientdetails->patientinfo->peg) > 0)
						{
							$stage = $xml->patientdetails->patientinfo->peg;
						}

						if(strlen($xml->patientdetails->patientinfo->zvk) > 0)
						{
							$stage = $xml->patientdetails->patientinfo->zvk;
						}

						if(strlen($xml->patientdetails->patientinfo->pumps) > 0)
						{
							$stage = $xml->patientdetails->patientinfo->pumps;
						}
						if(strlen($xml->patientdetails->patientinfo->dk) > 0)
						{
							$stage = $xml->patientdetails->patientinfo->dk;
						}
						$pmstge = new PatientMaintainanceStage();
						$pmstge->ipid = $cust->ipid;
						$pmstge->stage = $stage;
						$pmstge->fromdate = date("Y-m-d", time());
						$pmstge->create_user = $aduser;
						$pmstge->create_date = $xml->medicaldata->admission->date;
						$pmstge->save();
					}

					/* patient mobility */
					if(strlen($xml->patientdetails->patientinfo->patientmobility->bed) > 0 || strlen($xml->patientdetails->patientinfo->patientmobility->rollator) > 0 || strlen($xml->patientdetails->patientinfo->patientmobility->wheelchair) > 0 || strlen($xml->patientdetails->patientinfo->patientmobility->goable) > 0)
					{
						$pmob = new PatientMobility();
						$pmob->ipid = $cust->ipid;
						$pmob->bed = $xml->patientdetails->patientinfo->patientmobility->bed;
						$pmob->walker = $xml->patientdetails->patientinfo->patientmobility->rollator;
						$pmob->wheelchair = $xml->patientdetails->patientinfo->patientmobility->wheelchair;
						$pmob->goable = $xml->patientdetails->patientinfo->patientmobility->goable;
						$pmob->create_user = $aduser;
						$pmob->create_date = $xml->medicaldata->admission->date;
						$pmob->save();
					}

					/* patient contacts */

					for($c = 0; $c < count($xml->patientdetails->patientcontacts); $c++)
					{
						if(strlen($xml->patientdetails->patientcontacts->contact[$c]['firstname']) > 0 || strlen($xml->patientdetails->patientcontacts->contact[$c]['lastname']) > 0)
						{
							$patcont = new ContactPersonMaster();
							$patcont->ipid = $cust->ipid;
							$patcont->cnt_first_name = Pms_CommonData::aesEncrypt($xml->patientdetails->patientcontacts->contact[$c]['firstname']);
							$patcont->cnt_last_name = Pms_CommonData::aesEncrypt($xml->patientdetails->patientcontacts->contact[$c]['lastname']);
							$patcont->cnt_phone = Pms_CommonData::aesEncrypt($xml->patientdetails->patientcontacts->contact[$c]['phone']);
							$patcont->cnt_mobile = Pms_CommonData::aesEncrypt($xml->patientdetails->patientcontacts->contact[$c]['mobile']);
							$patcont->cnt_familydegree_id = $xml->patientdetails->patientcontacts->contact[$c]['type'];
							$patcont->create_user = $aduser;
							$patcont->create_date = $xml->medicaldata->admission->date;
							$patcont->save();
						}
					}
					
					/* Patient QPA Mapping */
					for($c = 0; $c < count($xml->medicaldata->assigneduser); $c++)
					{
						if(strlen($xml->medicaldata->assigneduser->user[$c]['firstname']) > 0 || strlen($xml->medicaldata->assigneduser->user[$c]['lastname']) > 0)
						{
							$asuser = Doctrine_Query::create()
								->select('*')
								->from('User')
								->where("first_name='" . $xml->medicaldata->assigneduser->user[$c]['firstname'] . "' and last_name='" . $xml->medicaldata->assigneduser->user[$c]['lastname'] . "' and username='" . $xml->medicaldata->assigneduser->user[$c]['username'] . "' and clientid='" . $clientid . "'");
							$asuseexec = $asuser->execute();
							$uarray = $asuseexec->toArray();

							if(count($uarray) < 1)
							{
								$user = new User();
								$user->username = $xml->medicaldata->assigneduser->user[$c]['username'];
								$user->first_name = $xml->medicaldata->assigneduser->user[$c]['firstname'];
								$user->last_name = $xml->medicaldata->assigneduser->user[$c]['lastname'];
								$user->clientid = $clientid;
								$user->save();
								$meduserid = $user->id;

								$res = new PatientQpaMapping();
								$res->clientid = $clientid;
								$res->epid = $case->epid;
								$res->userid = $meduserid;
								$res->save();
							}
							else
							{
								$res = new PatientQpaMapping();
								$res->clientid = $clientid;
								$res->epid = $case->epid;
								$res->userid = $uarray[0]['id'];
								$res->save();
							}
						}
					}

					/* Patient Discharge */
					if($xml->medicaldata->discharge->method)
					{
						$where = "";
						if($xml->medicaldata->discharge->method == 'T')
						{
							$where = "abbr ='TOD' and clientid='" . $clientid . "'";
						}

						if($xml->medicaldata->discharge->method == 'E')
						{
							$where = "abbr ='DIS' and clientid='" . $clientid . "'";
						}

						if(strlen($where) > 0)
						{
							$dtype = Doctrine_Query::create()
								->select('*')
								->from('DischargeMethod')
								->where($where);
							$dtypexec = $dtype->execute();
							$dtyparaay = $dtypexec->toArray();
							if(count($dtyparaay) > 0)
							{
								$dischragemethod = $dtyparaay[0]['id'];
							}
						}

						if(strlen($xml->medicaldata->discharge->location) > 0)
						{
							$dloc = Doctrine_Query::create()
								->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
								->from('DischargeLocation l ')
								->where("l.clientid='" . $clientid . "' and 
						  trim(lower(convert(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)))= trim(lower('" . $xml->medicaldata->discharge->location . "')'");
							//echo $dloc->getSqlQuery();
							$dlocexec = $dloc->execute();
							$dlocaraay = $dlocexec->toArray();

							if(count($dlocaraay) > 0)
							{
								$dischragelocation = $dlocaraay[0]['id'];
							}
							else
							{
								$location = new DischargeLocation();
								$location->location = Pms_CommonData::aesEncrypt($xml->medicaldata->discharge->location);
								$location->clientid = $clientid;
								$location->save();

								$dischragelocation = $location->id;
							}
						}

						$disc = new PatientDischarge();
						$disc->discharge_date = $xml->medicaldata->discharge->dischargedate;
						$disc->ipid = $ipid;
						$disc->discharge_method = $dischragemethod;
						$disc->discharge_location = $dischragelocation;
						$disc->discharge_comment = Pms_CommonData::aesEncrypt($xml->medicaldata->discharge->comment);

						$disc->save();

						$cust = Doctrine::getTable('PatientMaster')->find($insertid);
						$cust->isdischarged = 1;
						$cust->save();
					}

					/* Patient Course */
					for($cd = 0; $cd < count($xml->medicaldata->coursedocumentation->entry); $cd++)
					{
						if(strlen($xml->medicaldata->coursedocumentation->entry) > 0)
						{
							if(strlen($xml->medicaldata->coursedocumentation->entry[$cd]['username']) > 0)
							{
								$uquery = Doctrine_Query::create()
									->select('*')
									->from('User')
									->where("username='" . $xml->medicaldata->coursedocumentation->entry[$cd]['username'] . "' and clientid='" . $clientid . "'");
								$uqeryexec = $uquery->execute();
								$uaray = $uqeryexec->toArray();
								if(count($uaray) > 0)
								{
									$useid = $uaray[0]['id'];
								}
							}

							if($xml->medicaldata->coursedocumentation->entry[$cd]['type'] == 'D')
							{
								$coursetitle = addslashes(trim(htmlentities(base64_decode($xml->medicaldata->coursedocumentation->entry[$cd]))));
								if(strlen($coursetitle) > 0)
								{
									$courdiag = new DiagnosisText();
									$courdiag->clientid = $clientid;
									$courdiag->free_name = $coursetitle;
									$courdiag->create_user = $useid;
									$courdiag->create_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$courdiag->save();
									$freeid = $courdiag->id;

									$abb = "'AD'";
									$dg = new DiagnosisType();
									$darr = $dg->getDiagnosisTypes($clientid, $abb);
									$diagnotype = $darr[0]['id'];

									$patdia = new PatientDiagnosis();
									$patdia->ipid = $ipid;
									$patdia->tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
									$patdia->diagnosis_type_id = $diagnotype;
									$patdia->diagnosis_id = $freeid;
									$patdia->create_user = $useid;
									$patdia->create_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$patdia->save();

									$cdoc = new PatientCourse();
									$cdoc->ipid = $ipid;
									$cdoc->course_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$cdoc->course_type = Pms_CommonData::aesEncrypt($xml->medicaldata->coursedocumentation->entry[$cd]['type']);
									$cdoc->course_title = Pms_CommonData::aesEncrypt($coursetitle);
									$cdoc->user_id = $useid;
									$cdoc->save();
								}
							}
							elseif($xml->medicaldata->coursedocumentation->entry[$cd]['type'] == 'M')
							{
								$coursetitle = addslashes(trim(htmlentities(base64_decode($xml->medicaldata->coursedocumentation->entry[$cd]))));
								if(strlen($coursetitle) > 0)
								{

									$medex = explode("|", $coursetitle);

									$med = new Medication();
									$med->name = $medex[0];
									$med->clientid = $clientid;
									$med->create_user = $useid;
									$med->create_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$med->save();
									$medid = $med->id;

									$patdrug = new PatientDrugPlan();
									$patdrug->ipid = $ipid;
									$patdrug->dosage = $medex[1];
									$patdrug->medication_master_id = $medid;
									$patdrug->create_user = $useid;
									$patdrug->create_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$patdrug->save();

									$cdoc = new PatientCourse();
									$cdoc->ipid = $ipid;
									$cdoc->course_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$cdoc->course_type = Pms_CommonData::aesEncrypt($xml->medicaldata->coursedocumentation->entry[$cd]['type']);
									$cdoc->course_title = Pms_CommonData::aesEncrypt($coursetitle);
									$cdoc->user_id = $useid;
									$cdoc->save();
								}
							}
							elseif($xml->medicaldata->coursedocumentation->entry[$cd]['type'] != 'S')
							{
								$coursetitle = addslashes(trim(htmlentities(base64_decode($xml->medicaldata->coursedocumentation->entry[$cd]))));
								if(strlen($coursetitle) > 0 || strlen(trim($xml->medicaldata->coursedocumentation->entry[$cd]['type'])) > 0)
								{
									if($xml->medicaldata->coursedocumentation->entry[$cd]['type'] == 'H')
									{
										$coursetitle = addslashes(trim(htmlentities(base64_decode($xml->medicaldata->coursedocumentation->entry[$cd]))));
										if(strlen($coursetitle) > 0)
										{

											$dquery = Doctrine_Query::create()
												->select('*')
												->from('DiagnosisIcd')
												->where("description='" . $coursetitle . "'");
											$dqeryexec = $dquery->execute();
											$daray = $dqeryexec->toArray();

											if(count($daray) > 0)
											{
												$freeid = $daray[0]['id'];
												$diagnosistable = "diagnosis_icd";
											}
											else
											{

												$dicdquery = Doctrine_Query::create()
													->select('*')
													->from('Diagnosis')
													->where("description='" . $coursetitle . "'");
												$dicdqeryexec = $dicdquery->execute();
												$dicdaray = $dicdqeryexec->toArray();

												if(count($dicdaray) > 0)
												{
													$freeid = $dicdaray[0]['id'];
													$diagnosistable = "diagnosis";
												}
												else
												{

													$courdiag = new DiagnosisText();
													$courdiag->clientid = $clientid;
													$courdiag->free_name = $coursetitle;
													$courdiag->create_user = $useid;
													$courdiag->create_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
													$courdiag->save();
													$freeid = $courdiag->id;

													$diagnosistable = "diagnosis_freetext";
												}
											}
										}
										$abb = "'AD'";
										$dg = new DiagnosisType();
										$darr = $dg->getDiagnosisTypes($clientid, $abb);
										$diagnotype = $darr[0]['id'];

										$patdia = new PatientDiagnosis();
										$patdia->ipid = $ipid;
										$patdia->tabname = Pms_CommonData::aesEncrypt($diagnosistable);
										$patdia->diagnosis_type_id = $diagnotype;
										$patdia->diagnosis_id = $freeid;
										$patdia->create_user = $useid;
										$patdia->create_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
										$patdia->save();
									}

									$cdoc = new PatientCourse();
									$cdoc->ipid = $ipid;
									$cdoc->course_date = $xml->medicaldata->coursedocumentation->entry[$cd]['date'];
									$cdoc->course_type = Pms_CommonData::aesEncrypt($xml->medicaldata->coursedocumentation->entry[$cd]['type']);
									$cdoc->course_title = Pms_CommonData::aesEncrypt($coursetitle);
									$cdoc->user_id = $useid;
									$cdoc->save();
								}
							}
						}
					}

					$cdocnew = new PatientCourse();
					$cdocnew->ipid = $cust->ipid;
					$cdocnew->course_date = date("Y-m-d H:i:s", time());
					$cdocnew->course_type = addslashes(Pms_CommonData::aesEncrypt(trim("K")));
					$cdocnew->course_title = Pms_CommonData::aesEncrypt(trim("--- Importiert von Version 2.0 / Alte Patientennummer: " . $xml->patientdetails['id']));
					$cdocnew->user_id = $useid;
					$cdocnew->save();

					$pm = Doctrine_Query::create()
						->update('PatientMaster')
						->set('last_update', "'" . date("Y-m-d H:i:s") . "'")
						->set('last_update_user', $useid)
						->where("ipid = '" . $ipid . "'");
					$pm->execute();

					if(strlen($xml->medicaldata->documents->file[0]['file_name']) > 0)
					{
						$folderpath = time();
						mkdir("uploads/" . $folderpath);
					}

					for($f = 0; $f < count($xml->medicaldata->documents->file); $f++)
					{
						if(strlen($xml->medicaldata->documents->file[$f]['file_name']) > 0)
						{
							if(strlen($xml->medicaldata->documents->file[$f]['username']) > 0)
							{
								$uquery = Doctrine_Query::create()
									->select('*')
									->from('User')
									->where("username='" . $xml->medicaldata->documents->file[$f]['username'] . "' and clientid='" . $clientid . "'");
								$uqeryexec = $uquery->execute();
								$uaray = $uqeryexec->toArray();
								if(count($uaray) > 0)
								{
									$fileuseid = $uaray[0]['id'];
								}
							}

							$filehandler = fopen("uploads/" . $folderpath . "/" . trim($xml->medicaldata->documents->file[$f]['file_name']), "w");
							fwrite($filehandler, base64_decode($xml->medicaldata->documents->file[$f]->content));
							fclose($filehandler);

							$dbfilename = $folderpath . "/" . trim($xml->medicaldata->documents->file[$f]['file_name']);

							$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $folderpath . ".zip  uploads/" . $folderpath . ";";
							exec($cmd);
							$zipname = $folderpath . ".zip";

							$con_id = Pms_FtpFileupload::ftpconnect();
							if($con_id)
							{
								$upload = Pms_FtpFileupload::fileupload($con_id, 'uploads/' . $zipname, 'uploads/' . $zipname);
								Pms_FtpFileupload::ftpconclose($con_id);
							}

							if(strlen(trim($xml->medicaldata->documents->file[$f]['file_name'])) > 0)
							{
								if($xml->medicaldata->documents->file[$f]['file_type'] == "application/pdf")
								{
									$xmlfiletype = "PDF";
								}

								if($xml->medicaldata->documents->file[$f]['file_type'] == "image/jpeg")
								{
									$xmlfiletype = "JPG";
								}
							}

							$pfile = new PatientFileUpload();
							$pfile->title = Pms_CommonData::aesEncrypt(trim($xml->medicaldata->documents->file[$f]['file_name']));
							$pfile->ipid = $cust->ipid;
							$pfile->file_name = Pms_CommonData::aesEncrypt($dbfilename);
							$pfile->file_type = Pms_CommonData::aesEncrypt($xmlfiletype);
							$pfile->create_user = $fileuseid;
							$pfile->create_date = $xml->medicaldata->documents->file[$f]['upload_date'];
							$pfile->save();
						}
					}
					
					if(strlen($zipname) > 0)
					{
						$cmd = " rm -r uploads/" . $folderpath . "; rm -r uploads/" . $zipname . ";";
						exec($cmd);
					}

					if(count($xml->medicaldata->symptomatology->symp) > 0)
					{

						for($s = 0; $s < count($xml->medicaldata->symptomatology->symp); $s++)
						{
							$coursearrsymp = array();

							if(strlen($xml->medicaldata->symptomatology->symp[$s]['username']) > 0)
							{
								$uquery = Doctrine_Query::create()
									->select('*')
									->from('User')
									->where("username='" . $xml->medicaldata->symptomatology->symp[$s]['username'] . "' and clientid='" . $clientid . "'");
								$uqeryexec = $uquery->execute();
								$uaray = $uqeryexec->toArray();
								if(count($uaray) > 0)
								{
									$useid = $uaray[0]['id'];
								}
							}

							if(strlen($xml->medicaldata->symptomatology->symp[$s]['type']) > 0)
							{
								$sympquery = Doctrine_Query::create()
									->select('*')
									->from('SymptomatologyMaster')
									->where("sym_description='" . $xml->medicaldata->symptomatology->symp[$s]['type'] . "' and clientid='" . $clientid . "'");
								$sympqueryexec = $sympquery->execute();
								$symptaray = $sympqueryexec->toArray();
								if(count($symptaray) > 0)
								{
									$sympid = $symptaray[0]['id'];
								}
								else
								{

									$res = new SymptomatologyMaster();
									$res->sym_description = $xml->medicaldata->symptomatology->symp[$s]['type'];
									$res->clientid = $clientid;
									$res->entry_date = $xml->medicaldata->symptomatology->symp[$s]['date'];
									$res->save();
									$sympid = $res->id;
								}
							}

							$sympt = new Symptomatology();
							$sympt->ipid = $ipid;
							$sympt->symptomid = $sympid;
							$sympt->input_value = $xml->medicaldata->symptomatology->symp[$s];
							$sympt->entry_date = $xml->medicaldata->symptomatology->symp[$s]['date'];
							$sympt->save();


							if(strlen((string) $xml->medicaldata->symptomatology->symp[$s]) > 0)
							{
								$sympdate = strtotime($xml->medicaldata->symptomatology->symp[$s]['date']);
								$coursearrsymp['input_value'] = (string) $xml->medicaldata->symptomatology->symp[$s];
								$coursearrsymp['symptid'] = $sympid;
								$coursearraysymp[$sympdate][] = $coursearrsymp;
							}
						}

						foreach($coursearraysymp as $keysymp => $valsymp)
						{
							$input_arraysymp = serialize($valsymp);
							$pcours = new PatientCourse();
							$pcours->ipid = $ipid;
							$pcours->course_date = date("Y-m-d H:i:s", $keysymp);
							$pcours->course_type = Pms_CommonData::aesEncrypt("S");
							$pcours->isserialized = 1;
							$pcours->user_id = $useid;
							$pcours->course_title = Pms_CommonData::aesEncrypt($input_arraysymp);
							$pcours->save();
						}
					}
				}
			}
		}
	}

	private function generateLetterPdf($chk, $ids)
	{
		if(is_array($ids))
		{
			$this->retainValues($ids);
			$this->view->address = nl2br($_POST['address']);
		}
		else
		{
			$ipid = Pms_CommonData::getIpid($decid);
			$loca = Doctrine_Query::create()
				->select("*,AES_DECRYPT(subject,'" . Zend_Registry::get('salt') . "') as subject,
							AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content,
							AES_DECRYPT(address,'" . Zend_Registry::get('salt') . "') as address")
				->from('DoctorLetter')
				->where("id='" . $ids . "'");
			$locaexec = $loca->execute();
			if($locaexec)
			{
				$locaarray = $locaexec->toArray();
				$this->retainValues($locaarray[0]);
				$this->view->address = nl2br($locaarray[0]['address']);
			}
		}

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userinfo = Pms_CommonData::getUserData($logininfo->userid);

		$this->view->user_fname = $userinfo[0]['first_name'];
		$this->view->user_lname = $userinfo[0]['last_name'];
		$this->view->phone = $userinfo[0]['phone'];
		$this->view->emailid = $userinfo[0]['emailid'];

		$htmlform = $this->view->render('patient/doctorletterpdf.html');
		$pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('IPSC');
		$pdf->SetAuthor('ISPC');
		$pdf->SetTitle('ISPC');
		$pdf->SetSubject('ISPC');
		$pdf->SetKeywords('ISPC');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray('de');

		// set font
		$pdf->SetFont('times', '', 10);

		// add a page
		$pdf->AddPage('P', 'A4');
		//echo $htmlform;

		$pdf->writeHTML($htmlform, true, 0, true, 0);

		if($chk == 1)
		{
			$dlSession = new Zend_Session_Namespace('doctorLetterSession');
			mkdir("uploads/" . $dlSession->tmpstmp);
			$pdf->Output('uploads/' . $dlSession->tmpstmp . '/doctorletter' . $ids . '.pdf', 'F');
			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $dlSession->tmpstmp . ".zip uploads/" . $dlSession->tmpstmp . "; rm -r uploads/" . $dlSession->tmpstmp;
			exec($cmd);
			$zipname = $dlSession->tmpstmp . ".zip";
			$filename = "uploads/" . $dlSession->tmpstmp . ".zip";
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}
		}
		if($chk == 2)
		{
			$pdf->Output('doctorletter.pdf', 'D');
			exit;
		}
	}

	public function formoneAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);
		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		/* ######################################################### */

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->frmoneclass = "active";
		$this->view->verordnetarray = array('1' => "Beratung", '2' => "Korrdination", '3' => "Teilversorgung", "4" => "Vollversorgung");

		$parr = $patientmaster->getMasterData($decid, 0);
		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$brenmber = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid='" . $epid . "'");
		$bexec = $brenmber->execute();
		$barray = $bexec->toArray();

		$user = Doctrine::getTable('User')->find($logininfo->userid);
		if($user)
		{
			$uarray = $user->toArray();

			$this->view->betriebsstatten_nr = $uarray['betriebsstattennummer'];
		}

		$verd = new SapvVerordnung();
		$verdarray = $verd->getSapvVerordnungData($ipid);
		$beratung = "";
		$korrdination = "";
		$teilversorgung = "";
		$vollversorgung = "";
		
		foreach($verdarray as $vkey => $vval)
		{
			$verordnet[] = $vval['verordnet'];
			if($vval['verordnet'] == 1)
			{
				$beratung++;
			}
			if($vval['verordnet'] == 2)
			{
				$korrdination++;
			}
			if($vval['verordnet'] == 3)
			{
				$teilversorgung++;
			}
			if($vval['verordnet'] == 4)
			{
				$vollversorgung++;
			}
		}

		$this->view->beratung_count = $beratung;
		$this->view->korrdination_count = $korrdination;
		$this->view->teilversorgung_count = $teilversorgung;
		$this->view->vollversorgung_count = $vollversorgung;

		if($_POST['verordnet'])
		{
			foreach($_POST['verordnet'] as $vek => $verval)
			{
				$verordnet[] = $verval;
			}
			$this->view->verordnet = $verordnet;
		}
		else
		{
			$this->view->verordnet = $verordnet;
		}

		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);


		$pt = new PatientDiagnosis();
		$patarr = $pt->getPatientDiagnosisData($patientinfo['ipid']);
		$comma = ",";
		$icdarray = "0";
		foreach($patarr as $key => $val)
		{
			if($val['a_tabname'] == 'diagnosis')
			{
				$icdarray .= $comma . $val['diagnosis_id'];
				$comma = ",";
			}
		}
		$pms = new PatientMaintainanceStage();
		$pat_pmsinfo = $pms->getpatientMaintainanceStage($patientinfo['ipid']);
		if($pat_pmsinfo[0]['stage'] > 0)
		{
			$this->view->{"stage" . $pat_pmsinfo[0]['stage']} = 'checked="checked"';
		}

		$dt = new PatientDiagnosis();
		$textarr = $dt->getPatientMainDiagnosis($ipid, "diagnosis_icd");
		$did = $textarr[0]['diagnosis_id'];

		$pd = new DiagnosisIcd();
		$pdarr = $pd->getDiagnosisDataById($did);

		$this->view->icddiagnosis = $pdarr[0]['icd_primary'];


		$doc = Doctrine::getTable('PatientDischarge')->findBy('ipid', $patientinfo['ipid']);
		$patientarray = $doc->toArray();
		if(count($patientarray) > 0)
		{
			$pms = new PatientMaster();
			$this->view->daystreated = str_replace("days", "", $pms->GetTreatedDays($patientinfo['admission_date'], $patientarray[0]['discharge_date']));
		}
		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_status = $healthinsu_array[0]['insurance_status'];

		$insucom = new HealthInsurance();
		$insucomarray = $insucom->getCompanyinfofromId($healthinsu_array[0]['companyid']);
		$this->view->insurance_no = $insucomarray[0]['kvnumber'];

		list($this->view->bday, $this->view->bmonth, $this->view->byear) = explode(".", $patientinfo['birthd']);

		$patloc = Doctrine_Query::create()
			->select('*')
			->from('PatientLocation')
			->where('ipid ="' . $patientinfo['ipid'] . '"')
			->orderBy('id ASC');
		$patexe = $patloc->execute();
		$patloc = $patexe->toArray();
		if(count($patloc) > 0)
		{
			foreach($patloc as $key => $val)
			{
				$fdoc = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
					->from('Locations')
					->where("id='" . $val['location_id'] . "' and client_id='" . $logininfo->clientid . "'")
					->andWhere('isdelete=0')
					->andWhere("location_type=1")
					->orderBy('location ASC');
				//echo $fdoc->getSqlQuery();
				$loc = $fdoc->execute();
				$locationarray = $loc->toArray();
				if(count($locationarray) > 0)
				{
					$num_loc++;
				}
			}


			if($num_loc == 0)
			{
				$this->view->einweisungen_keine = 1;
			}
			if($num_loc == 1)
			{
				$this->view->einweisungen_one = 1;
			}
			if($num_loc == 2)
			{
				$this->view->einweisungen_two = 1;
			}
			if($num_loc == 3)
			{
				$this->view->einweisungen_three = 1;
			}
			if($num_loc == 4)
			{
				$this->view->einweisungen_four = 1;
			}
			if($num_loc == 5)
			{
				$this->view->einweisungen_five = 1;
			}
			if($num_loc > 5)
			{
				$this->view->einweisungen_grtfive = 1;
			}
		}
		$pl = new PatientLives();
		$pat_lives = $pl->getpatientLivesData($ipid);

		$this->view->alone = $pat_lives[0]['alone'];
		$this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
		$this->view->home = $pat_lives[0]['home'];

		$patl = new PatientLocation();
		$locid = $patl->getFirstLocation($decid);

		$ltyp = new Locations();
		$loctype = $ltyp->getLocationbyId($locid[0]['location_id']);

		$typearay = $ltyp->getLocationTypes();

		$this->view->ltypev = $loctype[0]['location_type'];

		if($this->getRequest()->isPost())
		{
			if(count($_POST['folgende_mabnahme_wurden_durch']) < 1)
			{
				$this->view->error_folgende_mabnahme_wurden_durch = "formerror";
				$error = 1;
			}
			if(count($_POST['verordnet']) < 1)
			{
				$this->view->error_verordnettop = "formerror";
				$error = 1;
			}
			if(count($_POST['erstkontakt']) < 1)
			{
				$this->view->error_erstkontakt = "formerror";
				$error = 1;
			}
			if(count($_POST['wohnsituation']) < 1)
			{
				$this->view->error_wohnsituation = "formerror";
				$error = 1;
			}
			if(count($_POST['pflegestufe']) < 1)
			{
				$this->view->error_pflegestufe = "formerror";
				$error = 1;
			}
			if(strlen($_POST['sapv_behandlungszie_ja']) < 1)
			{
				$this->view->error_sapv_behandlungszie_ja = "formerror";
				$error = 1;
			}
			if(count($_POST['betreuungsrelevante_nebendiagnosen']) < 1)
			{
				$this->view->error_betreuungsrelevante_nebendiagnosen = "formerror";
				$error = 1;
			}
			if(count($_POST['komplexes_symptomgeschehen']) < 1)
			{
				$this->view->error_komplexes_symptomgeschehen = "formerror";
				$error = 1;
			}
			if(count($_POST['weiteres_komplexes_geschehen']) < 1)
			{
				$this->view->error_weiteres_komplexes_geschehen = "formerror";
				$error = 1;
			}
			if(count($_POST['betreuungsnetz']) < 1)
			{
				$this->view->error_betreuungsnetz = "formerror";
				$error = 1;
			}
			if(count($_POST['beendigung_der_sapv_wegen']) < 1)
			{
				$this->view->error_beendigung_der_sapv_wegen = "formerror";
				$error = 1;
			}
			if(count($_POST['pfl_egestufe_b_abschluss']) < 1)
			{
				$this->view->error_pfl_egestufe_b_abschluss = "formerror";
				$error = 1;
			}
			if(count($_POST['zusatzliche_angaben_bei_verstorbenen']) < 1)
			{
				$this->view->error_zusatzliche_angaben_bei_verstorbenen = "formerror";
				$error = 1;
			}
			if(strlen($_POST['pat_wunsch_erfullt']) < 1)
			{
				$this->view->error_pat_wunsch_erfullt = "formerror";
				$error = 1;
			}
			if(strlen($_POST['sterbeort_n_wunsch']) < 1)
			{
				$this->view->error_sterbeort_n_wunsch = "formerror";
				$error = 1;
			}


			if($error == 0)
			{
				$this->generateformPdf(2, $_POST, 'Form_one', "formone_pdf.html");
			}
		}
	}

	public function formtwoAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->frmtwoclass = "active";

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$brenmber = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid='" . $epid . "'");
		$bexec = $brenmber->execute();
		$barray = $bexec->toArray();

		$user = Doctrine::getTable('User')->find($logininfo->userid);
		if($user)
		{
			$uarray = $user->toArray();
			$this->view->betriebsstatten_nr = $uarray['betriebsstattennummer'];
		}

		$cust = Doctrine_Query::create()
			->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment")
			->from('Client')
			->where('id = ' . $logininfo->clientid);
		$cust->getSqlQuery();
		$custexec = $cust->execute();
		if($custexec)
		{
			$disarray = $custexec->toArray();
			$this->view->client_name = $disarray[0]['client_name'];
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'Form_two', "formtwo_pdf.html");
		}
	}

	public function vertragAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);
		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->contclass = "active";
	}

	public function anlagethreeAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('vertrag');

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpId($decid);
		$patientmaster = new PatientMaster();
		$this->view->tablepatientinfo = $patientmaster->getMasterData($decid, 1);

		$parr = $patientmaster->getMasterData($decid, 0);

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt(addslashes("Formular Anlage 3 wurde erstellt"));
		$cust->user_id = $logininfo->userid;
		$cust->save();

		$this->generateanalagethreePdf(3, $_POST, 'Anlage_3', "anlagethreepdf.html");
	}

	public function anlagethreeaAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpId($decid);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$patientmaster = new PatientMaster();
		$this->view->tablepatientinfo = $patientmaster->getMasterData($decid, 1);

		$stam_diagno = array();
		$diagns = new PatientDiagnosis();
		$stam_diagno = $diagns->getPatientMainDiagnosis($ipid, "diagnosis_icd");

		$dd = new DiagnosisIcd();
		$ddarr = $dd->getDiagnosisDataById($stam_diagno[0]['diagnosis_id']);
		if($ddarr)
		{
			$this->view->diagnosis .= $ddarr[0]['description'] . " (" . $ddarr[0]['icd_primary'] . ")";
		}

		$dg = new DiagnosisType();
		$abb2 = "'AD','DD','HD','ND'";
		$ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);


		if(!$ddarr2[0]['id'])
		{
			$ddarr2[0]['id'] = 0;
		}

		$stam_diagno2 = array();
		$stam_diagno2 = $diagns->getFinalData($ipid, $ddarr2[0]['id']);

		foreach($stam_diagno2 as $key1 => $val1)
		{
			if(strlen($val1['diagnosis']) > 0)
			{
				$this->view->diagnosis2.= $val1['diagnosis'] . " (" . $val1['icdnumber'] . ")\n";
			}
		}
		if($this->getRequest()->isPost())
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt(addslashes("Formular Anlage 3a wurde erstellt"));
			$cust->user_id = $logininfo->userid;
			$cust->save();

			$this->generateanalagethreePdf(3, $_POST, 'Anlage_3a', "anlagethreeapdf.html");
		}
	}

	public function anlagefourAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patient_name = $parr['last_name'] . " " . $parr['first_name'];

		if($this->getRequest()->isPost())
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt(addslashes("Formular Anlage 4 wurde erstellt"));
			$cust->user_id = $logininfo->userid;
			$cust->save();

			$this->generateformPdf(3, $_POST, 'Anlage_4(Teil 1)', "anlagefourpdf.html");
		}

		$pl = new PatientLives();
		$pat_lives = $pl->getpatientLivesData($ipid);
		$this->view->alone = $pat_lives[0]['alone'];
		$this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];

		$ps = new PatientSupply();
		$pat_supply = $ps->getpatientSupplyData($ipid);
		$this->view->nursing = $pat_supply[0]['nursing'];

		$this->view->nooptionselected = 0;

		if($pat_lives[0]['alone'] == 0 && $pat_lives[0]['house_of_relatives'] == 0 && $pat_supply[0]['nursing'] == 0)
		{
			$this->view->nooptionselected = 1;
		}

		$pm = new PatientMaster();
		$pmarr = $pm->getMasterData($decid, 0);
		if($pmarr['living_will'] == 1)
		{
			$this->view->living_will = "checked='checked'";
		}
	}

	public function formthreeAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$this->view->frmthreeclass = "active";

		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patient_name = $parr['last_name'] . " " . $parr['first_name'];
		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'formthree', "formthreepdf.html");
		}
	}

	public function stammblattAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$ipid = Pms_CommonData::getIpId($decid);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->stmblclass = "active";

		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->city = $parr['city'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$brenmber = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid='" . $epid . "'");
		$bexec = $brenmber->execute();
		$barray = $bexec->toArray();

		$user = Doctrine::getTable('User')->find($logininfo->userid);
		if($user)
		{
			$uarray = $user->toArray();
			$this->view->betriebsstatten_nr = $uarray['betriebsstattennummer'];
		}

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->last_name = $loguserarray['last_name'];
			$this->view->first_name = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$pl = new PatientLives();
		$pat_lives = $pl->getpatientLivesData($ipid);

		$this->view->alone = $pat_lives[0]['alone'];
		$this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
		$this->view->home = $pat_lives[0]['home'];

		$abb = "'HD'";
		$dg = new DiagnosisType();
		$darr = $dg->getDiagnosisTypes($clientid, $abb);


		if(is_array($darr))
		{
			foreach($darr as $key => $val)
			{
				$diaval .= $comma . "'" . $val['id'] . "'";
				$comma = ",";
			}
		}

		$diagns = new PatientDiagnosis();
		$stam_diagno2 = array();
		$stam_diagno2 = $diagns->getFinalData($ipid, $diaval);
		$stam_diagno = $diagns->getPatientMainDiagnosis($ipid, "diagnosis_icd");

		$comma = "";
		foreach($stam_diagno2 as $key1 => $val1)
		{
			if(strlen($val1['diagnosis']) > 0)
			{
				$this->view->relevante_Input.= $comma . $val1['diagnosis'] . " (" . $val1['icdnumber'] . ")";
				$comma = ",&nbsp;";
			}
		}

		$abb1 = "'AD','ND','DD'";
		$dg = new DiagnosisType();
		$darr1 = $dg->getDiagnosisTypes($clientid, $abb1);

		$comma = "";
		if(is_array($darr1))
		{
			foreach($darr1 as $key => $val)
			{
				$diaval1 .= $comma . "'" . $val['id'] . "'";
				$comma = ",";
			}
		}
		$diagns1 = new PatientDiagnosis();
		$stam_diagno1 = array();
		$stam_diagno1 = $diagns1->getFinalData($ipid, $diaval1);


		$comma = "";
		foreach($stam_diagno1 as $key2 => $val2)
		{
			if(strlen($val1['diagnosis']) > 0)
			{
				$this->view->relevanteNebendia_Input.= $comma . $val2['diagnosis'] . " (" . $val2['icdnumber'] . ")";
				$comma = ",&nbsp;";
			}
		}

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

		$this->view->bdate = $patientinfo['birthd'];

		$pdm = new PatientDiagnosisMeta();
		$metaarr = $pdm->getPatientDiagnosismeta($ipid);

		$comma = ",";
		$ipidval = "'0'";

		if(is_array($metaarr))
		{
			foreach($metaarr as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['metaid'] . "'";
				$comma = ",";
			}
		}

		$drugs = Doctrine_Query::create()
			->select('*')
			->from('DiagnosisMeta')
			->where("id in (" . $ipidval . ")");
		$dr = $drugs->execute();

		if($dr)
		{
			$diagnoarray = $dr->toArray();

			for($i = 0; $i < count($diagnoarray); $i++)
			{

				if($diagnoarray[$i]['meta_title'] == trim("Cerebrale Metastasierung"))
				{
					$this->view->cerel = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Pulmonale Metastasierung"))
				{
					$this->view->pulm = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Hepatische Metastasierung"))
				{
					$this->view->hepa = 1;
				}

				if($diagnoarray[$i]['meta_title'] == trim(utf8_encode("Oss�re Metastasierung")))
				{
					$this->view->ossa = 1;
				}
			}
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'Stammblatt', "stammblatt_pdf.html");
		}
	}

	public function homecareAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->hmcareclass = "active";

		$parr = $patientmaster->getMasterData($decid, 0);
		// print_r($parr);
		$this->view->patient_name = $parr['last_name'] . " " . $parr['first_name'];
		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'homecare', "homecarepdf.html");
		}
	}

	public function hopeformAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}		

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$this->view->hopefrmclass = "active";

		$parr = $patientmaster->getMasterData($decid, 0);
		
		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];
		$this->view->admissiondate = $parr['admission_date'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$abb = "'HD'";
		$dg = new DiagnosisType();
		$darr = $dg->getDiagnosisTypes($logininfo->clientid, $abb);
		$hid = $darr[0]['id'];
		if($hid == "")
		{
			$hid = 0;
		}

		$pd = new PatientDiagnosis();
		$pdarr = $pd->getFinalData($ipid, $hid);

		if(count($pdarr) > 0)
		{
			if($pdarr[0]['create_date'] != '0000-00-00 00:00:00')
			{
				$this->view->maindiagnosisdate = date('d.m.Y', strtotime($pdarr[0]['create_date']));
			}
		}


		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

		$pl = new PatientLives();
		$pat_lives = $pl->getpatientLivesData($ipid);
		$this->view->alone = $pat_lives[0]['alone'];
		$this->view->home = $pat_lives[0]['home'];
		$this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
		$this->view->apartment = $pat_lives[0]['apartment'];

		$pm = new PatientMaster();
		$pmarr = $pm->getMasterData($decid, 0);
		$this->view->living_will = $pmarr['living_will'];

		$pms = new PatientMaintainanceStage();
		$pat_pmsinfo = $pms->getpatientMaintainanceStage($ipid);


		if($pat_pmsinfo[0]['stage'] == 1)
		{
			$this->view->stage1 = 1;
		}
		if($pat_pmsinfo[0]['stage'] == 2)
		{
			$this->view->stage2 = 1;
		}
		if($pat_pmsinfo[0]['stage'] == 3)
		{
			$this->view->stage3 = 1;
		}
		$this->view->datum_der_erfassung = date('d.m.Y  H:i', time());

		$userdata = Pms_CommonData::getUserData($logininfo->userid);

		$groupid = $userdata[0]['groupid'];
		$grp = new Usergroup();
		$groupdata = $grp->getUserGroupData($groupid);

		if(count($groupdata) > 0)
		{
			$groupname = $groupdata[0]['groupname'];

			if(trim($groupname) == "Doctor" || trim($groupname) == "Doktor" || trim($groupname) == "Arzt" || trim($groupname) == "�rztin" || trim($groupname) == "QPA")
			{
				$this->view->wer_hat = 1;
			}
		}


		$pd = new PatientDischarge();
		$pdarr = $pd->getPatientDischarge($ipid);
		$this->view->datum_entlassung = date('d.m.Y H:i', strtotime($pdarr[0]['discharge_date']));

		$dm = new DischargeMethod();
		$dmarr = $dm->getDischargeMethodById($pdarr[0]['discharge_method']);


		if($dmarr[0]['abbr'] == trim("TOD"))
		{
			$this->view->todmethod = 1;
		}
		if($dmarr[0]['abbr'] == trim("DIS"))
		{
			$this->view->dismethod = 1;
		}
		if($dmarr[0]['abbr'] == trim("CAN"))
		{
			$this->view->canmethod = 1;
		}
		
		$pdm = new PatientDiagnosisMeta();
		$metaarr = $pdm->getPatientDiagnosismeta($ipid);

		$comma = ",";
		$ipidval = "'0'";

		if(is_array($metaarr))
		{
			foreach($metaarr as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['metaid'] . "'";
				$comma = ",";
			}
		}

		$drugs = Doctrine_Query::create()
			->select('*')
			->from('DiagnosisMeta')
			->where("id in (" . $ipidval . ")");
		$dr = $drugs->execute();

		if($dr)
		{
			$diagnoarray = $dr->toArray();

			for($i = 0; $i < count($diagnoarray); $i++)
			{

				if($diagnoarray[$i]['meta_title'] == trim("Cerebrale Metastasierung"))
				{
					$this->view->cerel = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Pulmonale Metastasierung"))
				{
					$this->view->pulm = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Hepatische Metastasierung"))
				{
					$this->view->hepa = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Oss�re Metastasierung"))
				{
					$this->view->ossa = 1;
				}
			}
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(1, $_POST, 'hopeform', "hopeformpdf.html");

			$cust = new PatientFileUpload();
			$cust->title = Pms_CommonData::aesEncrypt('Hope Form');
			$cust->ipid = $ipid;
			$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
			$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
			$cust->save();

			$this->_redirect(APP_BASE . "patient/patientfileupload?id=" . $_GET['id']);
		}
	}

	public function formfourAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$this->view->frmfourclass = "active";
		$parr = $patientmaster->getMasterData($decid, 0);
		// print_r($parr);
		$this->view->patient_name = $parr['last_name'] . " " . $parr['first_name'];
		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'formfour', "formfourpdf.html");
		}
	}

	public function formfiveAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$this->view->frmfiveclass = "active";

		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];



		$pd = new PatientDischarge();
		$dischargearr = $pd->getPatientDischarge($ipid);
		$dismethod = $dischargearr[0]['discharge_method'];

		$this->view->abschlussgrund = "";
		if(count($dismethod) > 0)
		{
			$ds = new DischargeMethod();
			$dischargearr = $ds->getDischargeMethodById($dismethod);
			$abbr = $dischargearr[0]['abbr'];

			if(trim($abbr) == "TOD" || trim($abbr) == "tod")
			{
				$this->view->abschlussgrund = "checked='checked'";
			}
		}

		$fdoc = Doctrine_Query::create()
			->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
			->from('Locations')
			->where("client_id='" . $logininfo->clientid . "'")
			->andWhere('isdelete=0')
			->andWhere("location_type='1'")
			->orderBy('location ASC');
		$loc = $fdoc->execute();
		if($loc)
		{
			$locarr = $loc->toArray();
			$comma = ",";
			$locid = "'0'";

			foreach($locarr as $key => $val)
			{
				$locid.= $comma . "'" . $val['id'] . "'";
				$comma = ",";
			}
		}

		$patloc = Doctrine_Query::create()
			->select('*')
			->from('PatientLocation')
			->where('ipid ="' . $ipid . '"')
			->andWhere('location_id in (' . $locid . ')')
			->orderBy('id ASC');
		$patexe = $patloc->execute();

		if($patexe)
		{
			$patlocation = $patexe->toArray();
			$grid = new Pms_Grid($patlocation, 1, count($patlocation), "listformfivelocations.html");
			$this->view->locationgrid = $grid->renderGrid();
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'formfive', "formfivepdf.html");
		}
	}

	public function sapvfb3Action()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$userid = $logininfo->userid;
		
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);
		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$this->view->frmb3class = "active";

		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();
			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);
		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

		if(strlen($_POST['btnsave']) > 0)
		{

			$sp = new Sapsymptom();
			$sp->ipid = Pms_CommonData::getIpid($decid);
			$sp->sapvalues = join(",", $_POST['symptom']);
			$sp->gesamt_zeit_in_minuten = $_POST['gesamt_zeit_in_minuten'];
			$sp->gesamt_fahrstrecke_in_km = $_POST['gesamt_fahrstrecke_in_km'];
			$sp->save();

			for($i = 0; $i < count($_POST['comments']); $i++)
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("LE");
				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($_POST['comments'][$i]));
				$cust->user_id = $userid;
				$cust->save();
			}
		}
		if(strlen($_POST['btnpdf']) > 0)
		{
			$this->generateformPdf(2, $_POST, 'SAPVF_B3', "sapvf_b3pdf.html");
		}

		$sp = Doctrine_Core::getTable('Sapsymptom')->findBy('ipid', Pms_CommonData::getIpid($decid));
		$sparr = $sp->toArray();
		$this->view->sparr = $sparr;
	}

	public function sapvfb4Action()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->frmb4class = "active";

		$parr = $patientmaster->getMasterData($decid, 0);
		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];
		$familydoc_id = $parr['familydoc_id'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		if($ref)
		{
			$refarray = $ref->toArray();
			if($refarray['referred_name'] == trim("Hausarzt"))
			{
				$fd = new FamilyDoctor();
				$fdarr = $fd->getFamilyDoc($familydoc_id);
				if(count($fdarr) > 0)
				{

					$this->view->refarray = $fdarr[0]['first_name'] . " " . $fdarr[0]['last_name'] . ", " . $fdarr[0]['street1'] . ", " . $fdarr[0]['zip'] . " " . $fdarr[0]['city'] . ", " . $fdarr[0]['phone_practice'];
				}
			}
			else
			{
				$this->view->refarray = $refarray['referred_name'];
			}
		}


		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

		$pms = new PatientMaintainanceStage();
		$pat_pmsinfo = $pms->getpatientMaintainanceStage($patientinfo['ipid']);
		if($pat_pmsinfo[0]['stage'] > 0)
		{
			$this->view->{"stage" . $pat_pmsinfo[0]['stage']} = 'checked="checked"';
		}
		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'SAPVF_B4', "sapvf_b4pdf.html");
		}
	}

	public function sapvfb5Action()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->frmb5class = "active";

		$parr = $patientmaster->getMasterData($decid, 0);
		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];
		$ipid = $parr['ipid'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'SAPVF_B5', "sapvf_b5pdf.html");
		}
	}

	public function sapvfb12Action()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);

		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->frmb12class = "active";

		$parr = $patientmaster->getMasterData($decid, 0);
		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];
		$ipid = $parr['ipid'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];
		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(3, $_POST, 'SAPVF_B12', "sapvf_b12pdf.html");
		}
	}

	public function sapvfb8Action()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$this->view->ipid = Pms_CommonData::getIpid($decid);
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if(!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$this->view->notostrdays = array('1' => "one", '2' => "two", '3' => "three", '4' => "four", '5' => "five", '6' => "six", '7' => "seven", '8' => "eight", '9' => "nine", '10' => "ten");

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$this->view->frmb8class = "active";
		$parr = $patientmaster->getMasterData($decid, 0);
		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];
		$this->view->admission_date = $parr['admission_date'];

		$doc = Doctrine::getTable('PatientDischarge')->findBy('ipid', $ipid);
		$patientarray = $doc->toArray();

		if(count($patientarray) > 0)
		{
			$this->view->discharge_date = date("d.m.Y", strtotime($patientarray[0]['discharge_date']));
			$pms = new PatientMaster();
			$daystreated = str_replace("days", "", $pms->GetTreatedDays($parr['admission_date'], $patientarray[0]['discharge_date']));

			if($daystreated > 0)
			{
				$this->view->days_treated = $daystreated;
			}
			else
			{
				$this->view->days_treated = "-";
			}
		}
		
		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();
			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'SAPVF_B8', "sapvf_b8pdf.html");
		}
	}

	private function generateformPdf($chk, $post, $pdfname, $filename)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$post['ipid'] = Pms_CommonData::getIpid($decid);

		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$post['patientname'] = $parr['last_name'] . ", " . $parr['first_name'] . "<br>" . $parr['street1'] . "<br>" . $parr['zip'] . "<br>" . $parr['city'];
	
		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);

		if($loguser)
		{
			$loguserarray = $loguser->toArray();
			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}

		$clientdata = Pms_CommonData::getClientData($logininfo->clientid);
		$post['clientname'] = $clientdata[0]['clientname'];
		$post['clientfax'] = $clientdata[0]['fax'];
		$post['clientphone'] = $clientdata[0]['phone'];
		$post['clientemail'] = $clientdata[0]['emailid'];

		$sp = Doctrine_Core::getTable('Sapsymptom')->findBy('ipid', Pms_CommonData::getIpid($decid));
		$post['sapsymp'] = $sp->toArray();

		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$post['bdate'] = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$post['insurance_company_name'] = $healthinsu_array[0]['company_name'];
		$post['insurance_no'] = $healthinsu_array[0]['insurance_no'];
		$post['insurance_status'] = $healthinsu_array[0]['insurance_status'];
		
		/* analage3 */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$post['tablepatientinfo'] = Pms_Template::createTemplate($parr, 'templates/pdfprofile.html');
		$post['tag'] = date("d");
		$post['month'] = date("m");
		$post['jahr'] = date("Y");

		$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);

		$pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator('IPSC');
		$pdf->SetAuthor('ISPC');
		$pdf->SetTitle('ISPC');
		$pdf->SetSubject('ISPC');
		$pdf->SetKeywords('ISPC');
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(30, 10, 30);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetHeaderMargin(10);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 10);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray('de');
		// set font
		$pdf->SetFont('times', '', 10);

		// add a page
		$pdf->AddPage('P', 'A4');

		//echo $htmlform;

		$pdf->writeHTML($htmlform, true, 0, true, 0);

		if($chk == 1)
		{
			$tmpstmp = time();
			mkdir("uploads/" . $tmpstmp);
			$pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
			$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "uploads/" . $tmpstmp . ".zip";
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}
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

			$tmpstmp = time();
			mkdir("uploads/" . $tmpstmp);
			$pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
			$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "uploads/" . $tmpstmp . ".zip";
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}

			$cust = new PatientFileUpload();
			$cust->title = Pms_CommonData::aesEncrypt($pdfname);
			$cust->ipid = $ipid;
			$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
			$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
			$cust->save();

			ob_end_clean();
			ob_start();
			$pdf->Output($pdfname . '.pdf', 'D');
			exit;
		}
	}

	private function generateanalagethreePdf($chk, $post, $pdfname, $filename)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$post['patientname'] = $parr['last_name'] . ", " . $parr['first_name'] . "<br />" . $parr['street1'] . "&nbsp;" . $parr['zip'] . "<br />" . $parr['city'];
		
		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->lastname = $loguserarray['last_name'];
			$this->view->firstname = $loguserarray['first_name'];
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$post['bdate'] = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$post['insurance_company_name'] = $healthinsu_array[0]['company_name'];
		$post['insurance_no'] = $healthinsu_array[0]['insurance_no'];
		$post['insurance_status'] = $healthinsu_array[0]['insurance_status'];

		$insucom = new HealthInsurance();
		$insucomarray = $insucom->getCompanyinfofromId($healthinsu_array[0]['companyid']);
		$post['kvnumber'] = $insucomarray[0]['kvnumber'];

		$post['tag'] = date("d");
		$post['month'] = date("m");
		$post['jahr'] = date("Y");

		$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);

		$pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('IPSC');
		$pdf->SetAuthor('ISPC');
		$pdf->SetTitle('ISPC');
		$pdf->SetSubject('ISPC');
		$pdf->SetKeywords('ISPC');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(30, 10, 30);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetHeaderMargin(10);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 10);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray('de');

		// ---------------------------------------------------------
		// set font
		$pdf->SetFont('times', '', 10);

		// add a page
		$pdf->AddPage('P', 'A4');
		//echo $htmlform;

		$pdf->writeHTML($htmlform, true, 0, true, 0);
		
		if($chk == 3)
		{
			$tmpstmp = time();
			mkdir("uploads/" . $tmpstmp);
			$pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
			$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "uploads/" . $tmpstmp . ".zip";
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}

			$cust = new PatientFileUpload();
			$cust->title = Pms_CommonData::aesEncrypt($pdfname);
			$cust->ipid = $ipid;
			$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
			$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
			$cust->save();
			ob_end_clean();
			ob_start();
			$pdf->Output($pdfname . '.pdf', 'D');
			exit;
		}
		else
		{
			ob_end_clean();
			ob_start();
			$pdf->Output($pdfname . '.pdf', 'D');
			exit;
		}
	}

	public function updatepatientinfoAction()
	{

		$this->_helper->viewRenderer('patientdetails');
		//$arrs = explode("_",$_GET['modname']);
		$ipid = Pms_CommonData::getIpid($_GET['patid']);

		$pat = Doctrine::getTable('Patient' . $_GET['modname'] . '')->findBy('ipid', $ipid);
		$patarr = array();

		if($pat)
		{
			$patarr = $pat->toArray();
		}


		if($_GET['modname'] == "MaintainanceStage")
		{
			$post = $_GET;
			$post['ipid'] = $ipid;
			$mainform = new Application_Form_PatientMaintainanceStage();
			$mainform->InsertData($post);

			$pms = new PatientMaintainanceStage();
			$pat_pmsinfo = $pms->getpatientMaintainanceStage($ipid);
			$grid = new Pms_Grid($pat_pmsinfo, 1, count($pat_pmsinfo), "carelevellist.html");
			$carelevellist = $grid->renderGrid();

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "carediv";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['carelist'] = $carelevellist;

			echo json_encode($response);
			exit;
		}
		else
		{
			if(count($patarr) > 0)
			{
				$q = Doctrine_Query::create()
					->update('Patient' . $_GET['modname'] . '')
					->set($_GET['fldname'], $_GET['chkval'])
					->where("ipid='" . $ipid . "'");
				$q->execute();
			}
			else
			{
				$tblname = 'Patient' . $_GET['modname'];
				$nm = new $tblname();
				$nm->$_GET['fldname'] = $_GET['chkval'];
				$nm->ipid = $ipid;
				$nm->save();
			}
		}
	}

	public function sapvfanfrageAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->street = $parr['street1'];
		$this->view->zip = $parr['zip'];
		$this->view->telephone = $parr['phone'];
		$this->view->mobile = $parr['mobile'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_com_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];
		$this->insurance_status = $healthinsu_array[0]['insurance_status'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$cd = new PatientCourse();
		$newarr1 = $cd->getCourseDataByShortcut($ipid, 'H');

		$ctitle = "";
		$quama = "";
		$this->view->relevante_diagnosen = "";
		for($i = 0; $i < count($newarr1); $i++)
		{
			$this->view->relevante_diagnosen.= $quama . $newarr1[$i]['course_title'];
			$quama = ",";
		}

		$newarr2 = $cd->getCourseDataByShortcut($ipid, 'D');

		$ctitle = "";
		$quama = "";
		$this->view->relevante_nebendiagnosen = "";
		
		for($i = 0; $i < count($newarr2); $i++)
		{
			$this->view->relevante_nebendiagnosen.= $quama . $newarr2[$i]['course_title'];
			$quama = ",";
		}


		$loguser = Doctrine::getTable('User')->find($logininfo->userid);

		if($loguser)
		{
			$loguserarray = $loguser->toArray();
			$this->view->loginusername = $loguserarray['last_name'] . ", " . $loguserarray['first_name'];
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'SAPVf_anfrage', "sapvf_anfragepdf.html");
		}
	}

	public function uberleitungsbogenAction()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'] . "\n" . $parr['street1'] . "&nbsp;" . $parr['zip'] . "\n" . $parr['city'];
		
		$familydoc = new FamilyDoctor();
		$fdocarray = $familydoc->getFamilyDoc($parr['familydoc_id']);
		$this->view->fdocfirstname = $fdocarray[0]['first_name'] . ", " . $fdocarray[0]['last_name'];
		$this->view->phonepractice = $fdocarray[0]['phone_practice'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$epid = Pms_CommonData::getEpidFromId($decid);
		$assignid = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid = '" . $epid . "'");

		$assignidexec = $assignid->execute();
		$assignidarray = $assignidexec->toArray();

		$comma = ",";
		$userid = "'0'";
		foreach($assignidarray as $key => $val)
		{
			$userid.= $comma . "'" . $val['userid'] . "'";
			$comma = ",";
		}

		$assignuser = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('id in (' . $userid . ') and clientid=' . $logininfo->clientid)
			->andWhere('isdelete=0 and isactive=0');
		$assignuserexec = $assignuser->execute();
		$assignuserarray = $assignuserexec->toArray();

		$grid = new Pms_Grid($assignuserarray, 1, count($assignuserarray), "assignuseruberleitungsbogen.html");
		$this->view->assignedusersube = $grid->renderGrid();

		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		if($loguser)
		{
			$loguserarray = $loguser->toArray();

			$this->view->loginusername = $loguserarray['last_name'] . ", " . $loguserarray['first_name'] . "\n" . date('d.m.Y H:i:s');
		}
		$patientmaster = new PatientMaster();
		$patientinfo = $patientmaster->getMasterData($decid, 0);

		$this->view->bdate = $patientinfo['birthd'];

		$recorduser = Doctrine::getTable('User')->find($patientinfo['create_user']);
		if($recorduser)
		{
			$recorduserarray = $recorduser->toArray();
			$this->view->fullname = $recorduserarray['last_name'] . ", " . $recorduserarray['first_name'] . " | " . $patientinfo['admission_date'];
		}

		$patcont = new ContactPersonMaster();
		$patcontarray = $patcont->getPatientContact($patientinfo['ipid']);
		$this->view->contname = "";
		$this->view->familydegree = "";
		$this->view->mobile = "";
		$counter = 1;
		foreach($patcontarray as $key => $conval)
		{

			$this->view->contname .= '<tr><td><input name="cnt_name[' . $counter . ']" type="hidden" value="' . $conval['cnt_last_name'] . ', ' . $conval['cnt_first_name'] . '" />' . trim(htmlentities($conval['cnt_last_name'])) . ', ' . trim(htmlentities($conval['cnt_first_name'])) . '</td>';
			$familydegree = new FamilyDegree();
			$degreearray = $familydegree->getfamilydegreebyId($conval['cnt_familydegree_id']);
			//print_r($degreearray);
			$this->view->contname .= '<td>
		<input name="cnt_familydegree[' . $counter . ']" type="hidden" value="' . $patcontarray['cnt_familydegree_id'] . $degreearray[0]['family_degree'] . '" />
		' . trim(htmlentities($patcontarray['cnt_familydegree_id'] . $degreearray[0]['family_degree'])) . '&nbsp;</td>';
			$this->view->contname .= '<td>
		<input name="cnt_mobile[' . $counter . ']" type="hidden" value="' . $conval['cnt_phone'] . ', ' . $conval['cnt_mobile'] . '" />
		' . $conval['cnt_phone'] . ', ' . $conval['cnt_mobile'] . '</td></tr>';

			$counter++;
		}

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];
		$this->view->insurance_status = $healthinsu_array[0]['insurance_status'];

		$insucom = new HealthInsurance();
		$insucomarray = $insucom->getCompanyinfofromId($healthinsu_array[0]['companyid']);
		$this->view->kvnumber = $insucomarray[0]['kvnumber'];


		$post['tag'] = date("d");
		$post['month'] = date("m");
		$post['jahr'] = date("Y");

		$pl = new PatientLives();
		$pat_lives = $pl->getpatientLivesData($ipid);

		$this->view->alone = $pat_lives[0]['alone'];
		$this->view->house_of_relatives = $pat_lives[0]['house_of_relatives'];
		$this->view->home = $pat_lives[0]['home'];

		$pm = new PatientMobility();
		$pat_mob = $pm->getpatientMobilityData($ipid);

		$ps = new PatientSupply();
		$pat_supply = $ps->getpatientSupplyData($ipid);

		$pmf = new PatientMoreInfo();
		$pat_moreinfo = $pmf->getpatientMoreInfoData($ipid);
		$this->view->dk = $pat_moreinfo[0]['dk'];
		$this->view->peg = $pat_moreinfo[0]['peg'];
		$this->view->port = $pat_moreinfo[0]['port'];
		$this->view->pumpe = $pat_moreinfo[0]['pumps'];


		$pms = new PatientMaintainanceStage();
		$pat_pmsinfo = $pms->getpatientMaintainanceStage($ipid);
		if($pat_pmsinfo[0]['stage'] > 0)
		{

			$this->view->{"stage" . $pat_pmsinfo[0]['stage']} = $pat_pmsinfo[0]['stage'];
		}
		else
		{
			$this->view->keine = 1;
		}

		$qpa1 = Doctrine_Query::create()
			->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						  AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
			->from('PatientCourse')
			->where("ipid='" . $ipid . "' and course_type='" . addslashes(Pms_CommonData::aesEncrypt('C')) . "'");

		$qp1 = $qpa1->execute();
		if($qp1)
		{
			$newarr1 = $qp1->toArray();
		}

		$ctitle = "";
		$quama = "";
		for($i = 0; $i < count($newarr1); $i++)
		{
			$userarr = Pms_CommonData::getUserData($newarr1[$i]['create_user']);
			$username = $userarr[0]['last_name'] . "," . $userarr[0]['first_name'];

			$createdate = date('d.m.Y', strtotime($newarr1[$i]['create_date']));
			$title = str_replace("<", "&lt;", $newarr1[$i]['course_title']);
			$title = str_replace(">", "&gt;", $title);

			$ctitle .= $quama . $username . " (" . $createdate . ") : " . $title;
			$quama = " <br> ";
			$this->view->ctexttitle .='<input name="course_data[' . $i . ']" type="hidden" value="' . $username . ' (' . $createdate . ') : ' . $title . '" />';
		}
		$this->view->coursedata = $ctitle;

		$abb = "'AD','DD','HD','ND'";
		$dg = new DiagnosisType();
		$ddarr = $dg->getDiagnosisTypes($logininfo->clientid, $abb);

		if(!$ddarr[0]['id'])
		{
			$ddarr[0]['id'] = 0;
		}

		$stam_diagno = array();
		$diagns = new PatientDiagnosis();
		$stam_diagno = $diagns->getFinalData($ipid, $ddarr[0]['id']);
		$dia = 1;
		foreach($stam_diagno as $key => $val)
		{
			if(strlen($val['diagnosis']) > 0)
			{
				$this->view->maindiagnosis .= '<input name="other_diagnosis[' . $dia . ']" type="hidden" value="' . $val['diagnosis'] . ' (' . $val['icdnumber'] . ')" />
			 <div id="Pallnet_weiteDiasse_1" class="PalnetzShortTitle border_top">' . $val['diagnosis'] . ' (' . $val['icdnumber'] . ')</div>';
				$dia++;
			}
		}

		$pdm = new PatientDiagnosisMeta();
		$metaarr = $pdm->getPatientDiagnosismeta($ipid);

		$comma = ",";
		$ipidval = "'0'";

		if(is_array($metaarr))
		{
			foreach($metaarr as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['metaid'] . "'";
				$comma = ",";
			}
		}

		$stam_diagno = array();
		$diagns = new PatientDiagnosis();
		$stam_diagno = $diagns->getPatientMainDiagnosis($ipid, "diagnosis_icd");

		$dd = new DiagnosisIcd();
		$ddarr = $dd->getDiagnosisDataById($stam_diagno[0]['diagnosis_id']);
		if($ddarr)
		{
			$this->view->other_diagnosis .= '<input name="maindiagnosis[' . ($i + 1) . ']" type="hidden" value="' . $ddarr[0]['description'] . '(' . $ddarr[0]['icd_primary'] . ')" />
			<div id="Pallnet_DieLeim_1" class="PalnetzShortTitle border_top">tes ' . $ddarr[0]['description'] . '(' . $ddarr[0]['icd_primary'] . ')</div>';
		}

		$drugs = Doctrine_Query::create()
			->select('*')
			->from('DiagnosisMeta')
			->where("id in (" . $ipidval . ")");
		$dr = $drugs->execute();

		if($dr)
		{
			$diagnoarray = $dr->toArray();

			for($i = 0; $i < count($diagnoarray); $i++)
			{
				if($diagnoarray[$i]['meta_title'] == trim("Cerebrale Metastasierung"))
				{
					$this->view->cerel = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Pulmonale Metastasierung"))
				{
					$this->view->lunge = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Hepatische Metastasierung"))
				{
					$this->view->hepa = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Oss�re Metastasierung"))
				{
					$this->view->ossa = 1;
				}
				if($diagnoarray[$i]['meta_title'] == trim("Lokoregion�re  Metastasierung"))
				{
					$this->view->nerven = 1;
				}
			}
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'Uberleitungsbogen', "uberleitungsbogenpdf.html");
		}
	}

	public function verordnungAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();
		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'] . "\n" . $parr['street1'] . "&nbsp;" . $parr['zip'] . "\n" . $parr['city'];
		$this->view->birthdate = $parr['birthd'];
		$this->view->patientname1 = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->patietnaddress = $parr['street1'] . "&nbsp;" . $parr['zip'] . "\n" . $parr['city'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		$this->view->refarray = $ref['referred_name'];

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);

		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];
		$this->view->insurance_status = $healthinsu_array[0]['insurance_status'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$epid = Pms_CommonData::getEpidFromId($decid);
		$this->view->epid = $epid;

		$brenmber = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where("epid='" . $epid . "'");
		$bexec = $brenmber->execute();
		$barray = $bexec->toArray();

		$user = Doctrine::getTable('User')->find($logininfo->userid);
		if($user)
		{
			$uarray = $user->toArray();
			$this->view->betriebsstatten_nr = $uarray['betriebsstattennummer'];
			$this->view->arzt_nr = $uarray['LANR'];
		}

		$cd = new PatientCourse();
		$newarr1 = $cd->getCourseDataByShortcut($ipid, 'H');

		$ctitle = "";
		$quama = "";
		$this->view->relevante_Input = "";
		for($i = 0; $i < count($newarr1); $i++)
		{
			$this->view->relevante_Input.= $quama . $newarr1[$i]['course_title'];
			$quama = ",";
		}


		$newarr2 = $cd->getCourseDataByShortcut($ipid, 'D');

		$ctitle = "";
		$quama = "";
		$this->view->relevanteNebendia_Input = "";
		for($i = 0; $i < count($newarr2); $i++)
		{
			$this->view->relevanteNebendia_Input.= $quama . $newarr2[$i]['course_title'];
			$quama = ",";
		}

		$pdrug = new PatientDrugPlan();
		$drugarray = $pdrug->getPatientDrugPlan($decid);

		$comma = "";
		foreach($drugarray as $key => $val)
		{
			$medca = Doctrine::getTable('Medication')->find($val['medication_master_id']);
			$medcaarray = $medca->toArray();
			$this->view->medication .= $comma . $medcaarray['name'] . " | " . $val['dosage'];
			$comma = "\n";
		}

		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'Verordnung', "verordnungpdf.html");
		}
	}

	public function palliativversorgunga7Action()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid, 0);

		$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'] . "\n" . $parr['street1'] . "&nbsp;" . $parr['zip'] . "\n" . $parr['city'];
		$this->view->birthdate = $parr['birthd'];
		$this->view->patientname1 = $parr['last_name'] . ", " . $parr['first_name'];
		$this->view->patietnaddress = $parr['street1'] . "&nbsp;" . $parr['zip'] . "\n" . $parr['city'];

		if($parr['sex'] == 1)
		{
			$this->view->male = "checked='checked'";
		}
		
		if($parr['sex'] == 2)
		{
			$this->view->female = "checked='checked'";
		}

		$phelathinsurance = new PatientHealthInsurance();
		$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);
		$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
		$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];
		$this->view->insurance_status = $healthinsu_array[0]['insurance_status'];

		$insucom = new HealthInsurance();
		$insucomarray = $insucom->getCompanyinfofromId($healthinsu_array[0]['companyid']);
		$this->view->kvnumber = $insucomarray[0]['kvnumber'];


		$ploc = new PatientLocation();
		$plocarray = $ploc->getpatientLocation($ipid);
		$cont = 0;
		foreach($plocarray as $key => $val)
		{
			$loc = new Locations();
			$locarray = $loc->getLocationbyId($val['location_id']);

			foreach($locarray as $keyv => $kval)
			{
				if($kval['location_type'] == 1)
				{
					$krankenhuse = 1;
					$fromdate[$cont]['fromdate'] = date("d.m.Y", strtotime($val['valid_from'])) . " - " . date("d.m.Y", strtotime($val['valid_till']));

					$resonarray = $ploc->getReasons();
					$fromdate[$cont]['reason'] = $resonarray[$val['reason']];
					$cont++;
				}
			}
		}

		$this->view->fromdate = $fromdate;

		$pdis = new PatientDischarge();
		$disarray = $pdis->getPatientDischarge($ipid);
		if(count($disarray) > 0)
		{
			$dism = new DischargeMethod();
			$dismarray = $dism->getDischargeMethodById($disarray[0]['discharge_method']);
			$this->view->todmethod = "";
			if($dismarray[0]['abbr'] == 'TOD')
			{
				$this->view->todmethod = 1;
			}
		}
		
		if($this->getRequest()->isPost())
		{
			$this->generateformPdf(2, $_POST, 'Palliativ_versorgung_a7', "analage7_pdf.html");
		}
	}

	public function sapvfb3commentAction()
	{
		$this->_helper->layout->setLayout('layout_popup');
	}

	private function recursiveArraySearch($haystack, $needle, $index = null)
	{
		$aIt = new RecursiveArrayIterator($haystack);
		$it = new RecursiveIteratorIterator($aIt);

		while($it->valid())
		{
			if(((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle))
			{
				return $aIt->key();
			}

			$it->next();
		}

		return false;
	}
}
?>