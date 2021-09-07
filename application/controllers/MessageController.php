<?php

	class MessageController extends Zend_Controller_Action {

		public function init()
		{
			/* Initialize action controller here */
		}

		public function sendmessagesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$message_form = new Application_Form_Messages();

				if($message_form->Validate($_POST))
				{

					$message_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate('mailsentmsg');
				}
				else
				{
					$this->view->useridjs = $_POST['userid'];
					$this->retainValues($_POST);
					$message_form->assignErrorMessages();
				}
			}

			$messages_obj = new Messages();
			$priority_ranks = $messages_obj->priority_ranks();
			$this->view->priority_ranks = $priority_ranks;
			

			if($logininfo->usertype != 'SA' && $clientid > 0)
			{
			    // TODO-1647 @Ancuta 22.06.2018
				/* $user_pseudo =  new UserPseudoGroup();
				$user_ps =  $user_pseudo->get_userpseudo(); */
				
				$UserPseudoGroup = Doctrine_Query::create()
				->select("gr.*")
				->from('UserPseudoGroup gr INDEXBY gr.id')
				->where('gr.clientid = ?', $clientid)
				->andWhere('gr.isdelete = 0')

				->addSelect("gru.*")
				->leftJoin("gr.PseudoGroupUsers gru ON (gr.id=gru.pseudo_id AND gru.isdelete = 0)")
				 
				->fetchArray();
				
				$user_ps = $UserPseudoGroup;
				
				foreach($UserPseudoGroup as $ps_gr_id=>$ps_gr_details){
				    if ( ! empty($ps_gr_details['PseudoGroupUsers'])){
				        foreach($ps_gr_details['PseudoGroupUsers'] as $ps_gr_user_k =>$ps_gr_user_details){
				            $u2p[$ps_gr_user_details['user_id']] = $ps_gr_user_details['pseudo_id'];
				        }
				    } else{
				        unset($UserPseudoGroup[$ps_gr_id]);
				    }
				}
				$this->view->user_pseudo  = $UserPseudoGroup;
				$this->view->userp  = $u2p;
				//-- END TODO-1647
				
				$ug = new Usergroup();
				$grouparr = $ug->getClientGroups($clientid);

				foreach($grouparr as $k_group => $v_group)
				{
					$client_groups[$v_group['id']] = $v_group;
				}
				
				$user = Doctrine_Query::create()
					->select("*")
					->from('User')
					->where('clientid = ' . $clientid)
					->andWhere('isactive=0 and isdelete = 0')
					->orderBy('last_name ASC');
				$userarray = $user->fetchArray();

				if(count($userarray) > 0)
				{
					$available_user_groups = array();
					foreach($userarray as $k_user => $v_user)
					{
						if($v_user['groupid'] != '0')
						{
							$available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
						}
					}

					$this->view->available_groups = $available_user_groups;
					// Maria:: Migration ISPC to CISPC 08.08.2020
					//ISPC-2409 Ancuta 08.11.2019
					User::beautifyName($userarray);
					foreach($userarray as $k=>$udata){
					    $userarray[$k]['pseudo_id']  = $u2p[$udata['id']];
					}
					$this->view->user_array= $userarray;
					
					/*
					$grid = new Pms_Grid($userarray, 4, count($userarray), "listclientuser.html");
					$this->view->usergrid = $grid->renderGrid();
					*/
					//--
					//special client users
					$client_s_user = new UserMessageClient();
					$get_client_s_users = $client_s_user->getMessageSpecialUsers($clientid);

					$this->view->client_special_users = $get_client_s_users;
				}
				else
				{
					$this->view->usergrid = "<div class='err'>" . $this->view->translate('nousers') . "</div>";
				}
			}
			else
			{
				$user_pseudo =  new UserPseudoGroup();
				$user_ps_arr =  $user_pseudo->get_userpseudo();
				
				foreach($user_ps_arr as $k=>$psg){
					$user_ps[$psg['id']] = $psg;
				}
				
				
				
				$user_grouppseudo =  new PseudoGroupUsers();
				$user_gr_ps =  $user_grouppseudo->get_usersgroup();
				
				foreach($user_ps as $pseudo_id => $v)
				{
					$arr_gr_pseudo[$v['id']] = $user_grouppseudo->get_users($v['id']);
					
					if(empty($arr_gr_pseudo[$v['id']]))
					{
					unset( $user_ps[$pseudo_id]);
					}
				}
				
				
				$this->view->user_pseudo  = $user_ps;
				if($logininfo->clientid > 0)
				{
					$whereclient = ' or clientid = ' . $logininfo->clientid;
				}
				$ug = new Usergroup();
				$grouparr = $ug->getClientGroups($logininfo->clientid);

				foreach($grouparr as $k_group => $v_group)
				{
					$client_groups[$v_group['id']] = $v_group;
				}
				
			   foreach($user_gr_ps as $k_gr =>$val_gr)
			   {
			   	$u2p[$val_gr['user_id']]= $val_gr['pseudo_id'];
			   }
			  foreach ($user_ps as $k => $v)
			  {
			  	$arr_gr_pseudo = $user_grouppseudo->get_users( $v['id']);
			  	//print_r($arr_gr_pseudo); exit;
			  }
			 
			   
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('id = ' . $logininfo->userid . $whereclient)
					->andWhere('isactive=0 and isdelete = 0')
					->orderBy('last_name ASC');
				$userarray = $user->fetchArray();
				//print_r($userarray); exit;
				
				if(count($userarray) > 0)
				{
					$available_user_groups = array();
					foreach($userarray as $k_user => $v_user)
					{
						if($v_user['groupid'] != '0')
						{
							$available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
						}
					}
                 //print_r($available_user_groups); exit;
					$this->view->available_groups = $available_user_groups;
					
					$this->view->userp = $u2p;
					//ISPC-2409 Ancuta 08.11.2019
					User::beautifyName($userarray);
					foreach($userarray as $k=>$udata){
					    $userarray[$k]['pseudo_id']  = $u2p[$udata['id']];
					}
					$this->view->user_array= $userarray;

					/*
					$grid = new Pms_Grid($userarray, 4, count($userarray), "listclientuser.html");
					$this->view->usergrid = $grid->renderGrid();
					*/
					// --
					//special client users
					$client_s_user = new UserMessageClient();
					$get_client_s_users = $client_s_user->getMessageSpecialUsers($clientid);

					$this->view->client_special_users = $get_client_s_users;
				}
				else
				{
					$this->view->usergrid = "<div class='err'>" . $this->view->translate('nousers') . "</div>";
				}
			}
		}

		public function inboxAction()
		{
			setcookie("openmenu", "m27_menu", "", "/", "www.ispc-login.de");
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$a_folders = array("0" => $this->view->translate('inbox'));

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$a_folders = array("0" => $this->view->translate('inbox'));

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;

			$columnarray = array("pk" => "id", "dt" => "msg_date", "tt" => "title");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isactive=0 and isdelete = 0')
				->orderBy('last_name asc');
			$userarray = $user->fetchArray();

			foreach($userarray as $ks => $vuser)
			{
				$user_details[$vuser['id']]['name'] = $vuser['user_title'] . ' ' . $vuser['last_name'] . ', ' . $vuser['first_name'];
			}

			if(strlen($_GET['fld']) > 0)
			{
				$mail = Doctrine_Query::create()
					->select("count(*)")
					->from('Messages m')
					->leftJoin("m.MessagesDeleted m2 ON m.id = m2.messages_id AND m2.messages_id IS NOT NULL AND m2.recipient = '" . $logininfo->userid. "'")
					->where(' m2.messages_id IS NULL ')
					
					->andWhere('m.recipient = ' . $logininfo->userid)
					->andWhere('m.folder_id = ?', $_GET['fld'])
					->andWhere('m.delete_msg = ?', '0')
					->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
				$mailexec = $mail->execute();
				$mailarray = $mailexec->toArray();
				if($mailarray[0]['count'] == 0)
				{
					$this->view->btnmail = 'disabled="disabled"';
				}
				$limit = 15;
				$mail->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content");
				$mail->limit($limit);
				$mail->offset($_GET['pgno'] * $limit);

				$maillimitexec = $mail->execute();
				$maillimit = $maillimitexec->toArray();

				
				foreach($maillimit as $k=>$mdata){
					if( $mdata['priority'] != "none"){
						$maillimit[$k]['title'] = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mdata['priority']).' | '.$mdata['title'] ;
					}
				}
				$grid = new Pms_Grid($maillimit, 1, $mailarray[0]['count'], "listinboxmails.html");
				$grid->user_details = $user_details;
				$this->view->inboxgrid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("inboxnavigation.html", 5, $_GET['pgno'], $limit);

				$fold = Doctrine_Query::create()
					->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
					->from('MessageFolder')
					->where('id =?',$_GET['fld']);
				$foldexec = $fold->execute();
				$folderarray = $foldexec->toArray();

				$this->view->inbox = ucfirst($folderarray['folder_name']);
			}
			else
			{
				$mail = Doctrine_Query::create()
					->select("count(*)")
					->from('Messages m')
					->leftJoin("m.MessagesDeleted m2 ON m.id = m2.messages_id AND m2.messages_id IS NOT NULL AND m2.recipient = '" . $logininfo->userid. "'")
					->where(' m2.messages_id IS NULL ')
					->andWhere('m.recipient = ' . $logininfo->userid)
					->andWhere('m.folder_id = ?', 0)
					->andWhere('m.delete_msg = ?', '0')
					
					/*
					2 more variations of the message_deleted JOIN , for better understanding , 
					v2 if NOT to be used since id not indexable and will fail on null
					v2:
					->andWhere("( m.id NOT IN (SELECT m2.messages_id FROM MessagesDeleted as m2 WHERE m2.recipient = '".$logininfo->userid."') )")
					v3:
					->andWhere("(NOT EXISTS (SELECT m2.messages_id FROM MessagesDeleted AS m2 WHERE m.id=m2.messages_id AND m2.recipient = '".$logininfo->userid."') )")
					*/
					->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
				$mailexec = $mail->execute();
				$mailarray = $mailexec->toArray();

				if($mailarray[0]['count'] == 0)
				{
					$this->view->btnmail = 'disabled="disabled"';
				}

				$limit = 15;
				$mail->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content");
				$mail->limit($limit);
				$mail->offset($_GET['pgno'] * $limit);

				$maillimitexec = $mail->execute();
				$maillimit = $maillimitexec->toArray();

				foreach($maillimit as $k=>$mdata){
					if( $mdata['priority'] != "none"){
						$maillimit[$k]['title'] = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mdata['priority']).' | '.$mdata['title'] ;
					}
				}
				$grid = new Pms_Grid($maillimit, 1, $mailarray[0]['count'], "listinboxmails.html");
				$grid->user_details = $user_details;
				$this->view->inboxgrid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("inboxnavigation.html", 5, $_GET['pgno'], $limit);
				$this->view->inbox = $this->view->translate('inbox');
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['maillist'] = $this->view->render('message/fetchlist.html');
			echo json_encode($response);
			exit;
		}

		public function outboxAction()
		{
			setcookie("openmenu", "m27_menu", "", "/", "www.ispc-login.de");
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			$a_folders = array("0" => $this->view->translate('outbox'));

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;
		}

		public function fetchoutlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "dt" => "msg_date", "tt" => "title","rec" => "recipient");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isactive=0 and isdelete = 0')
				->orderBy('last_name asc');
			$userarray = $user->fetchArray();

			foreach($userarray as $ks => $vuser)
			{
				$user_details[$vuser['id']]['name'] = $vuser['last_name'] . ', ' . $vuser['first_name'];
			}

			if(strlen($_GET['fld']) > 0)
			{
				$mail = Doctrine_Query::create()
					->select("count(*)")
					->from('Messages m')
					->leftJoin("m.MessagesDeleted m2 ON m.id = m2.messages_id AND m2.messages_id IS NOT NULL AND m2.recipient = '" . $logininfo->userid. "'")
					->where(' m2.messages_id IS NULL ')
					->andWhere('m.sender = ' . $logininfo->userid)
					->andWhere('m.delete_msg = ?', '0')
					->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
				$mailexec = $mail->execute();
				$mailarray = $mailexec->toArray();

				$limit = 15;
				$mail->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content");
				$mail->limit($limit);
				$mail->offset($_GET['pgno'] * $limit);

				$maillimitexec = $mail->execute();
				$maillimit = $maillimitexec->toArray();

				foreach($maillimit as $k=>$mdata){
					if( $mdata['priority'] != "none"){
						$maillimit[$k]['title'] = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mdata['priority']).' | '.$mdata['title'] ;
					}
				}
				$grid = new Pms_Grid($maillimit, 1, $mailarray[0]['count'], "listinboxmails.html");
				$grid->user_details = $user_details;
				$this->view->inboxgrid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("outboxnavigation.html", 5, $_GET['pgno'], $limit);

				$fold = Doctrine_Query::create()
					->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
					->from('MessageFolder')
