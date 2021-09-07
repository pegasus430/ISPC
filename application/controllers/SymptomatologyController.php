<?php

class SymptomatologyController extends Zend_Controller_Action
{
	public function init()
	{
		/* Initialize action controller here */
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if(!$logininfo->clientid)
		{
			//redir to select client error
			$this->_redirect(APP_BASE . "error/noclient");
			exit;
		}
	}

	public function addsymptomAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE."error/previlege");
			}

			$split = explode(".",$_POST['entry_date']);
			$post_entry_date = $split[2]."-".$split[1]."-".$split[0];

			$symptomaster_form = new Application_Form_SymptomatologyMaster();
			if($symptomaster_form->validate($_POST))
			{
				$symptomaster_form->InsertData($_POST);

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}else{
				$symptomaster_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if($logininfo->clientid>0)
		{
			$client=$logininfo->clientid;

			$client = Doctrine_Query::create()
			->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
					AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
					,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
					->from('Client')
					->where('id = ?', $client);
			$clientexec = $client->execute();
			$clientarray = $clientexec->toArray();
			$this->view->client_name = $clientarray[0]['client_name'];
			$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly">
			<input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
		}else{
			$this->view->error_message = "<div class='err'>".$this->view->translate('selectclient')."</div>";
		}
	}

	public function editsymptomAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('addsymptom');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE."error/previlege");
			}


			$symptomaster_form = new Application_Form_SymptomatologyMaster();
			if($symptomaster_form->validate($_POST))
			{
				$symptomaster_form->UpdateData($_POST);

				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE.'symptomatology/symptomlist?flg=suc');
			}else{
				$symptomaster_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if(strlen($_GET['id'])>0)
		{
			$alert = Doctrine::getTable('SymptomatologyMaster')->find($_GET['id']);
			$alertarray = $alert->toArray();
			$alertarray['entry_date']=date('d-m-Y',strtotime($alertarray['entry_date']));
			$this->retainValues($alertarray);

			if($clientid>0 || $logininfo->clientid>0)
			{
				if($clientid>0){
					$client=$clientid;
				}else if($logininfo->clientid>0){
					$client=$logininfo->clientid;
				}
					
				$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
						AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
						,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
						->from('Client')
						->where('id = ?', $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
			}
		}else{

			if($logininfo->clientid>0)
			{
				$client=$logininfo->clientid;

				$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
						AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
						,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
						->from('Client')
						->where('id = ?', $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
			}
		}
	}

	public function symptomlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
			
		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
		}
	}



	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		if($logininfo->clientid>0)
		{
			$where = ' and clientid='.$logininfo->clientid;
		}else{
			$where = ' and clientid=0';
		}
			
		$columnarray = array("pk"=>"id","ds"=>"sym_description");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$this->view->{"style".$_GET['pgno']} = "active";

		$symptom = Doctrine_Query::create()
		->select('count(*)')
		->from('SymptomatologyMaster')
		->andWhere('isdelete = 0 '.$where)
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);

		$symptomexec = $symptom->execute();
		$symptomarray = $symptomexec->toArray();
			
		$limit = 50;
		$symptom->select('*');
		$symptom->where('isdelete = 0'.$where);
		$symptom->limit($limit);
		$symptom->offset($_GET['pgno']*$limit);
			
		$symptomlimitexec = $symptom->execute();
		$symptomlimit = $symptomlimitexec->toArray();
			
		$grid = new Pms_Grid($symptomlimit,1,$symptomarray[0]['count'],"listsymptom.html");
		$this->view->symptomgrid = $grid->renderGrid();
		// $this->view->navigation = $grid->navigation("symptomnavigation.html",$limit);
		$this->view->navigation = $grid->dotnavigation("symptomnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['symptomlist'] = $this->view->render('symptomatology/fetchlist.html');
			
		echo json_encode($response);
		exit;
			
			
	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}

	public function deletesymptomAction()
	{
		$this->_helper->viewRenderer('symptomlist');
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'candelete');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		if($this->getRequest()->isPost())
		{
			if(count($_POST['symptom_id'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}

			if($error==0)
			{
				foreach($_POST['symptom_id'] as $key=>$val)
				{
					$thrash = Doctrine::getTable('SymptomatologyMaster')->find($val);
					$thrash->isdelete = 1;
					$thrash->save();
				}
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");

			}
		}
	}

	public function symptomatologypermissionsAction() {
		$logininfo= new Zend_Session_Namespace('Login_Info');
		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("SymptomatologyPermissions")
			->where("clientid= ?", $logininfo->clientid);
			$q->execute();

			if(is_array($_POST['access']))
			{
				foreach($_POST['access'] as $key=>$val)
				{
					if($val == 1){
						$fc = new SymptomatologyPermissions();
						$fc->setid= $key;
						$fc->setorder= $_POST['order'][$key];
						$fc->clientid = $logininfo->clientid;
						$fc->save();
					}
				}
			}
			$this->_redirect(APP_BASE.'symptomatology/symptomatologypermissions');
		}
			
		$sets = Doctrine_Query::create()
		->select("*")
		->from("SymptomatologySets")
		->where("isdelete=0");
		$setarr = $sets->fetchArray();

		$symperm = new SymptomatologyPermissions();
		$perms = $symperm->getClientSymptomatology($logininfo->clientid);
		foreach ($setarr as $key => $sympset){
			$setperm[$key] = $sympset;
			$set = 0;
			foreach($perms as $perm){
				if($perm['setid'] == $sympset['id']){
					$setperm[$key]['access'] = 1;
					$setperm[$key]['order'] = $perm['setorder'];
					$set = 1;
				}
			}
			if($set == 0){
				$setperm[$key]['access'] = false;
				$setperm[$key]['order'] = false;
			}
		}

		$grid = new Pms_Grid($setperm,1,count($setperm),"listsets.html");
		$this->view->listsets = $grid->renderGrid();
	}

	public function setclientsAction()
	{
		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("SymptomatologyPermissions")
			->where("setid= ?", $_GET['setid']);
			$q->execute();

			if(is_array($_POST['clientid']))
			{
				foreach($_POST['clientid'] as $key=>$val)
				{
					$fc = new SymptomatologyPermissions();
					$fc->setid= $_GET['setid'];
					$fc->clientid = $val;
					$fc->save();
				}
			}
			$this->_redirect(APP_BASE.'symptomatology/symptomatologypermissions');
		}
		$set = Doctrine_Query::create()
		->select('*')
		->from("SymptomatologySets")
		->where("id= ?", $_GET['setid']);
		$setarr = $set->fetchArray();

		$q = Doctrine_Core::getTable('SymptomatologyPermissions')->findBy('setid',$_GET['setid']);

		$clarr = array();

		foreach($q->toArray() as $key=>$val)
		{
			$clarr[] = $val['clientid'];

		}

		$q = Doctrine_Query::create()
		->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
				->from('Client')
				->where('isdelete = ?',0);
		$qexec = $q->execute();
		$qarray = $qexec->toArray();
		$grid = new Pms_Grid($qarray,1,count($qarray),"clientlistcheckbox.html");
		$grid->clarr = $clarr;
		$this->view->setarr = $setarr;
		$this->view->listclients = $grid->renderGrid();
	}

	
	public  function clientsymptomgroupslistoldAction(){
	    set_time_limit(0);
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	}
	
	public  function getclientsymptomgroupslistAction(){
	    
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $this->_helper->viewRenderer->setNoRender();
	     
	    if(!$_REQUEST['length'])
	    {
	        $_REQUEST['length'] = "100";
	    }
	    
	    $limit = $_REQUEST['length'];
	    $offset = $_REQUEST['start'];
	    $search_value = $_REQUEST['search']['value'];
	    
	    if(!empty($_REQUEST['order'][0]['column']))
	    {
	        $order_column = $_REQUEST['order'][0]['column'];
	    }
	    else
	    {
	        $order_column = "1";
	    }
	    
	    $order_dir = $_REQUEST['order'][0]['dir'];
	     
	    $columns_array = array(
	        "1" => "groupname"
	    );
	     
	    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
	     
	    // ########################################
	    // #####  Query for count ###############
	    $fdoc1 = Doctrine_Query::create();
	    $fdoc1->select('count(*)');
	    $fdoc1->from('ClientSymptomsGroups');
	    $fdoc1->where("isdelete = ?", 0);
	    $fdoc1->andWhere("clientid = ?", $clientid);
	    /* ------------- Search options ------------------------- */
	    if (isset($search_value) && strlen($search_value) > 0)
	    {
	        $fdoc1->andWhere("groupname like ?","%" . trim($search_value) . "%");
	    }
	    $fdoc1->orderBy($order_by_str);
	    $fdocexec = $fdoc1->execute();
	    $fdocarray = $fdocexec->toArray();
	     
	    $full_count  = $fdocarray[0]['count'];
	     
	    // ########################################
	    // #####  Query for details ###############
	    $sql = '*,';
	    $fdoc1->select($sql);
	    $fdoc1->where("isdelete = 0  ");
	    $fdoc1->andWhere("clientid = ?", $clientid);
	    /* ------------- Search options ------------------------- */
	    if (isset($search_value) && strlen($search_value) > 0)
	    {
	        $fdoc1->andWhere("groupname like ?","%" . trim($search_value) . "%");
	    }
	    $fdoc1->limit($limit);
	    $fdoc1->offset($offset);
	    $fdoclimitexec = $fdoc1->execute();
	    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
	     
	    $report_ids[] = '99999999999999';
	    foreach ($fdoclimit as $key => $report)
	    {
	        $fdoclimit_arr[$report['id']] = $report;
	        $report_ids[] = $report['id'];
	    }
	    
	    $all_users = Pms_CommonData::get_client_users($clientid, true);
	    
	    foreach($all_users as $keyu => $user)
	    {
	        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
	    }
	    
	    
	    $row_id = 0;
	    $link = "";
	    $resulted_data = array();
	    foreach($fdoclimit_arr as $report_id =>$mdata)
	    {
	        $link = '%s ';
	         
	        $resulted_data[$row_id]['groupname'] = sprintf($link,$mdata['groupname']);
	    
	        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'symptomatology/editclientsymptomgroup?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	        $row_id++;
	    }
	     
	    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	    $response['recordsTotal'] = $full_count;
	    $response['recordsFiltered'] = $full_count; // ??
	    $response['data'] = $resulted_data;
	     
	    header("Content-type: application/json; charset=UTF-8");
	    
	    echo json_encode($response);
	    exit;
	    
	}
	

	
	public  function clientsymptomlistoldAction(){
	    set_time_limit(0);
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	}
	
	public  function getclientsymptomlistAction(){
	    
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $this->_helper->viewRenderer->setNoRender();
	     
	    if(!$_REQUEST['length'])
	    {
	        $_REQUEST['length'] = "100";
	    }
	    
	    $limit = $_REQUEST['length'];
	    $offset = $_REQUEST['start'];
	    $search_value = $_REQUEST['search']['value'];
	    
	    if(!empty($_REQUEST['order'][0]['column']))
	    {
	        $order_column = $_REQUEST['order'][0]['column'];
	    }
	    else
	    {
	        $order_column = "1";
	    }
	    
	    $order_dir = $_REQUEST['order'][0]['dir'];
	     
	    $columns_array = array(
	        "1" => "description"
	    );
	     
	    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
	    
	    if (isset($search_value) && strlen($search_value) > 0)
	    {
	        //ISPC 1739
	        $regexp = addslashes(trim($search_value));
	        Pms_CommonData::value_patternation($regexp);
	        	
	        // ###################################
	        // search by Hospice association START
	        // ###################################
	        //search hospice associations
	        $group_q = Doctrine_Query::create()
	        ->select("*")
	        ->from('ClientSymptomsGroups')
	        ->where('clientid = ?', $clientid)
	        //ISPC 1739
	        ->andWhere("isdelete=0 and  groupname REGEXP ?" , $regexp);
	        $group_array = $group_q->fetchArray();
	    
	        $group_ids = array();
	        if($group_array){
	    
	            foreach($group_array as $k=>$haso){
	                $group_ids_str .= '"'.$haso['id'].'",';
	                $group_ids[] = $haso['id'];
	            }
	        }
	    
	        $group_sql="";
	        if(!empty($group_ids)){
	            $group_sql = " OR group_id in (".substr($group_ids_str,0,-1).") ";
	        }
	    
	        // ###################################
	        // search by Hospice association END
	        // ###################################
	    }
	    
	    // ########################################
	    // #####  Query for count ###############
	    $fdoc1 = Doctrine_Query::create();
	    $fdoc1->select('count(*)');
	    $fdoc1->from('ClientSymptoms');
	    $fdoc1->where("isdelete = 0  ");
	    $fdoc1->andWhere("clientid = ?", $clientid);
	    /* ------------- Search options ------------------------- */
	    if (isset($search_value) && strlen($search_value) > 0)
	    {
	        $fdoc1->andWhere("description REGEXP '".$regexp ."' ".$group_sql." ");
	    }
	    $fdoc1->orderBy($order_by_str);
	    $fdocexec = $fdoc1->execute();
	    $fdocarray = $fdocexec->toArray();
	     
	    $full_count  = $fdocarray[0]['count'];
	     
	    // ########################################
	    // #####  Query for details ###############
	    $sql = '*,';
	    $fdoc1->select($sql);
	    $fdoc1->where("isdelete = 0  ");
	    $fdoc1->andWhere("clientid = ?", $clientid);
	    /* ------------- Search options ------------------------- */
		if (isset($search_value) && strlen($search_value) > 0)
	    {
	        $fdoc1->andWhere("description REGEXP '".$regexp ."' ".$group_sql." ");
	    }
	    $fdoc1->limit($limit);
	    $fdoc1->offset($offset);
	    $fdoclimitexec = $fdoc1->execute();
	    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
	     
	    $report_ids[] = '99999999999999';
	    foreach ($fdoclimit as $key => $report)
	    {
	        $fdoclimit_arr[$report['id']] = $report;
	        $report_ids[] = $report['id'];
	    }
	    
	    
	    $s_groups = ClientSymptomsGroups::get_client_symptoms_groups($clientid);
	    
	    
	    $all_users = Pms_CommonData::get_client_users($clientid, true);
	    
	    foreach($all_users as $keyu => $user)
	    {
	        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
	    }
	    
	    
	    $row_id = 0;
	    $link = "";
	    $resulted_data = array();
	    foreach($fdoclimit_arr as $report_id =>$mdata)
	    {
	        $link = '%s ';
	         
	        $resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
	        if($mdata['group_id']!= '0' && !empty($s_groups[$mdata['group_id']])){
    	        $resulted_data[$row_id]['groupname'] = sprintf($link,$s_groups[$mdata['group_id']]['groupname']);
	        } else{
    	        $resulted_data[$row_id]['groupname'] = sprintf($link,'-');
	        }
	    
	        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'symptomatology/editclientsymptom?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	        $row_id++;
	    }
	     
	    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	    $response['recordsTotal'] = $full_count;
	    $response['recordsFiltered'] = $full_count; // ??
	    $response['data'] = $resulted_data;
	     
	    header("Content-type: application/json; charset=UTF-8");
	    
	    echo json_encode($response);
	    exit;
	    
	}
	
	public  function addclientsymptomAction(){

	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid; 
	    
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	    if($this->getRequest()->isPost())
	    {
	        $symptomaster_form = new Application_Form_ClientSymptoms();
	        if($symptomaster_form->validate($_POST))
	        {
	            $symptomaster_form->InsertData($_POST,$clientid);
	            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
	        }else{
	            $symptomaster_form->assignErrorMessages();
	            $this->retainValues($_POST);
	        }
	    }
	    

	    $s_groups = ClientSymptomsGroups::get_client_symptoms_groups($clientid);
	    if($s_groups){
	        $this->view->groups_array = $s_groups;
	    }
	}
	
	
	
	public  function editclientsymptomAction(){
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $this->_helper->viewRenderer('addclientsymptom');
	    
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }

	    // get symptom details 
	    if(strlen($_GET['id'])>0)
	    {
	       $sym_id = $_GET['id'];
	       $sym_details = ClientSymptoms::get_client_symptoms($clientid,$sym_id);
	       if($sym_details){
	           $symptom_details = $sym_details[$sym_id]; 
	       }
	       
	       $this->view->symptom_details = $symptom_details;
	       // get symptom grous details
	       
	       $s_groups = ClientSymptomsGroups::get_client_symptoms_groups($clientid);
	       
	       if($s_groups){
	           $this->view->groups_array = $s_groups;
	       }
	       
	       
	       
    	    if($this->getRequest()->isPost())
    	    {
    	        $symptomaster_form = new Application_Form_ClientSymptoms();
    	        if($symptomaster_form->validate($_POST))
    	        {
    	            $_POST['symid'] = $_GET['id'];
    	            $symptomaster_form->UpdateData($_POST,$clientid);
    	    
    	            $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
    	            $this->_redirect(APP_BASE.'symptomatology/clientsymptomlist?flg=suc&mes='.urlencode( $this->view->error_message));
    	        }else{
    	            $symptomaster_form->assignErrorMessages();
    	            $this->retainValues($_POST);
    	        }
    	    }
	    }
	    
	}
	
	public  function deleteclientsymptomAction(){
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	    if($_GET['id'])
	    {
	         
	        $thrash = Doctrine::getTable('ClientSymptoms')->find($_GET['id']);
	        $thrash->isdelete = 1;
	        $thrash->save();
	         
	        $this->view->error_message = $this->view->translate("recorddeletedsucessfully");
    	    $this->_redirect(APP_BASE.'symptomatology/clientsymptomlist?flg=suc&mes='.urlencode( $this->view->error_message));
	        exit;
	    }
	    
	}
	

	public  function addclientsymptomgroupAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	     
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	     
	    if($this->getRequest()->isPost())
	    {
	        $symptomaster_form = new Application_Form_ClientSymptomsGroups();
	        if($symptomaster_form->validate($_POST))
	        {
	            $symptomaster_form->InsertData($_POST,$clientid);
	            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
	        }else{
	            $symptomaster_form->assignErrorMessages();
	            $this->retainValues($_POST);
	        }
	    }
	    
	}
	
	public  function editclientsymptomgroupAction(){
		    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $this->_helper->viewRenderer('addclientsymptomgroup');
	    
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	    // get group details 
	    if(strlen($_GET['id'])>0)
	    {
	       $gr_id = $_GET['id'];
	       $gr_details =  ClientSymptomsGroups::get_client_symptoms_groups($clientid,$gr_id);
	       if($gr_details){
	           $group_details = $gr_details[$gr_id]; 
	       }
	       
	       $this->view->group_details = $group_details ;
	       
	       
    
    	    if($this->getRequest()->isPost())
    	    {
    	        $_POST['group_id'] = $_GET['id'];
    	        $symptomaster_form = new Application_Form_ClientSymptomsGroups();
    	        if($symptomaster_form->validate($_POST))
    	        {
    	            $symptomaster_form->UpdateData($_POST,$clientid);
    	    
    	            $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
    	            $this->_redirect(APP_BASE.'symptomatology/clientsymptomgroupslist?flg=suc&mes='.urlencode($this->view->error_message));
    	        }else{
    	            $symptomaster_form->assignErrorMessages();
    	            $this->retainValues($_POST);
    	        }
    	    }
	       
	       
	       
	       
	       
	    }
	    
	}
	
	public  function deleteclientsymptomgroupAction(){
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }

	    if($_GET['id'])
	    {
	    
	        $thrash = Doctrine::getTable('ClientSymptomsGroups')->find($_GET['id']);
	        $thrash->isdelete = 1;
	        $thrash->save();
	    
	        $this->view->error_message = $this->view->translate("recorddeletedsucessfully");
    	            $this->_redirect(APP_BASE.'symptomatology/clientsymptomgroupslist?flg=suc&mes='.urlencode($this->view->error_message));
	        exit;
	    }
	    
	    
	}
	
	//get view list symptomatology
	public function clientsymptomlistAction(){
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
					"0" => "description",
					"1" => "groupname"
	
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
			$fdoc1->from('ClientSymptoms');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0  ");
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
	
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*, (SELECT csg.groupname FROM ClientSymptomsGroups csg WHERE csg.id = cs.group_id) as groupname');
			$fdoc1->from('ClientSymptoms cs');
			$fdoc1->andWhere("cs.clientid = ?", $clientid);
			$fdoc1->andWhere("cs.isdelete = 0  ");
	
			$fdoc1->orderBy($order_by_str);
	
			$fdoclimit = $fdoc1->fetchArray();
	
			if(trim($search_value) != "")
			{				
				$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp);
				
					foreach($columns_search_array as $ks=>$vs)
					{
						$pairs[$vs] = trim(str_replace('\\', '',$regexp));
						
					}
					//var_dump($pairs);
					$fdocsearch = array();
					foreach ($fdoclimit as $skey => $sval) {
						foreach ($pairs as $pkey => $pval) {
							$pval_arr = explode('|', $pval);
						
							foreach($pval_arr as $kpval=>$vpval)
							{
								if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) { 
									$fdocsearch[$skey] = $sval;
									break;
								}
							}
							
						}
					
					}
					
					 
					$fdoclimit = $fdocsearch;
			}
			$filter_count  = count($fdoclimit);
	
			if($limit != "")
			{
				$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
			}
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
	
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
				$resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
				$resulted_data[$row_id]['groupname'] = sprintf($link,$mdata['groupname']);
				 
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'symptomatology/editclientsymptom?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	//get view list client symptom groups
	public function clientsymptomgroupslistAction(){
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
					"0" => "groupname"
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
			$fdoc1->from('ClientSymptomsGroups');
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
				//$fdoc1->andWhere("(lower(groupname) like ?)", array("%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['groupname'] = sprintf($link,$mdata['groupname']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'symptomatology/editclientsymptomgroup?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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
