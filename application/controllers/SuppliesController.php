<?php
class SuppliesController extends Zend_Controller_Action
{
	public $act;

	public function init ()
	{
		/* Initialize action controller here */
	}

	public function addsuppliesAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		
		$this->view->clickaction = "";
		$this->view->closefrm = '""';

		if ($_GET['popup'] == "popup")
		{
			$this->_helper->layout->setLayout('layout_popup');
			$this->view->clickaction = "setchild()";
		}

		if ($this->getRequest()->isPost())
		{
			$supplies_form = new Application_Form_Supplies();

			if ($supplies_form->validate($_POST))
			{
				$supplies_form->InsertData($_POST);

				$fn = $_POST['first_name'];
				$curr_id = $supplies_form->id;
				$this->view->closefrm = "setchild('fn')"; // for closing iframe
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$supplies_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function editsuppliesAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$this->view->act = "supplies/editsupplies?id=" . $_GET['id'];
		$this->_helper->viewRenderer('addsupplies');
		if ($this->getRequest()->isPost())
		{
			$supplies_form = new Application_Form_Supplies();
			if ($supplies_form->validate($_POST))
			{
				$a_post = $_POST;
				$did = $_GET['id'];
				$a_post['did'] = $did;

				$supplies_form->UpdateData($a_post);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'supplies/supplieslist?flg=suc&mes='.urlencode($this->view->error_message));				
			}
			else
			{
				$supplies_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if ($_GET['id'] > 0)
		{
			$drop = Doctrine_Query::create()
			->select('*, supplier as supplier')
			->from('Supplies')
			->where("id= ?", $_GET['id']);
			$fdoc = $drop->execute();

			if ($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$this->retainValues($fdocarray[0]);
			}

			$clientid = $fdocarray['clientid'];
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


				$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
						->from('Client')
						->where('id = ?', $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
			}
		}
	}

	private function retainValues ( $values )
	{
		foreach ($values as $key => $val)
		{

			$this->view->$key = $val;
		}
	}

	public function supplieslistoldAction ()
	{


		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}
	}

	public function getjsondataAction ()
	{

		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('Supplies')
		->where('isdelete = ?', 0);
		$track = $fdoc->execute();


		echo json_encode($track->toArray());
		exit;
	}

	public function fetchlistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('supplies', $logininfo->userid, 'canview');

		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$columnarray = array(
				"pk" => "id",
				"pharm" => "supplier",
				"fn" => "first_name",
				"ln" => "last_name",
				"zp" => "zip",
				"ct" => "city",
				"ph" => "phone",
				"em" =>"email"
		);

		$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
		$this->view->order = $orderarray[$_REQUEST['ord']];
		$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

		if ($logininfo->clientid > 0)
		{
			$where = ' and clientid=' . $logininfo->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$fdoc = Doctrine_Query::create()
		->select('count(*)')
		->from('Supplies')
		->where("isdelete = 0 and valid_till='0000-00-00' " . $where)
		->andWhere('indrop=0')
		->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

		$fdocexec = $fdoc->execute();
		$fdocarray = $fdocexec->toArray();

		$limit = 50;
		$fdoc->select('*, supplier as supplier');
		$fdoc->where("isdelete = 0");
		$fdoc->andWhere("indrop=0 " . $where . " ");
		$fdoc->andWhere("valid_till='0000-00-00'");
// 		$fdoc->andWhere("first_name!='' or last_name!=''");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc->andWhere("(first_name like ? or  last_name like ? or supplier like ? or zip like ? or  city like ? or  phone like ?)",array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
			
		}
		$fdoc->limit($limit);
		$fdoc->offset($_REQUEST['pgno'] * $limit);
		$fdoclimitexec = $fdoc->execute();

		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		$this->view->{"style".$_GET['pgno']} = "active";
		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "supplieslist.html");
		$this->view->suppliesgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("suppliesnavigation.html", 5, $_REQUEST['pgno'], $limit);
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['supplieslist'] = $this->view->render('supplies/fetchlist.html');

		echo json_encode($response);
		exit;
	}

	public function deletesuppliesAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		//$this->_helper->viewRenderer('supplieslist');

		$fdoc = Doctrine::getTable('Supplies')->find($_GET['id']);
		$fdoc->isdelete = 1;
		$fdoc->save();
		
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . 'supplies/supplieslist?flg=suc&mes='.urlencode($this->view->error_message));
	}
	
	//get view list supplies
	public function supplieslistAction(){
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
					"0" => "supplier",
					"1" => "first_name",
					"2" => "last_name",
					"3" => "zip",
					"4" => "city",
					"5" => "phone",
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
			$fdoc1->from('Supplies');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0  ");
			$fdoc1->andWhere("indrop=0 ");
			$fdoc1->andWhere("valid_till='0000-00-00'");
				
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
				//$fdoc1->andWhere("(lower(first_name) like ? or lower(last_name) like ? or lower(supplier) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone) like ? or lower(email) like ?)",
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
				$resulted_data[$row_id]['supplier'] = sprintf($link,$mdata['supplier']);
				$resulted_data[$row_id]['first_name'] = sprintf($link,$mdata['first_name']);
				$resulted_data[$row_id]['last_name'] = sprintf($link,$mdata['last_name']);
				$resulted_data[$row_id]['zip'] = sprintf($link,$mdata['zip']);
				$resulted_data[$row_id]['city'] = sprintf($link,$mdata['city']);
				$resulted_data[$row_id]['phone'] = sprintf($link,$mdata['phone']);
				$resulted_data[$row_id]['email'] = sprintf($link,$mdata['email']);
				if($mdata['logo'] != "")
				{
					$logo_array = explode(".",$mdata['logo']);
					$extension = end($logo_array);
					if($extension != "")
					{
						$resulted_data[$row_id]['icon'] = '<img src="icons_system/'.$mdata['logo'].'" class="icon_image" />';
					}
				}
				else
				{
					$resulted_data[$row_id]['icon'] = '';
				}
	
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'supplies/editsupplies?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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