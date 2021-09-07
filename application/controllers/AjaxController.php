<?php

class AjaxController extends Zend_Controller_Action {

    protected $logininfo = null;//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
		
		public function init()
		{
		    $this->logininfo = new Zend_Session_Namespace('Login_Info');//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
		}
		public function deleteremedyAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			
			if($_REQUEST['remedy_id'] && $_REQUEST['id'])
			{
				
				$decid = Pms_Uuid::decrypt($_GET['id']);
				$ipid = Pms_CommonData::getIpId($decid);
				$remedy_id=($_REQUEST['remedy_id']);
			
					$q = Doctrine_Query::create()
					->update('PatientRemedies')
					->set('isdelete',"1")
					->where("id = ?", $remedy_id)
					->andWhere("ipid = '".$ipid."'");
					$q->execute();
			}
		}
		
		public function deleteworkdataAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			if($_REQUEST['vw_id'] && $_REQUEST['work_id'])
			{
				$work_id=($_REQUEST['work_id']);
				
				$q = Doctrine_Query::create()
				->update('VwWorkdata')
				->set('isdelete',"1")
				->where("id = ?", $work_id)
				->andWhere("vw_id = ?", $_REQUEST['vw_id']);
				$q->execute();
			}
			
		}
		
		//TODO-3796 Lore 16.02.2021
		public function deletehvisitdataAction()
		{
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    if($_REQUEST['vw_id'] && $_REQUEST['work_id'])
		    {
		        $work_id=($_REQUEST['work_id']);
		        
		        $q = Doctrine_Query::create()
		        ->update('PatientHospizvizits')
		        ->set('isdelete',"1")
		        ->where("id = ?", $work_id)
		        ->andWhere("vw_id = ?", $_REQUEST['vw_id']);
		        $q->execute();
		        
		        return true;
		        
		    } else {
		        
		        return false;
		    }
		    
		    
		}

		public function deleteaidAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			
			if($_REQUEST['remedy_id'] && $_REQUEST['id'])
			{
				$decid = Pms_Uuid::decrypt($_GET['id']);
				$ipid = Pms_CommonData::getIpId($decid);
				$remedy_id=($_REQUEST['remedy_id']);
			
				$q = Doctrine_Query::create()
				->update('PatientRemedies')
				->set('isdelete',"1")
				->where("id = ?", $remedy_id)
				->andWhere("ipid = '".$ipid."'");
				$q->execute();
			}
		}

		public function savememoAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			if($_REQUEST['id'] && $ipid && $_REQUEST['fieldname'] == 'patient_memo')
			{
				$content = nl2br($_POST['content']);
				$conn = Doctrine_Manager::getInstance()->getCurrentConnection();
				$q = 'INSERT INTO `patient_memo` (`id`, `ipid`, `memo`) VALUES (NULL, "' . $ipid . '","' . addslashes(strip_tags($content, '<br>')) . '") ON DUPLICATE KEY UPDATE memo="' . addslashes(strip_tags($content, '<br>')) . '";';
				$r = $conn->execute($q);
			}
			//load memo
			if($ipid)
			{
				$memos = new PatientMemo();
				$memo = $memos->getpatientMemo($ipid);
				$memo_pat = trim(strip_tags($memo[0]['memo']));

				if (strlen($memo_pat) == '0')
				{
					echo $this->view->translate('empty_memo');
				}
				else
				{
					echo strip_tags($memo[0]['memo'], '<br>');
				}
			}
			else
			{
				echo ''; //error
			}

			exit;
		}

		//ISPC-2827 Lore 30.03.2021
		public function saveclientuserboxAction()
		{
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		   
		    $decid = Pms_Uuid::decrypt($_REQUEST['id']);
		    $ipid = Pms_CommonData::getIpid($decid);
		    
		    if($clientid && $_REQUEST['fieldname'] == 'user_text_box')            //$_REQUEST['id'] && 
		    {
		        //dd($_POST['content']);
		        $content = nl2br($_POST['content']);
		        
		        $userbox = new ClientUserTextBox();
		        $box = $userbox->get_client_user_text_box($clientid, $userid);

		        if(empty($box)){
		            
		            $cutb = new ClientUserTextBox();
		            $cutb->clientid = $clientid;
		            $cutb->user = $userid;
		            $cutb->content = $content;
		            $cutb->save();
		            
		        } else {
		            
		            //update .... set inactive old one
		            $last_id = $box[0]['id'];

		            $q = Doctrine_Query::create()
		            ->update('ClientUserTextBox')
		            ->set('inactive',"1")
		            ->where("id = ?", $last_id);
		            $q->execute();
		            
		            //add new
		            $cutb = new ClientUserTextBox();
		            $cutb->clientid = $clientid;
		            $cutb->user = $userid;
		            $cutb->content = $content;
		            $cutb->previous_id = $last_id;
		            $cutb->save();
		        }

		    }
		    //load content
		    if($clientid)
		    {
		        $userbox = new ClientUserTextBox();
		        $box = $userbox->get_client_user_text_box($clientid, $userid);

		        $user_text_box = trim(strip_tags($box[0]['content']));
		        
		        if (strlen($user_text_box) == '0')
		        {
		            echo $this->view->translate('empty_user_text_box');
		        }
		        else
		        {
		            echo strip_tags($box[0]['content'], '<br>');
		        }
		    }
		    else
		    {
		        echo ''; //error
		    }
		    
		    exit;
		}
		
		//Maria:: Migration CISPC to ISPC 22.07.2020
		public function savediagnosisAction(){

            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            //echo $_REQUEST['id'];
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            //echo 'ajax';
            //echo 'ipid ' . $ipid;
            //echo '\n';

            $form_data = $_REQUEST['FormBlockDiagnosisClinic']['clinic_diagnosis'];
            //echo 'clinic diagnosis';
            //print_r($form_data);

            $af = new Application_Form_PatientDiagnosis();
            $af->save_form_diagnosis($ipid, $form_data);
            return 'done';




        }
		//Maria:: Migration CISPC to ISPC 22.07.2020
		public function savewhiteboxAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			// IM-5 whitebox
        if ($_REQUEST['id'] && $ipid && $_REQUEST['fieldname'] == 'patient_whitebox') {
            $content = ($_POST['content']); //ISPC-2800,elena,14.01.2021
				$oContent = new \StdClass();
            $oContent->whitebox = (addslashes(strip_tags($content))); //ISPC-2800,elena,14.01.2021
				$whitebox = json_encode($oContent);
				$patientWhitebox = new PatientWhitebox();
				$patientWhitebox->setPatientWhiteboxesAsDeleted($ipid);
				$patientWhitebox->ipid = $ipid;
				$patientWhitebox->whitebox = $whitebox;

				$patientWhitebox->save();
			}
			//load memo
			if($ipid) {
				$patientWhitebox = new PatientWhitebox();
				$whitebox = $patientWhitebox->getCurrentPatientWhitebox($ipid);
				$oWhitebox = json_decode($whitebox[0]['whitebox']);
                $whitebox_pat = trim(strip_tags($oWhitebox->whitebox));
				if (strlen($whitebox_pat) == '0') {
					echo $this->view->translate('empty_whitebox');
				} else {
                echo nl2br(trim(strip_tags($oWhitebox->whitebox), '<br>')); //ISPC-2800,elena,14.01.2021
			}
        } else {
				echo ''; //error
			}

			exit;
		}

		public function visitoverlappingAction()
		{

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['intersected'] = '0';

			if($_REQUEST['visit_type'] == 'D') //doctor visit
			{
				$kvno_d = new KvnoDoctor();
				$all_visits = $kvno_d->getAllPatientDoctorVisits($ipid, $_REQUEST['vizit_date']);
			} else if($_REQUEST['visit_type'] == 'BD') //nurse visit
			{
				$bayern_d = new BayernDoctorVisit();
				$all_visits = $bayern_d->getAllPatientBayernDoctorVisits($ipid, $_REQUEST['visit_date']);
			} else if($_REQUEST['visit_type'] == 'N') //nurse visit
			{
				$kvno_n = new KvnoNurse();
				$all_visits = $kvno_n->getAllPatientNurseVisits($ipid, $_REQUEST['vizit_date']);
			}
			else if($_REQUEST['visit_type'] == 'K') //koordination visit
			{
				$kvno_k = new VisitKoordination();
				$all_visits = $kvno_k->getAllPatientKoordinationVisits($ipid, $_REQUEST['visit_date']);
			}

			//separate users
			foreach($all_visits as $k_v_doc => $val_v_doc)
			{
				$create_users[] = $val_v_doc['create_user'];
			}

			//get users data
			$users = new User();
			$users_details = $users->getUsersDetails($create_users);

			foreach($all_visits as $k_doc => $v_doc)
			{
				//TODO: add a check if end visit datetime is same as post start visit datetime *
				//to be questioned if is allowed to select same time

				if($_REQUEST['visit_type'] != 'K')
				{
					if($_REQUEST['visit_type'] == 'BD')
					{// bayern doctor form
						$visit_date_arr = explode(" ", $v_doc['visit_date']);

						$source['start'] = date("Y-m-d H:i:s", strtotime($visit_date_arr[0] . " " . $v_doc['begin_date_h'] . ":" . $v_doc['begin_date_m'] . ":00"));
						$source['end'] = date("Y-m-d H:i:s", strtotime($visit_date_arr[0] . " " . $v_doc['end_date_h'] . ":" . $v_doc['end_date_m'] . ":00"));

						$start_date = $_REQUEST['visit_date'] . " " . $_REQUEST['begin_date_h'] . ":" . $_REQUEST['begin_date_m'] . ":00";
						$end_date = $_REQUEST['visit_date'] . " " . $_REQUEST['end_date_h'] . ":" . $_REQUEST['end_date_m'] . ":00";

						$target['start'] = date('Y-m-d H:i:s', strtotime($start_date));
						$target['end'] = date('Y-m-d H:i:s', strtotime($end_date));

						$v_doc['start_date'] = date('d.m.Y H:i', strtotime($source['start']));
						$v_doc['end_date'] = date('d.m.Y H:i', strtotime($source['end']));
					}
					else
					{
						$vizit_date_arr = explode(" ", $v_doc['vizit_date']);

						$source['start'] = date("Y-m-d H:i:s", strtotime($vizit_date_arr[0] . " " . $v_doc['kvno_begin_date_h'] . ":" . $v_doc['kvno_begin_date_m'] . ":00"));
						$source['end'] = date("Y-m-d H:i:s", strtotime($vizit_date_arr[0] . " " . $v_doc['kvno_end_date_h'] . ":" . $v_doc['kvno_end_date_m'] . ":00"));

						$start_date = $_REQUEST['vizit_date'] . " " . $_REQUEST['kvno_begin_date_h'] . ":" . $_REQUEST['kvno_begin_date_m'] . ":00";
						$end_date = $_REQUEST['vizit_date'] . " " . $_REQUEST['kvno_end_date_h'] . ":" . $_REQUEST['kvno_end_date_m'] . ":00";

						$target['start'] = date('Y-m-d H:i:s', strtotime($start_date));
						$target['end'] = date('Y-m-d H:i:s', strtotime($end_date));

						$v_doc['start_date'] = date('d.m.Y H:i', strtotime($source['start']));
						$v_doc['end_date'] = date('d.m.Y H:i', strtotime($source['end']));
					}
				}
				else
				{
					$vizit_date_arr = explode(" ", $v_doc['visit_date']);

					$source['start'] = date("Y-m-d H:i:s", strtotime($vizit_date_arr[0] . " " . $v_doc['visit_begin_date_h'] . ":" . $v_doc['visit_begin_date_m'] . ":00"));
					$source['end'] = date("Y-m-d H:i:s", strtotime($vizit_date_arr[0] . " " . $v_doc['visit_end_date_h'] . ":" . $v_doc['visit_end_date_m'] . ":00"));

					$start_date = $_REQUEST['vizit_date'] . " " . $_REQUEST['visit_begin_date_h'] . ":" . $_REQUEST['visit_begin_date_m'] . ":00";
					$end_date = $_REQUEST['vizit_date'] . " " . $_REQUEST['visit_end_date_h'] . ":" . $_REQUEST['visit_end_date_m'] . ":00";

					$target['start'] = date('Y-m-d H:i:s', strtotime($start_date));
					$target['end'] = date('Y-m-d H:i:s', strtotime($end_date));

					$v_doc['start_date'] = date('d.m.Y H:i', strtotime($source['start']));
					$v_doc['end_date'] = date('d.m.Y H:i', strtotime($source['end']));
				}

				if($_REQUEST['visit_type'] != 'BD')
				{
					$edit_id = $_REQUEST['kvno_edit_id'];
				}
				else
				{
					$edit_id = $_REQUEST['edit_id'];
				}


				if(Pms_CommonData::isintersected(strtotime($source['start']), strtotime($source['end']), strtotime($target['start']), strtotime($target['end'])) && $edit_id != $v_doc['id'])
				{
					$v_doc['user_details'] = $users_details[$v_doc['create_user']];
					$return['intersected'] = '1';
					$return['visits'][] = $v_doc;
				}
			}
			echo json_encode($return);
			exit;
		}

		public function visittimeintervalAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['intersected'] = '0';

			/* ------------------------------------------- Documentation time --------------------------------------------- */
			if(!empty($_REQUEST['documantation_time']) && $_REQUEST['documantation_time'] != '0')
			{
				$documantation_time = $_REQUEST['documantation_time'];
			}
			else
			{
				$documantation_time = 0;
			}

			/* ------------------------------------------- Driving time --------------------------------------------- */
			if(!empty($_REQUEST['driving_time']) && $_REQUEST['driving_time'] != '--')
			{
				$driving_time = $_REQUEST['driving_time'];
			}
			else
			{
				$driving_time = 0;
			}

			/* --------------------------------- Interval of curent visit  ------------------------------------------- */
			$vizit_date_arr = explode(".", $_REQUEST['visit_date']);
			$start_date = mktime($_REQUEST['begin_date_h'], $_REQUEST['begin_date_m'] - $driving_time, 0, $vizit_date_arr[1], $vizit_date_arr[0], $vizit_date_arr[2]);
			$end_date = mktime($_REQUEST['end_date_h'], ($_REQUEST['end_date_m'] + $driving_time) + $documantation_time, 0, $vizit_date_arr[1], $vizit_date_arr[0], $vizit_date_arr[2]);

			$target['start'] = date('Y-m-d H:i:s', $start_date);
			$target['end'] = date('Y-m-d H:i:s', $end_date);

			/* ------------------------------------- Interval 20:00-06:00  ------------------------------------------ */
			$visit_begin_date = strtotime($target['start']);
			$check_end_date = $_REQUEST['visit_date'] . " 06:00:00";
			$check_end_date = strtotime($check_end_date);

			if($visit_begin_date < $check_end_date)
			{
				$source_start_date = date('d.m.Y', strtotime('-1 day', strtotime($_REQUEST['visit_date']))) . " 20:00:00";
				$source_end_date = $_REQUEST['visit_date'] . " 06:00:00";
			}
			else
			{
				$source_start_date = $_REQUEST['visit_date'] . " 20:00:00";
				$source_end_date = date('d.m.Y', strtotime('+1 day', strtotime($_REQUEST['visit_date']))) . " 06:00:00";
			}

			$source['start'] = date('Y-m-d H:i:s', strtotime($source_start_date));
			$source['end'] = date('Y-m-d H:i:s', strtotime($source_end_date));

			/* ------------------------------------- Total visit duration------------------------------------------ */
			$to_time = strtotime($target['end']);
			$from_time = strtotime($target['start']);
			$total_visit_duration = round(abs($to_time - $from_time) / 60, 2);
			$return['total_visit_duration'] = $total_visit_duration;

			/* -------------- Check if Interval of curent visit is intersected with Interval 20:00-06:00-------------- */
			if(Pms_CommonData::isintersected(strtotime($source['start']), strtotime($source['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$return['intersected'] = '1';
			}
			echo json_encode($return);
			exit;
		}

		//NEW LIVESEARCH
		//	1. SAPV Doctor
		public function getsapvverordnungAction() // ISPC 1837 - not used
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('FamilyDoctor')
					->where("(trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ?) or (trim(lower(city)) like ?)",array( "%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%" ))
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("indrop = 0")
					->andWhere('isdelete = 0')
					->orderBy('last_name ASC');
				$droparray = $drop->fetchArray();

				foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
				}
				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	 2. FAMILY DOCTOR
		public function familydoctorAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 0)
			{
				
				$regexp = $search_string;
				Pms_CommonData::value_patternation($regexp);

				//ISPC-2582 Dragos 08.01.2021 - added doctornumber to where
				$drop = Doctrine_Query::create()
					->select('id,title,first_name,last_name,salutation,street1,zip,city,phone_practice,phone_private,fax,email,doctornumber')
					->from('FamilyDoctor')
// 					->where("trim(lower(last_name)) like ?  or trim(lower(first_name)) like ?",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->where("CONCAT_WS(',',last_name,first_name,practice,doctornumber)  REGEXP ?", $regexp)
					->andWhere('clientid = ?', $clientid)
					->andWhere("valid_till='0000-00-00'")
					->andWhere("indrop = 0")
					->andWhere('isdelete=0')
					->orderBy('last_name ASC');
				$droparray = $drop->fetchArray();

				foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['title'] = html_entity_decode($val['title'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['street1'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doc_fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doc_email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doctornumber'] = html_entity_decode($val['doctornumber'], ENT_QUOTES, "utf-8");
				}
				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	3. Health Insurance
		public function healthinsuranceAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$client = new Client();
			$client_specific = $client->clientOnlyHealthInsurance($clientid);

			if(strlen($_REQUEST['q']) > 0)
			{
				if($client_specific == '1')
				{
					$drop = Doctrine_Query::create()
						->select('id,name,city,iknumber,kvnumber,onlyclients,debtor_number')
						->from('HealthInsurance')
						->where("trim(lower(name)) like ?", "%".trim(mb_strtolower($search_string, 'UTF-8'))."%" )
						->andWhere(' isdelete= 0 ')
						->andWhere(' extra= 0 ')
						->andWhere(' onlyclients="1" ')
						->andWhere(' clientid="' . $clientid . '" ')
						->orderBy('name ASC')
						->limit('100');
					$droparray = $drop->fetchArray();
				}
				else
				{
					$drop = Doctrine_Query::create()
						->select('id,name,city,iknumber,kvnumber,onlyclients,debtor_number')
						->from('HealthInsurance')
						->where("trim(lower(name)) like ?", "%".trim(mb_strtolower($search_string, 'UTF-8'))."%" )
						->andWhere("(isdelete='0' and extra = '0' and onlyclients='0') or (isdelete='0' and extra='0' and onlyclients='1' and clientid='" . $clientid . "')  ")
						->orderBy('onlyclients DESC')
						->limit('100');
					$droparray = $drop->fetchArray();
				}

            	foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['iknumber'] = $val['iknumber'];
					$drop_array[$key]['kvnumber'] = $val['kvnumber'];
					$drop_array[$key]['onlyclients'] = $val['onlyclients'];
					$drop_array[$key]['debitor_number'] = $val['debtor_number'];
				}

				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	 4. Diagnosis (ICD & Diagnosis Description)
		public function diagnosisAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');

			$this->view->context = !empty($_REQUEST['context'] ) ? $_REQUEST['context'] : '';
			$this->view->returnRowId = !empty($_REQUEST['row'] ) ? $_REQUEST['row'] : '';
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = ! empty($limit) ? (int)$limit : 150; //ISPC - 2364 -description search bug
				
			if(strlen($_REQUEST['q']) > 0)
			{
				$search_string = addslashes(urldecode($_REQUEST['q']));

				if($_REQUEST['mode'] == 'icdnumber')
				{
					$srchoption = "trim(lower(icd_primary)) like trim(lower('%" . (addslashes(urldecode($_REQUEST['q']))) . "%'))";
					$order = 'icd_primary';
					
					$search_str = addslashes(urldecode($_REQUEST['q']));
					$search_str = trim($search_str);
					$srchoption = "LOWER(icd_primary) LIKE LOWER(:searchstr)";
						
				}
				else
				{
					//$search_str = htmlentities(addslashes(urldecode($_REQUEST['q'])), ENT_QUOTES, "utf-8"); //stupid indians saved data as html entities
					//$search_str = addslashes(urldecode($_REQUEST['q'])); //smart Alex saved it properly :P
					$search_str = urldecode($_REQUEST['q']);
					//$srchoption = "trim(lower(description)) like trim(lower('%" . ($search_str) . "%'))";
					
					//ISPC-1922 - p.2
					$search_str =  trim($search_str);
					//ISPC - 2364 - description search bug
					//$srchoption = "CONVERT(CAST(description as BINARY) USING utf8)  COLLATE utf8_general_ci LIKE CONVERT(CAST(:$search_str as BINARY) USING utf8)";
					if (isset($search_str) && strlen(trim($search_str)) > 0)
					{
						$regexp = trim($search_str);
						Pms_CommonData::value_patternation($regexp);

						$searchstring = mb_strtolower(trim($search_str), 'UTF-8');
						$searchstring_input = preg_quote(trim($search_str));
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

						$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \', description ) USING utf8 ) REGEXP ?';
						$regexp_arr[] = $regexp;
					}
					//$order = 'icd_primary';
					//$order ='CONVERT(CONVERT('.addslashes(htmlspecialchars('description')).' USING BINARY) USING utf8) ASC';
				}
		
				/**
				 * //ISPC-1922 - p.2
				 * 
				 * claudiu : columns/connection should be changed to utf8, before you reach a bottleneck with this typecasting
				 * ALTER TABLE diagnosis MODIFY description VARBINARY(255) DEFAULT '' NOT NULL;
				 * ALTER TABLE diagnosis MODIFY description VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
				 * 
				 * $conn = Doctrine_Manager::getInstance()->getConnection('SYSDAT');
				 * $conn->setCharset('utf8');
				 * $r = $conn->execute('SELECT id, icd_primary, description FROM diagnosis WHERE description LIKE \'%' . $search_str . '%\' AND isdelete=0 AND valid_till = '0000-00-00 00:00:00' LIMIT 100");
				 * $drop_array = $r->fetchAll();
				 * 
				 * //this is for column + connection = utf8 && _ci
				 * $srchoption = "description LIKE '%" . $search_str . "%'";
				 * 
				 * //this is for connection = utf8
				 * $srchoption = "CONVERT(CAST(description as BINARY) USING utf8)  COLLATE utf8_general_ci LIKE '%{$search_str}%'";
				 *
				 **/
				// Maria:: Migration ISPC to CISPC 08.08.2020
				//ISPC - 2364 - description search bug
				$drugs = Doctrine_Query::create()
				->select('*')
				->from('Diagnosis')
				//->where($srchoption)
				->where("isdelete=0 AND valid_till = '0000-00-00 00:00:00'");
				if($_REQUEST['mode'] == 'icdnumber')
				{
					$drugs->andWhere($srchoption);
					$drop_array = $drugs->fetchArray(array("searchstr" => "%".$search_str."%"));
				}
				else if($filter_search_value_arr && $regexp_arr)
				{
					$drugs->andWhere($filter_search_value_arr[0] , $regexp_arr);
					$drop_array = Pms_CommonData::array_stripslashes($drugs->fetchArray());
				}
				else
				{
					$drop_array = Pms_CommonData::array_stripslashes($drugs->fetchArray());
				}
				//$drugs->orderBy($order);
				
				/* if ( ! empty($limit)) {
				    $drugs->limit($limit);
				} */
				
				//$drop_array = Pms_CommonData::array_stripslashes($drugs->fetchArray());
				
				//$drop_array = $drugs->fetchArray(array("searchstr" => "%".$search_str."%"));
				foreach($drop_array as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['icd_primary'] = html_entity_decode($val['icd_primary'], ENT_QUOTES, "UTF-8");
					$drop_array[$key]['description'] = html_entity_decode($val['description'], ENT_QUOTES, "UTF-8");

					//muster13 splitted string
					$splitted_str = explode("\n", wordwrap(html_entity_decode($val['description'], ENT_QUOTES, "UTF-8"), "80", "\n"));
					$drop_array[$key]['description_line1'] = $splitted_str[0]; //first line should have <= 80 characters
					$drop_array[$key]['description_line2'] = $splitted_str[1]; //rest stands here
					//this is the increment to know which line to fill in admission diag form
					$drop_array[$key]['row'] = $_REQUEST['row'];
				}
					
				
				/**
				 * //ISPC-1922
				 * 1) if you search for "Demenz" then you get hundreds of results. but the diagnosis "Demenz" is place 55.
				 * --> please sort the results of the diagnosis to show FIRST a diagnosis which matches the search results. 
				 * after that sort the diagnosis by name
				 */
				if (isset($search_str) && ! empty($search_str)) 
				{	
					usort($drop_array, array(new Pms_Sorter('description', $search_str), "_strnatcmp"));
				}
				// Maria:: Migration ISPC to CISPC 08.08.2020
				//ISPC-2612 Ancuta 30.06.2020
				$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Diagnosis', $this->logininfo->clientid);
				// --


				//ISPC-2412 Carmen 22.11.2019
				$drop_client_array = array();
				$drop_general_array = array();
				$drop_array_final = array();
				foreach($drop_array as $keyd => $dropd)
				{
					if($dropd['clientid'] == $this->logininfo->clientid)
					{
					    //ISPC-2612 Ancuta 30.06.2020
					    if($client_is_follower){
					        if($dropd['connection_id'] != null && $dropd['master_id'] != null){
        						$drop_client_array[$keyd] = $dropd;
					        }

					    } else {

    						$drop_client_array[$keyd] = $dropd;
					    }
					    // --
					}
					else if($dropd['clientid'] == '0')
					{
						$drop_general_array[$keyd] = $dropd;
					}
				}

				if($limit != "")
				{
					$drop_general_array = array_slice($drop_general_array, null, $limit, true);
				}

				$drop_array_final = array_merge($drop_client_array, $drop_general_array);

				//$this->view->droparray = $drop_array;
				$this->view->droparray = $drop_array_final;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	 5. Medications
		public function medicationsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(strlen($_REQUEST['q']) > 0)
			{


				$querystr = "
			select m.id,m.name,m.comment, m.pkgsz from
			(select distinct(name),min(id)as id, package_size as pkgsz, comment as comment
			from medication_master
			where clientid = '" . $clientid . "'
			and extra=0
			and isdelete=0
			group by name)as m
			inner join medication_master b on m.id=b.id
			where(trim(lower(m.name)) like trim(lower(:search_string)))
			and isdelete=0
			and clientid = '" . $clientid . "'
			and extra=0";

				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('SYSDAT');
				$conn = $manager->getCurrentConnection();

				$query = $conn->prepare($querystr);

				$search_string = addslashes(urldecode(trim($_REQUEST['q']) . "%"));
				$query->bindValue(':search_string', $search_string);

				$dropexec = $query->execute();
				$drop_array = $query->fetchAll();

				foreach($drop_array as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
					$droparray[$key]['comment'] = html_entity_decode($val['comment'], ENT_QUOTES, "UTF-8");
					//this is the increment to know which line to fill in admission medis form
					$droparray[$key]['row'] = $_REQUEST['row'];
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}
		
		public function medicationsnutritionAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			if(strlen($_REQUEST['q']) > 0)
			{
		
		
				$querystr = "
			select m.id,m.name,m.comment, m.pkgsz from
			(select distinct(name),min(id)as id, package_size as pkgsz, comment as comment
			from medication_nutrition
			where clientid = '" . $clientid . "'
			and extra=0
			and isdelete=0
			group by name)as m
			inner join medication_nutrition b on m.id=b.id
			where(trim(lower(m.name)) like trim(lower(:search_string)))
			and isdelete=0
			and clientid = '" . $clientid . "'
			and extra=0";
				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('SYSDAT');
				$conn = $manager->getCurrentConnection();
		
				$query = $conn->prepare($querystr);
		
				$search_string = addslashes(urldecode(trim($_REQUEST['q']) . "%"));
				$query->bindValue(':search_string', $search_string);
		
				$dropexec = $query->execute();
				$drop_array = $query->fetchAll();
				foreach($drop_array as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
					$droparray[$key]['comment'] = html_entity_decode($val['comment'], ENT_QUOTES, "UTF-8");
					//this is the increment to know which line to fill in admission medis form
					$droparray[$key]['row'] = $_REQUEST['row'];
				}
		
				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
			
		}

		//	 6. General Patient Search
		public function patientsearchAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$clientid = $logininfo->clientid;
			$search_string = addslashes(trim(urldecode($_REQUEST['q'])));

			if($_REQUEST['json'] == 1) {
				$this->view->json = 1;
			}

			if(strlen($search_string) > 2)
			{


				$search_fl = explode(",",$search_string);
				$search_l = trim($search_fl[0]);
				$search_f = trim($search_fl[1]);
				$nr_fl = sizeof($search_fl);


				//print_r($nr_fl); exit;

				$drop = Doctrine_Query::create()
				->select('ipid, epid')
				->from('EpidIpidMapping')
				->where("clientid = ?" , $clientid);
				//->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				$comma = ",";
				$ipidval = "'0'";
				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$fn_epids[$val['ipid']] = $val['epid'];
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

					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,1, (IF(isdischarged = 1,2,0)))) )) ) as status,";
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
						$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,1, (IF(isdischarged = 1,2,0)))) )) ) as status,";
					}

					$search_string_umlaut = array (
							"ä"=>"ae",
							"ö"=>"oe",
							"ü"=>"ue",
							"ae"=>"ä",
							"oe"=>"ö",
							"ue"=>"ü",
							"ß"=>"ss",
							"ss"=>"ß"

					);

					if($nr_fl !=2)
					{

						foreach ($search_string_umlaut as $key => $value)
						{
							if(stripos($search_string, $value))
							{
								$search_str=$search_string;
								$search_string_ulm=str_ireplace($value,$key,$search_string);
								if (strpos('.', $search_str) !== false) {
									$dateborn = explode('.', $search_str);

									if(count($dateborn) > '2')
									{
										$datedb = $dateborn[2] . '-' . $dateborn[1] . '-' . $dateborn[0];
									}
									elseif(count($dateborn) =='2')
									{
										$datedb = $dateborn[1] . '-' . $dateborn[0];
									}
									elseif(count($dateborn) =='1')
									{
										$datedb = $dateborn[0];
									}
									$search_str = $datedb;
								}

								$patient = Doctrine_Query::create()
								->select($sql)
								->from('PatientMaster p')
								->where("p.ipid in(" . $ipidval . ")  and p.ipid IN (" . $user_patients['patients_str'] . ") and p.isdelete = 0");
								$patient->leftJoin("p.EpidIpidMapping e");
								$patient->leftJoin("p.PatientHealthInsurance h");
								$patient->andWhere("e.clientid = '" . $logininfo->clientid . "' AND (TRIM(LOWER(e.epid)) like TRIM(LOWER('%" . $search_str . "%')) OR TRIM(LOWER(h.insurance_no)) like TRIM(LOWER('%" . $search_str . "%')) OR TRIM(LOWER(p.birthd)) like TRIM(LOWER('%" . $search_str . "%'))
					       OR (
		
						       TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
			
						       OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
		
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
		
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
		
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
		
					       	   OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
					       ))");

								break;
							}
							else{

								if (strpos($search_string, '.')) {
									$dateborn = explode('.', $search_string);

									if(count($dateborn) > '2')
									{
										$datedb = $dateborn[2] . '-' . $dateborn[1] . '-' . $dateborn[0];
									}
									elseif(count($dateborn) =='2')
									{
										$datedb = $dateborn[1] . '-' . $dateborn[0];
									}
									elseif(count($dateborn) =='1')
									{
										$datedb = $dateborn[0];
									}
									$search_string = $datedb;
								}

								$patient = Doctrine_Query::create()
								->select($sql)
								->from('PatientMaster p')
								->where("p.ipid in(" . $ipidval . ")  and p.ipid IN (" . $user_patients['patients_str'] . ") and p.isdelete = 0");
								$patient->leftJoin("p.EpidIpidMapping e");
								$patient->leftJoin("p.PatientHealthInsurance h");
					  			$patient->leftJoin("p.PatientCaseStatus pcs");
								//old way -- search of Öztepe != öztepe  -- which was not OK
								//					$patient->andwhere("e.clientid = " . $logininfo->clientid . " and trim(lower(e.epid)) like trim(lower('%" . $search_string . "%')) or (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								//						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								//						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								//						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))");
					  		$patient->andWhere("e.clientid = '" . $logininfo->clientid . "' AND (TRIM(LOWER(e.epid)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(h.insurance_no)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(pcs.case_number)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(p.birthd)) like TRIM(LOWER('%" . $search_string . "%'))
					        OR (
						         TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
				           	))");

							}

						}


					}
					else
					{

						$search_l_query = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '". utf8_decode($search_l) ." ' USING utf8) USING latin1))) COLLATE latin1_german2_ci";
						$search_f_query = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( ' ".utf8_decode($search_f). "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci";

						$search_l_query_uml = '';
						$search_f_query_uml = '';

						foreach ($search_string_umlaut as $key => $value)
						{
							if(stripos($search_l, $value))
							{
								$search_l_uml = str_ireplace($value,$key,$search_l);
								$search_l_query_uml = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '". utf8_decode($search_l_uml) ." ' USING utf8) USING latin1))) COLLATE latin1_german2_ci";
								break;
							}

						}

						foreach ($search_string_umlaut as $key => $value)
						{

							if(stripos($search_f, $value))
							{
								$search_f_uml = str_ireplace($value,$key,$search_f);
								$search_f_query_uml = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '". utf8_decode($search_f_uml) ."%' USING utf8) USING latin1))) COLLATE latin1_german2_ci";
								break;
							}
						}

						if(!empty($search_l_query_uml)) {
							$search_l_query_final = '(('.$search_l_query.') OR ('.$search_l_query_uml.'))';
						} else {
							$search_l_query_final = $search_l_query;
						}

						if(!empty($search_f_query_uml)) {
							$search_f_query_final = '(('.$search_f_query.') OR ('.$search_f_query_uml.'))';
						} else {
							$search_f_query_final = $search_f_query;
						}

						$patient = Doctrine_Query::create()
						->select($sql)
						->from('PatientMaster p')
						->where("p.ipid in(" . $ipidval . ")  and p.ipid IN (" . $user_patients['patients_str'] . ") and p.isdelete = 0");
						$patient->leftJoin("p.EpidIpidMapping e");
						$patient->andWhere("e.clientid = '" . $logininfo->clientid . "' AND (TRIM(LOWER(e.epid)) OR (".$search_l_query_final." AND ".$search_f_query_final."))");
					}

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
					->select('ipid')
					->from('EpidIpidMapping')
					->where("clientid = ? " , $clientid );
					$fndroparray = $fndrop->fetchArray();

					$fnipidval = "'0'";
					if($fndroparray)
					{
						$comma = ",";
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
							IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,1, (IF(isdischarged = 1,2,0)))) )) ) as status,
							,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
									->from('PatientMaster')
									->where("isdelete = 0 and ipid in(" . $fnipidval . ") and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
							concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
							concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
							concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))")
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
					$res[$i]['epid_id'] = Pms_Uuid::encrypt($fn_epids[$res[$i]['ipid']]);
				}
				$this->view->droparray = $res;
				//print_r($this->view->droparray);exit;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	7. Family Doctor ( Stammdaten )
		public function sfamilydoctorAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$this->view->context = !empty($_REQUEST['context'] ) ? $_REQUEST['context'] : '';
			$this->view->returnRowId = !empty($_REQUEST['row'] ) ? $_REQUEST['row'] : '';
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{
				
				$regexp = $search_string;
				Pms_CommonData::value_patternation($regexp);

				//ISPC-2582 Dragos 08.01.2021 - added doctornumber to where
				$drop = Doctrine_Query::create()
					->select('id,title,salutation,first_name,last_name,street1,zip,city,phone_practice,phone_private,fax,email,doctornumber,doctor_bsnr,comments,practice,debitor_number,shift_billing')
					->from('FamilyDoctor')
// 					->where("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(practice)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->where("CONCAT_WS(',',last_name,first_name,practice,doctornumber)  REGEXP ?", $regexp)
					->andWhere('clientid = ?', $clientid)
					->andWhere("valid_till='0000-00-00'")
					->andWhere("indrop = 0")
					->andWhere('isdelete=0')
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$droparray = $drop->fetchArray();

				
				foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['title'] = html_entity_decode($val['title'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['street1'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doc_fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doc_email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doctornumber'] = html_entity_decode($val['doctornumber'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['doctor_bsnr'] = html_entity_decode($val['doctor_bsnr'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['practice'] = html_entity_decode($val['practice'], ENT_QUOTES, "utf-8");
					// ISPC-2272 (@ancuta 23.10.2018) 
					$drop_array[$key]['debitor_number'] = html_entity_decode($val['debitor_number'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['shift_billing'] = (int)$val['shift_billing'];
					// --
				}
				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	8. SAPV VERORDNUNG ( Stammdaten )
		public function sgetsapvverordnungAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			
			if(strlen($_REQUEST['q']) > 0)
			{
				$drop_q_fd = Doctrine_Query::create()
				->select('id,first_name,last_name,city')
				->from('FamilyDoctor')
				->where("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(city)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
				->andWhere('clientid = "' . $clientid . '"')
				->andWhere("indrop = 0")
				->andWhere('isdelete = 0')
				->andWhere('valid_till="0000-00-00"')
				->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop_q_fd->limit($limit);
				}
				
				$droparray_familydoctor = $drop_q_fd->fetchArray();
				
				if(!empty($droparray_familydoctor))
				{
					foreach($droparray_familydoctor as $key => $val)
					{
						$drop_array_familydoctor[$key]['id'] = $val['id'];
						$drop_array_familydoctor[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
						$drop_array_familydoctor[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
 						$drop_array_familydoctor[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
						$all['family_doctor'][] = $drop_array_familydoctor[$key];
					}
				}
				else
				{
					$all['family_doctor'][] = array();
				}
				
				$drop_q_sp = Doctrine_Query::create()
				->select('id,first_name,last_name,city')
				->from('Specialists')
				->where("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(city)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
				->andWhere('clientid = "' . $clientid . '"')
				->andWhere("indrop = 0")
				->andWhere('isdelete = 0')
				->andWhere('valid_till="0000-00-00"')
				->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop_q_sp->limit($limit);
				}
				
				$droparray_specialists = $drop_q_sp->fetchArray();
				
				if(!empty($droparray_specialists))
				{
					foreach($droparray_specialists as $key => $val)
					{
						$drop_array_specialists[$key]['id'] = $val['id'];
						$drop_array_specialists[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
						$drop_array_specialists[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
						$drop_array_specialists[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					
						$all['specialists'][] = $drop_array_specialists[$key];
					}
				}
				else
				{
					$all['specialists'][] = array();
				}
				//var_dump($drop_array_specialists); exit;
				
				// ISPC-2612 Ancuta 27.06.2020
				$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Locations', $clientid);
			 
				
				$drop_q_loc = Doctrine_Query::create()
				->select("id,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location,city")
				->from('Locations l ')
				->where("l.location_type = 7 or l.location_type = 1")
				->andWhere("(trim(lower(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1))) like ? ) or (trim(lower(city)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
				->andWhere("l.client_id='" . $clientid . "'");
				if ($client_is_follower) {// ISPC-2612 Ancuta 27.06.2020
				    $drop_q_loc->andWhere('l.connection_id is NOT null');
				    $drop_q_loc->andWhere('l.master_id is NOT null');
				}
				$drop_q_loc->andWhere('l.isdelete=0')
				->orderBy('location ASC');
				
				if ( ! empty($limit)) {
				    $drop_q_loc->limit($limit);
				}
				
				
// 				echo $drop_q_loc->getSqlQuery(); exit;
				$droparray_locations = $drop_q_loc->fetchArray();
				//var_dump($droparray_locations); exit;
				if(!empty($droparray_locations))
				{
					foreach($droparray_locations as $key => $val)
					{
						$drop_array_locations[$key]['id'] = $val['id'];
						$drop_array_locations[$key]['location'] = html_entity_decode($val['location'], ENT_QUOTES, "utf-8");
						$drop_array_locations[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
						$all['locations'][] = $drop_array_locations[$key];
				
					}
				}
				else
				{
					$all['locations'][] = array();
				}
				$this->view->droparray = $all;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	9. Health Insurance (Stammdaten)
		public function shealthinsuranceAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$client = new Client();
			$client_specific = $client->clientOnlyHealthInsurance($clientid);

			
			$this->view->context = !empty($_REQUEST['context'] ) ? $_REQUEST['context'] : '';
			$this->view->returnRowId = !empty($_REQUEST['row'] ) ? $_REQUEST['row'] : '';
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = ! empty($limit) ? (int)$limit : 100;
			
			if(strlen($_REQUEST['q']) > 0)
			{
				if($client_specific == '1')
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('HealthInsurance')
						->where("trim(lower(name)) like ?","%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
						->andWhere(' isdelete= 0 ')
						->andWhere(' extra= 0 ')
						->andWhere(' onlyclients="1" ')
						->andWhere(' clientid="' . $clientid . '" ')
						->orderBy('name ASC');
					
					if ( ! empty($limit)) {
					    $drop->limit($limit);
					}
					
					$droparray = $drop->fetchArray();
				}
				else
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('HealthInsurance')
						->where("trim(lower(name)) like ?", "%".trim(mb_strtolower($search_string, 'UTF-8'))."%" )
						->andWhere("(isdelete='0' and extra = '0' and onlyclients='0') or (isdelete='0' and extra='0' and onlyclients='1' and clientid='" . $clientid . "')  ")
						->orderBy('onlyclients DESC');
					
					if ( ! empty($limit)) {
					    $drop->limit($limit);
					}
					
					$droparray = $drop->fetchArray();
				}

				foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['insurance_provider'] = html_entity_decode($val['insurance_provider'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['street1'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone2'] = html_entity_decode($val['phone2'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phonefax'] = html_entity_decode($val['phonefax'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['post_office_box'] = html_entity_decode($val['post_office_box'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['post_office_box_location'] = html_entity_decode($val['post_office_box_location'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip_mailbox'] = html_entity_decode($val['zip_mailbox'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['email'] = $val['email'];
					$drop_array[$key]['iknumber'] = $val['iknumber'];
					$drop_array[$key]['kvnumber'] = $val['kvnumber'];
					$drop_array[$key]['debtor_number'] = $val['debtor_number'];
					$drop_array[$key]['onlyclients'] = $val['onlyclients'];
				}

				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}
		// custom report
		public function crhealthinsuranceAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$client = new Client();
			$client_specific = $client->clientOnlyHealthInsurance($clientid);

			if(strlen($_REQUEST['q']) > 0)
			{	
				
				if($client_specific == '1')
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('HealthInsurance')
						->where("trim(lower(name)) like ?","%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
						->andWhere(' isdelete= 0 ')
						->andWhere(' extra= 0 ')
						->andWhere(' onlyclients="1" ')
						->andWhere(' clientid="' . $clientid . '" ')
						->orderBy('name ASC')
						->limit('100');
					$droparray = $drop->fetchArray();
				}
				else
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('HealthInsurance')
						->where("trim(lower(name)) like ?","%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
						->andWhere("((isdelete='0' and extra = '0' and onlyclients='0') or (isdelete='0' and extra='0' and onlyclients='1' and clientid='" . $clientid . "') )")
						->orderBy('onlyclients DESC')
						->limit('100');
					$droparray = $drop->fetchArray();
				}

				
				foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['insurance_provider'] = html_entity_decode($val['insurance_provider'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['street1'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone2'] = html_entity_decode($val['phone2'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phonefax'] = html_entity_decode($val['phonefax'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['post_office_box'] = html_entity_decode($val['post_office_box'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['post_office_box_location'] = html_entity_decode($val['post_office_box_location'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip_mailbox'] = html_entity_decode($val['zip_mailbox'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['email'] = $val['email'];
					$drop_array[$key]['iknumber'] = $val['iknumber'];
					$drop_array[$key]['kvnumber'] = $val['kvnumber'];
					$drop_array[$key]['debtor_number'] = $val['debtor_number'];
					$drop_array[$key]['onlyclients'] = $val['onlyclients'];
					$drop_array[$key]['row'] = $_REQUEST['row'];
				}

				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	10. Pflegedienste (Stammdaten)
		public function pflegediensteAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$this->view->context = !empty($_REQUEST['context'] ) ? $_REQUEST['context'] : '';
			$this->view->returnRowId = !empty($_REQUEST['row'] ) ? $_REQUEST['row'] : '';
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{

				$drop = Doctrine_Query::create()
					->select('*')
					->from('Pflegedienstes')
					->where('clientid = "' . $clientid . '"')
					->andWhere("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(nursing)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere("valid_till='0000-00-00'")
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['nursing'] = html_entity_decode($val['nursing'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_emergency'] = html_entity_decode($val['phone_emergency'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['ik_number'] = html_entity_decode($val['ik_number'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	10.a  Pflegedienste ( Entlassplanung)
		public function otherpflegediensteAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('Pflegedienstes')
					->where('clientid = "' . $clientid . '"')
					->andWhere("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(nursing)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere("valid_till='0000-00-00'")
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['nursing'] = html_entity_decode($val['nursing'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_emergency'] = html_entity_decode($val['phone_emergency'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	11. Ehrenamlichte
		public function voluntaryworkersAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			
    		// get associated clients of current clientid START 
    		$logininfo = new Zend_Session_Namespace('Login_Info');
    		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
    		if($connected_client){
    		    $clientid = $connected_client;
    		} else{
    		    $clientid = $logininfo->clientid;
    		}
 
			
			
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
			
			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;

			if(strlen($_REQUEST['q']) > 0)
			{
				//ISPC 1739
				$regexp = $search_string;
				Pms_CommonData::value_patternation($regexp);
//				$regexp = mb_strtolower($regexp, 'UTF-8');
				
				$drop = Doctrine_Query::create()
					->select('*')
					->from('Voluntaryworkers')
					->where('clientid = ?', $clientid)
					//ISPC 1739
					->addWhere("lower(last_name) REGEXP ? OR lower(first_name) REGEXP ?",array($regexp, $regexp))
					
					/* 
					 * ->where("clientid='" . $clientid . "' and  trim(lower(last_name)) like trim(lower('%" . $search_string . "%'))) or (trim(lower(first_name)) like trim(lower('%" . $search_string . "%')))")
					*/
					
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
					//die($drop->getSqlQuery());
				$drop_arr = $drop->fetchArray();
				foreach($drop_arr as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['street'] = html_entity_decode($val['street'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['mobile'] = html_entity_decode($val['mobile'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['status'] = html_entity_decode($val['status'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['hospice_association'] = html_entity_decode($val['hospice_association'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	12. Apotheke (Stammdaten)
		public function pharmacyAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*, pharmacy as apotheke')
					->from('Pharmacy')
					->where('clientid = ?' ,$clientid)
					->andWhere("isdelete = ? ","0")
					->andWhere("(trim(lower(pharmacy)) like ? )  or (trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere("valid_till = ?","0000-00-00")
					->andWhere("indrop = ?","0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['pharmacy'] = html_entity_decode($val['apotheke'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
				}
				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	13. Medikation (Form Rezept) [ check if is the same as 13 ]
		public function receiptAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 0)
			{
				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('SYSDAT');
				$conn = $manager->getCurrentConnection();
				$querystr = "select m.id,m.name, m.pkgsz
			from (
			select distinct(name),min(id)as id, package_size as pkgsz
			from medication_receipt
			where clientid = '" . $clientid . "'
			and extra=0
			and isdelete=0
			group by name
			) as m
			inner join medication_receipt b
			on m.id=b.id
			where(trim(lower(m.name)) like trim(lower('" . $search_string . "%')))
			and isdelete=0
			and clientid = '" . $clientid . "'
			and extra=0";
				$query = $conn->prepare($querystr);
				$dropexec = $query->execute();
				$droparr = $query->fetchAll();


				foreach($droparr as $k_droparr => $v_droparr)
				{
					$droparray[$k_droparr]['id'] = $v_droparr['id'];
					$droparray[$k_droparr]['name'] = $v_droparr['name'];
					$droparray[$k_droparr]['row'] = $_REQUEST['row']; //pass row in dropdown html
				}
				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	14. Rechnungen
		public function patientsearchinvoiceAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$clientid = $logininfo->clientid;
			if(strlen($_REQUEST['q']) > 2)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("clientid = '" . $clientid . "'")
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
					}
				}

				$user_patients = PatientUsers::getUserPatients($logininfo->userid);

				if(count($droparray) > 0)
				{
					$sql = "*,e.epid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
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
					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "*, e.epid, ";
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
					$patient->andwhere("e.clientid = " . $logininfo->clientid . " and trim(lower(e.epid)) like trim(lower('%" . $search_string . "%')) or (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))")
						->andWhere('isstandby="0"')
						->andWhere('isstandbydelete="0"');

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
						->select("*, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
						->from('PatientMaster p')
						->leftJoin("p.EpidIpidMapping e")
						->where("isdelete = 0 and ipid in(" . $fnipidval . ") and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))")
						->andwhere("e.clientid = " . $logininfo->clientid)
						->andWhere('isstandby="0"')
						->andWhere('isstandbydelete="0"')
						->orderby('status');

					$droparray2 = $patient1->fetchArray();
				}
			}

			$res_data = array();

			if(is_array($droparray2) || is_array($droparray1))
			{
				$results = array_merge((array) $droparray2, (array) $droparray1);

				foreach($results as $i => $res)
				{
					$res_data[$i]['status'] = $res['status'];
					$res_data[$i]['epid'] = $res['EpidIpidMapping']['epid'];
					$res_data[$i]['first_name'] = $res['first_name'];
					$res_data[$i]['last_name'] = $res['last_name'];

					if(strlen($res['middle_name']) > 0)
					{
						$res_data[$i]['middle_name'] = $res['middle_name'];
					}
					else
					{
						$res_data[$i]['middle_name'] = " ";
					}

					if($res['admission_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['admission_date'] = date('d.m.Y', strtotime($res['admission_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['recording_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['recording_date'] = date('d.m.Y', strtotime($res['recording_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['birthd'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['birthd'] = date('d.m.Y', strtotime($res['birthd']));
					}
					else
					{
						$res_data[$i]['birthd'] = "-";
					}

					$res_data[$i]['birthd'] = Pms_CommonData::hideInfo($res['birthd'], $res['isadminvisible']);

					$res_data[$i]['id'] = Pms_Uuid::encrypt($res['id']);
				}
				$this->view->droparray = $res_data;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	15. Vernetzung
		public function patientsearchshareAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$clientid = $logininfo->clientid;
			if(strlen($_REQUEST['q']) > 2)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("clientid = '" . $clientid . "'")
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
					}
				}

				$user_patients = PatientUsers::getUserPatients($logininfo->userid);

				if(count($droparray) > 0)
				{
					$sql = "*,e.epid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
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
					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "*, e.epid, ";
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
					
					$patient->andwhere("e.clientid = " . $logininfo->clientid . " and trim(lower(e.epid)) like ?  or 
						(trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
							trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?   or 
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?  or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?  or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?  or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? )",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							);
					

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

						$patient1 = Doctrine_Query::create()
							->select("*, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
							IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
							,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
							->from('PatientMaster p')
							->leftJoin("p.EpidIpidMapping e")
							->where("isdelete = 0")
							->where("ipid in(" . $fnipidval . ") and 
									(trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
									trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
									concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?  or
									concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?  or
									concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?  or
									concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? )",
									array(
											"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
											"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
											"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
											"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
											"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
									)
									
									
							->andwhere("e.clientid = " . $logininfo->clientid)
							->orderby('status');

						$droparray2 = $patient1->fetchArray();
					}
				}
			}

			$res_data = array();

			if(is_array($droparray2) || is_array($droparray1))
			{
				$results = array_merge((array) $droparray2, (array) $droparray1);

				foreach($results as $i => $res)
				{
					$res_data[$i]['status'] = $res['status'];
					$res_data[$i]['epid'] = $res['EpidIpidMapping']['epid'];
					$res_data[$i]['first_name'] = $res['first_name'];
					$res_data[$i]['last_name'] = $res['last_name'];

					if(strlen($res['middle_name']) > 0)
					{
						$res_data[$i]['middle_name'] = $res['middle_name'];
					}
					else
					{
						$res_data[$i]['middle_name'] = " ";
					}

					if($res['admission_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['admission_date'] = date('d.m.Y', strtotime($res['admission_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['recording_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['recording_date'] = date('d.m.Y', strtotime($res['recording_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['birthd'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['birthd'] = date('d.m.Y', strtotime($res['birthd']));
					}
					else
					{
						$res_data[$i]['birthd'] = "-";
					}

					$res_data[$i]['birthd'] = Pms_CommonData::hideInfo($res['birthd'], $res['isadminvisible']);

					$res_data[$i]['id'] = Pms_Uuid::encrypt($res['id']);
				}
				$this->view->droparray = $res_data;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//  16. Supplies (Stammdaten)
		public function suppliesAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
			
			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('Supplies')
					->where("(trim(lower(supplier)) like ? )  or (trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere('clientid = ?',$clientid)
					->andWhere("indrop = ?","0")
					->andWhere("isdelete = ?","0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['supplier'] = html_entity_decode($val['supplier'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['logo'] = html_entity_decode($val['logo'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");

					if($_REQUEST['multiple'])
					{
						$droparray[$key]['supplier_row'] = $_REQUEST['multiple'];
					}
					else
					{
						$droparray[$key]['supplier_row'] = '';
					}
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//  17. hospice association (Stammdaten)
		public function hospiceassociationsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			// TODO-
			$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
			if($connected_client){
				$clientid = $connected_client;
			} else{
				$clientid = $logininfo->clientid;
			}
			
			
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
			
			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('Hospiceassociation') 
					->where("(trim(lower(hospice_association)) like ? )  or (trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere('clientid = ? ',$clientid)
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();
                // Maria:: Migration ISPC to CISPC 08.08.2020
				//TODO-3129 Lore 28.04.2020
				$droparray = array();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['hospice_association'] = html_entity_decode($val['hospice_association'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_emergency'] = html_entity_decode($val['phone_emergency'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");    //TODO-3129 Lore 28.04.2020
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	18  Medications - treatment care
		public function medicationstreatmentcareAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(strlen($_REQUEST['q']) > 0)
			{


				$querystr = "
			select m.id,m.name from
			(select distinct(name),min(id)as id
			from medication_treatment_care
			where clientid = '" . $clientid . "'
			and extra=0
			and isdelete=0
			group by name)as m
			inner join medication_treatment_care b on m.id=b.id
			where(trim(lower(m.name)) like trim(lower(:search_string)))
			and isdelete=0
			and clientid = '" . $clientid . "'
			and extra=0";

				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('SYSDAT');
				$conn = $manager->getCurrentConnection();

				$query = $conn->prepare($querystr);

				$search_string = addslashes(urldecode('%'.trim($_REQUEST['q']) . "%"));
				$query->bindValue(':search_string', $search_string);

				$dropexec = $query->execute();
				$drop_array = $query->fetchAll();

				foreach($drop_array as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
// 					$droparray[$key]['comment'] = html_entity_decode($val['comment'], ENT_QUOTES, "UTF-8");
					//this is the increment to know which line to fill in admission medis form
					$droparray[$key]['row'] = $_REQUEST['row'];
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		// ispc-1533 this function is now used also in dayplanningnew
		//	19. Teambesprechung - patientsearch
		/*
		 * @since 22.02.2018 + @cla sort_by & sort_dir introduced, the sort is done on php  
		 */
		public function patientsearchteammeetingAction()
		{

			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$clientid = $logininfo->clientid;


			$field_value = $_REQUEST['field_value'];
			$search_string = addslashes(urldecode(trim($_REQUEST['field_value'])));
			$patient_status = $_REQUEST['status'];

			if(strlen($_REQUEST['meeting_id']) > 0 && !empty($_REQUEST['meeting_id']))
			{
				$meeting_id = $_REQUEST['meeting_id'];
				$team_patients_array = TeamMeetingPatients::get_team_meeting_patients($meeting_id, $clientid);
				if(!empty($team_patients_array))
				{
					foreach($team_patients_array as $k => $pat_data)
					{
						$existing_team_patients[] = $pat_data['patient'];
					}
				}
			}

			$standby_sql = "";
			$discharge_sql = "";
			if(!empty($patient_status))
			{
				if($patient_status == "standby")
				{
					$standby_sql = " AND isstandby = 1 ";
				}
				else if($patient_status == "discharged_alive" || $patient_status == "discharged_dead")
				{
					$discharge_sql = " AND isdischarged = 1 AND isstandby = 0  AND isarchived = 0 ";
				}
			}




			if(!empty($_REQUEST['status']) && ($_REQUEST['status'] == "discharged_alive" || $_REQUEST['status'] == "discharged_dead"))
			{

				$discharge_method = new DischargeMethod();
				$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
				foreach($discharge_methods as $k_dis_method => $v_dis_method)
				{
					if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA" || $v_dis_method['abbr'] == "verstorben")
					{
						$death_methods[] = $v_dis_method['id'];
					}
				}
				$death_methods = array_values(array_unique($death_methods));
				if(empty($death_methods))
				{
					$death_methods[] = "999999";
				}
			}


			if(strlen($_REQUEST['field_value']) > 2 || !empty($_REQUEST['status']))
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("clientid = '" . $clientid . "'")
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
						$client_ipids[] = $val['ipid'];
					}
				}

				if(empty($client_ipids))
				{
					$client_ipids[] = "999999";
				}

				if(count($droparray) > 0)
				{
					$sql = "*,e.epid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
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
					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "*, e.epid, ";
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
						->whereIn("p.ipid", $client_ipids)
						->andWhere("p.isdelete = 0")
						->andWhere('isstandbydelete="0" ' . $standby_sql . $discharge_sql . ' ');
					if(!empty($existing_team_patients))
					{
						$patient->whereNotIn("p.ipid", $existing_team_patients);
					}
					$patient->leftJoin("p.EpidIpidMapping e");
					$patient->andwhere("e.clientid = " . $logininfo->clientid);
					$patient->andwhere("trim(lower(e.epid)) like ? or 
						(trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
						trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							);
					if($logininfo->hospiz == 1)
					{
						$patient->andwhere('ishospiz = 1');
					}
					$patient->orderby('status,ipid');
					$droparray1 = $patient->fetchArray();

					if($_REQUEST['status'] == "discharged_alive" || $_REQUEST['status'] == "discharged_dead")
					{

						if(!empty($droparray1))
						{

							foreach($droparray1 as $k => $pdata)
							{
								$discharged_patients[] = $pdata['ipid'];
								$discharged_patients_str .= '"' . $pdata['ipid'] . '", ';
							}

							if(!empty($discharged_patients))
							{
								$discharged_patients[] = "999999";
							}

							$discharged_patients_array = array();

							$patient_discharge = Doctrine_Query::create();
							$patient_discharge->from('PatientDischarge d');
							$patient_discharge->whereIn("d.ipid", $discharged_patients);
							$patient_discharge->andWhere("d.isdelete = 0");
							if($_REQUEST['status'] == "discharged_alive")
							{
								$patient_discharge->andWhereNotIn('d.discharge_method', $death_methods);
							}
							else
							{
								$patient_discharge->andWhereIn('d.discharge_method', $death_methods);
								$patient_discharge->andWhere("date(d.discharge_date) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 4 WEEK) AND CURRENT_DATE() ");
							}
							$discharged_patients_q_array = $patient_discharge->fetchArray();


							if(!empty($discharged_patients_q_array))
							{
								foreach($discharged_patients_q_array as $d_key => $d_patient)
								{
									$discharged_patients_array[] = $d_patient['ipid'];
								}
							}

							foreach($droparray1 as $patient_k => $patient_data)
							{
								if(!in_array($patient_data['ipid'], $discharged_patients_array))
								{
									$unseted[] = $droparray1[$patient_k];
									unset($droparray1[$patient_k]);
								}
							}
						}
					}
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
							$client_ipids[] = $val['ipid'];
						}
					}

					if(empty($client_ipids))
					{
						$client_ipids[] = "999999";
					}
					$patient1 = Doctrine_Query::create()
						->select("*, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
						->from('PatientMaster p')
						->leftJoin("p.EpidIpidMapping e")
						->whereIn("p.ipid", $client_ipids)
						->andWhere("p.isdelete = 0");

					if(!empty($existing_team_patients))
					{
						$patient1->whereNotIn("p.ipid", $existing_team_patients);
					}

					$patient1->andWhere("(trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
							trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
							concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							)
						->andwhere("e.clientid = " . $logininfo->clientid)
						->andWhere('isstandbydelete="0"  ' . $standby_sql . $discharge_sql . '  ')
						->orderby('status');

					$droparray2 = $patient1->fetchArray();

					if($_REQUEST['status'] == "discharged_alive" || $_REQUEST['status'] == "discharged_dead")
					{
						if(!empty($droparray2))
						{
							foreach($droparray2 as $k => $pdata)
							{
								$discharged_patients2[] = $pdata['ipid'];
							}

							if(!empty($discharged_patients2))
							{
								$discharged_patients2[] = "999999";
							}

							$discharged_patients_array_2 = array();

							$patient_discharge = Doctrine_Query::create();
							$patient_discharge->from('PatientDischarge d');
							$patient_discharge->whereIn("d.ipid", $discharged_patients2);
							$patient_discharge->andWhere("d.isdelete = 0");
							if($_REQUEST['status'] == "discharged_alive")
							{
								$patient_discharge->andWhereNotIn('d.discharge_method', $death_methods);
							}
							else
							{
								$patient_discharge->andWhereIn('d.discharge_method', $death_methods);
								$patient_discharge->andWhere("date(d.discharge_date) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 4 WEEK) AND CURRENT_DATE() ");
							}
							$discharged_patients_array_2 = $patient_discharge->fetchArray();

							if(!empty($discharged_patients_array_2))
							{
								foreach($discharged_patients_array_2 as $d_key => $d_patient)
								{
									$discharged_patients_array2[] = $d_patient['ipid'];
								}
							}
							foreach($droparray2 as $patient_k => $patient_data)
							{
								if(!in_array($patient_data['ipid'], $discharged_patients_array2))
								{
									$unseted2[] = $droparray2[$patient_k];
									unset($droparray2[$patient_k]);
								}
							}
						}
					}
				}
			}

			$res_data = array();


			if(is_array($droparray2) || is_array($droparray1))
			{
				$results = array_merge((array) $droparray2, (array) $droparray1);

				foreach($results as $i => $res)
				{
					$res_data[$i]['status'] = $res['status'];
					$res_data[$i]['epid'] = $res['EpidIpidMapping']['epid'];
					$res_data[$i]['first_name'] = $res['first_name'];
					$res_data[$i]['last_name'] = $res['last_name'];

					if(strlen($res['middle_name']) > 0)
					{
						$res_data[$i]['middle_name'] = $res['middle_name'];
					}
					else
					{
						$res_data[$i]['middle_name'] = " ";
					}

					if($res['admission_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['admission_date'] = date('d.m.Y', strtotime($res['admission_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['recording_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['recording_date'] = date('d.m.Y', strtotime($res['recording_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['birthd'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['birthd'] = date('d.m.Y', strtotime($res['birthd']));
					}
					else
					{
						$res_data[$i]['birthd'] = "-";
					}

					$res_data[$i]['birthd'] = Pms_CommonData::hideInfo($res['birthd'], $res['isadminvisible']);

					$res_data[$i]['id'] = Pms_Uuid::encrypt($res['id']);
				}
				$patient_data = $res_data;
			}
			else
			{
				$patient_data = array();
			}
			
			
			if ($sort_by = $this->getRequest()->getParam('sort_by')) {
			    
			    $sort_by = in_array($sort_by, ['epid', 'first_name', 'last_name']) ? $sort_by : "last_name";
			    
			    usort($patient_data, array(new Pms_Sorter($sort_by), "_strnatcasecmp"));
			    
			    if ($this->getRequest()->getParam('sort_dir') == "DESC") {
			        $patient_data = array_reverse($patient_data);
			    }
			}
			
			$this->view->droparray = $patient_data;

			return $patient_data;
// 			print_r($patient_data);
// 			exit;
// 			echo json_encode($patient_data);
		}
		
		
		/**
		 * Ancuta
		 * ISPC-2281
		 * show all but DEAD
		 */
		public function patientsearchordersAction()
		{

			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$clientid = $logininfo->clientid;


			$field_value = $_REQUEST['field_value'];
			$search_string = addslashes(urldecode(trim($_REQUEST['field_value'])));


			$discharge_method = new DischargeMethod();
			$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
			foreach($discharge_methods as $k_dis_method => $v_dis_method)
			{
				if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA" || $v_dis_method['abbr'] == "verstorben")
				{
					$death_methods[] = $v_dis_method['id'];
				}
			}
			$death_methods = array_values(array_unique($death_methods));
 

			if(strlen($_REQUEST['field_value']) > 2)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->where("clientid = '" . $clientid . "'")
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
						$client_ipids[] = $val['ipid'];
					}
				}

				if(empty($client_ipids))
				{
					$client_ipids[] = "999999";
				}

				if(count($droparray) > 0)
				{
					$sql = "*,e.epid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
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
					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					// if super admin check if patient is visible or not
					if($logininfo->usertype == 'SA')
					{
						$sql = "*, e.epid, ";
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
						->whereIn("p.ipid", $client_ipids)
						->andWhere("p.isdelete = 0")
						->andWhere('isstandbydelete="0"');
					$patient->leftJoin("p.EpidIpidMapping e");
					$patient->andwhere("e.clientid = " . $logininfo->clientid);
					$patient->andwhere("trim(lower(e.epid)) like ? or 
						(trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
						trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							);
					if($logininfo->hospiz == 1)
					{
						$patient->andwhere('ishospiz = 1');
					}
					$patient->orderby('status,ipid');
					$droparray1 = $patient->fetchArray();

					if(!empty($droparray1))
					{

						foreach($droparray1 as $k => $pdata)
						{
							$discharged_patients[] = $pdata['ipid'];
							$discharged_patients_str .= '"' . $pdata['ipid'] . '", ';
						}
 
						if(!empty($discharged_patients) && !empty($death_methods)){
						    
    						$discharged_patients_array = array();
    
    						$patient_discharge = Doctrine_Query::create();
    						$patient_discharge->from('PatientDischarge d');
    						$patient_discharge->whereIn("d.ipid", $discharged_patients);
    						$patient_discharge->andWhere("d.isdelete = 0");
    						$patient_discharge->andWhereIn('d.discharge_method', $death_methods);
    			 
    						$discharged_patients_q_array = $patient_discharge->fetchArray();
    
    
    						if(!empty($discharged_patients_q_array))
    						{
    							foreach($discharged_patients_q_array as $d_key => $d_patient)
    							{
    								$discharged_patients_array[] = $d_patient['ipid'];
    							}
    						}
    
    						foreach($droparray1 as $patient_k => $patient_data)
    						{
    							if(in_array($patient_data['ipid'], $discharged_patients_array))
    							{
    								$unseted[] = $droparray1[$patient_k];
    								unset($droparray1[$patient_k]);
    							}
    						}
					   }
					}
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
							$client_ipids[] = $val['ipid'];
						}
					}

					if(empty($client_ipids))
					{
						$client_ipids[] = "999999";
					}
					$patient1 = Doctrine_Query::create()
						->select("*, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
						IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
						->from('PatientMaster p')
						->leftJoin("p.EpidIpidMapping e")
						->whereIn("p.ipid", $client_ipids)
						->andWhere("p.isdelete = 0");

					$patient1->andWhere("(trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? or 
							trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ?  or 
							concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ? or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE ?)",
							array(
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							)
						->andwhere("e.clientid = " . $logininfo->clientid)
						->andWhere('isstandbydelete="0"')
						->orderby('status');

					$droparray2 = $patient1->fetchArray();

					if(!empty($droparray2))
					{
						foreach($droparray2 as $k => $pdata)
						{
							$discharged_patients2[] = $pdata['ipid'];
						}

						if( ! empty($discharged_patients2) && !empty($death_methods))
						{
							$discharged_patients_array_2 = array();

							$patient_discharge = Doctrine_Query::create();
							$patient_discharge->from('PatientDischarge d');
							$patient_discharge->whereIn("d.ipid", $discharged_patients2);
							$patient_discharge->andWhere("d.isdelete = 0");
							$patient_discharge->andWhereIn('d.discharge_method', $death_methods);
 
							$discharged_patients_array_2 = $patient_discharge->fetchArray();

							if(!empty($discharged_patients_array_2))
							{
								foreach($discharged_patients_array_2 as $d_key => $d_patient)
								{
									$discharged_patients_array2[] = $d_patient['ipid'];
								}
							}
							foreach($droparray2 as $patient_k => $patient_data)
							{
								if( in_array($patient_data['ipid'], $discharged_patients_array2))
								{
									$unseted2[] = $droparray2[$patient_k];
									unset($droparray2[$patient_k]);
								}
							}
							
						}
					}
				}
			}

			$res_data = array();


			if(is_array($droparray2) || is_array($droparray1))
			{
				$results = array_merge((array) $droparray2, (array) $droparray1);
 
				foreach($results as $i => $res)
				{
					$res_data[$i]['status'] = $res['status'];
					$res_data[$i]['epid'] = $res['EpidIpidMapping']['epid'];
					$res_data[$i]['first_name'] = $res['first_name'];
					$res_data[$i]['last_name'] = $res['last_name'];

					if(strlen($res['middle_name']) > 0)
					{
						$res_data[$i]['middle_name'] = $res['middle_name'];
					}
					else
					{
						$res_data[$i]['middle_name'] = " ";
					}

					if($res['admission_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['admission_date'] = date('d.m.Y', strtotime($res['admission_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['recording_date'] != '0000-00-00 00:00:00')
					{
						$res_data[$i]['recording_date'] = date('d.m.Y', strtotime($res['recording_date']));
					}
					else
					{
						$res_data[$i]['recording_date'] = "-";
					}

					if($res['birthd'] != '0000-00-00')
					{
						$res_data[$i]['birthd'] = date('d.m.Y', strtotime($res['birthd']));
					}
					else
					{
						$res_data[$i]['birthd'] = "-";
					}
					$res_data[$i]['birthd'] = Pms_CommonData::hideInfo($res_data[$i]['birthd'], $res['isadminvisible']);
			 

					$res_data[$i]['id'] = Pms_Uuid::encrypt($res['id']);
				}
				$patient_data = $res_data;
			}
			else
			{
				$patient_data = array();
			}
			$this->view->droparray = $patient_data;

			return $patient_data;
// 			print_r($patient_data);
// 			exit;
// 			echo json_encode($patient_data);
		}

		//	Update symptomatology date and time
		public function updatesymptomatologyAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			if(strlen($_REQUEST['create_date']) > 0 && strlen($_REQUEST['entry_date']) > 0)
			{
				if(strlen($_REQUEST['dt']) > 0) //update date only
				{
					$create_date = date('Y-m-d H:i:s', $_REQUEST['create_date']);
					$entry_date = date('Y-m-d H:i:s', $_REQUEST['entry_date']);
					$new_date = date('Y-m-d', strtotime(html_entity_decode($_REQUEST['dt']))) . " " . date("H:i:s", $_REQUEST['entry_date']);

					$dateddd = Doctrine_Query::create()
						->update("Symptomatology")
						->set('entry_date','?',$new_date)
						->where('ipid LIKE ?', $ipid)
						->andWhere('entry_date = ? ',$entry_date);
					$dateddd_arr = $dateddd->fetchArray();

					$response = array();
					$response['entry_date'] = $entry_date;
					$response['create_date'] = $create_date;
					$response['saved_date'] = $new_date;

					echo json_encode($response);
				}
				else if(strlen($_REQUEST['tt']) > 0) //update time only
				{
					$create_date = date('Y-m-d H:i:s', $_REQUEST['create_date']);
					$entry_date = date('Y-m-d H:i:s', $_REQUEST['entry_date']);
					$new_time = date('Y-m-d', html_entity_decode($_REQUEST['entry_date'])) . " " . date('H:i', strtotime(html_entity_decode($_REQUEST['tt']))) . ":" . date('s');

					$dateddd = Doctrine_Query::create()
						->update("Symptomatology")
						->set('entry_date','?', $new_time )
						->where('ipid LIKE ?', $ipid)
						->andWhere('entry_date = ?', $entry_date);
					$dateddd_arr = $dateddd->fetchArray();

					$response = array();
					$response['entry_date'] = $entry_date;
					$response['create_date'] = $create_date;
					$response['saved_date'] = $new_time;

					echo json_encode($response);
				}


				$qa = Doctrine_Query::create()
					->update('PatientCourse')
					->set('done_date', "'" . $response['saved_date'] . "'")
					->where('course_type = AES_ENCRYPT("S", "' . Zend_Registry::get('salt') . '")')
					->andWhere('done_date = ?', $response['entry_date'])
					->andWhere('ipid LIKE ?', $ipid)
					->andWhere('source_ipid = ""');
				$qa->execute();
			}
			exit;
		}

		public function assignpatienticonAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			if($_REQUEST['iconid'])
			{
				$icons_form = new Application_Form_Icons();
				$remove_icon = $icons_form->remove_patient_icon($ipid, $_REQUEST['iconid']);
				$assign_icon = $icons_form->assign_patient_icon($ipid, $_REQUEST['iconid']);
			}

			echo json_encode(array("status" => "ok"));
			exit;
		}

		public function removepatienticonAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			if($_REQUEST['iconid'])
			{
				$icons_form = new Application_Form_Icons();
				$remove_icon = $icons_form->remove_patient_icon($ipid, $_REQUEST['iconid']);
			}

			echo json_encode(array("status" => "ok"));
			exit;
		}

		public function saveallergiesAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			if($_REQUEST['id'])
			{

				$content = nl2br($_REQUEST['content']);

				if(strlen(trim($_REQUEST['content'])) == '0')
				{
				    //$content = $this->view->translate('no_allergies');//TODO-2853 Lore 27.01.2020 - Do not add default text to db
				}


				$aller_port['ipid'] = $ipid;
				$aller_port['allergies_comment'] = $content;

				$aller = new PatientDrugPlanAllergies();
				$allergies = $aller->getPatientDrugPlanAllergies($decid);

				$med_form_allergies = new Application_Form_PatientDrugPlanAllergies();

				if(!empty($allergies))
				{
					$med_form_allergies->UpdateData($aller_port);
				}
				else
				{
					$med_form_allergies->InsertData($aller_port);
				}
			}

			//load allergies
			if($ipid)
			{
				$allergies = new PatientDrugPlanAllergies();
				$allergies_comment = $allergies->getPatientDrugPlanAllergies($decid);
				//TODO-2853 Lore 27.01.2020// Maria:: Migration ISPC to CISPC 08.08.2020
				// if no allergies, send default text to view
				if(strlen($allergies_comment[0]['allergies_comment']) == '0' ){
				    echo $this->view->translate('no_allergies');
				} else{
    				echo $allergies_comment[0]['allergies_comment'];
				}
			}
			else
			{
				echo ''; //error
			}

			exit;
		}

		public function savepopupseenAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($_REQUEST['popup'] == 'sapv')
			{
				$popup_type = 'sapv_dialog_' . $userid . '_' . $clientid;
				$latest_news = '0';
			}
			else if($_REQUEST['popup'] == 'news')
			{
				$popup_type = 'news_dialog_' . $userid . '_' . $clientid . '_' . $_REQUEST['last_news'];
				$latest_news = $_REQUEST['last_news'];
			}
			//ISPC - 2125 - alerts if a verordnung is after XX days still in mode "Keine Angabe"
			if($_REQUEST['popup'] == 'sapv_noinf')
			{
				$popup_type = 'sapv_noinf_dialog_' . $userid . '_' . $clientid;
				$latest_news = '0';
			}			
			
			//clear old entries
			if($_REQUEST['popup'] != 'news')
			{
				PopupVisibility::clearUserPopupSettings($userid, $clientid, $popup_type);
			}

			//insert new popup seen today
			$pop_settings = new PopupVisibility();
			$pop_settings->popup = $popup_type;
			$pop_settings->userid = $userid;
			$pop_settings->clientid = $clientid;
			$pop_settings->newsid = $latest_news;
			$pop_settings->save();

			exit;
		}

		public function userstampinfoAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			/* ---------------------------------------------------------- */
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
			/* ---------------------------------------------------------- */

			$ustamp = new UserStamp();

			if($multiplestamps_option === true && !empty($_REQUEST['stamp-info']))
			{
				$stamp_info = explode('-', $_REQUEST['stamp-info']);

				$user = $stamp_info[0];
				$stamp = $stamp_info[1];

				$userstatmp = $ustamp->getUserStampById($user, $stamp);

				if(!empty($userstatmp))
				{
					$user_stamp_info['bsnr'] = $userstatmp[0]['stamp_bsnr'];
					$user_stamp_info['lanr'] = $userstatmp[0]['stamp_lanr'];

					$user_stamp_info['row1'] = $userstatmp[0]['row1'];
					$user_stamp_info['row2'] = $userstatmp[0]['row2'];
					$user_stamp_info['row3'] = $userstatmp[0]['row3'];
					$user_stamp_info['row4'] = $userstatmp[0]['row4'];
					$user_stamp_info['row5'] = $userstatmp[0]['row5'];
					$user_stamp_info['row6'] = $userstatmp[0]['row6'];
					$user_stamp_info['row7'] = $userstatmp[0]['row7'];

					echo json_encode($user_stamp_info);
					exit;
				}
				else
				{
					echo '0';
				}
			}
			else
			{


				$user = $_REQUEST['stamp-info'];
				$userstatmp = $ustamp->getLastUserStamp($user);

				$user_bsnr_lanr = Doctrine::getTable('User')->find($user);
				if(!empty($user_bsnr_lanr)){
    				$uarray = $user_bsnr_lanr->toArray();
				}

				//get user bsnr and lanr no matter if user has stamp or not
				$user_stamp_info['bsnr'] = $uarray['betriebsstattennummer'];
				$user_stamp_info['lanr'] = $uarray['LANR'];

				if(!empty($userstatmp))
				{
					$user_stamp_info['row1'] = $userstatmp[0]['row1'];
					$user_stamp_info['row2'] = $userstatmp[0]['row2'];
					$user_stamp_info['row3'] = $userstatmp[0]['row3'];
					$user_stamp_info['row4'] = $userstatmp[0]['row4'];
					$user_stamp_info['row5'] = $userstatmp[0]['row5'];
					$user_stamp_info['row6'] = $userstatmp[0]['row6'];
					$user_stamp_info['row7'] = $userstatmp[0]['row7'];

					echo json_encode($user_stamp_info);
				}
				else
				{
					echo '0';
				}
			}

			exit;
		}

		public function pricelistoverlappingAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			$source['start'] = $_REQUEST['start'];
			$source['end'] = $_REQUEST['end'];

			if(!empty($_REQUEST['list']))
			{
				$source['list'] = $_REQUEST['list'];
			}
			else
			{
				$source['list'] = '0';
			}

			$return['intersected'] = '0';

			if(!empty($_REQUEST['start']) && !empty($_REQUEST['end']))
			{
				$p_list = new PriceList();
				$price_lists = $p_list->get_lists($clientid);

				foreach($price_lists as $k_p_list => $v_p_list)
				{
					//incoming data is Y-m-d
					$r1start = strtotime($source['start']);
					$r1end = strtotime($source['end']);

					//array data is Y-m-d H:i:s -> Y-m-d
					$r2start = strtotime(date('Y-m-d', strtotime($v_p_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_p_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $v_p_list['id'] != $source['list'])
					{
						$return['intersected'] = '1';
						$return['list_data'] = $v_p_list;
					}
				}
				echo json_encode($return);
				exit;
			}
		}

		public function checklocationvisitsAction()
		{

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['visits_in_period'] = '0';

			$valid_from = strtotime("+1 day", strtotime($_REQUEST['location_valid_from']));

			if($_REQUEST['location_valid_till'] == '')
			{
				$_REQUEST['location_valid_till'] = date('d.m.Y');
			}
			$valid_till = strtotime("-1 day", strtotime($_REQUEST['location_valid_till']));

			$kvno_n = new KvnoNurse();
			$all_visits = $kvno_n->getPatientAllNurseVisitsInPeriod($ipid, $valid_from, $valid_till);

			if(!empty($all_visits) && count($all_visits) > 0)
			{
				$return['visits_in_period'] = '1';
			}
			else
			{
				$return['visits_in_period'] = '0';
			}

			echo json_encode($return);
			exit;
		}

		public function checkeditlocationvisitsAction()
		{

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['visits_in_period'] = '0';

			parse_str($_REQUEST['form_data'], $location_details);

			foreach($location_details as $key => $value)
			{
				$n = count($location_details['location_id']);
				for($i = 0; $i <= $n; $i++)
				{
					$location_array[$i][$key] = $value[$i];
				}
			}

			foreach($location_array as $keys => $values)
			{
				if(!empty($values['location_id']) && $values['location_type'] == '1')
				{ // if location it is hospital.
					$valid_from = strtotime("+1 day", strtotime($values['valid_from']));

					if($values['valid_till'] == '')
					{
						$values['valid_till'] = date('d.m.Y');
					}
					$valid_till = strtotime("-1 day", strtotime($values['valid_till']));

					$kvno_n = new KvnoNurse();
					$all_visits = $kvno_n->getPatientAllNurseVisitsInPeriod($ipid, $valid_from, $valid_till);
				}
			}

			if(!empty($all_visits) && count($all_visits) > 0)
			{
				$return['visits_in_period'] = '1';
			}
			else
			{
				$return['visits_in_period'] = '0';
			}

			echo json_encode($return);
			exit;
		}

		public function healthinsurancesubdivisionsAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$health_company_id = $_REQUEST['hid'];

			if(!empty($health_company_id))
			{
				$hisub = new HealthInsuranceSubdivisions();
				$health_insurance_subdivision = $hisub->getClientHealthInsuranceSubdivisions($health_company_id);

				echo json_encode($health_insurance_subdivision);
			}
			else
			{
				echo '0';
			}

			exit;
		}

		public function ebmblockconditionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$return['unforseen_i'] = '0';
			$return['unforseen_ii'] = '0';

			/* ------------------------------------------- VISIT DATE - DAY OF WEEK ----------------------------------------------- */
			$visit_day_of_week = date('l', strtotime($_REQUEST['date']));

			/* ------------------------------------------- VISIT DATE - NATIONAL HOLIDAY ----------------------------------------------- */
			$nh = new NationalHolidays();
			$national_holiday = $nh->getNationalHoliday($clientid, $_REQUEST['date']);

			/* --------------------------------- Interval of curent visit  ------------------------------------------- */
			$vizit_date_arr = explode(".", $_REQUEST['date']);
			$start_date = mktime($_REQUEST['begin_date_h'], $_REQUEST['begin_date_m'], 0, $vizit_date_arr[1], $vizit_date_arr[0], $vizit_date_arr[2]);
			$end_date = mktime($_REQUEST['end_date_h'], ($_REQUEST['end_date_m']), 0, $vizit_date_arr[1], $vizit_date_arr[0], $vizit_date_arr[2]);

			$target['start'] = date('Y-m-d H:i:s', $start_date);
			$target['end'] = date('Y-m-d H:i:s', $end_date);

			/* ------------------------------------- Interval 7PM - 10PM (19:00-22:00)  ------------------------------------------ */
			$start_date_7PM10PM = $_REQUEST['date'] . " 19:00:01";
			$end_date_7PM10PM = $_REQUEST['date'] . " 21:59:59";

			$int_07PM_10PM['start'] = date('Y-m-d H:i:s', strtotime($start_date_7PM10PM));
			$int_07PM_10PM['end'] = date('Y-m-d H:i:s', strtotime($end_date_7PM10PM));

			/* ------------------------------------- Interval 7AM - 7PM (07:00-19:00)  ------------------------------------------ */
			$start_date_7AM7PM = $_REQUEST['date'] . " 07:00:01";
			$end_date_7AM7PM = $_REQUEST['date'] . " 18:59:59";

			$int_07AM_07PM['start'] = date('Y-m-d H:i:s', strtotime($start_date_7AM7PM));
			$int_07AM_07PM['end'] = date('Y-m-d H:i:s', strtotime($end_date_7AM7PM));

			/* -------------------------------------ii Interval 10PM - 7AM (22:00-07:00)  ------------------------------------------ */
			$curent_visit_begin_date = strtotime($target['start']);
			$check_end_date = $_REQUEST['date'] . " 07:00:00";
			$check_end_date = strtotime($check_end_date);

			if($curent_visit_begin_date < $check_end_date)
			{
				$start_date_10PM7AM = date('d.m.Y', strtotime('-1 day', strtotime($_REQUEST['date']))) . " 22:00:01";
				$end_date_10PM7AM = $_REQUEST['date'] . " 07:00:00";
			}
			else
			{
				$start_date_10PM7AM = $_REQUEST['date'] . " 22:00:01";
				$end_date_10PM7AM = date('d.m.Y', strtotime('+1 day', strtotime($_REQUEST['date']))) . " 07:00:00";
			}

			$int_10PM_07AM['start'] = date('Y-m-d H:i:s', strtotime($start_date_10PM7AM));
			$int_10PM_07AM['end'] = date('Y-m-d H:i:s', strtotime($end_date_10PM7AM));

			/* ------------------------------------- Interval 7PM-7AM (19:00-07:00)  ------------------------------------------ */
			$visit_begin_date_7PM7AM = strtotime($target['start']);
			$check_end_date7PM7AM = $_REQUEST['date'] . " 07:00:00";
			$check_end_date7PM7AM = strtotime($check_end_date7PM7AM);

			if($visit_begin_date_7PM7AM < $check_end_date7PM7AM)
			{
				$start_date_7PM7AM = date('d.m.Y', strtotime('-1 day', strtotime($_REQUEST['date']))) . " 19:00:01";
				$end_date_7PM7AM = $_REQUEST['date'] . " 07:00:00";
			}
			else
			{
				$start_date_7PM7AM = $_REQUEST['date'] . " 19:00:01";
				$end_date_7PM7AM = date('d.m.Y', strtotime('+1 day', strtotime($_REQUEST['date']))) . " 07:00:00";
			}

			$int_07PM_07AM['start'] = date('Y-m-d H:i:s', strtotime($start_date_7PM7AM));
			$int_07PM_07AM['end'] = date('Y-m-d H:i:s', strtotime($end_date_7PM7AM));


			/* ------------------------------------- Contact form EMB conditions------------------------------------------ */

			// between 7PM - 10 PM
			if(Pms_CommonData::isintersected(strtotime($int_07PM_10PM['start']), strtotime($int_07PM_10PM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_07PM_10PM = true;
			}
			else
			{
				$between_07PM_10PM = false;
			}

			// between 7AM and 7PM
			if(Pms_CommonData::isintersected(strtotime($int_07AM_07PM['start']), strtotime($int_07AM_07PM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_07AM_07PM = true;
			}
			else
			{
				$between_07AM_07PM = false;
			}

			// between 10 PM - 7AM
			if(Pms_CommonData::isintersected(strtotime($int_10PM_07AM['start']), strtotime($int_10PM_07AM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_10PM_07AM = true;
			}
			else
			{
				$between_10PM_07AM = false;
			}

			//  between 7PM and 7AM
			if(Pms_CommonData::isintersected(strtotime($int_07PM_07AM['start']), strtotime($int_07PM_07AM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_07PM_07AM = true;
			}
			else
			{
				$between_07PM_07AM = false;
			}

			if($visit_day_of_week == 'Saturday')
			{
				$Saturday = true;
			}
			else
			{
				$Saturday = false;
			}

			if($visit_day_of_week == 'Sunday')
			{
				$Sunday = true;
			}
			else
			{
				$Sunday = false;
			}


			if(( $between_07PM_10PM === true && ($Saturday === false && $Sunday === false && $national_holiday === false)) ||
				( $between_07AM_07PM === true && ($Saturday === true || $Sunday === true || $national_holiday === true)))
			{
				$return['unforseen_i'] = '1';
			}
			else
			{
				$return['unforseen_i'] = '0';
			}

			if(( $between_10PM_07AM === true && ($Saturday === false && $Sunday === false && $national_holiday === false)) ||
				( $between_07PM_07AM === true && ($Saturday === true || $Sunday === true || $national_holiday === true)))
			{
				$return['unforseen_ii'] = '1';
			}
			else
			{
				$return['unforseen_ii'] = '0';
			}

			echo json_encode($return);
			exit;
		}

		public function goablockconditionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			/* ------------------------------------------- VISIT DATE - DAY OF WEEK ----------------------------------------------- */
			$visit_day_of_week = date('l', strtotime($_REQUEST['date']));


			/* ------------------------------------------- VISIT DATE - NATIONAL HOLIDAY ----------------------------------------------- */
			$nh = new NationalHolidays();
			$national_holiday = $nh->getNationalHoliday($clientid, $_REQUEST['date']);


			/* --------------------------------- Interval of curent visit  ------------------------------------------- */
			$vizit_date_arr = explode(".", $_REQUEST['date']);
			$start_date = mktime($_REQUEST['begin_date_h'], $_REQUEST['begin_date_m'], 0, $vizit_date_arr[1], $vizit_date_arr[0], $vizit_date_arr[2]);
			$end_date = mktime($_REQUEST['end_date_h'], ($_REQUEST['end_date_m']), 0, $vizit_date_arr[1], $vizit_date_arr[0], $vizit_date_arr[2]);

			$target['start'] = date('Y-m-d H:i:s', $start_date);
			$target['end'] = date('Y-m-d H:i:s', $end_date);


			/* ------------------------------------- Interval 8PM - 10PM (20:00-22:00)  ------------------------------------------ */
			$start_date_8PM10PM = $_REQUEST['date'] . " 20:00:01";
			$end_date_8PM10PM = $_REQUEST['date'] . " 21:59:59";

			$int_08PM_10PM['start'] = date('Y-m-d H:i:s', strtotime($start_date_8PM10PM));
			$int_08PM_10PM['end'] = date('Y-m-d H:i:s', strtotime($end_date_8PM10PM));

			/* ------------------------------------- Interval 6AM - 08AM (06:00-08:00)  ------------------------------------------ */
			$start_date_6AM8AM = $_REQUEST['date'] . " 06:00:01";
			$end_date_6AM8AM = $_REQUEST['date'] . " 07:59:59";

			$int_06AM_08AM['start'] = date('Y-m-d H:i:s', strtotime($start_date_6AM8AM));
			$int_06AM_08AM['end'] = date('Y-m-d H:i:s', strtotime($end_date_6AM8AM));

			/* -------------------------------------ii Interval 10PM - 6AM (22:00-06:00)  ------------------------------------------ */
			$curent_visit_begin_date = strtotime($target['start']);
			$check_end_date = $_REQUEST['date'] . " 06:00:00";
			$check_end_date = strtotime($check_end_date);

			if($curent_visit_begin_date < $check_end_date)
			{
				$start_date_10PM6AM = date('d.m.Y', strtotime('-1 day', strtotime($_REQUEST['date']))) . " 22:00:01";
				$end_date_10PM6AM = $_REQUEST['date'] . " 06:00:00";
			}
			else
			{
				$start_date_10PM6AM = $_REQUEST['date'] . " 22:00:01";
				$end_date_10PM6AM = date('d.m.Y', strtotime('+1 day', strtotime($_REQUEST['date']))) . " 06:00:00";
			}

			$int_10PM_06AM['start'] = date('Y-m-d H:i:s', strtotime($start_date_10PM6AM));
			$int_10PM_06AM['end'] = date('Y-m-d H:i:s', strtotime($end_date_10PM6AM));


			/* ------------------------------------- Contact form GOA conditions------------------------------------------ */

			// between 20.00 - 22.00
			if(Pms_CommonData::isintersected(strtotime($int_08PM_10PM['start']), strtotime($int_08PM_10PM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_2000_2200 = true;
			}
			else
			{
				$between_2000_2200 = false;
			}

			// between 06.00 - 08.00
			if(Pms_CommonData::isintersected(strtotime($int_06AM_08AM['start']), strtotime($int_06AM_08AM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_0600_0800 = true;
			}
			else
			{
				$between_0600_0800 = false;
			}

			//  between 22.00 - 06.00
			if(Pms_CommonData::isintersected(strtotime($int_10PM_06AM['start']), strtotime($int_10PM_06AM['end']), strtotime($target['start']), strtotime($target['end'])))
			{
				$between_2200_0600 = true;
			}
			else
			{
				$between_2200_0600 = false;
			}


			if($visit_day_of_week == 'Saturday')
			{
				$Saturday = true;
			}
			else
			{
				$Saturday = false;
			}

			if($visit_day_of_week == 'Sunday')
			{
				$Sunday = true;
			}
			else
			{
				$Sunday = false;
			}

			$consulting = $_REQUEST['consulting'];
			$recipe_transfer = $_REQUEST['recipe_transfer'];
			$expert_advice = $_REQUEST['expert_advice'];

			if(($consulting == '1' || $recipe_transfer == '1' || $expert_advice == '1') && ( $between_2000_2200 === true || $between_0600_0800 === true ))
			{
				$return['charge_i_y1'] = '1';
				$return['charge_i_y2'] = '0';
			}
			else if(($consulting == '1' || $recipe_transfer == '1' || $expert_advice == '1') && $between_2200_0600 === true)
			{
				$return['charge_i_y1'] = '0';
				$return['charge_i_y2'] = '1';
			}

			if(($consulting == '1' || $recipe_transfer == '1' || $expert_advice == '1') && ($Saturday === true || $Sunday === true || $national_holiday === true))
			{
				$return['charge_i_y3'] = '1';
			}
			else
			{
				$return['charge_i_y3'] = '0';
			}

			$return['dayofweek_saturday'] = $Saturday;
			$return['dayofweek_sunday'] = $Sunday;
			$return['nationalholiday'] = $national_holiday;

			$discussion_of_impact = $_REQUEST['discussion_of_impact'];
			$consultant_discussion = $_REQUEST['consultant_discussion'];
			$detailed_report = $_REQUEST['detailed_report'];

			if(($discussion_of_impact == '1' || $consultant_discussion == '1' || $detailed_report == '1') && ( $between_2000_2200 === true || $between_0600_0800 === true ))
			{
				$return['charge_ii_y1'] = '1';
				$return['charge_ii_y2'] = '0';
			}
			else if(($discussion_of_impact == '1' || $consultant_discussion == '1' || $detailed_report == '1') && $between_2200_0600 === true)
			{
				$return['charge_ii_y1'] = '0';
				$return['charge_ii_y2'] = '1';
			}

			if(($discussion_of_impact == '1' || $consultant_discussion == '1' || $detailed_report == '1') && ($Saturday === true || $Sunday === true || $national_holiday === true))
			{
				$return['charge_ii_y3'] = '1';
			}
			else
			{
				$return['charge_ii_y3'] = '0';
			}

			if($return)
			{
				echo json_encode($return);
			}
			else
			{
				echo '';
			}
			exit;
		}

		public function savecusomactionAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$edit_action_id = $_REQUEST['action_id'];

			if($_REQUEST['id'])
			{

				$action_name = $_REQUEST['action_name'];

				if(!$edit_action_id)
				{
					$user = new SocialCodeActions();
					$user->clientid = $logininfo->clientid;
					$user->action_name = $action_name;
					$user->custom = '1';
					$user->save();

					$custom_id = $user->id;

					$pat = new PatientCustomActions();
					$pat->ipid = $ipid;
					$pat->action_id = $custom_id;
					$pat->save();
				}
				else
				{
					$stmb = Doctrine::getTable('SocialCodeActions')->find($edit_action_id);
					$stmb->action_name = $_REQUEST['action_name'];
					$stmb->save();
				}
			}

			$actiondetais = array();
			if($ipid)
			{
				if(!$edit_action_id)
				{
					$actiondetais['action_name'] = $action_name;
					$actiondetais['action_id'] = $custom_id;
					$actiondetais['opt'] = 'new';
				}
				else
				{
					$actiondetais['action_name'] = $action_name;
					$actiondetais['action_id'] = $edit_action_id;
					$actiondetais['opt'] = 'edit';
				}

				echo json_encode($actiondetais);
			}
			else
			{
				echo ''; //error
			}

			exit;
		}

		public function savecustomparentAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$action_id = $_REQUEST['action_id'];
			$parent_id = $_REQUEST['action_parent'];

			if($_REQUEST['id'])
			{

				if(!empty($action_id) && !empty($parent_id))
					$stmb = Doctrine::getTable('SocialCodeActions')->find($action_id);
				$stmb->parent = $parent_id;
				$stmb->save();
			}

			$actiondetais = array();
			if($ipid)
			{
				$actiondetais['parent'] = $parent_id;

				echo json_encode($actiondetais);
			}
			else
			{
				echo ''; //error
			}

			exit;
		}

		public function deletecusomactionAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$delete_action_id = $_REQUEST['delete_action_id'];

			if($_REQUEST['id'])
			{
				$stmb = Doctrine::getTable('SocialCodeActions')->find($delete_action_id);
				$stmb->isdelete = '1';
				$stmb->save();

				$pca = Doctrine_Query::create()
					->update('PatientCustomActions')
					->set('isdelete', "1")
					->set('change_date', '?',date('Y-m-d H:i:s'))
					->where('ipid = ?', $ipid)
					->andWhere('action_id = ?', $delete_action_id);
				$pca->execute();

				$fmb = Doctrine_Query::create()
					->update('FormBlockSgbv')
					->set('unpaid', "1")
					->where('ipid = ?', $ipid)
					->andWhere('action_id = ?', $delete_action_id);
				$fmb->execute();

				$sfi = Doctrine_Query::create()
					->update('SgbvFormsItems')
					->set('isdelete', "1")
					->where('ipid = ?', $ipid)
					->andWhere('action_id = ?', $delete_action_id);
				$sfi->execute();
			}

			$actiondetais = array();
			if($ipid)
			{
				$actiondetais['delete_action_id'] = $delete_action_id;
				$actiondetais['deleted'] = '1';

				echo json_encode($actiondetais);
			}
			else
			{
				echo ''; //error
			}

			exit;
		}

		public function checksapvAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$inserted_date = date('Y-m-d H:i:s', strtotime($_REQUEST['date']));

			$select = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('status != "1"')
				->andWhere('isdelete = "0"')
				->andWhere('ipid = "' . $ipid . '"')
				->andWhere('DATE("' . $inserted_date . '") BETWEEN verordnungam AND verordnungbis');
			$sel_res = $select->fetchArray();


			if($sel_res)
			{
				echo json_encode(array('result' => '1'));
			}
			else
			{
				echo json_encode(array('result' => '0'));
			}
			exit;
		}

		public function dashboardlistoldAction()
		{
			setlocale(LC_ALL, 'de_DE.UTF-8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->_helper->layout->setLayout('layout_ajax');

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$this->view->userid = $userid;

			$groupid = $logininfo->groupid;
			$user_type = $logininfo->usertype;
			$done_events = new DashboardActionsDone();
			$labels_form = new Application_Form_DashboardActions();
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$wlprevileges = new Modules();
			$user_c = new User();
			$user_c_details = $user_c->getUserDetails($userid);
			$client_users = $user_c->getUserByClientid($clientid, 0, true);
			foreach($client_users as $k_c_usr => $v_c_usr)
			{
				$client_users_arr[$v_c_usr['id']] = $v_c_usr;
			}
			$data['client'] = $clientid;
			$data['user'] = $userid;
			$data['event'] = $_REQUEST['eventid'];
			$data['tabname'] = $_REQUEST['tabname'];
			$data['source'] = 'u'; //aded by user interactions
			$data['done_date'] = $_REQUEST['donedate']; //aded by user interactions

			$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
			$modules = new Modules();
			
			if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
			{
				$this->view->acknowledge_func = "1";
				if(in_array($userid,$approval_users)){
					$this->view->approval_rights = "1";
				} else{
					$this->view->approval_rights = "0";
				}
			
			}
			
			else
			{
				$this->view->acknowledge_func = "0";
			}
			
			
			if($_REQUEST['mode'] == 'undone')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//changed to delete a comment in verlauf entry
					//$save_todo = $todos->uncompleteTodo($_REQUEST['eventid']);
					$save_todo = $todos->uncompleteTodonew($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'custom_doctor_event_team')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}

				$done_entry_id = $_REQUEST['eventid'];
				$labels_event_form = $labels_form->delete_done_entry($done_entry_id);
				echo '1';
				exit;
			}
			else if($_REQUEST['mode'] == 'done')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//$save_todo = $todos->completeTodo($_REQUEST['eventid']);
					//changed to write a comment in verlauf entry
					$save_todo = $todos->completeTodonew($_REQUEST['eventid']);
				}
				else if($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_sgbxi = $dashboard_events->complete_dashboard_event($_REQUEST['eventid']);
				}

				$labels_event_form = $labels_form->add_done_entry($data);
				echo '1';
				exit;
			}

			//load excluded events
			$excluded_events = $done_events->getClientDashboardActions($clientid, true);

			/* ------------ BOX - "User Dashboard " START---------------- */
			$label_actions = Pms_CommonData::get_dashboard_actions();

			$dashboard_labels = new DashboardLabels();
			$label_details = $dashboard_labels->getClientLabels();

			foreach($label_details as $k_label => $v_label)
			{
				$labels[$v_label['id']] = $v_label;
			}


			//ANLAGE 4aWL
			$user = Doctrine_Query::create()
				->select("*")
				->from('User')
				->where('clientid = ' . $clientid . ' or usertype="SA"')
				->andWhere('isactive=0 and isdelete = 0')
				->orderBy('last_name ASC');
			$userarray = $user->fetchArray();

			$comma = ",";
			$usercomma = "'0'";
			if(count($userarray) > 0)
			{
				foreach($userarray as $key => $valu)
				{
					$clientUsersArray[$valu['id']] = $valu;
					$usercomma .= $comma . "'" . $valu['id'] . "'";
					$comma = ",";
				}
			}
			$key_start = 0;
			$wl_perms = $wlprevileges->checkModulePrivileges("51", $clientid);
			if($wl_perms)
			{

				$sqlWeekDays = "";
				$sqlHaving = "";
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  `admission_date` ) , 56 ) AS sixWeeks" . $i . " ,";
					$sqlHaving .= "sixWeeks" . $i . " = 0 OR ";
				}
				$sqlHaving = substr($sqlHaving, 0, -4);

				$patientwl = Doctrine_Query::create()
					->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, " . $sqlWeekDays . " e.epid")
					->from('PatientMaster as p')
					->where('isdelete = 0')
					->andWhere('isdischarged = 0')
					->andWhere('isstandby = 0')
					->andWhere('isarchived = 0')
					->andWhere('isstandbydelete = 0')
					->andWhere('admission_date < DATE(NOW())')
					->having($sqlHaving);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);

				//LEft join cu patient qupa mapping cu userid logininfo
				if($clientUsersArray[$userid]['onlyAssignedPatients'] == 1)
				{
					$patientwl->leftJoin("e.PatientQpaMapping q");
					$patientwl->andWhere("q.userid = '" . $userid . "'");
				}
				$patientidwlarray_all = $patientwl->fetchArray();

				$pat_array[] = '999999999';
				foreach($patientidwlarray_all as $k_pat => $v_pat)
				{
					$pat_array[] = $v_pat['ipid'];
				}



				//private patients
				$health = Doctrine_Query::create();
				$health->select("*")
					->from('PatientHealthInsurance')
					->whereIn('ipid', $pat_array)
					->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();

				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}
				//remove private patients
				$patientwl->andWhereNotIn('p.ipid', $privat_patient);
				$patientidwlarray = $patientwl->fetchArray();

				//process anlage 4 result array
				foreach($patientidwlarray as $k_pat_today => $v_pat_today)
				{
					$tabname = 'anlage';
					if($v_pat_today['sixWeeks0'] == 0)
					{ //today
						if(!in_array($v_pat_today['id'], $excluded_events[$tabname]))
						{
							$due_date = date("d.m.Y");
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . '</a>';
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date("d.m.Y");
							$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
							$key_start++;
						}
					}
					else
					{ //next week //no need to check any further cause all next 7 days are selected from query
						$curentDay = array_search("0", $v_pat_today, true);
						$curentDay = str_replace('sixWeeks', '', $curentDay);
						if($curentDay != "0" && !in_array($v_pat_today['id'], $excluded_events[$tabname]))
						{
							$due_date = date("Y-m-d", strtotime("+ " . $curentDay . " day"));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;

							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . '</a>';
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
							$key_start++;
						}
					}
				}
			}
			//ANLAGE 4a WL END


			if($wl_perms)
			{

				$sqlWeekDays = "";
				$sqlHaving = "";
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  `vollversorgung_date` ) , 28 ) AS fourWeeks" . $i . " ,";
					$sqlHaving .= "fourWeeks" . $i . " = 0 OR ";
				}
				$sqlHaving = substr($sqlHaving, 0, -4);

				$patientidwlarray_all = array();
				$patientwl = Doctrine_Query::create()
					->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, " . $sqlWeekDays . " e.epid")
					->from('PatientMaster as p')
					->where('isdelete = 0')
					->andWhere('isdischarged = 0')
					->andWhere('isstandby = 0')
					->andWhere('isarchived = 0')
					->andWhere('isstandbydelete = 0')
					->andWhere('vollversorgung = 1')
					->andWhere('vollversorgung_date < DATE(NOW())')
					->having($sqlHaving);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);

				//LEft join cu patient qupa mapping cu userid logininfo
				if($clientUsersArray[$userid]['onlyAssignedPatients'] == 1)
				{
					$patientwl->leftJoin("e.PatientQpaMapping q");
					$patientwl->andWhere("q.userid = '" . $userid . "'");
				}
				$patientidwlarray_all = $patientwl->fetchArray();

				$pat_array = array();
				$pat_array[] = '999999999';
				foreach($patientidwlarray_all as $k_pat => $v_pat)
				{
					$pat_array[] = $v_pat['ipid'];
				}

				//private patients
				$patientidwlarray = array();
				$health = Doctrine_Query::create();
				$health->select("*")
					->from('PatientHealthInsurance')
					->whereIn('ipid', $pat_array)
					->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();

				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}
				//remove private patients
				$patientwl->andWhereNotIn('p.ipid', $privat_patient);
				$patientidwlarray = $patientwl->fetchArray();

				//process anlage 4 result array
				foreach($patientidwlarray as $k_pat_today => $v_pat_today)
				{
					$tabname = 'anlage4awl';
					if($v_pat_today['fourWeeks0'] == 0)
					{ //today
						if(!in_array($v_pat_today['id'], $excluded_events[$tabname]))
						{
							$due_date = date("d.m.Y");
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4awl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . ' </a>';
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date("d.m.Y");
							$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
							$key_start++;
						}
					}
					else
					{ //next week //no need to check any further cause all next 7 days are selected from query
						$curentDay = array_search("0", $v_pat_today, true);
						$curentDay = str_replace('fourWeeks', '', $curentDay);
						if($curentDay != "0" && !in_array($v_pat_today['id'], $excluded_events[$tabname]))
						{
							$due_date = date("Y-m-d", strtotime("+ " . $curentDay . " day"));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;

							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4awl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . ' </a>';
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
							$key_start++;
						}
					}
				}
			}
			//ANLAGE 4a WL END
			//Assessment
			$client_patients_q = Doctrine_Query::create()
				->select('pm.ipid,ep.epid')
				->from('PatientMaster pm')
				->where('pm.isdelete = 0')
				->andWhere('pm.isstandbydelete = 0')
				->andWhere('pm.isstandby = 0')
				->andWhere('pm.isdischarged = 0')
				->leftJoin('pm.EpidIpidMapping ep')
				->andWhere('ep.clientid=' . $logininfo->clientid)
				->andWhere('ep.ipid=pm.ipid');
			$clipids = $client_patients_q->fetchArray();

			$client_ipids_arr[] = "'99999999'";
			foreach($clipids as $clipi)
			{
				$client_ipids_arr[] = $clipi['ipid'];
				$patientsEpidsFinal[$clipi['ipid']] = $clipi;
			}

			$assessment_events = Doctrine_Query::create()
				->select("*")
				->from('KvnoAssessment ')
				->whereIn('ipid', $client_ipids_arr)
				->andwhere("reeval between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
				->andWhere('iscompleted="1"');
			$assessment_events_arr = $assessment_events->fetchArray();

			$ipidass_arr[] = '99999999';
			foreach($assessment_events_arr as $dvisit)
			{
				$ipidass_arr[] = $dvisit['ipid'];
			}

			$pm = Doctrine_Query::create()
				->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date")
				->from('PatientMaster')
				->whereIn('ipid', $ipidass_arr);
			$patientsids = $pm->fetchArray();

			foreach($patientsids as $patient)
			{
				$patientsFinalIds[$patient['ipid']] = $patient;
			}
			
			
			
			$reassesment_array = array();
			if(!empty($assessment_events_arr))
			{
//				$tabname = 'asses';
				$tabname = 'reasses';
				foreach($assessment_events_arr as $key => $assess)
				{
					if(!in_array($assess['id'], $excluded_events[$tabname]))
					{
						$due_date = date('Y-m-d', strtotime($assess['reeval']));
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $assess['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($patientsEpidsFinal[$assess['ipid']]['EpidIpidMapping']['epid']) . " - " . ucfirst($patientsFinalIds[$assess['ipid']]['last_name']) . ", " . ucfirst($patientsFinalIds[$assess['ipid']]['first_name']);
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$reassesment_array[] = $assess['id']; 
						
						$key_start++;
					}
				}
			}
			
		
				
			//Assessment END
			//Re-Assessment
			$reassessmentweekall = array();
			$reassessmenttoday = array();
			$reassesmentprv = new Modules();
			$reass_mod = $reassesmentprv->checkModulePrivileges("56", $logininfo->clientid);

			//check reasessment module
			if($reass_mod)
			{
				//------------------------
				//Re-Assessment 14 days event
				//------------------------
				// get type of user, if Koordinator show all reassesments

				$usergroup = new Usergroup();
				$master_groups = array("6"); // Koordinator
				$usersgroups = $usergroup->getUserGroups($master_groups);
				if(count($usersgroups) > 0)
				{
					foreach($usersgroups as $group)
					{
						$groupsarray[] = $group['id'];
					}
				}
				$usrs = new User();
				$koord_array = $usrs->getuserbyGroupId($groupsarray, $clientid);

				foreach($koord_array as $user)
				{
					$koords[] = $user['id'];
				}


				$allpatk = Doctrine_Query::create()
					->select('pm.ipid')
					->from('PatientMaster pm')
					->where('pm.isdelete = 0 and pm.isstandbydelete = 0 and pm.isstandby = 0')
					->andWhere('isdischarged = 0')
					->leftJoin('pm.EpidIpidMapping ep')
					->andWhere('ep.clientid=' . $clientid)
					->andWhere('ep.ipid=pm.ipid');
				$allpatkoor = $allpatk->fetchArray();


				$re_ipidval_arr[] = '99999999';
				if(in_array($userid, $koords))
				{
					$comma = ",";
					$re_ipidval = "'0'";
					foreach($allpatkoor as $key => $val)
					{
						$re_ipidval_arr[] = $val['ipid'];
						$re_ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
					}
				}
				$reassessment_week = Doctrine_Query::create()
					->select("*")
					->from('KvnoAssessment ')
					->whereIn("ipid", $re_ipidval_arr)
					->andwhere("reeval between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
					->andWhere('iscompleted="1"');
				$reassessment_week_array = $reassessment_week->fetchArray();


				$ipids_reassessmet[] = '999999999';
				foreach($reassessment_week_array as $dvisit)
				{
					$ipids_reassessmet[] = $dvisit['ipid'];
				}

				$repatientsipidepid = Doctrine_Query::create()
					->select('ipid,epid')
					->from('EpidIpidMapping')
					->whereIn('ipid', $ipids_reassessmet);
				$repatientsepids = $repatientsipidepid->fetchArray();


				$repm = Doctrine_Query::create()
					->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid ")
					->from('PatientMaster')
					->whereIn('ipid', $ipids_reassessmet);
				$repatientsids = $repm->fetchArray();


				foreach($repatientsids as $patient)
				{
					$repatientsFinalIds[$patient['ipid']] = $patient;
				}

				foreach($repatientsepids as $pat)
				{
					$repatientsEpidsFinal[$pat['ipid']] = $pat;
				}

				if(!empty($reassessment_week_array))
				{
					$tabname = 'reasses';
					foreach($reassessment_week_array as $rekey => $reassess)
					{
						if(!in_array($reassess['id'], $excluded_events[$tabname]) && !in_array($reassess['id'],$reassesment_array))
						{
							$due_date = date('Y-m-d', strtotime($reassess['reeval']));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $reassess['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($repatientsEpidsFinal[$reassess['ipid']]['epid']) . " - " . ucfirst($repatientsFinalIds[$reassess['ipid']]['last_name']) . ", " . ucfirst($repatientsFinalIds[$reassess['ipid']]['first_name']);
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
				}
				//Re-assesment 28 days
				
				$reassessment_week_sec = Doctrine_Query::create()
				->select("*")
				->from('KvnoAssessment ')
				->whereIn("ipid", $re_ipidval_arr)
				->andwhere("DATE(DATE_ADD(reeval,INTERVAL 14 DAY)) between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
				->andWhere('iscompleted="1"');
				$reassessment_weeksec_array = $reassessment_week_sec->fetchArray();
				
				$ipids_reassessmet_sec[] = '999999999';
				foreach($reassessment_weeksec_array as $rassm)
				{
					$ipids_reassessmet_sec[] = $rassm['ipid'];
				}
				
				$repatientsipidepid_sec = Doctrine_Query::create()
				->select('ipid,epid')
				->from('EpidIpidMapping')
				->whereIn('ipid', $ipids_reassessmet_sec);
				$repatientsepids_sec = $repatientsipidepid_sec->fetchArray();
				
				$repm_sec = Doctrine_Query::create()
				->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid ")
				->from('PatientMaster')
				->whereIn('ipid', $ipids_reassessmet_sec);
				$repatientsids_sec = $repm_sec->fetchArray();
				
				
				foreach($repatientsids_sec as $patient)
				{
					$repatientsFinalIds_sec[$patient['ipid']] = $patient;
				}
				
				foreach($repatientsepids_sec as $pat)
				{
					$repatientsEpidsFinal_sec[$pat['ipid']] = $pat;
				}
			
				if(!empty($reassessment_weeksec_array))
				{
					$tabname = 'reasses';
					foreach($reassessment_weeksec_array as $rekey_sec => $reassess_sec)
					{
						if(!in_array($reassess_sec['id'], $excluded_events[$tabname]))
						{
							$due_date = date('Y-m-d',strtotime("+14 days",strtotime($reassess_sec['reeval'])));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $reassess_sec['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($repatientsEpidsFinal_sec[$reassess_sec['ipid']]['epid']) . " - " . ucfirst($repatientsFinalIds_sec[$reassess_sec['ipid']]['last_name']) . ", " . ucfirst($repatientsFinalIds_sec[$reassess_sec['ipid']]['first_name']);
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
				}
			}
			//Re-Assessment END
			//Custom events
			$team_event_types = array(
				'12' => 'Ferien: ',
				'13' => 'Team Sitzungen: ',
				'14' => 'Fortbildung: ',
				'15' => 'Supervision: ',
				'16' => 'Kongress: ',
				'17' => 'Rufbereitschaft: ',
				'18' => 'Urlaub / Vertretung: ',
				'20' => 'Einsatzleitung: ',
				'21' => 'Termin: ',
				'22' => 'Freier Termin: ',
			);

			//ISPC-311 - comments - show all team events
			$team_events = Doctrine_Query::create()
				->select("*")
				->from('TeamCustomEvents')
				->where('clientid = ?',$clientid)
				->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'");
			$team_events_res = $team_events->fetchArray();


			$shown_todos[] = '999999999';
			foreach($team_events_res as $k_team_events => $v_team_events)
			{

				if($v_team_events['user_id'] > '0' && $v_team_events != $userid && array_key_exists($v_team_events['userid'], $client_users_arr))
				{
					$user_details = $client_users_arr[$v_team_events['userid']]['user_title'] . ' ' . $client_users_arr[$v_team_events['userid']]['last_name'] . ', ' . $client_users_arr[$v_team_events['userid']]['first_name'];
				}
				else
				{
					$user_details = '';
				}

//					$tabname = 'team_events';
				$tabname = 'custom_team_event';
				if(!in_array($v_team_events['id'], $excluded_events[$tabname]))
				{
					$due_date = date('Y-m-d', strtotime($v_team_events['endDate']));
					$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
					$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_team_events['id'];
					$master_data[strtotime($due_date)][$key_start]['event_type'] = $team_event_types[$v_team_events['eventType']];
					$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_team_events['eventTitle'];
					$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
					$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
					$key_start++;

					$shown_todos[] = $v_team_events['id'];
				}
			}

			//usergroup team events
			$m_group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('id = ?',$groupid );
			$group_details = $m_group->fetchArray();

			if($group_details[0]['indashboard'] == "1")
			{
//ISPC-311 - comments - all custom events should be visible by every user of client
//				//get curent user group user ids
//				$group_users = Doctrine_Query::create()
//					->select('*')
//					->from('User')
//					->where('groupid = "' . $groupid . '"')
//					->andWhere('clientid = "' . $clientid . '"');
//
//				$gr_users_arr = $group_users->fetchArray();
//
//				$gr_users[] = '999999999';
//				foreach($gr_users_arr as $k_gr_user => $v_gr_user)
//				{
//					//remove own id from grouping (grouped team events and own team events have same label..now)
//					if($v_gr_user['id'] != $userid)
//					{
//						$gr_users[] = $v_gr_user['id'];
//					}
//				}
//
//				$team_events = Doctrine_Query::create()
//					->select("*")
//					->from('TeamCustomEvents')
//					->where('clientid ="' . $clientid . '"')
//					->andWhereIn('userid', $gr_users)
//					->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'");
//				$team_events_res = $team_events->fetchArray();
//
//
//				foreach($team_events_res as $k_team_events => $v_team_events)
//				{
//
//					if($v_team_events['user_id'] > '0' && $v_team_events != $userid && array_key_exists($v_team_events['userid'], $client_users_arr))
//					{
//						$user_details = $client_users_arr[$v_team_events['userid']]['last_name'] . ', ' . $client_users_arr[$v_team_events['userid']]['first_name'];
//					}
//					else
//					{
//						$user_details = '';
//					}
//
////					$tabname = 'team_events';
//					$tabname = 'custom_team_event';
//					if(!in_array($v_team_events['id'], $excluded_events[$tabname]))
//					{
//						$due_date = date('Y-m-d', strtotime($v_team_events['endDate']));
//						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
//						$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_team_events['id'];
//						$master_data[strtotime($due_date)][$key_start]['event_type'] = $team_event_types[$v_team_events['eventType']];
//						$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_team_events['eventTitle'];
//						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
//						$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
//						$key_start++;
//					}
//				}
				//get doctor custom events
				$doc_custom_events = new DoctorCustomEvents();
				$doc_custom_events_arr = $doc_custom_events->get_doc_team_all_custom_events($clientid);
				foreach($doc_custom_events_arr as $k_doc_event => $v_doc_event)
				{

					if($v_doc_event['user_id'] > '0' && $v_doc_event != $userid && array_key_exists($v_doc_event['userid'], $client_users_arr))
					{
						$user_details = $client_users_arr[$v_doc_event['userid']]['user_title'] . ' ' . $client_users_arr[$v_doc_event['userid']]['last_name'] . ', ' . $client_users_arr[$v_doc_event['userid']]['first_name'];
					}
					else
					{
						$user_details = '';
					}

//					$tabname = 'team_events';
					$tabname = 'custom_doctor_event_team';
					if(!in_array($v_doc_event['id'], $excluded_events[$tabname]))
					{
						$doc_due_date = date('Y-m-d', strtotime($v_doc_event['endDate']));
						$master_data[strtotime($doc_due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($doc_due_date)][$key_start]['event_id'] = $v_doc_event['id'];
						$master_data[strtotime($doc_due_date)][$key_start]['event_type'] = $team_event_types[$v_doc_event['eventType']];
						$master_data[strtotime($doc_due_date)][$key_start]['event_title'] = $v_doc_event['eventTitle'];
						$master_data[strtotime($doc_due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($doc_due_date));
						$master_data[strtotime($doc_due_date)][$key_start]['todo_user'] = $user_details;
						$key_start++;
					}
				}
			}

			//get custom team events if profile settings allows it
			if($user_c_details[0]['show_custom_events'] == '1')
			{
				$doctor_event_types = array('10' => 'Termin: ', '11' => 'Notiz: ');

				$team_custom_event = Doctrine_Query::create()
					->select("*")
					->from('TeamCustomEvents')
					->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
					->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'");
				$team_custom_event_res = $team_custom_event->fetchArray();

//				Process team custom events (curent user added events)
				foreach($team_custom_event_res as $k_team_event => $v_team_event)
				{
					$tabname = 'custom_team_event';
					if(!in_array($v_team_event['id'], $excluded_events[$tabname]) && !in_array($v_team_event['id'], $shown_todos))
					{
						$due_date = date('Y-m-d', strtotime($v_team_event['endDate']));
						$master_data[strtotime($due_date)][$key_start]['tabname'] = 'custom_team_event';
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_team_event['id'];
						$master_data[strtotime($due_date)][$key_start]['event_type'] = $team_event_types[$v_team_event['eventType']];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_team_event['eventTitle'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$key_start++;
					}
				}

				// doctor custom events
				$doc_custom_event = Doctrine_Query::create()
					->select("*")
					->from('DoctorCustomEvents')
					->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
					->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
					->andWhere('viewForAll != "1"');

				$doc_custom_event_res = $doc_custom_event->fetchArray();

				//get doctor event ipids
				$doc_ipids[] = '99999999';
				foreach($doc_custom_event_res as $kdoc_event => $vdoc_event)
				{
					$doc_ipids[] = $vdoc_event['ipid'];
				}

				$doc_ipid_epid = Doctrine_Query::create()
					->select('ipid,epid')
					->from('EpidIpidMapping')
					->whereIn('ipid', $doc_ipids);
				$doc_ipid_epid_res = $doc_ipid_epid->fetchArray();


				$doc_cust_evts_pat = Doctrine_Query::create()
					->select("id,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid ")
					->from('PatientMaster')
					->whereIn('ipid', $doc_ipids);
				$doctors_ipid_pats = $doc_cust_evts_pat->fetchArray();
				foreach($doctors_ipid_pats as $k_doc => $v_doc)
				{
					$doctors_cust_ev_patients[$v_doc['ipid']] = $v_doc;
				}

				foreach($doc_ipid_epid_res as $k_doc_epid => $v_doc_epid)
				{
					$doc_ipids_epids[$v_doc_epid['ipid']]['epid'] = $v_doc_epid['epid'] . ' - ' . $doctors_cust_ev_patients[$v_doc_epid['ipid']]['last_name'] . ', ' . $doctors_cust_ev_patients[$v_doc_epid['ipid']]['first_name'];
				}

				//process doc custom events
				foreach($doc_custom_event_res as $k_doc_event => $v_doc_event)
				{
					$tabname = 'custom_doctor_event';
					if(!in_array($v_doc_event['id'], $excluded_events[$tabname]))
					{
						$due_date = date('Y-m-d', strtotime($v_doc_event['endDate']));

						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_doc_event['id'];
						$master_data[strtotime($due_date)][$key_start]['event_type'] = $doctor_event_types[$v_doc_event['eventType']];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_doc_event['eventTitle'];
						$master_data[strtotime($due_date)][$key_start]['event_patient'] = $doc_ipids_epids[$v_doc_event['ipid']]['epid'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$key_start++;
					}
				}
			}

			//TODO
			//Client Users
			$this->view->userid = $userid;
			$this->view->groupid = $groupid;
			$this->view->user_type = $user_type;
			
			$users2groups[] = '99999999';
			foreach($client_users as $k_user => $v_user)
			{
				$todo_users[$v_user['id']] = $v_user;
				$client_users[$v_user['id']] = $v_user;
				$users2groups[$v_user['id']] = $v_user['groupid'];
				$groups2users[$v_user['groupid']][] = $v_user['id'];
			}

			$current_user_group_asignees = $groups2users[$groupid];
			$current_user_group_asignees[] = '999999999';
			$current_user_group_asignees[] = '9999999';

			$this->view->group2users = $groups2users;
			if($_REQUEST['dbgz'])
			{
				print_r($groupid);
				print_r($groups2users);
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

			//todos
			
			$all_client_patients_q = Doctrine_Query::create()
			->select('pm.ipid,ep.epid')
			->from('PatientMaster pm')
			->where('pm.isdelete = 0')
			->leftJoin('pm.EpidIpidMapping ep')
			->andWhere('ep.clientid=' . $logininfo->clientid)
			->andWhere('ep.ipid=pm.ipid');
			$all_clipids = $all_client_patients_q->fetchArray();
			
			$all_client_ipids_arr[] = "99999999";
			foreach($all_clipids as $clipi)
			{
			    $all_client_ipids_arr[] = $clipi['ipid'];
			}
			
			$todo = Doctrine_Query::create()
				->select("*")
				->from('ToDos')
				->where('client_id="' . $clientid . '"')
				->andWhere('isdelete="0"')
				->andWhereIn('ipid',$all_client_ipids_arr)
				->andWhere('iscompleted="0"')
				->orderBy('create_date DESC');
			if($user_type != 'SA')
			{
				if(!in_array($groupid, $coord_groups))
				{
					$todo->andWhere('triggered_by !="system"');
				}

				if($groupid > 0)
				{
					$sql_group = ' OR group_id = "' . $groupid . '"';
				}
				$todo->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
			}

			$todo_array = $todo->fetchArray();

			$todo_ipids[] = '99999999';
			$receipt_ids[] = '999999999';
			if(count($todo_array) > 0)
			{
				//first todo foreach to gather all ipids to avoid 10 second loading as in old todo!
				//here ... catch all receipts ids too .. in this way we have the receipt creator //only for "triggered_by = newreceipt_1 and newreceipt_2"
				$triggered_by_arr = "";
				foreach($todo_array as $k_todo_d => $v_todo_d)
				{
					$todo_ipids[] = $v_todo_d['ipid'];
					
					if($v_todo_d['triggered_by'] == "newreceipt_1")
					{
						$print_receipt_ids[] = $v_todo_d['record_id'];
					}
					else if($v_todo_d['triggered_by'] == "newreceipt_2")
					{
						$fax_receipt_ids[] = $v_todo_d['record_id'];
					}
					

				}
				
				//query to get all receipts involved
//				$receipts_creators = Receipts::get_multiple_receipts_creators($receipt_ids, $clientid);
				$receipt_creators_print = Receipts::get_multiple_receipt_print_assign_creators($print_receipt_ids, $clientid);
				$receipt_creators_fax = Receipts::get_multiple_receipt_fax_assign_creators($fax_receipt_ids, $clientid);
				
//				print_r($receipt_creators_print);
//				print_r($receipt_creators_fax);
//				exit;
				//second todo foreach to append data to master_data
				$tabname = 'todo';
				$triggered_by_arr = array();
				$triggered_by_arr[0] = "";
				$triggered_by_arr[1] = "";
				
				
				foreach($todo_array as $k_todo => $v_todo)
				{
					if(!in_array($v_todo['id'], $excluded_events[$tabname]))
					{
						if($v_todo['record_id'] != '0')
						{
							if(($v_todo['triggered_by'] == "newreceipt_1" && !empty($receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']])))
							{
								$creator_details = $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['user_title'] . ' ' . $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['last_name'] . ', ' . $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['first_name'];
							}
							else if(($v_todo['triggered_by'] == "newreceipt_2"  && !empty($receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']])))
							{
								$creator_details = $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['user_title'] . ' ' . $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['last_name'] . ', ' . $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['first_name'];
							}
							else
							{
								$creator_details = '';
							}
						}
						
						if($v_todo['user_id'] > '0')
						{
							$user_details = $todo_users[$v_todo['user_id']]['user_title'] . ' ' . $todo_users[$v_todo['user_id']]['last_name'] . ', ' . $todo_users[$v_todo['user_id']]['first_name'];
						}
						else
						{
							$user_details = '';
						}
						$todo_ipids[] = $v_todo['ipid'];

						if($v_todo['triggered_by'] != 'system_medipumps')
						{
							if(($v_todo['group_id'] == $groupid && $v_todo['group_id'] != '0') || $v_todo['user_id'] == $userid)
							{
								$triggered_by_arr[$key_start] = explode("-",$v_todo['triggered_by']);
								$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
								
								
								
														
                                if($triggered_by_arr[$key_start][0] == "medacknowledge")
                                {
    							    $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'medacknowledge';
    							    $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
    							    $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
                                    if(strlen($triggered_by_arr[$key_start][1]) > 0){
                                        $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = $triggered_by_arr[$key_start][1];
    							    }
    								$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
                                }   
														
                                elseif($triggered_by_arr[$key_start][0] == "pumpmedacknowledge")
                                {
    							    $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'pumpmedacknowledge';
    							    $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
    							    $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
    								$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
                                    if(strlen($triggered_by_arr[$key_start][1]) > 0){
                                        $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = $triggered_by_arr[$key_start][1];
    							    }
                                }   
                                else
                                {
    							    $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = '0';
    								$master_data[strtotime($due_date)][$key_start]['medical_change'] = '0';
    							    $master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '0';
    								$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
    								$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
    							}
								
								$master_data[strtotime($due_date)][$key_start]['alt_id'] = $v_todo['record_id'];
								$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
								$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
								$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
								$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
								$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
								$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
								$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
								$master_data[strtotime($due_date)][$key_start]['receipt_creator_user'] = $creator_details;
								$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
								$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
								$key_start++;
							}
						}
						else if($v_todo['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_todo['group_id'] (koord)
						{
							$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
							
							$triggered_by_arr[$key_start] = explode("-",$v_todo['triggered_by']);
							
                            if($triggered_by_arr[$key_start][0] == "medacknowledge")
                            {
							    $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'medacknowledge';
    							$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
							    $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
								$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
                                if(strlen($triggered_by_arr[$key_start][1]) > 0){
                                    $master_data[strtotime($due_date)][$key_start]['drugplan_id'] = $triggered_by_arr[$key_start][1];
							    }
                            }   
                            elseif($triggered_by_arr[$key_start][0] == "pumpmedacknowledge")
                            {
							    $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'pumpmedacknowledge';
    							$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
							    $master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
								$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
                                if(strlen($triggered_by_arr[$key_start][1]) > 0){
                                    $master_data[strtotime($due_date)][$key_start]['cocktail_id'] = $triggered_by_arr[$key_start][1];
							    }
                            }   
                            else
                            {
							    $master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = '0';
    							$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '0';
								$master_data[strtotime($due_date)][$key_start]['medical_change'] = '0';
								$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
								$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
							} 
								
							$master_data[strtotime($due_date)][$key_start]['alt_id'] = $v_todo['record_id'];
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
							$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
							$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
							$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
							$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
				}
			}

			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
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

			$patients = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn("p.ipid", $todo_ipids)
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$patients_res = $patients->fetchArray();
			foreach($patients_res as $k_pat_todo => $v_pat_todo)
			{
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']] = $v_pat_todo;
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_todo['EpidIpidMapping']['epid']);
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_todo['id']);
			}


			//TODO END
			//SGB XI START
			$show_to_group_users = false;
			$event_tabname = 'sgbxi';
			$sgbxi_events = Doctrine_Query::create()
				->select("*")
				->from('DashboardEvents')
				->where('client_id="' . $clientid . '"')
				->andWhere('tabname="' . $event_tabname . '" ')
				->andWhere('isdelete="0"')
				->andWhere('iscompleted="0"')
				->orderBy('create_date DESC');

			if($user_type != 'SA')
			{
				if($show_to_group_users)
				{

					if(!in_array($groupid, $coord_groups))
					{
						$sgbxi_events->andWhere('triggered_by !="system"');
					}

					if($groupid > 0)
					{
						$sql_group = ' OR group_id = "' . $groupid . '"';
					}
					$sgbxi_events->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
				}
				else
				{
					$sgbxi_events->andWhere('user_id = "' . $userid . '" ');
				}
			}


			$sgbxi_events_array = $sgbxi_events->fetchArray();

			$sgbxi_events_ipids[] = '99999999';
			if(count($sgbxi_events_array) > 0)
			{
				//first event foreach to gather all ipids to avoid 10 second loading as in old events!
				foreach($sgbxi_events_array as $k_s_d => $v_sgbxi_events_d)
				{
					$sgbxi_events_ipids[] = $v_sgbxi_events_d['ipid'];
				}


				//second sgbxi_events foreach to append data to master_data
				$tabname = 'sgbxi';

				foreach($sgbxi_events_array as $k_sgbxi_events => $v_sgbxi_events)
				{
					if(!in_array($v_sgbxi_events['id'], $excluded_events[$tabname]))
					{
						if($v_sgbxi_events['user_id'] > '0')
						{
							$sgbxi_user_details = $client_users[$v_sgbxi_events['user_id']]['user_title'] . ' ' . $client_users[$v_sgbxi_events['user_id']]['last_name'] . ', ' . $client_users[$v_sgbxi_events['user_id']]['first_name'];
						}
						else
						{
							$sgbxi_user_details = '';
						}
						$sgbxi_events_ipids[] = $v_sgbxi_events['ipid'];

						if($v_sgbxi_events['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_sgbxi_events['group_id'] (koord)
						{
							$due_date = date('Y-m-d', strtotime($v_sgbxi_events['until_date']));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_sgbxi_events['ipid'];
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_sgbxi_events['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_sgbxi_events['title'];
							$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_sgbxi_events['user_id'];
							$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_sgbxi_events['group_id'];
							$master_data[strtotime($due_date)][$key_start]['todo_user'] = $sgbxi_user_details;
							$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_sgbxi_events['triggered_by'];
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
						else
						{
							$due_date = date('Y-m-d', strtotime($v_sgbxi_events['until_date']));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_sgbxi_events['ipid'];
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_sgbxi_events['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_sgbxi_events['title'];
							$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_sgbxi_events['user_id'];
							$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_sgbxi_events['group_id'];
							$master_data[strtotime($due_date)][$key_start]['todo_user'] = $sgbxi_user_details;
							$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_sgbxi_events['triggered_by'];
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
				}
			}

			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
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

			$sgbxi_patients_q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn("p.ipid", $sgbxi_events_ipids)
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$sgbxi_patients_res = $sgbxi_patients_q->fetchArray();
			foreach($sgbxi_patients_res as $k_pat_sgbxi_events => $v_pat_sgbxi_events)
			{
				$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']] = $v_pat_sgbxi_events;
				$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_sgbxi_events['EpidIpidMapping']['epid']);
				$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_sgbxi_events['id']);
			}
			//SGB XI  END
			//Patient birthday

			$notifications = new Notifications();
			$user_notification_settings = $notifications->get_notification_settings($userid);


			//excluded evnts
			$clist = Doctrine_Query::create()
				->select("*")
				->from('DashboardActionsDone')
				->where('client = "' . $clientid . '"')
				->andWhere('user = "' . $userid . '"')
				->andWhere("tabname = 'patient_birthday'")
				/* ->andWhere("create_date <= '" . date('Y-m-d H:i:s', time()) . "'") */
				->andWhere("YEAR(create_date) = '" . date('Y', time()) . "'");
			$client_excluded_events = $clist->fetchArray();

			if($client_excluded_events)
			{
				foreach($client_excluded_events as $k_excluded => $v_excluded)
				{
					$excluded_birth[$v_excluded['tabname']][] = $v_excluded['event'];
					$excluded_birth[$v_excluded['tabname']] = array_unique($excluded_events[$v_excluded['tabname']]);
					$excluded_birth['excluded_date'][$v_excluded['event']] = $v_excluded['create_date'];
				}
			}

			$sql = "AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA' && $clone === false)
			{
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
			}

			if($user_notification_settings[$userid]['dashboard_display_patbirthday'] == 'assigned')
			{
				$fdoc = Doctrine_Query::create()
					->select("*,q.userid, e.epid, e.ipid")
					->from('EpidIpidMapping e')
					->andWhere('e.epid!=""')
					->leftJoin('e.PatientQpaMapping q')
					->where('e.epid = q.epid')
					->andWhere("q.userid = ?", $userid)
					->andWhere('e.clientid = ?', $clientid);
				$doc_assigned_patients = $fdoc->fetchArray();


				$asigned_patients[] = '999999999';
				foreach($doc_assigned_patients as $doc_patient)
				{
					foreach($doc_patient['PatientQpaMapping'] as $k_doc => $v_doc)
					{
						$users_patients[$v_doc['userid']][] = $doc_patient['ipid'];
						$asigned_patients[] = $doc_patient['ipid'];
					}
				}

				//2. Get patients with birthday in next 7 days
				//2.1 Patients asigned to users wich must receive the messajes

				$patient_dasboard = Doctrine_Query::create()
					->select("p.ipid,p.birthd," . $sql)
					->from('PatientMaster as p')
					->where('isdelete = 0')
					->andWhereIn('ipid', $asigned_patients)
					->andWhere('isdischarged = 0')
					->andWhere('isstandby = 0')
					->andWhere('isarchived = 0')
					->andWhere('isstandbydelete = 0')
					->andWhere("date_format( `birthd` , '%m-%d' ) BETWEEN date_format(now() ,'%m-%d') AND date_format(date_add( now() , INTERVAL 7 DAY ) , '%m-%d' )");
				$patient_dashboard_assigned = $patient_dasboard->fetchArray();
				//print_r($patient_dashboard_assigned);exit;
				foreach($patient_dashboard_assigned as $key_pat => $val_pat)
				{
					$patients_birthdays[$val_pat['ipid']] = date('d.m.Y', strtotime($val_pat['birthd']));
				}

				foreach($patient_dashboard_assigned as $key_pat_birth => $val_pat_birth)
				{
					$patient_birthday = date('d.m.Y', strtotime($val_pat_birth['birthd']));
					$patbirthd_arr = explode(".", $patient_birthday);
					$due_date = date("Y-m-d", mktime(0, 0, 0, $patbirthd_arr[1], $patbirthd_arr[0], date("Y")));
					$tabname = 'patient_birthday';
					if(!in_array($val_pat_birth['id'], $excluded_birth[$tabname]))
					{
						//$due_date = date("d.m.Y");
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $val_pat_birth['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $val_pat_birth['last_name'] . ', ' . $val_pat_birth['first_name'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
						$key_start++;
					}
				}
			}
			//print_r($patient_dashboard_assigned);exit;
			//2.2 All patients wich have birtday in next 7 days
			else if($user_notification_settings[$userid]['dashboard_display_patbirthday'] == 'all')
			{
				$patient_dasboard = Doctrine_Query::create()
					->select("p.ipid,p.birthd,ep.clientid," . $sql)
					->from('PatientMaster as p')
					->where('p.isdelete = 0')
					->andWhere('p.isdischarged = 0')
					->andWhere('p.isstandby = 0')
					->andWhere('p.isarchived = 0')
					->andWhere('p.isstandbydelete = 0')
					->andWhere("date_format( `birthd` , '%m-%d' ) BETWEEN date_format(now() ,'%m-%d') AND date_format(date_add( now() , INTERVAL 7 DAY ) , '%m-%d' )")
					->leftJoin('p.EpidIpidMapping ep')
					->andWhere('ep.clientid = ' . $clientid)
					->andWhere('ep.ipid=p.ipid');
				$patient_dashboard_all = $patient_dasboard->fetchArray();

				//print_r($patient_dashboard_all)	;exit;

				foreach($patient_dashboard_all as $key_pat_birth => $val_pat_birth)
				{
					$patient_birthday = date('d.m.Y', strtotime($val_pat_birth['birthd']));
					$patbirthd_arr = explode(".", $patient_birthday);
					//print_r($patient_birthday);
					$due_date = date("Y-m-d", mktime(0, 0, 0, $patbirthd_arr[1], $patbirthd_arr[0], date("Y")));
					//print_r($due_date);exit;
					$tabname = 'patient_birthday';
					if(!in_array($val_pat_birth['id'], $excluded_birth[$tabname]))
					{
						//$due_date = date("d.m.Y");
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $val_pat_birth['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $val_pat_birth['last_name'] . ', ' . $val_pat_birth['first_name'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = $due_date;
						$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
						$key_start++;
					}
				}
			}
			//End Patient birthday
			//==================================================================================================
			$action_last_label = $dashboard_labels->getActionsLastLabel();
			$action_last_label['custom_doctor_event_team'] = $action_last_label['custom_team_event'];

			$labels_f['0'] = $this->view->translate('select');
			foreach($action_last_label as $k_act_label => $v_act_label)
			{
				if($k_act_label != "custom_doctor_event_team")
				{
					$labels_f[$k_act_label] = $v_act_label['name'];
				}
			}


			if($_REQUEST['label_filter'] && $_REQUEST['label_filter'] != '0' && $_REQUEST['label_filter'] != 'undefined') //0=all
			{
				$this->view->filter = $_REQUEST['label_filter'];
				foreach($master_data as $k_master_data_date => $v_master_data)
				{
					foreach($v_master_data as $k_data => $v_data)
					{
						if($v_data['tabname'] == $_REQUEST['label_filter'] || ($_REQUEST['label_filter'] == "custom_team_event" && ($v_data['tabname'] == "custom_doctor_event_team" || $v_data['tabname'] == "custom_team_event")))
						{
							$master_data_filtered[$k_master_data_date][$k_data] = $v_data;
						}
					}
				}

				$master_data = array();
				$master_data = $master_data_filtered;
			}

			//LIMIT & SORT MASTER DATA
			$user_dash_limit = $user_c_details[0]['dashboard_limit'];

			if($user_dash_limit != '0')
			{
				$incr = 1;
				foreach($master_data as $k_tabname => $v_events)
				{
					foreach($v_events as $k_event => $v_event)
					{

						if($incr <= $user_dash_limit)
						{
							$master_data_final[$k_tabname][$k_event] = $v_event;
						}
						$incr++;
					}
				}
			}
			else
			{
				$master_data_final = $master_data;
			}

			if($_REQUEST['sort_order'] == 'desc')
			{
				krsort($master_data_final);
			}
			else
			{
				ksort($master_data_final);
			}
		
			$sort_arr = array('asc' => $this->view->translate('asc_sort'), 'desc' => $this->view->translate('desc_sort'));
			$this->view->date_sort = $this->view->formSelect("date_sort", $_REQUEST['sort_order'], '', $sort_arr);
			$this->view->label_filter = $this->view->formSelect("label_filter", $_REQUEST['label_filter'], '', $labels_f);
			$this->view->dasboard_events = $master_data_final;
			$this->view->action_label = $action_last_label;
			$this->view->todo_patients = $todo_patients;
			$this->view->sgbxi_events_patients = $sgbxi_events_patients;
			/* ------------ BOX - "User Dashboard" END---------------- */
		}
		
		public function dashboardhistoryoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$user_type = $logininfo->usertype;
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$hidemagic = Zend_Registry::get('hidemagic');
		
			setlocale(LC_ALL, 'de_DE.UTF-8');
		
			$done_events = new DashboardActionsDone();
			$labels_form = new Application_Form_DashboardActions();
			$wlprevileges = new Modules();
			$this->_helper->layout->setLayout('layout_ajax');
		
			//load excluded events
			$history_events = $done_events->getClientDashboardActions($clientid, false);
			$this->view->history_events = $history_events;
			$history_events['anlage']['ids'][] = '99999999';
			$history_events['asses']['ids'][] = '99999999';
			$history_events['reasses']['ids'][] = '99999999';
			$history_events['custom_team_event']['ids'][] = '99999999';
			$history_events['custom_doctor_event']['ids'][] = '99999999';
			$history_events['custom_doctor_event_team']['ids'][] = '99999999';
			$history_events['todo']['ids'][] = '99999999';
			$history_events['sgbxi']['ids'][] = '99999999';
			$history_events['patient_birthday']['ids'][] = '99999999';
		
			$user = Doctrine_Query::create()
			->select("*")
			->from('User')
			->where('clientid = ' . $clientid . ' or usertype="SA"')
			->andWhere('isactive=0 and isdelete = 0')
			->orderBy('last_name ASC');
			$userarray = $user->fetchArray();
		
			if(count($userarray) > 0)
			{
				foreach($userarray as $u_key => $u_value)
				{
					$client_users_arr[$u_value['id']] = $u_value;
				}
			}
			$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
			$modules = new Modules();
		
			if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
			{
				$this->view->acknowledge_func = "1";
				if(in_array($userid,$approval_users)){
					$this->view->approval_rights = "1";
				} else{
					$this->view->approval_rights = "0";
				}
		
			}
		
			else
			{
				$this->view->acknowledge_func = "0";
			}
		
				
				
			//anlage module
			$wl_perms = $wlprevileges->checkModulePrivileges("51", $clientid);
		
			if($wl_perms && count($history_events['anlage']['ids']) > 0)
			{
				$patientwl = Doctrine_Query::create()
				->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, e.epid")
				->from('PatientMaster as p')
				->where('isdelete = 0')
				->andWhere('isdischarged = 0')
				->andWhere('isstandby = 0')
				->andWhere('isarchived = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere('admission_date < DATE(NOW())')
				->andWhereIn('id', $history_events['anlage']['ids']);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
		
				//LEft join cu patient qupa mapping cu userid logininfo
				if($client_users_arr[$userid]['onlyAssignedPatients'] == 1)
				{
					$patientwl->leftJoin("e.PatientQpaMapping q");
					$patientwl->andWhere("q.userid = '" . $userid . "'");
				}
				$patientidwlarray_all = $patientwl->fetchArray();
		
				foreach($patientidwlarray_all as $k_pat => $v_pat)
				{
					$anlage_events[$v_pat['patientId']] = $v_pat;
				}
			}
		
			//team_custom_events
			$team_custom_event = Doctrine_Query::create()
			->select("*")
			->from('TeamCustomEvents')
			->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
			->andWhereIn('id', $history_events['custom_team_event']['ids']);
			$team_custom_event_res = $team_custom_event->fetchArray();
		
		
			foreach($team_custom_event_res as $k_team => $v_team)
			{
				$team_cust_events[$v_team['id']] = $v_team;
			}
		
			//custom doctor
			$doc_custom_event = Doctrine_Query::create()
			->select("*")
			->from('DoctorCustomEvents')
			->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
			->andWhereIn('id', $history_events['custom_doctor_event']['ids']);
			$doc_custom_event_res = $doc_custom_event->fetchArray();
		
		
			foreach($doc_custom_event_res as $k_doc => $v_doctor)
			{
				$doctor_cust_events[$v_doctor['id']] = $v_doctor;
			}
			//custom doctor team
			$doc_custom_event_team = Doctrine_Query::create()
			->select("*")
			->from('DoctorCustomEvents')
			->where("clientid='" . $clientid . "'")
			->andWhereIn('id', $history_events['custom_doctor_event_team']['ids']);
			$doc_custom_event_team_res = $doc_custom_event_team->fetchArray();
		
			foreach($doc_custom_event_team_res as $k_doc => $v_doctor)
			{
				$doctor_cust_events_team[$v_doctor['id']] = $v_doctor;
			}
		
			//todo
			$todo = Doctrine_Query::create()
			->select("*")
			->from('ToDos')
			->where('client_id="' . $clientid . '" and isdelete="0"')
			->andWhereIn('id', $history_events['todo']['ids']);
		
			$todo_array = $todo->fetchArray();
		
			foreach($todo_array as $k_todo => $v_todo)
			{
				$todos_arr[$v_todo['id']] = $v_todo;
			}
		
			//sgb xi
			$event_tabname = "sgbxi";
			$sgbxi = Doctrine_Query::create()
			->select("*")
			->from('DashboardEvents')
			->where('client_id="' . $clientid . '" and isdelete="0"')
			->andWhere('tabname = "' . $event_tabname . '" ')
			->andWhereIn('id', $history_events['sgbxi']['ids']);
			$sgbxi_array = $sgbxi->fetchArray();
		
			foreach($sgbxi_array as $k_sgbxi => $v_sgbxi)
			{
				$sgbxi_arr[$v_sgbxi['id']] = $v_sgbxi;
			}
		
			$assessment_events = Doctrine_Query::create()
			->select("*")
			->from('KvnoAssessment ')
			->whereIn('id', $history_events['asses']['ids'])
			->andWhere('iscompleted="1"');
			$assessment_events_arr = $assessment_events->fetchArray();
		
			$assessments_ipids[] = '99999999';
			foreach($assessment_events_arr as $k_asses_arr => $v_asses_arr)
			{
				$assessments_ipids[] = $v_asses_arr['ipid'];
				$assessment_arr[$v_asses_arr['id']] = $v_asses_arr;
			}
		
			//assessment patients
			$assessment_pat = Doctrine_Query::create()
			->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, e.epid")
			->from('PatientMaster as p')
			->andWhereIn('ipid', $assessments_ipids)
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
			$assessment_pat_res = $assessment_pat->fetchArray();
		
			foreach($assessment_pat_res as $k_assessment => $v_assessment)
			{
				$asses_pat_det[$v_assessment['ipid']] = $v_assessment;
			}
		
			//patient birthday
		
			$notifications = new Notifications();
			$user_notification_settings = $notifications->get_notification_settings($userid);
		
			if(count($history_events['patient_birthday']['ids']) > 0)
			{
				$patients_birth = Doctrine_Query::create()
				->select("p.ipid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, birthd, e.epid")
				->from('PatientMaster as p')
				->andWhereIn('id', $history_events['patient_birthday']['ids']);
				$patients_birth->leftJoin("p.EpidIpidMapping e");
				$patients_birth->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
				$patients_birth_arr = $patients_birth->fetchArray();
		
		
				foreach($patients_birth_arr as $k_pat => $v_pat)
				{
					$birth_ipids[] = $v_pat['ipid'];
					$patients_birthds[$v_pat['ipid']] = $v_pat;
					$birthds_events[$v_pat['id']] = $v_pat;
				}
				//print_r($patients_birthds);exit;
			}
			$i = 1;
			$todos_ipids[] = '99999999';
			$sgbxi_ipids[] = '99999999';
		
			$triggered_by_arr = array();
				
			foreach($history_events as $tab_name => $v_history_events)
			{
				foreach($v_history_events['details'] as $k_event => $v_event)
				{
					$create_date = date('Y-m-d', strtotime($v_event['create_date']));
					if($v_event['done_date'] != '0000-00-00 00:00:00')
					{
						$due_date = date('d.m.Y', strtotime($v_event['done_date']));
					}
					else
					{
						$due_date = '-';
					}
		
					$master_data[strtotime($create_date)][$i]['id'] = $v_event['id'];
					$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
		
		
					//anlage
					if($tab_name == 'anlage')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_event['event']) . '">' . $anlage_events[$v_event['event']]['last_name'] . ', ' . $anlage_events[$v_event['event']]['first_name'] . '</a>';
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					//team event
					if($tab_name == 'custom_team_event')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $team_cust_events[$v_event['event']]['eventTitle'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					//doctor event
					if($tab_name == 'custom_doctor_event')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $doctor_cust_events[$v_event['event']]['eventTitle'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					//doctor event team
					if($tab_name == 'custom_doctor_event_team')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $doctor_cust_events_team[$v_event['event']]['eventTitle'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					if($tab_name == 'todo')
					{
						$due_date = date('d.m.Y', strtotime($v_todo['until_date']));
						$todos_ipids[] = $todos_arr[$v_event['event']]['ipid'];
						if($todos_arr[$v_event['event']]['triggered_by'] != 'system_medipumps')
						{
		
							$triggered_by_arr[$i] = explode("-",$v_todo['triggered_by']);
		
							if($triggered_by_arr[$i][0] == "medacknowledge")
							{
								$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'medacknowledge';
								$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
								$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
								if(strlen($triggered_by_arr[$i][1]) > 0){
									$master_data[strtotime($create_date)][$i]['drugplan_id'] = $triggered_by_arr[$i][1];
								}
								$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
							}
							elseif($triggered_by_arr[$i][0] == "pumpmedacknowledge")
							{
								$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'pumpmedacknowledge';
								$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
								$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
								$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
								if(strlen($triggered_by_arr[$i][1]) > 0){
									$master_data[strtotime($create_date)][$i]['cocktail_id'] = $triggered_by_arr[$i][1];
								}
							}
							else
							{
								$master_data[strtotime($create_date)][$i]['triggered_by_info'] = '0';
								$master_data[strtotime($create_date)][$i]['medical_change'] = '0';
								$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '0';
								$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
								$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
							}
		
		
							$master_data[strtotime($create_date)][$i]['alt_id'] = $v_todo['record_id'];
							$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
							$master_data[strtotime($create_date)][$i]['ipid'] = $todos_arr[$v_event['event']]['ipid'];
							$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
							$master_data[strtotime($create_date)][$i]['event_title'] = $todos_arr[$v_event['event']]['todo'];
							$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
							$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
							$master_data[strtotime($create_date)][$i]['user_id'] = $todos_arr[$v_event['event']]['user_id'];
							$master_data[strtotime($create_date)][$i]['group_id'] = $todos_arr[$v_event['event']]['group_id'];
							$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
							$master_data[strtotime($create_date)][$i]['triggered_by'] = $todos_arr[$v_event['event']]['triggered_by'];
							$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
						}
						else if($todos_arr[$v_event['event']]['group_id'] == $groupid || $user_type == 'SA')
						{
							$master_data[strtotime($create_date)][$i]['triggered_by_info'] = '0';
							$master_data[strtotime($create_date)][$i]['medical_change'] = '0';
							$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '0';
							$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
		
		
							$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
							$master_data[strtotime($create_date)][$i]['ipid'] = $todos_arr[$v_event['event']]['ipid'];
							$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
							$master_data[strtotime($create_date)][$i]['event_title'] = $todos_arr[$v_event['event']]['todo'];
							$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
							$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
							$master_data[strtotime($create_date)][$i]['user_id'] = $todos_arr[$v_event['event']]['user_id'];
							$master_data[strtotime($create_date)][$i]['group_id'] = $todos_arr[$v_event['event']]['group_id'];
							$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
							$master_data[strtotime($create_date)][$i]['triggered_by'] = $todos_arr[$v_event['event']]['triggered_by'];
							$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
						}
						$todos_skip[] = $v_event['event'];
					}
		
		
					if($tab_name == 'sgbxi')
					{
						$sgbxi_ipids[] = $sgbxi_arr[$v_event['event']]['ipid'];
						$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
						$master_data[strtotime($create_date)][$i]['ipid'] = $sgbxi_arr[$v_event['event']]['ipid'];
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $sgbxi_arr[$v_event['event']]['title'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['user_id'] = $sgbxi_arr[$v_event['event']]['user_id'];
						$master_data[strtotime($create_date)][$i]['group_id'] = $sgbxi_arr[$v_event['event']]['group_id'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['triggered_by'] = $sgbxi_arr[$v_event['event']]['triggered_by'];
						$master_data[strtotime($create_date)][$i]['done_date'] = date('d.m.Y', strtotime($sgbxi_arr[$v_event['event']]['until_date']));
						$sgbxi_skip[] = $v_event['event'];
					}
		
					if($tab_name == 'asses')
					{
						$ipid = $assessment_arr[$v_event['event']]['ipid'];
		
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_ipid'] = $ipid;
						$master_data[strtotime($create_date)][$i]['event_title'] = '<a href="patientform/kvnoassessment?id=' . Pms_Uuid::encrypt($asses_pat_det[$ipid]['id']) . '">' . strtoupper($asses_pat_det[$ipid]['EpidIpidMapping']['epid']) . " - " . ucfirst($asses_pat_det[$ipid]['last_name']) . ", " . ucfirst($asses_pat_det[$ipid]['first_name']) . '</a>';
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					if($tab_name == 'reasses')
					{
						$ipid = $assessment_arr[$v_event['event']]['ipid'];
		
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_ipid'] = $ipid;
						$master_data[strtotime($create_date)][$i]['event_title'] = '<a href="patientform/reassessment?id=' . Pms_Uuid::encrypt($patientsFinalIds[$assess['ipid']]['id']) . '">' . strtoupper($asses_pat_det[$ipid]['epid']) . " - " . ucfirst($asses_pat_det[$ipid]['last_name']) . ", " . ucfirst($asses_pat_det[$ipid]['first_name']) . '</a>';
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
					if($tab_name == 'patient_birthday')
					{
						$ipid = $birthds_events[$v_event['event']]['ipid'];
		
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_ipid'] = $ipid;
						$master_data[strtotime($create_date)][$i]['event_title'] = strtoupper($patients_birthds[$ipid]['EpidIpidMapping']['epid']) . ' - ' . ucfirst($patients_birthds[$ipid]['last_name']) . ", " . ucfirst($patients_birthds[$ipid]['first_name']);
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['triggered_by'] = 'system';
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					$i++;
				}
				$i++;
			}
			$todos_ipids = array_values(array_unique($todos_ipids));
			$sgbxi_ipids = array_values(array_unique($sgbxi_ipids));
		
			$old_events = $todos->getCompletedTodosByClientId($clientid, $todos_skip);
		
			if($_REQUEST['dbgz'])
			{
				print_r($old_events);
			}
		
		
			foreach($old_events as $k_old_event => $v_old_event)
			{
				$create_date = date('Y-m-d', strtotime($v_old_event['create_date']));
		
				if(date('Y-m-d', strtotime($v_old_event['until_date'])) != '1970-01-01' && $v_old_event['until_date'] != '0000-00-00 00:00:00')
				{
					$done_date = date('d.m.Y', strtotime($v_old_event['until_date']));
				}
				else
				{
					$done_date = '-';
				}
		
				$todos_ipids[] = $v_old_event['ipid'];
		
				if($v_old_event['triggered_by'] != 'system_medipumps')
				{
		
		
					$triggered_by_arr[$i] = explode("-",$v_old_event['triggered_by']);
		
					if($triggered_by_arr[$i][0] == "medacknowledge")
					{
						$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'medacknowledge';
						$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
						$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
						if(strlen($triggered_by_arr[$i][1]) > 0){
							$master_data[strtotime($create_date)][$i]['drugplan_id'] = $triggered_by_arr[$i][1];
						}
						$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
					}
					elseif($triggered_by_arr[$i][0] == "pumpmedacknowledge")
					{
						$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'pumpmedacknowledge';
						$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
						$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
						$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
						if(strlen($triggered_by_arr[$i][1]) > 0){
							$master_data[strtotime($create_date)][$i]['cocktail_id'] = $triggered_by_arr[$i][1];
						}
					}
					else
					{
						$master_data[strtotime($create_date)][$i]['triggered_by_info'] = '0';
						$master_data[strtotime($create_date)][$i]['medical_change'] = '0';
						$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '0';
						$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
						$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
					}
		
		
		
					$master_data[strtotime($create_date)][$i]['tabname'] = 'old_todo';
					$master_data[strtotime($create_date)][$i]['event_id'] = $v_old_event['id'];
					$master_data[strtotime($create_date)][$i]['ipid'] = $v_old_event['ipid'];
					$master_data[strtotime($create_date)][$i]['event_title'] = $v_old_event['todo'];
					$master_data[strtotime($create_date)][$i]['event_source'] = 'u';
					$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_old_event['complete_user']]['user_title'] . ' ' . $client_users_arr[$v_old_event['complete_user']]['last_name'] . ', ' . $client_users_arr[$v_old_event['complete_user']]['first_name'];
					$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_old_event['create_date']));
					$master_data[strtotime($create_date)][$i]['done_date'] = $done_date;
				}
				$i++;
			}
		
		
		
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
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
		
			$patients = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->whereIn("p.ipid", $todos_ipids)
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.clientid = ' . $clientid);
			$patients_res = $patients->fetchArray();
			foreach($patients_res as $k_pat_todo => $v_pat_todo)
			{
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']] = $v_pat_todo;
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_todo['EpidIpidMapping']['epid']);
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_todo['id']);
			}
		
			/* ###################### SGB XI####################### */
		
			$sgbxi_old_events = $dashboard_events->get_completed_dashboard_events($clientid, $sgbxi_skip);
		
			if($_REQUEST['dbgz'])
			{
				print_r($sgbxi_old_events);
			}
		
			foreach($sgbxi_old_events as $k_sold_event => $v_sgbxi_old_event)
			{
				$create_date = date('Y-m-d', strtotime($v_sgbxi_old_event['create_date']));
		
				if(date('Y-m-d', strtotime($v_sgbxi_old_event['until_date'])) != '1970-01-01' && $v_sgbxi_old_event['until_date'] != '0000-00-00 00:00:00')
				{
					$done_date = date('d.m.Y', strtotime($v_sgbxi_old_event['until_date']));
				}
				else
				{
					$done_date = '-';
				}
		
				$sgbxi_ipids[] = $v_sgbxi_old_event['ipid'];
		
		
				$master_data[strtotime($create_date)][$i]['tabname'] = 'old_sgbxi';
				$master_data[strtotime($create_date)][$i]['event_id'] = $v_sgbxi_old_event['id'];
				$master_data[strtotime($create_date)][$i]['ipid'] = $v_sgbxi_old_event['ipid'];
				$master_data[strtotime($create_date)][$i]['event_title'] = $v_sgbxi_old_event['todo'];
				$master_data[strtotime($create_date)][$i]['event_source'] = 'u';
				$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_sgbxi_old_event['complete_user']]['user_title'] . ' ' . $client_users_arr[$v_sgbxi_old_event['complete_user']]['last_name'] . ', ' . $client_users_arr[$v_sgbxi_old_event['complete_user']]['first_name'];
				$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_sgbxi_old_event['create_date']));
				$master_data[strtotime($create_date)][$i]['done_date'] = $done_date;
				$i++;
			}
		
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
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
		
			$patients_sgbxi_q = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->whereIn("p.ipid", $sgbxi_ipids)
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.clientid = ' . $clientid);
			$patients_sgbxi_res = $patients_sgbxi_q->fetchArray();
			foreach($patients_sgbxi_res as $k_pat_sgbxi => $v_pat_sgbxi)
			{
				$sgbxi_patients[$v_pat_sgbxi['EpidIpidMapping']['ipid']] = $v_pat_sgbxi;
				$sgbxi_patients[$v_pat_sgbxi['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_sgbxi['EpidIpidMapping']['epid']);
				$sgbxi_patients[$v_pat_sgbxi['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_sgbxi['id']);
			}
		
			$this->view->master_data = $master_data;
			$this->view->todo_patients = $todo_patients;
			$this->view->sgbxi_patients = $sgbxi_patients;
		}

		public function multiplevisitoverlappingAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['new_intersected'] = '0';
			$return['intersected'] = '0';
			$return['error'] = '0';

			parse_str($_REQUEST['form_data'], $visit_details);
			if($visit_details['visit_type'] == 'N')
			{
				$kvno_n = new KvnoNurse();
				$all_visits = $kvno_n->getAllPatientNurseVisits($ipid, $_REQUEST['visit_date']);
			}

			foreach($visit_details['visit'] as $k_vis => $v_vis)
			{
				$visit[$k_vis]['start'] = $visit_details['visit_date'] . " " . $v_vis['start'] . ":00";
				$visit[$k_vis]['end'] = $visit_details['visit_date'] . " " . $v_vis['end'] . ":00";

				if(strtotime($visit[$k_vis]['start']) > strtotime($visit[$k_vis]['end']))
				{
					$return['error'] = '1';
				}

				$start_time[$k_vis] = explode(":", $v_vis['start']);
				$end_time[$k_vis] = explode(":", $v_vis['end']);

				if($start_time[$k_vis][0] > 23 || $start_time[$k_vis][1] > 59 || $end_time[$k_vis][0] > 23 || $end_time[$k_vis][1] > 59)
				{
					$return['error'] = '2';
				}
			}

			$i = 1;
			if($return['error'] == "0")
			{
				foreach($visit_details['visit'] as $k_vis => $v_vis)
				{

					$visit[$k_vis]['start'] = $visit_details['visit_date'] . " " . $v_vis['start'] . ":00";
					$visit[$k_vis]['end'] = $visit_details['visit_date'] . " " . $v_vis['end'] . ":00";

					if(!empty($v_vis['start']) && !empty($v_vis['end']))
					{
						foreach($visit_details['visit'] as $k_vis_sec => $v_vis_sec)
						{

							if($k_vis != $k_vis_sec)
							{

								$visit_sec[$k_vis_sec]['start'] = $visit_details['visit_date'] . " " . $v_vis_sec['start'] . ":00";
								$visit_sec[$k_vis_sec]['end'] = $visit_details['visit_date'] . " " . $v_vis_sec['end'] . ":00";

								if(Pms_CommonData::isintersected(strtotime($visit[$k_vis]['start']), strtotime($visit[$k_vis]['end']), strtotime($visit_sec[$k_vis_sec]['start']), strtotime($visit_sec[$k_vis_sec]['end'])))
								{
									$return['new_intersected'] = '1';
								}
							}
						}
					}
					$i++;
				}
			}


			foreach($all_visits as $k_doc => $v_doc)
			{
				$vizit_date_arr = explode(" ", $v_doc['vizit_date']);

				$source['start'] = date("Y-m-d H:i:s", strtotime($vizit_date_arr[0] . " " . $v_doc['kvno_begin_date_h'] . ":" . $v_doc['kvno_begin_date_m'] . ":00"));
				$source['end'] = date("Y-m-d H:i:s", strtotime($vizit_date_arr[0] . " " . $v_doc['kvno_end_date_h'] . ":" . $v_doc['kvno_end_date_m'] . ":00"));

				$v_doc['start_date'] = date('d.m.Y H:i', strtotime($source['start']));
				$v_doc['end_date'] = date('d.m.Y H:i', strtotime($source['end']));


				foreach($visit as $key => $visit_det)
				{
					if(Pms_CommonData::isintersected(strtotime($source['start']), strtotime($source['end']), strtotime($visit_det['start']), strtotime($visit_det['end'])))
					{
						$return['intersected'] = '1';
						$return['visits'][] = $v_doc;
					}
				}
			}

			echo json_encode($return);
			exit;
		}

		public function saveblockitemAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$block = $_REQUEST['block'];
			$option_id = $_REQUEST['option_id'];
			$option_name = $_REQUEST['option_name'];
			$option_shortcut = $_REQUEST['option_shortcut'];

			if(!$_REQUEST['available'] && $_REQUEST['available'] != 0)
			{
				$_REQUEST['available'] = 1; // default set visible
			}

			$available = $_REQUEST['available'];
			$coordinator_notification = $_REQUEST['coordinator_notification'];

			if(!empty($_REQUEST['block']))
			{
				if(empty($option_id))
				{
					$user = new FormBlocksSettings();
					$user->block = $block;
					$user->clientid = $logininfo->clientid;
					$user->option_name = $option_name;
					$user->shortcut = $option_shortcut;
					$user->available = $available;
					$user->coordinator_notification = $coordinator_notification;
					$user->valid_from = date("Y-m-d 00:00:00",time());
					$user->save();
					$new_option = $user->id;
				}
				else
				{
					$stmb = Doctrine::getTable('FormBlocksSettings')->find($option_id);
					$stmb->option_name = $_REQUEST['option_name'];
					$stmb->shortcut = $_REQUEST['option_shortcut'];
					$stmb->available = $_REQUEST['available'];
					$stmb->coordinator_notification = $_REQUEST['coordinator_notification'];
					$stmb->save();
				}

				$actiondetais = array();
				if(empty($option_id))
				{

					$actiondetais['option_name'] = $option_name;
					$actiondetais['option_shortcut'] = $option_shortcut;
					$actiondetais['option_id'] = $new_option;
					$actiondetais['available'] = $available;
					$actiondetais['coordinator_notification'] = $coordinator_notification;
					$actiondetais['block'] = $block;
				}
				else
				{

					$actiondetais['option_name'] = $option_name;
					$actiondetais['option_shortcut'] = $option_shortcut;
					$actiondetais['option_id'] = $option_id;
					$actiondetais['available'] = $available;
					$actiondetais['block'] = $block;
				}

				echo json_encode($actiondetais);
			}
			else
			{
				echo ''; //error
			}
			exit;
		}

		public function medipumpoverlappingAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$medipump = $_REQUEST['medipump'];
			$start_date = strtotime($_REQUEST['begin_date']);
			$end_date = strtotime($_REQUEST['end_date']);

			if(strlen($_REQUEST['exclude_id']) > '0')
			{
				$excluded_id = $_REQUEST['exclude_id'];
			}
			else
			{
				$excluded_id = false;
			}

			$patient_medipumps = new PatientMedipumps();
			$patient_medipumps_overlapping = $patient_medipumps->get_overlapping_medipumps($ipid, $medipump, $start_date, $end_date, $excluded_id);

			if(count($patient_medipumps_overlapping) == 0)
			{
				$return['intersected'] = '0';
			}
			else
			{
				$return['intersected'] = '1';
			}

			echo json_encode($return);
			exit;
		}

		public function approvedtypesoverlappingAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['interval_incorrect'] = "0";

			$start_date = strtotime($_REQUEST['start_date']);

			if($_REQUEST['end_date'])
			{
				$end_date = strtotime($_REQUEST['end_date']);

				if($start_date > $end_date)
				{ // check interval only if end date it is filled
					$return['interval_incorrect'] = '1';
				}
			}
			else
			{
				$end_date = time();
			}

			if(strlen($_REQUEST['exclude_id']) > '0')
			{
				$excluded_id = $_REQUEST['exclude_id'];
			}
			else
			{
				$excluded_id = false;
			}

			if($return['interval_incorrect'] == 0)
			{
				$pavt = new PatientApprovedVisitTypes();
				$pavt_overlapping = $pavt->get_overlapping_visits_types($ipid, $start_date, $end_date, $excluded_id);

				if(count($pavt_overlapping) == 0)
				{
					$return['intersected'] = '0';
				}
				else
				{
					$return['intersected'] = '1';
				}
			}


			echo json_encode($return);
			exit;
		}

		public function orderactionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			foreach($_REQUEST['order'] as $k_order => $v_action)
			{
				$update_order = Doctrine_Query::create()
					->update('SocialCodePriceActions')
					->set('aorder', $k_order)
					->where('actionid = ?', $v_action)
					->andWhere('list = ?', $_REQUEST['list_id'])
					->andWhere('clientid = ?', $clientid);
				$exec_update = $update_order->execute();
			}
			exit;
		}

		public function overlappingcheckAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);


			/* ---------------------GET DATA-------------------------- */
			if($_REQUEST['uid'])
			{
				$userid = $_REQUEST['uid'];
			}
			else
			{
				$userid = $logininfo->userid;
			}

			$edit_id = $_REQUEST['edit_id'];
			$begin_hr = $_REQUEST['begin_h'];
			$begin_min = $_REQUEST['begin_m'];
			$end_hr = $_REQUEST['end_h'];
			$end_min = $_REQUEST['end_m'];
			$visit_start = $_REQUEST['visit_start'];
			
			if($_REQUEST['visit_end'])
			{
				$visit_end = $_REQUEST['visit_end'];
			}
			else
			{
				if($_REQUEST['over_midnight'] == "1"){
					$visit_end  = date("d.m.Y", strtotime('+1 day', strtotime($_REQUEST['visit_start'])));
				} else{
					$visit_end = $_REQUEST['visit_start'];
				}
			}

			
			$full_start_date = mktime ( $begin_hr, $begin_min,"0",  date("n",strtotime($visit_start)),date("j",strtotime($visit_start)), date("Y",strtotime($visit_start)) );
			$full_end_date = mktime ( $end_hr, $end_min,"0",  date("n",strtotime($visit_end)),date("j",strtotime($visit_end)), date("Y",strtotime($visit_end)) );
			
			$return['start_date'] = date("Y-m-d H:i:s",$full_start_date);
			$return['end_date'] = date("Y-m-d H:i:s",$full_end_date);
			$error = 0;
			
			$return['error'] = 0;
			$return['confirmation'] = 0;
			$return['error_id'] = 0;
			$return['denied_confirmation'] = 0;
			if(empty( $visit_start) || strlen($visit_start) == 0 ) {
			    $error = 1;
			    $return['error'] = "Bitte füllen Beginn datum";
			}
			
			if(empty($visit_end) || strlen($visit_end) == 0 ) {
			    $error = 2;
			    $return['error'] = "Bitte füllen Beginn datum";;
			}
			
			
			
			    
			if($begin_hr >  $end_hr) {
			    if($_REQUEST['visit_type'] == 'CF' && $_REQUEST['over_midnight'] == "1")
			    {
    			    $error = 99;
    			    $return['confirmation'] =  $this->view->translate("mark vistis- as overnight");
    			    $return['denied_confirmation'] = "Die Zeit der Beendigung des Besuchs sollte später sein als die Anfangszeit.";
			    }
			    else
			    {
    			    $error = 3;
    			    $return['error'] = "Die Zeit der Beendigung des Besuchs sollte später sein als die Anfangszeit.";
			    }
			}

			if( $begin_hr == $end_hr  && $begin_min >=  $end_min) {
			    $error = 4;
			    $return['error'] = "Die Zeit der Beendigung des Besuchs sollte später sein als die Anfangszeit.";
			}
			
			if(!Pms_Validation::isdate($visit_start)) {
			    $error = 5;
			    $return['error'] = "ungültige Beginn datum";
			}
			
			if(strtotime($visit_start) > strtotime(date("d.m.Y"))){
			    $error = 6;
			    $return['error'] = "Future Datum nicht erlaubt";
			}
			
			if( (empty($begin_hr) || strlen($begin_hr) == 0)  || (empty($end_hr) || strlen($end_hr) == 0) || (empty($begin_min) || strlen($begin_min) == 0) || (empty($end_min) || strlen($end_min) == 0) ) {
			    $error = 7;
			    $return['error'] = "Bitte füllen zeit";
		   }
		   
		   $full_start_date = mktime ( $begin_hr, $begin_min,"0",  date("n",strtotime($visit_start)),date("j",strtotime($visit_start)), date("Y",strtotime($visit_start)) );
		   if($full_start_date > strtotime(date("d.m.Y H:i", time()))  )
		   {
			    $error = 8;
			    $return['error'] = "Future Datum nicht erlaubt";
		   }

		   if(date('Y', strtotime($visit_start)) < '2008')
		   {
		       $error = 9;
		       $return['error'] = "Datum Jahr muss größer als 2008 sein";
		        
		   }
		   
		   
		   
			if($error == 0){
			    $return['intersected'] = '0';
			    
    			$start_date = $visit_start . " " . $begin_hr . ":" . $begin_min . ":00";
    			$end_date = $visit_end . " " . $end_hr . ":" . $end_min . ":00";
    
    			$new_visit['start'] = date('Y-m-d H:i:s', strtotime($start_date));
    			$new_visit['end'] = date('Y-m-d H:i:s', strtotime($end_date));
    
    			/* ----------------------CHECK OVERLAPING VISITS ------------------------- */
    
    			if($_REQUEST['visit_type'] == 'D') // DOCTOR VISIT
    			{
    				$kvno_d = new KvnoDoctor();
    				$overlapping_visits = $kvno_d->checkDoctorVisitsByUser($userid, $new_visit['start'], $new_visit['end'], $edit_id);
    			}
    			else if($_REQUEST['visit_type'] == 'N') // NURSE VISIT
    			{
    				$kvno_n = new KvnoNurse();
    				$overlapping_visits = $kvno_n->checkNurseVisitsByUser($userid, $new_visit['start'], $new_visit['end'], $edit_id);
    			}
    			else if($_REQUEST['visit_type'] == 'K') // KOORDINATION VISIT
    			{
    				$kvno_k = new VisitKoordination();
    				$overlapping_visits = $kvno_k->checkKoordinationVisitsByUser($userid, $new_visit['start'], $new_visit['end'], $edit_id);
    			}
    			else if($_REQUEST['visit_type'] == 'BD') // BAYERN DOCTOR VISIT
    			{
    				$bayern_d = new BayernDoctorVisit();
    				$overlapping_visits = $bayern_d->checkBayernVisitsByUser($userid, $new_visit['start'], $new_visit['end'], $edit_id);
    			}
    			else if($_REQUEST['visit_type'] == 'CF') // CONTACT FORM
    			{
    				$contact_forms = new ContactForms();
    				$overlapping_visits = $contact_forms->checkContactFormsByUser($userid, $new_visit['start'], $new_visit['end'], $edit_id);
    			}
    
    			if(!empty($overlapping_visits))
    			{
    				foreach($overlapping_visits as $k_doc => $v_doc)
    				{
    					$return['visits'][] = $v_doc;
    					$return['intersected'] = '1';
    				}
    			}
            }
            $return['error_id'] = $error;
			echo json_encode($return);
			exit;
		}
		
		public function overmidnightcheckAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);


			/* ---------------------GET DATA-------------------------- */
			if($_REQUEST['uid'])
			{
				$userid = $_REQUEST['uid'];
			}
			else
			{
				$userid = $logininfo->userid;
			}

			$edit_id = $_REQUEST['edit_id'];
			$begin_hr = $_REQUEST['begin_h'];
			$begin_min = $_REQUEST['begin_m'];
			$end_hr = $_REQUEST['end_h'];
			$end_min = $_REQUEST['end_m'];
			$visit_start = $_REQUEST['visit_start'];
			
			if($_REQUEST['visit_end'])
			{
				$visit_end = $_REQUEST['visit_end'];
			}
			else
			{
				if($_REQUEST['over_midnight'] == "1"){
					$visit_end  = date("d.m.Y", strtotime('+1 day', strtotime($_REQUEST['visit_start'])));
				} else{
					$visit_end = $_REQUEST['visit_start'];
				}
			}
			
			$full_start_date = mktime ( $begin_hr, $begin_min,"0",  date("n",strtotime($visit_start)),date("j",strtotime($visit_start)), date("Y",strtotime($visit_start)) );
			$full_end_date = mktime ( $end_hr, $end_min,"0",  date("n",strtotime($visit_end)),date("j",strtotime($visit_end)), date("Y",strtotime($visit_end)) );
			
			$return['start_date'] = date("Y-m-d H:i:s",$full_start_date);
			$return['end_date'] = date("Y-m-d H:i:s",$full_end_date);
			$return['start_date_dmy'] = date("d.m.Y",$full_start_date);
			$return['end_date_dmy'] = date("d.m.Y",$full_end_date);
			$return['start_checked'] = '';
			$return['end_checked'] = '';
			
			if(!empty($edit_id) && $edit_id != "0")
			{
				$contact_forms = new ContactForms();
				$visit_details= $contact_forms->get_contact_form($edit_id);
			}
			
			
			if(date("d.m.Y",$full_start_date) == date("d.m.Y",strtotime($visit_details['date']))){
				$return['start_checked'] = 'checked="checked"';
				$return['end_checked'] = '';
			}
			
			if(date("d.m.Y",$full_end_date) == date("d.m.Y",strtotime($visit_details['date']))){
				$return['start_checked'] = '';
				$return['end_checked'] = 'checked="checked"';
			}
			
			$error = 0;
			
			$return['error'] = 0;
			echo json_encode($return);
			exit;
		}

		public function multipleoverlappingcheckAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$return['new_intersected'] = '0';
			$return['intersected'] = '0';
			$return['error'] = '0';

			parse_str($_REQUEST['form_data'], $visit_details);

			if($_REQUEST['uid'])
			{
				$userid = $_REQUEST['uid'];
			}
			else
			{
				$userid = $logininfo->userid;
			}

			$visit_start = $visit_details['visit_date']; // if needed chenge to star date

			if($visit_details['visit_end'])
			{
				$visit_end = $visit_details['visit_end'];
			}
			else
			{
				$visit_end = $visit_details['visit_date']; // if needed change to start date
			}

			foreach($visit_details['visit'] as $k_vis => $v_vis)
			{

				$visit[$k_vis]['start'] = $visit_start . " " . $v_vis['start'] . ":00";
				$visit[$k_vis]['end'] = $visit_end . " " . $v_vis['end'] . ":00";

				if(strtotime($visit_start) == strtotime($visit_end) && strtotime($visit[$k_vis]['start']) > strtotime($visit[$k_vis]['end']))
				{
					$return['error'] = '1';
				}

				$start_time[$k_vis] = explode(":", $v_vis['start']);
				$end_time[$k_vis] = explode(":", $v_vis['end']);

				if($start_time[$k_vis][0] > 23 || $start_time[$k_vis][1] > 59 || $end_time[$k_vis][0] > 23 || $end_time[$k_vis][1] > 59)
				{
					$return['error'] = '2';
				}
			}

			if($visit_details['visit_type'] == 'N')
			{
				$kvno_n = new KvnoNurse();
				$overlapping_visits = $kvno_n->checkNurseMultipleVisitsByUser($userid, $visit);
			}

			// check if the new visits are overlapping
			$i = 1;
			if($return['error'] == "0")
			{
				foreach($visit_details['visit'] as $k_vis => $v_vis)
				{
					$visit[$k_vis]['start'] = $visit_details['visit_date'] . " " . $v_vis['start'] . ":00";
					$visit[$k_vis]['end'] = $visit_details['visit_date'] . " " . $v_vis['end'] . ":00";

					if(!empty($v_vis['start']) && !empty($v_vis['end']))
					{
						foreach($visit_details['visit'] as $k_vis_sec => $v_vis_sec)
						{
							if($k_vis != $k_vis_sec)
							{
								$visit_sec[$k_vis_sec]['start'] = $visit_details['visit_date'] . " " . $v_vis_sec['start'] . ":00";
								$visit_sec[$k_vis_sec]['end'] = $visit_details['visit_date'] . " " . $v_vis_sec['end'] . ":00";

								if(Pms_CommonData::isintersected(strtotime($visit[$k_vis]['start']), strtotime($visit[$k_vis]['end']), strtotime($visit_sec[$k_vis_sec]['start']), strtotime($visit_sec[$k_vis_sec]['end'])))
								{
									$return['new_intersected'] = '1';
								}
							}
						}
					}
					$i++;
				}
			}

			if(!empty($overlapping_visits))
			{
				foreach($overlapping_visits as $k_doc => $v_doc)
				{
					$return['visits'][] = $v_doc;
					$return['intersected'] = '1';
				}
			}


			echo json_encode($return);
			exit;
		}

		public function getcontactpersonsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$hidemagic = Zend_Registry::get('hidemagic');
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";

			$adminvisible = PatientMaster::getAdminVisibility($ipid);

			if($logininfo->usertype == 'SA' && $adminvisible != 1)
			{
				$sql = "*,'" . $hidemagic . "' as cnt_first_name";
				$sql .=",'" . $hidemagic . "' as cnt_middle_name";
				$sql .=",'" . $hidemagic . "' as cnt_last_name";
				$sql .=",'" . $hidemagic . "' as cnt_title";
				$sql .=",'" . $hidemagic . "' as cnt_street1";
				$sql .=",'" . $hidemagic . "' as cnt_street2";
				$sql .=",'" . $hidemagic . "' as cnt_zip";
				$sql .=",'" . $hidemagic . "' as cnt_city";
				$sql .=",'" . $hidemagic . "' as cnt_phone";
				$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone_dec";
				$sql .=",'" . $hidemagic . "' as cnt_mobile";
				$sql .=",'" . $hidemagic . "' as cnt_comment";
				$sql .=",'" . $hidemagic . "' as cnt_nation";
			}

			if(strlen($_REQUEST['q']) != '0')
			{
				$drop = Doctrine_Query::create()
					->select($sql)
					->from('ContactPersonMaster')
					->where("ipid='" . $ipid . "'")
					->andWhere("( trim(lower(convert(AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? OR trim(lower(convert(AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like ? OR trim(lower(convert(AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') using latin1))) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere('isdelete = 0')
					->orderby('create_date ASC');
				$droparray = $drop->fetchArray();

				foreach($droparray as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['first_name'] = html_entity_decode($val['cnt_first_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['last_name'] = html_entity_decode($val['cnt_last_name'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['phone'] = html_entity_decode($val['cnt_phone'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
			
			return $drop_array;
		}

		public function overlappingvisitsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;

			$return = array();
			$return_visits = array();
			
			$return['new_intersected'] = '0';
			$return['intersected'] = '0';
			$return['error'] = '0';
			$return['visits'] = "";
			$return['inserted_overlapping'] = "";

			if($_REQUEST['uid'])
			{
				$userid = $_REQUEST['uid'];
			}
			else
			{
				$userid = $logininfo->userid;
			}
			parse_str($_REQUEST['form_data'], $visits_details);
			$x = 1;

			foreach($visits_details['visit'] as $date => $visit_values)
			{
				foreach($visit_values as $nr_vis => $visit_details)
				{
					if(!empty($visit_details['start_time']) && !empty($visit_details['end_time']) && $visit_details['active'] == '1')
					{
						$post_visits['all'][$x]['start'] = $visit_details['start_date'] . " " . $visit_details['start_time'] . ":00";
						$post_visits['all'][$x]['visit_start_old'] = $visit_details['start_date'] . " " . $visit_details['old_start_time'] . ":00";
						$post_visits['all'][$x]['end'] = $visit_details['start_date'] . " " . $visit_details['end_time'] . ":00";
						$post_visits['all'][$x]['visit_end_old'] = $visit_details['start_date'] . " " . $visit_details['old_end_time'] . ":00";
						$post_visits['all'][$x]['visit_id'] = $visit_details['id'];
						$post_visits['all'][$x]['visit_nr'] = $visit_details['nr'];
						if($visit_details['create_user'])
						{
							$post_visits['all'][$x]['create_user'] = $visit_details['create_user'];
						}
						else
						{
							$post_visits['all'][$x]['create_user'] = $userid;
						}
					}
					$x++;
				}
			}

			$all_visits = $post_visits['all'];

			$kvno_n = new KvnoNurse();
			$existing_visits = $kvno_n->get_all_overlaping_user_visits($userid, $all_visits);
			// check if the new visits are overlapping
			foreach($all_visits as $k_vis => $v_vis)
			{ // check oll visits in form for overlapping
				$visit[$k_vis]['start'] = $v_vis['start'];
				$visit[$k_vis]['end'] = $v_vis['end'];
				$visit[$k_vis]['visit_nr'] = $v_vis['visit_nr'];

				if(!empty($v_vis['start']) && !empty($v_vis['end']))
				{
					foreach($all_visits as $k_vis_sec => $v_vis_sec)
					{

						if($k_vis != $k_vis_sec && $v_vis['create_user'] == $v_vis_sec['create_user'])
						{

							$visit_sec[$k_vis_sec]['start'] = $v_vis_sec['start'];
							$visit_sec[$k_vis_sec]['end'] = $v_vis_sec['end'];
							$visit_sec[$k_vis_sec]['visit_nr'] = $v_vis_sec['visit_nr'];

							if(Pms_CommonData::isintersected(strtotime($visit[$k_vis]['start']), strtotime($visit[$k_vis]['end']), strtotime($visit_sec[$k_vis_sec]['start']), strtotime($visit_sec[$k_vis_sec]['end'])))
							{
							    if ( ! is_array($return['inserted_overlapping'])) {
							        $return['inserted_overlapping'] = array();
							    }
								$return['new_intersected'] = '1';
								$return['inserted_overlapping'][] = $visit[$k_vis]['visit_nr'];
							}
						}
					}
				}
			}


			foreach($existing_visits as $k_doc => $v_doc)
			{
				foreach($all_visits as $key => $visit_det)
				{

					if($v_doc['id'] != $visit_det['visit_id'] && $v_doc['create_user'] == $visit_det['create_user'])
					{
						if(Pms_CommonData::isintersected(strtotime($v_doc['start_date']), strtotime($v_doc['end_date']), strtotime($visit_det['start']), strtotime($visit_det['end'])))
						{
							$return['intersected'] = '1';
							
							
							if ( ! is_array($return['existing_visits'])) {
								$return['existing_visits'] = array();
							}
							
							if ( ! is_array($return['visits'])) {
								$return['visits'] = array();
							}
							
							if ( ! is_array($return_visits['system_visits']['visits'])) {
								$return_visits['system_visits']['visits'] = array();
							}
							
							if ( ! is_array($return_visits['system_visits']['ipids'])) {
								$return_visits['system_visits']['ipids'] = array();
							}
							
							
							
							$return['existing_visits'][] = $v_doc['id'];
							$return['visits'][] = $visit_det['visit_nr'];
							$return_visits['system_visits']['visits'][$v_doc['id']] = $v_doc;
							$return_visits['system_visits']['ipids'][] = $v_doc['ipid'];
						}
					}
				}
			}

			if($return_visits['system_visits'])
			{
				$return['return_visits'] = array();
				$sql = "ipid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
				$sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";

				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA')
				{
					$sql = "ipid,";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				}

				$patient = Doctrine_Query::create()
					->select($sql)
					->from('PatientMaster p')
					->whereIn("p.ipid", $return_visits['system_visits']['ipids'])
					->andWhere("p.isdelete = 0");
				$droparray1 = $patient->fetchArray();


				foreach($droparray1 as $kh => $pval)
				{
					$patient_details[$pval['ipid']] = $pval['last_name'] . ', ' . $pval['first_name'];
				}

				foreach($return_visits['system_visits']['visits'] as $vid => $vval)
				{
					$return['return_visits'][$vval['id']] = $patient_details[$vval['ipid']] . ': ' . date('H:i', strtotime($vval['start_date'])) . '-' . date('H:i', strtotime($vval['end_date'])) . ' ' . date('d.m.Y', strtotime($vval['start_date']));
				}
			}
			$return['existing_visits'] = array_unique($return['existing_visits']);

			echo json_encode($return);
			exit;
		}

		public function specialistsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$this->view->context = !empty($_REQUEST['context'] ) ? $_REQUEST['context'] : '';
			$this->view->returnRowId = !empty($_REQUEST['row'] ) ? $_REQUEST['row'] : '';
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			
			
			// ISPC-2612 Ancuta 29.06.2020
			$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Specialists', $clientid);
			// --
			
			if(strlen($_REQUEST['q']) > 0 && strlen($_REQUEST['type']) > 0)
			{
				$q = trim(strtolower(urldecode($_REQUEST['q'])));

				$drop = Doctrine_Query::create()
					->select('*')
					->from('Specialists')
					->where("(trim(lower(last_name)) like ?) or (trim(lower(first_name)) like ?) or (trim(lower(practice)) like ?)",array("%" . addslashes($q) . "%","%" . addslashes($q) . "%","%" . addslashes($q) . "%"))
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere('medical_speciality = ?', addslashes($_REQUEST['type']))
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0");
					if($client_is_follower){
					    $drop->andWhere('connection_id is NOT null');
					    $drop->andWhere('master_id is NOT null');
					}
					$drop->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['practice'] = html_entity_decode($val['practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['title'] = html_entity_decode($val['title'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['title_letter'] = html_entity_decode($val['title_letter'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation_letter'] = html_entity_decode($val['salutation_letter'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street2'] = html_entity_decode($val['street2'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_private'] = html_entity_decode($val['phone_private'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_cell'] = html_entity_decode($val['phone_cell'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['doctornumber'] = html_entity_decode($val['doctornumber'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['kv_no'] = html_entity_decode($val['kv_no'], ENT_QUOTES, "utf-8");
					$droparray[$key]['valid_from'] = html_entity_decode($val['valid_from'], ENT_QUOTES, "utf-8");
					$droparray[$key]['valid_till'] = html_entity_decode($val['valid_till'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function saveorganisationchartAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$patid = $_REQUEST['id'];
			$decid = Pms_Uuid::decrypt($patid);
			$ipid = Pms_CommonData::getIpid($decid);
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');


			if($this->getRequest()->isPost() && strlen($ipid) > 0)
			{
				$path_form = new Application_Form_PatientSteps();
				$insert_path = $path_form->insert_data($_POST, $ipid, $clientid);
				echo "1";
				exit;
			}
			exit;
		}

		public function getuserdayplanAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$date = $_REQUEST['date'];
			$current_visit_duration = $_REQUEST['visit_duration'];


			if(strlen($_REQUEST['user_id']) > 0)
			{
				$user_id = $_REQUEST['user_id'];

				$user_roster_details = new Roster();
				$user_details = $user_roster_details->get_user_overall_details($clientid, $date, $user_id);

				$patient_plan_visits = new DailyPlanningVisits();
				$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date, $user_id);

				$last_patient_plan_visits_array = $patient_plan_visits->get_last_patients_visits($clientid, $date, $user_id);

				if($user_details && !empty($user_details))
				{
					foreach($user_details as $ku => $value)
						if(empty($last_patient_plan_visits_array))
						{
							$roster_details['shift_start'] = $value['shift_start'];
							$roster_details['patient_start_visit'] = date('H:i', strtotime($value['shift_start']));
							$roster_details['patient_end_visit'] = date('H:i', mktime(date('H', strtotime($value['shift_start'])), date('i', strtotime($value['shift_start'])) + $current_visit_duration, 0, date('m', strtotime($value['shift_start'])), date('d', strtotime($value['shift_start'])), date('Y', strtotime($value['shift_start']))));
						}
						else
						{
							$time_between_visits = "0";
							$roster_details['shift_start'] = $last_patient_plan_visits_array['date'];
							$roster_details['patient_start_visit'] = date('H:i', mktime(date('H', strtotime($last_patient_plan_visits_array['end_date'])), date('i', strtotime($last_patient_plan_visits_array['end_date'])) + $time_between_visits, 0, date('m', strtotime($last_patient_plan_visits_array['end_date'])), date('d', strtotime($last_patient_plan_visits_array['end_date'])), date('Y', strtotime($last_patient_plan_visits_array['end_date']))));
							$roster_details['patient_end_visit'] = date('H:i', mktime(date('H', strtotime($roster_details['patient_start_visit'])), date('i', strtotime($roster_details['patient_start_visit'])) + $current_visit_duration, 0, date('m', strtotime($roster_details['patient_start_visit'])), date('d', strtotime($roster_details['patient_start_visit'])), date('Y', strtotime($roster_details['patient_start_visit']))));
						}
					$roster_details['userid'] = $value['userid'];
				}
				else
				{
					if(empty($last_patient_plan_visits_array))
					{
						$roster_details['shift_start'] = $date;
						$roster_details['patient_start_visit'] = date('H:i', strtotime($roster_details['shift_start']));
						$roster_details['patient_end_visit'] = date('H:i', mktime(date('H', strtotime($roster_details['patient_start_visit'])), date('i', strtotime($roster_details['patient_start_visit'])) + $current_visit_duration, 0, date('m', strtotime($roster_details['patient_start_visit'])), date('d', strtotime($roster_details['patient_start_visit'])), date('Y', strtotime($roster_details['patient_start_visit']))));
					}
					else
					{
						$time_between_visits = "0";
						$roster_details['shift_start'] = $last_patient_plan_visits_array['date'];
						$roster_details['patient_start_visit'] = date('H:i', mktime(date('H', strtotime($last_patient_plan_visits_array['end_date'])), date('i', strtotime($last_patient_plan_visits_array['end_date'])) + $time_between_visits, 0, date('m', strtotime($last_patient_plan_visits_array['end_date'])), date('d', strtotime($last_patient_plan_visits_array['end_date'])), date('Y', strtotime($last_patient_plan_visits_array['end_date']))));
						$roster_details['patient_end_visit'] = date('H:i', mktime(date('H', strtotime($roster_details['patient_start_visit'])), date('i', strtotime($roster_details['patient_start_visit'])) + $current_visit_duration, 0, date('m', strtotime($roster_details['patient_start_visit'])), date('d', strtotime($roster_details['patient_start_visit'])), date('Y', strtotime($roster_details['patient_start_visit']))));
					}
					$roster_details['userid'] = $user_id;
				}


				echo json_encode($roster_details);
				exit;
			}
			else
			{
				return false;
			}
		}

		public function uniqueshortcutsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;

			$return['duplicates'] = '0';

			parse_str($_REQUEST['form_data'], $price_list_details);

			foreach($price_list_details as $form_block => $block)
			{
				foreach($block as $block_id => $block_options)
				{
					foreach($block_options as $block_option_id => $block_option_details)
					{
						if(!empty($block_option_details['shortcut']))
						{
							$shortcut_array[$block_id][$block_option_id] = $block_option_details['shortcut'];
						}
					}
				}
			}


			$duplicates = 0;
			foreach($shortcut_array as $block_id => $option_shortcut)
			{
				$iArr = array_map('strtolower', $option_shortcut);
				$iArr = array_intersect($iArr, array_unique(array_diff_key($iArr, array_unique($iArr))));

				$shortcut_array_duplicates[$block_id] = array_intersect_key($option_shortcut, $iArr);
				if(!empty($shortcut_array_duplicates[$block_id]))
				{
					$duplicates++;
				}
			}

			$return['duplicates'] = $duplicates;
			$return['shortcut_array_duplicates'] = $shortcut_array_duplicates;

			echo json_encode($return);
			exit;
		}

		//terminal import patient search
		public function searchpatientimportAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 2)
			{
				$drop = Doctrine_Query::create()
					->select('*, epid')
					->from('EpidIpidMapping')
					->where("clientid = ?", $clientid)
					->orderBy('epid asc');
				$droparray = $drop->fetchArray();

				if($droparray)
				{
					foreach($droparray as $key => $val)
					{
						$fn_epids[$val['ipid']] = $val['epid'];
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

					//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
					$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					$sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

					//				if super admin check if patient is visible or not
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
						$sql .= "IF(isadminvisible = 1, birthd, '" . $hidemagic . "') as birthd, ";
						$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
					}


					$patient = Doctrine_Query::create()
						->select($sql)
						->from('PatientMaster p')
						->where("p.ipid in(" . $ipidval . ")  and p.ipid IN (" . $user_patients['patients_str'] . ") and p.isdelete = 0");
					$patient->leftJoin("p.EpidIpidMapping e");
					$patient->andwhere("e.clientid = ".$logininfo->clientid);
					$patient->andwhere("trim(lower(e.epid)) like trim(lower('%" . $search_string . "%')) or 
						(trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or 
						trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or 
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))"
						,array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
								"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
								"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
								"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
								"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
								"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
								"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")	
							);

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

					//IF(isdischarged != 1 AND isstandby != 1, 0,(IF(isdischarged = 1,1,2))) as status
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
						IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
						,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
						->from('PatientMaster')
						->where("isdelete = 0 and ipid in(" . $fnipidval . ") and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
								concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))")
						->orderby('status');

					$droparray2 = $patient1->fetchArray();
				}
			}

			if(is_array($droparray2) || is_array($droparray1))
			{
				$res = array_merge((array) $droparray2, (array) $droparray1);

				$res_ipids[] = '99999999';
				foreach($res as $k_res => $v_res)
				{
					$res_ipids[] = $v_res['ipid'];
				}

				//get patients healthinsurance
				$phelathinsurance = new PatientHealthInsurance();
				$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($res_ipids);


				foreach($healthinsu_array as $k_health_insu => $v_health_insu)
				{
					$healthinsu_arr[$v_health_insu['ipid']] = $v_health_insu;
				}

				$terminal_extra = new TerminalExtra();
				$patients_extra_data = $terminal_extra->get_patients_extra_data($res_ipids);

				foreach($res as $i => $v_res)
				{
					$res[$i]['status'] = $v_res['status'];

					//middle name
					if(strlen($res[$i]['middle_name']) > 0)
					{
						$res[$i]['middle_name'] = $v_res['middle_name'];
					}
					else
					{
						$res[$i]['middle_name'] = " ";
					}

					//admission date
					if($res[$i]['admission_date'] != '0000-00-00 00:00:00')
					{
						$res[$i]['admission_date'] = date('d.m.Y', strtotime($v_res['admission_date']));
					}
					else
					{
						$res[$i]['recording_date'] = "-";
					}

					//recording date
					if($res[$i]['recording_date'] != '0000-00-00 00:00:00')
					{
						$res[$i]['recording_date'] = date('d.m.Y', strtotime($v_res['recording_date']));
					}
					else
					{
						$res[$i]['recording_date'] = "-";
					}

					//birth date
					if($res[$i]['birthd'] != $hidemagic)
					{
						if($res[$i]['birthd'] != '0000-00-00 00:00:00')
						{
							$res[$i]['birthd'] = date('d.m.Y', strtotime($v_res['birthd']));
						}
						else
						{
							$res[$i]['birthd'] = "-";
						}
					}

					//patient health insurance
					$res[$i]['hi_name'] = $healthinsu_arr[$v_res['ipid']]['company_name'];
					$res[$i]['kassennummer'] = $healthinsu_arr[$v_res['ipid']]['kvk_no'];
					$res[$i]['versichertennummer'] = $healthinsu_arr[$v_res['ipid']]['insurance_no'];

					$res[$i]['decid'] = $v_res['id'];
					$res[$i]['id'] = str_replace('=', '', Pms_Uuid::encrypt($v_res['id']));

					//terminal import extra data
					$res[$i]['wop'] = $patients_extra_data[$v_res['ipid']]['wop'];
					$res[$i]['rsa'] = $patients_extra_data[$v_res['ipid']]['rsa'];
					$res[$i]['rechtskreis'] = $patients_extra_data[$v_res['ipid']]['legal_family'];
					$res[$i]['country'] = $patients_extra_data[$v_res['ipid']]['country'];
					$res[$i]['card_valid_till'] = $patients_extra_data[$v_res['ipid']]['card_expiration_date'];
				}
				$this->view->droparray = $res;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function comparepatientAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$import_session = new Zend_Session_Namespace('importSession');

			$hidemagic = Zend_Registry::get('hidemagic');
			$max_col = 18;
			$csv_labels = Pms_CommonData::terminal_import_csv_labels($max_col);

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			//reqired vars
			if(strlen($_REQUEST['row']) != '0')
			{
				$csv_row = $_REQUEST['row'];
			}

			if(strlen(trim(rtrim($_REQUEST['delimiter']))) == '0')
			{
				$delimiter = ';';
			}
			else
			{
				$delimiter = $_REQUEST['delimiter'];
			}

			if(!empty($_REQUEST['patient']))
			{
				$patient_id = $_REQUEST['patient'] . '=';
				$patient_decid = Pms_Uuid::decrypt($patient_id);
			}

			if($import_session->userid == $userid)
			{
				if(in_array($patient_decid, $import_session->target_patient) && array_key_exists($csv_row, $import_session->import_value))
				{
					$session_data = $import_session->import_value[$csv_row];
				}
			}

			//load required data
			$csv_data = $this->get_csv_line($csv_row, $delimiter);
			$patient_recipient = $this->get_patient_compare_data($patient_id, $clientid);

			//sent it to the view
			$this->view->csv_data = $csv_data;
			$this->view->csv_labels = $csv_labels;
			$this->view->patient_data = $patient_recipient;
			$this->view->session_saved_data = $session_data;

			$this->view->curent_csv_row = $csv_row;
		}

		private function get_csv_line($row, $delimiter = false)
		{
			//enable proper line endings detection
			ini_set("auto_detect_line_endings", true);
			setlocale(LC_ALL, 'de_DE.UTF8');

			$dir = "uploadfile/";
			$filename = $dir . $_SESSION['filename'];

			if(!is_writable($filename))
			{
				$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
				$log = new Zend_Log($writer);
				$log->info('Ajax load of imported file Procedure Error => ' . $filename . " does not exist!.");
				$error = 1;
			}
			if($delimiter === false)
			{
				$delimiter = ';';
			}

			if($error == 0)
			{
				$handle = fopen($filename, "r");

				//parse csv into an array
				while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
				{
					foreach($data as $k_data => $v_data)
					{
						$data[$k_data] = htmlspecialchars($v_data);
					}
					$csv_data[] = $data;
				}
				fclose($handle);
			}

			if($csv_data[$row])
			{
				return $csv_data[$row];
			}
			else
			{
				return false;
			}
		}

		private function get_patient_compare_data($pid, $clientid)
		{
			//decrypt pid
			$pid = Pms_Uuid::decrypt($pid);

			//get patient data
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
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
				$sql = "e.ipid,e.epid,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				$sql .= "IF(p.isadminvisible = 1,birthd,'" . $hidemagic . "') as birthd, ";
			}

			$q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->where('id = "' . $pid . '"')
				->andWhere('e.clientid = "' . $clientid . '"')
				->andWhere('isdelete = 0')
				->andWhere('isstandbydelete = 0')
				->limit('1');
			$q_res = $q->fetchArray();


			if($q_res)
			{
				//get patient extra-data
				$extra_data = TerminalExtra::get_patients_extra_data(array($q_res[0]['EpidIpidMapping']['ipid']));
				$q_res[0]['extra_data'] = $extra_data[$q_res[0]['EpidIpidMapping']['ipid']];
				if(strlen($q_res[0]['extra_data']['card_read_date']) > '0' && $q_res[0]['extra_data']['card_read_date'] != '0000-00-00 00:00:00' && date('Ymd', strtotime($q_res[0]['extra_data']['card_read_date'])) != '19700101')
				{
					$card_read_date = date('d.m.Y', strtotime($q_res[0]['extra_data']['card_read_date']));
				}
				else
				{
					$card_read_date = '';
				}

				//get patient health insurance
				$phelathinsurance = new PatientHealthInsurance();
				$healthinsu_array = $phelathinsurance->get_patients_healthinsurance(array($q_res[0]['EpidIpidMapping']['ipid']));


				$q_res[0]['health_insurance_data'] = $healthinsu_array[0];



				$patient_res[] = $q_res[0]['health_insurance_data']['company_name'];
				$patient_res[] = $q_res[0]['health_insurance_data']['kvk_no'];
				$patient_res[] = $q_res[0]['health_insurance_data']['insurance_no'];

				if(strlen($q_res[0]['extra_data']['wop']) > '0')
				{
					$patient_res[] = $q_res[0]['extra_data']['wop'];
				}
				else
				{
					$patient_res[] = '';
				}


				if(strlen($q_res[0]['extra_data']['rsa']) > '0')
				{
					$patient_res[] = $q_res[0]['extra_data']['rsa'];
				}
				else
				{
					$patient_res[] = '';
				}

				if(strlen($q_res[0]['extra_data']['legal_family']) > '0')
				{
					$patient_res[] = $q_res[0]['extra_data']['legal_family'];
				}
				else
				{
					$patient_res[] = '';
				}

				$patient_res[] = $q_res[0]['title'];
				$patient_res[] = $q_res[0]['firstname'];
				$patient_res[] = $q_res[0]['middlename'];
				$patient_res[] = $q_res[0]['lastname'];
				$patient_res[] = implode('.', array_reverse(explode('-', $q_res[0]['birthd'])));
				$patient_res[] = $q_res[0]['street1'];

				if(strlen($q_res[0]['extra_data']['country']) > '0')
				{
					$patient_res[] = $q_res[0]['extra_data']['country'];
				}
				else
				{
					$patient_res[] = '';
				}

				$patient_res[] = $q_res[0]['zip'];
				$patient_res[] = $q_res[0]['city'];

				if(strlen($q_res[0]['extra_data']['card_expiration_date']) > '0' && $q_res[0]['extra_data']['card_expiration_date'] != '0000-00-00')
				{
					$patient_res[] = implode('.', array_reverse(explode('-', $q_res[0]['extra_data']['card_expiration_date'])));
				}
				else
				{
					$patient_res[] = '';
				}
				$patient_res[] = $card_read_date;
				$patient_res[] = $q_res[0]['extra_data']['approve_number'];

				return $patient_res;
			}
			else
			{
				return false;
			}
		}

		public function saveimportsessionAction()
		{
			$this->_helper->viewRenderer->setNoRender();

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;


			$import_session = new Zend_Session_Namespace('importSession');
			$import_session->userid = $userid;
			$import_session->filename = $_SESSION['filename'];
			if(!is_array($import_session->target_patient))
			{
				$import_session->target_patient = array();
			}

			if(!is_array($import_session->import_value))
			{
				$import_session->import_value = array();
			}

			foreach($_REQUEST['target_patient'] as $k_tp_row => $v_patient_id)
			{
				$import_session->target_patient[$k_tp_row] = Pms_Uuid::decrypt($v_patient_id . '=');
			}

			foreach($_REQUEST['import_value'] as $k_iv_row => $v_patient_data)
			{
				$import_session->import_value[$k_iv_row] = $v_patient_data;
			}

			foreach($_REQUEST['import_type'] as $k_it_row => $v_import_type)
			{
				//clear old selected patient
				if($v_import_type > '1')
				{
					unset($import_session->target_patient[$k_iv_row]);
					unset($import_session->import_value[$k_iv_row]);
				}
				//then insert the import type option
				$import_session->import_type[$k_it_row] = $v_import_type;
			}
			exit;
		}

		public function loadsgbvactionsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			if($_REQUEST['type'] == 'cf')
			{
				$this->_helper->viewRenderer('loadsgbvactionscf');
			}


			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$phelathinsurance = new PatientHealthInsurance();
			$socialcode_price = new SocialCodePriceList();
			$patient_sgbv_actions = new PatientCustomActions();

			if(!empty($_POST['id']))
			{
				$id = trim(rtrim($_POST['id']));
			}

			if(!empty($_POST['date']))
			{
				$start_date_ts = strtotime(trim(rtrim($_POST['date'])));
			}

			//contact form edit
			if(!empty($_POST['formid']))
			{
				$formid = trim(rtrim($_POST['formid']));
			}

			//sgbv form edit
			if(!empty($_POST['sgbv_form_id']))
			{
				$sgbv_form_id = trim(rtrim($_POST['sgbv_form_id']));
			}

			if(!empty($_POST['end_date']))
			{
				$end_date_ts = strtotime(trim(rtrim($_POST['end_date'])));
			}

			$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($id));

			$sgbv_actions = new SocialCodeActions();
			$client_sgbv_actions = $sgbv_actions->getCientSocialCodeActions($clientid);

			$this->view->sgbv_client_actions = $client_sgbv_actions;

			foreach($client_sgbv_actions as $kh => $csgbva)
			{
				$sgbv_action_details[$csgbva['id']]['action_name'] = $csgbva['action_name'];
			}
			$this->view->sgbv_action_details = $sgbv_action_details;


			//get patient healthinsurance
			$patient_healthinsurance = $phelathinsurance->getPatientHealthInsurance($ipid);


			//get pricegroup from master health insurance
			if($patient_healthinsurance)
			{
				$health_insurance_id = $patient_healthinsurance[0]['companyid'];

				$hi_query = Doctrine_Query::create()
					->select('price_sheet, price_sheet_group')
					->from('HealthInsurance')
					->where("id='" . $health_insurance_id . "'");
				$hi_array = $hi_query->fetchArray();

				if(!empty($hi_array))
				{
					$price_sheet_group = $hi_array[0]['price_sheet_group'];

					if($price_sheet_group == '0')
					{
// 						echo json_encode(array('error' => $this->view->translate('no_group_selected_hi_master')));
						exit;
					}

					$used_actions = array();
					if(strlen($formid) > 0)
					{
						$form_sgbv_items = new FormBlockSgbv();
						$saved_actions = $form_sgbv_items->getAllPatientFormSavedActions($ipid, $formid, false); // ALL ACTIONS THAT WHERE SAVED IN CONTACT FORM -
						foreach($saved_actions as $kh => $aval)
						{
							$used_actions[] = $aval['action_id'];
						}
					}
					else if(strlen($sgbv_form_id) > '0')
					{

						$sgbv_form_items_master = new SgbvFormsItems();
						$sgbv_items_details = $sgbv_form_items_master->getPatientSgbvFormItems($ipid, $sgbv_form_id); //ALL ACTIONS THAT ARE SAVED IN SGBV
						foreach($sgbv_items_details as $k_val => $v_val)
						{
							$used_actions[] = $v_val['action_id'];
							$sgbv_items_det[$v_val['action_id']] = $v_val;
						}
					}
					$period_pricelist['start'] = date('Y-m-d', $start_date_ts);

					if($end_date_ts)
					{
						$period_pricelist['end'] = date('Y-m-d', $end_date_ts);
					}
					else
					{
						$period_pricelist['end'] = date('Y-m-' . date('t', $start_date_ts), $start_date_ts);
					}

					$price_sheet = $socialcode_price->get_group_period_pricelist($price_sheet_group, $clientid, $period_pricelist, true);

					if($price_sheet == '0')
					{
						exit;
					}

					$sgbv_actions_patient = $patient_sgbv_actions->getAllSgbvActionsPatient($clientid, $ipid, $price_sheet, $used_actions); // insert also used and deleted actions

					if(strlen($formid) > '0')
					{
						$sgbv_block = new FormBlockSgbv();
						$patient_sgbv = $sgbv_block->getPatientFormBlockSgbv($ipid, $formid);

						foreach($sgbv_actions_patient as $k_sgbv => $v_sgbv)
						{
							$sgbv_actions_patient[$k_sgbv]['selected'] = $patient_sgbv[$v_sgbv['id']];
						}
					}
					else if(strlen($sgbv_form_id) > '0')
					{
						foreach($sgbv_actions_patient as $k_sgbv => $v_sgbv)
						{
							if(in_array($v_sgbv['id'], $used_actions))
							{
								$sgbv_actions_patient[$k_sgbv]['selected'] = '1';
							}
							else
							{
								$sgbv_actions_patient[$k_sgbv]['selected'] = '0';
							}

							if($sgbv_items_det[$v_sgbv['id']])
							{
								$sgbv_actions_patient[$k_sgbv]['per_day'] = $sgbv_items_det[$v_sgbv['id']]['per_day'];
								$sgbv_actions_patient[$k_sgbv]['per_week'] = $sgbv_items_det[$v_sgbv['id']]['per_week'];
								$sgbv_actions_patient[$k_sgbv]['valid_from'] = date('d.m.Y', strtotime($sgbv_items_det[$v_sgbv['id']]['valid_from']));
								$sgbv_actions_patient[$k_sgbv]['valid_till'] = date('d.m.Y', strtotime($sgbv_items_det[$v_sgbv['id']]['valid_till']));
								$sgbv_actions_patient[$k_sgbv]['free_of_charge'] = $sgbv_items_det[$v_sgbv['id']]['free_of_charge'];
							}
						}
					}
				}
				else
				{
					//exit;
				    $sgbv_actions_patient =  array();
				}
			}
			else
			{
				//exit;
				$sgbv_actions_patient =  array();
			}

			$this->view->sgbv_actions_patient = $sgbv_actions_patient;
		}

		
		public function loadgoaiiactionsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			
			if(!empty($_POST['id']))
			{
				$id = trim(rtrim($_POST['id']));
			}

			$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($id));
				
			if(!empty($_POST['date']))
			{
				$start_date_ts = strtotime(trim(rtrim($_POST['date'])));
				$form_block_settings_date = date("Y-m-d",strtotime(trim(rtrim($_POST['date']))));
			}

			//contact form edit
			if(!empty($_POST['formid']))
			{
				$formid = trim(rtrim($_POST['formid']));
				
				$goaii_block = new FormBlockGoaii();
				$patient_goaii = $goaii_block->getPatientFormBlockGoaii($ipid, $formid);
				$this->view->patient_goaii_values = $patient_goaii;
			}

			if(!empty($_POST['end_date']))
			{
				$end_date_ts = strtotime(trim(rtrim($_POST['end_date'])));
			}

			$upload_date = "29.08.2016";
			
			if(strtotime($form_block_settings_date) < strtotime($upload_date)){
			    
    			$blocks_settings = new FormBlocksSettings();
    		    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid,$form_block_settings_date);
    		    
    		    foreach($blocks_settings_array as $k=>$kf)
    		    {
    	            $block_actions[$kf['block']][] = $kf;
    		    }
			}
			else
			{
			    /* ------------------  GET  XBDT GOAII block DETAILS ISPC-1779  ------------------------ */
			    $xbdt_goaii_block = new FormBlockXbdtGoaii();
			    $patient_xbdt_goaii = $xbdt_goaii_block->getPatientFormBlockXbdtGoaii($ipid, $formid);
			    $this->view->patient_xbdt_goaii_values = $patient_xbdt_goaii;
			    
    		    $xam = new XbdtActions();
    		    $xa_array = $xam->client_xbdt_actions($clientid,true,$only_cf_available=true);
    		    
    		    foreach($xa_array as $key => $value)
    		    {
    		        $block_actions['goaii'][$value['id']] = $value;
    		        $block_actions['goaii'][$value['id']]['option_name'] = $value['name'];
    		    }
			}
		    
			if($block_actions['goaii'])
			{
    			$this->view->goaii_actions =  $block_actions['goaii'];
			}
			else
			{
				exit;
			}

			$this->view->sgbv_actions_patient = $sgbv_actions_patient;
		}
		

		public function loadxbdtgoaiiactionsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			
			if(!empty($_POST['id']))
			{
				$id = trim(rtrim($_POST['id']));
			}

			$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($id));
				
			if(!empty($_POST['date']))
			{
				$start_date_ts = strtotime(trim(rtrim($_POST['date'])));
				$form_block_settings_date = date("Y-m-d",strtotime(trim(rtrim($_POST['date']))));
			} else{
				$form_block_settings_date = date("Y-m-d",time());
			}

			//contact form edit
			if(!empty($_POST['formid']))
			{
				$formid = trim(rtrim($_POST['formid']));
				
				$goaii_block = new FormBlockGoaii();
				$patient_goaii = $goaii_block->getPatientFormBlockGoaii($ipid, $formid);
				$this->view->patient_goaii_values = $patient_goaii;
				
				
				$xbdt_goaii_block = new FormBlockXbdtGoaii();
				$patient_xbdt_goaii = $xbdt_goaii_block->getPatientFormBlockXbdtGoaii($ipid, $formid);
				$this->view->patient_xbdt_goaii_values = $patient_xbdt_goaii;
				
			}

			if(!empty($_POST['end_date']))
			{
				$end_date_ts = strtotime(trim(rtrim($_POST['end_date'])));
			}

			$upload_date = "29.08.2016";
			
			$modules = new Modules();
			if($modules->checkModulePrivileges("135", $clientid)) 
			{
			    $use_xbdt_actions = 1;
			}
			else
			{
			    $use_xbdt_actions = 0;
			}
			
			if($use_xbdt_actions == "1"){
			
    			//NEW  first check date - and load  for start date
    			if(strtotime($form_block_settings_date) < strtotime($upload_date)){
    			    // load -goa
    			    $blocks_settings = new FormBlocksSettings();
    			    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid,$form_block_settings_date,"goaii");
    			    
    			    foreach($blocks_settings_array as $k=>$kf)
    			    {
    			        $block_actions[$kf['block']][$kf['id']] = $kf;
    			        $block_actions[$kf['block']][$kf['id']]['block_option_id'] = $kf['id'];
    			        $block_actions[$kf['block']][$kf['id']]['source'] = "goaii";
    			    }
    			    
    			} else {
    			    
    			    // load xbdt
    			    $xam = new XbdtActions();
    			    $xa_array = $xam->client_xbdt_actions($clientid,true,$only_cf_available=true,"goaii");
    			    
    			    foreach($xa_array as $key => $value)
    			    {
    			        $block_actions['goaii'][$value['id']] = $value;
    			        $block_actions['goaii'][$value['id']]['option_id'] = $value['action_id'];
    			        $block_actions['goaii'][$value['id']]['option_name'] = $value['name'];
    			        $block_actions['goaii'][$value['id']]['source'] = "xbdt";
    			        $block_actions['goaii'][$value['id']]['block_option_id'] = $value['block_option_id'];
    			    }
    			    
    			}
			} else{ 
			    
			    $blocks_settings = new FormBlocksSettings();
			    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid,false,"goaii");
			    
			    foreach($blocks_settings_array as $k=>$kf)
			    {
			            $block_actions[$kf['block']][$kf['id']] = $kf;
    			        $block_actions[$kf['block']][$kf['id']]['block_option_id'] = $kf['id'];
    			        $block_actions[$kf['block']][$kf['id']]['source'] = "goaii";
			    }
			}
			
			
// 			print_r($block_actions);exit;
			if($block_actions['goaii'])
			{
    			$this->view->goaii_actions =  $block_actions['goaii'];
			}
			else
			{
				exit;
			}

			$this->view->sgbv_actions_patient = $sgbv_actions_patient;
		}

		
		
		/*
		 * TODO-1414
		 */
		public function loadxbdtebmiiactionsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			
			if(!empty($_POST['id']))
			{
				$id = trim(rtrim($_POST['id']));
			}

			$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($id));
				
			
			if(!empty($_POST['date']))
			{
				$start_date_ts = strtotime(trim(rtrim($_POST['date'])));
				$form_block_settings_date = date("Y-m-d",strtotime(trim(rtrim($_POST['date']))));
			} else{
				$form_block_settings_date = date("Y-m-d",time());
			}

			//contact form edit
			if(!empty($_POST['formid']))
			{
				$formid = trim(rtrim($_POST['formid']));
				
				$ebmii_block = new FormBlockEbmii();
				$patient_ebmii = $ebmii_block->getPatientFormBlockEbmii($ipid, $formid);
				$this->view->patient_ebmii_values = $patient_ebmii;
				
				
				$xbdt_ebmii_block = new FormBlockXbdtEbmii();
				$patient_xbdt_ebmii = $xbdt_ebmii_block->getPatientFormBlockXbdtEbmii($ipid, $formid);
				$this->view->patient_xbdt_ebmii_values = $patient_xbdt_ebmii;
				
			}

			if(!empty($_POST['end_date']))
			{
				$end_date_ts = strtotime(trim(rtrim($_POST['end_date'])));
			}

			$upload_date = "06.03.2018";
			
			$modules = new Modules();
			if($modules->checkModulePrivileges("162", $clientid)) 
			{
			    $use_xbdt_actions = 1;
			}
			else
			{
			    $use_xbdt_actions = 0;
			}
			
			if($use_xbdt_actions == "1"){
			
    			//NEW  first check date - and load  for start date
    			if(strtotime($form_block_settings_date) < strtotime($upload_date)){
    			    // load -goa
    			    $blocks_settings = new FormBlocksSettings();
    			    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid,$form_block_settings_date,"ebmii");
    			    
    			    foreach($blocks_settings_array as $k=>$kf)
    			    {
    			        $block_actions[$kf['block']][$kf['id']] = $kf;
    			        $block_actions[$kf['block']][$kf['id']]['block_option_id'] = $kf['id'];
    			        $block_actions[$kf['block']][$kf['id']]['source'] = "ebmii";
    			    }
    			    
    			} else {
    			    
    			    // load xbdt
    			    $xam = new XbdtActions();
    			    $xa_array = $xam->client_xbdt_actions($clientid,true,$only_cf_available=true,"ebmii");
    			    
    			    foreach($xa_array as $key => $value)
    			    {
    			        $block_actions['ebmii'][$value['id']] = $value;
    			        $block_actions['ebmii'][$value['id']]['option_id'] = $value['action_id'];
    			        $block_actions['ebmii'][$value['id']]['option_name'] = $value['name'];
    			        $block_actions['ebmii'][$value['id']]['source'] = "xbdt";
    			        $block_actions['ebmii'][$value['id']]['block_option_id'] = $value['block_option_id'];
    			    }
    			    
    			}
			} else{ 
			    
			    $blocks_settings = new FormBlocksSettings();
			    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid,false,"ebmii");
			    
			    foreach($blocks_settings_array as $k=>$kf)
			    {
			            $block_actions[$kf['block']][$kf['id']] = $kf;
    			        $block_actions[$kf['block']][$kf['id']]['block_option_id'] = $kf['id'];
    			        $block_actions[$kf['block']][$kf['id']]['source'] = "ebmii";
			    }
			}
			
			
			if($block_actions['ebmii'])
			{
    			$this->view->ebmii_actions =  $block_actions['ebmii'];
			}
			else
			{
				exit;
			}
		}
		

		public function revertbtmmedicationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			//check if we have course id
			if(strlen($_POST['course']) > '0' && $_POST['course'] > '0')
			{
				$courses = explode(',', $_POST['course']);

				if(count($courses) > '0')
				{
					$course_ids = $courses;
				}
				else
				{
					exit;
				}
			}

			if($course_ids)
			{
				$patient_course = new PatientCourse();

				$removed_course_details = $patient_course->get_course_details($course_ids);

				foreach($removed_course_details as $v_course_data)
				{
					if($v_course_data['isserialized'] == '1')
					{
						$data_ids = unserialize($v_course_data['recorddata']);
					}

					if($v_course_data['wrong'] == '1')
					{
						$status = '1'; //mark entries as deleted = 1
					}

					$pat_ipid = $v_course_data['ipid'];
					$medication_id = $data_ids['medicationid'];


					$stock_reverted[$v_course_data['id']] = 0;
					$stock_details = '';
					if($data_ids['patient_stock_id'] > '0')
					{
						$patient_stock = new MedicationPatientHistory();
						$stock_details = $patient_stock->get_stock_entry_details($data_ids['patient_stock_id']);

						if($stock_details['ipid'] == $v_course_data['ipid'])
						{
							$reduced_amount = $stock_details['amount'];
							$patient_stock_details = $patient_stock::get_patient_stock($clientid, $pat_ipid, $medication_id);

							if(($patient_stock_details[0]['total_amount'] - ($reduced_amount)) >= '0')
							{
								//MedicationPatientHistory
								$save_medi_pat_hist = MedicationPatientHistory::toggle_stock_status($data_ids['patient_stock_id'], $clientid);
								$stock_reverted[$v_course_data['id']] = 1;
							}
						}
					}
					// we require userid and amount of stock_id
					$stock_details = '';
					if($data_ids['client_history_id'] > '0')
					{
						$client_stock = new MedicationClientHistory();
						$stock_details = $client_stock->get_stock_entry_details($data_ids['client_history_id']);

						if($stock_details['userid'] > '0')
						{
							$user_stock_details = $client_stock->get_user_stock($clientid, $stock_details['userid'], $medication_id);

							if(($user_stock_details[0]['total_amount'] - ($stock_details['amount'])) >= '0')
							{
								//	MedicationClientHistory
								$save_medi_client_hist = MedicationClientHistory::toggle_stock_status($data_ids['client_history_id'], $clientid);
								$stock_reverted[$v_course_data['id']] = 1;
							}
						}
					}
				}

				$all_stock_reverted = true;
				foreach($stock_reverted as $k_course => $v_course)
				{
					if($v_course != '1')
					{
						$all_stock_reverted = false;
					}
				}

				if($all_stock_reverted)
				{
					echo json_encode(array('success' => '1'));
					exit;
				}
				else
				{
					echo json_encode(array('error' => $this->view->translate('medi_not_reverted')));
					exit;
				}
			}
			else
			{
				exit;
			}
		}

		public function loadbtmformAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$this->view->userid = $userid;

			$btm_perms = new BtmGroupPermissions();
			$btm_permisions = $btm_perms->get_group_permissions($clientid, Usergroup::getMasterGroup($groupid));
			$this->view->lieferung_method = $btm_permisions['method_lieferung'];
			$this->view->btm_permisions = $btm_permisions;

			//check if we have course id
			if(strlen($_POST['id']) > '0')
			{
				$patid = $_POST['id'];

				$decid = Pms_Uuid::decrypt($patid);
				$ipid = Pms_CommonData::getIpid($decid);
			}
			else
			{
				exit;
			}

			//get all client users START
			$users = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('id = "' . $userid . '"')
				->andWhere('clientid = ' . $clientid . '');
			$groupusers = $users->fetchArray();

			//prepare users array..
			$usersarray[] = '99999999';
			foreach($groupusers as $user)
			{
				$usersarray[] = $user['id'];
				$doctorusers[$user['id']] = $user['user_title'] . " " . $user['first_name'] . ", " . $user['last_name'];
			}
			asort($doctorusers);


			//get all client users END

			$stocks = new MedicationClientStock();
			$stksarray = $stocks->getAllMedicationClientStock($clientid);

			$medis_arr[] = '99999999';
			foreach($stksarray as $stocmedis)
			{
				$stocmedications[$stocmedis['medicationid']] = $stocmedis;
				$medis_arr[] = $stocmedis['medicationid'];
			}

			$med = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('isdelete = 0 ')
				->andWhere('name!=""')
				->andWhereIn('id', $medis_arr)
				->andWhere('clientid = ' . $clientid . '');
			$medarray = $med->fetchArray();

			foreach($medarray as $medication)
			{
				if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
				{
					$medicationsarray[$medication['id']] = $medication;
					$medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
				}
			}

			//get data for users
			$btmbuch = new MedicationClientHistory();
			$btm = $btmbuch->getDataForUsers($clientid, $usersarray);

			//get patient BTM data START
			$medipat = new MedicationPatientHistory();
			$pat_history_arr = $medipat->getAllMedicationPatientHistory($clientid, $ipid);

			$pat_medis_arr[] = "99999999";

			foreach($pat_history_arr as $k_med => $pat_medication)
			{
				$patient_medications[$pat_medication['medicationid']] = $pat_medication;
				$pat_medis_arr[] = $pat_medication['medicationid'];
			}
			//get patient BTM data END

			foreach($btm as $record)
			{
				$btmuserdata[$record['userid']][$record['medicationid']] = $record;
			}


			foreach($medicationsarray as $keym => $medication)
			{
				foreach($usersarray as $keyu => $userid)
				{
					if($key_u != '99999999')
					{
						if($userid == $btmuserdata[$userid][$keym]['userid'] && $medication['id'] == $btmuserdata[$userid][$keym]['medicationid'])
						{
							$final_userdata['user'][$keym][$userid] = $btmuserdata[$userid][$keym]['total'];
						}
						else
						{
							$final_userdata['user'][$keym][$userid] = 0;
						}
					}
				}

				//append patient stock if exists
				if(array_key_exists($keym, $patient_medications))
				{
					$final_pat[$keym]['patient_stock'] = $patient_medications[$keym]['total_amount'];
				}
				else
				{
					$final_pat[$keym]['patient_stock'] = '0';
				}
//				if(($final_userdata['user'][$keym][$userid] >= '0' && $final_pat[$keym]['patient_stock'] >= '0') || $final_userdata['user'][$keym][$userid] > '0')
				//show only medications with stock > 0
				if($final_userdata['user'][$keym][$userid] > '0' || $final_pat[$keym]['patient_stock'] >= '0')
				{
					$medicationsarray[$keym]['users'] = $final_userdata['user'][$keym];
					$medicationsarray[$keym]['patient_stock'] = $final_pat[$keym]['patient_stock'];
				}
				else
				{
					unset($medicationsarray[$keym]);
				}
			}
			
			//ispc 1864 p.9
			//documenting a method BEFORE that SEAL_DATE is not possible.
			$mcss  = new MedicationClientStockSeal();
			$mcss_seal_date = $mcss->get_client_last_seal($clientid);
			if (!empty($mcss_seal_date['seal_date'])) {
				$this->view->btm_seal_date =  date("d.m.Y", strtotime($mcss_seal_date['seal_date']));
			} else {
				$this->view->btm_seal_date = date("d.m.Y", $mcss->get_default_seal_timestamp());
			}
			
			

//			print_r("Patient Medications\n");
//			print_r($patient_medications);
//
//			print_r("Final user data\n");
//			print_r($final_userdata);
//			print_r("Final patient data\n");
//			print_r($final_pat);
//
//			print_r("Final BTM data\n");
//			print_r($medicationsarray);

			$this->view->btm_perm = '1';
			$this->view->btm = $medicationsarray;
			$this->view->btm_users = $doctorusers;
			$this->view->btm_patient_hist = $pat_history_arr;
		}

		public function saverosterorderAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($this->getRequest()->isPost())
			{
				parse_str($_POST['groups_data'], $groups_data);
				parse_str($_POST['users_data'], $users_data);

				$user_sort = new Application_Form_RosterUsersOrder();
				$users_sort = $user_sort->insert_data($users_data, $clientid, $userid);
			}
			exit;
		}

		public function saverosteruserrowsAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($this->getRequest()->isPost())
			{
				parse_str($_POST['rows_data'], $data);

				$user_rows = new Application_Form_RosterClientUsersRows();
				$users_sort = $user_rows->insert_data($data['hidden_user_rows'], $clientid, $userid);
			}
			exit;
		}

		public function loadnewrosterdataAction()
		{
			setlocale(LC_ALL, 'de_DE.UTF8');
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$patient_master = new PatientMaster();


			$this->view->usertype = $logininfo->usertype;

			$modules = new Modules();
			if($modules->checkModulePrivileges("112", $clientid))//New roster - sum line 
			{
			    $this->view->sum_line = "1";
			} else
			{
			    $this->view->sum_line = "0";
			}
			
			//		1. get working month
			$this->view->options = Pms_CommonData::getMonths();
			if(strlen($_POST['month']) > 0)
			{
				$curent_month = $_POST['month'];
			}
			else if(strlen($_REQUEST['month']) > 0)
			{
				$curent_month = $_REQUEST['month'];
			}
			else
			{
				$curent_month = date("Y_m", time());
			}

			$this->view->curmonth = $curent_month;

			$month_start = str_replace("_", "-", $curent_month . "_01");
			$month_start_ts = strtotime(str_replace("_", "-", $curent_month . "_01"));

			$month_end = str_replace("_", "-", $curent_month . "_" . date('t', $month_start_ts));
			$month_end_ts = strtotime(str_replace("_", "-", $curent_month . "_" . date('t', $month_start_ts)));

			$month_days = $patient_master->getDaysInBetween($month_start, $month_end);
			$this->view->month_days = $month_days;


			if($this->getRequest()->isPost())
			{
				$docquery = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->where('clientid="' . $clientid . '"')
					->andWhere('isdelete="0"')
					->andWhere('isactive=1');
				$groups = $docquery->fetchArray();

				$groups_ids[] = '99999999';
				foreach($groups as $k_gr => $v_gr)
				{
					$groups_details[$v_gr['id']] = $v_gr;

					$groups_ids[] = $v_gr['id'];
				}
				$this->view->groups = $groups_details;

				$doc = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('isdelete=0')
					//->andWhere('isactive = "0"')
					->andWhereIn('groupid', $groups_ids);
				if($usertype != 'SA')
				{
					$doc->andWhere('clientid=' . $clientid);
					$doc->andWhere('usertype!="SA"');
				}
				$doc->orderBy('groupid, last_name ASC');
				$docarray = $doc->fetcharray();

				$roster_users_order = new RosterUsersOrder();
				$users_order = $roster_users_order->get_order($clientid, $userid);

				foreach($docarray as $k_doc => $v_doc)
				{
					$group2users[$v_doc['groupid']][0] = "";
					$group2users[$v_doc['groupid']][$v_doc['id']] = $v_doc['user_title'] . " " . $v_doc['last_name'] . ', ' . $v_doc['first_name'];


					$users['all'][$v_doc['id']] = $v_doc;

					if($v_doc['isdelete'] == '0')
					{
						$users['active'][] = $v_doc;
					}
					else
					{
						$users['deleted'][] = $v_doc;
					}
				}

				if($users_order)
				{
					foreach($users_order['users_order'] as $k_group => $v_group_users)
					{
						foreach($v_group_users as $k_user_order => $v_user)
						{
							if(array_key_exists($v_user, $group2users[$k_group]))
							{
								$sorted_group2users[$k_group][0] = '';
								$sorted_group2users[$k_group][$v_user] = $group2users[$k_group][$v_user];
							}
						}
					}

					//add newly added users which are not saved in users order
					foreach($docarray as $k_doc => $v_doc)
					{
						if(!array_key_exists($v_doc['id'], $sorted_group2users[$v_doc['groupid']]))
						{
							$sorted_group2users[$v_doc['groupid']][0] = '';
							$sorted_group2users[$v_doc['groupid']][$v_doc['id']] = $group2users[$v_doc['groupid']][$v_doc['id']];
						}
					}

					if(!empty($sorted_group2users))
					{
						$group2users = $sorted_group2users;
					}
				}

				$this->view->users = $users;

				$this->view->groupUsers = $group2users;

				//get client users row amount
				$client_users_rows = new RosterClientUsersRows();
				$users_rows_ammount = $client_users_rows->get_client_users_rows($clientid);
				$this->view->user_rows = $users_rows_ammount;

				//get all client shifts
				$c_shifts = new ClientShifts();
				//ISPC-2612 Ancuta 30.06.2020
				//$client_shifts = $c_shifts->get_client_shifts($clientid);// commented on 30.06.2020
                // Use function to get all shifts - for the saved data 
				$client_shifts = $c_shifts->get_all_shifts_details();//ISPC-2612 Ancuta 30.06.2020

				$shift_substitution = 0;
				foreach($client_shifts as $k_c_shift => $v_c_shift)
				{
					$client_shifts_arr[$v_c_shift['id']]['name'] = $v_c_shift['name'];
					$client_shifts_arr[$v_c_shift['id']]['color'] = $v_c_shift['color'];
					if(!empty($v_c_shift['shortcut']))
					{
						if(strlen($v_c_shift['shortcut']) > 3)
						{
							$client_shifts_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['shortcut'], 0, 3, "UTF-8");
						}
						else
						{
							$client_shifts_arr[$v_c_shift['id']]['shortcut'] = $v_c_shift['shortcut'];
						}
					}
					else
					{
						$client_shifts_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['name'], 0, 3, "UTF-8");
						$shift_substitution ++;
					}
				}

				$this->view->client_shifts_min = $client_shifts_arr;

				//		3. get roster saved data
				$docid = Doctrine_Query::create()
					->select('*')
					->from('Roster')
					->where('clientid = ' . $clientid)
					->andWhere("duty_date between '" . $month_start . "' and '" . $month_end . "'")
					->andWhere('isdelete = "0"');
				$roster_arr = $docid->fetchArray();

				foreach($roster_arr as $k_roster => $v_roster)
				{
					if($v_roster['userid'] != '0')
					{
						$master_roster_data[$v_roster['userid']][$v_roster['duty_date']][$v_roster['row']] = $v_roster['shift'];

						$group_shifts_count[$v_roster['user_group']][$v_roster['duty_date']][$v_roster['shift']]++;;
						$group_shifts[$v_roster['user_group']][$v_roster['shift']] = 0;
					}
				}

				$this->view->roster_saved_data = $master_roster_data;
// 				print_r($group_shifts_count);exit;
				
				$this->view->group_shifts = $group_shifts;
				$this->view->group_shifts_count = $group_shifts_count;

//national holidays -- just in case, latteget_period_monthsr might be required in roster
				$nh = new NationalHolidays();
				$national_holiday = $nh->getNationalHoliday($clientid, $month_start, true);

				foreach($national_holiday as $k_holiday => $v_holiday)
				{
					$holiday_dates[] = date('Y-m-d', strtotime($v_holiday['NationalHolidays']['date']));
				}

				$this->view->national_holidays = $holiday_dates;
			}
			else
			{
				exit;
			}
		}

		public function checkfalleditAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
 
			$new_date = date('Y-m-d', strtotime($_REQUEST['edit_date'])) . ' ' . date('H:i:s', strtotime($_REQUEST['edit_time']));
			$previous_date = $_REQUEST['previous_date'];
			$next_date = $_REQUEST['next_date'];

			$return['previous_date'] = date('d.m.Y H:i', strtotime($previous_date));
			$return['next_date'] = date('d.m.Y H:i', strtotime($next_date));

			if(strtotime($new_date) <= strtotime($previous_date))
			{
				$return['error'] = "1";
			}
			elseif(strtotime($new_date) >= strtotime($next_date))
			{
				$return['error'] = "2";
			}
			elseif(date("Y",strtotime($new_date)) < "2008")
			{
				$return['error'] = "3";
			}
			else
			{
				$return['error'] = "0";
			}

			echo json_encode($return);
			exit;
		}

		public function loadrpperformancedata_daAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');

			setlocale(LC_TIME, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$tabmenus = new TabMenus();
			$p_list = new PriceList();
			$form_types = new FormTypes();
			$sapvs = new SapvVerordnung();
			$patientmaster = new PatientMaster();
			$user = new User();
			$usergroups = new Usergroup();
			$patientdischarge = new PatientDischarge();
			$discharge_method = new DischargeMethod();

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);


			//get patient details
			$conditions['periods'][0]['start'] = '2009-01-01';
			$conditions['periods'][0]['end'] = date('Y-m-d');
			$conditions['client'] = $clientid;
			$conditions['ipids'] = array($ipid);
			$patient_days = Pms_CommonData::patients_days($conditions);

			//get patient active periods
			$all_patients_periods = array_values($patient_days[$ipid]['active_periods']);

			//get active period months
			$months = array();
			foreach($all_patients_periods as $k_period => $v_period)
			{
				$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], "Y-m");
				$months = array_merge($months, $period_months);
			}
			$months = array_values(array_unique($months));

			//sort months
			foreach($months as $k_m => $v_m)
			{
				$months_unsorted[strtotime($v_m)] = $v_m;
			}
			ksort($months_unsorted);
			$months = array_values(array_unique($months_unsorted));

			//calculate start_day, number_of_days, end_day for each month
			foreach($months as $k_month => $v_month)
			{
				if(!function_exists('cal_days_in_month'))
				{
					$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
				}
				else
				{
					$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
				}

				$months_details[$v_month]['start'] = $v_month . "-01";
				$months_details[$v_month]['days_in_month'] = $month_days;
				$months_details[$v_month]['end'] = $v_month . '-' . $month_days;

				$month_select_array[$v_month] = $v_month;
				$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
			}

			//check if a month is selected START
			if(empty($_REQUEST['list']) && strlen($list) == 0)
			{
				$selected_month = end($month_select_array);
			}
			else
			{
				if(strlen($list) == 0)
				{
					$list = $_REQUEST['list'];
				}
				$selected_month = $month_select_array[$list];
			}
			$this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
			//check if a month is selected END
//			//construct month_selector START
			$attrs['onChange'] = 'changeMonth(this.value);';
			$attrs['class'] = 'select_month_rpperformance';

			$this->view->months_selector = $this->view->formSelect("list", $selected_month, $attrs, $month_select_array);
//			//construct month_selector END
			//set current working period
			$current_period = $months_details[$selected_month];
			$current_period_days = $patientmaster->getDaysInBetween($current_period['start'], $current_period['end']);
			$this->view->current_period_days = $current_period_days;

			//get sapvs in period
			$patient_all_sapvs =$sapvs->get_all_sapvs($ipid);;
			
			$rp_obj = new RpControl();
			$prods = array();
			foreach($patient_all_sapvs as $k=>$sdata){
				$sapv_period['start'] = $sdata['verordnungam'];
				$sapv_period['end'] = $sdata['verordnungbis'];
				
				$prods = array();
				$rp_data[]  = $rp_obj->rp_invoice_sapv_period($ipid,$sdata['id'],$sapv_period,'form');
// 				$prods = array_merge($prods,$rp_data);
// 				$rp_data[$sdata['id']]['period'] =$sapv_period;
			}
			
			foreach($rp_data as $k=>$rp_shs){
				foreach($rp_shs as  $ksh=>$sh_days){
					$final_products[$ksh][] = $sh_days;
				}
			}
			
			foreach($final_products as $dh_arr =>$sh_dates_arr){
				foreach($sh_dates_arr as $k=>$dates){
					foreach($dates as $ymd=>$details){
						if(in_array($ymd,$current_period_days)){
							$final_products_array[$dh_arr][$ymd] = $details;
						}
					}
				}
			}
			
			//get default products pricelist
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();
			
			
			foreach($current_period_days as $k_sday => $v_sday)
			{
				if(empty($final_products_array[$v_sday])){
					foreach($shortcuts['rp'] as $k_short => $v_short)
					{
						$final_products_array[$v_short][$v_sday]['p_home'] = '0';
						$final_products_array[$v_short][$v_sday]['p_nurse'] = '0';
						$final_products_array[$v_short][$v_sday]['p_hospiz'] = '0';
					}
				}
			}
			$this->view->shortcuts = $shortcuts['rp'];
// 			print_r($final_products_array); exit;
// 			print_r($rp_data); exit;
// 			foreach($rp_data as $sapv_id=>$rp_data){
				
// 			}
// 			print_r(count($rp_data));exit;
// 			var_dump($rp_data); exit;
			
			
			/* $patient_sapvs = $sapvs->getSapvInPeriod($ipid, $current_period['start'], $current_period['end']);

			 
			
			
			
			
			
			
			
			
			
			$patient_discharge = $patientdischarge->getPatientDischarge($ipid);
			$discharge_dead_date = '';
			if($patient_discharge)
			{
				//get discharge methods
				$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);

				foreach($discharge_methods as $k_dis_method => $v_dis_method)
				{
					if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
					{
						$death_methods[] = $v_dis_method['id'];
					}
				}
				$death_methods = array_values(array_unique($death_methods));

				if(in_array($patient_discharge[0]['discharge_method'], $death_methods))
				{
					$discharge_dead_date = date('Y-m-d', strtotime($patient_discharge[0]['discharge_date']));
				}
			}

			//get patient locations and construct day2location_type arr
			$pat_locations = PatientLocation::get_period_locations($ipid, $current_period);

			foreach($pat_locations as $k_pat => $v_pat)
			{
				foreach($v_pat['all_days'] as $k_day => $v_day)
				{
					$pat_days2loctype[$v_day] = $v_pat['master_details']['location_type'];
				}
			}


			//get default products pricelist
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			$this->view->shortcuts = $shortcuts['rp'];

//			$invoice_date_start = $invoice_date_end = date('Y-m-d', time());
			foreach($patient_sapvs as $kpat_sapv => $vpat_sapv)
			{
				$pricel = PriceList::get_period_price_list(date('Y-m-d', strtotime($vpat_sapv['verordnungam'])), date('Y-m-d', strtotime($vpat_sapv['verordnungbis'])));
				if(empty($ppl))
				{
					$ppl = array();
				}

				$ppl = array_merge($ppl, $pricel);
			}

			//location type to price_type mapping
			$location_type_match = Pms_CommonData::get_rp_price_mapping();
 */

			//check if patient has saved data in db
			$saved_data = RpControl::get_rp_controlsheet($ipid, $current_period['start']);

			if(!$saved_data)
			{
				//apply changes
				
				
				$this->view->has_saved_data = '0';
				/* foreach($current_period_days as $k_sday => $v_sday)
				{
					foreach($shortcuts['rp'] as $k_short => $v_short)
					{
						$products[$v_short][$v_sday]['p_home'] = '0';
						$products[$v_short][$v_sday]['p_nurse'] = '0';
						$products[$v_short][$v_sday]['p_hospiz'] = '0';
					}
				}

				//GATHER CONTROL SHEET DATA START
				//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
				$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);

				foreach($rp_asses as $k_assessment => $v_assessment)
				{
					$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];

					if(strlen($location_matched_price) > 0)
					{
						$products['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] += '1';
					}
				}
				//Ebene 1 (reduziertes Assessment) - Not used yet
				//Ebene 2 - the daily added price when patient is active and has Verordnung

				foreach($patient_sapvs as $k_pat_sapv => $v_pat_sapv)
				{
					$sapvdays = $patientmaster->getDaysInBetween(date('Y-m-d', strtotime($v_pat_sapv['verordnungam'])), date('Y-m-d', strtotime($v_pat_sapv['verordnungbis'])));

					if(empty($sapv_days))
					{
						$sapv_days = array();
					}

					$sapv_days = array_merge($sapv_days, $sapvdays);
					$sapv_days = array_values(array_unique($sapv_days));
				}

				foreach($sapv_days as $k_sapv_day => $v_sapv_day)
				{
					$sapvday_loc_matched_price = $location_type_match[$pat_days2loctype[$v_sapv_day]];

					if(strlen($sapvday_loc_matched_price) > 0 && $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price] != '0.00')
					{
						$products['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price] += 1;
					}
				}

				//DOCTOR and NURSE VISITS - all
				//get used form types
				$form_types = new FormTypes();
				$set_one = $form_types->get_form_types($clientid, '1');
				foreach($set_one as $k_set_one => $v_set_one)
				{
					$set_one_ids[] = $v_set_one['id'];
				}

				//get doctor and nurse users
				//get all related users details
				$master_groups_first = array('4', '5');

				$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);

				foreach($client_user_groups_first as $k_group_f => $v_group_f)
				{
					$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
				}

				$client_users = $user->getClientsUsers($clientid);

				$nurse_users = array();
				$doctor_users = array();
				foreach($client_users as $k_cuser_det => $v_cuser_det)
				{
					$master_user_details[$v_cuser_det['id']] = $v_cuser_det;
					if(in_array($v_cuser_det['groupid'], $master2client['5']))
					{
						$nurse_users[] = $v_cuser_det['id'];
					}
					else if(in_array($v_cuser_det['groupid'], $master2client['4']))
					{
						$doctor_users[] = $v_cuser_det['id'];
					}
				}

				//get curent contact forms
				$contact_forms = $this->get_period_contact_forms($ipid, $current_period, false, true);

				$doctor_contact_forms = array();
				$nurse_contact_forms = array();

				foreach($contact_forms as $kcf => $day_cfs)
				{
					foreach($day_cfs as $k_dcf => $v_dcf)
					{
						$all_contact_forms[] = $v_dcf;
					}
				}

				foreach($all_contact_forms as $k_cf => $v_cf)
				{
					//visit date formated
					$visit_date = date('Y-m-d', strtotime($v_cf['date']));

					//switch shortcut_type based on patient location for *visit* date
					$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];

					//switch shortcut doctor/nurse
					$shortcut_switch = false;
					if(in_array($v_cf['create_user'], $doctor_users))
					{
						$shortcut_switch = 'doc';
					}
					else if(in_array($v_cf['create_user'], $nurse_users))
					{
						$shortcut_switch = 'nur';
					}

					//create products (doc||nurse)
					if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
					{
						if($ppl[$v_sapv_day][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
						{
							//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
							$products['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] += 1;
							if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
							{
								$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
							}
						}

						$shortcut = '';
						$qty[$vday_matched_loc_price_type] = '';
						//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
						if($v_cf['visit_duration'] >= '0')
						{
							$shortcut = 'rp_' . $shortcut_switch . '_1';
							$qty[$vday_matched_loc_price_type] = '1';
							if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
							{
								$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
							}
						}

						//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
						$multiplier = '';
						$qty[$vday_matched_loc_price_type] = '';
						if($v_cf['visit_duration'] > '45')
						{

							// calculate multiplier of 15 minutes after 60 min (round up)
							// ISPC-2006 :: From 60 was changed to 45
							// calculate multiplier of 15 minutes after 45 min (round up)
							$shortcut = 'rp_' . $shortcut_switch . '_3';
							$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
							$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value

							if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
							{
								$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
							}
						}

						$shortcut = '';
						$qty[$vday_matched_loc_price_type] = '';
						//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
						if($v_cf['visit_duration'] < '20')
						{
							$shortcut = 'rp_' . $shortcut_switch . '_4';
							$qty[$vday_matched_loc_price_type] = '1';

							if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
							{
								$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
							}
						}

						$shortcut = '';
						$qty[$vday_matched_loc_price_type] = '';
					}
				}

				//Fallabschluss - patient death coordination. added once (rp_pat_dead)
				if(strlen($discharge_dead_date) > 0)
				{
					//visit date formated
					$visit_date = date('Y-m-d', strtotime($discharge_dead_date));

					//switch shortcut_type based on patient location for *visit* date
					$dead_matched_loc_price_type = $location_type_match[$pat_days2loctype[$discharge_dead_date]];
					$qty[$vday_matched_loc_price_type] = '1';
					if($dead_matched_loc_price_type && $ppl[$v_sapv_day][0]['rp_pat_dead'][$dead_matched_loc_price_type] != '0.00')
					{
						$products['rp_pat_dead'][$v_sapv_day][$dead_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
					}
				} */
				//GATHER CONTROL SHEET DATA END
				
				$products = $final_products_array;
				
			}
			else
			{
				//use data saved in db
				$this->view->has_saved_data = '1';

				$products = $saved_data;
			}
			if($_REQUEST['dbgq'])
			{
				print_r($products);
				exit;
			}
			$this->view->products_data = $products;

			$html = Pms_Template::createTemplate($this->view, 'ajax/loadrpperformancedata.html');


			echo $html;
			exit;
		}

		
		
		public function loadrpperformancedataAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');

			setlocale(LC_TIME, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$tabmenus = new TabMenus();
			$p_list = new PriceList();
			$form_types = new FormTypes();
			$sapvs = new SapvVerordnung();
			$patientmaster = new PatientMaster();
			$user = new User();
			$usergroups = new Usergroup();
			$patientdischarge = new PatientDischarge();
			$discharge_method = new DischargeMethod();

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);



			//get patient details
			$conditions['periods'][0]['start'] = '2009-01-01';
			$conditions['periods'][0]['end'] = date('Y-m-d');
			$conditions['client'] = $clientid;
			$conditions['ipids'] = array($ipid);
			$patient_days = Pms_CommonData::patients_days($conditions);

			//get patient active periods
			$all_patients_periods = array_values($patient_days[$ipid]['active_periods']);

			//get active period months
			$months = array();
			foreach($all_patients_periods as $k_period => $v_period)
			{
				$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], "Y-m");
				$months = array_merge($months, $period_months);
			}
			$months = array_values(array_unique($months));

			//sort months
			foreach($months as $k_m => $v_m)
			{
				$months_unsorted[strtotime($v_m)] = $v_m;
			}
			ksort($months_unsorted);
			$months = array_values(array_unique($months_unsorted));

			//calculate start_day, number_of_days, end_day for each month
			foreach($months as $k_month => $v_month)
			{
				if(!function_exists('cal_days_in_month'))
				{
					$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
				}
				else
				{
					$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
				}

				$months_details[$v_month]['start'] = $v_month . "-01";
				$months_details[$v_month]['days_in_month'] = $month_days;
				$months_details[$v_month]['end'] = $v_month . '-' . $month_days;

				$month_select_array[$v_month] = $v_month;
				$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
			}

			//check if a month is selected START
			if(empty($_REQUEST['list']) && strlen($list) == 0)
			{
				$selected_month = end($month_select_array);
			}
			else
			{
				if(strlen($list) == 0)
				{
					$list = $_REQUEST['list'];
				}
				$selected_month = $month_select_array[$list];
			}
			$this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
			//check if a month is selected END
//			//construct month_selector START
			$attrs['onChange'] = 'changeMonth(this.value);';
			$attrs['class'] = 'select_month_rpperformance';

			$this->view->months_selector = $this->view->formSelect("list", $selected_month, $attrs, $month_select_array);
//			//construct month_selector END
			//set current working period
			$current_period = $months_details[$selected_month];
			$current_period_days = $patientmaster->getDaysInBetween($current_period['start'], $current_period['end']);
			$this->view->current_period_days = $current_period_days;

			//get sapvs in period
			$patient_sapvs = $sapvs->getSapvInPeriod($ipid, $current_period['start'], $current_period['end']);

			$patient_discharge = $patientdischarge->getPatientDischarge($ipid);
			$discharge_dead_date = '';
			if($patient_discharge)
			{
				//get discharge methods
				$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);

				foreach($discharge_methods as $k_dis_method => $v_dis_method)
				{
					if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
					{
						$death_methods[] = $v_dis_method['id'];
					}
				}
				$death_methods = array_values(array_unique($death_methods));

				if(in_array($patient_discharge[0]['discharge_method'], $death_methods))
				{
					$discharge_dead_date = date('Y-m-d', strtotime($patient_discharge[0]['discharge_date']));
					$discharge_dead_date_time = date('Y-m-d H:i:00', strtotime($patient_discharge[0]['discharge_date']));
				}
			}

			//get patient locations and construct day2location_type arr
			$pat_locations = PatientLocation::get_period_locations($ipid, $current_period);
			$pat_days2loctype = array();
			foreach($pat_locations as $k_pat => $v_pat)
			{
				if($v_pat['discharge_location'] == "0")
				{
					foreach($v_pat['all_days'] as $k_day => $v_day)
					{
						if(in_array(date("d.m.Y",strtotime($v_day)),$patient_days[$ipid]['real_active_days']) )
						{
							$pat_days2loctype[$v_day][] = $v_pat['master_details']['location_type'];
						}
					}
				}
			}
			// Maria:: Migration ISPC to CISPC 08.08.2020
			// TODO-2722 Ancuta 09.12.2019 - move patient location so locations according to client settings
			foreach($pat_days2loctype  as $loc_day => $day_loc_types ){

			    $del_val = "1";
			    if (  ! in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$ipid]['hospital']['real_days_cs']) && ($key = array_search($del_val, $day_loc_types)) !== false) {
			        unset($pat_days2loctype[$loc_day][$key]);
			    }

			    $del_val = "2";
			    if (  ! in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$ipid]['hospiz']['real_days_cs']) && ($key = array_search($del_val, $day_loc_types)) !== false) {
			        unset($pat_days2loctype[$loc_day][$key]);
			    }
			}



		    foreach($pat_days2loctype as $loc_day => $day_loc_types){
		        if (in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$ipid]['hospital']['real_days_cs']) ) {
		            $pat_days2loctype[$loc_day] = '1';
		        }
		        elseif (in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$ipid]['hospiz']['real_days_cs']) ) {
		            $pat_days2loctype[$loc_day] = '2';
		        } else{
		          $pat_days2loctype [$loc_day] = end($day_loc_types);
		        }

		    }
 			//--
		    if ($_REQUEST['ploc'] =='1'){
		        echo "<pre>";
		        print_r($pat_days2loctype);
		        print_r("\n");
		        print_r($pat_locations);
		    }
			//get default products pricelist
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			$this->view->shortcuts = $shortcuts['rp'];

			//get all sapvs
			$patient_sapvs_all = $sapvs->get_all_sapvs($ipid);
			
			foreach($patient_sapvs_all as $k=>$sv_data){
				$st_date = date('Y-m-d',strtotime($sv_data['verordnungam']));
				$sapv_period2type[$st_date] = $sv_data['verordnet'];
			}
			// check if there were sapv periods with only BE
			foreach($sapv_period2type as $per_start => $per_type )
			{
				if(strtotime($per_start)  < strtotime($current_period['start'])  && $per_type == "1")
				{
					$only_be_before[] =  $per_start ;
				} else {
					$execpt_be[] =  $per_start ;
				}
			}
			
			
			// if patient had an only be before
			$bill_assessment = 1;
			$bill_secondary_assessment = 0;
			if(isset($only_be_before) && !empty($only_be_before)){
				$admission_days = $patient_days[$ipid]['admission_days'];
					
				$last_only_be = end($only_be_before);
				$last_admission_date  = end($admission_days);
			
				if(strtotime($last_only_be) < strtotime($last_admission_date)){
					$from_sapv_be2patient_admision = $patientmaster->getDaysInBetween($last_only_be, $last_admission_date);
					if(count($from_sapv_be2patient_admision) < 28 ){
						// if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
						$bill_assessment = 0;
						$bill_secondary_assessment = 0;
			
					} else {
						//if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed
						$bill_assessment = 0;
						$bill_secondary_assessment = 1;
					}
				}
			}
			$curent_sapv_type = $patient_sapvs['0']['verordnet'];
			// get current sapc in period
// 			print_r($curent_sapv_type); 
			
			// get sapv type per day 
			
			// TODO-1801 :: start -> Added by Ancuta 13.09.2018  
			foreach($patient_sapvs_all as $k=>$v_sapv){
			    
			    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
			    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
			    $temp_sapv_verordnet[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);
			    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['types_str'] = $v_sapv['verordnet'];
			    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['types_arr'] = explode(',', $v_sapv['verordnet']);
			    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['highest'] = max($temp_sapv_verordnet[$v_sapv['ipid']]);
			    	
			    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['days'] = PatientMaster::getDaysInBetween($sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['start'], $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['end']);
			    array_walk($sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['days'], function(&$value) {
			        $value = date('d.m.Y', strtotime($value));
			    });
			    foreach($sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['days'] as $k=>$sd){
			        $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['day2type'][$sd] =  $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['types_str'];
			    }
			}
			
			$patient_sapvday2type = array();
			foreach($sapv_periods as $sipid=>$sdata){
			    foreach ($sdata as $s_id=>$svls){
			        foreach($svls['day2type'] as $day=>$type){
    			        $patient_sapvday2type[$sipid][$day]=$type;
			        }
			    }
			}
			// TODO-1801 :: end			
			
//			$invoice_date_start = $invoice_date_end = date('Y-m-d', time());
			foreach($patient_sapvs as $kpat_sapv => $vpat_sapv)
			{
				$pricel = PriceList::get_period_price_list(date('Y-m-d', strtotime($vpat_sapv['verordnungam'])), date('Y-m-d', strtotime($vpat_sapv['verordnungbis'])));
				if(empty($ppl))
				{
					$ppl = array();
				}

				$ppl = array_merge($ppl, $pricel);
			}

			//location type to price_type mapping
			$location_type_match = Pms_CommonData::get_rp_price_mapping();


			//check if patient has saved data in db
			$saved_data = RpControl::get_rp_controlsheet($ipid, $current_period['start']);

			if(!$saved_data)
			{
				$this->view->has_saved_data = '0';
				foreach($current_period_days as $k_sday => $v_sday)
				{
					foreach($shortcuts['rp'] as $k_short => $v_short)
					{
						$products[$v_short][$v_sday]['p_home'] = '0';
						$products[$v_short][$v_sday]['p_nurse'] = '0';
						$products[$v_short][$v_sday]['p_hospiz'] = '0';
					}
				}

				//GATHER CONTROL SHEET DATA START
				//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
				if($curent_sapv_type == "1"){
					$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
	
					if($rp_asses)
					{
						$location_matched_price = $location_type_match[$pat_days2loctype[$rp_asses[0]['completed_date']]];
	
						if(strlen($location_matched_price) > 0)
						{
							$products['rp_eb_1'][$rp_asses[0]['completed_date']][$location_matched_price] += '1';
						}
					}
				} else{
					if($bill_assessment == "1"){
						$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
		
						foreach($rp_asses as $k_assessment => $v_assessment)
						{
							$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
		
							if(strlen($location_matched_price) > 0)
							{
								$products['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] += '1';
							}
						}
					} else {
						if($bill_secondary_assessment == "1"){

							$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
							
							foreach($rp_asses as $k_assessment => $v_assessment)
							{
								$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
							
								if(strlen($location_matched_price) > 0)
								{
									$products['rp_eb_2'][$v_assessment['completed_date']][$location_matched_price] += '1';
								}
							}
						}
					}
				}
				//Ebene 1 (reduziertes Assessment) - Not used yet
				//Ebene 2 - the daily added price when patient is active and has Verordnung

				if($curent_sapv_type != "1"){
					foreach($patient_sapvs as $k_pat_sapv => $v_pat_sapv)
					{
						$sapvdays = $patientmaster->getDaysInBetween(date('Y-m-d', strtotime($v_pat_sapv['verordnungam'])), date('Y-m-d', strtotime($v_pat_sapv['verordnungbis'])));
	
						if(empty($sapv_days))
						{
							$sapv_days = array();
						}
	
						$sapv_days = array_merge($sapv_days, $sapvdays);
						$sapv_days = array_values(array_unique($sapv_days));
					}
	
					foreach($sapv_days as $k_sapv_day => $v_sapv_day)
					{
						$sapvday_loc_matched_price = $location_type_match[$pat_days2loctype[$v_sapv_day]];
	
						if(strlen($sapvday_loc_matched_price) > 0 && $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price] != '0.00')
						{
							$products['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price] += 1;
						}
					}
				}
				//DOCTOR and NURSE VISITS - all
				//get used form types
				$form_types = new FormTypes();
				$set_one = $form_types->get_form_types($clientid, '1');
				foreach($set_one as $k_set_one => $v_set_one)
				{
					$set_one_ids[] = $v_set_one['id'];
				}

				//get doctor and nurse users
				//get all related users details
				$master_groups_first = array('4', '5');

				$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);

				foreach($client_user_groups_first as $k_group_f => $v_group_f)
				{
					$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
				}

				$client_users = $user->getClientsUsers($clientid);

				$nurse_users = array();
				$doctor_users = array();
				foreach($client_users as $k_cuser_det => $v_cuser_det)
				{
					$master_user_details[$v_cuser_det['id']] = $v_cuser_det;
					if(in_array($v_cuser_det['groupid'], $master2client['5']))
					{
						$nurse_users[] = $v_cuser_det['id'];
					}
					else if(in_array($v_cuser_det['groupid'], $master2client['4']))
					{
						$doctor_users[] = $v_cuser_det['id'];
					}
				}

				//get curent contact forms
				$contact_forms = array();
				$contact_forms = $this->get_period_contact_forms($ipid, $current_period, false, false,true);

				$doctor_contact_forms = array();
				$nurse_contact_forms = array();

				$all_contact_forms = array();
				foreach($contact_forms as $kcf => $day_cfs) 
				{
					foreach($day_cfs as $k_dcf => $v_dcf)
					{
					    if(isset($v_dcf['id'])){
    						if(!empty($discharge_dead_date_time)){
    							if(strtotime($v_dcf['start_date']) <= strtotime($discharge_dead_date_time)){ // excude if the visit started after the discharge dead hour
    								$all_contact_forms[] = $v_dcf;
    							}
    						}
    						else
    						{
    							$all_contact_forms[] = $v_dcf;
    						}
					    }
// 						$all_contact_forms[] = $v_dcf;
					}
				}
				
				if($_REQUEST['show_cnts_prods'] =="1"){
				 
				    print_R("\n ");
 				    print_R($contact_forms);
 				    print_R("\n ");
				    print_R("\n all_contact_forms \n ");
 				    print_R($all_contact_forms);
 				    print_R("\n ");
				 
				}	
				if($curent_sapv_type == "1"){
					
					foreach($all_contact_forms as $k_cf => $v_cf)
					{
						//visit date formated
						$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
						$visit_date_dmY = date('d.m.Y', strtotime($v_cf['billable_date']));
	
						//switch shortcut_type based on patient location for *visit* date
						$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
	
						//switch shortcut doctor/nurse
						$shortcut_switch = false;
						if(in_array($v_cf['create_user'], $doctor_users))
						{
							$shortcut_switch = 'doc';
						}
						else if(in_array($v_cf['create_user'], $nurse_users))
						{
							$shortcut_switch = 'nur';
						}
	
						//create products (doc||nurse)
						if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
						{
						    if($patient_sapvday2type[$v_cf['ipid']][$visit_date_dmY] != "1"){// TODO-1801 13.09.2018 > The first sapv in period is BE but it got changed (ISPC-1997) - so visit must be billed if done in valid sapv non BE period

    							if(empty($billed_invoices_item[$shortcut_switch]) || !in_array( $v_cf['id'],$billed_invoices_item[$shortcut_switch]) ){
    								$billed_invoices_item[$shortcut_switch][] = $v_cf['id'];
    								
    								if($ppl[$v_sapv_day][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
    								{
    									//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
    									$products['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] += 1;
    									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
    									{
    										$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
    									}
    								}
    		
    								$shortcut = '';
    								$qty[$vday_matched_loc_price_type] = '';
    							}
						        
						    }else{
						        
    							if(empty($billed_invoices_item[$shortcut_switch])){
    								$billed_invoices_item[$shortcut_switch][]=$v_cf['id'];
    								
    								if($ppl[$v_sapv_day][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
    								{
    									//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
    									$products['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] += 1;
    									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
    									{
    										$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
    									}
    								}
    		
    								$shortcut = '';
    								$qty[$vday_matched_loc_price_type] = '';
    							}
						    }
						    
							//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
							if($v_cf['visit_duration'] >= '0')
							{
								if(in_array($v_cf['id'],$billed_invoices_item[$shortcut_switch])){
									
									$shortcut = 'rp_' . $shortcut_switch . '_1';
									$qty[$vday_matched_loc_price_type] = '1';
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
										$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
									}
								}
							}
	
							//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
							$multiplier = '';
							$qty[$vday_matched_loc_price_type] = '';
							if($v_cf['visit_duration'] > '45')
							{
								if(in_array($v_cf['id'],$billed_invoices_item[$shortcut_switch])){
									//calculate multiplier of 15 minutes after minute 61
									//ISPC-2006 :: From 60 was changed to 46 
									//calculate multiplier of 15 minutes after minute 46 
									$shortcut = 'rp_' . $shortcut_switch . '_3';
									$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
									$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value
		
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
										$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
									}
								}
							}
	
							$shortcut = '';
							$qty[$vday_matched_loc_price_type] = '';
							//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
							if($v_cf['visit_duration'] < '20')
							{
								$shortcut = 'rp_' . $shortcut_switch . '_4';
								$qty[$vday_matched_loc_price_type] = '1';
	
								if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
								{
									$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
								}
							}
	
							$shortcut = '';
							$qty[$vday_matched_loc_price_type] = '';
						}
					}
				} 
				else 
				{
					foreach($all_contact_forms as $k_cf => $v_cf)
					{
						//visit date formated
						$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
	
						//switch shortcut_type based on patient location for *visit* date
						$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
	
						//switch shortcut doctor/nurse
						$shortcut_switch = false;
						if(in_array($v_cf['create_user'], $doctor_users))
						{
							$shortcut_switch = 'doc';
						}
						else if(in_array($v_cf['create_user'], $nurse_users))
						{
							$shortcut_switch = 'nur';
						}
	
						//create products (doc||nurse)
						if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
						{
							if($ppl[$v_sapv_day][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
							{
								//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
								$products['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] += 1;
								if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
								{
									$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
								}
							}
	
							$shortcut = '';
							$qty[$vday_matched_loc_price_type] = '';
							//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
							if($v_cf['visit_duration'] >= '0')
							{
								$shortcut = 'rp_' . $shortcut_switch . '_1';
								$qty[$vday_matched_loc_price_type] = '1';
								if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
								{
									$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
								}
							}
	
							//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
							$multiplier = '';
							$qty[$vday_matched_loc_price_type] = '';
							if($v_cf['visit_duration'] > '45')
							{
								//calculate multiplier of 15 minutes after minute 61
								//ISPC-2006 :: From 60 was changed to 46
								//calculate multiplier of 15 minutes after minute 46
								
								$shortcut = 'rp_' . $shortcut_switch . '_3';
								$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
								$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value
	
								if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
								{
									$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
								}
							}
	
							$shortcut = '';
							$qty[$vday_matched_loc_price_type] = '';
							//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
							if($v_cf['visit_duration'] < '20')
							{
								$shortcut = 'rp_' . $shortcut_switch . '_4';
								$qty[$vday_matched_loc_price_type] = '1';
	
								if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
								{
									$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
								}
							}
	
							$shortcut = '';
							$qty[$vday_matched_loc_price_type] = '';
						}
					}
					
				}
				//Fallabschluss - patient death coordination. added once (rp_pat_dead)
				if(strlen($discharge_dead_date) > 0)
				{
					//visit date formated
					$visit_date = date('Y-m-d', strtotime($discharge_dead_date));

					//switch shortcut_type based on patient location for *visit* date
					$dead_matched_loc_price_type = $location_type_match[$pat_days2loctype[$discharge_dead_date]];
					$qty[$vday_matched_loc_price_type] = '1';
					if($dead_matched_loc_price_type && $ppl[$visit_date][0]['rp_pat_dead'][$dead_matched_loc_price_type] != '0.00')
					{
						$products['rp_pat_dead'][$visit_date][$dead_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
					}
				}
				//GATHER CONTROL SHEET DATA END
			}
			else
			{
				//use data saved in db
				$this->view->has_saved_data = '1';

				$products = $saved_data;
			}
			if($_REQUEST['dbgq'])
			{
			    print_r("curent_sapv_type:");
			    print_r($curent_sapv_type);
			    print_r("\n");
			    print_r("products:");
			    print_r("\n");
			    print_r($products);
			    print_r("\n");
			    print_r("all_contact_forms:");
			    print_r("\n");
				print_r($all_contact_forms);
				exit;
			}
			$this->view->products_data = $products;

			$html = Pms_Template::createTemplate($this->view, 'ajax/loadrpperformancedata.html');


			echo $html;
			exit;
		}

		private function get_period_contact_forms($ipid, $current_period, $sgbxi = false, $duration = false,$duration_after_death = false)
		{
			
			if($duration_after_death){
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				
				$patientdischarge = new PatientDischarge();
				$discharge_method = new DischargeMethod();
				$patient_discharge = $patientdischarge->getPatientDischarge($ipid);
				$discharge_dead_date = '';
				if($patient_discharge)
				{
					//get discharge methods
					$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
			
					foreach($discharge_methods as $k_dis_method => $v_dis_method)
					{
						if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
						{
							$death_methods[] = $v_dis_method['id'];
						}
					}
					$death_methods = array_values(array_unique($death_methods));
			
					if(in_array($patient_discharge[0]['discharge_method'], $death_methods))
					{
						$discharge_dead_date = date('Y-m-d', strtotime($patient_discharge[0]['discharge_date']));
						$discharge_dead_date_time = date('Y-m-d H:i:00', strtotime($patient_discharge[0]['discharge_date']));
					}
				}
			
			}
			
			$contact_from_course = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid ="' . $ipid . '"')
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
				->andWhere('source_ipid = ""')
				->orderBy('course_date ASC');

			$contact_v = $contact_from_course->fetchArray();

			$deleted_contact_forms[] = '99999999';
			foreach($contact_v as $k_contact_v => $v_contact_v)
			{
				$deleted_contact_forms[] = $v_contact_v['recordid'];
			}

			$contact_form_visits = Doctrine_Query::create()
				->select("*")
				->from("ContactForms")
				->where('ipid = "' . $ipid . '"')
				->andWhereNotIn('id', $deleted_contact_forms)
				->andWhere('DATE(billable_date) BETWEEN ? and ? ',array($current_period['start'],$current_period['end']))
				->andWhere('isdelete ="0"')
				->andWhere('parent ="0"');

			if($sgbxi)
			{
				$contact_form_visits->andWhere('sgbxi_quality = "1"');
			}

			$contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');
			$contact_form_visits_res = $contact_form_visits->fetchArray();

			foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
			{

				if(!$sgbxi)
				{
					$contact_form_visit_date = date('Y-m-d', strtotime($v_contact_visit['billable_date']));

					if($duration)
					{
// 						$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_contact_visit['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_m'], 2, "0", STR_PAD_LEFT), $v_contact_visit['date']);
						$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'], $v_contact_visit['end_date']);
					}
					elseif($duration_after_death)
					{
						if(!empty($discharge_dead_date_time)){
								
							// RE calculate visit duration  // ISPC 2051
							$visit_start_date = strtotime(date('Y-m-d H:i:00', strtotime($v_contact_visit['start_date'])));
							$visit_end_date = strtotime(date('Y-m-d H:i:00', strtotime($v_contact_visit['end_date'])));
							$a1start = strtotime($discharge_dead_date_time);
							$a1end = strtotime($discharge_dead_date_time);
						
							if(Pms_CommonData::isintersected($visit_start_date, $visit_end_date, $a1start, $a1end))
							{
								$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$discharge_dead_date_time);
							} 
							else
							{
								$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
							}
						}
						else
						{
							$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
						}
					}
					$cf_visit_days[$contact_form_visit_date][] = $v_contact_visit;

					$cf_visit_days[$contact_form_visit_date]['form_types'][] = $v_contact_visit['form_type'];
					$cf_visit_days[$contact_form_visit_date]['form_types'] = array_unique($cf_visit_days[$contact_form_visit_date]['form_types']);
				}
				else
				{
					$cf_visit_days[$v_contact_visit['id']] = $v_contact_visit;
				}
			}

			return $cf_visit_days;
		}

		public function clientusersAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$search_string = addslashes(urldecode(trim($_REQUEST['string'])));

			$users = new User();
			$userarray = $users->livesearch_users($search_string, $clientid, false);

			foreach($userarray as $user)
			{
				if(strlen(trim($user['last_name'])) > 0)
				{
					$user_fullname[$user['id']][] = trim(rtrim($user['last_name']));
				}

				if(strlen(trim($user['first_name'])))
				{
					$user_fullname[$user['id']][] = trim(rtrim($user['first_name']));
				}

				$user['return_row_id'] = $_REQUEST['row'];
				$user['full_name'] = implode(', ', $user_fullname[$user['id']]);

				$userarraylast[$user['id']] = $user;
			}

			$this->view->droparray = $userarraylast;
		}

		public function suppliersAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
			
			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('Suppliers')
					->where("(trim(lower(last_name)) like ?) or (trim(lower(first_name)) like ?) or (trim(lower(supplier)) like ?) or (trim(lower(supplier)) like ?)"
							,array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
									"%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							)
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['supplier'] = html_entity_decode($val['supplier'], ENT_QUOTES, "utf-8");
					$droparray[$key]['type'] = html_entity_decode($val['type'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");

					if($_REQUEST['multiple'])
					{
						$droparray[$key]['supplier_row'] = $_REQUEST['multiple'];
					}
					else
					{
						$droparray[$key]['supplier_row'] = '';
					}
				}

				
				//ISPC-2076, elena, 01.12.2020
				
				$drop_f = Doctrine_Query::create()
				->select('*')
				->from('Servicesfuneral')
				->where("(trim(lower(cp_lname)) like ?) or (trim(lower(cp_fname)) like ?) or (trim(lower(services_funeral_name)) like ?) or (trim(lower(services_funeral_name)) like ?)"
				    , array("%" . trim(mb_strtolower($search_string, 'UTF-8')) . "%",
				        "%" . trim(mb_strtolower($search_string, 'UTF-8')) . "%",
				        "%" . trim(mb_strtolower($search_string, 'UTF-8')) . "%",
				        "%" . trim(mb_strtolower($search_string, 'UTF-8')) . "%")
				    )
				    ->andWhere('clientid = "' . $clientid . '"')
				    //->andWhere("indrop = 0")
				->andWhere("isdelete = 0")
				->orderBy('cp_lname ASC');
				
				if (!empty($limit)) {
				    $drop_f->limit($limit);
				}
				
				
				//echo $drop_f->getSqlQuery();
				
				$drop_arr_f = $drop_f->fetchArray();
				//print_r($drop_arr_f);
				foreach ($drop_arr_f as $key => $val) {
				    $droparray_f[$key]['id'] = $val['id'];
				    $droparray_f[$key]['supplier'] = html_entity_decode($val['services_funeral_name'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['type'] = 'Bestätter'; // html_entity_decode($val['type'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['first_name'] = html_entity_decode($val['cp_fname'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['last_name'] = html_entity_decode($val['cp_lname'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['salutation'] = '';//html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['street'] = html_entity_decode($val['street'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
				    $droparray_f[$key]['comments'] = '';// html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
				    /*
				    
				    if ($_REQUEST['multiple']) {
				    $droparray[$key]['supplier_row'] = $_REQUEST['multiple'];
				    } else {
				    $droparray[$key]['supplier_row'] = '';
				    }*/
				}
				
							
				$this->view->droparray = $droparray;
				//ISPC-2076, elena, 01.12.2020
				$this->view->droparray_f = $droparray_f;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function lettertemplatesAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$pm = new PatientMaster();
			$patient_array = $pm->getMasterData($decid, 0);

			$client_users_res = User::getUserByClientid($clientid, 0, true);

			$default_recipient_names = Pms_CommonData::template_default_recipients();

			//get patient healthinsurance
			$phelathinsurance = PatientHealthInsurance::get_multiple_patient_healthinsurance(array($ipid), true);
			$healthinsu_array = $phelathinsurance[$ipid];
//print_r($healthinsu_array);exit;
			if($healthinsu_array)
			{
				$recipient_hi[] = $healthinsu_array['company_name'];
				
				if(strlen($healthinsu_array['ins_insurance_provider']) == '0')
				{
					$recipient_hi[] = $healthinsu_array['company']['insurance_provider'];
				}
				else
				{
					$recipient_hi[] = $healthinsu_array['ins_insurance_provider'];
				}
				
				if(strlen($healthinsu_array['ins_street']) == '0')
				{
					$recipient_hi[] = $healthinsu_array['company']['street1'];
				}
				else
				{
					$recipient_hi[] = $healthinsu_array['ins_street'];
				}

				$recipient_hi['last_line'] = '';
											
				if(strlen($healthinsu_array['ins_zip']) == '0')
				{
					$recipient_hi['last_line'] .= $healthinsu_array['company']['zip'] . ' ';
				}
				else
				{
					$recipient_hi['last_line'] .= $healthinsu_array['ins_zip'] . ' ';
				}

				if(strlen($healthinsu_array['ins_city']) == '0')
				{
					$recipient_hi['last_line'] .= $healthinsu_array['company']['city'];
				}
				else
				{
					$recipient_hi['last_line'] .= $healthinsu_array['ins_city'];
				}
			}

			//get patient family doctor
			if($patient_array['familydoc_id'] > '0')
			{
				$family_doctor_data = FamilyDoctor::getFamilyDoc($patient_array['familydoc_id']);

				if($family_doctor_data)
				{
					if(strlen($family_doctor_data[0]['practice']) > '0')
					{
						$fdoc_recipient[] = trim(rtrim($family_doctor_data[0]['practice']));
					}

					if(strlen($family_doctor_data[0]['first_name']) > '0' || strlen($family_doctor_data[0]['last_name']) > '0')
					{
						$name = '';
						if(strlen(trim(rtrim($family_doctor_data[0]['first_name']))) > '0')
						{
							$name .= trim(rtrim($family_doctor_data[0]['first_name'])) . ' ';
							$recipient_array['fdoc']['first_name'] = trim(rtrim($family_doctor_data[0]['first_name']));
						}

						if(strlen(trim(rtrim($family_doctor_data[0]['last_name']))) > '0')
						{
							$name .= trim(rtrim($family_doctor_data[0]['last_name']));
							$recipient_array['fdoc']['last_name'] = trim(rtrim($family_doctor_data[0]['last_name']));
						}

						$fdoc_recipient[] = $name;
					}

					
						if(strlen(trim(rtrim($family_doctor_data[0]['salutation']))) > '0')
						{
                            $recipient_array['fdoc']['salutation'] = trim(rtrim($family_doctor_data[0]['salutation']));
						}
						
						// ISPC-1236 - 19.10.2017
						if(strlen(trim(rtrim($family_doctor_data[0]['fax']))) > '0')
						{
                            $recipient_array['fdoc']['fax'] = trim(rtrim($family_doctor_data[0]['fax']));
						}
					
					if(strlen($family_doctor_data[0]['street1']) > '0')
					{
						$fdoc_recipient[] = trim(rtrim($family_doctor_data[0]['street1']));
					}

					if(strlen($family_doctor_data[0]['zip']) > '0' || strlen($family_doctor_data[0]['city']))
					{
						$fdoc_recipient[] = trim(rtrim($family_doctor_data[0]['zip'])) . ' ' . trim(rtrim($family_doctor_data[0]['city']));
					}
				}
			}

			//get patient data for recipient field
			if(strlen($patient_array['first_name']) > '0' || strlen($patient_array['last_name']) > '0')
			{
				$patient_recipient['name'] = '';
				if(strlen(trim(rtrim($patient_array['first_name']))) > '0')
				{
					$patient_recipient['name'] .= trim(rtrim($patient_array['first_name'])) . ' ';
					$recipient_array['pat']['first_name'] = trim(rtrim($patient_array['first_name']));
				}

				if(strlen(trim(rtrim($patient_array['last_name']))) > '0')
				{
					$patient_recipient['name'] .= trim(rtrim($patient_array['last_name']));
					$recipient_array['pat']['last_name'] = trim(rtrim($patient_array['last_name']));
				}
			}

			if(strlen(trim(rtrim($patient_array['salutation']))) > '0')
			{
				$recipient_array['pat']['salutation'] = trim(rtrim($patient_array['salutation']));
			}

			// ISPC-1236 - 19.10.2017
			if(strlen(trim(rtrim($patient_array['fax']))) > '0')
			{
				$recipient_array['pat']['fax'] = trim(rtrim($patient_array['fax']));
			}
			
			
			$patient_recipient['street'] = trim(rtrim($patient_array['street1']));

			if(strlen($patient_array['zip']) > '0' || strlen($patient_array['city']))
			{
				$patient_recipient['zip_city'] = trim(rtrim($patient_array['zip'])) . ' ' . trim(rtrim($patient_array['city']));
			}

			//get contact person recipient(top first if many)
			$contact_pers = new ContactPersonMaster();
			$contact_persons = $contact_pers->getPatientContact($ipid, true);

			if($contact_persons && $contact_persons[0])
			{
				$cont_per_recipient['name'] = '';
				if(strlen(trim(rtrim($contact_persons[0]['cnt_first_name']))) > '0')
				{
					$cont_per_recipient['name'] .= trim(rtrim($contact_persons[0]['cnt_first_name'])) . ' ';
					$recipient_array['cntpers']['first_name'] = trim(rtrim($contact_persons[0]['cnt_first_name']));
				}

				if(strlen(trim(rtrim($contact_persons[0]['cnt_last_name']))) > '0')
				{
					$cont_per_recipient['name'] .= trim(rtrim($contact_persons[0]['cnt_last_name']));
					$recipient_array['cntpers']['last_name'] = trim(rtrim($contact_persons[0]['cnt_last_name']));
				}
				
				if(strlen(trim(rtrim($contact_persons[0]['cnt_salutation']))) > '0')
				{
					$recipient_array['cntpers']['salutation'] = trim(rtrim($contact_persons[0]['cnt_salutation']));
				}
				

				if(strlen(trim(rtrim($contact_persons[0]['cnt_street1']))) > '0')
				{
					$cont_per_recipient['street'] .= trim(rtrim($contact_persons[0]['cnt_street1']));
				}

				$cont_per_recipient['zip_city'] = '';
				if(strlen(trim(rtrim($contact_persons[0]['cnt_zip']))) > '0')
				{
					$cont_per_recipient['zip_city'] .= trim(rtrim($contact_persons[0]['cnt_zip'])) . ' ';
				}

				if(strlen(trim(rtrim($contact_persons[0]['cnt_city']))) > '0')
				{
					$cont_per_recipient['zip_city'] .= trim(rtrim($contact_persons[0]['cnt_city']));
				}
			}

			//get pflegedienst (nursing) recipient data
			$patient_pflege_data = PatientPflegedienste::getPatientFirstPflegediensteDetails($ipid);

			if($patient_pflege_data)
			{
				$nursing_recipient['name'] = '';

				if(strlen(trim(rtrim($patient_pflege_data['first_name']))) > '0')
				{
					$nursing_recipient['name'] .= trim(rtrim($patient_pflege_data['first_name'])) . ' ';
					$recipient_array['pfl']['first_name'] = trim(rtrim($patient_pflege_data['first_name']));
				}

				if(strlen(trim(rtrim($patient_pflege_data['last_name']))) > '0')
				{
					$nursing_recipient['name'] .= trim(rtrim($patient_pflege_data['last_name']));
					$recipient_array['pfl']['last_name'] = trim(rtrim($patient_pflege_data['last_name']));
				}
				
				if(strlen(trim(rtrim($patient_pflege_data['salutation']))) > '0')
				{
					$recipient_array['pfl']['salutation'] = trim(rtrim($patient_pflege_data['salutation']));
				}
				
				// ISPC-1236 - 19.10.2017
				if(strlen(trim(rtrim($patient_pflege_data['fax']))) > '0')
				{
					$recipient_array['pfl']['fax'] = trim(rtrim($patient_pflege_data['fax']));
				}

				if(strlen(trim(rtrim($patient_pflege_data['street1']))) > '0')
				{
					$nursing_recipient['street1'] .= trim(rtrim($patient_pflege_data['street1']));
				}
				if(strlen(trim(rtrim($patient_pflege_data['street2']))) > '0')
				{
					$nursing_recipient['street2'] .= trim(rtrim($patient_pflege_data['street2']));
				}

				if(strlen(trim(rtrim($patient_pflege_data['zip']))) > '0')
				{
					$nursing_recipient['zip_city'] .= trim(rtrim($patient_pflege_data['zip'])) . ' ';
				}

				if(strlen(trim(rtrim($patient_pflege_data['city']))) > '0')
				{
					$nursing_recipient['zip_city'] .= trim(rtrim($patient_pflege_data['city']));
				}
			}

			//get apotheke (pharmacy) data
			$pharmacy_data = PatientPharmacy::getPatientPharmacy($ipid);

			if($pharmacy_data && $pharmacy_data[0])
			{
				if(strlen(trim(rtrim($pharmacy_data[0]['Pharmacy']['apotheke']))) > '0')
				{
					$pharmacy_recipient['apotheke'] = trim(rtrim($pharmacy_data[0]['Pharmacy']['apotheke']));
				}

				if(strlen(trim(rtrim($pharmacy_data[0]['Pharmacy']['first_name']))) > '0')
				{
					$pharmacy_recipient['name'] .= trim(rtrim($pharmacy_data[0]['Pharmacy']['first_name'])) . ' ';
					$recipient_array['apoth']['first_name'] = trim(rtrim($pharmacy_data[0]['Pharmacy']['first_name']));
				}

				if(strlen(trim(rtrim($pharmacy_data[0]['Pharmacy']['last_name']))) > '0')
				{
					$pharmacy_recipient['name'] .= trim(rtrim($pharmacy_data[0]['Pharmacy']['last_name']));
					$recipient_array['apoth']['last_name'] = trim(rtrim($pharmacy_data[0]['Pharmacy']['last_name']));
				}
				
				if(strlen(trim(rtrim($pharmacy_data[0]['Pharmacy']['salutation']))) > '0')
				{
					$recipient_array['apoth']['salutation'] = trim(rtrim($pharmacy_data[0]['Pharmacy']['salutation']));
				}
				
				// ISPC-1236 - 19.10.2017
				if(strlen(trim(rtrim($pharmacy_data[0]['Pharmacy']['fax']))) > '0')
				{
					$recipient_array['apoth']['fax'] = trim(rtrim($pharmacy_data[0]['Pharmacy']['fax']));
				}

				if(strlen(trim(rtrim($pharmacy_data[0]['street1']))) > '0')
				{
					$pharmacy_recipient['street1'] .= trim(rtrim($pharmacy_data[0]['street1']));
				}

				if(strlen(trim(rtrim($pharmacy_data[0]['zip']))) > '0')
				{
					$pharmacy_recipient['zip_city'] .= trim(rtrim($pharmacy_data[0]['zip'])) . ' ';
				}

				if(strlen(trim(rtrim($pharmacy_data[0]['city']))) > '0')
				{
					$pharmacy_recipient['zip_city'] .= trim(rtrim($pharmacy_data[0]['city']));
				}
			}

			//get patient supplies
			$supplies_data = PatientSupplies::getPatientSupplies($ipid);

			if($supplies_data && $supplies_data[0])
			{
				if(strlen(trim(rtrim($supplies_data[0]['supplier']))) > '0')
				{
					$supplies_recipient['supplier'] = trim(rtrim($supplies_data[0]['supplier']));
				}

				if(strlen(trim(rtrim($supplies_data[0]['first_name']))) > '0')
				{
					$supplies_recipient['name'] .= trim(rtrim($supplies_data[0]['first_name'])) . ' ';
					$recipient_array['supp']['first_name'] = trim(rtrim($supplies_data[0]['first_name']));
				}

				if(strlen(trim(rtrim($supplies_data[0]['last_name']))) > '0')
				{
					$supplies_recipient['name'] .= trim(rtrim($supplies_data[0]['last_name']));
					$recipient_array['supp']['last_name'] = trim(rtrim($supplies_data[0]['last_name']));
				}
				
				if(strlen(trim(rtrim($supplies_data[0]['salutation']))) > '0')
				{
					$recipient_array['supp']['salutation'] = trim(rtrim($supplies_data[0]['salutation']));
				}
				
				// ISPC-1236 - 19.10.2017
				if(strlen(trim(rtrim($supplies_data[0]['fax']))) > '0')
				{
					$recipient_array['supp']['fax'] = trim(rtrim($supplies_data[0]['fax']));
				}
				

				if(strlen(trim(rtrim($supplies_data[0]['street1']))) > '0')
				{
					$supplies_recipient['street1'] .= trim(rtrim($supplies_data[0]['street1']));
				}

				if(strlen(trim(rtrim($supplies_data[0]['zip']))) > '0')
				{
					$supplies_recipient['zip_city'] .= trim(rtrim($supplies_data[0]['zip'])) . ' ';
				}

				if(strlen(trim(rtrim($supplies_data[0]['city']))) > '0')
				{
					$supplies_recipient['zip_city'] .= trim(rtrim($supplies_data[0]['city']));
				}
			}

			//construct array with client users
			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['user_title'] . ' ' . $v_user['last_name'] . ', ' . $v_user['first_name'];
			}

			$search_string = addslashes(urldecode(trim($_REQUEST['string_query'])));

			if($clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('BriefTemplates')
				->where("isdeleted = '0' " . $where . "");
			if(isset($_REQUEST['string_query']) && strlen($_REQUEST['string_query']) > 0)
			{
				$fdoc->andWhere("(title != '' or file_type != '')");
				$fdoc->andWhere("(trim(lower(title)) like ? OR trim(lower(file_type)) like ?)",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"));
			}
			$fdoc->orderBy('title');
			$drop_arr = Pms_CommonData::array_stripslashes($fdoc->fetchArray());



			foreach($drop_arr as $key => $val)
			{
				$droparray[$key]['id'] = $val['id'];
				$droparray[$key]['date'] = date('d.m.Y H:i', strtotime($val['create_date']));
				$droparray[$key]['title'] = html_entity_decode($val['title'], ENT_QUOTES, "utf-8");
				$droparray[$key]['default_recipient'] = $default_recipient_names[$val['recipient']];
				$droparray[$key]['user'] = html_entity_decode($client_users[$val['create_user']], ENT_QUOTES, "utf-8");
				$droparray[$key]['userid'] = $val['create_user'];

				switch($val['recipient'])
				{
					case "fdoc":
						$recipient = implode("\r\n", $fdoc_recipient);
						break;

					case "hi":
						$recipient = implode("\r\n", $recipient_hi);
						break;

					case "pat":
						$recipient = implode("\r\n", $patient_recipient);
						break;

					case "cntpers":
						$recipient = implode("\r\n", $cont_per_recipient);
						break;

					case "pfl":
						$recipient = implode("\r\n", $nursing_recipient);
						break;

					case "apoth":
						$recipient = implode("\r\n", $pharmacy_recipient);
						break;
					case "supp":
						$recipient = implode("\r\n", $supplies_recipient);
						break;

					case "none":
						$recipient = '';
						break;

					default:
						$recipient = '';
						break;
				}


				$droparray[$key]['recipient'] = $recipient;
				$droparray[$key]['recipient_last_name'] = $recipient_array[$val['recipient']]['last_name'];
				$droparray[$key]['recipient_first_name'] = $recipient_array[$val['recipient']]['first_name'];
				$droparray[$key]['recipient_salutation'] = $recipient_array[$val['recipient']]['salutation'];
				$droparray[$key]['recipient_fax'] = $recipient_array[$val['recipient']]['fax'];

				//check if template file exists
				$droparray[$key]['file_exists'] = '1';
				if(!file_exists(BRIEF_TEMPLATE_PATH . '/' . $val['file_path']))
				{
					$droparray[$key]['file_exists'] = '0';
				}
			}
			$this->view->droparray = $droparray;

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callback_template";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['templateslist'] = $this->view->render('ajax/lettertemplates.html');

			echo json_encode($response);
			exit;
		}

		public function lettervisitAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$client_users_res = User::getUserByClientid($clientid, 0, true);

			foreach($client_users_res as $k_user => $v_user)
			{
				$client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
			}

			$search_string = addslashes(urldecode(trim($_REQUEST['string_query'])));


			if($clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}


			//get patient all visit forms
			$all_visit_forms = BriefTemplates::get_patient_forms($clientid, $ipid, $search_string);

			foreach($all_visit_forms as $key => $val)
			{
				switch($val['visit_form_type'])
				{
					case "cf":
						$droparray[$key]['id'] = $val['id'];
						$droparray[$key]['visit_date'] = date('d.m.Y', strtotime($val['billable_date']));
						$droparray[$key]['visit_date_full'] = $val['billable_date'];

						$droparray[$key]['visit_start'] = date('H:i', strtotime($val['start_date']));
						$droparray[$key]['visit_end'] = date('H:i', strtotime($val['end_date']));

						$droparray[$key]['visit_user'] = html_entity_decode($client_users[$val['create_user']], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type'] = html_entity_decode($val['form_type'], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type_code'] = $val['visit_form_type'];

						$droparray[$key]['create_date'] = date('d.m.Y H:i', strtotime($val['create_date']));
						break;

					case "kdoc":
						$droparray[$key]['id'] = $val['id'];
						$droparray[$key]['visit_date'] = date('d.m.Y', strtotime($val['vizit_date']));
						$droparray[$key]['visit_date_full'] = $val['vizit_date'];

						$droparray[$key]['visit_start'] = date('H:i', strtotime($val['start_date']));
						$droparray[$key]['visit_end'] = date('H:i', strtotime($val['end_date']));

						$droparray[$key]['visit_user'] = html_entity_decode($client_users[$val['create_user']], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type'] = html_entity_decode($val['form_type'], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type_code'] = $val['visit_form_type'];

						$droparray[$key]['create_date'] = date('d.m.Y H:i', strtotime($val['create_date']));
						break;

					case "knur":
						$droparray[$key]['id'] = $val['id'];
						$droparray[$key]['visit_date'] = date('d.m.Y', strtotime($val['vizit_date']));
						$droparray[$key]['visit_date_full'] = $val['vizit_date'];

						$droparray[$key]['visit_start'] = date('H:i', strtotime($val['start_date']));
						$droparray[$key]['visit_end'] = date('H:i', strtotime($val['end_date']));

						$droparray[$key]['visit_user'] = html_entity_decode($client_users[$val['create_user']], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type'] = html_entity_decode($val['form_type'], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type_code'] = $val['visit_form_type'];

						$droparray[$key]['create_date'] = date('d.m.Y H:i', strtotime($val['create_date']));
						break;

					case "bayern_doctorvisit":
						$droparray[$key]['id'] = $val['id'];
						$droparray[$key]['visit_date'] = date('d.m.Y', strtotime($val['visit_date']));
						$droparray[$key]['visit_date_full'] = $val['visit_date'];

						$droparray[$key]['visit_start'] = date('H:i', strtotime($val['start_date']));
						$droparray[$key]['visit_end'] = date('H:i', strtotime($val['end_date']));

						$droparray[$key]['visit_user'] = html_entity_decode($client_users[$val['create_user']], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type'] = html_entity_decode($val['form_type'], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type_code'] = $val['visit_form_type'];

						$droparray[$key]['create_date'] = date('d.m.Y H:i', strtotime($val['create_date']));
						break;

					case "vkf":
						$droparray[$key]['id'] = $val['id'];
						$droparray[$key]['visit_date'] = date('d.m.Y', strtotime($val['visit_date']));
						$droparray[$key]['visit_date_full'] = $val['visit_date'];

						$droparray[$key]['visit_start'] = date('H:i', strtotime($val['start_date']));
						$droparray[$key]['visit_end'] = date('H:i', strtotime($val['end_date']));

						$droparray[$key]['visit_user'] = html_entity_decode($client_users[$val['create_user']], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type'] = html_entity_decode($val['form_type'], ENT_QUOTES, "utf-8");
						$droparray[$key]['visit_type_code'] = $val['visit_form_type'];

						$droparray[$key]['create_date'] = date('d.m.Y H:i', strtotime($val['create_date']));
						break;

					default:
						exit;
						break;
				}
			}

			$droparray = $this->array_sort($droparray, 'visit_date_full', SORT_DESC);
			$this->view->droparray = $droparray;

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callback_visit";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['visitslist'] = $this->view->render('ajax/lettervisits.html');

			echo json_encode($response);
			exit;
		}

		//used in edit tags(creates a list with tags to be removed)
		public function filetagsAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$file_id = urldecode(trim($_REQUEST['fileid']));

			if(strlen(trim(rtrim($_REQUEST['fileid']))) > 0)
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('PatientFileTags')
					->where("file = ?", $file_id )
					->andWhere('client = ?', $clientid)
					->orderBy('tag ASC');
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[] = array('id' => $val['id'], 'tag' => html_entity_decode($val['tag'], ENT_QUOTES, "utf-8"));
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function finduserAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 0 && strlen($_REQUEST['row']) > '0')
			{
				$usr_search = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('isdelete = "0"')
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("(trim(lower(last_name)) like ? OR trim(lower(username)) like ? OR trim(lower(username)) like ?)",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"));
				$usr_res = $usr_search->fetchArray();

				if($usr_res)
				{
					foreach($usr_res as $k_usr => $v_usr)
					{
						$usr_array[$k_usr]['id'] = $v_usr['id'];
						$usr_array[$k_usr]['row'] = $_REQUEST['row'];
						$usr_array[$k_usr]['first_name'] = html_entity_decode($v_usr['first_name'], ENT_QUOTES, "utf-8");
						$usr_array[$k_usr]['last_name'] = html_entity_decode($v_usr['last_name'], ENT_QUOTES, "utf-8");
						$usr_array[$k_usr]['city'] = html_entity_decode($v_usr['city'], ENT_QUOTES, "utf-8");
					}
					$this->view->usr_array = $usr_array;
				}
				else
				{
					$this->view->usr_array = array();
				}
			}
		}

		public function getpatientmedicsAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			//$clientid = $logininfo->clientid;
			//$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			//$ipid = Pms_CommonData::getIpid($decid);

			$pdp = new PatientDrugPlan();

			$arr = $pdp->getPatientDrugPlan($decid, true);
			if(empty($arr))
			{
				$arr = array();
			}
			echo json_encode($arr);
			exit;
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
								if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on = 'visit_date_full'  || $on = 'start_date_full' || $on = 'start_date_Ymd' )
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
						if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on = 'visit_date_full'  || $on = 'start_date_full'  || $on = 'start_date_Ymd')
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
//					asort($sortable_array);
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case SORT_DESC:
//					arsort($sortable_array);
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

		public function mmitextAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			if(strlen($_REQUEST['q']) > 0)
			{

				$drop = Doctrine_Query::create()
					->select('*')
					->from('MmiReceiptTxtBlocks')
					->where("trim(lower(text)) like ?","%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("isdeleted = 0")
					->orderBy('create_date ASC');
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['text'] = html_entity_decode($val['text'], ENT_QUOTES, "utf-8");
					$droparray[$key]['row'] = $_REQUEST['row'];
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	19. Physiotherapist (Stammdaten)
		public function physiotherapistAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));

			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;
			
			if(strlen($_REQUEST['q']) > 0)
			{

				$drop = Doctrine_Query::create()
					->select('*')
					->from('Physiotherapists')
					->where("clientid='" . $clientid . "' and  (trim(lower(last_name)) like ?) or (trim(lower(first_name)) like ?) or (trim(lower(physiotherapist)) like ?) or (trim(lower(physiotherapist)) like ?)"
							,array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
							)
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("valid_till='0000-00-00'")
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['physiotherapist'] = html_entity_decode($val['physiotherapist'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_emergency'] = html_entity_decode($val['phone_emergency'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['ik_number'] = html_entity_decode($val['ik_number'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		//	20. Homecare (Stammdaten)
		public function homecaresAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
			
			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = (int)$limit;

			if(strlen($_REQUEST['q']) > 0)
			{

				$drop = Doctrine_Query::create()
					->select('*')
					->from('Homecare')
					->where("clientid='" . $clientid . "' and  (trim(lower(last_name)) like ?) or (trim(lower(first_name)) like ?) or (trim(lower(homecare)) like ?) or (trim(lower(homecare)) like ?)"
							,array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("valid_till='0000-00-00'")
					->andWhere("indrop = 0")
					->andWhere("isdelete = 0")
					->orderBy('last_name ASC');
				
				if ( ! empty($limit)) {
				    $drop->limit($limit);
				}
				
				$drop_arr = $drop->fetchArray();

				foreach($drop_arr as $key => $val)
				{
					$droparray[$key]['id'] = $val['id'];
					$droparray[$key]['homecare'] = html_entity_decode($val['homecare'], ENT_QUOTES, "utf-8");
					$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
					$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
					$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
					$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
					$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_practice'] = html_entity_decode($val['phone_practice'], ENT_QUOTES, "utf-8");
					$droparray[$key]['phone_emergency'] = html_entity_decode($val['phone_emergency'], ENT_QUOTES, "utf-8");
					$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
					$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
					$droparray[$key]['ik_number'] = html_entity_decode($val['ik_number'], ENT_QUOTES, "utf-8");
					$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
				}

				$this->view->droparray = $droparray;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function remediesAction()
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
			$this->_helper->layout->setLayout('layout_ajax');

			if(strlen($_REQUEST['q']) > 0)
			{

			/* 	if($_REQUEST['mode'] == 'indikationkey')
				{
					$search_string ="%".trim(mb_strtolower(addslashes(urldecode($_REQUEST['q'])), 'UTF-8'))."%"; 
					$srchoption = "trim(lower(indikation_key )) like ?)";
					$order = 'indikation_key';
				}
				else
				{
					$search_str = addslashes(urldecode($_REQUEST['q']));
					$search_string ="%".trim(mb_strtolower(addslashes(urldecode($_REQUEST['q'])), 'UTF-8'))."%";
					$srchoption = "trim(lower(indikation_name )) like ?";
					$order = 'indikation_key';
				}



				$drugs = Doctrine_Query::create()
					->select('*')
					->from('Remedies')
					->where(" " . $srchoption . " ",$search_string)
					->andWhere("isdelete=0 ")
					->andWhere("clientid = ".$clientid)
					->limit('150')
					->orderBy('' . $order . ' ASC');

				$drop_array = $drugs->fetchArray();
 */
				
				if($_REQUEST['mode'] == 'indikationkey')
				{
					$srchoption = "trim(lower(indikation_key )) like trim(lower('%" . (addslashes(urldecode($_REQUEST['q']))) . "%'))";
					$order = 'indikation_key';
				}
				else
				{
					//$search_str = htmlentities(addslashes(urldecode($_REQUEST['q'])), ENT_QUOTES, "utf-8"); //stupid indians saved data as html entities
					$search_str = addslashes(urldecode($_REQUEST['q'])); //smart Alex saved it properly :P
					$srchoption = "trim(lower(indikation_name )) like trim(lower('%" . ($search_str) . "%'))";
					$order = 'indikation_key';
				}
				
				
				
				// ISPC-2612 Ancuta 29.06.2020
				$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Remedies', $clientid);
				// --
				
				$drugs = Doctrine_Query::create()
				->select('*')
				->from('Remedies')
				->where(" " . $srchoption . " and isdelete=0 ")
				->andWhere("clientid = ".$clientid);
				if ($client_is_follower) {// ISPC-2612 Ancuta 29.06.2020
				    $drugs->andWhere('connection_id is NOT null');
				    $drugs->andWhere('master_id is NOT null');
				}
				$drugs->limit('150')
				->orderBy('' . $order . ' ASC');
				
				$drop_array = $drugs->fetchArray();
				foreach($drop_array as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['indikation_key'] = html_entity_decode($val['indikation_key'], ENT_QUOTES, "UTF-8");
					$drop_array[$key]['indikation_name'] = html_entity_decode($val['indikation_name'], ENT_QUOTES, "UTF-8");
					//this is the increment to know which line to fill in admission diag form
					$drop_array[$key]['row'] = $_REQUEST['row'];
				}
				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function aidAction()
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
			$this->_helper->layout->setLayout('layout_ajax');

			$this->view->context = $this->getRequest()->getParam('context', '');
			$this->view->returnRowId = $this->getRequest()->getParam('row', '');
			$limit = $this->getRequest()->getParam('limit', 0);
			$limit = ! empty($limit) ? (int)$limit : 150;
			
			// ISPC-2612 Ancuta 29.06.2020
			$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Aid', $clientid);
			// --
			if(strlen($_REQUEST['q']) > 0)
			{
				$search_str = addslashes(urldecode($_REQUEST['q'])); 
				$srchoption = "trim(lower(name)) like trim(lower('%" . ($search_str) . "%'))";
				$order = 'name';

				$drugs = Doctrine_Query::create()
					->select('*')
					->from('Aid')
					->where(" " . $srchoption . " and isdelete=0 ")
					->andWhere("clientid = ".$clientid);
					if ($client_is_follower) {// ISPC-2612 Ancuta 29.06.2020
					    $drugs->andWhere('connection_id is NOT null');
					    $drugs->andWhere('master_id is NOT null');
				    }
				    $drugs->orderBy('' . $order . ' ASC');

				if ( ! empty($limit)) {
				    $drugs->limit($limit);
				}
				
				$drop_array = $drugs->fetchArray();

				foreach($drop_array as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
					//this is the increment to know which line to fill in admission diag form
					$drop_array[$key]['row'] = $_REQUEST['row'];
				}
				$this->view->droparray = $drop_array;
			}
			else
			{
				$this->view->droparray = array();
			}
		}

		public function saveuserfilterAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$modules = new Modules();
			if($this->getRequest()->isPost())
			{
				// do not save user filters ISPC-
				if(!$modules->checkModulePrivileges("153", $clientid))
				{
					$data = json_decode($_POST['details']);
					if(!empty($data))
					{
						$user_filter = new Application_Form_UserCourseFilters ();
						$result = $user_filter->set_filter($userid, $clientid, $data);
					}
				}
			}
			exit();
		}

		//save patient readmission data from status icon
		public function readmitpatientAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_POST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			if(strlen($_POST['adm_date']) > '0' && Pms_Validation::isdate($_POST['adm_date']))
			{
				$date = date('Y-m-d', strtotime($_POST['adm_date']));

				$time = $_POST['adm_time_h'] . ':' . $_POST['adm_time_m'] . ':00';

				$date_time = $date . ' ' . $time;

				if(strlen($_POST['transition'])){
					$transition = $_POST['transition'];
				}
				
				//do readmission here
				$readm_op = PatientMaster::quick_readmission($ipid, $date_time,$transition);

				if($readm_op)
				{
					$status = "1"; //ok
				}
				else
				{
					$status = "0"; //something went wrong
				}

				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "readmisionCallback";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['status'] = $status;

				// Maria:: Migration ISPC to CISPC 08.08.2020
				//ISPC-2614 Ancuta 17.07.2020
				$int_connection = new IntenseConnections();
				$share_direction = $int_connection->get_intense_connection_by_ipid($ipid);

				$patient_master = new PatientMaster();
				foreach ($share_direction as $direction_k => $share_info) {
				    if (! empty($share_info['intense_connection'])) {
				        foreach ($share_info['intense_connection'] as $con => $con_ionfo) {
				            $IntenseConnectionsOptions = array_column($con_ionfo['IntenseConnectionsOptions'], 'option_name');
				            if (in_array('patient_falls', $IntenseConnectionsOptions) ) {
				                $patient_master->intense_connection_patient_admissions($share_info['source'], $share_info['target']);
				            }
				        }
				    }
				}
				// --

				echo json_encode($response);
				exit;
			}
		}

		// assign user to locaion
		public function saveuser2locationAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			$post = $_POST;
			$post['client'] = $clientid;

			if(!empty($post['location']) && !empty($post['user']))
			{
				$users2location_array = Users2Location::get_location_users($post['location']);
				foreach($users2location_array as $k => $vul)
				{
					$users2location[] = $vul['user'];
				}

				$assign_form = new Application_Form_Users2Location();
				// check if user exists
				if($_POST['value'] == "1")
				{

					if(!in_array($_POST['user'], $users2location))
					{ // check if user is assigned to location
						$result_user = $assign_form->assign_user($post);
					}
					else if(in_array($_POST['user'], $users2location) && $post['leader'] == "1")
					{
						$result_user = $assign_form->update_leader($post);
					}
					if($result_user)
					{
						$result = "1";
					}
				}
				else
				{
					// remove assigned user
					$result_remove = $assign_form->remove_user($post);
					if($result_remove)
					{
						$result = "0";
					}
				}
			}
			else
			{
				return false;
			}

			echo json_encode($result);
			exit;
		}

		public function checkprintuserassignedAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$user_form = new Application_Form_User();

			if(!empty($_REQUEST['user']) && $_REQUEST['user'] > '0')
			{
				$is_assigned = $user_form->check_printuser_assigned($clientid, $_REQUEST['user']);

				if($is_assigned)
				{
					$assigned_data = array("user" => $_REQUEST['user'], "assigned" => "1");
				}
				else
				{
					$assigned_data = array("user" => $_REQUEST['user'], "assigned" => "0");
				}

				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "select_assigned_patient";
				$response['callBackParameters'] = array();
				$response['callBackParameters'] = $assigned_data;

				echo json_encode($response);
				exit;
			}
		}

		public function checkfaxuserassignedAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$user_form = new Application_Form_User();

			if(!empty($_REQUEST['user']) && $_REQUEST['user'] > '0')
			{
				$is_assigned = $user_form->check_faxuser_assigned($clientid, $_REQUEST['user']);

				if($is_assigned)
				{
					$assigned_data = array("user" => $_REQUEST['user'], "assigned" => "1");
				}
				else
				{
					$assigned_data = array("user" => $_REQUEST['user'], "assigned" => "0");
				}

				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "select_assigned_patient";
				$response['callBackParameters'] = array();
				$response['callBackParameters'] = $assigned_data;

				echo json_encode($response);
				exit;
			}
		}

		public function receiptsassignedusersAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;


				if($_POST['type'] == "print" || empty($_POST['type']))
				{
					$assigned_users_mdl = new PrintUsersAssigned();
					$assigned_users = $assigned_users_mdl->get_receipt_assigned_users($clientid, false);

					if($assigned_users)
					{
						$json_asigned_users['print'] = $assigned_users;
					}
					else
					{
						$json_asigned_users['print'] = array("status" => '0');
					}
				}

				if($_POST['type'] == "fax" || empty($_POST['type']))
				{
					//fax users specific
					$assigned_fax_users_mdl = new FaxUsersAssigned();
					$assigned_fax_users = $assigned_fax_users_mdl->get_receipt_assigned_users($clientid, false);

					if($assigned_fax_users)
					{
						$json_asigned_users['fax'] = $assigned_fax_users;
					}
					else
					{
						$json_asigned_users['fax'] = array("status" => '0');
					}
				}

				echo json_encode($json_asigned_users);
			}
			exit;
		}
		
		public function changereceiptstatusAction()
		{
			$receipts_form = new Application_Form_Receipts();
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if(!empty($_REQUEST['rpid']) && !empty($_REQUEST['status']))
			{
				$data["receipt"] = $_REQUEST['rpid'];
				$data["status"] = $_REQUEST['status'];
				
				$receipts_form->update_receipt_status($data);
				$result = array("status"=>"1", "receiptid"=>$_REQUEST['rpid']);
			}
			else
			{
				$result = array("status"=>"0", "receiptid"=>$_REQUEST['rpid']);
			}
			
			
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "statuscallback";
			$response['callBackParameters'] = array();
			$response['callBackParameters'] = $result;

			echo json_encode($response);
			exit;

		}
		
		public function loadreceiptlogAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$users = new User();
			$receipts = new Receipts();
			$clientid = $logininfo->clientid;

			if(strlen($_REQUEST['id']) > 0 && strlen($_REQUEST['rpid']) > '0' && $_REQUEST['rpid']>'0')
			{
				$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_REQUEST['id']));
				$receipt_id = $_REQUEST['rpid'];
				
				$receipt_log = new ReceiptLog();
				
				//get all receipt log
				$receipt_log_res = $receipt_log->get_patient_receipt_log($ipid, $receipt_id);
				
				//TODO-3766 Lore 20.01.2021
				$receipt_items = new ReceiptItems();
				// has Softdelete() and i want by ignored for this time ---- get isdelete = 1 too
				$pc_listener = $receipt_items->getListener()->get('SoftdeleteListener');
				$pc_listener->setOption('disabled', true);
				$receipt_items_res = $receipt_items->get_items_by_receipt_id_all($receipt_id);
				$pc_listener->setOption('disabled', false);

				$receipt_items_medi = array();
				if($receipt_items_res){
				    foreach($receipt_items_res as $key => $vals){
				        //$receipt_items_medi[$receipt_id][$vals['create_date']] .= $vals['medication'].', ';
				        $receipt_items_medi[$receipt_id][date('Y-m-d H:i', strtotime($vals['create_date']))] .= $vals['medication'].', ';
				    }
				}
                //.
				
				//get all client users START
				$users_res = $users->get_client_users($clientid, "0", true);

				//prepare users array..
				$usersarray[] = '99999999';
				foreach($users_res as $k_user => $user)
				{
					$usersarray[] = $user['id'];
					$doctorusers[$user['id']] = $user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
				}
				
				$doctorusers = Pms_CommonData::a_sort($doctorusers);
				
				$receipt_ids[] = '99999999';
				if($receipt_log_res)
				{
				    $last_log_medi_name = '';
					foreach($receipt_log_res as $k_log => $v_log)
					{
						$vlog_date = strtotime(date('Y-m-d H:i', strtotime($v_log['date'])));
						
						//$v_log['medi_name'] = $receipt_items_medi[$v_log['receipt']][$v_log['date']]; //TODO-3766 Lore 20.01.2021
						$v_log['medi_name'] = $receipt_items_medi[$v_log['receipt']][date('Y-m-d H:i', strtotime($v_log['date']))]; //TODO-3766 Lore 20.01.2021
						if(empty($v_log['medi_name'])){
						    $v_log['medi_name'] = $last_log_medi_name;
						}
						$last_log_medi_name = $v_log['medi_name'];    				// for operation like printed get medi_name empty
						
						$history_log[$vlog_date.'-'.$v_log['user']][] = $v_log;
						$receipt_ids[] = $v_log['receipt'];
						
						if($v_log['operation'] == "assign")
						{
							$involved_users_assign[$v_log['id']] = unserialize($v_log['involved_users']);
							
							array_walk($involved_users_assign[$v_log['id']], function(&$value, $index, $doctorusers) {
								$value = $doctorusers[$value];
							}, $doctorusers);
							
							$assigned_users_fullnames[$v_log['id']]['assign'] = $involved_users_assign[$v_log['id']];
						}
						
						if($v_log['operation'] == "unassign")
						{
							$involved_users_unassign[$v_log['id']] = unserialize($v_log['involved_users']);
							
							array_walk($involved_users_unassign[$v_log['id']], function(&$value, $index, $doctorusers) {
								$value = $doctorusers[$value];
							}, $doctorusers);
							
							$assigned_users_fullnames[$v_log['id']]['unassign'] = $involved_users_unassign[$v_log['id']];
						}
					}
				}

				//get receipt details and construct medi arr for each receipt
				$receipts_data = $receipts->get_multiple_receipts($receipt_ids, $clientid);
				
				foreach($receipts_data as $k_receipts_d=> $v_receipts_d)
				{
					if(!empty($v_receipts_d['medication_1']) && strlen($v_receipts_d['medication_1']))
					{
						$receipts_medi[$v_receipts_d['id']][] = $v_receipts_d['medication_1'];
					}
					
					if(!empty($v_receipts_d['medication_2']) && strlen($v_receipts_d['medication_2']))
					{
						$receipts_medi[$v_receipts_d['id']][] = $v_receipts_d['medication_2'];
					}
					
					if(!empty($v_receipts_d['medication_3']) && strlen($v_receipts_d['medication_3']))
					{
						$receipts_medi[$v_receipts_d['id']][] = $v_receipts_d['medication_3'];
					}
					
					$receipts_medi_details[$v_receipts_d['id']] = implode(', ', $receipts_medi[$v_receipts_d['id']]);
					$receipts_details[$v_receipts_d['id']] = $v_receipts_d;
				}
				
				$this->view->history_log = $history_log;
				$this->view->users = $doctorusers;
				$this->view->receipts_medi_details =  $receipts_medi_details;
				$this->view->receipts_details =  $receipts_details;
				$this->view->assigned_users_fullnames =  $assigned_users_fullnames;
			}
			
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callback_history";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['receiptid'] = $_REQUEST['rpid'];//passthrough the receipt id
			$response['callBackParameters']['historylog'] = $this->view->render('ajax/loadreceiptlog.html');

			echo json_encode($response);
			exit;
		}
		
		
		public function changegroupAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($this->getRequest()->isPost())
			{
				if(!empty($_POST))
				{
					$user_form = new Application_Form_User();
					$result = $user_form -> update_user_group($_POST);
				}
			}
			exit();
		}
		
		
		
		//	Search patient in voluntary workers
		public function patientsearchvoluntaryworkerAction()
		{
		    $this->_helper->layout->setLayout('layout_ajax');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $search_string = addslashes(urldecode(trim($_REQUEST['q'])));
		
		    //ISPC 1739
		    $regexp = $search_string;
		    Pms_CommonData::value_patternation($regexp);
// 		    $regexp = mb_strtolower($regexp, 'UTF-8'); //@claudiu 12.2017, changed Pms_CommonData::value_patternation
		    
		    $clientid = $logininfo->clientid;
		    if(strlen($_REQUEST['q']) > 2)
		    {
		        $drop = Doctrine_Query::create()
		        ->select('*')
		        ->from('EpidIpidMapping')
		        ->where("clientid = ?", $clientid)
		        ->orderBy('epid asc');
		        $droparray = $drop->fetchArray();
		       
		        //ispc 1739
		        
		        $ipidval = array_column($droparray, 'ipid');
				$ipidval[] = "0";
				
		        /*
		        if($droparray)
		        {
		        	$ipidval = "'0'";
		            foreach($droparray as $key => $val)
		            {
		                $ipidval .= $comma . "'" . $val['ipid'] . "'";
		                $comma = ",";
		            }
		        }
				*/
		        $user_patients = PatientUsers::getUserPatients($logininfo->userid);

		        if(count($droparray) > 0)
		        {
		            $sql = "*,e.epid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
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
		            //if isstandby it must be shown as Anfrange in LS even if is also isdischarged
		            $sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
		            $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
		
		            // if super admin check if patient is visible or not
		            if($logininfo->usertype == 'SA')
		            {
		                $sql = "*, e.epid, ";
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
		            ->leftJoin("p.EpidIpidMapping e")
		            
		            ->where("p.ipid IN (". $user_patients['patients_str'] .")" )
		            ->andWhereIn("p.ipid", $ipidval)
		            ->andWhere("p.isdelete = 0")
		            ->andWhere("e.clientid = ?", $logininfo->clientid);
		            //ISPC 1739 REGEXP '".$regexp ."'"
		            $myor = "";
		            $myor .= "lower(e.epid) REGEXP ?";
		            $myor .= " OR lower(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "'))  REGEXP ?";
		            $myor .= " OR lower(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "')) REGEXP ?";
		            $myor .= " OR concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) REGEXP ? ";
		            $myor .= " OR concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) REGEXP ?";
		            
		            $patient->andWhere($myor, array($regexp, $regexp, $regexp, $regexp, $regexp));
		           	/*
		             * original query before ispc-1739
		            $patient->andwhere("e.clientid = " . $logininfo->clientid . " and trim(lower(e.epid)) like trim(lower('%" . $search_string . "%')) or (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
						concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))");
		            */
		            if($logininfo->hospiz == 1)
		            {
		                $patient->andwhere('ishospiz = 1');
		            }
		            $patient->orderby('status, ipid');
		            //die($patient->getSqlQuery());
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
		            	//ispc 1739
		            	$fnipidval = array_column($fndroparray, 'ipid');
		            	$fnipidval[]="0";
		            	/*
		                $comma = ",";
		                $fnipidval = "'0'";
		                foreach($fndroparray as $key => $val)
		                {
		                    $fnipidval .= $comma . "'" . $val['ipid'] . "'";
		                    $comma = ",";
		                }
		                */
			
		                $patient1 = Doctrine_Query::create()
		                ->select("*, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
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
							IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,
							,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
									->from('PatientMaster p')
									->leftJoin("p.EpidIpidMapping e")
									->where("e.clientid = ?", $logininfo->clientid)
									->andwhere("isdelete = 0")
									//ispc 1739
		                			->andWhereIn("ipid", $fnipidval );
									//->andwhere("ipid IN (" . $fnipidval . ")");
									//ispc-1739 REGEXP '".strtolower($regexp) ."'
						$myor  = '';
						$myor .= "lower(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "')) REGEXP ?";
						$myor .= " OR lower(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "')) REGEXP ?";
						$myor .= " OR concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) REGEXP ?";
						$myor .= " OR concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) REGEXP ?";
						$patient1->andwhere($myor, array($regexp, $regexp, $regexp, $regexp));
									/*
									 * original query before ispc-1739
									->andwhere("isdelete = 0 and ipid in(" . $fnipidval . ") and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('%" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
									concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
									concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
									concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))")
									*/		
						$patient1->orderby('status');
		                $droparray2 = $patient1->fetchArray();
		            }
		        }
		    }
		
		    $res_data = array();
		
		    if(is_array($droparray2) || is_array($droparray1))
		    {
		        $results = array_merge((array) $droparray2, (array) $droparray1);
		
		        foreach($results as $i => $res)
		        {
		            $res_data[$i]['status'] = $res['status'];
		            $res_data[$i]['epid'] = $res['EpidIpidMapping']['epid'];
		            $res_data[$i]['first_name'] = $res['first_name'];
		            $res_data[$i]['last_name'] = $res['last_name'];
		
		            if(strlen($res['middle_name']) > 0)
		            {
		                $res_data[$i]['middle_name'] = $res['middle_name'];
		            }
		            else
		            {
		                $res_data[$i]['middle_name'] = " ";
		            }
		
		            if($res['admission_date'] != '0000-00-00 00:00:00')
		            {
		                $res_data[$i]['admission_date'] = date('d.m.Y', strtotime($res['admission_date']));
		            }
		            else
		            {
		                $res_data[$i]['recording_date'] = "-";
		            }
		
		            if($res['recording_date'] != '0000-00-00 00:00:00')
		            {
		                $res_data[$i]['recording_date'] = date('d.m.Y', strtotime($res['recording_date']));
		            }
		            else
		            {
		                $res_data[$i]['recording_date'] = "-";
		            }
		
		            if($res['birthd'] != '0000-00-00 00:00:00')
		            {
		                $res_data[$i]['birthd'] = date('d.m.Y', strtotime($res['birthd']));
		            }
		            else
		            {
		                $res_data[$i]['birthd'] = "-";
		            }
		
		            $res_data[$i]['birthd'] = Pms_CommonData::hideInfo($res['birthd'], $res['isadminvisible']);
		
		            $res_data[$i]['id'] = Pms_Uuid::encrypt($res['id']);
		            $res_data[$i]['row'] = $_REQUEST['row'];
		        }
		        $this->view->droparray = $res_data;
		    }
		    else
		    {
		        $this->view->droparray = array();
		    }
		}
		
		

		public function saveuservwfilterAction()
		{
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    if($this->getRequest()->isPost())
		    {
		        $data = json_decode($_POST['details']);
		        
		        if(!empty($data))
		        {
		            $user_filter = new Application_Form_UserVwFilters ();
		            $result = $user_filter->set_filter($userid, $clientid, $data);
		        }
		    }
		    return $result;
		}

		
		
		
		
		
		public function attendingvoluntaryworkersAction()
		{
		    $this->_helper->layout->setLayout('layout_ajax');
		    	
		    // get associated clients of current clientid START
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		    if($connected_client){
		        $clientid = $connected_client;
		    } else{
		        $clientid = $logininfo->clientid;
		    }
 
		        $drop = Doctrine_Query::create()
		        ->select('*')
		        ->from('Voluntaryworkers')
		        ->where('clientid = "' . $clientid . '"')
		        ->andWhere("indrop = 0")
		        ->andWhere("isdelete = 0")
		        ->andWhere("inactive = 0")
		        ->orderBy('last_name ASC');
		        $droparray = $drop->fetchArray();
		        
		        if(empty($droparray))
		        {
		            $droparray = array();
		        }
		        
		  $this->view->droparray = $droparray;
		}

		public function overlappingmembershipsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $this->view->hidemagic = $hidemagic;
		
		    $return['new_intersected'] = '0';
		    $return['intersected'] = '0';
		    $return['error'] = '0';
		    $return['visits'] = "";
		    $return['inserted_overlapping'] = "";
		
		    if($_REQUEST['uid'])
		    {
		        $userid = $_REQUEST['uid'];
		    }
		    else
		    {
		        $userid = $logininfo->userid;
		    }
		    parse_str($_REQUEST['form_data'], $visits_details);
		    $x = 1;
// 		    print_r($visits_details); exit;
		    foreach($visits_details['membership'] as $date => $visit_values)
		    {
	            if(!empty($visit_values['start']) && !empty($visit_values['end']))
	            {
	                $post_visits['all'][$x]['start'] = $visit_values['start'].' 00:00:00';
	                $post_visits['all'][$x]['end'] = $visit_values['end'].' 00:00:00';
	                $post_visits['all'][$x]['visit_nr'] = $date;
	            } elseif(!empty($visit_values['start']) && empty($visit_values['end'])){
	                $post_visits['all'][$x]['start'] = $visit_values['start'].' 00:00:00';
	                $post_visits['all'][$x]['end'] = date("d.m.Y",time()).' 00:00:00';
	                $post_visits['all'][$x]['visit_nr'] = $date;
	            }
	            $x++;
		    }

		    $all_visits = $post_visits['all'];
		    
		    // check if the new visits are overlapping
		    foreach($all_visits as $k_vis => $v_vis)
		    { // check oll visits in form for overlapping
		        $visit[$k_vis]['start'] = $v_vis['start'];
		        $visit[$k_vis]['end'] = $v_vis['end'];
		        $visit[$k_vis]['visit_nr'] = $v_vis['visit_nr'];
		
		        if(!empty($v_vis['start']) && !empty($v_vis['end']))
		        {
		            foreach($all_visits as $k_vis_sec => $v_vis_sec)
		            {
		                if($k_vis != $k_vis_sec )
		                {
		                    $visit_sec[$k_vis_sec]['start'] = $v_vis_sec['start'];
		                    $visit_sec[$k_vis_sec]['end'] = $v_vis_sec['end'];
		                    $visit_sec[$k_vis_sec]['visit_nr'] = $k_vis_sec;
		
		                    if(Pms_CommonData::isintersected(strtotime($visit[$k_vis]['start']), strtotime($visit[$k_vis]['end']), strtotime($visit_sec[$k_vis_sec]['start']), strtotime($visit_sec[$k_vis_sec]['end'])))
		                    {
		                    	if( ! is_array($return['inserted_overlapping'])) {
		                    		$return['inserted_overlapping'] = array();
		                    	}
		                    	
		                        $return['new_intersected'] = '1';
		                        $return['inserted_overlapping'][] = $visit[$k_vis]['visit_nr'];
		                    }
		                }
		            }
		        }
		    }
		    echo json_encode($return);
		    exit;
		}

		public function overlappingvwcolorstatusAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $this->view->hidemagic = $hidemagic;
		
		    $return['new_intersected'] = '0';
		    $return['intersected'] = '0';
		    $return['error'] = '0';
		    $return['inserted_overlapping'] = "";
		    $return['error_invalid_period'] = "0";
		    $return['invalid_period'] = "";

		    $return['error_interrupted_period'] = "0";
		    $return['interrupted_period'] = "";
		    
		    
		    if($_REQUEST['uid'])
		    {
		        $userid = $_REQUEST['uid'];
		    }
		    else
		    {
		        $userid = $logininfo->userid;
		    }
		    parse_str($_REQUEST['form_data'], $periods_details);
		    $x = 1;

		    $count_unedned = 0;
		    $unended_periods = array();
		    
		    foreach($periods_details['color_status'] as $row_id => $period_values)
		    {
                $post_period[$x]['period_nr'] = $row_id;
                $post_period[$x]['start_date'] = $period_values['start_date'].' 00:00:00';

	                
	            if(!empty($period_values['start_date']) && !empty($period_values['end_date']))
	            {
	                $post_period[$x]['end_date'] = $period_values['end_date'].' 00:00:00';
	                
	            } elseif(!empty($period_values['start_date']) && empty($period_values['end_date'])){
	                
	            	//count un-ended periods
	            	$count_unedned ++;
	            	$unended_periods[] = $row_id;
	            	
	            	if (strtotime('now') < strtotime($period_values['start_date'])) {
	            		$post_period[$x]['end_date'] = date("d.m.Y", strtotime('+1 day', strtotime($period_values['start_date']))).' 00:00:00';
	            	} else {
	                	$post_period[$x]['end_date'] = date("d.m.Y", time()).' 00:00:00';
	            	}
	            }
	            
	            if ($count_unedned > 1) {
	            	if( ! is_array($return['invalid_period'])) {
	            		$return['invalid_period'] = array();
	            	}
	            	$return['invalid_period'] =  $unended_periods;
	            	$return['error_invalid_period'] = "1";
	            }
	            
                $post_period[$x]['start_date_full'] = date('Y-m-d H:i:s',strtotime($post_period[$x]['start_date']));
                $post_period[$x]['end_date_full'] = date('Y-m-d H:i:s',strtotime($post_period[$x]['end_date']));
                $post_period[$x]['start_date_Ymd'] = date('Y-m-d',strtotime($post_period[$x]['start_date']));
                $post_period[$x]['end_date_Ymd'] = date('Y-m-d',strtotime($post_period[$x]['end_date']));
	            
                if(strtotime($post_period[$x]['start_date']) > strtotime($post_period[$x]['end_date'])){
                	if( ! is_array($return['invalid_period'])) {
                		$return['invalid_period'] = array();
                	}
                    $return['invalid_period'][] =  $row_id;
                    $return['error_invalid_period'] = "1";
                }
	            $x++;
		    }

		    $post_period = array_values($post_period);
		    $sorted_data = $this->array_sort($post_period, 'start_date_Ymd', SORT_ASC);
		    $sorted_data = array_values($sorted_data);
		   
		    foreach($sorted_data as $sk => $pdata){
		        
		        if(!empty($sorted_data[$sk+1])){
		            
		            $datetime1 = new DateTime($sorted_data[$sk]['end_date_full']);
		            $datetime2 = new DateTime($sorted_data[$sk+1]['start_date_full']);
		            $interval = $datetime1->diff($datetime2);
		            $days_between =  $interval->format('%a');
		            if($days_between!=1){
		            	
		               	if( ! is_array($return['interrupted_period'])) {
		            		$return['interrupted_period'] = array();
		            	}
    		            $return['interrupted_period'][] =  $sorted_data[$sk]['period_nr'];
    		            $return['interrupted_period'][] =  $sorted_data[$sk+1]['period_nr'];
	       	            $return['error_interrupted_period'] = "1";
		            }
		        }
		    }
		    
		    // check if the new periods are overlapping
		    foreach($post_period as $k_vis => $v_vis)
		    { // check oll periods in form for overlapping
		        $period[$k_vis]['start_date'] = $v_vis['start_date'];
		        $period[$k_vis]['end_date'] = $v_vis['end_date'];
		        $period[$k_vis]['period_nr'] = $v_vis['period_nr'];
		
		        if(!empty($v_vis['start_date']) && !empty($v_vis['end_date']))
		        {
		            foreach($post_period as $k_vis_sec => $v_vis_sec)
		            {
		                if($k_vis != $k_vis_sec )
		                {
		                    $period_sec[$k_vis_sec]['start_date'] = $v_vis_sec['start_date'];
		                    $period_sec[$k_vis_sec]['end_date'] = $v_vis_sec['end_date'];
		                    $period_sec[$k_vis_sec]['period_nr'] = $k_vis_sec;
		
		                    if(Pms_CommonData::isintersected(strtotime($period[$k_vis]['start_date']), strtotime($period[$k_vis]['end_date']), strtotime($period_sec[$k_vis_sec]['start_date']), strtotime($period_sec[$k_vis_sec]['end_date'])))
		                    {
		                    	if( ! is_array($return['inserted_overlapping'])) {
		                    		$return['inserted_overlapping'] = array();
		                    	}
		                        $return['new_intersected'] = '1';
		                        $return['inserted_overlapping'][] = $period[$k_vis]['period_nr'];
		                    }
		                }
		            }
		        }
		    }
             
		    echo json_encode($return);
		    exit;
		}

		
		
		
		public function patientmedicationAction()
		{	
			//$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
						
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid); 
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Modules();
			$modulepriv_med = $previleges->checkModulePrivileges("148", $logininfo->clientid);
			
			
			
			// Get all medication 
			$pdrug = new PatientDrugPlan();
			$drugarray = $pdrug->getPatientDrugPlan($decid, true);
						
			$comma = "";
			$comma2 = "";
			$med_array = array();
			if($modulepriv_med ){
				foreach($drugarray as $key => $val)
				{
// 					$medca = Doctrine::getTable('Medication')->find($val['medication_master_id']);
// 					if(!empty($medca))
// 					{
// 						$medcaarray = $medca->toArray();
						
// 					}
					if($val['isbedarfs'] == '1')
					{
					    	
					    $med_array['b_bedarf'] .= $comma2 . $val['MedicationMaster']['name'];
					    $comma2 = " | ";
					}
					else if($val['isivmed'] == '1')
					{
					    	
					    $med_array['c_iv'] .= $comma2 . $val['MedicationMaster']['name'];
					    $comma2 = " | ";
					}
					else if($val['isschmerzpumpe'] == '1')
					{
					    	
					    $med_array['d_sc'] .= $comma2 . $val['MedicationMaster']['name'];
					    $comma2 = " | ";
					}
					else if($val['treatment_care'] == '1')
					{
					    	
					    $med_array['e_med'] .= $comma2 . $val['MedicationMaster']['name'];
					    $comma2 = " | ";
					}
					else if($val['isnutrition'] == '1')
					{
					    	
					    $med_array['f_med'] .= $comma2 . $val['MedicationMaster']['name'];
					    $comma2 = " | ";
					}
					else
					{
					    $med_array['a_med'] .= $comma . $val['MedicationMaster']['name'];
					    $comma = " | ";
					}
					ksort($med_array);
					
				}
			} else{
				
				foreach($drugarray as $key => $val)
				{
// 					$medca = Doctrine::getTable('Medication')->find($val['medication_master_id']);
// 					if(!empty($medca))
// 					{
// 						$medcaarray = $medca->toArray();
						
// 					}
					if($val['isbedarfs'] == '1')
					{
					    	
					    $med_array['b_bedarf'] .= $comma2 . $val['MedicationMaster']['name'] . ", " . $val['dosage'];
					    $comma2 = " | ";
					}
					else if($val['isivmed'] == '1')
					{
					    	
					    $med_array['c_iv'] .= $comma2 . $val['MedicationMaster']['name'] . ", " . $val['dosage'];
					    $comma2 = " | ";
					}
					else if($val['isschmerzpumpe'] == '1')
					{
					    	
					    $med_array['d_sc'] .= $comma2 . $val['MedicationMaster']['name'] . ", " . $val['dosage'];
					    $comma2 = " | ";
					}
					else if($val['treatment_care'] == '1')
					{
					    	
					    $med_array['e_med'] .= $comma2 . $val['MedicationMaster']['name']  ;
					    $comma2 = " | ";
					}
					else if($val['isnutrition'] == '1')
					{
					    	
					    $med_array['f_med'] .= $comma2 . $val['MedicationMaster']['name'] . ", " . $val['dosage'];
					    $comma2 = " | ";
					}
					else
					{
					    $med_array['a_med'] .= $comma . $val['MedicationMaster']['name'] . ", " . $val['dosage'];
					    $comma = " | ";
					}
					ksort($med_array);
					
				}
			}

			
			foreach($med_array as $med_type){
			    if(!empty($med_type)){
			        $medicationverord_arr[]= $med_type;
			    }
			}
			
			$medicationverord = implode("\n",$medicationverord_arr);
			
			echo $medicationverord;
			exit;
		}

		
		public function datevalidationAction(){
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    Pms_Validation::isdate($_REQUEST['adm_date']);
		    
		    
		    
		}
		
		
		
		public function btmbuchcheckAction(){
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid; 
		    $this->_helper->layout->setLayout('layout_ajax');
		    $decid = Pms_Uuid::decrypt($_REQUEST['id']);
		    $ipid = Pms_CommonData::getIpid($decid);
		    
            if( strlen($_REQUEST['method'])>0 && strlen($_REQUEST['medicationid']) > 0 ) 
            {
                
            	
            	$date_error = "";
            	 
            	$date = $_REQUEST['date'];
            	$time_arr = $_REQUEST['time'];
            	
            	if(strlen($date)>0)
            	{
            		 
            		if(!Pms_Validation::isdate($date)) {
            			$date_error = "ungültige Beginn datum";
            		}
            		if(strtotime($date) > strtotime(date("d.m.Y"))){
            			$date_error  = "Future Datum nicht erlaubt";
            		}
            		if(strlen($time_arr)){
            			$time = explode(':',$time_arr);
            			$full_start_date = mktime ( $time[0], $time[1],"0",  date("n",strtotime($date)),date("j",strtotime($date)), date("Y",strtotime($date)) );
            			if($full_start_date > strtotime(date("d.m.Y H:i", time()))  )
            			{
            				$date_error  = "Future Datum nicht erlaubt";
            			}
            		}
            		 
            		//ispc 1864 p.9
            		//documenting a method BEFORE that SEAL_DATE is not possible.
            		if ($date_error == '') {
            			$mcss  = new MedicationClientStockSeal();
            			$mcss_seal_date = $mcss->get_client_last_seal($clientid);
            			if ( ! empty($mcss_seal_date['seal_date'])
            					&& strtotime($date) < strtotime($mcss_seal_date['seal_date'])
            			) {
            				//btm seal_date error
            				$btmseal_lang = $this->view->translate('btmseal_lang');
            				$date_error  =  $btmseal_lang['error_btm_icon_date'];
            				 
            			}
            		}
            		 
            	}
            	
            	if ($date_error == "") 
            	{
	    		    $client_history = new MedicationClientHistory();
		       	    $user_stock = $client_history->get_user_stock($clientid,$_REQUEST['selectUser'],$_REQUEST['medicationid']);
	
	   	       	    if($user_stock){
	    	       	    $final_user_stock = $user_stock[0]['total_amount'];
	   	       	    } else{
	    	       	    $final_user_stock = 0;
	   	       	    }
 
	       	    
		       	    //get patient BTM current stock data
		       	    $patient_history = new MedicationPatientHistory();
		       	    $pat_history_arr = $patient_history->getAllMedicationPatientHistory($clientid, $ipid);
	       	    
		       	   
		       	    	       	    
		       	    foreach($pat_history_arr as $k_med => $pat_medication)
		       	    {
		       	        $patient_medications_amount[$pat_medication['medicationid']] = $pat_medication['total_amount'];
		       	    }
	       	    
	       	    	
	   	       	    switch ($_REQUEST['method']){
	                    // PLUS METHODS (add into patient stock)
	   	       	        case "7": // Ubergabe :: REMOVE from user stock and ADD to patient stock
	
	   	       	                $diff = $final_user_stock  - ($_REQUEST['amount']);
	   	       	                $return['user_current_amount'] = $final_user_stock; 
	
	//    	       	        	ispc 1864 p.10
	//    	       	        	if a user adds a docu earlier than "now".
	//    	       	        	like he documents that he used 5 BTM yesterday for patient JOHN DOE,
	//    	       	        	then we need to assure that the AMMOUNT was available at THAT time.
	   	       	                
	   	       	                $rez = $client_history->validate_amount_by_date(array_merge($_POST , array("ipid"=>$ipid , "clientid"=>$clientid)) );
	   	       	                if ( ! $rez ['result'] && $rez ['next_available_date'] ===  false ) {
	   	       	                	//user cannot insert this qty/amount at any given date... this should never occur, javascript should prevent this
	   	       	                } elseif ( ! $rez ['result'] ) {
	   	       	                	
	   	       	                	$btm_documenting_date_lang = $this->view->translate('btm_documenting_date_lang');
	   	       	                	$date_error = sprintf( $btm_documenting_date_lang['error_btm_zugang_ubergabe'],
	   	       	                			$rez ['t0'],
	   	       	                			$rez ['t0_amount'],
	   	       	                			$rez ['next_available_date'],
	   	       	                			$rez ['amount_available_date']
	   	       	                	);
	   	       	                	$diff = -1;
	   	       	                }
	   	       	                //else chosen date is OK , continue as usual
	   	       	               
	   	       	                
		       	              break;
	 	       	              
	   	       	        case "10": // Lieferung :: ADD to patient stock - does not need validation
	   	       	              $diff = 0; // allow
	 	       	              break;
	 	       	              
	      	            // MINUS METHODS (remove some qty from patient stock) 	       	              
	   	       	        case "8": // Verbrauch:: REMOVE from user stock and GIVE TO CONSUMPTION
	   	       	                  // Verbrauch:: REMOVE from patient stock and GIVE TO CONSUMPTION
	   	       	                switch ($_REQUEST['amount_source']){
	   	       	                    case "u": // REMOVE from user stock and GIVE TO CONSUMPTION
	   	       	                        $diff = $final_user_stock  - ($_REQUEST['amount']);
	   	       	                        $return['user_current_amount'] = $final_user_stock;
	   	       	                        
	   	       	                        //ispc 1864 p.10
										$rez = $client_history->validate_amount_by_date(array_merge($_POST , array("ipid"=>$ipid , "clientid"=>$clientid)) );
	   	       	                		if ( ! $rez ['result'] && $rez ['next_available_date'] ===  false ) {
	   	       	                			//user cannot insert this qty/amount at any given date... this should never occur, javascript should prevent this
	   	       	                		} elseif ( ! $rez ['result'] ) {
	   	       	                			$btm_documenting_date_lang = $this->view->translate('btm_documenting_date_lang');
	   	       	                			$date_error = sprintf( $btm_documenting_date_lang['error_btm_zugang_ubergabe'],
	   	       	                					$rez ['t0'],
	   	       	                					$rez ['t0_amount'],
	   	       	                					$rez ['next_available_date'],
	   	       	                					$rez ['amount_available_date']
	   	       	                			);
	   	       	                			$diff = -1;
	   	       	                		}
	   	       	                		//else chosen date is OK , continue as usual	   	       	                         
	   	       	                        break;
	   	       	                              
	   	       	                    case "p": // REMOVE from patient stock and GIVE TO CONSUMPTION
	   	       	                        $diff = $patient_medications_amount[$_REQUEST['medicationid']]  - ($_REQUEST['amount']);
	   	       	                        $return['patient_current_amount'] = $patient_medications_amount[$_REQUEST['medicationid']];
	   	       	                        
	   	       	                        // $ispc_1864_p_10	   	       	                       
	   	       	                        $rez = $patient_history->validate_amount_by_date(array_merge($_POST , array("ipid"=>$ipid , "clientid"=>$clientid)) );
	   	       	                        if ( ! $rez ['result'] && $rez ['next_available_date'] ===  false ) {
	   	       	                        	//user cannot insert this qty/amount at any given date... this should never occur, javascript should prevent this
	   	       	                        } elseif ( ! $rez ['result'] ) {
	   	       	                        	$btm_documenting_date_lang = $this->view->translate('btm_documenting_date_lang');
	   	       	                        	$date_error = sprintf( $btm_documenting_date_lang['error_btm_abgabe_verbrauch'],
	   	       	                        			$rez ['t0'],
	   	       	                        			$rez ['t0_amount'],
	   	       	                        			$rez ['next_available_date'],
	   	       	                        			$rez ['amount_available_date']
	   	       	                        	);
	   	       	                        	$diff = -1;
	   	       	                        }
	   	       	                        //else chosen date is OK , continue as usual
	   	       	                        
	   	       	                        break;
	   	       	                    default:
	   	       	                        break;
	   	       	                }   	       	            
	 	       	              break;
	   	       	            
	   	       	        case "9": // Ruckgabe an Benutzer:: REMOVE from patient stock and ADD to user stock
	   	       	              $diff = $patient_medications_amount[$_REQUEST['medicationid']]  - ($_REQUEST['amount']);
	   	       	              $return['patient_current_amount'] = $patient_medications_amount[$_REQUEST['medicationid']];
	   	       	              
	   	       	              // $ispc_1864_p_10
	   	       	              $rez = $patient_history->validate_amount_by_date(array_merge($_POST , array("ipid"=>$ipid , "clientid"=>$clientid)) );
	   	       	              if ( ! $rez ['result'] && $rez ['next_available_date'] ===  false ) {
	   	       	              	//user cannot insert this qty/amount at any given date... this should never occur, javascript should prevent this
	   	       	              } elseif ( ! $rez ['result'] ) {
	   	       	              	$btm_documenting_date_lang = $this->view->translate('btm_documenting_date_lang');
	   	       	              	$date_error = sprintf( $btm_documenting_date_lang['error_btm_abgabe_verbrauch'],
	   	       	              			$rez ['t0'],
	   	       	              			$rez ['t0_amount'],
	   	       	              			$rez ['next_available_date'],
	   	       	              			$rez ['amount_available_date'] 
	   	       	              	);
	   	       	              	$diff = -1;
	   	       	              }
	   	       	              //else chosen date is OK , continue as usual

	 	       	              break;
	   	       	            
	   	       	        case "11": // Sonstiges :: REMOVE from patient stock
	   	       	              $diff = $patient_medications_amount[$_REQUEST['medicationid']]  - ($_REQUEST['amount']);
	   	       	              $return['patient_current_amount'] = $patient_medications_amount[$_REQUEST['medicationid']];
	   	       	              // $ispc_1864_p_10
	   	       	              $rez = $patient_history->validate_amount_by_date(array_merge($_POST , array("ipid"=>$ipid , "clientid"=>$clientid)) );
	   	       	              if ( ! $rez ['result'] && $rez ['next_available_date'] ===  false ) {
	   	       	              	//user cannot insert this qty/amount at any given date... this should never occur, javascript should prevent this
	   	       	              } elseif ( ! $rez ['result'] ) {
	   	       	              	$btm_documenting_date_lang = $this->view->translate('btm_documenting_date_lang');
	   	       	              	$date_error = sprintf( $btm_documenting_date_lang['error_btm_abgabe_verbrauch'],
	   	       	              			$rez ['t0'],
	   	       	              			$rez ['t0_amount'],
	   	       	              			$rez ['next_available_date'],
	   	       	              			$rez ['amount_available_date']
	   	       	              	);
	   	       	              	$diff = -1;
	   	       	              }
	   	       	              //else chosen date is OK , continue as usual
	   	       	              
	 	       	              break;
	   	       	        default:
	   	       	            break;
	   	       	    }
   	       	    
	   	       	    if( $diff >= 0){
	   	       	        $return['allow_operation'] = "1";
	   	       	    } else {
	   	       	        $return['allow_operation'] = "0";
	   	       	    }
            	
            	}// end if ($date_error == "") 
   	       	    
   	       	    
   	       	    
            } else {
                $return['allow_operation'] = "0";
                $date_error = "Methode wählen";
            }
			
            $return['date_error'] = $date_error ;
            echo json_encode($return);
            exit;
		}

		
		
		
		public function clientbtmbuchcheckAction(){
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid; 
		    $this->_helper->layout->setLayout('layout_ajax');
   		    $client_history = new MedicationClientHistory();
   		    $btmbuch = new MedicationClientHistory();
   		    
            if( strlen($_REQUEST['method'])>0 && strlen($_REQUEST['medicationid']) > 0 )
            {
    		    $user = new User();
    		    $client_users = $user->get_client_users($clientid);
    		    foreach($client_users as $k=>$user_data) 
    		    {
    		        $usersarray[] = $user_data['id'];
    		    }
    		    
    		    $medication_stock = MedicationClientStock::getMedicationClientdetails($clientid, $_REQUEST['medicationid']);
                $medication_id = $_REQUEST['medicationid'];
                
    		    //prepare medis stocks array to get medis data by medis stocks id
    		    $medisstr = "'99999999'";
    		    $comma = ",";
    		    foreach($medication_stock as $stocmedis)
    		    {
    		        $stocmedications[$stocmedis['medicationid']] = $stocmedis;
    		        $medisstr .= $comma . "'" . $stocmedis['medicationid'] . "'";
    		        $comma = ",";
    		    }
                
                //get medication stocks medis id
                $med = Doctrine_Query::create()
                ->select('*')
                ->from('Medication')
                ->where('isdelete = 0 ')
                ->andWhere('name!=""')
                ->andWhere('id = ?', $medication_id)
                ->andWhere('clientid = ?', $clientid);
                $medarray = $med->fetchArray();
                
                foreach($medarray as $medication)
                {
                    if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
                    {
                        $medicationsarray[$medication['id']] = $medication;
                        $medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
                    }
                }
                
                
    		    $btm = $client_history->getDataForUsers($clientid,$usersarray);
    		    foreach($btm as $record)
    		    {
    		        $btmuserdata[$record['userid']][$record['medicationid']] = $record;
    		    }
    		    
                foreach($medicationsarray as $keym => $medication)
                {
                    $final[$keym]['id'] = $medication['id'];
                    $final[$keym]['name'] = $medication['name'];
                    $final[$keym]['stock'] = $medication['amount'];
                    
                    foreach($usersarray as $keyu => $userid)
                    {
                        //exclude dummy control
                        if($userid != '99999999')
                        {
                            if($userid == $btmuserdata[$userid][$keym]['userid'] && $medication['id'] == $btmuserdata[$userid][$keym]['medicationid'])
                            {
                                $final_userdata['user'][$keym][$userid] = $btmuserdata[$userid][$keym]['total'];
                            }
                            else
                            {
                                $final_userdata['user'][$keym][$userid] = 0;
                            }
                        }
                    }
                    $medicationsarray[$keym]['users'] = $final_userdata['user'];
                }
                
                $final_user_stock = array();
                foreach($medicationsarray as $medication_key => $medication_details){
                    $final_user_stock[$medication_key][0] = $medication_details['total'];
                    
                    foreach($medication_details['users'] as $med_key=> $user_stocks){
                        foreach($user_stocks as $uid=>$ustock){
                            $final_user_stock[$medication_key][$uid] = $ustock;
                        }
                        
                    }
                }
                
    		    // patient stock 
                $patient_ipid = ""; 
    		    if(!empty($_REQUEST['send2patient']) && $_REQUEST['send2patient'] != "0" || !empty($_REQUEST['removefrompatient']) && $_REQUEST['removefrompatient'] != "0")
    		    {
    		        if(!empty($_REQUEST['send2patient']) && $_REQUEST['send2patient'] != "0")
    		        {
    		            $patient_ipid = $_REQUEST['send2patient']; 
    		        } 
    		        elseif(!empty($_REQUEST['removefrompatient']) && $_REQUEST['removefrompatient'] != "0")
    		        {
    		            $patient_ipid = $_REQUEST['removefrompatient']; 
    		        }
    		    }
    		     
    		    if(strlen($patient_ipid) > 0 )
    		    {
        		    //get patient BTM current stock data
        		    $medipat = new MedicationPatientHistory();
        		    $pat_history_arr = $medipat->getAllMedicationPatientHistory($clientid, $patient_ipid,$medication_id);
        		     
        		    foreach($pat_history_arr as $k_med => $pat_medication)
        		    {
        		        $patient_medications_amount[$pat_medication['medicationid']] = $pat_medication['total_amount'];
        		    }
    		    }
    		    
    		    $select_user = $_REQUEST['selectUser'];
    		    
    		    //  APPLY METHODS
    		    switch ($_REQUEST['method']){
    		        // PLUS methods
    		        case "1"://Remove from USER/GROUP and add to USER/GROUP :: Ubergabe / Ubergabe von ... an Benutzer
    		              $diff = $final_user_stock[$_REQUEST['medicationid']][$select_user] - ($_REQUEST['amount']);
    		            break;
    		            
    		        case "3"://Add to GROUP :: Sonstiges
    		            $diff = 0; // allow
    		            break;
    		            
    		        case "12"://Remove from PATIENT add to USER :: Rucknahme von Patient
    		            $diff = $patient_medications_amount[$_REQUEST['medicationid']]  - ($_REQUEST['amount']);
    		            $return['patient_current_amount'] = $patient_medications_amount[$_REQUEST['medicationid']];    		            
    		            break;
    		            
    		        // MINUS methods
    		        case "4"://Remove from USER/GROUP add to USER / GROUP:: Ubergabe an Benutzer 
    		            $diff = $final_user_stock[$_REQUEST['medicationid']][$_REQUEST['current_user']] - ($_REQUEST['amount']);
    		            
    		            break;
    		        case "5"://Remove from USER add to PATIENT:: Abgabe an Patient 
    		            $diff = $final_user_stock[$_REQUEST['medicationid']][$_REQUEST['current_user']] - ($_REQUEST['amount']);
    		            
    		            break;
    		        case "6"://Remove from USER :: Sonstiges 
    		            $diff = $final_user_stock[$_REQUEST['medicationid']][$_REQUEST['current_user']] - ($_REQUEST['amount']);
    		            
    		            break;
    		            
                default:
    		            break;
    		        
    		    }
    		    
    		    if( $diff >= 0){
    		        $return['allow_operation'] = "1";
    		    } else {
    		        $return['allow_operation'] = "0";
    		    }
    		    
    		} else {
                $return['allow_operation'] = "0";
            }
		    
            echo json_encode($return);
            exit;
 
		}

        public function deletevwfileAction()
        {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $act['deleted'] = "0";
            
            if (!empty($_REQUEST['id'])){
                
                if ( $_REQUEST['delete_file_id'] > 0 && strlen($_REQUEST['delete_file_tabname']) > 0  && $_REQUEST['is_image'] == 0) {
                    $delete_id = $_REQUEST['delete_file_id'];
                    $delete_tabname = $_REQUEST['delete_file_tabname'];
                    
                    $client_file_form = new Application_Form_ClientFileUpload();
                    $client_file_form->deleteFile($delete_id,$delete_tabname);
                    
                } else if ( $_REQUEST['delete_file_id'] == 0 && strlen($_REQUEST['delete_file_tabname']) == 0  && $_REQUEST['is_image'] == 1) {
                    $stmb = Doctrine::getTable('Voluntaryworkers')->find($_REQUEST['id']);
                    $stmb->img_deleted = '1';
                    $stmb->save();
                }
                
                $act['deleted'] = "1" ;
            
            } else{
                
                $act['deleted'] = "0";
            }
            echo json_encode($act);
            exit();
        }
		
        public function servicesAction()
        {
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$clientid = $logininfo->clientid;
        	$this->_helper->layout->setLayout('layout_ajax');
        	
        	if(strlen($_REQUEST['q']) > 0)
        	{
        			$search_str = addslashes(urldecode($_REQUEST['q']));  
        			$srchoption = "trim(lower(services_name)) like ?";
        			$order = 'services_name';
        	
        		$drugs = Doctrine_Query::create()
        		->select('*')
        		->from('Services')
        		->where(" " . $srchoption . " and isdelete= ? and clientid= ?", array("%".trim(mb_strtolower($search_str, 'UTF-8'))."%","0",$clientid) )
        		->limit('150')
        		->orderBy('' . $order . ' ASC');
        		$drop_array = $drugs->fetchArray();

        		foreach($drop_array as $key => $val)
        		{
        			$drop_array[$key]['id'] = $val['id'];
        			$drop_array[$key]['services_name'] = html_entity_decode($val['services_name'], ENT_QUOTES, "UTF-8");
        	
        			$drop_array[$key]['row'] = $_REQUEST['row'];
        		}
        		$this->view->droparray = $drop_array;
        	}
        	else
        	{
        		$this->view->droparray = array();
        	}
        	
        }
        

        /**
         * export_highcharts
         */
        public function exporthighchartsAction()
        {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            
            
            $svg = $this->getRequest()->getPost('svg');
            // create image from svg
            $temp_img = $this->temporary_image_create ( $svg, 'svg' );
        
            if ($temp_img !== false) {
                	
                $encid = $this->getRequest()->getQuery('id');
                $decid = Pms_Uuid::decrypt($encid);
                $patientmaster = new PatientMaster();
                $parr = $patientmaster->getMasterData($decid, 0);
                
                $htmlHead = "<div>" . htmlspecialchars($parr['nice_name_epid']) . "<br />" . htmlspecialchars($parr['nice_address']) . "</div><br />";
     
                // create new PDF document
                $pdf = new Pms_PDF ( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );
                $pdf->SetAutoPageBreak(TRUE, 10);
                //set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                //set some language-dependent strings
                $pdf->setLanguageArray('de');
                // set font
                $pdf->SetFont('dejavusans', '', 10);
                // add a page
                $pdf->AddPage('P', 'A4');
                
                $pdf->writeHTML($htmlHead, true, 0, true, 0);
                
                $pdf->setJPEGQuality ( 100 );
                
                // Maria:: Migration ISPC to CISPC 08.08.2020
                //$pdf->Image ( $temp_img, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = true );
                $pdf->ImageSVG ( $temp_img, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = true );
                	
                unlink ( $temp_img );
                	
                $pdf->lastPage ();
                // Maria:: Migration ISPC to CISPC 08.08.2020
                //ISPC-2564 Carmen 25.06.2020
                if($this->getRequest()->getQuery('chart'))
                {
                	$pdf->Output (  Pms_CommonData::beautify_filename("RASS {$parr['nice_name_epid']}") .'.pdf', 'D' );
                }
                else
                {
                	$pdf->Output (  Pms_CommonData::beautify_filename("Vitalwerte {$parr['nice_name_epid']}") .'.pdf', 'D' );
                }
                //--
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

        private function temporary_image_create($data, $type = 'svg', $qtype = 'human') {
            // Maria:: Migration ISPC to CISPC 08.08.2020
            $tmp_file = uniqid('img' . rand(1000, 9999));
            $tmp_folder = APPLICATION_PATH . '/../public/temp';
            $this->temporary_files_delete($tmp_folder, '7200'); //delete all files older than 2 hours
            
            switch ($type) {
                case 'svg' :
                    if (get_magic_quotes_gpc ()) {
                        $data = stripslashes ( $data );
                    }
        
                    $data = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . $data;

                    $svg_tmp_file = $tmp_folder . '/' . $tmp_file . '.svgs';

                    file_put_contents($svg_tmp_file, $data);

                    $tmp_file_path = $tmp_folder . '/' . $tmp_file . '.jpg';
//                     $tmp_file_path = TMP_ABSPATH . '/_images/' . $tmp_file . '.jpg';
        
                    $handle = fopen ( $tmp_file_path, 'w+' );
                    fclose ( $handle );

                    $tmp_file_path = $svg_tmp_file;

                    //passing a file from $_POST directly to shell, this is a new kind of stupid, cutting corners in the name of ....?

                    //system('/usr/bin/inkscape -z '.$svg_tmp_file.' -e '.$tmp_file_path.' 2>&1');

                    /*$im = new Imagick ();
                    $im->readImageBlob ( $data );
                    $im->setImageFormat ( "jpeg" );
        
                    $im->writeImage ( $tmp_file_path );
        
                    $im->clear ();
                    $im->destroy ();*/
                    
                    break;
                     
                case 'base64' :
                    //bla bla neinteresant
        
                    break;
                     
                default :
                    break;
            }
        
            if (is_readable ( $tmp_file_path )) {
                return $tmp_file_path;
            } else {
                return false;
            }
        }
        
        
        public function loadweightchartAction()
        {
            
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            $this->view->patient_enc_id = $_GET['id']; 
            
            $graph_data = array();
            
            $clientid = $logininfo->clientid;           
            $modules = new Modules();           	
//             if($modules->checkModulePrivileges("132", $clientid))
//             {
//             	$this->view->show_height_detail = 1;
//             	$show_height = true;
//             }
//             else
//             {
//             	$this->view->show_height_detail = 0;
//             	$show_height = false;
//             }
            //show body_area_surface
//             if($modules->checkModulePrivileges("138", $clientid))
//             {
//             	$this->view->show_bas_detail = 1;
//             }
//             else
//             {
//             	$this->view->show_bas_detail = 0;
//             }
            
            
//             if($modules->checkModulePrivileges("139", $clientid))
//             {
//             	$show_head_circumference = 1;
//             }
//             else
//             {
//             	$show_head_circumference  = 0;
//             }
            $period = false;
            
            if($this->getRequest()->isPost())
            {
                if(strlen($_POST['from']) > 0  && strlen($_POST['to']) > 0 )
                {
                    $period['start'] = date("Y-m-d",strtotime($_POST['from']));
                    $period['end'] = date("Y-m-d",strtotime($_POST['to']));
                }
                else
                {
                    $period = false;
                }
            }
            $all_vital_signs = FormBlockVitalSigns::get_patients_chart($ipid,$period);
            // Maria:: Migration ISPC to CISPC 08.08.2020
            //ISPC-1439 @Lore 03.10.2019
            $all_bowel_movement = FormBlockBowelMovement::get_patients_chart($ipid,$period);


            if(empty($all_vital_signs) && empty($all_bowel_movement))
            {
            	//no data in our tables
            	$this->view->nodata = "1";
            	return;
            }
            
            /*
             * [icon_settings] => Array
        (
                [weight] => 1
                [head_circumference] => 1
                [waist_circumference] => 1
                [height] => 1
                [oxygen_saturation] => 1
                [temperature] => 1
                [blood_sugar] => 1
                [blood_pressure] => 1
                [bas] => 1
                [allways_display] => 1
        )
             */
            
            $show_weight = true; // this was by default ON
            $show_head_circumference = false; // this is set from module... so module is not removed
            $show_waist_circumference = true; 
            $show_height = false; //changed to default OFF 
            $show_oxygen_saturation = false;
            $show_temperature = false;
            $show_bas = false; // show body_area_surface text
            $show_blood_sugar = false; // show blood sugar (BZ)
            $show_blood_pressure = false; // show blood sugar (BZ)
            //ISPC-1439 @Lore 03.10.2019
            $show_bowel_movement = false;

            /*
             * this param was first added for the mobile version
             * so we fetch here the settings, not from the what a user is sending us
             */
            $use_icon_settings = $this->getRequest()->getParam('use_icon_settings');

            if ( ! is_null($use_icon_settings)) {
                $im = new IconsMaster();
                $sys_icon_details = $im->get_system_icons($logininfo->clientid, 49);
                
                if ( ! empty($sys_icon_details[49]['custom']['icon_settings']) 
                    && null !== ($icon_settings = json_decode($sys_icon_details[49]['custom']['icon_settings'], true)) ) 
                {
                    $_REQUEST['icon_settings'] = $icon_settings;
                }
                
            }

            if(!empty($_REQUEST['icon_settings'])) {
            	
            	if (isset($_REQUEST['icon_settings']['weight']) && $_REQUEST['icon_settings']['weight'] != "1") {
            		//default module ON = true is canceled by this 
            		$show_weight = false;
            	}
            	if (isset($_REQUEST['icon_settings']['head_circumference']) && $_REQUEST['icon_settings']['head_circumference'] == "1") {
            		//default module ON = true is canceled by this
            		$show_head_circumference = true;
            	}
            	if (isset($_REQUEST['icon_settings']['waist_circumference']) && $_REQUEST['icon_settings']['waist_circumference'] == "1") {
            		$show_waist_circumference = true;
            	}
            	
            	if (isset($_REQUEST['icon_settings']['oxygen_saturation']) && $_REQUEST['icon_settings']['oxygen_saturation'] == "1") {
            		$show_oxygen_saturation = true;
            	}
            	if (isset($_REQUEST['icon_settings']['temperature']) && $_REQUEST['icon_settings']['temperature'] == "1") {
            		$show_temperature = true;
            	}
            	
            	if (isset($_REQUEST['icon_settings']['height']) && $_REQUEST['icon_settings']['height'] == "1") {
            		$show_height = true;
            	}
            	
            	
            	if (isset($_REQUEST['icon_settings']['bas']) && $_REQUEST['icon_settings']['bas'] == "1") {
            		$show_bas = true;
            	}
            	
            	if (isset($_REQUEST['icon_settings']['blood_sugar']) && $_REQUEST['icon_settings']['blood_sugar'] == "1") {
            		$show_blood_sugar = true;
            	}
            	
            	if (isset($_REQUEST['icon_settings']['blood_pressure']) && $_REQUEST['icon_settings']['blood_pressure'] == "1") {
            		$show_blood_pressure = true;
            	}
            	
            	if (isset($_REQUEST['icon_settings']['bowel_movement']) && $_REQUEST['icon_settings']['bowel_movement'] == "1") {
            	    $show_bowel_movement = true;
            	}
            }
            

            $i_serie = 0;
            /*

black. color remains unused for now
             */
            
            if ($show_weight) {
	            $i_weight = $i_serie;
	            $i_serie++;
	            $graph_data[$i_weight] = array(
	            		'name'	=> $this->view->translate('weight'),
	            		'id'	=> 'weight',
	            		'type'	=> 'line',
	            		'visible' => true,
	            		'linkedTo'	=> null,
	            		'lineWidth'	=> 2,
	            		'pointPlacement'	=> 'on',
	            		//'whiskerLength'	=> '20%',
	            		'showInLegend'	=> true,
	            		'color'	=> '#4572A7',
	            		'tooltip'	=> array(
	            				"pointFormat" => "<b>{series.name}: {point.y:,.f} kg</b><br/>{point.x:%d.%m.%Y %H:%M}"
	            		)
	            );
            }          
            
            if($show_head_circumference) {
            	$i_head_circumference = $i_serie;
            	$i_serie++;
                $graph_data[$i_head_circumference] = array(
                		'name'	=> $this->view->translate('head_circumference'),
                		'id'	=> 'head_circumference',
                		'type'	=> 'line',
                		'visible' => true,
                		'linkedTo'	=> null,
                		'lineWidth'	=> 2,
                		'pointPlacement'	=> 'on',
                		//'whiskerLength'	=> '20%',
                		'showInLegend'	=> true,
                		'color'	=> '#AA4643',
                		'tooltip'	=> array(
                				"pointFormat" => "<b>{series.name}: {point.y:,.f} cm</b><br/>{point.x:%d.%m.%Y %H:%M}"
                		)
                );
            }
            
            if($show_waist_circumference) {
            	$i_waist_circumference = $i_serie;
            	$i_serie++;
            	$graph_data[$i_waist_circumference] = array(
            			'name'	=> $this->view->translate('waist_circumference'),
            			'id'	=> 'waist_circumference',
            			'type'	=> 'line',
            			'visible' => true,
            			'linkedTo'	=> null,
            			'lineWidth'	=> 2,
            			'pointPlacement'	=> 'on',
            			//'whiskerLength'	=> '20%',
            			'showInLegend'	=> true,
            			'color'	=> '#0B6623',
            			'tooltip'	=> array(
            					"pointFormat" => "<b>{series.name}: {point.y:,.f} cm</b><br/>{point.x:%d.%m.%Y %H:%M}"
            			)
            	);
            }
            
            if($show_height) {
            	$i_height = $i_serie;
            	$i_serie++;
            	$graph_data[$i_height] = array(
            			'name'	=> $this->view->translate('size'),
            			'id'	=> 'size',
            			'type'	=> 'line',
            			'visible' => true,
            			'linkedTo'	=> null,
            			'lineWidth'	=> 2,
            			'pointPlacement'	=> 'on',
            			//'whiskerLength'	=> '20%',
            			'showInLegend'	=> true,
            			'color'	=> '#89A54E',
            			'tooltip'	=> array(
            					"pointFormat" => "<b>{series.name}: {point.y:,.f} cm</b><br/>{point.x:%d.%m.%Y %H:%M}"
            			)
            	);
            }
            
            if($show_oxygen_saturation) {
            	$i_oxygen_saturation = $i_serie;
            	$i_serie++;
            	$graph_data[$i_oxygen_saturation] = array(
            			'name'	=> $this->view->translate('oxygen_saturation'),
            			'id'	=> 'oxygen_saturation',
            			'type'	=> 'line',
            			'visible' => true,
            			'linkedTo'	=> null,
            			'lineWidth'	=> 2,
            			'pointPlacement'	=> 'on',
            			//'whiskerLength'	=> '20%',
            			'showInLegend'	=> true,
            			'color'	=> '#80699B',
            			'tooltip'	=> array(
            					"pointFormat" => "<b>{series.name}: {point.y:,.f} %</b><br/>{point.x:%d.%m.%Y %H:%M}"
            			)
            	);
            }
            
            if($show_temperature) {
            	$i_temperature = $i_serie;
            	$i_serie++;
            	$graph_data[$i_temperature] = array(
            			'name'	=> $this->view->translate('temperature'),
            			'id'	=> 'temperature',
            			'type'	=> 'line',
            			'visible' => true,
            			'linkedTo'	=> null,
            			'lineWidth'	=> 2,
            			'pointPlacement'	=> 'on',
            			//'whiskerLength'	=> '20%',
            			'showInLegend'	=> true,
            			'color'	=> '#3D96AE',
            			'tooltip'	=> array(
            					"pointFormat" => "<b>{series.name}: {point.y:,.f} °C</b><br/>{point.x:%d.%m.%Y %H:%M}"
            			)
            	);
            	
            }
            
            if($show_blood_sugar) {
            	$i_blood_sugar = $i_serie;
            	$i_serie++;
            	$graph_data[$i_blood_sugar] = array(
            			'name'	=> $this->view->translate('blood_sugar'),
            			'id'	=> 'blood_sugar',
            			'type'	=> 'line',
            			'visible' => true,
            			'linkedTo'	=> null,
            			'lineWidth'	=> 2,
            			'pointPlacement'	=> 'on',
            			//'whiskerLength'	=> '20%',
            			'showInLegend'	=> true,
            			'color'	=> '#ff6600',
            			'tooltip'	=> array(
            					"pointFormat" => "<b>{series.name}: {point.y:,.f} mg/dl</b><br/>{point.x:%d.%m.%Y %H:%M}"
            			)
            	);
            	
            }
                        
            if($show_blood_pressure) {
            	$i_blood_pressure = $i_serie;
            	$i_serie++;
            	$graph_data[$i_blood_pressure] = array(
            			'name'	=> $this->view->translate('blood_pressure'),
            			'id'	=> 'blood_pressure',
            			'type'	=> 'errorbar',
            			'visible' => true,
            			'linkedTo'	=> null,
            			'lineWidth'	=> 2,
            			'pointPlacement'	=> 'on',
            			'whiskerLength'	=> '20%',
            			'showInLegend'	=> true,
            			'color'	=> '#89ff35',
            			'tooltip'	=> array(
                			    // TODO-4186 Ancuta 08.06.2021 
             					//"pointFormat" => "<b>{series.name}</b><br/>[" . $this->view->translate('systolic') . "]: <b>{point.high:,.f}</b><br/>[" . $this->view->translate('diastolic') . "]: <b>{point.low:,.f}</b><br/>{point.x:%d.%m.%Y %H:%M}"
            					"pointFormat" => "<b>{series.name}</b><br/>[" . $this->view->translate('systolic') . "]: <b>{point.low:,.f}</b><br/>[" . $this->view->translate('diastolic') . "]: <b>{point.high:,.f}</b><br/>{point.x:%d.%m.%Y %H:%M}"
                			    //--
            			)
            		);

            }
            
             //ISPC-1439 @Lore 03.10.2019
            if($show_bowel_movement) {
                $i_bowel_movement = $i_serie;
                $i_serie++;
                $graph_data[$i_bowel_movement] = array(
                    'name'	=> $this->view->translate('bowel_movement'),
                    'id'	=> 'bowel_movement',
                    'type'	=> 'line',
                    'visible' => true,
                    'linkedTo'	=> null,
                    'lineWidth'	=> 2,
                    'pointPlacement'	=> 'on',
                    'whiskerLength'	=> '20%',
                    'showInLegend'	=> true,
                    'color'	=> '#7d13cf',
                    'tooltip'	=> array(
                        "pointFormat" => "<b>{series.name}</b><br/>{point.x:%d.%m.%Y %H:%M}"
                    )
                );

            }


//             $graph_data[2]['name'] = 'Größe';
            
            $current_weight = 0;
            $current_height = 0;
            
            foreach($all_vital_signs as $ipid => $w_data){
                foreach($w_data as $k =>$w){
                	
//                 	$gdate = date("d.m.Y",strtotime($w['date']));
                	$gdate = strtotime($w['date']) * 1000;
                    $graphdate[] = $gdate;
                    
                    if($w['weight'] != "0.00" ) {
                        $current_weight = (float) $w['weight'];
                        
                        if ($show_weight) {
                        	$graph_data[$i_weight]['data'][] = array(
                        			"x"	=> $gdate,
                        			"y"	=> (float)$w['weight']
                        	);
                        }
                    }

                    
                    if($show_head_circumference && $w['head_circumference'] != "0.00" ) {
                    	$graph_data[$i_head_circumference]['data'][] = array(
                    			"x"	=> $gdate,
                    			"y"	=> (float)$w['head_circumference']
                    	);
                    }
                    
                    if($show_waist_circumference && $w['waist_circumference'] != "0.00" ) {
                    	$graph_data[$i_waist_circumference]['data'][] = array(
                    			"x"	=> $gdate,
                    			"y"	=> (float)$w['waist_circumference']
                    	);
                    }                    
                    
                    if($w['height'] != "0.00" ) {
                    	$current_height = (float) $w['height'];
                    	
                    	if ($show_height) {
                    		$graph_data[$i_height]['data'][] = array(
                    				"x"	=> $gdate,
                    				"y"	=> (float)$w['height']
                    		);
                    	}
                    }

                    
                    if($show_oxygen_saturation && $w['oxygen_saturation'] != "0.00" ) {
                    	$graph_data[$i_oxygen_saturation]['data'][] = array(
                    			"x"	=> $gdate,
                    			"y"	=> (float)$w['oxygen_saturation']
                    	);
                    }
                    
                    
                    if($show_temperature && $w['temperature'] != "0.00" ) {
                    	$graph_data[$i_temperature]['data'][] = array(
                    			"x"	=> $gdate,
                    			"y"	=> (float)$w['temperature']
                    	);	
                    }
                    
                    
                    if($show_blood_sugar && $w['blood_sugar'] != "0.00" ) {
                    	$graph_data[$i_blood_sugar]['data'][] = array(
                    			"x"	=> $gdate,
                    			"y"	=> (float)$w['blood_sugar']
                    	);
                    }
                    
                    
                    if($show_blood_pressure && $w['blood_pressure']['systolic'] != "0.00" && $w['blood_pressure']['diastolic'] != "0.00") {
                    	$graph_data[$i_blood_pressure]['data'][] = array(
                    			"x"		=> $gdate,
                    			"high"	=> (float)$w['blood_pressure']['systolic'],
                    			"low"	=> (float)$w['blood_pressure']['diastolic']
                    	);
                    }
                    
                }
            }
            
             //ISPC-1439 @Lore 03.10.2019
            foreach($all_bowel_movement as $ipid => $w_datas){
                foreach($w_datas as $k =>$sw){

 //                 	$gdate = date("d.m.Y",strtotime($w['date']));
                    $gdate = strtotime($sw['bowel_movement_date']) * 1000;
                    $graphdate[] = $gdate;


                    if($show_bowel_movement && $sw['bowel_movement'] != "0.00" ) {
                        $graph_data[$i_bowel_movement]['data'][] = array(
                            "x"	=> $gdate,
                            "y"	=> (float)$sw['bowel_movement']
                        );
                    }

                }
            }


            $this->view->xMin = min($graphdate);
            $this->view->xMax = max($graphdate);
            
            /*
            $str = array();
            foreach($graphdate as $k=>$date){
//                $str[] = strtotime($date);   
                $str[] = $date ; 
                
            }
            asort($str);
            */
            /*
            foreach($str as $k=>$date){
//                 $graphdate_ord [] = date("d.m.Y",$date);
                $graphdate_ord [] = $date;
            }
            */

            /*
            foreach($graph_details as $graph_line=>$graph_details){
            	
                foreach($graphdate_ord as $k=>$gdate){
                	
                	if (is_array($graph_details[$gdate])) {
                		
                		$graph_data[$graph_line]['data'][] = array_merge(array("x"=>$gdate) , $graph_details[$gdate]);
                		
                	} else if( strlen($graph_details[$gdate]) > 0 ) {
                		
                        $graph_data[$graph_line]['data'][] = array($gdate, $graph_details[$gdate]); 
                    } else{
                        //$graph_data[$graph_line]['data'][] = null; 
                    }
                }   
            }
            */
           
            //calculate BMI
            /*$patientmaster = new PatientMaster();
            $patientarr = $patientmaster->getMasterData($decid, 0);*/
            $patientarr = array();
            $bmi = 0;
          //	if(isset($patientarr['height']) && $patientarr['height']>0 && $current_weight>0){
            if($current_height>0 && $current_weight>0){
          		//BMI = weight / height * height
          		//$bmi_height = $patientarr['height']/100;
            	$bmi_height = $current_height/100;
          		$bmi = $current_weight / ($bmi_height * $bmi_height);
          		$bmi = round($bmi , 2);
          	} else {
          		//else added for backwards compatibility
          		$patientmaster = new PatientMaster();
          		$patientarr = $patientmaster->getMasterData($decid, 0);
          		if(isset($patientarr['height']) && $patientarr['height']>0 && $current_weight>0) {
          			$bmi_height = $patientarr['height']/100;
          			$bmi = $current_weight / ($bmi_height * $bmi_height);
          			$bmi = round($bmi , 2);
          		}
          	}
          	$this->view->bmi = $bmi;

          	
          	if ( $show_bas ) {
	            //calculate BMI
	            $body_area_surface = 0;
	          	//if(isset($patientarr['height']) && $patientarr['height']>0 && $current_weight>0){
	            if($current_height>0 && $current_weight>0){	
	          		//Mosteller =  root of ((height * weight) / 3600)
	          		//$body_area_surface = sqrt(($patientarr['height'] * $current_weight) / 3600);
	            	$body_area_surface = sqrt(($current_height * $current_weight) / 3600);
	          		$body_area_surface = round($body_area_surface , 2);
	          	} else {
	          		//else added for backwards compatibility
	          		if(isset($patientarr['height']) && $patientarr['height']>0 && $current_weight>0){
	          			$body_area_surface = sqrt(($patientarr['height'] * $current_weight) / 3600);
	          			$body_area_surface = round($body_area_surface , 2);
	          		}
	          	}
	          	$this->view->body_area_surface = $body_area_surface;
	          	$this->view->show_bas_detail = 1;
	          	
          	} else {
          		$this->view->show_bas_detail = 0;
          	}
          	
          	
            /* 
            $this->view->weight_date_start = $graphdate_ord[0];
            
            if(isset($period['end']))
            {
                $this->view->weight_date_end = date("d.m.Y", strtotime($period['end']));
            } 
            else
            {
                $this->view->weight_date_end = end($graphdate_ord);
            } */
            	
          	   
          	    	
//             $this->view->graph_dates = json_encode($graphdate_ord);
//             $this->view->graph_series = json_encode($graph_data, JSON_PRETTY_PRINT);

          	$we_have_data = false;
          	foreach ($graph_data as $one_serie) {
          		if ( ! empty($one_serie['data'])) {
          			$we_have_data = true;
          			break;
          		}
          	}

          	if (empty($graph_data) || ! $we_have_data) {
          		
          		$this->view->nodata = "1";
          		
          	} else {
          	    	
            	$this->view->graph_series = json_encode($graph_data);
          	}
        }

        
        public function loadvitalsignsformAction()
        {
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $logininfo->clientid;

            $modules = new Modules();
            if($modules->checkModulePrivileges("139", $clientid))
            {
                $show_head_circumference = "1";
            }
            else
            {
                $show_head_circumference  = "0";
            }
            $this->view->show_head_circumference = $show_head_circumference;
            
            //ISPC-2515 Carmen 16.04.2020
			#ISPC-2512PatientCharts 

            if($_REQUEST['recid'])
            {
            	$vs_data = FormBlockVitalSignsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
            	$vs_data['id'] = $_REQUEST['recid'];
            	$vs_data['signs_data'] = date('d.m.Y', strtotime($vs_data['signs_date']));
            	$vs_data['signs_hour'] = date('H', strtotime($vs_data['signs_date']));
            	$vs_data['signs_minute'] = date('i', strtotime($vs_data['signs_date']));
            }
            else 
            {
            	$vs_data['signs_data'] = date('d.m.Y', time());
            	$vs_data['signs_hour'] = date('H', time());
            	$vs_data['signs_minute'] = date('i', time());
            }
            $this->view->vital_signs_arr = $vs_data;
            //--
        }
	
        public function applymedicationchangesAction(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $userid = $logininfo->userid;
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            $modules = new Modules();
            if ($modules->checkModulePrivileges("111", $clientid)
                || $modules->checkModulePrivileges("155", $clientid))//Medication acknowledge
            {
                $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);

                if(in_array($userid,$approval_users)){
                    
                    if(isset($_POST['action']) && isset($_POST['recordid']) && $_POST['recordid'] != "0" && isset($_POST['alt_id']) && $_POST['alt_id'] != "0"){
                        $med_form = new Application_Form_PatientDrugPlan();
                        
                        $_POST['skip_trigger'] = "1";
                        $med_form->apply_medication_change($ipid,$userid,$_POST);
                    }
                }
            }
        }

        public function applypumpmedicationchangesAction(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $userid = $logininfo->userid;
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            $modules = new Modules();
            if ($modules->checkModulePrivileges("111", $clientid) 
                || $modules->checkModulePrivileges("155", $clientid))//Medication acknowledge
            {
                $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);

                if(in_array($userid,$approval_users)){
                    
                    if(isset($_POST['action']) && isset($_POST['recordid']) && $_POST['recordid'] != "0" && isset($_POST['alt_id']) && $_POST['alt_id'] != "0"){
                        $med_form = new Application_Form_PatientDrugPlan();
                        
                        $_POST['skip_trigger'] = "1";
                        $med_form->apply_pump_medication_change($ipid,$userid,$_POST);
                    }
                }
            }
        }
        
        

        public function addleadinguserAction()
        {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $userid = $logininfo->userid;
            
            if(!empty($_REQUEST['uid']) && !empty($_REQUEST['e']))
            {

                $uid = $_REQUEST['uid'];
                $epid = $_REQUEST['e'];
                $ipid = Pms_CommonData::getIpidFromEpid($epid);
                

                $qpal = new PatientQpaLeading();
                $qpal->ipid = $ipid;
                $qpal->userid = $uid;
                $qpal->clientid = $clientid;
                $qpal->start_date = date("Y-m-d H:i:s",time());
                $qpal->save();
            }
        
            exit;
        }
        
        public function deleteleadinguserAction()
        {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $userid = $logininfo->userid;
            
            if(!empty($_REQUEST['uid']) && !empty($_REQUEST['e']))
            {
            
                $uid = $_REQUEST['uid'];
                $epid = $_REQUEST['e'];
                $ipid = Pms_CommonData::getIpidFromEpid($epid);

                $q = Doctrine_Query::create()
                ->update('PatientQpaLeading a')
                ->set('end_date','?',date("Y-m-d H:i:s",time()))
                ->set('change_user','?', $userid)
                ->set('change_date','?',date("Y-m-d H:i:s",time()))
                ->where("a.userid = ? AND a.ipid = ? ",array($uid ,$ipid));
                $q->execute();
            }
            exit;
        }
        
        

        public function saveuserpageresultsAction()
        {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
        
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $userid = $logininfo->userid;
        
            if($this->getRequest()->isPost())
            {
                if(!empty($_POST))
                {
                    $user_page_results = new Application_Form_UserPageResults();
                    $result = $user_page_results ->set_page_results($userid, $clientid, $_POST);
                }
            }
            return $result;
        }
        

        
        public function sharingshortcutlistAction(){
            $this->_helper->layout->setLayout('layout_ajax');
            $cs = new Courseshortcuts();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            if(!empty($_REQUEST['clid'])){
                $target_client =  $_REQUEST['clid'];
                $source_client = $clientid;
			    $source_client_sh = $cs->getFilterCourseData("canview",false,$source_client);
			    $target_client_sh = $cs->getFilterCourseData("canview",false,$target_client);
			    
                
			    foreach($target_client_sh as $tk=>$tsh){
                    $target_shortcuts[] = $tsh['shortcut'];
			    }
			    
			    foreach($source_client_sh as $k=>$sh){
		           $final_sh_list[$sh['shortcut']] = $sh; 
			        if(!in_array($sh['shortcut'],$target_shortcuts)){
			           $final_sh_list[$sh['shortcut']]['status'] = "disabled"; 
			        } else {
			           $final_sh_list[$sh['shortcut']]['status'] = "available"; 
			        }
			    }
			    
                ksort($final_sh_list);
                $this->view->sharing_shortcuts = $final_sh_list;
            }
        }
        
        public function editsharingshortcutlistAction(){
            $this->_helper->layout->setLayout('layout_ajax');
            $cs = new Courseshortcuts();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            if(!empty($_REQUEST['sid'])){
                
                $marked_patients = new PatientsMarked();
                
                $marked = $marked_patients->share_get($_REQUEST['sid'], false);
                $this->view->marked = $marked;
                if(!empty($_REQUEST['clid'])){
                    $target_client = $_REQUEST['clid'];
                } else{
                    $target_client =  $marked[0]['target'];
                }
                
                $clientid =  $marked[0]['source'];
                
                $source_client_sh = $cs->getFilterCourseData("canview",false,$clientid);
			    $target_client_sh = $cs->getFilterCourseData("canview",false,$target_client);
                
			    foreach($source_client_sh as $tsk=>$tssh){
			        $source_shortcuts[] = $tssh['shortcut'];
			        $all_shortcuts_s[] = $tssh;
			        $all[] = $tssh;
			    }
			    
			    foreach($target_client_sh as $tk=>$tsh){
			        $target_shortcuts[] = $tsh['shortcut'];
			        $all_shortcuts_t[] = $tsh;
			        $all[] = $tsh;
			    }
			     
			    foreach($all as $k=>$short){
			        $final_sh_list[$short['shortcut']] = $short;
			        $final_sh_list[$short['shortcut']]['shortcutid'] = $short['shortcut_id'];
			        if(in_array($short['shortcut'],$source_shortcuts) && in_array($short['shortcut'],$target_shortcuts)){
			            $final_sh_list[$short['shortcut']]['status'] = "available";
			        } else {
			            $final_sh_list[$short['shortcut']]['status'] = "disabled";
			        }
			    }
			    
			    ksort($final_sh_list);
                $this->view->sharing_shortcuts = $final_sh_list;
            }
        }
        
        
        public function editsharedshortcutlistAction(){
            $this->_helper->layout->setLayout('layout_ajax');
            $cs = new Courseshortcuts();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;

            
            if(!empty($_REQUEST['lid'])){
                
                $linked_patients = new PatientsLinked();
                $link_data = $linked_patients->get_link_data($_REQUEST['lid']);
                
                $link_ipids[] = '99999999';
                foreach($link_data as $link)
                {
                    $target_ipid = $link['target'];
                    $source_ipid = $link['source'];
                    $link_ipids[] = $link['source'];
                    $link_ipids[] = $link['target'];
                }

                
                $drop = Doctrine_Query::create()
                ->select('ipid,clientid')
                ->from('EpidIpidMapping')
                ->whereIn("ipid",$link_ipids);
                $droparray = $drop->fetchArray();
                
                foreach($droparray as $k=>$data){
                    if($data['ipid'] == $source_ipid){
                        $source_client = $data['clientid'];
                    } else if($data['ipid'] == $target_ipid) {
                        $target_client = $data['clientid'];
                    }
                }

                $link_shortcuts = $linked_patients->get_link_shortcuts($_REQUEST['lid']);
                if($clientid == $source_client){
                    $link_shortcuts_array = $link_shortcuts[$source_ipid];
                } elseif($clientid == $target_client){ 
                    $link_shortcuts_array = $link_shortcuts[$target_ipid];
                }
                $this->view->link_shortcuts = $link_shortcuts_array;
 
                $source_client_sh = $cs->getFilterCourseData("canview",false,$source_client);
			    $target_client_sh = $cs->getFilterCourseData("canview",false,$target_client);
                
			    foreach($source_client_sh as $tsk=>$tssh){
                    $source_shortcuts[] = $tssh['shortcut'];
                    $all_shortcuts_s[] = $tssh; 
                    $all[] = $tssh;
			    }
			    
			    foreach($target_client_sh as $tk=>$tsh){
                    $target_shortcuts[] = $tsh['shortcut'];
                    $all_shortcuts_t[] = $tsh;
                    $all[] = $tsh;
			    }
			    
                foreach($all as $k=>$short){
                    if($short['clientid'] == $clientid){
                        $final_sh_list[$short['shortcut']] = $short;
                        $final_sh_list[$short['shortcut']]['shortcutid'] = $short['shortcut_id'];
                        if(in_array($short['shortcut'],$source_shortcuts) && in_array($short['shortcut'],$target_shortcuts)){
    			            $final_sh_list[$short['shortcut']]['status'] = "available"; 
                        } else {
    			            $final_sh_list[$short['shortcut']]['status'] = "disabled"; 
                        }
                    }
                }
                ksort($final_sh_list);
                $this->view->shared_shortcuts = $final_sh_list;
            }
        }
        
        public function xbdtactionsAction()
        {
            
            $this->_helper->layout->setLayout('layout_ajax');
        
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            if(strlen($_REQUEST['q']) > 0)
            {
                if($_REQUEST['mode'] == 'action_id')
                {
                	$search_string = "%".trim(mb_strtolower(addslashes(urldecode($_REQUEST['q'])), 'UTF-8'))."%";
                    $srchoption = "trim(lower(action_id)) like ?";
                    $order = 'action_id';
                }
                else
                {
                    $search_str = addslashes(urldecode($_REQUEST['q'])); 
                    $search_string = "%".trim(mb_strtolower(addslashes(urldecode($_REQUEST['q'])), 'UTF-8'))."%";
                    $srchoption = "trim(lower(name)) like ?";
                    $order = 'action_id';
                }
        
                $drugs = Doctrine_Query::create()
                ->select('*')
                ->from('XbdtActions')
                ->where(" " . $srchoption . "",$search_string)
                ->andWhere("isdelete=0")
                ->andWhere('clientid ="'.$clientid.'"')
                ->limit('150')
                ->orderBy('' . $order . ' ASC');
        
                $drop_array = $drugs->fetchArray();
        
                foreach($drop_array as $key => $val)
                {
                    $drop_array[$key]['id'] = $val['id'];
                    $drop_array[$key]['action_id'] = html_entity_decode($val['action_id'], ENT_QUOTES, "UTF-8");
                    $drop_array[$key]['action_name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
                    //this is the increment to know which line to fill in admission diag form
                    $drop_array[$key]['row'] = $_REQUEST['row'];
                }
                $this->view->droparray = $drop_array;
            }
            else
            {
                $this->view->droparray = array();
            }
        }
        
        
        public function settlementservicesAction(){
        	
        	if (!$this->getRequest()->isXmlHttpRequest()) {
        		die('!isXmlHttpRequest');
        	}
        	
        	$this->_helper->layout->setLayout('layout_ajax');
        
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            if(strlen($_REQUEST['q']) > 0){
	            if($_REQUEST['mode'] == 'action_id')
	            {
	            	$srchoption = "trim(lower(action_id)) like ?";
	            	$order = 'action_id';
	            }else if($_REQUEST['mode'] == 'description'){
	            	$srchoption = "trim(lower(description)) like ?";
	            	$order = 'description';
	            }else{
	            	$this->view->droparray = array();
	            	return;
	            }
	        	
	            $query = Doctrine_Query::create();
	            
	            $query 
	            ->select('*')
	            ->from('SettlementServices')
	            ->Where("clientid = ? ", $clientid)
	            ->andWhere("isdelete = '0'")
	            ->andWhere($srchoption,"%".trim(mb_strtolower(addslashes(urldecode($_REQUEST['q'])), 'UTF-8'))."%")
	            ->limit('50')
	            ->orderBy( $order . ' ASC');
	            //echo $query->getSqlQuery();die();
	            $r_query = $query->execute();
	            $drop_array = $r_query->toArray();

	            foreach($drop_array as $key => $val)
	            {
	            	$drop_array[$key]['id'] = $val['id'];
	            	$drop_array[$key]['action_id'] = html_entity_decode($val['action_id'], ENT_QUOTES, "UTF-8");
	            	$drop_array[$key]['description'] = html_entity_decode($val['description'], ENT_QUOTES, "UTF-8");
	            	//this is the increment to know which line to fill in admission diag form
	            	$drop_array[$key]['row'] = $_REQUEST['row'];
	            }	            
	            $this->view->droparray = $drop_array;
            }
            else
            {
            	$this->view->droparray = array();
            }
            
        }
        
        

        public function assigniconvwAction()
        {
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            // client id - connected vw ?????? 
            $clientid = $logininfo->clientid; 
            
            if($_REQUEST['vw_id'] && $_REQUEST['iconid'])
            {
                $vw_id  = $_REQUEST['vw_id'];
                $icons_form = new Application_Form_Icons();
                
                $remove_icon = $icons_form->remove_vw_icon($vw_id, $_REQUEST['iconid']);
                $assign_icon = $icons_form->assign_vw_icon($vw_id, $_REQUEST['iconid'],$clientid);
            }
            
        
            echo json_encode(array("status" => "ok"));
            exit;
        }
        
        public function removeiconvwAction()
        {
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
        
            if($_REQUEST['vw_id'] && $_REQUEST['iconid'])
            {
                $vw_id  = $_REQUEST['vw_id'];
                $icons_form = new Application_Form_Icons();
                $remove_icon = $icons_form->remove_vw_icon($vw_id, $_REQUEST['iconid']);
            }
        
            echo json_encode(array("status" => "ok"));
            exit;
        }
        
        
        
        public function churchAction()
        {
        	$this->_helper->layout->setLayout('layout_ajax');
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$clientid = $logininfo->clientid;
        	$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
        	
        	$this->view->context = $this->getRequest()->getParam('context', '');
        	$this->view->returnRowId = $this->getRequest()->getParam('row', '');
        	$limit = $this->getRequest()->getParam('limit', 0);
        	$limit = (int)$limit;
        	
        	if(strlen($_REQUEST['q']) > 0)
        	{
        
        		$drop = Doctrine_Query::create()
        		->select('*')
        		->from('Churches')
        		->where("(trim(lower(contact_lastname)) like ?) or (trim(lower(contact_firstname)) like ?) or (trim(lower(name)) like ?) or (trim(lower(name)) like ?)"
        				,array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
        						"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
        						"%".trim(mb_strtolower($search_string, 'UTF-8'))."%",
        						"%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
        		->andWhere('clientid = "' . $clientid . '"')
        		->andWhere("valid_till='0000-00-00'")
        		->andWhere("indrop = 0")
        		->andWhere("isdelete = 0")
        		->orderBy('name ASC');
        		
        		if ( ! empty($limit)) {
        		    $drop->limit($limit);
        		}
        		
        		$drop_arr = $drop->fetchArray();
        
        		foreach($drop_arr as $key => $val)
        		{
        			$droparray[$key]['id'] = $val['id'];
        			$droparray[$key]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['contact_firstname'] = html_entity_decode($val['contact_firstname'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['contact_lastname'] = html_entity_decode($val['contact_lastname'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['street'] = html_entity_decode($val['street'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['phone_cell'] = html_entity_decode($val['phone_cell'], ENT_QUOTES, "utf-8");
        			$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
        		}
        
        		$this->view->droparray = $droparray;
        	}
        	else
        	{
        		$this->view->droparray = array();
        	}
        }
        

        //ispc 1739 p.3
        public function membercheckduplicateAction(){
        	if (!$this->getRequest()->isXmlHttpRequest()) {
        		die('!isXmlHttpRequest');
        	}
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$clientid = $logininfo->clientid;
        	
        	$result = array("success"=> false , "membercheckduplicate"=>true );
        	$this->_helper->layout()->disableLayout();
        	$this->_helper->viewRenderer->setNoRender(true);
        	
        	if ((int)$_POST['data'] > 0){
        		$member = Member :: verify_member_name_exists($_POST['data'], $clientid);
        	}
        	if ( !empty($member[0]) ) {
        	    if(strlen($member[0]['birthd']) > 0 && $member[0]['birthd'] !="0000-00-00"){
        	        $member[0]['birthd'] = date("d.m.Y",strtotime($member[0]['birthd']));
        	    }
        		$result = array(
        				'success' => true,
        				'data' => array(
        				'id' => $member[0]['id'],
        				'first_name' => $member[0]['first_name'],
        				'last_name' => $member[0]['last_name'],
        				'birthd' => $member[0]['birthd'],
        				'member_company' => $member[0]['member_company']
        						)
        						
        		);
        		
        	}
        	
        	echo json_encode($result);
        	exit;
        }
        
        //ispc-1794
        public function vitabookAction(){
        	
        	$this->forward("membercheckduplicate");
        	return;
        	
        	$this->_helper->layout()->disableLayout();
        	$this->_helper->viewRenderer->setNoRender(true);
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$clientid = $logininfo->clientid;
        	
        	if (!$this->getRequest()->isXmlHttpRequest()) {
        		die('!isXmlHttpRequest');
        	}
        	$result = array("success"=> false);
        	if(empty($_POST['id']) || !$logininfo->clientid ) {
        		echo json_encode($result);
        		exit;
        	}
        	
        	
        	$decid = Pms_Uuid::decrypt($_POST['id']);
        	$ipid = Pms_CommonData::getIpId($decid);
        	
        	$VitabookPatient = VitabookPatient :: get_single_vitabookId_by_ipid($ipid , $clientid);
        	
       
        	if ($VitabookPatient === false || 1){
        		
        		//insert new vitabook patient
        		$new_vitabook_patient = VitabookPatient :: vitabook_soap_SavePatient($ipid , $clientid);
        		$result =  array(
        				"success"=> true,
        				"vitabook_id" => $new_vitabook_patient,
        				"allready" => false
        		);
        		
        	}else {
        		//id is allready here
        		$result =  array(
        			"success"=> true,
        			"vitabook_id" => $VitabookPatient [ $ipid ] [ "vitabook_id" ],
        			"allready" => true		
        		);
        		
        	}
        	
        	echo json_encode($result);
        	exit;
        	
        }
        

        public function givescheduledmedAction(){
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $userid = $logininfo->userid;
        
            //hidd_medication
            //verordnetvon
            //dosage
            //edited
            //medication_change
            //$post['replace_with'][$i] == 'none'
            //days_interval
            //administration_date
            //comments
            //$post['indication'][$i];
        
            $pdp = new PatientDrugPlan();
            $pdp_extra = new PatientDrugPlanExtra();
            $pdp_dosage = new PatientDrugPlanDosage();
            if(!empty($_REQUEST['drug_id']) && !empty($_REQUEST['id'])){
                $decid = Pms_Uuid::decrypt($_REQUEST['id']);
                $ipid = Pms_CommonData::getIpId($decid);
                $drug_id = $_REQUEST['drug_id'];
        
                // get drug details
                $drug['details'] = $pdp ->get_drugplan_id_details($ipid,$drug_id);
                $drug['extra']= $pdp_extra->get_patient_all_drugplan_extra($ipid, $drug_id);
                // get patient new dosage structure
                $drug['dosage'] = $pdp_dosage->get_patient_drugplan_dosage($ipid, $drug_id);
                
                $modules = new Modules();
                if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
                {
                    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
                    if(in_array($userid,$approval_users))
                    {
                        $_POST['skip_trigger'] = "1";
                    }
                }
                 
                if(!empty($drug['details']))
                {
                    if($drug['details']['isbedarfs'] == "1")
                    {
                        $m_type = "isbedarfs";
                    }
                    elseif($drug['details']['isivmed'] == "1")
                    {
                        $m_type = "isivmed";
                    }
                    elseif($drug['details']['isschmerzpumpe'] == "1")
                    {
                        $m_type = "isschmerzpumpe";
                    }
                    elseif($drug['details']['treatment_care'] == "1")
                    {
                        $m_type = "treatment_care";
                    }
                    elseif($drug['details']['isnutrition'] == "1")
                    {
                        $m_type = "isnutrition";
                    }
                    elseif($drug['details']['scheduled'] == "1")
                    {
                        $m_type = "scheduled";
                    }
                    else
                    {
                        $m_type = "actual";
                    }
                    
                    
                    $_POST['administrate_drug'] = "1";
                    $_POST['medication_block'][$m_type]['hidd_medication'][0] = $drug['details']['medication_master_id'];
                    $_POST['medication_block'][$m_type]['drid'][0] = $drug['details']['id'];
                    $_POST['medication_block'][$m_type]['verordnetvon'][0] = $drug['details']['verordnetvon'];
                    if(!empty($drug['dosage'])){
                        $_POST['medication_block'][$m_type]['dosage'][0] = $drug['dosage'][$drug_id];
                    }else{
                        $_POST['medication_block'][$m_type]['dosage'][0] = $drug['details']['dosage'];
                    }
                    $_POST['medication_block'][$m_type]['edited'][0] = "1";
                    $_POST['medication_block'][$m_type]['medication_change'][0] = date("d.m.Y",time());
                    $_POST['medication_block'][$m_type]['replace_with'][0] = "none";
                    //ISPC 2305 - add comment
                    if($m_type == "scheduled")
                    {
                    	$_POST['medication_block'][$m_type]['comments'][0] = $drug['details']['comments']."\n".$_REQUEST['reset_comment'];
                    }
                    else 
                    {
                    	$_POST['medication_block'][$m_type]['comments'][0] = $drug['details']['comments'];
                    }
                    
                    $_POST['medication_block'][$m_type]['has_interval'][0] = $drug['details']['has_interval'];
                    
                    $_POST['medication_block'][$m_type]['days_interval'][0] = $drug['details']['days_interval'];
                    $_POST['medication_block'][$m_type]['administration_date'][0] = date("d.m.Y",time());
                
                    $_POST['medication_block'][$m_type]['drug'][0] = $drug['extra'][$drug_id]['drug'];
                    $_POST['medication_block'][$m_type]['unit'][0] = $drug['extra'][$drug_id]['unit'];
                    $_POST['medication_block'][$m_type]['type'][0] = $drug['extra'][$drug_id]['type'];
                    $_POST['medication_block'][$m_type]['indication'][0] = $drug['extra'][$drug_id]['indication'];
                    $_POST['medication_block'][$m_type]['dosage_form'][0] = $drug['extra'][$drug_id]['dosage_form'];
                    $_POST['medication_block'][$m_type]['concentration'][0] = $drug['extra'][$drug_id]['concentration'];
                    $_POST['medication_block'][$m_type]['importance'][0] = $drug['extra'][$drug_id]['importance'];
                    
                    
                    $post = $_POST;
                    
                    if($post){
                        $drug_form = new Application_Form_PatientDrugPlan();
                        $drug_form ->update_multiple_data($post['medication_block'][$m_type],$ipid);
                    }
                
                }
            
                $data = IconsPatient::get_scheduled_medication(array($ipid));
                if(count($data['scheduled_medication_data'][$ipid]) > 0 ){
                    $return ['remove_icon'] = "0";
                } else{
                    $return ['remove_icon'] = "1";
                }
                
                echo json_encode($return);
                exit;
            
            }
            
        
        }
        
        //ispc 1533 input#toggle_viewmode from roster/dayplanningnew
        public function dayplanningviewmodeAction()
        {
        	
        	$has_link_permissions = Links::checkLinkActionsPermission();
        	if(!$has_link_permissions)
        	{
        		$this->_redirect(APP_BASE . "error/previlege");
        		exit;
        	}
        	
        	if (!$this->getRequest()->isXmlHttpRequest()) {
        		die('!isXmlHttpRequest');
        	}
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$clientid = $logininfo->clientid;
        
        	$result = array("success"=> false);
        	$this->_helper->layout()->disableLayout();
        	$this->_helper->viewRenderer->setNoRender(true);
        	 
        	$date = date("Y-m-d", strtotime($_POST['date']));
        	$view_mode = ($_POST['viewmode'] == 'timed') ? 'timed' : 'order';        	 

       
        	$q = Doctrine_Query::create()
        	->update('DailyPlanningUsers')
        	->set('view_mode' ,'?', $view_mode)
        	->where("clientid = ?" , $clientid)
        	->andWhere("DATE(date) =  DATE('" . $date . "')");
        	
        	$q->execute();
        		 
        	$result = array("success"=> true);
        	        	 
        	echo json_encode($result);
        }
        
        //ispc-1752
        public function getzipcitiesAction(){
        	
        	if (!$this->getRequest()->isXmlHttpRequest()) {
        		die('!isXmlHttpRequest');
        	}
        	$this->_helper->layout->setLayout('layout_ajax');
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	
        	$clientid = $logininfo->clientid;
        	$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
        	if (strlen($search_string) < 1 ){
        		return false;
	        }
        	
	        $limit = $this->getRequest()->getParam('limit', 0);
	        $limit = ! empty($limit) ? (int)$limit : 150;
	        
        	$result = array();
      
	        if ($_REQUEST['mode'] == "city"){
	        	$result = StateCityZip :: get_zips_from_city( $search_string , $limit);
	        }
	        elseif($_REQUEST['mode'] == "zipcode" || $_REQUEST['mode'] == "zip"){
	        	$result = StateCityZip :: get_citys_from_zip( $search_string , $limit);
	        }    	
        	
        	$this->view->droparray = $result;
        	$this->view->context = addslashes(urldecode(trim($_REQUEST['context'])));
        	$this->view->returnRowId = addslashes(urldecode(trim($_REQUEST['row'])));
        	
        	if (!empty($_REQUEST['extraid'])){
        		$this->view->extraid = addslashes(urldecode(trim($_REQUEST['extraid'])));
        	}else{
        		$this->view->extraid = "0" ;	
        	}
        	
        	header("Content-type: text/html; charset=utf-8");
        	
        	
        }
        
        /*
         * Mark a BA-packet from SystemsSyncPackets as done
         */
        public function marksyncbapacketdoneAction(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $syncid = intval($_REQUEST['syncid']);
            SystemsSyncPackets::get_ba_data($syncid,1);
            echo json_encode('OK');
        }

        
        
        
        
        /*
         * DELETE PATIENT FALLS
         */
        
        /**
         *  Old version - not used 
         *  28.08.2018
         *  
         */
        public function managepatientfallsV1Action(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            /*			 * ******* Patient History ************ */
            $patientmaster = new PatientMaster();
            $patient_falls_master = $patientmaster->patient_falls($ipid);
            $patient_falls = $patient_falls_master['falls'];
            $first_admission_ever = $patient_falls_master['first_admission_ever'];;
       
            $this->view->first_admission_ever = $first_admission_ever;
            $this->view->patient_falls = $patient_falls;
            
            $return['error'] = "0";
            if($this->getRequest()->isPost())
            {
            	
                if(!empty($_POST))
                {
                    $post = $_POST;
                    $fall = $_POST['fall'];
                    
                    if($patient_falls[$_POST['fall']][$_POST['date_type']] == $_POST['date'])
                    {// check if data was not changed 
                         
                    	if($_POST['fall_type'] == "standbydelete" && $patient_falls[$fall - 1][0] == "standby"){

                    		// move from standby delete to standby
                    		// delete detandbydelete start
                    		// delete standby end
                    		
                    		if($_POST['date_type'] == "1"){
                    			
                    			$post['status'] = 'Remove standbydelete admission -  move back to standby';
                    			$post['user'] = $logininfo->userid;

                    			$drop = Doctrine_Query::create()
                    			->select('id,date,date_type,ipid')
                    			->from('PatientStandbyDetails')
                    			->where('ipid = ?', $ipid)
                    			->andWhere('date_type = ?', 2)
                    			->orderBy('date DESC')
                    			->limit(1);
                    			$droparray = $drop->fetchArray();
                    			 
                    			if($droparray){
                    				$post['standby'] =  $droparray[0];
                    				 
                    				$standby_details['date'] = $droparray[0]['date'];
                    				if($standby_details['date'] == $_POST['date']){
                    					$q = Doctrine_Query::create()
                    					->delete('PatientStandbyDetails')
                    					->where('ipid = ?', $ipid)
                    					->andWhere('date = ?', $standby_details['date'])
                    					->andWhere('date_type = ?',2);
                    					$q->execute();
                    				}
                    			}
                    			// REFRESH PATEINT STANDBY
                    			//added patient admission/readmission new procedure
                    			PatientMaster::get_patient_standby_admissions($ipid);
                    			
                    			$drop_del = Doctrine_Query::create()
                    			->select('id,date,date_type,ipid')
                    			->from('PatientStandbyDeleteDetails')
                    			->where('ipid = ?', $ipid)
                    			->andWhere('date_type = ?', 1)
                    			->orderBy('date DESC')
                    			->limit(1);
                    			$droparray_del = $drop_del->fetchArray();
                    			 
                    			if($droparray_del){
                    				$post['standbydelete'] =  $droparray_del[0];
                    				 
                    				$standby_details['date'] = $droparray_del[0]['date'];
                    				if($standby_details['date'] == $_POST['date']){
                    					$q = Doctrine_Query::create()
                    					->delete('PatientStandbyDeleteDetails')
                    					->where('ipid = ?', $ipid)
                    					->andWhere('date = ?', $standby_details['date'])
                    					->andWhere('date_type = ?',1);
                    					$q->execute();
                    				}
                    			}
                    			// REFRESH PATEINT STANDBYDELETE
                    			//added patient admission/readmission new procedure
                    			PatientMaster::get_patient_standbydelete_admissions($ipid);
                    			
                    			
                    			$drop_pm = Doctrine_Query::create()
                    			->select('id,ipid')
                    			->from('PatientMaster')
                    			->where('ipid = ?', $ipid)
                    			->limit(1);
                    			$drop_pm_array = $drop_pm->fetchArray();
                    			
                    			if($drop_pm_array){
                    				$patient_master_db = Doctrine::getTable('PatientMaster')->find($drop_pm_array[0]['id']);
                    				if($patient_master_db){
                    					$patient_master_db->traffic_status = "1";
                    					
                    					if($patient_falls[$fall - 1][0] == "standby" ){
                    						$patient_master_db->isdischarged = "0";
                    						$patient_master_db->isstandbydelete = "0";
                    						$patient_master_db->isstandby = "1";
                    					} 
                    					$patient_master_db->save();
                    				}
                    			}
                    			
                    			
                    			
                    			

                    			// write in log
                    			$test = array_merge($post);
                    			$patient_fall_log = new PatientHistoryLog();
                    			$patient_fall_log->ipid = $ipid;
                    			$patient_fall_log->type = "adm";
                    			$patient_fall_log->details = serialize($test);
                    			$patient_fall_log->save();
                    			
                    			$return['error'] = "0";
                    			$return['text'] = "standbydelete admission deleted move back to stanby";
                    			
                    			// write in Patient Course
                    			$comment ="";
                    			$date_dmY = date('d.m.Y',strtotime($_POST['date']));
                    			$del_comment = $this->view->translate('manualy delelete standbydelete admission, moved back to standby');
                    			$comment = str_replace('%date',$date_dmY,$del_comment);
                    			
                    			$userid = $logininfo->userid;
                    			$cust = new PatientCourse();
                    			$cust->ipid = $ipid;
                    			$cust->course_date = date("Y-m-d H:i:s", time());
                    			$cust->course_type = Pms_CommonData::aesEncrypt("K");
                    			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
                    			$cust->user_id = $userid;
                    			$cust->save();
                    			
                    			
                    			
                    		
                    		}
                    	} else{
                    		
                    	
                    	
                        if($_POST['date_type'] == "1" && $_POST['date'] != $patient_falls[1][1]) // admission dare
                        {
                            $post['status'] = 'Remove admission -  move back to discharge';
                            $post['user'] = $logininfo->userid;
                            
                            // get previous admission date
                            if($patient_falls[$fall - 1][0] == "discharge" ){
	                            $previous_admission = $patient_falls[$fall - 2][1]; // previous fall admission date before discharge
                            } else {
	                            $previous_admission = $patient_falls[$fall - 1][1]; // previous fall admission date
                            }
                  
                            /* edit patient master 
                             * change admission date to previous admission date 
                             * set isdischarged = "1" 
                             * set traffic_status = 0 
                             */
                             
                            $drop_pm = Doctrine_Query::create()
                            ->select('id,ipid')
                            ->from('PatientMaster')
                            ->where('ipid = ?', $ipid)
                            ->limit(1);
                            $drop_pm_array = $drop_pm->fetchArray();
                            
                            if($drop_pm_array){
                                $patient_master_db = Doctrine::getTable('PatientMaster')->find($drop_pm_array[0]['id']);
                                if($patient_master_db){
                                    $patient_master_db->admission_date = $previous_admission;
                                    $patient_master_db->traffic_status = "1";
                                    
                                    if($patient_falls[$fall - 1][0] == "standby" ){
	                                    $patient_master_db->isdischarged = "0";
	                                    $patient_master_db->isstandby = "1";
                                    } else{
	                                    $patient_master_db->isdischarged = "1";
                                    }
                                    
                                    if( $patient_falls[$fall][0] == "standby"){
	                                    $patient_master_db->isstandby = "0";
                                    }
                                    $patient_master_db->save();
                                }
                            }
                            
                            
                            // remove from patient readmission the last admiision date                            
                            $drop = Doctrine_Query::create()
                            ->select('id,date,date_type,ipid')
                            ->from('PatientReadmission')
                            ->where('ipid = ?', $ipid)
                            ->andWhere('date_type = ?', 1)
                            ->orderBy('date DESC')
                            ->limit(1);
                            $droparray = $drop->fetchArray();

                            if($droparray){
                                $post['readmission'] =  $droparray[0];
                                
                                $readmission_details['date'] = $droparray[0]['date'];
                                if($readmission_details['date'] == $_POST['date']){
                                    $q = Doctrine_Query::create()
                                    ->delete('PatientReadmission')
                                    ->where('ipid = ?', $ipid)
                                    ->andWhere('date = ?', $readmission_details['date'])
                                    ->andWhere('date_type = ?',1);
                                    $q->execute();
                                }
                            }
                            
                            
                            // patient discharge -  edit - last discharge set isdelete = 0
                            $db_discharge = Doctrine_Query::create()
                            ->select('id,discharge_date,ipid')
                            ->from('PatientDischarge')
                            ->where('ipid = ?', $ipid)
                            ->andWhere('isdelete = ?', 1)
                            ->orderBy('discharge_date DESC')
                            ->limit(1);
                            $db_discharge_array = $db_discharge->fetchArray();
                            
                            if($db_discharge_array){
                                $pd_db = Doctrine::getTable('PatientDischarge')->find($db_discharge_array[0]['id']);
                                $pd_db->isdelete = 0;
                                $pd_db->save();
                            }
                            
                            // REFRESH PATEINT ACTIVE
                            //added patient admission/readmission new procedure
                            PatientMaster::get_patient_admissions($ipid);
                            
                            

                            // remove from patient STANDBY the last admiision date
                            if( $patient_falls[$fall][0] == "standby"){
                            	
	                            $drop = Doctrine_Query::create()
	                            ->select('id,date,date_type,ipid')
	                            ->from('PatientStandbyDetails')
	                            ->where('ipid = ?', $ipid)
	                            ->andWhere('date_type = ?', 1)
	                            ->orderBy('date DESC')
	                            ->limit(1);
	                            $droparray = $drop->fetchArray();
	                            
	                            if($droparray){
	                            	$post['standby'] =  $droparray[0];
	                            
	                            	$standby_details['date'] = $droparray[0]['date'];
	                            	if($standby_details['date'] == $_POST['date']){
	                            		$q = Doctrine_Query::create()
	                            		->delete('PatientStandbyDetails')
	                            		->where('ipid = ?', $ipid)
	                            		->andWhere('date = ?', $standby_details['date'])
	                            		->andWhere('date_type = ?',1);
	                            		$q->execute();
	                            	}
	                            }
	                            // REFRESH PATEINT STANDBY
	                            //added patient admission/readmission new procedure
	                            PatientMaster::get_patient_standby_admissions($ipid);
	                            
	                            
                            } else if( $patient_falls[$fall-1][0] == "standby"){
                            	// delete edn 
                            	
                            	$drop = Doctrine_Query::create()
                            	->select('id,date,date_type,ipid')
                            	->from('PatientStandbyDetails')
                            	->where('ipid = ?', $ipid)
                            	->andWhere('date_type = ?', 2)
                            	->orderBy('date DESC')
                            	->limit(1);
                            	$droparray = $drop->fetchArray();
                            	 
                            	if($droparray){
                            		$post['standby'] =  $droparray[0];
                            		 
                            		$standby_details['date'] = $droparray[0]['date'];
                            		if($standby_details['date'] == $_POST['date']){
                            			$q = Doctrine_Query::create()
                            			->delete('PatientStandbyDetails')
                            			->where('ipid = ?', $ipid)
                            			->andWhere('date = ?', $standby_details['date'])
                            			->andWhere('date_type = ?',2);
                            			$q->execute();
                            		}
                            	}
                            	// REFRESH PATEINT STANDBY
                            	//added patient admission/readmission new procedure
                            	PatientMaster::get_patient_standby_admissions($ipid);
                            	
                            	
                            }
                            
                            
                            // write in log
                            $test = array_merge($post);
                            $patient_fall_log = new PatientHistoryLog();
                            $patient_fall_log->ipid = $ipid;
                            $patient_fall_log->type = "adm";
                            $patient_fall_log->details = serialize($test);
                            $patient_fall_log->save();
                            
                            $return['error'] = "0";
                            $return['text'] = "admission deleted ";
                            
                            // write in Patient Course
                            $comment ="";
                            $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                            $del_comment = $this->view->translate('admission_date_manually_deleted');
                            $comment = str_replace('%date',$date_dmY,$del_comment);
                            
                            $userid = $logininfo->userid;
                            $cust = new PatientCourse();
                            $cust->ipid = $ipid;
                            $cust->course_date = date("Y-m-d H:i:s", time());
                            $cust->course_type = Pms_CommonData::aesEncrypt("K");
                            $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                            $cust->user_id = $userid;
                            $cust->save();
                            
                            
                        } 
                        elseif($_POST['date_type'] == "1" && $_POST['date'] == $patient_falls[1][1]) // delete first admission, move to STANDBY 
                        {
                            $post['status'] = 'Remove admission -  move back to STANDBY';
                            $post['user'] = $logininfo->userid;
                            
                            // get previous admission date
                            
                            if($patient_falls[$fall - 1][0] == "discharge" ){
                            	$previous_admission = $patient_falls[$fall - 2][1]; // previous fall admission date before discharge
                            } else {
                            	$previous_admission = $patient_falls[$fall - 1][1]; // previous fall admission date
                            }
                  
                            /* edit patient master 
                             * change admission date to previous admission date 
                             * set isdischarged = "1" 
                             * set traffic_status = 0 
                             */
                             
                            $drop_pm = Doctrine_Query::create()
                            ->select('id,ipid')
                            ->from('PatientMaster')
                            ->where('ipid = ?', $ipid)
                            ->limit(1);
                            $drop_pm_array = $drop_pm->fetchArray();
                            
                            if($drop_pm_array){
                                $patient_master_db = Doctrine::getTable('PatientMaster')->find($drop_pm_array[0]['id']);
                                if($patient_master_db){
                                    $patient_master_db->admission_date = $patient_falls[1][1];
                                    $patient_master_db->isstandby = "1";
                                    $patient_master_db->save();
                                }
                                
                                // insert in standby falls
                                $cust = new PatientStandby();
                                $cust->ipid = $ipid;
                                $cust->start = date("Y-m-d ", strtotime($patient_falls[1][1]));
                                $cust->save();
                                
                                // insert in standby details
                                $cust = new PatientStandbyDetails();
                                $cust->ipid = $ipid;
                                $cust->date = $patient_falls[1][1];
                                $cust->date_type = "1";
                                $cust->comment = "Move to standby - delete admission in pateint details";
                                
                                $cust->save();
                                
                                
                            }
                            
                            
                            // write in log
                            $test = array_merge($post);
                            $patient_fall_log = new PatientHistoryLog();
                            $patient_fall_log->ipid = $ipid;
                            $patient_fall_log->type = "adm";
                            $patient_fall_log->details = serialize($test);
                            $patient_fall_log->save();
                            
                            $return['error'] = "0";
                            $return['text'] = "admission deleted moved to standby ";
                            
                            // write in Patient Course
                            $comment ="";
                            $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                            $del_comment = $this->view->translate('admission_date_manually_deleted_moved_to_standby');
                            $comment = str_replace('%date',$date_dmY,$del_comment);
                            
                            $userid = $logininfo->userid;
                            $cust = new PatientCourse();
                            $cust->ipid = $ipid;
                            $cust->course_date = date("Y-m-d H:i:s", time());
                            $cust->course_type = Pms_CommonData::aesEncrypt("K");
                            $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                            $cust->user_id = $userid;
                            $cust->save();
                            
                        } 
                        else if($_POST['date_type'] == "2") // discharge date
                        {
                        	
                        	// ???
                        	
                        	
                            $post['status'] = 'Remove discharge -  move back to active';
                            $post['user'] = $logininfo->userid;
                            
                            // check patient death  --  death by button
                            $drop_pdeath = Doctrine_Query::create()
                            ->select('id,ipid')
                            ->from('PatientDeath')
                            ->where('ipid = ?', $ipid)
                            ->limit(1);
                            $drop_pdeath_arr = $drop_pdeath->fetchArray();
                            if($drop_pdeath_arr){
                                $pdeath_db = Doctrine::getTable('PatientDeath')->find($drop_pdeath_arr[0]['id']);
                                $pdeath_db->isdelete = 1;
                                $pdeath_db->save();
                                
                                
                                // update in patient course
                                $db_pc = Doctrine_Query::create()
                                ->select("id,ipid,recordid,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
                                ->from('PatientCourse')
                                ->where('ipid = ?', $ipid)
                                ->andWhere('recordid = ?', $drop_pdeath_arr[0]['id'])
								->andWhere('source_ipid = ""')
                                ->orderBy('id ASC');
                                $db_pc_array = $db_pc->fetchArray();
                                
                                 
                                $wrong_comment = $this->view->translate('discharge_date_manually_deleted_by_user');
                                $wrong_comment_str =str_replace('%user',$user_name,$wrong_comment); 
                                
                                $discharge_death_tabnames = array('patient_death');
                                foreach($db_pc_array as $k=>$pc_data){
                                	if(in_array($pc_data['tabname'],$discharge_death_tabnames)){
                                		$stmb = Doctrine::getTable('PatientCourse')->find($pc_data['id']);
                                		$stmb->tabname = Pms_CommonData::aesEncrypt("deleted_discharge_death");
                                		$stmb->wrong = "1";
                                		$stmb->wrongcomment = $wrong_comment_str;
                                		$stmb->save();
                                	}
                                }
                                
                                
                                
                                
                                
                                
                                
                            }
                            
                            // update patient master -  set isdischarged  = 1 . traffic status  = 1 

                            $drop_pm = Doctrine_Query::create()
                            ->select('id,ipid')
                            ->from('PatientMaster')
                            ->where('ipid = ?', $ipid)
                            ->limit(1);
                            $drop_pm_array = $drop_pm->fetchArray();

                            if($drop_pm_array){
                                $patient_master_db = Doctrine::getTable('PatientMaster')->find($drop_pm_array[0]['id']);
                                if($patient_master_db){
                                    $patient_master_db->isdischarged = "0";
                                    $patient_master_db->traffic_status = "1";
                                    $patient_master_db->save();
                                }
                            }
                            
                            
                            
                            // remove last discharge from patient readmission 
                            $drop = Doctrine_Query::create()
                            ->select('id,date,date_type,ipid')
                            ->from('PatientReadmission')
                            ->where('ipid = ?',$ipid)
                            ->andWhere('date_type = ?', 2)
                            ->orderBy('date DESC')
                            ->limit(1);
                            $droparray = $drop->fetchArray();
                            
                            if($droparray){
                                $post['readmission'] =  $droparray[0];
                            
                                $readmission_details['date'] = $droparray[0]['date'];
                                if($readmission_details['date'] == $_POST['date']){
                                    $q = Doctrine_Query::create()
                                    ->delete('PatientReadmission')
                                    ->where('ipid = ?', $ipid)
                                    ->andWhere('date = ?', $readmission_details['date'])
                                    ->andWhere('date_type = ?', 2);
                                    $q->execute();
                                }
                            }
                            
                            // patient discharge- mark as deleted an manualy deleted 
                            // patient discharge -  edit - last discharge set isdelete = 0
                            $db_discharge = Doctrine_Query::create()
                            ->select('id,discharge_date,ipid')
                            ->from('PatientDischarge')
                            ->where('ipid = ?', $ipid)
                            ->andWhere('isdelete = ?', 0)
                            ->orderBy('discharge_date DESC')
                            ->limit(1);
                            $db_discharge_array = $db_discharge->fetchArray();
                            
                            if($db_discharge_array){
                                $post['discharge_data'] = $db_discharge_array[0];
                                
                                if($db_discharge_array[0]['discharge_date'] == $_POST['date'])
                                {
                                    $q = Doctrine_Query::create()
                                    ->delete('PatientDischarge')
                                    ->where('ipid = ?', $ipid)
                                    ->andWhere('discharge_date = ?', $_POST['date'])
                                    ->andWhere('isdelete = ?', 0);
                                    $q->execute();
                                    
                                    
                                    

                                    // update in patient course
                                    $db_pc = Doctrine_Query::create()
                                    ->select("id,ipid,recordid,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
                                    ->from('PatientCourse')
                                    ->where('ipid = ?', $ipid)
                                    ->andWhere('recordid = ?', $db_discharge_array[0]['id'])
									->andWhere('source_ipid = ""')
                                    ->orderBy('id ASC');
                                    $db_pc_array = $db_pc->fetchArray();
                                    
                                   
                               		$wrong_comment = $this->view->translate('discharge_date_manually_deleted_by_user');
                                 	$wrong_comment_str =str_replace('%user',$user_name,$wrong_comment);
                                 	
                                    $discharge_tabnames = array('discharge','discharge_date');
                                    foreach($db_pc_array as $k=>$pc_data){
                                    	if(in_array($pc_data['tabname'],$discharge_tabnames)){
                                    		$stmb = Doctrine::getTable('PatientCourse')->find($pc_data['id']);
                                    		$stmb->tabname = Pms_CommonData::aesEncrypt("deleted_discharge");
                                    		$stmb->wrong = "1";
                                    		$stmb->wrongcomment = $wrong_comment_str;
                                    		$stmb->save(); 
                                    	}
                                    }
                                }
                            }                            
                            
                            
                            // REFRESH PATEINT ACTIVE
                            //added patient admission/readmission new procedure
                            PatientMaster::get_patient_admissions($ipid);
                            
                            // write in log
                            $test = array_merge($post);
                            $patient_fall_log = new PatientHistoryLog();
                            $patient_fall_log->ipid = $ipid;
                            $patient_fall_log->type = "dis";
                            $patient_fall_log->details = serialize($test);
                            $patient_fall_log->save();
                            
                            $return['error'] = "0";
                            $return['text'] = "discharge deleted ";
                            
                            
                            // write in Patient Course
                            $comment ="";
                            $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                            $del_comment = $this->view->translate('discharge_date_manually_deleted');
                            $comment = str_replace('%date',$date_dmY,$del_comment);
                            
                            $userid = $logininfo->userid;
                            $cust = new PatientCourse();
                            $cust->ipid = $ipid;
                            $cust->course_date = date("Y-m-d H:i:s", time());
                            $cust->course_type = Pms_CommonData::aesEncrypt("K");
                            $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                            $cust->user_id = $userid;
                            $cust->save();
                        }
                        
                    }
                    } 
                    else
                    {
                        $return['error'] = "1";
                        $return['text'] = $this->view->translate('error_manually_manage_dates');
                    }
                }
            }
            
            echo json_encode($return);
            exit;
        }
        
        
        public function managepatientfallsAction(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            /*			 * ******* Patient History ************ */
            $patientmaster = new PatientMaster();
            $patient_falls_master = $patientmaster->patient_falls($ipid);
            $patient_falls = $patient_falls_master['falls'];
            $first_admission_ever = $patient_falls_master['first_admission_ever'];;
       
            $this->view->first_admission_ever = $first_admission_ever;
            $this->view->patient_falls = $patient_falls;
            
            $return['error'] = "0";
            if($this->getRequest()->isPost() &&  ! empty($_POST) &&  ! empty($_POST['date']) )
        {
        	
                $post = $_POST;
                $post['user'] = $logininfo->userid;
                $fall = $_POST['fall'];
                
                
                if($patient_falls[$_POST['fall']][$_POST['date_type']] == $_POST['date'])
                {// check if data was not changed 
                     
                    switch($_POST['fall_type'])
                    {
                            
                        case "active":{
                            
                            switch($_POST['date_type'])
                            {
                                case "1":
                                    // delete current admission
                                    
                                    
                                    
                                    if( ! isset($patient_falls[$fall - 1][0]) ) {
                                        // delete first admission  
                                        
                                        $post['status'] = 'Remove ACTIVE admission -  move back to STANDBY';

                                        $patient_master_db = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                        if($patient_master_db){
                                            $patient_master_db->isstandby = "1";
                                            $patient_master_db->save();
                                        }
                                        $current_patient_master = $patient_master_db->toArray();
                                  
                                        // insert in standby falls
                                        $cust = new PatientStandby();
                                        $cust->ipid = $ipid;
                                        $cust->start = date("Y-m-d ", strtotime($current_patient_master['admission_date']));
                                        $cust->save();
                                        
                                        // insert in standby details
                                        $cust = new PatientStandbyDetails();
                                        $cust->ipid = $ipid;
                                        $cust->date = $current_patient_master['admission_date'];
                                        $cust->date_type = "1";
                                        $cust->comment = "Move to standby - delete FIRST admission in pateint details";
                                        $cust->save();
                                        
                                        // write in log
                                        $test = array_merge($post);
                                        $patient_fall_log = new PatientHistoryLog();
                                        $patient_fall_log->ipid = $ipid;
                                        $patient_fall_log->type = "adm";
                                        $patient_fall_log->details = serialize($test);
                                        $patient_fall_log->save();
                                        
                                        $return['error'] = "0";
                                        $return['text'] = "admission deleted moved to standby ";
                                        
                                        // write in Patient Course
                                        $comment ="";
                                        $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                                        $del_comment = $this->view->translate('admission_date_manually_deleted_moved_to_standby');
                                        $comment = str_replace('%date',$date_dmY,$del_comment);
                                        
                                        $userid = $logininfo->userid;
                                        $cust = new PatientCourse();
                                        $cust->ipid = $ipid;
                                        $cust->course_date = date("Y-m-d H:i:s", time());
                                        $cust->course_type = Pms_CommonData::aesEncrypt("K");
                                        $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                                        $cust->user_id = $userid;
                                        $cust->save();
                                        
                                        
                                    } else {
                                        
                                        if($patient_falls[$fall - 1][0] == "discharge" )
                                        {
                                            $post['status'] = 'Remove ACTIVE admission -  move back to discharge';
                                            
                                            // get previous admission date
                                            $previous_admission = $patient_falls[$fall - 2][1]; // previous fall admission date before discharge

                                            //Update PatientMaster
                                            $patient_master_db = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                            if($patient_master_db){
                                                $patient_master_db->admission_date = $previous_admission;
                                                $patient_master_db->isdischarged = "1";
                                                $patient_master_db->isstandby = "0";
                                                $patient_master_db->isstandbydelete = "0";
                                                $patient_master_db->traffic_status = "1";
                                                $patient_master_db->save();
                                            }
                                            $current_patient_master = $patient_master_db->toArray();
                                            
                                            
                                            // remove from patient readmission the last admiision date
                                            $remove_readmission = Doctrine_Query::create()
                                            ->delete('PatientReadmission')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('date = ?', date("Y-m-d H:i:s",strtotime($_POST['date'])))
                                            ->andWhere('date_type = ?',1)
                                            ->execute();

                                            
                                            // patient discharge -  edit - last discharge set isdelete = 0
                                            $db_discharge = Doctrine_Query::create()
                                            ->select('id,discharge_date,ipid')
                                            ->from('PatientDischarge')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('isdelete = ?', 1)
                                            ->orderBy('discharge_date DESC')
                                            ->limit(1);
                                            $db_discharge_array = $db_discharge->fetchArray();
                                            
                                            if($db_discharge_array){
                                                $pd_db = Doctrine::getTable('PatientDischarge')->find($db_discharge_array[0]['id']);
                                                $pd_db->isdelete = 0;
                                                $pd_db->save();
                                            }
                                            
                                            // REFRESH PATEINT ACTIVE
                                            //added patient admission/readmission new procedure
                                            PatientMaster::get_patient_admissions($ipid);
                                            
                                        } else if($patient_falls[$fall - 1][0] == "standby" ){
                                            
                                            $post['status'] = 'Remove ACTIVE admission -  move back to standby';
                                            
                                            // get previous admission date
                                            $previous_admission = $patient_falls[$fall - 1][1]; // previous fall admission date  
                                            
                                            //Update PatientMaster
                                            $patient_master_db = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                            if($patient_master_db){
                                                $patient_master_db->admission_date = $previous_admission;
                                                $patient_master_db->isdischarged = "0";
                                                $patient_master_db->isstandby = "1";
                                                $patient_master_db->isstandbydelete = "0";
                                                $patient_master_db->traffic_status = "1";
                                                $patient_master_db->save();
                                            }
                                            $current_patient_master = $patient_master_db->toArray();
                                            
                                            
                                            // readmission - delete last admission 
                                            $remove_readmission = Doctrine_Query::create()
                                            ->delete('PatientReadmission')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('date = ?', $_POST['date'])
                                            ->andWhere('date_type = ?',1)
                                            ->execute();
                                            
                                            // add new admission with the previous date 
                                            $cust = new PatientReadmission();
                                            $cust->ipid = $ipid;
                                            $cust->user_id = $userid;
                                            $cust->date = date("Y-m-d H:i:s", strtotime($previous_admission));
                                            $cust->date_type = 1;
                                            $cust->save();
                                            
                                            
                                            $drop = Doctrine_Query::create()
                                            ->select('id,date,date_type,ipid')
                                            ->from('PatientStandbyDetails')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('date_type = ?', 2)
                                            ->orderBy('date DESC')
                                            ->limit(1);
                                            $droparray = $drop->fetchArray();
                                            
                                            if($droparray){
                                                $post['standby'] =  $droparray[0];
                                                 
                                                $standby_details['date'] = $droparray[0]['date'];
                                                if($standby_details['date'] == $_POST['date']){
                                                    $q = Doctrine_Query::create()
                                                    ->delete('PatientStandbyDetails')
                                                    ->where('ipid = ?', $ipid)
                                                    ->andWhere('date = ?', $standby_details['date'])
                                                    ->andWhere('date_type = ?',2);
                                                    $q->execute();
                                                }
                                            }
                                            // REFRESH PATEINT STANDBY
                                            //added patient admission/readmission new procedure
                                            PatientMaster::get_patient_standby_admissions($ipid);
                                        }
                                        
                                        
                                        // write in log
                                        $test = array_merge($post);
                                        $patient_fall_log = new PatientHistoryLog();
                                        $patient_fall_log->ipid = $ipid;
                                        $patient_fall_log->type = "adm";
                                        $patient_fall_log->details = serialize($test);
                                        $patient_fall_log->save();
                                        
                                        $return['error'] = "0";
                                        $return['text'] = "admission deleted moved to ".$patient_falls[$fall - 1][0];
                                        
                                        // write in Patient Course
                                        $comment ="";
                                        $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                                        $del_comment = $this->view->translate('admission_date_manually_deleted_moved_to_'.$patient_falls[$fall - 1][0]);
                                        $comment = str_replace('%date',$date_dmY,$del_comment);
                                        
                                        $userid = $logininfo->userid;
                                        $cust = new PatientCourse();
                                        $cust->ipid = $ipid;
                                        $cust->course_date = date("Y-m-d H:i:s", time());
                                        $cust->course_type = Pms_CommonData::aesEncrypt("K");
                                        $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                                        $cust->user_id = $userid;
                                        $cust->save();
                                    } 
                                    break;

                                    
                                case "2":
                                    // delete current discharge
                                                 
                                    $post['status'] = 'Remove discharge -  move back to active';
                                    $post['user'] = $logininfo->userid;
                                    
                                    // check patient death  --  death by button
                                    $drop_pdeath = Doctrine_Query::create()
                                    ->select('id,ipid')
                                    ->from('PatientDeath')
                                    ->where('ipid = ?', $ipid)
                                    ->limit(1);
                                    $drop_pdeath_arr = $drop_pdeath->fetchArray();
                                    if($drop_pdeath_arr){
                                        $pdeath_db = Doctrine::getTable('PatientDeath')->find($drop_pdeath_arr[0]['id']);
                                        $pdeath_db->isdelete = 1;
                                        $pdeath_db->save();
                                    
                                    
                                        // update in patient course
                                        $db_pc = Doctrine_Query::create()
                                        ->select("id,ipid,recordid,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
                                        ->from('PatientCourse')
                                        ->where('ipid = ?', $ipid)
                                        ->andWhere('recordid = ?', $drop_pdeath_arr[0]['id'])
                                        ->andWhere('source_ipid = ""')
                                        ->orderBy('id ASC');
                                        $db_pc_array = $db_pc->fetchArray();
                                    
                                         
                                        $wrong_comment = $this->view->translate('discharge_date_manually_deleted_by_user');
                                        $wrong_comment_str =str_replace('%user',$user_name,$wrong_comment);
                                    
                                        $discharge_death_tabnames = array('patient_death');
                                        foreach($db_pc_array as $k=>$pc_data){
                                            if(in_array($pc_data['tabname'],$discharge_death_tabnames)){
                                                $stmb = Doctrine::getTable('PatientCourse')->find($pc_data['id']);
                                                $stmb->tabname = Pms_CommonData::aesEncrypt("deleted_discharge_death");
                                                $stmb->wrong = "1";
                                                $stmb->wrongcomment = $wrong_comment_str;
                                                $stmb->save();
                                            }
                                        }
                                    }
                                    
                                    
                                    
                                    // update patient master -  set isdischarged  = 1 . traffic status  = 1
                                    $drop_pm = Doctrine_Query::create()
                                    ->select('id,ipid')
                                    ->from('PatientMaster')
                                    ->where('ipid = ?', $ipid)
                                    ->limit(1);
                                    $drop_pm_array = $drop_pm->fetchArray();
                                    
                                    if($drop_pm_array){
                                        $patient_master_db = Doctrine::getTable('PatientMaster')->find($drop_pm_array[0]['id']);
                                        if($patient_master_db){
                                            $patient_master_db->isdischarged = "0";
                                            $patient_master_db->traffic_status = "1";
                                            $patient_master_db->save();
                                        }
                                    }
                                    
                                    
                                    
                                    // remove last discharge from patient readmission
                                    $drop = Doctrine_Query::create()
                                    ->select('id,date,date_type,ipid')
                                    ->from('PatientReadmission')
                                    ->where('ipid = ?',$ipid)
                                    ->andWhere('date_type = ?', 2)
                                    ->orderBy('date DESC')
                                    ->limit(1);
                                    $droparray = $drop->fetchArray();
                                    
                                    if($droparray){
                                        $post['readmission'] =  $droparray[0];
                                    
                                        $readmission_details['date'] = $droparray[0]['date'];
                                        if($readmission_details['date'] == $_POST['date']){
                                            $q = Doctrine_Query::create()
                                            ->delete('PatientReadmission')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('date = ?', $readmission_details['date'])
                                            ->andWhere('date_type = ?', 2);
                                            $q->execute();
                                        }
                                    }
                                    
                                    // patient discharge- mark as deleted an manualy deleted
                                    // patient discharge -  edit - last discharge set isdelete = 0
                                    $db_discharge = Doctrine_Query::create()
                                    ->select('id,discharge_date,ipid')
                                    ->from('PatientDischarge')
                                    ->where('ipid = ?', $ipid)
                                    ->andWhere('isdelete = ?', 0)
                                    ->orderBy('discharge_date DESC')
                                    ->limit(1);
                                    $db_discharge_array = $db_discharge->fetchArray();
                                    
                                    if($db_discharge_array){
                                        $post['discharge_data'] = $db_discharge_array[0];
                                    
                                        if($db_discharge_array[0]['discharge_date'] == $_POST['date'])
                                        {
                                            $q = Doctrine_Query::create()
                                            ->delete('PatientDischarge')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('discharge_date = ?', $_POST['date'])
                                            ->andWhere('isdelete = ?', 0);
                                            $q->execute();
                                    
                                    
                                            // update in patient course
                                            $db_pc = Doctrine_Query::create()
                                            ->select("id,ipid,recordid,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
                                            ->from('PatientCourse')
                                            ->where('ipid = ?', $ipid)
                                            ->andWhere('recordid = ?', $db_discharge_array[0]['id'])
                                            ->andWhere('source_ipid = ""')
                                            ->orderBy('id ASC');
                                            $db_pc_array = $db_pc->fetchArray();
                                             
                                            $wrong_comment = $this->view->translate('discharge_date_manually_deleted_by_user');
                                            $wrong_comment_str =str_replace('%user',$user_name,$wrong_comment);
                                    
                                            $discharge_tabnames = array('discharge','discharge_date');
                                            foreach($db_pc_array as $k=>$pc_data){
                                                if(in_array($pc_data['tabname'],$discharge_tabnames)){
                                                    $stmb = Doctrine::getTable('PatientCourse')->find($pc_data['id']);
                                                    $stmb->tabname = Pms_CommonData::aesEncrypt("deleted_discharge");
                                                    $stmb->wrong = "1";
                                                    $stmb->wrongcomment = $wrong_comment_str;
                                                    $stmb->save();
                                                }
                                            }
                                        }
                                    }
                                    
                                    // REFRESH PATEINT ACTIVE
                                    //added patient admission/readmission new procedure
                                    PatientMaster::get_patient_admissions($ipid);
                                    
                                    // write in log
                                    $test = array_merge($post);
                                    $patient_fall_log = new PatientHistoryLog();
                                    $patient_fall_log->ipid = $ipid;
                                    $patient_fall_log->type = "dis";
                                    $patient_fall_log->details = serialize($test);
                                    $patient_fall_log->save();
                                    
                                    $return['error'] = "0";
                                    $return['text'] = "discharge deleted ";
                                    
                                    
                                    // write in Patient Course
                                    $comment ="";
                                    $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                                    $del_comment = $this->view->translate('discharge_date_manually_deleted');
                                    $comment = str_replace('%date',$date_dmY,$del_comment);
                                    
                                    $userid = $logininfo->userid;
                                    $cust = new PatientCourse();
                                    $cust->ipid = $ipid;
                                    $cust->course_date = date("Y-m-d H:i:s", time());
                                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                                    $cust->user_id = $userid;
                                    $cust->save();
                                    
                                    
                                    break;
                            
                                default:
                             
                                    break;
                            }    
                            
                        }
                            break;
                            
                        case "standby":{
                            
                            if($_POST['date_type'] == "1" && $patient_falls[$fall - 1][0] == "discharge" )
                            {
                                $post['status'] = 'Remove STANDBY admission -  move back to discharge';
                            
                                // get previous admission date
                                $previous_admission = $patient_falls[$fall - 2][1]; // previous fall admission date before discharge
                            
                                //Update PatientMaster
                                $patient_master_db = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                if($patient_master_db){
                                    $patient_master_db->admission_date = $previous_admission;
                                    $patient_master_db->isdischarged = "1";
                                    $patient_master_db->isstandby = "0";
                                    $patient_master_db->isstandbydelete = "0";
                                    $patient_master_db->traffic_status = "1";
                                    $patient_master_db->save();
                                }
                                $current_patient_master = $patient_master_db->toArray();
                            
                            
                                // remove from patient readmission the last admiision date
                                $remove_readmission = Doctrine_Query::create()
                                ->delete('PatientReadmission')
                                ->where('ipid = ?', $ipid)
                                ->andWhere('date = ?', date("Y-m-d H:i:s",strtotime($_POST['date'])))
                                ->andWhere('date_type = ?',1)
                                ->execute();
                            
                            
                                // patient discharge -  edit - last discharge set isdelete = 0
                                $db_discharge = Doctrine_Query::create()
                                ->select('id,discharge_date,ipid')
                                ->from('PatientDischarge')
                                ->where('ipid = ?', $ipid)
                                ->andWhere('isdelete = ?', 1)
                                ->orderBy('discharge_date DESC')
                                ->limit(1);
                                $db_discharge_array = $db_discharge->fetchArray();
                            
                                if($db_discharge_array){
                                    $pd_db = Doctrine::getTable('PatientDischarge')->find($db_discharge_array[0]['id']);
                                    $pd_db->isdelete = 0;
                                    $pd_db->save();
                                }
                            
                                // REFRESH PATEINT ACTIVE
                                //added patient admission/readmission new procedure
                                PatientMaster::get_patient_admissions($ipid);
                            
                            

                                $drop = Doctrine_Query::create()
                                ->select('id,date,date_type,ipid')
                                ->from('PatientStandbyDetails')
                                ->where('ipid = ?', $ipid)
                                ->andWhere('date_type = ?', 1)
                                ->orderBy('date DESC')
                                ->limit(1);
                                $droparray = $drop->fetchArray();
                                
                                if($droparray){
                                    $post['standby'] =  $droparray[0];
                                     
                                    $standby_details['date'] = $droparray[0]['date'];
                                    if($standby_details['date'] == $_POST['date']){
                                        $q = Doctrine_Query::create()
                                        ->delete('PatientStandbyDetails')
                                        ->where('ipid = ?', $ipid)
                                        ->andWhere('date = ?', $standby_details['date'])
                                        ->andWhere('date_type = ?',1);
                                        $q->execute();
                                    }
                                }
                                // REFRESH PATEINT STANDBY
                                //added patient admission/readmission new procedure
                                PatientMaster::get_patient_standby_admissions($ipid);
                                
                                
                                
                                // log data
                                // write in log
                                $test = array_merge($post);
                                $patient_fall_log = new PatientHistoryLog();
                                $patient_fall_log->ipid = $ipid;
                                $patient_fall_log->type = "adm";
                                $patient_fall_log->details = serialize($test);
                                $patient_fall_log->save();
                                
                                $return['error'] = "0";
                                $return['text'] = "admission deleted moved to discharge ";
                                
                                // write in Patient Course
                                $comment ="";
                                $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                                $del_comment = $this->view->translate('admission_date_manually_deleted_moved_to_discharge');
                                $comment = str_replace('%date',$date_dmY,$del_comment);
                                
                                $userid = $logininfo->userid;
                                $cust = new PatientCourse();
                                $cust->ipid = $ipid;
                                $cust->course_date = date("Y-m-d H:i:s", time());
                                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                                $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                                $cust->user_id = $userid;
                                $cust->save();
                            
                            
                            }
                            
                            }
                            break;
                            
                            
                            
                            
                        case "standbydelete":
                            
                            // move from standby delete to standby
                            // delete detandbydelete start
                            // delete standby end
                            
                            if($_POST['date_type'] == "1" && $patient_falls[$fall - 1][0] == "standby"){
                                 
                                $post['status'] = 'Remove standbydelete admission -  move back to standby';
                                $post['user'] = $logininfo->userid;
                            
                                $drop = Doctrine_Query::create()
                                ->select('id,date,date_type,ipid')
                                ->from('PatientStandbyDetails')
                                ->where('ipid = ?', $ipid)
                                ->andWhere('date_type = ?', 2)
                                ->orderBy('date DESC')
                                ->limit(1);
                                $droparray = $drop->fetchArray();
                            
                                if($droparray){
                                    $post['standby'] =  $droparray[0];
                                     
                                    $standby_details['date'] = $droparray[0]['date'];
                                    if($standby_details['date'] == $_POST['date']){
                                        $q = Doctrine_Query::create()
                                        ->delete('PatientStandbyDetails')
                                        ->where('ipid = ?', $ipid)
                                        ->andWhere('date = ?', $standby_details['date'])
                                        ->andWhere('date_type = ?',2);
                                        $q->execute();
                                    }
                                }
                                // REFRESH PATEINT STANDBY
                                //added patient admission/readmission new procedure
                                PatientMaster::get_patient_standby_admissions($ipid);
                                 
                                $drop_del = Doctrine_Query::create()
                                ->select('id,date,date_type,ipid')
                                ->from('PatientStandbyDeleteDetails')
                                ->where('ipid = ?', $ipid)
                                ->andWhere('date_type = ?', 1)
                                ->orderBy('date DESC')
                                ->limit(1);
                                $droparray_del = $drop_del->fetchArray();
                            
                                if($droparray_del){
                                    $post['standbydelete'] =  $droparray_del[0];
                                     
                                    $standby_details['date'] = $droparray_del[0]['date'];
                                    if($standby_details['date'] == $_POST['date']){
                                        $q = Doctrine_Query::create()
                                        ->delete('PatientStandbyDeleteDetails')
                                        ->where('ipid = ?', $ipid)
                                        ->andWhere('date = ?', $standby_details['date'])
                                        ->andWhere('date_type = ?',1);
                                        $q->execute();
                                    }
                                }
                                // REFRESH PATEINT STANDBYDELETE
                                //added patient admission/readmission new procedure
                                PatientMaster::get_patient_standbydelete_admissions($ipid);
                                 
                                 
                                $drop_pm = Doctrine_Query::create()
                                ->select('id,ipid')
                                ->from('PatientMaster')
                                ->where('ipid = ?', $ipid)
                                ->limit(1);
                                $drop_pm_array = $drop_pm->fetchArray();
                                 
                                if($drop_pm_array){
                                    $patient_master_db = Doctrine::getTable('PatientMaster')->find($drop_pm_array[0]['id']);
                                    if($patient_master_db){
                                        $patient_master_db->traffic_status = "1";
                                         
                                        if($patient_falls[$fall - 1][0] == "standby" ){
                                            $patient_master_db->isdischarged = "0";
                                            $patient_master_db->isstandbydelete = "0";
                                            $patient_master_db->isstandby = "1";
                                        }
                                        $patient_master_db->save();
                                    }
                                }
                                 
                            
                                // write in log
                                $test = array_merge($post);
                                $patient_fall_log = new PatientHistoryLog();
                                $patient_fall_log->ipid = $ipid;
                                $patient_fall_log->type = "adm";
                                $patient_fall_log->details = serialize($test);
                                $patient_fall_log->save();
                                 
                                $return['error'] = "0";
                                $return['text'] = "standbydelete admission deleted move back to stanby";
                                 
                                // write in Patient Course
                                $comment ="";
                                $date_dmY = date('d.m.Y',strtotime($_POST['date']));
                                $del_comment = $this->view->translate('manualy delelete standbydelete admission, moved back to standby');
                                $comment = str_replace('%date',$date_dmY,$del_comment);
                                 
                                $userid = $logininfo->userid;
                                $cust = new PatientCourse();
                                $cust->ipid = $ipid;
                                $cust->course_date = date("Y-m-d H:i:s", time());
                                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                                $cust->course_title = Pms_CommonData::aesEncrypt($comment);
                                $cust->user_id = $userid;
                                $cust->save();
                            }
                            
                            
                            break;

                    
                        default:
                            break;
                    }
                    
                    // Maria:: Migration ISPC to CISPC 08.08.2020
                    //ISPC-2614 Ancuta 17.07.2020
                    $int_connection = new IntenseConnections();
                    $share_direction = $int_connection->get_intense_connection_by_ipid($ipid);

                    $patient_master = new PatientMaster();
                    foreach ($share_direction as $direction_k => $share_info) {
                        if (! empty($share_info['intense_connection'])) {
                            foreach ($share_info['intense_connection'] as $con => $con_ionfo) {
                                $IntenseConnectionsOptions = array_column($con_ionfo['IntenseConnectionsOptions'], 'option_name');
                                if (in_array('patient_falls', $IntenseConnectionsOptions) ) {
                                    $patient_master->intense_connection_patient_admissions($share_info['source'], $share_info['target']);
                                }
                            }
                        }
                    }
                    // --


                    echo json_encode($return);
                    exit;
                } 
                else
                {
                    $return['error'] = "1";
                    $return['text'] = $this->view->translate('error_manually_manage_dates');
                }
            }
            
            echo json_encode($return);
            exit;
        }
        
        
        
        
        public function deletepatientfallsAction(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            /*			 * ******* Patient History ************ */
            /*			 * ******* Patient Information ************ */
            $patientmaster = new PatientMaster();
            
            $patient_falls_master = $patientmaster->patient_falls($ipid);
            $patient_falls = $patient_falls_master['falls'];
            $first_admission_ever = $patient_falls_master['first_admission_ever'];;
             
            $this->view->first_admission_ever = $first_admission_ever;
            $this->view->patient_falls = $patient_falls;
            
            
            $return['error'] = "0";
            if($this->getRequest()->isPost())
            {
//             	print_r($_POST); //exit;
                if(!empty($_POST))
                {
                    $post = $_POST;
                    $fall = $_POST['fall'];
                    
                    if($patient_falls[$_POST['fall']][1] == $_POST['admission_date'] && $patient_falls[$_POST['fall']][2] == $_POST['discharge_date']  && $_POST['fall_type'] ==  $patient_falls[$_POST['fall']][0] )
                    {// check if data was not changed 
                    	
                    	if($patient_falls[$_POST['fall']][0] == "standby"){
                    		// if previous or next is standby delete - remove it as well 
                    		
                    		// remove from patient standby details the discharge date
                    		$readmission_dis_q = Doctrine_Query::create()
                    		->select('id,date,date_type,ipid')
                    		->from('PatientStandbyDetails')
                    		->where('ipid = ?', $ipid)
                    		->andWhere('date_type = ?', 2)
                    		->andWhere('date = ?', $_POST['discharge_date'])
                    		->limit(1);
                    		$readmission_dis_arr= $readmission_dis_q->fetchArray();
                    		 
                    		if($readmission_dis_arr)
                    		{
                    			$post['readmission'] =  $readmission_dis_arr[0];
                    			 
                    			$readmission_discharge_details =  $readmission_dis_arr[0];
                    			$readmission_discharge_details['date'] = $readmission_dis_arr[0]['date'];
                    			 
                    			if($readmission_discharge_details['date'] == $_POST['discharge_date'])
                    			{
                    				$q = Doctrine_Query::create()
                    				->delete('PatientStandbyDetails')
                    				->where('ipid = ?', $ipid)
                    				->andWhere('date = ?', $readmission_discharge_details['date'])
                    				->andWhere('date_type = ?', 2);
                    				$q->execute();
                    			}
                    		}
                    		 
                    		// remove from patient readmission the admission date
                    		$readmission_adm_q = Doctrine_Query::create()
                    		->select('id,date,date_type,ipid')
                    		->from('PatientStandbyDetails')
                    		->where('ipid = ?', $ipid)
                    		->andWhere('date_type = ?',1)
                    		->andWhere('date = ?', $_POST['admission_date'])
                    		->limit(1);
                    		$readmission_adm_arr= $readmission_adm_q->fetchArray();
                    		 
                    		if($readmission_adm_arr)
                    		{
                    			$readmission_admission_details =  $readmission_adm_arr[0];
                    			$readmission_admission_details['date'] = $readmission_adm_arr[0]['date'];
                    			 
                    			if($readmission_admission_details['date'] == $_POST['admission_date'])
                    			{
                    				$q = Doctrine_Query::create()
                    				->delete('PatientStandbyDetails')
                    				->where('ipid = ?', $ipid)
                    				->andWhere('date = ?', $readmission_admission_details['date'])
                    				->andWhere('date_type = ?', 1);
                    				$q->execute();
                    			}
                    		}
                    		 
                    		// REFRESH PATEINT Standby
                    		//added patient admission/readmission new procedure
                    		PatientMaster::get_patient_standby_admissions($ipid);
                    		 
                    		// write in log
                    		$test = array_merge($post);
                    		$patient_fall_log = new PatientHistoryLog();
                    		$patient_fall_log->ipid = $ipid;
                    		$patient_fall_log->type = "fall";
                    		$patient_fall_log->details = serialize($test);
                    		$patient_fall_log->save();
                    		 
                    		$return['error'] = "0";
                    		$return['text'] = "entire standby fall deleted ";
                    		 
                    		
                    		// write in Patient Course
                    		$date_adm_dmY = date('d.m.Y',strtotime($_POST['admission_date']));
                    		$date_dis_dmY = date('d.m.Y',strtotime($_POST['discharge_date']));
                    		$period = $date_adm_dmY."-".$date_dis_dmY;
                    		 
                    		$comment ="";
                    		$del_comment = $this->view->translate('fall_manually_deleted');
                    		$comment = str_replace('%period',$period,$del_comment);
                    		 
                    		$userid = $logininfo->userid;
                    		$cust = new PatientCourse();
                    		$cust->ipid = $ipid;
                    		$cust->course_date = date("Y-m-d H:i:s", time());
                    		$cust->course_type = Pms_CommonData::aesEncrypt("K");
                    		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
                    		$cust->user_id = $userid;
                    		$cust->save();
                    		
                    		
                    		
                    	} 
                    	elseif($patient_falls[$_POST['fall']][0] == "standbydelete")
                    	{
                    		
                    		// remove from patient standby details the discharge date
                    		$readmission_dis_q = Doctrine_Query::create()
                    		->select('id,date,date_type,ipid')
                    		->from('PatientStandbyDeleteDetails')
                    		->where('ipid = ?', $ipid)
                    		->andWhere('date_type = ?', 2)
                    		->andWhere('date = ?', $_POST['discharge_date'])
                    		->limit(1);
                    		$readmission_dis_arr= $readmission_dis_q->fetchArray();
                    		 
                    		if($readmission_dis_arr)
                    		{
                    			$post['readmission'] =  $readmission_dis_arr[0];
                    			 
                    			$readmission_discharge_details =  $readmission_dis_arr[0];
                    			$readmission_discharge_details['date'] = $readmission_dis_arr[0]['date'];
                    			 
                    			if($readmission_discharge_details['date'] == $_POST['discharge_date'])
                    			{
                    				$q = Doctrine_Query::create()
                    				->delete('PatientStandbyDeleteDetails')
                    				->where('ipid = ?', $ipid)
                    				->andWhere('date = ?', $readmission_discharge_details['date'])
                    				->andWhere('date_type = ?', 2);
                    				$q->execute();
                    			}
                    		}
                    		 
                    		// remove from patient readmission the admission date
                    		$readmission_adm_q = Doctrine_Query::create()
                    		->select('id,date,date_type,ipid')
                    		->from('PatientStandbyDeleteDetails')
                    		->where('ipid = ?', $ipid)
                    		->andWhere('date_type = ?',1)
                    		->andWhere('date = ?', $_POST['admission_date'])
                    		->limit(1);
                    		$readmission_adm_arr= $readmission_adm_q->fetchArray();

                    		
                    		if($readmission_adm_arr)
                    		{
                    			$readmission_admission_details =  $readmission_adm_arr[0];
                    			$readmission_admission_details['date'] = $readmission_adm_arr[0]['date'];
                    			 
                    			if($readmission_admission_details['date'] == $_POST['admission_date'])
                    			{
                    				$q = Doctrine_Query::create()
                    				->delete('PatientStandbyDeleteDetails')
                    				->where('ipid = ?', $ipid)
                    				->andWhere('date = ?', $readmission_admission_details['date'])
                    				->andWhere('date_type = ?', 1);
                    				$q->execute();
                    			}
                    		}
                    		 
                    		// REFRESH PATEINT Standby
                    		//added patient admission/readmission new procedure
                    		PatientMaster::get_patient_standbydelete_admissions($ipid);
                    		 
                    		
                    		
                    		//make stanby continu
                    		$readmission_dis_q = Doctrine_Query::create()
                    		->select('id,date,date_type,ipid')
                    		->from('PatientStandbyDetails')
                    		->where('ipid = ?', $ipid)
                    		->andWhere('date_type = ?', 2)
                    		->andWhere('date = ?', $_POST['admission_date'])
                    		->limit(1);
                    		$readmission_dis_arr_st= $readmission_dis_q->fetchArray();
                    		 
//                     		print_r($readmission_dis_arr_st);
                    		
                    		if($readmission_dis_arr_st)
                    		{
                    			$post['readmission'] =  $readmission_dis_arr_st[0];
                    		
                    			$readmission_discharge_details =  $readmission_dis_arr_st[0];
                    			$readmission_discharge_details['date'] = $readmission_dis_arr_st[0]['date'];
                    		
                    			if($readmission_discharge_details['date'] == $_POST['admission_date'])
                    			{
                    				$q = Doctrine_Query::create()
                    				->delete('PatientStandbyDetails')
                    				->where('ipid = ?', $ipid)
                    				->andWhere('date = ?', $readmission_discharge_details['date'])
                    				->andWhere('date_type = ?', 2);
                    				$q->execute();
                    			}
                    		}
                    		
                    		
                    		// remove from patient readmission the admission date
                    		$readmission_adm_q = Doctrine_Query::create()
                    		->select('id,date,date_type,ipid')
                    		->from('PatientStandbyDetails')
                    		->where('ipid = ?', $ipid)
                    		->andWhere('date_type = ?',1)
                    		->andWhere('date = ?', $_POST['discharge_date'])
                    		->limit(1);
                    		$readmission_adm_arr_st= $readmission_adm_q->fetchArray();
                    		 
//                     		print_R($readmission_adm_arr_st);
                    		
                    		if($readmission_adm_arr_st)
                    		{
                    			$readmission_admission_details =  $readmission_adm_arr_st[0];
                    			$readmission_admission_details['date'] = $readmission_adm_arr_st[0]['date'];
                    		
                    			if($readmission_admission_details['date'] == $_POST['discharge_date'])
                    			{
                    				$q = Doctrine_Query::create()
                    				->delete('PatientStandbyDetails')
                    				->where('ipid = ?', $ipid)
                    				->andWhere('date = ?', $readmission_admission_details['date'])
                    				->andWhere('date_type = ?', 1);
                    				$q->execute();
                    			}
                    		}
                    		 
                    		// REFRESH PATEINT Standby
                    		//added patient admission/readmission new procedure
                    		PatientMaster::get_patient_standby_admissions($ipid);
                    		 
                    		
                    		// write in log
                    		$test = array_merge($post);
                    		$patient_fall_log = new PatientHistoryLog();
                    		$patient_fall_log->ipid = $ipid;
                    		$patient_fall_log->type = "fall";
                    		$patient_fall_log->details = serialize($test);
                    		$patient_fall_log->save();
                    		 
                    		$return['error'] = "0";
                    		$return['text'] = "entire standbystandby fall deleted ";
                    		 
                    		
                    		// write in Patient Course
                    		$date_adm_dmY = date('d.m.Y',strtotime($_POST['admission_date']));
                    		$date_dis_dmY = date('d.m.Y',strtotime($_POST['discharge_date']));
                    		$period = $date_adm_dmY."-".$date_dis_dmY;
                    		 
                    		$comment ="";
                    		$del_comment = $this->view->translate('fall_manually_deleted');
                    		$comment = str_replace('%period',$period,$del_comment);
                    		 
                    		$userid = $logininfo->userid;
                    		$cust = new PatientCourse();
                    		$cust->ipid = $ipid;
                    		$cust->course_date = date("Y-m-d H:i:s", time());
                    		$cust->course_type = Pms_CommonData::aesEncrypt("K");
                    		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
                    		$cust->user_id = $userid;
                    		$cust->save();
                    		
                    		
                    		
                    	} 
                    	else
                    	{
	                        $post['status'] = 'Remove entire fall';
	                        $post['user'] = $logininfo->userid;
	                        
	                        // set as delete the discharge,
	                        $db_discharge = Doctrine_Query::create()
	                        ->select('id,discharge_date,ipid')
	                        ->from('PatientDischarge')
	                        ->where('ipid = ?', $ipid)
	                        ->andWhere('isdelete = ?',1)
	                        ->andWhere('discharge_date = ?', $_POST['discharge_date'])
	                        ->limit(1);
	                        $db_discharge_array = $db_discharge->fetchArray();
	                        
	                        if($db_discharge_array){ // delete
	                            $post['discharge_data'] = $db_discharge_array[0];
	                            
	                            if($db_discharge_array[0]['discharge_date'] == $_POST['discharge_date'])
	                            {
	                                $q = Doctrine_Query::create()
	                                ->delete('PatientDischarge')
	                                ->where('ipid = ?', $ipid)
	                                ->andWhere('discharge_date = ?',$_POST['discharge_date'])
	                                ->andWhere('isdelete = ?', 1);
	                                $q->execute();
	                                
	
	                                // update in patient course
	                                $db_pc = Doctrine_Query::create()
	                                ->select("id,ipid,recordid,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
	                                ->from('PatientCourse')
	                                ->where('ipid = ?', $ipid)
	                                ->andWhere('recordid = ?', $db_discharge_array[0]['id'])
									->andWhere('source_ipid = ""')
	                                ->orderBy('id ASC');
	                                $db_pc_array = $db_pc->fetchArray();
	                                
	                                 
	                                $wrong_comment = $this->view->translate('discharge_date_manually_deleted_by_user');
	                                $wrong_comment_str =str_replace('%user',$user_name,$wrong_comment);
	                                
	                                $discharge_tabnames = array('discharge','discharge_date');
	                                foreach($db_pc_array as $k=>$pc_data){
	                                	if(in_array($pc_data['tabname'],$discharge_tabnames)){
	                                		$stmb = Doctrine::getTable('PatientCourse')->find($pc_data['id']);
	                                		$stmb->tabname = Pms_CommonData::aesEncrypt("deleted_discharge");
	                                		$stmb->wrong = "1";
	                                		$stmb->wrongcomment = $wrong_comment_str;
	                                		$stmb->save();
	                                	}
	                                }
	                                
	                                
	                            }
	                        }
	                        
	                        
	                        // remove from patient readmission the discharge date
	                        $readmission_dis_q = Doctrine_Query::create()
	                        ->select('id,date,date_type,ipid')
	                        ->from('PatientReadmission')
	                        ->where('ipid = ?', $ipid)
	                        ->andWhere('date_type = ?', 2)
	                        ->andWhere('date = ?', $_POST['discharge_date'])
	                        ->limit(1);
	                        $readmission_dis_arr= $readmission_dis_q->fetchArray();
	                        
	                        if($readmission_dis_arr)
	                        {
	                            $post['readmission'] =  $readmission_dis_arr[0];
	                            
	                            $readmission_discharge_details =  $readmission_dis_arr[0];
	                            $readmission_discharge_details['date'] = $readmission_dis_arr[0]['date'];
	                            
	                            if($readmission_discharge_details['date'] == $_POST['discharge_date'])
	                            {
	                                $q = Doctrine_Query::create()
	                                ->delete('PatientReadmission')
	                                ->where('ipid = ?', $ipid)
	                                ->andWhere('date = ?', $readmission_discharge_details['date'])
	                                ->andWhere('date_type = ?', 2);
	                                $q->execute();
	                            }
	                        }
	                        
	                        // remove from patient readmission the admission date
	                        $readmission_adm_q = Doctrine_Query::create()
	                        ->select('id,date,date_type,ipid')
	                        ->from('PatientReadmission')
	                        ->where('ipid = ?', $ipid)
	                        ->andWhere('date_type = ?',1)
	                        ->andWhere('date = ?', $_POST['admission_date'])
	                        ->limit(1);
	                        $readmission_adm_arr= $readmission_adm_q->fetchArray();
	                        
	                        if($readmission_adm_arr)
	                        {
	                            $readmission_admission_details =  $readmission_adm_arr[0];
	                            $readmission_admission_details['date'] = $readmission_adm_arr[0]['date'];
	                            
	                            if($readmission_admission_details['date'] == $_POST['admission_date'])
	                            {
	                                $q = Doctrine_Query::create()
	                                ->delete('PatientReadmission')
	                                ->where('ipid = ?', $ipid)
	                                ->andWhere('date = ?', $readmission_admission_details['date'])
	                                ->andWhere('date_type = ?', 1);
	                                $q->execute();
	                            }
	                        }
	                        
	                        // REFRESH PATEINT ACTIVE
	                        //added patient admission/readmission new procedure
	                        PatientMaster::get_patient_admissions($ipid);
	                        
	                        // write in log
	                        $test = array_merge($post);
	                        $patient_fall_log = new PatientHistoryLog();
	                        $patient_fall_log->ipid = $ipid;
	                        $patient_fall_log->type = "fall";
	                        $patient_fall_log->details = serialize($test);
	                        $patient_fall_log->save();
	                        
	                        $return['error'] = "0";
	                        $return['text'] = "entire fall deleted ";
	                        
	
	                        // write in Patient Course
	                        $date_adm_dmY = date('d.m.Y',strtotime($_POST['admission_date']));
	                        $date_dis_dmY = date('d.m.Y',strtotime($_POST['discharge_date']));
	                        $period = $date_adm_dmY."-".$date_dis_dmY;
	                        
	                        $comment ="";
	                        $del_comment = $this->view->translate('fall_manually_deleted');
	                        $comment = str_replace('%period',$period,$del_comment);
	                        
	                        $userid = $logininfo->userid;
	                        $cust = new PatientCourse();
	                        $cust->ipid = $ipid;
	                        $cust->course_date = date("Y-m-d H:i:s", time());
	                        $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                        $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                        $cust->user_id = $userid;
	                        $cust->save();
                    	}
                    } 
                    else
                    {
                        $return['error'] = "1";
                        $return['text'] = $this->view->translate('error_manually_manage_dates');
                    }
                }
            }
            echo json_encode($return);
            exit;
        }
        
        
        /**
         * ISPC-2883 ISPC: PLZ delete Vollversorgung
         * ISPC-2883 Ancuta 06.05.2021
         */
        
        public function deletepatientvvfallsAction(){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            /*			 * ******* Patient History ************ */
            /*			 * ******* Patient Information ************ */
            $patientmaster = new PatientMaster();
            
            
            $patient_falls_master = $patientmaster->patient_falls($ipid);
            $patient_falls = $patient_falls_master['falls'];
            $first_admission_ever = $patient_falls_master['first_admission_ever'];;
             
            $this->view->first_admission_ever = $first_admission_ever;
            $this->view->patient_falls = $patient_falls;
            
            
            
            $return['error'] = "0";
            if($this->getRequest()->isPost())
            {
                if(!empty($_POST))
                {
                    $post = $_POST;
                    $fall_type = $_POST['fall_type'];
                    
                    if($fall_type == 'open'){
                        if(  !empty($_POST['admission_date']) && $_POST['admission_date'] != "01.01.1971"){
                            
                            
                            // edit patient master  - voll 1  and voll date
                            $cust = Doctrine::getTable('PatientMaster')->find($decid);
                            $cust->vollversorgung = '0';
                            $cust->vollversorgung_date = "0000-00-00 00:00:00";
                            $cust->save();
                            
                            $date = date('Y-m-d',strtotime($_POST['admission_date']));
                            
                            // last
                            // remove from voll  history   -
                            $vlh_open_adm = Doctrine_Query::create()
                            ->select("*")
                            ->from('VollversorgungHistory')
                            ->where('ipid = ?', $ipid)
                            ->andWhere('DATE(date) = ?', $date)
                            ->andWhere('date_type = "1"')
                            ->andWhere('isdelete = "0"')
                            ->orderBy('date DESC')
                            ->limit(1)
                            ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);;
                            
                            if($vlh_open_adm){
                                $cust = Doctrine::getTable('VollversorgungHistory')->find($vlh_open_adm['id']);
                                $cust->isdelete = '1';
                                $cust->save();
                            }
                        }
                        
                    } else if($fall_type == 'closed'){
                        
                        if(  !empty($_POST['admission_date']) && $_POST['admission_date'] != "01.01.1971"
                            && 
                            !empty($_POST['discharge_date']) && $_POST['discharge_date'] != "01.01.1971"
                            ){
                        }
                        
                        $adm_date = date('Y-m-d',strtotime($_POST['admission_date']));
                        $dis_date = date('Y-m-d',strtotime($_POST['discharge_date']));
                        
                        //Delete admission 
                        $vlh_open_adm = Doctrine_Query::create()
                        ->select("*")
                        ->from('VollversorgungHistory')
                        ->where('ipid = ?', $ipid)
                        ->andWhere('DATE(date) = ?', $adm_date)
                        ->andWhere('date_type = "1"')
                        ->andWhere('isdelete = "0"')
                        ->orderBy('date DESC')
                        ->limit(1)
                        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);;
                        
                        if($vlh_open_adm){
                            $cust = Doctrine::getTable('VollversorgungHistory')->find($vlh_open_adm['id']);
                            $cust->isdelete = '1';
                            $cust->save();
                        }
                        //Delete discharge 
                        $vlh_open_dis = Doctrine_Query::create()
                        ->select("*")
                        ->from('VollversorgungHistory')
                        ->where('ipid = ?', $ipid)
                        ->andWhere('DATE(date) = ?', $dis_date)
                        ->andWhere('date_type = "2"')
                        ->andWhere('isdelete = "0"')
                        ->orderBy('date DESC')
                        ->limit(1)
                        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);;
                        
                        if($vlh_open_dis){
                            $cust = Doctrine::getTable('VollversorgungHistory')->find($vlh_open_dis['id']);
                            $cust->isdelete = '1';
                            $cust->save();
                        }
                    }  
                    else
                    {
                        $return['error'] = "1";
                        $return['text'] = $this->view->translate('error_manually_manage_dates');
                    }
                }
            }
            echo json_encode($return);
            exit;
        }
        
        //  TEST
        
        
        
	    public function medisyncwidgetAction(){
	        $this->_helper->layout->setLayout('layout_ajax');
	
	        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
	        $ipid = Pms_CommonData::getIpid($decid);
	
	        $drugs_arr=SystemsSyncPackets::get_med_widget_data($ipid);
	
	        $drugs=array();
	
	        if(count($drugs_arr)>0) {
	            $drugs = json_decode($drugs_arr[0]['payload'], true);
	        }
	
	        $this->view->message="Dieser Medikationsplan wurde bei der letzen Synchronisierung des Patienten mitgeschickt. Die Medikamente k�nnen nun in den aktuellen Medikationsplan des Patienten �bernommen werden.";
	        $this->view->message="Dieser Medikationsplan wurde bei der letzten Synchronisierung des Patienten mitgeschickt. Die Medikamente künnen nun in den aktuellen Medikationsplan des Patienten  übernommen werden.";
	        $this->view->date=$drugs['date'];
	        $this->view->drugs=$drugs['drugs'];
	        $this->view->syncid=$drugs_arr[0]['id'];
	        $this->view->done=$drugs_arr[0]['done'];
    	}

	    /*
	     * Mark a med-packet from SystemsSyncPackets as done
	     */
	    public function marksyncmedpacketdoneAction(){
	        $this->_helper->viewRenderer->setNoRender();
	        $this->_helper->layout->setLayout('layout_ajax');
	        $syncid = intval($_REQUEST['syncid']);
	        SystemsSyncPackets::get_med_data($syncid,1);
	        echo json_encode('OK');
	    }
	    
	    public function loadmuster13logAction()
	    {
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid;
	    	
	    	
	    	if(strlen($_REQUEST['id']) > 0 && strlen($_REQUEST['prid']) > '0' && $_REQUEST['prid']>'0')
	    	{
	    		$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_REQUEST['id']));
	    		$ms13_id = $_REQUEST['prid'];
	    		
	    		$muster13 = new Muster13();
	    		$users = new User();
	    		$muster13_log = new Muster13Log();
	    		
	    		//get all muster13 log
	    		$muster13_log_res = $muster13_log->get_patient_muster13_log($ipid, $ms13_id);
	    		//get all client users START
	    		$users_res = $users->get_client_users($clientid, "0", true);
	    
	    		//prepare users array..
	    		$usersarray[] = '99999999';
	    		foreach($users_res as $k_user => $user)
	    		{
	    			$usersarray[] = $user['id'];
	    			$doctorusers[$user['id']] = $user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
	    		}
	    
	    		$doctorusers = Pms_CommonData::a_sort($doctorusers);
	    
	    		$ms13ids[] = '99999999';
	    		if($muster13_log_res)
	    		{
	    			foreach($muster13_log_res as $k_log => $v_log)
	    			{
	    				$vlog_date = strtotime(date('Y-m-d H:i', strtotime($v_log['date'])));
	    				$history_log[$vlog_date.'-'.$v_log['user']][] = $v_log;
	    				$ms13ids[] = $v_log['muster13id'];
	    			}
	    		}
   
	    		//get muster13 details
	    		$ms13ids_data = $muster13->get_multiple_muster13s($ms13ids, $clientid);
	    		
	    		foreach($ms13ids_data as $k_ms13s_d=> $v_ms13s_d)
	    		{
	    			$ms13s_details[$v_ms13s_d['id']] = $v_ms13s_d;
	    		}
	    
	    		$this->view->history_log = $history_log;
	    		$this->view->users = $doctorusers;
	    		$this->view->ms13s_details =  $ms13s_details;
	    	}
	    		
	    	$response['msg'] = "Success";
	    	$response['error'] = "";
	    	$response['callBack'] = "callback_history";
	    	$response['callBackParameters'] = array();
	    	$response['callBackParameters']['ms13id'] = $_REQUEST['prid'];//passthrough the receipt id
	    	$response['callBackParameters']['historylog'] = $this->view->render('ajax/loadmuster13log.html');
	    
	    	echo json_encode($response);
	    	exit;
	    }
	    
	    /**
	     * // ISPC-2530 + TODO-3572 Ancuta 11.11.2020
	     */
	    public function loadmuster132020logAction()
	    {
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid;
	    	
	    	
	    	if(strlen($_REQUEST['id']) > 0 && strlen($_REQUEST['prid']) > '0' && $_REQUEST['prid']>'0')
	    	{
	    		$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_REQUEST['id']));
	    		$ms13_id = $_REQUEST['prid'];
	    		
	    		$muster13 = new Muster13();
	    		$users = new User();
	    		$muster13_log = new Muster13Log();
	    		
	    		//get all muster13 log
	    		$muster13_log_res = $muster13_log->get_patient_muster13_log($ipid, $ms13_id);
	    		//get all client users START
	    		$users_res = $users->get_client_users($clientid, "0", true);
	    
	    		//prepare users array..
	    		$usersarray[] = '99999999';
	    		foreach($users_res as $k_user => $user)
	    		{
	    			$usersarray[] = $user['id'];
	    			$doctorusers[$user['id']] = $user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
	    		}
	    
	    		$doctorusers = Pms_CommonData::a_sort($doctorusers);
	    
	    		$ms13ids[] = '99999999';
	    		if($muster13_log_res)
	    		{
	    			foreach($muster13_log_res as $k_log => $v_log)
	    			{
	    				$vlog_date = strtotime(date('Y-m-d H:i', strtotime($v_log['date'])));
	    				$history_log[$vlog_date.'-'.$v_log['user']][] = $v_log;
	    				$ms13ids[] = $v_log['muster13id'];
	    			}
	    		}
   
	    		//get muster13 details
	    		$ms13ids_data = $muster13->get_multiple_muster13s($ms13ids, $clientid);
	    		
	    		foreach($ms13ids_data as $k_ms13s_d=> $v_ms13s_d)
	    		{
	    			$ms13s_details[$v_ms13s_d['id']] = $v_ms13s_d;
	    		}
	    
	    		$this->view->history_log = $history_log;
	    		$this->view->users = $doctorusers;
	    		$this->view->ms13s_details =  $ms13s_details;
	    	}
	    		
	    	$response['msg'] = "Success";
	    	$response['error'] = "";
	    	$response['callBack'] = "callback_history";
	    	$response['callBackParameters'] = array();
	    	$response['callBackParameters']['ms13id'] = $_REQUEST['prid'];//passthrough the receipt id
	    	$response['callBackParameters']['historylog'] = $this->view->render('ajax/loadmuster13log.html');
	    
	    	echo json_encode($response);
	    	exit;
	    }

	    public function contactformsymptomsAction(){
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid;
	    	
	    	// get all symptoms, ftom contact from id
	    	if(!empty($_REQUEST['ids']) && !empty($_REQUEST['id']) ){
	    		$cf_ids = $_REQUEST['ids'];
	    		$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_REQUEST['id']));
	    		$sym_blocks = FormBlockClientSymptoms::get_patients_form_block_ClientSymptoms($ipid,$cf_ids );

	    		if(!empty($sym_blocks)) {

	    			$client_symp_groups = ClientSymptomsGroups::get_client_symptoms_groups($clientid);
		    		$client_symps = ClientSymptoms::get_client_symptoms($clientid);
		    		
		    		foreach($sym_blocks[$ipid] as $cf_id=>$symp_data ){

		    			foreach($symp_data as $ko=>$sm_id){
		    				
							if(!is_array($return_data[$client_symps[$sm_id]['group_id']])){
								
								$return_data_extra[$client_symps[$sm_id]['group_id']] = array();
								
							}
							
// 							if(!in_array($sm_id,$return_data_extra[$client_symps[$sm_id]['group_id']])){
								
								$return_data[$client_symps[$sm_id]['group_id']] .= trim($client_symps[$sm_id]['description']).', '; 
								$return_data_arr[$client_symps[$sm_id]['group_id']][]= trim($client_symps[$sm_id]['description']); 

								$return_data_extra[$client_symps[$sm_id]['group_id']][] = $sm_id; 
// 							}
						}
		    		}
	    		}
	    	}
	    	
	    	if(!empty($return_data)){
	    		
		    	echo json_encode(array('text'=>$return_data, 'arr'=>$return_data_arr));
		    	
	    	} else {
	    		
		    	echo "0";
		    	
	    	}
	    	
	    	exit;
	    }
	    
	    public function getpatdiagnosisAction()
	    {
	    	$this->_helper->viewRenderer->setNoRender();
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$ipid = $_REQUEST['pid'];
	    	
	    	/* ---------------------------------------------------------- */
	    	
	    	$dg = new DiagnosisType();
	    	$abb2 = "'HD'";
	    	$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid, $abb2);
	    	$comma = ",";
	    	$typeid = "'0'";
	    	foreach($ddarr2 as $key => $valdia)
	    	{
	    		$typeid .=$comma . "'" . $valdia['id'] . "'";
	    		$comma = ",";
	    	}
	    	
	    	$patdia = new PatientDiagnosis();
	    	$dianoarray = $patdia->getFinalData($ipid, $typeid);
	   
	    	if(count($dianoarray) > 0)
	    	{
	    		foreach($dianoarray as $key => $valdia)
	    		{
	    			$pat_diagn[$key]['diagnosis'] = $valdia['diagnosis'];
	    			$pat_diagn[$key]['icdnumber'] = $valdia['icdnumber'];
	    		}
	    		$response = $pat_diagn;
	    	}
	    	else 
	    	{	    		
	    		$response = array('success'=>false,'msg'=>"no_data");
	    	}
	    	echo json_encode($response); 
	    	exit;
	    }
	    
	    
	    /**
	     * download one docx template
	     * Aug 9, 2017 @claudiu
	     *
	     */
	    public function docxtemplatedownloadAction()
	    {
	    	if ($this->getRequest()->isGet()) {
	    		$id = $this->getRequest()->getQuery('id');
	    		if( ! empty($id)) {
	    
	    			$logininfo = new Zend_Session_Namespace('Login_Info');
	    			$clientid = $logininfo->clientid;
	    
	    			$doc_obj = new DocxTemplates();
	    			$template = $doc_obj->getTemplate($clientid, $id);
	    
	    
	    			if( ! empty($template)) {
	    
	    				$fullPath = ! empty($template['fullPath']) ? $template['fullPath'] : DOCX_TEMPLATE_PATH . "/" . $template['clientid'] . "/" .$template['action'] . "/" .$template['file_name'];
	    				$file_nicename = $template['file_nicename'];
	    
	    				if( file_exists($fullPath))
	    				{
	    					$fsize = filesize($fullPath);
	    					$path_parts = pathinfo($fullPath);
	    	    	
	    					ob_end_clean();
	    					ob_start();
	    
	    					header('Content-Description: File Transfer');
	    					header("Content-type: application/octet-stream");
	    					header('Content-Transfer-Encoding: binary');
	    					header('Expires: 0');
	    					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    					header('Pragma: public');
	    					if($_COOKIE['mobile_ver'] != 'yes')
	    					{
	    						//if on mobile version don't send content-disposition to play nice with iPad
	    						header("Content-Disposition: attachment; filename=\"{$file_nicename}\"");
	    					}
	    
	    					header("Content-length: $fsize");
	    					header("Cache-control: private"); //use this to open files directly
	    					@readfile($fullPath);
	    					exit;
	    				} else {
	    					//who deleted this file from server?
	    					$response = array("result" => 'who deleted this file from server?');
	    				}
	    
	    			} else {
	    				$response = array("result" => 'why u try to download an wrong record?');
	    				//why u try to download an wrong record?
	    			}
	    
	    
	    		}
	    	}
	    
	    	$this->_helper->json->sendJson($response);
	    
	    
	    	exit;
	    }
	    
	    

	    public function diagsyncwidgetAction(){
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$decid = Pms_Uuid::decrypt($_REQUEST['id']);
	    	$ipid = Pms_CommonData::getIpid($decid);
	    
	    	$diags_arr=SystemsSyncPackets::get_diag_widget_data($ipid);
	    
	    	$diags=array();
	    
	    	if(count($diags_arr)>0) {
	    		$diags = json_decode($diags_arr[0]['payload'], true);
	    	}
	    
	    	$dt=new DiagnosisType();
	    	$types=$dt->getDiagnosisTypes($logininfo->clientid, "'hd', 'HD', 'nd', 'ND'");
	    	$type_to_typeid=array();
	    
	    	foreach ($types as $type){
	    		$type_to_typeid[$type['abbrevation']]=$type['id'];
	    	}
	    
	    	foreach ($diags['diags'] as $k=>$v){
	    		$diags['diags'][$k]['typeid'] = $type_to_typeid[$v['type']];
	    	}
	    
	    	$this->view->message="Dieser Diagnosesatz wurde bei der letzen Synchronisierung des Patienten mitgeschickt. Diese so übertragenen Diagnosen können nun übernommen werden.";
	    	$this->view->date=$diags['date'];
	    	$this->view->diags=$diags['diags'];
	    	$this->view->syncid=$diags_arr[0]['id'];
	    	$this->view->done=$diags_arr[0]['done'];
	    	$this->view->act_val=$diags['act'];
	    }
	    /*
	     * Mark a diag-packet from SystemsSyncPackets as done
	     */
	    public function marksyncdiagpacketdoneAction(){
	    	$this->_helper->viewRenderer->setNoRender();
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$syncid = intval($_REQUEST['syncid']);
	    	SystemsSyncPackets::get_diag_data($syncid,1);
	    	echo json_encode('OK');
	    }

	    
	    public function registertextsAction(){
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid; 
	    	
			if(strlen($_REQUEST['field_name'])){
			$rtl = new RegisterTextsList();
	    	$values = $rtl->get_client_list($clientid,$_REQUEST['field_name']);
	    	
	    	  if(!empty($values)){
	    	  	$this->view->returned_values = $values;
	    	  }
	    	
			}
	    }
	    
	    public function formstextsAction(){
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid; 
	    	
			if(strlen($_REQUEST['field_name']) ){
// 			if(strlen($_REQUEST['form_name']) && strlen($_REQUEST['field_name']) ){
			$rtl = new FormsTextsList();
	    	$values = $rtl->get_client_list($clientid,$_REQUEST['form_name'],$_REQUEST['field_name']);
	    	
	    	  if(!empty($values)){
	    	  	$this->view->returned_values = $values;
	    	  }
	    	
			}
	    }
	    
	    //Supplies (Hilfsmittel II) - ISPC-2077 
	    public function hilfsuppliesAction()
	    {
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    	$clientid = $logininfo->clientid;
	    	$search_string = addslashes(urldecode(trim($_REQUEST['q'])));
	    	
	    	$this->view->context = $this->getRequest()->getParam('context', '');
	    	$this->view->returnRowId = $this->getRequest()->getParam('row', '');
	    	$limit = $this->getRequest()->getParam('limit', 0);
	    	$limit = (int)$limit;
	    	
	    	$decid = Pms_Uuid::decrypt($_REQUEST['id']);
	    	$ipid = Pms_CommonData::getIpid($decid);
	    	
	    	if(strlen($_REQUEST['q']) > 0)
	    	{
	    		$drop = Doctrine_Query::create()
	    		->select('*')
	    		->from('Supplies')
	    		->where("(trim(lower(supplier)) like ? )  or (trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
	    		->andWhere('clientid = ?',$clientid)
	    		->andWhere("indrop = ?","0")
	    		->andWhere("isdelete = ?","0")
	    		->orderBy('last_name ASC');
	    		
	    		if ( ! empty($limit)) {
	    		    $drop->limit($limit);
	    		}
	    		
	    		$drop_arr = $drop->fetchArray();
	    		
	    		$drop_ps = Doctrine_Query::create()
	    		->select('*')
	    		->from('Supplies')
	    		->leftJoin('PatientSupplies')
	    		->where("(trim(lower(supplier)) like ? )  or (trim(lower(last_name)) like ? ) or (trim(lower(first_name)) like ? )",array("%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%","%".trim(mb_strtolower($search_string, 'UTF-8'))."%"))
	    		->andWhere("PatientSupplies.supplier_id = Supplies.id and PatientSupplies.ipid='" . $ipid . "' and PatientSupplies.isdelete = 0 ");
	    		
	    		if ( ! empty($limit)) {
	    		    $drop_ps->limit($limit);
	    		}
	    		
	    		$droparray_ps = $drop_ps->fetchArray();
	    		
	    		$drop_arr = array_merge($drop_arr, $droparray_ps);
	    		
	    		$sort_col = array();
	    		foreach ($drop_arr as $keyd=> $rowd) {
	    			$sort_col[$keyd] = $rowd['indrop'];
	    		}
	    		
	    		array_multisort($sort_col, SORT_DESC, $drop_arr);

	    		foreach($drop_arr as $key => $val)
	    		{
	    			$droparray[$key]['id'] = $val['id'];
	    			$droparray[$key]['supplier'] = html_entity_decode($val['supplier'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['salutation'] = html_entity_decode($val['salutation'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['street'] = html_entity_decode($val['street1'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['zip'] = html_entity_decode($val['zip'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['phone'] = html_entity_decode($val['phone'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['fax'] = html_entity_decode($val['fax'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['email'] = html_entity_decode($val['email'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['indrop'] = $val['indrop'];
	    			$droparray[$key]['logo'] = html_entity_decode($val['logo'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['comments'] = html_entity_decode($val['comments'], ENT_QUOTES, "utf-8");
	    			$droparray[$key]['supplier_row'] = $_REQUEST['row'];
	    		}
	    
	    		$this->view->droparray = $droparray;
	    	}
	    	else
	    	{
	    		$this->view->droparray = array();
	    	}
	    }
	    
	    

	    /**
	     * @claudiu 24.11.2017
	     * TODO: now is possible to extract patient zip+city from any customer... add a PatientPermissions check
	     */
	    public function createformcontactpersonAction()
	    {
	    
	        if ( ! $this->getRequest()->isXmlHttpRequest()) {
	            throw new Exception('!isXmlHttpRequest', 0);
	        }
	        $this->_helper->layout->setLayout('layout_ajax');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        $parent_form = $this->getRequest()->getParam('parent_form');
	        
	        $_block_name = $this->getRequest()->getParam('_block_name', null);
	        
	        $enc_id = $this->getRequest()->getParam('id');
	    
	        $_patientMasterData = null;
	        if ($enc_id) {
	            $patientmaster = new PatientMaster();
	            $patientmaster->getMasterData(Pms_Uuid::decrypt($enc_id));
	            $_patientMasterData=$patientmaster->get_patientMasterData();
	        }
	        
	        $af = new Application_Form_ContactPersonMaster(array(
	            "_patientMasterData"   => $_patientMasterData,
	            "_block_name"          => $_block_name
	        ));
	        
	        $contact_person_form = $af->create_form_contact_person(null, $parent_form. "[new_". uniqid(). "]");
	        
	        $this->getResponse()->setBody($contact_person_form)->sendResponse();
	    
	        exit;
	    }
	    
	    /**
	     * @claudiu 27.11.2017
	     */
	    public function createformpatientspcialistAction()
	    {
	    
	        if ( ! $this->getRequest()->isXmlHttpRequest()) {
	            throw new Exception('!isXmlHttpRequest', 0);
	        }
	        $this->_helper->layout->setLayout('layout_ajax');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        $parent_form = $this->getRequest()->getParam('parent_form');
	    
	        $_block_name = $this->getRequest()->getParam('_block_name', null);
	        
	        $af = new Application_Form_PatientSpecialist([
	            "_block_name"          => $_block_name
	        ]);
	        $contact_person_form = $af->create_form_specialist(null, $parent_form. "[new_". uniqid(). "]");
	    
	    
	        $this->getResponse()->setBody($contact_person_form)->sendResponse();
	    
	        exit;
	    }
	    
	    
	    /**
	     * @claudiu 27.11.2017
	     */
	    public function createformpatientpflegedienstAction()
	    {
	        if ( ! $this->getRequest()->isXmlHttpRequest()) {
	            throw new Exception('!isXmlHttpRequest', 0);
	        }
	        $this->_helper->layout->setLayout('layout_ajax');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        $parent_form = $this->getRequest()->getParam('parent_form');
	        
	        $_block_name = $this->getRequest()->getParam('_block_name', null);
	    
	        $af = new Application_Form_PatientPflegedienst([
	            "_block_name"          => $_block_name
	        ]);
	        $contact_person_form = $af->create_form_patient_pflegedienst(null, $parent_form. "[new_". uniqid(). "]");
	    
	    
	        $this->getResponse()->setBody($contact_person_form)->sendResponse();
	    
	        exit;
	    }
	    
	    /**
	     * @claudiu 27.11.2017
	     */
	    
	    public function createformdiagnosisrowAction()
	    {
	        if ( ! $this->getRequest()->isXmlHttpRequest()) {
	            throw new Exception('!isXmlHttpRequest', 0);
	        }
	        $this->_helper->layout->setLayout('layout_ajax');
	        $this->_helper->viewRenderer->setNoRender();
	         
	        $parent_form = $this->getRequest()->getParam('parent_form');
	        
	        $_block_name = $this->getRequest()->getParam('_block_name', null);
	         
	        $af = new Application_Form_PatientDiagnosis([
	            "_block_name"          => $_block_name
	        ]);
	        $row = $af->create_form_diagnosis_row(null, $parent_form. "[new_". uniqid(). "]");
	         
	         
	        $this->getResponse()->setBody($row)->sendResponse();
	         
	        exit;
	    }
	    
	    
	    /**
	     * @claudiu 14.05.2018
	     */
	     
	    public function createformprojectworkaddAction()
	    {
	        if ( ! $this->getRequest()->isXmlHttpRequest()) {
	            throw new Exception('!isXmlHttpRequest', 0);
	        }
	        $this->_helper->layout->setLayout('layout_ajax');
	        $this->_helper->viewRenderer->setNoRender();
	    
	        $parent_form = $this->getRequest()->getParam('parent_form');
	    
	        $_block_name = $this->getRequest()->getParam('_block_name', null);
	        
	        $af = new Application_Form_Projects();
	        $row = $af->create_form_add_project_work(null, $parent_form. "[new_". uniqid(). "]");
	    
	    
	        $this->getResponse()->setBody($row)->sendResponse();
	    
	        exit;
	    }
	    
	    /**
	     * 
	     * @Ancuta 29.05.2018
	     */
	    
	    public function createpatientppunAction(){
	        if ( ! $this->getRequest()->isXmlHttpRequest()) {
	            throw new Exception('!isXmlHttpRequest', 0);
	        }
	        $this->_helper->layout->setLayout('layout_ajax');
	        $this->_helper->viewRenderer->setNoRender();
	        

	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        
	        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
	        $ipid = Pms_CommonData::getIpid($decid);
	        
	        
	        
	        //get ppun (private patient unique number)
	        $ppun = new PpunIpid();
	        $ppun_number = $ppun->generate_patient_ppun($ipid, $clientid,false,false); // just retrive the next number - DO NOT SAVE

	        if ( ! empty($ppun_number)){
    	        $result['ppun'] = $ppun_number['ppun'];
	        } else{
    	        $result['ppun'] = "-1";
	        }
	        
	        
	        echo json_encode($result);
	        exit;
	    }
	    
	    
	    /**
	     * 
	     * @Ancuta 07.06.2018
	     * ISPC-2173
	     */
	    
	    
	    public function versorgerlivesearchAction()
	    {
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            $cat = $_GET['cat'];
            
            $vv = new Versorger();
            $this->view->data = $vv->getAddressbook($cat, $clientid, 25, $_GET['q']);
            
        }
        
        
        /**
         * @cla on 09.07.2018
         * 
         * @throws Exception
         */
        public function createformoutsideparticipantAction()
        {
            if ( ! $this->getRequest()->isXmlHttpRequest()) {
                throw new Exception('!isXmlHttpRequest', 0);
            }
            $this->_helper->layout->setLayout('layout_ajax');
            $this->_helper->viewRenderer->setNoRender();
             
            $parent_form = $this->getRequest()->getParam('parent_form', "project_outside_participants");
            
            $_block_name = $this->getRequest()->getParam('_block_name', null);
             
            $af = new Application_Form_Projects();
            $row = $af->create_form_add_project_outside_participants(null, $parent_form. "[new_". uniqid(). "]");
             
            $this->getResponse()->setBody($row)->sendResponse();
             
            exit;
        }
         
        
        /**
         * INFO : you must edit 
         * 
         * @cla on 09.07.2018
         * ISPC-2139
         * ajax for liveSearch of user and voluntaryworker
         * 
         * @cla on 01.04.2019
         * refactored name getuservwAction()
         * 
         * 
         * ISPC-2348
         * 
         * @throws Exception
         * @return boolean
         */
        public function getunifiedproviderAction()
        {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Exception('!isXmlHttpRequest', 0);
            }
            
            $this->_helper->layout->setLayout('layout_ajax');
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            $search_string = $this->getRequest()->getParam('q', null);
            
            $search_groups = $this->getRequest()->getParam('groups', []);
            
            $this->view->context = $this->getRequest()->getParam('context', '');
            $this->view->returnRowId = $this->getRequest()->getParam('row', '');
            $limit = $this->getRequest()->getParam('limit', 0);
            $limit = ! empty($limit) ? (int)$limit : 100;
            
            if (empty($search_string)){
                return false;
            }
             
            $groups = array(
                'user'              => null, 
                'voluntaryworker'   => null,
                'member'            => null,
            );

            if (in_array('user', $search_groups))
                $groups['user'] = User::livesearch_users($search_string, $clientid, false, $limit);
     
            if (in_array('voluntaryworker', $search_groups))
                $groups['voluntaryworker'] = Voluntaryworkers::livesearch_voluntaryworkers($search_string, $clientid, $limit);

            if (in_array('member', $search_groups))
                $groups['member'] = Member::livesearch_members($search_string, $clientid, false, $limit);
            
            $this->view->droparray = $groups;
            
            header("Content-type: text/html; charset=utf-8");
             
        }
        
    /**
     * @cla on 27.07.2018
     * ISPC-2198
     * 
     * !! multiple exit points
     */
    public function createformdgpkernAction()
    {
        
        $result = ['success' => false , 'message' => "add messages"];
        
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        $patid = $this->getRequest()->getPost('id', 0);
        $form_type = $this->getRequest()->getPost('form_type');
        $date = $this->getRequest()->getPost('date', date("Y-m-d"));
        
        // $form_blocks was not used TODO: if you add more blocks to this, create_form ony for them
        $form_blocks = $this->getRequest()->getPost('form_blocks'); 
        // also belongsTo is hardcoded, this was created just for wlasessment
        $belongsTo = $this->getRequest()->getPost('belongsTo'); 

        
        if (empty($patid) 
            || empty($form_blocks)
            || ($form_type != "adm" && $form_type != "dis"))
        {
            //fail-safe
            $result = ['success' => false , 'message' => "failed 1"];
            $this->_helper->json->sendJson($result);
            exit; //for read-adbility
        }
        
        
        $decid = Pms_Uuid::decrypt($patid);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $findReadmissionFromDate = PatientReadmission::findReadmissionFromDate($ipid, $date);
         
        $patient_readmission_ID = $form_type == 'adm' ? $findReadmissionFromDate['admission']['id'] : $findReadmissionFromDate['discharge']['id'];
        if (empty($patient_readmission_ID)) {
            //fail-safe
            $result = ['success' => false , 'message' => "failed 2"];
            $this->_helper->json->sendJson($result);
            exit; //for read-adbility
        }
        
        $savedDgpKern = Doctrine_Core::getTable('DgpKern')->findOneByIpidAndFormTypeAndPatientReadmissionId($ipid, $form_type, $patient_readmission_ID, Doctrine_Core::HYDRATE_ARRAY);
        if (empty($savedDgpKern)) {
            //fail-safe
            $result = ['success' => false , 'message' => "failed 3"];
            $this->_helper->json->sendJson($result);
            exit; //for read-adbility
        }

        $savedDgpKern['begleitung'] = explode(',', $savedDgpKern['begleitung']);
        
        $af_pdk = new Application_Form_PatientDgpKern();
        
        $subform_partners = $af_pdk->create_form_partners($savedDgpKern, '_page_1[PatientDgpKern]');
        $subform_partners->setAttrib('id', 'PatientDgpKern'); // this also hardcoded for wl
        
        
        $subform_ecog = $af_pdk->create_form_ecog($savedDgpKern, '_page_4[PatientDgpKern]');
        $subform_ecog->setAttrib('id', 'PatientDgpKern'); // this also hardcoded for wl
        
        
        $result = [
            'success' => true , 
            'form_blocks' => ['partners' => $subform_partners->render(), 'ecog' => $subform_ecog->render()]
        ];
        
        
        $this->getResponse()->setBody(Pms_CommonData::safe_json_encode($result))->sendResponse();
         
        exit;
    }  

    
    /**
     * ISPC-2220 30.07.2018
     * @Ancuta
     *
     * Changes made on 08.07.2019 - for TODO-2315
     * @Ancuta // Maria:: Migration ISPC to CISPC 08.08.2020
     * CHeck if partial invoices for selected period exist- if so show alert
     */
    
    public function  checkgenerateinvoicesAction(){
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        if(empty($_REQUEST['invoice_type'])){
            return;
        }
        
        $invoice_type = $_REQUEST['invoice_type'];
        
        $inv_period = array();
        if(!empty($_REQUEST['period_start']) && !empty($_REQUEST['period_end'])){
        
            $inv_period['start'] = date('Y-m-d',strtotime($_REQUEST['period_start']));
            $inv_period['end'] = date('Y-m-d',strtotime($_REQUEST['period_end']));
        }
        
        $period_completed="1";
        if(isset($_REQUEST['period_completed'])){
            $period_is_completed = $_REQUEST['period_completed'];
        }


        if(empty($inv_period)){
            return;
        }
        
        $all_generated_invoices = array();
        $ClientInvoices_obj = new ClientInvoices();
        
        
        
        $ipid = "";
        if( isset($_REQUEST['patient']) ){
            
            $epid = $_REQUEST['patient'];
            // get ipid of patient 
            $pat_ipid_data = Doctrine_Query::create()
            ->select('id, ipid')
            ->from('EpidIpidMapping')
            ->where('epid = ?', $epid )
            ->andWhere('clientid  = ?', $clientid )
            ->limit(1)
            ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
    
            if(empty($pat_ipid_data)){
                return;
            }
            $ipid = $pat_ipid_data['ipid'];
            




            if($_REQUEST['period_type'] == "admission"){
                // check if patient is discharged
                //get patient admissions

                $drop = Doctrine_Query::create()
                ->select('id,date,date_type,ipid')
                ->from('PatientReadmission')
                ->where('ipid = ?', $ipid)
                ->orderBy('date DESC')
                ;
                $droparray = $drop->fetchArray();
            }



            // TODO-2315: Commented on 08.07.2019 - gett all invoices  of patient -  not only for the exact period
            // $generated_invoices = $ClientInvoices_obj->getall_generated_invoices(array($ipid), $clientid, array($invoice_type),$inv_period,false,false);
            $all_generated_invoices = $ClientInvoices_obj->getall_generated_invoices(array($ipid), $clientid, array($invoice_type),false,false,false);
        }
        

        $userid = "";
        if( isset($_REQUEST['userid']) && $invoice_type != 'sh_shifts_internal_invoice' ){
            $userid = $_REQUEST['userid'];
            // TODO-2315: Commented on 08.07.2019 - gett all invoices  of patient -  not only for the exact period
            // $generated_invoices = $ClientInvoices_obj->getall_generated_invoices(array(), $clientid, array($invoice_type),$inv_period,false,false,array($userid));
            $all_generated_invoices = $ClientInvoices_obj->getall_generated_invoices(array(), $clientid, array($invoice_type),false,false,false,array($userid));
        }



        // TODO-2315 - Create array for completed and partial invoices
        $generated_invoices= array();
        $partially_generated_invoices= array();
        if(!empty($all_generated_invoices)){
            foreach($all_generated_invoices as $k => $invoice_vals){

                // TODO-2905 Lore 02.03.2020
                // 'bw_sapv_invoice' => 'bw_invoice',
                // 'bw_sgbv_invoice' => 'sgbv_invoice',
                // 'bw_mp_invoice' => 'medipumps_invoice',
                // 'bw_sapv_invoice_new' => 'bw_invoice_new',// TODO-2975 Loredana 09.03.2020

                if($invoice_vals['inv_type'] == 'sgbv_invoice'){
                    $invoice_vals['inv_type'] = 'bw_sgbv_invoice';
                }
                if($invoice_vals['inv_type'] == 'bw_invoice'){
                    $invoice_vals['inv_type'] = 'bw_sapv_invoice';
                }
                if($invoice_vals['inv_type'] == 'medipumps_invoice'){
                    $invoice_vals['inv_type'] = 'bw_mp_invoice';
                }
                // TODO-2975 Loredana 09.03.2020
                if($invoice_vals['inv_type'] == 'bw_invoice_new'){
                    $invoice_vals['inv_type'] = 'bw_sapv_invoice_new';
                }
                if($invoice_vals['inv_type'] == 'medipumps_invoice_new'){
                    $invoice_vals['inv_type'] = 'bw_medipumps_invoice';
                }
                if($invoice_vals['inv_type'] == 'hi_invoice'){
                    $invoice_vals['inv_type'] = 'nie_patient_invoice';
                }
                //.
                //dd($invoice_vals['inv_type'] , $invoice_type);
                if($invoice_vals['inv_type'] == $invoice_type){

                    if( date("Y-m-d",strtotime($invoice_vals['invoice_start'])) ==  $inv_period['start'] && date("Y-m-d",strtotime($invoice_vals['invoice_end'])) ==  $inv_period['end']){
                        $generated_invoices[] = $invoice_vals;
                    } elseif (Pms_CommonData::isintersected(date("Y-m-d",strtotime($invoice_vals['invoice_start'])), date("Y-m-d",strtotime($invoice_vals['invoice_end'])),date("Y-m-d",strtotime($inv_period['start'])),  date("Y-m-d",strtotime($inv_period['start']))) ){
                        $partially_generated_invoices[] = $invoice_vals;
                    }
                }
            }
        }
        
        $invoices = array();
        $result['skip_modal'] =  0; 
        $result['partial_invoices2period']= 0;
        if(empty($generated_invoices) && empty($partially_generated_invoices)){
            $result['skip_modal']  = 1;
        }
        // TODO-2315 - Show modal if partial invoices exist
        elseif(empty($generated_invoices) && !empty($partially_generated_invoices)){
            $result['skip_modal']  = 0;

            $result['partial_invoices2period']=1;
            foreach($partially_generated_invoices as $k=>$pinv){
                $invoices[] = $pinv['id'];
            }

            if(!empty($invoices)){
                $result['invoices']  = $invoices;
            }

        }
        else
        {
            $result['skip_modal']  = 0;
            $storno_invoices = array();
            foreach($generated_invoices as $k=>$inv){
                if($inv['storno']=="1"){
                    $storno_invoices[] = $inv['record_id'];
                }
            }
            // TODO-2315 Added status 3 by Ancuta 17.05.2019
            foreach($generated_invoices as $k=>$inv){
                if(in_array($inv['status'],array("2","5","3"))
                    && $inv['storno']!="1"
                    && !in_array($inv['id'],$storno_invoices)
                    ){
                    $completed_invoices[] = $inv['id'];
                } else if($inv['status'] =="1"){
                    $draft_invoices[] = $inv['id'];
                }
                $invoices[] = $inv['id'];
            }

            
            if(!empty($completed_invoices)){
                $result['completed_invoices']  = $invoices;
            }
            
            
            if(!empty($draft_invoices)){
                $result['invoices']  = $draft_invoices;
            }
        }
        
        $result['period_is_completed'] = $period_is_completed;
//          dd($_REQUEST,$result);
        echo json_encode($result);
        exit;
    }
    
    /**
     * ISPC-2220 30.07.2018
     * @Ancuta
     */
    
    
    public function  deletedraftinvoicesAction(){
        
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        if(empty($_REQUEST['invoice_type']) || empty($_REQUEST['invoices'])){
            return;
        }
        
        $invoice_type = $_REQUEST['invoice_type'];
        $invoices = $_REQUEST['invoices'];
        $generated_invoices = array();
        $ClientInvoices_obj = new ClientInvoices();
        
        $ipid = "";
        if(isset($_REQUEST['patient'])){
                
            $epid = $_REQUEST['patient'];
            // get ipid of patient 
            $pat_ipid_data = Doctrine_Query::create()
            ->select('id, ipid')
            ->from('EpidIpidMapping')
            ->where('epid = ?', $epid )
            ->andWhere('clientid  = ?', $clientid )
            ->limit(1)
            ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
    
            if(empty($pat_ipid_data)){
                return;
            }
            $ipid = $pat_ipid_data['ipid'];
            
            $delete_invoices = $ClientInvoices_obj->delete_drafts(array($ipid), $clientid, $invoices, $invoice_type);
        }
        
        
        $userid = "";
        if(isset($_REQUEST['userid'])){
            $userid = $_REQUEST['userid'];
            $delete_invoices = $ClientInvoices_obj->delete_drafts(array(), $clientid, $invoices, $invoice_type, array($userid ));
        }
        
        if($delete_invoices){
            $result['invoice_deleted'] = 1;
        } 
        else 
        {
            $result['invoice_deleted'] = 0;
            
        }    
        
        echo json_encode($result);
        exit;
    }
    
    //ISPC - 2261 - create 3 icon in patient for teammeeting aktuelle problem content
    public function savecurrentproblemAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->setLayout('layout_ajax');
    
    	$decid = Pms_Uuid::decrypt($_REQUEST['pid']);
    	$ipid = Pms_CommonData::getIpid($decid);
    	$pcp = new PatientCurrentProblems();
    	
    	if($_REQUEST['pid'] && $ipid)
    	{
    		$content = nl2br($_POST['content']);
    		
    		switch ($_REQUEST['fieldname'])
    		{
    			case 'measure':
    				$pat_icon_data = $pcp->findOneByIpidandIcon($ipid, 'measure');
    				if($pat_icon_data)
    				{
    					$pcprec = Doctrine::getTable('PatientCurrentProblems')->find($pat_icon_data['id']);    				
	    				$pcprec->isdelete = 1;
	    				$pcprec->save();
    				}
            		
    				if($content != '')
    				{
    					$pcp = new PatientCurrentProblems();
    					$pcp->ipid = $ipid;
    					$pcp->icon = "measure";
    					$pcp->problem = addslashes(strip_tags($content, '<br>'));
    					$pcp->save();
    				}
    			break;
    			case 'current_situation':
    				$pat_icon_data = $pcp->findOneByIpidandIcon($ipid, 'current_situation');
    				if($pat_icon_data)
    				{
    					$pcprec = Doctrine::getTable('PatientCurrentProblems')->find($pat_icon_data['id']);    				
	    				$pcprec->isdelete = 1;
	    				$pcprec->save();
    				}
    				
    				if($content != '')
    				{
	    				$pcp = new PatientCurrentProblems();
	    				$pcp->ipid = $ipid;
	    				$pcp->icon = "current_situation";
	    				$pcp->problem = addslashes(strip_tags($content, '<br>'));
	    				$pcp->save();
    				}
    			break;
    			case 'sapv_appl':
    				$pat_icon_data = $pcp->findOneByIpidandIcon($ipid, 'sapv_appl');
    				if($pat_icon_data)
    				{
    					$pcprec = Doctrine::getTable('PatientCurrentProblems')->find($pat_icon_data['id']);    				
	    				$pcprec->isdelete = 1;
	    				$pcprec->save();
    				}
    				
    				if($content != '')
    				{
	    				$pcp = new PatientCurrentProblems();
	    				$pcp->ipid = $ipid;
	    				$pcp->icon = "sapv_appl";
	    				$pcp->problem = addslashes(strip_tags(trim($content), '<br>'));
	    				$pcp->save();
    				}
    			break;
    			//TODO-3707 Lore 06.01.2021
    			case 'ventilation':
    			    $pat_icon_data = $pcp->findOneByIpidandIcon($ipid, 'ventilation');
    			    if($pat_icon_data)
    			    {
    			        $pcprec = Doctrine::getTable('PatientCurrentProblems')->find($pat_icon_data['id']);
    			        $pcprec->isdelete = 1;
    			        $pcprec->save();
    			    }
    			    
    			    if($content != '')
    			    {
    			        $pcp = new PatientCurrentProblems();
    			        $pcp->ipid = $ipid;
    			        $pcp->icon = "ventilation";
    			        $pcp->problem = addslashes(strip_tags($content, '<br>'));
    			        $pcp->save();
    			    }
    			break;
    			//.
    			default:
    			break;
    		}
    	}
    	
    	exit;
    }
        

    /**
     * @cla on 10.12.2018
     * @throws Exception
     */
    public function createformhospitalizationsrowAction()
    {
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
    
        $parent_form = $this->getRequest()->getParam('parent_form');
         
        $_block_name = $this->getRequest()->getParam('_block_name', null);
    
        $af = new Application_Form_PatientRegularChecks([
            "_block_name"          => $_block_name
        ]);
        $row = $af->create_form_hospitalizations_row(null, $parent_form. "[hospitalizations][new_". uniqid(). "]");
    
    
        $this->getResponse()->setBody($row)->sendResponse();
    
        exit;
    }	
    
    
    /**
     * @cla on 06.01.2018
     * One ring to rule them all and in the darkness bind them
     * 
     * available methods :
     * user
     * voluntaryworker
     * 
     * result[user] =[ $item = [
                        'type'          => 'user', -> this must be the same as the method
                        'id'            => $item['id'],  -> this is used                      
                        'nice_name'     => $item['nice_name'], -> this is used
                        
                        'first_name'    => $item['first_name'],
                        'last_name'     => $item['last_name'],
                        
                         not implemented, prefix ans suffix to create the label
                        'prefix'        => '',
                        'suffix'        => '',
                        
                    ], ..]
     * 
     */
    public function autocompleteAction() 
    {
        $result = [];
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        $search_string = $this->getRequest()->getParam('q', null);
        $methods = $this->getRequest()->getParam('methods', []);
        $limit = intval($this->getRequest()->getParam('limit', 100));
        

        
        
//         $this->view->context = $this->getRequest()->getParam('context', '');
//         $this->view->returnRowId = $this->getRequest()->getParam('row', '');
        

        if (empty($search_string)){
            $this->_helper->json->sendJson($response);
            exit;
        }
        
        
        /*
         * search for manual hardcoded values in the data-autocomplete_manual
         */
        if (is_array($methods) && in_array('manual', $methods))
        {
            $autocomplete_manual = $this->getRequest()->getParam('autocomplete_manual', null);
            
            if (! is_null($autocomplete_manual) && is_array($autocomplete_manual)) 
            {
                
                $result['manual'] = array_filter($autocomplete_manual, function($val) use (&$search_string) { return stripos($val, $search_string)!==false;});
                
                if (is_array($result['manual'])) {
                    array_walk($result['manual'], function(&$item){
                        $item = [
                            'type'          => 'manual',
                            'id'            => null,
                            'nice_name'     => $item,
                            'first_name'    => null,
                            'last_name'     => null,
                            'prefix'        => null,
                            'suffix'        => null,
                
                        ];
                    });
                } else {
                    $result['manual'] = [];
                }
            }
        }
        
        
        /*
         * search for User
         */
        if (is_array($methods) && in_array('user', $methods)) 
        {
            $result['user'] = User::livesearch_users($search_string, $clientid, false, $limit);
            
            if (is_array($result['user'])) {
                array_walk($result['user'], function(&$item){
                    $item = [
                        'type'          => 'user',
                        'id'            => $item['id'],                        
                        'nice_name'     => $item['nice_name'],
                        'first_name'    => $item['first_name'],
                        'last_name'     => $item['last_name'],
                        'prefix'        => '',
                        'suffix'        => '',
                        
                    ];
                });
            } else {
                $result['user'] = [];
            }
        }
        
        
        /*
         * search for Voluntaryworkers
         */
        if (is_array($methods) && in_array('voluntaryworker', $methods)) 
        {
            $result['voluntaryworker'] = Voluntaryworkers::livesearch_voluntaryworkers($search_string, $clientid, $limit);
            
            if (is_array($result['voluntaryworker'])) {
                array_walk($result['voluntaryworker'], function(&$item){
                    $item = [
                        'type'          => 'voluntaryworker',
                        'id'            => $item['id'],
                        'nice_name'     => $item['nice_name'],
                        'first_name'    => $item['first_name'],
                        'last_name'     => $item['last_name'],
                        'prefix'        => '',
                        'suffix'        => '',
                    ];
                });
            } else {
                $result['voluntaryworker'] = [];
            }
        }
        
        
        Zend_Json::$useBuiltinEncoderDecoder = true; 
        //Zend_Json::encode();
        
        
        $this->_helper->json->sendJson($result);
        
    }
    

    /**
     * @cla on 10.12.2018
     * @throws Exception
     */
    public function createformassessmentoneproblemrowAction()
    {
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
    
        $parent_form = $this->getRequest()->getParam('parent_form');
         
        $_block_name = $this->getRequest()->getParam('_block_name', null);
    
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        if (empty($decid)) {
            return '';
        }
        
        $ipid = Pms_CommonData::getIpid($decid);
        if (empty($ipid)) {
            return '';
        }
        // Maria:: Migration ISPC to CISPC 08.08.2020
        //ISPC-2293 Carmen 02.06.2020
        /* $lastAssessmentId = MamboAssessmentTable::getInstance()->createQuery('fnd')
        ->select('id')
        ->where('ipid = :ipid')
        ->orderBy('id DESC')
        ->limit(1)
        ->fetchOne([
            'ipid' => $ipid
        ], Doctrine_Core::HYDRATE_ARRAY);
        if (empty($lastAssessmentId) || empty($lastAssessmentId['id'])) {
            return '';
        } */
        //--
        
        $af = new Application_Form_AssessmentProblems([
            "_block_name"          => $_block_name
        ]);
        $rows = $af->create_one_problem_rows(['id' => $parent_form. "[new_". uniqid(). "]" , "assessment_id" => $lastAssessmentId['id']]);
    
        $subform = new Zend_Form_SubForm();
        $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
        $subform->setDecorators(array('FormElements'));        
        $subform->addSubForms($rows);
        
        $this->getResponse()->setBody($subform)->sendResponse();
    
        exit;
    }
    
    /**
     * this action is NOT related with model Notifications
     * 
     */
    public function formseditmodeajaxAction()
    {
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $this->logininfo = new Zend_Session_Namespace('Login_Info');
        
        
        $post = $this->getRequest()->getPost();
        $post['pid'] =  empty($post['pid']) ? null : Pms_Uuid::decrypt($post['pid']);
        $post['cid'] =  empty($post['cid']) ? null : Pms_Uuid::decrypt($post['cid']);
        $post['search'] =  empty($post['search']) ? null : $post['search'];

        $result = [
            'result' => true,
        
            //TODO : this should be notifications[] .. so we can have multiple distinct forms in the same page
            'notification' => [
                '__id'          => null, // not used
                '__change_date' => null, // we update display message only if this changes bethween requests
        
                'type'      => null, // info, success, warning, danger
                'icon'      => '',
                'title'     => null, // be aware this html is NOT escaped
                'message'   => null, // be aware this html is NOT escaped
                'autohide'  => false,
                'url'       => null,
                'target'    => null,
            ],
        
            '__debug' => APPLICATION_ENV == 'development' ? $post : null,
        ];
        
        
        
        $rObj = null;
        
        switch ($post['__action']) {
            
            case "gracefulEditor":
            case "overwriteEditor":
                
                $rObj = FormsEditmodeTable::getInstance()->findOrCreateOneBy(
                    ['pathname', 'client_id', 'patient_master_id', 'search'],
                    [$post['pathname'], $this->logininfo->clientid, $post['pid'], $post['search']],
                    array_merge($post , ['is_edited' => 'yes', 'user_id' => $this->logininfo->userid])
                );
                
                break;
            
            case "closeEditor":
                
                if ($rObj = FormsEditmodeTable::getInstance()->findOrCreateOneBy(
                    ['pathname', 'client_id', 'patient_master_id', 'search', 'is_edited'],
                    [$post['pathname'], $this->logininfo->clientid, $post['pid'], $post['search'], 'yes'],
                    $post)) 
                {
                    $rObj->delete();
                    $rObj = null;
                }
                
                $post['closeEditor'] = 'closeEditorcloseEditorcloseEditorcloseEditorcloseEditorcloseEditor';
                
                break;
        }
        
        

        $translator = $this->getInvokeArg('bootstrap')->getResource('Translate');
        
        
        
        if ($rObj || ($rObj = FormsEditmodeTable::getInstance()->createIfNotExistsOneBy(
            ['pathname', 'client_id', 'patient_master_id', 'search'],
            [$post['pathname'], $this->logininfo->clientid, $post['pid'], $post['search']],
            $post
        ))) {            
            
            if (empty($rObj->user_id)) {
                //'no one is editing this form now, do you want to become the editor ?';
                $result['notification'] = [
                    '__id'          => $rObj->id,
                    '__change_date' => ! empty($rObj->change_date) ? $rObj->change_date : $rObj->create_date,
                    
                    'type'      => 'warning',
                    'autohide'  => false,
                    'icon'      => '',
                    
                    'title'     => $translator->translate("Become formular's editor"),
                    
                    'message'   => "<span class='message_text'>"
                    .$translator->translate("No one is checked-in as this formular's editor")
                    . "<br/>"
                    . $translator->translate('By doing so, if someone else wants to edit this formular, he will be informed that you are editing it')
                    . "</span><span class='message_button'>"
                    . (new Zend_Form_Element_Button('deedeepushthebutton', [
                        'decorators' => ['ViewHelper'],
                        'name'     =>'deedeepushthebutton',
                        'label'    => "Check-In as formular editor",
                        'class'    => 'btnSubmit2018 dontPrint forms_editmode_button',
                        'onClick'  => "javascript:$('#{$post['__element_id']}').checkFormularEditmode('gracefulEditor'); return false;"
                    ]))->render()
                    . "</span>",
                    
                ];
                
            } elseif ($rObj->user_id == $this->logininfo->userid) {
                
                //'let someone else take your spot and become the editor';
                
                $result['notification'] = [
                    '__id'          => $rObj->id,
                    '__change_date' => ! empty($rObj->change_date) ? $rObj->change_date : $rObj->create_date,
                    
                    'type'      => 'success',
                    'autohide'  => false,
                    'icon'      => '',

                    'title'     => $translator->translate("You are checked-in as editor"),
                    
                    'message'   => "<span class='message_text'>"  
                    . $translator->translate('so you let someone else become the editor, without saving the form')
                    . "</span><span class='message_button'>"
                    . (new Zend_Form_Element_Button('deedeepushthebutton', [
                        'decorators' => ['ViewHelper'],
                        'name'     =>'deedeepushthebutton',
                        'label'    => "Check-Out as formular editor",
                        'class'    => 'btnSubmit2018 dontPrint forms_editmode_button',
                        'onClick'  => "javascript:$('#{$post['__element_id']}').checkFormularEditmode('closeEditor'); return false;"
                    ]))->render()
                    . "</span><span class='message_button'>"
                   ,
                    
                ];
                
            } else {
                
                //do you want to overwrite the current editor and you become the new editor?
                
                $niceUsers = User::getUsersNiceName([$rObj->user_id]);
                $editingUser = $niceUsers[$rObj->user_id];
              
                
                $result['notification'] = [
                    '__id'          => $rObj->id,
                    '__change_date' => ! empty($rObj->change_date) ? $rObj->change_date : $rObj->create_date,
                    
                    'type'      => 'danger',
                    'icon'      => '',
                    'autohide'  => false,
                    
                    
                    'title'     => ! empty($editingUser) 
                    ? sprintf($translator->translate("User %s is editing the formular, since %s") , $editingUser['nice_name'], (empty($rObj->change_date) ? date('d.m.Y H:i', strtotime($rObj->create_date)) : date('d.m.Y H:i', strtotime($rObj->change_date)))) 
                    : sprintf($translator->translate("Another user is editing the formular, since %s"), (empty($rObj->change_date) ? date('d.m.Y H:i', strtotime($rObj->create_date)) : date('d.m.Y H:i', strtotime($rObj->change_date)))),
                    
                    'message'   => "<span class='message_text'>"
                    . $translator->translate('You can take over the editing. Maybe the other user forgot to check out. Attention: It can happen that the work of the other user is lost.')
                    . "</span><span class='message_button'>"
                    . (new Zend_Form_Element_Button('deedeepushthebutton', [
                        'decorators' => ['ViewHelper'],
                        'name'     =>'deedeepushthebutton',
                        'label'    => "Check-In Overwrite Editor",
                        'class'    => 'btnSubmit2018 dontPrint forms_editmode_button',
                        'onClick'  => "javascript:$('#{$post['__element_id']}').checkFormularEditmode('overwriteEditor'); return false;"
                    ]))->render()
                    . "</span>"
                    ,
                    
                ];
            }
            
        } else {
           //mother of horrors
        }
        
        
        
        
        Zend_Json::$useBuiltinEncoderDecoder = true; 
        //Zend_Json::encode();
        
        
        $this->_helper->json->sendJson($result);
        
    }
    
    /**
     * @carmen 27.03.2019
     */
    public function createformactivateyesshortcutvclientsettingsAction()
    {
    	 
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	$parent_form = $this->getRequest()->getParam('parent_form');
    	 
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    	
    	$options = json_decode($this->getRequest()->getParam('activateyes_settings'), true);
    	
    	$af = new Application_Form_Client([
    			"_block_name"          => $_block_name
    	]);
    	$activateshortcut_form = $af->create_form_activate_shortcut_yes_settings($options, $parent_form);
    	 
    	 
    	$this->getResponse()->setBody($activateshortcut_form)->sendResponse();
    	 
    	exit;
    }

    /**
     * @carmen 16.09.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
     */

    public function createformblockcustomitemrowAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();

    	$parent_form = $this->getRequest()->getParam('parent_form');

    	$_block_name = $this->getRequest()->getParam('_block_name', null);

    	$af = new Application_Form_FormBlockCustomItems();
    	$row = $af->create_form_formblockcustomitem_row(null, $parent_form. "[new_". uniqid(). "]");

    	$this->getResponse()->setBody($row)->sendResponse();

    	exit;
    }



    /**
     * @author Ancuta 21.11.2019
     * ISPC-2452
     * @throws Exception
     *
     */
    public function createhidebitornumberAction(){
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $hi = new HealthInsurance();
        $ppun_number = $hi->generate_hi_debitor_number($clientid,false,false); // just retrive the next number - DO NOT SAVE

        if ( ! empty($ppun_number)){
            $result['hi_debitor_number'] = $ppun_number['hi_debitor_number'];
        } else{
            $result['hi_debitor_number'] = "-1";
        }


        echo json_encode($result);
        exit;
    }

    /**
     * ISPC-2432 Ancuta 21.01.2020
     * @author Ancuta 21.01.2020
     * @throws Exception
     */

    public function activatedeviceAction(){
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();


        $decid = Pms_Uuid::decrypt($_REQUEST['pid']);
        $ipid = Pms_CommonData::getIpid($decid);

        $result = array();

        switch ($_REQUEST['__action']){
            case 'activateDevice':
                if($ipid && !empty($_REQUEST['device_id']) && !empty($_REQUEST['activation_code']) && !empty($_REQUEST['device_password']))
                {
                    // validate activation code
                    $device_id = $_REQUEST['device_id'];
                    $activation_code = $_REQUEST['activation_code'];


                    $valide_code = false;
                    $me_patientDevices = new Application_Form_MePatientDevices ();

                    $qr_id =  $_REQUEST['qr_identifier'];
                    $device_internal_id =  $_REQUEST['device_internal_id'];
                    $program =  $_REQUEST['program'];

                    $password =  $_REQUEST['device_password'];
                    $authcode = substr(md5($qr_id.$device_internal_id.$program.$password.date('d.m.Y')),0,8);
//                     dd($authcode);
                    //3434343434fF
                    //$valide_code = $me_patientDevices->validateDeviceCode($activation_code) ;
                    if($activation_code == $authcode ){
                        $valide_code = true;
                    }
                    //check if qr code stil active
                    $qr_data = Doctrine_Query::create()
                    ->select('*')
                    ->from('MePatientQrCodes')
                    ->where('qr_identifier = ?', $qr_id )
                    ->limit(1)
                    ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                    $Qr_code = 'inactive';
                    if(!empty($qr_data) &&  strtotime($qr_data['expiration_date']) > strtotime() ){
                        $Qr_code = 'active';
                    }

                    if($valide_code === true && $Qr_code == 'active') {

                        //Activate device
                        $device_obj = Doctrine::getTable('MePatientDevices')->find($device_id);
                        if($device_obj){
                            $device_obj->isdelete = 0;
                            $device_obj->active= 'yes';
                            $device_obj->activation_date = date('Y-m-d H:i:s');
                            $device_obj->device_password = $_REQUEST['device_password'];
                            $device_obj->save();

                            //update qr code - marck as used
                            $device_qrj = Doctrine::getTable('MePatientQrCodes')->findOneBy('qr_identifier',$qr_id);
                            if($device_qrj){
                                $device_qrj->used= 'yes';
                                $device_qrj->save();
                            }

                            //send devices to proxiserver
                            MePatientDevices::cronjob_mePatient_sendDevices_to_servers();

                            $result['action'] = $_REQUEST['__action'];
                            $result['success'] = true;
                            $result['msg'] = 'Device, activated';

                            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/mepatient_devices.log');
                            $log = new Zend_Log($writer);
                            $log->info(' Device '.$device_internal_id.' was activated.');
                        }
                    } else{
                        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/mepatient_devices.log');
                        $log = new Zend_Log($writer);
                        if($valide_code === true ){
                            $acv_msg = 'valid';
                        } else{
                            $acv_msg = 'invalid';
                            $alert_msg = 'Activation code not valid';
                        }
                        $log->info(' Device '.$device_internal_id.' was not activated.Qr ident: '.$qr_id.' Activation code:'.$acv_msg.'. Qr status:'.$Qr_code);

                        $result['success'] = false;
                        $result['msg'] = 'Device, NOT activated'.$alert_msg;
                    }
                }

                break;

            default:
                break;
        }



        echo json_encode($result);
        exit;

    }
    /**
     * @author Ancuta
     * ISPC-2432
     * @throws Exception
     *
     * Changes: Date 12.02.2020
     * I) ISPC: add a verlauf entry for every PUSH sent in the same verlauf shortcut as "e"
     * I1) Verlauf entry for PUSH NOW 'A push message' message text" was sent" -> "Eine Push Nachricht mit dem Inhalt 'MESSAGE TEXT' wurde versendet."
     *
     * Changes: Date 12.02.2020
     * Send notification $pp_device_data to proxy using  MePatientDevicesNotifications::mePatient_sendNotification_to_servers
     */
    public function sendpushnotificationAction(){
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();


        $decid = Pms_Uuid::decrypt($_REQUEST['pid']);
        $ipid = Pms_CommonData::getIpid($decid);

        // Ancuta: Changes applied on 12.02.2020
        $modules = new Modules();
        if($modules->checkModulePrivileges("215", $this->logininfo->clientid))//specific ligetis labels
        {
            $mePatient_labels = Pms_CommonData::mePatientIdentification('ligetis');
        }
        else
        {
            $mePatient_labels = Pms_CommonData::mePatientIdentification('default');
        }
        //--

        $result = array();

        switch ($_REQUEST['__action']){
            case 'sendPushNow':
                if( $ipid && !empty($_REQUEST['push_comment']))
                {

                    // get all ACTIVE devices of pateint and send notification for all
                    $devices_arr = array();
                    $devices_arr = MePatientDevicesTable::find_patient_devices($ipid,true,true);

                    foreach( $devices_arr as $kd=> $device_data ) {

                        $data = array();
                        $data['ipid'] = $ipid;
                        $data['device_id'] = $device_data['id'];
                        $data['device_internal_id'] = $device_data['device_internal_id'];
                        $data['notification_text'] = $_REQUEST['push_comment'];
                        $data['registration_id'] = $device_data['registration_id'];
                        // add to history
                        $notification_history = new MePatientNotificationsHistory();
                        $notification_history->ipid = $data['ipid'];
                        $notification_history->device_id = $data['device_id'];
                        $notification_history->notification_type = 'send_now';
                        $notification_history->notification_text = $data['notification_text'] ;
                        $notification_history->save();

                        // send to device
                        if($notification_history->id){

                            //send to device
                            $data['notification_id'] = $notification_history->id;
                            //$device_response = MePatientDevices::sendPush2device($data);

                            if(! empty($data['registration_id']) && ! empty($data['notification_text'])){
                                $mePatient = new Pms_mePatient();
                                // MEP-151 Ancuta 13.07.2020
                                //$device_response_json  = $mePatient->push_notification($data['registration_id'],$data['notification_text']);
                                $device_response_json  = $mePatient->push_notification($data['registration_id'],$data['notification_text'],null,$data['notification_id']);
                                //--

                                // send notification to proxy
                                $pp_device_data = array();
                                $pp_device_data['device'] = $device_data['device_internal_id'];
                                $pp_device_data['type'] = 'send_now';
                                $pp_device_data['text'] = $data['notification_text'];
                                $pp_device_data['notification_id'] = $data['notification_id'];
                                $pp_device_data['date'] = date("Y-m-d H:i:s");
                                MePatientDevicesNotifications::mePatient_sendNotification_to_servers($pp_device_data);


                                //Add to verlauf
                                $comment = str_replace('%message%', $data['notification_text'], $mePatient_labels['notifications_pushNow']['course_entry']);

                                $custcourse = new PatientCourse();
                                $custcourse->ipid = $ipid;
                                $custcourse->course_date = date("Y-m-d H:i:s", time());
                                $custcourse->course_type = Pms_CommonData::aesEncrypt($mePatient_labels['notifications_pushNow']['course_shortcut']);
                                $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
                                $custcourse->user_id = $this->logininfo->userid;
                                $custcourse->recordid = $notification_history->id;
                                $custcourse->tabname = Pms_CommonData::aesEncrypt('mePatient_notification_pushNow');
                                $custcourse->save();

                            }
                            $device_response = array();
                            if(!empty($device_response_json)){
                                $device_response = json_decode($device_response_json,true);
                            }
                            // update history
                            if($device_response ){
                                $NotificationsHistory_obj = Doctrine::getTable('MePatientNotificationsHistory')->findOneBy('id',$data['notification_id']);
                                if($NotificationsHistory_obj){
                                    $NotificationsHistory_obj->message_ack= $device_response_json;
                                    $NotificationsHistory_obj->send_ok = ($device_response['success'] == '1') ? 'yes' : 'no' ;
                                    $NotificationsHistory_obj->save();
                                    $result['success'][] = $device_response['success'];
                                }
                            }
                        }

                    }

                }

                break;

            default:
                break;
        }

        echo json_encode($result);
        exit;

    }



    /**
     * ISPC-2508 Carmen 23.01.2020
     *  Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
     */
    public function setpatientartificialsettingAction(){
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    
    	if(!empty($_REQUEST['artset_id']) && !empty($_REQUEST['id'])){
    		$decid = Pms_Uuid::decrypt($_REQUEST['id']);
    		$ipid = Pms_CommonData::getIpId($decid);
    		$artset_id = $_REQUEST['artset_id'];
    		$action = $_REQUEST['action'];
    
    		$entity = PatientArtificialEntriesExitsTable::getInstance()->find($artset_id);
    
    		if($entity)
    		{
    			switch($action) {
    				case 'remove':
    					$entity->isremove = 1;
    					$entity->remove_date = date('Y-m-d H:i:s', time());
    					$entity->save();
    					break;
    				case 'refresh':
    					//remove the entity and create a new one starting now
    					$entity->isremove = 1;
    					$entity->remove_date = date('Y-m-d H:i:s', time());
    					$entity->save();
    					
    					$data['id'] = null;
    					$data['ipid'] = $ipid;
    					$data['option_id'] = $entity->option_id;
    					$data['option_date'] = date('Y-m-d H:i:s', time());
    					$data['option_localization'] = $entity->option_localization;
    					
    					$newentity = PatientArtificialEntriesExitsTable::getInstance()->createIfNotExistsOneBy(array('id', 'ipid'), array($data['id'], $ipid), $data);
    					
    					break;
    				case 'delete':
    					$entity->delete();
    			}
    		}
    
    		$data = IconsPatient::get_patient_artificial_entries_exits_expired(array($ipid));
    		if(count($data['patient_artificial_entries_exits_expired'][$ipid]) > 0 ){
    			$return ['remove_icon'] = "0";
    		} else{
    			$return ['remove_icon'] = "1";
    		}
    
    		echo json_encode($return);
    		exit;
    
    	}
    
    
    }
    
    
    /**
     * ISPC-2508 Carmen 23.01.2020
     *  Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
     */
    public function editartificialsettingAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	$artset_id = $_REQUEST['artset_id'];
    	if($artset_id != "")
    	{
	    	$saved = PatientArtificialEntriesExitsTable::getInstance()->findById($artset_id, Doctrine_Core::HYDRATE_ARRAY);
	    	$saved = $saved[0];
    	}
    	//var_dump($saved); exit;
    	//get the options box from the client list
    	$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
    	 
    	$af = new Application_Form_Stammdatenerweitert([
    			"_client_options"          => $client_options
    	]);
    	 
    	$artificial_setting_form = $af->create_form_artificial_entries_exits($saved);
    
    
    	$this->getResponse()->setBody($artificial_setting_form)->sendResponse();
    
    	exit;
    }
    
    
    /**
     * ISPC-2508 add artificial entry exit from contactform block
     * @carmen 05.03.2020
     * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
     */
    
    public function createformblockartificialentryexitrowAction()
    {
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $parent_form = $this->getRequest()->getParam('parent_form');
        
        $_block_name = $this->getRequest()->getParam('_block_name', null);
        
        $af = new Application_Form_FormBlockArtificialEntriesExits();
        $row = $af->create_form_block_artificial_entries_exits_row(['pat_opt_id' => "new_". uniqid()], $parent_form. "[new_". uniqid(). "]");
        
        $this->getResponse()->setBody($row)->sendResponse();
        
        exit;
    }
    

    // Maria:: Migration ISPC to CISPC 08.08.2020
    //ISPC-2381 Carmen 25.02.2020
    public function quickpatientremedieseditAction(){

    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->setLayout('layout_ajax');
    	$post=$_POST;

    	$decid = Pms_Uuid::decrypt($_POST['id']);
    	$ipid = Pms_CommonData::getIpId($decid);

    	if(strlen($ipid)<5){
    		exit();
    	}

    	if($post['mode']=="remove") {
    		$drop = Doctrine_Query::create()
    		->select('*')
    		->from('PatientRemedies')
    		->where("ipid=?", $ipid)
    		->andWhere('remedies=?', $post['itemname'])
    		->andWhere('isdelete=0');
    		$droparray = $drop->fetchArray();
    		if ($droparray) {
    			$item = Doctrine::getTable('PatientRemedies')->findOneBy('id',$droparray[0]['id']);
    			$item->isdelete = 1;
    			$item->save();
    			echo "OK: ITEM DELETED";
    		}
    	}

    	if($post['mode']=="add") {
    		$prm=new PatientRemedies();
    		$prm->remedies=$post['itemname'];
    		$prm->ipid=$ipid;
    		$prm->suppstatus="vorhanden";
    		$prm->save();
    		echo "OK: ITEM ADDED";
    	}

    }

    //ISPC-2547 Carmen 03.03.2020
    public function setpatientdosagegivenAction(){
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	$userid = $logininfo->userid;
   
    	if(!empty($_REQUEST['id'])){
    		$decid = Pms_Uuid::decrypt($_REQUEST['id']);
    		$ipid = Pms_CommonData::getIpId($decid);
    		$action = $_REQUEST['action'];
    		$dosage = $_REQUEST['dosage'];
    		$drugplan_id = $_REQUEST['drugplan_id'];
    		$medication = $_REQUEST['medication'];
    		if($_REQUEST['dosage_type'])
    		{
    			$dosage_type = $_REQUEST['dosage_type'];
    		}    		
    		if($_REQUEST['dosage_time'])
    		{
    			$dosage_time = $_REQUEST['dosage_time'] . ":00";
    		}
    		else 
    		{
    			$dosage_time = '00:00:00';
    		}
    		if($_REQUEST['documented_info'] != '')
    		{
    			$documented_info = $_REQUEST['documented_info'];
    		}
    		else
    		{
    			$documented_info = '';
    		}
    		/* if($_REQUEST['undocumented_info'] != '')
    		{
    			$undocumented_info = $_REQUEST['undocumented_info'];
    		}
    		else
    		{
    			$undocumented_info = '';
    		} */
	
    			switch($action) {
    				case 'remove':
    					
    					$entity = PatientDrugPlanDosageGivenTable::getInstance()->findOneCurrentDayGiven($ipid, $drugplan_id, $dosage_time);
    					
    					if($entity)
    					{
    						$entity->undocumented = '1';
    						$entity->undocumented_info = $documented_info;
    						$entity->undocumented_date = date('Y-m-d H:i:s', time());
    						$entity->undocumented_user = $userid;
    						//ISPC-2583 Carmen 27.04.2020    					
    						$entity->dosage_status = 'not_given';
    						//--
    						$entity->save();
    					}
    						

    					if($dosage_type == 'onedose' && $documented_info != '')
    					{
    						$comment = $this->view->translate('Given was undocumented for medication ') . $medication . ': ' . $documented_info;
    					}
    					elseif($dosage_type == 'onedose' && $documented_info == '')
    					{
    						$comment = $this->view->translate('Given was undocumented for medication ') . $medication;
    					}
    					else
    					{
    						$comment = $this->view->translate('Given was undocumented for medication ') . $medication . ': ' . $this->view->translate('dosage') . ' '. $dosage . $this->view->translate(' and time ') . $_REQUEST['dosage_time'] . ' ' . $undocumented_info;
    					}
    					$cust = new PatientCourse();
    					$cust->ipid = $ipid;
    					$cust->course_date = date("Y-m-d H:i:s", time());
    					$cust->course_type = Pms_CommonData::aesEncrypt("MG");         //ISPC-2547 Lore 26.03.2020
    					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
    					$cust->user_id = $userid;
    					$cust->save();
    					
    					break;
    				case 'set':
    					$data['ipid'] = $ipid;
    					$data['drugplan_id'] = $drugplan_id;
    					$data['dosage'] = $dosage;
    					$data['dosage_time_interval'] = $dosage_time;
    					$data['documented_info'] = $documented_info;
    					$data['documented_date'] = date('Y-m-d H:i:s', time());
    					//ISPC-2583 Carmen 27.04.2020
    					$data['dosage_status'] = 'given';
    					//--
    					
    					$entity = PatientDrugPlanDosageGivenTable::getInstance()->createIfNotExistsOneBy(array('ipid', 'drugplan_id', 'dosage_time_interval', 'documented_date'), array($ipid, $drugplan_id, $dosage_time, $data['documented_date']), $data);
    					
    					if($dosage_type == 'onedose' && $documented_info != '')
    					{
    						$comment = $this->view->translate('Given was documented for medication ') . $medication . ': ' . $documented_info;
    					}
    					elseif($dosage_type == 'onedose' && $documented_info == '')
    					{
    						$comment = $this->view->translate('Given was documented for medication ') . $medication;
    					}
    					else
    					{
    						$comment = $this->view->translate('Given was documented for medication ') . $medication . ': '. $this->view->translate('dosage') . ' ' . $dosage . $this->view->translate(' and time ') . $_REQUEST['dosage_time'] . ' ' . $documented_info;
    					}
    					$cust = new PatientCourse();
    					$cust->ipid = $ipid;
    					$cust->course_date = date("Y-m-d H:i:s", time());
    					$cust->course_type = Pms_CommonData::aesEncrypt("MG");         //ISPC-2547 Lore 26.03.2020
    					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
    					$cust->user_id = $userid;
    					$cust->save();
    					break;
    				default:
    					break;
    			}
    		}
    		exit;
    
    }
    // Maria:: Migration ISPC to CISPC 08.08.2020
    //ISPC-2440 Lore 11.03.2020
    public function saveuserlastcontactfilterAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

        $modules = new Modules();
        if($this->getRequest()->isPost())
        {
            if( $modules->checkModulePrivileges("222", $clientid) )
            {

                $data = $_POST;

                if(!empty($data))
                {
                    if($data['shortcut'] == 'all_sh' || $data['shortcut'] == 'all_time' ) {

                        $user_filter = new Application_Form_UserLastContactsFilters ();
                        $result = $user_filter->reset_filter_to_all($userid, $clientid, $data['shortcut']);

                    } else {

                        $user_filter = new Application_Form_UserLastContactsFilters ();
                        $result = $user_filter->set_filter($userid, $clientid, $data);

                    }

                }
            }
        }
        exit();
    }
	// Maria:: Migration ISPC to CISPC 08.08.2020
    //	 6. General Patient Search new
    public function patientconnectedsearchAction()
    {
    	//ISPC-2561 Carmen 12.03.2020 show pacients for all clients the loged user can connect
    	$this->_helper->layout->setLayout('layout_ajax');
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$hidemagic = Zend_Registry::get('hidemagic');

    	$clientid = $logininfo->clientid;
    	$this->view->clientid = $clientid;
    	$userid = $logininfo->userid; //ISPC-2561
    	$search_string = addslashes(trim(urldecode($_REQUEST['q'])));

    	if($_REQUEST['json'] == 1) {
    		$this->view->json = 1;
    	}

    	if(strlen($search_string) > 2)
    	{
    		$search_fl = explode(",",$search_string);
    		$search_l = trim($search_fl[0]);
    		$search_f = trim($search_fl[1]);
    		$nr_fl = sizeof($search_fl);

    		//print_r($nr_fl); exit;
    		$droparray = array();
    		$user_details = UserTable::getInstance()->find($userid, Doctrine_Core::HYDRATE_ARRAY);
    		$this->view->user_details = $user_details;

    		$cl = new Client();
    		$clients_details = $cl->getClientData();
    		foreach($clients_details as $clk=>$clv){
    			$clients_data[$clv['id']]['team_name'] = $clv['team_name'];
    			$clients_data[$clv['id']]['client_name'] = $clv['client_name'];
    		}
    		$this->view->client_data = $clients_data;

    		$modules =  new Modules();
    		$clientModules = $modules->get_client_modules($clientid);

    		$limit = 10;

    		$this->view->clientModules = $clientModules;

    		$user_patients_connected_cl = array();

    		if($user_details['usertype'] == 'SA')
    		{
    			$drop = Doctrine_Query::create()
    			//->select('ipid, epid')
    			->select('ipid, epid, clientid')
    			->from('EpidIpidMapping')
    			->where("clientid = ?" , $clientid);
    			//->orderBy('epid asc');
    			$droparray = $drop->fetchArray();

    			$comma = ",";
    			$ipidval = "'0'";
    			$ipids_array = array();
    			if($droparray)
    			{
    				foreach($droparray as $key => $val)
    				{
    					$fn_epids[$val['ipid']] = $val['epid'];
    					$ipidval .= $comma . "'" . $val['ipid'] . "'";
    					$comma = ",";
    					$ipids_array[] = $val['ipid'];
    				}
    			}

    			$connected_clients_arr = array($clientid);
    			$clients_str = implode(',',$connected_clients_arr);
    			$user_patients_connected_cl[] = PatientUsers::getUserPatientsConnected($userid,$clientid);
    		}
    		else
    		{
    			if($_REQUEST['json'] == 1 || $_REQUEST['op'] == 'assigned_patients') {
    				$uclconid = $userid; //the user we search for clients connected
    				$uclcon = $user_details;
    				$connected_clients_arr = array($clientid);
    			}
    			else
    			{

    				if($clientModules['221'])
    				{

    					if($user_details['duplicated_user'] != '0')
    					{
    						$uclconid = $user_details['duplicated_user']; //the user we search for clients connected
    						$uclcon = UserTable::getInstance()->find($uclconid, Doctrine_Core::HYDRATE_ARRAY);
    					}
    					else
    					{
    						$uclconid = $userid; //the user we search for clients connected
    						$uclcon = $user_details;
    					}
    					//$duplicates = UserTable::getInstance()->findByDuplicatedUser($uclconid, Doctrine_Core::HYDRATE_ARRAY);

    					$get_duplicated_users_q = Doctrine_Query::create()
    					->select('*')
    					->from('User')
    					->where('duplicated_user = ?',$uclconid)
    					->andWhere('isdelete = 0 ')
    					->andWhere('isactive = 0');
    					$duplicates = $get_duplicated_users_q->fetchArray();

    					if($uclconid == $userid && empty($duplicates))
    					{
    						$connected_clients_arr = array($clientid);
    					}
    					else
    					{
    						//$u2c = new User2Client();
    						//$user2clients_arr = $u2c->getTable()->findByUser($uclconid, Doctrine_Core::HYDRATE_ARRAY);
    						$clients = Doctrine_Query::create()
    						->select('client')
    						->from('User2Client s')
    						->leftJoin('s.User u')
    						->where('user= ?',$uclconid)
    						->andWhere('s.isdelete = 0 ');
    						$user2clients_arr = $clients->fetchArray();
    						$connected_clients_arr = array_column($user2clients_arr, 'client');
    					}

    				}
    				else
    				{
    					$uclconid = $userid; //the user we search for clients connected
    					$uclcon = $user_details;
    					$connected_clients_arr = array($clientid);


    					 //$user_patients_connected_cl[] = PatientUsers::getUserPatientsConnected($userid,$clientid);
    				}
    			}

    			$ipidval = "'0'";
    			$ipidval_arr = array();

    			$clients_str = implode(',',$connected_clients_arr);
    			$ipids_array = array();
    			foreach($connected_clients_arr as $concl)
    			{
    				$ipidval_arr[$concl] = '';
    				$comma = '';
    				$drop = Doctrine_Query::create()
    				->select('e.ipid, e.epid, e.clientid')
    				->from('EpidIpidMapping e')
    				->where("clientid = ?" , $concl);
    				$droparray_concl = $drop->fetchArray();
    				$droparray = array_merge($droparray, $droparray_concl);

    				if($droparray_concl)
    				{
    					foreach($droparray_concl as $key => $val)
    					{
    						$fn_epids[$val['ipid']] = $val['epid'];
    						$ipidval_arr[$concl] .= $comma . "'" . $val['ipid'] . "'";
    						$ipids_array[] = $val['ipid'];
    						$comma = ",";
    					}
    				}
    				$ipidval .= ','.implode(',', $ipidval_arr);

    				if($concl == $uclcon['clientid'])
    				{
    				    $user_patients_connected_cl[$uclcon['clientid']] = PatientUsers::getUserPatientsConnected($uclconid,$uclcon['clientid']);
    				}
    				else
    				{

    					foreach($duplicates as $userdupl)
    					{

    						if($concl == $userdupl['clientid'])
    						{
    						    $user_patients_connected_cl[ $userdupl['clientid']] = PatientUsers::getUserPatientsConnected($userdupl['id'],$userdupl['clientid']);
    						}
    					}

    				}
    			}

    		}
    		$duplicate_users_sql = "";
    		$duplicate_users_patients_sql_arr = array();
    		$client_qs = array();

//     		dd($user_patients_connected_cl);
    		$users_ipids = array();
    		if(!empty($user_patients_connected_cl)){
    		    foreach($user_patients_connected_cl as $uk=>$userpsts){
    		        if($userpsts['bypass'] === true){
    		            //meaning user can see all patients of client
    		            $client_qs[] = $userpsts['clientid'];
    		        }
    		        else
    		        {
    		           // Clear for clients where no users
   		               unset($userpsts['patients']['X']);
   		               if(!empty($userpsts['patients'])){
   		                   $users_ipids = array_merge($users_ipids,array_values($userpsts['patients']));
   		                   $duplicate_users_patients_sql_arr[] = " p.ipid IN (" . $userpsts['patients_str'] . ") ";
   		               }

    		        }
    		    }
    		}

    		$conected_client_ipids = array();
    		if(!empty($client_qs)){
    		    $clinets_arr = array();
    		    $clients_qusers = Doctrine_Query::create()
    		    ->select('ipid, epid, clientid')
    		    ->from('EpidIpidMapping')
    		    ->whereIn("clientid" , $client_qs);
    		    $clinets_arr = $clients_qusers->fetchArray();

    		    $conected_client_ipids = array_column($clinets_arr, 'ipid');
//     		    $client_qs_str = implode(',',$client_qs);
//     		    $duplicate_users_patients_sql_arr[] = " p.ipid IN (SELECT ipid FROM EpidIpidMapping WHERE clientid in (".$client_qs_str.") ) ";
    		}
    		$final_search_ipids = array();
    		$final_search_ipids = array_merge($users_ipids,$conected_client_ipids);

    		// intersect with all ipids of connected_cleints
    		$final_search_ipids = array_intersect($final_search_ipids,$ipids_array);

    		if(empty($final_search_ipids)){
    		    $final_search_ipids = array('X'); //Avoid error Hack
    		}

		    if( !empty($duplicate_users_patients_sql_arr)){
		        $duplicate_users_sql = implode(" OR ",$duplicate_users_patients_sql_arr);
		    } else {
		        $duplicate_users_sql = " p.ipid IN ('X') ";
		    }
// 		    dd($clients_str);
// 		    dd($duplicate_users_sql);
// 		    dd($duplicate_users_sql,$user_patients_connected_cl);



		    if(count($droparray) > 0 && !empty($ipids_array))
    		{
    			$sql = "*, e.ipid,e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
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

    			//if isstandby it must be shown as Anfrange in LS even if is also isdischarged
    			$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,1, (IF(isdischarged = 1,2,0)))) )) ) as status,";
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
    				$sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,1, (IF(isdischarged = 1,2,0)))) )) ) as status,";
    			}

    			$search_string_umlaut = array (
    					"ä"=>"ae",
    					"ö"=>"oe",
    					"ü"=>"ue",
    					"ae"=>"ä",
    					"oe"=>"ö",
    					"ue"=>"ü",
    					"ß"=>"ss",
    					"ss"=>"ß"

    			);

    			if($nr_fl !=2)
    			{
    				foreach ($search_string_umlaut as $key => $value)
    				{
    					if(stripos($search_string, $value))
    					{
    						$search_str=$search_string;
    						$search_string_ulm=str_ireplace($value,$key,$search_string);
    						if (strpos('.', $search_str) !== false) {
    							$dateborn = explode('.', $search_str);

    							if(count($dateborn) > '2')
    							{
    								$datedb = $dateborn[2] . '-' . $dateborn[1] . '-' . $dateborn[0];
    							}
    							elseif(count($dateborn) =='2')
    							{
    								$datedb = $dateborn[1] . '-' . $dateborn[0];
    							}
    							elseif(count($dateborn) =='1')
    							{
    								$datedb = $dateborn[0];
    							}
    							$search_str = $datedb;
    						}
							//ISPC-2587  Elena? search for case_number  // Maria:: Migration CISPC to ISPC 02.09.2020
    						$patient = Doctrine_Query::create()
    						->select($sql)
    						->from('PatientMaster p');
    						$patient->WhereIn('p.ipid',$final_search_ipids);
    						$patient->andWhere('p.isdelete = 0 ');
//     						$patient->where("p.ipid in(" . $ipidval . ")  and   (" . $duplicate_users_sql .")  and p.isdelete = 0");
    						//->where("p.ipid in(" . $ipidval . ")  and p.ipid IN (" . $user_patients['patients_str'] . ") and p.isdelete = 0");
    						$patient->leftJoin("p.EpidIpidMapping e");
    						$patient->leftJoin("p.PatientHealthInsurance h");
                            $patient->leftJoin("p.PatientCaseStatus pcs");
    						$patient->andWhere("e.clientid in  (" .$clients_str. ") AND (TRIM(LOWER(e.epid)) like TRIM(LOWER('%" . $search_str . "%')) OR TRIM(LOWER(h.insurance_no)) like TRIM(LOWER('%" . $search_str . "%')) OR TRIM(LOWER(pcs.case_number)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(p.birthd)) like TRIM(LOWER('%" . $search_str . "%'))
					       OR (
    
						       TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
			
						       OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
		
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
    
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
    
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_str) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
    
					       	   OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						       OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string_ulm) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
					       ))");

    						break;
    					}
    					else{
    						if (strpos($search_string, '.')) {
    							$dateborn = explode('.', $search_string);

    							if(count($dateborn) > '2')
    							{
    								$datedb = $dateborn[2] . '-' . $dateborn[1] . '-' . $dateborn[0];
    							}
    							elseif(count($dateborn) =='2')
    							{
    								$datedb = $dateborn[1] . '-' . $dateborn[0];
    							}
    							elseif(count($dateborn) =='1')
    							{
    								$datedb = $dateborn[0];
    							}
    							$search_string = $datedb;
    						}

							//ISPC-2587  Elena? search for case_number // Maria:: Migration CISPC to ISPC 02.09.2020
    						$patient = Doctrine_Query::create()
    						->select($sql)
    						->from('PatientMaster p');
//     						$patient->where("p.ipid in(" . $ipidval . ")  and   (" . $duplicate_users_sql .")  and p.isdelete = 0");
//     						$patient->where("p.isdelete = 0 AND  (" . $duplicate_users_sql .")  ");
    						$patient->WhereIn('p.ipid',$final_search_ipids);
    						$patient->andWhere('p.isdelete = 0 ');
    						$patient->leftJoin("p.EpidIpidMapping e");
    						$patient->leftJoin("p.PatientHealthInsurance h");
                            $patient->leftJoin("p.PatientCaseStatus pcs");
    						$patient->andWhere("e.clientid in  (" .$clients_str. ") AND  (TRIM(LOWER(e.epid)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(h.insurance_no)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(pcs.case_number)) like TRIM(LOWER('%" . $search_string . "%')) OR TRIM(LOWER(p.birthd)) like TRIM(LOWER('%" . $search_string . "%'))
					        OR (
						         TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,', ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
						         OR CONCAT(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)) COLLATE latin1_german2_ci,' ',LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($search_string) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci
				           	))");

    					}

    				}


    			}
    			else
    			{

    				$search_l_query = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '". utf8_decode($search_l) ." ' USING utf8) USING latin1))) COLLATE latin1_german2_ci";
    				$search_f_query = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( ' ".utf8_decode($search_f). "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci";

    				$search_l_query_uml = '';
    				$search_f_query_uml = '';

    				foreach ($search_string_umlaut as $key => $value)
    				{
    					if(stripos($search_l, $value))
    					{
    						$search_l_uml = str_ireplace($value,$key,$search_l);
    						$search_l_query_uml = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '". utf8_decode($search_l_uml) ." ' USING utf8) USING latin1))) COLLATE latin1_german2_ci";
    						break;
    					}

    				}

    				foreach ($search_string_umlaut as $key => $value)
    				{

    					if(stripos($search_f, $value))
    					{
    						$search_f_uml = str_ireplace($value,$key,$search_f);
    						$search_f_query_uml = "TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1))) COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '". utf8_decode($search_f_uml) ."%' USING utf8) USING latin1))) COLLATE latin1_german2_ci";
    						break;
    					}
    				}

    				if(!empty($search_l_query_uml)) {
    					$search_l_query_final = '(('.$search_l_query.') OR ('.$search_l_query_uml.'))';
    				} else {
    					$search_l_query_final = $search_l_query;
    				}

    				if(!empty($search_f_query_uml)) {
    					$search_f_query_final = '(('.$search_f_query.') OR ('.$search_f_query_uml.'))';
    				} else {
    					$search_f_query_final = $search_f_query;
    				}

    				$patient = Doctrine_Query::create()
    				->select($sql)
    				->from('PatientMaster p');
    				$patient->WhereIn('p.ipid',$final_search_ipids);
    				$patient->andWhere('p.isdelete = 0 ');
//     				$patient->where("p.ipid in(" . $ipidval . ")  and  ( " . $duplicate_users_sql .")  and p.isdelete = 0");
//     				$patient->where("p.ipid in(" . $ipidval . ")  and   " . $duplicate_users_sql ."  and p.isdelete = 0");
//     				$patient->where("p.isdelete = 0 and   " . $duplicate_users_sql ."  ");
//     				$patient->where("p.isdelete = 0");
//     				$patient->andWhereIn("p.ipid",$ipids_array);
//     				$patient->andWhere("p.ipid in(" . $ipidval . ") ");
//     				if( ! empty($duplicate_users_sql))
//     				{
//     				    $patient->andWhere($duplicate_users_sql);
//     				    $patient->andWhere('p.isdelete = 0 AND '.$duplicate_users_sql.' ');
//     				}
    				$patient->leftJoin("p.EpidIpidMapping e");
//     				$patient->andWhere("e.clientid in  (" .$clients_str. ") AND (TRIM(LOWER(e.epid)) OR (".$search_l_query_final." AND ".$search_f_query_final."))");
    				$patient->andWhere("e.clientid in  (" .$clients_str. ") AND  (TRIM(LOWER(e.epid)) OR (".$search_l_query_final." AND ".$search_f_query_final."))");
    			}

    			if($logininfo->hospiz == 1)
    			{
    				$patient->andwhere('ishospiz = 1');
    			}
    			$patient->orderby('status,ipid');
//                     $patient->limit($limit);
//     			dd($patient->getSqlQuery());
    			$droparray1 = $patient->fetchArray();
    		}
    		elseif($logininfo->showinfo == 'show')
    		{

    			$fndrop = Doctrine_Query::create()
    			->select('ipid')
    			->from('EpidIpidMapping')
    			//->where("clientid = ? " , $clientid );
    			->whereIn("clientid" , $connected_clients_arr );
    			$fndroparray = $fndrop->fetchArray();

    			$fnipidval = "'0'";
    			if($fndroparray)
    			{
    				$comma = ",";
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
							IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,1, (IF(isdischarged = 1,2,0)))) )) ) as status,
							,AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex")
    							->from('PatientMaster')
    							->where("isdelete = 0 and ipid in(" . $fnipidval . ") and (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%')) or trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $search_string . "%'))  or concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
							concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
							concat(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')) or
							concat(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $search_string . "%')))")
    							->orderby('status');
    							$droparray2 = $patient1->fetchArray();
    		}
    	}

    	if(is_array($droparray2) || is_array($droparray1))
    	{
    		$res_arr = array_merge((array) $droparray2, (array) $droparray1);

    		$res = array();
    		$res_current_cl = array();
    		$res_other_cl = array();
    		for($i = 0; $i < count($res_arr); $i++)
    		{
    			if($res_arr[$i]['EpidIpidMapping']['clientid'] == $clientid){
    				$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['status'] = $res_arr[$i]['status'];
    				$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['first_name'] = $res_arr[$i]['first_name'];
    				$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['last_name'] = $res_arr[$i]['last_name'];

    				if(strlen($res_arr[$i]['middle_name']) > 0)
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['middle_name'] = $res_arr[$i]['middle_name'];
    				}
    				else
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['middle_name'] = " ";
    				}
    				if($res_arr[$i]['admission_date'] != '0000-00-00 00:00:00')
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['admission_date'] = date('d.m.Y', strtotime($res_arr[$i]['admission_date']));
    				}
    				else
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['recording_date'] = "-";
    				}
    				if($res_arr[$i]['recording_date'] != '0000-00-00 00:00:00')
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['recording_date'] = date('d.m.Y', strtotime($res_arr[$i]['recording_date']));
    				}
    				else
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['recording_date'] = "-";
    				}
    				if($res_arr[$i]['birthd'] != '0000-00-00 00:00:00')
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'] = date('d.m.Y', strtotime($res_arr[$i]['birthd']));
    				}
    				else
    				{
    					$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'] = "-";
    				}

    				$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'] = Pms_CommonData::hideInfo($res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'], $res_arr[$i]['isadminvisible']);

    				$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['id'] = Pms_Uuid::encrypt($res_arr[$i]['id']);
    				$res_current_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['epid_id'] = Pms_Uuid::encrypt($fn_epids[$res_arr[$i]['ipid']]);
    			}
    			else {
    				$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['status'] = $res_arr[$i]['status'];
    				$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['first_name'] = $res_arr[$i]['first_name'];
    				$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['last_name'] = $res_arr[$i]['last_name'];

    				if(strlen($res_arr[$i]['middle_name']) > 0)
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['middle_name'] = $res_arr[$i]['middle_name'];
    				}
    				else
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['middle_name'] = " ";
    				}
    				if($res_arr[$i]['admission_date'] != '0000-00-00 00:00:00')
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['admission_date'] = date('d.m.Y', strtotime($res_arr[$i]['admission_date']));
    				}
    				else
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['recording_date'] = "-";
    				}
    				if($res_arr[$i]['recording_date'] != '0000-00-00 00:00:00')
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['recording_date'] = date('d.m.Y', strtotime($res_arr[$i]['recording_date']));
    				}
    				else
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['recording_date'] = "-";
    				}
    				if($res_arr[$i]['birthd'] != '0000-00-00 00:00:00')
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'] = date('d.m.Y', strtotime($res_arr[$i]['birthd']));
    				}
    				else
    				{
    					$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'] = "-";
    				}

    				$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'] = Pms_CommonData::hideInfo($res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['birthd'], $res_arr[$i]['isadminvisible']);

    				$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['id'] = Pms_Uuid::encrypt($res_arr[$i]['id']);
    				$res_other_cl[$res_arr[$i]['EpidIpidMapping']['clientid']][$i]['epid_id'] = Pms_Uuid::encrypt($fn_epids[$res_arr[$i]['ipid']]);
    			}
    		}

    		$res = $res_current_cl + $res_other_cl;
    		$this->view->droparray = $res;

    	}
    	else
    	{
    		$this->view->droparray = array();
    	}

    }




	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function createformextratreatmentplanclinicAction(){
        $this->_helper->layout->setLayout('layout_ajax');

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $model = new Application_Form_FormBlockKeyValue();
        $model->create_extra_treatmentplanclinic();

        exit;
    }
	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function hl7transmitopsAction(){

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

        $decid = Pms_Uuid::decrypt($_POST['encid']);
        $ipid = Pms_CommonData::getIpid($decid);

        $username=User::getUsersNiceName(array($userid));

        $opsdata = array(
            'ipid'=>$ipid,
            'opsdate'=>$_POST['opsdate'],
            'opscode'=>$_POST['opscode'],
            'casenumber'=>$_POST['case_number'],
            'anf_oe_fachlich'=>$_POST['anf_oe_fachlich'],
            'anf_oe_pflegerisch'=>$_POST['anf_oe_pflegerisch'],
            'erb_oe_pflegerisch'=> $_POST['erbringende_oe_pflegerisch'],
            'erb_oe_fachlich'=> $_POST['erbringende_oe_fachlich'],
            'startdate'=>$_POST['startdate'],
            'enddate'=>$_POST['enddate'],
            'case'=>$_POST['caseid'],
            'userid'=>$userid,
            'username'=>$username[$userid]['nice_name']
        );


        $opsconfig = ClientConfig::getConfig($clientid, 'opsconfig');

        $send_real=true;
        foreach ($opsconfig['codes'] as $code){
            if ($code['ops_only_internal']){
                foreach ($code['minutes'] as $min){
                    if($min['name']==$opsdata['opscode']){
                        $send_real=false;
                    }
                }
            }
            if(! $send_real){
                break;
            }
        }

        $opsdata['send_real']=$send_real;

        if($send_real == false){
            $return="Dieser Code wurde nicht versendet. <br>Er wird nur intern vermerkt.";
        }else{
            $opsdata['ipid']=$ipid;
            $opsdata['userid']=$userid;
            $opsdata['type']='ops';
            $conf = array (
                //the clientid for this server
                'clientid'		=>	$clientid,
                //the userid this server has
                'userid'		=>	1,
                //everything is logged to db is encrypted if true
                'encryptlog'	=>  false,
                //0=print all, 2 print only errors
                'verbosity' 	=>	9,
                //we dont have real productivity data: mark patient as testpatient
                'testdata'      =>  0
            );
            $return=Net_ProcessHL7::send_message($opsdata, $conf);
        }

        if ($return!="No ACK received"){
            $pcs=new PatientCaseStatusLog();
            $pcs->clientid=$clientid;
            $pcs->ipid=$ipid;
            $pcs->case_id=$opsdata['caseid'];
            $pcs->log_date=date('Y-m-d H:i:s');
            $pcs->log_type="OPS";
            $pcs->log_data=json_encode($opsdata);
            $pcs->isdelete=0;
            $pcs->save();

        }

        echo $return;
    }
	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function hl7transmittimesAction(){

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

        $decid = Pms_Uuid::decrypt($_POST['encid']);
        $ipid = Pms_CommonData::getIpid($decid);

        $username=User::getUsersNiceName(array($userid));

        $timesdata = array(
            'ipid'=>$ipid,
            'casenumber'=>$_POST['case_number'],
            'anf_oe_fachlich'=>$_POST['anf_oe_fachlich'],
            'anf_oe_pflegerisch'=>$_POST['anf_oe_pflegerisch'],
            'erb_oe_pflegerisch'=> $_POST['erbringende_oe_pflegerisch'],
            'erb_oe_fachlich'=> $_POST['erbringende_oe_fachlich'],
            'startdate'=>$_POST['startdate'],
            'enddate'=>$_POST['enddate'],
            'caseid'=>$_POST['caseid'],
            'userid'=>$userid,
            'username'=>$username[$userid]['nice_name'],
            'mins'=>$_POST['mins'],
            'mins_details'=>$_POST['mins_details']
        );

        $profsmap=Client::getClientconfig($clientid, 'hl7leistungen_profsmap');
        if(!$profsmap){
            die("FEHLER! Die Konfiguration muss überarbeitet werden!");
        }


        $return = "FEHLER";

        $opsconfig=ClientConfig::getConfig($clientid, 'opsconfig');
        $int_oes=$opsconfig['internal_oe'];
        $allinternal=$opsconfig['all_times_internal'];

        if(in_array($timesdata['erb_oe_pflegerisch'], $int_oes) || $allinternal){
            $return = "Die Zeiten wurden nur in ISPC vermerkt.";
        }else{
            //$sender=new Net_SendMessages();

            //$return = $sender->sendTimes($arr);
        }
        if ($return!="No ACK received") {
            $pcs = new PatientCaseStatusLog();
            $pcs->clientid = $clientid;
            $pcs->ipid = $ipid;
            $pcs->case_id = $timesdata['caseid'];
            $pcs->log_date = date('Y-m-d H:i:s');
            $pcs->log_type = "times";
            $pcs->log_data = json_encode($timesdata);
            $pcs->isdelete = 0;
            $pcs->save();
        }

        echo $return;
    }

	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function hidepatientcasestatuslogAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $isadmin=0;
        if($logininfo->usertype=='SA' && $logininfo->showinfo!='show')
        {
            $isadmin =1;
        }

        if($_REQUEST['hid'] && $_REQUEST['id'] && $isadmin)
        {
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpId($decid);
            $hid=intval($_REQUEST['hid']);

            $q = Doctrine_Query::create()
                ->update('PatientCaseStatusLog')
                ->set('isdelete',"1")
                ->where("id = ?", $hid)
                ->andWhere("ipid = '".$ipid."'");
            $q->execute();
        }

        exit('OK');
    }
	//Maria:: Migration CISPC to ISPC 22.07.2020
public function getnewmediselectwidgetAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);

        if (!strlen($ipid) > 0)
            return;

        $af_fbkv = new Application_Form_FormBlockKeyValue();
        $form = $af_fbkv->create_form_medicationclinic(null, $ipid);
        $__formHTML = $form->render();
        echo $__formHTML;
    }
//Maria:: Migration CISPC to ISPC 22.07.2020
    public function setuserdefaultpatientlistAction(){
        //this is the default patientlist used in the patientquicknav bar
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $logininfo = new Zend_Session_Namespace('Login_Info');

        if(isset($_GET['plist'])){
            $fallart= $_GET['plist'];
            $u=Doctrine::getTable('User')->findOneBy('id',$logininfo->userid);
            $u->preferred_clinic_list=$fallart;
            $u->save();
        }

        echo "OK";
    }


    /**
     * returns users with their stamps
     * used in lmu_sign contact form block //Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public function getuserstampsAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $logininfo = new Zend_Session_Namespace('Login_Info');

        $GroupsQ = Doctrine_Query::create()
            ->select('id, groupname')
            ->from('Usergroup')
            ->where("clientid=?", $logininfo->clientid);

        $clientgroups = $GroupsQ->fetchArray();

        $map_groupid_to_groupname = array();
        foreach ($clientgroups as $group){
            $map_groupid_to_groupname[$group['id']]=$group['groupname'];
        }
        // Get Users and Groups of client
        $UsersQ = Doctrine_Query::create()
            ->select('u.id, u.last_name, u.first_name, u.groupid')
            ->from('User u')
            ->where("clientid=?", $logininfo->clientid)
            ->andWhere("isdelete =0");
        $clientusers = $UsersQ->fetchArray();

        $users_by_id = array();
        $users_by_groupid = array();
        foreach ($clientusers as $user) {
            $users_by_id[$user['id']] = $user;
            if(!in_array($user['id'], $users_by_groupid[$user['groupid']])){
                $users_by_groupid[$user['groupid']][]= $user['id'];
            }
        }
        $users_by_profession=array();
        $userstamp = new UserStamp();

        //add letter creator
        $user = $users_by_id[$logininfo->userid];
        $stamptext=$userstamp->getLastUserStamp($user['id']);
        $u=array($user['last_name'] ." " . $user['first_name'], $map_groupid_to_groupname[$user['groupid']]);
        if ($stamptext[0] && $stamptext[0]['row1'] && $stamptext[0]['row2']){
            $u=array($stamptext[0]['row1'], $stamptext[0]['row2']);
        }
        $users_by_profession['Sie selbst'][] = array($u[0], $u[1]);

        //add other users
        foreach ($clientusers as $user) {
            $m = $map_groupid_to_groupname[$user['groupid']];
            $stamptext=$userstamp->getLastUserStamp($user['id']);
            $stamp=array($user['last_name'] ." " . $user['first_name'], $m);
            if ($stamptext[0] && $stamptext[0]['row1'] && $stamptext[0]['row2']){
                $stamp=array($stamptext[0]['row1'], $stamptext[0]['row2']);
            }
            $users_by_profession[$m][] = array($stamp[0], $stamp[1]);
        }

        echo json_encode($users_by_profession);
        exit();
    }
	



    /**
     * ISPC-2513 Lore 13.04.2020
     * #ISPC-2512PatientCharts
     */
    public function savepatientreaddetaactionsAction(){
        
        $this->_helper->layout->setLayout('layout_ajax');
            $this->_helper->viewRenderer->setNoRender();

            $logininfo = new Zend_Session_Namespace('Login_Info');
            $userid = $logininfo->userid;
            $clientid = $logininfo->clientid;

            $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
            $ipid = Pms_CommonData::getIpId($decid);


            if($this->getRequest()->isPost() )
            {

                $entity = PatientReadmissionDetailsTable::getInstance()->findOrCreateOneBy(['ipid', 'readmission_id'], [$ipid, $_POST['readmission_id']], $_POST);
            
            $this->redirect(APP_BASE . "patientnew/patientdetails?id=" . $_GET['id'] , array("exit"=>true));
            exit; //for readability
        }
   
    }

    /* ISPC-2508 new design for artifcial entries exits
     * @carmen 18.05.2020
	 * #ISPC-2512PatientCharts
     */
    
    public function modalactionsAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	$parent_form = $this->getRequest()->getParam('parent_form');
    
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    
    	if($_REQUEST['recid'])
    	{
    		$values = PatientArtificialEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
    	}
    	
    	if($_REQUEST['openfrom'] == 'icon')
    	{
    		$set_actions = [
    				//'edit' => 'Bearbeiten',
    				'remove' => 'Zugang ziehen / entfernen',
    				'delete' => 'Daten löschen',
    				'refresh' => 'Neu anlegen / erneuern'
    		];
    	}
    	else
    	{
	    	if($values['isremove'] == '1')
	    	{
	    		$set_actions = [
	    				'refresh' => 'Neu anlegen / erneuern'
	    		];
	    	}
	    	else 
	    	{	
		    	$set_actions = [
		    			'edit' => 'Bearbeiten',
		    			'remove' => 'Zugang ziehen / entfernen',
		    			'delete' => 'Daten löschen',
		    			'refresh' => 'Neu anlegen / erneuern'
		    	];
	    	}
    	}
    
    	$af = new Application_Form_ButtonsForm([
    			'_set_options' => $set_actions,
    	]
    			);
    	$fbuttons = $af->create_form_buttons();
    
    	$this->getResponse()->setBody($fbuttons)->sendResponse();
    
    	exit;
    }

	// Maria:: Migration ISPC to CISPC 08.08.2020
    public function loadrasschartAction()
    {
	    //ISPC-2564 Carmen 25.06.2020
    	$this->_helper->layout->setLayout('layout_ajax');
    	$logininfo = new Zend_Session_Namespace('Login_Info');

    	$decid = Pms_Uuid::decrypt($_GET['id']);
    	$ipid = Pms_CommonData::getIpid($decid);

    	$this->view->patient_enc_id = $_GET['id'];

    	$graph_data = array();

    	$clientid = $logininfo->clientid;

    	$period = false;

    	if($this->getRequest()->isPost())
    	{
    		if(strlen($_POST['from']) > 0  && strlen($_POST['to']) > 0 )
    		{
    			$period['start'] = date("Y-m-d",strtotime($_POST['from']));
    			$period['end'] = date("Y-m-d",strtotime($_POST['to']));
    		}
    		else
    		{
    			$period = false;
    		}
    	}
    	$all_rass = PatientRassTable::get_patients_chart($ipid,$period);

    	if(empty($all_rass))
    	{
    		//no data in our tables
    		$this->view->nodata = "1";
    		return;
    	}

    	$i_serie = 0;
    	$i_resp = $i_serie;

    	$graph_data[$i_resp] = array(
    		'name'	=> $this->view->translate('responsiveness'),
    		'id'	=> 'responsiveness',
    		'type'	=> 'line',
    		'visible' => true,
    		'linkedTo'	=> null,
    		'lineWidth'	=> 2,
    		'pointPlacement'	=> 'on',
    		//'whiskerLength'	=> '20%',
    		'showInLegend'	=> true,
    		'color'	=> '#4572A7',
    		'tooltip'	=> array(
    				"pointFormat" => "<b>{series.name}: {point.y:,.f} </b><br/>{point.info}<br/>{point.x:%d.%m.%Y %H:%M}"
    		)
    	);

    	foreach($all_rass as $ipid => $w_data){
    		foreach($w_data as $k =>$w){

    			$gdate = strtotime($w['date']) * 1000;
    			$graphdate[] = $gdate;

    			$current_ = (float) $w['responsiveness'];
    			$info = $w['responsiveness'] > 0 ? PatientRass::getPatientRassRadios()['+'.$w['responsiveness']] : PatientRass::getPatientRassRadios()[$w['responsiveness']];
    			$graph_data[$i_resp]['data'][] = array(
    				"x"	=> $gdate,
    				"y"	=> (float)$w['responsiveness'],
    				"info" => $info
    			);
    		}
    	}

    	$this->view->xMin = min($graphdate);
    	$this->view->xMax = max($graphdate);

    	$we_have_data = false;
    	foreach ($graph_data as $one_serie) {
    		if ( ! empty($one_serie['data'])) {
    			$we_have_data = true;
    			break;
    		}
    	}

    	if (empty($graph_data) || ! $we_have_data) {

    		$this->view->nodata = "1";

    	} else {

    		$this->view->graph_series = json_encode($graph_data);
    	}
    }


    /**
     * ISPC-2630 Elsa: Intervenions
     * Maria:: Migration CISPC to ISPC 20.08.2020
     * parses opscodes xml and searchs
     * @elena, 04.08.2020
     *
     * @todo do we need opscode import db table?
     */
    public function opscodeAction(){

        $opspart = $this->getRequest()->getParam('q', null);
        $cnt = $this->getRequest()->getParam('cnt', null);
        $this->_helper->layout->setLayout('layout_ajax');
        //$this->_helper->viewRenderer->setNoRender();

        $opsXml = PUBLIC_PATH . '/import/ops/ops2020syst_claml_20191018.xml';
        //echo $opsXml;
        $opscodes = [];
        if (file_exists($opsXml)) {
            $xml = simplexml_load_file($opsXml);
            $classes = $xml->xpath('Class');
            $i = 0;
            foreach($classes as $classNode){
                $i++;
                //print_r($classNode);
                $attrs = $classNode->attributes();
                if(is_null($attrs)){
                    continue;
                }
                $is_category = false;
                $code = '';
                $label = '';
                foreach($attrs as $key => $attr) {
                    //echo $key,'="',$attr,"\"\n";
                    if($key == 'kind' && $attr == 'category'){
                        //echo 'category!!';
                        $is_category = true;
                    }
                    if($key == 'code'){
                        $code = $attr;
                    }
                }

                $labels = $classNode->xpath('Rubric[@kind="preferred"]/Label');
                $label = (string)$labels[0];



                if(strlen($code) > 0 && $is_category){
                    //echo 'fill codes  ' . $code . ' , ' . (string)$label[0];
                    $opscodes[] = ['code' => (string)$code, 'label' => $label];
                }


               /*
                if($i >= 500){
                    //print_r($opscodes);
                    //break;
                }*/
            }
            $droparray = array();


            foreach($opscodes as $opscode){
                if(stristr($opscode['label'], $opspart)){
                    //echo 'ja';
                    $droparray[$opscode['code']] = $opscode['label'];
                }
            }
            //echo $opspart;
            //print_r($droparray);
           // echo 'drop';

            $this->view->droparray = $droparray;
            $this->view->cnt = $cnt;


        } else {
            exit('Konnte '. $opsXml . ' nicht öffnen.');
        }


    }


    /**
     * ISPC-2630, ELSA: Intervenzionen, Elena, 04.08.2020 // Maria:: Migration CISPC to ISPC 20.08.2020
     */
    public function medicalinterventionAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $cnt = $this->getRequest()->getParam('cnt', null);
        $blockname = $this->getRequest()->getParam('blockname', null);
        $this->view->cnt = $cnt;


        if($cnt > 0){
            $intervention = Doctrine::getTable('Interventions')->find($cnt);

        }else{
            $intervention = new Interventions();
            $intervention->id = 0;
            $intervention->typ = 'medical';
        }
        $this->view->intervention = $intervention;

        //DOSAGE FORM
        $medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid);

        foreach($medication_dosage_forms as $k=>$df){
            $client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
        }
        $this->view->js_med_dosage_form = json_encode($client_medication_extra['dosage_form']);

        //TYPE
        $medication_types = MedicationType::client_medication_types($clientid,true);
        foreach($medication_types as $k=>$type){
            $client_medication_extra['type'][$type['id']] = $type['type'];
        }
        $this->view->client_medication_extra = $client_medication_extra;
        //Häufigkeit der Gabe
        $af_i = new Application_Form_Interventions();
        $this->view->frequenz_ui = $af_i->create_frequenz_ui('interventions', true, ['intervention' => $intervention]);
        $this->view->leitsymptom_ui = $af_i->create_leitsymptom_ui('interventions', true, ['intervention' => $intervention]);
        //$this->view->a_freguenz;

    }

    /**
     * ISPC-2630 ELSA: Interventions
     * @elena , 07.08.2020 // Maria:: Migration CISPC to ISPC 20.08.2020
     */
    public function nonmedicalinterventionAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $cnt = $this->getRequest()->getParam('cnt', null);
        if($cnt > 0){
            $intervention = Doctrine::getTable('Interventions')->find($cnt);
            $this->view->intervention = $intervention;
        }else{
            $intervention = new Interventions();
        }


        //Häufigkeit der Gabe
        $af_i = new Application_Form_Interventions();
        $this->view->frequenz_ui = $af_i->create_frequenz_ui('interventions', true, ['intervention' => $intervention]);
        $this->view->proceed_ui = $af_i->create_proceed_ui('interventions', false, ['intervention' => $intervention]);
        $this->view->leitsymptom_ui = $af_i->create_leitsymptom_ui('interventions', false, ['intervention' => $intervention]);

    }

    /**
     * ISPC-2630 ELSA. Interventions
     * // Maria:: Migration CISPC to ISPC 20.08.2020
     * @elena, 10.08.2020
     */
    public function saveinterventionAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        //echo 'ajax';

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);


        if($this->getRequest()->isPost() ){

            $rawpost = ($this->getRequest()->getPost());
            $post = $rawpost['interventions'];

            $post['ipid'] = $ipid;
            $post['clientid'] = $clientid;
            $first = $post['first'];
            $first_time = $post['first_time'];
            if(trim($first_time) == ''){
                $first_time = '00:00';
            }

            $first = date_create_from_format('d.m.Y H:i', $first . ' ' . $first_time);
            if($first !== false ){
                $post['first'] = $first->format('Y-m-d H:i:s');
            }


            $last = $post['last'];
            $last_time = $post['last_time'];
            if(trim($last_time) == ''){
                $last_time = '00:00';
            }

            $last_fm = $last . ' ' . $last_time;

            $last_dt = date_create_from_format('d.m.Y H:i', $last_fm);
            if($last_dt !== false){
                $post['last'] = $last_dt->format('Y-m-d H:i:s') ;
            }


            $post['active_ingredient'] = $post['medication']['wirkstoff'];
            $post['preparation'] = $post['medication']['praeparat'];
            //print_r($post);
            $intervenions = new Interventions();
            $retObj = new stdClass();
            $af_i= new Application_Form_Interventions();
            $aErrors = $af_i->validate($post);
            //print_r($aErrors);
            if(!empty($aErrors)){
                $retObj->errors = $aErrors;
                $retObj->success = false;
                $retObj->action = 'rejected';

            }else{
                if(!isset($post['id']) || intval($post['id']) == 0){
                    $af_i->InsertData($post);
                    $retObj->errors = [];
                    $retObj->success = true;
                    $retObj->action = 'inserted';

                }else{

                    $af_i->UpdateData($post);
                    $retObj->errors = [];
                    $retObj->action = 'updated';
                    $retObj->success = true;
                }


            }


            echo json_encode($retObj);
            exit();




        }




    }
    /**
     * ISPC-2630 ELSA. Interventions
     * // Maria:: Migration CISPC to ISPC 20.08.2020
     * @elena, 10.08.2020
     */
    public function deleteinterventionAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        //echo 'ajax';

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);


        if($this->getRequest()->isPost() ){

            $intervention_id = $_POST['intervention_id'];
            $interventions = new Interventions();
            $interventions->id = $intervention_id;
            $retObj = new stdClass();
            $interventions->deleteIntervention();
            $retObj->success = true;
            $retObj->action = 'deleted';


            echo json_encode($retObj);
            exit();

        }
    }


    /**
     * ISPC-2657, Elena, 26.08.2020, ELSA: Reaktionen
     * Maria:: Migration CISPC to ISPC 02.09.2020
     */
    public function reactionAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $cnt = $this->getRequest()->getParam('cnt', null);
        $blockname = $this->getRequest()->getParam('blockname', null);
        $typ = $this->getRequest()->getParam('typ', null); // allergy/intolerance
        $this->view->cnt = $cnt;


        if($cnt > 0){
            $reaction = Doctrine::getTable('Reactions')->find($cnt);
            $options = ['date' => $reaction->first_diagnosis_date, 'date_knowledge' => $reaction->first_diagnosis_date_knowledge];

        }else{
            $reaction = new Reactions();
            $reaction->id = 0;
            $reaction->typ = ($typ != null) ? $typ : 'allergy';
            $options = [];
        }
        $this->view->reaction = $reaction;


        $af_r = new Application_Form_Reactions();
        $this->view->date_ui = $af_r->create_date_ui('reactions', 'first_diagnosis_date', $options);


    }

    /**
     * ISPC-2657, Elena, 26.08.2020, ELSA: Reaktionen
     *
     */
    public function savereactionAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        //echo 'ajax';

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);


        if ($this->getRequest()->isPost()) {

            $rawpost = ($this->getRequest()->getPost());
            $post = $rawpost['reactions'];
            //print_r($post);

            $post['ipid'] = $ipid;
            $post['clientid'] = $clientid;
            $first = $post['first_diagnosis_date'];
            if(intval($post['id']) == 0){
                unset($post['id']);
            }
            if($post['first_diagnosis_date_knowledge'] == 'year only'){
                $first = '01.01.' . $first;
            }else if($post['first_diagnosis_date_knowledge'] == 'year and month only'){
                $first = '01.' . $first;
            }
            $post['first_diagnosis_date'] = date_format( date_create_from_format('d.m.Y', $first), 'Y-m-d');

//print_r($post);
            $retObj = new stdClass();
            $af_i = new Application_Form_Reactions();
            $aErrors = [];
            $aErrors = $af_i->validate($post);
            if(!$post['first_diagnosis_date']){
                $Tr = new Zend_View_Helper_Translate();
                $aErrors['date'] = $Tr->translate('reactions_date_error');
            }
            //print_r($aErrors);
            //print_r($post);
            //exit();
            if (!empty($aErrors)) {
                $retObj->errors = $aErrors;
                $retObj->success = false;
                $retObj->action = 'rejected';

            } else {
                if (!isset($post['id']) || intval($post['id']) == 0) {
                    $af_i->InsertData($post);
                    $retObj->errors = [];
                    $retObj->success = true;
                    $retObj->action = 'inserted';

                } else {

                    $af_i->UpdateData($post);
                    $retObj->errors = [];
                    $retObj->action = 'updated';
                    $retObj->success = true;
                }

            }

            echo json_encode($retObj);
            exit();

        }
    }

    /**
     * ISPC-2657, Elena, 27.08.2020, ELSA: Reaktionen
     *
     */
    public function deletereactionAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        //echo 'ajax';

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);


        if($this->getRequest()->isPost() ){

            $reaction_id = $_POST['reaction_id'];
            $reaction = new Reactions();
            $reaction->id = $reaction_id;
            $retObj = new stdClass();
            $reaction->deleteReaction();
            $retObj->success = true;
            $retObj->action = 'deleted';


            echo json_encode($retObj);
            exit();

        }
    }

    /**
     * ISPC-2657, Elena, 27.08.2020, ELSA: Reaktionen
     */
    public function saereactionAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $cnt = $this->getRequest()->getParam('cnt', null);
        $blockname = $this->getRequest()->getParam('blockname', null);
        $typ = $this->getRequest()->getParam('typ', null); // allergy/intolerance
        $this->view->cnt = $cnt;


        if($cnt > 0){
            $saereaction = Doctrine::getTable('SaeReactions')->find($cnt);
            $options = ['date' => $saereaction->first_sae_date, 'date_knowledge' => $saereaction->first_sae_date_knowledge];

        }else{
            $saereaction = new SaeReactions();
            $saereaction->id = 0;

            $options = [];
        }
        $this->view->saereaction = $saereaction;


        $af_r = new Application_Form_Reactions();
        $this->view->date_ui = $af_r->create_date_ui('reactions', 'first_sae_date', $options);


    }
    /**
     * ISPC-2657, Elena, 27.08.2020, ELSA: Reaktionen
     *
     */
    public function savesaereactionAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        //echo 'ajax';

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);


        if ($this->getRequest()->isPost()) {

            $rawpost = ($this->getRequest()->getPost());
            $post = $rawpost['reactions'];

            $post['ipid'] = $ipid;
            $post['clientid'] = $clientid;
            $first = $post['first_sae_date'];
            if(intval($post['id']) == 0){
                unset($post['id']);
            }
            $post['first_sae_date'] = date_format( date_create_from_format('d.m.Y', $first), 'Y-m-d');

//print_r($post);
            $retObj = new stdClass();
            $af_i = new Application_Form_Reactions();
            $aErrors = $af_i->validate($post);

            if(!$post['first_sae_date']){
                $Tr = new Zend_View_Helper_Translate();
                $aErrors['date'] = $Tr->translate('reactions_date_error');
            }
            //print_r($aErrors);
            //print_r($post);
            //exit();
            if (!empty($aErrors)) {
                $retObj->errors = $aErrors;
                $retObj->success = false;
                $retObj->action = 'rejected';

            } else {
                if (!isset($post['id']) || intval($post['id']) == 0) {
                    $af_i->InsertData($post);
                    $retObj->errors = [];
                    $retObj->success = true;
                    $retObj->action = 'inserted';

                } else {

                    $af_i->UpdateData($post);
                    $retObj->errors = [];
                    $retObj->action = 'updated';
                    $retObj->success = true;
                }

            }

            echo json_encode($retObj);
            exit();

        }
    }

    /**
     * ISPC-2657, Elena, 27.08.2020, ELSA: Reaktionen
     *
     */
    public function deletesaereactionAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        //echo 'ajax';

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);


        if ($this->getRequest()->isPost()) {

            $reaction_id = $_POST['saereaction_id'];
            $reaction = new SaeReactions();
            $reaction->id = $reaction_id;
            $retObj = new stdClass();
            $reaction->deleteSaeReaction();
            $retObj->success = true;
            $retObj->action = 'deleted';


            echo json_encode($retObj);
            exit();

        }
    }
    


    //ISPC-2664 Carmen 28.09.2020
    public function setvitalsignselementAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    
    	if($_POST['element'] == 'weight')
    	{
    		$data['weight'] = $_POST['element_value'];
    	}
    	elseif($_POST['element'] == 'height')
    	{
    		$data['height'] = $_POST['element_value'];
    	}
    	
    	$decid = Pms_Uuid::decrypt($_POST['ipid']);
    	$ipid = Pms_CommonData::getIpId($decid);
  
    	$data['id'] = null;
    	$data['ipid'] = $ipid;
    	$data['source'] = 'medication';
    	$data['signs_date'] = date('Y-m-d H:i:s', time());
    
    	$newentity = FormBlockVitalSignsTable::getInstance()->createIfNotExistsOneBy(array('id', 'ipid'), array($data['id'], $data['ipid']), $data);
    
    	echo date('d.m.Y', strtotime($newentity->signs_date));
    	exit;
    }
    //--
    
	//ISPC-2654 Carmen 12.10.2020
    public function loadmrelogAction()
    {
    	$this->_helper->layout->setLayout('layout_ajax');
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$user = new User();
		
		$clientusers = $user->getUserByClientid($clientid, 0, true, false);
		$clientusersdet = array();
		foreach($clientusers as $clus)
		{
			$clientusersdet[$clus['id']] = $clus['user_title'] . " " . $clus['last_name'] . ", " . $clus['first_name'];
		}
		
		$this->view->users =  $clientusersdet;
		
    	$decid = Pms_Uuid::decrypt($_REQUEST['patid']);
    	$ipid = Pms_CommonData::getIpId($decid);
    	$recordid = $_REQUEST['mreid'];
    	
    	$patmrelog = PatientCourseTable::getInstance()->findMrelog(array($ipid), $recordid, PatientMre::PATIENT_COURSE_TABNAME);
    	
    	foreach($patmrelog as $k_log => $v_log)
    	{
    		$vlog_date = strtotime(date('Y-m-d H:i', strtotime($v_log['create_date'])));
    		$history_log[$vlog_date.'-'.$v_log['create_user']][] = $v_log;
    		//$mreids[] = $v_log['recordid'];
    	}

    	$this->view->history_log = $history_log;
    	
    	$response['msg'] = "Success";
    	$response['error'] = "";
    	$response['callBack'] = "callback_history";
    	$response['callBackParameters'] = array();
    	$response['callBackParameters']['mreid'] = $_REQUEST['mreid'];//passthrough the receipt id
    	$response['callBackParameters']['historylog'] = $this->view->render('ajax/loadmrelog.html');
    	 
    	echo json_encode($response);
    	exit;
    }
    //--
 /**
     * @elena, ISPC-2539, 29.10.2020
     *
     * removes "Verlauf" entry in SapvVerordnung for primary_set or secondary_set
     */
    public function removeverordnungjournalAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        if ($this->getRequest()->isPost()) {

            $journalid = $this->getRequest()->getPost('journalid');
            VerordnungSetStatusHistory::setHistoryEntryDeleted($journalid, $ipid);
            $retObj = new stdClass();
            $retObj->success = true;
            $retObj->action = 'deleted';

            echo json_encode($retObj);
            exit();

        }
    }



   /**
     * @elena, ISPC-2697, 30.10.2020
     */
    public function beatmungsoptionAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $machinename = $_POST['machine'];
        //print_r($_POST);

        $optionnumber = intval($_GET['optnumber']);

        $machine = Doctrine::getTable('Machine')->find($_GET['id']);

        $oMachine = new Machine();
        $machines =$oMachine->getClientMachinesForType($clientid, 'beatmung');


        //$savedoptions = $machines[$machine];
        //print_r($sets);
        $counter = 0;
        $editingoption = null;
        $sets = json_decode($machine->parameters, true);

        foreach($sets as $opt){
            if($counter == intval($optionnumber)){
                //echo 'found';
                $editingoption = $opt;

            }

            $counter++;
        }
        $this->view->editingoption = $editingoption;
        $this->view->optionnumber = $optionnumber;
        $this->view->machine = $machine;


    }


    /**
     * @elena, ISPC-2697, 30.10.2020
     */
    public function beatmungsformAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;

        $opt = $_GET['opt'];
        $machines = json_decode(ClientConfig::getConfig($clientid, 'beatmung'), true);
        $machine = Doctrine::getTable('Machine')->find($_GET['opt']);
        $this->view->machine = $machine;
        $this->view->machine_opt = $opt;
        $this->view->blockname = $_POST['blockname'];
    }


    /**
     * @elena, ISPC-2697, 13.11.2020
     */
    public function anordnungAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $oMachine = new Machine();
        $this->view->machines = $oMachine->getClientMachinesForType($clientid,'beatmung');
        $this->view->machine_opt = $_GET['device'];

        $editing_anordnung = $_GET['editing_anordnung'];
        if($editing_anordnung > 0){
            $entity = new Anordnung();
            $anordnung = $entity->getTable()->find($editing_anordnung, Doctrine_Core::HYDRATE_ARRAY );
            $this->view->anordnung = $anordnung;
            $this->view->machine_chosen = true;
            $machine = $oMachine->getTable()->find($anordnung['machine'],  Doctrine_Core::HYDRATE_ARRAY);
            $this->view->machine = $machine;

        }
        //print_r($anordnung->getSql());



    }

    /**
     * @elena, ISPC-2697, 23.11.2020
     */
    public function anordnungdeviceAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $oMachine = new Machine();
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);

        $other_machines = $oMachine->getClientMachinesWithoutType($clientid, 'beatmung');
        $other_anordnungen = Anordnung::getPatientAnordnungenWithout($ipid, 'beatmung');
        $aBusyMachines = [];
        $other_machines_new = [];

        foreach($other_anordnungen as $o_anord){
            $aBusyMachines[] = $o_anord['machine'];
        }

        foreach($other_machines as $other){
            if(!in_array($other['id'], $aBusyMachines)){
                $other_machines_new[] = $other;
            }
        }
        $this->view->machines = $other_machines_new;// $oMachine->getClientMachinesWithoutType($clientid, 'beatmung');
        $this->view->machine_opt = $_GET['device'];
        $this->view->machine_types = Machine::getTypes();

        $editing_anordnung = $_GET['editing_anordnung'];
        if($editing_anordnung > 0){
            $entity = new Anordnung();
            $anordnung = $entity->getTable()->find($editing_anordnung, Doctrine_Core::HYDRATE_ARRAY );
            $this->view->anordnung = $anordnung;
            $this->view->machine_chosen = true;
            $machine = $oMachine->getTable()->find($anordnung['machine'],  Doctrine_Core::HYDRATE_ARRAY);
            $this->view->machine = $machine;

        }
        //print_r($anordnung->getSql());



    }

    /**
     * @elena, ISPC-2697, 13.11.2020
     *
     * @throws Exception
     */
    function saveanordnungAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost('anordnung');
            $anordnung_id =  intval($post['anordnung_id']);
            $cust = new Anordnung();
            if($anordnung_id > 0){
                $entity = new Anordnung();
                $cust = $entity->getTable()->find($anordnung_id, Doctrine_Core::HYDRATE_RECORD) ;
            }
            //ISPC-2846,Elena,09.03.2021
            if(isset($post['parent_id'])){
                $cust->parent = $post['parent_id'];
            }
            $cust->color = $post['anordnung_color'];
            $cust->description = $post['description'];
            $cust->ipid = $ipid;
            $cust->clientid = $clientid;
            $cust->name = $post['anordnung_name'];
            $cust->parameters = json_encode($post['beatmung']);
            $cust->machine = $post['machine'];
            $oMaschine = new Machine();
            $machine =  $oMaschine->getTable()->find($post['machine'],  Doctrine_Core::HYDRATE_ARRAY);
            $cust->anordnung_type = $machine['machine_type'];
            //ISPC-2906,Elena,27.04.2021
            if(intval($post['set_time']) == 1){
                $cust->timelinedata = '';
            }
            
            if($anordnung_id == 0){
                $cust->save();
            }else{
                $cust->replace();
            }
            //<!-- ISPC-2816,Elena,12.02.2021-->
            //ISPC-2906,Elena,27.04.2021
            if(intval($post['set_time']) == 1){
                
                //if((intval($post['set_time']) == 1) && ($post['hours_from'] != '') &&($post['hours_till'] != '')){ //ISPC-2906,Elena,27.04.2021//bugfix: 0 was interpreted as empty - Elena
                //$anordnung = new Anordnung();
                //ISPC-2906,Elena,27.04.2021
                $timelinecounter = 0;
                foreach ($post['hours_from'] as $hours_from) {
                    //ISPC-2906,Elena,27.04.2021
                    $hours_till = $post['hours_till'][$timelinecounter];
                    //don't save and compare time parts if from and till are equal
                    if ($hours_from < $hours_till) {
                        $cust->rearrangeTimeline($ipid, $cust->id, $hours_from, $hours_till);
                    } elseif($hours_from != $hours_till) {
                        $first_from = 0;
                        $first_till = $hours_till;
                        
                        $second_from = $hours_from;
                        $second_till = 24;
                        $cust->rearrangeTimeline($ipid, $cust->id, $first_from, $first_till);
                        $cust->rearrangeTimeline($ipid, $cust->id, $second_from, $second_till);
                    }
                    $timelinecounter ++; //ISPC-2906,Elena,27.04.2021
                    
                }//ISPC-2906,Elena,27.04.2021
            }
            

            $retObj = new stdClass();
            $retObj->success = true;
            $retObj->action = 'updated';

            echo json_encode($retObj);
            exit();

        }

    }


    /**
     * ISPC-2697, elena, 16.11.2020
     */
    public function saveanordnungtimelineAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->_helper->layout->setLayout('layout_ajax');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();
            $anordnung = new Anordnung();
            $anordnung->rearrangeTimeline($ipid, $post['current_anordnung'], $post['hours_from'], $post['hours_till']);
            $retObj = new stdClass();
            $retObj->success = true;
            $retObj->action = 'updated';

            echo json_encode($retObj);
            exit();

        }

    }

    /**
     * ISPC-2697, elena, 16.11.2020
     */
    public function showanordnungeninactiveAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        $anordnungen = Anordnung::getPatientBeatmungAnordnungenInactive($ipid);
        $this->view->anordnungen = $anordnungen;


    }

    /**
     * ISPC-2697, elena, 18.11.2020
     */
    public function removeanordnungAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->_helper->layout->setLayout('layout_ajax');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();
            $anordnung = new Anordnung();
            $anordnung->id = $post['to_remove'];
            $anordnung->remove();

            $retObj = new stdClass();
            $retObj->success = true;
            $retObj->action = 'removed';

            echo json_encode($retObj);
            exit();

        }

    }
   /**
     * ISPC-2697, elena, 18.11.2020
     */
    public function deactivateanordnungAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->_helper->layout->setLayout('layout_ajax');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();
            //print_r($post['to_deactivate']);
            //echo 'all';
            //print_r($post);
            $anordnung = new Anordnung();
            $anordnung->id = intval($post['to_deactivate']);
            $anordnung->deactivate();

            $retObj = new stdClass();
            $retObj->success = true;
            $retObj->action = 'removed';

            echo json_encode($retObj);
            exit();

        }

    }

    /**
     * ISPC-2697, elena, 18.11.2020
     */
    public function activateanordnungAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->_helper->layout->setLayout('layout_ajax');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $aToActivate = $post['anordnung_inactive'];
            Anordnung::groupactivate($aToActivate);

            $retObj = new stdClass();
            $retObj->success = true;
            $retObj->action = 'updated';

            echo json_encode($retObj);
            exit();

        }

    }
    
    //ISPC-2746 Carmen 10.12.2020
    public function sgbxicontactformsAction()
    {
    	$this->_helper->layout->setLayout('layout_ajax');
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;    	

    	//$decid = Pms_Uuid::decrypt($_REQUEST['patient']);
    	$ipid = Pms_CommonData::get_ipid_from_epid($_REQUEST['patient'], $clientid);
    	
    	$form_types = new FormTypes();
    	$formtypes = $form_types->get_form_types($clientid);
    	
    	foreach($formtypes as $k_type => $v_type)
    	{
    		$form_types_arr[$v_type['id']] = $v_type['name'];
    	}
    	
    	$period['start'] = $_REQUEST["period_start"];
    	$period['end'] = $_REQUEST["period_end"];
    	
    	$sgbxi_invoices = SgbxiInvoices::get_period_patients_sgbxi_invoices($ipid, $clientid, $period);
    	
    	foreach($sgbxi_invoices as $k_invoice => $v_invoice)
    	{
    		$invoiced_contact_forms[$v_invoice['ipid']][] = $v_invoice['contact_form_id'];
    		$invoiced_contact_forms[$v_invoice['ipid']] = array_unique($invoiced_contact_forms[$v_invoice['ipid']]);
    	}
    	
    	$dropSGBXI = Doctrine_Query::create()
    	->select('*')
    	->from('ContactForms')
    	->whereIn('ipid', array($ipid))
    	->andWhere(' DATE(billable_date) BETWEEN DATE("' . date('Y-m-d', strtotime($_REQUEST["period_start"])) . '") AND DATE("' . date('Y-m-d', strtotime($_REQUEST["period_end"])) . '")  OR  DATE(end_date) BETWEEN DATE("' . date('Y-m-d', strtotime($_REQUEST["period_start"])) . '") AND DATE("' . date('Y-m-d', strtotime($_REQUEST["period_end"])) . '")  OR (  DATE(start_date) <= DATE("' . date('Y-m-d', strtotime($_REQUEST["period_start"])) . '")  AND DATE(end_date) >= DATE("' . date('Y-m-d', strtotime($_REQUEST['period_end'])) . '")) ')
    	->andWhere('start_date != "0000-00-00 00:00:00"')
    	->andWhere('end_date != "0000-00-00 00:00:00"')
    	->andWhere('isdelete=0')
    	->andWhere('sgbxi_quality="1"')
    	->andWhere('parent="0"')
    	->orderBy('start_date ASC');
    	$alowed_sgbxi = $dropSGBXI->fetchArray();
    	
    	foreach($alowed_sgbxi as $k_sgbxi => $v_sgbxi)
    	{
    		$patients_with_sgbxi[] = $v_sgbxi;
    	}
    	
    	$html_visit = '<div id="visit-'.str_replace("=", "", $_REQUEST["patient"]).'" class="visits">';		

		if(!empty($patients_with_sgbxi))
		{
			$html_visit .= '<select name="sel_visit" class="visit_selector" id="visit-'.str_replace("=", "", $_REQUEST["patient"]).'-sel" rel="'.$_REQUEST["patient"].'" style="width:302px;">';
			$html_visit .= '<option value="0">'.$this->view->translate("select_visit").'</option>';
			foreach($patients_with_sgbxi as $k_visit => $v_visit)
			{
				if(in_array($v_visit['id'],$invoiced_contact_forms[$v_visit['ipid']]))
				{
					$html_visit .= '<option value="'.$v_visit["id"].'">'.$form_types_arr[$v_visit["form_type"]].': '.date("d.m.Y", strtotime($v_visit["start_date"])).' '. '('.date("H:i", strtotime($v_visit["start_date"])).' - '.date("H:i", strtotime($v_visit["end_date"])).') *</option>';
				}
				else 
				{
					$html_visit .= '<option value="'.$v_visit["id"].'">'.$form_types_arr[$v_visit["form_type"]].': '.date("d.m.Y", strtotime($v_visit["start_date"])).' '. '('.date("H:i", strtotime($v_visit["start_date"])).' - '.date("H:i", strtotime($v_visit["end_date"])).') </option>';
				}
			}
			$html_visit .= '</select>';
			$html_visit .= '<p style="font-size: 10px;">'.$this->view->translate("sgbxi_asterix_note").'</p>';
		}
		else
		{
			$html_visit .= '<br />';
			$html_visit .= $this->view->translate("no_visits_found");   	
    	}
    	$html_visit .= '</div>';
    	echo $html_visit;
    	exit;
    }
    //--
    

    /**
     * IM-5,elena,10.12.2020 // ISPC-2800 ?? 
     */
    public function whiteboxhistoryAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);      //id-ul ipidului
        $ipid = Pms_CommonData::getIpId($decid);
        $whitebox = new PatientWhitebox();
        $history_data = $whitebox->getPatientWhiteBoxHistory($ipid);
        $aHistory = [];
        $aUsersIds = [];
        $prevText = '';
        foreach($history_data as $data){
            $text = '';
            $toShow = false;

            if(strlen(trim($data['whitebox'])) > 0){
                $aBox = json_decode($data['whitebox'], true);
                $text = trim($aBox['whitebox']);
                if($text != $prevText){
                    $toShow = true;
                    $prevText = $text;
                }
            }
            if($toShow){ //show the changes only
                $history = [];
                $create_timestamp = strtotime($data['create_date']);
                $create_date = date('d.m.Y H:i', $create_timestamp);
                $history['create_date'] = $create_date;
                $history['create_user'] = $data['create_user'];
                $history['whitebox'] = $text;

                $aUsersIds[] = $data['create_user'];
                $aHistory[$create_timestamp] = $history;
            }

        }
        krsort($aHistory);

        $aUsers = User::getMultipleUserDetails($aUsersIds);
        $this->view->history_data = $aHistory;
        $this->view->aUsers = $aUsers;

    }



    /**
     * ISPC-2381 carmen 12.01.2021 add extrafields to hilfsmittel elsa
     */
    
    public function createpatientaidsextrafieldsAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	$parent_form = $this->getRequest()->getParam('parent_form');
    
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    	 
    	$aidid = $_REQUEST['aidid'];
    	$belongsTo = $_REQUEST['belongsTo'];
    	
    	$af = new Application_Form_PatientAids();
    	$extraf = $af->create_patient_aids_extrafields([
    			'belongsTo' => $belongsTo,
    			'aidid' => $aidid,
    			'values' => $values,
    	], $parent_form);
    
    	$this->getResponse()->setBody($extraf)->sendResponse();
    
    	exit;
    }
    
    public function createpatientaidsadditionalrowAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	$parent_form = $this->getRequest()->getParam('parent_form');
    	
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    	//ISPC-2661 Carmen
    	$af = new Application_Form_PatientAids();

		$row = $af->create_form_patient_aids_for_add('add', $parent_form."[new_". uniqid(). "]");

    	//$row = $af->create_form_additional_row(array(), $parent_form."[new_". uniqid(). "]");
    	//--
    	$row->clearDecorators()->addDecorators( array(
    			'FormElements',));
    
    	$this->getResponse()->setBody($row)->sendResponse();
    
    	exit;
    }
    //--

    /**
     * ISPC-21797 Ancuta 24.02.2021
     * @throws Exception
     */
    public function processplannedmedicationAction(){
        
        if ( ! $this->getRequest()->isXmlHttpRequest()) {
            throw new Exception('!isXmlHttpRequest', 0);
        }
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        if(empty($_REQUEST['drugplan_id']) || empty($_REQUEST['patient'])){
            return;
        } else{
            $drugplan_id = $_REQUEST['drugplan_id'];
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);      //id-ul ipidului
            $ipid = Pms_CommonData::getIpId($decid);
        }
        
        $pm =  new PatientDrugplanPlanning();
        $processed = $pm->proccess_planned_medications($clientid,$userid,$drugplan_id,$ipid);
        
        if($processed){
            echo '1';
        } else {
            echo '0';
        }
        
        exit();
       
    }

    /**
     * TODO-3837 Ancuta 25.03.2021
     */
    public function savepatientvisitnumberAction() {
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        if(!empty($_REQUEST['hl7']) && !empty($_REQUEST['id'])){
            
            $decid = Pms_Uuid::decrypt($_REQUEST['id']);
            $ipid = Pms_CommonData::getIpId($decid);
 
            PatientVisitnumberTable::getInstance()->findOrCreateOneBy(
                
                //search fields
                ['ipid', 'id', 'admit_date'],
                
                //search values
                [$ipid, $_REQUEST['hl7']['id'], $_REQUEST['hl7']['admin_date']],
                
                //data
                [
                    "visit_number"   =>  $_REQUEST['hl7']['visit_number'],
                    "ignore_number"  => isset($_REQUEST['hl7']['ignore_number']) && $_REQUEST['hl7']['ignore_number'] == 1  ? "1" : "0"
                ]
                );
        }
        
        $this->redirect(APP_BASE . "patientnew/patientdetails?id=" . $_GET['id'] , array("exit"=>true));
        exit; //for readability
        
    }
    
}




