<?php
class SpecialistsController extends Zend_Controller_Action
{
	public $act;

	public function init ()
	{
		/* Initialize action controller here */
	}

	public function addspecialistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$types = new SpecialistsTypes();
		$specialist_types = $types->get_specialists_types($logininfo->clientid);
		$this->view->specialist_types = $specialist_types;


		if ($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Specialists();

			if ($fdoctor_form->validate($_POST))
			{
			    $_POST['indrop'] = 0 ; 
				$fdoctor_form->InsertData($_POST);

				$fn = $_POST['first_name'];
				$curr_id = $fdoctor_form->id;

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function editspecialistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$this->view->act = "specialists/editspecialist?id=" . $_GET['id'];

		$types = new SpecialistsTypes();
		$specialist_types = $types->get_specialists_types($logininfo->clientid);
		$this->view->specialist_types = $specialist_types;

		$this->_helper->viewRenderer('addspecialist');

		if ($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_Specialists();

			if ($fdoctor_form->validate($_POST))
			{
				$a_post = $_POST;
				$did = $_GET['id'];
				$a_post['did'] = $did;

				$fdoctor_form->UpdateData($a_post);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'specialists/specialists?flg=suc&mes='.urlencode($this->view->error_message));				
			}
			else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if ($_GET['id'] > 0)
		{
			$fdoc = Doctrine::getTable('Specialists')->find($_GET['id']);

			if ($fdoc)
			{
				$fdocarray = $fdoc->toArray();
				$this->retainValues($fdocarray);
			}


			$clientid = $fdocarray['clientid'];
		}
	}

	private function retainValues ( $values )
	{
		foreach ($values as $key => $val)
		{
			$this->view->$key = $val;
		}
	}

	public function specialistsoldAction ()
	{
		if ($_GET['flg'] == 'suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
		}
		
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if($has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->view->has_edit_permissions = 1;
		} else{
			$this->view->has_edit_permissions = 0;
		}
	}

	public function getjsondataAction ()
	{
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('Specialists')
		->where('isdelete = ?', 0);
		$track = $fdoc->fetchArray();

		echo json_encode($track);
		exit;
	}

	public function fetchlistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('specialists', $logininfo->userid, 'canview');

		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$columnarray = array("pk" => "id", "prac" => "practice", "fn" => "first_name", "ln" => "last_name", "ms" => "medical_speciality","zp" => "zip", "ct" => "city", "ph" => "phone_practice","em"=>"email");

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
		->from('Specialists')
		->where("isdelete = 0 and valid_till='0000-00-00' and (first_name!='' or last_name!='')" . $where)
		->andWhere('indrop=0')
		->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);

		$fdocarray = $fdoc->fetchArray();

		$limit = 50;
		$fdoc->select('*');
		$fdoc->where("isdelete = 0");
		$fdoc->andWhere("indrop=0 " . $where . "");
		$fdoc->andWhere("valid_till='0000-00-00'");
		$fdoc->andWhere("first_name!='' or last_name!='' ");
		if (isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
		{
			$fdoc->andWhere("first_name like ? or  last_name like ? or practice like ? or zip like ? or  city like ? or  phone_practice like ? ",array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%'"));
		}
		$fdoc->limit($limit);
		$fdoc->offset($_REQUEST['pgno'] * $limit);

		$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());

		
		$this->view->{"style".$_GET['pgno']} = "active";
		$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "specialists.html");
		$this->view->specialistsgrid = $grid->renderGrid();

		$this->view->navigation = $grid->dotnavigation("specialistsnavigation.html", 5, $_REQUEST['pgno'], $limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['specialists'] = $this->view->render('specialists/fetchlist.html');
		echo json_encode($response);
		exit;
	}

	public function deletespecialistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$this->_helper->viewRenderer('specialists');
		$fdoc = Doctrine::getTable('Specialists')->find($_GET['id']);
		$fdoc->isdelete = 1;
		$fdoc->save();
		
