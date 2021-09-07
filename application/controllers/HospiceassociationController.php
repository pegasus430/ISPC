<?php

class HospiceassociationController extends Zend_Controller_Action
{
	public $act;
	public function init()
	{
	}

	public function addhospiceassociationAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
				
		if($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Hospiceassociation();

			if($fdoctor_form->validate($_POST))
			{
				$fdoctor_form->InsertData($_POST);
				//$this->_redirect(APP_BASE.'hospiceassociation/hospiceassociationlist?flg=suc');
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");

			}else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function edithospiceassociationAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$this->view->act="hospiceassociation/edithospiceassociation?id=".$_GET['id'];

		$this->_helper->viewRenderer('addhospiceassociation');
		if($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Hospiceassociation();

			if($fdoctor_form->validate($_POST))
			{
				$a_post = $_POST;
				$did = $_GET['id'];
				$a_post['did'] = $did;

				$fdoctor_form->UpdateData($a_post);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE.'hospiceassociation/hospiceassociationlist?flg=suc&mes='.urlencode($this->view->error_message));				
			}else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if($_GET['id']>0)
		{
			$drop = Doctrine_Query::create()
			->select('*')
			->from('Hospiceassociation')
			->where("id= ?", $_GET['id']);

			$actipidarray = $drop->fetchArray();

			if(count($actipidarray)>0){
				$this->view->hospice_association = $actipidarray[0]['hospice_association'];
				$this->view->title = $actipidarray[0]['title'];
				$this->view->salutation = $actipidarray[0]['salutation'];
				$this->view->last_name = $actipidarray[0]['last_name'];
				$this->view->first_name = $actipidarray[0]['first_name'];
				$this->view->street1 = $actipidarray[0]['street1'];
				$this->view->zip = $actipidarray[0]['zip'];
				$this->view->city = $actipidarray[0]['city'];
				$this->view->phone_practice = $actipidarray[0]['phone_practice'];
				$this->view->phone_private = $actipidarray[0]['phone_private'];
				$this->view->phone_emergency = $actipidarray[0]['phone_emergency'];
				$this->view->fax = $actipidarray[0]['fax'];
				$this->view->email = $actipidarray[0]['email'];
				$this->view->comments = $actipidarray[0]['comments'];
			}
		}
	}


