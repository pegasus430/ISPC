<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
	class GroupdefaultController extends Zend_Controller_Action {
        //TODO-3509, elena, 08.10.2020
        protected $translator;

		public function init()
		{
			/* Initialize action controller here */
		}

		public function addgroupAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Usergroup', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$groupms = new GroupMaster();
			$groupmaster = $groupms->getGroupMaster();
			$this->view->groupmstr = $groupmaster;

			if($this->getRequest()->isPost())
			{
				$usergroup_form = new Application_Form_Usergroup();
				$this->group = $usergroup_form->validate($_POST);

				if($this->group)
				{
					$usergroup_form->InsertData($_POST);

					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{

					$usergroup_form->assignErrorMessages();

					$this->retainValues($_POST);
				}
			}
		}

		public function editgroupAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Usergroup', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->viewRenderer('addgroup');

			if($this->getRequest()->isPost())
			{
				$usergroup_form = new Application_Form_Usergroup();
				$this->group = $usergroup_form->validate($_POST);
				if($this->group)
				{
					$usergroup_form->UpdateData($_POST);

					$this->_redirect(APP_BASE . 'usergroup/grouplist?flg=suc');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				}
				else
				{

					$usergroup_form->assignErrorMessages();

					$this->retainValues($_POST);
				}
			}



			if(strlen($_GET['id']) > 0)
			{
				$groupms = new GroupMaster();
				$groupmaster = $groupms->getGroupMaster();
				$this->view->groupmstr = $groupmaster;


				$group = Doctrine::getTable('Usergroup')->find($_GET['id']);
				$grouparray = $group->toArray();

				$this->view->grouptype = $grouparray['groupmaster'];


				if($grouparray['isactive'] == 1)
				{
					$this->view->checked = 'checked="checked"';
				}

				$this->retainValues($grouparray);
			}
		}

		public function groupdefaultlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function clientgroupmasterlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
 
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

 

			$columnarray = array("pk" => "id", "gn" => "groupname");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$group = Doctrine_Query::create()
				->select('count(*)')
				->from('GroupMaster')
				->where('id !=  1 and id != 2')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$groupexec = $group->execute();
			$grouparray = $groupexec->toArray();

			$limit = 50;
			$group->select('*');
			$group->limit($limit);
			$group->offset($_GET['pgno'] * $limit);
			$grouplimitexec = $group->execute();
			$grouplimit = $grouplimitexec->toArray();

			$grid = new Pms_Grid($grouplimit, 1, $grouparray[0]['count'], "listmastergroups.html");
			$this->view->groupmastergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("groupmasternavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['groupdefaultlist'] = $this->view->render('groupdefault/fetchlist.html');
			echo json_encode($response);
			exit;
		}

		public function visibilityfetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup', $logininfo->userid, 'canview');

			$columnarray = array("pk" => "id", "gn" => "groupname");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$group = Doctrine_Query::create()
				->select('count(*)')
				->from('GroupMaster')
				->where('id !=  1 and id != 2')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$groupexec = $group->execute();
			$grouparray = $groupexec->toArray();

			$limit = 50;
			$group->select('*');
			$group->limit($limit);
			$group->offset($_GET['pgno'] * $limit);
			$grouplimitexec = $group->execute();
			$grouplimit = $grouplimitexec->toArray();

			/* --------------Get all client specific groups  for relevant master group--------------------- */
			foreach($grouplimit as $master_group_item)
			{
				$master_groups_array[] = $master_group_item['id'];
			}
			$client_groups = new Usergroup();
			$client_groups_array = $client_groups->getUserGroups($master_groups_array);
			foreach($client_groups_array as $client_group_item)
			{
				$client_specific_group[$client_group_item['groupmaster']][] = $client_group_item['groupname'];
			}
			$this->view->client_specific_group = $client_specific_group;

			$existing_permissions = GroupDefaultVisibility::getDefaultVisibilityAll();
			$this->view->existing_permissions = $existing_permissions;

			$grid = new Pms_Grid($grouplimit, 1, $grouparray[0]['count'], "listvisibilitymastergroups.html");
			$this->view->groupmastergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("groupmasternavigation.html", 5, $_GET['pgno'], $limit);


			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['clientgroupmasterlist'] = $this->view->render('groupdefault/visibilityfetchlist.html');


			echo json_encode($response);
			exit;
		}

		public function clientvisibilityfetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup', $logininfo->userid, 'canview');

			$columnarray = array("pk" => "id", "gn" => "groupname");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$group = Doctrine_Query::create()
				->select('count(*)')
				->from('GroupMaster')
				->where('id !=  1 and id != 2')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$groupexec = $group->execute();
			$grouparray = $groupexec->toArray();

			$limit = 50;
			$group->select('*');
			$group->limit($limit);
			$group->offset($_GET['pgno'] * $limit);
			$grouplimitexec = $group->execute();
			$grouplimit = $grouplimitexec->toArray();

			$existing_permissions = GroupDefaultVisibility::getClientVisibilityAll($clientid);
			if(!$existing_permissions)
			{
				$existing_permissions = GroupDefaultVisibility::getDefaultVisibilityAll(); 
			}
			$this->view->existing_permissions = $existing_permissions;

			/* Get client secrecy visibility settings */
			$secrecy_visibility = GroupSecrecyVisibility::getClientVisibilityAll($clientid);
			$this->view->existing_secrecy_permissions = $secrecy_visibility;


			/* --------------Get all client specific groups  for relevant master group--------------------- */
			foreach($grouplimit as $master_group_item)
			{
				$master_groups_array[] = $master_group_item['id'];
			}
			$client_groups = new Usergroup();
			$client_groups_array = $client_groups->getUserGroups($master_groups_array);
			
			foreach($client_groups_array as $client_group_item)
			{                                                            // ISPC-2482 Lore 21.11.2019
			    //$client_specific_group[$client_group_item['groupmaster']][] = $client_group_item['groupname'];
			    $client_specific_group[$client_group_item['groupmaster']][$client_group_item['id']] = $client_group_item['groupname'];
			}
			//dd($client_specific_group,$existing_permissions);
			$this->view->client_specific_group = $client_specific_group;
			$grid = new Pms_Grid($grouplimit, 1, $grouparray[0]['count'], "listvisibilitymastergroups.html");
			$grid->client_specific_group=$client_specific_group;
			$this->view->groupmastergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("groupmasternavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['clientgroupmasterlist'] = $this->view->render('groupdefault/visibilityfetchlist.html');


			echo json_encode($response);
			exit;
		}

		public function clientfetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup', $logininfo->userid, 'canview');

			$columnarray = array("pk" => "id", "gn" => "groupname");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$group = Doctrine_Query::create()
				->select('count(*)')
				->from('GroupMaster')
				->where('id !=  1 and id != 2')
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$groupexec = $group->execute();
			$grouparray = $groupexec->toArray();

			$limit = 50;
			$group->select('*');
			$group->limit($limit);
			$group->offset($_GET['pgno'] * $limit);
			$grouplimitexec = $group->execute();
			$grouplimit = $grouplimitexec->toArray();
			//check module permission
			$modules = new Modules();

			$hide_contact_perms = '1';
			if($modules->checkModulePrivileges("66", $clientid))
			{
				$hide_contact_perms = '0';
			}
			$this->view->hide_contact_perms = $hide_contact_perms;
			
			$hide_btm_perms = '1';
			if($modules->checkModulePrivileges("75", $clientid))
			{
				$hide_btm_perms = '0';
			}
			$this->view->hide_btm_perms = $hide_btm_perms;
			

			foreach($grouplimit as $k_group => $v_group)
			{
				$grouplimit[$k_group]['hide_contact_perms'] = $hide_contact_perms;
				$grouplimit[$k_group]['hide_btm_perms'] = $hide_btm_perms;
			}

			$grid = new Pms_Grid($grouplimit, 1, $grouparray[0]['count'], "listclientmastergroups.html");


			$this->view->groupmastergrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("groupmasternavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['clientgroupmasterlist'] = $this->view->render('groupdefault/clientfetchlist.html');

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

		public function groupdefaultpermissionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');

				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}


				$group_form = new Application_Form_GroupDefault();
				$group_form->InsertData($_POST);

				$this->_redirect(APP_BASE . "groupdefault/groupdefaultlist");
			}


			$usergrp = Doctrine_Query::create()
				->select('*')
				->from('GroupMaster')
				->where("id=? ",$_GET['id'] );
			$track = $usergrp->execute();

			$clgrp = Doctrine_Query::create()
				->select('*')
				->from('TabMenus')
				->where('isdelete = 0')
				->andWhere('clientid = ?', $logininfo->clientid);
			$trk = $clgrp->execute();
			$this->view->permisions = $trk;

			$comma = ",";
			$navstr .="999999999";
			foreach($trk as $val)
			{
				$navstr .= $comma . $val['id'];
				$comma = ",";
			}

			$query = Doctrine_Query::create()
				->select('*')
				->from('GroupDefaultPermissions')
				->where('master_group_id= ?', $_GET['id'])
				->andWhere('pat_nav_id IN ( ' . $navstr . ' )');

			$setuserpre = $query->execute();
			$prearray = $setuserpre->toArray();
			foreach($prearray as $nav)
			{
				$navarray[$nav['pat_nav_id']] = $nav;
			}
			$this->view->navarray = $navarray;

			$user = Doctrine::getTable("GroupMaster")->find($_GET['id']);
			$userarray = $user->toArray();
			$this->view->groupname = $userarray['groupname'];
		}

		public function groupdefaultvisibilityAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$q = Doctrine_Query::create()
					->delete('GroupDefaultVisibility')
					->where('clientid="0"');
				$q->execute();

				foreach($_POST['gdf'] as $group => $check)
				{
					if($check == '1')
					{
						$grp = new GroupDefaultVisibility();
						$grp->master_group_id = $group;
						$grp->clientid = '0';
						$grp->save();
					}
				}

				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function groupclientvisibilityAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost() && $clientid > 0)
			{
			    
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				//ISPC-2482 Lore 21.11.2019
				// GroupDefaultVisibility
				$gdv_form_perms = new Application_Form_FormGroupVisibility();

				$gdv_form_perms->save_HistoryVisibility($clientid, 'GroupDefaultVisibility');
				
				$gdv_form_perms->clear_visibility_permisions($clientid, 'GroupDefaultVisibility');
				
				$gdv_form_perms->insert_defa_visibility_permisions($_POST, $clientid);
				
				// GroupSecrecyVisibility
				$gsv_form_perms = new Application_Form_FormGroupVisibility();
				
				$gsv_form_perms->save_HistoryVisibility($clientid, 'GroupSecrecyVisibility');
				
				$gsv_form_perms->clear_visibility_permisions($clientid, 'GroupSecrecyVisibility');
				
				$gsv_form_perms->insert_secrecy_visibility_permisions($_POST, $clientid);
				//.

				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function clientgrouppermisionsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clienid = $logininfo->clientid;
		    
		    
		    $previleges = new Pms_Acl_Assertion();
		    $return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');
		    
		    
		    if($this->getRequest()->isPost())
		    {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		        $previleges = new Pms_Acl_Assertion();
		        $return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');
		        
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
		        
		        if($clientid > 0)
		        {
		            $group_form = new Application_Form_GroupDefault();
		            $group_form->save_historyData($_REQUEST['id'],true); //ISPC-2302 pct.3 @Lore 23.10.2019
		            
		            $group_form->InsertData($_POST, true);
		        }
		        
		        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        $this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
		    }
		    
		    
		    $this->view->theclientid = $clientid;
		    
		    $clgrp = Doctrine_Query::create()
		    ->select('*')
		    ->from('MenuClient')
		    ->andWhere('clientid = "' . $logininfo->clientid . '"');
		    $perms = $clgrp->fetchArray();
		    
		    $comma = ",";
		    $menustr .="999999999";
		    foreach($perms as $val)
		    {
		        $menustr .= $comma . $val['menu_id'];
		        $comma = ",";
		    }
		    
		    $onlyadmin = Doctrine_Query::create()
		    ->select('x.id')
		    ->from('Menus x')
		    // 				->where('x.foradmin=1 OR x.forsuperadmin=1')
		    ->where('x.forsuperadmin=1')
		    ->andWhere('isdelete=0');
		    
		    $fdoc = Doctrine_Query::create()
		    ->select('*')
		    ->from('Menus m')
		    ->where('m.isdelete = "0"')
		    ->andWhere("m.id in (" . $menustr . ")")
		    ->andWhere('m.isdelete = 0')
		    // 				->andWhere('m.left_position = 1')
		    ->andWhere('m.forsuperadmin = 0')
		    // 				->andWhere('m.foradmin = 0 and m.parent_id not in (' . $onlyadmin->getDql() . ')')
		    ->andWhere('m.parent_id not in (' . $onlyadmin->getDql() . ')')
		    ->orderBy('m.parent_id, m.sortorder,m.sortorder_top  ASC ' );
		    $trk = $fdoc->fetchArray();
		    
		    foreach($trk as $menu_item)
		    {
		        $menuz[$menu_item['id']] = $menu_item;
		        
		        if($menu_item['parent_id'] == 0 && $menu_item['top_position'] == '0'){
		            
		            $parent_menus[$menu_item['id']] = $menu_item;
		        }
		    }
		    foreach($menuz as $menu_item)
		    {
		        if($menu_item['left_position'] == "1"){
		            
		            if($menu_item['parent_id'] != 0){
		                $left_menus[$menu_item['parent_id']][$menu_item['id']] = $menu_item;
		            }
		        } else{
		            $top_menus[0][$menu_item['id']] = $menu_item;
		        }
		        
		    }
		    //  print_r($parent_menus); exit;
		    // 		$this->view->parent_menus = $parent_menus ;
		    $this->view->parent_menus = $parent_menus ;
		    $this->view->left_menus = $left_menus ;
		    $this->view->top_menus = $top_menus ;
		    $this->view->menus = $menuz ;
		    
		    array_multisort($sortbyarray, SORT_ASC, $sortablemenus);
		    
		    $this->view->permisions = $sortablemenus;
		    
		    $query = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupDefaultPermissions')
		    ->where('master_group_id= ?', $_GET['id'])
		    ->andWhere('menu_id IN ( ' . $menustr . ' )')
		    ->andWhere('clientid = "' . $logininfo->clientid . '"');
		    
		    $prearray = $query->fetchArray();
		    
		    foreach($prearray as $menup)
		    {
		        $menuarray[$menup['menu_id']] = $menup;
		    }
		    
		    $this->view->navarray = $menuarray;
		    
		    $user = Doctrine::getTable("GroupMaster")->find($_GET['id']);
		    $userarray = $user->toArray();
		    $this->view->groupname = $userarray['groupname'];
		}
		
		/*
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 */

		public function clientgrouppermisionsallAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clienid = $logininfo->clientid;


			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');


			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

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

				if($clientid > 0)
				{
					$group_form = new Application_Form_GroupDefault();
					$group_form->save_historyData(false, true); //($mastergroup = false, $menus = false, $misc = false)
					
					$group_form->SaveData($_POST,true); //$post, $menus=false, $misc = false
				}

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
			}


			$this->view->theclientid = $clientid;

			$clgrp = Doctrine_Query::create()
				->select('*')
				->from('MenuClient')
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$perms = $clgrp->fetchArray();

			$comma = ",";
			$menustr .="999999999";
			foreach($perms as $val)
			{
				$menustr .= $comma . $val['menu_id'];
				$comma = ",";
			}

			$onlyadmin = Doctrine_Query::create()
				->select('x.id')
				->from('Menus x')
				->where('x.forsuperadmin=1')
				->andWhere('isdelete=0');

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus m')
				->where('m.isdelete = "0"')
				->andWhere("m.id in (" . $menustr . ")")
				->andWhere('m.isdelete = 0')
				->andWhere('m.forsuperadmin = 0')
				->andWhere('m.parent_id not in (' . $onlyadmin->getDql() . ')')
				->orderBy('m.parent_id, m.sortorder,m.sortorder_top  ASC ' );
			$trk = $fdoc->fetchArray();

			foreach($trk as $menu_item)
			{
				$menuz[$menu_item['id']] = $menu_item;

				if($menu_item['parent_id'] == 0 && $menu_item['top_position'] == '0'){
					
					$parent_menus[$menu_item['id']] = $menu_item;
				}
			}
			foreach($menuz as $menu_item)
			{
				if($menu_item['left_position'] == "1"){
	
					if($menu_item['parent_id'] != 0){
						$left_menus[$menu_item['parent_id']][$menu_item['id']] = $menu_item;
					}
				} else{
					$top_menus[0][$menu_item['id']] = $menu_item;
				} 
				
			}

		$this->view->parent_menus = $parent_menus ;
		$this->view->left_menus = $left_menus ;
		$this->view->top_menus = $top_menus ;
		$this->view->menus = $menuz ;
		
			array_multisort($sortbyarray, SORT_ASC, $sortablemenus);

			$this->view->permisions = $sortablemenus;

			$query = Doctrine_Query::create()
				->select('*')
				->from('GroupDefaultPermissions')
				->Where('clientid = "' . $logininfo->clientid . '"');

			$prearray = $query->fetchArray();

			foreach($prearray as $menup)
			{
			    $menuarray[$menup['master_group_id']][$menup['menu_id']] = $menup;
			}

			$this->view->navarray = $menuarray;

			
			$user = Doctrine_Query::create()
			->select('groupname')
			->from('GroupMaster')
			->where('id != "1" && id !="2" ');
			$userarray = $user->fetchArray();
			
			foreach($userarray as $usergr)
			{
			    $grouparray[$usergr['groupname']] = $usergr;
			}
			
			$this->view->groupname = $grouparray;     
		}
		

		public function clientgrouppermisions_150630Action()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clienid = $logininfo->clientid;
		
		
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');
		
		
			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');
		
				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}
		
				if($clientid > 0)
				{
					$group_form = new Application_Form_GroupDefault();
					$group_form->InsertData($_POST, true);
				}
		
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
			}
		
		
			$this->view->theclientid = $clientid;
		
			$clgrp = Doctrine_Query::create()
			->select('*')
			->from('MenuClient')
			->andWhere('clientid = "' . $logininfo->clientid . '"');
			$perms = $clgrp->fetchArray();
		
			$comma = ",";
			$menustr .="999999999";
			foreach($perms as $val)
			{
				$menustr .= $comma . $val['menu_id'];
				$comma = ",";
			}
		
			$onlyadmin = Doctrine_Query::create()
			->select('x.id')
			->from('Menus x')
			->where('x.foradmin=1 OR x.forsuperadmin=1')
			->andWhere('isdelete=0');
		
			$fdoc = Doctrine_Query::create()
			->select('*')
			->from('Menus m')
			->where('m.isdelete = "0"')
			->andWhere("m.id in (" . $menustr . ")")
			->andWhere('m.isdelete = 0')
			->andWhere('m.forsuperadmin = 0')
			->andWhere('m.foradmin = 0 and m.parent_id not in (' . $onlyadmin->getDql() . ')')
			->orderBy('m.sortorder ASC');
		
			$trk = $fdoc->fetchArray();
		
			foreach($trk as $menu_item)
			{
				$menuz[$menu_item['id']] = $menu_item;
			}
		
			foreach($menuz as $menu_item)
			{
				if($menu_item['parent_id'] == 0)
				{
					$realsort = $menu_item['sortorder'];
				}
				elseif($menu_item['top_position'] == 1 && $menu_item['left_position'] != 1)
				{
					$realsort = 99999 + $menu_item['sortorder_top'] / 100;
				}
				else
				{
					$realsort = $menuz[$menu_item['parent_id']]['sortorder'] + $menu_item['sortorder'] / 100;
				}
		
				$menu_item['realsort'] = $realsort;
				$sortbyarray[] = $realsort;
				$sortablemenus[$menu_item['id']] = $menu_item;
			}
		
			array_multisort($sortbyarray, SORT_ASC, $sortablemenus);
		
			$this->view->permisions = $sortablemenus;
		
			$query = Doctrine_Query::create()
			->select('*')
			->from('GroupDefaultPermissions')
			->where('master_group_id= ?', $_GET['id'])
			->andWhere('menu_id IN ( ' . $menustr . ' )')
			->andWhere('clientid = "' . $logininfo->clientid . '"');
		
			$prearray = $query->fetchArray();
		
			foreach($prearray as $menup)
			{
				$menuarray[$menup['menu_id']] = $menup;
			}
		
			$this->view->navarray = $menuarray;
		
			$user = Doctrine::getTable("GroupMaster")->find($_GET['id']);
			$userarray = $user->toArray();
			$this->view->groupname = $userarray['groupname'];
		}
		
		
		public function _sortmenu($a, $b)
		{
			return ($a["realsort"] > $b["realsort"] ? 1 : -1);
		}

		
		public function patientgrouppermisionsAction()
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $previleges = new Pms_Acl_Assertion();
		    $return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');
		    
		    if($this->getRequest()->isPost())
		    {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		        $previleges = new Pms_Acl_Assertion();
		        $return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');
		        
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
		        if($clientid > 0)
		        {
		            if($_POST['misc_modules'])
		            {
		                $group_form = new Application_Form_GroupDefault();
		                $group_form->save_historyData($_REQUEST['id'], false, true ); //($mastergroup = true, $menus = false, $misc = false)
		                
		                $group_form->InsertData($_POST, false, true);
		            }
		            else
		            {
		                $group_form = new Application_Form_GroupDefault();
		                $group_form->save_historyData($_REQUEST['id']);  //ISPC-2302 @Lore 29.10.2019
		                
		                $group_form->InsertData($_POST);
		            }
		        }
		        
		        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        $this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
		    }
		    
		    $this->view->theclientid = $clientid;
		    
		    $clgrp = Doctrine_Query::create()
		    ->select('*')
		    ->from('TabMenuClient')
		    ->andWhere('clientid = "' . $logininfo->clientid . '"');
		    $perms = $clgrp->fetchArray();
		    $comma = ",";
		    $navstr .="999999999";
		    foreach($perms as $val)
		    {
		        $navstr .= $comma . $val['menu_id'];
		        $comma = ",";
		    }
		    
		    $fdoc = Doctrine_Query::create()
		    ->select('*')
		    ->from('TabMenus')
		    ->where('isdelete = "0"')
		    ->andWhere("id in (" . $navstr . ")");
		    $fdoc->orderBy('parent_id,sortorder ASC');
		    $trk = $fdoc->fetchArray();
		    
		    
		    
		    foreach($trk as $k => $value)
		    {
		        $menu_name[$value['id']] = $value['menu_title'];
		        $parent_menu[$value['id']] = $value['parent_id'];
		        
		        if($value['parent_id'] == '0'){
		            $master_menus[$value['id']] = $value;
		        }
		        
		        
		        if($value['parent_id'] != '0'){
		            $submenus[$value['parent_id']][] = $value;
		        }
		        
		    }
		    $this->view->master_menus = $master_menus;
		    $this->view->submenus = $submenus;
		    
		    
		    
		    
		    $this->view->menu_name = $menu_name;
		    $this->view->parent_menu = $parent_menu;
		    $this->view->permisions = $trk;
		    
		    $query = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupDefaultPermissions')
		    ->where('master_group_id= ?', $_GET['id'])
		    ->andWhere('pat_nav_id IN ( ' . $navstr . ' )')
		    ->andWhere('clientid = "' . $logininfo->clientid . '"');
		    
		    $setuserpre = $query->execute();
		    $prearray = $setuserpre->toArray();
		    foreach($prearray as $nav)
		    {
		        $navarray[$nav['pat_nav_id']] = $nav;
		    }
		    $this->view->navarray = $navarray;
		    
		    
		    $user = Doctrine::getTable("GroupMaster")->find($_GET['id']);
		    $userarray = $user->toArray();
		    $this->view->groupname = $userarray['groupname'];
		    
		    //get misc modules
		    $misc = new MiscModules();
		    $misc_modules = $misc->getMiscModules($clientid);
		    
		    
		    $misc_modules_ids[] = '999999999';
		    foreach($misc_modules as $k_mod => $v_mod)
		    {
		        $misc_modules_ids[] = $v_mod['id'];
		    }
		    
		    $this->view->misc_modules = $misc_modules;
		    
		    
		    $q = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupDefaultPermissions')
		    ->where('master_group_id= ?', $_GET['id'])
		    ->andWhere('clientid = "' . $clientid . '"')
		    ->andWhereIn('misc_id', $misc_modules_ids);
		    
		    $q_res = $q->fetchArray();
		    
		    foreach($q_res as $k_res => $v_res)
		    {
		        $misc_perms[$v_res['misc_id']] = $v_res;
		    }
		    
		    $this->view->misc_perms = $misc_perms;
		}
		
		/*
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 */
		public function patientgrouppermisionsallAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canview');

			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

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
				
				
				if($clientid > 0)
				{ //dd($_POST);
					if($_POST['misc_modules'])
					{ 
						$group_form = new Application_Form_GroupDefault();
						$group_form->save_historyData(false, false, true ); //($mastergroup = false, $menus = false, $misc = false)
						
						$group_form->SaveData($_POST, false, true); //$post, $menus=false, $misc = false
						
					}
					else
					{ 
						$group_form = new Application_Form_GroupDefault();
						$group_form->save_historyData(false);
						
						$group_form->SaveData($_POST); //$post, $menus=false, $misc = false
						
					}
				}

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
			}

			$this->view->theclientid = $clientid;

			$clgrp = Doctrine_Query::create()
				->select('*')
				->from('TabMenuClient')
				->andWhere('clientid = "' . $logininfo->clientid . '"');
			$perms = $clgrp->fetchArray();
			$comma = ",";
			$navstr .="999999999";
			foreach($perms as $val)
			{
				$navstr .= $comma . $val['menu_id'];
				$comma = ",";
			}

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('TabMenus')
				->where('isdelete = "0"')
				->andWhere("id in (" . $navstr . ")");
			$fdoc->orderBy('parent_id,sortorder ASC');
			$trk = $fdoc->fetchArray();
			
			

			foreach($trk as $k => $value)
			{
				$menu_name[$value['id']] = $value['menu_title'];
				$parent_menu[$value['id']] = $value['parent_id'];
				
				if($value['parent_id'] == '0'){
					$master_menus[$value['id']] = $value;
				}
				
				
				if($value['parent_id'] != '0'){
					$submenus[$value['parent_id']][] = $value;					
				}
				
			}
			$this->view->master_menus = $master_menus;
			$this->view->submenus = $submenus;
 
						
			$this->view->menu_name = $menu_name;
			$this->view->parent_menu = $parent_menu;
			$this->view->permisions = $trk;

			$query = Doctrine_Query::create()
				->select('*')
				->from('GroupDefaultPermissions')
				->where('pat_nav_id IN ( ' . $navstr . ' )')
				->andWhere('clientid = "' . $logininfo->clientid . '"');

			$setuserpre = $query->execute();
			$prearray = $setuserpre->toArray();
			
			foreach($prearray as $nav)
			{
			    $navarray[$nav['master_group_id']][$nav['pat_nav_id']] = $nav;
				
			}
			//dd(count($navarray[$nav['master_group_id']]));
			$this->view->navarray = $navarray;


			$user = Doctrine_Query::create()
			->select('groupname')
			->from('GroupMaster')
			->where('id != "1" && id !="2" ');
			$userarray = $user->fetchArray();
			
			foreach($userarray as $usergr)
			{
			    $grouparray[$usergr['groupname']] = $usergr;
			}
			
			$this->view->groupname = $grouparray; 
			

			//get misc modules
			$misc = new MiscModules();
			$misc_modules = $misc->getMiscModules($clientid);


			$misc_modules_ids[] = '999999999';
			foreach($misc_modules as $k_mod => $v_mod)
			{
				$misc_modules_ids[] = $v_mod['id'];
			}
			
			$this->view->misc_modules = $misc_modules;


			$q = Doctrine_Query::create()
				->select('*')
				->from('GroupDefaultPermissions')
				->Where('clientid = "' . $clientid . '"')
				->andWhereIn('misc_id', $misc_modules_ids);

			$q_res = $q->fetchArray();
			
			foreach($q_res as $k_res => $v_res)
			{
			    $misc_perms[$v_res['master_group_id']][$v_res['misc_id']] = $v_res;
			}
			
			$this->view->misc_perms = $misc_perms;
		}

		
		
		public function clientgroupcoursepermisionsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
		    $previleges = new Pms_Acl_Assertion();
		    $return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');
		    
		    if(!$return)
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		    }
		    
		    
		    $course = new Courseshortcuts();
		    $courseShortcuts = $course->getClientShortcuts($logininfo->clientid);
		    
		    
		    
		    if($this->getRequest()->isPost())
		    {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        
		        $previleges = new Pms_Acl_Assertion();
		        $return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');
		        
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
		        
		        $group_form = new Application_Form_GroupDefault();
		        $group_form->save_coursehistoryData($_REQUEST['id']);  //ISPC-2302 @Lore 29.10.2019
		        
		        $group_form->InsertCourseData($_POST);
		        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        $this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
		    }
		    
		    $perms = new GroupCourseDefaultPermissions();
		    $permissions = $perms->getGroupShortcutsClientAll($_REQUEST['id'], $logininfo->clientid);
		    
		    
		    $grid = new Pms_Grid($courseShortcuts, 1, count($courseShortcuts), "listclientgroupcoursepermisions.html");
		    $this->view->perms = $permissions;
		    $this->view->coursegrid = $grid->renderGrid();
		}
		
		/*
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 */             
		public function clientgroupcoursepermisionsallAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}


			$course = new Courseshortcuts();
			$courseShortcuts = $course->getClientShortcuts($logininfo->clientid);



			if($this->getRequest()->isPost())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');

				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $logininfo->userid, 'canadd');

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
				
				$group_form = new Application_Form_GroupDefault();
				
				$group_form->save_coursehistoryData();
				
				$group_form->SaveCourseData($_POST);
				
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
			}
			$user = Doctrine_Query::create()
			->select('groupname')
			->from('GroupMaster')
			->where('id != "1" && id !="2" ');
			$userarray = $user->fetchArray();
			
			foreach($userarray as $usergr)
			{
			    $grouparray[$usergr['groupname']] = $usergr;
			}
			$this->view->groupname = $grouparray;
			
			
			
			$perms = new GroupCourseDefaultPermissions();
			$permissions = $perms->getGCShortcutsClientAll( $logininfo->clientid);
			
			
			$grid = new Pms_Grid($courseShortcuts, 1, count($courseShortcuts), "listclientgroupcoursepermisionsall.html");
			$this->view->perms = $permissions;
		 
			$this->view->coursegrid = $grid->renderGrid();
			
	

		}

		
		public function shortcutprevilegesAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clienid = $logininfo->clientid;

			if($this->getRequest()->isPost())
			{
				if($_POST['copygroupid'] > 0)
				{
					$group_form = new Application_Form_ShortcutPrevileges();
					$group_form->CopypermissionData($_POST);
				}
			}

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isdelete = ?', 0)
				->andWhere('id = ?', $logininfo->userid)
				->andWhere('usertype != ?', 'SA');
			$track = $user->execute();

			$mod = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('clientid="' . $clienid . '"')
				->andWhere('isdelete = 0');

			$modexec = $mod->execute();
			$mod->getSqlQuery();

			$this->view->modarray = $modexec->toArray();

			$user = Doctrine::getTable("Usergroup")->find($_GET['id']);
			$userarray = $user->toArray();
			$this->view->groupname = $userarray['groupname'];

			$cl = new Client();
			$clientarray = $cl->getClientDataByid($userarray['clientid']);

			$this->view->clientid = $clientarray[0]['id'];
			$this->view->client_name = $clientarray[0]['client_name'];

			$mod = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid ="' . $userarray['clientid'] . '"')
				->andWhere('id!="' . $_GET['id'] . '"')
				->andWhere('isdelete = 0');

			$md = $mod->execute();

			if($md)
			{
				$cpuserarray = $md->toArray();

				$groupid = array("" => "");
				foreach($cpuserarray as $key => $val)
				{
					$groupid[$val['id']] = $val['groupname'];
				}

				$this->view->copygrouparray = $groupid;
			}
		}

		
		public function contactformpermisionsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $modules = new Modules();
		    $clientid = $logininfo->clientid;
		    
		    if($modules->checkModulePrivileges("66", $clientid))
		    {
		        $blocks = Pms_CommonData::contact_form_blocks();
		        //ISPC-2454-custom form block
		        $this->view->custom_blocks = FormBlockCustomSettingsTable::findByClientid($clientid);
		        $this->view->custom_abbrev = array_column($this->view->custom_blocks, 'block_abbrev');
                //TODO-3509, elena, 08.10.2020
		        $shortcutblocks_names = $this->getShortcutBlocks();
                $blocks = array_merge($blocks, $shortcutblocks_names);

                //ISPC-2698,elena,22.12.2020
                $optblocks_names = $this->getClientOptionsBlocks();
                $blocks = array_merge($blocks, $optblocks_names);

		        $block_perms = new FormBlockPermissions();
		        $block_form_perms = new Application_Form_FormBlockPermissions();
		        
		        $user = Doctrine::getTable("GroupMaster")->find($_REQUEST['id']);
		        $userarray = $user->toArray();
		        $this->view->groupname = $userarray['groupname'];
		        
		        
		        $cl = new Client();
		        $clientarray = $cl->getClientDataByid($clientid);
		        $this->view->client_name = $clientarray[0]['client_name'];
		        
		        
		        $groupid = $_REQUEST['id'];
		        
		        if($this->getRequest()->isPost())
		        {
		            $has_edit_permissions = Links::checkLinkActionsPermission();
		            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		            {
		                $this->_redirect(APP_BASE . "error/previlege");
		                exit;
		            }
		            
		            $block_form_perms->save_FormHistoryData($groupid);    //ISPC-2302 @Lore 29.10.2019
		            $save_perms = $block_form_perms->insert_block_permisions($clientid, $groupid, $_POST);
		            
		            if($save_perms)
		            {
		                $this->_redirect(APP_BASE . 'groupdefault/contactformpermisions?id=' . $_REQUEST['id'] . '&flg=suc');
		            }
		            else
		            {
		                $this->_redirect(APP_BASE . 'groupdefault/contactformpermisions?id=' . $_REQUEST['id'] . '&flg=err');
		            }
		        }
		        $blocks_permisions = $block_perms->get_group_permissions($clientid, $groupid);
		        $this->view->blocks_permisions = $blocks_permisions;
		        $this->view->blocks = $blocks;
		    }
		    else
		    {
		        $this->_redirect(APP_BASE . 'groupdefault/clientgroupmasterlist');
		    }
		}
		
		/*
		 * ISPC-2302 pct.3 @Lore 24.10.2019
		 */
		public function contactformpermisionsallAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$modules = new Modules();
			$clientid = $logininfo->clientid;

			if($modules->checkModulePrivileges("66", $clientid))
			{
				$blocks = Pms_CommonData::contact_form_blocks();

                $more_blocks=array_keys(Application_Form_FormBlockKeyValue::get_simpleblocks_config());

                $blocks=array_merge($blocks,$more_blocks);
                //TODO-3509, elena, 08.10.2020
                $shortcutblocks_names = $this->getShortcutBlocks();
                if(is_array($shortcutblocks_names)){
                $blocks = array_merge($blocks, $shortcutblocks_names);
                }

                //ISPC-2698,elena,22.12.2020
                $optblocks_names = $this->getClientOptionsBlocks();
                if(is_array($optblocks_names)){
                    $blocks = array_merge($blocks, $optblocks_names);
                }
				//ISPC-2454-custom form block
				$this->view->custom_blocks = FormBlockCustomSettingsTable::findByClientid($clientid);
				$this->view->custom_abbrev = array_column($this->view->custom_blocks, 'block_abbrev');
				$block_perms = new FormBlockPermissions();
				$block_form_perms = new Application_Form_FormBlockPermissions();
				
				$user = Doctrine_Query::create()
				->select('groupname')
				->from('GroupMaster')
				->where('id != "1" && id !="2" ');
				$userarray = $user->fetchArray();
				
				foreach($userarray as $usergr)
				{
				    $grouparray[$usergr['groupname']] = $usergr;
				}
				$this->view->groupname = $grouparray; 

	    
	    		if($this->getRequest()->isPost())
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					}
					
					$block_form_perms->save_FormHistoryData(false);
					$block_form_perms->save_block_permisions($clientid, $_POST);

					
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
					$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
				}
							
				$blocks_permisions = $block_perms->get_client_formblock_permissions($clientid);
				$this->view->blocks_permisions = $blocks_permisions;
				$this->view->blocks = $blocks;
				
			}
			else
			{
				$this->_redirect(APP_BASE . 'groupdefault/clientgroupmasterlist');
			}
		}

		public function blocksapvpermisionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$modules = new Modules();
			$clientid = $logininfo->clientid;

			if($modules->checkModulePrivileges("66", $clientid))
			{
				$blocks = Pms_CommonData::contact_form_blocks();
				//ISPC-2454-custom form block
				$this->view->custom_blocks = FormBlockCustomSettingsTable::findByClientid($clientid);
				$this->view->custom_abbrev = array_column($this->view->custom_blocks, 'block_abbrev');
				$block_sapv_perms = new FormSapvBlockPermissions();
				$block_sapv_form_perms = new Application_Form_FormSapvBlockPermissions();

				$cl = new Client();
				$clientarray = $cl->getClientDataByid($clientid);
				$this->view->client_name = $clientarray[0]['client_name'];

				if($this->getRequest()->isPost())
				{
					$save_perms = $block_sapv_form_perms->insert_block_permisions($clientid, $_POST);

					if($save_perms)
					{
						$this->_redirect(APP_BASE . 'groupdefault/blocksapvpermisions?flg=suc');
					}
					else
					{
						$this->_redirect(APP_BASE . 'groupdefault/blocksapvpermisions?flg=err');
					}
				}

				$blocks_sapv_permisions = $block_sapv_perms->get_sapv_permissions($clientid);
				$this->view->blocks_sapv_permisions = $blocks_sapv_permisions;
				$this->view->blocks = $blocks;
			}
		}

		public function formtypepermissionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$modules = new Modules();
			$clientid = $logininfo->clientid;

			if($modules->checkModulePrivileges("66", $clientid))
			{
				$form_types = new FormTypes();
				$form_type_array = $form_types->get_form_types($clientid);
				foreach($form_type_array as $k_type => $v_type)
				{
					$types[$v_type['id']] = $v_type;
				}

				$types_perms = new FormTypePermissions();
				$types_form_perms = new Application_Form_FormTypePermissions();

				$user = Doctrine::getTable("GroupMaster")->find($_REQUEST['id']);
				$userarray = $user->toArray();
				$this->view->groupname = $userarray['groupname'];


				$cl = new Client();
				$clientarray = $cl->getClientDataByid($clientid);
				$this->view->client_name = $clientarray[0]['client_name'];


				$groupid = $_REQUEST['id'];

				if($this->getRequest()->isPost())
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					}
					
					$types_form_perms->save_TypeHistoryData($groupid);     //ISPC-2302 @Lore 29.10.2019
					$save_perms = $types_form_perms->insert_type_permisions($clientid, $groupid, $_POST);

					if($save_perms)
					{
						$this->_redirect(APP_BASE . 'groupdefault/formtypepermissions?id=' . $_REQUEST['id'] . '&flg=suc');
					}
					else
					{
						$this->_redirect(APP_BASE . 'groupdefault/formtypepermissions?id=' . $_REQUEST['id'] . '&flg=err');
					}
				}

				$type_permisions = $types_perms->get_group_permissions($clientid, $groupid);
				$this->view->types_permisions = $type_permisions;
				$this->view->types = $types;
			}
			else
			{
				$this->_redirect(APP_BASE . 'groupdefault/clientgroupmasterlist');
			}
		}

		/*
		 * ISPC-2302 pct.3 @Lore 25.10.2019
		 */
		public function formtypepermissionsallAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $modules = new Modules();
		    $clientid = $logininfo->clientid;
		    
		    if($modules->checkModulePrivileges("66", $clientid))
		    {
		        $form_types = new FormTypes();
		        $form_type_array = $form_types->get_form_types($clientid);
		        foreach($form_type_array as $k_type => $v_type)
		        {
		            $types[$v_type['id']] = $v_type;
		        }
		        
		        $types_perms = new FormTypePermissions();
		        $types_form_perms = new Application_Form_FormTypePermissions();
		        
		        $user = Doctrine_Query::create()
		        ->select('groupname')
		        ->from('GroupMaster')
		        ->where('id != "1" && id !="2" ');
		        $userarray = $user->fetchArray();
		        
		        foreach($userarray as $usergr)
		        {
		            $grouparray[$usergr['groupname']] = $usergr;
		        }
		        $this->view->groupname = $grouparray; 
		        	        
        
		        if($this->getRequest()->isPost())
		        {
		            $has_edit_permissions = Links::checkLinkActionsPermission();
		            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		            {
		                $this->_redirect(APP_BASE . "error/previlege");
		                exit;
		            }
		            
		            $types_form_perms->save_TypeHistoryData(false);
		            $types_form_perms->save_type_permisions($clientid, $_POST);
		            
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		            $this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
		        }
		        
		        $type_permisions = $types_perms->get_client_permissions($clientid);
		        $this->view->types_permisions = $type_permisions;
		        $this->view->types = $types;
		    }
		    else
		    {
		        $this->_redirect(APP_BASE . 'groupdefault/clientgroupmasterlist');
		    }
		}
		
		public function pathstepspermissionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$modules = new Modules();
			$clientid = $logininfo->clientid;

			$paths = new OrgPaths();
			$client_paths = $paths->get_paths($clientid);

			foreach($client_paths as $k_c_path => $v_c_path)
			{
				$client_paths_details[$v_c_path['id']] = $v_c_path;
				$client_paths_ids[] = $v_c_path['id'];
			}


			$this->view->client_paths = $client_paths_details;


			if(!empty($client_paths_ids))
			{
				$final_steps = array();
				$this->getmodulehierarchy($final_steps, $client_paths_ids, '0', ' ');
				$this->view->client_steps_ordered = $final_steps;
			}


			$steps_perms = new OrgStepsPermissions();
			$org_steps_perms = new Application_Form_OrgStepsPermissions();

			$user = Doctrine::getTable("GroupMaster")->find($_REQUEST['id']);
			$userarray = $user->toArray();
			$this->view->groupname = $userarray['groupname'];


			$cl = new Client();
			$clientarray = $cl->getClientDataByid($clientid);
			$this->view->client_name = $clientarray[0]['client_name'];

			$groupid = $_REQUEST['id'];

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$org_steps_perms->save_StepHistoryData($groupid);   //ISPC-2302 @Lore 29.10.2019
				$save_perms = $org_steps_perms->insert_step_permisions($clientid, $groupid, $_POST);

				if($save_perms)
				{
					$this->_redirect(APP_BASE . 'groupdefault/pathstepspermissions?id=' . $_REQUEST['id'] . '&flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'groupdefault/pathstepspermissions?id=' . $_REQUEST['id'] . '&flg=err');
				}
			}




			$steps_permisions = $steps_perms->get_group_permissions($clientid, $groupid);
			$this->view->steps_permisions = $steps_permisions;
		}

		/*
		 * ISPC-2302 pct.3 @Lore 25.10.2019
		 */
		public function pathstepspermissionsallAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $modules = new Modules();
		    $clientid = $logininfo->clientid;
		    
		    $paths = new OrgPaths();
		    $client_paths = $paths->get_paths($clientid);
		    
		    foreach($client_paths as $k_c_path => $v_c_path)
		    {
		        $client_paths_details[$v_c_path['id']] = $v_c_path;
		        $client_paths_ids[] = $v_c_path['id'];
		    }
		    
		    
		    $this->view->client_paths = $client_paths_details;
		    
		    
		    if(!empty($client_paths_ids))
		    {
		        $final_steps = array();
		        $this->getmodulehierarchy($final_steps, $client_paths_ids, '0', ' ');
		        $this->view->client_steps_ordered = $final_steps;
		    }
		    
		    
		    $steps_perms = new OrgStepsPermissions();
		    $org_steps_perms = new Application_Form_OrgStepsPermissions();
		    
		    $user = Doctrine_Query::create()
		    ->select('groupname')
		    ->from('GroupMaster')
		    ->where('id != "1" && id !="2" ');
		    $userarray = $user->fetchArray();
		    
		    foreach($userarray as $usergr)
		    {
		        $grouparray[$usergr['groupname']] = $usergr;
		    }
		    $this->view->groupname = $grouparray; 
		    
		    		    
		    if($this->getRequest()->isPost())
		    {
		        $has_edit_permissions = Links::checkLinkActionsPermission();
		        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		        {
		            $this->_redirect(APP_BASE . "error/previlege");
		            exit;
		        }
		        
		        $org_steps_perms->save_StepHistoryData(false);
		        
		        $org_steps_perms->save_step_permisions($clientid, $_POST);
		       
		        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        $this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
		    }
		    	    
		    $steps_permisions = $steps_perms->get_orgsteps_permissions($clientid);
		    $this->view->steps_permisions = $steps_permisions;
		}
		
		private function getmodulehierarchy(&$final_steps, $client_paths, $parentid, $space)
		{
			$steps_q = Doctrine_Query::create()
				->select('*, CONVERT(`name` using latin1) as name, CONVERT(`shortcut` using latin1) as shortcut')
				->from('OrgSteps')
				->where('master ="' . $parentid . '"')
				->andWhereIn('path', $client_paths)
				->andWhere('isdelete = "0"')
				->orderBy("shortcut, order", 'ASC');

			$steps_res = $steps_q->fetchArray();

			foreach($steps_res as $key => $val)
			{
				$details = array(
					'id' => $val['id'],
					'path' => $val['path'],
					'master' => $val['master'],
					'name' => $val['name'],
					'shortcut' => $val['shortcut'],
					'tabname' => $val['tabname'],
					'ismanual' => $val['ismanual'],
					'order' => $val['order'],
					'space' => $space
				);
				array_push($final_steps, $details);
				$this->getmodulehierarchy($final_steps, $client_paths, $val['id'], $space . "&nbsp;&nbsp;-&nbsp;&nbsp; ");
			}

			return;
		}

		
		public function btmclientgrouppermissionsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $modules = new Modules();
		    $clientid = $logininfo->clientid;
		    
		    if($modules->checkModulePrivileges("75", $clientid))
		    {
		        $btm_perms = new BtmGroupPermissions();
		        $form_btmgroup_perms = new Application_Form_BtmGroupPermissions();
		        
		        $user = Doctrine::getTable("GroupMaster")->find($_REQUEST['id']);
		        $userarray = $user->toArray();
		        $this->view->groupname = $userarray['groupname'];
		        
		        
		        $cl = new Client();
		        $clientarray = $cl->getClientDataByid($clientid);
		        $this->view->client_name = $clientarray[0]['client_name'];
		        
		        
		        $groupid = $_REQUEST['id'];
		        
		        if($this->getRequest()->isPost())
		        {
		            $has_edit_permissions = Links::checkLinkActionsPermission();
		            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		            {
		                $this->_redirect(APP_BASE . "error/previlege");
		                exit;
		            }
		            
		            $form_btmgroup_perms->save_btmHistoryData($groupid);     // ISPC-2302 pct.3 @Lore 25.10.2019
		            $save_perms = $form_btmgroup_perms->insert_btm_permisions($clientid, $groupid, $_POST);
		            
		            if($save_perms)
		            {
		                $this->_redirect(APP_BASE . 'groupdefault/btmclientgrouppermissions?id=' . $_REQUEST['id'] . '&flg=suc');
		            }
		            else
		            {
		                $this->_redirect(APP_BASE . 'groupdefault/btmclientgrouppermissions?id=' . $_REQUEST['id'] . '&flg=err');
		            }
		        }
		        
		        $btm_permisions = $btm_perms->get_group_permissions($clientid, $groupid);
		        $this->view->btm_perms = $btm_permisions;
		    }
		    else
		    {
		        $this->_redirect(APP_BASE . 'groupdefault/clientgroupmasterlist');
		    }
		}
		
		/*
		 * ISPC-2302 pct.3 @Lore 25.10.2019
		 */
		public function btmclientgrouppermissionsallAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$modules = new Modules();
			$clientid = $logininfo->clientid;

			if($modules->checkModulePrivileges("75", $clientid))
			{
				$btm_perms = new BtmGroupPermissions();
				$form_btmgroup_perms = new Application_Form_BtmGroupPermissions();

				$user = Doctrine_Query::create()
				->select('groupname')
				->from('GroupMaster')
				->where('id != "1" && id !="2" ');
				$userarray = $user->fetchArray();
				
				foreach($userarray as $usergr)
				{
				    $grouparray[$usergr['groupname']] = $usergr;
				}
				$this->view->groupname = $grouparray; 

				if($this->getRequest()->isPost())
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					}
					
					
					$form_btmgroup_perms->save_btmHistoryData(false);
					$form_btmgroup_perms->save_btm_permisions($clientid, $_POST);

					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
					$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
				}

				$btm_permisions = $btm_perms->get_all_groups_permissions($clientid);
				$this->view->btm_perms = $btm_permisions;
			}
			else
			{
				$this->_redirect(APP_BASE . 'groupdefault/clientgroupmasterlist');
			}
		}

		
		public function clientgroupiconspermisionsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		    
		    $previleges = new Pms_Acl_Assertion();
		    $return = $previleges->checkPrevilege('userprevileges', $userid, 'canadd');
		    
		    if(!$return)
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		    }
		    
		    $user = Doctrine::getTable("GroupMaster")->find($_GET['id']);
		    $userarray = $user->toArray();
		    $this->view->groupname = $userarray['groupname'];
		    
		    $cl = new Client();
		    $clientarray = $cl->getClientDataByid($clientid);
		    $this->view->client_name = $clientarray[0]['client_name'];
		    
		    $wlprevileges = new Modules();
		    if($wlprevileges->checkModulePrivileges("51", $clientid) || $wlprevileges->checkModulePrivileges("57", $clientid))
		    {
		        $this->view->wlpermission = true;
		    }
		    else
		    {
		        $this->view->wlpermission = false;
		    }
		    
		    $sys_icons = new IconsMaster();
		    $this->view->system_icons_list = $sys_icons->get_system_icons($clientid, false, false, false);
		    
		    //get custom icons
		    $icons = new IconsClient();
		    $this->view->icons_list = $icons->get_client_icons($clientid);
		    
		    
		    if($this->getRequest()->isPost())
		    {
		        $previleges = new Pms_Acl_Assertion();
		        $return = $previleges->checkPrevilege('userprevileges', $userid, 'canadd');
		        
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
		        
		        $group_form = new Application_Form_GroupDefault();
		        
		        $group_form->save_historyIconsData($_REQUEST['id']);      //ISPC-2302 pct.3 @Lore 25.10.2019
		        $group_form->insert_icon_data($_POST);
		        
		        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        $this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
		    }
		    
		    $perms = new GroupIconsDefaultPermissions();
		    $permissions = $perms->getGroupIconsClientAll($_REQUEST['id'], $clientid);
		    
		    $this->view->perms = $permissions;
		    
		    
		    if($_REQUEST['fill_all_permissions'])
		    {
		        $this->default_perms();
		    }
		    
		}
		
		/*
		 * ISPC-2302 pct.3 @Lore 25.10.2019
		 */
		public function clientgroupiconspermisionsallAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges', $userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$user = Doctrine_Query::create()
			->select('groupname')
			->from('GroupMaster')
			->where('id != "1" && id !="2" ');
			$userarray = $user->fetchArray();
			
			foreach($userarray as $usergr)
			{
			    $grouparray[$usergr['groupname']] = $usergr;
			}
			$this->view->groupname = $grouparray; 

			
			$wlprevileges = new Modules();
			if($wlprevileges->checkModulePrivileges("51", $clientid) || $wlprevileges->checkModulePrivileges("57", $clientid))
			{
				$this->view->wlpermission = true;
			}
			else
			{
				$this->view->wlpermission = false;
			}

			$sys_icons = new IconsMaster();
			$this->view->system_icons_list = $sys_icons->get_system_icons($clientid, false, false, false);

			//get custom icons
			$icons = new IconsClient();
			$this->view->icons_list = $icons->get_client_icons($clientid);


			if($this->getRequest()->isPost())
			{
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('userprevileges', $userid, 'canadd');

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
				
				$group_form = new Application_Form_GroupDefault();
				
				$group_form->save_historyIconsData();
				
				$group_form->save_icon_data($_POST);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				$this->_redirect(APP_BASE . "groupdefault/clientgroupmasterlist");
			}

			$perms = new GroupIconsDefaultPermissions();
			$permissions = $perms->getAllGroupIconsClientAll( $clientid);

			$this->view->perms = $permissions;
			

			if($_REQUEST['fill_all_permissions'])
			{
				$this->default_perms();
			}
			
		}

		private function default_perms()
		{
			set_time_limit(0);
			//get all clients
			$no_clients = false;
			$clients = Client::get_all_clients_ids();
			if(count($clients) == '0')
			{
				$clients[] = '99999999999';
				$no_clients = true;
			}

			//get master groups
			$group = Doctrine_Query::create()
				->select('*')
				->from('GroupMaster')
				->where('id !=  1 and id != 2');
			$groups_res = $group->fetchArray();

			//get system icons(same for all clients
			$sys_icons = new IconsMaster();
			$system_icons_list = $sys_icons->get_system_icons($clientid, false, false, false);
			foreach($system_icons_list as $k_sys_icn => $v_sys_icn)
			{
				$sys_icons_ids[] = $v_sys_icn['id'];
			}

			//get all clients icons (indivitual for each client)
			$icons = new IconsClient();
			$icons_list_ids = $icons->get_clients_icons($clients);

			//check if there are any records in perms (some protection in case of accidental/double request)
			if(GroupIconsDefaultPermissions::check_empty_table() && !$no_clients)
			{
				//populate db
				foreach($groups_res as $k_gr => $v_gr_data)
				{
					foreach($clients as $k_client => $v_client)
					{
						foreach($icons_list_ids[$v_client] as $k_icon => $v_icon)
						{
							$master_insert[] = array(
								'clientid' => $v_client,
								'master_group_id' => $v_gr_data['id'],
								'icon' => $v_icon,
								'icon_type' => 'custom',
								'canadd' => '0',
								'candedit' => '0',
								'canview' => '1',
								'candelete' => '0',
							);
						}

						foreach($sys_icons_ids as $k_icon => $v_icon)
						{
							$master_insert[] = array(
								'clientid' => $v_client,
								'master_group_id' => $v_gr_data['id'],
								'icon' => $v_icon,
								'icon_type' => 'system',
								'canadd' => '0',
								'candedit' => '0',
								'canview' => '1',
								'candelete' => '0',
							);
						}
					}
				}

				if($master_insert)
				{
					$collection = new Doctrine_Collection('GroupIconsDefaultPermissions');
					$collection->fromArray($master_insert);
					$collection->save();

					$message = "Users icon permissions table was filled with default perms. for all clients.";
				}
			}
			else
			{
				$message = "Icon permissions table not emtpy!\n";
			}

			$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
			$log = new Zend_Log($writer);
			if($log)
			{
				$log->crit($message);
			}
		}

        /**
         *
         * returns shortcut (dynamic) blocks
         * TODO-3509, elena, 08.10.2020
         *
         * @return array
         */
        protected function getShortcutBlocks(): array
        {
            $this->translator = new Zend_View_Helper_Translate();
            $shortcutblocks = ShortcutTextBlock::getShortcutTextBlocks();
            $retArr = [];
            $shortcutblocks_names = [];
            foreach ($shortcutblocks as $sblock) {
                $retArr['block_block_shortcode_' . $sblock['id']] = $sblock['blockname'];
                $retArr['block_shortcode_' . $sblock['id']] = $sblock['blockname'];
            };

            if (count($shortcutblocks)) {
                //add translation from db (mapping block_shortcode_{id} to blockname)
                $this->translator->getTranslator()->addTranslation(array(
                    'content' => $retArr,
                    'locale' => 'de',
                    'clear' => false
                ));

                foreach ($shortcutblocks as $shortcodeblock) {
                    $shortcutblocks_names[] = 'block_shortcode_' . $shortcodeblock['id'];
                }
            }
            return $shortcutblocks_names;
        }


        /**
         * ISPC-2698,elena,22.12.2020
         *
         * @return array
         * @throws Zend_Translate_Exception
         */
        protected function getClientOptionsBlocks(){
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;

            $this->translator = new Zend_View_Helper_Translate();
            $optblocks = ClientOptionsBlocks::getClientOptionsBlocks($clientid);
            $retArr = [];
            $opt_names = [];
            foreach ( $optblocks as $sblock) {
                $retArr['block_block_opt_' . $sblock['id']] = $sblock['blockname'];
                $retArr['block_opt_' . $sblock['id']] = $sblock['blockname'];
            };

            if (count($optblocks)) {
                //add translation from db (mapping block_shortcode_{id} to blockname)
                $this->translator->getTranslator()->addTranslation(array(
                    'content' => $retArr,
                    'locale' => 'de',
                    'clear' => false
                ));

                foreach ($optblocks as $optblock) {
                    $optblocks_names[] = 'block_opt_' . $optblock['id'];
                }
            }
            return $optblocks_names;

        }


    }

?>