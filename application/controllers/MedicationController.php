
<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
	class MedicationController extends Pms_Controller_Action {

		
		public function init()
		{
			/* Initialize action controller here */
			array_push($this->actions_with_js_file, "btmbuchhistory");
			
			//ISPC-2247
			array_push($this->actions_with_js_file, "editmedicationsset");
			
// 			//setup a default layout form controlers that have header with a patient info and navTabs
// 			$this_action = $this->getRequest()->getActionName();
				
							
// 			if(in_array($this_action, self::$actions_with_js_file)) {
// 				$this->template_init();
// 			}
			
			
		}

		
// 		private function template_init()
// 		{
// 			setlocale(LC_ALL, 'de_DE.UTF-8');
			
// 			if ( (isset($_REQUEST['pdf_print_template']) && $_REQUEST['pdf_print_template']=="pdf_print_template")
// 					|| (isset($_REQUEST['bypass_template']) && $_REQUEST['bypass_template']== "1" )
// 			)
// 			{
// 				//pdf print template
// 				$this->_helper->viewRenderer->setNoRender(true);
				
// 			}
// 			elseif ( ! $this->getRequest()->isXmlHttpRequest()) {
// 					/* ------------- Include js file of this action --------------------- */
// 					$actionName = $this->getRequest()->getActionName();
// 					$controllerName = $this->getRequest()->getControllerName();
					
// 					//sanitize $js_file_name ?
// 					$actionName = Pms_CommonData::normalizeString($actionName);
// 					$controllerName = Pms_CommonData::normalizeString($controllerName);
						
// 					//this is only on pc... so remember to put the ipad version
// 					$pc_js_file =  PUBLIC_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";
					
// 					//$js_filename is for http ipad/pc 
// 					$js_filename = RES_FILE_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";
							
					
// 					if (file_exists( $pc_js_file )) {
// 						$this->view->headScript()->appendFile($js_filename . "?_".(int)filemtime($pc_js_file));
// 					}
				
// 			}
		
// 		}
		
		public function addmedicationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$this->_helper->layout->setLayout('layout');

			if($_GET['popup'] == "popup")
			{
				$this->_helper->layout->setLayout('layout_popup');
				$this->view->clickaction = "setchild()";
			}

			if($this->getRequest()->isPost())
			{

				$user_form = new Application_Form_Medication();

				if($user_form->validate($_POST))
				{
					$user_form->InsertData($_POST);

					$fn = $_POST['name'];
					$curr_id = $user_form->id;
					$this->view->closefrm = "setchild('$fn')";

					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$user_form->assignErrorMessages();
				}
			}
		}

		public function addmedicationnutritionAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			$this->_helper->layout->setLayout('layout');
		
			if($_GET['popup'] == "popup")
			{
				$this->_helper->layout->setLayout('layout_popup');
				$this->view->clickaction = "setchild()";
			}
		
			if($this->getRequest()->isPost())
			{
		
				$user_form = new Application_Form_Nutrition();
		
				if($user_form->validate($_POST))
				{
					$user_form->InsertData($_POST);
		
					$fn = $_POST['name'];
					$curr_id = $user_form->id;
					$this->view->closefrm = "setchild('$fn')";
		
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$user_form->assignErrorMessages();
				}
			}
		}
		

		
		public function addreceiptmedicationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$this->_helper->layout->setLayout('layout');

			if($_GET['popup'] == "popup")
			{
				$this->_helper->layout->setLayout('layout_popup');
				$this->view->clickaction = "setchild()";
			}

			if($this->getRequest()->isPost())
			{

				$user_form = new Application_Form_MedicationReceipt();

				if($user_form->validate($_POST))
				{
					$user_form->InsertData($_POST);

					$fn = $_POST['name'];
					$curr_id = $user_form->id;
					$this->view->closefrm = "setchild('$fn')";

					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$user_form->assignErrorMessages();
				}
			}
		}

		public function editmedicationnutritionAction()
		{
			$this->view->formid = Pms_CommonData::getFormId('Nutrition');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
				
			$this->_helper->viewRenderer('addmedicationnutrition');

			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_Nutrition();

				if($client_form->validate($_POST))
				{
					$client_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'medication/listmedicationnutrition?flg=suc&mes='.urlencode($this->view->error_message));					
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			if(strlen($_GET['id']) > 0)
			{
				$med = Doctrine::getTable('Nutrition')->find($_GET['id']);
				$this->retainValues($med->toArray());
				$medarray = $med->toArray();
				$clientid = $medarray['clientid'];


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
						->where('id = ?', $client);
					$clientexec = $client->execute();
					$clientarray = $clientexec->toArray();
					$this->view->client_name = $clientarray[0]['client_name'];
					$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
				}
			}
			else
			{


				if($logininfo->clientid > 0)
				{
					$client = $logininfo->clientid;


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
		
		public function editmedicationAction()
		{
			$this->view->formid = Pms_CommonData::getFormId('Medication');
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			$this->_helper->viewRenderer('addmedication');
		
			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_Medication();
		
				if($client_form->validate($_POST))
				{
					$client_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'medication/listmedication?flg=suc&mes='.urlencode($this->view->error_message));					
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			if(strlen($_GET['id']) > 0)
			{
				$med = Doctrine::getTable('Medication')->find($_GET['id']);
				$this->retainValues($med->toArray());
				$medarray = $med->toArray();
				$clientid = $medarray['clientid'];
		
		
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
								->where('id = ?', $client);
					$clientexec = $client->execute();
					$clientarray = $clientexec->toArray();
					$this->view->client_name = $clientarray[0]['client_name'];
					$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
				}
			}
			else
			{
		
		
				if($logininfo->clientid > 0)
				{
					$client = $logininfo->clientid;
		
		
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
		

		public function editreceiptmedicationAction()
		{
			$this->view->formid = Pms_CommonData::getFormId('MedicationReceipt');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$this->_helper->viewRenderer('addreceiptmedication');

			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_MedicationReceipt();

				if($client_form->validate($_POST))
				{
					$client_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'medication/listreceiptmedication?flg=suc&mes='.urlencode($this->view->error_message));					
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			if(strlen($_GET['id']) > 0)
			{
				$med = Doctrine::getTable('MedicationReceipt')->find($_GET['id']);
				$this->retainValues($med->toArray());
				$medarray = $med->toArray();
				$clientid = $medarray['clientid'];


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
						->where('id = ?', $client);
					$clientexec = $client->execute();
					$clientarray = $clientexec->toArray();
					$this->view->client_name = $clientarray[0]['client_name'];
					$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
				}
			}
			else
			{


				if($logininfo->clientid > 0)
				{
					$client = $logininfo->clientid;


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

		public function listmedicationoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				;
			}
		}
		
		public function listmedicationnutritionoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				;
			}
		}

		public function listreceiptmedicationoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				;
			}
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$columnarray = array("pk" => "id", "nm" => "name", "mf" => "manufacturer");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$med = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('isdelete = 0 ' . $where)
				->andWhere("extra = 0")
				->andWhere('name!=""')
				->groupBy('name') //stupid cow worshipers
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$medexec = $med->execute();
			$medarray = $medexec->toArray();


			$limit = 50;
			$med->select("*");
			$med->limit($limit);
			$med->offset($_GET['pgno'] * $limit);

			$medlimitexec = $med->execute();
			$medlimit = $medlimitexec->toArray();

			$grid = new Pms_Grid($medlimit, 1, count($medarray), "listmedication.html");
			$this->view->medicationgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("mednavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['medicationlist'] = $this->view->render('medication/fetchlist.html');

			echo json_encode($response);
			exit;
		}
		
		public function fetchlistnutritionAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');
		
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		
			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}
		
			$columnarray = array("pk" => "id", "nm" => "name", "mf" => "manufacturer");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$med = Doctrine_Query::create()
			->select('*')
			->from('Nutrition')
			->where('isdelete = 0 ' . $where)
			->andWhere("extra = 0")
			->andWhere('name!=""')
			->groupBy('name') //stupid cow worshipers
			->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$medexec = $med->execute();
			$medarray = $medexec->toArray();
		
		
			$limit = 50;
			$med->select("*");
			$med->limit($limit);
			$med->offset($_GET['pgno'] * $limit);
		
			$medlimitexec = $med->execute();
			$medlimit = $medlimitexec->toArray();
		
			$grid = new Pms_Grid($medlimit, 1, count($medarray), "listmedicationnutrition.html");
			$this->view->medicationgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("mednavigationnutrition.html", 5, $_GET['pgno'], $limit);
		
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['medicationlist'] = $this->view->render('medication/fetchlistnutrition.html');
		
			echo json_encode($response);
			exit;
		}
		
		

		public function fetchindexlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if(strlen($_REQUEST['ltr']) > 0)
			{
				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('SYSDAT');
				$conn = $manager->getCurrentConnection();
				$querystr = "select m.id,m.name, m.pkgsz, m.manufacturer from (
			select distinct(name),min(id)as id, package_size as pkgsz, manufacturer from medication_index  group by name
			)as m
			inner join medication_index b on m.id=b.id
			where(trim(lower(m.name)) like trim(lower(:search_string))) and isdelete=0";
				$query = $conn->prepare($querystr);
				$query->bindValue(':search_string', trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%");
				
				$dropexec = $query->execute();
				$droparray = $query->fetchAll();
			}
			else
			{
				$droparray = array();
			}

			$grid = new Pms_Grid($droparray, 1, count($droparray), "listindexmedication.html");
			$this->view->medicationgrid = $grid->renderGrid();

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['medicationlist'] = $this->view->render('medication/fetchindexlist.html');

			echo json_encode($response);
			exit;
		}

		public function fetchreceiptlistAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$columnarray = array("pk" => "id", "nm" => "name", "mf" => "manufacturer");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

			$med = Doctrine_Query::create()
				->select('*')
				->from('MedicationReceipt')
				->where('isdelete = 0 ' . $where)
				->andWhere("extra = 0")
				->andWhere('name!=""')
				->groupBy('name') //stupid cow worshipers
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$medexec = $med->execute();
			$medarray = $medexec->toArray();

			$limit = 50;
			$med->select("*");
			$med->limit($limit);
			$med->offset($_GET['pgno'] * $limit);

			$medlimitexec = $med->execute();
			$medlimit = $medlimitexec->toArray();

			$grid = new Pms_Grid($medlimit, 1, count($medarray), "listreceiptmedication.html");
			$this->view->medicationgrid = $grid->renderGrid();

			$this->view->navigation = $grid->dotnavigation("medreceiptnavigation.html", 5, $_GET['pgno'], $limit);


			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();

			$response['callBackParameters']['medicationlist'] = $this->view->render('medication/receiptfetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function bedarfsmediclistoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
 
			if($_GET['delid'] > 0)
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$thrash = Doctrine::getTable('BedarfsmedicationMaster')->find($_GET['delid']);
				$thrash->isdelete = 1;
				$thrash->save();
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
		}

		public function fetchlistbedarfsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}

			$columnarray = array("pk" => "id", "nm" => "title");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$med = Doctrine_Query::create()
				->select('count(*)')
				->from('BedarfsmedicationMaster')
				->where('isdelete = 0 ')
				->andWhere("clientid='" . $clientid . "'")
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$medexec = $med->execute();
			$medarray = $medexec->toArray();

			$limit = 50;
			$med->select('*');
			$med->limit($limit);
			$med->offset($_GET['pgno'] * $limit);

			$medlimitexec = $med->execute();
			$medlimit = $medlimitexec->toArray();


			$this->view->{"style" . $_GET['pgno']} = "active";

			$grid = new Pms_Grid($medlimit, 1, $medarray[0]['count'], "bedarfsmediclist.html");
			$this->view->medicationgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("mednavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['medicationlist'] = $this->view->render('medication/fetchlistbedarfs.html');

			echo json_encode($response);
			exit;
		}

		public function bedarfsmedicationaddAction()
		{	
			//ISPC - 2124 - add indikation, concentration, etc. like as adding a new line pacient medication
			//deprecated do not use - ISPC-2554 Carmen 07.04.2020 - moved to bedarfsmedicationedit
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			$this->view->userid = $userid;
			$this->view->medication_type = 'isbedarfs';
			$medications_array = array();
			$this->view->new_med = 1; 
			$this->view->medication_array = $medications_array;
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$modules = new Modules();
				
			/* ================ CLIENT USER DETAILS ======================= */
			$usr = new User();
			$all_users = $usr->getUserByClientid($clientid, '1', true);
			$this->view->all_users = $all_users;
			
			
			
			$pq = new User();
			$pqarr = $pq->getUserByClientid($clientid);
			
			$comma = ",";
			$userval = "'0'";
			
			foreach($pqarr as $key => $val)
			{
				$userval .= $comma . "'" . $val['id'] . "'";
				$comma = ",";
			}
			
			$usergroup = new Usergroup();
			$groupid = $usergroup->getMastergroupGroups($clientid, array('4'));
			
			$this->view->verordnetvon = $userid;
			
			$usr = new User();
			$users = $usr->getuserbyidsandGroupId($userval, $groupid, 1);
			
			$this->view->users = $users;
			$this->view->jsusers = json_encode($users);
				
			/* MMI functionality*/
			if($modules->checkModulePrivileges("87", $clientid))
			{
				$this->view->show_mmi = "1";
			}
			else
			{
				$this->view->show_mmi = "0";
			}
			
			/* ================ MEDICATION :: CLIENT EXTRA ======================= */
			//UNIT
			$medication_unit = MedicationUnit::client_medication_unit($clientid);
			
			foreach($medication_unit as $k=>$unit){
				$client_medication_extra['unit'][$unit['id']] = $unit['unit'];
			}
			$this->view->js_med_unit = json_encode($client_medication_extra['unit']);
			
			
			//TYPE
			$medication_types = MedicationType::client_medication_types($clientid);
			foreach($medication_types as $k=>$type){
				$client_medication_extra['type'][$type['id']] = $type['type'];
			}
			$this->view->js_med_type = json_encode($client_medication_extra['type']);
			
			//DOSAGE FORM
			$medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid);
			
			foreach($medication_dosage_forms as $k=>$df){
				$client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
			}
			$this->view->js_med_dosage_form = json_encode($client_medication_extra['dosage_form']);
			
			
			//INDICATIONS
			$medication_indications = MedicationIndications::client_medication_indications($clientid);
			
			foreach($medication_indications as $k=>$indication){
				$client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
				$client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
			}
			
			//TODO-1268
			uasort($client_medication_extra['indication'], array(new Pms_Sorter('name'), "_strcmp"));
			
			//TODO-1268
			// 		    $this->view->js_med_indication = json_encode($client_medication_extra['indication']);
			$js_med_indication = array_combine(
					array_map(function($key){ return ' '.$key; }, array_keys($client_medication_extra['indication'])),
					$client_medication_extra['indication']
					);
			$this->view->js_med_indication = json_encode($js_med_indication);
			$this->view->client_medication_extra = $client_medication_extra;			

			if($this->getRequest()->isPost())
			{
				$med = new Application_Form_BedarfsmedicationMaster();
				$patient_medication_form = new Application_Form_Medication();

				if($med->validate($_POST))
				{

					/*$a_post = $_POST;
					foreach($_POST['medication'] as $key => $val)
					{
						if(strlen($_POST['medication'][$key]) > 0 && strlen($_POST['hidd_medication'][$key]) < 1)
						{
							$a_post['newmedication'][$key] = $_POST['medication'][$key];
						}
					}


					if(is_array($a_post['newmedication']))
					{
						$dts = $patient_medication_form->InsertNewData($a_post);

						foreach($dts as $key => $dt)
						{
							$a_post['newhidd_medication'][$key] = $dt->id;
						}
					}

					$med->InsertData($a_post);*/
					
					foreach($_POST['medication_block'] as $type => $med_values)
					{
						$post_data = $med_values;
						foreach($med_values['medication'] as $amedikey => $amedi)
						{
							if(strlen($amedi) > 0 && empty($med_values['hidd_medication'][$amedikey]) && !empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey]))
							{
									
								$post_data['newmids'][$amedikey] = $med_values['drid'][$amedikey];
								$post_data['newmedication'][$amedikey] = $amedi;
							}
								
							if(strlen($amedi) > 0 && (!empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
							{
								$post_data['newmids'][$amedikey] = $med_values['hidd_medication'][$amedikey];
								$post_data['newmedication'][$amedikey] = $amedi;
							}
								
							if(strlen($amedi) > 0 && (empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
							{
								$post_data['newmedication'][$amedikey] = $amedi;
							}
							if($post_data[$amedikey]['source'] == '')
							{
								unset($post_data[$amedikey]['source']);
							}
						}
							
						if(is_array($post_data['newmedication']))
						{
							$dts = $patient_medication_form->InsertNewData($post_data);
								
							foreach($dts as $key => $dt)
							{
								$post_data['newhidd_medication'][$key] = $dt->id;
							}
						}
						//print_r($post_data); EXIT;
						$post_data['title'] = $_POST['title'];
						$insbid = $med->InsertData($post_data);	
							
					}//endforeach
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
					$this->_redirect(APP_BASE . "medication/bedarfsmedicationedit?bid=".$insbid);
				}
				else
				{
					$medarrayisb = array();
					foreach($_POST['medication_block']['isbedarfs'] as $keyisb=>$valuesisb)
					{
						if(is_numeric($keyisb))
						{
							foreach($valuesisb as $kvisb=>$vvisb)
							{
								$medarrayisb['isbedarfs'][$keyisb-1]['MedicationMaster'][$kvisb] = trim($vvisb);
							}
						}
						else 
						{
							foreach($valuesisb as $kvisb=>$vvisb)
							{								
								$medarrayisb['isbedarfs'][$kvisb-1][$keyisb] = trim($vvisb);	
							}
						}
					}
					//var_dump($medarrayisb); exit;
					$med->assignErrorMessages();
					//$this->retainValues($medarrayisb);
					$this->view->err = 1;
					$this->view->new_med = 0;
					$this->view->medication_array_err = $medarrayisb;
				}
			}


			/*if(is_array($_POST['hidd_medication']))
			{
				foreach($_POST['hidd_medication'] as $key => $val)
				{
					$a_medic[$key]['medication'] = $_POST['medication'][$key];
					$a_medic[$key]['hidd_medication'] = $_POST['hidd_medication'][$key];
				}
			}
			else
			{
				for($i = 0; $i < 6; $i++)
				{
					$a_medic[$i] = array('cnt' => $i);
				}
			}


			$this->view->jsmedcount = count($a_medic);
			$grid = new Pms_Grid($a_medic, 1, count($a_medic), "bedarfmedicationgrid.html");
			$this->view->medicgrid = $grid->renderGrid();
			$this->view->rowcount = count($a_medic);*/
		}

		public function bedarfsmedicationeditAction()
		{
			//ISPC - 2124 - add indikation, concentration, etc. like as adding a new line pacient medication
			$this->_helper->viewRenderer('bedarfsmedicationadd');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			$this->view->userid = $userid;
			$this->view->medication_type = 'isbedarfs';
			
 
			
			//ISPC-2554 Carmen 07.04.2020 change to have only one action for insert and update
			if(!$_GET['bid'])
			{
				$this->view->new_med = 1;
			}
			//--
			$medications_array = array();
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$modules = new Modules();
			
			/* ================ CLIENT USER DETAILS ======================= */
		    $usr = new User();
		    $all_users = $usr->getUserByClientid($clientid, '1', true);
		    $this->view->all_users = $all_users;
		    
		    
		    
		    $pq = new User();
		    $pqarr = $pq->getUserByClientid($clientid);
		    
		    $comma = ",";
		    $userval = "'0'";
		    
		    foreach($pqarr as $key => $val)
		    {
		        $userval .= $comma . "'" . $val['id'] . "'";
		        $comma = ",";
		    }
		    
		    $usergroup = new Usergroup();
		    $groupid = $usergroup->getMastergroupGroups($clientid, array('4'));
		    
		    $this->view->verordnetvon = $userid;
		    
		    $usr = new User();
		    $users = $usr->getuserbyidsandGroupId($userval, $groupid, 1);
		    
		    $this->view->users = $users;
		    $this->view->jsusers = json_encode($users);
			
			/* MMI functionality*/
			if($modules->checkModulePrivileges("87", $clientid))
			{
				$this->view->show_mmi = "1";
			}
			else
			{
				$this->view->show_mmi = "0";
			}
			
			/* ================ MEDICATION :: CLIENT EXTRA ======================= */
			//UNIT
			$medication_unit = MedicationUnit::client_medication_unit($clientid);
				
			foreach($medication_unit as $k=>$unit){
				$client_medication_extra['unit'][$unit['id']] = $unit['unit'];
			}
			$this->view->js_med_unit = json_encode($client_medication_extra['unit']);
				
				
			//TYPE
			$medication_types = MedicationType::client_medication_types($clientid);
			foreach($medication_types as $k=>$type){
				$client_medication_extra['type'][$type['id']] = $type['type'];
			}
			$this->view->js_med_type = json_encode($client_medication_extra['type']);
			
			//DOSAGE FORM
			$medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid,true); // retrive all- incliding extra
			//ISPC-2554
			$mmi_dosage_custom = array();
			$medication_dosageform_mmi = array();
			$cl_dosageform = array();
			//--
			foreach($medication_dosage_forms as $k=>$df){
				if($df['extra'] == 0){
					$client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
				}
				//ISPC-2554 pct.1 Carmen 07.04.2020
				if($modules->checkModulePrivileges("87", $clientid))
				{
					if($df['isfrommmi'] == 1)
					{
						$mmi_codes[$k] = $df['mmi_code'];
						$mmi_dosage_custom[$df['id']] = $df['dosage_form'];
					}
				}
				else
				{
					if($df['isfrommmi'] == 1)
					{
						$mmi_dosage_custom[$df['id']] = $df['dosage_form'];
					}
				}
				//--
			}
				
			//ISPC-2554 pct.1 Carmen 07.04.2020
			if(!$modules->checkModulePrivileges("87", $clientid))
			{
				$cl_dosageform = $client_medication_extra['dosage_form'];
				//TODO-3444 Ancuta 17.09.2020
				//$all_dosageform = $cl_dosageform + $mmi_dosage_custom;
				if(empty($cl_dosageform)){
				    $cl_dosageform = array();
				}
				$all_dosageform = array_merge($cl_dosageform,$mmi_dosage_custom);
				//--
			}
			else
			{
				$all_dosageform = $client_medication_extra['dosage_form'];
			}
			natcasesort($all_dosageform);
			$client_medication_extra['dosage_form'] = $all_dosageform;
			//--
			$this->view->js_med_dosage_form = json_encode($client_medication_extra['dosage_form']);
				
			//ISPC-2554 pct.1 Carmen 07.04.2020
			if($modules->checkModulePrivileges("87", $clientid))
			{
				$medication_dosagefrom_mmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();
				//var_dump($medication_dosagefrom_mmi); exit;
				if(!empty($medication_dosagefrom_mmi))
				{
					foreach($medication_dosagefrom_mmi as $kr => $vr)
					{
						if(in_array($vr['dosageform_code'], $mmi_codes))
						{
							unset($medication_dosagefrom_mmi[$kr]);
						}
						else
						{
							$medication_dosageform_mmi['mmi_'.$vr['dosageform_code']] = $vr['dosageform_name'];
						}
					}
				}
			}
			//--
				
			$medication_dosageform_mmi_all = $mmi_dosage_custom + $medication_dosageform_mmi;
			asort($medication_dosageform_mmi_all);
			$client_medication_extra['dosageform_mmi'] = $medication_dosageform_mmi_all;
			foreach($medication_dosageform_mmi_all as $kr => $vr)
			{
				$medication_dosageform_mmi_all_forjs[] = array($kr, $vr);
			}
			$this->view->js_med_dosageform_mmi = json_encode($medication_dosageform_mmi_all_forjs);
		
				
			//INDICATIONS
			$medication_indications = MedicationIndications::client_medication_indications($clientid);
				
			foreach($medication_indications as $k=>$indication){
				$client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
				$client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
			}
			
			//TODO-1268
			uasort($client_medication_extra['indication'], array(new Pms_Sorter('name'), "_strcmp"));
				
			//TODO-1268
			// 		    $this->view->js_med_indication = json_encode($client_medication_extra['indication']);
			$js_med_indication = array_combine(
					array_map(function($key){ return ' '.$key; }, array_keys($client_medication_extra['indication'])),
					$client_medication_extra['indication']
					);
			
			$this->view->js_med_indication = json_encode($js_med_indication);
			$this->view->client_medication_extra = $client_medication_extra;
			//$this->view->client_medication_extra = $client_medication_extra; ISPC-2554
						
			if($_GET['delid'] > 0)
			{
				$bm = Doctrine::getTable('Bedarfsmedication')->find($_GET['delid']);
				$bm->delete();
			}

			if($this->getRequest()->isPost())
			{
				$med = new Application_Form_BedarfsmedicationMaster();
				$patient_medication_form = new Application_Form_Medication();

				if($med->validate($_POST))
				{
					/*$a_post = $_POST;
					foreach($_POST['medication'] as $key => $val)
					{
						if(strlen($_POST['medication'][$key]) > 0 && strlen($_POST['hidd_medication'][$key]) < 1)
						{
							$a_post['newmedication'][$key] = $_POST['medication'][$key];
						}
					}

					if(is_array($a_post['newmedication']))
					{
						$dts = $patient_medication_form->InsertNewData($a_post);

						foreach($dts as $key => $dt)
						{
							$a_post['newhidd_medication'][$key] = $dt->id;
						}
					}

					$med->UpdateData($a_post);*/
				//var_dump($_POST['medication_block']); EXIT;	
					foreach($_POST['medication_block'] as $type => $med_values)
					{
							$post_data = $med_values;
							foreach($med_values['medication'] as $amedikey => $amedi)
							{
								if(strlen($amedi) > 0 && empty($med_values['hidd_medication'][$amedikey]) && !empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey]))
								{
					
									$post_data['newmids'][$amedikey] = $med_values['drid'][$amedikey];
									$post_data['newmedication'][$amedikey] = $amedi;
								}
					
								if(strlen($amedi) > 0 && (!empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
								{
									$post_data['newmids'][$amedikey] = $med_values['hidd_medication'][$amedikey];
									$post_data['newmedication'][$amedikey] = $amedi;
								}
					
								if(strlen($amedi) > 0 && (empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
								{
									$post_data['newmedication'][$amedikey] = $amedi;
								}
								if($post_data[$amedikey]['source'] == '')
								{
									unset($post_data[$amedikey]['source']);
								}
								
							}
					
							if(is_array($post_data['newmedication']))
							{
								$dts = $patient_medication_form->InsertNewData($post_data);
													
								foreach($dts as $key => $dt)
								{
									$post_data['newhidd_medication'][$key] = $dt->id;
								}
							}
							//print_r($post_data); EXIT;
							$post_data['title'] = $_POST['title'];
							$med->UpdateData($post_data);
							
					
						}//endforeach					
					
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . "medication/bedarfsmediclist?flg=succ&mes=".urlencode($this->view->error_message));
				}
				else
				{
					//var_dump($_POST['medication_block']['isbedarfs']); exit;
					$medarrayisb = array();
					foreach($_POST['medication_block']['isbedarfs'] as $keyisb=>$valuesisb)
					{
						if(is_numeric($keyisb))
						{
							foreach($valuesisb as $kvisb=>$vvisb)
							{
								$medarrayisb['isbedarfs'][$keyisb-1]['MedicationMaster'][$kvisb] = trim($vvisb);
							}
						}
						else
						{
							foreach($valuesisb as $kvisb=>$vvisb)
							{
								$medarrayisb['isbedarfs'][$kvisb-1][$keyisb] = trim($vvisb);
							}
						}
					}
					//var_dump($medarrayisb); exit;
					$med->assignErrorMessages();
					//$this->retainValues($medarrayisb);
					$this->view->err = 1;
					//ISPC-2554 Carmen 07.04.2020 to have only one action for insert and update
					if(!$_GET['bid'])
					{
						$this->view->new_med = 0;
					}
					//--
					$this->view->medication_array_err = $medarrayisb;
				}
			}
				
			if($_GET['bid'])
			{
				$bm = new BedarfsmedicationMaster();
				$bedsarr = $bm->getbedarfsmedicationById($_GET['bid']);
				$this->view->title = $bedsarr[0]['title'];
					
				$bedm = new Bedarfsmedication();
				$a_medic = $bedm->getbedarfsmedication($_GET['bid']);
				//started
				$med = new Medication();
	
				$med_ids[] = '9999999999';
				foreach($a_medic as $k_med => $v_med)
				{
					$medications_array[$this->view->medication_type][] = $v_med;
					//$med_details[$v_med['medication_id']] = $v_med;
					//$med_details[$v_med['medication_id']]['medication_id'] = $v_med['medication_id'];
	
					$med_ids[] = $v_med['medication_id'];
				}
				
				// get medication details
				$med = new Medication();
				// 		    $master_medication_array = $med->master_medications_get($med_ids, false); //= only names are fetched here
				$master_medication_array = $med->getMedicationById($med_ids, true); //changed to this so I can fetch pzn, etc..
				//var_dump($medications_array); exit;
				
				foreach($medications_array as $medication_type => $med_array)
				{
				
					foreach($med_array as $km=>$vm)
					{
						// #################################################################
						// MEDICATION NAME
						// #################################################################
						$medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_id']]['name'];
						
						//append all the info from medication_master table
						$medications_array[$medication_type ][$km]['MedicationMaster'] = $master_medication_array[$vm['medication_id']];
						
						// #################################################################
						// DOSAGE
						// #################################################################
						$medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage'];
				
						$medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
						
						// ############################################################
						// Extra details  - drug / unit/ type / indication / importance
						// ############################################################
				
						$medications_array[$medication_type ][$km]['drug'] =  $vm['drug'];
						$medications_array[$medication_type ][$km]['unit'] =  $client_medication_extra['unit'][$vm['unit']]['unit'];
						$medications_array[$medication_type ][$km]['unit_id'] =  $vm['unit'];
						$medications_array[$medication_type ][$km]['type'] =  $client_medication_extra['type'][$vm['type']]['type'];
						$medications_array[$medication_type ][$km]['type_id'] =  $vm['type'];
						$medications_array[$medication_type ][$km]['indication'] =  $client_medication_extra['indication'][$vm['indication']]['name'];
						$medications_array[$medication_type ][$km]['indication_id'] =  $vm['indication'];
						$medications_array[$medication_type ][$km]['indication_color'] =  $client_medication_extra['indication'][$vm['indication']]['color'];;
						$medications_array[$medication_type ][$km]['importance'] =  $vm['importance'];
						//$medications_array[$medication_type ][$km]['dosage_form'] =  $client_medication_extra['dosage_form'][$vm['dosage_form']]['dosage_form']; ISPC-2554
						$medications_array[$medication_type ][$km]['dosage_form_id'] =  $vm['dosage_form'];
						$medications_array[$medication_type ][$km]['concentration'] =  $vm['concentration'];
						
					}
			
				}
	
				$this->view->medication_array = $medications_array;
				if(count($medications_array) == 0)
				{
					$this->view->new_med = 1;
				}
				else 
				{
					$this->view->new_med = 0;
				}
				
				
				/*$medarr = $med->getMedicationById($med_ids);
	
				foreach($medarr as $k_med_arr => $v_med_arr)
				{
					$medications_array[$k_med_arr] = $v_med_arr;
					$medications_array[$k_med_arr]['drid'] = $med_details[$v_med_arr['id']]['id'];
					$medications_array[$k_med_arr]['medication_id'] = $med_details[$v_med_arr['id']]['medication_id'];
					if(strlen($v_med_arr['dosage']) == '0')
					{
						$medications_array[$k_med_arr]['dosage'] = trim($med_details[$v_med_arr['id']]['dosage']);
					}
	
					if(strlen($v_med_arr['comments']) == '0')
					{
						$medications_array[$k_med_arr]['comments'] = trim($med_details[$v_med_arr['id']]['comments']);
					}
				}
				//end
				$meds_cnt = count($medications_array);
				if($meds_cnt < 6)
				{
	
					for($i = ($meds_cnt + 1); $i <= 6; $i++)
					{
						$medications_array[$i]['medication'] = "";
						$medications_array[$i]['drid'] = "";
						$medications_array[$i]['medication_id'] = "";
						$medications_array[$i]['hidd_medication'] = "";
						$medications_array[$i]['id'] = "";
						$medications_array[$i]['name'] = "";
						$medications_array[$i]['dosage'] = "";
						$medications_array[$i]['comments'] = "";
					}
				}
	
				$this->view->jsmedcount = count($medications_array);
				$grid = new Pms_Grid($medications_array, 1, count($medications_array), "bedarfmedicationgrid.html");
				$this->view->medicgrid = $grid->renderGrid();
				$this->view->rowcount = count($medications_array);*/
			}
				
		}

		public function getjsondataAction()
		{
			if($_GET['cid'] > 0)
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('clientid= ? ', $_GET['cid'])
					->andWhere('isdelete= ?', 0);

				$track = $user->execute();
				echo json_encode($track->toArray());
				exit;
			}
			else
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('isdelete = ?', 0)
					->andWhere('usertype != ?', 'SA');
				$track = $user->execute();

				echo json_encode($track->toArray());
				exit;
			}
		}

		public function deletemedicationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			//$this->_helper->viewRenderer('listmedication');

			$ids = split(",", $_GET['id']);

			for($i = 0; $i < sizeof($ids); $i++)
			{
				$thrash = Doctrine::getTable('Medication')->find($ids[$i]);
				$thrash->isdelete = 1;
				$thrash->save();

				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'medication/listmedication?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}

		public function deletemedicationnutritionAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			//$this->_helper->viewRenderer('listmedicationnutrition');
		
			$ids = split(",", $_GET['id']);
		
			for($i = 0; $i < sizeof($ids); $i++)
			{
				$thrash = Doctrine::getTable('Nutrition')->find($ids[$i]);
				$thrash->isdelete = 1;
				$thrash->save();
		
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'medication/listreceiptmedication?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}
		
		public function deletereceiptmedicationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			//$this->_helper->viewRenderer('listreceiptmedication');

			$ids = split(",", $_GET['id']);

			for($i = 0; $i < sizeof($ids); $i++)
			{
				$thrash = Doctrine::getTable('MedicationReceipt')->find($ids[$i]);
				$thrash->isdelete = 1;
				$thrash->save();

				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'medication/listreceiptmedication?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}

		public function fetchdropdownAction()
		{
			$this->_helper->layout()->disableLayout();
		    $this->_helper->viewRenderer->setNoRender(true);
				
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			
			$searchtext = trim($_REQUEST['ltr']);
			$pms_mmi =  new Pms_MMI();
			$pms_mmi->setClientid($clientid);
			$pms_mmi->setHasMmi();
			$pms_mmi->setHasPersonalList(true);
			$drug_list_arr = $pms_mmi->getDrugList($searchtext);
			
			
			/*
		     $querystr = "
			select m.id,m.name, m.pkgsz from (select distinct(name),min(id)as id, package_size as pkgsz from medication_master where clientid = '" . $clientid . "' and extra=0 and isdelete=0  group by name)as m
			inner join medication_master b on m.id=b.id
			where(trim(lower(m.name)) like trim(lower(:search_string))) and isdelete=0 and clientid = '" . $clientid . "' and extra=0";
			*/

			$response = array();
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "medicdropdiv";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['didiv'] = $_REQUEST['mid'];
			$response['callBackParameters']['medicationarray'] = $drug_list_arr;

			$this->_helper->json->sendJson($response);
		}

		public function fetchdropdownreceiptAction()
		{
			$this->_helper->viewRenderer('patientmasteradd');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(strlen($_REQUEST['ltr']) > 0)
			{

				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('SYSDAT');
				$conn = $manager->getCurrentConnection();
				$querystr = "select m.id,m.name, m.pkgsz from (
			select distinct(name),min(id)as id, package_size as pkgsz from medication_receipt  group by name
			)as m
			inner join medication_receipt b on m.id=b.id
			where(trim(lower(m.name)) like trim(lower(:search_string))) and isdelete=0 and clientid = '" . $clientid . "' and extra=0";
				$query = $conn->prepare($querystr);
				$query->bindValue(':search_string', trim(mb_strtolower($_REQUEST['ltr'], 'UTF-8'))."%");
				
				$dropexec = $query->execute();
				$droparray = $query->fetchAll();
			}
			else
			{
				$droparray = array();
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "medicdropdiv";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['didiv'] = $_REQUEST['mid'];
			$response['callBackParameters']['medicationarray'] = $droparray;

			echo json_encode($response);
			exit;
		}

		public function medicationreportAction()
		{
			$user = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('char_length(name)>80')
				->andWhere('isdelete= 0');

			$track = $user->execute();


			if($track)
			{
				$trackarr = $track->toArray();

				$comma = "";
				$ver = "";

				for($i = 0; $i < count($trackarr); $i++)
				{
					$ver .= $comma . $trackarr[$i]['id'];
					$comma = ",";
				}
			}

			$doc = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("medication_master_id  in (" . $ver . ")")
				->andWhere('isdelete= 0');
			$cst = $doc->execute();

			if($cst)
			{
				$medarr = $cst->toArray();
			}
			$table = '<table width="100%" cellpadding="0" cellspacing = "0" border ="1" class="datatable"  >
		<tr><td><strong>Epid</strong></td><td width="20%"><strong>User Name</strong></td><td><strong>Medication</strong></td></tr>';
			foreach($medarr as $key => $value)
			{
				$epid = Pms_CommonData::getEpid($value['ipid']);
				$newarr = Pms_DataTable::search($trackarr, $value['medication_master_id'], 'id');
				$name = $trackarr[$newarr]['name'];
				$clientid = $trackarr[$newarr]['clientid'];
				$clientdata = Pms_CommonData::getUserDataById($value['create_user']);

				$clientname = $clientdata[0]['last_name'] . " " . $clientdata[0]['first_name'];

				$table .= '<tr>
			<td>' . $epid . '</td>
			<td>' . $clientname . '</td>
			<td>' . $name . '</td>
			</tr>';
			}
			$table .='</table>';

			$this->view->table = $table;
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}


		/**
		 * 2016.12.xx ... 2017.04.25
		 * 
		 * ispc-1864 p.4
		 * 
		 * BTM page. 
		 * at the bottom is the long LOG. 
		 * this gets longer and longer. 
		 * please move this to a page where only the log is and maby add a pagination so if we have 1.000.000 entries the page doesnt crash
		 * 
		 */
		public function btmbuchhistoryAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$groupid = $logininfo->groupid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patient', $userid, 'canview');

			$btm_perms = new BtmGroupPermissions();
			$btm_permisions = $btm_perms->get_group_permissions($clientid, Usergroup::getMasterGroup($groupid));
			

			if(!$logininfo->clientid 
					|| ((!$return || $btm_permisions['use'] != '1') && $logininfo->usertype != 'SA'))
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->view->clientid = $clientid;
			$this->view->userid = $userid;
			
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			
			$this->view->lieferung_method = $btm_permisions['method_lieferung'];
			$this->view->btm_permisions = $btm_permisions;
			$this->_helper->viewRenderer('btmbuchhistory');
			
			
			$filter_by_user =
			$this->view->selectuser = ( ! empty($_POST['selectuser'])) ? (int)$_POST['selectuser'] : 0;
					
			$this->view->filteron =  ( ! empty($_POST['filteron'])) ? $_POST['filteron'] : '';
			
			$years = array_merge(array("all"=>"Alle jahre"),  range(date("Y"), 2011));
			$this->view->selectyear_array = array_combine ($years,$years);
						
			$filter_by_year =
			$this->view->selectyear = ( ! empty($_POST['selectyear'])) ? (int)$_POST['selectyear'] : 0;
			
			//method names
			$methodsarr = Medication::get_methodsarr();
		
			
			//only for "BTM Verantwortliche" users
			$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');
			if ( (empty($btm_notification_users) || ! in_array($logininfo->userid, array_column($btm_notification_users, 'user')))
					&&  $logininfo->usertype != 'SA')
			{
				$btm_special_user = false;
							
			} else {
			
				$btm_special_user = true;
			}
			$this->view->btm_special_user = $btm_special_user;
			
			
			
			$print_pdf_with_filters =  false;
			if($this->getRequest()->isPost() && $_POST['print_pdf'] == 'print_pdf_with_filters') 
			{
				$print_pdf_with_filters =  true;
			}
			
			
			//@todo : MOVE this xhr into a private function
// 			xhr post for correction

// 			ispc 1864 p.11
// 			11) add a new method "Correction"
// 			this method is available only to BTM Verantwortliche users.
// 			if selected it relates to an older entry.
// 			correction --> select older entry --> correct it.
// 			example: correction --> usage from yesterday (usage of 10 morphin) --> change = ammount 10 -> 1
// 			this correction entry relates to an older entry and changes the ammount of that entry.
// 			ATTENTION: it DOES NOT really change the older entry. it does ADD a new correction event. so the older entry and all events since then are
// 			unchanged. we have one punctual correction event, which is added to BTM print at the moment the correction happens. it is for restting the ammounts to
// 			the correct level.
			if ($this->getRequest()->isXmlHttpRequest() && $_POST['action'] == "correction_validatePositiveStock") 
			{
			
				//only for "BTM Verantwortliche" users
				if ( ! $btm_special_user )
				{
					$btm_special_user = false;
					//why yu cumm in hia, yu byn a baad boy
					//this if should never execute
					$this->redirect(APP_BASE . "error/previlege");
					exit;
				
				}
				
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
				$post = $_POST;
				$post['clientid'] = $clientid;
				$post['medicationid'] = $post['old_medicationid'];
				$post['date_time'] = $post['old_done_date'];
				
				$mcbtmc_form = new Application_Form_MedicationClientBTMCorrection();
				$result = $mcbtmc_form->validate_PositiveStock($post);
				
				echo json_encode($result);
				exit;
				
			}
			elseif ($this->getRequest()->isXmlHttpRequest() && $_POST['action'] == "correction" ) {
				
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
				//only for "BTM Verantwortliche" users
// 				$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');
				if ( ! $btm_special_user )
				{
					$btm_special_user = false;
					//why yu cumm in hia, yu byn a baad boy
					//this if should never execute
					$this->redirect(APP_BASE . "error/previlege");
					exit;
				
				} else {
				
					$btm_special_user = true;
				
					//ispc 1864 p.9
					//documenting/correcting a method BEFORE that SEAL_DATE is not possible.
					$mcss  = new MedicationClientStockSeal();
					$mcss_seal_date = $mcss->get_client_last_seal($clientid);
					if (!empty($mcss_seal_date['seal_date'])) {
						$btm_seal_timestamp = strtotime($mcss_seal_date['seal_date']);
					} else {
						$btm_seal_timestamp = $mcss->get_default_seal_timestamp();
					}
	
					
					$mcbtmc_form = new Application_Form_MedicationClientBTMCorrection();
					
										
					if ( $mcbtmc_form->validate($_POST) ) {
						$mcbtmc_form->InsertData($_POST);
						$response = array("result" => true);
					}
					else
					{
						$mcbtmc_form->assignErrorMessages();
						$this->retainValues($_POST);
						$response = array("result" => false);
					}
					
					
				}
				
					
				header("Content-type: application/json; charset=UTF-8");
				
				echo json_encode($response);
				exit;
				
			}
			
			// this is not xhr, it can be a simple page request or a pdf_print
			if ( ! $this->getRequest()->isXmlHttpRequest() || $print_pdf_with_filters ) 
			{

				//get only the users with group permission
				$btm_groups_permisions = $btm_perms->get_all_groups_permissions($clientid);
					
				$group = Doctrine_Query::create()
				->select('id, groupmaster')
				->from('Usergroup')
				->where('isdelete =  0')
				->andWhere('clientid = ? ' , $clientid); // get all client users -> 06.03.2012
				$grouparray = $group->fetchArray();
					
				$groups_array = array();
				$all_groups = array();
			
				foreach($grouparray as $group)
				{
					if($btm_groups_permisions[$group['groupmaster']]['use'] == '1')
					{
						$groups_array[] = $group['id'];
					}
					$all_groups[] = $group['id'];
				}
					
					
					
					
				$groupusers = array();
				if (! empty ($groups_array)) {
					$groupusrs = Doctrine_Query::create()
					->select('id, title, user_title, last_name, first_name')
					->from('User')
					->whereIn('groupid', $groups_array)
					->andWhere('isdelete = 0')
					->andWhere('isactive = 0')
					->andWhere('clientid = ? ' , $clientid )
					->orderBy('last_name ASC' );
					$groupusers = $groupusrs->fetchArray();
				}
					
				$view_users =  array( 0 => $this->view->translate('allebenutzer'));
			
				foreach ($groupusers as $user) {
					$view_users[$user['id']] = trim( $user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'] );
				}
				$this->view->selectusersarray = $view_users;
					
					
				//get stocks
				$medicationsarray = array();
				$stocks = new MedicationClientStock();
				$stksarray = $stocks->get_all_medicationids($clientid);
				if ( ! empty($stksarray)) {
						
					$stks_medicationid_array =  array_column($stksarray, 'medicationid');
						
					$medarray = Doctrine_Query::create()
					->select('id, name, isdelete')
					->from('Medication')
					->whereIn('id', $stks_medicationid_array)
					->andWhere(' clientid = ? ' , $clientid )
					->fetchArray();
						
					foreach( $medarray as $medication )
					{
						if ($medication['isdelete'] == "1" || $stksarray[$medication['id']]['isdelete'] == "1") {
							// *** to signify that is deleted
							//@todo: add a legend what *** are
							$medication['name'] = "***" . $medication['name'];
						}
						$medicationsarray[ $medication['id'] ] = $medication;
					}
				}
				$this->view->medicationsarray = $medicationsarray;
					
				//not datatables, send a grid with the empty table
				$this->view->filteronpdfhiddeninputs = $filteroninputspdf;
			}
			
			
			//ajax request from datatables to fetch history, or to print pdf
			// ! if you change in the datatables you also edit the PDF !
			// ! if you change in the PDF you also edit the datatables !
			//@todo : MOVE this xhr into a private function, that we can later call for print , add a print_ALL variable for it
			if ( ($this->getRequest()->isXmlHttpRequest() && !empty($_REQUEST['ajax_btm_history']))	
				 || $print_pdf_with_filters
			) 
			{
						
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
					
				
				//datatables settings
				$offset = (!empty($_REQUEST['start']))  ? (int)$_REQUEST['start']  : 0 ;
				$length = (!empty($_REQUEST['length'])) ? (int)$_REQUEST['length'] : 50 ;
				
				
				//medis history like verlauf...
				
				{
					
					$selected_btm_array = array();
					if (count($_POST['filteron']) > 0 && count($_POST['tablefilter']) > 0 ) {					
						$selected_btm_array = $_POST['filteron']; 	
						
						$selected_btm_tables = $_POST['tablefilter']; //tresor, user, patient
						//@todo : MOVE this xhr into a private function,
					} else {
						//@todo: select NOT in maybe ? for now just no result
						
						$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
						$response['recordsTotal'] = 0;//$full_count;
						$response['recordsFiltered'] = 0;//count($resulted_data); // ??
						$response['data'] = array();
						header("Content-type: application/json; charset=UTF-8");
						
						echo json_encode($response);
						exit;
						
						return;
					}
				

			
					
					
					//start 'counting' so we can paginate
					//'counting' is done in multiple querys cause tables can be on different servers, and i don't create a federated table 
					// with federated, a union and orderby could be used to fetch data
					
					$r_MedicationClientHistory = array();
					if ( in_array("user", $selected_btm_tables) ) {
						$q_MedicationClientHistory = Doctrine_Query::create()
						->select('id,
						    self_id, 
						    IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as create_date, 
						    \'MedicationClientHistory\' as tb')
						->from('MedicationClientHistory')
						->where('isdelete =  0')
						->andWhere('clientid = ?' , $clientid)
						->andWhere('methodid != 13'); // 13 = correction event, we willl get this later
						
						if ( ! empty($selected_btm_array)) {
							$q_MedicationClientHistory->andwhereIn("medicationid" , $selected_btm_array);
						}					
						if ($filter_by_user != 0) {
							$q_MedicationClientHistory->andwhere("userid=? OR create_user=? OR change_user=?" , array($filter_by_user, $filter_by_user, $filter_by_user));
						}
						if ($filter_by_year != 0) {
							$q_MedicationClientHistory->andwhere("IF(done_date != '0000-00-00 00:00:00', YEAR(done_date), YEAR(create_date)) = ? ", $filter_by_year);
							
						}
						
						$r_MedicationClientHistory = $q_MedicationClientHistory->fetchArray();
						
					}

					$r_MedicationClientStock = array();
					if ( in_array("tresor", $selected_btm_tables) ) {
						
						$q_MedicationClientStock = Doctrine_Query::create()
						->select('id, 
						    IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as create_date, 
						    \'MedicationClientStock\' as tb')
						->from('MedicationClientStock')
						->where('isdelete =  0')
						->andWhere('clientid = ?' , $clientid)
						->andWhere('methodid != 13'); // 13 = correction event, we willl get this later
						
						if ( ! empty($selected_btm_array)) {
							$q_MedicationClientStock->andwhereIn("medicationid" , $selected_btm_array);
						}
						if ($filter_by_user != 0) {
							$q_MedicationClientStock->andwhere("userid=? OR create_user=? OR change_user=?" , array($filter_by_user, $filter_by_user, $filter_by_user));
						}
						if ($filter_by_year != 0) {
							$q_MedicationClientStock->andwhere("IF(done_date != '0000-00-00 00:00:00', YEAR(done_date), YEAR(create_date)) = ? ", $filter_by_year);
								
						}
						$r_MedicationClientStock = $q_MedicationClientStock->fetchArray();
					}

					$r_MedicationPatientHistory = array();
					if ( in_array("patient", $selected_btm_tables) ) {
					
						$q_MedicationPatientHistory = Doctrine_Query::create()
						->select('id,
						    self_id, 
						    IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as create_date, 
						    \'MedicationPatientHistory\' as tb'
						    )
						->from('MedicationPatientHistory')
						->where('isdelete =  0')
						->andWhere('clientid = ?' , $clientid)
						->andWhere('methodid != 13'); // 13 = correction event, we willl get this later
						//->andWhere('methodid = 8')
						//->andWhere('source = ?' , 'p');
						if ( ! empty($selected_btm_array)) {
							$q_MedicationPatientHistory->andwhereIn("medicationid" , $selected_btm_array);
						}
						if ($filter_by_user != 0) {
							$q_MedicationPatientHistory->andWhere("userid=? OR to_userid=? OR create_user=? or change_user=?" , array($filter_by_user, $filter_by_user, $filter_by_user, $filter_by_user));
						}
						if ($filter_by_year != 0) {
							$q_MedicationPatientHistory->andwhere("IF(done_date != '0000-00-00 00:00:00', YEAR(done_date), YEAR(create_date)) = ? ", $filter_by_year);
								
						}
						$r_MedicationPatientHistory = $q_MedicationPatientHistory->fetchArray();
					}
										
					$history_merge = array_merge($r_MedicationClientHistory, $r_MedicationClientStock, $r_MedicationPatientHistory);
					
					function copare_create_date($a, $b)
					{
						
						if ($a['tb'] == $b['tb'] && strtotime($a["create_date"]) == strtotime($b["create_date"]))
						{
							return $a["id"] - $b["id"];
						
						} 
						else 
						{
							return ( strtotime($a["create_date"]) - strtotime($b["create_date"]) ) ;
						}
					
						
					}
					function copare_create_date_descending($a, $b)
					{
					
						if ($a['tb'] == $b['tb'] && strtotime($a["create_date"]) == strtotime($b["create_date"]))
						{
							return -($a["id"] - $b["id"]) ;
					
						}
						else
						{
							return  -(strtotime($a["create_date"]) - strtotime($b["create_date"]));
						}
							
					
					}
				
					
					
					//sort ascending
					usort($history_merge, "copare_create_date_descending");

					$total_counter = count($history_merge);

					
// 					die (print_r($history_merge));
						
					//print all pages
					if ( $print_pdf_with_filters ) {
						$length = $total_counter ;
					}
					
					$fetch_details =  array();
					if ($total_counter <= $length) {
						//fetch all cause we nave only one page
						foreach ($history_merge as $history_row) {
							$fetch_details[ $history_row ['tb'] ] [] = $history_row ['id'];	
							
							if (isset($history_row ['self_id']) && $history_row ['self_id'] > 0) {
							    $fetch_details[ $history_row ['tb'] ] [] = $history_row ['self_id'];
							}
						}
					} else {
						//we need to paginate
						$i_start = $offset;
						for ($i = $i_start; $i<$i_start +$length; $i++ ){
							$fetch_details[ $history_merge [$i] ['tb'] ] [] = $history_merge [$i] ['id'];
							
							if (isset($history_merge [$i] ['self_id']) && $history_merge [$i] ['self_id'] > 0) {
							    $fetch_details[ $history_merge [$i] ['tb'] ] [] = $history_merge [$i] ['self_id'];
							}
						}
					}
					
// 					(print_r($history_merge));
// 					die(print_r($fetch_details));

					$clientStockData = 
					$r_MedicationClientStock =  array();
					if (isset($fetch_details['MedicationClientStock'])) {
						$r_MedicationClientStock = Doctrine_Query::create()
						->select('*, IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as create_date, \'MedicationClientStock\' as tb')
						->from('MedicationClientStock')
						->whereIn('id', $fetch_details['MedicationClientStock'])
						->fetchArray();
						
						$clientStockData = $r_MedicationClientStock;
// 						die(print_R($r_MedicationClientStock));	
					}
					
					$clientHistoryData = 
					$r_MedicationClientHistory =  array();
					if (isset($fetch_details['MedicationClientHistory'])) {
						$r_MedicationClientHistory = Doctrine_Query::create()
						->select('*, IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as create_date, \'MedicationClientHistory\' as tb')
						->from('MedicationClientHistory')
						->whereIn('id', $fetch_details['MedicationClientHistory'])
						->fetchArray();
						
						$clientHistoryData = $r_MedicationClientHistory;
// 						die(print_R($r_MedicationClientHistory));
					}
					
					$patient_usage_history = 
					$r_MedicationPatientHistory = array();
					if (isset($fetch_details['MedicationPatientHistory'])) {
						$r_MedicationPatientHistory = Doctrine_Query::create()
						->select('*, IF(done_date != \'0000-00-00 00:00:00\', done_date, create_date) as create_date, \'MedicationPatientHistory\' as tb')
						->from('MedicationPatientHistory')
						->whereIn('id', $fetch_details['MedicationPatientHistory'])
						->fetchArray();
						
						$patient_usage_history = $r_MedicationPatientHistory;
					}
					
// 					usort($r_MedicationClientStock, "copare_create_date");
// 					usort($r_MedicationClientHistory, "copare_create_date");
// 					usort($r_MedicationPatientHistory, "copare_create_date");
					
					$history_details_merge = array_merge($r_MedicationClientStock , $r_MedicationClientHistory, $r_MedicationPatientHistory);		
// 					die (print_r($r_MedicationClientHistory));
						
					//sort ascending
					usort($history_details_merge, "copare_create_date_descending");	
// 					die (print_r($history_details_merge));
		
						
				}
				
				
				
				$clientHistoryArray =  array();
				$clientStockArray =  array();
				
				
				
// 				$resulted_data[$i]['id'] = $i;
				// 						$resulted_data[$i]['entrydate'] = "111". rand(1, 88);
				// 						$resulted_data[$i]['process'] = "data". rand(1, 88);
				
				$resulted_data =  array();
				$resulted_data_array_id_table =  array();
				$resulted_data_counter = 0;
				
				$med_names_array = array(); // hold the medication id nice names
				$user_names_array = array(); // hold the user id nice names
				$ipid_details_arr = array(); // hold the ipid nice names and epid
				
				
				if ( ! empty($history_details_merge)) {

					
					//fetch correction events details, without the use of JOIN in the query
					$extra_userid_from_correction = array();
					$correction_new_id = array(); // will hold info/rows with the details
					$correction_id = array_column($history_details_merge, "id");
					$mcbtmc = new MedicationClientBTMCorrection();
					$client_corrections = $mcbtmc->get_by_correction_id( $correction_id , $clientid  );
					
					if (!empty($client_corrections)) {
						
						//attach correction event to our row
						
						foreach($history_details_merge as &$row_history) {
							
							foreach($client_corrections as $row_corrections) {
								
								if ( $row_history['tb'] == $row_corrections['correction_table'] 
										&& $row_history['id'] == $row_corrections['correction_id']) 
								{
									$row_history['correction_event'] = $row_corrections;
									$extra_userid_from_correction [] = $row_corrections['create_user'];
									$extra_userid_from_correction [] = $row_corrections['change_user'];
								}
								
							}
						}
					}		
					
					
					//fetch names of medicationid
					$medication_id_arr = array_column($history_details_merge, 'medicationid');
					$medication_id_arr = array_unique($medication_id_arr); 
					// @todo: remove id 0 from array
					//done
					$key = array_search('0', $medication_id_arr);
					if ($key !== false && isset($medication_id_arr[$key])){
						unset ($medication_id_arr[$key]);
					}
					if ( ! empty ($medication_id_arr)) {
						$med = Doctrine_Query::create()
						->select('id, name')
						->from('Medication') // INDEXBY id not used
						->whereIn('id', $medication_id_arr)
						->andWhere('clientid = ? ' , $clientid );
						$medarray = $med->fetchArray();
						foreach ( $medarray as $k ) {
							$med_names_array [ $k['id'] ] = $k ;
						}
					}					
					
				//fetch nice names of userid
					$user_id_arr =  array(0);
					
// 					$user_id_arr = array_merge( array_column($history_details_merge, 'userid'),
// 							array_column($history_details_merge, 'to_userid'),
// 							array_column($history_details_merge, 'create_user'),
// 							array_column($history_details_merge, 'change_user'),
// 							$extra_userid_from_correction
// 					);
					
					$user_colum = array_column($history_details_merge, 'userid');
					if ( ! empty($user_colum) && is_array($user_colum)) {
					   $user_id_arr = array_merge($user_id_arr, $user_colum);
					}
					$user_colum = array_column($history_details_merge, 'to_userid');
					if ( ! empty($user_colum) && is_array($user_colum)) {
					     $user_id_arr = array_merge($user_id_arr, $user_colum);
					}
					$user_colum = array_column($history_details_merge, 'create_user');
					if ( ! empty($user_colum) && is_array($user_colum)) {
					     $user_id_arr = array_merge($user_id_arr, $user_colum);
					}
					$user_colum = array_column($history_details_merge, 'change_user');
					if ( ! empty($user_colum) && is_array($user_colum)) {
					     $user_id_arr = array_merge($user_id_arr, $user_colum);
					}
					if ( ! empty($extra_userid_from_correction) && is_array($extra_userid_from_correction)) {
					     $user_id_arr = array_merge($user_id_arr, $extra_userid_from_correction);
					}
					$user_id_arr = array_unique($user_id_arr);
					
					//print_r($user_id_arr);
					// @todo: remove id 0 from array
					//done	
					$key = array_search('0', $user_id_arr);
					if ($key !== false && isset($user_id_arr[$key])){
						unset ($user_id_arr[$key]);
					}
					if ( ! empty ($user_id_arr)) {
						
						$user_names_array = User::getUsersNiceName($user_id_arr, $clientid);
				
					}
						
					
					
					//fetch nice names of patients
					$ipid_arr = array_column($history_details_merge, 'ipid');
					$ipid_arr = array_unique($ipid_arr);
					// @todo: remove id 0 from array
					//done
					$key = array_search('0', $ipid_arr);
					if ($key !== false && isset($ipid_arr[$key])){
						unset ($ipid_arr[$key]);
					}
					if ( ! empty ($ipid_arr)) {
						
						$ipid_details_arr = PatientMaster::getPatientsNiceName($ipid_arr);
						
					}
					
								
					
					//show the btm correction button only for "BTM Verantwortliche" users
// 					$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');
					if ( ! $btm_special_user )
					{
						
						$btm_special_user = false;
						
					} else {
						
						$btm_special_user = true;
						
						//ispc 1864 p.9
						//documenting/correcting a method BEFORE that SEAL_DATE is not possible.
						$mcss  = new MedicationClientStockSeal();
						$mcss_seal_date = $mcss->get_client_last_seal($clientid);
						if (!empty($mcss_seal_date['seal_date'])) {
							$btm_seal_timestamp = strtotime($mcss_seal_date['seal_date']);
						} else {
							$btm_seal_timestamp = $mcss->get_default_seal_timestamp();
						}
						
					}				
								
				}
// 				die(print_r($history_details_merge));
// 				die(print_r($ipid_details_arr));
// 				die(print_r($history_details_merge));
// 				die(print_r($user_names_array));

				$btmbuchhistory_lang = $this->view->translate('btmbuchhistory_lang');
				$translate_correction = $btmbuchhistory_lang['correction add event'];
							
				$interconnected_rows = array();
				$interconnected_patientid_2_userid = array();
				$interconnected_patientid_2_patientid = array();
				
				$connected_rows_array = array(); //is connected to another row 
				//$connected_rows_array[ 'thisid' ] ['thistablename'] = array( connected_id , connected_table );
				
				
// 				print_r($row);
				foreach ($history_details_merge as $grouped_key=>$row) {	
					
					$has_row_connected_here = false;
					$double_connected = false; //for verbrauch von benutzer
					
					$correction_id = false;
					
					$row['original_create_date'] =  $row['create_date'];
					$row['create_date'] =  date("d.m.Y H:i", strtotime($row['create_date']));
					
					$info_array = array(
							'id'			=> '',
							'entrydate'		=> $row['create_date'] . '<br/>' . $user_names_array [$row['create_user']] ['nice_name'],
							'medication'	=> $med_names_array [ $row['medicationid'] ] ['name'] ,
							'medicationid'	=> $row['medicationid'] ,
							'action'		=> '',
							'qty'			=> abs($row['amount']), //abs()
							'process'		=> '',//is filled in the switch 
							'type'			=> $row['tb'] . "<br> methodid: ". $row['methodid'] ."<br> id: " . $row['id'], 
							
							'table_name'	=> $row['tb'] ,
							
							'methodid'		=> $row['methodid'], 
							'row_id'		=> $row['id'], 
							'btm_correction'=> '', 
							
							'eventdate'		=> $row['original_create_date'],
							'eventuser'		=> $user_names_array [$row['create_user']] ['nice_name'],
							'amount'		=> ($row['amount']), 
							'userid'		=> (!empty($row['userid']) ? $row['userid'] : 0), 
							'ipid'			=> (!empty($row['ipid']) ? $row['ipid'] : 0), 
								
					);
					
					//add column for CORRECTION
					if ( $btm_special_user && ! in_array($row['methodid'], array("0","13")) ) {
						
						if ( $btm_seal_timestamp < strtotime($row['create_date']) ) {
							
							//@todo : do not correct methodid = 0
							 
							$info_array['btm_correction'] = <<<EOF
	<a class="button" style="float:none" onclick="correction(this);" data-id="{$row['id']}" href="javascript:void(0);">
		{$translate_correction}
	</a>
EOF;
																
						}
						
					}
					
					
					switch ($row['tb']) {
						
						case "MedicationClientStock":

							if ($row['self_id'] > 0) {
								//self_id allways connects in same table
								$connected_rows_array[ $row['id'] ] [ $row['tb'] ] = array("id"=> $row['self_id']  , "from_table"=>$row['tb'] );
							}
							
							switch ($row['methodid']) {
								
								case 0:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = '';								
									$text = '%s wurde hinzugefügt an Medikament/BTM';										
									$info_array['process'] = sprintf( $text,
											$info_array['medication']
									);	
								}							
								break;
								
								case 1:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
									if ($row['amount'] > 0) {
										//add to group
										$text = 'Benutzer: %s übergibt %d %s an Gruppe/Tresor';
										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']
										);
									} else {
										//remove from group and give to user
										$text =  "Gruppe/Tresor übergibt %d %s an Benutzer %s";
										$info_array['process'] = sprintf( $text,
												$info_array['qty'],
												$info_array['medication'],
												$user_names_array[$row['userid']]['nice_name']
										);
									}	
								}	
								break;
								
								case 2:{

									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($row['amount']);
									$text = 'Gruppe/Tresor Bestand von %s um %d erhöht durch %s %s';										
									$info_array['process'] = sprintf( $text,
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$info_array['sonstige_more']
									);
								}
								break;
								
								case 3:{

									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($row['amount']);
									
									//include sonstige comment
									if (trim($row['sonstige_more']) != '') {
										$info_array['sonstige_more'] = "[". $row['sonstige_more'] ."]";
									}
									
									$text = 'Gruppe/Tresor Bestand von %s um %d erhöht durch %s %s';
									$info_array['process'] = sprintf( $text, 
											$info_array['medication'], 
											$info_array['qty'], 
											$info_array['action'] , 
											$info_array['sonstige_more']
											);
								}
								break;
								
								case 4:{

									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($row['amount']);
									
									if ($row['amount'] > 0) {
										//add to group
										//received from user minus button
										$text = "Gruppe/Tresor erhält %d %s von Benutzer %s";
										$info_array['process'] = sprintf( $text,
												$info_array['qty'],
												$info_array['medication'],
												$user_names_array[$row['userid']]['nice_name']
										);
									} else {
										//remove from group and give to user, via group minus button
										$text =  "Gruppe/Tresor übergibt %d %s an Benutzer %s";
										$info_array['process'] = sprintf( $text,
												$info_array['qty'],
												$info_array['medication'],
												$user_names_array[$row['userid']]['nice_name']
										);
									}
								}
								break;
								
								case 5:{
									// there are 115 of them in my table...
									//tresor has given direclty to patient ? until 2015?
									$info_array['action'] .= $methodsarr[$row['methodid']];
										
									$text =  "Gruppe/Tresor übergibt %d %s an Patient %s durch %s !";
										
									$info_array['process'] = sprintf( $text,
								
											$info_array['qty'],
											$info_array['medication'],
												
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											$info_array['action']
									);
								
								}
								break;
								
								case 6:{
								
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($row['amount']);
										
									//include sonstige comment
									if (trim($row['sonstige_more']) != '') {
										$info_array['sonstige_more'] = "[". $row['sonstige_more'] ."]";
									}
										
									if ($row['amount'] > 0) {
										//add to group
										$text = 'Gruppe/Tresor Bestand von %s um %d erhöht durch %s %s';
									} else {
										$text = 'Gruppe/Tresor Bestand von %s um %d verringert durch %s %s';
									}
													
									$info_array['process'] = sprintf( $text,
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$info_array['sonstige_more']
									);
								}
								break;
								
								
								case 7:{
									// there is only 1 of them in my table...
									//tresor has given direclty to patient ?
									$info_array['action'] .= $methodsarr[$row['methodid']];
									
									$text =  "Gruppe/Tresor übergibt %d %s an Patient %s durch %s !";
									
									$info_array['process'] = sprintf( $text,
								
											$info_array['qty'],
											$info_array['medication'],
											
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											$info_array['action'] 
									);
								
								}
								break;
								
								
								case 9:{
									//this methodid should never be from 2015 onwards ! there are 2 of them in my table...
									//patient ruck directly into tresor ?
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = 'Gruppe/Tresor Bestand von %s um %d erhöht durch %s von %s !';
									$info_array['process'] = sprintf( $text,
												
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid']
									);
										
								}
								break;
								
								case 12:{
									//this methodid should never be from 2016 onwards ! there are some of them (in my dbf is only 1)... 
									//patient ruck back directly into user with methodid=9 and the userid using methodid=12 incremented also the tresor ? 
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = 'Gruppe/Tresor Bestand von %s um %d erhöht durch %s von %s !';
									$info_array['process'] = sprintf( $text,
											
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$user_names_array[$row['userid']]['nice_name']
									);
									
								}
								break;
								
								
							/* 	case 13:{
									//new correction method from ispc 1864
									
									if (empty($correction_new_id[ $row['tb'] ][ $row['id'] ])) {
										$text = "<font color='red'> Inform ADMIN, manual deleted correction event </font>";
										$info_array['process'] = $text;
										break;
									}
									
									$correction_id = $correction_new_id[ $row['tb'] ][ $row['id'] ] ['correction_id'];
									
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount_original']) . " -> ". $correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount'];
																		
									
									$text = $this->view->translate('btmbuchhistory_lang_correction_event_text');
										
									
									$info_array['process'] = sprintf( $text,

											abs($correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount_original'] ),
											$correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount'],
											
											//$user_names_array[$row['create_user']]['nice_name'],
											
											$row4correction[$row['tb']] [ $correction_id ] ['entrydate'],
											$row4correction[$row['tb']] [ $correction_id ] ['process']
									);
								
									
										
								}
								break; */
								
								default :{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = "<font color='red' style='color:#ff0000;'>ALERT ADMIN !</font> " . $row['tb'];
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name']
									);
								}
								
							}
							
						break;
							
						
							
						case "MedicationClientHistory":	
							
							
							if ($row['self_id'] > 0) {
								
								//self_id allways connects in same table
								//connects with MedicationClientHistory
								$connected_rows_array[ $row['id'] ] [ $row['tb'] ] = array("id" => $row['self_id']  , "from_table"=>$row['tb'] );
																
							} elseif ($row['stid'] > 0) {
								
								//connects with MedicationClientStock
								$connected_rows_array[ $row['id'] ] [$row['tb']] = array("id" =>  $row['stid']  , "from_table"=>"MedicationClientStock" );
								
							} elseif ($row['patient_stock_id'] > 0) {
								
								//connects with MedicationPatientHistory
								$connected_rows_array[ $row['id'] ] [$row['tb']] = array("id" =>  $row['patient_stock_id']  , "from_table"=>"MedicationPatientHistory" );
								
							}
							
							
// 							//every action, that involves 2 parties, will pass through this table 
							
// 							if ($row['self_id'] > 0 && isset($interconnected_rows ['MedicationClientHistory'] [$row['self_id']]) ){
								
// 								$has_row_connected_here = $row['self_id'];
// 								$has_row_connected_here_table = "MedicationClientHistory";
								
// 							} elseif ($row['patient_stock_id'] > 0 && isset($interconnected_rows ['MedicationPatientHistory'] [$row['patient_stock_id']]) ) {
								
// 								$has_row_connected_here = $row['patient_stock_id'];
// 								$has_row_connected_here_table = "MedicationPatientHistory";
// 								//die("sss");
								
								
															
// 							}elseif ($row['stid'] > 0 && isset($interconnected_rows ['MedicationClientStock'] [$row['stid']])){
								
// 								$has_row_connected_here = $row['stid'];
// 								$has_row_connected_here_table = "MedicationClientStock";
															
// 							}
							
// 							if ($row['patient_stock_id'] > 0) {
								
// 								$interconnected_patientid_2_userid [ $row['patient_stock_id'] ] = $row['id'];
// // 								die("ssssssss");
// 							}
															
							switch ($row['methodid']) {
								
								case 1:{
									//from the plus button
									$info_array['action'] .= $methodsarr[$row['methodid']];
// 									$info_array['qty'] = abs($row['amount']);
										
									if ($row['amount'] < 0 && $row['stid'] > 0) {
										//received medication from group
										//$text = "Benutzer %s erhielt %d %s von Gruppe/Tresor";
										$text =  "Benutzer %s übergibt %d %s an Gruppe/Tresor";
										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']
										);
									} else if ($row['amount'] >= 0 && $row['stid'] > 0) {
										$text = "Benutzer %s erhält %d %s von Gruppe/Tresor";
										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']
										);
									} else {
										//user 2 user
										//this will print a doublt-like info like: x sent to y, y received from x .. they are linked by self_id, so one could be hidden										
										$other_benutzer = '';
										$key = array_search($row['self_id'], array_column($r_MedicationClientHistory, 'id'));
										if (isset($r_MedicationClientHistory[$key])){
											$other_benutzer = $r_MedicationClientHistory[$key]['userid'];
											$other_benutzer = $user_names_array[$other_benutzer]['nice_name'];
										}
										
										if ($row['amount'] > 0){
											$text = "Benutzer %s erhält %d %s von Benutzer %s";
										} else {
											$text = "Benutzer %s übergibt %d %s an Benutzer %s";
										}
										
										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']	,
												$other_benutzer
										);
										
									}
										
								}	
								break;
								
								case 2:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									
									$text = '%s Bestand von %s um %d erhöht durch %s %s';
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$info_array['sonstige_more']
									);
									
								}
								break;
								
								
								case 3:{
									//19 rows
									$info_array['action'] .= $methodsarr[$row['methodid']];
									

									//include sonstige comment
									if (trim($row['sonstige_more']) != '') {
										$info_array['sonstige_more'] = "[". $row['sonstige_more'] ."]";
									}
									
									$text = '%s Bestand von %s um %d erhöht durch %s %s';
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$info_array['sonstige_more']
									);
								}
								
								case 4:{
									//from the minus button	
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
										
									if ($row['amount'] > 0 && $row['stid'] > 0) {
										//received medication from group
										$text = "Benutzer %s erhält %d %s von Gruppe/Tresor";
										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']
										);	
									}
									elseif ($row['amount'] <=0 && $row['stid'] > 0) {
										$text = "Benutzer %s übergibt %d %s an Gruppe/Tresor";
										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']
										);
									} else {
										//user 2 user 
										//this will print a doublt-like info like: x sent to y, y received from x .. they are linked by self_id, so one could be hidden 
										
										$other_benutzer = '';
										$key = array_search($row['self_id'], array_column($r_MedicationClientHistory, 'id'));
										if (isset($r_MedicationClientHistory[$key])){
											$other_benutzer = $r_MedicationClientHistory[$key]['userid'];
											$other_benutzer = $user_names_array[$other_benutzer]['nice_name'];
										}
										
										if ($row['amount'] > 0){
											$text = "Benutzer %s erhält %d %s von Benutzer %s" ;
										} else {
											$text = "Benutzer %s übergibt %d %s an Benutzer %s";
										}

										$info_array['process'] = sprintf( $text,
												$user_names_array[$row['userid']]['nice_name'],
												$info_array['qty'],
												$info_array['medication']	,
												$other_benutzer
										);
										
									}
										
								}
								break;
								
								
								case 5:{
									//minus dialog
									//take from user add to patient
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
// 									$text = "Patient %s erhält %d %s vom %s";
									$text = "Benutzer %s übergibt %d %s an Patient %s";
									//Abgabe an Patient: Thompson, Mohammed - MOE3002 erhält 2 Actraphane Ins. vom Ackermann, Vivien
																		
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											
											$info_array['qty'],
											$info_array['medication'],
											
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid']
											
									);
					
								}
								break;
								
								case 6:{							
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
								
									//include sonstige comment
									if (trim($row['sonstige_more']) != '') {
										$info_array['sonstige_more'] = "[". $row['sonstige_more'] ."]";
									}
								
									if ($row['amount'] > 0) {
										//add to group
										$text = '%s Bestand von %s um %d erhöht durch %s %s';
									} else {
										$text = '%s Bestand von %s um %d verringert durch %s %s';
									}
										
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['medication'],
											$info_array['qty'],
											$info_array['action'] ,
											$info_array['sonstige_more']
									);
								}
								break;
								
								case 7:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = "Benutzer %s übergibt %d %s an Patient %s";
									$info_array['process'] = sprintf( $text,
											
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['qty'],
											$info_array['medication'],
									
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid']
									);
								}
								break;
								
								case 8:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