	private function retainValues($values)

	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}


	public function hospiceassociationlistoldAction()
	{
		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}
	}

	public function getjsondataAction()
	{
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('Hospiceassociation')
		->where('isdelete = ?', 0);
		$track = $fdoc->execute();
			
		echo json_encode($track->toArray());
		exit;
	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		// get associated clients of current clientid START
		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $logininfo->clientid;
		}
		
		
		
		$columnarray = array(
				"pk"=>"id",
				"ha"=>"hospice_association",
				"fn"=>"first_name",
				"ln"=>"last_name",
				"zp"=>"zip",
				"ct"=>"city",
				"ph"=>"phone_practice",
				"em"=>"email"
		);

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm']."order"} = $orderarray[$_REQUEST['ord']];
		$this->view->{"style".$_GET['pgno']} = "active";
		
		if($clientid>0)
		{
			$where = ' and clientid='.$clientid;
		}else{
			$where = ' and clientid=0';
		}

		$fdoc1 = Doctrine_Query::create()
		->select('count(*)')
		->from('Hospiceassociation')
		->where("indrop= 0 and isdelete = 0 ".$where)
		->orderBy($columnarray[$_REQUEST['clm']]." ".$_REQUEST['ord']);

		$fdocarray = $fdoc1->fetchArray();

		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete = 0");
		$fdoc1->andWhere("indrop=0 ".$where." ");
		if(isset($_REQUEST['val']) && strlen($_REQUEST['val'])>0)
		{
// 			$fdoc1->where("isdelete = 0 and indrop=0 ".$where." and (first_name like '%".trim($_REQUEST['val'])."%' or hospice_association like '%".trim($_REQUEST['val'])."%' or last_name like '%".trim($_REQUEST['val'])."%' or  zip like '%".trim($_REQUEST['val'])."%' or  city like '%".trim($_REQUEST['val'])."%' or  phone_practice like '%".trim($_REQUEST['val'])."%')");
			$fdoc1->andWhere("(first_name like ? or hospice_association like ? or last_name like ? or  zip like ? or  city like ? or  phone_practice like ?)",
					array("%".trim($_REQUEST['val'])."%","%".trim($_REQUEST['val'])."%","%".trim($_REQUEST['val'])."%","%".trim($_REQUEST['val'])."%","%".trim($_REQUEST['val'])."%","%".trim($_REQUEST['val'])."%"));
		}
		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno']*$limit);


		$fdocarrayx = $fdoc1->fetchArray();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdocarrayx);
			
			
		$grid = new Pms_Grid($fdoclimit,1,$fdocarray[0]['count'],"hospiceassociationlist.html");
		$this->view->hospiceassociationgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("hospiceassocnavigation.html",5,$_REQUEST['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['hospiceassociationlist'] = $this->view->render('hospiceassociation/fetchlist.html');

		echo json_encode($response);
		exit;
	}

	public function deletehospiceassociationAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('hospiceassociationlist');
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
 
		if($has_edit_permissions)  // if canedit = 0 - don't allow any additions or changes
		{
			/*$drop = Doctrine_Query::create()
			->select('*')
			->from('Voluntaryworkers')
			->where("hospice_association= ?", $_GET['id'])
			->andwhere("isdelete = 0");
			$fdoc = $drop->fetchArray();

			if(count($fdoc)<1)
			{
				$ids = split(",",$_GET['id']);

				for($i=0;$i<sizeof($ids);$i++)
				{*/
					$thrash = Doctrine::getTable('Hospiceassociation')->find($_GET['id']);
					$thrash->isdelete = 1;
					$thrash->save();
					//$this->view->delete_message = "Record deleted sucessfully";
					$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
					$this->_redirect(APP_BASE.'hospiceassociation/hospiceassociationlist?flg=suc&mes='.urlencode($this->view->error_message));
			/*	}
			}else{
				$this->view->delete_message = "You can not delete the hospice association  because it is assign to voluntary workers";

			}*/
		} else{
			$this->_redirect(APP_BASE."error/previlege");
			exit;
		}
	}


	public function fetchdropdownAction()
	{
		$this->_helper->viewRenderer('patientmasteradd');
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
			
		if(strlen($_REQUEST['ltr'])>0)
		{
			$drop = Doctrine_Query::create()
			->select('*')
			->from('Hospiceassociation')
			->where("clientid= ?", $clientid)
			->andWhere("(trim(lower(last_name)) like ? ) or 
					(trim(lower(first_name)) like ? ) or 
					(trim(lower(hospice_association)) like ? ) or 
					(trim(lower(hospice_association)) like ? )",
					array(trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%",trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%", trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%", "%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%"))
			->andWhere("indrop = ?", 0)
			->andWhere("isdelete = ?", 0)
			->orderBy('last_name ASC');
			$droparray = $drop->fetchArray();

			$drop_array = $droparray;


			foreach($droparray as $key=>$val)
			{
				$drop_array[$key]['hospice_association'] = html_entity_decode($val['hospice_association'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
			}
			$droparray = $drop_array;

		}
		else
		{
			$droparray=array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		if($_REQUEST['modal'] != 1){
			$response['callBack'] = "docdropdiv";
		} else {
			$response['callBack'] = "docdropdivpfl";
		}
		$response['callBackParameters'] = array();
		$response['callBackParameters']['doctors'] = $droparray;
			
		echo json_encode($response);
		exit;

			
	}
	
	//get view list hospice associations
	public function hospiceassociationlistAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		// get associated clients of current clientid START
		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $logininfo->clientid;
		}
	
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$avw = new Voluntaryworkers();
			$attvw = $avw->getClientsVoluntaryworkers($clientid, false);
			
			$total_assigned_vw = array();
			foreach($attvw as $kavw=>$vavw)
			{
				if($vavw['hospice_association'] != '0')
				{
					$total_assigned_vw[] = $vavw['hospice_association'];
				}
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
					"0" => "hospice_association",
					"1" => "first_name",
					"2" => "last_name",
					"3" => "zip",
					"4" => "city",
					"5" => "phone_practice",
					"6" => "email",
	
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
			$fdoc1->from('Hospiceassociation');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0  ");
			$fdoc1->andWhere("indrop=0 ");
	
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
				//$fdoc1->andWhere("(lower(first_name) like ? or lower(last_name) like ? or lower(hospice_association) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone_practice) like ? or lower(email) like ?)",
				//		array("%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%"));
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
				
				if(in_array($mdata['id'], $total_assigned_vw))
				{
					$resulted_data[$row_id]['hospice_association'] = sprintf($link,'<span>!</span>'.$mdata['hospice_association']);
					$ask_on_del = '1';
				}
				else
				{
					$resulted_data[$row_id]['hospice_association'] = sprintf($link,$mdata['hospice_association']);
					$ask_on_del = '0';
				}
				
				$resulted_data[$row_id]['first_name'] = sprintf($link,$mdata['first_name']);
				$resulted_data[$row_id]['last_name'] = sprintf($link,$mdata['last_name']);
				$resulted_data[$row_id]['zip'] = sprintf($link,$mdata['zip']);
				$resulted_data[$row_id]['city'] = sprintf($link,$mdata['city']);
				$resulted_data[$row_id]['phone_practice'] = sprintf($link,$mdata['phone_practice']);
				$resulted_data[$row_id]['email'] = sprintf($link,$mdata['email']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'hospiceassociation/edithospiceassociation?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="'.$ask_on_del.'" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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