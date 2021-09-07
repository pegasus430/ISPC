<?php

	class LocationsController extends Zend_Controller_Action {

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

		public function addlocationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$locations_form = new Application_Form_Locations();
				if($locations_form->validate($_POST))
				{
					$locations_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate('recordinsertsucessfully');
					//$this->_helper->viewRenderer('locationslist');
				}
				else
				{
					$locations_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}


			$pl = new Locations();
			$this->view->loctype = $pl->getLocationTypes();
		}

		public function locationslistoldAction()
		{
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$showinfo = new Modules();

			$user2location_m = $showinfo->checkModulePrivileges("94", $logininfo->clientid);
			if($user2location_m)
			{
				$user2location = 1;
			} else{
				$user2location = 0;
			}
				
				
			
			$columnarray = array("pk" => "l__0", "location" => "location", "lo" => "location");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

			$location = Doctrine_Query::create()
				->select('*, count(*),CONVERT(AES_DECRYPT(location,"' . Zend_Registry::get('salt') . '") using latin1)  as location ')
				->from('Locations')
				->where('isdelete = 0')
				->andWhere("client_id='" . $clientid . "'");

			$locationexec = $location->execute();
			$locationarray = $locationexec->toArray();
			$locationAll = Doctrine_Query::create()
				->select('*, CONVERT(AES_DECRYPT(location,"' . Zend_Registry::get('salt') . '") using latin1)  as location ')
				->from('Locations')
				->where('isdelete = 0')
				->andWhere("client_id='" . $clientid . "'");
			$locAll = $locationAll->fetchArray();
			if($_REQUEST['source'] == "addressbook")
			{
				$limit = 10000;
			}
			else
			{
				$limit = 50;
			}

			$location->select("*,CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)  as location");
			//ISPC-2708,elena,07.01.2021
			if(isset($_REQUEST['letter'])){
				$location->andWhere("CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1) like '".   ($_REQUEST['letter']) . "%'");
			}


			$location->limit($limit);
			$location->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$location->offset($_GET['pgno'] * $limit);
			$locationlimitexec = $location->execute();
			$locationlimit = $locationlimitexec->toArray();

			foreach($locAll as $location)
			{
				$allLocations[$location['id']] = $location;
				$total_locations[] = $location['id'];
			}

			$this->view->allLocations = $allLocations;



			$tpl = new PatientLocation();
			$assigned_patient_location = $tpl->getLocationsAssignedToPatients($total_locations);

			foreach($assigned_patient_location as $location_ass)
			{
				$total_assigned_locations[] = $location_ass['location_id'];
			}

			$this->view->locations_with_patients = $total_assigned_locations;

			$lc = new Locations();
			$location_types_array = $lc->getLocationTypes();


			if($_REQUEST['source'] == "addressbook")
			{
				$grid = new Pms_Grid($locationlimit, 1, $locationarray[0]['count'], "listlocationsaddressbook.html");
			}
			elseif($_REQUEST['source'] == 'brief')
			{
				$grid = new Pms_Grid($locationlimit, 1, $locationarray[0]['count'], "listlocationsbrief.html");
			}
			else
			{
				$grid = new Pms_Grid($locationlimit, 1, $locationarray[0]['count'], "listlocations.html");
			}
			$grid->location_types_array = $location_types_array;
			$grid->user2location = $user2location;
			$this->view->locationgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("locationnavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			if($_REQUEST['source'] == "addressbook" || $_REQUEST['source'] == "brief")
			{
				//ISPC-2708,elena,11.01.2021
				if($_REQUEST['source'] == "brief" && empty($locationlimit)){
					$this->view->showMessageForEmpty = true;
				}
				$response['callBackParameters']['patientlist'] = $this->view->render('locations/fetchlist.html');
			}
			else
			{
				$response['callBackParameters']['locationlist'] = $this->view->render('locations/fetchlist.html');
			}

			echo json_encode($response);
			exit;
		}

		public function fetchuserlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;


			$locs = new UsersLocations();
			$locations = $locs->getUserLocations($clientid);
			if(count($locations) == 0)
			{
				$locations[0]['first_name'] = " ";
			}

			if($_REQUEST['source'] == 'brief')
			{
				$grid = new Pms_Grid($locations, 1, count($locations), "listuserlocationsaddressbookbrief.html");
			}
			else
			{
				$grid = new Pms_Grid($locations, 1, count($locations), "listuserlocationsaddressbook.html");
			}

			$this->view->userlocationgrid = $grid->renderGrid();

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['patientlist'] = $this->view->render('locations/fetchuserlist.html');

			echo json_encode($response);
			exit;
		}

		public function editlocationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer('addlocations');
			$clientid = $logininfo->clientid;
			$this->view->location_id = $_GET['id'];

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				$location_form = new Application_Form_Locations();
				if($location_form->validate($_POST))
				{

					if($_POST['has_stations'] != '1')
					{
						$del_stat = Doctrine_Query::create()
							->update('LocationsStations')
							->set('isdelete', '1')
							->where('location_id = ?', $_REQUEST['id'])
							->andWhere('client_id = ?', $clientid );
						$rows = $del_stat->execute();
					}

					$location_form->UpdateData($_POST);
					//$this->_helper->viewRenderer('locationslist');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'locations/locationslist?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$location_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}


			$cust = Doctrine_Query::create()
				->select("l.*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations l')
				->where('id = ?',$_GET['id'] );
			$cust->getSqlQuery();
			$custexec = $cust->execute();

			$pl = new Locations();
			$this->view->loctype = $pl->getLocationTypes();

			if($custexec)
			{
				$locarray = $custexec->toArray();
				$this->retainValues($locarray[0]);
			}

			$ls = new LocationsStations();
			$lsarr = $ls->getLocationsStationsByLocation($logininfo->clientid, $_GET['id']);


			$location_stations[] = '999999999';
			foreach($lsarr as $k_loc_stat => $v_loc_stat)
			{
				$location_stations[] = $v_loc_stat['id'];
			}


			$pl = new PatientLocation();
			$assigned_patient_location_stations = $pl->getPatientLocationsByStations($location_stations);


			foreach($assigned_patient_location_stations as $k_pat_loc_assigned_station => $v_pat_loc_assigned_station)
			{
				$patient_station_assigned[] = $v_pat_loc_assigned_station['ipids'];
			}
			$patient_station_assigned = array_values(array_unique($patient_station_assigned));


			if(count($patient_station_assigned) > 0)
			{
				$this->view->disable_stations = 0; //dont allow to disable sations
			}
			else
			{
				$this->view->disable_stations = 1;
			}

			if(!empty($lsarr))
			{
				$this->view->has_stations = 1;
			}
			else
			{
				$this->view->has_stations = 0;
			}

			$this->view->stations_array = $lsarr;
		}

		public function locationdeleteAction()
		{
			$this->_helper->viewRenderer('locationslist');
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{

				if(count($_POST['locationid']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 1;
				}
				if($error == 0)
				{
					foreach($_POST['locationid'] as $key => $val)
					{
						$mod = Doctrine::getTable('Locations')->find($val);
						$mod->isdelete = 1;
						$mod->save();
					}
					$this->view->error_message = $this->view->translate("recorddeletedsuccessfully");
				}
			}
		}
		
		public function deletelocationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('locations', $logininfo->userid, 'candelete');
		
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
				
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$delids = $_POST['delids'];
			if($delids == "")
			{
				$this->view->error_message = $this->view->translate('selectatleastone');
				$this->_redirect(APP_BASE . 'locations/locationslist?mes='.urlencode($this->view->error_message));
				//$this->_helper->viewRenderer('locationslist');
			}
			else 
			{
				$delids = explode('|', $delids);
				if(count($delids) > 1)
				{
					$this->view->error_message = $this->view->translate("recordsdeletedsucessfully");
				}
				else
				{
					$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				}
				foreach($delids as $delid)
				{
					$fdoc = Doctrine::getTable('Locations')->find($delid);
					
					$fdoc->isdelete = 1;
					$fdoc->save();					
				}
				
				$this->_redirect(APP_BASE . 'locations/locationslist?flg=suc&mes='.urlencode($this->view->error_message));
				//$this->_helper->viewRenderer('locationslist');
			}
		}

		public function getlocationtypeAction()
		{
			$start = (float) array_sum(explode(' ', microtime()));

			if(strlen($_REQUEST['lid']) > 0)
			{
				$ls = new Locations();
				$locarr = $ls->getLocationbyId($_REQUEST['lid']);

				$ltype = $locarr[0]['location_type'];
				$lid = $locarr[0]['id'];
				$phone = $locarr[0]['phone1'];

				$lt = $ls->getLocationTypes();
				$loctype = $lt[$ltype];
			}

			if(strlen($_REQUEST['patlid']) > 0)
			{
				$ls = new Locations();
				$locarr = $ls->getLocationByPatLocId($_REQUEST['patlid']);

				$ltype = $locarr[0]['location_type'];
				$lid = $locarr[0]['id'];
				$phone = $locarr[0]['phone1'];

				$lt = $ls->getLocationTypes();
				$loctype = $lt[$ltype];
			}

			$end = (float) array_sum(explode(' ', microtime()));

			if($_REQUEST['mod'] != "phone")
			{
				$response['msg'] = "Success";
				$response['error'] = "";
				$response['timeing'] = "Processing time: " . sprintf("%.4f", ($end - $start)) . " seconds";
				$response['callBack'] = "LocBack";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['location_type'] = $ltype;
			}
			else
			{
				$response['msg'] = "Success";
				$response['error'] = "";
				$response['timeing'] = "Processing time: " . sprintf("%.4f", ($end - $start)) . " seconds";
				$response['callBack'] = "LocBack";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['phone'] = $phone;
			}
			echo json_encode($response);
			exit;
		}

		public function getlocationstationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$start = (float) array_sum(explode(' ', microtime()));

			if(strlen($_REQUEST['lid']) > 0)
			{
				$ls = new LocationsStations();
				$lsarr = $ls->getLocationsStationsByLocation($clientid, $_REQUEST['lid']);

				if($lsarr && !empty($lsarr))
				{
					$has_stations = "1";
					$array_stations = $lsarr;
				}
				else
				{
					$has_stations = "0";
				}
			}

			if(!empty($array_stations) && count($array_stations) != 0)
			{
				$response['array_stations'] = $array_stations;
			}
			else
			{
				$response['array_stations'] = '0';
			}


			echo json_encode($response);
			exit;
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{

				$this->view->$key = $val;
			}
		}

		public function addstation2locationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$ls = new LocationsStations();
			$lsarr = $ls->getLocationsStationsByLocation($clientid, $_REQUEST['loc_id']);


			$this->view->location_stations_array = $lsarr;

			if($this->getRequest()->isPost())
			{
				$stations_form = new Application_Form_LocationsStations();
				if($stations_form->validate($_POST))
				{
					$stations_form->InsertData($_POST);
					$this->_redirect(APP_BASE . 'locations/stationslist?loc_id=' . $_REQUEST['loc_id'] . '&flg=suc');
				}
				else
				{
					$stations_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function editstationsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			$this->_helper->viewRenderer('addstation2location');
			$this->view->location_id = $_REQUEST['loc_id'];
			$this->view->station_id = $_REQUEST['st_id'];

			if(!empty($_REQUEST['st_id']))
			{
				$ls = new LocationsStations();
				$lsarr = $ls->getLocationsStationsById($clientid, $_REQUEST['loc_id'], $_REQUEST['st_id']);

				if($lsarr)
				{
					$this->retainValues($lsarr[0]);
				}
			}

			if($this->getRequest()->isPost())
			{
				$location_form = new Application_Form_LocationsStations();
				if($location_form->validate($_POST))
				{
					$location_form->UpdateData($_POST);

					$this->_redirect(APP_BASE . 'locations/stationslist?loc_id=' . $_REQUEST['loc_id'] . '&flg=suc');
				}
				else
				{
					$location_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function stationslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->view->error_class = "";

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_class = "success";
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
			else if($_REQUEST['flg'] == 'aerr')
			{
				$this->view->error_class = "err";
				$this->view->error_message = $this->view->translate('stationisassignedtopatient');
			}

			$ls = new LocationsStations();
			$lsarr = $ls->getLocationsStationsByLocation($clientid, $_REQUEST['loc_id']);

			$this->view->location_stations_array = $lsarr;
		}

		public function deletestationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$previleges = new Pms_Acl_Assertion();
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			$this->_helper->viewRenderer('stationslist');

			$pl = new PatientLocation();
			$pateint_locations_stations = $pl->getPatientLocationsByStations($_REQUEST['sid']);


			if($pateint_locations_stations)
			{
				//redirect error
				$this->_redirect(APP_BASE . 'locations/stationslist?loc_id=' . $_REQUEST['loc_id'] . '&flg=aerr');
			}
			else
			{
				//delete
				$del_stat = Doctrine_Query::create()
					->update('LocationsStations')
					->set('isdelete', '1')
					->where('id = ?', $_REQUEST['sid'] )
					->andWhere('client_id = ?', $clientid);
				$rows = $del_stat->execute();

				$this->_redirect(APP_BASE . 'locations/stationslist?loc_id=' . $_REQUEST['loc_id'] . '&flg=suc');
			}
		}

		
		public function users2locationAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('users2location', $logininfo->userid, 'canview');

			if(!$return || empty($_GET['id']))
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
				
			if($_GET['id']){
				$this->view->location_id = $_GET['id']; 
			
				$location_data = Locations:: getLocationbyId($_GET['id']);
				$this->view->location_data = $location_data[0];
// 		print_r($location_data[0]); exit;
			}
		}

		public function fetchlocationuserslistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('user', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$clientid = $logininfo->clientid;

			
			
			// get alredy assigend users to location
			$users2location_array = Users2Location::get_location_users($_GET['location_id']);
				
			$team_leader = "";
			foreach($users2location_array as $k=>$vul){
				$users2location[] = $vul['user'];
				if($vul['leader'] == "1"){
					$team_leader = $vul['user'];
				}
			}
			

 			$location_id = $_GET['location_id'];
 			$this->view->location_id =$location_id;
			
			$columnarray = array("pk" => "id", "un" => "username", "ut" => "user_title", "pwd" => "password", "fn" => "first_name", "ln" => "last_name");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$this->view->order = $orderarray['ASC'];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$user = Doctrine_Query::create()
				->select('count(*)')
				->from('User')
				->where('isdelete = 0')
				->andWhere('isactive = 0')
				->andWhere('clientid = ?', $clientid)
				->andWhere('usertype != ?', 'SA')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$userarray = $user->fetchArray();

			$limit = 50;
			$user->select('*');
			$user->limit($limit);
			$user->offset($_GET['pgno'] * $limit);
			$usserlimit = $user->fetchArray();

			//get user groups
			$ug = new Usergroup();
			$grouparr = $ug->get_clients_groups($clientid);
			
			foreach($grouparr as $k_gr => $v_gr)
			{
				$groups_arr[$v_gr['id']] = $v_gr;
			}

			
			$grid = new Pms_Grid($usserlimit, 1, $userarray[0]['count'], "listusers2location.html");
			$grid->location_id= $location_id;
			$grid->users2location= $users2location;
			$grid->team_leader= $team_leader;
			$grid->usergroups = $groups_arr;
			$this->view->usergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("locationusernavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['userlist'] = $this->view->render('locations/fetchlocationuserslist.html');

			echo json_encode($response);
			exit;
		}
		
		//get view list locations
		public function locationslistAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
				$showinfo = new Modules();
				
				$user2location_m = $showinfo->checkModulePrivileges("94", $logininfo->clientid);
				if($user2location_m)
				{
					$user2location = 1;
				} else{
					$user2location = 0;
				}

				$lc = new Locations();
				$location_types_array = $lc->getLocationTypes();
				
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "25";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
		
				$columns_array = array(
						"1" => "location_name",
						"2" => "location_type_name"		
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
				$fdoc1->from('Locations');
				$fdoc1->where("client_id = ?", $clientid);
				$fdoc1->andWhere("isdelete = 0  ");				
				
				$fdocarray = $fdoc1->fetchArray();
				$full_count  = $fdocarray[0]['count'];				
					
				// ########################################
				// #####  Query for details ###############
				$fdoc1->select('*, CONVERT(AES_DECRYPT(location,"' . Zend_Registry::get('salt') . '") using latin1)  as location_name ');
				
				if($order_column == "1")
				{
					$fdoc1->orderBy($order_by_str);
				}
				
				$fdoclimit = $fdoc1->fetchArray();
				
				foreach ($fdoclimit as $key=> $row) {
					$row['location_type_name'] = $location_types_array[$row['location_type']];
					$fdoclimit[$key] = $row;
					$total_locations[] = $row['id'];
				}
				
				$tpl = new PatientLocation();
				$assigned_patient_location = $tpl->getLocationsAssignedToPatients($total_locations);
				
				foreach($assigned_patient_location as $location_ass)
				{
					$total_assigned_locations[] = $location_ass['location_id'];
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
			
				if($order_column == "2")
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
						$resulted_data[$row_id]['checkloc'] = '<input class="checkloc" name="checkloc[]" id="'.$mdata['id'].'" type="checkbox" del="1" rel="'.$mdata['id'].'" value=""  />';
						$resulted_data[$row_id]['location'] = sprintf($link,'<span>!</span>'.$mdata['location_name']);
					}
					else
					{
						$resulted_data[$row_id]['checkloc'] = '<input class="checkloc" name="checkloc[]" id="'.$mdata['id'].'" type="checkbox" del="0" rel="'.$mdata['id'].'" value=""  />';
						$resulted_data[$row_id]['location'] = sprintf($link,$mdata['location_name']);
					}
				
					$resulted_data[$row_id]['location_type'] = sprintf($link,$mdata['location_type_name']);
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'locations/editlocations?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
					
					if($user2location == 1)
					{	
							$resulted_data[$row_id]['actions'] .= '<a href="'.APP_BASE .'locations/users2location?id='.$mdata['id'].'">'.$this->view->translate('location_users_icon').'</a>';
					}
					$row_id++;
				}
		
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
		
				$this->_helper->json->sendJson($response);
			}
		
		}
		
		//get view list hospital reasons
		public function listhospitalreasonsAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
				$ploc = new PatientLocation();
				$hrattached = $ploc->get_patient_hospreasons_attached();
				
				$total_assigned_hr = array();
					
				foreach($hrattached as $kdm=>$vdm)
				{
					$total_assigned_hr[] = $vdm['reason'];
				}
				
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "25";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
		
				$columns_array = array(
						"0" => "reason"
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
				$fdoc1->from('HospitalReasons');
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
					if(in_array($mdata['id'], $total_assigned_hr))
					{
						$resulted_data[$row_id]['name'] = sprintf($link,'<span>!</span>'.$mdata['reason']);
						$ask_on_del = '1';
					}
					else
					{
						$resulted_data[$row_id]['name'] = sprintf($link,$mdata['reason']);
						$ask_on_del = '0';
					}
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'locations/edithospitalreason?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="'.$ask_on_del.'" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
		
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
		
				$this->_helper->json->sendJson($response);
			}
		
		}
		
	public function addhospitalreasonAction()
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

				$hospreasons_form = new Application_Form_HospitalReasons();

				if($hospreasons_form->validate($_POST))
				{
					$hospreasons_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$hospreasons_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}
		
		public function edithospitalreasonAction()
		{		
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			$this->_helper->viewRenderer('addhospitalreason');
		
			if($this->getRequest()->isPost())
			{
				$hospreason_form = new Application_Form_HospitalReasons();
		
				if($hospreason_form->validate($_POST))
				{
					$hospreason_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'locations/listhospitalreasons?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$client_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			if(strlen($_GET['id']) > 0)
			{
				$hospreason = Doctrine::getTable('HospitalReasons')->find($_GET['id']);
				$this->retainValues($hospreason->toArray());
 
			}
	 
		}
		
		public function deletehospitalreasonAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$thrash = Doctrine::getTable('HospitalReasons')->find($_REQUEST['id']);
			$thrash->isdelete = 1;
			$thrash->save();
			
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			$this->_redirect(APP_BASE . 'locations/listhospitalreasons?flg=suc&mes='.urlencode($this->view->error_message));
		}

		/**
		 * Create and Edit clinic-beds. //Maria:: Migration CISPC to ISPC 22.07.2020
		 */
		public function bedlistAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			if ($this->getRequest()->isPost()) {
				$locs=$_POST['room'];



				$clinicBed = new ClinicBed();
				$locAll = $clinicBed->getAllBeds($clientid);

				foreach($locAll as $loc){
					if(! in_array($loc['id'], $locs['rowid']) || $loc['bed_name']==""){
						//delete the bed
						$del_stat = Doctrine_Query::create()
							->update('ClinicBed')
							->set('isdelete','1')
							->where('id = "'. $loc['id'] .'"')
							->andWhere('client_id = "'.$clientid.'"');
						$rows = $del_stat->execute();

						//delete the Patient-Mapping, if existing
						$del_map = Doctrine_Query::create()
							->select('id')
							->from('PatientClinicBed')
							->where('bed_id = ?', $loc['id'])
							->andWhere("valid_till= 0")
							->andWhere('clientid = "' . $clientid . '"');
						$rows = $del_map->fetchArray();

						foreach ($rows as $row) {
							$pr = Doctrine::getTable('PatientClinicBed')->findOneBy('id', $row['id']);
							$pr->valid_till = date('Y-m-d H:i:s');
							$pr->isdelete = '1';
							$pr->save();
						}
					}
				}

				foreach ($locs['bed_name'] as $r=>$locn){
					if($locs['rowid'][$r]<1 && $locn!=""){
						$iconname = $this->generateBedIcon($locs['bed_kuerzel'][$r], $clientid);
						$new=new ClinicBed();
						$new->bed_name=Pms_CommonData::aesEncrypt($locn);
						$new->bed_kuerzel=$locs['bed_kuerzel'][$r];
						$new->client_id=$clientid;
						$new->icon_name = $iconname;
						$new->save();
					}
				}

			}

			$clinicBed = new ClinicBed();
			$allbeds = $clinicBed->getAllBeds($clientid);
			$this->view->rooms= $allbeds;
		}

		/**
		 * Generate an icon with the token of a clinic-bed.
		 *
		 * This icons are shown in the list of patient-icons, so the user can see,
		 * in which bed the patient is assigend to.
		 *
		 * The icon ist generated during a clinic-bed is created.
		 *
		 * In the system-icons there is a "master-icon" called "bed.svg".
		 *
		 * When creating a bed, a new icon is generated with the given
		 * token of the bed.
		 * Maria:: Migration CISPC to ISPC 22.07.2020
		 * The icons are saved in ./icons_system/client-id/bedicons/ 
		 */
		private function generateBedIcon($bed_name, $clientid){
			$dirname = 'icons_system/' . $clientid . '/bedicons';
			$iconname = 'bed' . $bed_name . '.svg';

			$svg_file = file_get_contents('icons_system/bed.svg');

			$newsvg_file = str_replace('###', $bed_name, $svg_file);

			if (!file_exists($dirname)) {
				mkdir($dirname, 0777, true);
			}

			file_put_contents($dirname . '/' . $iconname, $newsvg_file);

			return $iconname;
		}

	}

?>