// 									$info_array['qty'] = abs($row['amount']);
														
// 									Verbrauch: Thompson, Mohammed - MOE3002 5 r aus Bestand des Ackermann, Vivien
									$text = "Benutzer %s übergibt %d %s an Patient %s fur %s";
									
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['qty'],
											$info_array['medication'],
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											$info_array['action']
									);
								}
								break;
								
								case 9:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = "Benutzer %s erhält %d %s von Patient %s fur %s";
										
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['qty'],
											$info_array['medication'],
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											$info_array['action']
									);
								}
								break;
								
								case 12:{
									//get from patinent and give to user -  plus button
									$info_array['action'] .= $methodsarr[$row['methodid']];
									// 									$info_array['qty'] = abs($row['amount']);
										
									$text = "Benutzer %s erhält %d %s von Patient %s";
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$info_array['qty'],
											$info_array['medication'],
												
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid']
									);
										
								}
								break;
								
								
								/* case 13:{
									//new correction method from ispc 1864
										
									if (empty($correction_new_id[ $row['tb'] ][ $row['id'] ])) {
										$text = "<font color='red'> Inform ADMIN, manual deleted correction event </font>";
										$info_array['process'] = $text;
										break;
									}
										
									$correction_id = $correction_new_id[ $row['tb'] ][ $row['id'] ] ['correction_id'];
										
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount_original'] ) . " -> ". $correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount'];
																			
										
									$text = $this->view->translate('btmbuchhistory_lang_correction_event_text');
								
										
									$info_array['process'] = sprintf( $text,
								
											abs($correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount_original']),
											$correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount'],
												
											//$user_names_array[$row['create_user']]['nice_name'],
												
											$row4correction[$row['tb']] [ $correction_id ] ['entrydate'],
											$row4correction[$row['tb']] [ $correction_id ] ['process']
									);
								
								
								}
								break; */
								
								default :{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = "<font color='red' style='color:#ff0000;'>ALERT ADMIN !</font> " . $row['tb'];
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name']
									);
								}
								
							}							
						break;					
							
						case "MedicationPatientHistory":
							
														
							if ($row['self_id'] > 0) {
								//self_id allways connects in same table
								$connected_rows_array[ $row['id'] ] [ $row['tb'] ] = array("id"=> $row['self_id']  , "from_table"=>$row['tb'] );
							}
							
