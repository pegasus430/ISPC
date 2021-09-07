<?php

	class PhysiotherapistController extends Zend_Controller_Action {

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

		public function addphysiotherapistAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				$fdoctor_form = new Application_Form_Physiotherapists();

				if($fdoctor_form->validate($_POST))
				{
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

				$this->clear_image_details();
			}

			$this->clear_image_details();
		}

		public function editphysiotherapistAction()
		{
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->view->act = "physiotherapist/editphysiotherapist?id=" . $_REQUEST['id'];

			$this->_helper->viewRenderer('addphysiotherapist');
			if($this->getRequest()->isPost())
			{
				$fdoctor_form = new Application_Form_Physiotherapists();
				if($fdoctor_form->validate($_POST))
				{
					$a_post = $_POST;
					$a_post['id'] = $_REQUEST['id'];

					if($_POST['remove_icon'] == '1' && strlen($_REQUEST['id']) > '0')
					{
						$this->remove_icon($_REQUEST['id']);
					}

					$fdoctor_form->UpdateData($a_post);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'physiotherapist/physiotherapistlist?flg=suc&mes='.urlencode($this->view->error_message));					
				}
				else
				{
					$fdoctor_form->assignErrorMessages();
					$this->retainValues($_POST);
				}

				$this->clear_image_details();
			}

			if($_REQUEST['id'] > 0)
			{
				$physiotherapist_details = Physiotherapists::get_physiotherapist($_REQUEST['id']);
				if($physiotherapist_details)
				{
					$this->retainValues($physiotherapist_details[0]);
				}

				$clientid = $physiotherapist_details[0]['clientid'];
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

				$this->clear_image_details();
			}

			$this->clear_image_details();
		}

		public function physiotherapistlistoldAction()
		{
			if($_REQUEST['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}

			$this->clear_image_details();
		}

		public function getjsondataAction()
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Physiotherapists')
				->where('isdelete = ?', 0);
			echo json_encode($fdoc->fetchArray());

			exit;
		}

		public function fetchlistAction()
		{
			$columnarray = array("pk" => "id", "phy" => "physiotherapist", "fn" => "first_name", "ln" => "last_name", "zp" => "zip", "ct" => "city", "ph" => "phone_practice","em"=>"email");

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
				->from('Physiotherapists');
				$fdoc1->where("isdelete = 0");
				$fdoc1->andWhere("indrop=0 " . $where . " ");
				$fdoc1->andWhere("valid_till='0000-00-00'");
				if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
				{
					$fdoc1->andWhere("trim(lower(concat(last_name,'_',first_name,'_',physiotherapist,'_',zip,'_',city,'_',phone_practice,'_')))  like ?", array("%".trim(mb_strtolower($_REQUEST['val'], 'UTF-8'))."%"));
				}
			$fdoc1->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$fdocarray = $fdoc1->fetchArray();

			$limit = 50;
			$fdoc1->select('*');
			$fdoc1->where("isdelete = 0");
			$fdoc1->andWhere("indrop=0 " . $where . " ");
			$fdoc1->andWhere("valid_till='0000-00-00'");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc1->andWhere("trim(lower(concat(last_name,'_',first_name,'_',physiotherapist,'_',zip,'_',city,'_',phone_practice,'_')))  like ?", array("%".trim(mb_strtolower($_REQUEST['val'], 'UTF-8'))."%"));
				
			}
			$fdoc1->limit($limit);
			$fdoc1->offset($_REQUEST['pgno'] * $limit);
// echo $fdoc1->getSqlQuery(); exit;
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());

			$this->view->{"style" . $_REQUEST['pgno']} = "active";
			$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "listphysiotherapist.html");
			$this->view->physiotherapistgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("physiotherapistnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['physiotherapistlist'] = $this->view->render('physiotherapist/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function deletephysiotherapistAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			//$this->_helper->viewRenderer('physiotherapistlist');

			//check if physiotherapist is assigned to anny patient
			//$physiotherapist_assigned = PatientPhysiotherapist::get_physiotherapist_patients($_REQUEST['id']);
			
			//if(!$physiotherapist_assigned)
			//{
				$trash = Doctrine::getTable('Physiotherapists')->find($_REQUEST['id']);
				$trash->isdelete = 1;
				$trash->save();
				
				//$this->view->delete_message = "Record deleted sucessfully";
				
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'physiotherapist/physiotherapistlist?flg=suc&mes='.urlencode($this->view->error_message));
				//}
				
			//}
			//else
			//{
				//$this->view->delete_message = "You can not delete the pflegediste because he is assigned to a patient.";
			//}
		}
		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		private function clear_image_details()
		{
			$_SESSION['file'] = '';
			$_SESSION['filefiletype'] = '';
			$_SESSION['filefiletitle'] = '';
			$_SESSION['filefilename'] = '';

			unset($_SESSION['filefile']);
			unset($_SESSION['filefiletype']);
			unset($_SESSION['filefiletitle']);
			unset($_SESSION['filefilename']);
		}

		private function remove_icon($physioid)
		{

			$physiotherapist = Physiotherapists::get_physiotherapist($physioid);

			if($this->clientid == $physiotherapist[0]['clientid'])
			{
				$icon_filename = 'icons_system/' . $physiotherapist[0]['logo'];

				//backup_old_file
				if(file_exists($icon_filename))
				{
					$path_parts = pathinfo($icon_filename);

					$new_filename_path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_' . time() . '_bak.' . $path_parts['extension'];
					copy($icon_filename, $new_filename_path);
				}

				//remove logo link from db
				$remove_icon = Doctrine::getTable('Physiotherapists')->findOneById($physioid);
				$remove_icon->logo = '';
				$remove_icon->save();
			}
		}
		
		//get view list physiotherapist
		public function physiotherapistlistAction(){
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
						"0" => "physiotherapist",
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
				$fdoc1->from('Physiotherapists');
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
					//$fdoc1->andWhere("(lower(first_name) like ? or lower(last_name) like ? or lower(physiotherapist) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone_practice) like ? or lower(email) like ?)",
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
					$resulted_data[$row_id]['physiotherapist'] = sprintf($link,$mdata['physiotherapist']);
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
		
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'physiotherapist/editphysiotherapist?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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