// 					->where('id =' . $_GET['fld']);
				    ->where('id =?',$_GET['fld']);
				$foldexec = $fold->execute();
				$folderarray = $foldexec->toArray();

				$this->view->inbox = ucfirst($folderarray['folder_name']);
			}
			else
			{
				$mail = Doctrine_Query::create()
					->select("count(*)")
					->from('Messages m')
					->leftJoin("m.MessagesDeleted m2 ON m.id = m2.messages_id AND m2.messages_id IS NOT NULL AND m2.recipient = '" . $logininfo->userid. "'")
					->where(' m2.messages_id IS NULL ')
					->andWhere('m.sender = ' . $logininfo->userid)
					->andWhere('m.delete_msg = ?', '0')
					->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
				$mailexec = $mail->execute();
				$mailarray = $mailexec->toArray();

				$limit = 15;
				$mail->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content");
				$mail->limit($limit);
				$mail->offset($_GET['pgno'] * $limit);

				$maillimitexec = $mail->execute();
				$maillimit = $maillimitexec->toArray();

				foreach($maillimit as $k=>$mdata){
					if( $mdata['priority'] != "none"){
						$maillimit[$k]['title'] = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mdata['priority']).' | '.$mdata['title'] ;
					}
				}
				$grid = new Pms_Grid($maillimit, 1, $mailarray[0]['count'], "listoutboxmails.html");
				$grid->user_details = $user_details;
				$this->view->inboxgrid = $grid->renderGrid();
				$this->view->navigation = $grid->dotnavigation("outboxnavigation.html", 5, $_GET['pgno'], $limit);

				$this->view->inbox = $this->view->translate('inbox');
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['maillist'] = $this->view->render('message/fetchoutlist.html');

			echo json_encode($response);
			exit;
		}

		public function openmailAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->view->userid = $logininfo->userid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			// get all user messages  
			$mail = Doctrine_Query::create()
			->select("id")
			->from('Messages')
			->where('recipient = ' . $logininfo->userid.' OR sender = ' . $logininfo->userid.'');
			$mailarray_user = $mail->fetchArray();
			
			foreach($mailarray_user as $k=>$msg_data){
			    $user_messages_array[] = $msg_data['id'];
			}
			
			if(empty($user_messages_array)){
			    $user_messages_array[] = "9999999999";
			}
			
			// Error   
			if(!in_array($_GET['id'],$user_messages_array)){
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			
			$mail = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content")
				->from('Messages')
				->where('id= ?',  $_GET['id']);
			$mailarray = $mail->fetchArray();

			$priority = "";
			if( ! empty($mailarray[0]['priority']) && $mailarray[0]['priority'] != "none"){
				$priority = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mailarray[0]['priority']);
				$priority .= " | ";
			}

			
			$this->view->title = $priority.$mailarray[0]['title'];
			$this->view->msg_date = date('d.m.Y H:i', strtotime($mailarray[0]['msg_date']));

			if($mailarray[0]['source'] == "6_weeks_system_message")
			{
				$content = str_replace("Anlage 4a", "Anlage 4", $mailarray[0]['content']);

				$content = str_replace("anlage4awl", "anlage4wl", $content);
			}
			else
			{
				$content = $mailarray[0]['content'];
			}
            //ISPC-2913,Elena,11.05.2021
			if($mailarray[0]['source'] != 'userrequest_client'){
                $this->view->content = nl2br($content);
            }else{
                $this->view->content = $content;
            }


			$userid = $mailarray[0]['sender'];
			if($userid > 0)
			{
				$user = Doctrine::getTable('User')->find($userid);
				$userarray = $user->toArray();
				$this->view->sendername = ucfirst($userarray['last_name']) . ", " . ucfirst($userarray['first_name']);
				$this->view->reply = '1';
			}
			else
			{
				$this->view->sendername = "System Message";
				$this->view->reply = '0';
			}

			$other_users = explode(',', $mailarray[0]['recipients']);
			if(!empty($other_users))
			{
				$usr = new User();
				$users_data = $usr->getMultipleUserDetails($other_users);

				foreach($users_data as $k_usrdata => $v_usrdata)
				{
					$users_datas[] = $v_usrdata['last_name'] . ' ' . $v_usrdata['first_name'];
				}
				$this->view->other_users_data = $users_datas;
			}

			$update = Doctrine_Query::create()
				->update('Messages')
				->set('read_msg', '1')
				->where('id = ?', $_GET['id'])
				->andWhere('recipient=' . $logininfo->userid);
			$update->execute();

			if($_GET['fld'] > 0)
			{
				$this->view->fld = "fld=" . $_GET['fld'] . "&";
			}
		}

		public function setreadAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('setread', $logininfo->userid, 'canadd');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->viewRenderer('inbox');
			if(strlen($_GET['fld']) > 0)
			{
				$str = "?fld=" . $_GET['fld'];
			}
			
			$this->view->action = "message/inbox" . $str;
			
			if($this->getRequest()->isPost())
			{
				if(count($_POST['msg_id']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 2;
				}
				
				if($error == 0)
				{
					foreach($_POST['msg_id'] as $key => $val)
					{
						$update = Doctrine_Query::create()
							->update('Messages')
							->set('read_msg', '1')
							->where('id = ?', $val);
						$update->execute();
						$this->view->error_message = $this->view->translate('setmailread');
					}
					$this->_redirect(APP_BASE . 'message/inbox' . $str);
				}
			}

			$a_folders = array("0" => $this->view->translate('inbox'));

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;
		}

		public function setunreadAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('setunread', $logininfo->userid, 'canadd');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->viewRenderer('inbox');
			if(strlen($_GET['fld']) > 0)
			{
				$str = "?fld=" . $_GET['fld'];
			}
			
			$this->view->action = "message/inbox" . $str;
			
			if($this->getRequest()->isPost())
			{
				if(count($_POST['msg_id']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 2;
				}
				
				if($error == 0)
				{
					foreach($_POST['msg_id'] as $key => $val)
					{
						$update = Doctrine_Query::create()
							->update('Messages')
							->set('read_msg', '0')
							->where('id = ?', $val);
						$update->execute();
						$this->view->error_message = $this->view->translate('setmailunread');
					}

					$this->_redirect(APP_BASE . 'message/inbox' . $str);
				}
			}

			$a_folders = array("0" => $this->view->translate('inbox'));

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;
		}

		public function replymailAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canadd');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->viewRenderer('sendmessages');
			if($_GET['fld'] > 0)
			{
				$fld = "?fld=" . $_GET['fld'];
			}
			if($this->getRequest()->isPost())
			{
				$message_form = new Application_Form_Messages();

				if($message_form->Validate($_POST))
				{
					$message_form->InsertReplyData($_POST);
					$this->view->error_message = $this->view->translate('mailsentmsg');
					$this->_redirect(APP_BASE . "message/inbox" . $fld);
				}
				else
				{
					$this->retainValues($_POST);
					$message_form->assignErrorMessages();
				}
			}
			$messages_obj = new Messages();
			$priority_ranks = $messages_obj->priority_ranks();
			$this->view->priority_ranks = $priority_ranks;
				
			$ug = new Usergroup();
			$grouparr = $ug->getClientGroups($logininfo->clientid);

			foreach($grouparr as $k_group => $v_group)
			{
				$client_groups[$v_group['id']] = $v_group;
			}

			$mail = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content")
				->from('Messages')
				->where('id= ?',  $_GET['id']);
			$mailarray = $mail->fetchArray();
			
			$userid = $mailarray[0]['sender'];

			$sender_id = $mailarray[0]['sender'];
			//ISPC-2409 Ancuta 08.11.2019    
			$this->view->sender_id =$sender_id;
            //--
            //ISPC-2808,Elena,29.01.2021
            $aAllRecipients = [];
            if($_REQUEST['reply'] == 'all'){

                $aAllRecipients = explode(',', $mailarray[0]['recipients']);
                $aOtherRecipients =  [];
                //don't send to me
                foreach($aAllRecipients as $rec){
                    if($rec != $logininfo->userid){
                        $aOtherRecipients[] = $rec;
                    }
                }
                $this->view->recipients = $aOtherRecipients;

            }



            $user = Doctrine::getTable('User')->find($userid);
			$userarray = $user->toArray();
			$this->view->sendername = ucfirst($userarray['last_name']) . "," . ucfirst($userarray['first_name']);

			
			$priority = "";
			/* 
			if( ! empty($mailarray[0]['priority']) && $mailarray[0]['priority'] != "none"){
				$priority = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mailarray[0]['priority']);
				$priority .= " | ";
			}
			 */
			$this->view->title = "AW: " . $mailarray[0]['title'];
			$this->view->content .= "\n";
			$this->view->content .= "\n";
			$this->view->content .= "\n";
			$this->view->content .= "\n";
			$this->view->content .= "---------------------------------------\n";
			$this->view->content .= $this->view->translate('date') . ": " . date('d.m.Y H:i', strtotime($mailarray[0]['msg_date'])) . "\n";
			$this->view->content .= $this->view->translate('sender') . ": " . ucfirst($userarray['last_name']) . "," . ucfirst($userarray['first_name']) . "\n";
			$this->view->content .= $this->view->translate('title') . ": ".$priority. ucfirst($mailarray[0]['title']) . "\n";
			$this->view->content .= "---------------------------------------\n";
			$this->view->content .= $mailarray[0]['content'];

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('id = ' . $userid . ' and isactive=0 and isdelete = 0')
				->orWhere('clientid = ' . $logininfo->clientid . ' and isactive=0 and isdelete = 0')
                ->orWhereIn('id', $aAllRecipients) //ISPC-2808,Elena,29.01.2021
				->orderBy('last_name asc');
			$userarray = $user->fetchArray();
			
			
		$user_pseudo =  new UserPseudoGroup();
				$user_ps_arr =  $user_pseudo->get_userpseudo();
				
				foreach($user_ps_arr as $k=>$psg){
					$user_ps[$psg['id']] = $psg;
				}
				
				
				
				$user_grouppseudo =  new PseudoGroupUsers();
				$user_gr_ps =  $user_grouppseudo->get_usersgroup();
				
				foreach($user_ps as $pseudo_id => $v)
				{
					$arr_gr_pseudo[$v['id']] = $user_grouppseudo->get_users($v['id']);
					
					if(empty($arr_gr_pseudo[$v['id']]))
					{
					unset( $user_ps[$pseudo_id]);
					}
				}
				
				
				$this->view->user_pseudo  = $user_ps;
				if($logininfo->clientid > 0)
				{
					$whereclient = ' or clientid = ' . $logininfo->clientid;
				}
				$ug = new Usergroup();
				$grouparr = $ug->getClientGroups($logininfo->clientid);

				foreach($grouparr as $k_group => $v_group)
				{
					$client_groups[$v_group['id']] = $v_group;
				}
				
			   foreach($user_gr_ps as $k_gr =>$val_gr)
			   {
			   	$u2p[$val_gr['user_id']]= $val_gr['pseudo_id'];
			   }
			  foreach ($user_ps as $k => $v)
			  {
			  	$arr_gr_pseudo = $user_grouppseudo->get_users( $v['id']);
			  	//print_r($arr_gr_pseudo); exit;
			  }
			 
			$this->view->user_pseudo  = $user_ps;

			$available_user_groups = array();
			foreach($userarray as $k_user => $v_user)
			{
				if($v_user['groupid'] != '0')
				{
					if(!empty($client_groups[$v_user['groupid']]['groupname']))
					{
						$available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
					}
					else //special case where user has diferent client id
					{
						$udata = $ug->getUserGroupData($v_user['groupid']);
						$available_user_groups[$v_user['groupid']] = $udata[0]['groupname'];
					}
				}
			}
			
			//ISPC-2409 Ancuta 08.11.2019
			User::beautifyName($userarray);
			foreach($userarray as $k=>$udata){
			    $userarray[$k]['pseudo_id']  = $u2p[$udata['id']];
			}
			$this->view->user_array= $userarray;
				
			/*
			 $grid = new Pms_Grid($userarray, 4, count($userarray), "listclientuser.html");
			 $this->view->usergrid = $grid->renderGrid();
			 */
			//--
			
			
			$this->view->available_groups = $available_user_groups;
			$this->view->idul = '3';
			$this->view->userp = $u2p;
			$grid = new Pms_Grid($userarray, 5, count($userarray), "listclientuser.html");
			$grid->sender = $sender_id;
			$this->view->usergrid = $grid->renderGrid();
		}

		public function deletemailsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$folder = Doctrine_Query::create()
				->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
				->from('MessageFolder')
				->where('clientid = ?', $logininfo->clientid)
				->andWhere('userid = ?', $logininfo->userid);
			$folderexec = $folder->execute();
			$this->view->options = '<option value="">' . $this->view->translate('movetofolder') . '</option><option value="0">' . $this->view->translate('inbox') . '</option>';
			
			foreach($folderexec->toArray() as $ke => $val)
			{
				$this->view->options.='<option value="' . $val['id'] . '">' . $val['folder_name'] . '</option>';
			}

			$this->_helper->viewRenderer('inbox');
			if($this->getRequest()->isPost())
			{
				if(count($_POST['msg_id']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 2;
				}
				
				if($error == 0)
				{
					$folder_id = 0;
					if ($_POST['hdnfld'] > 0 ){
						$folder_id = (int)$_POST['hdnfld'];
					}
					$insert_data = array();
					foreach($_POST['msg_id'] as $key => $val)
					{
						/*
						$update = Doctrine_Query::create()
							->update('Messages')
							->set('delete_msg', '1')
							->where('id = ?', $val);
						$update->execute();
						*/
						$insert_data[] = array(
							'messages_id'	=>	(int)$val,
							'clientid'	=>	$logininfo->clientid,
							'recipient'	=>	$logininfo->userid,
							'folder_id'	=>	$folder_id,
						);
					}
					//mark as deleted this messages					
					MessagesDeleted :: set_messages($insert_data);
			
					$this->view->error_message = $this->view->translate('maildelete');
				}
			}
		}

		public function createfolderAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('createfolder', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			else
			{
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('createfolder', $logininfo->userid, 'canadd');
				if(!$return)
				{
					$this->view->style = 'none';
				}
			}
			
			if($this->getRequest()->isPost())
			{
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('createfolder', $logininfo->userid, 'canadd');
				if(!$return)
				{
					$this->_redirect(APP_BASE . "error/previlege");
				}

				$folder_form = new Application_Form_Messages();

				if($folder_form->validatefolder($_POST))
				{
					$folder_form->InsertFolderData($_POST);
					$this->view->error_message = $this->view->translate('foldercreated');
				}
				else
				{
					$folder_form->assignErrorMessages();
				}
			}

			$a_folders = array("0" => $this->view->translate('inbox'));

			if($_GET['flg'] == 'suc')
			{
				$error_message = $this->view->translate('folderdelete');
			}
			else if($_GET['flg'] == 'err')
			{
				$error_message = $this->view->translate('cannotdeletefolder');
			}

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentidarray = $a_folders;

			$folder = Doctrine_Query::create()
				->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
				->from('MessageFolder')
				->where('userid = ' . $logininfo->userid);
			$folderexec = $folder->execute();
			$folderarray = $folderexec->toArray();

			$grid = new Pms_Grid($folderarray, 1, count($folderarray), "listuserfolder.html");
			$grid->gridview->error_message = $error_message;
			$this->view->foldergrid = $grid->renderGrid();
		}

		public function editfolderAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('createfolder', $logininfo->userid, 'canedit');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$a_folders = array("0" => $this->view->translate('inbox'));
			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentidarray = $a_folders;

			if(strlen($_GET['id']) > 0)
			{
				$folder = Doctrine_Query::create()
					->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
					->from('MessageFolder')
					->where('userid = ' . $logininfo->userid)
					->andWhere('id= ?', $_GET['id']);

				$folderexec = $folder->execute();
				$folder_array = $folderexec->toArray();
				$this->view->parentid = $folder_array[0]['parentid'];
				$this->retainValues($folder_array[0]);
			}

			$this->_helper->viewRenderer('createfolder');

			if($this->getRequest()->isPost())
			{

				$folder_form = new Application_Form_Messages();

				if($folder_form->validatefolder($_POST))
				{
					$folder_form->EditFolderData($_POST);
					$this->view->error_message = $this->view->translate('folderupdated');
					$this->retainValues($_POST);
				}
				else
				{
					$folder_form->assignErrorMessages();
				}
			}

			$folder = Doctrine_Query::create()
				->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
				->from('MessageFolder')
				->where('userid = ' . $logininfo->userid);
			$folderexec = $folder->execute();
			$folderarray = $folderexec->toArray();

			$grid = new Pms_Grid($folderarray, 1, count($folderarray), "listuserfolder.html");
			$grid->gridview->error_message = $error_message;
			$this->view->foldergrid = $grid->renderGrid();
		}

		public function deletefolderAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('createfolder', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$folder_form = new Application_Form_Messages();

			$this->_helper->viewRenderer('createfolder');
			if($_GET['fid'] > 0)
			{
				if($_GET['m'] == 'all')
				{
					$message = Doctrine_Query::create()
						->delete('Messages')
						->where('folder_id = ?', $_GET['fid']);

					$mess = $message->execute();
					$delete = Doctrine_Query::create()
						->delete('MessageFolder')
						->where('id= ?', $_GET['fid']);
					$delexec = $delete->execute();

					if($delexec)
					{
						$this->_redirect(APP_BASE . 'message/createfolder?flg=suc');
					}
				}
				else
				{
					$message = Doctrine_Query::create()
						->select('*')
						->from('Messages')
						->where('folder_id = ?', $_GET['fid']);
					$mess = $message->execute();
					$messagearay = $mess->toArray();

					if(count($messagearay) > 1)
					{
						$this->_redirect(APP_BASE . 'message/createfolder?flg=err');
					}
					else
					{
						$delete = Doctrine_Query::create()
							->delete('MessageFolder')
							->where('id= ?', $_GET['fid']);
						$delexec = $delete->execute();

						if($delexec)
						{
							$this->_redirect(APP_BASE . 'message/createfolder?flg=suc');
						}
					}
				}
			}
		}

		public function movetofolderAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('movetofolder', $logininfo->userid, 'canadd');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$folder = Doctrine_Query::create()
				->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
				->from('MessageFolder')
				->where('clientid = ?', $logininfo->clientid)
				->andWhere('userid = ?', $logininfo->userid);
			$folderexec = $folder->execute();
			$this->view->options = '<option value="">' . $this->view->translate('movetofolder') . '</option><option value="0">' . $this->view->translate('inbox') . '</option>';
			foreach($folderexec->toArray() as $ke => $val)
			{
				$this->view->options.='<option value="' . $val['id'] . '">' . $val['folder_name'] . '</option>';
			}

			$this->_helper->viewRenderer('inbox');

			if($this->getRequest()->isPost())
			{

				if(is_array($_POST['msg_id']) && count($_POST['msg_id']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 2;
				}
				if($_POST['folder_name'] == '-1' && ($_POST['folder_name'] == '' || $_POST['hdnfld'] == ''))
				{
					$this->view->error_message = $this->view->translate('selectfolder');
					$error = 2;
				}

				if($error == 0)
				{
					foreach($_POST['msg_id'] as $key => $val)
					{
						$update = Doctrine_Query::create()
							->update('Messages')
							->set('folder_id', "'" . $_POST['folder_name'] . "'")
							->where('id = ' . $val);
						$update->execute();
						$this->view->error_message = $this->view->translate('mailmovetofolder');
					}
				}
			}
			$a_folders = array("0" => "Inbox");

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;
		}

		public function privatemessagesAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['popup'] == "popup")
			{
				$this->_helper->layout->setLayout('layout_popup');
				$this->view->clickaction = "setchild()";
			}

			if($this->getRequest()->isPost())
			{
				$message_form = new Application_Form_Messages();

				if($message_form->Validate($_POST))
				{

					$message_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate('mailsentmsg');

					$this->view->javascriptfunc = "assigndoctor();";
				}
				else
				{

					$message_form->assignErrorMessages();
				}
			}

			$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('clientid = ?', $logininfo->clientid)
				->andWhere('id = ?', $_GET['id'])
				->orderBy('last_name ASC');
			$userexec = $user->execute();
			$userarray = $userexec->toArray();

			$grid = new Pms_Grid($userarray, 1, count($userarray), "listclientuser.html");
			$this->view->usergrid = $grid->renderGrid();
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		private function getparentcategory(&$a_folders, $userid, $parentid, $space)
		{
			$folder = Doctrine_Query::create()
				->select("*,AES_DECRYPT(folder_name,'" . Zend_Registry::get('salt') . "') as folder_name")
				->from('MessageFolder')
				->where('userid = ' . $userid . ' and parentid=' . $parentid)
				->andWhere('isdelete = 0');

			$folderexec = $folder->execute();
			$folderarray = $folderexec->toArray();


			foreach($folderarray as $key => $val)
			{
				$a_folders[$val['id']] = $space . $val['folder_name'];
// 				$this->getparentcategory($a_folders, $val['userid'], $val['id'], $space . "&nbsp;&nbsp;&nbsp;");
				$this->getparentcategory($a_folders, $val['userid'], $val['id'], $space . " - - - ");
			}

			return;
		}

		public function newinboxAction()
		{
			setcookie("openmenu", "m27_menu", "", "/", "www.ispc-login.de");
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$a_folders = array("0" => $this->view->translate('inbox'));

			$this->getparentcategory($a_folders, $logininfo->userid, 0, '');
			$this->view->parentid = $a_folders;
		}
	}
?>