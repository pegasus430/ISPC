<?
class DischargemethodController extends Zend_Controller_Action
{
	public function init()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
			
		if(!$logininfo->clientid)
		{
			//redir to select client error
			$this->_redirect(APP_BASE . "error/noclient");
			exit;
		}
	}

	public function addmethodAction ()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		if ($this->getRequest()->isPost())
		{
			$location_form = new Application_Form_DischargeMethod();

			if ($location_form->validate($_POST))
			{
				$location_form->InsertData($_POST);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				//$this->retainValues($_POST);
				//$this->_redirect(APP_BASE . 'dischargemethod/listmethod');
			}
			else
			{
				$this->retainValues($_POST);
				$location_form->assignErrorMessages();
			}
		}

		if ($logininfo->clientid > 0)
		{
			$clientid = $logininfo->clientid;
 
			$this->view->inputbox = '<input name="clientid" type="hidden" value="' . $clientid . '" />';
		}
		else
		{
				$this->_redirect(APP_BASE . "error/previlege");
		}
	}

	public function editmethodAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$this->_helper->viewRenderer('addmethod');

		if ($this->getRequest()->isPost())
		{
			$location_form = new Application_Form_DischargeMethod();

			if ($location_form->validate($_POST))
			{
				$location_form->UpdateData($_POST);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'dischargemethod/listmethod?flg=suc&mes='.urlencode($this->view->error_message));
			}
			else
			{
				$client_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if (strlen($_GET['id']) > 0)
		{
			$location = Doctrine_Query::create()
			->select("*")
			->from('DischargeMethod')
			->where('id = ?', $_GET['id']);

			$location->getSqlQuery();
			$locationexec = $location->execute();

			$disarray = $locationexec->toArray();

			$this->retainValues($disarray[0]);
			$locationarray = $locationexec->toArray();
			$clientid = $locationarray[0]['clientid'];
			if ($clientid > 0 || $logininfo->clientid > 0)
			{
				if ($clientid > 0)
				{
					$client = $clientid;
				}
				else if ($logininfo->clientid > 0)
				{
					$client = $logininfo->clientid;
				}
				$this->view->inputbox = '<input name="clientid" type="hidden" value="' . $client . '" />';
			}
		}
		else
		{
			if ($logininfo->clientid > 0)
			{
				$client = $logininfo->clientid;
				$this->view->inputbox = '<input name="clientid" type="hidden" value="' . $client . '" />';
			}
		}
	}

	public function listmethodoldAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Dischargemethod', $logininfo->userid, 'canview');
		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
		}
	}

	public function fetchlistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Dischargemethod', $logininfo->userid, 'canview');
		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if ($logininfo->clientid > 0)
		{
			$where = ' and clientid=' . $logininfo->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$columnarray = array("pk" => "id", "ab" => "abbr", "des" => "description");

		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
		$location = Doctrine_Query::create()
		->select('count(*)')
		->from('DischargeMethod')
		->where('isdelete = 0 ' . $where)
		->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);

		$locationexec = $location->execute();
		$locationarray = $locationexec->toArray();


		$limit = 50;
		$location->select("*");
		$location->where('isdelete = 0 ' . $where);
		$location->limit($limit);
		$location->offset($_GET['pgno'] * $limit);
		$this->view->{"style".$_GET['pgno']} = "active";
		$locationlimitexec = $location->execute();
		$locationlimit = $locationlimitexec->toArray();

		$grid = new Pms_Grid($locationlimit, 1, $locationarray[0]['count'], "listmethod.html");
		$this->view->methodgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("methodnavigation.html", 5, $_GET['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['methodlist'] = $this->view->render('dischargemethod/fetchlist.html');

		echo json_encode($response);
		exit;
	}

	private function retainValues ( $values )
	{
		foreach ($values as $key => $val)
		{
			$this->view->$key = $val;
		}
	}

	public function deletemethodAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		//$this->_helper->viewRenderer('listmethod');
		//$dlocation = Doctrine::getTable('PatientDischarge')->findBy('discharge_method', $_GET['id']);
		//$dlocationarray = $dlocation->toArray();

		//if (count($dlocationarray) < 1)
		//{
			$doc = Doctrine::getTable('DischargeMethod')->find($_GET['id']);
			$doc->isdelete = 1;
			$doc->save();

			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'dischargemethod/listmethod?flg=suc&mes='.urlencode($this->view->error_message));
		//}
		//else
		//{
		//	$this->view->error_message = $this->view->translate("youcannotdeletethismethodbecauseitsused");
		//}
	}
	
	//get view list discharge method
	public function listmethodAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$adm = new PatientDischarge();
			$attdm = $adm->get_patient_dischargemethod_attached();
			$attached_dm = array();
				
			foreach($attdm as $kdm=>$vdm)
			{
				$attached_dm[] = $vdm['discharge_method'];
			}
			
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
	
			$columns_array = array(
					"0" => "abbr",
					"1" => "description"
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
			$fdoc1->from('DischargeMethod');
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
				//$fdoc1->andWhere("(lower(abbr) like ? or lower(description) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
			}
	
			$fdocarray = $fdoc1->fetchArray();
			$filter_count  = $fdocarray[0]['count'];
	
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*');
			
			$fdoctotarr = $fdoc1->fetchArray();
			
			$fdoc1->orderBy($order_by_str);
			$fdoc1->limit($limit);
			$fdoc1->offset($offset);
	
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
			
			$fdoctotids = array();
			foreach($fdoctotarr as $kfd=>$vfd)
			{
				$fdoctotids[] = $vfd['id'];
			}
			$total_assigned_locations = array_intersect($fdoctotids, $attached_dm);
	
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
					$resulted_data[$row_id]['abbr'] = sprintf($link,'<span>!</span>'.$mdata['abbr']);
					$ask_on_del = '1';
				}
				else
				{
					$resulted_data[$row_id]['abbr'] = sprintf($link,$mdata['abbr']);
					$ask_on_del = '0';
				}
				$resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'dischargemethod/editmethod?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="'.$ask_on_del.'" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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