		$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		$this->_redirect(APP_BASE . 'specialists/specialists?flg=suc&mes='.urlencode($this->view->error_message));
	}

	/* public function getsapvverordnungAction ()
	{
		$this->_helper->viewRenderer('patientmasteradd');
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if (strlen($_REQUEST['ltr']) > 0)
		{
			$drop = Doctrine_Query::create()
			->select('*')
			->from('FamilyDoctor')
			->where("isdelete=0 and clientid='" . $clientid . "' and  (trim(lower(last_name)) like trim(lower('" . $_REQUEST['ltr'] . "%'))) or (trim(lower(first_name)) like trim(lower('" . $_REQUEST['ltr'] . "%'))) or (trim(lower(city)) like trim(lower('" . $_REQUEST['ltr'] . "%')))")
			->andWhere('clientid = "' . $clientid . '"')
			->andWhere("indrop = 0")
			->orderBy('last_name ASC');

			$droparray = $drop->fetchArray();

			$drop_array = $droparray;
			foreach ($drop_array as $key => $val)
			{
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
		$response['callBack'] = "verordiv";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['doctors'] = $droparray;

		echo json_encode($response);
		exit;
	} */

	/* ###########  Specialists types ############## */

	public function specialiststypesoldAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$types = new SpecialistsTypes();
		$types_form = new Application_Form_SpecialistsTypes();
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;


		if ($this->getRequest()->isPost())
		{
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if (count($_REQUEST['stid']) > 0)
			{
				//edit
				$insert_specialist_type = $types_form->update_specialist_type($_REQUEST['stid'], $_POST);

				if ($insert_specialist_type)
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=err');
				}
			}
			else
			{
				//insert new
				$insert_specialist_type = $types_form->insert_specialist_type($clientid, $_POST);

				if ($insert_specialist_type)
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=err');
				}
			}
		}

		$this->view->form_types = $types->get_specialists_types($clientid);

		if ($_REQUEST['stid'])
		{
			$edit_form_type_data = $types->get_specialists_type($_REQUEST['stid']);
			$this->view->edit_form_type = $edit_form_type_data[0];
		}
	}
	
	//get view list specialists types
	public function specialiststypesAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$types = new SpecialistsTypes();
		$types_form = new Application_Form_SpecialistsTypes();
	
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
					"0" => "name"
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
			$fdoc1->from('SpecialistsTypes');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
	
			/* ------------- Search options ------------------------- */
			if (isset($search_value) && strlen(trim($search_value)) > 0)
			{
				$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp, false, true);				
					//var_dump($regexp);
					//$filter_search_value_arr = array();
					//$regexp_arr = array();
					$comma = '';
					$filter_string_all = '';
					foreach ($regexp as $word) {
						foreach($columns_search_array as $ks=>$vs)
						{
							//$filter_search_value_arr [] = 'CONVERT(CAST('.$vs.' as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
							//$regexp_arr[] = '%'. $word .'%';
							$filter_string_all .= $comma.$vs;
							$comma = ',';
						}						
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
					
					//echo $fdoc1->getSQQuery();
					//var_dump($regexp_arr);
					$fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
				//$search_value = strtolower($search_value);
				//$fdoc1->andWhere("(lower(name) like ?)", array("%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['name'] = sprintf($link,$mdata['name']);
	
				$resulted_data[$row_id]['actions'] = '<a href="javascript:void(0);" rel="'. $mdata['id']. '" relvalue="'. $mdata['name']. '" class="edit" id="edit_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
		
		if ($this->getRequest()->isPost()) {
			if ($_REQUEST['stid'] != '')
			{
				//edit
				$insert_specialist_type = $types_form->update_specialist_type($_REQUEST['stid'], $_POST);
			
				if ($insert_specialist_type)
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=err');
				}
			}
			else
			{
				//insert new
				$insert_specialist_type = $types_form->insert_specialist_type($clientid, $_POST);
			
				if ($insert_specialist_type)
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'specialists/specialiststypes?flg=err');
				}
			}
		}
	
	}
	
	//get view list specialists
	public function specialistsAction(){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
        $specialistsTypes = SpecialistsTypes::get_specialists_types_mapping($clientid);//ISPC-2862,Elena,22.03.2021
        $reversingSpecialistsTypes = [];
        foreach($specialistsTypes as $key => $stype){
            $reversingSpecialistsTypes[$stype] = $key;
        }
	
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
					"0" => "practice",
					"1" => "first_name",
					"2" => "last_name",
					"3" => "name", //ISPC-2862,Elena,22.03.2021
					"4" => "zip",
					"5" => "city",
					"6" => "phone_practice",
					"7" => "email",
	
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
            //ISPC-2862,Elena,22.03.2021
			$fdoc1->from('Specialists s');
            $fdoc1->leftJoin('s.SpecialistsTypes st ON (st.id = s.medical_speciality)');

			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0  ");
			$fdoc1->andWhere("indrop=0 ");
			$fdoc1->andWhere("valid_till='0000-00-00'");
			$fdoc1->andWhere("first_name!='' or last_name!='' or practice !='' ");
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
	
			/* ------------- Search options ------------------------- */
			if (isset($search_value) && strlen(trim($search_value)) > 0)
			{
				$comma = '';
				$filter_string_all = '';
				
				foreach($columns_search_array as $ks=>$vs)
				{
					//$filter_search_value_arr [] = 'CONVERT(CAST('.$vs.' as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
					//$regexp_arr[] = '%'. $word .'%';
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
				//$fdoc1->andWhere("(lower(first_name) like ? or lower(last_name) like ? or lower(practice) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone_practice) like ? or lower(email) like ?)",
				//		array("%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%"));
			}
	
			$fdocarray = $fdoc1->fetchArray();
			$filter_count  = $fdocarray[0]['count'];
	
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('s.*, st.name as med_speciality');//ISPC-2862,Elena,22.03.2021
			
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
				$resulted_data[$row_id]['practice'] = sprintf($link,$mdata['practice']);
				$resulted_data[$row_id]['first_name'] = sprintf($link,$mdata['first_name']);
				$resulted_data[$row_id]['last_name'] = sprintf($link,$mdata['last_name']);
                $resulted_data[$row_id]['medical_speciality'] = trim($mdata['med_speciality']);//ISPC-2862,Elena,22.03.2021
				$resulted_data[$row_id]['zip'] = sprintf($link,$mdata['zip']);
				$resulted_data[$row_id]['city'] = sprintf($link,$mdata['city']);
				$resulted_data[$row_id]['phone_practice'] = sprintf($link,$mdata['phone_practice']);
				$resulted_data[$row_id]['email'] = sprintf($link,$mdata['email']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'specialists/editspecialist?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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