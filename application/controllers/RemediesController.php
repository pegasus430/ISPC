<?php

	class RemediesController extends Zend_Controller_Action {

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

		public function addremedyAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				$remedy_form = new Application_Form_Remedies();

				if($remedy_form->validate($_POST))
				{
					$remedy_form->InsertData($_POST);

					
					$curr_id = $remedy_form->id;
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$remedy_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
		
			}
		
		}

		public function editremedyAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->view->act = "remedies/editremedy?id=" . $_REQUEST['id'];

			$this->_helper->viewRenderer('addremedy');
			if($this->getRequest()->isPost())
			{
				$remedy_form = new Application_Form_Remedies();
				if($remedy_form->validate($_POST))
				{
					$a_post = $_POST;
					$a_post['id'] = $_REQUEST['id'];

					$remedy_form->UpdateData($a_post);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'remedies/remedieslist?flg=suc&mes='.urlencode($this->view->error_message));					
				}
				else
				{
					$remedy_form->assignErrorMessages();
					$this->retainValues($_POST);
				}

				
			}

			if($_REQUEST['id'] > 0)
			{
				$remedy_details = Remedies::get_remedy($_REQUEST['id']);
				if($remedy_details)
				{
					$this->retainValues($remedy_details[0]);
				}

				$clientid = $remedy_details[0]['clientid'];
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
						->where('id =' . $client);
					$clientarray = $client->fetchArray();

					$this->view->client_name = $clientarray[0]['client_name'];
					$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
				}

				
			}

		
		}

		public function remedieslistoldAction()
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
				->from('Remedies')
				->where('isdelete = ?', 0);
			echo json_encode($fdoc->fetchArray());

			exit;
		}

		public function fetchlistAction()
		{
			$columnarray = array("pk" => "id", "key" => "indikation_key", "name" => "indikation_name");

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

			$fdoc1 = Doctrine_Query::create()
				->select('count(*)')
				->from('Remedies');
				$fdoc1->where("isdelete=0 " . $where . " ");
				if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
				{
					$fdoc1->andWhere("concat(trim(lower(indikation_key)),'_',trim(lower(indikation_name)))  like ? ",array("%".trim(mb_strtolower($_REQUEST['val'], 'UTF-8'))."%"));
						
				}
				
				
				$fdoc1->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$fdocarray = $fdoc1->fetchArray();

			$limit = 50;
			$fdoc1->select('*');
			$fdoc1->where("isdelete=0 " . $where . " ");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
					$fdoc1->andWhere("concat(trim(lower(indikation_key)),'_',trim(lower(indikation_name)))  like ? ",array("%".trim(mb_strtolower($_REQUEST['val'], 'UTF-8'))."%"));	
			
			}
			
			$fdoc1->limit($limit);
			$fdoc1->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());

			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listremedies.html");
			$this->view->remedygrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("remediesnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['remedieslist'] = $this->view->render('remedies/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function deleteremedyAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			//$this->_helper->viewRenderer('remedieslist');

				$trash = Doctrine::getTable('Remedies')->find($_REQUEST['id']);
				$trash->isdelete = 1;
				$trash->save();
				
				//$this->view->delete_message = "Record deleted sucessfully";
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'remedies/remedieslist?flg=suc&mes='.urlencode($this->view->error_message));
		
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		//get view list remedies
		public function remedieslistAction(){
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
						"0" => "indikation_key",
						"1" => "indikation_name"
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
				$fdoc1->from('Remedies');
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
					//$fdoc1->andWhere("(lower(indikation_key) like ? or lower(indikation_name) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
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
					$resulted_data[$row_id]['indikation_key'] = sprintf($link,$mdata['indikation_key']);
					$resulted_data[$row_id]['indikation_name'] = sprintf($link,$mdata['indikation_name']);
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'remedies/editremedy?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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