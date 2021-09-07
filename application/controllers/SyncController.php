<?php

	class SyncController extends Pms_Controller_Action {

		public function init()
		{
			
		}

		public function listcertifieddevicesAction()
		{
			$certified_devices_form = new Application_Form_CertifiedDevices();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			
			/* if ($logininfo->usertype != 'SA' && $logininfo->usertype != 'CA') {
			    $this->redirect(APP_BASE . "error/previlege", array(
		            "exit" => true
		        ));
		        
		        exit;
			} */

			if($this->getRequest()->isPost())
			{
				$post = $_POST;

				//add device
				if($_POST['save_device_id'] == '1')
				{
					$save = $certified_devices_form->insert($post);
					$this->redirect(APP_BASE . 'sync/listcertifieddevices?flg=suc');
					exit;
				}
				else if($_REQUEST['rid'] > '0' && strlen($_REQUEST['rid']) > '0')
				{
					$save = $certified_devices_form->delete($_REQUEST['rid']);
					$this->redirect(APP_BASE . 'sync/listcertifieddevices?flg=suc');
					exit;
				}
			}
		}

		public function fetchcertifieddeviceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$user_type = $logininfo->usertype;

			
			if ($logininfo->usertype != 'SA' && $logininfo->usertype != 'CA') {
			    $this->redirect(APP_BASE . "error/previlege", array(
			        "exit" => true
			    ));
			
			    exit;
			}
			
			
			//client users & sadmins
			$client_users = User::getUserByClientid($clientid, '1', true);
			$this->view->client_users = $client_users;


			$columnarray = array(
				"did" => "deviceid",
				"user" => "userid",
				"cdate" => "create_date",
				"sdate" => "last_sync",
			);

			if($clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			$devices_q = Doctrine_Query::create()
				->select('*')
				->from('CertifiedDevices')
				->where("isdelete =0 " . $where)
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$devices_res = $devices_q->fetchArray();

			$fdoc = Doctrine_Query::create()
				->select("count(*)")
				->from('CertifiedDevices')
				->where("isdelete = 0 " . $where);
			$fdoc->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$fdocarray = $fdoc->fetchArray();

			$limit = 50;
			$fdoc->select("*");
			$fdoc->where("isdelete = 0 " . $where . "");
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());

			$this->view->{"style" . $_GET['pgno']} = "active";
			if(count($fdoclimit) > '0')
			{
				$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listdevices.html");
				$this->view->templates_grid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("listdevicesnavigation.html", 5, $_REQUEST['pgno'], $limit);
			}
			else
			{
				//no items found
				$this->view->templates_grid = '<tr><td colspan="6" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
				$this->view->navigation = '';
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['deviceslist'] = $this->view->render('sync/fetchcertifieddevice.html');

			echo json_encode($response);
			exit;
		}
		public function userpatientssynclist150803Action()
		{
		    $certified_devices_form = new Application_Form_CertifiedDevices();
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		    $user_type = $logininfo->usertype;
		
		    if($this->getRequest()->isPost())
		    {
		
		        $decrypted_ids = array_map('Pms_Uuid::decrypt', $_POST['sync_patients']);
		
		        if(count($decrypted_ids) == '0')
		        {
		            $decrypted_ids[] = '99999999999';
		        }
		
		        $certified_devices_form->insert_sync_patients($decrypted_ids);
		        $this->redirect(APP_BASE . 'sync/userpatientssynclist?flg=suc');
		        exit;
		    }
		}
		
		public function userpatientssynclistAction()
		{
			$certified_devices_form = new Application_Form_CertifiedDevices();
			//$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$userid = $this->logininfo->userid;
			$clientid = $this->logininfo->clientid;
			$user_type = $this->logininfo->usertype;
			
			$this->view->sync_4_user_nice_info = $logininfo->loguname .  " - ". $logininfo->clientname;			
			
			//get allready sync patients
			$sync_patients = PatientSync::get_patient_sync($clientid, $userid);
			
			//$first_ipids[] = '9999999999';
			//$selected_ipids[] = '9999999999';
			$selected_ipids = array();
			foreach($sync_patients as $k_res => $v_data)
			{
			    $selected_ipids[] = $v_data['ipid'];
			}
			/*if(empty($selected_ipids)){
			    $selected_ipids[] = "XXXXXX";
			}*/
			
			$sync_patientlimit = array();
			if(!empty($selected_ipids))
			{
				$sql = "ipid,e.epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
				$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
				
				// if super admin check if patient is visible or not
				if($user_type == 'SA')
				{
				    $sql = "*,ipid,e.epid as epid,p.isadminvisible,";
				    $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				    $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				}
				$patient_sync_q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where('p.isdelete = 0')
				->andWhereIn('p.ipid',$selected_ipids); // get already selected patients
				$patient_sync_q->leftJoin("p.EpidIpidMapping e");
				$patient_sync_q->andWhere('e.clientid = ?', $clientid);
				$patient_sync_q->orderBy('FIELD(e.ipid, "' . implode('","', $selected_ipids) . '") DESC');
				$patient_sync_array = $patient_sync_q->fetchArray();
					
				foreach($patient_sync_array as $keys => $patient_item_sync)
				{
					$patient_item_sync['enc_id'] = Pms_Uuid::encrypt($patient_item_sync['id']);
				    $sync_patientlimit[$patient_item_sync['ipid']] = $patient_item_sync;
				}
			}
			$this->view->sync_patientlist = $sync_patientlimit;
				
			
			
			if($this->getRequest()->isPost())
			{
			    if($_POST['action'] == 'sync')
			    {
				    $decrypted_ids['selected'] = array_map('Pms_Uuid::decrypt', $_POST['selected']);
				    $decrypted_ids['deselected'] = array_map('Pms_Uuid::decrypt', $_POST['unselected']);
				    
					$certified_devices_form->edit_sync_patients($decrypted_ids, $_POST['action']);
					
					$this->redirect(APP_BASE . 'sync/userpatientssynclist');
					exit;
			    }
			    else 
			    {
			    	$decrypted_ids['selected'] = array_map('Pms_Uuid::decrypt', $_POST['selected']);
			    	
			    	$certified_devices_form->edit_sync_patients($decrypted_ids, $_POST['action']);
			    		
			    	$this->redirect(APP_BASE . 'sync/userpatientssynclist');
			    	exit;
			    }
				
			}
		}

		public function fetchpatientsync150803Action()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');

			$this->view->hidemagic = $hidemagic;
			$previleges = new Pms_Acl_Assertion();

			//get allready sync patients
			$sync_patients = PatientSync::get_patient_sync($clientid, $userid);

			$first_ipids[] = '9999999999';
			foreach($sync_patients as $k_res => $v_data)
			{
				$selected_ipids[] = $v_data['ipid'];
			}

			$this->view->selected_ipids = $selected_ipids;

			$columnarray = array("pk" => "id", "fn" => "first_name", "ln" => "last_name", "ad" => "admission_date", "ledt" => "last_update", "bd" => "birthd", 'ed' => 'epid_num');
			$sorting_array = array('ln' => 'last_name', 'fn' => 'first_name');
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");

			$this->view->order = $orderarray[$_GET['ord']];

			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];


			$user_patients = PatientUsers::getUserPatients($userid); //get user's patients by permission
			$user_c_details = User::getUserDetails($userid);

			if($user_c_details[0]['allow_own_list_discharged'] == '1')
			{
				$show_discharged_not_dead = true;
			}
			else
			{
				$show_discharged_not_dead = false;
			}

			if($user_c_details[0]['assigned_standby'] == '0')
			{
				$standby_q = 'and p.isstandby = 0';
			}
			else
			{
				$standby_q = '';
			}

			$patient = Doctrine_Query::create()
				->select('p.ipid')
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->leftJoin("e.PatientQpaMapping q")
				->where("p.isdelete = 0 " . $standby_q . " and isstandbydelete = 0")
				->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')');

			if($logininfo->usertype != 'SA' || $logininfo->usertype != 'CA')
			{
				$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $clientid . ' and q.userid = ' . $userid);
			}
			else
			{
				$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $clientid);
			}

			if(!$show_discharged_not_dead)
			{
				$patient->andWhere('p.isdischarged = "0"');
			}
			$patientidarray = $patient->fetchArray(); //proper way

			$sql = "ipid,e.epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
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

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*,ipid,e.epid as epid,p.isadminvisible,";
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
			$patient->select($sql);

			if($_REQUEST['clm'] != 'ed')
			{
				$patient->orderBy('FIELD(e.ipid, "' . implode('","', $selected_ipids) . '") DESC, CONVERT(CONVERT(AES_DECRYPT('.$columnarray[$_REQUEST['clm']].', "' . Zend_Registry::get('salt') . '") using utf8) using latin1) COLLATE latin1_german2_ci ' . $orderarray[$_GET['ord']]);
			}
			else
			{
				$patient->orderBy('FIELD(e.ipid, "' . implode('","', $selected_ipids) . '") DESC, '.$columnarray[$_REQUEST['clm']].' '.$orderarray[$_GET['ord']]);
			}
			$patient->groupBy('p.ipid'); //group by ipid
			$patientlimit = $patient->fetchArray(); //proper way