// 							if ($row['self_id'] > 0) {
							
// 								$interconnected_patientid_2_patientid [ $row['self_id'] ] = $row['id'];
							
// 								if ( isset($interconnected_patientid_2_userid[ $row['self_id'] ]) ) {
							
// 									$interconnected_patientid_2_userid [ $row['id'] ] = $interconnected_patientid_2_userid[ $row['self_id'] ];
// 								}
							
// 							}
							
							
// 							if ( isset($interconnected_patientid_2_userid[$row['id']]) ) {
														
// 								$has_row_connected_here = $interconnected_patientid_2_userid[$row['id']];
// 								$has_row_connected_here_table = "MedicationClientHistory";
// // 							
								
// 							} elseif ( isset($interconnected_patientid_2_patientid[ $row['id'] ]) ) {
// // 								
// 									//single
// 									$has_row_connected_here = $interconnected_patientid_2_patientid[$row['id']];
// 									$has_row_connected_here_table = "MedicationPatientHistory";
							
// 							}
							
							
							
							
							
							switch ($row['methodid']) {
								
								
								case 0:{
									//add new medication to patient list (without any amount, just info)
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = '';
									$text = '%s wurde hinzugefügt an Patient %s';	
									
									$info_array['process'] = sprintf( $text,
											$info_array['medication'],
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid']
									);
								}
								break;
								
								
								case 5:{
									//take from user add to patient
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
									$text = "Patient %s erhält %d %s vom %s";
								
									$info_array['process'] = sprintf( $text,
												
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
												
											$info_array['qty'],
											$info_array['medication'],
											$user_names_array[$row['userid']]['nice_name']
									);
								
								
								}
								break;
								
								case 7:{
									//take from user add to patient, via icon
									$text = "Patient %s erhält %d %s vom %s";
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
									$info_array['process'] = sprintf( $text,
									
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
									
											$info_array['qty'],
											$info_array['medication'],
											$user_names_array[$row['userid']]['nice_name']
									);
									
								}
								break;
								
								
								case 8:{
									//patient icon
									$info_array['action'] .= $methodsarr[$row['methodid']];
									//$info_array['qty'] = abs($row['amount']);
									
									if ($row['source'] == 'p') {
										//patient takes from personal stock
										$text = "Der Bestand von %s des Patienten %s wurde um %d durch %s von Patient reduziert";
										$info_array['process'] = sprintf( $text,
												$info_array['medication'],
												$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
												$info_array['qty'],
												$info_array['action']
										);
									} 
									else if ($row['source'] == 'u') {
										
										//self_id
										
										if ($row['amount'] > 0) {
											//into stok
											$text = "Der Bestand von %s des Patienten %s um %d erhöht durch %s von %s";
											$info_array['process'] = sprintf( $text,
													$info_array['medication'],
													$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
													$info_array['qty'],
													$info_array['action'],
													$user_names_array[$row['userid']]['nice_name']
											);
											
										} else {
											//out for consumption
											
											$text = "Der Bestand von %s des Patienten %s wurde um %d durch %s reduziert ";
											$text = "Patienten %s %s  %d X %s von %s ";
											
											//$text = "out for consumption";
											
											$info_array['process'] = sprintf( $text,
													
													$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
													
													$info_array['action'],
													
													$info_array['qty'],
													$info_array['medication'],
													
													$user_names_array[$row['userid']]['nice_name']
													
													
											);
										}
										
										
									} else {
											
										$text = "without source ?";
										$text = "Patienten %s %s  %d X %s";
										
										$info_array['process'] = sprintf( $text,
												
												$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
												
												$info_array['action'],
												
												$info_array['qty'],
												$info_array['medication']
											
										);
									}
										
								}
								break;
								
								
								case 9:{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									
									$text = "Patient %s übergibt %d %s an Benutzer %s";
									
									$info_array['process'] = sprintf( $text,
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											$info_array['qty'],
											$info_array['medication'],
											$user_names_array[$row['userid']]['nice_name']											
									);
								}
								break;
								
								
								case 10:{
									//add to patient via Lieferung (Zeratul void)
									$info_array['action'] .= $methodsarr[$row['methodid']];
// 									Abgabe an Patient: Thompson, Mohammed - MOE3002 erhält 20 Tavor Expidet 1 mg TAE vom Schiffer, Sylvia
// 									Schiffer, Sylvia Bestand von Tavor Expidet 1 mg TAE verringert durch 30 Sonstiges
									
// 									$text = "Patient %s erhält %d %s vom %s";
// 									'Gruppe/Tresor Bestand von %s um %d erhöht durch %s %s';
// 									Lieferung: %d %s erhalten durch %s von %s';
									
// 									//$info_array['qty'] = abs($row['amount']);
									//$text = "Patient %s erhält %d %s durch %s von %s';";
									$text = "Patient %s erhält %d %s durch %s";
									
									$info_array['process'] = sprintf( $text,
									
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
									
											$info_array['qty'],
											$info_array['medication'],
											
											$info_array['action'], 
											
											$user_names_array[$row['userid']]['nice_name']
									);
									
								}
								break;
								
								
								case 11:{
									//remove from patient via Sonstiges (Zeratul void)
									$info_array['action'] .= $methodsarr[$row['methodid']];
									
									//include sonstige comment
									if (trim($row['sonstige_more']) != '') {
										$info_array['sonstige_more'] = "[". $row['sonstige_more'] ."]";
									}
									
									$text = "Der Bestand von %s des Patienten %s wurde um %d durch %s  %s  reduziert";
															
									$info_array['process'] = sprintf( $text,
														
											$info_array['medication'],
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											$info_array['qty'],
											$info_array['action'],
											$info_array['sonstige_more']
											);
												
								}
								break;
								
								case 12:{
									
									//get from patinent and give to user -  plus button
									$info_array['action'] .= $methodsarr[$row['methodid']];
									// 									$info_array['qty'] = abs($row['amount']);
								
									if ($row['to_userid'] == $row['userid']) {
										$touser = $row['userid'];
									} else {
										$touser = $row['to_userid'];
									}
										
									$text = "Patient %s übergibt %d %s an Benutzer %s";
									
									$info_array['process'] = sprintf( $text,
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid'],
											
											$info_array['qty'],
											$info_array['medication'],
											
											$user_names_array[ $touser ]['nice_name']

									);
								
								}
								break;
								
							/* 	case 13:{
									//new correction method from ispc 1864
										
									if (empty($correction_new_id[ $row['tb'] ][ $row['id'] ])) {
										$text = "<font color='red'> Inform ADMIN, manual deleted correction event </font>";
										$info_array['process'] = $text;
										break;
									}
										
									$correction_id = $correction_new_id[ $row['tb'] ][ $row['id'] ] ['correction_id'];
										
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$info_array['qty'] = abs($correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount_original'] ) . " -> ". $correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount'];
										
										
									$text = $this->view->translate('btmbuchhistory_lang_correction_event_text');
								
										
									$info_array['process'] = sprintf( $text,
								
											abs($correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount_original'] ),
											$correction_new_id[ $row['tb'] ][ $row['id'] ] ['amount'],
												
											//$user_names_array[$row['create_user']]['nice_name'],
												
											$row4correction[$row['tb']] [ $correction_id ] ['entrydate'],
											$row4correction[$row['tb']] [ $correction_id ] ['process']
									);
								
								
								}
								break; */
								
								default :{
									$info_array['action'] .= $methodsarr[$row['methodid']];
									$text = "<font color='red' style=\"color:#ff0000;\">ALERT ADMIN !</font> " . $row['tb'] . "<br>%s<br>%s";
									$info_array['process'] = sprintf( $text,
											$user_names_array[$row['userid']]['nice_name'],
											$ipid_details_arr[ $row['ipid'] ] ['nice_name_epid']
									);
								}
								
							}
							
						
					}
					
					
					
					//add correction event info
					if ( ! empty( $row['correction_event']) ) 
					{

						$correction_event = $row['correction_event'];
						
						$btmbuchhistory_lang = $this->view->translate('btmbuchhistory_lang');
						$text = $btmbuchhistory_lang['correction_event_text'];
						
						if (trim($correction_event['comment']) != '') {
							$correction_event['comment'] = "<br/>[". nl2br($correction_event['comment']) ."]";
						}
												
						$info_array['process'] .= "<hr>" . sprintf( $text,
									
								abs($correction_event ['amount_original'] ),
								abs($correction_event ['amount']),
									
								$user_names_array[$row['create_user']]['nice_name'],
								
								$correction_event['comment']
								
						);
						//change the amount also
						$info_array['qty'] = "<font color='red' style=\"color:#ff0000;\">" . abs($correction_event ['amount_original'] ) . "->". abs($correction_event ['amount']) . "</font>";
					}
					
			

					
					$resulted_data[] = $info_array;
					$resulted_data_array_id_table [ $row['tb'] ] [ $row['id'] ] = $info_array; //another format

					
				}

