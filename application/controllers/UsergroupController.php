<?php

class UsergroupController extends Zend_Controller_Action
{

	public function init()
	{
		/* Initialize action controller here */
	}

	public function addgroupAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Usergroup',$logininfo->userid,'canadd');
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		$groupms = new GroupMaster();
		$groupmaster = $groupms->getGroupMaster();
		$this->view->groupmstr = $groupmaster;

		
		
		// ISPC-2197 @Ancuta, 08.06.2018 
		$social_groups_values = Doctrine::getTable('Usergroup')->getEnumValues('social_groups');
		
		$social_groups_labels = array();
		foreach($social_groups_values as $val=>$label){
		    $social_groups_labels[$label] = $this->view->translate($label);
		}
		
		$this->view->social_groups_values = $social_groups_labels;
		//--
		
		
		if($this->getRequest()->isPost())
		{
			$usergroup_form = new Application_Form_Usergroup();
			$this->group = $usergroup_form->validate($_POST);
			if($this->group)
			{
				$usergroup_form->InsertData($_POST);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}else{
				$usergroup_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function editgroupAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Usergroup', $logininfo->userid, 'canedit');

		if (!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		$this->_helper->viewRenderer('addgroup');

		if ($this->getRequest()->isPost())
		{
			$usergroup_form = new Application_Form_Usergroup();
			$this->group = $usergroup_form->validate($_POST);

			if ($this->group)
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

		if (strlen($_GET['id']) > 0)
		{
			$groupms = new GroupMaster();
			$groupmaster = $groupms->getGroupMaster();
			$this->view->groupmstr = $groupmaster;

			$group = Doctrine::getTable('Usergroup')->find($_GET['id']);
			$grouparray = $group->toArray();
			$this->view->grouptype = $grouparray['groupmaster'];
			if ($grouparray['isactive'] == 1)
			{
				$this->view->checked = 'checked="checked"';
			}
			$this->retainValues($grouparray);
			
		}
		
		//ISPC-2197 @Ancuta, 08.06.2018
		$social_groups_values = Doctrine::getTable('Usergroup')->getEnumValues('social_groups');
		
		$social_groups_labels = array();
		foreach($social_groups_values as $val=>$label){
		    $social_groups_labels[$label] = $this->view->translate($label);			
		}
		
		$this->view->social_groups_values = $social_groups_labels;
		//--
	}


	public function grouplistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('usergroup',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
		}
	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('usergroup',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		$columnarray = array("pk"=>"id","gn"=>"groupname");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$group = Doctrine_Query::create()
		->select('count(*)')
		->from('Usergroup')
		->where('isdelete =  0')
		->andWhere('clientid = ?', $logininfo->clientid)
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$groupexec = $group->execute();
		$grouparray = $groupexec->toArray();
			
		$limit = 50;
		$group->select('*');
		$group->limit($limit);
		$group->offset($_GET['pgno']*$limit);
		$grouplimitexec = $group->execute();
		$grouplimit = $grouplimitexec->toArray();
		$this->view->{"style".$_GET['pgno']} = "active";
		$groupms = new GroupMaster();
		$groupmast = $groupms->getGroupMaster();

		foreach($grouplimit as $key=>$value){
			$grpmaster= $groupmast[$value['groupmaster']];
			$grouplimit[$key]['groupmaster'] = $grpmaster;
		}

			
		$grid = new Pms_Grid($grouplimit,1,$grouparray[0]['count'],"listusergroup.html");
		$this->view->usergroupgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['usergrouplist'] =$this->view->render('usergroup/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}

	public function setshortcutprevilegeAction()
	{
		$this->_helper->viewRenderer('shortcutprevileges');
		$shrt_form = new Application_Form_ShortcutPrevileges();

		$prevarr = array("act"=>$_GET['act'],"shrtid"=>$_GET['shrtid'],"val"=>$_GET['val'],"grpid"=>$_GET['grpid']);
		$shrt_form->setShortcutPrevilege($prevarr);
	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}

	public function deletegroupAction()
	{
		$this->_helper->viewRenderer('grouplist');

		if($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$logininfo= new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('usergroup',$logininfo->userid,'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE."error/previlege");
			}

			if(count($_POST['group_id'])<1){
				$this->view->error_message = $this->view->translate('selectatleastone'); $error=1;
			}
			if($error==0)
			{
				foreach($_POST['group_id'] as $key=>$val)
				{
					$group = Doctrine::getTable('Usergroup')->find($val);
					$group->isdelete = 1;
					$group->save();
					$this->view->error_message = $this->view->translate("recorddeletedsuccessfully");
				}
			}
		}
	}

	public function groupprevilegesAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('userprevileges',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			$logininfo= new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('userprevileges',$logininfo->userid,'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE."error/previlege");
			}

			if($_POST['copygroupid']>0)
			{
				$group_form = new Application_Form_ShortcutPrevileges();
				$group_form->CopypermissionData($_POST);
					
			}else{

				$group_form = new Application_Form_Groupprevileges();
				$group_form->InsertData($_POST);
			}
			$this->view->error_message = $this->view->translate("recordinsertsucessfully");
		}
			
			
		$usergrp = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where("id= ?", $_GET['id']);
		$track = $usergrp->execute();
			
		$groupclientid = 0;
		if($track)
		{
			$trackarr = $track->toArray();
			$groupclientid = $trackarr[0]['clientid'];

		}
		$clgrp =Doctrine_Query::create()
		->select('*')
		->from('ClientModules')
		->where("clientid='".$groupclientid."' and canaccess=1");
		$trk = $clgrp->execute();
		$mids ="0";
		if($trk)
		{
			$clarr = $trk->toArray();


			foreach($clarr as $key=>$val)
			{
				$comma = ",";
				$mids.=$comma.$val['moduleid'];
			}
		}

		$user = Doctrine_Query::create()
		->select('*')
		->from('User')
		->where('isdelete = ?', 0)
		->andWhere('id = ?',$logininfo->userid)
		->andWhere('usertype != ?','SA');
		$track = $user->execute();

		if(count($track->toArray())<1)
		{
			$mod = Doctrine_Query::create()
			->select('*')
			->from('Modules')
			->where('isdelete = 0')
			->andWhere("id in (".$mids.")");
			$modexec = $mod->execute();

			$this->view->modarray = $modexec->toArray();

		}else{

			$mod = Doctrine_Query::create()
			->select('*')
			->from('Modules')
			->where("module != 'client'")
			->andWhere('isdelete = 0')
			->andWhere("id in (".$mids.")");
			$modexec = $mod->execute();
			$this->view->modarray = $modexec->toArray();
		}

		$user = Doctrine::getTable("Usergroup")->find($_GET['id']);
		$userarray = $user->toArray();
		$this->view->groupname = $userarray['groupname'];

		$client = Doctrine_Query::create()
		->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
				->from("Client")
				->where('id='.$userarray['clientid']);
		$clientexec = $client->execute();
		$clientarray = $clientexec->toArray();
		$this->view->clientid =  $clientarray[0]['id'];
		$this->view->client_name = $clientarray[0]['client_name'];
			
			
		$copyuser = Doctrine::getTable("Usergroup")->findBy('clientid',$userarray['clientid']);
		$cpuserarray = $copyuser->toArray();
		$groupid = array(""=>"");
		foreach($cpuserarray as $key=>$val)
		{
			$groupid[$val['id']] = $val['groupname'];
		}
		$this->view->copygrouparray = $groupid;
	}




	public function shortcutprevilegesAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clienid = $logininfo->clientid;
			
		if($this->getRequest()->isPost())
		{
			if($_POST['copygroupid']>0)
			{
				$group_form = new Application_Form_ShortcutPrevileges();
				$group_form->CopypermissionData($_POST);
			}
		}
			
		$user = Doctrine_Query::create()
		->select('*')
		->from('User')
		->where('isdelete = ?', 0)
		->andWhere('id = ?',$logininfo->userid)
		->andWhere('usertype != ?','SA');
		$track = $user->execute();
			
		$mod = Doctrine_Query::create()
		->select('*')
		->from('Courseshortcuts')
		->where('clientid="'.$clienid.'"')
		->andWhere('isdelete = 0');

		$modexec = $mod->execute();
		$mod->getSqlQuery();

		$this->view->modarray = $modexec->toArray();

		$user = Doctrine::getTable("Usergroup")->find($_GET['id']);
		$userarray = $user->toArray();
		$this->view->groupname = $userarray['groupname'];
			
		$cl = new Client();
		$clientarray = $cl->getClientDataByid($userarray['clientid']);

		$this->view->clientid =  $clientarray[0]['id'];
		$this->view->client_name = $clientarray[0]['client_name'];

		$mod = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where('clientid = ?', $userarray['clientid'])
		->andWhere('id != ?', $_GET['id'])
		->andWhere('isdelete = ?',0);

		$md = $mod->execute();

		if($md)
		{
			$cpuserarray = $md->toArray();

			$groupid = array(""=>"");
			foreach($cpuserarray as $key=>$val)
			{
				$groupid[$val['id']] = $val['groupname'];
			}

			$this->view->copygrouparray = $groupid;
		}

	}

}

?>