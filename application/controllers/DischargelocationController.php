<?

class DischargelocationController extends Zend_Controller_Action
{
	public function addlocationAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		
		$this->view->locationTypesArray = Pms_CommonData::getDischargeLocationTypes();
		if($this->getRequest()->isPost())
		{
			$location_form = new Application_Form_DischargeLocation();

			if($location_form->validate($_POST))
			{
				$location_form->InsertData($_POST);
				//$this->_redirect(APP_BASE.'dischargelocation/listlocation?flg=suc');
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$location_form->assignErrorMessages();
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
			$this->view->labelClient = '<label for="client_name">'.$this->view->translate('client_name').'</label>';
			$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly">
			<input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
		}else{
			$this->view->inputbox = "<div class='err'>Select Client</div>";
		}
	}

	public function editlocationAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$this->_helper->viewRenderer('addlocation');
		if($this->getRequest()->isPost())
		{
			$location_form = new Application_Form_DischargeLocation();

			if($location_form->validate($_POST))
			{
				$location_form->UpdateData($_POST);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE.'dischargelocation/listlocation?flg=suc&mes='.urlencode($this->view->error_message));
			}else{
				$client_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
		$this->view->locationTypesArray = Pms_CommonData::getDischargeLocationTypes();

		if(strlen($_GET['id'])>0)
		{
			$location = Doctrine_Query::create()
			->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
			->from('DischargeLocation')
			->where('id = ?', $_GET['id']);

			$location->getSqlQuery();
			$locationexec =  $location->execute();

			$disarray = $locationexec->toArray();
			$this->retainValues($disarray[0]);
			$locationarray = $locationexec->toArray();
			$clientid = $locationarray[0]['clientid'];
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
				$this->view->labelClient = '<label for="client_name">'.$this->view->translate('client_name').'</label>';
				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
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
						->where('id =?', $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
			}
		}

	}

	public function listlocationoldAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Dischargelocation',$logininfo->userid,'canview');
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
		$return = $previleges->checkPrevilege('Dischargelocation',$logininfo->userid,'canview');
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

		$columnarray = array("pk"=>"id","lo"=>"d__0");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$location = Doctrine_Query::create()
		->select('count(*)')
		->from('DischargeLocation')
		->where('isdelete = 0 '.$where)
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);

		$locationexec = $location->execute();
		$locationarray = $locationexec->toArray();


		$limit = 50;
		$location->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location");
		$location->limit($limit);
		$location->offset($_GET['pgno']*$limit);
		$this->view->{"style".$_GET['pgno']} = "active";
		$locationlimitexec = $location->execute();
		$locationlimit = $locationlimitexec->toArray();
		$locationTypesArray = Pms_CommonData::getDischargeLocationTypes();

		foreach ($locationlimit as $kloc => $location) {
			if ($location['type'] != 0) {
				$locationlimit[$kloc]['typestr'] = $locationTypesArray[$location['type']];
			} else {
				$locationlimit[$kloc]['typestr'] = "-";
			}
		}


		$grid = new Pms_Grid($locationlimit,1,$locationarray[0]['count'],"listlocation.html");
		$this->view->locationgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("locationdischargednav.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['locationlist'] =$this->view->render('dischargelocation/fetchlist.html');
		echo json_encode($response);
		exit;
	}   private function retainValues($values)

	{

		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}

	public function deletelocationAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		//$this->_helper->viewRenderer('listlocation');
		//$dlocation = Doctrine::getTable('PatientDischarge')->findBy('discharge_location',$_GET['id']);
		//$dlocationarray = $dlocation->toArray();

		//if(count($dlocationarray)<1)
		//{

			$doc = Doctrine::getTable('DischargeLocation')->find($_GET['id']);
			$doc->isdelete = 1;
			$doc->save();

			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'dischargelocation/listlocation?flg=suc&mes='.urlencode($this->view->error_message));

		//}else{
		//	$this->view->error_message = $this->view->translate('youcannotdeletethislocationbecauseitsused');

		//}

	}
	
	//get view list discharge locations
	public function listlocationAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$alc = new PatientDischarge();
			$attlc = $alc->get_patient_dischargelocation_attached();
			$attached_lc = array();
			
			foreach($attlc as $kdm=>$vdm)
			{
				$attached_lc[] = $vdm['discharge_location'];
			}
			
			$location_types_array = Pms_CommonData::getDischargeLocationTypes();
	
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
	
			$columns_array = array(
					"0" => "location_name",
					"1" => "location_type_name"
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
			$fdoc1->from('DischargeLocation');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0  ");
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
				
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*, CONVERT(AES_DECRYPT(location,"' . Zend_Registry::get('salt') . '") using latin1)  as location_name ');
			
			$fdoctotarr = $fdoc1->fetchArray();
			
			if($order_column != "1")
			{
				$fdoc1->orderBy($order_by_str);
			}
		
			$fdoclimit = $fdoc1->fetchArray();
	
			foreach ($fdoclimit as $key=> $row) {
				if($row['type'] != '0')
				{
					$row['location_type_name'] = $location_types_array[$row['type']];
				}
				else 
				{
					$row['location_type_name'] = "";
				}
				$fdoclimit[$key] = $row;
			}
			
			$fdoctotids = array();
			foreach($fdoctotarr as $kfd=>$vfd)
			{
				$fdoctotids[] = $vfd['id'];
			}
			$total_assigned_locations = array_intersect($fdoctotids, $attached_lc);
	
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
			//var_dump($full_count);
				
			if($order_column == "1")
			{				
				$sort_col = array();
    			foreach ($fdoclimit as $key=> $row)
    			{
    				$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
    				$fdoclimit[$key] = $row;
    				$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
    			}
    			if($order_dir == 'desc')
    			{
    				$dir = SORT_DESC;
    			}
    			else
    			{
    				$dir = SORT_ASC;
    			}
    			array_multisort($sort_col, $dir, $fdoclimit);
    			
    			$keyw = $columns_array[$order_column].'_tr';
    			array_walk($fdoclimit, function (&$v) use ($keyw) {
    				unset($v[$keyw]);
    			});
			}
	
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
					
				if(in_array($mdata['id'], $total_assigned_locations))
				{
					$resulted_data[$row_id]['location'] = sprintf($link,'<span>!</span>'.$mdata['location_name']);
					$ask_on_del = '1';
				}
				else
				{
					$resulted_data[$row_id]['location'] = sprintf($link,$mdata['location_name']);
					$ask_on_del = '0';
				}
				
				if($mdata['location_type_name'] != "")
				{
					$resulted_data[$row_id]['location_type'] = sprintf($link,$mdata['location_type_name']);
				}
				else 
				{
					$resulted_data[$row_id]['location_type'] = '-';
				}
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'dischargelocation/editlocation?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="'.$ask_on_del.'" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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