// 				print_r($resulted_data_array_id_table);die();
				
				$output_array = array();
				$output_counter = 0;
						
				
				
				foreach ($resulted_data as $key => $row) {
					
					if ( $resulted_data_array_id_table [ $row['table_name'] ] [ $row['row_id'] ] ['row_was_processed'] === true ) {
						continue;
					}
					
					$output_counter++ ;
					
					$conn_1 = $connected_rows_array [$row['row_id']] [$row['table_name']] ;
					$conn_2 =  array();
					$conn_row_1st_level =  array();
					$conn_row_2nd_level =  array();
					$first_array =  array();
					$second_array =  array();
					$third_array =  array();
					
					
					// row is connected to something
					$conn_row_1st_level = $resulted_data_array_id_table [$conn_1['from_table']] [$conn_1['id']];
// 					if ( ! empty($conn_1) && ! empty($conn_row_1st_level = $resulted_data_array_id_table [$conn_1['from_table']] [$conn_1['id']])  ) {
					if ( ! empty($conn_1) && ! empty($conn_row_1st_level )  ) {
						
						$conn_2 = $connected_rows_array [$conn_row_1st_level['row_id']] [$conn_row_1st_level['table_name']] ;
											
						$conn_row_2nd_level = $resulted_data_array_id_table [$conn_2['from_table']] [$conn_2['id']];
						if ( ! empty($conn_2) && ! empty($conn_row_2nd_level)  ) {							
							
							//only verbreauch has now 3 connected
							//write the logic here and not in another usort()  
							
							if ($row['table_name'] == "MedicationClientHistory") {
								
								$first_array = $row; 
								$second_array = $conn_row_1st_level;
								$third_array = $conn_row_2nd_level;
								
							} elseif ($conn_row_1st_level['table_name'] == "MedicationClientHistory") {
								
								$first_array = $conn_row_1st_level;
								$second_array = $row;
								$third_array = $conn_row_2nd_level;
								
							} elseif ($conn_row_2nd_level['table_name'] == "MedicationClientHistory") {
								
								$first_array = $conn_row_2nd_level;
								$second_array = $conn_row_1st_level;
								$third_array = $row;
								
							} 
// 							else {
								
// 								if( $row['table_name'] ==  $conn_row_2nd_level['table_name'] && $row['row_id'] == $conn_row_2nd_level['row_id']){
// 									//we tried to connect back to self, so realy only 2 arrays here
																		
// 									if ($row['amount'] < $conn_row_1st_level['amount'] ) {
// 										$first_array = $row;
// 										$second_array = $conn_row_1st_level;
// 									} else {
// 										$first_array = $conn_row_1st_level;
// 										$second_array = $row;
// 									}
									
// 								}
// 							}
							
							
							if( $row['table_name'] ==  $conn_row_2nd_level['table_name'] && $row['row_id'] == $conn_row_2nd_level['row_id']){
								//we tried to connect back to self, so realy only 2 arrays here
							
								if ($row['amount'] < $conn_row_1st_level['amount'] ) {
									$first_array = $row;
									$second_array = $conn_row_1st_level;
								} else {
									$first_array = $conn_row_1st_level;
									$second_array = $row;
								}
								
								$third_array = array();
									
							}
							
							
							if ($second_array['amount'] < $third_array['amount'] ) {
								$second_array_tmp = $second_array;
								$second_array = $third_array;
								$third_array = $second_array_tmp;
							}
							
							
							if ( ! empty ($second_array)) {
								
								//remove if was processed as single row
								if ($resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed'] === true) {
									$row_was_processed_id = $resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed_id'];
									unset($output_array[$row_was_processed_id]);
								}
								
								$first_array['process'] .= "<hr>" . $second_array['process'] ;
								$resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed'] = true;
								$resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed_id'] = $output_counter;
								
							}
							if ( ! empty ($third_array)) {
								
								//remove if was processed as single row
								if ($resulted_data_array_id_table [$third_array['table_name']] [$third_array['row_id']] ['row_was_processed'] === true) {
									$row_was_processed_id = $resulted_data_array_id_table [$third_array['table_name']] [$third_array['row_id']] ['row_was_processed_id'];
									unset($output_array[$row_was_processed_id]);
								}
								
								$first_array['process'] .= "<hr>" . $third_array['process'] ;
								$resulted_data_array_id_table [$third_array['table_name']] [$third_array['row_id']] ['row_was_processed'] = true;
								$resulted_data_array_id_table [$third_array['table_name']] [$third_array['row_id']] ['row_was_processed_id'] = $output_counter;
							}
							
							//remove if was processed as single row
							if ($resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed'] === true) {
								$row_was_processed_id = $resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed_id'];
								unset($output_array[$row_was_processed_id]);
							}
							$resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed'] = true;
							$resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed_id'] = $output_counter;
							
							
							$output_array[$output_counter] = $first_array;
							

							
						} else {
							//2 interconnected
							// we display first the one that gived

							if ($row['amount'] < $conn_row_1st_level['amount'] ) {
								$first_array = $row;
								$second_array = $conn_row_1st_level;
							} else {
								$first_array = $conn_row_1st_level;
								$second_array = $row;
							}
							
							$first_array['process'] .= "<hr>" . $second_array['process'];
							
							$output_array[$output_counter] = $first_array;
							
							
							//remove if was processed as single row
							if ($resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed'] === true) {
								$row_was_processed_id = $resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed_id']; 
								unset($output_array[$row_was_processed_id]);
							}
							//remove if was processed as single row
							if ($resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed'] === true) {
								$row_was_processed_id = $resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed_id'];
								unset($output_array[$row_was_processed_id]);
							}
							
							
							$resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed'] = true;
							$resulted_data_array_id_table [$first_array['table_name']] [$first_array['row_id']] ['row_was_processed_id'] = $output_counter;
							$resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed'] = true;
							$resulted_data_array_id_table [$second_array['table_name']] [$second_array['row_id']] ['row_was_processed_id'] = $output_counter;
								
						}
						
					} else {
						//nothing interconnected here
						$output_array[$output_counter] = $row;
						
						$resulted_data_array_id_table [$row['table_name']] [$row['row_id']] ['row_was_processed'] = true;
						$resulted_data_array_id_table [$row['table_name']] [$row['row_id']] ['row_was_processed_id'] = $output_counter;
						
					}
					
				}
				
				
				$resulted_data = $output_array;
				$resulted_data = array_values($resulted_data); //datatables wants to start from key=0 .. why?
				

	
				
				if ($print_pdf_with_filters) {
					//print the pdf

					$gridpdf = new Pms_Grid($resulted_data, 2, count($resulted_data), "listbtmverlaufpdf_2017.html");
					
					$pdfgridtable = array();
							
					
					
					$pdfgridtable['filter_by_user'] = $this->view->selectusersarray[$filter_by_user];
					$pdfgridtable['filter_by_year'] = $filter_by_year == 0 ? "Alle jahre" : $this->view->selectyear_array[$filter_by_year];
					
			
					
					$pdfgridtable['selected_btm_array'] = $selected_btm_array;
					$pdfgridtable['medicationsarray'] = $this->view->medicationsarray;
					
					$pdfgridtable['selected_btm_tables'] = $selected_btm_tables;
					
					
					$pdfgridtable['grid'] = $gridpdf->renderGrid();
					
					set_time_limit(600);
					
					$this->generateformPdf(2, $pdfgridtable, "BTMBuchClientHistory", "btmclienthistory_2017.html");
					exit;
					die("finish");
					
				} else {

					
					$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
					$response['recordsTotal'] = $total_counter;//$full_count;
					$response['recordsFiltered'] = $total_counter;//count($resulted_data); // ??
					$response['data'] = $resulted_data;
					
					header("Content-type: application/json; charset=UTF-8");
				
					echo json_encode($response);
					exit;
				}
					
			}
			
			

		
			
		}
		
		public function btmbuchAction()
		{
// 			die(print_r($_REQUEST));
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$this->view->clientid = $clientid;
			$this->view->userid = $userid;

			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patient', $userid, 'canview');

			$btm_perms = new BtmGroupPermissions();
			$btm_permisions = $btm_perms->get_group_permissions($clientid, Usergroup::getMasterGroup($groupid));
			$this->view->lieferung_method = $btm_permisions['method_lieferung'];
			$this->view->btm_permisions = $btm_permisions;
			
			if((!$return || $btm_permisions['use'] != '1') && $logininfo->usertype != 'SA')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			//show the btm seal button only for "BTM Verantwortliche" users
			$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');
			if ( (empty($btm_notification_users) || ! in_array($logininfo->userid, array_column($btm_notification_users, 'user')))
					&&  $logininfo->usertype != 'SA') 
			{
			
				$this->view->btm_notification_users = false;
			
			} else {
				
				$this->view->btm_notification_users = true;
			}
			
			$btmbuch = new MedicationClientHistory();

// 			$methodsarr = array("1" => "Übergabe von", "2" => "Lieferung", "3" => "Sonstiges", "4" => "Übergabe an", "5" => "Abgabe an Patienten", "6" => "Sonstiges", "7" => "Abgabe an Patienten", "8" => "Verbrauch", "9" => "Rücknahme von Patienten", '10' => '', '11' => '', '12' => '');
			$methodsarr = Medication::get_methodsarr();
			
			//new method names for verlauf grid
			foreach($methodsarr as $k_method => $v_method)
			{
				if($this->view->translate('btm_tresor_' . $k_method) != 'btm_tresor_' . $k_method)
				{
					$grid_methods_arr['tresor'][$k_method] = $this->view->translate('btm_tresor_' . $k_method);
				}
				if($this->view->translate('btm_user_' . $k_method) != 'btm_user_' . $k_method)
				{
					$grid_methods_arr['user'][$k_method] = $this->view->translate('btm_user_' . $k_method);
				}

				if($this->view->translate('btm_patient_' . $k_method) != 'btm_patient_' . $k_method)
				{
					$grid_methods_arr['patient'][$k_method] = $this->view->translate('btm_patient_' . $k_method);
				}
			}
			$this->view->grid_methods_arr = $grid_methods_arr;


			if($logininfo->clientid != 0)
			{
				//get only the users with group permission
				
				$btm_groups_permisions = $btm_perms->get_all_groups_permissions($clientid);

				$group = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->where('isdelete =  0')
					->andWhere('clientid = ' . $clientid); // get all client users -> 06.03.2012
				$grouparray = $group->fetchArray();

				$groups_array[] = "999999999999";
				$all_groups[] = "999999999999";
				foreach($grouparray as $group)
				{
					if($btm_groups_permisions[$group['groupmaster']]['use'] == '1')
					{
						$groups_array[] = $group['id'];
					}

					$all_groups[] = $group['id'];
				}

				$users = new User();
				$userarray = $users->getUserByClientid($clientid, '1', true);
				$this->view->userarray = $userarray;

				$groupusrs = Doctrine_Query::create()
					->select('*')
					->from('User')
					->whereIn('groupid', $groups_array)
					->andWhere('isdelete = 0')
					->andWhere('isactive = 0')
					->andWhere('clientid = ' . $clientid . '')
					->orderBy('last_name ASC');
				$groupusers = $groupusrs->fetchArray();

				//prepare users array..
				foreach($groupusers as $user)
				{
				    if($user['isactive'] == "1" || $user['isdelete'] == "1"){
				        $sisactive = "***";
				    } else{
				        $sisactive = "";
				    }
					$doctorusers[$user['id']]['fullname'] = $sisactive.$user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
				}

				$usersarray[] = "99999999";


				if(count($doctorusers) > '1')
				{
					$usersarray = array_merge($usersarray, array_keys($doctorusers));
					unset($usersarray[array_search('99999999', $usersarray)]);
				}

				//get all group users
				$allgroupusrs = Doctrine_Query::create()
					->select('*')
					->from('User')
					->whereIn('groupid', $all_groups)
					->andWhere('isdelete = 0')
					->andWhere('isactive = 0')
					->andWhere('clientid = ' . $clientid . '')
					->orderBy('last_name ASC');
				$all_groupusers = $allgroupusrs->fetchArray();

				//prepare users array..
				foreach($all_groupusers as $user)
				{
				    if($user['isactive'] == "1" || $user['isdelete'] == "1"){
				        $isactive = "***";
				    } else{
				        $isactive = "";
				    }
				    
					$all_users[$user['id']] = $isactive.$user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
				}

				$qpa = Doctrine_Query::create()
					->select('*')
					->from('PatientQpaMapping')
					->whereIn('userid', $usersarray)
					->andWhere('clientid = "' . $logininfo->clientid . '"');
				$doctorPatients = $qpa->fetchArray();

				foreach($doctorPatients as $dPatients)
				{
					$doctorsPatientsEpids[$dPatients['userid']][$dPatients['epid']] = $dPatients['epid'];
					$doctorsEpids[] = $dPatients['epid'];
				}

				if(empty($doctorsEpids))
				{
					$doctorsEpids[] = "99999999";
				}

				$epidipid = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping')
					->andWhere('clientid = "' . $logininfo->clientid . '"');
				$clientDocEpidIpids = $epidipid->fetchArray();

				foreach($clientDocEpidIpids as $patientE)
				{
					$finalPatientsEpids[$patientE['epid']] = $patientE;
					$finalPatientsIpids[] = $patientE['ipid'];
					$finalPatientsIpidsArr[$patientE['ipid']] = $patientE['ipid'];

					//for all patients list
					$patient_epids[$patientE['ipid']] = $patientE;
				}
				if(empty($finalPatientsIpids))
				{
					$finalPatientsIpids[] = "999999999999";
				}
				
				if($this->getRequest()->isPost())
				{
					$patient_medication_form = new Application_Form_Medication();
					$a_post = $_POST;
					if(!empty($a_post['fromuserid']))
					{
						$a_post['user_fullname'][$a_post['fromuserid']] = $doctorusers[$a_post['fromuserid']]['fullname'];
					}
					if(strlen($a_post['add']['oldhidd_medication'][1]) == 0)
					{ //add as new
						
						if(strlen($a_post['add']['medication'][1]) > 0 && strlen($_POST['add']['hidd_medication'][1]) < 1)
						{
							$a_post['newmedication'][0] = $_POST['add']['medication'][1];
						}

						if(is_array($a_post['newmedication']))
						{
							$dts = $patient_medication_form->InsertNewData($a_post);

							foreach($dts as $key => $dt)
							{
								$a_post['newhidd_medication'][$key] = $dt->id;
							}
						}
						if(count($_POST['add']) == 0)
						{
							
							$medclihist = new Application_Form_MedicationClientHistory();
							$medclihist->insertData($a_post);
						}
						else
						{
							$medclihist = new Application_Form_MedicationClientHistory();
							$medclihist->insertNewMedication($a_post);
						}
						
						//post resend
						$this->redirect(APP_BASE . $this->getRequest()->getControllerName(). "/" . $this->getRequest()->getActionName());
						
					}
					else
					{ //edit
						
						if(strlen($a_post['add']['hidd_medication'][1]) == 0)
						{ //edit custom medication
							//check if medi text is  != old medi text and then do:
							if($a_post['add']['oldtext_medication'][1] != $a_post['add']['medication'][1])
							{
								$a_post['newmedication'][0] = $_POST['add']['medication'][1];

								$dts = $patient_medication_form->InsertNewData($a_post);
								foreach($dts as $key => $dt)
								{
									$a_post['newhidd_medication'][$key] = $dt->id;
								}
								//update procedure
								$mcs = new MedicationClientStock();
								$update = $mcs->updateNewMedicationId($a_post['add']['oldhidd_medication'][1], $a_post['newhidd_medication'][0], $clientid);

								$mch = new MedicationClientHistory();
								$update = $mch->updateNewMedicationId($a_post['add']['oldhidd_medication'][1], $a_post['newhidd_medication'][0], $clientid);

								$mph = new MedicationPatientHistory();
								$update = $mph->updateNewMedicationId($a_post['add']['oldhidd_medication'][1], $a_post['newhidd_medication'][0], $clientid);
								
								//post resend
								$this->redirect(APP_BASE . $this->getRequest()->getControllerName(). "/" . $this->getRequest()->getActionName());
							}
						}
						else
						{

							if($a_post['add']['oldhidd_medication'][1] != $a_post['add']['hidd_medication'][1])
							{ //edited with an existing medication master medi
								//update the new hidd_medication where oldhidd_medication in 3 tables
								$a_post['newhidd_medication'][0] = $a_post['add']['hidd_medication'][1];
							}
							//update procedure
							$mcs = new MedicationClientStock();
							$update = $mcs->updateNewMedicationId($a_post['add']['oldhidd_medication'][1], $a_post['newhidd_medication'][0], $clientid);

							$mch = new MedicationClientHistory();
							$update = $mch->updateNewMedicationId($a_post['add']['oldhidd_medication'][1], $a_post['newhidd_medication'][0], $clientid);

							$mph = new MedicationPatientHistory();
							$update = $mph->updateNewMedicationId($a_post['add']['oldhidd_medication'][1], $a_post['newhidd_medication'][0], $clientid);
							
							//post resend
							$this->redirect(APP_BASE . $this->getRequest()->getControllerName(). "/" . $this->getRequest()->getActionName());
						}
					}
				}

				//get patients which got medis from this client user
				$users_patients_arr = $btmbuch->get_users_patients($clientid, $usersarray);

				//users patients data
				$user_patient_details = Doctrine_Query::create()
					->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
					->from('PatientMaster p')
					->whereIn('p.ipid', $users_patients_arr['ipids'])
					->andWhere('p.isdelete = "0"')
					->andWhere('p.isstandbydelete = "0"');
				$user_patient_details_arr = $user_patient_details->fetchArray();

// 				if($_REQUEST['dbgq'])
// 				{
// 					print_r($users_patients_arr);
// 					print_r($user_patient_details->getSqlQuery() . "\n\n");
// 					print_r($user_patient_details_arr);

// 					exit;
// 				}

				foreach($user_patient_details_arr as $k_doc_pat => $v_doc_pat)
				{
					$patients_details[$v_doc_pat['ipid']] = $v_doc_pat;
					$patients_details[$v_doc_pat['ipid']]['epid'] = $patient_epids[$v_doc_pat['ipid']]['epid'];
				}

				//get patients stocks
				$pat_client_history = new MedicationPatientHistory();
				$patients_stocks = $pat_client_history->get_patients_stocks($clientid);

				$this->view->users_patients_arr = $users_patients_arr;
				$this->view->patients_details = $patients_details;
				$this->view->patients_stock = $patients_stocks;

				$patientsDetails = Doctrine_Query::create()
					->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
					->from('PatientMaster p')
					->whereIn('p.ipid', $finalPatientsIpids)
					->andWhere('p.isdelete = "0"')
					->andWhere('p.isdischarged = "0"')
					->andWhere('p.isstandby = "0"')
					->andWhere('p.isstandbydelete = "0"');
				$patientDetailsArr = $patientsDetails->fetchArray();

				foreach($patientDetailsArr as $patientDetail)
				{
					$finalPatientDetails[$patient_epids[$patientDetail['ipid']]['epid']] = $patientDetail;
				}

				if($_REQUEST['dbg'])
				{
					print_r($finalPatientDetails);
					exit;
				}
				ksort($finalPatientDetails);

				//	changed to get all patients not only the assigned... 30.04.2012 radu
				foreach($finalPatientDetails as $curentPatient)
				{
					$finalDoctorsPatients[0][0] = "Patienten wählen";
					$finalDoctorsPatients[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
				}

				$this->view->patientsDoctorSelect = $finalDoctorsPatients; //changed to get all patients not only the assigned... 30.04.2012 radu

				$patientsDetailsHistory = Doctrine_Query::create()
					->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
					->from('PatientMaster p')
					->whereIn('p.ipid', $finalPatientsIpids);
				$patientDetailsHistoryArr = $patientsDetailsHistory->fetchArray();

				$finalDoctorsPatients = array('');
				foreach($patientDetailsHistoryArr as $curentPatientH)
				{
					$finalDoctorsPatientsHistory[0][0] = "Patienten wählen";
					$finalDoctorsPatientsHistory[0][$curentPatientH['ipid']] = $curentPatientH['lname'] . ", " . $curentPatientH['fname'] . " - " . $patient_epids[$curentPatientH['ipid']]['epid'];
				}

				$this->view->patientsDoctorSelectHistory = $finalDoctorsPatientsHistory;
				//get stocks
				$stocks = new MedicationClientStock();
				$stksarray = $stocks->getAllMedicationClientStock($clientid);

				//prepare medis stocks array to get medis data by medis stocks id
				$medisstr = "'99999999999'";
				$comma = ",";
				foreach($stksarray as $stocmedis)
				{
					$stocmedications[$stocmedis['medicationid']] = $stocmedis;
					$medisstr .= $comma . "'" . $stocmedis['medicationid'] . "'";
					$comma = ",";
				}

				//get medication stocks medis id
				$med = Doctrine_Query::create()
					->select('*')
					->from('Medication')
					->where('isdelete = 0 ')
					->andWhere('name!=""')
					->andWhere('id IN (' . $medisstr . ')')
					->andWhere('clientid = ' . $clientid . '');
				$medarray = $med->fetchArray();

				foreach($medarray as $medication)
				{
					if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
					{
						$medicationsarray[$medication['id']] = $medication;
						$medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
					}
				}

				$btm = $btmbuch->getDataForUsers($clientid, $usersarray);
				foreach($btm as $record)
				{
					$btmuserdata[$record['userid']][$record['medicationid']] = $record;
				}

				$all_users[0] = "Gruppe / Tresor";
				$doctorsSelect[0] = "Gruppe / Tresor";
//				OLD BTM PERMS
//				if($can_add == '1')
//				{
//					$doctorsSelect_iadd[0] = "Gruppe / Tresor";
//				}
//				if($can_delete == '1')
//				{
//					$doctorsSelect_idel[0] = "Gruppe / Tresor";
//				}

				$doctorsSelect_iadd[0] = "Gruppe / Tresor";
				$doctorsSelect_idel[0] = "Gruppe / Tresor";

				foreach($doctorusers as $dockey => $docuser)
				{
					$doctors[$dockey]['fullname'] = $docuser['fullname'];
					$doctorsSelect[$dockey] = $docuser['fullname'];

					$doctorsSelect_iadd[$dockey] = $docuser['fullname'];
					$doctorsSelect_idel[$dockey] = $docuser['fullname'];
				}

				foreach($medicationsarray as $keym => $medication)
				{
					$final[$keym]['id'] = $medication['id'];
					$final[$keym]['name'] = $medication['name'];
					$final[$keym]['stock'] = $medication['amount'];

					foreach($usersarray as $keyu => $userid)
					{
						//exclude dummy control
						if($userid != '99999999')
						{
							if($userid == $btmuserdata[$userid][$keym]['userid'] && $medication['id'] == $btmuserdata[$userid][$keym]['medicationid'])
							{
								$final_userdata['user'][$keym][$userid] = $btmuserdata[$userid][$keym]['total'];
							}
							else
							{
								$final_userdata['user'][$keym][$userid] = 0;
							}
						}
					}
					$medicationsarray[$keym]['users'] = $final_userdata['user'];
				}
// 				print_r($medicationsarray);exit;

				//ispc-1856
				//order by medication name ASC
				function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
					$sort_col = array();
					foreach ($arr as $key=> $row) {
						$sort_col[$key] = $row[$col];
					}
				
					array_multisort($sort_col, $dir, $arr);
				}
				array_sort_by_column($medicationsarray, 'name');
				
				foreach($medicationsarray as $k=>$mik){
					$medicationsarray_tmp[$mik['id']] = $mik;
				}
				
				
				$medicationsarray = $medicationsarray_tmp;
				
				$grid = new Pms_Grid($medicationsarray, 1, count($medicationsarray), "listbtmdoctors.html");
				$this->view->doctorusers = $doctors;

				$this->view->filteron = $_POST['filteron'];
				$this->view->btmgrid = $grid->renderGrid();
				$this->view->doctorsSelect = $doctorsSelect;

				//used in verlauf grid...we need all client users
				$this->view->all_users = $all_users;

				$this->view->doctorsSelect_iadd = $doctorsSelect_iadd;
				$this->view->doctorsSelect_idel = $doctorsSelect_idel;

// 				if (  1  ||  ($_POST['genpdfval'] == 1 && isset($_POST['btm_history'])) ) 
				if ( $_POST['genpdfval'] == 1  ) 
				{

					//medis history like verlauf...
					if(count($_POST['filteron']) > 0)
					{
						$filterstr = "'99999999999'";
						$comma = ",";
						$medications_arr[]  = "99999999999";
						foreach($_POST['filteron'] as $filtervalue)
						{
							$filteroninputspdf .= '<input type="hidden" name="filteron[]" value="' . $filtervalue . '" />';
							$filterstr .= $comma . "'" . $filtervalue . "'";
							$comma = ",";
							$medications_arr[] = $filtervalue;
						}
	
						//get selected medis only
						$hist = new MedicationClientHistory();
						$clientHistoryData = $hist->getVerlaufMedicationsClientHistory($clientid, $filterstr);
	
						$histStock = new MedicationClientStock();
						$clientStockData = $histStock->getVerlaufMedicationsClientStock($clientid, $filterstr);
					}
					else
					{
						$hist = new MedicationClientHistory();
						$clientHistoryData = $hist->getVerlaufMedicationClientHistory($clientid);
	
						$histStock = new MedicationClientStock();
						$clientStockData = $histStock->getVerlaufMedicationClientStock($clientid);
					}
	//				print_r($clientStockData);
	//				exit;
					$x = 1;
					foreach($clientStockData as $dataClientStock)
					{
						$grouped_key = date("dmYHi", strtotime($dataClientStock['create_date'])) . '-' . $dataClientStock['create_user'];
						if($dataClientStock['amount'] == 0 && $dataClientStock['userid'] == 0 && $dataClientStock['methodid'] == 0)
						{
							$clientHistoryArray[$grouped_key]['sumary'][$x] = $dataClientStock;
							$clientHistoryArray[$grouped_key]['sumary'][$x]['type'] = "N"; //new
							$clientHistoryArray[$grouped_key]['date'] = date("d.m.Y H:i", strtotime($dataClientStock['create_date']));
							$clientHistoryArray[$grouped_key]['creator_full_name'] = $userarray[$dataClientStock['create_user']];
						}
						elseif($dataClientStock['amount'] != 0 && ($dataClientStock['methodid'] != 0 && $dataClientStock['methodid'] != 1 && $dataClientStock['methodid'] != 4 && $dataClientStock['methodid'] != 5))
						{
							$clientHistoryArray[$grouped_key]['sumary'][$x] = $dataClientStock;
							$clientHistoryArray[$grouped_key]['date'] = date("d.m.Y H:i", strtotime($dataClientStock['create_date']));
							$clientHistoryArray[$grouped_key]['creator_full_name'] = $userarray[$dataClientStock['create_user']];
						}
						else
						{
							// TO DO case 5 transfer to patient
							$clientHistoryArray[$grouped_key]['sumary'][$x] = $dataClientStock;
							foreach($clientHistoryData as $clientData)
							{
								if($clientData['stid'] == $dataClientStock['id'])
								{
									$clientHistoryArray[$grouped_key]['sumary'][$x]['ipid'] = $clientData['ipid'];
								}
							}
							$clientHistoryArray[$grouped_key]['date'] = date("d.m.Y H:i", strtotime($dataClientStock['create_date']));
							$clientHistoryArray[$grouped_key]['creator_full_name'] = $userarray[$dataClientStock['create_user']];
						}
						$clientStockArray[$dataClientStock['id']] = $dataClientStock;
						$x++;
					}
	
					foreach($clientHistoryData as $dataClientHistory)
					{
						$grouped_key = date("dmYHi", strtotime($dataClientHistory['create_date'])) . '-' . $dataClientHistory['create_user'];
	
						$clientHistoryArray[$grouped_key]['sumary'][$dataClientHistory['id']] = $dataClientHistory;
						if($dataClientHistory['methodid'] < 4)
						{
							if($dataClientHistory['amount'] < 0)
							{
								$clientHistoryArray[$grouped_key]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
							}
							else if($dataClientHistory['amount'] > 0 && $dataClientHistory['stid'] > 0)
							{
								$clientHistoryArray[$grouped_key]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
							}
						}
						else
						{
							if($dataClientHistory['amount'] > 0)
							{
								$clientHistoryArray[$grouped_key]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
							}
							else if($dataClientHistory['amount'] < 0 && $dataClientHistory['stid'] > 0)
							{
								$clientHistoryArray[$grouped_key]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
							}
						}
						$clientHistoryArray[$grouped_key]['date'] = date("d.m.Y H:i", strtotime($dataClientHistory['create_date']));
						$clientHistoryArray[$grouped_key]['creator_full_name'] = $userarray[$dataClientHistory['create_user']];
					}
					
	
					//get patient medi history with method 8 only and source patient!
					$methods = array('8');
					$patient_usage_history = MedicationPatientHistory::getVerlaufMedicationPatientHistory($clientid, $finalPatientsIpids, $medications_arr, $methods);

					foreach($patient_usage_history as $k_puh => $v_puh)
					{
						if($v_puh['source'] == 'p')
						{
							$grouped_key = date("dmYHi", strtotime($v_puh['create_date'])) . '-' . $v_puh['create_user'];

							$clientHistoryArray[$grouped_key]['sumary'][] = $v_puh;
							$clientHistoryArray[$grouped_key]['date'] = date("d.m.Y H:i", strtotime($v_puh['create_date']));
							$clientHistoryArray[$grouped_key]['creator_full_name'] = $userarray[$v_puh['create_user']];
						}
					}
	//				print_r($patient_usage_history);
	//				print_r($clientHistoryArray);
	//				exit;
					if($_REQUEST['dbgqq'])
					{
						print_r("clientHistoryData\n");
						print_r($clientHistoryData);
						print_r("clientStockData\n");
						print_r($clientStockData);
	
						print_r("clientHistoryArray\n");
						print_r($clientHistoryArray);
						exit;
					}
					$clientHistoryArray = $this->array_sort($clientHistoryArray, "date", SORT_ASC);
					$gridv = new Pms_Grid($clientHistoryArray, 1, count($clientHistoryArray), "listbtmverlauf.html");
					$gridpdf = new Pms_Grid($clientHistoryArray, 1, count($clientHistoryArray), "listbtmverlaufpdf.html");
	
	
					$this->view->medicationarray = $medicationsarray;
					$this->view->methodsarray = $methodsarr;
					$this->view->stocksarray = $clientStockArray;
	
					$this->view->filteronpdfhiddeninputs = $filteroninputspdf;
					$this->view->btmverlaufgrid = $gridv->renderGrid();
					$pdfgridtable['grid'] = $gridpdf->renderGrid();

				}//end history
				
				if($this->getRequest()->isPost())
				{
					if($_POST['method'] == 5)
					{
						if(count($_POST['patientselect']) > 0)
						{
							foreach($_POST['patientselect'] as $keyp => $patientsel)
							{
								if($patientsel != "0")
								{
									$patientSelect = $_POST['patientselect'][$keyp];
								}
							}
						}
						//add to patient verlauf entry with supplied amount in case of transfer to patient
						if(empty($_POST['fromuserid']))
						{
							$usrid = 0;
						}
						else
						{
							$usrid = $_POST['fromuserid'];
						}

						$medicationname = $medicationsarray[$_POST['medicationid']]['name'];
// 						$comment = "Patient erhält " . $_POST['amount'] . "     \"" . $medicationname . "\"  von " . $doctorsSelect[$usrid];
						$comment = "Der Bestand von ".$medicationname." des Patienten wurde durch Übergabe von ".$doctorsSelect[$usrid]." um ".$_POST['amount']." erhöht.";
						/* $cust = new PatientCourse();
						$cust->ipid = $patientSelect;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->user_id = $userid;
						$cust->save(); */
					}

					if($_POST['genpdfval'])
					{
						$this->generateformPdf(2, $pdfgridtable, "BTMBuchClientHistory", "btmclienthistory.html");
					}
				}
			}
		}

		public function btmformmedicationAction()
		{
		    set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patient', $logininfo->userid, 'canview');
			$methodsarr = array("1" => "Übergabe von", "2" => "Lieferung", "3" => "Sonstiges", "4" => "Übergabe an", "5" => "Abgabe an Patienten", "6" => "Sonstiges", "7" => "Abgabe an Patienten", "8" => "Verbrauch", "9" => "Rücknahme von Patienten", '10' => '', '11' => '', '12' => '');


			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$medid = $_GET['medid'];

			

			//construct months selector array START
			$start_period = '2011-12-01';
			$end_period = date('Y-m-d', time());
			$period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
			
			$month_select_array["overall"] = "Gesamt"; 
			foreach($period_months_array as $k_month => $v_month)
			{
			    $month_select_array[$v_month] = $v_month;
			}
			//construct months selector array END
			//check if a month is selected START
			if(strlen($_REQUEST['list']) == '0')
			{
// 			    $selected_month = end($month_select_array);
			    $selected_month = "overall";
			    $_REQUEST['list'] = "overall";
			}
			else
			{
			    $selected_month = $month_select_array[$_REQUEST['list']];
			}
			
			
			//ispc-1856
			if ( $selected_month == "overall" || $selected_month == "Gesamt" ){
				$selected_month_till = "overall";
			}
			elseif($_REQUEST['list_till'] == 'overall' || $_REQUEST['list_till'] == '0')
			{
				// 			    $selected_month = end($month_select_array);
				$selected_month_till = "overall";
			}
			else
			{
				$selected_month_till = $month_select_array[$_REQUEST['list_till']];
				if (strtotime($selected_month_till) < strtotime($selected_month)){
					$selected_month_till = $selected_month;
				}
			}

			$this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));

			if($_REQUEST['list'] == "overall"){
    			 $months_details[$selected_month]['start'] = $start_period;
	   		     $months_details[$selected_month]['end'] = $end_period;
	   		     
	   		     $months_details_till = false;
	   		     
			} else{
			    if(!function_exists('cal_days_in_month'))
			    {
			        $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
			        if ( $selected_month_till != "overall"){
			        	$month_days_till = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month_till . "-01")), 1, date("Y", strtotime($selected_month_till . "-01"))));
			        }
			    }
			    else
			    {
			        $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
			        if ( $selected_month_till != "overall"){
			        	$month_days_till = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month_till . "-01")), date("Y", strtotime($selected_month_till . "-01")));
			        }
			    }
			    
    			 $months_details[$selected_month]['start'] = $selected_month . "-01";
	   		     $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
	   		     
	   		     
	   		     $months_details_till = $selected_month_till . '-' . $month_days_till;
	   		   
			    
			}
			//check if a month is selected END
			
			//construct month_selector START
			$attrs['onChange'] = 'changeMonth("#month_from","#month_till");';
			$attrs['class'] = 'select_month_rehnung_patients';
			$attrs['id'] = "month_from";
			$this->view->months_selector_from = $this->view->formSelect("select_month", $selected_month, $attrs, $month_select_array);
			
			//till month
			$month_select_array["overall"] = "";
			$attrs['id'] = "month_till";
			$this->view->months_selector_till = $this->view->formSelect("select_month", $selected_month_till, $attrs, $month_select_array);
			
			
			//construct month_selector END
			
			$requested_period['start'] = $months_details[$selected_month]['start']; 
			$requested_period['end'] =  $months_details[$selected_month]['end']; 
			$requested_period['till'] = $months_details_till;
			
			$stocks = new MedicationClientStock();
			$stksarray = $stocks->getAllMedicationClientStock($clientid);
// 			print_r($stksarray); exit;
// 			getAllMedicationClientStock_in_period

			//prepare medis stocks array to get medis data by medis stocks id
			$medisstr = "'99999999999'";
			$comma = ",";
			foreach($stksarray as $stocmedis)
			{
				$stocmedications[$stocmedis['medicationid']] = $stocmedis;
				$medisstr .= $comma . "'" . $stocmedis['medicationid'] . "'";
				$comma = ",";
			}

			//get medication stocks medis id
			$med = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('isdelete = 0 ')
				->andWhere('name!=""')
				->andWhere('id=' . $medid)
				->andWhere('clientid = ' . $clientid . '');
			$medarray = $med->fetchArray();


			foreach($medarray as $medication)
			{
				if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
				{
					$medicationsarray[$medication['id']] = $medication;
					$medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
				}
				
				$medisarray[$medication['id']]['name'] = $medication['name']; 
			}
			$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('isdelete =  0')
				->andWhere('clientid = ' . $clientid); // get all client users -> 06.03.2012
			$grouparray = $group->fetchArray();

			$groupstr = "'999999999999'";
			$comma = ",";
			foreach($grouparray as $group)
			{
				$groupstr .= $comma . "'" . $group['id'] . "'";
				$comma = ",";
			}
			$groupusrs = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('groupid IN (' . $groupstr . ')')
