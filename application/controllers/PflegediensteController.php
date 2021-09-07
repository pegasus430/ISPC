<?php
class PflegediensteController extends Zend_Controller_Action
{
	public $act;

	public function init ()
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

	public function addpflegediensteAction ()
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
			$fdoctor_form = new Application_Form_Pflegedienstes();

			if ($fdoctor_form->validate($_POST))
			{
				$fdoctor_form->InsertData($_POST);

				$fn = $_POST['first_name'];
				$curr_id = $fdoctor_form->id;
				$this->view->closefrm = "setchild('$fn')"; // for closing iframe
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		$this->clear_image_details();
	}

	public function editpflegediensteAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$this->view->act = "pflegedienste/editpflegedienste?id=" . $_GET['id'];

		$this->_helper->viewRenderer('addpflegedienste');
		if ($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Pflegedienstes();


			if ($fdoctor_form->validate($_POST))
			{
				$a_post = $_POST;
				$did = $_GET['id'];
				$a_post['did'] = $did;

				$fdoctor_form->UpdateData($a_post);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'pflegedienste/pflegedienstelist?flg=suc&mes='.urlencode($this->view->error_message));				
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if ($_GET['id'] > 0)
		{
			$fdoc = Doctrine::getTable('Pflegedienstes')->find($_GET['id']);

			if ($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$this->retainValues($fdocarray);
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
						->where('id =' . $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
			}
		}

		$this->clear_image_details();

	}

	private function retainValues ( $values )
	{
		foreach ($values as $key => $val)
		{
			$this->view->$key = $val;
		}
	}

	public function pflegedienstelistoldAction ()
	{
		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			;
		}

		$this->clear_image_details();
	}

	public function getjsondataAction ()
	{
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('Pflegedienstes')
		->where('isdelete = ?', 0);
		$track = $fdoc->execute();

		echo json_encode($track->toArray());
		exit;
	}

	public function fetchlistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk" => "id", "nur" => "nursing", "fn" => "first_name", "ln" => "last_name", "zp" => "zip", "ct" => "city", "ph" => "phone_practice","em"=>"email");

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

		$fdoc1 = Doctrine_Query::create()
		->select('count(*)')
		->from('Pflegedienstes');
		$fdoc1->where("isdelete = 0");
		$fdoc1->andWhere("indrop=0 " . $where . " ");
		$fdoc1->andWhere("valid_till='0000-00-00' ");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("(first_name like ? or nursing like ? or last_name like ? or  zip like ? or  city like ? or  phone_practice like ?)",
					array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
		}
		$fdoc1->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

		$fdocexec = $fdoc1->execute();
		$fdocarray = $fdocexec->toArray();

		$limit = 50;
		$fdoc1->select('*');
		$fdoc1->where("isdelete = 0");
		$fdoc1->andWhere("indrop=0 " . $where . " ");
		$fdoc1->andWhere(" valid_till='0000-00-00' ");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc1->andWhere("(first_name like ? or nursing like ? or last_name like ? or  zip like ? or  city like ? or  phone_practice like ?)", 
					array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
		}
		$fdoc1->limit($limit);
		$fdoc1->offset($_REQUEST['pgno'] * $limit);