//			print_r($patient->getSqlQuery());	
//			exit;

			$this->view->{"style" . $_GET['pgno']} = "active";
			//get patient sapv statuses END
			foreach($patientlimit as $key => $patient_item)
			{
				$n_patientlimit[$patient_item['ipid']] = $patient_item;
				$n_patientlimit[$patient_item ['ipid']]['patient_epid'] = $patient_item['EpidIpidMapping']['epid'];
			}

			$grid = new Pms_Grid($n_patientlimit, 1, $patientarray[0]['count'], "synclistpatient.html");
			$this->view->patientgrid = $grid->renderGrid();

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['patientslist'] = $this->view->render('sync/fetchpatientsync.html');

			echo json_encode($response);
			exit;
		}
		
		public function fetchpatientsyncAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->_helper->layout->setLayout('layout_ajax');
			$this->view->hidemagic = $hidemagic;
			$previleges = new Pms_Acl_Assertion();

			//get allready sync patients
			$sync_patients = PatientSync::get_patient_sync($clientid, $userid);

			$first_ipids[] = '9999999999';
			foreach($sync_patients as $k_res => $v_data)
			{
				$selected_ipids[] = $v_data['ipid'];
			}

			$this->view->selected_ipids = $selected_ipids;

			$columnarray = array("pk" => "id", "fn" => "first_name", "ln" => "last_name", "ad" => "admission_date", "ledt" => "last_update", "bd" => "birthd", 'ed' => 'epid_num');
			$sorting_array = array('ln' => 'last_name', 'fn' => 'first_name');
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");

			$this->view->order = $orderarray[$_GET['ord']];

			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			/* ----------------- set limits, pages, order, sort ----------------------- */
			$standby_page = false;
			$limit = 50;
			$this->view->limit = $limit;
				
			if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
			{
			    $current_page = $_REQUEST['page'];
			}
			else
			{
			    $current_page = 1;
			}
				
			if($_REQUEST['sort'] == 'desc')
			{
			    $sort = 'desc';
			}
			else
			{
			    $sort = 'asc';
			}

			/* --------------------  set default status -------------------------------- */
			if(strlen($_REQUEST['f_status']) > 0)
			{
			    $reqestedTab = $_REQUEST['f_status'];
			}
			else
			{
			    $_REQUEST['f_status'] = "assignedpats";
			    $reqestedTab = "assignedpats";
			}
			
			/* --------------------  get user settings --------------------------------- */
			
			$user_patients = PatientUsers::getUserPatients($userid); //get user's patients by permission
			$user_c_details = User::getUserDetails($userid);

			if($user_c_details[0]['allow_own_list_discharged'] == '1')
			{
				$show_discharged_not_dead = true;
			}
			else
			{
				$show_discharged_not_dead = false;
			}

			if($user_c_details[0]['assigned_standby'] == '1')
			{
			    $show_assigned_standby = true;
			}
			else
			{
			    $show_assigned_standby = false;
			}
			

			/* --------------------  build filters array based on form input ------------------ */
			$filters = array();
			
			switch($_REQUEST['f_status'])
			{
			
			    case 'active':
			        $filters['patient_master'] = 'p.isdelete = 0 AND p.isstandbydelete=0 AND p.isdischarged = 0 AND p.isstandby = 0 AND p.isarchived = 0';
			        break;
			
			    case 'assignedpats':
			        if($show_assigned_standby)
			        {
			            $standby_q = '';
			        }
			        else
			        {
			            $standby_q = 'and p.isstandby = 0';
			        }
			
			        if($show_discharged_not_dead)
			        {
			            $filters['patient_master'] = 'p.isdelete = 0 ' . $standby_q . ' and p.isstandbydelete = 0';
			        }
			        else
			        {
			            $filters['patient_master'] = 'p.isdischarged = 0 and p.isdelete = 0 ' . $standby_q . ' and p.isstandbydelete = 0';
			        }
			
			        break;
			
 
			    default:
			        $filters['patient_master'] = '1';
			        break;
			}
 
			/* --------- get initial ipids and apply patient master filters ---------------------------- */
			$patient = Doctrine_Query::create()
				->select('p.ipid,e.epid,p.familydoc_id,p.isstandby,p.isdischarged,p.isstandbydelete, p.traffic_status')
				->from('PatientMaster p')
				->where('p.isdelete = 0')
				->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')')
				->andWhere($filters['patient_master']);
			$patient->leftJoin("p.EpidIpidMapping e");

			if($_REQUEST['f_status'] == "assignedpats")
			{
				$patient->leftJoin("e.PatientQpaMapping q");

				if($logininfo->usertype != 'SA')
				{
					$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $logininfo->clientid . ' and q.userid = ' . $logininfo->userid);
				}
				else
				{
					$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $logininfo->clientid);
				}
			}
			else
			{
				$patient->andWhere('e.clientid = ' . $logininfo->clientid);
			}
			$patientidarray = $patient->fetchArray(); //proper way

			
			$sql = "ipid,e.epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
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

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*,ipid,e.epid as epid,p.isadminvisible,";
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

			foreach($patientidarray as $patientid)
			{
			    $patientipidsfinal[] = $patientid['ipid'];
			}
			
			if($show_discharged_not_dead && $_REQUEST['f_status'] == "assignedpats")
			{
			    $patientipidsfinal = array_diff($patientipidsfinal, $dead_patients_ipids);
			    $patientipidsfinal = array_diff($patientipidsfinal, $death_ipids);
			}
			
			$no_patients = sizeof($patientipidsfinal) - 1; //substract dummy error control result
			$no_pages = ceil($no_patients / $limit);
				
			
			switch($_REQUEST['ord'])
			{
			
			    case 'fn':
			        $orderby = 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci ' . $sort;
			        break;
			
			    case 'ln':
			        $orderby = 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci ' . $sort;
			        break;
			
			    case 'dob':
			        $orderby = 'p.birthd ' . $sort;
			        break;
			
			    case 'adm':
			        $orderby = 'p.admission_date ' . $sort;
			        break;
			
			    case 'lastup':
			        $orderby = 'p.last_update ' . $sort;
			        break;
			
			    case 'dis':
			    case 'dot':
			        $orderby = 'FIELD(e__ipid, ' . substr($orderbydischarge_str, 0, -1) . '), e__ipid';
			        break;
			
			    case 'dot':
			        $orderby = 'FIELD(e__ipid, ' . substr($orderbydischarge_str, 0, -1) . '), e__ipid';
			        break;
			
			    case 'loc':
			        $orderby = 'FIELD(e__ipid, ' . substr($orderbylocation_str, 0, -1) . ') ' . $sort . ', e__ipid';
			        break;
			
			    case 'id':
			        $orderby = 'e.epid_num ' . $sort;
			        break;
			
			    default:
			        $orderby = 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci ' . $sort;
			        break;
			}
			
            if(empty($patientipidsfinal)){
                $patientipidsfinal[] = "999999999";
            }
            
			$patient = Doctrine_Query::create()
			     ->select($sql)
			     ->from('PatientMaster p')
			     ->whereIn("p.ipid", $patientipidsfinal)
			     ->leftJoin("p.EpidIpidMapping e")
			     ->andWhere('e.clientid = ' . $logininfo->clientid);
			$patient->orderby($orderby);
			$patient->offset(($current_page - 1) * $limit);
			$patient->limit($limit);
			$patientlimit = $patient->fetchArray(); //proper way

			
			$this->view->{"style" . $_GET['pgno']} = "active";
			foreach($patientlimit as $key => $patient_item)
			{
				$n_patientlimit[$patient_item['ipid']] = $patient_item;
				$n_patientlimit[$patient_item ['ipid']]['patient_epid'] = $patient_item['EpidIpidMapping']['epid'];
				$n_patientlimit[$patient_item['ipid']]['enc_id'] = Pms_Uuid::encrypt($patient_item['id']);
				if(in_array($patient_item['ipid'],$selected_ipids)){
    				$n_patientlimit[$patient_item['ipid']]['sync'] = "1";
				} else{
    				$n_patientlimit[$patient_item['ipid']]['sync'] = "0";
				}
			}
			
			$this->view->patientlist = $n_patientlimit;
			$this->view->current_page = $current_page;
			$this->view->standby_page = $standby_page;
			$this->view->no_pages = $no_pages;
			$this->view->no_patients = $no_patients;
			$this->view->orderby = $_REQUEST['ord'];
			$this->view->sort = $_REQUEST['sort'];
		}

	}

?>