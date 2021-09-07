<?php

	class FamilydoctorController extends Zend_Controller_Action {

		public $act;

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

		public function addfamilydoctorAction()
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

			if($_GET['popup'] == "popup")
			{
				$this->_helper->layout->setLayout('layout_popup');
				$this->view->clickaction = "setchild()";
			}

			if($this->getRequest()->isPost())
			{
				$fdoctor_form = new Application_Form_Familydoctor();

				if($fdoctor_form->validate($_POST))
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
		}

		public function editfamilydoctorAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$this->view->act = "familydoctor/editfamilydoctor?id=" . $_GET['id'];

			$this->_helper->viewRenderer('addfamilydoctor');
			if($this->getRequest()->isPost())
			{
				$fdoctor_form = new Application_Form_Familydoctor();

				if($fdoctor_form->validate($_POST))
				{
					$a_post = $_POST;
					$did = $_GET['id'];
					$a_post['did'] = $did;

					$fdoctor_form->UpdateData($a_post);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'familydoctor/familydoctorlist?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$fdoctor_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			if($_GET['id'] > 0)
			{
				$fdoc = Doctrine::getTable('FamilyDoctor')->find($_GET['id']);

				if($fdoc)
				{
					$fdocarray = $fdoc->toArray();
					$this->retainValues($fdocarray);
				}


				$clientid = $fdocarray['clientid'];
				if($clientid > 0 || $logininfo->clientid > 0)
				{
					if($clientid > 0)
					{
						$client = $clientid;
					}
					else if($logininfo->clientid > 0)
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
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		public function familydoctorlistoldAction()
		{
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				;
			}
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if($has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->view->has_edit_permissions = 1;
			} else{
				$this->view->has_edit_permissions = 0;
			}
		}

		public function getjsondataAction()
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->where('isdelete = ?', 0);
			$track = $fdoc->execute();

			echo json_encode($track->toArray());
			exit;
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('familydoctor', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "prac" => "practice", "fn" => "first_name", "ln" => "last_name", "zp" => "zip", "ct" => "city", "ph" => "phone_practice","em"=>"email");

			if(!array_key_exists($_REQUEST['clm'], $columnarray) || strlen($_REQUEST['clm']) == '0')
			{
				$order_by = $columnarray['pk'];
			}
			else
			{
				$order_by = $columnarray[$_REQUEST['clm']];
			}

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
			$this->view->{"style" . $_GET['pgno']} = "active";
			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$fdoc = Doctrine_Query::create()
				->select('count(*)')
				->from('FamilyDoctor')
				//->where("isdelete = 0 and valid_till='0000-00-00' and (first_name!='' or last_name!='')" . $where)
				->where("isdelete = 0 and valid_till='0000-00-00' and (first_name!='' or last_name!='' or practice !='')" . $where)
				->andWhere('indrop=0');
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("(first_name like '%" . trim($_REQUEST['val']) . "%' or  last_name like '%" . trim($_REQUEST['val']) . "%' or practice like '%" . trim($_REQUEST['val']) . "%' or zip like '%" . trim($_REQUEST['val']) . "%' or  city like '%" . trim($_REQUEST['val']) . "%' or  phone_practice like '%" . trim($_REQUEST['val']) . "%')");
			}
			$fdoc->orderBy($order_by . " " . $_REQUEST['ord']);
			$fdocarray = $fdoc->fetchArray();

			$limit = 50;
			$fdoc->select('*');
			$fdoc->where("isdelete = 0 and indrop=0 " . $where . " and valid_till='0000-00-00' and (first_name!='' or last_name!=''  or practice !='')");
			
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("(first_name like '%" . trim($_REQUEST['val']) . "%' or  last_name like '%" . trim($_REQUEST['val']) . "%' or practice like '%" . trim($_REQUEST['val']) . "%' or zip like '%" . trim($_REQUEST['val']) . "%' or  city like '%" . trim($_REQUEST['val']) . "%' or  phone_practice like '%" . trim($_REQUEST['val']) . "%')");
			}
			
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = $fdoc->fetchArray();

			$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listfamilydoctor.html");
			$this->view->familydoctorgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("fdocnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();

			if(!$fdoclimit)
			{
				$this->view->familydoctorgrid = '<tr><td colspan="8">' . $this->view->translate('no_familydoctor_records') . '</td></tr>';
			}
			
			$response['callBackParameters']['familydoctorlist'] = $this->view->render('familydoctor/fetchlist.html');
			
			echo json_encode($response);
			exit;
		}

		public function deletefamilydoctorAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			//$this->_helper->viewRenderer('familydoctorlist');

			//$fdoc = Doctrine::getTable('PatientMaster')->findBy('familydoc_id', $_GET['id']);			
			$fdoc = Doctrine::getTable('FamilyDoctor')->find($_GET['id']);
			$fdoc->isdelete = 1;
			$fdoc->save();
		
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'familydoctor/familydoctorlist?flg=suc&mes='.urlencode($this->view->error_message));
		}

		public function getsapvverordnungAction()
		{

			$this->_helper->viewRenderer('patientmasteradd');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(strlen($_REQUEST['ltr']) > 0)
			{

				$drop = Doctrine_Query::create()
					->select('*')
					->from('FamilyDoctor')
					->where("isdelete=0")
					->andWhere("(trim(lower(last_name)) like ? )  or (trim(lower(first_name)) like ? ) or (trim(lower(city)) like ? )",array("%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%","%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%","%".trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%"))
					->andWhere('clientid = "' . $clientid . '"')
					->andWhere("indrop = 0")
					->orderBy('last_name ASC');

				$dropexec = $drop->execute();


				$droparray = $dropexec->toArray();
				$drop_array = $droparray;
				foreach($dropexec->toArray() as $key => $val)
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
		}
		
		//get view list family doctor
		public function familydoctorlistAction(){
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
						"0" => "practice",
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
				$fdoc1->from('FamilyDoctor');
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
					//$fdoc1->andWhere("(lower(first_name) like ? or lower(last_name) like ? or lower(practice) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone_practice) like ? or lower(email) like ?)",
					//		array("%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%"));
				}
								
				$fdocarray = $fdoc1->fetchArray();
				$filter_count  = $fdocarray[0]['count'];
				
				// ########################################
				// #####  Query for details ###############
				$fdoc1->select('*');
			
				//$fdoclimit = $fdoc1->fetchArray();
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
					$resulted_data[$row_id]['zip'] = sprintf($link,$mdata['zip']);
					$resulted_data[$row_id]['city'] = sprintf($link,$mdata['city']);
					$resulted_data[$row_id]['phone_practice'] = sprintf($link,$mdata['phone_practice']);
					$resulted_data[$row_id]['email'] = sprintf($link,$mdata['email']);
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'familydoctor/editfamilydoctor?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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