// 				->andWhere('isdelete = 0')
//  				->andWhere('isactive = 0')
				->andWhere('clientid = ' . $clientid . '')
				->orderBy('last_name ASC');
			$groupusers = $groupusrs->fetchArray();

			//prepare users array..
			foreach($groupusers as $user)
			{

				$usersarray[$user['id']] = $user['id'];
				if($user['isactive'] == "1" || $user['isdelete'] == "1"){
//     				$doctorusers[$user['id']]['fullname'] = "<i>".$user['user_title'] . " " . $user['first_name'] . ", " . $user['last_name']." *</i>";
    				$doctorusers[$user['id']]['fullname'] = "<i>Sonstiges *</i>";
				} else{
    				$doctorusers[$user['id']]['fullname'] = $user['user_title'] . " " . $user['first_name'] . ", " . $user['last_name'];
				}
				
			}
			$usersarray[] = "99999999";
			$doctorsSelect[0] = "Gruppe / Tresor";
			foreach($doctorusers as $dockey => $docuser)
			{
				$doctors[$dockey]['fullname'] = $docuser['fullname'];
   				$doctorsSelect[$dockey] = $docuser['fullname'];
			}
			$this->view->doctorsSelect = $doctorsSelect;


			$qpa = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
				->whereIn('userid', $usersarray)
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$doctorPatients = $qpa->fetchArray();
			foreach($doctorPatients as $dPatients)
			{
				$doctorsPatientsEpids[$dPatients['userid']][$dPatients['epid']] = $dPatients['epid'];
				$doctorsEpids[] = $dPatients['epid'];
			}
			$doctorsEpids[] = "9999999999";


			$epidipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$clientDocEpidIpids = $epidipid->fetchArray();


			foreach($clientDocEpidIpids as $patientE)
			{
				$finalPatientsEpids[$patientE['epid']] = $patientE;
				$finalPatientsIpids[] = $patientE['ipid'];
				$finalPatientsIpidsArr[$patientE['ipid']] = $patientE['ipid'];

				//for all patients list
				$patient_epids[$patientE['ipid']] = $patientE;
			}
			if(empty($finalPatientsIpids))
			{
				$finalPatientsIpids[] = "999999999999";
			}

			$patientsDetails = Doctrine_Query::create()
				->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
				->from('PatientMaster p')
				->whereIn('p.ipid', $finalPatientsIpids)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isdischarged = "0"')
				->andWhere('p.isstandby = "0"')
				->andWhere('p.isstandbydelete = "0"');
			$patientDetailsArr = $patientsDetails->fetchArray();


			foreach($patientDetailsArr as $patientDetail)
			{
				$finalPatientDetails[$patient_epids[$patientDetail['ipid']]['epid']] = $patientDetail;
			}
			ksort($finalPatientDetails);
			//		changed to get all patients not only the assigned... 30.04.2012 radu
			foreach($finalPatientDetails as $curentPatient)
			{
				$finalDoctorsPatients[0][0] = "Patienten wählen";
				$finalDoctorsPatients[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
			}
			$this->view->patientsDoctorSelect = $finalDoctorsPatients; //changed to get all patients not only the assigned... 30.04.2012 radu

//             $method_ids = array("3","5","6","7","8","9","12");			
            $method_ids = false;			
			$hist = new MedicationClientHistory();
// 			$clientHistoryData = $hist->getVerlaufMedicationsClientHistory($clientid, $medid,$method_ids);
			$clientHistoryData = $hist->getVerlaufMedicationsClientHistory_original($clientid, $medid,$method_ids);
			
	 
			//ispc 1864 - remove methodid 13 and add correction with red
			foreach($clientHistoryData as $k => &$row) {
				if ( $row['methodid'] == 13) {
					unset($clientHistoryData[$k]);
				} else {
					$row['table_name'] = 'MedicationClientHistory';
				}
			}
			
			
			$histStock = new MedicationClientStock();
// 			$clientStockData = $histStock->getVerlaufMedicationsClientStock($clientid, $medid,$method_ids);
			$clientStockData = $histStock->getVerlaufMedicationsClientStock_original($clientid, $medid,$method_ids);
			
			//ispc 1864 - remove methodid 13 and add correction with red
			foreach($clientStockData as $k => &$row) {
				if ( $row['methodid'] == 13) {
					unset($clientStockData[$k]);
				} else {
					$row['table_name'] = 'MedicationClientStock';
				}
			}
			
			
			$x = 1;
			foreach($clientStockData as $dataClientStock)
			{
			    if($dataClientStock['done_date'] != "0000-00-00 00:00:00"){
			        
			        if(date("s",strtotime($dataClientStock['done_date'])) == "00"){
			            
			            $dsec = date("s",strtotime($dataClientStock['done_date']));
			            $csec = date("s",strtotime($dataClientStock['create_date']));
			            $dmin = date("i",strtotime($dataClientStock['done_date']));
			            $dhour = date("H",strtotime($dataClientStock['done_date']));
			            $dmonth = date("n",strtotime($dataClientStock['done_date']));
			            $dday = date("j",strtotime($dataClientStock['done_date']));
			            $dyear = date("Y",strtotime($dataClientStock['done_date']));
    			        $dataClientStock['done_date'] = date("Y-m-d H:i:s",mktime($dhour,$dmin,$csec,$dmonth,$dday,$dyear));
			        } 
			        
			        $dataClientStock['create_date'] = $dataClientStock['done_date'];
			         
			    } else {
			        $dataClientStock['create_date'] = $dataClientStock['create_date']; 
			    }
			    
			    
				if($dataClientStock['amount'] == 0 && $dataClientStock['userid'] == 0 && $dataClientStock['methodid'] == 0)
				{
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x]['type'] = "N"; //new
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
				}
				elseif($dataClientStock['amount'] != 0 && ($dataClientStock['methodid'] != 0 && $dataClientStock['methodid'] != 1 && $dataClientStock['methodid'] != 4 && $dataClientStock['methodid'] != 5))
				{
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
// 					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientStock['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientStock['create_date']);
				}
				else
				{
					// TO DO case 5 transfer to patient
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					foreach($clientHistoryData as $clientData)
					{
						if($clientData['stid'] == $dataClientStock['id'])
						{
							$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x]['ipid'] = $clientData['ipid'];
						}
					}
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
// 					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientStock['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientStock['create_date']);
				}
				$clientStockArray[$dataClientStock['id']] = $dataClientStock;
				$x++;
			}

			foreach($clientHistoryData as $dataClientHistory)
			{
			    
			    if($dataClientHistory['done_date'] != "0000-00-00 00:00:00"){
			    
			        if(date("s",strtotime($dataClientHistory['done_date'])) == "00"){
			    
			            $dsec = date("s",strtotime($dataClientHistory['done_date']));
			            $csec = date("s",strtotime($dataClientHistory['create_date']));
			            $dmin = date("i",strtotime($dataClientHistory['done_date']));
			            $dhour = date("H",strtotime($dataClientHistory['done_date']));
			            $dmonth = date("n",strtotime($dataClientHistory['done_date']));
			            $dday = date("j",strtotime($dataClientHistory['done_date']));
			            $dyear = date("Y",strtotime($dataClientHistory['done_date']));
			            $dataClientHistoryx['done_date'] = date("Y-m-d H:i:s",mktime($dhour,$dmin,$csec,$dmonth,$dday,$dyear));
			        } else{
			            $dataClientHistoryx['done_date'] = $dataClientHistory['done_date'];;
			        }
			    
			        $dataClientHistoryx['create_date'] = $dataClientHistoryx['done_date'];
			        	
			    } else {
			        $dataClientHistoryx['create_date'] = $dataClientHistory['create_date'];
			    }
			    
			    $dataClientHistory['create_date'] = $dataClientHistoryx['create_date'];
			    
			    
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']] = $dataClientHistory;
				if($dataClientHistory['methodid'] < 4)
				{
					if($dataClientHistory['amount'] < 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
// 						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
// 						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
					else if($dataClientHistory['amount'] > 0 && $dataClientHistory['stid'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
// 						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
				}
				else
				{
					if($dataClientHistory['amount'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
// 						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
					else if($dataClientHistory['amount'] < 0 && $dataClientHistory['stid'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
// 						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
				}
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientHistory['create_date']));
// 				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']) + $hist->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
			}

			$clientHistoryArray = $this->array_sort($clientHistoryArray, "date", SORT_ASC);
			

			/* ----------------------------------------------------------------------------------------------------------- */
			//get methods
			$methodsarray = $methodsarr;
			//get stock values
			$stocksarray = $clientStockArray;
			//get doctors array
			$doctorsarray = $doctorsSelect;
			//get patinets array
			$patientsDetailsHistory = Doctrine_Query::create()
				->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
				->from('PatientMaster p')
				->whereIn('p.ipid', $finalPatientsIpids);
			$patientDetailsHistoryArr = $patientsDetailsHistory->fetchArray();

			$patientsSelector = array();
			foreach($patientDetailsHistoryArr as $curentPatient)
			{
				$patientsSelector[0][0] = "Patienten wählen";
				$patientsSelector[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
			}
            if($_REQUEST['ch'] == "1"){
            	echo "<pre>xxxxxxxxxxxxxxx";
            	echo count($clientHistoryArray);
			 print_r($clientHistoryArray); 
			 exit;
            }
			$block = array();
			$k= 0;
			foreach($clientHistoryArray as $key => $val)
			{
			    $r1start = strtotime(date("Y-m-d",strtotime($val['date'])));
			    $r1end = strtotime(date("Y-m-d",strtotime($val['date'])));
			    
			    if (($requested_period['till'] !== false && $r1start >= strtotime($requested_period['start']) && $r1start <= strtotime($requested_period['till']))
			    	|| (Pms_CommonData::isintersected($r1start, $r1end, strtotime($requested_period['start']), strtotime($requested_period['end']))))
    			{
    				$needed[$key]['sumary'] = $val['sumary'];
    				$needed[$key]['date'] = $val['date'];
    				$needed[$key]['totaltr'] = $val['totaltr'];
    				$totaltr = $needed[$key]['totaltr'];
    
    				foreach($needed[$key]['sumary'] as $clienthistoryid => $ch)
    				{
    					if(!in_array($ch['id'], $skippedid))
    					{
    						
    						if ( ! empty( $needed[$key]['sumary'][($ch['id'])] ['id'])) {
    							$block[$needed[$key]['date']]['row_id'] = $needed[$key]['sumary'][($ch['id'])] ['id'];
    							$block[$needed[$key]['date']]['table_name'] = $needed[$key]['sumary'][($ch['id'])] ['table_name'];
    						}
    					    
//     					    $needed[$key]['date'] = $needed[$key]['date'].' -> '.$k;
    						switch($ch['methodid'])
    						{
    							//PLUS OPERATION
    							case "1":
    								if($ch['type'] == "C")
    								{ //Übergabe von
    									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
    									if($ch['amount'] < 0)
    									{
    										$ch['amount'] = ($ch['amount'] * (-1));
    									}
    									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];
    
    									if($ch['stid'] == 0)
    									{
    
    										if($ch['userid'] != 0)
    										{
    											$block[$needed[$key]['date']]['extra'] = "hide";
    										}
    										$block[$needed[$key]['date']]['case'] = "case 1 c1";
    										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    										$block[$needed[$key]['date']]['give'] =  $doctorsarray[$ch['userid']];
    										$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$next_user];
    										$block[$needed[$key]['date']]['add'] = $ch['amount'];
    										$block[$needed[$key]['date']]['subs'] = "";
    										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" .$doctorsarray[$ch['userid']] . '<b/> transferat prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . 'la userul' . $doctorsarray[$next_user];
    										$block[$needed[$key]['date']]['sonstige_more'] = '';
    										$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    										
    									}
    									else
    									{
    										$block[$needed[$key]['date']]['case'] = "case 1 c11";
    										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    										$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
    										$block[$needed[$key]['date']]['recieve'] = $doctorsarray['0'];
    										$block[$needed[$key]['date']]['add'] = $ch['amount'];
    										$block[$needed[$key]['date']]['subs'] = "";
    										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" .$doctorsarray[$ch['userid']] . '</b> transferat prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . ' la userul ' . $doctorsarray['0'];
    										$block[$needed[$key]['date']]['sonstige_more'] = '';
    										$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    										
    									}
    								}
    								if($ch['type'] == "CS")
    								{
    									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
    									if($ch['amount'] < 0)
    									{
    										$ch['amount'] = ($ch['amount'] * (-1));
    									}
    									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];
    
    									$block[$needed[$key]['date']]['case'] = "case 1 cs";
    									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    									$block[$needed[$key]['date']]['give'] = $doctorsarray['0'];
    									$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
    									$block[$needed[$key]['date']]['add'] = "";
    									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    									$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    									$block[$needed[$key]['date']]['details'] = 'Group a transferat cu + prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . ' la userul ' . $doctorsarray[$ch['userid']];
    									$block[$needed[$key]['date']]['sonstige_more'] = '';
    									$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    									
    								}
    								break;
    							case "2": //liferung
    								if($ch['amount'] < 0)
    								{
    									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
    									$ch['amount'] = ($ch['amount'] * (-1));
    								}
    								else
    								{
    									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
    								}
    
    								if($ch['userid'] != 0)
    								{
    									$block[$needed[$key]['date']]['extra'] = "hide";
    								}
    
    								$block[$needed[$key]['date']]['case'] = "liferung case 2";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['give'] = "Lieferung";
    								$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
    								$block[$needed[$key]['date']]['subs'] = "";
    								$block[$needed[$key]['date']]['add'] = $ch['amount'];
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = '';
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    								break;
    
    
    							case "3": //sonstige
    								if($ch['amount'] < 0)
    								{
    									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
    									$ch['amount'] = ($ch['amount'] * (-1));
    									$block[$needed[$key]['date']]['give'] = "";
    									$block[$needed[$key]['date']]['recieve'] = "";
    									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    									$block[$needed[$key]['date']]['add'] = "";
    									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    									
    								}
    								else
    								{
    									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
    									$block[$needed[$key]['date']]['give'] = "Sonstiges";
    									$block[$needed[$key]['date']]['recieve'] = $doctorsarray['0'];
    									$block[$needed[$key]['date']]['subs'] = "";
    									$block[$needed[$key]['date']]['add'] = $ch['amount'];
    									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    									
    								}
    
    								if($ch['userid'] != 0)
    								{
    									$block[$needed[$key]['date']]['extra'] = "hide";
    								}
    								$block[$needed[$key]['date']]['case'] = "sonstige case 3";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" .$doctorsarray[$ch['userid']] . "</b> " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = $ch['sonstige_more'];
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    								break;
    
    							//MINUS OPERATION
    							case "4":
    								if($ch['type'] == "C")
    								{ //Übergabe von
    									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
    									if($ch['amount'] < 0)
    									{
    										$ch['amount'] = ($ch['amount'] * (-1));
    									}
    									$direction = "übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an";
    									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];
    
    
    
    									if($ch['stid'] == 0)
    									{//display transfer from user to user
    										if($ch['userid'] != 0)
    										{
    											$block[$needed[$key]['date']]['extra'] = "hide";
    										}
    										$block[$needed[$key]['date']]['case'] = "case 4 c1";
    										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    										$block[$needed[$key]['date']]['give'] = $doctorsarray[$next_user];
    										$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
    										$block[$needed[$key]['date']]['add'] = "";
    										$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": " . $doctorsarray[$next_user] . " " . $direction . " " . $doctorsarray[$ch['userid']] . "<br />";
    										$block[$needed[$key]['date']]['sonstige_more'] = 'transfer from user to user';
    										$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    										
    									}
    									else
    									{// display transfer from group to user - >ubergabe an...
    										$block[$needed[$key]['date']]['case'] = " case 4 c2";
    										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    										$block[$needed[$key]['date']]['give'] = $doctorsarray['0'];
    										$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
    										$block[$needed[$key]['date']]['add'] = "";
    										$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": Group " . $direction . "  " . $doctorsarray[$ch['userid']] . "<br />";
    										$block[$needed[$key]['date']]['sonstige_more'] = '';
    										$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    										
    									}
    								}
    								if($ch['type'] == "CS")
    								{
    									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
    									if($ch['amount'] < 0)
    									{
    										$ch['amount'] = ($ch['amount'] * (-1));
    									}
    									$direction = "übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an";
    									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];
    
    									$block[$needed[$key]['date']]['case'] = "case 4 cs1";
    									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    									$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
    									$block[$needed[$key]['date']]['recieve'] = $doctorsarray['0'];
    									$block[$needed[$key]['date']]['add'] = $ch['amount'];
    									$block[$needed[$key]['date']]['subs'] = "";
    									$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    									$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": " . $doctorsarray[$ch['userid']] . " " . $direction . " Gruppe<br />";
    									$block[$needed[$key]['date']]['sonstige_more'] = '';
    									$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    									
    								}
    								break;
    
    							case "5":
    
    								if($ch['userid'] != 0)
    								{
    									$block[$needed[$key]['date']]['extra'] = "hide";
    								}
    								$ch['amount'] = ($ch['amount'] * (-1));
    								$block[$needed[$key]['date']]['case'] = "case 5 cs1 sent to pacinet";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
    								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']];
    								$block[$needed[$key]['date']]['add'] = "";
    								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $doctorsarray[$ch['userid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = '';
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    								break;
    
    							case "6": //sonstiges
    								if($ch['amount'] < 0)
    								{
    									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
    									$ch['amount'] = ($ch['amount'] * (-1));
    									$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
    									$block[$needed[$key]['date']]['recieve'] = "Sonstiges";
    									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    									$block[$needed[$key]['date']]['add'] = "";
    									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    									
    								}
    								else
    								{
    									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
    									$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
    									$block[$needed[$key]['date']]['recieve'] = "Sonstiges";
    									$block[$needed[$key]['date']]['subs'] = "";
    									$block[$needed[$key]['date']]['add'] = $ch['amount'];
    									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    									
    								}
    								if($ch['userid'] != 0)
    								{
    									$block[$needed[$key]['date']]['extra'] = "hide";
    								}
    								$block[$needed[$key]['date']]['case'] = "case 6 sonstiges";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    								$block[$needed[$key]['date']]['details'] =  $methodsarray[$ch['methodid']] . ": <b>" . $doctorsarray[$ch['userid']] . "</b>" . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = $ch['sonstige_more'];
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    								
    								break;
    							case "7":
    								if($ch['userid'] != 0)
    								{
    									$block[$needed[$key]['date']]['extra'] = "hide";
    								}
    								$ch['amount'] = ($ch['amount'] * (-1));
    								$block[$needed[$key]['date']]['case'] = "case 7 ";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['methode'] = $methodsarray[$ch['methodid']];
    								$block[$needed[$key]['date']]['amount'] = $ch['amount'];
    								$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
    								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']];
    								$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
    								$block[$needed[$key]['date']]['add'] = "";
    								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $doctorsarray[$ch['userid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = '';
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    
    								break;
    							case "8":// remove from user or patient and addto consumption
    								if($ch['source'] == "u"){ // Take in consideration, only if  the consumption was from user stock
        								if($ch['userid'] != 0)
        								{
        									$block[$needed[$key]['date']]['extra'] = "hide";
        								}
        								$block[$needed[$key]['date']]['case'] = "case 8";
        								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
        								$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
        								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']]."(Verbrauch)";
        								$block[$needed[$key]['date']]['add'] = "";
        								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
        								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
//         								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an " . $doctorsarray[$ch['userid']] . "<br />";
        								$block[$needed[$key]['date']]['details'] = "user gives for consumption from his stoc";
        								$block[$needed[$key]['date']]['sonstige_more'] = '';
        								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
        								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
        								
    								} 
//     								else {
//         								if($ch['userid'] != 0)
//         								{
//         									$block[$needed[$key]['date']]['extra'] = "hide";
//         								}
//         								$block[$needed[$key]['date']]['case'] = "case 8";
//         								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
//         								$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
//         								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']]."(Verbrauch)*";
//         								$block[$needed[$key]['date']]['add'] = "";
//         								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
//         								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
//         								$block[$needed[$key]['date']]['details'] = "user gives for consumption from his stoc";
//         								$block[$needed[$key]['date']]['sonstige_more'] = '';
//         								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								    
//     								}
    
    								break;
    								
    							case "9":
    								if($ch['userid'] != 0)
    								{
    									$block[$needed[$key]['date']]['extra'] = "hide";
    								}
    								//	$name .=" case 9 <br/>";
    								$block[$needed[$key]['date']]['case'] = "case 9";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
    								$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
    								$block[$needed[$key]['date']]['add'] = $ch['amount'];
    								$block[$needed[$key]['date']]['subs'] = "";
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
    								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an " . $doctorsarray[$ch['userid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = '';
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    								
    								break;
    							case "12":
    							    if($ch['userid'] != 0)
    							    {
    							        $block[$needed[$key]['date']]['extra'] = "hide";
    							    }
    								$block[$needed[$key]['date']]['case'] = "case 12 sent to user from patient";
    								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
    								$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
    								$block[$needed[$key]['date']]['add'] = $ch['amount'];
    								$block[$needed[$key]['date']]['subs'] = '';
    								$block[$needed[$key]['date']]['totaltr'] = $totaltr; //
    								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $doctorsarray[$ch['userid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $patientsSelector[0][$ch['ipid']] . "<br />";
    								$block[$needed[$key]['date']]['sonstige_more'] = '';
    								$block[$needed[$key]['date']]['methodid'] = $ch['methodid'];
    								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];          //ISPC-2768 Lore 05.01.2021
    								
    
    								break;
    							default:
    								if($ch['type'] == "N")
    								{
    									/* $block[$needed[$key]['date']]['extra'] = "hide";
    									$block[$needed[$key]['date']]['case'] = "default medicamnt name";
    									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
    									$block[$needed[$key]['date']]['methode'] = "";
    									$block[$needed[$key]['date']]['amount'] = "";
    									$block[$needed[$key]['date']]['give'] = "";
    									$block[$needed[$key]['date']]['recieve'] = "";
    									$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
    									$block[$needed[$key]['date']]['add'] = "";
    									$block[$needed[$key]['date']]['subs'] = "";
    									$block[$needed[$key]['date']]['totaltr'] = "";
    									$block[$needed[$key]['date']]['details'] = ""; */
    								}
    								break;
    						}
    						$k++;
    					}
    				}
    
    
/*     				foreach($block as $kh => $value)
    				{
    					$info[$kh]['extra'] = $value['extra'];
    					if($info[$kh]['extra'] == "hide")
    					{
    						unset($block[$needed[$key]['date']]);
    					}
    				} */
    			}
			}

			
// 			print_r(count($block));
// 			exit;
// 			print_r($block); 
// 			exit;
// 			2016-07-18 16:48:55
			
			
// 			foreach($block as  $date => $details_arr){
// 			    print_r(count($details_arr));
// 			    print_r("\n");
// 			}
			
			$total_adds = 0;
			$total_subs = 0;
			$total_data = 0;
			$total_data_s = 0;
			$total_data_h = 0;
			
            $j=0;

             if($_REQUEST['list'] != "overall"){
                $req_date = $requested_period['start']." 00:00:00";
                $total_data_s = $histStock->getClientTotalbydate($clientid, $medid, $req_date,true);
                $total_data_h = $hist->getClientTotalbydate($clientid, $medid, $req_date,true);
                
                $total_data = $total_data_s + $total_data_h;
             } else{
                 $total_data = 0;
             }
           
            //ispc 1864 p.5
            $this->view->total_data_original = $total_data; 
             
			foreach($block as  $date => $details){
			        $ned_block[$date]['extra'] = $details['extra'];		    
			        $ned_block[$date]['case'] = $details['case'];		    
    			    $ned_block[$date]['date'] = $details['date'];		    
    			    $ned_block[$date]['give'] = $details['give'];		    
//     			    $ned_block[$date]['recieve'] = $details['recieve']."<br/>".$details['methodid'];
    			    $ned_block[$date]['recieve'] = $details['recieve'];
    			    
    			    $ned_block[$date]['add'] = $details['add'];
    			    $ned_block[$date]['subs'] = abs($details['subs']);
    			    $ned_block[$date]['details'] = $details['details'];
    			    $ned_block[$date]['sonstige_more'] = $details['sonstige_more'];
    			    $ned_block[$date]['methodid'] = $details['methodid'];
    
                    if($details['methodid'] != "1" && $details['methodid'] != "4"){
                        $total_data  =  $total_data + ( $details['add'] - abs($details['subs']));
         			    $ned_block[$date]['hide'] = "0";
                    } else {
         			    $ned_block[$date]['hide'] = "1";
                    }
                    
     			    $ned_block[$date]['totaltr'] = $total_data;
     			    
     			    $ned_block[$date]['btm_number'] = $details['btm_number'];          //ISPC-2768 Lore 05.01.2021
     			    
     			    
     			    $ned_block[$date]['row_id'] = $details['row_id'];
     			    $ned_block[$date]['table_name'] = $details['table_name'];
		    }
			
		    
		    foreach($ned_block as $kh => $value)
		    {
		        if($value['hide'] == "1")
		        {
		            unset($ned_block[$kh]);
		        }
		    }
		    
		    
		   
		    //ispc 1864 - remove methodid 13 and add correction with red + recalculate bestand with correction
		    if ( ! empty($ned_block)){
		    
		    	
		    	
		    	$corrections_array = array();
		    	$row_id = array_column($ned_block, 'row_id') ;
		    	$mcbtmc = new MedicationClientBTMCorrection();
		    
		    	$corrections = $mcbtmc->get_by_correction_id ($row_id ,$logininfo->clientid);
		    	foreach($corrections as $row) {
		    		$corrections_array[$row['correction_table']][$row['correction_id']] = $row;
		    	}
		    	
		    	$cnt = 0;
		    
		    	$btmbuchhistory_lang = $this->view->translate('btmbuchhistory_lang');
		    	$correction_event_text_pdf = "<br>" . $btmbuchhistory_lang['correction_event_text_pdf'];
		    
		    	if ( ! empty($corrections_array)) {
		    		$total_data = $this->view->total_data_original;
			    	foreach($ned_block as $k => &$row) {
	
			    		$cnt++;
			    		
			    		if ( ! empty($corrections_array[ $row['table_name'] ][ $row['row_id'] ])) {
			    
			    			$correction = $corrections_array[ $row['table_name'] ][ $row['row_id'] ];
			    
			    			$correction_event_text_row = sprintf($correction_event_text_pdf, $doctorsarray[$correction['create_user']], date("d.m.Y H:i", strtotime($correction['create_date'])));
			    
			    			$row_aad = 0;
			    			$row_subs = 0;
			    			
			    
			    			if (trim($row['give']) != '' ) {
			    				$row['give'] .= $correction_event_text_row;
			    			}
			    			if (trim($row['recieve']) != '' ) {
			    				$row['recieve'] .= $correction_event_text_row;
			    			}
			    
			    			if (trim($row['add']) != '' ) {
			    				
			    				$row_aad = $correction['amount'];
			    				
			    				$row['add'] = "<font color='red' style=\"color:#ff0000;\">" . $row['add'] . '->' . $correction['amount'] . "</font>";
			    			}
			    			if (trim($row['subs']) != ''  && $row['subs'] != 0) { // TODO-3167 Ancuta 04.06.2020
			    				$row_subs = $correction['amount'];
			    				$row['subs'] = "<font color='red' style=\"color:#ff0000;\">" . $row['subs'] . '->' . $correction['amount'] . "</font>";
			    			}
			    
			    			$row['details'] .= $correction_event_text_row;
			    					    			
			    			if($row['methodid'] != "1" && $row['methodid'] != "4"){
			    				$total_data  =  $total_data + ( $row_aad - abs($row_subs));
			    				$row['hide'] = "0";
			    			} else {
			    				$row['hide'] = "1";
			    			}
			    			 
			    			$row['totaltr'] = $total_data;
	// 		    			$row['totaltr'] = $total_data . " A" .$cnt;
			    			
			    			
			    			
			    		} else {
			    			
			    			$total_data_before=$total_data;
							
			    			if($row['methodid'] != "1" && $row['methodid'] != "4"){
			    				$total_data  =  $total_data + ( $row['add'] - abs($row['subs']));
			    				$row['hide'] = "0";
			    			} else {
			    				$row['hide'] = "1";
			    			}
			    			
			    			$row['totaltr'] = $total_data;
	// 		    			$row['totaltr'] = $total_data . " X".$cnt ." " .$total_data_before;
			    			
			    		}
			    		
			    			
			    	}
		    	}
		    
		    
		    }
		    
		    
		    
// 			print_r($ned_block);
// 			exit;
			
			$this->view->rowsnr = count($ned_block);
			$this->view->medication_name = $medicationsarray[$medid]['name'];

			/* ---------------------------------------------------------------------- */
			/* ------------------- Client Data--------------------------------------- */
			/* ---------------------------------------------------------------------- */

			$clientdata = Pms_CommonData::getClientData($logininfo->clientid);
			$this->view->client_name = $clientdata[0]['client_name'];
			$this->view->client_city = $clientdata[0]['city'];
			$this->view->client_street = $clientdata[0]['street1'];
			$this->view->client_zip = $clientdata[0]['postcode'];
			$this->view->client_phone = $clientdata[0]['phone'];

			$this->view->medicationarray = $medicationsarray;
			$this->view->methodsarray = $methodsarr;
			$this->view->stocksarray = $clientStockArray;
			$this->view->nrofrows = count($clientHistoryArray);
			
			$this->view->medication_total = $total_data;

			$headergr = Pms_Template::createTemplate($this->view, 'templates/btmformmedicationheader.html');
			$footergr = Pms_Template::createTemplate($this->view, 'templates/btmformmedicationfooter.html');
			$this->view->headergr = $headergr;
			$this->view->footergr = $footergr;

			$form_team = new Pms_Grid($ned_block, 1, count($ned_block), "btmformmedication.html");

			$this->view->btmform_team_grid = $form_team->renderGrid();
			$form_teampdf = new Pms_Grid($ned_block, 1, count($ned_block), "btmformmedicationpdf.html");
			$pdfgridtable['grid_team'] = $form_teampdf->renderGrid();
			$pdfgridtable['ispdf'] = '1';


			if(strlen($_POST['pdf_team']) > 0)
			{
				$pdfgridtable['client_name'] = $this->view->client_name;
				$pdfgridtable['client_street'] = $this->view->client_street;
				$pdfgridtable['client_zip'] = $this->view->client_zip;
				$pdfgridtable['client_city'] = $this->view->client_city;
				$pdfgridtable['medication_name'] = $this->view->medication_name;
				$pdfgridtable['medication_total'] = $this->view->medication_total;
				$pdfgridtable['total_data_original'] = $this->view->total_data_original;
				
				
				$this->generateformPdf(4, $pdfgridtable, "BtmMedication", "btmformmedication.html");
			}
		}

		public function btmformteamAction()
		{
		    set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patient', $logininfo->userid, 'canview');
			$methodsarr = array("1" => "Übergabe von", "2" => "Lieferung", "3" => "Sonstiges", "4" => "Übergabe an", "5" => "Abgabe an Patienten", "6" => "Sonstiges", "7" => "Abgabe an Patienten", "8" => "Verbrauch", "9" => "Rücknahme von Patienten", '10' => '', '11' => '', '12' => '');


			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$medid = $_GET['medid'];

			$stocks = new MedicationClientStock();
			$stksarray = $stocks->getAllMedicationClientStock($clientid);


			//prepare medis stocks array to get medis data by medis stocks id
			$medisstr = "'99999999999'";
			$comma = ",";
			foreach($stksarray as $stocmedis)
			{
				$stocmedications[$stocmedis['medicationid']] = $stocmedis;
				$medisstr .= $comma . "'" . $stocmedis['medicationid'] . "'";
				$comma = ",";
			}

			//get medication stocks medis id
			$med = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('isdelete = 0 ')
				->andWhere('name!=""')
				->andWhere('id=' . $medid)
				->andWhere('clientid = ' . $clientid . '');
			$medarray = $med->fetchArray();


			foreach($medarray as $medication)
			{
				if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
				{
					$medicationsarray[$medication['id']] = $medication;
					$medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
				}
			}
			$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('isdelete =  0')
				->andWhere('clientid = ' . $clientid); // get all client users -> 06.03.2012
			$grouparray = $group->fetchArray();

			$groupstr = "'999999999999'";
			$comma = ",";
			foreach($grouparray as $group)
			{
				$groupstr .= $comma . "'" . $group['id'] . "'";
				$comma = ",";
			}
			$groupusrs = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('groupid IN (' . $groupstr . ')')
				->andWhere('isdelete = 0')
				->andWhere('isactive = 0')
				->andWhere('clientid = ' . $clientid . '')
				->orderBy('last_name ASC');
			$groupusers = $groupusrs->fetchArray();

			//prepare users array..
			foreach($groupusers as $user)
			{

				$usersarray[$user['id']] = $user['id'];
				$doctorusers[$user['id']]['fullname'] = $user['user_title'] . " " . $user['first_name'] . ", " . $user['last_name'];
			}
			$usersarray[] = "99999999";
			$doctorsSelect[0] = "Gruppe / Tresor";
			foreach($doctorusers as $dockey => $docuser)
			{
				$doctors[$dockey]['fullname'] = $docuser['fullname'];
				$doctorsSelect[$dockey] = $docuser['fullname'];
			}
			$this->view->doctorsSelect = $doctorsSelect;


			$qpa = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
				->whereIn('userid', $usersarray)
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$doctorPatients = $qpa->fetchArray();
			foreach($doctorPatients as $dPatients)
			{
				$doctorsPatientsEpids[$dPatients['userid']][$dPatients['epid']] = $dPatients['epid'];
				$doctorsEpids[] = $dPatients['epid'];
			}
			$doctorsEpids[] = "9999999999";


			$epidipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$clientDocEpidIpids = $epidipid->fetchArray();


			foreach($clientDocEpidIpids as $patientE)
			{
				$finalPatientsEpids[$patientE['epid']] = $patientE;
				$finalPatientsIpids[] = $patientE['ipid'];
				$finalPatientsIpidsArr[$patientE['ipid']] = $patientE['ipid'];

				//for all patients list
				$patient_epids[$patientE['ipid']] = $patientE;
			}
			if(empty($finalPatientsIpids))
			{
				$finalPatientsIpids[] = "999999999999";
			}

			$patientsDetails = Doctrine_Query::create()
				->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
				->from('PatientMaster p')
				->whereIn('p.ipid', $finalPatientsIpids)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isdischarged = "0"')
				->andWhere('p.isstandby = "0"')
				->andWhere('p.isstandbydelete = "0"');
			$patientDetailsArr = $patientsDetails->fetchArray();


			foreach($patientDetailsArr as $patientDetail)
			{
				$finalPatientDetails[$patient_epids[$patientDetail['ipid']]['epid']] = $patientDetail;
			}
			ksort($finalPatientDetails);
			//		changed to get all patients not only the assigned... 30.04.2012 radu
			foreach($finalPatientDetails as $curentPatient)
			{
				$finalDoctorsPatients[0][0] = "Patienten wählen";
				$finalDoctorsPatients[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
			}
			$this->view->patientsDoctorSelect = $finalDoctorsPatients; //changed to get all patients not only the assigned... 30.04.2012 radu

			$hist = new MedicationClientHistory();
			$clientHistoryData = $hist->getVerlaufMedicationsClientHistory($clientid, $medid);

			//ispc 1864 - remove methodid 13 and add correction with red
			foreach($clientHistoryData as $k => &$row) {
				if ( $row['methodid'] == 13) {
					unset($clientHistoryData[$k]);
				} else {
					$row['table_name'] = 'MedicationClientHistory';
				}
			}
			
			
			$histStock = new MedicationClientStock();
			$clientStockData = $histStock->getVerlaufMedicationsClientStock($clientid, $medid);

			//ispc 1864 - remove methodid 13 and add correction with red
			foreach($clientStockData as $k => &$row) {
				if ( $row['methodid'] == 13) {
					unset($clientStockData[$k]);
				} else {
					$row['table_name'] = 'MedicationClientStock';
				}
			}
			
			
			$x = 1;
			foreach($clientStockData as $dataClientStock)
			{
				if($dataClientStock['amount'] == 0 && $dataClientStock['userid'] == 0 && $dataClientStock['methodid'] == 0)
				{
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x]['type'] = "N"; //new
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
				}
				elseif($dataClientStock['amount'] != 0 && ($dataClientStock['methodid'] != 0 && $dataClientStock['methodid'] != 1 && $dataClientStock['methodid'] != 4 && $dataClientStock['methodid'] != 5))
				{
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientStock['create_date']);
				}
				else
				{
					// TO DO case 5 transfer to patient
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					foreach($clientHistoryData as $clientData)
					{
						if($clientData['stid'] == $dataClientStock['id'])
						{
							$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x]['ipid'] = $clientData['ipid'];
						}
					}
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientStock['create_date']);
				}
				$clientStockArray[$dataClientStock['id']] = $dataClientStock;
				$x++;
			}

			foreach($clientHistoryData as $dataClientHistory)
			{
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']] = $dataClientHistory;
				if($dataClientHistory['methodid'] < 4)
				{
					if($dataClientHistory['amount'] < 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
					else if($dataClientHistory['amount'] > 0 && $dataClientHistory['stid'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
				}
				else
				{
					if($dataClientHistory['amount'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
					else if($dataClientHistory['amount'] < 0 && $dataClientHistory['stid'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
					}
				}
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientHistory['create_date']));
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $histStock->getClientTotalbydate($clientid, $medid, $dataClientHistory['create_date']);
			}

			$clientHistoryArray = $this->array_sort($clientHistoryArray, "date", SORT_ASC);
			/* ----------------------------------------------------------------------------------------------------------- */
			//get methods
			$methodsarray = $methodsarr;
			//get stock values
			$stocksarray = $clientStockArray;
			//get doctors array
			$doctorsarray = $doctorsSelect;
			//get patinets array
			$patientsDetailsHistory = Doctrine_Query::create()
				->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
				->from('PatientMaster p')
				->whereIn('p.ipid', $finalPatientsIpids);
			$patientDetailsHistoryArr = $patientsDetailsHistory->fetchArray();

			$patientsSelector = array();
			foreach($patientDetailsHistoryArr as $curentPatient)
			{
				$patientsSelector[0][0] = "Patienten wählen";
				$patientsSelector[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
			}
			//get member id

			$block = array();
			foreach($clientHistoryArray as $key => $val)
			{
				$needed[$key]['sumary'] = $val['sumary'];
				$needed[$key]['date'] = $val['date'];
				$needed[$key]['totaltr'] = $val['totaltr'];
				$totaltr = $needed[$key]['totaltr'];


				foreach($needed[$key]['sumary'] as $clienthistoryid => $ch)
				{

					if(!in_array($ch['id'], $skippedid))
					{
						switch($ch['methodid'])
						{
							//PLUS OPERATION
							case "1":
								if($ch['type'] == "C")
								{ //Übergabe von
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];

									if($ch['stid'] == 0)
									{

										if($ch['userid'] != 0)
										{
											$block[$needed[$key]['date']]['extra'] = "hide";
										}
										$block[$needed[$key]['date']]['case'] = "case 1 c1";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$next_user];
										$block[$needed[$key]['date']]['add'] = $ch['amount'];
										$block[$needed[$key]['date']]['subs'] = "";
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . ' transferat prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . 'la userul' . $doctorsarray[$next_user];
										$block[$needed[$key]['date']]['sonstige_more'] = '';
									}
									else
									{
										$block[$needed[$key]['date']]['case'] = "case 1 c11";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
										$block[$needed[$key]['date']]['recieve'] = "";
										$block[$needed[$key]['date']]['add'] = $ch['amount'];
										$block[$needed[$key]['date']]['subs'] = "";
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . ' transferat prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . ' la userul ' . $doctorsarray['0'];
										$block[$needed[$key]['date']]['sonstige_more'] = '';
									}
								}
								if($ch['type'] == "CS")
								{
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];

									$block[$needed[$key]['date']]['case'] = "case 1 cs";
									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
									$block[$needed[$key]['date']]['add'] = "";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['totaltr'] = $totaltr;
									$block[$needed[$key]['date']]['details'] = 'Group a transferat cu + prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . 'la userul ' . $doctorsarray[$ch['userid']];
									$block[$needed[$key]['date']]['sonstige_more'] = '';
								}
								break;
							case "2": //liferung
								if($ch['amount'] < 0)
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
									$ch['amount'] = ($ch['amount'] * (-1));
								}
								else
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
								}

								if($ch['userid'] != 0)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}

								$block[$needed[$key]['date']]['case'] = "liferung case 2";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = "Lieferung";
								$block[$needed[$key]['date']]['recieve'] = "";
								$block[$needed[$key]['date']]['subs'] = "";
								$block[$needed[$key]['date']]['add'] = $ch['amount'];
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = '';
								break;


							case "3": //sonstige
								if($ch['amount'] < 0)
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
									$ch['amount'] = ($ch['amount'] * (-1));
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['add'] = "";
								}
								else
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
									$block[$needed[$key]['date']]['give'] = "Sonstiges";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
								}

								if($ch['userid'] != 0)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$block[$needed[$key]['date']]['case'] = "sonstige case 3";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = $ch['sonstige_more'];
								break;

							//MINUS OPERATION
							case "4":
								if($ch['type'] == "C")
								{ //Übergabe von
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$direction = "übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an";
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];



									if($ch['stid'] == 0)
									{//display transfer from user to user
										if($ch['userid'] != 0)
										{
											$block[$needed[$key]['date']]['extra'] = "hide";
										}
										$block[$needed[$key]['date']]['case'] = "case 4 c1";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['add'] = "";
										$block[$needed[$key]['date']]['subs'] = $ch['amount'];
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": " . $doctorsarray[$next_user] . " " . $direction . " " . $doctorsarray[$ch['userid']] . "<br />";
										$block[$needed[$key]['date']]['sonstige_more'] = '';
									}
									else
									{// display transfer from group to user - >ubergabe an...
										$block[$needed[$key]['date']]['case'] = " case 4 c2";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['give'] = "";
										$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
										$block[$needed[$key]['date']]['add'] = "";
										$block[$needed[$key]['date']]['subs'] = $ch['amount'];
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": Group " . $direction . "  " . $doctorsarray[$ch['userid']] . "<br />";
										$block[$needed[$key]['date']]['sonstige_more'] = '';
									}
								}
								if($ch['type'] == "CS")
								{
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$direction = "übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an";
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];

									$block[$needed[$key]['date']]['case'] = "case 4 cs1";
									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
									$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['totaltr'] = $totaltr;
									$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": " . $doctorsarray[$ch['userid']] . " " . $direction . " Gruppe<br />";
									$block[$needed[$key]['date']]['sonstige_more'] = '';
								}
								break;

							case "5":

								if($ch['userid'] != 0)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$ch['amount'] = ($ch['amount'] * (-1));
								$block[$needed[$key]['date']]['case'] = "case 5 cs1 sent to pacinet";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = "";
								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['add'] = "";
								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $doctorsarray[$ch['userid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = '';
								break;

							case "6": //sonstiges
								if($ch['amount'] < 0)
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
									$ch['amount'] = ($ch['amount'] * (-1));
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = "Sonstiges";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['add'] = "";
								}
								else
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
								}
								if($ch['userid'] != 0)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$block[$needed[$key]['date']]['case'] = "case 6 sonstiges";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = $ch['sonstige_more'];
								break;
							case "7":
								if($ch['userid'] != 0)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$ch['amount'] = ($ch['amount'] * (-1));
								$block[$needed[$key]['date']]['case'] = "case 7 ";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['methode'] = $methodsarray[$ch['methodid']];
								$block[$needed[$key]['date']]['amount'] = $ch['amount'];
								$block[$needed[$key]['date']]['give'] = "";
								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
								$block[$needed[$key]['date']]['add'] = "";
								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $doctorsarray[$ch['userid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = '';

								break;
							case "9":
								if($ch['userid'] != 0)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								//	$name .=" case 9 <br/>";
								$block[$needed[$key]['date']]['case'] = "case 9";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['recieve'] = "";
								$block[$needed[$key]['date']]['add'] = $ch['amount'];
								$block[$needed[$key]['date']]['subs'] = "";
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an " . $doctorsarray[$ch['userid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = '';

								break;
							case "12":
							    if($ch['userid'] != 0)
							    {
							        $block[$needed[$key]['date']]['extra'] = "hide";
							    }
								$block[$needed[$key]['date']]['case'] = "case 12 sent to user from patient";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
								$block[$needed[$key]['date']]['add'] = $ch['amount'];
								$block[$needed[$key]['date']]['subs'] = '';
								$block[$needed[$key]['date']]['totaltr'] = $totaltr; //
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $doctorsarray[$ch['userid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $patientsSelector[0][$ch['ipid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = '';

								break;
							default:
								if($ch['type'] == "N")
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
									$block[$needed[$key]['date']]['case'] = "default medicamnt name";
									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
									$block[$needed[$key]['date']]['methode'] = "";
									$block[$needed[$key]['date']]['amount'] = "";
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
									$block[$needed[$key]['date']]['add'] = "";
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['totaltr'] = "";
									$block[$needed[$key]['date']]['details'] = "";
								}
								break;
						}
						
						//ispc 1864
						if ( ! empty( $block[$needed[$key]['date']]['details'] )) {
							$block[$needed[$key]['date']] ['row_id'] = $ch['id'];//$needed[$key]['sumary']['id'];
							$block[$needed[$key]['date']] ['table_name'] = $ch['table_name'];//$needed[$key]['sumary']['table_name'];
						}
					}
				}


				foreach($block as $kh => $value)
				{
					$info[$kh]['extra'] = $value['extra'];
					if($info[$kh]['extra'] == "hide")
					{
						unset($block[$needed[$key]['date']]);
					}
				}
			}
			
			
			
			//ispc 1864 - remove methodid 13 and add correction with red
			if ( ! empty($block)){
			
				$corrections_array = array();
				$row_id = array_column($block, 'row_id') ;
				$mcbtmc = new MedicationClientBTMCorrection();
			
				$corrections = $mcbtmc->get_by_correction_id ($row_id ,$logininfo->clientid);
				foreach($corrections as $row) {
					$corrections_array[$row['correction_table']][$row['correction_id']] = $row;
				}			
			
				$cnt = 0;
			
				$btmbuchhistory_lang = $this->view->translate('btmbuchhistory_lang');
				$correction_event_text_pdf = "<br>" . $btmbuchhistory_lang['correction_event_text_pdf'];
			
				foreach($block as $k => &$row) {
						
						
						
					if ( ! empty($corrections_array[ $row['table_name'] ][ $row['row_id'] ])) {
			
						$correction = $corrections_array[ $row['table_name'] ][ $row['row_id'] ];
			
						$correction_event_text_row = sprintf($correction_event_text_pdf, $doctorsarray[$correction['create_user']], date("d.m.Y H:i", strtotime($correction['create_date'])));
			
			
						if (trim($row['give']) != '' ) {
							$row['give'] .= $correction_event_text_row;
						}
						if (trim($row['recieve']) != '' ) {
							$row['recieve'] .= $correction_event_text_row;
						}
			
						if (trim($row['add']) != '' ) {
							$row['add'] = "<font color='red' style=\"color:#ff0000;\">" . $row['add'] . '->' . $correction['amount'] . "</font>";
						}
						if (trim($row['subs']) != '' ) {
							$row['subs'] = "<font color='red' style=\"color:#ff0000;\">" . $row['subs'] . '->' . $correction['amount'] . "</font>";
						}
			
						$row['details'] .= $correction_event_text_row;
					}
						
						
				}
			
			
			}

			$this->view->rowsnr = count($block);
			$this->view->medication_name = $medicationsarray[$medid]['name'];

			/* ---------------------------------------------------------------------- */
			/* ------------------- Client Data--------------------------------------- */
			/* ---------------------------------------------------------------------- */

			$clientdata = Pms_CommonData::getClientData($logininfo->clientid);
			$this->view->client_name = $clientdata[0]['client_name'];
			$this->view->client_city = $clientdata[0]['city'];
			$this->view->client_street = $clientdata[0]['street1'];
			$this->view->client_zip = $clientdata[0]['postcode'];
			$this->view->client_phone = $clientdata[0]['phone'];

			$this->view->medicationarray = $medicationsarray;
			$this->view->methodsarray = $methodsarr;
			$this->view->stocksarray = $clientStockArray;
			$this->view->nrofrows = count($clientHistoryArray);

			$headergr = Pms_Template::createTemplate($this->view, 'templates/btmteammedicationheader.html');
			$footergr = Pms_Template::createTemplate($this->view, 'templates/btmteammedicationfooter.html');
			$this->view->headergr = $headergr;
			$this->view->footergr = $footergr;

			$form_team = new Pms_Grid($block, 1, count($block), "btmteamform.html");

			$this->view->btmform_team_grid = $form_team->renderGrid();
			$form_teampdf = new Pms_Grid($block, 1, count($block), "btmteamformpdf.html");
			$pdfgridtable['grid_team'] = $form_teampdf->renderGrid();
			$pdfgridtable['ispdf'] = '1';


			if(strlen($_POST['pdf_team']) > 0)
			{
				$pdfgridtable['client_name'] = $this->view->client_name;
				$pdfgridtable['client_street'] = $this->view->client_street;
				$pdfgridtable['client_zip'] = $this->view->client_zip;
				$pdfgridtable['client_city'] = $this->view->client_city;
				$pdfgridtable['medication_name'] = $this->view->medication_name;

				$this->generateformPdf(4, $pdfgridtable, "BtmTeamMedication", "btmteammedication.html");
			}
		}

		public function btmformmemberAction()
		{
		    set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Patient', $logininfo->userid, 'canview');
			$methodsarr = array("1" => "Übergabe von", "2" => "Lieferung", "3" => "Sonstiges", "4" => "Übergabe an", "5" => "Abgabe an Patienten", "6" => "Sonstiges", "7" => "Abgabe an Patienten", "8" => "Verbrauch", "9" => "Rücknahme von Patienten");
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$medid = $_GET['medid'];
			$member = $_GET['member'];

			$stocks = new MedicationClientStock();
			$stksarray = $stocks->getAllMedicationClientStock($clientid);

			//prepare medis stocks array to get medis data by medis stocks id
			$medisstr = "'99999999999'";
			$comma = ",";
			foreach($stksarray as $stocmedis)
			{
				$stocmedications[$stocmedis['medicationid']] = $stocmedis;
				$medisstr .= $comma . "'" . $stocmedis['medicationid'] . "'";
				$comma = ",";
			}

			//get medication stocks medis id
			$med = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('isdelete = 0 ')
				->andWhere('name!=""')
				->andWhere('id=' . $medid)
				->andWhere('clientid = ' . $clientid . '');
			$medarray = $med->fetchArray();

			foreach($medarray as $medication)
			{
				if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
				{
					$medicationsarray[$medication['id']] = $medication;
					$medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
				}
			}
			$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('isdelete =  0')
				->andWhere('clientid = ' . $clientid); // get all client users not only doctors : 06.03.2012
			$grouparray = $group->fetchArray();

			$groupstr = "'999999999999'";
			$comma = ",";
			foreach($grouparray as $group)
			{
				$groupstr .= $comma . "'" . $group['id'] . "'";
				$comma = ",";
			}
			$groupusrs = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('groupid IN (' . $groupstr . ')')
				//->andWhere('isdelete = 0')
				//->andWhere('isactive = 0')
				->andWhere('clientid = ' . $clientid . '')
				->orderBy('last_name ASC');
			$groupusers = $groupusrs->fetchArray();

			//prepare users array..
			foreach($groupusers as $user)
			{

				$usersarray[$user['id']] = $user['id'];
				
				if($user['isactive'] == "1" || $user['isdelete'] == "1")
				{
					$doctorusers[$user['id']]['fullname'] = "<i>Sonstiges *</i>";
				}
				else
				{
					$doctorusers[$user['id']]['fullname'] = $user['user_title'] . " " . $user['first_name'] . ", " . $user['last_name'];
				}
			}
			$usersarray[] = "99999999";
			$doctorsSelect[0] = "Gruppe / Tresor";
			foreach($doctorusers as $dockey => $docuser)
			{
				$doctors[$dockey]['fullname'] = $docuser['fullname'];
				$doctorsSelect[$dockey] = $docuser['fullname'];
			}
			$this->view->doctorsSelect = $doctorsSelect;
			$qpa = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
				->whereIn('userid', $usersarray)
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$doctorPatients = $qpa->fetchArray();
			foreach($doctorPatients as $dPatients)
			{
				$doctorsPatientsEpids[$dPatients['userid']][$dPatients['epid']] = $dPatients['epid'];
				$doctorsEpids[] = $dPatients['epid'];
			}
			$doctorsEpids[] = "9999999999";
			$epidipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->andWhere('clientid = "' . $logininfo->clientid . '"');

			$clientDocEpidIpids = $epidipid->fetchArray();
			foreach($clientDocEpidIpids as $patientE)
			{
				$finalPatientsEpids[$patientE['epid']] = $patientE;
				$finalPatientsIpids[] = $patientE['ipid'];
				$finalPatientsIpidsArr[$patientE['ipid']] = $patientE['ipid'];

				//for all patients list
				$patient_epids[$patientE['ipid']] = $patientE;
			}
			if(empty($finalPatientsIpids))
			{
				$finalPatientsIpids[] = "999999999999";
			}

			$patientsDetails = Doctrine_Query::create()
				->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
				->from('PatientMaster p')
				->whereIn('p.ipid', $finalPatientsIpids)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isdischarged = "0"')
				->andWhere('p.isstandby = "0"')
				->andWhere('p.isstandbydelete = "0"');
			$patientDetailsArr = $patientsDetails->fetchArray();


			foreach($patientDetailsArr as $patientDetail)
			{
				$finalPatientDetails[$patient_epids[$patientDetail['ipid']]['epid']] = $patientDetail;
			}
			if($_REQUEST['dbg'])
			{
				print_r($finalPatientDetails);
				exit;
			}
			ksort($finalPatientDetails);
			//changed to get all patients not only the assigned... 30.04.2012 radu
			foreach($finalPatientDetails as $curentPatient)
			{
				$finalDoctorsPatients[0][0] = "Patienten wählen";
				$finalDoctorsPatients[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
			}
			$this->view->patientsDoctorSelect = $finalDoctorsPatients; //changed to get all patients not only the assigned... 30.04.2012 radu

			$hist = new MedicationClientHistory();
			$clientHistoryData = $hist->getVerlaufMedicationsClientHistory($clientid, $medid);
			
			//ispc 1864 - remove methodid 13 and add correction with red
			foreach($clientHistoryData as $k => &$row) {
				if ( $row['methodid'] == 13) {
					unset($clientHistoryData[$k]);
				} else {
					$row['table_name'] = 'MedicationClientHistory';
				}
			}
			
			$histStock = new MedicationClientStock();
			$clientStockData = $histStock->getVerlaufMedicationsClientStock($clientid, $medid);

			//ispc 1864 - remove methodid 13 and add correction with red
			foreach($clientStockData as $k => &$row) {
				if ( $row['methodid'] == 13) {
					unset($clientStockData[$k]);
				} else {
					$row['table_name'] = 'MedicationClientStock';
				}
			}
				
			$x = 1;
			foreach($clientStockData as $dataClientStock)
			{
				if($dataClientStock['amount'] == 0 && $dataClientStock['userid'] == 0 && $dataClientStock['methodid'] == 0)
				{
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x]['type'] = "N"; //new
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
				}
				elseif($dataClientStock['amount'] != 0 && ($dataClientStock['methodid'] != 0 && $dataClientStock['methodid'] != 1 && $dataClientStock['methodid'] != 4 && $dataClientStock['methodid'] != 5))
				{
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientStock['create_date']);
				}
				else
				{
					// TO DO case 5 transfer to patient
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x] = $dataClientStock;
					foreach($clientHistoryData as $clientData)
					{
						if($clientData['stid'] == $dataClientStock['id'])
						{
							$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['sumary'][$x]['ipid'] = $clientData['ipid'];
						}
					}
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientStock['create_date']));
					$clientHistoryArray[date("dmYHis", strtotime($dataClientStock['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientStock['create_date']);
				}
				$clientStockArray[$dataClientStock['id']] = $dataClientStock;
				$x++;
			}

			foreach($clientHistoryData as $dataClientHistory)
			{
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']] = $dataClientHistory;
				if($dataClientHistory['methodid'] < 4)
				{
					if($dataClientHistory['amount'] < 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientHistory['create_date']);
					}
					else if($dataClientHistory['amount'] > 0 && $dataClientHistory['stid'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientHistory['create_date']);
					}
				}
				else
				{
					if($dataClientHistory['amount'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "C"; //client
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientHistory['create_date']);
					}
					else if($dataClientHistory['amount'] < 0 && $dataClientHistory['stid'] > 0)
					{
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['sumary'][$dataClientHistory['id']]['type'] = "CS"; //for client from stock
						$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientHistory['create_date']);
					}
				}
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['date'] = date("d.m.Y H:i:s", strtotime($dataClientHistory['create_date']));
				$clientHistoryArray[date("dmYHis", strtotime($dataClientHistory['create_date']))]['totaltr'] = $hist->getUserDetails($clientid, $medid, $member, $dataClientHistory['create_date']);
			}

			$clientHistoryArray = $this->array_sort($clientHistoryArray, "date", SORT_ASC);

			$this->view->medication_name = $medicationsarray[$medid]['name'];
			$this->view->medicationarray = $medicationsarray;
			$this->view->methodsarray = $methodsarr;
			$this->view->stocksarray = $clientStockArray;
			/* ----------------------------------------------------------------------------------------------------------- */
			//get methods
			$methodsarray = $methodsarr;
			//get stock values
			$stocksarray = $clientStockArray;
			//get doctors array
			$doctorsarray = $doctorsSelect;
			//get patinets array
			$patientsDetailsHistory = Doctrine_Query::create()
				->select('ipid, isdelete, isdischarged, isstandby, isstandbydelete,AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") as fname, AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") as lname, AES_DECRYPT(middle_name,"' . Zend_Registry::get('salt') . '") as mname')
				->from('PatientMaster p')
				->whereIn('p.ipid', $finalPatientsIpids);
			$patientDetailsHistoryArr = $patientsDetailsHistory->fetchArray();

			$patientsSelector = array();
			foreach($patientDetailsHistoryArr as $curentPatient)
			{
				$patientsSelector[0][0] = "Patienten wählen";
				$patientsSelector[0][$curentPatient['ipid']] = $curentPatient['lname'] . ", " . $curentPatient['fname'] . " - " . $patient_epids[$curentPatient['ipid']]['epid'];
			}
			//get member id
			$block = array();
			foreach($clientHistoryArray as $key => $val)
			{
				$needed[$key]['sumary'] = $val['sumary'];
				$needed[$key]['date'] = $val['date'];
				$needed[$key]['totaltr'] = $val['totaltr'];
				$totaltr = $needed[$key]['totaltr'];


				foreach($needed[$key]['sumary'] as $clienthistoryid => $ch)
				{

					if(!in_array($ch['id'], $skippedid))
					{
						
						if ( ! empty( $needed[$key]['sumary'][($ch['id'])] ['id'])) {
							$block[$needed[$key]['date']]['row_id'] = $needed[$key]['sumary'][($ch['id'])] ['id'];
							$block[$needed[$key]['date']]['table_name'] = $needed[$key]['sumary'][($ch['id'])] ['table_name'];
							
							$block[$needed[$key]['date']]['sonstige_more'] = '';
						}
						switch($ch['methodid'])
						{
							//PLUS OPERATION
							case "1":
								if($ch['type'] == "C")
								{ //Übergabe von
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];

									if($ch['stid'] == 0)
									{

										if($ch['userid'] != $member && $next_user != $member)
										{
											$block[$needed[$key]['date']]['extra'] = "hide";
										}
										else
										{
											$block[$needed[$key]['date']]['extra'] = "show";
										}
										if($ch['userid'] == $member && $next_user != $member)
										{
											$block[$needed[$key]['date']]['give'] = "";
											$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$next_user];
											$block[$needed[$key]['date']]['add'] = "";
											$block[$needed[$key]['date']]['subs'] = $ch['amount'];											
										}
										elseif($ch['userid'] != $member && $next_user == $member)
										{
											$block[$needed[$key]['date']]['give'] = $doctorsarray[$ch['userid']];
											$block[$needed[$key]['date']]['recieve'] = "";
											$block[$needed[$key]['date']]['add'] = $ch['amount'];
											$block[$needed[$key]['date']]['subs'] = "";											
										}

										$block[$needed[$key]['date']]['case'] = "case 1 c1";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										
										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
										
										$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . ' transferat prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . 'la userul' . $doctorsarray[$next_user];
									}
									else
									{
										if($ch['userid'] != $member && $next_user != $member)
										{
											$block[$needed[$key]['date']]['extra'] = "hide";
										}
										else
										{
											$block[$needed[$key]['date']]['extra'] = "show";
										}

										$block[$needed[$key]['date']]['case'] = "case 1 c11";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['give'] = "";
										$block[$needed[$key]['date']]['recieve'] = "Gruppe / Tresor";
										$block[$needed[$key]['date']]['add'] = "";
										$block[$needed[$key]['date']]['subs'] = $ch['amount'];
										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
										
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . ' transferat prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . ' la userul ' . $doctorsarray['0'];
									}
								}
								if($ch['type'] == "CS")
								{
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];

									if($ch['userid'] != $member)
									{
										$block[$needed[$key]['date']]['extra'] = "hide";
									}
									$block[$needed[$key]['date']]['case'] = "case 1 cs";
									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
									$block[$needed[$key]['date']]['give'] = "Gruppe / Tresor";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
									
									$block[$needed[$key]['date']]['totaltr'] = $totaltr;
									$block[$needed[$key]['date']]['details'] = 'Group a transferat cu + prin metoda ' . $methodsarray[$ch['methodid']] . ' suma de ' . $ch['amount'] . 'la userul ' . $doctorsarray[$ch['userid']];
								}
								break;

							case "2": //liferung
								if($ch['amount'] < 0)
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
									$ch['amount'] = ($ch['amount'] * (-1));
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['add'] = "";
								}
								else
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
								}

								if($ch['userid'] == 0 || $ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								else
								{
									$block[$needed[$key]['date']]['extra'] = "show";
								}

								$block[$needed[$key]['date']]['case'] = "liferung case 2";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = "Lieferung";

								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
								break;

							case "3": //sonstige
								if($ch['amount'] < 0)
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
									$ch['amount'] = ($ch['amount'] * (-1));
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['add'] = "";
								}
								else
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
								}
								if($ch['userid'] == 0 || $ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								else
								{
									$block[$needed[$key]['date']]['extra'] = "show";
								}
								$block[$needed[$key]['date']]['case'] = "sonstige case 3";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = "Sonstiges";
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
								break;

							//MINUS OPERATION
							case "4":
								if($ch['type'] == "C")
								{ //Übergabe von
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$direction = "übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an";
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];



									if($ch['stid'] == 0)
									{//display transfer from user to user
										if($ch['userid'] != $member && $next_user != $member)
										{
											$block[$needed[$key]['date']]['extra'] = "hide";
										}
										else if($ch['userid'] == $member && $next_user != $member)
										{
											$block[$needed[$key]['date']]['give'] = "";
											$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$next_user];
											$block[$needed[$key]['date']]['add'] = $ch['amount'];
											$block[$needed[$key]['date']]['subs'] = "";
										}
										else if($ch['userid'] != $member && $next_user == $member)
										{
											$block[$needed[$key]['date']]['give'] = "";
											$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
											$block[$needed[$key]['date']]['add'] = "";
											$block[$needed[$key]['date']]['subs'] = $ch['amount'];
										}

										$block[$needed[$key]['date']]['case'] = "case 4 c1";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
										
										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": " . $doctorsarray[$next_user] . " " . $direction . " " . $doctorsarray[$ch['userid']] . "<br />";
									}
									else
									{// display transfer from group to user -> ubergabe an...
										if($ch['userid'] != $member)
										{
											$block[$needed[$key]['date']]['extra'] = "hide";
										}

										$block[$needed[$key]['date']]['case'] = " case 4 c2";
										$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
										$block[$needed[$key]['date']]['give'] = "Gruppe / Tresor";
										$block[$needed[$key]['date']]['recieve'] = "";
										$block[$needed[$key]['date']]['add'] = $ch['amount'];
										$block[$needed[$key]['date']]['subs'] = "";
										$block[$needed[$key]['date']]['totaltr'] = $totaltr;
										$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
										
										$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": Group " . $direction . "  " . $doctorsarray[$ch['userid']] . "<br />";
									}
								}
								if($ch['type'] == "CS")
								{
									$skippedid = array("0" => ($ch['stid'])); //set next id to be skipped
									if($ch['amount'] < 0)
									{
										$ch['amount'] = ($ch['amount'] * (-1));
									}
									$direction = "übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an";
									$next_user = $needed[$key]['sumary'][($ch['id'] + 1)]['userid'];

									if($ch['userid'] == 0 || $ch['userid'] != $member)
									{
										$block[$needed[$key]['date']]['extra'] = "hide";
									}
									else
									{
										$block[$needed[$key]['date']]['extra'] = "show";
									}


									$block[$needed[$key]['date']]['case'] = "case 4 cs1";
									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = "Gruppe / Tresor ";
									$block[$needed[$key]['date']]['add'] = "";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['totaltr'] = $totaltr;
									$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
									
									$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": " . $doctorsarray[$ch['userid']] . " " . $direction . " Gruppe<br />";
								}
								break;

							case "5":
								if($ch['userid'] == 0 || $ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$ch['amount'] = ($ch['amount'] * (-1));
								//	$name .=" case 5 cs1 sent to pacinet";
								$block[$needed[$key]['date']]['case'] = "case 5 cs1 sent to pacinet";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = "";
								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
								$block[$needed[$key]['date']]['add'] = "";
								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $doctorsarray[$ch['userid']] . "<br />";
								break;

							case "6": //sonstiges
								if($ch['amount'] < 0)
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> verringert durch";
									$ch['amount'] = ($ch['amount'] * (-1));
									$block[$needed[$key]['date']]['recieve'] = "Sonstiges";
									$block[$needed[$key]['date']]['subs'] = $ch['amount'];
									$block[$needed[$key]['date']]['add'] = "";
								}
								else
								{
									$direction = "bestand von <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> erhöht durch";
									$block[$needed[$key]['date']]['recieve'] = $doctorsarray[$ch['userid']];
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['add'] = $ch['amount'];
								}
								if($ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								else
								{
									$block[$needed[$key]['date']]['extra'] = "show";
								}
								$block[$needed[$key]['date']]['case'] = "case 6 sonstiges";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = "";
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $doctorsarray[$ch['userid']] . " " . $direction . " " . $ch['amount'] . "  " . $methodsarray[$ch['methodid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = $ch['sonstige_more'];
								break;
							case "7":
								if($ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$ch['amount'] = ($ch['amount'] * (-1));
								$block[$needed[$key]['date']]['case'] = "case 7 ";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['methode'] = $methodsarray[$ch['methodid']];
								$block[$needed[$key]['date']]['amount'] = $ch['amount'];
								$block[$needed[$key]['date']]['give'] = "";
								$block[$needed[$key]['date']]['recieve'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
								$block[$needed[$key]['date']]['add'] = "";
								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $doctorsarray[$ch['userid']] . "<br />";
								break;
							case "8":
								if($ch['userid'] == 0 || $ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								$ch['amount'] = ($ch['amount'] * (-1));
								//	$name .=" case 8 sent to be used to patient<br/>";
								$block[$needed[$key]['date']]['case'] = "case 8";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['recieve'] = "";
								$block[$needed[$key]['date']]['add'] = "";
								$block[$needed[$key]['date']]['subs'] = $ch['amount'];
								;
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an " . $doctorsarray[$ch['userid']] . "<br />";
								break;
							case "9":
								if($ch['userid'] == 0 || $ch['userid'] != $member)
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
								}
								//	$name .=" case 9 <br/>";
								$block[$needed[$key]['date']]['case'] = "case 9";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['recieve'] = "";
								$block[$needed[$key]['date']]['add'] = $ch['amount'];
								$block[$needed[$key]['date']]['subs'] = "";
								$block[$needed[$key]['date']]['totaltr'] = $totaltr;
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $patientsSelector[0][$ch['ipid']] . "</b> übergibt " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> an " . $doctorsarray[$ch['userid']] . "<br />";
								break;
							case "12":
							    if($ch['userid'] == 0 || $ch['userid'] != $member)
							    {
							        $block[$needed[$key]['date']]['extra'] = "hide";
							    } 
							    
								$block[$needed[$key]['date']]['case'] = "case 12 sent to user from patient";
								$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
								$block[$needed[$key]['date']]['give'] = $patientsSelector[0][$ch['ipid']];
								$block[$needed[$key]['date']]['recieve'] = '';
								$block[$needed[$key]['date']]['add'] = $ch['amount'];
								$block[$needed[$key]['date']]['subs'] = '';
								$block[$needed[$key]['date']]['totaltr'] = $totaltr; //
								$block[$needed[$key]['date']]['btm_number'] = $ch['btm_number'];         //ISPC-2768 Lore 05.01.2021
								
								$block[$needed[$key]['date']]['details'] = $methodsarray[$ch['methodid']] . ": <b>" . $doctorsarray[$ch['userid']] . "</b> erhält " . $ch['amount'] . " <b>" . $medisarray[$ch['medicationid']]['name'] . "</b> vom " . $patientsSelector[0][$ch['ipid']] . "<br />";
								$block[$needed[$key]['date']]['sonstige_more'] = '';

								break;

							default:
								if($ch['type'] == "N")
								{
									$block[$needed[$key]['date']]['extra'] = "hide";
									$block[$needed[$key]['date']]['case'] = "default medicamnt name";
									$block[$needed[$key]['date']]['date'] = date('d.m.Y H:i', strtotime($needed[$key]['date']));
									$block[$needed[$key]['date']]['methode'] = "";
									$block[$needed[$key]['date']]['amount'] = "";
									$block[$needed[$key]['date']]['give'] = "";
									$block[$needed[$key]['date']]['recieve'] = "";
									$block[$needed[$key]['date']]['medication'] = $medisarray[$ch['medicationid']]['name'];
									$block[$needed[$key]['date']]['add'] = "";
									$block[$needed[$key]['date']]['subs'] = "";
									$block[$needed[$key]['date']]['totaltr'] = "";
									$block[$needed[$key]['date']]['details'] = "";
								}
								break;
						}
					}
					else
					{
						$block[$needed[$key]['date']]['extra'] = "hide";
					}
				}


				foreach($block as $kh => $value)
				{
					$info[$kh]['extra'] = $value['extra'];
					if($info[$kh]['extra'] == "hide")
					{
						unset($block[$needed[$key]['date']]);
					}
				}
			}
			/* -------------------------------------------------------------------- */
			/* ------------------- User Data--------------------------------------- */
			/* -------------------------------------------------------------------- */
			$loguser = Doctrine::getTable('User')->find($member);
			if($loguser)
			{
				$loguserarray = $loguser->toArray();
				$this->view->user_name = $loguserarray['user_title'] . ' ' . $loguserarray['first_name'] . ', ' . $loguserarray['last_name'];
			}

			/* ---------------------------------------------------------------------- */
			/* ------------------- Client Data--------------------------------------- */
			/* ---------------------------------------------------------------------- */

			$clientdata = Pms_CommonData::getClientData($logininfo->clientid);
			$this->view->client_name = $clientdata[0]['client_name'];
			$this->view->client_city = $clientdata[0]['city'];
			$this->view->client_street = $clientdata[0]['street1'];
			$this->view->client_zip = $clientdata[0]['postcode'];
			$this->view->client_phone = $clientdata[0]['phone'];

			$this->view->member = $member;
			$headergr = Pms_Template::createTemplate($this->view, 'templates/btmmembermedicationheader.html');
			$footergr = Pms_Template::createTemplate($this->view, 'templates/btmmembermedicationfooter.html');
			$this->view->headergr = $headergr;
			$this->view->footergr = $footergr;


			//ispc 1864 - remove methodid 13 and add correction with red
			if ( ! empty($block)){
				
				$corrections_array = array();
				$row_id = array_column($block, 'row_id') ; 
				$mcbtmc = new MedicationClientBTMCorrection();
				
				$corrections = $mcbtmc->get_by_correction_id ($row_id ,$logininfo->clientid);
				foreach($corrections as $row) {
					$corrections_array[$row['correction_table']][$row['correction_id']] = $row;
				}

				
				$cnt = 0;
				
				$btmbuchhistory_lang = $this->view->translate('btmbuchhistory_lang');
				$correction_event_text_pdf = "<br>" . $btmbuchhistory_lang['correction_event_text_pdf'];
				
				foreach($block as $k => &$row) {
					
					
					
					if ( ! empty($corrections_array[ $row['table_name'] ][ $row['row_id'] ])) {

						$correction = $corrections_array[ $row['table_name'] ][ $row['row_id'] ];
												
						$correction_event_text_row = sprintf($correction_event_text_pdf, $doctorsarray[$correction['create_user']], date("d.m.Y H:i", strtotime($correction['create_date'])));
						
						
						if (trim($row['give']) != '' ) {
							$row['give'] .= $correction_event_text_row;
						}
						if (trim($row['recieve']) != '' ) {
							$row['recieve'] .= $correction_event_text_row;
						}
						
						if (trim($row['add']) != '' ) {
							$row['add'] = "<font color='red' style=\"color:#ff0000;\">" . $row['add'] . '->' . $correction['amount'] . "</font>";
						}
						if (trim($row['subs']) != '' ) {
							$row['subs'] = "<font color='red' style=\"color:#ff0000;\">" . $row['subs'] . '->' . $correction['amount'] . "</font>";
						}
						
						$row['details'] .= $correction_event_text_row;
					}
					
					
				}
				

			}

			$this->view->rowsnr = count($block);
			$memb = new Pms_Grid($block, 1, count($block), "btmmemberform.html");
			$this->view->btmform_member_grid = $memb->renderGrid();


			$form_teampdf = new Pms_Grid($block, 1, count($block), "btmmemberformpdf.html");
			$pdfgridtable['grid_member'] = $form_teampdf->renderGrid();

			if(strlen($_POST['pdf_member']) > 0)
			{
				$pdfgridtable['user_name'] = $this->view->user_name;
				$pdfgridtable['user_street'] = $this->view->user_street;
				$pdfgridtable['user_zip'] = $this->view->user_zip;
				$pdfgridtable['user_city'] = $this->view->user_city;

				$pdfgridtable['client_name'] = $this->view->client_name;
				$pdfgridtable['client_street'] = $this->view->client_street;
				$pdfgridtable['client_zip'] = $this->view->client_zip;
				$pdfgridtable['client_city'] = $this->view->client_city;

				$pdfgridtable['medication_name'] = $this->view->medication_name;

				$this->generateformPdf(4, $pdfgridtable, "BtmMemberMedication", "btmmembermedication.html");
			}
		}

		public function btmlistusersAction() //listuser
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('User', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}


			if($this->getRequest()->isPost())
			{

				$add_perms = $_POST['perms'];
				//delete previous perms
				$del = Doctrine_Query::create()
					->delete('BtmPermissions')
					->where('client = "' . $clientid . '"');
				$del->execute();

				foreach($add_perms as $k_userid => $v_user)
				{
					$add_perm = new BtmPermissions();
					$add_perm->user = $k_userid;
					$add_perm->client = $clientid;
					$add_perm->canadd = $v_user['can_add'];
					$add_perm->candelete = $v_user['can_del'];
					$add_perm->save();
				}
			}
		}

		public function btmfetchuserlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['id'] > 0)
			{
				$clientid = $_GET['id'];
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			$columnarray = array("pk" => "id", "un" => "username", "pwd" => "password", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray['ASC'];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('id != ?', $logininfo->userid)
				->andWhere('usertype != ?', 'SA')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);

			//echo $user->getSqlQuery();
			$userarray = $user->fetchArray();

			$limit = 50;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_GET['pgno'] * $limit);

			$usserlimit = $user->fetchArray();

			//get btm permissions
			$btm = new BtmPermissions();
			$client_users_perms = $btm->btmPermissionsByClient($clientid);
			$this->view->client_users_perms = $client_users_perms;

			$all_add = '1';
			$all_del = '1';

			foreach($usserlimit as $k_user => $v_user)
			{
				if(array_key_exists($v_user['id'], $client_users_perms))
				{
					if($client_users_perms[$v_user['id']]['canadd'] == '0')
					{
						$all_add = '0';
					}

					if($client_users_perms[$v_user['id']]['candelete'] == '0')
					{
						$all_del = '0';
					}
				}
				else
				{
					$all_add = '0';
					$all_del = '0';
				}
			}


			if($all_add == '0')
			{
				$a_add = '';
			}
			else
			{
				$a_add = 'checked="checked"';
			}

			if($all_del == '0')
			{
				$a_del = '';
			}
			else
			{
				$a_del = 'checked="checked"';
			}

			$this->view->all_add = $a_add;
			$this->view->all_del = $a_del;

			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "btmlistuser.html");
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("btmusernavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('medication/btmusersfetchlist.html');

			echo json_encode($response);
			exit;
		}

		private function generateformPdf($chk, $post, $pdfname, $filename)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientinfo = Pms_CommonData::getClientData($logininfo->clientid);
			$userid = $logininfo->userid;


			$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
			if($chk == 1)
			{
				$tmpstmp = time();
				//mkdir("uploads/" . $tmpstmp);
				mkdir(PDF_PATH. "/" . $tmpstmp);
// 				$pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
				$pdf->Output(PDF_PATH. '/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
				$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 				$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
// 				exec($cmd);
				$zipname = $tmpstmp . ".zip";
				$filename = "uploads/" . $tmpstmp . ".zip";
				/*
				$con_id = Pms_FtpFileupload::ftpconnect();
				if($con_id)
				{
					$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
					Pms_FtpFileupload::ftpconclose($con_id);
				}
				*/
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH. '/' . $tmpstmp . '/' . $pdfname . '.pdf', "uploads" );
				
			}
			if($chk == 2)
			{
				
				$pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
 				$pdf->setDefaults(false); 
				$pdf->SetMargins(10, 10, 10, true); //reset margins
				
				$pdf->SetFont('dejavusans', '', 10 , '', true);
				$pdf->setFontSubsetting(false);
				
				$pdf->setPrintFooter(false); // remove black line at bottom
				$pdf->SetAutoPageBreak(TRUE, 10);
				
				$pdf->setHTML($htmlform);
				
				ob_end_clean();
				ob_start();
				$pdf->Output($pdfname . '.pdf', 'D');
				exit;
			}

			if($chk == 3)
			{

				$navnames = array("SAPVf_anfrage" => 'SAPVF Anfrage', "Uberleitungsbogen" => 'Überleitungsbogen', "Verordnung" => ' Verordnung (Anlage 63)',
					"Palliativ_versorgung_a7" => 'Palliativ Versorgung a7',
					"folgeverordnung" => 'folgeverordnung',
					"Form_one" => 'SAPV-Einzelfallevaluation',
					"Form_two" => 'Statistische Angaben',
					"Anlage_4(Teil 1)" => 'Basisdokumentation (Anlage 4)',
					"formthree" => 'Bescheinigung Arzt-Palliativversorgung',
					"Stammblatt" => 'Stammblatt',
					"hopeform" => 'Basisbogen',
					"formfour" => 'SAPV-Einzelfallevaluation',
					"formfive" => 'Abschlussdokumentation',
					"SAPVF_B3" => 'Leistungserfassung',
					"SAPVF_B4" => 'Überleitung',
					"SAPVF_B5" => 'Wunddokumentation',
					"SAPVF_B12" => 'Mängeldokumentation',
					"SAPVF_B8" => 'Leistungsnachweis',
					"form1_wurzburg" => 'form1_wurzburg',
					"KVNO_doctor" => 'KVNO doctor form',
					"KVNO_nurse" => 'KVNO nurse form',
					"KVNO_anlage7" => 'KVNO anlage7 form',
					"MdkSchne" => 'MDK Schnellbegutachtungsbogen',
					"Ruhen" => 'Feststellung des Ruhens der Teilnahme',
					"Teilnahmeerklarung" => 'Teilnahmeerklärung ',
					"Feststellung" => 'Feststellung des Nichtvorliegens der Teilnahmevorraussetzung',
					"Notfallplan" => 'Notfallplan',
					"Behandlungsvertrag" => 'Behandlungsvertrag',
					"BTMBuchClientHistory" => "BTM Buch Client History");
				$pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
				$pdf->setDefaults(true); //defaults with header
				$pdf->setImageScale(1.6);
				$pdf->SetMargins(10, 5, 10); //reset margins
				$pdf->setPrintFooter(false);

				switch($pdfname)
				{

					case 'form1_wurzburg':
						$background_type = '11';
						break;
					case 'Notfallplan':
						$background_type = '13';
						break;

					default:
						$background_type = false;
						break;
				}

				$pdf->HeaderText = false;
				if($background_type != false)
				{
					$bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
					if($bg_image !== false)
					{
						$bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
						if(is_file($bg_image_path))
						{
							$pdf->setBackgroundImage($bg_image_path);
						}
					}
				}

				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
				$html = preg_replace('/style=\"(.*)\"/i', '', $html);

				$pdf->setHTML($html);
				
				$tmpstmp = $pdf->uniqfolder(PDF_PATH);
				
				$file_name_real = basename($tmpstmp);

				$pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');

				$_SESSION ['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 				$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;

// 				exec($cmd);
				$zipname = $file_name_real . ".zip";
				$filename = "uploads/" . $file_name_real . ".zip";



				/*
				$con_id = Pms_FtpFileupload::ftpconnect();
				if($con_id)
				{
					$upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
					Pms_FtpFileupload::ftpconclose($con_id);
				}
				*/
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf', "uploads" );
				

				$cust = new PatientFileUpload ();
				$cust->title = Pms_CommonData::aesEncrypt(addslashes($navnames [$pdfname]));
				$cust->ipid = $ipid;
				$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']); //$post['fileinfo']['filename']['name'];
				$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				$cust->system_generated = "1";
				$cust->save();

				if($pdfname == "MdkSchne")
				{
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
					$cust->tabname = Pms_CommonData::aesEncrypt("MdkSchne");
					$cust->user_id = $userid;
					$cust->save();
				}
				else
				{
					$cust = new PatientCourse ();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Formular ' . $navnames [$pdfname] . ' wurde erstellt'));
					$cust->user_id = $logininfo->userid;
					$cust->save();
				}




				ob_end_clean();
				ob_start();

				$pdf->toBrowser($pdfname . '.pdf');

				exit;
			}

			//copy of the if($chk == 4)
			if($chk == 4444)
			{
				//              echo $htmlform; exit;
				$pdf = new Pms_PDF('L', 'mm', 'letter');
				$pdf->setHTML($htmlform);
				$pdf->setDefaults(false); //defaults with header
				$pdf->setPrintFooter(false);
				ob_end_clean();
				ob_start();
				$pdf->Output($pdfname . '.pdf', 'D');
				exit;
			}
			
			//variation
			if($chk == 4)
			{

				$pdf = new Pms_PDF('L', 'mm', 'A4', true, 'UTF-8', false);
				
				$pdf->SetMargins(10, 5, 10, true); //reset margins
				
// 				$pdf->SetFont('dejavusans', '', 10 , '', true);				
				$pdf->setPrintFooter(false); // remove black line at bottom
				$pdf->SetAutoPageBreak(TRUE, 5);
				
				
				$pdf->setHTML($htmlform);
				
				
				ob_end_clean();
				ob_start();
				$pdf->Output($pdfname . '.pdf', 'D');
				exit;
			}
		}

		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{
								if($on == 'date')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'date')
						{
							$sortable_array[$k] = strtotime($v);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}

				switch($order)
				{
					case SORT_ASC:
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case SORT_DESC:
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
						break;
				}

				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}

		public function medipumpsoldAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$medp = new Medipumps();
			$client_medipumps = $medp->getMedipumps($clientid);

			$this->view->medipumps = $client_medipumps;
		}

		public function addmedipumpAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			if($this->getRequest()->isPost())
			{

				$medipump_form = new Application_Form_Medipumps();

				if($medipump_form->validate($_POST))
				{
					$medipump_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$medipump_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function editmedipumpAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->viewRenderer("addmedipump");

			if(!empty($_REQUEST['id']))
			{
				$medp = new Medipumps();
				$medipump = $medp->medipump_details($clientid, $_REQUEST['id']);

				$this->view->medipump = $medipump;
			}

			if($this->getRequest()->isPost())
			{

				$medipump_form = new Application_Form_Medipumps();

				if($medipump_form->validate($_POST))
				{
					if(!empty($_REQUEST['id']))
					{
						$medipump_form->UpdateData($_POST, $_REQUEST['id']);
					}
					else
					{
						$medipump_form->InsertData($_POST);
					}
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'medication/medipumps?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$medipump_form->assignErrorMessages();
				}
			}
		}

		public function deletemedipumpAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer('medipumps');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$thrash = Doctrine::getTable('Medipumps')->find($_REQUEST['id']);
			$thrash->isdelete = 1;
			$thrash->save();
			
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'medication/medipumps?flg=suc&mes='.urlencode($this->view->error_message));
		}

		public function addmedicationtreatmentcareAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->layout->setLayout('layout');
		
			if($this->getRequest()->isPost())
			{
		
				$user_form = new Application_Form_MedicationTreatmentCare();
		
				if($user_form->validate($_POST))
				{
					$user_form->InsertData($_POST);
		
					$fn = $_POST['name'];
					$curr_id = $user_form->id;
		
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$user_form->assignErrorMessages();
				}
			}
		}
		
		public function editmedicationtreatmentcareAction()
		{
			$this->view->formid = Pms_CommonData::getFormId('MedicationTreatmentCare');
			$this->view->act = "edit";;
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			$this->_helper->viewRenderer('addmedicationtreatmentcare');
		
			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_MedicationTreatmentCare();
		
				if($client_form->validate($_POST))
				{
					$client_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'medication/listtreatmentcare?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			if(strlen($_GET['id']) > 0)
			{
				$med = Doctrine::getTable('MedicationTreatmentCare')->find($_GET['id']);
				$this->retainValues($med->toArray());
 
			}
	 
		}
		
		
		

		public function deletemedicationtreatmentcareAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			//$this->_helper->viewRenderer('listtreatmentcare');
		
			$ids = split(",", $_GET['id']);
		
			for($i = 0; $i < sizeof($ids); $i++)
			{
			$thrash = Doctrine::getTable('MedicationTreatmentCare')->find($ids[$i]);
			$thrash->isdelete = 1;
			$thrash->save();
		
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'medication/listtreatmentcare?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}

		public function listtreatmentcareoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');
		
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				;
			}
		}
		
		
		public function fetchtreatmentcarelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('medication', $logininfo->userid, 'canview');
		
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
		
			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and clientid=0';
			}
		
			$columnarray = array("pk" => "id", "nm" => "name");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			
			
			$med = Doctrine_Query::create()
			->select('*')
			->from('MedicationTreatmentCare')
			->where('isdelete = 0 ' . $where)
			->andWhere("extra = 0")
			->andWhere('name!=""')
			->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$medarray = $med->fetchArray();

			$limit = 50;
			$med->select("*");
			$med->limit($limit);
			$med->offset($_GET['pgno'] * $limit);
		
			$medlimit = $med->fetchArray();
