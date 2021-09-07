<?php

class PatientpermissionsController extends Zend_Controller_Action {

	public function init() {
	}

	public function assignAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'User', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
		$getkeyuser = PatientUsers::getPatientKeyUser ( $ptarray [0] ['ipid'] );
		$allowforall = PatientUsers::checkAllowforallPatient($ipid);


		if ($checkcreateuser === true && ! $getkeyuser) {
			$createuser = true;
		} else {
			$createuser = false;
		}
		$this->view->createuser = $createuser;

		if (!$return || !$_REQUEST['id'] || (!$keyuser && !$createuser)) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		if ($_GET ['flg'] == 'suc') {
			$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
		}

		$this->view->act = 'patientpermissions/assign?id='.$_REQUEST['id'];

		if($this->getRequest()->isPost()){
			if($createuser !== true) {
				$usersperm = $_POST['user_id'];
			} else {
				$usersperm[] = $_POST['keyuser'];
			}
			foreach($usersperm as $user_id){
				$perm = new PatientUsers();
				$perm->clientid = $logininfo->clientid;
				$perm->userid = $user_id;
				$perm->ipid = $ipid;
				if($user_id == $_POST['keyuser']) {
					$perm->iskeyuser = 1;
				}
				$perm->create_date = date("Y-m-d H:i:s",time());
				$perm->save();
			}
			if($createuser === true) {
				$this->_redirect ( APP_BASE . "patientnew/patientdetails?id=".$_GET['id'] );
			} else {
				$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
			}
		}

		/*-----------------------  show group list  -----------------------*/
		$group = Doctrine_Query::create()
		->select('*')
		->from('GroupMaster')
		->where('id !=  1 and id != 2')
		->orderBy('groupname');
		$groupexec = $group->execute();
		$grouparray = $groupexec->toArray();

		//get existing groups in PatientGroups(which have visibility)
		$existing_groups = PatientGroups::getPatientGroups($ipid);

// 		dd($existing_groups);
		
		//get existing groups in PatientGroupPermissions (which have permissions)
		foreach ( $grouparray as $group ) {
			if(array_key_exists($group['id'], $existing_groups) || $allowforall === true){
				$group_all [$group ['id']] = $group;
				$group_all [$group ['id']] ['creategroup'] = $creategroup;

				//TO DO: here we set what group has permissions, right now we have if it has visibility
				if (! empty ( $existing_groups [$group ['id']] ) || $allowforall === true) {
					$group_all [$group ['id']] ['assigned'] = 1;
				} else {
					$group_all [$group ['id']] ['assigned'] = 0;
				}
			}
		}

		$grid = new Pms_Grid ($group_all, 1, $group_all, "listgroupsassign.html" );
		$this->view->groupgridvisibility = $grid->renderGrid ();
		/*-----------------------  show user list  -----------------------*/
		$user = Doctrine_Query::create ()
		->select ( '*' )
		->from ( 'User' )
		->where ( 'isdelete = 0' )
		->andWhere ( 'clientid = ?', $clientid )
		->andWhere ( 'id != "'. $logininfo->userid.'"')
		->orderBy ( 'username' );
		$userexec = $user->execute ();
		$userarray = $userexec->toArray ();


		$existing_users = PatientUsers::getPatientUsers($ipid);

		foreach ( $userarray as $user ) {
			if(array_key_exists($user['id'], $existing_users) || $allowforall === true){
				$user_all [$user ['id']] = $user;
				$user_all [$user ['id']] ['createuser'] = $createuser;

				//TO DO: here we set what group has permissions, right now we have if it has visibility
				if (! empty ( $existing_users [$user ['id']] ) || $allowforall === true) {
					$user_all [$user ['id']] ['assigned'] = 1;
				} else {
					$user_all [$user ['id']] ['assigned'] = 0;
				}
			}
		}

