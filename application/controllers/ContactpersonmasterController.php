<?php

class ContactPersonMasterController extends Zend_Controller_Action
{

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
	 
	public function addcontactpersontempAction()
	{
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$modules = new Modules();
		
		if($modules->checkModulePrivileges("102", $clientid)) // primary status : Verordnung
		{
			$this->view->modul_custody = "1";
		}
		else
		{
			$this->view->modul_custody = "0";
		}
		
		$this->view->salutations = Pms_CommonData::getSalutation();
		$fd = new FamilyDegree();
		$this->view->familydegree = $fd->getFamilyDegrees(1);
		$this->view->genders = Pms_CommonData::getGender();
		$this->_helper->layout->setLayout('layout_popup');
		$this->view->regions = Pms_CommonData::getRegions();
		$this->view->closefrm='""';
		 
		if($this->getRequest()->isPost())
		{
			$contact_form = new Application_Form_ContactPersonTempMaster();

			if($contact_form->validate($_POST))
			{
				$cntf  = $contact_form->InsertData($_POST);

				$cntarray = $cntf->toArray();
					
				$jenc = json_encode($cntarray);

				$this->view->closefrm = "setchild($jenc)";

			}
			else
			{
				$contact_form->assignErrorMessages();
				$this->retainValues($_POST);

			}
		} 
	}

	public function edithealthinsuranceAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('healthinsurance',$logininfo->userid,'canedit');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		$this->_helper->viewRenderer('addhealthinsurance');
			
