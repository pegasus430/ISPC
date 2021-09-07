<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
class PatientcourseController extends Pms_Controller_Action //Zend_Controller_Action
{
	
	protected $logininfo = null;
	protected $_patientMasterData = null;
	
	public function init()
	{
		$this->logininfo = new Zend_Session_Namespace('Login_Info');
		
		//ISPC-2827 Ancuta 31.03.2021
		if ($this->logininfo->isEfaClient == '1' && $this->logininfo->isEfaUser == '1') 
		{
		    $this->_redirect(APP_BASE."patientformnew/ambulatorycurve?id=" . $_GET['id']);
		}
		//--
		
		
		//    ISPC-791 secrecy tracker
		$user_access= PatientPermissions::document_user_acces();

		//Check patient permissions on controller and action
		$patient_privileges = PatientPermissions::checkPermissionOnRun();
		
		if(!$patient_privileges){
			$this->_redirect(APP_BASE.'error/previlege');
		}
		
		
	
		$this
		->setActionsWithPatientinfoAndTabmenus([
		    /*
		     * actions that have the patient header
		     */
		    'patientcourse',
		])
		->setActionsWithJsFile([
		    /*
		     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		     */
		    'patientcourse',
		])
		->setActionsWithLayoutNew([
		    /*
		     * actions that will use layout_new.phtml
		     * Actions With Patientinfo And Tabmenus also use layout_new.phtml
		     */
		])
		;
		
		
	}

	/**
	 * @deprecated
	 * is this used? write ia comment is NOT deprecated and NOT removed
	 */
	public function oldpatientcourseAction()
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->view->pid = $_GET['id'];
		$clientid = $logininfo->clientid;
		$this->view->clientid = $clientid;

		//Shortcut LNR module
		$previleges = new Modules();
		$modulepriv = $previleges->checkModulePrivileges("55", $logininfo->clientid);

		if ($modulepriv)
		{
			$this->view->modulepriv = "1";
		}
		else
		{
			$this->view->modulepriv = "0";
		}

		//Shortcut Bavaria module
		$previleges = new Modules();
		$modulepriv_bav = $previleges->checkModulePrivileges("60", $logininfo->clientid);

		if ($modulepriv_bav)
		{
			$this->view->modulepriv_bav = "1";
		}
		else
		{
			$this->view->modulepriv_bav = "0";
		}

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

		if (!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}

		$isdicharged = PatientDischarge::isDischarged($decid);
		$this->view->isdischarged = 0;
		if ($isdicharged)
		{
			$this->view->isdischarged = 1;
		}

		/* ######################################################### */

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canview');

		if (!$return)
		{
			$this->view->style = 'none;';
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canadd');
			if (!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		}
		else
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canadd');
			if (!$return)
			{
				$this->view->coursestyle = 'none;';
			}
		}

		$ipid = Pms_CommonData::getIpid($decid);
		$epid = Pms_CommonData::getEpid($ipid);

		// 07.07
		$abb = "'HD','ND'";
		$dg = new DiagnosisType();
		$darr = $dg->getDiagnosisTypes($logininfo->clientid, $abb);
		$this->view->dtypearray = $darr;
		$this->view->jdarr = json_encode($darr);

		$dm = new DiagnosisMeta();
		$diagnosismeta = $dm->getDiagnosisMetaData(1);

		$this->view->diagnosismeta = $diagnosismeta;
		$this->view->jsdiagnosismeta = json_encode($diagnosismeta);

		// 07.07
		$this->view->patcrclass = "active";
		$this->view->act = "patientcourse/oldpatientcourse?id=" . $_GET['id'];
		$this->view->callcourse = "";
		$this->view->curr_date = date("d.m.Y", time());
		$this->view->pid = $_GET['id'];

        $this->view->ipid = $ipid;

		if ($this->getRequest()->isPost())
		{
			//unset diagnosis from post!!!
			$post = $_POST;
			unset($post['icdnumber']);
			unset($post['diagnosis']);
			unset($post['hidd_icdnumber']);
			unset($post['hidd_diagnosis']);
			unset($post['hidd_tab']);
			unset($_POST);
			$_POST = $post;

			$course_medications = new Application_Form_PatientDrugPlan();
			$course_medi_edit = $course_medications->UpdateMultiDataMedicationsVerlauf($_POST);

			$course_symptoms = new Application_Form_PatientSymptomatology();
			$course_symptom_add = $course_symptoms->InsertMultipleDataFromVerlauf($ipid, $_POST);

			$course_form = new Application_Form_PatientCourse();

			if ($course_form->validate($_POST))
			{
				$course_form->InsertData($_POST);
				$datainserted = 1;
				$courseSession = new Zend_Session_Namespace('courseSession');
				$courseSession->coursetype = array ();
			}
			else
			{
				$datainserted = 0;
				$course_form->assignErrorMessages();
				$this->retainValues($_POST);
			}

			$courseSession = new Zend_Session_Namespace('courseSession');
			$courseSession->coursetype = array ();
		}

		//get client user list start
		$users = new User();
		$userarray = $users->getUserByClientid($logininfo->clientid);
		$userarraylast[] = $this->view->translate('selectuser');
		foreach ($userarray as $user)
		{
			$userarraylast[$user['id']] = trim($user['last_name']).", ".trim($user['first_name']);
		}

		$this->view->users = $userarraylast;

		$cs = new Courseshortcuts();
		$ltrarray = $cs->getFilterCourseData();

		$letterarray = array ();
		$lettersforjs = array ();

		foreach ($ltrarray as $key => $value)
		{
			$letterarray[$value['shortcut']] = $value['course_fullname'];
		}

		$js = new Courseshortcuts();
		$jsarr = $js->getFilterCourseData('canedit'); //get shortcuts to be used

		foreach ($jsarr as $key => $value)
		{
			$lettersforjs[] = $value['shortcut'];
		}
		
		$lettersforjs[] = '_shared';
		$lettersforjs[] = '_owned';

		$jsfarr = $js->getFilterCourseData();
		foreach ($jsfarr as $key => $value)
		{
			$hkforjs[] = $value['shortcut'];
		}

		$hkforjs[] = 999999;

		$this->view->ltrjs = json_encode($lettersforjs);
		$this->view->ltrjsarr = $lettersforjs;