		$grid = new Pms_Grid ( $user_all, 1, $user_all, "listuserassign.html" );
		$this->view->usergridvisibility = $grid->renderGrid ();
	}

	public function fetchlistAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);

		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'user', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
		$getkeyuser = PatientUsers::getPatientKeyUser ( $ptarray [0] ['ipid'] );
		if ($checkcreateuser === true && ! $getkeyuser) {
			$createuser = true;
		} else {
			$createuser = false;
		}
		$this->view->createuser = $createuser;

		if (!$return || !$_REQUEST['id'] || (!$keyuser && !$createuser)) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		if ($_GET ['id'] > 0) {
			$clientid = $_GET ['id'];
		} else {
			$clientid = $logininfo->clientid;
		}

		$columnarray = array ("pk" => "id", "un" => "username", "pwd" => "password", "fn" => "first_name", "ln" => "last_name" );

		$orderarray = array ("ASC" => "DESC", "DESC" => "ASC" );
		$this->view->order = $orderarray ['ASC'];
		$this->view->{$_GET ['clm'] . "order"} = $orderarray [$_GET ['ord']];
		$user = Doctrine_Query::create ()
		->select ( 'count(*)' )
		->from ( 'User' )
		->where ( 'isdelete = 0' )
		->andWhere ( 'clientid = ?', $clientid )
		->andWhere ( 'id != ?', $logininfo->userid )
		->orderBy ( $columnarray [$_GET ['clm']] . " " . $_GET ['ord'] );
		$userexec = $user->execute ();
		$userarray = $userexec->toArray ();

		$limit = 50;
		$user->select ( '*' );
		$user->limit ( $limit );
		$user->offset ( $_GET ['pgno'] * $limit );

		$userlimitexec = $user->execute ();
		$usserlimit = $userlimitexec->toArray ();

		$existing_users = PatientUsers::getPatientUsers($ipid);
		if(empty($existing_users)){
			$allowforall = PatientUsers::checkAllowforallPatient($ipid);
		}

		foreach ( $usserlimit as $user ) {
			$user_all [$user ['id']] = $user;
			$user_all [$user ['id']] ['createuser'] = $createuser;
			if (! empty ( $existing_users [$user ['id']] ) || $allowforall === true) {
				$user_all [$user ['id']] ['assigned'] = 1;
			} else {
				$user_all [$user ['id']] ['assigned'] = 0;
			}
			if ($existing_users [$user ['id']] ['iskeyuser'] == '1') {
				$user_all [$user ['id']] ['keyuser'] = 1;
			} else {
				$user_all [$user ['id']] ['keyuser'] = 0;
			}
		}
		$grid = new Pms_Grid ( $user_all, 1, $userarray [0] ['count'], "listuserperm.html" );
		$this->view->usergrid = $grid->renderGrid ();

		$response ['msg'] = "Success";
		$response ['error'] = "";
		$response ['callBack'] = "callBack";
		$response ['callBackParameters'] = array ();
		$response ['callBackParameters'] ['userlist'] = $this->view->render ( 'patientpermissions/fetchlist.html' );
		echo json_encode ( $response );
		exit ();
	}


	private function getmodulehierarchy(&$a_mod,$parentid,$space)
	{
		$folder = Doctrine_Query::create()
		->select('*')
		->from('TabMenus')
		->where('parent_id='.$parentid)
		->andWhere('isdelete = ?',0)
		->orderBy("sortorder ASC");

		$folderexec = $folder->execute();
		$folderarray = $folderexec->toArray();
		foreach($folderarray as $key=>$val)
		{
			array_push($a_mod,array('space'=>$space,'menu_title'=>$val['menu_title'],'menu_link'=>$val['menu_link'],'id'=>$val['id']));
			$this->getmodulehierarchy($a_mod,$val['id'],$space."&nbsp;&nbsp;&nbsp;");
		}

		return ;

	}


	public function permissionsAction() {

		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		$this->view->clientid = $logininfo->clientid;
		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'user', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
		$clientid = $logininfo->clientid;


		if (!$return || !$_REQUEST['id'] || !$keyuser) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		$fdoc = Doctrine_Query::create()
		->select('count(*)')
		->from('TabMenus')
		->where('isdelete = ?',0)
		->andWhere('parent_id =0')
		->andWhere('clientid ='.$logininfo->clientid)
		->orderBy('sortorder ASC');

		$fdocexec = $fdoc->execute();
		$fdocarray = $fdocexec->toArray();


		$a_mod= array();
		$this->getmodulehierarchy($a_mod,0,'');

		//get misc modules
		$misc = new MiscModules();
		$misc_modules = $misc->getMiscModules($clientid);


		$misc_modules_ids[] = '999999999';
		foreach($misc_modules as $k_mod=>$v_mod)
		{
			$misc_modules_ids[] = $v_mod['id'];
			$misc_modules_array[$v_mod['id']] = $v_mod;
		}

		$this->view->misc_modules = $misc_modules;


		if($this->getRequest()->isPost()){


			$patient_form = new Application_Form_PatientPermissions();
			$post=$_POST;

			$post['ipid'] = $ipid;
			$post['hiddclientid'] = $logininfo->clientid;
			if($_POST['misc_modules'])
			{
				$patient_form->InsertMiscModulesData($post);
			}
			else
			{
				$patient_form->InsertData($post);
			}
			$this->view->error_message = $this->view->translate("recordinsertsucessfully");

			$this->_redirect ( APP_BASE . "patientpermissions/assign?id=".$_REQUEST['id'] );
		}
		if(!empty($_REQUEST['user_id'])){
			$userid = $_REQUEST['user_id'];
			$patperm = Doctrine_Query::create()
			->select('*')
			->from('PatientPermissions')
			->Where('clientid ='.$logininfo->clientid)
			->andWhere('ipid = "'.$ipid.'"')
			->andWhere('userid ='.$userid)
			->andWhere('pat_nav_id != 0');
			$patperms = $patperm->fetchArray();

			$patperm->where('clientid ='.$logininfo->clientid)
			->andWhere('ipid = "'.$ipid.'"')
			->andWhere('userid ='.$userid)
			->andWhereIn('misc_id', $misc_modules_ids);
			$user_module_perms = $patperm->fetchArray();

			if($user_module_perms)
			{
				foreach ($user_module_perms as $k_uperms=>$v_uperms)
				{
					$misc_perms[$v_uperms['misc_id']]['canedit'] = $v_uperms['canedit'];
					$misc_perms[$v_uperms['misc_id']]['canview'] = $v_uperms['canview'];
				}
			}
			else
			{
				$user_det = User::getUserDetails($userid);
				$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);
				$misc_perms = PatientGroupPermissions::getPatientGroupMiscPermisionsAll($group_id, $ipid);
				if (!$misc_perms)
				{
					$misc_perms = GroupDefaultPermissions::getMiscPermissionsByGroupAndClient($group_id, $clientid);
				}
			}

			if($patperms) {
				foreach ($patperms as $k_perms => $v_perms)
				{
					$permissions[$v_perms['pat_nav_id']]['canedit'] = $v_perms['canedit'];
					$permissions[$v_perms['pat_nav_id']]['canview'] = $v_perms['canview'];
				}
			} else {
				$user_det = User::getUserDetails($userid);
				$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);
				$permissions = PatientGroupPermissions::getPatientGroupPermisionsAll($group_id, $ipid);
				if (!$permissions)
				{
					$permissions = GroupDefaultPermissions::getPermissionsByGroupAndClient($group_id, $clientid);
				}
			}

			foreach($a_mod as $a_key => $a_val){
				$a_mod[$a_key]['canview'] = $permissions[$a_val['id']]['canview'];
				$a_mod[$a_key]['canedit'] = $permissions[$a_val['id']]['canedit'];
			}

			//Misc module id:1 = memo;
			$mo_previleges = new Modules();
			$mo = $mo_previleges->checkModulePrivileges("61", $logininfo->clientid);

			if(!$mo)
			{
				$misc_modules_array['1']['disabled'] = '1';
			}

			foreach ($misc_modules_array as $k_modul => $v_modul)
			{
				$misc_modules_array[$k_modul]['canview'] = $misc_perms[$v_modul['id']]['canview'];
				$misc_modules_array[$k_modul]['canedit'] = $misc_perms[$v_modul['id']]['canedit'];
			}

			$this->view->misc_modules_perms = $misc_modules_array;

		} else if(!empty($_REQUEST['group_id'])){

			$groupid = $_REQUEST['group_id'];
			$groupPerms = new PatientGroupPermissions();
			$groupPermissions = $groupPerms->getPatientGroupPermisionsAll($groupid, $ipid);

			if (!$groupPermissions)
			{
				$groupPermissions = GroupDefaultPermissions::getPermissionsByGroupAndClient($groupid, $clientid);
			}
				
			if($_REQUEST['dbg']=='1'){
				print_r($groupPermissions);
				exit;
			}
				
			foreach ($groupPermissions as $k_gperms => $v_gperms)
			{
				$permissions[$v_gperms['pat_nav_id']]['canedit'] = $v_gperms['canedit'];
				$permissions[$v_gperms['pat_nav_id']]['canview'] = $v_gperms['canview'];
			}

			foreach ($a_mod as $a_key => $a_val)
			{
				$a_mod[$a_key]['canview'] = $permissions[$a_val['id']]['canview'];
				$a_mod[$a_key]['canedit'] = $permissions[$a_val['id']]['canedit'];
			}

			/// MISC Modules
			$misc_perms = $groupPerms->getPatientGroupMiscPermisionsAll($groupid, $ipid);
			if (!$misc_perms)
			{
				$misc_perms = GroupDefaultPermissions::getMiscPermissionsByGroupAndClient($groupid, $clientid);
			}

			foreach ($misc_modules_array as $k_modul => $v_modul)
			{
				$misc_modules_array[$k_modul]['canview'] = $misc_perms[$v_modul['id']]['canview'];
				$misc_modules_array[$k_modul]['canedit'] = $misc_perms[$v_modul['id']]['canedit'];
			}

			//Misc module id:1 = memo;
			$mo_previleges = new Modules();
			$mo = $mo_previleges->checkModulePrivileges("61", $logininfo->clientid);

			if(!$mo)
			{
				$misc_modules_array['1']['disabled'] = '1';
			}

			$this->view->misc_modules_perms = $misc_modules_array;
		}

		$grid = new Pms_Grid($a_mod,1,$fdocarray[0]['count'],"listpatientpermisionsmenus.html");

		$this->grid->perms = $permissions;
		$this->view->menusgrid = $grid->renderGrid();
	}

	public function coursepermissionsAction(){
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		$this->view->clientid = $logininfo->clientid;
		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'user', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);

		$course = new Courseshortcuts();
		$courseShortcuts = $course->getClientShortcuts($logininfo->clientid);

		if (!$return || !$_REQUEST['id'] || !$keyuser) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		if($this->getRequest()->isPost()){
			$patient_form = new Application_Form_PatientPermissions();
			$post=$_POST;

			$post['ipid'] = $ipid;
			$post['hiddclientid'] = $logininfo->clientid;

			$patient_form->InsertCourseData($post);

			$this->view->error_message = $this->view->translate("recordinsertsucessfully");

			$this->_redirect ( APP_BASE . "patientpermissions/assign?id=".$_REQUEST['id'] );
		}

		if(!empty($_REQUEST['user_id'])) {
			$userid = $_REQUEST['user_id'];
			$patperm = Doctrine_Query::create()
			->select('*')
			->from('UserPatientShortcuts')
			->Where('clientid ='.$logininfo->clientid)
			->andWhere('ipid = "'.$ipid.'"')
			->andWhere('userid ='.$userid);

			$patperms = $patperm->fetchArray();

			if($patperms) {
				foreach($patperms as $k_perms=>$v_perms){
					$permissions[$v_perms['shortcutid']]['canedit'] = $v_perms['canedit'];
					$permissions[$v_perms['shortcutid']]['canview'] = $v_perms['canview'];
				}
			} else {

				$user_det = User::getUserDetails($userid);
				$groupid = Usergroup::getMasterGroup($user_det[0]['groupid']);

				$patperm = Doctrine_Query::create()
				->select('*')
				->from('GroupPatientShortcuts')
				->Where('clientid ='.$logininfo->clientid)
				->andWhere('ipid = "'.$ipid.'"')
				->andWhere('groupid ='.$groupid);

				$patperms = $patperm->fetchArray ();

				if ($patperms) {
					foreach ( $patperms as $k_perms => $v_perms ) {
						$permissions [$v_perms ['shortcutid']] ['canedit'] = $v_perms ['canedit'];
						$permissions [$v_perms ['shortcutid']] ['canview'] = $v_perms ['canview'];
					}
				} else {
					$permissions = GroupCourseDefaultPermissions::getGroupShortcutsClientAll($groupid, $clientid);
				}
			}

			$a_mod = array();
			foreach($courseShortcuts as $a_key => $a_val){
				$a_mod[$a_key] = $a_val;
				$a_mod[$a_key]['canview'] = $permissions[$a_val['shortcut_id']]['canview'];
				$a_mod[$a_key]['canedit'] = $permissions[$a_val['shortcut_id']]['canedit'];
			}

		} elseif(!empty($_REQUEST['group_id'])) {
			$groupid = $_REQUEST['group_id'];
			$patperm = Doctrine_Query::create()
			->select('*')
			->from('GroupPatientShortcuts')
			->Where('clientid ='.$logininfo->clientid)
			->andWhere('ipid = "'.$ipid.'"')
			->andWhere('groupid ='.$groupid);


			$patperms = $patperm->fetchArray();

			if($patperms) {
				foreach($patperms as $k_perms=>$v_perms){
					$permissions[$v_perms['shortcutid']]['canedit'] = $v_perms['canedit'];
					$permissions[$v_perms['shortcutid']]['canview'] = $v_perms['canview'];
				}
			} else {
				$permissions = GroupCourseDefaultPermissions::getGroupShortcutsClientAll($groupid, $clientid);
			}

			$a_mod = array();
			foreach($courseShortcuts as $a_key => $a_val){
				$a_mod[$a_key] = $a_val;
				$a_mod[$a_key]['canview'] = $permissions[$a_val['shortcut_id']]['canview'];
				$a_mod[$a_key]['canedit'] = $permissions[$a_val['shortcut_id']]['canedit'];
			}

		}

		$grid = new Pms_Grid($a_mod,1,count($courseShortcuts),"listpatientcoursepermisions.html");
		$this->grid->perms = $permissions;
		$this->view->coursegrid = $grid->renderGrid();
	}


	public function setvisibilityAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'User', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
		$getkeyuser = PatientUsers::getPatientKeyUser ( $ptarray [0] ['ipid'] );
		if ($checkcreateuser === true && ! $getkeyuser) {
			$createuser = true;
		} else {
			$createuser = false;
		}
		$this->view->createuser = $createuser;

		if (!$return || !$_REQUEST['id'] || (!$keyuser && !$createuser)) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		if ($_GET ['flg'] == 'suc') {
			$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
		}

		$this->view->act = 'patientpermissions/setvisibility?id='.$_REQUEST['id'];

		
		/*-----------------------  show master groups and client group list  -----------------------*/
		$master_groups_arr = array();
		$master_groups_arr = Doctrine_Query::create()
		->select('*')
		->from('GroupMaster')
		->where('id !=  1 and id != 2')
		->orderBy('groupname')
		->fetchArray();
		
		$master_groups2groups = array();
		foreach($master_groups_arr as $mg){
		    $master_groups2groups[$mg['id']]['id'] = $mg['id'];
		    $master_groups2groups[$mg['id']]['name'] = $mg['groupname'];
		}
		
		$client_groups_arr = array();
		$client_groups_arr = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where('clientid = ?',$logininfo->clientid)
		->andWhere('isdelete = 0')
		->fetchArray();
		
		$clGroup2masterGr = array();
		foreach($client_groups_arr as $cl_group){
		    $master_groups2groups [$cl_group['groupmaster']] ['client_groups'] [$cl_group['id']]['id']  = $cl_group['id'];
		    $master_groups2groups [$cl_group['groupmaster']] ['client_groups'] [$cl_group['id']]['name']  = $cl_group['groupname'];
		    $clGroup2masterGr [$cl_group['id']]  = $cl_group['groupmaster'] ;
		}
		
		if($this->getRequest()->isPost()){
			/*---------------------Save user visibility ---------------------*/
			$flush = Doctrine_Query::create ()
			->delete('PatientUsers p')
			->where('p.ipid = ?',$ipid)
			->andWhere('p.clientid = ?',$logininfo->clientid);
			$flush->execute();

			if($createuser !== true) {
				$usersperm = $_POST['user_id'];
			} else {
				$usersperm[] = $_POST['keyuser'];
			}
			foreach($usersperm as $user_id){
				$perm = new PatientUsers();
				$perm->clientid = $logininfo->clientid;
				$perm->userid = $user_id;
				$perm->ipid = $ipid;
				if($user_id == $_POST['keyuser']) {
					$perm->iskeyuser = 1;
				}
				$perm->create_date = date("Y-m-d H:i:s",time());
				$perm->save();
			}
			if($createuser === true) {
				$this->_redirect ( APP_BASE . "patientnew/patientdetails?id=".$_GET['id'] );
			} else {
				$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
			}
			/*-----------------------  Save group visibility  -----------------------*/
			$flush_group = Doctrine_Query::create ()
			->delete('PatientGroups p')
			->where('p.ipid = ?',$ipid)
			->andWhere('p.clientid = ?',$logininfo->clientid);
			$flush_group->execute();

			if($createuser !== true) {
				$groupssperm = $_POST['group_id'];
			}
			foreach($groupssperm as $group_id){
				$perm = new PatientGroups();
				$perm->clientid = $logininfo->clientid;
				$perm->master_groupid = $clGroup2masterGr[$group_id];
				$perm->groupid = $group_id;
				$perm->ipid = $ipid;
				$perm->create_date = date("Y-m-d H:i:s",time());
				$perm->save();
			}
			if($createuser === true) {
				$this->_redirect ( APP_BASE . "patientnew/patientdetails?id=".$_GET['id'] );
			} else {
				$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
			}
			/*----------------------- save admin visibility  -----------------------*/
			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			if($_POST['isadminvisible']){
				$cust->isadminvisible = 1;
			}else{
				$cust->isadminvisible = 0;
			}
			$cust->save();
			/*----------------------- save allow for all  -----------------------*/

			if($_POST['allowforall']==1){
				$allow = new PatientUsers();
				$allow->clientid = $logininfo->clientid;
				$allow->ipid = $ipid;
				$allow->allowforall = 1;
				$allow->create_date = date("Y-m-d H:i:s",time());
				$allow->save();
			} else{
				$flushall = Doctrine_Query::create ()
				->delete('PatientUsers p')
				->where('p.ipid = ?',$ipid)
				->andwhere('p.allowforall = ?',1)
				->andWhere('p.clientid = ?',$logininfo->clientid);
				$flushall->execute();

			}
		}
		/*----------------------- show allow for all----------------------*/

		$allow = PatientUsers::checkAllowforallPatient($ipid);
		if($allow === true){
			$this->view->allow = 'checked="checked"';
		} else {
			$this->view->allow= '';
		}
		
		
		$existing_groups = PatientGroups::getPatientGroups($ipid);
		
		
		foreach($master_groups2groups as $msg_id => $client_grs){
		    foreach($client_grs['client_groups'] as $clg_id => $clg_details){
		        if( in_array($clg_id,$existing_groups[$msg_id])){
		            $master_groups2groups [$msg_id] ['client_groups'] [$clg_id] ['assigned'] = 1;
		        } else {
		            $master_groups2groups[$msg_id]['client_groups'][$clg_id]['assigned'] = 0;
		        }
		    }
		}
		foreach($master_groups2groups as $msgid=>$data){
		    if(!isset($data['client_groups'])){
		        $master_groups2groups[$msgid]['client_groups'] = array();
		    }
		}
		
		if($_REQUEST['gr']==1){
		    
    		echo "<pre>";
    		print_r($existing_groups); 
    		print_r($master_groups2groups); 
    		exit;
		}
		
		
		$grid = new Pms_Grid ($master_groups2groups, 1, $master_groups2groups, "listclientgroupsvisibility.html" );
		$this->view->groupgridvisibility = $grid->renderGrid ();
		
 
		/*-----------------------  show user list  -----------------------*/
		$user = Doctrine_Query::create ()
		->select ( '*' )
		->from ( 'User' )
		->where ( 'isdelete = 0' )
		->andWhere ( 'clientid = ?', $clientid )
		->andWhere ( 'id != ?', $logininfo->userid )
		->orderBy ( 'username' );
		$userexec = $user->execute ();
		$userarray = $userexec->toArray ();

		$existing_users = PatientUsers::getPatientUsers($ipid);
		
		if(empty($existing_users)){
			$allowforall = PatientUsers::checkAllowforallPatient($ipid);
		}
		
		foreach ( $userarray as $user ) {
			$user_all [$user ['id']] = $user;
			$user_all [$user ['id']] ['createuser'] = $createuser;
			if (! empty ( $existing_users [$user ['id']] ) || $allowforall === true) {
				$user_all [$user ['id']] ['assigned'] = 1;
			} else {
				$user_all [$user ['id']] ['assigned'] = 0;
			}
		}

		$gmaster = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where('clientid = "'.$logininfo->clientid.'"')
		->andWhere('isdelete = 0');

		$mastergrArray = $gmaster->fetchArray();

		$ugroups[0]['groupmaster'] = "0";
		foreach($mastergrArray as $mgr){
			$ugroups[$mgr['id']] = $mgr;
		}

		$this->view->user_group_all = $ugroups;

		$grid = new Pms_Grid ( $user_all, 1, $userarray, "listuservisibility.html" );
		$this->view->usergridvisibility = $grid->renderGrid ();
		/*-----------------------  check admin visibility   -----------------------*/
		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid,0);
		if(!empty($parr['isadminvisible'])){
			$this->view->admvisibility = 'checked="checked"';
		} else {
			$this->view->admvisibility= '';

		}

	}


	/**
	 * Ancuta 
	 * Copy of  setvisibility
	 * @deprecated
	 */
	public function setvisibilityoldAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'User', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
		$getkeyuser = PatientUsers::getPatientKeyUser ( $ptarray [0] ['ipid'] );
		if ($checkcreateuser === true && ! $getkeyuser) {
			$createuser = true;
		} else {
			$createuser = false;
		}
		$this->view->createuser = $createuser;

		if (!$return || !$_REQUEST['id'] || (!$keyuser && !$createuser)) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		if ($_GET ['flg'] == 'suc') {
			$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
		}

		$this->view->act = 'patientpermissions/setvisibility?id='.$_REQUEST['id'];

		if($this->getRequest()->isPost()){
			/*---------------------Save user visibility ---------------------*/
			$flush = Doctrine_Query::create ()
			->delete('PatientUsers p')
			->where('p.ipid = ?',$ipid)
			->andWhere('p.clientid = ?',$logininfo->clientid);
			$flush->execute();

			if($createuser !== true) {
				$usersperm = $_POST['user_id'];
			} else {
				$usersperm[] = $_POST['keyuser'];
			}
			foreach($usersperm as $user_id){
				$perm = new PatientUsers();
				$perm->clientid = $logininfo->clientid;
				$perm->userid = $user_id;
				$perm->ipid = $ipid;
				if($user_id == $_POST['keyuser']) {
					$perm->iskeyuser = 1;
				}
				$perm->create_date = date("Y-m-d H:i:s",time());
				$perm->save();
			}
			if($createuser === true) {
				$this->_redirect ( APP_BASE . "patientnew/patientdetails?id=".$_GET['id'] );
			} else {
				$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
			}
			/*-----------------------  Save group visibility  -----------------------*/
			$flush_group = Doctrine_Query::create ()
			->delete('PatientGroups p')
			->where('p.ipid = ?',$ipid)
			->andWhere('p.clientid = ?',$logininfo->clientid);
			$flush_group->execute();

			if($createuser !== true) {
				$groupssperm = $_POST['group_id'];
			}
			foreach($groupssperm as $group_id){
				$perm = new PatientGroups();
				$perm->clientid = $logininfo->clientid;
				$perm->groupid = $group_id;
				$perm->ipid = $ipid;
				$perm->create_date = date("Y-m-d H:i:s",time());
				$perm->save();
			}
			if($createuser === true) {
				$this->_redirect ( APP_BASE . "patientnew/patientdetails?id=".$_GET['id'] );
			} else {
				$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
			}
			/*----------------------- save admin visibility  -----------------------*/
			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			if($_POST['isadminvisible']){
				$cust->isadminvisible = 1;
			}else{
				$cust->isadminvisible = 0;
			}
			$cust->save();
			/*----------------------- save allow for all  -----------------------*/

			if($_POST['allowforall']==1){
				$allow = new PatientUsers();
				$allow->clientid = $logininfo->clientid;
				$allow->ipid = $ipid;
				$allow->allowforall = 1;
				$allow->create_date = date("Y-m-d H:i:s",time());
				$allow->save();
			} else{
				$flushall = Doctrine_Query::create ()
				->delete('PatientUsers p')
				->where('p.ipid = ?',$ipid)
				->andwhere('p.allowforall = ?',1)
				->andWhere('p.clientid = ?',$logininfo->clientid);
				$flushall->execute();

			}
		}
		/*----------------------- show allow for all----------------------*/

		$allow = PatientUsers::checkAllowforallPatient($ipid);
		if($allow === true){
			$this->view->allow = 'checked="checked"';
		} else {
			$this->view->allow= '';
		}
		/*-----------------------  show group list  -----------------------*/
		$group = Doctrine_Query::create()
		->select('*')
		->from('GroupMaster')
		->where('id !=  1 and id != 2')
		->orderBy('groupname');
		$groupexec = $group->execute();
		$grouparray = $groupexec->toArray();

		$existing_groups = PatientGroups::getPatientGroups($ipid);

		foreach ( $grouparray as $group ) {
			$group_all [$group ['id']] = $group;
			$group_all [$group ['id']] ['creategroup'] = $creategroup;
			if (! empty ( $existing_groups [$group ['id']] ) ) {
				$group_all [$group ['id']] ['assigned'] = 1;
			} else {
				$group_all [$group ['id']] ['assigned'] = 0;
			}
		}

		$this->view->group_master_all = $group_all;
		$grid = new Pms_Grid ($group_all, 1, $grouparray, "listgroupsvisibility.html" );
		$this->view->groupgridvisibility = $grid->renderGrid ();
		/*-----------------------  show user list  -----------------------*/
		$user = Doctrine_Query::create ()
		->select ( '*' )
		->from ( 'User' )
		->where ( 'isdelete = 0' )
		->andWhere ( 'clientid = ?', $clientid )
		->andWhere ( 'id != ?', $logininfo->userid )
		->orderBy ( 'username' );
		$userexec = $user->execute ();
		$userarray = $userexec->toArray ();

		$existing_users = PatientUsers::getPatientUsers($ipid);

		if(empty($existing_users)){
			$allowforall = PatientUsers::checkAllowforallPatient($ipid);
		}
		foreach ( $userarray as $user ) {
			$user_all [$user ['id']] = $user;
			$user_all [$user ['id']] ['createuser'] = $createuser;
			if (! empty ( $existing_users [$user ['id']] ) || $allowforall === true) {
				$user_all [$user ['id']] ['assigned'] = 1;
			} else {
				$user_all [$user ['id']] ['assigned'] = 0;
			}
		}

		$gmaster = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where('clientid = "'.$logininfo->clientid.'"')
		->andWhere('isdelete = 0');

		$mastergrArray = $gmaster->fetchArray();

		$ugroups[0]['groupmaster'] = "0";
		foreach($mastergrArray as $mgr){
			$ugroups[$mgr['id']] = $mgr;
		}

		$this->view->user_group_all = $ugroups;

		$grid = new Pms_Grid ( $user_all, 1, $userarray, "listuservisibility.html" );
		$this->view->usergridvisibility = $grid->renderGrid ();
		/*-----------------------  check admin visibility   -----------------------*/
		$patientmaster = new PatientMaster();
		$parr = $patientmaster->getMasterData($decid,0);
		if(!empty($parr['isadminvisible'])){
			$this->view->admvisibility = 'checked="checked"';
		} else {
			$this->view->admvisibility= '';

		}

	}

	public function setkeyuserAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);
		$previleges = new Pms_Acl_Assertion ();
		$return = $previleges->checkPrevilege ( 'User', $logininfo->userid, 'canview' );
		$keyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);
		$getkeyuser = PatientUsers::getPatientKeyUser ( $ptarray [0] ['ipid'] );
		if ($checkcreateuser === true && ! $getkeyuser) {
			$createuser = true;
		} else {
			$createuser = false;
		}
		$this->view->createuser = $createuser;

		if (!$return || !$_REQUEST['id'] || (!$keyuser && !$createuser)) {
			$this->_redirect ( APP_BASE . "error/previlege" );
		}

		if ($_GET ['flg'] == 'suc') {
			$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
		}

		$this->view->act = 'patientpermissions/setkeyuser?id='.$_REQUEST['id'];

		if($this->getRequest()->isPost()){

			/*---------------------Save user visibility ---------------------*/
			$flush = Doctrine_Query::create ()
			->delete('PatientUsers p')
			->where('p.ipid = ?',$ipid)
			->andwhere('p.iskeyuser = ?',1)
			->andWhere('p.clientid = ?',$logininfo->clientid);
			$flush->execute();

			$perm = new PatientUsers();
			$perm->clientid = $logininfo->clientid;
			$perm->userid = $_POST['keyuser'];
			$perm->ipid = $ipid;
			$perm->iskeyuser = 1;
			$perm->create_date = date("Y-m-d H:i:s",time());
			$perm->save();

			if($createuser === true) {
				$this->_redirect ( APP_BASE . "patientnew/patientdetails?id=".$_GET['id'] );
			} else {
				$this->view->error_message = $this->view->translate ( "recordupdatedsuccessfully" );
			}
		}
		/*-----------------------  show user    -----------------------*/

		$users = new User();
		$userarray = $users->getUserByClientid($clientid);

		$userarraylast[] = $this->view->translate('selectuser');
		foreach($userarray as $user){
			$userarraylast[$user['id']] = trim($user['last_name']).", ".trim($user['first_name']);
		}
		$this->view->users = $userarraylast;

		$user = Doctrine_Query::create ()
		->select ( '*' )
		->from ( 'PatientUsers' )
		->where('ipid = ?',$ipid)
		->andWhere ( 'clientid = ?', $clientid )
		->andWhere ( 'iskeyuser = ?', 1 );
		$kuserexec = $user->execute ();
		$kuserarray = $kuserexec->toArray ();
		if (!empty($kuserarray)){

			$this->view->keyuser = $kuserarray[0]['userid'];
		}else {
		}
	}
}
?>