// 		echo $med->getSqlQuery(); exit;
		
// 			print_r($medarray); exit;
			
			$grid = new Pms_Grid($medlimit, 1, count($medarray), "listmedicationtreatmentcare.html");
			$this->view->treatmentcaregrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("medtrnavigation.html", 5, $_GET['pgno'], $limit);
		
			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['medicationtreatmentcarelist'] = $this->view->render('medication/fetchtreatmentcarelist.html');
		
			echo json_encode($response);
			exit;
		}
		

		public function btmstatusAction(){
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$this->view->clientid = $clientid;
			$this->view->userid = $userid;
				
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			 
			$btm_perms = new BtmGroupPermissions();
			$btm_permisions = $btm_perms->get_group_permissions($clientid, Usergroup::getMasterGroup($groupid));
			$this->view->lieferung_method = $btm_permisions['method_lieferung'];
			$this->view->btm_permisions = $btm_permisions;
			
			if($btm_permisions['use'] != '1' && $logininfo->usertype != 'SA')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
				
			$btmbuch = new MedicationClientHistory();
					
			if($logininfo->clientid != 0){
				
				if(strlen($_REQUEST['user']) > 0){
					$selected_user = $_REQUEST['user'];
				} else{
					$selected_user = "-1";
				}
				
					
				$this->view->selected_user = $selected_user;
				
				// get client data
				$clientdata = Pms_CommonData::getClientData($logininfo->clientid);
				$this->view->client_name = $clientdata[0]['client_name'];
				$this->view->client_team_name = $clientdata[0]['team_name'];
				$this->view->client_city = $clientdata[0]['city'];
				$this->view->client_street = $clientdata[0]['street1'];
				$this->view->client_zip = $clientdata[0]['postcode'];
				$this->view->client_phone = $clientdata[0]['phone'];
				
				//get only the users with group permission
				$btm_groups_permisions = $btm_perms->get_all_groups_permissions($clientid);
				
				$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('isdelete =  0')
				->andWhere('clientid = ' . $clientid); // get all client users -> 06.03.2012
				$grouparray = $group->fetchArray();
				
				$groups_array[] = "999999999999";
				$all_groups[] = "999999999999";
				
				foreach($grouparray as $group)
				{
					if($btm_groups_permisions[$group['groupmaster']]['use'] == '1')
					{
						$groups_array[] = $group['id'];
					}
					
					$all_groups[] = $group['id'];
				}
						
				$users = new User();
				$userarray = $users->getUserByClientid($clientid, '1', true);
				$all_user_array = $users->getUserByClientid($clientid, '0', true);
				
				
				$all_users[0]['name'] = "TRESOR";
				foreach($all_user_array as $keu => $user_data){
					$all_users[$user_data['id']] = $user_data;
					$all_users[$user_data['id']]['name'] = $user_data['title'].' '.$user_data['last_name'].', '.$user_data['first_name']; 
				}
				$this->view->all_user_array = $all_users;
				
				$this->view->userarray = $userarray;
				
				$groupusrs = Doctrine_Query::create()
				->select('*')
				->from('User')
				->whereIn('groupid', $groups_array)
				->andWhere('isdelete = 0')
				->andWhere('isactive = 0')
				->andWhere('clientid = ' . $clientid . '')
				->orderBy('last_name ASC');
				$groupusers = $groupusrs->fetchArray();

				$doctorusers[0]['fullname'] =  "TRESOR";
				$doctorusers[0]['username'] = "TRESOR";
				
				foreach($groupusers as $user)
				{
					$doctorusers[$user['id']]['fullname'] =  $user['user_title']." ".$user['last_name'].", ".$user['first_name'];
					$doctorusers[$user['id']]['username'] = $user['first_name'] . " " . $user['last_name'];
				}
				
				$this->view->btm_users = $doctorusers;
							

								
				$usersarray[] = "99999999";
						
				if(count($doctorusers) > '1')
				{
					$usersarray = array_merge($usersarray, array_keys($doctorusers));
					unset($usersarray[array_search('99999999', $usersarray)]);
				}
		
				if($selected_user && $selected_user != "-1"){
					$usersarray = array($selected_user);
				}
				//get stocks
				$stocks = new MedicationClientStock();
				$stksarray = $stocks->getAllMedicationClientStock($clientid);
						
				//prepare medis stocks array to get medis data by medis stocks id
				$medisstr = "'99999999999'";
				$comma = ",";
				foreach($stksarray as $stocmedis)
				{
					$stocmedications[$stocmedis['medicationid']] = $stocmedis;
					$medisstr .= $comma . "'" . $stocmedis['medicationid'] . "'";
					$comma = ",";
				}
								
				//get medication stocks medis id
				$med = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where('isdelete = 0 ')
				->andWhere('name!=""')
				->andWhere('id IN (' . $medisstr . ')')
				->andWhere('clientid = ' . $clientid . '');
				$medarray = $med->fetchArray();
						
				foreach($medarray as $medication)
				{
					if($medication['id'] == $stocmedications[$medication['id']]['medicationid'])
					{
						$medicationsarray[$medication['id']] = $medication;
						$medicationsarray[$medication['id']]['total'] = $stocmedications[$medication['id']]['total'];
					}
				}
				
								
				$btm = $btmbuch->getDataForUsers($clientid, $usersarray);
				foreach($btm as $record)
				{
					$btmuserdata[$record['userid']][$record['medicationid']] = $record;
				}
				
				foreach($doctorusers as $dockey => $docuser)
				{
					$doctors[$dockey]['fullname'] = $docuser['fullname'];
					$doctorsSelect[$dockey] = $docuser['fullname'];
				
					$doctorsSelect_iadd[$dockey] = $docuser['fullname'];
					$doctorsSelect_idel[$dockey] = $docuser['fullname'];
				}
								
				foreach($medicationsarray as $keym => $medication)
				{
					$final[$keym]['id'] = $medication['id'];
					$final[$keym]['name'] = $medication['name'];
					$final[$keym]['stock'] = $medication['amount'];
					
					foreach($usersarray as $keyu => $userid)
					{
						//exclude dummy control
						if($userid != '99999999')
						{
							if($userid == "0") // Gruppe / Tresor
							{
								$final_userdata['user'][$keym][$userid] = $medication['total'];
							} 
							elseif($userid == $btmuserdata[$userid][$keym]['userid'] && $medication['id'] == $btmuserdata[$userid][$keym]['medicationid'])
							{
								$final_userdata['user'][$keym][$userid] = $btmuserdata[$userid][$keym]['total'];
							} 
							else
							{
								$final_userdata['user'][$keym][$userid] = 0;
							}
						}
					}
					$medicationsarray[$keym]['users'] = $final_userdata['user'];
					$medicationsarray[$keym]['user_amount'] = $final_userdata['user'][$keym][$userid];
				}
				$this->view->user_medication = $medicationsarray;
				
				$history = ClientFileUpload::get_client_files_sorted( $logininfo->clientid, array ('btm_user_status') );
				
				if(strlen($_REQUEST['user']) > 0 ){
					foreach($history as $ke => $ve){
						if($ve['recordid'] !=$_REQUEST['user'] ){
							unset($history[$ke]);
						}
					}
				}
				$this->view->status_files = $history;
				
				
								
				if($this->getRequest()->isPost())
				{
					if(!empty($_POST['generate_pdf'])){
						
						$data['user_medication'] = $medicationsarray;
						$data['selected_user'] = $this->view->selected_user;
						$data['btm_users'] = $this->view->btm_users ;
						$data['client_team_name'] = $this->view->client_team_name;
						$data['clientid'] = $logininfo->clientid;
						$data['userid'] =  $this->view->selected_user;
						$data['generated_user'] =  $all_users[$logininfo->userid]['user_title'].' '.$all_users[$logininfo->userid]['last_name'].', '.$all_users[$logininfo->userid]['first_name'];
						
						$template_files = array('btmuserstatus.html');
						$orientation = array('P');
						$background_pages = false; //array('0') is first page;
						
						$this->generate_pdf($data, "BTMStatus", $template_files, $orientation,$background_pages);
						
					}
				}
			}
		}
		
		
		
		
		private function generate_pdf($post_data, $pdfname, $filename, $orientation = false, $background_pages = false, $mode = 'D')
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$pdf_names = array(
					'BTMStatus' => "BTMStatus"
			);

			
			$files_names = array(
					'BTMStatus' => "BTMStatus"
			);
			
			if(is_array($filename))
			{
				foreach($filename as $k_file => $v_file)
				{
					$htmlform[$k_file] = Pms_Template::createTemplate($post_data, 'templates/' . $v_file);
					$html[$k_file] = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform[$k_file]);
				}
			}
			else
			{
				$htmlform = Pms_Template::createTemplate($post_data, 'templates/' . $filename);
				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
			}
		
			$pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
			$pdf->setDefaults(true); //defaults with header
			$pdf->setImageScale(1.6);
			$pdf->SetMargins(6, 5, 10); //reset margins
			$pdf->setPrintFooter(false); // remove black line at bottom
			$pdf->SetAutoPageBreak(TRUE, 10);
		
			

			if($pdfname == 'BTMStatus')
			{
				$pdf->setPrintFooter(true);
				$generated_user= $post_data['generated_user'];
				$generated_date = date('d.m.Y H:i',time());
				$page_number_show = $pdf->getAliasNumPage();
				$pdf->footer_text = '<table width="100%" style="font-size:9pt;"><tr><td  style="text-align:left;">Dieses PDF wurde von '.$generated_user.' am '.$generated_date.' erstellt.</td></tr></table>';
			}
				
			
			
			
		
			//set page background for a defined page key in $background_pages array
			$bg_image = Pms_CommonData::getPdfBackground($post_data['clientid'], $pdf_type);
			if($bg_image !== false)
			{
				$bg_image_path = PDFBG_PATH . '/' . $post_data['clientid'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
				if(is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
			}
		
			if($_REQUEST['dbgpdf'])
			{
				print_r($html[0]);
				exit;
			}
		
			if(is_array($html))
			{
				foreach($html as $k_html => $v_html)
				{
					if(is_array($orientation))
					{
						if(is_array($background_pages))
						{
							if(!in_array($k_html, $background_pages))
							{
								//unset page background for a nondefined page key in $background_pages array
								$pdf->setBackgroundImage();
							}
						}
						//each page has it`s own orientation
						$pdf->setHTML($v_html, $orientation[$k_html]);
					}
					else
					{
						//all pages one custom orientation
						$pdf->setHTML($v_html, $orientation);
					}
				}
			}
			else
			{
				if(empty($background_pages) && is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
				$pdf->setHTML($html, $orientation);
			}
		
			$tmpstmp = $pdf->uniqfolder(CLIENTUPLOADS_PATH);
		
// 			die(print_r(func_get_args()));
			//$tmpstmp = substr(md5(time() . rand(0, 999)), 0, 12);
			//mkdir('clientuploads/' . $tmpstmp);
				
			if($pdfname == 'teammeeting' || $pdfname == 'teammeetingpdfview' || $pdfname == 'teammeetingpdfuserview')
			{
				$filepdf = str_replace(" ","_", $files_names[$pdfname]);
// 				str_replace("uploads", "clientuploads", PDF_PATH);
// 				$pdf->toFile(str_replace("uploads", "clientuploads", PDF_PATH) . '/' . $tmpstmp . '/' . $filepdf . '.pdf');
				$pdf->toFile(CLIENTUPLOADS_PATH . '/' . $tmpstmp . '/' . $filepdf . '.pdf');
		
				$_SESSION['filename'] = $tmpstmp . '/' . $filepdf . '.pdf';
				
		
			}
			else
			{
// 				str_replace("uploads", "clientuploads", PDF_PATH);
// 				$pdf->toFile(str_replace("uploads", "clientuploads", PDF_PATH) . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
				$pdf->toFile(CLIENTUPLOADS_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
				$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
				
			}
		
		
			//$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 			$cmd = "zip -9 -r -P " . $logininfo->filepass . " clientuploads/" . $tmpstmp . ".zip clientuploads/" . $tmpstmp . ";";
// 			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "clientuploads/" . $tmpstmp . ".zip";
			/*
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}
			*/
			$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (CLIENTUPLOADS_PATH. '/' . $_SESSION['filename'] , "clientuploads" );
				
		
 
			$tabname = 'btm_user_status';
			$cust = new ClientFileUpload();
			$cust->title = Pms_CommonData::aesEncrypt(addslashes($pdf_names[$pdfname]));
			$cust->clientid = $post_data['clientid'];
			$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']);
			$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
			$cust->tabname = $tabname;
			$cust->recordid = $post_data['userid'];
			$cust->save();

			ob_end_clean();
			ob_start();
		
			if($mode == "D")
			{
				//download file
				if($pdfname == 'teammeeting' || $pdfname == 'teammeetingpdfview' || $pdfname == 'teammeetingpdfuserview'){
					$filepdf = str_replace(" ","_", $files_names[$pdfname]);
					$pdf->toBrowser($filepdf . '.pdf', "D");
					exit;
				}
				else
				{
					//download file
					$pdf->toBrowser($pdfname . '.pdf', "D");
					exit;
				}
			}
			else if($mode == "F")
			{
				//to file without bugging the user to download the file
				//				$pdf->toFTP($pdfname . 'pdf');
			}
			else
			{
				//default (send to browser)
				$pdf->toBrowser($pdfname . '.pdf', "D");
				exit;
			}
		}
		
		public function btmstatusfileAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();
		
			if($_GET['delid'] > 0)
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$upload_form = new Application_Form_ClientFileUpload();
				$upload_form->deleteFile($_GET['delid'], 'btm_user_status');
		
				if($_REQUEST['user'])
				{
					$this->redirect(APP_BASE . 'medication/btmstatus?user=' . $_REQUEST['user']);
				}
				else
				{
					$this->redirect(APP_BASE . 'medication/btmstatus');
				}
				exit;
			}
		
			if($_REQUEST['doc_id'] > 0)
			{
				$patient = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
				AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
								->from('ClientFileUpload')
								->where('id = ?', $_REQUEST['doc_id'])
								->andWhere('clientid = ?', $clientid)
								->andWhere('tabname = ?', $_REQUEST['tab']);
				$flarr = $patient->fetchArray();
		
				if($flarr)
				{
					$explo = explode("/", $flarr[0]['file_name']);
		
					$fdname = $explo[0];
					$flname = utf8_decode($explo[1]);
				}
// 		die(print_r($flarr));
				if(!empty($flarr))
				{
					$old = $_REQUEST['old'] ? true : false;
						
					$path = Pms_CommonData::ftp_download('clientuploads/' . $fdname . '.zip' , $logininfo->filepass, $old, null, $flarr[0]['file_name'], "ClientFileUpload", $flarr[0]['id'] );
					if ($path === false){
						//failed to download file
						//die($path);
					}
					
					
					
// 					// if no doc_id - in db
// 					$con_id = Pms_FtpFileupload::ftpconnect();
		
// 					if($con_id)
// 					{
// 						$upload = Pms_FtpFileupload::filedownload($con_id, 'clientuploads/' . $fdname . '.zip', 'clientuploads/' . $fdname . '.zip');
// 						Pms_FtpFileupload::ftpconclose($con_id);
// 					}
		
// 					$file_password = $logininfo->filepass;
		
// 					$cmd = "unzip -P " . $file_password . " clientuploads/" . $fdname . ".zip;";
// 					exec($cmd);
					
		
// 					$pre = '';
// 					$path = $_SERVER['DOCUMENT_ROOT'] . $pre . "/clientuploads/" . $fdname . "/"; // change the path to fit your websites document structure
					$fullPath = $path ."/". $flname;
// 					die($fullPath);
// 					if($fd = fopen($fullPath, "r"))
					if(file_exists($fullPath))
					{
						$fsize = filesize($fullPath);
						$path_parts = pathinfo($fullPath);
		
						$ext = strtolower($path_parts["extension"]);
						ob_end_clean();
						ob_start();
						switch($ext)
						{
							case "pdf":
								header('Content-Description: File Transfer');
								header("Content-type: application/pdf"); // add here more headers for diff. extensions
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Pragma: public');
								if($_COOKIE['mobile_ver'] != 'yes')
								{
									//if on mobile version don't send content-disposition to play nice with iPad
									header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
								}
								break;
							default;
							header('Content-Description: File Transfer');
							header("Content-type: application/octet-stream");
							header('Content-Transfer-Encoding: binary');
							header('Expires: 0');
							header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
							header('Pragma: public');
							if($_COOKIE['mobile_ver'] != 'yes')
							{
								//if on mobile version don't send content-disposition to play nice with iPad
								header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
							}
						}
						header("Content-length: $fsize");
						header("Cache-control: private"); //use this to open files directly
						@readfile($fullPath);
// 						fclose($fd);
						unlink($fullPath);
					}
					
					exit;
				}
			}
		}

		/**
		 * 2017.04.25
		 * 
		 * ispc-1864 p.9
		 * 
		 * add a SEAL function. 
		 * the "BTM Verantwortliche" of that client can press a seal button to SEAL all BTM entries BEFORE a set date.
		 * this means that documenting a method BEFORE that date is not possible.
		 */
		public function btmsealAction()
		{
			
			
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			//get btm notification users
			//only this "BTM Verantwortliche" users are allowed to acces this page
			$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');
			if ( (empty($btm_notification_users) || ! in_array($logininfo->userid, array_column($btm_notification_users, 'user')))
					&&  $logininfo->usertype != 'SA') 
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			$this->view->has_edit_permissions = $has_edit_permissions;			
			
			//POST user adds a new seal_date, or deletes one
			if( $this->getRequest()->isPost() && $has_edit_permissions)
			{
				$mcss_form = new Application_Form_MedicationClientStockSeal();
				
				//add new seal
				if ( ! empty($_POST['new_seal_date']) && $_POST['new_seal_date'] == "1" ) {
					
					if ( $mcss_form->validate($_POST) ) {			
						$mcss_form->InsertData($_POST);
					}
					else
					{
						$mcss_form->assignErrorMessages();
						$this->retainValues($_POST);
					}
				
				} 
				//delete an old one
				elseif ( ! empty($_POST['delete_seal']) && $_POST['delete_seal'] == "1" &&  ! empty($_POST['id'])) {
					
					if ( ! $mcss_form->DeleteData($_POST)) {
						$mcss_form->assignErrorMessages();
					}
				}
			}//end POST
			
			//view
			$mcss  = new MedicationClientStockSeal();
			$mcss_history = $mcss->get_client_history($clientid);
		
			$highest_seal_date = $mcss->get_default_seal_timestamp(); // var used so you don't add a seal_date that is allready ? so add +1 day to it if ==
			
			$user_names_array = array();
			//get user names for those that have sealed
			if( ! empty($mcss_history) && is_array($mcss_history)) {
				
				$user_id_arr = array_column($mcss_history, 'create_user');
				$user_id_arr = array_unique($user_id_arr);
			
				$user_names_array = User::getUsersNiceName($user_id_arr, $clientid);

				
				foreach ($mcss_history as &$row) {
					
					$row['seal_by_user'] = $user_names_array [ $row['create_user'] ] ['nice_name'] ;
					$row['seal_date'] =  date("d.m.Y" , strtotime($row['seal_date']));
					
					if ($highest_seal_date < strtotime($row['seal_date'])) {
						$highest_seal_date = strtotime($row['seal_date']);
					}
				}
			}		
			$this->view->highest_seal_date = $highest_seal_date;
			$this->view->history = $mcss_history;
			
		}
		
		//get view list medication
		public function listmedicationAction(){
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
						"0" => "name",
						"1" => "manufacturer"
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
				$fdoc1->from('Medication');
				$fdoc1->where("clientid = ?", $clientid);
				$fdoc1->andWhere("isdelete = 0 ");
				$fdoc1->andWhere("extra = 0");
				$fdoc1->andWhere("name!=''");
		
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
					//$fdoc1->andWhere("(lower(name) like ? or lower(manufacturer) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
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
					$resulted_data[$row_id]['manufacturer'] = sprintf($link,$mdata['manufacturer']);
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/editmedication?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
		
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
		
				$this->_helper->json->sendJson($response);
			}
		
		}
		
		//get view list bedarfsmedication
		public function bedarfsmediclistAction(){
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
						"0" => "title"
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
				$fdoc1->from('BedarfsmedicationMaster');
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
					//$fdoc1->andWhere("(lower(title) like ?)", array("%" . trim($search_value) . "%"));
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
					$resulted_data[$row_id]['title'] = sprintf($link,$mdata['title']);
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/bedarfsmedicationedit?bid='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
		
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
		
				$this->_helper->json->sendJson($response);
			}
		
		}
		
		public function bedarfsmedicationdeleteAction(){
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			$thrash = Doctrine::getTable('BedarfsmedicationMaster')->find($_GET['id']);
			$thrash->isdelete = 1;
			$thrash->save();
			
			//$this->_helper->viewRenderer('bedarfsmediclist');
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'medication/bedarfsmediclist?flg=suc&mes='.urlencode($this->view->error_message));
	}
	
	//get view list treatment care
	public function listtreatmentcareAction(){
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
			$fdoc1->from('MedicationTreatmentCare');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			$fdoc1->andWhere("extra = 0");
			$fdoc1->andWhere("name!=''");
	
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
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/editmedicationtreatmentcare?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	//get view list medipumps
	public function medipumpsAction(){
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
					"0" => "shortcut",
					"1" => "medipump"
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
			$fdoc1->from('Medipumps');
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
				//$fdoc1->andWhere("(lower(medipump) like ? or lower(shortcut) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['shortcut'] = sprintf($link,$mdata['shortcut']);
				$resulted_data[$row_id]['medipump'] = sprintf($link,$mdata['medipump']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/editmedipump?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	//get view list medication receipt
	public function listreceiptmedicationAction(){
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
					"0" => "name",
					"1" => "manufacturer"
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
			$fdoc1->from('MedicationReceipt');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			$fdoc1->andWhere("extra = 0");
			$fdoc1->andWhere("name!=''");
	
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
				//$fdoc1->andWhere("(lower(name) like ? or lower(manufacturer) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['manufacturer'] = sprintf($link,$mdata['manufacturer']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/editreceiptmedication?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	//get view list medication nutrition
	public function listmedicationnutritionAction(){
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
					"0" => "name",
					"1" => "manufacturer"
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
			$fdoc1->from('Nutrition');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			$fdoc1->andWhere("extra = 0");
			$fdoc1->andWhere("name!=''");
	
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
				//$fdoc1->andWhere("(lower(name) like ? or lower(manufacturer) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['manufacturer'] = sprintf($link,$mdata['manufacturer']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/editmedicationnutrition?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	

	//get view list new sets crisismedication and bedarfsmedication - ISPC-2247
	public function medicationssetslistAction(){
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $this->logininfo->clientid;
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		if($_GET['action']=='delete')
		{
			$entity = Doctrine::getTable('MedicationsSetsList')->find($_GET['id']);
			$entity->delete();
			$this->_redirect(APP_BASE . "medication/medicationssetslist");
		}
		
		$medsets = new MedicationsSetsItems();
		$medsetsitems = $medsets->get_medications_group_by_set();
		
		$set_entries = array();
		foreach($medsetsitems as $medset)
		{
			$set_entries[$medset['bid']] = $medset['entries'];
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
					"0" => "title",
					"1" => 'entries'
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
			$fdoc1->from('MedicationsSetsList');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
				
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*');
	
			if($order_column == "0")
			{
				$fdoc1->orderBy($order_by_str);
			}
			
			$fdoclimit = $fdoc1->fetchArray();
			
			foreach ($fdoclimit as $key=> $row) {
				$row['entries'] = $set_entries[$row['id']];
				$fdoclimit[$key] = $row;
			}
			
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
				$resulted_data[$row_id]['title'] = sprintf($link,$mdata['title']);
				$resulted_data[$row_id]['entries'] = sprintf($link,$mdata['entries']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'medication/editmedicationsset?bid='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	// medications sets add - ISPC-2247
	public function editmedicationssetAction()
	{
		$userid = $this->logininfo->userid;
		$clientid = $this->logininfo->clientid;
		$this->view->clientid = $clientid;
		
		$links = new Links();
		$has_edit_permissions = $links->checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		//TODO-3365 Carmen 21.08.2020
	    //get client settings for pharmaindex values got from mmi
	    $client_details = Client::getClientDataByid($clientid);
	    
	    if( ! empty($client_details)){
	    	if($client_details[0]['pharmaindex_settings'])
	    	{
	    		$this->view->js_pharmaindex_settings = json_encode($client_details[0]['pharmaindex_settings']);
	    	}
    		else
    		{
    			$this->view->js_pharmaindex_settings = json_encode(array(
    					'atc' => 'yes',
    					'drug' => 'yes',
    					'dosage_form' => 'no',
    					'unit' => 'no',
    					'takinghint' => 'no',
    					'type' => 'no'
    			));
    		}
	    }		    
	    //--
		
		if($_GET['bid'])
		{
			$setid = $_GET['bid']; 
		}
		//CLIENT MODULES
		$modules =  new Modules();
		$clientModules = $modules->get_client_modules($clientid);
		$this->view->clientModules = $clientModules;
		
		/* MMI functionality*/
		if($clientModules['87'])
		{
			$this->view->show_mmi = "1";
		}
		else
		{
			$this->view->show_mmi = "0";
		}
		//PATIENT MEDICATION FORM
		$patient_medication_form = new Application_Form_Medication();
		
		// ISPC-2247 pct.1 Lore 30.04.2020 
		/* ================ PATIENT TIME SCHEME ======================= */
		$individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		if($individual_medication_time_m){
		    $individual_medication_time = 1;
		}else {
		    $individual_medication_time = 0;
		}
		$this->view->individual_medication_time = $individual_medication_time;
		
		//get get saved data
		$medication_blocks = array("actual","isbedarfs");
		if($individual_medication_time == "0"){
		    $client_time_scheme_old = MedicationIntervals::client_saved_medication_intervals($clientid,array("all"));
		} else {
		    $client_time_scheme_old = MedicationIntervals::client_saved_medication_intervals($clientid,$medication_blocks);
		}
		$client_time_scheme = array();
		foreach($client_time_scheme_old as $keyo => $valo){
		    foreach($valo as $newval){
		        $client_time_scheme[$keyo][] = $newval;
		    }
		}
		$this->view->intervals = $client_time_scheme;
		
		//get time scchedule options
		$client_med_options = MedicationOptions::client_saved_medication_options($clientid);
		$this->view->client_medication_options = $client_med_options;
		$sets_time_scheme = array();
		foreach($client_time_scheme as $key_cts => $val_cts){
		    if($client_med_options[$key_cts]['time_schedule'] == 1){
		        $sets_time_scheme[$key_cts] = $val_cts;
		    }
		}
 		//.
		
		//INDICATIONS
		$medication_indications_array = array();
		$medication_indications_array = MedicationIndications::client_medication_indications($clientid);
	    $medication_indications[] = "";
		foreach($medication_indications_array as $k=>$indication){
		    $medication_indications[$indication['id']] = $indication['indication'];
// 		    $medication_indications[$indication['id']]['name'] = $indication['indication'];
// 		    $medication_indications[$indication['id']]['color'] = $indication['indication_color'];
		}
		
		//UNIT
		$medication_unit = MedicationUnit::client_medication_unit($clientid);
		
		foreach($medication_unit as $k=>$unit){
			$client_medication_extra['unit'][$unit['id']] = $unit['unit'];
		}
		$this->view->js_med_unit = json_encode($client_medication_extra['unit']);
		$this->view->js_clientunit = json_encode($medication_unit); //ISPC-2554 Carmen 11.05.2020
		
		
		//DOSAGE FORM
		$medication_dosage_array = array();
		$meddos = new MedicationDosageform();
		$medication_dosage_array = $meddos->client_medication_dosage_form($clientid, true);
		$this->view->js_clientdosageform = json_encode($medication_dosage_array);
		
		$medication_dosage = array();
		$medication_dosage_js = array();
		$cl_dosageform = array();
		//Lore 06.05.2020 fara modulul de MMI crapa la linia 9326: 		$all_dosageform = $cl_dosageform + $mmi_dosage_custom;
		$mmi_dosage_custom = array();
		$mmi_codes = array();
		foreach($medication_dosage_array as $kdf=>$data_fd){
			//$medication_dosage[$data_fd['id']] = $data_fd; ISPC-2554
			if($data_fd['extra'] == 0)
			{
				$medication_dosage_js[$data_fd['id']] = $data_fd['dosage_form'];
			}
			
		    //ISPC-2554 pct.1 Carmen 06.04.2020
		    if($clientModules['87'])
		    {
		    	if($data_fd['isfrommmi'] == 1)
		    	{
		    		$mmi_codes[$kdf] = $data_fd['mmi_code'];
		    		$mmi_dosage_custom[$data_fd['id']] = $data_fd['dosage_form'];
		    	}
		    	else 
		    	{
		    		$medication_dosage[$data_fd['id']] = $data_fd;
		    	}
		    }
		    else 
		    {
		    	$medication_dosage[$data_fd['id']] = $data_fd;
		    	
		    	if($data_fd['isfrommmi'] == 1)
		    	{
		    		$mmi_dosage_custom[$data_fd['id']] = $data_fd['dosage_form'];
		    	} 
		    }
		    //--
		}
		
		//ISPC-2554 pct.1 Carmen 07.04.2020
		if(!$modules->checkModulePrivileges("87", $clientid))
		{
			$cl_dosageform = $medication_dosage_js;
			$all_dosageform = $cl_dosageform + $mmi_dosage_custom;
			$medication_dosage_js = $all_dosageform;
		}
		//--
		
		//ISPC-2554 pct.1 Carmen 06.04.2020
		$medication_dosageform_mmi_all = array();
		$medication_dosageform_mmi = array();
		
		if($clientModules['87'])
		{
			$medication_dosagefrom_mmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();
			if(!empty($medication_dosagefrom_mmi))
			{
				foreach($medication_dosagefrom_mmi as $kr => $vr)
				{
					if(in_array($vr['dosageform_code'], $mmi_codes))
					{
						unset($medication_dosagefrom_mmi[$kr]);
					}
					else
					{
						$medication_dosageform_mmi['mmi_'.$vr['dosageform_code']] = $vr['dosageform_name'];
					}
				}
				$medication_dosageform_mmi_all = $mmi_dosage_custom + $medication_dosageform_mmi;
			}
		
			asort($medication_dosageform_mmi_all);
		}
		foreach($medication_dosageform_mmi_all as $kr => $vr)
		{
			$medication_dosageform_mmi_all_forjs[] = array($kr, $vr);
		}
		//--
		
		//FREQUENCY FORM
		$medication_frequency_array = array();
		$medfreq = new MedicationFrequency();
		$medication_frequency_array = $medfreq->client_medication_frequency($clientid, true);
		
		$medication_frequency = array();
		$medication_frequency_js = array();
		foreach($medication_frequency_array as $k=>$fdata)
		{
            $medication_frequency[$fdata['id']]= $fdata; 

            if($fdata['extra'] == 0)
            {
                $medication_frequency_js[$fdata['id']] = $fdata['frequency'];
            }
		}
 
		//TYPE FORM
		$medication_types_array = array();
		$medtyp = new MedicationType();
		$medication_types_array = $medtyp->client_medication_types($clientid, true);
		
		$medication_types = array();
		$medication_types_js = array();
		
		foreach($medication_types_array as $kmt => $data_mt)
		{
		    $medication_types[$data_mt['id']]= $data_mt; 

            if($data_mt['extra'] == 0)
            {
                $medication_types_js[$data_mt['id']] = $data_mt['type'];
            }
		}
		
		$countmedfreq = count($medication_frequency_js);
		$countmeddosage = count($medication_dosage_js)+count($medication_dosageform_mmi_all); //ISPC-2554 pct.1 Carmen 06.04.2020
		$countmedtypes = count($medication_types_js);
		$textarearows = ($countmeddosage >= $countmedfreq) ? $countmeddosage : $countmedfreq;
		$textarearows = ($textarearows >= $countmedtypes) ? $textarearows : $countmedtypes;
		if($textarearows == 0)
		{
			$textarearows = 3;
		}
		$textarearows += 7;
		$divheight  = $textarearows*24;
		
		$this->view->textarearows = $textarearows;
		$this->view->newsetheight = $divheight;
		$this->view->medication_dosage = json_encode($medication_dosage_js);
		$this->view->medication_dosageform_mmi = json_encode($medication_dosageform_mmi_all_forjs); //ISPC-2554 pct.1 Carmen 06.04.2020
		$this->view->medication_frequency = json_encode($medication_frequency_js);
		$this->view->medication_types = json_encode($medication_types_js);
		$this->view->sets_intervals = json_encode($sets_time_scheme);         //ISPC-2247 pct.1 Lore 06.05.2020
		
		//print_r($this->view->medication_frequency); exit;
		$form = new Application_Form_MedicationsSetsItems(array(
				'_block_name'            => 'MEDICATIONSSETSITEMS',
				'_client_modules'		 => $clientModules,
				'_medication_dosage'	 => $medication_dosage,
				'_medication_frequency'  => $medication_frequency,
				'_medication_types'      => $medication_types,
				'_medication_indications'=> $medication_indications,
				'_medication_dosageform_mmi' => $medication_dosageform_mmi_all, //ISPC-2554 pct.1 Carmen 06.04.2020
		        '_sets_time_scheme' => $sets_time_scheme,         //ISPC-2247 pct.1 Lore 06.05.2020
		));
		
		//last saved values
		$saved_values = $this->_medssets_GatherDetails($setid);
		
		//ISPC-2247 pct.1 Lore 05.05.2020
		//dd($saved_values,$client_time_scheme,$client_med_options); exit;		
		if($client_med_options[$saved_values['med_type']]['time_schedule'] == 1 ){

		    $sets_values_dosage_arr = MedicationsSetsItemsDosage::get_dosage_by_set($setid);
		    
		    if(!empty($sets_values_dosage_arr)){
		        $dosage_sets_medication = array();
		        $schema_sets_medication = array();
		        
		        foreach($sets_values_dosage_arr as $key => $vals)
		        {
		            $dosage_sets_medication[$vals['medication_id']][] = $vals['dosage'];
		            $schema_sets_medication[$vals['medication_id']][] = $vals['dosage_time_interval'];
		        }
		        
		        foreach($saved_values['data'] as $keys => $valss ){
		            $saved_values['data'][$keys]['dosage']['value'] = $dosage_sets_medication[$valss['medication_id']['value']];
		            $saved_values['data'][$keys]['dosage']['schema'] = $schema_sets_medication[$valss['medication_id']['value']];
		        }
		        
		    } else {
		        
		        foreach($saved_values['data'] as $key => $vals ){
		            
		            $find_minus = strpos($vals['dosage']['value'][0],"-");
		            if( !($find_minus === false) ){
		                
		                $dsg_vals = explode("-",$vals['dosage']['value'][0]);
		                
		                if(count($dsg_vals) <= count($client_time_scheme[$saved_values['med_type']])){
		                    //  create array from old
		                    for($x = 0; $x < count($client_time_scheme[$saved_values['med_type']]); $x++)
		                    {
		                        $saved_values['data'][$key]['dosage']['value'][$x] = $dsg_vals[$x];
		                    }
		                }
		                else
		                {
		                    for($x = 0; $x < count($client_time_scheme[$saved_values['med_type']]); $x++)
		                    {
		                        $saved_values['data'][$key]['dosage']['value'][$x] = $dsg_vals[$x];
		                    }
		                }
		            }
		            else
		            {
		                $saved_values['data'][$key]['dosage']['value'][0] = "! ALTE DOSIERUNG!";
		                $saved_values['data'][$key]['dosage']['value'][1] = $vals['dosage']['value'][0];
		                
		                for($x = 2; $x < count($client_time_scheme[$saved_values['med_type']]); $x++)
		                {
		                    $saved_values['data'][$key]['dosage']['value'][$x] = " ";
		                }
		            }
		            
		            $saved_values['data'][$key]['dosage']['schema'] = $client_time_scheme[$saved_values['med_type']] ;
		            
		        }
		    }
		}
		//.
		
		//dd($saved_values['data']);
		//$saved_values = array();
		$form->create_form_medicationssetsitems($saved_values);
		
		$request = $this->getRequest();
		
		if ( ! $request->isPost()) {
		
			//TODO move to populate
			//$form->populate($options);
		
		
		} elseif ($request->isPost()) {
		
			$post = $request->getPost();
			
 			//dd($post);
			
			$form->populate($post);
		
			if ( $form->isValid($post)) // no validation is implemented
			{
		
				if($_POST['formular']['button_action'] == "save")
				{
					//print_r($post); exit;
					//$post_form['userid'] = $this->logininfo->userid;
					foreach($post['set'] as $ks=>&$vs)
					{
						if($vs['hidd_medication'] == '' && $vs['medication'] != '' && $vs['id'] != '')
						{
							$post['set']['newmedication'][$ks] = $vs['medication'];
						}
						
						if($vs['hidd_medication'] != '' && $vs['medication'] != '' && $vs['id'] == '')
						{
							$post['set']['newmedication'][$ks] = $vs['medication'];
						}
						
						if($vs['hidd_medication'] == '' && $vs['medication'] != '' && $vs['id'] == '')
						{
							$post['set']['newmedication'][$ks] = $vs['medication'];
						}
						if($vs['source'] == '')
						{
							unset($vs['source']);
						}
						
						// ISPC-2247 pct.1 Lore 30.04.2020
						if($client_med_options[$post['med_type']]['time_schedule'] == 1 ){
						    $post['set'][$ks]['dosage_arr'] = $vs['dosage'];
						    if(isset($schema_sets_medication) && !empty($schema_sets_medication[$vs['hidd_medication']]) ){
						        $post['set'][$ks]['dosage_arr_schema'] = $schema_sets_medication[$vs['hidd_medication']];
						    }else {
						        $post['set'][$ks]['dosage_arr_schema'] = $client_time_scheme[$post['med_type']];
						    }
						}

						if(max(array_keys($vs['dosage'])) > 0){
						    $ds_colected ='';
						    foreach($vs['dosage'] as $key_ds=> $val_ds){
						        if($val_ds == '! ALTE DOSIERUNG!'){
						            $val_ds = "-";
						        }
						        $ds_colected .= $val_ds;
						        if(!(max(array_keys($vs['dosage'])) == $key_ds) ){
						            $ds_colected .= "-";
						        }
						    }
						    $post['set'][$ks]['dosage'] = array();
						    $post['set'][$ks]['dosage'][0] = $ds_colected;
						}
						//.
					}
					if(!empty($post['set']['newmedication']))
					{
						$dts = $patient_medication_form->InsertNewData($post['set']);
						
						foreach($dts as $key => $dt)
						{
							$post['newhidd_medication'][$key] = $dt->id;
						}
					}
					unset($post['set']['newmedication']);
					//dd($post); exit;
										
					$medset  = $form->save_form_medicationset($post);
					//dd($medset);
					
					// ISPC-2247 pct.1 Lore 06.05.2020
					if($client_med_options[$post['med_type']]['time_schedule'] == 1 ){
					    if(empty($post['bid'])){       // new sets dont have bid_id
					        $post['bid'] = $medset;
					    }
					    $medsetdosage  = MedicationsSetsItemsDosage::save_form_medication_dosage_set($post);
					}
					//.
		
// 					$this->_redirect(APP_BASE . "medication/editmedicationsset?bid=" . $post['bid']);
					$this->_redirect(APP_BASE . "medication/medicationssetslist");
				}
					
		
			} else {
		
		
				$form->populate($post);
		
			}
		
		}
		
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
		
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
		
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
		
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
		
			
		
		}
		
		private function _medssets_GatherDetails( $setid = null)
		{
			$entity  = new MedicationsSetsItems();
			$saved_final = array();
			
			$saved_values = $entity->get_medications_by_set($setid);
			//dd($saved_values); exit;
			
			
			$med_ids = array();
			foreach($saved_values as $k_med => $v_med)
			{
				$medications_array[] = $v_med;
				//$med_details[$v_med['medication_id']] = $v_med;
				//$med_details[$v_med['medication_id']]['medication_id'] = $v_med['medication_id'];
			
				$med_ids[] = $v_med['medication_id'];
			}
			
			// get medication details
			$med = new Medication();
			// 		    $master_medication_array = $med->master_medications_get($med_ids, false); //= only names are fetched here
			$master_medication_array = $med->getMedicationById($med_ids, true);			
			
			if($setid)
			{
				$entity_set = new MedicationsSetsList();
				$saved_set = $entity_set->findById($setid);
				
 				//dd($saved_values);
				
				foreach($saved_values as $krow=>$vrow)
				{
					
					foreach($vrow as $kcol=>$vcol)
					{
						$saved_final['data'][$krow][$kcol]['colprop'] = $entity->getTable()->getColumnDefinition($kcol);
						$saved_final['data'][$krow][$kcol]['value'] = $vcol;
					}

					$saved_final['data'][$krow]['medication']['value'] = $master_medication_array[$vrow['medication_id']]['name'];
					$saved_final['data'][$krow]['MedicationMaster']['colprop'] = 'hidden';
					$saved_final['data'][$krow]['MedicationMaster']['value'] = $master_medication_array[$vrow['medication_id']];
				}
			}
			else
			{				
				$saved_values= $entity->getTable()->getFieldNames();
					
				foreach($saved_values as $kcol=>$vcol)
				{
					$saved_final['data']['0'][$vcol]['colprop'] = $entity->getTable()->getColumnDefinition($vcol);
					$saved_final['data']['0'][$vcol]['value'] = null;
				}
					
			}
		
			$saved_final['bid'] = $setid;
			$saved_final['clientid'] = $saved_set[0]['clientid'];
			$saved_final['title'] = $saved_set[0]['title'];
			$saved_final['med_type'] = $saved_set[0]['med_type'];
			
			$saved_final['set_indication'] = $saved_set[0]['set_indication'];
		//print_r($saved_final); exit;
			return $saved_final;
		}
		
}

?>