		if($this->getRequest()->isPost())
		{
			$hinsu_form = new Application_Form_HealthInsurance();

			if($hinsu_form->validate($_POST))
			{

				$hinsu_form->UpdateData($_POST);
			}
			else
			{
				$hinsu_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
		if(strlen($_GET['id'])>0)
		{
			$helathins = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
		 $this->retainValues($helathins->toArray());
		 $healtharray = $helathins->toArray();
		 $clientid = $healtharray['clientid'];
		 	
		 $client = Doctrine_Query::create()
		 ->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
		 		AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
		 		,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
		 		->from('Client')
		 		->where('id = '.$clientid);
			$clientexec = $client->execute();
		 $clientarray = $clientexec->toArray();
		 $this->view->client_name = $clientarray[0]['client_name'];
		 $this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientid.'" />';
		 	
		}else{
		 $client = Doctrine_Query::create()
		 ->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
		 		AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
		 		,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
		 		->from('Client')
		 		->where('id = '.$clientid);
			$clientexec = $client->execute();
		 $clientarray = $clientexec->toArray();

		 $this->view->client_name = $clientarray[0]['client_name'];
		}
	}

	private function retainValues($values = array())
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}

	}

	public function contactlistAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Contactpersonmaster',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

	 $this->view->pid = $_GET['id'];
	 $this->view->contclass="active";

	 /********* Patient Information *************/
	 $patientmaster = new PatientMaster();
	 $this->view->patientinfo = $patientmaster->getMasterData($_GET['id'],1);
	 /********************************************/
	}

	public function fetchlistAction()
	{
		$this->_helper->viewRenderer('contactedit');

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('contactpersonmaster',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
			
		$columnarray = array("fn"=>"cnt_first_name","ln"=>"cnt_last_name","ct"=>"cnt_city","ph"=>"cnt_phone","mob"=>"cnt_mob","cnt_email"=>"cnt_email");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$this->view->pid = $_GET['id'];

		$ipid = Pms_CommonData::getIpid($_GET['id']);
	  
		$dtype = Doctrine_Query::create()
		->select('count(*)')
		->from('ContactPersonMaster')
		->where('isdelete=0')
		->andWhere('ipid="'.$ipid.'"')
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$dtypeexec = $dtype->execute();
		$dtypearray = $dtypeexec->toArray();
			
			
		$limit = 50;
		$dtype->select('*');
		$dtype->limit($limit);
		$dtype->offset($_GET['pgno']*$limit);
		 
		$patientlimitexec = $dtype->execute();
		$patientlimit = $patientlimitexec->toArray();
		$dtypearray_count = $dtypearray[0]['count'];
		 
		$grid = new Pms_Grid($patientlimit,1,$dtypearray_count,"listcontactpersons.html");
		$this->view->contactgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("patientnavigation.html",5,$_GET['pgno'],$limit);
		 
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['dtypecount'] = $dtypearray_count;
		$response['callBackParameters']['contactpersonlist'] =$this->view->render('contactpersonmaster/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}

	public function deletecontactAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('contactpersonmaster',$logininfo->userid,'candelete');
		$this->_helper->viewRenderer('contactlist');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			if(count($_POST['cnt_id'])<1){
				$this->view->error_message=$this->view->translate('selectatleastone'); $error=1;
			}
			if($error==0)
			{
				foreach($_POST['cnt_id'] as $key=>$val)
				{
					$mod = Doctrine::getTable('contactpersonmaster')->find($val);
					$mod->isdelete = 1;
					$mod->save();
				}

				$this->view->error_message=$this->view->translate('contactdeletedsuccessfully');
			}
		}

	}
	 
	public function delcontactfromtempAction()
	{
		$this->_helper->viewRenderer('contactlist');

		if($_GET['cid']>0)
		{
			$mod = Doctrine::getTable('ContactPersonTempMaster')->find($_GET['cid']);
			$mod->delete();

			$q = Doctrine_Query::create()
			->delete('ContactPersonTempMaster a')
			->where('a.id = ?', $_GET['cid']);
				
			$q->execute();
		}
		 
	}
	//=================================================================================

	public function addrelationAction()
	{
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
			
		if($this->getRequest()->isPost())
		{ 
			
			$service_form = new Application_Form_FamilyDegree();
			 
				
			if($service_form ->validate($_POST))
			{
				
				$service_form ->InsertData($_POST);
				$curr_id = $service_form ->id;
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$service_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
			 
		}
		 
	}
	 
	public function editrelationAction()
	{
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
			
		$this->view->act = "contactpersonmaster/editrelation?id=" . $_REQUEST['id'];
		 
		$this->_helper->viewRenderer('addrelation');
		if($this->getRequest()->isPost())
		{
			$services_form = new Application_Form_FamilyDegree();
			if($services_form->validate($_POST))
			{
				$a_post = $_POST;
				$a_post['id'] = $_REQUEST['id'];
				 
				$services_form->UpdateData($a_post);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'contactpersonmaster/relation?flg=suc&mes='.urlencode($this->view->error_message));				
			}
			else
			{
				$remedy_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
			 
			 
		}
		 
		if($_REQUEST['id'] > 0)
		{
			$relation_details = FamilyDegree::get_relation($_REQUEST['id']);
			if($relation_details)
			{
				$this->retainValues($relation_details[0]);
				//$this->view->relation_name=$relation_details[0]['relation_name'];
			}
			//echo($this->view->relation_name);exit;
			//var_dump($this->retainValues($servicesf_details [0])); exit;
			 
			$clientid = $relation_details [0]['clientid'];
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
							->where('id = ?' , $client);
				$clientarray = $client->fetchArray();
				 
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
			}
			 
			 
		}
		 
		 
	}
	 
	public function relationoldAction()
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
		->from('FamilyDegree')
		->where('isdelete = ?', 0);
		echo json_encode($fdoc->fetchArray());
		 
		exit;
	}
	 
	public function fetchlistrelationAction()
	{
		$columnarray = array("pk" => "id","rn" => "family_degree");
	
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
		//		print_r($_REQUEST);exit;
		$fdoc1 = Doctrine_Query::create()
		->select('count(*)')
		->from('FamilyDegree')
		->where("isdelete = 0" . $where)
		->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
		$fdocarray = $fdoc1->fetchArray();
		//print_r($fdocarray);exit;
		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete = 0" . $where . "");
		if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("family_degree like ?","%" . trim($_REQUEST['val']) ."%");
		}
		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno'] * $limit);
		 
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
		 
		$this->view->{"style" . $_REQUEST['pgno']} = "active";
		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listrelation.html");
		$this->view->remedygrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("relationnavigation.html", 5, $_REQUEST['pgno'], $limit);
		 
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['relation'] = $this->view->render('contactpersonmaster/fetchlistrelation.html');
		 
		echo json_encode($response);
		exit;
	}
	 
	public function deleterelationAction()
	{
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
			
		//$this->_helper->viewRenderer('relation');
		 
		$trash = Doctrine::getTable('FamilyDegree')->find($_REQUEST['id']);
		$trash->isdelete = 1;
		$trash->save();
		 
		//$this->view->delete_message = "Record deleted sucessfully";
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . 'contactpersonmaster/relation?flg=suc&mes='.urlencode($this->view->error_message));
	}
	
	//get view list family degree
	public function relationAction(){
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
					"0" => "family_degree"
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
			$fdoc1->from('FamilyDegree');
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
				//$fdoc1->andWhere("(lower(family_degree) like ?)", array("%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['family_degree'] = sprintf($link,$mdata['family_degree']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'contactpersonmaster/editrelation?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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