		$fdoclimitexec = $fdoc1->execute();
		$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());

		$this->view->{"style".$_GET['pgno']} = "active";
		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listpflegedienste.html");
		$this->view->pflegedienstegrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("pflegnavigation.html", 5, $_REQUEST['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['pflegedienstelist'] = $this->view->render('pflegedienste/fetchlist.html');

		echo json_encode($response);
		exit;
	}

	public function deletepflegediensteAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		/*$this->_helper->viewRenderer('pflegedienstelist');
 
		if ($has_edit_permissions)
		{
			$fdoc = Doctrine::getTable('PatientMaster')->findBy('pflegedienste', $_GET['id']);

			if (count($fdoc->toArray()) < 1)
			{
				$ids = split(",", $_GET['id']);

				for ($i = 0; $i < sizeof($ids); $i++)
				{
					$thrash = Doctrine::getTable('Pflegedienstes')->find($_GET['id']);
					$thrash->isdelete = 1;
					$thrash->save();
					$this->view->delete_message = "Record deleted sucessfully";
				}
			}
			else
			{
				$this->view->delete_message = "You can not delete the pflegediste because he is assign to patient";
			}
		}
		else
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}*/
	
		$thrash = Doctrine::getTable('Pflegedienstes')->find($_GET['id']);
		$thrash->isdelete = 1;
		$thrash->save();
		
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . 'pflegedienste/pflegedienstelist?mes='.urlencode($this->view->error_message));
	}

	public function fetchdropdownAction ()
	{
		$this->_helper->viewRenderer('patientmasteradd');
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if (strlen($_REQUEST['ltr']) > 0)
		{

			$drop = Doctrine_Query::create()
			->select('*')
			->from('Pflegedienstes')
			->where("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(nursing)) like ? )",array("%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%","%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%","%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%"))
			->andWhere('clientid = "' . $clientid . '"')
			->andWhere("valid_till='0000-00-00'")
			->andWhere("indrop = 0")
			->andWhere("isdelete = 0")
			->orderBy('last_name ASC');

			$dropexec = $drop->execute();


			$droparray = $dropexec->toArray();
			$drop_array = $droparray;
			foreach ($dropexec->toArray() as $key => $val)
			{
				$drop_array[$key]['nursing'] = html_entity_decode($val['nursing'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['first_name'] = html_entity_decode($val['first_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['last_name'] = html_entity_decode($val['last_name'], ENT_QUOTES, "utf-8");
				$drop_array[$key]['city'] = html_entity_decode($val['city'], ENT_QUOTES, "utf-8");
			}
			$droparray = $drop_array;
		}
		else
		{
			$droparray = array();
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		if ($_REQUEST['modal'] != 1)
		{
			$response['callBack'] = "docdropdiv";
		}
		else
		{
			$response['callBack'] = "docdropdivpfl";
		}
		$response['callBackParameters'] = array();
		$response['callBackParameters']['doctors'] = $droparray;

		echo json_encode($response);
		exit;
	}

	public function allpfleAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');


		if ($logininfo->clientid > 0)
		{
			$where = ' and clientid=' . $logininfo->clientid;
		}
		else
		{
			$where = ' and clientid=0';
		}

		$fdoc1 = Doctrine_Query::create()
		->select('*')
		->from('Pflegedienstes')
		->where("isdelete = 0 and valid_till='0000-00-00'" . $where)
		->andWhere('indrop=0')
		->orderBy('nursing');
		$fdocarray = $fdoc1->fetchArray();
		$i = 1;
		foreach ($fdocarray as $value)
		{
			echo '"' . $i . '",';
			echo '"' . $value['nursing'] . '",';
			echo '"' . $value['title'] . '",';
			echo '"' . $value['last_name'] . '",';
			echo '"' . $value['first_name'] . '",';
			echo '"' . $value['street1'] . '",';
			echo '"' . $value['zip'] . '",';
			echo '"' . $value['city'] . '",';
			echo '"' . $value['phone_practice'] . '",';
			echo '"' . $value['phone_private'] . '",';
			echo '"' . $value['phone_emergency'] . '",';
			echo '"' . $value['fax'] . '",';
			echo '"' . $value['doctornumber'] . '",';
			echo '"' . $value['medical_speciality'] . '",';
			echo '"' . $value['comments'] . '",';
			echo '"' . $value['palliativpflegedienst'] . '"';
			echo "\n";
			$i++;
		}
	}

	private function clear_image_details()
	{
		$_SESSION['file'] = '';
		$_SESSION['filetype'] = '';
		$_SESSION['filetitle'] = '';
		$_SESSION['filename'] = '';

		unset($_SESSION['file']);
		unset($_SESSION['filetype']);
		unset($_SESSION['filetitle']);
		unset($_SESSION['filename']);
	}
	
	//get view list pflegedienste
	public function pflegedienstelistAction(){
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
					"0" => "nursing",
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
			$fdoc1->from('Pflegedienstes');
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
				//$fdoc1->andWhere("(lower(first_name) like ? or lower(last_name) like ? or lower(nursing) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone_practice) like ? or lower(email) like ?)",
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
				$resulted_data[$row_id]['nursing'] = sprintf($link,$mdata['nursing']);
				$resulted_data[$row_id]['first_name'] = sprintf($link,$mdata['first_name']);
				$resulted_data[$row_id]['last_name'] = sprintf($link,$mdata['last_name']);
				$resulted_data[$row_id]['zip'] = sprintf($link,$mdata['zip']);
				$resulted_data[$row_id]['city'] = sprintf($link,$mdata['city']);
				$resulted_data[$row_id]['phone_practice'] = sprintf($link,$mdata['phone_practice']);
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
						
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'pflegedienste/editpflegedienste?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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