		$patient = Doctrine_Query::create()
			   ->select("distinct(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'))")
			   ->from('PatientCourse')
			   ->where('ipid ="' . $ipid . '"')
			   ->andWhere('`source_ipid` =  ""')
			->andWhereIn("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "')", $hkforjs)
			   ->orderBy('course_date ASC');
		$patientarray = $patient->fetchArray();

		$patient_shared_shortcuts = Doctrine_Query::create()
			   ->select("distinct(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'))")
			   ->from('PatientCourse')
			   ->where('ipid ="' . $ipid . '"')
			   ->andWhere('`source_ipid` !=  ""')
			   ->andWhereIn("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "')", $hkforjs)
			   ->orderBy('course_date ASC');
		$patient_shared_shortcuts_arr = $patient_shared_shortcuts->fetchArray();

		$finalarr = array ();
		$finalarr_shared = array ();
		$hotkeys = array ();
		$postdisplay = array ();
		$postcnt = 0;

		//the foreach from hell
		foreach ($patientarray as $key => $value)
		{
			$distval = $value['distinct'];
			//@TODO optimize this

			$shorts = new Courseshortcuts();
			$coursearr = $shorts->getCourseDataByShortcut($distval);

			if ($_POST[$distval] == 1)
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

			if ($coursearr[0]['isfilter'] == 1)
			{
				foreach ($letterarray as $key => $val)
				{
					if ($key == $distval)
					{
						array_push($finalarr, array (
						    'cletter' => $distval,
						    'ctype' => $letterarray[$distval],
						    'font_color' => $coursearr[0]['font_color'],
						    'isbold' => $coursearr[0]['isbold'],
						    'isitalic' => $coursearr[0]['isitalic'],
						    'isunderline' => $coursearr[0]['isunderline'],
						    'chk' => $chk,
						));
					}
				}
			}

			$hotkeys[] = $distval;
		}

		//another foreach from hell for shared shortcuts
		foreach ($patient_shared_shortcuts_arr as $s_key => $s_value)
		{
			$s_distval = $s_value['distinct'];
			//@TODO optimize this
			$shorts = new Courseshortcuts();
			$coursearr_shared = $shorts->getCourseDataByShortcut($s_distval);

			if ($_POST[$s_distval] == 1)
			{
				$s_postcnt++;
				$chk = 1;
				$chkval.= $comma . "'" . $s_distval . "'";
				$comma = ",";
				$s_postdisplay[$s_distval] = 1;
			}
			else
			{
				$chk = 0;
				$s_postdisplay[$s_distval] = 0;
			}

			if ($coursearr_shared[0]['isfilter'] == 1)
			{
				foreach ($letterarray as $key => $val)
				{
					if ($key == $s_distval)
					{
						array_push($finalarr_shared, array (
						    'cletter' => $s_distval."_shared",
						    'ctype' => $letterarray[$s_distval],
						    'font_color' => $coursearr_shared[0]['font_color'],
						    'isbold' => $coursearr_shared[0]['isbold'],
						    'isitalic' => $coursearr_shared[0]['isitalic'],
						    'isunderline' => $coursearr_shared[0]['isunderline'],
						    'chk' => $chk,
						));

					}
				}
			}

			$s_hotkeys[] = $s_distval;
		}

		$this->view->hotkeysjs = json_encode($hotkeys);

		$newarr = Pms_DataTable::sortArray($finalarr, 'ctype', SORT_ASC);
		$newarr_share = Pms_DataTable::sortArray($finalarr_shared, 'ctype', SORT_ASC);

		$this->view->checkcounter = count($newarr);

		$grid = new Pms_Grid($newarr, 1, count($newarr), "listcoursechecks.html");
		$this->view->gridchecks = $grid->renderGrid();

		$this->view->hasSharedShortcuts = count($newarr_share);

		$pcourse = new PatientCourse();
		$allblocks = $pcourse->getCourseData($decid, 0);

		//getting all user ids
		foreach ($allblocks as $block)
		{
			$allusers[] = $block['user'];
		}

		$allusers[] = '999999'; //prevent fehler on empty array
		//getting all user details
		$allusers_details = User::getMultipleUserDetails($allusers);

		//adding user details to each block
		foreach ($allblocks as $key => $block)
		{
			$allblocks[$key]['user_fname'] = $allusers_details[$block['user']]['first_name'];
			$allblocks[$key]['user_lname'] = $allusers_details[$block['user']]['last_name'];
		}

		//optimized!
		$pm = new PatientMaster();
		$masterdata = $pm->getMasterData(0, 0, 0, $ipid);

		$grid = new Pms_Grid($allblocks, 1, count($allblocks), "listpatientcourse.html");
		$this->view->gridcourse = $grid->renderGrid();

		if ($postcnt > 0)
		{
			$this->view->callcheck2 = "check2()";
		}
		else
		{
			$this->view->callcheck2 = '""';
		}


		$courseSession = new Zend_Session_Namespace('courseSession');
		if ($courseSession->patientId != $decid)
		{
			$courseSession->patientId = $decid;
			$courseSession->coursetype = array ();
		}


		if (is_array($_POST['course_type']) && $datainserted == 0)
		{
			foreach ($_POST['course_type'] as $key => $val)
			{
				$courses[$key]['course_type'] = $_POST['course_type'][$key];
				$courses[$key]['course_title'] = $_POST['course_title'][$key];
			}
		}
		else
		{
			if (!empty($courseSession->coursetype))
			{
				$courses = $courseSession->coursetype;
			}
			else
			{
				for ($i = 0; $i < 1; $i++)
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
		$this->view->countshct = count($shortcutarray);

		$shortgrid = new Pms_Grid($shortcutarray, 1, count($shortcutarray), "CourseShortcuts.html");
		$this->view->cshortcuts = $shortgrid->renderGrid();

		/*		 * ******* Patient Information ************ */
		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

		$tm = new TabMenus();
		$this->view->tabmenus = $tm->getMenuTabs();

		/*		 * ***************************************** */

		//verlauf medications edit start
		$medic = new PatientDrugPlan();
		$drug_plans = $medic->getMedicationPlanCourse($decid);

		$patient_drugs['999999999'] = $this->view->translate('no_medications');
		foreach($drug_plans as $k_drugplan=>$v_drugplan)
		{
			if($v_drugplan['isbedarfs'] == '1')
			{
				$type='bedarfs_medi_select';
			}
			else if($v_drugplan['isivmed'] == '1')
			{
				$type='iv_medi_select';
			}
			else
			{
				$type='normal_medi_select';
			}

			if($v_drugplan['change_date'] != '0000-00-00 00:00:00')
			{
				$date = date('d.m.Y', strtotime($v_drugplan['change_date']));
			}
			else
			{
				$date = date('d.m.Y', strtotime($v_drugplan['create_date']));
			}

			if(!empty($v_drugplan['dosage']))
			{
				$dosage = ' - '.$v_drugplan['dosage'];
			}
			else
			{
				$dosage = '';
			}

			$medi_name = $date. ' - ' .$v_drugplan['medi_name'].$dosage;

			$patient_drugs['999999999'] = $this->view->translate('select_medication_edit');
			$patient_drugs[$type][$v_drugplan['id'].'-'.$v_drugplan['medication_master_id']] = $medi_name;


			$medi_name = '';
			$type = '';
			$dosage = '';
		}

		$this->view->patient_medications = $patient_drugs;
		//verlauf medications edit start


		//verlauf symptom add start
		$symperm = new SymptomatologyPermissions();
		$clientsymsets = $symperm->getClientSymptomatology($clientid);


		/*get Client symptomatology view options */
		$cl = new Client();
		$clarr = Pms_CommonData::getClientData($logininfo->clientid);
		$sympt_view_select = $clarr[0]['symptomatology_scale'];  // n-> Numbers Scale(0-10); a-> Attributes scale (none/weak/averge/strong)
		$this->view->sympt_view_select = $sympt_view_select;
		if($_REQUEST['scale']){
			print_R($sympt_view_select); exit;
		}
		/*-------------------------------- */


		if($clientsymsets)
		{
		    foreach($clientsymsets as $k_cset =>$v_cset)
		    {
			    $setsids[] = $v_cset['setid'];
		    }

		    $patsymval =  new SymptomatologyValues();
		    $patsymvalarr = $patsymval->getSymptpomatologyValues($setsids);

		    $symptoms_data['999999999'] = $this->view->translate('selectsymptom');
		    foreach($patsymvalarr as $k_symval=>$v_symval)
		    {
			    $symptoms_data[$clientsymsets[$v_symval['set']]['set_name']][$v_symval['id']] = $v_symval['sym_description'];
		    }

    		}
		else
		{
		    $sm = new SymptomatologyMaster();
		    $symarr = $sm->getSymptpomatology($clientid);

		    $symptoms_data['999999999'] = $this->view->translate('selectsymptom');
		    foreach($symarr as $k_symmaster=>$v_symmaster)
		    {
			$symptoms_data[$this->view->translate('clientsymmasterdata')][$v_symmaster['id']] = $v_symmaster['sym_description'];
		    }
		}
		$this->view->symptoms_data = $symptoms_data;
		//verlauf symptom add end
	}

	/**
	 * @deprecated, @cla on 21.09.2018
	 * you MUST NOT have 3 functions that each fetch data for the view = patientcourseAction , the pdf = printpdfcourseAction and the print printcourseAction ...
	 * please stop this practice, i've deprecated so you don't copy-paste this fn
	 */
	public function printcourseAction()
	{
	    /*
	     * @cla ispc-2071
	     */
		//ISPC - 2334
		if(!empty($_REQUEST['f']) && count($_REQUEST['f']) == 1 && $_REQUEST['f'][0] == '_shared')
		{
			$this->setParam('shortcuts', null);
			$shortcuts = null;
			$shortcuts_shared = $_REQUEST['f'];
		}
		else 
		{
			$this->setParam('shortcuts', $_REQUEST['f']);
			$shortcuts = $_REQUEST['f'];
		}
		//ISPC - 2334
	    $this->setParam('start_date', $_REQUEST['sd']);
	    $this->setParam('end_date', $_REQUEST['ed']);
	    //$this->setParam('shortcuts', $_REQUEST['f']);
	    
	    $this->patientcourseAction();
	    $allblocks_from_patientcourseAction = $this->view->course_data;
	   
	    /*
	     * TODO-2162  @cla
	     * php filter the rows to print only selected shortcuts
	     */
	    //$shortcuts = $_REQUEST['f'];
	    if ( ! empty($shortcuts) && is_array($shortcuts)) {
	        foreach ($allblocks_from_patientcourseAction as $k => &$oneRow) {
	            $oneRow['summary'] = array_filter($oneRow['summary'], function($course) use ($shortcuts) {
	                return in_array($course['course_type'], $shortcuts);
	            });
	            if (empty($oneRow['summary'])) 
	                unset($allblocks_from_patientcourseAction[$k]);
	            if(in_array('_shared', $shortcuts))
	            {
	            	if($oneRow['source_ipid'] != "")
	            	{
	            		unset($allblocks_from_patientcourseAction[$k]);
	            	}
	            }
	        }	        
	    }
	    else 
	    {
	    	if ( ! empty($shortcuts_shared) && is_array($shortcuts_shared)) {
	    		foreach ($allblocks_from_patientcourseAction as $k => &$oneRow) {	    			
    				if($oneRow['source_ipid'] != "")
    				{
    					unset($allblocks_from_patientcourseAction[$k]);
    				}
	    		}
	    	}
	    }
	    
	    	    
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->view->pid = $_GET['id'];
		$this->_helper->layout->setLayout('layout_printverlauf');

		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);

		//HS module
		$previleges = new Modules();
		$modulepriv_hs = $previleges->checkModulePrivileges("81", $logininfo->clientid);
		/* ######################################################### */

		$start_date = $_REQUEST['sd'];
		$end_date = $_REQUEST['ed'];

		$pt = new PatientMaster();
		$patarr = $pt->getMasterData(0,0,0,$ipid);
		$ptt = new PatientMaster();
		$this->view->patHeader = $ptt->getMasterData(Pms_Uuid::decrypt($_REQUEST['id']),1,0,$ipid,1,false,1,'html');

		$pfirstname = $patarr['first_name'];
		$plastname = $patarr['last_name'];
		$birthdate = $patarr['birthd'];
		$this->view->patientname = $plastname.", ".$pfirstname.""."(".$birthdate.")";

		$this->view->currdate = date("d.m.Y",time());

		/* $pt = new PatientMaster();
		$patarr = $pt->getMasterData(0,0,0,$ipid);
		$ptt = new PatientMaster();
		$this->view->patHeader = $ptt->getMasterData(Pms_Uuid::decrypt($_REQUEST['id']),1,0,$ipid,1);

		$pfirstname = $patarr['first_name'];
		$plastname = $patarr['last_name'];
		$birthdate = $patarr['birthd'];
		$this->view->patientname = $plastname.", ".$pfirstname.""."(".$birthdate.")";

		$this->view->currdate = date("d.m.Y",time()); */

		
		
		
		$period = array();
		$pcourse = new PatientCourse();
		$users = new User();
		$user_details = $users->getUserDetails($logininfo->userid);
		
		$sort_directions = array('t'=>'DESC','b'=>'ASC');
		$sort_fields = array('a'=>'course_date', 'd'=>'done_date');
		
		//set defaults
		if(empty($user_details[0]['verlauf_newest'])) { $user_details[0]['verlauf_newest'] = 'b'; }
		if(empty($user_details[0]['verlauf_action'])) { $user_details[0]['verlauf_action'] = 'a'; }
		if(empty($user_details[0]['verlauf_fload'])) { $user_details[0]['verlauf_fload'] = 'n'; }
		
		$this->view->verlauf_sort_type = $user_details[0]['verlauf_newest'];
		$this->view->verlauf_facebook_load = $user_details[0]['verlauf_fload'];
		$this->view->verlauf_action = $user_details[0]['verlauf_action'];
		
		
		
		foreach($_REQUEST['f'] as $shorcut){
			$shortcuts .= '"'.$shorcut.'",';
		}
		$shortcuts = substr($shortcuts,0,-1);

// 		$pcourse = new PatientCourse();
// 		if(strlen($shortcuts) > 0) {
// 			$allblocks = $pcourse->getCourseData($decid,0,$shortcuts,$start_date,$end_date, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']]); //grab all existing data
// 		} else {
// 			$allblocks = $pcourse->getCourseData($decid,0,0,$start_date,$end_date, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']]); //grab all existing data
// 		}
		/*
		 * @cla ispc-2071
		 */
		$allblocks = $allblocks_from_patientcourseAction;
		
		
		foreach($allblocks as $dbb_key => $dbblock){
			foreach($dbblock['summary'] as $spos) {
				$existing_blocks[] = $spos['course_type'];
			}
		}

		foreach($allblocks as $ablock){
			$allusers[] = $ablock['user'];
		}

		$allusers[] = '999999'; //prevent fehler on empty array

		//getting all user details
		$allusers_details = User::getMultipleUserDetails($allusers);
		$this->view->$allusers_details = $allusers_details;

		//adding user details to each block
		foreach($allblocks as $key=>$block){
			$allblocks[$key]['user_fname'] = $allusers_details[$block['user']]['first_name'];
			$allblocks[$key]['user_lname'] = $allusers_details[$block['user']]['last_name'];
		}

		//get client user list start
		$users = new User();
		$userarray = $users->getUserByClientid($logininfo->clientid);
		$userarraylast[] = $this->view->translate('selectuser');
		foreach ($userarray as $user)
		{
			$userarraylast[$user['id']] = trim($user['last_name']).", ".trim($user['first_name']);
			$todo_userarraylast[$user['id']] = trim($user['last_name']).", ".trim($user['first_name']);
		}
		
		$this->view->users = $userarraylast;
		$this->view->users_todo = $todo_userarraylast;
		
		
		//for the todo selectbox
		$this->view->usersnewtodos = $this->get_nice_name_multiselect();
		$flat_todo_users_selectbox = array();
		foreach($this->view->usersnewtodos as $k=>$row_user) {
			if ( ! is_array( $row_user)) { $row_user = array($k => $row_user); }
			$flat_todo_users_selectbox = array_merge($flat_todo_users_selectbox, $row_user );
		}
		$this->view->usersnewtodos_flat = $flat_todo_users_selectbox;
		
		
		//get client Sanitätshaus user
		$usergroup = new Usergroup();
		$master_group_ids = array('12'); // Sanitätshaus
		$user_groups_ids = $usergroup->getMastergroupGroups($logininfo->clientid, $master_group_ids);
		
		$sb_userarray = $users->getuserbyGroupId($user_groups_ids,$logininfo->clientid,true);
		
		$sb_userarraylast[] = $this->view->translate('selectuser');
		foreach ($sb_userarray as $sb_user)
		{
			$sb_userarraylast[$sb_user['id']] = trim($sb_user['last_name']).", ".trim($sb_user['first_name']);
		}
		
		//ISPC - 2368
		$pattodo =  new ToDos();
		$pattodos_arr = $pattodo->getTodosByClientIdAndIpid($logininfo->clientid, $ipid);
		$patcompltodocourseids = array();
		$patcompltodosdata = array();
		foreach($pattodos_arr as $kr=>$vr)
		{
			if($vr['course_id'] && $vr['iscompleted'] == '1')
			{
				$patcompltodocourseids[] = $vr['course_id'];
				$patcompltodosdata[$vr['course_id']] = $vr;
			}
		}
		$this->view->patcompltodocourseids = $patcompltodocourseids;
		$this->view->patcompltodosdata = $patcompltodosdata;
		//print_r($patcompltodosdata); exit;

		$key_first = array_keys($allblocks)[0];
		if(count($_REQUEST) > 1)
		{
			if($_REQUEST['pun'] == '1')
			{
				//$allblocks[0]['print_user'] = true;
				$allblocks[$key_first]['print_user'] = true;
			}
		}
		else 
		{
			//$allblocks[0]['print_user'] = true;
			$allblocks[$key_first]['print_user'] = true;
		}
		
		$grid = new Pms_Grid($allblocks,1,count($allblocks),"listpatientcourse.html");
		$this->view->gridcourse = $grid->renderGrid();
		
		$kshortcut = array();
		$filter_shorcuts = Courseshortcuts::getFilterCourseData('canview', true);
		foreach($filter_shorcuts as $fshrt) {
			if(in_array($fshrt['shortcut'], $existing_blocks)){
				$existing_filter_shorcuts[] = $fshrt;
				$kshortcut[] = $fshrt['shortcut'];
			}
		}
		$this->view->filter_shortcuts = $existing_filter_shorcuts;





		$cs = new Courseshortcuts();
		$shortcutarray = $cs->getCourseData();
		$this->view->countshct = count($shortcutarray);
		//remove HS Shortcut if module is not activated
		if(!$modulepriv_hs)
		{
			foreach($shortcutarray as $k_short => $v_short)
			{
				if($v_short['shortcut'] == 'HS')
				{
					unset($shortcutarray[$k_short]);
				}
			}
		}
		
		$patient_shared_shortcuts_arr = array();
		
		if ( ! empty($shortcutarray)) {
			$patient_shared_shortcuts = Doctrine_Query::create()
			->select("distinct(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'))")
			->from('PatientCourse')
			->where('ipid = ?', $ipid)
			->andWhere('`source_ipid` !=  ""')
			->andWhereIn("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "')", $kshortcut)
			->orderBy('course_date ASC');
			$patient_shared_shortcuts_arr = $patient_shared_shortcuts->fetchArray();
		}
		
		if(!empty($patient_shared_shortcuts_arr))
		{
			$this->view->hasSharedShortcuts = true;
		}
		
		$shortgrid = new Pms_Grid($shortcutarray, 1, count($shortcutarray), "CourseShortcuts.html");
		$this->view->cshortcuts = $shortgrid->renderGrid();
	}

	
	/**
	 * @deprecated , @cla on 21.09.2018
	 * you MUST NOT have 3 functions that each fetch data for the view = patientcourseAction , the pdf = printpdfcourseAction and the print printcourseAction ...
	 * please stop this practice, i've deprecated so you don't copy-paste this fn
	 */
	public function printpdfcourseAction()
	{
	    
	    /*
	     * @cla ispc-2071
	     */
		//ISPC - 2334
		if(!empty($_REQUEST['f']) && count($_REQUEST['f']) == 1 && $_REQUEST['f'][0] == '_shared')
		{
			$this->setParam('shortcuts', null);
			$shortcuts = null;
			$shortcuts_shared = $_REQUEST['f'];
		}
		else
		{
			$this->setParam('shortcuts', $_REQUEST['f']);
			$shortcuts = $_REQUEST['f'];
		}
		//ISPC - 2334
	    $this->setParam('start_date', $_REQUEST['sd']);
	    $this->setParam('end_date', $_REQUEST['ed']);
	    //$this->setParam('shortcuts', $_REQUEST['f']);
	     
	    $this->patientcourseAction();
	    $allblocks_from_patientcourseAction = $this->view->course_data;
	    
	    /*
	     * TODO-2162  @cla
	     * php filter the rows to print only selected shortcuts
	     */
	    //$shortcuts = $_REQUEST['f'];
	    if ( ! empty($shortcuts) && is_array($shortcuts)) {
	        foreach ($allblocks_from_patientcourseAction as $k => &$oneRow) {
	            $oneRow['summary'] = array_filter($oneRow['summary'], function($course) use ($shortcuts) {
	                return in_array($course['course_type'], $shortcuts);
	            });
	            if (empty($oneRow['summary']))
	                unset($allblocks_from_patientcourseAction[$k]);
                if(in_array('_shared', $shortcuts))
                {
                	if($oneRow['source_ipid'] != "")
                	{
                		unset($allblocks_from_patientcourseAction[$k]);
                	}
                }
	        }
	    }
	    else
	    {
	    	if ( ! empty($shortcuts_shared) && is_array($shortcuts_shared)) {
	    		foreach ($allblocks_from_patientcourseAction as $k => &$oneRow) {
	    			if($oneRow['source_ipid'] != "")
	    			{
	    				unset($allblocks_from_patientcourseAction[$k]);
	    			}
	    		}
	    	}
	    }
	    
	    $this->_helper->viewRenderer->setNoRender();
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$print_data['pid'] = $_GET['id'];
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		
		if($_REQUEST['ts'] == "larger")
		{
		    $print_data['font_size'] = "12";
		}
		elseif($_REQUEST['ts'] == "large")
		{
		    $print_data['font_size'] = "10";
		}		
		else 
		{
		    $print_data['font_size'] = "8";
		}
		
		//HS module
		$previleges = new Modules();
		$modulepriv_hs = $previleges->checkModulePrivileges("81", $logininfo->clientid);
		/* ######################################################### */

		$start_date = $_REQUEST['sd'];
		$end_date = $_REQUEST['ed'];

		$pt = new PatientMaster();
		$patarr = $pt->getMasterData(0,0,0,$ipid);
		if($_REQUEST['pph'] == "1"){
    		$ptt = new PatientMaster();
    		$patHeader = $ptt->getMasterData(Pms_Uuid::decrypt($_REQUEST['id']),1,0,$ipid,1,false,1,'pdf');
    		$print_data['patHeader'] = $patHeader;
		} else {
    		$print_data['patHeader'] = "";
		}
		
// 		print_r($print_data['patHeader']); exit;
		
		$pfirstname = $patarr['first_name'];
		$plastname = $patarr['last_name'];
		$birthdate = $patarr['birthd'];
		$patientname = $plastname.", ".$pfirstname.""."(".$birthdate.")";
		$print_data['patientname'] = $patientname;

		$currdate = date("d.m.Y",time());
		$print_data['currdate'] = $currdate;

		


		$period = array();
		$pcourse = new PatientCourse();
		$users = new User();
		$user_details = $users->getUserDetails($logininfo->userid);
		
		$sort_directions = array('t'=>'DESC','b'=>'ASC');
		$sort_fields = array('a'=>'course_date', 'd'=>'done_date');
		
		//set defaults
		if(empty($user_details[0]['verlauf_newest'])) { $user_details[0]['verlauf_newest'] = 'b'; }
		if(empty($user_details[0]['verlauf_action'])) { $user_details[0]['verlauf_action'] = 'a'; }
		if(empty($user_details[0]['verlauf_fload'])) { $user_details[0]['verlauf_fload'] = 'n'; }
		
		$this->view->verlauf_sort_type = $user_details[0]['verlauf_newest'];
		$this->view->verlauf_facebook_load = $user_details[0]['verlauf_fload'];
		$this->view->verlauf_action = $user_details[0]['verlauf_action'];
		
		if($_REQUEST['pun'] == "1"){
			$print_data['print_user'] = true;
		}
		
		
		
		
		foreach($_REQUEST['f'] as $shorcut){
			$shortcuts .= '"'.$shorcut.'",';
		}
		$shortcuts = substr($shortcuts,0,-1);

// 		$pcourse = new PatientCourse();
// 		if(strlen($shortcuts) > 0) {
// 			$allblocks = $pcourse->getCourseData($decid,0,$shortcuts,$start_date,$end_date, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']]); //grab all existing data
// 		} else {
// 			$allblocks = $pcourse->getCourseData($decid,0,0,$start_date,$end_date, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']]); //grab all existing data
// 		}
		/*
	     * @cla ispc-2071
	     */
		$allblocks = $allblocks_from_patientcourseAction;
		
		
		
		
		
		
		foreach($allblocks as $dbb_key => $dbblock){
			foreach($dbblock['summary'] as $spos) {
				$existing_blocks[] = $spos['course_type'];
			}
		}

		foreach($allblocks as $ablock){
			$allusers[] = $ablock['user'];
		}

		$allusers[] = '999999'; //prevent fehler on empty array

		//getting all user details
		$allusers_details = User::getMultipleUserDetails($allusers);

		//adding user details to each block
		foreach($allblocks as $key=>$block){
			$allblocks[$key]['user_fname'] = $allusers_details[$block['user']]['first_name'];
			$allblocks[$key]['user_lname'] = $allusers_details[$block['user']]['last_name'];
		}

		//get client user list start
		$users = new User();
		$userarray = $users->getUserByClientid($logininfo->clientid);
		$userarraylast[] = $this->view->translate('selectuser');
		$allarraytodo['a'] = $this->view->translate('all');
		foreach ($userarray as $user)
		{
			$userarraylast[$user['id']] = trim($user['last_name']).", ".trim($user['first_name']);
			$todo_userarraylast[$user['id']] = trim($user['last_name']).", ".trim($user['first_name']);
		    $userarraytodo['u'.$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
		}
		
		$todogroups = array();
		$usergroup = new Usergroup();
		$todogroups = $usergroup->getClientGroups($logininfo->clientid);
		foreach ($todogroups as $group)
		{
		    $grouparraytodo['g'.$group['id']] = trim($group['groupname']);
		}
		
		asort($userarraytodo);
		asort($grouparraytodo);
		
		foreach($grouparraytodo as $k_user => $v_user) {
		    $allarraytodo[$k_user] = $v_user;
		}
		foreach($userarraytodo as $k_user => $v_user) {
		    $allarraytodo[$k_user] = $v_user;
		}
		$print_data['usersnewtodos'] = $allarraytodo;
		
		$print_data['users'] = $userarraylast;
		$print_data['users_todo'] = $todo_userarraylast;
		
		//for the todo selectbox
		$print_data['usersnewtodos'] = $this->get_nice_name_multiselect();
		$flat_todo_users_selectbox = array();
		foreach($print_data['usersnewtodos'] as $k=>$row_user) {
			if ( ! is_array( $row_user)) { $row_user = array($k => $row_user); }
			$flat_todo_users_selectbox = array_merge($flat_todo_users_selectbox, $row_user );
		}
		$print_data['usersnewtodos_flat'] = $flat_todo_users_selectbox;
		
		//get client Sanitätshaus user
		$usergroup = new Usergroup();
		$master_group_ids = array('12'); // Sanitätshaus
		$user_groups_ids = $usergroup->getMastergroupGroups($logininfo->clientid, $master_group_ids);
		
		$sb_userarray = $users->getuserbyGroupId($user_groups_ids,$logininfo->clientid,true);
		
		$sb_userarraylast[] = $this->view->translate('selectuser');
		foreach ($sb_userarray as $sb_user)
		{
			$sb_userarraylast[$sb_user['id']] = trim($sb_user['last_name']).", ".trim($sb_user['first_name']);
		}
		
		
// 		print_r($allblocks); exit;
		
// 		$grid = new Pms_Grid($allblocks,1,count($allblocks),"listpatientcourse.html");
// 		$this->view->gridcourse = $grid->renderGrid();
// 		$gridcourse = $grid->renderGrid();
// 		$print_data['gridcourse'] = $gridcourse; 
		$print_data['allblocks'] = $allblocks; 
		
		$filter_shorcuts = Courseshortcuts::getFilterCourseData('canview', true);
		foreach($filter_shorcuts as $fshrt) {
			if(in_array($fshrt['shortcut'], $existing_blocks)){
				$existing_filter_shorcuts[] = $fshrt;
			}
		}
// 		$this->view->filter_shortcuts = $existing_filter_shorcuts;
		$print_data['filter_shortcuts'] = $existing_filter_shorcuts;




		$cs = new Courseshortcuts();
		$shortcutarray = $cs->getCourseData();
		$this->view->countshct = count($shortcutarray);
		$countshct = count($shortcutarray);
		$print_data['countshct'] = $countshct;
		
		//remove HS Shortcut if module is not activated
		if(!$modulepriv_hs)
		{
			foreach($shortcutarray as $k_short => $v_short)
			{
				if($v_short['shortcut'] == 'HS')
				{
					unset($shortcutarray[$k_short]);
				}
			}
		}
		$this->view->no_color = $_REQUEST['nc'];
		$shortgrid = new Pms_Grid($shortcutarray, 1, count($shortcutarray), "CourseShortcuts_pdf.html");
// 		$this->view->cshortcuts = $shortgrid->renderGrid();
		$print_data['cshortcuts'] = $shortgrid->renderGrid();
		
		//ISPC - 2368
		$pattodo =  new ToDos();
		$pattodos_arr = $pattodo->getTodosByClientIdAndIpid($logininfo->clientid, $ipid);
		$patcompltodocourseids = array();
		$patcompltodosdata = array();
		foreach($pattodos_arr as $kr=>$vr)
		{
			if($vr['course_id'] && $vr['iscompleted'] == '1')
			{
				$patcompltodocourseids[] = $vr['course_id'];
				$patcompltodosdata[$vr['course_id']] = $vr;
			}
		}
		$print_data['patcompltodocourseids'] = $patcompltodocourseids;
		$print_data['patcompltodosdata'] = $patcompltodosdata;
		//print_r($patcompltodosdata); exit;
		
// 		print_R($print_data); exit;
		$this->generate_pdf(3, $print_data, "patient_course","patient_course_pdf.html");
		
	}

	private function retainValues($values)
	{

		foreach($values as $key=>$val)
		{
			if(!is_array($val))
			{
				$this->view->$key = $val;
			}
		}
	}


	public function savewrongcdAction()
	{
		$this->_helper->viewRenderer->setNoRender();


		$pc = new Application_Form_PatientCourse();
		$ids = $_REQUEST['ids'];
		$comment = $_REQUEST['comment'];
		$val = $_REQUEST['val'];

		$a_post['ids'] = $ids;
		$a_post['comment'] = $comment;
		$a_post['val'] = $val;

		$tc = $pc->UpdateWrongEntry($a_post);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBackWrong";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['id'] = $_REQUEST['blockcnt'];
		$response['callBackParameters']['val'] = $val;
		$response['callBackParameters']['comment'] = $comment;

		$response['callBackParameters']['extra_params'] = $pc->extra_params_mainpage;

		echo json_encode($response);
		exit;
	}

	public function requestmedicationdataAction()
	{
		$decid = Pms_Uuid::decrypt($_REQUEST['id']);

		$this->_helper->viewRenderer->setNoRender();

		$medication_master_id = $_REQUEST['mmid'];
		$medication_id = $_REQUEST['mid'];

		$medic = new PatientDrugPlan();
		$drug_plans = $medic->getMedicationPlanCourse($decid, $medication_id);

		echo json_encode($drug_plans[0]);
		exit;
	}

	public function patientcourseAction()
	{
		$decid = ! empty($this->dec_id) ? $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
// 		$this->view->pid = $_GET['id'];
		$this->view->pid = ! empty($this->enc_id) ? $this->enc_id : $_GET['id'];
		
		$ipid = ! empty($this->ipid) ? $this->ipid : Pms_CommonData::getIpid($decid);
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
// 		$logininfo = $this->logininfo;
		
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$allusers = array();
		
		$this->view->userid = $logininfo->userid;
		$this->view->clientid = $clientid;
		$this->view->usertype = $logininfo->usertype;
		
		if($_REQUEST['mod'] == 'minimal')
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$this->_helper->viewRenderer('newpatientcourse_min');
		}

	    $session_PatientCourse_Fetched_Contactforms = new Zend_Session_Namespace('PatientCourse_Fetched_Contactforms');
	    
    	//TODO-3365 Carmen 21.08.2020
	    //get client settings for pharmaindex values got from mmi
	    $client_details = Client::getClientDataByid($clientid);
	    
	    if( ! empty($client_details)){
	    	if($client_details[0]['pharmaindex_settings'])
	    	{
	    		$this->view->js_pharmaindex_settings = json_encode($client_details[0]['pharmaindex_settings']);
	    	}
    		else
    		{
    			$this->view->js_pharmaindex_settings = json_encode(array(
    					'atc' => 'yes',
    					'drug' => 'yes',
    					'unit' => 'no',
    					'takinghint' => 'no',
    					'type' => 'no'
    			));
    		}
	    }		    
	    //--
	    
		if($_REQUEST['mod'] != 'minimal')
		{
		    //this are needed for ajaxloader, and here he reset
		    $session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid] = null;//this are cf ids
		    $session_PatientCourse_Fetched_Contactforms->recalculated_offset_diff[$ipid] = 0;
		    $session_PatientCourse_Fetched_Contactforms->allready_fetched_ids[$ipid] = [];//this are pc ids
		    
			$previleges = new Modules();
			
			//Shortcut LNR module
			$modulepriv = $previleges->checkModulePrivileges("55", $logininfo->clientid);
			if ($modulepriv)
			{
				$this->view->modulepriv = "1";
			}
			else
			{
				$this->view->modulepriv = "0";
			}


			//Shortcut Leistungen - patient course L Livesearch
// 			$previlegesl= new Modules();
			$moduleprivl = $previleges->checkModulePrivileges("101", $logininfo->clientid);
			if ($moduleprivl)
			{
				$this->view->modulepriv_l = "1";
			}
			else
			{
				$this->view->modulepriv_l = "0";
			}
			
			//Shortcut Bavaria module
			$modulepriv_bav = $previleges->checkModulePrivileges("60", $logininfo->clientid);
			if ($modulepriv_bav)
			{
				$this->view->modulepriv_bav = "1";
			}
			else
			{
				$this->view->modulepriv_bav = "0";
			}

			//Maria:: Migration CISPC to ISPC 20.08.2020
			//ISPC-2651 Modul for shortcut G, which add date and time to the shortcut, elena, 14.08.2020
			//Module for shortcut G (show date/time)
			$modulepriv_g = $previleges->checkModulePrivileges("1010", $logininfo->clientid);
			if ($modulepriv_g)
			{
				$this->view->modulepriv_g = "1";
			}
			else
			{
				$this->view->modulepriv_g = "0";
			}


			//No auto xt comment module
			$modulenoauto = $previleges->checkModulePrivileges("84", $logininfo->clientid);


			if ($modulenoauto)
			{
				$this->view->module_noauto = "1";
			}
			else
			{
				$this->view->module_noauto = "0";
			}

			
			//HB shortcut 
			$module_hb= $previleges->checkModulePrivileges("121", $logininfo->clientid);
			if($module_hb)
			{
				$this->view->module_hb= "1";
			}
			else
			{
				$this->view->module_hb= "0";
			}
			

			//LE shortcut 
			$module_le= $previleges->checkModulePrivileges("128", $logininfo->clientid);
			if($module_le)
			{
				$this->view->module_le= "1";
			}
			else
			{
				$this->view->module_le= "0";
			}
			
			//XS shortcut 
			$module_xs= $previleges->checkModulePrivileges("145", $logininfo->clientid);
			if($module_xs)
			{
				$this->view->module_xs= "1";
			}
			else
			{
				$this->view->module_xs= "0";
			}
			//XE shortcut 
			$module_xe= $previleges->checkModulePrivileges("146", $logininfo->clientid);
			if($module_xe)
			{
				$this->view->module_xe= "1";
			}
			else
			{
				$this->view->module_xe= "0";
			}
			
			//ISPC-1979 duplicate the V shortcut We will add it to all "NIE_" clients
			$module_vo_ve= $previleges->checkModulePrivileges("152", $logininfo->clientid);
			if($module_vo_ve)
			{
				$this->view->module_vo_ve= "1";
			}
			else
			{
				$this->view->module_vo_ve= "0";
			}

			// ISPC-2387
			$module_pk= $previleges->checkModulePrivileges("187", $logininfo->clientid);
			if($module_pk)
			{
				$this->view->module_pk= "1";
			}
			else
			{
				$this->view->module_pk= "0";
			}
			$module_xn= $previleges->checkModulePrivileges("188", $logininfo->clientid);
			if($module_xn)
			{
				$this->view->module_xn = "1";
			}
			else
			{
				$this->view->module_xn = "0";
			}
			//TODO-2683 Ancuta 22.11.2019 Special ML shortcut  LIKE XT or XN 
			$module_ml= $previleges->checkModulePrivileges("205", $logininfo->clientid);
			if($module_ml)
			{
				$this->view->module_ml = "1";
			}
			else
			{
				$this->view->module_ml = "0";
			}
			//
			
			//ISPC-2486 Ancuta 20.11.2019
			$module203_rlp_special_sh= $previleges->checkModulePrivileges("203", $logininfo->clientid);
			if($module203_rlp_special_sh)
			{
				$this->view->module203_rlp_special_sh = "1";
			}
			else
			{
				$this->view->module203_rlp_special_sh = "0";
			}
			
			
			//TODO-2749 Ancuta 13.12.2019
			$module209_demstepcare_special_sh= $previleges->checkModulePrivileges("209", $logininfo->clientid);
			if($module209_demstepcare_special_sh)
			{
			    $this->view->module209_demstepcare_special_sh = "1";
			}
			else
			{
			    $this->view->module209_demstepcare_special_sh = "0";
			}
			
			//TODO-2942 Carmen 24.02.2020 Special XM shortcut LIKE XT
			$module_xm= $previleges->checkModulePrivileges("217", $logininfo->clientid);
			if($module_xm)
			{
				$this->view->module_xm = "1";
			}
			else
			{
				$this->view->module_xm = "0";
			}
			//
			
			//TODO-2942 Carmen 24.02.2020 Special XH shortcut LIKE XT for client WL_Muenster
			$module_xh= $previleges->checkModulePrivileges("218", $logininfo->clientid);
			if($module_xh)
			{
				$this->view->module_xh = "1";
			}
			else
			{
				$this->view->module_xh = "0";
			}
			//
			
			//TODO-2942 Carmen 24.02.2020 Special XH shortcut LIKE V
			$module_xg= $previleges->checkModulePrivileges("219", $logininfo->clientid);
			if($module_xg)
			{
				$this->view->module_xg = "1";
			}
			else
			{
				$this->view->module_xg = "0";
			}
			//
		
			//ISPC-2902 Lore 27.04.2021
			$module_companion_xt= $previleges->checkModulePrivileges("255", $logininfo->clientid);
			if($module_companion_xt) {
			    $this->view->module_companion_xt = "1";
			} else {
			    $this->view->module_companion_xt = "0";
			}
			//
			
			/* ######################################################### */
			
			//HS module
// 			$previleges = new Modules();
			$modulepriv_hs = $previleges->checkModulePrivileges("81", $logininfo->clientid);
			/* ######################################################### */
		
			$isclient = Pms_CommonData::getPatientClient($decid, $logininfo->clientid);

			if (!$isclient)
			{
				$this->_redirect(APP_BASE . "overview/overview");
			}

			$isdicharged = PatientDischarge::isDischarged($decid);
			$this->view->isdischarged = 0;
			if ($isdicharged)
			{
				$this->view->isdischarged = 1;
			}

			/* ######################################################### */
		}

		/*
		 * @cla removed next  
		 */
		/*
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canview');

		if (!$return)
		{

			$this->view->style = 'none;';
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canadd');
			if (!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		}
		else
		{

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patientcourse', $logininfo->userid, 'canadd');
			if (!$return)
			{
				$this->view->coursestyle = 'none;';
			}
		}
		*/

		
		
		/*
		 * @cla
		 * what are u doying with $epid ??
		 * removed
		 */
// 		$epid = Pms_CommonData::getEpid($ipid);
		
		$modules = new Modules();
// 		$modules = $previleges;
		if($modules->checkModulePrivileges("87", $logininfo->clientid))
		{
			$this->view->show_mmi = "1";
		}
		else
		{
			$this->view->show_mmi = "0";
		}
			
		if($this->view->show_mmi == '1')
		{
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);

			$this->view->kassen_no = $healthinsu_array[0]['kvk_no'];
		}
		
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		{
			
			$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
			$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
			
			$acknowledge = "1";
		}
		else
		{
			$acknowledge = "0";
		}
		
		//SD shortcut 2016.08.24
		
		if($modules->checkModulePrivileges("133", $clientid))
		{
			$this->view->module_sd= "0"; //deactivated on client request - Daniel
		}
		else
		{
			$this->view->module_sd= "0";
		}
		
		//AL shortcut ISPC-1696 2016.08.26
		if($modules->checkModulePrivileges("134", $clientid))
		{
			$this->view->module_al= "1"; 
		}
		else
		{
			$this->view->module_al= "0";
		}
		
		//ISPC-2604 Lore 20.10.2020
		$module_divider_verlauf= $modules->checkModulePrivileges("244", $clientid);
		if($module_divider_verlauf)	{
		    $this->view->module_divider_verlauf = "1";
		} else {
		    $this->view->module_divider_verlauf = "0";
		}
        //.
        //ISPC-2876,Elena,15.04.2021
        $module_ca_permissions = $modules->checkModulePrivileges("1019", $clientid);//Hotfix Nico 26.04.2021
        $this->view->ca_delete_permissions = '0';
		if($module_ca_permissions && ($logininfo->usertype == "CA" )) {
            $this->view->ca_delete_permissions = '1';
        }
		/*
		 * ALL cleint modules are sent as array... or if you like continue adding variable names for each of them..
		 */
		$this->view->clientModules = $modules->get_client_modules($clientid);
		
		// 07.07
		$abb = "'HD','ND'";
		$dg = new DiagnosisType();
		$darr = $dg->getDiagnosisTypes($logininfo->clientid, $abb);
		$this->view->dtypearray = $darr;
		$this->view->jdarr = json_encode($darr);

		$dm = new DiagnosisMeta();
		$diagnosismeta = $dm->getDiagnosisMetaData(1);

		$this->view->diagnosismeta = $diagnosismeta;
		$this->view->jsdiagnosismeta = json_encode($diagnosismeta);

		// 07.07
		$this->view->patcrclass = "active";
		$this->view->act = "patientcourse/patientcourse?id=" . $_GET['id'];
		$this->view->callcourse = "";
		$this->view->curr_date = date("d.m.Y", time());
		$this->view->pid = $_GET['id'];

		if ($this->getRequest()->isPost())
		{
			$_POST['skip_trigger'] = "0";
			if($acknowledge == "1"){
				$_POST['skip_trigger'] = "1";
			}
			
			//unset diagnosis from post!!!
			$post = $_POST;
			unset($post['icdnumber']);
			unset($post['diagnosis']);
			unset($post['hidd_icdnumber']);
			unset($post['hidd_diagnosis']);
			unset($post['hidd_tab']);
			unset($_POST);

			
			
			//exclude hs entries from being inserted in patient course if we allready have HS in diagnosis
			if($modulepriv_hs)
			{
				$patient_hs_diagnosis = PatientDiagnosis::check_hs_diagnosis($clientid, $ipid);
				
				foreach($post['course_type'] as $k_course => $v_course)
				{
					if($v_course == 'HS' && $patient_hs_diagnosis)
					{
						unset($post['course_type'][$k_course]);
						unset($post['course_title'][$k_course]);
					}
				}
				$post['course_type'] = array_values($post['course_type']);
				$post['course_title'] = array_values($post['course_title']);
			}
			

			//  do not insert medication if no rights
			if($acknowledge == "1" ){
				if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA'){
					
				}
				else
				{	 // do not allow to add medication			
    				foreach($post['course_type'] as $k_course => $v_course)
    				{
    					if($v_course == 'M'  || $v_course == 'N' || $v_course == 'I' || $v_course == 'BP')
    					{
    						unset($post['course_type'][$k_course]);
    						unset($post['course_title'][$k_course]);
    					}
    				}
    				$post['course_type'] = array_values($post['course_type']);
    				$post['course_title'] = array_values($post['course_title']);
    				
    				$misc = "Medication change  Permission Error - Insert medication from VERLAUF";
    				PatientPermissions::MedicationLogRightsError(false,$misc);
    				
				}
				
			}
			
			
			$_POST = $post;
//			print_r($_POST);
//			exit;
			$course_medications = new Application_Form_PatientDrugPlan();
			$course_medi_edit = $course_medications->UpdateMultiDataMedicationsVerlauf($_POST);

			$course_symptoms = new Application_Form_PatientSymptomatology();
			$course_symptom_add = $course_symptoms->InsertMultipleDataFromVerlauf($ipid, $_POST);

			$course_form = new Application_Form_PatientCourse();

			if ($course_form->validate($_POST))
			{
				$course_form->InsertData($_POST);
				$datainserted = 1;
				$courseSession = new Zend_Session_Namespace('courseSession');
				$courseSession->coursetype = array ();
			}
			else
			{
				$datainserted = 0;
				$course_form->assignErrorMessages();
				$this->retainValues($_POST);
			}

			//$courseSession = new Zend_Session_Namespace('courseSession');
			//$courseSession->coursetype = array ();
		}
		
		
		
		if($modulepriv_hs)
		{
			$patient_hs_diagnosis = PatientDiagnosis::check_hs_diagnosis($clientid, $ipid);
		}
		
		if($patient_hs_diagnosis)
		{
			$this->view->disable_hs_insert = '1';
		}
		else
		{
			$this->view->disable_hs_insert = '0';
		}

		//get client user list start
		$users = new User();
		$userarray = $users->getUserByClientid($logininfo->clientid);		
		$userarraylast[] = $this->view->translate('selectuser');
//		$userarraytodo['a'] = $this->view->translate('all');
		$allarraytodo['a'] = $this->view->translate('all');
		$user_array_le["-1"] = "Team";
		$userarraytodo['u000000'] = " -----------------";
		foreach ($userarray as $user)
		{
			$userarraylast[$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
			$userarraytodo['u'.$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
			$user_array_le[$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
			$todo_userarraylast[$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
		}
		
		$this->view->users = $userarraylast;
		$this->view->users_le = $user_array_le;
		$this->view->users_todo = $todo_userarraylast;
		
		/*
		//new list for todos(all, groups, users)
		$usergroup = new Usergroup();
		$todogroups = $usergroup->getClientGroups($clientid);
		$grouparraytodo['g000000'] = "-----------------";
		foreach ($todogroups as $group)
		{
			$grouparraytodo['g'.$group['id']] = trim($group['groupname']);
		}
		asort($userarraytodo);
		asort($grouparraytodo);
		
		foreach($grouparraytodo as $k_user => $v_user) {
			$allarraytodo[$k_user] = $v_user;
		}
		foreach($userarraytodo as $k_user => $v_user) {
			$allarraytodo[$k_user] = $v_user;
		}

		$user_pseudo =  new UserPseudoGroup();
		$user_ps =  $user_pseudo->get_pseudogroups_for_todo($clientid);
		if (is_array($user_ps)) {
			
			$allarraytodo['pseudogroups_header'] = "-----------------";

			foreach($user_ps as $row) {
				$allarraytodo["pseudogroup_".$row['id']] = $row['servicesname'];
			}
			$this->view->pseudo_usersnewtodos = $user_ps;
		}
		
		$this->view->usersnewtodos = $allarraytodo;
		*/
		
		$this->view->usersnewtodos = $this->get_nice_name_multiselect();
		
		$flat_todo_users_selectbox = array();
		foreach($this->view->usersnewtodos as $k=>$row_user) {
			if ( ! is_array( $row_user)) { $row_user = array($k => $row_user); }
			$flat_todo_users_selectbox = array_merge($flat_todo_users_selectbox, $row_user );
		}
		$this->view->usersnewtodos_flat = $flat_todo_users_selectbox;
				
// 		print_r($userarraytodo);
		//ksort($userarraytodo);
		
// 		print_r($userarraytodo);
// 		exit;
		//$this->view->usersnewtodos = $userarraytodo;
		//var_dump($userarraytodo); exit;
		
		//get client Sanitätshaus user  
		$usergroup = new Usergroup();
		$master_group_ids = array('12'); // Sanitätshaus
		$user_groups_ids = $usergroup->getMastergroupGroups($logininfo->clientid, $master_group_ids);
		
		$sb_userarray = $users->getuserbyGroupId($user_groups_ids,$logininfo->clientid,true);
		
		$sb_userarraylast[] = $this->view->translate('selectuser');
		foreach ($sb_userarray as $sb_user)
		{
			$sb_userarraylast[$sb_user['id']] = trim($sb_user['user_title'])." ".trim($sb_user['last_name']).", ".trim($sb_user['first_name']);
		}
		
		$this->view->sb_users = $sb_userarraylast;
 
		//shortcuts
		$cs = new Courseshortcuts();
		$ltrarray = $cs->getFilterCourseData();

		$letterarray = array ();
		$lettersforjs = array ();


		//remove HS Shortcut from filters too
		foreach ($ltrarray as $key => $value)
		{
			$letterarray[$value['shortcut']] = $value['course_fullname'];
			
			if(!$modulepriv_hs)
			{
				if($value['shortcut'] == 'HS')
				{
					unset($letterarray['HS']);
				}
			}
		}

		$js = new Courseshortcuts();
		$jsarr = $js->getFilterCourseData('canedit'); //get shortcuts to be used

		foreach ($jsarr as $key => $value)
		{
			$lettersforjs[] = $value['shortcut'];
		}
		
		$lettersforjs[] = '_shared';
		$lettersforjs[] = '_owned';

		$jsfarr = $js->getFilterCourseData();
		$hkforjs = array();
		foreach ($jsfarr as $key => $value)
		{
			$hkforjs[] = $value['shortcut'];
		}

// 		$hkforjs[] = 999999;

		$this->view->ltrjs = json_encode($lettersforjs);
		$this->view->ltrjsarr = $lettersforjs;

		$patientarray = array();
		$patient_shared_shortcuts_arr = array();
		
		//@claudiu added IF and removed $hkforjs[] = 999999;
        if ( ! empty($hkforjs)) {
               
    		$patient = Doctrine_Query::create()
    			   ->select("distinct(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'))")
    			   ->from('PatientCourse')
    			   ->where('ipid = ?', $ipid)
    			   ->andWhere('`source_ipid` =  ""')
    			->andWhereIn("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "')", $hkforjs)
    			   ->orderBy('course_date ASC');
    		$patientarray = $patient->fetchArray();
//     dd($patient);
    		$patient_shared_shortcuts = Doctrine_Query::create()
    			   ->select("distinct(AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'))")
    			   ->from('PatientCourse')
    			   ->where('ipid = ?', $ipid)
    			   ->andWhere('`source_ipid` !=  ""')
    			   ->andWhereIn("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "')", $hkforjs)
    			   ->orderBy('course_date ASC');
    		$patient_shared_shortcuts_arr = $patient_shared_shortcuts->fetchArray();
        }


		$finalarr = array ();
		$finalarr_shared = array ();
		$hotkeys = array ();
		$postdisplay = array ();
		$postcnt = 0;
// dd($patientarray);
		//@claudiu added IF and removed foreach do query
		if ( ! empty($patientarray)) {
		    
		    $patientarray_letters =  array_column($patientarray, 'distinct');
		    
		    $shorts = new Courseshortcuts();
		    $Courseshortcuts_arr = $shorts->getCourseDataByShortcut($patientarray_letters);
		    $coursearr_multi = array();
		    foreach ($Courseshortcuts_arr as $row) {
		        //if you have multiple with same letter you are toasted
		        $coursearr_multi [$row['shortcut']] = $row;
		    }
		
    		//the foreach from hell
    		foreach ($patientarray as $key => $value)
    		{
    			$distval = $value['distinct'];
    			
    			//@TODO optimize this
    
//     			$shorts = new Courseshortcuts();
//     			$coursearr = $shorts->getCourseDataByShortcut($distval);
    			$coursearr = array( 0 => $coursearr_multi[$distval]); //@claudiu
    
				if ($_POST[$distval] == 1)
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
    
    
    			if ($coursearr[0]['isfilter'] == 1)
    			{
    				foreach ($letterarray as $key => $val)
    				{
    					if ($key == $distval)
    					{
    						array_push($finalarr, array (
    						    'cletter' => $distval,
    						    'ctype' => $letterarray[$distval],
    						    'font_color' => $coursearr[0]['font_color'],
    						    'isbold' => $coursearr[0]['isbold'],
    						    'isitalic' => $coursearr[0]['isitalic'],
    						    'isunderline' => $coursearr[0]['isunderline'],
    						    'chk' => $chk,
    						));
    
    					}
    				}
    			}
    
    			$hotkeys[] = $distval;
    		}
		
		}
		
		//@claudiu added IF and removed foreach do query
		if ( ! empty($patient_shared_shortcuts_arr)) {
		
		    $patient_shared_shortcuts_arr_letters =  array_column($patient_shared_shortcuts_arr, 'distinct');
		
		    $shorts = new Courseshortcuts();
		    $Courseshortcuts_arr = $shorts->getCourseDataByShortcut($patient_shared_shortcuts_arr_letters);
		    $coursearr_multi = array();
		    foreach ($Courseshortcuts_arr as $row) {
		        //if you have multiple with same letter you are toasted
		        $coursearr_multi [$row['shortcut']] = $row;
		    }
    		    
    		//another foreach from hell for shared shortcuts
    		foreach ($patient_shared_shortcuts_arr as $s_key => $s_value)
    		{
    			$s_distval = $s_value['distinct'];
    
    			//@TODO optimize this
//     			$shorts = new Courseshortcuts();
//     			$coursearr_shared = $shorts->getCourseDataByShortcut($s_distval);
    			$coursearr_shared = array( 0 => $coursearr_multi[$s_distval]); //@claudiu
    
    			if ($_POST[$s_distval] == 1)
    			{
    				$s_postcnt++;
    				$chk = 1;
    				$chkval.= $comma . "'" . $s_distval . "'";
    				$comma = ",";
    				$s_postdisplay[$s_distval] = 1;
    			}
    			else
    			{
    				$chk = 0;
    				$s_postdisplay[$s_distval] = 0;
    			}
    
    			if ($coursearr_shared[0]['isfilter'] == 1)
    			{
    				foreach ($letterarray as $key => $val)
    				{
    					if ($key == $s_distval)
    					{
    						array_push($finalarr_shared, array (
    						    'cletter' => $s_distval."_shared",
    						    'ctype' => $letterarray[$s_distval],
    						    'font_color' => $coursearr_shared[0]['font_color'],
    						    'isbold' => $coursearr_shared[0]['isbold'],
    						    'isitalic' => $coursearr_shared[0]['isitalic'],
    						    'isunderline' => $coursearr_shared[0]['isunderline'],
    						    'chk' => $chk,
    						));
    
    					}
    				}
    			}
    
    			$s_hotkeys[] = $s_distval;
    		}
		}

		$this->view->hotkeysjs = json_encode($hotkeys);

		// do not use user filters ISPC-
		if($modules->checkModulePrivileges("153", $clientid))
		{
			$filterkeysjs = array();
		}
		else
		{		
			// get user Filter - ISPC-1272 =150401
			$m_user_filters = new UserCourseFilters();
			$user_filter = $m_user_filters->get_user_filter(); 
		}
		if(!empty($user_filter)){
			foreach($user_filter as $k =>$uf){
				$filterkeysjs[] = $uf['shortcut'];
			}
			foreach($filterkeysjs as $kint=> $s_value){
				if(!in_array($s_value,$hotkeys) && $s_value !="wrong"){
					unset($filterkeysjs[$kint]);
				}
			}
		} else{
			$filterkeysjs = array();
		}
		
		$filterkeysjs = array_values($filterkeysjs);
		
		if($_REQUEST['show_filter'] == 1 ){
			
			print_r($filterkeysjs);
			//exit;
		}
	 
		
		$this->view->filterkeysjs = json_encode($filterkeysjs);
		
		$this->view->user_filter_patient_course = $filterkeysjs;

		$newarr = Pms_DataTable::sortArray($finalarr, 'ctype', SORT_ASC);
		$newarr_share = Pms_DataTable::sortArray($finalarr_shared, 'ctype', SORT_ASC);

		$this->view->checkcounter = count($newarr);

		$this->view->course_checks_arr = $newarr;

		$this->view->hasSharedShortcuts = count($newarr_share);

		$period = array();
		$pcourse = new PatientCourse();
		$user_details = $users->getUserDetails($logininfo->userid);

		$sort_directions = array('t'=>'DESC','b'=>'ASC');
		$sort_fields = array('a'=>'course_date', 'd'=>'done_date');

		//set defaults
		if(empty($user_details[0]['verlauf_newest'])) { $user_details[0]['verlauf_newest'] = 'b'; }
		if(empty($user_details[0]['verlauf_action'])) { $user_details[0]['verlauf_action'] = 'a'; }
		if(empty($user_details[0]['verlauf_fload'])) { $user_details[0]['verlauf_fload'] = 'n'; }

		$this->view->verlauf_sort_type = $user_details[0]['verlauf_newest'];
		$this->view->verlauf_facebook_load = $user_details[0]['verlauf_fload'];
		$this->view->verlauf_action = $user_details[0]['verlauf_action'];


		/*
		 * ISPC-2071
		 * 
		 * STEP 1 of 2
		 *  
		 * get cf's that have parents = are edited, to exclude them from our search ($exclude_done_ids)
		 * 
		 */
		$filter_contactforms_excluded = array();
		if ($modules->checkModulePrivileges("173", $clientid)) {
		    $filter_contactforms_excluded = $this->_patientcourse_contactform_step1($ipid);
		}

		
		/*
		 * count and get all verlauf data
		 */
		//first count
		
		if ($this->getParam('action') == 'patientcourse') {
		    $cnt_verlauf_entries = $pcourse->getCourseData($decid, 0, 0, false, false, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']], 0, false, false, true, $first_limit = false, $filter_contactforms_excluded);
		}
				
		if ($user_details[0]['verlauf_newest'] == 't' && $user_details[0]['verlauf_fload'] == 'y' 
		    && $_COOKIE['mobile_ver'] != 'yes'
		    && $this->getParam('action') == 'patientcourse') //double check
		{
			$first_limit = '50';

			if (!empty($_REQUEST['page']))
			{
			    
			    $limit = (int)$user_details[0]['verlauf_entries'];
// 				$limit = ! empty($limit) ?  $limit : $first_limit;// default to 50 is user has set 0

				$page = (int)$_REQUEST['page'];
				
// 				//verlauf bug, reload same data if set to "0" instead of rest data
// 				if ($limit == '0')
// 				{
// 					$limit = $cnt_verlauf_entries[0]['count'] - $first_limit;

// 					if ($limit < '0')
// 					{
// 						$limit = true;
// 					}
// 				}
				
				
				$offset = (((int) $limit * ($page - 1)) + $first_limit) - $session_PatientCourse_Fetched_Contactforms->recalculated_offset_diff[$ipid];
				
				if ($offset < 0) {
				    $offset =0;
				} 
			}
			else
			{
				$limit = $first_limit;
				$page = '0';
				$offset = ($limit * $page);
			}
			
			/*
			 * i've excluded previous fetched ids (session exclude_ids), so we only limit, no need for offset
			 */
			if ($modules->checkModulePrivileges("173", $clientid)) {
			    $offset = 0;
			}
			
			$this->view->max_pages = ceil(($cnt_verlauf_entries[0]['count'] - $first_limit) / $limit);

			$allblocks = $pcourse->getCourseData($decid, 0, 0, false, false, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']], $offset, $limit, $page, false, $first_limit, $filter_contactforms_excluded);
		}
		//load paginated data used only in mobile version(overwrites the facebook load)
		else if ($_COOKIE['mobile_ver'] == 'yes'
		         && $this->getParam('action') == 'patientcourse')
		{
			$limit = "250";
			
			if (!empty($_REQUEST['pageno']))
			{
				$page = $_REQUEST['pageno'];
			}
			else
			{
				$page = '0';
			}
			$offset = ($limit * $page);
			
			$allblocks = $pcourse->getCourseData($decid, 0, 0, false, false, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']], $offset, $limit, $page, false, false, $filter_contactforms_excluded);
			
			$this->view->{"style".$page} = "active";
			$grid = new Pms_Grid($allblocks, 1, $cnt_verlauf_entries[0]['count'], "dtainvoiceslist.html");
//			$this->view->templates_grid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("patientcoursenavigation.html", 5, $page, $limit);
		}
		else
		{
		    $start_date = $this->getParam('start_date', null);
		    $start_date = ! empty($start_date) ? $start_date : false;
		    
		    $end_date = $this->getParam('end_date', null);
		    $end_date = ! empty($end_date) ? $end_date : false;
		    
		    $shortcuts = $this->getParam('shortcuts', null);
		    $shortcuts = ! empty($shortcuts) ? "'" . implode("', '" , $shortcuts) . "'" : 0;
		    
			$allblocks = $pcourse->getCourseData($decid, 0, $shortcuts, $start_date, $end_date, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']], $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false, $filter_contactforms_excluded);
			
		}
		//print_r($allblocks); exit;
		
		//ISPC-2902 Lore 28.04.2021
		$pco = new PatientCourseOptions();
		$pco_array = $pco->getCourseDataOptions($ipid);
		$shortcut_options = Pms_CommonData::patient_course_options();
		$this->view->shortcut_options = $shortcut_options;
		if($module_companion_xt){
    		foreach($allblocks as $kbl => $vbl)
    		{
    		    foreach($vbl['summary'] as $ksum => $vsum)
    		    {
    		        if(array_key_exists($vsum['id'],$pco_array)){
    		            $allblocks[$kbl]['summary'][$ksum]['course_title'] = $shortcut_options[$vsum['course_type']][$pco_array[$vsum['id']]['option_id']]. ' | '. $vsum['course_title'] ;
    		        }
    		    }
    		}
		}
		//.		    
		
		
		//ISPC-2691 Carmen 04.11.2020
		$allblocksnew = array();
		foreach($allblocks as $kbl => $vbl)
		{
			foreach($vbl['summary'] as $ksum => $vsum)
			{	    
				if (strpos($vsum['tabname'], 'discharge') !== false || strpos($vsum['course_title'], 'Entlassungszeitpunkt') !== false)
				{
					$vblnew = $vbl;
					$vbl['main_tabname'] = 'discharge';					
					unset($vblnew['summary']);
					foreach($vbl['summary'] as $ksumi => $vsumi)
					{
						if (strpos($vsumi['tabname'], 'new_hospiz_register_v3') !== false)
						{
							$vblnew['summary'][$ksumi] = $vsumi;
							unset($vbl['summary'][$ksumi]);
						}
					}
					if(!empty($vblnew['summary']))
					{
						$newkey = $kbl."f";
						$allblocksnew[$newkey] = $vblnew;
					}
					break;
				}
				if (strpos($vsum['course_title'], 'Aufnahmezeitpunkt') !== false || strpos($vsum['tabname'], 'aufnahme') !== false)
				{
					$vblnew = $vbl;		
					$vblnew['main_tabname'] = 'aufnahme';
					unset($vblnew['summary']);
					$vblnew['summary'][$ksum] = $vsum;					
					unset($vbl['summary'][$ksum]);
					foreach($vbl['summary'] as $ksumi => $vsumi)
					{
						if (strpos($vsumi['course_title'], 'aktiviert') !== false || strpos($vsumi['course_title'], 'aufgenommen') !== false || strpos($vsumi['course_title'], 'Standby') !== false || strpos($vsum['tabname'], 'aufnahme') !== false || strpos($vsumi['course_title'], 'Aufnahmezeitpunkt') !== false)
						{
							$vblnew['summary'][$ksumi] = $vsumi;
							unset($vbl['summary'][$ksumi]);							
						}
					}
					if(!empty($vblnew['summary']))
					{
						$newkey = $kbl."a";
						$allblocksnew[$newkey] = $vblnew;
					}
					break;
				}
				if (strpos($vsum['course_title'], 'Patient wurde wieder aufgenommen') !== false || strpos($vsum['tabname'], 'aufnahme') !== false)
				{
					$vblnew = $vbl;
					$vblnew['main_tabname'] = 'aufnahme';
					unset($vblnew['summary']);
					$vblnew['summary'][$ksum] = $vsum;
					unset($vbl['summary'][$ksum]);
					foreach($vbl['summary'] as $ksumi => $vsumi)
					{
						if (strpos($vsumi['course_title'], 'Aufnahmezeitpunkt') !== false || strpos($vsumi['course_title'], 'aufhahme') !== false)
						{
							$vblnew['summary'][$ksumi] = $vsumi;
							unset($vbl['summary'][$ksumi]);
						}
					}
					if(!empty($vblnew['summary']))
					{
						$newkey = $kbl."a";
						$allblocksnew[$newkey] = $vblnew;
					}
					break;
				}
				if (strpos($vsum['course_title'], 'Patient was moved from discharge') !== false || strpos($vsum['tabname'], 'aufnahme') !== false)
				{
					$vblnew = $vbl;
					$vblnew['main_tabname'] = 'aufnahme';
					unset($vblnew['summary']);
					$vblnew['summary'][$ksum] = $vsum;
					unset($vbl['summary'][$ksum]);
					foreach($vbl['summary'] as $ksumi => $vsumi)
					{
						if (strpos($vsumi['course_title'], 'Aufnahmezeitpunkt') !== false || strpos($vsumi['course_title'], 'aufhahme') !== false)
						{
							$vblnew['summary'][$ksumi] = $vsumi;
							unset($vbl['summary'][$ksumi]);
						}
					}
					if(!empty($vblnew['summary']))
					{
						$newkey = $kbl."a";
						$allblocksnew[$newkey] = $vblnew;	
					}
					break;
				}
				if ((strpos($vsum['course_title'], 'Patient wurde zum') !== false && strpos($vsum['course_title'], 'aktiviert') !== false) || strpos($vsum['tabname'], 'aufnahme') !== false)
				{
					$vblnew = $vbl;
					$vblnew['main_tabname'] = 'aufnahme';
					unset($vblnew['summary']);
					$vblnew['summary'][$ksum] = $vsum;
					unset($vbl['summary'][$ksum]);
					foreach($vbl['summary'] as $ksumi => $vsumi)
					{
						if (strpos($vsumi['course_title'], 'Aufnahmezeitpunkt') !== false || strpos($vsumi['course_title'], 'aufhahme') !== false)
						{
							$vblnew['summary'][$ksumi] = $vsumi;
							unset($vbl['summary'][$ksumi]);
						}
					}
					if(!empty($vblnew['summary']))
					{
						$newkey = $kbl."a";
						$allblocksnew[$newkey] = $vblnew;
					}
					break;
				}
				if (strpos($vsum['course_title'], 'Patient wurde in den Status') !== false || strpos($vsum['course_title'], 'Patient wurde in Standby') !== false || strpos($vsum['tabname'], 'aufnahme') !== false)
				{
					$vblnew = $vbl;
					$vblnew['main_tabname'] = 'aufnahme';
					unset($vblnew['summary']);
					$vblnew['summary'][$ksum] = $vsum;
					unset($vbl['summary'][$ksum]);
					foreach($vbl['summary'] as $ksumi => $vsumi)
					{
						if (strpos($vsumi['course_title'], 'Aufnahmezeitpunkt') !== false || strpos($vsumi['course_title'], 'aufhahme') !== false)
						{
							$vblnew['summary'][$ksumi] = $vsumi;
							unset($vbl['summary'][$ksumi]);
						}
					}
					if(!empty($vblnew['summary']))
					{
						$newkey = $kbl."a";
						$allblocksnew[$newkey] = $vblnew;
					}
					break;
				}
			}
			if(!empty($vbl['summary']))
			{
				$allblocksnew[$kbl] = $vbl;
			}
		}
		$allblocks = $allblocksnew;
		//dd($allblocks);
		//--
		//print_r($allblocks); exit;
		/*
		 * ISPC-2071
		 *
		 * STEP 2 of 2
		 */
		if ($modules->checkModulePrivileges("173", $clientid)) {
		    $this->_patientcourse_contactform_step2($allblocks, $pcourse, $ipid, $decid, $user_details, $sort_directions, $sort_fields);
		}
		
		
		
		
		/*
		 * @cla - i've removed the next IF
		 * optimized what?
		 * what are u doying with $masterdata ??
		 */
		/*
		 if($_REQUEST['mod'] != 'minimal')
		 {
		 //optimized!
		 $pm = new PatientMaster();
		 $masterdata = $pm->getMasterData(0, 0, 0, $ipid);
		 	
		 }
		 */
		
		

        if ( ! is_array($allusers)) {
            $allusers = [];//fail-safe
        }

		//getting all user ids
		foreach ($allblocks as $block)
		{
// 		    $allusers[] = $block['user'];
		    //TODO-3267 Ancuta 07.07.2020 :: added change user to user array
		    //$allusers = array_merge($allusers, [$block['user']], array_column($block['summary'], 'user_id'));
		    $allusers = array_merge($allusers, [$block['user']], array_column($block['summary'], 'user_id'), array_column($block['summary'], 'change_user'));
		    //-- 
		}
		
		$allusers = array_values(array_unique($allusers));
		// dd($allusers);
		//$allusers[] = '999999'; //prevent fehler on empty array //@claudiu prevent fehler on empty array by condition
		
		//getting all user details
		$allusers_details = User::getMultipleUserDetails($allusers,$deleted_diplicated  = true);
		
		User::beautifyName($allusers_details);
		
		$this->view->allusers_details = $allusers_details;
		//adding user details to each block
		foreach ($allblocks as $key => $block)
		{
		    $allblocks[$key]['user_tname'] = $allusers_details[$block['user']]['user_title'];
		    $allblocks[$key]['user_fname'] = $allusers_details[$block['user']]['first_name'];
		    $allblocks[$key]['user_lname'] = $allusers_details[$block['user']]['last_name'];
		    	
		    	
		    /*
		     * ISPC-2071
		     * add cf [creator's name and date ]
		    */
		    if ($modules->checkModulePrivileges("173", $clientid)) {
		        
		        
		        $session_PatientCourse_Fetched_Contactforms->allready_fetched_ids[$ipid] = array_merge($session_PatientCourse_Fetched_Contactforms->allready_fetched_ids[$ipid], array_column($block['summary'], 'id' ));
		        
    		    foreach($block['summary'] as $key_sumary=>$summary) {
    		        	
    		        if ($summary['done_id'] > 0
    		            && $summary['done_name'] == ContactForms::PatientCourse_DONE_NAME
    		            && isset($summary['__is_bottom_cf']) && $summary['__is_bottom_cf'])
    		        {
    		            //TODO-2252 15.04.2019  Ancuta- change from create date to course date 
    		            $allblocks[$key]['summary'][$key_sumary]['__course_title_bottom_cf'] = " am " . date('d.m.Y H:i', strtotime($allblocks[$key]['summary'][$key_sumary]['course_date']));//$allblocks[$key]['summary'][$key_sumary]['__course_title_bottom_cf'] = " am " . date('d.m.Y H:i', strtotime($allblocks[$key]['summary'][$key_sumary]['create_date']));
    		            //--
    		            
    		            //TODO-2378 Ancuta 26.06.2019 - apply differently for the one shared
    		            if( ! empty($summary['source_ipid'])) {
        		            $allblocks[$key]['summary'][$key_sumary]['__course_title_bottom_cf'] .= " von " . $allusers_details[ $summary['user_id']] ['nice_name'];
    		            } else {
        		            $allblocks[$key]['summary'][$key_sumary]['__course_title_bottom_cf'] .= " von " . $allusers_details[ $summary['create_user']] ['nice_name'];
    		            }
						//TODO-3843 Ancuta 12.02.2021
    		            $allblocks[$key]['summary'][$key_sumary]['__course_title_recorddata'] = $summary['recorddata'];
    		        }
    		    }
		    }
		    	
		}
		
		
// 		cf_client_symptoms -ok allready has done_name
// 		contact_form
// 		block_todos - ok to update
// 		lmu_pmba_psysoz -ok
// 		patient_drugplan -. NOT ok
// 		contact_form_save
		
		$this->view->course_data = $allblocks;
		
		//ISPC - 2368
		$pattodo =  new ToDos();
		$pattodos_arr = $pattodo->getTodosByClientIdAndIpid($clientid, $ipid);
		$patcompltodocourseids = array();
		$patcompltodosdata = array();
		foreach($pattodos_arr as $kr=>$vr)
		{
			if($vr['course_id'] && $vr['iscompleted'] == '1')
			{
				$patcompltodocourseids[] = $vr['course_id'];
				$patcompltodosdata[$vr['course_id']] = $vr;
			}
		}
		$this->view->patcompltodocourseids = $patcompltodocourseids;
		$this->view->patcompltodosdata = $patcompltodosdata;
//print_r($pattodos_arr); exit;
		if ($postcnt > 0)
		{
			$this->view->callcheck2 = "check2()";
		}
		else
		{
			$this->view->callcheck2 = '""';
		}

		if($_REQUEST['mod'] != 'minimal')
		{
			$courseSession = new Zend_Session_Namespace('courseSession');

			if ($courseSession->patientId != $decid)
			{
				$courseSession->patientId = $decid;
				$courseSession->coursetype = array ();
			}

			if (is_array($_POST['course_type']) && $datainserted == 0)
			{
				foreach ($_POST['course_type'] as $key => $val)
				{
					$courses[$key]['course_type'] = $_POST['course_type'][$key];
					$courses[$key]['course_title'] = $_POST['course_title'][$key];
				}
			}
			else
			{
				if (!empty($courseSession->coursetype))
				{
					$courses = $courseSession->coursetype;
				}
				else
				{
					for ($i = 0; $i < 1; $i++)
					{
						$courses[$i]['course_type'] = "";
						$courses[$i]['course_title'] = "";
					}
				}
			}
			
			

			$cs = new Courseshortcuts();
			$shortcutarray = $cs->getCourseData();
			$this->view->countshct = count($shortcutarray);
			//remove HS Shortcut if module is not activated
			if(!$modulepriv_hs)
			{
				foreach($shortcutarray as $k_short => $v_short)
				{
					if($v_short['shortcut'] == 'HS')
					{
						unset($shortcutarray[$k_short]);
					}
				}
			}

			$this->view->coursecnt = count($courses);
			$grid1 = new Pms_Grid($courses, 1, count($courses), "listcourseSession.html");	
			
			
			$this->view->listcourseSessionArray = $courses;
			$this->view->shortcutsArray = $grid1->shortcutsArray = $shortcutarray;//array is used to create the selectbox
			
			$this->view->gridcoursetaks = $grid1->renderGrid();
				
			if (ISPC_WEBSITE_VIEW_VERSION == "mobile") {
			    $this->view->render('templates/placeholderPatientCourseAddInline.phtml');
			}
			
			
			$shortgrid = new Pms_Grid($shortcutarray, 1, count($shortcutarray), "CourseShortcuts.html");
			$this->view->cshortcuts = $shortgrid->renderGrid();

			
			/*		 * ******* Patient Information ************ */
			//$patientmaster = new PatientMaster();
			//$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);

			//$tm = new TabMenus();
			//$this->view->tabmenus = $tm->getMenuTabs();

			/*		 * ***************************************** */
		}

		//verlauf medications edit start
		$medic = new PatientDrugPlan();
		$drug_plans = $medic->getMedicationPlanCourse($decid);

		$patient_drugs['999999999'] = $this->view->translate('no_medications');
		foreach($drug_plans as $k_drugplan=>$v_drugplan)
		{
			if($v_drugplan['isbedarfs'] == '1')
			{
				$type='bedarfs_medi_select';
			}
			else if($v_drugplan['iscrisis'] == '1')
			{
				$type='crisis_medi_select';
			}
			else if($v_drugplan['isivmed'] == '1')
			{
				$type='iv_medi_select';
			}
			else
			{
				$type='normal_medi_select';
			}

			if($v_drugplan['medi_change'] != '0000-00-00 00:00:00')
			{
				$date = $v_drugplan['medi_change'];
			}
			else
			{
				$date = date('d.m.Y', strtotime($v_drugplan['create_date']));
			}

			if(!empty($v_drugplan['dosage']))
			{
				$dosage = ' - '.$v_drugplan['dosage'];
			}
			else
			{
				$dosage = '';
			}

			$medi_name = $date. ' - ' .$v_drugplan['medi_name'].$dosage;

			$patient_drugs['999999999'] = $this->view->translate('select_medication_edit');
			$patient_drugs[$type][$v_drugplan['id'].'-'.$v_drugplan['medication_master_id']] = $medi_name;

			$medi_name = '';
			$type = '';
			$dosage = '';
		}
		
		$this->view->patient_medications = $patient_drugs;
		//verlauf medications edit start


		//verlauf symptom add start
		$symperm = new SymptomatologyPermissions();
		$clientsymsets = $symperm->getClientSymptomatology($clientid);


		/*get Client symptomatology view options */
		$cl = new Client();
		$clarr = Pms_CommonData::getClientData($logininfo->clientid);
		$sympt_view_select = $clarr[0]['symptomatology_scale'];  // n-> Numbers Scale(0-10); a-> Attributes scale (none/weak/averge/strong)
		$this->view->sympt_view_select = $sympt_view_select;

		if($_REQUEST['scale'] == '1'){
			print_R($sympt_view_select); exit;
		}
		/*-------------------------------- */

		if($clientsymsets)
		{
		    foreach($clientsymsets as $k_cset =>$v_cset)
		    {
			    $setsids[] = $v_cset['setid'];
		    }
		    $patsymval =  new SymptomatologyValues();
		    $patsymvalarr = $patsymval->getSymptpomatologyValues($setsids);

		    $symptoms_data['999999999'] = $this->view->translate('selectsymptom');
		    foreach($patsymvalarr as $k_symval=>$v_symval)
		    {
			    $symptoms_data[$clientsymsets[$v_symval['set']]['set_name']][$v_symval['id']] = $v_symval['sym_description'];
		    }

		}
		else
		{
		    $sm = new SymptomatologyMaster();
		    $symarr = $sm->getSymptpomatology($clientid);

		    $symptoms_data['999999999'] = $this->view->translate('selectsymptom');
		    foreach($symarr as $k_symmaster=>$v_symmaster)
		    {
			$symptoms_data[$this->view->translate('clientsymmasterdata')][$v_symmaster['id']] = $v_symmaster['sym_description'];
		    }
		}
		$this->view->symptoms_data = $symptoms_data;
		//verlauf symptom add end
		/*-------------------------------- */
		// Patient files deleted
		
		$patientfile = Doctrine_Query::create()
		->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
				AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('isdeleted = "1"');
		  $filedelarray = $patientfile->fetchArray();
		  
		 //print_r($filedelarray) ;exit;
		 if($filedelarray)
		 {
		 	foreach($filedelarray as $k=>$val)
		 	{
		 		$delids_array[] = $val['id'];
		 	}
		 }
		$this->view->delids_array = $delids_array;
		//print_r($this->view->delids_array);exit;
	}
	
	/*
	 * ISPC-2071
	 */
	private function _patientcourse_contactform_step1($ipid = '')
	{
	    $filter_contactforms = array(); //exclude childrens, we will fetch them later
	    $PatientCourse_DONE_NAME = ContactForms::PatientCourse_DONE_NAME;
	    
	    $cf_entity = new ContactForms();
	    $cf_reedited = $cf_entity->fetchReEditedForms($ipid);
	    $cf_exclude = $cf_reedited;

	    //this are needed for ajaxloader
	    $session_PatientCourse_Fetched_Contactforms = new Zend_Session_Namespace('PatientCourse_Fetched_Contactforms');
	    if ( ! empty($session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid]) && is_array($session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid])) {
	        $cf_exclude =  ! empty ($cf_exclude) ? array_merge($cf_exclude, $session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid]) : $session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid];
	    }
	    
	    if ( ! empty($session_PatientCourse_Fetched_Contactforms->allready_fetched_ids[$ipid]) && is_array($session_PatientCourse_Fetched_Contactforms->allready_fetched_ids[$ipid])) {
	        $exclude_ids =  $session_PatientCourse_Fetched_Contactforms->allready_fetched_ids[$ipid];
	        $filter_contactforms['exclude_ids'] = $exclude_ids;
	    }
	    
	    if ( ! empty ($cf_exclude)) {
	        
	        $filter_contactforms_excludeIN = [];
	        array_walk($cf_exclude, function ($done_id) use (&$filter_contactforms_excludeIN, $PatientCourse_DONE_NAME) { $filter_contactforms_excludeIN[] = [$done_id, $PatientCourse_DONE_NAME];});
	        $filter_contactforms['excludeIN'] = $filter_contactforms_excludeIN;
	        
	        array_walk($cf_exclude, function (&$done_id) use ($PatientCourse_DONE_NAME) { $done_id .= " CONCAT_WS_SEPARATOR " . $PatientCourse_DONE_NAME;});
	    
	        $filter_contactforms['exclude'] = $cf_exclude;
	    }
	    return $filter_contactforms;
	}
	
	
	/*
	 * ISPC-2071
	 * !! attention by ref
	 * 
 	 * I bet you one case of beer, that you have not ever seen such density of foreaches ...
 	 * and another for the density of vars
	 */ 
	private function _patientcourse_contactform_step2( &$allblocks, PatientCourse $pcourse, $ipid, $decid, $user_details, $sort_directions, $sort_fields)
	{
	    $cf_entity = new ContactForms();
	    
	    $PatientCourse_DONE_NAME = $cf_entity::PatientCourse_DONE_NAME;
	    
	    $form_type_tabname_order = [];// this will be used to order $top_cf lines
	    
	    /*
	     * we hold in session recalculated_offset_diff & allready_fetched
	     */
	    $session_PatientCourse_Fetched_Contactforms = new Zend_Session_Namespace('PatientCourse_Fetched_Contactforms');
	    
	    
	    
// 	    dd($allblocks);
	    /*
	     * move all the cf's we have now, into the same block.. to be sure
	     *
	     */
	    $cf_first_positioning_row = array();
	    $cf_on_this_page = array();//extract all the cf's from this page, so we build a history log for them
	    
	    foreach ($allblocks as $key_row=>$row) {
	        foreach($row['summary'] as $key_sumary=>$summary) {
	             
	            if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME) {
	                 
	                if (isset($cf_first_positioning_row [$summary['done_id']]) && $cf_first_positioning_row [$summary['done_id']] != $key_row) {
	                    //copy there this sumary
	                    array_push($allblocks[$cf_first_positioning_row [$summary['done_id']]] ['summary'], $summary);
	                    //remove it from this row
	                    unset($allblocks [$key_row] ['summary'] [$key_sumary]);
	                } else {
	                    //this is the first row with this cf, remember so we can move all in here, and break 1;
	                    //this position will later be used to append the reEdited cf's that we will fetch in another query
	                    $cf_first_positioning_row [$summary['done_id']] = $key_row; //this done_is should-be the latest version of this cf...emphasis on should
	                    $cf_on_this_page[] = $summary['done_id'];
	                        break 1;
	                }
                }
            }
	             
            //!!Warning - after the moving, remove if empty row
            if (empty($allblocks[$key_row]['summary'])) {
                unset($allblocks [$key_row]);
            }
        }
        
        
        /*
         * if we have $cf_on_this_page , then we must search for $cf_reedited from this page, and append then to the current version of the contactform
         * we excluded all reEdited from the main search when we started
         */
        
        if ( ! empty($cf_on_this_page)) {
        
            //this are needed for ajaxloader
            
            $session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid] = isset($session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid]) && is_array($session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid]) ? array_merge($session_PatientCourse_Fetched_Contactforms->allready_fetched[$ipid], $cf_on_this_page) : $cf_on_this_page;
            
            
            //if you are using ajax to load parts, then this parent cf may be split, we must re-fetch him to have it all
            if($user_details[0]['verlauf_newest'] == 't' && $user_details[0]['verlauf_fload'] == 'y' && $_COOKIE['mobile_ver'] != 'yes') //double check
            {               
                $filter_contactforms_include = $cf_on_this_page;
                
                $filter_contactforms_includeIN = [];
                array_walk($filter_contactforms_include, function ($done_id) use (&$filter_contactforms_includeIN, $PatientCourse_DONE_NAME) { $filter_contactforms_includeIN[] = [$done_id, $PatientCourse_DONE_NAME];});
                
                array_walk($filter_contactforms_include, function (&$done_id) use ($PatientCourse_DONE_NAME) { $done_id .= " CONCAT_WS_SEPARATOR " . $PatientCourse_DONE_NAME;});
                
                $filter_contactforms_include = array(
                    'include' => $filter_contactforms_include, 
                    'includeIN' => $filter_contactforms_includeIN,
                );

                $final_allblocks_contactform_parent = [];
                $allblocks_contactform = $pcourse->getCourseData($decid, 0, 0, false, false, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']], $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false, $filter_contactforms_include);
                foreach ($allblocks_contactform as $view_pc_row) {
                    foreach($view_pc_row['summary'] as $summary) {
                        $final_allblocks_contactform_parent[$summary['done_id']][$summary['id']] = $summary; 
                    } 
                }   

                $recalculated_offset_diff = 0;
                
                foreach ($allblocks as $key_row=>$row) {
                    foreach($row['summary'] as $key_sumary=>$summary) {
                        if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME && isset($final_allblocks_contactform_parent[$summary['done_id']])) {
                            
//                             $recalculated_offset_diff += count($final_allblocks_contactform_parent[$summary['done_id']]) - count($allblocks[$key_row]['summary']); 
                            $recalculated_offset_diff += count($final_allblocks_contactform_parent[$summary['done_id']]); 
                
                            $allblocks[$key_row]['summary'] += $final_allblocks_contactform_parent[$summary['done_id']];

                            
                            break 1;
                        }
                    }
                }
                
                
                $session_PatientCourse_Fetched_Contactforms->recalculated_offset_diff[$ipid] += $recalculated_offset_diff;
                
                
                //now filter out the duplicates we just added.. cause they are allreay here
                //this must be moved at the end
                foreach ($allblocks as $key_row=>$row) {
                    $ids_pc_cf = [];
                    foreach($row['summary'] as $key_sumary=>$summary) {
                        if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME && isset($ids_pc_cf[$summary['id']])) {
                            unset($allblocks[$key_row]['summary'][$key_sumary]);                            
                        } else {
                            $ids_pc_cf[$summary['id']] = 1;
                        }
                    }
                }
                
            }
            
            
            
            //get cf's with parents/childrens
            $cf_traceHistory = $cf_entity->traceHistory($cf_on_this_page);
            
            $cf_history = $cf_traceHistory['_trace'];
            $cf_form_type = $cf_traceHistory['_form_type'];
            $cf_form_nicename = []; //we will add cf name on the first line

            if ( ! empty($cf_form_type)) {
                
                //fetch the order of the blocks inside the cf, it will be used to order the entry lines to match the cf
                $form_blocks_order = FormBlocksOrder::fetch_multiple_forms(array_unique($cf_form_type), $this->logininfo->clientid);

                $block_2_patientCourse_tabname = $cf_entity->block_2_patientCourse_tabname();
                
                foreach ($form_blocks_order as $formular_order) {
                    
                    $form_type_tabname_order[$formular_order['form_type']] = [];
                    
                    foreach ($formular_order['box_order']  as $box) {
                        if (isset($block_2_patientCourse_tabname[$box]) && ! empty($block_2_patientCourse_tabname[$box])) { 
                            $form_type_tabname_order[$formular_order['form_type']] = array_merge($form_type_tabname_order[$formular_order['form_type']], (is_array($block_2_patientCourse_tabname[$box]) ? $block_2_patientCourse_tabname[$box] : [$block_2_patientCourse_tabname[$box]]));
                        }
                    }
                    
                    $form_type_tabname_order[$formular_order['form_type']] = array_values(array_unique(array_filter($form_type_tabname_order[$formular_order['form_type']]))) ;
                }
                
                $paddedq = implode(", ", array_pad([], count($cf_form_type), "?"));
                
               
                $cf_form_nicename = Doctrine_Core::getTable('FormTypes')->findByDql("id IN ({$paddedq})", array_values($cf_form_type), Doctrine_Core::HYDRATE_ARRAY);
                $cf_form_nicename = array_column($cf_form_nicename, "name", "id");
            }
        
            $cf_on_this_page_childen2parent = array();//hold only ids of all the older reEdited cfs; fetch from patientCourse where done_id IN $cf_on_this_page_childen2parent
            $cf_on_this_page_childens = array();//hold the assoc ids of all the older reEdited cfs; after the fetch use this arr to append in our $allblocks
            foreach ($cf_history as $key_newest_cf => $cfs) {
                foreach ($cfs as $cf) {
                    if ($key_newest_cf != $cf) {
                        $cf_on_this_page_childens[$key_newest_cf][] = $cf;
                        $cf_on_this_page_childen2parent[$cf] = $key_newest_cf;
                    }
                }
            }
        
        
            
            if ( ! empty($cf_on_this_page_childen2parent)) {
                //we have children for the cf's we will display, include them too
        
                
                $cf_include = array_keys($cf_on_this_page_childen2parent);
                
                $filter_contactforms_includeIN = [];
                array_walk($cf_include, function ($done_id) use (&$filter_contactforms_includeIN, $PatientCourse_DONE_NAME) { $filter_contactforms_includeIN[] = [$done_id, $PatientCourse_DONE_NAME];});
                
                array_walk($cf_include, function (&$done_id) use ($PatientCourse_DONE_NAME) { $done_id .= " CONCAT_WS_SEPARATOR " . $PatientCourse_DONE_NAME;});
        
                $filter_contactforms_include = array(
                    'include' => $cf_include,
                    'includeIN' => $filter_contactforms_includeIN,
                );
        
                $allblocks_extra_contactform = $pcourse->getCourseData($decid, 0, 0, false, false, $sort_directions[$user_details[0]['verlauf_newest']], $sort_fields[$user_details[0]['verlauf_action']], $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false, $filter_contactforms_include);
                //     		    dd($cf_first_positioning_row);
        
                //     		    dd($filter_contactforms,$allblocks_extra_contactform);
                //proceed to moving
                foreach ($allblocks_extra_contactform as $key_row=>$row) {
        
                    foreach($row['summary'] as $key_sumary=>$summary) {
                         
                        if ( $summary['done_id'] > 0 && $summary['done_name'] == ContactForms::PatientCourse_DONE_NAME) {
        
                            $cf_parent_id = $cf_on_this_page_childen2parent[$summary['done_id']];
        
                            if (isset($cf_first_positioning_row [$cf_parent_id])) {
                                //copy there this sumary
                                array_push($allblocks [$cf_first_positioning_row [$cf_parent_id]] ['summary'], $summary);
        
                            } else {
                                //this is the first row with this cf ??? this should never happen
                                //record an error about this
                                
                                if (APPLICATION_ENV != 'production') {
                                    $this->_helper->log(__METHOD__ . " " .__LINE__ . " - 1 - BIG FUCKING ERROR IN THE LOGIC ! or error in your table! IPID:{$ipid}", 0);
                                }
        
                            }
                        }
                    }
                }
        
            }
        }
        
        
        
        /*
         * we now have the grouped contactform in a single row
         * leave in the cf's row only the last 'Contact form verlauf entry', and all the Contact form was edited by Daniel Zenz on 30.10.2016 20:15  + Contact Form PDF (30.10.2016 20:15)
         * and after that sort like that
         */
        $cf_tabname_bottom = array(
            '',
            'contact_form', //Besuch vom 08.08.2018 14:12 wurde editiert
            'contact_form_moved_date', // this was removed with ispc 2071
            'contact_form_save', //PDF des Kontaktformular - Kontaktformular Arzt in Dateien und Dokumente wurde hinterlegt
            'contact_form_first_date', //14:12 - 14:17  08.08.2018
            'contact_form_no_link', //Kontaktformular  hinzugefügt || Besuch vom 08.08.2018 14:12 wurde editier
            'contact_form_nosave_pdf_print',//Formular Kontaktformular - Praxisbesuch Arzt wurde erstellt
        );
        
        foreach ($allblocks as $key_row=>$row) {
        
            if (empty($row['__is_contactform'])) {
                continue;
            }
            
            $top_cf       = array();
            $bottom_cf    = array();
            $footer_cf    = array();
             
            usort($row['summary'], array(new Pms_Sorter('done_id'), "_number_desc")); // sort desc , so we can add only one
            
            foreach($row['summary'] as $key_sumary=>$summary) {
        
        
                if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME) {
                    
                    
                    if (isset($cf_history[$summary['done_id']])) {
                        //this is the latest edit of this form
                        $row['__latest_cf_edited'] = $summary['done_id'];
                    }
                    
                    //         	        dd($row['summary']);
                    if ($summary['is_removed'] == 'yes') {
                        
                        //$allblocks [$key_row] ['summary'] [$key_sumary] ['course_title'] .= " !!! REMOVED - WHAT TO DO ??? ";
                        
                    }
                    elseif (in_array($summary['tabname'], $cf_tabname_bottom)) {
                        //this must be at the bottom
                        $summary["__is_bottom_cf"] = 1;
                        
                        array_push($bottom_cf, $summary);
                        
                    } else {
                        //this must be the top
                        
                        $summary["__is_top_cf"] = 1;
                        //ISPC-2470 Ancuta - Multiple blocks for vital signs(tabname FormBlockVitalSigns) are now allowed
                        //TODO-2840 Ancuta - 23.01.2020 - Add module condition  so  vital signes is allowed multiple times ONLY if module activated  
                        $modules = new Modules();
                        $multiple_allowed = array('patient_drugplan', 'patient_drugplan_deleted', 'block_todos', 'patient_xbdt_actions');
                        if ($modules->checkModulePrivileges("182", $this->logininfo->clientid)) {
                            $multiple_allowed[] = 'FormBlockVitalSigns';
                        } 
                        //if (in_array($summary['tabname'], ['patient_drugplan', 'patient_drugplan_deleted', 'block_todos', 'patient_xbdt_actions','FormBlockVitalSigns'])) {
                        //-- END //TODO-2840
                        
                        if (in_array($summary['tabname'], $multiple_allowed)) {
                            /*
                             * ATTENTION !! 
                             * this tabnames have multiple entryes.. so we add ALL of them
                             * 'patient_drugplan', 'patient_drugplan_deleted', 'block_todos', 'patient_xbdt_actions'
                             * If client has module 182 - FormBlockVitalSigns also has multiple entries 
                             */
                            $allusers[] = $summary['create_user'];
                            $top_cf[ $summary['tabname']. '_'.$summary['id'] ] =  $summary;
                            
                        } elseif ( ! isset( $top_cf[  $summary['tabname']. '_'.$summary['course_type']  ] )) {
                            //add only one
                            $allusers[] = $summary['create_user'];
                            $top_cf[ $summary['tabname']. '_'.$summary['course_type'] ] =  $summary;
                        }
                        
                    }
                    
                    
                } else {
                    //this is not a cf-related entry, keep track of it maybe we need it later if this is a cf-row
                    //keep-it and add it in the $footer_cf
                    $footer_cf [] = $summary;
                }
            }
        
        
            if ( ! empty($top_cf) || ! empty($bottom_cf)) {
                
                if ( ! empty($footer_cf)) {
                    
                    //TODO remove this for production
                    if (APPLICATION_ENV != 'production') {
                        $this->_helper->log(__METHOD__ . " " .__LINE__ . " - 2 - BIG FUCKING ERROR IN THE LOGIC OR ERROR WHEN WE UPDATED via /misc/ ! or error in your table! IPID:{$ipid}", 0);
                    }
                }

                $_latest_done_date = null;
                
                $second_line = []; //this will hold  'contact_form_change_date' or contact_form_first_date .. it is taken from $top_cf
                
                foreach ($top_cf as $ktop => $top_cf_row) {
                    
                    if ( $top_cf_row['tabname'] == "contact_form_change_date"
                        && $top_cf_row['done_name'] == 'contact_form'
                        && $top_cf_row['done_id'] > 0)
                    {
                        $top_cf[$ktop]['__form_type_name'] = $cf_form_nicename[$cf_form_type[$top_cf[$ktop]['done_id']]] ;
                        $top_cf[$ktop]['course_title'] = $cf_form_nicename[$cf_form_type[$top_cf[$ktop]['done_id']]] . ": " . $top_cf[$ktop]['course_title'];
                        $second_line = [$top_cf[$ktop]];
                                                
                        $_latest_done_date      = $top_cf_row['done_date'];
                        
                        unset($top_cf[$ktop]);
                    
                    }
                }
                
                //sort $top_cf by blocks order in this cf
                if (count($top_cf) > 1 && isset($form_type_tabname_order[$cf_form_type[$row['__latest_cf_edited']]])) {
                    
                    
                    $top_cf_tabnames = array_column($top_cf, 'tabname');
                    
                    $search_order  = $form_type_tabname_order[$cf_form_type[$row['__latest_cf_edited']]];
                    
                    usort($top_cf, array(new Pms_Sorter('tabname', $search_order), "_customorder"));
                    
                }
                
                
                //sort $bottom_cf
                
                $bottom_cf_pdf = array_filter($bottom_cf, function($row){
                    return ($row['tabname'] == "contact_form_save"  && $row['done_name'] == 'contact_form' && $row['recordid'] > 0);
                });
                
                $first_line = []; //this will hold the click-me to re-edit .. it is taken from $bottom_cf
               
                
                foreach ($bottom_cf as $kbottom => $bottom_cf_row) {
                    
                    //this tabname will be only on contactforms before this ispc-2071 .. i leave like this cause they are ordered (add etxra if's.. cause contact_form_first_date could be before)
                    if ( $bottom_cf_row['tabname'] == "contact_form_moved_date" 
                        && $bottom_cf_row['done_name'] == 'contact_form' 
                        && $bottom_cf_row['done_id'] > 0) 
                    { 
                        if (empty($second_line)) {
                            $bottom_cf[$kbottom]['__form_type_name'] = $cf_form_nicename[$cf_form_type[$bottom_cf[$kbottom]['done_id']]] ;
                            $bottom_cf[$kbottom]['course_title'] = $cf_form_nicename[$cf_form_type[$bottom_cf[$kbottom]['done_id']]] . ": " . $bottom_cf[$kbottom]['course_title'];
                            $second_line = [$bottom_cf[$kbottom]];
                        }
                           
                        unset($bottom_cf[$kbottom]);
                        
                    } 
                    
                    elseif ($bottom_cf_row['tabname'] == "contact_form_first_date" 
                        && $bottom_cf_row['done_name'] == 'contact_form'
                        && $bottom_cf_row['done_id'] > 0)
                    {
                        if (empty($second_line)) {
                            $bottom_cf[$kbottom]['__form_type_name'] = $cf_form_nicename[$cf_form_type[$bottom_cf[$kbottom]['done_id']]] ;
                            $bottom_cf[$kbottom]['course_title'] = $cf_form_nicename[$cf_form_type[$bottom_cf[$kbottom]['done_id']]] . ": " . $bottom_cf[$kbottom]['course_title'];
                            $second_line = [$bottom_cf[$kbottom]];
                        }
                        unset($bottom_cf[$kbottom]);
                    
                    }
                    
                    elseif ($bottom_cf_row['tabname'] == "contact_form_save" 
                        && $bottom_cf_row['done_name'] == 'contact_form' 
                        && $bottom_cf_row['recordid'] > 0) 
                    {
                        unset($bottom_cf[$kbottom]);
                        
                    } 
                    
                    elseif (($bottom_cf_row['tabname'] == "contact_form" || $bottom_cf_row['tabname'] == "contact_form_no_link") 
                        && $bottom_cf_row['done_name'] == 'contact_form' 
                        && $bottom_cf_row['done_id'] > 0) 
                    {
                        
                        //this is added later, cause we fetch all users at once
                        //$bottom_cf[$kbottom]['course_title'] .= " am  dateXXX von BenutzerXXX";
                        
                        $bottom_cf[$kbottom]['__pdf'] = array_filter($bottom_cf_pdf, function($row) use ($bottom_cf_row) {
                            return ($row['done_id'] == $bottom_cf_row['done_id']);
                        });
                        
                        
                        if ($bottom_cf_row['tabname'] == "contact_form" ) {
                            //this must be allways 1st line on thi row... is clickable to re-edit this form
//                             $bottom_cf[$kbottom]['course_title'] .= $cf_form_nicename[$cf_form_type[$bottom_cf[$kbottom]['done_id']]] . $bottom_cf[$kbottom]['course_title'];
                            $bottom_cf[$kbottom]['__form_type_name'] = $cf_form_nicename[$cf_form_type[$bottom_cf[$kbottom]['done_id']]] ;
                            
                            if (empty($_latest_done_date)) {
                                //TODO-2346 - 12.06.2019 Changed by Ancuta
                                //$_latest_done_date = $top_cf_row['done_date'];
                                $_latest_done_date = $bottom_cf_row['done_date'];
                            } else {
                                $bottom_cf[$kbottom]['course_title'] = "Besuch vom " . date ('d.m.Y H:i',strtotime($_latest_done_date)). " wurde editiert";
                            }
                            
                            $first_line = [$bottom_cf[$kbottom]];
                            
                            unset($bottom_cf[$kbottom]);

                            
                        }
                    }
                }
                
                
                
//                 if ($modules->checkModulePrivileges("173", $clientid)) {
//                     foreach($block['summary'] as $key_sumary=>$summary) {
                         
//                         if ($summary['done_id'] > 0
//                             && $summary['done_name'] == ContactForms::PatientCourse_DONE_NAME
//                             && isset($summary['__bottom_cf']))
//                         {
//                             $allblocks[$key]['summary'][$key_sumary]['__course_title_bottom_cf'] = $allusers_details[ $summary['create_user']] ['nice_name'];
//                             $allblocks[$key]['summary'][$key_sumary]['__course_title_bottom_cf'] .= " am " . date('d.m.Y H:i', strtotime($allblocks[$key]['summary'][$key_sumary]['create_date']));
                             
//                         }
//                     }
//                 }
                
                usort($bottom_cf, function($a, $b) {return $b['id'] > $a['id']; });
                
//                 dd($bottom_cf);
                
                
                /*
                 * you can switch $first_line with $second_line, cause they look better
                 */
                $allblocks [$key_row] ['summary'] = array_merge($first_line, $second_line, $top_cf, $bottom_cf, $footer_cf);
                                
                $allblocks [$key_row] ['__latest_done_date']    = $_latest_done_date;
            }
        
        }
        
//         dd($allblocks);

        /*
        $cf_on_this_page = array();
        $cf2move = array(); // this must be moved into another parent
        
        //extRacat all the cf's from this page, so we build a history log for them
        foreach ($allblocks as &$row) {
            foreach($row['summary'] as &$summary) {
                if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME) {
                    $cf_on_this_page[$summary['done_id']] = $summary['done_id'];
                }
            }
        }
        
        
        
        //get cf's with parents .. i think i allready have them...
        $cf_entity = new ContactForms();
        $cf_history = $cf_entity->traceHistory($cf_on_this_page);
        
        $cf_on_this_page_parent = array();//hold the id of the latest edit of this form <=> contactform->parent only for this page
        $cf_history_parent = array(); //hold all the id of the latest edit of this form <=> contactform->parent
        foreach ($cf_history as $key => $cfs) {
            foreach ($cfs as $cf) {
                if (isset($cf_on_this_page[$cf])) {
                    $cf_on_this_page_parent[$cf] = $key;
                }
                $cf_history_parent[$cf] = $key;
            }
        }
        
        //filter children out
        foreach ($allblocks as &$row) {
            foreach($row['summary'] as $key => &$summary) {
                if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME) {
        
                    if ( ! isset($cf_history[$summary['done_id']])) {
                        //this is a course form a formular that was edited, and must be moved into his parent
                    $cf2move [ $cf_history_parent[$summary['done_id']] ] [$summary['id']] = $summary;
            
                        unset($row['summary'][$key]);
            
                        continue;
                    }
        
                    $cf_on_this_page[$summary['done_id']] = $summary['done_id'];
                }
            }
        }
        
        //add children 2 parent
        foreach ($allblocks as &$row) {
            foreach($row['summary'] as $key => &$summary) {
                if ( $summary['done_id'] > 0 && $summary['done_name'] == $PatientCourse_DONE_NAME && ! empty($cf2move[$summary['done_id']])) {
                 
                    krsort($cf2move[$summary['done_id']]);
                    $row['summary'] +=  $cf2move[$summary['done_id']];
                    break 1;
                }
            }
        }
        
        */
        

        /*
        *  !! Warning, here I unset empty row
        */
        foreach ($allblocks as $k => $row) {
            if (empty($row['summary'])) {
                unset($allblocks[$k]);
            }
            
            /*
             * fix the done_date TODO-1896
             */
            if (isset($row['__is_contactform']) && $row['__is_contactform'] == '1' 
                && ! empty ($row['__latest_done_date'])
                && $sort_fields[$user_details[0]['verlauf_action']] == 'done_date' ) 
            {
                $allblocks [$k] ['date'] = date('Y-m-d H:i', strtotime($row['__latest_done_date']));
                $allblocks [$k] ['date_dt'] = date('d.m.Y', strtotime($row['__latest_done_date']));
                $allblocks [$k] ['date_hm'] = date('H:i', strtotime($row['__latest_done_date']));
            }
            
            /*
             * ISPC-2363
             * KEEP the initial contact form creator .. 
             * to be displayed in the left, and not the __latest_editor
             * ! this fn can be changed to use the cd's array that is groupped by id
             * APPLY ONLY for OWN contact forms  - TODO-2378 Ancuta 26.06.2019 - check if no source_ipid
             */
            if (isset($row['__is_contactform']) && $row['__is_contactform'] == '1' && empty($row['source_ipid'])){
                
                $midCfId = min(array_map(function ($i){return $i['done_id'];}, $row['summary'])); // this should be the first form
                $formularCreatedRows = array_filter($row['summary'], function($i) use($midCfId) {return $i['done_id'] == $midCfId; });
                $formularCreatedRow = reset($formularCreatedRows);
                $allblocks [$k] ['__latest_editor'] = $allblocks [$k] ['user'];
                $allblocks [$k] ['user'] = $formularCreatedRow['create_user'];
            }
        }
        
        /*
         * fix the done_date TODO-1896
         */
        usort($allblocks, array(new Pms_Sorter('date'), "_date_compare"));
        
        if ($sort_directions[$user_details[0]['verlauf_newest']] == "DESC") {
            $allblocks = array_reverse($allblocks);
        }
	}
	
	
	
	
	
	
	
	
	public function savewrongcaveAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$logininfo = new Zend_Session_Namespace('Login_Info');
	
