<?php

  class UserpseudogroupController extends Zend_Controller_Action{
  	
  	public function init()
  	{
  		$logininfo = new Zend_Session_Namespace('Login_Info');
  		$this->clientid = $logininfo->clientid;
  		$this->userid = $logininfo->userid;
  	
  		if(!$logininfo->clientid)
  		{
  			//redir to select client error
  			$this->_redirect(APP_BASE . "error/noclient");
  			exit;
  		}
  	}
  	
  	public function addAction()
  	{
  		$logininfo = new Zend_Session_Namespace('Login_Info'); 
  		$clientid = $logininfo->clientid ;
  		
  		if($this->getRequest()->isPost())
  		{
  				
  			$service_form = new Application_Form_UserPseudoGroup();
  			$service_user = new Application_Form_PseudoGroupUsers();
  			 
  			$_POST['pseudo_id'] = '3';
  			//print_r($_POST); exit;
  			if($service_form ->validate($_POST))
  			{
  		
  				$curr_id = $service_form ->InsertData($_POST);
  				if(strlen($curr_id)>0)
  				{
  					$service_user ->InsertData($_POST,$curr_id);
  				}
  		
  				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
  			}
  			else
  			{
  				$service_form->assignErrorMessages();
  				$this->retainValues($_POST);
  		
  			}
  			 
  		}
  		
  		$has_edit_permissions = Links::checkLinkActionsPermission();
  		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
  		{
  			$this->_redirect(APP_BASE . "error/previlege");
  			exit;
  		}
  		$user = new User();
  		$user_detail = $user ->get_client_users($clientid);
  		
  		$usergr = new Usergroup();
  		$usergr_detail = $usergr ->getClientGroups($clientid);
  		

  		foreach($usergr_detail as $k_gr => $v_gr)
  		{
  			foreach($user_detail as $k_us => $v_us)
  			{
  				if($v_us['groupid'] == $v_gr['id'])
  				{
  					$user_groups[$v_gr['id']]['group_name']= $v_gr['groupname'];
  					$user_groups[$v_gr['id']]['users'][$v_us['id']]= $v_us['last_name'].' '.$v_us['first_name'];
  				}
  			}
  		}
  		
  		$this->view->user_groups = $user_groups;
  	
  		$activ_user = PseudoGroupUsers::get_usersgroup();
  
  		foreach($activ_user as $key_us => $value_us)
  		{
  		$ex_user[$key_us]=$value_us['user_id'];
  		}
  		$this->view->exit_user = $ex_user;
  		
  			
  		
  		
  		
  		
  	}
  	
  	public function editAction()
  	{
  		$logininfo = new Zend_Session_Namespace('Login_Info');
  		$clientid = $logininfo->clientid ;
  		
  		$has_edit_permissions = Links::checkLinkActionsPermission();
  		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
  		{
  			$this->_redirect(APP_BASE . "error/previlege");
  			exit;
  		}
  			
  		$this->view->act = "userpseudogroup/edit?id=" . $_REQUEST['id'];
  	
  		$this->_helper->viewRenderer('add');
  		
  		if($this->getRequest()->isPost())
  		{
  			$services_form = new Application_Form_UserPseudoGroup();
  			$service_user = new Application_Form_PseudoGroupUsers();
  			if($services_form->validate($_POST))
  			{
  				$a_post = $_POST;
  				$a_post['id'] = $_REQUEST['id'];
  				$a_post['clientid'] = $clientid;
  	
  				$services_form->UpdateData($a_post);
  				
  				$service_user->UpdateData($a_post);
  				
  				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
  				$this->_redirect(APP_BASE . 'userpseudogroup/list?flg=suc&mes='.urlencode($this->view->error_message));  				
  			}
  			else
  			{
  				$services_form->assignErrorMessages();
  				$this->retainValues($_POST);
  			} 	        
  		}
  		$user = new User();
  		$user_detail = $user ->get_client_users($clientid);
  		
  		$usergr = new Usergroup();
  		$usergr_detail = $usergr ->getClientGroups($clientid);
  		
  		
  		foreach($usergr_detail as $k_gr => $v_gr)
  		{
  			foreach($user_detail as $k_us => $v_us)
  			{
  				if($v_us['groupid'] == $v_gr['id'])
  				{
  					$user_groups[$v_gr['id']]['group_name']= $v_gr['groupname'];
  					$user_groups[$v_gr['id']]['users'][$v_us['id']]= $v_us['last_name'].' '.$v_us['first_name'];
  				}
  			}
  		}
  		$this->view->user_groups = $user_groups;
  		
  		
  		if($_REQUEST['id'] > 0)
  		{
  			$servicesf_details = UserPseudoGroup::get_service($_REQUEST['id']);
  			if($servicesf_details )
  			{
  				$this->retainValues($servicesf_details [0]);
  			}
  	
  			$clientid = $servicesf_details [0]['clientid'];
  			
  			$servicesf_details_user = PseudoGroupUsers::get_users($_REQUEST['id']);
  			
  			foreach($servicesf_details_user as $key => $value)
  			{
  				$ps_user[]=$value['user_id'];
  			}
  			$this->view->ps_user = $ps_user;
  			
  		    $activ_user = PseudoGroupUsers::get_usersgroup();
  
  		    foreach($activ_user as $key_us => $value_us)
  		    {
  			$ex_user[$key_us]=$value_us['user_id'];
  		    }
  		    $this->view->exit_user = $ex_user;
  		   
  			
  			if($clientid > 0 || $this->clientid > 0)
  			{
  				if($clientid > 0)
  				{
  					$client = $clientid;
  				}
  				else if($this->clientid > 0)
  				{
  					$client = $this->clientid;
  				}
  	
  	
  				$client = Doctrine_Query::create()
  				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
  							->from('Client')
  							->where('id = ?', $client);
  				$clientarray = $client->fetchArray();
  	
  				$this->view->client_name = $clientarray[0]['client_name'];
  				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
  			}
  	
  	
  		}
  	
  	
  	}
  	
  	public function listoldAction()
  	{
  		if($_REQUEST['flg'] == 'suc')
  		{
  			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
  		}
  	
  	}
  	
  	public function getjsondataAction()
  	{
  		$fdoc = Doctrine_Query::create()
  		->select('*')
  		->from('UserPseudoGroup')
  		->where('isdelete = ?', 0);
  		echo json_encode($fdoc->fetchArray());
  	
  		exit;
  	}
  	
  	public function fetchlistAction()
  	{
        $columnarray = array("pk" => "id", "name" => "servicesname");
		
        $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
  		
  		if($this->clientid > 0)
  		{
  			$where = ' and clientid=' . $this->clientid;
  		}
  		else
  		{
  			$where = ' and clientid=0';
  		}
//   		print_r($_REQUEST);exit;
  		$fdoc1 = Doctrine_Query::create()
  		->select('count(*)')
  		->from('UserPseudoGroup')
  		->where("isdelete = 0" . $where);
  		if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
  		{
  			$fdoc1->andWhere("servicesname like ?","%" . trim($_REQUEST['val']) ."%");
  		}
  		$fdoc1->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
  		$fdocarray = $fdoc1->fetchArray();
  	
  		$limit = 50;
  		$fdoc1->select('*');
  	  	$fdoc1->where("isdelete = 0" . $where);
  		if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
  		{
  			$fdoc1->andWhere("servicesname like ?","%" . trim($_REQUEST['val']) ."%");
  		}
  		$fdoc1->limit($limit);
  		$fdoc1->offset($_REQUEST['pgno'] * $limit);
  	
  		$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
  	
  		$this->view->{"style" . $_REQUEST['pgno']} = "active";
  		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listservicespseudo.html");
  		$this->view->remedygrid = $grid->renderGrid();
  		$this->view->navigation = $grid->dotnavigation("servicespseudonavigation.html", 5, $_REQUEST['pgno'], $limit);
  	
  		$response['msg'] = "Success";
  		$response['error'] = "";
  		$response['callBack'] = "callBack";
  		$response['callBackParameters'] = array();
  		$response['callBackParameters']['serviceslist'] = $this->view->render('userpseudogroup/fetchlist.html');
  	
  		echo json_encode($response);
  		exit;
  	}
  	
  	public function deleteservicesAction()
  	{
  		$logininfo = new Zend_Session_Namespace('Login_Info');
  		$userid = $logininfo->clientid ;
  		
  		$has_edit_permissions = Links::checkLinkActionsPermission();
  		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
  		{
  			$this->_redirect(APP_BASE . "error/previlege");
  			exit;
  		}
  			
  		//$this->_helper->viewRenderer('list');
  	
  		$trash = Doctrine::getTable('UserPseudoGroup')->find($_REQUEST['id']);
  		$trash->isdelete = 1;
  		$trash->save();
  		
  		$Q = Doctrine_Query::create()
				->update('PseudoGroupUsers')
				->set('isdelete', '1')
				->set('change_date', "'" . date('Y-m-d H:i:s') . "'")
				->set('change_user', "'" . $userid . "'")
				->where("pseudo_id = ?", $_GET['id']);
				$result = $Q->execute();
				
  	
  		//$this->view->delete_message = "Record deleted sucessfully";
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . 'userpseudogroup/list?flg=suc&mes='.urlencode($this->view->error_message));
  	
  	}
  	
  	private function retainValues($values)
  	{
  		foreach($values as $key => $val)
  		{
  			$this->view->$key = $val;
  		}
  	}
  	
  	
  	//get view list servicesname
  	public function listAction(){
  		$logininfo = new Zend_Session_Namespace('Login_Info');
  		$clientid = $logininfo->clientid;
  	
  		//populate the datatables
  		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
  			$this->_helper->layout()->disableLayout();
  			$this->_helper->viewRenderer->setNoRender(true);
  			if(!$_REQUEST['length']){
  				$_REQUEST['length'] = "25";
  			}
  			$limit = (int)$_REQUEST['length'];
  			$offset = (int)$_REQUEST['start'];
  			$search_value = addslashes($_REQUEST['search']['value']);
  	
  			$columns_array = array(
  					"0" => "servicesname"
  			);
  			$columns_search_array = $columns_array;
  			
  			if(isset($_REQUEST['order'][0]['column']))
  			{
  				$order_column = $_REQUEST['order'][0]['column'];
  				$order_dir = $_REQUEST['order'][0]['dir'];
  			}
  			else
  			{
  				array_push($columns_array, "id");
  				$nrcol = array_search ('id', $columns_array);
  				$order_column = $nrcol;
  				$order_dir = "ASC";
  			}
  			
  			$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
  			// ########################################
  			// #####  Query for count ###############
  			$fdoc1 = Doctrine_Query::create();
  			$fdoc1->select('count(*)');
  			$fdoc1->from('UserPseudoGroup');
  			$fdoc1->where("clientid = ?", $clientid);
  			$fdoc1->andWhere("isdelete = 0 ");
  	
  			$fdocarray = $fdoc1->fetchArray();
  			$full_count  = $fdocarray[0]['count'];
  	
  			/* ------------- Search options ------------------------- */
  			if (isset($search_value) && strlen(trim($search_value)) > 0)
			{
				$comma = '';
				$filter_string_all = '';
				
				foreach($columns_search_array as $ks=>$vs)
				{
					$filter_string_all .= $comma.$vs;
					$comma = ',';
				}			
			
				$regexp = trim($search_value);
				Pms_CommonData::value_patternation($regexp);
				
				$searchstring = mb_strtolower(trim($search_value), 'UTF-8');
				$searchstring_input = trim($search_value);
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
				
				$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \','.$filter_string_all.' ) USING utf8 ) REGEXP ?';
				$regexp_arr[] = $regexp;

				//var_dump($regexp_arr);
				$fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
  				//$search_value = strtolower($search_value);
  				//$fdoc1->andWhere("(lower(servicesname) like ?)", array("%" . trim($search_value) . "%"));
  			}
  	
  			$fdocarray = $fdoc1->fetchArray();
  			$filter_count  = $fdocarray[0]['count'];
  	
  			// ########################################
  			// #####  Query for details ###############
  			$fdoc1->select('*');
  			
  			$fdoc1->orderBy($order_by_str);
  			$fdoc1->limit($limit);
  			$fdoc1->offset($offset);
  	
  			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
  	
  			$report_ids = array();
  			$fdoclimit_arr = array();
  			foreach ($fdoclimit as $key => $report)
  			{
  				$fdoclimit_arr[$report['id']] = $report;
  				$report_ids[] = $report['id'];
  			}
  	
  			$row_id = 0;
  			$link = "";
  	
  			$resulted_data = array();
  			foreach($fdoclimit_arr as $report_id =>$mdata)
  			{
  				$link = '%s';
  				$resulted_data[$row_id]['servicesname'] = sprintf($link,$mdata['servicesname']);
  	
  				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'userpseudogroup/edit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
  				$row_id++;
  			}
  	
  			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
  			$response['recordsTotal'] = $full_count;
  			$response['recordsFiltered'] = $filter_count; // ??
  			$response['data'] = $resulted_data;
  	
  			$this->_helper->json->sendJson($response);
  		}
  	
  	}
  	 
  	
  }

?>