		$pc = new Application_Form_PatientCourse();

		
		$ids = $_REQUEST['ids'];
		$comment = $_REQUEST['comment'];
		$val = $_REQUEST['val'];
	
		
		
		$a_post['ids'] = $ids;
		$a_post['comment'] = $comment;
		$a_post['val'] = $val;
	
		$tc = $pc->UpdateWrongEntry($a_post);
		

		/* user details */ 
		$user_c_details = User::getUserDetails($logininfo->userid);
		$username = $user_c_details[0]['first_name'].' '.$user_c_details[0]['last_name'];

		
		$comment = str_replace("%usernamefnln%",$username,$this->view->translate('comment for cave deletion'));

		if(!empty($_REQUEST['patient'])){
				
			$decid = Pms_Uuid::decrypt($_REQUEST['patient']);
			$ipid = Pms_CommonData::getIpid($decid);
		
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->user_id = $logininfo->userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->save();
		
		}
		
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBackWrong";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['id'] = $_REQUEST['blockcnt'];
		$response['callBackParameters']['val'] = $val;
		$response['callBackParameters']['comment'] = $comment;
	
		echo json_encode($response);
		exit;
	}
	
	
	
	

	private function generate_pdf($chk, $post, $pdfname, $filename)
	{
	    $clientid = $this->clientid;
	    $clientinfo = Pms_CommonData::getClientData($clientid);
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_CommonData::getIpid($decid);
	    $excluded_keys = array(
	        'patHeader',
	        'course_title',
	        'cshortcuts',
	        
	    );
	    $post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
	
	    $post['ipid'] = Pms_CommonData::getIpid($decid);
	    $userid = $this->userid;
	    $patientmaster = new PatientMaster();
	    $parr = $patientmaster->getMasterData($decid, 0);
	
	
	    $previleges = new Modules();
	    if($previleges->checkModulePrivileges("131", $clientid)){
	        $med_module = "1";
	    } else{
	        $med_module = "0";
	    }
	
	
	    $epid = Pms_CommonData::getEpidFromId($decid);
	    $this->view->epid = $epid;
	
	    $post['patientname'] = htmlspecialchars($parr['last_name']) . ", " . htmlspecialchars($parr['first_name']) . " \n" . htmlspecialchars($parr['street1']) . "\n" . htmlspecialchars($parr['zip']) . "&nbsp;" . htmlspecialchars($parr['city']);
	    $post['patientaddress'] = htmlspecialchars($parr['street1']) . " \n " . htmlspecialchars($parr['zip']) . " " . htmlspecialchars($parr['city']);
	    $post['pataddress'] = htmlspecialchars($parr['street1']) . ", " . htmlspecialchars($parr['zip']) . " " . htmlspecialchars($parr['city']);
	    $post['patname'] = htmlspecialchars($parr['last_name']) . ", " . htmlspecialchars($parr['first_name']);
	    $post['patbirth'] = $parr['birthd'];
	    $post['epid'] = $epid;
	
	    if($parr['sex'] == 1)
	    {
	        $this->view->male = "checked='checked'";
	    }
	    if($parr['sex'] == 2)
	    {
	        $this->view->female = "checked='checked'";
	    }
	    
	    if($parr['sex'] == 1)
	    {
	        $this->view->gender = $this->view->translate("male");
	    }
	    elseif($parr['sex'] == 2)
	    {
	        $this->view->gender = $this->view->translate("female");
	    }
	    elseif($parr['sex'] != null && $parr['sex'] == 0)  
	    {
	        $this->view->gender = $this->view->translate("divers");  //ISPC-2442 @Lore   30.09.2019
	    }
	    else           //if($parr['sex'] == null)  &&  == ""
	    {
	        $this->view->gender = $this->view->translate("gender_not_documented");
	    }
	    
	    
	    $dian = new Application_Form_Diagnosis();
	    $sortarr = $dian->getHDdiagnosis($parr['ipid']);
	    foreach($sortarr as $key => $diagnosis)
	    {
	        $maind .= ' ' . $diagnosis['description'] . ',';
	    }
	
	    $post['maindiagnosis'] = substr($maind, 0, -1);
	
	    $ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
	    $this->view->refarray = $ref['referred_name'];
	
	    $loguser = Doctrine::getTable('User')->find($this->userid);
	
	    if($loguser)
	    {
	        $loguserarray = $loguser->toArray();
	        $this->view->lastname = $loguserarray['last_name'];
	        $this->view->firstname = $loguserarray['first_name'];
	    }
	
	    $symp = new Symptomatology();
	    $symptomarr = $symp->getPatientSymptpomatologyLast($ipid);
	
	    if(empty($symptomarr))
	    {
	        $sympval = new SymptomatologyValues();
	        $set_details = $sympval->getSymptpomatologyValues(1); //HOPE set
	        foreach($set_details as $key => $sym)
	        {
	            $symptomarr[$key] = $sym;
	            $symptomarr[$key]['symptomid'] = $sym['id'];
	        }
	    }
	    else
	    {
	        foreach($symptomarr as $key => $sym)
	        {
	            $symptomarr[$key]['sym_desc_array'] = $sym['sym_description'];
	            $symptomarr[$key]['sym_description'] = utf8_encode($sym['sym_description']['value']);
	        }
	    }
	
	    $post['symptomarr'] = $symptomarr;
	
	    $clientdata = Pms_CommonData::getClientData($this->clientid);
	    $post['clientname'] = $clientdata[0]['clientname'];
	    $post['clientfax'] = $clientdata[0]['fax'];
	    $post['clientphone'] = $clientdata[0]['phone'];
	    $post['clientemail'] = $clientdata[0]['emailid'];
	    $post['clientcity'] = $clientdata[0]['city'];
	
	    $pmf = new PatientMoreInfo();
	    $pat_moreinfo = $pmf->getpatientMoreInfoData($ipid);
	
	    $post['dk'] = $pat_moreinfo[0]['dk'];
	    $post['peg'] = $pat_moreinfo[0]['peg'];
	    $post['port'] = $pat_moreinfo[0]['port'];
	    $post['pumps'] = $pat_moreinfo[0]['pumps'];
	
	    $post['sapsymp'] = Sapsymptom::get_patient_sapvsymptom(Pms_CommonData::getIpid($decid));
	
	    $patientmaster = new PatientMaster();
	    $patientinfo = $patientmaster->getMasterData($decid, 0);
	
	    $post['bdate'] = $patientinfo['birthd'];
	
	    if($patientinfo['isdischarged'] != 1)
	    {
	        $sav = new SapvVerordnung();
	        $post['savarry'] = $sav->getSapvVerordnungData($patientinfo['ipid']);
	    }
	
	    $phelathinsurance = new PatientHealthInsurance();
	    $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);
	
	    $post['insurance_company_name'] = $healthinsu_array[0]['company_name'];
	    $post['insurance_no'] = $healthinsu_array[0]['insurance_no'];
	    $post['insurance_status'] = $healthinsu_array[0]['insurance_status'];
	
	    $hquery = Doctrine_Query::create()
	    ->select('*')
	    ->from('HealthInsurance')
	    ->where("id='" . $healthinsu_array[0]['companyid'] . "' or name='" . htmlentities($healthinsu_array[0]['company_name'], ENT_QUOTES) . "'");
	    $harray = $hquery->fetchArray();
	    $post['kvnumber'] = $harray[0]['kvnumber'];
	
	    /* analage3 */
	    $patientmaster = new PatientMaster();
	    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
	
	    $tm = new TabMenus();
	    $this->view->tabmenus = $tm->getMenuTabs();
	
	    if(!empty($ipid)){
	       $imgtag = Doctrine::getTable('SapfiveImagetags')->findBy('ipid', $ipid);
    	    $post['tagarray'] = $imgtag->toArray();
	    }
	
	    $post['tablepatientinfo'] = Pms_Template::createTemplate($parr, 'templates/pdfprofile.html');
	
	    $post['tag'] = date("d");
	    $post['month'] = date("m");
	    $post['jahr'] = date("Y");
	
	    //get main diagnosis types
	    $abb = "'HD','ND'";
	    $dg = new DiagnosisType();
	    $darr = $dg->getDiagnosisTypes($clientid, $abb);
	
	    foreach($darr as $k_dt => $v_dt)
	    {
	        $dtypearray[$v_dt['abbrevation']] = $v_dt['id'];
	    }
	
	    foreach($post['dtype'] as $k_dtype => $v_dtype)
	    {
	        if(in_array($v_dtype, $dtypearray))
	        {
	            if(!empty($post['diagnosis'][$k_dtype]))
	            {
	                $current_diagnosis_type = array_search($v_dtype, $dtypearray);
	                $diagnosis_arr[$current_diagnosis_type][] = trim(rtrim($post['icdnumber'][$k_dtype] . ' ' . $post['diagnosis'][$k_dtype]));
	            }
	        }
	    }
	
	    $metas = array('');
	    foreach($post['meta_title'] as $k_meta => $v_meta)
	    {
	        $metas = array_merge($metas, $v_meta);
	    }
	
	    //get all metastases
	    $dm = new DiagnosisMeta();
	    $diagnosismeta = $dm->getDiagnosisMetaData(1);
	
	    foreach($metas as $k_metas => $v_metas)
	    {
	        if(!empty($v_metas))
	        {
	            $metastases[] = trim(rtrim($diagnosismeta[$v_metas]));
	        }
	    }
	
	    $post['main_diagnosis'] = implode(', ', $diagnosis_arr['HD']);
	    $post['metastases'] = implode(', ', $metastases);
	    $post['side_diagnosis'] = implode(', ', $diagnosis_arr['ND']);
	
	    // sapv questionnaire
	    $htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
	    /* print_r($htmlform);exit; */
	    if($chk == 1)
	    {
	        // $dlSession = new Zend_Session_Namespace('doctorLetterSession');
	        $tmpstmp = time();
// 	        mkdir("uploads/" . $tmpstmp);
	        mkdir(PDF_PATH. "/" . $tmpstmp);
// 	        $pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
	        $pdf->Output(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
	        $_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 	        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
// 	        exec($cmd);
	        $zipname = $tmpstmp . ".zip";
	        $filename = "uploads/" . $tmpstmp . ".zip";
			/*
	        $con_id = Pms_FtpFileupload::ftpconnect();
	        if($con_id)
	        {
	            $upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
	            Pms_FtpFileupload::ftpconclose($con_id);
	        }
	        */
	        $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ( PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
	         
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
	
	        $navnames = array(
	            "patient_course"=> "Verlauf"
	        );
	
	        //$pdf = new Pms_PDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
	       if($pdfname == 'patient_course')
	        {
	            $orientation = 'P';
	            $bottom_margin = '20';
	            $format = "A4";
	        }
	        else
	        {
	            $orientation = 'P';
	            $bottom_margin = '20';
	            $format = "A4";
	        }
	
	        $pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
	        $pdf->SetMargins(10, 5, 10); //reset margins
	        $pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
	        $pdf->setImageScale(1.6);
	        $pdf->format = $format;
	        $pdf->setPrintFooter(false); // remove black line at bottom
	
	        $font_size = $post['font_size'];
	        
	        switch($pdfname)
	        {
	            case 'patient_course':
// 	                $background_type = "63";
	                $pdf->SetMargins(10, 20, 10); //reset margins
	                $pdf->SetFont('', '', $font_size);
	                break;
	
	            default:
	                $background_type = false;
	                $pdf->SetMargins(10, 5, 10); //reset margins
	                break;
	        }
	
            $pdf->HeaderText = false;
	
	        if($background_type != false)
	        {
	            $bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
	            if($bg_image !== false)
	            {
	                $bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
	
	                if(is_file($bg_image_path))
	                {
	                    $pdf->setBackgroundImage($bg_image_path);
	                }
	            }
	        }
	
	        $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
	
	        $excluded_css_cleanup_pdfs = array(
	            'patient_course'
	        );
	        	
	        if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
	        {
	            $html = preg_replace('/style=\"(.*)\"/i', '', $html);
	        }
	        
	        if($_REQUEST['dbg'] == "show_html"){
	        	echo $html; 
	        	exit;
	        }
	        
	        $pdf->setHTML($html);
	        
	        
	        
	        $tmpstmp = $pdf->uniqfolder(PDF_PATH);
	
	        $file_name_real = basename($tmpstmp);
	
	        $pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
	
	        $_SESSION ['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 	        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
	
// 	        exec($cmd);
	        $zipname = $file_name_real . ".zip";
	
	        $filename = "uploads/" . $file_name_real . ".zip";
	
	        /*
	        $con_id = Pms_FtpFileupload::ftpconnect();
	
	        if($con_id)
	        {2213
	            $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
	            Pms_FtpFileupload::ftpconclose($con_id);
	        } */
	        $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , strtolower(__CLASS__), null, true );
	         
	
            ob_end_clean();
            ob_start();
            $pdf->toBrowser($pdfname . '.pdf', "d");
            exit;
	    }
	
	    //dont return the pdf file to user
	    if($chk == 4)
	    {
	        $navnames = array(
	            "patient_course" => 'Verlauf'
	             
	        );
	
	        $pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
	        $pdf->setDefaults(true);
	        $pdf->setImageScale(1.6);
	        $pdf->SetMargins(10, 5, 10);
	        $background_type = false;
	        $pdf->HeaderText = false;
	
	        $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
	        $html = preg_replace('/style=\"(.*)\"/i', '', $html);
	
	        $pdf->setHTML($html);
	
	        $tmpstmp = $pdf->uniqfolder(PDF_PATH);
	        $file_name_real = basename($tmpstmp);
	        $pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
	        $_SESSION ['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 	        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
// 	        exec($cmd);
	        $zipname = $file_name_real . ".zip";
	        $filename = "uploads/" . $file_name_real . ".zip";
	
	        /*
	        $con_id = Pms_FtpFileupload::ftpconnect();
	        if($con_id)
	        {
	            $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
	            Pms_FtpFileupload::ftpconclose($con_id);
	        }
	        */
	        $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' ,'uploads');
	        //is this if ever used?
	         
	       
	        $cust = new PatientFileUpload ();
	        $cust->title = Pms_CommonData::aesEncrypt(addslashes($navnames[$pdfname]));
	        $cust->ipid = $ipid;
	        $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']); //$post['fileinfo']['filename']['name'];
	        $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
	        $cust->recordid = $record_id;
	        $cust->tabname = $form_tabname;
	        $cust->system_generated = "1";
	        $cust->save();
	        $recordid = $cust->id;
	    }
	}
	
	
	/**
	 * this fn is used for display and for saved data...
	 * if pseudogroup has now keine benutzer assidned to it, it will not be displayed in the selectbox
	 * BUTTT it will also make a blank_space in the allready saved W
	 * @todo fix this
	 * Jul 17, 2017 @claudiu 
	 * 
	 * @return multitype:NULL multitype:string  multitype:Ambigous <string, NULL, string, Ambigous <string, Zend_View_Helper_Translate>>  multitype:Ambigous <>
	 */
	private function get_nice_name_multiselect ()
	{
			
		$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
			
		$todousersarr = array(
				"0" => $this->view->translate('select'),
				$selectbox_separator_string['all'] => $this->view->translate('all')
		);
			
		$usergroup = new Usergroup();
		$todogroups = $usergroup->getClientGroups($this->logininfo->clientid);
		$grouparraytodo = array();
		foreach ($todogroups as $group)
		{
			$grouparraytodo[$selectbox_separator_string['group'] .  $group['id']] = trim($group['groupname']);
		}
	
		if (isset( $this->{'_patientMasterData'}['User'])){
			$userarray = $this->{'_patientMasterData'}['User'];
		} else {
			$users = new User();
			$userarray = $users->getUserByClientid($this->logininfo->clientid);
		}
		
		
		User::beautifyName($userarray);
			
		$userarraytodo = array();
		foreach ($userarray as $user)
		{
		    if($user['isactive'] == "0"){
                $userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
            }
		}
	
		//asort($userarraytodo);//ISPC-2878,Elena,12.04.2021 //sorting by last name AND NOT by nice name with title wanted - elena
		asort($grouparraytodo);
			
		$todousersarr[$this->view->translate('group_name')] = $grouparraytodo;
		
	
		$user_pseudo =  new UserPseudoGroup();
		$user_ps =  $user_pseudo->get_pseudogroups_for_todo($this->logininfo->clientid);
		$pseudogrouparraytodo = array();
		if ( ! empty ($user_ps)) {
			
			//pseudogroup must have users in order to display 
			$user_ps_ids =  array_column($user_ps, 'id');
			$user_pseudo_users = new PseudoGroupUsers();
			$users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);				
			
			foreach($user_ps as $row) {
				if ( ! empty($users_in_pseudogroups[$row['id']]))
				$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
			}
				
			$todousersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
		}
		$todousersarr[$this->view->translate('users')] = $userarraytodo;
		return $todousersarr;
	}
	
	
}
?>